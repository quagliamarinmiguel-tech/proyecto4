<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 && $_SESSION['cargo_spm']!=3 && $_SESSION['cargo_spm']!=2 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; LISTA DE PROFESORES
    </h3>
    <p class="text-justify">
        Visualiza todos los profesores registrados en el sistema. Puedes editarlos, ver sus imágenes o archivarlos si ya no están activos.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        

<?php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
    <li>
        <a href="<?php echo SERVERURL; ?>index.php?views=profesor-new">
            <i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO PROFESOR
        </a>
    </li>
<?php endif; ?>
        
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=profesor-list">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PROFESORES
            </a>
        </li>

    </ul>
</div>




<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="profesor-list">
  <div class="form-row align-items-end">

    <div class="col-md-3">
      <label for="filtro_id">ID del profesor</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div>

    <div class="col-md-3">
      <label for="filtro_ci">Cédula del profesor</label>
      <input type="text" name="filtro_ci" id="filtro_ci" class="form-control" value="<?= isset($_GET['filtro_ci']) ? $_GET['filtro_ci'] : "" ?>">
    </div>

    <div class="col-md-4">
    <label for="filtro_nombre">Nombre o apellido</label>
    <!--select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los profesores</option>
        <php
        $conexion = mainModel::conectar();
        $profesores = $conexion->query("
            SELECT id_profesor, nombre_profesor, apellido_profesor 
            FROM profesor 
            WHERE estado_profesor = 1 
            ORDER BY nombre_profesor ASC, apellido_profesor ASC
        ");
        foreach ($profesores as $prof) {
            $nombreCompleto = $prof['nombre_profesor'] . ' ' . $prof['apellido_profesor'];
            $selected = (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] == $prof['id_profesor']) ? "selected" : "";
            echo '<option value="' . $prof['id_profesor'] . '" ' . $selected . '>' . htmlspecialchars($nombreCompleto) . '</option>';
        }
        ?>
    </select-->
        <?php
        $filtro_nombre = $_GET['filtro_nombre'] ?? '';
        $profesor_nombre = '';

        if ($filtro_nombre !== '') {
            $conexion = mainModel::conectar();
            $stmt = $conexion->prepare("SELECT nombre_profesor, apellido_profesor FROM profesor WHERE id_profesor = :id");
            $stmt->execute([':id' => $filtro_nombre]);
            $prof = $stmt->fetch();
            if ($prof) {
                $profesor_nombre = $prof['nombre_profesor'] . ' ' . $prof['apellido_profesor'];
            }
        }
        ?>
        <select name="filtro_nombre" id="filtro_nombre" class="form-control select2-ajax">
          <?php if ($filtro_nombre && $profesor_nombre): ?>
            <option value="<?= $filtro_nombre ?>" selected><?= htmlspecialchars($profesor_nombre) ?></option>
          <?php endif; ?>
        </select>
        
        
    </div>
    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="default" <?= (!isset($_GET['orden']) || $_GET['orden'] == "default") ? "selected" : "" ?>>Más reciente</option>
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


<div class="container-fluid">
    <?php
        require_once "./controladores/profesorControlador.php";
        $ins_profesor = new profesorControlador();
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        echo $ins_profesor->paginador_profesor_controlador($pagina, 10, "profesor-list", "");
    ?>
</div>







<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
  $('#filtro_nombre').select2({
    placeholder: "Seleccione un profesor",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_profesor",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(p => ({
          id: p.id_profesor,
          text: `${p.nombre_profesor} ${p.apellido_profesor}`
        }))
      }),
      cache: true
    }
  });
});
</script>
<!--script>
  $(document).ready(function() {
    $('#filtro_nombre').select2({
      placeholder: "Seleccione un profesor",
      allowClear: true
    });
  });
</script-->




