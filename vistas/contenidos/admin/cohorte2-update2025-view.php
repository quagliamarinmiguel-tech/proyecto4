

<?php  
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
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

    $cursosData = $ctrl->listar_cursos_controlador_para_cohorte();
    $profesoresData = $ctrl->listar_profesores_controlador();



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
  const cursosData = <?= json_encode($cursosData) ?>;
  const profesoresData = <?= json_encode($profesoresData) ?>;
    const SERVERURL = "<?php echo SERVERURL; ?>";

</script>



<style>
  .is-invalid {
    border: 1px solid #dc3545 !important;
  }
  select.form-select {
  max-height: 150px;
  overflow-y: auto;

  option[disabled][selected] {
  background-color: #ffe6e6;
  font-weight: bold;
  }
}
</style>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-plus fa-fw"></i> &nbsp; ACTUALIZAR COHORTE <?php echo $id_real; ?>

    </h3>
    <p class="text-justify">
        Actualiza los datos de la cohorte y los registros de los cursos vigentes en este.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-new'; ?>"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a>
            <!--a href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a-->
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES</a>
            <!--a href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES</a-->
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; ACTUALIZAR COHORTE</a>
        </li>
        
    </ul>   
</div>

<div class="container-fluid">

    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cohorteAjax.php" method="POST" data-form="save" autocomplete="off">
        <!--input type="hidden" name="cohorte_id_up" value="<?php echo $cohorte_id; ?>"-->
        <input type="hidden" name="cohorte_id_up" id="cohorte_id" value="<?php echo $cohorte_id; ?>">
        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Información básica</legend>
            <div id="alerta-cohorte-unico" class="text-danger small mb-2" style="display:none;"></div>

            <!--div id="contador-total-cursos" class="alert alert-info text-center mt-3">
  Total de cursos vigentes: <strong>0</strong>
</div-->



            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_tipo" class="bmd-label-floating">Tipo de cohorte</label>
                            <!--select class="form-control" name="cohorte_tipo_up" id="cohorte_tipo" required>
                                <option value="" selected disabled>Seleccione una opción</option>
                                <option <php if($cohorte['tipo_cohorte'] == 'Intensivo') echo 'selected'; ?> value="Intensivo">Intensivo</option>
                                <option <php if($cohorte['tipo_cohorte'] == 'Extendido') echo 'selected'; ?> value="Extendido">Extendido</option>
                                <option <php if($cohorte['tipo_cohorte'] == 'Técnico Profesional') echo 'selected'; ?> value="Técnico Profesional">Técnico Profesional</option>
                            </select-->

                            <select class="form-control" name="cohorte_tipo_up" id="cohorte_tipo" required>
                                <option selected value="<?php echo $cohorte['tipo_cohorte']; ?>">
                                    <?php echo $cohorte['tipo_cohorte']; ?>
                                </option>
                            </select>
                            
                        </div>
                    </div>
                    <!--div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_estado" class="bmd-label-floating">Estado</label>
                            <select class="form-control" name="cohorte_estado_up" id="cohorte_estado" required>
                                <option value="" selected disabled>Seleccione una opción</option>
                                <option <?php if($cohorte['estado_cohorte'] == 'Vigente') echo 'selected'; ?> value="Vigente">Vigente</option>
                                <option <?php if($cohorte['estado_cohorte'] == 'Próximo') echo 'selected'; ?> value="Próximo">Próximo</option>
                                <option <?php if($cohorte['estado_cohorte'] == 'Terminado') echo 'selected'; ?> value="Terminado">Terminado</option>
                            </select>
                        </div>
                    </div-->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_numero" class="bmd-label-floating">N° Cohorte</label>
                            <input type="number" class="form-control" name="cohorte_numero_up" id="cohorte_numero" min="1" max="4" value="<?php echo $cohorte['numero_cohorte']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_anio" class="bmd-label-floating">Año</label>
                            <input type="number" class="form-control" name="cohorte_anio_up" value="<?php echo $cohorte['anio_cohorte']; ?>" id="cohorte_anio" min="25" max="99" readonly>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <fieldset>
            <legend><i class="fas fa-calendar-day fa-fw"></i> &nbsp; Fechas de la cohorte</legend>
            <div id="alerta-fecha-inicio" class="text-danger small mb-2" style="display:none;"></div>
			<div id="alerta-solapamiento-fechas" class="alert alert-warning mt-2" style="display: none;"></div>

            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_inicio" class="bmd-label-floating">Fecha de inicio</label>
                            <input type="date" class="form-control" name="cohorte_fecha_inicio_up" id="cohorte_fecha_inicio" value="<?php echo $cohorte['fecha_inicio']; ?>"  min="2024-01-01" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_fin" class="bmd-label-floating">Fecha de fin</label>
                            <input type="date" class="form-control" name="cohorte_fecha_fin_up" id="cohorte_fecha_fin" value="<?php echo $cohorte['fecha_fin']; ?>"  min="2024-01-01" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        
        <!-- Agregar este div para mostrar fechas del último cohorte -->
<div class="col-md-4">
  <div class="form-group">
    <label class="bmd-label-floating">Fechas del último cohorte que coincide con el tipo</label>
    <p class="text-muted small" id="fecha_inicio_previa"></p>
    <p class="text-muted small" id="fecha_fin_previa"></p>
  </div>
</div>
        <br><br>




<div id="cursos-asignados-contenedor">
  <legend><i class="fas fa-plus-circle"></i> &nbsp; Curso(s) ya asignado(s) a la cohorte</legend>

      <p class="text-justify">
        Al archivar un curso vigente, se archiva también sus secciones, evaluaciones y calificaciones de estudiantes inscritos a dicho periodo de curso
    </p>





<div id="alerta-cursos-vigentes" class="text-danger small mb-2" style="display:none;">
  Los cursos deben ser del mismo tipo que el cohorte y no pueden repetirse.
</div>












  <?php
    $cursos = $ctrl->cursos_vigentes_por_cohorte_controlador("Unico", $cohorte_id); 
    foreach($cursos as $i => $curso){

      if (
        empty($curso['id_curso_vigente']) || 
        empty($curso['id_curso_fk'])
      ) {
        echo '<div class="alert alert-warning"> Curso asignado con datos incompletos o inválidos. No se puede mostrar.</div>';
        continue;
      }

      echo '
      <div class="row border rounded p-3 mb-3 bg-light curso-asignado">               
        <input type="hidden" name="cursos_asignados['.$i.'][id_curso_vigente]" value="'.$curso['id_curso_vigente'].'">
        <input type="hidden" name="cursos_asignados['.$i.'][estado_curso_vigente]" value="1" class="estado-curso-vigente">




<div class="col-md-2 mb-2">
  <label class="form-label text-center w-100">ID</label>
  <div class="text-center" >
    <span class="badge bg-secondary fs-6" style="background-color: #FFFDF7; color: #FFF;">'.$curso['id_curso_vigente'].'</span>
  </div>
</div>


        <div class="col-md-4 mb-2">
          <label>Curso</label>
 

          <select name="cursos_asignados['.$i.'][id_curso_fk]" class="form-select select-curso-vigente" disabled>
          <option value="'.$curso['id_curso_fk'].'" selected> '.$curso['nombre_curso'].' ('.$curso['tipo_curso'].')</option>

            <option value="" disabled>Seleccione...</option>';

      echo '</select>





        </div>


        <div class="col-md-2 mb-2">
          <input type="hidden" name="cursos_asignados['.$i.'][id_curso_fk]" value="'.$curso['id_curso_fk'].'">
        </div>
                <div class="col-md-2 mb-2">
            <label>Día de la sección</label>
            <input type="text" class="form-control" value="'.$curso['dia'].'" readonly>
        </div>

        <div class="col-md-2 mb-2">
            <label>Profesor asignado (sección A)</label>
            <input type="text" class="form-control" value="'.$curso['nombre_profesor'].' '.$curso['apellido_profesor'].'" readonly>
        </div>

        <div class="col-md-2 mb-2 d-flex align-items-end">
          <button type="button" class="btn btn-warning btn-sm archivar-curso-vigente" title="Archivar curso">
            <i class="fas fa-box-archive"></i> Archivar
          </button>
        </div>
      </div>';
    }
  ?>
</div>





<fieldset>
    <legend><i class="fas fa-plus-circle"></i> &nbsp; Asignar Curso(s) a la cohorte</legend>


    <div id="curso-vigente-contenedor"></div>

    <p class="text-end">
        <button type="button" id="agregarCursoVigente" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus"></i> Agregar curso vigente
        </button>
    </p>



</fieldset>
<!------------------------------------------------------------------------------------------->
<br><br>

        <fieldset>
            <legend><i class="fas fa-dollar-sign fa-fw"></i> &nbsp; Fechas y montos de pagos</legend>
            <div id="alerta-fechas-pago" class="text-danger small mb-2" style="display:none;"></div>
            <div id="alerta-orden-fechas" class="alert alert-warning mt-2" style="display: none;"></div>

            

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_art13" class="bmd-label-floating">Pago art.13</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_art13_up" value="<?php echo $cohorte['pago_art13']; ?>" id="cohorte_pago_art13" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_art13" class="bmd-label-floating">Fecha pago art.13</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_art13_up" id="cohorte_fecha_pago_art13" value="<?php echo $cohorte['fecha_pago_art13']; ?>" min="2024-01-01" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_aranceles" class="bmd-label-floating">Pago aranceles</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_aranceles_up" id="cohorte_pago_aranceles"  value="<?php echo $cohorte['pago_aranceles']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_aranceles" class="bmd-label-floating">Fecha pago aranceles</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_aranceles_up" value="<?php echo $cohorte['fecha_pago_aranceles']; ?>" id="cohorte_fecha_pago_aranceles" min="2024-01-01" required>
                        </div>
                    </div>
                </div>


                <br>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pago_inscripcion_c" class="form-label">Pago inscripción general</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_inscripcion_up" value="<?php echo $cohorte['pago_inscripcion']; ?>" id="pago_inscripcion_c" readonly>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pago_modulo_c" class="form-label">Pago por módulo general</label>
                            <input type="number" min="1" class="form-control" name="cohorte_pago_modulo_up" value="<?php echo $cohorte['pago_modulo']; ?>" id="pago_modulo_c" readonly>
                        </div>
                    </div>
                <br>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo1_reg" class="bmd-label-floating">Fecha módulo 1</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo1_up" id="cohorte_fecha_modulo1" value="<?php echo $cohorte['fecha_pago_modulo1']; ?>" min="2024-01-01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo2_reg" class="bmd-label-floating">Fecha módulo 2</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo2_up" id="cohorte_fecha_modulo2" value="<?php echo $cohorte['fecha_pago_modulo2']; ?>" min="2024-01-01" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo3_reg" class="bmd-label-floating">Fecha módulo 3</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo3_up" id="cohorte_fecha_modulo3" value="<?php echo $cohorte['fecha_pago_modulo3']; ?>" min="2024-01-01" required>
                        </div>
                    </div>

                </div>
            </div>
        </fieldset>

        <br><br>

        <!--input type="hidden" name="total_cursos_vigentes" id="total_cursos_vigentes" value="0"-->
        <input type="hidden" name="total_cursos_nuevos" id="total_cursos_nuevos" value="0">
        <input type="hidden" name="total_cursos_asignados" id="total_cursos_asignados" value="0">
        <p class="text-center" style="margin-top: 40px;">
            <!--button type="reset" class="btn btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> LIMPIAR</button-->
            &nbsp; &nbsp;

            <!--button type="submit" class="btn btn-info btn-sm"><i class="far fa-save"></i> GUARDAR</button-->

            <button type="submit" id="btn-submit-cohorte" class="btn btn-info btn-sm" disabled>
            <i class="far fa-save"></i> ACTUALIZAR
            </button>

        </p>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // Variables globales
  let ultimoCohorte = null;
  let contadorCursos = 0;
  let tipoCohorteSeleccionado = document.getElementById("cohorte_tipo").value;
  let cursosFiltrados = [];

  // Elementos del DOM
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const alertaCohorte = document.getElementById("alerta-cohorte-unico");
  const alertaFechas = document.getElementById("alerta-fecha-inicio");
  const alertaPagos = document.getElementById("alerta-fechas-pago");
  const btnSubmit = document.getElementById("btn-submit-cohorte");
  const cohorteId = document.getElementById("cohorte_id").value;

  // Inicialización
  actualizarContadoresCursos();
  
  if (tipoCohorteSeleccionado) {
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
  }

  // Event Listeners principales
  document.getElementById("cohorte_tipo").addEventListener("change", function() {
    tipoCohorteSeleccionado = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
    validarCohorteUnico();
    obtenerUltimoCohorte();
  });

  // Event Listeners para validaciones
  ["cohorte_numero", "cohorte_anio"].forEach(id => {
    const input = document.getElementById(id);
    if (input) input.addEventListener("input", validarCohorteUnico);
  });

  ["cohorte_fecha_inicio", "cohorte_fecha_fin"].forEach(id => {
    const input = document.getElementById(id);
    if (input) {
      input.addEventListener("change", () => {
        validarFechasInicioFin();
        validarFechasDePagoEnRango();
        validarSolapamientoFechasCompleto(); // Nueva función mejorada
      });
      
      // Validación en tiempo real con debounce
      input.addEventListener("input", () => {
        clearTimeout(window.solapamientoTimeout);
        window.solapamientoTimeout = setTimeout(validarSolapamientoFechasCompleto, 500);
      });
    }
  });

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
      if (input) input.addEventListener("change", validarFechasDePagoEnRango);
    });

  document.addEventListener("input", e => {
    if (e.target.classList.contains("select-curso-vigente")) {
      setTimeout(validarCursosVigentes, 1000);
    }
  });

  // Ejecutar al cargar la página
  obtenerUltimoCohorte();

  // ========== FUNCIONES PRINCIPALES ==========

  // 1. Función MEJORADA para validar solapamiento con TODOS los cohortes
  function validarSolapamientoFechasCompleto() {
    const fechaInicioInput = document.getElementById("cohorte_fecha_inicio");
    const fechaFinInput = document.getElementById("cohorte_fecha_fin");
    const alerta = document.getElementById("alerta-solapamiento-fechas");
    const tipo = document.getElementById("cohorte_tipo").value;

    if (!fechaInicioInput || !fechaFinInput || !alerta || !tipo) return;

    const fechaInicio = fechaInicioInput.value;
    const fechaFin = fechaFinInput.value;

    // Validación básica de orden
    if (fechaInicio && fechaFin && new Date(fechaInicio) > new Date(fechaFin)) {
      alerta.textContent = "La fecha de inicio no puede ser posterior a la fecha de fin.";
      alerta.style.display = "block";
      fechaInicioInput.classList.add("is-invalid");
      fechaFinInput.classList.add("is-invalid");
      validarFormularioCohorte();
      return;
    }

    // Si las fechas están completas, validar solapamiento con todos los cohortes
    if (fechaInicio && fechaFin) {
      fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_solapamiento_fechas&tipo=${tipo}&fecha_inicio=${fechaInicio}&fecha_fin=${fechaFin}&excluir=${cohorteId}`)
        .then(res => res.json())
        .then(data => {
          if (data.solapado) {
            alerta.textContent = data.mensaje;
            alerta.style.display = "block";
            fechaInicioInput.classList.add("is-invalid");
            fechaFinInput.classList.add("is-invalid");
          } else {
            alerta.textContent = "";
            alerta.style.display = "none";
            fechaInicioInput.classList.remove("is-invalid");
            fechaFinInput.classList.remove("is-invalid");
          }
          validarFormularioCohorte();
        })
        .catch(error => {
          console.error("Error al validar solapamiento:", error);
          fechaInicioInput.classList.remove("is-invalid");
          fechaFinInput.classList.remove("is-invalid");
        });
    } else {
      // Si no hay fechas completas, limpiar la alerta
      alerta.textContent = "";
      alerta.style.display = "none";
      fechaInicioInput.classList.remove("is-invalid");
      fechaFinInput.classList.remove("is-invalid");
      validarFormularioCohorte();
    }
  }

  // 2. Función para obtener el último cohorte
  function obtenerUltimoCohorte() {
    const tipo = document.getElementById("cohorte_tipo").value;
    const anio = document.getElementById("cohorte_anio").value;

    if (!tipo || !anio) return;

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=obtener_ultimo_cohorte&tipo=${tipo}&anio=${anio}&excluir=${cohorteId}`)
      .then(res => res.json())
      .then(data => {
        const fechaInicioElem = document.getElementById("fecha_inicio_previa");
        const fechaFinElem = document.getElementById("fecha_fin_previa");
        
        if (fechaInicioElem) {
          fechaInicioElem.textContent = data.fecha_inicio ? `Último inicio: ${data.fecha_inicio}` : "";
        }
        if (fechaFinElem) {
          fechaFinElem.textContent = data.fecha_fin ? `Último fin: ${data.fecha_fin}` : "";
        }

        if (data.fecha_inicio && data.fecha_fin) {
          ultimoCohorte = {
            fecha_inicio: data.fecha_inicio,
            fecha_fin: data.fecha_fin,
            numero: data.numero
          };
        } else {
          ultimoCohorte = null;
        }
      })
      .catch(error => {
        console.error("Error al obtener último cohorte:", error);
      });
  }

  // 3. Función de validación de orden de módulos
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
        if (fechas.modulo1) fechas.modulo1.classList.add("is-invalid");
        if (fechas.modulo2) fechas.modulo2.classList.add("is-invalid");
        if (fechas.modulo3) fechas.modulo3.classList.add("is-invalid");
      }
    }

    alerta.textContent = mensaje;
    alerta.style.display = hayError ? "block" : "none";
    validarFormularioCohorte();
  }

  // 4. Función de validación general del formulario
  function validarFormularioCohorte() {
    const errores = [
      "alerta-cursos-vigentes",
      "alerta-cohorte-unico", 
      "alerta-fecha-inicio",
      "alerta-fechas-pago",
      "alerta-solapamiento-fechas",
      "alerta-orden-fechas"
    ];

    const hayErrores = errores.some(id => {
      const el = document.getElementById(id);
      return el && el.style.display === "block";
    });

    btnSubmit.disabled = hayErrores;
  }

  // 5. Archivar curso vigente
  document.addEventListener("click", function(e) {
    const btn = e.target.closest(".archivar-curso-vigente");
    if (!btn) return;

    const bloque = btn.closest(".curso-asignado");
    if (!bloque) return;

    const estadoInput = bloque.querySelector(".estado-curso-vigente");
    if (estadoInput) estadoInput.value = "0";

    btn.innerHTML = '<i class="fas fa-box-open"></i> Archivado';
    btn.classList.remove("btn-warning");
    btn.classList.add("btn-secondary");
    btn.disabled = true;

    actualizarContadoresCursos();
  });

  // 6. Agregar nuevo curso
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

      <div class="col-md-3 mb-2">
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
			<select class="form-select"  name="cursos_vigentes[${contadorCursos}][dia]" required>
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
      <div class="col-md-1 mb-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger btn-sm eliminar-curso-vigente" title="Eliminar curso">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    `;

    contenedor.appendChild(div);
    actualizarContadoresCursos();

    div.querySelector(".eliminar-curso-vigente").addEventListener("click", () => {
      div.remove();
      validarCursosVigentes();
      actualizarContadoresCursos();
    });

    contadorCursos++;
    validarCursosVigentes();
  });

  // ========== FUNCIONES AUXILIARES ==========

  function actualizarContadoresCursos() {
    const nuevos = document.querySelectorAll("#curso-vigente-contenedor .select-curso-vigente").length;
    const asignados = document.querySelectorAll("#cursos-asignados-contenedor .curso-asignado").length;
    document.getElementById("total_cursos_nuevos").value = nuevos;
    document.getElementById("total_cursos_asignados").value = asignados;

    const total = nuevos + asignados;
    const contadorDiv = document.getElementById("contador-total-cursos");
    if (contadorDiv) {
      contadorDiv.querySelector("strong").textContent = total;
    }
  }

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

    if (alertaCursos) {
      alertaCursos.style.display = valid ? "none" : "block";
    }
    validarFormularioCohorte();
  }

  function validarCohorteUnico() {
    const tipo = tipoCohorteInput.value;
    const numero = document.getElementById("cohorte_numero").value;
    const anio = document.getElementById("cohorte_anio").value;

    if (!tipo || !numero || !anio) {
      if (alertaCohorte) alertaCohorte.style.display = "none";
      validarFormularioCohorte();
      return;
    }

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico_update&tipo=${tipo}&numero=${numero}&anio=${anio}&excluir=${cohorteId}`)
      .then(res => res.json())
      .then(data => {
        if (alertaCohorte) {
          alertaCohorte.textContent = data.valido ? "" : data.mensaje;
          alertaCohorte.style.display = data.valido ? "none" : "block";
        }
        validarFormularioCohorte();
      });
  }

  function validarFechasInicioFin() {
    const inicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
    const fin = new Date(document.getElementById("cohorte_fecha_fin").value);
    const alerta = document.getElementById("alerta-fecha-inicio");

    let mensaje = "";
    const hoy = new Date();
    const hace90 = new Date();
    hace90.setDate(hoy.getDate() - 90);

    //if (inicio < hace90) {
  //    mensaje = "La fecha de inicio no puede ser anterior a los últimos 90 días.";
//    } else 
        
        if (inicio > fin) {
      mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin.";
    }

    if (alerta) {
      alerta.textContent = mensaje;
      alerta.style.display = mensaje ? "block" : "none";
    }
    validarFormularioCohorte();
  }

  function validarFechasDePagoEnRango() {
    const inicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
    const fin = new Date(document.getElementById("cohorte_fecha_fin").value);
    const alerta = document.getElementById("alerta-fechas-pago");

    const campos = [
      "cohorte_fecha_pago_art13",
      "cohorte_fecha_pago_aranceles",
      "cohorte_fecha_modulo1",
      "cohorte_fecha_modulo2",
      "cohorte_fecha_modulo3"
    ];

    let fueraDeRango = [];

    campos.forEach(id => {
      const input = document.getElementById(id);
      if (!input || !input.value) return;
      const fecha = new Date(input.value);
      if (fecha < inicio || fecha > fin) {
        fueraDeRango.push(id);
        input.classList.add("is-invalid");
      } else {
        input.classList.remove("is-invalid");
      }
    });

    if (alerta) {
      alerta.textContent = fueraDeRango.length > 0
        ? "Las fechas de pago deben estar dentro del rango de inicio y fin de la cohorte."
        : "";
      alerta.style.display = fueraDeRango.length > 0 ? "block" : "none";
    }
    validarFormularioCohorte();
  }
});
</script>

<!--script>
document.addEventListener("DOMContentLoaded", () => {
  actualizarContadoresCursos();
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const alertaCohorte = document.getElementById("alerta-cohorte-unico");
  const alertaFechas = document.getElementById("alerta-fecha-inicio");
  const alertaPagos = document.getElementById("alerta-fechas-pago");
  const btnSubmit = document.getElementById("btn-submit-cohorte");
  const cohorteId = document.getElementById("cohorte_id").value;
  // Agregar estas variables globales
  let ultimoCohorte = null;
  const cohorteId = document.getElementById("cohorte_id").value;
  let contadorCursos = 0;
  let tipoCohorteSeleccionado = tipoCohorteInput.value;
  let cursosFiltrados = [];


  document.getElementById("cohorte_tipo").addEventListener("change", function() {
    tipoCohorteSeleccionado = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
    validarCohorteUnico();
    obtenerUltimoCohorte(); // Nueva función
  });

  // Ejecutar al cargar la página
  obtenerUltimoCohorte();
      // Agregar event listeners para validación de módulos
  ["cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"]
    .forEach(id => {
      const input = document.getElementById(id);
      if (input) {
        input.addEventListener("change", validarOrdenFechasModulos);
      }
    });

  // Nueva función para obtener el último cohorte
  function obtenerUltimoCohorte() {
    const tipo = document.getElementById("cohorte_tipo").value;
    const anio = document.getElementById("cohorte_anio").value;

    if (!tipo || !anio) return;

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=obtener_ultimo_cohorte&tipo=${tipo}&anio=${anio}&excluir=${cohorteId}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById("fecha_inicio_previa").textContent = 
          data.fecha_inicio ? `Último inicio: ${data.fecha_inicio}` : "";
        document.getElementById("fecha_fin_previa").textContent = 
          data.fecha_fin ? `Último fin: ${data.fecha_fin}` : "";

        if (data.fecha_inicio && data.fecha_fin) {
          ultimoCohorte = {
            fecha_inicio: data.fecha_inicio,
            fecha_fin: data.fecha_fin,
            numero: data.numero
          };
        } else {
          ultimoCohorte = null;
        }

        // Validar solapamiento después de obtener datos
        validarSolapamientoFechas();
      });
  }

  // Función de validación de solapamiento corregida
  function validarSolapamientoFechas() {
    const fechaInicioInput = document.getElementById("cohorte_fecha_inicio");
    const fechaFinInput = document.getElementById("cohorte_fecha_fin");
    const alerta = document.getElementById("alerta-solapamiento-fechas");

    if (!fechaInicioInput || !fechaFinInput || !alerta) return;

    const fechaInicioNew = new Date(fechaInicioInput.value);
    const fechaFinNew = new Date(fechaFinInput.value);

    let mensaje = "";

    // Validación básica de orden
    if (fechaInicioNew > fechaFinNew) {
      mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin.";
      fechaInicioInput.classList.add("is-invalid");
      fechaFinInput.classList.add("is-invalid");
    } 
    // Validación de solapamiento con último cohorte
    else if (ultimoCohorte && fechaInicioInput.value && fechaFinInput.value) {
      const fechaInicioLast = new Date(ultimoCohorte.fecha_inicio);
      const fechaFinLast = new Date(ultimoCohorte.fecha_fin);

      const solapado = (
        (fechaInicioNew >= fechaInicioLast && fechaInicioNew <= fechaFinLast) ||
        (fechaFinNew >= fechaInicioLast && fechaFinNew <= fechaFinLast) ||
        (fechaInicioNew <= fechaInicioLast && fechaFinNew >= fechaFinLast)
      );

      if (solapado) {
        mensaje = `Las fechas se solapan con el cohorte #${ultimoCohorte.numero} (${ultimoCohorte.fecha_inicio} - ${ultimoCohorte.fecha_fin})`;
        fechaInicioInput.classList.add("is-invalid");
        fechaFinInput.classList.add("is-invalid");
      } else {
        fechaInicioInput.classList.remove("is-invalid");
        fechaFinInput.classList.remove("is-invalid");
      }
    } else {
      fechaInicioInput.classList.remove("is-invalid");
      fechaFinInput.classList.remove("is-invalid");
    }

    alerta.textContent = mensaje;
    alerta.style.display = mensaje ? "block" : "none";
    validarFormularioCohorte();
  }

  // Función de validación de orden de módulos corregida
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
    validarFormularioCohorte();
  }

  // Actualizar la función validarFormularioCohorte para incluir nuevas validaciones
  function validarFormularioCohorte() {
    const errores = [
      "alerta-cursos-vigentes",
      "alerta-cohorte-unico", 
      "alerta-fecha-inicio",
      "alerta-fechas-pago",
      "alerta-solapamiento-fechas",
      "alerta-orden-fechas"
    ];

    const hayErrores = errores.some(id => {
      const el = document.getElementById(id);
      return el && el.style.display === "block";
    });

    btnSubmit.disabled = hayErrores;
  }
    

//archivaar curso vigente
document.addEventListener("click", function(e) {
  const btn = e.target.closest(".archivar-curso-vigente");
  if (!btn) return;

  const bloque = btn.closest(".curso-asignado");
  if (!bloque) return;

  // Solo cambiar el valor del estado
  const estadoInput = bloque.querySelector(".estado-curso-vigente");
  if (estadoInput) estadoInput.value = "0";

  // Opcional: cambiar el texto del botón para indicar que fue marcado
  btn.innerHTML = '<i class="fas fa-box-open"></i> Archivado';
  btn.classList.remove("btn-warning");
  btn.classList.add("btn-secondary");
  btn.disabled = true;

  // Actualizar contador si lo estás mostrando
  actualizarContadoresCursos();
});









  if (tipoCohorteSeleccionado) {
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
  }

  tipoCohorteInput.addEventListener("change", () => {
    tipoCohorteSeleccionado = tipoCohorteInput.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
    validarCohorteUnico();
  });

  ["cohorte_numero", "cohorte_anio"].forEach(id => {
    const input = document.getElementById(id);
    if (input) input.addEventListener("input", validarCohorteUnico);
  });

  ["cohorte_fecha_inicio", "cohorte_fecha_fin"].forEach(id => {
    const input = document.getElementById(id);
    if (input) input.addEventListener("change", () => {
      validarFechasInicioFin();
      validarFechasDePagoEnRango();
    });
  });

  ["cohorte_fecha_pago_art13", "cohorte_fecha_pago_aranceles", "cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"]
    .forEach(id => {
      const input = document.getElementById(id);
      if (input) input.addEventListener("change", validarFechasDePagoEnRango);
    });

  document.addEventListener("input", e => {
    if (e.target.classList.contains("select-curso-vigente")) {
      setTimeout(validarCursosVigentes, 1000);
    }
  });

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

      <div class="col-md-3 mb-2">
        <label class="form-label">Docente de la sección "A"</label>
        <select name="cursos_vigentes[${contadorCursos}][id_profesor_fk]" class="form-select select-docente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${profesoresData.map(p => `
            <option value="${p.id_profesor}">${p.nombre_profesor} ${p.apellido_profesor}</option>
          `).join("")}
        </select>
      </div>
      <div class="col-md-1 mb-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger btn-sm eliminar-curso-vigente" title="Eliminar curso">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    `;

    contenedor.appendChild(div);
    actualizarContadoresCursos();

    div.querySelector(".eliminar-curso-vigente").addEventListener("click", () => {
      div.remove();
      validarCursosVigentes();
      actualizarContadoresCursos();
    });

    contadorCursos++;
    validarCursosVigentes();
  });

  function actualizarContadoresCursos() {
    const nuevos = document.querySelectorAll("#curso-vigente-contenedor .select-curso-vigente").length;
    //const asignados = document.querySelectorAll("#cursos-asignados-contenedor .select-curso-vigente").length;
    const asignados = document.querySelectorAll("#cursos-asignados-contenedor .curso-asignado").length;
    document.getElementById("total_cursos_nuevos").value = nuevos;
    document.getElementById("total_cursos_asignados").value = asignados;

  const total = nuevos + asignados;
  const contadorDiv = document.getElementById("contador-total-cursos");
  if (contadorDiv) {
    contadorDiv.querySelector("strong").textContent = total;
  }


  }

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

    alertaCursos.style.display = valid ? "none" : "block";
    validarFormularioCohorte();
  }

  function validarCohorteUnico() {
    const tipo = tipoCohorteInput.value;
    const numero = document.getElementById("cohorte_numero").value;
    const anio = document.getElementById("cohorte_anio").value;

    if (!tipo || !numero || !anio) {
      alertaCohorte.style.display = "none";
      validarFormularioCohorte();
      return;
    }

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&numero=${numero}&anio=${anio}&excluir=${cohorteId}`)
      .then(res => res.json())
      .then(data => {
     //   alertaCohorte.textContent = data.valido



//c
        alertaCohorte.textContent = data.valido ? "" : data.mensaje;
        alertaCohorte.style.display = data.valido ? "none" : "block";
        validarFormularioCohorte();
      });
  }

  function validarFechasInicioFin() {
    const inicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
    const fin = new Date(document.getElementById("cohorte_fecha_fin").value);
    const alerta = document.getElementById("alerta-fecha-inicio");

    let mensaje = "";
    const hoy = new Date();
    const hace90 = new Date();
    hace90.setDate(hoy.getDate() - 90);

    if (inicio < hace90) {
      mensaje = "La fecha de inicio no puede ser anterior a los últimos 90 días.";
    } else if (inicio > fin) {
      mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin.";
    }

    alerta.textContent = mensaje;
    alerta.style.display = mensaje ? "block" : "none";
    validarFormularioCohorte();
  }

  function validarFechasDePagoEnRango() {
    const inicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
    const fin = new Date(document.getElementById("cohorte_fecha_fin").value);
    const alerta = document.getElementById("alerta-fechas-pago");

    const campos = [
      "cohorte_fecha_pago_art13",
      "cohorte_fecha_pago_aranceles",
      "cohorte_fecha_modulo1",
      "cohorte_fecha_modulo2",
      "cohorte_fecha_modulo3"
    ];

    let fueraDeRango = [];

    campos.forEach(id => {
      const input = document.getElementById(id);
      if (!input || !input.value) return;
      const fecha = new Date(input.value);
      if (fecha < inicio || fecha > fin) {
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

    
    
    
    
    function validarSolapamientoFechas() {
  const inicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
  const fin = new Date(document.getElementById("cohorte_fecha_fin").value);
  const alerta = document.getElementById("alerta-solapamiento-fechas");
  
  if (!alerta) {
    console.warn("Elemento alerta-solapamiento-fechas no encontrado");
    return;
  }

  let mensaje = "";
  if (inicio > fin) {
    mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin.";
  }
  
  alerta.textContent = mensaje;
  alerta.style.display = mensaje ? "block" : "none";
  validarFormularioCohorte();
}

function validarOrdenFechasModulos() {
  const mod1Input = document.getElementById("cohorte_fecha_modulo1");
  const mod2Input = document.getElementById("cohorte_fecha_modulo2");
  const mod3Input = document.getElementById("cohorte_fecha_modulo3");
  const alerta = document.getElementById("alerta-orden-fechas");
  
  if (!alerta) {
    console.warn("Elemento alerta-orden-fechas no encontrado");
    return;
  }

  let mensaje = "";
  
  if (mod1Input.value && mod2Input.value && mod3Input.value) {
    const mod1 = new Date(mod1Input.value);
    const mod2 = new Date(mod2Input.value);
    const mod3 = new Date(mod3Input.value);
    
    if (mod1 > mod2 || mod2 > mod3) {
      mensaje = "Las fechas de módulos deben estar en orden secuencial: Módulo1 ≤ Módulo2 ≤ Módulo3";
      mod1Input.classList.add("is-invalid");
      mod2Input.classList.add("is-invalid");
      mod3Input.classList.add("is-invalid");
    } else {
      mod1Input.classList.remove("is-invalid");
      mod2Input.classList.remove("is-invalid");
      mod3Input.classList.remove("is-invalid");
    }
  }
  
  alerta.textContent = mensaje;
  alerta.style.display = mensaje ? "block" : "none";
  validarFormularioCohorte();
}

    
    
    
    
    
    
    
    
    
  function validarFormularioCohorte() {
    const errores = [
      alertaCursos,
      alertaCohorte,
      alertaFechas,
      alertaPagos
    ];

    const hayErrores = errores.some(el => el && el.style.display === "block");
    btnSubmit.disabled = hayErrores;
  }
});


</script-->





