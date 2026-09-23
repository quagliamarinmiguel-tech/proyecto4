<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

// Parámetros recibidos
$busqueda = mainModel::limpiar_cadena($_POST['busqueda'] ?? '');
$filtro = mainModel::limpiar_cadena($_POST['filtro'] ?? 'nombre');
$pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$por_pagina = 5;
$inicio = ($pagina - 1) * $por_pagina;

// Mapeo de filtros a columnas de la vista
$columnas = [
    'nombre' => 'nombre_completo',
    'apellido' => 'apellido_estudiante',
    'curso' => 'nombre_curso',
    'tipo' => 'tipo_curso',
    'seccion' => 'nombre_seccion', // si la vista incluye sección
    'estado' => 'estado_inscripcion',
    'pago' => 'pago_faltante',
    'cohorte' => 'numero_cohorte'
];

// Construir filtros para Supabase
$filtros_supabase = [];

if ($busqueda !== '') {
    if ($filtro === 'pago') {
        // Filtrar por pago atrasado o pendiente (ajusta según los valores reales en la vista)
        $filtros_supabase['pago_faltante'] = 'in.(atrasado,pendiente)';
    } elseif (isset($columnas[$filtro])) {
        // Filtro LIKE: PostgREST usa * como comodín
        $col = $columnas[$filtro];
        $filtros_supabase[$col] = 'like.*' . urlencode($busqueda) . '*';
    }
}

// Obtener resultados paginados y total
$resultado = mainModel::supabaseQuery(
    'vista_inscripciones',
    'id_inscripcion,fecha_inscripcion,if_fecha_abandono,estado_inscripcion,nota_final,observaciones,pago_faltante,ci_estudiante,nombre_estudiante,apellido_estudiante,nombre_seccion,nombre_curso,tipo_curso,numero_cohorte,anio_cohorte,tipo_cohorte,fecha_inicio_int,fecha_fin_int,estado_periodo',
    $filtros_supabase,
    [
        'limit' => $por_pagina,
        'offset' => $inicio,
        'order' => 'fecha_inscripcion.desc',
        'count' => true  // pedimos el total
    ]
);

$inscripciones = $resultado['data'] ?? [];
$total_resultados = $resultado['total'] ?? 0;
$total_paginas = ceil($total_resultados / $por_pagina);

// ==============================================
// GENERAR HTML COMPLETO
// ==============================================
?>
<div class="table-responsive">
    <table class="table table-bordered table-hover bg-white">
        <thead class="thead-dark">
            <tr>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>ID Inscripción</th>
                <th>Curso</th>
                <th>Tipo</th>
                <th>Sección</th>
                <th>Cohorte/Intensivo</th>
                <th>Fecha Inscripción</th>
                <th>Fecha Abandono</th>
                <th>Estado</th>
                <th>Nota Final</th>
                <th>Observaciones</th>
                <th>Pago Faltante</th>
                <th>Acciones</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($inscripciones)): ?>
                <tr>
                    <td colspan="14" class="text-center py-4">
                        <i class="fas fa-search fa-2x text-muted mb-3"></i>
                        <p class="text-muted">No se encontraron inscripciones.</p>
                    </td>
                </tr>
            <?php else: ?>
                <?php foreach ($inscripciones as $ins): 
                    $pago = strtolower($ins['pago_faltante'] ?? '');
                    $color = $pago === 'atrasado' ? 'danger' : ($pago === 'pendiente' ? 'warning' : 'success');
                    $fecha_abandono = $ins['if_fecha_abandono'] ? date("d/m/Y", strtotime($ins['if_fecha_abandono'])) : '—';
                    
                    if ($ins['tipo_cohorte'] === 'Intensivo') {
                        $cohorte_text = 'Intensivo';
                        if ($ins['fecha_inicio_int']) {
                            $cohorte_text .= ' (' . date("m/Y", strtotime($ins['fecha_inicio_int'])) . ')';
                        }
                    } else {
                        $cohorte_text = $ins['numero_cohorte'] ? "Cohorte {$ins['numero_cohorte']} - {$ins['anio_cohorte']}" : '—';
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($ins['ci_estudiante']) ?></td>
                    <td><?= htmlspecialchars($ins['nombre_estudiante'] . ' ' . $ins['apellido_estudiante']) ?></td>
                    <td><?= htmlspecialchars($ins['id_inscripcion']) ?></td>
                    <td><?= htmlspecialchars($ins['nombre_curso']) ?></td>
                    <td><?= htmlspecialchars($ins['tipo_curso']) ?></td>
                    <td><?= htmlspecialchars($ins['nombre_seccion'] ?? '—') ?></td>
                    <td>
                        <span class="badge badge-<?= $ins['tipo_cohorte'] === 'Intensivo' ? 'info' : 'primary' ?>">
                            <?= htmlspecialchars($cohorte_text) ?>
                        </span>
                    </td>
                    <td><?= date("d/m/Y", strtotime($ins['fecha_inscripcion'])) ?></td>
                    <td><?= $fecha_abandono ?></td>
                    <td>
                        <span class="badge badge-<?= $ins['estado_inscripcion'] === 'Activa' ? 'success' : ($ins['estado_inscripcion'] === 'Inactiva' ? 'secondary' : 'warning') ?>">
                            <?= htmlspecialchars($ins['estado_inscripcion']) ?>
                        </span>
                    </td>
                    <td><?= $ins['nota_final'] ? htmlspecialchars($ins['nota_final']) : '—' ?></td>
                    <td><?= $ins['observaciones'] ? htmlspecialchars($ins['observaciones']) : '—' ?></td>
                    <td><span class="badge badge-<?= $color ?>"><?= ucfirst($pago) ?></span></td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-transacciones" data-id="<?= $ins['id_inscripcion'] ?>" data-toggle="modal" data-target="#modalTransacciones" title="Ver transacciones">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                            <button class="btn btn-primary btn-modificar" data-id="<?= $ins['id_inscripcion'] ?>" data-toggle="modal" data-target="#modalModificar" title="Modificar">
                                <i class="fas fa-edit"></i>
                            </button>
                            <button class="btn btn-danger btn-archivar-inscripcion" data-id="<?= $ins['id_inscripcion'] ?>" title="Archivar">
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