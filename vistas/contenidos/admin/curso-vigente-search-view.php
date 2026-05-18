<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<?php if(!isset($_SESSION['busqueda_nombre_curso'])){ ?>
<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/buscadorAjax.php" method="POST" data-form="default" autocomplete="off">
        <input type="hidden" name="modulo" value="curso_vigente">

        <div class="row justify-content-md-center">
            <div class="col-md-4 form-group">
                <label for="curso_nombre_busqueda" class="bmd-label-floating">Nombre del curso</label>
                <input type="text" class="form-control" name="busqueda_nombre_curso" id="curso_nombre_busqueda" required>
            </div>
            <div class="col-md-4 form-group">
                <label for="curso_tipo_busqueda" class="bmd-label-floating">Tipo de curso</label>
                <select class="form-control" name="busqueda_tipo_curso" id="curso_tipo_busqueda">
                    <option value="">(Opcional)</option>
                    <option value="Intensivo">Intensivo</option>
                    <option value="Extendido">Extendido</option>
                </select>
            </div>
            <div class="col-12 text-center mt-4">
                <button type="submit" class="btn btn-info btn-sm"><i class="fas fa-search"></i> Buscar</button>
            </div>
        </div>
    </form>
</div>
<?php } else { ?>

<form class="FormularioAjax" action="<?php echo SERVERURL; ?>ajax/buscadorAjax.php" method="POST" data-form="search" autocomplete="off">
    <input type="hidden" name="modulo" value="curso_vigente">
    <input type="hidden" name="eliminar_busqueda" value="eliminar">
    <p class="text-center">
        Buscando curso: <strong><?php echo $_SESSION['busqueda_nombre_curso']; ?></strong>
        <?php if(isset($_SESSION['busqueda_tipo_curso']) && $_SESSION['busqueda_tipo_curso'] != ""): ?>
            <br><small>Tipo: <strong><?php echo $_SESSION['busqueda_tipo_curso']; ?></strong></small>
        <?php endif; ?>
    </p>
    <div class="text-center mt-3">
        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-times"></i> Eliminar búsqueda</button>
    </div>
</form>

<?php
    require_once "./controladores/cursoVigenteControlador.php";
    $ins_curso_vigente = new cursoVigenteControlador();
    $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
    echo $ins_curso_vigente->paginador_curso_vigente_search_controlador(
        $pagina_actual,
        15,
        "curso-vigente-search",
        $_SESSION['busqueda_nombre_curso'],
        $_SESSION['busqueda_tipo_curso'] ?? ''
    );
?>
<?php } ?>