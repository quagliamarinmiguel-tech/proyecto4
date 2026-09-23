<?php
if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}


    require_once "./controladores/cursoPublicidadControlador.php";
    $insPublic = new cursoPublicidadControlador();

    $idPublicEnc = $_GET['publicidad_id_up'] ?? '';
    $idPublicDec = mainModel::decryption($idPublicEnc);

    if(!$idPublicDec || !is_numeric($idPublicDec)){
        echo $lc->forzar_cierre_sesion_controlador(); exit();
    }

    $datos = $insPublic::datos_publicidad_modelo("Unico", $idPublicDec);
    if($datos->rowCount() != 1){
        echo '<div class="alert alert-danger text-center">No se encontró la publicidad</div>'; exit();
    }

    $campos = $datos->fetch();
?>

<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">


<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-bullhorn fa-fw"></i> &nbsp; ACTUALIZAR PUBLICIDAD DE CURSO
    </h3>
    <p class="text-justify">Editá los datos de esta entrada publicitaria asociada al curso <strong><?= htmlspecialchars($campos['nombre_curso']) ?></strong>.</p>
</div>

<div class="container-fluid">
    <form id="form-publicidad" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/publicidadAjax.php" method="POST" data-form="update" enctype="multipart/form-data" autocomplete="off">
        
        <input type="hidden" name="publicidad_id_up" value="<?= $_GET['publicidad_id_up'] ?>">
        <input type="hidden" name="id_curso" id="id_curso_seleccionado" value="<?= $campos['id_curso'] ?>">

        <fieldset>
            <legend><i class="fas fa-search"></i> &nbsp; Curso asignado</legend>
            <div class="row">
                <div class="col-md-8 mb-3">
                    <label class="form-label">Curso actual</label>
                    <input type="text" class="form-control" value="<?= htmlspecialchars($campos['nombre_curso']) ?>" readonly>
                    <small id="val_curso" class="form-text mt-1"></small>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Prefacio</label>
                    <input type="text" class="form-control" name="prefacio" maxlength="80" required value="<?= htmlspecialchars($campos['prefacio']) ?>">
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="fas fa-file-alt"></i> &nbsp; Descripción
                        <button type="button" class="btn btn-sm ver-ayuda" 
        data-src="<?php echo SERVERURL; ?>vistas/assets/img/ayuda1.webp" 
        title="Ver ayuda 1"
        style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
  <i class="fas fa-question"></i>
</button>
            
            </legend>
            <div class="row">
                <div class="col-md-12 mb-3">
                    <textarea name="materia_descripcion" class="form-control" rows="3" maxlength="360" required><?= htmlspecialchars($campos['materia_descripcion']) ?></textarea>
                </div>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="fas fa-list"></i> &nbsp; Contenido del curso
                                    <button type="button" class="btn btn-sm ver-ayuda" 
        data-src="<?php echo SERVERURL; ?>vistas/assets/img/ayuda2.webp" 
        title="Ver ayuda 2"
        style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
  <i class="fas fa-question"></i>
</button>   
            </legend>
            <div class="row">
                <?php for($i = 1; $i <= 10; $i++): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contenido <?= $i ?></label>
                    <input type="text" class="form-control" name="cont<?= $i ?>" maxlength="80" value="<?= htmlspecialchars($campos["cont$i"]) ?>">
                </div>
                <?php endfor; ?>
            </div>
        </fieldset>

        <fieldset>
            <legend><i class="fas fa-check-circle"></i> &nbsp; Requisitos   
                        <button type="button" class="btn btn-sm ver-ayuda" 
        data-src="<?php echo SERVERURL; ?>vistas/assets/img/ayuda3.webp" 
        title="Ver ayuda 3"
        style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
  <i class="fas fa-question"></i>
</button>   
            </legend>
            <div class="row">
                <?php for($i = 1; $i <= 4; $i++): ?>
                <div class="col-md-6 mb-3">
                    <label class="form-label">Requisito <?= $i ?></label>
                    <input type="text" class="form-control" name="requisito<?= $i ?>" maxlength="60" value="<?= htmlspecialchars($campos["requisito$i"]) ?>">
                </div>
                <?php endfor; ?>
            </div>
        </fieldset>



        <fieldset>
            <legend><i class="fas fa-calendar-alt"></i> &nbsp; Horario de clases</legend>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Selecciona el horario del curso</label>
                    <select name="clase" class="form-select" required>
                        <option value="" disabled>Seleccione una opción...</option>
                        <option value="lunes-viernes-mañana" <?= $campos['clase'] == 'lunes-viernes-mañana' ? 'selected' : '' ?>>Clases lunes a viernes por la mañana</option>
                        <option value="lunes-viernes-tarde" <?= $campos['clase'] == 'lunes-viernes-tarde' ? 'selected' : '' ?>>Clases lunes a viernes por la tarde</option>
                        <option value="lunes-viernes-full" <?= $campos['clase'] == 'lunes-viernes-full' ? 'selected' : '' ?>>Clases lunes a viernes mañana y tarde</option>
                        <option value="sabado-mañana" <?= $campos['clase'] == 'sabado-mañana' ? 'selected' : '' ?>>Clases los sábados por la mañana</option>
                        <option value="sabado-tarde" <?= $campos['clase'] == 'sabado-tarde' ? 'selected' : '' ?>>Clases los sábados por la tarde</option>
                        <option value="sabado-full" <?= $campos['clase'] == 'sabado-full' ? 'selected' : '' ?>>Clases los sábados mañana y tarde</option>
                    </select>
                </div>
            </div>
        </fieldset>


        <fieldset>
            <legend><i class="fas fa-icons"></i> &nbsp; Cualidades del curso
            
            <button type="button" class="btn btn-sm ver-ayuda" 
                data-src="<?php echo SERVERURL; ?>vistas/assets/img/ayuda4.webp" 
                title="Ver ayuda 4"
                style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
                <i class="fas fa-question"></i>
            </button>
            </legend>
            <!--div class="row">
                <div class="col-md-4 mb-3 text-center">
                    <label class="form-label">¿Es un curso Técnico Profesional?</label><br>
                    <input type="checkbox" name="icono1" value="1" <?= $campos['icono1'] == 1 ? 'checked' : '' ?>>
                </div>
                <div class="col-md-4 mb-3 text-center">
                    <label class="form-label">¿Se colabora con negocios y empresas?</label><br>
                    <input type="checkbox" name="icono2" value="1" <?= $campos['icono2'] == 1 ? 'checked' : '' ?>>
                </div>
                <div class="col-md-4 mb-3 text-center">
                    <label class="form-label">¿Hay prácticas y proyectos dentro y fuera de la institución?</label><br>
                    <input type="checkbox" name="icono3" value="1" <?= $campos['icono3'] == 1 ? 'checked' : '' ?>>
                </div>
            </div-->

<div class="row">
  <!-- Curso Técnico Profesional -->
  <div class="col-md-4 mb-3 text-center">
    <i class="bi bi-mortarboard" style="font-size: 2rem; color: #F54927;"></i><br>
    <label class="form-label mt-2 fw-bold">¿Es un curso Técnico Profesional?</label><br>
    <input type="checkbox" name="icono1" value="1" <?= $campos['icono1'] == 1 ? 'checked' : '' ?>>
  </div>

  <!-- Colaboración con empresas -->
  <div class="col-md-4 mb-3 text-center">
    <i class="bi bi-buildings" style="font-size: 2rem; color: #3CA6A6;"></i><br>
    <label class="form-label mt-2 fw-bold">¿Se colabora con negocios y empresas?</label><br>
    <input type="checkbox" name="icono2" value="1" <?= $campos['icono2'] == 1 ? 'checked' : '' ?>>
  </div>

  <!-- Prácticas y proyectos -->
  <div class="col-md-4 mb-3 text-center">
    <i class="bi bi-tools" style="font-size: 2rem; color: #5E27F5;"></i><br>
    <label class="form-label mt-2 fw-bold">¿Hay prácticas y proyectos dentro y fuera de la institución?</label><br>
    <input type="checkbox" name="icono3" value="1" <?= $campos['icono3'] == 1 ? 'checked' : '' ?>>
  </div>
</div>

        </fieldset>

        <fieldset>
            <legend><i class="far fa-image"></i> &nbsp; Imagen publicitaria    
            <button type="button" class="btn btn-sm ver-ayuda" 
                data-src="<?php echo SERVERURL; ?>vistas/assets/img/ayuda5.webp" 
                title="Ver ayuda 5"
                style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
                <i class="fas fa-question"></i>
            </button>

            </legend>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Reemplazar imagen (.jpg, .png, .webp)</label>
                    <input type="file" id="archivo_imagen" name="archivo_imagen" class="form-control-file" accept=".jpg,.jpeg,.png,.webp">
                    <div class="mt-2" id="preview-imagen">
                        <img src="<?php echo SERVERURL; ?>vistas/<?= $campos['ruta_imagen'] ?>" alt="Actual" class="img-thumbnail" style="max-width:200px;">
                    </div>
                </div>
            </div>
        </fieldset>

        <br>
        <p class="text-center">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-sync"></i> ACTUALIZAR PUBLICIDAD
            </button>
        </p>
    </form>
</div>







<div id="modalAyuda" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
  <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff; padding:20px; border-radius:8px;">
    <img id="imgAyuda" src="" class="img-thumbnail" style="max-width:100%; height:auto; display:block; margin:auto; border:3px solid white;">
    <button onclick="document.getElementById('modalAyuda').style.display='none'" style="position:absolute; top:10px; right:10px;background-color:red;" class="btn btn-sm btn-light">
      <i class="fas fa-times"></i>
    </button>
  </div>
</div>

<script>
//modal
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
//imagen
document.addEventListener("DOMContentLoaded", () => {
    // 🖼️ Preview de imagen subida
    const inputImagen = document.getElementById("archivo_imagen");
    const preview = document.getElementById("preview-imagen");
    const previewImg = preview.querySelector("img");

    inputImagen.addEventListener("change", () => {
        const file = inputImagen.files[0];
        if (!file) {
            preview.style.display = "none";
            return;
        }

        const ext = file.name.split('.').pop().toLowerCase();
        if (!["jpg", "jpeg", "png", "webp"].includes(ext)) {
            alert("Formato no permitido.");
            inputImagen.value = "";
            preview.style.display = "none";
            return;
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



