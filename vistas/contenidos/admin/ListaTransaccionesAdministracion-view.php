<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<head>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {
        function cargarTransacciones(pagina = 1) {
            const busqueda = $('#busqueda').val().trim();
            const filtro   = $('#filtro').val();

            $.ajax({
                url: '../../../ajax/buscarTransacciones.php',
                method: 'POST',
                data: { busqueda, filtro, pagina },
                beforeSend: function () {
                    $('#tabla-container').html(
                        '<div class="text-center p-4">' +
                        '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>' +
                        '<p class="mt-2 text-muted">Cargando transacciones...</p>' +
                        '</div>'
                    );
                },
                success: function (data) {
                    $('#tabla-container').html(data);
                },
                error: function (xhr, status, error) {
                    console.error('Error al cargar:', error);
                    $('#tabla-container').html(
                        '<div class="alert alert-danger text-center">' +
                        '<i class="fas fa-exclamation-triangle"></i> ' +
                        'Error al cargar las transacciones: ' + error +
                        '</div>'
                    );
                }
            });
        }


        $('#busqueda, #filtro').on('input change', function () {
            cargarTransacciones(1);
        });


        $(document).on('click', '.pagina-link', function (e) {
            e.preventDefault();
            const pagina = $(this).data('pagina');
            if (pagina < 1) return;
            cargarTransacciones(pagina);
            $('html, body').animate({
                scrollTop: $('#tabla-container').offset().top - 50
            }, 300);
        });


        cargarTransacciones();
    });
    </script>
</head>

<style>
    .table td, .table th { vertical-align: middle; }
    .badge { font-size: 0.85em; }
</style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Lista de Transacciones</h2>
    </div>

    <!-- 🔍 Filtros -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" id="busqueda" class="form-control"
                       placeholder="Buscar por estudiante, CI, referencia o motivo..." aria-label="Buscar transacciones">
            </div>
        </div>
        <div class="col-md-4">
            <select id="filtro" class="form-control">
                <option value="todos">Todos los campos</option>
                <option value="estudiante">Estudiante / CI</option>
                <option value="motivo">Motivo de pago</option>
                <option value="referencia">Referencia</option>
            </select>
        </div>
    </div>


    <div id="tabla-container">
        <div class="text-center p-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2 text-muted">Cargando transacciones...</p>
        </div>
    </div>
</div>