<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    require_once "./controladores/cohorteControlador.php";
    $ctrl = new cohorteControlador();

    $cohorte_id = $_GET['cohorte_id_up'] ?? '';
    $datos = $ctrl->datos_cohorte_controlador("Unico", $cohorte_id);

    if ($datos->rowCount() == 1) {
        $cohorte = $datos->fetch();
    } else {
        echo "<div class='alert alert-danger text-center'>No se encontró la cohorte.</div>";
        exit();
    }

    $cursosDisponibles = $ctrl->listar_cursos_controlador_para_cohorte();
    $profesoresData = $ctrl->listar_profesores_controlador();
    
    // Obtener cursos asignados
    $cursosAsignadosRaw = $ctrl->cursos_vigentes_por_cohorte_controlador("Unico", $cohorte_id);
    $cursosYaAsignados = [];
    foreach($cursosAsignadosRaw as $c) {
        $cursosYaAsignados[] = [
            'id_curso_vigente' => $c['id_curso_vigente'],
            'id_curso_fk' => $c['id_curso_fk'],
            'nombre_curso' => $c['nombre_curso'],
            'tipo_curso' => $c['tipo_curso'],
            'ci_profesor' => $c['ci_profesor'],
            'nombre_profesor' => $c['nombre_profesor'],
            'apellido_profesor' => $c['apellido_profesor'],
            'dia' => $c['dia']
        ];
    }

    require_once "./modelos/mainModel.php";
    $id_real = mainModel::decryption($cohorte_id);

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
  const cursosData = <?php echo json_encode($cursosDisponibles); ?>;
  const profesoresData = <?php echo json_encode($profesoresData); ?>;
  const cursosYaAsignados = <?php echo json_encode($cursosYaAsignados); ?>;
  const SERVERURL = "<?php echo SERVERURL; ?>";
</script>

<style>
  .is-invalid {
    border: 1px solid #dc3545 !important;
  }
    
  select.form-select {
    max-height: 150px;
    overflow-y: auto;
  }

  /* Estilos para cursos ya asignados (AZUL) vs nuevos (GRIS) */
  .curso-asignado-db {
      background-color: #e6f2ff !important;
      border: 1px solid #b3d4ff !important;
  }
  .curso-nuevo-js {
      background-color: #f8f9fa !important;
      border: 1px solid #dee2e6 !important;
  }
  
  .archived-row {
      opacity: 0.5;
      background-color: #e9ecef !important;
      border: 1px solid #ced4da !important;
  }
</style>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-plus fa-fw"></i> &nbsp; ACTUALIZAR COHORTE <?php echo $id_real; ?>
    </h3>
    <p class="text-justify">
        Actualiza el trimestre académico y añade/archiva cursos.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-new'; ?>"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES</a>
        </li>
        <li>
            <a class="active" href="#"><i class="fas fa-sync-alt fa-fw"></i> &nbsp; ACTUALIZAR COHORTE</a>
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cohorteAjax.php" method="POST" data-form="save" autocomplete="off">
        <input type="hidden" name="cohorte_id_up" id="cohorte_id" value="<?php echo $cohorte_id; ?>">
        
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
                            <select class="form-control" name="cohorte_tipo_up" id="cohorte_tipo" required>
                                <option value="" disabled>Seleccione una opción</option>
                                <option value="Extendido" <?php if($cohorte['tipo_cohorte'] == 'Extendido') echo 'selected'; ?>>Extendido</option>
                                <option value="Técnico Profesional" <?php if($cohorte['tipo_cohorte'] == 'Técnico Profesional') echo 'selected'; ?>>Técnico Profesional</option>
                                <option value="Otro" <?php if($cohorte['tipo_cohorte'] == 'Otro') echo 'selected'; ?>>Otro</option>
                                <option value="Intensivo" <?php if($cohorte['tipo_cohorte'] == 'Intensivo') echo 'selected'; ?>>Intensivo</option>
                            </select>
                        </div>
                    </div>

                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_numero" class="bmd-label-floating">N° Cohorte</label>
                            <input type="number" class="form-control" name="cohorte_numero_up" id="cohorte_numero" min="1" max="4" value="<?php echo $cohorte['numero_cohorte']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_anio" class="bmd-label-floating">Año</label>
                            <input type="number" class="form-control" name="cohorte_anio_up" id="cohorte_anio" min="24" max="99" value="<?php echo $cohorte['anio_cohorte']; ?>" required>
                        </div>
                    </div>
                    
                    <div class="col-md-4" id="contenedor_alterno" style="<?php echo ($cohorte['tipo_cohorte'] == 'Otro' ? 'display:block;' : 'display:none;'); ?>">
                        <div class="form-group">
                            <label for="cohorte_alterno" class="bmd-label-floating">Nombre alterno</label>
                            <input type="text" class="form-control" name="cohorte_alterno_up" id="cohorte_alterno" maxlength="59" value="<?php echo $cohorte['cohorte_alterno'] ?? ''; ?>" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ0-9][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 ]{0,58}">
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>
        
        <div class="col-md-4 mt-3">
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
                            <input type="date" class="form-control" name="cohorte_fecha_inicio_up" id="cohorte_fecha_inicio" min="2024-01-01" value="<?php echo $cohorte['fecha_inicio']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_fin" class="bmd-label-floating">Fecha de fin</label>
                            <input type="date" class="form-control" name="cohorte_fecha_fin_up" id="cohorte_fecha_fin" min="2024-01-01" value="<?php echo $cohorte['fecha_fin']; ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <fieldset>
            <legend><i class="fas fa-book fa-fw"></i> &nbsp; Cursos Vigentes en el Trimestre</legend>

            <div class="text-danger small mb-2" id="alerta-cursos-vigentes" style="display:none;">
                Los cursos deben ser del mismo tipo que el cohorte y no pueden haber 2 cursos iguales en 1 cohorte.
            </div>

            <div id="curso-vigente-contenedor"></div>

            <p class="text-end">
                <button type="button" id="agregarCursoVigente" class="btn btn-outline-primary btn-sm mt-3">
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
                            <input type="number" min="1" class="form-control" name="cohorte_pago_art13_up" id="cohorte_pago_art13" value="<?php echo $cohorte['pago_art13']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_art13" class="bmd-label-floating">Fecha pago art.13</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_art13_up" id="cohorte_fecha_pago_art13" min="2024-01-01" value="<?php echo $cohorte['fecha_pago_art13']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_aranceles" class="bmd-label-floating">Pago aranceles</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_aranceles_up" id="cohorte_pago_aranceles" value="<?php echo $cohorte['pago_aranceles']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_aranceles" class="bmd-label-floating">Fecha pago aranceles</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_aranceles_up" id="cohorte_fecha_pago_aranceles" min="2024-01-01" value="<?php echo $cohorte['fecha_pago_aranceles']; ?>" required>
                        </div>
                    </div>
                </div>

                <br>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pago_inscripcion_c" class="form-label">Pago inscripción general</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_inscripcion_up" id="pago_inscripcion_c" value="<?php echo $cohorte['pago_inscripcion']; ?>" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pago_modulo_c" class="form-label">Pago por módulo general</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_modulo_up" id="pago_modulo_c" value="<?php echo $cohorte['pago_modulo']; ?>" readonly>
                        </div>
                    </div>
                <br>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_modulo1" class="bmd-label-floating">Fecha módulo 1</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo1_up" id="cohorte_fecha_modulo1" min="2024-01-01" value="<?php echo $cohorte['fecha_pago_modulo1']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_modulo2" class="bmd-label-floating">Fecha módulo 2</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo2_up" id="cohorte_fecha_modulo2" min="2024-01-01" value="<?php echo $cohorte['fecha_pago_modulo2']; ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_modulo3" class="bmd-label-floating">Fecha módulo 3</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo3_up" id="cohorte_fecha_modulo3" min="2024-01-01" value="<?php echo $cohorte['fecha_pago_modulo3']; ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <input type="hidden" name="total_cursos_asignados" id="total_cursos_asignados" value="0">
        <input type="hidden" name="total_cursos_nuevos" id="total_cursos_nuevos" value="0">
        <input type="hidden" name="total_cursos_vigentes" id="total_cursos_vigentes" value="0">

        <p class="text-center" style="margin-top: 40px;">
            &nbsp; &nbsp;
            <button type="submit" id="btn-submit-cohorte" class="btn btn-info btn-sm" disabled>
            <i class="far fa-save"></i> ACTUALIZAR
            </button>
        </p>
    </form>
</div>

<script src="<?php echo SERVERURL; ?>vistas/js/miscript.js"></script>

<script>
// ==============================================
// SCRIPT UNIFICADO: COHORTE UPDATE
// ==============================================
document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const inputAlterno = document.getElementById("cohorte_alterno");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const divAlterno = document.getElementById("contenedor_alterno");
  
  let contadorAsignados = 0;
  let contadorNuevos = 0;
  let tipoCohorteSeleccionado = tipoCohorteInput.value;
  let cursosFiltrados = [];

  // Inicializar validador
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

  // Inicializar estado base
  if (tipoCohorteSeleccionado === "Otro") {
      cursosFiltrados = cursosData.filter(c => c.tipo_curso === "Extendido");
  } else {
      cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
  }

  // Cargar Cursos Precargados (de Base de Datos)
  if (Array.isArray(cursosYaAsignados)) {
      cursosYaAsignados.forEach(curso => {
          renderizarFilaCurso(curso, true);
      });
  }
  
  // Refrescar al inicio para que Validador evalúe
  validarCursosVigentes();

  // ==============================================
  // LÓGICA DE INTERFAZ Y FILTRADO (CAMBIO DE TIPO)
  // ==============================================
  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;

    if (this.value === "Otro") {
      if (divAlterno) divAlterno.style.display = "block";
      if (inputAlterno) inputAlterno.setAttribute("required", "required");
      cursosFiltrados = cursosData.filter(c => c.tipo_curso === "Extendido");
    } else {
      if (divAlterno) divAlterno.style.display = "none";
      if (inputAlterno) {
        inputAlterno.value = ""; 
        inputAlterno.removeAttribute("required");
        inputAlterno.classList.remove("is-invalid");
      }
      cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    }

    actualizarOpcionesSelects(); 
    validarCursosVigentes();     
    obtenerUltimoCohorte(this.value);
    validarCohorteUnico();
  });

  // ==============================================
  // RENDERIZADOR ÚNICO DE CURSOS
  // ==============================================
  function renderizarFilaCurso(cursoInfo = null, esAsignado = false) {
      const div = document.createElement("div");
      div.className = `row p-3 mb-3 ${esAsignado ? 'curso-asignado-db' : 'curso-nuevo-js'}`;
      div.style.borderRadius = "8px";

      let htmlInterno = "";

      if (esAsignado) {
          // FILA DE CURSO EXISTENTE (Con Checkbox de Archivar)
          const index = contadorAsignados;
          
          htmlInterno = `
            <input type="hidden" name="cursos_asignados[${index}][id_curso_vigente]" value="${cursoInfo.id_curso_vigente}">
            <input type="hidden" name="cursos_asignados[${index}][estado_curso_vigente]" class="estado-curso" value="1">
            
            <div class="col-md-4 mb-2">
              <label class="form-label text-primary"><i class="fas fa-bookmark"></i> Curso Asignado</label>
              <select name="cursos_asignados[${index}][id_curso_fk]" class="form-select select-curso-vigente" required>
                <option value="${cursoInfo.id_curso_fk}" selected>${cursoInfo.nombre_curso} (${cursoInfo.tipo_curso})</option>
              </select>
            </div>

            <div class="col-md-3 mb-2">
              <label class="form-label text-muted">Docente Actual</label>
              <input type="text" class="form-control" value="${cursoInfo.nombre_profesor || ''} ${cursoInfo.apellido_profesor || ''}" readonly>
            </div>

            <div class="col-md-2 mb-2">
              <label class="form-label text-muted">Día de clases</label>
              <input type="text" class="form-control" value="${cursoInfo.dia || ''}" readonly>
            </div>

            <div class="col-md-3 mb-2 d-flex align-items-center mt-3">
              <div class="form-check form-switch w-100 p-0 text-end">
                <label class="form-check-label d-block text-danger fw-bold fs-6 mb-1">¿Archivar?</label>
                <input class="form-check-input switch-archivar" type="checkbox" role="switch" style="margin-left:auto; margin-right:0;">
              </div>
            </div>
          `;
          contadorAsignados++;
      } else {
          // FILA DE CURSO NUEVO (Con botón de Eliminar)
          const index = contadorNuevos;
          
          htmlInterno = `
            <div class="col-md-4 mb-2">
              <label class="form-label text-success"><i class="fas fa-star"></i> Nuevo Curso</label>
              <select name="cursos_vigentes[${index}][id_curso_fk]" class="form-select select-curso-vigente" required>
                <option value="" selected disabled>Seleccione...</option>
                ${cursosFiltrados.map(c => `
                  <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
                `).join("")}
              </select>
            </div>

            <div class="col-md-4 mb-2">
              <label class="form-label">Docente de la sección "A"</label>
              <select name="cursos_vigentes[${index}][id_profesor_fk]" class="form-select" required>
                <option value="" selected disabled>Seleccione...</option>
                ${profesoresData.map(p => `
                  <option value="${p.id_profesor}">${p.nombre_profesor} ${p.apellido_profesor}</option>
                `).join("")}
              </select>
            </div>

            <div class="col-md-2 mb-2">
              <label class="form-label">Día de clases</label> 
              <select class="form-control" name="cursos_vigentes[${index}][dia]" required>
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
            
            <div class="col-md-2 mb-2 d-flex align-items-end justify-content-end">
              <button type="button" class="btn btn-danger btn-sm eliminar-curso-nuevo">
                <i class="fas fa-trash-alt"></i> Borrar
              </button>
            </div>
          `;
          contadorNuevos++;
      }

      div.innerHTML = htmlInterno;
      contenedor.appendChild(div);

      // Listeners Específicos según tipo de fila
      if (esAsignado) {
          const checkArchivar = div.querySelector(".switch-archivar");
          const inputEstado = div.querySelector(".estado-curso");
          const selectElement = div.querySelector(".select-curso-vigente");
          
          checkArchivar.addEventListener("change", (e) => {
              if (e.target.checked) {
                  inputEstado.value = "0"; // 0 = Archivar en el backend
                  div.classList.add("archived-row");
                  selectElement.disabled = true; // No lo evaluamos para validaciones si lo archivamos
              } else {
                  inputEstado.value = "1";
                  div.classList.remove("archived-row");
                  selectElement.disabled = false;
              }
              validarCursosVigentes();
          });
      } else {
          div.querySelector(".eliminar-curso-nuevo").addEventListener("click", () => {
              div.remove();
              validarCursosVigentes();
          });
      }

      // Listener General de validación al cambiar selects
      const selectCurso = div.querySelector(".select-curso-vigente");
      if(selectCurso) {
          selectCurso.addEventListener("change", validarCursosVigentes);
      }
      
      actualizarContadoresOcultos();
  }

  btnAgregar?.addEventListener("click", () => {
    if (!tipoCohorteSeleccionado) {
      alert("Primero selecciona el tipo de cohorte.");
      return;
    }
    renderizarFilaCurso(null, false);
    validarCursosVigentes();
  });

  // ==============================================
  // FUNCIONES DE RE-VALIDACIÓN UNIFICADA
  // ==============================================
  function actualizarOpcionesSelects() {
    const selectsNuevos = document.querySelectorAll(".curso-nuevo-js .select-curso-vigente");
    
    selectsNuevos.forEach(select => {
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

    // Validar asignados también (no cambian las opciones pero sí se chequean sus valores)
    validarCursosVigentes();
  }

  function validarCursosVigentes() {
    // Tomar ambos tipos de selects activos
    const selects = document.querySelectorAll(".select-curso-vigente:not(:disabled)");
    const cursosSeleccionados = [];
    let hayError = false;

    selects.forEach(select => {
      const cursoId = select.value;
      select.classList.remove("is-invalid");

      // Cursos ya asignados (precargados) no están en cursosData siempre, por ende sólo validamos duplicados entre ellos y los nuevos, 
      // y si el tipo coincide con el de la cohorte.
      
      const cursoData = cursosData.find(c => c.id_curso == cursoId) || cursosYaAsignados.find(c => c.id_curso_fk == cursoId);
      
      const esTipoCorrecto = (tipoCohorteSeleccionado === "Otro") 
        ? (cursoData?.tipo_curso === "Extendido")
        : (cursoData?.tipo_curso === tipoCohorteSeleccionado);

      // Si no es correcto el tipo o si ya se repite el curso (duplicado)
      if (!cursoId || !esTipoCorrecto || cursosSeleccionados.includes(cursoId)) {
        hayError = true;
        select.classList.add("is-invalid");
      } else {
        cursosSeleccionados.push(cursoId);
      }
    });

    alertaCursos.style.display = hayError ? "block" : "none";
    Validador.set("alerta-cursos-vigentes", hayError);
    actualizarContadoresOcultos();
  }

  function actualizarContadoresOcultos() {
      // Usamos el contador nativo que genera el array para el Backend
      document.getElementById("total_cursos_asignados").value = document.querySelectorAll(".curso-asignado-db").length;
      document.getElementById("total_cursos_nuevos").value = contadorNuevos;
      document.getElementById("total_cursos_vigentes").value = document.querySelectorAll(".select-curso-vigente").length;
  }

  // ==============================================
  // VALIDACIONES DE FECHAS (Idéntico a cohorte-new)
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
    const tipo = inputs.tipo?.value;
    const anio = inputs.anio?.value;
    const numero = inputs.numero?.value;
    const alterno = document.getElementById("cohorte_alterno")?.value || ""; 

    if (!tipo || !anio || !numero) return;

    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&anio=${anio}&numero=${numero}&cohorte_alterno=${encodeURIComponent(alterno)}&id=${document.getElementById("cohorte_id")?.value || ""}`;
    
    fetch(url)
        .then(res => res.json())
        .then(dup => {
            Validador.set(ALERTAS.cohorteUnico, dup);
            Utils.marcarInputError("cohorte_numero", dup);
        });
  }

  function obtenerUltimoCohorte(tipo) {
      if (!tipo) return;
      const anio = inputs.anio?.value || new Date().getFullYear().toString().slice(-2);
      
      const url = `${SERVERURL}ajax/cohorteAjax.php?accion=obtener_ultimo_cohorte&tipo=${tipo}&anio=${anio}&excluir=${document.getElementById("cohorte_id")?.value || ""}`;
      
      fetch(url)
        .then(res => res.json())
        .then(data => {
          document.getElementById("cohorte_previa").innerHTML = `Última Cohorte: <b>${data.numero || '-'}</b>`;
          document.getElementById("fecha_inicio_previa").textContent = `Inició: ${data.fecha_inicio || '-'}`;
          document.getElementById("fecha_fin_previa").textContent = `Finalizó: ${data.fecha_fin || '-'}`;
        });
  }

  function validarFechasSolapadas() {
    const fI = inputs.fechaInicio?.value, fF = inputs.fechaFin?.value;
    if (!inputs.tipo?.value || !fI || !fF) return;

    const url = `${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${inputs.tipo.value}&fecha_inicio=${fI}&fecha_fin=${fF}&excluir=${document.getElementById("cohorte_id")?.value || ""}`;
    fetch(url).then(res => res.json()).then(data => {
      Validador.set(ALERTAS.fechasSolapadas, data.solapado);
      Utils.marcarInputError("cohorte_fecha_inicio", data.solapado);
      Utils.marcarInputError("cohorte_fecha_fin", data.solapado);
    });
  }

  // EVENTOS A LOS INPUTS:
  [inputs.numero, inputs.anio].forEach(el => el?.addEventListener("input", validarCohorteUnico));
  [inputs.fechaInicio, inputs.fechaFin].forEach(el => {
    el?.addEventListener("change", () => {
      validarInicioFin();
      validarFechasEnRango();
      validarFechasSolapadas();
    });
  });
  
  [inputs.modulo1, inputs.modulo2, inputs.modulo3].forEach(id => {
    document.getElementById(id)?.addEventListener("change", validarOrdenModulos);
  });
  
  [inputs.pagoArt13, inputs.pagoAranceles, inputs.modulo1, inputs.modulo2, inputs.modulo3].forEach(id => {
    document.getElementById(id)?.addEventListener("change", () => {
      validarFechasEnRango();
      validarFechasNoRepetidas();
    });
  });

});
</script>


