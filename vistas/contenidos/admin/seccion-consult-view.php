<?php

if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4 && $_SESSION['cargo_spm'] != 2) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

$periodCourseId = isset($_GET['curso_vigente']) ? (int) $_GET['curso_vigente'] : 0;
$sectionLetter = isset($_GET['seccion']) ? $_GET['seccion'] : '';
$archivados =     isset($_GET['archivados']) ? (int) $_GET['archivados'] : '';

$state=1;
if ($archivados == 0) {$state=$archivados;}
//var_dump($state);die();

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
        <i class="fas fa-users fa-fw"></i> &nbsp; SECCIÓN <?php echo strtoupper($sectionLetter); ?>
    </h3>
    <p class="text-justify">
        Detalles de la sección y evaluaciones registradas.
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
            <!-- Formulario para modificar profesor y día -->
            <form action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" class="FormularioAjax" autocomplete="off">
                <input type="hidden" name="id_seccion_up" value="<?php echo $seccion['id']; ?>">
                <div class="form-group">
                    <label for="profesor_up">Modificar profesor</label>
                    <select name="profesor_up" class="form-control select2" required>
                        <option value="">Seleccione un profesor</option>
                        <?php foreach ($profesores as $p): ?>
                            <option value="<?= $p['id'] ?>" <?= ($p['id'] == $seccion['teacher_id']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($p['name'] . ' ' . $p['last_name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label for="seccion_dia_up">Día de clases</label>
                    <select name="seccion_dia_up" class="form-control" required>
                        <option value="" disabled>Seleccione un día</option>
                        <?php
                        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                        foreach ($dias as $dia):
                            $selected = ($dia == $seccion['section_day']) ? 'selected' : '';
                            echo "<option value='{$dia}' {$selected}>{$dia}</option>";
                        endforeach;
                        ?>
                    </select>
                </div>
                <button type="submit" class="btn btn-success mt-2">Actualizar</button>
            </form>

            <form class="FormularioAjax d-inline" action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" data-form="delete2" data-entity="seccion" autocomplete="off">
                  <input type="hidden" name="id_seccion_eliminar" value="<?php echo  $seccion['id']; ?>">
                  <button type="submit" class="btn btn-warning">Eliminar sección</button>
            </form>
            <br>
            <!-- Botón para abrir el modal -->
            <button type="button" class="btn btn-primary" id="btnTransferirEstudiantes" data-section-id="<?php echo $seccion['id']; ?>">
                <i class="fas fa-exchange-alt"></i> Transferir estudiantes
            </button>
            
        </div>
        <?php endif; ?>
    </div>
</div>



<!-- Modal -->
<div id="modalTransferir" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:2; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
    <div style="position:relative; margin:auto; top:5%; max-width:600px; background:#fff; padding:20px; border-radius:8px; border: 2px solid #007bff;">
        <h5 class="text-center">Transferir estudiantes</h5>
        <hr>
        <div id="contenidoTransferir">
            <p>Cargando datos...</p>
        </div>
        <div class="text-center mt-3">
            <button type="button" class="btn btn-secondary" onclick="document.getElementById('modalTransferir').style.display='none'">Cancelar</button>
            <button type="button" class="btn btn-primary" id="btnEjecutarTransferencia">Transferir</button>
        </div>
    </div>
</div>







<?php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2): ?>
<div class="container-fluid mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">Evaluaciones registradas</h4>

        <?php if ($evaluacionesCount < 6): ?>
            <?php if ($state ==1): ?>
                <a href="<?= SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/evaluacion-new&curso_vigente=' . urlencode($periodCourseId) . '&seccion=' . urlencode($sectionLetter); ?>" class="btn btn-lg btn-primary" style="text-transform: none;">
                    <i class="fas fa-plus"></i> Agregar evaluación
                </a>
            <?php endif; ?>
        <?php else: ?>
		            <span class="text-danger font-weight-bold">Límite de pruebas alcanzado</span>
        <?php endif; ?>
    </div>

<?php
$pagina_eval = isset($_GET['eval_page']) ? (int)$_GET['eval_page'] : 1;
echo $ctrl->paginador_evaluaciones_controlador($pagina_eval, 10, $seccion['id'],$state);
    
?>
   
</div>
<?php endif; ?>





<!--c?php if ($_SESSION['cargo_spm'] != 5): ?-->
<div class="container-fluid mb-4">
    <h4 class="text-center">Estudiantes y puntajes</h4>
    <?php
    $pagina_est = isset($_GET['est_page']) ? (int)$_GET['est_page'] : 1;
    echo $ctrl->paginador_estudiantes_con_puntajes_controlador($pagina_est, 10, $seccion['id'],$state);
    ?>
</div>
<!--c?php endif; ?-->


<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <?php if ($state ==1): ?>
            <li>
                <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-consult&archivados=0&curso_vigente=' . urlencode($periodCourseId) . '&seccion=' . urlencode($sectionLetter) . '' ?>">
                    <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de evaluaciones archivadas
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-consult&curso_vigente=' . urlencode($periodCourseId) . '&seccion=' . urlencode($sectionLetter) . '' ?>">
                    <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de evaluaciones activas
                </a>
            </li>
        <?php endif; ?>        
    </ul>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
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
</script>
<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnAbrir = document.getElementById('btnTransferirEstudiantes');
    const modal = document.getElementById('modalTransferir');
    const contenido = document.getElementById('contenidoTransferir');
    const btnEjecutar = document.getElementById('btnEjecutarTransferencia');

    // Variable para guardar el ID de la sección origen
    let sectionOrigenId = null;

    btnAbrir.addEventListener('click', function() {
        sectionOrigenId = this.dataset.sectionId;
        modal.style.display = 'block';
        contenido.innerHTML = '<p>Cargando datos...</p>';

        // Obtener datos via AJAX
        fetch('<?php echo SERVERURL; ?>ajax/periodoAjax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                accion: 'obtener_datos_transferencia',
                section_id: sectionOrigenId
            })
        })
        .then(res => res.json())
        .then(response => {
            // Verificar respuesta general
            if (!response.ok) {
                contenido.innerHTML = '<p class="text-danger">Error: ' + (response.error || 'Error desconocido') + '</p>';
                return;
            }

            // Extraer los datos anidados (si existen)
            const datos = response.data || response; // Si tiene "data", úsalo; si no, usa la respuesta directamente

            // Verificar que los datos tengan la estructura esperada
            if (!datos.estudiantes || !datos.secciones) {
                contenido.innerHTML = '<p class="text-danger">Error: la respuesta no tiene el formato esperado.</p>';
                console.error('Respuesta inesperada:', datos);
                return;
            }

            renderizarModal(datos, sectionOrigenId);
        })
        .catch(err => {
            contenido.innerHTML = '<p class="text-danger">Error al cargar datos.</p>';
            console.error(err);
        });
    });

    function renderizarModal(datos, origenId) {
        const estudiantes = datos.estudiantes || [];
        const secciones = datos.secciones || [];

        let html = `
            <p><strong>Estudiantes de la sección actual:</strong> ${estudiantes.length} inscritos</p>
            <div style="max-height: 200px; overflow-y: auto; border: 1px solid #ccc; padding: 10px; margin-bottom: 15px;">
                ${estudiantes.length === 0 ? '<p class="text-muted">No hay estudiantes para transferir.</p>' : ''}
                ${estudiantes.map(est => `
                    <div class="form-check">
                        <input class="form-check-input" type="checkbox" name="estudiantes" value="${est.registration_id}" id="est_${est.registration_id}">
                        <label class="form-check-label" for="est_${est.registration_id}">
                            ${est.ci} - ${est.name} ${est.last_name}
                        </label>
                    </div>
                `).join('')}
            </div>

            <div class="form-group">
                <label for="destinoSelect">Sección de destino:</label>
                <select class="form-control" id="destinoSelect">
                    <option value="">Seleccione...</option>
                    ${secciones.map(sec => `
                        <option value="${sec.id}">${sec.name} (${sec.count} estudiantes)</option>
                    `).join('')}
                </select>
            </div>
        `;

        contenido.innerHTML = html;

        // Guardar el ID de la sección origen en el botón de transferencia
        btnEjecutar.dataset.sectionOrigen = origenId;
    }

    // Botón de transferencia (ahora con fetch)
    btnEjecutar.addEventListener('click', function() {
        const destino = document.getElementById('destinoSelect').value;
        if (!destino) {
            alert('Selecciona una sección de destino.');
            return;
        }
        const checkboxes = document.querySelectorAll('input[name="estudiantes"]:checked');
        if (checkboxes.length === 0) {
            alert('Selecciona al menos un estudiante.');
            return;
        }
        const registrationIds = Array.from(checkboxes).map(cb => cb.value);

        const sectionOrigen = this.dataset.sectionOrigen;
        if (!sectionOrigen) {
            alert('Error: no se pudo identificar la sección origen.');
            return;
        }

        // Construir FormData
        const formData = new FormData();
        formData.append('id_seccion_origen', sectionOrigen);
        formData.append('id_seccion_destino', destino);
        registrationIds.forEach(id => {
            formData.append('registration_ids[]', id);
        });

        // Enviar con fetch
        fetch('<?php echo SERVERURL; ?>ajax/periodoAjax.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            // Usar la función global alertas_ajax
            if (typeof alertas_ajax === 'function') {
                alertas_ajax(res);
            } else {
                console.error('alertas_ajax no definida');
                // Fallback: mostrar alerta simple
                Swal.fire({
                    title: res.Titulo || 'Operación completada',
                    text: res.Texto || '',
                    type: res.Tipo || 'success',
                    confirmButtonText: 'Aceptar'
                });
            }
        })
        .catch(err => {
            console.error('Error en la transferencia:', err);
            Swal.fire('Error', 'Ocurrió un error al transferir', 'error');
        });
    });

    // Cerrar modal al hacer clic fuera
    modal.addEventListener('click', function(e) {
        if (e.target === this) {
            this.style.display = 'none';
        }
    });
});
</script>
