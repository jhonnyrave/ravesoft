class [[programa]]_xajax extends xajaxController
{
	function __construct(){

	}

	function funcionXajax(){
		$modelo= new [[programa]]("myDato2");
		$modelo->funcionModelo();
		$resultado=$modelo->atributo3;
		$this->Alert($resultado);
		return $this->response;
	}
	[[funciones]]
}
if(!function_exists("inicializar")){
	function inicializar(){
		$controlador = new [[programa]]_xajax();
		return $controlador;
	}
}
