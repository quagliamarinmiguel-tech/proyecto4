<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }





$cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";




?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; COHORTES ARCHIVADAS
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todas las cohortes archivadas en el sistema y restaurarlas si es necesario.
    </p>
</div>

<!--
    <li><a href="<php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a></li>
        <li><a href="<php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES</a></li>
        <li><a class="active" href="<php echo SERVERURL; ?>index.php?views=cohorte-archived"><i class="fas fa-archive fa-fw"></i> &nbsp; COHORTES ARCHIVADAS</a></li>
   
-->

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
    <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a></li>
    <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES</a></li>
	<li><a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-archived'; ?>"><i class="fas fa-archive fa-fw"></i> &nbsp; COHORTES ARCHIVADAS</a></li>    
    </ul>   
</div>

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="cohorte-archived">

  <div class="form-row align-items-end">
    <div class="col-md-2">
      <label for="filtro_id">ID de cohorte</label>
      <input type="number" min="0" name="filtro_id" id="filtro_id" class="form-control" min="1" value="<?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div>

    <div class="col-md-2">
      <label for="filtro_anio">Año de cohorte</label>
      <input type="number" name="filtro_anio" id="filtro_anio" class="form-control" min="24" max="40" value="<?= isset($_GET['filtro_anio']) ? $_GET['filtro_anio'] : "" ?>">
    </div>

    <div class="col-md-3">
      <label for="filtro_tipo">Tipo de cohorte</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
      </select>
    </div>

    <!--div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="normal" <?= (!isset($_GET['orden']) || $_GET['orden'] == "normal") ? "selected" : "" ?>>Más reciente</option>
        <option value="cursos" <?= (isset($_GET['orden']) && $_GET['orden'] == "cursos") ? "selected" : "" ?>>Cantidad de cursos</option>
        <option value="estudiantes" <?= (isset($_GET['orden']) && $_GET['orden'] == "estudiantes") ? "selected" : "" ?>>Cantidad de estudiantes</option>
      </select>
    </div-->

    <div class="col-md-2">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form>

<div class="container-fluid">
    <?php
        require_once "./controladores/cohorteControlador.php";
        $ins_cohorte = new cohorteControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_cohorte->paginador_cohorte_archivado_controlador($pagina_actual, 15, "cohorte-archived", "");
    ?>
</div>