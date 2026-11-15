<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

error_log("ID recibido: " . ($_POST['id_inscripcion'] ?? 'NULO'));


$conexion = mainModel::conectar();

// 1) Validar entrada
if (empty($_POST['id_inscripcion'])) {
  echo "<p class='text-danger text-center'>ID de inscripción no recibido.</p>";
  exit;
}
$id = mainModel::limpiar_cadena($_POST['id_inscripcion']);

// 2) Traer datos actuales de la inscripción (curso vigente, sección y estado)
$consulta = $conexion->prepare("
  SELECT 
    i.id_inscripcion,
    i.id_seccion_fk AS seccion_actual,
    i.estado_inscripcion AS estado_actual,
    cv.id_curso_vigente AS curso_vigente_actual
  FROM inscripcion i
  JOIN seccion s ON s.id_seccion = i.id_seccion_fk
  JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
  WHERE i.id_inscripcion = :id
");

$consulta->execute([':id' => $id]);
$ins = $consulta->fetch(PDO::FETCH_ASSOC);

$consulta->execute([':id' => $id]);
$ins = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$ins || empty($ins['curso_vigente_actual'])) {
  echo "<p class='text-danger text-center'>Inscripción o curso vigente no encontrado.</p>";
  exit;
}

$seccionActual = $ins['seccion_actual'];
$cursoVigenteActual = $ins['curso_vigente_actual'];
$estadoActual = $ins['estado_actual'];



// 3) Traer todos los cursos vigentes para el select de curso
$cursos = $conexion->query("
  SELECT DISTINCT
    c.id_curso,
    c.nombre_curso,
    c.tipo_curso
  FROM curso c
  JOIN curso_vigente cv ON cv.id_curso_fk = c.id_curso
  WHERE cv.estado_curso_vigente = 1
  ORDER BY c.nombre_curso, c.tipo_curso
")->fetchAll(PDO::FETCH_ASSOC);

// 4) Traer secciones del curso vigente actual para prellenar el select de sección
$seccionesStmt = $conexion->prepare("
  SELECT id_seccion, nombre_seccion
  FROM seccion
  WHERE id_curso_vigente_fk = :cv
  ORDER BY nombre_seccion
");

if (empty($cursoVigenteActual)) {
  echo "<p class='text-danger text-center'>Curso vigente no definido para esta inscripción.</p>";
  exit;
}

$seccionesStmt->execute([':cv' => $cursoVigenteActual]);
$secciones = $seccionesStmt->fetchAll(PDO::FETCH_ASSOC);


// 5) Construir HTML del formulario (se inyecta en #contenidoModificar)
?>
<input type="hidden" name="id_inscripcion" value="<?= htmlspecialchars($ins['id_inscripcion']) ?>">

<div class="form-group">
  <label for="curso">Curso</label>
    <select class="form-control" name="curso" id="curso" required>
      <?php foreach ($cursos as $cur): ?>
        <option
          value="<?= htmlspecialchars($cur['id_curso']) ?>"
          <?= ($cur['id_curso'] == $cursoBaseActual) ? 'selected' : '' ?>>
          <?= htmlspecialchars($cur['nombre_curso']) ?> (<?= htmlspecialchars($cur['tipo_curso']) ?>)
        </option>
      <?php endforeach; ?>
    </select>

  <small class="form-text text-muted">
    Al cambiar el curso, el listado de secciones se actualizará.
  </small>
</div>

<div class="form-group">
  <label for="id_seccion_fk">Sección</label>
  <select class="form-control" name="id_seccion_fk" id="seccion" required>
    <?php if ($secciones): ?>
      <?php foreach ($secciones as $sec): ?>
        <option
          value="<?= htmlspecialchars($sec['id_seccion']) ?>"
          <?= ($sec['id_seccion'] == $seccionActual) ? 'selected' : '' ?>>
          <?= htmlspecialchars($sec['nombre_seccion']) ?>
        </option>
      <?php endforeach; ?>
    <?php else: ?>
      <option value="">No hay secciones para este curso</option>
    <?php endif; ?>
  </select>
</div>

<div class="form-group">
  <label for="estado_inscripcion">Estado de la inscripción</label>
  <select class="form-control" name="estado_inscripcion" id="estado" required>
    <?php
      $estados = ['Activa', 'Inactiva', 'Suspendida', 'Finalizada', 'Abandonada'];
      foreach ($estados as $est):
    ?>
      <option value="<?= $est ?>" <?= ($est === $estadoActual) ? 'selected' : '' ?>>
        <?= $est ?>
      </option>
    <?php endforeach; ?>
  </select>
</div>

<script>
(function () {
  const $curso = $('#curso');
  const $seccion = $('#seccion');

  $curso.off('change.modificar');
  $curso.on('change.modificar', function () {
    const idCurso = $(this).val();
    $seccion.html('<option>Cargando secciones...</option>');

    $.ajax({
      url: '../../../ajax/obtenerSeccionesPorCurso.php',
      method: 'POST',
      data: { id_curso: idCurso },
      success: function (htmlOptions) {
        $seccion.html(htmlOptions);
      },
      error: function () {
        $seccion.html('<option value="">Error al cargar secciones</option>');
      }
    });
  });
})();
</script>