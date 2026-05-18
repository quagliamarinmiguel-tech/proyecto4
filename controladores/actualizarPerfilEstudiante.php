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
if ($action !== 'update_profile') {
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

// Obtener datos del formulario
$email = filter_var($_POST['email'], FILTER_SANITIZE_EMAIL);
$phone = filter_var($_POST['phone'], FILTER_SANITIZE_STRING);
$currentPassword = $_POST['currentPassword'] ?? '';
$newPassword = $_POST['newPassword'] ?? '';

try {
    // Incluir mainModel
    $ruta_modelo = __DIR__ . '/../modelos/mainModel.php';
    if (!file_exists($ruta_modelo)) {
        throw new Exception("No se encuentra mainModel.php en: " . $ruta_modelo);
    }
    require_once $ruta_modelo;
    
    $pdo = mainModel::conectar();
    
    // 1. Verificar contraseña actual usando tu método de encriptación
    $sql = $pdo->prepare("SELECT Password FROM usuario WHERE ID_usuario = :id_usuario");
    $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
    $sql->execute();
    
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);
    
    if (!$usuario) {
        echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
        exit;
    }
    
    // Encriptar la contraseña actual ingresada para comparar con la base de datos
    $currentPasswordEncrypted = mainModel::encryption($currentPassword);
    
    if ($currentPasswordEncrypted !== $usuario['Password']) {
        echo json_encode(['success' => false, 'message' => 'La contraseña actual es incorrecta']);
        exit;
    }
    
    // 2. Actualizar datos del estudiante
    $sql = $pdo->prepare("UPDATE estudiante SET correo_estudiante = :email, telefono_estudiante = :phone WHERE id_estudiante = :id_estudiante");
    $sql->bindParam(":email", $email);
    $sql->bindParam(":phone", $phone);
    $sql->bindParam(":id_estudiante", $id_estudiante, PDO::PARAM_INT);
    
    if ($sql->execute()) {
        // 3. Actualizar contraseña si se proporcionó una nueva
        if (!empty($newPassword)) {
            $newPasswordEncrypted = mainModel::encryption($newPassword);
            $sql = $pdo->prepare("UPDATE usuario SET Password = :clave WHERE ID_usuario = :id_usuario");
            $sql->bindParam(":clave", $newPasswordEncrypted);
            $sql->bindParam(":id_usuario", $id_usuario, PDO::PARAM_INT);
            $sql->execute();
        }
        
        echo json_encode(['success' => true, 'message' => 'Perfil actualizado correctamente']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Error al actualizar el perfil del estudiante']);
    }
    
} catch (Exception $e) {
    // Log del error
    error_log("Error en actualizarPerfilEstudiante: " . $e->getMessage());
    
    // Respuesta de error para el cliente
    echo json_encode([
        'success' => false, 
        'message' => 'Error en el servidor: ' . $e->getMessage()
    ]);
}
?>