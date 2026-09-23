<?php
if($_SESSION['cargo_spm'] != 1){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-history fa-fw"></i> &nbsp; SEGUIMIENTO DE ACTIVIDAD
    </h3>
    <p class="text-justify">
        Consulte el historial de acciones realizadas por el personal del sistema.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO USUARIO/PERSONAL</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=user-personal-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE USUARIO/PERSONAL</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=user-activity-list"><i class="fas fa-history fa-fw"></i> &nbsp; ACTIVIDAD</a>
        </li>
    </ul>
</div>

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="user-activity-list">
  <div class="form-row align-items-end">

    <!-- Búsqueda -->
    <div class="col-md-3">
      <label for="busqueda">Buscar usuario</label>
      <input type="text" name="busqueda" id="busqueda" class="form-control"
             placeholder="Nombre, apellido o cédula..."
             value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
    </div>

    <!-- Acción -->
    <div class="col-md-2">
      <label for="filtro_accion">Acción</label>
      <select name="filtro_accion" id="filtro_accion" class="form-control">
        <option value="">Todas</option>
        <option value="INSERT" <?= ($_GET['filtro_accion'] ?? '') == 'INSERT' ? 'selected' : '' ?>>INSERT</option>
        <option value="UPDATE" <?= ($_GET['filtro_accion'] ?? '') == 'UPDATE' ? 'selected' : '' ?>>UPDATE</option>
        <option value="DELETE" <?= ($_GET['filtro_accion'] ?? '') == 'DELETE' ? 'selected' : '' ?>>DELETE</option>
      </select>
    </div>

    <!-- Tabla -->
    <div class="col-md-2">
      <label for="filtro_tabla">Tabla</label>
      <select name="filtro_tabla" id="filtro_tabla" class="form-control">
        <option value="">Todas</option>
        <?php
        $tablas = ['course','marketing_course','period','period_course','student','teacher','registration','transaction','staff','page_info','section','evaluation'];
        foreach ($tablas as $t):
            $selected = ($_GET['filtro_tabla'] ?? '') === $t ? 'selected' : '';
        ?>
            <option value="<?= $t ?>" <?= $selected ?>><?= $t ?></option>
        <?php endforeach; ?>
      </select>
    </div>

    <!-- Fecha desde -->
    <div class="col-md-2">
      <label for="fecha_desde">Desde</label>
      <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
             value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
    </div>

    <!-- Fecha hasta -->
    <div class="col-md-2">
      <label for="fecha_hasta">Hasta</label>
      <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
             value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
    </div>

    <!-- Orden -->
    <div class="col-md-2">
      <label for="orden">Orden</label>
      <select name="orden" id="orden" class="form-control">
        <option value="reciente" <?= ($_GET['orden'] ?? '') == 'reciente' ? 'selected' : '' ?>>Más reciente</option>
        <option value="antiguo" <?= ($_GET['orden'] ?? '') == 'antiguo' ? 'selected' : '' ?>>Más antiguo</option>
      </select>
    </div>

    <!-- Botón -->
    <div class="col-md-12 mt-3">
      <button type="submit" class="btn btn-primary">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
      <a href="<?= SERVERURL ?>index.php?views=user-activity-list" class="btn btn-secondary">
        <i class="fas fa-eraser"></i> Limpiar
      </a>
    </div>
  </div>
</form>

<div class="container-fluid">
    <?php
        require_once "./controladores/usuarioControlador.php";
        $ins_usuario = new usuarioControlador();
        $pagina_actual = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        echo $ins_usuario->paginador_actividad_controlador($pagina_actual, 15, "user-activity-list");
    ?>
</div>