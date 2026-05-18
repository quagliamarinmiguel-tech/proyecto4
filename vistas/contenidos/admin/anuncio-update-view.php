<?php
if($_SESSION['cargo_spm']!=1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

// ID encriptado
$idEnc = $_GET['anuncio_id_up'] ?? '';

// Obtener anuncio
require_once "./controladores/anuncioControlador.php";
$ctrl = new anuncioControlador();
$datos = $ctrl->datos_anuncio_controlador("Unico", $idEnc);

if($datos->rowCount() != 1){
    echo '<div class="alert alert-danger text-center">No se encontró el anuncio.</div>';
    exit();
}

$anuncio = $datos->fetch();

// Cursos
require_once "./controladores/cursoControlador.php";
$cursoCtrl = new cursoControlador();
$cursos = $cursoCtrl->obtener_lista_cursos_tipo();

// Imágenes fond
require_once "./controladores/imagenControlador.php";
$imgCtrl = new imagenControlador();
$fondImgs = $imgCtrl->obtener_imagenes_por_tipo('fond');

// Colores
$color_rutas = [];
for($i = 1; $i <= 15; $i++){
    $color_rutas[] = "http://www.cecilioceproca.wuaze.com/vistas/assets/img/color{$i}.webp";
}
?>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-bullhorn fa-fw"></i> &nbsp; ACTUALIZAR ANUNCIO PUBLICITARIO</h3>
</div>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/anuncioAjax.php" method="POST" data-form="update" autocomplete="off">
        <input type="hidden" name="publicidad_id_up" value="<?php echo $idEnc; ?>">

        <fieldset>
            <legend><i class="far fa-edit"></i> &nbsp; Edita los datos</legend>

            <div class="row">
                <!-- CURSO -->
                <div class="col-md-6 mb-4 position-relative">
                    <label class="form-label">Curso vinculado</label>
                    <div class="input-group">
                        <input type="text" class="form-control campo-curso" readonly value="" placeholder="Selecciona un curso...">
                        <input type="hidden" name="curso_up" class="input-curso-id" value="<?php echo $anuncio['link_publicidad']; ?>">
                        <button type="button" class="btn btn-outline-secondary btn-selector-curso"><i class="fas fa-chevron-down"></i></button>
                    </div>
                    <div class="lista-cursos d-none position-absolute bg-white border rounded mt-1 shadow-sm" style="z-index:9999; max-height:200px; overflow:auto;">
                        <?php foreach($cursos as $c): ?>
                            <?php 
                                $label = $c['nombre_curso'].' ('.$c['tipo_curso'].')';
                                $selected = $anuncio['link_publicidad'] == $c['id_curso'] ? 'data-default="true"' : '';
                            ?>
                            <div class="list-group-item list-select-curso small text-muted text-truncate"
                                 data-id="<?php echo $c['id_curso']; ?>"
                                 data-label="<?php echo $label; ?>"
                                 <?php echo $selected; ?>
                                 style="cursor:pointer;">
                                 <?php echo $label; ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- FONDO -->
                <div class="col-md-6 mb-4 position-relative">
                    <label class="form-label">Fondo del anuncio</label>
                    <div class="input-group">
                        <input type="text" class="form-control campo-color" readonly value="<?php echo basename($anuncio['fondo_publicidad']); ?>" placeholder="Selecciona un fondo...">
                        <input type="hidden" name="fondo_up" class="input-color-id" value="<?php echo $anuncio['fondo_publicidad']; ?>">
                        <button type="button" class="btn btn-outline-secondary btn-selector-color"><i class="fas fa-palette"></i></button>
                    </div>
                    <div class="lista-colores d-none position-absolute bg-white border rounded mt-1 shadow-sm" style="z-index:9999; max-height:200px; overflow:auto;">
                        <?php foreach($color_rutas as $ruta): ?>
                            <?php $selected = $anuncio['fondo_publicidad'] == $ruta ? 'data-default="true"' : ''; ?>
                            <div class="list-group-item list-select-color small text-muted text-truncate"
                                 data-id="<?php echo $ruta; ?>"
                                 data-label="<?php echo basename($ruta); ?>"
                                 <?php echo $selected; ?>
                                 style="cursor:pointer;">
                                 <?php echo basename($ruta); ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>

                <!-- TÍTULO -->
                <div class="col-md-6 mb-4">
                    <label class="form-label">Título del anuncio</label>
                    <input type="text" class="form-control" name="titulo_up" maxlength="100" required value="<?php echo htmlspecialchars($anuncio['titulo_publicidad']); ?>">
                </div>

                <!-- TEXTO -->
                <div class="col-md-6 mb-4">
                    <label class="form-label">Texto descriptivo</label>
                    <textarea class="form-control" name="texto_up" rows="3" maxlength="460" required><?php echo htmlspecialchars($anuncio['texto_publicidad']); ?></textarea>
                </div>

                <!-- IMAGEN FOND -->
                <div class="col-md-6 mb-4 position-relative">
                    <label class="form-label">Imagen sin fondo</label>
                    <div class="input-group">
                        <input type="text" class="form-control campo-fond" readonly placeholder="Selecciona imagen...">
                        <input type="hidden" name="imagen_up" class="input-fond-id" value="<?php echo $anuncio['imagen_publicidad_fk']; ?>">
                        <button type="button" class="btn btn-outline-secondary btn-selector-fond"><i class="fas fa-chevron-down"></i></button>
                    </div>
                    <div class="lista-fond d-none position-absolute bg-white border rounded mt-1 shadow-sm" style="z-index:9999; max-height:200px; overflow:auto;">
                        <?php foreach($fondImgs as $img): ?>
                            <?php 
                                $selected = $anuncio['imagen_publicidad_fk'] == $img['id_imagen'] ? 'data-default="true"' : '';
                            ?>
                            <div class="list-group-item list-select-fond small text-muted text-truncate"
                                 data-id="<?php echo $img['id_imagen']; ?>"
                                 data-label="<?php echo $img['ruta']; ?>"
                                 <?php echo $selected; ?>
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
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-sync-alt"></i> ACTUALIZAR</button>
        </p>
    </form>
</div>

<script>
// Reutiliza el mismo script de anuncio-new pero aplica los valores precargados
document.addEventListener("DOMContentLoaded", () => {
    const sets = [
        { input: '.campo-curso', id: '.input-curso-id', tipo: '.list-select-curso' },
        { input: '.campo-color', id: '.input-color-id', tipo: '.list-select-color' },
        { input: '.campo-fond',  id: '.input-fond-id',  tipo: '.list-select-fond'  }
    ];

    sets.forEach(({ input, id, tipo }) => {
        const itemDefault = document.querySelector(`${tipo}[data-default="true"]`);
        if(itemDefault){
            document.querySelector(input).value = itemDefault.dataset.label;
        }
    });

    // ...podés incluir aquí el mismo script de interacción dropdown si lo tenés global

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