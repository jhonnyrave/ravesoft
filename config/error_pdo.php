<?php
/**
 * Muestra errores PDO
 *
 * @author       Alvaro Pulgarin <aepulgarin@lebon.com.co>
 * @copyright    Alvaro Pulgarin 2014-04-16
 * @category     Area
 * @package      Modulo
 * @subpackage   SubModulo
 * @version         1.1
 *
 * 1FIX Se mejora mostrar errores (catch Lee-todo y SP) - se separa ErrorPDO en archivo a parte || jul/6/2015 || JMG-AEP
 * Se agrega traductor de errores para intentar que logistica entienda mejor los errores || jul 24 || JMG
 */

function error_PDO($exception, $query, $respuestaType = '')
{
    
    $respuesta = '';
    $trace = $exception->getTrace();
    for ($a = 0; $a < count($trace); $a++) {
        if ($trace[$a]['function'] == 'lee_todo' || $trace[$a]['function'] == 'ejecuta_query' || $trace[$a]['function'] == 'lee_uno') {
            $error = explode("]", $exception->getMessage());
            $descError = str_replace(array("(SQLPrepare[-", " (SQLExecDirect[-", "(SQLFetchScroll[", "(SQLExecute[-"), "", $error[count($error) - 2]);

            $bk_descError = htmlentities((nl2br($descError)));
            $descError = traducir_error($descError);

            $respuesta = "<hr><div style='border: 1px solid #AFAFAF; padding: 3px; margin: 3px; background-color: #FFFDD1'><span style='color:blue; font-size: 24px'><b>Error: </b>" . $descError . "</span><br><b>Ruta:</b>" . $trace[$a]['file'] . "<br><b>Linea " . $trace[$a]['line'] . "</b><br><b>" . $trace[$a]['function'] . "()</b><br><i><b>Query: </b><pre style='color:red; white-space:normal'>" . $query . "</pre></i></div>";
            if (($_SESSION['debug_lee_todo'] ?? '') == "1" || ($_SESSION['debug_ejecuta_query'] ?? '') == "1") {
                $_SESSION['contenido_debug'] .= "<div class='debug_error'>$respuesta</div></div>";
            }
            $file_ = $trace[$a]['file'];
            $function_ = $trace[$a]['function'];
        }
    }
    $respuesta2 = 'werrorororo';
    if ($respuesta == '') {
        $bk_descError = "Error desconocido";
        /*$respuesta2 = guardar_log_errores(
            'informaw',
            $trace[0]['file'],
            $trace[0]['function'],
            print_r($exception, true) . $query,
            trim($bk_descError)
        );*/
        $mensa_usuario = "<span style='color:red;'><b>CODIGO: $respuesta2</b><br>$bk_descError</span>";
    } else {
        $path2=str_replace('/','\\',APP_PATH);
        $respuesta.=str_replace(['#',APP_PATH,$path2],['<br>#','',''],$exception->getTraceAsString()??'');
        /*$respuesta2 = guardar_log_errores(
            'informaw',
            $file_,
            $function_,
            $respuesta,
            $bk_descError
        );*/
        $mensa_usuario = "<span style='color:red;'><b>CODIGO: $respuesta2</b><br>" . $descError . "</span>";
    }
    if (defined('exceptionMode')) {
       // if (exceptionMode == 'throw') {
            $exception->message2 = $query;
            throw $exception;
       // }
    }

    if ($_SERVER['SERVER_ADDR'] == '127.0.0.1' || $_SERVER['SERVER_NAME'] == 'localhost') {
        if ($respuestaType == 'JSON') {
            echo json_encode(array("success" => "error", "message" => $descError));
            exit;
        } else {
            die($respuesta);//codigos de error solo en produccion
        }
    } else {
        die($mensa_usuario);
    }
}

function traducir_error($traduccion)
{
    // error de BD
    if (strstr($traduccion, 'Could not position within a table')) return "Tabla bloqueada, reintente de nuevo";
    if (strstr($traduccion, 'Serialization failure')) return "Error de bloqueo, reintente de nuevo";
    if (strstr($traduccion, 'physical-order')) return "Tabla bloqueada, reintente de nuevo";
    if (strstr($traduccion, 'A syntax error has occurred')) return "Error de sintaxis en la consulta. Informa a sistemas.";
    if (strstr($traduccion, 'The specified table')) return "Tabla no existe. Informa a sistemas.";
    if (strstr($traduccion, 'String to date conversion error')) return "Fecha mal escrita, verifique (mm/dd/aaaa).";
    if (strstr($traduccion, 'not found in any table in the query')) return "Columna no existe en la tabla. Informe a sistemas.";
    if (strstr($traduccion, 'character to numeric conversion')) return "Error de conversion, verifique los datos ingresados: caracteres en numericos, comillas o saltos de linea.";

    // traducciones de logistica e inventarios
    $traduccion = str_replace("ubica:) REV. MAPA", "). Por favor revise el MAPA DEL BIN. Item sin ubicacion", $traduccion);
    $traduccion = str_replace("> DE 2 UBICACIONES. REV. MAPA", "). Por favor revise el MAPA DEL BIN. Item con mas de 2 ubicaciones", $traduccion);
    $traduccion = str_replace("Negativo", "Reconstruir item (saldo negativo).", $traduccion);
    //$traduccion = str_replace(array("Reconstruir i","Reconstruir"),"Reconstruir item.",$traduccion);

    return $traduccion;
}