<?php
if($_SESSION['cargo_spm']!=1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/usuarioControlador.php";
$ctrl = new usuarioControlador();

$idEnc = $_GET['user_id_up'] ?? '';
if ($idEnc === '') {
    echo '<div class="alert alert-danger">ID de usuario no proporcionado.</div>';
    exit;
}

$resp = $ctrl->datos_staff_controlador($idEnc);
if (!$resp['ok']) {
    echo '<div class="alert alert-danger">' . htmlspecialchars($resp['message']) . '</div>';
    exit;
}

$staff      = $resp['data'];
$staffId    = (int) $staff['id'];
$ci         = htmlspecialchars($staff['ci'] ?? '', ENT_QUOTES, 'UTF-8');
$nombre     = htmlspecialchars($staff['name'] ?? '', ENT_QUOTES, 'UTF-8');
$apellido   = htmlspecialchars($staff['last_name'] ?? '', ENT_QUOTES, 'UTF-8');
$correo     = htmlspecialchars($staff['email'] ?? '', ENT_QUOTES, 'UTF-8');
$roleNumber = (int) ($staff['role_number'] ?? 0);
$state      = (int) ($staff['state'] ?? 0);
$idEncEdit  = mainModel::encryption($staffId);
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
  <h3 class="text-left"><i class="fas fa-user-edit fa-fw"></i> &nbsp; ACTUALIZAR USUARIO / PERSONAL</h3>
</div>

<div class="full-box page-header">
    <p class="text-justify">
        Modifique los datos del usuario según sea necesario. Si no desea cambiar la contraseña, deje ambos campos de clave vacíos.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/PERSONAL</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-personal-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/PERSONAL</a>
        </li>
    </ul>
</div>

<div class="container-fluid">
  <form id="form-usuario" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax.php" method="POST" data-form="update" autocomplete="off">
    <input type="hidden" name="staff_id_up" value="<?= $idEncEdit ?>">

    <fieldset>
      <legend><i class="far fa-id-card"></i> &nbsp; Datos de acceso</legend>
      <p class="text-justify">Mínimo 7 caracteres si cambia la contraseña.</p>

      <div class="text-success small mt-1" id="alerta-clave" style="font-weight: bold;">
        <i class="fas fa-check-circle"></i> Deje las claves vacías para no cambiar, o ingrese al menos 7 caracteres y confirme
      </div>

      <br>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nueva contraseña (opcional)</label>
          <input type="password" class="form-control" name="clave1" id="clave1"
                 pattern="[^ ].*" maxlength="100">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Confirmar nueva contraseña</label>
          <input type="password" class="form-control" name="clave2" id="clave2"
                 pattern="[^ ].*" maxlength="100">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Cargo</label>
          <select name="cargo" id="cargo" class="form-select" required>
            <option value="" disabled> - Seleccione - </option>
            <option value="1" <?= $roleNumber === 1 ? 'selected' : '' ?>>Administrador</option>
            <option value="3" <?= $roleNumber === 3 ? 'selected' : '' ?>>Control de estudio</option>
            <option value="2" <?= $roleNumber === 2 ? 'selected' : '' ?>>Administración de pagos</option>
            <option value="4" <?= $roleNumber === 4 ? 'selected' : '' ?>>Supervisor de cursos</option>
          </select>
        </div>

        <!--div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Estado</label>
          <select name="state" id="state" class="form-select" required>
            <option value="1" <?= $state === 1 ? 'selected' : '' ?>>Activo</option>
            <option value="0" <?= $state === 0 ? 'selected' : '' ?>>Inactivo</option>
          </select>
        </div-->
      </div>
    </fieldset>

    <fieldset>
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

    <p class="text-center">
      <button type="submit" class="btn btn-info btn-sm" id="btn-guardar-usuario">
        <i class="far fa-save"></i> ACTUALIZAR
      </button>
    </p>
  </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  // ==============================================
  // REFERENCIAS
  // ==============================================
  const clave1Input = document.getElementById("clave1");
  const clave2Input = document.getElementById("clave2");
  const cargoSelect = document.getElementById("cargo");
  const ciInput     = document.getElementById("ci_personal");
  const correoInput = document.getElementById("correo_personal");
  const alertaClave = document.getElementById("alerta-clave");
  const alertaPersonal = document.getElementById("alerta-personal");
  const btnSubmit   = document.getElementById("btn-guardar-usuario");
  const SERVERURL   = "<?php echo SERVERURL; ?>";
  const STAFF_ID_ENC = "<?= $idEncEdit ?>";

  // ==============================================
  // ESTADO DE VALIDACIONES
  // En update: claveValida arranca en true (claves vacías = OK)
  // cargoValido arranca en true (ya viene seleccionado)
  // duplicado arranca en false hasta validar
  // ==============================================
  let estadoValidaciones = {
    claveValida: true,
    cargoValido: true,
    duplicado: false
  };

  // ==============================================
  // BOTÓN
  // ==============================================
  function verificarEstadoBoton() {
    const todoOk = Object.values(estadoValidaciones).every(v => v === true);
    btnSubmit.disabled = !todoOk;
    btnSubmit.classList.toggle("btn-disabled", !todoOk);
    btnSubmit.style.opacity = todoOk ? "1" : "0.5";
    btnSubmit.style.cursor = todoOk ? "pointer" : "not-allowed";
  }

  // ==============================================
  // VALIDACIÓN CLAVES (opcional en update)
  // ==============================================
  function validarClaves() {
    const c1 = clave1Input.value.trim();
    const c2 = clave2Input.value.trim();

    // Ambas vacías → OK (no se cambia la clave)
    if (c1 === "" && c2 === "") {
      estadoValidaciones.claveValida = true;
      alertaClave.classList.remove("text-danger");
      alertaClave.classList.add("text-success");
      alertaClave.innerHTML = `<i class="fas fa-check-circle"></i> Deje las claves vacías para no cambiar, o ingrese al menos 7 caracteres y confirme`;
      clave1Input.classList.remove("has-error");
      clave2Input.classList.remove("has-error");
      verificarEstadoBoton();
      return;
    }

    // Una llena y otra no, o no coinciden, o son cortas → error
    if (c1.length < 7 || c2.length < 7 || c1 !== c2) {
      estadoValidaciones.claveValida = false;
      alertaClave.classList.remove("text-success");
      alertaClave.classList.add("text-danger");
      alertaClave.innerHTML = `<i class="fas fa-times-circle"></i> Las claves deben tener mínimo 7 caracteres y coincidir`;
      clave1Input.classList.add("has-error");
      clave2Input.classList.add("has-error");
    } else {
      estadoValidaciones.claveValida = true;
      alertaClave.classList.remove("text-danger");
      alertaClave.classList.add("text-success");
      alertaClave.innerHTML = `<i class="fas fa-check-circle"></i> Las claves coinciden, se actualizará la contraseña`;
      clave1Input.classList.remove("has-error");
      clave2Input.classList.remove("has-error");
    }
    verificarEstadoBoton();
  }

  // ==============================================
  // VALIDACIÓN CARGO
  // ==============================================
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

  // ==============================================
  // VALIDACIÓN DUPLICADOS (AJAX, excluye el propio ID)
  // ==============================================
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

    timeoutDuplicados = setTimeout(() => {
      // ✅ Se envía &id= para excluir el registro actual
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

  // ==============================================
  // EVENTOS
  // ==============================================
  clave1Input.addEventListener("input", validarClaves);
  clave2Input.addEventListener("input", validarClaves);
  cargoSelect.addEventListener("change", validarCargo);
  ciInput.addEventListener("input", validarDuplicados);
  correoInput.addEventListener("input", validarDuplicados);

  // ==============================================
  // INICIALIZAR
  // ==============================================
  validarClaves();
  validarCargo();
  // Validar duplicados con los datos pre-llenados (excluyendo este ID)
  validarDuplicados();
  verificarEstadoBoton();
});
</script>