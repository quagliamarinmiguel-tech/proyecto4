<?php  
    if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-user-slash fa-fw"></i> &nbsp; PROFESORES ARCHIVADOS
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todos los profesores archivados en el sistema y restaurarlos si lo deseas. Un profesor archivado no se mostrará en la plataforma.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li><a href="<?php echo SERVERURL; ?>professor-new/"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a></li>
        <li><a href="<?php echo SERVERURL; ?>professor-list/"><i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; LISTA DE PROFESORES</a></li>
        <li><a href="<?php echo SERVERURL; ?>professor-search/"><i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR PROFESOR</a></li>
        <li><a class="active" href="<?php echo SERVERURL; ?>professor-archived/"><i class="fas fa-user-slash fa-fw"></i> &nbsp; PROFESORES ARCHIVADOS</a></li>
    </ul>
</div>
<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="profesor-list-archived">
  <div class="form-row align-items-end">

    <div class="col-md-2">
      <label for="filtro_id">ID Profesor</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= $_GET['filtro_id'] ?? "" ?>">
    </div>

    <div class="col-md-2">
      <label for="filtro_ci">CI</label>
      <input type="text" name="filtro_ci" id="filtro_ci" class="form-control" value="<?= $_GET['filtro_ci'] ?? "" ?>">
    </div>

    <div class="col-md-4">
      <label for="filtro_nombre">Nombre y Apellido</label>
      <select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los profesores</option>
        <?php
          $conexion = mainModel::conectar();
          $profesores = $conexion->query("SELECT id_profesor, nombre_profesor, apellido_profesor FROM profesor WHERE estado_profesor = 0 ORDER BY nombre_profesor ASC");
          foreach ($profesores as $prof) {
              $id = $prof['id_profesor'];
              $nombreCompleto = $prof['nombre_profesor'] . ' ' . $prof['apellido_profesor'];
              $selected = (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] == $id) ? "selected" : "";
              echo '<option value="' . $id . '" ' . $selected . '>' . $nombreCompleto . '</option>';
          }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="">Más reciente primero</option>
        <option value="alfabetico" <?= (isset($_GET['orden']) && $_GET['orden'] == "alfabetico") ? "selected" : "" ?>>Nombre (A-Z)</option>
      </select>
    </div>

    <div class="col-md-3 mt-3">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form>
<!-- 📋 Tabla de resultados -->
<div class="container-fluid">
    <?php
        require_once "./controladores/profesorControlador.php";
        $ins_profesor = new profesorControlador();

        $pagina_actual = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        echo $ins_profesor->paginador_profesor_archivado_controlador($pagina_actual, 15, "profesor-list-archived", "");
    ?>
</div>

<!-- Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('#filtro_nombre').select2({
      placeholder: "Seleccione un profesor",
      allowClear: true,
      width: '100%'
    });
  });
</script>
<!--div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-user-slash fa-fw"></i> &nbsp; PROFESORES ARCHIVADOS
    </h3>
    <p class="text-justify">
        En este módulo puedes visualizar todos los profesores archivados en el sistema y restaurarlos si lo deseas. Un profesor archivado no se mostrará en la plataforma.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>professor-new/"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>professor-list/"><i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; LISTA DE PROFESORES</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>professor-search/"><i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR PROFESOR</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>professor-archived/"><i class="fas fa-user-slash fa-fw"></i> &nbsp; PROFESORES ARCHIVADOS</a>
        </li>
    </ul>
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/profesorControlador.php";
        $ins_profesor = new profesorControlador();

        $pagina_actual = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        echo $ins_profesor->paginador_profesor_archivado_controlador($pagina_actual, 15, "professor-archived", "");
    ?>
</div-->