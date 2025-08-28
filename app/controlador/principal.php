<?php
class principal {
    private $pagina;

    public function __construct($pagina) {
        $this->pagina = $pagina;
    }

    public function procesarSolicitud() {

        if (!is_file(__DIR__ . "/../modelo/" . $this->pagina . ".php")) {
            require_once(__DIR__ . "/../app/vista/complementos/error.php");
            exit;
        }
        if (!empty($_SESSION['id'])) {
            if (is_file(__DIR__ . "/../vista/" . $this->pagina . ".php")) {
                require_once(__DIR__ . "/../controlador/cookie.php");
                require_once(__DIR__ . "/../vista/" . $this->pagina . ".php");

                if (!empty($_SESSION['error'])) {
                    $mensajeError = json_encode($_SESSION['error']);
                    echo "<script>window.mensajeError = $mensajeError;</script>";
                    unset($_SESSION['error']);
                }
            } else {
                require_once(__DIR__ . "/../app/vista/complementos/error.php");
            }
        } else {
            header("Location: /Proyecto_Plantilla/public/");
            exit;
        }
    }
}
