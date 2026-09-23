<?php

require_once "mainModel.php";

class profesorModelo extends mainModel{

    
    public static function guardar_profesor_con_imagen_supabase(array $datos, string $accessToken): array{
        $params = [
            'p_ci' => $datos['ci'],
            'p_name' => $datos['nombre'],
            'p_last_name' => $datos['apellido'],
            'p_phone' => $datos['telefono'] ?? '',
            'p_email' => $datos['correo'],
            'p_description' => $datos['descripcion'] ?? '',
            'p_show_teacher' => (int) $datos['mostrar'],
            'p_img_route' => $datos['ruta_imagen'], 
            'p_img_type' => 'prof', 
            'p_state' => 1, 
        ];

        return SupabaseApi::rpcWithToken('guardar_teacher_con_img', $params, $accessToken);
    }

    // para teacher_update, se trae los datos necesarios
    // img(route) ahora route
    public static function obtener_profesor_con_imagen_supabase(int $id, string $accessToken): array{
        return SupabaseApi::getWithToken('teacher', [
            'select' => 'id, ci, name, last_name, phone, email, description, show_teacher, state, img_id, img(route)',
            'id' => 'eq.' . $id,
        ], $accessToken);
    }
    
    
    //para el filtro del nombre del profe que hay q traerlo si se filtra porque el select lo q envia es el id, no el nombre del profe
    public static function obtener_profesor_supabase(int $id, string $accessToken): array{
        return SupabaseApi::getWithToken('teacher', [
            'select' => 'id,name,last_name',
            'id' => 'eq.' . $id,
        ], $accessToken);
    }
    
    
    public static function actualizar_profesor_con_imagen_supabase(array $datos, string $accessToken): array{
        $params = [
            'p_teacher_id' => (int) $datos['id'],
            'p_ci' => $datos['ci'],
            'p_name' => $datos['nombre'],
            'p_last_name' => $datos['apellido'],
            'p_phone' => $datos['telefono'] ?? '',
            'p_email' => $datos['correo'],
            'p_description' => $datos['descripcion'] ?? '',
            'p_show_teacher' => (bool) $datos['mostrar'],
            'p_new_img_route' => $datos['nueva_ruta_imagen'] ?? null,
        ];

        //var_dump($params);
        //die();
        
        return SupabaseApi::rpcWithToken('actualizar_teacher_con_img', $params, $accessToken);
    }
    
    

    public static function archivar_profesor_supabase(int $id, string $accessToken): array{
        return SupabaseApi::patchWithToken('teacher', ['id' => 'eq.' . $id], ['state' => 0], $accessToken);
    }

    public static function restaurar_profesor_supabase(int $id, string $accessToken): array{
        return SupabaseApi::patchWithToken('teacher', ['id' => 'eq.' . $id], ['state' => 1], $accessToken);
    }
    
    
    
    
    
    /*--------- Modelo datos profesor ---------*/
    /*
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
    }*/


    /*--------- Modelo eliminar profesor ---------/
    protected static function eliminar_profesor_modelo($id){
        $sql = mainModel::conectar()->prepare("DELETE FROM profesor WHERE id_profesor = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo archivar profesor ---------/
    protected static function archivar_profesor_modelo($id){
        $sql = mainModel::conectar()->prepare("UPDATE profesor SET estado_profesor = 0 WHERE id_profesor = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }

    /*--------- Modelo restaurar profesor ---------/
    protected static function restaurar_profesor_modelo($id){
        $sql = mainModel::conectar()->prepare("UPDATE profesor SET estado_profesor = 1 WHERE id_profesor = :ID");
        $sql->bindParam(":ID", $id, PDO::PARAM_INT);
        $sql->execute();
        return $sql;
    }*/

    /*--------- Modelo actualizar profesor ---------/
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
    }*/
}
?>