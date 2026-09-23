<?php
if ($_SESSION['cargo_spm'] != 1) {
    echo "<div class='alert alert-danger'>Acceso denegado. Solo administradores.</div>";
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Gestión de Usuarios</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .user-row { cursor: pointer; }
        .user-row:hover { background-color: #eef5ff; }
        .table-wrapper { max-height: 600px; overflow-y: auto; }
        .badge-role-1 { background-color: #dc3545; }
        .badge-role-2 { background-color: #fd7e14; }
        .badge-role-3 { background-color: #ffc107; color: #000; }
        .badge-role-4 { background-color: #17a2b8; }
        .badge-role-5 { background-color: #28a745; }
    </style>
</head>
<body>

<div class="container mt-4">
    <h2 class="mb-3">Gestión de Usuarios</h2>

    <div class="input-group mb-3">
        <input type="text" id="busqueda" class="form-control" placeholder="Buscar por nombre, cédula o rol...">
    </div>

    <div class="table-wrapper card">
        <table class="table table-hover table-sm mb-0">
            <thead class="thead-dark">
                <tr>
                    <th>#</th>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody id="tablaUsuarios">
                <tr><td colspan="6" class="text-center">Cargando usuarios...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal Editar Usuario -->
<div class="modal fade" id="modalEditarUsuario" tabindex="-1" role="dialog">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header bg-primary text-white">
        <h5 class="modal-title">Editar Usuario</h5>
        <button type="button" class="close text-white" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <div id="modalAlerta"></div>
        <input type="hidden" id="edit_profile_id">
        <input type="hidden" id="edit_student_id">
        <input type="hidden" id="edit_role_id">

        <div id="camposEstudiante" style="display:none;">
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Nombre</label>
              <input type="text" class="form-control" id="edit_name">
            </div>
            <div class="form-group col-md-6">
              <label>Apellido</label>
              <input type="text" class="form-control" id="edit_last_name">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Cédula</label>
              <input type="text" class="form-control" id="edit_ci">
            </div>
            <div class="form-group col-md-6">
              <label>Fecha de nacimiento</label>
              <input type="date" class="form-control" id="edit_birthday">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Correo</label>
              <input type="email" class="form-control" id="edit_email">
            </div>
            <div class="form-group col-md-6">
              <label>Teléfono</label>
              <input type="text" class="form-control" id="edit_phone">
            </div>
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>WhatsApp</label>
              <input type="text" class="form-control" id="edit_whatsapp_phone">
            </div>
            <div class="form-group col-md-6">
              <label>Nivel educativo</label>
              <input type="text" class="form-control" id="edit_education_level">
            </div>
          </div>
          <div class="form-group">
            <label>Dirección</label>
            <input type="text" class="form-control" id="edit_address">
          </div>
          <div class="form-row">
            <div class="form-group col-md-6">
              <label>Lugar de nacimiento</label>
              <input type="text" class="form-control" id="edit_birthplace">
            </div>
            <div class="form-group col-md-6">
              <label>Oficio actual</label>
              <input type="text" class="form-control" id="edit_current_work">
            </div>
          </div>
          <div class="form-group">
            <label>¿Trabaja actualmente?</label>
            <select class="form-control" id="edit_work">
              <option value="0">No</option>
              <option value="1">Sí</option>
            </select>
          </div>
        </div>

        <div id="camposGenerales">
          <div class="form-group">
            <label>Estado de la cuenta</label>
            <select class="form-control" id="edit_state">
              <option value="1">Activa</option>
              <option value="0">Inactiva</option>
            </select>
          </div>
          <p class="text-muted small">Este usuario no tiene datos personales de estudiante registrados.</p>
        </div>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        <button type="button" class="btn btn-success" id="btnGuardarUsuario">Guardar cambios</button>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
const SERVER = '<?php echo SERVERURL; ?>';

$(document).ready(function() {
    cargarUsuarios();

    $('#busqueda').on('input', function() {
        const q = $(this).val().toLowerCase();
        $('#tablaUsuarios tr').each(function() {
            $(this).toggle($(this).text().toLowerCase().includes(q));
        });
    });

    $('#btnGuardarUsuario').click(guardarUsuario);
});

function cargarUsuarios() {
    $.ajax({
        url: SERVER + 'ajax/listarUsuariosAdmin.php',
        type: 'GET',
        dataType: 'json',
        success: function(res) {
            if (!res.success) {
                $('#tablaUsuarios').html('<tr><td colspan="6" class="text-danger">' + res.message + '</td></tr>');
                return;
            }
            if (!res.data.length) {
                $('#tablaUsuarios').html('<tr><td colspan="6" class="text-center">No hay usuarios.</td></tr>');
                return;
            }
            let html = '';
            res.data.forEach((u, i) => {
                const nombre = u.is_student ? `${u.name} ${u.last_name}` : '(sin nombre)';
                const ci = u.ci || '-';
                const estado = u.state == 1 ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>';
                html += `<tr class="user-row" data-profile='${JSON.stringify(u)}'>
                    <td>${i+1}</td>
                    <td>${nombre}</td>
                    <td>${ci}</td>
                    <td><span class="badge badge-role-${u.role_id}">${u.role_name}</span></td>
                    <td>${estado}</td>
                    <td><button class="btn btn-sm btn-primary">Editar</button></td>
                </tr>`;
            });
            $('#tablaUsuarios').html(html);
        },
        error: function() {
            $('#tablaUsuarios').html('<tr><td colspan="6" class="text-danger">Error al cargar.</td></tr>');
        }
    });
}

$(document).on('click', '.user-row', function() {
    const u = $(this).data('profile');
    abrirModal(u);
});

function abrirModal(u) {
    $('#modalAlerta').html('');
    $('#edit_profile_id').val(u.profile_id);
    $('#edit_student_id').val(u.student_id || '');
    $('#edit_role_id').val(u.role_id);
    $('#edit_state').val(u.state);

    if (u.is_student && u.student_id) {
        // Cargar datos completos del estudiante
        $.ajax({
            url: SERVER + 'controladores/obtenerUsuarioAdminControlador.php',
            type: 'POST',
            data: { profile_id: u.profile_id },
            dataType: 'json',
            success: function(res) {
                if (!res.success) {
                    $('#modalAlerta').html('<div class="alert alert-danger">' + res.message + '</div>');
                    return;
                }
                const s = res.data.student;
                $('#edit_name').val(s.name || '');
                $('#edit_last_name').val(s.last_name || '');
                $('#edit_ci').val(s.ci || '');
                $('#edit_birthday').val(s.birthday || '');
                $('#edit_email').val(s.email || '');
                $('#edit_phone').val(s.phone || '');
                $('#edit_whatsapp_phone').val(s.whatsapp_phone || '');
                $('#edit_education_level').val(s.education_level || '');
                $('#edit_address').val(s.address || '');
                $('#edit_birthplace').val(s.birthplace || '');
                $('#edit_current_work').val(s.current_work || '');
                $('#edit_work').val(s.work ? '1' : '0');
                $('#camposEstudiante').show();
                $('#camposGenerales').hide();
                $('#modalEditarUsuario').modal('show');
            },
            error: function() {
                $('#modalAlerta').html('<div class="alert alert-danger">Error de conexión.</div>');
            }
        });
    } else {
        $('#camposEstudiante').hide();
        $('#camposGenerales').show();
        $('#modalEditarUsuario').modal('show');
    }
}

function guardarUsuario() {
    const payload = {
        profile_id: $('#edit_profile_id').val(),
        student_id: $('#edit_student_id').val(),
        state: $('#edit_state').val()
    };

    if ($('#edit_student_id').val()) {
        payload.name = $('#edit_name').val();
        payload.last_name = $('#edit_last_name').val();
        payload.ci = $('#edit_ci').val();
        payload.birthday = $('#edit_birthday').val();
        payload.email = $('#edit_email').val();
        payload.phone = $('#edit_phone').val();
        payload.whatsapp_phone = $('#edit_whatsapp_phone').val();
        payload.education_level = $('#edit_education_level').val();
        payload.address = $('#edit_address').val();
        payload.birthplace = $('#edit_birthplace').val();
        payload.current_work = $('#edit_current_work').val();
        payload.work = $('#edit_work').val();
    }

    $('#btnGuardarUsuario').prop('disabled', true).text('Guardando...');

    $.ajax({
        url: SERVER + 'controladores/actualizarUsuarioAdminControlador.php',
        type: 'POST',
        data: payload,
        dataType: 'json',
        success: function(res) {
            if (res.success) {
                $('#modalAlerta').html('<div class="alert alert-success">' + res.message + '</div>');
                setTimeout(() => {
                    $('#modalEditarUsuario').modal('hide');
                    cargarUsuarios();
                }, 1200);
            } else {
                $('#modalAlerta').html('<div class="alert alert-danger">' + res.message + '</div>');
            }
        },
        error: function() {
            $('#modalAlerta').html('<div class="alert alert-danger">Error de conexión.</div>');
        },
        complete: function() {
            $('#btnGuardarUsuario').prop('disabled', false).text('Guardar cambios');
        }
    });
}
</script>
</body>
</html>