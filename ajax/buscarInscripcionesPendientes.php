<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

if (session_status() === PHP_SESSION_NONE) {
    session_name('SPM');
    session_start();
}

require_once __DIR__ . '/../config/APP.php';
require_once __DIR__ . '/../modelos/mainModel.php';

$camposEncriptados = ['ci', 'name', 'last_name'];

try {
    // Consultamos las inscripciones "En espera" y hacemos el JOIN con student y transaction_detail -> transaction
    $select = 'id,current_state,registration_date,student(ci,name,last_name),transaction_detail(transaction(img_route,cost,payment_method))';
    
    // Nota: Usamos el endpoint de registration
    $respuesta = mainModel::supabaseGet('registration', $select, [
        'current_state' => 'eq.En espera',
        'state' => 'eq.1',
        'order' => 'id.desc'
    ]);
    $inscripciones = $respuesta['data'] ?? [];
} catch (Exception $e) {
    echo '<div class="alert alert-danger">Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    exit;
}

if (empty($inscripciones)) {
    echo '<div class="alert alert-info text-center">No hay inscripciones pendientes por revisar.</div>';
    exit;
}

echo '<div class="table-responsive">';
echo '<table class="table table-hover mb-0">';
echo '<thead class="thead-light"><tr>'
    . '<th>ID</th>'
    . '<th>Estudiante</th>'
    . '<th>Fecha Reg.</th>'
    . '<th>Monto</th>'
    . '<th>Comprobante</th>'
    . '<th class="text-center">Acciones</th>'
    . '</tr></thead><tbody>';

foreach ($inscripciones as $ins) {
    // Desencriptar estudiante
    if (!empty($ins['student'])) {
        foreach ($camposEncriptados as $campo) {
            if (!empty($ins['student'][$campo])) {
                $dec = mainModel::decryption($ins['student'][$campo]);
                if ($dec !== false && $dec !== null && $dec !== '') {
                    $ins['student'][$campo] = $dec;
                }
            }
        }
    }

    $nombre = trim(($ins['student']['name'] ?? '') . ' ' . ($ins['student']['last_name'] ?? ''));
    $ci = $ins['student']['ci'] ?? '—';
    
    // Extraer transacción (puede venir en un array)
    $detalles = $ins['transaction_detail'] ?? [];
    $transaccion = $detalles[0]['transaction'] ?? [];
    $img_route = $transaccion['img_route'] ?? null;
    $costo = isset($transaccion['cost']) ? number_format($transaccion['cost'] / 100, 2) : '0.00';

    // Botón de comprobante
    $btnComprobante = $img_route 
        ? '<button class="btn btn-sm btn-outline-info btn-ver-comprobante" data-img="'.SERVERURL.$img_route.'"><i class="fas fa-image"></i> Ver</button>'
        : '<span class="badge badge-warning">Sin imagen</span>';

    echo '<tr id="fila-inscripcion-'.$ins['id'].'">';
    echo '<td>#' . $ins['id'] . '</td>';
    echo '<td>' . htmlspecialchars($nombre) . '<br><small class="text-muted">CI: ' . htmlspecialchars($ci) . '</small></td>';
    echo '<td>' . htmlspecialchars($ins['registration_date'] ?? '') . '</td>';
    echo '<td><strong>$' . $costo . '</strong></td>';
    echo '<td>' . $btnComprobante . '</td>';
    echo '<td class="text-center">'
        . '<button class="btn btn-sm btn-success btn-aceptar" data-id="'.$ins['id'].'"><i class="fas fa-check"></i> Aceptar</button> '
        . '<button class="btn btn-sm btn-danger btn-rechazar" data-id="'.$ins['id'].'"><i class="fas fa-times"></i> Rechazar</button>'
        . '</td>';
    echo '</tr>';
}

echo '</tbody></table></div>';