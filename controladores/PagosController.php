<?php
// INICIO ABSOLUTO del archivo
session_start();
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Headers
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: ' . ($_SERVER['HTTP_ORIGIN'] ?? $_SERVER['HTTP_HOST'] ?? '*'));
header('Access-Control-Allow-Credentials: true');
header('Cache-Control: no-cache, must-revalidate');

require_once '../config/conection.php';
require_once '../modelos/PagosModel.php';

try {
    if (!isset($_SESSION['id_usuario']) || empty($_SESSION['id_usuario'])) {
        throw new Exception('No autenticado');
    }

    $id_usuario = $_SESSION['id_usuario'];

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
        throw new Exception('Estudiante no encontrado');
    }

    $id_estudiante = $row['id_estudiante_fk'];
    $modelo = new PagosModel($connect);
    $resumen = $modelo->obtenerResumenPagos($id_estudiante);
    
    echo json_encode([
        'success' => true,
        'pagado' => $resumen['pagado'],
        'faltante' => $resumen['faltante']
    ]);

} catch (Exception $e) {
    error_log("ERROR en PagosController: " . $e->getMessage());
    http_response_code(400);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage()
    ]);
}
?>