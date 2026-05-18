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


<?php
    require_once "./controladores/cursoControlador.php";
    $ins_curso = new cursoControlador();
  
    $course_id = $_GET['course_id_up'] ?? '';

    $datos_curso = $ins_curso->datos_curso_controlador("Unico", $course_id);

    if($datos_curso->rowCount()==1){
        $campos = $datos_curso->fetch();
        $tipoFijo = $ins_curso->verificar_curso_periodo_existente($course_id);

?>

<style>
button[disabled] {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
  box-shadow: none;
  filter: grayscale(40%);
  transition: all 0.3s ease;
}
.btn-disabled {
  opacity: 0.6;
  cursor: not-allowed;
  pointer-events: none;
  box-shadow: none;
  filter: grayscale(40%);
}
</style>

<style>
/* Estilos para inputs con error */
.has-error {
    border-color: #dc3545 !important;
    box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}

/* Estilo para el botón deshabilitado */
.btn-disabled {
    opacity: 0.65;
    pointer-events: none;
}

/* Estilo para la lista de errores */
.alertas-lista {
    margin-bottom: 20px;
    padding: 10px;
    border-radius: 4px;
    background-color: #f8d7da;
    border: 1px solid #f5c6cb;
    color: #721c24;
}

.alertas-lista ul {
    margin: 0;
    padding-left: 20px;
}

.alertas-lista li {
    margin: 5px 0;
}
</style>




<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-sync-alt fa-fw"></i> &nbsp; ACTUALIZAR CURSO #<?php echo $campos['id_curso']; ?>
    </h3>
    <p class="text-justify">
        Actualiza la información del curso seleccionado. Todos los campos son obligatorios.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
            <!--a href="<?php echo SERVERURL; ?>index.php?views=course-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a-->
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
            <!--a href="<?php echo SERVERURL; ?>index.php?views=course-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a-->
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=course-update&course_id_up=<?php echo $course_id; ?>"><i class="fas fa-sync-alt fa-fw"></i> &nbsp; ACTUALIZAR CURSO</a>
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoAjax.php" method="POST" data-form="update" autocomplete="off">

        <input type="hidden" name="curso_id_up" id="curso_id" value="<?php echo $campos['id_curso']; ?>">

        <fieldset>
            <legend><i class="fas fa-book fa-fw"></i> &nbsp; Información del curso</legend>
            <!--div class="text-danger small mt-1" id="alerta-curso" style="display:none;">
                <!-- Mensaje dinámico ->
            </div-->
            <div class="text-success small mt-1" id="alerta-curso" style="font-weight: bold;">
                <i class="fas fa-check-circle"></i> No se puede repetir un curso de mismo nombre y tipo
            </div>

            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="curso_nombre" class="bmd-label-floating">Nombre del curso</label>
                            <input type="text" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ](?:[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,59})" class="form-control" name="curso_nombre_up" id="curso_nombre" maxlength="100" value="<?php echo htmlspecialchars($campos['nombre_curso']); ?>" required="">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="curso_tipo" class="bmd-label-floating">Tipo de curso</label>
                      
                        <?php if ($tipoFijo): ?>
                            <select class="form-control" name="curso_tipo_up" id="curso_tipo" disabled>
                                <option value="<?php echo $tipoFijo; ?>" selected><?php echo $tipoFijo; ?></option>
                            </select>
                            <input type="hidden" name="curso_tipo_up" value="<?php echo $tipoFijo; ?>">
                            <div class="form-text text-muted">Este curso está vinculado a un periodo intensivo o en un cohorte. No se puede cambiar el tipo.</div>
                        <?php else: ?>
                            <select class="form-control" name="curso_tipo_up" id="curso_tipo" required>
                                <option value="Intensivo" <?php echo ($campos['tipo_curso']=='Intensivo') ? 'selected' : ''; ?>>Intensivo</option>
                                <option value="Extendido" <?php echo ($campos['tipo_curso']=='Extendido') ? 'selected' : ''; ?>>Extendido</option>
                                <option value="Técnico Profesional" <?php echo ($campos['tipo_curso']=='Técnico Profesional') ? 'selected' : ''; ?>>Técnico Profesional</option>
                            </select>
                        <?php endif; ?>
                                  
                        
                        </div>
                    </div>
				</div>
            </div>
        </fieldset>

        <br><br><br>

        <br><br><br>


        <p class="text-center" style="margin-top: 40px;">
            <button type="submit" class="btn btn-raised btn-success btn-sm"><i class="fas fa-sync-alt"></i> &nbsp; ACTUALIZAR</button>
        </p>
    </form>
</div>

<?php 
    }else{
        echo '<div class="alert alert-danger text-center" role="alert">
            <i class="fas fa-exclamation-triangle fa-5x"></i>
            <h4 class="alert-heading">¡Ocurrió un error inesperado!</h4>
            <p class="mb-0">No hemos encontrado el curso solicitado</p>
        </div>';
    }
?>



<script>
document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById("curso_nombre");
  const tipoSelect = document.getElementById("curso_tipo");
  const idInput = document.getElementById("curso_id"); // Capturamos el ID oculto
  const alertaCurso = document.getElementById("alerta-curso");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  // LISTA DE VALIDACIONES
  let estadoValidaciones = {
    nombreLargo: true, // En edición suele venir ya con nombre, por eso iniciamos en true
    duplicado: true 
  };

  function verificarEstadoBoton() {
    const tieneErrores = Object.values(estadoValidaciones).includes(false);
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
  }

  function validarCurso() {
    const nombre = nombreInput.value.trim();
    const tipo = tipoSelect.value;
    const id = idInput.value; // El ID del curso actual

    // 1. Validación de longitud
    if (nombre.length < 4) {
      estadoValidaciones.nombreLargo = false;
      verificarEstadoBoton();
      return;
    } else {
      estadoValidaciones.nombreLargo = true;
    }

    if (tipo === "") return;

    // 2. AJAX enviando el ID para excluirlo de la búsqueda de duplicados
    fetch(`${SERVERURL}ajax/cursoAjax.php?accion=validar_curso&nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}&id=${encodeURIComponent(id)}`)
      .then(res => {
        // Console log de la respuesta bruta (opcional para debug)
        console.log("Status Code:", res.status); 
        return res.json();
      })
      .then(esValido => {
        // CONSOLE LOG de los datos devueltos (true o false)
        console.log("¿Es el curso válido (no duplicado)?:", esValido);

        estadoValidaciones.duplicado = esValido;

        if (!esValido) {
          // --- ESTADO ERROR ---
          alertaCurso.classList.replace("text-success", "text-danger");
          alertaCurso.innerHTML = `<i class="fas fa-times-circle"></i> Este nombre y tipo ya están registrados en otro curso.`;
          
          nombreInput.style.borderColor = "#ff5722";
          tipoSelect.style.borderColor = "#ff5722";
        } else {
          // --- ESTADO ÉXITO ---
          alertaCurso.classList.replace("text-danger", "text-success");
          alertaCurso.innerHTML = `<i class="fas fa-check-circle"></i> No se puede repetir un curso de mismo nombre y tipo`;
          
          nombreInput.style.borderColor = "";
          tipoSelect.style.borderColor = "";
        }
        verificarEstadoBoton();
      })
      .catch(err => {
        console.error("Error en la petición AJAX:", err);
        estadoValidaciones.duplicado = false;
        verificarEstadoBoton();
      });
  }

  // Eventos para disparar la validación
  nombreInput.addEventListener("input", validarCurso);
  tipoSelect.addEventListener("change", validarCurso);

  // Ejecución inicial para validar los datos que ya vienen cargados
  validarCurso(); 
        });</script>


<!--script>
document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById("curso_nombre");
  const tipoSelect = document.getElementById("curso_tipo");
  const idCurso = document.getElementById("curso_id").value;
  const alertaCurso = document.getElementById("alerta-curso");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  function actualizarEstiloBoton() {
    btnSubmit.classList.toggle("btn-disabled", btnSubmit.disabled);
  }

  function validarCursoEdicion() {
    const nombre = nombreInput.value.trim();
    const tipo = tipoSelect.value;

    if (nombre.length < 4 || tipo === "") {
      alertaCurso.style.display = "none";
      btnSubmit.disabled = true;
      actualizarEstiloBoton();
      return;
    }

    fetch(`${SERVERURL}ajax/cursoAjax.php?accion=validar_curso_edicion&nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}&id=${idCurso}`)
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

  .catch(async err => {
  console.error("Error al validar curso:", err);

  let mensajeError = "Error de conexión con el servidor.";

  if (err instanceof Response) {
    try {
      const errorData = await err.json();
      if (errorData.mensaje) {
        mensajeError = errorData.mensaje;
      } else if (errorData.Texto) {
        mensajeError = errorData.Texto;
      }
    } catch (parseError) {
      mensajeError = "Error inesperado al procesar la respuesta del servidor.";
    }
  } else if (err.message) {
    mensajeError = `Error: ${err.message}`;
  }

  alertaCurso.textContent = mensajeError;
  alertaCurso.style.display = "block";
  btnSubmit.disabled = true;
  actualizarEstiloBoton();
});

  }


  nombreInput.addEventListener("input", validarCursoEdicion);
  tipoSelect.addEventListener("change", validarCursoEdicion);
});
</script-->


