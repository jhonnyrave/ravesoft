<?php
session_start();

include_once("config/config.php");
include_once(APP_MODELS."mainModel.php");
die('holaaaa');
include_once(APP_BUSINESS."mainBusiness.php");
include_once(APP_CONTROLLERS."mainController.php");
include_once(APP_TEMPLATE."mainTemplate.php");


// Sanitizar parámetros GET
$Core = new mainController();

$_REQUEST = $Core->sanitize($_REQUEST);

$tipo = isset($_REQUEST['tipo']) ? $_REQUEST['tipo'] : '';
$modulo = isset($_REQUEST['modulo']) ? $_REQUEST['modulo'] : '';
$metodo = isset($_REQUEST['metodo']) ? $_REQUEST['metodo'] : '';
$token = isset($_REQUEST['token']) ? $_REQUEST['token'] : '';
$parametros = isset($_REQUEST['parametros']) ? $_REQUEST['parametros'] : '';
$user = isset($_REQUEST['user']) ? $_REQUEST['user'] : '';

include_once MODULE_PATH."1/programa/modelo.php";
include_once MODULE_PATH."1/login/negocio.php";
include_once MODULE_PATH."1/login/modelo.php";



$mPrograma = new programaModel($modulo);
$menu = $mPrograma->menu;

if ($user !== '') {
    $_SESSION['usuario'] = $user;
}

if ($mPrograma->autenticado == 'S') {
    if ($token == '') {
        header(':', true, '401');
        die("No envió token - $modulo::$metodo");
    } else {
        $mLogin = new loginBusiness($_SESSION['usuario']);
        $mLogin->getToken();

        if ($token !== $mLogin->token) {
            $mLogin->logOut();
            die("Token incorrecto o caducado");
        }
    }
}

if ($menu !== '') {
    $fileController = MODULE_PATH.$menu."/".$modulo."/controlador.php";
    $fileBusiness = MODULE_PATH.$menu."/".$modulo."/negocio.php";
    $fileModel = MODULE_PATH.$menu."/".$modulo."/modelo.php";
} else {
    print_r($_REQUEST);
    die("-");
}

// Aplica cuando se llama un componente CORE
if ($tipo == 'CORE') {
    $fileController = CORE."componentes/$modulo/controlador.php";
    $fileModel = CORE."componentes/$modulo/modelo.php";
    $clase = $modulo."Controller";
} else {
    $clase = $modulo."Controller";
}

if (file_exists($fileController)) {
    include_once $fileController;
} else {
    echo $modulo;
}

if (file_exists($fileModel)) {
    include_once $fileModel;
}

if (file_exists($fileBusiness)) {
    include_once $fileBusiness;
} else {
    die($fileBusiness);
}

if (class_exists($clase)) {
    $rest = new $clase();
    $parametros = $rest->sanitize($parametros);
    $rest->{$metodo}($parametros);
}