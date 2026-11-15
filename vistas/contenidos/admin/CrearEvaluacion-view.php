<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<?php
require_once __DIR__ . "/../../../controladores/CrearEvaluacionControlador.php";
$controlador = new CrearEvaluacionControlador();

$cursos = $controlador->obtener_cursos();
$mensaje = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $datos = [
        'tipo' => $_POST['tipo_evaluacion'] ?? '',
        'obj' => $_POST['obj'] ?? '',
        'porcentaje' => $_POST['porcentaje'] ?? 0,
        'fecha' => $_POST['fecha_evaluacion'] ?? '',
        'id_seccion' => $_POST['id_seccion'] ?? null
    ];

    $exito = $controlador->crear_evaluacion($datos);
    $mensaje = $exito ? "✅ Evaluación creada correctamente." : "❌ Error al guardar la evaluación.";
}
?>

<div class="container mt-5">
    <h2 class="mb-4 text-center">Crear Nueva Evaluación</h2>

    <?php if ($mensaje): ?>
        <div class="alert alert-<?= strpos($mensaje, 'Error') ? 'danger' : 'success' ?> text-center"><?= $mensaje ?></div>
    <?php endif; ?>

    <form method="post" class="bg-light p-4 border rounded">
        <div class="form-group">
            <label for="id_curso">Curso</label>
            <select id="id_curso" class="form-control" required>
                <option value="">Seleccione un curso</option>
                <?php foreach($cursos as $curso): ?>
                    <option value="<?= $curso['id_curso'] ?>"><?= $curso['nombre_curso'] ?></option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="id_seccion">Sección</label>
            <select name="id_seccion" id="id_seccion" class="form-control" required>
                <option value="">Seleccione primero un curso</option>
            </select>
        </div>

        <div class="form-group">
            <label for="tipo_evaluacion">Tipo de Evaluación</label>
            <input type="text" name="tipo_evaluacion" id="tipo_evaluacion" class="form-control" required>
        </div>

        <div class="form-group">
            <label for="obj">Objetivo</label>
            <textarea name="obj" id="obj" rows="3" class="form-control" required></textarea>
        </div>

        <div class="form-group">
            <label for="porcentaje">Porcentaje (%)</label>
            <input type="number" name="porcentaje" id="porcentaje" class="form-control" min="1" max="100" required>
        </div>

        <div class="form-group">
            <label for="fecha_evaluacion">Fecha</label>
            <input type="date" name="fecha_evaluacion" id="fecha_evaluacion" class="form-control" required>
        </div>

        <div class="text-center mt-4">
            <button type="submit" class="btn btn-primary">Guardar Evaluación</button>
        </div>
    </form>
</div>

<script>
// Script para cargar secciones del curso seleccionado
document.getElementById('id_curso').addEventListener('change', function () {
    const cursoId = this.value;
    const seccionSelect = document.getElementById('id_seccion');

    seccionSelect.innerHTML = '<option>Cargando...</option>';

    fetch('<?= SERVERURL ?>ajax/seccionesPorCursoCDE.php?id=' + cursoId)
        .then(res => res.json())
        .then(data => {
            seccionSelect.innerHTML = '<option value="">Seleccione una sección</option>';
            data.forEach(sec => {
                const option = document.createElement('option');
                option.value = sec.id_seccion;
                option.textContent = sec.nombre_seccion;
                seccionSelect.appendChild(option);
            });
        });
});
</script>