<?php
    $state = $_SESSION['profile_status'] ?? 0;
	$cargo = $_SESSION['cargo_spm'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1]) || $state != 1) {
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
  background-color: inherit;
}
legend {
  font-size: 1.2rem;
  font-weight: bold;
  color: #007bff;
}
input, select {
  border-radius: 6px;
}
.has-error {
  border-color: #dc3545 !important;
  box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
}
.btn-disabled {
  opacity: 0.65;
  pointer-events: none;
}
</style>

<div class="full-box page-header">
  <h3 class="text-left"><i class="fas fa-user-plus fa-fw"></i> &nbsp; NUEVO USUARIO / PERSONAL</h3>
</div>

<div class="full-box page-header">
    <p class="text-justify">
        Aquí puede crear un usuario que usará un miembro del personal de la institución
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO </a>
        </li>
    </ul>
</div>

<div class="container-fluid">
  <form id="form-usuario" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax.php" method="POST" data-form="save" autocomplete="off"  data-cache="true">
    <fieldset>
      <legend><i class="far fa-id-card"></i> &nbsp; Datos de acceso</legend>
<p class="text-justify">
  La clave debe cumplir: mínimo 8 caracteres, al menos 1 mayúscula, 1 minúscula, 1 número y sin caracteres especiales.
</p>
        
      <div class="text-success small mt-1" id="alerta-clave" style="font-weight: bold;">
        <i class="fas fa-check-circle"></i> La clave debe tener mínimo 7 caracteres y coincidir con la confirmación
      </div>

      <br>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Contraseña</label>
          <!--input type="password" class="form-control" name="clave1" id="clave1"
                 pattern="[^ ].*" minlength="7" maxlength="100" required data-no-cache-->
	   	  <input type="password" class="form-control" name="clave1" id="clave1" pattern="[A-Za-z0-9]{8,100}" minlength="8" maxlength="100"
       title="Mínimo 8 caracteres. Solo letras y números. Al menos 1 mayúscula, 1 minúscula y 1 número." autocomplete="new-password" required data-no-cache>
            
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Confirmar contraseña</label>
          <!--input type="password" class="form-control" name="clave2" id="clave2"
                 pattern="[^ ].*" minlength="7" maxlength="100" required data-no-cache-->
          <input type="password" class="form-control" name="clave2" id="clave2" pattern="[A-Za-z0-9]{8,100}" minlength="8" maxlength="100"
               title="Debe coincidir exactamente con la contraseña." autocomplete="new-password" required data-no-cache>
            
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Cargo</label>
          <select name="cargo" id="cargo" class="form-select" required data-no-cache>
            <option value="" disabled selected> - Seleccione - </option>
            <option value="1">Administrador</option>
            <option value="3">Control de estudio</option>
            <option value="2">Administración de pagos</option>
            <option value="4">Supervisor de cursos</option>
          </select>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend><i class="fas fa-user-tag"></i> &nbsp; Datos personales</legend>

      <div class="text-success small mt-1" id="alerta-personal"  style="font-weight: bold;">
        <i class="fas fa-check-circle"></i> La cédula y el correo no deben repetirse en el sistema
      </div>

      <br>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Cédula</label>
            
          <div class="small mt-1">La cédula debe tener un formato que empiece por P o E seguido de 7 u 8 dígitos</div>
            
          <input type="text" class="form-control" name="ci_personal" id="ci_personal"
                maxlength="10" pattern="[VE]{1}[1-9]{1}[0-9]{6,7}" placeholder="ej:V12345678" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nombre</label>
          <input type="text" class="form-control" name="nombre_personal" id="nombre_personal"
                 maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Apellido</label>
          <input type="text" class="form-control" name="apellido_personal" id="apellido_personal"
                 maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Correo electrónico</label>
          <input type="text" class="form-control" name="correo_personal" id="correo_personal"
                 maxlength="60" required>
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
  // REFERENCIAS
  const form        = document.getElementById("form-usuario");
  const clave1Input = document.getElementById("clave1");
  const clave2Input = document.getElementById("clave2");
  const cargoSelect = document.getElementById("cargo");
  const ciInput     = document.getElementById("ci_personal");
  const correoInput = document.getElementById("correo_personal");
  const alertaClave = document.getElementById("alerta-clave");
  const alertaPersonal = document.getElementById("alerta-personal");
  const btnSubmit   = document.getElementById("btn-guardar-usuario");
  const SERVERURL   = "<?php echo SERVERURL; ?>";

  // ESTADO DE VALIDACIONES (true = OK, false = error)
  let estadoValidaciones = {
    claveValida: false,
    cargoValido: false,
    duplicado: false
  };

  // BOTÓN
  function verificarEstadoBoton() {
    const todoOk = Object.values(estadoValidaciones).every(v => v === true);
    btnSubmit.disabled = !todoOk;
    btnSubmit.classList.toggle("btn-disabled", !todoOk);
    btnSubmit.style.opacity = todoOk ? "1" : "0.5";
    btnSubmit.style.cursor = todoOk ? "pointer" : "not-allowed";
  }

  // VALIDACIÓN CLAVES (local)
// Helper para no repetir el patrón de la alerta
function mostrarAlertaClave(esError, mensaje) {
  alertaClave.classList.remove("text-success", "text-danger");
  alertaClave.classList.add(esError ? "text-danger" : "text-success");
  const icono = esError ? "fa-times-circle" : "fa-check-circle";
  alertaClave.innerHTML = `<i class="fas ${icono}"></i> ${mensaje}`;
}

function validarClaves() {
  const c1 = clave1Input.value;
  const c2 = clave2Input.value;

  // ── Ambas vacías → inválido (este form exige claves) ──
  if (c1 === "" && c2 === "") {
    estadoValidaciones.claveValida = false;
    mostrarAlertaClave(true, "Debe ingresar una contraseña y su confirmación");
    clave1Input.classList.add("has-error");
    clave2Input.classList.add("has-error");
    verificarEstadoBoton();
    return;
  }

  // ── Una vacía y otra llena → inválido ──
  if (c1 === "" || c2 === "") {
    estadoValidaciones.claveValida = false;
    mostrarAlertaClave(true, "Debe completar ambas contraseñas");
    clave1Input.classList.add("has-error");
    clave2Input.classList.add("has-error");
    verificarEstadoBoton();
    return;
  }

  // ── Ambas llenas → evaluar las 5 reglas ──
  const errores = [];
  if (c1.length < 8)                 errores.push("mínimo 8 caracteres");
  if (!/[A-Z]/.test(c1))             errores.push("1 mayúscula");
  if (!/[a-z]/.test(c1))             errores.push("1 minúscula");
  if (!/[0-9]/.test(c1))             errores.push("1 número");
  if (!/^[A-Za-z0-9]+$/.test(c1))    errores.push("sin caracteres especiales");

  if (errores.length > 0) {
    estadoValidaciones.claveValida = false;
    mostrarAlertaClave(true, "Falta: " + errores.join(", "));
    clave1Input.classList.add("has-error");
    clave2Input.classList.add("has-error");
    verificarEstadoBoton();
    return;
  }

  // ── Claves válidas pero deben coincidir ──
  if (c1 !== c2) {
    estadoValidaciones.claveValida = false;
    mostrarAlertaClave(true, "Las contraseñas no coinciden");
    clave1Input.classList.add("has-error");
    clave2Input.classList.add("has-error");
    verificarEstadoBoton();
    return;
  }

  // ── Todo OK ──
  estadoValidaciones.claveValida = true;
  mostrarAlertaClave(false, "Contraseña válida y coincide con la confirmación");
  clave1Input.classList.remove("has-error");
  clave2Input.classList.remove("has-error");
  verificarEstadoBoton();
}
    
    
   
    
    
    
  // VALIDACIÓN CARGO (local)
  function validarCargo() {
    const valor = parseInt(cargoSelect.value);
    if (isNaN(valor) || valor < 1 || valor > 4) {
      estadoValidaciones.cargoValido = false;
      cargoSelect.classList.add("has-error");
    } else {
      estadoValidaciones.cargoValido = true;
      cargoSelect.classList.remove("has-error");
    }
    verificarEstadoBoton();
  }

  // VALIDACIÓN DUPLICADOS (AJAX)
  let timeoutDuplicados = null;

    
function validarDuplicados() {
  clearTimeout(timeoutDuplicados);

  const ci     = ciInput.value.trim();
  const correo = correoInput.value.trim();

  // Solo consultar si AMBOS tienen datos
  if (ci === "" || correo === "") {
    estadoValidaciones.duplicado = false;
    verificarEstadoBoton();
    return;
  }

  // Debounce 500ms
  timeoutDuplicados = setTimeout(() => {
    fetch(`${SERVERURL}ajax/usuarioAjax.php?accion=validar_usuario_personal&ci=${encodeURIComponent(ci)}&correo=${encodeURIComponent(correo)}`)
      .then(res => res.json())
      .then(data => {
        if (data && typeof data === "object" && "valido" in data) {
          if (!data.valido) {
            estadoValidaciones.duplicado = false;
            alertaPersonal.classList.remove("text-success");
            alertaPersonal.classList.add("text-danger");
            alertaPersonal.innerHTML = `<i class="fas fa-times-circle"></i> ${data.mensaje || "Los datos ya están registrados"}`;
            correoInput.classList.add("has-error");
            ciInput.classList.add("has-error");
          } else {
            estadoValidaciones.duplicado = true;
            alertaPersonal.classList.remove("text-danger");
            alertaPersonal.classList.add("text-success");
            alertaPersonal.innerHTML = `<i class="fas fa-check-circle"></i> La cédula y el correo no deben repetirse en el sistema`;
            correoInput.classList.remove("has-error");
            ciInput.classList.remove("has-error");
          }
          verificarEstadoBoton();
        }
      })
      .catch(err => {
        console.error("Error validando duplicados:", err);
        estadoValidaciones.duplicado = false;
        verificarEstadoBoton();
      });
  }, 500);
}
    
    
    
    
  // EVENTOS
  clave1Input.addEventListener("input", validarClaves);
  clave2Input.addEventListener("input", validarClaves);
  cargoSelect.addEventListener("change", validarCargo);
  ciInput.addEventListener("input", validarDuplicados);
  correoInput.addEventListener("input", validarDuplicados);

  // INICIALIZAR
  validarClaves();
  validarCargo();
  verificarEstadoBoton();
});
</script>



