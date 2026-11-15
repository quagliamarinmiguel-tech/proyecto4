<?php
require_once __DIR__ . "/../../../controladores/ListInscripcionesAdministracionControlador.php";
$controlador = new ListInscripcionesControlador();


$por_pagina = 5;
$pagina = isset($_GET['pagina']) ? max(1, intval($_GET['pagina'])) : 1;
$inicio = ($pagina - 1) * $por_pagina;


$inscripciones = $controlador->listar_inscripciones_archivadas($inicio, $por_pagina);
$total = $controlador->contar_inscripciones_archivadas();
$total_paginas = ceil($total / $por_pagina);
?>

<div class="container mt-5">
  <h2 class="mb-4">Inscripciones Archivadas</h2>

  <!-- 🔍 Buscador y filtro (visual; funcionalidad opcional) -->
  <div class="form-row mb-4">
    <div class="col-md-6">
      <input type="text" class="form-control" placeholder="Buscar archivadas..." id="busqueda-archivadas">
    </div>
    <div class="col-md-3">
      <select class="form-control" id="filtro-archivadas">
        <option value="nombre">Nombre</option>
        <option value="apellido">Apellido</option>
        <option value="curso">Curso</option>
        <option value="tipo">Tipo</option>
        <option value="seccion">Sección</option>
        <option value="estado">Estado</option>
      </select>
    </div>
  </div>


  <p class="text-muted text-right">
    Mostrando página <?= $pagina ?> de <?= $total_paginas ?> — Total: <?= $total ?> inscripciones archivadas
  </p>


  <div id="tabla-inscripciones-archivadas"></div>

</div>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
$(document).on('click', '.btn-desarchivar-inscripcion', function () {
  const id = $(this).data('id');
  const paginaActual = $('.pagination .active .page-link').text().trim();

  Swal.fire({
    title: '¿Quieres desarchivar esta inscripción?',
    text: 'Todas las transacciones ligadas a esta inscripción serán desarchivadas también.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, desarchivar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '../../../controladores/desarchivarInscripcionControlador.php',
        method: 'POST',
        data: { id_inscripcion: id },
        dataType: 'json',
        success: function (resp) {
          if (resp.status === 'success') {
            Swal.fire('Desarchivado', resp.mensaje, 'success').then(() => {
              $.post('../../../ajax/obtenerInscripcionesArchivadas.php', { pagina: paginaActual }, function (html) {
                $('#tabla-inscripciones-archivadas').html(html);
              });
            });
          } else {
            Swal.fire('Error', resp.mensaje, 'error');
          }
        },
        error: function () {
          Swal.fire('Error', 'No se pudo contactar al servidor.', 'error');
        }
      });
    }
  });
});

function cargarInscripcionesArchivadas(pagina = 1) {
  const busqueda = $('#busqueda-archivadas').val().trim();
  const filtro = $('#filtro-archivadas').val();

  $.post('../../../ajax/obtenerInscripcionesArchivadas.php', {
    pagina: pagina,
    busqueda: busqueda,
    filtro: filtro
  }, function (html) {
    $('#tabla-inscripciones-archivadas').html(html);
  });
}

$(document).ready(function () {
  cargarInscripcionesArchivadas();
});

$(document).on('input', '#busqueda-archivadas', function () {
  cargarInscripcionesArchivadas();
});

$(document).on('change', '#filtro-archivadas', function () {
  cargarInscripcionesArchivadas();
});

$(document).on('click', '.pagina-link-archivadas', function (e) {
  e.preventDefault();
  const pagina = $(this).data('pagina');
  cargarInscripcionesArchivadas(pagina);
});
</script>
