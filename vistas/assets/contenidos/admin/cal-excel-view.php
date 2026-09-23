<?php
$cargo = $_SESSION['cargo_spm'] ?? 0;

if (!in_array($cargo, [1, 2, 3, 4, 5])) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

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
        <i class="fas fa-file-excel fa-fw"></i> &nbsp; CALIFICACIONES LISTAS PARA EXCEL
    </h3>
    <p class="text-justify">
        Visualiza las secciones que ya tienen todas sus evaluaciones completadas y pueden generar el Excel de calificaciones.
    </p>
</div>

<!--div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a class="active" href="<php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cal-excel'; ?>">
                <i class="fas fa-file-excel fa-fw"></i> &nbsp; Secciones evaluadas
            </a>
        </li>
    </ul>
</div-->

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="cal-excel">

  <div class="form-row align-items-end">
    <div class="col-md-2">
      <label for="filtro_id">ID de cohorte</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= $_GET['filtro_id'] ?? "" ?>">
    </div>

    <div class="col-md-2">
      <label for="filtro_tipo">Tipo de cohorte</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos</option>
        <option value="Intensivo" <?= ($_GET['filtro_tipo'] ?? "") == "Intensivo" ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= ($_GET['filtro_tipo'] ?? "") == "Extendido" ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= ($_GET['filtro_tipo'] ?? "") == "Técnico Profesional" ? "selected" : "" ?>>Técnico Profesional</option>
      </select>
    </div>

    <div class="col-md-2">
      <label for="filtro_anio">Año de cohorte</label>
      <input type="number" name="filtro_anio" id="filtro_anio" class="form-control" min="25" max="40" value="<?= $_GET['filtro_anio'] ?? "" ?>">
    </div>

    <div class="col-md-2">
      <label for="filtro_estado">Estado de cohorte</label>
      <select name="filtro_estado" id="filtro_estado" class="form-control">
        <option value="">Todos</option>
        <option value="Vigente" <?= ($_GET['filtro_estado'] ?? "") == "Vigente" ? "selected" : "" ?>>Vigente</option>
        <option value="Próximo" <?= ($_GET['filtro_estado'] ?? "") == "Próximo" ? "selected" : "" ?>>Próximo</option>
        <option value="Terminado" <?= ($_GET['filtro_estado'] ?? "") == "Terminado" ? "selected" : "" ?>>Terminado</option>
      </select>
    </div>

    <div class="col-md-2">
      <label for="filtro_evaluado">Evaluado al 100%</label>
      <select name="filtro_evaluado" id="filtro_evaluado" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= ($_GET['filtro_evaluado'] ?? "") == "1" ? "selected" : "" ?>>Sí</option>
        <option value="0" <?= ($_GET['filtro_evaluado'] ?? "") == "0" ? "selected" : "" ?>>No</option>
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
        require_once "./controladores/seccionControlador.php";
        $ins_cohorte = new seccionControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_cohorte->paginador_secciones_excel_controlador($pagina_actual, 15);
    ?>
</div>


    <a href="http://www.cecilioceproca.wuaze.com/exportadores/exportar-excel-phpspreadsheet2_7.php?id_cohorte=47" target="_blank">
    	<button class="btn btn-success">Descargar Excel (.xlsx)</button>
    </a>



<form class="queriaponeralgoaqui" method="GET" action="<?php echo SERVERURL; ?>exportadores/exportar-excel-phpspreadsheet2.php" class="mb-4">
  <div class="form-row align-items-end">
    <div class="col-md-6">
      <label for="id_cohorte">Selecciona cohorte para generar Excel</label>
      <select name="id_cohorte" id="id_cohorte" class="form-control" required>
        <option value="">-- Selecciona --</option>
        <?php
          require_once "./controladores/seccionControlador.php";
          $cohortes = seccionControlador::cohortes_para_excel_select();
          foreach ($cohortes as $c) {
              $tipo = ($c['tipo_cohorte'] === 'Intensivo') ? 'Int' :
                      (($c['tipo_cohorte'] === 'Extendido') ? 'Ext' : 'Tec');
              $label = "Co {$c['numero_cohorte']}_{$c['anio_cohorte']} $tipo";
              echo "<option value=\"{$c['id_cohorte']}\">$label</option>";
          }
        ?>
      </select>
    </div>
    <div class="col-md-2">
      <button type="submit" class="btn btn-success btn-block">
        <i class="fas fa-file-excel"></i> Generar Excel de calificaciones de un cohorte
      </button>
    </div>
  </div>
</form>









