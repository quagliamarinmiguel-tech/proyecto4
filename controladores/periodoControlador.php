<?php

if($peticionAjax){
    require_once "../modelos/periodoModelo.php";
}else{
    require_once "./modelos/periodoModelo.php";
}


//maneja la logica de los periodos de los cursos, o cohortes e intensivos,
//en este ultimo caso tambien se manejan las vistas de crear y modificar
// ademas de las vistas de secciones y lista de evaluaciones y puntajes

//contar_evaluaciones_controlador
//agregar_seccion_controlador()
//obtener_puntajes_evaluacion_controlador
    //type
    class periodoControlador extends periodoModelo{

//archivada
        //paginador_periodo_archivadas_controlador22
//$dia

public function listar_cohortes_disponibles_controlador(){
    $consulta = mainModel::conectar()->prepare("
        SELECT 
            id_cohorte,
            numero_cohorte,
            anio_cohorte,
            tipo_cohorte
        FROM cohorte
        WHERE estado_cohorte IN ('Vigente', 'Próximo') 
          AND archivar_cohorte = 1
        ORDER BY anio_cohorte DESC, numero_cohorte ASC
    ");
    $consulta->execute();
    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

        
        

        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
//para periodo-new y update, trae la lista de cursos y profes para los period_course
   /*public function obtener_todos_los_cursos_para_periodo_controlador() {
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        return periodoModelo::listar_cursos_supabase_para_periodo($accessToken);
    }*/
  	public function obtener_todos_los_cursos_para_periodo_controlador(?string $type = null) {
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        return periodoModelo::listar_cursos_supabase_para_periodo($accessToken, $type);
    }
        
    public function obtener_todos_los_profesores_para_periodo_controlador() {
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        return periodoModelo::listar_profesores_supabase_para_periodo($accessToken);
   }
        
        
        
// para periodo new y update para validar periodos de nombre duplicado 
   public function validar_periodo_no_duplicado_supabase($nombre, $accessToken, $id = null): array {
   		return periodoModelo::verificar_periodo_duplicado_supabase($nombre, $accessToken, $id);
   }     

    public function validar_nombre_periodo_controlador($nombre, $id = null): string {
        $nombre = mainModel::limpiar_cadena($nombre);
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if ($nombre === '' || !$accessToken) {
            return json_encode(['valido' => false, 'repetido' => false]);
        }

        $resultado = $this->validar_periodo_no_duplicado_supabase($nombre, $accessToken, $id);
        $repetido = $resultado['ok'] && !empty($resultado['data']);
        return json_encode(['valido' => !$repetido, 'repetido' => $repetido]);
    }


        
        //para periodo new y update para traer el ultimo cohorte con nombre, y fecha de inicio y fin para dar contexto
    public function obtener_ultimo_periodo_controlador() {
        $tipo = mainModel::limpiar_cadena($_GET['tipo'] ?? '');

        if (empty($tipo)) {
            echo json_encode(['existe' => false]);
            return;
        }

        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if (!$accessToken) {
            echo json_encode(['existe' => false, 'error' => 'No session token']);
            return;
        }

        $resultado = periodoModelo::obtener_ultimo_periodo_supabase($tipo, $accessToken);
        if ($resultado['ok'] && !empty($resultado['data'][0])) {
            $periodo = $resultado['data'][0];
            echo json_encode([
                'existe' => true,
                'nombre' => $periodo['name'],
                'fecha_inicio' => $periodo['start_date'],
                'fecha_fin' => $periodo['end_date']
            ]);
        } else {
            echo json_encode(['existe' => false]);
        }
    }

                
        
    //para period update, trae el periodo sus period costs y period courses        
     public function datos_periodo_para_update_controlador($idEnc): array{
        $idReal = mainModel::decryption($idEnc);
        if (!$idReal) {
            return ['ok' => false, 'message' => 'ID inválido'];
        }
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        $resultado = periodoModelo::obtener_periodo_con_cursos_costos_supabase($idReal, $token);
        if (!$resultado['ok'] || !isset($resultado['data'])) {
            return ['ok' => false, 'message' => 'No se encontró el periodo'];
        }
        $data = $resultado['data'];
        return [
            'ok' => true,
            'periodo' => $data['periodo'],
            'cursos' => $data['cursos'] ?? [],
            'costos' => $data['costos'] ?? []
        ];
    }  
        
        


        
        
        
        
        


   
   public function crear_periodo_controlador(): string {
       return $this->crear_periodo_rpc_controlador();
   }

   private function crear_periodo_rpc_controlador(): string {
       $accessToken = $_SESSION['auth_access_token'] ?? null;
       $nombre = trim(mainModel::limpiar_cadena($_POST['periodo_nombre'] ?? ''));
       $tipo = trim(mainModel::limpiar_cadena($_POST['periodo_tipo'] ?? ''));
       $inicio = $_POST['periodo_fecha_inicio'] ?? '';
       $fin = $_POST['periodo_fecha_fin'] ?? '';
       $cursos = $_POST['cursos_vigentes'] ?? [];
       
              /*$paramounts = [
           'p_name' => $nombre,
           'p_type' => $tipo,
           'p_start_date' => $inicio,
           'p_end_date' => $fin,
           'el babo' => $accessToken];
      var_dump($paramounts); die();*/

       if (!$accessToken || $nombre === '' || $tipo === '' || $inicio === '' || $fin === '') {
        return  json_encode(mainModel::generarAlertaSimple("Todos los campos son obligatorios", "error", "simple"));
       }
       if (!in_array($tipo, ['Intensivo', 'Extendido', 'Técnico Profesional'], true)) {
            return  json_encode(mainModel::generarAlertaSimple("Selecciona un tipo de periodo válido.", "error", "simple"));
       }
       if ($inicio >= $fin) {
           return json_encode(mainModel::generarAlertaSimple("La fecha de inicio debe ser anterior a la fecha de fin.", "error", "simple"));
       }

        $hoy = date('Y-m-d');
        if ($fin < $hoy) {
            return json_encode(mainModel::generarAlertaSimple("La fecha de fin no puede ser anterior al día de hoy.", "error", "simple"));
        }
       
       $duplicado = $this->validar_periodo_no_duplicado_supabase($nombre, $accessToken);
       if ($duplicado['ok'] && !empty($duplicado['data'])) {
            return json_encode(mainModel::generarAlertaSimple("Ese nombre de periodo ya está registrado.", "error", "simple"));
       }

    $idsCursos = [];
    $teacherIds = [];
    $sectionDays = [];

    foreach ($cursos as $curso) {
        $courseId = (int) ($curso['course_id'] ?? 0);
        if ($courseId <= 0) {
            return json_encode(mainModel::generarAlertaSimple("Cada curso debe estar seleccionado correctamente.", "error", "simple"));
        }
        if (in_array($courseId, $idsCursos, true)) {
            return json_encode(mainModel::generarAlertaSimple("No puedes agregar el mismo curso dos veces.", "error", "simple"));
        }
        $idsCursos[] = $courseId;

        $teacherId = (int) ($curso['teacher_id'] ?? 0);
        $day = trim($curso['dia'] ?? '');
        if ($teacherId <= 0 || $day === '') {
            return json_encode(mainModel::generarAlertaSimple("Cada curso debe tener profesor y día de clases.", "error", "simple"));
        }
        $teacherIds[] = $teacherId;
        $sectionDays[] = $day;
    }

       $fechasPago = $tipo === 'Intensivo'
           ? ['periodo_fecha_pago_inscripcion_int', 'periodo_fecha_pago_encuentro_int', 'periodo_fecha_pago_titulo_int']
           : ['periodo_fecha_pago_inscripcion', 'periodo_fecha_modulo1', 'periodo_fecha_modulo2', 'periodo_fecha_modulo3', 'periodo_fecha_pago_art13', 'periodo_fecha_pago_aranceles'];
       $fechasPago = array_values(array_filter(array_map(fn($campo) => $_POST[$campo] ?? '', $fechasPago)));
       if (count($fechasPago) !== count(array_unique($fechasPago))) {
           return json_encode(mainModel::generarAlertaSimple("Las fechas de pago no pueden repetirse..", "error", "simple"));
       }
       foreach ($fechasPago as $fechaPago) {
           if ($fechaPago < $inicio || $fechaPago > $fin) {
                return json_encode(mainModel::generarAlertaSimple("Las fechas de pago deben estar dentro del periodo..", "error", "simple"));
           }
       }
       if ($tipo !== 'Intensivo') {
           $modulos = array_values(array_filter([
               $_POST['periodo_fecha_modulo1'] ?? '',
               $_POST['periodo_fecha_modulo2'] ?? '',
               $_POST['periodo_fecha_modulo3'] ?? '',
           ]));
           if (count($modulos) === 3 && !($modulos[0] < $modulos[1] && $modulos[1] < $modulos[2])) {
                return json_encode(mainModel::generarAlertaSimple("Las fechas de módulos deben estar en orden 1, 2 y 3.", "error", "simple"));
           }
       }

       $hoy = date('Y-m-d');
       $estado = $hoy > $fin ? 'Terminado' : ($hoy >= $inicio ? 'Vigente' : 'Próximo');
       $valor = static fn(string $campo): ?int => ($_POST[$campo] ?? '') === '' ? null : (int) $_POST[$campo];
       $fecha = static fn(string $campo): ?string => ($_POST[$campo] ?? '') === '' ? null : $_POST[$campo];

       $params = [
           'p_name' => $nombre,
           'p_type' => $tipo,
           'p_current_state' => $estado,
           'p_start_date' => $inicio,
           'p_end_date' => $fin,
           'p_period_state' => 1,
           'p_course_ids' => $idsCursos,
           'p_teacher_ids' => $teacherIds,
           'p_section_days' => $sectionDays,
           'p_int_inscripcion_price' => $tipo === 'Intensivo' ? $valor('periodo_pago_inscripcion_int') : null,
           'p_int_encuentro_price' => $tipo === 'Intensivo' ? $valor('periodo_pago_encuentro_int') : null,
           'p_int_titulo_price' => $tipo === 'Intensivo' ? $valor('periodo_pago_titulo_int') : null,
           'p_ext_inscripcion_price' => $tipo !== 'Intensivo' ? $valor('periodo_pago_inscripcion') : null,
           'p_ext_modulo_price' => $tipo !== 'Intensivo' ? $valor('periodo_pago_modulo') : null,
           'p_ext_art13_price' => $tipo !== 'Intensivo' ? $valor('periodo_pago_art13') : null,
           'p_ext_aranceles_price' => $tipo !== 'Intensivo' ? $valor('periodo_pago_aranceles') : null,
           'p_ext_inscripcion_date' => $tipo !== 'Intensivo' ? $fecha('periodo_fecha_pago_inscripcion') : null,
           'p_ext_modulo1_date' => $tipo !== 'Intensivo' ? $fecha('periodo_fecha_modulo1') : null,
           'p_ext_modulo2_date' => $tipo !== 'Intensivo' ? $fecha('periodo_fecha_modulo2') : null,
           'p_ext_modulo3_date' => $tipo !== 'Intensivo' ? $fecha('periodo_fecha_modulo3') : null,
           'p_ext_art13_date' => $tipo !== 'Intensivo' ? $fecha('periodo_fecha_pago_art13') : null,
           'p_ext_aranceles_date' => $tipo !== 'Intensivo' ? $fecha('periodo_fecha_pago_aranceles') : null,
       ];

       $resultado = periodoModelo::crear_periodo_rpc($params, $accessToken);
       $rpc = $resultado['data'] ?? null;
       if (!$resultado['ok'] || !is_array($rpc) || ($rpc['ok'] ?? false) !== true) {
			$message = 'Error al crear periodo: ' . ($rpc['error'] ?? $resultado['message'] ?? 'La RPC no pudo crear el periodo.');
           return json_encode(mainModel::generarAlertaSimple($message, "error", "simple"));
       }
       return json_encode(mainModel::generarAlertaSimple("El periodo fue registrado correctamente.", "success", "recargar"));
   }
        
        
        
        
        

    public function actualizar_periodo_controlador(): string{
        try {
            $idEnc = $_POST['periodo_id_up'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            if (!$idReal) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            // Obtener datos del formulario
            $nombre = trim(mainModel::limpiar_cadena($_POST['periodo_nombre_up'] ?? ''));
            $tipo = trim(mainModel::limpiar_cadena($_POST['periodo_tipo_up'] ?? ''));
            $inicio = $_POST['periodo_fecha_inicio_up'] ?? '';
            $fin = $_POST['periodo_fecha_fin_up'] ?? '';

            // Validaciones básicas
            if ($nombre === '' || $tipo === '' || $inicio === '' || $fin === '') {
                return json_encode(mainModel::generarAlertaSimple('Todos los campos son obligatorios', 'error', 'simple'));
            }
            if (!in_array($tipo, ['Intensivo', 'Extendido', 'Técnico Profesional'], true)) {
                return json_encode(mainModel::generarAlertaSimple('Tipo inválido', 'error', 'simple'));
            }
            if ($inicio >= $fin) {
                return json_encode(mainModel::generarAlertaSimple('La fecha de inicio debe ser anterior a la fecha de fin', 'error', 'simple'));
            }

            // Validación de 10 días
            $hoy = new DateTime();
            $fechaInicio = new DateTime($inicio);
            $diasRestantes = $hoy->diff($fechaInicio)->days;
            if ($fechaInicio <= $hoy || $diasRestantes <= 10) {
                return json_encode(mainModel::generarAlertaSimple('No se puede modificar el periodo (menos de 10 días para el inicio o ya empezó).', 'error', 'simple'));
            }

            // Validar nombre duplicado (excluyendo el actual)
            $duplicado = $this->validar_periodo_no_duplicado_supabase($nombre, $token, $idReal);
            if ($duplicado['ok'] && !empty($duplicado['data'])) {
                return json_encode(mainModel::generarAlertaSimple('Ese nombre de periodo ya está registrado', 'error', 'simple'));
            }


            $fechasPago = $tipo === 'Intensivo'
                ? [] // Intensivo no tiene fechas de pago (solo precios)
                : [
                    $_POST['periodo_fecha_modulo1_up'] ?? '',
                    $_POST['periodo_fecha_modulo2_up'] ?? '',
                    $_POST['periodo_fecha_modulo3_up'] ?? '',
                    $_POST['periodo_fecha_pago_art13_up'] ?? '',
                    $_POST['periodo_fecha_pago_aranceles_up'] ?? '',
                ];

            if ($tipo !== 'Intensivo') {
                $fechasPago = array_filter($fechasPago); // elimina vacíos
                if (count($fechasPago) !== count(array_unique($fechasPago))) {
                    return json_encode(mainModel::generarAlertaSimple('Las fechas de pago no pueden repetirse.', 'error', 'simple'));
                }

                foreach ($fechasPago as $fecha) {
                    if ($fecha < $inicio || $fecha > $fin) {
                        return json_encode(mainModel::generarAlertaSimple('Todas las fechas de pago deben estar dentro del rango del periodo.', 'error', 'simple'));
                    }
                }

                // Validar orden de módulos (solo si las tres existen)
                $modulo1 = $_POST['periodo_fecha_modulo1_up'] ?? '';
                $modulo2 = $_POST['periodo_fecha_modulo2_up'] ?? '';
                $modulo3 = $_POST['periodo_fecha_modulo3_up'] ?? '';
                if ($modulo1 && $modulo2 && $modulo3) {
                    if (!($modulo1 < $modulo2 && $modulo2 < $modulo3)) {
                        return json_encode(mainModel::generarAlertaSimple('Las fechas de módulos deben estar en orden: 1, 2 y 3.', 'error', 'simple'));
                    }
                }
            }    

               
            // Recoger nuevos cursos
            $cursosNuevos = $_POST['cursos_vigentes'] ?? [];
            $idsCursos = [];
            $teacherIds = [];
            $sectionDays = [];

            foreach ($cursosNuevos as $curso) {
                $courseId = (int) ($curso['course_id'] ?? 0);
                $teacherId = (int) ($curso['teacher_id'] ?? 0);
                $day = trim($curso['dia'] ?? '');
                if ($courseId <= 0 || $teacherId <= 0 || $day === '') {
                    return json_encode(mainModel::generarAlertaSimple('Cada curso debe tener profesor y día', 'error', 'simple'));
                }
                if (in_array($courseId, $idsCursos, true)) {
                    return json_encode(mainModel::generarAlertaSimple('No puedes agregar el mismo curso dos veces', 'error', 'simple'));
                }
                $idsCursos[] = $courseId;
                $teacherIds[] = $teacherId;
                $sectionDays[] = $day;
            }

            // Recoger costos según el tipo
            $valor = static fn(string $campo): ?int => ($_POST[$campo] ?? '') === '' ? null : (int) $_POST[$campo];
            $fecha = static fn(string $campo): ?string => ($_POST[$campo] ?? '') === '' ? null : $_POST[$campo];

            // Construir parámetros base
            $params = [
                'p_period_id' => $idReal,
                'p_name' => $nombre,
                'p_type' => $tipo,
                'p_start_date' => $inicio,
                'p_end_date' => $fin,
            ];

            // Agregar parámetros de costos según tipo
            if ($tipo === 'Intensivo') {
                $params['p_int_inscripcion_price'] = $valor('periodo_pago_inscripcion_int_up');
                $params['p_int_encuentro_price'] = $valor('periodo_pago_encuentro_int_up');
                $params['p_int_titulo_price'] = $valor('periodo_pago_titulo_int_up');
            } else {
                $params['p_ext_inscripcion_price'] = $valor('periodo_pago_inscripcion_up');
                $params['p_ext_modulo_price'] = $valor('periodo_pago_modulo_up');
                $params['p_ext_art13_price'] = $valor('periodo_pago_art13_up');
                $params['p_ext_aranceles_price'] = $valor('periodo_pago_aranceles_up');

                $params['p_ext_modulo1_date'] = $fecha('periodo_fecha_modulo1_up');
                $params['p_ext_modulo2_date'] = $fecha('periodo_fecha_modulo2_up');
                $params['p_ext_modulo3_date'] = $fecha('periodo_fecha_modulo3_up');
                $params['p_ext_art13_date'] = $fecha('periodo_fecha_pago_art13_up');
                $params['p_ext_aranceles_date'] = $fecha('periodo_fecha_pago_aranceles_up');
            }

            // Agregar cursos nuevos si existen
            if (!empty($idsCursos)) {
                $params['p_course_ids'] = $idsCursos;
                $params['p_teacher_ids'] = $teacherIds;
                $params['p_section_days'] = $sectionDays;
            }

            $resultado = periodoModelo::actualizar_periodo($params, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                return json_encode(mainModel::generarAlertaSimple('Periodo actualizado correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al actualizar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        
        
      
        
        
        
        
      
        
        
        
        
        
        
public function paginador_periodo_controlador($pagina, $registros, $vista, $busqueda, $state = 1) {
    $pagina = max(1, (int) $pagina);
    $registros = max(1, (int) $registros);
    $token = $_SESSION['auth_access_token'] ?? null;

    if (!$token) {
        return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
    }

    // Preparar filtros desde GET (incluyendo los nuevos)
    $filtros = [
        'busqueda' => trim($busqueda),
        'tipo' => trim($_GET['filtro_tipo'] ?? '') ?: null,
        'estado' => trim($_GET['filtro_estado'] ?? '') ?: null,
        'orden' => $_GET['orden'] ?? 'id_desc',
        'state' => $state,
        'id' => isset($_GET['filtro_id']) && $_GET['filtro_id'] !== '' ? (int) $_GET['filtro_id'] : null,
        'anio' => isset($_GET['filtro_anio']) && $_GET['filtro_anio'] !== '' ? (int) $_GET['filtro_anio'] : null,
    ];

    $resultado = periodoModelo::listar_periodos_con_costos_supabase(
        $filtros,
        $token,
        $registros,
        ($pagina - 1) * $registros
    );

    if (!$resultado['ok']) {
        return '<div class="alert alert-danger">No se pudo cargar la lista de periodos.</div>';
    }

    $periodos = $resultado['data']['data'] ?? [];
    $total = (int) ($resultado['data']['total'] ?? 0);
    $paginas = max(1, (int) ceil($total / $registros));

    // Cargo y carpeta
    $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
    $carpetas = [1 => 'admin', 2 => 'Control de estudio', 3 => 'administrador de pagos', 4 => 'Supervisor de cursos', 5 => 'Estudiante'];
    $carpeta = $carpetas[$cargo] ?? 'admin';
    $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

    // Generar tabla
    $tabla = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center roboto-medium">';
    $tabla .= '<th>ID</th><th>Nombre</th><th>Tipo</th><th>Fecha inicio/fin</th><th>Estado</th>';
    
    $tabla .= '<th>Costos</th>';
    $tabla .= '<th>Ver periodos</th>';

    if (in_array($cargo, [1, 4], true)) {
        if ($state == 1) {
            $tabla .= '<th>Actualizar</th>';
            $tabla .= '<th>Archivar</th>';
        } else {
            $tabla .= '<th>Restaurar</th>';
        }
    }
    $tabla .= '</tr></thead><tbody>';

    if ($total > 0) {
        $regInicio = ($pagina - 1) * $registros + 1;
        foreach ($periodos as $periodo) {
            $id = (int) $periodo['id'];
            $name = htmlspecialchars($periodo['name'] ?? '', ENT_QUOTES, 'UTF-8');
            $type = htmlspecialchars($periodo['type'] ?? '', ENT_QUOTES, 'UTF-8');
            $startDate = htmlspecialchars($periodo['start_date'] ?? '', ENT_QUOTES, 'UTF-8');
            $endDate = htmlspecialchars($periodo['end_date'] ?? '', ENT_QUOTES, 'UTF-8');
            $current_state = htmlspecialchars($periodo['current_state'] ?? '', ENT_QUOTES, 'UTF-8');
            $state = htmlspecialchars($periodo['state'] ?? '', ENT_QUOTES, 'UTF-8');
            $date = 'FI:' . $periodo['start_date'] . ' - FF:' . $periodo['end_date'];
            $costos = $periodo['costos'] ?? [];
            $idEnc = mainModel::encryption($id);

            $tabla .= '<tr class="text-center">';
            $tabla .= '<td>' . $id . '</td>';
            $tabla .= '<td>' . $name . '</td>';
            $tabla .= '<td>' . $type . '</td>';
            $tabla .= '<td>' . $date . '</td>'; 
            $tabla .= '<td>' . $current_state . '</td>';

            $tabla .= '<td>
                <button type="button" class="btn btn-info btn-sm ver-costos" title="ver precios del periodo"
                        data-periodo-id="' . $id . '"
                        data-periodo-nombre="' . $name . '"
                        data-costos=\'' . json_encode($costos, JSON_HEX_APOS) . '\'>
                    <i class="fas fa-dollar-sign"></i>
                </button>
            </td>';

            if($state==1){
                $tabla .= '<td>
                    <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list&filtro_id_periodo=' . $id. '&origen=periodo" class="btn btn-info btn-sm" title="Ver cursos vigentes">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>';
            }
            
            if($state==0){
                $tabla .= '<td>
                    <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list-archived&filtro_id_cohorte=' . $id . '&origen=cohorte" class="btn btn-info btn-sm" title="Ver cursos vigentes">
                        <i class="fas fa-eye"></i>
                    </a>
                </td>';
            }
            if (in_array($cargo, [1, 4], true)) {
                if ($state == 1) {
                    $tabla .= '<td><a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-update&periodo_id_up=' . $idEnc . '" class="btn btn-success btn-sm"><i class="fas fa-sync-alt"></i></a></td>';
                    $tabla .= '<td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/periodoAjax.php" method="POST" data-form="delete" data-entity="periodo" autocomplete="off">
                            <input type="hidden" name="periodo_id_archive" value="' . $idEnc . '">
                            <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-archive"></i></button>
                        </form>
                    </td>';
                } else {
                    $tabla .= '<td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/periodoAjax.php" method="POST" data-form="restore" data-entity="periodo" autocomplete="off">
                            <input type="hidden" name="periodo_id_restore" value="' . $idEnc . '">
                            <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-undo"></i> Restaurar</button>
                        </form>
                    </td>';
                }
            }
            $tabla .= '</tr>';
        }
        $regFinal = $regInicio + count($periodos) - 1;
    } else {
        $columnas = in_array($cargo, [1, 4]) ? 8 : 6;
        $tabla .= '<tr class="text-center"><td colspan="' . $columnas . '">No hay periodos registrados.</td></tr>';
    }
    $tabla .= '</tbody></table></div>';

    // Paginador
    if ($total > 0) {
        $tabla .= '<p class="text-right">Mostrando periodo ' . $regInicio . ' al ' . $regFinal . ' de un total de ' . $total . '</p>';
        // Incluir TODOS los filtros en la URL del paginador
        $paramsGet = array_filter([
            'busqueda' => $busqueda,
            'filtro_tipo' => $_GET['filtro_tipo'] ?? '',
            'filtro_estado' => $_GET['filtro_estado'] ?? '',
            'orden' => $_GET['orden'] ?? '',
            'filtro_id' => $_GET['filtro_id'] ?? '',
            'filtro_anio' => $_GET['filtro_anio'] ?? '',
        ]);
        $queryString = http_build_query($paramsGet);
        if ($queryString) {
            $queryString = '&' . $queryString;
        }
        $tabla .= mainModel::paginador_tablas($pagina, $paginas, SERVERURL . 'index.php?views=' . $vistaCompleta . $queryString . '&page=', 7);
    }

    // Modal de costos (sin cambios)
    $tabla .= '
    <div id="modalCostos" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
        <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#fff; border:3px solid #007bff; padding:20px; border-radius:8px;">
            <button onclick="document.getElementById(\'modalCostos\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-danger">
                <i class="fas fa-times"></i>
            </button>
            <h4 id="modalCostosTitulo" class="text-center">Costos del periodo</h4>
            <hr>
            <div id="modalCostosContenido" style="max-height:400px; overflow-y:auto;">
                <ul id="listaCostos" class="list-group">
                    <!-- Los costos se insertarán dinámicamente -->
                </ul>
            </div>
        </div>
    </div>

    <script>
    document.querySelectorAll(".ver-costos").forEach(btn => {
        btn.addEventListener("click", function() {
            const modal = document.getElementById("modalCostos");
            const titulo = document.getElementById("modalCostosTitulo");
            const lista = document.getElementById("listaCostos");

            const periodoNombre = this.dataset.periodoNombre;
            const costos = JSON.parse(this.dataset.costos);

            titulo.textContent = "Costos del periodo: " + periodoNombre;
            lista.innerHTML = "";

            if (costos.length === 0) {
                lista.innerHTML = "<li class=\\"list-group-item text-center\\">No hay costos registrados para este periodo.</li>";
            } else {
                costos.forEach(c => {
                    let fecha = c.cost_date ? " (Fecha: " + c.cost_date + ")" : "";
                    let precio = c.price !== null ? "$ " + c.price : "Sin precio";
                    let desc = c.cost_description || "Sin descripción";
                    let item = document.createElement("li");
                    item.className = "list-group-item d-flex justify-content-between align-items-center";
                    item.innerHTML = "<span><strong>" + desc + "</strong>" + fecha + "</span><span class=\\"badge badge-primary badge-pill\\">" + precio + "</span>";
                    lista.appendChild(item);
                });
            }

            modal.style.display = "block";
        });
    });

    // Cerrar modal al hacer clic fuera del contenido
    document.getElementById("modalCostos").addEventListener("click", function(e) {
        if (e.target === this) {
            this.style.display = "none";
        }
    });
    </script>';

    return $tabla;
}
        
        
        
        //para seccion list o period course list, trae los datos para ligar la vista con course list
    public function obtener_curso_por_id_controlador(int $id): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        return periodoModelo::obtener_curso_supabase($id, $token);
    }
                
        //para seccion list o period course list, trae los datos para ligar la vista con period list
    public function obtener_periodo_por_id_controlador(int $id): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        return periodoModelo::obtener_periodo_supabase($id, $token);
    }
        
        
        //archiva solo periodos sin inscritos, osea solo period, period course y section sin estudiantes, 
    public function archivar_periodo_controlador(): string
    {
        try {
            $idEnc = $_POST['periodo_id_archive'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $resultado = periodoModelo::archivar_periodo_cascada_supabase($idReal, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                return json_encode(mainModel::generarAlertaSimple('Periodo archivado correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al archivar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
  //restaura el period con sus period course y sections si no hay un periodo del mismo, tipo y cuyo rango de fechas coincida en al menos 1 dia con el del periodo archivado      
    public function restaurar_periodo_controlador(): string{
        try {
            $idEnc = $_POST['periodo_id_restore'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $resultado = periodoModelo::restaurar_periodo_cascada_supabase($idReal, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                return json_encode(mainModel::generarAlertaSimple('Periodo restaurado correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al restaurar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        
        
        
        
        
        
        
        
        
        
        
        /*-----------------------------------------------seguimos con period course--------------------------------------------------------*/
        
/**
 * Paginador de period_course (cursos asignados a periodos)
 * 
 * @param int    $pagina            Número de página actual
 * @param int    $registros         Registros por página
 * @param string $vista             Nombre de la vista (para enlaces)
 * @param string $busqueda_periodo  Filtro: nombre del periodo (ILIKE)
 * @param string $busqueda_curso    Filtro: nombre del curso (ILIKE)
 * @param int    $state             1 = activos, 0 = archivados
 * @return string HTML de la tabla con paginador y modal
 */
    public function paginador_period_course_controlador($pagina,$registros,$vista,$busqueda_periodo,$busqueda_curso,$state = 1, $course_id = null, $period_id = null) {
        // Sanitización y validación de parámetros
        $pagina = max(1, (int) $pagina);
        $registros = max(1, (int) $registros);
        $token = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

        // Construir array de filtros desde GET
        $filtros = [
            'busqueda_periodo' => trim($busqueda_periodo) ?: null,
            'busqueda_curso'   => trim($busqueda_curso) ?: null,
            'id_period_course' => isset($_GET['filtro_id']) && $_GET['filtro_id'] !== '' ? (int) $_GET['filtro_id'] : null,
            'estado_periodo'   => trim($_GET['filtro_estado'] ?? '') ?: null,
            'orden'            => $_GET['orden'] ?? 'periodo_desc',
            'state'            => (int) $state,
            'course_id'        => $course_id,  
            'period_id'        => $period_id,  
        ];

        // Llamar al modelo (RPC en Supabase)
        $resultado = periodoModelo::listar_period_course_resumen_supabase(
            $filtros,
            $token,
            $registros,
            ($pagina - 1) * $registros
        );

        if (!$resultado['ok']) {
            return '<div class="alert alert-danger">No se pudo cargar la lista de period‑cursos.</div>';
        }

        // Extraer datos y total
        $items = $resultado['data']['data'] ?? [];
        $total = (int) ($resultado['data']['total'] ?? 0);
        $paginas = max(1, (int) ceil($total / $registros));

        // Datos del usuario (cargo, carpeta)
        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        $carpetas = [
            1 => 'admin',
            2 => 'Control de estudio',
            3 => 'administrador de pagos',
            4 => 'Supervisor de cursos',
            5 => 'Estudiante'
        ];
        $carpeta = $carpetas[$cargo] ?? 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        // ============================================================
        // CONSTRUCCIÓN DE LA TABLA HTML
        // ============================================================
        $tabla = '<div class="table-responsive">';
        $tabla .= '<table class="table table-dark table-sm table-hover">';
        $tabla .= '<thead><tr class="text-center roboto-medium">';
        $tabla .= '<th>Periodo</th>';
        $tabla .= '<th>Curso</th>';
        $tabla .= '<th>Period‑Course ID</th>';
        $tabla .= '<th>Tipo</th>';
        $tabla .= '<th>Fechas</th>';
        $tabla .= '<th>Estado</th>';
        $tabla .= '<th>Secciones</th>';  
       
        if($state==1){$tabla .= '<th>Crear sección</th>';}
        
        if (in_array($cargo, [1, 4], true)) {
            $tabla .= '<th>Acciones</th>';
        }
        $tabla .= '</tr></thead><tbody>';

        // ============================================================
        // RENDERIZADO DE FILAS
        // ============================================================
        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;
            foreach ($items as $item) {
                // Extraer datos
                $pcId = (int) $item['period_course_id'];
                $periodId = $item['period_id'];
                $periodName = htmlspecialchars($item['period_name'] ?? '', ENT_QUOTES, 'UTF-8');
                $periodType = htmlspecialchars($item['period_type'] ?? '', ENT_QUOTES, 'UTF-8');
                $periodState = htmlspecialchars($item['period_current_state'] ?? '', ENT_QUOTES, 'UTF-8');
                $startDate = htmlspecialchars($item['period_start_date'] ?? '', ENT_QUOTES, 'UTF-8');
                $endDate = htmlspecialchars($item['period_end_date'] ?? '', ENT_QUOTES, 'UTF-8');
                $courseId = $item['course_id'];
                $courseName = htmlspecialchars($item['course_name'] ?? '', ENT_QUOTES, 'UTF-8');
                $sectionsCount = (int) ($item['sections_count'] ?? 0);
                $encryptedPcId = mainModel::encryption($pcId);

                // Fila
                $tabla .= '<tr class="text-center">';
                $tabla .= '<td>' . $periodId . ' / ' . $periodName . '</td>';
                $tabla .= '<td>' . $courseId . ' / ' . $courseName . '</td>';
                $tabla .= '<td>' . $pcId . '</td>';
                $tabla .= '<td>' . $periodType . '</td>';
                $tabla .= '<td>' . $startDate . ' / ' . $endDate . '</td>';
                $tabla .= '<td><span class="badge ' . $this->getEstadoBadgeClass($periodState) . '">' . $periodState . '</span></td>';

                // Botón "Ver secciones" (dispara el modal)
                if($state==1){
                    $consultarseccionesdestino = 'seccion-consult';
                	$archivar="0";
				}
                if($state==0){
                    $consultarseccionesdestino = 'seccion-consult-archived';
                    $archivar="1";
				}
                $tabla .= '<td>
                    <button type="button" class="btn btn-secondary btn-sm ver-secciones" 
                            data-id="' . $pcId . '" 
                            data-nombre="' . $courseName . '" 
                            data-carpeta="' . $carpeta . '"
                            data-vista="' . $consultarseccionesdestino . '"
                            data-archivadas="' . $archivar . '">
                        <i class="fas fa-eye"></i> (' . $sectionsCount . ')
                    </button>
                </td>';

        	     if($state==1){
                    // Botón "Crear sección"
                    $tabla .= '<td>
                        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-new&curso_vigente=' . $pcId . '" 
                           class="btn btn-success btn-sm" title="Crear sección">
                            <i class="fas fa-plus"></i>
                        </a>
                    </td>';
    	        }
	
                // Botones de Archivar / Restaurar (solo admin y supervisor)
                if (in_array($cargo, [1, 4], true)) {
                    $tabla .= '<td>';
                    if ($state == 1) {
                        // Activo → Mostrar botón Archivar
                        $tabla .= '
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/periodoAjax.php" method="POST" data-form="delete" data-entity="period_course" autocomplete="off">
                                <input type="hidden" name="period_course_id_archive" value="' . $encryptedPcId . '">
                                <button type="submit" class="btn btn-warning btn-sm" title="Archivar">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                        ';
                    } else {
                        // Archivado → Mostrar botón Restaurar
                        $tabla .= '
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/periodoAjax.php" method="POST" data-form="restore" data-entity="period_course" autocomplete="off">
                                <input type="hidden" name="period_course_id_restore" value="' . $encryptedPcId . '">
                                <button type="submit" class="btn btn-info btn-sm" title="Restaurar">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                        ';
                    }
                    $tabla .= '</td>';
                }

                $tabla .= '</tr>';
            }
            $regFinal = $regInicio + count($items) - 1;
        } else {
            // No hay registros
            $columnas = in_array($cargo, [1, 4]) ? 9 : 7;
            $tabla .= '<tr class="text-center"><td colspan="' . $columnas . '">No hay period‑cursos registrados.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // ============================================================
        // PAGINADOR
        // ============================================================
        if ($total > 0) {
            $tabla .= '<p class="text-right">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de un total de ' . $total . '</p>';
            $paramsGet = array_filter([
                'busqueda_periodo' => $busqueda_periodo,
                'busqueda_curso'   => $busqueda_curso,
                'filtro_id'        => $_GET['filtro_id'] ?? '',
                'filtro_estado'    => $_GET['filtro_estado'] ?? '',
                'orden'            => $_GET['orden'] ?? '',
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

        // MODAL DE SECCIONES (reutilizando la función existente)
        $tabla .= mainModel::generar_modal_secciones_js('seccion-consult');

        return $tabla;
    }

    /**
     * Helper para asignar clase CSS según el estado del periodo
     */
    private function getEstadoBadgeClass($estado)
    {
        return match ($estado) {
            'Vigente'   => 'badge-success',
            'Próximo'   => 'badge-primary',
            'Terminado' => 'badge-danger',
            default     => 'badge-secondary',
        };
    }        
        

// para manejar el modal de secciones de period list        
    public function obtener_secciones_ajax(): void
    {
        $periodCourseId = $_POST['curso_vigente_id'] ?? null;
        if (!$periodCourseId) {
            echo json_encode([]);
            return;
        }
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            echo json_encode([]);
            return;
        }
        $soloArchivadas = isset($_POST['solo_archivadas']) && $_POST['solo_archivadas'] === '1';
        $resultado = periodoModelo::obtener_secciones_por_period_course_supabase(
            (int) $periodCourseId,
            $soloArchivadas,
            $token
        );
        echo json_encode($resultado['data'] ?? []);
    }
        
        
        
        
        
    public function archivar_period_course_controlador(): string{
        try {
            $idEnc = $_POST['period_course_id_archive'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $resultado = periodoModelo::archivar_period_course_supabase($idReal, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                return json_encode(mainModel::generarAlertaSimple(
                    $resultado['data']['message'] ?? 'Period-course archivado correctamente',
                    'success',
                    'recargar'
                ));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al archivar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        
        
        
        
        
        
        
        
        
/*--------------------------------------------------------a partir de aqui empieza las secciones-----------------------------------------------------------------*/


    public function datos_para_nueva_seccion_controlador($periodCourseId): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }

        // Obtener period_course con periodo y curso
        $resultadoPC = SupabaseApi::getWithToken('period_course', [
            'select' => 'id,period_id,period(name,type,start_date,end_date),course_id,course(name,type)',
            'id' => 'eq.' . $periodCourseId,
        ], $token);
        if (!$resultadoPC['ok'] || empty($resultadoPC['data'][0])) {
            return ['ok' => false, 'message' => 'Period‑course no encontrado'];
        }
        $data = $resultadoPC['data'][0];
        $period = $data['period'];
        $course = $data['course'];

        // Obtener siguiente letra para sección
        $nextLetter = $this->obtener_siguiente_letra_seccion($periodCourseId, $token);
        if ($nextLetter === null) {
            return ['ok' => false, 'message' => 'No se pueden crear más secciones (máximo 26)'];
        }

        // Obtener profesores activos
        //$profesores = $this->obtener_profesores_activos_para_select();
      //  $profesores = $profesoresData['data'] ?? []; 
            $profesoresData = $this->obtener_profesores_activos_para_select();
    $profesores = $profesoresData['data'] ?? []; 

        return [
            'ok' => true,
            'data' => $data,
            'profesores' => $profesores,
            'next_section_name' => $nextLetter,
        ];
    }
        
    public function obtener_siguiente_letra_seccion(int $periodCourseId, string $accessToken): ?string{
        // Contar secciones existentes para este period_course_id
        $resultado = SupabaseApi::getWithToken('section', [
            'select' => 'count',
            'period_course_id' => 'eq.' . $periodCourseId,
            'state' => 'eq.1', // solo secciones activas
        ], $accessToken);

        if (!$resultado['ok']) {
            return null;
        }

        $count = (int) ($resultado['data'][0]['count'] ?? 0);
        if ($count >= 26) {
            return null; // ya hay 26 secciones (A-Z)
        }

        $letras = range('A', 'Z');
        return $letras[$count];
    }
        
        

    public function obtener_profesores_activos_para_select(): array{
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        return periodoModelo::listar_profesores_supabase_para_periodo($accessToken);
    }
        
/**
 * Crea una nueva sección (sin encuentros)
 * Espera: period_course_id, section_name, section_day, teacher_id
 *
        public function crear_seccion_controlador(): string{
        try {
            // 1. Verificar sesión
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            // 2. Recibir y limpiar datos
            $periodCourseId = (int) ($_POST['period_course_id'] ?? 0);
            $sectionName = trim(mainModel::limpiar_cadena($_POST['section_name'] ?? ''));
            $sectionDay = trim(mainModel::limpiar_cadena($_POST['section_day'] ?? ''));
            $teacherId = (int) ($_POST['teacher_id'] ?? 0);

            // 3. Validaciones básicas
            if ($periodCourseId <= 0 || $sectionName === '' || $sectionDay === '' || $teacherId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('Todos los campos son obligatorios', 'error', 'simple'));
            }

            // 4. Validar que la letra de sección aún esté disponible (seguridad)
            $nextLetter = $this->obtener_siguiente_letra_seccion($periodCourseId, $token);
            if ($nextLetter === null || $nextLetter !== $sectionName) {
                return json_encode(mainModel::generarAlertaSimple('El nombre de sección ya no está disponible', 'error', 'simple'));
            }

            // 5. Preparar datos para la inserción
            $datos = [
                'period_course_id' => $periodCourseId,
                'name' => $sectionName,
                'section_day' => $sectionDay,
                'teacher_id' => $teacherId,
                'state' => 1, // activa por defecto
            ];

            // 6. Llamar al modelo para crear la sección
            $resultado = periodoModelo::crear_seccion_supabase($datos, $token);

            // 7. Manejar respuesta
            if ($resultado['ok'] && !empty($resultado['data'])) {
                return json_encode(mainModel::generarAlertaSimple('Sección creada exitosamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al crear sección: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }*/
    public function crear_seccion_controlador(): string{
        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $periodCourseId = (int) ($_POST['period_course_id'] ?? 0);
            $sectionName = trim(mainModel::limpiar_cadena($_POST['section_name'] ?? ''));
            $sectionDay = trim(mainModel::limpiar_cadena($_POST['section_day'] ?? ''));
            $teacherId = (int) ($_POST['teacher_id'] ?? 0);

            if ($periodCourseId <= 0 || $sectionName === '' || $sectionDay === '' || $teacherId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('Todos los campos son obligatorios', 'error', 'simple'));
            }

            $estadoPeriodo = periodoModelo::obtener_estado_periodo_de_seccion_supabase(
                $token,
                $periodCourseId,
                null
            );
            if ($estadoPeriodo === 'Terminado') {
                return json_encode(mainModel::generarAlertaSimple(
                    'No se puede crear una sección porque el periodo ya ha terminado.',
                    'error', 'simple'
                ));
            }

            // Verificar que la letra de sección aún esté disponible
            $nextLetter = $this->obtener_siguiente_letra_seccion($periodCourseId, $token);
            if ($nextLetter === null || $nextLetter !== $sectionName) {
                return json_encode(mainModel::generarAlertaSimple('El nombre de sección ya no está disponible', 'error', 'simple'));
            }

            $datos = [
                'period_course_id' => $periodCourseId,
                'name' => $sectionName,
                'section_day' => $sectionDay,
                'teacher_id' => $teacherId,
                'state' => 1,
            ];

            $resultado = periodoModelo::crear_seccion_supabase($datos, $token);

            if ($resultado['ok'] && !empty($resultado['data'])) {
                return json_encode(mainModel::generarAlertaSimple('Sección creada exitosamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al crear sección: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        

        
/**
 * Actualiza el profesor y el día de una sección.
 * Espera: id_seccion_up, profesor_up, seccion_dia_up
 *
    public function actualizar_seccion_controlador(): string
    {
        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $sectionId = (int) ($_POST['id_seccion_up'] ?? 0);
            $teacherId = (int) ($_POST['profesor_up'] ?? 0);
            $sectionDay = trim(mainModel::limpiar_cadena($_POST['seccion_dia_up'] ?? ''));

            if ($sectionId <= 0 || $teacherId <= 0 || $sectionDay === '') {
                return json_encode(mainModel::generarAlertaSimple('Todos los campos son obligatorios', 'error', 'simple'));
            }

            // Actualizar en Supabase
            $resultado = SupabaseApi::patchWithToken('section', ['id' => 'eq.' . $sectionId], [
                'teacher_id' => $teacherId,
                'section_day' => $sectionDay,
            ], $token);

            if ($resultado['ok']) {
                return json_encode(mainModel::generarAlertaSimple('Sección actualizada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al actualizar sección: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }*/
    public function actualizar_seccion_controlador(): string{
        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $sectionId = (int) ($_POST['id_seccion_up'] ?? 0);
            $teacherId = (int) ($_POST['profesor_up'] ?? 0);
            $sectionDay = trim(mainModel::limpiar_cadena($_POST['seccion_dia_up'] ?? ''));

            if ($sectionId <= 0 || $teacherId <= 0 || $sectionDay === '') {
                return json_encode(mainModel::generarAlertaSimple('Todos los campos son obligatorios', 'error', 'simple'));
            }

            // 🔥 VALIDACIÓN: Verificar que el periodo NO esté Terminado
            $estadoPeriodo = periodoModelo::obtener_estado_periodo_de_seccion_supabase(
                $token,
                0, // no tenemos period_course_id directamente
                $sectionId
            );
            if ($estadoPeriodo === 'Terminado') {
                return json_encode(mainModel::generarAlertaSimple(
                    'No se puede actualizar la sección porque el periodo ya ha terminado.',
                    'error', 'simple'
                ));
            }

            $data = [
                'teacher_id' => $teacherId,
                'section_day' => $sectionDay,
            ];

            $resultado = periodoModelo::actualizar_seccion_supabase($sectionId, $data, $token);

            if ($resultado['ok']) {
                return json_encode(mainModel::generarAlertaSimple('Sección actualizada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al actualizar sección: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        

    public function obtener_seccion_completa_controlador(int $sectionId): array
    {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        return periodoModelo::obtener_seccion_completa_supabase($sectionId, $token);
    }

    public function obtener_evaluaciones_por_seccion_controlador(int $sectionId): array
    {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        return periodoModelo::obtener_evaluaciones_por_seccion_supabase($sectionId, $token);
    }

    public function obtener_estudiantes_por_seccion_controlador(int $sectionId): array
    {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        return periodoModelo::obtener_estudiantes_por_seccion_supabase($sectionId, $token);
    }

    public function obtener_puntajes_por_evaluacion_controlador(int $evaluationId): array
    {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        return periodoModelo::obtener_puntajes_por_evaluacion_supabase($evaluationId, $token);
    }

    public function obtener_puntajes_por_seccion_controlador(int $sectionId): array
    {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        return periodoModelo::obtener_puntajes_por_seccion_supabase($sectionId, $token);
    }
        


/**
 * Elimina una sección que no tenga inscritos. para casos donde haya una seccion que sobre
 * Redirige a la lista de secciones archivadas (state=0).
     */
    public function eliminar_seccion_controlador(): string{
        try {
            // 1. Validar token de sesión
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            // 2. Validar permisos (solo admin o supervisor)
            $cargo = $_SESSION['cargo_spm'] ?? 0;
            if (!in_array($cargo, [1, 4])) {
                return json_encode(mainModel::generarAlertaSimple('No tienes permisos para eliminar secciones', 'error', 'simple'));
            }

            // 3. Obtener ID (puede ser encriptado o texto plano)
            $id = $_POST['id_seccion_eliminar'] ?? '';
            if (empty($id)) {
                return json_encode(mainModel::generarAlertaSimple('ID no proporcionado', 'error', 'simple'));
            }

            // Determinar si está encriptado (si contiene caracteres no numéricos o es muy largo)
            if (is_numeric($id)) {
                $idReal = (int) $id;
            } else {
                $idReal = mainModel::decryption($id);
                $idReal = (int) $idReal;
            }

            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            // 4. Llamar al modelo (RPC)
            $resultado = periodoModelo::eliminar_seccion_supabase($idReal, $token);

            // 5. Procesar respuesta
            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                // Construir URL de redirección a la lista de secciones archivadas
                $carpetas = [
                    1 => 'admin',
                    2 => 'Control de estudio',
                    3 => 'administrador de pagos',
                    4 => 'Supervisor de cursos',
                    5 => 'Estudiante'
                ];
                $carpeta = $carpetas[$cargo] ?? 'admin';
                $url = SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list';

                return json_encode([
                    'Alerta' => 'redireccionar',
                    'Titulo' => 'Sección eliminada',
                    'Texto' => 'La sección ha sido eliminada correctamente.',
                    'Tipo' => 'success',
                    'URL' => $url
                ]);
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al eliminar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }


        /**
 * Obtiene todos los datos necesarios para la vista de consulta de sección.
 * Reutiliza las funciones existentes del modelo y controlador.
 * 
 * @param int $periodCourseId ID del period_course.
 * @param string $sectionLetter Letra de la sección (A, B, C, ...).
 * @return array Con los datos de la sección, periodo, curso, profesor, evaluaciones activas y profesores para select.
 */
        public function obtener_datos_para_seccion_consulta(int $periodCourseId, string $sectionLetter): array
        {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return ['ok' => false, 'message' => 'Sesión no disponible'];
            }

            // 1. Obtener sección con periodo, curso y profesor (relaciones)
            $resultadoSeccion = periodoModelo::obtener_seccion_por_period_course_y_letra_supabase(
                $periodCourseId,
                $sectionLetter,
                $token
            );
            if (!$resultadoSeccion['ok'] || empty($resultadoSeccion['data'][0])) {
                return ['ok' => false, 'message' => 'Sección no encontrada'];
            }
            $seccion = $resultadoSeccion['data'][0];
            $sectionId = $seccion['id'];

            // 2. Obtener datos del profesor (si tiene asignado)
            $teacher = null;
            if (!empty($seccion['teacher_id'])) {
                $resultadoTeacher = periodoModelo::obtener_profesor_por_id_supabase(
                    $seccion['teacher_id'],
                    $token
                );
                if ($resultadoTeacher['ok'] && !empty($resultadoTeacher['data'][0])) {
                    $teacher = $resultadoTeacher['data'][0];
                }
            }

            // 3. Obtener evaluaciones activas de la sección (para conteo y suma de porcentajes)
            $resultadoEvals = periodoModelo::obtener_evaluaciones_por_seccion_supabase($sectionId, $token);
            $evaluacionesActivas = [];
            if ($resultadoEvals['ok']) {
                foreach ($resultadoEvals['data'] as $eval) {
                    if (isset($eval['state']) && $eval['state'] == 1) {
                        $evaluacionesActivas[] = $eval;
                    }
                }
            }
            $evaluacionesCount = count($evaluacionesActivas);
            $totalPorcentaje = 0;
            foreach ($evaluacionesActivas as $eval) {
                $totalPorcentaje += (float) ($eval['percentage'] ?? 0);
            }

            // 4. Obtener profesores para el select de modificación (usando función existente)
            $profesoresResult = $this->obtener_todos_los_profesores_para_periodo_controlador();
            $profesores = $profesoresResult['data'] ?? [];

            // 5. Preparar respuesta
            return [
                'ok' => true,
                'seccion' => $seccion,
                'period' => $seccion['period_course']['period'] ?? [],
                'course' => $seccion['period_course']['course'] ?? [],
                'teacher' => $teacher,
                'evaluaciones_count' => $evaluacionesCount,
                'total_porcentaje' => $totalPorcentaje,
                'profesores' => $profesores,
                'periodCourseId' => $periodCourseId,
                'sectionLetter' => $sectionLetter,
            ];
        }        


        /**
         * Controlador para transferir estudiantes entre secciones
         * Espera POST: id_seccion_origen, id_seccion_destino (pueden ser encriptados)
         */
    public function transferir_estudiantes_seleccionados_controlador(): string
    {
        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $cargo = $_SESSION['cargo_spm'] ?? 0;
            if (!in_array($cargo, [1, 4])) {
                return json_encode(mainModel::generarAlertaSimple('No tienes permisos', 'error', 'simple'));
            }

            $origen = $_POST['id_seccion_origen'] ?? '';
            $destino = $_POST['id_seccion_destino'] ?? '';
            $registrations = $_POST['registration_ids'] ?? [];

            if (empty($origen) || empty($destino) || empty($registrations)) {
                return json_encode(mainModel::generarAlertaSimple('Faltan datos', 'error', 'simple'));
            }

            // Desencriptar IDs
            $origenId = is_numeric($origen) ? (int) $origen : (int) mainModel::decryption($origen);
            $destinoId = is_numeric($destino) ? (int) $destino : (int) mainModel::decryption($destino);

            if ($origenId <= 0 || $destinoId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('IDs inválidos', 'error', 'simple'));
            }

            // Asegurar que registration_ids sea un array de enteros
            $regIds = array_map('intval', $registrations);

            $resultado = periodoModelo::transferir_estudiantes_seleccionados_supabase(
                $origenId,
                $destinoId,
                $regIds,
                $token
            );

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                $transferidos = $resultado['data']['transferidos'] ?? 0;
                return json_encode(mainModel::generarAlertaSimple(
                    $transferidos . ' estudiante(s) transferido(s)',
                    'success',
                    'recargar'
                ));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }        
    //ligado    
    public function obtener_datos_transferencia_ajax(): void{
        $sectionId = $_POST['section_id'] ?? 0;
//var_dump($sectionId);die();
        if (!$sectionId) {
            echo json_encode(['ok' => false, 'error' => 'ID de sección no proporcionado']);
            return;
        }
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            echo json_encode(['ok' => false, 'error' => 'Sesión no disponible']);
            return;
        }
        $resultado = periodoModelo::obtener_datos_transferencia_supabase((int) $sectionId, $token);
        echo json_encode($resultado);
    }
/*----------------------------------------------a partir de aqui empiezan las evaluaciones--------------------------------------------------*/

    /**
     * Obtiene todos los datos necesarios para la vista de nueva evaluación.
     * Incluye: sección, periodo, evaluaciones activas, estudiantes y datos de validación.
     */
    public function datos_para_evaluacion_new(int $periodCourseId, string $sectionLetter): array
    {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }

        // 1. Obtener sección con periodo y curso
        $seccionResult = periodoModelo::obtener_seccion_por_period_course_y_letra_supabase(
            $periodCourseId,
            $sectionLetter,
            $token
        );
        if (!$seccionResult['ok'] || empty($seccionResult['data'][0])) {
            return ['ok' => false, 'message' => 'Sección no encontrada'];
        }
        $seccion = $seccionResult['data'][0];
        $period = $seccion['period_course']['period'] ?? [];

        $evaluacionesResult = periodoModelo::obtener_evaluaciones_por_seccion_supabase(
            $seccion['id'],
            $token
        );
        $evaluaciones = $evaluacionesResult['data'] ?? [];
        $evaluacionesActivas = array_filter($evaluaciones, function($e) {
            return isset($e['state']) && $e['state'] == 1;
        });
        $totalActivas = count($evaluacionesActivas);
        $sumaPorcentajes = 0;
        foreach ($evaluacionesActivas as $e) {
            $sumaPorcentajes += (float) ($e['percentage'] ?? 0);
        }
        $porcentajeDisponible = max(0, 100 - $sumaPorcentajes);
        $limiteAlcanzado = ($totalActivas >= 6);

        /*$estudiantesResult = periodoModelo::obtener_estudiantes_por_seccion_supabase($seccion['id'],$token);*/
        
        $estudiantesResult = periodoModelo::obtener_estudiantes_por_seccion_supabase(
            $seccion['id'],
            9999, // límite grande para obtener todos
            0,   // offset
            $token,
            true // solo activos
        );
        $estudiantes = $estudiantesResult['data'] ?? [];

        return [
            'ok' => true,
            'seccion' => $seccion,
            'period' => $period,
            'evaluaciones_activas' => $evaluacionesActivas,
            'total_evaluaciones_activas' => $totalActivas,
            'suma_porcentajes' => $sumaPorcentajes,
            'porcentaje_disponible' => $porcentajeDisponible,
            'limite_alcanzado' => $limiteAlcanzado,
            'estudiantes' => $estudiantes,
            'periodCourseId' => $periodCourseId,
            'sectionLetter' => $sectionLetter,
        ];
    }
        
        
        
        
        
        

    public function agregar_evaluacion_controlador(): void{
        try {
            $sectionId = (int) ($_POST['id_seccion'] ?? 0);
            $name = trim(mainModel::limpiar_cadena($_POST['tipo_evaluacion'] ?? ''));
            $objective = (float) ($_POST['obj'] ?? 0);
            $percentage = (int) ($_POST['porcentaje'] ?? 0);
            $evaluationDate = $_POST['fecha_evaluacion'] ?? '';

            if ($sectionId <= 0 || $name === '' || $objective <= 0 || $percentage <= 0 || $evaluationDate === '') {
                echo json_encode(mainModel::generarAlertaSimple('Todos los campos son obligatorios', 'error', 'simple'));
                return;
            }

            if ($percentage < 1 || $percentage > 100) {
                echo json_encode(mainModel::generarAlertaSimple('El porcentaje debe estar entre 1 y 100', 'error', 'simple'));
                return;
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                echo json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
                return;
            }

            $puntajes = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'puntaje_') === 0) {
                    $registrationId = (int) str_replace('puntaje_', '', $key);
                    if ($registrationId > 0) {
                        $score = $value === 'NP' ? null : (float) $value;
                        $puntajes[] = [
                            'registration_id' => $registrationId,
                            'score' => $score
                        ];
                    }
                }
            }

            if (empty($puntajes)) {
                echo json_encode(mainModel::generarAlertaSimple('No se recibieron puntajes de estudiantes (o están mal formados)', 'error', 'simple'));
                return;
            }

            $puntajesJson = json_encode($puntajes);
            //var_dump($puntajesJson);die();
            if ($puntajesJson === false || $puntajesJson === 'null') {
                echo json_encode(mainModel::generarAlertaSimple('Error al codificar los puntajes', 'error', 'simple'));
                return;
            }

            // 4. Llamar a la RPC
            $datos = [
                'section_id' => $sectionId,
                'evaluation_type' => $name,
                'objective' => $objective,
                'percentage' => $percentage,
                'evaluation_date' => $evaluationDate,
                'puntajes' => $puntajes
            ];
            //var_dump($datos);die();
              //  'puntajes' => $puntajesJson

            $resultado = periodoModelo::crear_evaluacion_con_puntajes_supabase($datos, $token);


            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
      
                echo json_encode(mainModel::generarAlertaSimple('Evaluación registrada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                echo json_encode(mainModel::generarAlertaSimple('Error al crear evaluación: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            echo json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        


    public function actualizar_evaluacion_controlador(): void
    {
        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                echo json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
                return;
            }

            $evaluationId = (int) ($_POST['evaluacion_id_up'] ?? 0);
            $sectionId = (int) ($_POST['id_seccion'] ?? 0);
            $name = trim(mainModel::limpiar_cadena($_POST['tipo_evaluacion'] ?? ''));
            $objective = (float) ($_POST['obj'] ?? 0);
            $percentage = (int) ($_POST['porcentaje'] ?? 0);
            $evaluationDate = $_POST['fecha_evaluacion'] ?? '';

            if ($evaluationId <= 0 || $sectionId <= 0 || $name === '' || $objective <= 0 || $percentage <= 0 || $evaluationDate === '') {
                echo json_encode(mainModel::generarAlertaSimple('Todos los campos son obligatorios', 'error', 'simple'));
                return;
            }

            if ($percentage < 1 || $percentage > 100) {
                echo json_encode(mainModel::generarAlertaSimple('El porcentaje debe estar entre 1 y 100', 'error', 'simple'));
                return;
            }

            // Preparar puntajes (todos los estudiantes de la sección)
            $puntajes = [];
            foreach ($_POST as $key => $value) {
                if (strpos($key, 'puntaje_') === 0) {
                    $registrationId = (int) str_replace('puntaje_', '', $key);
                    if ($registrationId > 0) {
                        $score = $value === 'NP' ? null : (float) $value;
                        $puntajes[] = [
                            'registration_id' => $registrationId,
                            'score' => $score
                        ];
                    }
                }
            }

            if (empty($puntajes)) {
                echo json_encode(mainModel::generarAlertaSimple('No se recibieron puntajes de estudiantes', 'error', 'simple'));
                return;
            }

            $datos = [
                'evaluation_id' => $evaluationId,
                'evaluation_type' => $name,
                'objective' => $objective,
                'percentage' => $percentage,
                'evaluation_date' => $evaluationDate,
                'puntajes' => $puntajes,
            ];

            $resultado = periodoModelo::actualizar_evaluacion_con_puntajes_supabase($datos, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                echo json_encode(mainModel::generarAlertaSimple('Evaluación actualizada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                echo json_encode(mainModel::generarAlertaSimple('Error al actualizar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            echo json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        
    public function datos_para_evaluacion_update_controlador(int $evaluationId): array
    {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        return periodoModelo::obtener_evaluacion_para_update_supabase($evaluationId, $token);
    }
        
        
        
        
        
        
        


    /**
     * Paginador de evaluaciones (activas o archivadas)
     * @param int $pagina Número de página
     * @param int $registros Registros por página
     * @param int $sectionId ID de la sección
     * @param int $state 1: activas, 0: archivadas
     */
    public function paginador_evaluaciones_controlador(
        int $pagina,
        int $registros,
        int $sectionId,
        int $state = 1
    ): string {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return '<div class="alert alert-danger">Sesión no disponible.</div>';
        }

        $limit = max(1, $registros);
        $offset = ($pagina - 1) * $limit;

        $resultado = periodoModelo::listar_evaluaciones_por_seccion_supabase(
            $sectionId,
            $limit,
            $offset,
            $token,
            $state
        );
        if (!$resultado['ok']) {
            return '<div class="alert alert-danger">Error al cargar evaluaciones.</div>';
        }
        $evaluaciones = $resultado['data'] ?? [];

        $totalResult = periodoModelo::contar_evaluaciones_por_seccion_supabase(
            $sectionId,
            $token,
            $state
        );
        $total = $totalResult['ok'] ? (int) ($totalResult['data'][0]['count'] ?? 0) : 0;
        $paginas = max(1, ceil($total / $limit));

        // Datos del usuario (cargo, carpeta)
        $cargo = $_SESSION['cargo_spm'] ?? 0;
        $carpetas = [
            1 => 'admin',
            2 => 'Control de estudio',
            3 => 'administrador de pagos',
            4 => 'Supervisor de cursos',
            5 => 'Estudiante'
        ];
        $carpeta = $carpetas[$cargo] ?? 'admin';

        
        //<th>Puntajes</th>
        // Construir tabla
        $html = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center">';
        $html .= '<th>ID</th><th>Tipo</th><th>Objetivo</th><th>Porcentaje</th><th>Fecha</th>';
        if (in_array($cargo, [1, 2])) {
            $html .= '<th>Acciones</th>';
        }
        $html .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = $offset + 1;
            foreach ($evaluaciones as $eval) {
                $id = $eval['id'];
                $encId = mainModel::encryption($id);
                $html .= '<tr class="text-center">';
                $html .= '<td>' . $id . '</td>';
                $html .= '<td>' . htmlspecialchars($eval['evaluation_type'] ?? '') . '</td>';
                $html .= '<td>' . htmlspecialchars($eval['objective'] ?? '') . '</td>';
                $html .= '<td>' . ($eval['percentage'] ?? 0) . '%</td>';
                $html .= '<td>' . ($eval['evaluation_date'] ?? '') . '</td>';
                // Botón para abrir modal de puntajes
                
                // Dentro del bucle de evaluaciones
                /*
                $html .= '<td>
                    <button type="button" class="btn btn-info btn-sm ver-puntajes" 
                            data-evaluacion="' . $id . '"
                            data-nombre="' . htmlspecialchars($eval['evaluation_type'] ?? '') . '"
                            data-state="' . $state . '"> 
                        <i class="fas fa-eye"></i>
                    </button>
                </td>';*/
                
                // Acciones según estado
                if (in_array($cargo, [1, 2])) {
                    $html .= '<td>';
                    if ($state == 1) {
                        // Activa → botones de Editar y Archivar
                        $html .= '
                            <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/evaluacion-update&evaluacion_id_up=' . $encId . '" class="btn btn-success btn-sm" title="Actualizar"><i class="fas fa-edit"></i></a>
                            <form class="FormularioAjax d-inline" action="' . SERVERURL . 'ajax/periodoAjax.php" method="POST" data-form="delete" data-entity="evaluacion">
                                <input type="hidden" name="id_evaluacion_archivar" value="' . $encId . '">
                                <button type="submit" class="btn btn-warning btn-sm" title="archivar"><i class="fas fa-archive"></i></button>
                            </form>
                        ';
                    } else {
                        // Archivada → solo botón Restaurar
                        $html .= '
                            <form class="FormularioAjax d-inline" action="' . SERVERURL . 'ajax/periodoAjax.php" method="POST" data-form="restore" data-entity="evaluacion">
                                <input type="hidden" name="id_evaluacion_restaurar" value="' . $encId . '">
                                <button type="submit" class="btn btn-info btn-sm" title="Restaurar"><i class="fas fa-undo"></i></button>
                            </form>
                            <form class="FormularioAjax d-inline" action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" data-form="delete" data-entity="seccion_archivada" autocomplete="off">
                                <input type="hidden" name="id_seccion_eliminar" value="<?php echo $seccionId; ?>">
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar sección">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>
                        ';
                    }
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
            $regFinal = $regInicio + count($evaluaciones) - 1;
        } else {
            $colspan = in_array($cargo, [1, 2]) ? 7 : 6;
            $html .= '<tr><td colspan="' . $colspan . '" class="text-center">No hay evaluaciones registradas.</td></tr>';
        }

        $html .= '</tbody></table></div>';

        // Paginador
        if ($total > 0) {
            $html .= '<p class="text-right">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';
            $url = SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-consult&archivados=' . $state . '&curso_vigente=' . ($_GET['curso_vigente'] ?? 0) . '&seccion=' . ($_GET['seccion'] ?? '') . '&eval_page=';
            $html .= mainModel::paginador_tablas($pagina, $paginas, $url, 7);
        }

        /*
        // Agregar el modal genérico (se añade una sola vez)
        $html .= mainModel::generar_modal_tabla(
            'modalPuntajes',
            'Puntajes de la evaluación',
            ['CI', 'Nombre', 'Apellido', 'Puntaje'],
            'cargar_puntajes'
        );*/

        return $html;
    }        
        


    /**
     * Paginador de estudiantes con sus puntajes en evaluaciones (activas o archivadas).
     * @param int $pagina Número de página
     * @param int $registros Registros por página
     * @param int $sectionId ID de la sección
     * @param int $state 1: evaluaciones activas, 0: archivadas
     */
    public function paginador_estudiantes_con_puntajes_controlador(
        int $pagina,
        int $registros,
        int $sectionId,
        int $state = 1
    ): string {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return '<div class="alert alert-danger">Sesión no disponible.</div>';
        }

        $limit = max(1, $registros);
        $offset = ($pagina - 1) * $limit;

        // Llamar a la RPC
        $resultado = periodoModelo::listar_estudiantes_seccion_consulta_supabase(
            $sectionId,
            $limit,
            $offset,
            $state,
            $token
        );

        if (!$resultado['ok'] || !isset($resultado['data']['ok']) || !$resultado['data']['ok']) {
            return '<div class="alert alert-danger">Error al cargar datos: ' . ($resultado['data']['error'] ?? '') . '</div>';
        }

        $data = $resultado['data'];
        $estudiantes = $data['estudiantes'] ?? [];
        $evaluaciones = $data['evaluaciones'] ?? [];
        $puntajes = $data['puntajes'] ?? [];
        $total = (int) ($data['total_estudiantes'] ?? 0);
        $paginas = max(1, ceil($total / $limit));

        // Construir mapa de puntajes por registration_id y evaluation_id
        $puntajesMap = [];
        foreach ($puntajes as $p) {
            $regId = $p['registration_id'];
            $evalId = $p['evaluation_id'];
            if (!isset($puntajesMap[$regId])) {
                $puntajesMap[$regId] = [];
            }
            $puntajesMap[$regId][$evalId] = $p['score'];
        }

        // Generar tabla HTML
        $html = '<div class="table-responsive" style="max-height:500px; overflow-y:auto;">';
        $html .= '<table class="table table-dark table-sm table-bordered">';
        $html .= '<thead><tr class="text-center">';
        $html .= '<th>ID</th><th>Cédula</th><th>Nombre</th><th>Apellido</th>';

        // Si hay evaluaciones, agregar columnas dinámicas
        foreach ($evaluaciones as $eval) {
            $tipo = htmlspecialchars($eval['evaluation_type'] ?? 'Evaluación');
            $obj = $eval['objective'] ?? '';
            $html .= "<th>{$tipo}<br><small>(Obj: {$obj})</small></th>";
        }
        $html .= '<th>Nota final</th>';
        $html .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = $offset + 1;
            foreach ($estudiantes as $est) {
                $studentId = $est['student_id'] ?? '';
                $ci = $est['ci'] ?? '';
                $name = htmlspecialchars($est['name'] ?? '');
                $lastName = htmlspecialchars($est['last_name'] ?? '');
                $registrationId = $est['registration_id'];

                $html .= '<tr class="text-center">';
                $html .= "<td>{$studentId}</td>";
                $html .= "<td>{$ci}</td>";
                $html .= "<td>{$name}</td>";
                $html .= "<td>{$lastName}</td>";

                // Si no hay evaluaciones, mostrar mensaje o nada
                if (empty($evaluaciones)) {
                    $html .= '<td colspan="1" class="text-muted">Sin evaluaciones</td>';
                } else {
                    // Calcular nota final ponderada
                    $notaFinal = 0;
                    $totalPorcentaje = 0;
                    $tienePuntaje = false;

                    foreach ($evaluaciones as $eval) {
                        $evalId = $eval['id'];
                        $porcentaje = (float) ($eval['percentage'] ?? 0);
                        $totalPorcentaje += $porcentaje;

                        $puntaje = $puntajesMap[$registrationId][$evalId] ?? null;
                        $mostrar = ($puntaje !== null) ? $puntaje : 'NP';

                        if ($puntaje !== null && $puntaje !== 'NP') {
                            $notaFinal += (float) $puntaje * $porcentaje / 100;
                            $tienePuntaje = true;
                        }

                        $html .= "<td>{$mostrar}</td>";
                    }

                    // Nota final
                    if ($totalPorcentaje == 100 && $tienePuntaje) {
                        $notaRedondeada = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);
                        $color = ($notaRedondeada > 10) ? 'text-success' : 'text-danger';
                        $html .= "<td class='{$color}'><strong>{$notaRedondeada}</strong></td>";
                    } else {
                        $html .= "<td class='text-muted'>-</td>";
                    }
                }

                $html .= '</tr>';
            }
            $regFinal = $regInicio + count($estudiantes) - 1;
        } else {
            $colspan = empty($evaluaciones) ? 5 : (count($evaluaciones) + 5);
            $html .= "<tr><td colspan='{$colspan}' class='text-center'>No hay estudiantes inscritos en esta sección.</td></tr>";
        }

        $html .= '</tbody></table></div>';

        // Paginador
        if ($total > 0) {
            $html .= '<p class="text-right">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';
            $url = SERVERURL . 'index.php?views=admin/seccion-consult&curso_vigente=' . ($_GET['curso_vigente'] ?? 0) . '&seccion=' . ($_GET['seccion'] ?? '') . '&est_page=';
            $html .= mainModel::paginador_tablas($pagina, $paginas, $url, 7);
        }

        return $html;
    }
  
       
        
        
        
        
        
        
        
        
        
        
        
        
/**
 * Paginador de estudiantes con sus puntajes en evaluaciones (activas o archivadas).
 * @param int $pagina Número de página
 * @param int $registros Registros por página
 * @param int $sectionId ID de la sección
 * @param int $state 1: evaluaciones activas, 0: archivadas
 *
public function paginador_estudiantes_con_puntajes_controlador(
    int $pagina,
    int $registros,
    int $sectionId,
    int $state = 1
): string {
    $token = $_SESSION['auth_access_token'] ?? null;
    if (!$token) {
        return '<div class="alert alert-danger">Sesión no disponible.</div>';
    }

    $limit = max(1, $registros);
    $offset = ($pagina - 1) * $limit;

    // 1. Obtener evaluaciones de la sección con el state indicado
    $evalResult = periodoModelo::obtener_evaluaciones_por_seccion_y_state_supabase(
        $sectionId,
        $token,
        $state
    );
    if (!$evalResult['ok']) {
        return '<div class="alert alert-danger">Error al cargar evaluaciones.</div>';
    }
    $evaluaciones = $evalResult['data'] ?? [];

    // Si no hay evaluaciones, mostrar mensaje
    if (empty($evaluaciones)) {
        return '<div class="alert alert-info text-center">No hay evaluaciones ' . ($state == 1 ? 'activas' : 'archivadas') . ' para esta sección.</div>';
    }

    // 2. Obtener estudiantes (solo inscripciones activas)
    $estudiantesResult = periodoModelo::obtener_estudiantes_por_seccion_supabase(
        $sectionId,
        $limit,
        $offset,
        $token,
        true
    );
    if (!$estudiantesResult['ok']) {
        return '<div class="alert alert-danger">Error al cargar estudiantes: ' . ($estudiantesResult['message'] ?? '') . '</div>';
    }
    $estudiantes = $estudiantesResult['data'] ?? [];

    // 3. Obtener total de estudiantes para paginación
    $total = periodoModelo::contar_estudiantes_por_seccion_supabase($sectionId, $token, true);
    $paginas = max(1, ceil($total / $limit));

    // 4. Para cada evaluación, obtener puntajes y armar un mapa por registration_id
    $puntajesMap = [];
    foreach ($evaluaciones as $eval) {
        $evalId = $eval['id'];
        $puntajesResult = SupabaseApi::getWithToken('qualification_student', [
            'select' => 'registration_id, score',
            'evaluation_id' => 'eq.' . $evalId,
            'state' => 'eq.' . $state,
        ], $token);
        if ($puntajesResult['ok']) {
            $puntajesMap[$evalId] = [];
            foreach ($puntajesResult['data'] as $p) {
                $puntajesMap[$evalId][$p['registration_id']] = $p['score'];
            }
        } else {
            $puntajesMap[$evalId] = [];
        }
    }

    // 5. Construir la tabla HTML
    $html = '<div class="table-responsive" style="max-height:500px; overflow-y:auto;">';
    $html .= '<table class="table table-dark table-sm table-bordered">';
    $html .= '<thead><tr class="text-center">';
    $html .= '<th>ID</th><th>Cédula</th><th>Nombre</th><th>Apellido</th>';
    // Columnas dinámicas para cada evaluación
    foreach ($evaluaciones as $eval) {
        $tipo = htmlspecialchars($eval['evaluation_type'] ?? 'Evaluación');
        $obj = $eval['objective'] ?? '';
        $html .= "<th>{$tipo}<br><small>(Obj: {$obj})</small></th>";
    }
    $html .= '<th>Nota final</th>';
    $html .= '</tr></thead><tbody>';

    if ($total > 0) {
        $regInicio = $offset + 1;
        foreach ($estudiantes as $est) {
            $student = $est['student'] ?? [];
            $studentId = $student['id'] ?? '';
            $ci = $student['ci'] ?? '';
            $name = htmlspecialchars($student['name'] ?? '');
            $lastName = htmlspecialchars($student['last_name'] ?? '');
            $registrationId = $est['id']; // ID de inscripción

            $html .= '<tr class="text-center">';
            $html .= "<td>{$studentId}</td>";
            $html .= "<td>{$ci}</td>";
            $html .= "<td>{$name}</td>";
            $html .= "<td>{$lastName}</td>";

            // Calcular nota final ponderada
            $notaFinal = 0;
            $totalPorcentaje = 0;
            $tienePuntaje = false;

            foreach ($evaluaciones as $eval) {
                $evalId = $eval['id'];
                $porcentaje = (float) ($eval['percentage'] ?? 0);
                $totalPorcentaje += $porcentaje;

                // Obtener puntaje del mapa
                $puntaje = $puntajesMap[$evalId][$registrationId] ?? null;
                $mostrar = ($puntaje !== null) ? $puntaje : 'NP';

                if ($puntaje !== null && $puntaje !== 'NP') {
                    $notaFinal += (float) $puntaje * $porcentaje / 100;
                    $tienePuntaje = true;
                }

                $html .= "<td>{$mostrar}</td>";
            }

            // Nota final
            if ($totalPorcentaje == 100 && $tienePuntaje) {
                // Redondear según regla: >= .5 redondear arriba
                $notaRedondeada = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);
                $color = ($notaRedondeada > 10) ? 'text-success' : 'text-danger';
                $html .= "<td class='{$color}'><strong>{$notaRedondeada}</strong></td>";
            } else {
                $html .= "<td class='text-muted'>-</td>";
            }

            $html .= '</tr>';
        }
        $regFinal = $regInicio + count($estudiantes) - 1;
    } else {
        $colspan = count($evaluaciones) + 5; // ID, CI, Nombre, Apellido, Nota final
        $html .= "<tr><td colspan='{$colspan}' class='text-center'>No hay estudiantes inscritos en esta sección.</td></tr>";
    }

    $html .= '</tbody></table></div>';

    // Paginador
    if ($total > 0) {
        $html .= '<p class="text-right">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';
        // URL fija con carpeta 'admin'
        $url = SERVERURL . 'index.php?views=admin/seccion-consult&curso_vigente=' . ($_GET['curso_vigente'] ?? 0) . '&seccion=' . ($_GET['seccion'] ?? '') . '&est_page=';
        $html .= mainModel::paginador_tablas($pagina, $paginas, $url, 7);
    }

    return $html;
}*/
        
        
        
        

//restaurar evaluacion
    public function archivar_evaluacion_controlador($encryptedId): string
    {
        try {
            $idDec = mainModel::decryption($encryptedId);
            $idReal = (int) $idDec;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }
            $resultado = periodoModelo::archivar_evaluacion_con_puntajes_supabase($idReal, $token);
            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                return json_encode(mainModel::generarAlertaSimple('Evaluación archivada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al archivar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }


    public function restaurar_evaluacion_controlador($encryptedId): string
    {
        try {
            $idDec = mainModel::decryption($encryptedId);
            $idReal = (int) $idDec;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }
            $resultado = periodoModelo::restaurar_evaluacion_con_puntajes_supabase($idReal, $token);
            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                return json_encode(mainModel::generarAlertaSimple($resultado['data']['message'] ?? 'Evaluación restaurada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al restaurar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        

//modificar profe en seccion consult
public function modificar_profesor_seccion_controlador() {
    if (!isset($_POST['id_seccion_up']) || !isset($_POST['profesor_up']) || !isset($_POST['seccion_dia_up'])) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Datos incompletos",
            "Texto" => "Faltan datos para modificar el profesor",
            "Tipo" => "error"
        ]);
        exit();
    }

    $idSeccion = mainModel::decryption($_POST['id_seccion_up']);
    $idSec = $_POST['id_seccion_up'];
    $idProfesor = mainModel::decryption($_POST['profesor_up']);
    //$idSeccion = mainModel::limpiar_cadena($idSec);
    $idProfesor = mainModel::limpiar_cadena($_POST['profesor_up']);
    $dia = mainModel::limpiar_cadena($_POST['seccion_dia_up']); 
    $idSeccion = $idSec;
    
    if (!ctype_digit((string)$idSeccion) || !ctype_digit((string)$idProfesor)) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Datos inválidos",
            "Texto" => "Los identificadores no son válidos", 
            "Tipo" => "error"
        ]);
        exit();
    }

    // Verificar existencia de sección 
    $checkSeccion = mainModel::ejecutar_consulta_simple("SELECT id_seccion FROM seccion WHERE id_seccion = '$idSeccion'");
    if ($checkSeccion->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Sección no encontrada",
            "Texto" => "La sección no existe en el sistema",
            "Tipo" => "error"
        ]);
        exit();
    }

    // Verificar existencia y estado del profesor
    $checkProfesor = mainModel::ejecutar_consulta_simple("SELECT estado_profesor FROM profesor WHERE id_profesor = '$idProfesor'");
    if ($checkProfesor->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Profesor no encontrado",
            "Texto" => "El profesor seleccionado no existe",
            "Tipo" => "error"
        ]);
        exit();
    }

    $estado = $checkProfesor->fetch(PDO::FETCH_ASSOC)['estado_profesor'];
    if ((int)$estado !== 1) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Profesor inactivo",
            "Texto" => "No se puede asignar un profesor que está inactivo",
            "Tipo" => "error"
        ]);
        exit();
    }

    // Ejecutar actualización desde el modelo
    //require_once "../modelos/seccionModelo.php";
    //$seccion = new seccionModelo();
    //$actualizar = $seccion->actualizar_profesor_seccion_modelo($idSeccion, $idProfesor);

$pdo = mainModel::conectar();

    // Actualizar profesor
    $stmtUpdate = $pdo->prepare("UPDATE seccion SET ci_profesor = :idProf, dia = :dia WHERE id_seccion = :idSec");
    $stmtUpdate->execute([
        ':idProf' => $idProfesor,
        ':idSec' => $idSeccion,
        ':dia' => $dia
    ]);


    if ($stmtUpdate->rowCount() > 0) {
        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Profesor actualizado",
            "Texto" => "El profesor de la sección fue modificado correctamente",
            "Tipo" => "success"
        ]);
    } else {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Sin cambios",
            "Texto" => "No se realizaron modificaciones en la sección (s:$idSeccion p:$idProfesor)",
            "Tipo" => "info"
        ]);
    }

}







public function archivar_seccion_controlador() {
    // Desencriptar y limpiar
    $id = mainModel::decryption($_POST['id_seccion_archivar']);
    $id2 = $_POST['id_seccion_archivar'];
    $id = $id2;

    // Verificar existencia de la sección
    $check_seccion = mainModel::ejecutar_consulta_simple("SELECT id_seccion FROM seccion WHERE id_seccion = '$id'");
    if ($check_seccion->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error inesperado",
            "Texto" => "La sección que intenta archivar no existe en el sistema $id - $id2",
            "Tipo" => "error"
        ]);
        exit();
    }
	
    $consultaCV = mainModel::ejecutar_consulta_simple("SELECT id_curso_vigente_fk FROM seccion WHERE id_seccion = '$id'");
	$idCursoVigente = $consultaCV->fetchColumn();
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

        // Archivar sección
        $ps_up_seccion = $pdo->prepare("UPDATE seccion SET archivar_seccion = 0 WHERE id_seccion = :id");
        $ps_up_seccion->execute([':id' => $id]);

        // Archivar evaluaciones
        $ps_get_evals = $pdo->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $pdo->prepare("UPDATE evaluacion SET archivar_evaluacion = 0 WHERE id_evaluacion = :idEval");

        // Archivar puntajes
        $ps_up_puntaje = $pdo->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 0 WHERE id_evaluacion = :idEval");

        // Actualizar observaciones en inscripciones
        $ps_up_insc = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Sección archivada')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%Sección archivada%'
        ");

        $ps_get_evals->execute([':idSec' => $id]);
        while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
            $idEval = (int)$eval['id_evaluacion'];
            $ps_up_eval->execute([':idEval' => $idEval]);
            $ps_up_puntaje->execute([':idEval' => $idEval]);
        }

        $ps_up_insc->execute([':idSec' => $id]);

        $pdo->commit();
//            "URL" => SERVERURL . "index.php?views=" . $carpeta . "/seccion-list"

        echo json_encode([
            "Alerta" => "redireccionar",
            "Titulo" => "Sección archivada",
            "Texto" => "La sección y sus registros relacionados han sido archivados exitosamente",
            "Tipo" => "success",
            "URL" => SERVERURL . "index.php?views=" . $carpeta . "/seccion-archived2&curso_vigente=" . $idCursoVigente
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al archivar sección",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}

//$a2

public function restaurar_seccion_controlador() {
    if (!isset($_POST['id_seccion_restore'])) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Error","Texto"=>"Falta el ID de la sección","Tipo"=>"error"]); exit;
    }

    //$id = mainModel::decryption($_POST['id_seccion_restore']);
    $id_raw = $_POST['id_seccion_restore'] ?? '';
    $id = trim($id_raw);
    
	$a2 = $_POST['a2'] ?? $_GET['a2'] ?? '';
    
    $cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
    
    //$id = mainModel::limpiar_cadena($id);
    //error_log(">>«". $id ."»");       // para ver si hay espacios
    //error_log("ctype_digit? ". (ctype_digit((string)$id) ? 'sí' : 'no'));
    header('Content-Type: application/json; charset=utf-8');

    if (!ctype_digit((string)$id) || (int)$id <= 0) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Entrada inválida","Texto"=>"ID inválido","Tipo"=>"error"]); exit;
    }

    $pdo = mainModel::conectar();

    // Verificar existencia y obtener curso vigente y profesor
    $stmt = $pdo->prepare("SELECT id_seccion, id_curso_vigente_fk, ci_profesor FROM seccion WHERE id_seccion = :id AND archivar_seccion = 0");
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() <= 0) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"No existe","Texto"=>"La sección no está archivada o no existe","Tipo"=>"error"]); exit;
    }

    $sec = $stmt->fetch(PDO::FETCH_ASSOC);
    $idCursoVigente = (int)$sec['id_curso_vigente_fk'];
    $idProfesor = (int)$sec['ci_profesor'];

    // Verificar curso vigente activo
    $stmtCurso = $pdo->prepare("SELECT estado_curso_vigente FROM curso_vigente WHERE id_curso_vigente = :id");
    $stmtCurso->execute([':id' => $idCursoVigente]);
    $cursoActivo = $stmtCurso->fetchColumn() == 1;

    // Verificar profesor activo
    $stmtProf = $pdo->prepare("SELECT estado_profesor FROM profesor WHERE id_profesor = :id");
    $stmtProf->execute([':id' => $idProfesor]);
    $profActivo = $stmtProf->fetchColumn() == 1;

    if (! $cursoActivo || ! $profActivo) {
        $omitted = [];
        if (! $cursoActivo) $omitted[] = "curso vigente";
        if (! $profActivo) $omitted[] = "profesor";
        echo json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"Restauración omitida",
            "Texto"=>"No se pudo restaurar la sección porque ".implode(" y ", $omitted)." está inactivo",
            "Tipo"=>"warning"
        ]);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Restaurar sección
        $ps_up_seccion = $pdo->prepare("UPDATE seccion SET archivar_seccion = 1 WHERE id_seccion = :id");
        $ps_up_seccion->execute([':id' => $id]);

        // Restaurar evaluaciones
        $ps_get_evals = $pdo->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $pdo->prepare("UPDATE evaluacion SET archivar_evaluacion = 1 WHERE id_evaluacion = :idEval");
        $ps_up_puntaje = $pdo->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 1 WHERE id_evaluacion = :idEval");

        $ps_get_evals->execute([':idSec' => $id]);
        $evals = $ps_get_evals->fetchAll(PDO::FETCH_ASSOC);

        $restored = ['seccion'=>1, 'evaluaciones'=>0, 'puntajes'=>0];
        $omitted = ['puntajes'=>0];
        $examples = [];

        foreach ($evals as $eval) {
            $idEval = (int)$eval['id_evaluacion'];
            $ps_up_eval->execute([':idEval' => $idEval]);
            $restored['evaluaciones'] += 1;

            // Verificar inscripción activa
            $ps_get_insc = $pdo->prepare("SELECT id_inscripcion, archivada FROM inscripcion WHERE id_inscripcion = (SELECT id_inscripcion FROM puntaje_estudiante WHERE id_evaluacion = :idEval LIMIT 1)");
            $ps_get_insc->execute([':idEval' => $idEval]);
            $insc = $ps_get_insc->fetch(PDO::FETCH_ASSOC);

            if (!$insc || (int)$insc['archivada'] !== 1) {
                $omitted['puntajes'] += 1;
                if (count($examples) < 10) $examples[] = $idEval;
                continue;
            }

            $ps_up_puntaje->execute([':idEval' => $idEval]);
            $restored['puntajes'] += $ps_up_puntaje->rowCount() ?: 1;
        }

        // Actualizar observaciones en inscripciones
        $ps_up_obs = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Sección restaurada')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%Sección restaurada%'
        ");
        $ps_up_obs->execute([':idSec' => $id]);

        $pdo->commit();
        
        
                // Recalcular nota final de la sección restaurada
		require_once "evaluacionControlador.php";
        $evalController = new evaluacionControlador();
		$evalController->actualizar_nota_final_seccion($id);
        
        
        $texto = "Sección restaurada correctamente.";
        $tipo = "success";
        if ($omitted['puntajes'] > 0) {
            $texto .= " Se omitieron {$omitted['puntajes']} puntaje(s) por inscripciones inactivas.";
            $tipo = "warning";
        }

        
        $redir = $carpeta . "/seccion-archived2&curso_vigente=" . $idCursoVigente;
       // $redir = ($a2 === '1') ?  $carpeta . "/seccion-archived" : $carpeta . "/seccion-list";
               echo json_encode([
            "Alerta" => "redireccionar",
            "Titulo" => "Sección restaurada",
            "Texto" => $texto,
            "Tipo" => $tipo,
            "URL" => SERVERURL . "index.php?views=" . $redir
        ]); 
        

        return;



    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Error","Texto"=>"No se pudo completar: ".$e->getMessage(),"Tipo"=>"error"]);
    }
}

//no probe lo de next page pero por lo menos lo que antes tenia aun funciona

    /*
            SELECT SQL_CALC_FOUND_ROWS
          cv.id_curso_vigente,
          c.nombre_curso,
          c.id_curso,
          ch.numero_cohorte,
          ch.anio_cohorte,
          ch.estado_cohorte,
          ch.tipo_cohorte,
          ch.id_cohorte,
          COUNT(DISTINCT i.id_inscripcion) AS total_inscripciones,
          COUNT(DISTINCT s.id_seccion) AS total_secciones
        FROM curso_vigente cv
        INNER JOIN cohorte ch 
          ON cv.id_cohorte_fk = ch.id_cohorte
        INNER JOIN curso c 
          ON cv.id_curso_fk = c.id_curso
        LEFT JOIN seccion s 
          ON s.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s.archivar_seccion = 1
        LEFT JOIN profesor p 
          ON s.ci_profesor = p.id_profesor 
          AND p.estado_profesor = 1
        LEFT JOIN inscripcion i 
          ON i.id_curso_vigente_fk = cv.id_curso_vigente 
          AND i.archivada = 1
        WHERE $where 
          AND c.estado_curso = 1
          AND cv.estado_curso_vigente = 1
        GROUP BY 
          cv.id_curso_vigente, 
          c.nombre_curso, 
          ch.numero_cohorte, 
          ch.anio_cohorte, 
          ch.estado_cohorte, 
          ch.tipo_cohorte

        $orden_sql
        LIMIT {$inicio}, {$registros}
    */
    















/*nos pondremos muy skibidi con esto

// ... (limpieza de variables, filtros, etc.)

// 1. Determinar modo
$modo_activo = ($es_activo == 1);

// 2. Construir las partes variables
if ($modo_activo) {
    // Condiciones para modo activo
    $where_base = "dp.archivar_periodo = 1 AND cv.estado_curso_vigente = 1";
    $join_seccion = "LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente AND s.archivar_seccion = 1";
    $subquery_inscripciones = "AND s2.archivar_seccion = 1";
    $total_secciones_sql = "COUNT(DISTINCT s.id_seccion) AS total_secciones";
} else {
    // Condiciones para modo archivado
    $where_base = "cv.estado_curso_vigente = 0";   // Sin filtrar dp.archivar_periodo
    $join_seccion = "";   // No necesitamos LEFT JOIN porque no contamos secciones activas
    $subquery_inscripciones = "AND s2.archivar_seccion = 0";
    $total_secciones_sql = "0 AS total_secciones";   // No hay secciones activas
}

// 3. Añadir filtros adicionales del usuario (como ya hacías)
$where_extra = "";
if ($filtro_id != "")           $where_extra .= " AND cv.id_curso_vigente = '$filtro_id'";
if ($filtro_nombre != "")       $where_extra .= " AND c.id_curso = '$filtro_nombre'";
if ($filtro_tipo != "")         $where_extra .= " AND ch.tipo_cohorte = '$filtro_tipo'";
if ($filtro_estado != "")       $where_extra .= " AND dp.estado_periodo = '$filtro_estado'";
if ($filtro_anio >= 25 && $filtro_anio <= 50) $where_extra .= " AND ch.anio_cohorte = '$filtro_anio'";
if ($filtro_profesor != "")     $where_extra .= " AND s.ci_profesor = '$filtro_profesor'";
if ($filtro_dia != "")          $where_extra .= " AND s.dia = '$filtro_dia'";
if ($filtro_id_seccion != "")   $where_extra .= " AND s.id_seccion = '$filtro_id_seccion'";
if ($filtro_id_cohorte != "")   $where_extra .= " AND ch.id_cohorte = '$filtro_id_cohorte'";

$where_completo = $where_base . $where_extra;

// 4. Construir la consulta final (única, con partes variables)
$consulta = "
SELECT SQL_CALC_FOUND_ROWS
    cv.id_curso_vigente,
    c.nombre_curso,
    c.id_curso,
    ch.numero_cohorte,
    ch.anio_cohorte,
    dp.estado_periodo,
    ch.tipo_cohorte,
    ch.id_cohorte,
    (
        SELECT COUNT(*)
        FROM inscripcion i
        INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion
        WHERE i.id_curso_vigente_fk = cv.id_curso_vigente
          AND i.archivada = 1
          $subquery_inscripciones
    ) AS total_inscripciones,
    $total_secciones_sql,
    (
        SELECT COUNT(*)
        FROM seccion s3
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
          AND s3.archivar_seccion = 0
    ) AS total_secciones_archivadas
FROM curso_vigente cv
INNER JOIN duracion_periodo dp ON cv.id_duracion_fk = dp.id_duracion
INNER JOIN cohorte ch ON ch.id_duracion_fk = dp.id_duracion
INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
$join_seccion
LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
WHERE c.estado_curso = 1
  AND $where_completo
GROUP BY
    cv.id_curso_vigente,
    c.nombre_curso,
    ch.numero_cohorte,
    ch.anio_cohorte,
    dp.estado_periodo,
    ch.tipo_cohorte,
    ch.id_cohorte,
    c.id_curso
$orden_sql
LIMIT {$inicio}, {$registros}
";


*/














    /*
    if($es_activo==0){
        //        COUNT(DISTINCT s.id_seccion) AS total_secciones,
        $consulta= "
            SELECT SQL_CALC_FOUND_ROWS
        cv.id_curso_vigente,
        c.nombre_curso,
        c.id_curso,
        ch.numero_cohorte,
        ch.anio_cohorte,
        dp.estado_periodo,
        ch.tipo_cohorte,
        ch.id_cohorte,
        (
            SELECT COUNT(*)
            FROM inscripcion i
            INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion
            WHERE i.id_curso_vigente_fk = cv.id_curso_vigente
              AND i.archivada = 1
        ) AS total_inscripciones,
        (
            SELECT COUNT(*)
            FROM seccion s3
            WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
            --  AND s3.archivar_seccion = 0
        ) AS total_secciones_archivadas
        FROM curso_vigente cv
        INNER JOIN duracion_periodo dp ON cv.id_duracion_fk = dp.id_duracion
        INNER JOIN cohorte ch ON ch.id_duracion_fk = dp.id_duracion
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente
        -- AND s.archivar_seccion = $es_activo
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        -- AND p.estado_profesor = 1
        -- WHERE c.estado_curso = 1
        -- AND cv.estado_curso_vigente = 1
        -- AND
        WHERE $where
        GROUP BY
            cv.id_curso_vigente,
            c.nombre_curso,
            ch.numero_cohorte,
            ch.anio_cohorte,
            dp.estado_periodo,
            ch.tipo_cohorte,
            ch.id_cohorte,
            c.id_curso
        $orden_sql
        LIMIT {$inicio}, {$registros}
    ";




    }
    else{

    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
        cv.id_curso_vigente,
        c.nombre_curso,
        c.id_curso,
        ch.numero_cohorte,
        ch.anio_cohorte,
        dp.estado_periodo,
        ch.tipo_cohorte,
        ch.id_cohorte,
        (
            SELECT COUNT(*)
            FROM inscripcion i
            INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion
            WHERE i.id_curso_vigente_fk = cv.id_curso_vigente
              AND i.archivada = 1
              AND s2.archivar_seccion = 1
        ) AS total_inscripciones,
        COUNT(DISTINCT s.id_seccion) AS total_secciones,
        (
            SELECT COUNT(*)
            FROM seccion s3
            WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
              AND s3.archivar_seccion = 0
        ) AS total_secciones_archivadas
    FROM curso_vigente cv
    INNER JOIN duracion_periodo dp ON cv.id_duracion_fk = dp.id_duracion
    INNER JOIN cohorte ch ON ch.id_duracion_fk = dp.id_duracion
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente AND s.archivar_seccion = $es_activo
    LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor 
    -- AND p.estado_profesor = 1
    WHERE c.estado_curso = 1
      AND cv.estado_curso_vigente = 1
      AND $where
    GROUP BY
        cv.id_curso_vigente,
        c.nombre_curso,
        ch.numero_cohorte,
        ch.anio_cohorte,
        dp.estado_periodo,
        ch.tipo_cohorte,
        ch.id_cohorte,
        c.id_curso
    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";
    }

*/



















/**
 * este es un paginador de periodos de cursos,tanto activos como archivados, y tanto de intensivos como cohortes,
 * @param int $pagina Pagina actual
 * @param int $registros Número de registros por página
 * @param int $es_activo Estado de los periodos (1 = activos, 0 = archivados)
 * @param string $periodo Tipo de periodo (cohorte o intensivo)
 */
public function paginador_secciones_controlador($pagina, $registros, $es_activo, $periodo) {
    // Limpieza y validación
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina    = ($pagina > 0) ? $pagina : 1;
    $inicio    = (($pagina * $registros) - $registros);
    $es_activo = ($es_activo == 1) ? 1 : 0;  // 1 = periodos activos, 0 = archivados

    // Filtros GET 
    $filtro_id           = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre       = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_tipo         = mainModel::limpiar_cadena($_GET['filtro_tipo'] ?? '');
    $filtro_estado       = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? '');
    $filtro_anio         = (int) ($_GET['filtro_anio'] ?? 0);
    $filtro_profesor     = mainModel::limpiar_cadena($_GET['filtro_profesor'] ?? '');
    $filtro_dia          = mainModel::limpiar_cadena($_GET['filtro_dia'] ?? '');
    $filtro_id_seccion   = mainModel::limpiar_cadena($_GET['filtro_id_seccion'] ?? '');
    $filtro_id_cohorte   = mainModel::limpiar_cadena($_GET['filtro_id_cohorte'] ?? '');
    $filtro_id_intensivo   = mainModel::limpiar_cadena($_GET['filtro_id_intensivo'] ?? '');
    $orden               = $_GET['orden'] ?? 'reciente';

    // Conexión
    $conexion = mainModel::conectar();

    //determiar los datos que se traen segun el tipo de periodo
    if($periodo === "cohorte"){
        $datos_contexto="ch.numero_cohorte,ch.anio_cohorte,ch.tipo_cohorte,
                    ch.id_cohorte,ch.cohorte_alterno,
                    ch.pago_art13, ch.fecha_pago_art13,
                    ch.pago_aranceles,ch.fecha_pago_aranceles,
                    ch.pago_inscripcion, ch.pago_modulo,
                    ch.fecha_pago_modulo1, ch.fecha_pago_modulo2, ch.fecha_pago_modulo3,";
        $join_contexto = "INNER JOIN cohorte ch ON ch.id_duracion_fk = dp.id_duracion";
        $group="ch.numero_cohorte,ch.anio_cohorte,ch.tipo_cohorte,ch.id_cohorte,";
    }

    else{//Intensivo
        $datos_contexto="pi.id_periodo_intensivo, pi.pago_inscripcion_int,pi.pago_encuentro_int,pi.pago_titulo_int,";
        $join_contexto = "INNER JOIN periodo_intensivo pi ON pi.id_duracion_fk = dp.id_duracion";
        $group="pi.id_periodo_intensivo,";
    }

    


    // Ordenamiento
    $orden_sql = match($orden) {
        "alfabetico" => "ORDER BY c.nombre_curso ASC",
        "inscritos"  => "ORDER BY total_inscripciones DESC",
        default      => "ORDER BY cv.id_curso_vigente ASC"
    };


    // Determinar modo
    $modo_activo = ($es_activo == 1);

    // Construir las partes variables
    if ($modo_activo) {
        // Condiciones para modo activo
        $where_base = "dp.archivar_periodo = 1 AND cv.estado_curso_vigente = 1";
        $join_seccion = "LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente AND s.archivar_seccion = 1";
        $subquery_inscripciones = "AND s2.archivar_seccion = 1";
        $total_secciones_sql = "COUNT(DISTINCT s.id_seccion) AS total_secciones";
    } else {
        // Condiciones para modo archivado
        $where_base = "cv.estado_curso_vigente = 0";   // Sin filtrar dp.archivar_periodo
        $join_seccion = "LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente AND s.archivar_seccion = 0";   // No se necesita LEFT JOIN porque no se cuenta secciones activas
        $subquery_inscripciones = "AND s2.archivar_seccion = 0";
        $total_secciones_sql = "0 AS total_secciones";   // No hay secciones activas
    }

    // Añadir filtros adicionales del usuario
    $where_extra = "";
    if ($filtro_id != "")           $where_extra .= " AND cv.id_curso_vigente = '$filtro_id'";
    if ($filtro_nombre != "")       $where_extra .= " AND c.id_curso = '$filtro_nombre'";
    if ($periodo == 'cohorte') {
        if ($filtro_tipo != "")     $where_extra .= " AND ch.tipo_cohorte = '$filtro_tipo'";
        if ($filtro_anio >= 25 && $filtro_anio <= 50) $where_extra .= " AND ch.anio_cohorte = '$filtro_anio'";
        if ($filtro_id_cohorte != "") $where_extra .= " AND ch.id_cohorte = '$filtro_id_cohorte'";
    }
    if ($filtro_estado != "")       $where_extra .= " AND dp.estado_periodo = '$filtro_estado'";
    if ($filtro_profesor != "")     $where_extra .= " AND s.ci_profesor = '$filtro_profesor'";
    if ($filtro_dia != "")          $where_extra .= " AND s.dia = '$filtro_dia'";
    if ($filtro_id_seccion != "")   $where_extra .= " AND s.id_seccion = '$filtro_id_seccion'";
    if ($periodo == 'intensivo') {
        if ($filtro_id_intensivo != "") $where_extra .= " AND pi.id_periodo_intensivo = '$filtro_id_intensivo'";
    }

    $where_completo = $where_base . $where_extra;

    // 4. Construir la consulta final (única, con partes variables)
    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
        cv.id_curso_vigente,
        c.nombre_curso,
        c.id_curso,
        dp.estado_periodo,
        dp.fecha_inicio,
        dp.fecha_fin,
        $datos_contexto
        (
            SELECT COUNT(*)
            FROM inscripcion i
            INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion
            WHERE i.id_curso_vigente_fk = cv.id_curso_vigente
            AND i.archivada = 1
            $subquery_inscripciones
        ) AS total_inscripciones,
        $total_secciones_sql,
        (
            SELECT COUNT(*)
            FROM seccion s3
            WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
            AND s3.archivar_seccion = 0
        ) AS total_secciones_archivadas
    FROM curso_vigente cv
    INNER JOIN duracion_periodo dp ON cv.id_duracion_fk = dp.id_duracion
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    $join_contexto
    $join_seccion
    LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
    WHERE c.estado_curso = 1
    AND $where_completo
    GROUP BY
        cv.id_curso_vigente,
        c.nombre_curso,
        $group
        dp.estado_periodo,
        c.id_curso
    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";



    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Determinar carpeta y vista según el cargo
    $cargo = $_SESSION['cargo_spm'] ?? 1;
    $carpetasPorCargo = [
        1 => "admin",
        2 => "Control de estudio",
        3 => "administrador de pagos",
        4 => "Supervisor de cursos",
        5 => "Estudiante"
    ];
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";
    $vistaCompleta = urlencode($carpeta) . '/seccion-list';


    //<th>Tipo</th>
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-bordered table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID Periodo-Curso</th>
                    <th>Curso</th>';

    if($periodo=='cohorte'){$tabla .= '<th>Cohorte</th>';}
    if($periodo=='intensivo'){$tabla .= '<th>Intensivo</th>';}
    
    $tabla .= '<th>Estado</th>';
    
    if ($cargo != 5) {$tabla .= '<th>Inscritos</th>';}

    if($es_activo == 1) {$tabla .= '<th>Secciones</th>';}

        //<th>Crear sección</th>
        //<th>Archivar</th>';
    if ($cargo == 1 || $cargo == 4) {$tabla .= '<th>Secciones archivadas</th><th>Acciones</th>';}

    $tabla .= '</tr></thead><tbody>';


    //empieza el ciclo
    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;
        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);

            if($es_activo == 1) {$secCount = (int) $row['total_secciones'];}            

            $secArchivadas = (int) $row['total_secciones_archivadas'];

            $tipo='';
            $tipo_contexto='';
            if($periodo=='cohorte'){
                if($row['tipo_cohorte'] == 'Extendido'){ $tipo = 'Ext.';}
                else if($row['tipo_cohorte'] == 'Técnico Profesional'){$tipo = 'Tec.';}
                else{$tipo = "Otro";}

                if($row['tipo_cohorte'] == 'Otro'){$tipo_contexto = $row['cohorte_alterno'];} 
                else{$tipo_contexto = $row['tipo_cohorte'];}
                $lista_contexto=[
                    "{$tipo_contexto}", "FI:{$row['fecha_inicio']} - FF:{$row['fecha_fin']}",
                    "Pago art. 13:{$row['pago_art13']} / {$row['fecha_pago_art13']}",
                    "Pago aranceles:{$row['pago_aranceles']} / {$row['fecha_pago_aranceles']}",
                    "Pago de Inscripción:{$row['pago_inscripcion']}", "Pago de Módulo: {$row['pago_modulo']}",
                    "Pago 1: {$row['fecha_pago_modulo1']}", "Pago 2: {$row['fecha_pago_modulo2']}", "Pago 3: {$row['fecha_pago_modulo3']}"
                ];

                $idcoencp=mainModel::encryption($row['id_cohorte']);
                $urlEdicion = SERVERURL . "index.php?views=cohorte2-update&cohorte_id_up={$idcoencp}";
                //$urlEdicion="' . SERVERURL . 'index.php?views=cohorte2-update&cohorte_id_up={$idcoencp}";
            }

            if($periodo=='intensivo'){
                $tipo_contexto = "FI:{$row['fecha_inicio']} - FF:{$row['fecha_fin']}";
                $lista_contexto=[
                    "{$tipo_contexto}",
                    "Pago de Inscripción:{$row['pago_inscripcion_int']}",
                    "Pago de Encuentro:{$row['pago_encuentro_int']}",
                    "Pago de Título:{$row['pago_titulo_int']}"
                ];

                $idpiencp=mainModel::encryption($row['id_periodo_intensivo']);
                $urlEdicion = SERVERURL . "index.php?views=cv-int-update&course_id_up={$idpiencp}";
               // $urlEdicion="' . SERVERURL . 'index.php?views=cv-int-update&course_id_up={$idpiencp}";
            }

            //datos del modal de contexto
            $modalData = [
                'titulo' => 'Información del período',
                'textos' => $lista_contexto,
                'link' => [
                    'text' => 'Modificar período',
                    'href' => $urlEdicion
                ]
            ];
            $modalDataJson = json_encode($modalData, JSON_UNESCAPED_UNICODE);
            $modalDataJsonEsc = htmlspecialchars($modalDataJson, ENT_QUOTES, 'UTF-8');   

            // Botón para secciones activas (usa clase 'ver-secciones')
            if($es_activo == 1) {
                if ($secCount > 0) {
                    $boton = "<button type='button' class='btn btn-sm btn-secondary m-1 ver-secciones'
                                    data-id='{$idCV}'
                                    data-nombre='{$nombreCurso}'
                                    data-vista='seccion-consult'
                                    data-carpeta='{$carpeta}'>
                                    Ver secciones ({$secCount})
                            </button>";
                } else {
                    $boton = "<span class='text-muted'>Sin secciones</span>";
                }
            }

            // Botón para secciones archivadas (usa clase 'ver-secciones-archivadas')
            if ($secArchivadas > 0) {
                $botonArchivadas = "<button type='button' class='btn btn-sm btn-warning m-1 ver-secciones-archivadas'
                                             data-id='{$idCV}'
                                             data-nombre='{$nombreCurso}'
                                             data-archivadas='1'
                                             data-vista='seccion-consult'
                                             data-carpeta='{$carpeta}'>
                                             Ver archivadas ({$secArchivadas})
                                      </button>";
            } else {
                $botonArchivadas = "<span class='text-muted'>Sin archivadas</span>";
            }

            //<td>{$row['tipo_cohorte']}</td>
            // Construcción de fila
            if($periodo=='cohorte'){
                $tabla .=   "<tr class='text-center'>
                            <td>{$idCV}</td>
                            <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                            <td>{$row['numero_cohorte']}.{$row['anio_cohorte']} {$tipo} / id:{$row['id_cohorte']}</td>
                            <td>{$row['estado_periodo']}</td>
                            ";
            }

            if($periodo=='intensivo'){
                $tabla .=   "<tr class='text-center'>
                            <td>{$idCV}</td>
                            <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                            <td>FI:{$row['fecha_inicio']} - FF:{$row['fecha_fin']} / id:{$row['id_periodo_intensivo']}</td>
                            <td>{$row['estado_periodo']}</td>
                            ";
            }

            if ($cargo != 5) {
                $tabla .= "<td>{$row['total_inscripciones']}</td>";
            }
//$tabla .= "<td>{$secCount}</td>";            

            if($es_activo == 1) {
                $tabla .= "<td>{$boton}</td>";
            }

            if ($cargo == 1 || $cargo == 4) {
                $tabla .= "
                    <td>{$botonArchivadas}</td>
                ";

                if($es_activo == 1) {

                    $tabla .= "
                        <td>
                            <div class='d-flex justify-content-center'>
                            <button type='button' class='btn btn-sm btn-info mr-1'
                                onclick='mostrarModalTexto(JSON.parse(`$modalDataJsonEsc`))'
                                title='Ver detalles del período'>
                                <i class='fas fa-eye'></i>
                            </button>
                    ";

                    if($periodo == 'intensivo') {
                        $tabla .="
                                <a href='" . SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-new&curso_vigente={$idCV}' class='btn btn-success'  title='Actualizar periodo no terminado'>
                                    <i class='fas fa-sync-alt'></i>
                                </a>
                        ";
                    }
                        
                    $tabla .= "
                                <a href='" . SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-new&curso_vigente={$idCV}' class='btn btn-sm btn-success' title='Crear sección'>
                                    <i class='fas fa-plus'></i>
                                </a>
                                <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='delete' data-entity='curso_vigente' autocomplete='off'>
                                    <input type='hidden' name='curso_vigente_id_archive' value='" . mainModel::encryption($idCV) . "'>
                                    <button type='submit' class='btn btn-warning btn-sm' title='Archivar periodo'>
                                        <i class='fas fa-archive'></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    ";
                }
                else{
                    $tabla .= "
                        <td>
                            <div class='d-flex justify-content-center'>
                                <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='delete' data-entity='curso_vigente' autocomplete='off'>
                                    <input type='hidden' name='curso_vigente_id_archive' value='" . mainModel::encryption($idCV) . "'>
                                    <button type='submit' class='btn btn-warning btn-sm' title='restaurar no terminado'>
                                        <i class='fas fa-history'></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    ";
                }
            }

            $tabla .= "</tr>";
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="10">No hay cursos vigentes registrados que coincidan con los filtros.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // PAGINACIÓN 
    if ($total > 0 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-center">Mostrando registros del <strong>' . $reg_inicio . '</strong> al <strong>' . $reg_final . '</strong> de un total de <strong>' . $total . '</strong></p>';

        //  'filtro_arc'   => $filtro_arc,
        $queryParams = http_build_query([
            'filtro_id'            => $filtro_id,
            'filtro_nombre'        => $filtro_nombre,
            'filtro_tipo'          => $filtro_tipo,
            'filtro_estado'        => $filtro_estado,
            'filtro_anio'          => $filtro_anio,
            'filtro_profesor'      => $filtro_profesor,
            'filtro_dia'           => $filtro_dia,
            'filtro_id_seccion'    => $filtro_id_seccion,
            'filtro_id_cohorte'    => $filtro_id_cohorte,
            'filtro_id_intensivo'  => $filtro_id_intensivo,
            'origen'               => $_GET['origen'] ?? '',
            'orden'                => $orden,
            'status'               => $es_activo   
        ]);

        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    // Se genera una sola vez y se añade al final de la tabla
    $tabla .= mainModel::generar_modal_secciones_js('seccion-consult');

    return $tabla;
}













































    
    
    
    
   
                    
                    
             
    
    
    
/*------------------------------------------------------------------------------------------------------------------------*



//------------------------------------------------------seccion consult
//evaluaciones de la vista de seccion
        public function paginador_evaluaciones_controlador($pagina, $registros, $id_seccion){
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $evaluaciones = periodoModelo::obtener_evaluaciones_modelo($id_seccion, $inicio, $registros)->fetchAll();
        $total = count($evaluaciones);
        $Npaginas = ceil($total / $registros);

$cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";            

            
    
            
        $tabla = '<div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark">
                    <tr class="text-center roboto-medium">
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Objetivo</th>
                        <th>Porcentaje</th>
                        <th>Fecha</th>
                        <th>Modificar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>';
            
            
     
            

        if ($total >= 1 && $pagina <= $Npaginas) {
            foreach ($evaluaciones as $eval) {
                $idEnc = mainModel::encryption($eval['id_evaluacion']);
                $tabla .= "<tr class='text-center'>
                    <td>{$eval['id_evaluacion']}</td>
                    <td>{$eval['tipo_evaluacion']}</td>
                    <td>{$eval['obj']}</td>
                    <td>{$eval['porcentaje']}%</td>
                    <td>".date("d/m/Y", strtotime($eval['fecha_evaluacion']))."</td>
                    <td>
	                    <a href='".SERVERURL."index.php?views=" . urlencode($carpeta) . "/evaluacion-update&evaluacion_id_up={$idEnc}' class='btn btn-success btn-sm'>
                            <i class='fas fa-edit'></i>
                        </a>
                    </td>
                                        <td>
                    <form class='FormularioAjax' action='".SERVERURL."ajax/seccionAjax.php' method='POST' data-form='delete2' data-entity='evaluacion' autocomplete='off'>
                        <input type='hidden' name='evaluacion_id_delete' value='".$idEnc."'>
                        <button type='submit' class='btn btn-danger btn-sm' title='Eliminar evaluación y sus calificaciones'>
                            <i class='fas fa-trash-alt'></i>
                        </button>
                    </form>
                </td>
                </tr>";
            }
        } else {
            $tabla .= "<tr class='text-center'><td colspan='7'>No hay evaluaciones registradas en esta sección.</td></tr>";
        }

        $tabla .= '</tbody></table></div>';

            //                        <a href='".SERVERURL."index.php?views=evaluacion-update&evaluacion_id_up={$idEnc}' class='btn btn-success btn-sm'>
            
            
        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando evaluaciones '.($inicio+1).' al '.($inicio+count($evaluaciones)).'</p>';
           
            
            
            $tabla .= mainModel::paginador_tablas(
                $pagina,
                $Npaginas,
                SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-view&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&eval_page=",
                7
            );
            
         //   $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, SERVERURL."index.php?views=seccion-view&curso_vigente=".$_GET['curso_vigente']."&seccion=".$_GET['seccion']."&eval_page=", 7);
        }

        return $tabla;
    }

    
    
    public function obtener_estudiantes_controlador($id_seccion){
        $id_seccion = mainModel::limpiar_cadena($id_seccion);
        return periodoModelo::obtener_estudiantes_modelo($id_seccion, 0, 0);
    }

    public function obtener_evaluaciones_controlador($id_seccion){
        $id_seccion = mainModel::limpiar_cadena($id_seccion);
        return periodoModelo::obtener_evaluaciones_modelo($id_seccion);
    }
    
    public function obtener_puntajes_controlador($id_seccion){
        $id_seccion = mainModel::limpiar_cadena($id_seccion);
        $estudiantes= periodoModelo::obtener_estudiantes_modelo($id_seccion, 0, 0);
        foreach ($estudiantes as $est) {  
            $puntajes = periodoModelo::obtener_puntajes_modelo($est['id_inscripcion']);
        
            $puntajes_map = [];
            foreach ($puntajes as $p) {
                $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
            }
        }
    }
    */
    
    
    
    public function paginador_estudiantes_con_puntajes_controlador___old($pagina, $registros, $id_seccion){
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

            
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

        // 1. Evaluaciones ordenadas por obj
        $evaluaciones = periodoModelo::obtener_evaluaciones_modelo($id_seccion)->fetchAll(PDO::FETCH_ASSOC);
        usort($evaluaciones, function($a, $b) {
            return version_compare($a['obj'], $b['obj']);
        });

        // 2. Estudiantes ordenados por apellido
        $estudiantes = periodoModelo::obtener_estudiantes_modelo($id_seccion, $inicio, $registros)->fetchAll(PDO::FETCH_ASSOC);

        $tabla = '<div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark">
                    <tr class="text-center roboto-medium">
                        <th>Cédula</th>
                        <th>Nombre</th>
                        <th>Apellido</th>';

        foreach ($evaluaciones as $eval) {
            $tabla .= "<th>{$eval['tipo_evaluacion']} (Obj {$eval['obj']})</th>";
        }

        $tabla .= "<th>Nota final</th></tr></thead><tbody>";

        foreach ($estudiantes as $est) {
            $tabla .= "<tr class='text-center'>
                <td>{$est['ci_estudiante']}</td>
                <td>{$est['nombre']}</td>
                <td>{$est['apellido']}</td>";

            // 3. Obtener puntajes del estudiante
            $puntajes = periodoModelo::obtener_puntajes_modelo($est['id_inscripcion'])->fetchAll(PDO::FETCH_ASSOC);
            $puntajes_map = [];
            foreach ($puntajes as $p) {
                $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
            }

            $notaFinal = 0;
            $totalPorcentaje = 0;

            foreach ($evaluaciones as $eval) {
                $idEval = $eval['id_evaluacion'];
                $porcentaje = (float)$eval['porcentaje'];
                $totalPorcentaje += $porcentaje;

                $valor = '-';
                if (isset($puntajes_map[$idEval])) {
                    $puntaje = $puntajes_map[$idEval];
                    $valor = $puntaje;
                    $puntaje = ($puntaje === 'NP') ? 0 : floatval($puntaje);
                    $notaFinal += $puntaje * $porcentaje / 100;
                }

                $tabla .= "<td>{$valor}</td>";
            }

            if ($totalPorcentaje == 100) {
                $notaRedondeada = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);
                $color = ($notaRedondeada > 10) ? 'text-success' : 'text-danger';
                $tabla .= "<td class='{$color}'><strong>{$notaRedondeada}</strong></td>";
            } else {
                $tabla .= "<td class='text-muted'>-</td>";
            }

            $tabla .= '</tr>';
        }

        $tabla .= '</tbody></table></div>';

        // 4. Paginador visual
        $total = periodoModelo::contar_estudiantes_modelo($id_seccion);
        $Npaginas = ceil($total / $registros);
        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando estudiantes '.($inicio+1).' al '.($inicio+count($estudiantes)).'</p>';

            
            $tabla .= mainModel::paginador_tablas(
                $pagina,
                $Npaginas,
                SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-view&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&est_page=",
                7
            );
            
            
            //   $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, SERVERURL."index.php?views=seccion-view&curso_vigente=".$_GET['curso_vigente']."&seccion=".$_GET['seccion']."&est_page=", 7);
        }

        return $tabla;
    }
        
        
        
    
        
    
        
    public function obtener_datos_seccion_controlador_para_ambos_casos($curso_vigente_id, $seccion_letra){
        $datos = periodoModelo::obtener_datos_seccion_para_ambos_casos_modelo($curso_vigente_id, $seccion_letra);
        return $datos;
    }    


        //datos de la seccion obtener_datos_seccion_controlador_para_ambos_casos
        //public function obtener_datos_seccion_controlador($curso_vigente_id, $seccion_letra){
        //  $datos = periodoModelo::obtener_datos_seccion_modelo($curso_vigente_id, $seccion_letra);
            //return $datos->fetch();
    // }
        //datos de profesor
    public function obtener_profesores_activos_controlador(){
        $consulta = periodoModelo::obtener_profesores_activos_modelo();
        return $consulta->fetchAll();
    }

    //--------------fin seccion consult


public function obtener_estudiantes_inscritos_controlador($id_seccion){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);
    return periodoModelo::obtener_estudiantes_inscritos_por_seccion_modelo($id_seccion);
}










//de evaluacion_new
/*
        public function obtener_estudiantes_inscritos_controlador($id_seccion){
            $id_seccion = mainModel::limpiar_cadena($id_seccion);
            $consulta = mainModel::conectar()->prepare("
                SELECT 
                    i.id_inscripcion,
                    e.ci_estudiante,
                    e.nombre,
                    e.apellido
                FROM inscripcion i
                INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
                WHERE i.id_seccion_fk = :id
                AND i.archivada = 1
                ORDER BY e.apellido ASC
            ");
            $consulta->bindParam(":id", $id_seccion);
            $consulta->execute();
            return $consulta->fetchAll();
        }
*/

//de seccion new
        public function listar_cursos_vigentes_por_cohorte_ajax($id_cohorte){
            $id_cohorte = mainModel::limpiar_cadena($id_cohorte);
            $consulta = mainModel::conectar()->prepare("
                SELECT 
                    cv.id_curso_vigente,
                    c.nombre_curso,
                    COUNT(s.id_seccion) AS total_secciones
                FROM curso_vigente cv
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente
                WHERE cv.id_cohorte_fk = :id AND cv.estado_curso_vigente = 1
                GROUP BY cv.id_curso_vigente
                ORDER BY c.nombre_curso ASC
            ");
            $consulta->bindParam(":id", $id_cohorte);
            $consulta->execute();
            return json_encode($consulta->fetchAll(PDO::FETCH_ASSOC));
        }






public function agregar_seccion_controlador(){
    $curso_vigente = mainModel::limpiar_cadena($_POST['id_curso_vigente']);
    $nombre = strtoupper(mainModel::limpiar_cadena($_POST['nombre_seccion']));
    $profesor = mainModel::limpiar_cadena($_POST['id_profesor_fk']);
    $dia = mainModel::limpiar_cadena($_POST['seccion_dia_reg']);
//c
    // Validación de campos obligatorios
    if($curso_vigente == "" || $nombre == "" || $profesor == "" || $dia == ""){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Campos incompletos",
            "Texto" => "Todos los campos son obligatorios",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    // Validar formato de nombre (una letra A-Z)
    if(!preg_match("/^[A-Z]{1}$/", $nombre)){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Formato inválido",
            "Texto" => "El nombre de la sección debe ser una sola letra (A-Z)",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    // Verificar duplicado
    $check = mainModel::ejecutar_consulta_simple("
        SELECT id_seccion FROM seccion 
        WHERE id_curso_vigente_fk = '$curso_vigente' AND nombre_seccion = '$nombre' AND archivar_seccion = 1
    ");
    if($check->rowCount() > 0){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Sección duplicada",
            "Texto" => "Ya existe una sección con ese nombre para el curso seleccionado",
            "Tipo" => "warning"
        ];
        echo json_encode($alerta);
        exit();
    }

    // Datos para el modelo
    $datos = [
        "CursoVigente" => $curso_vigente,
        "Nombre" => $nombre,
        "Profesor" => $profesor,
        "Dia" => $dia
    ];

    $guardar = seccionModelo::agregar_seccion_modelo($datos);

    if($guardar->rowCount() == 1){
        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Sección registrada",
            "Texto" => "La sección fue creada exitosamente",
            "Tipo" => "success"
        ];
    } else {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "No se pudo registrar la sección",
            "Tipo" => "error"
        ];
    }

    echo json_encode($alerta);
}

        
//@@@@@@@@@@@@@@@@@@@@@@@@este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@
public function obtener_datos_seccion_archivada_controlador_para_ambos_casos($id_curso_vigente, $letra_seccion) {
    $id_curso_vigente = mainModel::limpiar_cadena($id_curso_vigente);
    $letra_seccion = mainModel::limpiar_cadena($letra_seccion);

    $pdo = mainModel::conectar();

    // 🔹 Consulta principal
    $stmt = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.nombre_seccion,
            s.id_curso_vigente_fk AS id_curso_vigente,
            cv.id_cohorte_fk,
            c.nombre_curso,
            p.id_profesor,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :id_cv
          AND s.nombre_seccion = :letra
          AND s.archivar_seccion = 0
        LIMIT 1
    ");
    $stmt->bindValue(":id_cv", $id_curso_vigente, PDO::PARAM_INT);
    $stmt->bindValue(":letra", $letra_seccion, PDO::PARAM_STR);
    $stmt->execute();

    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        return [
            "id_seccion" => 0,
            "nombre_seccion" => $letra_seccion,
            "id_curso_vigente" => $id_curso_vigente,
            "nombre_curso" => "Curso no encontrado",
            "numero_cohorte" => "-",
            "anio_cohorte" => "-",
            "fecha_inicio_int" => null,
            "fecha_fin_int" => null,
            "id_profesor" => null,
            "nombre_profesor" => "Sin",
            "apellido_profesor" => "asignar"
        ];
    }

    // 🔹 Si tiene cohorte, buscar sus datos
    if (!empty($datos['id_cohorte_fk']) && $datos['id_cohorte_fk'] != 0) {
        $sqlCohorte = $pdo->prepare("
            SELECT numero_cohorte, anio_cohorte
            FROM cohorte
            WHERE id_cohorte = :id
            LIMIT 1
        ");
        $sqlCohorte->bindParam(":id", $datos['id_cohorte_fk'], PDO::PARAM_INT);
        $sqlCohorte->execute();
        $cohorte = $sqlCohorte->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = $cohorte['numero_cohorte'] ?? null;
        $datos['anio_cohorte'] = $cohorte['anio_cohorte'] ?? null;
        $datos['fecha_inicio_int'] = null;
        $datos['fecha_fin_int'] = null;
    } else {
        // 🔹 Si no tiene cohorte, buscar periodo intensivo
        $sqlIntensivo = $pdo->prepare("
            SELECT fecha_inicio_int, fecha_fin_int
            FROM periodo_intensivo
            WHERE id_curso_vigente_fk = :id
            LIMIT 1
        ");
        $sqlIntensivo->bindParam(":id", $datos['id_curso_vigente'], PDO::PARAM_INT);
        $sqlIntensivo->execute();
        $intensivo = $sqlIntensivo->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = null;
        $datos['anio_cohorte'] = null;
        $datos['fecha_inicio_int'] = $intensivo['fecha_inicio_int'] ?? null;
        $datos['fecha_fin_int'] = $intensivo['fecha_fin_int'] ?? null;
    }

    return $datos;
}
        
        
        
        
public function obtener_datos_evaluacion_controlador_para_ambos_casos($id_evaluacion){
    $id_evaluacion = mainModel::decryption($id_evaluacion);
    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $pdo = mainModel::conectar();

    // 🔹 Consulta principal
    $consulta = $pdo->prepare("
        SELECT 
            e.id_evaluacion,
            e.tipo_evaluacion AS tipo,
            e.obj,
            e.porcentaje,
            e.fecha_evaluacion AS fecha,
            s.id_seccion,
            s.dia,
            s.nombre_seccion AS seccion,
            cv.id_curso_vigente,
            cv.id_cohorte_fk,
            c.nombre_curso,
            p.nombre_profesor,
            p.apellido_profesor
        FROM evaluacion e
        INNER JOIN seccion s ON e.id_seccion = s.id_seccion
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE e.id_evaluacion = :id
        LIMIT 1
    ");
    $consulta->bindParam(":id", $id_evaluacion, PDO::PARAM_INT);
    $consulta->execute();

    if ($consulta->rowCount() !== 1) {
        return false;
    }

    $datos = $consulta->fetch(PDO::FETCH_ASSOC);

    // 🔹 Si tiene cohorte, buscar sus datos
    if (!empty($datos['id_cohorte_fk']) && $datos['id_cohorte_fk'] != 0) {
        $sqlCohorte = $pdo->prepare("
            SELECT numero_cohorte, anio_cohorte
            FROM cohorte
            WHERE id_cohorte = :id
            LIMIT 1
        ");
        $sqlCohorte->bindParam(":id", $datos['id_cohorte_fk']);
        $sqlCohorte->execute();
        $cohorte = $sqlCohorte->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = $cohorte['numero_cohorte'] ?? null;
        $datos['anio_cohorte'] = $cohorte['anio_cohorte'] ?? null;
        $datos['fecha_inicio_int'] = null;
        $datos['fecha_fin_int'] = null;
    } else {
        // Si no tiene cohorte, buscar periodo intensivo
        $sqlIntensivo = $pdo->prepare("
            SELECT fecha_inicio_int, fecha_fin_int
            FROM periodo_intensivo
            WHERE id_curso_vigente_fk = :id
            LIMIT 1
        ");
        $sqlIntensivo->bindParam(":id", $datos['id_curso_vigente']);
        $sqlIntensivo->execute();
        $intensivo = $sqlIntensivo->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = null;
        $datos['anio_cohorte'] = null;
        $datos['fecha_inicio_int'] = $intensivo['fecha_inicio_int'] ?? null;
        $datos['fecha_fin_int'] = $intensivo['fecha_fin_int'] ?? null;
    }

    return $datos;
}        
        
        
        
        
        
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//para evaluacion update 
public function obtener_datos_evaluacion_controlador($id_evaluacion){
    $id_evaluacion = mainModel::decryption($id_evaluacion);

    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $conexion = mainModel::conectar();

    $consulta = $conexion->prepare("
        SELECT 
            e.id_evaluacion,
            e.tipo_evaluacion AS tipo,
            e.obj,
            e.porcentaje,
            e.fecha_evaluacion AS fecha,
            s.id_seccion,
            s.nombre_seccion AS seccion,
            cv.id_curso_vigente,
            c.nombre_curso,
            ch.numero_cohorte,
            ch.anio_cohorte,
            p.nombre_profesor,
            p.apellido_profesor
        FROM evaluacion e
        INNER JOIN seccion s ON e.id_seccion = s.id_seccion
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE e.id_evaluacion = :id
        LIMIT 1
    ");

    $consulta->bindParam(":id", $id_evaluacion, PDO::PARAM_INT);
    $consulta->execute();

    if($consulta->rowCount() == 1){
        return $consulta->fetch();
    } else {
        return false;
    }
}


        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara si puedo@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
//tambien para evaluacion update
public function obtener_puntajes_evaluacion_controlador($id_evaluacion){
    // Desencriptar y limpiar
    $id_evaluacion = mainModel::decryption($id_evaluacion);
    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $conexion = mainModel::conectar();

$consulta = $conexion->prepare("
    SELECT 
        i.id_inscripcion,
        est.ci_estudiante,
        est.nombre,
        est.apellido,
        COALESCE(pe.puntaje, 'NP') AS puntaje
    FROM inscripcion i
    INNER JOIN estudiante est 
        ON i.id_estudiante_fk = est.id_estudiante
    LEFT JOIN puntaje_estudiante pe 
        ON pe.id_inscripcion = i.id_inscripcion 
        AND pe.id_evaluacion = :id
    WHERE 
        i.archivada = 1
        AND est.estado_estudiante = 1
        AND i.id_seccion_fk = (
            SELECT e.id_seccion
            FROM evaluacion e
            INNER JOIN seccion s ON e.id_seccion = s.id_seccion
            WHERE e.id_evaluacion = :id
              AND e.archivar_evaluacion = 1
              AND s.archivar_seccion = 1
              AND pe.archivar_puntaje = 1
        )
    ORDER BY est.apellido ASC, est.nombre ASC
");


    $consulta->bindParam(":id", $id_evaluacion, PDO::PARAM_INT);
    $consulta->execute();

    return $consulta->fetchAll();
}


        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara si puedo@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
public function obtener_puntajes_evaluacion_controlador_coninscritos($id_evaluacion){
    // Desencriptar y limpiar
    $id_evaluacion = mainModel::decryption($id_evaluacion);
    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $conexion = mainModel::conectar();

    // Obtener la sección asociada a la evaluación
    $consultaSeccion = $conexion->prepare("
        SELECT id_seccion
        FROM evaluacion
        WHERE id_evaluacion = :id
          AND archivar_evaluacion = 1
    ");
    $consultaSeccion->bindParam(":id", $id_evaluacion);
    $consultaSeccion->execute();
    $id_seccion = $consultaSeccion->fetchColumn();

    if (!$id_seccion) return [];

    // Traer todos los inscritos en la sección, con puntaje si existe
    $consulta = $conexion->prepare("
        SELECT 
            i.id_inscripcion,
            est.ci_estudiante,
            est.nombre,
            est.apellido,
            COALESCE((
                SELECT pe.puntaje
                FROM puntaje_estudiante pe
                WHERE pe.id_inscripcion = i.id_inscripcion
                  AND pe.id_evaluacion = :id
                  AND pe.archivar_puntaje = 1
                LIMIT 1
            ), 0) AS puntaje
        FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        WHERE i.id_seccion_fk = :id_seccion
          AND i.archivada = 1
          AND est.estado_estudiante = 1
        ORDER BY est.apellido ASC, est.nombre ASC
    ");
    $consulta->bindParam(":id", $id_evaluacion);
    $consulta->bindParam(":id_seccion", $id_seccion);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}
        
//obtener_estudiantes_inscritos_controlador

//paginador_estudiantes_con_puntajes_controlador
//agregar_evaluacion_controlador
//archivar_seccion_controlador
//Entrada 
//restaurar

        
        
        
        
        
        
        
        
        
    
    
        
        
        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

        
public function paginador_evaluacion_archivada_controlador($pagina, $registros, $id_seccion) {
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

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

    // Consulta directa sin modelo
    $stmt = $pdo->prepare("
        SELECT id_evaluacion, tipo_evaluacion, obj, porcentaje, fecha_evaluacion
        FROM evaluacion
        WHERE id_seccion = :id AND archivar_evaluacion = 0
        ORDER BY fecha_evaluacion ASC
        LIMIT :inicio, :registros
    ");
    $stmt->bindValue(":id", $id_seccion, PDO::PARAM_INT);
    $stmt->bindValue(":inicio", $inicio, PDO::PARAM_INT);
    $stmt->bindValue(":registros", $registros, PDO::PARAM_INT);
    $stmt->execute();
    $evaluaciones = $stmt->fetchAll();

    // Total sin paginación
    $totalStmt = $pdo->prepare("
        SELECT COUNT(*) FROM evaluacion
        WHERE id_seccion = :id AND archivar_evaluacion = 0
    ");
    $totalStmt->bindValue(":id", $id_seccion, PDO::PARAM_INT);
    $totalStmt->execute();
    $total = (int) $totalStmt->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-dark">
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Objetivo</th>
                    <th>Porcentaje</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        foreach ($evaluaciones as $eval) {
            $idEnc = mainModel::encryption($eval['id_evaluacion']);
            $tabla .= "<tr class='text-center'>
                <td>{$eval['id_evaluacion']}</td>
                <td>{$eval['tipo_evaluacion']}</td>
                <td>{$eval['obj']}</td>
                <td>{$eval['porcentaje']}%</td>
                <td>".date("d/m/Y", strtotime($eval['fecha_evaluacion']))."</td>

            </tr>";
        }
    } else {
        $tabla .= "<tr class='text-center'><td colspan='6'>No hay evaluaciones archivadas en esta sección.</td></tr>";
    }

    $tabla .= '</tbody></table></div>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando evaluaciones '.($inicio+1).' al '.($inicio+count($evaluaciones)).'</p>';
        
        $tabla .= mainModel::paginador_tablas(
            $pagina,
            $Npaginas,
            SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-arc-consult&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&eval_page=",
            7
        );
    }

    return $tabla;
}
        
        

        
        
        
        
        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    public function paginador_puntaje_archivado_controlador($pagina, $registros, $id_seccion) {
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

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

    // 1. Evaluaciones archivadas
    $stmtEval = $pdo->prepare("
        SELECT id_evaluacion, tipo_evaluacion, obj, porcentaje
        FROM evaluacion
        WHERE id_seccion = :id AND archivar_evaluacion = 0
        ORDER BY obj ASC
    ");
    $stmtEval->bindValue(":id", $id_seccion, PDO::PARAM_INT);
    $stmtEval->execute();
    $evaluaciones = $stmtEval->fetchAll(PDO::FETCH_ASSOC);

    usort($evaluaciones, fn($a, $b) => version_compare($a['obj'], $b['obj']));

    // 2. Estudiantes con inscripciones activas o archivadas
    $stmtEst = $pdo->prepare("
        SELECT est.id_estudiante, est.ci_estudiante, est.nombre, est.apellido, i.id_inscripcion
        FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        WHERE i.id_seccion_fk = :id_seccion AND est.estado_estudiante = 1
        ORDER BY est.apellido ASC, est.nombre ASC
        LIMIT :inicio, :registros
    ");
    $stmtEst->bindValue(":id_seccion", $id_seccion, PDO::PARAM_INT);
    $stmtEst->bindValue(":inicio", $inicio, PDO::PARAM_INT);
    $stmtEst->bindValue(":registros", $registros, PDO::PARAM_INT);
    $stmtEst->execute();
    $estudiantes = $stmtEst->fetchAll(PDO::FETCH_ASSOC);

    // 3. Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-dark">
                <tr class="text-center roboto-medium">
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Apellido</th>';

    foreach ($evaluaciones as $eval) {
        $tabla .= "<th>{$eval['tipo_evaluacion']} (Obj {$eval['obj']})</th>";
    }

    $tabla .= "<th>Nota final</th></tr></thead><tbody>";

    foreach ($estudiantes as $est) {
        $tabla .= "<tr class='text-center'>
            <td>{$est['ci_estudiante']}</td>
            <td>{$est['nombre']}</td>
            <td>{$est['apellido']}</td>";

        // 4. Puntajes archivados
        $stmtPuntaje = $pdo->prepare("
            SELECT pe.id_evaluacion, pe.puntaje
            FROM puntaje_estudiante pe
            INNER JOIN evaluacion ev ON pe.id_evaluacion = ev.id_evaluacion
            WHERE pe.id_inscripcion = :id_inscripcion
              AND pe.archivar_puntaje = 0
              AND ev.archivar_evaluacion = 0
        ");
        $stmtPuntaje->bindValue(":id_inscripcion", $est['id_inscripcion'], PDO::PARAM_INT);
        $stmtPuntaje->execute();
        $puntajes = $stmtPuntaje->fetchAll(PDO::FETCH_ASSOC);

        $puntajes_map = [];
        foreach ($puntajes as $p) {
            $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
        }

        $notaFinal = 0;
        $totalPorcentaje = 0;

        foreach ($evaluaciones as $eval) {
            $idEval = $eval['id_evaluacion'];
            $porcentaje = (float)$eval['porcentaje'];
            $totalPorcentaje += $porcentaje;

            $valor = '-';
            if (isset($puntajes_map[$idEval])) {
                $puntaje = $puntajes_map[$idEval];
                $valor = $puntaje;
                $puntaje = ($puntaje === 'NP') ? 0 : floatval($puntaje);
                $notaFinal += $puntaje * $porcentaje / 100;
            }

            $tabla .= "<td>{$valor}</td>";
        }

        if ($totalPorcentaje == 100) {
            $notaRedondeada = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);
            $color = ($notaRedondeada > 10) ? 'text-success' : 'text-danger';
            $tabla .= "<td class='{$color}'><strong>{$notaRedondeada}</strong></td>";
        } else {
            $tabla .= "<td class='text-muted'>-</td>";
        }

        $tabla .= '</tr>';
    }

    $tabla .= '</tbody></table></div>';

    // 5. Paginador visual
    $stmtTotal = $pdo->prepare("
        SELECT COUNT(*) FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        WHERE i.id_seccion_fk = :id_seccion AND est.estado_estudiante = 1
    ");
    $stmtTotal->bindValue(":id_seccion", $id_seccion, PDO::PARAM_INT);
    $stmtTotal->execute();
    $total = (int) $stmtTotal->fetchColumn();
    $Npaginas = ceil($total / $registros);

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando estudiantes '.($inicio+1).' al '.($inicio+count($estudiantes)).'</p>';
      
        $tabla .= mainModel::paginador_tablas(
            $pagina,
            $Npaginas,
            SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-arc-consult&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&est_page=",
            7
        );
        
        
        
        
        //  $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, SERVERURL."index.php?views=seccion-arc-consult&curso_vigente=".$_GET['curso_vigente']."&seccion=".$_GET['seccion']."&est_page=", 7);
    }

    return $tabla;
}
    
    
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    public function obtener_datos_seccion_archivada_controlador($id_curso_vigente, $letra_seccion) {
    $id_curso_vigente = mainModel::limpiar_cadena($id_curso_vigente);
    $letra_seccion = mainModel::limpiar_cadena($letra_seccion);

    $pdo = mainModel::conectar();

    $stmt = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.nombre_seccion,
            s.id_curso_vigente_fk AS id_curso_vigente,
            c.nombre_curso,
            ch.numero_cohorte,
            ch.anio_cohorte,
            p.id_profesor,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :id_cv
          AND s.nombre_seccion = :letra
          AND s.archivar_seccion = 0
        LIMIT 1
    ");
    $stmt->bindValue(":id_cv", $id_curso_vigente, PDO::PARAM_INT);
    $stmt->bindValue(":letra", $letra_seccion, PDO::PARAM_STR);
    $stmt->execute();

    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        return [
            "id_seccion" => 0,
            "nombre_seccion" => $letra_seccion,
            "id_curso_vigente" => $id_curso_vigente,
            "nombre_curso" => "Curso no encontrado",
            "numero_cohorte" => "-",
            "anio_cohorte" => "-",
            "id_profesor" => null,
            "nombre_profesor" => "Sin",
            "apellido_profesor" => "asignar"
        ];
    }

    return $datos;
}
    
    
    
    
    
    
    
    
    
    
    
    
    
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    public static function exsel_prueba_1($id_curso_vigente, $nombre_seccion) {
    $pdo = mainModel::conectar();
    $id_curso_vigente = mainModel::limpiar_cadena($id_curso_vigente);
    $nombre_seccion = mainModel::limpiar_cadena($nombre_seccion);

    // 1. Buscar la sección con todos los metadatos
    $sec = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.nombre_seccion,
            p.nombre_profesor,
            p.apellido_profesor,
            c.nombre_curso,
            coh.numero_cohorte,
            coh.anio_cohorte,
            coh.tipo_cohorte
        FROM seccion s
        INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        WHERE s.id_curso_vigente_fk = :cv AND s.nombre_seccion = :nombre
        
        
    ");
    $sec->bindParam(":cv", $id_curso_vigente, PDO::PARAM_INT);
    $sec->bindParam(":nombre", $nombre_seccion, PDO::PARAM_STR);
    $sec->execute();
    $datos_seccion = $sec->fetch(PDO::FETCH_ASSOC);

    if (!$datos_seccion) return null;

    $id_seccion = $datos_seccion['id_seccion'];

    // 2. Evaluaciones
    $evaluaciones = seccionModelo::obtener_evaluaciones_modelo($id_seccion)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Estudiantes
    $estudiantes = seccionModelo::obtener_estudiantes_modelo($id_seccion, 0, 10000)->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [
        'seccion' => $datos_seccion,
        'evaluaciones' => $evaluaciones,
        'estudiantes' => []
    ];

    foreach ($estudiantes as $est) {
        $puntajes = seccionModelo::obtener_puntajes_modelo($est['id_inscripcion'])->fetchAll(PDO::FETCH_ASSOC);
        $puntajes_map = [];
        foreach ($puntajes as $p) {
            $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
        }

        $detalle = [
            'id_estudiante' => $est['id_estudiante'],
            'ci' => $est['ci_estudiante'],
            'nombre' => $est['nombre'],
            'apellido' => $est['apellido'],
            'evaluaciones' => [],
            'suma_porcentajes' => 0,
            'nota_final' => 0,
            'nota_redondeada' => '-'
        ];

        $notaFinal = 0;
        foreach ($evaluaciones as $eval) {
            $idEval = $eval['id_evaluacion'];
            $porcentaje = (float)$eval['porcentaje'];
            $puntaje = isset($puntajes_map[$idEval]) ? $puntajes_map[$idEval] : 'NP';
            $puntaje_valor = ($puntaje === 'NP') ? 0 : floatval($puntaje);

            $puntaje_maximo = 20; // Puedes ajustar si lo tienes en BD
            $porcentaje_obtenido = ($puntaje_maximo > 0) ? ($puntaje_valor / $puntaje_maximo) * $porcentaje : 0;

            $notaFinal += $porcentaje_obtenido;

            $detalle['evaluaciones'][] = [
                'id_evaluacion' => $idEval,
                'tipo' => $eval['tipo_evaluacion'],
                'obj' => $eval['obj'],
                'porcentaje' => $porcentaje,
                'puntaje' => $puntaje,
                'porcentaje_obtenido' => round($porcentaje_obtenido, 2)
            ];
        }

        $detalle['suma_porcentajes'] = round($notaFinal, 2);
        $detalle['nota_final'] = round($notaFinal, 2);
        $detalle['nota_redondeada'] = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);

        $resultado['estudiantes'][] = $detalle;
    }
var_dump($resultado);
exit();
        
        
    return $resultado;
}
    
    
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        public function paginador_seccion_archivadas_controlador22($pagina, $registros, $curso_vigente_id){
    $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
    $inicio = ($pagina - 1) * $registros;

    $conexion = mainModel::conectar();

    // Traer secciones archivadas del curso vigente con nombre del profesor
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            s.id_seccion, 
            s.dia,
            s.nombre_seccion, 
            s.id_curso_vigente_fk,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.archivar_seccion = 0 AND s.id_curso_vigente_fk = :curso_vigente
        ORDER BY s.nombre_seccion ASC
        LIMIT {$inicio}, {$registros}
    ";

    $stmt = $conexion->prepare($consulta);
    $stmt->execute([':curso_vigente' => $curso_vigente_id]);
    $datos = $stmt->fetchAll();

    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-bordered table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Día</th>
                    <th>Profesor</th>
                    <th>Inscritos</th>
                    <th>Evaluaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1) {
        foreach ($datos as $row) {
            $id = $row['id_seccion'];
            $letra = htmlspecialchars($row['nombre_seccion']);
            $dia = htmlspecialchars($row['dia']);

            // 🔹 Contar inscritos
            $stmtInscritos = $conexion->prepare("SELECT COUNT(*) FROM inscripcion WHERE id_seccion_fk = :id");
            $stmtInscritos->execute([':id' => $id]);
            $inscritos = (int) $stmtInscritos->fetchColumn();

            // 🔹 Contar evaluaciones
            $stmtEvaluaciones = $conexion->prepare("SELECT COUNT(*) FROM evaluacion WHERE id_seccion = :id");
            $stmtEvaluaciones->execute([':id' => $id]);
            $evaluaciones = (int) $stmtEvaluaciones->fetchColumn();

            // 🔹 Nombre del profesor
            $profesor = "<em>Sin asignar</em>";
            if (!empty($row['nombre_profesor']) || !empty($row['apellido_profesor'])) {
                $profesor = htmlspecialchars(trim($row['nombre_profesor'] . ' ' . $row['apellido_profesor']));
            }

            $link = SERVERURL . "index.php?views=seccion-arc-consult&curso_vigente=" . urlencode($curso_vigente_id) . "&seccion=" . urlencode($letra);

            $tabla .= "<tr class='text-center'>
                <td>{$id}</td>
                <td>{$letra}</td>
                <td>{$dia}</td>
                <td>{$profesor}</td>
                <td>{$inscritos}</td>
                <td>{$evaluaciones}</td>
                <td>
                    <a href='{$link}' class='btn btn-sm btn-light' style='color:black;'>Ver sección</a>
                </td>
            </tr>";
        }
    } else {
        $tabla .= '<tr class="text-center"><td colspan="6">No hay secciones archivadas registradas para este curso.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador simple
    if ($total > 0 && $pagina <= $Npaginas) {
        $url = SERVERURL . "index.php?views=seccion-archived2&curso_vigente=" . $curso_vigente_id . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 5);
    }

    return $tabla;
}
        
        
        
        
        
        
        
        
        
        
    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara si puedo@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        public function paginador_secciones_evaluadas($pagina, $registros) {
            $pagina    = (int) mainModel::limpiar_cadena($pagina);
            $registros = (int) mainModel::limpiar_cadena($registros);
            $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

            $conexion = mainModel::conectar();

            // Consulta principal con filtros y cálculo de porcentaje
            $consulta = "
                SELECT SQL_CALC_FOUND_ROWS
                    cv.id_curso_vigente AS id_periodo_curso,
                    c.nombre_curso,
                    CONCAT(coh.numero_cohorte, '.', RIGHT(coh.anio_cohorte, 2)) AS cohorte,
                    coh.tipo_cohorte,
                    coh.estado_cohorte,
                    CONCAT(s.nombre_seccion, ' / id: ', s.id_seccion) AS seccion,
                    IFNULL(SUM(e.porcentaje), 0) AS suma_porcentajes
                FROM seccion s
                INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
                LEFT JOIN evaluacion e ON e.id_seccion = s.id_seccion AND e.archivar_evaluacion = 1
                WHERE
                    s.archivar_seccion = 1
                    AND cv.estado_curso_vigente = 1
                    AND coh.archivar_cohorte = 1
                    AND coh.estado_cohorte IN ('Vigente', 'Terminado')
                    AND coh.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY s.id_seccion
                HAVING suma_porcentajes = 100
                ORDER BY coh.fecha_inicio DESC
                LIMIT $inicio, $registros
            ";

            $datos = $conexion->query($consulta)->fetchAll();
            $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
            $Npaginas = ceil($total / $registros);

            // Construcción de tabla
            $tabla = '<div class="table-responsive">
                <table class="table table-dark table-sm">
                    <thead>
                        <tr class="text-center roboto-medium">
                            <th>Id_periodo_curso</th>
                            <th>Nombre del curso</th>
                            <th>Cohorte</th>
                            <th>Tipo cohorte</th>
                            <th>Estado cohorte</th>
                            <th>Sección</th>
                            <th>Evaluado al 100%</th>
                        </tr>
                    </thead>
                    <tbody>';

            if ($total >= 1 && $pagina <= $Npaginas) {
                $reg_inicio = $inicio + 1;

                foreach ($datos as $row) {
                    $tabla .= '<tr class="text-center">
                        <td>' . $row['id_periodo_curso'] . '</td>
                        <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                        <td>' . htmlspecialchars($row['cohorte']) . '</td>
                        <td>' . htmlspecialchars($row['tipo_cohorte']) . '</td>
                        <td>' . htmlspecialchars($row['estado_cohorte']) . '</td>
                        <td>' . htmlspecialchars($row['seccion']) . '</td>
                        <td><span class="badge badge-success">Sí</span></td>
                    </tr>';
                }

                $reg_final = $reg_inicio + count($datos) - 1;
            } else {
                $tabla .= '<tr class="text-center"><td colspan="7">No hay registros en el sistema</td></tr>';
            }

            $tabla .= '</tbody></table></div>';

            // Paginador visual
            if ($total >= 1 && $pagina <= $Npaginas) {
                $tabla .= '<p class="text-right">Mostrando secciones ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
                $urlCompleta = SERVERURL . "index.php?views=admin/secciones-evaluadas&page=";
                $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
            }

            return $tabla;
        }
        
        
        
        
        

        
        
        
        
        
      
        

        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

        public static function cohortes_para_excel_select() {
    $pdo = mainModel::conectar();

    // Buscar todos los cohortes archivados dentro de los últimos 13 meses
    $stmt = $pdo->prepare("
        SELECT id_cohorte, numero_cohorte, anio_cohorte, tipo_cohorte
        FROM cohorte
        WHERE archivar_cohorte = 1
          AND DATE_ADD(fecha_inicio, INTERVAL 13 MONTH) >= CURDATE()
        ORDER BY anio_cohorte DESC, numero_cohorte DESC
    ");
    $stmt->execute();
    $cohortes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cohortes_validos = [];

    foreach ($cohortes as $cohorte) {
        $id_cohorte = $cohorte['id_cohorte'];

        // Buscar todas las secciones activas del cohorte
        $secciones_stmt = $pdo->prepare("
            SELECT s.id_seccion
            FROM curso_vigente cv
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            INNER JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente
            WHERE cv.id_cohorte_fk = :id
              AND cv.estado_curso_vigente = 1
              AND c.estado_curso = 1
              AND s.archivar_seccion = 1
        ");
        $secciones_stmt->bindParam(":id", $id_cohorte, PDO::PARAM_INT);
        $secciones_stmt->execute();
        $secciones = $secciones_stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($secciones)) continue;

        $todas_completas = true;

        foreach ($secciones as $id_seccion) {
            $eval_stmt = $pdo->prepare("
                SELECT ROUND(SUM(porcentaje), 2) as total
                FROM evaluacion
                WHERE id_seccion = :id AND archivar_evaluacion = 1
            ");
            $eval_stmt->bindParam(":id", $id_seccion, PDO::PARAM_INT);
            $eval_stmt->execute();
            $total = (float) $eval_stmt->fetchColumn();

            if ($total !== 100.00) {
                $todas_completas = false;
                break;
            }
        }

        if ($todas_completas) {
            $cohortes_validos[] = $cohorte;
        }
    }

    return $cohortes_validos;
}
        
        
        
        
        
        
        
        
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        public function paginador_secciones_excel_controlador($pagina, $registros) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
  $vistaCompleta = urlencode($carpeta) . '/cal-excel';
      
            
    $conexion = mainModel::conectar();
    // Filtros
    $filtro_anio     = mainModel::limpiar_cadena($_GET['filtro_anio']     ?? "");
    $filtro_tipo     = mainModel::limpiar_cadena($_GET['filtro_tipo']     ?? "");
    $filtro_id       = mainModel::limpiar_cadena($_GET['filtro_id']       ?? "");
    $filtro_evaluado = mainModel::limpiar_cadena($_GET['filtro_evaluado'] ?? "");
    $filtro_estado   = mainModel::limpiar_cadena($_GET['filtro_estado']   ?? "");

    // WHERE dinámico
    $where = "s.archivar_seccion = 1 AND cv.estado_curso_vigente = 1 AND c.estado_curso = 1 AND coh.archivar_cohorte = 1 AND DATE_ADD(coh.fecha_inicio, INTERVAL 13 MONTH) >= CURDATE()";
    if ($filtro_anio != "")     $where .= " AND coh.anio_cohorte = '$filtro_anio'";
    if ($filtro_tipo != "")     $where .= " AND coh.tipo_cohorte = '$filtro_tipo'";
    if ($filtro_id != "")       $where .= " AND coh.id_cohorte = '$filtro_id'";
    if ($filtro_estado != "")   $where .= " AND coh.estado_cohorte = '$filtro_estado'";

    // Total de secciones
    $total_stmt = $conexion->query("
        SELECT COUNT(*) FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
        WHERE $where
    ");
    $total = (int) $total_stmt->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Secciones paginadas
    $secciones_stmt = $conexion->prepare("
        SELECT
            coh.id_cohorte,
            coh.numero_cohorte,
            coh.anio_cohorte,
            coh.tipo_cohorte,
            coh.estado_cohorte,
            c.nombre_curso,
            cv.id_curso_vigente,
            c.id_curso,
            s.id_seccion,
            s.nombre_seccion
        FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
        WHERE $where
        ORDER BY coh.fecha_inicio DESC
        LIMIT $inicio, $registros
    ");
    $secciones_stmt->execute();
    $secciones = $secciones_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Cohorte</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Curso</th>
                    <th>Id Periodo Curso</th>
                    <th>Sección</th>
                    <th>Evaluado al 100%</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($secciones as $sec) {
        $id_seccion = $sec['id_seccion'];

        // Verificar evaluación
        $eval_stmt = $conexion->prepare("
            SELECT SUM(porcentaje) as total
            FROM evaluacion
            WHERE id_seccion = :id AND archivar_evaluacion = 1
        ");
        $eval_stmt->bindParam(":id", $id_seccion, PDO::PARAM_INT);
        $eval_stmt->execute();
        $total_eval = (float) $eval_stmt->fetchColumn();
        $evaluado = (abs($total_eval - 100) < 0.01) ? "Sí" : "No";

        // Filtro evaluado
        if ($filtro_evaluado !== "") {
            if (($filtro_evaluado == "1" && $evaluado != "Sí") || ($filtro_evaluado == "0" && $evaluado != "No")) {
                continue;
            }
        }

        $cohorte_str  = $sec['numero_cohorte'] . '.' . substr($sec['anio_cohorte'], -2) . " / id: " . $sec['id_cohorte'];
        $curso_str    = $sec['nombre_curso'] . " / id:" . $sec['id_curso'];
        $seccion_str  = $sec['nombre_seccion'] . " / id:" . $sec['id_seccion'];

        $tabla .= '<tr class="text-center">
            <td>' . $cohorte_str . '</td>
            <td>' . $sec['tipo_cohorte'] . '</td>
            <td>' . $sec['estado_cohorte'] . '</td>
            <td>' . $curso_str . '</td>
            <td>' . $sec['id_curso_vigente'] . '</td>
            <td>' . $seccion_str . '</td>
            <td>' . $evaluado . '</td>
        </tr>';
    }

    if ($total == 0) {
        $tabla .= '<tr class="text-center"><td colspan="7">No hay registros disponibles</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando secciones ' . ($inicio + 1) . ' al ' . ($inicio + count($secciones)) . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'filtro_anio'     => $filtro_anio,
            'filtro_tipo'     => $filtro_tipo,
            'filtro_id'       => $filtro_id,
            'filtro_evaluado' => $filtro_evaluado,
            'filtro_estado'   => $filtro_estado
        ]);
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
//        $urlCompleta = SERVERURL . "index.php?views=cal-excel&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}
        
        
        
        
                //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

        public function paginador_secciones_intensivos_controlador($pagina, $registros) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<script>console.log('[v0-intensivos] Iniciando paginador_secciones_intensivos_controlador');</script>";
    
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina    = ($pagina > 0) ? $pagina : 1;
    $inicio    = (($pagina * $registros) - $registros);

    $cargo = $_SESSION['cargo_spm'] ?? 1;

    $carpetasPorCargo = [
        1 => "admin",
        2 => "Control de estudio",
        3 => "administrador de pagos",
        4 => "Supervisor de cursos",
        5 => "Estudiante"
    ];
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";
    $vistaCompleta = urlencode($carpeta) . '/cv-int-list';
               
    echo "<script>console.log('[v0-intensivos] Pagina: {$pagina}, Registros: {$registros}, Inicio: {$inicio}');</script>";

    // Filtros
    $filtro_id           = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre       = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_estado       = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? '');
    $filtro_anio         = (int) ($_GET['filtro_anio'] ?? 0);
    $filtro_profesor     = mainModel::limpiar_cadena($_GET['filtro_profesor'] ?? '');
    $filtro_dia     = mainModel::limpiar_cadena($_GET['filtro_dia'] ?? '');
    $filtro_id_seccion   = mainModel::limpiar_cadena($_GET['filtro_id_seccion'] ?? '');
    $orden               = $_GET['orden'] ?? 'reciente';

    echo "<script>console.log('[v0-intensivos] Filtros aplicados:', " . json_encode([
        'id' => $filtro_id,
        'nombre' => $filtro_nombre,
        'estado' => $filtro_estado,
        'anio' => $filtro_anio,
        'orden' => $orden
    ]) . ");</script>";

    try {
        $conexion = mainModel::conectar();
        echo "<script>console.log('[v0-intensivos] Conexión a BD exitosa');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('[v0-intensivos] Error de conexión: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $where = "cv.id_cohorte_fk IS NULL AND c.tipo_curso = 'Intensivo'";
    
    if ($filtro_id != "")           $where .= " AND cv.id_curso_vigente = '$filtro_id'";
    if ($filtro_nombre != "")       $where .= " AND c.id_curso = '$filtro_nombre'";
    if ($filtro_estado != "")       $where .= " AND pi.estado_periodo = '$filtro_estado'";
    if ($filtro_anio >= 2025)       $where .= " AND YEAR(pi.fecha_inicio_int) = '$filtro_anio'";
    if ($filtro_profesor != "")     $where .= " AND s.ci_profesor = '$filtro_profesor'";
    if ($filtro_dia != "")     $where .= " AND s.dia = '$filtro_dia'";
    if ($filtro_id_seccion != "")   $where .= " AND s.id_seccion = '$filtro_id_seccion'";
    
    $filtro_arc = $_GET['filtro_arc'] ?? 'no';
    if ($filtro_arc === 'si') {
        $where .= " AND EXISTS (
            SELECT 1 FROM seccion s3
            WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
              AND s3.archivar_seccion = 0
        )";
    }

    echo "<script>console.log('[v0-intensivos] WHERE construido: " . addslashes($where) . "');</script>";

    // Ordenamiento
    $orden_sql = match($orden) {
        "alfabetico" => "ORDER BY c.nombre_curso ASC",
        "inscritos"  => "ORDER BY total_inscripciones DESC",
        default      => "ORDER BY pi.fecha_inicio_int DESC"
    };

    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
      cv.id_curso_vigente,
      c.nombre_curso,
      c.id_curso,
      pi.fecha_inicio_int,
      pi.fecha_fin_int,
      pi.pago_inscripcion_int,
      pi.pago_encuentro_int,
      pi.pago_titulo_int,
      pi.estado_periodo,
      pi.id_periodo_intensivo,

      (
        SELECT COUNT(*) 
        FROM inscripcion i 
        INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion 
        WHERE i.id_curso_vigente_fk = cv.id_curso_vigente 
          AND i.archivada = 1 
          AND s2.archivar_seccion = 1
      ) AS total_inscripciones,

      COUNT(DISTINCT s.id_seccion) AS total_secciones,

      (
        SELECT COUNT(*) 
        FROM seccion s3 
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s3.archivar_seccion = 0
      ) AS total_secciones_archivadas

    FROM curso_vigente cv
    INNER JOIN curso c 
      ON cv.id_curso_fk = c.id_curso
    INNER JOIN periodo_intensivo pi 
      ON pi.id_curso_vigente_fk = cv.id_curso_vigente

    LEFT JOIN seccion s 
      ON s.id_curso_vigente_fk = cv.id_curso_vigente 
      AND s.archivar_seccion = 1

    LEFT JOIN profesor p 
      ON s.ci_profesor = p.id_profesor 
      AND p.estado_profesor = 1

    WHERE c.estado_curso = 1
      AND cv.estado_curso_vigente = 1 
      AND $where
      
    GROUP BY 
      cv.id_curso_vigente, 
      c.nombre_curso,
      c.id_curso,
      pi.fecha_inicio_int,
      pi.fecha_fin_int,
      pi.pago_inscripcion_int,
      pi.pago_encuentro_int,
      pi.pago_titulo_int,
      pi.estado_periodo,
      pi.id_periodo_intensivo

    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";

    echo "<script>console.log('[v0-intensivos] Ejecutando consulta SQL');</script>";
    
    try {
        $datos = $conexion->query($consulta);
        
        if ($datos === false) {
            $errorInfo = $conexion->errorInfo();
            echo "<script>console.error('[v0-intensivos] Error SQL:', " . json_encode($errorInfo) . ");</script>";
            return '<div class="alert alert-danger">Error en la consulta SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
        
        $datos = $datos->fetchAll();
        echo "<script>console.log('[v0-intensivos] Consulta exitosa. Registros encontrados: " . count($datos) . "');</script>";
        
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        echo "<script>console.log('[v0-intensivos] Total de registros: {$total}');</script>";
        
    } catch (Exception $e) {
        echo "<script>console.error('[v0-intensivos] Excepción en consulta: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error al ejecutar la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $Npaginas = ceil($total / $registros);

    $cargo = $_SESSION['cargo_spm'] ?? 0;

    $tabla = '<div class="table-responsive">
    <table class="table table-dark table-bordered table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>ID Periodo-Curso</th>
                <th>Curso</th>
                <th>Fechas</th>
                <th>Pago Inscripción</th>
                <th>Pago Encuentro</th>
                <th>Pago Título</th>
                <th>Estado</th>';

    if ($cargo != 5) {
        $tabla .= '<th>Inscritos</th>';
    }

    $tabla .= '<th>Secciones</th>';

    if ($cargo == 1 || $cargo == 4) {
        $tabla .= '
            <th>Secciones archivadas</th>
            <th>Crear sección</th>
            <th>Actualizar</th>
            <th>Archivar</th>';
    }

    $tabla .= '
            </tr>
        </thead>
        <tbody>';
               
    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);
            $secCount = (int) $row['total_secciones'];

            // Formatear fechas
            $fechaInicio = $row['fecha_inicio_int'] ? date('d-m-y', strtotime($row['fecha_inicio_int'])) : 'N/A';
            $fechaFin = $row['fecha_fin_int'] ? date('d-m-y', strtotime($row['fecha_fin_int'])) : 'N/A';
            $fechas = "FI: {$fechaInicio} / FF: {$fechaFin}";

            if ($secCount > 0) {
                $boton = "<button type='button' class='btn btn-sm btn-secondary m-1 ver-secciones'
                              data-id='{$idCV}' data-nombre='{$nombreCurso}'>
                              Ver secciones ({$secCount})
                          </button>";
            } else {
                $boton = "<span class='text-muted'>Sin secciones</span>";
            }

            $secArchivadas = (int) $row['total_secciones_archivadas'];
            if ($secArchivadas > 0) {
                $botonArchivadas = '<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-archived2&curso_vigente=' . urlencode($idCV) . '&nombre_curso=' . urlencode($nombreCurso) . '" class="btn btn-sm btn-warning m-1">Ver archivadas (' . $secArchivadas . ')</a>';
            } else {
                $botonArchivadas = "<span class='text-muted'>Sin archivadas</span>";
            }
            
            $tabla .= "<tr class='text-center'>
                <td>{$idCV}</td>
                <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                <td>{$fechas}</td>
                <td>{$row['pago_inscripcion_int']}</td>
                <td>{$row['pago_encuentro_int']}</td>
                <td>{$row['pago_titulo_int']}</td>
                <td>{$row['estado_periodo']}</td>";

            if ($cargo != 5) {
                $tabla .= "<td>{$row['total_inscripciones']}</td>";
            }

            $tabla .= "<td>{$boton}</td>";

            if ($cargo == 1 || $cargo == 4) {
                $tabla .= "
                <td>{$botonArchivadas}</td>
                <td>
                    <a href='" . SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-new&curso_vigente={$idCV}' class='btn btn-sm btn-success'>
                        <i class='fas fa-plus'></i> Crear
                    </a>
                </td>
                <td>
                    <a href='" . SERVERURL . "index.php?views=" . urlencode($carpeta) . "/cv-int-update&course_id_up=" . mainModel::encryption($idCV) . "' class='btn btn-success btn-sm'>
                        <i class='fas fa-sync-alt'></i>
                    </a>
                </td>
                <td>
                    <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='delete' data-entity='curso_vigente' autocomplete='off'>
                        <input type='hidden' name='curso_vigente_id_archive' value='" . mainModel::encryption($idCV) . "'>
                        <button type='submit' class='btn btn-warning btn-sm'>
                            <i class='fas fa-archive'></i>
                        </button>
                    </form>
                </td>";
            }

            $tabla .= "</tr>";
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="' . ($cargo == 5 ? 8 : ($cargo == 1 || $cargo == 4 ? 12 : 9)) . '">No hay cursos intensivos registrados que coincidan con los filtros.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    $tabla .= '
    <div id="modalSecciones" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
        <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
            <h5 id="tituloSecciones" class="text-white text-center mb-3">Secciones del curso</h5>
            <div id="contenedorSecciones" class="d-flex flex-wrap justify-content-center"></div>
            <button type="button" onclick="document.getElementById(\'modalSecciones\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
    console.log("[v0-intensivos] Agregando event listeners para modales");
    
    document.querySelectorAll(".ver-secciones").forEach(btn => {
        btn.addEventListener("click", function() {
            const modal = document.getElementById("modalSecciones");
            const contenedor = document.getElementById("contenedorSecciones");
            const titulo = document.getElementById("tituloSecciones");
            const carpeta = "' . urlencode($carpeta) . '";
            const cursoId = this.dataset.id;
            const nombreCurso = this.dataset.nombre;
            
            console.log("[v0-intensivos] Abriendo modal de secciones para CV:", cursoId);
            
            titulo.textContent = "Secciones de " + nombreCurso;
            contenedor.innerHTML = "<span class=\'text-light\'>Cargando secciones...</span>";
            
            const body = new URLSearchParams({
                curso_vigente_id: cursoId
            }).toString();
            
            fetch("' . SERVERURL . 'ajax/seccionAjax.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body
            })
            .then(res => res.json())
            .then(data => {
                console.log("[v0-intensivos] Datos recibidos:", data);
                contenedor.innerHTML = "";
                
                if (!data || data.length === 0) {
                    contenedor.innerHTML = "<span class=\'badge bg-danger\'>No hay secciones registradas</span>";
                    return;
                }
                
                data.forEach(sec => {
                    const nombre = sec.nombre_profesor || "";
                    const apellido = sec.apellido_profesor || "";
                    const profText = (nombre || apellido) ? nombre + " " + apellido : "<em>Sin profesor asignado</em>";
                    
                    const card = document.createElement("div");
                    card.className = "card bg-secondary text-white m-2 p-2";
                    card.style.width = "160px";
                    card.innerHTML = `
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-1">Sección ${sec.nombre_seccion}</h6>
                            <p class="card-text text-center mb-1">ID: ${sec.id_seccion}</p>
                            <p class="card-text text-center mb-1">Día: ${sec.dia}</p>
                            <p class="card-text text-center mb-1">Prof: ${profText}</p>
                            <p class="card-text text-center mb-1">Inscritos: ${sec.inscritos || 0}</p>
                            <p class="card-text text-center mb-1">Evaluaciones: ${sec.evaluaciones || 0}</p>
	
	                        <a href="' . SERVERURL . 'index.php?views=${carpeta}/seccion-consult&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
                      </div>
                    `;
                    contenedor.appendChild(card);
                });
            })
            .catch(err => {
                console.error("[v0-intensivos] Error cargando secciones:", err);
                contenedor.innerHTML = "<span class=\'badge bg-danger\'>Error al cargar secciones</span>";
            });
            
            modal.style.display = "block";
        });
    });
    
    console.log("[v0-intensivos] Event listeners agregados correctamente");
    </script>';

    // Paginación
    if ($total > 0 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-center">Mostrando registros del <strong>' . $reg_inicio . '</strong> al <strong>' . $reg_final . '</strong> de un total de <strong>' . $total . '</strong></p>';
        
        $queryParams = http_build_query([
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_estado' => $filtro_estado,
            'filtro_anio' => $filtro_anio,
            'filtro_profesor' => $filtro_profesor,
            'filtro_dia' => $filtro_dia,
            'filtro_id_seccion' => $filtro_id_seccion,
            'filtro_arc' => $filtro_arc,
            'origen' => $_GET['origen'] ?? '',
            'orden' => $orden
        ]);
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }
    
    echo "<script>console.log('[v0-intensivos] Retornando tabla HTML completa');</script>";
    return $tabla;
}
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        //@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ este se eliminara@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
        public function paginador_secciones_intensivos_archivados_controlador($pagina, $registros) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<script>console.log('[v0-intensivos-archivados] Iniciando paginador_secciones_intensivos_archivados_controlador');</script>";
    
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina    = ($pagina > 0) ? $pagina : 1;
    $inicio    = (($pagina * $registros) - $registros);

    $cargo = $_SESSION['cargo_spm'] ?? 1;

    $carpetasPorCargo = [
        1 => "admin",
        2 => "Control de estudio",
        3 => "administrador de pagos",
        4 => "Supervisor de cursos",
        5 => "Estudiante"
    ];
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";
    $vistaCompleta = urlencode($carpeta) . '/cv-int-list-archived';
               
    echo "<script>console.log('[v0-intensivos-archivados] Pagina: {$pagina}, Registros: {$registros}, Inicio: {$inicio}');</script>";

    // Filtros
    $filtro_id           = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre       = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_estado       = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? '');
    $filtro_anio         = (int) ($_GET['filtro_anio'] ?? 0);
    $filtro_profesor     = mainModel::limpiar_cadena($_GET['filtro_profesor'] ?? '');
    $filtro_id_seccion   = mainModel::limpiar_cadena($_GET['filtro_id_seccion'] ?? '');
    $orden               = $_GET['orden'] ?? 'reciente';

    echo "<script>console.log('[v0-intensivos-archivados] Filtros aplicados:', " . json_encode([
        'id' => $filtro_id,
        'nombre' => $filtro_nombre,
        'estado' => $filtro_estado,
        'anio' => $filtro_anio,
        'orden' => $orden
    ]) . ");</script>";

    try {
        $conexion = mainModel::conectar();
        echo "<script>console.log('[v0-intensivos-archivados] Conexión a BD exitosa');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('[v0-intensivos-archivados] Error de conexión: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $where = "cv.id_cohorte_fk IS NULL AND c.tipo_curso = 'Intensivo' AND cv.estado_curso_vigente = 0";
    
    if ($filtro_id != "")           $where .= " AND cv.id_curso_vigente = '$filtro_id'";
    if ($filtro_nombre != "")       $where .= " AND c.id_curso = '$filtro_nombre'";
    if ($filtro_estado != "")       $where .= " AND pi.estado_periodo = '$filtro_estado'";
    if ($filtro_anio >= 2025)       $where .= " AND YEAR(pi.fecha_inicio_int) = '$filtro_anio'";
    if ($filtro_profesor != "")     $where .= " AND s.ci_profesor = '$filtro_profesor'";
    if ($filtro_id_seccion != "")   $where .= " AND s.id_seccion = '$filtro_id_seccion'";
    
    $filtro_arc = $_GET['filtro_arc'] ?? 'no';
    if ($filtro_arc === 'si') {
        $where .= " AND EXISTS (
            SELECT 1 FROM seccion s3
            WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
              AND s3.archivar_seccion = 0
        )";
    }

    echo "<script>console.log('[v0-intensivos-archivados] WHERE construido: " . addslashes($where) . "');</script>";

    // Ordenamiento
    $orden_sql = match($orden) {
        "alfabetico" => "ORDER BY c.nombre_curso ASC",
        "inscritos"  => "ORDER BY total_inscripciones DESC",
        default      => "ORDER BY pi.fecha_inicio_int DESC"
    };

    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
      cv.id_curso_vigente,
      c.nombre_curso,
      c.id_curso,
      pi.fecha_inicio_int,
      pi.fecha_fin_int,
      pi.pago_inscripcion_int,
      pi.pago_encuentro_int,
      pi.pago_titulo_int,
      pi.estado_periodo,
      pi.id_periodo_intensivo,

      (
        SELECT COUNT(*) 
        FROM inscripcion i 
        INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion 
        WHERE i.id_curso_vigente_fk = cv.id_curso_vigente 
          AND i.archivada = 1 
          AND s2.archivar_seccion = 0
      ) AS total_inscripciones,

      COUNT(DISTINCT s.id_seccion) AS total_secciones,

      (
        SELECT COUNT(*) 
        FROM seccion s3 
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s3.archivar_seccion = 0
      ) AS total_secciones_archivadas

    FROM curso_vigente cv
    INNER JOIN curso c 
      ON cv.id_curso_fk = c.id_curso
    INNER JOIN periodo_intensivo pi 
      ON pi.id_curso_vigente_fk = cv.id_curso_vigente

    LEFT JOIN seccion s 
      ON s.id_curso_vigente_fk = cv.id_curso_vigente 
      AND s.archivar_seccion = 0

    LEFT JOIN profesor p 
      ON s.ci_profesor = p.id_profesor 
      AND p.estado_profesor = 1

    WHERE $where
      
    GROUP BY 
      cv.id_curso_vigente, 
      c.nombre_curso,
      c.id_curso,
      pi.fecha_inicio_int,
      pi.fecha_fin_int,
      pi.pago_inscripcion_int,
      pi.pago_encuentro_int,
      pi.pago_titulo_int,
      pi.estado_periodo,
      pi.id_periodo_intensivo

    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";

    echo "<script>console.log('[v0-intensivos-archivados] Ejecutando consulta SQL');</script>";
    
    try {
        $datos = $conexion->query($consulta);
        
        if ($datos === false) {
            $errorInfo = $conexion->errorInfo();
            echo "<script>console.error('[v0-intensivos-archivados] Error SQL:', " . json_encode($errorInfo) . ");</script>";
            return '<div class="alert alert-danger">Error en la consulta SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
        
        $datos = $datos->fetchAll();
        echo "<script>console.log('[v0-intensivos-archivados] Consulta exitosa. Registros encontrados: " . count($datos) . "');</script>";
        
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        echo "<script>console.log('[v0-intensivos-archivados] Total de registros: {$total}');</script>";
        
    } catch (Exception $e) {
        echo "<script>console.error('[v0-intensivos-archivados] Excepción en consulta: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error al ejecutar la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $Npaginas = ceil($total / $registros);

    $cargo = $_SESSION['cargo_spm'] ?? 0;

    $tabla = '<div class="table-responsive">
    <table class="table table-dark table-bordered table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>ID Periodo-Curso</th>
                <th>Curso</th>
                <th>Fechas</th>
                <th>Pago Inscripción</th>
                <th>Pago Encuentro</th>
                <th>Pago Título</th>
                <th>Estado</th>';

    if ($cargo != 5) {
        $tabla .= '<th>Inscritos</th>';
    }

    $tabla .= '<th>Secciones archivadas</th>';

    if ($cargo == 1 || $cargo == 4) {
        $tabla .= '<th>Restaurar</th>';
    }

    $tabla .= '
            </tr>
        </thead>
        <tbody>';
               
    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);

            // Formatear fechas
            $fechaInicio = $row['fecha_inicio_int'] ? date('d-m-y', strtotime($row['fecha_inicio_int'])) : 'N/A';
            $fechaFin = $row['fecha_fin_int'] ? date('d-m-y', strtotime($row['fecha_fin_int'])) : 'N/A';
            $fechas = "FI: {$fechaInicio} / FF: {$fechaFin}";

            $secArchivadas = (int) $row['total_secciones_archivadas'];
            if ($secArchivadas > 0) {
                $botonArchivadas = '<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-archived2&curso_vigente=' . urlencode($idCV) . '&nombre_curso=' . urlencode($nombreCurso) . '" class="btn btn-sm btn-warning m-1">Ver archivadas (' . $secArchivadas . ')</a>';
            } else {
                $botonArchivadas = "<span class='text-muted'>Sin archivadas</span>";
            }
            
            $tabla .= "<tr class='text-center'>
                <td>{$idCV}</td>
                <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                <td>{$fechas}</td>
                <td>{$row['pago_inscripcion_int']}</td>
                <td>{$row['pago_encuentro_int']}</td>
                <td>{$row['pago_titulo_int']}</td>
                <td>{$row['estado_periodo']}</td>";

            if ($cargo != 5) {
                $tabla .= "<td>{$row['total_inscripciones']}</td>";
            }

            $tabla .= "<td>{$botonArchivadas}</td>";

            if ($cargo == 1 || $cargo == 4) {
                $tabla .= "
                <td>
                    <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='restore' autocomplete='off'>
                        <input type='hidden' name='curso_vigente_id_restore' value='" . mainModel::encryption($idCV) . "'>
                        <button type='submit' class='btn btn-info btn-sm'>
                            <i class='fas fa-undo'></i> Restaurar
                        </button>
                    </form>
                </td>";
            }

            $tabla .= "</tr>";
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="' . ($cargo == 5 ? 8 : ($cargo == 1 || $cargo == 4 ? 9 : 8)) . '">No hay cursos intensivos archivados que coincidan con los filtros.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginación
    if ($total > 0 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-center">Mostrando registros del <strong>' . $reg_inicio . '</strong> al <strong>' . $reg_final . '</strong> de un total de <strong>' . $total . '</strong></p>';
        
        $queryParams = http_build_query([
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_estado' => $filtro_estado,
            'filtro_anio' => $filtro_anio,
            'filtro_profesor' => $filtro_profesor,
            'filtro_id_seccion' => $filtro_id_seccion,
            'filtro_arc' => $filtro_arc,
            'origen' => $_GET['origen'] ?? '',
            'orden' => $orden
        ]);
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }
    
    echo "<script>console.log('[v0-intensivos-archivados] Retornando tabla HTML completa');</script>";
    return $tabla;
}
        
        
        
        
        
        
        
        
    
    
}

?>