<?php
require_once __DIR__ . "/../controladores/ListInscripcionesAdministracionControlador.php";
$controlador = new ListInscripcionesControlador();

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$por_pagina = 5;
$pagina = isset($_POST['pagina']) ? max(1, intval($_POST['pagina'])) : 1;
$inicio = ($pagina - 1) * $por_pagina;

$inscripciones = $controlador->listar_inscripciones_archivadas($inicio, $por_pagina);
$total = $controlador->contar_inscripciones_archivadas();
$total_paginas = ceil($total / $por_pagina);

// Renderizar tabla
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
      <th>Cohorte</th>
      <th>Fecha Inscripción</th>
      <th>Fecha Abandono</th>
      <th>Estado</th>
      <th>Nota Final</th>
      <th>Observaciones</th>
      <th>Acciones</th>
    </tr>
  </thead>
  <tbody>";

if (empty($inscripciones)) {
    echo "<tr><td colspan='13' class='text-center'>No hay inscripciones archivadas.</td></tr>";
} else {
    foreach ($inscripciones as $ins) {
        $fecha_inscripcion = !empty($ins['fecha_inscripcion'])
            ? date("d/m/Y", strtotime($ins['fecha_inscripcion']))
            : '—';

        $fecha_abandono = !empty($ins['if_fecha_abandono'])
            ? date("d/m/Y", strtotime($ins['if_fecha_abandono']))
            : '—';

        echo "<tr>
            <td>" . ($ins['ci_estudiante'] ?? '—') . "</td>
            <td>" . trim($ins['nombre_estudiante'] ?? '') . " " . trim($ins['apellido_estudiante'] ?? '') . "</td>
            <td>{$ins['id_inscripcion']}</td>
            <td>{$ins['nombre_curso']}</td>
            <td>{$ins['tipo_curso']}</td>
            <td>{$ins['nombre_seccion']}</td>
            <td>Cohorte {$ins['numero_cohorte']} - {$ins['anio_cohorte']}</td>
            <td>{$fecha_inscripcion}</td>
            <td>{$fecha_abandono}</td>
            <td>{$ins['estado_inscripcion']}</td>
            <td>" . ($ins['nota_final'] ?? '—') . "</td>
            <td>" . ($ins['observaciones'] ?? '—') . "</td>
            <td>
              <button class='btn btn-success btn-sm btn-desarchivar-inscripcion' data-id='{$ins['id_inscripcion']}'>
                Desarchivar
              </button>
            </td>
        </tr>";
    }
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
?>
