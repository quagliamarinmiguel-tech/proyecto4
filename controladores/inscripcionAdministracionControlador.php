<?php

error_reporting(0); 
ini_set('display_errors', 0);

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

header('Content-Type: application/json; charset=utf-8');

try {

    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        throw new Exception("Método no permitido.");
    }


    $recaptcha_token = $_POST['recaptcha_token'] ?? '';

    if (empty($recaptcha_token)) {
        throw new Exception("Token de seguridad no proporcionado. Intenta de nuevo.");
    }

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => 'https://www.google.com/recaptcha/api/siteverify',
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => http_build_query([
            'secret'   => '6LfbzLwtAAAAAGTXlH6jrPWHnH7dygwREY7P19Yf',
            'response' => $recaptcha_token,
            'remoteip' => $_SERVER['REMOTE_ADDR'] ?? ''
        ]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_TIMEOUT => 10
    ]);
    $recaptchaResponse = curl_exec($ch);
    $recaptchaHttpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($recaptchaHttpCode !== 200) {
        throw new Exception("No se pudo verificar la seguridad con Google. Intenta de nuevo.");
    }

    $recaptchaResult = json_decode($recaptchaResponse, true);

    if (
        !$recaptchaResult['success'] ||
        ($recaptchaResult['score'] ?? 0) < 0.5 ||
        ($recaptchaResult['action'] ?? '') !== 'submit_inscripcion'
    ) {
        throw new Exception("Verificación de seguridad fallida. Por favor, inténtalo de nuevo.");
    }


    session_name('SPM');
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }

    if (!isset($_SESSION['cargo_spm']) || !in_array($_SESSION['cargo_spm'], [1, 2])) {
        throw new Exception("Acceso denegado. Solo administradores.");
    }

    $serviceToken = SUPABASE_SECRET_KEY;


    $student_id = (int)($_POST['id_estudiante_fk'] ?? 0);
    $section_id = (int)($_POST['id_seccion_fk'] ?? 0);
    $period_course_id = (int)($_POST['period_course_id'] ?? 0);
    $fecha_inscripcion = $_POST['fecha_inscripcion'] ?? date('Y-m-d');
    $fecha_abono = $_POST['fecha_abono'] ?? null;
    $estado_inscripcion = 'En espera';
    $metodo_pago = $_POST['metodo_pago'] ?? '';
    $monto = (float)($_POST['monto'] ?? 0);
    $fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d');

    if (!$student_id || !$section_id || !$period_course_id || !$monto || !$metodo_pago) {
        throw new Exception("Todos los campos son obligatorios.");
    }


    $resultStudent = mainModel::supabaseQuery('student', 'id', ['id' => 'eq.' . $student_id, 'state' => 'eq.1']);
    if (empty($resultStudent['data'])) {
        throw new Exception("Estudiante no válido o inactivo.");
    }

    $resultSection = mainModel::supabaseQuery('section', 'id,period_course_id', ['id' => 'eq.' . $section_id, 'state' => 'eq.1']);
    if (empty($resultSection['data'])) {
        throw new Exception("Sección no válida o inactiva.");
    }
    if ((int)$resultSection['data'][0]['period_course_id'] !== $period_course_id) {
        throw new Exception("La sección no pertenece al curso seleccionado.");
    }


    if ($monto <= 0) {
        throw new Exception("El monto a pagar debe ser mayor a 0.");
    }

    $resultPC = mainModel::supabaseQuery('period_course', 'period_id', ['id' => 'eq.' . $period_course_id]);
    if (empty($resultPC['data'])) {
        throw new Exception("No se encontró el period_course.");
    }
    $period_id = $resultPC['data'][0]['period_id'];

    $resultPrecio = mainModel::supabaseQuery('period_costs', 'price', ['period_id' => 'eq.' . $period_id]);
    if (empty($resultPrecio['data'])) {
        throw new Exception("No se encontró el precio para el periodo.");
    }
    $precioCurso = floatval($resultPrecio['data'][0]['price']);

    if ($monto > $precioCurso) {
        throw new Exception("El monto ingresado ($monto) es mayor al precio a pagar ($precioCurso).");
    }

    $tasaVES = mainModel::obtenerTasaBCV();
    $montoEnBolivares = $monto * $tasaVES;


    $img_path = null;
    if (isset($_FILES['comprobante']) && $_FILES['comprobante']['error'] === UPLOAD_ERR_OK) {
        $fileTmpPath = $_FILES['comprobante']['tmp_name'];
        $fileName = $_FILES['comprobante']['name'];
        $fileExtension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        
        $extensionesPermitidas = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        if (!in_array($fileExtension, $extensionesPermitidas)) {
            throw new Exception("Formato de imagen no permitido. Use JPG, PNG, GIF o WEBP.");
        }

        // Generar un nombre único
        $nombreUnico = 'comprobante_' . time() . '_' . uniqid() . '.' . $fileExtension;
        

        $directorioDestino = $_SERVER['DOCUMENT_ROOT'] . '/comprobantes/pagos/';
        

        if (!is_dir($directorioDestino)) {
            mkdir($directorioDestino, 0755, true);
        }

        $rutaFisicaDestino = $directorioDestino . $nombreUnico;


        if (move_uploaded_file($fileTmpPath, $rutaFisicaDestino)) {

            $img_path = 'comprobantes/pagos/' . $nombreUnico; 
        } else {
            throw new Exception("Error al guardar la imagen en el servidor. Verifica los permisos de la carpeta 'comprobantes/pagos'.");
        }
    }
    


    $registrationData = [
        'student_id' => $student_id,
        'period_course_id' => $period_course_id,
        'section_id' => $section_id,
        'registration_date' => $fecha_inscripcion,
        'abandonment_date' => $fecha_abono,
        'current_state' => $estado_inscripcion,
        'state' => 1,
        'last_qualification' => null,
        'observation' => null,
        'missing_payment' => null,
        'graduand_code' => null
    ];

    $resultRegistration = mainModel::supabasePost('registration', $registrationData, $serviceToken);
    if (!$resultRegistration['ok'] || empty($resultRegistration['data'])) {
        throw new Exception("Error al crear la inscripción: " . ($resultRegistration['message'] ?? ''));
    }

    $registrationId = $resultRegistration['data'][0]['id'] ?? null;


    $transactionData = [
        'student_id' => $student_id,
        'cost' => (int)($monto * 100),
        'payment_method' => $metodo_pago,
        'state' => 1,
        'if_refund' => false,
        'reference' => null,
        'img_route' => $img_path
    ];

    $resultTransaction = mainModel::supabasePost('transaction', $transactionData, $serviceToken);
    if (!$resultTransaction['ok'] || empty($resultTransaction['data'])) {
        mainModel::supabaseDelete('registration', ['id' => 'eq.' . $registrationId], $serviceToken);
        throw new Exception("Error al crear la transacción: " . ($resultTransaction['message'] ?? ''));
    }

    $transactionId = $resultTransaction['data'][0]['id'] ?? null;


    $detailData = [
        'registration_id' => $registrationId,
        'transaction_id' => $transactionId,
        'motive_payment' => 'Inscripción',
        'amount' => (int)($monto * 100),
        'state' => 1
    ];

    $resultDetail = mainModel::supabasePost('transaction_detail', $detailData, $serviceToken);
    if (!$resultDetail['ok']) {
        mainModel::supabaseDelete('transaction', ['id' => 'eq.' . $transactionId], $serviceToken);
        mainModel::supabaseDelete('registration', ['id' => 'eq.' . $registrationId], $serviceToken);
        throw new Exception("Error al crear el detalle: " . ($resultDetail['message'] ?? ''));
    }


    echo json_encode([
        'status' => 'success',
        'message' => '¡Inscripción registrada exitosamente! ID: ' . $registrationId
    ]);
    exit;

} catch (Exception $e) {
    // Respuesta de ERROR (Siempre en formato JSON)
    http_response_code(400);
    echo json_encode([
        'status' => 'error',
        'message' => $e->getMessage()
    ]);
    exit;
}
?>