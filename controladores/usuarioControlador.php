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
            // 1. Validar sesión y cargo (solo admin puede crear usuarios)
            $accessToken = $_SESSION['auth_access_token'] ?? null;
            if (!$accessToken) {
                //return json_encode(['valido' => false, 'mensaje' => 'Sesión no disponible.']);
                return json_encode (mainModel::generarAlertaSimple('Sesión no disponible.', 'error', 'simple'));

            }
            if ((int) ($_SESSION['cargo_spm'] ?? 0) !== 1) {
                //return json_encode(['valido' => false, 'mensaje' => 'No tienes permisos.']);
                return json_encode (mainModel::generarAlertaSimple('No tiene los permisos requeridos para ejecutar esta acción.', 'error', 'simple'));
            }

            // 2. Recoger y limpiar datos
            $clave1    = $_POST['clave1']    ?? '';
            $clave2    = $_POST['clave2']    ?? '';
            $cargo     = (int) ($_POST['cargo'] ?? 0);
            $ci        = mainModel::limpiar_cadena($_POST['ci_personal'] ?? '');
            $nombre    = mainModel::limpiar_cadena($_POST['nombre_personal'] ?? '');
            $apellido  = mainModel::limpiar_cadena($_POST['apellido_personal'] ?? '');
            $correo    = mainModel::limpiar_cadena($_POST['correo_personal'] ?? '');

            // 3. Validaciones básicas
            if ($clave1 === '' || $clave2 === '' || $ci === '' || $nombre === '' || $apellido === '' || $correo === '') {
                //return json_encode(['valido' => false, 'mensaje' => 'Complete todos los campos obligatorios.']);
                return json_encode (mainModel::generarAlertaSimple('Complete todos los campos obligatorios.', 'error', 'simple'));
            }
            if ($clave1 !== $clave2) {
                //return json_encode(['valido' => false, 'mensaje' => 'Las contraseñas no coinciden.']);
                return json_encode (mainModel::generarAlertaSimple('Las contraseñas no coinciden.', 'error', 'simple'));

            }
            if (strlen($clave1) < 7) {
                return json_encode (mainModel::generarAlertaSimple('La contraseña debe tener al menos 7 caracteres', 'error', 'simple'));
                //return json_encode(['valido' => false, 'mensaje' => 'La contraseña debe tener al menos 7 caracteres.']);
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return json_encode (mainModel::generarAlertaSimple('El correo no es válido', 'error', 'simple'));
                //return json_encode(['valido' => false, 'mensaje' => 'El correo no es válido.']);
            }
            if ($cargo < 1 || $cargo > 4) {
                return json_encode (mainModel::generarAlertaSimple('El cargo no es válido', 'error', 'simple'));
                //return json_encode(['valido' => false, 'mensaje' => 'El cargo no es válido.']);
            }

            // 4. Validar duplicados (correo o cédula)
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
                //return json_encode(['valido' => false, 'mensaje' => $mensaje]);
                return json_encode (mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));

            }

            // 5. Crear usuario en auth.users (Admin API)
            $crearAuth = SupabaseApi::adminCreateUser($correo, $clave1, false);
            if (!$crearAuth['ok']) {
                //return json_encode(['valido' => false, 'mensaje' => $crearAuth['message']]);
                return json_encode (mainModel::generarAlertaSimple($crearAuth['message'], 'error', 'simple'));

            }
            
            // Forzar el envío del correo de confirmación
            SupabaseApi::resendConfirmationEmail($correo);
            
            $userId = $crearAuth['user_id'];

            // 6. Crear profile + staff via RPC
            $rpc = usuarioModelo::crear_staff_desde_auth_supabase(
                $userId, $correo, $ci, $nombre, $apellido, $cargo, $accessToken
            );

            $rpcData = $rpc['data'] ?? [];
            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                // Rollback: eliminar el usuario auth recién creado
                SupabaseApi::adminDeleteUser($userId);
                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo crear el usuario.';
                //return json_encode(['valido' => false, 'mensaje' => $mensaje]);
                return json_encode (mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));

            }

          //  return json_encode(['valido' => true, 'mensaje' => 'Usuario creado correctamente.']);
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
            //return json_encode(['valido' => false, 'mensaje' => 'Sesión no disponible.']);
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
        //return json_encode (mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
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

            // 2. Validar ID
            $idEnc   = $_POST['user_id_delete'] ?? '';
            $staffId = (int) mainModel::decryption($idEnc);
            if ($staffId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            // 3. Borrar staff + profile vía RPC (una sola llamada, atómico)
            $rpc     = usuarioModelo::eliminar_staff_cascada_supabase($staffId, $accessToken);
            $rpcData = $rpc['data'] ?? [];

            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo eliminar el usuario';
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }

            // 4. El RPC devuelve el user_id → borrar auth.users
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
        $filtros = [
            'busqueda' => trim($_GET['busqueda'] ?? ''),
            'rol'      => trim($_GET['filtro_rol'] ?? '') ?: null,
            'state'    => isset($_GET['filtro_state']) && $_GET['filtro_state'] !== '' ? (int) $_GET['filtro_state'] : null,
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
        $carpetas = [
            1 => 'admin',
            2 => 'Control de estudio',
            3 => 'administrador de pagos',
            4 => 'Supervisor de cursos',
            5 => 'Estudiante',
        ];
        $carpeta       = $carpetas[$cargo] ?? 'admin';
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

                        // Eliminar usuario
                        $tabla .= '
                            <form class="FormularioAjax d-inline-block" action="' . SERVERURL . 'ajax/usuarioAjax.php" method="POST" data-form="delete2" autocomplete="off">
                                <input type="hidden" name="user_id_delete" value="' . $idEnc . '">
                                <button type="submit" class="btn btn-danger btn-sm" title="Eliminar usuario">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>';
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

        return [
            'ok' => true,
            'data' => [
                'id'          => (int) ($fila['id'] ?? 0),
                'user_id'     => $fila['user_id'] ?? '',
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
       



//actualiza usuario staff
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

            // Recoger datos
            $idEnc   = $_POST['staff_id_up'] ?? '';
            $staffId = (int) mainModel::decryption($idEnc);
            if ($staffId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $clave1   = trim($_POST['clave1'] ?? '');
            $clave2   = trim($_POST['clave2'] ?? '');
            $cargo    = (int) ($_POST['cargo'] ?? 0);
            $state    = (int) ($_POST['state'] ?? 1);
            $ci       = mainModel::limpiar_cadena($_POST['ci_personal'] ?? '');
            $nombre   = mainModel::limpiar_cadena($_POST['nombre_personal'] ?? '');
            $apellido = mainModel::limpiar_cadena($_POST['apellido_personal'] ?? '');
            $correo   = mainModel::limpiar_cadena($_POST['correo_personal'] ?? '');

                        //var_dump($clave1,$clave2,$cargo,$state,$ci,$nombre,$apellido,$correo);die();
            // Validaciones básicas
            if ($ci === '' || $nombre === '' || $apellido === '' || $correo === '') {
                return json_encode(mainModel::generarAlertaSimple('Complete todos los campos obligatorios', 'error', 'simple'));
            }
            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return json_encode(mainModel::generarAlertaSimple('El correo no es válido', 'error', 'simple'));
            }
            if ($cargo < 1 || $cargo > 4) {
                return json_encode(mainModel::generarAlertaSimple('El cargo no es válido', 'error', 'simple'));
            }

            // Validar claves si se enviaron
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

            // Obtener datos actuales del staff (para detectar cambio de email y user_id)
            $staffActual = SupabaseApi::getWithToken('staff', [
                'select' => 'email,profile!inner(user_id)',
                'id'     => 'eq.' . $staffId,
                'limit'  => '1',
            ], $accessToken);

            $emailActual  = $staffActual['data'][0]['email'] ?? '';
            $userId       = $staffActual['data'][0]['profile']['user_id'] ?? '';
            $cambiarEmail = ($emailActual !== $correo && $userId !== '');

            // Validar duplicados (correo o cédula)
            $duplicado = usuarioModelo::verificar_staff_duplicado_supabase(
                $correo, $ci, $accessToken, $staffId
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
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }

            // Actualizar profile + staff vía RPC
            $rpc = usuarioModelo::actualizar_staff_desde_auth_supabase(
                $staffId, $ci, $nombre, $apellido, $correo, $cargo, $state, $accessToken
            );

            $rpcData = $rpc['data'] ?? [];
            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo actualizar';
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }

            // Actualizar auth.users si cambió email y/o contraseña (una sola llamada)
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

            return json_encode(mainModel::generarAlertaSimple('Usuario actualizado correctamente', 'success', 'recargar'));

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

        // Filtros desde GET
        /*$filtros = [
            'busqueda'    => trim($_GET['busqueda'] ?? ''),
            'accion'      => trim($_GET['filtro_accion'] ?? '') ?: null,
            'tabla'       => trim($_GET['filtro_tabla'] ?? '') ?: null,
            'fecha_desde' => !empty($_GET['fecha_desde']) ? $_GET['fecha_desde'] : null,
            'fecha_hasta' => !empty($_GET['fecha_hasta']) ? $_GET['fecha_hasta'] : null,
            'orden'       => $_GET['orden'] ?? 'reciente',
        ];*/

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
//var_dump($resultado);die();
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
        
        
        
        
        
        
        
        
        
        
       
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        public function datos_usuario_personal_controlador($id_usuario) {
    $id_usuario = mainModel::limpiar_cadena($id_usuario);
    $conexion = mainModel::conectar();

    $sql = $conexion->prepare("
        SELECT 
            u.ID_usuario, u.Nombre, u.Password, u.estado_usuario, u.cargo, u.fecha_creacion,
            p.id_personal, p.nombre_personal, p.apellido_personal, p.ci_personal, p.correo_personal
        FROM usuario u
        INNER JOIN personal p ON p.id_usuario_fk = u.ID_usuario
        WHERE u.ID_usuario = :id
        LIMIT 1
    ");
    $sql->bindParam(":id", $id_usuario, PDO::PARAM_INT);
    $sql->execute();

    return $sql;
}
        
        
        
     public function agregar_personal_controlador() {
        // 🔹 Datos de usuario
        $nombre_u = mainModel::limpiar_cadena($_POST['Nombre_u'] ?? '');
        $clave1   = mainModel::limpiar_cadena($_POST['clave1'] ?? '');
        $clave2   = mainModel::limpiar_cadena($_POST['clave2'] ?? '');
        $cargo    = (int) ($_POST['cargo'] ?? 0);

        // 🔹 Datos personales
        $ci_personal       = (int) ($_POST['ci_personal'] ?? 0);
        $nombre_personal   = mainModel::limpiar_cadena($_POST['nombre_personal'] ?? '');
        $apellido_personal = mainModel::limpiar_cadena($_POST['apellido_personal'] ?? '');
        $correo_personal   = mainModel::limpiar_cadena($_POST['correo_personal'] ?? '');

        // 🔹 Validaciones combinadas
        if($nombre_u == "" || $clave1 == "" || $clave2 == "" || $ci_personal <= 0 || $nombre_personal == "" || $apellido_personal == "" || $correo_personal == "") {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar todos los campos de usuario y personal.",
                "Tipo"=>"error"
            ]);
        }

        if($clave1 !== $clave2){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Claves no coinciden",
                "Texto"=>"Las contraseñas ingresadas deben ser iguales.",
                "Tipo"=>"error"
            ]);
        }

        if($cargo < 1 || $cargo > 5){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Cargo inválido",
                "Texto"=>"El cargo debe estar entre 1 y 5.",
                "Tipo"=>"error"
            ]);
        }

        if(!filter_var($correo_personal, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo inválido",
                "Texto"=>"Debes ingresar un correo electrónico válido.",
                "Tipo"=>"error"
            ]);
        }

        // 🔹 Verificar duplicados
        $pdo = mainModel::conectar();

        $check_usuario = $pdo->prepare("SELECT id_usuario FROM usuario WHERE Nombre = :nombre");
        $check_usuario->bindParam(":nombre", $nombre_u);
        $check_usuario->execute();
        if($check_usuario->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Usuario duplicado",
                "Texto"=>"Ya existe un usuario con ese nombre.",
                "Tipo"=>"error"
            ]);
        }

        $check_ci = $pdo->prepare("SELECT ci_personal FROM personal WHERE ci_personal = :ci");
        $check_ci->bindParam(":ci", $ci_personal);
        $check_ci->execute();
        if($check_ci->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Cédula duplicada",
                "Texto"=>"Ya existe un registro con esa cédula.",
                "Tipo"=>"error"
            ]);
        }

        $check_correo = $pdo->prepare("SELECT correo_personal FROM personal WHERE correo_personal = :correo");
        $check_correo->bindParam(":correo", $correo_personal);
        $check_correo->execute();
        if($check_correo->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo duplicado",
                "Texto"=>"Ya existe un registro con ese correo.",
                "Tipo"=>"error"
            ]);
        }

        // 🔹 Crear usuario
        //$clave_segura = password_hash($clave1, PASSWORD_BCRYPT, ["cost"=>10]);
        $clave_segura = mainModel::encryption($clave1);
        $id_usuario = usuarioModelo3::agregar_usuario_modelo([
            "nombre_usuario" => $nombre_u,
            "clave_usuario"  => $clave_segura,
            "cargo_usuario"  => $cargo
        ]);

        if($id_usuario <= 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al crear usuario",
                "Texto"=>"No se pudo registrar el usuario.",
                "Tipo"=>"error"
            ]);
        }

        // 🔹 Crear personal
        $guardar_personal = usuarioModelo3::agregar_personal_modelo([
            "ci_personal"      => $ci_personal,
            "nombre_personal"  => $nombre_personal,
            "apellido_personal"=> $apellido_personal,
            "correo_personal"  => $correo_personal,
            "id_usuario_fk"    => $id_usuario
        ]);

        if($guardar_personal == 1){
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Personal registrado",
                "Texto"=>"El usuario y sus datos personales fueron guardados correctamente.",
                "Tipo"=>"success"
            ]);
        } else {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al guardar personal",
                "Texto"=>"El usuario fue creado pero no se pudo guardar los datos personales.",
                "Tipo"=>"error"
            ]);
        }
    }

        

        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
    public function actualizar_cuenta_per_controlador() {
        $id_usuario = mainModel::decryption($_POST['usuario_per_id_up'] ?? '');
        $id_usuario = mainModel::limpiar_cadena($id_usuario);


                
                
        // 🔹 Datos de usuario
        $nombre_u = mainModel::limpiar_cadena($_POST['Nombre_u'] ?? '');
        $clave1   = mainModel::limpiar_cadena($_POST['clave1'] ?? '');
        $clave2   = mainModel::limpiar_cadena($_POST['clave2'] ?? '');
        $cargo    = (int) ($_POST['cargo'] ?? 0);
        $estado   = isset($_POST['estado_usuario']) ? 1 : 0;

        // 🔹 Datos personales
        $ci_personal       = (int) ($_POST['ci_personal'] ?? 0);
        $nombre_personal   = mainModel::limpiar_cadena($_POST['nombre_personal'] ?? '');
        $apellido_personal = mainModel::limpiar_cadena($_POST['apellido_personal'] ?? '');
        $correo_personal   = mainModel::limpiar_cadena($_POST['correo_personal'] ?? '');

  //   echo "<pre>";
//print_r($_POST);
//echo "</pre>";
                         
        // 🔹 Validaciones
        if($nombre_u == "" || $ci_personal <= 0 || $nombre_personal == "" || $apellido_personal == "" || $correo_personal == "") {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar todos los campos obligatorios.",
                "Tipo"=>"error"
            ]);
        }

        if($cargo < 1 || $cargo > 5){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Cargo inválido",
                "Texto"=>"El cargo debe estar entre 1 y 5.",
                "Tipo"=>"error"
            ]);
        }

        if(!filter_var($correo_personal, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo inválido",
                "Texto"=>"Debes ingresar un correo electrónico válido.",
                "Tipo"=>"error"
            ]);
        }

        if($clave1 !== "" || $clave2 !== "") {
            if($clave1 !== $clave2 || strlen($clave1) < 7){
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"Error en la contraseña",
                    "Texto"=>"Las contraseñas deben coincidir y tener al menos 7 caracteres.",
                    "Tipo"=>"error"
                ]);
            }
            $clave_segura = mainModel::encryption($clave1);
        } else {
            $clave_segura = mainModel::conectar()
                ->query("SELECT Password FROM usuario WHERE ID_usuario = '$id_usuario'")
                ->fetchColumn();
        }

        // 🔹 Verificar duplicados excluyendo el actual
        $pdo = mainModel::conectar();

        $check_usuario = $pdo->prepare("SELECT ID_usuario FROM usuario WHERE Nombre = :nombre AND ID_usuario != :id");
        $check_usuario->bindParam(":nombre", $nombre_u);
        $check_usuario->bindParam(":id", $id_usuario);
        $check_usuario->execute();
        if($check_usuario->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Usuario duplicado",
                "Texto"=>"Ya existe otro usuario con ese nombre.",
                "Tipo"=>"error"
            ]);
        }

        $check_ci = $pdo->prepare("
            SELECT p.id_personal FROM personal p
            INNER JOIN usuario u ON u.ID_usuario = p.id_usuario_fk
            WHERE p.ci_personal = :ci AND u.ID_usuario != :id
        ");
        $check_ci->bindParam(":ci", $ci_personal);
        $check_ci->bindParam(":id", $id_usuario);
        $check_ci->execute();
        if($check_ci->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Cédula duplicada",
                "Texto"=>"Ya existe otro registro con esa cédula.",
                "Tipo"=>"error"
            ]);
        }

        $check_correo = $pdo->prepare("
            SELECT p.id_personal FROM personal p
            INNER JOIN usuario u ON u.ID_usuario = p.id_usuario_fk
            WHERE p.correo_personal = :correo AND u.ID_usuario != :id
        ");
        $check_correo->bindParam(":correo", $correo_personal);
        $check_correo->bindParam(":id", $id_usuario);
        $check_correo->execute();
        if($check_correo->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo duplicado",
                "Texto"=>"Ya existe otro registro con ese correo.",
                "Tipo"=>"error"
            ]);
            
            

        }

        // 🔹 Actualizar usuario
        $usuario_actualizado = usuarioModelo3::actualizar_usuario_per_modelo([
            "ID"       => $id_usuario,
            "Nombre"   => $nombre_u,
            "Password" => $clave_segura,
            "Cargo"    => $cargo,
            "Estado"   => $estado
        ]);

        // 🔹 Actualizar personal
        $personal_actualizado = usuarioModelo3::actualizar_cuenta_personal_modelo([
            "ci_personal"      => $ci_personal,
            "nombre_personal"  => $nombre_personal,
            "apellido_personal"=> $apellido_personal,
            "correo_personal"  => $correo_personal,
            "id_usuario_fk"    => $id_usuario
        ]);

        if($usuario_actualizado->rowCount() > 0 || $personal_actualizado->rowCount() > 0){
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Datos actualizados",
                "Texto"=>"El usuario y sus datos personales fueron modificados correctamente.",
                "Tipo"=>"success"
            ]);
        } else {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Actualización incompleta",
                "Texto"=>"No se pudo actualizar correctamente el usuario o el personal.",
                "Tipo"=>"error"
            ]);
        }
    }
        
        
 
    public function paginador_personal_controlador($pagina, $registros, $vista, $busqueda) {
        $pagina    = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $vista     = mainModel::limpiar_cadena($vista);
        $busqueda  = mainModel::limpiar_cadena($busqueda);

        $orden = $_GET['orden'] ?? "";
        $orden_sql = match($orden) {
            "nombre" => "ORDER BY nombre_personal ASC",
            "fecha"  => "ORDER BY usuario.ID_usuario DESC",
            default  => "ORDER BY usuario.ID_usuario ASC"
        };

        $filtro_usuario  = $_GET['filtro_usuario']  ?? "";
        $filtro_personal = $_GET['filtro_personal'] ?? "";
        $filtro_ci       = $_GET['filtro_ci']       ?? "";
        $filtro_cargo    = $_GET['filtro_cargo']    ?? "";
        $filtro_estado   = $_GET['filtro_estado']   ?? "";

        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        // WHERE dinámico
        $where = "usuario.ID_usuario = personal.id_usuario_fk";
        if ($busqueda != "") {
            $where .= " AND (nombre_personal LIKE '%$busqueda%' OR apellido_personal LIKE '%$busqueda%' OR ci_personal LIKE '%$busqueda%')";
        }
        if ($filtro_usuario != "")  $where .= " AND usuario.ID_usuario = '$filtro_usuario'";
        if ($filtro_personal != "") $where .= " AND personal.id_personal = '$filtro_personal'";
        if ($filtro_ci != "")       $where .= " AND personal.ci_personal = '$filtro_ci'";
        if ($filtro_cargo != "")    $where .= " AND usuario.cargo = '$filtro_cargo'";
        if ($filtro_estado != "")   $where .= " AND usuario.estado_usuario = '$filtro_estado'";

        $consulta = "
            SELECT SQL_CALC_FOUND_ROWS 
                usuario.ID_usuario, usuario.Nombre, usuario.cargo, usuario.estado_usuario,
                personal.id_personal, personal.nombre_personal, personal.apellido_personal,
                personal.ci_personal, personal.correo_personal
            FROM usuario, personal
            WHERE $where
            $orden_sql
            LIMIT $inicio, $registros
        ";

        $conexion = mainModel::conectar();
        $datos    = $conexion->query($consulta)->fetchAll();
        $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        $Npaginas = ceil($total / $registros);

        // Construcción de tabla
        $tabla = '<div class="table-responsive">
            <table class="table table-dark table-sm">
                <thead>
                    <tr class="text-center roboto-medium">
                        <th>ID Usuario</th>
                        <th>ID Personal</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Usuario</th>
                        <th>Cédula</th>
                        <th>Correo</th>
                        <th>Cargo</th>
                        <th>Activo</th>
                        <th>Actualizar</th>
                    </tr>
                </thead>
                <tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $reg_inicio = $inicio + 1;

            foreach ($datos as $rows) {
                $activo = $rows['estado_usuario'] == 1 ? "Sí" : "No";

                $tabla .= '
                <tr class="text-center">
                    <td>' . $rows['ID_usuario'] . '</td>
                    <td>' . $rows['id_personal'] . '</td>
                    <td>' . $rows['nombre_personal'] . '</td>
                    <td>' . $rows['apellido_personal'] . '</td>
                    <td>' . $rows['Nombre'] . '</td>
                    <td>' . $rows['ci_personal'] . '</td>
                    <td>' . $rows['correo_personal'] . '</td>
                    <td>' . $rows['cargo'] . '</td>
                    <td>' . $activo . '</td>
                    <td>
                        <a href="' . SERVERURL . 'index.php?views=user-upt-per&usuario_id_up=' . mainModel::encryption($rows['ID_usuario']) . '" class="btn btn-success">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </td>
                </tr>';
            }

            $reg_final = $reg_inicio + count($datos) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="10">';
            $tabla .= ($total >= 1)
                ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic acá para recargar el listado</a>'
                : 'No hay registros en el sistema';
            $tabla .= '</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador con filtros
        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando personal ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

            $queryParams = http_build_query([
                'orden' => $orden,
                'filtro_usuario' => $filtro_usuario,
                'filtro_personal' => $filtro_personal,
                'filtro_ci' => $filtro_ci,
                'filtro_cargo' => $filtro_cargo,
                'filtro_estado' => $filtro_estado,
                'busqueda' => $busqueda
            ]);

            $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
        }

        return $tabla;
    }
               
        
        
        
        
        
        
        
        
        
        
        
        
        
        
		/*--------- Controlador agregar usuario ---------*
		public function agregar_usuario_controlador(){
			$dni=mainModel::limpiar_cadena($_POST['usuario_dni_reg']);
			$nombre=mainModel::limpiar_cadena($_POST['usuario_nombre_reg']);
			$apellido=mainModel::limpiar_cadena($_POST['usuario_apellido_reg']);
			$telefono=mainModel::limpiar_cadena($_POST['usuario_telefono_reg']);
			$direccion=mainModel::limpiar_cadena($_POST['usuario_direccion_reg']);

			$usuario=mainModel::limpiar_cadena($_POST['usuario_usuario_reg']);
			$email=mainModel::limpiar_cadena($_POST['usuario_email_reg']);
			$clave1=mainModel::limpiar_cadena($_POST['usuario_clave_1_reg']);
			$clave2=mainModel::limpiar_cadena($_POST['usuario_clave_2_reg']);


			$privilegio=mainModel::limpiar_cadena($_POST['usuario_privilegio_reg']);


			if($dni=="" || $nombre=="" || $apellido=="" || $usuario=="" || $clave1=="" || $clave2==""){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No has llenado todos los campos que son obligatorios",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}


			if(mainModel::verificar_datos("[0-9-]{10,20}",$dni)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El DNI no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if(mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,35}",$nombre)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El NOMBRE no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if(mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,35}",$apellido)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El APELLIDO no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if($telefono!=""){
				if(mainModel::verificar_datos("[0-9()+]{8,20}",$telefono)){
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"El TELEFONO no coincide con el formato solicitado",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}
			}

			if($direccion!=""){
				if(mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}",$direccion)){
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"La DIRECCION no coincide con el formato solicitado",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}
			}

			if(mainModel::verificar_datos("[a-zA-Z0-9]{1,35}",$usuario)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El NOMBRE DE USUARIO no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if(mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$clave1) || mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$clave2)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Las CLAVES no coinciden con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			$check_dni=mainModel::ejecutar_consulta_simple("SELECT usuario_dni FROM usuario WHERE usuario_dni='$dni'");
			if($check_dni->rowCount()>0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El DNI ingresado ya se encuentra registrado en el sistema",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			$check_user=mainModel::ejecutar_consulta_simple("SELECT usuario_usuario FROM usuario WHERE usuario_usuario='$usuario'");
			if($check_user->rowCount()>0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El NOMBRE DE USUARIO ingresado ya se encuentra registrado en el sistema",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if($email!=""){
				if(filter_var($email,FILTER_VALIDATE_EMAIL)){
					$check_email=mainModel::ejecutar_consulta_simple("SELECT usuario_email FROM usuario WHERE usuario_email='$email'");
					if($check_email->rowCount()>0){
						$alerta=[
							"Alerta"=>"simple",
							"Titulo"=>"Ocurrió un error inesperado",
							"Texto"=>"El EMAIL ingresado ya se encuentra registrado en el sistema",
							"Tipo"=>"error"
						];
						echo json_encode($alerta);
						exit();
					}
				}else{
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"Ha ingresado un correo no valido",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}
			}


			if($clave1!=$clave2){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Las claves que acaba de ingresar no coinciden",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}else{
				$clave=mainModel::encryption($clave1);
			}

			if($privilegio<1 || $privilegio>3){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El privilegio seleccionado no es valido",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			$datos_usuario_reg=[
				"DNI"=>$dni,
				"Nombre"=>$nombre,
				"Apellido"=>$apellido,
				"Telefono"=>$telefono,
				"Direccion"=>$direccion,
				"Email"=>$email,
				"Usuario"=>$usuario,
				"Clave"=>$clave,
				"Estado"=>"Activa",
				"Privilegio"=>$privilegio
			];

			$agregar_usuario=usuarioModelo::agregar_usuario_modelo($datos_usuario_reg);

			if($agregar_usuario->rowCount()==1){
				$alerta=[
					"Alerta"=>"limpiar",
					"Titulo"=>"usuario registrado",
					"Texto"=>"Los datos del usuario han sido registrados con exito",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido registrar el usuario",
					"Tipo"=>"error"
				];
			}
			echo json_encode($alerta);
		}


		/*--------- Controlador paginar usuario ---------*
		public function paginador_usuario_controlador($pagina,$registros,$privilegio,$id,$url,$busqueda){

			$pagina=mainModel::limpiar_cadena($pagina);
			$registros=mainModel::limpiar_cadena($registros);
			$privilegio=mainModel::limpiar_cadena($privilegio);
			$id=mainModel::limpiar_cadena($id);

			$url=mainModel::limpiar_cadena($url);
			$url=SERVERURL.$url."/";

			$busqueda=mainModel::limpiar_cadena($busqueda);
			$tabla="";

			$pagina= (isset($pagina) && $pagina>0) ? (int) $pagina : 1 ;
			$inicio= ($pagina>0) ? (($pagina*$registros)-$registros) : 0 ;

			if(isset($busqueda) && $busqueda!=""){
				$consulta="SELECT SQL_CALC_FOUND_ROWS * FROM usuario WHERE ((usuario_id!='$id' AND usuario_id!='1') AND (usuario_dni LIKE '%$busqueda%' OR usuario_nombre LIKE '%$busqueda%' OR usuario_apellido LIKE '%$busqueda%' OR usuario_telefono LIKE '%$busqueda%' OR usuario_email LIKE '%$busqueda%' OR usuario_usuario LIKE '%$busqueda%')) ORDER BY usuario_nombre ASC LIMIT $inicio,$registros";
			}else{
				$consulta="SELECT SQL_CALC_FOUND_ROWS * FROM usuario WHERE usuario_id!='$id' AND usuario_id!='1' ORDER BY usuario_nombre ASC LIMIT $inicio,$registros";
			}

			$conexion = mainModel::conectar();

			$datos = $conexion->query($consulta);
			$datos = $datos->fetchAll();

			$total = $conexion->query("SELECT FOUND_ROWS()");
			$total = (int) $total->fetchColumn();

			$Npaginas=ceil($total/$registros);

			
			$tabla.='<div class="table-responsive">
				<table class="table table-dark table-sm">
					<thead>
						<tr class="text-center roboto-medium">
							<th>#</th>
							<th>DNI</th>
							<th>NOMBRE</th>
							<th>TELÉFONO</th>
							<th>USUARIO</th>
							<th>EMAIL</th>
							<th>ACTUALIZAR</th>
							<th>ELIMINAR</th>
						</tr>
					</thead>
					<tbody>';
			if($total>=1 && $pagina<=$Npaginas){
				$contador=$inicio+1;
				$reg_inicio=$inicio+1;
				foreach($datos as $rows){
					$tabla.='
					<tr class="text-center" >
						<td>'.$contador.'</td>
						<td>'.$rows['usuario_dni'].'</td>
						<td>'.$rows['usuario_nombre'].' '.$rows['usuario_apellido'].'</td>
						<td>'.$rows['usuario_telefono'].'</td>
						<td>'.$rows['usuario_usuario'].'</td>
						<td>'.$rows['usuario_email'].'</td>
						<td>
							<a href="'.SERVERURL.'user-update/'.mainModel::encryption($rows['usuario_id']).'/" class="btn btn-success">
									<i class="fas fa-sync-alt"></i>	
							</a>
						</td>
						<td>
							<form class="FormularioAjax" action="'.SERVERURL.'ajax/usuarioAjax.php" method="POST" data-form="delete" autocomplete="off">
								<input type="hidden" name="usuario_id_del" value="'.mainModel::encryption($rows['usuario_id']).'">
								<button type="submit" class="btn btn-warning">
										<i class="far fa-trash-alt"></i>
								</button>
							</form>
						</td>
					</tr>';
					$contador++;
				}
				$reg_final=$contador-1;
			}else{
				if($total>=1){
					$tabla.='<tr class="text-center" ><td colspan="9">
					<a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">Haga clic aca para recargar el listado</a>
					</td></tr>';
				}else{
					$tabla.='<tr class="text-center" ><td colspan="9">No hay registros en el sistema</td></tr>';
				}
			}

			$tabla.='</tbody></table></div>';

			if($total>=1 && $pagina<=$Npaginas){
				$tabla.='<p class="text-right">Mostrando usuario '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';

				$tabla.=mainModel::paginador_tablas($pagina,$Npaginas,$url,7);
			}

			return $tabla;
		} 


		public function eliminar_usuario_controlador(){

			$id=mainModel::decryption($_POST['usuario_id_del']);
			$id=mainModel::limpiar_cadena($id);

			if($id==1){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos eliminar el usuario principal del sistema",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}


			$check_usuario=mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM usuario WHERE usuario_id='$id'");
			if($check_usuario->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El usuario que intenta eliminar no existe en el sistema",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			$check_prestamos=mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM prestamo WHERE usuario_id='$id' LIMIT 1");
			if($check_prestamos->rowCount()>0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No podemos eliminar este usuario debido a que tiene prestamos asociados, recomendamos deshabilitar el usuario si ya no sera utilizado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			session_start(['name'=>'SPM']);
			if($_SESSION['privilegio_spm']!=1){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No tienes los permisos necesarios para realizar esta operacion",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}


			$eliminar_usuario=usuarioModelo::eliminar_usuario_modelo($id);

			if($eliminar_usuario->rowCount()==1){
				$alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"Usuario eliminado",
					"Texto"=>"El usuario ha sido eliminado del sistema exitosamente",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido eliminar el usuario, por favor intente nuevamente",
					"Tipo"=>"error"
				];
			}
			echo json_encode($alerta);
		} 


		public function datos_usuario_controlador($tipo,$id){
			$tipo=mainModel::limpiar_cadena($tipo);

			$id=mainModel::decryption($id);
			$id=mainModel::limpiar_cadena($id);

			return usuarioModelo::datos_usuario_modelo($tipo,$id);
		} 


		public function actualizar_usuario_controlador(){

			// Recibiendo el id
			$id=mainModel::decryption($_POST['usuario_id_up']);
			$id=mainModel::limpiar_cadena($id);

			// comprobar el usuario en la BD
			$check_user=mainModel::ejecutar_consulta_simple("SELECT * FROM usuario WHERE usuario_id='$id'");
			if($check_user->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos encontrado el usuario en el sistema",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}else{
				$campos=$check_user->fetch();
			}

			$dni=mainModel::limpiar_cadena($_POST['usuario_dni_up']);
			$nombre=mainModel::limpiar_cadena($_POST['usuario_nombre_up']);
			$apellido=mainModel::limpiar_cadena($_POST['usuario_apellido_up']);
			$telefono=mainModel::limpiar_cadena($_POST['usuario_telefono_up']);
			$direccion=mainModel::limpiar_cadena($_POST['usuario_direccion_up']);

			$usuario=mainModel::limpiar_cadena($_POST['usuario_usuario_up']);
			$email=mainModel::limpiar_cadena($_POST['usuario_email_up']);

			if(isset($_POST['usuario_estado_up'])){
				$estado=mainModel::limpiar_cadena($_POST['usuario_estado_up']);
			}else{
				$estado=$campos['usuario_estado'];
			}

			if(isset($_POST['usuario_privilegio_up'])){
				$privilegio=mainModel::limpiar_cadena($_POST['usuario_privilegio_up']);
			}else{
				$privilegio=$campos['usuario_privilegio'];
			}

			$admin_usuario=mainModel::limpiar_cadena($_POST['usuario_admin']);

			$admin_clave=mainModel::limpiar_cadena($_POST['clave_admin']);

			$tipo_cuenta=mainModel::limpiar_cadena($_POST['tipo_cuenta']);

			if($dni=="" || $nombre=="" || $apellido=="" || $usuario=="" || $admin_usuario=="" || $admin_clave==""){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No has llenado todos los campos que son obligatorios",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if(mainModel::verificar_datos("[0-9-]{10,20}",$dni)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El DNI no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if(mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,35}",$nombre)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El NOMBRE no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if(mainModel::verificar_datos("[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,35}",$apellido)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El APELLIDO no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if($telefono!=""){
				if(mainModel::verificar_datos("[0-9()+]{8,20}",$telefono)){
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"El TELEFONO no coincide con el formato solicitado",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}
			}

			if($direccion!=""){
				if(mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,190}",$direccion)){
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"La DIRECCION no coincide con el formato solicitado",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}
			}

			if(mainModel::verificar_datos("[a-zA-Z0-9]{1,35}",$usuario)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El NOMBRE DE USUARIO no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if(mainModel::verificar_datos("[a-zA-Z0-9]{1,35}",$admin_usuario)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"TU NOMBRE DE USUARIO no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if(mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$admin_clave)){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"TU CLAVE no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			$admin_clave=mainModel::encryption($admin_clave);

			if($privilegio<1 || $privilegio>3){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El privilegio no corresponde a un valor valido",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if($estado!="Activa" && $estado!="Deshabilitada"){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"El estado de la cuenta no coincide con el formato solicitado",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			if($dni!=$campos['usuario_dni']){
				$check_dni=mainModel::ejecutar_consulta_simple("SELECT usuario_dni FROM usuario WHERE usuario_dni='$dni'");
				if($check_dni->rowCount()>0){
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"El DNI ingresado ya se encuentra registrado en el sistema",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}
			}

			if($usuario!=$campos['usuario_usuario']){
				$check_user=mainModel::ejecutar_consulta_simple("SELECT usuario_usuario FROM usuario WHERE usuario_usuario='$usuario'");
				if($check_user->rowCount()>0){
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"El NOMBRE DE USUARIO ingresado ya se encuentra registrado en el sistema",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}	
			}


			if($email!=$campos['usuario_email'] && $email!=""){
				if(filter_var($email,FILTER_VALIDATE_EMAIL)){
					$check_email=mainModel::ejecutar_consulta_simple("SELECT usuario_email FROM usuario WHERE usuario_email='$email'");
					if($check_email->rowCount()>0){
						$alerta=[
							"Alerta"=>"simple",
							"Titulo"=>"Ocurrió un error inesperado",
							"Texto"=>"El nuevo email ingresado ya se encuentra registrado en el sistema",
							"Tipo"=>"error"
						];
						echo json_encode($alerta);
						exit();
					}
				}else{
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"Ha ingresado un correo no valido",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}
			}

			if($_POST['usuario_clave_nueva_1']!="" || $_POST['usuario_clave_nueva_2']!=""){
				if($_POST['usuario_clave_nueva_1']!=$_POST['usuario_clave_nueva_2']){
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"Las nuevas claves ingresadas no coinciden",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}else{
					if(mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$_POST['usuario_clave_nueva_1']) || mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$_POST['usuario_clave_nueva_2'])){
						$alerta=[
							"Alerta"=>"simple",
							"Titulo"=>"Ocurrió un error inesperado",
							"Texto"=>"Las nuevas claves no coinciden con el formato solicitado",
							"Tipo"=>"error"
						];
						echo json_encode($alerta);
						exit();
					}
					$clave=mainModel::encryption($_POST['usuario_clave_nueva_1']);
				}
			}else{
				$clave=$campos['usuario_clave'];
			}


			if($tipo_cuenta=="Propia"){
				$check_cuenta=mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM usuario WHERE usuario_usuario='$admin_usuario' AND usuario_clave='$admin_clave' AND usuario_id='$id'");
			}else{
				session_start(['name'=>'SPM']);
				if($_SESSION['privilegio_spm']!=1){
					$alerta=[
						"Alerta"=>"simple",
						"Titulo"=>"Ocurrió un error inesperado",
						"Texto"=>"No tienes los permisos necesarios para realizar esta operacion",
						"Tipo"=>"error"
					];
					echo json_encode($alerta);
					exit();
				}
				$check_cuenta=mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM usuario WHERE usuario_usuario='$admin_usuario' AND usuario_clave='$admin_clave'");
			}

			if($check_cuenta->rowCount()<=0){
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"Nombre y clave de administrador no validos",
					"Tipo"=>"error"
				];
				echo json_encode($alerta);
				exit();
			}

			$datos_usuario_up=[
				"DNI"=>$dni,
				"Nombre"=>$nombre,
				"Apellido"=>$apellido,
				"Telefono"=>$telefono,
				"Direccion"=>$direccion,
				"Email"=>$email,
				"Usuario"=>$usuario,
				"Clave"=>$clave,
				"Estado"=>$estado,
				"Privilegio"=>$privilegio,
				"ID"=>$id
			];

			if(usuarioModelo::actualizar_usuario_modelo($datos_usuario_up)){
				$alerta=[
					"Alerta"=>"recargar",
					"Titulo"=>"Datos actualizados",
					"Texto"=>"Los datos han sido actualizados con exito",
					"Tipo"=>"success"
				];
			}else{
				$alerta=[
					"Alerta"=>"simple",
					"Titulo"=>"Ocurrió un error inesperado",
					"Texto"=>"No hemos podido actualizar los datos, por favor intente nuevamente",
					"Tipo"=>"error"
				];
			}
			echo json_encode($alerta);
		} /* Fin controlador */
        
        
        
        
        
	}