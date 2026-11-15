<?php

    require_once "mainModel.php";

class seccionModelo extends mainModel{


        

/*
* para period necesito las funciones de validacion, las de traer profes activos, cursos activos, creo q una de cursos q no esten en el period a actualizar, funcion de crear, actualizar, archivar, restaurar, y listar
* para period course una que traiga cursos disponibles, otra q  traiga periodos disponibles, las de validacion, la de listar, y la de modales
* para section necesito una q traiga sus datos, los estudiantes, evaluaciones y puntajes, mas la de listar, transferir y eliminar 
* para evaluaciones necesito una q traiga sus datos, los estudiantes, y puntajes, mas la de listar, actualizar, arhcivar y restaurar
*/
        
        
//este modelo depende de una rpc llamada datos_seccion, esta adaptada para traer los datos que necesita seccion consult, seccion archived consult, evaluacion  new, evaluacion update, seccion new (que tambien es period course new) y reportes
//trae datos de la seccion como su curso, periodo, profesor, estudiantes, evaluaciones y sus puntajes, siempre se apunta a esa rpc para traer datos, y esta a su ves apunta como a 4 funciones auxiliares en supabase
        
        
// trae period, course, su period course y secciones para seccion new
    public static function datos_nueva_seccion_supabase(
        ?int $periodId,
        ?int $periodCourseId,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('datos_nueva_seccion', [
            'p_period_id'        => $periodId,
            'p_period_course_id' => $periodCourseId,
        ], $accessToken);
    }
    
    // crear period_course (opcional) + sección en una sola transacción
    public static function crear_period_course_y_seccion_supabase(
        ?int $periodCourseId,
        ?int $periodId,
        ?int $courseId,
        string $sectionName,
        string $sectionDay,
        int $teacherId,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('crear_period_course_y_seccion', [
            'p_period_course_id' => $periodCourseId,
            'p_period_id'        => $periodId,
            'p_course_id'        => $courseId,
            'p_section_name'     => $sectionName,
            'p_section_day'      => $sectionDay,
            'p_teacher_id'       => $teacherId,
        ], $accessToken);
    }    
    
    //esto es de seccion consult, trae las notas totales de la seccion para pdf o excel    
    public static function datos_pdf_seccion_supabase(int $sectionId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('datos_pdf_seccion', [
            'p_section_id' => $sectionId,
        ], $accessToken);
    }
        
//para reportes: state=3 (notas con scores) o state=2 (nómina con estudiantes)
    public static function datos_seccion_reporte_supabase(int $sectionId, int $state, string $accessToken): array {
        return SupabaseApi::rpcWithToken('datos_seccion', [
            'p_section_id' => $sectionId,
            'p_state'      => $state,
        ], $accessToken);
    }

    
//reportes de pagos
    public static function reportes_pagos_seccion_supabase(int $sectionId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('reportes_pagos_seccion', [
            'p_section_id' => $sectionId,
        ], $accessToken);
    }
 
//reporte de pago individual de estudiante
    public static function reporte_pagos_estudiante_supabase(int $registrationId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('reporte_pagos_estudiante', [
            'p_registration_id' => $registrationId,
        ], $accessToken);
    }
    
//reportes notas individual por estudiante   
    public static function reporte_notas_estudiante_supabase(int $registrationId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('aux_notas_por_inscripcion', [
            'p_registration_id' => $registrationId,
        ], $accessToken);
    }
    
    
    
    
    
    
    
    
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
    
    
// Obtener datos para el modal de transferencia de estudiantes entre secciones, si es true, trae secciones y un conteo de sus estudiantes, es para el modal de transferencia
    public static function obtener_datos_transferencia_supabase(int $sectionId, string $accessToken): array{
        //obtener_datos_transferencia_estudiantes
        return SupabaseApi::rpcWithToken('aux_traer_estudiantes_de_seccion', ['p_section_id' => $sectionId,'p_incluir_hermanas' => true], $accessToken);
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
        
        

//para seccion consult, caso A: trae los datos de la seccion, su profe,
//y las evaluaciones de state 1 para el calculo de porcentaje y limite de evaluaciones
    public static function obtener_seccion_por_id(int $sectionId, string $accessToken, int $state): array {
        return SupabaseApi::rpcWithToken('datos_seccion', [
            'p_section_id' => $sectionId,
            'p_state'      => $state,
        ], $accessToken);
    }
    
    
//para evaluación new (caso C: solo section_id + state=2)
    public static function datos_evaluacion_new_supabase(int $sectionId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('datos_seccion', [
            'p_section_id' => $sectionId,
            'p_state'      => 2,
        ], $accessToken);
    }
    
//para evaluacion update, caso D: trae la evaluación específica,
//su sección de contexto, y los estudiantes con su puntaje para esa evaluación
    public static function datos_evaluacion_update_supabase(int $evaluationId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('datos_seccion', ['p_evaluation_id' => $evaluationId,], $accessToken);
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
        return SupabaseApi::rpcWithToken('crear_evaluacion_con_puntajes', $params, $accessToken);
    } 

        
        

    // Listar evaluaciones de una sección con paginación y filtro por state
    public static function listar_evaluaciones_por_seccion_supabase(
        int $sectionId,
        int $limit,
        int $offset,
        string $accessToken,
        int $state = 1 // 1: activas, 0: archivadas
    ): array {
        return SupabaseApi::getWithToken('evaluation', [
            'select' => 'id, evaluation_type, objective, percentage, evaluation_date, state,section_id',
            'section_id' => 'eq.' . $sectionId,
            'state' => 'eq.' . $state,
            'order' => 'id.desc',
            'limit' => $limit,
            'offset' => $offset,
        ], $accessToken);
    }

    
    
    
    
public static function listar_estudiantes_seccion_consulta_supabase(
    int $sectionId,
    int $limit,
    int $offset,
    int $state,
    int $role,
    string $accessToken
): array {
    return SupabaseApi::rpcWithToken('listar_estudiantes_seccion_consulta', [
        'p_section_id' => $sectionId,
        'p_limit'      => $limit,
        'p_offset'     => $offset,
        'p_state'      => $state,
        'p_role'       => $role,
    ], $accessToken);
}
    
    
        
    /**
     * Actualiza una evaluación y sus puntajes (con UPSERT).
     */
    public static function actualizar_evaluacion_con_puntajes_supabase(array $datos, string $accessToken): array{
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
    public static function archivar_evaluacion_con_puntajes_supabase(int $evaluationId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('archivar_evaluacion_con_puntajes', ['p_evaluation_id' => $evaluationId], $accessToken);
    }
//restaurar evaluaciones
    public static function restaurar_evaluacion_con_puntajes_supabase(int $evaluationId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('restaurar_evaluacion_con_puntajes', ['p_evaluation_id' => $evaluationId], $accessToken);
    }
        
        
        

}
?>