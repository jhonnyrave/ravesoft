<?php
class programaController  extends mainController{

	private $Business;
    public $plantilla="default/blank.html";

	public function __construct () {
		$this->Business = new programaBusiness();
	}

	function buscarPrograma($parametros){

		try{
			$programa=strtoupper($parametros['programa']);
			$myProg= $this->Business->getPermisos($programa);
			//$myProg->getPermisos();
			//$myProg->getGruposPrograma($programa);
            /** @var object $this retorna la informacion al servicio REST o controlador JS*/
            $this->response($myProg);
        }catch (Exception $e){
            $this->response($this->logError($e));
        }
		
	}
	
	function buscarPermisosPrograma($parametros){
		$programa=strtoupper($parametros['programa']);
		$opcion=strtoupper($parametros['opcion']);
		$myProg= new programa($programa);
		$myProg->getGruposPrograma($programa, " and p.opcion='$opcion'");
		$this->response($myProg);
	}
	function traerComponentes(){
		$gestor=opendir(COMPONENT_PATH);
		while (false !== ($componente=readdir($gestor))) {
			$file_ini=COMPONENT_PATH."/$componente/config.ini";	
			if(file_exists($file_ini)){
				$config=parse_ini_file($file_ini);
				$mcomponentes[$componente]=$config;

			}
		}
		$this->response($mcomponentes); 
	}
	function getPermiso($parametros){
		$programa= new programa(strtoupper($parametros['programa']));
		$tienePermiso=$programa->getPermiso(strtoupper($parametros['opcion']??''),false);
		$this->response(array("permiso"=>$tienePermiso,"listado"=>$programa->permisos)); 	
	}
	function getOpciones(){
		$programa= new programa();
		$opciones=$programa->getOpciones();
		for ($i=0; $i <count($opciones) ; $i++) { 
			$opciones[$i]->nombre=htmlentities($opciones[$i]->nombre);
		}
		$this->response($opciones); 		
	}
	
	function getMenuProgramas(){

		try{
			$menu = $this->Business->getMenuProgramas(0);
            $this->response($menu);
        }catch (Exception $e){
            $this->response($this->logError($e));
		}		
	}
	
	function eliminarPrograma($parametros){
		$programa=$parametros['programa'];
		$myProg= new programa($programa);
		$carpetaModulo=MODULE_PATH.$myProg->menu."/".strtolower($programa);
		Template::rmDir($carpetaModulo);
		$myProg->eliminarPrograma();
		$this->response(array("resultado"=>"success")); 	
	}
}