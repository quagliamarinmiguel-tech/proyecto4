<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
?>

<head>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

    <script>
    $(document).ready(function () {

        function cargarEstudiantes(pagina = 1) {
            const busqueda = $('#busqueda').val().trim();
            const filtro   = $('#filtro').val();

            $.ajax({
                url: '../../../ajax/buscarEstudiantes.php',
                method: 'POST',
                data: { busqueda, filtro, pagina },
                beforeSend: function () {
                    $('#tabla-container').html(
                        '<div class="text-center p-4">' +
                        '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>' +
                        '<p class="mt-2 text-muted">Cargando estudiantes...</p>' +
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
                        'Error al cargar los estudiantes: ' + error +
                        '</div>'
                    );
                }
            });
        }


        $('#busqueda, #filtro').on('input change', function () {
            cargarEstudiantes(1);
        });


        $(document).on('click', '.pagina-link', function (e) {
            e.preventDefault();
            const pagina = $(this).data('pagina');
            if (pagina < 1) return;
            cargarEstudiantes(pagina);
            $('html, body').animate({
                scrollTop: $('#tabla-container').offset().top - 50
            }, 300);
        });


        $(document).on('click', '.ver-estudiante', function () {
            let est = $(this).data('estudiante');
            if (typeof est === 'string') {
                try { est = JSON.parse(est); } catch (e) { return; }
            }

            const mapa = {
                ci: 'CI',
                name: 'Nombre',
                last_name: 'Apellido',
                birthday: 'Fecha de nacimiento',
                phone: 'Teléfono',
                email: 'Email',
                address: 'Dirección',
                birthplace: 'Lugar de nacimiento',
                education_level: 'Nivel educativo',
                work: '¿Trabaja?',
                current_work: 'Trabajo actual',
                whatsapp_phone: 'WhatsApp',
                state: 'Estado'
            };

            let html = '<table class="table table-sm table-striped mb-0"><tbody>';
            Object.keys(mapa).forEach(function (key) {
                let valor = est[key];
                if (key === 'work')  valor = valor ? 'Sí' : 'No';
                if (key === 'state') valor = (parseInt(valor) === 1) ? 'Activo' : 'Inactivo';
                if (valor === null || valor === undefined || valor === '') valor = '—';
                html += '<tr><th class="w-50">' + mapa[key] + '</th><td>' +
                        $('<div>').text(valor).html() + '</td></tr>';
            });
            html += '</tbody></table>';

            $('#detalleEstudianteBody').html(html);
            $('#modalDetalleEstudiante').modal('show');
        });

        // Carga inicial
        cargarEstudiantes();
    });
    </script>
</head>

<style>
    .table td, .table th { vertical-align: middle; }
    .badge { font-size: 0.85em; }
    .ver-estudiante i { pointer-events: none; }
</style>

<div class="container mt-5">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2>Estudiantes Registrados</h2>
    </div>

    <!-- 🔍 Filtros -->
    <div class="row mb-4">
        <div class="col-md-8">
            <div class="input-group">
                <input type="text" id="busqueda" class="form-control"
                       placeholder="Buscar estudiantes..." aria-label="Buscar estudiantes">
            </div>
        </div>
        <div class="col-md-4">
            <select id="filtro" class="form-control">
                <option value="todos">Todos los campos</option>
                <option value="ci">CI</option>
                <option value="name">Nombre</option>
                <option value="last_name">Apellido</option>
                <option value="email">Email</option>
                <option value="estado">Estado</option>
            </select>
        </div>
    </div>


    <div id="tabla-container">
        <div class="text-center p-4">
            <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
            <p class="mt-2 text-muted">Cargando estudiantes...</p>
        </div>
    </div>
</div>

<!-- Modal de detalle -->
<div class="modal fade" id="modalDetalleEstudiante" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Detalle del Estudiante</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body" id="detalleEstudianteBody"></div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>