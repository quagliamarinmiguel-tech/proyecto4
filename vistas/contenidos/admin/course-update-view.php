<?php  
    $cargo = $_SESSION['cargo_spm'] ?? 0;
	$state = $_SESSION['profile_status'] ?? 0;

    if (!in_array($cargo, [1, 4]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    $carpeta =  "admin";

    require_once "./controladores/cursoControlador.php";
    $ins_curso = new cursoControlador();
  
    $course_id = $_GET['course_id_up'] ?? '';
    $c_id = mainModel::decryption($course_id);

    $datos_curso = $ins_curso->datos_curso_controlador("Unico", $course_id);

    if(is_array($datos_curso) && !empty($datos_curso)){
    $campos = $datos_curso;
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
      <i class="fas fa-sync-alt fa-fw"></i> &nbsp; ACTUALIZAR Y CONSULTAR CURSO #<?php echo $campos['id']; ?>
    </h3>
    <p class="text-justify">
        Actualiza la información del curso seleccionado. Consulta datos del curso mediante los reportes.
    </p>
</div>


<fieldset>
    
    <div class="card-header bg-dark text-white">
      Reportes
    </div>
    <br>
    
    <div class="row">
        <div class="col-md-3 mb-3">
            <div class="d-flex align-items-center card justify-content-between">
                <span class="fw-bold text-secondary"> Inscritos actuales en el curso</span>
                <div class="d-flex gap-2">
                     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_curso_inscritos&course_id=<?= $c_id ?>" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
				     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_curso_inscritos&course_id=<?= $c_id ?>" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i></a>
                     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_curso_inscritos&course_id=<?= $c_id ?>" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
            <div class="d-flex align-items-center card justify-content-between">
                <span class="fw-bold text-secondary"> Recaudación del curso (último año)</span>
                <div class="d-flex gap-2">                    
                     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_curso_recaudacion&course_id=<?= $c_id ?>" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
				     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_curso_recaudacion&course_id=<?= $c_id ?>" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i></a>
                     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_curso_recaudacion&course_id=<?= $c_id ?>" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                </div>
            </div>
        </div>
        <div class="col-md-3 mb-3">
             <div class="d-flex align-items-center card justify-content-between">
                  <span class="fw-bold text-secondary"> Historial de aprobados (último año)</span>
                  <div class="d-flex gap-2">
                     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_curso_aprobados&course_id=<?= $c_id ?>" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
				     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_curso_aprobados&course_id=<?= $c_id ?>" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i></a>
                     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_curso_aprobados&course_id=<?= $c_id ?>" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                  </div>
             </div>
        </div>    
        
        <div class="col-md-3 mb-3">
             <div class="d-flex align-items-center card justify-content-between">
                  <span class="fw-bold text-secondary"> Profesores que dictaron el curso</span>
                  <div class="d-flex gap-2">       
                  	 <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_curso_profesores&course_id=<?= $c_id ?>" class="btn btn-warning btn-sm" target="_blank" title="Descargar PDF"><i class="fas fa-file-pdf"></i></a>
				     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_curso_profesores&course_id=<?= $c_id ?>" class="btn btn-success btn-sm" target="_blank" title="Descargar Excel"><i class="fas fa-file-excel"></i></a>
                     <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_curso_profesores&course_id=<?= $c_id ?>" class="btn btn-sm" target="_blank" title="Descargar CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                  </div>
             </div>
        </div>  
    </div>
    
</fieldset>



<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        
        <?php if (in_array($cargo, [1, 4])): ?>
           <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-new'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; NUEVO CURSO</a>
        </li>
        <?php endif; ?>
        
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
        </li>
        
        
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=course-update&course_id_up=<?php echo $course_id; ?>"><i class="fas fa-sync-alt fa-fw"></i> &nbsp; ACTUALIZAR CURSO</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list-archived'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS ARCHIVADOS</a>
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoAjax.php" method="POST" data-form="update" autocomplete="off">

        <input type="hidden" name="curso_id_up" id="curso_id" value="<?php echo $campos['id']; ?>">

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
                            <input type="text" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ](?:[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,59})" class="form-control" name="curso_nombre_up" id="curso_nombre" maxlength="100" value="<?php echo htmlspecialchars($campos['name']); ?>" required="">
                        </div>
                    </div>
                    <div class="col-12 col-md-6">
                        <div class="form-group">
                            <label for="curso_tipo" class="bmd-label-floating">Tipo de curso</label>
                      
                        <?php if ($tipoFijo): ?>
                            <select class="form-control" name="curso_tipo_up" id="curso_tipo" disabled>
                                <option value="<?php echo htmlspecialchars($campos['type']); ?>" selected><?php echo htmlspecialchars($campos['type']); ?></option>
                            </select>
                            <input type="hidden" name="curso_tipo_up" value="<?php echo $tipoFijo; ?>">
                            <div class="form-text text-muted">Este curso está vinculado a un periodo intensivo o en un cohorte. No se puede cambiar el tipo.</div>
                        <?php else: ?>
                            <select class="form-control" name="curso_tipo_up" id="curso_tipo" required>
                                <option value="Intensivo" <?php echo ($campos['type']=='Intensivo') ? 'selected' : ''; ?>>Intensivo</option>
                                <option value="Extendido" <?php echo ($campos['type']=='Extendido') ? 'selected' : ''; ?>>Extendido</option>
                                <option value="Técnico Profesional" <?php echo ($campos['type']=='Técnico Profesional') ? 'selected' : ''; ?>>Técnico Profesional</option>
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
  const idInput = document.getElementById("curso_id"); 
  const alertaCurso = document.getElementById("alerta-curso");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  // LISTA DE VALIDACIONES
  let estadoValidaciones = {
    nombreLargo: true, 
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
        //console.log("Status Code:", res.status); 
        return res.json();
      })
      .then(esValido => {
        //console.log("¿Es el curso válido (no duplicado)?:", esValido);

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

