<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<?php
    // Obtener el ID de la cohorte desde el parámetro
    //$id_cohorte = $_GET['cohorte_id'] ?? '';
    // Capturar y desencriptar el ID de la cohorte
$id_cohorte_encrypted = $_GET['cohorte_id'] ?? '';
$id_cohorte = mainModel::decryption($id_cohorte_encrypted);
?>




<div class="full-box page-header">
    <h3 class="text-left">
        <!--i class="fas fa-layer-group fa-fw"></i> &nbsp; CURSOS ASIGNADOS A LA COHORTE-->
        <i class="fas fa-layer-group fa-fw"></i> &nbsp; CURSOS ASIGNADOS A LA COHORTE #<?php echo htmlspecialchars($id_cohorte); ?>
    </h3>
    <p class="text-justify">
        Aquí se muestran todos los cursos vigentes asociados a la cohorte seleccionada. Para modificar sus datos, utilizá las opciones del listado.
    </p>
</div>

<!--div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li><a href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-arrow-left fa-fw"></i> &nbsp; VOLVER A COHORTES</a></li>
    </ul>
</div-->

<div class="container-fluid">
    <?php
        require_once "./controladores/cursoVigenteControlador.php";
        $ins_curso_vigente = new cursoVigenteControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        echo $ins_curso_vigente->paginador_curso_vigente_por_cohorte_controlador($pagina_actual, 15, "curso-cohorte-view", $id_cohorte);
    ?>
</div>