<?php
session_start();
include_once("config/config.php");

include_once(APP_MODELS."mainModel.php");
include_once(APP_BUSINESS."mainBusiness.php");
include_once(APP_CONTROLLERS."mainController.php");
include_once(APP_TEMPLATE."mainTemplate.php");


//sanitizar parametros GET
$Core=new  mainController();
$_GET=$Core->sanitize($_GET);

if(isset($_GET['modulo'])){
    
	include_once(MODULE_PATH."/1/programa/modelo.php");//clase controladora de aplicaciones
	$mPrograma= new programaModel($_GET['modulo']);
	$menu=$mPrograma->menu;

	if($mPrograma->autenticado=='S' && $_SESSION['usuario']==''){
        $_GET['modulo']=''; 
		echo "<script>document.location='index.php?modulo=login&redir=".base64_encode($_SERVER['REQUEST_URI'])."'</script>";
        die("");
	}


	#Valida permiso al modulo
	if(!$mPrograma->getPermiso('',false) && $mPrograma->programa!='INICIO' && $mPrograma->autenticado=='S'){ 
		echo "<script>alert('".$_GET['modulo'].": Modulo no existe. ".$mPrograma->descripcion."');document.location='index.php?modulo=inicio'</script>";
		die();
	}
    
	$controlador=ROOT_PATH."/modules/$menu/".$_GET['modulo']."/controlador.php";
	$modelo=ROOT_PATH."/modules/$menu/".$_GET['modulo']."/modelo.php";
    $negocio=ROOT_PATH."/modules/$menu/".$_GET['modulo']."/negocio.php";

	###---MODELO
	if(file_exists($modelo)){
		include_once($modelo);
	}

    ###---BUSINESS
    if(file_exists($negocio)){
        include_once($negocio);
    }

	###---CONTROLADOR
	if(file_exists($controlador)){
		include_once($controlador);
        $modulo = isset($_GET['modulo']) ? $_GET['modulo'] : '';
        // Limpiar el valor para evitar problemas de seguridad (usar el método adecuado según el contexto)
        $modulo = htmlspecialchars($modulo);
        $controllerName = $modulo . 'Controller';
        $controller = new $controllerName;
        ###---VISTA
        $template=new Template();
        $template->modulo=$_GET['modulo'];
        $template->template=$controller->plantilla;
        $template->cargarTemplate();
        $mPrograma->core_log_programa(strtoupper($_GET['modulo']));

	}else{
        die('Controlador no existe');
    }

	#Redireccion CORE
	if(($_GET['redirect']??'') != ''){
		$url=explode("/",substr(base64_decode($_GET['redirect']),1));
		if($url[1]=='index.php' && count($url)==2){
			//do nothing
		}else{
			echo "<script>setTimeout(function(){document.location='".base64_decode($_GET['redirect'])."';}, 1000);</script>";
            die("");
		}
		
	}
}else{
	if($_SESSION['usuario']==''){
		header("Location: index.php?modulo=login&redir=".base64_encode($_SERVER['REQUEST_URI']));
	}
}