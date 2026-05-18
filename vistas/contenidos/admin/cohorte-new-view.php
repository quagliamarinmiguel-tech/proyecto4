<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
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
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-new'; ?>"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a>
            <!--a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a-->
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES</a>
            <!--a href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES</a-->
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cohorteAjax.php" method="POST" data-form="save" autocomplete="off">
        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Información básica</legend>
            <div class="text-danger small mb-2" id="alerta-cohorte-unico" style="display:none;"></div>
                                        <!------------------------------------------>
                                <div class="small mt-2" id="alerta-cohorte-unico_irrepetible" style="color: #28a745; display: block;">
                                    <i class="fas fa-check-circle"></i> No pueden haber cohortes del mismo numero, año y tipo
                                </div>
                            <!------------------------------------------>
  
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            
                          
                            <label for="cohorte_tipo" class="bmd-label-floating">Tipo de cohorte</label>
                            <select class="form-control" name="cohorte_tipo_reg" id="cohorte_tipo" required>
                                <option value="" selected disabled>Seleccione una opción</option>
                                <option value="Extendido">Extendido</option>
                                <option value="Técnico Profesional">Técnico Profesional</option>
                                <option value="Otro">Otro</option>

                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_numero" class="bmd-label-floating">N° Cohorte</label>
                            <input type="number" class="form-control" name="cohorte_numero_reg" id="cohorte_numero" min="1" max="4" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_anio" class="bmd-label-floating">Año</label>
                            <input type="number" class="form-control" name="cohorte_anio_reg" id="cohorte_anio" min="24" max="99" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4" id="contenedor_alterno" style="display:none;">
                        <div class="form-group">
                            <label for="cohorte_anio" class="bmd-label-floating">co alt</label>
                    <input type="text" class="form-control" name="cohorte_alterno" id="cohorte_alterno" maxlength="59" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]{0,58}" required>
                        </div>
                    </div>
                    
                    
                    
                </div>
            </div>
        </fieldset>
        
        <div class="col-md-4">
          <div class="form-group">
            <label class="bmd-label-floating">Fechas del último cohorte que coincide con el tipo</label>
            <p class="text-muted small" id="cohorte_previa"></p>
            <p class="text-muted small" id="fecha_inicio_previa"></p>
            <p class="text-muted small" id="fecha_fin_previa"></p>
            <p class="text-muted small" id="numero_sugerido_previa"></p>
          </div>
        </div>

        <br><br>

        <fieldset>
            <legend><i class="fas fa-calendar-day fa-fw"></i> &nbsp; Fechas de la cohorte</legend>
            <!--div class="text-danger small mb-2" id="alerta-fecha-inicio" style="display:none;"></div-->
			<div id="alerta-solapamiento-fechas" class="alert alert-warning mt-2" style="display: none;"></div>
            
            <!---------------------------------->
                <div class="small mt-2" id="alerta-inicio-fin_irrepetible" style="color: #28a745; display: block;">
                    <i class="fas fa-check-circle"></i> la fecha de inicio no puede ser posterior a la fecha de fin
                </div>

            <!---------------------------------->
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_inicio" class="bmd-label-floating">Fecha de inicio</label>
                            <input type="date" class="form-control" name="cohorte_fecha_inicio_reg" id="cohorte_fecha_inicio" min="2024-01-01" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_fin" class="bmd-label-floating">Fecha de fin</label>
                            <input type="date" class="form-control" name="cohorte_fecha_fin_reg" id="cohorte_fecha_fin" min="2024-01-01" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

<fieldset>
    <legend><i class="fas fa-plus-circle"></i> &nbsp; Asignar cursos a la cohorte</legend>

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

            <p class="text-justify">
				Una vez creada la cohorte no pueden cambiarse los precios.
            </p>
            <!--
            <div id="alerta-fechas-pago" class="text-danger small mb-2" style="display:none;"></div>
            <div id="alerta-orden-fechas" class="alert alert-warning mt-2" style="display: none;"></div>
            -->
            <!-------------------------->
            
            <div class="small mt-2" id="alerta-fechas-repetidas_irrepetible" style="color: #28a745; display: block;">
                <i class="fas fa-check-circle"></i> las fechas no pueden repetirse
            </div>
             <div class="small mt-2" id="alerta-fechas-rango_irrepetible" style="color: #28a745; display: block;">
                <i class="fas fa-exclamation-circle"></i> las fechas deben estar dentro del rango
            </div>
            
            <div class="small mt-2" id="alerta-orden-modulos_irrepetible" style="color: #28a745; display: block;">
                <i class="fas fa-check-circle"></i> las fechas de pagos mensuales deben ir una tras la otra: mod1 -> mod2 -> mod3
            </div>
            <!-------------------------->

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_art13" class="bmd-label-floating">Pago art.13</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_art13_reg" id="cohorte_pago_art13" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_art13" class="bmd-label-floating">Fecha pago art.13</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_art13_reg" id="cohorte_fecha_pago_art13" min="2024-01-01" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_aranceles" class="bmd-label-floating">Pago aranceles</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_aranceles_reg" id="cohorte_pago_aranceles"  required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_aranceles" class="bmd-label-floating">Fecha pago aranceles</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_aranceles_reg" id="cohorte_fecha_pago_aranceles" min="2024-01-01" required>
                        </div>
                    </div>
                </div>

                <br>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pago_inscripcion_c" class="form-label">Pago inscripción general</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_inscripcion_reg" id="pago_inscripcion_c" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pago_modulo_c" class="form-label">Pago por módulo general</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_modulo_reg" id="pago_modulo_c" required>
                        </div>
                    </div>
                <br>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo1_reg" class="bmd-label-floating">Fecha módulo 1</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo1_reg" id="cohorte_fecha_modulo1" min="2024-01-01"  required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo2_reg" class="bmd-label-floating">Fecha módulo 2</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo2_reg" id="cohorte_fecha_modulo2" min="2024-01-01"  required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo3_reg" class="bmd-label-floating">Fecha módulo 3</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo3_reg" id="cohorte_fecha_modulo3" min="2024-01-01"  required>
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











<script src="<?php echo SERVERURL; ?>vistas/js/miscript.js" ></script>

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
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const inputAlterno = document.getElementById("cohorte_alterno");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  // CAMBIO SEGURO: Referencia directa al ID del contenedor para evitar fallos de visibilidad
  const divAlterno = document.getElementById("contenedor_alterno");

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = "";
  let cursosFiltrados = [];

  // Inicializar el validador central
  Validador.init("btn-submit-cohorte");

  const ALERTAS = {
    rango: "alerta-fechas-rango_irrepetible",
    ordenModulos: "alerta-orden-modulos_irrepetible",
    inicioFin: "alerta-inicio-fin_irrepetible",
    fechasRepetidas: "alerta-fechas-repetidas_irrepetible",
    cohorteUnico: "alerta-cohorte-unico_irrepetible",
    fechasSolapadas: "alerta-solapamiento-fechas"
  };

  const inputs = {
    tipo: document.getElementById("cohorte_tipo"),
    numero: document.getElementById("cohorte_numero"),
    anio: document.getElementById("cohorte_anio"),
    fechaInicio: document.getElementById("cohorte_fecha_inicio"),
    fechaFin: document.getElementById("cohorte_fecha_fin"),
    pagoArt13: "cohorte_fecha_pago_art13",
    pagoAranceles: "cohorte_fecha_pago_aranceles",
    modulo1: "cohorte_fecha_modulo1",
    modulo2: "cohorte_fecha_modulo2",
    modulo3: "cohorte_fecha_modulo3"
  };

  // ==============================================
  // LÓGICA DE INTERFAZ Y FILTRADO (CAMBIO DE TIPO)
  // ==============================================
  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;

    // 1. Manejo del Cohorte Alterno ("Otro")
    if (this.value === "Otro") {
      if (divAlterno) divAlterno.style.display = "block";
      if (inputAlterno) inputAlterno.setAttribute("required", "required");
      
      // FILTRO ESPECIAL: Si es "Otro", los cursos deben ser de tipo "Extendido"
      cursosFiltrados = cursosData.filter(c => c.tipo_curso === "Extendido");
    } else {
      if (divAlterno) divAlterno.style.display = "none";
      if (inputAlterno) {
        inputAlterno.value = ""; // Limpiar el nombre escrito
        inputAlterno.removeAttribute("required");
        inputAlterno.classList.remove("is-invalid"); // Limpiar estado de error
      }
      
      // FILTRO NORMAL: Por el tipo seleccionado
      cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    }

    // 2. Sincronizar UI
    actualizarOpcionesSelects(); 
    validarCursosVigentes();     
    obtenerUltimoCohorte(this.value);
    validarCohorteUnico();
  });

  // ==============================================
  // FUNCIONES DE CURSOS VIGENTES
  // ==============================================
  
  function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    selects.forEach(select => {
      const selectedValue = select.value;
      
      let opciones = cursosFiltrados.map(c => `
        <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
          ${c.nombre_curso} (${c.tipo_curso})
        </option>
      `).join("");

      const cursoAunValido = cursosFiltrados.some(c => c.id_curso == selectedValue);
      if (selectedValue && !cursoAunValido) {
        const cursoDataOriginal = cursosData.find(c => c.id_curso == selectedValue);
        opciones = `<option value="${selectedValue}" selected disabled style="color:red;">
                      ${cursoDataOriginal ? cursoDataOriginal.nombre_curso : 'No válido'} (Tipo incorrecto)
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

      const cursoData = cursosData.find(c => c.id_curso == cursoId);
      
      const esTipoCorrecto = (tipoCohorteSeleccionado === "Otro") 
        ? (cursoData?.tipo_curso === "Extendido")
        : (cursoData?.tipo_curso === tipoCohorteSeleccionado);

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
    if(inputTotal) inputTotal.value = selects.length;
  }

  // ==============================================
  // BOTÓN AGREGAR CURSO
  // ==============================================
  btnAgregar?.addEventListener("click", () => {
    if (!tipoCohorteSeleccionado) {
      alert("Primero selecciona el tipo de cohorte.");
      return;
    }

    const div = document.createElement("div");
    div.className = "row border rounded p-3 mb-3 bg-light";

    div.innerHTML = `
      <div class="col-md-4 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${cursosFiltrados.map(c => `
            <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
          `).join("")}
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

  function validarFechasEnRango() {
    const fInicio = inputs.fechaInicio?.value;
    const fFin = inputs.fechaFin?.value;
    if (!fInicio || !fFin) return;

    const fechaI = new Date(fInicio);
    const fechaF = new Date(fFin);
    const fechasAValidar = [
      Utils.getVal(inputs.pagoArt13),
      Utils.getVal(inputs.pagoAranceles),
      Utils.getVal(inputs.modulo1),
      Utils.getVal(inputs.modulo2),
      Utils.getVal(inputs.modulo3)
    ].filter(f => f);

    const hayError = fechasAValidar.some(f => {
      const d = new Date(f);
      return d < fechaI || d > fechaF;
    });

    Validador.set(ALERTAS.rango, hayError);
  }

  function validarOrdenModulos() {
    const m1 = Utils.getVal(inputs.modulo1);
    const m2 = Utils.getVal(inputs.modulo2);
    const m3 = Utils.getVal(inputs.modulo3);
    if (!m1 || !m2 || !m3) return;

    const d1 = new Date(m1), d2 = new Date(m2), d3 = new Date(m3);
    const hayError = (d1 > d2 || d2 > d3);
    Validador.set(ALERTAS.ordenModulos, hayError);
    Utils.marcarInputError(inputs.modulo1, d1 > d2);
    Utils.marcarInputError(inputs.modulo2, d2 > d3);
  }

  function validarInicioFin() {
    Validador.validarRangoFechas("cohorte_fecha_inicio", "cohorte_fecha_fin", ALERTAS.inicioFin);
  }

  function validarFechasNoRepetidas() {
    const ids = [inputs.pagoArt13, inputs.pagoAranceles, inputs.modulo1, inputs.modulo2, inputs.modulo3];
    Validador.validarFechasUnicas(ids, ALERTAS.fechasRepetidas);
  }

  //function validarCohorteUnico() {
  //  if (!inputs.tipo?.value || !inputs.anio?.value || !inputs.numero?.value) return;
    //const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${inputs.tipo.value}&anio=${inputs.anio.value}&numero=${inputs.numero.value}&id=${document.getElementById("cohorte_id")?.value || ""}`;
  //  fetch(url).then(res => res.json()).then(dup => Validador.set(ALERTAS.cohorteUnico, dup));
//  }

    function validarCohorteUnico() {
    const tipo = inputs.tipo?.value;
    const anio = inputs.anio?.value;
    const numero = inputs.numero?.value;
    const alterno = document.getElementById("cohorte_alterno")?.value || ""; // Capturamos el nombre alterno

    if (!tipo || !anio || !numero) return;

    // Añadimos &cohorte_alterno= a la URL
    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&anio=${anio}&numero=${numero}&cohorte_alterno=${encodeURIComponent(alterno)}&id=${document.getElementById("cohorte_id")?.value || ""}`;
    
    fetch(url)
        .then(res => res.json())
        .then(dup => {
            Validador.set(ALERTAS.cohorteUnico, dup);
            // Opcional: marcar el input como inválido visualmente
            Utils.marcarInputError("cohorte_numero", dup);
        });
	}
    
  //function validarFechasSolapadas() {
    //const fI = inputs.fechaInicio?.value, fF = inputs.fechaFin?.value;
  //  if (!inputs.tipo?.value || !fI || !fF) return;

//    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${inputs.tipo.value}&fecha_inicio=${fI}&fecha_fin=${fF}&excluir=${document.getElementById("cohorte_id")?.value || ""}`;
    //fetch(url).then(res => res.json()).then(data => {
  //    Validador.set(ALERTAS.fechasSolapadas, data.solapado);
//      Utils.marcarInputError("cohorte_fecha_inicio", data.solapado);
    //  Utils.marcarInputError("cohorte_fecha_fin", data.solapado);
  //  });
//  }

    function validarFechasSolapadas() {
    const fI = inputs.fechaInicio?.value;
    const fF = inputs.fechaFin?.value;
    const tipo = inputs.tipo?.value;
    const alterno = document.getElementById("cohorte_alterno")?.value || "";

    if (!tipo || !fI || !fF) return;

    // Añadimos &cohorte_alterno= a la URL
    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${tipo}&fecha_inicio=${fI}&fecha_fin=${fF}&cohorte_alterno=${encodeURIComponent(alterno)}&excluir=${document.getElementById("cohorte_id")?.value || ""}`;
    
    fetch(url)
        .then(res => res.json())
        .then(data => {
            Validador.set(ALERTAS.fechasSolapadas, data.solapado);
            
            // Si hay solapamiento, mostramos el mensaje detallado que configuramos en el controlador
            const divAlerta = document.getElementById(ALERTAS.fechasSolapadas);
            if(data.solapado) {
                divAlerta.innerHTML = `<i class="fas fa-exclamation-triangle"></i> ${data.mensaje}`;
                divAlerta.style.display = "block";
            } else {
                divAlerta.style.display = "none";
            }

            Utils.marcarInputError("cohorte_fecha_inicio", data.solapado);
            Utils.marcarInputError("cohorte_fecha_fin", data.solapado);
        });
	}
    
    
  function obtenerUltimoCohorte(tipo) {
    if (!tipo) return;
    const id = document.getElementById("cohorte_id")?.value || "";
    const anio = document.getElementById("cohorte_anio")?.value || "";
    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=obtener_ultimo_cohorte&tipo=${tipo}&anio=${anio}&excluir=${id}`)
      .then(res => res.json())
      .then(data => {
        const ci = document.getElementById("cohorte_previa");
        const fi = document.getElementById("fecha_inicio_previa");
        const ff = document.getElementById("fecha_fin_previa");
        if (data.existe) {
          if(ci) ci.textContent = `Último cohorte: ${data.numero} - ${data.anio}`;
          if(fi) fi.textContent = `Último inicio: ${data.fecha_inicio}`;
          if(ff) ff.textContent = `Último fin: ${data.fecha_fin}`;
        } else {
          if(ci) ci.textContent = ""; if(fi) fi.textContent = ""; if(ff) ff.textContent = "";
        }
      });
  }

  // ==============================================
  // LISTENERS DE TIEMPO REAL
  // ==============================================
  inputs.numero?.addEventListener("input", validarCohorteUnico);
  inputs.anio?.addEventListener("input", validarCohorteUnico);
    
document.getElementById("cohorte_alterno")?.addEventListener("input", () => {
    validarCohorteUnico();
    validarFechasSolapadas();
});
  
  inputs.fechaInicio?.addEventListener("change", () => {
    validarInicioFin(); validarFechasEnRango(); validarFechasSolapadas();
  });
  
  inputs.fechaFin?.addEventListener("change", () => {
    validarInicioFin(); validarFechasEnRango(); validarFechasSolapadas();
  });

  [inputs.modulo1, inputs.modulo2, inputs.modulo3].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarOrdenModulos(); validarFechasNoRepetidas(); validarFechasEnRango();
    });
  });

  [inputs.pagoArt13, inputs.pagoAranceles].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarFechasNoRepetidas(); validarFechasEnRango();
    });
  });

  // ==============================================
  // ESTADO INICIAL
  // ==============================================
  // Disparamos manualmente el evento change para que la UI se ajuste al cargar
  if (tipoCohorteInput && tipoCohorteInput.value) {
    tipoCohorteInput.dispatchEvent(new Event('change'));
  }

  setTimeout(() => {
    validarCursosVigentes();
    validarFechasEnRango();
    validarInicioFin();
  }, 500);
});
</script>

<!--script>
// ==============================================
// SCRIPT ESPECÍFICO PARA COHORTES (VERSIÓN INTEGRADA CON "OTRO")
// ==============================================
document.addEventListener("DOMContentLoaded", () => {
  // ==============================================
  // INICIALIZACIÓN DE ELEMENTOS
  // ==============================================
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const inputAlterno = document.getElementById("cohorte_alterno");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  // Referencia al contenedor del input alterno (para ocultar la columna completa)
  const divAlterno = inputAlterno ? inputAlterno.closest(".col-md-4") : null;

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = "";
  let cursosFiltrados = [];

  // Inicializar el validador central
  Validador.init("btn-submit-cohorte");

  const ALERTAS = {
    rango: "alerta-fechas-rango_irrepetible",
    ordenModulos: "alerta-orden-modulos_irrepetible",
    inicioFin: "alerta-inicio-fin_irrepetible",
    fechasRepetidas: "alerta-fechas-repetidas_irrepetible",
    cohorteUnico: "alerta-cohorte-unico_irrepetible",
    fechasSolapadas: "alerta-solapamiento-fechas"
  };

  const inputs = {
    tipo: document.getElementById("cohorte_tipo"),
    numero: document.getElementById("cohorte_numero"),
    anio: document.getElementById("cohorte_anio"),
    fechaInicio: document.getElementById("cohorte_fecha_inicio"),
    fechaFin: document.getElementById("cohorte_fecha_fin"),
    pagoArt13: "cohorte_fecha_pago_art13",
    pagoAranceles: "cohorte_fecha_pago_aranceles",
    modulo1: "cohorte_fecha_modulo1",
    modulo2: "cohorte_fecha_modulo2",
    modulo3: "cohorte_fecha_modulo3"
  };

  // ==============================================
  // LÓGICA DE INTERFAZ Y FILTRADO (CAMBIO DE TIPO)
  // ==============================================
  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;

    // 1. Manejo del Cohorte Alterno ("Otro")
    if (this.value === "Otro") {
      if (divAlterno) divAlterno.style.display = "block";
      if (inputAlterno) inputAlterno.setAttribute("required", "required");
      
      // FILTRO ESPECIAL: Si es "Otro", los cursos deben ser de tipo "Extendido"
      cursosFiltrados = cursosData.filter(c => c.tipo_curso === "Extendido");
    } else {
      if (divAlterno) divAlterno.style.display = "none";
      if (inputAlterno) {
        inputAlterno.value = ""; // Limpiar el nombre escrito
        inputAlterno.removeAttribute("required");
      }
      
      // FILTRO NORMAL: Por el tipo seleccionado
      cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    }

    // 2. Sincronizar UI
    actualizarOpcionesSelects(); // Actualiza filas ya existentes
    validarCursosVigentes();     // Valida consistencia
    obtenerUltimoCohorte(this.value);
    validarCohorteUnico();
  });

  // ==============================================
  // FUNCIONES DE CURSOS VIGENTES
  // ==============================================
  
  function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    selects.forEach(select => {
      const selectedValue = select.value;
      
      // Generar nuevas opciones basadas en cursosFiltrados
      let opciones = cursosFiltrados.map(c => `
        <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
          ${c.nombre_curso} (${c.tipo_curso})
        </option>
      `).join("");

      // Mantener la opción seleccionada si ya no es válida (marcar error visual)
      const cursoAunValido = cursosFiltrados.some(c => c.id_curso == selectedValue);
      if (selectedValue && !cursoAunValido) {
        const cursoDataOriginal = cursosData.find(c => c.id_curso == selectedValue);
        opciones = `<option value="${selectedValue}" selected disabled style="color:red;">
                      ${cursoDataOriginal ? cursoDataOriginal.nombre_curso : 'No válido'} (Tipo incorrecto)
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

      const cursoData = cursosData.find(c => c.id_curso == cursoId);
      
      // El curso es inválido si: no existe, no coincide con el filtro, o está repetido
      const esTipoCorrecto = (tipoCohorteSeleccionado === "Otro") 
        ? (cursoData?.tipo_curso === "Extendido")
        : (cursoData?.tipo_curso === tipoCohorteSeleccionado);

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
    if(inputTotal) inputTotal.value = selects.length;
  }

  // ==============================================
  // BOTÓN AGREGAR CURSO
  // ==============================================
  btnAgregar?.addEventListener("click", () => {
    if (!tipoCohorteSeleccionado) {
      alert("Primero selecciona el tipo de cohorte.");
      return;
    }

    const div = document.createElement("div");
    div.className = "row border rounded p-3 mb-3 bg-light";

    div.innerHTML = `
      <div class="col-md-4 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${cursosFiltrados.map(c => `
            <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
          `).join("")}
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

  function validarFechasEnRango() {
    const fInicio = inputs.fechaInicio?.value;
    const fFin = inputs.fechaFin?.value;
    if (!fInicio || !fFin) return;

    const fechaI = new Date(fInicio);
    const fechaF = new Date(fFin);
    const fechasAValidar = [
      Utils.getVal(inputs.pagoArt13),
      Utils.getVal(inputs.pagoAranceles),
      Utils.getVal(inputs.modulo1),
      Utils.getVal(inputs.modulo2),
      Utils.getVal(inputs.modulo3)
    ].filter(f => f);

    const hayError = fechasAValidar.some(f => {
      const d = new Date(f);
      return d < fechaI || d > fechaF;
    });

    Validador.set(ALERTAS.rango, hayError);
  }

  function validarOrdenModulos() {
    const m1 = Utils.getVal(inputs.modulo1);
    const m2 = Utils.getVal(inputs.modulo2);
    const m3 = Utils.getVal(inputs.modulo3);
    if (!m1 || !m2 || !m3) return;

    const d1 = new Date(m1), d2 = new Date(m2), d3 = new Date(m3);
    const hayError = (d1 > d2 || d2 > d3);
    Validador.set(ALERTAS.ordenModulos, hayError);
    Utils.marcarInputError(inputs.modulo1, d1 > d2);
    Utils.marcarInputError(inputs.modulo2, d2 > d3);
  }

  function validarInicioFin() {
    Validador.validarRangoFechas("cohorte_fecha_inicio", "cohorte_fecha_fin", ALERTAS.inicioFin);
  }

  function validarFechasNoRepetidas() {
    const ids = [inputs.pagoArt13, inputs.pagoAranceles, inputs.modulo1, inputs.modulo2, inputs.modulo3];
    Validador.validarFechasUnicas(ids, ALERTAS.fechasRepetidas);
  }

  function validarCohorteUnico() {
    if (!inputs.tipo?.value || !inputs.anio?.value || !inputs.numero?.value) return;
    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${inputs.tipo.value}&anio=${inputs.anio.value}&numero=${inputs.numero.value}&id=${document.getElementById("cohorte_id")?.value || ""}`;
    fetch(url).then(res => res.json()).then(dup => Validador.set(ALERTAS.cohorteUnico, dup));
  }

  function validarFechasSolapadas() {
    const fI = inputs.fechaInicio?.value;
    const fF = inputs.fechaFin?.value;
    if (!inputs.tipo?.value || !fI || !fF) return;

    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${inputs.tipo.value}&fecha_inicio=${fI}&fecha_fin=${fF}&excluir=${document.getElementById("cohorte_id")?.value || ""}`;
    fetch(url).then(res => res.json()).then(data => {
      Validador.set(ALERTAS.fechasSolapadas, data.solapado);
      Utils.marcarInputError("cohorte_fecha_inicio", data.solapado);
      Utils.marcarInputError("cohorte_fecha_fin", data.solapado);
    });
  }

  function obtenerUltimoCohorte(tipo) {
    if (!tipo) return;
    const id = document.getElementById("cohorte_id")?.value || "";
    const anio = document.getElementById("cohorte_anio")?.value || "";
    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=obtener_ultimo_cohorte&tipo=${tipo}&anio=${anio}&excluir=${id}`)
      .then(res => res.json())
      .then(data => {
        const ci = document.getElementById("cohorte_previa");
        const fi = document.getElementById("fecha_inicio_previa");
        const ff = document.getElementById("fecha_fin_previa");
        if (data.existe) {
          if(ci) ci.textContent = `Último cohorte: ${data.numero} - ${data.anio}`;
          if(fi) fi.textContent = `Último inicio: ${data.fecha_inicio}`;
          if(ff) ff.textContent = `Último fin: ${data.fecha_fin}`;
        } else {
          if(ci) ci.textContent = ""; if(fi) fi.textContent = ""; if(ff) ff.textContent = "";
        }
      });
  }

  // ==============================================
  // LISTENERS DE TIEMPO REAL
  // ==============================================
  inputs.numero?.addEventListener("input", validarCohorteUnico);
  inputs.anio?.addEventListener("input", validarCohorteUnico);
  
  inputs.fechaInicio?.addEventListener("change", () => {
    validarInicioFin(); validarFechasEnRango(); validarFechasSolapadas();
  });
  
  inputs.fechaFin?.addEventListener("change", () => {
    validarInicioFin(); validarFechasEnRango(); validarFechasSolapadas();
  });

  [inputs.modulo1, inputs.modulo2, inputs.modulo3].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarOrdenModulos(); validarFechasNoRepetidas(); validarFechasEnRango();
    });
  });

  [inputs.pagoArt13, inputs.pagoAranceles].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarFechasNoRepetidas(); validarFechasEnRango();
    });
  });

  // ==============================================
  // ESTADO INICIAL
  // ==============================================
  if (inputs.tipo?.value) {
    tipoCohorteInput.dispatchEvent(new Event('change'));
  }

  setTimeout(() => {
    validarCursosVigentes();
    validarFechasEnRango();
    validarInicioFin();
  }, 500);
});
</script-->


<!--script>//este es del 4 de abril
document.addEventListener("DOMContentLoaded", () => {
  // ==============================================
  // INICIALIZACIÓN
  // ==============================================
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = "";
  let cursosFiltrados = [];

  // Inicializar el validador central
  Validador.init("btn-submit-cohorte");

  const ALERTAS = {
    rango: "alerta-fechas-rango_irrepetible",
    ordenModulos: "alerta-orden-modulos_irrepetible",
    inicioFin: "alerta-inicio-fin_irrepetible",
    fechasRepetidas: "alerta-fechas-repetidas_irrepetible",
    cohorteUnico: "alerta-cohorte-unico_irrepetible",
    fechasSolapadas: "alerta-solapamiento-fechas"
  };

  const inputs = {
    tipo: document.getElementById("cohorte_tipo"),
    numero: document.getElementById("cohorte_numero"),
    anio: document.getElementById("cohorte_anio"),
    fechaInicio: document.getElementById("cohorte_fecha_inicio"),
    fechaFin: document.getElementById("cohorte_fecha_fin"),
    pagoArt13: "cohorte_fecha_pago_art13",
    pagoAranceles: "cohorte_fecha_pago_aranceles",
    modulo1: "cohorte_fecha_modulo1",
    modulo2: "cohorte_fecha_modulo2",
    modulo3: "cohorte_fecha_modulo3"
  };

  // ==============================================
  // FUNCIONES DE VALIDACIÓN
  // ==============================================

  // Validación 1: Fechas dentro del rango
  function validarFechasEnRango() {
    const fInicio = inputs.fechaInicio?.value;
    const fFin = inputs.fechaFin?.value;
    
    if (!fInicio || !fFin) {
      Validador.set(ALERTAS.rango, false);
      return;
    }

    const fechaI = new Date(fInicio);
    const fechaF = new Date(fFin);
    const fechasAValidar = [
      Utils.getVal(inputs.pagoArt13),
      Utils.getVal(inputs.pagoAranceles),
      Utils.getVal(inputs.modulo1),
      Utils.getVal(inputs.modulo2),
      Utils.getVal(inputs.modulo3)
    ].filter(f => f);

    const hayError = fechasAValidar.some(f => {
      const d = new Date(f);
      return d < fechaI || d > fechaF;
    });

    Validador.set(ALERTAS.rango, hayError);
  }

  // Validación 2: Orden de módulos
  function validarOrdenModulos() {
    const m1 = Utils.getVal(inputs.modulo1);
    const m2 = Utils.getVal(inputs.modulo2);
    const m3 = Utils.getVal(inputs.modulo3);

    if (!m1 || !m2 || !m3) {
      Validador.set(ALERTAS.ordenModulos, false);
      return;
    }

    const d1 = new Date(m1), d2 = new Date(m2), d3 = new Date(m3);
    const hayError = (d1 > d2 || d2 > d3);
    
    Validador.set(ALERTAS.ordenModulos, hayError);
    
    Utils.marcarInputError(inputs.modulo1, d1 > d2);
    Utils.marcarInputError(inputs.modulo2, d2 > d3);
  }

  // Validación 3: Inicio no posterior a Fin
  function validarInicioFin() {
    Validador.validarRangoFechas("cohorte_fecha_inicio", "cohorte_fecha_fin", ALERTAS.inicioFin);
  }

  // Validación 4: Fechas no repetidas
  function validarFechasNoRepetidas() {
    const idsAPrevenir = [inputs.pagoArt13, inputs.pagoAranceles, inputs.modulo1, inputs.modulo2, inputs.modulo3];
    Validador.validarFechasUnicas(idsAPrevenir, ALERTAS.fechasRepetidas);
  }

  // Validación 5: Cohorte único (AJAX)
  function validarCohorteUnico() {
    if (!inputs.tipo?.value || !inputs.anio?.value || !inputs.numero?.value) return;
    
    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${inputs.tipo.value}&anio=${inputs.anio.value}&numero=${inputs.numero.value}&id=${document.getElementById("cohorte_id")?.value || ""}`;
    
    fetch(url)
      .then(res => res.json())
      .then(duplicado => Validador.set(ALERTAS.cohorteUnico, duplicado));
  }

  // Validación 6: Fechas solapadas (AJAX)
  function validarFechasSolapadas() {
    const fI = inputs.fechaInicio?.value;
    const fF = inputs.fechaFin?.value;
    if (!inputs.tipo?.value || !fI || !fF) return;

    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${inputs.tipo.value}&fecha_inicio=${fI}&fecha_fin=${fF}&excluir=${document.getElementById("cohorte_id")?.value || ""}`;

    fetch(url)
      .then(res => res.json())
      .then(data => {
        Validador.set(ALERTAS.fechasSolapadas, data.solapado);
        Utils.marcarInputError("cohorte_fecha_inicio", data.solapado);
        Utils.marcarInputError("cohorte_fecha_fin", data.solapado);
      });
  }

  // ==============================================
  // FUNCIÓN PARA OBTENER ÚLTIMO COHORTE
  // ==============================================
  function obtenerUltimoCohorte(tipo) {
    if (!tipo) return;
    
    const excluir = document.getElementById("cohorte_id")?.value || "";
    const anio = document.getElementById("cohorte_anio")?.value || "";
    const fechaInicioPrev = document.getElementById("fecha_inicio_previa");
    const fechaFinPrev = document.getElementById("fecha_fin_previa");
    const cohorteprevia = document.getElementById("cohorte_previa");
    
    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=obtener_ultimo_cohorte&tipo=${tipo}&anio=${anio}${excluir ? '&excluir=' + excluir : ''}`)
      .then(res => res.json())
      .then(data => {
        if (data.existe) {
          window.ultimoCohorte = {
            fecha_inicio: data.fecha_inicio,
            fecha_fin: data.fecha_fin,
            numero: data.numero,
            anio: data.anio
          };
          
          if (cohorteprevia) cohorteprevia.textContent = `Último cohorte: ${data.numero} - ${data.anio}`;
          if (fechaInicioPrev) fechaInicioPrev.textContent = `Último inicio: ${data.fecha_inicio}`;
          if (fechaFinPrev) fechaFinPrev.textContent = `Último fin: ${data.fecha_fin}`;
        } else {
          window.ultimoCohorte = null;
          if (cohorteprevia) cohorteprevia.textContent = "";
          if (fechaInicioPrev) fechaInicioPrev.textContent = "";
          if (fechaFinPrev) fechaFinPrev.textContent = "";
        }
      })
      .catch(err => console.error("Error al obtener último cohorte:", err));
  }

  // ==============================================
  // FUNCIONES DE CURSOS VIGENTES (VERSIÓN COMPLETA)
  // ==============================================
  
  function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    selects.forEach(select => {
      const selectedValue = select.value;
      const cursoSeleccionado = cursosData.find(c => c.id_curso == selectedValue);

      let opciones = cursosFiltrados.map(c => `
        <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
          ${c.nombre_curso} (${c.tipo_curso})
        </option>
      `).join("");

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

  function validarCursosVigentes() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    const cursosSeleccionados = [];
    let hayError = false;

    selects.forEach(select => {
      const cursoId = select.value;
      select.classList.remove("is-invalid");

      const cursoData = cursosData.find(c => c.id_curso == cursoId);

      if (!cursoId || !cursoData || cursoData.tipo_curso !== tipoCohorteSeleccionado || cursosSeleccionados.includes(cursoId)) {
        hayError = true;
        select.classList.add("is-invalid");
      } else {
        cursosSeleccionados.push(cursoId);
      }
    });

    // Mostrar/ocultar alerta visual
    alertaCursos.style.display = hayError ? "block" : "none";

    // Registrar error en el sistema central
    Validador.set("alerta-cursos-vigentes", hayError);
    
    // Actualizar contador de cursos
    document.getElementById("total_cursos_vigentes").value = selects.length;
  }

  // ==============================================
  // EVENT LISTENERS
  // ==============================================
  
  // Tipo de cohorte
  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
    obtenerUltimoCohorte(this.value);
    validarCohorteUnico();
  });

  // Validaciones en tiempo real
  inputs.numero?.addEventListener("input", validarCohorteUnico);
  inputs.anio?.addEventListener("input", validarCohorteUnico);
  
  inputs.fechaInicio?.addEventListener("change", () => {
    validarInicioFin();
    validarFechasEnRango();
    validarFechasSolapadas();
  });
  
  inputs.fechaFin?.addEventListener("change", () => {
    validarInicioFin();
    validarFechasEnRango();
    validarFechasSolapadas();
  });

  ["cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarOrdenModulos();
      validarFechasNoRepetidas();
      validarFechasEnRango();
    });
  });

  ["cohorte_fecha_pago_art13", "cohorte_fecha_pago_aranceles"].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarFechasNoRepetidas();
      validarFechasEnRango();
    });
  });

  // ==============================================
  // BOTÓN AGREGAR CURSO - VERSIÓN COMPLETA
  // ==============================================
  btnAgregar?.addEventListener("click", () => {
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

    // HTML COMPLETO con todos los campos (curso, docente, día)
    div.innerHTML = `
      <div class="col-md-4 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${cursosFiltrados.map(c => `
            <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
          `).join("")}
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
        <button type="button" class="btn btn-danger btn-sm eliminar-curso-vigente" title="Eliminar curso">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    `;

    contenedor.appendChild(div);

    // Evento para eliminar curso
    div.querySelector(".eliminar-curso-vigente").addEventListener("click", () => {
      div.remove();
      validarCursosVigentes();
    });

    // Evento para validar al cambiar el select de curso
    div.querySelector(".select-curso-vigente").addEventListener("change", () => {
      validarCursosVigentes();
    });

    contadorCursos++;
    validarCursosVigentes();
  });

  // Validación de cursos en tiempo real (para selects existentes)
  document.addEventListener("change", e => {
    if (e.target.classList && e.target.classList.contains("select-curso-vigente")) {
      validarCursosVigentes();
    }
  });

  // ==============================================
  // VALIDACIONES INICIALES
  // ==============================================
  if (inputs.tipo?.value) {
    tipoCohorteSeleccionado = inputs.tipo.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    obtenerUltimoCohorte(inputs.tipo.value);
  }

  // Disparar validaciones iniciales
  setTimeout(() => {
    validarFechasEnRango();
    validarOrdenModulos();
    validarInicioFin();
    validarFechasNoRepetidas();
    validarFechasSolapadas();
    validarCohorteUnico();
    validarCursosVigentes();
  }, 500);
});
</script-->

<!--script>// este es del 030426
// ==============================================
// SCRIPT ESPECÍFICO PARA COHORTES
// ==============================================
document.addEventListener("DOMContentLoaded", () => {
  // ==============================================
  // INICIALIZACIÓN
  // ==============================================
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = "";
  let cursosFiltrados = [];

  // Inicializar el validador central
  Validador.init("btn-submit-cohorte");

  const ALERTAS = {
    rango: "alerta-fechas-rango_irrepetible",
    ordenModulos: "alerta-orden-modulos_irrepetible",
    inicioFin: "alerta-inicio-fin_irrepetible",
    fechasRepetidas: "alerta-fechas-repetidas_irrepetible",
    cohorteUnico: "alerta-cohorte-unico_irrepetible",
    fechasSolapadas: "alerta-solapamiento-fechas"
  };

  const inputs = {
    tipo: document.getElementById("cohorte_tipo"),
    numero: document.getElementById("cohorte_numero"),
    anio: document.getElementById("cohorte_anio"),
    fechaInicio: document.getElementById("cohorte_fecha_inicio"),
    fechaFin: document.getElementById("cohorte_fecha_fin"),
    pagoArt13: "cohorte_fecha_pago_art13",
    pagoAranceles: "cohorte_fecha_pago_aranceles",
    modulo1: "cohorte_fecha_modulo1",
    modulo2: "cohorte_fecha_modulo2",
    modulo3: "cohorte_fecha_modulo3"
  };

  // ==============================================
  // FUNCIONES DE VALIDACIÓN (REFACTORIZADAS)
  // ==============================================

  // Validación 1: Fechas dentro del rango (Custom)
  function validarFechasEnRango() {
    const fInicio = inputs.fechaInicio?.value;
    const fFin = inputs.fechaFin?.value;
    
    if (!fInicio || !fFin) {
      Validador.set(ALERTAS.rango, false);
      return;
    }

    const fechaI = new Date(fInicio);
    const fechaF = new Date(fFin);
    const fechasAValidar = [
      Utils.getVal(inputs.pagoArt13),
      Utils.getVal(inputs.pagoAranceles),
      Utils.getVal(inputs.modulo1),
      Utils.getVal(inputs.modulo2),
      Utils.getVal(inputs.modulo3)
    ].filter(f => f);

    const hayError = fechasAValidar.some(f => {
      const d = new Date(f);
      return d < fechaI || d > fechaF;
    });

    Validador.set(ALERTAS.rango, hayError, hayError ? "Las fechas deben estar dentro del rango del trimestre" : null);
  }

  // Validación 2: Orden de módulos (Custom)
  function validarOrdenModulos() {
    const m1 = Utils.getVal(inputs.modulo1);
    const m2 = Utils.getVal(inputs.modulo2);
    const m3 = Utils.getVal(inputs.modulo3);

    if (!m1 || !m2 || !m3) {
      Validador.set(ALERTAS.ordenModulos, false);
      return;
    }

    const d1 = new Date(m1), d2 = new Date(m2), d3 = new Date(m3);
    const hayError = (d1 > d2 || d2 > d3);
    
    Validador.set(ALERTAS.ordenModulos, hayError, hayError ? "Los módulos deben ser secuenciales (1 → 2 → 3)" : null);
    
    Utils.marcarInputError(inputs.modulo1, d1 > d2);
    Utils.marcarInputError(inputs.modulo2, d2 > d3);
  }

  // Validación 3: Inicio no posterior a Fin (USANDO Validador.js)
  function validarInicioFin() {
    Validador.validarRangoFechas("cohorte_fecha_inicio", "cohorte_fecha_fin", ALERTAS.inicioFin);
  }

  // Validación 4: Fechas no repetidas (USANDO Validador.js)
  function validarFechasNoRepetidas() {
    const idsAPrevenir = [inputs.pagoArt13, inputs.pagoAranceles, inputs.modulo1, inputs.modulo2, inputs.modulo3];
    Validador.validarFechasUnicas(idsAPrevenir, ALERTAS.fechasRepetidas);
  }

  // Validación 5: Cohorte único (AJAX)
  function validarCohorteUnico() {
    if (!inputs.tipo?.value || !inputs.anio?.value || !inputs.numero?.value) return;
    
    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${inputs.tipo.value}&anio=${inputs.anio.value}&numero=${inputs.numero.value}&id=${document.getElementById("cohorte_id")?.value || ""}`;
    
    fetch(url)
      .then(res => res.json())
      .then(duplicado => Validador.set(ALERTAS.cohorteUnico, duplicado, duplicado ? "Este cohorte ya existe" : null));
  }

  // Validación 6: Fechas solapadas (AJAX)
  function validarFechasSolapadas() {
    const fI = inputs.fechaInicio?.value;
    const fF = inputs.fechaFin?.value;
    if (!inputs.tipo?.value || !fI || !fF) return;

    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${inputs.tipo.value}&fecha_inicio=${fI}&fecha_fin=${fF}&excluir=${document.getElementById("cohorte_id")?.value || ""}`;

    fetch(url)
      .then(res => res.json())
      .then(data => {
        alertaCursos.style.display = data.solapado ? "block" : "none"; // Visual manual
        Validador.set(ALERTAS.fechasSolapadas, data.solapado, data.mensaje);
      });
  }

  // Validación 7: Cursos Vigentes (Manual + Reporte)
  function validarCursosVigentes() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    const seleccionados = [];
    let hayError = false;

    selects.forEach(select => {
      const id = select.value;
      const data = cursosData.find(c => c.id_curso == id);
      const errorEnEste = !id || !data || data.tipo_curso !== tipoCohorteSeleccionado || seleccionados.includes(id);
      
      Utils.marcarInputError(select.id || select, errorEnEste);
      if (errorEnEste) hayError = true;
      else seleccionados.push(id);
    });

    alertaCursos.style.display = hayError ? "block" : "none";
    Validador.set("alerta-cursos-vigentes", hayError, "Cursos inválidos o repetidos");
  }

  // ==============================================
  // EVENT LISTENERS Y DISPARADORES
  // ==============================================
  
  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    validarCursosVigentes();
    validarCohorteUnico();
  });

  // Escucha masiva para cambios en fechas
  document.querySelectorAll('input[type="date"]').forEach(input => {
    input.addEventListener("change", () => {
      validarInicioFin();
      validarFechasEnRango();
      validarOrdenModulos();
      validarFechasNoRepetidas();
      validarFechasSolapadas();
    });
  });

  // Agregar curso dinámico
  btnAgregar?.addEventListener("click", () => {
    if (!tipoCohorteSeleccionado) return alert("Selecciona el tipo de cohorte.");
    
    const div = document.createElement("div");
    div.className = "row border rounded p-3 mb-3 bg-light";
    div.innerHTML = `
      <div class="col-md-4"><select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
        <option value="" disabled selected>Seleccione...</option>
        ${cursosFiltrados.map(c => `<option value="${c.id_curso}">${c.nombre_curso}</option>`).join("")}
      </select></div>
      <div class="col-md-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger btn-sm eliminar-curso-vigente"><i class="fas fa-trash-alt"></i></button>
      </div>`;

    contenedor.appendChild(div);
    div.querySelector(".eliminar-curso-vigente").onclick = () => { div.remove(); validarCursosVigentes(); };
    contadorCursos++;
    validarCursosVigentes();
  });

  // Ejecución inicial
  setTimeout(() => {
    validarInicioFin();
    validarFechasEnRango();
    validarCohorteUnico();
  }, 500);
          });

</script-->



<!--script> este script es del 020426
// ==============================================
// SCRIPT ESPECÍFICO PARA COHORTES
// ==============================================
document.addEventListener("DOMContentLoaded", () => {
  // ==============================================
  // INICIALIZACIÓN
  // ==============================================
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const btnSubmit = document.getElementById("btn-submit-cohorte");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = "";
  let cursosFiltrados = [];

  // Inicializar el validador central
  Validador.init("btn-submit-cohorte");

  // Mapeo de IDs de alertas (para facilitar cambios)
  const ALERTAS = {
    rango: "alerta-fechas-rango_irrepetible",
    ordenModulos: "alerta-orden-modulos_irrepetible",
    inicioFin: "alerta-inicio-fin_irrepetible",
    fechasRepetidas: "alerta-fechas-repetidas_irrepetible",
    cohorteUnico: "alerta-cohorte-unico_irrepetible",
    fechasSolapadas: "alerta-solapamiento-fechas"
  };

  // Referencias a los inputs principales
  const inputs = {
    tipo: document.getElementById("cohorte_tipo"),
    numero: document.getElementById("cohorte_numero"),
    anio: document.getElementById("cohorte_anio"),
    fechaInicio: document.getElementById("cohorte_fecha_inicio"),
    fechaFin: document.getElementById("cohorte_fecha_fin"),
    pagoArt13: document.getElementById("cohorte_fecha_pago_art13"),
    pagoAranceles: document.getElementById("cohorte_fecha_pago_aranceles"),
    modulo1: document.getElementById("cohorte_fecha_modulo1"),
    modulo2: document.getElementById("cohorte_fecha_modulo2"),
    modulo3: document.getElementById("cohorte_fecha_modulo3")
  };

  // ==============================================
  // FUNCIONES DE VALIDACIÓN (usando Validador)
  // ==============================================

  // Validación 1: Fechas dentro del rango
  function validarFechasEnRango() {
    if (!inputs.fechaInicio?.value || !inputs.fechaFin?.value) {
      Validador.set(ALERTAS.rango, false);
      return;
    }

    const fechaInicio = new Date(inputs.fechaInicio.value);
    const fechaFin = new Date(inputs.fechaFin.value);

    const fechasPago = [
      inputs.pagoArt13?.value,
      inputs.pagoAranceles?.value,
      inputs.modulo1?.value,
      inputs.modulo2?.value,
      inputs.modulo3?.value
    ].filter(f => f);

    let todasEnRango = true;
    fechasPago.forEach(fechaStr => {
      const fecha = new Date(fechaStr);
      if (fecha < fechaInicio || fecha > fechaFin) {
        todasEnRango = false;
      }
    });

    Validador.set(
      ALERTAS.rango, 
      !todasEnRango,
      !todasEnRango ? "Las fechas de pago deben estar dentro del rango del trimestre" : null
    );
  }

  // Validación 2: Orden de módulos
  function validarOrdenModulos() {
    if (!inputs.modulo1?.value || !inputs.modulo2?.value || !inputs.modulo3?.value) {
      Validador.set(ALERTAS.ordenModulos, false);
      return;
    }

    const mod1 = new Date(inputs.modulo1.value);
    const mod2 = new Date(inputs.modulo2.value);
    const mod3 = new Date(inputs.modulo3.value);

    const hayError = (mod1 > mod2 || mod2 > mod3);
    
    Validador.set(
      ALERTAS.ordenModulos, 
      hayError,
      hayError ? "Las fechas de módulos deben estar en orden secuencial: Módulo 1 → Módulo 2 → Módulo 3" : null
    );

    // Marcar inputs individualmente
    Utils.marcarInputError("cohorte_fecha_modulo1", mod1 > mod2);
    Utils.marcarInputError("cohorte_fecha_modulo2", mod2 > mod3);
    if (!hayError) {
      Utils.marcarInputError("cohorte_fecha_modulo3", false);
    }
  }

  // Validación 3: Inicio no posterior a Fin
  function validarInicioFin() {
        console.log("=== VALIDANDO INICIO/FIN ===");
  console.log("Fecha inicio:", inputs.fechaInicio?.value);
  console.log("Fecha fin:", inputs.fechaFin?.value);
  
    if (!inputs.fechaInicio?.value || !inputs.fechaFin?.value) {
            console.log("❌ Fechas incompletas");

      Validador.set(ALERTAS.inicioFin, false);
      return;
    }

    const inicio = new Date(inputs.fechaInicio.value);
    const fin = new Date(inputs.fechaFin.value);
    const hayError = (inicio > fin);
        console.log("Inicio objeto:", inicio);
  console.log("Fin objeto:", fin);
  console.log("Inicio > Fin?:", inicio > fin);

    Validador.set(
      ALERTAS.inicioFin, 
      hayError,
      hayError ? "La fecha de inicio no puede ser posterior a la fecha de fin" : null
    );

    Utils.marcarInputError("cohorte_fecha_inicio", hayError);
    Utils.marcarInputError("cohorte_fecha_fin", hayError);
  }

  // Validación 4: Fechas no repetidas
  function validarFechasNoRepetidas() {
    const fechas = [
      inputs.pagoArt13?.value,
      inputs.pagoAranceles?.value,
      inputs.modulo1?.value,
      inputs.modulo2?.value,
      inputs.modulo3?.value
    ].filter(f => f);

    const fechasUnicas = new Set(fechas);
    const hayError = (fechas.length !== fechasUnicas.size);

    Validador.set(
      ALERTAS.fechasRepetidas, 
      hayError,
      hayError ? "Las fechas de pago no pueden repetirse" : null
    );
  }

  // Validación 5: Cohorte único (servidor)
  function validarCohorteUnico() {
    if (!inputs.tipo?.value || !inputs.anio?.value || !inputs.numero?.value) {
      Validador.set(ALERTAS.cohorteUnico, false);
      return;
    }

    const tipo = inputs.tipo.value;
    const anio = inputs.anio.value;
    const numero = inputs.numero.value;
    const id = document.getElementById("cohorte_id")?.value || "";

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&anio=${anio}&numero=${numero}&id=${id}`)
      .then(res => res.json())
      .then(esDuplicado => {
        console.log("Respuesta del servidor:", esDuplicado);
        // Si es DUPLICADO (true) -> HAY ERROR
        Validador.set(
          ALERTAS.cohorteUnico, 
          esDuplicado,
          esDuplicado ? "Ya existe una cohorte con el mismo número, año y tipo" : null
        );
      });
  }

  // Validación 6: Fechas solapadas (servidor)
  function validarFechasSolapadas() {
    const alertaDiv = document.getElementById(ALERTAS.fechasSolapadas);
    
    if (!inputs.tipo?.value || !inputs.fechaInicio?.value || !inputs.fechaFin?.value) {
      if (alertaDiv) alertaDiv.style.display = "none";
      Validador.set(ALERTAS.fechasSolapadas, false);
      return;
    }

    const tipo = inputs.tipo.value;
    const fechaInicio = inputs.fechaInicio.value;
    const fechaFin = inputs.fechaFin.value;
    const excluir = document.getElementById("cohorte_id")?.value || "";

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&excluir=${excluir}`)
      .then(res => res.json())
      .then(data => {
        if (data.solapado) {
          // Esta alerta tiene comportamiento especial (aparece/desaparece)
          if (alertaDiv) {
            alertaDiv.style.display = "block";
            alertaDiv.style.color = "#dc3545";
            alertaDiv.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.mensaje}`;
          }
          Validador.set(ALERTAS.fechasSolapadas, true);
          Utils.marcarInputError("cohorte_fecha_inicio", true);
          Utils.marcarInputError("cohorte_fecha_fin", true);
        } else {
          if (alertaDiv) alertaDiv.style.display = "none";
          Validador.set(ALERTAS.fechasSolapadas, false);
          Utils.marcarInputError("cohorte_fecha_inicio", false);
          Utils.marcarInputError("cohorte_fecha_fin", false);
        }
      });
  }

  // ==============================================
  // FUNCIÓN PARA OBTENER ÚLTIMO COHORTE
  // ==============================================
  function obtenerUltimoCohorte(tipo) {
    if (!tipo) return;
    
    const excluir = document.getElementById("cohorte_id")?.value || "";
    const anio = document.getElementById("cohorte_anio")?.value || "";
    const fechaInicioPrev = document.getElementById("fecha_inicio_previa");
    const fechaFinPrev = document.getElementById("fecha_fin_previa");
    const cohorteprevia = document.getElementById("cohorte_previa");
    
    console.log("📡 Solicitando último cohorte para tipo:", tipo);
    
    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=obtener_ultimo_cohorte&tipo=${tipo}&anio=${anio}${excluir ? '&excluir=' + excluir : ''}`)
      .then(res => res.json())
      .then(data => {
        if (data.existe) {
          window.ultimoCohorte = {
            fecha_inicio: data.fecha_inicio,
            fecha_fin: data.fecha_fin,
            numero: data.numero,
            anio: data.anio
          };
          
          if (cohorteprevia) cohorteprevia.textContent = `Último cohorte: ${data.numero} - ${data.anio}`;
          if (fechaInicioPrev) fechaInicioPrev.textContent = `Último inicio: ${data.fecha_inicio}`;
          if (fechaFinPrev) fechaFinPrev.textContent = `Último fin: ${data.fecha_fin}`;
        } else {
          window.ultimoCohorte = null;
          if (fechaInicioPrev) fechaInicioPrev.textContent = "";
          if (fechaFinPrev) fechaFinPrev.textContent = "";
        }
      })
      .catch(err => console.error("Error al obtener último cohorte:", err));
  }

  // ==============================================
  // FUNCIONES DE CURSOS VIGENTES
  // ==============================================
  function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    selects.forEach(select => {
      const selectedValue = select.value;
      const cursoSeleccionado = cursosData.find(c => c.id_curso == selectedValue);

      let opciones = cursosFiltrados.map(c => `
        <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
          ${c.nombre_curso} (${c.tipo_curso})
        </option>
      `).join("");

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

//  function validarCursosVigentes() {
  //  const selects = document.querySelectorAll(".select-curso-vigente");
    //const cursosSeleccionados = [];
    //let valid = true;

//    selects.forEach(select => {
  //    const cursoId = select.value;
    //  select.classList.remove("is-invalid");

      //const cursoData = cursosData.find(c => c.id_curso == cursoId);

//      if (!cursoId || !cursoData || cursoData.tipo_curso !== tipoCohorteSeleccionado || cursosSeleccionados.includes(cursoId)) {
  //      valid = false;
    //    select.classList.add("is-invalid");
      //} else {
        //cursosSeleccionados.push(cursoId);
     // }
    //});

//    alertaCursos.style.display = valid ? "none" : "block";
  //}

    

    function validarCursosVigentes() {
        const selects = document.querySelectorAll(".select-curso-vigente");
        const cursosSeleccionados = [];
        let hayError = false; // Cambiamos 'valid' por 'hayError' para claridad

        selects.forEach(select => {
            const cursoId = select.value;
            select.classList.remove("is-invalid");

            const cursoData = cursosData.find(c => c.id_curso == cursoId);

            // Si no hay ID, o el tipo no coincide, o está repetido -> ERROR
            if (!cursoId || !cursoData || cursoData.tipo_curso !== tipoCohorteSeleccionado || cursosSeleccionados.includes(cursoId)) {
                hayError = true;
                select.classList.add("is-invalid");
            } else {
                cursosSeleccionados.push(cursoId);
            }
        });

        // 1. Mantienes tu comportamiento visual manual (Aparecer/Desaparecer)
        alertaCursos.style.display = hayError ? "block" : "none";

        // 2. REPORTAS el error al contador central de miscript.js
        // Esto agregará o quitará el ID del Set 'erroresActivos' automáticamente
        Validador.set(
            "alerta-cursos-vigentes", 
            hayError, 
            hayError ? "Los cursos deben ser del mismo tipo que el cohorte y no pueden repetirse." : null
        );
    }
    
    
    
    
  // ==============================================
  // EVENT LISTENERS
  // ==============================================
  
  // Tipo de cohorte
  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
    obtenerUltimoCohorte(this.value);
    validarCohorteUnico();
  });

  // Validaciones en tiempo real
  inputs.numero?.addEventListener("input", validarCohorteUnico);
  inputs.anio?.addEventListener("input", validarCohorteUnico);
  
  inputs.fechaInicio?.addEventListener("change", () => {
    validarInicioFin();
    validarFechasEnRango();
    validarFechasSolapadas();
  });
  
  inputs.fechaFin?.addEventListener("change", () => {
    validarInicioFin();
    validarFechasEnRango();
    validarFechasSolapadas();
  });

  ["cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarOrdenModulos();
      validarFechasNoRepetidas();
      validarFechasEnRango();
    });
  });

  ["cohorte_fecha_pago_art13", "cohorte_fecha_pago_aranceles"].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarFechasNoRepetidas();
      validarFechasEnRango();
    });
  });

  // Botón agregar curso
  btnAgregar?.addEventListener("click", () => {
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

  // Validación de cursos en tiempo real
  document.addEventListener("input", e => {
    if (e.target.classList.contains("select-curso-vigente")) {
      setTimeout(validarCursosVigentes, 1000);
    }
  });

  // ==============================================
  // VALIDACIONES INICIALES
  // ==============================================
  if (inputs.tipo?.value) {
    obtenerUltimoCohorte(inputs.tipo.value);
  }

  // Disparar validaciones iniciales
  setTimeout(() => {
    validarFechasEnRango();
    validarOrdenModulos();
    validarInicioFin();
    validarFechasNoRepetidas();
    validarFechasSolapadas();
    validarCohorteUnico();
  }, 500);
});
</script-->














<!--script>
// cursos_vigentes
document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const btnSubmit = document.getElementById("btn-submit-cohorte");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = "";
  let cursosFiltrados = [];

  // ==============================================
  // 1. LISTA CENTRAL DE ERRORES
  // ==============================================
  const erroresActivos = new Set();

  // Referencias a los divs de alerta
  const alertas = {
    rango: document.getElementById("alerta-fechas-rango_irrepetible"),
    ordenModulos: document.getElementById("alerta-orden-modulos_irrepetible"),
    inicioFin: document.getElementById("alerta-inicio-fin_irrepetible"),
    fechasRepetidas: document.getElementById("alerta-fechas-repetidas_irrepetible"),
    cohorteUnico: document.getElementById("alerta-cohorte-unico_irrepetible"),
    fechasSolapadas: document.getElementById("alerta-solapamiento-fechas") //alerta-fechas-solapadas
  };

  // Referencias a los inputs principales
  const inputs = {
    tipo: document.getElementById("cohorte_tipo"),
    numero: document.getElementById("cohorte_numero"),
    anio: document.getElementById("cohorte_anio"),
    fechaInicio: document.getElementById("cohorte_fecha_inicio"),
    fechaFin: document.getElementById("cohorte_fecha_fin"),
    pagoArt13: document.getElementById("cohorte_fecha_pago_art13"),
    pagoAranceles: document.getElementById("cohorte_fecha_pago_aranceles"),
    modulo1: document.getElementById("cohorte_fecha_modulo1"),
    modulo2: document.getElementById("cohorte_fecha_modulo2"),
    modulo3: document.getElementById("cohorte_fecha_modulo3")
  };

  // ==============================================
  // 2. FUNCIÓN PARA ACTUALIZAR ESTADO DEL BOTÓN
  // ==============================================
  function actualizarEstadoBoton() {
    const tieneErrores = erroresActivos.size > 0;
    btnSubmit.disabled = tieneErrores;

    if (tieneErrores) {
      btnSubmit.classList.add("btn-disabled");
      btnSubmit.style.opacity = "0.5";
      btnSubmit.style.cursor = "not-allowed";
    } else {
      btnSubmit.classList.remove("btn-disabled");
      btnSubmit.style.opacity = "1";
      btnSubmit.style.cursor = "pointer";
    }
    
    console.log("🔴 Errores activos:", Array.from(erroresActivos));
  }

  // ==============================================
  // 3. FUNCIÓN PARA ACTUALIZAR ALERTAS FIJAS
  // ==============================================
  function actualizarAlertaFija(tipo, esError) {
    const alerta = alertas[tipo];
    if (!alerta) return;

    if (esError) {
      alerta.style.color = "#dc3545"; // Rojo
      // Mantener el mismo texto, solo cambia el color
    } else {
      alerta.style.color = "#28a745"; // Verde
    }
  }

  // ==============================================
  // 4. FUNCIÓN PARA OBTENER ÚLTIMO COHORTE (NUEVA)
  // ==============================================
  function obtenerUltimoCohorte(tipo) {
    if (!tipo) return;
    
    const excluir = document.getElementById("cohorte_id")?.value || "";
    const anio = document.getElementById("cohorte_anio")?.value || "";
    const fechaInicioPrev = document.getElementById("fecha_inicio_previa");
    const fechaFinPrev = document.getElementById("fecha_fin_previa");
    const cohorteprevia = document.getElementById("cohorte_previa");
    const numeroprevia = document.getElementById("numero_sugerido_previa");
    
    console.log(" Solicitando último cohorte para tipo:", tipo, "excluir:", excluir);
    
    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=obtener_ultimo_cohorte&tipo=${tipo}&anio=${anio}${excluir ? '&excluir=' + excluir : ''}`)
      .then(res => {
        console.log("📡 Respuesta status:", res.status);
        return res.json();
      })
      .then(data => {
        console.log(" Datos recibidos del último cohorte:", data);
        
        if (data.existe) {
          window.ultimoCohorte = {
            fecha_inicio: data.fecha_inicio,
            fecha_fin: data.fecha_fin,
            numero: data.numero,
            anio: data.anio
          };
          
          if (cohorteprevia) cohorteprevia.textContent = `Último cohorte: ${data.numero} - ${data.anio}`;
          if (fechaInicioPrev) fechaInicioPrev.textContent = `Último inicio: ${data.fecha_inicio}`;
          if (fechaFinPrev) fechaFinPrev.textContent = `Último fin: ${data.fecha_fin}`;
          
          // Sugerir siguiente número si el campo está vacío
          if (inputs.numero && !inputs.numero.value) {
           // inputs.numero.value = data.numero + 1;

          }
        } else {
          window.ultimoCohorte = null;
          if (fechaInicioPrev) fechaInicioPrev.textContent = "";
          if (fechaFinPrev) fechaFinPrev.textContent = "";
        }
      })
      .catch(err => {
        console.error(" Error al obtener último cohorte:", err);
      });
  }

  // ==============================================
  // 5. VALIDACIONES ESPECÍFICAS
  // ==============================================

  // Validación 1: Fechas dentro del rango
  function validarFechasEnRango() {
    if (!inputs.fechaInicio?.value || !inputs.fechaFin?.value) {
      erroresActivos.delete("rango");
      actualizarAlertaFija("rango", false);
      return;
    }

    const fechaInicio = new Date(inputs.fechaInicio.value);
    const fechaFin = new Date(inputs.fechaFin.value);

    const fechasPago = [
      inputs.pagoArt13?.value,
      inputs.pagoAranceles?.value,
      inputs.modulo1?.value,
      inputs.modulo2?.value,
      inputs.modulo3?.value
    ].filter(f => f); // Solo las que tienen valor

    let todasEnRango = true;

    fechasPago.forEach(fechaStr => {
      const fecha = new Date(fechaStr);
      if (fecha < fechaInicio || fecha > fechaFin) {
        todasEnRango = false;
      }
    });

    if (!todasEnRango) {
      erroresActivos.add("rango");
      actualizarAlertaFija("rango", true);
    } else {
      erroresActivos.delete("rango");
      actualizarAlertaFija("rango", false);
    }

    actualizarEstadoBoton();
  }

  // Validación 2: Orden de módulos (mod1 -> mod2 -> mod3)
  function validarOrdenModulos() {
    if (!inputs.modulo1?.value || !inputs.modulo2?.value || !inputs.modulo3?.value) {
      erroresActivos.delete("ordenModulos");
      actualizarAlertaFija("ordenModulos", false);
      return;
    }

    const mod1 = new Date(inputs.modulo1.value);
    const mod2 = new Date(inputs.modulo2.value);
    const mod3 = new Date(inputs.modulo3.value);

    if (mod1 > mod2 || mod2 > mod3) {
      erroresActivos.add("ordenModulos");
      actualizarAlertaFija("ordenModulos", true);

      // Marcar inputs con error
      if (mod1 > mod2) inputs.modulo1.classList.add("is-invalid");
      if (mod2 > mod3) inputs.modulo2.classList.add("is-invalid");
    } else {
      erroresActivos.delete("ordenModulos");
      actualizarAlertaFija("ordenModulos", false);
      inputs.modulo1.classList.remove("is-invalid");
      inputs.modulo2.classList.remove("is-invalid");
      inputs.modulo3.classList.remove("is-invalid");
    }

    actualizarEstadoBoton();
  }

  // Validación 3: Inicio no posterior a Fin
  function validarInicioFin() {
    if (!inputs.fechaInicio?.value || !inputs.fechaFin?.value) {
      erroresActivos.delete("inicioFin");
      actualizarAlertaFija("inicioFin", false);
      return;
    }

    const inicio = new Date(inputs.fechaInicio.value);
    const fin = new Date(inputs.fechaFin.value);

    if (inicio > fin) {
      erroresActivos.add("inicioFin");
      actualizarAlertaFija("inicioFin", true);
      inputs.fechaInicio.classList.add("is-invalid");
      inputs.fechaFin.classList.add("is-invalid");
    } else {
      erroresActivos.delete("inicioFin");
      actualizarAlertaFija("inicioFin", false);
      inputs.fechaInicio.classList.remove("is-invalid");
      inputs.fechaFin.classList.remove("is-invalid");
    }

    actualizarEstadoBoton();
  }

  // Validación 4: Fechas no repetidas
  function validarFechasNoRepetidas() {
    const fechas = [
      inputs.pagoArt13?.value,
      inputs.pagoAranceles?.value,
      inputs.modulo1?.value,
      inputs.modulo2?.value,
      inputs.modulo3?.value
    ].filter(f => f); // Solo las que tienen valor

    const fechasUnicas = new Set(fechas);

    if (fechas.length !== fechasUnicas.size) {
      erroresActivos.add("fechasRepetidas");
      actualizarAlertaFija("fechasRepetidas", true);
    } else {
      erroresActivos.delete("fechasRepetidas");
      actualizarAlertaFija("fechasRepetidas", false);
    }

    actualizarEstadoBoton();
  }

  // Validación 5: Cohorte único (servidor)
  function validarCohorteUnico() {
    if (!inputs.tipo?.value || !inputs.anio?.value || !inputs.numero?.value) {
      erroresActivos.delete("cohorteUnico");
      actualizarAlertaFija("cohorteUnico", false);
      return;
    }

    const tipo = inputs.tipo.value;
    const anio = inputs.anio.value;
    const numero = inputs.numero.value;
    const id = document.getElementById("cohorte_id")?.value || "";

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&anio=${anio}&numero=${numero}&id=${id}`)
      .then(res => res.json())
      .then(esDuplicado => {
       console.log("Respuesta del servidor:", esDuplicado); //  muestra valor
        if (!esDuplicado) {
          erroresActivos.add("cohorteUnico");
          actualizarAlertaFija("cohorteUnico", true);
        } else {
          erroresActivos.delete("cohorteUnico");
          actualizarAlertaFija("cohorteUnico", false);
        }
        actualizarEstadoBoton();
      });
  }

  // Validación 6: Fechas solapadas (servidor) - LA ÚNICA QUE APARECE/DESAPARECE
  function validarFechasSolapadas() {
    const alerta = alertas.fechasSolapadas;

    if (!inputs.tipo?.value || !inputs.fechaInicio?.value || !inputs.fechaFin?.value) {
      alerta.style.display = "none";
      erroresActivos.delete("fechasSolapadas");
      actualizarEstadoBoton();
      return;
    }

    const tipo = inputs.tipo.value;
    const fechaInicio = inputs.fechaInicio.value;
    const fechaFin = inputs.fechaFin.value;
    const excluir = document.getElementById("cohorte_id")?.value || "";

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&excluir=${excluir}`)
      .then(res => res.json())
      .then(data => {
              //console.log("📦 Datos recibidos:", data);
        if (data.solapado) {
          // Mostrar alerta con información detallada
          alerta.style.display = "block";
          alerta.style.color = "#dc3545";
          alerta.innerHTML = `<i class="fas fa-exclamation-circle"></i> ${data.mensaje}`;

          erroresActivos.add("fechasSolapadas");
          inputs.fechaInicio.classList.add("is-invalid");
          inputs.fechaFin.classList.add("is-invalid");
        } else {
          alerta.style.display = "none";
          erroresActivos.delete("fechasSolapadas");
          inputs.fechaInicio.classList.remove("is-invalid");
          inputs.fechaFin.classList.remove("is-invalid");
        }
        actualizarEstadoBoton();
      });
  }

  // ==============================================
  // 6. EVENT LISTENERS (MANTENIENDO LOS EXISTENTES)
  // ==============================================

  // Validación de cohorte único (existente)
  document.getElementById("cohorte_tipo").addEventListener("change", validarCohorteUnico);
  document.getElementById("cohorte_numero").addEventListener("input", validarCohorteUnico);
  document.getElementById("cohorte_anio").addEventListener("input", validarCohorteUnico);

  // Validación de fecha de inicio y fin (existente)
  document.getElementById("cohorte_fecha_inicio").addEventListener("change", function() {
    validarFechaInicio(); // Función existente
    validarInicioFin();   // Nueva validación
  });
  
  document.getElementById("cohorte_fecha_fin").addEventListener("change", function() {
    validarFechaInicio(); // Función existente
    validarInicioFin();   // Nueva validación
  });

  // Validación de fechas dentro del rango (existente)
  document.getElementById("cohorte_fecha_inicio").addEventListener("change", function() {
    validarFechasDePagoEnRango(); // Función existente
    validarFechasEnRango();       // Nueva validación
  });
  
  document.getElementById("cohorte_fecha_fin").addEventListener("change", function() {
    validarFechasDePagoEnRango(); // Función existente
    validarFechasEnRango();       // Nueva validación
  });

  // Validación de solapamiento (existente)
  document.getElementById("cohorte_fecha_inicio").addEventListener("change", validarFechasSolapadas);
  document.getElementById("cohorte_fecha_fin").addEventListener("change", validarFechasSolapadas);

  // Validación de orden de módulos (existente)
  ["cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"]
    .forEach(id => {
      const input = document.getElementById(id);
      if (input) {
        input.addEventListener("change", function() {
          validarOrdenFechasModulos(); // Función existente
          validarOrdenModulos();        // Nueva validación
          validarFechasNoRepetidas();   // Nueva validación
        });
      }
    });

  // Validación de pagos y módulos en rango (existente)
  ["cohorte_fecha_pago_art13", "cohorte_fecha_pago_aranceles", "cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"]
    .forEach(id => {
      const input = document.getElementById(id);
      if (input) {
        input.addEventListener("change", function() {
          validarFechasDePagoEnRango(); // Función existente
          validarFechasEnRango();       // Nueva validación
          validarFechasNoRepetidas();   // Nueva validación
        });
      }
    });

  // ==============================================
  // 7. CÓDIGO EXISTENTE DE CURSOS VIGENTES (SIN CAMBIOS) + NUEVA LLAMADA
  // ==============================================

  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
    
    // 🔥 NUEVA LLAMADA: Obtener último cohorte cuando cambia el tipo
    obtenerUltimoCohorte(this.value);
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

      <div class="col-md-2 mb-2">
        <label class="form-label">Día de clases</label> 
        <select class="form-control" name="cursos_vigentes[${contadorCursos}][dia]" id="seccion_dia_reg" required>
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

  // ==============================================
  // 8. FUNCIONES EXISTENTES
  // ==============================================

  function validarFormularioCohorte() {
    const hayErrores = erroresActivos.size > 0;
    btnSubmit.disabled = hayErrores;
  }

  function validarFechaInicio() {
    const fechaInicioInput = document.getElementById("cohorte_fecha_inicio");
    const fechaFinInput = document.getElementById("cohorte_fecha_fin");
    const alerta = document.getElementById("alerta-fecha-inicio");

    const fechaInicio = new Date(fechaInicioInput.value);
    const fechaFin = new Date(fechaFinInput.value);

    let mensaje = "";

    if (fechaInicio > fechaFin) {
      mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin.";
    }

    alerta.textContent = mensaje;
    alerta.style.display = mensaje ? "block" : "none";
  }

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
  }

  function validarOrdenFechasModulos() {
    const alerta = document.getElementById("alerta-orden-fechas");
    const fechas = {
      modulo1: document.getElementById("cohorte_fecha_modulo1"),
      modulo2: document.getElementById("cohorte_fecha_modulo2"),
      modulo3: document.getElementById("cohorte_fecha_modulo3")
    };

    let mensaje = "";
    let hayError = false;

    // Remover clases de error primero
    Object.values(fechas).forEach(input => {
      if (input) input.classList.remove("is-invalid");
    });

    // Validar orden de módulos: modulo1 <= modulo2 <= modulo3
    if (fechas.modulo1.value && fechas.modulo2.value && fechas.modulo3.value) {
      const fechaMod1 = new Date(fechas.modulo1.value);
      const fechaMod2 = new Date(fechas.modulo2.value);
      const fechaMod3 = new Date(fechas.modulo3.value);

      if (fechaMod1 > fechaMod2 || fechaMod2 > fechaMod3) {
        mensaje = "Las fechas de módulos deben estar en orden secuencial: Módulo 1 → Módulo 2 → Módulo 3";
        hayError = true;
        fechas.modulo1.classList.add("is-invalid");
        fechas.modulo2.classList.add("is-invalid");
        fechas.modulo3.classList.add("is-invalid");
      }
    }

    alerta.textContent = mensaje;
    alerta.style.display = hayError ? "block" : "none";
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
  }

  // ==============================================
  // 9. INICIALIZACIÓN
  // ==============================================

  // Obtener último cohorte si ya hay un tipo seleccionado al cargar la página
  if (inputs.tipo?.value) {
    obtenerUltimoCohorte(inputs.tipo.value);
  }

  // Disparar validaciones iniciales si hay datos precargados
  if (inputs.fechaInicio?.value || inputs.fechaFin?.value) {
    validarFechasEnRango();
    validarOrdenModulos();
    validarInicioFin();
    validarFechasNoRepetidas();
    validarFechasSolapadas();
  }

  if (inputs.tipo?.value && inputs.anio?.value && inputs.numero?.value) {
    validarCohorteUnico();
  }

  // Estado inicial del botón
  actualizarEstadoBoton();
});
</script-->

























<!--//el original-->
<!----------------------------------------------------------->

<!--script>
    // cursos_vigentes
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

   
   // Validación de cohorte único (se ejecuta cuando cambia tipo o año)
//document.getElementById("cohorte_tipo").addEventListener("change", validarCohorteUnico);
//document.getElementById("cohorte_anio").addEventListener("input", validarCohorteUnico);

// Validación de solapamiento (se ejecuta cuando cambian las fechas de inicio/fin)
document.getElementById("cohorte_fecha_inicio").addEventListener("change", validarSolapamientoFechas);
document.getElementById("cohorte_fecha_fin").addEventListener("change", validarSolapamientoFechas);

// Validación de orden de módulos
["cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"]
    .forEach(id => {
        const input = document.getElementById(id);
        if (input) {
            input.addEventListener("change", validarOrdenFechasModulos);
        }
    }); 
    
    
    
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
        
        <div class="col-md-2 mb-2 ">
                   <label class="form-label">Día de clases</label> 
			<select class="form-control"  name="cursos_vigentes[${contadorCursos}][dia]" id="seccion_dia_reg" required>
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
    "alerta-cursos-vigentes",
              "alerta-solapamiento-fechas", // Nueva validación
        "alerta-orden-fechas" // Validación de módulos
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

            // Guardar las fechas del último cohorte para validación de solapamiento
            if (data.fecha_inicio && data.fecha_fin) {
                window.ultimoCohorte = {
                    fecha_inicio: data.fecha_inicio,
                    fecha_fin: data.fecha_fin,
                    numero: data.numero - 1 // Porque data.numero es el nuevo número
                };
            } else {
                window.ultimoCohorte = null;
            }

            // Validar solapamiento si hay fechas ingresadas
            validarSolapamientoFechas();
            validarFormularioCohorte();
        });
}

// Nueva función para validar solapamiento en JavaScript
function validarSolapamientoFechas() {
    const fechaInicioInput = document.getElementById("cohorte_fecha_inicio");
    const fechaFinInput = document.getElementById("cohorte_fecha_fin");
    const alerta = document.getElementById("alerta-solapamiento-fechas");

    // Verificar que los elementos existan
    if (!fechaInicioInput || !fechaFinInput || !alerta) {
        console.warn("Elementos necesarios para validación de solapamiento no encontrados");
        return;
    }

    // Solo validar si hay un último cohorte y fechas ingresadas
    if (!window.ultimoCohorte || !fechaInicioInput.value || !fechaFinInput.value) {
        alerta.style.display = "none";
        // También limpiar clases de error
        if (fechaInicioInput) fechaInicioInput.classList.remove("is-invalid");
        if (fechaFinInput) fechaFinInput.classList.remove("is-invalid");
        return;
    }

    const fechaInicioNew = new Date(fechaInicioInput.value);
    const fechaFinNew = new Date(fechaFinInput.value);
    const fechaInicioLast = new Date(window.ultimoCohorte.fecha_inicio);
    const fechaFinLast = new Date(window.ultimoCohorte.fecha_fin);

    // Verificar solapamiento
    const solapado = (
        (fechaInicioNew >= fechaInicioLast && fechaInicioNew <= fechaFinLast) || // Inicio nuevo dentro del rango anterior
        (fechaFinNew >= fechaInicioLast && fechaFinNew <= fechaFinLast) ||       // Fin nuevo dentro del rango anterior
        (fechaInicioNew <= fechaInicioLast && fechaFinNew >= fechaFinLast)       // Nuevo rango contiene al anterior
    );

    if (solapado) {
        alerta.textContent = `Las fechas se solapan con el cohorte #${window.ultimoCohorte.numero} (${window.ultimoCohorte.fecha_inicio} - ${window.ultimoCohorte.fecha_fin})`;
        alerta.style.display = "block";
        fechaInicioInput.classList.add("is-invalid");
        fechaFinInput.classList.add("is-invalid");
    } else {
        alerta.style.display = "none";
        fechaInicioInput.classList.remove("is-invalid");
        fechaFinInput.classList.remove("is-invalid");
    }

    validarFormularioCohorte();
}

    
    
function validarOrdenFechasModulos() {
    const alerta = document.getElementById("alerta-orden-fechas");
    const fechas = {
        modulo1: document.getElementById("cohorte_fecha_modulo1"),
        modulo2: document.getElementById("cohorte_fecha_modulo2"),
        modulo3: document.getElementById("cohorte_fecha_modulo3")
    };

    let mensaje = "";
    let hayError = false;

    // Remover clases de error primero
    Object.values(fechas).forEach(input => {
        if (input) input.classList.remove("is-invalid");
    });

    // Validar orden de módulos: modulo1 <= modulo2 <= modulo3
    // Solo si están las tres fechas completas
    if (fechas.modulo1.value && fechas.modulo2.value && fechas.modulo3.value) {
        const fechaMod1 = new Date(fechas.modulo1.value);
        const fechaMod2 = new Date(fechas.modulo2.value);
        const fechaMod3 = new Date(fechas.modulo3.value);

        if (fechaMod1 > fechaMod2 || fechaMod2 > fechaMod3) {
            mensaje = "Las fechas de módulos deben estar en orden secuencial: Módulo 1 → Módulo 2 → Módulo 3";
            hayError = true;
            fechas.modulo1.classList.add("is-invalid");
            fechas.modulo2.classList.add("is-invalid");
            fechas.modulo3.classList.add("is-invalid");
        }
    }

    alerta.textContent = mensaje;
    alerta.style.display = hayError ? "block" : "none";
    validarFormularioCohorte();
}    
    
    
    
    
    
    
  


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

      if (fechaInicio > fechaFin) {
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








//  if (fechaInicio < hace90dias) {
//    mensaje = "La fecha de inicio no puede ser anterior a los últimos 90 días.";
//  } else 
});
</script-->