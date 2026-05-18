<?php
    if($_SESSION['cargo_spm']!=1){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS
    </h3>
    <p class="text-justify">
        Aquí puedes visualizar todos los cursos registrados en el sistema, editarlos o eliminarlos.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>course-new/"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>course-list/"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>course-search/"><i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR CURSO</a>
        </li>
    </ul>   
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/cursoControlador.php";
        $ins_curso = new cursoControlador();

        echo $ins_curso->paginador_curso_controlador($pagina[1],15,$pagina[0],"");
    ?>
</div>
