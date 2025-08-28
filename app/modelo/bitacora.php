<?php
require_once(__DIR__ . "/conexion.php");
class modelo_bitacora extends Conexion
{
    private $id;
    private $modulo;
    private $accion;
    private $fecha;
    private $hora;
    private $usuario;

    private $conex;

    public function __construct()
    {
        $this->conex = new Conexion();
        $this->conex = $this->conex->conexSG();
    }

    public function set_id($valor)
    {
        if (empty($valor)) {
            throw new Exception("El id no puede estar vacío.");
        }

        if (!filter_var($valor, FILTER_VALIDATE_INT)) {
            throw new Exception("El ID debe ser un número entero positivo.");
        }
        $this->id = $valor;
    }

    public function set_modulo($valor)
    {
        if (empty($valor)) {
            throw new Exception("Error al ingresar el modulo");
        }

        if (!filter_var($valor, FILTER_VALIDATE_INT)) {
            throw new Exception("Error al ingresar el modulo");
        }
        $this->modulo = $valor;
    }
    public function set_usuario($valor)
    {
        if (empty($valor)) {
            throw new Exception("Error al ingresar el usuario");
        }

        if (!filter_var($valor, FILTER_VALIDATE_INT)) {
            throw new Exception("Error al ingresar el usuario");
        }
        $this->usuario = $valor;
    }

    public function set_accion($valor)
    {
        $valor = trim($valor);
        if (empty($valor)) {
            throw new Exception("El nombre no puede estar vacío.");
        }

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,255}$/", $valor)) {
            throw new Exception("El nombre solo puede contener letras y espacios.");
        }
        $this->accion = $valor;
    }
    public function set_fecha($valor)
    {
        $this->fecha = $valor;
    }
    public function set_hora($valor)
    {
        $this->hora = $valor;
    }

    public function ingresar()
    {
        try {
            $sentencia = "INSERT INTO `bitacora`(`id_modulo`, `acciones`, `fecha`, `hora`, `idUsuario`) 
                            VALUES (:modulo,:accion,:fecha,:hora,:usuario)";
            $str = $this->conex->prepare($sentencia);
            $str->bindParam(':modulo', $this->modulo);
            $str->bindParam(':accion', $this->accion);
            $str->bindParam(':fecha', $this->fecha);
            $str->bindParam(':hora', $this->hora);
            $str->bindParam(':usuario', $this->usuario);
            $respuesta = $str->execute();

            if ($respuesta) {
                $resultado = ['accion' => 'incluir', 'resultado' => 1, 'mensaje' => 'Accion registrada con exito.'];
            } else {
                $resultado = ['accion' => 'incluir', 'resultado' => 0, 'mensaje' => 'Error al registrar la accion.'];
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = ['accion' => 'error', 'mensaje' => $e->getMessage()];
        }
        return $resultado;
    }

    public function consultar()
    {
        try {
            $sentencia = 'SELECT bitacora.id_bitacora,usuarios.nombreUsuario,usuarios.apellidoUsuario,usuarios.cedulaUsuario,modulo.nombre_modulo,bitacora.acciones,bitacora.fecha,bitacora.hora FROM `bitacora` 
                            INNER JOIN usuarios ON usuarios.idUsuario=bitacora.idUsuario
                            INNER JOIN modulo ON modulo.id_modulo=bitacora.id_modulo ORDER BY bitacora.id_bitacora ASC;';
            $str = $this->conex->prepare($sentencia);
            $str->execute();
            $datos = $str->fetchAll(PDO::FETCH_ASSOC);
            $resultado = array('accion' => 'consultar', 'datos' => $datos);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
        }
        return $resultado;
    }
    public function eliminar()
    {
        try {
            $sentencia = "DELETE FROM `bitacora` WHERE id_bitacora=:id";
            $str = $this->conex->prepare($sentencia);
            $str->bindParam(':id', $this->id);
            $respuesta = $str->execute();
            if ($respuesta) {
                $resultado = array('accion' => 'eliminar', 'resultado' => 1, 'mensaje' => 'Registro eliminado correctamente.');
            } else {
                $resultado = array('accion' => 'eliminar', 'resultado' => 0, 'mensaje' => 'Error al eliminar el registro.');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
        }
        return $resultado;
    }
}
