<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

$cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [1 => "admin", 2 => "Control de estudio", 3 => "administrador de pagos", 4 => "Supervisor de cursos", 5 => "Estudiante"];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";

$idEnc = $_GET['periodo_id_up'] ?? '';
require_once "./controladores/periodoControlador.php";
$ctrl = new periodoControlador();
$datos = $ctrl->datos_periodo_para_update_controlador($idEnc);
if (!$datos['ok']) {
    echo '<div class="alert alert-danger text-center">No se encontró el periodo.</div>';
    exit();
}
$periodo = $datos['periodo'];
$cursosExistentes = $datos['cursos'];
$costos = $datos['costos'];

// Mapear costos por descripción
$costosMap = [];
foreach ($costos as $c) {
    $costosMap[$c['cost_description']] = $c;
}
?>

<script src="<?php echo SERVERURL; ?>vistas/js/miscript.js"></script>

<script>
    const SERVERURL = "<?php echo SERVERURL; ?>";
    const tipoPeriodo = "<?php echo $periodo['type']; ?>";
    const cursosExistentes = <?php echo json_encode($cursosExistentes); ?>;
//    const cursosData = <c?php echo json_encode($ctrl->obtener_todos_los_cursos_para_periodo_controlador()['data'] ?? []); ?>;
    const cursosData = <?php echo json_encode($ctrl->obtener_todos_los_cursos_para_periodo_controlador($periodo['type'])['data'] ?? []); ?>;
    const profesoresData = <?php echo json_encode($ctrl->obtener_todos_los_profesores_para_periodo_controlador()['data'] ?? []); ?>;
    const idreal=<?php echo htmlspecialchars($periodo['id']); ?>;
</script>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-calendar-edit fa-fw"></i> &nbsp; ACTUALIZAR PERIODO</h3>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-new'; ?>"><i class="fas fa-plus"></i> &nbsp; NUEVO PERIODO</a></li>
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-list'; ?>"><i class="fas fa-list"></i> &nbsp; LISTA DE PERIODOS</a></li>
        <li><a class="active" href="#"><i class="fas fa-sync-alt"></i> &nbsp; ACTUALIZAR PERIODO</a></li>
    </ul>
</div>


	<div id="alerta-bloqueo-edicion" class="small fw-bold" style="display:none; color: #dc3545; font-size: 1.5rem; background-color: #f8d7da; padding: 15px; border-radius: 8px; border: 1px solid #f5c6cb;">
        <i class="fas fa-exclamation-circle"></i> 
        <span id="mensaje-bloqueo"></span>
    </div>


<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" data-form="save" autocomplete="off">
        <input type="hidden" name="periodo_id_up" value="<?php echo $idEnc; ?>">

        <!-- Información básica -->
        <fieldset>
            <legend><i class="fas fa-info-circle"></i> &nbsp; Información básica</legend>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        
                        <label for="periodo_nombre" class="bmd-label-floating">Nombre del periodo</label>
                        <input type="text" class="form-control" name="periodo_nombre_up" id="periodo_nombre"
                               value="<?php echo htmlspecialchars($periodo['name']); ?>" maxlength="59" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 \-/]{0,58}" required>
                        <div class="text-danger small" id="alerta-periodo-nombre" style="display:none;"></div>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label class="bmd-label-floating">Tipo de Periodo (no modificable)</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($periodo['type']); ?>" readonly disabled>
                        <input type="hidden" name="periodo_tipo_up" value="<?php echo htmlspecialchars($periodo['type']); ?>">
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Fechas -->
        <fieldset>
            <legend><i class="fas fa-calendar-day"></i> &nbsp; Fechas del periodo</legend>
            <div class="small mt-2" id="alerta-inicio-fin_irrepetible" style="color: #28a745; display: block;">
                <i class="fas fa-check-circle"></i> La fecha de inicio debe ser anterior a la fecha de fin
            </div>
            <div class="row">
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="periodo_fecha_inicio">Fecha inicio</label>
                        <input type="date" class="form-control" name="periodo_fecha_inicio_up" id="periodo_fecha_inicio"
                               value="<?php echo $periodo['start_date']; ?>" required>
                    </div>
                </div>
                <div class="col-md-6">
                    <div class="form-group">
                        <label for="periodo_fecha_fin">Fecha fin</label>
                        <input type="date" class="form-control" name="periodo_fecha_fin_up" id="periodo_fecha_fin"
                               value="<?php echo $periodo['end_date']; ?>" required>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Costos (solo lectura) -->
        <fieldset>
            <legend><i class="fas fa-dollar-sign"></i> &nbsp; Montos de pago (solo consulta)</legend>
            <?php if ($periodo['type'] === 'Intensivo'): ?>
            <div class="row">
                <div class="col-md-4"><label>Inscripción</label><input type="number" class="form-control" name="periodo_pago_inscripcion_int_up"  value="<?php echo $costosMap['pago_inscripcion']['price'] ?? ''; ?>"></div>
                <div class="col-md-4"><label>Encuentro</label><input type="number" class="form-control" name="periodo_pago_encuentro_int_up"  value="<?php echo $costosMap['pago_encuentro']['price'] ?? ''; ?>"></div>
                <div class="col-md-4"><label>Título</label><input type="number" class="form-control" name="periodo_pago_titulo_int_up" value="<?php echo $costosMap['pago_titulo']['price'] ?? ''; ?>"></div>
            </div>
            <?php else: ?>
            <div class="row">
                <div class="col-md-3"><label>Art.13</label><input type="number" class="form-control" name="periodo_pago_art13_up" value="<?php echo $costosMap['pago_art13']['price'] ?? ''; ?>"></div>
                <div class="col-md-3"><label>Aranceles</label><input type="number" class="form-control" name="periodo_pago_aranceles_up" value="<?php echo $costosMap['pago_aranceles']['price'] ?? ''; ?>"></div>
                <div class="col-md-3"><label>Inscripción</label><input type="number" class="form-control" name="periodo_pago_inscripcion_up" value="<?php echo $costosMap['pago_inscripcion']['price'] ?? ''; ?>"></div>
                <div class="col-md-3"><label>Módulo</label><input type="number" class="form-control" name="periodo_pago_modulo_up" value="<?php echo $costosMap['pago_modulo1']['price'] ?? ''; ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-4"><label>Fecha Mod 1</label><input type="date" class="form-control" name="periodo_fecha_modulo1_up" value="<?php echo $costosMap['pago_modulo1']['cost_date'] ?? ''; ?>"></div>
                <div class="col-md-4"><label>Fecha Mod 2</label><input type="date" class="form-control" name="periodo_fecha_modulo2_up" value="<?php echo $costosMap['pago_modulo2']['cost_date'] ?? ''; ?>"></div>
                <div class="col-md-4"><label>Fecha Mod 3</label><input type="date" class="form-control" name="periodo_fecha_modulo3_up" value="<?php echo $costosMap['pago_modulo3']['cost_date'] ?? ''; ?>"></div>
            </div>
            <div class="row">
                <div class="col-md-6"><label>Fecha Art.13</label><input type="date" class="form-control" name="periodo_fecha_pago_art13_up" value="<?php echo $costosMap['pago_art13']['cost_date'] ?? ''; ?>"></div>
                <div class="col-md-6"><label>Fecha Aranceles</label><input type="date" class="form-control" name="periodo_fecha_pago_aranceles_up" value="<?php echo $costosMap['pago_aranceles']['cost_date'] ?? ''; ?>"></div>
            </div>
            <?php endif; ?>
        </fieldset>
        
        
        
        
                   <!-- if ($tipo === 'Intensivo') {
                $params['p_int_inscripcion_price'] = $valor('periodo_pago_inscripcion_int_up');
                $params['p_int_encuentro_price'] = $valor('periodo_pago_encuentro_int_up');
                $params['p_int_titulo_price'] = $valor('periodo_pago_titulo_int_up');
            } else {
                $params['p_ext_inscripcion_price'] = $valor('periodo_pago_inscripcion_up');
                $params['p_ext_modulo_price'] = $valor('periodo_pago_modulo_up');
                $params['p_ext_art13_price'] = $valor('periodo_pago_art13_up');
                $params['p_ext_aranceles_price'] = $valor('periodo_pago_aranceles_up');

                $params['p_ext_modulo1_date'] = $fecha('periodo_fecha_modulo1_up');
                $params['p_ext_modulo2_date'] = $fecha('periodo_fecha_modulo2_up');
                $params['p_ext_modulo3_date'] = $fecha('periodo_fecha_modulo3_up');
                $params['p_ext_art13_date'] = $fecha('periodo_fecha_pago_art13_up');
                $params['p_ext_aranceles_date'] = $fecha('periodo_fecha_pago_aranceles_up');
            }-->
        
        
        
        
        
        
        
        
        

        <!-- Cursos -->
        <fieldset>
            <legend><i class="fas fa-book"></i> &nbsp; Cursos en el periodo</legend>

            <div class="text-danger small mb-2" id="alerta-cursos-vigentes" style="display:none;">
                Los cursos no pueden repetirse ni coincidir con los ya existentes en el periodo.
            </div>

            <!-- Cursos existentes (readonly) -->
            <div id="cursos-existentes-contenedor">
                <?php foreach ($cursosExistentes as $curso): ?>
                <div class="row p-3 mb-3" style="background-color: #e6f2ff; border-radius:8px;">
                    <div class="col-md-4">
                        <label class="form-label text-primary">Curso</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($curso['course_name'] . ' (' . $curso['course_type'] . ')'); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Docente</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($curso['teacher_name'] ?? ''); ?>" readonly>
                    </div>
                    <div class="col-md-4">
                        <label class="form-label text-muted">Día</label>
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($curso['section_day'] ?? ''); ?>" readonly>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Nuevos cursos -->
            <div id="curso-nuevo-contenedor"></div>

            <p class="text-end">
                <button type="button" id="agregarCursoPeriodo" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i> Agregar nuevo curso
                </button>
            </p>
        </fieldset>

        <p class="text-center">
            <button type="submit" id="btn-submit-periodo" class="btn btn-info btn-sm" disabled>
                <i class="far fa-save"></i> ACTUALIZAR
            </button>
        </p>
    </form>
</div>


<script>
document.addEventListener("DOMContentLoaded", function () {
    // ==============================================
    // INICIALIZACIÓN
    // ==============================================
    Validador.init("btn-submit-periodo");

    const contenedorNuevos = document.getElementById("curso-nuevo-contenedor");
    const btnAgregar = document.getElementById("agregarCursoPeriodo");
    const alertaCursos = document.getElementById("alerta-cursos-vigentes");
    const nombreInput = document.getElementById("periodo_nombre");
    const fechaInicio = document.getElementById("periodo_fecha_inicio");
    const fechaFin = document.getElementById("periodo_fecha_fin");
    const formulario = document.querySelector(".FormularioAjax");

    // Datos desde PHP
    //const idreal = <d?php echo json_encode($idReal); ?>;
	const idreal=<?php echo htmlspecialchars($periodo['id']); ?>;
    const fechaInicioPeriodo = <?php echo json_encode($periodo['start_date']); ?>;
    const estadoPeriodo = <?php echo json_encode($periodo['current_state']); ?>;

    const cursosExistentesIds = cursosExistentes.map(c => c.course_id);
    let contadorNuevos = 0;

    // ==============================================
    // VALIDACIÓN DE BLOQUEO (10 días)
    // ==============================================
    function validarBloqueoEdicion() {
        const hoy = new Date();
        hoy.setHours(0, 0, 0, 0);
        const inicio = new Date(fechaInicioPeriodo + "T00:00:00");
        const diffTime = inicio.getTime() - hoy.getTime();
        const diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));

        const bloqueado = (diffDays <= 10 || estadoPeriodo === 'Vigente' || estadoPeriodo === 'Terminado');

        // 1. Registrar error en Validador (bloquea el botón automáticamente)
        Validador.set("alerta-bloqueo-edicion", bloqueado);

        // 2. Mostrar/ocultar el div de alerta
        const alertaDiv = document.getElementById("alerta-bloqueo-edicion");
        const mensajeSpan = document.getElementById("mensaje-bloqueo");
        if (bloqueado) {
            let mensaje = '';
            if (estadoPeriodo === 'Vigente') mensaje = 'El periodo ya está en curso.';
            else if (estadoPeriodo === 'Terminado') mensaje = 'El periodo ya ha finalizado.';
            else mensaje = `Faltan ${diffDays} días para el inicio (menos de 10 días). No se pueden realizar modificaciones.`;
            mensajeSpan.textContent = mensaje;
            alertaDiv.style.display = 'block';
        } else {
            alertaDiv.style.display = 'none';
        }

        // 3. Deshabilitar el botón "Agregar curso" manualmente (si está bloqueado)
        btnAgregar.disabled = bloqueado;
        btnAgregar.style.opacity = bloqueado ? '0.5' : '1';
        btnAgregar.style.cursor = bloqueado ? 'not-allowed' : 'pointer';

        // 4. Deshabilitar todos los campos del formulario (opcional, pero recomendado)
        formulario.querySelectorAll("input, select, textarea").forEach(el => {
            if (el.id !== "btn-submit-periodo" && el.id !== "agregarCursoPeriodo") {
                el.disabled = bloqueado;
            }
        });

        return bloqueado;
    }

    // ==============================================
    // VALIDACIÓN DE CURSOS (duplicados)
    // ==============================================
    function validarCursos() {
        if (validarBloqueoEdicion()) return; // Si está bloqueado, no validar cursos

        const selects = document.querySelectorAll(".select-curso-nuevo");
        const idsSeleccionados = [];
        let hayError = false;

        selects.forEach(select => {
            const id = parseInt(select.value);
            select.classList.remove("is-invalid");
            if (!id || cursosExistentesIds.includes(id) || idsSeleccionados.includes(id)) {
                hayError = true;
                select.classList.add("is-invalid");
            } else {
                idsSeleccionados.push(id);
            }
        });

        alertaCursos.style.display = hayError ? "block" : "none";
        Validador.set("alerta-cursos-vigentes", hayError);
    }

    // ==============================================
    // AGREGAR CURSO NUEVO
    // ==============================================
    btnAgregar.addEventListener("click", function () {
        if (btnAgregar.disabled) return;

        if (cursosData.length === 0) {
            alert("No hay cursos disponibles para agregar.");
            return;
        }

        const div = document.createElement("div");
        div.className = "row border rounded p-3 mb-3 bg-light";
        div.innerHTML = `
            <div class="col-md-4">
                <label class="form-label">Curso</label>
                <select name="cursos_vigentes[${contadorNuevos}][course_id]" class="form-select select-curso-nuevo" required>
                    <option value="">Seleccione...</option>
                    ${cursosData.map(c => `<option value="${c.id}">${c.name} (${c.type})</option>`).join('')}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Docente</label>
                <select name="cursos_vigentes[${contadorNuevos}][teacher_id]" class="form-select" required>
                    <option value="">Seleccione...</option>
                    ${profesoresData.map(p => `<option value="${p.id}">${p.name} ${p.last_name}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Día</label>
                <select name="cursos_vigentes[${contadorNuevos}][dia]" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="Lunes">Lunes</option>
                    <option value="Martes">Martes</option>
                    <option value="Miércoles">Miércoles</option>
                    <option value="Jueves">Jueves</option>
                    <option value="Viernes">Viernes</option>
                    <option value="Sábado">Sábado</option>
                    <option value="Domingo">Domingo</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm eliminar-curso-nuevo"><i class="fas fa-trash-alt"></i></button>
            </div>
        `;

        contenedorNuevos.appendChild(div);
        contadorNuevos++;

        div.querySelector(".eliminar-curso-nuevo").addEventListener("click", function () {
            div.remove();
            validarCursos();
        });

        div.querySelector(".select-curso-nuevo").addEventListener("change", validarCursos);
        validarCursos();
    });

    // ==============================================
    // VALIDACIÓN DE NOMBRE (con AJAX)
    // ==============================================
    let timeoutNombre = null;

    function validarNombrePeriodo() {
        clearTimeout(timeoutNombre);
        const nombre = nombreInput.value.trim();
        if (!nombre) {
            Validador.set("alerta-periodo-nombre", false);
            document.getElementById("alerta-periodo-nombre").style.display = "none";
            return;
        }

        timeoutNombre = setTimeout(() => {
            fetch(`${SERVERURL}ajax/periodoAjax.php?accion=validar_nombre_periodo&nombre=${encodeURIComponent(nombre)}&id=${idreal}`)
                .then(res => res.json())
                .then(data => {
                    const repetido = data.repetido === true;
                    const alerta = document.getElementById("alerta-periodo-nombre");
                    if (repetido) {
                        alerta.textContent = "Ese nombre de periodo ya está registrado.";
                        alerta.style.display = "block";
                    } else {
                        alerta.textContent = "";
                        alerta.style.display = "none";
                    }
                    Validador.set("alerta-periodo-nombre", repetido);
                })
                .catch(() => {
                    Validador.set("alerta-periodo-nombre", true);
                    const alerta = document.getElementById("alerta-periodo-nombre");
                    alerta.textContent = "Error al validar el nombre.";
                    alerta.style.display = "block";
                });
        }, 500);
    }

    nombreInput.addEventListener("input", function () {
        Validador.set("alerta-periodo-nombre", false);
        document.getElementById("alerta-periodo-nombre").style.display = "none";
        clearTimeout(timeoutNombre);
    });

    nombreInput.addEventListener("blur", validarNombrePeriodo);

    // ==============================================
    // VALIDACIÓN DE FECHAS
    // ==============================================
    function validarFechas() {
        const inicio = fechaInicio.value;
        const fin = fechaFin.value;
        const alerta = document.getElementById("alerta-inicio-fin_irrepetible");
        let hayError = false;

        if (inicio && fin && inicio >= fin) {
            hayError = true;
            alerta.style.color = "#dc3545";
            alerta.innerHTML = `<i class="fas fa-exclamation-circle"></i> La fecha de inicio debe ser anterior a la fecha de fin.`;
        } else {
            alerta.style.color = "#28a745";
            alerta.innerHTML = `<i class="fas fa-check-circle"></i> La fecha de inicio debe ser anterior a la fecha de fin.`;
        }
        Validador.set("alerta-inicio-fin_irrepetible", hayError);
    }

    fechaInicio.addEventListener("change", validarFechas);
    fechaFin.addEventListener("change", validarFechas);
    validarFechas();

    // ==============================================
    // VALIDACIÓN GENERAL
    // ==============================================
    function validarFormulario() {
        // Solo validar si no está bloqueado
        if (!Validador.hayErrores() || !Validador.obtenerErrores().includes("alerta-bloqueo-edicion")) {
            validarCursos();
            validarFechas();
        }
    }

    formulario.addEventListener("input", validarFormulario);
    formulario.addEventListener("change", validarFormulario);

    // Inicializar
    validarBloqueoEdicion();
    validarFormulario();
            });</script>

<!--script>
document.addEventListener("DOMContentLoaded", function () {
    // ==============================================
    // INICIALIZACIÓN
    // ==============================================
    Validador.init("btn-submit-periodo");

    const tipoPeriodoSeleccionado = tipoPeriodo;
    const contenedorNuevos = document.getElementById("curso-nuevo-contenedor");
    const btnAgregar = document.getElementById("agregarCursoPeriodo");
    const alertaCursos = document.getElementById("alerta-cursos-vigentes");
    const nombreInput = document.getElementById("periodo_nombre");
    const fechaInicio = document.getElementById("periodo_fecha_inicio");
    const fechaFin = document.getElementById("periodo_fecha_fin");
    const formulario = document.querySelector(".FormularioAjax");
    const idEnc = "<?php echo $idEnc; ?>"; // ID encriptado

    // IDs de cursos ya existentes para evitar duplicados
    const cursosExistentesIds = cursosExistentes.map(c => c.course_id);
    let contadorNuevos = 0;

    // ==============================================
    // VALIDACIÓN DE CURSOS (duplicados)
    // ==============================================
    function validarCursos() {
        const selects = document.querySelectorAll(".select-curso-nuevo");
        const idsSeleccionados = [];
        let hayError = false;

        selects.forEach(select => {
            const id = parseInt(select.value);
            select.classList.remove("is-invalid");
            if (!id || cursosExistentesIds.includes(id) || idsSeleccionados.includes(id)) {
                hayError = true;
                select.classList.add("is-invalid");
            } else {
                idsSeleccionados.push(id);
            }
        });

        alertaCursos.style.display = hayError ? "block" : "none";
        Validador.set("alerta-cursos-vigentes", hayError);
    }

    // ==============================================
    // AGREGAR CURSO NUEVO
    // ==============================================
    btnAgregar.addEventListener("click", function () {
        if (cursosData.length === 0) {
            alert("No hay cursos disponibles para agregar.");
            return;
        }

        const div = document.createElement("div");
        div.className = "row border rounded p-3 mb-3 bg-light";
        div.innerHTML = `
            <div class="col-md-4">
                <label class="form-label">Curso</label>
                <select name="cursos_vigentes[${contadorNuevos}][course_id]" class="form-select select-curso-nuevo" required>
                    <option value="">Seleccione...</option>
                    ${cursosData.map(c => `<option value="${c.id}">${c.name} (${c.type})</option>`).join('')}
                </select>
            </div>
            <div class="col-md-4">
                <label class="form-label">Docente</label>
                <select name="cursos_vigentes[${contadorNuevos}][teacher_id]" class="form-select" required>
                    <option value="">Seleccione...</option>
                    ${profesoresData.map(p => `<option value="${p.id}">${p.name} ${p.last_name}</option>`).join('')}
                </select>
            </div>
            <div class="col-md-3">
                <label class="form-label">Día</label>
                <select name="cursos_vigentes[${contadorNuevos}][dia]" class="form-select" required>
                    <option value="">Seleccione...</option>
                    <option value="Lunes">Lunes</option>
                    <option value="Martes">Martes</option>
                    <option value="Miércoles">Miércoles</option>
                    <option value="Jueves">Jueves</option>
                    <option value="Viernes">Viernes</option>
                    <option value="Sábado">Sábado</option>
                    <option value="Domingo">Domingo</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm eliminar-curso-nuevo"><i class="fas fa-trash-alt"></i></button>
            </div>
        `;

        contenedorNuevos.appendChild(div);
        contadorNuevos++;

        div.querySelector(".eliminar-curso-nuevo").addEventListener("click", function () {
            div.remove();
            validarCursos();
        });

        div.querySelector(".select-curso-nuevo").addEventListener("change", validarCursos);
        validarCursos();
    });

    // ==============================================
    // VALIDACIÓN DE NOMBRE (con AJAX y exclusión de ID)
    // ==============================================
    let timeoutNombre = null;

    function validarNombrePeriodo() {
        clearTimeout(timeoutNombre);
        const nombre = nombreInput.value.trim();
        if (!nombre) {
            Validador.set("alerta-periodo-nombre", false);
            // Limpiar mensaje visual
            const alerta = document.getElementById("alerta-periodo-nombre");
            alerta.textContent = "";
            alerta.style.display = "none";
            return;
        }

        // Esperar 500ms para no saturar
        timeoutNombre = setTimeout(() => {
            // Enviar ID encriptado para excluir en la validación
            fetch(`${SERVERURL}ajax/periodoAjax.php?accion=validar_nombre_periodo&nombre=${encodeURIComponent(nombre)}&id=${encodeURIComponent(idreal)}`)
                .then(res => res.json())
                .then(data => {
                console.log(data);
                    const repetido = data.repetido === true;
                    const alerta = document.getElementById("alerta-periodo-nombre");
                    if (repetido) {
                        alerta.textContent = "Ese nombre de periodo ya está registrado.";
                        alerta.style.display = "block";
                    } else {
                        alerta.textContent = "";
                        alerta.style.display = "none";
                    }
                    Validador.set("alerta-periodo-nombre", repetido);
                })
                .catch(() => {
                    Validador.set("alerta-periodo-nombre", true);
                    const alerta = document.getElementById("alerta-periodo-nombre");
                    alerta.textContent = "Error al validar el nombre.";
                    alerta.style.display = "block";
                });
        }, 500);
    }

    nombreInput.addEventListener("input", function () {
        // Limpiar error mientras escribe
        Validador.set("alerta-periodo-nombre", false);
        const alerta = document.getElementById("alerta-periodo-nombre");
        alerta.textContent = "";
        alerta.style.display = "none";
        // Reiniciar timeout de validación
        clearTimeout(timeoutNombre);
    });

    nombreInput.addEventListener("blur", validarNombrePeriodo);

    // ==============================================
    // VALIDACIÓN DE FECHAS
    // ==============================================
    function validarFechas() {
        const inicio = fechaInicio.value;
        const fin = fechaFin.value;
        const alerta = document.getElementById("alerta-inicio-fin_irrepetible");
        let hayError = false;

        if (inicio && fin && inicio >= fin) {
            hayError = true;
            alerta.style.color = "#dc3545";
            alerta.innerHTML = `<i class="fas fa-exclamation-circle"></i> La fecha de inicio debe ser anterior a la fecha de fin.`;
        } else {
            alerta.style.color = "#28a745";
            alerta.innerHTML = `<i class="fas fa-check-circle"></i> La fecha de inicio debe ser anterior a la fecha de fin.`;
        }
        Validador.set("alerta-inicio-fin_irrepetible", hayError);
    }

    fechaInicio.addEventListener("change", validarFechas);
    fechaFin.addEventListener("change", validarFechas);
    validarFechas();

    // ==============================================
    // VALIDACIÓN GENERAL DEL FORMULARIO
    // ==============================================
    function validarFormulario() {
        validarCursos();
        validarFechas();
        // Revisar si el botón debe habilitarse usando Validador.hayErrores()
        // y además verificar que el formulario sea válido según HTML5
        const hayErrores = Validador.hayErrores();
        formulario.querySelector("button[type='submit']").disabled = hayErrores || !formulario.checkValidity();
    }

    formulario.addEventListener("input", validarFormulario);
    formulario.addEventListener("change", validarFormulario);

    // Inicializar
    validarFormulario();
});
</script-->