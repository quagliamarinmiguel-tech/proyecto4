<?php  
    if($_SESSION['cargo_spm']!=1){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-users fa-fw"></i> &nbsp; LISTA DE PERSONAL
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todos los registros de personal vinculados a usuarios y actualizarlos si es necesario.
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
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-personal-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/PERSONAL</a>       
        </li>

        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-estudiante-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/ESTUDIANTE</a>       
        </li>    
    
    </ul>   
</div>


    
    
    
    
    
    
    
    
    
    
    


    
    
    
    
    
<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="user-personal-list">
  <div class="form-row align-items-end">

    <div class="col-md-2">
      <label for="filtro_usuario">ID Usuario</label>
      <input type="number" name="filtro_usuario" id="filtro_usuario" min="0" class="form-control" value="<?= $_GET['filtro_usuario'] ?? "" ?>">
    </div>

    <!--div class="col-md-2">
      <label for="filtro_personal">ID Personal</label>
      <input type="number" name="filtro_personal" id="filtro_personal" class="form-control" value="<?= $_GET['filtro_personal'] ?? "" ?>">
    </div-->
      
      <!--div class="col-md-4">
  <label for="filtro_personal">Nombre del personal</label>
  <select name="filtro_personal" id="filtro_personal" class="form-control select2">
    <option value="">Todos los nombres</option>
    <php
      $conexion = mainModel::conectar();
      $personal = $conexion->query("
        SELECT id_personal, nombre_personal, apellido_personal 
        FROM personal 
        ORDER BY nombre_personal ASC
      ");
      foreach ($personal as $persona) {
          $selected = (isset($_GET['filtro_personal']) && $_GET['filtro_personal'] == $persona['id_personal']) ? "selected" : "";
          echo '<option value="' . $persona['id_personal'] . '" ' . $selected . '>' . $persona['nombre_personal'] . ' ' . $persona['apellido_personal'] . '</option>';
      }
    ?>
  </select>
</div-->
      
<div class="col-md-4">
  <label for="filtro_personal">Nombre del personal</label>
  <select name="filtro_personal" id="filtro_personal" class="form-control select2">
    <option value="">Todos los nombres</option>
    <?php
      $conexion = mainModel::conectar();
      $personal = $conexion->query("
        SELECT id_personal, nombre_personal, apellido_personal 
        FROM personal 
        ORDER BY nombre_personal ASC
      ");
      foreach ($personal as $persona) {
          $selected = (isset($_GET['filtro_personal']) && $_GET['filtro_personal'] == $persona['id_personal']) ? "selected" : "";
          echo '<option value="' . $persona['id_personal'] . '" ' . $selected . '>' 
              . $persona['nombre_personal'] . ' ' 
              . $persona['apellido_personal'] . ' - id: ' 
              . $persona['id_personal'] . '</option>';
      }
    ?>
  </select>
</div>
      
    <div class="col-md-2">
      <label for="filtro_ci">Cédula</label>
      <input type="number" name="filtro_ci" id="filtro_ci" min="0" class="form-control" value="<?= $_GET['filtro_ci'] ?? "" ?>">
    </div>

    <div class="col-md-3">
      <label for="filtro_cargo">Cargo</label>
      <select name="filtro_cargo" id="filtro_cargo" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= ($_GET['filtro_cargo'] ?? "") == "1" ? "selected" : "" ?>>Administrador</option>
        <option value="2" <?= ($_GET['filtro_cargo'] ?? "") == "2" ? "selected" : "" ?>>Control de estudio</option>
        <option value="3" <?= ($_GET['filtro_cargo'] ?? "") == "3" ? "selected" : "" ?>>Administración de pagos</option>
        <option value="4" <?= ($_GET['filtro_cargo'] ?? "") == "4" ? "selected" : "" ?>>Supervisor de cursos</option>
      </select>
    </div>

    <div class="col-md-2">
      <label for="filtro_estado">Activo</label>
      <select name="filtro_estado" id="filtro_estado" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= ($_GET['filtro_estado'] ?? "") == "1" ? "selected" : "" ?>>Sí</option>
        <option value="0" <?= ($_GET['filtro_estado'] ?? "") == "0" ? "selected" : "" ?>>No</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="">Sin orden específico</option>
        <option value="nombre" <?= ($_GET['orden'] ?? "") == "nombre" ? "selected" : "" ?>>Nombre (A-Z)</option>
        <option value="fecha" <?= ($_GET['orden'] ?? "") == "fecha" ? "selected" : "" ?>>Más nuevo primero</option>
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
        require_once "./controladores/usuarioControlador3.php";
        $ins_usuario = new usuarioControlador3();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_usuario->paginador_personal_controlador($pagina_actual, 15, "user-personal-list", "");
    ?>
</div>



      
      <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
  $(document).ready(function() {
    $('#filtro_personal').select2({
      placeholder: "Seleccione un nombre",
      allowClear: true
    });
  });
</script>
