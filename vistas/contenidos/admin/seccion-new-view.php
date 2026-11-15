<?php
    $state = $_SESSION['profile_status'] ?? 0;
    $cargo = $_SESSION['cargo_spm'] ?? 0;
    $carpeta = "admin";

    if (!in_array($cargo, [1, 2, 4]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    $periodCourseId = (int) ($_GET['period_course_id'] ?? 0);
    $periodId       = (int) ($_GET['period_id'] ?? 0);

    if ($periodCourseId <= 0 && $periodId <= 0) {
        echo '<div class="alert alert-danger">Debe especificar un período o un curso del período.</div>';
        exit();
    }

    require_once "./controladores/seccionControlador.php";
    $ctrl = new seccionControlador();

    $datos = $periodCourseId > 0
        ? $ctrl->datos_para_nueva_seccion_controlador($periodCourseId, null)
        : $ctrl->datos_para_nueva_seccion_controlador(null, $periodId);

    if (!$datos['ok']) {
        echo '<div class="alert alert-danger">' . htmlspecialchars($datos['message']) . '</div>';
        exit();
    }

    $modo    = $datos['modo'];
    $periodo = $datos['periodo'];
    $cursos  = $datos['cursos'];

    // En modo period_course, el curso viene directo
    $cursoInicial = ($modo === 'period_course' && !empty($cursos)) ? $cursos[0] : null;
?>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-layer-group fa-fw"></i> &nbsp; AGREGAR CURSO O SECCIÓN</h3>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li><a class="active" href="#"><i class="fas fa-plus"></i> NUEVA SECCIÓN</a></li>
        <li><a href="<?= SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-list"></i> LISTA DE SECCIONES</a></li>
        <li><a href="<?= SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-list'; ?>"><i class="fas fa-list"></i> LISTA DE PERIODOS</a></li>
    </ul>
</div>

<div class="container-fluid">
    <form id="form-seccion" class="form-neon FormularioAjax"
          action="<?php echo SERVERURL; ?>ajax/seccionAjax.php"
          method="POST" data-form="save" autocomplete="off">
        
        <p>
            <strong>Periodo:</strong> <?= htmlspecialchars($periodo['name']) ?> (<?= htmlspecialchars($periodo['type']) ?>)<br>
            <strong>Fechas:</strong> <?= $periodo['start_date'] ?> al <?= $periodo['end_date'] ?>
            <?php if ($cursoInicial): ?>
                <br><strong>Curso:</strong> <?= htmlspecialchars($cursoInicial['course_name']) ?>
                (<?= htmlspecialchars($cursoInicial['course_type']) ?>)
            <?php endif; ?>
        </p>

        <?php if ($modo === 'period'): ?>
            <fieldset>
                <legend><i class="fas fa-book"></i> Seleccionar curso</legend>
                <div class="form-group">
                    <label>Curso del período</label>
                    <select id="select-curso" class="form-control" required>
                        <option value="" selected disabled>Seleccione un curso...</option>
                        <?php foreach ($cursos as $c): ?>
                            <!-- El VALUE ahora es el course_id para que siempre exista -->
                            <option value="<?= (int) $c['course_id'] ?>"
                                    data-pc-id="<?= $c['period_course_id'] ?? '' ?>"
                                    data-next-letter="<?= htmlspecialchars($c['next_section_name']) ?>">
                                <?= htmlspecialchars($c['course_name'] . ' (' . $c['course_type'] . ')') ?>
                                — <?= (int) $c['total_secciones'] ?> sección(es)
                                <?= empty($c['period_course_id']) ? ' [Nuevo en este periodo]' : '' ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </fieldset>
        <?php endif; ?>

        <!-- Contenedor donde el JS inyectará los inputs, fieldset y el botón -->
        <div id="contenedor-secciones"></div>
    </form>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
// Datos inyectados desde PHP
const CURSOS_DATA   = <?= json_encode($cursos, JSON_UNESCAPED_UNICODE) ?>;
const MODO          = <?= json_encode($modo) ?>;
const CURSO_INICIAL = <?= json_encode($cursoInicial, JSON_UNESCAPED_UNICODE) ?>;
const SERVERURL_JS  = <?= json_encode(SERVERURL) ?>;
const PERIOD_ID     = <?= json_encode($periodo['id']) ?>; // Inyectamos el ID del periodo para cursos nuevos

// Helpers
function escapeHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;').replace(/'/g, '&#39;');
}

function initSelectProfesor() {
    const $sel = $('#select-profesor');
    if (!$sel.length) return;

    if ($sel.hasClass('select2-hidden-accessible')) {
        $sel.select2('destroy');
    }

    $sel.select2({
        placeholder: "Seleccione un profesor",
        allowClear: true,
        minimumInputLength: 2,
        language: { inputTooShort: () => "Por favor ingrese 2 o más caracteres" },
        ajax: {
            url: SERVERURL_JS + 'ajax/select2Ajax.php?accion=buscar_profesor',
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
}

function renderFormularioSeccion(curso) {
    const contenedor = document.getElementById('contenedor-secciones');
    
    // Evaluamos si es un curso nuevo en el periodo
    const esCursoNuevo = !curso.period_course_id;

    let html = '';

    if (curso.secciones && curso.secciones.length > 0) {
        html += `<h5>Secciones existentes (${curso.total_secciones})</h5><div class="row mb-4">`;
        curso.secciones.forEach(s => {
            const prof = s.teacher_name
                ? `${escapeHtml(s.teacher_name)} ${escapeHtml(s.teacher_last_name)}`
                : '<span class="text-muted">Sin profesor</span>';
            html += `
                <div class="col-md-6 mb-2">
                    <div class="p-2" style="background:inherit; box-shadow: rgba(0,0,0,.16) 0 3px 6px;">
                        <strong>Sección ${escapeHtml(s.section_name)}</strong>
                        &nbsp;|&nbsp; ${escapeHtml(s.section_day || 'Sin día')}
                        &nbsp;|&nbsp; ${prof}
                    </div>
                </div>`;
        });
        html += `</div>`;
    }

    // ── Form nueva sección o aviso de límite ──
    if (curso.next_section_name === null) {
        html += `<div class="alert alert-warning">
            No se pueden crear más secciones en este curso (máximo 26 alcanzado).
        </div>`;
    } else {
        // Inputs ocultos condicionales
        let inputsOcultos = `
            <input type="hidden" name="section_name" value="${escapeHtml(curso.next_section_name)}">
        `;

        if (esCursoNuevo) {
            // Si es nuevo, enviamos el ID del periodo y el ID del curso
            inputsOcultos += `
                <input type="hidden" name="period_id_crear_pc" value="${PERIOD_ID}">
                <input type="hidden" name="course_id_crear_pc" value="${curso.course_id}">
            `;
        } else {
            // Si ya existe, enviamos el period_course_id
            inputsOcultos += `
                <input type="hidden" name="period_course_id_crear_s" value="${curso.period_course_id}">
            `;
        }

        html += `
            ${inputsOcultos}
            <fieldset>
                <legend><i class="fas fa-info-circle"></i> Nueva sección ${esCursoNuevo ? '(Creará el curso en el periodo)' : ''}</legend>
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label>Nombre de sección</label>
                            <input type="text" class="form-control"
                                   value="${escapeHtml(curso.next_section_name)}" readonly>
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
                            <select name="teacher_id" id="select-profesor" class="form-control" required></select>
                        </div>
                    </div>
                </div>
            </fieldset>

            <p class="text-center">
                <button type="submit" class="btn btn-info btn-sm">
                    <i class="far fa-save"></i> GUARDAR SECCIÓN
                </button>
            </p>
        `;
    }

    contenedor.innerHTML = html;
    initSelectProfesor();
}

// Arranque según el modo
document.addEventListener('DOMContentLoaded', () => {

    if (MODO === 'period_course' && CURSO_INICIAL) {
        renderFormularioSeccion(CURSO_INICIAL);
    } else if (MODO === 'period') {
        const selectCurso = document.getElementById('select-curso');
        if (selectCurso) {
            selectCurso.addEventListener('change', function () {
                // Buscamos el curso por course_id, ya que el value ahora es ese
                const courseId = parseInt(this.value);
                const curso = CURSOS_DATA.find(c => c.course_id === courseId);
                if (curso) {
                    renderFormularioSeccion(curso);
                }
            });
        }
    }
});
</script>




