<?php  
    if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 3){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-user-edit fa-fw"></i> &nbsp; LISTA DE SOLICITUDES DE INSCRIPCIÓN
    </h3>
    <p class="text-justify">
        Visualiza todas las solicitudes de inscripción registradas. Puedes ver la captura de pago, procesar la solicitud o eliminarla si es inválida.
    </p>
</div>



<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="solicitud-list">
  <div class="form-row align-items-end">

    <div class="col-md-3">
      <label for="filtro_ci">Cédula del estudiante</label>
      <input type="text" name="filtro_ci" id="filtro_ci" class="form-control" value="<?= $_GET['filtro_ci'] ?? "" ?>">
    </div>

    <div class="col-md-4">
      <label for="filtro_estudiante">Nombre o apellido del estudiante</label>
      <?php
        $filtro_estudiante = $_GET['filtro_estudiante'] ?? '';
        $estudiante_nombre = '';

        if ($filtro_estudiante !== '') {
            $conexion = mainModel::conectar();
            $stmt = $conexion->prepare("SELECT nombre_estudiante, apellido_estudiante FROM solicitud_inscripcion WHERE id_solicitud = :id");
            $stmt->execute([':id' => $filtro_estudiante]);
            $est = $stmt->fetch();
            if ($est) {
                $estudiante_nombre = $est['nombre_estudiante'] . ' ' . $est['apellido_estudiante'];
            }
        }
      ?>
      <select name="filtro_estudiante" id="filtro_estudiante" class="form-control select2-ajax">
        <?php if ($filtro_estudiante && $estudiante_nombre): ?>
          <option value="<?= $filtro_estudiante ?>" selected><?= htmlspecialchars($estudiante_nombre) ?></option>
        <?php endif; ?>
      </select>
    </div>

    <!--div class="col-md-4">
      <label for="filtro_curso">Nombre del curso</label>
      <php
        $filtro_curso = $_GET['filtro_curso'] ?? '';
        $curso_nombre = '';

        if ($filtro_curso !== '') {
            $conexion = mainModel::conectar();
            $stmt = $conexion->prepare("SELECT nombre_curso FROM curso WHERE id_curso = :id");
            $stmt->execute([':id' => $filtro_curso]);
            $curso = $stmt->fetch();
            if ($curso) {
                $curso_nombre = $curso['nombre_curso'];
            }
        }
      ?>
      <select name="filtro_curso" id="filtro_curso" class="form-control select2-ajax">
        <php if ($filtro_curso && $curso_nombre): ?>
          <option value="<?= $filtro_curso ?>" selected><?= htmlspecialchars($curso_nombre) ?></option>
        <php endif; ?>
      </select>
    </div-->

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="reciente" <?= (!isset($_GET['orden']) || $_GET['orden'] == "reciente") ? "selected" : "" ?>>Más reciente</option>
        <option value="apellido" <?= ($_GET['orden'] ?? "") == "apellido" ? "selected" : "" ?>>Apellido (A-Z)</option>
        <option value="curso" <?= ($_GET['orden'] ?? "") == "curso" ? "selected" : "" ?>>Curso (A-Z)</option>
      </select>
    </div>

    <div class="col-md-3 mt-3">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form>

<div class="container-fluid">
  <?php
    require_once "./controladores/recuperarControlador.php";
    $ins_solicitud = new recuperarControlador();
    $pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    echo $ins_solicitud->paginador_solicitud_inscripcion_controlador($pagina, 10, "solicitud-list", "");
  ?>
</div>

<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
$(document).ready(function() {
  $('#filtro_estudiante').select2({
    placeholder: "Seleccione un estudiante",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_estudiante_solicitud",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(e => ({
          id: e.id_solicitud,
          text: `${e.nombre_estudiante} ${e.apellido_estudiante}`
        }))
      }),
      cache: true
    }
  });

});
</script>

<!--/*
  $('#filtro_curso').select2({
    placeholder: "Seleccione un curso",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso_solicitud",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(c => ({
          id: c.id_curso,
          text: c.nombre_curso
        }))
      }),
      cache: true
    }
  });
*/-->
