<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    require_once "./controladores/seccionControlador.php";
    $ins_seccion = new seccionControlador();

    $cohortes = $ins_seccion->listar_cohortes_disponibles_controlador();
    $profesores = $ins_seccion->obtener_profesores_activos_controlador();
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-layer-group fa-fw"></i> &nbsp; NUEVA SECCIÓN
    </h3>
    <p class="text-justify">
        Crea una nueva sección para un curso vigente dentro de una cohorte activa. El nombre de la sección se asignará automáticamente según las existentes.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li><a href="<?php echo SERVERURL; ?>index.php?views=seccion-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA SECCIÓN</a></li>
        <li><a href="<?php echo SERVERURL; ?>index.php?views=seccion-list"><i class="fas fa-list fa-fw"></i> &nbsp; LISTA DE SECCIONES</a></li>
    </ul>   
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" data-form="save" autocomplete="off">
        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Datos de la sección</legend>
            <div class="container-fluid">
                <div class="row">

                    <!-- Select de cohorte -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_cohorte" class="bmd-label-floating">Cohorte</label>
                            <select name="id_cohorte" id="id_cohorte" class="form-control" required>
                                <option value="" selected disabled>Seleccione una cohorte</option>
                                <?php foreach($cohortes as $ch){ ?>
                                    <option value="<?php echo $ch['id_cohorte']; ?>">
                                        <?php echo $ch['numero_cohorte']." / ".$ch['anio_cohorte']." (".$ch['tipo_cohorte'].")"; ?>
                                    </option>
                                <?php } ?>
                            </select>
                            <small id="resumen-cohorte" class="form-text text-muted mt-1"></small>
                        </div>
                    </div>

                    <!-- Select de curso vigente -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_curso_vigente" class="bmd-label-floating">Curso vigente</label>
                            <select name="id_curso_vigente" id="id_curso_vigente" class="form-control" required>
                                <option value="" selected disabled>Seleccione un curso</option>
                            </select>
                            <small id="resumen-curso" class="form-text text-muted mt-1"></small>
                        </div>
                    </div>

                </div>

                <div class="row mt-3">

                    <!-- Nombre de sección -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre_seccion" class="bmd-label-floating">Nombre de sección</label>
                            <input type="text" name="nombre_seccion" id="nombre_seccion" class="form-control" readonly required>
                        </div>
                    </div>

                    <!-- Profesor -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_profesor_fk" class="bmd-label-floating">Profesor asignado</label>
                            <select name="id_profesor_fk" id="id_profesor_fk" class="form-control select2" required>
                                <option value="" selected disabled>Seleccione un profesor</option>
                                <?php foreach($profesores as $p){ ?>
                                    <option value="<?php echo $p['id_profesor']; ?>">
                                        <?php echo $p['nombre_profesor']." ".$p['apellido_profesor']; ?>
                                    </option>
                                <?php } ?>
                            </select>
                        </div>
                    </div>

                </div>
            </div>
        </fieldset>

        <p class="text-center" style="margin-top: 40px;">
            <button type="reset" class="btn btn-raised btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> &nbsp; LIMPIAR</button>
            &nbsp; &nbsp;
            <button type="submit" class="btn btn-raised btn-success btn-sm" id="btn-submit-seccion" disabled>
                <i class="far fa-save"></i> &nbsp; GUARDAR SECCIÓN
            </button>
        </p>
    </form>
</div>




<script>
document.addEventListener("DOMContentLoaded", () => {
  const selectCohorte = document.getElementById("id_cohorte");
  const selectCursoVigente = document.getElementById("id_curso_vigente");
  const nombreSeccionInput = document.getElementById("nombre_seccion");
  const btnSubmit = document.getElementById("btn-submit-seccion");
  const resumenCohorte = document.getElementById("resumen-cohorte");
  const resumenCurso = document.getElementById("resumen-curso");

  const letras = "ABCDEFGHIJKLMNOPQRSTUVWXYZ";

  selectCohorte.addEventListener("change", () => {
    const cohorteId = selectCohorte.value;
    const cohorteTexto = selectCohorte.selectedOptions[0].textContent;
    resumenCohorte.textContent = `Cohorte seleccionada: ${cohorteTexto}`;

    selectCursoVigente.innerHTML = `<option value="" disabled selected>Cargando cursos...</option>`;
    nombreSeccionInput.value = "";
    resumenCurso.textContent = "";
    btnSubmit.disabled = true;

    fetch(`<?php echo SERVERURL; ?>ajax/seccionAjax.php?accion=cursos_vigentes&id_cohorte=${cohorteId}`)
      .then(res => res.json())
      .then(data => {
        if (data.length === 0) {
          selectCursoVigente.innerHTML = `<option value="" disabled selected>No hay cursos vigentes</option>`;
          return;
        }

        selectCursoVigente.innerHTML = `<option value="" disabled selected>Seleccione un curso</option>`;
        data.forEach(curso => {
          const texto = `${curso.nombre_curso} (${curso.total_secciones} ${curso.total_secciones == 1 ? 'sección' : 'secciones'})`;
          const option = document.createElement("option");
          option.value = curso.id_curso_vigente;
          option.textContent = texto;
          option.dataset.secciones = curso.total_secciones;
          selectCursoVigente.appendChild(option);
        });
      })
      .catch(err => {
        console.error("Error al cargar cursos vigentes:", err);
        selectCursoVigente.innerHTML = `<option value="" disabled selected>Error al cargar</option>`;
      });
  });

  selectCursoVigente.addEventListener("change", () => {
    const selected = selectCursoVigente.selectedOptions[0];
    const total = parseInt(selected.dataset.secciones || "0");
    const letra = letras[total] || "Z";

    resumenCurso.textContent = `Curso seleccionado: ${selected.textContent}`;
    nombreSeccionInput.value = letra;

    if (total >= letras.length) {
      alert("Este curso ya tiene el máximo de secciones permitidas (A-Z). No se puede crear una nueva.");
      btnSubmit.disabled = true;
    } else {
      btnSubmit.disabled = false;
    }
  });
});
</script>
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

