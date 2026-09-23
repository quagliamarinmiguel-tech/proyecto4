<?php

    require_once "mainModel.php";

    class periodoModelo extends mainModel{




//        obtener_evaluaciones_modelo    estos son los que nos competen, estos traen todo lo que necesitamos
  //      obtener_estudiantes_modelo
    //    obtener_puntajes_modelo

        //a lo mejor paso la logica de las evaluaciones y secciones pa otro controlador

//----------------------- aqui pondre las funciones de periodo new, update y list-------------------------

    //estas 2 son para traer cursos y profes para la seccion en periodo new y update de agregar cursos vigentes o period_courses    
   
        
    /*public static function listar_cursos_supabase_para_periodo(string $accessToken): array {
        $params = [
            'select' => 'id, name, type',
            'state' => 'eq.1',
            'order' => 'name.asc'
        ];
        return SupabaseApi::getWithToken('course', $params, $accessToken);
    }*/
    public static function listar_cursos_supabase_para_periodo(string $accessToken, ?string $type = null): array {
        $params = [
            'select' => 'id, name, type',
            'state'  => 'eq.1',
            'order'  => 'name.asc'
        ];

        // Filtro opcional por tipo de curso
        if (!empty($type)) {
            $params['type'] = 'eq.' . $type;
        }

        return SupabaseApi::getWithToken('course', $params, $accessToken);
    }
        
        
    public static function listar_profesores_supabase_para_periodo(string $accessToken): array {
        $params = [
            'select' => 'id, name, last_name',
            'state' => 'eq.1',
            'order' => 'name.asc'
        ];
        return SupabaseApi::getWithToken('teacher', $params, $accessToken);
    }
/*-----------------------------------------------------------------------------------------------------------------------------------*/
//estas son las validaciones de periodo new y update
        //para verificar nombre de periodo duplicado en periodo new y update, lo usa el ajax para validar q el nombre sea valido antes de enviarlo
    public static function verificar_periodo_duplicado_supabase($nombre, $accessToken, $id_excluir = null): array {
        $filtros = [
            'select' => 'id',
            'name' => 'eq.' . $nombre,
            'state' => 'neq.0',
        ];
        if ($id_excluir !== null && $id_excluir !== '') {
            $filtros['id'] = 'neq.' . $id_excluir;
        }
        return SupabaseApi::getWithToken('period', $filtros, $accessToken);
    }        
   
    public static function obtener_ultimo_periodo_supabase(string $tipo, string $accessToken): array {
        // Query to get the last period of the same type ordered by start_date descending
        $params = [
            'select' => 'id, name, start_date, end_date',
            'type' => 'eq.' . $tipo,
            'order' => 'start_date.desc',
            'limit' => '1'
        ];
        return SupabaseApi::getWithToken('period', $params, $accessToken);
    }
/*--------------------------------------------------------------------------------------------------------------------------------*/
   
        
        
        
        
        
        
        
        
        
        /*     
public static function crear_periodo_supabase(array $datos, string $accessToken): array {
    return SupabaseApi::postWithToken('period', [
        'name' => $datos['nombre'],
        'type' => $datos['tipo'],
        'current_state' => $datos['estado'],
        'start_date' => $datos['fecha_inicio'],
        'end_date' => $datos['fecha_fin'],
        'state' => 1,
    ], $accessToken);
}

public static function crear_costos_periodo_supabase(array $costos, string $accessToken): array {
    return SupabaseApi::postWithToken('period_costs', $costos, $accessToken);
}

public static function crear_cursos_periodo_supabase(array $cursos, string $accessToken): array {
    return SupabaseApi::postWithToken('period_course', $cursos, $accessToken);
}*/


/*-------------------------------------------------------------------------*/


//crea el perido con sus period cost y period course y sections
    //    @parametros que incluyen el periodo, sus costos o periodcosts que cambian dependiendo del type del periodo, sus periodcourse para crear si los incluye y una section A para cada periodCourse
     public static function crear_periodo_rpc(array $params, string $accessToken): array {
    	return SupabaseApi::rpcWithToken('crear_periodo', $params, $accessToken);
	}   
        
        //patra actualizar el periodo en period update
    public static function actualizar_periodo(array $params, string $accessToken): array{
        return SupabaseApi::rpcWithToken('actualizar_periodo', $params, $accessToken);
    }
        
      
        
        

//lista periodos, en teorioa con state 1 y 0
        
public static function listar_periodos_con_costos_supabase(array $filtros, string $accessToken, int $limit, int $offset): array{
    $params = [
        'p_busqueda' => $filtros['busqueda'] ?? null,
        'p_tipo' => $filtros['tipo'] ?? null,
        'p_estado' => $filtros['estado'] ?? null,
        'p_orden' => $filtros['orden'] ?? 'id_desc',
        'p_limit' => $limit,
        'p_offset' => $offset,
        'p_state' => (int) ($filtros['state'] ?? 1),
        'p_id' => isset($filtros['id']) && $filtros['id'] !== '' ? (int) $filtros['id'] : null, // 🔥 NUEVO
        'p_anio' => isset($filtros['anio']) && $filtros['anio'] !== '' ? (int) $filtros['anio'] : null, // 🔥 NUEVO
    ];
    return SupabaseApi::rpcWithToken('listar_periodos', $params, $accessToken);
}
 
    public static function obtener_periodo_con_cursos_costos_supabase(int $id, string $accessToken): array{
        return SupabaseApi::rpcWithToken('obtener_periodo_con_cursos_para_period_update', ['p_period_id' => $id], $accessToken);
    }
        
        
        
        
        
        

    public static function archivar_periodo_cascada_supabase(int $periodId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('archivar_periodo_cascada', ['p_period_id' => $periodId], $accessToken);
    }
        
    public static function restaurar_periodo_cascada_supabase(int $periodId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('restaurar_periodo', ['p_period_id' => $periodId], $accessToken);
    }
        
        /*----------------------------------------------------------funciones de period course---------------------------------------------------------------------*/
        /*
    public static function listar_period_course_resumen_supabase(array $filtros, string $accessToken, int $limit, int $offset): array{
        $params = [
            'p_busqueda_periodo' => $filtros['busqueda_periodo'] ?? null,
            'p_busqueda_curso' => $filtros['busqueda_curso'] ?? null,
            'p_id_period_course' => isset($filtros['id_period_course']) && $filtros['id_period_course'] !== '' ? (int) $filtros['id_period_course'] : null,
            'p_estado_periodo' => $filtros['estado_periodo'] ?? null,
            'p_orden' => $filtros['orden'] ?? 'periodo_desc',
            'p_limit' => $limit,
            'p_offset' => $offset,
            'p_state' => (int) ($filtros['state'] ?? 1),
        ];
        return SupabaseApi::rpcWithToken('listar_period_course_resumen', $params, $accessToken);
    }   */ 
    


    public static function listar_period_course_resumen_supabase(array $filtros, string $accessToken, int $limit, int $offset): array
    {
        $params = [
            'p_busqueda_periodo' => $filtros['busqueda_periodo'] ?? null,
            'p_busqueda_curso'   => $filtros['busqueda_curso'] ?? null,
            'p_id_period_course' => isset($filtros['id_period_course']) && $filtros['id_period_course'] !== '' ? (int) $filtros['id_period_course'] : null,
            'p_estado_periodo'   => $filtros['estado_periodo'] ?? null,
            'p_orden'            => $filtros['orden'] ?? 'periodo_desc',
            'p_limit'            => $limit,
            'p_offset'           => $offset,
            'p_state'            => (int) ($filtros['state'] ?? 1),
            'p_course_id'        => isset($filtros['course_id']) && $filtros['course_id'] !== '' ? (int) $filtros['course_id'] : null,
            'p_period_id'        => isset($filtros['period_id']) && $filtros['period_id'] !== '' ? (int) $filtros['period_id'] : null,
        ];
        return SupabaseApi::rpcWithToken('listar_period_course_resumen', $params, $accessToken);
    }
        
        
//para period list, desde el link de ver periodos de course list busca el curso para mostrar datos y filtrar la lista de periodcourse
    public static function obtener_curso_supabase(int $id, string $accessToken): array{
        return SupabaseApi::getWithToken('course', [
            'select' => 'id, name, type',
            'id' => 'eq.' . $id,
        ], $accessToken);
    }
        
        
//para period list, desde el link de ver periodos de period list busca el periodo para mostrar datos y filtrar la lista de periodcourse
    public static function obtener_periodo_supabase(int $id, string $accessToken): array{
        return SupabaseApi::getWithToken('period', [
            'select' => 'id, name, type',
            'id' => 'eq.' . $id,
        ], $accessToken);
    }
        
        
//esto es para el modal de secciones hecho con js en period list
    public static function obtener_secciones_por_period_course_supabase(
        int $periodCourseId,
        bool $soloArchivadas,
        string $accessToken
    ): array {
        $params = [
            'p_period_course_id' => $periodCourseId,
            'p_solo_archivadas' => $soloArchivadas,
        ];
        return SupabaseApi::rpcWithToken('obtener_secciones_para_period_course', $params, $accessToken);
    }    
        
        
//archivar periodo de curso sin funcion de reembolso
    public static function archivar_period_course_supabase(int $periodCourseId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('archivar_period_course', ['p_period_course_id' => $periodCourseId], $accessToken);
    }
        
        
        
        
        
        
        
/*----------------------------------------------a partir de aqui empiezan las secciones---------------------------------------------------------*/
        
    public static function crear_seccion_supabase(array $datos, string $accessToken): array{
        return SupabaseApi::postWithToken('section', $datos, $accessToken);
    }
        
    public static function actualizar_seccion_supabase(int $sectionId, array $data, string $accessToken): array{
    	return SupabaseApi::patchWithToken('section', ['id' => 'eq.' . $sectionId], $data, $accessToken);
	}
        

    /**
     * Obtiene el current_state del periodo asociado a una sección o period_course.
     * @param int $periodCourseId ID del period_course (si se conoce)
     * @param int|null $sectionId ID de la sección (alternativa)
     * @return string|null Estado del periodo ('Próximo', 'Vigente', 'Terminado') o null si no existe
     */
    public static function obtener_estado_periodo_de_seccion_supabase(
        string $accessToken,
		int $periodCourseId,
        ?int $sectionId = null
    ): ?string {
        if ($sectionId !== null) {
            $resultado = SupabaseApi::getWithToken('section', [
                'select' => 'period_course_id',
                'id' => 'eq.' . $sectionId,
            ], $accessToken);
            if (!$resultado['ok'] || empty($resultado['data'][0])) {
                return null;
            }
            $periodCourseId = (int) $resultado['data'][0]['period_course_id'];
        }

        // Obtener periodo desde period_course
        $resultado = SupabaseApi::getWithToken('period_course', [
            'select' => 'period!inner(current_state)',
            'id' => 'eq.' . $periodCourseId,
        ], $accessToken);

        if (!$resultado['ok'] || empty($resultado['data'][0])) {
            return null;
        }

        return $resultado['data'][0]['period']['current_state'] ?? null;
    }
        
        
    public static function eliminar_seccion_supabase(int $sectionId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('eliminar_seccion', ['p_section_id' => $sectionId], $accessToken);
    }

        // Obtener datos para el modal de transferencia de estudiantes entre secciones
    public static function obtener_datos_transferencia_supabase(int $sectionId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('obtener_datos_transferencia_estudiantes', ['p_section_id' => $sectionId], $accessToken);
    }

    // Transferir estudiantes seleccionados
    public static function transferir_estudiantes_seleccionados_supabase(int $origenId, int $destinoId, array $registrationIds, string $accessToken): array{
        $params = [
            'p_section_origen_id' => $origenId,
            'p_section_destino_id' => $destinoId,
            'p_registration_ids' => $registrationIds,
        ];
        return SupabaseApi::rpcWithToken('transferir_estudiantes_seleccionados', $params, $accessToken);
    }
        
        
/*------------------------------------------------------a partir de aqui empiezan las evalauciones-----------------------------------------------------------------------------*/
        
            /**
     * Obtiene una sección con toda su información relacionada (periodo, curso, profesor)
     */
    public static function obtener_seccion_completa_supabase(int $sectionId, string $accessToken): array
    {
        $resultado = SupabaseApi::getWithToken('section', [
            'select' => 'id,period_course_id,name,section_day,teacher_id,state,period_course!inner(course_id,period_id,period!inner(id,name,type,start_date,end_date),course!inner(id,name,type))',
            'id' => 'eq.' . $sectionId,
        ], $accessToken);
        return $resultado;
    }

    /**
     * Obtiene todas las evaluaciones de una sección
     */
    public static function obtener_evaluaciones_por_seccion_supabase(int $sectionId, string $accessToken): array
    {
        $resultado = SupabaseApi::getWithToken('evaluation', [
            'select' => 'id,evaluation_type,percentage,state',
            'section_id' => 'eq.' . $sectionId,
            'order' => 'id.asc',
        ], $accessToken);
        return $resultado;
    }



/**
 * Obtiene los estudiantes inscritos en una sección (solo activos o todos).
 * @param bool $soloActivos Si true, solo estudiantes con registration.state = 1.
 */
public static function obtener_estudiantes_por_seccion_supabase(
    int $sectionId,
    int $limit,
    int $offset,
    string $accessToken,
    bool $soloActivos = true
): array {
    $filtroState = $soloActivos ? 'eq.1' : 'neq.0'; // o 'eq.0' si quieres solo archivados
    return SupabaseApi::getWithToken('registration', [
        'select' => 'id, student_id, student!inner(id, ci, name, last_name)',
        'section_id' => 'eq.' . $sectionId,
        'state' => $filtroState,
       // 'order' => 'student.last_name.asc',
        'limit' => $limit,
        'offset' => $offset,
    ], $accessToken);
}
  
        
/**
 * Obtiene todos los puntajes de una evaluación (para mapear por estudiante).
 * @param int $evaluationId ID de la evaluación
 * @param int $state Estado de la evaluación (1=activa, 0=archivada) para filtrar calificaciones
 * @param string $accessToken Token de Supabase
 */
public static function obtener_puntajes_por_evaluacion_supabase(
    int $evaluationId,
    int $state,
    string $accessToken
): array {
    return SupabaseApi::getWithToken('qualification_student', [
        'select' => 'registration_id, score',
        'evaluation_id' => 'eq.' . $evaluationId,
        'state' => 'eq.' . $state, // 🔥 Usar el state que se pasa como parámetro
    ], $accessToken);
}
        
        

    // Obtener sección por period_course_id y letra (con periodo, curso, profesor)
        // quite el equal 1 para usar esta funcion para seccion consult archived
        //            'state' => 'neq.0',

    public static function obtener_seccion_por_period_course_y_letra_supabase(
        int $periodCourseId,
        string $sectionLetter,
        string $accessToken
    ): array {
        return SupabaseApi::getWithToken('section', [
            'select' => 'id, period_course_id, name, section_day, teacher_id, state, period_course!inner(course_id, period_id, period!inner(name, type, start_date, end_date), course!inner(name, type))',
            'period_course_id' => 'eq.' . $periodCourseId,
            'name' => 'eq.' . $sectionLetter,
            //'state' => 'neq.0',
        ], $accessToken);
    }

    // Obtener profesor por ID
    public static function obtener_profesor_por_id_supabase(int $teacherId, string $accessToken): array {
        return SupabaseApi::getWithToken('teacher', [
            'select' => 'id, name, last_name',
            'id' => 'eq.' . $teacherId,
        ], $accessToken);
    }

        
 
        
        
// para evaluation new
    public static function crear_evaluacion_con_puntajes_supabase(array $datos, string $accessToken): array{
        $params = [
            'p_section_id' => $datos['section_id'],
            'p_name' => $datos['evaluation_type'],
            'p_objective' => (float) $datos['objective'],
            'p_percentage' => (int) $datos['percentage'],
            'p_evaluation_date' => $datos['evaluation_date'],
        	'p_puntajes' => $datos['puntajes']
        ];
        //var_dump($params);die();
        return SupabaseApi::rpcWithToken('crear_evaluacion_con_puntajes', $params, $accessToken);
    } //            'p_puntajes' => json_encode($datos['puntajes']),

        
        

    // Listar evaluaciones de una sección con paginación y filtro por state
    public static function listar_evaluaciones_por_seccion_supabase(
        int $sectionId,
        int $limit,
        int $offset,
        string $accessToken,
        int $state = 1 // 1: activas, 0: archivadas
    ): array {
        return SupabaseApi::getWithToken('evaluation', [
            'select' => 'id, evaluation_type, objective, percentage, evaluation_date, state',
            'section_id' => 'eq.' . $sectionId,
            'state' => 'eq.' . $state,
            'order' => 'id.desc',
            'limit' => $limit,
            'offset' => $offset,
        ], $accessToken);
    }

        
        
        
    // Contar total de evaluaciones de una sección con filtro por state
    public static function contar_evaluaciones_por_seccion_supabase(
        int $sectionId,
        string $accessToken,
        int $state = 1
    ): array {
        return SupabaseApi::getWithToken('evaluation', [
            'select' => 'count',
            'section_id' => 'eq.' . $sectionId,
            'state' => 'eq.' . $state,
        ], $accessToken);
    }
        
        
        

/**
 * Obtiene las evaluaciones de una sección con un estado específico.
 */
public static function obtener_evaluaciones_por_seccion_y_state_supabase(
    int $sectionId,
    string $accessToken, int $state = 1,
): array {
    return SupabaseApi::getWithToken('evaluation', [
        'select' => 'id, evaluation_type, objective, percentage, state',
        'section_id' => 'eq.' . $sectionId,
        'state' => 'eq.' . $state,
        'order' => 'objective.asc', // orden por objetivo (opcional)
    ], $accessToken);
}


/**
 * Cuenta el total de estudiantes inscritos en una sección.
 *
public static function contar_estudiantes_por_seccion_supabase(
    int $sectionId,
    string $accessToken,
    bool $soloActivos = true
): int {
    $filtroState = $soloActivos ? 'eq.1' : 'neq.0';
    $resultado = SupabaseApi::getWithToken('registration', [
        'select' => 'count',
        'section_id' => 'eq.' . $sectionId,
        'state' => $filtroState,
    ], $accessToken);
    return $resultado['ok'] ? (int) ($resultado['data'][0]['count'] ?? 0) : 0;
}
*/

//para lista de estudiantes y puntajes de seccion consult        
    public static function listar_estudiantes_seccion_consulta_supabase(
        int $sectionId,
        int $limit,
        int $offset,
        int $state,
        string $accessToken
    ): array {
        $params = [
            'p_section_id' => $sectionId,
            'p_limit' => $limit,
            'p_offset' => $offset,
            'p_state' => $state,
        ];
        return SupabaseApi::rpcWithToken('listar_estudiantes_seccion_consulta', $params, $accessToken);
    }
        
        
        
        
        

    public static function obtener_evaluacion_para_update_supabase(int $evaluationId, string $accessToken): array
    {
        // 1. Obtener evaluación
        $eval = SupabaseApi::getWithToken('evaluation', [
            'select' => 'id, section_id, evaluation_type, objective, percentage, evaluation_date, state',
            'id' => 'eq.' . $evaluationId,
        ], $accessToken);
        if (!$eval['ok'] || empty($eval['data'][0])) {
            return ['ok' => false, 'message' => 'Evaluación no encontrada'];
        }
        $evaluacion = $eval['data'][0];
        $sectionId = $evaluacion['section_id'];

        // 2. Obtener todos los estudiantes de la sección (usar función existente con límite alto)
        $estudiantesResult = self::obtener_estudiantes_por_seccion_supabase(
            $sectionId,
            9999, // límite grande para obtener todos
            0,
            $accessToken,
            true // solo activos
        );
        $estudiantes = $estudiantesResult['data'] ?? [];

        // 3. Obtener puntajes existentes (usar función existente)
        $puntajesResult = self::obtener_puntajes_por_evaluacion_supabase(
            $evaluationId,
            $evaluacion['state'], // usar el state de la evaluación para filtrar calificaciones
            $accessToken
        );
        $puntajesExistentes = $puntajesResult['data'] ?? [];
        $puntajesMap = [];
        foreach ($puntajesExistentes as $p) {
            $puntajesMap[$p['registration_id']] = $p['score'];
        }

        // 4. Obtener rango de fechas del periodo (de la sección)
        $seccionResult = SupabaseApi::getWithToken('section', [
            'select' => 'period_course!inner(period!inner(start_date,end_date))',
            'id' => 'eq.' . $sectionId,
        ], $accessToken);
        $period = [];
        if ($seccionResult['ok'] && !empty($seccionResult['data'][0])) {
            $period = $seccionResult['data'][0]['period_course']['period'] ?? [];
        }

        // 5. Calcular suma de porcentajes de otras evaluaciones
        $otrasEvalResult = SupabaseApi::getWithToken('evaluation', [
            'select' => 'percentage',
            'section_id' => 'eq.' . $sectionId,
            'state' => 'eq.1', // solo activas
            'id' => 'neq.' . $evaluationId,
        ], $accessToken);
        $sumaExcluyendo = 0;
        if ($otrasEvalResult['ok']) {
            foreach ($otrasEvalResult['data'] as $e) {
                $sumaExcluyendo += (float) ($e['percentage'] ?? 0);
            }
        }

        return [
            'ok' => true,
            'evaluacion' => $evaluacion,
            'estudiantes' => $estudiantes,
            'puntajes_map' => $puntajesMap,
            'period_start' => $period['start_date'] ?? '',
            'period_end' => $period['end_date'] ?? '',
            'suma_excluyendo' => $sumaExcluyendo,
        ];
    }

    /**
     * Actualiza una evaluación y sus puntajes (con UPSERT).
     */
    public static function actualizar_evaluacion_con_puntajes_supabase(array $datos, string $accessToken): array
    {
        $params = [
            'p_evaluation_id' => (int) $datos['evaluation_id'],
            'p_evaluation_type' => $datos['evaluation_type'],
            'p_objective' => (float) $datos['objective'],
            'p_percentage' => (int) $datos['percentage'],
            'p_evaluation_date' => $datos['evaluation_date'],
            'p_puntajes' => $datos['puntajes'], // array PHP, se convierte a JSONB
        ];
        return SupabaseApi::rpcWithToken('actualizar_evaluacion_con_puntajes', $params, $accessToken);
    }
        

        //archivar evaluacion
    public static function archivar_evaluacion_con_puntajes_supabase(int $evaluationId, string $accessToken): array
    {
        return SupabaseApi::rpcWithToken('archivar_evaluacion_con_puntajes', ['p_evaluation_id' => $evaluationId], $accessToken);
    }
	//restaurar evaluaciones
    public static function restaurar_evaluacion_con_puntajes_supabase(int $evaluationId, string $accessToken): array
    {
        return SupabaseApi::rpcWithToken('restaurar_evaluacion_con_puntajes', ['p_evaluation_id' => $evaluationId], $accessToken);
    }
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
public static function obtener_evaluaciones_modelo($id_seccion){
    $sql = mainModel::conectar()->prepare("
        SELECT id_evaluacion, tipo_evaluacion, obj, porcentaje, fecha_evaluacion 
        FROM evaluacion 
        WHERE id_seccion = :id AND archivar_evaluacion = 1 
        ORDER BY obj ASC
    ");
    $sql->bindParam(":id", $id_seccion);
    $sql->execute();
    return $sql;
}

        
//tambien del paginador de estudiantes y sus puntajes
protected static function obtener_estudiantes_modelo($id_seccion, $inicio, $registros){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);
    $inicio = (int) $inicio;
    $registros = (int) $registros;
    $limit="";
    if($inicio >= 0 && $registros > 0){
        $limit = "LIMIT :inicio, :registros";
    }

    //LIMIT :inicio, :registros
    $sql = mainModel::conectar()->prepare("
        SELECT 
            est.id_estudiante,
            est.ci_estudiante,
            est.nombre,
            est.apellido,
            i.id_inscripcion
        FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
        WHERE 
            i.id_seccion_fk = :id_seccion
            AND i.archivada = 1
            AND est.estado_estudiante = 1
            AND s.archivar_seccion = 1
        ORDER BY est.apellido ASC, est.nombre ASC
        $limit
    ");

    $sql->bindParam(":id_seccion", $id_seccion, PDO::PARAM_INT);
    if($inicio >= 0 && $registros > 0){
        $sql->bindParam(":inicio", $inicio, PDO::PARAM_INT);
        $sql->bindParam(":registros", $registros, PDO::PARAM_INT);
    }
    $sql->execute();

    return $sql;
}
//paginador de estudiantes y puntos tambien
protected static function obtener_puntajes_modelo($id_inscripcion){
    $id_inscripcion = mainModel::limpiar_cadena($id_inscripcion);

    $sql = mainModel::conectar()->prepare("
        SELECT 
            pe.id_evaluacion,
            pe.puntaje
        FROM puntaje_estudiante pe
        INNER JOIN evaluacion ev ON pe.id_evaluacion = ev.id_evaluacion
        INNER JOIN inscripcion i ON pe.id_inscripcion = i.id_inscripcion
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
        WHERE 
            pe.id_inscripcion = :id_inscripcion
            AND pe.archivar_puntaje = 1
            AND ev.archivar_evaluacion = 1
            AND i.archivada = 1
            AND est.estado_estudiante = 1
            AND s.archivar_seccion = 1
    ");

    $sql->bindParam(":id_inscripcion", $id_inscripcion, PDO::PARAM_INT);
    $sql->execute();

    return $sql;
}


 
public function obtener_datos_seccion_para_ambos_casos_modelo($curso_vigente_id, $seccion_letra) {
    $pdo = mainModel::conectar();

    // Obtener sección, curso vigente, duración y luego determinar si es cohorte o intensivo
    $sql = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.dia,
            cv.id_curso_vigente,
            cv.id_duracion_fk,
            c.nombre_curso,
            dp.fecha_inicio,dp.fecha_fin,dp.estado_periodo,
            p.id_profesor,
            p.nombre_profesor,
            p.apellido_profesor,

            -- para evaluacion new y update
            (SELECT COUNT(*) FROM evaluacion e WHERE e.id_seccion = s.id_seccion AND e.archivar_evaluacion = 1) AS total_evaluaciones,
            (SELECT SUM(porcentaje) FROM evaluacion e WHERE e.id_seccion = s.id_seccion AND e.archivar_evaluacion = 1) AS total_porcentaje
        
            FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN duracion_periodo dp ON cv.id_duracion_fk = dp.id_duracion
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :curso 
          AND s.nombre_seccion = :seccion 
          AND s.archivar_seccion = 1
        LIMIT 1
    ");
    $sql->bindParam(":curso", $curso_vigente_id);
    $sql->bindParam(":seccion", $seccion_letra);
    $sql->execute();

    if ($sql->rowCount() === 0) {
        return $sql; // sin resultados
    }

    $datos = $sql->fetch(PDO::FETCH_ASSOC);
    $idDuracion = $datos['id_duracion_fk'];

    // Buscar si esta duración pertenece a una cohorte
    $sqlCohorte = $pdo->prepare("
        SELECT numero_cohorte, anio_cohorte
        FROM cohorte
        WHERE id_duracion_fk = :id_duracion
        LIMIT 1
    ");
    $sqlCohorte->bindParam(":id_duracion", $idDuracion);
    $sqlCohorte->execute();
    $cohorte = $sqlCohorte->fetch(PDO::FETCH_ASSOC);

    if ($cohorte) {
        $datos['numero_cohorte'] = $cohorte['numero_cohorte'];
        $datos['anio_cohorte'] = $cohorte['anio_cohorte'];
        $datos['fecha_inicio_int'] = null;
        $datos['fecha_fin_int'] = null;
    } else {
        // Si no es cohorte, buscar en periodo_intensivo
        $sqlIntensivo = $pdo->prepare("
            SELECT pago_inscripcion_int
            FROM periodo_intensivo
            WHERE id_duracion_fk = :id_duracion
            LIMIT 1
        ");
        $sqlIntensivo->bindParam(":id_duracion", $idDuracion);
        $sqlIntensivo->execute();
        $intensivo = $sqlIntensivo->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = null;
        $datos['anio_cohorte'] = null;
        $datos['fecha_inicio'] = $datos['fecha_inicio'] ?? null;
        $datos['fecha_fin'] = $datos['fecha_fin'] ?? null;
    }

    return $datos;
}












protected function actualizar_profesor_seccion_modelo($idSeccion, $idProfesor) {
    $sql = mainModel::conectar()->prepare("
        UPDATE seccion 
        SET ci_profesor = :idProf 
        WHERE id_seccion = :idSec
    ");
    $sql->bindParam(':idProf', $idProfesor, PDO::PARAM_INT);
    $sql->bindParam(':idSec', $idSeccion, PDO::PARAM_INT);
    return $sql;
}
































/*
//evaluacion new, busca a los estudiantes inscritos en la seccion para ponerles nota
public static function obtener_estudiantes_inscritos_por_seccion_modelo($id_seccion) {
    $consulta = mainModel::conectar()->prepare("
        SELECT i.id_inscripcion, e.ci_estudiante, e.nombre, e.apellido
        FROM inscripcion i
        INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
        WHERE i.id_seccion_fk = :id AND i.archivada = 1
        ORDER BY e.apellido ASC
    ");
    $consulta->bindParam(":id", $id_seccion);
    $consulta->execute();
    return $consulta->fetchAll();
}
*/


//-----------------------------------------------------------------------------------------
//para el conteo de evaluaciones >6 de seccion consult
 
protected static function contar_evaluaciones_modelo($id_seccion) {
            $sql = mainModel::conectar()->prepare("
                SELECT COUNT(*) FROM evaluacion
                WHERE id_seccion = :id_seccion AND archivar_evaluacion=1;
            ");
            $sql->execute([':id_seccion' => $id_seccion]);
            return (int)$sql->fetchColumn();
        }
        
  //------------------------------------------------------------------------------------



















//para los datos de las calificaciones de los estudiantes en la paginacion de calificaciones y pagos osea seccion consult
/*
public function obtener_evaluaciones_sin_limite_modelo($id_seccion){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);

    $sql = mainModel::conectar()->prepare("
       SELECT 
            id_evaluacion,
            tipo_evaluacion,
            porcentaje,
            obj,
            fecha_evaluacion
        FROM evaluacion
        WHERE id_seccion  = :id_seccion
          AND archivar_evaluacion = 1
        ORDER BY fecha_evaluacion ASC
    ");

    $sql->bindParam(":id_seccion", $id_seccion, PDO::PARAM_INT);
    $sql->execute();
    return $sql;
}
*/
        

        //contar estudiantes en el paginador de calificaciones de seccion consult
        //tengo q averigua pa q mierda me sirve este--------------------------------
        protected static function contar_estudiantes_modelo($id_seccion){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);

    $sql = mainModel::conectar()->prepare("
        SELECT COUNT(*) FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
        WHERE 
            i.id_seccion_fk = :id_seccion
            AND i.archivada = 1
            AND est.estado_estudiante = 1
            AND s.archivar_seccion = 1
    ");
    $sql->bindParam(":id_seccion", $id_seccion, PDO::PARAM_INT);
    $sql->execute();

    return (int) $sql->fetchColumn();
}




//estudiantes inscritos en la seccion
/*consulta q no excluye archivados

                SELECT e.id_estudiante, e.ci_estudiante, e.nombre, e.apellido, i.id_inscripcion
                FROM inscripcion i
                INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
                WHERE i.id_seccion_fk = :id
                LIMIT :inicio, :registros
*/

/*
        public function obtener_estudiantes_modelo($id_seccion, $inicio, $registros){
SELECT 
    est.id_estudiante,
    est.ci_estudiante,
    est.nombre,
    est.apellido,
    i.id_inscripcion,
    ev.id_evaluacion,
    ev.tipo_evaluacion,
    ev.fecha_evaluacion,
    pe.puntaje
FROM inscripcion i
INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
INNER JOIN evaluacion ev ON ev.id_seccion_fk = s.id_seccion
INNER JOIN puntaje_estudiante pe 
    ON pe.id_inscripcion = i.id_inscripcion 
    AND pe.id_evaluacion = ev.id_evaluacion
WHERE 
    i.id_seccion_fk = :id_seccion
    AND i.archivada = 1
    AND est.estado_estudiante = 1
    AND s.archivar_seccion = 1
    AND ev.archivar_evaluacion = 1
    AND pe.archivado = 1
ORDER BY est.apellido ASC, est.nombre ASC, ev.fecha_evaluacion ASC
            $sql->bindParam(":id", $id_seccion);
            $sql->bindParam(":inicio", $inicio, PDO::PARAM_INT);
            $sql->bindParam(":registros", $registros, PDO::PARAM_INT);
            $sql->execute();
            return $sql;
        }
        */

//obtener_estudiantes_con_puntajes_modelo
protected static function obtener_estudiantes_con_puntajes_modelo($id_seccion){
    $id_seccion = mainModel::limpiar_cadena($id_seccion);

    try {
        $pdo = mainModel::conectar();

        $sql = $pdo->prepare("
SELECT 
    est.id_estudiante,
    est.ci_estudiante,
    est.nombre,
    est.apellido,
    i.id_inscripcion,
    ev.id_evaluacion,
    ev.tipo_evaluacion,
    ev.fecha_evaluacion,
    ev.porcentaje,
    ev.obj,
    pe.puntaje
FROM inscripcion i
INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
INNER JOIN evaluacion ev ON ev.id_seccion = s.id_seccion
INNER JOIN puntaje_estudiante pe 
    ON pe.id_inscripcion = i.id_inscripcion 
    AND pe.id_evaluacion = ev.id_evaluacion
WHERE 
    i.id_seccion_fk = :id_seccion
    AND i.archivada = 1
    AND est.estado_estudiante = 1
    AND s.archivar_seccion = 1
    AND ev.archivar_evaluacion = 1
    AND pe.archivar_puntaje = 1
ORDER BY est.apellido ASC, est.nombre ASC, ev.fecha_evaluacion ASC
        ");

        $sql->bindParam(":id_seccion", $id_seccion, PDO::PARAM_INT);
        $sql->execute();

        return $sql->fetchAll();
    } catch (Exception $e) {
        error_log("Error al obtener estudiantes con puntajes: " . $e->getMessage());
        return [];
    }
}


//puntajes de los estudiantes(no se donde se usa esto)
/*
        public function obtener_puntajes_modelo($id_inscripcion){
            $sql = mainModel::conectar()->prepare("
                SELECT id_evaluacion, puntaje
                FROM puntaje_estudiante
                WHERE id_inscripcion = :id
            ");
            $sql->bindParam(":id", $id_inscripcion);
            $sql->execute();
            return $sql;
        }


*/

//datos de la seccion para seccion-list
/*
        public function obtener_datos_seccion_modelo($curso_vigente_id, $seccion_letra){
            $sql = mainModel::conectar()->prepare("
        SELECT 
            s.id_seccion, 
            cv.id_curso_vigente,
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
        INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :curso AND s.nombre_seccion = :seccion
        LIMIT 1

            ");
            $sql->bindParam(":curso", $curso_vigente_id);
            $sql->bindParam(":seccion", $seccion_letra);
            $sql->execute();
            return $sql;
        }

*/


public function obtener_datos_seccion_modelo($curso_vigente_id, $seccion_letra){
    $sql = mainModel::conectar()->prepare("
        SELECT 
            s.id_seccion, 
            cv.id_curso_vigente,
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
        WHERE s.id_curso_vigente_fk = :curso AND s.nombre_seccion = :seccion AND archivar_seccion = 1
        LIMIT 1
    ");
    $sql->bindParam(":curso", $curso_vigente_id);
    $sql->bindParam(":seccion", $seccion_letra);
    $sql->execute();
    return $sql;
}

        
        
   

/* no trae id de curso vigente
                SELECT 
                    s.id_seccion, 
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
                INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
                WHERE s.id_curso_vigente_fk = :curso AND s.nombre_seccion = :seccion
                LIMIT 1

*/


        //datos de profesor de seccion list
        public static function obtener_profesores_activos_modelo(){
            $sql = mainModel::conectar()->prepare("
                SELECT id_profesor, nombre_profesor, apellido_profesor 
                FROM profesor 
                WHERE estado_profesor = 1
                ORDER BY nombre_profesor ASC
            ");
            $sql->execute();
            return $sql;
        }





    protected static function agregar_seccion_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            INSERT INTO seccion(nombre_seccion, id_curso_vigente_fk, ci_profesor, dia, archivar_seccion)
            VALUES(:Nombre, :CursoVigente, :Profesor, :Dia, 1)
        ");

        $sql->bindParam(":Nombre", $datos['Nombre']);
        $sql->bindParam(":CursoVigente", $datos['CursoVigente']);
        $sql->bindParam(":Profesor", $datos['Profesor']);
        $sql->bindParam(":Dia", $datos['Dia']);

        $sql->execute();
        return $sql;
    }

    protected static function actualizar_seccion_modelo($datos){
        $sql = mainModel::conectar()->prepare("
            UPDATE seccion SET id_profesor_fk = :Profesor WHERE id_seccion = :ID
        ");
        $sql->bindParam(":Profesor", $datos['Profesor']);
        $sql->bindParam(":ID", $datos['ID']);
        $sql->execute();
        return $sql;
    }













//                $evaluaciones = seccionModelo::obtener_evaluaciones_sin_limite_modelo($id_seccion)->fetchAll();
             //   $estudiantes = seccionModelo::obtener_estudiantes_modelo($id_seccion, $inicio, $registros)->fetchAll();


    }
?>