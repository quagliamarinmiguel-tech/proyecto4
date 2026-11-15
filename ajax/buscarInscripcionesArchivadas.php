<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

$conexion = mainModel::conectar();

// Parámetros recibidos
$busqueda = mainModel::limpiar_cadena($_POST['busqueda'] ?? '');
$filtro = mainModel::limpiar_cadena($_POST['filtro'] ?? 'nombre');
$por_pagina = 5;
$pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$inicio = ($pagina - 1) * $por_pagina;

// Mapeo de filtros a columnas
$columnas = [
    'nombre' => "CONCAT(e.nombre, ' ', e.apellido)",
    'apellido' => "e.apellido",
    'curso' => "c.nombre_curso",
    'tipo' => "c.tipo_curso",
    'seccion' => "s.nombre_seccion",
    'estado' => "i.estado_inscripcion",
    'pago' => "i.pago_faltante"
];

// Construcción del WHERE
$where = "i.archivada = 0";
$params = [];

if ($busqueda !== '') {
    if ($filtro === 'pago') {
        $where .= " AND i.pago_faltante != 0";
    } elseif (isset($columnas[$filtro])) {
        $where .= " AND " . $columnas[$filtro] . " LIKE :busqueda";
        $params[':busqueda'] = "%$busqueda%";
    }
}

// Contar total de resultados
$totalConsulta = $conexion->prepare("
    SELECT COUNT(*) FROM inscripcion i
    INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
    INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
    INNER JOIN curso c ON s.id_curso_vigente_fk = c.id_curso
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
        c.tipo_curso
    FROM inscripcion i
    INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
    INNER JOIN seccion s ON i.id_seccion_fk = s.id_seccion
    INNER JOIN curso c ON s.id_curso_vigente_fk = c.id_curso
    WHERE $where
    ORDER BY i.fecha_inscripcion DESC
    LIMIT $por_pagina OFFSET $inicio
");
$consulta->execute($params);
$inscripciones = $consulta->fetchAll(PDO::FETCH_ASSOC);

// Mostrar resultados
if (empty($inscripciones)) {
    echo "<p class='text-center'>No se encontraron resultados.</p>";
    exit;
}

echo "<div class='table-responsive'>
<table class='table table-bordered table-hover bg-white'>
  <thead class='thead-dark'>
    <tr>
      <th>Cédula</th>
      <th>Nombre</th>
      <th>ID Inscripción</th>
      <th>Curso</th>
      <th>Tipo</th>
      <th>Sección</th>
      <th>Fecha Inscripción</th>
      <th>Fecha Abandono</th>
      <th>Estado</th>
      <th>Nota Final</th>
      <th>Observaciones</th>
      <th>Pago Faltante</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>";

    foreach ($inscripciones as $ins) {
        $pago = strtolower($ins['pago_faltante'] ?? '');
        $color = $pago === 'atrasado' ? 'danger' : ($pago === 'pendiente' ? 'warning' : 'success');
        $fecha_abandono = $ins['if_fecha_abandono'] ? date("d/m/Y", strtotime($ins['if_fecha_abandono'])) : '—';

        echo "<tr>
            <td>{$ins['ci_estudiante']}</td>
            <td>{$ins['nombre_estudiante']} {$ins['apellido_estudiante']}</td>
            <td>{$ins['id_inscripcion']}</td>
            <td>{$ins['nombre_curso']}</td>
            <td>{$ins['tipo_curso']}</td>
            <td>{$ins['nombre_seccion']}</td>
            <td>" . date("d/m/Y", strtotime($ins['fecha_inscripcion'])) . "</td>
            <td>{$fecha_abandono}</td>
            <td>{$ins['estado_inscripcion']}</td>
            <td>" . ($ins['nota_final'] ?? '—') . "</td>
            <td>" . ($ins['observaciones'] ?? '—') . "</td>
            <td><span class='badge badge-{$color}'>" . ucfirst($pago) . "</span></td>
            <td>
                <button class='btn btn-info btn-sm btn-transacciones' data-id='{$ins['id_inscripcion']}' data-toggle='modal' data-target='#modalTransacciones'>Transacciones</button>
                <button class='btn btn-primary btn-sm btn-modificar' data-id='{$ins['id_inscripcion']}' data-toggle='modal' data-target='#modalModificar'>Modificar</button>
                <button class='btn btn-danger btn-archivar-inscripcion' data-id='{$ins['id_inscripcion']}'>Archivar</button>
            </td>
        </tr>";
    }

echo "</tbody></table></div>";

// Paginación
if ($total_paginas > 1) {
    echo "<nav><ul class='pagination justify-content-center'>";
    for ($i = 1; $i <= $total_paginas; $i++) {
        echo "<li class='page-item " . ($i == $pagina ? 'active' : '') . "'>
                <a href='#' class='page-link pagina-link-archivadas' data-pagina='{$i}'>{$i}</a>
              </li>";
    }
    echo "</ul></nav>";
}