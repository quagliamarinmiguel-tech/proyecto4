<?php
    $state = $_SESSION['profile_status'] ?? 0;
	$cargo = $_SESSION['cargo_spm'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1, 2,3,4,5]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    require_once "./controladores/usuarioControlador.php";
    $ctrl = new usuarioControlador();

$resp = $ctrl->datos_staff_self_controlador();
if (!$resp['ok']) {
    $alerta = $ctrl->usuario_user_config_no_encontrado();
    ?>
    <div class="alert alert-danger text-center mt-5">
        <i class="fas fa-user-slash fa-3x mb-3"></i>
        <h4>Perfil no encontrado</h4>
        <p><?= htmlspecialchars($resp['message']) ?></p>
        <a href="' . SERVERURL . 'index.php?views=admin/home" class="btn btn-primary btn-sm">Volver al inicio</a>
    </div>
    <script>
        alert('El perfil del usuario en sesión no fue encontrado o el perfil es de mantenimiento o configuración. Será redirigido al inicio.');
        window.location.href = '<?= SERVERURL ?>index.php?views=admin/home';
    </script>
    <?php
    exit;
}

$staff = $resp['data'] ?? null;

$staffId = (int) $staff['id'];

// A partir de acá, $staff es un array válido con id > 0
$ci         = htmlspecialchars($staff['ci'] ?? '', ENT_QUOTES, 'UTF-8');
$nombre     = htmlspecialchars($staff['name'] ?? '', ENT_QUOTES, 'UTF-8');
$apellido   = htmlspecialchars($staff['last_name'] ?? '', ENT_QUOTES, 'UTF-8');
$correo     = htmlspecialchars($staff['email'] ?? '', ENT_QUOTES, 'UTF-8');
$roleNumber = (int) ($staff['role_number'] ?? 0);
$state      = (int) ($staff['state'] ?? 0);

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
  <h3 class="text-left"><i class="fas fa-user-edit fa-fw"></i> &nbsp; MI PERFIL</h3>
</div>

<div class="full-box page-header">
    <p class="text-justify">
        Modifique sus datos personales. Si no desea cambiar la contraseña, deje ambos campos de clave vacíos.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-update">
                <i class="fas fa-user-edit fa-fw"></i> &nbsp; EDITAR MI PERFIL
            </a>
        </li>
    </ul>
</div>

<div class="container-fluid">
  <form id="form-usuario" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax.php" method="POST" data-form="update" autocomplete="off">
          <input type="hidden" name="user_unique_id" value="1">
    <fieldset style="background-color:inherit;">
      <legend><i class="fas fa-user-tag"></i> &nbsp; Datos personales</legend>

      <div class="text-success small mt-1" id="alerta-personal" style="font-weight: bold;">
        <i class="fas fa-check-circle"></i> La cédula y el correo no deben repetirse en el sistema
      </div>

      <br>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Cédula</label>
          <div class="small mt-1">Formato: empiece por V o E seguido de 7 u 8 dígitos</div>
          <input type="text" class="form-control" name="ci_personal" id="ci_personal"
                 maxlength="10" pattern="[VE]{1}[1-9]{1}[0-9]{6,7}" placeholder="ej:V12345678"
                 value="<?= $ci ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nombre</label>
          <input type="text" class="form-control" name="nombre_personal" id="nombre_personal"
                 maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}"
                 value="<?= $nombre ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Apellido</label>
          <input type="text" class="form-control" name="apellido_personal" id="apellido_personal"
                 maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}"
                 value="<?= $apellido ?>" required>
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Correo electrónico</label>
          <input type="text" class="form-control" name="correo_personal" id="correo_personal"
                 maxlength="60" value="<?= $correo ?>" required>
        </div>
      </div>
    </fieldset>

    <fieldset style="background-color:inherit">
      <legend><i class="far fa-id-card"></i> &nbsp; Datos de acceso</legend>
      <p class="text-justify"> Deje las claves vacías para no cambiar la contraseña, o ingrese una nueva que cumpla:
  mínimo 8 caracteres, al menos 1 mayúscula, 1 minúscula, 1 número y sin caracteres especiales..</p>

      <div class="text-success small mt-1" id="alerta-clave" style="font-weight: bold;">
        <i class="fas fa-check-circle"></i> Deje las claves vacías para no cambiar, o ingrese una nueva válida y confírmela.
      </div>

      <br>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nueva contraseña (opcional)</label>
      
          <input type="password" class="form-control" name="clave1" id="clave1" pattern="[A-Za-z0-9]{8,100}" minlength="8" maxlength="100" 
                 title="Mínimo 8 caracteres. Solo letras y números. Al menos 1 mayúscula, 1 minúscula y 1 número."  autocomplete="new-password">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Confirmar nueva contraseña</label>
      
          <input type="password" class="form-control" name="clave2" id="clave2" pattern="[A-Za-z0-9]{8,100}" minlength="8" maxlength="100"
   		    title="Debe coincidir exactamente con la nueva contraseña." autocomplete="new-password">
        </div>
      </div>
    </fieldset>

    <p class="text-center">
      <button type="submit" class="btn btn-info btn-sm" id="btn-guardar-usuario">
        <i class="far fa-save"></i> ACTUALIZAR
      </button>
    </p>
  </form>
</div>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const clave1Input = document.getElementById("clave1");
  const clave2Input = document.getElementById("clave2");
  const ciInput     = document.getElementById("ci_personal");
  const correoInput = document.getElementById("correo_personal");
  const alertaClave = document.getElementById("alerta-clave");
  const alertaPersonal = document.getElementById("alerta-personal");
  const btnSubmit   = document.getElementById("btn-guardar-usuario");
  const SERVERURL   = "<?php echo SERVERURL; ?>";
  const STAFF_ID_ENC = "<?= mainModel::encryption($staffId) ?>";   // ← añadido

  let estadoValidaciones = {
    claveValida: true,
    duplicado: false
  };

  function verificarEstadoBoton() {
    const todoOk = Object.values(estadoValidaciones).every(v => v === true);
    btnSubmit.disabled = !todoOk;
    btnSubmit.classList.toggle("btn-disabled", !todoOk);
    btnSubmit.style.opacity = todoOk ? "1" : "0.5";
    btnSubmit.style.cursor = todoOk ? "pointer" : "not-allowed";
  }

  // ─── Claves ───  
    
// Helper para no repetir el patrón de mostrar la alerta
function mostrarAlertaClave(esError, mensaje) {
    alertaClave.classList.remove("text-success", "text-danger");
    alertaClave.classList.add(esError ? "text-danger" : "text-success");
    const icono = esError ? "fa-times-circle" : "fa-check-circle";
    alertaClave.innerHTML = `<i class="fas ${icono}"></i> ${mensaje}`;
}

function validarClaves() {
    const c1 = clave1Input.value;
    const c2 = clave2Input.value;

    // ── Caso 1: ambas vacías → no se cambia la clave ──
    if (c1 === "" && c2 === "") {
        estadoValidaciones.claveValida = true;
        mostrarAlertaClave(false,
            "Deje las claves vacías para no cambiar, o ingrese una nueva válida y confírmela");
        clave1Input.classList.remove("has-error");
        clave2Input.classList.remove("has-error");
        verificarEstadoBoton();
        return;
    }

    // ── Caso 2: una vacía y otra llena → inválido ──
    if (c1 === "" || c2 === "") {
        estadoValidaciones.claveValida = false;
        mostrarAlertaClave(true,
            "Debe completar ambas contraseñas o dejar ambas vacías");
        clave1Input.classList.add("has-error");
        clave2Input.classList.add("has-error");
        verificarEstadoBoton();
        return;
    }

    // ── Caso 3a: ambas llenas → evaluar las 5 reglas ──
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

    // ── Caso 3b: claves válidas pero deben coincidir ──
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
    mostrarAlertaClave(false, "Contraseña válida, se actualizará al guardar");
    clave1Input.classList.remove("has-error");
    clave2Input.classList.remove("has-error");
    verificarEstadoBoton();
}
    
    

  // ─── Duplicados ───
  let timeoutDuplicados = null;

  function validarDuplicados() {
    clearTimeout(timeoutDuplicados);

    const ci     = ciInput.value.trim();
    const correo = correoInput.value.trim();

    if (ci === "" || correo === "") {
      estadoValidaciones.duplicado = false;
      verificarEstadoBoton();
      return;
    }

    timeoutDuplicados = setTimeout(() => {
      fetch(`${SERVERURL}ajax/usuarioAjax.php?accion=validar_usuario_personal&ci=${encodeURIComponent(ci)}&correo=${encodeURIComponent(correo)}&id=${encodeURIComponent(STAFF_ID_ENC)}`)
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

  // ─── Eventos ───
  clave1Input.addEventListener("input", validarClaves);
  clave2Input.addEventListener("input", validarClaves);
  ciInput.addEventListener("input", validarDuplicados);
  correoInput.addEventListener("input", validarDuplicados);

  // ─── Inicialización ───
  validarClaves();
  validarDuplicados();
  verificarEstadoBoton();
});
</script>

