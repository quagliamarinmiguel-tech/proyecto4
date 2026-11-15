<?php
    $state = $_SESSION['profile_status'] ?? 0;
	$cargo = $_SESSION['cargo_spm'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1, 2]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }


require_once "./controladores/solicitudControlador.php";
$ctrl = new solicitudControlador();

$idEnc = $_GET['solicitud_id'] ?? '';
if ($idEnc === '') {
    echo '<div class="alert alert-danger">ID no proporcionado.</div>';
    exit;
}

$resp = $ctrl->datos_solicitud_check_controlador($idEnc);
if (!$resp['ok']) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($resp['message']) . '</div>';
    exit;
}

$sol = $resp['data'];

// ── Datos principales ──
$student      = $sol['student'];
$course       = $sol['course'];
$period       = $sol['period'];
$transaction  = $sol['transaction'];
$secciones    = $sol['secciones'] ?? [];
$alternativos = $sol['periodos_alternativos'] ?? [];

// ── Banderas de validación ──
$periodoValido  = (bool) ($sol['periodo_valido'] ?? false);
$motivoBloqueo  = $sol['motivo_bloqueo'] ?? null;
$dias           = (int) ($sol['dias_transcurridos'] ?? 0);
$puedeAceptarse = (bool) ($sol['puede_aceptarse'] ?? false);

// ── Duplicados ──
$duplicados       = $sol['duplicados'] ?? [];
$hayDuplicados    = (bool) ($duplicados['hay_duplicados'] ?? false);
$motivoDuplicados = $duplicados['motivo'] ?? null;

// ── Estado de la solicitud ──
$registrationState = (int) ($sol['registration_state'] ?? 0);
$estadosMap = [
    1 => ['texto' => 'Aceptada',  'color' => 'success', 'icono' => 'fa-check-circle'],
    2 => ['texto' => 'Pendiente', 'color' => 'warning', 'icono' => 'fa-clock'],
    3 => ['texto' => 'Rechazada', 'color' => 'danger',  'icono' => 'fa-times-circle'],
];
$estadoInfo = $estadosMap[$registrationState] ?? [
    'texto' => 'Desconocido', 'color' => 'secondary', 'icono' => 'fa-question-circle'
];

$pcIdEnc = mainModel::encryption((int) $sol['period_course_id']);

// Nombres de secciones existentes (para calcular la letra en JS)
$nombresSecciones = array_column($secciones, 'name');
?>

<style>
.label-destacada { font-size: 1.15rem; font-weight: 600; color: #212529; }
.captura-img { max-height: 500px; border: 2px solid #ccc; }
.info-curso { background: #e9f7ef; border-left: 4px solid #28a745; padding: 15px; border-radius: 4px; }
.estado-solicitud { border-left-width: 5px; }
</style>

<div class="container mt-4">
    <div class="full-box page-header">
        <h3><i class="fas fa-check-circle fa-fw"></i> &nbsp; VERIFICAR SOLICITUD</h3>
    </div>

    <div class="container-fluid">
        <ul class="full-box list-unstyled page-nav-tabs">
            <li><a class="active" href="#"><i class="fas fa-check"></i> VERIFICAR</a></li>
            <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/solicitud-nuevo-ingreso-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE SOLICITUDES</a></li>
        </ul>

    </div>

    <!-- ESTADO DE LA SOLICITUD -->
    <div class="alert alert-<?= $estadoInfo['color'] ?> estado-solicitud d-flex align-items-center mb-3">
        <i class="fas <?= $estadoInfo['icono'] ?> fa-2x mr-3"></i>
        <div>
            <strong>Estado de la solicitud:</strong> <?= $estadoInfo['texto'] ?>
            <?php if ($registrationState === 1): ?>
                — Esta solicitud ya fue aceptada y el estudiante está inscrito.
            <?php elseif ($registrationState === 3): ?>
                — Esta solicitud fue rechazada.
            <?php elseif ($registrationState === 2): ?>
                — Pendiente de revisión.
            <?php endif; ?>
        </div>
    </div>

    <!-- ALERTA DE DUPLICADOS -->
    <?php if ($hayDuplicados): ?>
    <div class="alert alert-warning mb-3">
        <i class="fas fa-exclamation-triangle"></i>
        <strong>Datos duplicados detectados:</strong>
        <?= htmlspecialchars($motivoDuplicados ?? 'Existen datos que coinciden con otro estudiante activo.') ?>
    </div>
    <?php endif; ?>

    <!-- Info del curso y período -->
    <div class="card mb-3 info-curso">
        <div class="card-body">
            <h5 class="label-destacada">
                <i class="fas fa-book"></i> <?= htmlspecialchars($course['name']) ?>
                <small class="text-muted">(<?= htmlspecialchars($course['type']) ?>)</small>
            </h5>
            <p class="mb-1"><strong>Período:</strong> <?= htmlspecialchars($period['name']) ?> (<?= htmlspecialchars($period['type']) ?>)</p>
            <p class="mb-1"><strong>Estado:</strong> <?= htmlspecialchars($period['current_state']) ?>
                <?php if ($period['current_state'] === 'Vigente'): ?>
                    — Día <?= $dias ?> desde inicio
                <?php endif; ?>
            </p>
            <p class="mb-1"><strong>Fechas:</strong> <?= htmlspecialchars($period['start_date']) ?> al <?= htmlspecialchars($period['end_date']) ?></p>

            <?php if (!$periodoValido && $motivoBloqueo): ?>
                <div class="alert alert-danger mt-3 mb-0">
                    <i class="fas fa-exclamation-triangle"></i>
                    <strong>Período no disponible:</strong> <?= htmlspecialchars($motivoBloqueo) ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <div class="row mb-3">
        <!-- Datos del estudiante -->
        <div class="col-md-6">
            <div class="card p-3">
                <h5 class="label-destacada">Datos del estudiante</h5>
                <p><strong>Nombre:</strong> <?= htmlspecialchars($student['name'] . ' ' . $student['last_name']) ?></p>
                <p><strong>Cédula:</strong> <?= htmlspecialchars($student['ci']) ?></p>
                <p><strong>F. Nacimiento:</strong> <?= htmlspecialchars($student['birthday']) ?></p>
                <p><strong>Teléfono:</strong> <?= htmlspecialchars($student['phone']) ?></p>
                <p><strong>WhatsApp:</strong> <?= htmlspecialchars($student['whatsapp_phone']) ?></p>
                <p><strong>Correo:</strong> <?= htmlspecialchars($student['email']) ?></p>
                <p><strong>Dirección:</strong> <?= htmlspecialchars($student['address']) ?></p>
                <p><strong>Lugar nac.:</strong> <?= htmlspecialchars($student['birthplace']) ?></p>
                <p><strong>Nivel estudio:</strong> <?= htmlspecialchars($student['education_level']) ?></p>
                <p><strong>Trabaja:</strong> <?= $student['work'] ? 'Sí' : 'No' ?> <?= $student['current_work'] ? '- ' . htmlspecialchars($student['current_work']) : '' ?></p>
            </div>
        </div>

        <!-- Captura de pago -->
        <div class="col-md-6 text-center">
            <p class="label-destacada">Captura de pago</p>
            <?php if (!empty($transaction['img_route'])): ?>
                <img src="<?= SERVERURL . htmlspecialchars($transaction['img_route']) ?>" alt="Captura" class="img-fluid captura-img">
            <?php else: ?>
                <div class="border p-4 text-muted">No hay captura adjunta</div>
            <?php endif; ?>
            <div class="mt-3">
                <p><strong>Monto:</strong> <?= htmlspecialchars($transaction['amount'] ?? 0) ?>$</p>
                <p><strong>Método:</strong> <?= htmlspecialchars($transaction['payment_method'] ?? 'N/A') ?></p>
                <?php if (!empty($transaction['reference'])): ?>
                    <p><strong>Referencia:</strong> <?= htmlspecialchars($transaction['reference']) ?></p>
                <?php endif; ?>
            </div>
        </div>
    </div>

 
    <?php if ($puedeAceptarse): ?>
    <form id="form-aceptar-solicitud"
          class="FormularioAjax"
          action="<?= SERVERURL ?>ajax/solicitudAjax.php?accion=aceptar_solicitud"
          method="POST"
          data-form="save"
          autocomplete="off">

        <input type="hidden" name="solicitud_id" value="<?= htmlspecialchars($idEnc) ?>">
        <input type="hidden" name="section_id" id="hidden_section_id" value="">
        <input type="hidden" name="new_section_name" id="hidden_new_section_name" value="">
        <input type="hidden" name="teacher_id" id="hidden_teacher_id" value="">
        <input type="hidden" name="section_day" id="hidden_section_day" value="">   

        <div class="card mb-3 p-3">
            <h5 class="label-destacada">Sección para inscribir</h5>

            <?php if (empty($secciones)): ?>
                <div class="alert alert-info">No hay secciones creadas. Puedes crear una nueva.</div>
            <?php endif; ?>

            <!-- Selección de sección existente -->
            <div class="row mb-3">
                <div class="col-md-8">
                    <label>Sección existente</label>
                    <select id="select-seccion" class="form-control" <?= empty($secciones) ? 'disabled' : '' ?>>
                        <option value="">-- Seleccione una sección --</option>
                        <?php foreach ($secciones as $sec): ?>
                            <option value="<?= (int) $sec['id'] ?>">
                                Sección <?= htmlspecialchars($sec['name']) ?> (Inscritos: <?= (int) $sec['inscritos'] ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex align-items-end">
                    <button type="button" class="btn btn-info btn-block" id="btn-toggle-nueva">
                        <i class="fas fa-plus"></i> Crear nueva sección
                    </button>
                </div>
            </div>

            <!-- Bloque para crear nueva sección (oculto por defecto) -->
            <div id="bloque-nueva-seccion" style="display:none;">
                <hr>
                <h6 class="label-destacada">Nueva sección</h6>

                <div class="row">
                    <div class="col-md-6">
                        <label>Profesor (opcional)</label>
                        <select id="select-profesor" class="form-control" style="width:100%;"></select>
                        <small class="text-muted">Escriba al menos 2 caracteres para buscar.</small>
                    </div>
                    <div class="col-md-6">
                        <label>Día de la sección <span class="text-danger">*</span></label>
                        <select id="select-dia" class="form-control" >
                            <option value="">-- Seleccione un día --</option>
                            <option value="Lunes">Lunes</option>
                            <option value="Martes">Martes</option>
                            <option value="Miércoles">Miércoles</option>
                            <option value="Jueves">Jueves</option>
                            <option value="Viernes">Viernes</option>
                            <option value="Sábado">Sábado</option>
                            <option value="Domingo">Domingo</option>
                        </select>
                        <small class="text-muted">El día en que se dictará esta sección.</small>
                    </div>
                </div>
            </div>
        </div>

        <!-- Botón de aceptar -->
        <div class="text-center mb-4">
            <button type="submit" class="btn btn-success" id="btn-aceptar">
                <i class="fas fa-check"></i> Aceptar solicitud
            </button>
        </div>
    </form>
    <?php endif; ?>

    <!-- Períodos alternativos (solo lectura) -->
    <?php if (!empty($alternativos)): ?>
    <div class="card mb-3 p-3">
        <h5 class="label-destacada">Otras opciones disponibles para este curso</h5>
        <p class="text-muted">Sugerencias para ofrecer al cliente:</p>
        <table class="table table-sm">
            <thead>
                <tr><th>Período</th><th>Tipo</th><th>Estado</th><th>Inicio</th><th>Fin</th></tr>
            </thead>
            <tbody>
                <?php foreach ($alternativos as $alt): ?>
                    <tr>
                        <td><?= htmlspecialchars($alt['period_name']) ?></td>
                        <td><?= htmlspecialchars($alt['period_type']) ?></td>
                        <td><?= htmlspecialchars($alt['current_state']) ?></td>
                        <td><?= htmlspecialchars($alt['start_date']) ?></td>
                        <td><?= htmlspecialchars($alt['end_date']) ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

    <!-- RECHAZAR: solo si la solicitud está pendiente y sin duplicados -->
    <?php if ($registrationState === 2): ?>
    <div class="text-center mb-4">
        <form class="FormularioAjax"
              action="<?= SERVERURL ?>ajax/solicitudAjax.php"
              method="POST"
              data-form="rechazar">
            <input type="hidden" name="solicitud_id_rechazar" value="<?= htmlspecialchars($idEnc) ?>">
            <button type="submit" class="btn btn-danger">
                <i class="fas fa-times"></i> Rechazar solicitud
            </button>
        </form>
    </div>
    <?php endif; ?>
</div>

<!-- Librerías Select2 -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Referencias
    const formAceptar = document.getElementById("form-aceptar-solicitud");
    const btnToggle   = document.getElementById("btn-toggle-nueva");
    const bloqueNueva = document.getElementById("bloque-nueva-seccion");
    const selectProf  = document.getElementById("select-profesor");
    const selectSec   = document.getElementById("select-seccion");
    const selectDia   = document.getElementById("select-dia");       


    // Hidden inputs
    const hSectionId = document.getElementById("hidden_section_id");
    const hNewName   = document.getElementById("hidden_new_section_name");
    const hTeacherId = document.getElementById("hidden_teacher_id");
    const hDay       = document.getElementById("hidden_section_day");  


    // Secciones existentes (para calcular la siguiente letra)
    const seccionesExistentes = <?= json_encode($nombresSecciones, JSON_UNESCAPED_UNICODE) ?>;

    // Calcular siguiente letra disponible (A-Z)
    function calcularSiguienteLetra() {
        for (let i = 0; i < 26; i++) {
            const letra = String.fromCharCode(65 + i);
            if (!seccionesExistentes.includes(letra)) {
                return letra;
            }
        }
        return null;
    }

    // Toggle del bloque "nueva sección"
    if (btnToggle) {
        btnToggle.addEventListener("click", () => {
            const visible = bloqueNueva.style.display !== "none";
            bloqueNueva.style.display = visible ? "none" : "block";
            btnToggle.innerHTML = visible
                ? '<i class="fas fa-plus"></i> Crear nueva sección'
                : '<i class="fas fa-times"></i> Cancelar';

            // Si se muestra el bloque, limpiar el select de sección existente
            if (!visible) {
                selectSec.value = "";
                selectDia.value = "";   
            }
        });
    }

    // Select2 para búsqueda de profesor
    if (window.jQuery && $('#select-profesor').length) {
        $('#select-profesor').select2({
            placeholder: "Buscar profesor...",
            allowClear: true,
            minimumInputLength: 2,
            language: {
                inputTooShort: () => "Ingrese 2 o más caracteres",
                noResults: () => "Sin resultados"
            },
            ajax: {
                url: "<?= SERVERURL ?>ajax/select2Ajax.php?accion=buscar_profesor",
                dataType: 'json',
                delay: 250,
                data: params => ({ q: params.term }),
                processResults: data => ({
                    results: data.map(p => ({
                        id: p.id,
                        text: `${p.name} ${p.last_name}`
                    }))
                }),
                cache: true
            }
        });
    }

    // Antes del submit: llenar hidden inputs    
    
    if (formAceptar) {
        formAceptar.addEventListener("submit", (e) => {
            // Limpiar todos los hidden
            hSectionId.value = "";
            hNewName.value = "";
            hTeacherId.value = "";
            hDay.value = "";

            const usaNueva = bloqueNueva.style.display !== "none";

            if (usaNueva) {
                const nuevaLetra = calcularSiguienteLetra();
                if (!nuevaLetra) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    Swal.fire("Sin letras", "No hay más letras disponibles (A-Z)", "error");
                    return;
                }

                if (!selectDia.value) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    Swal.fire("Falta día", "Seleccione el día en que se dictará la sección", "warning");
                    return;
                }

                hNewName.value   = nuevaLetra;
                hTeacherId.value = selectProf ? (selectProf.value || "") : "";
                hDay.value       = selectDia.value;

            } else if (selectSec.value) {
                hSectionId.value = selectSec.value;

            } else {
                e.preventDefault();
                e.stopImmediatePropagation();
                Swal.fire("Falta sección", "Seleccione o cree una sección primero", "warning");
                return;
            }
        }, { capture: true }); 
    }
    
    
    
    
    
});
</script>


<!-- Cargar Select2 después de jQuery ->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script-->



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
