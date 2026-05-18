<?php
if($_SESSION['cargo_spm']!=1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}
?>

<!-- Select2 y jQuery -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<div class="full-box page-header">
  <h3 class="text-left"><i class="fas fa-user-graduate fa-fw"></i> &nbsp; CREAR CUENTA PARA ESTUDIANTE</h3>
</div>





<div class="full-box page-header">

    <p class="text-justify">
        Aquí puede crear un usuario que usará un estudiante ya registrado en el sistema pero no tiene una cuenta.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user4-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/PERSONAL</a>
        </li>

        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user3-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/ESTUDIANTE</a>
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
  <form id="form-usuario-estudiante" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/usuarioAjax2.php" method="POST" data-form="save" autocomplete="off">
    <fieldset>
      <legend><i class="far fa-id-card"></i> &nbsp; Selección de estudiante</legend>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Buscar estudiante</label>
          <select name="id_estudiante" id="id_estudiante" class="form-control select2" required></select>
          <small class="text-muted">Escriba nombre, apellido o cédula para buscar.</small>
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend><i class="fas fa-key"></i> &nbsp; Datos de acceso</legend>
            <p class="text-justify">  Mínimo 7 caracteres    </p>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Contraseña</label>
          <input type="password" class="form-control" minlength="7" name="clave1" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." id="clave1" maxlength="100" required>
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label fw-bold text-primary">Confirmar contraseña</label>
          <input type="password" class="form-control" minlength="7" name="clave2" required pattern="[^ ].*" title="El nombre no debe comenzar con espacio." id="clave2" maxlength="100" required>
        </div>
      </div>
    </fieldset>

    <p class="text-center">
      <button type="submit" class="btn btn-info btn-sm" id="btn-guardar-usuario-estudiante">
        <i class="far fa-save"></i> GUARDAR
      </button>
    </p>
  </form>
</div>


<script>
$(document).ready(function() {
  // Inicializar Select2
  $('#id_estudiante').select2({
    placeholder: "Buscar estudiante...",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/usuarioAjax2.php?accion=buscar_estudiante",
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return { q: params.term };
      },
      processResults: function (data) {
        return {
          results: data.map(est => ({
            id: est.id_estudiante,
            text: `${est.nombre} ${est.apellido} - ${est.ci_estudiante}`
          }))
        };
      },
      cache: true
    }
  });

  // Validación de contraseñas
  const clave1 = document.getElementById("clave1");
  const clave2 = document.getElementById("clave2");
  const btnGuardar = document.getElementById("btn-guardar-usuario-estudiante");

  const alerta = document.createElement("div");
  alerta.className = "text-danger fw-bold mt-2";
  clave2.parentNode.appendChild(alerta);

  function validarClaves() {
    const val1 = clave1.value.trim();
    const val2 = clave2.value.trim();

    if (val1.length >= 7 && val2.length >= 7) {
      if (val1 !== val2) {
        alerta.textContent = "Las contraseñas no coinciden.";
        btnGuardar.disabled = true;
      } else {
        alerta.textContent = "";
        btnGuardar.disabled = false;
      }
    } else {
      alerta.textContent = "Las contraseñas deben tener al menos 7 caracteres.";
      btnGuardar.disabled = true;
    }
  }

  clave1.addEventListener("input", validarClaves);
  clave2.addEventListener("input", validarClaves);
});
</script>

<!--script>
$(document).ready(function() {
  $('#id_estudiante').select2({
    placeholder: "Buscar estudiante...",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/usuarioAjax2.php?accion=buscar_estudiante",
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return { q: params.term };
      },
      processResults: function (data) {
        return {
          results: data.map(est => ({
            id: est.id_estudiante,
            text: `${est.nombre} ${est.apellido} - ${est.ci_estudiante}`
          }))
        };
      },
      cache: true
    }
  });
});
</script-->

<!--script>
$(document).ready(function() {
  $('#id_estudiante').select2({
    placeholder: "Buscar estudiante...",
    allowClear: true,
    ajax: {
        url: "<php echo SERVERURL; >ajax/usuarioAjax2.php ? accion=buscar_estudiante"
      dataType: 'json',
      delay: 250,
      data: function (params) {
        return { q: params.term };
      },
      processResults: function (data) {
        return {
          results: data.map(est => ({
            id: est.id_estudiante,
            text: `${est.nombre} ${est.apellido} - ${est.ci_estudiante}`
          }))
        };
      },
      cache: true
    },
    minimumInputLength: 2
  });
});
</script-->