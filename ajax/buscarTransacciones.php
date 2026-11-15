<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

// Parámetros recibidos
$busqueda = mainModel::limpiar_cadena($_POST['busqueda'] ?? '');
$filtro = mainModel::limpiar_cadena($_POST['filtro'] ?? 'todos');
$pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$por_pagina = 10;

// 1. Traemos los registros de la tabla 'transaction' con sus relaciones
$select = 'id,created_at,cost,payment_method,reference,img_route,student(ci,name,last_name),transaction_detail(motive_payment,registration_id)';

$resultado = mainModel::supabaseQuery(
    'transaction',
    $select,
    [],
    [
        'order' => 'id.desc'
    ]
);

$todas_las_transacciones = $resultado['data'] ?? [];

// 2. DESENCRIPTAR DATOS Y FILTRAR EN PHP
foreach ($todas_las_transacciones as &$trans) {
    if (!empty($trans['student'])) {
        foreach (['ci', 'name', 'last_name'] as $campo) {
            if (!empty($trans['student'][$campo])) {
                $dec = mainModel::decryption($trans['student'][$campo]);
                if ($dec !== false) $trans['student'][$campo] = $dec;
            }
        }
    }
    // Procesar el detalle (solo tomamos el primero, ya que una transacción puede tener varios ítems)
    $detalle = $trans['transaction_detail'][0] ?? [];
    $trans['motivo'] = $detalle['motive_payment'] ?? 'No especificado';
    $trans['id_inscripcion'] = $detalle['registration_id'] ?? 'N/A';
    
    // Formatear monto (de céntimos a dólares)
    $trans['monto'] = number_format(($trans['cost'] ?? 0) / 100, 2);
}
unset($trans);

// 3. Aplicar filtro de búsqueda
if ($busqueda !== '') {
    $q = mb_strtolower($busqueda);
    $todas_las_transacciones = array_values(array_filter($todas_las_transacciones, function($trans) use ($q, $filtro) {
        $nombre = mb_strtolower(trim(($trans['student']['name'] ?? '') . ' ' . ($trans['student']['last_name'] ?? '')));
        $ci = mb_strtolower($trans['student']['ci'] ?? '');
        $motivo = mb_strtolower($trans['motivo'] ?? '');
        $referencia = mb_strtolower($trans['reference'] ?? '');
        
        if ($filtro === 'estudiante') return mb_strpos($nombre, $q) !== false || mb_strpos($ci, $q) !== false;
        if ($filtro === 'motivo') return mb_strpos($motivo, $q) !== false;
        if ($filtro === 'referencia') return mb_strpos($referencia, $q) !== false;
        
        // Filtro "todos"
        return mb_strpos($nombre, $q) !== false 
            || mb_strpos($ci, $q) !== false 
            || mb_strpos($motivo, $q) !== false
            || mb_strpos($referencia, $q) !== false;
    }));
}

// 4. Paginación en PHP
$total_resultados = count($todas_las_transacciones);
$total_paginas = ceil($total_resultados / $por_pagina);
$inicio = ($pagina - 1) * $por_pagina;
$transacciones = array_slice($todas_las_transacciones, $inicio, $por_pagina);

?>
<div class="table-responsive">
    <table class="table table-bordered table-hover bg-white">
        <thead class="thead-dark">
            <tr>
                <th>ID</th>
                <th>Fecha</th>
                <th>Estudiante</th>
                <th>CI</th>
                <th>Motivo</th>
                <th>Inscripción</th>
                <th>Monto</th>
                <th>Método</th>
                <th>Comprobante</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($transacciones)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No se encontraron transacciones.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($transacciones as $t): 
                    $fecha = date("d/m/Y h:i A", strtotime($t['created_at']));
                    $nombre = trim(($t['student']['name'] ?? '') . ' ' . ($t['student']['last_name'] ?? ''));
                    $ci = $t['student']['ci'] ?? '—';
                    
                    // Botón de comprobante
                    if (!empty($t['img_route'])) {
                        $urlImg = SERVERURL . $t['img_route'];
                        $btnComp = '<button class="btn btn-sm btn-outline-info btn-ver-comprobante" data-img="'.$urlImg.'" title="Ver comprobante"><i class="fas fa-image"></i> Ver</button>';
                    } else {
                        $btnComp = '<span class="badge badge-secondary">Sin imagen</span>';
                    }
                ?>
                <tr>
                    <td>#<?= htmlspecialchars($t['id']) ?></td>
                    <td><small><?= $fecha ?></small></td>
                    <td><?= htmlspecialchars($nombre) ?></td>
                    <td><?= htmlspecialchars($ci) ?></td>
                    <td><span class="badge badge-info"><?= htmlspecialchars($t['motivo']) ?></span></td>
                    <td><small class="text-muted">#<?= htmlspecialchars($t['id_inscripcion']) ?></small></td>
                    <td><span class="text-success font-weight-bold">$<?= $t['monto'] ?></span></td>
                    <td><?= htmlspecialchars($t['payment_method']) ?></td>
                    <td><?= $btnComp ?></td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_paginas > 1): ?>
<div class="mt-4">
    <nav aria-label="Paginación de transacciones">
        <ul class="pagination justify-content-center mb-2">
            <?php if ($pagina > 1): ?>
                <li class="page-item">
                    <a class="page-link pagina-link" href="#" data-pagina="<?= $pagina - 1 ?>">Anterior</a>
                </li>
            <?php endif; ?>
            
            <?php 
            $inicio_paginas = max(1, $pagina - 2);
            $fin_paginas = min($total_paginas, $pagina + 2);
            for ($i = $inicio_paginas; $i <= $fin_paginas; $i++): ?>
                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                    <a class="page-link pagina-link" href="#" data-pagina="<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            
            <?php if ($pagina < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link pagina-link" href="#" data-pagina="<?= $pagina + 1 ?>">Siguiente</a>
                </li>
            <?php endif; ?>
        </ul>
        <div class="text-center text-muted">
            <small>
                Mostrando <?= (($pagina - 1) * $por_pagina + 1) ?> - <?= min($pagina * $por_pagina, $total_resultados) ?> 
                de <?= $total_resultados ?> transacciones
            </small>
        </div>
    </nav>
</div>
<?php endif; ?>