

<?php  
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    require_once "./controladores/cohorteControlador.php";
    $ctrl = new cohorteControlador();

    $cohorte_id = $_GET['cohorte_id_up'] ?? '';
    $datos = $ctrl->datos_cohorte_controlador("Unico", $cohorte_id);

    if ($datos->rowCount() == 1) {
        $cohorte = $datos->fetch();
    } else {
        echo "<div class='alert alert-danger text-center'>No se encontró la cohorte.</div>";
        exit();
    }

    $cursosData = $ctrl->listar_cursos_controlador_para_cohorte();
    $profesoresData = $ctrl->listar_profesores_controlador();



    require_once "./modelos/mainModel.php";
    $id_real = mainModel::decryption($cohorte_id);


?>

<script>
  const cursosData = <?= json_encode($cursosData) ?>;
  const profesoresData = <?= json_encode($profesoresData) ?>;
    const SERVERURL = "<?php echo SERVERURL; ?>";

</script>


<!--php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>


<php
    require_once "./controladores/cohorteControlador.php";
    $ins_cohorte = new cohorteControlador();

    //$datos = $ins_cohorte->datos_cohorte_controlador("Unico", $pagina[1]);


    $cohorte_id = $_GET['cohorte_id_up'] ?? '';
    $datos = $ins_cohorte->datos_cohorte_controlador("Unico", $cohorte_id);

    if($datos->rowCount() == 1){
        $cohorte = $datos->fetch();
    } else {
        echo "<div class='alert alert-danger text-center'>No se encontró la cohorte.</div>";
        exit();
    }
?>



<!--php
$ctrl = new cohorteControlador();
$profesoresAll = $ctrl->listar_profesores_controlador();
?>

<script>
  const profesoresData = <php echo json_encode($profesoresAll); ?>;
</script>


<script>
  const cursosData = <php
    require_once "./controladores/cohorteControlador.php";
    $ctrl = new cohorteControlador();
    $todosCursos = $ctrl->listar_cursos_controlador_para_cohorte(); // debe devolver un array con todos los cursos
    echo json_encode($todosCursos);
  ?>;
</script>

<script>
  const cursosData = <php
    require_once "./controladores/cohorteControlador.php";
    $ctrl = new cohorteControlador();
    echo json_encode($ctrl->listar_cursos_controlador_para_cohorte());
  ?>;

  const profesoresData = <?php
    echo json_encode($ctrl->listar_profesores_controlador());
  ?>;
</script-->

<style>
  .is-invalid {
    border: 1px solid #dc3545 !important;
  }
  select.form-select {
  max-height: 150px;
  overflow-y: auto;

  option[disabled][selected] {
  background-color: #ffe6e6;
  font-weight: bold;
  }
}
</style>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-plus fa-fw"></i> &nbsp; ACTUALIZAR COHORTE <?php echo $id_real; ?>

    </h3>
    <p class="text-justify">
        Actualiza los datos de la cohorte y los registros de los cursos vigentes en este.
    </p>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-new"><i class="fas fa-calendar-plus fa-fw"></i> &nbsp; NUEVA COHORTE</a>
        </li>
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES</a>
        </li>
        <li>
            <a class="active" href="<?php echo SERVERURL; ?>index.php?views=cohorte-list"><i class="fas fa-calendar-alt fa-fw"></i> &nbsp; ACTUALIZAR COHORTE</a>
        </li>
        
    </ul>   
</div>

<div class="container-fluid">

    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cohorteAjax.php" method="POST" data-form="save" autocomplete="off">
        <!--input type="hidden" name="cohorte_id_up" value="<?php echo $cohorte_id; ?>"-->
        <input type="hidden" name="cohorte_id_up" id="cohorte_id" value="<?php echo $cohorte_id; ?>">
        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Información básica</legend>
            <div id="alerta-cohorte-unico" class="text-danger small mb-2" style="display:none;"></div>

            <!--div id="contador-total-cursos" class="alert alert-info text-center mt-3">
  Total de cursos vigentes: <strong>0</strong>
</div-->



            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_tipo" class="bmd-label-floating">Tipo de cohorte</label>
                            <select class="form-control" name="cohorte_tipo_up" id="cohorte_tipo" required>
                                <option value="" selected disabled>Seleccione una opción</option>
                                <option <?php if($cohorte['tipo_cohorte'] == 'Intensivo') echo 'selected'; ?> value="Intensivo">Intensivo</option>
                                <option <?php if($cohorte['tipo_cohorte'] == 'Extendido') echo 'selected'; ?> value="Extendido">Extendido</option>
                                <option <?php if($cohorte['tipo_cohorte'] == 'Técnico Profesional') echo 'selected'; ?> value="Técnico Profesional">Técnico Profesional</option>
                                <option <?php if($cohorte['tipo_cohorte'] == 'Otro') echo 'selected'; ?> value="Otro">Otro</option>
                            </select>
                        </div>
                    </div>
                    <!--div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_estado" class="bmd-label-floating">Estado</label>
                            <select class="form-control" name="cohorte_estado_up" id="cohorte_estado" required>
                                <option value="" selected disabled>Seleccione una opción</option>
                                <option <?php if($cohorte['estado_cohorte'] == 'Vigente') echo 'selected'; ?> value="Vigente">Vigente</option>
                                <option <?php if($cohorte['estado_cohorte'] == 'Próximo') echo 'selected'; ?> value="Próximo">Próximo</option>
                                <option <?php if($cohorte['estado_cohorte'] == 'Terminado') echo 'selected'; ?> value="Terminado">Terminado</option>
                            </select>
                        </div>
                    </div-->
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_numero" class="bmd-label-floating">N° Cohorte</label>
                            <input type="number" class="form-control" name="cohorte_numero_up" id="cohorte_numero" min="1" max="4" value="<?php echo $cohorte['numero_cohorte']; ?>" readonly>
                        </div>
                    </div>
                    <div class="col-md-2">
                        <div class="form-group">
                            <label for="cohorte_anio" class="bmd-label-floating">Año</label>
                            <input type="number" class="form-control" name="cohorte_anio_up" value="<?php echo $cohorte['anio_cohorte']; ?>" id="cohorte_anio" min="24" max="99" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <fieldset>
            <legend><i class="fas fa-calendar-day fa-fw"></i> &nbsp; Fechas de la cohorte</legend>
            <div id="alerta-fecha-inicio" class="text-danger small mb-2" style="display:none;"></div>

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_inicio" class="bmd-label-floating">Fecha de inicio</label>
                            <input type="date" class="form-control" name="cohorte_fecha_inicio_up" id="cohorte_fecha_inicio" value="<?php echo $cohorte['fecha_inicio']; ?>"  min="<?php echo date('Y-m-d', strtotime('-100 days')); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="form-group">
                            <label for="cohorte_fecha_fin" class="bmd-label-floating">Fecha de fin</label>
                            <input type="date" class="form-control" name="cohorte_fecha_fin_up" id="cohorte_fecha_fin" value="<?php echo $cohorte['fecha_fin']; ?>"  min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>




<!--div id="cursos-asignados-contenedor" 23.8.25>
<php
  $cursos = $ctrl->cursos_vigentes_por_cohorte_controlador($cohorte_id); 
  foreach($cursos as $i => $curso){
    echo '
    <div class="row border rounded p-3 mb-3 bg-light curso-asignado">
      <input type="hidden" name="cursos_asignados['.$i.'][id_curso_vigente]" value="'.$curso['id_curso_vigente'].'">
      <div class="col-md-4 mb-2">
        <label>Curso</label>
        <select name="cursos_asignados['.$i.'][id_curso_fk]" class="form-select select-curso-vigente" required>
          <option value="" disabled>Seleccione...</option>';
          foreach($cursosData as $c){
            if($c['tipo_curso'] == $curso['tipo_curso']){
              $selected = $c['id_curso'] == $curso['id_curso_fk'] ? 'selected' : '';
              echo "<option value='{$c['id_curso']}' $selected>{$c['nombre_curso']} ({$c['tipo_curso']})</option>";
            }
          }
    echo '</select>
      </div>
      <div class="col-md-4 mb-2">
        <label>Oferta (%)</label>
        <input type="number" name="cursos_asignados['.$i.'][oferta]" class="form-control" value="'.$curso['oferta'].'" required>
      </div>

      <div class="col-md-1 mb-2 d-flex align-items-end">
        <button type="button" class="btn btn-warning btn-sm archivar-curso-vigente" title="Archivar curso">
          <i class="fas fa-box-archive"></i>
        </button>
      </div>
    </div>';
  }
?>
</div-->


<!--
      <div class="col-md-3 mb-2">
        <label>Docente sección A</label>
        <input type="text" class="form-control" value="'.$curso['nombre_profesor'].' '.$curso['apellido_profesor'].'" readonly>
      </div>

-->



<!-- 25.8.25 funciona pero no tiene ci profe ni boton de archivar
<div id="cursos-asignados-contenedor">

    <legend><i class="fas fa-plus-circle"></i> &nbsp; Curso(s) ya asignado(s) a la cohorte</legend>


    <div class="text-danger small mb-2" id="alerta-cursos-vigentes" style="display:none;">
        Los cursos deben ser del mismo tipo que el cohorte y no pueden haber 2 cursos iguales en 1 cohorte.
    </div>


  <?php
    $cursos = $ctrl->cursos_vigentes_por_cohorte_controlador("Unico",$cohorte_id); 
    foreach($cursos as $i => $curso){

      // Validación de datos
      if (
        empty($curso['id_curso_vigente']) || 
        empty($curso['id_curso_fk'])
      ) {
        echo '<div class="alert alert-warning"> Curso asignado con datos incompletos o inválidos. No se puede mostrar.</div>';
        continue;
      }

      echo '
      <div class="row border rounded p-3 mb-3 bg-light curso-asignado">
        <input type="hidden" name="cursos_asignados['.$i.'][id_curso_vigente]" value="'.$curso['id_curso_vigente'].'">
        <div class="col-md-4 mb-2">
          <label>Curso</label>
          <select name="cursos_asignados['.$i.'][id_curso_fk]" class="form-select select-curso-vigente" required>
            <option value="" disabled>Seleccione...</option>';
            foreach($cursosData as $c){
              if($c['tipo_curso'] == $curso['tipo_curso']){
                $selected = $c['id_curso'] == $curso['id_curso_fk'] ? 'selected' : '';
                echo "<option value='{$c['id_curso']}' $selected>{$c['nombre_curso']} ({$c['tipo_curso']})</option>";
              }
            }
      echo '</select>
        </div>

        <div class="col-md-2 mb-2 d-flex align-items-end">
          <button type="button" class="btn btn-warning btn-sm archivar-curso-vigente" title="Archivar curso">
            <i class="fas fa-box-archive"></i>
          </button>
        </div>
      </div>';
    }
  ?>
</div-->

<div id="cursos-asignados-contenedor">
  <legend><i class="fas fa-plus-circle"></i> &nbsp; Curso(s) ya asignado(s) a la cohorte</legend>

      <p class="text-justify">
        Al archivar un curso vigente, se archiva también sus secciones, evaluaciones y calificaciones de estudiantes inscritos a dicho periodo de curso
    </p>

  <!--div class="text-danger small mb-2" id="alerta-cursos-vigentes" style="display:none;">
    Los cursos deben ser del mismo tipo que el cohorte y no pueden haber 2 cursos iguales en 1 cohorte.
  </div-->

<!--


no tiene sentido cambiar el curso vigente
          <select name="cursos_asignados['.$i.'][id_curso_fk]" class="form-select select-curso-vigente" readonly>
            <option value="" disabled>Seleccione...</option>';
            foreach($cursosData as $c){
              $selected = $c['id_curso'] == $curso['id_curso_fk'] ? 'selected' : '';
              echo "<option value='{$c['id_curso']}' $selected>{$c['nombre_curso']} ({$c['tipo_curso']})</option>";
            }
      echo '</select>



<select class="form-select select-curso-vigente" disabled>
  <option value="" disabled>Seleccione...</option>';
  foreach($cursosData as $c){
    $selected = $c['id_curso'] == $curso['id_curso_fk'] ? 'selected' : '';
    echo '<option value='{$c['id_curso']}' $selected>{$c['nombre_curso']} ({$c['tipo_curso']})</option>';
  }
echo '</select>
<input type="hidden" name="cursos_asignados['.$i.'][id_curso_fk]" value="'.$curso['id_curso_fk'].'">';


-->





















<!--
este es importante

          <select name="cursos_asignados['.$i.'][id_curso_fk]" class="form-select select-curso-vigente" disabled>
            <option value="" disabled>Seleccione...</option>';
            foreach($cursosData as $c){
              $selected = $c['id_curso'] == $curso['id_curso_fk'] ? 'selected' : '';
              echo "<option value='{$c['id_curso']}' $selected>{$c['nombre_curso']} ({$c['tipo_curso']})</option>";
            }
      echo '</select>




        <div class="col-md-2 mb-2">
          <label>ID</label>
          <div class="form-control-sm text-center fw-bold">
            '.$curso['id_curso_vigente'].'
          </div>
        </div>



                    foreach($cursosData as $c){
              $selected = $c['id_curso'] == $curso['id_curso_fk'] ? 'selected' : '';
              echo "<option value='{$c['id_curso']}' $selected>{$c['nombre_curso']} ({$c['tipo_curso']})</option>";
            }

                    foreach($cursosData as $c){
              $selected = $c['id_curso'] == $curso['id_curso_fk'] ? 'selected' : '';
              echo "<option value='{$c['id_curso']}' $selected>{$c['nombre_curso']} ({$c['tipo_curso']})</option>";
            }
               $selected = $c['curso'] == $curso['id_curso_fk'] ? 'selected' : '';
                  $selected='selected';
                   $selected = ((int)$curso['id_curso'] === (int)$curso['id_curso_fk']) ? 'selected' : '';


-->


<div id="alerta-cursos-vigentes" class="text-danger small mb-2" style="display:none;">
  Los cursos deben ser del mismo tipo que el cohorte y no pueden repetirse.
</div>












  <?php
    $cursos = $ctrl->cursos_vigentes_por_cohorte_controlador("Unico", $cohorte_id); 
    foreach($cursos as $i => $curso){

      if (
        empty($curso['id_curso_vigente']) || 
        empty($curso['id_curso_fk'])
      ) {
        echo '<div class="alert alert-warning"> Curso asignado con datos incompletos o inválidos. No se puede mostrar.</div>';
        continue;
      }

      echo '
      <div class="row border rounded p-3 mb-3 bg-light curso-asignado">               
        <input type="hidden" name="cursos_asignados['.$i.'][id_curso_vigente]" value="'.$curso['id_curso_vigente'].'">
        <input type="hidden" name="cursos_asignados['.$i.'][estado_curso_vigente]" value="1" class="estado-curso-vigente">




<div class="col-md-2 mb-2">
  <label class="form-label text-center w-100">ID</label>
  <div class="text-center" >
    <span class="badge bg-secondary fs-6" style="background-color: #FFFDF7; color: #FFF;">'.$curso['id_curso_vigente'].'</span>
  </div>
</div>


        <div class="col-md-4 mb-2">
          <label>Curso</label>
 

          <select name="cursos_asignados['.$i.'][id_curso_fk]" class="form-select select-curso-vigente" disabled>
          <option value="'.$curso['id_curso_fk'].'" selected> '.$curso['nombre_curso'].' ('.$curso['tipo_curso'].')</option>

            <option value="" disabled>Seleccione...</option>';

      echo '</select>





        </div>


        <div class="col-md-2 mb-2">
          <input type="hidden" name="cursos_asignados['.$i.'][id_curso_fk]" value="'.$curso['id_curso_fk'].'">
        </div>

        <div class="col-md-2 mb-2">
            <label>Profesor asignado (sección A)</label>
            <input type="text" class="form-control" value="'.$curso['nombre_profesor'].' '.$curso['apellido_profesor'].'" readonly>
        </div>

        <div class="col-md-2 mb-2 d-flex align-items-end">
          <button type="button" class="btn btn-warning btn-sm archivar-curso-vigente" title="Archivar curso">
            <i class="fas fa-box-archive"></i> Archivar
          </button>
        </div>
      </div>';
    }
  ?>
</div>


<!--

        <div class="col-md-3 mb-2">
          <label>CI del profesor (sección A)</label>
          <input type="text" class="form-control" value="'.$curso['ci_profesor'].'" readonly>
        </div>
-->








<!--div id="cursos-asignados-contenedor">
<php
  $cursos = $ins_cohorte->cursos_vigentes_por_cohorte($cohorte_id);
  foreach($cursos as $i => $curso){
    echo '
    <div class="row border rounded p-3 mb-3 bg-light curso-asignado">
      <input type="hidden" name="cursos_asignados['.$i.'][id_curso_vigente]" value="'.$curso['id_curso_vigente'].'">
      <div class="col-md-4 mb-2">
        <label>Curso</label>
        <select name="cursos_asignados['.$i.'][id_curso_fk]" class="form-select select-curso-vigente" required>
          <option value="" disabled>Seleccione...</option>';
          foreach($cursosData as $c){
            if($c['tipo_curso'] == $curso['tipo_curso']){
              $selected = $c['id_curso'] == $curso['id_curso_fk'] ? 'selected' : '';
              echo "<option value='{$c['id_curso']}' $selected>{$c['nombre_curso']} ({$c['tipo_curso']})</option>";
            }
          }
    echo '</select>
      </div>
      <div class="col-md-4 mb-2">
        <label>Oferta (%)</label>
        <input type="number" name="cursos_asignados['.$i.'][oferta]" class="form-control" value="'.$curso['oferta'].'" required>
      </div>
      <div class="col-md-3 mb-2">
        <label>Docente sección A</label>
        <input type="text" class="form-control" value="'.$curso['nombre_profesor'].' '.$curso['apellido_profesor'].'" readonly>
      </div>
      <div class="col-md-1 mb-2 d-flex align-items-end">
        <button type="button" class="btn btn-warning btn-sm archivar-curso-vigente" title="Archivar curso">
          <i class="fas fa-box-archive"></i>
        </button>
      </div>
    </div>';
  }
?>
</div-->


<fieldset>
    <legend><i class="fas fa-plus-circle"></i> &nbsp; Asignar Curso(s) a la cohorte</legend>


    <div id="curso-vigente-contenedor"></div>

    <p class="text-end">
        <button type="button" id="agregarCursoVigente" class="btn btn-outline-primary btn-sm">
            <i class="fas fa-plus"></i> Agregar curso vigente
        </button>
    </p>



</fieldset>
<!------------------------------------------------------------------------------------------->
<br><br>

        <fieldset>
            <legend><i class="fas fa-dollar-sign fa-fw"></i> &nbsp; Fechas y montos de pagos</legend>
            <div id="alerta-fechas-pago" class="text-danger small mb-2" style="display:none;"></div>

            

            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_art13" class="bmd-label-floating">Pago art.13</label>
                            <input type="number" class="form-control" name="cohorte_pago_art13_up" value="<?php echo $cohorte['pago_art13']; ?>" id="cohorte_pago_art13" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_art13" class="bmd-label-floating">Fecha pago art.13</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_art13_up" id="cohorte_fecha_pago_art13" value="<?php echo $cohorte['fecha_pago_art13']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_pago_aranceles" class="bmd-label-floating">Pago aranceles</label>
                            <input type="number" class="form-control" name="cohorte_pago_aranceles_up" id="cohorte_pago_aranceles"  value="<?php echo $cohorte['pago_aranceles']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-3">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_aranceles" class="bmd-label-floating">Fecha pago aranceles</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_aranceles_up" value="<?php echo $cohorte['fecha_pago_aranceles']; ?>" id="cohorte_fecha_pago_aranceles" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                </div>


                <br>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="pago_inscripcion_c" class="form-label">Pago inscripción general</label>
                            <input type="number" class="form-control" name="cohorte_pago_inscripcion_up" value="<?php echo $cohorte['pago_inscripcion']; ?>" id="pago_inscripcion_c" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="pago_modulo_c" class="form-label">Pago por módulo general</label>
                            <input type="number" class="form-control" name="cohorte_pago_modulo_up" value="<?php echo $cohorte['pago_modulo']; ?>" id="pago_modulo_c" required>
                        </div>
                    </div>
                <br>

                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo1_reg" class="bmd-label-floating">Fecha módulo 1</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo1_up" id="cohorte_fecha_modulo1" value="<?php echo $cohorte['fecha_pago_modulo1']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo2_reg" class="bmd-label-floating">Fecha módulo 2</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo2_up" id="cohorte_fecha_modulo2" value="<?php echo $cohorte['fecha_pago_modulo2']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="cohorte_fecha_pago_modulo3_reg" class="bmd-label-floating">Fecha módulo 3</label>
                            <input type="date" class="form-control" name="cohorte_fecha_pago_modulo3_up" id="cohorte_fecha_modulo3" value="<?php echo $cohorte['fecha_pago_modulo3']; ?>" min="<?php echo date('Y-m-d'); ?>" required>
                        </div>
                    </div>

                </div>
            </div>
        </fieldset>

        <br><br>

        <!--input type="hidden" name="total_cursos_vigentes" id="total_cursos_vigentes" value="0"-->
        <input type="hidden" name="total_cursos_nuevos" id="total_cursos_nuevos" value="0">
        <input type="hidden" name="total_cursos_asignados" id="total_cursos_asignados" value="0">
        <p class="text-center" style="margin-top: 40px;">
            <!--button type="reset" class="btn btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> LIMPIAR</button-->
            &nbsp; &nbsp;

            <!--button type="submit" class="btn btn-info btn-sm"><i class="far fa-save"></i> GUARDAR</button-->

            <button type="submit" id="btn-submit-cohorte" class="btn btn-info btn-sm" disabled>
            <i class="far fa-save"></i> ACTUALIZAR
            </button>

        </p>
    </form>
</div>




<!--24.8.25 borra la opcion del select si se cambia el tipo de cohorte

    function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    selects.forEach(select => {
        const selectedValue = select.value; // intenta conservar la opción si aún existe
        select.innerHTML = `<option value="" disabled selected>Seleccione...</option>` +
        cursosFiltrados.map(c => `
            <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
            ${c.nombre_curso} (${c.tipo_curso})
            </option>
        `).join("");
    });
    }


-->




<!--
esta no se si sirve 16925
document.addEventListener("click", function(e) {
  const btn = e.target.closest(".archivar-curso-vigente");
  if (!btn) return;

  const bloque = btn.closest(".curso-asignado");
  if (!bloque) return;

  // Desactivar campos excepto estado
  bloque.querySelectorAll("input, select").forEach(el => {
    if (!el.classList.contains("estado-curso-vigente")) {
      el.disabled = true;
    }
  });

  // Marcar estado como archivado
  const estadoInput = bloque.querySelector(".estado-curso-vigente");
  if (estadoInput) estadoInput.value = "0";

  // Cambiar estilo visual
  bloque.style.opacity = "0.6";
  bloque.style.backgroundColor = "#f8d7da";

  // Cambiar botón
  btn.innerHTML = '<i class="fas fa-box-open"></i> Archivado';
  btn.classList.remove("btn-warning");
  btn.classList.add("btn-secondary");
  btn.disabled = true;
});

-->



<script>
document.addEventListener("DOMContentLoaded", () => {
  actualizarContadoresCursos();
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const alertaCohorte = document.getElementById("alerta-cohorte-unico");
  const alertaFechas = document.getElementById("alerta-fecha-inicio");
  const alertaPagos = document.getElementById("alerta-fechas-pago");
  const btnSubmit = document.getElementById("btn-submit-cohorte");
  const cohorteId = document.getElementById("cohorte_id").value;

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = tipoCohorteInput.value;
  let cursosFiltrados = [];




//archivaar curso vigente


document.addEventListener("click", function(e) {
  const btn = e.target.closest(".archivar-curso-vigente");
  if (!btn) return;

  const bloque = btn.closest(".curso-asignado");
  if (!bloque) return;

  // Solo cambiar el valor del estado
  const estadoInput = bloque.querySelector(".estado-curso-vigente");
  if (estadoInput) estadoInput.value = "0";

  // Opcional: cambiar el texto del botón para indicar que fue marcado
  btn.innerHTML = '<i class="fas fa-box-open"></i> Archivado';
  btn.classList.remove("btn-warning");
  btn.classList.add("btn-secondary");
  btn.disabled = true;

  // Actualizar contador si lo estás mostrando
  actualizarContadoresCursos();
});









  if (tipoCohorteSeleccionado) {
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
  }

  tipoCohorteInput.addEventListener("change", () => {
    tipoCohorteSeleccionado = tipoCohorteInput.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    actualizarOpcionesSelects();
    validarCursosVigentes();
    validarCohorteUnico();
  });

  ["cohorte_numero", "cohorte_anio"].forEach(id => {
    const input = document.getElementById(id);
    if (input) input.addEventListener("input", validarCohorteUnico);
  });

  ["cohorte_fecha_inicio", "cohorte_fecha_fin"].forEach(id => {
    const input = document.getElementById(id);
    if (input) input.addEventListener("change", () => {
      validarFechasInicioFin();
      validarFechasDePagoEnRango();
    });
  });

  ["cohorte_fecha_pago_art13", "cohorte_fecha_pago_aranceles", "cohorte_fecha_modulo1", "cohorte_fecha_modulo2", "cohorte_fecha_modulo3"]
    .forEach(id => {
      const input = document.getElementById(id);
      if (input) input.addEventListener("change", validarFechasDePagoEnRango);
    });

  document.addEventListener("input", e => {
    if (e.target.classList.contains("select-curso-vigente")) {
      setTimeout(validarCursosVigentes, 1000);
    }
  });

  btnAgregar.addEventListener("click", () => {
    if (!tipoCohorteSeleccionado) {
      alert("Primero selecciona el tipo de cohorte.");
      return;
    }

    if (cursosFiltrados.length === 0) {
      alert("No hay cursos disponibles para el tipo seleccionado.");
      return;
    }

    const div = document.createElement("div");
    div.className = "row border rounded p-3 mb-3 bg-light";

    const selectHTML = cursosFiltrados.map(c => `
      <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
    `).join("");

    div.innerHTML = `
      <div class="col-md-4 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${selectHTML}
        </select>
      </div>

      <div class="col-md-3 mb-2">
        <label class="form-label">Docente de la sección "A"</label>
        <select name="cursos_vigentes[${contadorCursos}][id_profesor_fk]" class="form-select select-docente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${profesoresData.map(p => `
            <option value="${p.id_profesor}">${p.nombre_profesor} ${p.apellido_profesor}</option>
          `).join("")}
        </select>
      </div>
      <div class="col-md-1 mb-2 d-flex align-items-end">
        <button type="button" class="btn btn-danger btn-sm eliminar-curso-vigente" title="Eliminar curso">
          <i class="fas fa-trash-alt"></i>
        </button>
      </div>
    `;

    contenedor.appendChild(div);
    actualizarContadoresCursos();

    div.querySelector(".eliminar-curso-vigente").addEventListener("click", () => {
      div.remove();
      validarCursosVigentes();
      actualizarContadoresCursos();
    });

    contadorCursos++;
    validarCursosVigentes();
  });

  function actualizarContadoresCursos() {
    const nuevos = document.querySelectorAll("#curso-vigente-contenedor .select-curso-vigente").length;
    //const asignados = document.querySelectorAll("#cursos-asignados-contenedor .select-curso-vigente").length;
    const asignados = document.querySelectorAll("#cursos-asignados-contenedor .curso-asignado").length;
    document.getElementById("total_cursos_nuevos").value = nuevos;
    document.getElementById("total_cursos_asignados").value = asignados;

  const total = nuevos + asignados;
  const contadorDiv = document.getElementById("contador-total-cursos");
  if (contadorDiv) {
    contadorDiv.querySelector("strong").textContent = total;
  }


  }

  function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    selects.forEach(select => {
      const selectedValue = select.value;
      const cursoSeleccionado = cursosData.find(c => c.id_curso == selectedValue);
      let opciones = cursosFiltrados.map(c => `
        <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
          ${c.nombre_curso} (${c.tipo_curso})
        </option>
      `).join("");

      if (selectedValue && (!cursoSeleccionado || cursoSeleccionado.tipo_curso !== tipoCohorteSeleccionado)) {
        opciones = `
          <option value="${selectedValue}" selected disabled style="color:red;">
            ${cursoSeleccionado ? cursoSeleccionado.nombre_curso : 'Curso desconocido'} (No válido para este tipo)
          </option>
        ` + opciones;
      }

      select.innerHTML = `<option value="" disabled>Seleccione...</option>` + opciones;
    });
  }

  function validarCursosVigentes() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    const cursosSeleccionados = [];
    let valid = true;

    selects.forEach(select => {
      const cursoId = select.value;
      select.classList.remove("is-invalid");
      const cursoData = cursosData.find(c => c.id_curso == cursoId);

      if (!cursoId || !cursoData || cursoData.tipo_curso !== tipoCohorteSeleccionado || cursosSeleccionados.includes(cursoId)) {
        valid = false;
        select.classList.add("is-invalid");
      } else {
        cursosSeleccionados.push(cursoId);
      }
    });

    alertaCursos.style.display = valid ? "none" : "block";
    validarFormularioCohorte();
  }

  function validarCohorteUnico() {
    const tipo = tipoCohorteInput.value;
    const numero = document.getElementById("cohorte_numero").value;
    const anio = document.getElementById("cohorte_anio").value;

    if (!tipo || !numero || !anio) {
      alertaCohorte.style.display = "none";
      validarFormularioCohorte();
      return;
    }

    fetch(`${SERVERURL}ajax/cohorteAjax.php?accion=validar_cohorte_unico&tipo=${tipo}&numero=${numero}&anio=${anio}&excluir=${cohorteId}`)
      .then(res => res.json())
      .then(data => {
     //   alertaCohorte.textContent = data.valido



//c
        alertaCohorte.textContent = data.valido ? "" : data.mensaje;
        alertaCohorte.style.display = data.valido ? "none" : "block";
        validarFormularioCohorte();
      });
  }

  function validarFechasInicioFin() {
    const inicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
    const fin = new Date(document.getElementById("cohorte_fecha_fin").value);
    const alerta = document.getElementById("alerta-fecha-inicio");

    let mensaje = "";
    const hoy = new Date();
    const hace90 = new Date();
    hace90.setDate(hoy.getDate() - 90);

    if (inicio < hace90) {
      mensaje = "La fecha de inicio no puede ser anterior a los últimos 90 días.";
    } else if (inicio > fin) {
      mensaje = "La fecha de inicio no puede ser posterior a la fecha de fin.";
    }

    alerta.textContent = mensaje;
    alerta.style.display = mensaje ? "block" : "none";
    validarFormularioCohorte();
  }

  function validarFechasDePagoEnRango() {
    const inicio = new Date(document.getElementById("cohorte_fecha_inicio").value);
    const fin = new Date(document.getElementById("cohorte_fecha_fin").value);
    const alerta = document.getElementById("alerta-fechas-pago");

    const campos = [
      "cohorte_fecha_pago_art13",
      "cohorte_fecha_pago_aranceles",
      "cohorte_fecha_modulo1",
      "cohorte_fecha_modulo2",
      "cohorte_fecha_modulo3"
    ];

    let fueraDeRango = [];

    campos.forEach(id => {
      const input = document.getElementById(id);
      if (!input || !input.value) return;
      const fecha = new Date(input.value);
      if (fecha < inicio || fecha > fin) {
        fueraDeRango.push(id);
        input.classList.add("is-invalid");
      } else {
        input.classList.remove("is-invalid");
      }
    });

    alerta.textContent = fueraDeRango.length > 0
      ? "Las fechas de pago deben estar dentro del rango de inicio y fin de la cohorte."
      : "";
    alerta.style.display = fueraDeRango.length > 0 ? "block" : "none";
    validarFormularioCohorte();
  }

  function validarFormularioCohorte() {
    const errores = [
      alertaCursos,
      alertaCohorte,
      alertaFechas,
      alertaPagos
    ];

    const hayErrores = errores.some(el => el && el.style.display === "block");
    btnSubmit.disabled = hayErrores;
  }
});


</script>


<!--
      <div class="col-md-4 mb-2">
        <label class="form-label">Oferta (%)</label>
        <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" max="100" required>
      </div>
-->













<!--script> funciona pero es el viejo,este validara todo
document.addEventListener("DOMContentLoaded", () => {
  const btnAgregar = document.getElementById("agregarCursoVigente");
  const contenedor = document.getElementById("curso-vigente-contenedor");
  const tipoCohorteInput = document.getElementById("cohorte_tipo");
  const alertaCursos = document.getElementById("alerta-cursos-vigentes");
  const btnSubmit = document.getElementById("btn-submit-cohorte");

  let contadorCursos = 0;
  let tipoCohorteSeleccionado = "";
  let cursosFiltrados = [];

// Inicializar tipo de cohorte si ya tiene valor precargado
tipoCohorteSeleccionado = tipoCohorteInput.value;

if (tipoCohorteSeleccionado) {
  cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
  actualizarOpcionesSelects();
  validarCursosVigentes();
}



  tipoCohorteInput.addEventListener("change", function () {
    tipoCohorteSeleccionado = this.value;
    cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
    validarCursosVigentes();
  });


  tipoCohorteInput.addEventListener("change", function () {
  tipoCohorteSeleccionado = this.value;
  cursosFiltrados = cursosData.filter(c => c.tipo_curso === tipoCohorteSeleccionado);
  actualizarOpcionesSelects();
  validarCursosVigentes();
    });

  function actualizarContadoresCursos() {
    const nuevos = document.querySelectorAll("#curso-vigente-contenedor .select-curso-vigente").length;
    const asignados = document.querySelectorAll("#cursos-asignados-contenedor .select-curso-vigente").length;

    document.getElementById("total_cursos_nuevos").value = nuevos;
    document.getElementById("total_cursos_asignados").value = asignados;
  }


  function actualizarOpcionesSelects() {
    const selects = document.querySelectorAll(".select-curso-vigente");

    selects.forEach(select => {
      const selectedValue = select.value;
      const cursoSeleccionado = cursosData.find(c => c.id_curso == selectedValue);

      // Generar opciones válidas según tipo de cohorte
      let opciones = cursosFiltrados.map(c => `
        <option value="${c.id_curso}" ${selectedValue == c.id_curso ? 'selected' : ''}>
          ${c.nombre_curso} (${c.tipo_curso})
        </option>
      `).join("");

      // Si el curso seleccionado ya no está en el filtro, lo agregamos manualmente
      if (selectedValue && (!cursoSeleccionado || cursoSeleccionado.tipo_curso !== tipoCohorteSeleccionado)) {
        opciones = `
          <option value="${selectedValue}" selected disabled style="color:red;">
            ${cursoSeleccionado ? cursoSeleccionado.nombre_curso : 'Curso desconocido'} (No válido para este tipo)
          </option>
        ` + opciones;
      }

      select.innerHTML = `<option value="" disabled>Seleccione...</option>` + opciones;
    });
  }






  btnAgregar.addEventListener("click", () => {
    if (!tipoCohorteSeleccionado) {
      alert("Primero selecciona el tipo de cohorte.");
      return;
    }

    if (cursosFiltrados.length === 0) {
      alert("No hay cursos disponibles para el tipo seleccionado.");
      return;
    }

    const div = document.createElement("div");
    div.className = "row border rounded p-3 mb-3 bg-light";

    const selectHTML = cursosFiltrados.map(c => `
      <option value="${c.id_curso}">${c.nombre_curso} (${c.tipo_curso})</option>
    `).join("");


    div.innerHTML = `
        <div class="col-md-4 mb-2">
            <label class="form-label">Curso</label>
            <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
            <option value="" selected disabled>Seleccione...</option>
            ${selectHTML}
            </select>
        </div>
        <div class="col-md-4 mb-2">
            <label class="form-label">Oferta (%)</label>
            <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" max="100" required>
        </div>
        <div class="col-md-3 mb-2">
            <label class="form-label">Docente de la sección "A"</label>
            <select name="cursos_vigentes[${contadorCursos}][id_profesor_fk]" class="form-select select-docente" required>
            <option value="" selected disabled>Seleccione...</option>
            ${profesoresData.map(p => `
                <option value="${p.id_profesor}">${p.nombre_profesor} ${p.apellido_profesor}</option>
            `).join("")}
            </select>
        </div>
        <div class="col-md-1 mb-2 d-flex align-items-end">
            <button type="button" class="btn btn-danger btn-sm eliminar-curso-vigente" title="Eliminar curso">
            <i class="fas fa-trash-alt"></i>
            </button>
        </div>
    `;
    actualizarContadoresCursos();
    contenedor.appendChild(div);

    div.querySelector(".eliminar-curso-vigente").addEventListener("click", () => {
    div.remove();
    validarCursosVigentes();
    actualizarContadoresCursos();
    });

    contadorCursos++;
    validarCursosVigentes();

    document.getElementById("total_cursos_vigentes").value = document.querySelectorAll(".select-curso-vigente").length;
  });

  document.addEventListener("input", e => {
    if (e.target.classList.contains("select-curso-vigente")) {
      setTimeout(validarCursosVigentes, 1000); // debounce 1s
    }
  });

  function validarCursosVigentes() {
    const selects = document.querySelectorAll(".select-curso-vigente");
    const cursosSeleccionados = [];
    let valid = true;

    selects.forEach(select => {
      const cursoId = select.value;
      select.classList.remove("is-invalid");

      const cursoData = cursosData.find(c => c.id_curso == cursoId);

      if (!cursoId || !cursoData || cursoData.tipo_curso !== tipoCohorteSeleccionado || cursosSeleccionados.includes(cursoId)) {
        valid = false;
        select.classList.add("is-invalid");
      } else {
        cursosSeleccionados.push(cursoId);
      }
    });

    if (!valid) {
      alertaCursos.style.display = "block";
      btnSubmit.disabled = true;
    } else {
      alertaCursos.style.display = "none";
      btnSubmit.disabled = false;
    }

    actualizarContadoresCursos();
  }
});


document.querySelectorAll(".archivar-curso-vigente").forEach(btn => {
  btn.addEventListener("click", () => {
    const bloque = btn.closest(".curso-asignado");

    // Desactivar campos
    bloque.querySelectorAll("input, select").forEach(el => {
      if (!el.classList.contains("estado-curso-vigente")) {
        el.disabled = true;
      }
    });

    // Marcar estado como archivado
    const estadoInput = bloque.querySelector(".estado-curso-vigente");
    if (estadoInput) estadoInput.value = "0";

    // Cambiar estilo visual
    bloque.style.opacity = "0.6";
    bloque.style.backgroundColor = "#f8d7da";
    btn.innerHTML = '<i class="fas fa-box-open"></i> Archivado';
    btn.classList.remove("btn-warning");
    btn.classList.add("btn-secondary");
    btn.disabled = true;
  });
});






 
</script-->






























<!-- 9.8.25

    <div class="col-md-4 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
        <option value="" selected disabled>Seleccione...</option>
        ${selectHTML}
        </select>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Oferta (%)</label>
        <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" required>
    </div>
    <div class="col-md-4 mb-2">
        <label class="form-label">Docente de la sección "A"</label>
        <select name="cursos_vigentes[${contadorCursos}][id_profesor_fk]" class="form-select select-docente" required>
        <option value="" selected disabled>Seleccione...</option>
        ${profesoresData.map(p => `
            <option value="${p.id_profesor}">${p.nombre_profesor} ${p.apellido_profesor}</option>
        `).join("")}
        </select>
    </div>


-->




<!--

    div.innerHTML = `
      <div class="col-md-8 mb-2">
        <label class="form-label">Curso</label>
        <select name="cursos_vigentes[${contadorCursos}][id_curso_fk]" class="form-select select-curso-vigente" required>
          <option value="" selected disabled>Seleccione...</option>
          ${selectHTML}
        </select>
      </div>
      <div class="col-md-4 mb-2">
        <label class="form-label">Oferta (%)</label>
        <input type="number" name="cursos_vigentes[${contadorCursos}][oferta]" class="form-control" value="0" min="0" required>
      </div>
    `;

-->



