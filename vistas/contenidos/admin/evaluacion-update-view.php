<?php  
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=2 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

    require_once "./controladores/seccionControlador.php";
    $ins_seccion = new seccionControlador();

    $id_evaluacion = isset($_GET['evaluacion_id_up']) ? $_GET['evaluacion_id_up'] : 0;
//    $datos_evaluacion = $ins_seccion->obtener_datos_evaluacion_controlador($id_evaluacion);
    $datos_evaluacion = $ins_seccion->obtener_datos_evaluacion_controlador_para_ambos_casos($id_evaluacion);

//    echo $id_evaluacion;

    if(!$datos_evaluacion){
        echo '<div class="alert alert-danger text-center">Evaluación no encontrada.</div>';
        exit();
    }
    //$estudiantes = $ins_seccion->obtener_puntajes_evaluacion_controlador($id_evaluacion);    
    $estudiantes = $ins_seccion->obtener_puntajes_evaluacion_controlador_coninscritos($id_evaluacion);
?>
<!-- 🔹 Encabezado 
 -->
<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-edit fa-fw"></i> &nbsp; MODIFICAR EVALUACIÓN
    </h3>
    <p class="text-justify">
        Actualiza los datos de la evaluación y modifica los puntajes de los estudiantes si es necesario.
    </p>
</div>

<!-- 🔹 Navegación -->
<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li>
            <a href="<?php echo SERVERURL; ?>index.php?views=seccion-consult&curso_vigente=<?php echo $datos_evaluacion['id_curso_vigente']; ?>&seccion=<?php echo $datos_evaluacion['seccion']; ?>">
                <i class="fas fa-arrow-left fa-fw"></i> &nbsp; VOLVER A LA SECCIÓN
            </a>
        </li>
    </ul>   
</div>

<!-- 🔹 Contexto -->
<div class="container-fluid mb-4">
    <div class="alert alert-info" role="alert">
        <strong>Sección:</strong> <?php echo $datos_evaluacion['seccion']; ?> &nbsp; | &nbsp;
        <strong>Curso:</strong> <?php echo $datos_evaluacion['nombre_curso']; ?> &nbsp; | &nbsp;
        <?php if (!is_null($datos_evaluacion['numero_cohorte']) && !is_null($datos_evaluacion['anio_cohorte'])): ?>
            <strong>Cohorte:</strong> <?php echo $datos_evaluacion['numero_cohorte'] . " / " . $datos_evaluacion['anio_cohorte']; ?> &nbsp; | &nbsp;
        <?php else: ?>
            <strong>Fecha de inicio del periodo intensivo:</strong> <?php echo $datos_evaluacion['fecha_inicio_int']; ?> &nbsp; | &nbsp;
            <strong>Fecha de culminación del periodo intensivo:</strong> <?php echo $datos_evaluacion['fecha_fin_int']; ?> &nbsp; | &nbsp;
        <?php endif; ?>
        <strong>Profesor:</strong>  
        <?php 
            if(!empty($datos_evaluacion['nombre_profesor'])){
                echo $datos_evaluacion['nombre_profesor']." ".$datos_evaluacion['apellido_profesor'];
            } else {
                echo "<span class='text-muted'>Sin asignar</span>";
            }
        ?>
    </div>
</div>

<!-- 🔹 Formulario -->
<div class="container-fluid">
    <form class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/seccionAjax.php" method="POST" data-form="update" autocomplete="off">
        <!--input type="hidden" name="id_evaluacion_up" value="<?php echo $id_evaluacion; ?>"-->
        <input type="hidden" name="evaluacion_id_up" value="<?php echo $id_evaluacion; ?>">
        <input type="hidden" name="id_seccion" value="<?php echo $datos_evaluacion['id_seccion']; ?>">

        <fieldset>
            <legend><i class="fas fa-info-circle fa-fw"></i> &nbsp; Datos de la evaluación</legend>
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
                            <input type="text" name="tipo_evaluacion" id="tipo_evaluacion" class="form-control" pattern="[a-zA-ZáéíóúÁÉÍÓÚñÑ][a-zA-ZáéíóúÁÉÍÓÚñÑ0-9 .]{0,19}" required value="<?php echo $datos_evaluacion['tipo']; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="obj" class="bmd-label-floating">Objetivo</label>
                            <input type="number" min="0" step="0.1" name="obj" id="obj" class="form-control" required value="<?php echo $datos_evaluacion['obj']; ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="porcentaje" class="bmd-label-floating">Porcentaje</label>
                            <input type="number" name="porcentaje" id="porcentaje" class="form-control" min="1" max="100" required value="<?php echo $datos_evaluacion['porcentaje']; ?>">
                        </div>
                    </div>
                </div>
                <div class="row mt-3">
                    <div class="col-md-4">
                        <div class="form-group">
                            <label for="fecha_evaluacion" class="bmd-label-floating">Fecha de evaluación</label>
                            <input type="date" name="fecha_evaluacion" id="fecha_evaluacion" class="form-control" max="<?php echo date('Y-m-d'); ?>" required value="<?php echo $datos_evaluacion['fecha']; ?>">
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
                                        <option value="NP" <?= $est['puntaje'] === "NP" ? "selected" : "" ?>>NP</option>
                                        <?php for($i=0; $i<=20; $i++){ 
                                            $selected = ($est['puntaje'] == $i) ? "selected" : "";
                                            echo "<option value='{$i}' {$selected}>{$i}</option>"; 
                                        } ?>
                                    </select>
                                </td>
                            </tr>
                        <?php } ?>
                    </tbody>
                </table>
            </div>
        </fieldset>

        <p class="text-center" style="margin-top: 40px;">
            <!--button type="reset" class="btn btn-raised btn-secondary btn-sm"><i class="fas fa-paint-roller"></i> &nbsp; LIMPIAR</button>
            &nbsp; &nbsp;-->
            <button type="submit" class="btn btn-raised btn-success btn-sm"><i class="far fa-save"></i> &nbsp; ACTUALIZAR EVALUACIÓN</button>
        </p>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
  const porcentajeInput = document.getElementById("porcentaje");
  const idSeccion = document.querySelector("input[name='id_seccion']").value;
  const idEvaluacion = document.querySelector("input[name='evaluacion_id_up']").value;
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

        fetch(`${SERVERURL}ajax/seccionAjax.php?accion=validar_porcentaje&id_seccion=${idSeccion}&nuevo=${nuevo}&excluir=${idEvaluacion}`)
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