<?php
class loginModel extends mainModel {  
	public $id_usuario;  
	public $usuario;  
	public $password;  
	public $nombre;
	public $apellidos;
	public $nit;
	public $correo;
	public $dominio;
	public $token;
	public $existe='N';  
	public $params = []; 
	                                                                    
	public function __construct()  
	{  
		$this->Conectarse();
	}

	public function getUsuario(){
	
		$this->params = [];
		$query = "SELECT id, usuario, usr_pass as password, nombre, apellidos, correo, nit 
				FROM core_usuarios 
				WHERE usuario = :usuario AND estado != 'I'";

		$params[':usuario'] = $this->usuario; 
		$data = $this->lee_prepare($query, $params);
		return $data;
	}
	public function grabarToken(){
		$m=$this->lee_uno("SELECT id,token FROM core_token WHERE id_usuario='{$this->id_usuario}' and dominio='{$this->dominio}'");
		if ($m->id>0){
			$this->ejecuta_query("UPDATE core_token set token='{$this->token}', vigencia=CURDATE(), fecha_hora=CURRENT_TIMESTAMP where id='{$m->id}' and id_usuario=(select id from core_usuarios where usuario='{$this->usuario}' and usr_pass='{$this->password}' and estado !='I')");	
		}else{
			$this->ejecuta_query("INSERT INTO core_token(id_usuario, dominio, token, vigencia, fecha_hora) VALUES('{$this->id_usuario}', '{$this->dominio}', '{$this->token}', CURDATE(), CURRENT_TIMESTAMP)");	
		}
		
	}
	public function getToken(){
		$m=$this->lee_uno("SELECT token FROM core_token WHERE id_usuario='{$this->id_usuario}' and dominio='{$this->dominio}' and vigencia>=CURDATE()");
		return $m->token;
	}
}  