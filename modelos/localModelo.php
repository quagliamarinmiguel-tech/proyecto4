<?php

require_once "mainModel.php";

class localModelo extends mainModel {

    /*--------- Modelo actualizar información institucional ---------*/
    protected static function actualizar_local_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            UPDATE informacion_pagina SET
                telefono_contacto = :Telefono,
                correo_contacto = :Correo,
                link_facebook = :Facebook,
                link_instagram = :Instagram,
                direccion_local_ciudad = :Ciudad,
                direccion_local_pais = :Pais,
                nombre_local = :NombreCompleto,
                nombre_local_corto = :NombreCorto,
                rif = :Rif,
                colores_pagina = :ColoresJson,
                textos_pagina = :TextosJson
            WHERE id_informacion_pagina = 1
        ");

        $sql->bindParam(":Telefono", $datos['Telefono']);
        $sql->bindParam(":Correo", $datos['Correo']);
        $sql->bindParam(":Facebook", $datos['Facebook']);
        $sql->bindParam(":Instagram", $datos['Instagram']);
        $sql->bindParam(":Ciudad", $datos['Ciudad']);
        $sql->bindParam(":Pais", $datos['Pais']);
        $sql->bindParam(":NombreCompleto", $datos['NombreCompleto']);
        $sql->bindParam(":NombreCorto", $datos['NombreCorto']);
        $sql->bindParam(":Rif", $datos['Rif']);
        $sql->bindParam(":ColoresJson", $datos['ColoresJson']);
        $sql->bindParam(":TextosJson", $datos['TextosJson']);

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener datos del local ---------*/
    protected static function datos_local_modelo(){
        $sql = mainModel::conectar()->prepare("SELECT * FROM informacion_pagina WHERE id_informacion_pagina = 1");
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener imagenes mapeadas del local ---------*/
    public static function obtener_imagenes_local_modelo(){
        $sql = mainModel::conectar()->prepare("
            SELECT ip.nom_esp, i.ruta 
            FROM img_info_pagina ip 
            INNER JOIN imagen i ON ip.id_img_fk = i.id_imagen 
            WHERE ip.id_infop_fk = 1
        ");
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener ID de imagen vinculada ---------*/
    public static function obtener_id_imagen_local_modelo($nom_esp){
        $sql = mainModel::conectar()->prepare("SELECT id_img_fk, i.ruta FROM img_info_pagina ip INNER JOIN imagen i ON ip.id_img_fk = i.id_imagen WHERE ip.id_infop_fk = 1 AND ip.nom_esp = :NomEsp");
        $sql->bindParam(":NomEsp", $nom_esp);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo vincular imagen al local ---------*/
    public static function vincular_imagen_local_modelo($id_img, $nom_esp){
        $conexion = mainModel::conectar();
        
        $check = $conexion->prepare("SELECT id_img_infp FROM img_info_pagina WHERE id_infop_fk = 1 AND nom_esp = :NomEsp");
        $check->bindParam(":NomEsp", $nom_esp);
        $check->execute();
        
        if($check->rowCount() > 0){
            $update = $conexion->prepare("UPDATE img_info_pagina SET id_img_fk = :IdImg WHERE id_infop_fk = 1 AND nom_esp = :NomEsp");
            $update->bindParam(":IdImg", $id_img);
            $update->bindParam(":NomEsp", $nom_esp);
            $update->execute();
            return $update;
        } else {
            $insert = $conexion->prepare("INSERT INTO img_info_pagina (id_infop_fk, id_img_fk, nom_esp) VALUES (1, :IdImg, :NomEsp)");
            $insert->bindParam(":IdImg", $id_img);
            $insert->bindParam(":NomEsp", $nom_esp);
            $insert->execute();
            return $insert;
        }
    }

    /*--------- Modelo eliminar registro de imagen ---------*/
    public static function eliminar_registro_imagen_modelo($id_img){
        $sql = mainModel::conectar()->prepare("DELETE FROM imagen WHERE id_imagen = :IdImg");
        $sql->bindParam(":IdImg", $id_img);
        $sql->execute();
        return $sql;
    }
}
?>