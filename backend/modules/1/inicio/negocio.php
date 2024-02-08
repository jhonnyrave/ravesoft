<?php
class inicioBusiness extends mainBusiness{ 
    
    public function __construct () {
    	$this->Model = new inicioModel();
        $this->Model->wait();
    }
    
    public function funcionNegocio(){
		return $this->Model->funcionModelo("dato en negocio");
	}
}