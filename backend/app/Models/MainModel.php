<?php
@include_once("config/error_pdo.php");

class DatabaseConfig
{
    public $motor;
    public $host;
    public $db;
    public $usuario;
    public $password;
    public $puerto;
    public $server;
    public $status = 'off';
    public $default = false;
}

abstract class mainModel
{
    private $DB;
    private $conexion;
    public $exceptionMode ="die";

    public function __construct()
    {
        $this->conexion = new DatabaseConfig();
    }

    private function loadConfigFromIni($file)
    {
        if (file_exists($file)) {
            $config = parse_ini_file($file, true);
            $config = $config['database'];
            $this->conexion = (object) [
                "motor" => $config['motor'],
                "host" => $config['servidor'],
                "db" => $config['base'],
                "usuario" => $config['usuario'],
                "password" => $config['clave'],
                "puerto" => $config['puerto'],
                "server" => $config['server'] ?? '',
                "status" => 'off',
                "default" => false
            ];
        }
    }

    function Conectarse($conexion_=""){
        
        $this->conexion = new DatabaseConfig();
        if ($conexion_ != '') {
           
            $file = CORE . trim($conexion_) . ".ini";
            $this->loadConfigFromIni($file);
        } elseif ($this->conexion->host == '') {
            $config = $_SESSION['config']['database'];
            $this->conexion = (object) [
                "motor" => $config['motor'],
                "host" => $config['servidor'],
                "db" => $config['base'],
                "usuario" => $config['usuario'],
                "password" => $config['clave'],
                "puerto" => $config['puerto'],
                "server" => $config['server'],
                "default" => true
            ];
        }

         if(!isset($GLOBALS['DB']) || $this->conexion->default==false){
            #realiza conexion a la base de datos correspondiente
            try{
                switch ($this->conexion->motor) {
                    case 'mysql':
                        $dbHandle = new PDO("mysql:host={$this->conexion->host};port={$this->conexion->puerto}; dbname={$this->conexion->db}; charset=utf8", $this->conexion->usuario, $this->conexion->password);
                        $dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                        break;
                    case 'mssql':
                        $dbHandle = new PDO("dblib:host={$this->conexion->host}:{$this->conexion->puerto}; dbname={$this->conexion->db}", $this->conexion->usuario, $this->conexion->password);
                        $dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                        break;
                    case 'sqlsrv':
                        $dbHandle = new PDO("sqlsrv:server={$this->conexion->host}; Database={$this->conexion->db}", $this->conexion->usuario, $this->conexion->password);
                        $dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                        break;
                    case 'informix':
                        $dbHandle = new PDO("informix:host={$this->conexion->host};service={$this->conexion->puerto};database={$this->conexion->db};server={$this->conexion->server};client_locale=en_us.819;db_locale=en_us.819;protocol=onsoctcp;EnableScrollableCursors=1;charset=utf8",$this->conexion->usuario,$this->conexion->password);
                        $dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                        break;

                    case 'postgresql':
                        $dbHandle = new PDO("pgsql:host={$this->conexion->host};port={$this->conexion->puerto};dbname={$this->conexion->db}",$this->conexion->usuario,$this->conexion->password);
                        $dbHandle->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
                        $dbHandle->setAttribute(PDO::ATTR_CASE, PDO::CASE_LOWER);
                        break;

                    default:
                        die("Motor -{$this->conexion->motor}- no soportado aun");
                        break;
                }
                $this->DB= $dbHandle;
                $this->conexion->status='on';
            }catch( PDOException $exception ){
                error_PDO($exception,print_r($this->conexion,true));
            }
           
            if($this->conexion->default){
                $GLOBALS['DB']=$this->DB;
            }
        }else{
            if($this->conexion->default){
                $this->DB=$GLOBALS['DB'];
            }
        }
    }

    function lee_todo($query){
		$rows=array();
		if (isset($_REQUEST['modulo'])){
			$modulo_=$_REQUEST['modulo'];
		}else{
			$modulo_='';
		}

		if (isset($_REQUEST['metodo'])){
			$metodo_=$_REQUEST['metodo'];
		}else{
			$metodo_='';
		}

		if (!isset($base)){
			$base='';
		}

		if (!isset($_SESSION['usuario'])){
			$_SESSION['usuario']='';
		}

		if (!isset($_SESSION['datos_adicionales'])){
			$_SESSION['datos_adicionales']='';
		}

		if (!isset($_SESSION['nombreusu'])){
			$_SESSION['nombreusu']='';
		}

		$bk_query=$query;
        $encontrado = "no";
        $arr_fechas=array();
        $arr_bool = array();
		$query = "-- $base".trim($_SESSION['usuario']).$_SESSION['datos_adicionales']."=>".trim($_SESSION['nombreusu'])." (".$_SERVER['REMOTE_ADDR'].") [".$_SERVER['SCRIPT_NAME']." modulo:$modulo_ metodo:$metodo_] ".date("h:i:s a")."
		".$query;
		try{ 
			$statement = $this->DB->query($query);
            $colcount = $statement->columnCount();
            $encontrado = "no";
            $arr_bool = Array(); $arr_fechas = Array();
			$this->conexion=(object) $this->conexion;

            if(in_array($this->conexion->motor, array("mysql","sqlsrv"))){
                for ($i=1; $i <= $colcount; $i++) {
                    $meta = $statement->getColumnMeta(($i-1));
                    if($meta['native_type'] == "DATE"){
                        $encontrado = "si";
                        $arr_fechas[] = $meta['name'];
                    } else if($meta['native_type'] == "BOOLEAN"){
                        $encontrado = "si";
                        $arr_bool[] = $meta['name'];
                    }
                    if($meta['name']==''){
                        $encontrado = "si";
                    }
                }
            }
            $rows = $statement->fetchAll(PDO::FETCH_CLASS);

		}catch( PDOException $exception ){
			$this->error_PDO($exception,$query);
		}


		# Si encuentra campos del FIX los recorre para realizar la correccion
		if($encontrado == "si"){
		    $cantRows =count($rows);
			for ($i=0; $i < $cantRows; $i++) {
				// para corregir las fechas
				if(count($arr_fechas) > 0){
					$count_fechas=count($arr_fechas);
					for ($j=0; $j < $count_fechas; $j++) { 
						$registros='';
						@preg_match ("([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})", $rows[$i]->$arr_fechas[$j], $registros);
						if($registros!=''){//fix para campos tipo fecha nulos
							$rows[$i]->$arr_fechas[$j] = $registros[2]."/".$registros[3]."/".$registros[1];
						}
					}
				}
				// para corregir los booleanos
				if(count($arr_bool) > 0){
					$count_arr_bool=count($arr_bool);
					for ($j=0; $j < $count_arr_bool; $j++) { 
						$rows[$i]->$arr_bool[$j] = $rows[$i]->$arr_bool[$j]==0?"f":"t"; 
					}
				}
			}
		}
		
		return $rows;
	}

    function error_PDO($exception,$query){
		switch ($this->exceptionMode) {
			case 'throw':
				throw $exception;
				break;
			case 'die':
				echo "<pre>";
				print_r($exception);
				print_r($query);
				die("");
				break;
		}	
	}

    function lee_prepare($query, $params)
{
    try {
        if (isset($_REQUEST['modulo'])) {
            $modulo = $_REQUEST['modulo'];
        } else {
            $modulo = '';
        }

        if (isset($_REQUEST['metodo'])) {
            $metodo = $_REQUEST['metodo'];
        } else {
            $metodo = '';
        }

        if (!isset($base)) {
            $base = '';
        }

        if (!isset($_SESSION['usuario'])) {
            $_SESSION['usuario'] = '';
        }

        if (!isset($_SESSION['datos_adicionales'])) {
            $_SESSION['datos_adicionales'] = '';
        }

        if (!isset($_SESSION['nombreusu'])) {
            $_SESSION['nombreusu'] = '';
        }

        $query = "-- " . trim($_SESSION['usuario'] ?? '') . " " . ($_SERVER['SCRIPT_NAME'] ?? '') . " modulo:$modulo metodo:$metodo \n" . $query;

        $statement = $this->DB->prepare($query);
        $statement->execute($params);

        $colcount = $statement->columnCount();
        $encontrado = false;
        $arr_bool = $arr_fechas = $arr_lchar = [];
        $campo_vacio = false;

        if (in_array($this->conexion->motor, array("mysql", "informix"))) {
            for ($i = 1; $i <= $colcount; $i++) {
                $meta = $statement->getColumnMeta(($i - 1));

                if (empty($meta['name'])) {
                    $encontrado = true;
                    $campo_vacio = true;
                }

                if ($meta['native_type'] == "DATE") {
                    $encontrado = true;
                    $arr_fechas[] = $meta['name'];
                } else if ($meta['native_type'] == "BOOLEAN") {
                    $encontrado = true;
                    $arr_bool[] = $meta['name'];
                }

                if ($meta['native_type'] == "CHAR" || $meta['native_type'] == "VARCHAR") {
                    $encontrado = true;
                    $arr_lchar[] = $meta['name'];
                }
            }
        }

        $rows = $statement->fetchAll(PDO::FETCH_CLASS);

        if ($encontrado) {
            foreach ($rows as $i => $row) {
                if (!empty($arr_fechas)) {
                    foreach ($arr_fechas as $fecha) {
                        $registros = [];
                        preg_match("/([0-9]{4})-([0-9]{1,2})-([0-9]{1,2})/", $row->$fecha, $registros);
                        if (!empty($registros)) {
                            $rows[$i]->$fecha = $registros[2] . "/" . $registros[3] . "/" . $registros[1];
                        }
                    }
                }

                if (!empty($arr_bool)) {
                    foreach ($arr_bool as $bool) {
                        $rows[$i]->$bool = $row->$bool == 0 ? "f" : "t";
                    }
                }

                if (!empty($arr_lchar)) {
                    foreach ($arr_lchar as $lchar) {
                        $rows[$i]->$lchar = utf8_encode(trim($row->$lchar));
                    }
                }
            }
        }

        if ($_SESSION['debug_lee_todo'] ?? '' == "1") {
            $_SESSION['contenido_debug'] .= "<div class='debug_lee_todo'>$query<br><a href='javascript:void(0)' onclick='xajax_traer_lee_todo(\"" . base64_encode($query) . "\")' />Ver resultados</a><br><b><i>-&gt; " . count($rows) . " registros devueltos</i></b></div>";
        }

        return $rows;
    } catch (PDOException $exception) {
       
            $this->error_PDO($exception, $query);
      

        return [];
    }
}


    
    
    function ejecuta_query($queri,$retorna='count',$respuestaType=''){
        if (isset($_REQUEST['modulo'])){
			$modulo_=$_REQUEST['modulo'];
		}else{
			$modulo_='';
		}

		if (isset($_REQUEST['metodo'])){
			$metodo_=$_REQUEST['metodo'];
		}else{
			$metodo_='';
		}

		if (!isset($base)){
			$base='';
		}

		if (!isset($_SESSION['usuario'])){
			$_SESSION['usuario']='';
		}

		if (!isset($_SESSION['datos_adicionales'])){
			$_SESSION['datos_adicionales']='';
		}

		if (!isset($_SESSION['nombreusu'])){
			$_SESSION['nombreusu']='';
		}

        $bk_query=$queri;
        $queri = "-- ".trim($_SESSION['usuario']??'')." ".($_SERVER['SCRIPT_NAME']??'')." modulo:$modulo_ metodo:$metodo_ "."
		".$queri;//algo raro con: CURSOR not on SELECT statement, se debe dejar esa primera linea el --
        $queri=utf8_decode($queri);
        try{
            $cant=$this->DB->exec($queri);
            $error=$this->DB->errorInfo();
            if($error[0]!='000' && $error!=''){
                Throw new Exception("<span style='color:red;'><b>CODIGO: {$error[1]}</b><br>{$error[2]}</span>");
            }
        }catch( PDOException $exception ){
            error_PDO($exception,$queri,'');
        }
        if($_SESSION['debug_ejecuta_query']??'' == "1"){
            $_SESSION['contenido_debug'] .= "<div class='debug_ejecuta_query'><br>$queri<br><b><i>-&gt; ".$cant." registros afectados</i></b></div>";
        }
        if($retorna=='count') return $cant;
        else return $this->DB->lastInsertId();
    }
    function ejecuta_prepare($queri,$params,$retorna='count',$respuestaType=''){
        $modulo_=$_REQUEST['modulo']??'';
        $metodo_=$_REQUEST['metodo']??''; //.print_r($params,true)."
        $queri = "-- ".trim($_SESSION['usuario']??'')." ".($_SERVER['SCRIPT_NAME']??'')." modulo:$modulo_ metodo:$metodo_ 
		".$queri;
        $queri=utf8_decode($queri);
        try{
            $resultado = $this->DB->prepare($queri);
            $cant = $resultado->execute($params);
        }catch( PDOException $exception ){
            error_PDO($exception,$queri,'');
        }
        if(($_SESSION['debug_ejecuta_query']??'') == "1"){
            $_SESSION['contenido_debug'] .= "<div class='debug_ejecuta_query'><br>$queri<br><b><i>-&gt; ".$cant." registros afectados</i></b></div>";
        }
        $cant=$resultado->rowCount();
        if($retorna=='count') return $cant;
        else return $this->DB->lastInsertId();
    }
    function begin_work(){
        $this->DB->beginTransaction();
    }
    function commit(){
        $this->DB->commit();
    }
    function rollback(){
        $this->DB->rollback();
    }
    function ejecuta_sp($queri){
        try{
            $statement = $this->DB->query($queri);
        }catch( PDOException $exception ){
            error_PDO($exception,$queri);
        }
        $rows = $statement->fetchAll(PDO::FETCH_ASSOC);
        for ($i=0; $i <count($rows) ; $i++) {
            $result[]=trim($rows[$i]['']);
        }
        if(count($rows)==1) $result=$result[0];
        return $result;
    }
    function lee_uno($query){
        $mat=$this->lee_todo($query);
        return $mat[0]??null;
    }
    function isolation($tipo){
        switch (strtolower($tipo)) {
            case 'dirty':
                $sql_add="DIRTY READ";
                break;
            case 'committed':
                $sql_add="COMMITTED READ";
                break;
        }
        $this->ejecuta_query("SET ISOLATION TO $sql_add");
    }
    
    function wait() {
        $this->conexion = (object)$this->conexion;
    
        switch ($this->conexion->motor) {
            case 'sqlsrv':
                $this->ejecuta_query("WAITFOR DELAY '00:00:02'");
                break;
    
            case 'mysql':
                // Utilizar SELECT SLEEP() para lograr un retraso de 2 segundos
                //$this->ejecuta_query("SELECT SLEEP(2)");
                break;
    
            default:
                // Manejar otros motores de base de datos si es necesario
                break;
        }
    }
    
    function notWait(){
        $this->ejecuta_query("SET LOCK MODE TO NOT WAIT");
    }
    function getConn($column){
        return $this->conexion->$column;
    }
    function guardaLog($accion, $antes, $despues, $solicitud, $tabla=""){
        $usuario=$_SESSION['usuario'];
        $sql_guarda_log="INSERT INTO logs(id_solicitud, tabla, accion, usuario, fecha_grab, antes, despues) VALUES('$solicitud', '$tabla', '$accion', '$usuario', current, '$antes', '$despues')";
        $this->ejecuta_query($sql_guarda_log);
    }
    /**
     * [getSequence Ontiene el consecutivo de la tabla ]
     * @param  [type] $sequence [Nombre de la Tabla ]
     * @return [type]           [Consecutivo]
     */
    function getSequence($sequence){

        $consulta="execute procedure spUpdateSequence('$sequence')";
        $sequence=$this->ejecuta_sp($consulta);
        return $sequence;
    }

    function core_log_programa($modulo){

		$usuario = $_SESSION['usuario'];
	
	   $consulta = "SELECT count(distinct programa)conteo FROM logs_programas WHERE programa ='$modulo' AND  usuario = '$usuario' ";
		$respuesta = $this->lee_uno($consulta);
			
	   	if ($respuesta->conteo == 0) {
			$insert = "INSERT INTO logs_programas(usuario,programa,f_ingreso,nro_ingresos,tipo_programa) VALUES('$usuario','$modulo',NOW(),1,'new_core')";
			$this->ejecuta_query($insert);
	    }else{
	        $update = "UPDATE logs_programas SET nro_ingresos = nro_ingresos+1, f_ingreso = NOW() WHERE usuario ='$usuario' and programa ='$modulo'";
	       $this->ejecuta_query($update);
	
	 	}
		
	}
    
    function guardar_log_errores($tipo,$ruta,$funcion,$parametros, $desc_error=''){

    $ruta       = str_replace(PATH_DIRECTORIO."/","",$ruta);
    $tipo       =$_SERVER['SERVER_ADDR'];
    $usuario=$_REQUEST['usuario']??'';

    $queri = "insert into log_errores (usuario, fecha, programa, funcion, tipo, descripcion, desc_error) 
    values (:usuario, current, :ruta,:funcion, :tipo, :parametros,:desc_error)";

    $resu = $this->ejecuta_prepare($queri,[
        ':usuario'=>$usuario,
        ':ruta'=>$ruta,
        ':funcion'=>$funcion,
        ':tipo'=>$tipo,
        ':parametros'=>$parametros,
        ':desc_error'=>$desc_error,
        ],'rowid');
        return $resu;
    }
    
}