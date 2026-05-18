<?php
class NotificacionControlEstudioModelo {
    private $db;
    
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function generarNotificacionesControlEstudio($id_usuario) {
        error_log("=== GENERANDO NOTIFICACIONES PARA CONTROL DE ESTUDIO ===");
        
        $notificaciones = [];

        try {
            // 1. EVALUACIONES CON PUNTAJES PENDIENTES (fecha ya pasó pero no hay puntajes)
            $sqlNotasPendientes = "SELECT ev.id_evaluacion, ev.tipo_evaluacion, 
                                          s.nombre_seccion, ev.fecha_evaluacion,
                                          COUNT(pe.id_puntaje) as puntajes_registrados,
                                          (SELECT COUNT(*) 
                                           FROM inscripcion i 
                                           WHERE i.id_seccion_fk = s.id_seccion 
                                           AND i.estado_inscripcion = 'Activa'
                                           AND i.archivada = 1) as total_estudiantes
                                   FROM evaluacion ev
                                   JOIN seccion s ON s.id_seccion = ev.id_seccion
                                   LEFT JOIN puntaje_estudiante pe ON pe.id_evaluacion = ev.id_evaluacion
                                   WHERE ev.fecha_evaluacion < CURDATE()
                                     AND ev.archivar_evaluacion = 1
                                   GROUP BY ev.id_evaluacion, ev.tipo_evaluacion, s.nombre_seccion, ev.fecha_evaluacion
                                   HAVING puntajes_registrados < total_estudiantes
                                   ORDER BY ev.fecha_evaluacion DESC";
            
            $stmtNotasPendientes = $this->db->prepare($sqlNotasPendientes);
            $stmtNotasPendientes->execute();
            $evaluaciones_pendientes = $stmtNotasPendientes->fetchAll(PDO::FETCH_ASSOC);

            foreach ($evaluaciones_pendientes as $eval) {
                $puntajes_faltantes = $eval['total_estudiantes'] - $eval['puntajes_registrados'];
                $notificaciones[] = [
                    'motivo' => 'Puntajes pendientes de ingresar',
                    'referencia' => "Evaluación: {$eval['tipo_evaluacion']} - Sección: {$eval['nombre_seccion']} - Faltan {$puntajes_faltantes} puntajes",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'puntajes_pendientes',
                    'id_evaluacion' => $eval['id_evaluacion'],
                    'puntajes_faltantes' => $puntajes_faltantes,
                    'fecha_evaluacion' => $eval['fecha_evaluacion']
                ];
            }

            // 2. SECCIONES SIN EVALUACIONES ASIGNADAS
            $sqlSeccionesSinEvaluacion = "SELECT s.id_seccion, s.nombre_seccion, 
                                                 cv.id_curso_vigente, c.nombre_curso
                                          FROM seccion s
                                          JOIN curso_vigente cv ON cv.id_curso_vigente = s.id_curso_vigente_fk
                                          JOIN curso c ON c.id_curso = cv.id_curso_fk
                                          WHERE s.archivar_seccion = 1
                                            AND NOT EXISTS (SELECT 1 FROM evaluacion e 
                                                           WHERE e.id_seccion = s.id_seccion
                                                           AND e.archivar_evaluacion = 1)
                                          ORDER BY c.nombre_curso, s.nombre_seccion";
            
            $stmtSeccionesSinEvaluacion = $this->db->prepare($sqlSeccionesSinEvaluacion);
            $stmtSeccionesSinEvaluacion->execute();
            $secciones_sin_evaluacion = $stmtSeccionesSinEvaluacion->fetchAll(PDO::FETCH_ASSOC);

            foreach ($secciones_sin_evaluacion as $seccion) {
                $notificaciones[] = [
                    'motivo' => 'Sección sin evaluaciones',
                    'referencia' => "Curso: {$seccion['nombre_curso']} - Sección: {$seccion['nombre_seccion']}",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'seccion_sin_evaluacion',
                    'id_seccion' => $seccion['id_seccion'],
                    'id_curso_vigente' => $seccion['id_curso_vigente']
                ];
            }

            // 3. ESTUDIANTES CON BAJO RENDIMIENTO (nota_final < 10)
            $sqlBajoRendimiento = "SELECT e.id_estudiante, e.nombre, e.apellido,
                                          c.nombre_curso, i.nota_final,
                                          s.nombre_seccion
                                   FROM estudiante e
                                   JOIN inscripcion i ON i.id_estudiante_fk = e.id_estudiante
                                   JOIN seccion s ON s.id_seccion = i.id_seccion_fk
                                   JOIN curso_vigente cv ON cv.id_curso_vigente = s.id_curso_vigente_fk
                                   JOIN curso c ON c.id_curso = cv.id_curso_fk
                                   WHERE i.estado_inscripcion = 'Activa'
                                     AND i.archivada = 1
                                     AND e.estado_estudiante = 1
                                     AND i.nota_final IS NOT NULL
                                     AND i.nota_final < 10
                                   ORDER BY i.nota_final ASC
                                   LIMIT 8";
            
            $stmtBajoRendimiento = $this->db->prepare($sqlBajoRendimiento);
            $stmtBajoRendimiento->execute();
            $estudiantes_bajo_rendimiento = $stmtBajoRendimiento->fetchAll(PDO::FETCH_ASSOC);

            foreach ($estudiantes_bajo_rendimiento as $estudiante) {
                $notificaciones[] = [
                    'motivo' => 'Estudiante con bajo rendimiento',
                    'referencia' => "{$estudiante['nombre']} {$estudiante['apellido']} - {$estudiante['nombre_curso']} - Nota: {$estudiante['nota_final']}",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'bajo_rendimiento',
                    'id_estudiante' => $estudiante['id_estudiante'],
                    'nota_final' => $estudiante['nota_final']
                ];
            }
            
            // 1. ESTUDIANTES CON BAJO RENDIMIENTO
$sqlBajoRendimiento = "SELECT e.nombre, e.apellido, c.nombre_curso,
                              AVG(CAST(pe.puntaje AS DECIMAL)) as promedio,
                              COUNT(pe.id_evaluacion) as evaluaciones_realizadas
                       FROM puntaje_estudiante pe
                       JOIN evaluacion ev ON ev.id_evaluacion = pe.id_evaluacion
                       JOIN inscripcion i ON i.id_inscripcion = pe.id_inscripcion
                       JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                       JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                       JOIN curso c ON c.id_curso = cv.id_curso_fk
                       WHERE pe.archivar_puntaje = 1
                         AND pe.puntaje IS NOT NULL
                         AND pe.puntaje != ''
                       GROUP BY e.id_estudiante, i.id_inscripcion
                       HAVING promedio < 12 AND evaluaciones_realizadas >= 2
                       LIMIT 10";

// 2. EVALUACIONES PRÓXIMAS (próximos 7 días)
$sqlEvaluacionesProximas = "SELECT e.tipo_evaluacion, e.fecha_evaluacion, c.nombre_curso,
                                   s.nombre_seccion, DATEDIFF(e.fecha_evaluacion, CURDATE()) as dias_restantes
                            FROM evaluacion e
                            JOIN seccion s ON s.id_seccion = e.id_seccion
                            JOIN curso_vigente cv ON cv.id_curso_vigente = s.id_curso_vigente_fk
                            JOIN curso c ON c.id_curso = cv.id_curso_fk
                            WHERE e.archivar_evaluacion = 1
                              AND e.fecha_evaluacion BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                            ORDER BY e.fecha_evaluacion ASC
                            LIMIT 10";

// 3. ESTUDIANTES EN RIESGO DE ABANDONO
$sqlRiesgoAbandono = "SELECT e.nombre, e.apellido, c.nombre_curso, 
                             i.fecha_inscripcion, i.if_fecha_abandono,
                             (SELECT COUNT(*) FROM puntaje_estudiante pe 
                              WHERE pe.id_inscripcion = i.id_inscripcion 
                              AND pe.puntaje IS NULL) as evaluaciones_pendientes
                      FROM inscripcion i
                      JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                      JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                      JOIN curso c ON c.id_curso = cv.id_curso_fk
                      WHERE i.estado_inscripcion = 'Activa'
                        AND i.if_fecha_abandono IS NULL
                        AND DATEDIFF(CURDATE(), i.fecha_inscripcion) > 30
                      HAVING evaluaciones_pendientes >= 2
                      LIMIT 10";

// 4. SECCIONES SIN EVALUACIONES REGISTRADAS
$sqlSeccionesSinEvaluaciones = "SELECT s.nombre_seccion, c.nombre_curso,
                                       COUNT(e.id_evaluacion) as total_evaluaciones
                                FROM seccion s
                                JOIN curso_vigente cv ON cv.id_curso_vigente = s.id_curso_vigente_fk
                                JOIN curso c ON c.id_curso = cv.id_curso_fk
                                LEFT JOIN evaluacion e ON e.id_seccion = s.id_seccion
                                WHERE s.archivar_seccion = 1
                                GROUP BY s.id_seccion
                                HAVING total_evaluaciones = 0
                                LIMIT 8";

        } catch (Exception $e) {
            error_log("Error en generarNotificacionesControlEstudio: " . $e->getMessage());
        }

        return $notificaciones;
    }
}
?>