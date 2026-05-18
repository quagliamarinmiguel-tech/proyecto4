<?php

require_once "mainModel.php";

class respaldoModelo extends mainModel{

    /*--------- Modelo generar respaldo ---------
    protected static function generar_respaldo_modelo(){
        
        try {
            $conexion = mainModel::conectar();
            $sql = "";
            
            // Encabezado del archivo SQL
            $sql .= "-- =============================================\n";
            $sql .= "-- Respaldo Base de Datos CEPROCA\n";
            $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- =============================================\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            // Obtener todas las tablas
            $tablas = $conexion->query("SHOW TABLES");
            
            while($tabla = $tablas->fetch(PDO::FETCH_NUM)){
                $nombre_tabla = $tabla[0];
                
                $sql .= "-- =============================================\n";
                $sql .= "-- Tabla: $nombre_tabla\n";
                $sql .= "-- =============================================\n\n";
                
                // Obtener estructura de la tabla
                $estructura = $conexion->query("SHOW CREATE TABLE `$nombre_tabla`");
                $create_table = $estructura->fetch(PDO::FETCH_ASSOC);
                
                $sql .= "DROP TABLE IF EXISTS `$nombre_tabla`;\n";
                $sql .= $create_table['Create Table'] . ";\n\n";
                
                // Obtener datos de la tabla
                $datos = $conexion->query("SELECT * FROM `$nombre_tabla`");
                $num_filas = $datos->rowCount();
                
                if($num_filas > 0){
                    $sql .= "-- Datos de la tabla $nombre_tabla\n";
                    
                    while($fila = $datos->fetch(PDO::FETCH_ASSOC)){
                        $columnas = array_keys($fila);
                        $valores = array_values($fila);
                        
                        // Escapar valores
                        $valores_escapados = array_map(function($valor) use ($conexion) {
                            if($valor === null){
                                return 'NULL';
                            }
                            return $conexion->quote($valor);
                        }, $valores);
                        
                        $sql .= "INSERT INTO `$nombre_tabla` (`" . implode('`, `', $columnas) . "`) VALUES (" . implode(', ', $valores_escapados) . ");\n";
                    }
                    
                    $sql .= "\n";
                }
            }
            
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            return [
                'success' => true,
                'sql' => $sql
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }
*/
    
    
        protected static function generar_respaldo_modelo(){
        
        try {
            $conexion = mainModel::conectar();
            $sql = "";
            
            // Encabezado del archivo SQL
            $sql .= "-- =============================================\n";
            $sql .= "-- Respaldo Base de Datos CEPROCA\n";
            $sql .= "-- Fecha: " . date('Y-m-d H:i:s') . "\n";
            $sql .= "-- =============================================\n\n";
            $sql .= "SET FOREIGN_KEY_CHECKS=0;\n\n";

            // Obtener todas las tablas
            $tablas = $conexion->query("SHOW TABLES");
            
            while($tabla = $tablas->fetch(PDO::FETCH_NUM)){
                $nombre_tabla = $tabla[0];
                
                $sql .= "-- =============================================\n";
                $sql .= "-- Tabla: $nombre_tabla\n";
                $sql .= "-- =============================================\n\n";
                
                // Obtener estructura de la tabla
                $estructura = $conexion->query("SHOW CREATE TABLE `$nombre_tabla`");
                $create_table = $estructura->fetch(PDO::FETCH_ASSOC);
                
                $sql .= "DROP TABLE IF EXISTS `$nombre_tabla`;\n";
                $sql .= $create_table['Create Table'] . ";\n\n";
                
                // Obtener datos de la tabla
                $datos = $conexion->query("SELECT * FROM `$nombre_tabla`");
                $num_filas = $datos->rowCount();
                
                if($num_filas > 0){
                    $sql .= "-- Datos de la tabla $nombre_tabla\n";
                    
                    while($fila = $datos->fetch(PDO::FETCH_ASSOC)){
                        $columnas = array_keys($fila);
                        $valores = array_values($fila);
                        
                        // Escapar valores
                        $valores_escapados = array_map(function($valor) use ($conexion) {
                            if($valor === null){
                                return 'NULL';
                            }
                            return $conexion->quote($valor);
                        }, $valores);
                        
                        $sql .= "INSERT INTO `$nombre_tabla` (`" . implode('`, `', $columnas) . "`) VALUES (" . implode(', ', $valores_escapados) . ");\n";
                    }
                    
                    $sql .= "\n";
                }
            }
            
            $sql .= "SET FOREIGN_KEY_CHECKS=1;\n";
            
            return [
                'success' => true,
                'sql' => $sql
            ];
            
        } catch (Exception $e) {
            return [
                'success' => false,
                'mensaje' => $e->getMessage()
            ];
        }
    }

    /*--------- Modelo actualizar fecha ultimo respaldo ---------*/
    protected static function actualizar_fecha_respaldo_modelo(){
        try {
            $conexion = mainModel::conectar();
            $fecha_actual = date('Y-m-d H:i:s');
            
            $sql = $conexion->prepare("UPDATE informacion_pagina SET fecha_ultimo_respaldo = :fecha WHERE id_informacion_pagina = 1");
            $sql->bindParam(":fecha", $fecha_actual);
            
            if($sql->execute()){
                return ['success' => true];
            }else{
                return ['success' => false];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'mensaje' => $e->getMessage()];
        }
    }

    /*--------- Modelo obtener fecha ultimo respaldo ---------*/
    protected static function obtener_fecha_respaldo_modelo(){
        try {
            $conexion = mainModel::conectar();
            
            $sql = $conexion->query("SELECT fecha_ultimo_respaldo FROM informacion_pagina WHERE id_informacion_pagina = 1");
            
            if($sql->rowCount() > 0){
                $datos = $sql->fetch();
                return [
                    'success' => true,
                    'fecha' => $datos['fecha_ultimo_respaldo']
                ];
            }else{
                return [
                    'success' => false,
                    'fecha' => null
                ];
            }
            
        } catch (Exception $e) {
            return ['success' => false, 'fecha' => null];
        }
    }

    
    
    
    
} /* Fin modelo */
?>
