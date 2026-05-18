<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>
<style>
button[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
  box-shadow: none;
  transition: all 0.3s ease;
}
</style>
<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO
    </h3>
    <p class="text-justify">
        Registra un nuevo curso en el sistema. Completa todos los campos obligatorios para agregar el curso correctamente.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>course-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>course-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
    </ul>   
</div>



<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoAjax.php" method="POST" data-form="save" autocomplete="off">
        <fieldset>
            <legend><i class="fas fa-book fa-fw"></i> &nbsp; Información del curso</legend>
            <div class="text-danger small mt-1" id="alerta-curso" style="display:none;">
                <!-- Mensaje dinámico -->
            </div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="curso_nombre" class="bmd-label-floating">Nombre del curso</label>
                            <input type="text" pattern="[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,60}" class="form-control" name="curso_nombre_reg" id="curso_nombre" maxlength="60" required="">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">

                        <div class="form-group">
                            <label for="curso_tipo" class="bmd-label-floating">Tipo de curso</label>
                            <select class="form-control" name="curso_tipo_reg" id="curso_tipo" required="">
                                <option value="" selected="" disabled="">Seleccione una opción</option>
                                <option value="Intensivo">Intensivo</option>
                                <option value="Extendido">Extendido</option>                                
                                <option value="Técnico Profesional">Técnico Profesional</option>
                                <option value="Otro">Otro</option>
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </fieldset>

        <br><br><br>

        <fieldset>
            <!--legend><i class="fas fa-cogs fa-fw"></i> &nbsp; Configuración del curso</legend-->
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="curso_cupos" class="bmd-label-floating">Disponibilidad de cupos</label>
                            <select class="form-control" name="curso_cupos_reg" id="curso_cupos" required="">
                                <option value="" selected="" disabled="">Seleccione una opción</option>
                                <option value="1">Cupos disponibles</option>
                                <option value="0">Sin cupos disponibles</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br><br>

        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Información adicional</legend>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <p><span class="badge badge-info">Cupos disponibles</span> El curso acepta nuevas inscripciones</p>
                        <p><span class="badge badge-warning">Sin cupos disponibles</span> El curso ha alcanzado su capacidad máxima</p>
                    </div>
                </div>
            </div>
        </fieldset>

        <p class="text-center" style="margin-top: 40px;">
            <!--button type="reset" class="btn btn-raised btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> &nbsp; LIMPIAR</button-->
            &nbsp; &nbsp;
            <button type="submit" class="btn btn-raised btn-info btn-sm"><i class="far fa-save"></i> &nbsp; GUARDAR</button>
        </p>
    </form>
</div-->



<script>
document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById("curso_nombre");
  const tipoSelect = document.getElementById("curso_tipo");
  const alertaCurso = document.getElementById("alerta-curso");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  function actualizarEstiloBoton() {
    if (btnSubmit.disabled) {
      btnSubmit.classList.add("btn-disabled");
    } else {
      btnSubmit.classList.remove("btn-disabled");
    }
  }

  function validarCurso() {
    const nombre = nombreInput.value.trim();
    const tipo = tipoSelect.value;

    if (nombre.length < 4 || tipo === "") {
      alertaCurso.style.display = "none";
      btnSubmit.disabled = true;
      actualizarEstiloBoton();
      return;
    }

    fetch(`${SERVERURL}ajax/cursoAjax.php?accion=validar_curso&nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}`)
      .then(res => res.json())
      .then(data => {
        if (!data.valido) {
          alertaCurso.textContent = data.mensaje;
          alertaCurso.style.display = "block";
          btnSubmit.disabled = true;
        } else {
          alertaCurso.style.display = "none";
          btnSubmit.disabled = false;
        }
        actualizarEstiloBoton();
      })
      .catch(err => {
        console.error("Error al validar curso:", err);
        alertaCurso.textContent = "Error de conexión con el servidor.";
        alertaCurso.style.display = "block";
        btnSubmit.disabled = true;
        actualizarEstiloBoton();
      });
  }

  nombreInput.addEventListener("input", validarCurso);
  tipoSelect.addEventListener("change", validarCurso);
});
</script>
<!--script>
document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById("curso_nombre");
  const tipoSelect = document.getElementById("curso_tipo");
  const alertaCurso = document.getElementById("alerta-curso");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  function validarCurso() {
    const nombre = nombreInput.value.trim();
    const tipo = tipoSelect.value;

    if (nombre.length < 4 || tipo === "") {
      alertaCurso.style.display = "none";
      btnSubmit.disabled = true;
      return;
    }

    fetch(`${SERVERURL}ajax/cursoAjax.php?accion=validar_curso&nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}`)
      .then(res => res.json())
      .then(data => {
        if (!data.valido) {
          alertaCurso.textContent = data.mensaje;
          alertaCurso.style.display = "block";
          btnSubmit.disabled = true;
        } else {
          alertaCurso.style.display = "none";
          btnSubmit.disabled = false;
        }
      })
      .catch(err => {
        console.error("Error al validar curso:", err);
        alertaCurso.textContent = "Error de conexión con el servidor.";
        alertaCurso.style.display = "block";
        btnSubmit.disabled = true;
      });
  }

  nombreInput.addEventListener("input", validarCurso);
  tipoSelect.addEventListener("change", validarCurso);
});
</script-->

