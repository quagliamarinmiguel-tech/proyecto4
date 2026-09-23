<?php
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/../config/APP.php';
require_once __DIR__ . '/../modelos/mainModel.php';

session_name('SPM');
if (session_status() === PHP_SESSION_NONE) session_start();

if (!isset($_SESSION['cargo_spm']) || (int)$_SESSION['cargo_spm'] !== 1) {
    echo json_encode(['success' => false, 'message' => 'Acceso denegado. Solo administradores.']);
    exit;
}

try {
    // Roles
    $rolesRes = mainModel::supabaseQuery('role', 'id,name');
    $rolesMap = [];
    foreach ($rolesRes['data'] ?? [] as $r) {
        $rolesMap[$r['id']] = $r['name'] ?? ('Rol ' . $r['id']);
    }

    // Profiles (todos los usuarios)
    $profilesRes = mainModel::supabaseQuery('profile', 'id,user_id,role_id,state,created_at');
    $profiles = $profilesRes['data'] ?? [];

    // Students
    $studentsRes = mainModel::supabaseQuery('student', 'id,profile_id,ci,name,last_name,state');
    $studentsByProfile = [];
    foreach ($studentsRes['data'] ?? [] as $s) {
        $studentsByProfile[$s['profile_id']] = $s;
    }

    $users = [];
    foreach ($profiles as $p) {
        $student = $studentsByProfile[$p['id']] ?? null;
        $ciDecoded = '';
        if ($student && !empty($student['ci'])) {
            $dec = mainModel::decryption($student['ci']);
            $ciDecoded = ($dec !== false) ? $dec : '(cifrado)';
        }
        $users[] = [
            'profile_id' => $p['id'],
            'user_id'    => $p['user_id'],
            'role_id'    => $p['role_id'],
            'role_name'  => $rolesMap[$p['role_id']] ?? 'Desconocido',
            'state'      => $p['state'],
            'student_id' => $student['id'] ?? null,
            'name'       => $student['name'] ?? '',
            'last_name'  => $student['last_name'] ?? '',
            'ci'         => $ciDecoded,
            'is_student' => $student !== null
        ];
    }

    echo json_encode(['success' => true, 'data' => $users]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}