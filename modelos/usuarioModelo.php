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


//lista el personal del local
    public static function listar_staff_supabase(array $filtros, string $accessToken, int $limit, int $offset): array {
        $params = [
            'p_busqueda' => $filtros['busqueda'] ?? null,
            'p_rol'      => $filtros['rol'] ?? null,
            'p_state'    => isset($filtros['state']) && $filtros['state'] !== '' ? (int) $filtros['state'] : null,
            'p_orden'    => $filtros['orden'] ?? 'reciente',
            'p_limit'    => $limit,
            'p_offset'   => $offset,
        ];
        return SupabaseApi::rpcWithToken('listar_staff', $params, $accessToken);
    }
        
        
    public static function actualizar_estado_profile_supabase(int $staffId, int $state, string $accessToken): array {
        // 1. Obtener profile_id
        $staff = SupabaseApi::getWithToken('staff', [
            'select' => 'profile_id',
            'id'     => 'eq.' . $staffId,
            'limit'  => '1',
        ], $accessToken);

        if (!$staff['ok'] || empty($staff['data'][0]['profile_id'])) {
            return ['ok' => false, 'message' => 'Staff no encontrado'];
        }

        $profileId = (int) $staff['data'][0]['profile_id'];

        // 2. PATCH al profile
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
        
        
        
        
        
		/*--------- Modelo agregar usuario ---------*
		protected static function agregar_usuario_modelo($datos){
			$sql=mainModel::conectar()->prepare("INSERT INTO usuario(usuario_dni,usuario_nombre,usuario_apellido,usuario_telefono,usuario_direccion,usuario_email,usuario_usuario,usuario_clave,usuario_estado,usuario_privilegio) VALUES(:DNI,:Nombre,:Apellido,:Telefono,:Direccion,:Email,:Usuario,:Clave,:Estado,:Privilegio)");

			$sql->bindParam(":DNI",$datos['DNI']);
			$sql->bindParam(":Nombre",$datos['Nombre']);
			$sql->bindParam(":Apellido",$datos['Apellido']);
			$sql->bindParam(":Telefono",$datos['Telefono']);
			$sql->bindParam(":Direccion",$datos['Direccion']);
			$sql->bindParam(":Email",$datos['Email']);
			$sql->bindParam(":Usuario",$datos['Usuario']);
			$sql->bindParam(":Clave",$datos['Clave']);
			$sql->bindParam(":Estado",$datos['Estado']);
			$sql->bindParam(":Privilegio",$datos['Privilegio']);
			$sql->execute();

			return $sql;
		}


		/*--------- Modelo eliminar usuario ---------*
		protected static function eliminar_usuario_modelo($id){
			$sql=mainModel::conectar()->prepare("DELETE FROM usuario WHERE usuario_id=:ID");

			$sql->bindParam(":ID",$id);
			$sql->execute();

			return $sql;
		}


		/*--------- Modelo datos usuario ---------*
		protected static function datos_usuario_modelo($tipo,$id){
			if($tipo=="Unico"){
				$sql=mainModel::conectar()->prepare("SELECT * FROM usuario WHERE usuario_id=:ID");
				$sql->bindParam(":ID",$id);
			}elseif($tipo=="Conteo"){
				$sql=mainModel::conectar()->prepare("SELECT usuario_id FROM usuario WHERE usuario_id!='1'");
			}

			$sql->execute();
			return $sql;
		}


		/*--------- Modelo actualizar usuario ---------*
		protected static function actualizar_usuario_modelo($datos){
			$sql=mainModel::conectar()->prepare("UPDATE usuario SET usuario_dni=:DNI,usuario_nombre=:Nombre,usuario_apellido=:Apellido,usuario_telefono=:Telefono,usuario_direccion=:Direccion,usuario_email=:Email,usuario_usuario=:Usuario,usuario_clave=:Clave,usuario_estado=:Estado,usuario_privilegio=:Privilegio WHERE usuario_id=:ID");

			$sql->bindParam(":DNI",$datos['DNI']);
			$sql->bindParam(":Nombre",$datos['Nombre']);
			$sql->bindParam(":Apellido",$datos['Apellido']);
			$sql->bindParam(":Telefono",$datos['Telefono']);
			$sql->bindParam(":Direccion",$datos['Direccion']);
			$sql->bindParam(":Email",$datos['Email']);
			$sql->bindParam(":Usuario",$datos['Usuario']);
			$sql->bindParam(":Clave",$datos['Clave']);
			$sql->bindParam(":Estado",$datos['Estado']);
			$sql->bindParam(":Privilegio",$datos['Privilegio']);
			$sql->bindParam(":ID",$datos['ID']);
			$sql->execute();

			return $sql;
		}*/

	}