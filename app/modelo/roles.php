<?php
require_once(__DIR__ . "/conexion.php");

class modelo_roles extends Conexion
{

    private $id;
    private $nombre;
    private $id_modulo;
    private $c_incluir;
    private $c_modificar;
    private $c_eliminar;
    private $c_reporte;
    private $c_otros;

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
    public function set_id_modulo($valor)
    {
        if (empty($valor)) {
            throw new Exception("Se debe ingresar al menos un modulo.");
        }
        if (!$this->comprobar_array_int($valor)) {
            throw new Exception("El modulo es invalido.");
        }
        $this->id_modulo = $valor;
    }
    public function set_c_incluir($valor)
    {
        if (!$this->comprobar_array_int($valor)) {
            throw new Exception("El incluir del modulo es invalido.");
        }
        $this->c_incluir = $valor;
    }
    public function set_c_modificar($valor)
    {
        if (!$this->comprobar_array_int($valor)) {
            throw new Exception("El modificar del modulo es invalido.");
        }
        $this->c_modificar = $valor;
    }
    public function set_c_eliminar($valor)
    {
        if (!$this->comprobar_array_int($valor)) {
            throw new Exception("El eliminar del modulo es invalido.");
        }
        $this->c_eliminar = $valor;
    }
    public function set_c_reporte($valor)
    {
        if (!$this->comprobar_array_int($valor)) {
            throw new Exception("El reporte del modulo es invalido.");
        }
        $this->c_reporte = $valor;
    }
    public function set_c_otros($valor)
    {
        if (!$this->comprobar_array_int($valor)) {
            throw new Exception("El otros del modulo es invalido.");
        }
        $this->c_otros = $valor;
    }

    /* function get_id()
    {
        return $this->id;
    }
    function get_nombre()
    {
        return $this->nombre;
    }
    function get_id_modulo()
    {
        return $this->id_modulo;
    }
    function get_c_incluir()
    {
        return $this->c_incluir;
    }
    function get_c_modificar()
    {
        return $this->c_modificar;
    }
    function get_c_eliminar()
    {
        return $this->c_eliminar;
    } */

    public function incluir()
    {
        try {
            if ($this->existeRol($this->nombre)) {
                $sentencia = "INSERT INTO `roles`(`nombre_rol`) VALUES (:nombre)";
                $str = $this->conex->prepare($sentencia);
                $str->bindParam(':nombre', $this->nombre);
                $respuesta = $str->execute();

                if ($respuesta) {
                    $id_rol = $this->conex->lastInsertId();

                    if (!empty($this->id_modulo)) {
                        $sentencia = "INSERT INTO `permiso`(`id_rol`, `id_modulo`, `eliminar`, `modificar`, `incluir`, `reporte`, `otros`) 
                                VALUES (:id_rol,:id_modulo,:eliminar,:modificar,:incluir,:reporte,:otros)";
                        $respuesta_permiso = true;

                        foreach ($this->id_modulo as $modulo) {
                            $incluir = isset($this->c_incluir[$modulo]) ? 1 : 0;
                            $modificar = isset($this->c_modificar[$modulo]) ? 1 : 0;
                            $eliminar = isset($this->c_eliminar[$modulo]) ? 1 : 0;
                            $reporte = isset($this->c_reporte[$modulo]) ? 1 : 0;
                            $otros = isset($this->c_otros[$modulo]) ? 1 : 0;

                            $str = $this->conex->prepare($sentencia);
                            $str->bindParam(':id_rol', $id_rol);
                            $str->bindParam(':id_modulo', $modulo);
                            $str->bindParam(':eliminar', $eliminar);
                            $str->bindParam(':modificar', $modificar);
                            $str->bindParam(':incluir', $incluir);
                            $str->bindParam(':reporte', $reporte);
                            $str->bindParam(':otros', $otros);
                            $respuesta = $str->execute();
                            if (!$respuesta) {
                                $respuesta_permiso = false;
                                break;
                            }
                        }

                        if ($respuesta_permiso) {
                            $resultado = ['accion' => 'incluir', 'resultado' => 1, 'mensaje' => 'Rol y permisos registrados correctamente.'];
                        } else {
                            $resultado = ['accion' => 'incluir', 'resultado' => 0, 'mensaje' => 'Rol registrado, pero falló la asignación de permisos.'];
                        }
                    } else {
                        $resultado = ['accion' => 'incluir', 'resultado' => 1, 'mensaje' => 'Rol registrado sin permisos.'];
                    }
                } else {
                    $resultado = ['accion' => 'incluir', 'resultado' => 0, 'mensaje' => 'Error al registrar el Rol.'];
                }
            } else {
                $resultado = ['accion' => 'incluir', 'resultado' => 0, 'mensaje' => 'Este rol ya existe.'];
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
            if ($this->coincide($this->nombre, $this->id) || $this->existeRol($this->nombre)) {
                $sentencia = "UPDATE `roles` SET `nombre_rol`=:nombre WHERE id_rol=:id";
                $str = $this->conex->prepare($sentencia);
                $str->bindParam(':nombre', $this->nombre);
                $str->bindParam(':id', $this->id);
                $respuesta = $str->execute();

                if ($respuesta) {
                    $sentencia = "DELETE FROM `permiso` WHERE `id_rol` = :id";
                    $str = $this->conex->prepare($sentencia);
                    $str->bindParam(':id', $this->id);
                    $str->execute();


                    if (!empty($this->id_modulo)) {
                        $sentencia = "INSERT INTO `permiso`(`id_rol`, `id_modulo`, `eliminar`, `modificar`, `incluir`, `reporte`, `otros`) 
                                VALUES (:id_rol,:id_modulo,:eliminar,:modificar,:incluir,:reporte,:otros)";
                        $respuesta_permiso = true;

                        foreach ($this->id_modulo as $modulo) {
                            $incluir = isset($this->c_incluir[$modulo]) ? 1 : 0;
                            $modificar = isset($this->c_modificar[$modulo]) ? 1 : 0;
                            $eliminar = isset($this->c_eliminar[$modulo]) ? 1 : 0;
                            $reporte = isset($this->c_reporte[$modulo]) ? 1 : 0;
                            $otros = isset($this->c_otros[$modulo]) ? 1 : 0;

                            $str = $this->conex->prepare($sentencia);
                            $str->bindParam(':id_rol', $this->id);
                            $str->bindParam(':id_modulo', $modulo);
                            $str->bindParam(':eliminar', $eliminar);
                            $str->bindParam(':modificar', $modificar);
                            $str->bindParam(':incluir', $incluir);
                            $str->bindParam(':reporte', $reporte);
                            $str->bindParam(':otros', $otros);
                            $respuesta = $str->execute();
                            if (!$respuesta) {
                                $respuesta_permiso = false;
                                break;
                            }
                        }

                        if ($respuesta_permiso) {
                            $resultado = ['accion' => 'modificar', 'resultado' => 1, 'mensaje' => 'Rol y permisos modificados correctamente.'];
                        } else {
                            $resultado = ['accion' => 'modificar', 'resultado' => 0, 'mensaje' => 'Rol modificado, pero falló la asignación de permisos.'];
                        }
                    } else {
                        $resultado = ['accion' => 'modificar', 'resultado' => 1, 'mensaje' => 'Rol modificado sin permisos.'];
                    }
                } else {
                    $resultado = ['accion' => 'modificar', 'resultado' => 0, 'mensaje' => 'Error al modificar el Rol.'];
                }
            } else{
                $resultado = ['accion' => 'modificar', 'resultado' => 0, 'mensaje' => 'Este rol ya existe.'];
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
            if (!$this->usado($this->id)) {

                $sentencia = "DELETE FROM `roles` WHERE id_rol=:id";
                $str = $this->conex->prepare($sentencia);
                $str->bindParam(':id', $this->id);
                $respuesta = $str->execute();
                if ($respuesta) {
                    $resultado = array('accion' => 'eliminar', 'resultado' => 1, 'mensaje' => 'Rol eliminado correctamente.');
                } else {
                    $resultado = array('accion' => 'eliminar', 'resultado' => 0, 'mensaje' => 'Error al eliminar el Rol.');
                }
            } else {
                $resultado = array('accion' => 'eliminar', 'resultado' => 0, 'mensaje' => 'Hay usuarios con este rol asignado.');
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
            $sentencia = 'SELECT * FROM roles;';
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
    public function consultarModulo()
    {
        try {
            $sentencia = 'SELECT modulo.id_modulo, modulo.nombre_modulo FROM `modulo` WHERE modulo.id_modulo!=4 AND modulo.id_modulo!=5;';
            $str = $this->conex->prepare($sentencia);
            $str->execute();
            $datos = $str->fetchAll(PDO::FETCH_ASSOC);
            $resultado = array('accion' => 'consultarModulo', 'datos' => $datos);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = array('accion' => 'error', 'mensaje' => $e->getMessage());
        }
        return $resultado;
    }
    public function buscar()
    {
        try {
            $sentencia = 'SELECT roles.id_rol,roles.nombre_rol,permiso.eliminar,permiso.modificar,permiso.incluir,permiso.reporte,permiso.otros,modulo.nombre_modulo,modulo.id_modulo FROM `roles` 
            INNER JOIN permiso ON roles.id_rol=permiso.id_rol 
            INNER JOIN modulo ON permiso.id_modulo=modulo.id_modulo 
            WHERE roles.id_rol=:id';
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

    private function comprobar_array_int($a)
    {
        $valido = true;
        if (!empty($a) && is_array($a)) {
            foreach ($a as $num) {
                $a_filtrado = filter_var($num, FILTER_VALIDATE_INT);
                if ($a_filtrado === false) {
                    $valido = false;
                    break;
                }
            }
        } else {
            $valido = false;
        }

        return $valido;
    }

    private function existeRol($nombre)
    {
        $sentencia = "SELECT id_rol FROM roles WHERE nombre_rol=:nombre;";
        $str = $this->conex->prepare($sentencia);
        $str->bindParam(':nombre', $nombre);
        $str->execute();

        $respuesta = $str->fetchAll(PDO::FETCH_BOTH);
        if ($respuesta) {
            return false;
        } else {
            return true;
        }
    }

    private function usado($id)
    {
        $sentencia = "SELECT usuarios.idUsuario FROM `roles`
        INNER JOIN usuarios ON usuarios.id_rol=:id GROUP BY usuarios.idUsuario;";
        $str = $this->conex->prepare($sentencia);
        $str->bindParam(':id', $id);
        $str->execute();

        $respuesta = $str->fetchAll(PDO::FETCH_BOTH);
        if ($respuesta) {
            return true;
        } else {
            return false;
        }
    }

    private function coincide($nombre, $id)
    {
        $sentencia = "SELECT id_rol FROM roles WHERE nombre_rol=:nombre AND id_rol=:id;";
        $str = $this->conex->prepare($sentencia);
        $str->bindParam(':nombre', $nombre);
        $str->bindParam(':id', $id);
        $str->execute();

        $respuesta = $str->fetchAll(PDO::FETCH_BOTH);
        if ($respuesta) {
            return true;
        } else {
            return false;
        }
    }
}
