<?php
include_once(__DIR__ . "/sesion.php");
require_once(__DIR__ . "/../../config/config.php");
require_once(__DIR__ . "/../../servicios/servicios_bitacora.php");
function bitacora($mensaje)
{
    $servicio_b = new servicios_bitacora();
    $registro = [
        'modulo' => _MD_Cerrar_,
        'accion' => $mensaje,
        'fecha' => date('Y-m-d'),
        'hora' => date('H:i:s'),
        'usuario' => $_SESSION['id']
    ];
    $servicio_b->registro_B($registro);
}
bitacora('Cerro sesion de forma exitosa');
session_destroy();
header("location:/Proyecto_Plantilla/public/");
