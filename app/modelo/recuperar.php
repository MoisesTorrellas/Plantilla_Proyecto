<?php
require_once(__DIR__ . "/conexion.php");
require_once(__DIR__ . '/../../public/libraries/PHPMailer-master/src/PHPMailer.php');
require_once(__DIR__ . '/../../public/libraries/PHPMailer-master/src/Exception.php');
require_once(__DIR__ . '/../../public/libraries/PHPMailer-master/src/SMTP.php');

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

class modelo_recuperar extends Conexion
{
    private $codigo;
    private $cedula;
    private $conex;
    private $contraseña;
    private $verificacion;

    public function __construct()
    {
        $this->conex = new Conexion();
        $this->conex = $this->conex->conexSG();
        $this->verificacion = false;
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
    public function set_codigo($valor)
    {
        if (empty($valor)) {
            throw new Exception("El codigo no puede estar vacío.");
        }

        if (!preg_match("/^[0-9]{6}$/", $valor)) {
            throw new Exception("El codigo solo puede tener 6 caracteres.");
        }
        $this->codigo = $valor;
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
    }

    public function comprobarCedula()
    {
        try {
            $consulta = "SELECT usuarios.correo, usuarios.nombreUsuario, usuarios.apellidoUsuario FROM `usuarios` WHERE usuarios.cedulaUsuario=:cedula AND usuarios.estatus=1";
            $str = $this->conex->prepare($consulta);
            $str->bindParam(':cedula', $this->cedula);
            $str->execute();
            $respuesta = $str->fetch(PDO::FETCH_ASSOC);
            if ($respuesta) {
                $_SESSION['correo'] = $respuesta['correo'];
                $_SESSION['destinatario'] = $respuesta['nombreUsuario'] . ' ' . $respuesta['apellidoUsuario'];
                $_SESSION['cedula_r'] = $this->cedula;
                $resultado = $this->enviarCorreo('Se envio un código a su correo.');
            } else {
                $resultado = array('accion' => 'comprobar', 'resultado' => 0, 'mensaje' => 'La cedula ingresada no existe.');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = ['accion' => 'error', 'mensaje' => $e->getMessage()];
        }

        return $resultado;
    }

    public function reenviar()
    {
        try {
            $resultado = $this->enviarCorreo('Se le envio otro código al correo.');
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = ['accion' => 'error', 'mensaje' => $e->getMessage()];
        }
        return $resultado;
    }

    public function comprobarCodigo()
    {
        try {
            if ($this->codigo == $_SESSION['codigo_verificacion']) {
                unset($_SESSION['codigo']);
                unset($_SESSION['destinatario']);
                unset($_SESSION['correo']);
                $this->verificacion = true;
                $resultado = array('accion' => 'comprobarCodigo', 'resultado' => 1, 'mensaje' => 'Código Validado.');
            } else {
                $resultado = array('accion' => 'comprobarCodigo', 'resultado' => 0, 'mensaje' => 'el código no es correcto.');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = ['accion' => 'error', 'mensaje' => $e->getMessage()];
        }
        return $resultado;
    }

    public function cambiarContraseña()
    {
        try {

            /*if ($this->verificacion) { */
            $cedula_r = $_SESSION['cedula_r'];
            $sentencia = "UPDATE `usuarios` SET 
                            `contraseña` = :contra
                            WHERE cedulaUsuario = :cedula";

            $str = $this->conex->prepare($sentencia);
            $str->bindParam(':cedula', $cedula_r);
            $str->bindParam(':contra', $this->contraseña);
            $respuesta = $str->execute();

            if ($respuesta) {
                $resultado = ['accion' => 'cambiar', 'resultado' => 1, 'mensaje' => 'Contraseña modificada correctamente.'];
                unset($_SESSION['cedula_r']);
            } else {
                $resultado = ['accion' => 'cambiar', 'resultado' => 0, 'mensaje' => 'Error al modificar la contraseña.'];
            }
            /*}else{
                $resultado = ['accion' => 'error', 'mensaje' => 'No verificado'];
            } */
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = ['accion' => 'error', 'mensaje' => $e->getMessage()];
        }
        return $resultado;
    }

    private function enviarCorreo($mensaje)
    {
        $mail = new PHPMailer(true);
        try {
            $codigo = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT); // Asegura que tenga 6 dígitos
            $correo = $_SESSION['correo'];
            $destinatario = $_SESSION['destinatario'];
            // 2. Configurar el servidor SMTP
            $mail->isSMTP();
            $mail->Host       = 'smtp.gmail.com';
            $mail->SMTPAuth   = true;
            $mail->Username   = 'moitcj@gmail.com';            // Cambia por tu correo real
            $mail->Password   = 'dcotyzvsafgxnfjt';      // Usa una clave de aplicación si usas Gmail
            $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
            $mail->Port       = 465;

            // 3. Configurar remitente y destinatario
            $mail->setFrom('moitcj@gmail.com', 'Moises Torrellas');
            $mail->addAddress($correo, $destinatario);

            // 4. Contenido del mensaje
            $mail->isHTML(true);
            $mail->Subject = 'Tu codigo de verificacion';
            $mail->Body    = "<p>Tu código de verificación es:</p><h2>$codigo</h2>";
            $mail->AltBody = "Tu código de verificación es: $codigo";

            // 5. Enviar correo
            $mail->send();
            $_SESSION['codigo_verificacion'] = $codigo;
            $resultado = array('accion' => 'comprobar', 'resultado' => 1, 'mensaje' => $mensaje);
        } catch (Exception $e) {
            error_log($e->getMessage());
            $resultado = ['accion' => 'error', 'mensaje' => $e->getMessage()];
        }
        return $resultado;
    }
}
