<?php
class NotificacionModelo {
    private $db;
    
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function generarNotificacionesEstudiante($id_usuario, $id_estudiante) {
        error_log("=== GENERANDO NOTIFICACIONES PARA ESTUDIANTE ===");
        
        $notificaciones = [];

        try {
            // 1. Notificaciones de pagos pendientes
            $sqlPagos = "SELECT i.id_inscripcion, c.nombre_curso, i.pago_faltante,
                                DATE_FORMAT(i.fecha_inscripcion, '%d/%m/%Y') as fecha_insc
                         FROM inscripcion i
                         JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                         JOIN curso c ON c.id_curso = cv.id_curso_fk
                         WHERE i.id_estudiante_fk = ? 
                           AND i.pago_faltante > 0
                           AND i.estado_inscripcion = 'Activa'
                           AND i.archivada = 1"; // 1 = NO archivada (activa)
            
            $stmtPagos = $this->db->prepare($sqlPagos);
            $stmtPagos->execute([$id_estudiante]);
            $pagosPendientes = $stmtPagos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($pagosPendientes as $pago) {
                $notificaciones[] = [
                    'motivo' => 'Pago pendiente',
                    'referencia' => "Curso: {$pago['nombre_curso']} - Monto pendiente: \${$pago['pago_faltante']}",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'pago',
                    'id_inscripcion' => $pago['id_inscripcion']
                ];
            }

            // 2. Notificaciones de cursos próximos a iniciar
            $sqlCursos = "SELECT c.nombre_curso, ch.fecha_inicio
                          FROM inscripcion i
                          JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                          JOIN curso c ON c.id_curso = cv.id_curso_fk
                          JOIN cohorte ch ON ch.Id_cohorte = cv.id_cohorte_fk
                          WHERE i.id_estudiante_fk = ? 
                            AND i.estado_inscripcion = 'Activa'
                            AND i.archivada = 1
                            AND ch.estado_cohorte = 'Activo'
                            AND ch.archivar_cohorte = 1
                            AND ch.fecha_inicio BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
            
            $stmtCursos = $this->db->prepare($sqlCursos);
            $stmtCursos->execute([$id_estudiante]);
            $cursosProximos = $stmtCursos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cursosProximos as $curso) {
                $notificaciones[] = [
                    'motivo' => 'Curso próximo a iniciar',
                    'referencia' => "{$curso['nombre_curso']} inicia el " . date('d/m/Y', strtotime($curso['fecha_inicio'])),
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'curso'
                ];
            }
            
            // 1. EVALUACIONES PRÓXIMAS DEL ESTUDIANTE
			$sqlMisEvaluaciones = "SELECT e.tipo_evaluacion, e.fecha_evaluacion, e.porcentaje,
                              c.nombre_curso, s.nombre_seccion,
                              DATEDIFF(e.fecha_evaluacion, CURDATE()) as dias_restantes
                       FROM evaluacion e
                       JOIN seccion s ON s.id_seccion = e.id_seccion
                       JOIN inscripcion i ON i.id_seccion_fk = s.id_seccion
                       JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                       JOIN curso c ON c.id_curso = cv.id_curso_fk
                       WHERE i.id_estudiante_fk = :id_estudiante
                         AND i.estado_inscripcion = 'Activa'
                         AND e.fecha_evaluacion BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                       ORDER BY e.fecha_evaluacion ASC
                       LIMIT 10";

					// 2. CALIFICACIONES RECIENTES
					$sqlMisCalificaciones = "SELECT e.tipo_evaluacion, pe.puntaje, c.nombre_curso,
                                pe.fecha_registro
                         FROM puntaje_estudiante pe
                         JOIN evaluacion e ON e.id_evaluacion = pe.id_evaluacion
                         JOIN inscripcion i ON i.id_inscripcion = pe.id_inscripcion
                         JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                         JOIN curso c ON c.id_curso = cv.id_curso_fk
                         WHERE i.id_estudiante_fk = :id_estudiante
                           AND pe.puntaje IS NOT NULL
                           AND pe.fecha_registro >= DATE_SUB(CURDATE(), INTERVAL 7 DAY)
                         ORDER BY pe.fecha_registro DESC
                         LIMIT 8";

			// 3. PAGOS PERSONALES PENDIENTES
			$sqlMisPagosPendientes = "SELECT i.pago_faltante, c.nombre_curso,
                                 (SELECT COALESCE(SUM(t.monto), 0) 
                                  FROM transaccion t 
                                  WHERE t.id_inscripcion = i.id_inscripcion 
                                  AND t.estatus_pago = 1) as total_pagado
                          FROM inscripcion i
                          JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                          JOIN curso c ON c.id_curso = cv.id_curso_fk
                          WHERE i.id_estudiante_fk = :id_estudiante
                            AND i.estado_inscripcion = 'Activa'
                            AND i.pago_faltante > 0";

		// 4. PROMEDIO ACTUAL POR CURSO
		$sqlMiPromedio = "SELECT c.nombre_curso, 
                         AVG(CAST(pe.puntaje AS DECIMAL)) as promedio_actual,
                         COUNT(pe.id_evaluacion) as evaluaciones_calificadas
                  FROM puntaje_estudiante pe
                  JOIN evaluacion e ON e.id_evaluacion = pe.id_evaluacion
                  JOIN inscripcion i ON i.id_inscripcion = pe.id_inscripcion
                  JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                  JOIN curso c ON c.id_curso = cv.id_curso_fk
                  WHERE i.id_estudiante_fk = :id_estudiante
                    AND pe.puntaje IS NOT NULL
                    AND pe.puntaje != ''
                  GROUP BY i.id_inscripcion
                  HAVING evaluaciones_calificadas >= 1";

        } catch (Exception $e) {
            error_log("Error en generarNotificacionesEstudiante: " . $e->getMessage());
        }

        return $notificaciones;
    }
}
?>