<?php
require_once(__DIR__ . "/conexion.php");
class modelo_usuarios extends Conexion
{

    private $id;
    private $cedula;
    private $nombre;
    private $apellido;
    private $telefono;
    private $contraseña;
    private $correo;
    private $rol;

    private $actualizar_contraseña = false;

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

    public function set_nombre($valor)
    {
        $valor = trim($valor);
        $valor = ucwords(strtolower($valor));
        if (empty($valor)) {
            throw new Exception("El nombre no puede estar vacío.");
        }

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/", $valor)) {
            throw new Exception("El nombre solo puede contener letras y espacios.");
        }
        $this->nombre = $valor;
    }
    public function set_apellido($valor)
    {
        $valor = trim($valor);
        $valor = ucwords(strtolower($valor));
        if (empty($valor)) {
            throw new Exception("El apellido no puede estar vacío.");
        }

        if (!preg_match("/^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]{3,30}$/", $valor)) {
            throw new Exception("El apellido solo puede contener letras y espacios.");
        }
        $this->apellido = $valor;
    }
    public function set_telefono($valor)
    {

        if (empty($valor)) {
            throw new Exception("El telefono no puede estar vacío.");
        }

        if (!preg_match("/^[0-9]{4}[-]{1}[0-9]{7}$/", $valor)) {
            throw new Exception("El formato del telefono es: 0000-0000000.");
        }
        $this->telefono = $valor;
    }
    public function set_contraseña($valor)
    {

        if (empty($valor)) {
            throw new Exception("La contraseña no puede estar vacía.");
        }

        if (!preg_match('/^(?=.*[0-9])(?=.*[A-Z])(?=.*[a-z])(?=.*[!@#\$%\^&\*\(\)\+=\._-])[0-9A-Za-z!@#\$%\^&\*\(\)\+=\._-]{8,20}$/', $valor)) {
            throw new Exception("Entre 8 y 20 caracteres, un número, una letra mayúscula, una letra minúscula y un carácter especial.");
        }

        $this->contraseña = password_hash($valor, PASSWORD_BCRYPT);
        $this->actualizar_contraseña = true;
    }
    public function set_correo($valor)
    {
        if (empty($valor)) {
            throw new Exception("El correo no puede estar vacío.");
        }

        if (strlen($valor) < 3 || strlen($valor) > 60) {
            throw new Exception("El correo debe tener entre 3 y 60 caracteres.");
        }

        if (!preg_match('/^[^\s@]+@[^\s@]+\.(com|org|net|edu|gov|mil|info|io|co|es|mx|ar|cl|pe|br)$/i', $valor)) {
            throw new Exception("Formato de correo inválido o extensión de dominio no permitida.");
        }

        $this->correo = $valor;
    }

    public function set_rol($valor)
    {
        if (empty($valor)) {
            throw new Exception("El rol no puede estar vacío.");
        }

        if (!filter_var($valor, FILTER_VALIDATE_INT)) {
            throw new Exception("Error al registrar el rol.");
        }
        $this->rol = $valor;
    }

    public function incluir()
    {
        try {
            if (!$this->existeCedulaUno($this->cedula) && !$this->existeCorreoUno($this->correo)) {
                if ($this->existeCedulaCero($this->cedula)) {
                    $sentencia = "UPDATE `usuarios` SET 
                                `nombreUsuario` = :nombre,
                                `apellidoUsuario` = :apellido,
                                `telefonoUsuario` = :telefono,
                                `contraseña` = :contra,
                                `correo` = :correo,
                                `id_rol` = :rol,
                                `estatus` = 1 
                                WHERE cedulaUsuario = :cedula";
                } else {
                    $sentencia = "INSERT INTO `usuarios`
                                (`cedulaUsuario`, `nombreUsuario`, `apellidoUsuario`, `telefonoUsuario`, `contraseña`,`correo`, `id_rol`) 
                                VALUES 
                                (:cedula, :nombre, :apellido, :telefono, :contra,:correo, :rol)";
                }

                $str = $this->conex->prepare($sentencia);
                $str->bindParam(':cedula', $this->cedula);
                $str->bindParam(':nombre', $this->nombre);
                $str->bindParam(':apellido', $this->apellido);
                $str->bindParam(':telefono', $this->telefono);
                $str->bindParam(':contra', $this->contraseña);
                $str->bindParam(':correo', $this->correo);
                $str->bindParam(':rol', $this->rol);
                $respuesta = $str->execute();

                if ($respuesta) {
                    $resultado = ['accion' => 'incluir', 'resultado' => 1, 'mensaje' => 'Usuario registrado correctamente.'];
                } else {
                    $resultado = ['accion' => 'incluir', 'resultado' => 0, 'mensaje' => 'Error al registrar el usuario.'];
                }
            }else if($this->existeCorreoUno($this->correo)){
                $resultado = ['accion' => 'incluir', 'resultado' => 0, 'mensaje' => 'El correo ya está registrado.'];
            } else {
                $resultado = ['accion' => 'incluir', 'resultado' => 0, 'mensaje' => 'La cédula ya está registrada.'];
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = ['accion' => 'error', 'mensaje' => $e->getMessage()];
        }

        return $resultado;
    }


    public function modificar()
    {
        try {
            if ($this->existeCedulaCero($this->cedula)) {
                $resultado = ['accion' => 'modificar', 'resultado' => 0, 'mensaje' => 'La cédula ya fue usada por otro usuario (eliminado). No se puede reutilizar.'];
                return $resultado;
            }
            if ($this->existeCorreoCero($this->correo)) {
                $resultado = ['accion' => 'modificar', 'resultado' => 0, 'mensaje' => 'El correo ya fue usado por otro usuario (eliminado). No se puede reutilizar.'];
                return $resultado;
            }
            if ($this->coincide($this->cedula, $this->id, $this->correo) || (!$this->existeCedulaUno($this->cedula) && !$this->existeCorreoUno($this->correo)) ) {

                $sentencia = "UPDATE `usuarios` SET 
                            `cedulaUsuario` = :cedula,
                            `nombreUsuario` = :nombre,
                            `apellidoUsuario` = :apellido,
                            `correo` = :correo,
                            `telefonoUsuario` = :telefono,";

                if ($this->actualizar_contraseña) {
                    $sentencia .= "`contraseña` = :contra,";
                }

                $sentencia .= "`id_rol` = :rol,
                                `estatus` = 1 
                                WHERE idUsuario = :id";

                $str = $this->conex->prepare($sentencia);
                $str->bindParam(':id', $this->id);
                $str->bindParam(':cedula', $this->cedula);
                $str->bindParam(':nombre', $this->nombre);
                $str->bindParam(':apellido', $this->apellido);
                $str->bindParam(':correo', $this->correo);
                $str->bindParam(':telefono', $this->telefono);
                if ($this->actualizar_contraseña) {
                    $str->bindParam(':contra', $this->contraseña);
                }
                $str->bindParam(':rol', $this->rol);
                $respuesta = $str->execute();

                if ($respuesta) {
                    $resultado = ['accion' => 'modificar', 'resultado' => 1, 'mensaje' => 'Usuario modificado correctamente.'];
                } else {
                    $resultado = ['accion' => 'modificar', 'resultado' => 0, 'mensaje' => 'Error al modificar el usuario.'];
                }
            } else {
                $resultado = ['accion' => 'modificar', 'resultado' => 0, 'mensaje' => 'La cédula ya está registrada.'];
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
        }
        return $resultado;
    }
    public function eliminar()
    {
        try {
            if ($this->id != 1) {

                $sentencia = "UPDATE `usuarios` SET `estatus` = 0 WHERE usuarios.idUsuario = :id";
                $str = $this->conex->prepare($sentencia);
                $str->bindParam(':id', $this->id);
                $respuesta = $str->execute();
                if ($respuesta) {
                    $resultado = array('accion' => 'eliminar', 'resultado' => 1, 'mensaje' => 'Usuario eliminado correctamente.');
                } else {
                    $resultado = array('accion' => 'eliminar', 'resultado' => 0, 'mensaje' => 'Error al eliminar el usuario.');
                }
            } else {
                $resultado = array('accion' => 'eliminar', 'resultado' => 0, 'mensaje' => 'No puedes eliminar el Administrador Principal.');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
        }
        return $resultado;
    }
    public function consultar()
    {
        try {
            $sentencia = 'SELECT usuarios.idUsuario,usuarios.cedulaUsuario,usuarios.nombreUsuario,usuarios.apellidoUsuario,usuarios.telefonoUsuario,usuarios.correo,roles.nombre_rol 
                            FROM `usuarios` INNER JOIN roles ON roles.id_rol=usuarios.id_rol WHERE usuarios.estatus=1 ORDER BY usuarios.idUsuario ASC;;';
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
    public function consultarRoles($roles)
    {
        try {
            $datos = array();
            foreach ($roles['datos'] as $a) {
                $datos[] = [
                    'id_rol' => $a['id_rol'],
                    'nombre_rol' => $a['nombre_rol']
                ];
            }
            $resultado = array('accion' => 'consultarRoles', 'datos' => $datos);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
        }
        return $resultado;
    }
    public function buscar()
    {
        try {
            $sentencia = 'SELECT usuarios.idUsuario,usuarios.cedulaUsuario,usuarios.nombreUsuario,usuarios.apellidoUsuario,usuarios.telefonoUsuario,usuarios.correo,usuarios.id_rol FROM `usuarios` 
            WHERE usuarios.idUsuario=:id AND usuarios.estatus=1;';
            $str = $this->conex->prepare($sentencia);
            $str->bindParam(':id', $this->id);
            $str->execute();
            $datos = $str->fetchAll(PDO::FETCH_ASSOC);
            $resultado = array('accion' => 'buscar', 'resultado' => 1, 'datos' => $datos);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
        }
        return $resultado;
    }

    private function existeCedulaUno($cedula)
    {
        $sentencia = "SELECT usuarios.idUsuario FROM `usuarios` WHERE usuarios.cedulaUsuario=:cedula AND usuarios.estatus=1";
        $str = $this->conex->prepare($sentencia);
        $str->bindParam(':cedula', $cedula);
        $str->execute();

        $respuesta = $str->fetchAll(PDO::FETCH_BOTH);
        if ($respuesta) {
            return true;
        } else {
            return false;
        }
    }

    private function existeCorreoUno($correo)
    {
        $sentencia = "SELECT usuarios.idUsuario FROM `usuarios` WHERE usuarios.correo=:correo AND usuarios.estatus=1";
        $str = $this->conex->prepare($sentencia);
        $str->bindParam(':correo', $correo);
        $str->execute();

        $respuesta = $str->fetchAll(PDO::FETCH_BOTH);
        if ($respuesta) {
            return true;
        } else {
            return false;
        }
    }

    private function existeCedulaCero($cedula)
    {
        $sentencia = "SELECT usuarios.idUsuario FROM `usuarios` WHERE usuarios.cedulaUsuario=:cedula AND usuarios.estatus=0";
        $str = $this->conex->prepare($sentencia);
        $str->bindParam(':cedula', $cedula);
        $str->execute();

        $respuesta = $str->fetchAll(PDO::FETCH_BOTH);
        if ($respuesta) {
            return true;
        } else {
            return false;
        }
    }
    private function existeCorreoCero($correo)
    {
        $sentencia = "SELECT usuarios.idUsuario FROM `usuarios` WHERE usuarios.correo=:correo AND usuarios.estatus=0";
        $str = $this->conex->prepare($sentencia);
        $str->bindParam(':correo', $correo);
        $str->execute();

        $respuesta = $str->fetchAll(PDO::FETCH_BOTH);
        if ($respuesta) {
            return true;
        } else {
            return false;
        }
    }
    private function coincide($cedula, $id, $correo)
    {
        $sentencia = "SELECT usuarios.idUsuario FROM `usuarios` WHERE usuarios.idUsuario=:id AND usuarios.cedulaUsuario=:cedula  AND usuarios.correo=:correo AND usuarios.estatus=1;";
        $str = $this->conex->prepare($sentencia);
        $str->bindParam(':id', $id);
        $str->bindParam(':cedula', $cedula);
        $str->bindParam(':correo', $correo);
        $str->execute();

        $respuesta = $str->fetchAll(PDO::FETCH_BOTH);
        if ($respuesta) {
            return true;
        } else {
            return false;
        }
    }
}
