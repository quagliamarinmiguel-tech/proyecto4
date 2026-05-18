<?php
    if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm']!=4){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-link fa-fw"></i> &nbsp; CURSOS TERMINADOS POR COHORTE
    </h3>
    <p class="text-justify">
        Visualizá las asociaciones de cursos activos con cohortes específicas.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-new">
                <i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO VIGENTE
            </a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-list">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS VIGENTES
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-search">
                <i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR CURSO VIGENTE
            </a>
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/cursoVigenteControlador.php";
        $ins_curso_vigente = new cursoVigenteControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        echo $ins_curso_vigente->paginador_curso_vigente_finalizado_controlador($pagina_actual, 15, "curso-terminado-list", "");
    ?>
</div>