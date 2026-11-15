<?php 
    $state = $_SESSION['profile_status'] ?? 0;
	$cargo = $_SESSION['cargo_spm'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1, 2]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-user-plus fa-fw"></i> &nbsp; LISTA DE SOLICITUDES DE NUEVO INGRESO
    </h3>
    <p class="text-justify">
        A continuación se muestran las solicitudes de nuevo ingreso pendientes de revisión. Puedes verificar los datos del estudiante, la captura del pago y aprobar o rechazar la solicitud.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/solicitud-nuevo-ingreso-list'; ?>">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE SOLICITUDES
            </a>
        </li>
    </ul>
</div>


<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="<?= urlencode($carpeta) ?>/solicitud-nuevo-ingreso-list">

  <div class="form-row align-items-end">

    <div class="col-md-4">
      <label for="busqueda">Buscar</label>
      <input type="text" name="busqueda" id="busqueda" class="form-control"
             maxlength="80" placeholder="Nombre, apellido, cédula o curso..."
             value="<?= htmlspecialchars($_GET['busqueda'] ?? '') ?>">
    </div>

    <div class="col-md-3">
      <label for="estado">Estado</label>
      <select name="estado" id="estado" class="form-control">
        <?php
          // Default: 2 (Pendiente) si no viene nada en la URL
          $estadoActual = $_GET['estado'] ?? '2';
        ?>
        <option value="2" <?= $estadoActual === '2' ? 'selected' : '' ?>>Pendiente</option>
        <option value="3" <?= $estadoActual === '3' ? 'selected' : '' ?>>Rechazada</option>
        <option value="all" <?= $estadoActual === 'all' ? 'selected' : '' ?>>Todos los estados</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="reciente" <?= ($_GET['orden'] ?? 'reciente') === 'reciente' ? 'selected' : '' ?>>Más reciente primero</option>
        <option value="apellido" <?= ($_GET['orden'] ?? '') === 'apellido' ? 'selected' : '' ?>>Apellido (A-Z)</option>
        <option value="curso"    <?= ($_GET['orden'] ?? '') === 'curso'    ? 'selected' : '' ?>>Nombre del curso (A-Z)</option>
      </select>
    </div>

    <div class="col-md-2">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Filtrar
      </button>
    </div>

  </div>

  <div class="form-row align-items-end mt-2">

    <div class="col-md-3">
      <label for="fecha_desde">Fecha desde</label>
      <input type="date" name="fecha_desde" id="fecha_desde" class="form-control"
             value="<?= htmlspecialchars($_GET['fecha_desde'] ?? '') ?>">
    </div>

    <div class="col-md-3">
      <label for="fecha_hasta">Fecha hasta</label>
      <input type="date" name="fecha_hasta" id="fecha_hasta" class="form-control"
             value="<?= htmlspecialchars($_GET['fecha_hasta'] ?? '') ?>">
    </div>

  </div>
</form>




<div class="container-fluid">
    <?php
        require_once "./controladores/solicitudControlador.php";
        $ins_solicitud = new solicitudControlador();
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        echo $ins_solicitud->paginador_solicitud_inscripcion_controlador($pagina, 15, "solicitud-nuevo-ingreso-list");
    ?>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

