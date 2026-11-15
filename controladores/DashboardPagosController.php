<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class DashboardPagosController extends mainModel {

    // 1. Resumen general de pagos
    public function obtener_resumen_pagos_general() {
        $conexion = mainModel::conectar();

        // Total recaudado (suma de montos de transacciones aprobadas)
        $sql_recaudado = "
            SELECT COALESCE(SUM(monto), 0) as total_recaudado
            FROM transaccion 
            WHERE estatus_pago = 1
        ";
        $total_recaudado = (int)$conexion->query($sql_recaudado)->fetchColumn();

        // Total pendiente (suma de pago_faltante de la última transacción de cada inscripción activa)
        $sql_pendiente = "
            SELECT COALESCE(SUM(ultimos_faltantes.pago_faltante), 0) as total_pendiente
            FROM (
                SELECT t1.id_inscripcion, t1.pago_faltante
                FROM transaccion t1
                INNER JOIN (
                    SELECT id_inscripcion, MAX(fecha_pago) as max_fecha, MAX(id_transaccion) as max_id
                    FROM transaccion
                    GROUP BY id_inscripcion
                ) t2 ON t1.id_inscripcion = t2.id_inscripcion AND t1.fecha_pago = t2.max_fecha AND t1.id_transaccion = t2.max_id
            ) as ultimos_faltantes
        ";
        $total_pendiente = (int)$conexion->query($sql_pendiente)->fetchColumn();

        // Total de transacciones aprobadas
        $sql_total_transacciones = "
            SELECT COUNT(*) as total_transacciones
            FROM transaccion 
            WHERE estatus_pago = 1
        ";
        $total_transacciones = (int)$conexion->query($sql_total_transacciones)->fetchColumn();

        // Total de inscripciones activas
        $sql_inscripciones_activas = "
            SELECT COUNT(*) as total_inscripciones
            FROM inscripcion 
            WHERE archivada = 1 AND estado_inscripcion = 'Activa'
        ";
        $total_inscripciones = (int)$conexion->query($sql_inscripciones_activas)->fetchColumn();

        return [
            'total_recaudado' => $total_recaudado,
            'total_pendiente' => $total_pendiente,
            'total_transacciones' => $total_transacciones,
            'total_inscripciones' => $total_inscripciones
        ];
    }

    // 2. Estudiantes con deudas (pago_faltante > 0)
    public function obtener_estudiantes_deudores() {
        $sql = "
            SELECT 
                e.id_estudiante,
                e.ci_estudiante,
                e.nombre,
                e.apellido,
                e.correo_estudiante,
                SUM(ultimos_faltantes.pago_faltante) as deuda_total,
                COUNT(ultimos_faltantes.id_inscripcion) as inscripciones_con_deuda
            FROM estudiante e
            INNER JOIN inscripcion i ON e.id_estudiante = i.id_estudiante_fk
            INNER JOIN (
                SELECT t1.id_inscripcion, t1.pago_faltante
                FROM transaccion t1
                INNER JOIN (
                    SELECT id_inscripcion, MAX(fecha_pago) as max_fecha, MAX(id_transaccion) as max_id
                    FROM transaccion
                    GROUP BY id_inscripcion
                ) t2 ON t1.id_inscripcion = t2.id_inscripcion AND t1.fecha_pago = t2.max_fecha AND t1.id_transaccion = t2.max_id
            ) as ultimos_faltantes ON i.id_inscripcion = ultimos_faltantes.id_inscripcion
            WHERE i.archivada = 1 
              AND i.estado_inscripcion = 'Activa'
              AND ultimos_faltantes.pago_faltante > 0
            GROUP BY e.id_estudiante
            ORDER BY deuda_total DESC
            LIMIT 50
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 3. Cursos más vendidos (con más inscripciones activas)
    public function obtener_cursos_mas_vendidos() {
        $sql = "
            SELECT 
                c.id_curso,
                c.nombre_curso,
                COUNT(i.id_inscripcion) as total_inscripciones,
                COALESCE(SUM(t.monto), 0) as total_recaudado
            FROM curso c
            INNER JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
            INNER JOIN inscripcion i ON cv.id_curso_vigente = i.id_curso_vigente_fk
            LEFT JOIN transaccion t ON i.id_inscripcion = t.id_inscripcion AND t.estatus_pago = 1
            WHERE i.archivada = 1 
              AND i.estado_inscripcion = 'Activa'
            GROUP BY c.id_curso
            ORDER BY total_inscripciones DESC
            LIMIT 10
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 4. Cursos menos vendidos (con menos inscripciones activas)
    public function obtener_cursos_menos_vendidos() {
        $sql = "
            SELECT 
                c.id_curso,
                c.nombre_curso,
                COUNT(i.id_inscripcion) as total_inscripciones,
                COALESCE(SUM(t.monto), 0) as total_recaudado
            FROM curso c
            INNER JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
            INNER JOIN inscripcion i ON cv.id_curso_vigente = i.id_curso_vigente_fk
            LEFT JOIN transaccion t ON i.id_inscripcion = t.id_inscripcion AND t.estatus_pago = 1
            WHERE i.archivada = 1 
              AND i.estado_inscripcion = 'Activa'
            GROUP BY c.id_curso
            HAVING total_inscripciones > 0
            ORDER BY total_inscripciones ASC
            LIMIT 10
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // 5. Transacciones recientes (últimos pagos aprobados)
    public function obtener_transacciones_recientes() {
        $sql = "
            SELECT 
                t.id_transaccion,
                t.monto,
                t.fecha_pago,
                t.metodo_pago,
                t.motivo_pago,
                e.nombre,
                e.apellido,
                c.nombre_curso
            FROM transaccion t
            INNER JOIN inscripcion i ON t.id_inscripcion = i.id_inscripcion
            INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
            INNER JOIN curso_vigente cv ON i.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            WHERE t.estatus_pago = 1
            ORDER BY t.fecha_pago DESC
            LIMIT 20
        ";

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    // Función principal que devuelve todos los datos para el dashboard
    public function obtener_datos_completos() {
        try {
            $resumen = $this->obtener_resumen_pagos_general();
            $estudiantes_deudores = $this->obtener_estudiantes_deudores();
            $cursos_mas_vendidos = $this->obtener_cursos_mas_vendidos();
            $cursos_menos_vendidos = $this->obtener_cursos_menos_vendidos();
            $transacciones_recientes = $this->obtener_transacciones_recientes();

            return [
                'success' => true,
                'resumen' => $resumen,
                'estudiantes_deudores' => $estudiantes_deudores,
                'cursos_mas_vendidos' => $cursos_mas_vendidos,
                'cursos_menos_vendidos' => $cursos_menos_vendidos,
                'transacciones_recientes' => $transacciones_recientes
            ];

        } catch (Exception $e) {
            return [
                'success' => false, 
                'error' => $e->getMessage()
            ];
        }
    }
}

// Manejo de la solicitud para el rol #3
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $controller = new DashboardPagosController();
    $datos = $controller->obtener_datos_completos();
    
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}
?>