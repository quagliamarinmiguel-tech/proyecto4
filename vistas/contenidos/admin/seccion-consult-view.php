<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 && $_SESSION['cargo_spm']!=2 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    $curso_vigente_id = isset($_GET['curso_vigente']) ? $_GET['curso_vigente'] : 0;
    $seccion_letra = isset($_GET['seccion']) ? $_GET['seccion'] : '';

    if($curso_vigente_id == 0 || $seccion_letra == ''){
        echo '<div class="alert alert-danger text-center">Parámetros inválidos. No se puede cargar la sección.</div>';
        exit();
    }

    require_once "./controladores/periodoControlador.php";
    $ins_seccion = new periodoControlador();
    $datos_seccion = $ins_seccion->obtener_datos_seccion_controlador_para_ambos_casos($curso_vigente_id, $seccion_letra);
    $profesores = $ins_seccion->obtener_profesores_activos_controlador();


    $mostrarExportar = false;
    $total_porcentaje = (float) ($datos_seccion['total_porcentaje'] ?? 0);
    $mostrarExportar = ($total_porcentaje === 100.0);

$cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";

?>


<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-users fa-fw"></i> &nbsp; SECCIÓN <?php echo strtoupper($seccion_letra); ?>
    </h3>
    <p class="text-justify">
        Detalles de la sección y evaluaciones registradas.
    </p>
</div>






<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS COHORTES </a>
        </li>
        <li>
            <a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list'; ?>"><i class="fas fa-clipboard-list fa-fw"></i> &nbsp; LISTA DE PERIODOS INTENSIVOS </a>
        </li>
    </ul>   
</div>




<!-- Datos generales de la sección -->
<div class="container-fluid mb-4">
    <div class="row">
        <div class="col-md-6">
            <p><strong>ID de sección:</strong> <?php echo $datos_seccion['id_seccion']; ?></p>
            <p><strong>Curso:</strong> <?php echo $datos_seccion['nombre_curso']; ?></p>
            <p><strong>ID del periodo del curso:</strong> <?php echo $datos_seccion['id_curso_vigente']; ?></p>
                <?php if (!is_null($datos_seccion['numero_cohorte']) && !is_null($datos_seccion['anio_cohorte'])): ?>
        <p><strong>Cohorte:</strong> <?php echo $datos_seccion['numero_cohorte'] . " / " . $datos_seccion['anio_cohorte']; ?></p>
    <?php else: ?>
        <p><strong>Fecha de inicio del periodo intensivo:</strong> <?php echo $datos_seccion['fecha_inicio']; ?></p>
        <p><strong>Fecha de culminación del periodo intensivo:</strong> <?php echo $datos_seccion['fecha_fin']; ?></p>
    <?php endif; ?>


            
            <p><strong>Día:</strong> <?php echo $datos_seccion['dia']; ?></p>    
            <p><strong>Profesor:</strong> <?php echo $datos_seccion['nombre_profesor']." ".$datos_seccion['apellido_profesor']; ?></p>    
        </div>
        
        <?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 4): ?>
        <div class="col-md-6">
            <!-- Formulario para modificar profesor -->
            <form action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" class="FormularioAjax" autocomplete="off">
                <input type="hidden" name="id_seccion_up" value="<?php echo $datos_seccion['id_seccion']; ?>">
                <div class="form-group">
                    <label for="profesor_up">Modificar profesor</label>
                                    
                    <select name="profesor_up" class="form-control select2" required>
                        <option value="">Seleccione un profesor</option>
                        <?php
                            foreach($profesores as $row){
                                $selected = ($row['id_profesor'] == $datos_seccion['id_profesor']) ? 'selected' : '';
                                echo "<option value='{$row['id_profesor']}' {$selected}>{$row['nombre_profesor']} {$row['apellido_profesor']}</option>";
                            }
                        ?>
                    </select>                
                
                </div>
                    <div class="form-group">
                        <label for="seccion_dia_up">Día de clases</label>
                        <select name="seccion_dia_up" class="form-control" required>
                            <option value="" disabled>Seleccione un día</option>
                            <?php
                            $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes', 'Sábado', 'Domingo'];
                            $dia_actual = $datos_seccion['dia'] ?? ''; 

                            foreach($dias as $dia){
                                $selected = ($dia == $dia_actual) ? 'selected' : '';
                                echo "<option value='{$dia}' {$selected}>{$dia}</option>";
                            }
                            ?>
                        </select>
                    </div>
                <button type="submit" class="btn btn-success mt-2">Actualizar</button>
            </form>


            <!-- Botón para archivar sección -->
            <form action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" class="FormularioAjax mt-3" data-form="delete" data-entity="seccion">
                <input type="hidden" name="id_seccion_archivar" value="<?php echo $datos_seccion['id_seccion']; ?>">
                <button type="submit" class="btn btn-warning">Archivar sección</button>
            </form>    
        </div>
    <?php endif; ?>

    
    </div>
</div>




<?php if($_SESSION['cargo_spm'] == 1 || $_SESSION['cargo_spm'] == 2): ?>

    <?php if ($mostrarExportar): ?>
        <a href="<?php echo SERVERURL; ?>exportadores/exportar-excel-phpspreadsheet1.php?curso_vigente=<?php echo $curso_vigente_id; ?>&seccion=<?php echo $seccion_letra; ?>" 
        class="btn btn-success" target="_blank">
        Exportar a Excel
        </a>
        <a href="<?php echo SERVERURL; ?>exportadores/exportar_pdf_seccion.php?curso_vigente=<?php echo $curso_vigente_id; ?>&seccion=<?php echo $seccion_letra; ?>"
        class="btn btn-danger" target="_blank" rel="noopener">
        <i class="fa fa-file-pdf-o" aria-hidden="true"></i> Exportar a PDF
        </a>
    <?php endif; ?>


<div class="container-fluid mb-4">
    <div class="d-flex justify-content-between align-items-center mb-2">
        <h4 class="mb-0">Evaluaciones registradas</h4>
   
        <?php
          // Contar evaluaciones
          $total_evaluaciones = ($datos_seccion['total_evaluaciones']);

          if ($total_evaluaciones < 6):
        ?>
          <a href="<?= SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/evaluacion-new&curso_vigente=' . urlencode($datos_seccion['id_curso_vigente']) . '&seccion=' . urlencode($seccion_letra); ?>" class="btn btn-lg btn-primary" style="text-transform: none;">
            <i class="fas fa-plus"></i> Agregar evaluación
          </a>
        <?php else: ?>
          <span class="text-danger font-weight-bold">Límite de pruebas alcanzado</span>
        <?php endif; ?>
        
    </div>

    


    


    <?php
        $pagina_eval = isset($_GET['eval_page']) ? (int)$_GET['eval_page'] : 1;
        echo $ins_seccion->paginador_evaluaciones_controlador($pagina_eval, 10, $datos_seccion['id_seccion']);
    ?>
</div>
    <?php endif; ?>



<?php if($_SESSION['cargo_spm'] != 5): ?>

<div class="container-fluid mb-4">
    <h4 class="text-center">Estudiantes y puntajes</h4>
    <?php
        $pagina_est = isset($_GET['est_page']) ? (int)$_GET['est_page'] : 1;
        echo $ins_seccion->paginador_estudiantes_con_puntajes_controlador($pagina_est, 10, $datos_seccion['id_seccion']);
    ?>
</div>
    <?php endif; ?>



<!--script src="..vistas/js/miscript.js"></scrip--t-->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>

<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<!-- Inicialización -->
<script>
  $(document).ready(function() {
    $('.select2').select2({
      placeholder: "Escriba el nombre del profesor",
      allowClear: true,
      width: '100%'
    });
  });
</script>

