<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=2 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    require_once "./controladores/seccionControlador.php";
    $ins_seccion = new seccionControlador();

    $curso_vigente_id = isset($_GET['curso_vigente']) ? $_GET['curso_vigente'] : 0;
    $seccion_letra = isset($_GET['seccion']) ? $_GET['seccion'] : '';

    if($curso_vigente_id == 0 || $seccion_letra == ''){
        echo '<div class="alert alert-danger text-center">Parámetros inválidos. No se puede cargar la sección.</div>';
        exit();
    }

    $datos_seccion = $ins_seccion->obtener_datos_seccion_controlador_para_ambos_casos($curso_vigente_id, $seccion_letra);
    $estudiantes = $ins_seccion->obtener_estudiantes_inscritos_controlador($datos_seccion['id_seccion']);
          $total_evaluaciones = $ins_seccion->contar_evaluaciones_controlador($datos_seccion['id_seccion']);

?>

<!-- 🔹 Encabezado -->
<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-plus fa-fw"></i> &nbsp; NUEVA EVALUACIÓN
    </h3>
    <p class="text-justify">
        Completa todos los campos obligatorios para agregar la evaluación correctamente. Al guardar, se registrará la evaluación y los puntajes de cada estudiante.
    </p>
</div>

<!-- 🔹 Navegación -->
<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=seccion-consult&curso_vigente=<?php echo $curso_vigente_id; ?>&seccion=<?php echo $seccion_letra; ?>">
                <i class="fas fa-arrow-left fa-fw"></i> &nbsp; VOLVER A LA SECCIÓN
            </a>
        </li>
    </ul>   
</div>


<!-- 🔹 Contexto de la sección -->
<div class="container-fluid mb-4">
    <div class="alert alert-info" role="alert">
        <strong>Sección:</strong> <?php echo $seccion_letra; ?> &nbsp; | &nbsp;
        <strong>Curso:</strong> <?php echo $datos_seccion['nombre_curso']; ?> &nbsp; | &nbsp;

        <?php if (!is_null($datos_seccion['numero_cohorte']) && !is_null($datos_seccion['anio_cohorte'])): ?>
            <strong>Cohorte:</strong> <?php echo $datos_seccion['numero_cohorte'] . " / " . $datos_seccion['anio_cohorte']; ?> &nbsp; | &nbsp;
        <?php else: ?>
            <strong>Fecha de inicio del periodo intensivo:</strong> <?php echo $datos_seccion['fecha_inicio_int']; ?> &nbsp; | &nbsp;
            <strong>Fecha de culminación del periodo intensivo:</strong> <?php echo $datos_seccion['fecha_fin_int']; ?> &nbsp; | &nbsp;
        <?php endif; ?>
        <strong>Profesor:</strong> 
        <?php 
            if(!empty($datos_seccion['nombre_profesor'])){
                echo $datos_seccion['nombre_profesor']." ".$datos_seccion['apellido_profesor'];
            } else {
                echo "<span class='text-muted'>Sin asignar</span>";
            }
        ?>
    </div>
</div>

<!-- 🔹 Formulario -->
<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" data-form="save" autocomplete="off">
        <input type="hidden" name="id_seccion" value="<?php echo $datos_seccion['id_seccion']; ?>">

        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Datos de la evaluación</legend>
            
            
                            <?php
          // Contar evaluaciones usando el controlador
          if ($total_evaluaciones == 6):
        ?>
                      <span class="text-danger font-weight-bold">Límite de pruebas alcanzado</span>
                    <?php endif; ?>    


            
            <div class="text-danger small mt-1" id="alerta-porcentaje" style="display:none;">
                El porcentaje total de evaluaciones en esta sección excede el 100%. Ajusta el valor.
            </div>
            <div class="text-muted small" id="porcentaje-acumulado" style="display:none;">
                Porcentaje acumulado actual: <span id="porcentaje-total">0</span>%
            </div>
            
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="tipo_evaluacion" class="bmd-label-floating">Tipo de evaluación</label>
                            <input type="text" name="tipo_evaluacion" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 .]{0,19}"  id="tipo_evaluacion" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="obj" class="bmd-label-floating">Objetivo</label>
                            <input type="number" min="0" step="0.1" name="obj" id="obj" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="porcentaje" class="bmd-label-floating">Porcentaje</label>
                            <input type="number" name="porcentaje" id="porcentaje" class="form-control" min="1" max="100" required>
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_evaluacion" class="bmd-label-floating">Fecha de evaluación</label>
                            <input type="date" name="fecha_evaluacion" id="fecha_evaluacion" max="<?php echo date('Y-m-d'); ?>" class="form-control" required>
                        </div>
                    </div>
                </div>
            </div>
        </fieldset>

        <br><br>

        <fieldset>
            <legend><i class="fas fa-user-graduate fa-fw"></i> &nbsp; Calificación por estudiante</legend>
            <div class="table-responsive">
                <table class="table table-bordered table-sm">
                    <thead class="text-center roboto-medium">
                        <tr>
                            <th>CI</th>
                            <th>Nombre</th>
                            <th>Apellido</th>
                            <th>Puntaje</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($estudiantes as $est){ ?>
                            <tr class="text-center">
                                <td><?php echo $est['ci_estudiante']; ?></td>
                                <td><?php echo $est['nombre']; ?></td>
                                <td><?php echo $est['apellido']; ?></td>
                                <td>
                                    <select name="puntaje_<?php echo $est['id_inscripcion']; ?>" class="form-control">
                                        <option value="NP">NP</option>
                                        <?php for($i=0; $i<=20; $i++){ echo "<option value='{$i}'>{$i}</option>"; } ?>
                                    </select>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </fieldset>

   
                <?php
          // Contar evaluaciones usando el controlador
          if ($total_evaluaciones < 6):
        ?>
                <p class="text-center" style="margin-top: 40px;">
            <button type="submit" class="btn btn-raised btn-success btn-sm"><i class="far fa-save"></i> &nbsp; GUARDAR EVALUACIÓN</button>
        </p>
        <?php else: ?>
                        <p class="text-center" style="margin-top: 40px;">
            <button type="submit" class="btn btn-raised btn-success btn-sm" disabled><i class="far fa-save" ></i> &nbsp; GUARDAR EVALUACIÓN</button>
        </p>
        <?php endif; ?>    
    
    
    
    
    </form>
</div>


<script>
document.addEventListener("DOMContentLoaded", () => {
  const porcentajeInput = document.getElementById("porcentaje");
  const idSeccion = document.querySelector("input[name='id_seccion']").value;
  const alertaPorcentaje = document.getElementById("alerta-porcentaje");
  const porcentajeTotalSpan = document.getElementById("porcentaje-total");
  const porcentajeAcumulado = document.getElementById("porcentaje-acumulado");
  const btnSubmit = document.querySelector("button[type='submit']");
  const SERVERURL = "<?php echo SERVERURL; ?>";



  porcentajeInput.addEventListener("input", () => {
    const nuevo = parseFloat(porcentajeInput.value);
    if (isNaN(nuevo) || nuevo <= 0) {
      alertaPorcentaje.style.display = "none";
      porcentajeAcumulado.style.display = "none";
      btnSubmit.disabled = true;
      return;
    }


fetch(`${SERVERURL}ajax/seccionAjax.php?accion=validar_porcentaje&id_seccion=${idSeccion}&nuevo=${nuevo}`)
  .then(res => res.text())
  .then(texto => {
    console.log("Respuesta cruda del servidor:", texto);
    try {
      const data = JSON.parse(texto);
      porcentajeTotalSpan.textContent = data.total;
      porcentajeAcumulado.style.display = "block";

      if (data.excede) {
        alertaPorcentaje.style.display = "block";
        btnSubmit.disabled = true;
      } else {
        alertaPorcentaje.style.display = "none";
        btnSubmit.disabled = false;
      }
    } catch (error) {
      console.error("Error al convertir a JSON:", error);
      console.warn("Contenido recibido:", texto);
      alertaPorcentaje.style.display = "none";
      porcentajeAcumulado.style.display = "none";
      btnSubmit.disabled = true;
    }
  })
  .catch(err => {
    console.error("Error de red o fetch:", err);
    alertaPorcentaje.style.display = "none";
    porcentajeAcumulado.style.display = "none";
    btnSubmit.disabled = true;
  });

  });
});
</script>





