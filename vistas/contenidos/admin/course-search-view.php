<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR CURSO
    </h3>
    <p class="text-justify">
        Utiliza el buscador para encontrar cursos específicos por nombre o ID. Los resultados se mostrarán en una tabla paginada para facilitar la navegación.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=course-new"><i class="fas fa-plus fa-fw"></i> &nbsp; NUEVO CURSO</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=course-list"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE CURSOS</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=course-search"><i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR CURSO</a>
        </li>
    </ul>   
</div>

<?php
    if(!isset($_SESSION['busqueda_curso']) && empty($_SESSION['busqueda_curso'])){
?>

<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/buscadorAjax.php" method="POST" data-form="default" autocomplete="off">
        <input type="hidden" name="modulo" value="curso">
        
        <div class="container-fluid">
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="inputSearch" class="bmd-label-floating">¿Qué curso estás buscando?</label>
                        <input type="text" class="form-control" name="busqueda_inicial" id="inputSearch" maxlength="100" placeholder="Buscar por nombre o ID del curso">
                    </div>
                </div>
                <div class="col-12">
                    <p class="text-center" style="margin-top: 40px;">
                        <button type="submit" class="btn btn-raised btn-info"><i class="fas fa-search"></i> &nbsp; BUSCAR</button>
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<?php }else{ ?>

<div class="container-fluid">
    <form class="FormularioAjax" action="<?php echo SERVERURL; ?>ajax/buscadorAjax.php" method="POST" data-form="search" autocomplete="off">
        <input type="hidden" name="modulo" value="curso">
        <input type="hidden" name="eliminar_busqueda" value="eliminar">
        
        <div class="container-fluid">
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-6">
                    <p class="text-center" style="font-size: 20px;">
                        Resultados de la búsqueda <strong>"<?php echo $_SESSION['busqueda_curso']; ?>"</strong>
                    </p>
                </div>
                <div class="col-12">
                    <p class="text-center" style="margin-top: 20px;">
                        <button type="submit" class="btn btn-raised btn-danger"><i class="far fa-trash-alt"></i> &nbsp; ELIMINAR BÚSQUEDA</button>
                    </p>
                </div>
            </div>
        </div>
    </form>
</div>

<div class="container-fluid">
    <?php
        require_once "./controladores/cursoControlador.php";
        $ins_curso = new cursoControlador();
        
        // Obtener el número de página actual
        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        
        echo $ins_curso->paginador_curso_controlador($pagina_actual, 15, "course-search", $_SESSION['busqueda_curso']);
    ?>
</div>

<?php } ?>
