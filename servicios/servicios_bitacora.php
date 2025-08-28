<?php
require_once(__DIR__.'/../app/modelo/bitacora.php');

class servicios_bitacora{
    private $bitacora;

    public function __construct()
    {
        $this->bitacora= new modelo_bitacora();
    }

    public function registro_B($registro){
        try{
            $this->bitacora->set_modulo($registro['modulo']);
            $this->bitacora->set_accion($registro['accion']);
            $this->bitacora->set_fecha($registro['fecha']);
            $this->bitacora->set_hora($registro['hora']);
            $this->bitacora->set_usuario($registro['usuario']);

            $resultado=$this->bitacora->ingresar();
        }catch(Exception $e){
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
            return $resultado;
        }
    }
}