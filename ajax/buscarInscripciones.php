<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

$conexion = mainModel::conectar();

// Parámetros recibidos
$busqueda = mainModel::limpiar_cadena($_POST['busqueda'] ?? '');
$filtro = mainModel::limpiar_cadena($_POST['filtro'] ?? 'nombre');
$pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$por_pagina = 5;
$inicio = ($pagina - 1) * $por_pagina;

// Mapeo de filtros a columnas - ACTUALIZADO para incluir intensivos
$columnas = [
    'nombre' => "CONCAT(e.nombre, ' ', e.apellido)",
    'apellido' => "e.apellido", 
    'curso' => "c.nombre_curso",
    'tipo' => "c.tipo_curso",
    'seccion' => "s.nombre_seccion",
    'estado' => "i.estado_inscripcion",
    'pago' => "i.pago_faltante",
    'cohorte' => "CONCAT('Cohorte ', COALESCE(co.numero_cohorte, 'Intensivo'), ' - ', COALESCE(co.anio_cohorte, YEAR(pi.fecha_inicio_int)))"
];

// Construcción del WHERE - MODIFICADO para incluir cursos intensivos
$where = "i.archivada = 1
          AND e.estado_estudiante = 1 
          AND s.archivar_seccion = 1
          AND cv.estado_curso_vigente = 1 
          AND c.estado_curso = 1 
          AND (co.archivar_cohorte = 1 OR pi.id_periodo_intensivo IS NOT NULL)"; // Incluye cohortes activos O periodos intensivos
$params = [];

if ($busqueda !== '') {
    if ($filtro === 'pago') {
        $where .= " AND i.pago_faltante IN ('atrasado', 'pendiente')";
    } elseif (isset($columnas[$filtro])) {
        $where .= " AND " . $columnas[$filtro] . " LIKE :busqueda";
        $params[':busqueda'] = "%$busqueda%";
    }
}

// Contar total de resultados - CONSULTA MODIFICADA
$totalConsulta = $conexion->prepare("
    SELECT COUNT(*) 
    FROM inscripcion i
    INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
    INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
    INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk  -- JOIN con periodo_intensivo
    WHERE $where
");
$totalConsulta->execute($params);
$total_resultados = $totalConsulta->fetchColumn();
$total_paginas = ceil($total_resultados / $por_pagina);

// Obtener resultados paginados - CONSULTA MODIFICADA
$consulta = $conexion->prepare("
    SELECT 
        i.id_inscripcion,
        i.fecha_inscripcion,
        i.if_fecha_abandono,
        i.estado_inscripcion,
        i.nota_final,
        i.observaciones,
        i.pago_faltante,
        e.ci_estudiante,
        e.nombre AS nombre_estudiante,
        e.apellido AS apellido_estudiante,
        s.nombre_seccion,
        c.nombre_curso,
        c.tipo_curso,
        -- Campos de cohorte con valores por defecto para intensivos
        COALESCE(co.numero_cohorte, 'Intensivo') AS numero_cohorte,
        COALESCE(co.anio_cohorte, YEAR(pi.fecha_inicio_int)) AS anio_cohorte,
        COALESCE(co.tipo_cohorte, 'Intensivo') AS tipo_cohorte,
        -- Información del periodo intensivo
        pi.fecha_inicio_int,
        pi.fecha_fin_int,
        pi.estado_periodo
    FROM inscripcion i
    INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
    INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
    INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte AND co.archivar_cohorte = 1
    LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk  -- JOIN con periodo_intensivo
    WHERE $where
    ORDER BY i.fecha_inscripcion DESC
    LIMIT :limite OFFSET :inicio
");

// Bind parameters
$consulta->bindValue(':limite', $por_pagina, PDO::PARAM_INT);
$consulta->bindValue(':inicio', $inicio, PDO::PARAM_INT);
foreach ($params as $key => $value) {
    $consulta->bindValue($key, $value);
}

$consulta->execute();
$inscripciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

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
                    
                    // MODIFICADO: Mostrar cohorte o intensivo
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
                    <td><?= htmlspecialchars($ins['nombre_seccion']) ?></td>
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
<!-- PAGINACIÓN MEJORADA -->
<div class="mt-4">
    <nav aria-label="Paginación de inscripciones">
        <ul class="pagination justify-content-center mb-2">
            <!-- Botón Anterior -->
            <?php if ($pagina > 1): ?>
                <li class="page-item">
                    <a class="page-link pagina-link" href="#" data-pagina="<?= $pagina - 1 ?>">
                        <i class="fas fa-chevron-left"></i> Anterior
                    </a>
                </li>
            <?php endif; ?>
            
            <!-- Números de página -->
            <?php 
            $inicio_paginas = max(1, $pagina - 2);
            $fin_paginas = min($total_paginas, $pagina + 2);
            
            for ($i = $inicio_paginas; $i <= $fin_paginas; $i++): ?>
                <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                    <a class="page-link pagina-link" href="#" data-pagina="<?= $i ?>"><?= $i ?></a>
                </li>
            <?php endfor; ?>
            
            <!-- Botón Siguiente -->
            <?php if ($pagina < $total_paginas): ?>
                <li class="page-item">
                    <a class="page-link pagina-link" href="#" data-pagina="<?= $pagina + 1 ?>">
                        Siguiente <i class="fas fa-chevron-right"></i>
                    </a>
                </li>
            <?php endif; ?>
        </ul>
        
        <!-- Información de paginación -->
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