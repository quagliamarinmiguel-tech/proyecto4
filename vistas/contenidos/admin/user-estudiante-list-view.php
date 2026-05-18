<?php  
    if($_SESSION['cargo_spm']!=1){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-user-graduate fa-fw"></i> &nbsp; CUENTAS DE ESTUDIANTES
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todas las cuentas activas de estudiantes y actualizarlas si es necesario.
    </p>
</div>



<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user4-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/PERSONAL</a>
        </li>

        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user3-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/ESTUDIANTE</a>
        </li>
        
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-personal-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/PERSONAL</a>       
        </li>

        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-estudiante-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/ESTUDIANTE</a>       
        </li>    
    
    </ul>   
</div>


<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="user-estudiante-list">
  <div class="form-row align-items-end">

    <div class="col-md-2">
      <label for="filtro_usuario">ID Usuario</label>
      <input type="number" name="filtro_usuario" id="filtro_usuario" min="0" class="form-control" value="<?= $_GET['filtro_usuario'] ?? "" ?>">
    </div>

    <div class="col-md-4">
      <label for="filtro_estudiante">Nombre del estudiante</label>
      <!--select name="filtro_estudiante" id="filtro_estudiante" class="form-control select2">
        <option value="">Todos los nombres</option>
        <php
          $conexion = mainModel::conectar();
          $estudiantes = $conexion->query("
            SELECT e.id_estudiante, e.nombre, e.apellido
            FROM estudiante e
            INNER JOIN usuario_estudiante ue ON ue.id_estudiante_fk = e.id_estudiante
            WHERE e.estado_estudiante = 1
            ORDER BY e.nombre ASC

          ");
          foreach ($estudiantes as $est) {
              $selected = (isset($_GET['filtro_estudiante']) && $_GET['filtro_estudiante'] == $est['id_estudiante']) ? "selected" : "";
              echo '<option value="' . $est['id_estudiante'] . '" ' . $selected . '>' . $est['nombre'] . ' ' . $est['apellido'] . ' - id: ' . $est['id_estudiante'] . '</option>';
          }
        ?>
      </select-->
        
        
        
        
        <?php
$conexion = mainModel::conectar();
$filtro_estudiante = $_GET['filtro_estudiante'] ?? '';
$estudiante_nombre = '';

if ($filtro_estudiante !== '') {
    $stmt = $conexion->prepare("
        SELECT nombre, apellido 
        FROM estudiante 
        WHERE id_estudiante = :id AND estado_estudiante = 1
    ");
    $stmt->execute([':id' => $filtro_estudiante]);
    $est = $stmt->fetch();
    if ($est) {
        $estudiante_nombre = $est['nombre'] . ' ' . $est['apellido'] . ' - id: ' . $filtro_estudiante;
    }
}
?>
<select name="filtro_estudiante" id="filtro_estudiante" class="form-control select2-ajax">
  <?php if ($filtro_estudiante && $estudiante_nombre): ?>
    <option value="<?= htmlspecialchars($filtro_estudiante) ?>" selected>
      <?= htmlspecialchars($estudiante_nombre) ?>
    </option>
  <?php endif; ?>
</select>
        
        
        
        
        <!--select name="filtro_estudiante" id="filtro_estudiante" class="form-control select2-ajax">
          <php if (!empty($_GET['filtro_estudiante']) && !empty($estudiante_nombre)): ?>
            <option value="<?= htmlspecialchars($_GET['filtro_estudiante']) ?>" selected>
              <?= htmlspecialchars($estudiante_nombre) ?>
            </option>
          <php endif; ?>
        </select-->
        
    </div>

    <div class="col-md-2">
      <label for="filtro_ci">Cédula</label>
      <input type="number" name="filtro_ci" id="filtro_ci" min="0" class="form-control" value="<?= $_GET['filtro_ci'] ?? "" ?>">
    </div>

    <div class="col-md-2">
      <label for="filtro_estado">Activo</label>
      <select name="filtro_estado" id="filtro_estado" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= ($_GET['filtro_estado'] ?? "") == "1" ? "selected" : "" ?>>Sí</option>
        <option value="0" <?= ($_GET['filtro_estado'] ?? "") == "0" ? "selected" : "" ?>>No</option>
      </select>
    </div>

    <div class="col-md-2">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="">Sin orden específico</option>
        <option value="nombre" <?= ($_GET['orden'] ?? "") == "nombre" ? "selected" : "" ?>>Nombre (A-Z)</option>
        <option value="fecha" <?= ($_GET['orden'] ?? "") == "fecha" ? "selected" : "" ?>>Más nuevo primero</option>
        <!--option value="cedula" <?= ($_GET['orden'] ?? "") == "cedula" ? "selected" : "" ?>>Cédula (A-Z)</option-->
      </select>
    </div>

    <div class="col-md-2">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form>

<div class="container-fluid">
    <?php
        require_once "./controladores/usuarioControlador3.php";
        $ins_usuario = new usuarioControlador3();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_usuario->paginador_cuenta_estudiante_controlador($pagina_actual, 15, "user-estudiante-list", "");
    ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
$(document).ready(function() {
  $('#filtro_estudiante').select2({
    placeholder: "Seleccione un estudiante",
    allowClear: true,
    minimumInputLength: 2,
    width: '100%',
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_estudiante_usuario",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(e => ({
          id: e.id_estudiante,
          text: `${e.nombre} ${e.apellido} - id: ${e.id_estudiante}`
        }))
      }),
      cache: true
    }
  });
});
</script>

<!--script>
  $(document).ready(function() {
    $('#filtro_estudiante').select2({
      placeholder: "Seleccione un estudiante",
      allowClear: true
    });
  });
</script-->