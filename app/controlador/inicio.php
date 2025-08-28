<?php
class inicio
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
        $obj = new modelo_inicio();
        switch ($accion) {
            case 'inicio':
                $this->accion_iniciar($obj);
                break;
        }
        exit;
    }


    private function accion_iniciar($obj)
    {
        if (!empty($_POST['cedula']) && !empty($_POST['contraseña'])) {
            try {
                $obj->set_cedula($_POST['cedula']);
                $obj->set_contraseña($_POST['contraseña']);
                $resultado = $obj->ingreso();

                if ($resultado['resultado'] == 1) {
                    $_SESSION['id'] = $resultado['datos']['idUsuario'];
                    $_SESSION['rol'] = $resultado['datos']['nombre_rol'];
                    $_SESSION['nombre'] = $resultado['datos']['nombreUsuario'];
                    $_SESSION['apellido'] = $resultado['datos']['apellidoUsuario'];
                    $_SESSION['permisos'] = $resultado['permisos'];
                    $this->bitacora('inicio sesion de forma exitosa',$_SESSION['id']);

                }
                echo json_encode($resultado);
            } catch (Exception $e) {
                echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
            }
        } else if (empty($_POST['cedula'])) {
            $resultado = array('accion' => 'error', 'mensaje' => 'Nesecitas ingresar la cedula.');
            echo json_encode($resultado);
        } else if (empty($_POST['contraseña'])) {
            $resultado = array('accion' => 'error', 'mensaje' => 'Nesecitas ingresar la contraseña.');
            echo json_encode($resultado);
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'Nesecitas ingresar sus datos.');
            echo json_encode($resultado);
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
