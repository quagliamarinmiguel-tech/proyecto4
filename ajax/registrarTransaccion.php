<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $conexion = mainModel::conectar();

        // Obtener y limpiar datos
        $id_inscripcion = mainModel::limpiar_cadena($_POST['id_inscripcion'] ?? '');
        $monto = mainModel::limpiar_cadena($_POST['monto'] ?? '');
        $fecha_pago = mainModel::limpiar_cadena($_POST['fecha_pago'] ?? '');
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago'] ?? '');
        $motivo_pago = mainModel::limpiar_cadena($_POST['motivo_pago'] ?? '');
        $archivo = $_FILES['comprobante'];

        // Validaciones básicas
        if (
            empty($id_inscripcion) || empty($monto) || empty($fecha_pago) ||
            empty($metodo_pago) || empty($motivo_pago) || empty($archivo['name'])
        ) {
            throw new Exception("Faltan campos obligatorios.");
        }

        // Validar tipo de archivo
        $permitidos = ['image/jpeg', 'image/png', 'image/jpg', 'image/gif', 'image/webp'];
        if (!in_array($archivo['type'], $permitidos)) {
            throw new Exception("Formato de imagen no permitido. Solo JPG, PNG, GIF y WEBP.");
        }

        $monto = floatval($monto);
        if ($monto <= 0) {
            throw new Exception("El monto debe ser mayor a cero.");
        }

        // Guardar comprobante
        $nombreArchivo = uniqid("comprobante_") . "." . pathinfo($archivo['name'], PATHINFO_EXTENSION);
        $rutaDestino = "../comprobantes/" . $nombreArchivo;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaDestino)) {
            throw new Exception("No se pudo guardar el comprobante.");
        }

        // 🔍 OBTENER INFORMACIÓN DEL CURSO (INTENSIVO O NO)
        $consultaCurso = $conexion->prepare("
            SELECT 
                c.tipo_curso,
                co.pago_inscripcion as pago_inscripcion_cohorte,
                co.pago_modulo,
                co.pago_aranceles,
                co.pago_art13,
                pi.pago_inscripcion_int,
                pi.pago_encuentro_int,
                pi.pago_titulo_int,
                pi.id_periodo_intensivo
            FROM inscripcion i
            INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
            INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            WHERE i.id_inscripcion = :id_inscripcion
        ");
        $consultaCurso->execute([':id_inscripcion' => $id_inscripcion]);
        $curso = $consultaCurso->fetch(PDO::FETCH_ASSOC);

        if (!$curso) {
            throw new Exception("No se pudo obtener la información del curso.");
        }

        $tipo_curso = $curso['tipo_curso'];
        $montoRequerido = 0;
        $campoMonto = '';

        // 🎯 DETERMINAR MONTO REQUERIDO SEGÚN TIPO DE CURSO Y MOTIVO
        if ($tipo_curso === 'Intensivo') {
            // Validar motivos para cursos intensivos
            $motivosIntensivos = ['Inscripción', 'Titulación'];
            for ($i = 1; $i <= 10; $i++) {
                $motivosIntensivos[] = "Encuentro $i";
            }
            
            if (!in_array($motivo_pago, $motivosIntensivos)) {
                throw new Exception("Motivo de pago no válido para curso intensivo.");
            }

            switch ($motivo_pago) {
                case 'Inscripción':
                    $campoMonto = 'pago_inscripcion_int';
                    break;
                case 'Titulación':
                    $campoMonto = 'pago_titulo_int';
                    break;
                default:
                    // Para encuentros (Encuentro 1, Encuentro 2, etc.)
                    if (strpos($motivo_pago, 'Encuentro') === 0) {
                        $campoMonto = 'pago_encuentro_int';
                    } else {
                        throw new Exception("Motivo de pago no válido para curso intensivo.");
                    }
                    break;
            }
            
            $montoRequerido = $curso[$campoMonto] ?? 0;
            
        } else {
            // Validar motivos para cursos NO intensivos (Extendido/Técnico Profesional)
            $motivosNoIntensivos = ['Inscripción', 'Módulo 1', 'Módulo 2', 'Módulo 3', 'Aranceles', 'art13'];
            
            if (!in_array($motivo_pago, $motivosNoIntensivos)) {
                throw new Exception("Motivo de pago no válido para este tipo de curso.");
            }

            switch ($motivo_pago) {
                case 'Inscripción': 
                    $campoMonto = 'pago_inscripcion_cohorte';
                    break;
                case 'Módulo 1':
                case 'Módulo 2':
                case 'Módulo 3': 
                    $campoMonto = 'pago_modulo';
                    break;
                case 'Aranceles': 
                    $campoMonto = 'pago_aranceles';
                    break;
                case 'art13': 
                    $campoMonto = 'pago_art13';
                    break;
                default: 
                    throw new Exception("Motivo de pago no válido.");
            }
            
            $montoRequerido = $curso[$campoMonto] ?? 0;
        }

        if ($montoRequerido <= 0) {
            throw new Exception("No se pudo obtener el monto requerido para '$motivo_pago' o el monto está configurado como 0.");
        }

        // 💰 CALCULAR MONTO YA PAGADO PARA ESTE MOTIVO
        $consultaPagos = $conexion->prepare("
            SELECT SUM(monto) as total_pagado 
            FROM transaccion 
            WHERE id_inscripcion = :id 
            AND motivo_pago = :motivo
            AND estatus_pago = 1
        ");
        $consultaPagos->execute([
            ':id' => $id_inscripcion,
            ':motivo' => $motivo_pago
        ]);
        $resultado = $consultaPagos->fetch(PDO::FETCH_ASSOC);
        $montoActual = $resultado['total_pagado'] ?? 0;

        // 🚫 VALIDAR QUE NO EXCEDA EL MONTO REQUERIDO
        if (($montoActual + $monto) > $montoRequerido) {
            $restante = $montoRequerido - $montoActual;
            throw new Exception("El monto excede lo requerido. Ya se pagó \${$montoActual} de \${$montoRequerido} para '$motivo_pago'. Máximo permitido: \${$restante}.");
        }

        // 🧮 CALCULAR PAGO FALTANTE PARA ESTA TRANSACCIÓN
        $pagoFaltanteTransaccion = max(0, $montoRequerido - ($montoActual + $monto));

        // 💾 INSERTAR TRANSACCIÓN
        $insert = $conexion->prepare("
            INSERT INTO transaccion (
                id_inscripcion,
                monto,
                estatus_pago,
                motivo_pago,
                fecha_pago,
                metodo_pago,
                if_pagomovil_imagen,
                pago_faltante
            ) VALUES (
                :id_inscripcion,
                :monto,
                1,
                :motivo_pago,
                :fecha_pago,
                :metodo_pago,
                :imagen,
                :pago_faltante
            )
        ");

        $ok = $insert->execute([
            ':id_inscripcion' => $id_inscripcion,
            ':monto' => $monto,
            ':fecha_pago' => $fecha_pago,
            ':metodo_pago' => $metodo_pago,
            ':motivo_pago' => $motivo_pago,
            ':imagen' => $nombreArchivo,
            ':pago_faltante' => $pagoFaltanteTransaccion
        ]);

        if (!$ok) {
            throw new Exception("Error al registrar la transacción en la base de datos.");
        }

        // ✅ ACTUALIZAR PAGO FALTANTE EN LA INSCRIPCIÓN
        $consultaTotalFaltante = $conexion->prepare("
            SELECT SUM(pago_faltante) as total_faltante 
            FROM transaccion 
            WHERE id_inscripcion = :id_inscripcion
            AND estatus_pago = 1
        ");
        $consultaTotalFaltante->execute([':id_inscripcion' => $id_inscripcion]);
        $totalFaltante = $consultaTotalFaltante->fetchColumn() ?? 0;

        $actualizarInscripcion = $conexion->prepare("
            UPDATE inscripcion 
            SET pago_faltante = :pago_faltante 
            WHERE id_inscripcion = :id_inscripcion
        ");
        $actualizarInscripcion->execute([
            ':pago_faltante' => $totalFaltante,
            ':id_inscripcion' => $id_inscripcion
        ]);

        // 📦 RESPUESTA DE ÉXITO
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'success',
            'mensaje' => 'Transacción registrada correctamente.',
            'data' => [
                'monto_requerido' => $montoRequerido,
                'monto_anterior' => $montoActual,
                'monto_nuevo' => $montoActual + $monto,
                'faltante_restante' => $pagoFaltanteTransaccion
            ]
        ]);

    } catch (Exception $e) {
        // 🗑️ ELIMINAR ARCHIVO SI HUBO ERROR DESPUÉS DE SUBIRLO
        if (isset($nombreArchivo) && file_exists($rutaDestino)) {
            unlink($rutaDestino);
        }

        http_response_code(400);
        header('Content-Type: application/json');
        echo json_encode([
            'status' => 'error',
            'mensaje' => $e->getMessage()
        ]);
    }
} else {
    http_response_code(405);
    header('Content-Type: application/json');
    echo json_encode([
        'status' => 'error',
        'mensaje' => 'Método no permitido.'
    ]);
}
?>