<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>


<?php
require_once __DIR__ . "/../../../controladores/ListEvaluacionesGlobalControlador.php";
$controlador = new ListEvaluacionesGlobalControlador();

$secciones = $controlador->obtener_secciones_con_cursos();

// Procesar edición
if (isset($_POST['guardar_edicion'])) {
    $datos = [
        ':tipo' => $_POST['tipo_evaluacion'],
        ':obj' => $_POST['obj'],
        ':porcentaje' => $_POST['porcentaje'],
        ':fecha' => $_POST['fecha_evaluacion'],
        ':id' => $_POST['editar_id']
    ];
    $controlador->actualizar_evaluacion($datos);
    echo "<div class='alert alert-success text-center'>✅ Evaluación actualizada.</div>";
}

// Cargar evaluaciones si se ha seleccionado una sección
$evaluaciones = [];
$total = 0;
$Npaginas = 0;
$inicio = 0;
$final = 0;
$id_seccion = $_POST['seccion'] ?? null;
$pagina_actual = $_POST['page'] ?? 1;

if ($id_seccion) {
    $resultado = $controlador->paginador_evaluaciones_por_seccion($id_seccion, $pagina_actual, 5);
    $evaluaciones = $resultado['datos'];
    $total = $resultado['total'];
    $Npaginas = $resultado['paginas'];
    $inicio = ($pagina_actual > 0) ? (($pagina_actual * 5) - 4) : 1;
    $final = $inicio + count($evaluaciones) - 1;
}
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Consultar Evaluaciones por Sección</h2>

    <form method="post" class="mb-4">
        <div class="form-group">
            <label for="seccion">Seleccione una Sección:</label>
            <select name="seccion" id="seccion" class="form-control" required>
                <option value="">— Seleccione —</option>
                <?php foreach ($secciones as $sec): ?>
                    <option value="<?= $sec['id_seccion'] ?>" <?= ($id_seccion == $sec['id_seccion']) ? 'selected' : '' ?>>
                        <?= $sec['nombre_curso'] ?> - <?= $sec['nombre_seccion'] ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="text-center">
            <button type="submit" name="buscar" class="btn btn-primary">Ver Evaluaciones</button>
        </div>
    </form>

    <?php if (!empty($evaluaciones)): ?>
    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white mt-4">
            <thead class="thead-dark text-center">
                <tr>
                    <th>#</th>
                    <th>Tipo</th>
                    <th>Objetivo</th>
                    <th>Porcentaje</th>
                    <th>Fecha</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($evaluaciones as $index => $ev): ?>
                <tr class="text-center">
                    <td><?= $inicio + $index ?></td>
                    <td><?= $ev['tipo_evaluacion'] ?></td>
                    <td><?= $ev['obj'] ?? '—' ?></td>
                    <td><?= $ev['porcentaje'] ?>%</td>
                    <td>
                        <?= !empty($ev['fecha_evaluacion']) 
                            ? date("d/m/Y", strtotime($ev['fecha_evaluacion'])) 
                            : '—' ?>
                    </td>
                    <td>
                        <button type="button" class="btn btn-warning btn-sm" data-toggle="modal" data-target="#modalEditar<?= $ev['id_evaluacion'] ?>">✏️ Editar</button>
                        <a href="#" class="btn btn-danger btn-sm disabled">🗃️ Archivar</a>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>

    <?php if ($total >= 1 && $Npaginas > 1): ?>
    <p class="text-right">Mostrando evaluaciones <?= $inicio ?> al <?= $final ?> de <?= $total ?></p>
    <form method="post" class="text-center">
        <input type="hidden" name="seccion" value="<?= $id_seccion ?>">
        <div class="btn-group pagination pagination-sm">
            <?php for ($i = 1; $i <= $Npaginas; $i++): ?>
            <button type="submit" name="page" value="<?= $i ?>" class="btn btn-outline-primary btn-sm <?= ($i == $pagina_actual) ? 'active' : '' ?>">
                <?= $i ?>
            </button>
            <?php endfor; ?>
        </div>
    </form>
    <?php endif; ?>
    <?php elseif (isset($_POST['buscar'])): ?>
        <div class="alert alert-warning text-center">No hay evaluaciones para la sección seleccionada.</div>
    <?php endif; ?>
</div>

<?php foreach ($evaluaciones as $ev): ?>
<div class="modal fade" id="modalEditar<?= $ev['id_evaluacion'] ?>" tabindex="-1" role="dialog" aria-labelledby="editarLabel<?= $ev['id_evaluacion'] ?>" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <form method="post" class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editarLabel<?= $ev['id_evaluacion'] ?>">Editar Evaluación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <input type="hidden" name="editar_id" value="<?= $ev['id_evaluacion'] ?>">
                <input type="hidden" name="seccion" value="<?= $id_seccion ?>">
                <input type="hidden" name="page" value="<?= $pagina_actual ?>">

                <div class="form-group">
                    <label>Tipo</label>
                    <input type="text" name="tipo_evaluacion" class="form-control" value="<?= $ev['tipo_evaluacion'] ?>" required>
                </div>

                <div class="form-group">
                    <label>Objetivo</label>
                    <textarea name="obj" class="form-control" required><?= $ev['obj'] ?? '' ?></textarea>
                </div>

                <div class="form-group">
                    <label>Porcentaje</label>
                    <input type="number" name="porcentaje" class="form-control" value="<?= $ev['porcentaje'] ?>" min="1" max="100" required>
                </div>

                <div class="form-group">
                    <label>Fecha</label>
                    <input type="date" name="fecha_evaluacion" class="form-control" value="<?= date('Y-m-d', strtotime($ev['fecha_evaluacion'])) ?>" required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="guardar_edicion" class="btn btn-primary">Guardar Cambios</button>
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
            </div>
        </form>
    </div>
</div>
<?php endforeach; ?>