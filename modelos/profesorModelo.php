<?php

require_once "mainModel.php";

class profesorModelo extends mainModel{

    /*--------- Modelo agregar profesor ---------*/
    protected static function agregar_profesor_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO profesor (
                ci_profesor, nombre_profesor, apellido_profesor,
                correo, telefono_profesor, descripcion,
                profe_mostrar, imagen_profesor_fk, estado_profesor
            )
            VALUES (
                :CI, :Nombre, :Apellido,
                :Correo, :Telefono, :Descripcion,
                :Mostrar, :Imagen, :Estado
            )
        ");

        $sql->bindParam(":CI", $datos['CI']);
        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":Apellido", $datos['Apellido']);
        $sql->bindParam(":Correo", $datos['Correo']);
        $sql->bindParam(":Telefono", $datos['Telefono']);
        $sql->bindParam(":Descripcion", $datos['Descripcion']);
        $sql->bindParam(":Mostrar", $datos['Mostrar']);
        $sql->bindParam(":Imagen", $datos['Imagen'], PDO::PARAM_INT);
        $sql->bindParam(":Estado", $datos['Estado']);

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo datos profesor ---------*/
    
    protected static function datos_profesor_modelo($tipo, $id){
        if($tipo == "Unico"){
            $sql = mainModel::conectar()->prepare("
                SELECT p.*, i.ruta 
                FROM profesor p 
                INNER JOIN imagen i ON p.imagen_profesor_fk = i.id_imagen 
                WHERE p.id_profesor = :ID
            ");
            $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        } elseif($tipo == "Conteo"){
            $sql = mainModel::conectar()->prepare("SELECT id_profesor FROM profesor");
        }

        $sql->execute();
        return $sql;
    }


    /*--------- Modelo eliminar profesor ---------*/
    protected static function eliminar_profesor_modelo($id){
        $sql = mainModel::conectar()->prepare("DELETE FROM profesor WHERE id_profesor = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo archivar profesor ---------*/
    protected static function archivar_profesor_modelo($id){
        $sql = mainModel::conectar()->prepare("UPDATE profesor SET estado_profesor = 0 WHERE id_profesor = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo restaurar profesor ---------*/
    protected static function restaurar_profesor_modelo($id){
        $sql = mainModel::conectar()->prepare("UPDATE profesor SET estado_profesor = 1 WHERE id_profesor = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo actualizar profesor ---------*/
    protected static function actualizar_profesor_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            UPDATE profesor SET 
                ci_profesor = :CI,
                nombre_profesor = :Nombre,
                apellido_profesor = :Apellido,
                correo = :Correo,
                telefono_profesor = :Telefono,
                profe_mostrar = :Mostrar,
                descripcion = :Descripcion
            WHERE id_profesor = :ID
        ");

        $sql->bindParam(":CI", $datos['CI']);
        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":Apellido", $datos['Apellido']);
        $sql->bindParam(":Correo", $datos['Correo']);
        $sql->bindParam(":Telefono", $datos['Telefono']);
        $sql->bindParam(":Descripcion", $datos['Descripcion']);
        $sql->bindParam(":Mostrar", $datos['Mostrar']);
        $sql->bindParam(":ID", $datos['ID'], PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }
}
?>