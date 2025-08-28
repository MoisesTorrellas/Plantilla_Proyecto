<?php
//require_once(__DIR__ . '/../../config/config.php');
class Conexion extends PDO
{
	private $conex;
	private $BDSG;

	public function __construct()
	{
		$this->BDSG = $this->conexionBD(_DB_HOST_, _DB_NAME_, _DB_USER_, _DB_PASS_);
	}

	public function conexionBD($host,$name,$user,$pass){
		$conexstring = "mysql:host=" . $host . ";dbname=" . $name . ";charset=utf8";
		try {
			$conexion = new PDO($conexstring, $user, $pass);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
		} catch (PDOException $e) {
			die("Conexión Fallida" . $e->getMessage());
		}
	}
	public function conex()
	{
		return $this->conex;
	}
	public function conexSG()
	{
		return $this->BDSG;
	}
}