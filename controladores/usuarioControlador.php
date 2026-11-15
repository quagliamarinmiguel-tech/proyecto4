<?php

	if($peticionAjax){
		require_once "../modelos/usuarioModelo.php";
	}else{
		require_once "./modelos/usuarioModelo.php";
	}

	class usuarioControlador extends usuarioModelo{

        
       

    public function crear_usuario_staff_controlador(): string {
        header('Content-Type: application/json');
        
        try {
            $accessToken = $_SESSION['auth_access_token'] ?? null;
            if (!$accessToken) {
                return json_encode (mainModel::generarAlertaSimple('Sesión no disponible.', 'error', 'simple'));

            }
            if ((int) ($_SESSION['cargo_spm'] ?? 0) !== 1) {
                return json_encode (mainModel::generarAlertaSimple('No tiene los permisos requeridos para ejecutar esta acción.', 'error', 'simple'));
            }

            $clave1    = $_POST['clave1']    ?? '';
            $clave2    = $_POST['clave2']    ?? '';
            $cargo     = (int) ($_POST['cargo'] ?? 0);
            $ci        = mainModel::limpiar_cadena($_POST['ci_personal'] ?? '');
            $nombre    = mainModel::limpiar_cadena($_POST['nombre_personal'] ?? '');
            $apellido  = mainModel::limpiar_cadena($_POST['apellido_personal'] ?? '');
            $correo    = mainModel::limpiar_cadena($_POST['correo_personal'] ?? '');

            if ($clave1 === '' || $clave2 === '' || $ci === '' || $nombre === '' || $apellido === '' || $correo === '') {
                return json_encode (mainModel::generarAlertaSimple('Complete todos los campos obligatorios.', 'error', 'simple'));
            }
            if ($clave1 !== $clave2) {
                return json_encode (mainModel::generarAlertaSimple('Las contraseñas no coinciden.', 'error', 'simple'));

            }
            
            $validandoclave=mainModel::validar_clave_segura($clave1);
            if ($validandoclave !=null) {return json_encode(mainModel::generarAlertaSimple($validandoclave, 'error', 'simple'));}
            
    
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return json_encode (mainModel::generarAlertaSimple('El correo no es válido', 'error', 'simple'));
            }
            if ($cargo < 1 || $cargo > 4) {
                return json_encode (mainModel::generarAlertaSimple('El cargo no es válido', 'error', 'simple'));
            }

            $duplicado = usuarioModelo::verificar_staff_duplicado_supabase(
                $correo, $ci, $accessToken, null
            );

            if (!empty($duplicado['data'])) {
                $encontrado = $duplicado['data'][0];
                $ciDup     = ($encontrado['ci'] ?? '') === $ci;
                $emailDup  = ($encontrado['email'] ?? '') === $correo;

                if ($ciDup && $emailDup) {
                    $mensaje = 'La cédula y el correo ya están registrados.';
                } elseif ($ciDup) {
                    $mensaje = 'La cédula ya está registrada.';
                } else {
                    $mensaje = 'El correo ya está registrado.';
                }
                return json_encode (mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));

            }

            //crear usuario en auth.users (Admin API)
            $crearAuth = SupabaseApi::adminCreateUser($correo, $clave1,false);
            if (!$crearAuth['ok']) {
                return json_encode (mainModel::generarAlertaSimple($crearAuth['message'], 'error', 'simple'));

            }
            
            SupabaseApi::resendConfirmationEmail($correo);
            $userId = $crearAuth['user_id'];

            // Crear profile + staff via RPC
            $rpc = usuarioModelo::crear_staff_desde_auth_supabase(
                $userId, $correo, $ci, $nombre, $apellido, $cargo, $accessToken
            );

            $rpcData = $rpc['data'] ?? [];
            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                // Rollback: eliminar el usuario auth recién creado
                SupabaseApi::adminDeleteUser($userId);
                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo crear el usuario.';
                return json_encode (mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));

            }

                mainModel::invalidarCachePorPrefijo('staff_');   
                return json_encode (mainModel::generarAlertaSimple('Usuario creado correctamente', 'success', 'recargar'));


        } catch (Exception $e) {
            return json_encode(['valido' => false, 'mensaje' => 'Error: ' . $e->getMessage()]);
        }
    }
        
    public function validar_usuario_personal_controlador(): string {
        header('Content-Type: application/json');

        $ci     = mainModel::limpiar_cadena($_GET['ci'] ?? '');
        $correo = mainModel::limpiar_cadena($_GET['correo'] ?? '');
        $idEnc  = $_GET['id'] ?? null; // opcional

        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if (!$accessToken) {
            return json_encode (mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
        }

        // Desencriptar ID si viene (para edición)
        $excludeId = null;
        if ($idEnc !== null && $idEnc !== '') {
            $tmp = (int) mainModel::decryption($idEnc);
            if ($tmp > 0) $excludeId = $tmp;
        }

        $rpc = usuarioModelo::verificar_staff_duplicado_supabase(
            $correo, $ci, $accessToken, $excludeId
        );

        if (!$rpc['ok'] || empty($rpc['data'])) {
            return json_encode(['valido' => true]);
        }

        $encontrado = $rpc['data'][0];
        $ciDup     = ($encontrado['ci'] ?? '') === $ci;
        $emailDup  = ($encontrado['email'] ?? '') === $correo;

        if ($ciDup && $emailDup) {
            $mensaje = 'La cédula y el correo ya están registrados.';
        } elseif ($ciDup) {
            $mensaje = 'La cédula ya está registrada.';
        } elseif ($emailDup) {
            $mensaje = 'El correo ya está registrado.';
        } else {
            $mensaje = 'Los datos ya están registrados.';
        }
        return json_encode(['valido' => false, 'mensaje' => $mensaje]);
    }
        
        
//cambia de 1 a 0 el state de profile de staff
    public function desactivar_usuario_controlador(): string {
        $idEnc = $_POST['user_id_deactivate'] ?? '';
        $id = (int) mainModel::decryption($idEnc);
        $token = $_SESSION['auth_access_token'] ?? null;

        if ($id <= 0 || !$token) {
            return json_encode(mainModel::generarAlertaSimple('Datos inválidos', 'error', 'simple'));
        }

        $resp = usuarioModelo::actualizar_estado_profile_supabase($id, 0, $token);
        if ($resp['ok']) {
                mainModel::invalidarCachePorPrefijo('staff_');  
            return json_encode(mainModel::generarAlertaSimple('Usuario desactivado', 'success', 'recargar'));
        }
        return json_encode(mainModel::generarAlertaSimple('No se pudo desactivar', 'error', 'simple'));
    }

//cambia de 0 a 1 el state de profile de  staff
    public function reactivar_usuario_controlador(): string {
        $idEnc = $_POST['user_id_reactivate'] ?? '';
        $id = (int) mainModel::decryption($idEnc);
        $token = $_SESSION['auth_access_token'] ?? null;

        if ($id <= 0 || !$token) {
            return json_encode(mainModel::generarAlertaSimple('Datos inválidos', 'error', 'simple'));
        }

        $resp = usuarioModelo::actualizar_estado_profile_supabase($id, 1, $token);
        if ($resp['ok']) {
                mainModel::invalidarCachePorPrefijo('staff_'); 
            return json_encode(mainModel::generarAlertaSimple('Usuario reactivado', 'success', 'recargar'));
        }
        return json_encode(mainModel::generarAlertaSimple('No se pudo reactivar', 'error', 'simple'));
    }


//elimina staff, profile y user
    public function eliminar_usuario_controlador(): string {
        header('Content-Type: application/json');

        try {
            // 1. Validar sesión y permisos
            $accessToken = $_SESSION['auth_access_token'] ?? null;
            if (!$accessToken) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }
            if ((int) ($_SESSION['cargo_spm'] ?? 0) !== 1) {
                return json_encode(mainModel::generarAlertaSimple('No tiene permisos', 'error', 'simple'));
            }

            // Validar ID
            $idEnc   = $_POST['user_id_delete'] ?? '';
            $staffId = (int) mainModel::decryption($idEnc);
            if ($staffId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            // Borrar staff + profile vía RPC (una sola llamada, atómico)
            $rpc     = usuarioModelo::eliminar_staff_cascada_supabase($staffId, $accessToken);
            $rpcData = $rpc['data'] ?? [];

            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo eliminar el usuario';
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }

            // El RPC devuelve el user_id → borrar auth.users
            $userId = $rpcData['user_id'] ?? '';
            if ($userId !== '') {
                $delAuth = usuarioModelo::eliminar_auth_user_supabase($userId);
                if (!$delAuth['ok']) {
                    return json_encode(mainModel::generarAlertaSimple(
                        'Personal y perfil eliminados, pero no se pudo eliminar el acceso: ' . ($delAuth['message'] ?? 'Error'),
                        'warning', 'simple'
                    ));
                }
            }

                mainModel::invalidarCachePorPrefijo('staff_');
            return json_encode(mainModel::generarAlertaSimple('Usuario eliminado correctamente', 'success', 'recargar'));

        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        

    public function paginador_staff_controlador($pagina, $registros, $vista) {
        $pagina    = max(1, (int) $pagina);
        $registros = max(1, (int) $registros);
        $token     = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

        // Filtros desde GET
        
        // Default: solo activos. "2" o "" = todos.
        $stateParam = $_GET['filtro_state'] ?? '1';
        if ($stateParam === '2' || $stateParam === '' || $stateParam === null) {
            $stateFilter = null;   // todos
        } else {
            $stateFilter = (int) $stateParam;   // 1 o 0
        }

        $filtros = [
            'busqueda' => trim($_GET['busqueda'] ?? ''),
            'rol'      => trim($_GET['filtro_rol'] ?? '') ?: null,
            'state'    => $stateFilter,
            'orden'    => $_GET['orden'] ?? 'reciente',
        ];
        $resultado = usuarioModelo::listar_staff_supabase(
            $filtros,
            $token,
            $registros,
            ($pagina - 1) * $registros
        );

        if (!$resultado['ok']) {
            return '<div class="alert alert-danger">No se pudo cargar la lista de usuarios.</div>';
        }

        $items   = $resultado['data']['data'] ?? [];
        $total   = (int) ($resultado['data']['total'] ?? 0);
        $paginas = max(1, (int) ceil($total / $registros));

        // Carpeta del rol actual
        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        $carpeta = 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        $esAdmin = ($cargo === 1);

        // Encabezado de la tabla
        $tabla  = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center roboto-medium">';
        $tabla .= '<th>ID</th><th>Cédula</th><th>Nombre</th><th>Apellido</th><th>Correo</th><th>Rol</th><th>Estado</th>';
        if ($esAdmin) {
            $tabla .= '<th>Acciones</th>';
        }
        $tabla .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;

            foreach ($items as $item) {
                $id        = (int) $item['id'];
                $idEnc     = mainModel::encryption($id);
                $ci        = htmlspecialchars($item['ci'] ?? '', ENT_QUOTES, 'UTF-8');
                $nombre    = htmlspecialchars($item['name'] ?? '', ENT_QUOTES, 'UTF-8');
                $apellido  = htmlspecialchars($item['last_name'] ?? '', ENT_QUOTES, 'UTF-8');
                $correo    = htmlspecialchars($item['email'] ?? '', ENT_QUOTES, 'UTF-8');
                $rol       = htmlspecialchars($item['role'] ?? '', ENT_QUOTES, 'UTF-8');
                $state     = (int) ($item['state'] ?? 0);
                $esActivo  = ($state === 1);

                $estadoBadge = $esActivo
                    ? '<span class="badge bg-success" style="color:white;">Activo</span>'
                    : '<span class="badge bg-secondary" style="color:white;">Inactivo</span>';

                $tabla .= '<tr class="text-center">';
                $tabla .= '<td>' . $id . '</td>';
                $tabla .= '<td>' . $ci . '</td>';
                $tabla .= '<td>' . $nombre . '</td>';
                $tabla .= '<td>' . $apellido . '</td>';
                $tabla .= '<td>' . $correo . '</td>';
                $tabla .= '<td>' . $rol . '</td>';
                $tabla .= '<td>' . $estadoBadge . '</td>';

                if ($esAdmin) {
                    $tabla .= '<td style="white-space:nowrap;">';

                    if ($esActivo) {
                        // Editar (solo si activo)
                        $tabla .= '
                            <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/user-update&user_id_up=' . $idEnc . '"
                               class="btn btn-success btn-sm" title="Actualizar">
                                <i class="fas fa-sync-alt"></i>
                            </a>';

                        // Desactivar
                        $tabla .= '
                            <form class="FormularioAjax d-inline-block" action="' . SERVERURL . 'ajax/usuarioAjax.php" method="POST" data-form="deactivate" autocomplete="off">
                                <input type="hidden" name="user_id_deactivate" value="' . $idEnc . '">
                                <button type="submit" class="btn btn-warning btn-sm" title="Desactivar">
                                    <i class="fas fa-user-slash"></i>
                                </button>
                            </form>';
                    } else {
                        // Reactivar
                        $tabla .= '
                            <form class="FormularioAjax d-inline-block" action="' . SERVERURL . 'ajax/usuarioAjax.php" method="POST" data-form="reactivate" autocomplete="off">
                                <input type="hidden" name="user_id_reactivate" value="' . $idEnc . '">
                                <button type="submit" class="btn btn-info btn-sm" title="Reactivar">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>';

                        // Eliminar usuario. no se si dejarlo debido a q podria eliminar registros en activitylog *(mala mia)
                       /* $tabla .= '
                            <form class="FormularioAjax d-inline-block" action="' . SERVERURL . 'ajax/usuarioAjax.php" method="POST" data-form="delete2" autocomplete="off">
                                <input type="hidden" name="user_id_delete" value="' . $idEnc . '">
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar usuario">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>';*/
                    }

                    $tabla .= '</td>';
                }

                $tabla .= '</tr>';
            }

            $regFinal = $regInicio + count($items) - 1;
        } else {
            $columnas = $esAdmin ? 8 : 7;
            $tabla .= '<tr class="text-center"><td colspan="' . $columnas . '">No hay usuarios registrados.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador
        if ($total > 0) {
            $tabla .= '<p class="text-right">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';

            
            $paramsGet = array_filter([
                'busqueda'      => $_GET['busqueda'] ?? '',
                'filtro_rol'    => $_GET['filtro_rol'] ?? '',
                'filtro_state'  => $_GET['filtro_state'] ?? '',
                'orden'         => $_GET['orden'] ?? '',
            ], fn($v) => $v !== '' && $v !== null);
            
            $queryString = http_build_query($paramsGet);
            if ($queryString) {
                $queryString = '&' . $queryString;
            }

            $tabla .= mainModel::paginador_tablas(
                $pagina,
                $paginas,
                SERVERURL . 'index.php?views=' . $vistaCompleta . $queryString . '&page=',
                7
            );
        }

        return $tabla;
    }

    
        
        
//trae los datos necesarios para user update
    public function datos_staff_controlador(string $idEnc): array {
        $id = (int) mainModel::decryption($idEnc);
        if ($id <= 0) {
            return ['ok' => false, 'message' => 'ID inválido'];
        }

        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }

        $rpc = usuarioModelo::obtener_staff_por_id_supabase($id, $token);

        if (!$rpc['ok'] || empty($rpc['data'][0])) {
            return ['ok' => false, 'message' => 'Usuario no encontrado'];
        }

        $fila = $rpc['data'][0];
        $perfil = $fila['profile'] ?? [];
        $rol    = $perfil['role'] ?? [];
        $cc=$perfil['user_id'];

        return [
            'ok' => true,
            'data' => [
                'id'          => (int) ($fila['id'] ?? 0),
                'user_id'     => $cc,
                'ci'          => $fila['ci'] ?? '',
                'name'        => $fila['name'] ?? '',
                'last_name'   => $fila['last_name'] ?? '',
                'email'       => $fila['email'] ?? '',
                'profile_id'  => (int) ($perfil['id'] ?? 0),
                'state'       => (int) ($perfil['state'] ?? 0),
                'role_id'     => (int) ($rol['id'] ?? 0),
                'role'        => $rol['role'] ?? '',
                'role_number' => (int) ($rol['role_number'] ?? 0),
            ],
        ];
    }
       

       

//actualiza rol y hace lo de reenviar a un correo una vaina
    public function actualizar_usuario_staff_controlador(): string {
        header('Content-Type: application/json');

        try {
            $accessToken = $_SESSION['auth_access_token'] ?? null;
            if (!$accessToken) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }
            if ((int) ($_SESSION['cargo_spm'] ?? 0) !== 1) {
                return json_encode(mainModel::generarAlertaSimple('No tiene permisos', 'error', 'simple'));
            }

            $idEnc   = $_POST['staff_id_up'] ?? '';
            $staffId = (int) mainModel::decryption($idEnc);
            if ($staffId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $profileId = (int) mainModel::decryption($_POST['profile_id_enc'] ?? '');
            if ($profileId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('Perfil inválido', 'error', 'simple'));
            }

            $email = trim($_POST['staff_email'] ?? '');

            $cargo = (int) ($_POST['cargo'] ?? 0);
            if ($cargo < 1 || $cargo > 4) {
                return json_encode(mainModel::generarAlertaSimple('El cargo no es válido', 'error', 'simple'));
            }

            $reenviarConfirmacion = isset($_POST['reenviar_confirmacion']);

            $update = usuarioModelo::actualizar_rol_staff_supabase($profileId, $cargo, $accessToken);

            if (!$update['ok']) {
                $mensaje = $update['message'] ?? 'No se pudo actualizar el cargo';
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }


            if ($reenviarConfirmacion) {

                if ($email === '') {
                    mainModel::invalidarCachePorPrefijo('staff_');
                    return json_encode(mainModel::generarAlertaSimple(
                        'Cargo actualizado, pero el usuario no tiene correo registrado',
                        'warning', 'simple'
                    ));
                }

                // 1) Verificar el usuario en GoTrue antes de reenviar
                $authUser = SupabaseApi::adminGetUserByEmail($email);

                if (!$authUser['ok']) {
                    error_log('[actualizar_usuario_staff] adminGetUserByEmail falló: ' . json_encode($authUser));
                    mainModel::invalidarCachePorPrefijo('staff_');
                    return json_encode(mainModel::generarAlertaSimple(
                        'Cargo actualizado, pero no se pudo verificar el estado del correo: ' . $authUser['message'],
                        'warning', 'simple'
                    ));
                }

                if ($authUser['data'] === null) {
                    mainModel::invalidarCachePorPrefijo('staff_');
                    return json_encode(mainModel::generarAlertaSimple(
                        'Cargo actualizado, pero el correo no está registrado en el sistema de autenticación',
                        'warning', 'simple'
                    ));
                }

                // 2) Si YA confirmó, no reenviar (evita el falso positivo del 200 sin envío)
                if (!empty($authUser['data']['email_confirmed_at'])) {
                    mainModel::invalidarCachePorPrefijo('staff_');
                    return json_encode(mainModel::generarAlertaSimple(
                        'Cargo actualizado. El usuario ya había confirmado su correo, no se reenvió nada.',
                        'warning', 'simple'
                    ));
                }

                // 3) Solo si NO está confirmado, reenviar
                $envio = SupabaseApi::resendConfirmationEmail($email);

                if (!$envio['ok']) {
                    error_log('[actualizar_usuario_staff] resend falló: ' . json_encode($envio));

                    $status  = (int) ($envio['status'] ?? 0);
                    $mensaje = match (true) {
                        $status === 429 => 'Límite de envíos de correo alcanzado. Intente más tarde.',
                        $status === 404 => 'El correo no está registrado en el sistema de autenticación.',
                        $status === 422 => 'El correo ya fue confirmado o no es válido.',
                        $status >= 500  => 'Error del servidor de correo. Intente más tarde.',
                        default         => 'No se pudo reenviar el correo: ' . ($envio['message'] ?? 'Error desconocido'),
                    };

                    mainModel::invalidarCachePorPrefijo('staff_');
                    return json_encode(mainModel::generarAlertaSimple(
                        'Cargo actualizado. ' . $mensaje,
                        'warning', 'simple'
                    ));
                }
            }

            mainModel::invalidarCachePorPrefijo('staff_');
            return json_encode(mainModel::generarAlertaSimple(
                'Usuario actualizado correctamente',
                'success', 'recargar'
            ));

        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        
        
        
        
//la lista de seguimiento de actividad de user activity list
    public function paginador_actividad_controlador($pagina, $registros, $vista) {
        $pagina    = max(1, (int) $pagina);
        $registros = max(1, (int) $registros);
        $token     = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

        $filtros = [
            'busqueda'    => trim($_GET['busqueda'] ?? '') ?: null,
            'accion'      => trim($_GET['filtro_accion'] ?? '') ?: null,
            'tabla'       => trim($_GET['filtro_tabla'] ?? '') ?: null,
            'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
            'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null,
            'orden'       => $_GET['orden'] ?? 'reciente',
        ];
        
        $resultado = usuarioModelo::listar_activity_log_supabase(
            $filtros,
            $token,
            $registros,
            ($pagina - 1) * $registros
        );
        
        if (!$resultado['ok']) {
            return '<div class="alert alert-danger">No se pudo cargar la actividad.</div>';
        }

        $items   = $resultado['data']['data'] ?? [];
        $total   = (int) ($resultado['data']['total'] ?? 0);
        $paginas = max(1, (int) ceil($total / $registros));

        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        $carpetas = [
            1 => 'admin',
            2 => 'Control de estudio',
            3 => 'administrador de pagos',
            4 => 'Supervisor de cursos',
            5 => 'Estudiante',
        ];
        $carpeta       = $carpetas[$cargo] ?? 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        // Colores por acción
        $coloresAccion = [
            'INSERT' => 'success',
            'UPDATE' => 'warning',
            'DELETE' => 'danger',
        ];

        // Tabla
        $tabla  = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center roboto-medium">';
        $tabla .= '<th>ID</th><th>Fecha</th><th>Usuario</th><th>Cédula</th><th>Acción</th><th>Tabla</th><th>Registro afectado</th>';
        $tabla .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;

            foreach ($items as $item) {
                $id         = (int) $item['id'];
                $fecha      = $item['action_date'] ?? '';
                $accion     = strtoupper($item['action'] ?? '');
                $tablaNom   = htmlspecialchars($item['table_name'] ?? '', ENT_QUOTES, 'UTF-8');
                $rowId      = (int) ($item['row_id'] ?? 0);
                $nombre     = htmlspecialchars(trim(($item['staff_name'] ?? '') . ' ' . ($item['staff_last_name'] ?? '')), ENT_QUOTES, 'UTF-8');
                $ci         = htmlspecialchars($item['staff_ci'] ?? '', ENT_QUOTES, 'UTF-8');

                // Formatear fecha
                $fechaFmt = '';
                if ($fecha) {
                    try {
                        $dt = new DateTime($fecha);
                        $fechaFmt = $dt->format('d/m/Y H:i');
                    } catch (Exception $e) {
                        $fechaFmt = $fecha;
                    }
                }

                $colorAccion = $coloresAccion[$accion] ?? 'secondary';

                $tabla .= '<tr class="text-center">';
                $tabla .= '<td>' . $id . '</td>';
                $tabla .= '<td>' . $fechaFmt . '</td>';
                $tabla .= '<td>' . ($nombre ?: '<em>—</em>') . '</td>';
                $tabla .= '<td>' . ($ci ?: '<em>—</em>') . '</td>';
                $tabla .= '<td><span class="badge bg-' . $colorAccion . '" style="color:white;">' . htmlspecialchars($accion) . '</span></td>';
                $tabla .= '<td>' . $tablaNom . '</td>';
                $tabla .= '<td>#' . $rowId . '</td>';
                $tabla .= '</tr>';
            }

            $regFinal = $regInicio + count($items) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="7">No hay actividad registrada con esos filtros.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador
        if ($total > 0) {
            $tabla .= '<p class="text-right">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';

            $paramsGet = array_filter([
                'busqueda'      => $_GET['busqueda'] ?? '',
                'filtro_accion' => $_GET['filtro_accion'] ?? '',
                'filtro_tabla'  => $_GET['filtro_tabla'] ?? '',
                'fecha_desde'   => $_GET['fecha_desde'] ?? '',
                'fecha_hasta'   => $_GET['fecha_hasta'] ?? '',
                'orden'         => $_GET['orden'] ?? '',
            ]);
            $queryString = http_build_query($paramsGet);
            if ($queryString) {
                $queryString = '&' . $queryString;
            }

            $tabla .= mainModel::paginador_tablas(
                $pagina,
                $paginas,
                SERVERURL . 'index.php?views=' . $vistaCompleta . $queryString . '&page=',
                7
            );
        }

        return $tabla;
    }
        
        
        




    public function actualizar_usuario_unico_controlador(): string {
        header('Content-Type: application/json');

        try {
            $accessToken = $_SESSION['auth_access_token'] ?? null;
            if (!$accessToken) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $authUserId = $_SESSION['auth_user_id'] ?? '';
            if ($authUserId === '') {
                return json_encode(mainModel::generarAlertaSimple('No se pudo identificar al usuario', 'error', 'simple'));
            }

            $clave1   = trim($_POST['clave1'] ?? '');
            $clave2   = trim($_POST['clave2'] ?? '');
            $state    = (int) ($_POST['state'] ?? 1);
            $ci       = mainModel::limpiar_cadena($_POST['ci_personal'] ?? '');
            $nombre   = mainModel::limpiar_cadena($_POST['nombre_personal'] ?? '');
            $apellido = mainModel::limpiar_cadena($_POST['apellido_personal'] ?? '');
            $correo   = mainModel::limpiar_cadena($_POST['correo_personal'] ?? '');

            $staffActual = SupabaseApi::getWithToken('staff', [
                'select' => 'id,email,profile!inner(user_id, role_id, role!inner(role_number))',
                'profile.user_id' => 'eq.' . $authUserId,
                'limit' => '1',
            ], $accessToken);

            if (!$staffActual['ok'] || empty($staffActual['data'][0])) {
                return json_encode(mainModel::generarAlertaSimple('No se encontró el usuario', 'error', 'simple'));
            }

            $staffRow    = $staffActual['data'][0];
            $staffId     = (int) ($staffRow['id'] ?? 0);
            $emailActual = $staffRow['email'] ?? '';
            $userId      = $staffRow['profile']['user_id'] ?? '';
            $cargoActual = (int) ($staffRow['profile']['role']['role_number'] ?? 0);
            $cambiarEmail = ($emailActual !== $correo && $userId !== '');

            // Validaciones
            if ($ci === '' || $nombre === '' || $apellido === '' || $correo === '') {
                return json_encode(mainModel::generarAlertaSimple('Complete todos los campos obligatorios', 'error', 'simple'));
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return json_encode(mainModel::generarAlertaSimple('El correo no es válido', 'error', 'simple'));
            }
            if ($cargoActual < 1 || $cargoActual > 4) {
                return json_encode(mainModel::generarAlertaSimple('El rol del usuario no está configurado correctamente', 'error', 'simple'));
            }

            $cambiarClave = false;
            if ($clave1 !== '' || $clave2 !== '') {
                if ($clave1 === '' || $clave2 === '') {
                    return json_encode(mainModel::generarAlertaSimple('Debe completar ambas contraseñas o dejar ambas vacías', 'error', 'simple'));
                }
                if ($clave1 !== $clave2) {
                    return json_encode(mainModel::generarAlertaSimple('Las contraseñas no coinciden', 'error', 'simple'));
                }
                if (strlen($clave1) < 7) {
                    return json_encode(mainModel::generarAlertaSimple('La contraseña debe tener al menos 7 caracteres', 'error', 'simple'));
                }
                $cambiarClave = true;
            }


            $validandoclave=mainModel::validar_clave_segura($clave1);
            if ($validandoclave !=null) {return json_encode(mainModel::generarAlertaSimple($validandoclave, 'error', 'simple'));}


            // Duplicados (excluyendo el propio staff.id)
            $duplicado = usuarioModelo::verificar_staff_duplicado_supabase(
                $correo, $ci, $accessToken, $staffId
            );
            if (!empty($duplicado['data'])) {
                $encontrado = $duplicado['data'][0];
                $ciDup     = ($encontrado['ci'] ?? '') === $ci;
                $emailDup  = ($encontrado['email'] ?? '') === $correo;
                if ($ciDup && $emailDup)   $mensaje = 'La cédula y el correo ya están registrados.';
                elseif ($ciDup)            $mensaje = 'La cédula ya está registrada.';
                else                       $mensaje = 'El correo ya está registrado.';
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }

            // Actualizar staff + profile vía RPC (con el cargo actual)
            $rpc = usuarioModelo::actualizar_staff_desde_auth_supabase(
                $staffId, $ci, $nombre, $apellido, $correo, $cargoActual, $state, $accessToken
            );

            $rpcData = $rpc['data'] ?? [];
            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo actualizar';
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }

            // Actualizar auth.users si cambió email y/o password
            $nuevoEmail    = $cambiarEmail ? $correo  : null;
            $nuevaPassword = $cambiarClave ? $clave1  : null;

            if ($userId !== '' && ($nuevoEmail !== null || $nuevaPassword !== null)) {
                $resp = SupabaseApi::adminUpdateUser($userId, $nuevoEmail, $nuevaPassword);
                if (!$resp['ok']) {
                    return json_encode(mainModel::generarAlertaSimple(
                        'Datos actualizados, pero no se pudo actualizar el acceso: ' . $resp['message'],
                        'warning', 'simple'
                    ));
                }
            }

            return json_encode(mainModel::generarAlertaSimple('Perfil actualizado correctamente', 'success', 'recargar'));

        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }

//en caso de q el usuario en user config no se encuentre
    public function usuario_user_config_no_encontrado(){
        return json_encode(mainModel::generarAlertaRedireccion('Perfil no encontrado','El perfil del usuario en sesión no fue encontrado, o el perfil es de mantenimiento o configuración', 'error', ''. SERVERURL . 'index.php?views=admin/home'));
    }
        
        

//lo usa userconfig
    public function datos_staff_self_controlador(): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $authUserId = $_SESSION['auth_user_id'] ?? '';
        //var_dump($authUserId);die();
        if ($authUserId === '') return ['ok' => false, 'message' => 'Usuario no identificado'];

        $resp = usuarioModelo::datos_staff_por_auth_user_supabase($authUserId, $token);
        //var_dump($resp);die();
        if (!$resp['ok'] || empty($resp['data'][0])) {
            return ['ok' => false, 'message' => 'No se encontró el perfil'];
        }

        return ['ok' => true, 'data' => $resp['data'][0]];
    }


        
	}