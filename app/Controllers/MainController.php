<?php
class mainController {
	public $debug = 'N';
    public $tiempoDebug;
    private $rutaComponente;
    public $medio = "servicio";
    public $response;
    public $rutaTransaccion;

    public function __construct($medio = "servicio")
    {
        die('controller');
        if ($medio === '') $medio = 'servicio';
        $this->tiempoDebug = microtime(true);
        $this->medio = $medio;
        
        if ($this->debug === 'S') {
            $this->fileLog("************\n" . date("Y-m-d H:i"));
            $this->fileLog("Request:\n" . $this->prettyPrint(json_encode($_REQUEST)));
        }
    }

    public function cargarComponente($componente)
    {
        $this->rutaComponente = CORE . "/componentes/$componente/";
        $controlador = $this->rutaComponente . "controlador.php";
        $modelo = $this->rutaComponente . "modelo.php";

        if (file_exists($controlador)) {
            include_once $controlador;
        } else {
            die("Componente $componente no encontrado");
        }

        if (file_exists($modelo)) {
            include_once $modelo;
        }

        $clase = "C" . ucfirst(strtolower($componente));
        $obj = new $clase('local');
        return $obj;
    }

    public static function cargarModulo($modulo)
    {
        $modulo = strtolower($modulo);
        $mPrograma = new programaModel($modulo);
        $menu = $mPrograma->menu;
        $controlador = ROOT_PATH . "/modulos/$menu/$modulo/controlador.php";
        $modelo = ROOT_PATH . "/modulos/$menu/$modulo/modelo.php";
        $negocio = ROOT_PATH . "/modulos/$menu/$modulo/negocio.php";

        if (file_exists($controlador)) {
            include_once $controlador;
        } else {
            if (!class_exists($modulo)) {
                die("Componente $modulo no encontrado.. $controlador $modelo");
            }
        }

        if (file_exists($modelo)) {
            include_once $modelo;
        }

        if (file_exists($negocio)) {
            include_once $negocio;
        }

        $clase = "C" . strtolower($modulo);
        $obj = new $clase('local');
        return $obj;
    }

    public function cargarModelo($modulo)
    {
        $modulo = strtolower($modulo);
        $mPrograma = new programaModel($modulo);
        $menu = $mPrograma->menu;
        $modelo = ROOT_PATH . "/modulos/$menu/$modulo/modelo.php";

        if (file_exists($modelo)) {
            include_once $modelo;
        } else {
            die("Componente $modulo no encontrado.. $modelo");
        }
    }

    public function cargarTransaccion($transaccion)
    {
        $transaccion = ucfirst(strtolower($transaccion));
        $this->rutaTransaccion = CORE . "/transacciones/";
        $controlador = $this->rutaTransaccion . "$transaccion.php";

        if (file_exists($controlador)) {
            include_once $controlador;
        } else {
            die("Transaccion $transaccion no encontrado");
        }

        $clase = $transaccion;
        $obj = new $clase($transaccion);
        return $obj;
    }

    public function response($resultado)
    {
        if ($this->debug === 'S') {
            $this->fileLog("Response:\n" . $this->prettyPrint(json_encode($resultado)));
            $this->fileLog("{$_REQUEST['modulo']} -> {$_REQUEST['metodo']} - Tiempo:" . number_format($this->diffMicrotime2($this->tiempoDebug, microtime(true)), 4));
        }

        if ($this->medio === 'local') {
            $this->response = $resultado;
        }

        if ($this->medio === 'servicio') {
            echo json_encode($resultado);
            die("");
        }
    }

	public function filter($variable, $tipo){
		switch ($tipo) {
			case 'email':
				$variable=filter_var($variable,FILTER_SANITIZE_EMAIL);
				break;
			case 'int':
				$variable=filter_var($variable,FILTER_SANITIZE_NUMBER_INT);
				break;
			case 'float':
				$variable=filter_var($variable,FILTER_SANITIZE_NUMBER_FLOAT);
				break;
			case 'string':
				$variable=filter_var($variable, FILTER_SANITIZE_STRING);
                /*$array1 = array('`','"','”','Ñ','ñ','ã','Ã‘','‘','„','ç','Ç','á','à','ä','é','è','ë','í','ì','ï','ó','ò','ö','ú','ú','ü','Á','À','Ä','É','È','Ë','Í','Ì','Ï','Ó','Ò','Ö','Ú','Ù','Ü','*','+','º','ª','°','·',':','/','\\','(',')','$',';','&','=','¿','¡','[',']','{','}','´',',','½','Ÿ','Ð','~','±','!','­','Û','¨','¬','¨','?','Ð','~','<','>','   ','  ','','¥','Ã','','','','Â','','','','�');
                $array2 = array(' ',' ','O','N','n','N','N', '', 'N','A','A','a','A','A','e','E','E','i','I','I','o','O','O','u','u','U','A','A','A','E','E','E','I','I','I','O','O','O','U','U','U',' ',' ',' ',' ',' ',' ',' ',' ',' ', ' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ',' ', '',' ',' ',' ','N',' ','N',' ',' ','U',' ','', ' ',' ','N',' ',' ',' ',  ' ', ' ','','N','N','E','O','I','','','','','A');
                $variable=str_replace($array1, $array2, $variable);*/
                break;
			case 'sql':
				$variable  = preg_replace(array("/(select )/i","/(delete )/i","/(insert )/i","/(truncate )/i","/(drop )/i","/(union )/i","/(create )/i"),  '', $variable);	
				break;
			case 'url':
				if(filter_var($variable, FILTER_VALIDATE_URL, FILTER_FLAG_PATH_REQUIRED)==true);
					$variable=filter_var($variable, FILTER_VALIDATE_URL);

				break;
			case 'raw':
				$variable=filter_var($variable, FILTER_UNSAFE_RAW);
				break;
			default:
				die("Sanitize:Tipo=$tipo No existe");
				break;

		}
		return $variable;
	}

    public function diffMicrotime2($mtOld, $mtNew)
    {
        list($oldUsec, $oldSec) = explode(' ', $mtOld);
        list($newUsec, $newSec) = explode(' ', $mtNew);

        $oldMt = ((float)$oldUsec + (float)$oldSec);
        $newMt = ((float)$newUsec + (float)$newSec);

        return $newMt - $oldMt;
    }


	public function sanitize($parametros){
		if(isset($_POST['sanitize'])){
			$sanitize=$_POST['sanitize'];
			foreach ($sanitize as $key => $tipo) {
				if(!is_array($tipo)){
					if(isset($parametros[$key])) $parametros[$key]=$this->filter($parametros[$key],$tipo);
				}
			}
		}else{
			$sanitize=array();
		}
		if(is_array($parametros)){
			$campo_sin_sanitizar=array_diff(array_keys($parametros), array_keys($sanitize));
			foreach ($campo_sin_sanitizar as $key => $campo){
				if(!is_array($parametros[$campo])){
					$parametros[$campo]=$this->filter($parametros[$campo],'string');
					$parametros[$campo]=$this->filter($parametros[$campo],'sql');
				}else{
					$parametros[$campo]=$this->sanitize($parametros[$campo]);
				}
			}
		}
		return $parametros;
	}

	public function getFile ($file){
		$link = @fopen($file,'r');
		if ($link){
			$size=filesize($file);
			if($size==0) $size=1;
			$data = fread($link,$size);
			fclose($link);
		}
		return $data;
	}

	public function coreLogPrograma($modulo, $parametros)
    {
        $clase = "C" . strtolower($modulo);
        $obj = new $clase('local');
        $obj->coreLogPrograma($modulo, $parametros);
    }

    public function fileLog($nota)
    {
        $usuario = $_SESSION['usuario'] ?? '';
        $programa = $_REQUEST['modulo'] ?? '';
        $archivoPlano = fopen("querys/" . $programa . "_" . $usuario . ".log", "a+");

        if ($archivoPlano) {
            fwrite($archivoPlano, $nota . "\n");
            fclose($archivoPlano);
        }
    }

	public function diff_microtime2($mt_old,$mt_new){
	    list($old_usec, $old_sec) = explode(' ',$mt_old);
	    list($new_usec, $new_sec) = explode(' ',$mt_new);
	    $old_mt = ((float)$old_usec + (float)$old_sec);
	    $new_mt = ((float)$new_usec + (float)$new_sec);
	    return $new_mt - $old_mt;
	}
	public function prettyPrint( $json ){
	    $result = '';
	    $level = 0;
	    $in_quotes = false;
	    $in_escape = false;
	    $ends_line_level = NULL;
	    $json_length = strlen( $json );

	    for( $i = 0; $i < $json_length; $i++ ) {
	        $char = $json[$i];
	        $new_line_level = NULL;
	        $post = "";
	        if( $ends_line_level !== NULL ) {
	            $new_line_level = $ends_line_level;
	            $ends_line_level = NULL;
	        }
	        if ( $in_escape ) {
	            $in_escape = false;
	        } else if( $char === '"' ) {
	            $in_quotes = !$in_quotes;
	        } else if( ! $in_quotes ) {
	            switch( $char ) {
	                case '}': case ']':
	                    $level--;
	                    $ends_line_level = NULL;
	                    $new_line_level = $level;
	                    break;

	                case '{': case '[':
	                    $level++;
	                case ',':
	                    $ends_line_level = $level;
	                    break;

	                case ':':
	                    $post = " ";
	                    break;

	                case " ": case "\t": case "\n": case "\r":
	                    $char = "";
	                    $ends_line_level = $new_line_level;
	                    $new_line_level = NULL;
	                    break;
	            }
	        } else if ( $char === '\\' ) {
	            $in_escape = true;
	        }
	        if( $new_line_level !== NULL ) {
	            $result .= "\n".str_repeat( "\t", $new_line_level );
	        }
	        $result .= $char.$post;
	    }

	    return $result;
	}

	public function logError(Exception $exception){
        $trace=($exception->getTraceAsString());
        $descError=$exception->getMessage();
        die($descError);
    }
}