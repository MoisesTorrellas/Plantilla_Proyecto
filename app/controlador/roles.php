<?php
class roles
{
    private $pagina;
    private $permisos;
    private $permiso_incluir;
    private $permiso_modificar;
    private $permiso_eliminar;
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
        $this->permisos = true;
        $this->permiso_incluir = false;
        $this->permiso_modificar = false;
        $this->permiso_eliminar = false;
        foreach ($_SESSION['permisos'] as $fila) {
            if ($fila['id_modulo'] == _MD_Roles_) {
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
        $obj = new modelo_roles();

        switch ($accion) {
            case 'consultar':
                echo json_encode($obj->consultar());
                break;
            case 'consultarModulo':
                echo json_encode($obj->consultarModulo());
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
            if (!empty($_POST['nombre']) && !empty($_POST['modulo_id'])) {
                try {
                    $obj->set_nombre($_POST['nombre']);
                    $obj->set_id_modulo($_POST['modulo_id']);
                    if (isset($_POST['check_incluir'])) {
                        $obj->set_c_incluir($_POST['check_incluir']);
                    }
                    if (isset($_POST['check_modificar'])) {
                        $obj->set_c_modificar($_POST['check_modificar']);
                    }
                    if (isset($_POST['check_eliminar'])) {
                        $obj->set_c_eliminar($_POST['check_eliminar']);
                    }
                    if (isset($_POST['check_reporte'])) {
                        $obj->set_c_reporte($_POST['check_reporte']);
                    }
                    if (isset($_POST['check_otros'])) {
                        $obj->set_c_otros($_POST['check_otros']);
                    }
                    echo json_encode($obj->incluir());
                } catch (Exception $e) {
                    echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
                }
            } else {
                $resultado = array('accion' => 'error', 'mensaje' => 'Error al registrar. Datos inválidos o faltantes, verifique los campos ingresados');
                echo json_encode($resultado);
            }
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'No tienes los permisos para registrar un rol.');
            echo json_encode($resultado);
        }
    }

    private function accion_modifiar($obj)
    {
        if ($this->permiso_modificar) {
            if (!empty($_POST['id']) && !empty($_POST['nombre']) && !empty($_POST['modulo_id'])) {
                try {
                    $obj->set_id($_POST['id']);
                    $obj->set_nombre($_POST['nombre']);
                    $obj->set_id_modulo($_POST['modulo_id']);
                    if (isset($_POST['check_incluir'])) {
                        $obj->set_c_incluir($_POST['check_incluir']);
                    }
                    if (isset($_POST['check_modificar'])) {
                        $obj->set_c_modificar($_POST['check_modificar']);
                    }
                    if (isset($_POST['check_eliminar'])) {
                        $obj->set_c_eliminar($_POST['check_eliminar']);
                    }
                    if (isset($_POST['check_reporte'])) {
                        $obj->set_c_reporte($_POST['check_reporte']);
                    }
                    if (isset($_POST['check_otros'])) {
                        $obj->set_c_otros($_POST['check_otros']);
                    }
                    echo json_encode($obj->modificar());
                } catch (Exception $e) {
                    echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
                }
            } else {
                $resultado = array('accion' => 'error', 'mensaje' => 'Error al modificar. Datos inválidos o faltantes, verifique los campos ingresados');
                echo json_encode($resultado);
            }
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'No tienes los permisos para modificar un rol.');
            echo json_encode($resultado);
        }
    }
    private function accion_eliminar($obj)
    {
        if ($this->permiso_eliminar) {
            if (!empty($_POST['id'])) {
                try {
                    $obj->set_id($_POST['id']);
                    echo json_encode($obj->eliminar());
                } catch (Exception $e) {
                    echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
                }
            } else {
                $resultado = array('accion' => 'error', 'mensaje' => 'Error al eliminar. Datos inválidos o faltantes, verifique los campos ingresados');
                echo json_encode($resultado);
            }
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'No tienes los permisos para eliminar un rol.');
            echo json_encode($resultado);
        }
    }
    private function accion_buscar($obj)
    {
        if ($this->permiso_modificar) {
            if (!empty($_POST['id'])) {
                try {
                    $obj->set_id($_POST['id']);
                    echo json_encode($obj->buscar());
                } catch (Exception $e) {
                    echo json_encode(['accion' => 'error', 'mensaje' => $e->getMessage()]);
                }
            } else {
                $resultado = array('accion' => 'error', 'mensaje' => 'Error al eliminar. Datos inválidos o faltantes, verifique los campos ingresados');
                echo json_encode($resultado);
            }
        } else {
            $resultado = array('accion' => 'error', 'mensaje' => 'No tienes los permisos para modificar un rol.');
            echo json_encode($resultado);
        }
    }
    private function bitacora($mensaje)
    {
        $registro = [
            'modulo' => _MD_Bitacora_,
            'accion' => $mensaje,
            'fecha' => date('Y-m-d'),
            'hora' => date('H:i:s'),
            'usuario' => $_SESSION['id']
        ];
        $this->servicio_b->registro_B($registro);
    }
}
