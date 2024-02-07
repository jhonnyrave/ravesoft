<?php
class [[programa]]Bussines extends mainBusiness{
    public $Model;

    public function __construct () {
        parent::__construct();
        $this->Model = new [[programa]]();
    }

    /**
     * Metodo de ejemplo para mostrar el funcionamiento de las capas del CORE
     *
     * @param $myVariable int Numero entero para realizar el ejemplo
     * @return float Ejemplo de campo calculado
     * @throws Exception
     */
    public function metodoNegocio($myVariable){
        /** @var int $myVariabledesdeModel ejecuta metodo del modelo para traer información a la capa de negocios */
        $myVariabledesdeModel= $this->Model->getCamCupo();
        $numeroHijas= $this->getNumeroHijas();
        if( (int) $myVariabledesdeModel==0) {
            throw new Exception('Parametro getCamCupo No valido (0)',333);
        }
        return ($myVariable * $numeroHijas) / $myVariabledesdeModel;
    }

    /**
     * Metodo ejemplo para consumir una regla de negocio desde otra
     *
     * @return mixed
     */
    public function getNumeroHijas(){
        return $this->Model->getNumeroHijas();
    }
}