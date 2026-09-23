<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

$cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [1 => "admin", 2 => "Control de estudio", 3 => "administrador de pagos", 4 => "Supervisor de cursos", 5 => "Estudiante"];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";

$periodCourseId = $_GET['curso_vigente'] ?? '';
require_once "./controladores/periodoControlador.php";
$ctrl = new periodoControlador();
$datos = $ctrl->datos_para_nueva_seccion_controlador($periodCourseId);
if (!$datos['ok']) {
    echo '<div class="alert alert-danger">No se encontró el periodo‑curso.</div>';
    exit();
}
$info = $datos['data'];
$period = $info['period'];
$course = $info['course'];
$profesores = $datos['profesores'] ?? [];
$nextSectionName = $datos['next_section_name'] ?? 'A';
?>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-layer-group fa-fw"></i> &nbsp; NUEVA SECCIÓN</h3>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li><a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-new&period_course_id=' . $periodCourseId; ?>"><i class="fas fa-plus"></i> NUEVA SECCIÓN</a></li>
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-list"></i> LISTA DE PERIODOS</a></li>
    </ul>
</div>

<div class="container-fluid">
    <p><strong>Periodo:</strong> <?= htmlspecialchars($period['name']) ?> (<?= $period['type'] ?>)<br>
    <strong>Curso:</strong> <?= htmlspecialchars($course['name']) ?> (<?= $course['type'] ?>)<br>
    <strong>ID Period-Curso:</strong> <?= $periodCourseId ?></p>

    <form id="form-seccion" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" data-form="save" autocomplete="off">
        <input type="hidden" name="period_course_id" value="<?= $periodCourseId ?>">

        <fieldset>
            <legend><i class="fas fa-info-circle"></i> Datos de la sección</legend>
            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Nombre de sección</label>
                        <input type="text" name="section_name" class="form-control" value="<?= htmlspecialchars($nextSectionName) ?>" readonly required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Día de clases</label>
                        <select name="section_day" class="form-control" required>
                            <option value="" selected disabled>Seleccione...</option>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Profesor</label>

<select name="teacher_id" class="form-control" required>
    <option value="" selected disabled>Seleccione...</option>
    <!--php foreach (($datos['profesores']['data'] ?? []) as $p): ?-->
    <?php foreach (($datos['profesores'] ?? []) as $p): ?>
        <option value="<?= $p['id'] ?>"><?= htmlspecialchars($p['name'] . ' ' . $p['last_name']) ?></option>
    <?php endforeach; ?>
</select>                        
                        
                    </div>
                </div>
            </div>
        </fieldset>

        <p class="text-center">
            <button type="submit" id="btn-submit-seccion" class="btn btn-info btn-sm">
                <i class="far fa-save"></i> GUARDAR SECCIÓN
            </button>
        </p>
    </form>
</div>


<!--script src="<?php echo SERVERURL; ?>vistas/js/miscript.js"></script-->