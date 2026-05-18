<?php
if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
    
    
    
$cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";

    
    
}
$pdo = mainModel::conectar();
$sql = $pdo->query("
    SELECT id_curso, nombre_curso, tipo_curso
    FROM curso
    WHERE estado_curso = 1
      AND id_curso NOT IN (
          SELECT id_curso FROM curso_publicidad
      )
    ORDER BY nombre_curso ASC
");
$cursos = $sql->fetchAll();
?>
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">



<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-book-open fa-fw"></i> &nbsp; NUEVA PUBLICIDAD DE CURSO
    </h3>
    <p class="text-justify">Completa la información publicitaria asociada a un curso existente.</p>
</div>




<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cursop-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA PUBLICIDAD</a>

        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cursop-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PUBLICIDADES</a>

        </li>
    </ul>
</div>

<div class="container-fluid">
    <form id="form-publicidad" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/publicidadAjax.php" method="POST" data-form="save" enctype="multipart/form-data" autocomplete="off">
        
        <!-- ID del curso seleccionado -->
        <input type="hidden" name="id_curso" id="id_curso_seleccionado">

            <div class="form-group">
            <label for="id_curso">Curso</label>
            <!--select name="id_curso" id="curso_up" class="form-control select2" required>
                <option value="">Seleccione un curso</option>
                <php
                // Suponiendo que $cursos contiene los cursos activos
                foreach($cursos as $curso){
                    echo "<option value='{$curso['id_curso']}'>{$curso['nombre_curso']} ({$curso['tipo_curso']})</option>";
                }
                ?>
            </select-->
                <select name="id_curso" id="curso_up" class="form-control select2-ajax" required>
                  <option value="">Seleccione un curso</option>
                </select>
            </div>


        <!-- Bloque: Prefacio + Descripción juntos -->
        <fieldset>
            <legend><i class="fas fa-file-alt"></i> &nbsp; Información del contenido
            
            <button type="button" class="btn btn-sm ver-ayuda" 
        data-src="<?php echo SERVERURL; ?>vistas/assets/img/ayuda1.webp" 
        title="Ver ayuda 1"
        style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
  <i class="fas fa-question"></i>
</button>
            
            </legend>
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Prefacio</label>
                    
                    <!--input type="text" pattern="^[^\s^&+=|{}\[\]<>][a-zA-Z0-9áéíóúÁÉÍÓÚñÑ\s.,-]{0,79}$" maxlength="50" required-->
                    <input type="text" class="form-control" 
                           									 name="prefacio" 
 maxlength="79" required>
                </div>


                
                <div class="col-md-8 mb-3">
                    <label class="form-label">Descripción extensa</label>
                    <textarea name="materia_descripcion" class="form-control"  rows="3" maxlength="345" required></textarea>
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
            <?php
            for($i = 1; $i <= 10; $i++){
                $required = ($i <= 4) ? 'required' : '';
                echo '
                <div class="col-md-6 mb-3">
                    <label class="form-label">Contenido '.$i.'</label>
                    <input type="text" class="form-control" name="cont'.$i.'" maxlength="79" '.$required.'>
                </div>';
            }
            ?>
            </div>



        </fieldset>

        <fieldset>
            <legend><i class="fas fa-question-circle"></i> &nbsp;Requisitos del curso
            
            <button type="button" class="btn btn-sm ver-ayuda" 
        data-src="<?php echo SERVERURL; ?>vistas/assets/img/ayuda3.webp" 
        title="Ver ayuda 3"
        style="background-color: #3CA6A6; color: #fff; border: none; padding: 4px 8px; border-radius: 4px;">
  <i class="fas fa-question"></i>
</button>
            
            </legend>

            <div class="row">
                <?php
                for($i = 1; $i <= 4; $i++){
                    echo '
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Requisito '.$i.'</label>
                        <input type="text" class="form-control" name="requisito'.$i.'" maxlength="59">
                    </div>';
                }
                ?>
            </div>


        </fieldset>

        <fieldset>
            <legend><i class="fas fa-calendar-alt"></i> &nbsp;Horario de clases
        
            
            </legend>

            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Selecciona el horario del curso</label>
                    <select name="clase" class="form-select" required>
                        <option value="" disabled selected>Seleccione una opción...</option>
                        <option value="lunes-viernes-mañana">Clases lunes a viernes por la mañana</option>
                        <option value="lunes-viernes-tarde">Clases lunes a viernes por la tarde</option>
                        <option value="lunes-viernes-full">Clases lunes a viernes mañana y tarde</option>
                        <option value="sabado-mañana">Clases los sábados por la mañana</option>
                        <option value="sabado-tarde">Clases los sábados por la tarde</option>
                        <option value="sabado-full">Clases los sábados mañana y tarde</option>
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



<div class="row">
  <!-- Curso Técnico Profesional -->
  <div class="col-md-4 mb-3 text-center">
    <i class="bi bi-mortarboard" style="font-size: 2rem; color: #F54927;"></i><br>
    <label class="form-label mt-2 fw-bold">¿Es un curso Técnico Profesional?</label><br>
    <input type="checkbox" name="icono1" value="1">
  </div>

  <!-- Colaboración con empresas -->
  <div class="col-md-4 mb-3 text-center">
    <i class="bi bi-buildings" style="font-size: 2rem; color: #3CA6A6;"></i><br>
    <label class="form-label mt-2 fw-bold">¿Se colabora con negocios y empresas?</label><br>
    <input type="checkbox" name="icono2" value="1">
  </div>

  <!-- Prácticas y proyectos -->
  <div class="col-md-4 mb-3 text-center">
    <i class="bi bi-tools" style="font-size: 2rem; color: #5E27F5;"></i><br>
    <label class="form-label mt-2 fw-bold">¿Hay prácticas y proyectos dentro y fuera de la institución?</label><br>
    <input type="checkbox" name="icono3" value="1">
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

<!-- Repite para ayuda_3.webp hasta ayuda_6.webp -->
            
            
            </legend>
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label">Imagen (.png, .jpg, .webp)</label>
                    <input type="file" id="archivo_imagen" name="archivo_imagen" class="form-control-file" accept=".jpg,.jpeg,.png,.webp" required>
                    <div class="mt-2" id="preview-imagen" style="display:none;">
                        <img src="" alt="Preview" style="max-width:200px;" class="img-thumbnail">
                    </div>
                </div>
            </div>
        </fieldset>




        <br>
        <p class="text-center">
            <button type="submit" class="btn btn-success btn-sm">
                <i class="far fa-save"></i> GUARDAR PUBLICIDAD
            </button>
        </p>
    </form>

</div>







<!-- jQuery primero -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<!-- Select2 CSS -->
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<!-- Select2 JS -->
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Inicialización -->
<script>
$(document).ready(function() {
  $('#curso_up').select2({
    placeholder: "Seleccione un curso",
    allowClear: true,
    width: '100%',
    minimumInputLength: 2,
                language: {
      inputTooShort: function(args) {
        return "Por favor ingrese 2 o más caracteres";
      }
    },
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso_publicidad",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(c => ({
          id: c.id_curso,
          text: `${c.nombre_curso} (${c.tipo_curso})`
        }))
      }),
      cache: true
    }
  });
});
</script>
<!--script>
  $(document).ready(function() {
    $('.select2').select2({
      placeholder: "Seleccione un curso",
      allowClear: true,
      width: '100%'
    });
  });
</script-->

<!--script>
  $(document).ready(function() {
    $('#curso_up').on('change', function() {
      const selectedID = $(this).val();
      $('#id_curso_seleccionado').val(selectedID);
    });
  });
</script-->

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





<!--
contenidos sin required
-->
            <!--div class="row">
                <php
                for($i = 1; $i <= 10; $i++){
                    echo '
                    <div class="col-md-6 mb-3">
                        <label class="form-label">Contenido '.$i.'</label>
                        <input type="text" class="form-control" name="cont'.$i.'" maxlength="79">
                    </div>';
                }
                ?>
            </div-->

<!---
filas de aqui obtendras
-->

            <!--div class="row">
                <div class="col-md-4 mb-3 text-center">
                    <label class="form-label">¿Es un curso Técnico Profesional?</label><br>
                    <input type="checkbox" name="icono1" value="1">
                </div>
                <div class="col-md-4 mb-3 text-center">
                    <label class="form-label">¿Se colabora con negocios y empresas?</label><br>
                    <input type="checkbox" name="icono2" value="1">
                </div>
                <div class="col-md-4 mb-3 text-center">
                    <label class="form-label">¿Hay prácticas y proyectos dentro y fuera de la institución?</label><br>
                    <input type="checkbox" name="icono3" value="1">
                </div>
            </div-->



            <!--div class="row">
            <div class="col-md-4 mb-3 text-center">
                <i class='bx bx-buildings' style="font-size: 2rem; color: #007bff;"></i><br>
                <label class="form-label mt-2 fw-bold">¿Es un curso Técnico Profesional?</label><br>
                <input type="checkbox" name="icono1" value="1">
            </div>

            <div class="col-md-4 mb-3 text-center">
                <i class='bx bxs-wrench' style="font-size: 2rem; color: #28a745;"></i><br>
                <label class="form-label mt-2 fw-bold">¿Se colabora con negocios y empresas?</label><br>
                <input type="checkbox" name="icono2" value="1">
            </div>

            <div class="col-md-4 mb-3 text-center">
                <i class='bx bx-bookmark-alt-minus' style="font-size: 2rem; color: #ffc107;"></i><br>
                <label class="form-label mt-2 fw-bold">¿Hay prácticas y proyectos dentro y fuera de la institución?</label><br>
                <input type="checkbox" name="icono3" value="1">
            </div>
            </div-->
