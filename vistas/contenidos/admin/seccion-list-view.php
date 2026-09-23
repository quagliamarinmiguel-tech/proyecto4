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
            Mostrando los period-cursos asociados a este ' . ($headerOrigen === 'curso' ? 'curso' : 'periodo') . '.
        </p>
    </div>';
} else {
    // Texto por defecto (cuando no hay origen)
    echo '
    <div class="full-box page-header">
        <h3 class="text-left">
            <i class="fas fa-layer-group fa-fw"></i> &nbsp; Periodos de cursos y secciones
        </h3>
        <p class="text-justify">
            Los periodos de cursos determinan qué cursos estuvieron vigentes en qué periodos. Desde aquí pueden visualizarse los periodos de cada curso y sus secciones.
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















<!--div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-layer-group fa-fw"></i> &nbsp; Periodos de cursos y secciones
    </h3>
    <p class="text-justify">
        Los periodos de cursos determinan qué cursos estuvieron vigentes en qué periodos. Desde aquí pueden visualizarse los periodos de cada curso y sus secciones.
    </p>
</div-->

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-layer-group fa-fw"></i> &nbsp; LISTA DE PERIODOS DE CURSOS</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-list'; ?>"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE PERIODOS</a>
        </li>
    </ul>   
</div>

<form method="GET" class="mb-4">
    <input type="hidden" name="views" value="seccion-list">

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
            'seccion-list',
            $_GET['busqueda_periodo'] ?? '',
            $_GET['busqueda_curso'] ?? '',
            1,
            $course_id,
    		$period_id
        );
    ?>
</div>

<!-- Scripts opcionales (select2 ya no se usa, pero se mantienen por si se necesitan en otros lados) -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>




<!--
<php  
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


?>


<php
$origen = $_GET['origen'] ?? '';
$conexion = mainModel::conectar();

if ($origen === 'curso' && isset($_GET['filtro_nombre'])) {
    $idCurso = mainModel::limpiar_cadena($_GET['filtro_nombre']);
    $info = $conexion->prepare("SELECT nombre_curso, tipo_curso FROM curso WHERE id_curso = :id");
    $info->bindParam(":id", $idCurso, PDO::PARAM_STR);
    $info->execute();

    if ($info->rowCount() > 0) {
        $curso = $info->fetch();
        echo '
        <div class="full-box page-header">
            <h3 class="text-left">
                <i class="fas fa-layer-group fa-fw"></i> &nbsp; periodos del curso <strong>' . htmlspecialchars($curso['nombre_curso']) . '</strong> <em>(' . htmlspecialchars($curso['tipo_curso']) . ')</em>
            </h3>
        </div>';
    }
} elseif ($origen === 'cohorte' && isset($_GET['filtro_id_cohorte'])) {
    $idCohorte = mainModel::limpiar_cadena($_GET['filtro_id_cohorte']);
    $info = $conexion->prepare("SELECT numero_cohorte, anio_cohorte, tipo_cohorte FROM cohorte WHERE id_cohorte = :id");
    $info->bindParam(":id", $idCohorte, PDO::PARAM_INT);
    $info->execute();

    if ($info->rowCount() > 0) {
        $cohorte = $info->fetch();
        echo '
        <div class="full-box page-header">
            <h3 class="text-left">
                <i class="fas fa-layer-group fa-fw"></i> &nbsp; Cursos vigentes del cohorte <strong>' . $cohorte['numero_cohorte'] . '.' . $cohorte['anio_cohorte'] . '</strong> <em>(' . htmlspecialchars($cohorte['tipo_cohorte']) . ')</em>
            </h3>
        </div>';
    }
      
} elseif ($origen === 'profe' && isset($_GET['filtro_profesor'])) {
    $idProfesor = mainModel::limpiar_cadena($_GET['filtro_profesor']);
    $info = $conexion->prepare("SELECT nombre_profesor, apellido_profesor FROM profesor WHERE id_profesor = :id");
    $info->bindParam(":id", $idProfesor, PDO::PARAM_STR);
    $info->execute();

    if ($info->rowCount() > 0) {
        $prof = $info->fetch();
        echo '
        <div class="full-box page-header">
            <h3 class="text-left">
                <i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; Clases impartidas por <strong>' . htmlspecialchars($prof['nombre_profesor'] . ' ' . $prof['apellido_profesor']) . '</strong>
            </h3>
        </div>';
    }
} else {
    // Texto por defecto
    echo '
    <div class="full-box page-header">
        <h3 class="text-left">
            <i class="fas fa-layer-group fa-fw"></i> &nbsp; Periodos de cursos y secciones
        </h3>
        <p class="text-justify">
            Los periodos de cursos determinan qué cursos estuvieron vigentes en qué cohortes. Desde aquí pueden visualizarse los periodos de cada curso y sus secciones.
        </p>
    </div>';
}
?>


<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS </a>
        </li>

        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS DE COHORTES </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list'; ?>"><i class="fas fa-bolt fa-fw"></i> &nbsp; LISTA DE PERIODOS INTENSIVOS </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES </a>
        </li>

    </ul>   
</div>




<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="seccion-list">
  <input type="hidden" name="tipo" value="Vigente">

  <div class="card shadow-sm mb-3">
    <div class="card-header bg-dark text-white">
      <i class="fas fa-filter"></i> &nbsp; Filtros de búsqueda
    </div>
    <div class="card-body">

      <div class="form-row">
        <div class="col-md-2">
          <label for="filtro_id">ID periodo de curso</label>
          <input type="number" min="0" name="filtro_id" id="filtro_id" class="form-control" value="<?= $_GET['filtro_id'] ?? '' ?>">
        </div>
        <div class="col-md-2">
          <label for="filtro_id_seccion">ID sección</label>
          <input type="number" min="0" name="filtro_id_seccion" id="filtro_id_seccion" class="form-control" value="<?= $_GET['filtro_id_seccion'] ?? '' ?>">
        </div>
        <div class="col-md-2">
          <label for="filtro_id_cohorte">ID cohorte</label>
          <input type="number" min="0" name="filtro_id_cohorte" id="filtro_id_cohorte" class="form-control" value="<?= $_GET['filtro_id_cohorte'] ?? '' ?>">
        </div>
      </div>

      <hr>

      <div class="form-row">
        <div class="col-md-6">
          <label for="filtro_nombre">Nombre del curso</label>
            
            ;?php
                $curso_id = $_GET['filtro_nombre'] ?? '';
                $curso_nombre = '';

                if ($curso_id !== '') {
                    $stmt = $conexion->prepare("SELECT nombre_curso, tipo_curso FROM curso WHERE id_curso = :id");
                    $stmt->execute([':id' => $curso_id]);
                    $curso = $stmt->fetch();
                    if ($curso) {
                        $curso_nombre = $curso['nombre_curso'] . ' (' . $curso['tipo_curso'] . ')';
                    }
                }
            ?>
            
            <select name="filtro_nombre" id="filtro_nombre" class="form-control select2-ajax">
              <w?php if ($curso_id && $curso_nombre): ?>
                <option value="<w?= $curso_id ?>" selected><?= htmlspecialchars($curso_nombre) ?></option>
              <w?php endif; ?>
            </select>
            
        </div>

        <div class="col-md-6">
          <label for="filtro_profesor">Profesor</label>
            
          ?php
            $profesor_id = $_GET['filtro_profesor'] ?? '';
            $profesor_nombre = '';

            if ($profesor_id !== '') {
                $stmt = $conexion->prepare("SELECT nombre_profesor, apellido_profesor FROM profesor WHERE id_profesor = :id");
                $stmt->execute([':id' => $profesor_id]);
                $prof = $stmt->fetch();
                if ($prof) {
                    $profesor_nombre = $prof['nombre_profesor'] . ' ' . $prof['apellido_profesor'];
                }
            }
            ?>  
            
            <select name="filtro_profesor" id="filtro_profesor" class="form-control select2-ajax">
              <php if ($profesor_id && $profesor_nombre): ?>
                <option value="<?= $profesor_id ?>" selected><?= htmlspecialchars($profesor_nombre) ?></option>
              <g?php endif; ?>
            </select>
        
          
          </div>
      </div>

      <hr>

      <div class="form-row">
        <div class="col-md-4">
          <label for="filtro_tipo">Tipo de cohorte</label>
          <select name="filtro_tipo" id="filtro_tipo" class="form-control">
            <option value="">Todos</option>
            <option value="Intensivo" <?= ($_GET['filtro_tipo'] ?? '') == "Intensivo" ? "selected" : "" ?>>Intensivo</option>
            <option value="Extendido" <?= ($_GET['filtro_tipo'] ?? '') == "Extendido" ? "selected" : "" ?>>Extendido</option>
            <option value="Técnico Profesional" <?= ($_GET['filtro_tipo'] ?? '') == "Técnico Profesional" ? "selected" : "" ?>>Técnico Profesional</option>
            </select>
        </div>

        <div class="col-md-4">
          <label for="filtro_estado">Estado de cohorte</label>
          <select name="filtro_estado" id="filtro_estado" class="form-control">
            <option value="">Todos</option>
            <option value="Vigente" <?= ($_GET['filtro_estado'] ?? '') == "Vigente" ? "selected" : "" ?>>Vigente</option>
            <option value="Próximo" <?= ($_GET['filtro_estado'] ?? '') == "Próximo" ? "selected" : "" ?>>Próximo</option>
            <option value="Terminado" <?= ($_GET['filtro_estado'] ?? '') == "Terminado" ? "selected" : "" ?>>Terminado</option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="filtro_anio">Año de cohorte</label>
          <select name="filtro_anio" id="filtro_anio" class="form-control">
            <option value="">Todos</option>
            <5?php
            for ($anio = 25; $anio <= 50; $anio++) {
              $selected = ($_GET['filtro_anio'] ?? '') == $anio ? "selected" : "";
              echo "<option value='$anio' $selected>$anio</option>";
            }
            ?>
          </select>
        </div>
      </div>

      <hr>

      <div class="form-row">
        <div class="col-md-3">
          <label for="filtro_dia">Día de clases de la sección</label>
			<select class="form-control"  name="filtro_dia" id="filtro_dia">
				<option value="">Seleccione...</option>
				<option value="Lunes" <?= ($_GET['filtro_dia'] ?? '') == "Lunes" ? "selected" : "" ?>>Lunes</option>
				<option value="Martes" <?= ($_GET['filtro_dia'] ?? '') == "Martes" ? "selected" : "" ?>>Martes</option>
				<option value="Miércoles" <?= ($_GET['filtro_dia'] ?? '') == "Miércoles" ? "selected" : "" ?>>Miércoles</option>
				<option value="Jueves" <?= ($_GET['filtro_dia'] ?? '') == "Jueves" ? "selected" : "" ?>>Jueves</option>
				<option value="Viernes" <?= ($_GET['filtro_dia'] ?? '') == "Viernes" ? "selected" : "" ?>>Viernes</option>
				<option value="Sábado" <?= ($_GET['filtro_dia'] ?? '') == "Sábado" ? "selected" : "" ?>>Sábado</option>
				<option value="Domingo" <?= ($_GET['filtro_dia'] ?? '') == "Domingo" ? "selected" : "" ?>>Domingo</option>
		    </select>
        </div>
        <div class="col-md-3">
          <label for="orden">Ordenar por</label>
          <select name="orden" id="orden" class="form-control">
            <option value="reciente" <?= ($_GET['orden'] ?? '') == "reciente" ? "selected" : "" ?>>Más reciente primero</option>
            <option value="alfabetico" <?= ($_GET['orden'] ?? '') == "alfabetico" ? "selected" : "" ?>>Nombre del curso (A-Z)</option>
          </select>
        </div>
          
           
<!--div class="col-md-3">
  <label for="filtro_arc">¿Solo secciones inactivas?</label>
  <select name="filtro_arc" id="filtro_arc" class="form-control">
    <option value="si" <?= ($_GET['filtro_arc'] ?? 'no') == "si" ? "selected" : "" ?>>Sí</option>
    <option value="no" <?= ($_GET['filtro_arc'] ?? 'no') == "no" ? "selected" : "" ?>>No</option>
  </select>
</div->
          
          
          
          
        <div class="col-md-3 d-flex align-items-end">
          <button type="submit" class="btn btn-primary btn-block mt-2">
            <i class="fas fa-filter"></i> Aplicar filtros
          </button>
        </div>
      </div>

    </div>
  </div>
</form>






<div class="container-fluid">
    <php
        require_once "./controladores/seccionControlador.php";
        $ins_secciones = new seccionControlador();
                    
                    
         $cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";

                    
                    
                            // Botón antes del paginador
        $origen = $_GET['origen'] ?? '';
      
                    
    if (in_array($cargo, [1, 4])) {
    $origen = $_GET['origen'] ?? '';

    if ($origen === 'curso' && isset($_GET['filtro_nombre'])) {
        $idCurso = mainModel::limpiar_cadena($_GET['filtro_nombre']);
        echo '
        <p class="text-right mb-3">
            <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-vigente-new&curso_id=' . $idCurso . '&origen=curso" class="btn btn-lg btn-primary" style="text-transform: none;">
                <i class="fas fa-plus"></i> Agregar periodo de curso
            </a>
        </p>';
    } elseif ($origen === 'cohorte' && isset($_GET['filtro_id_cohorte'])) {
        $idCohorte = mainModel::limpiar_cadena($_GET['filtro_id_cohorte']);
        echo '
        <p class="text-right mb-3">
            <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-vigente-new&cohorte_id=' . $idCohorte . '&origen=cohorte" class="btn btn-lg btn-primary" style="text-transform: none;">
                <i class="fas fa-plus"></i> Agregar periodo de curso
            </a>
        </p>';
    } else {
        echo '
        <p class="text-right mb-3">
            <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-vigente-new" class="btn btn-lg btn-primary" style="text-transform: none;">
                <i class="fas fa-plus"></i> Agregar periodo de curso
            </a>
        </p>';
    }
}
                
                    

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_secciones->paginador_secciones_controlador($pagina_actual, 15, "");
                    
                                
                    
    ?>
</div>

            
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!--script>
$(document).ready(function() {
  $('#filtro_nombre').select2({
    placeholder: "Seleccione un curso",
    allowClear: true
  });

  $('#filtro_profesor').select2({
    placeholder: "Seleccione un profesor",
    allowClear: true
  });
});
</script->

<script>
$(document).ready(function() {
  $('#filtro_profesor').select2({
    placeholder: "Seleccione un profesor",
    allowClear: true,
    minimumInputLength: 2,
                      language: {
      inputTooShort: function(args) {
        return "Por favor ingrese 2 o más caracteres";
      }
    },

    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_profesor",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(p => ({
          id: p.id_profesor,
          text: `${p.nombre_profesor} ${p.apellido_profesor}`
        }))
      }),
      cache: true
    }
  });
});
</script>

<script>
$(document).ready(function() {
  $('#filtro_nombre').select2({
    placeholder: "Seleccione un curso",
    allowClear: true,
    minimumInputLength: 2,
                      language: {
      inputTooShort: function(args) {
        return "Por favor ingrese 2 o más caracteres";
      }
    },

    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(c => ({
          id: c.id_curso,
          text: `${c.nombre_curso} (${c.tipo_curso})`
        }))
      }),
      cache: true
    }
  });
});
</script>
-->