<?php
	$state = $_SESSION['profile_status'] ?? 0;
	$cargo = $_SESSION['cargo_spm'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1, 2, 4]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

$periodCourseId = isset($_GET['curso_vigente']) ? (int) $_GET['curso_vigente'] : 0;
$sectionLetter = isset($_GET['seccion']) ? $_GET['seccion'] : '';
$sectionId = (int) ($_GET['seccion_id'] ?? 0);
$archivados =     isset($_GET['archivados']) ? (int) $_GET['archivados'] : '';

$state=1;
if ($archivados == 0) {$state=$archivados;}

if ($sectionId == 0) {
    echo '<div class="alert alert-danger text-center">Parámetros inválidos. No se puede cargar la sección.</div>';
    exit();
}

require_once "./controladores/seccionControlador.php";
$ctrl = new seccionControlador();

$datos = $ctrl->obtener_datos_para_seccion_consulta( $sectionId,$state);
if (!$datos['ok']) {
    echo '<div class="alert alert-danger text-center">' . htmlspecialchars($datos['message']) . '</div>';
    exit();
}

// Extraer variables para la vista
$seccion = $datos['seccion'];
$period = $datos['period'];
$course = $datos['course'];
$teacher = $datos['teacher']; 

$profearchivado = "";
if ($teacher !== null && (int)($teacher['state'] ?? 1) === 0) {$profearchivado = '(Archivado)';}

$evaluacionesCount = $datos['evaluaciones_count'];
$totalPorcentaje = $datos['total_porcentaje'];

$evaluacionesArray = $datos['evaluaciones'] ?? $datos['data']['evaluations'] ?? [];


$mostrarExportar = ($totalPorcentaje == 100.0);
?>
<style>
    .FormularioAjax{background-color:inherit;}
</style>

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
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE SECCIONES</a></li>
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-course-list-archived'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE SECCIONES ARCHIVADAS</a></li>
        <li><a class="active" href="#"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; SECCIÓN</a></li>
    </ul>
</div>


<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-md-6">
            <p><strong>ID de sección:</strong> <?php echo $seccion['id']; ?></p>
            <p><strong>Curso:</strong> <?php echo htmlspecialchars($course['name'] ?? ''); ?></p>
            <p><strong>ID del periodo del curso:</strong> <?php echo $periodCourseId; ?></p>
            <p><strong>Periodo:</strong> <?php echo htmlspecialchars($period['name'] ?? ''); ?></p>
            <p><strong>Estado:</strong> <?php echo htmlspecialchars($period['current_state'] ?? ''); ?></p>
            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($period['type'] ?? ''); ?></p>
            <p><strong>Fechas:</strong> <?php echo $period['start_date'] ?? ''; ?> al <?php echo $period['end_date'] ?? ''; ?></p>
            <p><strong>Día:</strong> <?php echo $seccion['section_day']; ?></p>            
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

        <!-- && $period['current_state'] != "Terminado"-->
        <?php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
        <div class="col-md-6">
            <!-- Formulario para modificar profesor y día -->
            <form action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" class="FormularioAjax" autocomplete="off">
                <input type="hidden" name="id_seccion_up" value="<?php echo $seccion['id']; ?>">
                <div class="form-group"  style="background-color:inherit;">
                    <label for="profesor_up">Modificar profesor</label>
          
                    <select name="profesor_up" id="profesor_up" class="form-control select2" required>
                        <?php if (!empty($teacher)): ?>
                            <option value="<?= (int) $teacher['id'] ?>" selected>
                                <?= htmlspecialchars($teacher['name'] . ' ' . $teacher['last_name']) ?>
                            </option>
                        <?php endif; ?>
                    </select>
                    
                </div>
                <div class="form-group" style="background-color:inherit;">
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

            <form class="FormularioAjax d-inline" action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" data-form="delete2" data-entity="seccion" autocomplete="off">
                  <input type="hidden" name="id_seccion_eliminar" value="<?php echo  $seccion['id']; ?>">
                  <button type="submit" class="btn btn-warning">Eliminar sección</button>
            </form>
            <br>
            
            
           <?php if ($_SESSION['cargo_spm'] != 4): ?>
            <!-- Botón para abrir el modal -->
            <button type="button" class="btn btn-primary" id="btnTransferirEstudiantes" data-section-id="<?php echo $seccion['id']; ?>">
                <i class="fas fa-exchange-alt"></i> Transferir estudiantes
            </button>
           <?php endif; ?>

            
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





<br><br>


<?php
    $tipo=$period['type'];
    $filas=12;
    if($tipo== "Intensivo"){$filas=6;} 
?>

<fieldset>
    <div class="card-header bg-dark text-white">
      Reportes
    </div>
    <br>

             <div class="row">
                 <div class="col-md-4 mb-3">
                     <div class="d-flex align-items-center card justify-content-between">
                         <span class="fw-bold text-secondary"> Nómina</span>
                         <div class="d-flex gap-2">
                             <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_nomina&seccion_id=<?= $seccion['id'] ?>" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
                             <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_nomina&seccion_id=<?= $seccion['id'] ?>" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i></a>
                             <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=csv_nomina&seccion_id=<?= $seccion['id'] ?>" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                         </div>
                    </div>
                 </div>
                 <div class="col-md-4 mb-3">
                      <div class="d-flex align-items-center card justify-content-between">
                           <span class="fw-bold text-secondary"> Calificaciones</span>
                           <div class="d-flex gap-2">
                               <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_seccion&seccion_id=<?= $seccion['id'] ?>" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i> </a>
                               <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_seccion&seccion_id=<?= $seccion['id']; ?>" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i> </a>
                               <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=csv_seccion&seccion_id=<?= $seccion['id'] ?>" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                           </div>
                       </div>
                 </div>
                 
                 <div class="col-md-4 mb-3">
                      <div class="d-flex align-items-center card justify-content-between">
                           <span class="fw-bold text-secondary"> Pagos</span>
                           <div class="d-flex gap-2">
                               <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_pagos_seccion&seccion_id=<?= $seccion['id'] ?>" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i> </a>
                               <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_pagos_seccion&seccion_id=<?= $seccion['id']; ?>" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i> </a>
                               <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=csv_pagos_seccion&seccion_id=<?= $seccion['id'] ?>" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                           </div>
                       </div>
                 </div>
                 
                 
             <?php if ($_SESSION['cargo_spm'] != 4): ?>
                 <div class="col-md-3 mb-3">
                      <div class="d-flex align-items-center card justify-content-between">
                           <span class="fw-bold text-secondary"> Acta de calificaciones</span>
                           <div class="d-flex gap-2">
                               <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_acta_cal&seccion_id=<?= $seccion['id'] ?>" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i> </a>
                               <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_acta_cal&seccion_id=<?= $seccion['id']; ?>" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i> </a>
                           </div>
                       </div>
                 </div>
                 <div class="col-md-3 mb-3">
                      <div class="d-flex align-items-center card justify-content-between">
                           <span class="fw-bold text-secondary"> Formato de asistencia</span>
                           <div class="d-flex gap-2">
                                <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_asistencia&seccion_id=<?= $seccion['id'] ?>&num_fechas=<?= $filas ?>"class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
                                <a href="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_asistencia&seccion_id=<?= $seccion['id'] ?>&num_fechas=<?= $filas ?>"class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i> </a>
                           </div>
                       </div>
                 </div>  
             <?php endif; ?>

                 
             </div>
</fieldset>



<?php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 3): ?>
<div class="container-fluid mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        
        <?php if ($state ==1): ?>
            <h4 class="mb-0">Evaluaciones registradas</h4>
        <?php else: ?>
            <h4 class="mb-0">Evaluaciones Archivadas</h4>
		<?php endif; ?>  
        
        <?php if ($evaluacionesCount < 6): ?>
            <?php if ($state ==1): ?>
				<a href="<?= SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/evaluacion-new&seccion=' . urlencode($sectionId); ?>" class="btn btn-lg btn-primary" style="text-transform: none;">
                    <i class="fas fa-plus"></i> Agregar evaluación
                </a>
            <?php endif; ?>
        <?php else: ?>
		            <span class="text-danger font-weight-bold">Límite de pruebas alcanzado</span>
        <?php endif; ?>
    </div>

<?php
echo $ctrl->tabla_evaluaciones_controlador($evaluacionesArray, (int)$seccion['id'], $state);

?>
   
</div>
<?php endif; ?>






<div class="container-fluid mb-4">
        <?php if ($state ==1): ?>
    		<h4 class="text-center">Estudiantes y puntajes</h4>
        <?php else: ?>
    		<h4 class="text-center">Puntajes de evaluaciones archivadas</h4>
        <?php endif; ?>
    <?php
    $pagina_est = isset($_GET['est_page']) ? (int)$_GET['est_page'] : 1;
    echo $ctrl->paginador_estudiantes_con_puntajes_controlador($pagina_est, 10, $seccion['id'],$state);
    ?>
</div>


<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <?php if ($state ==1): ?>
            <li>
                <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-consult&curso_vigente=' . urlencode($periodCourseId) . '&archivados=0&seccion_id=' . urlencode($sectionId) . '' ?>">
                    <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; Lista de evaluaciones archivadas
                </a>
            </li>
        <?php else: ?>
            <li>
                <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-consult&curso_vigente=' . urlencode($periodCourseId) . '&seccion_id=' . urlencode($sectionId) . '' ?>">
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
    $('#profesor_up').select2({
        placeholder: "Seleccione un profesor",
        allowClear: true,
        minimumInputLength: 2,
        language: {
            inputTooShort: function() {
                return "Por favor ingrese 2 o más caracteres";
            }
        },
        ajax: {
            url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_profesor",
            dataType: 'json',
            delay: 250,
            data: params => ({ q: params.term }),
            processResults: data => ({
                results: data.map(p => ({
                    id: p.id,
                    text: `${p.name} ${p.last_name} (id: ${p.id})`
                }))
            }),
            cache: true
        }
    });
});
</script>








<script>
document.addEventListener("DOMContentLoaded", function() {
    const btnAbrir    = document.getElementById('btnTransferirEstudiantes');
    const modal       = document.getElementById('modalTransferir');
    const contenido   = document.getElementById('contenidoTransferir');
    const btnEjecutar = document.getElementById('btnEjecutarTransferencia');

    let sectionOrigenId = null;

    btnAbrir.addEventListener('click', function() {
        sectionOrigenId = this.dataset.sectionId;
        modal.style.display = 'block';
        contenido.innerHTML = '<p>Cargando datos...</p>';

        fetch('<?php echo SERVERURL; ?>ajax/seccionAjax.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
            body: new URLSearchParams({
                accion: 'obtener_datos_transferencia',
                section_id: sectionOrigenId
            })
        })
        .then(res => res.json())
        .then(response => {
            if (response.ok === false) {
                contenido.innerHTML = '<p class="text-danger">Error: ' + (response.error || 'Error desconocido') + '</p>';
                return;
            }

            // El backend responde: { estudiantes: [...], hermanas: [...] }
            // (con posible envoltura {ok, data})
            const datos = response.data || response;
            const estudiantes = Array.isArray(datos.estudiantes) ? datos.estudiantes : [];
            const hermanas    = Array.isArray(datos.hermanas)    ? datos.hermanas    : [];

            renderizarModal({ estudiantes, hermanas }, sectionOrigenId);
        })
        .catch(err => {
            contenido.innerHTML = '<p class="text-danger">Error al cargar datos.</p>';
            console.error(err);
        });
    });

    function renderizarModal(datos, origenId) {
        const estudiantes = datos.estudiantes || [];
        const hermanas    = datos.hermanas    || [];

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
                    ${hermanas.length === 0
                        ? '<option value="" disabled>No hay otras secciones en este periodo</option>'
                        : hermanas.map(sec => `
                            <option value="${sec.id}">
                                ${sec.name}${sec.section_day ? ' - ' + sec.section_day : ''} (${sec.count ?? 0} estudiantes)
                            </option>
                        `).join('')
                    }
                </select>
            </div>
        `;

        contenido.innerHTML = html;
        btnEjecutar.dataset.sectionOrigen = origenId;
    }

    btnEjecutar.addEventListener('click', function() {
        const destino = document.getElementById('destinoSelect').value;
        if (!destino) { alert('Selecciona una sección de destino.'); return; }

        const checkboxes = document.querySelectorAll('input[name="estudiantes"]:checked');
        if (checkboxes.length === 0) { alert('Selecciona al menos un estudiante.'); return; }

        const registrationIds = Array.from(checkboxes).map(cb => cb.value);
        const sectionOrigen = this.dataset.sectionOrigen;
        if (!sectionOrigen) { alert('Error: no se pudo identificar la sección origen.'); return; }

        const formData = new FormData();
        formData.append('id_seccion_origen', sectionOrigen);
        formData.append('id_seccion_destino', destino);
        registrationIds.forEach(id => formData.append('registration_ids[]', id));

        fetch('<?php echo SERVERURL; ?>ajax/seccionAjax.php', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if (typeof alertas_ajax === 'function') {
                alertas_ajax(res);
            } else {
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

    modal.addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});
</script>

