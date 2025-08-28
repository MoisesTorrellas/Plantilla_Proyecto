<?php
require_once(__DIR__ . "/conexion.php");
class modelo_inicio extends Conexion
{
    private $contraseña;
    private $cedula;
    private $conex;

    public function __construct()
    {
        $this->conex = new Conexion();
        $this->conex = $this->conex->conexSG();
    }
    public function set_cedula($valor)
    {
        if (empty($valor)) {
            throw new Exception("La cedula no puede estar vacío.");
        }

        if (!filter_var($valor, FILTER_VALIDATE_INT)) {
            throw new Exception("La cedula no puede tener letras ni caracteres especiales.");
        }
        if (!preg_match("/^[0-9]{7,8}$/", $valor)) {
            throw new Exception("La cedula solo puede tener de 7 a 8 caracteres.");
        }
        $this->cedula = $valor;
    }

    public function set_contraseña($valor)
    {

        if (empty($valor)) {
            throw new Exception("La contraseña no puede estar vacía.");
        }

        if (!preg_match('/^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#\$%\^&\*\(\)\+=\._-])[0-9A-Za-z!@#\$%\^&\*\(\)\+=\._-]{8,20}$/', $valor)) {
            throw new Exception("Entre 8 y 20 caracteres, un número, una letra mayúscula, una letra minúscula y un carácter especial.");
        }

        $this->contraseña = $valor;
    }
    public function ingreso()
    {
        try {
            $consulta = "SELECT usuarios.idUsuario,usuarios.nombreUsuario,usuarios.apellidoUsuario,roles.nombre_rol,roles.id_rol,usuarios.contraseña 
                            FROM `usuarios` 
                            INNER JOIN roles ON roles.id_rol=usuarios.id_rol WHERE cedulaUsuario = :cedula;";
            $str = $this->conex->prepare($consulta);
            $str->bindParam(':cedula', $this->cedula);
            $str->execute();
            $respuesta = $str->fetch(PDO::FETCH_ASSOC);

            $consulta = "SELECT permiso.id_modulo,permiso.eliminar,permiso.modificar,permiso.incluir 
                            FROM `usuarios` 
                            INNER JOIN permiso ON permiso.id_rol=usuarios.id_rol WHERE usuarios.idUsuario=:id;";
            $str = $this->conex->prepare($consulta);
            $str->bindParam(':id', $respuesta['idUsuario']);
            $str->execute();
            $permisos = $str->fetchAll(PDO::FETCH_ASSOC);

            if ($respuesta && $permisos) {
                if (password_verify($this->contraseña, $respuesta['contraseña'])) {
                    $resultado = array('accion' => 'inicio', 'resultado' => 1, 'datos' => $respuesta, 'permisos' => $permisos, 'mensaje' => 'BIENVENIDO');
                } else {
                    $resultado = array('accion' => 'inicio', 'resultado' => 0, 'mensaje' => 'La contraseña es incorrecta');
                }
            } else {
                $resultado = array('accion' => 'inicio', 'resultado' => 2, 'mensaje' => 'La cédula no existe');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = ['accion' => 'error', 'mensaje' => $e->getMessage()];
        }

        return $resultado;
    }
}
