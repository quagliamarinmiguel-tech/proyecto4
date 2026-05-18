<?php

class evaluacionModelo extends mainModel {

    /*--------- Modelo: Agregar evaluación ---------
    protected static function agregar_evaluacion_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO evaluacion(tipo_evaluacion, obj, porcentaje, fecha_evaluacion, id_seccion, archivar_evaluacion)
            VALUES(:Tipo, :Objetivo, :Porcentaje, :Fecha, :Seccion, 1)
        ");
        $sql->bindParam(":Tipo", $datos['Tipo']);
        $sql->bindParam(":Objetivo", $datos['Objetivo']);
        $sql->bindParam(":Porcentaje", $datos['Porcentaje']);
        $sql->bindParam(":Fecha", $datos['Fecha']);
        $sql->bindParam(":Seccion", $datos['Seccion']);
        $sql->execute();
        return $sql;
    }
*/


    protected static function agregar_evaluacion_modelo($datos){
        try {
            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                INSERT INTO evaluacion(tipo_evaluacion, obj, porcentaje, fecha_evaluacion, id_seccion, archivar_evaluacion)
                VALUES(:Tipo, :Objetivo, :Porcentaje, :Fecha, :Seccion, 1)
            ");
            $sql->bindParam(":Tipo", $datos['Tipo']);
            $sql->bindParam(":Objetivo", $datos['Objetivo']);
            $sql->bindParam(":Porcentaje", $datos['Porcentaje']);
            $sql->bindParam(":Fecha", $datos['Fecha']);
            $sql->bindParam(":Seccion", $datos['Seccion']);

            if ($sql->execute()) {
                return $pdo->lastInsertId(); 
            } else {
                return 0;
            }
        } catch (Exception $e) {
            error_log("Error al insertar evaluación: " . $e->getMessage());
            return 0;
        }
    }



    /*--------- Modelo: Agregar puntaje ---------*/
    protected static function agregar_puntaje_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO puntaje_estudiante(id_evaluacion, id_inscripcion, puntaje, archivar_puntaje)
            VALUES(:Evaluacion, :Inscripcion, :Puntaje, 1)
        ");
        $sql->bindParam(":Evaluacion", $datos['Evaluacion']);
        $sql->bindParam(":Inscripcion", $datos['Inscripcion']);
        $sql->bindParam(":Puntaje", $datos['Puntaje']);
        $sql->execute();
        return $sql;
    }






    protected static function actualizar_evaluacion_modelo($datos){
        try {
            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("
                UPDATE evaluacion
                SET tipo_evaluacion = :Tipo,
                    obj = :Objetivo,
                    porcentaje = :Porcentaje,
                    fecha_evaluacion = :Fecha
                WHERE id_evaluacion = :ID
                AND archivar_evaluacion = 1
            ");

            $sql->bindParam(":Tipo", $datos['Tipo']);
            $sql->bindParam(":Objetivo", $datos['Objetivo']);
            $sql->bindParam(":Porcentaje", $datos['Porcentaje']);
            $sql->bindParam(":Fecha", $datos['Fecha']);
            $sql->bindParam(":ID", $datos['ID'], PDO::PARAM_INT);

            return $sql->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar evaluación: " . $e->getMessage());
            return false;
        }
    }









    protected static function actualizar_o_insertar_puntaje_modelo($datos){
        try {
            $pdo = mainModel::conectar();

            // Verificar si ya existe el puntaje
            $check = $pdo->prepare("
                SELECT COUNT(*) FROM puntaje_estudiante
                WHERE id_evaluacion = :Evaluacion AND id_inscripcion = :Inscripcion
            ");
            $check->bindParam(":Evaluacion", $datos['Evaluacion'], PDO::PARAM_INT);
            $check->bindParam(":Inscripcion", $datos['Inscripcion'], PDO::PARAM_INT);
            $check->execute();

            if($check->fetchColumn() > 0){
                // Actualizar puntaje existente
                $update = $pdo->prepare("
                    UPDATE puntaje_estudiante
                    SET puntaje = :Puntaje
                    WHERE id_evaluacion = :Evaluacion AND id_inscripcion = :Inscripcion
                ");
            } else {
                // Insertar nuevo puntaje
                $update = $pdo->prepare("
                    INSERT INTO puntaje_estudiante(id_evaluacion, id_inscripcion, puntaje, archivar_puntaje)
                    VALUES(:Evaluacion, :Inscripcion, :Puntaje, 1)
                ");
            }

            $update->bindParam(":Evaluacion", $datos['Evaluacion'], PDO::PARAM_INT);
            $update->bindParam(":Inscripcion", $datos['Inscripcion'], PDO::PARAM_INT);
            $update->bindParam(":Puntaje", $datos['Puntaje']);

            return $update->execute();
        } catch (Exception $e) {
            error_log("Error al actualizar/insertar puntaje: " . $e->getMessage());
            return false;
        }
    }







    /*--------- Modelo: Obtener evaluaciones por sección ---------
    protected static function obtener_evaluaciones_modelo($id_seccion, $inicio, $limite){
        $sql = mainModel::conectar()->prepare("
            SELECT * FROM evaluacion 
            WHERE id_seccion_fk = :id AND estado_evaluacion = 'Activa'
            ORDER BY fecha_evaluacion ASC
            LIMIT :inicio, :limite
        ");
        $sql->bindParam(":id", $id_seccion, PDO::PARAM_INT);
        $sql->bindParam(":inicio", $inicio, PDO::PARAM_INT);
        $sql->bindParam(":limite", $limite, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }*/

/* porcentaje q no servia
protected static function sumar_porcentajes_por_seccion_modelo($id_seccion, $id_excluir = null){
    $sql = mainModel::conectar()->prepare("
        SELECT SUM(porcentaje) FROM evaluacion 
        WHERE id_seccion = :id 
          AND archivar_evaluacion = 1
          " . ($id_excluir ? "AND id_evaluacion != :excluir" : "") . "
    ");
    $sql->bindParam(":id", $id_seccion);
    if($id_excluir){
        $sql->bindParam(":excluir", $id_excluir);
    }
    $sql->execute();
    return $sql;
}*/

public static function sumar_porcentajes_por_seccion_modelo($id_seccion, $id_excluir = null){
    $sql = mainModel::conectar()->prepare("
        SELECT COALESCE(SUM(porcentaje), 0) FROM evaluacion 
        WHERE id_seccion = :id 
          AND archivar_evaluacion = 1
          " . ($id_excluir ? "AND id_evaluacion != :excluir" : "") . "
    ");
    $sql->bindParam(":id", $id_seccion);
    if($id_excluir){
        $sql->bindParam(":excluir", $id_excluir);
    }
    $sql->execute();
    return $sql;
}















}