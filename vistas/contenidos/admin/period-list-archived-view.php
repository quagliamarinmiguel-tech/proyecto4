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
        <i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PERIODOS
    </h3>
    <p class="text-justify">
        Visualiza todos los periodos archivados en el sistema. Solo pueden archivarse cursos sin inscritos.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <?php if (in_array($cargo, [1, 4])): ?>
        	<li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-new'; ?>"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO PERIODO</a></li>
        <?php endif; ?>
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS</a></li>
        <li><a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-list-archived'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS ARCHIVADOS</a></li>
    </ul>
</div>


<form method="GET" class="mb-4">
    <input type="hidden" name="views" value="period-list-archived">

    <div class="form-row align-items-end">
        <!-- ID -->
        <div class="col-md-2">
            <label for="filtro_id">ID del periodo</label>
            <input type="number" min="0" name="filtro_id" id="filtro_id" class="form-control" value="<?= $_GET['filtro_id'] ?? '' ?>">
        </div>

        <!-- Nombre (búsqueda general) -->
        <div class="col-md-3">
            <label for="busqueda">Nombre o tipo</label>
            <input type="text" name="busqueda" id="busqueda" class="form-control" value="<?= $_GET['busqueda'] ?? '' ?>" placeholder="Buscar...">
        </div>

        <!-- Año -->
        <div class="col-md-2">
            <label for="filtro_anio">Año del periodo</label>
            <select name="filtro_anio" id="filtro_anio" class="form-control">
                <option value="">Todos</option>
                <?php for ($anio = 2020; $anio <= 2050; $anio++): ?>
                    <option value="<?= $anio ?>" <?= ($_GET['filtro_anio'] ?? '') == $anio ? 'selected' : '' ?>><?= $anio ?></option>
                <?php endfor; ?>
            </select>
        </div>

        <!-- Tipo -->
        <div class="col-md-2">
            <label for="filtro_tipo">Tipo</label>
            <select name="filtro_tipo" id="filtro_tipo" class="form-control">
                <option value="">Todos</option>
                <option value="Intensivo" <?= ($_GET['filtro_tipo'] ?? '') == "Intensivo" ? "selected" : "" ?>>Intensivo</option>
                <option value="Extendido" <?= ($_GET['filtro_tipo'] ?? '') == "Extendido" ? "selected" : "" ?>>Extendido</option>
                <option value="Técnico Profesional" <?= ($_GET['filtro_tipo'] ?? '') == "Técnico Profesional" ? "selected" : "" ?>>Técnico Profesional</option>
            </select>
        </div>

        <!-- Estado -->
        <div class="col-md-2">
            <label for="filtro_estado">Estado</label>
            <select name="filtro_estado" id="filtro_estado" class="form-control">
                <option value="">Todos</option>
                <option value="Próximo" <?= ($_GET['filtro_estado'] ?? '') == "Próximo" ? "selected" : "" ?>>Próximo</option>
                <option value="Vigente" <?= ($_GET['filtro_estado'] ?? '') == "Vigente" ? "selected" : "" ?>>Vigente</option>
                <option value="Terminado" <?= ($_GET['filtro_estado'] ?? '') == "Terminado" ? "selected" : "" ?>>Terminado</option>
            </select>
        </div>

        <!-- Orden -->
        <div class="col-md-2">
            <label for="orden">Ordenar por</label>
            <select name="orden" id="orden" class="form-control">
                <option value="id_desc" <?= ($_GET['orden'] ?? '') == "id_desc" ? "selected" : "" ?>>Más reciente</option>
                <option value="fecha_asc" <?= ($_GET['orden'] ?? '') == "fecha_asc" ? "selected" : "" ?>>Fecha inicio (asc)</option>
                <option value="fecha_desc" <?= ($_GET['orden'] ?? '') == "fecha_desc" ? "selected" : "" ?>>Fecha inicio (desc)</option>
                <option value="name_asc" <?= ($_GET['orden'] ?? '') == "name_asc" ? "selected" : "" ?>>Nombre (A-Z)</option>
                <option value="name_desc" <?= ($_GET['orden'] ?? '') == "name_desc" ? "selected" : "" ?>>Nombre (Z-A)</option>
            </select>
        </div>

        <div class="col-md-1 d-flex align-items-end">
            <button type="submit" class="btn btn-primary btn-block mt-2"><i class="fas fa-filter"></i></button>
        </div>
    </div>
</form>


<!-- Lista de periodos -->
<div class="container-fluid">
    <?php
        require_once "./controladores/periodoControlador.php";
        $ins_periodo = new periodoControlador();
        $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        echo $ins_periodo->paginador_periodo_controlador($pagina, 15, 'period-list-archived', $_GET['busqueda'] ?? '', 0);
    ?>
</div>
