
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



    require_once "./controladores/seccionControlador.php";
    $ins_seccion = new seccionControlador();

    $curso_vigente_id = $_GET['curso_vigente'] ?? '';
    $letras = range('A', 'Z');
    $letra_seccion = '';
    $bloquear = false;

    $conecccion = mainModel::conectar();
    $curso_id = '';
    $curso_nombre = '';
    $curso_tipo = '';
    $cohorte_id = '';
    $numero_cohorte = '';
    $anio_cohorte = '';


        $stmt = $conecccion->prepare("                        
SELECT 
    c.nombre_curso,
    cv.id_curso_fk,
    cv.id_cohorte_fk,
    ch.numero_cohorte,
    ch.anio_cohorte,
    c.tipo_curso,
    pi.id_periodo_intensivo,
    pi.fecha_inicio_int,
    pi.fecha_fin_int
FROM curso_vigente cv
INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
LEFT JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
LEFT JOIN periodo_intensivo pi ON pi.id_curso_vigente_fk = cv.id_curso_vigente
WHERE cv.id_curso_vigente = :id
LIMIT 1
            
            
        ");
        $stmt->execute([':id' => $curso_vigente_id]);
        $info = $stmt->fetch();

        if ($info) {
            $curso_id = $info['id_curso_fk'];
            $curso_nombre = $info['nombre_curso'];
            $curso_tipo = $info['tipo_curso'];
            $cohorte_id = $info['id_cohorte_fk'];
            $numero_cohorte = $info['numero_cohorte'];
            $anio_cohorte = $info['anio_cohorte'];
        }
    




    if ($curso_vigente_id !== '') {
        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare("SELECT COUNT(*) FROM seccion WHERE id_curso_vigente_fk = :id");
        $stmt->execute([':id' => $curso_vigente_id]);
        $total = (int) $stmt->fetchColumn();
        $letra_seccion = $letras[$total] ?? 'Z';
        $bloquear = $total >= count($letras);
    }

    $profesores = $ins_seccion->obtener_profesores_activos_controlador();
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-layer-group fa-fw"></i> &nbsp; NUEVA SECCIÓN
    </h3>
    <p class="text-justify">
        Crea una nueva sección para un curso intensivo o un periodo de curso de un cohorte. El nombre se asignará automáticamente.
    </p>
</div>


<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">

        <li>
            <a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-new'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; NUEVA SECCIÓN </a>
        </li>        
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS REGULARES </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS INTENSIVOS </a>
        </li>
              
    </ul>   
</div>

<div class="container-fluid">
    

<div class="container-fluid">
    <p class="text-justify text-muted">
        <strong>Curso:</strong> <?= htmlspecialchars($curso_nombre) ?> 
        &nbsp; <em>(<?= $curso_tipo ?>)</em> 
        &nbsp; <span class="text-muted">ID: <?= $curso_id ?></span>
    </p>

    <?php if ($cohorte_id): ?>
        <p class="text-justify text-muted">
            <strong>Cohorte:</strong> <?= $numero_cohorte ?> / <?= $anio_cohorte ?> 
            &nbsp; <em>(ID: <?= $cohorte_id ?>)</em>
        </p>
    <?php elseif (!empty($info['id_periodo_intensivo'])): ?>
        <p class="text-justify text-muted">
            <strong>Periodo Intensivo:</strong> 
            <?= $info['fecha_inicio_int'] ?> al <?= $info['fecha_fin_int'] ?> 
            &nbsp; <em>(ID: <?= $info['id_periodo_intensivo'] ?>)</em>
        </p>
    <?php endif; ?>
</div>
    
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" data-form="save" autocomplete="off">
        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Datos de la sección</legend>
            <div class="container-fluid">
                <div class="row">

                    <!-- Curso vigente -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_curso_vigente" class="bmd-label-floating">Periodo id</label>
                            <input type="hidden" name="id_curso_vigente" value="<?= $curso_vigente_id ?>">
                            <input type="text" class="form-control" value="ID: <?= $curso_vigente_id ?>" readonly>
                        </div>
                    </div>

                    <!-- Nombre de sección -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="nombre_seccion" class="bmd-label-floating">Nombre de sección</label>
                            <input type="text" name="nombre_seccion" id="nombre_seccion" class="form-control" value="<?= $letra_seccion ?>" readonly required>
                        </div>
                    </div>

                </div>

                <div class="row mt-3">

                    <!-- Profesor -->
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="id_profesor_fk" class="bmd-label-floating">Profesor asignado</label>
                         
                            <select name="id_profesor_fk" id="id_profesor_fk" class="form-control select2-ajax" required>
                              <option value="" selected disabled>Seleccione un profesor</option>
                            </select>
                        </div>
                    </div>
                    
                    
                <div class="col-md-2 mb-2 ">
                    <label class="form-label">Día de clases</label> 
                    <select class="form-control"  name="seccion_dia_reg" id="seccion_dia_reg" required>
                        <option value="" selected disabled>Seleccione...</option>
                        <option value="Lunes">Lunes</option>
                        <option value="Martes">Martes</option>
                        <option value="Miércoles">Miércoles</option>
                        <option value="Jueves">Jueves</option>
                        <option value="Viernes">Viernes</option>
                        <option value="Sábado">Sábado</option>
                        <option value="Domingo">Domingo</option>
                    </select>
                </div>


                </div>
            </div>
        </fieldset>

        <?php if ($bloquear): ?>
            <div class="alert alert-danger text-center mt-3">
                Este curso ya tiene el máximo de secciones permitidas (A-Z). No se puede crear una nueva.
            </div>
        <?php endif; ?>

        <p class="text-center" style="margin-top: 40px;">           
            <button type="submit" class="btn btn-raised btn-success btn-sm" <?= $bloquear ? 'disabled' : '' ?>>
                <i class="far fa-save"></i> &nbsp; GUARDAR SECCIÓN
            </button>
        </p>
    </form>
</div>

<!-- Select2 -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
$(document).ready(function() {
  $('#id_profesor_fk').select2({
    placeholder: "Escriba el nombre del profesor",
    allowClear: true,
    width: '100%',
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
<!--script>
  $(document).ready(function() {
    $('.select2').select2({
      placeholder: "Escriba el nombre del profesor",
      allowClear: true,
      width: '100%'
    });
  });
</script-->

