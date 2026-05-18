<?php
if($_SESSION['cargo_spm']!=1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}
?>

<style>
fieldset {
  border: 1px solid #ccc;
  padding: 20px;
  border-radius: 8px;
  margin-bottom: 30px;
  background-color: #f9f9f9;
}
legend {
  font-size: 1.2rem;
  font-weight: bold;
  color: #007bff;
}
input, select {
  border-radius: 6px;
}
#alerta-usuario {
  display: none;
  font-size: 1rem;
  margin-top: 0.5rem;
}
</style>

<div class="full-box page-header">
  <h3 class="text-left"><i class="fas fa-user-plus fa-fw"></i> &nbsp; NUEVO USUARIO / PERSONAL</h3>
</div>




<div class="full-box page-header">

    <p class="text-justify">
        Aquí puede crear un usuario que usará un miembro del personal de la institucion
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user4-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/PERSONAL</a>
        </li>

        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user3-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/ESTUDIANTE</a>
        </li>
        
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-personal-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/PERSONAL</a>       
        </li>

        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-estudiante-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/ESTUDIANTE</a>       
        </li>    
    
    </ul>   
</div>



<div class="container-fluid">
  <form id="form-usuario" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax2.php" method="POST" data-form="save" autocomplete="off">
    <fieldset>
      <legend><i class="far fa-id-card"></i> &nbsp; Datos de acceso</legend>
            <p class="text-justify">
Mínimo 7 caracteres para la clave    </p>
      <div class="text-danger fw-bold" id="alerta-usuario"></div>
      <br><br>
      <div class="row">
        <!-- Nombre de usuario -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nombre de usuario</label>
          <input type="text" class="form-control" name="Nombre_u" required pattern="[^ ].*" id="Nombre_u" maxlength="100" required>
        </div>

        <!-- Clave -->
          
          
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Contraseña</label>
          <input type="password" class="form-control" name="clave1" required pattern="[^ ].*" minlength="7" title="El nombre no debe comenzar con espacio."
 id="clave1" maxlength="100" required>
        </div>

        <!-- Confirmar clave -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Confirmar contraseña</label>
          <input type="password" class="form-control" name="clave2" required pattern="[^ ].*" minlength="7" title="El nombre no debe comenzar con espacio."
 id="clave2" maxlength="100" required>
        </div>

        <!-- Cargo -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Cargo</label>
          <select name="cargo" id="cargo" class="form-select" required>
            <option value="" disabled selected> - Seleccione - </option>
            <option value="1">Administrador</option>
            <option value="2">Control de estudio</option>
            <option value="3">Administración de pagos</option>
            <option value="4">Supervisor de cursos</option>
          </select>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend><i class="fas fa-user-tag"></i> &nbsp; Datos personales</legend>
        
      <div class="row">
        <!-- Cédula -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Cédula</label>
          <input type="text" class="form-control" name="ci_personal" id="ci_personal"  maxlength="9" pattern="[1-9]{1}[0-9]{6,7}" placeholder="ej:12345678" required>
        </div>

        <!-- Nombre -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nombre</label>
          <input type="text" class="form-control" name="nombre_personal" id="nombre_personal" maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}" required>
        </div>

          
<!--div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Nombre</label>
                    <input type="text" class="form-control" name="nombre_reg" maxlength="20" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,19}"  required>
                </di-v-->
          
        <!-- Apellido -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Apellido</label>
          <input type="text" class="form-control" name="apellido_personal" id="apellido_personal" maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}" required>
        </div>

        <!-- Correoc -->
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Correo electrónico</label>
          <input type="text" class="form-control" name="correo_personal" id="correo_personal"  maxlength="60" required>
        </div>
      </div>
    </fieldset>

    <p class="text-center">
      <button type="submit" class="btn btn-info btn-sm" id="btn-guardar-usuario">
        <i class="far fa-save"></i> GUARDAR
      </button>
    </p>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const nombreInput = document.getElementById("Nombre_u");
  const clave1Input = document.getElementById("clave1");
  const clave2Input = document.getElementById("clave2");
  const cargoSelect = document.getElementById("cargo");
  const ciInput = document.getElementById("ci_personal");
  const correoInput = document.getElementById("correo_personal");
  const alerta = document.getElementById("alerta-usuario");
  const btnSubmit = document.getElementById("btn-guardar-usuario");
  const SERVERURL = "<?php echo SERVERURL; ?>";

  function mostrarError(mensaje) {
    alerta.textContent = mensaje;
    alerta.style.display = "block";
    btnSubmit.disabled = true;
  }

  function limpiarError() {
    alerta.textContent = "";
    alerta.style.display = "none";
    btnSubmit.disabled = false;
  }

  function validarUsuarioPersonal() {
    const nombre = nombreInput.value.trim();
    const clave1 = clave1Input.value.trim();
    const clave2 = clave2Input.value.trim();
    const cargo = parseInt(cargoSelect.value);
    const ci = ciInput.value.trim();
    const correo = correoInput.value.trim();

    if (nombre === "" || clave1 === "" || clave2 === "" || isNaN(cargo) || ci === "" || correo === "") {
      mostrarError("Complete todos los campos obligatorios.");
      return;
    }

    if (clave1 !== clave2) {
      mostrarError("Las contraseñas no coinciden.");
      return;
    }

    if (cargo < 1 || cargo > 4) {
      mostrarError("El cargo seleccionado no es válido.");
      return;
    }
	console.log("Enviando validación AJAX con:", { nombre, ci, correo });
fetch(`${SERVERURL}ajax/usuarioAjax2.php?accion=validar_usuario_personal&nombre=${encodeURIComponent(nombre)}&ci=${encodeURIComponent(ci)}&correo=${encodeURIComponent(correo)}`)
  .then(res => {
    console.log("Estado HTTP:", res.status);
    return res.text(); // capturamos texto crudo
  })
  .then(texto => {
    console.log("Respuesta cruda:", texto);
    try {
      const data = JSON.parse(texto);
      if (data && typeof data === "object" && "valido" in data) {
        if (!data.valido) {
          mostrarError(data.mensaje || "Hay un error en los datos.");
        } else {
          limpiarError();
        }
      } else {
        mostrarError("Respuesta inesperada del servidor.");
      }
    } catch (e) {
      console.error("Error al parsear JSON:", e);
      mostrarError("La respuesta del servidor no es válida.");
    }
  })
  .catch(err => {
    console.error("Error de red o fetch:", err);
    mostrarError("Error de conexión con el servidor.");
  });

  }

  nombreInput.addEventListener("input", validarUsuarioPersonal);
  clave1Input.addEventListener("input", validarUsuarioPersonal);
  clave2Input.addEventListener("input", validarUsuarioPersonal);
  cargoSelect.addEventListener("change", validarUsuarioPersonal);
  ciInput.addEventListener("input", validarUsuarioPersonal);
  correoInput.addEventListener("input", validarUsuarioPersonal);
});
</script>