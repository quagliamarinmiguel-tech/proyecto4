<?php
if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm'] != 4){
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
input[type="text"],
input[type="email"],
input[type="tel"],
textarea,
select {
  border-radius: 6px;
}
#btn-guardar-profesor {
  padding: 10px 30px;
  font-size: 0.95rem;
}
#preview-imagen img {
  border: 2px solid #007bff;
}
</style>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; NUEVO PROFESOR</h3>
</div>



<!--div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=profesor-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=profesor-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PROFESORES</a>
        </li>
    </ul>   
</div-->




<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/profesor-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a>
            <!--a href="<?php echo SERVERURL; ?>professor-new/"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a-->
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/profesor-list'; ?>"><i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; LISTA DE PROFESORES</a>
            <!--a href="<?php echo SERVERURL; ?>professor-list/"><i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; LISTA DE PROFESORES</a-->
        </li>

      </ul>
</div>




<div class="container-fluid">
    <form id="form-profesor" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/profesorAjax.php" method="POST" data-form="save" enctype="multipart/form-data" autocomplete="off">
    <!--form id="form-profesor" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/profesorAjax.php" method="POST" data-form="save" autocomplete="off"-->
        <!-- CI combinada -->
        <input type="hidden" name="ci_reg" id="ci_reg_combinada">
        <!--input type="hidden" name="imagen_fk" class="input-imagen-id" value=""-->
        <input type="hidden" name="estado_profesor" value="1">
        <!--input type="hidden" name="profe_mostrar" value="1"-->

        <fieldset>
            <legend><i class="far fa-id-card"></i> &nbsp; Datos personales</legend>
            <!--div class="text-danger fw-bold" id="alerta-profesor" style="display:none; font-size: 1rem; margin-top: 0.5rem;"></div-->
            <!--div class="text-danger small mt-2" id="alerta-profesor" style="display:none;"></div-->
            <div class="text-danger fw-bold" id="alerta-profesor" style="display:none; font-size: 1rem; margin-top: 0.5rem;"></div>
            <br><br>
            <div class="row">
                <!-- Cédula -->
                <div class="col-md-6 mb-3">
                    <!--label class="form-label">Cédula</label-->
                    <div class="input-group">
                        <!-- Cédula completa -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-primary">Cédula</label>
                          <!--pattern="[VEve]{1}[0-9]{7,8}"-->
                            <input type="text" id="ci_input" name="ci_reg" class="form-control" maxlength="10" pattern="[VE]{1}[1-9]{1}[0-9]{6,7}" placeholder="ej:V12345678" required>
                            <small class="text-muted">Debe comenzar con V o E seguido de hasta 8 dígitos.</small>
                        </div>
                        <!--input type="number" id="ci_number" class="form-control" min="1000000" max="99999999" placeholder="12345678" required-->
                    </div>
                </div>

                <!-- Nombre -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Nombre</label>
                    <input type="text" class="form-control" name="nombre_reg" maxlength="20" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,19}"  required>
                </div>
<!--pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}" -->
                <!-- Apellido -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Apellido</label>
                    <input type="text" class="form-control" name="apellido_reg" maxlength="20" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,19}" required>
                </div>

                <!-- Correo -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Correo electrónico</label>
                    <input type="text" 
                           class="form-control"        
                           name="correo_reg" maxlength="60"  title="Solo se permiten correos personales como Gmail, Yahoo, Outlook, etc."
                           required>
                </div>

                <!-- Teléfono -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Teléfono</label>
                    <input type="tel" class="form-control" name="telefono_reg" maxlength="12" pattern="[0-9]{7,12}">
                </div>

                <!-- Descripción -->
                <div class="col-md-6 mb-3" id="descripcion_profesor">
                    <label class="form-label fw-bold text-primary">Descripción profesional</label>
                    <textarea name="descripcion_reg" class="form-control" rows="3" maxlength="120"></textarea>
                </div>

                <div class="col-md-6 mb-4">
                <label for="profe_mostrar" class="form-label fw-bold text-primary mb-2">
                    Mostrar al profesor en la página publicitaria?

    <button type="button" class="btn btn-sm ver-ayuda" 
            data-src="<?php echo SERVERURL; ?>vistas/assets/img/imagenprofecontexto.webp" 
            title="Ver explicación"
            style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
    <i class="fas fa-question"></i>
    </button>

                </label>
                <select name="profe_mostrar" id="profe_mostrar" class="form-select" required>
                    <option value="" disabled selected> - Seleccione - </option>
                    <option value="1">Sí</option>
                    <option value="0">No</option>
                </select>
                <div class="form-text text-muted mt-1">
                    Esto determinará si los datos aquí registrados podrán ser vistos por los visitantes del portal publicitario.
                </div>
                </div>

                <!-- Imagen -->
                <div class="col-md-6 mb-3" id="imagen_profesor" >
                    <label class="form-label">Imagen del profesor (.png, .jpg, .webp)</label>
                    <input type="file" id="archivo_imagen" name="archivo_imagen" class="form-control-file" accept=".jpg,.jpeg,.png,.webp" >
                    <div class="mt-2" id="preview-imagen" style="display:none;">
                        <img src="" alt="Preview" style="max-width:200px;" class="img-thumbnail">
                    </div>
                </div>
            </div>
        </fieldset>

        <br>
        <!--div class="text-danger small mt-2" id="alerta-profesor" style="display:none;"></div-->

        <br>
        <p class="text-center">
            <button type="submit" class="btn btn-info btn-sm" id="btn-guardar-profesor" >
                <i class="far fa-save"></i> GUARDAR
            </button>
        </p>
    </form>
</div>



<div id="modalAyuda" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
  <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff; padding:20px; border-radius:8px;">
    <img id="imgAyuda" src="" class="img-thumbnail" style="max-width:100%; height:auto; display:block; margin:auto; border:3px solid white;">
    <button onclick="document.getElementById('modalAyuda').style.display='none'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>







<!--div class="text-danger fw-bold" id="alerta-profesor" style="display:none; font-size: 1rem; margin-top: 0.5rem;"></div-->


<script>
document.addEventListener("DOMContentLoaded", () => {
  const selectMostrar = document.getElementById("profe_mostrar");
  const descripcionDiv = document.getElementById("descripcion_profesor");
  const imagenDiv = document.getElementById("imagen_profesor");

  function actualizarVisibilidad() {
    const valor = selectMostrar.value;
    const mostrar = valor === "1";

    descripcionDiv.style.display = mostrar ? "block" : "none";
    imagenDiv.style.display = mostrar ? "block" : "none";
  }

  selectMostrar.addEventListener("change", actualizarVisibilidad);

  // Ejecutar al cargar por si ya hay un valor seleccionado
  actualizarVisibilidad();
});
</script>



<script>
document.addEventListener("DOMContentLoaded", () => {
  document.querySelectorAll(".ver-ayuda").forEach(btn => {
    btn.addEventListener("click", () => {
      const modal = document.getElementById("modalAyuda");
      modal.querySelector("#imgAyuda").src = btn.dataset.src;
      modal.style.display = "block";
    });
  });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
  const ciInput = document.getElementById("ci_input");
  const correoInput = document.querySelector("input[name='correo_reg']");
  const telefonoInput = document.querySelector("input[name='telefono_reg']");
  const alerta = document.getElementById("alerta-profesor");
  const btnSubmit = document.getElementById("btn-guardar-profesor");
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

  function validarProfesor() {
    const ci = ciInput.value.trim();
    const correo = correoInput.value.trim();
    const telefono = telefonoInput.value.trim();

    if (ci === "" || correo === "" || telefono === "") {
      mostrarError("Complete todos los campos obligatorios.");
      return;
    }


    fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_profesor&ci=${encodeURIComponent(ci)}&telefono=${encodeURIComponent(telefono)}&correo=${encodeURIComponent(correo)}`)
      .then(res => res.json())
        .then(data => {
        if (data && typeof data === "object" && "valido" in data) {
            if (!data.valido) {
            mostrarError(data.mensaje || "Hay un error en los datos.");
            } else {
            limpiarError();
            }
        } else {
            mostrarError("Respuesta inesperada del servidor.");
        }
        })

      .catch(err => {
        console.error("Error al validar profesor:", err);
        mostrarError("Error de conexión con el servidor.");
      });
  }

  ciInput.addEventListener("input", validarProfesor);
  correoInput.addEventListener("input", validarProfesor);
  telefonoInput.addEventListener("input", validarProfesor);
});
</script>



