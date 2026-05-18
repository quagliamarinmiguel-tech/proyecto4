<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-archive fa-fw"></i> &nbsp; CURSOS VIGENTES ARCHIVADOS
    </h3>
    <p class="text-justify">
        Visualizá las relaciones curso-cohorte que fueron archivadas. Podés restaurarlas si es necesario.
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
            <a href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-list">
                <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS VIGENTES
            </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-search">
                <i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR CURSO VIGENTE
            </a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=curso-vigente-archived">
                <i class="fas fa-archive fa-fw"></i> &nbsp; RELACIONES ARCHIVADAS
            </a>
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/cursoVigenteControlador.php";
        $ins_archivados = new cursoVigenteControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_archivados->paginador_curso_vigente_archivado_controlador($pagina_actual, 15, "curso-vigente-archived", "");
    ?>
</div>