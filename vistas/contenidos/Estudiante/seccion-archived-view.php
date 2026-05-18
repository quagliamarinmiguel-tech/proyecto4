<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
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
                <i class="fas fa-layer-group fa-fw"></i> &nbsp;  Periodos de cursos archivados <strong>' . htmlspecialchars($curso['nombre_curso']) . '</strong> <em>(' . htmlspecialchars($curso['tipo_curso']) . ')</em>
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
                <i class="fas fa-layer-group fa-fw"></i> &nbsp; Periodos de cursos archivados del cohorte <strong>' . $cohorte['numero_cohorte'] . '.' . $cohorte['anio_cohorte'] . '</strong> <em>(' . htmlspecialchars($cohorte['tipo_cohorte']) . ')</em>
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
                <i class="fas fa-chalkboard-teacher fa-fw"></i> &nbsp; Clases archivadas impartidas por <strong>' . htmlspecialchars($prof['nombre_profesor'] . ' ' . $prof['apellido_profesor']) . '</strong>
            </h3>
        </div>';
    }
} else {
    // Texto por defecto
    echo '
    <div class="full-box page-header">
        <h3 class="text-left">
            <i class="fas fa-layer-group fa-fw"></i> &nbsp;  Periodos de cursos archivados
        </h3>
        <p class="text-justify">
            Los periodos de cursos archivados se muestran aqui, solo pueden restaurarse si su curso o cohorte no se encuentran archivados.
        </p>
    </div>';
}
?>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>course-list-archived"><i class="fas fa-plus fa-fw"></i> &nbsp; LISTA DE CURSOS ARCHIVADOS</a>
        </li>

        <li>
            <a class="active" href="<?php echo SERVERURL; ?>seccion-archived"><i class="fas fa-plus fa-fw"></i> &nbsp; LISTA DE PERIODOS ARCHIVADOS</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>cohorte-list-archived"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES ARCHIVADOS</a>
        </li>

    </ul>   
</div>


<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="seccion-archived">
  <input type="hidden" name="tipo" value="Terminado">

  <div class="card shadow-sm mb-3">
    <div class="card-header bg-dark text-white">
      <i class="fas fa-filter"></i> &nbsp; Filtros de búsqueda
    </div>
    <div class="card-body">

      <div class="form-row">
        <!-- IDs -->
        <div class="col-md-2">
          <label for="filtro_id">ID curso vigente</label>
          <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= $_GET['filtro_id'] ?? '' ?>">
        </div>
        <div class="col-md-2">
          <label for="filtro_id_seccion">ID sección</label>
          <input type="number" name="filtro_id_seccion" id="filtro_id_seccion" class="form-control" value="<?= $_GET['filtro_id_seccion'] ?? '' ?>">
        </div>
        <div class="col-md-2">
          <label for="filtro_id_cohorte">ID cohorte</label>
          <input type="number" name="filtro_id_cohorte" id="filtro_id_cohorte" class="form-control" value="<?= $_GET['filtro_id_cohorte'] ?? '' ?>">
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
            <option value="Otro" <?= ($_GET['filtro_tipo'] ?? '') == "Otro" ? "selected" : "" ?>>Otro</option>
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
        <div class="col-md-6">
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
 
        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_secciones->paginador_secciones_controlador2($pagina_actual, 15, "");
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
