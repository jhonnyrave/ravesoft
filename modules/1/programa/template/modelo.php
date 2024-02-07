<?php
class [[programa]] extends mainModel
{
    public function __construct($parametro = "")
    {
        parent::__construct();
        $this->Conectarse($parametro);
    }

    public function getCamCupo()
    {
        $data = $this->lee_uno("SELECT valor from parametros where variable='nro_cam_cupo'");
        return $data->valor;
    }

    public function getNumeroHijas()
    {
        $data = $this->lee_uno("SELECT valor from parametros where variable='nro_cam_teneg'");
        return $data->valor;
    }
}