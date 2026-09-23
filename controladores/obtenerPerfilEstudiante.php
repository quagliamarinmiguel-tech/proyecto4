<?php
while (ob_get_level()) { ob_end_clean(); }

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

header("Access-Control-Allow-Origin: http://www.cecilioceproca.wuaze.com");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

$action        = $_POST['action'] ?? '';
$id_estudiante = $_POST['id_estudiante'] ?? null;
$cargo_spm     = $_POST['cargo_spm'] ?? null;

if ($action !== 'get_profile') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida']);
    exit;
}

if (!$id_estudiante || !$cargo_spm) {
    echo json_encode(['success' => false, 'message' => 'Datos de sesión incompletos']);
    exit;
}

if ($cargo_spm != 5) {
    echo json_encode(['success' => false, 'message' => 'No tienes permisos de estudiante']);
    exit;
}

try {
    require_once __DIR__ . '/../modelos/mainModel.php';

    // 🎯 Buscar estudiante SIN token (funciona sin RLS)
    $resStudent = mainModel::supabaseQuery(
        'student',
        'id,name,last_name,email,phone',
        ['id' => 'eq.' . $id_estudiante, 'state' => 'eq.1']
    );

    if (empty($resStudent['data']) || !is_array($resStudent['data'])) {
        echo json_encode([
            'success' => false,
            'message' => 'Estudiante no encontrado'
        ]);
        exit;
    }

    $student = $resStudent['data'][0];

    // Descifrar email y phone
    $emailDescifrado = '';
    $phoneDescifrado = '';

    if (!empty($student['email'])) {
        $tmp = mainModel::decryption($student['email']);
        $emailDescifrado = ($tmp !== false) ? $tmp : $student['email'];
    }

    if (!empty($student['phone'])) {
        $tmp = mainModel::decryption($student['phone']);
        $phoneDescifrado = ($tmp !== false) ? $tmp : $student['phone'];
    }

    echo json_encode([
        'success' => true,
        'data' => [
            'id_estudiante' => $student['id'],
            'nombre' => $student['name'],
            'apellido' => $student['last_name'],
            'correo_estudiante' => $emailDescifrado,
            'telefono_estudiante' => $phoneDescifrado
        ]
    ]);

} catch (Exception $e) {
    error_log("Error en obtenerPerfilEstudiante: " . $e->getMessage());
    echo json_encode([
        'success' => false,
        'message' => 'Error: ' . $e->getMessage()
    ]);
}