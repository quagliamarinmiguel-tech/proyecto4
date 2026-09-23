<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<?php
require_once __DIR__ . "/../../../controladores/calificacionControlador.php";
$controlador = new calificacionControlador();
$datos = $controlador->datos_calificacion_controlador($_GET['id_ins'], $_GET['id_ev']);
$row = $datos->fetch(PDO::FETCH_ASSOC);
?>

<div class="container mt-5">
  <h2 class="mb-4 text-center">Editar Calificación</h2>
  <form class="FormularioAjax" method="POST" data-form="update"
        action="<?php echo SERVERURL; ?>ajax/calificacionAjax.php" autocomplete="off">

    <input type="hidden" name="id_inscripcion_up" value="<?= $row['id_inscripcion'] ?>">
    <input type="hidden" name="id_evaluacion_up" value="<?= $row['id_evaluacion'] ?>">

    <div class="form-group">
      <label>Inscripción</label>
      <input type="text" class="form-control" value="<?= $row['id_inscripcion'] ?>" readonly>
    </div>

    <div class="form-group">
      <label>Evaluación</label>
      <input type="text" class="form-control" value="<?= $row['tipo_evaluacion'] ?>" readonly>
    </div>

    <div class="form-group">
      <label>Puntaje</label>
      <input type="number" class="form-control" name="puntaje_up"
             value="<?= $row['puntaje'] ?>" min="0" max="100" required>
    </div>

    <button type="submit" class="btn btn-warning">Actualizar</button>
    <div class="RespuestaAjax mt-3"></div>
  </form>
</div>
