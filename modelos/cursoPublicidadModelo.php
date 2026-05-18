<?php
require_once "mainModel.php";

class cursoPublicidadModelo extends mainModel {

    /*--------- Modelo agregar publicidad ---------*/
    protected static function agregar_publicidad_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO curso_publicidad (
                id_curso, id_imagen_materia_fk,
                prefacio, materia_descripcion,
                cont1, cont2, cont3, cont4, cont5,
                cont6, cont7, cont8, cont9, cont10,
                requisito1, requisito2, requisito3, requisito4,
                icono1, icono2, icono3, estado_curso_publicidad ,clase
                
            ) VALUES (
                :id_curso, :imagen_fk,
                :prefacio, :descripcion,
                :cont1, :cont2, :cont3, :cont4, :cont5,
                :cont6, :cont7, :cont8, :cont9, :cont10,
                :req1, :req2, :req3, :req4,
                :ico1, :ico2, :ico3, 1 ,:clase
                
            )
        ");

        $sql->bindParam(":id_curso", $datos['id_curso']);
        $sql->bindParam(":imagen_fk", $datos['id_imagen_materia_fk']);
        $sql->bindParam(":prefacio", $datos['prefacio']);
        $sql->bindParam(":clase", $datos['clase']);
        $sql->bindParam(":descripcion", $datos['materia_descripcion']);

        for($i = 1; $i <= 10; $i++){
            $sql->bindParam(":cont$i", $datos["cont$i"]);
        }

        for($i = 1; $i <= 4; $i++){
            $sql->bindParam(":req$i", $datos["requisito$i"]);
        }

        for($i = 1; $i <= 3; $i++){
            $sql->bindParam(":ico$i", $datos["icono$i"], PDO::PARAM_INT);
        }        

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener datos publicidad ---------*/
    public static function datos_publicidad_modelo($tipo, $id){
        if($tipo == "Unico"){
            $sql = mainModel::conectar()->prepare("
                SELECT cp.*, i.ruta AS ruta_imagen, c.nombre_curso, c.tipo_curso
                FROM curso_publicidad cp
                INNER JOIN imagen i ON cp.id_imagen_materia_fk = i.id_imagen
                INNER JOIN curso c ON cp.id_curso = c.id_curso
                WHERE cp.id_materia = :ID
            ");
            $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        } elseif($tipo == "Conteo"){
            $sql = mainModel::conectar()->prepare("SELECT id_materia FROM curso_publicidad");
        }

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo eliminar publicidad ---------*/
    protected static function eliminar_publicidad_modelo($id){
        $sql = mainModel::conectar()->prepare("DELETE FROM curso_publicidad WHERE id_materia = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo archivar publicidad ---------*/
    protected static function archivar_publicidad_modelo($id){
        $sql = mainModel::conectar()->prepare("UPDATE curso_publicidad SET estado_materia = 0 WHERE id_materia = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo restaurar publicidad ---------*/
    protected static function restaurar_publicidad_modelo($id){
        $sql = mainModel::conectar()->prepare("UPDATE curso_publicidad SET estado_materia = 1 WHERE id_materia = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }


    /*--------- Modelo actualizar publicidad ---------*/
    protected static function actualizar_publicidad_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            UPDATE curso_publicidad SET
                prefacio = :prefacio,
                materia_descripcion = :descripcion,
                cont1 = :cont1, cont2 = :cont2, cont3 = :cont3, cont4 = :cont4, cont5 = :cont5,
                cont6 = :cont6, cont7 = :cont7, cont8 = :cont8, cont9 = :cont9, cont10 = :cont10,
                requisito1 = :req1, requisito2 = :req2, requisito3 = :req3, requisito4 = :req4,
                icono1 = :ico1, icono2 = :ico2, icono3 = :ico3,
                clase = :clase
            WHERE id_materia = :ID
        ");

        $sql->bindParam(":prefacio", $datos['prefacio']);
        $sql->bindParam(":descripcion", $datos['materia_descripcion']);
        $sql->bindParam(":clase", $datos['clase']);

        for($i = 1; $i <= 10; $i++){
            $sql->bindParam(":cont$i", $datos["cont$i"]);
        }

        for($i = 1; $i <= 4; $i++){
            $sql->bindParam(":req$i", $datos["requisito$i"]);
        }

        for($i = 1; $i <= 3; $i++){
            $sql->bindParam(":ico$i", $datos["icono$i"], PDO::PARAM_INT);
        }

        $sql->bindParam(":ID", $datos['id_materia'], PDO::PARAM_INT);

        $sql->execute();
        return $sql;
    }


}