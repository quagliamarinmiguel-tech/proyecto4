<?php

require_once "mainModel.php";

class imagenModelo extends mainModel {

    /*----------  Guardar nueva imagen en la tabla  ----------*/
    protected function guardar_imagen_modelo($ruta, $tipo){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO imagen(ruta, tipo_imagen) 
            VALUES(:Ruta, :Tipo)
        ");
        $sql->bindParam(":Ruta", $ruta, PDO::PARAM_STR);
        $sql->bindParam(":Tipo", $tipo, PDO::PARAM_STR);
        $sql->execute();
        return $sql;
    }



public function guardar_imagen_modelo2($ruta, $tipo){
    try {
        $pdo = mainModel::conectar();
        $sql = $pdo->prepare("
            INSERT INTO imagen(ruta, tipo_imagen)
            VALUES(:Ruta, :Tipo)
        ");
        $sql->bindParam(":Ruta", $ruta, PDO::PARAM_STR);
        $sql->bindParam(":Tipo", $tipo, PDO::PARAM_STR);

        if ($sql->execute()) {
            return $pdo->lastInsertId(); 
        } else {
            error_log("Fallo en execute() al guardar imagen: $ruta, tipo: $tipo");
            return 0;
        }
    } catch (Exception $e) {
        error_log("Error en guardar_imagen_modelo2(): " . $e->getMessage());
        return 0;
    }
}




public static function actualizar_ruta_imagen_modelo($idImagen, $nuevaRuta){
    $sql = mainModel::conectar()->prepare("
        UPDATE imagen SET ruta = :Ruta WHERE id_imagen = :ID
    ");

    $sql->bindParam(":Ruta", $nuevaRuta, PDO::PARAM_STR);
    $sql->bindParam(":ID", $idImagen, PDO::PARAM_INT);

    return $sql;
}


    protected function obtener_imagenes_modelo($tipo, $inicio, $limite){
        $sql = mainModel::conectar()->prepare("
            SELECT id_imagen, ruta, tipo_imagen 
            FROM imagen 
            WHERE tipo_imagen = :Tipo 
            ORDER BY id_imagen DESC 
            LIMIT :Inicio, :Limite
        ");
        $sql->bindParam(":Tipo", $tipo, PDO::PARAM_STR);
        $sql->bindParam(":Inicio", $inicio, PDO::PARAM_INT);
        $sql->bindParam(":Limite", $limite, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    protected function contar_imagenes_modelo($tipo){
        $sql = mainModel::conectar()->prepare("
            SELECT COUNT(id_imagen) AS total FROM imagen WHERE tipo_imagen = :Tipo
        ");
        $sql->bindParam(":Tipo", $tipo, PDO::PARAM_STR);
        $sql->execute();
        return $sql;
    }



protected function contar_imagenes_filtradas_modelo($tipo, $excluirIDs){
    $db = mainModel::conectar();

    if(empty($excluirIDs)){
        $sql = $db->prepare("SELECT COUNT(*) AS total FROM imagen WHERE tipo_imagen = :Tipo");
        $sql->bindParam(":Tipo", $tipo, PDO::PARAM_STR);
    } else {
        $placeholders = implode(',', array_fill(0, count($excluirIDs), '?'));
        $sql = $db->prepare("SELECT COUNT(*) AS total FROM imagen WHERE tipo_imagen = ? AND id_imagen NOT IN ($placeholders)");
        array_unshift($excluirIDs, $tipo);
        $sql->execute($excluirIDs);
        return $sql;
    }

    $sql->execute();
    return $sql;
}





public function obtener_imagenes_filtradas_modelo($tipo, $excluirIDs, $inicio, $limite){
    $db = mainModel::conectar();

    if(empty($excluirIDs)){
        $sql = $db->prepare("
            SELECT id_imagen, ruta FROM imagen
            WHERE tipo_imagen = :Tipo
            ORDER BY id_imagen DESC
            LIMIT :Inicio, :Limite
        ");
        $sql->bindParam(":Tipo", $tipo, PDO::PARAM_STR);
        $sql->bindParam(":Inicio", $inicio, PDO::PARAM_INT);
        $sql->bindParam(":Limite", $limite, PDO::PARAM_INT);
    } else {
        $placeholders = implode(',', array_fill(0, count($excluirIDs), '?'));
        $sql = $db->prepare("
            SELECT id_imagen, ruta FROM imagen
            WHERE tipo_imagen = ? AND id_imagen NOT IN ($placeholders)
            ORDER BY id_imagen DESC
            LIMIT $inicio, $limite
        ");
        array_unshift($excluirIDs, $tipo);
        $sql->execute($excluirIDs);
        return $sql;
    }

    $sql->execute();
    return $sql;
}










public function obtener_ids_imagenes_por_slider($tipo){
    $sql = mainModel::conectar()->prepare("
        SELECT id_imagen FROM imagen WHERE tipo_imagen = :Tipo
    ");
    $sql->bindParam(":Tipo", $tipo, PDO::PARAM_STR);
    $sql->execute();
    return array_column($sql->fetchAll(PDO::FETCH_ASSOC), 'id_imagen');
}



    protected function eliminar_imagen_modelo($id){
        $sql = mainModel::conectar()->prepare("DELETE FROM imagen WHERE id_imagen = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }





protected function buscar_imagenes_por_ruta($tipo, $texto, $excluirIDs){
    $db = mainModel::conectar();
    $texto = "%".strtolower($texto)."%";

    if(empty($excluirIDs)){
        $sql = $db->prepare("SELECT id_imagen, ruta FROM imagen WHERE tipo_imagen = :tipo AND LOWER(ruta) LIKE :texto LIMIT 10");
        $sql->bindParam(":tipo", $tipo, PDO::PARAM_STR);
        $sql->bindParam(":texto", $texto, PDO::PARAM_STR);
    } else {
        $placeholders = implode(',', array_fill(0, count($excluirIDs), '?'));
        $sql = $db->prepare("SELECT id_imagen, ruta FROM imagen WHERE tipo_imagen = ? AND LOWER(ruta) LIKE ? AND id_imagen NOT IN ($placeholders) LIMIT 10");
        array_unshift($excluirIDs, $tipo, $texto);
        $sql->execute($excluirIDs);
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }

    $sql->execute();
    return $sql->fetchAll(PDO::FETCH_ASSOC);
}

public static function obtener_imagenes_por_tipo_modelo($tipo){
    $sql = mainModel::conectar()->prepare("
        SELECT id_imagen, ruta 
        FROM imagen 
        WHERE tipo_imagen = :Tipo 
        ORDER BY id_imagen DESC
    ");
    $sql->bindParam(":Tipo", $tipo);
    $sql->execute();
    return $sql;
}








    //para sliderupdate

    protected static function obtener_imagenes_disponibles_modelo($tipo, $sliders_excluir = []) {
        $conexion = mainModel::conectar();

        $exclusion = "";
        $params = [":tipo" => $tipo];

        if (!empty($sliders_excluir)) {
            $placeholders = implode(',', array_fill(0, count($sliders_excluir), '?'));
            $exclusion = "AND NOT EXISTS (
                SELECT 1 FROM imagen_slider s
                WHERE s.id_imagen_fk = i.id_imagen
                AND s.id_slider_fk IN ($placeholders)
            )";
        }

        $sql = $conexion->prepare("
            SELECT i.id_imagen, i.tipo_imagen, i.ruta
            FROM imagen i
            WHERE i.tipo_imagen = :tipo
            $exclusion
        ");

        $sql->bindValue(":tipo", $tipo, PDO::PARAM_STR);
        foreach ($sliders_excluir as $k => $id_slider) {
            $sql->bindValue(($k+1), $id_slider, PDO::PARAM_INT);
        }

        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }



    protected static function obtener_imagenes_por_slider_modelo($id_slider) {
        $conexion = mainModel::conectar();
        $sql = $conexion->prepare("
            SELECT i.id_imagen, i.ruta
            FROM imagen_slider s
            JOIN imagen i ON i.id_imagen = s.id_imagen_fk
            WHERE s.id_slider_fk = :slider
            ORDER BY s.id_imagen_fk ASC
        ");
        $sql->bindParam(":slider", $id_slider, PDO::PARAM_INT);
        $sql->execute();

        $resultados = $sql->fetchAll(PDO::FETCH_ASSOC);
        return [
            'ids' => array_column($resultados, 'id_imagen'),
            'rutas' => array_column($resultados, 'ruta')
        ];
    }






}
