<?php

if($peticionAjax){
    require_once "../modelos/seccionModelo.php";
}else{
    require_once "./modelos/seccionModelo.php";
}


//seccion new
//seccion consult
//seccion consult archived
//evaluacion new
//evaluacion update
class seccionControlador extends seccionModelo{

//datos para seccion new, trae periiodos, period courses y cursos
    public function datos_para_nueva_seccion_controlador(
        ?int $periodCourseId = null,
        ?int $periodId = null
    ): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }

        if ($periodCourseId === null && $periodId === null) {
            return ['ok' => false, 'message' => 'Debe especificar un período o un curso del período'];
        }

        $r = seccionModelo::datos_nueva_seccion_supabase($periodId, $periodCourseId, $token);

        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }

        return [
            'ok'      => true,
            'modo'    => $r['data']['modo'],
            'periodo' => $r['data']['periodo'],
            'cursos'  => $r['data']['cursos'],
        ];
    }


   
    
    
    
// no tiene logica de encuentros
    public function crear_seccion_controlador(): string {
        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            // ── Detectar modo ──
            $periodCourseId = (int) ($_POST['period_course_id_crear_s'] ?? 0);
            $periodId       = (int) ($_POST['period_id_crear_pc'] ?? 0);
            $courseId       = (int) ($_POST['course_id_crear_pc'] ?? 0);

            $sectionName = trim(mainModel::limpiar_cadena($_POST['section_name'] ?? ''));
            $sectionDay  = trim(mainModel::limpiar_cadena($_POST['section_day'] ?? ''));
            $teacherId   = (int) ($_POST['teacher_id'] ?? 0);

            // ── Validaciones básicas ──
            if ($sectionName === '' || $sectionDay === '' || $teacherId <= 0) {
                return json_encode(mainModel::generarAlertaSimple('Todos los campos son obligatorios', 'error', 'simple'));
            }

            if (!preg_match('/^[A-Z]$/', $sectionName)) {
                return json_encode(mainModel::generarAlertaSimple('Nombre de sección inválido', 'error', 'simple'));
            }

            // ── Determinar modo ──
            $modo = null;
            if ($periodCourseId > 0) {
                $modo = 'period_course';
            } elseif ($periodId > 0 && $courseId > 0) {
                $modo = 'period';
            } else {
                return json_encode(mainModel::generarAlertaSimple(
                    'Parámetros insuficientes: debe indicar un curso del período o un período + curso',
                    'error', 'simple'
                ));
            }

            // ── Llamar a la RPC transaccional ──
            $resultado = seccionModelo::crear_period_course_y_seccion_supabase(
                $modo === 'period_course' ? $periodCourseId : null,
                $modo === 'period'        ? $periodId       : null,
                $modo === 'period'        ? $courseId       : null,
                $sectionName,
                $sectionDay,
                $teacherId,
                $token
            );

            $rpcData = $resultado['data'] ?? [];

            if (($resultado['ok'] ?? false) && ($rpcData['ok'] ?? false)) {
                $esNuevoPC = (bool) ($rpcData['period_course_new'] ?? false);
                $mensaje = $esNuevoPC
                    ? ' Curso del período y sección creados exitosamente'
                    : ' Sección creada exitosamente';

                return json_encode(mainModel::generarAlertaSimple($mensaje, 'success', 'recargar'));
            }

            $mensaje = $rpcData['error'] ?? $resultado['message'] ?? 'Error desconocido';
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $mensaje, 'error', 'simple'));

        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
    
        
/**
 * Actualiza el profesor y el día de una sección.
 * Espera: id_seccion_up, profesor_up, seccion_dia_up
 */
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

            //Verificar que el periodo NO esté Terminado
            $estadoPeriodo = seccionModelo::obtener_estado_periodo_de_seccion_supabase(
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

            $resultado = seccionModelo::actualizar_seccion_supabase($sectionId, $data, $token);

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
        
        


/**
 * Elimina una sección que no tenga inscritos. para casos donde haya una seccion que sobre
 * Redirige a la lista de secciones archivadas (state=0).
     */
    public function eliminar_seccion_controlador(): string{
        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $cargo = $_SESSION['cargo_spm'] ?? 0;
            if (!in_array($cargo, [1, 4])) {
                return json_encode(mainModel::generarAlertaSimple('No tienes permisos para eliminar secciones', 'error', 'simple'));
            }

            $id = $_POST['id_seccion_eliminar'] ?? '';
            if (empty($id)) {
                return json_encode(mainModel::generarAlertaSimple('ID no proporcionado', 'error', 'simple'));
            }

            if (is_numeric($id)) {
                $idReal = (int) $id;
            } else {
                $idReal = mainModel::decryption($id);
                $idReal = (int) $idReal;
            }

            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $resultado = seccionModelo::eliminar_seccion_supabase($idReal, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
       
                $carpeta =  'admin';
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

    
//obtener datos para seccion consult
    public function obtener_datos_para_seccion_consulta(int $sectionId, int $state): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }

        $resultadoSeccion = seccionModelo::obtener_seccion_por_id($sectionId, $token, $state);

        if (!($resultadoSeccion['ok'] ?? false) || empty($resultadoSeccion['data']['section'])) {
            return ['ok' => false, 'message' => 'Sección no encontrada'];
        }

        $seccion = $resultadoSeccion['data']['section'];
        $teacher = $seccion['teacher'] ?? null;

        $evaluacionesActivas = $resultadoSeccion['data']['evaluations'] ?? [];
        $evaluacionesCount   = count($evaluacionesActivas);

        $totalPorcentaje = 0.0;
        foreach ($evaluacionesActivas as $eval) {
            $totalPorcentaje += (float) ($eval['percentage'] ?? 0);
        }

        return [
            'ok'                 => true,
            'seccion'            => $seccion,
            'period'             => $seccion['period_course']['period'] ?? [],
            'course'             => $seccion['period_course']['course'] ?? [],
            'teacher'            => $teacher,
            'evaluaciones'       => $evaluacionesActivas,  
            'evaluaciones_count' => $evaluacionesCount,
            'total_porcentaje'   => $totalPorcentaje,
        ];
    }


    public function tabla_evaluaciones_controlador(array $evaluaciones, int $sectionId, int $state = 1): string {
        $cargo   = (int) ($_SESSION['cargo_spm'] ?? 0);
        $carpeta = 'admin';

        $total = count($evaluaciones);

        $html  = '<div class="table-responsive">';
        // Si hay más de 10 evaluaciones, aplicamos scroll vertical
        $html .= $total > 10
            ? '<div style="max-height: 500px; overflow-y: auto;">'
            : '';

        $html .= '<table class="table table-dark table-sm"><thead><tr class="text-center">';
        $html .= '<th>ID</th><th>Tipo</th><th>Objetivo</th><th>Porcentaje</th><th>Fecha</th>';
        if (in_array($cargo, [1, 2], true)) {
            $html .= '<th>Acciones</th>';
        }
        $html .= '</tr></thead><tbody>';

        if ($total > 0) {
            foreach ($evaluaciones as $eval) {
                $id    = (int) $eval['id'];
                $encId = mainModel::encryption($id);

                $html .= '<tr class="text-center">';
                $html .= '<td>' . $id . '</td>';
                $html .= '<td>' . htmlspecialchars($eval['evaluation_type'] ?? '', ENT_QUOTES, 'UTF-8') . '</td>';

                $objRaw = $eval['objective'] ?? null;
                if ($objRaw !== null && $objRaw !== '') {
                    $objFmt = (floor((float) $objRaw) == (float) $objRaw)
                        ? (string) (int) $objRaw
                        : rtrim(rtrim(number_format((float) $objRaw, 4, '.', ''), '0'), '.');
                } else {
                    $objFmt = '';
                }
                $html .= '<td>' . htmlspecialchars($objFmt, ENT_QUOTES, 'UTF-8') . '</td>';

                $html .= '<td>' . (int) ($eval['percentage'] ?? 0) . '%</td>';

                $fechaRaw = $eval['evaluation_date'] ?? '';
                $fecha    = '';
                if ($fechaRaw !== '') {
                    try {
                        $dt = new DateTime($fechaRaw);
                        $fecha = $dt->format('d/m/Y');
                    } catch (Exception $e) {
                        $fecha = $fechaRaw;
                    }
                }
                $html .= '<td>' . htmlspecialchars($fecha, ENT_QUOTES, 'UTF-8') . '</td>';

                if (in_array($cargo, [1, 2], true)) {
                    $html .= '<td>';
                    if ($state == 1) {
                        $html .= '
                            <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/evaluacion-update&evaluacion_id_up=' . $encId . '"
                               class="btn btn-success btn-sm" title="Actualizar">
                                <i class="fas fa-edit"></i>
                            </a>
                            <form class="FormularioAjax d-inline" action="' . SERVERURL . 'ajax/seccionAjax.php" method="POST" data-form="delete" data-entity="evaluacion">
                                <input type="hidden" name="id_evaluacion_archivar" value="' . $encId . '">
                                <button type="submit" class="btn btn-warning btn-sm" title="Archivar">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                        ';
                    } else {
                        $html .= '
                            <form class="FormularioAjax d-inline" action="' . SERVERURL . 'ajax/seccionAjax.php" method="POST" data-form="restore" data-entity="evaluacion">
                                <input type="hidden" name="id_evaluacion_restaurar" value="' . $encId . '">
                                <button type="submit" class="btn btn-info btn-sm" title="Restaurar">
                                    <i class="fas fa-undo"></i>
                                </button>
                            </form>
                        ';
                        
							/*<form class="FormularioAjax d-inline" action="' . SERVERURL . 'ajax/seccionAjax.php" method="POST" data-form="delete" data-entity="seccion_archivada" autocomplete="off">
                                <input type="hidden" name="id_seccion_eliminar" value="' . (int) $sectionId . '">
                                <button type="submit" class="btn btn-sm btn-danger" title="Eliminar sección">
                                    <i class="fas fa-trash-alt"></i>
                                </button>
                            </form>*/
                    }
                    $html .= '</td>';
                }
                $html .= '</tr>';
            }
        } else {
            $colspan = in_array($cargo, [1, 2], true) ? 6 : 5;
            $html .= '<tr><td colspan="' . $colspan . '" class="text-center">No hay evaluaciones registradas.</td></tr>';
        }

        $html .= '</tbody></table>';

        // Cerrar el div del scroll (si se abrió)
        if ($total > 10) {
            $html .= '</div>';
        }

        $html .= '</div>';

        return $html;
    }
    
    
    
    
        
        /**
         * Controlador para transferir estudiantes entre secciones
         * Espera POST: id_seccion_origen, id_seccion_destino (pueden ser encriptados)
         */
    public function transferir_estudiantes_seleccionados_controlador(): string{
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

            $resultado = seccionModelo::transferir_estudiantes_seleccionados_supabase(
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
        
        
//carga los estudiantes disponibles en una seccion al cargar el modal de transferirlos
    public function obtener_datos_transferencia_ajax(): void{
        $sectionId = $_POST['section_id'] ?? 0;
        if (!$sectionId) {
            echo json_encode(['ok' => false, 'error' => 'ID de sección no proporcionado']);
            return;
        }
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            echo json_encode(['ok' => false, 'error' => 'Sesión no disponible']);
            return;
        }
        $resultado = seccionModelo::obtener_datos_transferencia_supabase((int) $sectionId, $token);
        echo json_encode($resultado);
    }
    
    
/*----------------------------------------------a partir de aqui empiezan las evaluaciones--------------------------------------------------*/

    public function datos_para_evaluacion_new(int $sectionId): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }

        // Caso C: solo section_id, state=2
        $resultado = seccionModelo::datos_evaluacion_new_supabase($sectionId, $token);

        if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
            $error = $resultado['data']['error'] ?? $resultado['message'] ?? 'No se pudieron cargar los datos de la sección';
            return ['ok' => false, 'message' => $error];
        }

        $data = $resultado['data'];

        $seccion     = $data['section']   ?? null;
        $estudiantes = $data['students']  ?? [];

        if (!$seccion) {
            return ['ok' => false, 'message' => 'Sección no encontrada'];
        }

        // Counters (vienen en el top-level y en el section)
        $totalActivas         = (int)   ($data['evaluation_count']     ?? 0);
        $sumaPorcentajes      = (float) ($data['percentage_sum']       ?? 0);
        $porcentajeDisponible = (float) ($data['percentage_remaining'] ?? (100 - $sumaPorcentajes));
        $maxEvaluaciones      = (int)   ($seccion['max_active_evaluations'] ?? 6);
        $limiteAlcanzado      = ($totalActivas >= $maxEvaluaciones);

        // Contexto
        $periodCourse = $seccion['period_course'] ?? [];
        $period       = $periodCourse['period']   ?? [];
        $course       = $periodCourse['course']   ?? [];
        $teacher      = $seccion['teacher']       ?? null;

        return [
            'ok'                         => true,
            'seccion'                    => $seccion,
            'period'                     => $period,
            'course'                     => $course,
            'teacher'                    => $teacher,
            'evaluaciones_activas'       => [],   // Caso C no trae evaluaciones
            'total_evaluaciones_activas' => $totalActivas,
            'suma_porcentajes'           => $sumaPorcentajes,
            'porcentaje_disponible'      => $porcentajeDisponible,
            'limite_alcanzado'           => $limiteAlcanzado,
            'max_evaluaciones'           => $maxEvaluaciones,
            'estudiantes'                => $estudiantes,
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
            if ($puntajesJson === false || $puntajesJson === 'null') {
                echo json_encode(mainModel::generarAlertaSimple('Error al codificar los puntajes', 'error', 'simple'));
                return;
            }

            $datos = [
                'section_id' => $sectionId,
                'evaluation_type' => $name,
                'objective' => $objective,
                'percentage' => $percentage,
                'evaluation_date' => $evaluationDate,
                'puntajes' => $puntajes
            ];

            $resultado = seccionModelo::crear_evaluacion_con_puntajes_supabase($datos, $token);


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
        
        


    public function actualizar_evaluacion_controlador(): void{
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

            $resultado = seccionModelo::actualizar_evaluacion_con_puntajes_supabase($datos, $token);

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
        

    public function datos_para_evaluacion_update_controlador(int $evaluationId): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }

        $resultado = seccionModelo::datos_evaluacion_update_supabase($evaluationId, $token);

        if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
            $error = $resultado['data']['error'] ?? $resultado['message'] ?? 'Evaluación no encontrada';
            return ['ok' => false, 'message' => $error];
        }

        $data = $resultado['data'];

        $seccion      = $data['section']     ?? null;
        $evaluaciones = $data['evaluations'] ?? [];
        $estudiantes  = $data['students']    ?? [];

        // La evaluación es el primer (y único) elemento
        $evaluacion = $evaluaciones[0] ?? null;

        if (!$seccion || !$evaluacion) {
            return ['ok' => false, 'message' => 'Datos incompletos de la evaluación'];
        }

        // Mapa de puntajes por registration_id (ya vienen embebidos en cada student)
        $puntajesMap = [];
        foreach ($estudiantes as $est) {
            $puntajesMap[(int) $est['registration_id']] = $est['score'] ?? null;
        }

        // Contexto
        $periodCourse = $seccion['period_course'] ?? [];
        $period       = $periodCourse['period']   ?? [];

        $sumaTotal        = (float) ($seccion['active_percentage_sum'] ?? 0);
        $porcentajeActual = (float) ($evaluacion['percentage']         ?? 0);
        $sumaExcluyendo   = max(0, $sumaTotal - $porcentajeActual);

        return [
            'ok'                => true,
            'evaluacion'        => $evaluacion,
            'seccion'           => $seccion,
            'estudiantes'       => $estudiantes,
            'puntajes_map'      => $puntajesMap,
            'period_start'      => $period['start_date'] ?? '',
            'period_end'        => $period['end_date']   ?? '',
            'suma_excluyendo'   => $sumaExcluyendo,
            'porcentaje_actual' => $porcentajeActual,
        ];
    }


    /**
     * Paginador de evaluaciones (activas o archivadas)
     * @param int $pagina Número de página
     * @param int $registros Registros por página
     * @param int $sectionId ID de la sección
     * @param int $state 1: activas, 0: archivadas
     *
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

        $resultado = seccionModelo::listar_evaluaciones_por_seccion_supabase(
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

        $totalResult = seccionModelo::contar_evaluaciones_por_seccion_supabase(
            $sectionId,
            $token,
            $state
        );
        $total = $totalResult['ok'] ? (int) ($totalResult['data'][0]['count'] ?? 0) : 0;
        $paginas = max(1, ceil($total / $limit));

        // Datos del usuario (cargo, carpeta)
        $cargo = $_SESSION['cargo_spm'] ?? 0;
        $carpeta = 'admin';
        
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

        return $html;
    }        */
        


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

        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        $limit = max(1, $registros);
        $offset = ($pagina - 1) * $limit;

        $resultado = seccionModelo::listar_estudiantes_seccion_consulta_supabase(
            $sectionId, $limit, $offset, $state, $cargo, $token
        );

        if (!$resultado['ok'] || !isset($resultado['data']['ok']) || !$resultado['data']['ok']) {
            return '<div class="alert alert-danger">Error al cargar datos: ' . ($resultado['data']['error'] ?? '') . '</div>';
        }

        $data         = $resultado['data'];
        $estudiantes  = $data['estudiantes']  ?? [];
        $evaluaciones = $data['evaluaciones'] ?? [];
        $puntajes     = $data['puntajes']     ?? [];
        $periodCosts  = $data['period_costs'] ?? [];
        $pagos        = $data['pagos']        ?? [];
        $total        = (int) ($data['total_estudiantes'] ?? 0);
        $paginas      = max(1, ceil($total / $limit));

        $puntajesMap = [];
        foreach ($puntajes as $p) {
            $puntajesMap[$p['registration_id']][$p['evaluation_id']] = $p['score'];
        }

        $pagosMap = [];
        foreach ($pagos as $p) {
            $pagosMap[(int) $p['registration_id']][$p['motive']] = (int) $p['pagado'];
        }

        $mostrarPagos = in_array($cargo, [1, 2], true) && !empty($periodCosts);

        $baseCols      = 4;
        $evalCols      = count($evaluaciones);
        $notaFinalCols = 1;
        $pagoCols      = $mostrarPagos ? count($periodCosts) : 0;
        $totalCols     = $baseCols + $evalCols + $notaFinalCols + $pagoCols;

        $html  = '<style>
            .tbl-estudiantes th.col-pago { font-size: 10px !important; padding: 4px 2px !important; }
            .tbl-estudiantes th.col-pago small { font-size: 9px !important; }
            .tbl-estudiantes td.col-pago { font-size: 10px !important; padding: 3px 2px !important; }
        </style>';

        $html .= '<div class="table-responsive" style="max-height:500px; overflow:auto;">';
        $html .= '<table class="table table-dark table-sm table-bordered tbl-estudiantes" style="white-space:nowrap; table-layout:auto; width:100%;">';
        $html .= '<thead><tr class="text-center">';
        $html .= '<th>ID</th><th>Cédula</th><th>Nombre</th><th>Apellido</th>';

        foreach ($evaluaciones as $eval) {
            $tipo = htmlspecialchars($eval['evaluation_type'] ?? 'Evaluación');
            $obj  = htmlspecialchars((string) ($eval['objective'] ?? ''));
            $html .= "<th>{$tipo}<br><small>(Obj: {$obj})</small></th>";
        }
        
        
        if($state==1){$html .= '<th>Nota final</th>';}
        
        if ($mostrarPagos) {
            foreach ($periodCosts as $pc) {
                $motive = $pc['motive'];
                if (preg_match('/^pago_encuentro(\d+)$/', $motive, $m)) {
                    $label = 'Encuentro ' . $m[1];
                } else {
                    $label = ucfirst(str_replace('_', ' ', str_replace('pago_', '', $motive)));
                }
                $label = htmlspecialchars($label);
                $price = number_format((float) $pc['price'], 0, '.', ',');
                $html .= "<th class='col-pago'>{$label}<br><small>\${$price}</small></th>";
            }
        }
        if (in_array($cargo, [1, 2, 3], true) && $state==1) {             
            if ($cargo != 3) {$html .= "<th class='col-pago'><small>Reporte de pagos</small></th>";}
            $html .= "<th class='col-pago'><small>Reporte de notas</small></th>";
        }

        $html .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = $offset + 1;
            $hoy = date('Y-m-d');

            foreach ($estudiantes as $est) {
                $studentId      = $est['student_id'] ?? '';
                //$ci             = htmlspecialchars($est['ci'] ?? '');
                $ci             = htmlspecialchars( mainModel::decryption($est['ci'] ?? ''));
                $name           = htmlspecialchars($est['name'] ?? '');
                $lastName       = htmlspecialchars($est['last_name'] ?? '');
                $registrationId = (int) $est['registration_id'];

                $html .= '<tr class="text-center">';
                $html .= "<td>{$studentId}</td><td>{$ci}</td><td>{$name}</td><td>{$lastName}</td>";

                // ── Evaluaciones + Nota final ──
                if (empty($evaluaciones)) {
                    // Fix: 1 sola celda (colspan ya no se usa)
                    $html .= '<td class="text-muted">-</td>';
                }else {
                    $notaFinal       = 0;
                    $totalPorcentaje = 0;
                    $tienePuntaje    = false;

                    foreach ($evaluaciones as $eval) {
                        $evalId     = $eval['id'];
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

                    if ($totalPorcentaje == 100 && $tienePuntaje) {
                        $notaRedondeada = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);
                        $color = ($notaRedondeada >= 10) ? 'text-success' : 'text-danger';
                        $html .= "<td class='{$color}'><strong>{$notaRedondeada}</strong></td>";
                    }else if($state!=1){
                    
                	}else {
                        $html .= "<td class='text-muted'>-</td>";
                    }
                }

                // ── Pagos ──
                if ($mostrarPagos) {
                    foreach ($periodCosts as $pc) {
                        $motive  = $pc['motive'];
                        $price   = (int) $pc['price'];
                        $dueDate = $pc['due_date'];
                        $pagado  = $pagosMap[$registrationId][$motive] ?? 0;

                        if ($pagado >= $price) {
                            $bg = '#28a745';
                        } elseif ($hoy <= $dueDate) {
                            $bg = '#ffc107';
                        } else {
                            $bg = '#dc3545';
                        }

                        $textColor = ($bg === '#ffc107') ? '#000' : '#fff';
                        // Fix: clase col-pago (sin font-size inline)
                        $html .= "<td class='col-pago' style='background-color:{$bg}; color:{$textColor};'>";
                        $html .= '$' . number_format($pagado, 0, '.', ',');
                        $html .= ' / $' . number_format($price, 0, '.', ',');
                        $html .= '</td>';
                    }
                }


                if (in_array($cargo, [1, 2, 3], true) && $state==1) {
                    if ($cargo != 3) {
                        $html .= '<td>
                            <a href="' . SERVERURL . 'ajax/seccionAjax.php?accion=pdf_pagos_estudiante&registration_id=' . $registrationId . '" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i> </a>
                            <a href="' . SERVERURL . 'ajax/seccionAjax.php?accion=excel_pagos_estudiante&registration_id=' . $registrationId . '" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i> </a>
                            <a href="' . SERVERURL . 'ajax/seccionAjax.php?accion=csv_pagos_estudiante&registration_id=' . $registrationId . '" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </td> ';
                    }
                        $html .= '<td>
                            <a href="' . SERVERURL . 'ajax/seccionAjax.php?accion=pdf_notas_estudiante&registration_id=' . $registrationId . '" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i> </a>
                            <a href="' . SERVERURL . 'ajax/seccionAjax.php?accion=excel_notas_estudiante&registration_id=' . $registrationId . '" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i> </a>
                            <a href="' . SERVERURL . 'ajax/seccionAjax.php?accion=csv_notas_estudiante&registration_id=' . $registrationId . '" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </td>';
                }


                $html .= '</tr>';
            }
            $regFinal = $regInicio + count($estudiantes) - 1;
        } else {
            $html .= "<tr><td colspan='{$totalCols}' class='text-center'>No hay estudiantes inscritos en esta sección.</td></tr>";
        }

        $html .= '</tbody></table></div>';

        if ($mostrarPagos && $total > 0) {
            $html .= '<div class="mt-2" style="font-size:12px;">';
            $html .= '<span class="badge" style="background:#28a745;">Pagado</span> ';
            $html .= '<span class="badge" style="background:#ffc107;color:#000;">Pendiente (≤ 28 días)</span> ';
            $html .= '<span class="badge" style="background:#dc3545;">Atrasado</span>';
            $html .= '</div>';
        }

        if ($total > 0) {
            $html .= '<p class="text-right mt-2">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';
            
            $url = SERVERURL . 'index.php?views=admin/seccion-consult'
             . '&curso_vigente=' . ($_GET['curso_vigente'] ?? 0)
             . '&seccion_id=' . $sectionId              
             . '&archivados=' . (int) ($_GET['archivados'] ?? 1)  
             . '&est_page=';
            
            $html .= mainModel::paginador_tablas($pagina, $paginas, $url, 7);
        }

        return $html;
    }    
        

//archivar evaluacion
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
            $resultado = seccionModelo::archivar_evaluacion_con_puntajes_supabase($idReal, $token);
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

//restaura evaluacion
    public function restaurar_evaluacion_controlador($encryptedId): string{
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
            $resultado = seccionModelo::restaurar_evaluacion_con_puntajes_supabase($idReal, $token);
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
        
        
        
/*--------------------------------------------------------funciones de generar pdf y excel-----------------------------------------------------------------*/

    
    
    public function reporte_pagos_estudiante_controlador(int $registrationId): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        if (!in_array($cargo, [1, 2], true)) {
            return ['ok' => false, 'message' => 'No tiene permisos para este reporte'];
        }

        $r = seccionModelo::reporte_pagos_estudiante_supabase($registrationId, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }

        return [
            'ok'             => true,
            'registration'   => $r['data']['registration']  ?? [],
            'period_costs'   => $r['data']['period_costs']  ?? [],
            'transacciones'  => $r['data']['transacciones'] ?? [],
            'pagos'          => $r['data']['pagos']         ?? [],
        ];
    }
    
    
    public function reporte_notas_estudiante_controlador(int $registrationId): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = seccionModelo::reporte_notas_estudiante_supabase($registrationId, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }

        return [
            'ok'           => true,
            'registration' => $r['data']['registration']  ?? [],
            'evaluations'  => $r['data']['evaluations']   ?? [],
            'grade_summary'=> $r['data']['grade_summary'] ?? [],
        ];
    }
    
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@ NOTAS SECCION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    
//pdf
    public function generar_pdf_notas_seccion_controlador(int $sectionId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $resultado = seccionModelo::datos_seccion_reporte_supabase($sectionId, 3, $token);
            if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
                die("No se pudieron cargar los datos de la sección");
            }

            $data        = $resultado['data'];
            $section     = $data['section']     ?? [];
            $students    = $data['students']    ?? [];
            $scoresMap   = $data['scores_map']  ?? [];
            $evaluations = $data['evaluations'] ?? [];

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

            // ── ¿Sección al 100%? ──
            $maxEval  = 6;
            $evals    = array_slice($evaluations, 0, $maxEval);
            $totalPct = 0.0;
            foreach ($evals as $ev) $totalPct += (float) ($ev['percentage'] ?? 0);
            $seccionCompleta = (abs($totalPct - 100.0) < 0.001);

            // ── Cálculos delegados al helper ──
            $calc = formatoControlador::construirFilasNotas(
                $students, $evals, $scoresMap, $seccionCompleta, $maxEval
            );

            // ── Contexto ──
            $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
            $curso  = $section['period_course']['course']['name'] ?? '';
            $tipo   = $section['period_course']['course']['type'] ?? '';
            $period = $section['period_course']['period']['name'] ?? '';
            $tipoP  = $section['period_course']['period']['type'] ?? '';
            $letra  = $section['name'] ?? '';
            $dia    = $section['section_day'] ?? '';

            // ── PDF ──
            $pdf = formatoControlador::pdfIniciar('CALIFICACIONES DE LA SECCIÓN');

            formatoControlador::pdfContexto($pdf, [
                ['bold' => 'PROFESOR:', 'text' => $prof ?: 'Sin asignar'],
                ['bold' => 'CURSO:',    'text' => $curso . ($tipo ? " ($tipo)" : '')],
                ['bold' => 'PERÍODO:',  'text' => $period . ($tipoP ? " - $tipoP" : '')],
                ['bold' => 'SECCIÓN:',  'text' => $letra . ($dia ? " ($dia)" : '')],
            ]);

            // ── Tabla de evaluaciones ──
            $columnasEval = [
                ['key'=>'n',     'label'=>'N',        'ancho'=>10,  'align'=>'C'],
                ['key'=>'tipo',  'label'=>'Tipo',     'ancho'=>35,  'align'=>'L'],
                ['key'=>'fecha', 'label'=>'Fecha',    'ancho'=>25,  'align'=>'C'],
                ['key'=>'pct',   'label'=>'%',        'ancho'=>15,  'align'=>'C'],
                ['key'=>'obj',   'label'=>'Objetivo', 'ancho'=>110, 'align'=>'C'],
            ];

            $filasEval = [];
            for ($i = 0; $i < $maxEval; $i++) {
                $ev = $evals[$i] ?? null;
                if ($ev === null) {
                    $filasEval[] = ['n'=>$i+1, 'tipo'=>'', 'fecha'=>'', 'pct'=>'', 'obj'=>''];
                    continue;
                }
                $obj    = $ev['objective'] ?? '';
                $objFmt = '';
                if ($obj !== '' && $obj !== null) {
                    $objFmt = (floor((float)$obj) == (float)$obj)
                        ? (string)(int)$obj
                        : rtrim(rtrim(number_format((float)$obj, 4, '.', ''), '0'), '.');
                }
                $filasEval[] = [
                    'n'     => $i + 1,
                    'tipo'  => $ev['evaluation_type'] ?? '',
                    'fecha' => $ev['evaluation_date'] ?? '',
                    'pct'   => isset($ev['percentage']) ? $ev['percentage'] . '%' : '',
                    'obj'   => $objFmt,
                ];
            }
            formatoControlador::pdfTabla($pdf, $columnasEval, $filasEval);
            $pdf->Ln(4);

            // ── Tabla de estudiantes (columnas dinámicas) ──
            $columnasEst = [
                ['key'=>'n',      'label'=>'N',                   'ancho'=>8,  'align'=>'C'],
                ['key'=>'ci',     'label'=>'C.I',                 'ancho'=>20, 'align'=>'C'],
                ['key'=>'nombre', 'label'=>'Apellidos y Nombres', 'ancho'=>42, 'align'=>'L'],
            ];
            for ($i = 0; $i < $maxEval; $i++) {
                $columnasEst[] = ['key'=>"t$i", 'label'=>'T', 'ancho'=>8, 'align'=>'C'];
                $columnasEst[] = ['key'=>"p$i", 'label'=>'%', 'ancho'=>8, 'align'=>'C'];
            }
            $columnasEst[] = ['key'=>'acum',  'label'=>'Acum',  'ancho'=>10, 'align'=>'C'];
            $columnasEst[] = ['key'=>'calif', 'label'=>'Calif', 'ancho'=>10, 'align'=>'C'];
            $columnasEst[] = ['key'=>'def',   'label'=>'Def',   'ancho'=>10, 'align'=>'C'];

            formatoControlador::pdfTabla($pdf, $columnasEst, $calc['filas']);
            $pdf->Ln(4);

            // ── Resumen ──
            $res = $calc['resumen'];
            formatoControlador::pdfResumen($pdf, [
                ['label'=>'Cursantes',  'valores'=>[$res['total'],      '100']],
                ['label'=>'Aprobados',  'valores'=>[$res['aprobados'],  $res['pct_aprobados']]],
                ['label'=>'Reprobados', 'valores'=>[$res['reprobados'], $res['pct_reprobados']]],
            ], [80, 30, 30], [
                'header' => ['RESUMEN', 'N', '%'],
            ]);

            // ── Footer ──
            formatoControlador::pdfFooter($pdf, [
                'ubicacion' => $direccion,
                'firmas'    => [
                    ['nombre' => $prof ?: 'Profesor', 'cargo' => 'Profesor de la asignatura'],
                ],
            ]);

            formatoControlador::pdfSalida(
                $pdf,
                formatoControlador::nombreArchivo('Notas_' . $letra, 'pdf')
            );

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    
//excel
    public function generar_excel_notas_seccion_controlador(int $sectionId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $resultado = seccionModelo::datos_seccion_reporte_supabase($sectionId, 3, $token);
            if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
                die("No se pudieron cargar los datos de la sección");
            }

            $data        = $resultado['data'];
            $section     = $data['section']     ?? [];
            $students    = $data['students']    ?? [];
            $scoresMap   = $data['scores_map']  ?? [];
            $evaluations = $data['evaluations'] ?? [];

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

            $maxEval  = 6;
            $evals    = array_slice($evaluations, 0, $maxEval);
            $totalPct = 0.0;
            foreach ($evals as $ev) $totalPct += (float) ($ev['percentage'] ?? 0);
            $seccionCompleta = (abs($totalPct - 100.0) < 0.001);

            $calc = formatoControlador::construirFilasNotas(
                $students, $evals, $scoresMap, $seccionCompleta, $maxEval
            );

            $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
            $curso  = $section['period_course']['course']['name'] ?? '';
            $tipo   = $section['period_course']['course']['type'] ?? '';
            $period = $section['period_course']['period']['name'] ?? '';
            $tipoP  = $section['period_course']['period']['type'] ?? '';
            $letra  = $section['name'] ?? '';
            $dia    = $section['section_day'] ?? '';

            // ── Columnas dinámicas ──
            $columnas = [
                ['key'=>'n',      'label'=>'Nº',  'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',     'label'=>'C.I', 'align'=>'C'],
                ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
            ];
            $anchos = ['n'=>6, 'ci'=>14, 'nombre'=>35];

            for ($i = 0; $i < $maxEval; $i++) {
                $columnas[] = ['key'=>"t$i", 'label'=>'T', 'align'=>'C'];
                $columnas[] = ['key'=>"p$i", 'label'=>'%', 'align'=>'C'];
                $anchos["t$i"] = 5;
                $anchos["p$i"] = 5;
            }
            $columnas[] = ['key'=>'acum',  'label'=>'Acum',        'align'=>'C'];
            $columnas[] = ['key'=>'calif', 'label'=>'Calificación','align'=>'C'];
            $columnas[] = ['key'=>'def',   'label'=>'Definitiva',  'align'=>'C'];
            $anchos['acum']  = 8;
            $anchos['calif'] = 8;
            $anchos['def']   = 8;

            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            // ── Excel ──
            $excel = formatoControlador::excelIniciar(
                'CALIFICACIONES DE LA SECCIÓN',
                'Notas',
                count($columnas),
                $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            // ── Contexto ──
            $contexto = [
                'CENTRO EDUCATIVO PROFESIONAL "CECILIO ACOSTA"',
                'PROFESOR: ' . ($prof ?: 'Sin asignar'),
                'CURSO: ' . $curso . ($tipo ? " ($tipo)" : ''),
                'PERÍODO: ' . $period . ($tipoP ? " - $tipoP" : '') . ' | SECCIÓN: ' . $letra . ($dia ? " ($dia)" : ''),
            ];

            // Detalle de cada evaluación como línea de contexto
            foreach ($evals as $i => $ev) {
                $obj    = $ev['objective'] ?? '';
                $objFmt = '';
                if ($obj !== '' && $obj !== null) {
                    $objFmt = (floor((float)$obj) == (float)$obj)
                        ? (string)(int)$obj
                        : rtrim(rtrim(number_format((float)$obj, 4, '.', ''), '0'), '.');
                }
                $contexto[] = sprintf(
                    'Estrategia %d — %s | %s | %s%% | Obj: %s',
                    $i + 1,
                    $ev['evaluation_type'] ?? '',
                    $ev['evaluation_date'] ?? '',
                    $ev['percentage'] ?? '',
                    $objFmt
                );
            }

            $fila = formatoControlador::excelContexto($sheet, $fila, $contexto, count($columnas));

            // ── Tabla de estudiantes ──
            $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $calc['filas'], $anchos);
            $fila += 2;

            // ── Resumen (Excel no tiene helper de resumen) ──
            $res    = $calc['resumen'];
            $bgGray = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']]];
            $bold   = ['font' => ['bold' => true]];

            $sheet->setCellValue("A{$fila}", "RESUMEN");
            $sheet->setCellValue("B{$fila}", "N");
            $sheet->setCellValue("C{$fila}", "%");
            $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bgGray);
            $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bold);
            $fila++;

            $sheet->setCellValue("A{$fila}", "Cursantes");
            $sheet->setCellValue("B{$fila}", $res['total']);
            $sheet->setCellValue("C{$fila}", "100");
            $fila++;

            $sheet->setCellValue("A{$fila}", "Aprobados");
            $sheet->setCellValue("B{$fila}", $res['aprobados']);
            $sheet->setCellValue("C{$fila}", $res['pct_aprobados']);
            $fila++;

            $sheet->setCellValue("A{$fila}", "Reprobados");
            $sheet->setCellValue("B{$fila}", $res['reprobados']);
            $sheet->setCellValue("C{$fila}", $res['pct_reprobados']);
            $fila++;

            // ── Footer ──
            formatoControlador::excelFooter($sheet, $fila, [
                'ubicacion' => $direccion,
                'num_cols'  => count($columnas),
                'firmas'    => [
                    ['nombre' => $prof ?: 'Profesor', 'cargo' => 'Profesor de la asignatura'],
                ],
            ]);

            formatoControlador::excelSalida(
                $excel['spreadsheet'],
                formatoControlador::nombreArchivo('Notas_' . $letra, 'xlsx')
            );

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    

//csv
    public function generar_csv_notas_seccion_controlador(int $sectionId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $resultado = seccionModelo::datos_seccion_reporte_supabase($sectionId, 3, $token);
            if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
                die("No se pudieron cargar los datos de la sección");
            }

            $data        = $resultado['data'];
            $section     = $data['section']     ?? [];
            $students    = $data['students']    ?? [];
            $scoresMap   = $data['scores_map']  ?? [];
            $evaluations = $data['evaluations'] ?? [];

            $maxEval  = 6;
            $evals    = array_slice($evaluations, 0, $maxEval);
            $totalPct = 0.0;
            foreach ($evals as $ev) $totalPct += (float) ($ev['percentage'] ?? 0);
            $seccionCompleta = (abs($totalPct - 100.0) < 0.001);

            $calc = formatoControlador::construirFilasNotas(
                $students, $evals, $scoresMap, $seccionCompleta, $maxEval
            );

            $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
            $curso  = $section['period_course']['course']['name'] ?? '';
            $tipo   = $section['period_course']['course']['type'] ?? '';
            $period = $section['period_course']['period']['name'] ?? '';
            $tipoP  = $section['period_course']['period']['type'] ?? '';
            $letra  = $section['name'] ?? 'seccion';
            $dia    = $section['section_day'] ?? '';

            // ── Columnas dinámicas ──
            $columnas = [
                ['key'=>'n',      'label'=>'Nº',  'formato'=>'int'],
                ['key'=>'ci',     'label'=>'C.I'],
                ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
            ];
            for ($i = 0; $i < $maxEval; $i++) {
                $ev     = $evals[$i] ?? null;
                $num    = $i + 1;
                $tipoEv = $ev['evaluation_type'] ?? '';
                $pctEv  = $ev['percentage'] ?? '';
                $columnas[] = ['key'=>"t$i", 'label'=>"Eval{$num} ({$tipoEv} {$pctEv}%) T"];
                $columnas[] = ['key'=>"p$i", 'label'=>"Eval{$num} %"];
            }
            $columnas[] = ['key'=>'acum',  'label'=>'Acumulado'];
            $columnas[] = ['key'=>'calif', 'label'=>'Calificación'];
            $columnas[] = ['key'=>'def',   'label'=>'Definitiva'];

            // ── Salida ──
            $nombre = formatoControlador::nombreArchivo('Notas_' . $letra, 'csv');
            $out    = formatoControlador::csvIniciar($nombre);

            // ── Contexto ──
            $contexto = [
                'CALIFICACIONES DE LA SECCIÓN',
                'PROFESOR: ' . ($prof ?: 'Sin asignar'),
                'CURSO: ' . $curso . ($tipo ? " ($tipo)" : ''),
                'PERÍODO: ' . $period . ($tipoP ? " - $tipoP" : ''),
                'SECCIÓN: ' . $letra . ($dia ? " ($dia)" : ''),
            ];
            foreach ($evals as $i => $ev) {
                $obj    = $ev['objective'] ?? '';
                $objFmt = '';
                if ($obj !== '' && $obj !== null) {
                    $objFmt = (floor((float)$obj) == (float)$obj)
                        ? (string)(int)$obj
                        : rtrim(rtrim(number_format((float)$obj, 4, '.', ''), '0'), '.');
                }
                $contexto[] = sprintf(
                    'Estrategia %d — %s | %s | %s%% | Obj: %s',
                    $i + 1,
                    $ev['evaluation_type'] ?? '',
                    $ev['evaluation_date'] ?? '',
                    $ev['percentage'] ?? '',
                    $objFmt
                );
            }
            formatoControlador::csvContexto($out, $contexto);

            // ── Tabla de estudiantes ──
            formatoControlador::csvTabla($out, $columnas, $calc['filas']);

            // ── Resumen (CSV: fputcsv directo) ──
            $res = $calc['resumen'];
            fputcsv($out, [], ';', '"', '');
            fputcsv($out, ['RESUMEN', 'N', '%'], ';', '"', '');
            fputcsv($out, ['Cursantes',  $res['total'],      '100'],                ';', '"', '');
            fputcsv($out, ['Aprobados',  $res['aprobados'],  $res['pct_aprobados']],  ';', '"', '');
            fputcsv($out, ['Reprobados', $res['reprobados'], $res['pct_reprobados']], ';', '"', '');

            formatoControlador::csvSalida($out);

        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    
    
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@ NOMINA SECCION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    
    
// genera nomina con todos los estudiantes de la seccion en pdf
   public function generar_pdf_nomina_seccion_controlador(int $sectionId) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);

        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) die("Sesión no disponible");

        require_once "formatoreporteControlador.php";

        $datos = seccionModelo::datos_seccion_reporte_supabase($sectionId, 2, $token);
        if (!$datos['ok'] || empty($datos['data']['ok'])) die("No se pudieron cargar los datos");

        $section  = $datos['data']['section']  ?? [];
        $students = $datos['data']['students'] ?? [];

        $curso  = $section['period_course']['course']['name'] ?? '';
        $tipo   = $section['period_course']['course']['type'] ?? '';
        $period = $section['period_course']['period']['name'] ?? '';
        $letra  = $section['name'] ?? '';
        $dia    = $section['section_day'] ?? '';
        $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));

        $filas = [];
        foreach ($students as $i => $e) {
            $filas[] = [
                'n'      => $i + 1,
                'ci'     => mainModel::decryption($e['ci'] ?? ''),
                'nombre' => trim(($e['last_name'] ?? '') . ' ' . ($e['name'] ?? '')),
            ];
        }

        $columnas = [
            ['key' => 'n',      'label' => 'N',        'ancho' => 10,  'align' => 'C', 'formato' => 'int'],
            ['key' => 'ci',     'label' => 'C.I',      'ancho' => 30,  'align' => 'C'],
            ['key' => 'nombre', 'label' => 'Apellidos y Nombres', 'ancho' => 156, 'align' => 'L', 'wrap' => true],
        ];

        $pdf = formatoControlador::pdfIniciar('NÓMINA DE ESTUDIANTES');

        formatoControlador::pdfContexto($pdf, [
            ['bold' => 'PROFESOR:', 'text' => $prof ?: 'Sin asignar'],
            ['bold' => 'CURSO:',    'text' => $curso . ($tipo ? " ($tipo)" : '')],
            ['bold' => 'PERÍODO:',  'text' => $period],
            ['bold' => 'SECCIÓN:',  'text' => $letra . ($dia ? " ($dia)" : '')],
            ['bold' => 'TOTAL:',    'text' => count($students) . ' estudiantes'],
        ]);

        $nombre = formatoControlador::nombreArchivo('Nomina_' . $letra, 'pdf');
        formatoControlador::pdfTabla($pdf, $columnas, $filas);
        formatoControlador::pdfSalida($pdf, $nombre);
    }
 

//generar nomina excel de la seccion
    public function generar_excel_nomina_seccion_controlador(int $sectionId) {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            session_start(['name' => 'SPM']);
        }
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) die("Sesión no disponible");

        require_once "formatoreporteControlador.php";
        $datos = seccionModelo::datos_seccion_reporte_supabase($sectionId, 2, $token);
        if (!$datos['ok'] || empty($datos['data']['ok'])) die("No se pudieron cargar los datos");

        $section  = $datos['data']['section']  ?? [];
        $students = $datos['data']['students'] ?? [];

        $curso  = $section['period_course']['course']['name'] ?? '';
        $tipo   = $section['period_course']['course']['type'] ?? '';
        $period = $section['period_course']['period']['name'] ?? '';
        $letra  = $section['name'] ?? '';
        $dia    = $section['section_day'] ?? '';
        $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));

        // ── Filas ──
        $filas = [];
        //'ci'     => $e['ci'] ?? '',
        foreach ($students as $i => $e) {
            $filas[] = [
                'n'      => $i + 1,
                'ci'     => mainModel::decryption($e['ci'] ?? ''),
                'nombre' => trim(($e['last_name'] ?? '') . ' ' . ($e['name'] ?? '')),
            ];
        }

        // ── Columnas ──
        $columnas = [
            ['key'=>'n',      'label'=>'N',  'align'=>'C', 'formato'=>'int'],
            ['key'=>'ci',     'label'=>'C.I','align'=>'C'],
            ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
        ];

        $anchos = ['n' => 6, 'ci' => 20, 'nombre' => 45];

        $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

        $excel = formatoControlador::excelIniciar(
            'NÓMINA DE ESTUDIANTES',
            'Nómina',
            count($columnas),
            $anchoTotalPx
        );
        $sheet = $excel['sheet'];
        $fila  = $excel['fila'];

        // ── Contexto ──
        $fila = formatoControlador::excelContexto($sheet, $fila, [
            'PROFESOR: ' . ($prof ?: 'Sin asignar'),
            'CURSO: ' . $curso . ($tipo ? " ($tipo)" : ''),
            'PERÍODO: ' . $period,
            'SECCIÓN: ' . $letra . ($dia ? " ($dia)" : ''),
            'Total de estudiantes: ' . count($students),
        ], count($columnas));

        formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos);
        $nombreArchivo = formatoControlador::nombreArchivo('Nomina_' . $letra, 'xlsx');
        formatoControlador::excelSalida($excel['spreadsheet'], $nombreArchivo);
    }
    
    

// genera nómina con todos los estudiantes de la sección en CSV
    public function generar_csv_nomina_seccion_controlador(int $sectionId) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);

        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) die("Sesión no disponible");

        $datos = seccionModelo::datos_seccion_reporte_supabase($sectionId, 2, $token);
        if (!$datos['ok'] || empty($datos['data']['ok'])) die("No se pudieron cargar los datos");

        require_once "formatoreporteControlador.php";

        $section  = $datos['data']['section']  ?? [];
        $students = $datos['data']['students'] ?? [];

        $curso  = $section['period_course']['course']['name'] ?? '';
        $tipo   = $section['period_course']['course']['type'] ?? '';
        $period = $section['period_course']['period']['name'] ?? '';
        $letra  = $section['name'] ?? 'seccion';
        $dia    = $section['section_day'] ?? '';
        $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
        $total  = count($students);

        $filas = [];
        //'ci'     => $e['ci'] ?? '',
        foreach ($students as $i => $e) {
            $filas[] = [
                'n'      => $i + 1,
                'ci'     => mainModel::decryption($e['ci'] ?? ''),
                'nombre' => trim(($e['last_name'] ?? '') . ' ' . ($e['name'] ?? '')),
            ];
        }

        $columnas = [
            ['key'=>'n',      'label'=>'N',                 'formato'=>'int'],
            ['key'=>'ci',     'label'=>'C.I'],
            ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
        ];

        // FIX 2: 'csv' sin punto
        $nombre = formatoControlador::nombreArchivo('Nomina_' . $letra, 'csv');
        $out    = formatoControlador::csvIniciar($nombre);

        formatoControlador::csvContexto($out, [
            'NÓMINA DE ESTUDIANTES',
            'PROFESOR: ' . ($prof ?: 'Sin asignar'),
            'CURSO: '    . $curso . ($tipo ? " ($tipo)" : ''),
            'PERÍODO: '  . $period,
            'SECCIÓN: '  . $letra . ($dia ? " ($dia)" : ''),
            'TOTAL: '    . $total . ' estudiantes',
        ]);

        formatoControlador::csvTabla($out, $columnas, $filas);
        formatoControlador::csvSalida($out);
    }

    
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@ ACTA CALIFICACIONES SECCION @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

    
//pdf
    public function generar_pdf_acta_calificaciones_controlador(int $sectionId) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) die("Sesión no disponible");

        require_once 'formatoreporteControlador.php';

        $resultado = seccionModelo::datos_seccion_reporte_supabase($sectionId, 3, $token);
        if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
            die("No se pudieron cargar los datos de la sección");
        }

        $data        = $resultado['data'];
        $section     = $data['section']     ?? [];
        $students    = $data['students']    ?? [];
        $scoresMap   = $data['scores_map']  ?? [];
        $evaluations = $data['evaluations'] ?? [];

        $jsonLocal    = mainModel::leer_json_local(['contacto']);
        $contactoJSON = $jsonLocal['contacto'] ?? [];
        $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

        // ── ¿Sección completa? ──
        $maxEval = 6;
        $evals   = array_slice($evaluations, 0, $maxEval);
        $totalPct = 0.0;
        foreach ($evals as $ev) $totalPct += (float) ($ev['percentage'] ?? 0);
        $seccionCompleta = (abs($totalPct - 100.0) < 0.001);

        // ── Cálculos ──
        $calc = formatoControlador::construirFilasNotas(
            $students, $evals, $scoresMap, $seccionCompleta, $maxEval
        );

        // ── Contexto ──
        $curso  = $section['period_course']['course']['name'] ?? '';
        $tipo   = $section['period_course']['course']['type'] ?? '';
        $period = $section['period_course']['period']['name'] ?? '';
        $tipoP  = $section['period_course']['period']['type'] ?? '';
        $letra  = $section['name'] ?? '';
        $dia    = $section['section_day'] ?? '';
        $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));

        // ── PDF ──
        $pdf = formatoControlador::pdfIniciar('ACTA DE CALIFICACIONES');

        formatoControlador::pdfContexto($pdf, [
            ['PROFESOR:', $prof ?: 'Sin asignar'],
            ['CURSO:',    $curso . ($tipo ? " ($tipo)" : '')],
            ['PERÍODO:',  $period . ($tipoP ? " - $tipoP" : '')],
            ['SECCIÓN:',  $letra . ($dia ? " ($dia)" : '')],
        ]);

        // ── Tabla de estudiantes ──
        $columnas = [
            ['key'=>'n',      'label'=>'N',                    'ancho'=>12,  'align'=>'C'],
            ['key'=>'ci',     'label'=>'C.I',                  'ancho'=>32,  'align'=>'C'],
            ['key'=>'nombre', 'label'=>'Apellidos y Nombres',  'ancho'=>110, 'align'=>'L'],
            ['key'=>'def',    'label'=>'Calif. Definitiva',    'ancho'=>36,  'align'=>'C'],
        ];

        formatoControlador::pdfTabla($pdf, $columnas, $calc['filas']);

        // ── Resumen ──
        $pdf->Ln(6);
        $res = $calc['resumen'];

        formatoControlador::pdfResumen($pdf, [
            ['label'=>'Cursantes',  'valores'=>[$res['total'],      '100']],
            ['label'=>'Aprobados',  'valores'=>[$res['aprobados'],  $res['pct_aprobados']]],
            ['label'=>'Reprobados', 'valores'=>[$res['reprobados'], $res['pct_reprobados']]],
        ], [50, 30, 30], [
            'header' => ['RESUMEN', 'Cantidad', '%'],
        ]);

        // ── Footer con firma + ubicación ──
        formatoControlador::pdfFooter($pdf, [
            'ubicacion' => $direccion,
            'firmas'    => [
                [
                    'nombre' => $prof ?: 'Profesor',
                    'cargo'  => 'Profesor de la asignatura',
                ],
            ],
        ]);

        formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo('Acta_Calificaciones_' . $letra, 'pdf'));
    }
    
       
//excel
    public function generar_excel_acta_calificaciones_controlador(int $sectionId) {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) die("Sesión no disponible");

        require_once 'formatoreporteControlador.php';

        $resultado = seccionModelo::datos_seccion_reporte_supabase($sectionId, 3, $token);
        if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
            die("No se pudieron cargar los datos de la sección");
        }

        $data        = $resultado['data'];
        $section     = $data['section']     ?? [];
        $students    = $data['students']    ?? [];
        $scoresMap   = $data['scores_map']  ?? [];
        $evaluations = $data['evaluations'] ?? [];

        $jsonLocal    = mainModel::leer_json_local(['contacto']);
        $contactoJSON = $jsonLocal['contacto'] ?? [];
        $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

        $maxEval = 6;
        $evals   = array_slice($evaluations, 0, $maxEval);
        $totalPct = 0.0;
        foreach ($evals as $ev) $totalPct += (float) ($ev['percentage'] ?? 0);
        $seccionCompleta = (abs($totalPct - 100.0) < 0.001);

        // ── Cálculos ──
        $calc = formatoControlador::construirFilasNotas(
            $students, $evals, $scoresMap, $seccionCompleta, $maxEval
        );

        // ── Contexto ──
        $curso  = $section['period_course']['course']['name'] ?? '';
        $tipo   = $section['period_course']['course']['type'] ?? '';
        $period = $section['period_course']['period']['name'] ?? '';
        $tipoP  = $section['period_course']['period']['type'] ?? '';
        $letra  = $section['name'] ?? '';
        $dia    = $section['section_day'] ?? '';
        $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));

        // ── Columnas y anchos ──
        $columnas = [
            ['key'=>'n',      'label'=>'N',                   'align'=>'C'],
            ['key'=>'ci',     'label'=>'C.I',                 'align'=>'C'],
            ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
            ['key'=>'def',    'label'=>'Calif. Definitiva',   'align'=>'C'],
        ];

        $anchos = ['n' => 6, 'ci' => 16, 'nombre' => 50, 'def' => 18];

        $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

        // ── Excel ──
        $excel = formatoControlador::excelIniciar(
            'ACTA DE CALIFICACIONES',
            'Acta',
            count($columnas),
            $anchoTotalPx
        );
        $sheet = $excel['sheet'];
        $fila  = $excel['fila'];

        // Contexto
        $fila = formatoControlador::excelContexto($sheet, $fila, [
            'PROFESOR: ' . ($prof ?: 'Sin asignar'),
            'CURSO: ' . $curso . ($tipo ? " ($tipo)" : ''),
            'PERÍODO: ' . $period . ($tipoP ? " - $tipoP" : ''),
            'SECCIÓN: ' . $letra . ($dia ? " ($dia)" : ''),
        ], count($columnas));

        // Tabla
        $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $calc['filas'], $anchos);
        $fila += 2;

        // Resumen manual (Excel no tiene helper de resumen, es pequeño)
        $res = $calc['resumen'];

        $bgGray = ['fill' => ['fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EEEEEE']]];
        $bold   = ['font' => ['bold' => true]];

        $sheet->setCellValue("A{$fila}", "RESUMEN");
        $sheet->setCellValue("B{$fila}", "Cantidad");
        $sheet->setCellValue("C{$fila}", "%");
        $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bgGray);
        $sheet->getStyle("A{$fila}:C{$fila}")->applyFromArray($bold);
        $fila++;

        $sheet->setCellValue("A{$fila}", "Cursantes");
        $sheet->setCellValue("B{$fila}", $res['total']);
        $sheet->setCellValue("C{$fila}", "100");
        $fila++;

        $sheet->setCellValue("A{$fila}", "Aprobados");
        $sheet->setCellValue("B{$fila}", $res['aprobados']);
        $sheet->setCellValue("C{$fila}", $res['pct_aprobados']);
        $fila++;

        $sheet->setCellValue("A{$fila}", "Reprobados");
        $sheet->setCellValue("B{$fila}", $res['reprobados']);
        $sheet->setCellValue("C{$fila}", $res['pct_reprobados']);
        $fila++;

        // Footer: firma + ubicación
        formatoControlador::excelFooter($sheet, $fila, [
            'ubicacion' => $direccion,
            'num_cols'  => count($columnas),
            'firmas'    => [
                [
                    'nombre' => $prof ?: 'Profesor',
                    'cargo'  => 'Profesor de la asignatura',
                ],
            ],
        ]);

        formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo('Acta_Calificaciones_' . $letra, 'xlsx'));
    }
    
    
    
//@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ ASISTENCIA @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@
    
// lista de asistencia pdf 
    public function generar_pdf_asistencia_seccion_controlador(int $sectionId, int $numFechas = 6) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $resultado = seccionModelo::datos_seccion_reporte_supabase($sectionId, 2, $token);
            if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
                die("No se pudieron cargar los datos de la sección");
            }

            $data     = $resultado['data'];
            $section  = $data['section']  ?? [];
            $students = $data['students'] ?? [];

            $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
            $curso  = $section['period_course']['course']['name'] ?? '';
            $tipo   = $section['period_course']['course']['type'] ?? '';
            $period = $section['period_course']['period']['name'] ?? '';
            $tipoP  = $section['period_course']['period']['type'] ?? '';
            $letra  = $section['name'] ?? '';
            $dia    = $section['section_day'] ?? '';

            // ── Anchos (total útil = 196 mm para Letter con márgenes 10/10) ──
            $anchoNum    = 8;
            $anchoCI     = 22;
            $anchoNombre = 55;
            $anchoFirma  = 35;
            $anchoFecha  = (196 - $anchoNum - $anchoCI - $anchoNombre - $anchoFirma) / $numFechas;

            // ── Columnas dinámicas ──
            $columnas = [
                ['key'=>'n',      'label'=>'N',                   'ancho'=>$anchoNum,    'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',     'label'=>'C.I',                 'ancho'=>$anchoCI,     'align'=>'C'],
                ['key'=>'nombre', 'label'=>'Apellidos y Nombres', 'ancho'=>$anchoNombre, 'align'=>'L'],
            ];
            for ($i = 0; $i < $numFechas; $i++) {
                $columnas[] = ['key'=>"f$i", 'label'=>'F'.($i+1), 'ancho'=>$anchoFecha, 'align'=>'C'];
            }
            $columnas[] = ['key'=>'firma', 'label'=>'Firma', 'ancho'=>$anchoFirma, 'align'=>'C'];

            // ── Filas: solo datos del estudiante; columnas F* y firma vacías ──
            $filas = [];
            //'ci'     => $e['ci'] ?? '',
            foreach ($students as $i => $e) {
                $row = [
                    'n'      => $i + 1,
                    'ci'     => mainModel::decryption($e['ci'] ?? ''),
                    'nombre' => trim(($e['last_name'] ?? '') . ' ' . ($e['name'] ?? '')),
                ];
                for ($j = 0; $j < $numFechas; $j++) $row["f$j"] = '';
                $row['firma'] = '';
                $filas[] = $row;
            }

            // ── PDF ──
            $pdf = formatoControlador::pdfIniciar('LISTA DE ASISTENCIA');

            formatoControlador::pdfContexto($pdf, [
                ['bold' => 'PROFESOR:', 'text' => $prof ?: 'Sin asignar'],
                ['bold' => 'CURSO:',    'text' => $curso . ($tipo ? " ($tipo)" : '')],
                ['bold' => 'PERÍODO:',  'text' => $period . ($tipoP ? " - $tipoP" : '')],
                ['bold' => 'SECCIÓN:',  'text' => $letra . ($dia ? " ($dia)" : '')],
            ]);

            // alto_fila=9mm para poder firmar/escribir a mano
            formatoControlador::pdfTabla($pdf, $columnas, $filas, [
                'alto_header' => 8,
                'alto_fila'   => 9,
            ]);

            $pdf->Ln(4);

            // ── Leyenda ──
            $pdf->SetFont('Arial', 'I', 8);
            $pdf->Cell(0, 5, formatoControlador::iso(
                'Nota: cada estudiante debe firmar al final de su fila. Las columnas F1-F'
                . $numFechas . ' son para la fecha/clase correspondiente.'
            ), 0, 1);

            // ── Firma del profesor (sin ubicación, igual que el original) ──
            formatoControlador::pdfFooter($pdf, [
                'firmas' => [
                    ['nombre' => $prof ?: 'Profesor', 'cargo' => 'Profesor de la asignatura'],
                ],
            ]);

            formatoControlador::pdfSalida(
                $pdf,
                formatoControlador::nombreArchivo('Asistencia_' . $letra, 'pdf')
            );

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }


//excel
    public function generar_excel_asistencia_seccion_controlador(int $sectionId, int $numFechas = 6) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $resultado = seccionModelo::datos_seccion_reporte_supabase($sectionId, 2, $token);
            if (!$resultado['ok'] || empty($resultado['data']['ok'])) {
                die("No se pudieron cargar los datos de la sección");
            }

            $data     = $resultado['data'];
            $section  = $data['section']  ?? [];
            $students = $data['students'] ?? [];

            $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
            $curso  = $section['period_course']['course']['name'] ?? '';
            $tipo   = $section['period_course']['course']['type'] ?? '';
            $period = $section['period_course']['period']['name'] ?? '';
            $tipoP  = $section['period_course']['period']['type'] ?? '';
            $letra  = $section['name'] ?? '';
            $dia    = $section['section_day'] ?? '';

            // ── Columnas dinámicas ──
            $columnas = [
                ['key'=>'n',      'label'=>'N',                   'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',     'label'=>'C.I',                 'align'=>'C'],
                ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
            ];
            $anchos = ['n'=>6, 'ci'=>14, 'nombre'=>30];

            for ($i = 0; $i < $numFechas; $i++) {
                $columnas[] = ['key'=>"f$i", 'label'=>'F'.($i+1), 'align'=>'C'];
                $anchos["f$i"] = 6;
            }
            $columnas[] = ['key'=>'firma', 'label'=>'Firma', 'align'=>'C'];
            $anchos['firma'] = 18;

            // ── Filas ──
            $filas = [];
            //'ci'     => $e['ci'] ?? '',
            foreach ($students as $i => $e) {
                $row = [
                    'n'      => $i + 1,
                    'ci'     => mainModel::decryption($e['ci'] ?? ''),
                    'nombre' => trim(($e['last_name'] ?? '') . ' ' . ($e['name'] ?? '')),
                ];
                for ($j = 0; $j < $numFechas; $j++) $row["f$j"] = '';
                $row['firma'] = '';
                $filas[] = $row;
            }

            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            // ── Excel ──
            $excel = formatoControlador::excelIniciar(
                'LISTA DE ASISTENCIA',
                'Asistencia',
                count($columnas),
                $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            // ── Contexto ──
            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'PROFESOR: ' . ($prof ?: 'Sin asignar'),
                'CURSO: ' . $curso . ($tipo ? " ($tipo)" : ''),
                'PERÍODO: ' . $period . ($tipoP ? " - $tipoP" : ''),
                'SECCIÓN: ' . $letra . ($dia ? " ($dia)" : ''),
            ], count($columnas));

            // ── Tabla ──
            $filaInicioTabla = $fila;
            $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos);
            $filaFinTabla = $fila - 1;

            // Filas altas para escribir a mano (25 pt ≈ 8.8 mm)
            for ($r = $filaInicioTabla + 1; $r <= $filaFinTabla; $r++) {
                $sheet->getRowDimension($r)->setRowHeight(25);
            }

            // ── Leyenda ──
            $colFin = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex(count($columnas));
            $fila++;
            $sheet->setCellValue("A{$fila}",
                'Nota: cada estudiante debe firmar al final de su fila. Las columnas F1-F'
                . $numFechas . ' son para la fecha/clase correspondiente.'
            );
            $sheet->mergeCells("A{$fila}:{$colFin}{$fila}");
            $sheet->getStyle("A{$fila}")->getFont()->setItalic(true)->setSize(8);

            // ── Firma del profesor ──
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'num_cols' => count($columnas),
                'firmas'   => [
                    ['nombre' => $prof ?: 'Profesor', 'cargo' => 'Profesor de la asignatura'],
                ],
            ]);

            // ── Ajustes de impresión ──
            $sheet->getPageSetup()->setOrientation(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            );
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            formatoControlador::excelSalida(
                $excel['spreadsheet'],
                formatoControlador::nombreArchivo('Asistencia_' . $letra, 'xlsx')
            );

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
/*@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@ Pagos de la seccion @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/
    
    public function reporte_pagos_seccion_controlador(int $sectionId): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        if (!in_array($cargo, [1, 2], true)) {
            return ['ok' => false, 'message' => 'No tiene permisos para este reporte'];
        }

        $r = seccionModelo::reportes_pagos_seccion_supabase($sectionId, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }

        return [
            'ok'           => true,
            'section'      => $r['data']['section']      ?? [],
            'students'     => $r['data']['students']     ?? [],
            'period_costs' => $r['data']['period_costs'] ?? [],
            'pagos'        => $r['data']['pagos']        ?? [],
        ];
    }
    
//pdf
    public function generar_pdf_pagos_seccion_controlador(int $sectionId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);

            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pagos_seccion_controlador($sectionId);
            if (!$datos['ok']) die($datos['message']);

            $section     = $datos['section'];
            $students    = $datos['students'];
            $periodCosts = $datos['period_costs'];
            $pagos       = $datos['pagos'];

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

            // ── Mapa de pagos por registration_id (formato que espera el helper en el resumen) ──
            $pagosPorRegistro = [];
            foreach ($pagos as $p) {
                $pagosPorRegistro[(int)$p['registration_id']][$p['motive']] = (int)$p['pagado'];
            }

            // ── Contexto de la sección ──
            $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
            $curso  = $section['period_course']['course']['name'] ?? '';
            $tipo   = $section['period_course']['course']['type'] ?? '';
            $period = $section['period_course']['period']['name'] ?? '';
            $letra  = $section['name'] ?? '';
            $dia    = $section['section_day'] ?? '';

            // ── Etiquetas de cada concepto (una sola vez) ──
            $conceptLabels = [];
            foreach ($periodCosts as $pc) {
                $motive = $pc['motive'];
                $conceptLabels[] = preg_match('/^pago_encuentro(\d+)$/', $motive, $m)
                    ? 'E' . $m[1]
                    : ucfirst(str_replace('_', ' ', str_replace('pago_', '', $motive)));
            }

            // ── Columnas dinámicas ──
            $anchoTotalDisponible = 279 - 16;   // landscape Letter, márgenes 8/8
            $anchoNum    = 8;
            $anchoCI     = 22;
            $anchoNombre = 60;
            $numPagos    = count($periodCosts);
            $anchoPago   = $numPagos > 0
                ? ($anchoTotalDisponible - $anchoNum - $anchoCI - $anchoNombre) / $numPagos
                : 0;

            $columnas = [
                ['key'=>'n',      'label'=>'N',                   'ancho'=>$anchoNum,    'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',     'label'=>'C.I',                 'ancho'=>$anchoCI,     'align'=>'C'],
                ['key'=>'nombre', 'label'=>'Apellidos y Nombres', 'ancho'=>$anchoNombre, 'align'=>'L'],
            ];
            foreach ($periodCosts as $j => $pc) {
                $label = $conceptLabels[$j] . ' ($' . number_format((float)$pc['price'], 0, '.', ',') . ')';
                $columnas[] = ['key'=>"c$j", 'label'=>$label, 'ancho'=>$anchoPago, 'align'=>'C'];
            }

            // ── Filas usando calcularPagosEstudiante por estudiante ──
            $hoy = date('Y-m-d');
            $filas = [];
            $totalEsperado = 0;
            $totalCobrado  = 0;

            foreach ($students as $i => $est) {
                $regId = (int) $est['registration_id'];

                // Sub-mapa del estudiante (formato que espera calcularPagosEstudiante)
                $pagosEst = [];
                foreach ($periodCosts as $pc) {
                    $pagosEst[$pc['motive']] = (int) ($pagosPorRegistro[$regId][$pc['motive']] ?? 0);
                }
                $calcEst = formatoControlador::calcularPagosEstudiante($periodCosts, $pagosEst, $hoy);
                $totalEsperado += $calcEst['total_esperado'];
                $totalCobrado  += $calcEst['total_cobrado'];

                //'ci'     => $est['ci'] ?? '',
                $row = [
                    'n'      => $i + 1,
                    'ci'     => mainModel::decryption($est['ci'] ?? ''),
                    'nombre' => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                ];
                foreach ($calcEst['filas'] as $j => $f) {
                    $row["c$j"] = '$' . $f['pagado'] . '/$' . $f['precio'];
                    $row["e$j"] = $f['estado'];   // estado por concepto (para colorear)
                }
                $filas[] = $row;
            }

            // ── Footer totales ──
            $deuda = $totalEsperado - $totalCobrado;
            $pct   = $totalEsperado > 0 ? round(($totalCobrado / $totalEsperado) * 100, 1) : 0;

            // ── PDF (landscape) ──
            $pdf = formatoControlador::pdfIniciar('ESTADO DE PAGOS DE LA SECCIÓN', 'L');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'PROFESOR:', 'text'=>$prof ?: 'Sin asignar'],
                ['bold'=>'CURSO:',    'text'=>$curso . ($tipo ? " ($tipo)" : '')],
                ['bold'=>'PERÍODO:',  'text'=>$period],
                ['bold'=>'SECCIÓN:',  'text'=>$letra . ($dia ? " ($dia)" : '')],
            ], 8);

            formatoControlador::pdfTabla($pdf, $columnas, $filas, [
                'alto_header' => 8,
                'alto_fila'   => 5,
                'color_cell'  => function(array $row, array $col) {
                    if (!preg_match('/^c(\d+)$/', $col['key'], $m)) return null;
                    $estado = $row['e' . $m[1]] ?? '';
                    return [
                        'Pagado'    => ['bg'=>[40,167,69],  'fg'=>[255,255,255]],
                        'Pendiente' => ['bg'=>[255,193,7],  'fg'=>[0,0,0]],
                        'Atrasado'  => ['bg'=>[220,53,69],  'fg'=>[255,255,255]],
                    ][$estado] ?? null;
                },
            ]);

            $pdf->Ln(3);
            formatoControlador::pdfResumen($pdf, [
                ['label'=>'Totales',
                 'valores'=>[
                     '$' . number_format($totalEsperado, 0, '.', ','),
                     '$' . number_format($totalCobrado,  0, '.', ','),
                     '$' . number_format($deuda,         0, '.', ','),
                     $pct . '%',
                 ]],
            ], [80, 40, 40, 40, 30], [
                'header' => ['RESUMEN', 'Total esperado', 'Total cobrado', 'Deuda', '% Cobrado'],
                'alto'   => 6,
            ]);

            // ── Leyenda ──
            $pdf->Ln(2);
            $pdf->SetFont('Arial', 'I', 6);
            $pdf->SetFillColor(40, 167, 69);  $pdf->Cell(4, 3, '', 1, 0, 'C', true);
            $pdf->Cell(20, 3, formatoControlador::iso(' Pagado'), 0, 0, 'L');
            $pdf->SetFillColor(255, 193, 7);  $pdf->Cell(4, 3, '', 1, 0, 'C', true);
            $pdf->Cell(30, 3, formatoControlador::iso(' Pendiente'), 0, 0, 'L');
            $pdf->SetFillColor(220, 53, 69);  $pdf->Cell(4, 3, '', 1, 0, 'C', true);
            $pdf->Cell(20, 3, formatoControlador::iso(' Atrasado'), 0, 1, 'L');

            // ── Footer ──
            formatoControlador::pdfFooter($pdf, [
                'ubicacion' => $direccion,
                'firmas'    => [
                    ['nombre'=>$prof ?: 'Profesor', 'cargo'=>'Profesor de la asignatura'],
                ],
            ]);

            formatoControlador::pdfSalida(
                $pdf,
                formatoControlador::nombreArchivo('Pagos_Seccion_' . $letra, 'pdf')
            );

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    
    
//excel
    public function generar_excel_pagos_seccion_controlador(int $sectionId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);

            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pagos_seccion_controlador($sectionId);
            if (!$datos['ok']) die($datos['message']);

            $section     = $datos['section'];
            $students    = $datos['students'];
            $periodCosts = $datos['period_costs'];
            $pagos       = $datos['pagos'];

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

            // Mapa de pagos
            $pagosPorRegistro = [];
            foreach ($pagos as $p) {
                $pagosPorRegistro[(int)$p['registration_id']][$p['motive']] = (int)$p['pagado'];
            }

            $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
            $curso  = $section['period_course']['course']['name'] ?? '';
            $tipo   = $section['period_course']['course']['type'] ?? '';
            $period = $section['period_course']['period']['name'] ?? '';
            $letra  = $section['name'] ?? '';
            $dia    = $section['section_day'] ?? '';

            // Etiquetas de conceptos
            $conceptLabels = [];
            foreach ($periodCosts as $pc) {
                $m = [];
                $conceptLabels[] = preg_match('/^pago_encuentro(\d+)$/', $pc['motive'], $m)
                    ? 'E' . $m[1]
                    : ucfirst(str_replace('_', ' ', str_replace('pago_', '', $pc['motive'])));
            }

            // ── Columnas dinámicas ──
            $columnas = [
                ['key'=>'n',      'label'=>'Nº',  'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',     'label'=>'C.I', 'align'=>'C'],
                ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
            ];
            $anchos = ['n'=>6, 'ci'=>14, 'nombre'=>35];

            foreach ($periodCosts as $j => $pc) {
                $columnas[] = [
                    'key'   => "c$j",
                    'label' => $conceptLabels[$j] . ' ($' . number_format((float)$pc['price'], 0, '.', ',') . ')',
                    'align' => 'C',
                ];
                $anchos["c$j"] = 14;
            }

            // ── Filas ──
            $hoy = date('Y-m-d');
            $filas = [];
            $totalEsperado = 0;
            $totalCobrado  = 0;

            foreach ($students as $i => $est) {
                $regId = (int) $est['registration_id'];
                $pagosEst = [];
                foreach ($periodCosts as $pc) {
                    $pagosEst[$pc['motive']] = (int) ($pagosPorRegistro[$regId][$pc['motive']] ?? 0);
                }
                $calcEst = formatoControlador::calcularPagosEstudiante($periodCosts, $pagosEst, $hoy);
                $totalEsperado += $calcEst['total_esperado'];
                $totalCobrado  += $calcEst['total_cobrado'];

                //'ci'     => $est['ci'] ?? '',
                $row = [
                    'n'      => $i + 1,
                    'ci'     => mainModel::decryption($est['ci'] ?? ''),
                    'nombre' => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
                ];
                foreach ($calcEst['filas'] as $j => $f) {
                    $row["c$j"] = '$' . $f['pagado'] . ' / $' . $f['precio'];
                    $row["e$j"] = $f['estado'];
                }
                $filas[] = $row;
            }

            $deuda = $totalEsperado - $totalCobrado;
            $pct   = $totalEsperado > 0 ? round(($totalCobrado / $totalEsperado) * 100, 1) : 0;

            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            // ── Excel ──
            $excel = formatoControlador::excelIniciar(
                'ESTADO DE PAGOS DE LA SECCIÓN',
                'Pagos',
                count($columnas),
                $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            // ── Contexto ──
            $contexto = [
                'PROFESOR: ' . ($prof ?: 'Sin asignar'),
                'CURSO: ' . $curso . ($tipo ? " ($tipo)" : ''),
                'PERÍODO: ' . $period,
                'SECCIÓN: ' . $letra . ($dia ? " ($dia)" : ''),
            ];
            $listaPrecios = [];
            foreach ($periodCosts as $j => $pc) {
                $listaPrecios[] = $conceptLabels[$j] . ' ($' . number_format((float)$pc['price'], 0, '.', ',') . ')';
            }
            if (!empty($listaPrecios)) {
                $contexto[] = 'Conceptos: ' . implode(' | ', $listaPrecios);
            }
            $fila = formatoControlador::excelContexto($sheet, $fila, $contexto, count($columnas));

            // ── Tabla con color por celda ──
            $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos, [
                'color_cell' => function(array $row, array $col) {
                    if (!preg_match('/^c(\d+)$/', $col['key'], $m)) return null;
                    $estado = $row['e' . $m[1]] ?? '';
                    return [
                        'Pagado'    => ['bg'=>'C6EFCE', 'fg'=>'006100'],
                        'Pendiente' => ['bg'=>'FFEB9C', 'fg'=>'9C6500'],
                        'Atrasado'  => ['bg'=>'FFC7CE', 'fg'=>'9C0006'],
                    ][$estado] ?? null;
                },
            ]);
            $fila += 2;

            // ── Resumen (no hay helper de resumen para Excel) ──
            $bgGray = ['fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'EEEEEE']]];
            $bold   = ['font'=>['bold'=>true]];

            $sheet->setCellValue("A{$fila}", 'RESUMEN');
            $sheet->setCellValue("B{$fila}", 'Total esperado');
            $sheet->setCellValue("C{$fila}", 'Total cobrado');
            $sheet->setCellValue("D{$fila}", 'Deuda');
            $sheet->setCellValue("E{$fila}", '% Cobrado');
            $sheet->getStyle("A{$fila}:E{$fila}")->applyFromArray($bgGray);
            $sheet->getStyle("A{$fila}:E{$fila}")->applyFromArray($bold);
            $fila++;

            $sheet->setCellValue("A{$fila}", 'Totales');
            $sheet->setCellValue("B{$fila}", '$' . number_format($totalEsperado, 0, '.', ','));
            $sheet->setCellValue("C{$fila}", '$' . number_format($totalCobrado,  0, '.', ','));
            $sheet->setCellValue("D{$fila}", '$' . number_format($deuda,         0, '.', ','));
            $sheet->setCellValue("E{$fila}", $pct . '%');
            $fila++;

            // ── Footer ──
            formatoControlador::excelFooter($sheet, $fila, [
                'ubicacion' => $direccion,
                'num_cols'  => count($columnas),
                'firmas'    => [
                    ['nombre'=>$prof ?: 'Profesor', 'cargo'=>'Profesor de la asignatura'],
                ],
            ]);

            // Ajustes de impresión
            $sheet->getPageSetup()->setOrientation(
                \PhpOffice\PhpSpreadsheet\Worksheet\PageSetup::ORIENTATION_LANDSCAPE
            );
            $sheet->getPageSetup()->setFitToWidth(1);
            $sheet->getPageSetup()->setFitToHeight(0);

            formatoControlador::excelSalida(
                $excel['spreadsheet'],
                formatoControlador::nombreArchivo('Pagos_Seccion_' . $letra, 'xlsx')
            );

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }    
    
    
    
//csv    
public function generar_csv_pagos_seccion_controlador(int $sectionId) {
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);

        require_once 'formatoreporteControlador.php';

        $datos = $this->reporte_pagos_seccion_controlador($sectionId);
        if (!$datos['ok']) die($datos['message']);

        $section     = $datos['section'];
        $students    = $datos['students'];
        $periodCosts = $datos['period_costs'];
        $pagos       = $datos['pagos'];

        // Mapa de pagos
        $pagosPorRegistro = [];
        foreach ($pagos as $p) {
            $pagosPorRegistro[(int)$p['registration_id']][$p['motive']] = (int)$p['pagado'];
        }

        $prof   = trim(($section['teacher']['name'] ?? '') . ' ' . ($section['teacher']['last_name'] ?? ''));
        $curso  = $section['period_course']['course']['name'] ?? '';
        $tipo   = $section['period_course']['course']['type'] ?? '';
        $period = $section['period_course']['period']['name'] ?? '';
        $letra  = $section['name'] ?? 'seccion';
        $dia    = $section['section_day'] ?? '';

        // Etiquetas
        $conceptLabels = [];
        foreach ($periodCosts as $pc) {
            $m = [];
            $conceptLabels[] = preg_match('/^pago_encuentro(\d+)$/', $pc['motive'], $m)
                ? 'E' . $m[1]
                : ucfirst(str_replace('_', ' ', str_replace('pago_', '', $pc['motive'])));
        }

        // ── Columnas dinámicas ──
        $columnas = [
            ['key'=>'n',      'label'=>'Nº',  'formato'=>'int'],
            ['key'=>'ci',     'label'=>'C.I'],
            ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
        ];
        foreach ($periodCosts as $j => $pc) {
            $columnas[] = [
                'key'    => "c$j",
                'label'  => $conceptLabels[$j] . ' ($' . number_format((float)$pc['price'], 0, '.', ',') . ')',
                'render' => fn($val, $row) => $val . ' (' . ($row['e' . substr($columnas_key = null, 0) ?: ''] ?? '') . ')',
            ];
        }

        // reconstruir las columnas de pago una a una con su render
        $columnas = [
            ['key'=>'n',      'label'=>'Nº',  'formato'=>'int'],
            ['key'=>'ci',     'label'=>'C.I'],
            ['key'=>'nombre', 'label'=>'Apellidos y Nombres'],
        ];
        foreach ($periodCosts as $j => $pc) {
            $label = $conceptLabels[$j] . ' ($' . number_format((float)$pc['price'], 0, '.', ',') . ')';
            $columnas[] = [
                'key'    => "c$j",
                'label'  => $label,
                'render' => function($val, $row) use ($j) {
                    return $val . ' (' . ($row["e$j"] ?? '') . ')';
                },
            ];
        }

        // ── Filas ──
        $hoy = date('Y-m-d');
        $filas = [];
        foreach ($students as $i => $est) {
            $regId = (int) $est['registration_id'];
            $pagosEst = [];
            foreach ($periodCosts as $pc) {
                $pagosEst[$pc['motive']] = (int) ($pagosPorRegistro[$regId][$pc['motive']] ?? 0);
            }
            $calcEst = formatoControlador::calcularPagosEstudiante($periodCosts, $pagosEst, $hoy);

            //'ci'     => $est['ci'] ?? '',
            $row = [
                'n'      => $i + 1,
                'ci'     => mainModel::decryption($est['ci'] ?? ''),
                'nombre' => trim(($est['last_name'] ?? '') . ' ' . ($est['name'] ?? '')),
            ];
            foreach ($calcEst['filas'] as $j => $f) {
                $row["c$j"] = '$' . $f['pagado'] . ' / $' . $f['precio'];
                $row["e$j"] = $f['estado'];
            }
            $filas[] = $row;
        }

        // ── Salida ──
        $nombre = formatoControlador::nombreArchivo('Pagos_Seccion_' . $letra, 'csv');
        $out    = formatoControlador::csvIniciar($nombre);

        formatoControlador::csvContexto($out, [
            'ESTADO DE PAGOS DE LA SECCIÓN',
            'PROFESOR: ' . ($prof ?: 'Sin asignar'),
            'CURSO: ' . $curso . ($tipo ? " ($tipo)" : ''),
            'PERÍODO: ' . $period,
            'SECCIÓN: ' . $letra . ($dia ? " ($dia)" : ''),
        ]);

        formatoControlador::csvTabla($out, $columnas, $filas);
        formatoControlador::csvSalida($out);

    } catch (Throwable $e) {
        die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    }
}
    

/*@@@@@@@@@@@@@@@@@@@@@@@@@@ Reporte pago individual por estudiante@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/    
    
//pdf
    public function generar_pdf_pagos_estudiante_controlador(int $registrationId) {
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) die("La sesión de Supabase no está disponible");

        require_once 'formatoreporteControlador.php';

        $datos = $this->reporte_pagos_estudiante_controlador($registrationId);
        if (!$datos['ok']) die($datos['message']);

        $reg           = $datos['registration'];
        $periodCosts   = $datos['period_costs'];
        $transacciones = $datos['transacciones'];
        $pagos         = $datos['pagos'];

        $student = $reg['student'] ?? [];
        $course  = $reg['course']  ?? [];
        $section = $reg['section'] ?? null;
        $period  = $reg['period']  ?? [];
        
        $ciDecrypted = mainModel::decryption($student['ci'] ?? '');

        // Mapa por concepto (formato que espera calcularPagosEstudiante)
        $pagosMap = [];
        foreach ($pagos as $p) {
            $pagosMap[$p['motive']] = (int) $p['pagado'];
        }

        $nombreCompleto = trim(($student['last_name'] ?? '') . ' ' . ($student['name'] ?? ''));

        // ── Cálculos ──
        $hoy  = date('Y-m-d');
        $calc = formatoControlador::calcularPagosEstudiante($periodCosts, $pagosMap, $hoy);

        // ── PDF ──
        $pdf = formatoControlador::pdfIniciar('ESTADO DE PAGOS DEL ESTUDIANTE');

        formatoControlador::pdfContexto($pdf, [
            ['bold'=>'ESTUDIANTE:', 'text'=>$nombreCompleto . '  |  C.I: ' .$ciDecrypted],
            ['bold'=>'CURSO:',      'text'=>($course['name'] ?? '') . (!empty($course['type']) ? ' (' . $course['type'] . ')' : '') . '  |  SECCIÓN: ' . ($section['name'] ?? '—')],
            ['bold'=>'PERÍODO:',    'text'=>($period['name'] ?? '') . (!empty($period['type']) ? ' (' . $period['type'] . ')' : '')],
            ['bold'=>'Fechas:',     'text'=>($period['start_date'] ?? '') . ' al ' . ($period['end_date'] ?? '')],
            ['bold'=>'Inscripción:','text'=>($reg['registration_date'] ?? '')],
        ]);

        // ── Tabla 1: conceptos ──
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, formatoControlador::iso('ESTADO DE PAGOS POR CONCEPTO'), 0, 1);
        $pdf->Ln(1);

        $columnasConceptos = [
            ['key'=>'concepto', 'label'=>'Concepto', 'ancho'=>60, 'align'=>'L'],
            ['key'=>'precio',   'label'=>'Precio',   'ancho'=>22, 'align'=>'C', 'formato'=>'money'],
            ['key'=>'pagado',   'label'=>'Pagado',   'ancho'=>22, 'align'=>'C', 'formato'=>'money'],
            ['key'=>'deuda',    'label'=>'Deuda',    'ancho'=>22, 'align'=>'C', 'formato'=>'money'],
            ['key'=>'vence',    'label'=>'Vence',    'ancho'=>30, 'align'=>'C'],
            ['key'=>'estado',   'label'=>'Estado',   'ancho'=>40, 'align'=>'C'],
        ];

        formatoControlador::pdfTabla($pdf, $columnasConceptos, $calc['filas'], [
            'alto_fila'  => 6,
            'color_cell' => function(array $row, array $col) {
                if ($col['key'] !== 'estado') return null;
                return [
                    'Pagado'    => ['bg'=>[40,167,69],  'fg'=>[255,255,255]],
                    'Pendiente' => ['bg'=>[255,193,7],  'fg'=>[0,0,0]],
                    'Atrasado'  => ['bg'=>[220,53,69],  'fg'=>[255,255,255]],
                ][$row['estado']] ?? null;
            },
        ]);

        // Totales vía pdfResumen
        formatoControlador::pdfResumen($pdf, [
            ['label'=>'TOTALES', 'valores'=>[
                '$' . number_format($calc['total_esperado'], 0, '.', ','),
                '$' . number_format($calc['total_cobrado'],  0, '.', ','),
                '$' . number_format($calc['total_deuda'],    0, '.', ','),
                '', '',
            ]],
        ], [60, 22, 22, 22, 30, 40], [
            'alto' => 7,
            'fill' => [240,240,240],
        ]);

        $pdf->Ln(6);

        // ── Tabla 2: transacciones ──
        $pdf->SetFont('Arial', 'B', 10);
        $pdf->Cell(0, 6, formatoControlador::iso('HISTORIAL DE TRANSACCIONES'), 0, 1);
        $pdf->Ln(1);

        if (empty($transacciones)) {
            $pdf->SetFont('Arial', 'I', 9);
            $pdf->Cell(0, 8, formatoControlador::iso('No hay transacciones registradas.'), 1, 1, 'C');
        } else {
            $filasTrans = [];
            foreach ($transacciones as $i => $t) {
                $fecha = $t['created_at'] ?? '';
                if ($fecha !== '') {
                    try { $fecha = (new DateTime($fecha))->format('d/m/Y H:i'); }
                    catch (Exception $e) {}
                }

                $conceptos = [];
                foreach (($t['details'] ?? []) as $d) {
                    $m = $d['motive_payment'] ?? '';
                    $label = preg_match('/^pago_encuentro(\d+)$/', $m, $mm)
                        ? 'Encuentro ' . $mm[1]
                        : ucfirst(str_replace('_', ' ', str_replace('pago_', '', $m)));
                    $conceptos[] = $label . ' ($' . $d['amount'] . ')';
                }

                $filasTrans[] = [
                    'n'          => $i + 1,
                    'fecha'      => $fecha,
                    'metodo'     => $t['payment_method'] ?? '',
                    'referencia' => $t['reference']      ?? '',
                    'conceptos'  => implode(', ', $conceptos),
                ];
            }

            $columnasTrans = [
                ['key'=>'n',          'label'=>'N',                 'ancho'=>8,  'align'=>'C', 'formato'=>'int'],
                ['key'=>'fecha',      'label'=>'Fecha',             'ancho'=>26, 'align'=>'C'],
                ['key'=>'metodo',     'label'=>'Método',            'ancho'=>28, 'align'=>'C', 'wrap'=>true],
                ['key'=>'referencia', 'label'=>'Referencia',        'ancho'=>44, 'align'=>'C', 'wrap'=>true],
                ['key'=>'conceptos',  'label'=>'Conceptos pagados', 'ancho'=>90, 'align'=>'L', 'wrap'=>true],
            ];

            formatoControlador::pdfTabla($pdf, $columnasTrans, $filasTrans, [
                'alto_header' => 8,
                'alto_linea'  => 4,
            ]);
        }

        // ── Footer (nuevo; el original no lo tenía) ──
        $jsonLocal    = mainModel::leer_json_local(['contacto']);
        $contactoJSON = $jsonLocal['contacto'] ?? [];
        $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

        formatoControlador::pdfFooter($pdf, [
            'ubicacion' => $direccion,
        ]);

        formatoControlador::pdfSalida(
            $pdf,
            formatoControlador::nombreArchivo('Pagos_Estudiante_' .$ciDecrypted, 'pdf')
        );

    } catch (Throwable $e) {
        die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    }
}
    

    
//excel
    public function generar_excel_pagos_estudiante_controlador(int $registrationId) {
    try {
        if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) die("La sesión de Supabase no está disponible");

        require_once 'formatoreporteControlador.php';

        $datos = $this->reporte_pagos_estudiante_controlador($registrationId);
        if (!$datos['ok']) die($datos['message']);

        $reg           = $datos['registration'];
        $periodCosts   = $datos['period_costs'];
        $transacciones = $datos['transacciones'];
        $pagos         = $datos['pagos'];

        $student = $reg['student'] ?? [];
        $course  = $reg['course']  ?? [];
        $section = $reg['section'] ?? null;
        $period  = $reg['period']  ?? [];
        
		$ciDecrypted = mainModel::decryption($student['ci'] ?? '');

        $pagosMap = [];
        foreach ($pagos as $p) {
            $pagosMap[$p['motive']] = (int) $p['pagado'];
        }

        $nombreCompleto = trim(($student['last_name'] ?? '') . ' ' . ($student['name'] ?? ''));

        $hoy  = date('Y-m-d');
        $calc = formatoControlador::calcularPagosEstudiante($periodCosts, $pagosMap, $hoy);

        // ── Columnas de conceptos (mismas del original: A..F, 6 columnas) ──
        $columnasConceptos = [
            ['key'=>'concepto', 'label'=>'Concepto'],
            ['key'=>'precio',   'label'=>'Precio', 'align'=>'C'],   // sin formato → numérico
            ['key'=>'pagado',   'label'=>'Pagado', 'align'=>'C'],
            ['key'=>'deuda',    'label'=>'Deuda',  'align'=>'C'],
            ['key'=>'vence',    'label'=>'Vence',  'align'=>'C'],
            ['key'=>'estado',   'label'=>'Estado', 'align'=>'C'],
        ];
        $anchosConceptos = ['concepto'=>30, 'precio'=>12, 'pagado'=>12, 'deuda'=>12, 'vence'=>15, 'estado'=>14];

        $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnasConceptos, $anchosConceptos);

        // ── Excel ──
        $excel = formatoControlador::excelIniciar(
            'ESTADO DE PAGOS DEL ESTUDIANTE',
            'Pagos',
            count($columnasConceptos),
            $anchoTotalPx
        );
        $sheet = $excel['sheet'];
        $fila  = $excel['fila'];

        // ── Contexto ──
        $fila = formatoControlador::excelContexto($sheet, $fila, [
            'ESTUDIANTE: ' . $nombreCompleto . ' | C.I: ' . $ciDecrypted,
            'CURSO: '    . ($course['name'] ?? '') . (!empty($course['type']) ? ' (' . $course['type'] . ')' : '') . ' | SECCIÓN: ' . ($section['name'] ?? '—'),
            'PERÍODO: '  . ($period['name'] ?? '') . ' | Fechas: ' . ($period['start_date'] ?? '') . ' al ' . ($period['end_date'] ?? ''),
            'Fecha de inscripción: ' . ($reg['registration_date'] ?? ''),
        ], count($columnasConceptos));

        // ── Tabla 1: conceptos ──
        $sheet->setCellValue("A{$fila}", 'ESTADO DE PAGOS POR CONCEPTO');
        $sheet->mergeCells("A{$fila}:F{$fila}");
        $sheet->getStyle("A{$fila}:F{$fila}")->applyFromArray(['font'=>['bold'=>true]]);
        $fila++;

        $fila = formatoControlador::excelTabla($sheet, $fila, $columnasConceptos, $calc['filas'], $anchosConceptos, [
            'color_cell' => function(array $row, array $col) {
                if ($col['key'] !== 'estado') return null;
                return [
                    'Pagado'    => ['bg'=>'C6EFCE', 'fg'=>'006100'],
                    'Pendiente' => ['bg'=>'FFEB9C', 'fg'=>'9C6500'],
                    'Atrasado'  => ['bg'=>'FFC7CE', 'fg'=>'9C0006'],
                ][$row['estado']] ?? null;
            },
        ]);

        // Fila de totales (numérica, igual que el original)
        $sheet->setCellValue("A{$fila}", 'TOTALES');
        $sheet->setCellValue("B{$fila}", $calc['total_esperado']);
        $sheet->setCellValue("C{$fila}", $calc['total_cobrado']);
        $sheet->setCellValue("D{$fila}", $calc['total_deuda']);
        $sheet->getStyle("A{$fila}:F{$fila}")->applyFromArray(['font'=>['bold'=>true]]);
        $fila += 2;

        // ── Tabla 2: transacciones (manual por el merge E:F) ──
        $sheet->setCellValue("A{$fila}", 'HISTORIAL DE TRANSACCIONES');
        $sheet->mergeCells("A{$fila}:F{$fila}");
        $sheet->getStyle("A{$fila}:F{$fila}")->applyFromArray(['font'=>['bold'=>true]]);
        $fila++;

        if (empty($transacciones)) {
            $sheet->setCellValue("A{$fila}", 'No hay transacciones registradas.');
            $sheet->mergeCells("A{$fila}:F{$fila}");
            $fila++;
        } else {
            $bgBlue = ['fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>'DCDCFF']]];
            $bold   = ['font'=>['bold'=>true]];
            $center = ['alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];

            // Header
            $sheet->setCellValue("A{$fila}", 'Fecha');
            $sheet->setCellValue("B{$fila}", 'Método');
            $sheet->setCellValue("C{$fila}", 'Referencia');
            $sheet->setCellValue("D{$fila}", 'Total');
            $sheet->setCellValue("E{$fila}", 'Conceptos pagados');
            $sheet->mergeCells("E{$fila}:F{$fila}");
            $rangoCab = "A{$fila}:F{$fila}";
            $sheet->getStyle($rangoCab)->applyFromArray($bgBlue);
            $sheet->getStyle($rangoCab)->applyFromArray($bold);
            $sheet->getStyle($rangoCab)->applyFromArray($center);
            $fila++;
            $filaIniTrans = $fila;

            foreach ($transacciones as $t) {
                $fecha = $t['created_at'] ?? '';
                if ($fecha !== '') {
                    try { $fecha = (new DateTime($fecha))->format('d/m/Y H:i'); }
                    catch (Exception $e) {}
                }
                $conceptos = [];
                foreach (($t['details'] ?? []) as $d) {
                    $m = $d['motive_payment'] ?? '';
                    $label = preg_match('/^pago_encuentro(\d+)$/', $m, $mm)
                        ? 'Encuentro ' . $mm[1]
                        : ucfirst(str_replace('_', ' ', str_replace('pago_', '', $m)));
                    $conceptos[] = $label . ' ($' . $d['amount'] . ')';
                }

                $sheet->setCellValue("A{$fila}", $fecha);
                $sheet->setCellValue("B{$fila}", $t['payment_method'] ?? '');
                $sheet->setCellValue("C{$fila}", $t['reference'] ?? '');
                $sheet->setCellValue("D{$fila}", (int) ($t['cost'] ?? 0));
                $sheet->setCellValue("E{$fila}", implode(', ', $conceptos));
                $sheet->mergeCells("E{$fila}:F{$fila}");
                $fila++;
            }

            $rangoT = "A" . ($filaIniTrans - 1) . ":F" . ($fila - 1);
            $sheet->getStyle($rangoT)->getBorders()->getAllBorders()->setBorderStyle(
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            );
        }

        // ── Footer ──
        $jsonLocal    = mainModel::leer_json_local(['contacto']);
        $contactoJSON = $jsonLocal['contacto'] ?? [];
        $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

        formatoControlador::excelFooter($sheet, $fila + 1, [
            'ubicacion' => $direccion,
            'num_cols'  => 6,
        ]);

        formatoControlador::excelSalida(
            $excel['spreadsheet'],
            formatoControlador::nombreArchivo('Pagos_Estudiante_' . $ciDecrypted, 'xlsx')
        );

    } catch (Throwable $e) {
        die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
    }
}
    
//csv
    public function generar_csv_pagos_estudiante_controlador(int $registrationId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_pagos_estudiante_controlador($registrationId);
            if (!$datos['ok']) die($datos['message']);

            $reg           = $datos['registration'];
            $periodCosts   = $datos['period_costs'];
            $transacciones = $datos['transacciones'];
            $pagos         = $datos['pagos'];

            $student = $reg['student'] ?? [];
            $course  = $reg['course']  ?? [];
            $section = $reg['section'] ?? null;
            $period  = $reg['period']  ?? [];
            
			$ciDecrypted = mainModel::decryption($student['ci'] ?? '');

            $pagosMap = [];
            foreach ($pagos as $p) {
                $pagosMap[$p['motive']] = (int) $p['pagado'];
            }

            $nombreCompleto = trim(($student['last_name'] ?? '') . ' ' . ($student['name'] ?? ''));

            $hoy  = date('Y-m-d');
            $calc = formatoControlador::calcularPagosEstudiante($periodCosts, $pagosMap, $hoy);

            // ── Columnas ──
            $columnasConceptos = [
                ['key'=>'concepto', 'label'=>'Concepto'],
                ['key'=>'precio',   'label'=>'Precio', 'formato'=>'money'],
                ['key'=>'pagado',   'label'=>'Pagado', 'formato'=>'money'],
                ['key'=>'deuda',    'label'=>'Deuda',  'formato'=>'money'],
                ['key'=>'vence',    'label'=>'Vence'],
                ['key'=>'estado',   'label'=>'Estado'],
            ];

            $columnasTrans = [
                ['key'=>'fecha',      'label'=>'Fecha'],
                ['key'=>'metodo',     'label'=>'Método'],
                ['key'=>'referencia', 'label'=>'Referencia'],
                ['key'=>'total',      'label'=>'Total'],
                ['key'=>'conceptos',  'label'=>'Conceptos pagados'],
            ];

            $filasTrans = [];
            foreach ($transacciones as $t) {
                $fecha = $t['created_at'] ?? '';
                if ($fecha !== '') {
                    try { $fecha = (new DateTime($fecha))->format('d/m/Y H:i'); }
                    catch (Exception $e) {}
                }
                $conceptos = [];
                foreach (($t['details'] ?? []) as $d) {
                    $m = $d['motive_payment'] ?? '';
                    $label = preg_match('/^pago_encuentro(\d+)$/', $m, $mm)
                        ? 'Encuentro ' . $mm[1]
                        : ucfirst(str_replace('_', ' ', str_replace('pago_', '', $m)));
                    $conceptos[] = $label . ' ($' . $d['amount'] . ')';
                }

                $filasTrans[] = [
                    'fecha'      => $fecha,
                    'metodo'     => $t['payment_method'] ?? '',
                    'referencia' => $t['reference']      ?? '',
                    'total'      => (int) ($t['cost'] ?? 0),
                    'conceptos'  => implode(', ', $conceptos),
                ];
            }

            // ── Salida ──
            $nombre = formatoControlador::nombreArchivo('Pagos_Estudiante_' . $ciDecrypted, 'csv');
            $out    = formatoControlador::csvIniciar($nombre);

            formatoControlador::csvContexto($out, [
                'ESTADO DE PAGOS DEL ESTUDIANTE',
                'ESTUDIANTE: ' . $nombreCompleto . ' | C.I: ' .$ciDecrypted,
                'CURSO: '    . ($course['name'] ?? '') . (!empty($course['type']) ? ' (' . $course['type'] . ')' : '') . ' | SECCIÓN: ' . ($section['name'] ?? '—'),
                'PERÍODO: '  . ($period['name'] ?? '') . ' | Fechas: ' . ($period['start_date'] ?? '') . ' al ' . ($period['end_date'] ?? ''),
            ]);

            // Sección 1
            fputcsv($out, ['ESTADO DE PAGOS POR CONCEPTO'], ';', '"', '');
            formatoControlador::csvTabla($out, $columnasConceptos, $calc['filas']);

            fputcsv($out, [], ';', '"', '');

            // Sección 2
            fputcsv($out, ['HISTORIAL DE TRANSACCIONES'], ';', '"', '');
            if (empty($filasTrans)) {
                fputcsv($out, ['Sin transacciones registradas'], ';', '"', '');
            } else {
                formatoControlador::csvTabla($out, $columnasTrans, $filasTrans);
            }

            formatoControlador::csvSalida($out);

        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    
    
/*@@@@@@@@@@@@@@@@@@@@@@@@@@ Reporte notas individual por estudiante@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@@*/

    
//pdf
    public function generar_pdf_notas_estudiante_controlador(int $registrationId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_notas_estudiante_controlador($registrationId);
            if (!$datos['ok']) die($datos['message']);

            $reg         = $datos['registration'];
            $evaluations = $datos['evaluations'];
            $summary     = $datos['grade_summary'];

            $student = $reg['student'] ?? [];
            $course  = $reg['course']  ?? [];
            $section = $reg['section'] ?? null;
            $period  = $reg['period']  ?? [];
            $teacher = $section['teacher'] ?? null;

			$ciDecrypted = mainModel::decryption($student['ci'] ?? '');
            
            $nombreCompleto = trim(($student['last_name'] ?? '') . ' ' . ($student['name'] ?? ''));
            $nombreProf     = $teacher ? trim(($teacher['name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) : '';

            // ── PDF ──
            $pdf = formatoControlador::pdfIniciar('REPORTE DE NOTAS DEL ESTUDIANTE');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'ESTUDIANTE:', 'text'=>$nombreCompleto . '  |  C.I: ' .$ciDecrypted],
                ['bold'=>'CURSO:',      'text'=>($course['name'] ?? '') . (!empty($course['type']) ? ' (' . $course['type'] . ')' : '') .
                                                 '  |  SECCIÓN: ' . ($section['name'] ?? '—') .
                                                 ($nombreProf ? '  |  PROFESOR: ' . $nombreProf : '')],
                ['bold'=>'PERÍODO:',    'text'=>($period['name'] ?? '') . (!empty($period['type']) ? ' (' . $period['type'] . ')' : '') .
                                                 '  |  Fechas: ' . ($period['start_date'] ?? '') . ' al ' . ($period['end_date'] ?? '')],
                ['bold'=>'Inscripción:','text'=>($reg['registration_date'] ?? '')],
            ]);

            // ── Tabla 1: Evaluaciones ──
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 6, formatoControlador::iso('DETALLE DE EVALUACIONES'), 0, 1);
            $pdf->Ln(1);

            if (empty($evaluations)) {
                $pdf->SetFont('Arial', 'I', 9);
                $pdf->Cell(0, 8, formatoControlador::iso('No hay evaluaciones registradas en esta sección.'), 1, 1, 'C');
            } else {
                $filasEv = [];
                foreach ($evaluations as $i => $ev) {
                    $fecha = $ev['evaluation_date'] ?? '';
                    if ($fecha !== '') {
                        try { $fecha = (new DateTime($fecha))->format('d/m/Y'); }
                        catch (Exception $e) {}
                    }

                    $obj = $ev['objective'] ?? '';
                    if ($obj !== '' && $obj !== null) {
                        $obj = (floor((float)$obj) == (float)$obj)
                            ? (string)(int)$obj
                            : rtrim(rtrim(number_format((float)$obj, 4, '.', ''), '0'), '.');
                    } else $obj = '';

                    $presented = (bool)($ev['presented'] ?? false);

                    $filasEv[] = [
                        'n'       => $i + 1,
                        'fecha'   => $fecha,
                        'tipo'    => $ev['evaluation_type'] ?? '',
                        'obj'     => $obj,
                        'pct'     => ((int)($ev['percentage'] ?? 0)) . '%',
                        'puntaje' => $presented ? (int)($ev['score'] ?? 0) : 'NP',
                        'aporte'  => number_format((float)($ev['weighted_contribution'] ?? 0), 2, '.', ','),
                        'estado'  => $presented ? 'Presentada' : 'No presentada',
                    ];
                }

                $columnasEv = [
                    ['key'=>'n',       'label'=>'N',           'ancho'=>8,  'align'=>'C', 'formato'=>'int'],
                    ['key'=>'fecha',   'label'=>'Fecha',       'ancho'=>22, 'align'=>'C'],
                    ['key'=>'tipo',    'label'=>'Tipo',        'ancho'=>34, 'align'=>'L'],
                    ['key'=>'obj',     'label'=>'Objetivo',    'ancho'=>18, 'align'=>'C'],
                    ['key'=>'pct',     'label'=>'%',           'ancho'=>12, 'align'=>'C'],
                    ['key'=>'puntaje', 'label'=>'Puntaje',     'ancho'=>22, 'align'=>'C'],
                    ['key'=>'aporte',  'label'=>'Aporte',      'ancho'=>22, 'align'=>'C'],
                    ['key'=>'estado',  'label'=>'Estado',      'ancho'=>58, 'align'=>'C'],
                ];

                formatoControlador::pdfTabla($pdf, $columnasEv, $filasEv, [
                    'alto_fila'  => 6,
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'estado') return null;
                        return $row['estado'] === 'Presentada'
                            ? ['fg'=>[0, 130, 0]]
                            : ['fg'=>[180, 0, 0]];
                    },
                ]);
            }

            $pdf->Ln(6);

            // ── Tabla 2: Resumen ──
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->Cell(0, 6, formatoControlador::iso('RESUMEN DE CALIFICACIONES'), 0, 1);
            $pdf->Ln(1);

            $totalEval   = (int)   ($summary['total_evaluations']         ?? 0);
            $pctTotal    = (int)   ($summary['percentage_total']          ?? 0);
            $presentadas = (int)   ($summary['presented_evaluations']     ?? 0);
            $noPresent   = (int)   ($summary['not_presented_evaluations'] ?? 0);
            $notaFinal   = $summary['final_grade'] ?? null;
            $status      = (string)($summary['status']        ?? '');
            $notaAprob   = (int)   ($summary['passing_score'] ?? 10);

            formatoControlador::pdfResumen($pdf, [
                [
                    'label'   => $totalEval,
                    'valores' => [
                        $presentadas,
                        $noPresent,
                        $pctTotal . '%',
                        ($notaFinal === null) ? '—' : number_format((float)$notaFinal, 2, '.', ','),
                        $status ?: '—',
                    ],
                ],
            ], [36, 32, 34, 24, 30, 40], [
                'header' => ['Evaluaciones', 'Presentadas', 'No presentadas', '% Total', 'Nota final', 'Estado'],
                'color_cell' => function(array $fila, array $col) {
                    if ($col['idx'] !== 5) return null;   // solo la columna Estado
                    $estado = $fila['valores'][4] ?? '';
                    if ($estado === 'Aprobado')  return ['bg'=>[40,167,69],  'fg'=>[255,255,255]];
                    if ($estado === 'Reprobado') return ['bg'=>[220,53,69],  'fg'=>[255,255,255]];
                    return ['bg'=>[120,120,120], 'fg'=>[255,255,255]];
                },
            ]);

            // ── Leyenda ──
            $pdf->Ln(3);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->Cell(0, 4, formatoControlador::iso(
                'Nota mínima aprobatoria: ' . $notaAprob . ' | "NP" = No presentó (cuenta como 0 en el aporte)'
            ), 0, 1);

            // ── Footer ──
            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

            formatoControlador::pdfFooter($pdf, [
                'ubicacion' => $direccion,
            ]);

            formatoControlador::pdfSalida(
                $pdf,
                formatoControlador::nombreArchivo('Notas_Estudiante_' . $ciDecrypted, 'pdf')
            );

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    
    
    
//excel
      public function generar_excel_notas_estudiante_controlador(int $registrationId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_notas_estudiante_controlador($registrationId);
            if (!$datos['ok']) die($datos['message']);

            $reg         = $datos['registration'];
            $evaluations = $datos['evaluations'];
            $summary     = $datos['grade_summary'];

            $student = $reg['student'] ?? [];
            $course  = $reg['course']  ?? [];
            $section = $reg['section'] ?? null;
            $period  = $reg['period']  ?? [];
            $teacher = $section['teacher'] ?? null;
            
			$ciDecrypted = mainModel::decryption($student['ci'] ?? '');

            $nombreCompleto = trim(($student['last_name'] ?? '') . ' ' . ($student['name'] ?? ''));
            $nombreProf     = $teacher ? trim(($teacher['name'] ?? '') . ' ' . ($teacher['last_name'] ?? '')) : '';

            // ── Columnas (8: A..H) ──
            $columnas = [
                ['key'=>'n',       'label'=>'Nº',        'align'=>'C', 'formato'=>'int'],
                ['key'=>'fecha',   'label'=>'Fecha',     'align'=>'C'],
                ['key'=>'tipo',    'label'=>'Tipo'],
                ['key'=>'obj',     'label'=>'Objetivo',  'align'=>'C'],
                ['key'=>'pct',     'label'=>'%',         'align'=>'C'],
                ['key'=>'puntaje', 'label'=>'Puntaje',   'align'=>'C'],
                ['key'=>'aporte',  'label'=>'Aporte',    'align'=>'C'],
                ['key'=>'estado',  'label'=>'Estado',    'align'=>'C'],
            ];
            $anchos = ['n'=>6, 'fecha'=>14, 'tipo'=>22, 'obj'=>10, 'pct'=>8, 'puntaje'=>10, 'aporte'=>12, 'estado'=>16];

            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            // ── Excel ──
            $excel = formatoControlador::excelIniciar(
                'REPORTE DE NOTAS DEL ESTUDIANTE',
                'Notas',
                count($columnas),
                $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            // ── Contexto ──
            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'ESTUDIANTE: ' . $nombreCompleto . '  |  C.I: ' . ($student['ci'] ?? ''),
                'CURSO: '     . ($course['name'] ?? '') . (!empty($course['type']) ? ' (' . $course['type'] . ')' : '') .
                                '  |  SECCIÓN: ' . ($section['name'] ?? '—') .
                                ($nombreProf ? '  |  PROFESOR: ' . $nombreProf : ''),
                'PERÍODO: '   . ($period['name'] ?? '') . (!empty($period['type']) ? ' (' . $period['type'] . ')' : '') .
                                '  |  Fechas: ' . ($period['start_date'] ?? '') . ' al ' . ($period['end_date'] ?? ''),
                'Fecha de inscripción: ' . ($reg['registration_date'] ?? ''),
            ], count($columnas));

            // ── Tabla 1: evaluaciones ──
            $sheet->setCellValue("A{$fila}", 'DETALLE DE EVALUACIONES');
            $sheet->mergeCells("A{$fila}:H{$fila}");
            $sheet->getStyle("A{$fila}:H{$fila}")->applyFromArray(['font'=>['bold'=>true]]);
            $fila++;

            if (empty($evaluations)) {
                $sheet->setCellValue("A{$fila}", 'No hay evaluaciones registradas en esta sección.');
                $sheet->mergeCells("A{$fila}:H{$fila}");
                $sheet->getStyle("A{$fila}")->getFont()->setItalic(true);
                $fila++;
            } else {
                $filasEv = [];
                foreach ($evaluations as $i => $ev) {
                    $fecha = $ev['evaluation_date'] ?? '';
                    if ($fecha !== '') {
                        try { $fecha = (new DateTime($fecha))->format('d/m/Y'); }
                        catch (Exception $e) {}
                    }
                    $obj = $ev['objective'] ?? '';
                    if ($obj !== '' && $obj !== null) {
                        $obj = (floor((float)$obj) == (float)$obj)
                            ? (string)(int)$obj
                            : rtrim(rtrim(number_format((float)$obj, 4, '.', ''), '0'), '.');
                    } else $obj = '';

                    $presented = (bool)($ev['presented'] ?? false);
                    $filasEv[] = [
                        'n'       => $i + 1,
                        'fecha'   => $fecha,
                        'tipo'    => $ev['evaluation_type'] ?? '',
                        'obj'     => $obj,
                        'pct'     => ((int)($ev['percentage'] ?? 0)) . '%',
                        'puntaje' => $presented ? (int)($ev['score'] ?? 0) : 'NP',
                        'aporte'  => (float)($ev['weighted_contribution'] ?? 0),
                        'estado'  => $presented ? 'Presentó' : 'No presentó',
                    ];
                }

                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filasEv, $anchos, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'estado') return null;
                        return $row['estado'] === 'Presentó'
                            ? ['bg'=>'C6EFCE', 'fg'=>'006100', 'bold'=>true]
                            : ['bg'=>'FFC7CE', 'fg'=>'9C0006', 'bold'=>true];
                    },
                ]);
            }
            $fila += 2;

            // ── Tabla 2: resumen (manual por el merge F:H del Estado) ──
            $sheet->setCellValue("A{$fila}", 'RESUMEN DE CALIFICACIONES');
            $sheet->mergeCells("A{$fila}:H{$fila}");
            $sheet->getStyle("A{$fila}:H{$fila}")->applyFromArray(['font'=>['bold'=>true]]);
            $fila++;

            $totalEval   = (int)   ($summary['total_evaluations']         ?? 0);
            $pctTotal    = (int)   ($summary['percentage_total']          ?? 0);
            $presentadas = (int)   ($summary['presented_evaluations']     ?? 0);
            $noPresent   = (int)   ($summary['not_presented_evaluations'] ?? 0);
            $notaFinal   = $summary['final_grade'] ?? null;
            $status      = (string)($summary['status']        ?? '');

            $bgBlue = ['fill'=>['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,'startColor'=>['rgb'=>'DCDCFF']]];
            $bold   = ['font'=>['bold'=>true]];
            $center = ['alignment'=>['horizontal'=>\PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER]];

            $sheet->setCellValue("A{$fila}", "Evaluaciones");
            $sheet->setCellValue("B{$fila}", "Presentadas");
            $sheet->setCellValue("C{$fila}", "No presentadas");
            $sheet->setCellValue("D{$fila}", "% Total");
            $sheet->setCellValue("E{$fila}", "Nota final");
            $sheet->setCellValue("F{$fila}", "Estado");
            $sheet->mergeCells("F{$fila}:H{$fila}");
            $sheet->getStyle("A{$fila}:H{$fila}")->applyFromArray($bgBlue);
            $sheet->getStyle("A{$fila}:H{$fila}")->applyFromArray($bold);
            $sheet->getStyle("A{$fila}:H{$fila}")->applyFromArray($center);
            $fila++;

            $sheet->setCellValue("A{$fila}", $totalEval);
            $sheet->setCellValue("B{$fila}", $presentadas);
            $sheet->setCellValue("C{$fila}", $noPresent);
            $sheet->setCellValue("D{$fila}", $pctTotal . '%');
            $sheet->setCellValue("E{$fila}", ($notaFinal === null) ? '—' : $notaFinal);
            $sheet->setCellValue("F{$fila}", $status);
            $sheet->mergeCells("F{$fila}:H{$fila}");

            if ($status === 'Aprobado')       { $bg = 'C6EFCE'; $fg = '006100'; }
            elseif ($status === 'Reprobado')  { $bg = 'FFC7CE'; $fg = '9C0006'; }
            else                              { $bg = 'E7E6E6'; $fg = '595959'; }

            $sheet->getStyle("F{$fila}:H{$fila}")->applyFromArray([
                'fill' => ['fillType'=>\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID, 'startColor'=>['rgb'=>$bg]],
                'font' => ['color'=>['rgb'=>$fg], 'bold'=>true],
            ]);
            $sheet->getStyle("A{$fila}:H{$fila}")->applyFromArray($center);

            // Bordes del resumen
            $rangoRes = "A" . ($fila - 1) . ":H{$fila}";
            $sheet->getStyle($rangoRes)->getBorders()->getAllBorders()->setBorderStyle(
                \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN
            );

            // ── Footer ──
            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';

            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion,
                'num_cols'  => 8,
            ]);

            formatoControlador::excelSalida(
                $excel['spreadsheet'],
                formatoControlador::nombreArchivo('Notas_Estudiante_' .$ciDecrypted, 'xlsx')
            );

        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    
    
    
//csv
      public function generar_csv_notas_estudiante_controlador(int $registrationId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_notas_estudiante_controlador($registrationId);
            if (!$datos['ok']) die($datos['message']);

            $reg         = $datos['registration'];
            $evaluations = $datos['evaluations'];
            $summary     = $datos['grade_summary'];

            $student = $reg['student'] ?? [];
            $course  = $reg['course']  ?? [];
            $section = $reg['section'] ?? null;
            $period  = $reg['period']  ?? [];
            
            
			$ciDecrypted = mainModel::decryption($student['ci'] ?? '');

            $nombreCompleto = trim(($student['last_name'] ?? '') . ' ' . ($student['name'] ?? ''));

            // ── Columnas ──
            $columnasEv = [
                ['key'=>'n',       'label'=>'Nº',     'formato'=>'int'],
                ['key'=>'fecha',   'label'=>'Fecha'],
                ['key'=>'tipo',    'label'=>'Tipo'],
                ['key'=>'obj',     'label'=>'Objetivo'],
                ['key'=>'pct',     'label'=>'%'],
                ['key'=>'puntaje', 'label'=>'Puntaje'],
                ['key'=>'aporte',  'label'=>'Aporte'],
                ['key'=>'estado',  'label'=>'Estado'],
            ];

            $filasEv = [];
            foreach ($evaluations as $i => $ev) {
                $fecha = $ev['evaluation_date'] ?? '';
                if ($fecha !== '') {
                    try { $fecha = (new DateTime($fecha))->format('d/m/Y'); }
                    catch (Exception $e) {}
                }
                $obj = $ev['objective'] ?? '';
                if ($obj !== '' && $obj !== null) {
                    $obj = (floor((float)$obj) == (float)$obj)
                        ? (string)(int)$obj
                        : rtrim(rtrim(number_format((float)$obj, 4, '.', ''), '0'), '.');
                } else $obj = '';

                $presented = (bool)($ev['presented'] ?? false);
                $filasEv[] = [
                    'n'       => $i + 1,
                    'fecha'   => $fecha,
                    'tipo'    => $ev['evaluation_type'] ?? '',
                    'obj'     => $obj,
                    'pct'     => ((int)($ev['percentage'] ?? 0)) . '%',
                    'puntaje' => $presented ? (int)($ev['score'] ?? 0) : 'NP',
                    'aporte'  => number_format((float)($ev['weighted_contribution'] ?? 0), 2, '.', ','),
                    'estado'  => $presented ? 'Presentó' : 'No presentó',
                ];
            }

            // ── Resumen (estructura clave-valor, se arma directo con fputcsv) ──
            $totalEval   = (int)   ($summary['total_evaluations']         ?? 0);
            $presentadas = (int)   ($summary['presented_evaluations']     ?? 0);
            $noPresent   = (int)   ($summary['not_presented_evaluations'] ?? 0);
            $pctTotal    = (int)   ($summary['percentage_total']          ?? 0);
            $notaFinal   = $summary['final_grade'] ?? null;
            $status      = (string)($summary['status'] ?? '');

            // ── Salida ──
            $nombre = formatoControlador::nombreArchivo('Notas_Estudiante_' .$ciDecrypted, 'csv');
            $out    = formatoControlador::csvIniciar($nombre);

            formatoControlador::csvContexto($out, [
                'REPORTE DE NOTAS DEL ESTUDIANTE',
                'ESTUDIANTE: ' . $nombreCompleto . ' | C.I: ' . $ciDecrypted,
                'CURSO: '    . ($course['name'] ?? '') . (!empty($course['type']) ? ' (' . $course['type'] . ')' : ''),
                'SECCIÓN: '  . ($section['name'] ?? '—'),
                'PERÍODO: '  . ($period['name'] ?? '') . (!empty($period['type']) ? ' (' . $period['type'] . ')' : ''),
                'Fecha de inscripción: ' . ($reg['registration_date'] ?? ''),
            ]);

            // Tabla 1
            fputcsv($out, ['DETALLE DE EVALUACIONES'], ';', '"', '');
            formatoControlador::csvTabla($out, $columnasEv, $filasEv);

            fputcsv($out, [], ';', '"', '');

            // Tabla 2
            fputcsv($out, ['RESUMEN DE CALIFICACIONES'], ';', '"', '');
            fputcsv($out, ['Evaluaciones', 'Presentadas', 'No presentadas', '% Total', 'Nota final', 'Estado'], ';', '"', '');
            fputcsv($out, [
                $totalEval,
                $presentadas,
                $noPresent,
                $pctTotal . '%',
                ($notaFinal === null) ? '—' : $notaFinal,
                $status,
            ], ';', '"', '');

            formatoControlador::csvSalida($out);

        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }  
    
    
    
}
?>