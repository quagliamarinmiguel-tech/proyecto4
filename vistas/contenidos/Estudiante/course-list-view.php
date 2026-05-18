
<?php  
    $cargo = $_SESSION['cargo_spm'] ?? 0;

    if (!in_array($cargo, [1, 2, 3, 4, 5])) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>
    

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS
    </h3>


    <p class="text-justify">
        Aquí puedes visualizar todos los cursos registrados en el sistema, editarlos o archivarlos.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
	     <?php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=course-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
        </li>
        <?php endif; ?>

        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=course-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
    </ul>   
</div>


<!--form method="GET" class="mb-4">
  <input type="hidden" name="views" value="course-list">
  <div class="form-row align-items-end">
    <div class="col-md-4">
      <label for="filtro_tipo">Filtrar por tipo de curso</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos los tipos</option>
        <option value="Intensivo" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Intensivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
        <option value="Otro" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Otro") ? "selected" : "" ?>>Otro</option>
      </select>
    </div>

    <div class="col-md-4">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="">Sin orden específico</option>
        <option value="tipo" <?= (isset($_GET['orden']) && $_GET['orden'] == "tipo") ? "selected" : "" ?>>Tipo de curso (A-Z)</option>
        <option value="fecha" <?= (isset($_GET['orden']) && $_GET['orden'] == "fecha") ? "selected" : "" ?>>Más nuevo primero</option>
      </select>
    </div>

    <div class="col-md-4">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form-->



















<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="course-list">

  <div class="card shadow-sm mb-3">
    <div class="card-header bg-dark text-white">
      <i class="fas fa-filter"></i> &nbsp; Filtros de búsqueda de cursos
    </div>
    <div class="card-body">

      <!-- Fila 1: ID y nombre -->
      <div class="form-row">
        <div class="col-md-1 mb-2">
          <label for="filtro_id">ID del curso</label>
          <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= $_GET['filtro_id'] ?? '' ?>">
        </div>

        <div class="col-md-4 mb-3">
          <label for="filtro_nombre">Nombre del curso</label>
            <?php
    			$conexion = mainModel::conectar();
                $filtro_nombre = $_GET['filtro_nombre'] ?? '';
                $curso_nombre = '';

                if ($filtro_nombre !== '') {
                    $stmt = $conexion->prepare("SELECT nombre_curso, tipo_curso FROM curso WHERE id_curso = :id");
                    $stmt->execute([':id' => $filtro_nombre]);
                    $curso = $stmt->fetch();
                    if ($curso) {
                        $curso_nombre = $curso['nombre_curso'] . ' (' . $curso['tipo_curso'] . ')';
                    }
                }
            ?>
            <select name="filtro_nombre" id="filtro_nombre" class="form-control select2-ajax">
              <?php if ($filtro_nombre && $curso_nombre): ?>
                <option value="<?= $filtro_nombre ?>" selected><?= htmlspecialchars($curso_nombre) ?></option>
              <?php endif; ?>
            </select>
          <!--select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
            <option value="">Todos los cursos</option>
            <php
              $conexion = mainModel::conectar();
              $cursos = $conexion->query("SELECT id_curso, nombre_curso FROM curso WHERE estado_curso = 1 ORDER BY nombre_curso ASC");
              foreach ($cursos as $curso) {
                  $selected = ($_GET['filtro_nombre'] ?? '') == $curso['id_curso'] ? "selected" : "";
                  echo "<option value='{$curso['id_curso']}' $selected>{$curso['nombre_curso']}</option>";
              }
            ?>
          </select-->
        </div>
          
        <div class="col-md-2 mb-3">
          <label for="filtro_tipo">Tipo de curso</label>
          <select name="filtro_tipo" id="filtro_tipo" class="form-control">
            <option value="">Todos los tipos</option>
            <option value="Intensivo" <?= ($_GET['filtro_tipo'] ?? '') == "Intensivo" ? "selected" : "" ?>>Intensivo</option>
            <option value="Extendido" <?= ($_GET['filtro_tipo'] ?? '') == "Extendido" ? "selected" : "" ?>>Extendido</option>
            <option value="Técnico Profesional" <?= ($_GET['filtro_tipo'] ?? '') == "Técnico Profesional" ? "selected" : "" ?>>Técnico Profesional</option>
            <option value="Otro" <?= ($_GET['filtro_tipo'] ?? '') == "Otro" ? "selected" : "" ?>>Otro</option>
          </select>
        </div>
          
          
                  <div class="col-md-2 mb-3">
          <label for="filtro_cupos">Disponibilidad de cupos</label>
          <select name="filtro_cupos" id="filtro_cupos" class="form-control">
            <option value="">Todos</option>
            <option value="1" <?= ($_GET['filtro_cupos'] ?? '') == "1" ? "selected" : "" ?>>Disponible</option>
            <option value="0" <?= ($_GET['filtro_cupos'] ?? '') == "0" ? "selected" : "" ?>>No disponible</option>
          </select>
        </div>
          
          
          
          
      </div>

      <hr>

      <!-- Fila 2: Tipo, cupos, publicidad ->
      <div class="form-row">
        <div class="col-md-4 mb-3">
          <label for="filtro_tipo">Tipo de curso</label>
          <select name="filtro_tipo" id="filtro_tipo" class="form-control">
            <option value="">Todos los tipos</option>
            <option value="Intensivo" <?= ($_GET['filtro_tipo'] ?? '') == "Intensivo" ? "selected" : "" ?>>Intensivo</option>
            <option value="Extendido" <?= ($_GET['filtro_tipo'] ?? '') == "Extendido" ? "selected" : "" ?>>Extendido</option>
            <option value="Técnico Profesional" <?= ($_GET['filtro_tipo'] ?? '') == "Técnico Profesional" ? "selected" : "" ?>>Técnico Profesional</option>
            <option value="Otro" <?= ($_GET['filtro_tipo'] ?? '') == "Otro" ? "selected" : "" ?>>Otro</option>
          </select>
        </div-->

        <!--div class="col-md-4 mb-3">
          <label for="filtro_cupos">Disponibilidad de cupos</label>
          <select name="filtro_cupos" id="filtro_cupos" class="form-control">
            <option value="">Todos</option>
            <option value="1" <?= ($_GET['filtro_cupos'] ?? '') == "1" ? "selected" : "" ?>>Disponible</option>
            <option value="0" <?= ($_GET['filtro_cupos'] ?? '') == "0" ? "selected" : "" ?>>No disponible</option>
          </select>
        </div>


      </div-->

      <!-- Fila 3: Orden y botón -->
      <div class="form-row">
          
        <div class="col-md-3 mb-3">
          <label for="filtro_publicidad">Publicidad</label>
          <select name="filtro_publicidad" id="filtro_publicidad" class="form-control">
            <option value="">Todos</option>
            <option value="1" <?= ($_GET['filtro_publicidad'] ?? '') == "1" ? "selected" : "" ?>>Con publicidad</option>
            <option value="0" <?= ($_GET['filtro_publicidad'] ?? '') == "0" ? "selected" : "" ?>>Sin publicidad</option>
          </select>
        </div>
          
          
        <div class="col-md-3 mb-3">
          <label for="orden">Ordenar por</label>
          <select name="orden" id="orden" class="form-control">
            <option value="">Sin orden específico</option>
            <option value="tipo" <?= ($_GET['orden'] ?? '') == "tipo" ? "selected" : "" ?>>Tipo de curso (A-Z)</option>
            <option value="fecha" <?= ($_GET['orden'] ?? '') == "fecha" ? "selected" : "" ?>>Más nuevo primero</option>
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














<!--form method="GET" class="mb-4">
  <input type="hidden" name="views" value="course-list">
  <div class="form-row align-items-end">

    <div class="col-md-2">
      <label for="filtro_id">ID del curso</label>
      <input type="number" name="filtro_id" id="filtro_id" class="form-control" value="<?= isset($_GET['filtro_id']) ? $_GET['filtro_id'] : "" ?>">
    </div>

    <div class="col-md-4">
      <label for="filtro_nombre">Nombre del curso</label>
      <select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los cursos</option>
        <php
          $conexion = mainModel::conectar();
          $cursos = $conexion->query("SELECT id_curso, nombre_curso FROM curso WHERE estado_curso = 1 ORDER BY nombre_curso ASC");
          foreach ($cursos as $curso) {
              $selected = (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] == $curso['id_curso']) ? "selected" : "";
              echo '<option value="' . $curso['id_curso'] . '" ' . $selected . '>' . $curso['nombre_curso'] . '</option>';
          }
        ?>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtro_tipo">Tipo de curso</label>
      <select name="filtro_tipo" id="filtro_tipo" class="form-control">
        <option value="">Todos los tipos</option>
        <option value="Intensivo" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Intensivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
        <option value="Otro" <?= (isset($_GET['filtro_tipo']) && $_GET['filtro_tipo'] == "Otro") ? "selected" : "" ?>>Otro</option>
      </select>
    </div>
      

    <div class="col-md-3">
      <label for="filtro_cupos">Disponibilidad de cupos</label>
      <select name="filtro_cupos" id="filtro_cupos" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= (isset($_GET['filtro_cupos']) && $_GET['filtro_cupos'] == "1") ? "selected" : "" ?>>Disponible</option>
        <option value="0" <?= (isset($_GET['filtro_cupos']) && $_GET['filtro_cupos'] == "0") ? "selected" : "" ?>>No disponible</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtro_publicidad">Publicidad</label>
      <select name="filtro_publicidad" id="filtro_publicidad" class="form-control">
        <option value="">Todos</option>
        <option value="1" <?= (isset($_GET['filtro_publicidad']) && $_GET['filtro_publicidad'] == "1") ? "selected" : "" ?>>Con publicidad</option>
        <option value="0" <?= (isset($_GET['filtro_publicidad']) && $_GET['filtro_publicidad'] == "0") ? "selected" : "" ?>>Sin publicidad</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="">Sin orden específico</option>
        <option value="tipo" <?= (isset($_GET['orden']) && $_GET['orden'] == "tipo") ? "selected" : "" ?>>Tipo de curso (A-Z)</option>
        <option value="fecha" <?= (isset($_GET['orden']) && $_GET['orden'] == "fecha") ? "selected" : "" ?>>Más nuevo primero</option>
      </select>
    </div>

    <div class="col-md-3">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form-->

<div class="container-fluid">
    <?php
        require_once "./controladores/cursoControlador.php";
        $ins_curso = new cursoControlador();

        // Obtener el número de página actual
        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        echo $ins_curso->paginador_curso_controlador($pagina_actual, 15, "course-list", "");
    ?>
</div>



<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

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
<!--script>
  $(document).ready(function() {
    $('#filtro_nombre').select2({
      placeholder: "Seleccione un curso",
      allowClear: true
    });
  });
</script-->