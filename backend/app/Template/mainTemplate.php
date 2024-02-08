<?php

class Template {
	public $modulo;
	public $template="default/default.html";
	public $VistaModulo='vista.html';
	private $arrayXajaxDefault = array ("xajax_cargando()");
	private $arrayHead = array ();
	private $arrayBody = array ();
	private $arrayFooter = array ();
	private $objxAjax;

	public function __construct(){
	}
	public function AgregarCSS($file){
		if(file_exists($file)){
			$templateCSS="<link rel='stylesheet' type='text/css' href='$file'/>";
			$this->AgregarHead($templateCSS);
			return true;
		}else{
			return false;
		}
	}
	public function AgregarJS($file){
		$templateCSS="<script type='text/javascript' src='$file?".date("Ymdh")."'></script>";
		$this->AgregarFooter($templateCSS);
	}

	private function AgregarHead ($html){
		$this->arrayHead[]=$html;
	}
	private function AgregarFooter ($html){
		$this->arrayFooter[]=$html;
	}
	
	static public function getFile ($file){
        $data = "";
		$link = @fopen($file,'r');
		if ($link){
			$size=filesize($file);
			if($size==0) $size=1;
			$data = fread($link,$size);
			fclose($link);
		}
		return $data;
	}
	static public function putFile ($file,$data,$method='a+'){
		$link = @fopen($file,$method);
		if ($link){
			$data = fputs($link,$data);
			fclose($link);
		}
		@chmod($file, 0777);
	}
	static public function makeDir($dir){
		if(!file_exists($dir)){
			if(@mkdir($dir)!==false){

			}else{
				die("Problema con permisos en las  carpetas, verifique");
			}

			@chmod($dir, 0777);
		}
	}
	public function rmDir($dir){
		if(file_exists($dir)){
			$files = glob($dir . '/*', GLOB_MARK);
		    foreach ($files as $file) {
		        if (is_dir($file)) {
		            self::rmDir($file);
		        } else {
		            unlink($file);
		        }
		    }
			@rmdir($dir);
		}
	}

	public function cargarTemplate(){
		
		$mPrograma=new programaModel($this->modulo);

		#trae template
		$fileTemplate = TEMPLATE_PATH.$this->template;
		$dataTemplate=$this->getFile($fileTemplate);
	
		#trae vista del modulo
		$fileVista = MODULE_PATH.$mPrograma->menu."/".$this->modulo."/".$this->VistaModulo;
		$dataVista=$this->getFile($fileVista);

		if($dataTemplate=='') $dataTemplate=$dataVista;
		
		#Carga librerias que requiere el modulo
		foreach ($mPrograma->componentes as $componente) {
		    $componente=trim($componente);
			$componenteJS=COMPONENT_PATH."$componente/config.ini";	
			if(file_exists($componenteJS)){
				$config=parse_ini_file($componenteJS);
				foreach ($config['includeJS'] as $archivo) {
					if($archivo!='')
						$this->AgregarJS(COMPONENT_PATH.$componente."/".$archivo);	
				}
				foreach ($config['includeCSS'] as $archivo) {
					if($archivo!=''){
						$this->AgregarCSS(COMPONENT_PATH.$componente."/".$archivo);
					}
				}
				
			}
		}

		$fileCSS = MODULE_PATH.$mPrograma->menu."/".$this->modulo."/estilo.css";
		if(file_exists($fileCSS)){
			$this->AgregarCSS($fileCSS);
		}
		$ControllerJS = MODULE_PATH.$mPrograma->menu."/".$this->modulo."/controlador.js";
		if(file_exists($ControllerJS)){
			$this->AgregarJS($ControllerJS);
		}
		
		if(isset($GLOBALS['objAjax'])){
			$//this->AgregarHead($GLOBALS['objAjax']->getJavascript(PATH_XAJAX_JS));	
			/*if($programa->xajaxDefault!=''){
				$this->arrayXajaxDefault[]="xajax_".$programa->xajaxDefault;
			}*/
			$dataTemplate=str_replace("<body","<body onload='".implode(";",$this->arrayXajaxDefault)."'", $dataTemplate);
		}
		


		$dataTemplate=str_replace("</modulo>", $dataVista."</modulo>", $dataTemplate);
		$dataTemplate=str_replace("</head>", implode("\n", $this->arrayHead)."</head>", $dataTemplate);
		$dataTemplate=str_replace("</body>", implode("\n", $this->arrayFooter)."</body>", $dataTemplate);
		$config=$_SESSION['config'];

		foreach ($config['general'] as $key => $value) {
			$dataTemplate=str_replace("[[".strtoupper($key)."]]", $value, $dataTemplate);
		}
		foreach ($config['apariencia'] as $key => $value) {
			$dataTemplate=str_replace("[[".strtoupper($key)."]]", $value, $dataTemplate);
		}

		if (isset($_GET['unit_test']) && $_GET['unit_test']=='si') {
			$value="<link rel='stylesheet' href='https://code.jquery.com/qunit/qunit-2.3.2.css'>\n
					<div id='qunit' style='padding:80px;'></div>
  					<div id='qunit-fixture'></div>
  					<script src='https://code.jquery.com/qunit/qunit-2.3.2.js'></script>
  					<script src='".MODULE_PATH.$mPrograma->menu."/".$this->modulo."/test.js?".date("His")."'></script>";
			$dataTemplate=str_replace("[[PRUEBAS]]", $value, $dataTemplate);
		}else{
			$dataTemplate=str_replace("[[PRUEBAS]]", '', $dataTemplate);
		}
		
	
		echo $dataTemplate;
	}  
}