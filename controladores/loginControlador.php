<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if($peticionAjax){
    require_once __DIR__ . '/../modelos/login_mdo.php';
}else{
    require_once __DIR__ . '/../modelos/login_mdo.php';
}


//hay algo raro con login_mdo y loginModelo y no se porque funciona incluso con eso

//se toma en consideracion ciertas medidas de seguridad, supabase protege ante inyecciones junto con la funcion de 'limpiar_cadena dentro del mainModel',
//tambien se verifica la ip del usuario antes de alguna accion en los formularios de login y cambiar clave, el peso de las variables para saber si se envia algo incorrecto,
// si se envia un formulario tipo html y si los datos son recibidos como POST
//el uso de recaptcha v3 en las vistas para verificar ataques de bots

class loginControlador extends loginModelo{
//no solo crea y cierra la sesion, sino que maneja las funciones de las vistas de cambiar clave, recuperar cuenta y la de solicitud d nuevo ingreso
    

/*--------- Controlador iniciar sesion ---------*/
    public function iniciar_sesion_controlador() {

        // Validar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '<script>Swal.fire({ title: "Método no permitido", text: "La petición debe realizarse por POST.", type: "error", confirmButtonText: "Aceptar" });</script>';
            return;
        }

        // Validar Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/x-www-form-urlencoded') === false
            && stripos($contentType, 'multipart/form-data') === false) {
            http_response_code(415);
            echo '<script>Swal.fire({ title: "Formato no soportado", text: "El formulario debe enviarse como formulario HTML.", type: "error", confirmButtonText: "Aceptar" });</script>';
            return;
        }

        // Limpieza y validaciones del formulario
        $usuario = mainModel::limpiar_cadena($_POST['usuario_log']);
        $clave   = mainModel::limpiar_cadena($_POST['clave_log']);

        if ($usuario === '' || $clave === '') {
            echo '<script>Swal.fire({ title: "Ocurrió un error inesperado", text: "No has llenado todos los campos que son requeridos", type: "error", confirmButtonText: "Aceptar" });</script>';
            return;
        }
        if (!filter_var($usuario, FILTER_VALIDATE_EMAIL)) {
            echo '<script>Swal.fire({ title: "Ocurrió un error inesperado", text: "El correo del usuario no coincide con el formato solicitado", type: "error", confirmButtonText: "Aceptar" });</script>';
            return;
        }
        if (strlen($usuario) > 100) {
            echo '<script>Swal.fire({ title: "Error", text: "El correo es demasiado largo.", type: "error" });</script>';
            return;
        }
        if (strlen($clave) < 7 || strlen($clave) > 100) {
            echo '<script>Swal.fire({ title: "Error", text: "La clave debe tener entre 7 y 100 caracteres.", type: "error" });</script>';
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // LLAMADA 1 — Supabase Auth (GoTrue)
        $auth = loginModelo::autenticar_supabase($usuario, $clave);

        // FALLO — LLAMADA 2a: registra intento y devuelve bloqueo
        if (!$auth['ok']) {
            $resp = loginModelo::procesar_login($ip, 'login', success: false);

            $r = $resp['data'] ?? [];
            if (isset($r[0]) && is_array($r[0])) $r = $r[0];

            if (!empty($r['bloqueado'])) {
                $bloqueoHasta = $r['bloqueo_hasta'] ?? null;
                $minutos = 1;
                //if ($bloqueoHasta) {
                //    $minutos = max(1, (int) ceil((strtotime($bloqueoHasta) - time()) / 60));
               // }

                if ($bloqueoHasta) {
                    try {
                        $dtBloqueo = new DateTime($bloqueoHasta, new DateTimeZone('UTC'));
                        $dtAhora   = new DateTime('now',          new DateTimeZone('UTC'));
                        $restante  = $dtBloqueo->getTimestamp() - $dtAhora->getTimestamp();
                        $minutos   = max(1, (int) ceil($restante / 60));
                    } catch (Exception $e) {
                        $minutos = 1;
                    }
                }


                $titulo = 'Acceso bloqueado';
                $texto  = "Demasiados intentos fallidos. Intente más tarde en $minutos minutos.";
                echo '<script>
                    Swal.fire({
                        title: ' . json_encode($titulo) . ',
                        text: '  . json_encode($texto)  . ',
                        type: "error",
                        confirmButtonText: "Aceptar"
                    }).then((result) => {
                        if (result.value) {
                            window.location.href = ' . json_encode(SERVERURL . 'index.php') . ';
                        }
                    });
                </script>';
                return;
            }

            $this->mostrar_error_login($auth['message'] ?? 'El USUARIO o CLAVE son incorrectos');
            return;
        }

        // ÉXITO — LLAMADA 2b
        $resp = loginModelo::procesar_login(
            $ip,
            'login',
            success: true,
            accessToken: $auth['data']['jwt']
        );

        $r = $resp['data'] ?? [];
        if (isset($r[0]) && is_array($r[0])) $r = $r[0];

        if (empty($resp['ok']) || empty($r)) {
            $this->mostrar_error_login('Error al procesar el login: ' . ($resp['message'] ?? 'respuesta vacía'));
            return;
        }

        if (empty($r['ok'])) {
            // IP bloqueada detectada por la RPC
            if (($r['error'] ?? '') === 'ip_bloqueada') {
                $minutos = max(1, (int) ceil(($r['tiempo_restante'] ?? 0) / 60));
                $titulo  = 'Acceso bloqueado';
                $texto   = "Demasiados intentos fallidos. Intente más tarde en $minutos minutos.";
                echo '<script>
                    Swal.fire({
                        title: ' . json_encode($titulo) . ',
                        text: '  . json_encode($texto)  . ',
                        type: "error",
                        confirmButtonText: "Aceptar"
                    }).then((result) => {
                        if (result.value) {
                            window.location.href = ' . json_encode(SERVERURL . 'index.php') . ';
                        }
                    });
                </script>';
                return;
            }

            $msg = match ($r['error'] ?? '') {
                'usuario_inactivo'      => 'El usuario está inactivo.',
                'perfil_no_configurado' => 'El perfil del usuario está incompleto.',
                'no_jwt'                => 'Error de autenticación. Reintente.',
                'tipo_invalido'         => 'Error de configuración. Reintente.',
                default                 => 'No se pudo completar el inicio de sesión.',
            };
            $this->mostrar_error_login($msg);
            return;
        }

        // ── Extracción de datos ──
        $perfil   = $r['profile']      ?? [];
        $persona  = $r['person']       ?? [];
        $rol      = (int) ($r['role_number'] ?? 0);
        $idPerfil = $perfil[AUTH_PROFILE_ID_COLUMN]   ?? null;
        $roleId   = $perfil[AUTH_PROFILE_ROLE_COLUMN] ?? null;
        $nombre   = $persona['name']      ?? $usuario;
        $apellido = $persona['last_name'] ?? '';

        if ($idPerfil === null || $rol === 0) {
            $this->mostrar_error_login('El perfil del usuario está incompleto.');
            return;
        }

        // ── Crear sesión ──
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_set_cookie_params([
                'lifetime' => AUTH_SESSION_SECONDS,
                'path'     => '/',
                'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax',
            ]);
            session_start(['name' => 'SPM']);
        }
        session_regenerate_id(true);

        $_SESSION['id_spm']             = $idPerfil;
        $_SESSION['id_usuario']         = $idPerfil;
        $_SESSION['auth_user_id']       = $auth['data']['auth_user_id'];
        $_SESSION['usuario_spm']        = $usuario;
        $_SESSION['nombre_spm']         = $nombre;
        $_SESSION['apellido_spm']       = $apellido;
        $_SESSION['cargo_spm']          = $rol;
        $_SESSION['role_id']            = $roleId;
        $_SESSION['estado_usuario']     = $r['profile_status'] ?? null;
        $_SESSION['profile_status']     = $r['profile_status'] ?? null;
        $_SESSION['token_spm']          = bin2hex(random_bytes(32));
        $_SESSION['auth_access_token']  = $auth['data']['jwt'];
        $_SESSION['auth_refresh_token'] = $auth['data']['refresh_token'];
        $_SESSION['ultima_actividad']   = time();
        $_SESSION['expira_en']          = time() + AUTH_SESSION_SECONDS;

        if ($rol === 5) {
            $idEstudiante = $persona['id_estudiante'] ?? $persona['id'] ?? null;
            if ($idEstudiante !== null) {
                $_SESSION['id_estudiante'] = $idEstudiante;
            }
        }

        header("Location: " . SERVERURL . "index.php?views=admin/home");
        exit();
    } /* Fin controlador */

    
    
    
    
//verifica si esta entrando alguien con un ip registrada y si tiene intentos fallidos de entrar bloquea su acceso    
    public function validar_ip_controlador(): string {
        header('Content-Type: application/json');

        $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $tipo = mainModel::limpiar_cadena($_GET['tipo'] ?? 'login');

        if (!in_array($tipo, ['login', 'recuperacion', 'cc'], true)) {
            return json_encode(['bloqueado' => false, 'mensaje' => 'Tipo inválido.']);
        }

        $rpc = login_Modelo::verificar_ip_bloqueada_supabase($ip, $tipo);
        var_dump($rpc);die();

        if (!$rpc['ok'] || empty($rpc['bloqueado'])) {
            return json_encode(['bloqueado' => false]);
        }

        $minutos = (int) ceil(($rpc['tiempo_restante'] ?? 0) / 60);
        return json_encode([
            'bloqueado' => true,
            'mensaje'   => "Demasiados intentos fallidos. Intente más tarde en $minutos minutos.",
        ]);
    }
    
//registra el intento de acceso fallido del usuario
    public function registrar_intento_fallido_controlador(string $ip, string $tipo): array {
        return loginModelo::registrar_intento_fallido_supabase($ip, $tipo);
    }

//reinicia a 0 los intentos si el acceso fue existoso
    public function reiniciar_intentos_controlador(string $ip, string $tipo): array {
        return loginModelo::reiniciar_intentos_supabase($ip, $tipo);
    }


//recibe el correo y si esta registrado enviara el enlace despues, esta es de recuperar-cuenta view
    
    //supabase permite solo 2 intentos por hora
    public function solicitar_recuperacion_controlador(): void {
        // Validar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            echo '<script>Swal.fire({ title: "Método no permitido", text: "La petición debe realizarse por POST.", type: "error" });</script>';
            return;
        }

        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';

        // Verificar bloqueo
        $bloqueo = loginModelo::verificar_ip_bloqueada_supabase($ip, 'recuperacion');
        if (!empty($bloqueo['bloqueado'])) {
            $minutos = (int) ceil(($bloqueo['tiempo_restante'] ?? 0) / 60);
            echo '<script>Swal.fire({ title: "Acceso bloqueado", text: "Demasiados intentos. Intente en ' . $minutos . ' minutos.", type: "error" });</script>';
            return;
        }

        $correo = mainModel::limpiar_cadena($_POST['correo'] ?? '');

        if ($correo === '' || !filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo '<script>Swal.fire({ title: "Error", text: "Ingrese un correo válido.", type: "error" });</script>';
            return;
        }

        if (strlen($correo) > 100) {
            echo '<script>Swal.fire({ title: "Error", text: "El correo es demasiado largo.", type: "error" });</script>';
            return;
        }

        // Enviar correo vía Supabase
        
        //no envia enlace: $resp = loginModelo::enviar_recuperacion_supabase($correo);
        //ahora envia el enlace donde te redirecciona despues de aceptar lo del correo
        $resp = loginModelo::enviar_recuperacion_supabase($correo,SERVERURL . 'cambiar-clave');
        
        //no funciono lo del correo asique ahora sera por codigo
        //$resp = loginModelo::enviar_recuperacion_supabase($correo);

        if ($resp['ok']) {
            echo '<script>
                Swal.fire({
                    title: "Correo enviado",
                    text: "Si el correo está registrado, recibirás un email para restablecer tu contraseña.",
                    type: "success",
                    confirmButtonText: "Aceptar"
                }).then(() => {
                    window.location.href = "' . SERVERURL . '?views=login";
                });
            </script>';
            //                    window.location.href = "' . SERVERURL . '?views=recuperar-cuenta-validar";
        } else {
            // Registrar intento fallido (solo si falla, para no bloquear por correos inexistentes)
            $this->registrar_intento_fallido_controlador($ip, 'recuperacion');

        	echo '<script>Swal.fire({ title: "Error", text: "No se pudo enviar el correo. Intente más tarde.", type: "error" });</script>';
        }
    }
    
    
    
    public function cambiar_clave_controlador(): string {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            return json_encode(['ok' => false, 'mensaje' => 'Método no permitido.']);
        }

        $accessToken = $_POST['access_token'] ?? '';
        $nuevaClave  = trim($_POST['nueva_clave'] ?? '');

        if ($accessToken === '') {
            return json_encode(['ok' => false, 'mensaje' => 'Token inválido.']);
        }
        if (strlen($nuevaClave) < 7 || strlen($nuevaClave) > 100) {
            return json_encode(['ok' => false, 'mensaje' => 'La contraseña debe tener entre 7 y 100 caracteres.']);
        }
        
        
        $confirmar  = trim($_POST['confirmar_clave'] ?? '');
        if ($nuevaClave !== $confirmar) {
            return json_encode(['ok' => false, 'mensaje' => "Las claves no coinciden. El valor fue: $confirmar"]);
        }
        
        $validandoclave=mainModel::validar_clave_segura($nuevaClave);
        if ($validandoclave !=null) {return json_encode(mainModel::generarAlertaSimple($validandoclave, 'error', 'simple'));}
    
        
        $resp = loginModelo::actualizar_clave_usuario($accessToken, $nuevaClave);

        if ($resp['ok']) {
            return json_encode(['ok' => true, 'mensaje' => 'Contraseña actualizada.']);
        }
		
        $mensaje = $resp['message'] ?? 'Error al actualizar.';

        // Opcional: si el mensaje contiene detalles técnicos, ocultarlos
        if (stripos($mensaje, 'jwt') !== false || stripos($mensaje, 'token') !== false) {
            $mensaje = 'El enlace expiró o no es válido. Solicita uno nuevo.';
        }

        return json_encode(['ok' => false, 'mensaje' => $mensaje]);
    }
    
    
    
    
    
    
    private function mostrar_error_login(string $mensaje){
        echo '<script>Swal.fire({title: "Ocurrio un error inesperado", text: ' . json_encode($mensaje) . ', type: "error", confirmButtonText: "Aceptar"});</script>';
    }

    
//esto no se que lo usa
    private function obtener_id_estudiante($id_usuario) {
        try {
            error_log("=== BUSCANDO ID_ESTUDIANTE ===");
            error_log("ID Usuario recibido: " . $id_usuario);
            
            // Consulta usando la tabla intermedia usuario_estudiante
            $sql = "SELECT id_estudiante_fk 
                    FROM usuario_estudiante 
                    WHERE id_usuario_fk = ?";
                    
            $stmt = mainModel::conectar()->prepare($sql);
            $stmt->execute([$id_usuario]);
            
            error_log("Filas encontradas en usuario_estudiante: " . $stmt->rowCount());
            
            if($stmt->rowCount() == 1) {
                $result = $stmt->fetch();
                error_log("¡ID ESTUDIANTE ENCONTRADO!: " . $result['id_estudiante_fk']);
                return $result['id_estudiante_fk'];
            } else {
                error_log("NO se encontró relación en usuario_estudiante para el usuario: " . $id_usuario);
                
                // Debug: verificar qué hay en la tabla usuario_estudiante
                $sql_debug = "SELECT * FROM usuario_estudiante";
                $stmt_debug = mainModel::conectar()->prepare($sql_debug);
                $stmt_debug->execute();
                $todas_relaciones = $stmt_debug->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Todas las relaciones en usuario_estudiante: " . print_r($todas_relaciones, true));
                return null;
            }
            
        } catch (Exception $e) {
            error_log("Error crítico obteniendo id_estudiante: " . $e->getMessage());
            return null;
        }
    }

    
    
    
    
/*--------- Controlador forzar cierre de sesion ---------*/
    public function forzar_cierre_sesion_controlador(){
        session_unset();
        session_destroy();
        if(headers_sent()){
            return "<script> window.location.href='".SERVERURL."index.php?views=login'; </script>";
        }else{
            return header("Location: ".SERVERURL."index.php?views=login");
        }
    } 

    
/*--------- Controlador cierre de sesion ---------*/
    public function cerrar_sesion_controlador(){
        session_start(['name'=>'SPM']);
        $token=mainModel::decryption($_POST['token']);
        $usuario=mainModel::decryption($_POST['usuario']);

        if($token==$_SESSION['token_spm'] && $usuario==$_SESSION['usuario_spm']){
            session_unset();
            session_destroy();
            //$alerta=[
           //     "Alerta"=>"redireccionar",
            //    "URL"=>SERVERURL."index.php?views=login"
          //  ];
            
            //"ResetTema"  => true   //pone la preferencia en modo claro
            $alerta=[
                "Alerta"=>"logout",
				"URL"=>SERVERURL."index.php?views=login"
            ];					

            
        }else{
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No se pudo cerrar la sesion en el sistema",
                "Tipo"=>"error"
            ];
        }
        echo json_encode($alerta);
    } 
    
}