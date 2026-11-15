<?php
error_reporting(0); 
ini_set('display_errors', 0);

require_once "../config/SERVER.php";
header('Content-Type: application/json; charset=utf-8');

try {
    $id_transaccion = (int)($_POST['id'] ?? 0);
    $accion = $_POST['accion'] ?? '';

    if (!$id_transaccion || !in_array($accion, ['aceptar', 'rechazar'])) {
        throw new Exception("Datos inválidos.");
    }

    $nuevo_estado = ($accion === 'aceptar') ? 1 : 3; 


    $urlTrans = rtrim(SUPABASE_URL, '/') . '/rest/v1/transaction?id=eq.' . $id_transaccion;
    
    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => $urlTrans,
        CURLOPT_CUSTOMREQUEST => 'PATCH',
        CURLOPT_POSTFIELDS => json_encode(['state' => $nuevo_estado]),
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
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {

        $urlDet = rtrim(SUPABASE_URL, '/') . '/rest/v1/transaction_detail?transaction_id=eq.' . $id_transaccion;
        $ch2 = curl_init();
        curl_setopt_array($ch2, [
            CURLOPT_URL => $urlDet,
            CURLOPT_CUSTOMREQUEST => 'PATCH',
            CURLOPT_POSTFIELDS => json_encode(['state' => $nuevo_estado]),
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'apikey: ' . SUPABASE_SECRET_KEY,
                'Authorization: Bearer ' . SUPABASE_SECRET_KEY,
                'Content-Type: application/json'
            ]
        ]);
        curl_exec($ch2);
        curl_close($ch2);

        echo json_encode([
            'status' => 'success',
            'message' => 'Transacción ' . ($accion === 'aceptar' ? 'aceptada' : 'rechazada') . ' correctamente.'
        ]);
    } else {
        throw new Exception("Error de Supabase ($httpCode): " . $response);
    }

} catch (Throwable $e) {
    http_response_code(200); 
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}