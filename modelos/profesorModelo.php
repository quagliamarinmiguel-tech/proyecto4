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

        
        return SupabaseApi::rpcWithToken('actualizar_teacher_con_img', $params, $accessToken);
    }
    
    

    public static function archivar_profesor_supabase(int $id, string $accessToken): array{
        return SupabaseApi::patchWithToken('teacher', ['id' => 'eq.' . $id], ['state' => 0], $accessToken);
    }

    public static function restaurar_profesor_supabase(int $id, string $accessToken): array{
        return SupabaseApi::patchWithToken('teacher', ['id' => 'eq.' . $id], ['state' => 1], $accessToken);
    }
    

    //lista de profesor list
    public static function listar_profesores_supabase(array $filtros, string $accessToken, int $limit, int $offset): array {
        $params = [
            'p_state'    => (int) ($filtros['state'] ?? 1),
            'p_busqueda' => $filtros['busqueda'] ?? null,
            'p_id'       => isset($filtros['id']) && $filtros['id'] !== '' ? (int) $filtros['id'] : null,
            'p_ci'       => $filtros['ci'] ?? null,
            'p_orden'    => $filtros['orden'] ?? 'default',
            'p_limit'    => $limit,
            'p_offset'   => $offset,
        ];

        // Clave única basada en TODOS los parámetros que afectan el resultado
        $claveCache = 'profesores_' . md5(json_encode($params));

        $dataCache = mainModel::leerCache($claveCache);
        if ($dataCache !== null) {
            return $dataCache;
        }

        $resultado = SupabaseApi::rpcWithToken('listar_profesores', $params, $accessToken);

        if (!empty($resultado['ok'])) {
            mainModel::guardarCache($claveCache, $resultado, 3600);
        }

        return $resultado;
    }
    
    
   
}
?>