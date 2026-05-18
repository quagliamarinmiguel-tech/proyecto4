<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; CURSOS VIGENTES ARCHIVADOS
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todos los cursos vigentes que pertenecen a cohortes archivadas. Puedes filtrar por tipo de curso y año de cohorte para encontrar lo que necesitas.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=course-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=course-archived"><i class="fas fa-archive fa-fw"></i> &nbsp; CURSOS ARCHIVADOS</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=course-vigente-archived"><i class="fas fa-clipboard-check fa-fw"></i> &nbsp; CURSOS VIGENTES ARCHIVADOS</a>
        </li>
    </ul>   
</div>

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="course-vigente-archived">
  <div class="form-row align-items-end">


    <div class="col-md-3">
      <label for="filtro_id_cv">ID de curso vigente</label>
      <input type="number" name="filtro_id_cv" id="filtro_id_cv" class="form-control" value="<?= isset($_GET['filtro_id_cv']) ? $_GET['filtro_id_cv'] : "" ?>">
    </div>

    <div class="col-md-4">
      <label for="filtro_tipo">Filtrar por tipo de curso</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos los tipos</option>
        <option value="Intensivo" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Intensivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
        <option value="Otro" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Otro") ? "selected" : "" ?>>Otro</option>
      </select>
    </div>


    <div class="col-md-4">
      <label for="filtro_curso">Buscar curso por nombre</label>
      <select name="filtro_curso" id="filtro_curso" class="form-control select2">
        <option value="">Todos los cursos</option>
        <?php
          $conexion = mainModel::conectar();
          $cursos = $conexion->query("SELECT id_curso, nombre_curso, tipo_curso FROM curso WHERE estado_curso = 0 ORDER BY nombre_curso ASC")->fetchAll();
          foreach($cursos as $curso){
              $selected = (isset($_GET['filtro_curso']) && $_GET['filtro_curso'] == $curso['id_curso']) ? 'selected' : '';
              echo "<option value='{$curso['id_curso']}' {$selected}>{$curso['nombre_curso']} ({$curso['tipo_curso']})</option>";
          }
        ?>
      </select>
    </div>

    <div class="col-md-4">
      <label for="filtro_anio">Filtrar por año de cohorte</label>
      <input type="number" name="filtro_anio" id="filtro_anio" class="form-control" min="25" max="40" value="<?= isset($_GET['filtro_anio']) ? $_GET['filtro_anio'] : "" ?>">
    </div>


    <div class="col-md-3">
    <label for="orden">Ordenar por</label>
    <select name="orden" id="orden" class="form-control">
      <option value="default" <?= (!isset($_GET['orden']) || $_GET['orden'] == "default") ? "selected" : "" ?>>Más reciente</option>
      <option value="nombre" <?= (isset($_GET['orden']) && $_GET['orden'] == "nombre") ? "selected" : "" ?>>Nombre del curso</option>
      <option value="estudiantes" <?= (isset($_GET['orden']) && $_GET['orden'] == "estudiantes") ? "selected" : "" ?>>Cantidad de estudiantes</option>
    </select>
    </div>

    <div class="col-md-4">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form>

<div class="container-fluid">
    <?php
        require_once "./controladores/cohorteControlador.php";
        $ins_curso = new cohorteControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_curso->paginador_curso_vigente_archivado_controlador($pagina_actual, 15, "course-vigente-archived");
    ?>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('#filtro_curso').select2({
      placeholder: "Escriba el nombre del curso",
      allowClear: true,
      width: '100%'
    });
  });
</script>

