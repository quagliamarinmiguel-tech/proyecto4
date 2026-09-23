<?php
// Activar errores para depuración
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

header('Content-Type: text/html; charset=utf-8');


// --------------------------------------------
// 0. VERIFICAR reCAPTCHA v3
// --------------------------------------------
$recaptcha_token = $_POST['recaptcha_token'] ?? '';

if (empty($recaptcha_token)) {
    throw new Exception("Token de seguridad no proporcionado. Intenta de nuevo.");
}

// Verificación con Google (usando cURL, sin librería externa)
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
    throw new Exception("No se pudo verificar la seguridad. Intenta de nuevo.");
}

$recaptchaResult = json_decode($recaptchaResponse, true);

// Validar: éxito, puntaje aceptable (>=0.5) y acción correcta
if (
    !$recaptchaResult['success'] ||
    ($recaptchaResult['score'] ?? 0) < 0.5 ||
    ($recaptchaResult['action'] ?? '') !== 'submit_inscripcion'
) {
    error_log("Fallo reCAPTCHA: " . print_r($recaptchaResult, true));
    throw new Exception("Verificación de seguridad fallida. Por favor, inténtalo de nuevo.");
}

// Si llegamos aquí, el reCAPTCHA fue válido. Continúa con tu lógica existente...

// --------------------------------------------
// 1. VERIFICAR SESIÓN Y ROL
// --------------------------------------------
session_name('SPM');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}


if (!isset($_SESSION['cargo_spm']) || !in_array($_SESSION['cargo_spm'], [1, 2])) {
    die("<div class='alert alert-danger'>Acceso denegado. Solo administradores.</div>");
}

$serviceToken = SUPABASE_SECRET_KEY;

// --------------------------------------------
// 2. PROCESAR SOLO POST
// --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<div class='alert alert-danger'>Método no permitido.</div>");
}

error_log("=== SERVICE TOKEN ===");
error_log(defined('SUPABASE_SECRET_KEY') ? 'Definido' : 'NO DEFINIDO');
error_log(substr(SUPABASE_SECRET_KEY, 0, 20) . '...'); // Muestra los primeros 20 caracteres para verificar

try {
   // --------------------------------------------
// 3. RECIBIR Y VALIDAR DATOS
// --------------------------------------------
$student_id = (int)($_POST['id_estudiante_fk'] ?? 0);
$section_id = (int)($_POST['id_seccion_fk'] ?? 0);
$period_course_id = (int)($_POST['period_course_id'] ?? 0);
$fecha_inscripcion = $_POST['fecha_inscripcion'] ?? date('Y-m-d');
$fecha_abono = $_POST['fecha_abono'] ?? null;
$estado_inscripcion = $_POST['estado_inscripcion'] ?? 'Activa';
$metodo_pago = $_POST['metodo_pago'] ?? '';
$monto = (float)($_POST['monto'] ?? 0);
$fecha_pago = $_POST['fecha_pago'] ?? date('Y-m-d');

// Validar campos obligatorios
if (!$student_id || !$section_id || !$period_course_id || !$monto || !$metodo_pago) {
    throw new Exception("Todos los campos son obligatorios.");
}

// 🔍 Depuración: ver qué llega
error_log("=== DATOS RECIBIDOS ===");
error_log("student_id: $student_id");
error_log("section_id: $section_id");
error_log("period_course_id: $period_course_id");

// Validar que el estudiante exista y esté activo
$filtrosStudent = [
    'id' => 'eq.' . $student_id,
    'state' => 'eq.1'
];
$resultStudent = mainModel::supabaseQuery('student', 'id', ['id' => 'eq.' . $student_id, 'state' => 'eq.1']);

// 🔍 Depuración del resultado
error_log("=== RESULTADO STUDENT ===");
error_log("Resultado student: " . print_r($resultStudent, true));

// Verificar si hay datos
if (empty($resultStudent['data'])) {
    throw new Exception("Estudiante no válido o inactivo. ID: " . $student_id);
}
// Si hay error, también lanzar excepción
if (isset($resultStudent['error'])) {
    throw new Exception("Error al consultar estudiante: " . $resultStudent['error']);
}

// Validar que la sección exista y esté activa
$filtrosSection = [
    'id' => 'eq.' . $section_id,
    'state' => 'eq.1'
];
// 2. Validar sección - CON DEPURACIÓN COMPLETA
error_log("=== VALIDANDO SECCIÓN ID: $section_id ===");

// Primero, consultar sin filtro de state para ver si la sección existe
$resultSectionSinState = mainModel::supabaseQuery('section', 'id,period_course_id,state,section_day', ['id' => 'eq.' . $section_id]);
error_log("SECCIÓN SIN STATE FILTER: " . print_r($resultSectionSinState, true));

// Luego, consultar con filtro de state (como antes)
$resultSection = mainModel::supabaseQuery('section', 'id,period_course_id', ['id' => 'eq.' . $section_id, 'state' => 'eq.1']);
error_log("SECCIÓN CON STATE=1: " . print_r($resultSection, true));

// Verificar si hay datos en la consulta con state
if (isset($resultSection['error']) || empty($resultSection['data'])) {
    // Si no hay datos, mostramos la sección sin state para depurar
    if (!empty($resultSectionSinState['data'])) {
        $seccionData = $resultSectionSinState['data'][0];
        $estadoActual = $seccionData['state'] ?? 'desconocido';
        $periodCourseId = $seccionData['period_course_id'] ?? 'sin period';
        throw new Exception("Sección no válida o inactiva. ID: $section_id. Estado actual: $estadoActual. period_course_id: $periodCourseId. (Debe ser state=1 y period_course_id=$period_course_id)");
    } else {
        throw new Exception("Sección no válida o inactiva. ID: $section_id. No existe en la tabla.");
    }
}
$sectionData = $resultSection['data'][0];

// Verificar que la sección pertenezca al period_course_id enviado
if ((int)$sectionData['period_course_id'] !== $period_course_id) {
    throw new Exception("La sección no pertenece al curso seleccionado. Sección period_course_id: " . $sectionData['period_course_id'] . " vs enviado: " . $period_course_id);
}
    
## A continuacion la validacion para que el monto no sea mayor al precio que hay que pagar
if ($monto <= 0) {
    throw new Exception("El monto a pagar debe ser mayor a 0.");
}

// 1. Obtener el period_id a partir del period_course_id
$resultPC = mainModel::supabaseQuery(
    'period_course',
    'period_id',
    ['id' => 'eq.' . $period_course_id]
);

error_log("=== RESULTADO period_course ===");
error_log(print_r($resultPC, true));

if (isset($resultPC['error']) || empty($resultPC['data'])) {
    throw new Exception("No se encontró el period_course con ID: $period_course_id");
}

$period_id = $resultPC['data'][0]['period_id'] ?? null;

if (!$period_id) {
    throw new Exception("El period_course no tiene period_id válido.");
}

// 2. Buscar el precio en period_costs usando el period_id
$resultPrecio = mainModel::supabaseQuery(
    'period_costs',
    'price',
    ['period_id' => 'eq.' . $period_id]
);

error_log("=== RESULTADO period_costs ===");
error_log(print_r($resultPrecio, true));

if (isset($resultPrecio['error']) || empty($resultPrecio['data'])) {
    throw new Exception("No se encontró el precio para el periodo ID: $period_id");
}

$precioCurso = floatval($resultPrecio['data'][0]['price'] ?? 0);

if ($precioCurso <= 0) {
    throw new Exception("El precio del curso es inválido o es 0 (period_id: $period_id).");
}

// 3. Validar que el monto no sea mayor
if ($monto > $precioCurso) {
    throw new Exception("El monto ingresado ($monto) es mayor al precio a pagar ($precioCurso).");
}
	## Aqui termina la validacion del monto > precio

    // --------------------------------------------
    // 4. CREAR LA INSCRIPCIÓN (registration)
    // --------------------------------------------
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
        'missing_payment' => null,   // <-- CAMBIO: null en lugar de 0
        'graduand_code' => null
    ];

    $resultRegistration = mainModel::supabasePost('registration', $registrationData, $serviceToken);
    if (!$resultRegistration['ok'] || empty($resultRegistration['data'])) {
        throw new Exception("Error al crear la inscripción: " . ($resultRegistration['message'] ?? ''));
    }

    $registrationId = $resultRegistration['data'][0]['id'] ?? null;
    if (!$registrationId) {
        throw new Exception("No se pudo obtener el ID de la inscripción.");
    }

    // --------------------------------------------
    // 5. CREAR LA TRANSACCIÓN (transaction)
    // --------------------------------------------
    $transactionData = [
        'student_id' => $student_id,
        'cost' => (int)($monto * 100), // Guardamos en céntimos (smallint)
        'payment_method' => $metodo_pago,
        'state' => 1,
        'if_refund' => false,
        'reference' => null,
        'img_id' => null // Por ahora sin imagen
    ];

    $resultTransaction = mainModel::supabasePost('transaction', $transactionData, $serviceToken);
    if (!$resultTransaction['ok'] || empty($resultTransaction['data'])) {
        // Rollback: eliminar la inscripción creada
        mainModel::supabaseDelete('registration', ['id' => 'eq.' . $registrationId], $serviceToken);
        throw new Exception("Error al crear la transacción: " . ($resultTransaction['message'] ?? ''));
    }

    $transactionId = $resultTransaction['data'][0]['id'] ?? null;
    if (!$transactionId) {
        mainModel::supabaseDelete('registration', ['id' => 'eq.' . $registrationId], $serviceToken);
        throw new Exception("No se pudo obtener el ID de la transacción.");
    }

    // --------------------------------------------
    // 6. CREAR EL DETALLE DE TRANSACCIÓN (transaction_detail)
    // --------------------------------------------
    $detailData = [
        'registration_id' => $registrationId,
        'transaction_id' => $transactionId,
        'motive_payment' => 'Inscripción',
        'amount' => (int)($monto * 100),
        'state' => 1
    ];

    $resultDetail = mainModel::supabasePost('transaction_detail', $detailData, $serviceToken);
    if (!$resultDetail['ok']) {
        // Rollback: eliminar transacción e inscripción
        mainModel::supabaseDelete('transaction', ['id' => 'eq.' . $transactionId], $serviceToken);
        mainModel::supabaseDelete('registration', ['id' => 'eq.' . $registrationId], $serviceToken);
        throw new Exception("Error al crear el detalle: " . ($resultDetail['message'] ?? ''));
    }

    // --------------------------------------------
    // 7. ÉXITO
    // --------------------------------------------
    echo "<div class='alert alert-success text-center'>
            <i class='bi bi-check-circle-fill'></i> ¡Inscripción registrada exitosamente!<br>
            <strong>ID Inscripción:</strong> {$registrationId}
          </div>";

} catch (Exception $e) {
    $errorMsg = htmlspecialchars($e->getMessage());
    error_log("Error en inscripción: " . $errorMsg);
    echo "<div class='alert alert-danger text-center'>
            <i class='bi bi-exclamation-triangle-fill'></i> Error: {$errorMsg}
          </div>";
}
?>