<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


if($_SESSION['cargo_spm']!=5 ){
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}

// Pasar datos de sesión directamente a JavaScript
$session_data = [
    'id_usuario' => $_SESSION['id_usuario'] ?? null,
    'id_estudiante' => $_SESSION['id_estudiante'] ?? null,
    'cargo_spm' => $_SESSION['cargo_spm'] ?? null
];
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Editar Perfil - Estudiante</title>
    <style>
        :root {
            --primary-color: #3498db;
            --primary-dark: #2980b9;
            --secondary-color: #2ecc71;
            --secondary-dark: #27ae60;
            --danger-color: #e74c3c;
            --text-color: #333;
            --bg-color: #f9f9f9;
            --card-bg: #fff;
            --border-color: #ddd;
            --shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        body {
            background-color: var(--bg-color);
            color: var(--text-color);
            line-height: 1.6;
            padding: 20px;
        }

        .container {
            max-width: 800px;
            margin: 0 auto;
        }

        .header {
            text-align: center;
            margin-bottom: 30px;
        }

        .header h1 {
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .card {
            background-color: var(--card-bg);
            border-radius: 10px;
            box-shadow: var(--shadow);
            padding: 30px;
            margin-bottom: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        label {
            display: block;
            margin-bottom: 8px;
            font-weight: 600;
            color: var(--text-color);
        }

        .input-group {
            position: relative;
        }

        input[type="email"],
        input[type="tel"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            border: 1px solid var(--border-color);
            border-radius: 5px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        input:focus {
            outline: none;
            border-color: var(--primary-color);
            box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
        }

        .btn {
            display: inline-block;
            padding: 12px 25px;
            background-color: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            text-align: center;
        }

        .btn:hover {
            background-color: var(--primary-dark);
            transform: translateY(-2px);
        }

        .btn-secondary {
            background-color: var(--secondary-color);
        }

        .btn-secondary:hover {
            background-color: var(--secondary-dark);
        }

        .form-actions {
            display: flex;
            justify-content: space-between;
            margin-top: 30px;
        }

        .alert {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            display: none;
        }

        .alert-success {
            background-color: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }

        .alert-error {
            background-color: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }

        .password-toggle {
            position: absolute;
            right: 10px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            cursor: pointer;
            color: #777;
        }

        .current-data {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
        }

        @media (max-width: 768px) {
            .form-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>Editar Perfil</h1>
            <p>Actualiza tu información personal</p>
        </div>

        <div class="alert alert-success" id="successAlert">
            ¡Tus datos se han actualizado correctamente!
        </div>

        <div class="alert alert-error" id="errorAlert">
            Ha ocurrido un error al actualizar tus datos. Por favor, intenta nuevamente.
        </div>

        <div class="card">
            <div class="current-data">
                <h3>Datos Actuales</h3>
                <p><strong>Correo:</strong> <span id="currentEmail">cargando...</span></p>
                <p><strong>Teléfono:</strong> <span id="currentPhone">cargando...</span></p>
            </div>

            <form id="profileForm">
                <div class="form-group">
                    <label for="email">Correo Electrónico</label>
                    <input type="email" id="email" name="email" required>
                </div>

                <div class="form-group">
                    <label for="phone">Teléfono</label>
                    <input type="tel" id="phone" name="phone" required>
                </div>

                <div class="form-group">
                    <label for="currentPassword">Contraseña Actual (para verificación)</label>
                    <div class="input-group">
                        <input type="password" id="currentPassword" name="currentPassword" required>
                        <button type="button" class="password-toggle" data-target="currentPassword">👁️</button>
                    </div>
                </div>

                <div class="form-group">
                    <label for="newPassword">Nueva Contraseña (opcional)</label>
                    <div class="input-group">
                        <input type="password" id="newPassword" name="newPassword">
                        <button type="button" class="password-toggle" data-target="newPassword">👁️</button>
                    </div>
                    <small>Deja este campo vacío si no deseas cambiar tu contraseña</small>
                </div>

                <div class="form-group">
                    <label for="confirmPassword">Confirmar Nueva Contraseña</label>
                    <div class="input-group">
                        <input type="password" id="confirmPassword" name="confirmPassword">
                        <button type="button" class="password-toggle" data-target="confirmPassword">👁️</button>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="button" class="btn btn-secondary" id="cancelBtn">Cancelar</button>
                    <button type="submit" class="btn" id="submitBtn">Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    
    <script>
        // Datos de sesión pasados directamente desde PHP
        const sessionData = <?php echo json_encode($session_data); ?>;

        $(document).ready(function() {
            console.log('Datos de sesión:', sessionData);
            
            // Cargar datos actuales del estudiante
            loadCurrentData();

            // Toggle para mostrar/ocultar contraseñas
            $('.password-toggle').click(function() {
                const targetId = $(this).data('target');
                const input = $('#' + targetId);
                const type = input.attr('type') === 'password' ? 'text' : 'password';
                input.attr('type', type);
                $(this).text(type === 'password' ? '👁️' : '🙈');
            });

            // Validación del formulario
            $('#profileForm').submit(function(e) {
                e.preventDefault();
                
                // Validar que las contraseñas coincidan si se está cambiando
                const newPassword = $('#newPassword').val();
                const confirmPassword = $('#confirmPassword').val();
                
                if (newPassword && newPassword !== confirmPassword) {
                    showAlert('error', 'Las contraseñas no coinciden');
                    return;
                }
                
                // Enviar datos
                updateProfile();
            });

            // Botón cancelar
            $('#cancelBtn').click(function() {
                if (confirm('¿Estás seguro de que deseas cancelar? Los cambios no guardados se perderán.')) {
                    loadCurrentData(); // Restablecer valores
                    $('#currentPassword, #newPassword, #confirmPassword').val('');
                }
            });
        });

        function loadCurrentData() {
            console.log('Cargando datos con sesión:', sessionData);
            
            // AJAX para obtener los datos actuales del estudiante
            $.ajax({
                url: '../../../controladores/obtenerPerfilEstudiante.php',
                type: 'POST',
                data: {
                    action: 'get_profile',
                    id_usuario: sessionData.id_usuario,
                    id_estudiante: sessionData.id_estudiante,
                    cargo_spm: sessionData.cargo_spm
                },
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta:', response);
                    if (response.success) {
                        $('#currentEmail').text(response.data.correo_estudiante);
                        $('#currentPhone').text(response.data.telefono_estudiante);
                        $('#email').val(response.data.correo_estudiante);
                        $('#phone').val(response.data.telefono_estudiante);
                    } else {
                        let errorMsg = 'Error al cargar los datos: ' + response.message;
                        if (response.debug) {
                            errorMsg += ' (Debug: ' + JSON.stringify(response.debug) + ')';
                        }
                        showAlert('error', errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en AJAX:', error);
                    console.log('Respuesta completa:', xhr.responseText);
                    showAlert('error', 'Error de conexión: ' + error);
                }
            });
        }

        function updateProfile() {
            const submitBtn = $('#submitBtn');
            submitBtn.prop('disabled', true).text('Guardando...');
            
            // Obtener datos del formulario
            const formData = {
                action: 'update_profile',
                id_usuario: sessionData.id_usuario,
                id_estudiante: sessionData.id_estudiante,
                cargo_spm: sessionData.cargo_spm,
                email: $('#email').val(),
                phone: $('#phone').val(),
                currentPassword: $('#currentPassword').val(),
                newPassword: $('#newPassword').val()
            };

            console.log('Enviando datos:', formData);

            // AJAX para actualizar el perfil
            $.ajax({
                url: '../../../controladores/actualizarPerfilEstudiante.php',
                type: 'POST',
                data: formData,
                dataType: 'json',
                success: function(response) {
                    console.log('Respuesta actualización:', response);
                    if (response.success) {
                        showAlert('success', 'Perfil actualizado correctamente');
                        // Actualizar datos actuales mostrados
                        $('#currentEmail').text(formData.email);
                        $('#currentPhone').text(formData.phone);
                        // Limpiar campos de contraseña
                        $('#currentPassword').val('');
                        $('#newPassword').val('');
                        $('#confirmPassword').val('');
                    } else {
                        let errorMsg = 'Error al actualizar: ' + response.message;
                        if (response.debug) {
                            errorMsg += ' (Debug: ' + JSON.stringify(response.debug) + ')';
                        }
                        showAlert('error', errorMsg);
                    }
                },
                error: function(xhr, status, error) {
                    console.error('Error en AJAX:', error);
                    console.log('Respuesta completa:', xhr.responseText);
                    showAlert('error', 'Error de conexión: ' + error);
                },
                complete: function() {
                    submitBtn.prop('disabled', false).text('Guardar Cambios');
                }
            });
        }

        function showAlert(type, message) {
            const alertElement = type === 'success' ? $('#successAlert') : $('#errorAlert');
            alertElement.text(message).fadeIn();
            
            setTimeout(function() {
                alertElement.fadeOut();
            }, 5000);
        }
    </script>
</body>
</html>
