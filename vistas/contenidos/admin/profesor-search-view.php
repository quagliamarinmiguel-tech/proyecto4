<?php
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm'] != 4){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR PROFESOR
    </h3>
    <p class="text-justify">
        Utiliza el buscador para encontrar profesores por nombre, cédula o especialidad. Los resultados aparecerán en una tabla paginada.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=profesor-new"><i class="fas fa-user-plus fa-fw"></i> &nbsp; NUEVO PROFESOR</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=profesor-list"><i class="fas fa-users fa-fw"></i> &nbsp; LISTA DE PROFESORES</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=profesor-search"><i class="fas fa-search fa-fw"></i> &nbsp; BUSCAR PROFESOR</a>
        </li>
    </ul>
</div>

<?php
    if(!isset($_SESSION['busqueda_profesor']) || empty($_SESSION['busqueda_profesor'])){
?>

<!-- Formulario de búsqueda -->
<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/buscadorAjax.php" method="POST" data-form="default" autocomplete="off">
        <input type="hidden" name="modulo" value="profesor">
        
        <div class="container-fluid">
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-6">
                    <div class="form-group">
                        <label for="inputSearch" class="bmd-label-floating">¿Qué profesor estás buscando?</label>
                        <input type="text" class="form-control" name="busqueda_inicial" id="inputSearch" maxlength="100" placeholder="Buscar por nombre, cédula o especialidad">
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

<?php } else { ?>

<!-- Mostrar resultados y botón para eliminar búsqueda -->
<div class="container-fluid">
    <form class="FormularioAjax" action="<?php echo SERVERURL; ?>ajax/buscadorAjax.php" method="POST" data-form="search" autocomplete="off">
        <input type="hidden" name="modulo" value="profesor">
        <input type="hidden" name="eliminar_busqueda" value="eliminar">

        <div class="container-fluid">
            <div class="row justify-content-md-center">
                <div class="col-12 col-md-6">
                    <p class="text-center" style="font-size: 20px;">
                        Resultados de la búsqueda <strong>"<?php echo $_SESSION['busqueda_profesor']; ?>"</strong>
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

<!-- Resultados paginados -->
<div class="container-fluid">
    <?php
        require_once "./controladores/profesorControlador.php";
        $ins_profesor = new profesorControlador();
        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ins_profesor->paginador_profesor_controlador($pagina_actual, 15, "profesor-search", $_SESSION['busqueda_profesor']);
    ?>
</div>

<?php } ?>