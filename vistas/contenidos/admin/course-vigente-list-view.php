<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 && $_SESSION['cargo_spm']!=3 && $_SESSION['cargo_spm']!=2 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    // Desencriptar el ID del curso recibido
$curso_id_encriptado = isset($_GET['curso_id']) ? $_GET['curso_id'] : "";
$curso_id = mainModel::decryption($curso_id_encriptado);

require_once "./controladores/cursoControlador.php";
$cursoCtrl = new cursoControlador();
$cursoDatos = $cursoCtrl->datos_curso_controlador("Unico", $curso_id_encriptado);

    if ($cursoDatos->rowCount() == 1) {
        $curso = $cursoDatos->fetch();
    } else {
        $curso = ["nombre_curso" => "Curso no encontrado", "tipo_curso" => ""];
    }
?>

<!--div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-layer-group fa-fw"></i> &nbsp; CURSOS VIGENTES DEL CURSO BASE
    </h3-->

<div class="full-box page-header">

    <h3 class="text-left">
        <i class="fas fa-layer-group fa-fw"></i> &nbsp; CURSOS VIGENTES DE: 
        <span class="text-primary fw-bold"><?php echo $curso['nombre_curso']; ?></span> 
        <small class="text-muted">(<?php echo $curso['tipo_curso']; ?>)</small>
    </h3>



    <p class="text-justify">
        Aquí puedes visualizar todos los cursos vigentes asociados al curso seleccionado.
    </p>
</div>
<div class="mb-4 text-end">
    <a href="<?php echo SERVERURL; ?>index.php?views=course-vigente-new&curso_id=<?php echo $curso_id_encriptado; ?>" class="btn btn-primary btn-sm">
        <i class="fas fa-plus-circle"></i> &nbsp; Agregar curso vigente
    </a>
</div>

<form method="GET" class="mb-4">
  <input type="hidden" name="views" value="course-vigente-list">
  <input type="hidden" name="curso_id" value="<?php echo $curso_id_encriptado; ?>">

  <div class="form-row align-items-end">
    <div class="col-md-4">
      <label for="filtro_anio">Filtrar por año de cohorte</label>
      <input type="number" name="filtro_anio" id="filtro_anio" class="form-control" min="25" max="40" value="<?= isset($_GET['filtro_anio']) ? $_GET['filtro_anio'] : "" ?>">
    </div>

    <div class="col-md-4">
      <button type="submit" class="btn btn-primary btn-block">
        <i class="fas fa-filter"></i> Aplicar filtros
      </button>
    </div>
  </div>
</form>

<div class="container-fluid">
    <?php
        require_once "./controladores/cohorteControlador.php";
        $ctrl_vigente = new cohorteControlador();

        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;

        echo $ctrl_vigente->paginador_curso_vigente_por_curso_controlador($curso_id, $pagina_actual, 15);
    ?>
</div>