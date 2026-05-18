<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class DashboardControlEstudioController extends mainModel {

    // 1. RESUMEN DE EVALUACIONES Y CALIFICACIONES
    public function obtener_resumen_evaluaciones() {
        $sql = "
            SELECT 
                COUNT(*) as total_evaluaciones,
                SUM(CASE WHEN e.fecha_evaluacion >= CURDATE() THEN 1 ELSE 0 END) as evaluaciones_futuras,
                SUM(CASE WHEN e.fecha_evaluacion < CURDATE() THEN 1 ELSE 0 END) as evaluaciones_pasadas,
                COUNT(DISTINCT e.id_seccion) as secciones_con_evaluaciones
            FROM evaluacion e
            WHERE e.archivar_evaluacion = 1
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 2. ESTADO DE CALIFICACIONES
    public function obtener_estado_calificaciones() {
        $sql = "
            SELECT 
                COUNT(*) as total_calificaciones,
                SUM(CASE WHEN pe.puntaje IS NOT NULL AND pe.puntaje != '' THEN 1 ELSE 0 END) as calificaciones_registradas,
                SUM(CASE WHEN pe.puntaje IS NULL OR pe.puntaje = '' THEN 1 ELSE 0 END) as calificaciones_pendientes,
                COUNT(DISTINCT pe.id_inscripcion) as estudiantes_calificados,
                COUNT(DISTINCT pe.id_evaluacion) as evaluaciones_con_calificaciones
            FROM puntaje_estudiante pe
            WHERE pe.archivar_puntaje = 1
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // 3. EVALUACIONES PRÓXIMAS (próximos 7 días)
    public function obtener_evaluaciones_proximas() {
        $sql = "
            SELECT 
                e.id_evaluacion,
                e.tipo_evaluacion,
                e.fecha_evaluacion,
                e.porcentaje,
                c.nombre_curso,
                s.nombre_seccion,
                ch.numero_cohorte,
                ch.anio_cohorte,
                CONCAT(p.nombre_profesor, ' ', p.apellido_profesor) as profesor,
                DATEDIFF(e.fecha_evaluacion, CURDATE()) as dias_restantes
            FROM evaluacion e
            JOIN seccion s ON e.id_seccion = s.id_seccion
            JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            JOIN curso c ON cv.id_curso_fk = c.id_curso
            JOIN cohorte ch ON cv.id_cohorte_fk = ch.Id_cohorte
            LEFT JOIN profesor p ON s.ci_profesor = p.ci_profesor
            WHERE e.archivar_evaluacion = 1
              AND e.fecha_evaluacion BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
            ORDER BY e.fecha_evaluacion ASC
            LIMIT 10
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. ESTUDIANTES EN RIESGO ACADÉMICO (notas bajas)
    public function obtener_estudiantes_riesgo() {
        $sql = "
            SELECT 
                i.id_inscripcion,
                e.id_estudiante,
                e.nombre,
                e.apellido,
                e.ci_estudiante,
                c.nombre_curso,
                s.nombre_seccion,
                i.nota_final,
                COUNT(pe.id_puntaje) as total_evaluaciones,
                AVG(CAST(pe.puntaje AS DECIMAL)) as promedio_actual,
                SUM(CASE WHEN CAST(pe.puntaje AS DECIMAL) < 10 THEN 1 ELSE 0 END) as evaluaciones_reprobadas
            FROM estudiante e
            JOIN inscripcion i ON e.id_estudiante = i.id_estudiante_fk
            JOIN seccion s ON i.id_seccion_fk = s.id_seccion
            JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN puntaje_estudiante pe ON i.id_inscripcion = pe.id_inscripcion AND pe.archivar_puntaje = 1
            WHERE i.archivada = 1 
              AND i.estado_inscripcion = 'Activa'
            GROUP BY i.id_inscripcion
            HAVING promedio_actual < 12 OR evaluaciones_reprobadas >= 2 OR i.nota_final < 10
            ORDER BY promedio_actual ASC
            LIMIT 15
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. PROMEDIOS POR CURSO
    public function obtener_promedios_cursos() {
        $sql = "
            SELECT 
                c.nombre_curso,
                ch.numero_cohorte,
                ch.anio_cohorte,
                s.nombre_seccion,
                COUNT(i.id_inscripcion) as total_estudiantes,
                AVG(CAST(i.nota_final AS DECIMAL)) as promedio_curso,
                SUM(CASE WHEN i.nota_final >= 10 THEN 1 ELSE 0 END) as aprobados,
                SUM(CASE WHEN i.nota_final < 10 THEN 1 ELSE 0 END) as reprobados,
                ROUND((SUM(CASE WHEN i.nota_final >= 10 THEN 1 ELSE 0 END) / COUNT(*)) * 100, 2) as tasa_aprobacion
            FROM curso c
            JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
            JOIN cohorte ch ON cv.id_cohorte_fk = ch.Id_cohorte
            JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk
            JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk
            WHERE i.archivada = 1 
              AND i.estado_inscripcion = 'Activa'
              AND i.nota_final IS NOT NULL
            GROUP BY c.id_curso, ch.Id_cohorte, s.id_seccion
            ORDER BY promedio_curso DESC
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 6. EVALUACIONES SIN CALIFICAR
    public function obtener_evaluaciones_sin_calificar() {
        $sql = "
            SELECT 
                e.id_evaluacion,
                e.tipo_evaluacion,
                e.fecha_evaluacion,
                c.nombre_curso,
                s.nombre_seccion,
                COUNT(i.id_inscripcion) as total_estudiantes,
                COUNT(pe.id_puntaje) as calificaciones_registradas,
                (COUNT(i.id_inscripcion) - COUNT(pe.id_puntaje)) as pendientes_calificar
            FROM evaluacion e
            JOIN seccion s ON e.id_seccion = s.id_seccion
            JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            JOIN curso c ON cv.id_curso_fk = c.id_curso
            JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk AND i.archivada = 1
            LEFT JOIN puntaje_estudiante pe ON e.id_evaluacion = pe.id_evaluacion AND pe.id_inscripcion = i.id_inscripcion AND pe.archivar_puntaje = 1
            WHERE e.archivar_evaluacion = 1
              AND e.fecha_evaluacion <= CURDATE()
            GROUP BY e.id_evaluacion
            HAVING pendientes_calificar > 0
            ORDER BY e.fecha_evaluacion DESC
            LIMIT 10
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 7. MÉTRICAS GENERALES PARA TARJETAS
    public function obtener_metricas_generales() {
        $conexion = mainModel::conectar();
        
        $metricas = [];
        
        // Total evaluaciones
        $sql = "SELECT COUNT(*) FROM evaluacion WHERE archivar_evaluacion = 1";
        $metricas['total_evaluaciones'] = $conexion->query($sql)->fetchColumn();
        
        // Total calificaciones registradas
        $sql = "SELECT COUNT(*) FROM puntaje_estudiante WHERE archivar_puntaje = 1 AND puntaje IS NOT NULL";
        $metricas['total_calificaciones'] = $conexion->query($sql)->fetchColumn();
        
        // Evaluaciones próximas (7 días)
        $sql = "SELECT COUNT(*) FROM evaluacion WHERE archivar_evaluacion = 1 AND fecha_evaluacion BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)";
        $metricas['evaluaciones_proximas'] = $conexion->query($sql)->fetchColumn();
        
        // Estudiantes en riesgo
        $sql = "SELECT COUNT(DISTINCT i.id_inscripcion) 
                FROM inscripcion i 
                JOIN puntaje_estudiante pe ON i.id_inscripcion = pe.id_inscripcion 
                WHERE i.archivada = 1 
                AND i.estado_inscripcion = 'Activa'
                AND pe.archivar_puntaje = 1
                AND CAST(pe.puntaje AS DECIMAL) < 10";
        $metricas['estudiantes_riesgo'] = $conexion->query($sql)->fetchColumn();
        
        // Evaluaciones sin calificar
        $sql = "SELECT COUNT(DISTINCT e.id_evaluacion) 
                FROM evaluacion e 
                JOIN seccion s ON e.id_seccion = s.id_seccion 
                JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk 
                LEFT JOIN puntaje_estudiante pe ON e.id_evaluacion = pe.id_evaluacion AND pe.id_inscripcion = i.id_inscripcion 
                WHERE e.archivar_evaluacion = 1 
                AND e.fecha_evaluacion <= CURDATE() 
                AND pe.id_puntaje IS NULL";
        $metricas['evaluaciones_sin_calificar'] = $conexion->query($sql)->fetchColumn();
        
        // Tasa de aprobación general
        $sql = "SELECT ROUND(AVG(nota_final), 2) FROM inscripcion WHERE archivada = 1 AND nota_final IS NOT NULL";
        $metricas['promedio_general'] = $conexion->query($sql)->fetchColumn();
        
        return $metricas;
    }

    // 8. DATOS COMPLETOS PARA EL DASHBOARD
    public function obtener_datos_completos() {
        return [
            'metricas_generales' => $this->obtener_metricas_generales(),
            'resumen_evaluaciones' => $this->obtener_resumen_evaluaciones(),
            'estado_calificaciones' => $this->obtener_estado_calificaciones(),
            'evaluaciones_proximas' => $this->obtener_evaluaciones_proximas(),
            'estudiantes_riesgo' => $this->obtener_estudiantes_riesgo(),
            'promedios_cursos' => $this->obtener_promedios_cursos(),
            'evaluaciones_sin_calificar' => $this->obtener_evaluaciones_sin_calificar()
        ];
    }
}

// Manejo de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new DashboardControlEstudioController();
    $datos = $controller->obtener_datos_completos();
    
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}
?>