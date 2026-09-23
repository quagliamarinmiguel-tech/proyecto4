
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

    <script src="<?php echo SERVERURL; ?>vistas/js/miscript.js"></script>
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

        <br><br><br>

        <p class="text-center" style="margin-top: 40px;">
            <!--button type="reset" class="btn btn-raised btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> &nbsp; LIMPIAR</button-->
            &nbsp; &nbsp;
            <button type="submit" class="btn btn-raised btn-info btn-sm"><i class="far fa-save"></i> &nbsp; GUARDAR</button>
        </p>
    </form>
</div> <!--esto taba como --.> no se porque-->





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


<script>
    
document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById("curso_nombre");
  const tipoSelect = document.getElementById("curso_tipo");
  const alertaCurso = document.getElementById("alerta-curso");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  let estadoValidaciones = {
    nombreLargo: false,
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

    if (nombre.length < 4) {
      estadoValidaciones.nombreLargo = false;
      verificarEstadoBoton();
      return;
    } else {
      estadoValidaciones.nombreLargo = true;
    }

    if (tipo === "") return;

    // --- CAMBIO AQUÍ: Consumimos la respuesta como JSON o Texto ---
    fetch(`${SERVERURL}ajax/cursoAjax.php?accion=validar_curso&nombre=${encodeURIComponent(nombre)}&tipo=${encodeURIComponent(tipo)}`)
    //  .then(res => res.json())
      .then(res => {
    console.log('Status:', res.status); // Ver el código de respuesta
    console.log('Headers:', res.headers); // Ver los headers
    return res.json();
  })
      
      // El servidor debe responder 'true' o 'false'
      .then(esValido => { // 'esValido' ahora es directamente el booleano
        
        estadoValidaciones.duplicado = esValido;

        if (!esValido) {
          // --- ESTADO ERROR (Respuesta: false) ---
          alertaCurso.classList.replace("text-success", "text-danger");
          alertaCurso.innerHTML = `<i class="fas fa-times-circle"></i> Ya existe un curso con ese nombre y tipo.`;
          
          nombreInput.style.borderColor = "#ff5722";
          tipoSelect.style.borderColor = "#ff5722";
        } else {
          // --- ESTADO ÉXITO (Respuesta: true) ---
          alertaCurso.classList.replace("text-danger", "text-success");
          alertaCurso.innerHTML = `<i class="fas fa-check-circle"></i> No se puede repetir un curso de mismo nombre y tipo`;
          
          nombreInput.style.borderColor = "";
          tipoSelect.style.borderColor = "";
        }
        verificarEstadoBoton();
      })
      .catch(err => {
        console.error("Error:", err);
        // En caso de error de red, bloqueamos por seguridad
        estadoValidaciones.duplicado = false;
        verificarEstadoBoton();
      });
  }
    
    
    
    
    

  nombreInput.addEventListener("input", validarCurso);
  tipoSelect.addEventListener("change", validarCurso);

  verificarEstadoBoton();
});</script>
