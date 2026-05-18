<?php 
    if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4 ){
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }
?>

<?php
    require_once "./controladores/cohorteControlador.php";
    $ins_cohorte = new cohorteControlador();

    $cohortes = $ins_cohorte->listar_cohortes_controlador(); // Esta función la implementamos abajo
?>

<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-book-reader"></i> &nbsp; ASIGNAR CURSOS A COHORTE</h3>
    <p class="text-justify">Selecciona una cohorte para ver y editar sus cursos asignados.</p>
</div>

<div class="container-fluid">
    <form id="form-cursos-cohorte" class="FormularioAjax" action="<?php echo SERVERURL; ?>ajax/cohorteAjax.php" method="POST" data-form="update" autocomplete="off">
        <div class="form-group">
            <label>Selecciona la cohorte:</label>
            <select name="cohorte_id_cursos_up" id="selector-cohorte" class="form-control" required>
                <option value="" selected disabled>-- Escoge una cohorte --</option>
                <?php
                    foreach($cohortes as $cohorte){
                        $nombre = $cohorte['numero_cohorte'] . "." . substr($cohorte['anio_cohorte'], -2);
                        echo '<option value="'.$cohorte['id_cohorte'].'">'.$nombre.'</option>';
                    }
                ?>
            </select>
        </div>

        <div id="bloque-cursos" class="mt-4"></div>

        <div class="text-center mt-3">
            <button type="submit" class="btn btn-primary" style="display: none;" id="btn-guardar-cursos">
                <i class="fas fa-save"></i> Guardar asignación
            </button>
        </div>
    </form>
</div>

<script>
document.getElementById("selector-cohorte").addEventListener("change", function(){
    const cohorteId = this.value;
    const bloque = document.getElementById("bloque-cursos");
    const boton = document.getElementById("btn-guardar-cursos");
    bloque.innerHTML = "<p class='text-muted'>Cargando cursos...</p>";
    boton.style.display = "none";

    fetch("<?php echo SERVERURL; ?>ajax/cohorteAjax.php?cargar_cursos_cohorte="+cohorteId)
    .then(res => res.json())
    .then(data => {
        if(data && data.cursos){
            if(data.cursos.length > 0){
                let html = '<fieldset><legend><i class="fas fa-check-double"></i> &nbsp; Cursos disponibles</legend><div class="row">';
                data.cursos.forEach(curso => {
                    const checked = data.seleccionados.map(id => id.toString()).includes(curso.id.toString()) ? "checked" : "";
                    //const checked = data.seleccionados.includes(curso.id.toString()) ? "checked" : "";
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