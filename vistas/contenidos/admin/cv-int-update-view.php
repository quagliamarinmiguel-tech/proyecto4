<?php

    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }    
    
    require_once "./controladores/cvintControlador.php";
$ins_cvint = new cvintControlador();



//$idCursoVigenteEncriptado = $_GET['course_id_up'] ?? null;
//$idCursoVigente =  mainModel::decryption($idCursoVigenteEncriptado);

$idCursoVigente = $_GET['course_id_up'] ?? null;
$datos = $ins_cvint->obtener_datos_cv_intensivo_controlador($idCursoVigente);

if (!$datos) {
    echo "<p class='text-center text-danger'>Curso intensivo no encontrado.</p>";
    exit();
}
?>



<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-plus fa-fw"></i> &nbsp; ACTUALIZAR PERIODO DE CURSO
    </h3>
    <p class="text-justify">
        Registra un periodo intensivo y asigna su profesor.
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
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-update'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; ACTUALIZAR PERIODO INTENSIVO </a>
        </li>        
    </ul>   
</div>




<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoVigenteAjax.php" method="POST" data-form="update" autocomplete="off">
        <input type="hidden" name="cv_int_id_up" value="<?= $datos['id_curso_vigente'] ?>">
        <input type="hidden" name="id_periodo_actual" id="id_periodo_actual" value="<?= $datos['id_periodo_intensivo'] ?>">
        <input type="hidden" name="id_seccion_a" id="id_seccion_a" value="<?= $datos['id_seccion'] ?>">

        
    <legend>
        <span class="badge badge-info ml-2">ID_Periodo: <?= $datos['id_curso_vigente'] ?></span>
        <span class="badge badge-secondary ml-1">Id_Sec: <?= $datos['id_seccion'] ?></span>
    </legend>
        
        
        
        <fieldset>
            <legend><i class="fas fa-book fa-fw"></i> &nbsp; Curso y profesor</legend>
            <div class="row">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label class="bmd-label-floating">Curso</label>
                        <select class="form-control" disabled>
                            <option selected><?= htmlspecialchars($datos['nombre_curso']) ?></option>
                        </select>
                        <input type="hidden" name="curso_id_fk_up" value="<?= $datos['id_curso_fk'] ?>">
                    </div>
                </div>

                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="profesor_id_fk" class="bmd-label-floating">Profesor de la sección "A"</label>
                        <select name="profesor_id_fk" id="profesor_id_fk" class="form-control select2-ajax" required>
                            <option value="<?= $datos['ci_profesor'] ?>" selected>
                                <?= htmlspecialchars($datos['nombre_profesor'] . ' ' . $datos['apellido_profesor']) ?>
                            </option>
                        </select>
                    </div>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; Fechas y pagos</legend>
            <div class="text-danger small mt-1" id="alerta-curso" style="display:none;"></div>
            <div class="row">
                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label for="fecha_inicio_int" class="bmd-label-floating">Fecha de inicio</label>
                        <input type="date" class="form-control" name="fecha_inicio_int" id="fecha_inicio_int"
                            value="<?= $datos['fecha_inicio_int'] ?>" required>
                    </div>
                </div>

                <div class="col-12 col-md-5">
                    <div class="form-group">
                        <label for="fecha_fin_int" class="bmd-label-floating">Fecha de fin</label>
                        <input type="date" class="form-control" name="fecha_fin_int" id="fecha_fin_int"
                            value="<?= $datos['fecha_fin_int'] ?>" required>
                    </div>
                </div>
                
                <div class="form-group">
                        <label for="seccion_dia_up">Día de clases (sección A)</label>
                        <select name="seccion_dia_up" class="form-control" required>
                            <option value="" disabled>Seleccione un día</option>
                            <?php
                            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                            $dia_actual = $datos['dia'] ?? ''; 

                            foreach($dias as $dia){
                                $selected = ($dia == $dia_actual) ? 'selected' : '';
                                echo "<option value='{$dia}' {$selected}>{$dia}</option>";
                            }
                            ?>
                        </select>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="pago_inscripcion_int" class="bmd-label-floating">Pago inscripción</label>
                        <input type="number" class="form-control" name="pago_inscripcion_int" min="1"
                            value="<?= $datos['pago_inscripcion_int'] ?>" readonly>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="pago_encuentro_int" class="bmd-label-floating">Pago por encuentro</label>
                        <input type="number" class="form-control" name="pago_encuentro_int" min="1"
                            value="<?= $datos['pago_encuentro_int'] ?>" readonly>
                    </div>
                </div>

                <div class="col-12 col-md-4">
                    <div class="form-group">
                        <label for="pago_titulo_int" class="bmd-label-floating">Pago por título</label>
                        <input type="number" class="form-control" name="pago_titulo_int" min="1"
                            value="<?= $datos['pago_titulo_int'] ?>" readonly>
                    </div>
                </div>
            </div>
        </fieldset>

        <!--input type="hidden" name="nombre_seccion" value="A"-->

        <p class="text-center mt-4">
            <button type="submit" class="btn btn-raised btn-success btn-sm">
                <i class="fas fa-sync-alt"></i> &nbsp; ACTUALIZAR
            </button>
        </p>
    </form>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Select2 y validación -->
<script>
document.addEventListener("DOMContentLoaded", () => {
    const fechaInicioInput = document.getElementById("fecha_inicio_int");
    const fechaFinInput = document.getElementById("fecha_fin_int");
    const alertaCurso = document.getElementById("alerta-curso");
    const btnSubmit = document.querySelector("button[type='submit']");
    const SERVERURL = "<?= SERVERURL ?>";
    const idPeriodoActual = document.getElementById("id_periodo_actual").value;

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
        const cursoId = <?= $datos['id_curso_fk'] ?>;
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
        const hoy = new Date();
        hoy.setDate(hoy.getDate() - 1);
        hoy.setHours(0, 0, 0, 0);

        if (inicioDate > finDate) {
            alertaCurso.textContent = "La fecha de inicio no puede ser posterior a la fecha de fin.";
            alertaCurso.style.display = "block";
            validaciones.fechas = false;
            validaciones.solapamiento = false;
            actualizarEstadoBoton();
            return;
        }

      //  if (inicioDate < hoy) {
            //alertaCurso.textContent = "La fecha de inicio no puede ser anterior al día actual.";
          //  alertaCurso.style.display = "block";
        //    validaciones.fechas = false;
      //      validaciones.solapamiento = false;
    //        actualizarEstadoBoton();
  //          return;
//        }

        if (inicioDate.getTime() === finDate.getTime()) {
            alertaCurso.textContent = "La fecha de inicio y la fecha de fin no pueden ser el mismo día.";
            alertaCurso.style.display = "block";
            validaciones.fechas = false;
            validaciones.solapamiento = false;
            actualizarEstadoBoton();
            return;
        }

        validaciones.fechas = true;
		fetch(`${SERVERURL}ajax/cursoVigenteAjax.php?accion=validar_periodo_intensivo_para_actualizar&curso_id=${cursoId}&inicio=${fechaInicio}&fin=${fechaFin}&excluir=${idPeriodoActual}`)
 //     fetch(`${SERVERURL}ajax/cursoVigenteAjax.php?accion=validar_periodo_intensivo&curso_id=${cursoId}&inicio=${fechaInicio}&fin=${fechaFin}&excluir=${idPeriodoActual}`)
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

    $('#profesor_id_fk').select2({
        placeholder: "Seleccione un profesor",
        allowClear: true,
        minimumInputLength: 2,
        ajax: {
            url: "<?= SERVERURL ?>ajax/select2Ajax.php?accion=buscar_profesor",
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