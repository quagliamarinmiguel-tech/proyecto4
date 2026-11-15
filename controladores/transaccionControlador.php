<?php
error_reporting(0); 
ini_set('display_errors', 0);

require_once "../config/SERVER.php";
require_once "../modelos/mainModel.php";

header('Content-Type: application/json; charset=utf-8');

try {
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido.");
    }

    session_name('SPM');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['cargo_spm']) || !in_array($_SESSION['cargo_spm'], [1, 2, 3])) {
        throw new Exception("Acceso denegado.");
    }

    $serviceToken = SUPABASE_SECRET_KEY;

    // 1. Recibir datos
    $id_inscripcion = (int)($_POST['id_inscripcion'] ?? 0);
    $motivo_pago    = $_POST['motivo_pago'] ?? '';
    $monto          = (float)($_POST['monto'] ?? 0);
    $metodo_pago    = $_POST['metodo_pago'] ?? '';
    
    if (!$id_inscripcion || !$motivo_pago || $monto <= 0 || !$metodo_pago) {
        throw new Exception("Todos los campos son obligatorios.");
    }

    // 2. Validar que la inscripción exista y obtener el student_id
    $resultReg = mainModel::supabaseQuery('registration', 'id,student_id,period_course_id', ['id' => 'eq.' . $id_inscripcion]);
    if (empty($resultReg['data'])) {
        throw new Exception("La inscripción no existe.");
    }
    $student_id = $resultReg['data'][0]['student_id'];
    $period_course_id = $resultReg['data'][0]['period_course_id'];

    // 3. VALIDAR QUE EL MONTO NO SUPERE EL PRECIO DE LA TARIFA
    $resultPC = mainModel::supabaseQuery('period_course', 'period_id', ['id' => 'eq.' . $period_course_id]);
    $period_id = $resultPC['data'][0]['period_id'] ?? 0;

    if ($period_id) {
        $resultCosts = mainModel::supabaseQuery('period_costs', 'cost_description,price', ['period_id' => 'eq.' . $period_id]);
        $costs = $resultCosts['data'] ?? [];
        
        $precioMaximo = null;
        $motivo_lower = strtolower($motivo_pago);

        foreach ($costs as $c) {
            $desc = strtolower(trim($c['cost_description']));
            
            if (strpos($motivo_lower, 'modulo') !== false && strpos($desc, 'modulo') !== false) {
                $precioMaximo = (float)$c['price']; break;
            } elseif (strpos($motivo_lower, 'arancel') !== false && strpos($desc, 'arancel') !== false) {
                $precioMaximo = (float)$c['price']; break;
            } elseif ((strpos($motivo_lower, 'art') !== false || strpos($motivo_lower, '13') !== false) && (strpos($desc, 'art') !== false || strpos($desc, '13') !== false)) {
                $precioMaximo = (float)$c['price']; break;
            } elseif (strpos($motivo_lower, 'inscripcion') !== false && strpos($desc, 'inscripcion') !== false) {
                $precioMaximo = (float)$c['price']; break;
            } elseif (strpos($motivo_lower, 'encuentro') !== false && strpos($desc, 'encuentro') !== false) {
                $precioMaximo = (float)$c['price']; break;
            } elseif ((strpos($motivo_lower, 'titulo') !== false || strpos($motivo_lower, 'titulacion') !== false) && (strpos($desc, 'titulo') !== false || strpos($desc, 'titulacion') !== false)) {
                $precioMaximo = (float)$c['price']; break;
            }
        }

        if ($precioMaximo !== null && $monto > $precioMaximo) {
            throw new Exception("El monto ingresado ($monto) no puede ser mayor al costo de la tarifa ($precioMaximo).");
        }
    }

    // 4. Procesar la imagen (hosting local)
    $img_path = null;
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['comprobante']['tmp_name'];
        $fileName = $_FILES['comprobante']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExtension, $extensionesPermitidas)) {
            throw new Exception("Formato de imagen no permitido.");
        }

        $nombreUnico = 'transaccion_' . time() . '_' . uniqid() . '.' . $fileExtension;
        $directorioDestino = $_SERVER['DOCUMENT_ROOT'] . '/comprobantes/pagos/';
        
        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }

        $rutaFisicaDestino = $directorioDestino . $nombreUnico;

        if (move_uploaded_file($fileTmpPath, $rutaFisicaDestino)) {
            $img_path = 'comprobantes/pagos/' . $nombreUnico; 
        } else {
            throw new Exception("Error al guardar la imagen en el servidor.");
        }
    }

    // 5. Crear la transacción
    $transactionData = [
        'student_id' => $student_id,
        'cost' => (int)($monto * 100), // En céntimos
        'payment_method' => $metodo_pago,
        'state' => 2, // 🚨 ESTADO 2 (EN ESPERA)
        'if_refund' => false,
        'reference' => null,
        'img_route' => $img_path
    ];

    $resultTransaction = mainModel::supabasePost('transaction', $transactionData, $serviceToken);
    if (!$resultTransaction['ok'] || empty($resultTransaction['data'])) {
        throw new Exception("Error al crear la transacción: " . ($resultTransaction['message'] ?? ''));
    }

    $transactionId = $resultTransaction['data'][0]['id'] ?? null;

    // 6. Crear el detalle de la transacción
    $detailData = [
        'registration_id' => $id_inscripcion,
        'transaction_id' => $transactionId,
        'motive_payment' => $motivo_pago,
        'amount' => (int)($monto * 100),
        'state' => 2 // 🚨 ESTADO 2 (EN ESPERA)
    ];

    $resultDetail = mainModel::supabasePost('transaction_detail', $detailData, $serviceToken);
    if (!$resultDetail['ok']) {
        // Rollback
        mainModel::supabaseDelete('transaction', ['id' => 'eq.' . $transactionId], $serviceToken);
        throw new Exception("Error al crear el detalle: " . ($resultDetail['message'] ?? ''));
    }

    echo json_encode([
        'status' => 'success',
        'message' => 'Transacción registrada exitosamente. Queda en espera de aprobación.'
    ]);
    exit;

} catch (Exception $e) {
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}