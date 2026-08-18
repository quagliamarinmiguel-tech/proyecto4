<?php
if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/localControlador.php";
$ctrl = new localControlador();
$resImg = $ctrl->obtener_imagenes_local_controlador();
$imagenesMap = [];
while($img = $resImg->fetch(PDO::FETCH_ASSOC)){
    $imagenesMap[$img['nom_esp']] = $img['ruta'];
}

function getMappedImage($nom_esp, $array) {
    return isset($array[$nom_esp]) ? SERVERURL . "vistas/" . $array[$nom_esp] : SERVERURL . "vistas/assets/img/default.png";
}
?>










<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <fieldset class="border p-4" style="border-width:2px;">
                <legend class="w-auto px-2">
                    <h3 class="text-center mb-4"><i class="fas fa-image"></i> Administrar Imágenes de Sliders</h3>
                    <h4 class="text-center mb-4"> Deben ser formato png, jpg o webp</h4>
                </legend>

                <form class="FormSubirImagenAjax" action="<?php echo SERVERURL; ?>ajax/localAjax.php" method="POST" enctype="multipart/form-data" autocomplete="off">
                    <input type="hidden" name="slider_update" value="1">

                    <div class="mb-4">
                        <legend class="w-auto px-2">
                            <h5 class="text-center mb-4"><i class="fas fa-images"></i> Slider automático</h5>
                            <h6 class="text-center mb-3">Sube imágenes cuadradas para reemplazar las existentes.</h6>
                        </legend>
                        <div class="row">
                            <?php for ($i = 1; $i <= 8; $i++):
                                $nom_esp = "slider_auto_{$i}";
                            ?>
                                <div class="col-md-3 mb-4">
                                    <div class="card shadow-sm h-100">
                                        <img id="preview-<?php echo $nom_esp; ?>" src="<?php echo getMappedImage($nom_esp, $imagenesMap); ?>" class="card-img-top" style="height:180px; object-fit:cover;" alt="Slider automático <?php echo $i; ?>">
                                        <div class="card-body p-2">
                                            <label class="form-label">Imagen <?php echo $i; ?></label>
                                            <input type="file" name="<?php echo $nom_esp; ?>" class="form-control-file slider-image-input" accept=".jpg,.jpeg,.png,.webp">
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="mb-4">
                        <legend class="w-auto px-2">
                            <h5 class="text-center mb-4"><i class="fas fa-columns"></i> Slider vertical</h5>
                            <h6 class="text-center mb-3">Sube imágenes verticales para reemplazar las existentes.</h6>
                        </legend>
                        <div class="row">
                            <?php for ($i = 1; $i <= 6; $i++):
                                $nom_esp = "slider_vertical_{$i}";
                            ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card shadow-sm h-100">
                                        <img id="preview-<?php echo $nom_esp; ?>" src="<?php echo getMappedImage($nom_esp, $imagenesMap); ?>" class="card-img-top" style="height:240px; object-fit:cover;" alt="Slider vertical <?php echo $i; ?>">
                                        <div class="card-body p-2">
                                            <label class="form-label">Imagen <?php echo $i; ?></label>
                                            <input type="file" name="<?php echo $nom_esp; ?>" class="form-control-file slider-image-input" accept=".jpg,.jpeg,.png,.webp">
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <div class="text-center mt-4">
                        <button type="submit" class="btn btn-info btn-lg">
                            <i class="fas fa-upload"></i> Subir Imágenes
                        </button>
                    </div>
                </form>
            </fieldset>
        </div>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const form = document.querySelector("form.FormSubirImagenAjax");
    const inputs = document.querySelectorAll('.slider-image-input');

    inputs.forEach((input) => {
        input.addEventListener('change', function () {
            const file = this.files[0];
            const preview = document.getElementById('preview-' + this.name);
            if (!file || !preview) return;

            const reader = new FileReader();
            reader.onload = (event) => {
                preview.src = event.target.result;
            };
            reader.readAsDataURL(file);
        });
    });

    if (form) {
        form.addEventListener("submit", function (e) {
            e.preventDefault();

            Swal.fire({
                title: 'Subiendo imágenes...',
                text: 'Por favor espera mientras se procesan las imágenes.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });

            const datos = new FormData(form);

            fetch(form.action, {
                method: 'POST',
                body: datos
            })
            .then(res => res.json())
            .then(respuesta => {
                Swal.fire({
                    title: respuesta.Titulo,
                    text: respuesta.Texto,
                    icon: respuesta.Tipo
                }).then(() => {
                    if (respuesta.Alerta === 'recargar') {
                        location.reload();
                    } else if (respuesta.Alerta === 'limpiar') {
                        form.reset();
                    }
                });
            })
            .catch(error => {
                console.error('Error al subir las imágenes:', error);
                Swal.fire({
                    title: 'Error',
                    text: 'Hubo un error al subir las imágenes.',
                    icon: 'error'
                });
            });
        });
    }
});
</script>

