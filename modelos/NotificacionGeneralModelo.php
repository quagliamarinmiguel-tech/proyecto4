<?php
class NotificacionGeneralModelo {
    private $db;
    
    public function __construct($conexion) {
        $this->db = $conexion;
    }

    // ==================== ADMIN (ROL 1) ====================
    public function generarNotificacionesAdmin($id_usuario) {
        $notificaciones = [];

        // Obtener notificaciones de Control de estudio (rol 2)
        $notificaciones = array_merge($notificaciones, $this->generarNotificacionesControlEstudio($id_usuario, true));

        // Obtener notificaciones de Administrador de pagos (rol 3)
        $notificaciones = array_merge($notificaciones, $this->generarNotificacionesAdminPagos($id_usuario, true));

        // Obtener notificaciones de Supervisor de cursos (rol 4)
        $notificaciones = array_merge($notificaciones, $this->generarNotificacionesSupervisorCursos($id_usuario, true));

        return $notificaciones;
    }

    // ==================== CONTROL DE ESTUDIO (ROL 2) ====================
    public function generarNotificacionesControlEstudio($id_usuario, $esAdmin = false) {
        $notificaciones = [];

        // 1. Notificaciones de estudiantes próximos a graduarse
        $sql = "SELECT e.id_estudiante, e.nombre, e.apellido, c.nombre_curso,
                       DATEDIFF(cv.fecha_fin, CURDATE()) as dias_para_finalizar
                FROM estudiante e
                JOIN inscripcion i ON i.id_estudiante_fk = e.id_estudiante
                JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                WHERE i.estado_inscripcion = 'Activa'
                AND DATEDIFF(cv.fecha_fin, CURDATE()) BETWEEN 0 AND 30";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $estudiantesProximos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($estudiantesProximos as $est) {
            $notificaciones[] = [
                'motivo' => 'Estudiante próximo a finalizar',
                'referencia' => "{$est['nombre']} {$est['apellido']} - Curso: {$est['nombre_curso']} - Finaliza en {$est['dias_para_finalizar']} días",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'academico',
                'rol_origen' => 2
            ];
        }

        // 2. Notificaciones de cursos sin profesores asignados
        $sql = "SELECT cv.id_curso_vigente, c.nombre_curso
                FROM curso_vigente cv
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                LEFT JOIN profesor_curso pc ON pc.id_curso_vigente_fk = cv.id_curso_vigente
                WHERE pc.id_profesor_curso IS NULL
                AND cv.estado_curso_vigente = 'Activo'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $cursosSinProfesor = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursosSinProfesor as $curso) {
            $notificaciones[] = [
                'motivo' => 'Curso sin profesor asignado',
                'referencia' => "Curso: {$curso['nombre_curso']}",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'academico',
                'rol_origen' => 2
            ];
        }

        // 3. Notificaciones de inscripciones pendientes de revisión
        $sql = "SELECT i.id_inscripcion, e.nombre, e.apellido, c.nombre_curso,
                       DATEDIFF(CURDATE(), i.fecha_inscripcion) as dias_pendiente
                FROM inscripcion i
                JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                WHERE i.estado_inscripcion = 'Pendiente'
                AND DATEDIFF(CURDATE(), i.fecha_inscripcion) > 2";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $inscripcionesPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($inscripcionesPendientes as $insc) {
            $notificaciones[] = [
                'motivo' => 'Inscripción pendiente de revisión',
                'referencia' => "{$insc['nombre']} {$insc['apellido']} - Curso: {$insc['nombre_curso']} - {$insc['dias_pendiente']} días pendiente",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'academico',
                'rol_origen' => 2
            ];
        }

        // Si es admin, agregar prefijo al motivo
        if ($esAdmin) {
            foreach ($notificaciones as &$notif) {
                $notif['motivo'] = "[Control Estudio] " . $notif['motivo'];
            }
        }

        return $notificaciones;
    }

// ==================== ADMINISTRADOR DE PAGOS (ROL 3) ====================
    public function generarNotificacionesAdminPagos($id_usuario, $esAdmin = false) {
        $notificaciones = [];

        // 1. Notificaciones de pagos pendientes de todos los estudiantes
        $sql = "SELECT i.id_inscripcion, e.nombre, e.apellido, c.nombre_curso, i.pago_faltante
                FROM inscripcion i
                JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                WHERE i.pago_faltante > 0
                AND i.estado_inscripcion = 'Activa'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pagosPendientes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pagosPendientes as $pago) {
            $notificaciones[] = [
                'motivo' => 'Pago pendiente',
                'referencia' => "{$pago['nombre']} {$pago['apellido']} - Curso: {$pago['nombre_curso']} - Monto: \${$pago['pago_faltante']}",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'financiero',
                'rol_origen' => 3
            ];
        }

        // 2. Notificaciones de pagos atrasados (más de 30 días)
        $sql = "SELECT i.id_inscripcion, e.nombre, e.apellido, c.nombre_curso, i.pago_faltante,
                       DATEDIFF(CURDATE(), i.fecha_inscripcion) as dias_atraso
                FROM inscripcion i
                JOIN estudiante e ON e.id_estudiante = i.id_estudiante_fk
                JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                WHERE i.pago_faltante > 0
                AND i.estado_inscripcion = 'Activa'
                AND DATEDIFF(CURDATE(), i.fecha_inscripcion) > 30";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pagosAtrasados = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($pagosAtrasados as $pago) {
            $notificaciones[] = [
                'motivo' => 'Pago atrasado',
                'referencia' => "{$pago['nombre']} {$pago['apellido']} - Curso: {$pago['nombre_curso']} - Atraso: {$pago['dias_atraso']} días",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'financiero',
                'rol_origen' => 3
            ];
        }

        // 3. Notificaciones de pagos del día
        $sql = "SELECT COUNT(*) as total_pagos, SUM(t.monto) as total_monto
                FROM transaccion t
                WHERE DATE(t.fecha_pago) = CURDATE()
                AND t.estatus_pago = 'Completado'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $pagosHoy = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($pagosHoy['total_pagos'] > 0) {
            $notificaciones[] = [
                'motivo' => 'Resumen de pagos del día',
                'referencia' => "{$pagosHoy['total_pagos']} pagos procesados - Total: \${$pagosHoy['total_monto']}",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'financiero',
                'rol_origen' => 3
            ];
        }

        // Si es admin, agregar prefijo al motivo
        if ($esAdmin) {
            foreach ($notificaciones as &$notif) {
                $notif['motivo'] = "[Admin Pagos] " . $notif['motivo'];
            }
        }

        return $notificaciones;
    }

    // ==================== SUPERVISOR DE CURSOS (ROL 4) ====================
    public function generarNotificacionesSupervisorCursos($id_usuario, $esAdmin = false) {
        $notificaciones = [];

        // 1. Notificaciones de cursos próximos a iniciar
        $sql = "SELECT c.nombre_curso, cv.id_curso_vigente, ch.fecha_inicio,
                       DATEDIFF(ch.fecha_inicio, CURDATE()) as dias_para_inicio
                FROM curso_vigente cv
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                JOIN cohorte ch ON ch.id_cohorte = cv.id_cohorte_fk
                WHERE ch.fecha_inicio BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND cv.estado_curso_vigente = 'Activo'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $cursosProximos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursosProximos as $curso) {
            $notificaciones[] = [
                'motivo' => 'Curso próximo a iniciar',
                'referencia' => "{$curso['nombre_curso']} - Inicia en {$curso['dias_para_inicio']} días",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'curso',
                'rol_origen' => 4
            ];
        }

        // 2. Notificaciones de cursos con cupos llenos
        $sql = "SELECT c.nombre_curso, cv.id_curso_vigente, c.cupos,
                       COUNT(i.id_inscripcion) as inscritos
                FROM curso_vigente cv
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                JOIN inscripcion i ON i.id_curso_vigente_fk = cv.id_curso_vigente
                WHERE i.estado_inscripcion = 'Activa'
                AND cv.estado_curso_vigente = 'Activo'
                GROUP BY cv.id_curso_vigente
                HAVING inscritos >= c.cupos";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $cursosLlenos = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursosLlenos as $curso) {
            $notificaciones[] = [
                'motivo' => 'Curso con cupo lleno',
                'referencia' => "{$curso['nombre_curso']} - {$curso['inscritos']}/{$curso['cupos']} estudiantes",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'curso',
                'rol_origen' => 4
            ];
        }

        // 3. Notificaciones de cursos sin estudiantes inscritos
        $sql = "SELECT c.nombre_curso, cv.id_curso_vigente
                FROM curso_vigente cv
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                LEFT JOIN inscripcion i ON i.id_curso_vigente_fk = cv.id_curso_vigente AND i.estado_inscripcion = 'Activa'
                WHERE i.id_inscripcion IS NULL
                AND cv.estado_curso_vigente = 'Activo'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $cursosSinEstudiantes = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursosSinEstudiantes as $curso) {
            $notificaciones[] = [
                'motivo' => 'Curso sin estudiantes',
                'referencia' => "{$curso['nombre_curso']} - Sin inscripciones activas",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'curso',
                'rol_origen' => 4
            ];
        }

        // 4. Notificaciones de cursos finalizando esta semana
        $sql = "SELECT c.nombre_curso, cv.id_curso_vigente, ch.fecha_fin,
                       DATEDIFF(ch.fecha_fin, CURDATE()) as dias_para_finalizar
                FROM curso_vigente cv
                JOIN curso c ON c.id_curso = cv.id_curso_fk
                JOIN cohorte ch ON ch.id_cohorte = cv.id_cohorte_fk
                WHERE ch.fecha_fin BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                AND cv.estado_curso_vigente = 'Activo'";
        
        $stmt = $this->db->prepare($sql);
        $stmt->execute();
        $cursosFinalizando = $stmt->fetchAll(PDO::FETCH_ASSOC);

        foreach ($cursosFinalizando as $curso) {
            $notificaciones[] = [
                'motivo' => 'Curso próximo a finalizar',
                'referencia' => "{$curso['nombre_curso']} - Finaliza en {$curso['dias_para_finalizar']} días",
                'fecha_creacion' => date('Y-m-d H:i:s'),
                'leida' => false,
                'tipo' => 'curso',
                'rol_origen' => 4
            ];
        }

        // Si es admin, agregar prefijo al motivo
        if ($esAdmin) {
            foreach ($notificaciones as &$notif) {
                $notif['motivo'] = "[Supervisor Cursos] " . $notif['motivo'];
            }
        }

        return $notificaciones;
    }

    // ==================== ESTUDIANTE (ROL 5) ====================
    public function generarNotificacionesEstudiante($id_usuario, $id_estudiante) {
        $notificaciones = [];

        // 1. Notificaciones de pagos pendientes
        $sqlPagos = "SELECT i.id_inscripcion, c.nombre_curso, i.pago_faltante
                     FROM inscripcion i
                     JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                     JOIN curso c ON c.id_curso = cv.id_curso_fk
                     WHERE i.id_estudiante_fk = ? 
                       AND i.pago_faltante > 0
                       AND i.estado_inscripcion = 'Activa'";
        
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
                'rol_origen' => 5
            ];
        }

        // 2. Notificaciones de cursos próximos a iniciar
        $sqlCursos = "SELECT c.nombre_curso, ch.fecha_inicio
                      FROM inscripcion i
                      JOIN curso_vigente cv ON cv.id_curso_vigente = i.id_curso_vigente_fk
                      JOIN curso c ON c.id_curso = cv.id_curso_fk
                      JOIN cohorte ch ON ch.id_cohorte = cv.id_cohorte_fk
                      WHERE i.id_estudiante_fk = ? 
                        AND i.estado_inscripcion = 'Activa'
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
                'tipo' => 'curso',
                'rol_origen' => 5
            ];
        }

        return $notificaciones;
    }
}
?>