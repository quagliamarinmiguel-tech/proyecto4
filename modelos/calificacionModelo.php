<?php
require_once "mainModel.php";

class calificacionModelo extends mainModel {

    /*--------- Agregar calificación ---------*/
    protected static function agregar_calificacion_modelo($datos) {
        // Verificar si ya existe una calificación para esa inscripción y evaluación
        $check = mainModel::conectar()->prepare("
            SELECT id_inscripcion 
            FROM puntaje_estudiante 
            WHERE id_inscripcion = :ins AND id_evaluacion = :eval
        ");
        $check->bindParam(":ins", $datos['Inscripcion']);
        $check->bindParam(":eval", $datos['Evaluacion']);
        $check->execute();

        if ($check->rowCount() > 0) {
            return false; // Ya existe
        }

        // Insertar si no existe
        $sql = mainModel::conectar()->prepare("
            INSERT INTO puntaje_estudiante (id_inscripcion, id_evaluacion, puntaje)
            VALUES (:ins, :eval, :puntaje)
        ");
        $sql->bindParam(":ins",     $datos['Inscripcion']);
        $sql->bindParam(":eval",    $datos['Evaluacion']);
        $sql->bindParam(":puntaje", $datos['Puntaje']);
        $sql->execute();
        return $sql;
    }

    /*--------- Obtener calificación específica ---------*/
    protected static function datos_calificacion_modelo($inscripcion, $evaluacion) {
        $sql = mainModel::conectar()->prepare("
            SELECT * 
            FROM puntaje_estudiante
            WHERE id_inscripcion = :ins AND id_evaluacion = :eval
        ");
        $sql->bindParam(":ins",   $inscripcion);
        $sql->bindParam(":eval",  $evaluacion);
        $sql->execute();
        return $sql;
    }

    /*--------- Actualizar calificación ---------*/
    protected static function actualizar_calificacion_modelo($datos) {
        $sql = mainModel::conectar()->prepare("
            UPDATE puntaje_estudiante
            SET puntaje = :puntaje
            WHERE id_inscripcion = :ins AND id_evaluacion = :eval
        ");
        $sql->bindParam(":puntaje", $datos['Puntaje']);
        $sql->bindParam(":ins",     $datos['Inscripcion']);
        $sql->bindParam(":eval",    $datos['Evaluacion']);
        $sql->execute();
        return $sql;
    }

    /*--------- Eliminar calificación ---------*/
    protected static function eliminar_calificacion_modelo($inscripcion, $evaluacion) {
        $sql = mainModel::conectar()->prepare("
            DELETE FROM puntaje_estudiante
            WHERE id_inscripcion = :ins AND id_evaluacion = :eval
        ");
        $sql->bindParam(":ins",  $inscripcion);
        $sql->bindParam(":eval", $evaluacion);
        $sql->execute();
        return $sql;
    }

    /*--------- (Opcional) Obtener inscripciones ---------*/
    public function obtener_inscripciones_controlador() {
        $sql = mainModel::conectar()->prepare("
            SELECT i.id_inscripcion, e.nombre, e.apellido
            FROM inscripcion i
            INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
            ORDER BY e.apellido ASC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    /*--------- (Opcional) Obtener evaluaciones ---------*/
    public function obtener_evaluaciones_controlador() {
        $sql = mainModel::conectar()->prepare("
            SELECT id_evaluacion, tipo_evaluacion, fecha_evaluacion
            FROM evaluacion
            ORDER BY fecha_evaluacion DESC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }
}