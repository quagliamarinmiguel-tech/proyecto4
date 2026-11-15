<?php
// Activar errores
ini_set('display_errors', 1);
error_reporting(E_ALL);

// Registrar todo en un archivo de log
function logDebug($msg) {
    file_put_contents('debug_secciones.log', date('Y-m-d H:i:s') . " - " . $msg . "\n", FILE_APPEND);
}

logDebug("=== INICIO ===");
logDebug("POST: " . print_r($_POST, true));

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

session_name('SPM');
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['cargo_spm']) || !in_array($_SESSION['cargo_spm'], [1, 2])) {
    logDebug("Acceso denegado por rol");
    die('<option value="">Acceso denegado</option>');
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST' || empty($_POST['period_course_id'])) {
    logDebug("No es POST o falta period_course_id");
    die('<option value="">Seleccione un curso primero</option>');
}

$periodCourseId = (int)$_POST['period_course_id'];
logDebug("period_course_id: $periodCourseId");

// Verificar que SUPABASE_SECRET_KEY esté definida
if (!defined('SUPABASE_SECRET_KEY')) {
    logDebug("SUPABASE_SECRET_KEY NO DEFINIDA");
    die('<option value="">Error: Clave de servicio no configurada</option>');
} else {
    logDebug("SUPABASE_SECRET_KEY definida (longitud: " . strlen(SUPABASE_SECRET_KEY) . ")");
}

$serviceToken = SUPABASE_SECRET_KEY;
$filtros = [
    'period_course_id' => 'eq.' . $periodCourseId,
    'state' => 'eq.1'
];

logDebug("Filtros: " . print_r($filtros, true));

try {
    // 1. Intentar con supabaseGet + token
    logDebug("Intentando supabaseGet con token...");
    $result = mainModel::supabaseGet('section', 'id,name,section_day', $filtros, $serviceToken);
    logDebug("Resultado supabaseGet: " . print_r($result, true));
    
    // Verificar si funcionó
    if (isset($result['ok']) && $result['ok'] && !empty($result['data'])) {
        logDebug("Éxito con supabaseGet, datos: " . count($result['data']));
        $secciones = $result['data'];
    } else {
        // 2. Fallback: intentar con supabaseQuery (sin token)
        logDebug("Fallback: intentando supabaseQuery...");
        $result2 = mainModel::supabaseQuery('section', 'id,name,section_day', $filtros);
        logDebug("Resultado supabaseQuery: " . print_r($result2, true));
        
        if (isset($result2['data']) && !empty($result2['data'])) {
            logDebug("Éxito con supabaseQuery, datos: " . count($result2['data']));
            $secciones = $result2['data'];
        } else {
            throw new Exception("No hay secciones disponibles para este curso.");
        }
    }
    
    // Generar HTML
    $html = '<option value="">Seleccione una sección</option>';
    foreach ($secciones as $sec) {
        $nombre = htmlspecialchars($sec['name'] ?? 'Sin nombre');
        $dia = htmlspecialchars($sec['section_day'] ?? '');
        $texto = $nombre . ($dia ? " - $dia" : "");
        $html .= "<option value='{$sec['id']}'>{$texto}</option>";
    }
    logDebug("HTML generado: " . strlen($html) . " caracteres");
    echo $html;

} catch (Exception $e) {
    logDebug("EXCEPCIÓN: " . $e->getMessage());
    echo '<option value="">Error: ' . htmlspecialchars($e->getMessage()) . '</option>';
}
logDebug("=== FIN ===");
?>