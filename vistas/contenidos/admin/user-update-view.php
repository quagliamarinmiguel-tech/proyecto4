<?php
    $state = $_SESSION['profile_status'] ?? 0;
	$cargo = $_SESSION['cargo_spm'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1]) || $state != 1) {
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
    $nombre     = htmlspecialchars(trim(($staff['name'] ?? '') . ' ' . ($staff['last_name'] ?? '')), ENT_QUOTES, 'UTF-8');
    $correo     = htmlspecialchars($staff['email'] ?? '', ENT_QUOTES, 'UTF-8');
    $roleNumber = (int) ($staff['role_number'] ?? 0);
    $idEncEdit  = mainModel::encryption($staffId);
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
        Modifique el cargo del usuario o reenvíe el correo de confirmación de cuenta.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIOS</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-update"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; ACTUALIZAR USUARIO</a>
        </li>
    </ul>
</div>

<!-- Info del usuario que se está editando (solo lectura) -->
<div class="container-fluid mb-4">
    <div class="alert alert-info" role="alert" style="background-color:inherit;">
        <strong>Usuario:</strong> <?= $nombre !== '' ? $nombre : '<em>Sin nombre</em>' ?> &nbsp; | &nbsp;
        <strong>Correo:</strong> <?= $correo ?>
    </div>
</div>

<div class="container-fluid">
  <form id="form-usuario" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax.php" method="POST" data-form="update" autocomplete="off">
    <input type="hidden" name="staff_id_up"    value="<?= $idEncEdit ?>">
    <input type="hidden" name="profile_id_enc" value="<?= mainModel::encryption((int) $staff['profile_id']) ?>">
    <input type="hidden" name="staff_email"    value="<?= $correo ?>">

    <fieldset>
      <legend><i class="fas fa-user-tag fa-fw"></i> &nbsp; Cargo</legend>

      <div class="row">
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
      </div>
    </fieldset>

    <fieldset>
      <legend><i class="fas fa-envelope fa-fw"></i> &nbsp; Confirmación de cuenta</legend>

      <div class="form-check">
        <input class="form-check-input" type="checkbox" name="reenviar_confirmacion" id="reenviar_confirmacion" value="1">
        <label class="form-check-label" for="reenviar_confirmacion">
          Reenviar correo de confirmación de cuenta
        </label>
      </div>
      <small class="text-muted d-block mt-2">
        Si el usuario no ha confirmado su correo, márcalo para enviarle un nuevo enlace de confirmación.
      </small>
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
  const cargoSelect = document.getElementById("cargo");
  const btnSubmit   = document.getElementById("btn-guardar-usuario");

  function verificarEstadoBoton() {
    const valor = parseInt(cargoSelect.value);
    const valido = !isNaN(valor) && valor >= 1 && valor <= 4;
    btnSubmit.disabled = !valido;
    btnSubmit.classList.toggle("btn-disabled", !valido);
    btnSubmit.style.opacity = valido ? "1" : "0.5";
    btnSubmit.style.cursor  = valido ? "pointer" : "not-allowed";
  }

  cargoSelect.addEventListener("change", () => {
    const valor = parseInt(cargoSelect.value);
    if (isNaN(valor) || valor < 1 || valor > 4) {
      cargoSelect.classList.add("has-error");
    } else {
      cargoSelect.classList.remove("has-error");
    }
    verificarEstadoBoton();
  });

  // Inicializar
  verificarEstadoBoton();
});
</script>