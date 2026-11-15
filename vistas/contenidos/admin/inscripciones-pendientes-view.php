<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<head>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    $(document).ready(function() {
        // 1. Cargar la tabla
        function cargarPendientes() {
            $.ajax({
                url: '../../../ajax/buscarInscripcionesPendientes.php',
                method: 'POST',
                beforeSend: function() {
                    $('#tabla-container').html('<div class="text-center p-4"><i class="fas fa-spinner fa-spin fa-2x text-primary"></i><p>Cargando...</p></div>');
                },
                success: function(data) {
                    $('#tabla-container').html(data);
                }
            });
        }
        cargarPendientes();

        // 2. Ver comprobante
        $(document).on('click', '.btn-ver-comprobante', function() {
            const imgUrl = $(this).data('img');
            $('#imagenComprobante').attr('src', imgUrl);
            $('#modalComprobante').modal('show');
        });

        // 3. Aceptar / Rechazar
        $(document).on('click', '.btn-aceptar, .btn-rechazar', function() {
            const id = $(this).data('id');
            const accion = $(this).hasClass('btn-aceptar') ? 'aceptar' : 'rechazar';
            const confirmMsg = accion === 'aceptar' ? '¿Aceptar esta inscripción?' : '¿Rechazar esta inscripción?';

            if (!confirm(confirmMsg)) return;

                $.ajax({
                url: '../../../controladores/procesarInscripcionControlador.php',
                method: 'POST',
                data: { id: id, accion: accion },
                dataType: 'json',
                success: function(res) {
                    if (res.status === 'success') {
                        alert(res.message);
                        $('#fila-inscripcion-' + id).fadeOut(400, function() { $(this).remove(); });
                    } else {
                        alert('Error: ' + res.message);
                    }
                },
                error: function(xhr, status, error) {
                    // Este bloque capturará si el servidor devuelve algo que no es JSON
                    let errorMsg = 'Error crítico en el servidor.';
                    if (xhr.responseText) {
                        try {
                            let json = JSON.parse(xhr.responseText);
                            errorMsg = json.message || errorMsg;
                        } catch(e) {
                            errorMsg = xhr.responseText; // Muestra el HTML del error si no es JSON
                        }
                    }
                    alert(errorMsg);
                    console.error(xhr.responseText);
                }
            });
        });
    });
    </script>
</head>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Inscripciones Pendientes</h2>
        <span class="badge badge-warning p-2">Requieren revisión</span>
    </div>

    <div id="tabla-container">
        <div class="text-center p-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2 text-muted">Cargando inscripciones...</p>
        </div>
    </div>
</div>

<!-- Modal para ver la imagen -->
<div class="modal fade" id="modalComprobante" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Comprobante de Pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <img id="imagenComprobante" src="" alt="Comprobante" class="img-fluid rounded" style="max-height: 70vh;">
            </div>
        </div>
    </div>
</div>