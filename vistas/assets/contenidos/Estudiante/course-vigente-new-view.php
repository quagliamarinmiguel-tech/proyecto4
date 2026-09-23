<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

require_once "./modelos/mainModel.php";
$pdo = mainModel::conectar();

$curso_id = $_GET['curso_id'] ?? '';
$cohorte_id = $_GET['cohorte_id'] ?? '';
$tipo = $_GET['tipo'] ?? '';

$cursos = [];
$cohortes = [];
$tipos = [];


$profesores = $pdo->query("
    SELECT id_profesor, nombre_profesor, apellido_profesor
    FROM profesor
    WHERE estado_profesor = 1
    ORDER BY nombre_profesor ASC
")->fetchAll(PDO::FETCH_ASSOC);

if ($curso_id !== '') {
    $stmt = $pdo->prepare("SELECT id_curso, nombre_curso, tipo_curso FROM curso WHERE id_curso = :id");
    $stmt->execute([':id' => $curso_id]);
    $curso = $stmt->fetch();

    if ($curso) {
        $tipo = $curso['tipo_curso'];
    //se podria usar buscar cohortes para cv
        $stmt = $pdo->prepare("
            SELECT id_cohorte, numero_cohorte, anio_cohorte, estado_cohorte, tipo_cohorte
            FROM cohorte
            WHERE tipo_cohorte = :tipo
              AND estado_cohorte IN ('Vigente', 'Próximo')
              AND id_cohorte NOT IN (
                  SELECT id_cohorte_fk
                  FROM curso_vigente
                  WHERE id_curso_fk = :id_curso
              )
            ORDER BY anio_cohorte DESC, numero_cohorte DESC
        ");
        $stmt->bindParam(":tipo", $tipo);
        $stmt->bindParam(":id_curso", $curso['id_curso']);
        $stmt->execute();

        $cohortes = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($cohorte_id !== '') {
    $stmt = $pdo->prepare("SELECT id_cohorte, numero_cohorte, anio_cohorte, tipo_cohorte FROM cohorte WHERE id_cohorte = :id");
    $stmt->execute([':id' => $cohorte_id]);
    $cohorte = $stmt->fetch();

    if ($cohorte) {
        $tipo = $cohorte['tipo_cohorte'];
        $cursos = $pdo->query("
            SELECT id_curso, nombre_curso, tipo_curso
            FROM curso
            WHERE tipo_curso = '$tipo' AND estado_curso = 1
            ORDER BY nombre_curso ASC
        ")->fetchAll(PDO::FETCH_ASSOC);
    }
} elseif ($tipo === '') {
    $tipos = $pdo->query("
        SELECT DISTINCT tipo_curso AS tipo FROM curso WHERE estado_curso = 1
        UNION
        SELECT DISTINCT tipo_cohorte AS tipo FROM cohorte WHERE estado_cohorte IN ('Vigente', 'Próximo')
    ")->fetchAll(PDO::FETCH_COLUMN);
}
?>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-plus fa-fw"></i> &nbsp; Nuevo Periodo de curso</h3>
    <p class="text-justify">Registra un nuevo periodo asignando un curso base, cohorte destino y el docente de la sección "A".</p>
</div>


<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>course-list"><i class="fas fa-plus fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>seccion-list"><i class="fas fa-plus fa-fw"></i> &nbsp; LISTA DE PERIODOS</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>cohorte-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE COHORTES</a>
        </li>

    </ul>   
</div>

<?php if ($curso_id !== '' && isset($curso)): ?>
    <div class="mb-3">
        <a href="http://www.cecilioceproca.wuaze.com/index.php?views=seccion-list&filtro_nombre=<?= $curso_id ?>&origen=curso" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al curso
        </a>
    </div>
<?php endif; ?>

<?php if ($cohorte_id !== '' && isset($cohorte)): ?>
    <div class="mb-3">
        <!--http://www.cecilioceproca.wuaze.com/index.php?views=course-vigente-new&cohorte_id=<?= $cohorte_id ?>&origen=cohorte-->		 
        
        <a href="http://www.cecilioceproca.wuaze.com/index.php?views=seccion-list&filtro_id_cohorte=<?= $cohorte_id ?>&origen=cohorte" class="btn btn-secondary btn-sm">
            <i class="fas fa-arrow-left"></i> Volver al cohorte
        </a>
    </div>
<?php endif; ?>

<div class="container-fluid">
    <?php if ($curso_id !== '' && isset($curso)): ?>
        <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoVigenteAjax.php" method="POST" data-form="save" autocomplete="off">
            <input type="hidden" name="curso_id_fk_reg" value="<?= $curso_id ?>">
            <div class="alert alert-secondary mb-3">
                <strong>ID del curso:</strong> <?= $curso_id ?>
            </div>

            <fieldset>
                <legend><i class="fas fa-book fa-fw"></i> &nbsp; Información del periodo</legend>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Curso base</label>
                        <input type="text" class="form-control" value="<?= $curso['nombre_curso'] ?> (<?= $curso['tipo_curso'] ?>)" readonly>
                    </div>
                    <div class="col-md-10 mb-2">
                        <label class="form-label">Cohorte </label>
                        <select name="id_cohorte_fk_reg" class="form-select col-md-6" required>
                            <option value="" selected disabled>Seleccione...</option>
                            <?php foreach ($cohortes as $c): ?>
                                <option value="<?= $c['id_cohorte'] ?>">
                                    <?= $c['numero_cohorte'] . '.' . $c['anio_cohorte'] ?> (<?= $c['estado_cohorte'] ?> - <?= $c['tipo_cohorte'] ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </fieldset>
            <fieldset>
    <!--legend><i class="fas fa-user-tie"></i> &nbsp; Docente y </legend-->
    <div class="row">
        <div class="col-md-6 mb-2">
            <label class="form-label">Docente de la sección "A"</label>
            <!--select name="id_profesor_fk_reg" id="profesor_select" class="form-select col-md-8" required>
                <option value="" selected disabled>Seleccione un profesor...</option>
                <php foreach ($profesores as $p): ?>
                <option value="<?= $p['id_profesor'] ?>">
                    <?= $p['nombre_profesor'] . ' ' . $p['apellido_profesor'] ?>
                </option>
                <php endforeach; ?>
            </select-->
            <select name="id_profesor_fk_reg" id="profesor_select" class="form-select col-md-8" required>
              <option value="" selected disabled>Buscar profesor...</option>
            </select>
        </div>
    </div>
    <p class="text-center" style="margin-top: 30px;">
        <button type="submit" class="btn btn-raised btn-info btn-sm">
            <i class="far fa-save"></i> &nbsp; GUARDAR
        </button>
    </p>
</fieldset>
        </form>

    <?php elseif ($cohorte_id !== '' && isset($cohorte)): ?>
        <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cursoVigenteAjax.php" method="POST" data-form="save" autocomplete="off">
            <input type="hidden" name="id_cohorte_fk_reg" value="<?= $cohorte_id ?>">
            <div class="alert alert-secondary mb-3">
                <strong>ID del cohorte:</strong> <?= $cohorte_id ?>
            </div>

            <fieldset>
                <legend><i class="fas fa-book fa-fw"></i> &nbsp; Información del periodo</legend>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Cohorte destino</label>
                        <input type="text" class="form-control" value="<?= $cohorte['numero_cohorte'] . '.' . $cohorte['anio_cohorte'] ?> (<?= $cohorte['tipo_cohorte'] ?>)" readonly>
                    </div>
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Curso base</label>
                        <!--select name="curso_id_fk_reg" class="form-select" required>
                            <option value="" selected disabled>Seleccione...</option>
                            <php foreach ($cursos as $c): ?>
                                <option value="<?= $c['id_curso'] ?>">
                                    <?= $c['nombre_curso'] ?> (<?= $c['tipo_curso'] ?>)
                                </option>
                            <php endforeach; ?>
                        </select-->
                         <select name="curso_id_fk_reg" id="curso_select" class="form-select col-md-8" required>
                          <option value="" selected disabled>Buscar curso...</option>
                        </select>
                    </div>
                </div>
            </fieldset>
    <div class="row">
        <div class="col-md-6 mb-2">
            <label class="form-label">Docente de la sección "A"</label>
            <!--select name="id_profesor_fk_reg" id="profesor_select" class="form-select col-md-8" required>
                <option value="" selected disabled>Seleccione un profesor...</option>
                <php foreach ($profesores as $p): ?>
                <option value="<?= $p['id_profesor'] ?>">
                    <?= $p['nombre_profesor'] . ' ' . $p['apellido_profesor'] ?>
                </option>
                <php endforeach; ?>
            </select-->
            <select name="id_profesor_fk_reg" id="profesor_select" class="form-select col-md-8" required>
              <option value="" selected disabled>Buscar profesor...</option>
            </select>

            
        </div>
    </div>
            <p class="text-center" style="margin-top: 30px;">
                <button type="submit" class="btn btn-raised btn-info btn-sm">
                    <i class="far fa-save"></i> &nbsp; GUARDAR
                </button>
            </p>

    </form>

    <?php elseif ($tipo === ''): ?>
        <form method="GET" autocomplete="off">
            <fieldset>
                <legend><i class="fas fa-filter"></i> &nbsp; Selección de tipo</legend>
                <div class="row">
                    <div class="col-md-6 mb-2">
                        <label class="form-label">Seleccione tipo</label>
                        <select name="tipo" class="form-select" required onchange="this.form.submit()">
                            <option value="" selected disabled>Seleccione tipo...</option>
                            <?php foreach ($tipos as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
            </fieldset>
            <p class="text-center" style="margin-top: 30px;">
    <button type="submit" class="btn btn-raised btn-info btn-sm">
        <i class="far fa-save"></i> &nbsp; GUARDAR
    </button>
</p>

        </form>
    <?php endif; ?>
</div>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<script>
$(document).ready(function() {
  $('#profesor_select').select2({
    placeholder: "Buscar profesor...",
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
  $('#curso_select').select2({
    placeholder: "Buscar curso...",
    allowClear: true,
    minimumInputLength: 2,
    ajax: {
      url: "<?php echo SERVERURL; ?>ajax/select2Ajax.php?accion=buscar_curso_por_cohorte&id_cohorte=<?= $cohorte_id ?>",
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
