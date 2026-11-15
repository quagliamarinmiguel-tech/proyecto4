<?php
class NotificacionAdminModelo {
    private $db;
    
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function generarNotificacionesAdmin($id_usuario) {
        error_log("=== GENERANDO NOTIFICACIONES PARA ADMIN ===");
        
        $notificaciones = [];

        try {
            // 1. COHORTES QUE TERMINAN EN PRÓXIMOS 3 DÍAS (URGENTE)
            $sqlCohortesUrgentes = "SELECT Id_cohorte, tipo_cohorte, numero_cohorte, anio_cohorte,
                                           fecha_fin, DATEDIFF(fecha_fin, CURDATE()) as dias_restantes
                                    FROM cohorte 
                                    WHERE estado_cohorte = 'Activo'
                                      AND archivar_cohorte = 1
                                      AND fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 3 DAY)
                                    ORDER BY fecha_fin ASC";
            
            $stmtCohortesUrgentes = $this->db->prepare($sqlCohortesUrgentes);
            $stmtCohortesUrgentes->execute();
            $cohortes_urgentes = $stmtCohortesUrgentes->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cohortes_urgentes as $cohorte) {
                $notificaciones[] = [
                    'motivo' => 'Cohorte por Finalizar (URGENTE)',
                    'referencia' => "{$cohorte['tipo_cohorte']} {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']} - Finaliza en {$cohorte['dias_restantes']} días",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'cohorte_urgente',
                    'id_cohorte' => $cohorte['Id_cohorte'],
                    'dias_restantes' => $cohorte['dias_restantes']
                ];
            }

            // 2. DEUDA PENDIENTE SIGNIFICATIVA
            $sqlDeuda = "SELECT COALESCE(SUM(pago_faltante), 0) as total_deuda_pendiente
                         FROM inscripcion 
                         WHERE estado_inscripcion = 'Activa'
                         AND archivada = 1";
            $stmtDeuda = $this->db->prepare($sqlDeuda);
            $stmtDeuda->execute();
            $deuda = $stmtDeuda->fetch(PDO::FETCH_ASSOC);

            if ($deuda['total_deuda_pendiente'] > 1000) {
                $notificaciones[] = [
                    'motivo' => 'Deuda Pendiente Significativa',
                    'referencia' => "Deuda total del sistema: $" . number_format($deuda['total_deuda_pendiente'], 2),
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'deuda_significativa',
                    'monto_deuda' => $deuda['total_deuda_pendiente']
                ];
            }

            // 3. EVALUACIONES CON PUNTAJES PENDIENTES
            $sqlEvalPendientes = "SELECT ev.id_evaluacion, ev.tipo_evaluacion, s.nombre_seccion,
                                         COUNT(pe.id_puntaje) as puntajes_registrados,
                                         (SELECT COUNT(*) FROM inscripcion i 
                                          WHERE i.id_seccion_fk = s.id_seccion 
                                          AND i.estado_inscripcion = 'Activa'
                                          AND i.archivada = 1) as total_estudiantes
                                  FROM evaluacion ev
                                  JOIN seccion s ON s.id_seccion = ev.id_seccion
                                  LEFT JOIN puntaje_estudiante pe ON pe.id_evaluacion = ev.id_evaluacion
                                  WHERE ev.fecha_evaluacion < CURDATE()
                                    AND ev.archivar_evaluacion = 1
                                  GROUP BY ev.id_evaluacion, ev.tipo_evaluacion, s.nombre_seccion
                                  HAVING puntajes_registrados < total_estudiantes
                                  ORDER BY (total_estudiantes - puntajes_registrados) DESC
                                  LIMIT 5";
            
            $stmtEvalPendientes = $this->db->prepare($sqlEvalPendientes);
            $stmtEvalPendientes->execute();
            $eval_pendientes = $stmtEvalPendientes->fetchAll(PDO::FETCH_ASSOC);

            foreach ($eval_pendientes as $eval) {
                $puntajes_faltantes = $eval['total_estudiantes'] - $eval['puntajes_registrados'];
                $notificaciones[] = [
                    'motivo' => 'Evaluación con Puntajes Pendientes',
                    'referencia' => "{$eval['tipo_evaluacion']} - {$eval['nombre_seccion']} - Faltan {$puntajes_faltantes} puntajes",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'evaluacion_pendiente',
                    'id_evaluacion' => $eval['id_evaluacion'],
                    'puntajes_faltantes' => $puntajes_faltantes
                ];
            }

            // 4. CURSOS CON BAJA INSCRIPCIÓN (< 30%)
            $sqlCursosBaja = "SELECT cv.id_curso_vigente, c.nombre_curso, 
                                     cv.oferta as cupos_totales,
                                     COUNT(i.id_inscripcion) as inscritos_actuales,
                                     ROUND((COUNT(i.id_inscripcion) / cv.oferta) * 100, 2) as porcentaje_ocupacion
                              FROM curso_vigente cv
                              JOIN curso c ON c.id_curso = cv.id_curso_fk
                              LEFT JOIN inscripcion i ON i.id_curso_vigente_fk = cv.id_curso_vigente 
                                    AND i.estado_inscripcion = 'Activa'
                                    AND i.archivada = 1
                              WHERE cv.estado_curso_vigente = 1
                              GROUP BY cv.id_curso_vigente, c.nombre_curso, cv.oferta
                              HAVING porcentaje_ocupacion < 30
                              ORDER BY porcentaje_ocupacion ASC
                              LIMIT 5";
            
            $stmtCursosBaja = $this->db->prepare($sqlCursosBaja);
            $stmtCursosBaja->execute();
            $cursos_baja = $stmtCursosBaja->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cursos_baja as $curso) {
                $notificaciones[] = [
                    'motivo' => 'Curso con Muy Baja Inscripción',
                    'referencia' => "{$curso['nombre_curso']} - {$curso['porcentaje_ocupacion']}% ocupado ({$curso['inscritos_actuales']}/{$curso['cupos_totales']})",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'curso_baja_inscripcion',
                    'id_curso_vigente' => $curso['id_curso_vigente'],
                    'porcentaje_ocupacion' => $curso['porcentaje_ocupacion']
                ];
            }
            
           $sqlUsuariosActivos = "SELECT u.Nombre, u.cargo, u.fecha_creacion, 
                              MAX(s.fecha_login) as ultimo_login
                       FROM Usuario u 
                       LEFT JOIN sesiones s ON s.id_usuario = u.ID_usuario
                       WHERE u.estado_usuario = 1
                       GROUP BY u.ID_usuario
                       HAVING ultimo_login >= DATE_SUB(NOW(), INTERVAL 1 DAY)
                       LIMIT 8";

						// 2. USUARIOS INACTIVOS (más de 30 días sin login)
			$sqlUsuariosInactivos = "SELECT u.Nombre, u.cargo, MAX(s.fecha_login) as ultimo_login,
                                DATEDIFF(CURDATE(), MAX(s.fecha_login)) as dias_inactivo
                         FROM Usuario u 
                         LEFT JOIN sesiones s ON s.id_usuario = u.ID_usuario
                         WHERE u.estado_usuario = 1
                         GROUP BY u.ID_usuario
                         HAVING dias_inactivo > 30
                         LIMIT 10";
           
            		// 4. CURSOS SIN INSCRIPCIONES
					$sqlCursosSinInscripciones = "SELECT c.nombre_curso, cv.id_curso_vigente
                              FROM curso_vigente cv
                              JOIN curso c ON c.id_curso = cv.id_curso_fk
                              WHERE cv.estado_curso_vigente = 1
                                AND NOT EXISTS (SELECT 1 FROM inscripcion i 
                                                WHERE i.id_curso_vigente_fk = cv.id_curso_vigente
                                                AND i.estado_inscripcion = 'Activa')";

					// 5. COHORTES PRÓXIMAS A FINALIZAR
					$sqlCohortesFinalizando = "SELECT tipo_cohorte, numero_cohorte, anio_cohorte, fecha_fin,
                                  DATEDIFF(fecha_fin, CURDATE()) as dias_restantes
                           FROM cohorte 
                           WHERE archivar_cohorte = 1
                             AND fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY)";

        } catch (Exception $e) {
            error_log("Error en generarNotificacionesAdmin: " . $e->getMessage());
        }

        return $notificaciones;
    }
}
?>