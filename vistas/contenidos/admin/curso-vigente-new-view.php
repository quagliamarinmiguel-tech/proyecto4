<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>
<?php
require_once "./controladores/cohorteControlador.php";
$cohorteCtrl = new cohorteControlador();
//$cohortes = $cohorteCtrl->obtener_cohortes_de_cv();
?>




<div class="full-box page-header">
  <h3 class="text-left">
    <i class="fas fa-link fa-fw"></i> &nbsp; NUEVO CURSO VIGENTE
  </h3>
  <p class="text-justify">
    Asocia un curso existente con una cohorte vigente o próxima, indicando sus valores económicos.
  </p>
</div>




<!--ul>
<php foreach ($cursos as $curso): ?>
    <li>
        CI: <strong><?php echo htmlspecialchars($curso['nombre_curso']); ?></strong> -
        Nombre: <strong><?php echo htmlspecialchars($curso['pago_inscripcion']); ?></strong> -
        Apellido: <strong><?php echo htmlspecialchars($curso['tipo_curso']); ?></strong>
    </li>
<php endforeach; ?>
</ul-->


<!--div style="height:10vh;"></div>
<h4>📋 Lista de imagenes registrados</h4>
<ul>
<php foreach ($imgn1 as $img1): ?>
    <li>
        CI: <p><?php echo htmlspecialchars($img1); ?></p> -
    </li>
<php endforeach; ?>
</ul-->



<div class="container-fluid">
  <form id="form-curso-vigente" class="form-neon FormularioAjax"
        action="<?php echo SERVERURL; ?>ajax/cursoVigenteAjax.php" method="POST"
        data-form="save" autocomplete="off">

    <input type="hidden" name="id_curso_fk" id="id_curso_seleccionado">
    <input type="hidden" name="id_cohorte_fk" id="id_cohorte_seleccionado">

    <!-- Curso -->
    <fieldset>
      <legend><i class="fas fa-search"></i> &nbsp; Buscar curso</legend>
      <div class="row">
        <div class="col-md-8 mb-3 position-relative">
          <label class="form-label">Nombre del curso</label>
          <input type="text" id="buscarCurso" class="form-control" placeholder="Escriba y seleccione...">
          <div id="resultadosCursos" class="list-group shadow-sm"
               style="position: absolute; z-index: 10; width: 100%; max-height: 230px; overflow-y: auto;"></div>
          <small id="val_curso" class="form-text mt-1"></small>
        </div>
      </div>
    </fieldset>

    <!-- Cohorte -->
    <fieldset>
      <legend><i class="fas fa-layer-group"></i> &nbsp; Selección de cohorte</legend>
      <div class="row">
        <div class="col-md-8 mb-3 position-relative">
          <label class="form-label">Cohorte vigente</label>
          <input type="text" id="buscarCohorte" class="form-control campo-color" readonly placeholder="Seleccione...">
        
        
          <!--div id="listaCohortes" class="list-group shadow-sm position-absolute d-none"
               style="z-index: 9999; width: 100%; max-height: 250px; overflow-y: auto;"-->

              <div id="listaCohortes" class="list-group shadow-sm position-absolute d-none"
                style="z-index: 9999; width: 100%; max-height: 250px; overflow-y: auto; background-color: #fff; border: 1px solid #ced4da;">


            <?php
            if (!empty($cohortes)) {
              foreach ($cohortes as $c) {
                $label = $c['numero_cohorte'] . '.' . $c['anio_cohorte'] . ' ' . $c['tipo_cohorte'];
                echo '<div class="list-group-item list-select-color small text-truncate"
                      data-id="' . $c['id_cohorte'] . '" data-label="' . $label . '"
                      style="cursor:pointer;">' . $label . '</div>';
              }
            } else {
              
              echo '<div class="list-group-item  disabled">No hay cohortes vigentes</div>';
            }
            ?>
            <!--//text-muted-->
          </div>
          <small id="val_cohorte" class="form-text mt-1"></small>
        </div>
      </div>
    </fieldset>

    <!-- Valores -->
    <fieldset>
      <legend><i class="fas fa-coins"></i> &nbsp; Valores</legend>
      <div class="row">
        <!--div class="col-md-4 mb-3">
          <label class="form-label">Pago inscripción</label>
          <input type="number" class="form-control" name="pago_inscripcion" required>
        </div>
        <div class="col-md-4 mb-3">
          <label class="form-label">Pago por módulo</label>
          <input type="number" class="form-control" name="pago_modulo" required>
        </div-->
        <div class="col-md-4 mb-3">
          <label class="form-label">Oferta (%)</label>
          <input type="number" class="form-control" name="oferta" value="0">
        </div>
      </div>
    </fieldset>

    <p class="text-center mt-4">
      <button type="submit" class="btn btn-success btn-sm">
        <i class="far fa-save"></i> GUARDAR ASIGNACIÓN
      </button>
    </p>
  </form>
</div>

<script src="<?php echo SERVERURL; ?>vistas/js/buscadorFK.js"></script>

<script>
document.addEventListener("DOMContentLoaded", () => {
  iniciarBuscadorFK({
    inputTextoID: "buscarCurso",
    listaResultadosID: "resultadosCursos",
    inputHiddenID: "id_curso_seleccionado",
    validacionID: "val_curso",
    endpointURL: "<?php echo SERVERURL; ?>ajax/codigo_busqueda.php"
  });

  const inputCohorte = document.getElementById("buscarCohorte");
  const listaCohortes = document.getElementById("listaCohortes");

  inputCohorte.addEventListener("click", () => {
    listaCohortes.classList.toggle("d-none");
  });

  listaCohortes.querySelectorAll(".list-group-item").forEach(item => {
    item.addEventListener("click", () => {
      const nombre = item.dataset.label;
      const id = item.dataset.id;
      document.getElementById("buscarCohorte").value = nombre;
      document.getElementById("id_cohorte_seleccionado").value = id;
      listaCohortes.classList.add("d-none");
      document.getElementById("val_cohorte").textContent = "";
    });
  });

  document.getElementById("form-curso-vigente").addEventListener("submit", (e) => {
    let errores = 0;
    const curso = document.getElementById("id_curso_seleccionado").value.trim();
    const cohorte = document.getElementById("id_cohorte_seleccionado").value.trim();

    if (curso === "") {
      document.getElementById("val_curso").textContent = "Selecciona un curso válido.";
      errores++;
    }

    if (cohorte === "") {
      document.getElementById("val_cohorte").textContent = "Selecciona una cohorte válida.";
      errores++;
    }

    if (errores > 0) e.preventDefault();
  });
});
</script>