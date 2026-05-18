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

<?php
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
                <i class="fas fa-layer-group fa-fw"></i> &nbsp; Periodos intensivos del curso <strong>' . htmlspecialchars($curso['nombre_curso']) . '</strong> <em>(' . htmlspecialchars($curso['tipo_curso']) . ')</em>
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
                <i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; Clases intensivas impartidas por <strong>' . htmlspecialchars($prof['nombre_profesor'] . ' ' . $prof['apellido_profesor']) . '</strong>
            </h3>
        </div>';
    }
} else {
    // Texto por defecto
    echo '
    <div class="full-box page-header">
        <h3 class="text-left">
            <i class="fas fa-bolt fa-fw"></i> &nbsp; Periodos de cursos intensivos
        </h3>
        <p class="text-justify">
            Los periodos de cursos intensivos determinan los cursos que se imparten en modalidad intensiva, con fechas específicas de inicio y fin. Desde aquí pueden visualizarse los periodos intensivos de cada curso y sus secciones.
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
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS REGULARES </a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list'; ?>"><i class="fas fa-bolt fa-fw"></i> &nbsp; LISTA DE PERIODOS INTENSIVOS </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES </a>
        </li>
    </ul>   
</div>

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="cv-int-list-archived">
  <input type="hidden" name="tipo" value="Intensivo">

  <div class="card shadow-sm mb-3">
    <div class="card-header bg-dark text-white">
      <i class="fas fa-filter"></i> &nbsp; Filtros de búsqueda - Cursos Intensivos
    </div>
    <div class="card-body">

      <div class="form-row">
        <!-- IDs -->
        <div class="col-md-2">
          <label for="filtro_id">ID periodo de curso</label>
          <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= $_GET['filtro_id'] ?? '' ?>">
        </div>
        <div class="col-md-2">
          <label for="filtro_id_seccion">ID sección</label>
          <input type="number" name="filtro_id_seccion" id="filtro_id_seccion" class="form-control" value="<?= $_GET['filtro_id_seccion'] ?? '' ?>">
        </div>
      </div>

      <hr>

      <div class="form-row">
        <!-- Curso y profesor -->
        <div class="col-md-6">
          <label for="filtro_nombre">Nombre del curso</label>
            
            <?php
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
              <?php if ($curso_id && $curso_nombre): ?>
                <option value="<?= $curso_id ?>" selected><?= htmlspecialchars($curso_nombre) ?></option>
              <?php endif; ?>
            </select>

        </div>

        <div class="col-md-6">
          <label for="filtro_profesor">Profesor</label>
            
          <?php
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
              <?php if ($profesor_id && $profesor_nombre): ?>
                <option value="<?= $profesor_id ?>" selected><?= htmlspecialchars($profesor_nombre) ?></option>
              <?php endif; ?>
            </select>
          </div>
      </div>

      <hr>

      <div class="form-row">
        <!-- Estado y Año -->
        <div class="col-md-4">
          <label for="filtro_estado">Estado del periodo</label>
          <select name="filtro_estado" id="filtro_estado" class="form-control">
            <option value="">Todos</option>
            <option value="Vigente" <?= ($_GET['filtro_estado'] ?? '') == "Vigente" ? "selected" : "" ?>>Vigente</option>
            <option value="Próximo" <?= ($_GET['filtro_estado'] ?? '') == "Próximo" ? "selected" : "" ?>>Próximo</option>
            <option value="Terminado" <?= ($_GET['filtro_estado'] ?? '') == "Terminado" ? "selected" : "" ?>>Terminado</option>
          </select>
        </div>

        <div class="col-md-4">
          <label for="filtro_anio">Año de inicio</label>
          <input type="number" 
                 name="filtro_anio" 
                 id="filtro_anio" 
                 class="form-control" 
                 min="2025" 
                 max="2050" 
                 value="<?= $_GET['filtro_anio'] ?? '' ?>" 
                 placeholder="Ej: 2025">
        </div>

        <div class="col-md-4">
          <label for="filtro_arc">¿Solo secciones inactivas?</label>
          <select name="filtro_arc" id="filtro_arc" class="form-control">
            <option value="si" <?= ($_GET['filtro_arc'] ?? 'no') == "si" ? "selected" : "" ?>>Sí</option>
            <option value="no" <?= ($_GET['filtro_arc'] ?? 'no') == "no" ? "selected" : "" ?>>No</option>
          </select>
        </div>
      </div>

      <hr>

      <div class="form-row">
        <!-- Orden y botón -->
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
            <option value="inscritos" <?= ($_GET['orden'] ?? '') == "inscritos" ? "selected" : "" ?>>Más inscritos primero</option>
          </select>
        </div>

        <div class="col-md-6 d-flex align-items-end">
          <button type="submit" class="btn btn-primary btn-block mt-2">
            <i class="fas fa-filter"></i> Aplicar filtros
          </button>
        </div>
      </div>

    </div>
  </div>
</form>

<div class="container-fluid">
    <?php
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
        if ($origen === 'curso' && isset($_GET['filtro_nombre'])) {
            $idCurso = mainModel::limpiar_cadena($_GET['filtro_nombre']);
            echo '
            <p class="text-right mb-3">
                <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-vigente-intensivo-new&curso_id=' . $idCurso . '&origen=curso" class="btn btn-lg btn-primary" style="text-transform: none;">
                    <i class="fas fa-plus"></i> Agregar periodo intensivo
                </a>
            </p>';
        }

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        // Llamar al nuevo paginador para cursos intensivos
        echo $ins_secciones->paginador_secciones_intensivos_archivados_controlador($pagina_actual, 15, "");
    ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
  $('#filtro_profesor').select2({
    placeholder: "Seleccione un profesor",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_cualquier_profesor",
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
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso_int_archivado",
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

<script>
// Establecer el año actual como valor mínimo
document.addEventListener('DOMContentLoaded', function() {
    const anioInput = document.getElementById('filtro_anio');
    if (anioInput) {
        const anioActual = new Date().getFullYear();
        anioInput.min = anioActual;
        
        // Si no hay valor, establecer el año actual como placeholder
        if (!anioInput.value) {
            anioInput.placeholder = `Ej: ${anioActual}`;
        }
    }
});
</script>