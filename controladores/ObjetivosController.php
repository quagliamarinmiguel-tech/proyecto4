<?php

session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);


header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Cache-Control: no-cache, must-revalidate');


error_log("=== OBJETIVOS CONTROLLER ===");
error_log("Session ID: " . session_id());
error_log("Session Data: " . print_r($_SESSION, true));
error_log("POST Data: " . print_r($_POST, true));
error_log("GET Data: " . print_r($_GET, true));

require_once '../config/conection.php';
require_once '../modelos/ObjetivosModel.php';

try {

    if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
        throw new Exception('No autenticado. Session ID: ' . session_id());
    }

    $id_usuario = $_SESSION['id_usuario'];
    error_log("Buscando estudiante para usuario: " . $id_usuario);


    $stmt = $connect->prepare("SELECT id_estudiante_fk FROM usuario_estudiante WHERE id_usuario_fk = ?");
    if (!$stmt) {
        throw new Exception('Error preparando consulta: ' . $connect->error);
    }
    
    $stmt->bind_param("i", $id_usuario);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();
    $stmt->close();

    if (!$row || empty($row['id_estudiante_fk'])) {
        throw new Exception('Estudiante no encontrado para usuario: ' . $id_usuario);
    }

    $id_estudiante = $row['id_estudiante_fk'];
    error_log("ID Estudiante encontrado: " . $id_estudiante);

    $modelo = new ObjetivosModel($connect);
    $progreso = $modelo->obtenerProgreso($id_estudiante);
    
    error_log("Progreso obtenido: " . print_r($progreso, true));
    
    echo json_encode([
        'success' => true,
        'aprobadas' => $progreso['aprobadas'],
        'pendientes' => $progreso['pendientes'],
        'total' => $progreso['total']
    ]);

} catch (Exception $e) {
    error_log("ERROR en ObjetivosController: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'session_id' => session_id()
    ]);
}
?>