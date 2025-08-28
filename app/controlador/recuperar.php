<?php

class recuperar
{
    private $pagina;
    private $servicio_b;

    public function __construct($pagina)
    {
        $this->pagina = $pagina;
        $this->comprobarPermisos();
    }

    private function comprobarPermisos()
    {
        require_once(__DIR__ . "/../../servicios/servicios_bitacora.php");
        $this->servicio_b = new servicios_bitacora();
    }

    public function procesarSolicitud()
    {
        if (!is_file(__DIR__ . "/../modelo/" . $this->pagina . ".php")) {
            require_once(__DIR__ . "/../app/vista/complementos/error.php");
            exit;
        }
        require_once(__DIR__ . "/../modelo/" . $this->pagina . ".php");
        if (is_file(__DIR__ . "/../vista/" . $this->pagina . ".php")) {

            if ($this->comprobarAjax() && !empty($_POST)) {
                $this->manejarSolicitud();
            } else {
                $_SESSION['token'] = bin2hex(random_bytes(32));
                require_once(__DIR__ . "/../vista/" . $this->pagina . ".php");
            }
        } else {
            require_once(__DIR__ . "/../app/vista/complementos/error.php");
        }
    }

    private function comprobarAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    private function manejarSolicitud()
    {
        $accion = isset($_POST["accion"]) ? filter_var($_POST["accion"], FILTER_SANITIZE_SPECIAL_CHARS) : "";
        $obj = new modelo_recuperar();
        switch ($accion) {
            case 'comprobar':
                $this->accion_comprobar($obj);
                break;
            case 'comprobarCodigo':
                $this->accion_codigo($obj);
                break;
            case 'reenviar':
                $this->accion_reenviar($obj);
                break;
            case 'cambiar':
                $this->accion_cambiar($obj);
                break;
        }
        exit;
    }

    private function accion_comprobar($obj)
    {
        if (!empty($_POST['cedula'])) {
            try {
                $obj->set_cedula($_POST['cedula']);
                echo json_encode($obj->comprobarCedula());
            } catch (Exception $e) {
                echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
            }
        } else if (empty($_POST['cedula'])) {
            $resultado = array('accion' => 'error', 'mensaje' => 'Nesecitas ingresar la cedula.');
            echo json_encode($resultado);
        }
    }
    private function accion_codigo($obj)
    {
        if (!empty($_POST['codigo'])) {
            try {
                $obj->set_codigo($_POST['codigo']);
                echo json_encode($obj->comprobarCodigo());
            } catch (Exception $e) {
                echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
            }
        } else if (empty($_POST['cedula'])) {
            $resultado = array('accion' => 'error', 'mensaje' => 'Nesecitas ingresar el código.');
            echo json_encode($resultado);
        }
    }

    private function accion_cambiar($obj)
    {
        if (!empty($_POST['contraseña']) and !empty($_POST['contraseña_r'])) {
            if ($_POST['contraseña'] === $_POST['contraseña_r']) {
                try {
                    $obj->set_contraseña($_POST['contraseña']);
                    echo json_encode($obj->cambiarContraseña());
                } catch (Exception $e) {
                    echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
                }
            } else {
                $resultado = array('accion' => 'error', 'mensaje' => 'Las contraseñas ingresadas no coinciden. Por favor, verifíquelas.');
                echo json_encode($resultado);
            }
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'Datos erroneos o faltantes.');
            echo json_encode($resultado);
        }
    }

    private function accion_reenviar($obj)
    {
        try {
            echo json_encode($obj->reenviar());
        } catch (Exception $e) {
            echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
        }
    }

    private function bitacora($mensaje, $id)
    {
        $registro = [
            'modulo' => _MD_Inicio_,
            'accion' => $mensaje,
            'fecha' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'usuario' => $id
        ];
        $this->servicio_b->registro_B($registro);
    }
}
