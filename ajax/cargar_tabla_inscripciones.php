<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

$conexion = mainModel::conectar();

// Parámetros recibidos
$busqueda = mainModel::limpiar_cadena($_POST['busqueda'] ?? '');
$filtro = mainModel::limpiar_cadena($_POST['filtro'] ?? 'nombre');
$pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$por_pagina = 10;
$inicio = ($pagina - 1) * $por_pagina;

// Mapeo de filtros a columnas
$columnas = [
    'nombre' => "CONCAT(e.nombre, ' ', e.apellido)",
    'apellido' => "e.apellido", 
    'curso' => "c.nombre_curso",
    'tipo' => "c.tipo_curso",
    'seccion' => "s.nombre_seccion",
    'estado' => "i.estado_inscripcion",
    'pago' => "i.pago_faltante",
    'cohorte' => "CONCAT('Cohorte ', co.numero_cohorte, ' - ', co.anio_cohorte)"
];

// Construcción del WHERE
$where = "i.archivada = 0 
          AND e.estado_estudiante = 1 
          AND s.archivar_seccion = 0 
          AND cv.estado_curso_vigente = 1 
          AND c.estado_curso = 1 
          AND co.archivar_cohorte = 0";
$params = [];

if ($busqueda !== '') {
    if ($filtro === 'pago') {
        $where .= " AND i.pago_faltante IN ('atrasado', 'pendiente')";
    } elseif (isset($columnas[$filtro])) {
        $where .= " AND " . $columnas[$filtro] . " LIKE :busqueda";
        $params[':busqueda'] = "%$busqueda%";
    }
}

// Contar total de resultados
$totalConsulta = $conexion->prepare("
    SELECT COUNT(*) 
    FROM inscripcion i
    INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
    INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
    INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    WHERE $where
");
$totalConsulta->execute($params);
$total_resultados = $totalConsulta->fetchColumn();
$total_paginas = ceil($total_resultados / $por_pagina);

// Obtener resultados paginados
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
        co.numero_cohorte,
        co.anio_cohorte,
        co.tipo_cohorte
    FROM inscripcion i
    INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
    INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
    INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
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

// Generar HTML completo de la tabla
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
                <th>Cohorte</th>
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
                    <td colspan="14" class="text-center">No se encontraron inscripciones.</td>
                </tr>
            <?php else: ?>
                <?php foreach ($inscripciones as $ins): 
                    $pago = strtolower($ins['pago_faltante']);
                    $color = $pago === 'atrasado' ? 'danger' : ($pago === 'pendiente' ? 'warning' : 'success');
                    $fecha_abandono = $ins['if_fecha_abandono'] ? date("d/m/Y", strtotime($ins['if_fecha_abandono'])) : '—';
                    $cohorte_text = $ins['numero_cohorte'] ? "Cohorte {$ins['numero_cohorte']} - {$ins['anio_cohorte']}" : '—';
                ?>
                <tr>
                    <td><?= $ins['ci_estudiante'] ?></td>
                    <td><?= $ins['nombre_estudiante'] . ' ' . $ins['apellido_estudiante'] ?></td>
                    <td><?= $ins['id_inscripcion'] ?></td>
                    <td><?= $ins['nombre_curso'] ?></td>
                    <td><?= $ins['tipo_curso'] ?></td>
                    <td><?= $ins['nombre_seccion'] ?></td>
                    <td><?= $cohorte_text ?></td>
                    <td><?= date("d/m/Y", strtotime($ins['fecha_inscripcion'])) ?></td>
                    <td><?= $fecha_abandono ?></td>
                    <td><?= $ins['estado_inscripcion'] ?></td>
                    <td><?= $ins['nota_final'] ?? '—' ?></td>
                    <td><?= $ins['observaciones'] ?? '—' ?></td>
                    <td>
                        <span class="badge badge-<?= $color ?>"><?= ucfirst($pago) ?></span>
                    </td>
                    <td>
                        <button class="btn btn-info btn-sm btn-transacciones" data-id="<?= $ins['id_inscripcion'] ?>" data-toggle="modal" data-target="#modalTransacciones">
                            Transacciones
                        </button>
                        <button class="btn btn-primary btn-sm btn-modificar" data-id="<?= $ins['id_inscripcion'] ?>" data-toggle="modal" data-target="#modalModificar">
                            Modificar
                        </button>
                        <button class="btn btn-danger btn-sm btn-archivar-inscripcion" data-id="<?= $ins['id_inscripcion'] ?>">
                            Archivar
                        </button>
                    </td>
                </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
</div>

<?php if ($total_paginas > 1): ?>
<nav aria-label="Paginación de inscripciones">
    <ul class="pagination justify-content-center">
        <?php if ($pagina > 1): ?>
            <li class="page-item">
                <a class="page-link pagina-link" href="#" data-pagina="<?= $pagina - 1 ?>">&laquo; Anterior</a>
            </li>
        <?php endif; ?>
        
        <?php for ($i = 1; $i <= $total_paginas; $i++): ?>
            <li class="page-item <?= $i == $pagina ? 'active' : '' ?>">
                <a class="page-link pagina-link" href="#" data-pagina="<?= $i ?>"><?= $i ?></a>
            </li>
        <?php endfor; ?>
        
        <?php if ($pagina < $total_paginas): ?>
            <li class="page-item">
                <a class="page-link pagina-link" href="#" data-pagina="<?= $pagina + 1 ?>">Siguiente &raquo;</a>
            </li>
        <?php endif; ?>
    </ul>
    
    <div class="text-center text-muted mt-2">
        Mostrando <?= (($pagina - 1) * $por_pagina + 1) ?> - <?= min($pagina * $por_pagina, $total_resultados) ?> de <?= $total_resultados ?> inscripciones
    </div>
</nav>
<?php endif; ?>