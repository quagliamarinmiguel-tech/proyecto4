<?php
if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm'] != 4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./controladores/imagenControlador.php";
$imgCtrl = new imagenControlador();
$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;

// Imágenes ya usadas
//$imagenes_actuales = $imgCtrl->obtener_imagenes_slider("sldr1");
//$ids_actuales = $imagenes_actuales['ids'];
//$rutas_actuales = $imagenes_actuales['rutas'];

//$dataSlider = $imgCtrl->imagen_disponible_update_controlador("sldr1", 1, [2]);
$dataSlider = $imgCtrl->imagen_disponible_update_controlador("sldr1", 2, [1]);

$ids_actuales = $dataSlider['ids'];        // Para precargar el <input hidden>
$rutas_actuales = $dataSlider['rutas'];    // Para precargar el <input visible>
$imagenes_disponibles = $dataSlider['disponibles']; // Para las opciones disponibles

// Imágenes disponibles precargadas (excluyendo usadas por slider 2)
//$excluidas = $imgCtrl->obtener_ids_imagenes_por_slider("sldr2");
//$imagenes_disponibles = $imgCtrl->obtener_imagenes_filtradas_modelo("sldr1", $excluidas, 0, 999)->fetchAll(PDO::FETCH_ASSOC);
?>

<div class="container-fluid">
    <h4 class="text-center my-3"><i class="fas fa-images"></i> Imágenes disponibles para el slider horizontal</h4>
    <div id="contenedor-slider">
        <!--php echo $imgCtrl->paginador_imagen_ajax("sldr1", $pagina, 8, 1); ?-->
        <?php echo $imgCtrl->paginar_slider_imagenes_update_controlador('sldr1', $pagina, 8, 1); ?>
    </div>
</div>

<form id="form-actualizar-slider" action="<?php echo SERVERURL; ?>ajax/imagenAjax.php" method="POST" autocomplete="off">
    <input type="hidden" name="slider_id" value="2">
    <div class="row">
        <?php for($i = 0; $i < 8; $i++): 
            $ruta = $rutas_actuales[$i] ?? '';
            $id = $ids_actuales[$i] ?? '';
        ?>
        <div class="col-md-6 mb-4 position-relative">
            <label class="form-label">Imagen #<?php echo $i+1; ?></label>
            <div class="input-group">
                <input type="text" class="form-control campo-img" readonly value="<?php echo $ruta; ?>">
                <input type="hidden" name="ids[]" class="input-id" value="<?php echo $id; ?>">
                <button type="button" class="btn btn-outline-secondary btn-selector"><i class="fas fa-chevron-down"></i></button>
            </div>
            <div class="lista-imagenes d-none position-absolute bg-white border rounded mt-1 shadow-sm" style="z-index:9999; max-height:200px; overflow:auto;">
                <?php foreach($imagenes_disponibles as $img): ?>
                    <div class="list-group-item list-select small text-truncate text-muted" data-id="<?php echo $img['id_imagen']; ?>" data-ruta="<?php echo $img['ruta']; ?>" style="cursor:pointer;">
                        <?php echo $img['ruta']; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
        <?php endfor; ?>
    </div>
    <div class="text-center">
        <button type="submit" class="btn btn-success btn-lg" id="btn-guardar">Guardar cambios</button>
    </div>
</form>
<div style="height:20vh";></div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    const contenedor = document.getElementById("contenedor-slider");

    // PAGINADOR AJAX
    contenedor.addEventListener("click", function(e){
        if(e.target.classList.contains("paginarAjax")){
            e.preventDefault();
            const pagina = e.target.getAttribute("data-page");
            fetch(`<?php echo SERVERURL; ?>ajax/imagenAjax.php?action=paginador-imagen-ajax&page=${pagina}&tipo=sldr1&excluir=1`)
            .then(res => res.text())
            .then(html => { contenedor.innerHTML = html; })
            .catch(err => {
                console.error("Error al paginar imágenes:", err);
                alert("Error al cargar las imágenes.");
            });
        }
    });

    // SELECCIÓN DE IMAGEN
    document.querySelectorAll(".btn-selector").forEach((btn, index) => {
        btn.addEventListener("click", () => {
            const wrapper = btn.closest(".position-relative");
            const lista = wrapper.querySelector(".lista-imagenes");
            document.querySelectorAll(".lista-imagenes").forEach(l => {
                if(l !== lista) l.classList.add("d-none");
            });
            lista.classList.toggle("d-none");
        });
    });

    document.querySelectorAll(".list-select").forEach(item => {
        item.addEventListener("click", () => {
            const wrapper = item.closest(".position-relative");
            wrapper.querySelector(".campo-img").value = item.dataset.ruta;
            wrapper.querySelector(".input-id").value = item.dataset.id;
            wrapper.querySelector(".lista-imagenes").classList.add("d-none");
        });
    });

    // Cierre global si hace clic fuera
    document.addEventListener("click", e => {
        if(!e.target.closest(".position-relative")){
            document.querySelectorAll(".lista-imagenes").forEach(p => p.classList.add("d-none"));
        }
    });
});





</script>
<script>


document.addEventListener("DOMContentLoaded", () => {
    const form = document.getElementById("form-actualizar-slider");

    form.addEventListener("submit", function(e) {
        e.preventDefault(); // Evita el envío tradicional

        const formData = new FormData(this);

        fetch(this.action, {
            method: "POST",
            body: formData
        })
        .then(r => r.json())
        .then(data => {
            Swal.fire({
                title: data.Titulo,
                text: data.Texto,
                icon: data.Tipo
            });

            if (data.Alerta === "recargar") {
                setTimeout(() => location.reload(), 1500);
            }
        })
        .catch(err => {
            console.error("Error:", err);
            Swal.fire("Error", "No se pudo procesar la respuesta del servidor.", "error");
        });
    });
});


</script>
