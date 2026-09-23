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
<script src="<?php echo SERVERURL; ?>vistas/js/miscript.js"></script>

<?php
require_once "./controladores/periodoControlador.php";
  $ctrl = new periodoControlador();
  $dr=$ctrl->obtener_todos_los_cursos_para_periodo_controlador();
  $d2r=$ctrl->obtener_todos_los_profesores_para_periodo_controlador();
 //var_dump($dr,$d2r);
//die();
?>



<script>
  const cursosData = <?php
  require_once "./controladores/periodoControlador.php";
  $ctrl = new periodoControlador();
  $cursosRespuesta = $ctrl->obtener_todos_los_cursos_para_periodo_controlador();
  echo json_encode(($cursosRespuesta['ok'] ?? false) && is_array($cursosRespuesta['data'] ?? null)
    ? $cursosRespuesta['data']
    : []);
  ?>;

  const SERVERURL = "<?php echo SERVERURL; ?>";

  const profesoresData = <?php
  $profesoresRespuesta = $ctrl->obtener_todos_los_profesores_para_periodo_controlador();
  echo json_encode(($profesoresRespuesta['ok'] ?? false) && is_array($profesoresRespuesta['data'] ?? null)
    ? $profesoresRespuesta['data']
    : []);
    
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
    <i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVO PERIODO
  </h3>
  <p class="text-justify">
    Registra un nuevo trimestre académico o curso intensivo. Selecciona sus fechas, tipo, estado y cursos activos en el
    transcurso del periodo.
  </p>
</div>

<div class="container-fluid">
  <ul class="full-box list-unstyled page-nav-tabs">
    <li>
      <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-new'; ?>"><i
          class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVO PERIODO</a>
      <!--a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a-->
    </li>
    <li>
      <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-list'; ?>"><i
          class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PERIODOS</a>
      <!--a href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES</a-->
    </li>
  </ul>
</div>

<div class="container-fluid">
  <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST"
    data-form="save" autocomplete="off">
    <fieldset>
      <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Información básica</legend>
      <div class="text-danger small mb-2" id="alerta-periodo-nombre" style="display:none;"></div>


      <div class="container-fluid">
        <div class="row">
          <div class="col-md-4">
            <div class="form-group">


              <label for="periodo_tipo" class="bmd-label-floating">Tipo de Periodo</label>
              <select class="form-control" name="periodo_tipo" id="periodo_tipo" required>
                <option value="" selected disabled>Seleccione una opción</option>
                <option value="Extendido">Extendido</option>
                <option value="Técnico Profesional">Técnico Profesional</option>
                <option value="Intensivo">Intensivo</option>

              </select>
            </div>
          </div>

          <div class="col-md-4">
            <div class="form-group">
              <label for="periodo_nombre" class="bmd-label-floating">Nombre del periodo</label> <!--pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]{0,58}"-->
              <input type="text" class="form-control" name="periodo_nombre" id="periodo_nombre" maxlength="59"
                  pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 \-/]{0,58}" required>
            </div>
          </div>

        </div>
      </div>
    </fieldset>

    <div class="col-md-4">
      <div class="form-group">
        <label class="bmd-label-floating">Último periodo del mismo tipo</label>
        <p class="text-muted small" id="periodo_previo"></p>
        <p class="text-muted small" id="fecha_inicio_previa"></p>
        <p class="text-muted small" id="fecha_fin_previa"></p>
      </div>
    </div>

    <br><br>

    <fieldset>
      <legend><i class="fas fa-calendar-day fa-fw"></i> &nbsp; Fechas de la cohorte</legend>
      <!--div class="text-danger small mb-2" id="alerta-fecha-inicio" style="display:none;"></div-->
      <div class="small mt-2" id="alerta-inicio-fin_irrepetible" style="color: #28a745; display: block;">
        <i class="fas fa-check-circle"></i> la fecha de inicio debe ser anterior a la fecha de fin
      </div>

      <div class="container-fluid">
        <div class="row">
          <div class="col-md-6">
            <div class="form-group">
              <label for="periodo_fecha_inicio" class="bmd-label-floating">Fecha de inicio</label>
              <input type="date" class="form-control" name="periodo_fecha_inicio" id="periodo_fecha_inicio"
                min="2024-01-01" required>
            </div>
          </div>
          <div class="col-md-6">
            <div class="form-group">
              <label for="periodo_fecha_fin" class="bmd-label-floating">Fecha de fin</label>
              <input type="date" class="form-control" name="periodo_fecha_fin" id="periodo_fecha_fin"
                min="2024-01-01" required>
            </div>
          </div>
        </div>
      </div>
    </fieldset>

    <br><br>

    <fieldset>
      <legend><i class="fas fa-plus-circle"></i> &nbsp; Asignar cursos al periodo</legend>

      <div class="text-danger small mb-2" id="alerta-cursos-vigentes" style="display:none;">
        Selecciona cursos válidos, sin repetir y del mismo tipo del periodo.
      </div>


      <div id="curso-vigente-contenedor"></div>

      <p class="text-end">
        <button type="button" id="agregarCursoVigente" class="btn btn-outline-primary btn-sm">
          <i class="fas fa-plus"></i> Agregar curso vigente en periodo
        </button>
      </p>



    </fieldset>

    <br><br>

    <fieldset>
      <legend><i class="fas fa-dollar-sign fa-fw"></i> &nbsp; Fechas y montos de pagos</legend>

      <div class="text-danger small mb-2" id="alerta-fechas-pago" style="display:none;"></div>

      <div class="container-fluid" id="costos_extendido" style="display:none;">
        <div class="row">
          <div class="col-md-3">
            <div class="form-group">
              <label for="periodo_pago_art13" class="bmd-label-floating">Pago art.13</label>
              <input type="number" min="1" class="form-control input-precio-ext" name="periodo_pago_art13" id="periodo_pago_art13">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="periodo_fecha_pago_art13" class="bmd-label-floating">Fecha pago art.13</label>
              <input type="date" class="form-control input-precio-ext" name="periodo_fecha_pago_art13" id="periodo_fecha_pago_art13" min="2024-01-01">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="periodo_pago_aranceles" class="bmd-label-floating">Pago aranceles</label>
              <input type="number" min="1" class="form-control input-precio-ext" name="periodo_pago_aranceles" id="periodo_pago_aranceles">
            </div>
          </div>
          <div class="col-md-3">
            <div class="form-group">
              <label for="periodo_fecha_pago_aranceles" class="bmd-label-floating">Fecha pago aranceles</label>
              <input type="date" class="form-control input-precio-ext" name="periodo_fecha_pago_aranceles" id="periodo_fecha_pago_aranceles" min="2024-01-01">
            </div>
          </div>
        </div>

        <br>
        <div class="row">
          <div class="col-md-6 mb-3">
            <label for="pago_inscripcion_c" class="form-label">Pago inscripción general</label>
            <input type="number" min="1" class="form-control input-precio-ext" name="periodo_pago_inscripcion" id="pago_inscripcion_c">
          </div>
          <div class="col-md-6 mb-3">
            <label for="pago_modulo_c" class="form-label">Pago por módulo general</label>
            <input type="number" min="1" class="form-control input-precio-ext" name="periodo_pago_modulo" id="pago_modulo_c">
          </div>
        </div>
        <br>

        <div class="row">
          <div class="col-md-4">
            <div class="form-group">
              <label for="periodo_fecha_modulo1" class="bmd-label-floating">Fecha módulo 1</label>
              <input type="date" class="form-control input-precio-ext" name="periodo_fecha_modulo1" id="periodo_fecha_modulo1" min="2024-01-01">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="periodo_fecha_modulo2" class="bmd-label-floating">Fecha módulo 2</label>
              <input type="date" class="form-control input-precio-ext" name="periodo_fecha_modulo2" id="periodo_fecha_modulo2" min="2024-01-01">
            </div>
          </div>
          <div class="col-md-4">
            <div class="form-group">
              <label for="periodo_fecha_modulo3" class="bmd-label-floating">Fecha módulo 3</label>
              <input type="date" class="form-control input-precio-ext" name="periodo_fecha_modulo3" id="periodo_fecha_modulo3" min="2024-01-01">
            </div>
          </div>
        </div>
      </div>

      <div class="container-fluid" id="costos_intensivo" style="display:none;">
        <div class="row">
              <div class="col-md-4 mb-3">
                <label for="periodo_pago_inscripcion_int" class="form-label">Pago inscripción</label>
                <input type="number" min="1" class="form-control input-precio-int" name="periodo_pago_inscripcion_int" id="periodo_pago_inscripcion_int">
                <!--input type="date" class="form-control input-precio-int mt-2" name="periodo_fecha_pago_inscripcion_int" id="periodo_fecha_pago_inscripcion_int" min="2024-01-01"-->
          </div>
              <div class="col-md-4 mb-3">
                <label for="periodo_pago_encuentro_int" class="form-label">Pago encuentro</label>
                <input type="number" min="1" class="form-control input-precio-int" name="periodo_pago_encuentro_int" id="periodo_pago_encuentro_int">
                <!--input type="date" class="form-control input-precio-int mt-2" name="periodo_fecha_pago_encuentro_int" id="periodo_fecha_pago_encuentro_int" min="2024-01-01"-->
          </div>
              <div class="col-md-4 mb-3">
                <label for="periodo_pago_titulo_int" class="form-label">Pago título</label>
                <input type="number" min="1" class="form-control input-precio-int" name="periodo_pago_titulo_int" id="periodo_pago_titulo_int">
                <!--input type="date" class="form-control input-precio-int mt-2" name="periodo_fecha_pago_titulo_int" id="periodo_fecha_pago_titulo_int" min="2024-01-01"-->
          </div>
        </div>
      </div>
    </fieldset>

    <br><br>

    <input type="hidden" name="total_cursos_vigentes" id="total_cursos_vigentes" value="0">

    <p class="text-center" style="margin-top: 40px;">
      &nbsp; &nbsp;
      <button type="submit" id="btn-submit-cohorte" class="btn btn-info btn-sm" disabled>
        <i class="far fa-save"></i> GUARDAR
      </button>
    </p>
  </form>
</div>



<script>
  // ==============================================
  // SCRIPT ESPECÍFICO PARA COHORTES (VERSIÓN INTEGRADA Y CORREGIDA)
  // ==============================================
  document.addEventListener("DOMContentLoaded", () => {
    // ==============================================
    // INICIALIZACIÓN DE ELEMENTOS
    // ==============================================
    const btnAgregar = document.getElementById("agregarCursoVigente");
    const contenedor = document.getElementById("curso-vigente-contenedor");
    const tipoPeriodoInput = document.getElementById("periodo_tipo");
    const nombrePeriodoInput = document.getElementById("periodo_nombre");
    const alertaCursos = document.getElementById("alerta-cursos-vigentes");
    const formulario = document.querySelector(".FormularioAjax");
    const SERVERURL = "<?php echo SERVERURL; ?>";

    let contadorCursos = 0;
    let tipoPeriodoSeleccionado = "";
    let cursosFiltrados = [];

    // Inicializar el validador central
    Validador.init("btn-submit-cohorte");

    const inputs = {
      fechaInicio: document.getElementById("periodo_fecha_inicio"),
      fechaFin: document.getElementById("periodo_fecha_fin"),
      pagosExtendido: ["periodo_fecha_pago_art13", "periodo_fecha_pago_aranceles", "periodo_fecha_modulo1", "periodo_fecha_modulo2", "periodo_fecha_modulo3"],
      pagosIntensivo: ["periodo_fecha_pago_inscripcion_int", "periodo_fecha_pago_encuentro_int", "periodo_fecha_pago_titulo_int"]
    };

    // ==============================================
    // LÓGICA DE INTERFAZ Y FILTRADO (CAMBIO DE TIPO)
    // ==============================================
    tipoPeriodoInput.addEventListener("change", function () {
      tipoPeriodoSeleccionado = this.value;

      const costosExtendido = document.getElementById("costos_extendido");
      const costosIntensivo = document.getElementById("costos_intensivo");
      const inputsExt = document.querySelectorAll(".input-precio-ext");
      const inputsInt = document.querySelectorAll(".input-precio-int");

      if (this.value === "Extendido" || this.value === "Técnico Profesional") {
        if (costosExtendido) costosExtendido.style.display = "block";
        if (costosIntensivo) costosIntensivo.style.display = "none";
        
        inputsExt.forEach(input => input.setAttribute("required", "required"));
        inputsInt.forEach(input => {
          input.removeAttribute("required");
          input.value = "";
          input.classList.remove("is-invalid");
        });
      } else if (this.value === "Intensivo") {
        if (costosExtendido) costosExtendido.style.display = "none";
        if (costosIntensivo) costosIntensivo.style.display = "block";
        
        inputsInt.forEach(input => input.setAttribute("required", "required"));
        inputsExt.forEach(input => {
          input.removeAttribute("required");
          input.value = "";
          input.classList.remove("is-invalid");
        });
      } else {
        if (costosExtendido) costosExtendido.style.display = "none";
        if (costosIntensivo) costosIntensivo.style.display = "none";
        inputsExt.forEach(input => input.removeAttribute("required"));
        inputsInt.forEach(input => input.removeAttribute("required"));
      }

      cursosFiltrados = cursosData.filter(c => c.type === tipoPeriodoSeleccionado);

      // 2. Sincronizar UI
      actualizarOpcionesSelects();
      validarCursosVigentes();
      obtenerUltimoPeriodo(this.value);
      validarFormulario();
    });

    // ==============================================
    // FUNCIONES DE CURSOS VIGENTES
    // ==============================================

    function actualizarOpcionesSelects() {
      const selects = document.querySelectorAll(".select-curso-vigente");
      selects.forEach(select => {
        const selectedValue = select.value;

        let opciones = cursosFiltrados.map(c => `
        <option value="${c.id}" ${selectedValue == c.id ? 'selected' : ''}>
          ${c.name} (${c.type})
        </option>
      `).join("");

        const cursoAunValido = cursosFiltrados.some(c => c.id == selectedValue);
        if (selectedValue && !cursoAunValido) {
          const cursoDataOriginal = cursosData.find(c => c.id == selectedValue);
          opciones = `<option value="${selectedValue}" selected disabled style="color:red;">
                      ${cursoDataOriginal ? cursoDataOriginal.name : 'No válido'} (Tipo incorrecto)
                    </option>` + opciones;
        }

        select.innerHTML = `<option value="" disabled ${!selectedValue ? 'selected' : ''}>Seleccione...</option>` + opciones;
      });
    }

    function validarCursosVigentes() {
      const selects = document.querySelectorAll(".select-curso-vigente");
      const cursosSeleccionados = [];
      let hayError = false;

      selects.forEach(select => {
        const cursoId = select.value;
        select.classList.remove("is-invalid");

        const cursoData = cursosData.find(c => c.id == cursoId);

        const esTipoCorrecto = cursoData?.type === tipoPeriodoSeleccionado;

        if (!cursoId || !esTipoCorrecto || cursosSeleccionados.includes(cursoId)) {
          hayError = true;
          select.classList.add("is-invalid");
        } else {
          cursosSeleccionados.push(cursoId);
        }
      });

      alertaCursos.style.display = hayError ? "block" : "none";
      Validador.set("alerta-cursos-vigentes", hayError);

      const inputTotal = document.getElementById("total_cursos_vigentes");
      if (inputTotal) inputTotal.value = selects.length;
    }

    // ==============================================
    // BOTÓN AGREGAR CURSO
    // ==============================================
    btnAgregar?.addEventListener("click", () => {
      if (!tipoPeriodoSeleccionado) {
        alert("Primero selecciona el tipo de periodo.");
        return;
      }

      const div = document.createElement("div");
      div.className = "row border rounded p-3 mb-3 bg-light";

      div.innerHTML = `
      <div class="col-md-4 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][course_id]" class="form-select select-curso-vigente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${cursosFiltrados.map(c => `
            <option value="${c.id}">${c.name} (${c.type})</option>
          `).join("")}
        </select>
      </div>

      <div class="col-md-4 mb-2">
        <label class="form-label">Docente de la sección "A"</label>
        <select name="cursos_vigentes[${contadorCursos}][teacher_id]" class="form-select select-docente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${profesoresData.map(p => `
            <option value="${p.id}">${p.name} ${p.last_name}</option>
          `).join("")}
        </select>
      </div>

      <div class="col-md-2 mb-2">
        <label class="form-label">Día de clases</label> 
        <select class="form-control" name="cursos_vigentes[${contadorCursos}][dia]" required>
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
      
      <div class="col-md-2 mb-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger btn-sm eliminar-curso-vigente">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    `;

      contenedor.appendChild(div);

      div.querySelector(".eliminar-curso-vigente").addEventListener("click", () => {
        div.remove();
        validarCursosVigentes();
      });

      div.querySelector(".select-curso-vigente").addEventListener("change", validarCursosVigentes);

      contadorCursos++;
      validarCursosVigentes();
    });

    // ==============================================
    // VALIDACIONES DE FECHAS Y AJAX
    // ==============================================

    function obtenerValoresFechas(ids) {
      return ids.map(id => document.getElementById(id)?.value).filter(Boolean);
    }

    function validarFechas() {
      const inicio = inputs.fechaInicio.value;
      const fin = inputs.fechaFin.value;
      const alerta = document.getElementById("alerta-fechas-pago");
      let mensaje = "";

      if (inicio && fin && inicio >= fin) {
        mensaje = "La fecha de inicio debe ser anterior a la fecha de fin.";
      }

      const fechasPago = tipoPeriodoSeleccionado === "Intensivo"
        ? obtenerValoresFechas(inputs.pagosIntensivo)
        : obtenerValoresFechas(inputs.pagosExtendido);
      const repetidas = new Set(fechasPago).size !== fechasPago.length;
      const fueraDeRango = inicio && fin && fechasPago.some(fecha => fecha < inicio || fecha > fin);

      if (!mensaje && repetidas) mensaje = "Las fechas de pago no pueden repetirse.";
      if (!mensaje && fueraDeRango) mensaje = "Las fechas de pago deben estar dentro del periodo.";

      if (tipoPeriodoSeleccionado !== "Intensivo") {
        const modulos = obtenerValoresFechas(["periodo_fecha_modulo1", "periodo_fecha_modulo2", "periodo_fecha_modulo3"]);
        if (!mensaje && modulos.length === 3 && !(modulos[0] < modulos[1] && modulos[1] < modulos[2])) {
          mensaje = "Las fechas de módulos deben estar en orden: 1, 2 y 3.";
        }
      }

      alerta.textContent = mensaje;
      alerta.style.display = mensaje ? "block" : "none";
      Validador.set("alerta-fechas-pago", Boolean(mensaje));
    }

    function validarFormulario() {
      validarFechas();
      validarCursosVigentes();
      Validador.set("formulario-invalido", !formulario.checkValidity());
    }

    function obtenerUltimoPeriodo(tipo) {
      if (!tipo) return;
      fetch(`${SERVERURL}ajax/periodoAjax.php?accion=obtener_ultimo_periodo&tipo=${encodeURIComponent(tipo)}`)
        .then(res => res.json())
        .then(data => {
          console.log(data);
          const ci = document.getElementById("periodo_previo");
          const fi = document.getElementById("fecha_inicio_previa");
          const ff = document.getElementById("fecha_fin_previa");
          if (data.existe) {
            if (ci) ci.textContent = `Último periodo: ${data.nombre}`;
            if (fi) fi.textContent = `Último inicio: ${data.fecha_inicio}`;
            if (ff) ff.textContent = `Último fin: ${data.fecha_fin}`;
          } else {
            if (ci) ci.textContent = "No hay periodos anteriores de este tipo"; 
            if (fi) fi.textContent = ""; 
            if (ff) ff.textContent = "";
          }
        })
        .catch(err => {
          console.error(err);
        });
    }




    function validarNombrePeriodo() {
      const nombre = nombrePeriodoInput.value.trim();
      if (!nombre) {
        Validador.set("alerta-periodo-nombre", false);
        return;
      }

      fetch(`${SERVERURL}ajax/periodoAjax.php?accion=validar_nombre_periodo&nombre=${encodeURIComponent(nombre)}`)
        .then(res => res.json())
        .then(data => {
          console.log(data);
          const alerta = document.getElementById("alerta-periodo-nombre");
          const repetido = data.repetido === true;
          // || data.valido === true;
          alerta.textContent = repetido ? "Ese nombre de periodo ya está registrado." : "";
          alerta.style.display = repetido ? "block" : "none";
          Validador.set("alerta-periodo-nombre", repetido);
        })
        .catch(() => Validador.set("alerta-periodo-nombre", true, "No se pudo validar el nombre."));
    }

    tipoPeriodoInput.addEventListener("change", validarFormulario);
    nombrePeriodoInput.addEventListener("input", () => {
      Validador.set("alerta-periodo-nombre", false);
      validarFormulario();
    });
    nombrePeriodoInput.addEventListener("blur", validarNombrePeriodo);
    inputs.fechaInicio.addEventListener("change", validarFormulario);
    inputs.fechaFin.addEventListener("change", validarFormulario);
    formulario.addEventListener("input", validarFormulario);
    [...inputs.pagosExtendido, ...inputs.pagosIntensivo].forEach(id => {
      document.getElementById(id)?.addEventListener("change", validarFormulario);
    });

    validarFormulario();
  });
</script>