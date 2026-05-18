<?php
// Limpiar buffer de salida
while (ob_get_level()) {
    ob_end_clean();
}

error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);

// HEADERS CORS - PRIMERO
header("Access-Control-Allow-Origin: http://www.cecilioceproca.wuaze.com");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
    http_response_code(200);
    exit();
}

// Recibir datos por POST
$action = $_POST['action'] ?? '';
$id_usuario = $_POST['id_usuario'] ?? null;
$id_estudiante = $_POST['id_estudiante'] ?? null;
$cargo_spm = $_POST['cargo_spm'] ?? null;

// Verificar que tenemos todos los datos necesarios
if ($action !== 'get_profile') {
    echo json_encode(['success' => false, 'message' => 'Acción no válida: ' . $action]);
    exit;
}

if (!$id_usuario || !$id_estudiante || !$cargo_spm) {
    echo json_encode([
        'success' => false, 
        'message' => 'Datos de sesión incompletos'
    ]);
    exit;
}

// Verificar permisos
if ($cargo_spm != 5) {
    echo json_encode([
        'success' => false, 
        'message' => 'No tienes permisos de estudiante. Rol: ' . $cargo_spm
    ]);
    exit;
}

try {
    // Incluir mainModel
    $ruta_modelo = __DIR__ . '/../modelos/mainModel.php';
    if (!file_exists($ruta_modelo)) {
        throw new Exception("No se encuentra mainModel.php en: " . $ruta_modelo);
    }
    require_once $ruta_modelo;
    
    $pdo = mainModel::conectar();
    
    // Obtener datos del estudiante
    $sql = $pdo->prepare("SELECT correo_estudiante, telefono_estudiante FROM estudiante WHERE id_estudiante = :id_estudiante");
    $sql->bindParam(":id_estudiante", $id_estudiante, PDO::PARAM_INT);
    $sql->execute();
    
    $estudiante = $sql->fetch(PDO::FETCH_ASSOC);
    
    if ($estudiante) {
        echo json_encode([
            'success' => true, 
            'data' => [
                'correo_estudiante' => $estudiante['correo_estudiante'],
                'telefono_estudiante' => $estudiante['telefono_estudiante']
            ]
        ]);
    } else {
        echo json_encode(['success' => false, 'message' => 'Estudiante no encontrado en la base de datos']);
    }
    
} catch (Exception $e) {
    // Log del error (para debugging del servidor)
    error_log("Error en obtenerPerfilEstudiante: " . $e->getMessage());
    
    // Respuesta de error para el cliente
    echo json_encode([
        'success' => false, 
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}
?>