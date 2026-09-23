<?php
while (ob_get_level()) ob_end_clean();
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/APP.php';
require_once __DIR__ . '/../modelos/mainModel.php';

session_name('SPM');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['cargo_spm']) || (int)$_SESSION['cargo_spm'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
    exit;
}

$profile_id = (int)($_POST['profile_id'] ?? 0);
if (!$profile_id) {
    echo json_encode(['success' => false, 'message' => 'profile_id requerido']);
    exit;
}

try {
    $profileRes = mainModel::supabaseQuery('profile', 'id,user_id,role_id,state', ['id' => 'eq.' . $profile_id]);
    if (empty($profileRes['data'])) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    $profile = $profileRes['data'][0];

    $studentRes = mainModel::supabaseQuery('student', '*', ['profile_id' => 'eq.' . $profile_id]);
    $student = $studentRes['data'][0] ?? null;

    $data = [
        'profile_id' => $profile['id'],
        'user_id'    => $profile['user_id'],
        'role_id'    => $profile['role_id'],
        'state'      => $profile['state'],
        'is_student' => $student !== null,
    ];

    if ($student) {
        // Descifrar campos sensibles
        $fields = ['ci', 'phone', 'email', 'address', 'whatsapp_phone'];
        foreach ($fields as $f) {
            if (!empty($student[$f])) {
                $dec = mainModel::decryption($student[$f]);
                $student[$f] = ($dec !== false) ? $dec : '';
            }
        }
        $data['student'] = $student;
    }

    echo json_encode(['success' => true, 'data' => $data]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}