<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

try {
    // Traemos las transacciones que estén en state=2 y su relación con el estudiante y detalle
    $select = 'id,created_at,cost,payment_method,img_route,student(ci,name,last_name),transaction_detail(motive_payment,registration_id)';
    
    $respuesta = mainModel::supabaseQuery(
        'transaction', 
        $select, 
        ['state' => 'eq.2'], 
        ['order' => 'id.desc']
    );
    
    $transacciones = $respuesta['data'] ?? [];
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

if (empty($transacciones)) {
    echo '<div class="alert alert-info text-center">No hay transacciones pendientes por revisar.</div>';
    exit;
}

// Desencriptar y procesar
$camposEncriptados = ['ci', 'name', 'last_name'];
foreach ($transacciones as &$trans) {
    if (!empty($trans['student'])) {
        foreach ($camposEncriptados as $campo) {
            if (!empty($trans['student'][$campo])) {
                $dec = mainModel::decryption($trans['student'][$campo]);
                if ($dec !== false) $trans['student'][$campo] = $dec;
            }
        }
    }
    $detalle = $trans['transaction_detail'][0] ?? [];
    $trans['motivo'] = $detalle['motive_payment'] ?? 'N/A';
    $trans['id_inscripcion'] = $detalle['registration_id'] ?? 'N/A';
    $trans['monto'] = number_format(($trans['cost'] ?? 0) / 100, 2);
    $trans['fecha'] = date("d/m/Y h:i A", strtotime($trans['created_at']));
}
unset($trans);

echo '<div class="table-responsive">';
echo '<table class="table table-hover mb-0">';
echo '<thead class="thead-light"><tr>'
    . '<th>ID</th>'
    . '<th>Fecha</th>'
    . '<th>Estudiante</th>'
    . '<th>Motivo</th>'
    . '<th>Inscripción</th>'
    . '<th>Monto</th>'
    . '<th>Comprobante</th>'
    . '<th class="text-center">Acciones</th>'
    . '</tr></thead><tbody>';

foreach ($transacciones as $t) {
    $nombre = trim(($t['student']['name'] ?? '') . ' ' . ($t['student']['last_name'] ?? ''));
    $ci = $t['student']['ci'] ?? '—';
    
    $btnComp = !empty($t['img_route']) 
        ? '<button class="btn btn-sm btn-outline-info btn-ver-comprobante" data-img="'.SERVERURL.$t['img_route'].'"><i class="fas fa-image"></i> Ver</button>' 
        : '<span class="badge badge-warning">Sin imagen</span>';

    echo '<tr id="fila-transaccion-'.$t['id'].'">';
    echo '<td>#' . htmlspecialchars($t['id']) . '</td>';
    echo '<td><small>' . $t['fecha'] . '</small></td>';
    echo '<td>' . htmlspecialchars($nombre) . '<br><small class="text-muted">CI: ' . htmlspecialchars($ci) . '</small></td>';
    echo '<td><span class="badge badge-secondary">' . htmlspecialchars($t['motivo']) . '</span></td>';
    echo '<td><small class="text-muted">#' . htmlspecialchars($t['id_inscripcion']) . '</small></td>';
    echo '<td><strong>$' . $t['monto'] . '</strong></td>';
    echo '<td>' . $btnComp . '</td>';
    echo '<td class="text-center">'
        . '<button class="btn btn-sm btn-success btn-aceptar-trans" data-id="'.$t['id'].'"><i class="fas fa-check"></i> Aceptar</button> '
        . '<button class="btn btn-sm btn-danger btn-rechazar-trans" data-id="'.$t['id'].'"><i class="fas fa-times"></i> Rechazar</button>'
        . '</td>';
    echo '</tr>';
}

echo '</tbody></table></div>';