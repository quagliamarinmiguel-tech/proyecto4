<?php
// Activar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

header('Content-Type: text/html; charset=utf-8');

// --------------------------------------------
// 1. VERIFICAR SESIÓN (CON EL NOMBRE CORRECTO)
// --------------------------------------------
session_name('SPM');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cargo_spm'])) {
    die("<div class='alert alert-danger'>Debes iniciar sesión para acceder a esta función.</div>");
}

$role = $_SESSION['cargo_spm'];
if (!in_array($role, [1, 2])) {
    die("<div class='alert alert-danger'>Acceso denegado. Se requiere rol de administrador o personal administrativo.</div>");
}

// Token de servicio para operaciones administrativas
$serviceToken = SUPABASE_SECRET_KEY;

// --------------------------------------------
// 2. PROCESAR SOLO POST
// --------------------------------------------
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    die("<div class='alert alert-danger'>Acceso no permitido.</div>");
}

// --------------------------------------------
// 3. OBTENER Y LIMPIAR DATOS DEL FORMULARIO
// --------------------------------------------
$ci_estudiante          = trim($_POST['ci'] ?? '');
$nombre                 = trim($_POST['nombre'] ?? '');
$apellido               = trim($_POST['apellido'] ?? '');
$fecha_nac              = $_POST['fecha_nacimiento'] ?? '';
$lugar_nacimiento       = trim($_POST['lugar_nacimiento'] ?? '');
$telefono_estudiante    = trim($_POST['telefono_estudiante'] ?? '');
$correo_estudiante      = trim($_POST['correo_estudiante'] ?? '');
$wsp_estudiante         = trim($_POST['wsp_estudiante'] ?? '');
$direccion_estudiante   = trim($_POST['direccion_estudiante'] ?? '');
$nivel_estudio_estudiante = trim($_POST['nivel_estudio_estudiante'] ?? '');
$oficio_actual_estudiante = trim($_POST['oficio_actual_estudiante'] ?? '');
$if_trabaja_estudiante  = isset($_POST['if_trabaja_estudiante']) ? (int)$_POST['if_trabaja_estudiante'] : 0;
$usuario                = trim($_POST['usuario'] ?? '');
$clave                  = $_POST['clave'] ?? '';
$clave2                 = $_POST['clave2'] ?? '';

// --------------------------------------------
// 4. VALIDACIONES (iguala a las que ya tenías)
// --------------------------------------------
try {
    if (empty($ci_estudiante) || empty($nombre) || empty($apellido) || empty($fecha_nac) || 
        empty($telefono_estudiante) || empty($correo_estudiante) || empty($usuario) || empty($clave)) {
        throw new Exception("Todos los campos obligatorios (*) deben ser completados.");
    }

    if (!preg_match('/^[VE]\d{8}$/', $ci_estudiante)) {
        throw new Exception("La cédula debe tener el formato V-12345678 o E-12345678.");
    }

    if (!preg_match('/^[A-Za-záéíóúñÑ\s]{2,100}$/', $nombre)) {
        throw new Exception("El nombre solo puede contener letras y espacios (2-100 caracteres).");
    }
    if (!preg_match('/^[A-Za-záéíóúñÑ\s]{2,100}$/', $apellido)) {
        throw new Exception("El apellido solo puede contener letras y espacios (2-100 caracteres).");
    }

    if (strlen($telefono_estudiante) !== 11) {
        throw new Exception("El teléfono debe tener 11 dígitos.");
    }
    $codigos_permitidos = ['0412','0414','0416','0422','0424','0426','58412','58414','58416','58422','58424','58426'];
    if (!in_array(substr($telefono_estudiante, 0, 4), $codigos_permitidos)) {
        throw new Exception("Código de teléfono no válido.");
    }

    if (!empty($wsp_estudiante) && strlen($wsp_estudiante) !== 12) {
        throw new Exception("El WhatsApp debe tener 12 dígitos.");
    }
    if (!empty($wsp_estudiante) && !in_array(substr($wsp_estudiante, 0, 5), $codigos_permitidos)) {
        throw new Exception("Código de WhatsApp no válido.");
    }

    if (!filter_var($correo_estudiante, FILTER_VALIDATE_EMAIL)) {
        throw new Exception("El correo electrónico no es válido.");
    }

    if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $clave)) {
        throw new Exception("La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número.");
    }
    if ($clave !== $clave2) {
        throw new Exception("Las contraseñas no coinciden.");
    }

    $fecha_nac_obj = new DateTime($fecha_nac);
    $hoy = new DateTime();
    $fecha_minima = (new DateTime())->modify('-6 years');
    if ($fecha_nac_obj > $hoy) {
        throw new Exception("La fecha de nacimiento no puede ser futura.");
    }
    if ($fecha_nac_obj > $fecha_minima) {
        throw new Exception("El estudiante debe tener al menos 6 años de edad.");
    }

    if (!in_array($if_trabaja_estudiante, [0, 1])) {
        $if_trabaja_estudiante = 0;
    }

    // --------------------------------------------
    // 5. VERIFICAR DUPLICADOS (vía Supabase)
    // --------------------------------------------
    $serviceToken = SUPABASE_SECRET_KEY;

    // Cédula en student
    $resultCI = mainModel::verificar_cedula_duplicada_supabase($ci_estudiante, 'student', $serviceToken);
    if ($resultCI['ok'] && !empty($resultCI['data'])) {
        throw new Exception("La cédula ya está registrada.");
    }

    // Correo en student
    $resultEmail = mainModel::verificar_correo_duplicado_supabase($correo_estudiante, 'student', $serviceToken);
    if ($resultEmail['ok'] && !empty($resultEmail['data'])) {
        throw new Exception("El correo electrónico ya está registrado.");
    }
	
    echo $correo_estudiante;
    // Correo en auth.users
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => SUPABASE_URL . '/auth/v1/admin/users?filter=email=eq.' . urlencode($correo_estudiante),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_SECRET_KEY,
            'Authorization: Bearer ' . SUPABASE_SECRET_KEY
        ]
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    if ($httpCode == 200) {
        $users = json_decode($response, true);
        if (isset($users['users']) && count($users['users']) > 0) {
            throw new Exception("El correo electrónico ya está registrado en el sistema de autenticación.");
        }
    } else {
        throw new Exception("Error al verificar el correo en autenticación.");
    }

    // --------------------------------------------
    // 6. CREAR USUARIO EN AUTH.USERS
    // --------------------------------------------
    $payload = json_encode([
        'email' => $correo_estudiante,
        'password' => $clave,
        'email_confirm' => true
    ]);

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => SUPABASE_URL . '/auth/v1/admin/users',
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => $payload,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_SECRET_KEY,
            'Authorization: Bearer ' . SUPABASE_SECRET_KEY,
            'Content-Type: application/json'
        ]
    ]);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    if ($httpCode < 200 || $httpCode >= 300) {
        throw new Exception("No se pudo crear el usuario: " . $response);
    }

    $userData = json_decode($response, true);
    if (!isset($userData['id'])) {
        throw new Exception("La respuesta de creación no contiene ID.");
    }
    $userId = $userData['id'];

    // --------------------------------------------
    // 7. INSERTAR EN PROFILE
    // --------------------------------------------
    $profileData = [
        'user_id'    => $userId,
        'role_id'    => 5,
        'state'      => 1
    ];

    $resultProfile = mainModel::supabasePost('profile', $profileData, $serviceToken);
    if (!$resultProfile['ok']) {
        eliminarUsuarioAuth($userId);
        throw new Exception("Error al crear el perfil: " . ($resultProfile['message'] ?? ''));
    }

    // Obtener profile.id
    $profileInserted = $resultProfile['data'][0] ?? null;
    if (!$profileInserted || !isset($profileInserted['id'])) {
        eliminarUsuarioAuth($userId);
        throw new Exception("No se pudo obtener el ID del perfil.");
    }
    $profileId = $profileInserted['id'];

    // --------------------------------------------
    // 8. CIFRAR Y INSERTAR EN STUDENT
    // --------------------------------------------
    $ci_cifrado       = mainModel::encryption($ci_estudiante);
    $telefono_cifrado = mainModel::encryption($telefono_estudiante);
    $correo_cifrado   = mainModel::encryption($correo_estudiante);
    $direccion_cifrada = mainModel::encryption($direccion_estudiante);
    $wsp_cifrado       = mainModel::encryption($wsp_estudiante);

    $studentData = [
        'profile_id'        => $profileId,
        'ci'                => $ci_cifrado,
        'name'              => $nombre,
        'last_name'         => $apellido,
        'birthday'          => $fecha_nac,
        'phone'             => $telefono_cifrado,
        'email'             => $correo_cifrado,
        'address'           => $direccion_cifrada,
        'birthplace'        => $lugar_nacimiento,
        'education_level'   => $nivel_estudio_estudiante,
        'work'              => $if_trabaja_estudiante == 1,
        'current_work'      => $oficio_actual_estudiante,
        'whatsapp_phone'    => $wsp_cifrado,
        'state'             => 1
    ];

    $resultStudent = mainModel::supabasePost('student', $studentData, $serviceToken);
    if (!$resultStudent['ok']) {
        // Rollback
        mainModel::supabaseDelete('profile', ['id' => 'eq.' . $profileId]);
        eliminarUsuarioAuth($userId);
        throw new Exception("Error al crear el estudiante: " . ($resultStudent['message'] ?? ''));
    }

   // --------------------------------------------
    // 9. ÉXITO
    // --------------------------------------------
    $trabaja_texto = $if_trabaja_estudiante == 1 ? 'Sí' : 'No';
    echo "<div class='alert alert-success text-center mt-3'>
            <i class='bi bi-check-circle-fill'></i> ¡Registro exitoso!<br>
            <strong>Estudiante:</strong> {$nombre} {$apellido}<br>
            <strong>Cédula:</strong> {$ci_estudiante}<br>
            <strong>Usuario:</strong> {$usuario}<br>
            <strong>Correo:</strong> {$correo_estudiante}<br>
            <strong>Teléfono:</strong> {$telefono_estudiante}<br>
            <strong>WhatsApp:</strong> " . ($wsp_estudiante ?: 'No registrado') . "<br>
            <strong>Dirección:</strong> " . ($direccion_estudiante ?: 'No registrada') . "<br>
            <strong>Lugar de nacimiento:</strong> " . ($lugar_nacimiento ?: 'No registrado') . "<br>
            <strong>Nivel de estudio:</strong> " . ($nivel_estudio_estudiante ?: 'No registrado') . "<br>
            <strong>Oficio actual:</strong> " . ($oficio_actual_estudiante ?: 'No registrado') . "<br>
            <strong>Trabaja actualmente:</strong> {$trabaja_texto}
          </div>";

} catch (Exception $e) {
    $mensajeError = htmlspecialchars($e->getMessage());
    error_log("Error en registro: " . $mensajeError);
    echo "<div class='alert alert-danger text-center mt-3'>
            <i class='bi bi-exclamation-triangle-fill'></i> Error: {$mensajeError}
          </div>";
}

// --------------------------------------------
// FUNCIÓN AUXILIAR: eliminar usuario de Auth
// --------------------------------------------
function eliminarUsuarioAuth($userId) {
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => SUPABASE_URL . '/auth/v1/admin/users/' . $userId,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => 'DELETE',
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_SECRET_KEY,
            'Authorization: Bearer ' . SUPABASE_SECRET_KEY
        ]
    ]);
    curl_exec($ch);
    curl_close($ch);
}
?>