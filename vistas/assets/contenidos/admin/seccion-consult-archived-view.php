<?php

if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4 && $_SESSION['cargo_spm'] != 2) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

$periodCourseId = isset($_GET['curso_vigente']) ? (int) $_GET['curso_vigente'] : 0;
$sectionLetter = isset($_GET['seccion']) ? $_GET['seccion'] : '';

if ($periodCourseId == 0 || $sectionLetter == '') {
    echo '<div class="alert alert-danger text-center">Parámetros inválidos. No se puede cargar la sección.</div>';
    exit();
}

require_once "./controladores/periodoControlador.php";
$ctrl = new periodoControlador();

// Datos principales de la sección (periodo, curso, profesor, conteos)
$datos = $ctrl->obtener_datos_para_seccion_consulta($periodCourseId, $sectionLetter);
if (!$datos['ok']) {
    echo '<div class="alert alert-danger text-center">' . htmlspecialchars($datos['message']) . '</div>';
    exit();
}

// Extraer variables para la vista
$seccion = $datos['seccion'];
$period = $datos['period'];
$course = $datos['course'];
$teacher = $datos['teacher'];
$evaluacionesCount = $datos['evaluaciones_count'];
$totalPorcentaje = $datos['total_porcentaje'];
$profesores = $datos['profesores']; // array plano de profesores para el select


$cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
$mostrarExportar = ($totalPorcentaje == 100.0);
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-users fa-fw"></i> &nbsp; SECCIÓN ARCHIVADA "<?php echo strtoupper($sectionLetter); ?>"
    </h3>
    <p class="text-justify">
        Detalles de la sección  archivada y evaluaciones registradas.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS REGULARES
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list'; ?>">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS INTENSIVOS
            </a>
        </li>
    </ul>
</div>

<!-- ============================================================
     DATOS GENERALES DE LA SECCIÓN
     ============================================================ -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-md-6">
            <p><strong>ID de sección:</strong> <?php echo $seccion['id']; ?></p>
            <p><strong>Curso:</strong> <?php echo htmlspecialchars($course['name'] ?? ''); ?></p>
            <p><strong>ID del periodo del curso:</strong> <?php echo $periodCourseId; ?></p>
            <p><strong>Periodo:</strong> <?php echo htmlspecialchars($period['name'] ?? ''); ?></p>
            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($period['type'] ?? ''); ?></p>
            <p><strong>Fechas:</strong> <?php echo $period['start_date'] ?? ''; ?> al <?php echo $period['end_date'] ?? ''; ?></p>
            <p><strong>Día:</strong> <?php echo $seccion['section_day']; ?></p>
            <p><strong>Profesor:</strong> <?php echo $teacher ? htmlspecialchars($teacher['name'] . ' ' . $teacher['last_name']) : 'Sin asignar'; ?></p>
        </div>

        <?php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
        <div class="col-md-6">
            <!-- Formulario para modificar profesor y día ->
            <form action="<c?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" class="FormularioAjax" autocomplete="off">
                <input type="hidden" name="id_seccion_up" value="<?php echo $seccion['id']; ?>">
                <div class="form-group">
                    <label for="profesor_up">Modificar profesor</label>
                    <select name="profesor_up" class="form-control select2" required>
                        <option value="">Seleccione un profesor</option>
                        <c?php foreach ($profesores as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($p['id'] == $seccion['teacher_id']) ? 'selected' : '' ?>>
                                <c?= htmlspecialchars($p['name'] . ' ' . $p['last_name']) ?>
                            </option>
                        <c?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="seccion_dia_up">Día de clases</label>
                    <select name="seccion_dia_up" class="form-control" required>
                        <option value="" disabled>Seleccione un día</option>
                        <c?php
                        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                        foreach ($dias as $dia):
                            $selected = ($dia == $seccion['section_day']) ? 'selected' : '';
                            echo "<option value='{$dia}' {$selected}>{$dia}</option>";
                        endforeach;
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success mt-2">Actualizar</button>
            </form-->

            <!-- Botón para archivar sección -->
            <!--form action="<s?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" class="FormularioAjax mt-3" data-form="delete" data-entity="seccion">
                <input type="hidden" name="id_seccion_eliminar" value="<s?php echo $seccion['id']; ?>">
                <button type="submit" class="btn btn-warning">Eliminar sección</button>
            </form-->
            
            <form class="FormularioAjax d-inline" action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" data-form="delete" data-entity="seccion_archivada" autocomplete="off">
                  <input type="hidden" name="id_seccion_eliminar" value="<?php echo $seccionId; ?>">
                        <!--button type="submit" class="btn btn-sm btn-danger" title="Eliminar sección">
                             <i class="fas fa-trash-alt"></i>
                        </button-->
                  <button type="submit" class="btn btn-warning">Eliminar sección</button>
            </form>
            
        </div>
        <?php endif; ?>
    </div>
</div>


<!--php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2): ?>
<div class="container-fluid mb-4">
  
<c?php
$pagina_eval = isset($_GET['eval_page']) ? (int)$_GET['eval_page'] : 1;
echo $ctrl->paginador_evaluaciones_controlador($pagina_eval, 10, $seccion['id'],0);
    
?>
   
</div>
<c?php endif; ?>



<div class="container-fluid mb-4">
    <h4 class="text-center">Estudiantes y puntajes</h4>
    <c?php
    $pagina_est = isset($_GET['est_page']) ? (int)$_GET['est_page'] : 1;
    echo $ctrl->paginador_estudiantes_con_puntajes_controlador($pagina_est, 10, $seccion['id'],0);
    ?>
</div>
<!--c?php endif; ?-->




<!--script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
    $(document).ready(function() {
        $('.select2').select2({
            placeholder: "Escriba el nombre del profesor",
            allowClear: true,
            width: '100%'
        });
    });
</script-->