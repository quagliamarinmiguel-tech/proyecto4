<?php

require_once __DIR__ . '/supabase.env.php';

class SupabaseApi
{
    public static function authenticate(string $email, string $password): array
    {
        $url = SUPABASE_AUTH_URL . '/token?grant_type=password';
        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode([
                'email' => $email,
                'password' => $password,
            ], JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['ok' => false, 'status' => 0, 'message' => $error, 'data' => null];
        }

        $decoded = json_decode($response, true);

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'message' => $decoded['error_description'] ?? $decoded['msg'] ?? 'Error de autenticación',
            'data' => is_array($decoded) ? $decoded : null,
        ];
    }

    public static function request(string $method, string $table, array $params = [], array $payload = [], ?string $accessToken = null): array
    {
        $url = SUPABASE_REST_URL . '/' . ltrim($table, '/');

        if (!empty($params)) {
            $url .= '?' . http_build_query($params);
        }

        $ch = curl_init($url);

        $headers = [
            'apikey: ' . SUPABASE_KEY,
            'Authorization: Bearer ' . ($accessToken ?? SUPABASE_KEY),
            'Content-Type: application/json',
            'Accept: application/json',
        ];

        if (strtoupper($method) === 'POST') {
            $headers[] = 'Prefer: return=representation';
        }

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => strtoupper($method),
            CURLOPT_HTTPHEADER => $headers,
            CURLOPT_TIMEOUT => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        if (!empty($payload)) {
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($payload, JSON_UNESCAPED_UNICODE));
        }

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['ok' => false, 'status' => 0, 'message' => $error, 'data' => null];
        }

        $decoded = json_decode($response, true);

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'message' => $decoded['message'] ?? 'OK',
            'data' => is_array($decoded) ? $decoded : null,
        ];
    }

    public static function get(string $table, array $params = []): array
    {
        return self::request('GET', $table, $params, []);
    }

    public static function getWithToken(string $table, array $params, string $accessToken): array
    {
        return self::request('GET', $table, $params, [], $accessToken);
    }

    public static function post(string $table, array $payload): array
    {
        return self::request('POST', $table, [], $payload);
    }

    public static function postWithToken(string $table, array $payload, string $accessToken): array
    {
        return self::request('POST', $table, [], $payload, $accessToken);
    }

    public static function put(string $table, array $params, array $payload): array
    {
        return self::request('PUT', $table, $params, $payload);
    }

    public static function patchWithToken(string $table, array $params, array $payload, string $accessToken): array
    {
        return self::request('PATCH', $table, $params, $payload, $accessToken);
    }

    public static function rpcWithToken(string $function, array $payload, string $accessToken): array
    {
        return self::request('POST', 'rpc/' . ltrim($function, '/'), [], $payload, $accessToken);
    }
    

    public static function rpcWithoutToken(string $function, array $payload = []): array
    {
        return self::request('POST', 'rpc/' . ltrim($function, '/'), [], $payload);
    }

    public static function patch(string $table, array $params, array $payload): array
    {
        return self::request('PATCH', $table, $params, $payload);
    }

    public static function delete(string $table, array $params = []): array
    {
        return self::request('DELETE', $table, $params, []);
    }
    
    ##ESTO ES ALGO MIO QUAGLIA
    ## no me grites
    public static function signup(string $email, string $password, array $userMetadata = []): array
	{
    	$url = SUPABASE_AUTH_URL . '/signup';
    	$payload = [
        	'email' => $email,
        	'password' => $password,
        	'data' => $userMetadata, // opcional: metadatos adicionales
    	];
    	$ch = curl_init($url);
    	curl_setopt_array($ch, [
        	CURLOPT_RETURNTRANSFER => true,
        	CURLOPT_POST => true,
        	CURLOPT_HTTPHEADER => [
            	'apikey: ' . SUPABASE_ANON_KEY,
            	'Content-Type: application/json',
            	'Accept: application/json',
        	],
        	CURLOPT_POSTFIELDS => json_encode($payload),
        	CURLOPT_TIMEOUT => API_TIMEOUT_SECONDS,
    	]);
    	$response = curl_exec($ch);
    	$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	$error = curl_error($ch);
    	curl_close($ch);

    	if ($error) {
        	return ['ok' => false, 'status' => 0, 'message' => $error, 'data' => null];
    	}
    	$decoded = json_decode($response, true);
    	return [
        	'ok' => $status >= 200 && $status < 300,
        	'status' => $status,
        	'message' => $decoded['msg'] ?? ($decoded['error_description'] ?? 'OK'),
        	'data' => $decoded,
    	];
	}
    
    
    


    /**
     * Crea un usuario en auth.users usando la Admin API.
     * Requiere SUPABASE_SERVICE_ROLE_KEY.
     */
    public static function adminCreateUser(string $email, string $password, bool $emailConfirm = true): array
    {
        $url = SUPABASE_AUTH_URL . '/admin/users';

        $payload = [
            'email'         => $email,
            'password'      => $password,
            'email_confirm' => $emailConfirm,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
                'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['ok' => false, 'status' => 0, 'message' => $error, 'data' => null];
        }

        $decoded = json_decode($response, true);

        $ok = $status >= 200 && $status < 300 && !empty($decoded['id']);

        return [
            'ok'      => $ok,
            'status'  => $status,
            'message' => $ok
                ? 'Usuario creado'
                : ($decoded['msg'] ?? $decoded['error_description'] ?? $decoded['error'] ?? 'Error al crear usuario'),
            'data'    => is_array($decoded) ? $decoded : null,
            'user_id' => $ok ? $decoded['id'] : null,
        ];
    }

    /**
     * Elimina un usuario de auth.users por su UUID.
     * Requiere SUPABASE_SERVICE_ROLE_KEY.
     */
    public static function adminDeleteUser(string $userId): array
    {
        $url = SUPABASE_AUTH_URL . '/admin/users/' . urlencode($userId);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'DELETE',
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
                'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                'Accept: application/json',
            ],
            CURLOPT_TIMEOUT        => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['ok' => false, 'status' => 0, 'message' => $error];
        }

        $ok = $status >= 200 && $status < 300;

        return [
            'ok'      => $ok,
            'status'  => $status,
            'message' => $ok ? 'Usuario eliminado' : 'Error al eliminar usuario',
            'data'    => json_decode($response, true),
        ];
    }
    
    /**
     * Reenvía el correo de confirmación de registro (signup) a un usuario.
     */
    public static function resendConfirmationEmail(string $email): array
    {
        $url = rtrim(SUPABASE_AUTH_URL, '/') . '/resend';

        $payload = [
            'type'  => 'signup',
            'email' => $email,
        ];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['ok' => false, 'status' => 0, 'message' => $error];
        }

        return [
            'ok'      => $status >= 200 && $status < 300,
            'status'  => $status,
            'message' => $status >= 200 && $status < 300 ? 'Correo enviado' : 'Error al enviar correo',
            'data'    => json_decode($response, true),
        ];
    }
    
    
    
    
    
/*------------------------------------------------logica de recuperacion de cuenta via codigo enviado al correo--------------------------------------------------------*/    
//envia el correo y supabase genera el codigo y lo envia al correo enviado
    /*public static function sendCodeRecoveryEmail(string $email): array {
        //enviar_recuperacion_supabase
        //$url = SUPABASE_URL . '/auth/v1/recover';
        $url = SUPABASE_AUTH_URL . '/recover';

        $payload = [
            'email' => $email,
        ];

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS => json_encode($payload),
            CURLOPT_TIMEOUT => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);

        curl_close($ch);

        if ($error !== '') {
            return [
                'ok' => false,
                'status' => 0,
                'message' => $error,
            ];
        }

        $data = json_decode($response, true);

        return [
            'ok' => $status >= 200 && $status < 300,
            'status' => $status,
            'message' => $status >= 200 && $status < 300
                ? 'Código enviado'
                : ($data['msg'] ?? $data['message'] ?? 'No se pudo enviar el código'),
            'data' => $data,
        ];
    }*/
    
    
    
    
    //envia un enlace al correo, lo usa recuperar-cuenta-view (deberia llamarse cambiarclave view)

     /** IMPORTANTE, ESTE METODO NO SIRVIO ASIQUE AHORA SE ENVIA UN CORREO
	     * Envía el correo de recuperación de contraseña.
    	 * Usa la ANON_KEY (no requiere token).
     */
     
    //el url lo define supabase en authentication/ url configuration, esto es el enlace al que te envia supabase si entras desde el enlace enviado al correo,
    //para este caso el enlace es: https://ceproca2.wuaze.com
    
    //el enlace original por defecto en supabase era este: http://localhost:3000
    // se deben agregar estas en redirect URls dentro de la misma vista de configuracion de urls: 
    //	https://ceproca2.wuaze.com/cambiar-clave
	//	https://ceproca2.wuaze.com/index.php?views=cambiar-clave
     
    
    //ESTO PARA PRODUCCION ES INVIABLE, abria q pagar a supabase para hacerlo viable imagino
    
    
    //esta funciona pero la comento para probar otra q permite hacer vardumpal json de payload
    /*public static function sendPasswordRecoveryEmail(string $email, string $redirectTo): array{
        $url = SUPABASE_AUTH_URL . '/recover';

        $payload = [
            'email'       => $email,
            'redirect_to' => $redirectTo,   //  El ENLACE DONDE TE REDIRIJE DESPUES
        ];
        
        // var_dump($redirectTo);die();
         //var_dump($payload);die();
		// string(40) "https://ceproca2.wuaze.com/cambiar-clave"
        
        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        var_dump(json_encode($payload));die();
        
        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['ok' => false, 'status' => 0, 'message' => $error];
        }

        return [
            'ok'      => $status >= 200 && $status < 300,
            'status'  => $status,
            'message' => $status >= 200 && $status < 300
                ? 'Correo enviado'
                : 'Error al enviar el correo',
        ];
    }*/
    
	public static function sendPasswordRecoveryEmail(
        string $email,
        string $redirectTo
    ): array {
        $url = rtrim(SUPABASE_AUTH_URL, '/') . '/recover';

        $payload = [
            'email'       => $email,
            'redirect_to' => $redirectTo,
        ];

        $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

        if ($json === false) {
            return [
                'ok'      => false,
                'status'  => 0,
                'message' => 'Error generando el JSON: ' . json_last_error_msg(),
            ];
        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST           => true,
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . SUPABASE_ANON_KEY,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => $json,
            CURLOPT_TIMEOUT        => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);

        curl_close($ch);
        
              /*var_dump([
                    'url'  => $url,
                    'json' => $json,
                    'status' => $status,
                    'error' => $error,
                    'response' => $response,
                ]);
                die();*/

        if ($response === false || $error !== '') {
            return [
                'ok'      => false,
                'status'  => 0,
                'message' => $error !== '' ? $error : 'Error desconocido de cURL',
            ];
        }

        if ($status >= 200 && $status < 300) {
            return [
                'ok'      => true,
                'status'  => $status,
                'message' => 'Correo enviado',
            ];
        }

        return [
            'ok'      => false,
            'status'  => $status,
            'message' => 'Error al enviar el correo',
            'details' => $response,
        ];
    }
    
/*---------------------------------------------------------------------------------------------------------------------------------------------*/
    
    
//para actualizar la clave via correo, es mejor hacer funciones especificas para vistas especificas debido a q por cada consulta la pagina tarda mas en cargar
    
    public static function updateUserPassword(string $accessToken, string $newPassword): array
    {
        $url = SUPABASE_AUTH_URL . '/user';

        $payload = ['password' => $newPassword];

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_ANON_KEY,
                'Authorization: Bearer ' . $accessToken,
                'Content-Type: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload),
            CURLOPT_TIMEOUT        => API_TIMEOUT_SECONDS,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        return [
            'ok'      => $status >= 200 && $status < 300,
            'status'  => $status,
            'message' => $status >= 200 && $status < 300 ? 'Contraseña actualizada' : 'Error al actualizar',
        ];
    }
    
    
    
    
    
   

    
    
    /**
     * Actualiza campos de un usuario en auth.users usando la Admin API.
     * Acepta email y/o password de forma opcional.
     * Requiere SUPABASE_SERVICE_ROLE_KEY.
     *
     * @param string      $userId   UUID del usuario en auth.users
     * @param string|null $email    Nuevo email (opcional)
     * @param string|null $password Nueva contraseña (opcional)
     * @param bool        $emailConfirm Si true, marca el email como confirmado
     * @return array
     */
    public static function adminUpdateUser(
        string $userId,
        ?string $email = null,
        ?string $password = null,
        bool $emailConfirm = true
    ): array {
        // Armar payload solo con los campos enviados
        $payload = [];
        if ($email !== null && $email !== '') {
            $payload['email'] = $email;
            if ($emailConfirm) {
                $payload['email_confirm'] = true;
            }
        }
        if ($password !== null && $password !== '') {
            $payload['password'] = $password;
        }

        if (empty($payload)) {
            return ['ok' => false, 'message' => 'No hay campos para actualizar'];
        }

        $url = SUPABASE_AUTH_URL . '/admin/users/' . urlencode($userId);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST  => 'PUT',
            CURLOPT_HTTPHEADER     => [
                'apikey: ' . SUPABASE_SERVICE_ROLE_KEY,
                'Authorization: Bearer ' . SUPABASE_SERVICE_ROLE_KEY,
                'Content-Type: application/json',
                'Accept: application/json',
            ],
            CURLOPT_POSTFIELDS     => json_encode($payload, JSON_UNESCAPED_UNICODE),
            CURLOPT_TIMEOUT        => API_TIMEOUT_SECONDS,
            CURLOPT_CONNECTTIMEOUT => 10,
        ]);

        $response = curl_exec($ch);
        $status   = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error    = curl_error($ch);
        curl_close($ch);

        if ($error !== '') {
            return ['ok' => false, 'status' => 0, 'message' => $error, 'data' => null];
        }

        $decoded = json_decode($response, true);
        $ok = $status >= 200 && $status < 300;

        return [
            'ok'      => $ok,
            'status'  => $status,
            'message' => $ok
                ? 'Usuario actualizado'
                : ($decoded['msg'] ?? $decoded['error_description'] ?? $decoded['error'] ?? 'Error al actualizar'),
            'data'    => is_array($decoded) ? $decoded : null,
        ];
    }
    
    
    
    ##Y TERMINA AQUI QUAGLIA
}

