<?php

error_reporting(E_ALL); 
ini_set('display_errors', 0);


require_once __DIR__ . '/../config/SERVER.php';

header('Content-Type: application/json; charset=utf-8');

try {
    $id_inscripcion = (int)($_POST['id'] ?? 0);
    $accion = $_POST['accion'] ?? '';

    if (!$id_inscripcion || !in_array($accion, ['aceptar', 'rechazar'])) {
        throw new Exception("Datos inválidos. ID o acción no recibidos.");
    }


    if (!defined('SUPABASE_SECRET_KEY')) {
        throw new Exception("La constante SUPABASE_SECRET_KEY no está definida en config/SERVER.php.");
    }
    

    if (!defined('SUPABASE_URL')) {

        throw new Exception("La constante SUPABASE_URL no está definida en config/SERVER.php.");
    }

    $nuevo_estado = ($accion === 'aceptar') ? 'Activa' : 'Rechazada';


    $url = rtrim(SUPABASE_URL, '/') . '/rest/v1/registration?id=eq.' . $id_inscripcion;
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $url,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => json_encode(['current_state' => $nuevo_estado]),
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_HTTPHEADER => [
            'apikey: ' . SUPABASE_SECRET_KEY,
            'Authorization: Bearer ' . SUPABASE_SECRET_KEY,
            'Content-Type: application/json',
            'Prefer: return=representation'
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $curlError = curl_error($ch);
    curl_close($ch);

    if ($curlError) {
        throw new Exception("Error de conexión cURL: " . $curlError);
    }

    if ($httpCode >= 200 && $httpCode < 300) {
        echo json_encode([
            'status' => 'success',
            'message' => 'Inscripción ' . ($accion === 'aceptar' ? 'aceptada' : 'rechazada') . ' correctamente.'
        ]);
    } else {
        $errData = json_decode($response, true);
        $msg = $errData['message'] ?? $response;
        throw new Exception("Supabase respondió con error ($httpCode): " . $msg);
    }

} catch (Throwable $e) { 

    http_response_code(200); // Devolvemos 200 para que jQuery procese el JSON correctamente
    echo json_encode([
        'status' => 'error', 
        'message' => $e->getMessage(),
        'linea' => $e->getLine()
    ]);
}