<?php
date_default_timezone_set('America/Caracas');
setlocale(LC_TIME, 'es_ES.UTF-8');
require_once(__DIR__.'/../app/controlador/sesion.php');
require_once(__DIR__ . "/../config/config.php");

require_once(__DIR__ . "/../routes/rutas.php");
$pagina = isset($_GET['pagina']) ? $_GET['pagina'] : "inicio";
manejarRuta($pagina);

?>