<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

// Parámetros recibidos
$busqueda = mainModel::limpiar_cadena($_POST['busqueda'] ?? '');
$filtro = mainModel::limpiar_cadena($_POST['filtro'] ?? 'nombre');
$pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$por_pagina = 5;

// 1. Traemos los registros de la tabla 'registration' y sus relaciones
$select = '*,student(ci,name,last_name),section(id,section_day),period_course(id,course(id,name))';

$resultado = mainModel::supabaseQuery(
    'registration',
    $select,
    [], 
    [
        'order' => 'id.desc'
    ]
);

$todas_las_inscripciones = $resultado['data'] ?? [];

// 2. DESENCRIPTAR DATOS Y FILTRAR EN PHP
foreach ($todas_las_inscripciones as &$ins) {
    if (!empty($ins['student'])) {
        // Desencriptar CI
        if (!empty($ins['student']['ci'])) {
            $dec_ci = mainModel::decryption($ins['student']['ci']);
            if ($dec_ci !== false) $ins['student']['ci'] = $dec_ci;
        }
        // Desencriptar Nombre
        if (!empty($ins['student']['name'])) {
            $dec_name = mainModel::decryption($ins['student']['name']);
            if ($dec_name !== false) $ins['student']['name'] = $dec_name;
        }
        // Desencriptar Apellido
        if (!empty($ins['student']['last_name'])) {
            $dec_last = mainModel::decryption($ins['student']['last_name']);
            if ($dec_last !== false) $ins['student']['last_name'] = $dec_last;
        }
    }
}
unset($ins);

// 3. Aplicar filtro de búsqueda
if ($busqueda !== '') {
    $q = mb_strtolower($busqueda);
    $todas_las_inscripciones = array_values(array_filter($todas_las_inscripciones, function($ins) use ($q, $filtro) {
        $nombre = mb_strtolower(trim(($ins['student']['name'] ?? '') . ' ' . ($ins['student']['last_name'] ?? '')));
        $ci = mb_strtolower($ins['student']['ci'] ?? '');
        $curso = mb_strtolower($ins['period_course']['course']['name'] ?? '');
        $estado = mb_strtolower($ins['current_state'] ?? '');
        
        if ($filtro === 'nombre') return mb_strpos($nombre, $q) !== false;
        if ($filtro === 'apellido') return mb_strpos(mb_strtolower($ins['student']['last_name'] ?? ''), $q) !== false;
        if ($filtro === 'curso') return mb_strpos($curso, $q) !== false;
        if ($filtro === 'estado') return mb_strpos($estado, $q) !== false;
        
        // Búsqueda general
        return mb_strpos($nombre, $q) !== false || mb_strpos($ci, $q) !== false || mb_strpos($curso, $q) !== false;
    }));
}

// 4. Paginación
$total_resultados = count($todas_las_inscripciones);
$total_paginas = ceil($total_resultados / $por_pagina);
$inicio = ($pagina - 1) * $por_pagina;
$inscripciones = array_slice($todas_las_inscripciones, $inicio, $por_pagina);


?>
<div class="table-responsive">
    <table class="table table-bordered table-hover bg-white">
        <thead class="thead-dark">
            <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>ID Inscripción</th>
                <th>Curso</th>
                <th>Sección</th>
                <th>Fecha Inscripción</th>
                <th>Estado</th>
                <th>Pago Faltante</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inscripciones)): ?>
                <tr>
                    <td colspan="9" class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No se encontraron inscripciones.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($inscripciones as $ins): 
                    $pago = $ins['missing_payment'] ?? 0;
                    $color = $pago > 0 ? 'danger' : 'success';
                    $fecha = $ins['registration_date'] ? date("d/m/Y", strtotime($ins['registration_date'])) : '—';
                    
                    // Datos relacionados ya desencriptados
                    $ci = $ins['student']['ci'] ?? '—';
                    $nombre = ($ins['student']['name'] ?? '') . ' ' . ($ins['student']['last_name'] ?? '');
                    $curso = $ins['period_course']['course']['name'] ?? '—';
                    $seccion = $ins['section']['section_day'] ?? '—';
                    $estado = $ins['current_state'] ?? 'Sin estado';
                ?>
                <tr>
                    <td><?= htmlspecialchars($ci) ?></td>
                    <td><?= htmlspecialchars(trim($nombre)) ?></td>
                    <td><?= htmlspecialchars($ins['id']) ?></td>
                    <td><?= htmlspecialchars($curso) ?></td>
                    <td><?= htmlspecialchars($seccion) ?></td>
                    <td><?= $fecha ?></td>
                    <td>
                        <span class="badge badge-<?= $estado === 'Activa' ? 'success' : ($estado === 'En espera' ? 'warning' : 'secondary') ?>">
                            <?= htmlspecialchars($estado) ?>
                        </span>
                    </td>
                    <td><span class="badge badge-<?= $color ?>"><?= $pago ? '$'.$pago : 'Pagado' ?></span></td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-transacciones" data-id="<?= $ins['id'] ?>" data-toggle="modal" data-target="#modalTransacciones" title="Ver transacciones">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                            <button class="btn btn-danger btn-archivar-inscripcion" data-id="<?= $ins['id'] ?>" title="Archivar">
                                <i class="fas fa-archive"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_paginas > 1): ?>
<div class="mt-4">
    <nav aria-label="Paginación de inscripciones">
        <ul class="pagination justify-content-center mb-2">
            <?php if ($pagina > 1): ?>
                <li class="page-item">
                    <a class="page-link pagina-link" href="#" data-pagina="<?= $pagina - 1 ?>">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
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
                    <a class="page-link pagina-link" href="#" data-pagina="<?= $pagina + 1 ?>">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <div class="text-center text-muted">
            <small>
                Mostrando <strong><?= (($pagina - 1) * $por_pagina + 1) ?> - <?= min($pagina * $por_pagina, $total_resultados) ?></strong> 
                de <strong><?= $total_resultados ?></strong> inscripciones
                | Página <strong><?= $pagina ?></strong> de <strong><?= $total_paginas ?></strong>
            </small>
        </div>
    </nav>
</div>
<?php endif; ?>