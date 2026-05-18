<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<?php
    require_once "./controladores/cohorteControlador.php";
    $ins_cohorte = new cohorteControlador();

    if(!isset($_GET['cohorte_id']) || $_GET['cohorte_id']==""){
        echo '<div class="alert alert-danger text-center">No se especificó ningún cohorte.</div>';
        exit();
    }

    $main = new mainModel();
    $idCohorte = $main->decryption($_GET['cohorte_id']);
?>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-sliders-h fa-fw"></i> &nbsp; MODIFICAR CURSOS ASIGNADOS</h3>
    <p class="text-justify">
        Desde aquí puedes modificar los cursos que tiene asignada esta cohorte. Solo selecciona o desmarca las opciones necesarias.
    </p>
</div>

<div class="container-fluid">
    <form id="form-cursos-cohorte" class="FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cohorteAjax.php" method="POST" data-form="update" autocomplete="off">
        <input type="hidden" name="cohorte_id_cursos_up" value="<?php echo $idCohorte; ?>">
        
        <div id="bloque-cursos" class="mt-4">
            <p class="text-muted">Cargando cursos...</p>
        </div>

        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary" style="display:none;" id="btn-guardar-cursos">
                <i class="fas fa-save"></i> Guardar cambios
            </button>
        </div>
    </form>
</div>

<script>
document.addEventListener("DOMContentLoaded", function(){
    const bloque = document.getElementById("bloque-cursos");
    const boton = document.getElementById("btn-guardar-cursos");
    const id = "<?php echo $idCohorte; ?>";

    fetch("<?php echo SERVERURL; ?>ajax/cohorteAjax.php?cargar_cursos_cohorte=" + id)
    .then(res => res.json())
    .then(data => {
        if(data && data.cursos){
            if(data.cursos.length > 0){
                let html = '<fieldset><legend><i class="fas fa-check-double"></i> &nbsp; Cursos disponibles</legend><div class="row">';
                data.cursos.forEach(curso => {
                    const checked = data.seleccionados.map(id => id.toString()).includes(curso.id.toString()) ? "checked" : "";
                    html += `
                        <div class="col-md-4">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="cursos_seleccionados[]" value="${curso.id}" id="curso_${curso.id}" ${checked}>
                                <label class="form-check-label" for="curso_${curso.id}">${curso.nombre}</label>
                            </div>
                        </div>
                    `;
                });
                html += '</div></fieldset>';
                bloque.innerHTML = html;
                boton.style.display = "inline-block";
            } else {
                bloque.innerHTML = "<p class='text-muted'>No hay cursos disponibles para esta cohorte.</p>";
            }
        } else {
            bloque.innerHTML = "<p class='text-danger'>Error al cargar datos.</p>";
        }
    })
    .catch(err => {
        console.error(err);
        bloque.innerHTML = "<p class='text-danger'>No se pudo obtener la información.</p>";
    });
});
</script>