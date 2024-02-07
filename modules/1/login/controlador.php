<?php
class loginController extends mainController{
    private $Business;
    public $plantilla="default/blank.html";
    
    function autenticar($parametros) {
        try {
            $username = $parametros['usuario'];
            $password = $parametros['password'];
            
            $this->Business = new loginBusiness($username);
            $this->Business->logOut();
    
            if ($this->Business->validarUsuarioExiste()) {
                if ($this->Business->validarContrasena($password)) {
                    $token = $this->Business->crearToken($password);
                    $this->Business->grabarToken($token);
                    $this->Business->iniciarSession();
    
                    $resultado = [
                        'estado' => 'Exitoso',
                        'info' => [
                            'usuario' => $username,
                            'token' => $token,
                            'nombre' => $this->Business->Model->nombre,
                            'apellidos' => $this->Business->Model->apellidos,
                            'nit' => $this->Business->Model->nit
                        ],
                        'referrer' => isset($parametros['redir']) ? base64_decode($parametros['redir']) : ''
                    ];
    
                } else {
                    $resultado = [
                        'estado' => 'Error',
                        'mensaje' => 'Contraseña no válida'
                    ];
                }
            } else {
                $resultado = [
                    'estado' => 'Error',
                    'mensaje' => 'Usuario no existe'
                ];
            }
 
            $this->response($resultado);
        } catch (Exception $e) {
            // Manejar excepciones según sea necesario
            $this->response(['estado' => 'Error', 'mensaje' => 'Error en la autenticación']);
        }
    }
    
   
    function logOut(){
    	$this->Business = new loginBusiness($_SESSION['usuario']);
        $this->Business->logOut();
        $this->response('');
    }
    function ValidarMultipleDB(){
        $config=$_SESSION['config']['database'];
        $this->response(array("multiple"=>$config['multiple'],"defecto"=>$config['server']));
    }
}