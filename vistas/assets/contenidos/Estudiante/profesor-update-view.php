<?php 
if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

// ID encriptado
$idEnc = $_GET['profesor_id_up'] ?? '';
require_once "./controladores/profesorControlador.php";
$ctrl = new profesorControlador();
$datos = $ctrl->datos_profesor_controlador("Unico", $idEnc);

if($datos->rowCount() != 1){
    echo '<div class="alert alert-danger text-center">No se encontró el profesor.</div>';
    exit();
}
$idReal = mainModel::decryption($idEnc); 
$profesor = $datos->fetch();
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
    <h3 class="text-left"><i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; ACTUALIZAR PROFESOR</h3>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PROFESORES</a>
        </li>
                <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; ACTUALIZAR PROFESOR</a>
        </li>
    </ul>   
</div>



<div class="container-fluid">
    <form id="form-update-profesor" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/profesorAjax.php" method="POST" data-form="update" enctype="multipart/form-data" autocomplete="off">

        <input type="hidden" name="profesor_id_up" value="<?php echo $idEnc; ?>">
        <input type="hidden" name="estado_profesor" value="<?php echo $profesor['estado_profesor']; ?>">
        <!--input type="hidden" name="profe_mostrar" value="<?php echo $profesor['profe_mostrar']; ?>"-->
        <input type="hidden" name="imagen_fk" value="<?php echo $profesor['imagen_profesor_fk']; ?>">
        <fieldset>
            <legend><i class="far fa-id-card"></i> &nbsp; Datos personales</legend>
            <div class="text-danger fw-bold" id="alerta-profesor" style="display:none; font-size: 1rem; margin-top: 0.5rem;"></div>

            <div class="row">


                <div class="col-md-6 mb-3">
                    <!--label class="form-label">Cédula</label-->
                    <div class="input-group">
                        <!-- Cédula completa -->
                                        <!--div class="col-md-6 mb-3">
                                            <label class="form-label">Cédula</label>
                                        <input type="text" id="ci_input" name="ci_reg" class="form-control" maxlength="10" pattern="[VEve]{1}[0-9]{5,9}" 
                                        placeholder="V12345678" required value="<?php echo htmlspecialchars($profesor['ci_profesor']); ?>">
                                            <small class="text-muted">Debe comenzar con V o E seguido de hasta 9 dígitos.</small>
                                        </div-->


                        <!-- Cédula completa -->
                        <div class="col-md-6 mb-3">
                            <label class="form-label fw-bold text-primary">Cédula</label>
                            <input type="text" id="ci_input" name="ci_reg" class="form-control" maxlength="10" pattern="[VE]{1}[0-9]{5,8}" placeholder="V12345678" value="<?php echo htmlspecialchars($profesor['ci_profesor']); ?>" required>
                            <small class="text-muted">Debe comenzar con V o E seguido de hasta 8 dígitos.</small>
                        </div>

                    </div>
                </div>


                <!-- Nombre -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Nombre</label>
                    <input type="text" class="form-control" name="nombre_reg" maxlength="20" required
                        value="<?php echo htmlspecialchars($profesor['nombre_profesor']); ?>">
                </div>

                <!-- Apellido -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Apellido</label>
                    <input type="text" class="form-control" name="apellido_reg" maxlength="20" required
                        value="<?php echo htmlspecialchars($profesor['apellido_profesor']); ?>">
                </div>

                <!-- Correo -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Correo electrónico</label>
                    <input type="text" class="form-control" name="correo_reg" maxlength="60" required
                        value="<?php echo htmlspecialchars($profesor['correo']); ?>">
                </div>

                <!-- Teléfono -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Teléfono</label>
                    <input type="tel" class="form-control" name="telefono_reg" maxlength="12"
                        pattern="[0-9]{7,12}" value="<?php echo htmlspecialchars($profesor['telefono_profesor']); ?>">
                </div>

                <!-- Descripción -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Descripción profesional</label>
                    <textarea name="descripcion_reg" class="form-control" rows="3" maxlength="120"><?php echo htmlspecialchars($profesor['descripcion']); ?></textarea>
                </div>

                <!-- Imagen actual -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Imagen actual</label><br>
                    <img src="<?php echo SERVERURL.'vistas/'.$profesor['ruta']; ?>" alt="Imagen actual" class="img-thumbnail" style="max-width:200px;">
                </div>

                <!-- Imagen nueva -->
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-bold text-primary">Reemplazar imagen (opcional)</label>
                    <input type="file" id="archivo_imagen" name="archivo_imagen" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                    <div class="mt-2" id="preview-imagen" style="display:none;">
                        <img src="" alt="Preview" style="max-width:200px;" class="img-thumbnail">
                    </div>
                    <small class="text-muted">Si no seleccionas una nueva imagen, se conservará la actual.</small>
                </div>

                <!-- Mostrar profesor (1 = sí, 2 = no) -->
                <!--div class="col-md-6 mb-3">
                    <label class="form-label">¿Mostrar al profesor en la plataforma?</label>
                    <select name="profe_mostrar" class="form-select" required>
                        <option value="1" <php echo ($profesor['profe_mostrar'] == 1) ? 'selected' : ''; ?>>Sí</option>
                        <option value="2" <php echo ($profesor['profe_mostrar'] == 2) ? 'selected' : ''; ?>>No</option>
                    </select>
                    <small class="text-muted">Esta opción controla si el perfil del profesor está visible públicamente.</small>
                </div-->

                <div class="col-md-6 mb-4">
                <label for="profe_mostrar" class="form-label fw-bold text-primary mb-2 d-flex align-items-center">
                    ¿Mostrar al profesor en la plataforma?   .
                    <button type="button" class="btn btn-sm ver-ayuda ms-2"
                            data-src="<?php echo SERVERURL; ?>vistas/assets/img/imagenprofecontexto.webp"
                            title="Ver explicación"
                            style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
                    <i class="fas fa-question"></i>
                    </button>
                </label>

                <select name="profe_mostrar" class="form-select" required>
                    <option value="1" <?php echo ($profesor['profe_mostrar'] == 1) ? 'selected' : ''; ?>>Sí</option>
                    <option value="2" <?php echo ($profesor['profe_mostrar'] == 2) ? 'selected' : ''; ?>>No</option>
                </select>

                <div class="form-text text-muted mt-1">
                    Esta opción controla si el perfil del profesor está visible públicamente.
                </div>
                </div>


            </div>
        </fieldset>

        <br>
        <p class="text-center">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> ACTUALIZAR</button>
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


<!--



    // 🧠 Validar y transformar la cédula antes de enviar
    form.addEventListener("submit", (e) => {
        const ciVal = ciInput.value.trim().toUpperCase();
        const regex = /^[VE]{1}[0-9]{5,9}$/;

        if (!regex.test(ciVal)) {
            e.preventDefault();
            alert("La cédula debe comenzar con V o E seguida de 5 a 9 dígitos.");
            return;
        }

        ciInput.value = ciVal;
    });

    // 🔠 Autotransformar la cédula a mayúscula + dígitos válidos
    ciInput.addEventListener("input", () => {
        if (ciInput.value.length >= 1) {
            const prefix = ciInput.value.charAt(0).toUpperCase();
            const digits = ciInput.value.slice(1).replace(/[^0-9]/g, '');
            ciInput.value = prefix + digits;
        }
    });

-->


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
    const form = document.getElementById("form-update-profesor");
    const ciInput = document.getElementById("ci_input");
    const inputImagen = document.getElementById("archivo_imagen");
    const preview = document.getElementById("preview-imagen");
    const previewImg = preview.querySelector("img");

    // 🖼️ Preview de imagen y detección de cambio
    inputImagen.addEventListener("change", () => {
        const file = inputImagen.files[0];
        if (!file) {
            preview.style.display = "none";
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        if (!["jpg", "jpeg", "png", "webp"].includes(ext)) {
            alert("Formato de imagen no permitido.");
            inputImagen.value = "";
            preview.style.display = "none";
            return;
        }

        // 📌 Crear campo oculto imagen_cambiada = 1 si aún no existe
        if (!form.querySelector('input[name="imagen_cambiada"]')) {
            const campoCambio = document.createElement("input");
            campoCambio.type = "hidden";
            campoCambio.name = "imagen_cambiada";
            campoCambio.value = "1";
            form.appendChild(campoCambio);
        }

        const reader = new FileReader();
        reader.onload = () => {
            previewImg.src = reader.result;
            preview.style.display = "block";
        };
        reader.readAsDataURL(file);
    });
});
</script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const ciInput = document.getElementById("ci_input");
  const correoInput = document.querySelector("input[name='correo_reg']");
  const telefonoInput = document.querySelector("input[name='telefono_reg']");
  const alerta = document.getElementById("alerta-profesor");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";
  //const profesorId = "<hp echo $idEnc; >";
  const profesorId = "<?php echo $idReal; ?>";

console.log(profesorId);
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

  function validarProfesorEdicion() {
    const ci = ciInput.value.trim();
    const correo = correoInput.value.trim();
    const telefono = telefonoInput.value.trim();

    if (ci === "" || correo === "" || telefono === "") {
      mostrarError("Complete todos los campos obligatorios.");
      return;
    }

    fetch(`${SERVERURL}ajax/profesorAjax.php?accion=validar_profesor_edicion&id=${encodeURIComponent(profesorId)}&ci=${encodeURIComponent(ci)}&telefono=${encodeURIComponent(telefono)}&correo=${encodeURIComponent(correo)}`)
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

  ciInput.addEventListener("input", validarProfesorEdicion);
  correoInput.addEventListener("input", validarProfesorEdicion);
  telefonoInput.addEventListener("input", validarProfesorEdicion);
});
</script>