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
            <!--a href="<?php echo SERVERURL; ?>course-list-archived"><i class="fas fa-plus fa-fw"></i> &nbsp; LISTA DE CURSOS ARCHIVADOS</a-->
        </li>

        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS DE COHORTES </a>
            <!--a class="active" href="<?php echo SERVERURL; ?>seccion-archived"><i class="fas fa-plus fa-fw"></i> &nbsp; LISTA DE PERIODOS ARCHIVADOS</a-->
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list'; ?>"><i class="fas fa-bolt fa-fw"></i> &nbsp; LISTA DE PERIODOS INTENSIVOS </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cohorte-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES </a>
            <!--a href="<?php echo SERVERURL; ?>cohorte-list-archived"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES ARCHIVADOS</a-->
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
        <!-- IDs -->
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
        <!-- Cohorte -->
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
            <?php
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
            <!--option value="inscritos" <?= ($_GET['orden'] ?? '') == "inscritos" ? "selected" : "" ?>>Más inscritos primero</option-->
          </select>
        </div>
          
           
<!--div class="col-md-3">
  <label for="filtro_arc">¿Solo secciones inactivas?</label>
  <select name="filtro_arc" id="filtro_arc" class="form-control">
    <option value="si" <?= ($_GET['filtro_arc'] ?? 'no') == "si" ? "selected" : "" ?>>Sí</option>
    <option value="no" <?= ($_GET['filtro_arc'] ?? 'no') == "no" ? "selected" : "" ?>>No</option>
  </select>
</div-->
          
          
          
          
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
      
  /*                  
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
        } else{
                        echo '
            <p class="text-right mb-3">
                    <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-vigente-new" class="btn btn-lg btn-primary" style="text-transform: none;">
                        <i class="fas fa-plus"></i> Agregar periodo de curso
                    </a>
            </p>';
        }
*/
                    
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

                <!--a href="' . SERVERURL . 'index.php?views=course-vigente-new&curso_id=' . $idCurso . '&origen=curso" class="btn btn-lg btn-primary" style="text-transform: none;">
                    <i class="fas fa-plus"></i> Agregar periodo de curso
                </a-->
                <!--a href="' . SERVERURL . 'index.php?views=course-vigente-new&cohorte_id=' . $idCohorte . '&origen=cohorte" class="btn btn-lg btn-primary" style="text-transform: none;">
                    <i class="fas fa-plus"></i> Agregar periodo de curso
                </a-->

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
</script-->

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



<!--form method="GET" class="mb-4"> creo q es el form con el style viejo
  <input type="hidden" name="views" value="seccion-list">
  <input type="hidden" name="tipo" value="Vigente">

  <div class="form-row align-items-end">

    <!-- ID curso vigente ->
    <div class="col-md-2">
      <label for="filtro_id">ID curso vigente</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= $_GET['filtro_id'] ?? '' ?>">
    </div>

    <!-- ID de sección ->
    <div class="col-md-2">
      <label for="filtro_id_seccion">ID de sección</label>
      <input type="number" name="filtro_id_seccion" id="filtro_id_seccion" class="form-control" value="<?= $_GET['filtro_id_seccion'] ?? '' ?>">
    </div>

    <!-- Nombre del curso ->
    <div class="col-md-4">
      <label for="filtro_nombre">Nombre del curso</label>
      <select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los cursos</option>
        <php
        $conexion = mainModel::conectar();
        $cursos = $conexion->query("
          SELECT DISTINCT c.nombre_curso
          FROM curso c
          INNER JOIN curso_vigente cv ON cv.id_curso_fk = c.id_curso
          INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
          WHERE ch.archivar_cohorte = 1 AND c.estado_curso=1 AND cv.estado_curso_vigente=1
          ORDER BY c.nombre_curso ASC
        ");
        foreach ($cursos as $curso) {
          $selected = ($_GET['filtro_nombre'] ?? '') == $curso['nombre_curso'] ? "selected" : "";
          echo "<option value='{$curso['nombre_curso']}' $selected>{$curso['nombre_curso']}</option>";
        }
        ?>
      </select>
    </div>

    <!-- Profesor ->
    <div class="col-md-4">
      <label for="filtro_profesor">Profesor</label>
      <select name="filtro_profesor" id="filtro_profesor" class="form-control select2">
        <option value="">Todos los profesores</option>
        <php
        $profesores = $conexion->query("
          SELECT id_profesor, nombre_profesor, apellido_profesor
          FROM profesor
          WHERE estado_profesor = 1
          ORDER BY nombre_profesor ASC
        ");
        foreach ($profesores as $prof) {
          $selected = ($_GET['filtro_profesor'] ?? '') == $prof['id_profesor'] ? "selected" : "";
          echo "<option value='{$prof['id_profesor']}' $selected>{$prof['nombre_profesor']} {$prof['apellido_profesor']}</option>";
        }
        ?>
      </select>
    </div>

    <!-- Tipo de cohorte ->
    <div class="col-md-3">
      <label for="filtro_tipo">Tipo de cohorte</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos</option>
        <option value="Intensivo" <?= ($_GET['filtro_tipo'] ?? '') == "Intensivo" ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= ($_GET['filtro_tipo'] ?? '') == "Extendido" ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= ($_GET['filtro_tipo'] ?? '') == "Técnico Profesional" ? "selected" : "" ?>>Técnico Profesional</option>
        <option value="Otro" <?= ($_GET['filtro_tipo'] ?? '') == "Otro" ? "selected" : "" ?>>Otro</option>
      </select>
    </div>

    <!-- Estado de cohorte ->
    <div class="col-md-3">
      <label for="filtro_estado">Estado de cohorte</label>
      <select name="filtro_estado" id="filtro_estado" class="form-control">
        <option value="">Todos</option>
        <option value="Vigente" <?= ($_GET['filtro_estado'] ?? '') == "Vigente" ? "selected" : "" ?>>Vigente</option>
        <option value="Próximo" <?= ($_GET['filtro_estado'] ?? '') == "Próximo" ? "selected" : "" ?>>Próximo</option>
        <option value="Terminado" <?= ($_GET['filtro_estado'] ?? '') == "Terminado" ? "selected" : "" ?>>Terminado</option>
      </select>
    </div>

    <!-- Año de cohorte ->
    <div class="col-md-2">
      <label for="filtro_anio">Año de cohorte</label>
      <select name="filtro_anio" id="filtro_anio" class="form-control">
        <option value="">Todos</option>
        <php
        for ($anio = 25; $anio <= 50; $anio++) {
          $selected = ($_GET['filtro_anio'] ?? '') == $anio ? "selected" : "";
          echo "<option value='$anio' $selected>$anio</option>";
        }
        ?>
      </select>
    </div>

    <!-- Ordenar por ->
    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="reciente" <?= ($_GET['orden'] ?? '') == "reciente" ? "selected" : "" ?>>Más reciente primero</option>
        <option value="alfabetico" <?= ($_GET['orden'] ?? '') == "alfabetico" ? "selected" : "" ?>>Nombre del curso (A-Z)</option>
        <option value="inscritos" <?= ($_GET['orden'] ?? '') == "inscritos" ? "selected" : "" ?>>Más inscritos primero</option>
      </select>
    </div>

    <!-- Botón ->
    <div class="col-md-3">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>

  </div>
</form-->











<!--form method="GET" class="mb-4">
  <input type="hidden" name="views" value="seccion-list">
  <input type="hidden" name="tipo" value="Vigente">

  <div class="form-row align-items-end">

  
    <div class="col-md-2">
      <label for="filtro_id">ID curso vigente</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div>

    <div class="col-md-4">
      <label for="filtro_nombre">Nombre del curso</label>
      <select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los cursos</option>
        <php
        $conexion = mainModel::conectar();
        $cursos = $conexion->query("
          SELECT DISTINCT c.nombre_curso
          FROM curso c
          INNER JOIN curso_vigente cv ON cv.id_curso_fk = c.id_curso
          INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
          WHERE ch.archivar_cohorte = 1 AND c.estado_curso=1 AND cv.estado_curso_vigente=1
          ORDER BY c.nombre_curso ASC
        ");
        foreach ($cursos as $curso) {
          $selected = (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] == $curso['nombre_curso']) ? "selected" : "";
          echo '<option value="' . $curso['nombre_curso'] . '" ' . $selected . '>' . $curso['nombre_curso'] . '</option>';
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtro_tipo">Tipo de cohorte</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos</option>
        <option value="Intensivo" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Intesivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
        <option value="Otro" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Otro") ? "selected" : "" ?>>Otro</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtro_estado">Estado de cohorte</label>
      <select name="filtro_estado" id="filtro_estado" class="form-control">
        <option value="">Todos</option>
        <option value="Vigente" <?= (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == "Vigente") ? "selected" : "" ?>>Vigente</option>
        <option value="Próximo" <?= (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == "Próximo") ? "selected" : "" ?>>Próximo</option>
        <option value="Terminado" <?= (isset($_GET['filtro_estado']) && $_GET['filtro_estado'] == "Terminado") ? "selected" : "" ?>>Terminado</option>
      </select>
    </div>

    <div class="col-md-2">
      <label for="filtro_anio">Año de cohorte</label>
      <select name="filtro_anio" id="filtro_anio" class="form-control">
        <option value="">Todos</option>
        <php
        for ($anio = 25; $anio <= 50; $anio++) {
          $selected = (isset($_GET['filtro_anio']) && $_GET['filtro_anio'] == $anio) ? "selected" : "";
          echo "<option value='$anio' $selected>$anio</option>";
        }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="reciente" <?= (isset($_GET['orden']) && $_GET['orden'] == "reciente") ? "selected" : "" ?>>Más reciente primero</option>
        <option value="alfabetico" <?= (isset($_GET['orden']) && $_GET['orden'] == "alfabetico") ? "selected" : "" ?>>Nombre del curso (A-Z)</option>
        <option value="inscritos" <?= (isset($_GET['orden']) && $_GET['orden'] == "inscritos") ? "selected" : "" ?>>Más inscritos primero</option>
      </select>
    </div>

    <div class="col-md-3">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>

  </div>
</form-->
