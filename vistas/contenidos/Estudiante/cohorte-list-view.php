<!--php 
   // if(_SESSION['cargo_spm']!=1 && _SESSION['cargo_spm']!=4 ){
  //      echo $lc->forzar_cierre_sesion_controlador();
 //       exit();
//    }
?-->
<?php  
    $cargo = $_SESSION['cargo_spm'] ?? 0;

    if (!in_array($cargo, [1, 2, 3, 4, 5])) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>
    
    
<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES
    </h3>
    <p class="text-justify">
        Visualiza todas las cohortes registradas en el sistema. Puedes editarlas, ver los cursos que tienen asignados o archivarlas.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        
        <?php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a>
        </li>
        <?php endif; ?>

        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES</a>
        </li>
        <!--li>
            <a href="<php echo SERVERURL; ?>index.php?views=cohorte-search"><i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR COHORTE</a>
        </li-->
    </ul>
</div>


<!--div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a  href="<?php echo SERVERURL; ?>index.php?views=cohorte-terminado"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; COHORTES TERMINADOS</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; COHORTES VIGENTES</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-proximo"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; COHORTES PRÓXIMOS</a>
        </li>
    </ul>
</div-->


<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="cohorte-list">
  <input type="hidden" name="estado" value="Vigente">

  <div class="form-row align-items-end">
    <div class="col-md-2">
      <label for="filtro_id">ID de cohorte</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div>

    <div class="col-md-2">
      <label for="filtro_tipo">Tipo de cohorte</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos</option>
        <option value="Intensivo" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Intensivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
        <option value="Otro" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Otro") ? "selected" : "" ?>>Otro</option>
      </select>
    </div>

    <div class="col-md-2">
      <label for="filtro_anio">Año de cohorte</label>
      <input type="number" name="filtro_anio" id="filtro_anio" class="form-control" min="25" max="40" value="<?= isset($_GET['filtro_anio']) ? $_GET['filtro_anio'] : "" ?>">
    </div>

    <div class="col-md-2">
      <label for="filtro_estado">Estado de cohorte</label>
      <select name="filtro_estado" id="filtro_estado" class="form-control">
        <option value="">Todos</option>
        <option value="Vigente" <?= (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == "Vigente") ? "selected" : "" ?>>Vigente</option>
        <option value="Próximo" <?= (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == "Próximo") ? "selected" : "" ?>>Próximo</option>
        <option value="Terminado" <?= (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == "Terminado") ? "selected" : "" ?>>Terminado</option>
      </select>
    </div>

    <div class="col-md-2">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="normal" <?= (!isset($_GET['orden']) || $_GET['orden'] == "normal") ? "selected" : "" ?>>Más reciente</option>
        <option value="cursos" <?= (isset($_GET['orden']) && $_GET['orden'] == "cursos") ? "selected" : "" ?>>Cantidad de cursos</option>
        <option value="estudiantes" <?= (isset($_GET['orden']) && $_GET['orden'] == "estudiantes") ? "selected" : "" ?>>Cantidad de estudiantes</option>
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
        require_once "./controladores/cohorteControlador.php";
        $ins_cohorte = new cohorteControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_cohorte->paginador_cohorte_controlador($pagina_actual, 15, "cohorte-list", "");
    ?>
</div>