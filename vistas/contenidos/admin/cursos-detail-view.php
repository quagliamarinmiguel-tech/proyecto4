<?php
    
$idEnc = $_GET['id'] ?? '';
$marketingId = (int) mainModel::decryption($idEnc);
if ($marketingId <= 0) {
    // Redirigir o mostrar error
}

// Obtener datos del curso usando $marketingId
$detalle = cursoPublicidadModelo::obtener_marketing_curso_supabase($marketingId, null); // o RPC pública
?>
