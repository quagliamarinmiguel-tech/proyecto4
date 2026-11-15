<?php
if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}
?>



<div class="container-fluid">
    <h4 class="text-center my-3"><i class="fas fa-images"></i> Imágenes ya subidas al slider horizontal</h4>

    <?php
        require_once "./controladores/imagenControlador.php";
        $imgCtrl = new imagenControlador();
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        echo $imgCtrl->paginar_slider_imagenes_controlador("sldr1", $pagina, 8);

    ?>
</div>


<div class="container-fluid">
    <div class="row justify-content-center">
        <div class="col-md-8">
            <fieldset class="border p-4" style="border-width:2px;">
                <legend class="w-auto px-2">
                    <h4 class="text-center mb-4"><i class="fas fa-image"></i> Administrar Imágenes de Sliders</h4>
                </legend>

                <!--form action="procesar_slider.php" method="POST" enctype="multipart/form-data" autocomplete="off"-->
                <!--form class="FormularioAjax" action="..." method="POST" ..-->

                <form class="FormSubirImagenAjax" action="<?php echo SERVERURL; ?>ajax/imagenAjax.php" method="POST" enctype="multipart/form-data" autocomplete="off">    
                    <div class="form-group">
                        <label for="imagen_slider" class="bmd-label-floating">Selecciona una imagen (recomendado: JPG/PNG)</label>
                        <input type="file" class="form-control-file" name="imagen" id="imagen_slider" accept=".jpg, .jpeg, .png, .webp" required>
                    </div>

                    <hr>


                    <div class="form-group">
                        <label class="d-block mb-2">Tipo esperado:</label>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_seleccionado" value="sldr1" id="radioHorizontal" required>
                            <label class="form-check-label" for="radioHorizontal">Horizontal (slider principal)</label>
                        </div>
                        <div class="form-check form-check-inline">
                            <input class="form-check-input" type="radio" name="tipo_seleccionado" value="sldr2" id="radioVertical" required>
                            <label class="form-check-label" for="radioVertical">Vertical (slider secundario)</label>
                        </div>

                        <div class="text-center mt-4">
                            <button type="submit" class="btn btn-info btn-lg">
                                <i class="fas fa-upload"></i> Subir Imagen
                            </button>
                        </div>

                    </div>
                </form>
            </fieldset>
        </div>
    </div>
</div>




<div class="container-fluid">
    <h4 class="text-center my-3"><i class="fas fa-images"></i> Imágenes ya subidas al slider vertical</h4>

    <?php
        require_once "./controladores/imagenControlador.php";
        $imgCtrl = new imagenControlador();
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        echo $imgCtrl->paginar_slider_imagenes_controlador("sldr2", $pagina, 8);

    ?>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {

    // SUBIDA DE IMAGEN
    const formSubida = document.querySelector("form.FormSubirImagenAjax");
    if(formSubida){
        formSubida.addEventListener("submit", function(e){
            e.preventDefault();


                //  Mostrar alerta de carga antes de enviar
            Swal.fire({
                title: 'Subiendo imagen...',
                text: 'Por favor espera mientras se procesa.',
                allowOutsideClick: false,
                allowEscapeKey: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });


            let datos = new FormData(formSubida);

            fetch(formSubida.action, {
                method: "POST",
                body: datos
            })
            .then(res => res.json())
            .then(respuesta => {
         
                Swal.fire({
                title: respuesta.Titulo,
                text: respuesta.Texto,
                icon: respuesta.Tipo
                }).then(() => {
                if(respuesta.Alerta === "recargar"){
                    location.reload();
                } else if(respuesta.Alerta === "limpiar"){
                    formSubida.reset();
                }
                });


            })
            .catch(error => {
                console.error("Error al subir la imagen:", error);
                alert("Hubo un error al subir la imagen.");
            });
        });
    }

    // ELIMINACIÓN DE IMAGEN
    document.addEventListener("submit", function(e){
        const form = e.target;
        if(form.classList.contains("FormEliminarImagenAjax")){
            e.preventDefault();

            const datos = new FormData(form);

            fetch(form.action, {
                method: "POST",
                body: datos
            })
            .then(res => res.json())
            .then(respuesta => {

                    Swal.fire({
                    title: respuesta.Titulo,
                    text: respuesta.Texto,
                    icon: respuesta.Tipo
                    }).then(() => {
                    if(respuesta.Alerta === "recargar"){
                        location.reload();
                    } else if(respuesta.Alerta === "limpiar"){
                        formSubida.reset();
                    }
                    });



            })
            .catch(error => {
                console.error("Error al eliminar la imagen:", error);
                alert("Hubo un error al eliminar la imagen.");
            });
        }
    });

});
</script>
