<?php
$contexto_exportar = 'cohortes';

if (isset($_POST['exportar_excel'])) {
    require_once __DIR__ . "/../../../exportadores/exportar_excel_cohortes.php";
    exportar_excel_cohortes($cohortes);
    exit;
}

if (isset($_POST['exportar_pdf'])) {
    require_once __DIR__ . "/../../../exportadores/exportar_pdf_cohortes.php";
    exportar_pdf_cohortes($cohortes);
    exit;
}
?>

<div class="full-box page-header">
    <h3 class="text-left">
        <i class="fas fa-calendar-alt fa-fw"></i> &nbsp; LISTA DE COHORTES
    </h3>
    <p class="text-justify">
        Visualiza todas las cohortes vigentes registradas en el sistema.
    </p>
</div>

    <?php include_once __DIR__ . "/../../../fragmentos/botones_exportar.php"; ?>

<div class="container-fluid mb-4">
    <form id="buscadorCohorteForm" autocomplete="off">
        <div class="row justify-content-center">
            <div class="col-md-3">
                <label class="bmd-label-floating">N° Cohorte</label>
                <input type="number" class="form-control" name="numero_cohorte" min="1" max="99" required>
            </div>
            <div class="col-md-3">
                <label class="bmd-label-floating">Año</label>
                <input type="number" class="form-control" name="anio_cohorte" min="20" max="99" required>
            </div>
            <div class="col-12 text-center mt-3">
                <button type="submit" class="btn btn-info btn-sm">
                    <i class="fas fa-search"></i> Buscar
                </button>
                <button type="button" id="limpiarBusqueda" class="btn btn-danger btn-sm ml-2">
                    <i class="fas fa-times"></i> Limpiar
                </button>
            </div>
        </div>
    </form>
</div>

<!-- 🟦 Lista principal -->
<div class="container-fluid" id="lista-principal">
    <?php
        require_once "./controladores/cohorteControladorAdministracion.php";
        $controlador = new cohorteControladorAdministracion();
        $pagina_actual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
        echo $controlador->paginador_cohorte_controlador($pagina_actual, 10, "ListCohorteAdministracion", "");
    ?>
</div>

<!-- 🟧 Resultados por búsqueda AJAX -->
<div class="container-fluid" id="lista-busqueda" style="display:none;"></div>


<script>
document.getElementById("buscadorCohorteForm").addEventListener("submit", function(e){
    e.preventDefault();

    const form = e.target;
    const numero = form.numero_cohorte.value.trim();
    const anio = form.anio_cohorte.value.trim();

    if(numero === "" || anio === ""){
        alert("Debes ingresar número y año de cohorte.");
        return;
    }

    fetch("<?php echo SERVERURL; ?>ajax/cohorteBusquedaAjax.php", {
        method: "POST",
        headers: { "Content-Type": "application/x-www-form-urlencoded" },
        body: "numero=" + numero + "&anio=" + anio
    })
    .then(res => res.text())
    .then(html => {
        document.getElementById("lista-principal").style.display = "none";
        document.getElementById("lista-busqueda").innerHTML = html;
        document.getElementById("lista-busqueda").style.display = "block";
    })
    .catch(err => console.error("Error al buscar cohortes", err));
});

document.getElementById("limpiarBusqueda").addEventListener("click", function(){
    document.getElementById("buscadorCohorteForm").reset();
    document.getElementById("lista-busqueda").style.display = "none";
    document.getElementById("lista-principal").style.display = "block";
});
</script>
