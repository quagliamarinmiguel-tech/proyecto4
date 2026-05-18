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
<style>
button[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
  box-shadow: none;
  transition: all 0.3s ease;
}
</style>

<!--style>
.tooltip-custom {
    background: #dc3545;
    color: white;
    padding: 8px 12px;
    border-radius: 4px;
    font-size: 13px;
    font-weight: 500;
    z-index: 9999;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    opacity: 0;
    transition: opacity 0.2s ease-in-out;
    white-space: nowrap;
    pointer-events: none;
    position: fixed !important;
    will-change: transform;
}

.tooltip-custom.show {
    opacity: 1; position: fixed;
}

.btn-disabled {
    opacity: 0.5;
    cursor: not-allowed;
    pointer-events: none;
}
</style-->
<style>
/* Tooltip personalizado - fondo rojo */
.tooltip-personalizado {
  /* Asegurar que sea fijo (Bootstrap ya lo hace, pero reforzamos) */
  position: fixed !important;
}

.tooltip-personalizado .tooltip-inner {
  background-color: #dc3545 !important;  /* Rojo Bootstrap */
  color: white !important;
  font-weight: 500;
  font-size: 13px;
  padding: 8px 12px;
  border-radius: 4px;
  box-shadow: 0 2px 8px rgba(220, 53, 69, 0.3); /* Sombra rojiza */
  max-width: 300px;
  text-align: center;
  border: none;
}

/* Flechita del tooltip en rojo */
.tooltip-personalizado .tooltip-arrow::before {
  border-bottom-color: #dc3545 !important;  /* Flecha abajo */
}

/* Si el tooltip se coloca arriba, la flecha también roja */
.tooltip-personalizado.bs-tooltip-top .tooltip-arrow::before {
  border-top-color: #dc3545 !important;
}

/* Estilo para botón deshabilitado */
.btn-disabled {
  opacity: 0.5;
  cursor: not-allowed;
  pointer-events: none;
}

/* Forzar que el tooltip se vea bien */
.tooltip.show {
  opacity: 1;
}
    
    .custom-tooltip {
  --bs-tooltip-bg: var(--bd-violet-bg);
  --bs-tooltip-color: var(--bs-white);
}

</style>
<style>
/* Cuerpo del tooltip */
.tooltip-danger .tooltip-inner {
    background-color: #dc3545 !important;
    color: white !important;
    font-weight: 500;
    box-shadow: 0 2px 8px rgba(220, 53, 69, 0.4);
    padding: 8px 12px;
}

/* Flecha del tooltip (BS4 usa .arrow y ::before) */
.tooltip-danger.bs-tooltip-top .arrow::before { border-top-color: #dc3545 !important; }
.tooltip-danger.bs-tooltip-bottom .arrow::before { border-bottom-color: #dc3545 !important; }
.tooltip-danger.bs-tooltip-left .arrow::before { border-left-color: #dc3545 !important; }
.tooltip-danger.bs-tooltip-right .arrow::before { border-right-color: #dc3545 !important; }
    
    
    
    /* Forzar que el tooltip ignore el flujo del scroll del contenedor */
.tooltip-danger {
    position: fixed !important;
}

.tooltip-danger .tooltip-inner {
    background-color: #dc3545 !important;
    color: white !important;
    font-weight: 500;
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.15);
    padding: 10px 15px;
    border-radius: 4px;
}

/* Ajuste de flecha para BS4 */
.tooltip-danger.bs-tooltip-bottom .arrow::before {
    border-bottom-color: #dc3545 !important;
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
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
            <!--a class="active" href="<?php echo SERVERURL; ?>course-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a-->
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
            <!--a href="<?php echo SERVERURL; ?>course-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a-->
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
                            <input type="text" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ](?:[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,59})" class="form-control" name="curso_nombre_reg" id="curso_nombre" maxlength="60" required="">
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
                                <!--option value="Otro">Otro</option-->
                            </select>
                        </div>

                    </div>
                </div>
            </div>
        </fieldset>

        <br><br><br>

        <fieldset>
            <!--legend><i class="fas fa-cogs fa-fw"></i> &nbsp; Configuración del curso</legend-->
            <!--div class="container-fluid">
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
            </div-->
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
</div> <!--esto taba como --.> no se porque-->



<!--pope version do bebe-->
<script src="<?php echo SERVERURL; ?>vistas/js/popper.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const $nombreInput = $("#curso_nombre"); // Usamos jQuery para BS4
  const tipoSelect = document.getElementById("curso_tipo");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  function initBootstrapTooltip(mensaje) {
    // Destruir si ya existe
    $nombreInput.tooltip('dispose');

    // Inicializar con el template personalizado
    $nombreInput.tooltip({
      title: mensaje,
      placement: 'bottom',
      trigger: 'manual',
      html: true,
      // Aquí inyectamos la clase 'tooltip-danger' manualmente
      template: '<div class="tooltip tooltip-danger" role="tooltip"><div class="arrow"></div><div class="tooltip-inner"></div></div>',
        popperConfig: {
            positionFixed: true
        }
    }).tooltip('show');
  }

  function ocultarTooltip() {
    $nombreInput.tooltip('hide');
  }

  function validarCurso() {
    const nombre = $nombreInput.val().trim();
    const tipo = tipoSelect.value;

    if (nombre.length >= 3 && tipo !== "") {
      fetch(`${SERVERURL}ajax/cursoAjax.php?accion=validar_curso&nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}`)
        .then(res => res.json())
        .then(data => {
          if (!data.valido) {
            btnSubmit.disabled = true;
            btnSubmit.classList.add("btn-disabled");
            initBootstrapTooltip(data.mensaje);
          } else {
            btnSubmit.disabled = false;
            btnSubmit.classList.remove("btn-disabled");
            ocultarTooltip();
          }
        });
    } else {
      ocultarTooltip();
    }
  }

  // Escuchar eventos
  $nombreInput.on("input", validarCurso);
  tipoSelect.addEventListener("change", validarCurso);
});

</script>
<!--script>
document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById("curso_nombre");
  const tipoSelect = document.getElementById("curso_tipo");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  // Variable para controlar el estado de validación
  let cursoValido = true;
  let tooltipInstance = null;

  // Inicializar tooltip de Bootstrap (solo una vez)
  function initBootstrapTooltip(mensaje) {
    // Si ya existe un tooltip, destruirlo primero
    if (tooltipInstance) {
      tooltipInstance.dispose();
    }

    // Configurar el tooltip con opciones personalizadas
    tooltipInstance = new bootstrap.Tooltip(nombreInput, {
      title: mensaje,
      placement: 'bottom',
      trigger: 'manual', // Control manual (no queremos que aparezca con hover)
      boundary: 'viewport', // Para que sea responsive
      container: 'body',
      animation: true,
      customClass: 'tooltip-danger' // Clase personalizada para el estilo rojo
    });

    // Mostrar el tooltip
    tooltipInstance.show();
  }

  // Función para ocultar el tooltip
  function ocultarTooltip() {
    if (tooltipInstance) {
      tooltipInstance.hide();
      // Pequeño retraso para permitir la animación antes de destruir
      setTimeout(() => {
        if (tooltipInstance) {
          tooltipInstance.dispose();
          tooltipInstance = null;
        }
      }, 200);
    }
  }

  // Función para validar el curso (solo contra la BD)
  function validarCurso() {
    const nombre = nombreInput.value.trim();
    const tipo = tipoSelect.value;

    // Solo validar si ambos campos tienen valor
    if (nombre.length > 0 && tipo !== "") {
      fetch(`${SERVERURL}ajax/cursoAjax.php?accion=validar_curso&nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}`)
        .then(res => res.json())
        .then(data => {
          if (!data.valido) {
            // Curso duplicado - mostrar error
            cursoValido = false;
            btnSubmit.disabled = true;
            btnSubmit.classList.add("btn-disabled");
            
            // USAR EL TOOLTIP DE BOOTSTRAP
            initBootstrapTooltip(data.mensaje);
          } else {
            // Curso válido - habilitar botón y OCULTAR TOOLTIP
            cursoValido = true;
            btnSubmit.disabled = false;
            btnSubmit.classList.remove("btn-disabled");
            
            // OCULTAR EL TOOLTIP
            ocultarTooltip();
          }
        })
        .catch(err => {
          console.error("Error al validar curso:", err);
        });
    } else {
      // Si falta algún campo, deshabilitar botón pero sin tooltip
      btnSubmit.disabled = true;
      btnSubmit.classList.add("btn-disabled");
      ocultarTooltip(); // Asegurar que no haya tooltips visibles
    }
  }

  // Event listeners
  nombreInput.addEventListener("input", validarCurso);
  tipoSelect.addEventListener("change", validarCurso);

  // Limpiar tooltip si el input pierde foco (opcional)
  nombreInput.addEventListener("blur", () => {
    if (cursoValido) {
      ocultarTooltip();
    }
  });

  // Validación inicial
  validarCurso();
});
</script-->


<!-- Popper.js v1 ->
<script src="<php echo SERVERURL; ?>vistas/js/popper.min.js"></script>

<!--script>
document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById("curso_nombre");
  const tipoSelect = document.getElementById("curso_tipo");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  // Variable para controlar el estado de validación
  let cursoValido = true;
  let popperInstance = null;
  let tooltipActual = null;

  // Función para crear tooltip fijo con Popper v1
  function mostrarTooltip(elemento, mensaje) {
    // Si ya hay un tooltip, no crear otro
    if (tooltipActual) {
      // Actualizar el mensaje si es diferente
      if (tooltipActual.textContent !== mensaje) {
        tooltipActual.textContent = mensaje;
        if (popperInstance) {
          popperInstance.update();
        }
      }
      return;
    }

    // Crear nuevo tooltip (fondo rojo, texto blanco)
    const tooltip = document.createElement('div');
    tooltip.className = 'tooltip-custom';
    tooltip.textContent = mensaje;
    tooltip.setAttribute('role', 'tooltip');

    document.body.appendChild(tooltip);
    tooltipActual = tooltip;

    // Inicializar Popper v1 con configuración para tooltip fijo
    popperInstance = new Popper(elemento, tooltip, {
      placement: 'bottom',
      positionFixed: true,  // <-- ESTA ES LA CLAVE: hace que el tooltip sea fixed
      modifiers: {
        offset: {
          enabled: true,
          offset: '0,8'
        },
        preventOverflow: {
          enabled: true,
          boundariesElement: 'viewport'
        },
        flip: {
          enabled: false  // Deshabilitar flip para que no cambie de posición
        },
        keepTogether: {
          enabled: true
        }
      },
      onCreate: function() {
        // Hacer el tooltip fijo (position: fixed)
        tooltip.style.position = 'fixed';
        // Mostrar con animación después de crear
        setTimeout(() => {
          tooltip.classList.add('show');
        }, 10);
      }
    });

    // Forzar actualización después de un pequeño retraso
    setTimeout(() => {
      if (popperInstance) {
        popperInstance.update();
      }
    }, 50);
  }

  // Función para ocultar/eliminar tooltip
  function ocultarTooltip() {
    if (tooltipActual) {
      tooltipActual.classList.remove('show');
      setTimeout(() => {
        if (tooltipActual) {
          tooltipActual.remove();
          if (popperInstance) {
            popperInstance.destroy();
            popperInstance = null;
          }
          tooltipActual = null;
        }
      }, 200);
    }
  }

  // Función para validar el curso (solo contra la BD)
  function validarCurso() {
    const nombre = nombreInput.value.trim();
    const tipo = tipoSelect.value;

    // Solo validar si ambos campos tienen valor
    if (nombre.length > 0 && tipo !== "") {
      fetch(`${SERVERURL}ajax/cursoAjax.php?accion=validar_curso&nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}`)
        .then(res => res.json())
        .then(data => {
          if (!data.valido) {
            // Curso duplicado - mostrar/actualizar error
            cursoValido = false;
            btnSubmit.disabled = true;
            btnSubmit.classList.add("btn-disabled");
            mostrarTooltip(nombreInput, data.mensaje);
          } else {
            // Curso válido - habilitar botón y OCULTAR TOOLTIP
            cursoValido = true;
            btnSubmit.disabled = false;
            btnSubmit.classList.remove("btn-disabled");
            
            // OCULTAR EL TOOLTIP SOLO CUANDO SEA VÁLIDO
            ocultarTooltip();
          }
        })
        .catch(err => {
          console.error("Error al validar curso:", err);
        });
    } else {
      // Si falta algún campo, deshabilitar botón pero sin tooltip
      btnSubmit.disabled = true;
      btnSubmit.classList.add("btn-disabled");
    }
  }

  // Event listeners
  nombreInput.addEventListener("input", validarCurso);
  tipoSelect.addEventListener("change", validarCurso);

  // NO eliminar tooltip al hacer scroll (ahora es fijo)
  // Solo actualizar posición si es necesario
  window.addEventListener('scroll', () => {
    if (popperInstance && tooltipActual) {
      // Pequeña actualización para mantener posición relativa
      popperInstance.update();
    }
  });

  // Actualizar popper al cambiar tamaño de ventana
  window.addEventListener('resize', () => {
    if (popperInstance && tooltipActual) {
      popperInstance.update();
    }
  });

  // Validación inicial
  validarCurso();
});
</script-->


<!--script> este es el bueno
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
</script-->
