<?php
if($_SESSION['cargo_spm']!=1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

// Cursos disponibles
require_once "./controladores/cursoControlador.php";
$cursoCtrl = new cursoControlador();
$cursos = $cursoCtrl->obtener_lista_cursos_tipo();

// Imágenes tipo fond
require_once "./controladores/imagenControlador.php";
$imgCtrl = new imagenControlador();
$fondImgs = $imgCtrl->obtener_imagenes_por_tipo('fond');

// Colores disponibles
$color_rutas = [];
for($i = 1; $i <= 15; $i++){
    $color_rutas[] = "http://www.cecilioceproca.wuaze.com/vistas/assets/img/color{$i}.webp";
}
?>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-bullhorn fa-fw"></i> &nbsp; NUEVO ANUNCIO PUBLICITARIO</h3>
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/anuncioAjax.php" method="POST" data-form="save" autocomplete="off">
        <fieldset>
            <legend><i class="far fa-image"></i> &nbsp; Datos del anuncio</legend>

            <div class="row">
                <!-- CURSO -->
                <div class="col-md-6 mb-4 position-relative">
                    <label class="form-label">Curso vinculado</label>
                    <div class="input-group">
                        <input type="text" class="form-control campo-curso" readonly placeholder="Selecciona un curso...">
                        <input type="hidden" name="curso_reg" class="input-curso-id">
                        <button type="button" class="btn btn-outline-secondary btn-selector-curso"><i class="fas fa-chevron-down"></i></button>
                    </div>
                    <div class="lista-cursos d-none position-absolute bg-white border rounded mt-1 shadow-sm" style="z-index:9999; max-height:200px; overflow:auto;">
                        <?php foreach($cursos as $c): ?>
                            <div class="list-group-item list-select-curso small text-muted text-truncate" 
                                 data-id="<?php echo $c['id_curso']; ?>" 
                                 data-label="<?php echo $c['nombre_curso'].' ('.$c['tipo_curso'].')'; ?>" 
                                 style="cursor:pointer;">
                                 <?php echo $c['nombre_curso'].' ('.$c['tipo_curso'].')'; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- COLOR DE FONDO -->
                <div class="col-md-6 mb-4 position-relative">
                    <label class="form-label">Fondo del anuncio</label>
                    <div class="input-group">
                        <input type="text" class="form-control campo-color" readonly placeholder="Selecciona un fondo...">
                        <input type="hidden" name="fondo_reg" class="input-color-id">
                        <button type="button" class="btn btn-outline-secondary btn-selector-color"><i class="fas fa-palette"></i></button>
                    </div>
                    <div class="lista-colores d-none position-absolute bg-white border rounded mt-1 shadow-sm" style="z-index:9999; max-height:200px; overflow:auto;">
                        <?php foreach($color_rutas as $ruta): ?>
                            <div class="list-group-item list-select-color small text-muted text-truncate" 
                                 data-id="<?php echo $ruta; ?>" 
                                 data-label="<?php echo basename($ruta); ?>" 
                                 style="cursor:pointer;">
                                 <?php echo basename($ruta); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TITULO -->
                <div class="col-md-6 mb-4">
                    <label class="form-label">Título del anuncio</label>
                    <input type="text" class="form-control" name="titulo_reg" maxlength="100" required>
                </div>

                <!-- TEXTO -->
                <div class="col-md-6 mb-4">
                    <label class="form-label">Texto descriptivo</label>
                    <textarea class="form-control" name="texto_reg" rows="3" maxlength="460" required></textarea>
                </div>

                <!-- IMAGEN FOND -->
                <div class="col-md-6 mb-4 position-relative">
                    <label class="form-label">Imagen sin fondo</label>
                    <div class="input-group">
                        <input type="text" class="form-control campo-fond" readonly placeholder="Selecciona imagen...">
                        <input type="hidden" name="imagen_reg" class="input-fond-id">
                        <button type="button" class="btn btn-outline-secondary btn-selector-fond"><i class="fas fa-chevron-down"></i></button>
                    </div>
                    <div class="lista-fond d-none position-absolute bg-white border rounded mt-1 shadow-sm" style="z-index:9999; max-height:200px; overflow:auto;">
                        <?php foreach($fondImgs as $img): ?>
                            <div class="list-group-item list-select-fond small text-muted text-truncate" 
                                 data-id="<?php echo $img['id_imagen']; ?>" 
                                 data-label="<?php echo $img['ruta']; ?>" 
                                 style="cursor:pointer;">
                                 <?php echo $img['ruta']; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </fieldset>

        <br>
        <p class="text-center">
            <button type="reset" class="btn btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> LIMPIAR</button>
            &nbsp;&nbsp;
            <button type="submit" class="btn btn-info btn-sm"><i class="far fa-save"></i> GUARDAR</button>
        </p>
    </form>
</div>
<div style="height:10vh;"></div>
<script>
document.addEventListener("DOMContentLoaded", () => {
    const listas = [
        { btn: '.btn-selector-curso', lista: '.lista-cursos', input: '.campo-curso', id: '.input-curso-id', tipo: '.list-select-curso' },
        { btn: '.btn-selector-color', lista: '.lista-colores', input: '.campo-color', id: '.input-color-id', tipo: '.list-select-color' },
        { btn: '.btn-selector-fond', lista: '.lista-fond', input: '.campo-fond', id: '.input-fond-id', tipo: '.list-select-fond' }
    ];

    listas.forEach(({btn, lista, input, id, tipo}) => {
        document.querySelectorAll(btn).forEach(b => {
            b.addEventListener("click", () => {
                const wrap = b.closest(".position-relative");
                const panel = wrap.querySelector(lista);
                document.querySelectorAll(lista).forEach(p => { if(p !== panel) p.classList.add("d-none"); });
                panel.classList.toggle("d-none");
            });
        });

        document.querySelectorAll(tipo).forEach(item => {
            item.addEventListener("click", () => {
                const wrap = item.closest(".position-relative");
                wrap.querySelector(input).value = item.dataset.label;
                wrap.querySelector(id).value = item.dataset.id;
                wrap.querySelector(lista).classList.add("d-none");
            });
        });
    });

    document.addEventListener("click", e => {
        if(!e.target.closest(".position-relative")){
            document.querySelectorAll(".lista-cursos, .lista-colores, .lista-fond").forEach(p => p.classList.add("d-none"));
        }
    });
});
</script>


