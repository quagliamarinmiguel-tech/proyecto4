<?php
if($_SESSION['cargo_spm']!=1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/usuarioControlador3.php";
$ins_usuario = new usuarioControlador3();

$id_usuario = isset($_GET['usuario_id_up']) ? $lc->decryption($_GET['usuario_id_up']) : 0;
$id_usuario = (int) $id_usuario;

$datos = $ins_usuario->datos_usuario_personal_controlador($id_usuario);
if ($datos->rowCount() !== 1) {
    echo '<div class="alert alert-danger text-center">No se encontró el usuario/personal.</div>';
    exit();
}

$row = $datos->fetch();
?>


    <style>
.estado-usuario-box {
  width: 40%;
  background-color: #f0f8ff;
  border: 1px solid #ccc;
  border-radius: 8px;
  padding: 12px 20px;
  display: flex;
  justify-content: center;
  align-items: center;
}

.estado-usuario-contenido {
  display: flex;
  align-items: center;
  width: 80%;
  justify-content: flex-start;
}
</style>


<div class="full-box page-header">
  <h3 class="text-left"><i class="fas fa-user-edit fa-fw"></i> &nbsp; ACTUALIZAR USUARIO / PERSONAL</h3>
</div>


<div class="full-box page-header">

    <p class="text-justify">
        Aquí puedes actualizar un usuario de un miembro del personal.
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
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-upt-per"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; ACTUALIZAR USUARIO/PERSONAL</a>       
        </li>           
        
    </ul>   
</div>


<div class="container-fluid">
  <form id="form-usuario-update" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax2.php" method="POST" data-form="update" autocomplete="off">
    <input type="hidden" name="usuario_per_id_up" value="<?= $lc->encryption($row['ID_usuario']) ?>">

    <fieldset>
      <legend><i class="far fa-id-card"></i> &nbsp; Datos de acceso</legend>
      <div class="text-danger fw-bold" id="alerta-usuario-update"></div>
      <br><br>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nombre de usuario</label>
          <input type="text" class="form-control" name="Nombre_u" id="Nombre_u" maxlength="100" pattern="[^ ].*" required value="<?= $row['Nombre'] ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Contraseña</label>
          <input type="password" class="form-control" name="clave1" id="clave1" maxlength="100" minlength="7" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." value="<?= mainModel::decryption($row['Password']) ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Confirmar contraseña</label>
          <input type="password" class="form-control" name="clave2" id="clave2" maxlength="100" minlength="7" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." value="<?= mainModel::decryption($row['Password']) ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Cargo</label>
          <select name="cargo" id="cargo" class="form-select" required>
            <option value="" disabled> - Seleccione - </option>
            <option value="1" <?= $row['cargo'] == 1 ? 'selected' : '' ?>>Administrador</option>
            <option value="2" <?= $row['cargo'] == 2 ? 'selected' : '' ?>>Control de estudio</option>
            <option value="3" <?= $row['cargo'] == 3 ? 'selected' : '' ?>>Administración de pagos</option>
            <option value="4" <?= $row['cargo'] == 4 ? 'selected' : '' ?>>Supervisor de cursos</option>
          </select>
        </div>


      </div>
        

                    
                     <!--div class="estado-usuario-box mb-3">
  <div class="estado-usuario-contenido">
      <input type="hidden" name="estado_usuario" value="0">
    <input class="form-check-input me-3" type="checkbox" name="estado_usuario" id="estado_usuario" value="1" <?= $row['estado_usuario'] == 1 ? "checked" : "" ?>>
    <label class="form-label fw-bold text-primary mb-0" for="estado_usuario">Usuario activo</label>
  </div>
</div-->
                    
    
        
        <div class="form-check mb-3">
    <input class="form-check-input" type="checkbox" name="estado_usuario" id="estado_usuario" value="1" <?= $row['estado_usuario'] == 1 ? "checked" : "" ?>>
    <label class="form-check-label fw-bold text-primary" for="estado_usuario">
        Usuario activo
    </label>
</div>
    
        
        
                    
<!--input class="form-check-input me-3" type="checkbox" name="estado_usuario" id="estado_usuario" value="1" <= row['estado_usuario'] == 1 ? "checked" : "" ?>-->  
                    
    </fieldset>

    <fieldset>
      <legend><i class="fas fa-user-tag"></i> &nbsp; Datos personales</legend>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Cédula</label>
          <input type="text" class="form-control" name="ci_personal" id="ci_personal" maxlength="9" pattern="[1-9]{1}[0-9]{6,7}" placeholder="ej:12345678"  required value="<?= $row['ci_personal'] ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Nombre</label>
          <input type="text" class="form-control" name="nombre_personal" id="nombre_personal" maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}" required value="<?= $row['nombre_personal'] ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Apellido</label>
          <input type="text" class="form-control" name="apellido_personal" id="apellido_personal"maxlength="30" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,29}" required value="<?= $row['apellido_personal'] ?>">
        </div>

        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Correo electrónico</label>
          <input type="email" class="form-control" name="correo_personal" id="correo_personal" maxlength="60" required value="<?= $row['correo_personal'] ?>">
        </div>
      </div>
    </fieldset>

    <p class="text-center">
      <button type="submit" class="btn btn-success btn-sm" id="btn-actualizar-usuario">
        <i class="fas fa-sync-alt"></i> ACTUALIZAR
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
  const alerta = document.getElementById("alerta-usuario-update");
  const btnSubmit = document.getElementById("btn-actualizar-usuario");
  const idUsuario = "<?= $lc->encryption($row['ID_usuario']) ?>";
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

    if (clave1.length < 7 || clave2.length < 7) {
      mostrarError("Las contraseñas deben tener al menos 7 caracteres.");
      return;
    }

    fetch(`${SERVERURL}ajax/usuarioAjax2.php?accion=validar_usuario_personal_update&id=${encodeURIComponent(idUsuario)}&nombre=${encodeURIComponent(nombre)}&ci=${encodeURIComponent(ci)}&correo=${encodeURIComponent(correo)}`)
      .then(res => res.text())
      .then(texto => {
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
          mostrarError("La respuesta del servidor no es válida.");
        }
      })
      .catch(() => {
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


<!--

function validarUsuarioPersonal() {
  const nombre = nombreInput.value.trim();
  const clave1 = clave1Input.value.trim();
  const clave2 = clave2Input.value.trim();
  const cargo = parseInt(cargoSelect.value);
  const ci = ciInput.value.trim();
  const correo = correoInput.value.trim();

  // Validación básica antes del fetch
  if (nombre === "" || clave1 === "" || clave2 === "" || isNaN(cargo) || ci === "" || correo === "") {
    mostrarError("Complete todos los campos obligatorios.");
    return;
  }

  if (clave1 !== clave2) {
    mostrarError("Las contraseñas no coinciden.");
    return;
  }

  if (clave1.length < 7 || clave2.length < 7) {
    mostrarError("Las contraseñas deben tener al menos 7 caracteres.");
    return;
  }

  // Validación remota
  fetch(`${SERVERURL}ajax/usuarioAjax2.php?accion=validar_usuario_personal_update&id=${encodeURIComponent(idUsuario)}&nombre=${encodeURIComponent(nombre)}&ci=${encodeURIComponent(ci)}&correo=${encodeURIComponent(correo)}`)
    .then(res => res.text())
    .then(texto => {
      try {
        const data = JSON.parse(texto);
        if (data && typeof data === "object" && "valido" in data) {
          if (!data.valido) {
            mostrarError(data.mensaje || "Hay un error en los datos.");
          } else {
            limpiarError(); // ✅ solo aquí se activa el botón
          }
        } else {
          mostrarError("Respuesta inesperada del servidor.");
        }
      } catch (e) {
        mostrarError("La respuesta del servidor no es válida.");
      }
    })
    .catch(() => {
      mostrarError("Error de conexión con el servidor.");
    });
}
-->
