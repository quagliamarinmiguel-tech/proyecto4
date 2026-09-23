<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once "./modelos/mainModel.php";
$conexion = mainModel::conectar();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Inscripción a Curso - Estudiante</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
    .precio-card {
        border: none;
        border-radius: 15px;
        box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        transition: all 0.3s ease;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
        border-left: 5px solid #007bff;
    }

    .precio-display {
        font-size: 2.5rem;
        font-weight: 800;
        background: linear-gradient(135deg, #28a745, #20c997);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        text-shadow: 2px 2px 4px rgba(0,0,0,0.1);
    }

    .precio-icono {
        color: #28a745;
        opacity: 0.8;
    }

    .info-adicional {
        background: #f8f9fa;
        padding: 15px;
        border-radius: 10px;
        margin-top: 15px;
    }

    .sticky-top {
        position: -webkit-sticky;
        position: sticky;
        z-index: 100;
    }

    #sinPrecio {
        border: 2px dashed #dee2e6;
        background: #fafafa;
    }

    .card-header.bg-primary {
        border-radius: 13px 13px 0 0 !important;
    }

    .card-footer {
        border-radius: 0 0 13px 13px !important;
    }
    </style>
</head>
<body class="bg-light">

<div class="container mt-4">
    <div class="card mx-auto shadow">
        <div class="card-header bg-primary text-white">
            <h4 class="card-title text-center mb-0">
                <i class="fas fa-graduation-cap"></i> Inscribirse a un Curso
            </h4>
        </div>
        <div class="card-body">
            <?php if (isset($_SESSION['usuario_spm'])): ?>
                <div class="alert alert-info text-center">
                    <i class="fas fa-user"></i> Estás inscrito como: <strong><?= $_SESSION['usuario_spm'] ?></strong>
                </div>
            <?php endif; ?>

            <form id="formInscripcion" method="POST" enctype="multipart/form-data">
                <div class="row">
                    <!-- Columna del formulario -->
                    <div class="col-md-8">
                        <!-- Fila 1: Curso -->
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="id_curso"><i class="fas fa-book"></i> Curso</label>
                                <select class="form-control" name="id_curso" id="id_curso" required>
                                    <option value="">Seleccione un curso</option>
                                    <?php
                                        // CONSULTA MODIFICADA: Incluye cursos intensivos y no intensivos
                                        $cursos = mainModel::conectar()->query("
                                            -- Cursos No Intensivos (Extendido y Técnico Profesional)
                                            SELECT 
                                                c.id_curso, 
                                                c.nombre_curso, 
                                                c.tipo_curso, 
                                                ch.pago_inscripcion as precio,
                                                'cohorte' as fuente
                                            FROM curso c 
                                            INNER JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk 
                                            INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte 
                                            WHERE c.estado_curso = 1 
                                            AND cv.estado_curso_vigente = 1
                                            AND ch.estado_cohorte = 'Vigente'
                                            AND c.tipo_curso IN ('Extendido', 'Técnico Profesional')
                                            
                                            UNION ALL
                                            
                                            -- Cursos Intensivos
                                            SELECT 
                                                c.id_curso, 
                                                c.nombre_curso, 
                                                c.tipo_curso, 
                                                pi.pago_inscripcion_int as precio,
                                                'intensivo' as fuente
                                            FROM curso c 
                                            INNER JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk 
                                            INNER JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
                                            WHERE c.estado_curso = 1 
                                            AND cv.estado_curso_vigente = 1
                                            AND pi.estado_periodo = 'Vigente'
                                            AND c.tipo_curso = 'Intensivo'
                                        ");
                                        
                                        if($cursos->rowCount() > 0) {
                                            foreach ($cursos as $curso) {
                                                $precio = $curso['precio'] ? number_format($curso['precio'], 2) : '0.00';
                                                $fuente = $curso['fuente'];
                                                
                                                echo "<option value='{$curso['id_curso']}' 
                                                      data-precio='{$curso['precio']}' 
                                                      data-tipo='{$curso['tipo_curso']}'
                                                      data-fuente='{$fuente}'>
                                                        {$curso['nombre_curso']} ({$curso['tipo_curso']})
                                                    </option>";
                                            }
                                        } else {
                                            echo "<option value=''>No hay cursos vigentes disponibles</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>

                        <!-- Fila 2: Sección -->
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="id_seccion_fk"><i class="fas fa-users"></i> Sección</label>
                                <select class="form-control" name="id_seccion_fk" id="id_seccion_fk" required>
                                    <option value="">Seleccione una sección</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila 3: Fechas -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="fecha_inscripcion"><i class="fas fa-calendar"></i> Fecha de Inscripción</label>
                                <input type="date" class="form-control" name="fecha_inscripcion" id="fecha_inscripcion" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fecha_abono"><i class="fas fa-calendar-times"></i> Fecha de Abandono (opcional)</label>
                                <input type="date" class="form-control" name="fecha_abono" id="fecha_abono">
                            </div>
                        </div>

                        <!-- Fila 4: Estado -->
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="estado_inscripcion"><i class="fas fa-check-circle"></i> Estado</label>
                                <select class="form-control" name="estado_inscripcion" id="estado_inscripcion" required>
                                    <option value="Activa">Activa</option>
                                </select>
                            </div>
                        </div>

                        <!-- Fila 5: Método de pago -->
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label for="metodo_pago"><i class="fas fa-credit-card"></i> Método de Pago</label>
                                <select class="form-control" name="metodo_pago" id="metodo_pago" required>
                                    <option value="">Seleccione</option>
                                    <option value="Efectivo">Efectivo</option>
                                    <option value="Transferencia">Transferencia</option>
                                    <option value="Pago Móvil">Pago Móvil</option>
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label for="fecha_pago"><i class="fas fa-calendar-check"></i> Fecha de Pago</label>
                                <input type="date" class="form-control" name="fecha_pago" id="fecha_pago" required>
                            </div>
                        </div>

                        <!-- Fila 6: Monto -->
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="monto"><i class="fas fa-dollar-sign"></i> Monto a Pagar</label>
                                <input type="number" step="0.01" min="0" class="form-control" name="monto" id="monto" required placeholder="Ingrese el monto">
                                <small class="form-text text-muted">Consulte el precio de referencia a la derecha →</small>
                            </div>
                        </div>

                        <!-- Fila 7: Comprobante -->
                        <div class="form-row">
                            <div class="form-group col-md-12">
                                <label for="comprobante"><i class="fas fa-file-image"></i> Comprobante de Pago <span class="text-danger">*</span></label>
                                <input type="file" class="form-control-file" name="comprobante" id="comprobante" accept=".jpg, .jpeg, .png, .gif, .webp, image/jpeg, image/png, image/gif, image/webp" required>
                                <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP</small>
                                <div class="invalid-feedback" id="comprobanteError">
                                    Por favor, seleccione una imagen válida (JPG, JPEG, PNG, GIF o WEBP)
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Columna de la tarjeta de precio -->
                    <div class="col-md-4">
                        <div class="sticky-top" style="top: 20px;">
                            <div id="precioCurso" class="card precio-card" style="display: none;">
                                <div class="card-header bg-primary text-white text-center">
                                    <h5 class="mb-0"><i class="fas fa-tag"></i> Precio de Referencia</h5>
                                </div>
                                <div class="card-body text-center py-4">
                                    <div class="precio-icono mb-3">
                                        <i class="fas fa-dollar-sign fa-3x text-success"></i>
                                    </div>
                                    <h2 class="card-title text-primary precio-display mb-2">$0.00</h2>
                                    <p class="card-text text-muted mb-3">Precio oficial del curso</p>
                                    
                                    <div class="info-adicional">
                                        <div class="d-flex justify-content-between mb-2">
                                            <small class="text-muted">Tipo:</small>
                                            <small class="text-dark" id="precio-tipo">-</small>
                                        </div>
                                        <div class="d-flex justify-content-between mb-2">
                                            <small class="text-muted">Estado:</small>
                                            <small class="text-success" id="precio-estado">Disponible</small>
                                        </div>
                                        <hr>
                                        <small class="text-info">
                                            <i class="fas fa-info-circle"></i> Este es el monto sugerido para la inscripción
                                        </small>
                                    </div>
                                </div>
                                <div class="card-footer bg-light text-center">
                                    <small class="text-muted">Actualizado al seleccionar el curso</small>
                                </div>
                            </div>

                            <!-- Estado cuando no hay curso seleccionado -->
                            <div id="sinPrecio" class="card border-secondary">
                                <div class="card-body text-center py-5">
                                    <i class="fas fa-shopping-cart fa-3x text-muted mb-3"></i>
                                    <h5 class="text-muted">Precio no seleccionado</h5>
                                    <p class="text-muted small">Seleccione un curso para ver el precio de referencia</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Botón -->
                <div class="form-row mt-4">
                    <div class="form-group col-md-12 text-center">
                        <button type="submit" class="btn btn-success btn-lg">
                            <i class="fas fa-check"></i> Inscribirse
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>

<script>
$(document).ready(function() {
    // Establecer fecha actual por defecto
    const hoy = new Date().toISOString().split('T')[0];
    $('#fecha_inscripcion').val(hoy);
    $('#fecha_pago').val(hoy);

    // Mostrar precio cuando se selecciona un curso
    $('#id_curso').on('change', function() {
        const selectedOption = $(this).find('option:selected');
        const precio = selectedOption.data('precio');
        const tipoCurso = selectedOption.data('tipo');
        const fuente = selectedOption.data('fuente');
        
        console.log("Curso seleccionado:", {
            precio: precio,
            tipo: tipoCurso,
            fuente: fuente
        });
        
        if (precio && parseFloat(precio) > 0) {
            const precioFormateado = '$' + parseFloat(precio).toFixed(2);
            
            // Mostrar en la tarjeta
            $('.precio-display').text(precioFormateado);
            
            // Mostrar tipo de curso
            $('#precio-tipo').text(tipoCurso);
            
            // Mostrar fuente del precio (información adicional)
            let fuenteTexto = '';
            if (fuente === 'intensivo') {
                fuenteTexto = '<i class="fas fa-bolt"></i> Curso Intensivo';
                $('#precio-estado').html('<i class="fas fa-check-circle"></i> Período Vigente');
            } else {
                fuenteTexto = '<i class="fas fa-calendar-alt"></i> Cohorte Vigente';
                $('#precio-estado').html('<i class="fas fa-check-circle"></i> Disponible');
            }
            
            // Actualizar información adicional
            $('.text-info small').html(`
                <i class="fas fa-info-circle"></i> ${fuenteTexto}
            `);
            
            // Mostrar/ocultar tarjetas
            $('#precioCurso').slideDown(300);
            $('#sinPrecio').slideUp(300);
            
            // Auto-completar el monto con el precio de referencia
            $('#monto').val(parseFloat(precio).toFixed(2));
            
            console.log("Precio mostrado:", precioFormateado);
        } else {
            $('#precioCurso').slideUp(300);
            $('#sinPrecio').slideDown(300);
            console.log("No hay precio disponible para este curso");
        }

        // Cargar secciones cuando se selecciona un curso
        const idCurso = $(this).val();
        const seccionSelect = $('#id_seccion_fk');
        
        if (idCurso === '') {
            seccionSelect.html('<option value="">Seleccione una sección</option>');
            return;
        }

        seccionSelect.html('<option value="">Cargando secciones...</option>');

        $.ajax({
            url: 'ajax/obtenerSeccionesPorCurso.php',
            method: 'POST',
            data: { id_curso: idCurso },
            success: function(data) {
                seccionSelect.html(data);
            },
            error: function() {
                seccionSelect.html('<option value="">Error al cargar secciones</option>');
            }
        });
    });

    // Validación de archivo de imagen
    $('#comprobante').change(function() {
        var fileInput = $(this)[0];
        var file = fileInput.files[0];
        var comprobanteError = $('#comprobanteError');
        
        if (file) {
            // Validar tipo de archivo
            var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
            if (!validTypes.includes(file.type)) {
                comprobanteError.show();
                fileInput.value = ''; // Limpiar el campo
                $(this).addClass('is-invalid');
                return false;
            }
            
            // Validar tamaño de archivo (opcional: máximo 5MB)
            var maxSize = 5 * 1024 * 1024; // 5MB en bytes
            if (file.size > maxSize) {
                comprobanteError.text('El archivo es demasiado grande. Máximo 5MB permitido.');
                comprobanteError.show();
                fileInput.value = '';
                $(this).addClass('is-invalid');
                return false;
            }
            
            // Si pasa todas las validaciones
            comprobanteError.hide();
            $(this).removeClass('is-invalid');
            $(this).addClass('is-valid');
        }
    });

    // Manejar método de pago
    $('#metodo_pago').on('change', function() {
        const metodo = $(this).val();
        const comprobante = $('#comprobante');
        const comprobanteSmall = comprobante.siblings('small');
        
        console.log("Método de pago cambiado a:", metodo);
        
        // EL COMPROBANTE SIEMPRE ESTÁ HABILITADO Y ES OBLIGATORIO
        comprobante.prop('disabled', false);
        comprobante.prop('required', true);
        
        // Solo cambiamos el mensaje informativo según el método de pago
        if (metodo === 'Pago Móvil') {
            comprobanteSmall.html('<i class="fas fa-info-circle"></i> Obligatorio - Se recomienda captura del comprobante móvil');
            comprobanteSmall.removeClass('text-muted text-info').addClass('text-primary');
        } else if (metodo === 'Efectivo') {
            comprobanteSmall.html('<i class="fas fa-info-circle"></i> Obligatorio - Foto del recibo o voucher de pago en efectivo');
            comprobanteSmall.removeClass('text-muted text-info').addClass('text-primary');
        } else if (metodo === 'Transferencia') {
            comprobanteSmall.html('<i class="fas fa-info-circle"></i> Obligatorio - Captura de pantalla de la transferencia bancaria');
            comprobanteSmall.removeClass('text-muted text-info').addClass('text-primary');
        } else {
            comprobanteSmall.html('<i class="fas fa-info-circle"></i> Obligatorio - Comprobante de pago requerido');
            comprobanteSmall.removeClass('text-info').addClass('text-muted');
        }
    });

    // Validación del formulario completo
    $('#formInscripcion').submit(function(e) {
        var comprobanteInput = $('#comprobante')[0];
        var comprobanteError = $('#comprobanteError');
        
        if (comprobanteInput.files.length === 0) {
            comprobanteError.text('El comprobante de pago es obligatorio');
            comprobanteError.show();
            $('#comprobante').addClass('is-invalid');
            e.preventDefault();
            return false;
        }
        
        var file = comprobanteInput.files[0];
        var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
        
        if (!validTypes.includes(file.type)) {
            comprobanteError.text('Por favor, seleccione una imagen válida (JPG, JPEG, PNG, GIF o WEBP)');
            comprobanteError.show();
            $('#comprobante').addClass('is-invalid');
            e.preventDefault();
            return false;
        }
        
        return true;
    });

    // Validación opcional: mostrar advertencia si el monto difiere mucho del precio de referencia
    $('#monto').on('blur', function() {
        const montoIngresado = parseFloat($(this).val());
        const precioReferencia = parseFloat($('#id_curso').find('option:selected').data('precio') || 0);
        
        if (montoIngresado && precioReferencia > 0) {
            const diferencia = Math.abs(montoIngresado - precioReferencia);
            const porcentajeDiferencia = (diferencia / precioReferencia) * 100;
            
            if (porcentajeDiferencia > 10) { // Si difiere más del 10%
                Swal.fire({
                    icon: 'info',
                    title: 'Verifique el monto',
                    text: `El monto ingresado ($${montoIngresado}) difiere del precio de referencia ($${precioReferencia}). ¿Está seguro?`,
                    confirmButtonText: 'Sí, continuar',
                    showCancelButton: true,
                    cancelButtonText: 'Corregir'
                }).then((result) => {
                    if (result.dismiss === Swal.DismissReason.cancel) {
                        $(this).focus();
                    }
                });
            }
        }
    });

    // Manejar envío del formulario
    $('#formInscripcion').on('submit', function(e) {
        e.preventDefault();
        
        const form = this;
        const formData = new FormData(form);

        // Mostrar loading
        Swal.fire({
            title: 'Procesando inscripción...',
            text: 'Por favor espera mientras procesamos tu solicitud.',
            allowOutsideClick: false,
            showConfirmButton: false,
            willOpen: () => {
                Swal.showLoading();
            }
        });

        $.ajax({
            url: 'controladores/inscripcionControlador.php',
            method: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            dataType: 'json',
            success: function(data) {
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Inscripción exitosa!',
                        text: data.mensaje,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        window.location.href = 'index.php?views=Estudiante/inscribirse';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error en la inscripción',
                        text: data.mensaje
                    });
                }
            },
            error: function(xhr) {
                let mensaje = 'Error inesperado';
                try {
                    const response = JSON.parse(xhr.responseText);
                    mensaje = response.mensaje || mensaje;
                } catch (e) {
                    mensaje = xhr.responseText || mensaje;
                }
                
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: mensaje
                });
            }
        });
    });
});
</script>

</body>
</html>
