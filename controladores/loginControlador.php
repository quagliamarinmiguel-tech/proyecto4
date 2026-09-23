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
    public function iniciar_sesion_controlador(){
           
        // Validar método HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405); // Method Not Allowed
            echo '
            <script>
                Swal.fire({
                    title: "Método no permitido",
                    text: "La petición debe realizarse por POST.",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
            return;
            //exit();
        }

        // Validar Content-Type
        $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
        if (stripos($contentType, 'application/x-www-form-urlencoded') === false
            && stripos($contentType, 'multipart/form-data') === false) {
            http_response_code(415); // Unsupported Media Type
            echo '
            <script>
                Swal.fire({
                    title: "Formato no soportado",
                    text: "El formulario debe enviarse como formulario HTML.",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
            //exit();
        	return;
        }
        
      
        //verifica si la ip esta bloqueada antes de continuar
        $ip = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        //var_dump($ip);die();
        $bloqueo = loginModelo::verificar_ip_bloqueada_supabase($ip, 'login');
        //var_dump($bloqueo);die();

        if (!empty($bloqueo['bloqueado'])) {
            //echo 'hola'; die();
            //exit('IP bloqueada temporalmente.');
            
            /*$minutos = (int) ceil(($bloqueo['tiempo_restante'] ?? 0) / 60);
            echo '<script>
                Swal.fire({
                    title: "Acceso bloqueado",
                    text: "Demasiados intentos fallidos. Intente más tarde en ' . $minutos . ' minutos.",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>';*/
                        
            $minutos = (int) ceil(($bloqueo['tiempo_restante'] ?? 0) / 60);
            $titulo  = 'Acceso bloqueado';
            $texto   = "Demasiados intentos fallidos. Intente más tarde en $minutos minutos.";
            echo '<script>
                Swal.fire({
                    title: ' . json_encode($titulo) . ',
                    text: ' . json_encode($texto) . ',
                    type: "error",
                    confirmButtonText: "Aceptar"
                }).then((result) => {
                if (result.value) {
                    window.location.href = ' . json_encode(SERVERURL . 'index.php') . ';
                }
            });
            </script>';
       
            
            
            
            
            
            
            
            //exit();
			return;
        }

        
        
        $usuario=mainModel::limpiar_cadena($_POST['usuario_log']);
        $clave=mainModel::limpiar_cadena($_POST['clave_log']);
        
        /*== comprobar campos vacios ==*/
        if($usuario=="" || $clave==""){
            echo '
            <script>
                Swal.fire({
                    title: "Ocurrio un error inesperado",
                    text: "No has llenado todos los campos que son requeridos",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
            //exit();
            return;
        }

        /*== Verificando integridad de los datos ==*/
        if(!filter_var($usuario, FILTER_VALIDATE_EMAIL)){
            echo '
            <script>
                Swal.fire({
                    title: "Ocurrio un error inesperado",
                    text: "El correo del usuario no coincide con el formato solicitado",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
            return;
            //exit();
        }

        
        //verifica longitud del correo
        if (strlen($usuario) > 100) {
            echo '<script>Swal.fire({ title: "Error", text: "El correo es demasiado largo.", type: "error" });</script>';
            return; //exit();
        }
        //verifica longitud de la clave
        if (strlen($clave) < 7 || strlen($clave) > 100) {
            echo '<script>Swal.fire({ title: "Error", text: "La clave debe tener entre 7 y 100 caracteres.", type: "error" });</script>';
            return; //exit();
        }
        
        $resultado = loginModelo::autenticar_supabase($usuario, $clave);
        if($resultado['ok']){
            $datos = $resultado['data'];
            $perfil = $datos['profile'];
            $persona = $datos['person'];
            $idPerfil = $perfil[AUTH_PROFILE_ID_COLUMN] ?? null;
            $roleId = $datos['role_id'] ?? ($perfil[AUTH_PROFILE_ROLE_COLUMN] ?? null);
            $rol = $datos['role_number'] ?? null;
            $nombre = $persona['name'] ?? $usuario;
            $apellido = $persona['last_name'] ?? '';

            if($idPerfil === null || $rol === null){
                $this->mostrar_error_login('El perfil del usuario está incompleto.');
                return; //exit();
            }

            //mainModel::reiniciar_intentos_ip($_SERVER['REMOTE_ADDR'], 'login');
            //$this->reiniciar_intentos_controlador($_SERVER['REMOTE_ADDR'], 'login');
			$this->reiniciar_intentos_controlador($ip, 'login');

            
            
               // Solo iniciar sesión si no está activa
    if (session_status() !== PHP_SESSION_ACTIVE) {
        session_set_cookie_params([
            'lifetime' => AUTH_SESSION_SECONDS,
            'path'     => '/',
            'secure'   => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
            'httponly' => true,
            'samesite' => 'Lax'
        ]);
        session_start(['name' => 'SPM']);
    }
/*
            session_set_cookie_params([
                'lifetime' => AUTH_SESSION_SECONDS,
                'path' => '/',
                'secure' => (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off'),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            session_start(['name'=>'SPM']);*/
            session_regenerate_id(true);

            $_SESSION['id_spm'] = $idPerfil;
            $_SESSION['id_usuario'] = $idPerfil;
            $_SESSION['auth_user_id'] = $datos['auth_user_id'];
            $_SESSION['usuario_spm'] = $usuario;
            $_SESSION['nombre_spm'] = $nombre;
            $_SESSION['apellido_spm'] = $apellido;
            $_SESSION['cargo_spm'] = $rol;
            $_SESSION['role_id'] = $roleId;
            $_SESSION['estado_usuario'] = $perfil[AUTH_PROFILE_STATUS_COLUMN] ?? null;
            $_SESSION['token_spm'] = bin2hex(random_bytes(32));
            $_SESSION['auth_access_token'] = $datos['jwt'];
            $_SESSION['auth_refresh_token'] = $datos['refresh_token'];
            $_SESSION['ultima_actividad'] = time();
            $_SESSION['expira_en'] = time() + AUTH_SESSION_SECONDS;

            if((int) $rol === 5){
                $idEstudiante = $persona['id_estudiante'] ?? $persona['id'] ?? null;
                if($idEstudiante !== null){
                    $_SESSION['id_estudiante'] = $idEstudiante;
                }
            }


            header("Location: ".SERVERURL."index.php?views=admin/home");
            exit();
        } else {
			//$this->registrar_intento_fallido_controlador($_SERVER['REMOTE_ADDR'], 'login');
			//$this->registrar_intento_fallido_controlador($ip, 'login');
            $paco=$this->registrar_intento_fallido_controlador($ip, 'login');
//var_dump($paco);die();
            
            //mainModel::registrar_intento_ip($_SERVER['REMOTE_ADDR'], 'login');
            $this->mostrar_error_login($resultado['message'] ?? 'El USUARIO o CLAVE son incorrectos');
        }
    } /* Fin controlador */

    
    
    
    
    	//pendejada de oliver arreglada
         // === REDIRECCIÓN ORIGINAL ===
            /*switch ((int)$rol) {
                case 1:
                    header("Location: ".SERVERURL."index.php?views=admin/home");
                    break;
                case 4:
                    header("Location: ".SERVERURL."index.php?views=Supervisor de cursos/home");
                    break;
                case 3:
                    header("Location: ".SERVERURL."index.php?views=administrador de pagos/home");
                    break;
                case 2:
                    header("Location: ".SERVERURL."index.php?views=Control de estudio/home");
                    break;
                case 5:
                    header("Location: ".SERVERURL."index.php?views=Estudiante/home");
                    break;
                default:
                    echo '
                    <script>
                        Swal.fire({
                            title: "Error",
                            text: "Cargo no reconocido",
                            icon: "error",
                            confirmButtonText: "Aceptar"
                        });
                    </script>';
            }*/
    
    
    
    
    
    
    
    
    
    
    
    
    		/*--------- esta funcion es la original o creo q es la original de la demo, por ende,
            el sistema de redireccion debe ser parecido o al menos debo serciorarme de que siempre redirija las vistas a contenidos/admin/ ---------*/
		public function arreglar_pendejada_de_oliver(){
			$usuario=mainModel::limpiar_cadena($_POST['usuario_log']);
			$clave=mainModel::limpiar_cadena($_POST['clave_log']);

			/*== comprobar campos vacios ==*/
			if($usuario=="" || $clave==""){
				echo '
				<script>
					Swal.fire({
						title: "Ocurrio un error inesperado",
						text: "No has llenado todos los campos que son requeridos",
						type: "error",
						confirmButtonText: "Aceptar"
					});
				</script>
				';
				exit();
			}


			/*== Verificando integridad de los datos ==*/
			if(mainModel::verificar_datos("[a-zA-Z0-9]{1,35}",$usuario)){
				echo '
				<script>
					Swal.fire({
						title: "Ocurrio un error inesperado",
						text: "El NOMBRE DE USUARIO no coincide con el formato solicitado",
						type: "error",
						confirmButtonText: "Aceptar"
					});
				</script>
				';
				exit();
			}

			if(mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$clave)){
				echo '
				<script>
					Swal.fire({
						title: "Ocurrio un error inesperado",
						text: "La CLAVE no coincide con el formato solicitado",
						type: "error",
						confirmButtonText: "Aceptar"
					});
				</script>
				';
				exit();
			}

			$clave=mainModel::encryption($clave);

			$datos_login=[
				"Usuario"=>$usuario,
				"Clave"=>$clave
			];

			$datos_cuenta=loginModelo::iniciar_sesion_modelo($datos_login);
			
			if($datos_cuenta->rowCount()==1){
				$row=$datos_cuenta->fetch();

				session_start(['name'=>'SPM']);

				$_SESSION['id_spm']=$row['ID_usuario'];
				$_SESSION['nombre_spm']=$row['Nombre'];
				$_SESSION['privilegio_spm']=$row['cargo'];
				$_SESSION['token_spm']=md5(uniqid(mt_rand(),true));

				return header("Location: ".SERVERURL."home/");
			}else{
				echo '
				<script>
					Swal.fire({
						title: "Ocurrio un error inesperado",
						text: "El USUARIO o CLAVE son incorrectos",
						type: "error",
						confirmButtonText: "Aceptar"
					});
				</script>
				';
			}
		} /* Fin controlador */


    
    
    
    
    
    
    
/*
    public function validar_ip_controlador(): string {
        header('Content-Type: application/json');

        $ip   = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $tipo = mainModel::limpiar_cadena($_GET['tipo'] ?? 'login');

        if (!in_array($tipo, ['login', 'recuperacion', 'cc'], true)) {
            return json_encode(['bloqueado' => false, 'mensaje' => 'Tipo inválido.']);
        }

        $rpc = loginModelo::verificar_ip_bloqueada_supabase($ip, $tipo);

        if (!$rpc['ok'] || empty($rpc['data'])) {
            return json_encode(['bloqueado' => false]);
        }

        $data = $rpc['data'];

        if (!empty($data['bloqueado'])) {
            $minutos = (int) ceil(($data['tiempo_restante'] ?? 0) / 60);
            return json_encode([
                'bloqueado' => true,
                'mensaje'   => "Demasiados intentos fallidos. Intente más tarde en $minutos minutos.",
            ]);
        }

        return json_encode(['bloqueado' => false]);
    }*/


    
    
    
    
    
    
    
    
    
    
    
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


    /*----------------------------------------------------------logica de cambiar clave---------------------------------------------------------------*/
//recibe el correo y si esta registrado enviara el enlace despues, esta es de recuperar-cuenta view
    
    //IMPORTANTE: supabase permite solo 2 intentos por hora
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

        $resp = loginModelo::actualizar_clave_usuario($accessToken, $nuevaClave);

        if ($resp['ok']) {
            return json_encode(['ok' => true, 'mensaje' => 'Contraseña actualizada.']);
        }

        return json_encode(['ok' => false, 'mensaje' => $resp['message'] ?? 'Error al actualizar.']);
    }
    
    
    
    private function mostrar_error_login(string $mensaje){
        echo '<script>Swal.fire({title: "Ocurrio un error inesperado", text: ' . json_encode($mensaje) . ', type: "error", confirmButtonText: "Aceptar"});</script>';
    }

            /*=== NUEVO MÉTODO PARA OBTENER ID_ESTUDIANTE ===*/
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
    } /* Fin controlador */

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
            
            //                 "ResetTema"  => true   //pone la preferencia en modo claro
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
    } /* Fin controlador */
}