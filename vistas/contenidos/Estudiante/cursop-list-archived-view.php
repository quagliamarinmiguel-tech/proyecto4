<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; PUBLICIDADES ARCHIVADAS
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todas las publicidades de cursos que han sido archivadas. Si lo deseas, puedes restaurarlas para que vuelvan a estar disponibles en el sistema.
    </p>
</div>


<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="cursop-list-archived">
  <div class="form-row align-items-end">

    <!-- ID de publicidad -->
    <div class="col-md-2">
      <label for="filtro_id">ID de publicidad</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div>

    <!-- Nombre del curso -->
    <div class="col-md-4">
      <label for="filtro_nombre">Nombre del curso</label>
      <select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los cursos</option>
        <?php
        $conexion = mainModel::conectar();
        $cursos = $conexion->query("
          SELECT DISTINCT c.nombre_curso, c.tipo_curso
          FROM curso c
          INNER JOIN curso_publicidad cp ON cp.id_curso = c.id_curso
          WHERE cp.estado_curso_publicidad = 0 
          ORDER BY c.nombre_curso ASC
        ");
        foreach ($cursos as $curso) {
          $selected = (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] == $curso['nombre_curso']) ? "selected" : "";
          echo '<option value="' . $curso['nombre_curso'] . '" ' . $selected . '>' . $curso['nombre_curso'] . ' (' . $curso['tipo_curso'] . ')</option>';
        }
        ?>
      </select>
    </div>

    <!-- Tipo de curso -->
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

    <!-- Ordenar por -->
    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="" <?= (isset($_GET['orden']) && $_GET['orden'] == "") ? "selected" : "" ?>>Más reciente primero</option>
        <option value="nombre" <?= (isset($_GET['orden']) && $_GET['orden'] == "nombre") ? "selected" : "" ?>>Nombre del curso (A-Z)</option>
      </select>
    </div>

    <!-- Botón -->
    <div class="col-md-3">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>

  </div>
</form>


<div class="container-fluid">
    <?php
        require_once "./controladores/cursoPublicidadControlador.php";
        $insPublicidad = new cursoPublicidadControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $insPublicidad->paginador_publicidad_archivada_controlador($pagina_actual, 15, "cursop-list-archived", "");
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