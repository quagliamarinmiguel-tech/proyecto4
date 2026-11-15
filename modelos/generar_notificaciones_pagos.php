<?php
class NotificacionPagosModelo {
    private $db;
    
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function generarNotificacionesPagos($id_usuario) {
        error_log("=== GENERANDO NOTIFICACIONES PARA ADMINISTRACIÓN DE PAGOS ===");
        
        $notificaciones = [];

        try {
            // 1. ALUMNOS CON PAGOS PENDIENTES (deuda mayor a $0)
            $sqlPendientes = "SELECT e.id_estudiante, e.nombre, e.apellido, 
                                     c.nombre_curso, i.pago_faltante,
                                     (SELECT COALESCE(SUM(t.monto), 0) 
                                      FROM transaccion t 
                                      WHERE t.id_inscripcion = i.id_inscripcion 
                                      AND t.estatus_pago = 1) as total_pagado
                              FROM inscripcion i
                              JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                              JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                              JOIN curso c ON c.id_curso = cv.id_curso_fk
                              WHERE i.estado_inscripcion = 'Activa'
                                AND i.archivada = 1
                                AND i.pago_faltante > 0
                              ORDER BY i.pago_faltante DESC
                              LIMIT 15";
            
            $stmtPendientes = $this->db->prepare($sqlPendientes);
            $stmtPendientes->execute();
            $alumnos_pendientes = $stmtPendientes->fetchAll(PDO::FETCH_ASSOC);

            foreach ($alumnos_pendientes as $alumno) {
                $notificaciones[] = [
                    'motivo' => 'Pago pendiente',
                    'referencia' => "{$alumno['nombre']} {$alumno['apellido']} - Curso: {$alumno['nombre_curso']} - Debe: \${$alumno['pago_faltante']}",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'pago_pendiente',
                    'id_estudiante' => $alumno['id_estudiante'],
                    'monto_deuda' => $alumno['pago_faltante'],
                    'total_pagado' => $alumno['total_pagado']
                ];
            }

            // 2. ALUMNOS SIN PAGOS REGISTRADOS (total_pagado = 0)
            $sqlSinPago = "SELECT e.id_estudiante, e.nombre, e.apellido, 
                                  c.nombre_curso, i.pago_faltante
                           FROM inscripcion i
                           JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                           JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                           JOIN curso c ON c.id_curso = cv.id_curso_fk
                           WHERE i.estado_inscripcion = 'Activa'
                             AND i.archivada = 1
                             AND i.pago_faltante > 0
                             AND NOT EXISTS (SELECT 1 FROM transaccion t 
                                             WHERE t.id_inscripcion = i.id_inscripcion 
                                             AND t.estatus_pago = 1)
                           ORDER BY i.fecha_inscripcion DESC
                           LIMIT 10";
            
            $stmtSinPago = $this->db->prepare($sqlSinPago);
            $stmtSinPago->execute();
            $alumnos_sin_pago = $stmtSinPago->fetchAll(PDO::FETCH_ASSOC);

            foreach ($alumnos_sin_pago as $alumno) {
                $notificaciones[] = [
                    'motivo' => 'Sin pago registrado',
                    'referencia' => "{$alumno['nombre']} {$alumno['apellido']} - Curso: {$alumno['nombre_curso']} - Deuda total: \${$alumno['pago_faltante']}",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'sin_pago',
                    'id_estudiante' => $alumno['id_estudiante'],
                    'monto_deuda' => $alumno['pago_faltante']
                ];
            }

            // 3. RESUMEN DE RECAUDACIÓN DEL MES
            $sqlResumen = "SELECT 
                           COUNT(DISTINCT i.id_estudiante_fk) as total_alumnos_deudores,
                           SUM(i.pago_faltante) as total_deuda_pendiente,
                           (SELECT COALESCE(SUM(t.monto), 0) 
                            FROM transaccion t 
                            WHERE t.estatus_pago = 1
                            AND MONTH(t.fecha_pago) = MONTH(CURDATE())
                            AND YEAR(t.fecha_pago) = YEAR(CURDATE())) as recaudado_mes_actual
                           FROM inscripcion i
                           WHERE i.estado_inscripcion = 'Activa'
                           AND i.archivada = 1
                           AND i.pago_faltante > 0";
            
            $stmtResumen = $this->db->prepare($sqlResumen);
            $stmtResumen->execute();
            $resumen = $stmtResumen->fetch(PDO::FETCH_ASSOC);
            // En generar_notificaciones_pagos.php - agregar después del resumen mensual

			// 4. PAGOS PRÓXIMOS A VENCER (próximos 7 días)
			$sqlProximosVencer = "SELECT e.id_estudiante, e.nombre, e.apellido, 
            	c.nombre_curso, i.pago_faltante,
                DATE_ADD(i.fecha_inscripcion, INTERVAL 30 DAY) as fecha_vencimiento,
                DATEDIFF(DATE_ADD(i.fecha_inscripcion, INTERVAL 30 DAY), CURDATE()) as dias_restantes
                       FROM inscripcion i
                       JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                       JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                       JOIN curso c ON c.id_curso = cv.id_curso_fk
                       WHERE i.estado_inscripcion = 'Activa'
                         AND i.archivada = 1
                         AND i.pago_faltante > 0
                         AND DATEDIFF(DATE_ADD(i.fecha_inscripcion, INTERVAL 30 DAY), CURDATE()) BETWEEN 1 AND 7
                       ORDER BY dias_restantes ASC
                       LIMIT 10";

				$stmtProximosVencer = $this->db->prepare($sqlProximosVencer);
				$stmtProximosVencer->execute();
				$proximos_vencer = $stmtProximosVencer->fetchAll(PDO::FETCH_ASSOC);

				foreach ($proximos_vencer as $pago) {
    				$notificaciones[] = [
        				'motivo' => 'Pago próximo a vencer',
        				'referencia' => "{$pago['nombre']} {$pago['apellido']} - Vence en {$pago['dias_restantes']} días - Curso: {$pago['nombre_curso']}",
        				'fecha_creacion' => date('Y-m-d H:i:s'),
        				'leida' => false,
        				'tipo' => 'pago_proximo_vencer',
        				'id_estudiante' => $pago['id_estudiante'],
        				'dias_restantes' => $pago['dias_restantes'],
        				'monto_deuda' => $pago['pago_faltante']
    				];
				}
            
            // 5. PAGOS MÓVILES PENDIENTES DE VERIFICACIÓN
			$sqlPagosMoviles = "SELECT t.id_transaccion, t.monto, t.fecha_pago, t.metodo_pago,
            	e.nombre, e.apellido, c.nombre_curso,
                DATEDIFF(CURDATE(), t.fecha_pago) as dias_pendiente
                    FROM transaccion t
                    JOIN inscripcion i ON i.id_inscripcion = t.id_inscripcion
                    JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                    JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                    JOIN curso c ON c.id_curso = cv.id_curso_fk
                    WHERE t.estatus_pago = 0
                      AND t.metodo_pago = 'pago_movil'
                      AND t.if_pagomovil_imagen IS NOT NULL
                    ORDER BY t.fecha_pago ASC
                    LIMIT 10";

			$stmtPagosMoviles = $this->db->prepare($sqlPagosMoviles);
			$stmtPagosMoviles->execute();
			$pagos_moviles = $stmtPagosMoviles->fetchAll(PDO::FETCH_ASSOC);

			foreach ($pagos_moviles as $pago) {
    			$notificaciones[] = [
        			'motivo' => 'Pago móvil pendiente de verificación',
        			'referencia' => "{$pago['nombre']} {$pago['apellido']} - Monto: \${$pago['monto']} - Pendiente desde hace {$pago['dias_pendiente']} días",
       				'fecha_creacion' => date('Y-m-d H:i:s'),
        			'leida' => false,
        			'tipo' => 'pago_movil_pendiente',
        			'id_transaccion' => $pago['id_transaccion'],
        			'monto' => $pago['monto'],
        			'dias_pendiente' => $pago['dias_pendiente']
    			];
			}
            
            // 6. COHORTES CON PAGOS PENDIENTES
			$sqlCohortesPendientes = "SELECT ch.tipo_cohorte, ch.numero_cohorte, ch.anio_cohorte,
                                 COUNT(DISTINCT i.id_estudiante_fk) as estudiantes_deudores,
                                 SUM(i.pago_faltante) as deuda_total,
                                 c.nombre_curso
                          FROM cohorte ch
                          JOIN curso_vigente cv ON cv.id_cohorte_fk = ch.id_cohorte
                          JOIN inscripcion i ON i.id_curso_vigente_fk = cv.id_curso_vigente
                          JOIN curso c ON c.id_curso = cv.id_curso_fk
                          WHERE i.estado_inscripcion = 'Activa'
                            AND i.archivada = 1
                            AND i.pago_faltante > 0
                            AND ch.archivar_cohorte = 1
                          GROUP BY ch.id_cohorte, c.nombre_curso
                          HAVING deuda_total > 0
                          ORDER BY deuda_total DESC
                          LIMIT 8";

			$stmtCohortesPendientes = $this->db->prepare($sqlCohortesPendientes);
			$stmtCohortesPendientes->execute();
			$cohortes_pendientes = $stmtCohortesPendientes->fetchAll(PDO::FETCH_ASSOC);

			foreach ($cohortes_pendientes as $cohorte) {
    			$notificaciones[] = [
        			'motivo' => 'Cohorte con deuda pendiente',
        			'referencia' => "{$cohorte['tipo_cohorte']} {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']} - Curso: {$cohorte['nombre_curso']}",
        			'fecha_creacion' => date('Y-m-d H:i:s'),
        			'leida' => false,
        			'tipo' => 'cohorte_deuda',
        			'estudiantes_deudores' => $cohorte['estudiantes_deudores'],
       			 	'deuda_total' => $cohorte['deuda_total'],
        			'cohorte_info' => "{$cohorte['tipo_cohorte']} {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']}"
    			];
			}

            if ($resumen['total_alumnos_deudores'] > 0) {
                $notificaciones[] = [
                    'motivo' => 'Resumen mensual',
                    'referencia' => "{$resumen['total_alumnos_deudores']} alumnos con deuda - Total pendiente: \${$resumen['total_deuda_pendiente']}",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'resumen_mensual',
                    'total_deudores' => $resumen['total_alumnos_deudores'],
                    'total_deuda' => $resumen['total_deuda_pendiente'],
                    'recaudado_mes' => $resumen['recaudado_mes_actual']
                ];
            }

        } catch (Exception $e) {
            error_log("Error en generarNotificacionesPagos: " . $e->getMessage());
        }

        return $notificaciones;
    }
}
?>