<?php

require_once "mainModel.php";

class anuncioModelo extends mainModel {

    /*--------- Modelo agregar anuncio ---------*/
    protected static function agregar_anuncio_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO anuncio_publicidad(
                titulo_publicidad, texto_publicidad, fondo_publicidad,
                imagen_publicidad_fk, link_publicidad
            ) VALUES (
                :Titulo, :Texto, :Fondo, :Imagen, :Curso
            )
        ");

        $sql->bindParam(":Titulo", $datos['Titulo']);
        $sql->bindParam(":Texto", $datos['Texto']);
        $sql->bindParam(":Fondo", $datos['Fondo']);
        $sql->bindParam(":Imagen", $datos['Imagen']);
        $sql->bindParam(":Curso", $datos['Curso']);

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo actualizar anuncio ---------*/
    protected static function actualizar_anuncio_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            UPDATE anuncio_publicidad SET
                titulo_publicidad = :Titulo,
                texto_publicidad = :Texto,
                fondo_publicidad = :Fondo,
                imagen_publicidad_fk = :Imagen,
                link_publicidad = :Curso
            WHERE id_publicidad = :ID
        ");

        $sql->bindParam(":Titulo", $datos['Titulo']);
        $sql->bindParam(":Texto", $datos['Texto']);
        $sql->bindParam(":Fondo", $datos['Fondo']);
        $sql->bindParam(":Imagen", $datos['Imagen']);
        $sql->bindParam(":Curso", $datos['Curso']);
        $sql->bindParam(":ID", $datos['ID']);

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo eliminar anuncio ---------*/
    protected static function eliminar_anuncio_modelo($id){
        $sql = mainModel::conectar()->prepare("DELETE FROM anuncio_publicidad WHERE id_publicidad = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener datos de anuncio ---------*/
    protected static function datos_anuncio_modelo($tipo, $id){
        if($tipo == "Unico"){
            $sql = mainModel::conectar()->prepare("SELECT * FROM anuncio_publicidad WHERE id_publicidad = :ID");
            $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        } elseif($tipo == "Conteo"){
            $sql = mainModel::conectar()->prepare("SELECT id_publicidad FROM anuncio_publicidad");
        }

        $sql->execute();
        return $sql;
    }
}
?>