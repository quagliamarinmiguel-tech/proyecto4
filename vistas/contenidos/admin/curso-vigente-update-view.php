<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>
<?php
require_once "./controladores/cursoVigenteControlador.php";
$ctrl = new cursoVigenteControlador();

$id = $_GET['curso_vigente_id_up'] ?? '';
$datos = $ctrl->datos_curso_vigente_controlador("Unico", $id);

if ($datos->rowCount() == 1) {
  $campos = $datos->fetch(PDO::FETCH_ASSOC);
?>

<div class="full-box page-header">
  <h3 class="text-left"><i class="fas fa-sync-alt fa-fw"></i> &nbsp; ACTUALIZAR CURSO VIGENTE #<?= $campos['id_curso_vigente']; ?></h3>
  <p class="text-justify">Modificá los montos asociados a esta relación curso/cohorte. El curso y la cohorte no son modificables.</p>
</div>

<div class="container-fluid">
  <ul class="full-box list-unstyled page-nav-tabs">
    <li>
      <a href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO VIGENTE</a>
    </li>
    <li>
      <a href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS VIGENTES</a>
    </li>
    <li>
      <a class="active" href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-update&curso_vigente_id_up=<?= $id; ?>"><i class="fas fa-sync-alt fa-fw"></i> &nbsp; ACTUALIZAR</a>
    </li>
  </ul>   
</div>

<div class="container-fluid">
  <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoVigenteAjax.php" method="POST"
        data-form="update" autocomplete="off">

    <input type="hidden" name="curso_vigente_id_up" value="<?= $campos['id_curso_vigente']; ?>">

    <!-- Info bloqueada -->
    <fieldset>
      <legend><i class="fas fa-info-circle"></i> &nbsp; Relación actual</legend>
      <div class="row">
        <div class="col-md-6 mb-3">
          <label class="form-label">Curso asignado</label>
          <input type="text" class="form-control" readonly value="<?= htmlspecialchars($campos['nombre_curso']); ?>">
        </div>
        <div class="col-md-6 mb-3">
          <label class="form-label">Cohorte</label>
          <input type="text" class="form-control" readonly value="<?= $campos['numero_cohorte'] . '.' . $campos['anio_cohorte'] . ' ' . ucfirst($campos['tipo_cohorte']); ?>">
        </div>
      </div>
    </fieldset>

    <fieldset>
      <legend><i class="fas fa-coins"></i> &nbsp; Valores Económicos</legend>
      <div class="row">
        <div class="col-md-4 mb-3">
          <label class="form-label">Pago inscripción</label>
          <input type="number" class="form-control" name="pago_inscripcion_up" value="<?= $campos['pago_inscripcion']; ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Pago por módulo</label>
          <input type="number" class="form-control" name="pago_modulo_up" value="<?= $campos['pago_modulo']; ?>" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Oferta (%)</label>
          <input type="number" class="form-control" name="oferta_up" value="<?= $campos['oferta']; ?>">
        </div>
      </div>
    </fieldset>

    <p class="text-center mt-4">
      <button type="submit" class="btn btn-success btn-sm">
        <i class="fas fa-sync-alt"></i> ACTUALIZAR
      </button>
    </p>
  </form>
</div>

<?php 
} else {
  echo '<div class="alert alert-danger text-center" role="alert">
    <i class="fas fa-exclamation-triangle fa-5x"></i>
    <h4 class="alert-heading">¡Ocurrió un error inesperado!</h4>
    <p>No hemos encontrado la relación solicitada.</p>
  </div>';
}
?>