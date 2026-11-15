<?php
session_start();
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['accion']) && $_POST['accion'] == 'obtener_precios') {
    try {
        $conexion = mainModel::conectar();
        

        $id_inscripcion = mainModel::limpiar_cadena($_POST['id_inscripcion'] ?? '');
        
        if (empty($id_inscripcion)) {
            throw new Exception("ID de inscripción requerido.");
        }


        $consulta = $conexion->prepare("
            SELECT 
                c.pago_modulo as modulo_pago,
                c.pago_aranceles as arancel_pago,
                c.pago_art13 as art13_pago
            FROM inscripcion i
            JOIN curso_vigente cv ON i.id_curso_vigente_fk = cv.id_curso_vigente
            JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
            WHERE i.id_inscripcion = :id_inscripcion
        ");
        
        $consulta->execute([':id_inscripcion' => $id_inscripcion]);
        $precios = $consulta->fetch(PDO::FETCH_ASSOC);
        
        if (!$precios) {
            throw new Exception("No se encontraron precios para esta inscripción.");
        }


        $consultaPagos = $conexion->prepare("
            SELECT 
                motivo_pago,
                SUM(monto) as total_pagado
            FROM transaccion 
            WHERE id_inscripcion = :id_inscripcion 
            AND estatus_pago = 1  -- Asumiendo que 1 significa pago confirmado
            GROUP BY motivo_pago
        ");
        
        $consultaPagos->execute([':id_inscripcion' => $id_inscripcion]);
        $pagosExistentes = $consultaPagos->fetchAll(PDO::FETCH_KEY_PAIR);


        $montosRestantes = [
            'Módulo 1' => floatval($precios['modulo_pago']) - floatval($pagosExistentes['Módulo 1'] ?? 0),
            'Módulo 2' => floatval($precios['modulo_pago']) - floatval($pagosExistentes['Módulo 2'] ?? 0),
            'Módulo 3' => floatval($precios['modulo_pago']) - floatval($pagosExistentes['Módulo 3'] ?? 0),
            'Aranceles' => floatval($precios['arancel_pago']) - floatval($pagosExistentes['Aranceles'] ?? 0),
            'art13' => floatval($precios['art13_pago']) - floatval($pagosExistentes['art13'] ?? 0)
        ];


        foreach ($montosRestantes as $key => $valor) {
            if ($valor < 0) $montosRestantes[$key] = 0;
        }

        header('Content-Type: application/json');
        echo json_encode([
            'success' => true,
            'precios' => [
                'modulos' => floatval($precios['modulo_pago']),
                'aranceles' => floatval($precios['arancel_pago']),
                'art13' => floatval($precios['art13_pago'])
            ],
            'montos_restantes' => $montosRestantes,
            'pagos_existentes' => $pagosExistentes
        ]);

    } catch (Exception $e) {
        header('Content-Type: application/json');
        http_response_code(400);
        echo json_encode([
            'success' => false,
            'message' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo "Método no permitido.";
}
?>