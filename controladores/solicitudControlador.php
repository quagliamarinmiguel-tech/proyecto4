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
		//require_once __DIR__ . '/../modelos/login_mdo.php';
        require_once "../modelos/solicitudModelo.php";
	}else{
		//require_once __DIR__ . '/../modelos/login_mdo.php';
        require_once "./modelos/solicitudModelo.php";

	}

//esta solamente verificara lo de la vista de solicitar nuevo ingreso, la lista y la vista de chequear la peticion

//	class recuperarControlador extends recuperarModelo{
	class solicitudControlador extends solicitudModelo{


        
    public function registrar_solicitud_inscripcion_controlador(): string {
        header('Content-Type: application/json');

        
       /*
            echo '
            <script>
                Swal.fire({
                    title: "Método no permitido",
                    text: "maincra.",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
            return;*/
        
        try {
            // 1. Validar método
            if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
                return json_encode(mainModel::generarAlertaSimple('Método no permitido', 'error', 'simple'));
            }

            // 2. Recoger y limpiar datos
            $nombre         = mainModel::limpiar_cadena($_POST['nombre'] ?? '');
            $apellido       = mainModel::limpiar_cadena($_POST['apellido'] ?? '');
            $correo         = mainModel::limpiar_cadena($_POST['correo'] ?? '');
            // $idPeriodCourse = (int) ($_POST['period_course_id'] ?? 0);
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

                // === DEBUG ===
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
            if (strlen($clave1) < 7) {
                return json_encode(mainModel::generarAlertaSimple('La clave debe tener al menos 7 caracteres', 'error', 'simple'));
            }

            // 4. Validar imagen del pago
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

            // 5. Guardar imagen en comprobantes/pagos/
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
            
       
            // 6. Cifrar la clave para guardarla temporalmente en profile.temp_password
            $tempPassword = mainModel::encryption($clave1);           
            
            
            $correoRaw = $correo;
            $telefonoRaw = $telefono;
            $wspRaw = $wsp;
            $direccionRaw = $direccion;
            
            $correo    	  = mainModel::encryption($correoRaw);
            $telefono  = mainModel::encryption($telefonoRaw);
            $wsp       = mainModel::encryption($wspRaw);
            $direccion = mainModel::encryption($direccionRaw);
//var_dump($direccion);die();
            
            

            // 7. Registrar todo en la BD vía RPC (sin crear auth.user todavía)
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

        // ============================================
        // 1. Recoger y validar filtros
        // ============================================

        // ── Estado (opcional) ──
        $estadoRaw = $_GET['estado'] ?? '';
        $estado = ($estadoRaw === '' || $estadoRaw === 'all')
            ? null
            : (int) $estadoRaw;
        if ($estado !== null && !in_array($estado, [1, 2, 3], true)) {
            $estado = null;
        }

        // ── Fechas (opcional) ──
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

        // ============================================
        // 2. Consultar al modelo
        // ============================================
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

        $cargo         = (int) ($_SESSION['cargo_spm'] ?? 1);
        $carpeta       = 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        // ============================================
        // 3. Construir la tabla
        // ============================================
        $tabla  = '<div class="table-responsive"><table class="table table-bordered table-sm table-striped">';
        $tabla .= '<thead class="text-center bg-dark text-white"><tr>';
        $tabla .= '<th>ID</th><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Correo</th>';
        $tabla .= '<th>F. Nacimiento</th><th>Curso</th><th>Periodo</th><th>F. Emisión</th>';
        $tabla .= '<th>Estado</th><th>Captura</th><th>Ir</th><th>Eliminar</th>';
        $tabla .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;

            foreach ($items as $row) {
                $regIdEnc = mainModel::encryption((int) $row['registration_id']);
                $nombre   = htmlspecialchars(trim(($row['student_name'] ?? '') . ' ' . ($row['student_last_name'] ?? '')), ENT_QUOTES, 'UTF-8');
                $ci       = htmlspecialchars($row['student_ci'] ?? '', ENT_QUOTES, 'UTF-8');

                // 👇 Descifrar los campos cifrados en BD
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

                // ── Estado ──
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
                $tabla .= '<td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/solicitudAjax.php" method="POST" data-form="delete2" data-entity="solicitud_inscripcion" autocomplete="off">
                        <input type="hidden" name="solicitud_id_delete" value="' . $regIdEnc . '">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>';
                $tabla .= '</tr>';
            }
            $regFinal = $regInicio + count($items) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="13">No hay solicitudes registradas.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // ============================================
        // 4. Paginador (preserva TODOS los filtros)
        // ============================================
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

        // ============================================
        // 5. Modal de captura
        // ============================================
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
        
        
        /* le faltaban filtros
    public function paginador_solicitud_inscripcion_controlador($pagina, $registros, $vista) {
        $pagina    = max(1, (int) $pagina);
        $registros = max(1, (int) $registros);
        $token     = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

                
        $filtros = [
            'busqueda' => trim($_GET['busqueda'] ?? '') ?: null,
            'orden'    => $_GET['orden'] ?? 'reciente',
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

        $cargo = (int) ($_SESSION['cargo_spm'] ?? 1);
        $carpeta = 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        // Tabla
        $tabla  = '<div class="table-responsive"><table class="table table-bordered table-sm table-striped">';
        $tabla .= '<thead class="text-center bg-dark text-white"><tr>';
        $tabla .= '<th>ID</th><th>Nombre</th><th>Cédula</th><th>Teléfono</th><th>Correo</th>';
        $tabla .= '<th>F. Nacimiento</th><th>Curso</th><th>Periodo</th><th>F. Emisión</th>';
        $tabla .= '<th>Estado</th><th>Captura</th><th>Ir</th><th>Eliminar</th>';
        $tabla .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;

            foreach ($items as $row) {
                $regIdEnc  = mainModel::encryption((int) $row['registration_id']);
                $nombre    = htmlspecialchars(trim(($row['student_name'] ?? '') . ' ' . ($row['student_last_name'] ?? '')), ENT_QUOTES, 'UTF-8');
                $ci        = htmlspecialchars($row['student_ci'] ?? '', ENT_QUOTES, 'UTF-8');
                $telefono  = htmlspecialchars($row['student_phone'] ?? '', ENT_QUOTES, 'UTF-8');
                $correo    = htmlspecialchars($row['student_email'] ?? '', ENT_QUOTES, 'UTF-8');
                $fNac      = htmlspecialchars($row['student_birthday'] ?? '', ENT_QUOTES, 'UTF-8');
                $curso     = htmlspecialchars(($row['course_name'] ?? 'Sin curso') . ' (' . ($row['course_type'] ?? '') . ')', ENT_QUOTES, 'UTF-8');
                $periodo   = htmlspecialchars(($row['period_name'] ?? 'Sin periodo') . ' - ' . ($row['period_state'] ?? ''), ENT_QUOTES, 'UTF-8');

                // Formatear la fecha de emisión
                $fEmisionRaw = $row['profile_created_at'] ?? '';
                $fEmision = '—';
                if ($fEmisionRaw) {
                    try {
                        $dt = new DateTime($fEmisionRaw);
                        $fEmision = $dt->format('d/m/Y H:i');
                    } catch (Exception $e) {
                        $fEmision = $fEmisionRaw;
                    }
                }

                $estado    = (int) ($row['registration_state'] ?? 0);

                $estadoBadge = match($estado) {
                    2 => '<span class="badge bg-warning" style="color:black;">Pendiente</span>',
                    1 => '<span class="badge bg-success" style="color:white;">Activo</span>',
                    0 => '<span class="badge bg-secondary" style="color:white;">Inactivo</span>',
                    default => '<span class="badge bg-info" style="color:white;">' . $estado . '</span>',
                };

                $imgRoute = $row['img_route'] ?? '';
                $botonImagen = $imgRoute
                    ? '<button type="button" class="btn btn-info btn-sm ver-captura" data-src="' . SERVERURL . htmlspecialchars($imgRoute, ENT_QUOTES, 'UTF-8') . '">
                           <i class="fas fa-eye"></i>
                       </button>'
                    : '<span class="text-muted">—</span>';

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
                $tabla .= '<td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/solicitudAjax.php" method="POST" data-form="delete2" data-entity="solicitud_inscripcion" autocomplete="off">
                        <input type="hidden" name="solicitud_id_delete" value="' . $regIdEnc . '">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>';
                $tabla .= '</tr>';
            }
            $regFinal = $regInicio + count($items) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="13">No hay solicitudes registradas.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador
        if ($total > 0) {
            $tabla .= '<p class="text-right">Mostrando solicitud ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';

            $paramsGet = array_filter([
                'busqueda' => $_GET['busqueda'] ?? '',
                'orden'    => $_GET['orden'] ?? '',
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

        // Modal de captura
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
    }*/
        //el id que se envia es el de registration id
        
        
        
        


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

        $data['student']['email']          = mainModel::decryption($data['student']['email'] ?? '');
        $data['student']['phone']          = mainModel::decryption($data['student']['phone'] ?? '');
        $data['student']['whatsapp_phone'] = mainModel::decryption($data['student']['whatsapp_phone'] ?? '');
        $data['student']['address']        = mainModel::decryption($data['student']['address'] ?? '');

        return ['ok' => true, 'data' => $data];
    }
        
        
        
        
        
        
        
        
        
        
        
        
// 2. ACEPTAR SOLICITUD DE NUEVO ingreso
    public function aceptar_solicitud_controlador(): string {
        header('Content-Type: application/json');

        try {
            // 1. Validar sesión
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(['ok' => false, 'mensaje' => 'Sesión no disponible']);
            }

            $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
            if (!in_array($cargo, [1, 3], true)) {
                return json_encode(['ok' => false, 'mensaje' => 'No tiene permisos para esta acción']);
            }

            // 2. Recoger datos
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
//var_dump($solIdEnc,$sectionId,$teacherId,$sectionDay);die();
            
            if ($registrationId <= 0) {
                return json_encode(['ok' => false, 'mensaje' => 'ID de solicitud inválido']);
            }
            if ($sectionId === null && $newSectionName === null) {
                return json_encode(['ok' => false, 'mensaje' => 'Debe seleccionar o crear una sección']);
            }

            // 3. Obtener datos del estudiante (email, temp_password, monto)
            $datos = solicitudModelo::obtener_datos_aceptacion_supabase($registrationId, $token);
            //var_dump($datos);die();
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

            /* se archiva para reducir las consultas en el controlador sabiendo q la rpc ya lo valida
            // 4. Obtener precio esperado de inscripción
            // ----------------------------------------
            $costos = solicitudModelo::obtener_precio_inscripcion_supabase($periodCourseId, $token);
            //var_dump($costos);die();
            $precioEsperado = 0;

            if ($costos['ok'] && !empty($costos['data'][0])) {
                foreach (($costos['data'][0]['period']['period_costs'] ?? []) as $c) {
                    if (($c['cost_description'] ?? '') === 'pago_inscripcion') {
                        $precioEsperado = (int) $c['price'];
                        break;
                    }
                }
            }*/


            //aqui habia un condicional para ver si el monto enviado coincidia con el pago de inscripcion, pero eso ya lo hace a la rpc
            
            // 5. Desencriptar la clave temporal
            $claveDesencriptada = mainModel::decryption($tempPassword);
            if (!$claveDesencriptada) {
                return json_encode([
                    'ok' => false,
                    'mensaje' => 'No se pudo desencriptar la clave del estudiante',
                ]);
            }

            // 6. Crear el auth.user
            
            //echo 'hola aasta aqui llegas';die();
            //desencriptamos
            //$email2="emailprueba@prueba.com";
            $emaildesencriptado = mainModel::decryption($email);

            
            $crearAuth = SupabaseApi::adminCreateUser($emaildesencriptado, $claveDesencriptada, false);
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
                
                
                
                
               // return json_encode([
               //     'ok' => false,
               //     'mensaje' => 'No se pudo crear la cuenta: ' . ($crearAuth['message'] ?? 'Error'),
               // ]);
            }
            
            // Forzar el envío del correo de confirmación
            SupabaseApi::resendConfirmationEmail($emaildesencriptado);
            
            $newUserId = $crearAuth['user_id'];
            

            // ----------------------------------------
            // 7. Llamar a la RPC de aceptación
            // ----------------------------------------
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

                $mensaje = $rpcData['error'] ?? $rpc['message'] ?? 'No se pudo aceptar la solicitudsupongoqakillegas';
                return json_encode(['ok' => false, 'mensaje' => $mensaje]);
            }

            // ----------------------------------------
            // 8. Respuesta exitosa
            // ----------------------------------------
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

    // ============================================
    // 3. RECHAZAR SOLICITUD DE INSCRIPCIÓN
    // ============================================
    public function rechazar_solicitud_controlador(): string {
        header('Content-Type: application/json');

        try {
            // ----------------------------------------
            // 1. Validar sesión
            // ----------------------------------------
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(['ok' => false, 'mensaje' => 'Sesión no disponible']);
            }

            $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
            if (!in_array($cargo, [1, 3], true)) {
                return json_encode(['ok' => false, 'mensaje' => 'No tiene permisos para esta acción']);
            }

            // ----------------------------------------
            // 2. Recoger datos
            // ----------------------------------------
            $solIdEnc = $_POST['solicitud_id_rechazar'] ?? '';
            $registrationId = (int) mainModel::decryption($solIdEnc);

            if ($registrationId <= 0) {
                return json_encode(['ok' => false, 'mensaje' => 'ID de solicitud inválido']);
            }

            // ----------------------------------------
            // 3. Llamar a la RPC de rechazo
            // ----------------------------------------
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
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        

        
public function reprobar_solicitud_controlador() {
    $id = mainModel::decryption($_POST['id_solicitud_reprobar']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia
    $check = mainModel::ejecutar_consulta_simple("SELECT id_solicitud FROM solicitud_inscripcion WHERE id_solicitud = '$id'");
    if ($check->rowCount() <= 0) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Solicitud no encontrada",
            "Texto" => "La solicitud no existe o el ID es inválido",
            "Tipo" => "error"
        ]);
    }

    // Determinar carpeta según cargo
    $cargo = $_SESSION['cargo_spm'] ?? 1;
    $carpetasPorCargo = [
        1 => "admin",
        2 => "Control de estudio",
        3 => "administrador de pagos",
        4 => "Supervisor de cursos",
        5 => "Estudiante"
    ];
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";

    $pdo = mainModel::conectar();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE solicitud_inscripcion SET estado_solicitud = 'Rechazada' WHERE id_solicitud = :id");
        $stmt->execute([':id' => $id]);

        $pdo->commit();

        return json_encode([
            "Alerta" => "redireccionar",
            "Titulo" => "Solicitud reprobada",
            "Texto" => "La solicitud ha sido marcada como reprobada exitosamente",
            "Tipo" => "success",
            "URL" => SERVERURL . "index.php?views=" . $carpeta . "/solicitud-list"
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al reprobar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}
        
        
        
        
public function eliminar_solicitud_controlador() {
    $id = mainModel::decryption($_POST['solicitud_id_delete']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia
    $check = mainModel::ejecutar_consulta_simple("SELECT captura_pago FROM solicitud_inscripcion WHERE id_solicitud = '$id'");
    if ($check->rowCount() <= 0) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Solicitud no encontrada",
            "Texto" => "La solicitud que intenta eliminar no existe o el ID es inválido",
            "Tipo" => "error"
        ]);
    }

    $datos = $check->fetch(PDO::FETCH_ASSOC);
    $rutaRelativa = $datos['captura_pago'] ?? '';
    $nombreArchivo = basename($rutaRelativa); // Extrae solo el nombre del archivo

    // Ruta física del archivo
    $rutaFisica = "../vistas/cm/pay/" . $nombreArchivo;

    $pdo = mainModel::conectar();

    try {
        $pdo->beginTransaction();

        // Eliminar archivo si existe
        if (file_exists($rutaFisica)) {
            unlink($rutaFisica);
        }

        // Eliminar registro
        $stmt = $pdo->prepare("DELETE FROM solicitud_inscripcion WHERE id_solicitud = :id");
        $stmt->execute([':id' => $id]);

        $pdo->commit();

        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Solicitud eliminada",
            "Texto" => "La solicitud y su comprobante han sido eliminados exitosamente",
            "Tipo" => "success"
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al eliminar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        /*
        // esta es la q solo permite secciones del cv enviado 71125
public function aceptar_solicitud_controlador071125() {
    // Verificar permisos
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 3) {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Acceso Denegado",
            "Texto" => "No tienes permisos para realizar esta acción",
            "Tipo" => "error"
        ];
        return json_encode($alerta);
    }

    $pdo = mainModel::conectar();

    try {
        // Obtener y limpiar datos del formulario
        $nombre_estudiante = mainModel::limpiar_cadena($_POST['nombre_estudiante']);
        $apellido_estudiante = mainModel::limpiar_cadena($_POST['apellido_estudiante']);
        $cedula_estudiante = mainModel::limpiar_cadena($_POST['cedula_estudiante']);
        $telefono_estudiante = mainModel::limpiar_cadena($_POST['telefono_estudiante']);
        $correo_estudiante = mainModel::limpiar_cadena($_POST['correo_Estudiante']);
        $fecha_nacimiento = mainModel::limpiar_cadena($_POST['fecha_nacimiento']);
        $monto = mainModel::limpiar_cadena($_POST['monto']);
        $fecha_pago = mainModel::limpiar_cadena($_POST['fecha_pago']);
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago']);
        $captura_pago = mainModel::limpiar_cadena($_POST['captura_pago']);
        $clave_estudiante = mainModel::limpiar_cadena($_POST['clave_estudiante']);
        $solicitud_id = mainModel::decryption($_POST['solicitud_id_check']);

        $id_curso_vigente_fk = mainModel::limpiar_cadena($_POST['id_curso_vigente_fk']);
        $seccion_id = mainModel::limpiar_cadena($_POST['seccion_id'] ?? '');

        // Validar campos obligatorios
        if ($nombre_estudiante == "" || $apellido_estudiante == "" || $cedula_estudiante == "" || 
            $telefono_estudiante == "" || $correo_estudiante == "" || $fecha_nacimiento == "" || 
            $monto == "" || $fecha_pago == "" || $metodo_pago == "" || $captura_pago == "" || 
            $clave_estudiante == "" || $id_curso_vigente_fk == "" || $solicitud_id == "" || 
            empty($seccion_id)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto"  => "No has llenado todos los campos que son obligatorios",
                "Tipo"   => "error"
            ];
            return json_encode($alerta);
        }

        // MANEJO DE LA IMAGEN (COMPLETADO)
        $nombreImagen = basename($captura_pago);
        $rutaOrigen = __DIR__ . "/../vistas/cm/pay/" . $nombreImagen;
        $rutaDestino = __DIR__ . "/../comprobantes/" . $nombreImagen;
        
        if (!file_exists($rutaOrigen)) {
            throw new Exception("La imagen de pago no se encontró en el servidor");
        }
        
        if (!copy($rutaOrigen, $rutaDestino)) {
            throw new Exception("No se pudo copiar la imagen de pago a la carpeta de comprobantes");
        }
        
        if (!unlink($rutaOrigen)) {
            throw new Exception("No se pudo eliminar la imagen temporal de pago");
        }

        // Ahora sí, iniciar la transacción
        $pdo->beginTransaction();

        // PASO 1: Verificar si el estudiante ya existe
        $consultaEstudiante = $pdo->prepare("
            SELECT id_estudiante FROM estudiante 
            WHERE ci_estudiante = :cedula 
            AND estado_estudiante = 1
        ");
        $consultaEstudiante->execute([':cedula' => $cedula_estudiante]);
        
        if ($consultaEstudiante->rowCount() > 0) {
            // Estudiante existe, obtener su ID
            $id_estudiante = $consultaEstudiante->fetch()['id_estudiante'];
            
            // Actualizar datos del estudiante existente si es necesario
            $updateEstudiante = $pdo->prepare("
                UPDATE estudiante SET 
                nombre = :nombre,
                apellido = :apellido,
                telefono_estudiante = :telefono,
                correo_estudiante = :correo,
                fecha_nacimiento = :fecha_nac
                WHERE id_estudiante = :id
            ");
            $updateEstudiante->execute([
                ':nombre' => $nombre_estudiante,
                ':apellido' => $apellido_estudiante,
                ':telefono' => $telefono_estudiante,
                ':correo' => $correo_estudiante,
                ':fecha_nac' => $fecha_nacimiento,
                ':id' => $id_estudiante
            ]);
        } else {
            // PASO 2: Crear nuevo estudiante
            $insertEstudiante = $pdo->prepare("
                INSERT INTO estudiante (
                    ci_estudiante, nombre, apellido, fecha_nacimiento, 
                    telefono_estudiante, correo_estudiante, estado_estudiante
                ) VALUES (
                    :cedula, :nombre, :apellido, :fecha_nac, 
                    :telefono, :correo, 1
                )
            ");
            $insertEstudiante->execute([
                ':cedula' => $cedula_estudiante,
                ':nombre' => $nombre_estudiante,
                ':apellido' => $apellido_estudiante,
                ':fecha_nac' => $fecha_nacimiento,
                ':telefono' => $telefono_estudiante,
                ':correo' => $correo_estudiante
            ]);
            $id_estudiante = $pdo->lastInsertId();
        }

        // CREAR USUARIO PARA EL ESTUDIANTE
        $fechaCreacion = date("Y-m-d H:i:s");
        $claveEncriptada = password_hash($clave_estudiante, PASSWORD_BCRYPT);
        
        $insertUsuario = $pdo->prepare("
            INSERT INTO usuario (cargo, estado_usuario, fecha_creacion, Nombre, Password)
            VALUES (5, 1, :fecha, :nombre, :clave)
        ");
        $insertUsuario->execute([
            ':fecha' => $fechaCreacion,
            ':nombre' => $cedula_estudiante,
            ':clave' => $claveEncriptada
        ]);
        $id_usuario = $pdo->lastInsertId();

        $insertUsuarioEstudiante = $pdo->prepare("
            INSERT INTO usuario_estudiante (id_usuario_fk, id_estudiante_fk) 
            VALUES (:usuario, :estudiante)
        ");
        $insertUsuarioEstudiante->execute([
            ':usuario' => $id_usuario,
            ':estudiante' => $id_estudiante
        ]);

        // PASO 3: Verificar sección existente
        $id_seccion_final = $seccion_id;
        
        $consultaSeccion = $pdo->prepare("
            SELECT 
                s.id_seccion,
                COUNT(i.id_inscripcion) as inscritos_actuales
            FROM seccion s
            LEFT JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk AND i.archivada = 1
            WHERE s.id_seccion = :seccion_id 
            AND s.archivar_seccion = 1
            GROUP BY s.id_seccion
        ");
        $consultaSeccion->execute([':seccion_id' => $seccion_id]);
        
        if ($consultaSeccion->rowCount() !== 1) {
            throw new Exception("La sección seleccionada no está disponible");
        }
        
        $datosSeccion = $consultaSeccion->fetch();
        if ($datosSeccion['inscritos_actuales'] >= 20) {
            throw new Exception("La sección seleccionada ya tiene 20 estudiantes inscritos");
        }

        // PASO 4: Obtener datos del curso vigente para el pago
        $consultaCursoVigente = $pdo->prepare("
            SELECT 
                cv.id_curso_vigente, 
                cv.id_cohorte_fk,
                -- Datos de cohorte (si existe)
                ch.pago_inscripcion,
                ch.estado_cohorte,
                -- Datos de periodo intensivo (si existe)
                pi.pago_inscripcion_int,
                pi.estado_periodo,
                -- Determinar tipo
                CASE 
                    WHEN cv.id_cohorte_fk IS NOT NULL THEN 'cohorte'
                    ELSE 'intensivo'
                END AS tipo_curso
            FROM curso_vigente cv
            LEFT JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
            LEFT JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            WHERE cv.id_curso_vigente = :id_curso_vigente
            AND cv.estado_curso_vigente = 1
        ");
        $consultaCursoVigente->execute([':id_curso_vigente' => $id_curso_vigente_fk]);
        
        if ($consultaCursoVigente->rowCount() !== 1) {
            throw new Exception("El curso vigente no está disponible");
        }
        
        $datosCurso = $consultaCursoVigente->fetch();
        
        // Determinar pago según el tipo de curso
        if ($datosCurso['tipo_curso'] === 'cohorte') {
            $pago_inscripcion = $datosCurso['pago_inscripcion'];
            // Validar estado del cohorte
            if (!in_array($datosCurso['estado_cohorte'], ['Vigente', 'Próximo'])) {
                throw new Exception("El cohorte no está Vigente o Próximo (estado: {$datosCurso['estado_cohorte']})");
            }
        } else {
            $pago_inscripcion = $datosCurso['pago_inscripcion_int'];
            // Validar estado del periodo intensivo
            if (!in_array($datosCurso['estado_periodo'], ['Vigente', 'Próximo'])) {
                throw new Exception("El periodo intensivo no está Vigente o Próximo (estado: {$datosCurso['estado_periodo']})");
            }
        }
        
        // Validar que el pago_inscripcion no sea nulo
        if ($pago_inscripcion === null) {
            throw new Exception("No se pudo determinar el monto de inscripción para este curso");
        }
        
        // Validar monto
        if ($monto > $pago_inscripcion) {
            throw new Exception("El monto ingresado ($monto) excede el precio de inscripción permitido ($pago_inscripcion)");
        }

        // Calcular pago faltante
        $pago_faltante = max(0, $pago_inscripcion - $monto);

        // PASO 5: Crear la inscripción
        $fecha_inscripcion_actual = date('Y-m-d');
        
        $insertInscripcion = $pdo->prepare("
            INSERT INTO inscripcion (
                id_curso_vigente_fk, id_estudiante_fk, id_seccion_fk,
                fecha_inscripcion, estado_inscripcion, archivada, pago_faltante
            ) VALUES (
                :curso_vigente, :estudiante, :seccion,
                :fecha_insc, 'Activa', 1, :pago_faltante
            )
        ");
        $insertInscripcion->execute([
            ':curso_vigente' => $id_curso_vigente_fk,
            ':estudiante' => $id_estudiante,
            ':seccion' => $id_seccion_final,
            ':fecha_insc' => $fecha_inscripcion_actual,
            ':pago_faltante' => $pago_faltante
        ]);
        
        $id_inscripcion = $pdo->lastInsertId();

        // PASO 6: Crear transacción de pago
        $insertTransaccion = $pdo->prepare("
            INSERT INTO transaccion (
                id_inscripcion, monto, estatus_pago, motivo_pago,
                fecha_pago, metodo_pago, if_pagomovil_imagen, pago_faltante
            ) VALUES (
                :inscripcion, :monto, 1, 'Inscripción',
                :fecha_pago, :metodo, :imagen, :pago_faltante
            )
        ");
        $insertTransaccion->execute([
            ':inscripcion' => $id_inscripcion,
            ':monto' => $monto,
            ':fecha_pago' => $fecha_pago,
            ':metodo' => $metodo_pago,
            ':imagen' => $nombreImagen,
            ':pago_faltante' => $pago_faltante
        ]);

        // PASO 7: Actualizar estado de la solicitud
        $updateSolicitud = $pdo->prepare("
            UPDATE solicitud_inscripcion 
            SET estado_solicitud = 'Aceptada' 
            WHERE id_solicitud = :id_solicitud
        ");
        $updateSolicitud->execute([':id_solicitud' => $solicitud_id]);

        $pdo->commit();

        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Solicitud Aceptada",
            "Texto" => "La solicitud ha sido aceptada exitosamente. El estudiante ha sido inscrito en el curso.",
            "Tipo" => "success"
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "Error al aceptar la solicitud: " . $e->getMessage(),
            "Tipo" => "error"
        ];
    }

    return json_encode($alerta);
}
*/        
       


    }
