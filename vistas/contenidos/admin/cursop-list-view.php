<?php 
    $cargo   = $_SESSION['cargo_spm'] ?? 0;
	$state   = $_SESSION['profile_status'] ?? 0;
    $carpeta =  "admin";

    if (!in_array($cargo, [1, 4]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-bullhorn fa-fw"></i> &nbsp; LISTA DE PUBLICIDADES DE CURSO
    </h3>
    <p class="text-justify">
        A continuación se muestran las publicidades activas asociadas a cursos registrados. Puedes actualizar sus datos, visualizar la imagen o eliminarlas si ya no son necesarias.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cursop-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA PUBLICIDAD</a>
        
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cursop-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PUBLICIDADES</a>

        </li>
        
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cursop-list-archived'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PUBLICIDADES ARCHIVADAS</a>

        </li>
    </ul>
</div>



<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="cursop-list">
  <div class="form-row align-items-end">

    <!-- ID de publicidad -->
    <div class="col-md-2">
      <label for="filtro_id">ID de publicidad</label>
      <input type="number" min="0" name="filtro_id" id="filtro_id" class="form-control" value="<?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div>

    <!-- Nombre del curso -->
    <div class="col-md-4">
        
      <label for="busqueda">Nombre del curso</label>
      <input type="text" name="busqueda" id="busqueda" class="form-control" max="60" placeholder="Escriba parte del nombre..." value="<?= isset($_GET['busqueda']) ? htmlspecialchars($_GET['busqueda']) : '' ?>">
        
        
    </div>

    <!-- Tipo de curso -->
    <div class="col-md-3">
      <label for="filtro_tipo">Tipo de curso</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos los tipos</option>
        <option value="Intensivo" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Intensivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
      </select>
    </div>

    <!-- Estado del curso --
    <div class="col-md-3">
      <label for="estado">Estado del curso</label>
      <select name="estado" id="estado" class="form-control">
        <option value="">Todos</option>
        <option value="Vigente" <c?= (isset($_GET['estado']) && $_GET['estado'] == "Vigente") ? "selected" : "" ?>>Vigente</option>
        <option value="Próximo" <c?= (isset($_GET['estado']) && $_GET['estado'] == "Próximo") ? "selected" : "" ?>>Próximo</option>
        <option value="No vigente" <c?= (isset($_GET['estado']) && $_GET['estado'] == "No vigente") ? "selected" : "" ?>>No vigente</option>
      </select>
    </div-->

    <!-- Ordenar por -->
    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="" <?= (isset($_GET['orden']) && $_GET['orden'] == "") ? "selected" : "" ?>>Sin orden específico</option>
        <option value="reciente" <?= (isset($_GET['orden']) && $_GET['orden'] == "reciente") ? "selected" : "" ?>>Más nuevo primero</option>
        <option value="nombre" <?= (isset($_GET['orden']) && $_GET['orden'] == "nombre") ? "selected" : "" ?>>Nombre del curso (A-Z)</option>
        <option value="interacciones" <?= (isset($_GET['orden']) && $_GET['orden'] == "interacciones") ? "selected" : "" ?>>Más clics en publicidad</option>
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
        require_once "./controladores/cursoPublicidadControlador.php";
        $ins_publicidad = new cursoPublicidadControlador();
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        echo $ins_publicidad->paginador_publicidad_controlador($pagina, 10, "cursop-list", 1);
    ?>
</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
