<?php
    
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//ANTES ESTE ERA recuperarControlador, Le cambie el nommbre
//esta vista maneja todo lo relacionado con las solicitudes de nuevo ingreso
//su ajax solo maneja la peticion de inscribir al estudiante, la de crear la solicitud esta en loginAjax
//este controlador en 2025 si recuerdo bien manejaba el bloqueo de intentos de acceso fallidos, cambios de clave, creacion de cuentas, y funciones de solicitudes de nuevo ingreso

//enviar_codigo_por_correo
	if($peticionAjax){
        require_once "../modelos/solicitudModelo.php";
	}else{
        require_once "./modelos/solicitudModelo.php";

	}

//esta solamente verificara lo de la vista de solicitar nuevo ingreso, la lista y la vista de chequear la peticion

	class solicitudControlador extends solicitudModelo{


        
    public function registrar_solicitud_inscripcion_controlador(): string {
        header('Content-Type: application/json');
        
        try {
            
            //validaciones de seguridad
                // Validar método
                if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                    return json_encode(mainModel::generarAlertaSimple('Método no permitido', 'error', 'simple'));
                }

                //validar content type 
                $contentType = $_SERVER['CONTENT_TYPE'] ?? '';
                if (stripos($contentType, 'multipart/form-data') === false) {
                    http_response_code(415);
                    return json_encode(mainModel::generarAlertaSimple('Formato no soportado', 'error', 'simple'));
                }

            	//honeypot y timetrap son campos q en teoria rellenan bots y no los usuarios, asique son una trampa
                // Honeypot
                if (!empty($_POST['website'])) {
                    // Es un bot. Fingimos éxito para no darle pistas.
                    return json_encode(mainModel::generarAlertaSimple('Solicitud enviada correctamente. Pronto nos pondremos en contacto...','success', 'recargar'));
                }

                // Time-trap: si tardó menos de 3 segundos, es bot
                $formStarted = (int) ($_POST['form_started_at'] ?? 0);
                if ($formStarted === 0 || (time() - $formStarted) < 3) {
                    return json_encode(mainModel::generarAlertaSimple('Solicitud enviada correctamente. Pronto nos pondremos en contacto..','success', 'recargar'));
                }

            
            // 2. Recoger y limpiar datos
            $nombre         = mainModel::limpiar_cadena($_POST['nombre'] ?? '');
            $apellido       = mainModel::limpiar_cadena($_POST['apellido'] ?? '');
            $correo         = mainModel::limpiar_cadena($_POST['correo'] ?? '');
            $idPeriodCourse = (int) ($_POST['id_curso'] ?? 0); //id_curso porque asi esta en el select pero  en realidad es id period course
            $fechaNac       = mainModel::limpiar_cadena($_POST['fecha_nacimiento'] ?? '');
            $cedula         = mainModel::limpiar_cadena($_POST['cedula'] ?? '');
            $telefono       = mainModel::limpiar_cadena($_POST['telefono'] ?? '');
            $wsp            = mainModel::limpiar_cadena($_POST['wsp'] ?? '');
            $direccion      = mainModel::limpiar_cadena($_POST['direccion'] ?? '');
            $lugarN         = mainModel::limpiar_cadena($_POST['lugar_n'] ?? '');
            $nivelEst       = mainModel::limpiar_cadena($_POST['nivel_est'] ?? '');
            $oficio         = mainModel::limpiar_cadena($_POST['oficio'] ?? '');
            $trabajo        = isset($_POST['trabajo']) ? true : false;
            $monto          = (int) ($_POST['monto'] ?? 0);
            $fechaPago      = mainModel::limpiar_cadena($_POST['fecha_pago'] ?? '');
            $metodoPago     = mainModel::limpiar_cadena($_POST['metodo_pago'] ?? '');
            $referencia     = mainModel::limpiar_cadena($_POST['referencia'] ?? '');
            $clave1         = trim($_POST['clave'] ?? '');
            $clave2         = trim($_POST['confirmar_clave'] ?? '');

            //var_dump($direccion);die();

              /*   echo '<pre>';
                var_dump([
                    'nombre'         => $nombre,
                    'apellido'       => $apellido,
                    'correo'         => $correo,
                    'idPeriodCourse' => $idPeriodCourse,
                    'fechaNac'       => $fechaNac,
                    'cedula'         => $cedula,
                    'telefono'       => $telefono,
                    'wsp'            => $wsp,
                    'direccion'      => $direccion,
                    'lugarN'         => $lugarN,
                    'nivelEst'       => $nivelEst,
                    'oficio'         => $oficio,
                    'monto'          => $monto,
                    'fechaPago'      => $fechaPago,
                    'metodoPago'     => $metodoPago,
                ]);
                echo '</pre>';
                die();*/
            
            // 3. Validaciones básicas
            if ($nombre === '' || $apellido === '' || $correo === '' || $idPeriodCourse <= 0
                || $fechaNac === '' || $cedula === '' || $telefono === '' || $wsp === ''
                || $direccion === '' || $lugarN === '' || $nivelEst === '' || $oficio === ''
                || $monto <= 0 || $fechaPago === '' || $metodoPago === '') {
                
                return json_encode(mainModel::generarAlertaSimple('Complete todos los campos obligatorios', 'error', 'simple'));
            }

            if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
                return json_encode(mainModel::generarAlertaSimple('El correo no es válido', 'error', 'simple'));
            }

            if ($clave1 !== $clave2) {
                return json_encode(mainModel::generarAlertaSimple('Las claves no coinciden', 'error', 'simple'));
            }
            
            $validandoclave=mainModel::validar_clave_segura($clave1);
            if ($validandoclave !=null) {return json_encode(mainModel::generarAlertaSimple($validandoclave, 'error', 'simple'));}
            

            if (!isset($_FILES['captura_pago']) || $_FILES['captura_pago']['error'] !== UPLOAD_ERR_OK) {
                return json_encode(mainModel::generarAlertaSimple('Debe subir la captura del pago', 'error', 'simple'));
            }

            $archivo        = $_FILES['captura_pago'];
            $nombreOriginal = basename($archivo['name']);
            $extension      = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));

            if (!in_array($extension, ['jpg', 'jpeg', 'png', 'webp'])) {
                return json_encode(mainModel::generarAlertaSimple('La imagen debe ser JPG, PNG o WEBP', 'error', 'simple'));
            }

            if ($archivo['size'] > 5 * 1024 * 1024) {
                return json_encode(mainModel::generarAlertaSimple('La imagen no debe exceder 5 MB', 'error', 'simple'));
            }

            // Guardar imagen en comprobantes/pagos/
            $nombreFinal    = uniqid('pago_') . '.' . $extension;
            $rutaRelativa   = 'comprobantes/pagos/' . $nombreFinal;
            $carpetaDestino = __DIR__ . '/../comprobantes/pagos/';

            if (!file_exists($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }

            $rutaAbsoluta = $carpetaDestino . $nombreFinal;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
                return json_encode(mainModel::generarAlertaSimple('No se pudo guardar la imagen', 'error', 'simple'));
            }
            
       
            //Cifrar la clave para guardarla temporalmente en profile.temp_password
            $tempPassword = mainModel::encryption($clave1);           
            
            
            $correoRaw = $correo;
            $telefonoRaw = $telefono;
            $wspRaw = $wsp;
            $direccionRaw = $direccion;
            $cedularaw=$cedula;
            
            $cedula    = mainModel::encryption($cedularaw);
            $correo    = mainModel::encryption($correoRaw);
            $telefono  = mainModel::encryption($telefonoRaw);
            $wsp       = mainModel::encryption($wspRaw);
            $direccion = mainModel::encryption($direccionRaw);            

            // Registrar todo en la BD vía RPC (sin crear auth.user todavía)
            $rpc = solicitudModelo::registrar_solicitud_inscripcion_supabase(
                $correo, $cedula, $nombre, $apellido,
                $fechaNac, $telefono, $wsp, $direccion, $lugarN,
                $nivelEst, $trabajo, $oficio,
                $idPeriodCourse, $monto,
                $metodoPago, $rutaRelativa,
                $tempPassword,$referencia
            );

            $rpcData = $rpc['data'] ?? [];
            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                // Rollback: borrar la imagen subida
                @unlink($rutaAbsoluta);
                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo registrar la solicitud';
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }

            return json_encode(mainModel::generarAlertaSimple(
                'Solicitud enviada correctamente. Pronto nos pondremos en contacto.',
                'success', 'recargar'
            ));

        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }    
        
        
    
        
        
        
        
        
        
        
        
    public function paginador_solicitud_inscripcion_controlador($pagina, $registros, $vista) {
        $pagina    = max(1, (int) $pagina);
        $registros = max(1, (int) $registros);
        $token     = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

        // Recoger y validar filtros
        $estadoRaw = $_GET['estado'] ?? '';
        $estado = ($estadoRaw === '' || $estadoRaw === 'all')
            ? null
            : (int) $estadoRaw;
        if ($estado !== null && !in_array($estado, [1, 2, 3], true)) {
            $estado = null;
        }

        $validarFecha = function ($f) {
            $f = trim($f);
            if ($f === '') return null;
            $d = DateTime::createFromFormat('Y-m-d', $f);
            return ($d && $d->format('Y-m-d') === $f) ? $f : null;
        };

        $fechaDesde = $validarFecha($_GET['fecha_desde'] ?? '');
        $fechaHasta = $validarFecha($_GET['fecha_hasta'] ?? '');

        // Si ambas están y desde > hasta, invertirlas
        if ($fechaDesde && $fechaHasta && $fechaDesde > $fechaHasta) {
            [$fechaDesde, $fechaHasta] = [$fechaHasta, $fechaDesde];
        }

        $filtros = [
            'busqueda'    => trim($_GET['busqueda'] ?? '') ?: null,
            'orden'       => $_GET['orden'] ?? 'reciente',
            'estado'      => $estado,
            'fecha_desde' => $fechaDesde,
            'fecha_hasta' => $fechaHasta,
        ];

        $resultado = solicitudModelo::listar_solicitudes_nuevo_ingreso_supabase(
            $filtros,
            $token,
            $registros,
            ($pagina - 1) * $registros
        );

        if (!$resultado['ok']) {
            return '<div class="alert alert-danger">No se pudo cargar la lista de solicitudes.</div>';
        }

        $items   = $resultado['data']['data'] ?? [];
        $total   = (int) ($resultado['data']['total'] ?? 0);
        $paginas = max(1, (int) ceil($total / $registros));

        $cargo         = (int) ($_SESSION['cargo_spm'] ?? 0);
        $carpeta       = 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        $tabla  = '<div class="table-responsive"><table class="table table-bordered table-sm table-striped">';
        $tabla .= '<thead class="text-center bg-dark text-white"><tr>';
        $tabla .= '<th>ID</th><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Correo</th>';
        $tabla .= '<th>F. Nacimiento</th><th>Curso</th><th>Periodo</th><th>F. Emisión</th>';
        $tabla .= '<th>Estado</th><th>Captura</th><th>Ir</th>';//<th>Eliminar</th>
        $tabla .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;

            foreach ($items as $row) {
                $ci1= mainModel::decryption($row['student_ci'] ?? '');
                $regIdEnc = mainModel::encryption((int) $row['registration_id']);
                $nombre   = htmlspecialchars(trim(($row['student_name'] ?? '') . ' ' . ($row['student_last_name'] ?? '')), ENT_QUOTES, 'UTF-8');
                $ci=$ci1;

                $telefono = htmlspecialchars(mainModel::decryption($row['student_phone'] ?? ''), ENT_QUOTES, 'UTF-8');
                $correo   = htmlspecialchars(mainModel::decryption($row['student_email'] ?? ''), ENT_QUOTES, 'UTF-8');

                $fNac    = htmlspecialchars($row['student_birthday'] ?? '', ENT_QUOTES, 'UTF-8');
                $curso   = htmlspecialchars(($row['course_name'] ?? 'Sin curso') . ' (' . ($row['course_type'] ?? '') . ')', ENT_QUOTES, 'UTF-8');
                $periodo = htmlspecialchars(($row['period_name'] ?? 'Sin periodo') . ' - ' . ($row['period_state'] ?? ''), ENT_QUOTES, 'UTF-8');

                // ── Fecha de emisión (profile.created_at) ──
                $fEmisionRaw = $row['profile_created_at'] ?? '';
                $fEmision    = '—';
                if ($fEmisionRaw) {
                    try {
                        $dt = new DateTime($fEmisionRaw);
                        $fEmision = $dt->format('d/m/Y H:i');
                    } catch (Exception $e) {
                        $fEmision = $fEmisionRaw;
                    }
                }

                $estado = (int) ($row['registration_state'] ?? 0);

                $estadoBadge = match ($estado) {
                    1       => '<span class="badge bg-success" style="color:white;">Aceptada</span>',
                    2       => '<span class="badge bg-warning" style="color:black;">Pendiente</span>',
                    3       => '<span class="badge bg-danger"  style="color:white;">Rechazada</span>',
                    default => '<span class="badge bg-secondary" style="color:white;">' . $estado . '</span>',
                };

                // ── Captura ──
                $imgRoute    = $row['img_route'] ?? '';
                $botonImagen = $imgRoute
                    ? '<button type="button" class="btn btn-info btn-sm ver-captura" data-src="' . SERVERURL . htmlspecialchars($imgRoute, ENT_QUOTES, 'UTF-8') . '">
                           <i class="fas fa-eye"></i>
                       </button>'
                    : '<span class="text-muted">—</span>';

                // ── Fila ──
                $tabla .= '<tr class="text-center">';
                $tabla .= '<td>' . (int) $row['registration_id'] . '</td>';
                $tabla .= '<td>' . $nombre . '</td>';
                $tabla .= '<td>' . $ci . '</td>';
                $tabla .= '<td>' . $telefono . '</td>';
                $tabla .= '<td>' . $correo . '</td>';
                $tabla .= '<td>' . $fNac . '</td>';
                $tabla .= '<td>' . $curso . '</td>';
                $tabla .= '<td>' . $periodo . '</td>';
                $tabla .= '<td>' . htmlspecialchars($fEmision) . '</td>';
                $tabla .= '<td>' . $estadoBadge . '</td>';
                $tabla .= '<td>' . $botonImagen . '</td>';
                $tabla .= '<td>
                    <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/solicitud-nuevo-ingreso-check&solicitud_id=' . $regIdEnc . '" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </td>';
               /* $tabla .= '<td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/solicitudAjax.php" method="POST" data-form="delete2" data-entity="solicitud_inscripcion" autocomplete="off">
                        <input type="hidden" name="solicitud_id_delete" value="' . $regIdEnc . '">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>';*/
                $tabla .= '</tr>';
            }
            $regFinal = $regInicio + count($items) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="13">No hay solicitudes registradas.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($total > 0) {
            $tabla .= '<p class="text-right">Mostrando solicitud ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';

            $paramsGet = array_filter([
                'busqueda'    => $_GET['busqueda']    ?? '',
                'orden'       => $_GET['orden']       ?? '',
                'estado'      => $_GET['estado']      ?? '',
                'fecha_desde' => $_GET['fecha_desde'] ?? '',
                'fecha_hasta' => $_GET['fecha_hasta'] ?? '',
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

        // 5. Modal de captura
        $tabla .= '
        <div id="modalCaptura" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
          <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff;">
            <img id="imgCaptura" src="" class="img-thumbnail" style="max-width:400px; width:80%; height:auto; display:block; margin:auto; border:3px solid white;">
            <button onclick="document.getElementById(\'modalCaptura\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
              <i class="fas fa-times"></i>
            </button>
          </div>
        </div>
        <script>
        document.querySelectorAll(".ver-captura").forEach(btn => {
          btn.addEventListener("click", () => {
            const modal = document.getElementById("modalCaptura");
            modal.querySelector("#imgCaptura").src = btn.dataset.src;
            modal.style.display = "block";
          });
        });
        </script>';

        return $tabla;
    }
        
        
        


    public function datos_solicitud_check_controlador(string $idEnc): array {
        $id = (int) mainModel::decryption($idEnc);
        if ($id <= 0) return ['ok' => false, 'message' => 'ID inválido'];

        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $rpc = solicitudModelo::obtener_solicitud_inscripcion_supabase($id, $token);
        //var_dump($rpc);die();
        if (!$rpc['ok'] || empty($rpc['data']['ok'])) {
            return ['ok' => false, 'message' => $rpc['data']['error'] ?? 'Solicitud no encontrada'];
        }

        $data = $rpc['data'];
        
        $data['student']['ci']             = mainModel::decryption($data['student']['ci'] ?? '');
        $data['student']['email']          = mainModel::decryption($data['student']['email'] ?? '');
        $data['student']['phone']          = mainModel::decryption($data['student']['phone'] ?? '');
        $data['student']['whatsapp_phone'] = mainModel::decryption($data['student']['whatsapp_phone'] ?? '');
        $data['student']['address']        = mainModel::decryption($data['student']['address'] ?? '');

        return ['ok' => true, 'data' => $data];
    }
        
        
        
        
        
        
        
        
        
        
        
        
// ACEPTAR SOLICITUD DE NUEVO ingreso
    public function aceptar_solicitud_controlador(): string {
        header('Content-Type: application/json');

        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(['ok' => false, 'mensaje' => 'Sesión no disponible']);
            }

            $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
            if (!in_array($cargo, [1, 2], true)) {
                return json_encode(['ok' => false, 'mensaje' => 'No tiene permisos para esta acción']);
            }

            $solIdEnc = $_POST['solicitud_id'] ?? '';
            $sectionId = isset($_POST['section_id']) && $_POST['section_id'] !== ''
                            ? (int) $_POST['section_id']
                            : null;
            $newSectionName = isset($_POST['new_section_name']) && $_POST['new_section_name'] !== ''
                            ? strtoupper(trim($_POST['new_section_name']))
                            : null;
            $teacherId = isset($_POST['teacher_id']) && $_POST['teacher_id'] !== ''
                            ? (int) $_POST['teacher_id']
                            : null;

            $sectionDay = isset($_POST['section_day']) && $_POST['section_day'] !== ''
                ? trim($_POST['section_day'])
                : null;

            // Validación: si va a crear nueva sección, el día es obligatorio
            if ($newSectionName !== null && $sectionDay === null) {
                return json_encode(mainModel::generarAlertaSimple(
                    'Debe seleccionar un día para la nueva sección',
                    'error',
                    'simple'
                ));
            }
            
            
            $registrationId = (int) mainModel::decryption($solIdEnc);
            
            if ($registrationId <= 0) {
                return json_encode(['ok' => false, 'mensaje' => 'ID de solicitud inválido']);
            }
            if ($sectionId === null && $newSectionName === null) {
                return json_encode(['ok' => false, 'mensaje' => 'Debe seleccionar o crear una sección']);
            }

            // Obtener datos del estudiante (email, temp_password, monto)
            $datos = solicitudModelo::obtener_datos_aceptacion_supabase($registrationId, $token);
            if (!$datos['ok'] || empty($datos['data'][0])) {
                return json_encode(['ok' => false, 'mensaje' => 'Solicitud no encontrada o ya procesada']);
            }

            $fila = $datos['data'][0];
            $email          = $fila['student']['email'] ?? '';
            $tempPassword   = $fila['student']['profile']['temp_password'] ?? '';
            $montoPagado    = (int) ($fila['transaction_detail'][0]['amount'] ?? 0);
            $periodCourseId = (int) ($fila['period_course_id'] ?? 0);

            if ($email === '' || $tempPassword === '') {
                return json_encode(['ok' => false, 'mensaje' => 'Datos de cuenta incompletos']);
            }


            //aqui habia un condicional para ver si el monto enviado coincidia con el pago de inscripcion, pero eso ya lo hace a la rpc
            
            // Desencriptar la clave temporal
            $claveDesencriptada = mainModel::decryption($tempPassword);
            if (!$claveDesencriptada) {
                return json_encode([
                    'ok' => false,
                    'mensaje' => 'No se pudo desencriptar la clave del estudiante',
                ]);
            }

            // Crear el auth.user
            
            //desencriptamos
            $emaildesencriptado = mainModel::decryption($email);

            
            $crearAuth = SupabaseApi::adminCreateUser($emaildesencriptado, $claveDesencriptada,false);
            if (!$crearAuth['ok']) {
                    $msg = $crearAuth['message'] ?? 'Error desconocido';

                    // Traducir errores comunes de Supabase
                    if (stripos($msg, 'already') !== false || stripos($msg, 'email_exists') !== false) {
                        $msg = 'Ese correo ya tiene una cuenta creada. Es posible que otra solicitud con el mismo email fue aceptada antes.';
                    } elseif (stripos($msg, 'rate limit') !== false) {
                        $msg = 'Demasiados intentos de crear cuentas. Espera unos minutos e intenta de nuevo.';
                    } elseif (stripos($msg, 'invalid') !== false && stripos($msg, 'email') !== false) {
                        $msg = 'El correo del estudiante no tiene un formato válido.';
                    }

                    return json_encode(mainModel::generarAlertaSimple(
                        'No se pudo crear la cuenta: ' . $msg,
                        'error',
                        'simple'
                    ));
                
            }
            $newUserId = $crearAuth['user_id'];
            

            // Llamar a la RPC de aceptación
            //$rpc=NULL;
            $rpc = solicitudModelo::aceptar_solicitud_nuevo_ingreso_supabase(
                $registrationId,
                $newUserId,
                $sectionId,
                $newSectionName,
                $teacherId,
                    $sectionDay,  
                $token
            );

            $rpcData = $rpc['data'] ?? [];
            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                // Rollback: eliminar el auth.user recién creado
                SupabaseApi::adminDeleteUser($newUserId);

                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo aceptar la solicitud';
                return json_encode(['ok' => false, 'mensaje' => $mensaje]);
            }

            // Respuesta exitosa
            return json_encode([
                'Alerta' => 'recargar',
                'Titulo' => 'Solicitud aceptada',
                'Texto'  => 'El estudiante ha sido inscrito correctamente.',
                'Tipo'   => 'success',
            ]);

        } catch (Exception $e) {
            return json_encode([
                'ok' => false,
                'mensaje' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }


        
        
    public function rechazar_solicitud_controlador(): string {
        header('Content-Type: application/json');

        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(['ok' => false, 'mensaje' => 'Sesión no disponible']);
            }

            $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
            if (!in_array($cargo, [1, 2], true)) {
                return json_encode(['ok' => false, 'mensaje' => 'No tiene permisos para esta acción']);
            }

            $solIdEnc = $_POST['solicitud_id_rechazar'] ?? '';
            $registrationId = (int) mainModel::decryption($solIdEnc);

            if ($registrationId <= 0) {
                return json_encode(['ok' => false, 'mensaje' => 'ID de solicitud inválido']);
            }

            $rpc = solicitudModelo::rechazar_solicitud_nuevo_ingreso_supabase(
                $registrationId,
                $token
            );

            $rpcData = $rpc['data'] ?? [];
            if (!($rpc['ok'] ?? false) || empty($rpcData['ok'])) {
                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo rechazar la solicitud';
                return json_encode(['ok' => false, 'mensaje' => $mensaje]);
            }

            return json_encode([
                'Alerta' => 'recargar',
                'Titulo' => 'Solicitud rechazada',
                'Texto'  => 'La solicitud fue rechazada correctamente.',
                'Tipo'   => 'warning',
            ]);

        } catch (Exception $e) {
            return json_encode([
                'ok' => false,
                'mensaje' => 'Error: ' . $e->getMessage(),
            ]);
        }
    }
        
        

    }
