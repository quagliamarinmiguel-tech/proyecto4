<?php
if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/localControlador.php";
$ctrl = new localControlador();
$datos = $ctrl->datos_local_controlador(); // ← Devuelve el registro único con imágenes

if($datos->rowCount() != 1){
    echo '<div class="alert alert-danger text-center">No se encontró la información institucional.</div>';
    exit();
}

$local = $datos->fetch();
?>
<style>
#preview-local, #preview-mapa, #preview-satelite {
  display: inline-block;
  margin-right: 10px;
}

</style>



<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-map-marked-alt fa-fw"></i> &nbsp; ACTUALIZAR INFORMACIÓN CEPROCA</h3>
    <p class="text-justify">
        Edita los datos institucionales y reemplaza las imágenes del local, mapa y vista satelital si es necesario.
    </p>
</div>

<div class="container-fluid">

    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/localAjax.php" method="POST" data-form="update" enctype="multipart/form-data" autocomplete="off">

        <input type="hidden" name="local_id_up" value="1"> <!-- único registro -->
        <input type="hidden" name="imagen_local_fk" value="<?php echo $local['imagen_local']; ?>">
        <input type="hidden" name="imagen_local_mapa_fk" value="<?php echo $local['imagen_local_mapa']; ?>">
        <input type="hidden" name="imagen_local_satelite_fk" value="<?php echo $local['imagen_local_satelite']; ?>">

        <fieldset>
            <legend><i class="fas fa-phone"></i> &nbsp; Datos de contacto</legend>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Teléfono</label>
                    <input type="tel" class="form-control" name="telefono_contacto" maxlength="13" required
                        value="<?php echo htmlspecialchars($local['telefono_contacto']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Correo</label>
                    <input type="email" class="form-control" name="correo_contacto" maxlength="60" required
                        value="<?php echo htmlspecialchars($local['correo_contacto']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Facebook</label>
                    <input type="url" class="form-control" name="link_facebook" maxlength="100"
                        value="<?php echo htmlspecialchars($local['link_facebook']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Instagram</label>
                    <input type="url" class="form-control" name="link_instagram" maxlength="100"
                        value="<?php echo htmlspecialchars($local['link_instagram']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">Ciudad</label>
                    <input type="text" class="form-control" name="direccion_local_ciudad" maxlength="60" required
                        value="<?php echo htmlspecialchars($local['direccion_local_ciudad']); ?>">
                </div>

                <div class="col-md-6 mb-3">
                    <label class="form-label">País</label>
                    <input type="text" class="form-control" name="direccion_local_pais" maxlength="60" required
                        value="<?php echo htmlspecialchars($local['direccion_local_pais']); ?>">
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="far fa-image"></i> &nbsp; Imágenes institucionales</legend>
            <div class="row">

                <!-- Fachada -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Imagen actual del local</label><br>
                    <img src="<?php echo SERVERURL . 'vistas/' . $local['ruta_local']; ?>" class="img-thumbnail" style="max-width: 200px;">
                    <label class="form-label mt-2">Reemplazar imagen</label>
                    <input type="file" name="imagen_local" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <!-- Mapa -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Mapa actual</label><br>
                    <img src="<?php echo SERVERURL . 'vistas/' . $local['ruta_mapa']; ?>" class="img-thumbnail" style="max-width: 200px;">
                    <label class="form-label mt-2">Reemplazar imagen</label>
                    <input type="file" name="imagen_local_mapa" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                </div>

                <!-- Satélite -->
                <div class="col-md-4 mb-3">
                    <label class="form-label">Vista satelital actual</label><br>
                    <img src="<?php echo SERVERURL . 'vistas/' . $local['ruta_satelite']; ?>" class="img-thumbnail" style="max-width: 200px;">
                    <label class="form-label mt-2">Reemplazar imagen</label>
                    <input type="file" name="imagen_local_satelite" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                </div>

            </div>
        </fieldset>
<div id="preview-container" style="display: flex; gap: 15px; margin-top: 20px;">
        <div id="preview-local" style="display:none;"><img src="" style="max-width:200px;"></div>
        <div id="preview-mapa" style="display:none;"><img src="" style="max-width:200px;"></div>
        <div id="preview-satelite" style="display:none;"><img src="" style="max-width:200px;"></div>
</div>
        <br>
        <p class="text-center">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-sync-alt"></i> ACTUALIZAR DATOS
            </button>
        </p>
    </form>
</div>





<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form[data-form='update']");
    const camposImagen = [
        { inputName: "imagen_local", previewId: "preview-local" },
        { inputName: "imagen_local_mapa", previewId: "preview-mapa" },
        { inputName: "imagen_local_satelite", previewId: "preview-satelite" }
    ];

    camposImagen.forEach(campo => {
        const input = document.querySelector(`input[name="${campo.inputName}"]`);
        const previewContainer = document.getElementById(campo.previewId);
        const previewImg = previewContainer?.querySelector("img");

        if (!input) return;

        input.addEventListener("change", () => {
            const file = input.files[0];
            if (!file) {
                previewContainer && (previewContainer.style.display = "none");
                return;
            }

            const ext = file.name.split(".").pop().toLowerCase();
            if (!["jpg", "jpeg", "png", "webp"].includes(ext)) {
                input.value = "";
                previewContainer && (previewContainer.style.display = "none");
                return;
            }

            // Añadir campo oculto solo una vez
            if (!form.querySelector('input[name="imagen_cambiada"]')) {
                const hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = "imagen_cambiada";
                hidden.value = "1";
                form.appendChild(hidden);
            }

            // Mostrar preview
            if (previewImg) {
                const reader = new FileReader();
                reader.onload = () => {
                    previewImg.src = reader.result;
                    previewContainer.style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        });
    });
});
</script>






<!--script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form[data-form='update']");
    const camposImagen = [
        { inputName: "imagen_local", previewId: "preview-local" },
        { inputName: "imagen_local_mapa", previewId: "preview-mapa" },
        { inputName: "imagen_local_satelite", previewId: "preview-satelite" }
    ];

    let seCambioImagen = false;

    camposImagen.forEach(campo => {
        const input = document.querySelector(`input[name="${campo.inputName}"]`);
        const previewContainer = document.getElementById(campo.previewId);
        const previewImg = previewContainer?.querySelector("img");

        if (!input) return;

        input.addEventListener("change", () => {
            const file = input.files[0];
            if (!file) {
                previewContainer && (previewContainer.style.display = "none");
                return;
            }

            const ext = file.name.split(".").pop().toLowerCase();
            if (!["jpg", "jpeg", "png", "webp"].includes(ext)) {
                alert("Formato de imagen no permitido.");
                input.value = "";
                previewContainer && (previewContainer.style.display = "none");
                return;
            }

            seCambioImagen = true;

            // Añadir campo oculto solo una vez
            if (!form.querySelector('input[name="imagen_cambiada"]')) {
                const hidden = document.createElement("input");
                hidden.type = "hidden";
                hidden.name = "imagen_cambiada";
                hidden.value = "1";
                form.appendChild(hidden);
            }

            // Mostrar preview
            if (previewImg) {
                const reader = new FileReader();
                reader.onload = () => {
                    previewImg.src = reader.result;
                    previewContainer.style.display = "block";
                };
                reader.readAsDataURL(file);
            }
        });
    });

    // Mensaje visual si se detecta al menos una imagen nueva
    form.addEventListener("submit", () => {
        if (seCambioImagen) {
            alert("✅ Imagen actualizada. Cambios guardados.");
        }
    });
});
</script-->

