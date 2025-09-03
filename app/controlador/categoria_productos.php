<?php
class categoria_productos
{
    private $pagina;
    private $permisos;
    private $servicios;
    private $servicio_b;
    private $permiso_incluir;
    private $permiso_modificar;
    private $permiso_eliminar;

    public function __construct($pagina)
    {
        $this->pagina = $pagina;
        $this->comprobarPermisos();
    }

    private function comprobarPermisos()
    {
        require_once(__DIR__ . "/../../servicios/servicios_bitacora.php");
        $this->servicio_b = new servicios_bitacora();
        $this->permisos = true;
        $this->permiso_incluir = false;
        $this->permiso_modificar = false;
        $this->permiso_eliminar = false;
        foreach ($_SESSION['permisos'] as $fila) {
            if ($fila['id_modulo'] == _MD_Categoria_Productos_) {
                $this->permisos = false;
                if ($fila['incluir'] == _PRM_)$this->permiso_incluir = true;
                if ($fila['modificar'] == _PRM_)$this->permiso_modificar = true;
                if ($fila['eliminar'] == _PRM_)$this->permiso_eliminar = true;
                break;
            }
        }

        if ($this->permisos) {
            $this->bitacora('Intentó ingresar al módulo sin permiso');
            $_SESSION['error'] = array('mensaje' => 'No cuentas con los permisos para entrar a este modulo.');
            header("location:/Proyecto_Plantilla/public/principal");
            exit;
        } else {
            if (!isset($_SESSION['bitacora_ingreso_modulo'][$this->pagina])) {
                if ($_SESSION['registrar_ingreso_modulo']) {
                    if ($this->permiso_incluir || $this->permiso_modificar || $this->permiso_eliminar) {
                        $this->bitacora('Ingreso al módulo con permisos de edición');
                    } else {
                        $this->bitacora('Ingreso al módulo con solo permisos de visualización');
                    }
                    $_SESSION['bitacora_ingreso_modulo'][$this->pagina] = true;
                }
            }
        }
    }

    public function procesarSolicitud()
    {
        if (!is_file(__DIR__ . "/../modelo/" . $this->pagina . ".php")) {
            require_once(__DIR__ . "/../app/vista/complementos/error.php");
            exit;
        }

        if (!empty($_SESSION['id'])) {
            require_once(__DIR__ . "/../modelo/" . $this->pagina . ".php");
            require_once(__DIR__ . "/../../servicios/servicios_compartidos.php");
            if (is_file(__DIR__ . "/../vista/" . $this->pagina . ".php")) {

                if ($this->comprobarAjax() && !empty($_POST)) {
                    $this->manejarSolicitud();
                } else {
                    $_SESSION['token'] = bin2hex(random_bytes(32));
                    require_once(__DIR__ . "/../vista/" . $this->pagina . ".php");
                    $this->permisosAcciones();
                }
            } else {
                require_once(__DIR__ . "/../app/vista/complementos/error.php");
            }
        } else {
            header("location:/Proyecto_Plantilla/public/");
        }
    }

    private function comprobarAjax()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }

    private function permisosAcciones()
    {
        echo '<script>
                            window.permisos = {
                                incluir: ' . ($this->permiso_incluir ? 'true' : 'false') . ',
                                modificar: ' . ($this->permiso_modificar ? 'true' : 'false') . ',
                                eliminar: ' . ($this->permiso_eliminar ? 'true' : 'false') . '
                            };
                        </script>';
    }

    private function manejarSolicitud()
    {
        require_once(__DIR__ . "/../controlador/cookie.php");
        if (!isset($_SESSION['token'])) {
            $_SESSION['token'] = bin2hex(random_bytes(32));
        }

        if (!isset($_POST['token']) || $_POST['token'] !== $_SESSION['token']) {
            $resultado = array('accion' => 'error', 'mensaje' => 'Error de validación de token.');
            echo json_encode($resultado);
            exit;
        }

        $accion = isset($_POST["accion"]) ? filter_var($_POST["accion"], FILTER_SANITIZE_SPECIAL_CHARS) : "";
        $obj = new modelo_usuarios();
        $this->servicios = new servicios_compartidos();
        switch ($accion) {
            case 'consultar':
                echo json_encode($obj->consultar());
                break;
            case 'consultarRoles':
                echo json_encode($obj->consultarRoles($this->servicios->obtener_roles()));
                break;
            case 'incluir':
                $this->accion_incluir($obj);
                break;
            case 'buscar':
                $this->accion_buscar($obj);
                break;
            case 'modificar':
                $this->accion_modifiar($obj);
                break;
            case 'eliminar':
                $this->accion_eliminar($obj);
                break;
        }
        exit;
    }


    private function accion_incluir($obj)
    {
        if ($this->permiso_incluir) {
            
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'No tienes los permisos para registrar un usuario.');
            echo json_encode($resultado);
            $this->bitacora('Intentó registrar un usuario sin tener permiso');
        }
    }

    private function accion_modifiar($obj)
    {
        if ($this->permiso_modificar) {
            
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'No tienes los permisos para modificar un usuario.');
            echo json_encode($resultado);
            $this->bitacora('Intentó modificar un usuario sin tener permiso');
        }
    }
    private function accion_eliminar($obj)
    {
        if ($this->permiso_eliminar) {
            
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'No tienes los permisos para eliminar un usuario.');
            echo json_encode($resultado);
            $this->bitacora('Intentó eliminar un usuario sin tener permiso');
        }
    }
    private function accion_buscar($obj)
    {
        if ($this->permiso_modificar) {
            
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'No tienes los permisos para modificar un usuario.');
            echo json_encode($resultado);
            $this->bitacora('Intentó modificar un usuario sin tener permiso');
        }
    }

    private function bitacora($mensaje)
    {
        $registro = [
            'modulo' => _MD_Categoria_Productos_,
            'accion' => $mensaje,
            'fecha' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'usuario' => $_SESSION['id']
        ];
        $this->servicio_b->registro_B($registro);
    }
}
