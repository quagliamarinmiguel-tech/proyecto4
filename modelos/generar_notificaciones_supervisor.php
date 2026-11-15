<?php
class NotificacionSupervisorModelo {
    private $db;
    
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    public function generarNotificacionesSupervisor($id_usuario) {
        error_log("=== GENERANDO NOTIFICACIONES PARA SUPERVISOR ===");
        
        $notificaciones = [];

        try {
            // 1. Cohortes que terminan en los próximos 7 días
            $sqlCohortes = "SELECT ch.Id_cohorte, ch.tipo_cohorte, ch.numero_cohorte, ch.anio_cohorte, 
                                   ch.fecha_fin, DATEDIFF(ch.fecha_fin, CURDATE()) as dias_restantes
                            FROM cohorte ch
                            WHERE ch.estado_cohorte = 'Activo'
                              AND ch.archivar_cohorte = 1
                              AND ch.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                            ORDER BY ch.fecha_fin ASC";
            
            $stmtCohortes = $this->db->prepare($sqlCohortes);
            $stmtCohortes->execute();
            $cohortes_proximos = $stmtCohortes->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cohortes_proximos as $cohorte) {
                $cursos_vigentes = $this->obtenerCursosVigentes($cohorte['Id_cohorte']);
                
                $notificaciones[] = [
                    'motivo' => 'Cohorte por finalizar',
                    'referencia' => "Cohorte {$cohorte['tipo_cohorte']} {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']} - Finaliza en {$cohorte['dias_restantes']} días",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'cohorte',
                    'id_cohorte' => $cohorte['Id_cohorte'],
                    'fecha_fin' => $cohorte['fecha_fin'],
                    'cursos_asociados' => $cursos_vigentes
                ];
            }

            // 2. Cursos vigentes con baja inscripción (menos del 50% de cupos)
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
                              HAVING porcentaje_ocupacion < 50
                              ORDER BY porcentaje_ocupacion ASC";
            
            $stmtCursosBaja = $this->db->prepare($sqlCursosBaja);
            $stmtCursosBaja->execute();
            $cursos_baja_inscripcion = $stmtCursosBaja->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cursos_baja_inscripcion as $curso) {
                $notificaciones[] = [
                    'motivo' => 'Curso con baja inscripción',
                    'referencia' => "{$curso['nombre_curso']} - {$curso['porcentaje_ocupacion']}% ocupado ({$curso['inscritos_actuales']}/{$curso['cupos_totales']})",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'baja_inscripcion',
                    'id_curso_vigente' => $curso['id_curso_vigente'],
                    'porcentaje_ocupacion' => $curso['porcentaje_ocupacion']
                ];
            }

            // 3. Cohortes que iniciaron hace menos de 7 días (nuevos)
            $sqlCohortesNuevos = "SELECT ch.Id_cohorte, ch.tipo_cohorte, ch.numero_cohorte, ch.anio_cohorte,
                                         ch.fecha_inicio, DATEDIFF(CURDATE(), ch.fecha_inicio) as dias_desde_inicio
                                  FROM cohorte ch
                                  WHERE ch.estado_cohorte = 'Activo'
                                    AND ch.archivar_cohorte = 1
                                    AND ch.fecha_inicio BETWEEN DATE_SUB(CURDATE(), INTERVAL 7 DAY) AND CURDATE()
                                  ORDER BY ch.fecha_inicio DESC";
            
            $stmtCohortesNuevos = $this->db->prepare($sqlCohortesNuevos);
            $stmtCohortesNuevos->execute();
            $cohortes_nuevos = $stmtCohortesNuevos->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cohortes_nuevos as $cohorte) {
                $notificaciones[] = [
                    'motivo' => 'Cohorte nuevo iniciado',
                    'referencia' => "Cohorte {$cohorte['tipo_cohorte']} {$cohorte['numero_cohorte']}-{$cohorte['anio_cohorte']} - Inició hace {$cohorte['dias_desde_inicio']} días",
                    'fecha_creacion' => date('Y-m-d H:i:s'),
                    'leida' => false,
                    'tipo' => 'cohorte_nuevo',
                    'id_cohorte' => $cohorte['Id_cohorte'],
                    'fecha_inicio' => $cohorte['fecha_inicio']
                ];
            }

        } catch (Exception $e) {
            error_log("Error en generarNotificacionesSupervisor: " . $e->getMessage());
        }

        return $notificaciones;
    }

    private function obtenerCursosVigentes($id_cohorte) {
        $sql = "SELECT c.nombre_curso, cv.oferta, 
                       COUNT(i.id_inscripcion) as inscritos
                FROM curso_vigente cv
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                LEFT JOIN inscripcion i ON i.id_curso_vigente_fk = cv.id_curso_vigente 
                      AND i.estado_inscripcion = 'Activa'
                      AND i.archivada = 1
                WHERE cv.id_cohorte_fk = ?
                GROUP BY cv.id_curso_vigente, c.nombre_curso, cv.oferta";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute([$id_cohorte]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>