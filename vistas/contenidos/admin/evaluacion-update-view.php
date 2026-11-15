
<?php
    $state = $_SESSION['profile_status'] ?? 0;
    $cargo = $_SESSION['cargo_spm'] ?? 0;
    $carpeta = "admin";

    if (!in_array($cargo, [1, 3]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    require_once "./controladores/seccionControlador.php";
    $ctrl = new seccionControlador();

    $encryptedId = $_GET['evaluacion_id_up'] ?? '';
    $decryptedId = mainModel::decryption($encryptedId);
    $evaluationId = (int) $decryptedId;

    if ($evaluationId <= 0) {
        echo '<div class="alert alert-danger text-center">ID de evaluación inválido.</div>';
        exit();
    }

    $datos = $ctrl->datos_para_evaluacion_update_controlador($evaluationId);
    if (!$datos['ok']) {
        echo '<div class="alert alert-danger text-center">' . htmlspecialchars($datos['message']) . '</div>';
        exit();
    }

    // Datos que ya devuelve el controlador
    $evaluacion    = $datos['evaluacion'];
    $seccion       = $datos['seccion'];         
    $estudiantes   = $datos['estudiantes'];
    $puntajesMap   = $datos['puntajes_map'];
    $sumaExcluyendo = $datos['suma_excluyendo'];
    $porcentajeActual = $datos['porcentaje_actual'];

    
    $sectionId      = (int) $seccion['id'];
    $sectionLetter  = $seccion['name'] ?? '';
    $periodCourseId = $seccion['period_course_id'] ?? 0;

    $periodCourse = $seccion['period_course'] ?? [];
    $period       = $periodCourse['period']   ?? [];
    $course       = $periodCourse['course']   ?? [];
    $teacher      = $seccion['teacher']       ?? null;

$profearchivado="";
if ($teacher['state'] == 0){$profearchivado="(Archivado)";}

    // Fechas del periodo
    $periodStart = $period['start_date'] ?? '';
    $periodEnd   = $period['end_date']   ?? '';
?>


<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-edit fa-fw"></i> &nbsp; ACTUALIZAR EVALUACIÓN</h3>
    <p class="text-justify">Modifica los datos de la evaluación y los puntajes de los estudiantes.</p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=seccion-consult&curso_vigente=<?php echo $periodCourseId; ?>&seccion_id=<?php echo $sectionId; ?>">
                <i class="fas fa-arrow-left fa-fw"></i> &nbsp; VOLVER A LA SECCIÓN
            </a>
        </li>
    </ul>
</div>

<div class="container-fluid">
    
    <!-- Contexto de la sección -->
    <div class="container-fluid mb-4">
        <div class="alert alert-info" role="alert">
            <strong>Sección:</strong> <?php echo htmlspecialchars($sectionLetter); ?> &nbsp; | &nbsp;
            <strong>Curso:</strong> <?php echo htmlspecialchars($course['name'] ?? ''); ?> &nbsp; | &nbsp;
            <strong>Periodo:</strong> <?php echo htmlspecialchars($period['name'] ?? ''); ?> &nbsp; | &nbsp;
            <strong>Fechas:</strong> <?php echo $periodStart; ?> al <?php echo $periodEnd; ?>
            <br>
            
        	<p>
                <strong>Profesor:</strong> 
                <?= $teacher 
                    ? htmlspecialchars($teacher['name'] . ' ' . $teacher['last_name']) 
                    : 'Sin asignar' ?>
                <?php if ($profearchivado !== ''): ?>
                    <span class="badge badge-warning" style="background-color:#BA3116;"><?= $profearchivado ?></span>
                <?php endif; ?>
            </p>
            
            
        </div>
    </div>
    
    
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" data-form="save" autocomplete="off">
        <input type="hidden" name="evaluacion_id_up" value="<?php echo $evaluationId; ?>">
        <input type="hidden" name="id_seccion" value="<?php echo $sectionId; ?>">

        <fieldset>
            <legend><i class="fas fa-info-circle"></i> Datos de la evaluación</legend>

            <div class="text-danger small mt-1" id="alerta-porcentaje" style="display:none;">
                El porcentaje total de evaluaciones en esta sección excede el 100%. Ajusta el valor.
            </div>
            <div class="text-muted small" id="porcentaje-acumulado">
                Porcentaje acumulado actual (sin contar esta evaluación): <span id="porcentaje-total"><?php echo $sumaExcluyendo; ?></span>%
            </div>

            <div class="row">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="tipo_evaluacion">Tipo de evaluación</label>
                        <input type="text" name="tipo_evaluacion" id="tipo_evaluacion" class="form-control"  pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 .]{0,19}"
                               value="<?php echo htmlspecialchars($evaluacion['evaluation_type'] ?? ''); ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="obj">Objetivo</label>
                        <input type="number" step="0.1" name="obj" id="obj" class="form-control"
                               value="<?php echo $evaluacion['objective'] ?? 0; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="porcentaje">Porcentaje</label>
                        <input type="number" name="porcentaje" id="porcentaje" class="form-control"
                               min="1" max="100"
                               value="<?php echo $evaluacion['percentage'] ?? 0; ?>" required>
                    </div>
                </div>
            </div>
            <div class="row mt-3">
                <div class="col-md-4">
                    <div class="form-group">
                        <label for="fecha_evaluacion">Fecha de evaluación</label>
                        <input type="date" name="fecha_evaluacion" id="fecha_evaluacion" class="form-control"
                               min="<?php echo $periodStart; ?>"
                               max="<?php echo $periodEnd; ?>"
                               value="<?php echo $evaluacion['evaluation_date'] ?? ''; ?>" required>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="form-group">
                        <label>Estado</label>
                        <input type="text" class="form-control" value="<?php echo ($evaluacion['state'] == 1) ? 'Activa' : 'Archivada'; ?>" readonly>
                    </div>
                </div>
            </div>
        </fieldset>

        <br>

        <fieldset>
            <legend><i class="fas fa-user-graduate"></i> Puntajes por estudiante</legend>
            <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                <table class="table table-bordered table-sm">
                    <thead class="text-center">
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
                                $registrationId = (int) $est['registration_id'];
                                $puntajeActual  = $puntajesMap[$registrationId] ?? null;
                            ?>
                            <tr class="text-center">
                                <td><?php echo htmlspecialchars(mainModel::decryption($est['ci'] ?? '')); ?></td>
                                <td><?php echo htmlspecialchars($est['name'] ?? ''); ?></td>
                                <td><?php echo htmlspecialchars($est['last_name'] ?? ''); ?></td>
                                <td>
                                    <select name="puntaje_<?php echo $registrationId; ?>" class="form-control">
                                        <option value="NP" <?php echo ($puntajeActual === null || $puntajeActual === '') ? 'selected' : ''; ?>>NP</option>
                                        <?php for ($i = 0; $i <= 20; $i++): ?>
                                            <option value="<?php echo $i; ?>" <?php echo ((string) $puntajeActual === (string) $i) ? 'selected' : ''; ?>>
                                                <?php echo $i; ?>
                                            </option>
                                        <?php endfor; ?>
                                    </select>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
      
                    
                </table>
            </div>
        </fieldset>

        <p class="text-center">
            <button type="submit" class="btn btn-success btn-sm" id="btn-guardar">
                <i class="far fa-save"></i> ACTUALIZAR EVALUACIÓN
            </button>
        </p>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function() {
    const porcentajeInput = document.getElementById("porcentaje");
    const alertaPorcentaje = document.getElementById("alerta-porcentaje");
    const porcentajeTotalSpan = document.getElementById("porcentaje-total");
    const btnGuardar = document.getElementById("btn-guardar");

    let sumaExcluyendo = <?php echo json_encode($sumaExcluyendo); ?>;

    porcentajeInput.addEventListener("input", function() {
        const valor = parseFloat(this.value) || 0;
        const total = sumaExcluyendo + valor;
        porcentajeTotalSpan.textContent = total.toFixed(1);

        if (total > 100) {
            alertaPorcentaje.style.display = "block";
            btnGuardar.disabled = true;
        } else {
            alertaPorcentaje.style.display = "none";
            btnGuardar.disabled = false;
        }
    });
});
</script>