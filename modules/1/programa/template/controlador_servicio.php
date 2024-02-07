define('exceptionMode','throw');

$plantilla="default/default.html";
if(!class_exists("servicio")){
    class_alias('c[[programa]]', 'servicio');

}
class c[[programa]] extends mainController{
    private $Business;

    public function __construct ($medio="") {
        try{
            parent::__construct($medio);
            $this->Business = new [[programa]]Bussines();
        }catch (Exception $e){
            $this->response($this->logError($e));
        }
    }

    /**
     * @param $parametros array que llegan desde el formulario o servicio REST
     */
    function funcionRest($parametros){
        try{
            /** @var int $myVariable Sanitización de la variable desde el controlador*/
            $myVariable = $this->filter($parametros['campo1'], 'int');

            /** @var int $resultado ejecuta el Metodo de la capa de negocio*/
            $resultado = $this->Business->metodoNegocio($myVariable);

            /** @var object $this retorna la informacion al servicio REST o controlador JS*/
            $this->response($resultado);
        }catch (Exception $e){
            $this->response($this->logError($e));
        }

    }
}