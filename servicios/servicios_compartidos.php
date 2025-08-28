<?php
require_once(__DIR__.'/../app/modelo/roles.php');

class servicios_compartidos{
    private $obtener_roles;

    public function __construct()
    {
        $this->obtener_roles= new modelo_roles();
    }

    public function obtener_roles(){
        try{
            return $this->obtener_roles->consultar();
        }catch(Exception $e){
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
            return $resultado;
        }
    }
}