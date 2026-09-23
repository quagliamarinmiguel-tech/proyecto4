<?php
require_once __DIR__ . "/../../../controladores/ListCoursesCohorteAdministracionControlador.php";
$controlador = new ListCursosCohorteControlador();

$id_cohorte = $_GET['id'] ?? null;
if (!$id_cohorte) {
    exit("Cohorte no especificado.");
}

$contexto_exportar = 'cursos_cohorte';

$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Obtener datos del cohorte
$cohorte = $controlador->obtener_nombre_cohorte($id_cohorte);
if (!$cohorte) {
    echo "<div class='alert alert-warning text-center'>No se encontró el cohorte especificado.</div>";
    return;
}

// Cursos paginados
$resultado = $controlador->paginador_cursos_por_cohorte($id_cohorte, $pagina_actual, 5);
$cursos = $resultado['datos'];
$total = $resultado['total'];
$Npaginas = $resultado['paginas'];
$inicio = ($pagina_actual > 0) ? (($pagina_actual * 5) - 4) : 1;
$final = $inicio + count($cursos) - 1;

?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">
        Cursos del Cohorte <?= $cohorte['tipo_cohorte'] ?> <?= $cohorte['numero_cohorte'] ?>/<?= $cohorte['anio_cohorte'] ?>
    </h2>

    <?php include_once __DIR__ . "/../../../fragmentos/botones_exportar.php"; ?>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="thead-dark">
                <tr class="text-center">
                    <th>Nombre del Curso</th>
                    <th>Tipo</th>
                    <th>Secciones Afiliadas</th>
                    <th>Acción</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($cursos as $curso): ?>
                <tr class="text-center">
                    <td><?= $curso['nombre_curso'] ?></td>
                    <td><?= $curso['tipo_curso'] ?></td>
                    <td><?= $curso['cantidad_secciones'] ?></td>
                    <td>
                        <a href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListSectionCohorteAdministracion&id=<?= $curso['id_curso'] ?>" class="btn btn-info btn-sm">
                            Ver Secciones
                        </a>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($cursos)): ?>
                <tr>
                    <td colspan="4" class="text-center">Este cohorte no tiene cursos asignados.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if($total >= 1 && $Npaginas > 1): ?>
    <p class="text-right">Mostrando cursos <?= $inicio ?> al <?= $final ?> de <?= $total ?></p>
    <nav class="text-center">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for($i = 1; $i <= $Npaginas; $i++): ?>
            <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                <a class="page-link" href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListCursosCohorteAdministracion&id=<?= $id_cohorte ?>&page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>

    <div class="text-left mt-4">
        <a href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListCohorteAdministracion" class="btn btn-secondary btn-sm">
            ← Volver a lista de cohortes
        </a>
    </div>
</div>
