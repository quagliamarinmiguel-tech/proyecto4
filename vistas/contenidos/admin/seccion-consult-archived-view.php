<?php
    $state = $_SESSION['profile_status'] ?? 0;
    $cargo = $_SESSION['cargo_spm'] ?? 0;
    $carpeta = "admin";

    if (!in_array($cargo, [1, 2, 4], true) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    // Ahora se recibe el id de la sección (igual que seccion-consult activa)
    $sectionId = (int) ($_GET['seccion_id'] ?? 0);

    if ($sectionId <= 0) {
        echo '<div class="alert alert-danger text-center">Parámetros inválidos. No se puede cargar la sección.</div>';
        exit();
    }

    require_once "./controladores/seccionControlador.php";
    $ctrl = new seccionControlador();


    // sección, periodo, curso, profesor, evaluaciones activas
    $datos = $ctrl->obtener_datos_para_seccion_consulta($sectionId,0);
    if (!$datos['ok']) {
        echo '<div class="alert alert-danger text-center">' . htmlspecialchars($datos['message']) . '</div>';
        exit();
    }

    // Extraer variables
    $seccion           = $datos['seccion'];
    $period            = $datos['period'];
    $course            = $datos['course'];
    $teacher           = $datos['teacher'];

$profearchivado="";
if ($teacher['state'] == 0){$profearchivado="(Archivado)";}

    $evaluacionesCount = $datos['evaluaciones_count'];
    $totalPorcentaje   = $datos['total_porcentaje'];

    // Derivadas (para no romper los enlaces que usan $periodCourseId)
    $periodCourseId = $seccion['period_course_id'] ?? 0;
    $sectionLetter  = $seccion['name'] ?? '';

    $mostrarExportar = (abs($totalPorcentaje - 100.0) < 0.001);
?>
<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-users fa-fw"></i> &nbsp; SECCIÓN ARCHIVADA "<?php echo strtoupper($sectionLetter); ?>"
    </h3>
    <p class="text-justify">
        Detalles de la sección  archivada y evaluaciones registradas.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE SECCIONES DE CURSOS</a></li>
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/period-course-list-archived'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE SECCIONES DE CURSOS ARCHIVADOS</a></li>
    </ul>
</div>


<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-md-6">
            <p><strong>ID de sección:</strong> <?php echo $seccion['id']; ?></p>
            <p><strong>Curso:</strong> <?php echo htmlspecialchars($course['name'] ?? ''); ?></p>
            <p><strong>ID del periodo del curso:</strong> <?php echo $periodCourseId; ?></p>
            <p><strong>Periodo:</strong> <?php echo htmlspecialchars($period['name'] ?? ''); ?></p>
            <p><strong>Tipo:</strong> <?php echo htmlspecialchars($period['type'] ?? ''); ?></p>
            <p><strong>Fechas:</strong> <?php echo $period['start_date'] ?? ''; ?> al <?php echo $period['end_date'] ?? ''; ?></p>
            <p><strong>Día:</strong> <?php echo $seccion['section_day']; ?></p>

        	<p>
                <strong>Profesor:</strong> 
                <?= $teacher 
                    ? htmlspecialchars($teacher['name'] . ' ' . $teacher['last_name']) 
                    : 'Sin asignar' ?>
                <?php if ($profearchivado !== ''): ?>
                    <span class="badge badge-warning" style="background-color:#BA3116;"><?= $profearchivado ?></span>
                <?php endif; ?>
            </p>
        
        </div>

        
        <?php if (($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4) && $sectionLetter!='A' ): ?>
        <div class="col-md-6">
            
            <form class="FormularioAjax d-inline" action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" data-form="delete2" data-entity="seccion" autocomplete="off">
                  <input type="hidden" name="id_seccion_eliminar" value="<?php echo $sectionId; ?>">
                  <button type="submit" class="btn btn-warning">Eliminar sección</button>
            </form>
            
        </div>
        <?php endif; ?>
    </div>
</div>



<?php if ($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2): ?>
<!--div class="container-fluid mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
            <h4 class="mb-0">Evaluaciones Archivadas</h4>
    </div-->

<!--php
//$pagina_eval = isset($_GET['eval_page']) ? (int)$_GET['eval_page'] : 1;
//echo $ctrl->paginador_evaluaciones_controlador($pagina_eval, 10, $seccion['id'],0);   
?-->
   
</div>
<?php endif; ?>



<div class="container-fluid mb-4">
    		<h4 class="text-center">Estudiantes </h4>
    <?php
    $pagina_est = isset($_GET['est_page']) ? (int)$_GET['est_page'] : 1;
    echo $ctrl->paginador_estudiantes_con_puntajes_controlador($pagina_est, 10, $seccion['id'],0);
    ?>
</div>


