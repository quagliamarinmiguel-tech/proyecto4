
<?php  
    $cargo = $_SESSION['cargo_spm'] ?? 0;
	$state = $_SESSION['profile_status'] ?? 0;

    if (!in_array($cargo, [1, 2, 3, 4]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
$carpeta =  "admin";


?>
    

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS
    </h3>


    <p class="text-justify">
        Aquí puedes visualizar todos los cursos registrados en el sistema, editarlos o archivarlos.
    </p>
</div>


<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <?php if (in_array($cargo, [1, 4])): ?>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-new'; ?>">
                <i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO
            </a>
        </li>
        <?php endif; ?>

        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>

           <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list-archived'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS ARCHIVADOS</a>
        </li>

    </ul>   
</div>










<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="course-list">

  <div class="card shadow-sm mb-3">
    <div class="card-header bg-dark text-white">
      <i class="fas fa-filter"></i> &nbsp; Filtros de búsqueda de cursos
    </div>
    <div class="card-body">

      <!-- Fila 1: ID y nombre -->
      <div class="form-row">
        <!--div class="col-md-1 mb-2">
          <label for="filtro_id">ID del curso</label>
          <input type="number" name="filtro_id" id="filtro_id" min="0" class="form-control" value="<-?= $_GET['filtro_id'] ?? '' ?>">
        </div-->

        <div class="col-md-4 mb-3">
          <label for="filtro_nombre">Nombre del curso</label>
            <?php
                require_once "./controladores/cursoControlador.php";
                $ins_curso = new cursoControlador();

                $filtro_nombre = $_GET['filtro_nombre'] ?? '';
                $curso_nombre = '';

                if ($filtro_nombre !== '') {
                   $curso = $ins_curso->obtener_nombre_curso_para_filtro_controlador($filtro_nombre);
                   if ($curso) {
                        $curso_nombre = $curso['name'] . ' (' . $curso['type'] . ')';
                    }
                }
                 
            ?>
            <select name="filtro_nombre" id="filtro_nombre" class="form-control select2-ajax">
              <?php if ($filtro_nombre && $curso_nombre): ?>
                <option value="<?= $filtro_nombre ?>" selected><?= htmlspecialchars($curso_nombre) ?></option>
              <?php endif; ?>
            </select>

        </div>
          
        <div class="col-md-2 mb-3">
          <label for="filtro_tipo">Tipo de curso</label>
          <select name="filtro_tipo" id="filtro_tipo" class="form-control">
            <option value="">Todos los tipos</option>
            <option value="Intensivo" <?= ($_GET['filtro_tipo'] ?? '') == "Intensivo" ? "selected" : "" ?>>Intensivo</option>
            <option value="Extendido" <?= ($_GET['filtro_tipo'] ?? '') == "Extendido" ? "selected" : "" ?>>Extendido</option>
            <option value="Técnico Profesional" <?= ($_GET['filtro_tipo'] ?? '') == "Técnico Profesional" ? "selected" : "" ?>>Técnico Profesional</option>
            <!--option value="Otro" <?= ($_GET['filtro_tipo'] ?? '') == "Otro" ? "selected" : "" ?>>Otro</option-->
          </select>
        </div>
          
                    
          
      </div>

      <hr>

      <!-- Fila 3: Orden y botón -->
      <div class="form-row">
          
        <div class="col-md-3 mb-3">
          <label for="filtro_publicidad">Publicidad</label>
          <select name="filtro_publicidad" id="filtro_publicidad" class="form-control">
            <option value="">Todos</option>
            <option value="1" <?= ($_GET['filtro_publicidad'] ?? '') == "1" ? "selected" : "" ?>>Con publicidad</option>
            <option value="0" <?= ($_GET['filtro_publicidad'] ?? '') == "0" ? "selected" : "" ?>>Sin publicidad</option>
          </select>
        </div>
          
          
        <div class="col-md-3 mb-3">
          <label for="orden">Ordenar por</label>
          <select name="orden" id="orden" class="form-control">
            <option value="">Default</option>
            <!--option value="tipo" <?= ($_GET['orden'] ?? '') == "tipo" ? "selected" : "" ?>>Tipo de curso (A-Z)</option-->
            <option value="fecha" <?= ($_GET['orden'] ?? '') == "fecha" ? "selected" : "" ?>>Más nuevo primero</option>
          </select>
        </div>

        <div class="col-md-6 d-flex align-items-end">
          <button type="submit" class="btn btn-primary btn-block mt-2">
            <i class="fas fa-filter"></i> Aplicar filtros
          </button>
        </div>
      </div>

    </div>
  </div>
</form>









<div class="container-fluid">
    <?php
        require_once "./controladores/cursoControlador.php";
        $ins_curso = new cursoControlador();

        // Obtener el número de página actual
        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        echo $ins_curso->paginador_curso_supabase_controlador($pagina_actual, 15, "course-list", 1);
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
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso",
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
