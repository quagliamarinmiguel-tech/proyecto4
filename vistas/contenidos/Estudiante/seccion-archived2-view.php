<?php  
    if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    // Validar parámetro obligatorio
    if (!isset($_GET['curso_vigente']) || !is_numeric($_GET['curso_vigente'])) {
        echo '<div class="alert alert-danger text-center">No se especificó el curso vigente.</div>';
        exit();
    }

    $curso_vigente_id = (int) $_GET['curso_vigente'];
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; SECCIONES ARCHIVADAS
    </h3>
    <p class="text-justify">
        Esta vista muestra todas las secciones archivadas del curso vigente seleccionado. 
    </p>
</div>


<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">

        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=seccion-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS DE CURSOS</a>
        </li>

    </ul>   
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/seccionControlador.php";
        $ins_seccion = new seccionControlador();

        $pagina = isset($_GET['page']) ? (int) $_GET['page'] : 1;

        // Llamada al paginador con solo_archivadas activado
        echo $ins_seccion->paginador_seccion_archivadas_controlador22($pagina, 10, $curso_vigente_id);
    ?>
</div>






