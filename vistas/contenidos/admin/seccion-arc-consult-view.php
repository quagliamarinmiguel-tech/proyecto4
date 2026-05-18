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








    $curso_vigente_id = isset($_GET['curso_vigente']) ? $_GET['curso_vigente'] : 0;
    $seccion_letra = isset($_GET['seccion']) ? $_GET['seccion'] : '';

    if($curso_vigente_id == 0 || $seccion_letra == ''){
        echo '<div class="alert alert-danger text-center">Parámetros inválidos. No se puede cargar la sección.</div>';
        exit();
    }

    require_once "./controladores/seccionControlador.php";
    $ins_seccion = new seccionControlador();
   // $datos_seccion = $ins_seccion->obtener_datos_seccion_archivada_controlador($curso_vigente_id, $seccion_letra);
    $datos_seccion = $ins_seccion->obtener_datos_seccion_archivada_controlador_para_ambos_casos($curso_vigente_id, $seccion_letra);
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; SECCIÓN ARCHIVADA <?php echo strtoupper($seccion_letra); ?>
    </h3>
    <p class="text-justify">
        Detalles de la sección archivada, evaluaciones registradas y desempeño de los estudiantes.
    </p>
</div>

<?php
    $volver = "seccion-list";
    if (isset($_GET['a2']) && $_GET['a2'] == '1') {
        $volver = "seccion-archived";
    }
?>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
	   <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/' . urlencode($volver); ?>">
    <!--a href="<?php echo SERVERURL; ?>index.php?views=<?php echo $volver; ?>"-->
                <i class="fas fa-arrow-left fa-fw"></i> &nbsp; VOLVER A LA SECCIÓN
            </a>
        </li>
    </ul>   
</div>

<!-- 🔹 Datos generales de la sección -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-md-6">
            <p><strong>ID de sección:</strong> <?php echo $datos_seccion['id_seccion']; ?></p>
            <p><strong>Curso:</strong> <?php echo $datos_seccion['nombre_curso']; ?></p>
            <p><strong>ID del periodo del curso:</strong> <?php echo $datos_seccion['id_curso_vigente']; ?></p>
            <!--p><strong>Cohorte:</strong> <php echo $datos_seccion['numero_cohorte']." / ".$datos_seccion['anio_cohorte']; ?></p-->
          
            
            
                            <?php if (!is_null($datos_seccion['numero_cohorte']) && !is_null($datos_seccion['anio_cohorte'])): ?>
        <p><strong>Cohorte:</strong> <?php echo $datos_seccion['numero_cohorte'] . " / " . $datos_seccion['anio_cohorte']; ?></p>
    <?php else: ?>
        <p><strong>Fecha de inicio del periodo intensivo:</strong> <?php echo $datos_seccion['fecha_inicio_int']; ?></p>
        <p><strong>Fecha de culminación del periodo intensivo:</strong> <?php echo $datos_seccion['fecha_fin_int']; ?></p>
    <?php endif; ?>
            
            
            
            
            <p><strong>Profesor:</strong> <?php echo $datos_seccion['nombre_profesor']." ".$datos_seccion['apellido_profesor']; ?></p>    
        </div>

        <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
        <div class="col-md-6">
            <!-- Botón para restaurar sección -->
            <form action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" class="FormularioAjax mt-3" data-form="restore" data-entity="seccion">
                <input type="hidden" name="id_seccion_restore" value="<?php echo $datos_seccion['id_seccion']; ?>">
                <button type="submit" class="btn btn-info">Restaurar sección</button>
            </form>    
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- 🔹 Evaluaciones registradas -->
<div class="container-fluid mb-4">
    <h4 class="mb-3">Evaluaciones registradas</h4>
    <?php
        $pagina_eval = isset($_GET['eval_page']) ? (int)$_GET['eval_page'] : 1;
        echo $ins_seccion->paginador_evaluacion_archivada_controlador($pagina_eval, 10, $datos_seccion['id_seccion']);
    ?>
</div>

<!-- 🔹 Estudiantes con puntajes -->
<div class="container-fluid mb-4">
    <h4 class="text-center">Estudiantes y puntajes</h4>
    <?php
        $pagina_est = isset($_GET['est_page']) ? (int)$_GET['est_page'] : 1;
        echo $ins_seccion->paginador_puntaje_archivado_controlador($pagina_est, 10, $datos_seccion['id_seccion']);
    ?>
</div>