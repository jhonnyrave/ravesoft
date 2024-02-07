<?php

class MainBusiness
{
    public $Model;
    public $rutaTransaccion;
    public $rutaComponente;

    public function __construct()
    {
    }

    public function cargarBusiness($modulo)
    {
        $modulo = strtolower($modulo);
        $mPrograma = new programaModel($modulo);
        $menu = $mPrograma->menu;
        $negocio = ROOT_PATH . "/modules/$menu/$modulo/negocio.php";
        $modelo = ROOT_PATH . "/modules/$menu/$modulo/modelo.php";

        if (file_exists($negocio)) {
            include_once $negocio;
        } else {
            if (!class_exists($modulo)) {
                die("Modulo $modulo no encontrado.. Business $modelo");
            }
        }

        if (file_exists($modelo)) {
            include_once $modelo;
        }

        $clase = strtolower($modulo) . 'Business';
        $obj = new $clase();
        return $obj;
    }

    public function fileLog($nota)
    {
        $usuario = $_SESSION['usuario'] ?? '';
        $programa = $_REQUEST['modulo'] ?? '';
        $archivoPlano = fopen("querys/$programa" . "_$usuario.log", "a+");

        if ($archivoPlano) {
            fwrite($archivoPlano, $nota . PHP_EOL);
            fclose($archivoPlano);
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
            die("Transaccion $transaccion no encontrada");
        }

        $clase = $transaccion;
        $obj = new $clase($transaccion);
        return $obj;
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

    public function counts($mat)
    {
        return is_array($mat) ? count($mat) : 0;
    }

    public function cambiarArrayKey($matriz, $nuevokey)
    {
        $nuevaMatriz = [];

        foreach ($matriz as $value) {
            $nuevaMatriz[$value->{$nuevokey}] = $value;
        }

        return $nuevaMatriz;
    }

    public function getDateES($fecha)
    {
        $mesesES = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
        $mesesEN = ["January", "February", "March", "April", "May", "June", "July", "August", "September", "October", "November", "December"];
        $fecha = str_replace($mesesEN, $mesesES, $fecha);
        $mesesES = ["Ene ", "Feb ", "Mar ", "Abr ", "May ", "Jun ", "Jul ", "Ago ", "Sep ", "Oct ", "Nov ", "Dic "];
        $mesesEN = ["Jan ", "Feb ", "Mar ", "Apr ", "May ", "Jun ", "Jul ", "Aug ", "Sep ", "Oct ", "Nov ", "Dec "];
        $fecha = str_replace($mesesEN, $mesesES, $fecha);
        return trim($fecha);
    }

    public function getCurl($param)
    {
        if (!isset($param['url'])) {
            throw new Exception('URL no definida en el servicio');
        }

        $curl = curl_init();

        curl_setopt_array($curl, [
            CURLOPT_URL => $param['url'],
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_TIMEOUT => 0,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => $param['request'] ?? 'POST',
            CURLOPT_POSTFIELDS => $param['fields'] ?? '',
            CURLOPT_HTTPHEADER => $param['headers'] ?? ['Content-Type: application/json'],
        ]);

        $response = curl_exec($curl);
        curl_close($curl);
        return $response;
    }

    public function __destruct()
    {
        $this->Model = null;
        unset($this->Model);
    }
}