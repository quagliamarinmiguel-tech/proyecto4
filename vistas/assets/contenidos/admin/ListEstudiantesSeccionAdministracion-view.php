<?php
require_once __DIR__ . "/../../../controladores/ListEstudianteSeccionAdministracionControlador.php";
$controlador = new ListEstudiantesSeccionControlador();

$contexto_exportar = 'estudiantes';


$id_seccion = $_GET['id'] ?? null;
if (!$id_seccion) {
    exit("Sección no especificada.");
}

// Obtener el nombre de la sección
$nombre_seccion = $controlador->obtener_nombre_seccion($id_seccion);

// Configuración de paginación
$pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$resultado = $controlador->paginador_estudiantes_por_seccion($id_seccion, $pagina_actual, 5);
$estudiantes = $resultado['datos'];
$total = $resultado['total'];
$Npaginas = $resultado['paginas'];
$inicio = ($pagina_actual > 0) ? (($pagina_actual * 5) - 4) : 1;
$final = $inicio + count($estudiantes) - 1;

if (isset($_POST['exportar_excel'])) {
    require_once __DIR__ . "/../../../exportadores/exportar_excel_estudiantes.php";
    exportar_excel_estudiantes($estudiantes);
    exit;
}

if (isset($_POST['exportar_pdf'])) {
    require_once __DIR__ . "/../../../exportadores/exportar_pdf_estudiantes.php";
    exportar_pdf_estudiantes($estudiantes);
    exit;
}
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Estudiantes de la Sección: <?= $nombre_seccion ?></h2>
    <?php include_once __DIR__ . "/../../../fragmentos/botones_exportar.php"; ?>


    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="thead-dark text-center">
                <tr>
                    <th>ID</th>
                    <th>Nombre y Apellido</th>
                    <th>Fecha de Nacimiento</th>
                    <th>Teléfono</th>
                    <th>Correo Electrónico</th>
                    <th>Usuario</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($estudiantes as $est): ?>
                <tr class="text-center">
                    <td><?= $est['id_estudiante'] ?></td>
                    <td><?= $est['nombre'] . ' ' . $est['apellido'] ?></td>
                    <td>
                        <?= !empty($est['fecha_nacimiento']) 
                            ? date("d/m/Y", strtotime($est['fecha_nacimiento'])) 
                            : '—' ?>
                    </td>
                    <td><?= $est['telefono_estudiante'] ?></td>
                    <td><?= $est['correo_estudiante'] ?></td>
                    <td><?= $est['nombre_usuario'] ?></td>
                </tr>
                <?php endforeach; ?>
                <?php if (empty($estudiantes)): ?>
                <tr>
                    <td colspan="6" class="text-center">No hay estudiantes inscritos en esta sección.</td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total >= 1 && $Npaginas > 1): ?>
    <p class="text-right">Mostrando estudiantes <?= $inicio ?> al <?= $final ?> de <?= $total ?></p>
    <nav class="text-center">
        <ul class="pagination pagination-sm justify-content-center">
            <?php for ($i = 1; $i <= $Npaginas; $i++): ?>
            <li class="page-item <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                <a class="page-link" href="<?= SERVERURL ?>index.php?views=administrador de pagos/ListEstudiantesSeccionAdministracion&id=<?= $id_seccion ?>&page=<?= $i ?>">
                    <?= $i ?>
                </a>
            </li>
            <?php endfor; ?>
        </ul>
    </nav>
    <?php endif; ?>
</div>
