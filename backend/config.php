<?php

date_default_timezone_set('America/Bogota');
define('ROOT_PATH',	".");
define('MODULE_PATH',	ROOT_PATH.'/modules/');
define('STATIC_PATH',	ROOT_PATH.'/public/static/');
define('APP_PATH',	ROOT_PATH.'/app/');
define('APP_CONTROLLERS',	ROOT_PATH.'/app/Controllers/');
define('APP_BUSINESS',	ROOT_PATH.'/app/Business/');
define('CORE',	ROOT_PATH.'/app/Core/');
define('APP_MODELS',	ROOT_PATH.'/app/Models/');
define('APP_TEMPLATE',	ROOT_PATH.'/app/Template/');
define('COMPONENT_PATH',	STATIC_PATH.'componentes/');
define('TEMPLATE_PATH',	STATIC_PATH.'template/');
define ('BASE_URL_PATH', 'http://'.dirname($_SERVER['HTTP_HOST'].''.$_SERVER['SCRIPT_NAME']).'/');

$path_raiz = $_SERVER ["DOCUMENT_ROOT"]."/";
define("DIRECTORIO","matrix");
define("PATH_RAIZ",$path_raiz);
define("PATH_DIRECTORIO",PATH_RAIZ.DIRECTORIO);

$file=CORE.$_SERVER['SERVER_NAME'].".ini";
if(!isset($_SESSION['config'])){
	if(file_exists($file)){
		$_SESSION['config']=parse_ini_file($file,true);
	}
}