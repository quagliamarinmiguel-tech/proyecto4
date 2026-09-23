<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 && $_SESSION['cargo_spm']!=3 && $_SESSION['cargo_spm']!=2 ){
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





$course_id = null;
$period_id = null;

if (isset($_GET['origen']) && $_GET['origen'] === 'curso' && isset($_GET['filtro_id_curso'])) {
    $course_id = (int) $_GET['filtro_id_curso'];
} elseif (isset($_GET['origen']) && $_GET['origen'] === 'periodo' && isset($_GET['filtro_id_periodo'])) {
    $period_id = (int) $_GET['filtro_id_periodo'];
}

require_once "./controladores/periodoControlador.php";
$ins_periodo = new periodoControlador();


// Inicializar variables para el encabezado
$headerInfo = null;
$headerOrigen = '';

if ($course_id !== null) {
    // Obtener datos del curso
    $resultado = $ins_periodo->obtener_curso_por_id_controlador($course_id);
    if ($resultado['ok'] && !empty($resultado['data'][0])) {
        $curso = $resultado['data'][0];
        $headerInfo = [
            'titulo' => 'Periodos del curso',
            'nombre' => $curso['name'],
            'tipo'   => $curso['type'],
            'id'     => $curso['id']
        ];
        $headerOrigen = 'curso';
    }
} elseif ($period_id !== null) {
    // Obtener datos del periodo
    $resultado = $ins_periodo->obtener_periodo_por_id_controlador($period_id);
    if ($resultado['ok'] && !empty($resultado['data'][0])) {
        $periodo = $resultado['data'][0];
        $headerInfo = [
            'titulo' => 'Cursos vigentes del periodo',
            'nombre' => $periodo['name'],
            'tipo'   => $periodo['type'],
            'id'     => $periodo['id']
        ];
        $headerOrigen = 'periodo';
    }
}

// Mostrar encabezado dinámico
if ($headerInfo !== null) {
    echo '
    <div class="full-box page-header">
        <h3 class="text-left">
            <i class="fas fa-layer-group fa-fw"></i> &nbsp; ' . $headerInfo['titulo'] . ' <strong>' . htmlspecialchars($headerInfo['nombre']) . '</strong> <em>(' . htmlspecialchars($headerInfo['tipo']) . ')</em>
        </h3>
        <p class="text-justify">
            Mostrando los period-cursos asociados a este ' . ($headerOrigen === 'curso' ? 'curso' : 'periodo') . ' archivado.
        </p>
    </div>';
} else {
    // Texto por defecto (cuando no hay origen)
    echo '
    <div class="full-box page-header">
        <h3 class="text-left">
            <i class="fas fa-layer-group fa-fw"></i> &nbsp; Periodos de cursos y secciones archivados
        </h3>
        <p class="text-justify">
            Los periodos de cursos determinan qué cursos estuvieron vigentes en qué periodos. Desde aquí pueden visualizarse los periodos de cada curso y sus secciones archivadas
        </p>
    </div>';
}


?>
<style>
    .badge-success { background-color: #28a745; color: #fff; }
    .badge-primary { background-color: #007bff; color: #fff; }
    .badge-danger  { background-color: #dc3545; color: #fff; }
    .badge-secondary { background-color: #6c757d; color: #fff; }
</style>















<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-layer-group fa-fw"></i> &nbsp; LISTA DE PERIODOS DE CURSOS ARCHIVADOS</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-list'; ?>"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PERIODOS</a>
        </li>
    </ul>   
</div>

<form method="GET" class="mb-4">
    <input type="hidden" name="views" value="period-course-list-archived">

    <div class="card shadow-sm mb-3">
        <div class="card-header bg-dark text-white">
            <i class="fas fa-filter"></i> &nbsp; Filtros de búsqueda
        </div>
        <div class="card-body">

            <div class="form-row">
                <!-- ID Period-Curso -->
                <div class="col-md-2">
                    <label for="filtro_id">ID Period-Curso</label>
                    <input type="number" min="0" name="filtro_id" id="filtro_id" class="form-control" value="<?php echo $_GET['filtro_id'] ?? ''; ?>">
                </div>

                <!-- Nombre del Periodo (búsqueda) -->
                <div class="col-md-4">
                    <label for="busqueda_periodo">Nombre del periodo</label>
                    <input type="text" name="busqueda_periodo" id="busqueda_periodo" class="form-control" value="<?php echo htmlspecialchars($_GET['busqueda_periodo'] ?? ''); ?>" placeholder="Ej: Periodo 2025-1">
                </div>

                <!-- Nombre del Curso (búsqueda) -->
                <div class="col-md-4">
                    <label for="busqueda_curso">Nombre del curso</label>
                    <input type="text" name="busqueda_curso" id="busqueda_curso" class="form-control" value="<?php echo htmlspecialchars($_GET['busqueda_curso'] ?? ''); ?>" placeholder="Ej: Matemáticas">
                </div>
            </div>

            <hr>

            <div class="form-row">
                <!-- Estado del Periodo -->
                <div class="col-md-3">
                    <label for="filtro_estado">Estado del periodo</label>
                    <select name="filtro_estado" id="filtro_estado" class="form-control">
                        <option value="">Todos</option>
                        <option value="Próximo" <?php echo ($_GET['filtro_estado'] ?? '') == 'Próximo' ? 'selected' : ''; ?>>Próximo</option>
                        <option value="Vigente" <?php echo ($_GET['filtro_estado'] ?? '') == 'Vigente' ? 'selected' : ''; ?>>Vigente</option>
                        <option value="Terminado" <?php echo ($_GET['filtro_estado'] ?? '') == 'Terminado' ? 'selected' : ''; ?>>Terminado</option>
                    </select>
                </div>

                <!-- Orden -->
                <div class="col-md-3">
                    <label for="orden">Ordenar por</label>
                    <select name="orden" id="orden" class="form-control">
                        <option value="periodo_desc" <?php echo ($_GET['orden'] ?? '') == 'periodo_desc' ? 'selected' : ''; ?>>Periodo (más reciente)</option>
                        <option value="periodo_asc" <?php echo ($_GET['orden'] ?? '') == 'periodo_asc' ? 'selected' : ''; ?>>Periodo (A-Z)</option>
                        <option value="curso_asc" <?php echo ($_GET['orden'] ?? '') == 'curso_asc' ? 'selected' : ''; ?>>Curso (A-Z)</option>
                        <option value="fecha_asc" <?php echo ($_GET['orden'] ?? '') == 'fecha_asc' ? 'selected' : ''; ?>>Fecha inicio (asc)</option>
                        <option value="fecha_desc" <?php echo ($_GET['orden'] ?? '') == 'fecha_desc' ? 'selected' : ''; ?>>Fecha inicio (desc)</option>
                    </select>
                </div>

                <div class="col-md-3 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-filter"></i> Aplicar filtros</button>
                </div>
            </div>

        </div>
    </div>
</form>

<div class="container-fluid">
    <?php
 
        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // state = 1 (activos) para esta vista. Para archivados, pasar state=0 en otra vista.
        echo $ins_periodo->paginador_period_course_controlador(
            $pagina_actual,
            15,
            'period-course-list-archived',
            $_GET['busqueda_periodo'] ?? '',
            $_GET['busqueda_curso'] ?? '',
            0,
            $course_id,
    		$period_id
        );
    ?>
</div>

<!-- Scripts opcionales (select2 ya no se usa, pero se mantienen por si se necesitan en otros lados) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

