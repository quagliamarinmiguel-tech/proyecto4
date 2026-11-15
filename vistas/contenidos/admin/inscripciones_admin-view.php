<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


$resultPrecios = mainModel::supabaseGet('period_costs', 'cost_description,price');
$precios = $resultPrecios['data'] ?? [];

$precioModulo = 0;
$precioArancel = 0;
$precioArt13 = 0;

foreach ($precios as $p) {
    $desc = strtolower(trim($p['cost_description']));
    if (strpos($desc, 'modulo') !== false) {
        $precioModulo = floatval($p['price']);
    } elseif (strpos($desc, 'arancel') !== false) {
        $precioArancel = floatval($p['price']);
    } elseif (strpos($desc, 'art') !== false || strpos($desc, '13') !== false) {
        $precioArt13 = floatval($p['price']);
    }
}

?>

<head>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=6LfbzLwtAAAAAIprBfjlKu0kaztX4515xgt6Wdoq" async defer></script>
    
    <script>
    $(document).ready(function() {
        
        
        function cargarInscripciones(pagina = 1) {
            const busqueda = $('#busqueda').val().trim();
            const filtro = $('#filtro').val();

            $.ajax({
                url: '../../../ajax/buscarInscripciones.php',
                method: 'POST',
                data: { busqueda: busqueda, filtro: filtro, pagina: pagina },
                beforeSend: function() {
                    $('#tabla-container').html(
                        '<div class="text-center p-4">' +
                        '<i class="fas fa-spinner fa-spin fa-2x text-primary"></i>' +
                        '<p class="mt-2 text-muted">Cargando inscripciones...</p>' +
                        '</div>'
                    );
                },
                success: function(data) {
                    $('#tabla-container').html(data);
                },
                error: function(xhr, status, error) {
                    $('#tabla-container').html(
                        '<div class="alert alert-danger text-center">' +
                        '<i class="fas fa-exclamation-triangle"></i> Error al cargar las inscripciones: ' + error +
                        '</div>'
                    );
                }
            });
        }

        $('#busqueda, #filtro').on('input change', function() { cargarInscripciones(1); });
        $(document).on('click', '.pagina-link', function(e) {
            e.preventDefault();
            cargarInscripciones($(this).data('pagina'));
            $('html, body').animate({ scrollTop: $('#tabla-container').offset().top - 50 }, 300);
        });
        cargarInscripciones();


        $('#id_curso').on('change', function() {
            const periodCourseId = $(this).val();
            const $selectSeccion = $('#id_seccion_fk');
            const $inputPeriodCourse = $('#period_course_id');

            $inputPeriodCourse.val(periodCourseId);
            $selectSeccion.html('<option value="">Cargando secciones...</option>');

            if (!periodCourseId) {
                $selectSeccion.html('<option value="">Seleccione una sección</option>');
                $('#precioCurso').hide();
                $('#sinPrecio').show();
                return;
            }

            $.ajax({
                url: '../../../ajax/obtenerSeccionesPorCurso.php',
                method: 'POST',
                data: { period_course_id: periodCourseId },
                success: function(data) {
                    $selectSeccion.html(data);
                },
                error: function() {
                    $selectSeccion.html('<option value="">Error al cargar secciones</option>');
                }
            });


            const selectedOption = $(this).find('option:selected');
            const priceUSD = parseFloat(selectedOption.data('price'));
            const tipoCurso = selectedOption.text();

            if (!isNaN(priceUSD) && priceUSD > 0) {
                $('#sinPrecio').hide();
                $('#precioCurso').show();
                
                $('#precioUSD').text('$' + priceUSD.toFixed(2));
                $('#precio-tipo').text(tipoCurso);
                $('#precioVES').text('Calculando...');
                
                fetch('https://ve.dolarapi.com/v1/dolares/oficial')
                    .then(response => response.json())
                    .then(data => {
                        const tasaBCV = data.promedio;
                        const priceVES = priceUSD * tasaBCV;
                        $('#precioVES').text('Bs. ' + priceVES.toFixed(2));
                    })
                    .catch(error => {
                        $('#precioVES').text('Error al calcular Bs.');
                    });
            } else {
                $('#precioCurso').hide();
                $('#sinPrecio').show();
            }
        });


        const formInscripcion = document.getElementById('formInscripcion');
        
        if (formInscripcion) {
            formInscripcion.addEventListener('submit', function(e) {
                e.preventDefault();

                const submitBtn = formInscripcion.querySelector('button[type="submit"]');
                const originalText = submitBtn.innerHTML;
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Procesando...';
                submitBtn.disabled = true;

                grecaptcha.ready(function() {
                    grecaptcha.execute('6LfbzLwtAAAAAIprBfjlKu0kaztX4515xgt6Wdoq', {action: 'submit_inscripcion'})
                        .then(function(token) {
                            document.getElementById('recaptcha_token').value = token;
                            const formData = new FormData(formInscripcion);

                            fetch(formInscripcion.action, {
                                method: 'POST',
                                body: formData
                            })
                            .then(response => response.text())
                            .then(text => {
                                try {
                                    return JSON.parse(text);
                                } catch (err) {
                                    console.error("Respuesta del servidor no es JSON:", text);
                                    throw new Error("El servidor devolvió un error interno.");
                                }
                            })
                            .then(data => {
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;

                                if (data.status === 'success') {
                                    // Cerrar modal
                                    $('#modalInscripcion').modal('hide'); 
                                    

                                    setTimeout(() => {
                                        location.reload();
                                    }, 1000);


                                    mostrarAlerta('success', '¡Éxito!', data.message);
                                } else {
                                    mostrarAlerta('error', 'Error', data.message);
                                }
                            })
                            .catch(error => {
                                submitBtn.innerHTML = originalText;
                                submitBtn.disabled = false;
                                mostrarAlerta('error', 'Error', 'Ocurrió un error: ' + error.message);
                            });
                        })
                        .catch(function(error) {
                            submitBtn.innerHTML = originalText;
                            submitBtn.disabled = false;
                            mostrarAlerta('error', 'Seguridad', 'Error de verificación de seguridad. Intenta de nuevo.');
                        });
                });
            });
        }
        

        $(document).on('click', '.btn-transacciones', function() {
            const idInscripcion = $(this).data('id');
            $('#transaccion_id_inscripcion').val(idInscripcion);

            // --- 1. Cargar Tarjetas y Opciones ---
            $('#contenedor-tarjetas').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin text-primary"></i> Cargando tarifas...</div>');
            $('#motivo_pago').html('<option value="">Cargando...</option>');

            $.ajax({
                url: '../../../ajax/obtenerTarifasInscripcion.php',
                method: 'POST',
                data: { id_inscripcion: idInscripcion },
                dataType: 'json',
                success: function(res) {
                    $('#contenedor-tarjetas').html(res.tarjetas);
                    $('#motivo_pago').html(res.options);

                    // Calcular Bolívares
                    fetch('https://ve.dolarapi.com/v1/dolares/oficial')
                        .then(response => response.json())
                        .then(data => {
                            const tasa = data.promedio;
                            $('#contenedor-tarjetas').find('.precio-ves').each(function() {
                                const priceUSD = parseFloat($(this).siblings('[data-usd]').data('usd'));
                                if (!isNaN(priceUSD) && priceUSD > 0) {
                                    $(this).text('Bs. ' + (priceUSD * tasa).toFixed(2));
                                } else {
                                    $(this).text('');
                                }
                            });
                        }).catch(() => $('#contenedor-tarjetas').find('.precio-ves').text('Error Bs.'));
                }
            });


            $('#contenidoTransacciones').html('<div class="text-center p-3"><i class="fas fa-spinner fa-spin text-secondary"></i> Cargando historial...</div>');

            $.ajax({
                url: '../../../ajax/obtenerTransacciones.php',
                method: 'POST',
                data: { id_inscripcion: idInscripcion },
                success: function(htmlTransacciones) {
                    $('#contenidoTransacciones').html(htmlTransacciones);
                },
                error: function() {
                    $('#contenidoTransacciones').html('<div class="alert alert-danger">Error al cargar el historial.</div>');
                }
            });
        });


        function mostrarAlerta(tipo, titulo, mensaje) {
            const color = tipo === 'success' ? '#28a745' : '#dc3545';
            const icono = tipo === 'success' ? 'fa-check-circle' : 'fa-exclamation-triangle';
            
            // Creamos el HTML de la alerta
            const html = `
                <div class="modal fade" id="alertaDinamica" tabindex="-1" role="dialog" style="z-index: 9999;">
                    <div class="modal-dialog modal-dialog-centered modal-sm" role="document">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 15px;">
                            <div class="modal-body text-center p-4">
                                <i class="fas ${icono} fa-4x mb-3" style="color: ${color};"></i>
                                <h4 class="font-weight-bold mb-2">${titulo}</h4>
                                <p class="text-muted mb-4">${mensaje}</p>
                                <button type="button" class="btn btn-${tipo === 'success' ? 'success' : 'danger'} btn-block" data-dismiss="modal" style="border-radius: 8px;">
                                    Aceptar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            `;


            $('#alertaDinamica').remove();
            

            $('body').append(html);
            $('#alertaDinamica').modal('show');
            

            $('#alertaDinamica').on('hidden.bs.modal', function () {
                $(this).remove();
            });
        }
        

        $('#formNuevaTransaccion').on('submit', function(e) {
            e.preventDefault();

            const form = this;
            const submitBtn = $(form).find('button[type="submit"]');
            const originalText = submitBtn.html();
            

            const montoIngresado = parseFloat($('#formNuevaTransaccion input[name="monto"]').val());
            const motivoSeleccionado = $('#motivo_pago').find('option:selected');
            const precioMaximo = parseFloat(motivoSeleccionado.data('price'));

            if (isNaN(montoIngresado) || montoIngresado <= 0) {
                mostrarAlerta('error', 'Monto Inválido', 'Debe ingresar un monto mayor a 0.');
                return;
            }

            if (!isNaN(precioMaximo) && montoIngresado > precioMaximo) {
                mostrarAlerta('error', 'Monto Excedido', 'El monto ingresado ($' + montoIngresado.toFixed(2) + ') no puede ser mayor al costo de la tarifa ($' + precioMaximo.toFixed(2) + ').');
                return;
            }
            // ---------------------------

            submitBtn.html('<i class="fas fa-spinner fa-spin"></i> Guardando...');
            submitBtn.prop('disabled', true);

            const formData = new FormData(form);
            const url = '<?php echo SERVERURL; ?>controladores/transaccionControlador.php';

            fetch(url, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(text => {
                try {
                    return JSON.parse(text);
                } catch (err) {
                    console.error("Respuesta no JSON:", text);
                    throw new Error("El servidor devolvió un error interno.");
                }
            })
            .then(data => {
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);

                if (data.status === 'success') {
                    mostrarAlerta('success', '¡Éxito!', data.message);
                    form.reset();
                    $('#motivo_pago').val(''); // Resetear select
                } else {
                    mostrarAlerta('error', 'Error', data.message);
                }
            })
            .catch(error => {
                submitBtn.html(originalText);
                submitBtn.prop('disabled', false);
                mostrarAlerta('error', 'Error', 'Ocurrió un error: ' + error.message);
            });
        });
    });
    </script>
    
    <script>
        
        $('#motivo_pago').on('change', function() {
    		const price = $(this).find('option:selected').data('price');
    		if (price !== undefined && price > 0) {
        		// Asumiendo que el input de monto en el modal de transacción tiene el name="monto"
        		$('#formNuevaTransaccion input[name="monto"]').val(price);
   			}
		});
        
    </script>
    
    <script>
                // Ver comprobante en modal
        $(document).on('click', '.btn-ver-comprobante', function(e) {
            e.preventDefault();
            const imgUrl = $(this).data('img');
            $('#imagenComprobante').attr('src', imgUrl);
            $('#modalComprobante').modal('show');
        });
    </script>
</head>

<style>
.precio-card { border: none; border-radius: 15px; box-shadow: 0 8px 25px rgba(0,0,0,0.1); transition: all 0.3s ease; background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%); border-left: 5px solid #007bff; }
.precio-display { font-size: 2.5rem; font-weight: 800; background: linear-gradient(135deg, #28a745, #20c997); -webkit-background-clip: text; -webkit-text-fill-color: transparent; text-shadow: 2px 2px 4px rgba(0,0,0,0.1); }
.precio-icono { color: #28a745; opacity: 0.8; }
.info-adicional { background: #f8f9fa; padding: 15px; border-radius: 10px; margin-top: 15px; }
.sticky-top { position: -webkit-sticky; position: sticky; z-index: 100; }
#sinPrecio { border: 2px dashed #dee2e6; background: #fafafa; }
.card-header.bg-primary { border-radius: 13px 13px 0 0 !important; }
.card-footer { border-radius: 0 0 13px 13px !important; }
</style>

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Inscripciones</h2>
    <button class="btn btn-success" data-toggle="modal" data-target="#modalInscripcion">
      Crear Inscripción
    </button>
  </div>

  <div class="row mb-4">
    <div class="col-md-8">
      <div class="input-group">
        <input type="text" id="busqueda" class="form-control" placeholder="Buscar inscripciones..." aria-label="Buscar inscripciones">
      </div>
    </div>
    <div class="col-md-4">
      <select id="filtro" class="form-control">
        <option value="nombre">Nombre</option>
        <option value="apellido">Apellido</option>
        <option value="curso">Curso</option>
        <option value="tipo">Tipo</option>
        <option value="cohorte">Cohorte</option>
        <option value="seccion">Sección</option>
        <option value="estado">Estado</option>
        <option value="pago">Pago Faltante ≠ 0</option>
      </select>
    </div>
  </div>

  <div id="tabla-container">
    <div class="text-center p-4">
      <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
      <p class="mt-2 text-muted">Cargando inscripciones...</p>
    </div>
  </div>
</div>


<div class="modal fade" id="modalInscripcion" tabindex="-1" role="dialog" aria-labelledby="modalInscripcionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <form action="<?php echo SERVERURL; ?>controladores/inscripcionAdministracionControlador.php"
          method="POST" enctype="multipart/form-data" class="modal-content" id="formInscripcion">

      <div class="modal-header">
        <h5 class="modal-title" id="modalInscripcionLabel">Registrar Nueva Inscripción</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>

      <div class="modal-body">
      <input type="hidden" name="recaptcha_token" id="recaptcha_token" value="">
        <div class="row">
          <div class="col-md-8">
 
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="id_estudiante_fk">Estudiante</label>
                <select class="form-control" name="id_estudiante_fk" id="id_estudiante_fk" required>
                  <option value="">Seleccione un estudiante</option>
                  <?php
                    $respuesta = mainModel::supabaseGet('student', 'id,ci,name,last_name', ['state' => 'eq.1']);
                    $estudiantes = $respuesta['data'] ?? [];
                    if ($estudiantes) {
                        foreach ($estudiantes as $e) {
 
                            $ci = mainModel::decryption($e['ci']);
                            $name = mainModel::decryption($e['name']);
                            $last_name = mainModel::decryption($e['last_name']);
                            

                            $ci = $ci !== false ? $ci : $e['ci'];
                            $name = $name !== false ? $name : $e['name'];
                            $last_name = $last_name !== false ? $last_name : $e['last_name'];

                            echo "<option value='{$e['id']}'>{$ci} - {$name} {$last_name}</option>";
                        }
                    } else {
                        echo "<option value=''>No hay estudiantes disponibles</option>";
                    }
                  ?>
                </select>
              </div>

                <div class="form-group col-md-6">
                <label for="id_curso">Curso Vigente</label>
                <select class="form-control" name="period_course_id_select" id="id_curso" required>
                    <option value="">Seleccione un curso</option>
                    <?php

                    $filtrosPeriod = ['current_state' => 'in.(Vigente,Próximo)']; 
                    $resultPeriods = mainModel::supabaseGet('period', 'id,name', $filtrosPeriod);
                    $periods = $resultPeriods['data'] ?? [];
                    

                    $periodIdsActivos = array_column($periods, 'id');

                    if (!empty($periodIdsActivos)) {

                        $filtrosPC = [
                            'period_id' => 'in.(' . implode(',', $periodIdsActivos) . ')',
                            'state' => 'eq.1' 
                        ];
                        $resultPC = mainModel::supabaseGet('period_course', 'id,course_id,period_id', $filtrosPC);
                        $periodCourses = $resultPC['data'] ?? [];

                        if (!empty($periodCourses)) {
                            $courseIds = array_unique(array_column($periodCourses, 'course_id'));


                            $filtrosCosts = ['period_id' => 'in.(' . implode(',', $periodIdsActivos) . ')'];
                            $resultCosts = mainModel::supabaseQuery('period_costs', 'period_id,price', $filtrosCosts);
                            $costs = $resultCosts['data'] ?? [];
                            $costsByPeriod = [];
                            foreach ($costs as $c) {
                                if (!isset($costsByPeriod[$c['period_id']])) {
                                    $costsByPeriod[$c['period_id']] = $c['price'] ?? 0;
                                }
                            }


                            $coursesById = [];
                            if (!empty($courseIds)) {
                                $filtrosCourses = ['id' => 'in.(' . implode(',', $courseIds) . ')'];
                                $resultCourses = mainModel::supabaseGet('course', 'id,name', $filtrosCourses);
                                $courses = $resultCourses['data'] ?? [];
                                foreach ($courses as $c) {
                                    $coursesById[$c['id']] = $c['name'];
                                }
                            }


                            foreach ($periodCourses as $pc) {
                                $periodId = $pc['period_id'];
                                $courseId = $pc['course_id'];
                                $periodCourseId = $pc['id'];

                                $courseName = $coursesById[$courseId] ?? 'Curso sin nombre';
                                $price = $costsByPeriod[$periodId] ?? 0;
                                $priceFormatted = number_format($price, 2);

                                $periodName = '';
                                foreach ($periods as $p) {
                                    if ($p['id'] == $periodId) {
                                        $periodName = $p['name'] ?? '';
                                        break;
                                    }
                                }

                                $texto = $courseName . ($periodName ? " ($periodName)" : "") . " - $$priceFormatted";
                                echo "<option value='{$periodCourseId}' data-price='{$price}' data-period='{$periodId}'>{$texto}</option>";
                            }
                        } else {
                            echo '<option value="" disabled>No hay periodos-cursos activos</option>';
                        }
                    } else {
                        echo '<option value="" disabled>No hay periodos vigentes o próximos</option>';
                    }
                    ?>
                </select>
              </div>
            </div>


            <div class="form-row">
              <div class="form-group col-md-8">
                <label for="id_seccion_fk">Sección</label>
                <select class="form-control" name="id_seccion_fk" id="id_seccion_fk" required>
                  <option value="">Seleccione una sección</option>
                </select>
                <input type="hidden" name="period_course_id" id="period_course_id" value="">
              </div>
              <div class="form-group col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" id="btnCrearSeccion">Crear Sección</button>
              </div>
            </div>


            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="fecha_inscripcion">Fecha de Inscripción</label>
                <input type="date" class="form-control" name="fecha_inscripcion" id="fecha_inscripcion" required>
              </div>
              <div class="form-group col-md-6">
                <label for="fecha_abono">Fecha de Abandono</label>
                <input type="date" class="form-control" name="fecha_abono" id="fecha_abono">
              </div>
            </div>


            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="metodo_pago">Método de pago</label>
                <select class="form-control" name="metodo_pago" id="metodo_pago" required>
                  <option value="">Seleccione</option>
                  <option value="Efectivo">Efectivo</option>
                  <option value="Transferencia">Transferencia</option>
                  <option value="Pago Móvil">Pago Móvil</option>
                </select>
              </div>
            </div>


            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="monto">Monto a Pagar</label>
                <input type="number" step="0.01" min="0" class="form-control" name="monto" id="monto" required placeholder="Ingrese el monto">
                <small class="form-text text-muted">Consulte el precio de referencia a la derecha →</small>
              </div>
              <div class="form-group col-md-6">
                <label for="fecha_pago">Fecha de pago</label>
                <input type="date" class="form-control" name="fecha_pago" id="fecha_pago" required>
              </div>
            </div>


            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="comprobante">Comprobante de Pago <span class="text-danger">*</span></label>
                <input type="file" class="form-control-file" name="comprobante" id="comprobante" accept=".jpg, .jpeg, .png, .gif, .webp, image/jpeg, image/png, image/gif, image/webp" required>
                <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP</small>
              </div>
            </div>
          </div>


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
                  <h2 class="card-title text-primary precio-display mb-1" id="precioUSD">$0.00</h2>
                  <p class="text-success font-weight-bold mb-3" id="precioVES">Bs. 0.00</p>
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
                      <i class="fas fa-info-circle"></i> Tasa BCV aplicada al momento
                    </small>
                  </div>
                </div>
                <div class="card-footer bg-light text-center">
                  <small class="text-muted">Actualizado al seleccionar el curso</small>
                </div>
              </div>

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
      </div> 

      <div class="modal-footer">
        <button type="submit" class="btn btn-success">
          <i class="fas fa-save"></i> Registrar Inscripción
        </button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">
          <i class="fas fa-times"></i> Cancelar
        </button>
      </div>
    </form>
  </div>
</div>


<div class="modal fade" id="modalTransacciones" tabindex="-1" role="dialog" aria-labelledby="modalTransaccionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document"> <!-- Cambiado a modal-xl para que quepa la tabla -->
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Transacciones de la Inscripción</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        

        <div id="contenedor-tarjetas">

        </div>


        <h5 class="mt-4 mb-3 text-primary"><i class="fas fa-history"></i> Historial de Pagos</h5>
        <div id="contenidoTransacciones" class="mb-4">
            <p class="text-center text-muted">Cargando transacciones...</p>
        </div>


        <h5 class="mt-4 border-top pt-3">Registrar Nueva Transacción</h5>
        <form id="formNuevaTransaccion" enctype="multipart/form-data">
          <input type="hidden" name="id_inscripcion" id="transaccion_id_inscripcion">
          
          <div class="form-group col-md-6 pl-0">
              <label for="motivo_pago">Motivo de Pago</label>
              <select class="form-control" name="motivo_pago" id="motivo_pago" required>
                  <option value="">Cargando opciones...</option>
              </select>
          </div>
          
          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="monto">Monto</label>
              <input type="number" class="form-control" name="monto" required step="0.01">
            </div>
            <div class="form-group col-md-6">
              <label for="fecha_pago">Fecha de Pago</label>
              <input type="date" class="form-control" name="fecha_pago" required>
            </div>
            <div class="form-group col-md-6">
              <label for="metodo_pago">Método de Pago</label>
              <select class="form-control" name="metodo_pago" required>
                <option value="">Seleccione</option>
                <option value="Transferencia">Transferencia</option>
                <option value="Pago móvil">Pago móvil</option>
                <option value="Efectivo">Efectivo</option>
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="comprobante">Comprobante</label>
              <input type="file" class="form-control-file" name="comprobante" accept="image/*" required>
            </div>
          </div>
          <button type="submit" class="btn btn-primary">Guardar Transacción</button>
        </form>
      </div>
    </div>
  </div>
</div>


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



<div class="modal fade" id="modalEditarTransaccion" tabindex="-1" role="dialog" aria-labelledby="modalEditarLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="formEditarTransaccion" method="POST" enctype="multipart/form-data" class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Editar Transacción</h5>
        <button type="button" class="close" data-dismiss="modal"><span>&times;</span></button>
      </div>
      <div class="modal-body">
        <input type="hidden" name="id_transaccion" id="edit_id_transaccion">
        <div class="form-group">
          <label>Monto</label>
          <input type="number" class="form-control" name="monto" id="edit_monto" required>
        </div>
        <div class="form-group">
          <label>Fecha de Pago</label>
          <input type="date" class="form-control" name="fecha_pago" id="edit_fecha_pago" required>
        </div>
        <div class="form-group">
          <label>Método de Pago</label>
          <select class="form-control" name="metodo_pago" id="edit_metodo_pago" required>
            <option value="Transferencia">Transferencia</option>
            <option value="Pago móvil">Pago móvil</option>
            <option value="Efectivo">Efectivo</option>
          </select>
        </div>
        <div class="form-group">
          <label>Nuevo Comprobante (opcional)</label>
          <input type="file" class="form-control-file" name="comprobante" accept="image/*">
        </div>
      </div>
      <div class="modal-footer">
        <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
      </div>
    </form>
  </div>
</div>
