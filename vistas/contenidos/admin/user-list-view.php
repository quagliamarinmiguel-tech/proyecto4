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
            <a href="<?php echo SERVERURL; ?>index.php?views=user-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/PERSONAL</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/PERSONAL</a>
        </li>
    </ul>
</div>

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="user-list">
  <div class="form-row align-items-end">

    <!-- Búsqueda general: nombre, apellido, cédula, correo -->
    <div class="col-md-4">
      <label for="busqueda">Buscar</label>
      <input type="text" name="busqueda" id="busqueda" class="form-control"
             placeholder="Nombre, apellido, cédula o correo..."
             value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
    </div>

    <!-- Cargo -->
    <div class="col-md-3">
      <label for="filtro_rol">Cargo</label>
      <select name="filtro_rol" id="filtro_rol" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= ($_GET['filtro_rol'] ?? "") == "1" ? "selected" : "" ?>>Administrador</option>
        <option value="3" <?= ($_GET['filtro_rol'] ?? "") == "3" ? "selected" : "" ?>>Control de estudio</option>
        <option value="2" <?= ($_GET['filtro_rol'] ?? "") == "2" ? "selected" : "" ?>>Administración de pagos</option>
        <option value="4" <?= ($_GET['filtro_rol'] ?? "") == "4" ? "selected" : "" ?>>Supervisor de cursos</option>
      </select>
    </div>

    <!-- Estado -->
    <div class="col-md-2">
      <label for="filtro_state">Estado</label>
      <select name="filtro_state" id="filtro_state" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= ($_GET['filtro_state'] ?? "") == "1" ? "selected" : "" ?>>Activo</option>
        <option value="0" <?= ($_GET['filtro_state'] ?? "") == "0" ? "selected" : "" ?>>Inactivo</option>
      </select>
    </div>

    <!-- Orden -->
    <div class="col-md-2">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="">Sin orden específico</option>
        <option value="nombre" <?= ($_GET['orden'] ?? "") == "nombre" ? "selected" : "" ?>>Nombre (A-Z)</option>
        <option value="reciente" <?= ($_GET['orden'] ?? "") == "reciente" ? "selected" : "" ?>>Más nuevo primero</option>
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
        require_once "./controladores/usuarioControlador.php";
        $ins_usuario = new usuarioControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_usuario->paginador_staff_controlador($pagina_actual, 15, "user-list");
    ?>
</div>
