<?php
class programaBusiness extends mainBusiness{ 
    
    public function __construct () {
    	$this->Model = new programaModel();
        $this->Model->wait();
    }
    
    public function getPermisos($programa){
		$data = $this->Model->getPermisos($programa);
        for ($i=0; $i <count($data) ; $i++) { 
			$permisos[$data[$i]->codigo]=[
                'nombre'=>$data[$i]->nombre,
                'sensible'=>$data[$i]->sensible
            ];
		}
	}

    public function getMenuProgramas($programa){

        $menu=$this->Model->getMenuProgramas($programa);
		return $menu; 

    }
}