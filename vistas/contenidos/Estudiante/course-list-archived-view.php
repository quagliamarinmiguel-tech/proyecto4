<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; CURSOS ARCHIVADOS
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todos los cursos que han sido archivados. Puedes revisar sus vigencias, secciones, evaluaciones y calificaciones, o restaurarlos si es necesario.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=course-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=course-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=course-list-archived"><i class="fas fa-archive fa-fw"></i> &nbsp; CURSOS ARCHIVADOS</a>
        </li>
    </ul>   
</div>

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="course-list-archived">
  <div class="form-row align-items-end">

    <div class="col-md-2">
      <label for="filtro_id">ID del curso</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div>

    <div class="col-md-4">
      <label for="filtro_nombre">Nombre del curso</label>
      <select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los cursos</option>
        <?php
          $conexion = mainModel::conectar();
          $cursos = $conexion->query("SELECT id_curso, nombre_curso FROM curso WHERE estado_curso = 0 ORDER BY nombre_curso ASC");
          foreach ($cursos as $curso) {
              $selected = (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] == $curso['id_curso']) ? "selected" : "";
              echo '<option value="' . $curso['id_curso'] . '" ' . $selected . '>' . $curso['nombre_curso'] . '</option>';
          }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtro_tipo">Tipo de curso</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos los tipos</option>
        <option value="Intensivo" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Intensivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
        <option value="Otro" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Otro") ? "selected" : "" ?>>Otro</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtro_cupos">Disponibilidad de cupos</label>
      <select name="filtro_cupos" id="filtro_cupos" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= (isset($_GET['filtro_cupos']) && $_GET['filtro_cupos'] == "1") ? "selected" : "" ?>>Disponible</option>
        <option value="0" <?= (isset($_GET['filtro_cupos']) && $_GET['filtro_cupos'] == "0") ? "selected" : "" ?>>No disponible</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="">Sin orden específico</option>
        <option value="tipo" <?= (isset($_GET['orden']) && $_GET['orden'] == "tipo") ? "selected" : "" ?>>Tipo de curso (A-Z)</option>
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

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_curso->paginador_curso_archivado_controlador($pagina_actual, 15, "course-list-archived", "");
    ?>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
  $(document).ready(function() {
    $('#filtro_nombre').select2({
      placeholder: "Seleccione un curso",
      allowClear: true
    });
  });
</script>
