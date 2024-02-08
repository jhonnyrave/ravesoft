<?php
class inicioModel extends mainModel {
	                                                                    
	public function __construct($usuario=""){  
		$this->Conectarse();
	}

	public function GetUsuarios(){
		$consulta = "SELECT usuario,nombre,apellidos,correo from core_usuarios";
		$respuesta = $this->lee_todo($consulta);		
		return $respuesta;
	}
}  