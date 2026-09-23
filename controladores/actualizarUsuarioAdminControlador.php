<?php
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/APP.php';
require_once __DIR__ . '/../modelos/mainModel.php';
require_once __DIR__ . '/../config/supabaseApi.php';

session_name('SPM');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['cargo_spm']) || (int)$_SESSION['cargo_spm'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$profile_id = (int)($_POST['profile_id'] ?? 0);
$student_id = (int)($_POST['student_id'] ?? 0);
if (!$profile_id) {
    echo json_encode(['success' => false, 'message' => 'profile_id requerido']);
    exit;
}

try {
    // 1. Actualizar estado del profile
    if (isset($_POST['state'])) {
        $state = (int)$_POST['state'];
        $payload = ['state' => $state];
        $res = SupabaseApi::request('PATCH', 'profile', ['id' => 'eq.' . $profile_id], $payload);
        if (!$res['ok']) {
            throw new Exception("Error al actualizar perfil: " . ($res['message'] ?? ''));
        }
    }

    // 2. Si es estudiante, actualizar datos
    if ($student_id) {
        $payload = [];

        // Campos planos
        $plainFields = ['name', 'last_name', 'birthday', 'address', 'birthplace',
                        'education_level', 'current_work'];
        foreach ($plainFields as $f) {
            if (isset($_POST[$f])) {
                $payload[$f] = $_POST[$f] !== '' ? $_POST[$f] : null;
            }
        }
        if (isset($_POST['work'])) {
            $payload['work'] = (int)$_POST['work'] === 1;
        }

        // Campos cifrados (solo si se enviaron con valor)
        $encFields = ['ci', 'phone', 'email', 'whatsapp_phone'];
        foreach ($encFields as $f) {
            if (!empty($_POST[$f])) {
                $payload[$f] = mainModel::encryption($_POST[$f]);
            }
        }

        if (!empty($payload)) {
            $res = SupabaseApi::request('PATCH', 'student', ['id' => 'eq.' . $student_id], $payload);
            if (!$res['ok']) {
                throw new Exception("Error al actualizar estudiante: " . ($res['message'] ?? ''));
            }
        }
    }

    echo json_encode(['success' => true, 'message' => 'Usuario actualizado correctamente']);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}