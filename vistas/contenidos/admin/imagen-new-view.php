<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/localControlador.php";
$ctrl = new localControlador();


/*$sliders = $ctrl->obtener_datos_sliders_controlador();
//var_dump($sliders);die();
// Arrays de rutas (ya indexados por posición)
$rutasAuto   = $sliders['sldr1'] ?? [];
$rutasVertical = $sliders['sldr2'] ?? [];
$default = SERVERURL . 'vistas/assets/img/default.webp';*/


// Sliders auto
$rutasAuto = [];
for ($i = 1; $i <= 8; $i++) {
    $rutasAuto[$i - 1] = mainModel::obtenerRutaImagenLocal('slider_auto_' . $i);
}

// Sliders vertical
$rutasVertical = [];
for ($i = 1; $i <= 6; $i++) {
    $rutasVertical[$i - 1] = mainModel::obtenerRutaImagenLocal('slider_vertical_' . $i);
}


$imgn1 = [];
for ($i = 1; $i <= 8; $i++) {
    $ruta = mainModel::obtenerRutaImagenLocal('slider_auto_' . $i);
    if ($ruta !== SERVERURL . 'vistas/assets/img/default.webp') {
        $imgn1[] = $ruta;
    }
}

$imgn3 = [];
for ($i = 1; $i <= 6; $i++) {
    $ruta = mainModel::obtenerRutaImagenLocal('slider_vertical_' . $i);
    if ($ruta !== SERVERURL . 'vistas/assets/img/default.webp') {
        $imgn3[] = $ruta;
    }
}
//                                             src="<?php echo isset($rutasAuto[$i-1]) && $rutasAuto[$i-1] !== '' ? SERVERURL . 'vistas/' . $rutasAuto[$i-1] : $default; ?v"
//                                             <!--src="<c?cphp echoc cisset($rutasVertical[$i-1]) && $rutasVertical[$i-1] !== '' ? SERVERURL . 'vistas/' . $rutasVertical[$i-1] : $default; ?v"--> 
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

                    <!-- Slider automático (8 imágenes) -->
                    <div class="mb-4">
                        <legend class="w-auto px-2">
                            <h5 class="text-center mb-4"><i class="fas fa-images"></i> Slider automático</h5>
                            <h6 class="text-center mb-3">Sube imágenes cuadradas para reemplazar las existentes.</h6>
                        </legend>
                        <div class="row">
                            <?php for ($i = 1; $i <= 8; $i++): ?>
                                <?php $nomEsp = "slider_auto_{$i}"; ?>
                                <div class="col-md-3 mb-4">
                                    <div class="card shadow-sm h-100">
                                        <img id="preview-<?php echo $nomEsp; ?>" 
                                             src="<?= $rutasAuto[$i - 1] ?>"
                                             class="card-img-top" 
                                             style="height:180px; object-fit:cover;" 
                                             alt="Slider automático <?php echo $i; ?>">
                                        <div class="card-body p-2">
                                            <label class="form-label">Imagen <?php echo $i; ?></label>
                                            <input type="file" name="<?php echo $nomEsp; ?>" 
                                                   class="form-control-file slider-image-input" 
                                                   accept=".jpg,.jpeg,.png,.webp">
                                        </div>
                                    </div>
                                </div>
                            <?php endfor; ?>
                        </div>
                    </div>

                    <!-- Slider vertical (6 imágenes) -->
                    <div class="mb-4">
                        <legend class="w-auto px-2">
                            <h5 class="text-center mb-4"><i class="fas fa-columns"></i> Slider vertical</h5>
                            <h6 class="text-center mb-3">Sube imágenes verticales para reemplazar las existentes.</h6>
                        </legend>
                        <div class="row">
                            <?php for ($i = 1; $i <= 6; $i++): ?>
                                <?php $nomEsp = "slider_vertical_{$i}"; ?>
                                <div class="col-md-4 mb-4">
                                    <div class="card shadow-sm h-100">
                                        <img id="preview-<?php echo $nomEsp; ?>" 
                                             src="<?= $rutasVertical[$i - 1] ?>"
                                             class="card-img-top" 
                                             style="height:240px; object-fit:cover;" 
                                             alt="Slider vertical <?php echo $i; ?>">
                                        <div class="card-body p-2">
                                            <label class="form-label">Imagen <?php echo $i; ?></label>
                                            <input type="file" name="<?php echo $nomEsp; ?>" 
                                                   class="form-control-file slider-image-input" 
                                                   accept=".jpg,.jpeg,.png,.webp">
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

    // Previsualización en tiempo real
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

    // Envío del formulario con fetch
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
                        // Restaurar las previsualizaciones a las imágenes originales (recargando la página)
                        // O simplemente recargar la página para volver a cargar las imágenes actuales.
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