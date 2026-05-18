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
                direccion_local_pais = :Pais
            WHERE id_informacion_pagina = 1
        ");

        $sql->bindParam(":Telefono", $datos['Telefono']);
        $sql->bindParam(":Correo", $datos['Correo']);
        $sql->bindParam(":Facebook", $datos['Facebook']);
        $sql->bindParam(":Instagram", $datos['Instagram']);
        $sql->bindParam(":Ciudad", $datos['Ciudad']);
        $sql->bindParam(":Pais", $datos['Pais']);

        $sql->execute();
        return $sql;
    }

    /*--------- Modelo obtener datos + rutas de imágenes ---------*/
    protected static function datos_local_modelo(){
        $sql = mainModel::conectar()->prepare("
            SELECT i.*, 
                   img1.ruta AS ruta_local, 
                   img2.ruta AS ruta_mapa, 
                   img3.ruta AS ruta_satelite
            FROM informacion_pagina i
            LEFT JOIN imagen img1 ON i.imagen_local = img1.id_imagen
            LEFT JOIN imagen img2 ON i.imagen_local_mapa = img2.id_imagen
            LEFT JOIN imagen img3 ON i.imagen_local_satelite = img3.id_imagen
            WHERE i.id_informacion_pagina = 1
        ");

        $sql->execute();
        return $sql;
    }
}
?>