<?php

class inicioController extends mainController{
	private $Business;
	public $plantilla="default/default.html";


	public function __construct () {
		$this->Business = new inicioBusiness();
	}

	function traerInfoData($parametros){

        try{
            /** @var int $resultado ejecuta el Metodo de la capa de negocio*/
            $id = $parametros['id_entrega'];
          
            $resultado = $this->Business->funcionNegocio();
            /** @var object $this retorna la informacion al servicio REST o controlador JS*/
            $this->response($resultado);
        }catch (Exception $e){
            $this->response($this->logError($e));
        }
    }

}	