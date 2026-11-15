<?php

require_once 'SERVER.php'; 

class Conexion {
    public static function conectar() {
        try {
            $conexion = new PDO(SGBD, USER, PASS);
            $conexion->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $conexion;
        } catch (PDOException $e) {
            die("Error de conexión: " . $e->getMessage());
        }
    }
}

?>