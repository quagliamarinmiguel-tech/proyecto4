<?php

if($peticionAjax){
    require_once "../modelos/periodoModelo.php";
}else{
    require_once "./modelos/periodoModelo.php";
}

//maneja los ddatos del modulo de periodo y de periodos de cursos, mas no evaluaciones y secciones

//maneja la logica de los periodos de los cursos, o cohortes e intensivos,
//en este ultimo caso tambien se manejan las vistas de crear y modificar
// ademas de las vistas de secciones y lista de evaluaciones y puntajes


class periodoControlador extends periodoModelo{     

    
    
//para periodo-new y update, trae la lista de cursos y profes para los period_course
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

       
       if (!$accessToken) {
        return  json_encode(mainModel::generarAlertaSimple("Sesión no disponible", "error", "simple"));
       }
       
       
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


        $preciosRequeridos = $tipo === 'Intensivo'
            ? [
                'periodo_pago_inscripcion_int' => 'Inscripción',
                'periodo_pago_encuentro_int'   => 'Encuentro',
                'periodo_pago_titulo_int'      => 'Título',
            ]
            : [
                'periodo_pago_inscripcion' => 'Inscripción',
                'periodo_pago_modulo'      => 'Módulo mensual',
                'periodo_pago_art13'       => 'Artículo 13',
                'periodo_pago_aranceles'   => 'Aranceles de graduación',
            ];

        foreach ($preciosRequeridos as $campo => $etiqueta) {
            $precio = (int) ($_POST[$campo] ?? 0);
            if ($precio <= 0 || $precio >= 100) {
                return json_encode(mainModel::generarAlertaSimple("El precio de \"$etiqueta\" debe ser mayor a 0 y menor a 100.","error", "simple"));
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
       
       mainModel::invalidarCachePorPrefijo('periodos_');
	   mainModel::invalidarCachePorPrefijo('cursos_publicos_');
       return json_encode(mainModel::generarAlertaSimple("El periodo fue registrado correctamente.", "success", "recargar"));
   }
        
        
        
        
        
//actualizar periodo, de period update
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


            $preciosRequeridos = $tipo === 'Intensivo'
                ? [
                    'periodo_pago_inscripcion_int_up' => 'Inscripción',
                    'periodo_pago_encuentro_int_up'   => 'Encuentro',
                    'periodo_pago_titulo_int_up'      => 'Título',
                ]
                : [
                    'periodo_pago_inscripcion_up' => 'Inscripción',
                    'periodo_pago_modulo_up'      => 'Módulo mensual',
                    'periodo_pago_art13_up'       => 'Artículo 13',
                    'periodo_pago_aranceles_up'   => 'Aranceles de graduación',
                ];

            foreach ($preciosRequeridos as $campo => $etiqueta) {
                $precio = (int) ($_POST[$campo] ?? 0);
                if ($precio <= 0 || $precio >= 100) {
                    return json_encode(mainModel::generarAlertaSimple("El precio de \"$etiqueta\" debe ser mayor a 0 y menor a 100.","error", "simple"));
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
                
      			mainModel::invalidarCachePorPrefijo('periodos_');
	   			mainModel::invalidarCachePorPrefijo('cursos_publicos_');
                return json_encode(mainModel::generarAlertaSimple('Periodo actualizado correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al actualizar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
  
        
        
//paginador de periodos de period list y archived
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

        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        //$carpetas = [1 => 'admin', 2 => 'Control de estudio', 3 => 'administrador de pagos', 4 => 'Supervisor de cursos', 5 => 'Estudiante'];
        $carpeta = 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        // Generar tabla
        $tabla = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center roboto-medium">';
        $tabla .= '<th>ID</th><th>Nombre</th><th>Tipo</th><th>Fecha inicio/fin</th><th>Estado</th>';

        $tabla .= '<th>Costos</th><th>Ver secciones</th>';
        
        if($state!=0){$tabla .=' <th>Agregar curso</th>';}
        
        $tabla .='<th>Acciones</th>';
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
                    $tabla .= '<td>
                        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-new&period_id=' . $id . '" class="btn btn-success btn-sm" title="Crear sección o agregar curso"><i class="fas fa-plus"></i></a>
                    </td>';
                }

                if($state==0){
                    $tabla .= '<td>
                        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-course-list-archived&filtro_id_periodo=' . $id . '&origen=periodo" class="btn btn-info btn-sm" title="Ver cursos vigentes">
                            <i class="fas fa-eye"></i>
                        </a>
                    </td>';
                }
                if (in_array($cargo, [1, 4], true)) {
                    if ($state == 1) {
                 
              $tabla .= '<td style="display:flex;justify-content:center;align-items:center;">
						<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-update&periodo_id_up=' . $idEnc . '" class="btn btn-success btn-sm" title="Actualizar y consultar"><i class="fas fa-sync-alt"></i></a>
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/periodoAjax.php" method="POST" data-form="delete" data-entity="periodo" autocomplete="off">
                                <input type="hidden" name="periodo_id_archive" value="' . $idEnc . '">
                                <button type="submit" class="btn btn-warning btn-sm" title="Archivar"><i class="fas fa-archive"></i></button>
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
		       mainModel::invalidarCachePorPrefijo('periodos_');
	 		   mainModel::invalidarCachePorPrefijo('cursos_publicos_');
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
      			 mainModel::invalidarCachePorPrefijo('periodos_');
	  			 mainModel::invalidarCachePorPrefijo('cursos_publicos_');
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
    public function paginador_period_course_controlador($pagina,$registros,$vista,$busqueda_periodo,$busqueda_curso,$state = 1, $course_id = null, $period_id = null,$busqueda_section_id = null) {
        $pagina = max(1, (int) $pagina);
        $registros = max(1, (int) $registros);
        $token = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

        $filtros = [
            'busqueda_periodo' => trim($busqueda_periodo) ?: null,
            'busqueda_curso'   => trim($busqueda_curso) ?: null,
            'id_period_course' => isset($_GET['filtro_id']) && $_GET['filtro_id'] !== '' ? (int) $_GET['filtro_id'] : null,
            'estado_periodo'   => trim($_GET['filtro_estado'] ?? '') ?: null,
            'orden'            => $_GET['orden'] ?? 'periodo_desc',
            'state'            => (int) $state,
            'course_id'        => $course_id,  
            'period_id'        => $period_id,  
            'section_id'       => is_numeric($busqueda_section_id) ? (int) $busqueda_section_id : null, 
        ];

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
        $carpeta = 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        // CONSTRUCCIÓN DE LA TABLA HTML
        $tabla = '<div class="table-responsive">';
        $tabla .= '<table class="table table-dark table-sm table-hover">';
        $tabla .= '<thead><tr class="text-center roboto-medium">';
        $tabla .= '<th>Periodo</th>';
        $tabla .= '<th>Curso</th>';
        //$tabla .= '<th>Period‑Course ID</th>';
        $tabla .= '<th>Tipo</th>';
        $tabla .= '<th>Fechas</th>';
        $tabla .= '<th>Estado</th>';
        $tabla .= '<th>Secciones</th>';  
       
        if($state==1){$tabla .= '<th>Crear sección</th>';}
        
        if (in_array($cargo, [1, 4], true) && $state!=0) {
            $tabla .= '<th>Acciones</th>';
        }
        $tabla .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;
            foreach ($items as $item) {
                // Extraer datos
                $pcId = (int) $item['period_course_id'];
                $periodId = $item['period_id'];
                $periodName = htmlspecialchars($item['period_name'] ?? '', ENT_QUOTES, 'UTF-8');
                $periodType = htmlspecialchars($item['period_type'] ?? '', ENT_QUOTES, 'UTF-8');
                $periodState = htmlspecialchars($item['period_current_state'] ?? '', ENT_QUOTES, 'UTF-8');
                $period_s = htmlspecialchars($item['period_state'] ?? '', ENT_QUOTES, 'UTF-8');
                $startDate = htmlspecialchars($item['period_start_date'] ?? '', ENT_QUOTES, 'UTF-8');
                $endDate = htmlspecialchars($item['period_end_date'] ?? '', ENT_QUOTES, 'UTF-8');
                $courseId = $item['course_id'];
                $courseName = htmlspecialchars($item['course_name'] ?? '', ENT_QUOTES, 'UTF-8');
                $coursestate = htmlspecialchars($item['course_state'] ?? '', ENT_QUOTES, 'UTF-8');
                $sectionsCount = (int) ($item['sections_count'] ?? 0);
                $encryptedPcId = mainModel::encryption($pcId);
			
                $tabla .= '<style>td{pointer-events: none;}td:has(a, button, form) {pointer-events: auto;} td:hover {color: grey;}</style>';
                
    		    $tabla .= '<style>.red{color:red;}</style>';
	                
                // Fila
                $tabla .= '<tr class="text-center">';
                //$tabla .= '<td>' . $periodId . ' / ' . $periodName . '</td>';
                
                if($period_s == 0){$tabla .= '<td style="color:red;">' . $periodId . ' / ' . $periodName . '</td>';}
                else{$tabla .= '<td>' . $periodId . ' / ' . $periodName . '</td>';}
                
                if($coursestate == 0){$tabla .= '<td style="color:red;">' . $courseId . ' / ' . $courseName . '</td>';}
                else{$tabla .= '<td>' . $courseId . ' / ' . $courseName . '</td>';}
                
                //$tabla .= '<td>' . $pcId . '</td>';
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
                        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-new&period_course_id=' . $pcId . '" 
                           class="btn btn-success btn-sm" title="Crear sección">
                            <i class="fas fa-plus"></i>
                        </a>
                    </td>';
    	        }
	
                // Botones de Archivar / Restaurar (solo admin y supervisor)
                if (in_array($cargo, [1, 4], true) && $state!=0) {
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
                    } 
                    /*else {
                        // Archivado → Mostrar botón Restaurar
                        $tabla .= '
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/periodoAjax.php" method="POST" data-form="restore" data-entity="period_course" autocomplete="off">
                                <input type="hidden" name="period_course_id_restore" value="' . $encryptedPcId . '">
                                <button type="submit" class="btn btn-info btn-sm" title="Restaurar">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                        ';
                    }*/
                    $tabla .= '</td>';
                }

                $tabla .= '</tr>';
            }
            $regFinal = $regInicio + count($items) - 1;
        } else {
            $columnas = in_array($cargo, [1, 4]) ? 9 : 7;
            $tabla .= '<tr class="text-center"><td colspan="' . $columnas . '">No hay period‑cursos registrados.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // PAGINADOR
        if ($total > 0) {
            $tabla .= '<p class="text-right">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de un total de ' . $total . '</p>';
            $paramsGet = array_filter([
                'busqueda_periodo'    => $busqueda_periodo,
                'busqueda_curso'      => $busqueda_curso,
                'busqueda_section_id' => $busqueda_section_id,
                'filtro_id'           => $_GET['filtro_id'] ?? '',
                'filtro_estado'       => $_GET['filtro_estado'] ?? '',
                'orden'               => $_GET['orden'] ?? '',
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

        $tabla .= periodoModelo::generar_modal_secciones_js('seccion-consult');

        return $tabla;
    }

     //Helper para asignar clase CSS según el estado del periodo
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
        
        
        
        
//este creo q no tiene logica de reembolso
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
        
  
    
    
/*---------------------------------------------------generacion de pdfs de periodos--------------------------------------------------------------*/
                
// REPORTES DE PERÍODO 
// tipo: 1=estudiantes, 2=cursos, 3=recaudación
    public function reporte_periodo_controlador(int $periodId, int $tipo = 1): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = periodoModelo::reportes_periodo_individual_supabase($periodId, $tipo, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }

        return [
            'ok'      => true,
            'periodo' => $r['data']['periodo'] ?? [],
            'items'   => $r['data']['data']    ?? [],
        ];
    }

// reportes de los cursos del periodo(2 casos)
// tipo: 1=aprobados, 2=pagos
    public function reporte_pc_controlador(int $periodCourseId, int $tipo = 1): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = periodoModelo::reportes_period_course_individual_supabase($periodCourseId, $tipo, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }

        return [
            'ok'    => true,
            'curso' => $r['data']['curso'] ?? [],
            'data'  => $r['data']['data']  ?? [],
        ];
    }


/*@@@@@@@@@@@@@@@@@@@@@@@@ Todos los estudiantes del periodo @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/

//pdf
    public function generar_pdf_periodo_estudiantes_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 1);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            $pdf = formatoControlador::pdfIniciar('ESTUDIANTES DEL PERÍODO');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'PERÍODO:', 'text'=>($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : '')],
                ['bold'=>'Fechas:',  'text'=>($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? '')],
                ['bold'=>'TOTAL:',   'text'=>count($items) . ' estudiantes'],
            ]);

            if (empty($items)) {
                formatoControlador::pdfSinDatos($pdf, 'No hay estudiantes inscritos en este período.');
            } else {
                $filas = [];
                //'ci'           => $it['ci'] ?? '',
                foreach ($items as $i => $it) {
                    $filas[] = [
                        'n'            => $i + 1,
    				    'ci'           => mainModel::decryption($it['ci'] ?? ''),   // ← CAMBIO
                        'nombre'       => trim(($it['last_name'] ?? '') . ' ' . ($it['name'] ?? '')),
                        'cursos'       => $it['cursos'] ?? '',
                        'secciones'    => $it['secciones'] ?? '',
                        'total_cursos' => (int)($it['total_cursos'] ?? 0),
                    ];
                }
                $columnas = [
                    ['key'=>'n',            'label'=>'Nº',                   'ancho'=>10, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'ci',           'label'=>'C.I',                 'ancho'=>22, 'align'=>'C'],
                    ['key'=>'nombre',       'label'=>'Apellidos y Nombres', 'ancho'=>55, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'cursos',       'label'=>'Cursos',              'ancho'=>50, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'secciones',    'label'=>'Secciones',           'ancho'=>45, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'total_cursos', 'label'=>'Total cursos',        'ancho'=>14, 'align'=>'C', 'formato'=>'int'],
                ];
                formatoControlador::pdfTabla($pdf, $columnas, $filas);
            }

            $pdf->Ln(3);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->Cell(0, 4, formatoControlador::iso(
                'Los cursos y secciones se listan en orden alfabético, correspondiendo cada sección al curso en la misma posición.'
            ), 0, 1);

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida(
                $pdf,
                formatoControlador::nombreArchivo('Estudiantes_Periodo_' . ($periodo['name'] ?? ''), 'pdf')
            );

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
        
        
//excel
    public function generar_excel_periodo_estudiantes_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 1);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            $columnas = [
                ['key'=>'n',            'label'=>'Nº',                   'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',           'label'=>'C.I',                 'align'=>'C'],
                ['key'=>'nombre',       'label'=>'Apellidos y Nombres', 'wrap'=>true],
                ['key'=>'cursos',       'label'=>'Cursos',              'wrap'=>true],
                ['key'=>'secciones',    'label'=>'Secciones',           'wrap'=>true],
                ['key'=>'total_cursos', 'label'=>'Total cursos',        'align'=>'C', 'formato'=>'int'],
            ];
            $anchos = ['n'=>6, 'ci'=>14, 'nombre'=>35, 'cursos'=>40, 'secciones'=>25, 'total_cursos'=>10];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'ESTUDIANTES DEL PERÍODO', 'Estudiantes', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'PERÍODO: ' . ($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : ''),
                'Fechas: '  . ($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? ''),
                'Total de estudiantes: ' . count($items),
            ], count($columnas));

            if (empty($items)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay estudiantes en este período.', count($columnas));
            } else {
                $filas = [];
                //'ci'           => $it['ci'] ?? '',
                foreach ($items as $i => $it) {
                    $filas[] = [
                        'n'            => $i + 1,
     				    'ci'           => mainModel::decryption($it['ci'] ?? ''),
                        'nombre'       => trim(($it['last_name'] ?? '') . ' ' . ($it['name'] ?? '')),
                        'cursos'       => $it['cursos'] ?? '',
                        'secciones'    => $it['secciones'] ?? '',
                        'total_cursos' => (int)($it['total_cursos'] ?? 0),
                    ];
                }
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida(
                $excel['spreadsheet'],
                formatoControlador::nombreArchivo('Estudiantes_Periodo_' . ($periodo['name'] ?? ''), 'xlsx')
            );

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
        
        
//csv
   public function generar_csv_periodo_estudiantes_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 1);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            $columnas = [
                ['key'=>'n',            'label'=>'Nº',                   'formato'=>'int'],
                ['key'=>'ci',           'label'=>'C.I'],
                ['key'=>'nombre',       'label'=>'Apellidos y Nombres'],
                ['key'=>'cursos',       'label'=>'Cursos'],
                ['key'=>'secciones',    'label'=>'Secciones'],
                ['key'=>'total_cursos', 'label'=>'Total cursos',        'formato'=>'int'],
            ];

            $filas = [];
            foreach ($items as $i => $it) {
                //'ci'           => $it['ci'] ?? '',
                $filas[] = [
                    'n'            => $i + 1,
     				'ci'           => mainModel::decryption($it['ci'] ?? ''),
                    'nombre'       => trim(($it['last_name'] ?? '') . ' ' . ($it['name'] ?? '')),
                    'cursos'       => $it['cursos'] ?? '',
                    'secciones'    => $it['secciones'] ?? '',
                    'total_cursos' => (int)($it['total_cursos'] ?? 0),
                ];
            }

            $out = formatoControlador::csvIniciar(
                formatoControlador::nombreArchivo('Estudiantes_Periodo_' . ($periodo['name'] ?? ''), 'csv')
            );
            formatoControlador::csvContexto($out, [
                'ESTUDIANTES DEL PERÍODO',
                'PERÍODO: ' . ($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : ''),
                'Fechas: '  . ($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? ''),
                'Total de estudiantes: ' . count($items),
            ]);
            if (empty($filas)) {
                formatoControlador::csvSinDatos($out, 'No hay estudiantes en este período.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $filas);
            }
            formatoControlador::csvSalida($out);

        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    } 
       

/*@@@@@@@@@@@@@@@@@ todos los cursos del periodo@@@@@@@@@@@@@@@@@@@*/           
            
//pdf
    public function generar_pdf_periodo_cursos_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 2);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            $pdf = formatoControlador::pdfIniciar('CURSOS DEL PERÍODO');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'PERÍODO:', 'text'=>($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : '')],
                ['bold'=>'Fechas:',  'text'=>($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? '')],
                ['bold'=>'TOTAL:',   'text'=>count($items) . ' cursos'],
            ]);

            if (empty($items)) {
                formatoControlador::pdfSinDatos($pdf, 'No hay cursos en este período.');
            } else {
                $columnasSecc = [
                    ['key'=>'section_name',  'label'=>'Sección',     'ancho'=>25, 'align'=>'C'],
                    ['key'=>'section_day',   'label'=>'Día',         'ancho'=>22, 'align'=>'C'],
                    ['key'=>'profesor',      'label'=>'Profesor',    'ancho'=>75, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'inscritos',     'label'=>'Inscritos',   'ancho'=>38, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'evaluaciones',  'label'=>'Evaluaciones','ancho'=>36, 'align'=>'C', 'formato'=>'int'],
                ];

                foreach ($items as $curso) {
                    $nombreCurso = $curso['course_name'] ?? '';
                    $tipoCurso   = $curso['course_type'] ?? '';
                    $totalInsc   = (int)($curso['total_inscritos'] ?? 0);
                    $totalSecc   = (int)($curso['total_secciones'] ?? 0);
                    $secciones   = $curso['secciones'] ?? [];

                    // ── Header del curso ──
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->SetFillColor(220, 220, 255);
                    $pdf->Cell(196, 7, formatoControlador::iso(
                        $nombreCurso . ($tipoCurso ? ' (' . $tipoCurso . ')' : '') .
                        '   |   Inscritos: ' . $totalInsc .
                        '   |   Secciones: ' . $totalSecc
                    ), 1, 1, 'L', true);

                    if (empty($secciones)) {
                        $pdf->SetFont('Arial', 'I', 8);
                        $pdf->Cell(196, 6, formatoControlador::iso('Este curso no tiene secciones activas.'), 1, 1, 'C');
                        $pdf->Ln(3);
                        continue;
                    }

                    // ── Sub-tabla de secciones ──
                    $filasSecc = [];
                    foreach ($secciones as $s) {
                        $prof = trim(($s['teacher_name'] ?? '') . ' ' . ($s['teacher_last_name'] ?? ''));
                        $filasSecc[] = [
                            'section_name' => $s['section_name'] ?? '',
                            'section_day'  => $s['section_day']  ?? '',
                            'profesor'     => $prof !== '' ? $prof : 'Sin asignar',
                            'inscritos'    => (int)($s['total_inscritos']    ?? 0),
                            'evaluaciones' => (int)($s['total_evaluaciones'] ?? 0),
                        ];
                    }
                    formatoControlador::pdfTabla($pdf, $columnasSecc, $filasSecc, [
                        'alto_header' => 7,
                        'alto_fila'   => 6,
                    ]);
                    $pdf->Ln(3);
                }
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida(
                $pdf,
                formatoControlador::nombreArchivo('Cursos_Periodo_' . ($periodo['name'] ?? ''), 'pdf')
            );

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }        
        
//excel
        
    public function generar_excel_periodo_cursos_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 2);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            $columnas = [
                ['key'=>'curso',        'label'=>'Curso',                'wrap'=>true],
                ['key'=>'tipo',         'label'=>'Tipo',                  'align'=>'C'],
                ['key'=>'seccion',      'label'=>'Sección',               'align'=>'C'],
                ['key'=>'dia',          'label'=>'Día',                   'align'=>'C'],
                ['key'=>'profesor',     'label'=>'Profesor',              'wrap'=>true],
                ['key'=>'inscritos',    'label'=>'Inscritos sección',     'align'=>'C', 'formato'=>'int'],
                ['key'=>'evaluaciones', 'label'=>'Evaluaciones',          'align'=>'C', 'formato'=>'int'],
                ['key'=>'total_curso',  'label'=>'Inscritos total curso', 'align'=>'C', 'formato'=>'int'],
            ];
            $anchos = ['curso'=>30, 'tipo'=>16, 'seccion'=>12, 'dia'=>14, 'profesor'=>30, 'inscritos'=>12, 'evaluaciones'=>14, 'total_curso'=>14];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'CURSOS DEL PERÍODO', 'Cursos', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'PERÍODO: ' . ($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : ''),
                'Fechas: '  . ($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? ''),
                'Total de cursos: ' . count($items),
            ], count($columnas));

            // Aplanar: una fila por sección (o una sola fila "sin secciones")
            $filas = [];
            foreach ($items as $curso) {
                $nombreCurso = $curso['course_name'] ?? '';
                $tipoCurso   = $curso['course_type'] ?? '';
                $totalInsc   = (int)($curso['total_inscritos'] ?? 0);
                $secciones   = $curso['secciones'] ?? [];

                if (empty($secciones)) {
                    $filas[] = [
                        'curso'=>$nombreCurso, 'tipo'=>$tipoCurso,
                        'seccion'=>'—', 'dia'=>'', 'profesor'=>'',
                        'inscritos'=>0, 'evaluaciones'=>0, 'total_curso'=>$totalInsc,
                    ];
                    continue;
                }
                foreach ($secciones as $s) {
                    $prof = trim(($s['teacher_name'] ?? '') . ' ' . ($s['teacher_last_name'] ?? ''));
                    $filas[] = [
                        'curso'        => $nombreCurso,
                        'tipo'         => $tipoCurso,
                        'seccion'      => $s['section_name'] ?? '',
                        'dia'          => $s['section_day']  ?? '',
                        'profesor'     => $prof !== '' ? $prof : 'Sin asignar',
                        'inscritos'    => (int)($s['total_inscritos']    ?? 0),
                        'evaluaciones' => (int)($s['total_evaluaciones'] ?? 0),
                        'total_curso'  => $totalInsc,
                    ];
                }
            }

            if (empty($filas)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay cursos en este período.', count($columnas));
            } else {
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida(
                $excel['spreadsheet'],
                formatoControlador::nombreArchivo('Cursos_Periodo_' . ($periodo['name'] ?? ''), 'xlsx')
            );

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
        
        
//csv
    public function generar_csv_periodo_cursos_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 2);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            $columnas = [
                ['key'=>'curso',        'label'=>'Curso'],
                ['key'=>'tipo',         'label'=>'Tipo'],
                ['key'=>'seccion',      'label'=>'Sección'],
                ['key'=>'dia',          'label'=>'Día'],
                ['key'=>'profesor',     'label'=>'Profesor'],
                ['key'=>'inscritos',    'label'=>'Inscritos sección',     'formato'=>'int'],
                ['key'=>'evaluaciones', 'label'=>'Evaluaciones',          'formato'=>'int'],
                ['key'=>'total_curso',  'label'=>'Inscritos total curso', 'formato'=>'int'],
            ];

            $filas = [];
            foreach ($items as $curso) {
                $nombreCurso = $curso['course_name'] ?? '';
                $tipoCurso   = $curso['course_type'] ?? '';
                $totalInsc   = (int)($curso['total_inscritos'] ?? 0);
                $secciones   = $curso['secciones'] ?? [];

                if (empty($secciones)) {
                    $filas[] = [
                        'curso'=>$nombreCurso, 'tipo'=>$tipoCurso,
                        'seccion'=>'—', 'dia'=>'', 'profesor'=>'',
                        'inscritos'=>0, 'evaluaciones'=>0, 'total_curso'=>$totalInsc,
                    ];
                    continue;
                }
                foreach ($secciones as $s) {
                    $prof = trim(($s['teacher_name'] ?? '') . ' ' . ($s['teacher_last_name'] ?? ''));
                    $filas[] = [
                        'curso'        => $nombreCurso,
                        'tipo'         => $tipoCurso,
                        'seccion'      => $s['section_name'] ?? '',
                        'dia'          => $s['section_day']  ?? '',
                        'profesor'     => $prof !== '' ? $prof : 'Sin asignar',
                        'inscritos'    => (int)($s['total_inscritos']    ?? 0),
                        'evaluaciones' => (int)($s['total_evaluaciones'] ?? 0),
                        'total_curso'  => $totalInsc,
                    ];
                }
            }

            $out = formatoControlador::csvIniciar(
                formatoControlador::nombreArchivo('Cursos_Periodo_' . ($periodo['name'] ?? ''), 'csv')
            );
            formatoControlador::csvContexto($out, [
                'CURSOS DEL PERÍODO',
                'PERÍODO: ' . ($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : ''),
                'Fechas: '  . ($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? ''),
                'Total de cursos: ' . count($items),
            ]);
            if (empty($filas)) {
                formatoControlador::csvSinDatos($out, 'No hay cursos en este período.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $filas);
            }
            formatoControlador::csvSalida($out);

        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

        
/*@@@@@@@@@@@@@@@@@@@@@@@@ Recaudacion por curso @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/  


//pdf
    public function generar_pdf_periodo_recaudacion_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 3);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            // Totales del período
            $granEsp = 0.0; $granCob = 0.0;
            foreach ($items as $it) {
                $rec = $it['recaudacion'] ?? [];
                $granEsp += (float)($rec['total_esperado'] ?? 0);
                $granCob += (float)($rec['total_cobrado']  ?? 0);
            }

            $pdf = formatoControlador::pdfIniciar('RECAUDACIÓN POR CURSO');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'PERÍODO:', 'text'=>($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : '')],
                ['bold'=>'Fechas:',  'text'=>($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? '')],
                ['bold'=>'ESPERADO:','text'=>'$' . number_format($granEsp, 2, '.', ',')],
                ['bold'=>'COBRADO:', 'text'=>'$' . number_format($granCob, 2, '.', ',')],
                ['bold'=>'DIFERENCIA:','text'=>'$' . number_format($granEsp - $granCob, 2, '.', ',')],
            ]);

            if (empty($items)) {
                formatoControlador::pdfSinDatos($pdf, 'No hay cursos en este período.');
            } else {
                $columnasConc = [
                    ['key'=>'motive',   'label'=>'Concepto',    'ancho'=>60, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'esp_unit', 'label'=>'Esp. Unit.',  'ancho'=>26, 'align'=>'C', 'formato'=>'money_dec'],
                    ['key'=>'esp_tot',  'label'=>'Esp. Total',  'ancho'=>28, 'align'=>'C', 'formato'=>'money_dec'],
                    ['key'=>'cobrado',  'label'=>'Cobrado',     'ancho'=>26, 'align'=>'C', 'formato'=>'money_dec'],
                    ['key'=>'difer',    'label'=>'Diferencia',  'ancho'=>26, 'align'=>'C', 'formato'=>'money_dec'],
                    ['key'=>'pagos',    'label'=>'# Pagos',     'ancho'=>30, 'align'=>'C', 'formato'=>'int'],
                ];
                $anchosTotal = [60, 26, 28, 26, 26, 30];

                foreach ($items as $it) {
                    $nombreCurso = $it['course_name'] ?? '';
                    $tipoCurso   = $it['course_type'] ?? '';
                    $rec         = $it['recaudacion'] ?? [];
                    $inscritos   = (int)($rec['inscritos'] ?? 0);
                    $conceptos   = $rec['items'] ?? [];
                    $totalEsp    = (float)($rec['total_esperado'] ?? 0);
                    $totalCob    = (float)($rec['total_cobrado']  ?? 0);

                    // ── Header del curso ──
                    $pdf->SetFont('Arial', 'B', 9);
                    $pdf->SetFillColor(220, 220, 255);
                    $pdf->Cell(196, 7, formatoControlador::iso(
                        $nombreCurso . ($tipoCurso ? ' (' . $tipoCurso . ')' : '') .
                        '   |   Inscritos: ' . $inscritos .
                        '   |   Esperado: $' . number_format($totalEsp, 2, '.', ',') .
                        '   |   Cobrado: $'  . number_format($totalCob, 2, '.', ',')
                    ), 1, 1, 'L', true);

                    if (empty($conceptos)) {
                        $pdf->SetFont('Arial', 'I', 8);
                        $pdf->Cell(196, 6, formatoControlador::iso('Sin conceptos de pago configurados.'), 1, 1, 'C');
                        $pdf->Ln(3);
                        continue;
                    }

                    // ── Sub-tabla de conceptos ──
                    $filasConc = [];
                    foreach ($conceptos as $c) {
                        $espTot = (float)($c['esperado_total'] ?? 0);
                        $cob    = (float)($c['cobrado']        ?? 0);
                        $filasConc[] = [
                            'motive'   => $c['motive']              ?? '',
                            'esp_unit' => (float)($c['esperado_unitario'] ?? 0),
                            'esp_tot'  => $espTot,
                            'cobrado'  => $cob,
                            'difer'    => $espTot - $cob,
                            'pagos'    => (int)($c['count_pagos']   ?? 0),
                        ];
                    }
                    formatoControlador::pdfTabla($pdf, $columnasConc, $filasConc, [
                        'alto_header' => 7,
                        'alto_fila'   => 6,
                        'color_cell'  => function(array $row, array $col) {
                            if ($col['key'] !== 'difer') return null;
                            return ((float)$row['difer'] > 0)
                                ? ['fg'=>[200, 0, 0]]     // rojo: deuda pendiente
                                : ['fg'=>[0, 130, 0]];    // verde: al día
                        },
                    ]);

                    // ── Fila TOTAL DEL CURSO ──
                    formatoControlador::pdfResumen($pdf, [
                        ['label'=>'TOTAL DEL CURSO', 'valores'=>[
                            '',
                            '$' . number_format($totalEsp, 2, '.', ','),
                            '$' . number_format($totalCob, 2, '.', ','),
                            '$' . number_format($totalEsp - $totalCob, 2, '.', ','),
                            '',
                        ]],
                    ], $anchosTotal, ['alto' => 7, 'fill' => [240, 240, 240]]);

                    $pdf->Ln(3);
                }
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida(
                $pdf,
                formatoControlador::nombreArchivo('Recaudacion_Periodo_' . ($periodo['name'] ?? ''), 'pdf')
            );

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

        
//excel
    public function generar_excel_periodo_recaudacion_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 3);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            $columnas = [
                ['key'=>'curso',    'label'=>'Curso',      'wrap'=>true],
                ['key'=>'tipo',     'label'=>'Tipo',       'align'=>'C'],
                ['key'=>'inscritos','label'=>'Inscritos',  'align'=>'C', 'formato'=>'int'],
                ['key'=>'concepto', 'label'=>'Concepto',   'wrap'=>true],
                ['key'=>'esp_unit', 'label'=>'Esp. Unit.', 'align'=>'R', 'formato'=>'money_dec'],
                ['key'=>'esp_tot',  'label'=>'Esp. Total', 'align'=>'R', 'formato'=>'money_dec'],
                ['key'=>'cobrado',  'label'=>'Cobrado',    'align'=>'R', 'formato'=>'money_dec'],
                ['key'=>'difer',    'label'=>'Diferencia', 'align'=>'R', 'formato'=>'money_dec'],
                ['key'=>'pagos',    'label'=>'# Pagos',    'align'=>'C', 'formato'=>'int'],
            ];
            $anchos = ['curso'=>30, 'tipo'=>16, 'inscritos'=>10, 'concepto'=>24, 'esp_unit'=>14, 'esp_tot'=>14, 'cobrado'=>14, 'difer'=>14, 'pagos'=>10];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'RECAUDACIÓN POR CURSO', 'Recaudación', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'PERÍODO: ' . ($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : ''),
                'Fechas: '  . ($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? ''),
            ], count($columnas));

            // Aplanar: filas de conceptos + fila TOTAL por curso
            $filas = [];
            foreach ($items as $it) {
                $nombreCurso = $it['course_name'] ?? '';
                $tipoCurso   = $it['course_type'] ?? '';
                $rec         = $it['recaudacion'] ?? [];
                $inscritos   = (int)($rec['inscritos'] ?? 0);
                $conceptos   = $rec['items'] ?? [];
                $totalEsp    = (float)($rec['total_esperado'] ?? 0);
                $totalCob    = (float)($rec['total_cobrado']  ?? 0);

                if (empty($conceptos)) {
                    $filas[] = [
                        'curso'=>$nombreCurso, 'tipo'=>$tipoCurso, 'inscritos'=>$inscritos,
                        'concepto'=>'Sin conceptos', 'esp_unit'=>'', 'esp_tot'=>'',
                        'cobrado'=>'', 'difer'=>'', 'pagos'=>'',
                    ];
                    continue;
                }
                foreach ($conceptos as $c) {
                    $espTot = (float)($c['esperado_total'] ?? 0);
                    $cob    = (float)($c['cobrado']        ?? 0);
                    $filas[] = [
                        'curso'    => $nombreCurso,
                        'tipo'     => $tipoCurso,
                        'inscritos'=> $inscritos,
                        'concepto' => $c['motive'] ?? '',
                        'esp_unit' => (float)($c['esperado_unitario'] ?? 0),
                        'esp_tot'  => $espTot,
                        'cobrado'  => $cob,
                        'difer'    => $espTot - $cob,
                        'pagos'    => (int)($c['count_pagos'] ?? 0),
                    ];
                }
                // Fila TOTAL por curso
                $filas[] = [
                    'curso'=>$nombreCurso, 'tipo'=>$tipoCurso, 'inscritos'=>$inscritos,
                    'concepto'=>'TOTAL DEL CURSO',
                    'esp_unit'=>'',
                    'esp_tot'=>$totalEsp,
                    'cobrado'=>$totalCob,
                    'difer'=>$totalEsp - $totalCob,
                    'pagos'=>'',
                ];
            }

            if (empty($filas)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay cursos en este período.', count($columnas));
            } else {
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos, [
                    'color_cell' => function(array $row, array $col) {
                        // Resaltar fila TOTAL DEL CURSO (gris)
                        if (($row['concepto'] ?? '') === 'TOTAL DEL CURSO') {
                            return ['bg'=>'F0F0F0', 'bold'=>true];
                        }
                        return null;
                    },
                ]);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida(
                $excel['spreadsheet'],
                formatoControlador::nombreArchivo('Recaudacion_Periodo_' . ($periodo['name'] ?? ''), 'xlsx')
            );

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
        
        
//csv
    public function generar_csv_periodo_recaudacion_controlador(int $periodId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_periodo_controlador($periodId, 3);
            if (!$datos['ok']) die($datos['message']);

            $periodo = $datos['periodo'];
            $items   = $datos['items'];

            $columnas = [
                ['key'=>'curso',    'label'=>'Curso'],
                ['key'=>'tipo',     'label'=>'Tipo'],
                ['key'=>'inscritos','label'=>'Inscritos',       'formato'=>'int'],
                ['key'=>'concepto', 'label'=>'Concepto'],
                ['key'=>'esp_unit', 'label'=>'Esperado unitario','formato'=>'money_dec'],
                ['key'=>'esp_tot',  'label'=>'Esperado total',  'formato'=>'money_dec'],
                ['key'=>'cobrado',  'label'=>'Cobrado',         'formato'=>'money_dec'],
                ['key'=>'difer',    'label'=>'Diferencia',      'formato'=>'money_dec'],
                ['key'=>'pagos',    'label'=>'Cantidad de pagos','formato'=>'int'],
            ];

            $filas = [];
            foreach ($items as $it) {
                $nombreCurso = $it['course_name'] ?? '';
                $tipoCurso   = $it['course_type'] ?? '';
                $rec         = $it['recaudacion'] ?? [];
                $inscritos   = (int)($rec['inscritos'] ?? 0);
                $conceptos   = $rec['items'] ?? [];

                if (empty($conceptos)) {
                    $filas[] = [
                        'curso'=>$nombreCurso, 'tipo'=>$tipoCurso, 'inscritos'=>$inscritos,
                        'concepto'=>'Sin conceptos', 'esp_unit'=>'', 'esp_tot'=>'',
                        'cobrado'=>'', 'difer'=>'', 'pagos'=>'',
                    ];
                    continue;
                }
                foreach ($conceptos as $c) {
                    $espTot = (float)($c['esperado_total'] ?? 0);
                    $cob    = (float)($c['cobrado']        ?? 0);
                    $filas[] = [
                        'curso'    => $nombreCurso,
                        'tipo'     => $tipoCurso,
                        'inscritos'=> $inscritos,
                        'concepto' => $c['motive'] ?? '',
                        'esp_unit' => (float)($c['esperado_unitario'] ?? 0),
                        'esp_tot'  => $espTot,
                        'cobrado'  => $cob,
                        'difer'    => $espTot - $cob,
                        'pagos'    => (int)($c['count_pagos'] ?? 0),
                    ];
                }
            }

            $out = formatoControlador::csvIniciar(
                formatoControlador::nombreArchivo('Recaudacion_Periodo_' . ($periodo['name'] ?? ''), 'csv')
            );
            formatoControlador::csvContexto($out, [
                'RECAUDACIÓN POR CURSO',
                'PERÍODO: ' . ($periodo['name'] ?? '') . (!empty($periodo['type']) ? ' (' . $periodo['type'] . ')' : ''),
                'Fechas: '  . ($periodo['start_date'] ?? '') . ' al ' . ($periodo['end_date'] ?? '') . ' | Estado: ' . ($periodo['current_state'] ?? ''),
            ]);
            if (empty($filas)) {
                formatoControlador::csvSinDatos($out, 'No hay cursos en este período.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $filas);
            }
            formatoControlador::csvSalida($out);

        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

                
        
/*@@@@@@@@@@@@@@@@@@@@ aprobados y reprobados de un period course@@@@@@@@@@@@@@@@@@@@@@@*/
        
//pdf
    public function generar_pdf_pc_aprobados_controlador(int $periodCourseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pc_controlador($periodCourseId, 1);
            if (!$datos['ok']) die($datos['message']);

            $curso       = $datos['curso'];
            $data        = $datos['data'];
            $estudiantes = $data['estudiantes'] ?? [];

            $pdf = formatoControlador::pdfIniciar('APROBADOS Y REPROBADOS');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'CURSO:',   'text'=>($curso['course_name'] ?? '') . (!empty($curso['course_type']) ? ' (' . $curso['course_type'] . ')' : '')],
                ['bold'=>'PERÍODO:', 'text'=>($curso['period_name'] ?? '')],
                ['bold'=>'FECHAS:',  'text'=>($curso['start_date'] ?? '') . ' al ' . ($curso['end_date'] ?? '')],
            ]);

            // ── Resumen ──
            $total      = (int)($data['total_inscritos'] ?? 0);
            $aprobados  = (int)($data['aprobados']       ?? 0);
            $reprobados = (int)($data['reprobados']      ?? 0);
            $sinNota    = (int)($data['sin_nota']        ?? 0);
            $pctApr     = $data['porcentaje_aprobados'];

            /*
            formatoControlador::pdfResumen($pdf, [
                ['label'=>'Cursantes',  'valores'=>[$total,      '100']],
                ['label'=>'Aprobados',  'valores'=>[$aprobados,  $pctApr === null ? '—' : number_format((float)$pctApr, 1, '.', ',')]],
                ['label'=>'Reprobados', 'valores'=>[$reprobados, $total > 0 ? round(($reprobados / $total) * 100, 1) : 0]],
                ['label'=>'Sin nota',   'valores'=>[$sinNota,    $total > 0 ? round(($sinNota    / $total) * 100, 1) : 0]],
            ], [80, 30, 30], [
                'header' => ['RESUMEN', 'N', '%'],
            ]);*/

            formatoControlador::pdfResumen($pdf, [
                ['label'=>'Cursantes',  'valores'=>[$total]],
                ['label'=>'Aprobados',  'valores'=>[$aprobados]],
                ['label'=>'Reprobados', 'valores'=>[$reprobados]],
                ['label'=>'Sin nota',   'valores'=>[$sinNota]],
            ], [110, 40], [
                'header' => ['RESUMEN', 'N'],
            ]);
            
            
            $pdf->Ln(4);

            // ── Tabla estudiantes ──
            if (empty($estudiantes)) {
                formatoControlador::pdfSinDatos($pdf, 'No hay estudiantes inscritos en este curso.');
            } else {
                $filas = [];
                //'ci'      => $est['ci'] ?? '',
                foreach ($estudiantes as $i => $est) {
                    $filas[] = [
                        'n'       => $i + 1,
                        'ci'      => mainModel::decryption($est['ci'] ?? ''),
                        'nombre'  => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                        'seccion' => $est['section_name'] ?? '—',
                        'calif'   => ($est['last_qualification'] === null || $est['last_qualification'] === '') ? '—' : $est['last_qualification'],
                        'estado'  => $est['estado'] ?? 'Sin nota',
                    ];
                }

                $columnas = [
                    ['key'=>'n',       'label'=>'Nº',                   'ancho'=>10, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'ci',      'label'=>'C.I',                  'ancho'=>28, 'align'=>'C'],
                    ['key'=>'nombre',  'label'=>'Apellidos y Nombres',  'ancho'=>65, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'seccion', 'label'=>'Sección',              'ancho'=>20, 'align'=>'C'],
                    ['key'=>'calif',   'label'=>'Calificación',         'ancho'=>25, 'align'=>'C'],
                    ['key'=>'estado',  'label'=>'Estado',               'ancho'=>48, 'align'=>'C'],
                ];

                formatoControlador::pdfTabla($pdf, $columnas, $filas, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'estado') return null;
                        switch ($row['estado']) {
                            case 'Aprobado':  return ['fg'=>[0, 130, 0]];
                            case 'Reprobado': return ['fg'=>[200, 0, 0]];
                            default:          return ['fg'=>[120, 120, 120]];
                        }
                    },
                ]);
            }

            // Leyenda
            $pdf->Ln(4);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->Cell(0, 4, formatoControlador::iso(
                'Aprobado: calificación ≥ 10 | Reprobado: calificación < 10 | Sin nota: no tiene calificación final asignada'
            ), 0, 1);

            // Footer
            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo(
                'Aprobados_' . ($curso['course_name'] ?? '') . '_' . ($curso['period_name'] ?? ''),
                'pdf'
            ));

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

        
//excel
    public function generar_excel_pc_aprobados_controlador(int $periodCourseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pc_controlador($periodCourseId, 1);
            if (!$datos['ok']) die($datos['message']);

            $curso       = $datos['curso'];
            $data        = $datos['data'];
            $estudiantes = $data['estudiantes'] ?? [];

            $columnas = [
                ['key'=>'n',       'label'=>'Nº',                   'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',      'label'=>'C.I',                  'align'=>'C'],
                ['key'=>'nombre',  'label'=>'Apellidos y Nombres',  'wrap'=>true],
                ['key'=>'seccion', 'label'=>'Sección',              'align'=>'C'],
                ['key'=>'calif',   'label'=>'Calificación',         'align'=>'C'],
                ['key'=>'estado',  'label'=>'Estado',               'align'=>'C'],
            ];
            $anchos = ['n'=>6, 'ci'=>14, 'nombre'=>35, 'seccion'=>10, 'calif'=>12, 'estado'=>14];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'APROBADOS Y REPROBADOS', 'Aprobados', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            // Resumen
            $total      = (int)($data['total_inscritos'] ?? 0);
            $aprobados  = (int)($data['aprobados']       ?? 0);
            $reprobados = (int)($data['reprobados']      ?? 0);
            $sinNota    = (int)($data['sin_nota']        ?? 0);
            $pctApr     = $data['porcentaje_aprobados'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'CURSO: ' . ($curso['course_name'] ?? '') . (!empty($curso['course_type']) ? ' (' . $curso['course_type'] . ')' : ''),
                'PERÍODO: ' . ($curso['period_name'] ?? '') . ' | Fechas: ' . ($curso['start_date'] ?? '') . ' al ' . ($curso['end_date'] ?? ''),
                'Total inscritos: ' . $total .
                    ' | Aprobados: ' . $aprobados .
                    ' | Reprobados: ' . $reprobados .
                    ' | Sin nota: ' . $sinNota ,
            ], count($columnas));
            // ' | % Aprobados: ' . (($pctApr === null || $pctApr === '') ? 'Sin datos' : $pctApr . '%'),

            // Filas
            if (empty($estudiantes)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay estudiantes en este curso.', count($columnas));
            } else {
                $filas = [];
                //'ci'      => $est['ci'] ?? '',
                foreach ($estudiantes as $i => $est) {
                    $filas[] = [
                        'n'       => $i + 1,
                        'ci'      => mainModel::decryption($est['ci'] ?? ''),
                        'nombre'  => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                        'seccion' => $est['section_name'] ?? '—',
                        'calif'   => ($est['last_qualification'] === null || $est['last_qualification'] === '') ? '—' : (int)$est['last_qualification'],
                        'estado'  => $est['estado'] ?? 'Sin nota',
                    ];
                }
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'estado') return null;
                        switch ($row['estado']) {
                            case 'Aprobado':  return ['bg'=>'C6EFCE', 'fg'=>'006100'];
                            case 'Reprobado': return ['bg'=>'FFC7CE', 'fg'=>'9C0006'];
                            default:          return ['bg'=>'E7E6E6', 'fg'=>'595959'];
                        }
                    },
                ]);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo(
                'Aprobados_' . ($curso['course_name'] ?? '') . '_' . ($curso['period_name'] ?? ''),
                'xlsx'
            ));

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

        
//csv
    public function generar_csv_pc_aprobados_controlador(int $periodCourseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pc_controlador($periodCourseId, 1);
            if (!$datos['ok']) die($datos['message']);

            $curso       = $datos['curso'];
            $data        = $datos['data'];
            $estudiantes = $data['estudiantes'] ?? [];

            $columnas = [
                ['key'=>'n',       'label'=>'Nº',                   'formato'=>'int'],
                ['key'=>'ci',      'label'=>'C.I'],
                ['key'=>'nombre',  'label'=>'Apellidos y Nombres'],
                ['key'=>'seccion', 'label'=>'Sección'],
                ['key'=>'calif',   'label'=>'Calificación'],
                ['key'=>'estado',  'label'=>'Estado'],
            ];

            $filas = [];
			//'ci'      => $est['ci'] ?? '',
            foreach ($estudiantes as $i => $est) {
                $filas[] = [
                    'n'       => $i + 1,
                    'ci'      => mainModel::decryption($est['ci'] ?? ''),
                    'nombre'  => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                    'seccion' => $est['section_name'] ?? '—',
                    'calif'   => ($est['last_qualification'] === null || $est['last_qualification'] === '') ? '—' : (int)$est['last_qualification'],
                    'estado'  => $est['estado'] ?? 'Sin nota',
                ];
            }

            $total      = (int)($data['total_inscritos'] ?? 0);
            $aprobados  = (int)($data['aprobados']       ?? 0);
            $reprobados = (int)($data['reprobados']      ?? 0);
            $sinNota    = (int)($data['sin_nota']        ?? 0);
            $pctApr     = $data['porcentaje_aprobados'];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo(
                'Aprobados_' . ($curso['course_name'] ?? '') . '_' . ($curso['period_name'] ?? ''),
                'csv'
            ));

            formatoControlador::csvContexto($out, [
                'APROBADOS Y REPROBADOS',
                'CURSO: ' . ($curso['course_name'] ?? '') . (!empty($curso['course_type']) ? ' (' . $curso['course_type'] . ')' : ''),
                'PERÍODO: ' . ($curso['period_name'] ?? '') . ' | Fechas: ' . ($curso['start_date'] ?? '') . ' al ' . ($curso['end_date'] ?? ''),
                'Total inscritos: ' . $total .
                    ' | Aprobados: ' . $aprobados .
                    ' | Reprobados: ' . $reprobados .
                    ' | Sin nota: ' . $sinNota ,
            ]);
            // ' | % Aprobados: ' . (($pctApr === null || $pctApr === '') ? 'Sin datos' : $pctApr . '%'),

            if (empty($filas)) {
                formatoControlador::csvSinDatos($out, 'No hay estudiantes en este curso.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $filas);
            }

            formatoControlador::csvSalida($out);

        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
        
       
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ endeudados del periodo @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
        //ESTO ESTA MAL CALCULADO, ASIQUE COMENTE SU ACCESO EN PERIOD UPDATE VIEW
//pdf
    public function generar_pdf_pc_pagos_controlador(int $periodCourseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pc_controlador($periodCourseId, 2);
            if (!$datos['ok']) die($datos['message']);

            $curso       = $datos['curso'];
            $data        = $datos['data'];
            $estudiantes = $data['estudiantes'] ?? [];

            $pdf = formatoControlador::pdfIniciar('ENDEUDADOS Y PAGADOS');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'CURSO:',   'text'=>($curso['course_name'] ?? '') . (!empty($curso['course_type']) ? ' (' . $curso['course_type'] . ')' : '')],
                ['bold'=>'PERÍODO:', 'text'=>($curso['period_name'] ?? '')],
                ['bold'=>'FECHAS:',  'text'=>($curso['start_date'] ?? '') . ' al ' . ($curso['end_date'] ?? '')],
            ]);

            // ── Resumen ──
            $total      = (int)($data['total_inscritos']   ?? 0);
            $pagados    = (int)($data['pagados']           ?? 0);
            $endeudados = (int)($data['endeudados']        ?? 0);
            $deudaTotal = (int)($data['monto_total_deuda'] ?? 0);
            $pctPagados = $data['porcentaje_pagados'];

            formatoControlador::pdfResumen($pdf, [
                ['label'=>'Cursantes',  'valores'=>[$total,      '100']],
                ['label'=>'Pagados',    'valores'=>[$pagados,    $pctPagados === null ? '—' : number_format((float)$pctPagados, 1, '.', ',')]],
                ['label'=>'Endeudados', 'valores'=>[$endeudados, $total > 0 ? round(($endeudados / $total) * 100, 1) : 0]],
                ['label'=>'Deuda total','valores'=>['$' . number_format($deudaTotal, 0, '.', ','), '—']],
            ], [80, 30, 30], [
                'header' => ['RESUMEN', 'N', '%'],
            ]);

            $pdf->Ln(4);

            // ── Tabla estudiantes ──
            if (empty($estudiantes)) {
                formatoControlador::pdfSinDatos($pdf, 'No hay estudiantes inscritos en este curso.');
            } else {
                $filas = [];
                //'ci'      => $est['ci'] ?? '',
                foreach ($estudiantes as $i => $est) {
                    $filas[] = [
                        'n'       => $i + 1,
              	        'ci'      => mainModel::decryption($est['ci'] ?? ''),
                        'nombre'  => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                        'seccion' => $est['section_name'] ?? '—',
                        'deuda'   => (int)($est['deuda'] ?? 0),
                        'estado'  => $est['estado'] ?? 'Pagado',
                    ];
                }

                $columnas = [
                    ['key'=>'n',       'label'=>'Nº',                   'ancho'=>10, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'ci',      'label'=>'C.I',                  'ancho'=>28, 'align'=>'C'],
                    ['key'=>'nombre',  'label'=>'Apellidos y Nombres',  'ancho'=>70, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'seccion', 'label'=>'Sección',              'ancho'=>20, 'align'=>'C'],
                    ['key'=>'deuda',   'label'=>'Deuda',                'ancho'=>30, 'align'=>'C', 'formato'=>'money'],
                    ['key'=>'estado',  'label'=>'Estado',               'ancho'=>38, 'align'=>'C'],
                ];

                formatoControlador::pdfTabla($pdf, $columnas, $filas, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'estado') return null;
                        return $row['estado'] === 'Endeudado'
                            ? ['fg'=>[200, 0, 0]]
                            : ['fg'=>[0, 130, 0]];
                    },
                ]);
            }

            // Leyenda
            $pdf->Ln(4);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->Cell(0, 4, formatoControlador::iso(
                'Pagado: sin deuda pendiente | Endeudado: tiene monto pendiente de pago'
            ), 0, 1);

            // Footer
            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo(
                'Pagos_' . ($curso['course_name'] ?? '') . '_' . ($curso['period_name'] ?? ''),
                'pdf'
            ));

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
        
        
//excel
    public function generar_excel_pc_pagos_controlador(int $periodCourseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pc_controlador($periodCourseId, 2);
            if (!$datos['ok']) die($datos['message']);

            $curso       = $datos['curso'];
            $data        = $datos['data'];
            $estudiantes = $data['estudiantes'] ?? [];

            $columnas = [
                ['key'=>'n',       'label'=>'Nº',                   'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',      'label'=>'C.I',                  'align'=>'C'],
                ['key'=>'nombre',  'label'=>'Apellidos y Nombres',  'wrap'=>true],
                ['key'=>'seccion', 'label'=>'Sección',              'align'=>'C'],
                ['key'=>'deuda',   'label'=>'Deuda',                'align'=>'R', 'formato'=>'money_dec'],
                ['key'=>'estado',  'label'=>'Estado',               'align'=>'C'],
            ];
            $anchos = ['n'=>6, 'ci'=>14, 'nombre'=>35, 'seccion'=>10, 'deuda'=>14, 'estado'=>14];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'ENDEUDADOS Y PAGADOS', 'Pagos', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            // Resumen
            $total      = (int)($data['total_inscritos']   ?? 0);
            $pagados    = (int)($data['pagados']           ?? 0);
            $endeudados = (int)($data['endeudados']        ?? 0);
            $deudaTotal = (int)($data['monto_total_deuda'] ?? 0);
            $pctPagados = $data['porcentaje_pagados'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'CURSO: ' . ($curso['course_name'] ?? '') . (!empty($curso['course_type']) ? ' (' . $curso['course_type'] . ')' : ''),
                'PERÍODO: ' . ($curso['period_name'] ?? '') . ' | Fechas: ' . ($curso['start_date'] ?? '') . ' al ' . ($curso['end_date'] ?? ''),
                'Total inscritos: ' . $total .
                    ' | Pagados: ' . $pagados .
                    ' | Endeudados: ' . $endeudados .
                    ' | Deuda total: $' . number_format($deudaTotal, 0, '.', ',') .
                    ' | % Pagados: ' . (($pctPagados === null || $pctPagados === '') ? 'Sin datos' : $pctPagados . '%'),
            ], count($columnas));

            // Filas
            if (empty($estudiantes)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay estudiantes en este curso.', count($columnas));
            } else {
                $filas = [];
                //'ci'      => $est['ci'] ?? '',
                foreach ($estudiantes as $i => $est) {
                    $filas[] = [
                        'n'       => $i + 1,
              	        'ci'      => mainModel::decryption($est['ci'] ?? ''),
                        'nombre'  => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                        'seccion' => $est['section_name'] ?? '—',
                        'deuda'   => (int)($est['deuda'] ?? 0),
                        'estado'  => $est['estado'] ?? 'Pagado',
                    ];
                }
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'estado') return null;
                        return $row['estado'] === 'Endeudado'
                            ? ['bg'=>'FFC7CE', 'fg'=>'9C0006']
                            : ['bg'=>'C6EFCE', 'fg'=>'006100'];
                    },
                ]);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo(
                'Pagos_' . ($curso['course_name'] ?? '') . '_' . ($curso['period_name'] ?? ''),
                'xlsx'
            ));

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
        
        
//csv
    public function generar_csv_pc_pagos_controlador(int $periodCourseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pc_controlador($periodCourseId, 2);
            if (!$datos['ok']) die($datos['message']);

            $curso       = $datos['curso'];
            $data        = $datos['data'];
            $estudiantes = $data['estudiantes'] ?? [];

            $columnas = [
                ['key'=>'n',       'label'=>'Nº',                   'formato'=>'int'],
                ['key'=>'ci',      'label'=>'C.I'],
                ['key'=>'nombre',  'label'=>'Apellidos y Nombres'],
                ['key'=>'seccion', 'label'=>'Sección'],
                ['key'=>'deuda',   'label'=>'Deuda',                'formato'=>'money_dec'],
                ['key'=>'estado',  'label'=>'Estado'],
            ];

            $filas = [];
            //'ci'      => $est['ci'] ?? '',
            foreach ($estudiantes as $i => $est) {
                $filas[] = [
                    'n'       => $i + 1,
              	    'ci'      => mainModel::decryption($est['ci'] ?? ''),
                    'nombre'  => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                    'seccion' => $est['section_name'] ?? '—',
                    'deuda'   => (int)($est['deuda'] ?? 0),
                    'estado'  => $est['estado'] ?? 'Pagado',
                ];
            }

            $total      = (int)($data['total_inscritos']   ?? 0);
            $pagados    = (int)($data['pagados']           ?? 0);
            $endeudados = (int)($data['endeudados']        ?? 0);
            $deudaTotal = (int)($data['monto_total_deuda'] ?? 0);
            $pctPagados = $data['porcentaje_pagados'];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo(
                'Pagos_' . ($curso['course_name'] ?? '') . '_' . ($curso['period_name'] ?? ''),
                'csv'
            ));

            formatoControlador::csvContexto($out, [
                'ENDEUDADOS Y PAGADOS',
                'CURSO: ' . ($curso['course_name'] ?? '') . (!empty($curso['course_type']) ? ' (' . $curso['course_type'] . ')' : ''),
                'PERÍODO: ' . ($curso['period_name'] ?? '') . ' | Fechas: ' . ($curso['start_date'] ?? '') . ' al ' . ($curso['end_date'] ?? ''),
                'Total inscritos: ' . $total .
                    ' | Pagados: ' . $pagados .
                    ' | Endeudados: ' . $endeudados .
                    ' | Deuda total: $' . number_format($deudaTotal, 0, '.', ',') .
                    ' | % Pagados: ' . (($pctPagados === null || $pctPagados === '') ? 'Sin datos' : $pctPagados . '%'),
            ]);

            if (empty($filas)) {
                formatoControlador::csvSinDatos($out, 'No hay estudiantes en este curso.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $filas);
            }

            formatoControlador::csvSalida($out);

        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
}

?>