<?php
class loginBusiness extends mainBusiness{ 
	public $Model;
    public $token;

    public function __construct ($usuario) {
        parent::__construct();
    	$this->Model = new loginModel();
    	$this->Model->wait();
    	$this->Model->usuario=strtolower(trim($usuario));

        if($this->Model->usuario!=''){
        	$data = $this->Model->getUsuario();
			if (!empty($data)) {
				$usuario = $data[0];
				$this->Model->existe = 'S';
				$this->Model->id_usuario = $usuario->id;
				$this->Model->password = $usuario->password;
				$this->Model->nombre = mb_convert_encoding($usuario->nombre, 'UTF-8', 'ISO-8859-1');
				$this->Model->apellidos = mb_convert_encoding($usuario->apellidos, 'UTF-8', 'ISO-8859-1');
				$this->Model->correo = mb_convert_encoding($usuario->correo, 'UTF-8', 'ISO-8859-1');
				$this->Model->nit = $usuario->nit;
			}
		}
    }

    public function crearToken($password) {
		
		// Concatenar datos y utilizar password_hash para generar el token
		$data = $this->Model->usuario . $password . rand() . time();
		$token = password_hash($data, PASSWORD_DEFAULT);
	
		return $token;
	}

	public function getToken(){
		$this->Model->dominio=$_SERVER['SERVER_NAME'];
		$token=$this->Model->getToken();
		$this->token=$token;
	}

	public function grabarToken($token){
		$this->Model->token=$token;
		$this->Model->grabarToken();
	}

	public function iniciarSession(){
		$_SESSION['id_usuario']=$this->Model->id_usuario;
        $_SESSION['usuario']=$this->Model->usuario;
	}

	public function logOut(){
		session_destroy();
        session_start();
	}

	public function	validarUsuarioExiste(){
		if($this->Model->existe=='S'){
			return true;
		}
		return false;
	}
	public function	validarContrasena($password){

		$hashedPassword = $this->Model->password;
        // Verificar si la contraseña proporcionada coincide con el hash almacenado
		if (password_verify($password, $hashedPassword)) {
			$this->Model->dominio=$_SERVER['SERVER_NAME'];
            return true;
        }
		return false;
	}
}