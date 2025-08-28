<?php
$routes = [
    "inicio" => "inicio",
    "principal" => "principal",
    "usuarios" => "usuarios",
    "roles" => "roles",
    "bitacora" => "bitacora",
    "recuperar" => "recuperar",
    "productos" => "productos"
];

function manejarRuta($ruta)
{
    global $routes;

    if (array_key_exists($ruta, $routes)) {
        $controlador = $routes[$ruta];
        if (!isset($_SESSION['modulo_actual']) || $_SESSION['modulo_actual'] !== $ruta) {
            $_SESSION['registrar_ingreso_modulo'] = true;
        } else {
            $_SESSION['registrar_ingreso_modulo'] = false;
        }
        $_SESSION['modulo_actual'] = $ruta;
        require_once(__DIR__ . "/../app/controlador/" . $controlador . ".php");

        $obj = new $controlador($controlador);
        $obj->procesarSolicitud();
    } else {
        require_once(__DIR__ . "/../app/vista/complementos/error.php");
    }
}
