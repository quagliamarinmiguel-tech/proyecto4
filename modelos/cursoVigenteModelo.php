<?php

require_once "mainModel.php";

class cursoVigenteModelo extends mainModel {

    /*--------- Modelo agregar curso vigente ---------*/
    protected static function agregar_curso_vigente_modelo($datos) {
        $sql = mainModel::conectar()->prepare("
            INSERT INTO curso_vigente(
                id_curso_fk, 
                id_cohorte_fk, 
                pago_inscripcion, 
                pago_modulo, 
                oferta
            ) VALUES (
                :Curso, :Cohorte, :Inscripcion, :Modulo, :Oferta
            )
        ");

        $sql->bindParam(":Curso", $datos['Curso']);
        $sql->bindParam(":Cohorte", $datos['Cohorte']);
        $sql->bindParam(":Inscripcion", $datos['Inscripcion']);
        $sql->bindParam(":Modulo", $datos['Modulo']);
        $sql->bindParam(":Oferta", $datos['Oferta']);

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo eliminar curso vigente ---------*/
    protected static function eliminar_curso_vigente_modelo($id) {
        $sql = mainModel::conectar()->prepare("
            DELETE FROM curso_vigente WHERE id_curso_vigente = :ID
        ");
        $sql->bindParam(":ID", $id);
        $sql->execute();
        return $sql;
    }




    protected static function datos_curso_vigente_modelo($tipo, $id) {
        if ($tipo == "Unico") {
            $sql = mainModel::conectar()->prepare("
                SELECT 
                    cv.id_curso_vigente,
                    cv.id_curso_fk,
                    cv.id_cohorte_fk,
                    cv.pago_inscripcion,
                    cv.pago_modulo,
                    cv.oferta,
                    c.nombre_curso, 
                    co.numero_cohorte, 
                    co.anio_cohorte, 
                    co.tipo_cohorte
                FROM curso_vigente cv
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
                WHERE cv.id_curso_vigente = :ID
            ");
            $sql->bindParam(":ID", $id);
        } elseif ($tipo == "Conteo") {
            $sql = mainModel::conectar()->prepare("
                SELECT id_curso_vigente FROM curso_vigente
            ");
        }

        $sql->execute();
        return $sql;
    }





    /*--------- Modelo actualizar curso vigente ---------*/
    protected static function actualizar_curso_vigente_modelo($datos) {
        $sql = mainModel::conectar()->prepare("
            UPDATE curso_vigente SET 
                pago_inscripcion = :Inscripcion,
                pago_modulo = :Modulo,
                oferta = :Oferta
            WHERE id_curso_vigente = :ID
        ");

        $sql->bindParam(":Inscripcion", $datos['Inscripcion']);
        $sql->bindParam(":Modulo", $datos['Modulo']);
        $sql->bindParam(":Oferta", $datos['Oferta']);
        $sql->bindParam(":ID", $datos['ID']);

        $sql->execute();
        return $sql;
    }

}