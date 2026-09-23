<?php  
if($_SESSION['cargo_spm']!=1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/usuarioControlador3.php";
$ins_usuario = new usuarioControlador3();

$id_usuario = isset($_GET['usuario_id_up']) ? $lc->decryption($_GET['usuario_id_up']) : 0;
$id_usuario = (int) $id_usuario;

$datos = $ins_usuario->datos_usuario_estudiante_controlador($id_usuario);
if ($datos->rowCount() !== 1) {
    echo '<div class="alert alert-danger text-center">No se encontró la cuenta del estudiante.</div>';
    exit();
}

$row = $datos->fetch();
?>

<div class="full-box page-header">
  <h3 class="text-left"><i class="fas fa-user-edit fa-fw"></i> &nbsp; ACTUALIZAR CUENTA DE ESTUDIANTE</h3>
</div>


<div class="full-box page-header">

    <p class="text-justify">
        Aquí puedes actualizar un usuario de un estudiante registrado.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user4-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/PERSONAL</a>
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
     

        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-upt-est"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; ACTUALIZAR USUARIO/ESTUDIANTE</a>       
        </li>   
        
    </ul>   
</div>










<div class="container-fluid">
  <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax2.php" method="POST" data-form="update" autocomplete="off">
    <input type="hidden" name="usuario_est_id_up" value="<?= $lc->encryption($row['ID_usuario']) ?>">

    <fieldset>
      <legend><i class="far fa-id-card"></i> &nbsp; Información del estudiante</legend>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label fw-bold text-primary">ID Usuario</label>
          <p class="form-control-plaintext"><?= $row['ID_usuario'] ?></p>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label fw-bold text-primary">ID Estudiante</label>
          <p class="form-control-plaintext"><?= $row['id_estudiante'] ?></p>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label fw-bold text-primary">Fecha de creación</label>
          <p class="form-control-plaintext"><?= date("d/m/Y H:i", strtotime($row['fecha_creacion'])) ?></p>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label fw-bold text-primary">Nombre</label>
          <p class="form-control-plaintext"><?= $row['nombre'] ?></p>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label fw-bold text-primary">Apellido</label>
          <p class="form-control-plaintext"><?= $row['apellido'] ?></p>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label fw-bold text-primary">Cédula</label>
          <p class="form-control-plaintext"><?= $row['ci_estudiante'] ?></p>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend><i class="fas fa-toggle-on"></i> &nbsp; Estado de la cuenta</legend>
      <div class="form-check form-switch mb-3">
        <input class="form-check-input" type="checkbox" name="estado_usuario" id="estado_usuario" <?= $row['estado_usuario'] == 1 ? "checked" : "" ?>>
        <label class="form-check-label fw-bold text-primary" for="estado_usuario">Activado</label>
      </div>
    </fieldset>

    <fieldset>
      <legend><i class="fas fa-key"></i> &nbsp; Cambiar contraseña</legend>
                    <p class="text-justify">  Mínimo 7 caracteres    </p>

      <div class="row">
          
          
          
          
          
          
          <div class="col-md-6 mb-3">
  <label class="form-label fw-bold text-primary">Nueva contraseña</label>
  <input type="password" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." minlength="7" class="form-control" name="clave1" id="clave1" maxlength="100" value="<?= mainModel::decryption($row['Password']) ?>">
</div>
<div class="col-md-6 mb-3">
  <label class="form-label fw-bold text-primary">Confirmar contraseña</label>
  <input type="password" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." minlength="7" class="form-control" name="clave2" id="clave2" maxlength="100" value="<?= mainModel::decryption($row['Password']) ?>">
</div>
          
          
          
          
          
        <!--div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nueva contraseña</label>
          <input type="password" class="form-control" name="clave1" id="clave1" maxlength="100" value="<= $row['Password'] ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Confirmar contraseña</label>
          <input type="password" class="form-control" name="clave2" id="clave2" maxlength="100" value="<= $row['Password'] ?>">
        </div-->
      </div>
      <div id="alerta-clave" class="text-danger fw-bold mt-2"></div>
    </fieldset>

      
      
      
      

      
      
      
    <p class="text-center">
      <button type="submit" class="btn btn-success btn-sm" id="btn-actualizar-usuario-estudiante">
        <i class="fas fa-sync-alt"></i> ACTUALIZAR
      </button>
    </p>
  </form>
</div>






<script>
document.addEventListener("DOMContentLoaded", function() {
  const clave1 = document.getElementById("clave1");
  const clave2 = document.getElementById("clave2");
  const btnActualizar = document.getElementById("btn-actualizar-usuario-estudiante");
  const alerta = document.getElementById("alerta-clave");

  const originalClave = clave1.value.trim(); // valor precargado desencriptado

  function validarClaves() {
    const val1 = clave1.value.trim();
    const val2 = clave2.value.trim();

    // Si no hay cambios, permitir envío
    if (val1 === originalClave && val2 === originalClave) {
      alerta.textContent = "";
      btnActualizar.disabled = false;
      return;
    }

    // Si hay cambios, validar
    if (val1.length >= 7 && val2.length >= 7) {
      if (val1 !== val2) {
        alerta.textContent = "Las contraseñas no coinciden.";
        btnActualizar.disabled = true;
      } else {
        alerta.textContent = "";
        btnActualizar.disabled = false;
      }
    } else {
      alerta.textContent = "Las contraseñas deben tener al menos 7 caracteres.";
      btnActualizar.disabled = true;
    }
  }

  clave1.addEventListener("input", validarClaves);
  clave2.addEventListener("input", validarClaves);
});
</script>





<!--script>
$(document).ready(function() {
  const clave1 = document.getElementById("clave1");
  const clave2 = document.getElementById("clave2");
  const btnActualizar = document.getElementById("btn-actualizar-usuario-estudiante");
  const alerta = document.getElementById("alerta-clave");

  function validarClaves() {
    const val1 = clave1.value.trim();
    const val2 = clave2.value.trim();

    if (val1.length === 0 && val2.length === 0) {
      alerta.textContent = "";
      btnActualizar.disabled = false;
      return;
    }

    if (val1.length >= 7 && val2.length >= 7) {
      if (val1 !== val2) {
        alerta.textContent = "Las contraseñas no coinciden.";
        btnActualizar.disabled = true;
      } else {
        alerta.textContent = "";
        btnActualizar.disabled = false;
      }
    } else {
      alerta.textContent = "Las contraseñas deben tener al menos 7 caracteres.";
      btnActualizar.disabled = true;
    }
  }

  clave1.addEventListener("input", validarClaves);
  clave2.addEventListener("input", validarClaves);
});
</script-->
