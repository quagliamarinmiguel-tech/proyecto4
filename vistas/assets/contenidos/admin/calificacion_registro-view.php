<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>


<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once __DIR__ . "/../../../controladores/CalificacionControlador.php";

?>

<div class="container mt-5">
  <h2 class="mb-4 text-center">Registrar Calificación</h2>

  <form class="FormularioAjax" method="POST" data-form="save"
        action="<?php echo SERVERURL; ?>ajax/calificacionAjax.php" autocomplete="off">

    <!-- 1. Select de Curso -->
    <div class="form-group">
      <label for="id_curso">Curso</label>
      <select class="form-control" name="id_curso" id="id_curso" required>
        <option value="">Seleccione un curso</option>
        <!-- Se llenará con AJAX -->
      </select>
    </div>

    <!-- 2. Select de Sección -->
    <div class="form-group">
      <label for="id_seccion">Sección</label>
      <select class="form-control" name="id_seccion" id="id_seccion" disabled required>
        <option value="">Seleccione una sección</option>
      </select>
    </div>

    <!-- 3. Select de Estudiante -->
    <div class="form-group">
      <label for="id_inscripcion">Estudiante</label>
      <select class="form-control" name="id_inscripcion_reg" id="id_inscripcion" disabled required>
        <option value="">Seleccione un estudiante</option>
      </select>
    </div>

    <!-- 4. Select de Evaluación -->
    <div class="form-group">
      <label for="id_evaluacion">Evaluación</label>
      <select class="form-control" name="id_evaluacion_reg" id="id_evaluacion" disabled required>
        <option value="">Seleccione una evaluación</option>
      </select>
    </div>

    <!-- 5. Input de Puntaje -->
    <div class="form-group">
      <label for="puntaje_reg">Puntaje</label>
      <input type="number" class="form-control" name="puntaje_reg" id="puntaje_reg" min="0" max="100" required>
    </div>

    <button type="submit" class="btn btn-primary">Guardar Calificación</button>
    <div class="RespuestaAjax mt-3"></div>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const cursoSelect = document.getElementById("id_curso");
  const seccionSelect = document.getElementById("id_seccion");
  const inscripcionSelect = document.getElementById("id_inscripcion");
  const evaluacionSelect = document.getElementById("id_evaluacion");
  const puntajeInput = document.getElementById("puntaje_reg");

  // 🔹 1. Cargar cursos al iniciar
  fetch("<?php echo SERVERURL; ?>ajax/calificacioncursoAjax.php")
    .then(res => res.json())
    .then(cursos => {
      cursos.forEach(curso => {
        cursoSelect.innerHTML += `<option value="${curso.id_curso}">
          ${curso.cohorte} - ${curso.id_curso} - ${curso.nombre_curso} (${curso.tipo})
        </option>`;
      });
    });

  // 🔹 2. Al seleccionar curso → cargar secciones
  cursoSelect.addEventListener("change", () => {
    const idCurso = cursoSelect.value;
    seccionSelect.innerHTML = '<option value="">Seleccione una sección</option>';
    inscripcionSelect.innerHTML = '<option value="">Seleccione un estudiante</option>';
    evaluacionSelect.innerHTML = '<option value="">Seleccione una evaluación</option>';
    seccionSelect.disabled = true;
    inscripcionSelect.disabled = true;
    evaluacionSelect.disabled = true;

    if (idCurso) {
      fetch(`<?php echo SERVERURL; ?>ajax/calificacionseccionAjax.php?id_curso=${idCurso}`)
        .then(res => res.json())
        .then(secciones => {
          secciones.forEach(sec => {
            seccionSelect.innerHTML += `<option value="${sec.id_seccion}">${sec.nombre_seccion}</option>`;
          });
          seccionSelect.disabled = false;
        });
    }
  });

  // 🔹 3. Al seleccionar sección → cargar estudiantes inscritos
  seccionSelect.addEventListener("change", () => {
    const idSeccion = seccionSelect.value;
    inscripcionSelect.innerHTML = '<option value="">Seleccione un estudiante</option>';
    evaluacionSelect.innerHTML = '<option value="">Seleccione una evaluación</option>';
    inscripcionSelect.disabled = true;
    evaluacionSelect.disabled = true;

    if (idSeccion) {
      fetch(`<?php echo SERVERURL; ?>ajax/calificacionestudianteAjax.php?id_seccion=${idSeccion}`)
        .then(res => res.json())
        .then(estudiantes => {
          estudiantes.forEach(est => {
            inscripcionSelect.innerHTML += `<option value="${est.id_inscripcion}">
              ${est.nombre} ${est.apellido}
            </option>`;
          });
          inscripcionSelect.disabled = false;
        });
    }
  });

  // 🔹 4. Al seleccionar estudiante → cargar evaluaciones de la sección
  inscripcionSelect.addEventListener("change", () => {
    const idSeccion = seccionSelect.value;
    evaluacionSelect.innerHTML = '<option value="">Seleccione una evaluación</option>';
    evaluacionSelect.disabled = true;

    if (idSeccion) {
      fetch(`<?php echo SERVERURL; ?>ajax/calificacionesevaluacionesAjax.php?id_seccion=${idSeccion}`)
        .then(res => res.json())
        .then(evaluaciones => {
          evaluaciones.forEach(ev => {
            evaluacionSelect.innerHTML += `<option value="${ev.id_evaluacion}" data-porcentaje="${ev.porcentaje}">
              ${ev.tipo_evaluacion} - ${ev.obj} (${ev.porcentaje}%) - ${ev.fecha_evaluacion}
            </option>`;
          });
          evaluacionSelect.disabled = false;
        });
    }
  });

  // 🔹 5. Validar porcentaje al seleccionar evaluación
  evaluacionSelect.addEventListener("change", () => {
    const selected = evaluacionSelect.options[evaluacionSelect.selectedIndex];
    const porcentaje = parseFloat(selected.getAttribute("data-porcentaje") || 0);
    if (porcentaje > 50) {
      alert("⚠️ Esta evaluación supera el 50% permitido.");
      evaluacionSelect.value = "";
    }
  });

  // 🔹 6. Validar puntaje entre 0 y 100
  puntajeInput.addEventListener("input", () => {
    const val = parseFloat(puntajeInput.value);
    if (val < 0) puntajeInput.value = 0;
    if (val > 100) puntajeInput.value = 100;
  });
});
</script>