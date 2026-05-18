<?php
require_once __DIR__ . "/../../../controladores/ListSectionCohorteAdministracionControlador.php";
$controlador = new ListSeccionesCursoControlador();

$id_curso = $_GET['id'] ?? null;
if (!$id_curso) {
    exit("Curso no especificado.");
}

$contexto_exportar = 'secciones_curso';

$nombre_curso = $controlador->obtener_nombre_curso($id_curso);
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

$resultado = $controlador->paginador_secciones_por_curso($id_curso, $pagina_actual, 5);
$secciones = $resultado['datos'];
$total = $resultado['total'];
$Npaginas = $resultado['paginas'];
$inicio = ($pagina_actual > 0) ? (($pagina_actual * 5) - 4) : 1;
$final = $inicio + count($secciones) - 1;

echo "ID Curso: " . $id_curso . "<br>";
echo "Nombre Curso: " . $nombre_curso . "<br>";
echo "Número de secciones: " . count($secciones) . "<br>";
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Secciones del Curso: <?= $nombre_curso ?></h2>

    <?php include_once __DIR__ . "/../../../fragmentos/botones_exportar.php"; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="thead-dark text-center">
                <tr>
                    <th>Nombre de la Sección</th>
                    <th>Profesor</th>
                    <th>Estudiantes</th>
                    <th>Evaluaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($secciones as $sec): ?>
                <tr class="text-center">
                    <td><?= $sec['nombre_seccion'] ?></td>
                    <td><?= $sec['nombre_completo_profesor'] ?? '—' ?></td>
                    <td><?= $sec['cantidad_estudiantes'] ?></td>
                    <td><?= $sec['cantidad_evaluaciones'] ?></td>
                    <td>
                        <a href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListEvaluacionSeccionAdministracion&id=<?= $sec['id_seccion'] ?>" class="btn btn-info btn-sm mb-1">Evaluaciones</a>
                        <a href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListEstudiantesSeccionAdministracion&id=<?= $sec['id_seccion'] ?>" class="btn btn-secondary btn-sm">Estudiantes</a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($secciones)): ?>
                <tr>
                    <td colspan="5" class="text-center">Este curso no tiene secciones registradas.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total >= 1 && $Npaginas > 1): ?>
    <p class="text-right">Mostrando secciones <?= $inicio ?> al <?= $final ?> de <?= $total ?></p>
    <nav class="text-center">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for ($i = 1; $i <= $Npaginas; $i++): ?>
            <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                <a class="page-link" href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListSectionCohorteAdministracion&id=<?= $id_curso ?>&page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
    
        <div class="text-left mt-4">
        <a href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListCursosCohorteAdministracion" class="btn btn-secondary btn-sm">
            ← Volver a lista de cursos
        </a>
    </div>

</div>
