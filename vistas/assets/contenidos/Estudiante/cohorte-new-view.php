<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<!--php
$ctrl = new cohorteControlador();
$profesoresAll = $ctrl->listar_profesores_controlador();
?>

<script>
  const profesoresData = <?php echo json_encode($profesoresAll); ?>;
</script>


<script>
  const cursosData = <php
    require_once "./controladores/cohorteControlador.php";
    $ctrl = new cohorteControlador();
    $todosCursos = $ctrl->listar_cursos_controlador_para_cohorte(); // debe devolver un array con todos los cursos
    echo json_encode($todosCursos);
  ?>;
</script-->

<script>
  const cursosData = <?php
    require_once "./controladores/cohorteControlador.php";
    $ctrl = new cohorteControlador();
    echo json_encode($ctrl->listar_cursos_controlador_para_cohorte());
  ?>;

  const SERVERURL = "<?php echo SERVERURL; ?>";
 
  const profesoresData = <?php
    echo json_encode($ctrl->listar_profesores_controlador());
  ?>;
</script>

<style>
  .is-invalid {
    border: 1px solid #dc3545 !important;
  }
  select.form-select {
  max-height: 150px;
  overflow-y: auto;
}
</style>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE
    </h3>
    <p class="text-justify">
        Registra un nuevo trimestre académico. Selecciona sus fechas, tipo, estado y cursos activos para esta cohorte.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES</a>
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cohorteAjax.php" method="POST" data-form="save" autocomplete="off">
        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Información básica</legend>
            <div class="text-danger small mb-2" id="alerta-cohorte-unico" style="display:none;"></div>
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_tipo" class="bmd-label-floating">Tipo de cohorte</label>
                            <select class="form-control" name="cohorte_tipo_reg" id="cohorte_tipo" required>
                                <option value="" selected disabled>Seleccione una opción</option>
                                <option value="Intensivo">Intensivo</option>
                                <option value="Extendido">Extendido</option>
                                <option value="Técnico Profesional">Técnico Profesional</option>
                                <option value="Otro">Otro</option>

                            </select>
                        </div>
                    </div>
                    <!--div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_estado" class="bmd-label-floating">Estado</label>
                            <select class="form-control" name="cohorte_estado_reg" id="cohorte_estado" required>
                                <option value="" selected disabled>Seleccione una opción</option>
                                <option value="Vigente">Vigente</option>
                                <option value="Próximo">Próximo</option>
                                <option value="Terminado">Terminado</option>
                            </select>
                        </div>
                    </div-->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_numero" class="bmd-label-floating">N° Cohorte</label>
                            <input type="number" class="form-control" name="cohorte_numero_reg" id="cohorte_numero" min="1" max="4" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_anio" class="bmd-label-floating">Año</label>
                            <input type="number" class="form-control" name="cohorte_anio_reg" id="cohorte_anio" min="24" max="99" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <fieldset>
            <legend><i class="fas fa-calendar-day fa-fw"></i> &nbsp; Fechas de la cohorte</legend>
            <div class="text-danger small mb-2" id="alerta-fecha-inicio" style="display:none;"></div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_inicio" class="bmd-label-floating">Fecha de inicio</label>
                            <input type="date" class="form-control" name="cohorte_fecha_inicio_reg" id="cohorte_fecha_inicio" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_fin" class="bmd-label-floating">Fecha de fin</label>
                            <input type="date" class="form-control" name="cohorte_fecha_fin_reg" id="cohorte_fecha_fin" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
<div class="col-md-4">
  <div class="form-group">
    <label class="bmd-label-floating">Fechas del último cohorte que coincide con el tipo</label>
    <p class="text-muted small" id="fecha_inicio_previa"></p>
    <p class="text-muted small" id="fecha_fin_previa"></p>
  </div>
</div>
        <br><br>

<fieldset>
    <legend><i class="fas fa-plus-circle"></i> &nbsp; Curso(s) vigente(s) asignado(s)</legend>

    <div class="text-danger small mb-2" id="alerta-cursos-vigentes" style="display:none;">
        Los cursos deben ser del mismo tipo que el cohorte y no pueden haber 2 cursos iguales en 1 cohorte.
    </div>


    <div id="curso-vigente-contenedor"></div>

    <p class="text-end">
        <button type="button" id="agregarCursoVigente" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus"></i> Agregar curso vigente
        </button>
    </p>



</fieldset>

<br><br>

        <fieldset>
            <legend><i class="fas fa-dollar-sign fa-fw"></i> &nbsp; Fechas y montos de pagos</legend>
            <div id="alerta-fechas-pago" class="text-danger small mb-2" style="display:none;"></div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_art13" class="bmd-label-floating">Pago art.13</label>
                            <input type="number" class="form-control" name="cohorte_pago_art13_reg" id="cohorte_pago_art13" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_art13" class="bmd-label-floating">Fecha pago art.13</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_art13_reg" id="cohorte_fecha_pago_art13" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_aranceles" class="bmd-label-floating">Pago aranceles</label>
                            <input type="number" class="form-control" name="cohorte_pago_aranceles_reg" id="cohorte_pago_aranceles" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_aranceles" class="bmd-label-floating">Fecha pago aranceles</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_aranceles_reg" id="cohorte_fecha_pago_aranceles" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>

                <br>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pago_inscripcion_c" class="form-label">Pago inscripción general</label>
                            <input type="number" class="form-control" name="cohorte_pago_inscripcion_reg" id="pago_inscripcion_c" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pago_modulo_c" class="form-label">Pago por módulo general</label>
                            <input type="number" class="form-control" name="cohorte_pago_modulo_reg" id="pago_modulo_c" required>
                        </div>
                    </div>
                <br>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo1_reg" class="bmd-label-floating">Fecha módulo 1</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo1_reg" id="cohorte_fecha_modulo1" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo2_reg" class="bmd-label-floating">Fecha módulo 2</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo2_reg" id="cohorte_fecha_modulo2" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo3_reg" class="bmd-label-floating">Fecha módulo 3</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo3_reg" id="cohorte_fecha_modulo3" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <input type="hidden" name="total_cursos_vigentes" id="total_cursos_vigentes" value="0">

        <p class="text-center" style="margin-top: 40px;">
            <!--button type="reset" class="btn btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> LIMPIAR</button-->
            &nbsp; &nbsp;



            <!--button type="submit" class="btn btn-info btn-sm"><i class="far fa-save"></i> GUARDAR</button-->

            <button type="submit" id="btn-submit-cohorte" class="btn btn-info btn-sm" disabled>
            <i class="far fa-save"></i> GUARDAR
            </button>

        </p>
    </form>
</div>



<!-- 9.8.25

    <div class="col-md-4 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
        <option value="" selected disabled>Seleccione...</option>
        ${selectHTML}
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Oferta (%)</label>
        <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" required>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Docente de la sección "A"</label>
        <select name="cursos_vigentes[${contadorCursos}][id_profesor_fk]" class="form-select select-docente" required>
        <option value="" selected disabled>Seleccione...</option>
        ${profesoresData.map(p => `
            <option value="${p.id_profesor}">${p.nombre_profesor} ${p.apellido_profesor}</option>
        `).join("")}
        </select>
    </div>


-->




<!--

    div.innerHTML = `
      <div class="col-md-8 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${selectHTML}
        </select>
      </div>
      <div class="col-md-4 mb-2">
        <label class="form-label">Oferta (%)</label>
        <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" required>
      </div>
    `;

-->


<!-- validar fecha inicio tiempo real vieja 15925


function validarFechaInicio() {
  const fechaInicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
  const alerta = document.getElementById("alerta-fecha-inicio");
  const btnSubmit = document.getElementById("btn-submit-cohorte");

  const hoy = new Date();
  const hace90dias = new Date();
  hace90dias.setDate(hoy.getDate() - 90);

  if (fechaInicio < hace90dias) {
    alerta.textContent = "La fecha de inicio no puede ser anterior a los últimos 90 días.";
    alerta.style.display = "block";
    btnSubmit.disabled = true;
  } else {
    alerta.style.display = "none";
    btnSubmit.disabled = false;
  }
}

-->


<!-- funciona pero al cambiar de tipo queda en blanco la cosa dell  select


    function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    selects.forEach(select => {
        const selectedValue = select.value; // intenta conservar la opción si aún existe
        select.innerHTML = `<option value="" disabled selected>Seleccione...</option>` +
        cursosFiltrados.map(c => `
            <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
            ${c.nombre_curso} (${c.tipo_curso})
            </option>
        `).join("");
    });
    }



este por cuestiones del boton de guardar bloqueado
function validarFechasDePagoEnRango() {
  const fechaInicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
  const fechaFin = new Date(document.getElementById("cohorte_fecha_fin").value);
  const btnSubmit = document.getElementById("btn-submit-cohorte");
  const alerta = document.getElementById("alerta-fechas-pago");

  const camposFecha = [
    "cohorte_fecha_pago_art13",
    "cohorte_fecha_pago_aranceles",
    "cohorte_fecha_modulo1",
    "cohorte_fecha_modulo2",
    "cohorte_fecha_modulo3"
  ];

  let fueraDeRango = [];

  camposFecha.forEach(id => {
    const input = document.getElementById(id);
    if (!input || !input.value) return;

    const fecha = new Date(input.value);
    if (fecha < fechaInicio || fecha > fechaFin) {
      fueraDeRango.push(id);
      input.classList.add("is-invalid");
    } else {
      input.classList.remove("is-invalid");
    }
  });

  if (fueraDeRango.length > 0) {
    alerta.textContent = "Las fechas de pago deben estar dentro del rango de inicio y fin de la cohorte.";
    alerta.style.display = "block";
    btnSubmit.disabled = true;
  } else {
    alerta.style.display = "none";
    btnSubmit.disabled = false;
  }
}




function validarCohorteUnico() {
  const tipo = document.getElementById("cohorte_tipo").value;
  const numero = document.getElementById("cohorte_numero").value;
  const anio = document.getElementById("cohorte_anio").value;
  const alerta = document.getElementById("alerta-cohorte-unico");
  const btnSubmit = document.getElementById("btn-submit-cohorte");

  if (!tipo || !numero || !anio) {
    alerta.style.display = "none";
    btnSubmit.disabled = true;
    return;
  }

  fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&numero=${numero}&anio=${anio}`)
    .then(res => res.json())
    .then(data => {
      if (!data.valido) {
        alerta.textContent = data.mensaje;
        alerta.style.display = "block";
        btnSubmit.disabled = true;
      } else {
        alerta.style.display = "none";
        btnSubmit.disabled = false;
      }
    });
}




  function validarCursosVigentes() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    const cursosSeleccionados = [];
    let valid = true;

    selects.forEach(select => {
      const cursoId = select.value;
      select.classList.remove("is-invalid");

      const cursoData = cursosData.find(c => c.id_curso == cursoId);

      if (!cursoId || !cursoData || cursoData.tipo_curso !== tipoCohorteSeleccionado || cursosSeleccionados.includes(cursoId)) {
        valid = false;
        select.classList.add("is-invalid");
      } else {
        cursosSeleccionados.push(cursoId);
      }
    });

    if (!valid) {
      alertaCursos.style.display = "block";
      btnSubmit.disabled = true;
    } else {
      alertaCursos.style.display = "none";
      btnSubmit.disabled = false;
    }
  }



-->


<script>
document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const btnSubmit = document.getElementById("btn-submit-cohorte");

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = "";
  let cursosFiltrados = [];

// Validación de cohorte único
document.getElementById("cohorte_tipo").addEventListener("change", validarCohorteUnico);
document.getElementById("cohorte_numero").addEventListener("input", validarCohorteUnico);
document.getElementById("cohorte_anio").addEventListener("input", validarCohorteUnico);

// Validación de fecha de inicio y fin
document.getElementById("cohorte_fecha_inicio").addEventListener("change", validarFechaInicio);
document.getElementById("cohorte_fecha_fin").addEventListener("change", validarFechaInicio);


//validacion de fechas dentro del rango
document.getElementById("cohorte_fecha_inicio").addEventListener("change", validarFechasDePagoEnRango);
document.getElementById("cohorte_fecha_fin").addEventListener("change", validarFechasDePagoEnRango);

["cohorte_fecha_pago_art13", "cohorte_fecha_pago_aranceles", "cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"]
  .forEach(id => {
    const input = document.getElementById(id);
    if (input) {
      input.addEventListener("change", validarFechasDePagoEnRango);
    }
  });




  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    validarCursosVigentes();
  });


  tipoCohorteInput.addEventListener("change", function () {
  tipoCohorteSeleccionado = this.value;
  cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
  actualizarOpcionesSelects();
  validarCursosVigentes();
    });






  function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");

    selects.forEach(select => {
      const selectedValue = select.value;
      const cursoSeleccionado = cursosData.find(c => c.id_curso == selectedValue);

      // Generar opciones válidas según tipo de cohorte
      let opciones = cursosFiltrados.map(c => `
        <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
          ${c.nombre_curso} (${c.tipo_curso})
        </option>
      `).join("");

      // Si el curso seleccionado ya no está en el filtro, lo agregamos manualmente
      if (selectedValue && (!cursoSeleccionado || cursoSeleccionado.tipo_curso !== tipoCohorteSeleccionado)) {
        opciones = `
          <option value="${selectedValue}" selected disabled style="color:red;">
            ${cursoSeleccionado ? cursoSeleccionado.nombre_curso : 'Curso desconocido'} (No válido para este tipo)
          </option>
        ` + opciones;
      }

      select.innerHTML = `<option value="" disabled>Seleccione...</option>` + opciones;
    });
  }









  btnAgregar.addEventListener("click", () => {
    if (!tipoCohorteSeleccionado) {
      alert("Primero selecciona el tipo de cohorte.");
      return;
    }

    if (cursosFiltrados.length === 0) {
      alert("No hay cursos disponibles para el tipo seleccionado.");
      return;
    }

    const div = document.createElement("div");
    div.className = "row border rounded p-3 mb-3 bg-light";

    const selectHTML = cursosFiltrados.map(c => `
      <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
    `).join("");


    div.innerHTML = `
        <div class="col-md-4 mb-2">
            <label class="form-label">Curso</label>
            <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
            <option value="" selected disabled>Seleccione...</option>
            ${selectHTML}
            </select>
        </div>

        <div class="col-md-4 mb-2">
            <label class="form-label">Docente de la sección "A"</label>
            <select name="cursos_vigentes[${contadorCursos}][id_profesor_fk]" class="form-select select-docente" required>
            <option value="" selected disabled>Seleccione...</option>
            ${profesoresData.map(p => `
                <option value="${p.id_profesor}">${p.nombre_profesor} ${p.apellido_profesor}</option>
            `).join("")}
            </select>
        </div>
        <div class="col-md-3 mb-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm eliminar-curso-vigente" title="Eliminar curso">
            <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `;

    contenedor.appendChild(div);

    div.querySelector(".eliminar-curso-vigente").addEventListener("click", () => {
    div.remove();
    validarCursosVigentes();
    });

    contadorCursos++;
    validarCursosVigentes();

    document.getElementById("total_cursos_vigentes").value = document.querySelectorAll(".select-curso-vigente").length;
  });

  document.addEventListener("input", e => {
    if (e.target.classList.contains("select-curso-vigente")) {
      setTimeout(validarCursosVigentes, 1000); // debounce 1s
    }
  });






function validarFormularioCohorte() {
  const btnSubmit = document.getElementById("btn-submit-cohorte");

  const errores = [
    "alerta-fecha-inicio",
    "alerta-fechas-pago",
    "alerta-cohorte-unico",
    "alerta-cursos-vigentes"
  ];

  const hayErrores = errores.some(id => {
    const el = document.getElementById(id);
    return el && el.style.display === "block";
  });

  btnSubmit.disabled = hayErrores;
}






function validarCohorteUnico() {
  const tipo = document.getElementById("cohorte_tipo").value;
  const anio = document.getElementById("cohorte_anio").value;
  const alerta = document.getElementById("alerta-cohorte-unico");
  const numeroInput = document.getElementById("cohorte_numero");
  const fechaInicioPrev = document.getElementById("fecha_inicio_previa");
  const fechaFinPrev = document.getElementById("fecha_fin_previa");
 console.log("Ejecutando validación de cohorte único…", tipo, anio);

  if (!tipo || !anio) {
    alerta.style.display = "none";
    numeroInput.value = "";
    validarFormularioCohorte();
    return;
  }

  fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&anio=${anio}`)
    .then(res => res.json())
    .then(data => {
      if (data.valido) {
        numeroInput.value = data.numero;
        alerta.style.display = "none";
      } else {
        numeroInput.value = "";
        alerta.textContent = data.mensaje;
        alerta.style.display = "block";
      }

      fechaInicioPrev.textContent = data.fecha_inicio ? `Último inicio: ${data.fecha_inicio}` : "";
      fechaFinPrev.textContent = data.fecha_fin ? `Último fin: ${data.fecha_fin}` : "";

      validarFormularioCohorte();
    });
}

/*
function validarCohorteUnico() {
  const tipo = document.getElementById("cohorte_tipo").value;
  const numero = document.getElementById("cohorte_numero").value;
  const anio = document.getElementById("cohorte_anio").value;
  const alerta = document.getElementById("alerta-cohorte-unico");

  if (!tipo || !numero || !anio) {
    alerta.style.display = "none";
    validarFormularioCohorte();
    return;
  }

  fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&numero=${numero}&anio=${anio}`)
    .then(res => res.json())
    .then(data => {
      alerta.textContent = data.valido ? "" : data.mensaje;
      alerta.style.display = data.valido ? "none" : "block";
      validarFormularioCohorte();
    });
}*/



function validarFechasDePagoEnRango() {
  const fechaInicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
  const fechaFin = new Date(document.getElementById("cohorte_fecha_fin").value);
  const alerta = document.getElementById("alerta-fechas-pago");

  const camposFecha = [
    "cohorte_fecha_pago_art13",
    "cohorte_fecha_pago_aranceles",
    "cohorte_fecha_modulo1",
    "cohorte_fecha_modulo2",
    "cohorte_fecha_modulo3"
  ];

  let fueraDeRango = [];

  camposFecha.forEach(id => {
    const input = document.getElementById(id);
    if (!input || !input.value) return;

    const fecha = new Date(input.value);
    if (fecha < fechaInicio || fecha > fechaFin) {
      fueraDeRango.push(id);
      input.classList.add("is-invalid");
    } else {
      input.classList.remove("is-invalid");
    }
  });

  alerta.textContent = fueraDeRango.length > 0
    ? "Las fechas de pago deben estar dentro del rango de inicio y fin de la cohorte."
    : "";
  alerta.style.display = fueraDeRango.length > 0 ? "block" : "none";

  validarFormularioCohorte();
}




function validarFechaInicio() {
  const fechaInicioInput = document.getElementById("cohorte_fecha_inicio");
  const fechaFinInput = document.getElementById("cohorte_fecha_fin");
  const alerta = document.getElementById("alerta-fecha-inicio");

  const fechaInicio = new Date(fechaInicioInput.value);
  const fechaFin = new Date(fechaFinInput.value);
  const hoy = new Date();
  const hace90dias = new Date();
  hace90dias.setDate(hoy.getDate() - 90);

  let mensaje = "";

  if (fechaInicio < hace90dias) {
    mensaje = "La fecha de inicio no puede ser anterior a los últimos 90 días.";
  } else if (fechaInicio > fechaFin) {
    mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin.";
  }

  alerta.textContent = mensaje;
  alerta.style.display = mensaje ? "block" : "none";

  validarFormularioCohorte();
}












function validarCursosVigentes() {
  const selects = document.querySelectorAll(".select-curso-vigente");
  const cursosSeleccionados = [];
  const alerta = document.getElementById("alerta-cursos-vigentes");

  let valid = true;

  selects.forEach(select => {
    const cursoId = select.value;
    select.classList.remove("is-invalid");

    const cursoData = cursosData.find(c => c.id_curso == cursoId);

    if (!cursoId || !cursoData || cursoData.tipo_curso !== tipoCohorteSeleccionado || cursosSeleccionados.includes(cursoId)) {
      valid = false;
      select.classList.add("is-invalid");
    } else {
      cursosSeleccionados.push(cursoId);
    }
  });

  alerta.style.display = valid ? "none" : "block";
  validarFormularioCohorte();
}









});
</script>


<!--
        <div class="col-md-4 mb-2">
            <label class="form-label">Oferta (%)</label>
            <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" max="100" required>
        </div>
-->

<!--script>
document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  let contadorCursos = 0;

  let cursosFiltrados = [];

  tipoCohorteInput.addEventListener("change", function () {
    const tipo = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipo);
    contenedor.innerHTML = ""; // Reinicia el contenedor
    contadorCursos = 0;
  });

  btnAgregar.addEventListener("click", () => {
    if (cursosFiltrados.length === 0) {
      alert("No hay cursos disponibles para el tipo de cohorte seleccionado.");
      return;
    }

    const div = document.createElement("div");
    div.className = "row border rounded p-3 mb-3 bg-light";

    const selectHTML = cursosFiltrados.map(c => `
      <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
    `).join("");

    div.innerHTML = `
      <div class="col-md-8 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select" required>
          <option value="" selected disabled>Seleccione...</option>
          ${selectHTML}
        </select>
      </div>
      <div class="col-md-4 mb-2">
        <label class="form-label">Oferta (%)</label>
        <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" required>
      </div>
    `;

    contenedor.appendChild(div);
    contadorCursos++;
  });
});

</script-->

<!--script>
document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  let cursosData = [];
  let contadorCursos = 0;

  const tipoCohorteInput = document.getElementById("cohorte_tipo");

  tipoCohorteInput.addEventListener("change", function () {
    const tipo = this.value;
    fetch("<?php echo SERVERURL; ?>ajax/cursosPorTipo.php?tipo=" + tipo)
      .then(res => res.json())
      .then(data => {
        cursosData = data;
        contadorCursos = 0;
        contenedor.innerHTML = ""; // Limpiar contenido anterior
      });
  });

  btnAgregar.addEventListener("click", () => {
    if (cursosData.length === 0) {
      alert("No hay cursos disponibles para el tipo de cohorte seleccionado.");
      return;
    }

    const div = document.createElement("div");
    div.className = "row border rounded p-3 mb-3 bg-light";

    const selectHTML = cursosData.map(c => `
      <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
    `).join("");

    div.innerHTML = `
      <div class="col-md-8 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select" required>
          <option value="" selected disabled>Seleccione...</option>
          ${selectHTML}
        </select>
      </div>
      <div class="col-md-4 mb-2">
        <label class="form-label">Oferta (%)</label>
        <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" required>
      </div>
    `;

    contenedor.appendChild(div);
    contadorCursos++;
  });
});
</script-->
<!--script>
document.addEventListener("DOMContentLoaded", () => {
    const btnAgregar = document.getElementById("agregarCursoVigente");
    const contenedor = document.getElementById("curso-vigente-contenedor");
    let cursosData = [];
    let contadorCursos = 0;

    // Escucha el tipo de cohorte
    document.getElementById("cohorte_tipo").addEventListener("change", function () {
        const tipo = this.value;
        fetch("<?php echo SERVERURL; ?>ajax/cursosPorTipo.php?tipo=" + tipo)
            .then(res => res.json())
            .then(data => {
                cursosData = data;
            });
    });

    btnAgregar.addEventListener("click", () => {
        const div = document.createElement("div");
        div.className = "row border rounded p-3 mb-3 bg-light";

        const selectHTML = cursosData.map(c => `
            <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
        `).join("");

        div.innerHTML = `
            <div class="col-md-8 mb-2">
                <label class="form-label">Curso</label>
                <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select" required>
                    <option value="" selected disabled>Seleccione...</option>
                    ${selectHTML}
                </select>
            </div>
            <div class="col-md-4 mb-2">
                <label class="form-label">Oferta (%)</label>
                <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" required>
            </div>
        `;

        contenedor.appendChild(div);
        contadorCursos++;
    });
});
</script-->