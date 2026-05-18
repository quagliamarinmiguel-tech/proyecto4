<?php
require_once __DIR__ . '/../modelos/mainModel.php';

class DashboardEstudianteController extends mainModel {

    public function obtener_progreso_academico($id_estudiante) {
        $sql = "
            SELECT 
                COUNT(*) AS total,
                SUM(CASE WHEN nota_final >= 10 AND nota_final IS NOT NULL THEN 1 ELSE 0 END) AS aprobadas
            FROM inscripcion
            WHERE id_estudiante_fk = ? 
              AND archivada = 1
              AND estado_inscripcion = 'Activa'
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id_estudiante]);
        $data = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$data) {
            return ['aprobadas' => 0, 'pendientes' => 0, 'total' => 0];
        }

        return [
            'aprobadas' => (int)$data['aprobadas'],
            'pendientes' => (int)$data['total'] - (int)$data['aprobadas'],
            'total' => (int)$data['total']
        ];
    }

    public function obtener_resumen_pagos($id_estudiante) {
        // Obtener inscripciones del estudiante (solo activas)
        $sql_inscripciones = "
            SELECT id_inscripcion 
            FROM inscripcion 
            WHERE id_estudiante_fk = ? 
              AND archivada = 1                          
              AND estado_inscripcion = 'Activa'          
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql_inscripciones);
        $stmt->execute([$id_estudiante]);
        $inscripciones = $stmt->fetchAll(PDO::FETCH_COLUMN);

        $pagado = 0;
        $faltante = 0;

        if (!empty($inscripciones)) {
            // Crear placeholders para la consulta
            $placeholders = str_repeat('?,', count($inscripciones) - 1) . '?';
            
            // 1. MONTO PAGADO: Suma de todas las transacciones CON estatus_pago = 1 (aprobadas)
            $sql_pagado = "
                SELECT COALESCE(SUM(monto), 0) 
                FROM transaccion 
                WHERE id_inscripcion IN ($placeholders) 
                  AND estatus_pago = 1
            ";
            $stmt_pagado = $conexion->prepare($sql_pagado);
            $stmt_pagado->execute($inscripciones);
            $pagado = (int)$stmt_pagado->fetchColumn();

            // 2. PAGO FALTANTE: Suma del último pago_faltante de cada inscripción
            // Para cada inscripción, obtenemos la transacción más reciente y tomamos su pago_faltante
            $faltante = 0;
            foreach ($inscripciones as $id_inscripcion) {
                $sql_ultima_transaccion = "
                    SELECT pago_faltante 
                    FROM transaccion 
                    WHERE id_inscripcion = ? 
                    ORDER BY fecha_pago DESC, id_transaccion DESC 
                    LIMIT 1
                ";
                $stmt_ultima = $conexion->prepare($sql_ultima_transaccion);
                $stmt_ultima->execute([$id_inscripcion]);
                $ultimo_faltante = $stmt_ultima->fetchColumn();
                
                if ($ultimo_faltante !== false) {
                    $faltante += (int)$ultimo_faltante;
                } else {
                    // Si no hay transacciones, usar el pago_faltante de la inscripción
                    $sql_inscripcion_faltante = "
                        SELECT pago_faltante 
                        FROM inscripcion 
                        WHERE id_inscripcion = ?
                    ";
                    $stmt_insc = $conexion->prepare($sql_inscripcion_faltante);
                    $stmt_insc->execute([$id_inscripcion]);
                    $faltante_insc = $stmt_insc->fetchColumn();
                    if ($faltante_insc !== false) {
                        $faltante += (int)$faltante_insc;
                    }
                }
            }
        }

        return [
            'pagado' => $pagado,
            'faltante' => $faltante
        ];
    }

    public function obtener_id_estudiante($id_usuario) {
        $sql = "SELECT id_estudiante_fk FROM usuario_estudiante WHERE id_usuario_fk = ?";
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql);
        $stmt->execute([$id_usuario]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        
        return $result ? $result['id_estudiante_fk'] : null;
    }

    // Función de DEBUG para ver todas las transacciones del estudiante
    public function debug_transacciones_estudiante($id_estudiante) {
        // Primero obtener las inscripciones del estudiante
        $sql_inscripciones = "
            SELECT i.id_inscripcion, c.nombre_curso, i.pago_faltante as inscripcion_pago_faltante
            FROM inscripcion i
            JOIN curso_vigente cv ON i.id_curso_vigente_fk = cv.id_curso_vigente
            JOIN curso c ON cv.id_curso_fk = c.id_curso
            WHERE i.id_estudiante_fk = ? 
              AND i.archivada = 1
              AND i.estado_inscripcion = 'Activa'
        ";
        
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare($sql_inscripciones);
        $stmt->execute([$id_estudiante]);
        $inscripciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $resultado = [];

        foreach ($inscripciones as $inscripcion) {
            $sql_transacciones = "
                SELECT 
                    t.id_transaccion,
                    t.monto,
                    t.estatus_pago,
                    t.motivo_pago,
                    t.fecha_pago,
                    t.pago_faltante
                FROM transaccion t
                WHERE t.id_inscripcion = ?
                ORDER BY t.fecha_pago DESC, t.id_transaccion DESC
            ";
            $stmt_trans = $conexion->prepare($sql_transacciones);
            $stmt_trans->execute([$inscripcion['id_inscripcion']]);
            $transacciones = $stmt_trans->fetchAll(PDO::FETCH_ASSOC);

            // Obtener la última transacción (la más reciente)
            $ultima_transaccion = !empty($transacciones) ? $transacciones[0] : null;

            $resultado[] = [
                'inscripcion' => $inscripcion,
                'transacciones' => $transacciones,
                'total_transacciones' => count($transacciones),
                'ultima_transaccion' => $ultima_transaccion,
                'pago_faltante_usado' => $ultima_transaccion ? $ultima_transaccion['pago_faltante'] : $inscripcion['inscripcion_pago_faltante'],
                'suma_montos_aprobados' => array_sum(array_column(
                    array_filter($transacciones, function($t) { 
                        return $t['estatus_pago'] == 1; 
                    }), 
                    'monto'
                ))
            ];
        }

        return $resultado;
    }

    public function obtener_datos_completos($id_usuario) {
        try {
            $id_estudiante = $this->obtener_id_estudiante($id_usuario);
            
            if (!$id_estudiante) {
                return [
                    'success' => false, 
                    'error' => 'Estudiante no encontrado para el usuario: ' . $id_usuario
                ];
            }

            // DEBUG: Obtener información detallada de las transacciones
            $debug_transacciones = $this->debug_transacciones_estudiante($id_estudiante);

            $progreso = $this->obtener_progreso_academico($id_estudiante);
            $pagos = $this->obtener_resumen_pagos($id_estudiante);

            return [
                'success' => true,
                'progreso' => $progreso,
                'pagos' => $pagos,
                'debug' => [
                    'user_id' => $id_usuario,
                    'student_id' => $id_estudiante,
                    'total_inscripciones_activas' => count($debug_transacciones),
                    'transacciones_detalle' => $debug_transacciones,
                    'calculos' => [
                        'pagado' => 'SUM(monto) WHERE estatus_pago = 1',
                        'faltante' => 'SUM(último pago_faltante por inscripción)'
                    ]
                ]
            ];

        } catch (Exception $e) {
            return [
                'success' => false, 
                'error' => $e->getMessage()
            ];
        }
    }
}

// Manejo de la solicitud
if ($_SERVER['REQUEST_METHOD'] === 'GET' && isset($_GET['user_id'])) {
    $user_id = (int)$_GET['user_id'];
    
    $controller = new DashboardEstudianteController();
    $datos = $controller->obtener_datos_completos($user_id);
    
    header('Content-Type: application/json');
    echo json_encode($datos);
    exit;
}

// Si no se proporciona user_id, devolver error
header('Content-Type: application/json');
http_response_code(400);
echo json_encode([
    'success' => false, 
    'error' => 'ID de usuario no proporcionado. Use: ?user_id=7'
]);
?>