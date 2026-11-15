<?php
	
	require_once "mainModel.php";

	class usuarioModelo extends mainModel{
        
        
        
        

    public static function crear_staff_desde_auth_supabase(
        string $userId, string $email, string $ci,
        string $name, string $lastName, int $roleNumber,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('crear_staff_desde_auth', [
            'p_user_id'     => $userId,
            'p_email'       => $email,
            'p_ci'          => $ci,
            'p_name'        => $name,
            'p_last_name'   => $lastName,
            'p_role_number' => $roleNumber,
        ], $accessToken);
    }

   

    public static function verificar_staff_duplicado_supabase(
        string $correo,
        string $ci,
        string $accessToken,
        ?int $excludeId = null
    ): array {
        $filtros = [
            'select' => 'id,ci,email',
            'or'     => '(email.eq.' . $correo . ',ci.eq.' . $ci . ')',
            'limit'  => '1',
        ];

        if ($excludeId !== null && $excludeId > 0) {
            $filtros['id'] = 'neq.' . $excludeId;
        }

        return SupabaseApi::getWithToken('staff', $filtros, $accessToken);
    }


//lista el personal del local, con cache
    public static function listar_staff_supabase(array $filtros, string $accessToken, int $limit, int $offset): array {
        $params = [
            'p_busqueda' => $filtros['busqueda'] ?? null,
            'p_rol'      => $filtros['rol'] ?? null,
            'p_state'    => $filtros['state'] ?? null,
            'p_orden'    => $filtros['orden'] ?? 'reciente',
            'p_limit'    => $limit,
            'p_offset'   => $offset,
        ];

        // Clave única basada en todos los parámetros que afectan el resultado
        $claveCache = 'staff_' . md5(json_encode($params));

        // Intentar leer de caché
        $dataCache = mainModel::leerCache($claveCache);
        if ($dataCache !== null) {
            return $dataCache;
        }

        // Consultar a Supabase
        $resultado = SupabaseApi::rpcWithToken('listar_staff', $params, $accessToken);

        // Guardar en caché si fue exitoso (TTL 1 hora)
        if (!empty($resultado['ok'])) {
            mainModel::guardarCache($claveCache, $resultado, 3600);
        }

        return $resultado;
    }
        
        
    public static function actualizar_estado_profile_supabase(int $staffId, int $state, string $accessToken): array {
        // Obtener profile_id
        $staff = SupabaseApi::getWithToken('staff', [
            'select' => 'profile_id',
            'id'     => 'eq.' . $staffId,
            'limit'  => '1',
        ], $accessToken);

        if (!$staff['ok'] || empty($staff['data'][0]['profile_id'])) {
            return ['ok' => false, 'message' => 'Staff no encontrado'];
        }

        $profileId = (int) $staff['data'][0]['profile_id'];

        // PATCH al profile
        return SupabaseApi::patchWithToken('profile', [
            'id' => 'eq.' . $profileId,
        ], [
            'state' => $state,
        ], $accessToken);
    }


        
//obtener datos del usuario a actualizar en user update
    public static function obtener_staff_por_id_supabase(int $staffId, string $accessToken): array {
        return SupabaseApi::getWithToken('staff', [
            'select' => 'id,ci,name,last_name,email,profile!inner(id,state,role_id,user_id,role!inner(id,role,role_number))',
            'id'     => 'eq.' . $staffId,
            'limit'  => '1',
        ], $accessToken);
    }

    public static function actualizar_staff_desde_auth_supabase(
        int $staffId,
        string $ci,
        string $name,
        string $lastName,
        string $email,
        int $roleNumber,
        int $state,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('actualizar_staff_desde_auth', [
            'p_staff_id'    => $staffId,
            'p_ci'          => $ci,
            'p_name'        => $name,
            'p_last_name'   => $lastName,
            'p_email'       => $email,
            'p_role_number' => $roleNumber,
            'p_state'       => $state,
        ], $accessToken);
    }
        
        
//de user update, actualiza el cargo        
    public static function actualizar_rol_staff_supabase(int $profileId, int $roleId, string $accessToken): array {
        return SupabaseApi::patchWithToken('profile', [
            'id' => 'eq.' . $profileId,
        ], [
            'role_id' => $roleId,
        ], $accessToken);
    }
        
        
//busca staff, y lo elimina junto con su profile, despues el controlador llama a auth user pa eliminar user
    public static function eliminar_staff_cascada_supabase(int $staffId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('eliminar_staff_cascada', [
            'p_staff_id' => $staffId,
        ], $accessToken);
    }
//elimina user despues de eliminar staff y profile
    public static function eliminar_auth_user_supabase(string $userId): array {
        return SupabaseApi::adminDeleteUser($userId);
    }
        
        
        
//la llamada a la rpc para la lista de activity list
    public static function listar_activity_log_supabase(array $filtros, string $accessToken, int $limit, int $offset): array {
        $params = [
            'p_busqueda'    => $filtros['busqueda'] ?? null,
            'p_accion'      => $filtros['accion'] ?? null,
            'p_tabla'       => $filtros['tabla'] ?? null,
            'p_fecha_desde' => $filtros['fecha_desde'] ?? null,
            'p_fecha_hasta' => $filtros['fecha_hasta'] ?? null,
            'p_orden'       => $filtros['orden'] ?? 'reciente',
            'p_limit'       => $limit,
            'p_offset'      => $offset,
        ];
        return SupabaseApi::rpcWithToken('listar_activity_log', $params, $accessToken);
    }
        
        
 

//lo usa user config
    public static function datos_staff_por_auth_user_supabase(string $authUserId, string $accessToken): array {
        return SupabaseApi::getWithToken('staff', [
            'select' => 'id, ci, name, last_name, email, ' .
                        'profile!inner(user_id, state, role!inner(role_number))',
            'profile.user_id' => 'eq.' . $authUserId,
            'limit' => '1',
        ], $accessToken);
    }


	}