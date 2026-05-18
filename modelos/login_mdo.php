<?php
require_once __DIR__ . '/../config/conection.php';

require_once "mainModel.php";

class loginModelo extends mainModel{

		/*--------- Modelo iniciar sesion ---------*/
		public function iniciar_sesion_modelo($datos) {
    		$sql = "SELECT * FROM usuario WHERE Nombre = :usuario AND Password = :clave AND estado_usuario !=0";
    		$stmt = Conexion::conectar()->prepare($sql);
    		$stmt->bindParam(':usuario', $datos['Usuario']);
            $stmt->bindParam(':clave', $datos['Clave']);
    		$stmt->execute();
    		return $stmt;
		}
	}



?>