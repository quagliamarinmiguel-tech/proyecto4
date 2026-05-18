<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

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
        <i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO PERIODO DE CURSO
    </h3>
    <p class="text-justify">
        Registra un periodo intensivo y asigna su profesor a su sección "A".
    </p>
</div>




<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS REGULARES </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS INTENSIVOS </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES </a>
        </li>
        
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-vigente-int-new'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; CREAR PERIODO INTENSIVO </a>
        </li>        
    </ul>   
</div>




<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoVigenteAjax.php" method="POST" data-form="save" autocomplete="off">
        <fieldset>
            <legend><i class="fas fa-book fa-fw"></i> &nbsp; Selección de curso y profesor</legend>
            <div class="container-fluid">
                <div class="row">
                    <!-- Select2 Curso -->
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="curso_id_fk" class="bmd-label-floating">Curso</label>
                            <select name="curso_id_fk" id="curso_id_fk" class="form-control select2-ajax" required>
                                <?php if ($curso_id && $curso_nombre): ?>
                                    <option value="<?= $curso_id ?>" selected><?= htmlspecialchars($curso_nombre) ?></option>
                                <?php endif; ?>
                            </select>
                        </div>
                    </div>

                    <!-- Select2 Profesor -->
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="profesor_id_fk" class="bmd-label-floating">Profesor de la sección A</label>
                            <select name="profesor_id_fk" id="profesor_id_fk" class="form-control select2-ajax" required></select>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <fieldset>
            <legend><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; Fechas y costos</legend>
                <p class="text-justify">
        Una vez creado el periodo intensivo no pueden cambiarse los costos
    </p>
            <div class="text-danger small mt-1" id="alerta-curso" style="display:none;">
  <!-- Mensaje dinámico -->
</div>
            <div class="container-fluid">
                <div class="row">
                    <!-- Fecha inicio -->
                    <div class="col-12 col-md-5">
                        <div class="form-group">
                            <label for="fecha_inicio_int" class="bmd-label-floating">Fecha de inicio</label>
                            <input type="date" class="form-control" name="fecha_inicio_int" id="fecha_inicio_int" min="2024-01-01" required>
                        </div>
                    </div>

                    <!-- Fecha fin -->
                    <div class="col-12 col-md-5">
                        <div class="form-group">
                            <label for="fecha_fin_int" class="bmd-label-floating">Fecha de fin</label>
                            <input type="date" class="form-control" name="fecha_fin_int" id="fecha_fin_int" min="2024-01-01" required>
                        </div>
                    </div>
                        <div class="col-md-2 mb-2 ">
                    <label class="form-label">Día de clase (sección A)</label> 
                    <select class="form-control"  name="seccion_dia_reg" id="seccion_dia_reg" required>
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
                    <!-- Pago inscripción -->
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="pago_inscripcion_int" class="bmd-label-floating">Pago inscripción</label>
                            <input type="number" class="form-control" name="pago_inscripcion_int" id="pago_inscripcion_int" min="1" required>
                        </div>
                    </div>

                    <!-- Pago encuentro -->
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="pago_encuentro_int" class="bmd-label-floating">Pago por encuentro</label>
                            <input type="number" class="form-control" name="pago_encuentro_int" id="pago_encuentro_int" min="1" required>
                        </div>
                    </div>

                    <!-- Pago título -->
                    <div class="col-12 col-md-4">
                        <div class="form-group">
                            <label for="pago_titulo_int" class="bmd-label-floating">Pago por título</label>
                            <input type="number" class="form-control" name="pago_titulo_int" id="pago_titulo_int" min="1" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <!-- Sección A oculta -->
        <input type="hidden" name="nombre_seccion" value="A">

        <p class="text-center" style="margin-top: 40px;">
            <button type="submit" class="btn btn-raised btn-info btn-sm"><i class="far fa-save"></i> &nbsp; GUARDAR</button>
        </p>
    </form>
</div>




<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Select2 AJAX Curso -->
<script>
$(document).ready(function() {
$('#curso_id_fk').select2({
  placeholder: "Seleccione un curso intensivo",
  allowClear: true,
  minimumInputLength: 2,
  ajax: {
    url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso_intensivo",
    dataType: 'json',
    delay: 250,
    data: params => ({ q: params.term }),
    processResults: data => ({
      results: data.map(c => ({
        id: c.id_curso,
        text: `${c.nombre_curso} (${c.tipo_curso})`
      }))
    }),
    cache: true
  }
});

  $('#profesor_id_fk').select2({
    placeholder: "Seleccione un profesor",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_profesor",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(p => ({
          id: p.id_profesor,
          text: `${p.nombre_profesor} ${p.apellido_profesor}`
        }))
      }),
      cache: true
    }
  });
});
</script>



<script>
document.addEventListener("DOMContentLoaded", () => {
  const fechaInicioInput = document.getElementById("fecha_inicio_int");
  const fechaFinInput = document.getElementById("fecha_fin_int");
  const cursoSelect = document.getElementById("curso_id_fk");
  const alertaCurso = document.getElementById("alerta-curso");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  const validaciones = {
    fechas: false,
    solapamiento: false
  };

  function actualizarEstadoBoton() {
    const todoValido = Object.values(validaciones).every(v => v === true);
    btnSubmit.disabled = !todoValido;
    btnSubmit.classList.toggle("btn-disabled", btnSubmit.disabled);
  }

  function validarFechasIntensivo() {
    const cursoId = $('#curso_id_fk').val();
    const fechaInicio = fechaInicioInput.value;
    const fechaFin = fechaFinInput.value;

    if (!cursoId || !fechaInicio || !fechaFin) {
      alertaCurso.style.display = "none";
      validaciones.fechas = false;
      validaciones.solapamiento = false;
      actualizarEstadoBoton();
      return;
    }

    const inicioDate = new Date(fechaInicio);
    const finDate = new Date(fechaFin);
  //  const hoy = new Date();
//    hoy.setHours(0, 0, 0, 0);
const hoy = new Date();
hoy.setDate(hoy.getDate() - 1); // retrocede un día
hoy.setHours(0, 0, 0, 0);       // limpia la hora
      
      
    if (inicioDate > finDate) {
      alertaCurso.textContent = "La fecha de inicio no puede ser posterior a la fecha de fin.";
      alertaCurso.style.display = "block";
      validaciones.fechas = false;
      validaciones.solapamiento = false;
      actualizarEstadoBoton();
      return;
    }

  //  if (inicioDate < hoy) {
//      alertaCurso.textContent = "La fecha de inicio no puede ser anterior al día actual.";
    //  alertaCurso.style.display = "block";
  //    validaciones.fechas = false;
//      validaciones.solapamiento = false;
    //  actualizarEstadoBoton();
  //    return;
//    }

    if (inicioDate.getTime() === finDate.getTime()) {
      alertaCurso.textContent = "La fecha de inicio y la fecha de fin no pueden ser el mismo día.";
      alertaCurso.style.display = "block";
      validaciones.fechas = false;
      validaciones.solapamiento = false;
      actualizarEstadoBoton();
      return;
    }

    validaciones.fechas = true;

    fetch(`${SERVERURL}ajax/cursoVigenteAjax.php?accion=validar_periodo_intensivo&curso_id=${cursoId}&inicio=${fechaInicio}&fin=${fechaFin}`)
      .then(res => res.json())
      .then(data => {
        if (!data.valido) {
          alertaCurso.textContent = data.mensaje;
          alertaCurso.style.display = "block";
          validaciones.solapamiento = false;
        } else {
          alertaCurso.style.display = "none";
          validaciones.solapamiento = true;
        }
        actualizarEstadoBoton();
      })
      .catch(err => {
        console.error("Error al validar fechas:", err);
        alertaCurso.textContent = "Error de conexión con el servidor.";
        alertaCurso.style.display = "block";
        validaciones.solapamiento = false;
        actualizarEstadoBoton();
      });
  }

  fechaInicioInput.addEventListener("change", validarFechasIntensivo);
  fechaFinInput.addEventListener("change", validarFechasIntensivo);
  $('#curso_id_fk').on('select2:select', validarFechasIntensivo);
  $('#curso_id_fk').on('select2:clear', validarFechasIntensivo);
});
</script>
