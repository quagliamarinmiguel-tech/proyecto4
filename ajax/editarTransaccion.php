<?php

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion = mainModel::conectar();

        // Obtener y limpiar datos
        $id_transaccion = mainModel::limpiar_cadena($_POST['id_transaccion'] ?? '');
        $monto = mainModel::limpiar_cadena($_POST['monto'] ?? '');
        $fecha_pago = mainModel::limpiar_cadena($_POST['fecha_pago'] ?? '');
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago'] ?? '');
        $archivo = $_FILES['comprobante'] ?? null;

        if (empty($id_transaccion) || empty($monto) || empty($fecha_pago) || empty($metodo_pago)) {
            throw new Exception("Faltan campos obligatorios.");
        }

        $monto = floatval($monto);
        if ($monto <= 0) {
            throw new Exception("El monto debe ser mayor a cero.");
        }

        // Obtener datos actuales de la transacción
        $consultaTrans = $conexion->prepare("
            SELECT id_inscripcion, motivo_pago, if_pagomovil_imagen 
            FROM transaccion 
            WHERE id_transaccion = :id
        ");
        $consultaTrans->execute([':id' => $id_transaccion]);
        $transaccion = $consultaTrans->fetch(PDO::FETCH_ASSOC);

        if (!$transaccion) {
            throw new Exception("Transacción no encontrada.");
        }

        $id_inscripcion = $transaccion['id_inscripcion'];
        $motivo_pago = $transaccion['motivo_pago'];
        $imagenActual = $transaccion['if_pagomovil_imagen'];

        // Validar motivo y mapear al campo de cohorte
        switch ($motivo_pago) {
            case 'Inscripción': $campoMonto = 'pago_inscripcion'; break;
            case 'Módulo 1':
            case 'Módulo 2':
            case 'Módulo 3': $campoMonto = 'pago_modulo'; break;
            case 'Aranceles': $campoMonto = 'pago_aranceles'; break;
            case 'art13': $campoMonto = 'pago_art13'; break;
            default: throw new Exception("Motivo de pago no válido.");
        }

        // Obtener monto requerido desde cohorte
        $consultaMonto = $conexion->prepare("
            SELECT c.$campoMonto
            FROM inscripcion i
            JOIN curso_vigente cv ON i.id_curso_vigente_fk = cv.id_curso_vigente
            JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
            WHERE i.id_inscripcion = :id_inscripcion
        ");
        $consultaMonto->execute([':id_inscripcion' => $id_inscripcion]);
        $montoRequerido = $consultaMonto->fetchColumn();

        if (!$montoRequerido) {
            throw new Exception("No se pudo obtener el monto requerido para '$motivo_pago'.");
        }

        // Sumar pagos del mismo motivo, excluyendo esta transacción
        $consultaPagos = $conexion->prepare("
            SELECT SUM(monto) 
            FROM transaccion 
            WHERE id_inscripcion = :id 
              AND motivo_pago = :motivo
              AND id_transaccion != :id_transaccion
        ");
        $consultaPagos->execute([
            ':id' => $id_inscripcion,
            ':motivo' => $motivo_pago,
            ':id_transaccion' => $id_transaccion
        ]);
        $montoActual = $consultaPagos->fetchColumn() ?? 0;

        if (($montoActual + $monto) > $montoRequerido) {
            throw new Exception("La suma de pagos para '$motivo_pago' excede el monto permitido ($montoRequerido).");
        }

        // 🧮 Calcular pago faltante para esta transacción
        $pagoFaltanteTransaccion = max(0, $montoRequerido - ($montoActual + $monto));

        // Manejo de comprobante (opcional)
        $nombreArchivo = $imagenActual; // por defecto se conserva
        if ($archivo && $archivo['size'] > 0) {
            $permitidos = ['image/jpeg', 'image/png', 'image/jpg'];
            if (!in_array($archivo['type'], $permitidos)) {
                throw new Exception("Formato de imagen no permitido. Solo JPG y PNG.");
            }

            $nombreArchivo = uniqid("comprobante_") . "." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
            $rutaDestino = "../comprobantes/" . $nombreArchivo;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
                throw new Exception("No se pudo guardar el comprobante.");
            }
        }

        // Actualizar transacción
        $update = $conexion->prepare("
            UPDATE transaccion SET
                monto = :monto,
                fecha_pago = :fecha_pago,
                metodo_pago = :metodo_pago,
                if_pagomovil_imagen = :imagen,
                pago_faltante = :pago_faltante
            WHERE id_transaccion = :id
        ");

        $ok = $update->execute([
            ':monto' => $monto,
            ':fecha_pago' => $fecha_pago,
            ':metodo_pago' => $metodo_pago,
            ':imagen' => $nombreArchivo,
            ':pago_faltante' => $pagoFaltanteTransaccion,
            ':id' => $id_transaccion
        ]);

        if (!$ok) {
            throw new Exception("Error al actualizar la transacción.");
        }

        echo "OK";

    } catch (Exception $e) {
        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    echo "Método no permitido.";
}
