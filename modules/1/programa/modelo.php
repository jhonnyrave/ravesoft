<?php
class programaModel extends mainModel {

	public $id;  
	public $programa='';
	public $descripcion='';
	public $menu='';
	public $submenu='';
	public $xajaxDefault='';
	public $permisos=array();  
	public $componentes=array();
    public $grupos=array();
	public $autenticado='S';
	public $existe='N';
	                                                                    
	public function __construct($programa=""){  
		parent::__construct();
		$this->programa = strtoupper($programa);
		$this->Conectarse();
		if($programa!=''){
			$this->getDatos();
		}
	}

	public function agregarPermiso($codigo,$descripcion,$sensible){
		$this->permisos[$codigo]=[
            "descripcion"=>$descripcion,
            "sensible"=>$sensible
            ];
	}

	public function getDatos() {

		$programaData = $this->lee_todo("SELECT * from core_programas where programa='{$this->programa}'");
	
		if (!empty($programaData)) {
			$this->existe = 'S';
			$this->id = $programaData[0]->id;
			$this->descripcion = trim($programaData[0]->descripcion);
			$this->menu = trim($programaData[0]->id_menu);
			$this->autenticado = trim($programaData[0]->autenticado);
	
			// Cargar en el objeto los componentes
			$file = MODULE_PATH . $this->menu . "/" . strtolower($this->programa) . "/componentes.ini";
			$componetes = "";
			$link = @fopen($file,'r');
			if ($link){
				$size=filesize($file);
				if($size==0) $size=1;
				$componetes = fread($link,$size);
				fclose($link);
			}
			if($componetes!=''){
				$this->componentes= explode("\n",$componetes);
			}
		} else {
			$this->existe = 'N';
		}
	}
	
	public function getPermisos($programa){
		$data=$this->lee_todo("SELECT trim(opcion) as codigo,trim(nota_opc) as nombre, depende as sensible  
                                        FROM infse.nue_programa01 
                                        where programa='{$programa}' 
                                        order by 1");
		
	}

	public function grabarGeneral(){
		if($this->existe=='N'){
			//inserta
			$this->id=$this->ejecuta_query("INSERT into infse.nue_programa(programa,nue_mod,descripcion,xajaxdefault,autenticado,nuevo,f_ingreso, f_modifica,cod_mod,cod_sub) values ('{$this->programa}','{$this->menu}','{$this->descripcion}','{$this->xajaxDefault}','{$this->autenticado}','S',today,today,'{$this->menu}',0)","id");
		}else{
			//actualiza
			$this->ejecuta_query("UPDATE infse.nue_programa set descripcion='{$this->descripcion}',nue_mod='{$this->menu}',xajaxdefault='{$this->xajaxDefault}',autenticado='{$this->autenticado}', f_modifica=today where programa='{$this->programa}'");
		}
	} 

	public function grabarPermisos(){
		foreach ($this->permisos as $codigo => $info) {
            $nombre=$info['descripcion'];
            $sensible=$info['sensible'];
            $mpermisos[]=$codigo;
			$mval=$this->lee_todo("SELECT opcion FROM infse.nue_programa01 WHERE programa='{$this->programa}' and opcion='$codigo' ");
			if(count($mval)>0){
				//update
				$this->ejecuta_query("UPDATE infse.nue_programa01 set nota_opc='$nombre', depende='$sensible' where programa='{$this->programa}' and opcion='$codigo'");
			}else{
				//insert
				$this->ejecuta_query("INSERT into infse.nue_programa01 (programa,opcion,nota_opc,cod_mod,cod_sub,depende) values ('{$this->programa}','$codigo','$nombre',1,0,'$sensible')");
			}
		}
		$this->ejecuta_query("DELETE FROM infse.nue_programa01 WHERE programa='{$this->programa}' and opcion not in ('".implode("','",$mpermisos)."')");

	}

	public function getPermiso($opcion='',$muestra_error=true){

		if($opcion!=''){ 
			$sql_add=" and po.opcion='$opcion'";
		}else {
			$sql_add="";
		}

		$consulta="SELECT
		DISTINCT po.opcion, po.descripcion as nombre
		FROM
		core_usuarios u,
		core_usuarios_roles ur,
		core_roles r, 
		core_permisos p,
		core_programas_opciones po,
		core_programas pg 
		WHERE
		u.id=ur.id_usuario and
		ur.id_rol=r.id and
		r.id=p.id_rol and
		p.id_programa_opcion=po.id and
		po.id_programa=pg.id and
		u.usuario='".$_SESSION['usuario']."' AND
		pg.id='{$this->id}' 
		$sql_add";
		$m=$this->lee_todo($consulta);
		if(!empty($m)){
			$this->permisos=$m;
			return true;	
		}else{
			return false;
		}
	}

	public function getOpciones(){
		return $this->lee_todo("SELECT a.id,a.programa, trim(b.opcion) opcion, lower(b.nota_opc) as nombre FROM infse.nue_programa a, infse.nue_programa01 b WHERE a.programa=b.programa order by 1,2");
	}

	public function eliminarPrograma(){
		$this->begin_work();
		$this->ejecuta_query("DELETE from infse.nue_programa01 where programa='{$this->programa}'");
		$this->ejecuta_query("DELETE from infse.nue_programa where id='{$this->id}'");
		$this->ejecuta_query("DELETE from informix.permisos where id_programa='{$this->id}'");
		$this->commit();
	}

	public function getMenuProgramas($sub){
		#menus dependientes
		$consulta="SELECT distinct up.id_menu as id, up.orden_menu as orden, up.nombre_menu  as nombre, up.id_menu_parent as id_sub, up.icono from v_usuarios_permisos as up where id_usuario='".$_SESSION['id_usuario']."' and id_menu_parent='$sub' order by 1,2";
		$mmenu=$this->lee_todo($consulta);
		for ($i=0; $i <count($mmenu) ; $i++) { 
			$idsub=$mmenu[$i]->id;
			$mmenu[$i]->sub=$this->getMenuProgramas($idsub);
			
			#programas del menu
			$consulta="SELECT distinct up.id_programa, up.programa, up.descripcion_programa as descripcion , up.orden_programa as orden from  v_usuarios_permisos as up where id_usuario='".$_SESSION['id_usuario']."' and id_menu='$idsub' order by 4,3";
			$mmenu[$i]->progs=$this->lee_todo($consulta);

		}
		return $mmenu;
	}

	public function getMenuProgramasCompat(){
		$consulta="SELECT distinct b.nue_sub as id, trim(INITCAP(lower(c.des_mod))) as nombre, c.icon_mod as icono
		FROM infse.nue_perpro a, infse.nue_programa b, infse.modulo c
		where a.programa=b.programa and b.nue_sub=c.cod_mod and usuario='".$_SESSION['usuario']."' and f_expira_p>=today
		union
		SELECT distinct np.nue_sub as id, trim(INITCAP(lower(m.des_mod))) as nombre, m.icon_mod as icono
		FROM grupos_usuario gu, grupos g, permisos p, nue_programa np, modulo m
		where gu.id_grupo=g.id and p.id_grupo=g.id and p.id_programa=np.id and gu.usuario='".$_SESSION['usuario']."' and np.nue_sub=m.cod_mod and g.estado='A'
		and not exists (select programa from nue_perpro where usuario='".$_SESSION['usuario']."' and programa=np.programa)
		order by 2";
		$m=$this->lee_todo($consulta);
		for ($i=0; $i <count($m) ; $i++) { 
			$sub=$m[$i]->id;
			$consulta="SELECT distinct b.nue_mod as id, trim(INITCAP(lower(c.des_mod))) as nombre, c.icon_mod as icono
			FROM infse.nue_perpro a, infse.nue_programa b, infse.modulo c
			where a.programa=b.programa and b.nue_mod=c.cod_mod and usuario='".$_SESSION['usuario']."' and b.nue_sub='$sub' and f_expira_p>=today
			union
            SELECT distinct np.nue_mod as id, trim(INITCAP(lower(m.des_mod))) as nombre, m.icon_mod as icono
            FROM grupos_usuario gu, grupos g, permisos p, nue_programa np, modulo m
            where gu.id_grupo=g.id and p.id_grupo=g.id and p.id_programa=np.id and gu.usuario='".$_SESSION['usuario']."' and np.nue_sub='$sub' and np.nue_mod=m.cod_mod and g.estado='A'
            and not exists (select programa from nue_perpro where usuario='".$_SESSION['usuario']."' and programa=np.programa)
            order by 2";
			$m2=$this->lee_todo($consulta);
			
			for ($i2=0; $i2 <count($m2) ; $i2++) { 
				$sub2=$m2[$i2]->id;
				$consulta="SELECT distinct lower(trim(b.programa)) as programa, lower(trim(b.descripcion)) as descripcion, nvl(b.nuevo,'') as nuevo 
                    FROM infse.nue_perpro a, infse.nue_programa b 
                    where a.programa=b.programa and usuario='".$_SESSION['usuario']."' and b.nue_sub='$sub' and b.nue_mod='$sub2' and f_expira_p>=today 
                    union
                    SELECT distinct lower(trim(np.programa)) as programa, lower(trim(np.descripcion)) as descripcion, nvl(np.nuevo,'') as nuevo
                    FROM grupos_usuario gu, grupos g, permisos p, nue_programa np, modulo m
                    where gu.id_grupo=g.id and p.id_grupo=g.id and p.id_programa=np.id and gu.usuario='".$_SESSION['usuario']."' and np.nue_sub='$sub' and np.nue_mod='$sub2' and np.nue_mod=m.cod_mod and g.estado='A'
                    and not exists (select programa from nue_perpro where usuario='".$_SESSION['usuario']."' and programa=np.programa)
                    order by 2";
				$m2[$i2]->sub=array();
				$progs=$this->lee_todo($consulta);
				for ($i3=0; $i3 <count($progs) ; $i3++) { 
					$progs[$i3]->descripcion=(ucfirst(strtolower($progs[$i3]->descripcion)));
				}
				$m2[$i2]->progs=$progs;
			}
			$m[$i]->sub=$m2;
			$m[$i]->progs=array();
		}
		return $m;
	}

    public function getGruposPrograma($programa, $sql=''){
        $this->grupos= $this->lee_todo("select g.id,g.grupo,count(distinct gu.usuario) cant
            from grupos g, grupos_usuario gu, permisos p, nue_programa np, nue_usuario nu 
            where g.id=gu.id_grupo and g.id =p.id_grupo and p.id_programa =np.id and gu.usuario=nu.usuario and np.programa='$programa' and clave='rsn' $sql
            group by 1,2
            order by 2");
    }

	
}  