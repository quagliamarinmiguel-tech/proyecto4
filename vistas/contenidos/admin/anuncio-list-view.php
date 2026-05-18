<?php 
    if($_SESSION['cargo_spm']!=1){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES
    </h3>
    <p class="text-justify">
        Visualiza todas las cohortes registradas en el sistema. Puedes editarlas, ver los cursos que tienen asignados o archivarlas.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO ANUNCIO</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE ANUNCIOS</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-search"><i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR ANUNCIO</a>
        </li>
    </ul>
</div>


<div class="container-fluid">
<!--php
$ad = new anuncioControlador();
echo $ad->paginador_anuncio_controlador($pagina, 10, "anuncio-list", "");
?-->


<?php
require_once "./controladores/anuncioControlador.php";
$ad = new anuncioControlador();
$pagina = isset($_GET['page']) ? (int)$_GET['page'] : 1;
echo $ad->paginador_anuncio_controlador($pagina, 10, "anuncio-list", "");
?>
</div>