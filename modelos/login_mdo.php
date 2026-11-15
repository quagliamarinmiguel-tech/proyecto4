<?php
require_once __DIR__ . '/../config/conection.php';

require_once "mainModel.php";

class loginModelo extends mainModel{

		public function autenticar_supabase(string $usuario, string $clave): array{
			$auth = SupabaseApi::authenticate($usuario, $clave);

			if (!$auth['ok'] || empty($auth['data']['user']['id'])) {
				return ['ok' => false, 'message' => $auth['message'], 'data' => null];
			}

			$authUserId = $auth['data']['user']['id'];
			$profile = SupabaseApi::getWithToken(AUTH_PROFILE_TABLE, [
				'select' => '*',
				AUTH_PROFILE_USER_COLUMN => 'eq.' . $authUserId,
			], $auth['data']['access_token']);

			if (!$profile['ok']) {
				return [
					'ok' => false,
					'message' => 'No se pudo consultar profiles (HTTP ' . $profile['status'] . '): ' . $profile['message'],
					'data' => null
				];
			}

			if (empty($profile['data'][0])) {
				return [
					'ok' => false,
					'message' => 'No existe un perfil cuyo users_id coincida con el usuario autenticado.',
					'data' => null
				];
			}

			$profileData = $profile['data'][0];
			$status = $profileData[AUTH_PROFILE_STATUS_COLUMN] ?? null;
			if (!in_array($status, [1, '1', true, 'active', 'activo'], true)) {
				return ['ok' => false, 'message' => 'El usuario está inactivo.', 'data' => null];
			}

			$profileId = $profileData[AUTH_PROFILE_ID_COLUMN] ?? null;
			$roleId = $profileData[AUTH_PROFILE_ROLE_COLUMN] ?? null;
			$role = SupabaseApi::getWithToken(AUTH_ROLE_TABLE, [
				'select' => AUTH_ROLE_NUMBER_COLUMN,
				'id' => 'eq.' . $roleId,
			], $auth['data']['access_token']);

			if (!$role['ok'] || empty($role['data'][0][AUTH_ROLE_NUMBER_COLUMN])) {
				return ['ok' => false, 'message' => 'El rol del usuario no está configurado.', 'data' => null];
			}

			$roleNumber = (int) $role['data'][0][AUTH_ROLE_NUMBER_COLUMN];
			$personData = ($roleNumber === 5)
				? $this->obtener_persona_supabase('student', $profileId, $auth['data']['access_token'])
				: $this->obtener_persona_supabase('staff', $profileId, $auth['data']['access_token']);

			return [
				'ok' => true,
				'message' => 'OK',
				'data' => [
					'jwt' => $auth['data']['access_token'],
					'refresh_token' => $auth['data']['refresh_token'] ?? null,
					'expires_in' => (int) ($auth['data']['expires_in'] ?? AUTH_SESSION_SECONDS),
					'auth_user_id' => $authUserId,
					'profile' => $profileData,
					'role_id' => $roleId,
					'role_number' => $roleNumber,
                    'profile_status' => $status,
					'person' => $personData,
				],
			];
		}


    public static function procesar_login(
        string $ip,
        string $tipo,
        bool $success,
        ?string $accessToken = null,
        bool $dryRun = false
    ): array {
        $params = [
            'p_ip'      => $ip,
            'p_tipo'    => $tipo,
            'p_success' => $success,
            'p_dry_run' => $dryRun,
        ];

        // Éxito → con JWT.  Fallo o dry-run → sin token.
        return ($accessToken !== null)
            ? SupabaseApi::rpcWithToken('procesar_login', $params, $accessToken)
            : SupabaseApi::rpcWithoutToken('procesar_login', $params);
    }
    
    
    
    
    
    
    
		private function obtener_persona_supabase(string $table, $profileId, string $accessToken): array{
			if ($profileId === null) {
				return [];
			}

			$result = SupabaseApi::getWithToken($table, [
				'select' => '*',
				AUTH_PROFILE_PERSON_COLUMN => 'eq.' . $profileId,
			], $accessToken);

			return $result['ok'] && !empty($result['data'][0]) ? $result['data'][0] : [];
		}



// Verifica si la ip esta bloqueada cuando carga login view
    // Verifica si la IP está bloqueada cuando carga login view
public static function verificar_ip_bloqueada_supabase(string $ip, string $tipo): array {
    $campoBloqueo = match($tipo) {
        'login'        => 'block_login_access_time',
        'recuperacion' => 'block_recover_account_time',
        'cc'           => 'block_create_account_time',
        default        => 'block_login_access_time',
    };

    $ipEncoded = urlencode($ip);

    $resp = SupabaseApi::get('ip_visitor', [
        'select'       => $campoBloqueo,
        'ip_direction' => 'eq.' . $ipEncoded,
        'limit'        => '1',
    ]);

    if (!$resp['ok'] || empty($resp['data'][0])) {
        return ['ok' => true, 'bloqueado' => false, 'tiempo_restante' => 0];
    }

    $bloqueo = $resp['data'][0][$campoBloqueo] ?? null;
    if (!$bloqueo) {
        return ['ok' => true, 'bloqueado' => false, 'tiempo_restante' => 0];
    }

    // ── FIX: interpretar el timestamp como UTC ──
    // La columna es timestamp without time zone, pero Postgres (Supabase)
    // guarda NOW() en UTC. Sin forzar UTC, PHP lo interpreta con su timezone
    // local y la diferencia se desfasa 4 horas (UTC-4 Caracas).
    try {
        $dtBloqueo = new DateTime($bloqueo, new DateTimeZone('UTC'));
        $dtAhora   = new DateTime('now',      new DateTimeZone('UTC'));
        $restante  = $dtBloqueo->getTimestamp() - $dtAhora->getTimestamp();

        if ($restante > 0) {
            return [
                'ok'              => true,
                'bloqueado'       => true,
                'tiempo_restante' => $restante,
            ];
        }
    } catch (Exception $e) {
        // Si el timestamp no se puede parsear, tratar como sin bloqueo
        // (no queremos romper el login por un formato inesperado)
    }

    return ['ok' => true, 'bloqueado' => false, 'tiempo_restante' => 0];
}
    


//registra el intento fallido de entrar al login o recuperar clave
        public static function registrar_intento_fallido_supabase(string $ip, string $tipo): array {
            return SupabaseApi::rpcWithoutToken('registrar_intento_fallido', [
                'p_ip'   => $ip,
                'p_tipo' => $tipo,
            ]);
        }

//reinicia los intentos a 0 si la comprobacion ya sea de login o de cambiar clave fueron existosas
 	   public static function reiniciar_intentos_supabase(string $ip, string $tipo): array {
            $campoIntentos = match($tipo) {
                'login'        => 'failed_login_access_attemps',
                'recuperacion' => 'failed_recover_account_attemps',
                default        => null,
            };
           //'cc' => 'failed_creating_account_attemps',
            $campoBloqueo = match($tipo) {
                'login'        => 'block_login_access_time',
                'recuperacion' => 'block_recover_account_time',
                default        => null,
            };
           //'cc' => 'block_create_account_time',

            if ($campoIntentos === null) {
                return ['ok' => false, 'message' => 'Tipo inválido'];
            }

            return SupabaseApi::patch('ip_visitor', [
                'ip_direction' => 'eq.' . $ip,
            ], [
                $campoIntentos => 0,
                $campoBloqueo  => null,
            ]);
        }
    
    

//para la vista de recuperar cuenta
    public static function enviar_recuperacion_supabase(string $email, string $redirectTo): array {
    
        return SupabaseApi::sendPasswordRecoveryEmail($email, $redirectTo);
        
        //return SupabaseApi::sendCodeRecoveryEmail($email);
    }
    
    
    
//cambia la clave del usuario si acepto lo del enlace del correo e ingreso la nueva clave en la vista de cambiar clave    
    public static function actualizar_clave_usuario(string $accessToken, string $nuevaClave): array {
        return SupabaseApi::updateUserPassword($accessToken, $nuevaClave);
    }    
    
}



?>