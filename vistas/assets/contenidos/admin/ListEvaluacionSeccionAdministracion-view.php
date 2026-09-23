<?php
require_once __DIR__ . "/../../../controladores/ListEvaluacionesSeccionAdministracionControlador.php";
$controlador = new ListEvaluacionesSeccionControlador();

$id_seccion = $_GET['id'] ?? null;
if (!$id_seccion) {
    exit("Sección no especificada.");
}


$nombre_seccion = $controlador->obtener_nombre_seccion($id_seccion);

// Cálculo de paginación
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$resultado = $controlador->paginador_evaluaciones_por_seccion($id_seccion, $pagina_actual, 5);
$evaluaciones = $resultado['datos'];
$total = $resultado['total'];
$Npaginas = $resultado['paginas'];
$inicio = ($pagina_actual > 0) ? (($pagina_actual * 5) - 4) : 1;
$final = $inicio + count($evaluaciones) - 1;

$contexto_exportar = 'evaluaciones_seccion';

?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Evaluaciones de la Sección: <?= $nombre_seccion ?></h2>

    <?php include_once __DIR__ . "/../../../fragmentos/botones_exportar.php"; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="thead-dark">
                <tr class="text-center">
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Objetivo</th>
                    <th>Porcentaje</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluaciones as $index => $ev): ?>
                <tr class="text-center">
                    <td><?= $inicio + $index ?></td>
                    <td><?= $ev['tipo_evaluacion'] ?></td>
                    <td><?= $ev['obj'] ?></td>
                    <td><?= $ev['porcentaje'] ?>%</td>
                    <td>
                        <?= !empty($ev['fecha_evaluacion']) 
                            ? date("d/m/Y", strtotime($ev['fecha_evaluacion'])) 
                            : '—' ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($evaluaciones)): ?>
                <tr>
                    <td colspan="5" class="text-center">No hay evaluaciones registradas para esta sección.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total >= 1 && $Npaginas > 1): ?>
    <p class="text-right">Mostrando evaluaciones <?= $inicio ?> al <?= $final ?> de <?= $total ?></p>
    <nav class="text-center">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for ($i = 1; $i <= $Npaginas; $i++): ?>
            <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                <a class="page-link" href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListEvaluacionSeccionAdministracion&id=<?= $id_seccion ?>&page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
