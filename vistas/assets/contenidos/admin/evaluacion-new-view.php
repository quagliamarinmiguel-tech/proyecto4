<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 2) {
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
$datos = $ctrl->datos_para_evaluacion_new($periodCourseId, $sectionLetter);
if (!$datos['ok']) {
    echo '<div class="alert alert-danger text-center">' . htmlspecialchars($datos['message']) . '</div>';
    exit();
}

// Extraer datos
$seccion = $datos['seccion'];
$period = $datos['period'];
$estudiantes = $datos['estudiantes'];
$totalActivas = $datos['total_evaluaciones_activas'];
$sumaPorcentajes = $datos['suma_porcentajes'];
$porcentajeDisponible = $datos['porcentaje_disponible'];
$limiteAlcanzado = $datos['limite_alcanzado'];


$cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA EVALUACIÓN
    </h3>
    <p class="text-justify">
        Completa todos los campos obligatorios para agregar la evaluación correctamente. Al guardar, se registrará la evaluación y los puntajes de cada estudiante.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=seccion-consult&curso_vigente=<?php echo $periodCourseId; ?>&seccion=<?php echo $sectionLetter; ?>">
                <i class="fas fa-arrow-left fa-fw"></i> &nbsp; VOLVER A LA SECCIÓN
            </a>
        </li>
    </ul>
</div>

<!-- Contexto de la sección -->
<div class="container-fluid mb-4">
    <div class="alert alert-info" role="alert">
        <strong>Sección:</strong> <?php echo $sectionLetter; ?> &nbsp; | &nbsp;
        <strong>Curso:</strong> <?php echo htmlspecialchars($seccion['period_course']['course']['name'] ?? ''); ?> &nbsp; | &nbsp;
        <strong>Periodo:</strong> <?php echo htmlspecialchars($period['name'] ?? ''); ?> &nbsp; | &nbsp;
        <strong>Fechas:</strong> <?php echo $period['start_date'] ?? ''; ?> al <?php echo $period['end_date'] ?? ''; ?>
        <br>
        <strong>Profesor:</strong>
        <?php
        if (!empty($seccion['teacher_id'])) {
            $teacher = periodoModelo::obtener_profesor_por_id_supabase($seccion['teacher_id'], $_SESSION['auth_access_token'] ?? '');
            if ($teacher['ok'] && !empty($teacher['data'][0])) {
                echo htmlspecialchars($teacher['data'][0]['name'] . ' ' . $teacher['data'][0]['last_name']);
            } else {
                echo '<span class="text-muted">Sin asignar</span>';
            }
        } else {
            echo '<span class="text-muted">Sin asignar</span>';
        }
        ?>
    </div>
</div>

<!-- Formulario -->
<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" data-form="save" autocomplete="off">
        <input type="hidden" name="id_seccion" value="<?php echo $seccion['id']; ?>">
        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Datos de la evaluación</legend>

            <?php if ($limiteAlcanzado): ?>
                <div class="alert alert-danger">
                    <strong>Límite de pruebas alcanzado.</strong> No se pueden agregar más de 6 evaluaciones por sección.
                </div>
            <?php endif; ?>

            <div class="text-danger small mt-1" id="alerta-porcentaje" style="display:none;">
                El porcentaje total de evaluaciones en esta sección excede el 100%. Ajusta el valor.
            </div>
            <div class="text-muted small" id="porcentaje-acumulado">
                Porcentaje acumulado actual: <span id="porcentaje-total"><?php echo $sumaPorcentajes; ?></span>%
                <?php if ($porcentajeDisponible > 0): ?>
                    (disponible: <span id="porcentaje-disponible"><?php echo $porcentajeDisponible; ?></span>%)
                <?php endif; ?>
            </div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tipo_evaluacion" class="bmd-label-floating">Tipo de evaluación</label>
                            <input type="text" name="tipo_evaluacion" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 .]{0,19}" id="tipo_evaluacion" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="obj" class="bmd-label-floating">Objetivo</label>
                            <input type="number" min="1" step="0.1" name="obj" id="obj" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="porcentaje" class="bmd-label-floating">Porcentaje</label>
                            <input type="number" name="porcentaje" id="porcentaje" class="form-control"
                                   min="1" max="<?php echo $porcentajeDisponible; ?>"
                                   <?php echo ($limiteAlcanzado || $porcentajeDisponible <= 0) ? 'disabled' : ''; ?>
                                   required>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_evaluacion" class="bmd-label-floating">Fecha de evaluación</label>
                            <input type="date" name="fecha_evaluacion" id="fecha_evaluacion"
                                   min="<?php echo $period['start_date'] ?? ''; ?>"
                                   max="<?php echo $period['end_date'] ?? ''; ?>"
                                   class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <fieldset>
            <legend><i class="fas fa-user-graduate fa-fw"></i> &nbsp; Calificación por estudiante</legend>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered table-sm">
                    <thead class="text-center roboto-medium">
                        <tr>
                            <th>CI</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                        <tbody>
                            <?php foreach ($estudiantes as $est): ?>
                                <?php
                                // $est contiene: id (registration), student_id, student (objeto)
                                $student = $est['student'] ?? [];
                                ?>
                                <tr class="text-center">
                                    <td><?php echo htmlspecialchars($student['ci'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($student['name'] ?? ''); ?></td>
                                    <td><?php echo htmlspecialchars($student['last_name'] ?? ''); ?></td>
                                    <td>
                                        <select name="puntaje_<?php echo $est['id']; ?>" class="form-control">
                                            <option value="NP">NP</option>
                                            <?php for ($i = 0; $i <= 20; $i++): ?>
                                                <option value="<?php echo $i; ?>"><?php echo $i; ?></option>
                                            <?php endfor; ?>
                                        </select>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                </table>
            </div>
        </fieldset>

        <p class="text-center" style="margin-top: 40px;">
            <button type="submit" class="btn btn-raised btn-success btn-sm" id="btn-guardar"
                    <?php echo ($limiteAlcanzado || $porcentajeDisponible <= 0) ? 'disabled' : ''; ?>>
                <i class="far fa-save"></i> &nbsp; GUARDAR EVALUACIÓN
            </button>
        </p>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const porcentajeInput = document.getElementById("porcentaje");
    const alertaPorcentaje = document.getElementById("alerta-porcentaje");
    const porcentajeTotalSpan = document.getElementById("porcentaje-total");
    const porcentajeDisponibleSpan = document.getElementById("porcentaje-disponible");
    const btnGuardar = document.getElementById("btn-guardar");
    const sumaActual = <?php echo json_encode($sumaPorcentajes); ?>;
    const disponible = <?php echo json_encode($porcentajeDisponible); ?>;

    // Si ya no hay disponible, deshabilitar todo
    if (disponible <= 0) {
        porcentajeInput.disabled = true;
        btnGuardar.disabled = true;
        return;
    }

    porcentajeInput.addEventListener("input", function() {
        const valor = parseFloat(this.value) || 0;
        const total = sumaActual + valor;
        porcentajeTotalSpan.textContent = total.toFixed(1);

        if (total > 100) {
            alertaPorcentaje.style.display = "block";
            btnGuardar.disabled = true;
        } else {
            alertaPorcentaje.style.display = "none";
            btnGuardar.disabled = false;
        }

        // Mostrar cuánto queda si el valor es válido
        if (valor > 0 && valor <= disponible) {
            const resto = disponible - valor;
            if (porcentajeDisponibleSpan) {
                porcentajeDisponibleSpan.textContent = resto.toFixed(1);
            }
        }
    });
});
</script>




