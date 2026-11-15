
<?php  
    $cargo   = $_SESSION['cargo_spm'] ?? 0;
	$state   = $_SESSION['profile_status'] ?? 0;
    $carpeta =  "admin";

    if (!in_array($cargo, [1, 2, 3, 4]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>



<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; CURSOS ARCHIVADOS
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todos los cursos que han sido archivados. Al archivar un curso, ya no estará disponible al crear periodos y en el apartado público.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
       <?php if (in_array($cargo, [1, 4])): ?>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
            <!--a href="<?php echo SERVERURL; ?>index.php?views=course-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a-->
        </li>
      <?php endif; ?>
        
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
            <!--a href="<?php echo SERVERURL; ?>index.php?views=course-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a-->
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list-archived'; ?>"><i class="fas fa-archive fa-fw"></i> &nbsp; CURSOS ARCHIVADOS</a>
        </li>
    </ul>   
    
    
    
    
    
    
</div>

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="course-list-archived">
  <div class="form-row align-items-end">

    <!--div class="col-md-2">
      <label for="filtro_id">ID del curso</label>
      <input type="number" name="filtro_id" id="filtro_id" min="0" class="form-control" value="<c?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div-->

    <div class="col-md-4">
      <label for="filtro_nombre">Nombre del curso</label>
      <select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los cursos</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtro_tipo">Tipo de curso</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos los tipos</option>
        <option value="Intensivo" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Intensivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
        <!--option value="Otro" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Otro") ? "selected" : "" ?>>Otro</option-->
      </select>
    </div>

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="">Default</option>
        <!--option value="tipo" <?= (isset($_GET['orden']) && $_GET['orden'] == "tipo") ? "selected" : "" ?>>Tipo de curso (A-Z)</option-->
        <option value="fecha" <?= (isset($_GET['orden']) && $_GET['orden'] == "fecha") ? "selected" : "" ?>>Más nuevo primero</option>
      </select>
    </div>

    <div class="col-md-3">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form>

<div class="container-fluid">
    <?php
        require_once "./controladores/cursoControlador.php";
        $ins_curso = new cursoControlador();

        // Obtener el número de página actual
        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        echo $ins_curso->paginador_curso_supabase_controlador($pagina_actual, 15, "course-list-archived","", 0);
    ?>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
  $('#filtro_nombre').select2({
    placeholder: "Seleccione un curso",
    allowClear: true,
    minimumInputLength: 2,
          language: {
      inputTooShort: function(args) {
        return "Por favor ingrese 2 o más caracteres";
      }
    },

    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso_archivado",
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