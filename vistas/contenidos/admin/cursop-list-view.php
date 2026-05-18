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
            <!--a href="<?php echo SERVERURL; ?>index.php?views=cursop-new">
                <i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA PUBLICIDAD
            </a-->
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cursop-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PUBLICIDADES</a>

            <!--a class="active" href="<?php echo SERVERURL; ?>index.php?views=cursop-list">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PUBLICIDADES
            </a-->
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
      <label for="filtro_nombre">Nombre del curso</label>
      <!--select name="filtro_nombre" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los cursos</option>
        <php
        $conexion = mainModel::conectar();
        $cursos = $conexion->query("
          SELECT c.id_curso, c.nombre_curso, c.tipo_curso
          FROM curso c
          INNER JOIN curso_publicidad cp ON cp.id_curso = c.id_curso
          WHERE c.estado_curso = 1 AND cp.estado_curso_publicidad = 1
          GROUP BY c.id_curso
          ORDER BY c.nombre_curso ASC
        ");
        foreach ($cursos as $curso) {
          $selected = (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] == $curso['nombre_curso']) ? "selected" : "";
          echo '<option value="' . $curso['nombre_curso'] . '" ' . $selected . '>' . $curso['nombre_curso'] . ' (' . $curso['tipo_curso'] . ')</option>';
        }
        ?>
      </select-->
        <?php
            $filtro_nombre = $_GET['filtro_nombre'] ?? '';
            $curso_nombre = '';

            if ($filtro_nombre !== '') {
                $conexion = mainModel::conectar();
                $stmt = $conexion->prepare("
                    SELECT nombre_curso, tipo_curso
                    FROM curso
                    WHERE nombre_curso = :nombre AND estado_curso = 1
                ");
                $stmt->execute([':nombre' => $filtro_nombre]);
                $curso = $stmt->fetch();
                if ($curso) {
                    $curso_nombre = $curso['nombre_curso'] . ' (' . $curso['tipo_curso'] . ')';
                }
            }
        ?>
        <select name="filtro_nombre" id="filtro_nombre" class="form-control select2-ajax">
          <?php if ($filtro_nombre && $curso_nombre): ?>
            <option value="<?= htmlspecialchars($filtro_nombre) ?>" selected><?= htmlspecialchars($curso_nombre) ?></option>
          <?php endif; ?>
        </select>
        
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

    <!-- Estado del curso -->
    <div class="col-md-3">
      <label for="estado">Estado del curso</label>
      <select name="estado" id="estado" class="form-control">
        <option value="">Todos</option>
        <option value="Vigente" <?= (isset($_GET['estado']) && $_GET['estado'] == "Vigente") ? "selected" : "" ?>>Vigente</option>
        <option value="Próximo" <?= (isset($_GET['estado']) && $_GET['estado'] == "Próximo") ? "selected" : "" ?>>Próximo</option>
        <option value="No vigente" <?= (isset($_GET['estado']) && $_GET['estado'] == "No vigente") ? "selected" : "" ?>>No vigente</option>
      </select>
    </div>

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


<!--form method="GET" class="mb-4">
  <input type="hidden" name="views" value="cursop-list">
  <div class="form-row align-items-end">

    <div class="col-md-2">
      <label for="busqueda">ID de publicidad</label>
      <input type="number" name="busqueda" id="busqueda" class="form-control" value="<?= isset($_GET['busqueda']) ? $_GET['busqueda'] : "" ?>">
    </div>

    <div class="col-md-4">
      <label for="filtro_nombre">Nombre del curso</label>
      <select name="busqueda" id="filtro_nombre" class="form-control select2">
        <option value="">Todos los cursos</option>
<php
$conexion = mainModel::conectar();
$cursos = $conexion->query("
    SELECT c.id_curso, c.nombre_curso, c.tipo_curso
    FROM curso c
    INNER JOIN curso_publicidad cp ON cp.id_curso = c.id_curso
    WHERE c.estado_curso = 1 AND cp.estado_curso_publicidad = 1
    GROUP BY c.id_curso
    ORDER BY c.nombre_curso ASC
");

foreach ($cursos as $curso) {
    $selected = (isset($_GET['busqueda']) && $_GET['busqueda'] == $curso['nombre_curso']) ? "selected" : "";
    echo '<option value="' . $curso['nombre_curso'] . '" ' . $selected . '>' . $curso['nombre_curso'] . ' (' . $curso['tipo_curso'] . ')</option>';
}
?!>
      </select>
    </div>

    <div class="col-md-3">
      <label for="filtro_tipo">Tipo de curso</label>
      <select name="busqueda" id="filtro_tipo" class="form-control">
        <option value="">Todos los tipos</option>
        <option value="Intensivo" <?= (isset($_GET['busqueda']) && $_GET['busqueda'] == "Intensivo") ? "selected" : "" ?>>Intensivo</option>
        <option value="Extendido" <?= (isset($_GET['busqueda']) && $_GET['busqueda'] == "Extendido") ? "selected" : "" ?>>Extendido</option>
        <option value="Técnico Profesional" <?= (isset($_GET['busqueda']) && $_GET['busqueda'] == "Técnico Profesional") ? "selected" : "" ?>>Técnico Profesional</option>
        <option value="Otro" <?= (isset($_GET['busqueda']) && $_GET['busqueda'] == "Otro") ? "selected" : "" ?>>Otro</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="estado">Estado del curso</label>
      <select name="estado" id="estado" class="form-control">
        <option value="">Todos</option>
        <option value="Vigente" <?= (isset($_GET['estado']) && $_GET['estado'] == "Vigente") ? "selected" : "" ?>>Vigente</option>
        <option value="Próximo" <?= (isset($_GET['estado']) && $_GET['estado'] == "Próximo") ? "selected" : "" ?>>Próximo</option>
        <option value="No vigente" <?= (isset($_GET['estado']) && $_GET['estado'] == "No vigente") ? "selected" : "" ?>>No vigente</option>
      </select>
    </div>

    <div class="col-md-3">
      <label for="orden">Ordenar por</label>
      <select name="orden" id="orden" class="form-control">
        <option value="nombre" <?= (isset($_GET['orden']) && $_GET['orden'] == "nombre") ? "selected" : "" ?>>Nombre del curso (A-Z)</option>
        <option value="interacciones" <?= (isset($_GET['orden']) && $_GET['orden'] == "interacciones") ? "selected" : "" ?>>Más clics en publicidad</option>
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
        require_once "./controladores/cursoPublicidadControlador.php";
        $ins_publicidad = new cursoPublicidadControlador();
        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        echo $ins_publicidad->paginador_publicidad_controlador($pagina, 10, "cursop-list", "");
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
    width: '100%',
                      language: {
      inputTooShort: function(args) {
        return "Por favor ingrese 2 o más caracteres";
      }
    },
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso_publicitado",
      dataType: 'json',
      delay: 250,
      data: params => ({ q: params.term }),
      processResults: data => ({
        results: data.map(c => ({
          id: c.nombre_curso,
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