<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

// 1. Recibimos el ID de la inscripción (como siempre)
$id_inscripcion = (int)($_POST['id_inscripcion'] ?? 0);

if (!$id_inscripcion) {
    echo '<div class="alert alert-danger">ID de inscripción inválido.</div>';
    exit;
}

try {
    // 2. Buscamos a qué estudiante pertenece esta inscripción
    $resultReg = mainModel::supabaseQuery('registration', 'student_id', ['id' => 'eq.' . $id_inscripcion]);
    $student_id = $resultReg['data'][0]['student_id'] ?? 0;

    if (!$student_id) {
        echo '<div class="alert alert-warning">No se encontró el estudiante para esta inscripción.</div>';
        exit;
    }

    // 3. Buscamos TODAS las transacciones de ese estudiante (sin importar la inscripción)
    $select = 'id,created_at,cost,payment_method,img_route,state,transaction_detail(motive_payment,registration_id)';
    
    $resultado = mainModel::supabaseQuery(
        'transaction',
        $select,
        ['student_id' => 'eq.' . $student_id],
        ['order' => 'created_at.desc']
    );
    
    $transacciones = $resultado['data'] ?? [];
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

if (empty($transacciones)) {
    echo '<div class="alert alert-info text-center p-3"><i class="fas fa-info-circle"></i> No hay transacciones registradas para este estudiante.</div>';
    exit;
}

echo '<div class="table-responsive">';
echo '<table class="table table-sm table-bordered table-striped bg-white mb-0">';
echo '<thead class="thead-light"><tr>'
    . '<th>Fecha</th>'
    . '<th>Motivo</th>'
    . '<th>Monto</th>'
    . '<th>Método</th>'
    . '<th>Estado</th>'
    . '<th>Comprobante</th>'
    . '</tr></thead><tbody>';

foreach ($transacciones as $t) {
    $fecha = isset($t['created_at']) ? date("d/m/Y h:i A", strtotime($t['created_at'])) : '—';
    $monto = number_format(($t['cost'] ?? 0) / 100, 2);
    
    // Motivo e inscripción a la que pertenece
    $detalle = $t['transaction_detail'][0] ?? [];
    $motivo = htmlspecialchars($detalle['motive_payment'] ?? '—');
    $id_inscripcion_relacionada = $detalle['registration_id'] ?? '—';
    
    $metodo = htmlspecialchars($t['payment_method'] ?? '—');
    $img_route = $t['img_route'] ?? null;
    
    // Estado
    $estado_num = (int)($t['state'] ?? 1);
    if ($estado_num === 1) {
        $estado_badge = '<span class="badge badge-success">Aprobado</span>';
    } elseif ($estado_num === 2) {
        $estado_badge = '<span class="badge badge-warning">En espera</span>';
    } else {
        $estado_badge = '<span class="badge badge-danger">Rechazado</span>';
    }

    $btnComp = !empty($img_route) 
        ? '<button class="btn btn-sm btn-outline-info btn-ver-comprobante" data-img="'.SERVERURL.$img_route.'"><i class="fas fa-image"></i></button>' 
        : '<span class="text-muted">—</span>';

    echo '<tr>';
    echo '<td><small>' . $fecha . '</small></td>';
    echo '<td>' . $motivo . '<br><small class="text-muted">Inscripción #' . $id_inscripcion_relacionada . '</small></td>';
    echo '<td><strong>$' . $monto . '</strong></td>';
    echo '<td>' . $metodo . '</td>';
    echo '<td>' . $estado_badge . '</td>';
    echo '<td>' . $btnComp . '</td>';
    echo '</tr>';
}

echo '</tbody></table></div>';
?>