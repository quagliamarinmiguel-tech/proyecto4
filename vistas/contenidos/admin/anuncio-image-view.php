<?php
if($_SESSION['cargo_spm'] != 1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}
?>


<!--div class="container-fluid">
    <h4 class="text-center my-3"><i class="fas fa-images"></i> Imágenes ya subidas al slider horizontal</h4>

    <php
        require_once "./controladores/imagenControlador.php";
        $imgCtrl = new imagenControlador();
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        echo $imgCtrl->paginar_slider_imagenes_controlador("fond", $pagina, 8);

    ?>
</div-->

<div class="container-fluid">
    <h4 class="text-center my-3"><i class="fas fa-images"></i> Imágenes ya subidas al slider horizontal</h4>

    <div id="contenedorFondSliderAjax"></div>
</div>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-upload fa-fw"></i> &nbsp; SUBIR IMAGEN DE PERSONA PARA ANUNCIO
    </h3>
    <p class="text-justify text-muted">
        Aquí se suben las <strong>imágenes de personas sin fondo</strong> que aparecerán en los anuncios de la página publicitaria. Solo se admiten formatos <code>.png</code> o <code>.webp</code>.
    </p>
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/imagenAjax.php" method="POST" data-form="save" enctype="multipart/form-data" autocomplete="off">
        <input type="hidden" name="tipo_seleccionado" value="fond">

        <fieldset>
            <legend><i class="far fa-image"></i> &nbsp; Selección de imagen</legend>
            <div class="form-group">
                <label for="imagen" class="bmd-label-floating">Archivo PNG o WEBP sin fondo</label>
                <input type="file" name="imagen" id="imagen" accept=".png,.webp" class="form-control-file" required>
            </div>
        </fieldset>

        <br><br>
        <p class="text-center">
            <button type="reset" class="btn btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> LIMPIAR</button>
            &nbsp; &nbsp;
            <button type="submit" class="btn btn-info btn-sm"><i class="far fa-save"></i> GUARDAR</button>
        </p>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
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
                if(respuesta.Tipo === "success"){
                    Swal.fire({
                        title: respuesta.Titulo,
                        text: respuesta.Texto,
                        icon: respuesta.Tipo,
                        confirmButtonColor: '#28a745',
                        confirmButtonText: 'OK'
                    }).then(() => {
                        if(respuesta.Alerta === "recargar"){
                            location.reload();
                        }
                    });
                }
            })
            .catch(error => {
                console.error("Error al eliminar la imagen:", error);
                Swal.fire({
                    title: "Error",
                    text: "Hubo un error al eliminar la imagen.",
                    icon: "error"
                });
            });
        }
    });
});
</script>
<script>
document.addEventListener("DOMContentLoaded", () => {
    // 🔁 Cargar paginador AJAX inicial (modo admin con botón eliminar)
    cargarFondSlider(1, 8);

    // 🧭 Delegación para navegación
    document.addEventListener("click", function(e){
        if(e.target.classList.contains("btn-img-paginador")){
            const pagina = parseInt(e.target.dataset.pagina);
            const tipo = e.target.dataset.tipo;
            const cantidad = parseInt(e.target.dataset.cantidad);
            const contenedorID = "contenedorFondSliderAjax";

            fetch(`<?php echo SERVERURL; ?>ajax/imagenAjax.php?action=paginador-imagen-nuevo-ajax&page=${pagina}&tipo=${tipo}&cantidad=${cantidad}&eliminar=1`)
            .then(res => res.text())
            .then(html => {
                document.getElementById(contenedorID).innerHTML = html;
            });
        }
    });
});

// Función de carga inicial
function cargarFondSlider(pagina = 1, cantidad = 8){
    fetch(`<?php echo SERVERURL; ?>ajax/imagenAjax.php?action=paginador-imagen-nuevo-ajax&page=${pagina}&tipo=fond&cantidad=${cantidad}&eliminar=1`)
    .then(res => res.text())
    .then(html => {
        document.getElementById("contenedorFondSliderAjax").innerHTML = html;
    });
}
</script>