<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);


?>

<head>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://www.google.com/recaptcha/api.js?render=6LfbzLwtAAAAAIprBfjlKu0kaztX4515xgt6Wdoq" async defer></script>
    <!--########################################################
 	Por si todo falla, recargar restaurar las funciones aqui 
	la dejaste guardadas en el archivo backups en tu computadora
	############################################################-->  
    
    <!--#################################################################
 		Codigo de javascript para mostrar los registros de las inscripciones activas en el sistema
		INICIO 
		##########################################################-->
    <script>
$(document).ready(function() {
    // Función para cargar inscripciones vía AJAX
    function cargarInscripciones(pagina = 1) {
        const busqueda = $('#busqueda').val().trim();
        const filtro = $('#filtro').val();

        $.ajax({
            url: '../../../ajax/buscarInscripciones.php',
            method: 'POST',
            data: {
                busqueda: busqueda,
                filtro: filtro,
                pagina: pagina
            },
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
                console.log('Datos cargados');
            },
            error: function(xhr, status, error) {
                console.error('Error al cargar:', error);
                $('#tabla-container').html(
                    '<div class="alert alert-danger text-center">' +
                    '<i class="fas fa-exclamation-triangle"></i> ' +
                    'Error al cargar las inscripciones: ' + error +
                    '</div>'
                );
            }
        });
    }

    // Evento de búsqueda en tiempo real
    $('#busqueda, #filtro').on('input change', function() {
        cargarInscripciones(1);
    });

    // Paginación dinámica
    $(document).on('click', '.pagina-link', function(e) {
        e.preventDefault();
        const pagina = $(this).data('pagina');
        cargarInscripciones(pagina);
        $('html, body').animate({
            scrollTop: $('#tabla-container').offset().top - 50
        }, 300);
    });

    // Cargar al inicio
    cargarInscripciones();
});
</script>
    <!--########################################################
 		FIN DE LA FUNCIONA PARA MOSTRAR los registros de las inscripciones activas en el sistema -->
    
  	<!--#########################################################
		INICIO PARA LA FUNCION DE TRAER EL CURSO PARA REGISTRAR LA INSCRIPCION
		#########################################################-->

    <script>
    $('#id_curso').on('change', function () {
    	const idCurso = $(this).val();
    	// ...
    	$.ajax({
        	url: '../../../ajax/obtenerSeccionesPorCurso.php',
        	method: 'POST',
        	data: { id_curso: idCurso },
        	success: function (data) {
            	$('#id_seccion_fk').html(data);
        	},
        	error: function () {
            	$('#id_seccion_fk').html('<option value="">Error al cargar</option>');
       		}
    	});
	});
    </script>
    
    <!--#########################################################
		FIN PARA LA FUNCION DE TRAER EL CURSO PARA REGISTRAR LA INSCRIPCION
		#########################################################-->
    
<!--#####################################################################
    INICIO DE LA FUNCION PARA OBTENER LAS SECCIONES RELACIONADAS AL CURSO OBTENIDO EN EL SELECT DE CURSO
    #####################################################################################################-->

<script>
$(document).ready(function() {
    $('#id_curso').on('change', function() {
        const periodCourseId = $(this).val(); // AHORA ES period_course_id
        const $selectSeccion = $('#id_seccion_fk');
        const $inputPeriodCourse = $('#period_course_id');

        $inputPeriodCourse.val('');
        $selectSeccion.html('<option value="">Cargando secciones...</option>');

        if (!periodCourseId) {
            $selectSeccion.html('<option value="">Seleccione una sección</option>');
            return;
        }

        $.ajax({
            url: '../../../ajax/obtenerSeccionesPorCurso.php',
            method: 'POST',
            data: { period_course_id: periodCourseId }, // ENVIAMOS period_course_id
            success: function(data) {
                $selectSeccion.html(data);
                // El period_course_id ya está en el select de cursos, así que lo guardamos
                $('#period_course_id').val(periodCourseId);
            },
            error: function(xhr, status, error) {
                console.error('Error:', error);
                $selectSeccion.html('<option value="">Error al cargar secciones</option>');
            }
        });
    });

    // Al seleccionar una sección, aseguramos que period_course_id esté guardado
    $('#id_seccion_fk').on('change', function() {
        // Ya lo tenemos del select de curso, pero lo confirmamos
        const periodCourseId = $('#id_curso').val();
        if (periodCourseId) {
            $('#period_course_id').val(periodCourseId);
        }
    });
});
</script>

<!--##################################################################################
    FIN DE LA FUNCION PARA OBTENER LAS SECCIONES RELACIONADAS AL CURSO OBTENIDO EN EL SELECT DE CURSO
    ##################################################################################-->
    
    <script>
		document.addEventListener('DOMContentLoaded', function() {
    	const formInscripcion = document.getElementById('formInscripcion');
    	if (!formInscripcion) return;

    	formInscripcion.addEventListener('submit', function(e) {
        	e.preventDefault(); // Detenemos el envío normal temporalmente

        	// Ejecutar reCAPTCHA v3
        	grecaptcha.ready(function() {
            	grecaptcha.execute('6LfbzLwtAAAAAIprBfjlKu0kaztX4515xgt6Wdoq', {action: 'submit_inscripcion'})
                	.then(function(token) {
                    	// Colocar el token en el campo oculto
                    	document.getElementById('recaptcha_token').value = token;

                    	// Enviar el formulario manualmente (ahora sí)
                    	formInscripcion.submit();
               		})
                	.catch(function(error) {
                    	console.error('Error en reCAPTCHA:', error);
                    	// Si falla reCAPTCHA, aún enviamos el formulario
                    	// (el backend rechazará si no hay token)
                    	formInscripcion.submit();
                	});
        	});
    	});
	});
	</script>
    
<head/>

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

<div class="container mt-5">
  <div class="d-flex justify-content-between align-items-center mb-4">
    <h2>Gestión de Inscripciones</h2>
    <button class="btn btn-success" data-toggle="modal" data-target="#modalInscripcion">
      Crear Inscripción
    </button>
  </div>

  <!-- 🔍 Filtros de búsqueda -->
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

  <!-- 🔥 SOLO ESTE CONTENEDOR - ELIMINA LOS OTROS -->
  <div id="tabla-container">
    <!-- Aquí se cargará la tabla completa + paginación via AJAX -->
    <div class="text-center p-4">
      <i class="fas fa-spinner fa-spin fa-2x text-primary"></i>
      <p class="mt-2 text-muted">Cargando inscripciones...</p>
    </div>
  </div>
</div>

<div class="modal fade" id="modalInscripcion" tabindex="-1" role="dialog" aria-labelledby="modalInscripcionLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document"> <!-- Cambiado a modal-xl para más espacio -->
    <form action="<?php echo SERVERURL; ?>/../../../controladores/inscripcionAdministracionControlador.php"
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
          <!-- Columna del formulario -->
          <div class="col-md-8">
            <!-- Fila 1: Estudiante / Curso -->
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
        						echo "<option value='{$e['id']}'>{$e['ci']} - {$e['name']} {$e['last_name']}</option>";
    						}
						} else {
    						echo "<option value=''>No hay estudiantes disponibles</option>";
						}
                  ?>
                </select>
              </div>

              <div class="form-group col-md-6">
    <label for="id_curso">Curso Vigente</label>
    <select class="form-control" name="id_curso" id="id_curso" required>
        <option value="">Seleccione un curso</option>
        <?php
        // 1. Obtener todos los period_course activos
        $resultPC = mainModel::supabaseGet('period_course', 'id,course_id,period_id', ['state' => 'eq.1']);
        $periodCourses = $resultPC['data'] ?? [];

        if (!empty($periodCourses)) {
            // Extraer IDs de period y course
            $periodIds = array_unique(array_column($periodCourses, 'period_id'));
            $courseIds = array_unique(array_column($periodCourses, 'course_id'));

            // 2. Obtener los períodos activos (por fechas)
            // Asumiendo que tienes tabla 'period' con start_date y end_date
            $filtrosPeriod = ['state' => 'eq.1']; // o filtrar por fechas
            $resultPeriods = mainModel::supabaseGet('period', 'id,name,start_date,end_date', $filtrosPeriod);
            $periods = $resultPeriods['data'] ?? [];
            $periodsActivos = array_filter($periods, function($p) {
                $hoy = date('Y-m-d');
                return $p['start_date'] <= $hoy && $p['end_date'] >= $hoy;
            });
            $periodIdsActivos = array_column($periodsActivos, 'id');

            // 3. Obtener los costos (period_costs) para esos períodos
            if (!empty($periodIdsActivos)) {
                $filtrosCosts = ['period_id' => 'in.(' . implode(',', $periodIdsActivos) . ')'];
                $resultCosts = mainModel::supabaseQuery('period_costs', 'period_id,price,cost_description', $filtrosCosts);
                $costs = $resultCosts['data'] ?? [];
                // Agrupar costos por period_id (tomamos el primer precio)
                $costsByPeriod = [];
                foreach ($costs as $c) {
                    if (!isset($costsByPeriod[$c['period_id']])) {
                        $costsByPeriod[$c['period_id']] = $c['price'] ?? 0;
                    }
                }
            } else {
                $costsByPeriod = [];
            }

            // 4. Obtener los cursos
            if (!empty($courseIds)) {
                $filtrosCourses = ['id' => 'in.(' . implode(',', $courseIds) . ')'];
                $resultCourses = mainModel::supabaseGet('course', 'id,name', $filtrosCourses);
                $courses = $resultCourses['data'] ?? [];
                $coursesById = [];
                foreach ($courses as $c) {
                    $coursesById[$c['id']] = $c['name'];
                }
            } else {
                $coursesById = [];
            }

            // 5. Construir las opciones del select
            foreach ($periodCourses as $pc) {
                $periodId = $pc['period_id'];
                $courseId = $pc['course_id'];
                $periodCourseId = $pc['id'];

                // Solo si el período está activo
                if (!in_array($periodId, $periodIdsActivos)) continue;

                $courseName = $coursesById[$courseId] ?? 'Curso sin nombre';
                $price = $costsByPeriod[$periodId] ?? 0;
                $priceFormatted = number_format($price, 2);

                // Obtener nombre del período (opcional)
                $periodName = '';
                foreach ($periodsActivos as $p) {
                    if ($p['id'] == $periodId) {
                        $periodName = $p['name'] ?? '';
                        break;
                    }
                }

                $texto = $courseName . ($periodName ? " ($periodName)" : "") . " - $$priceFormatted";
                echo "<option value='{$periodCourseId}' data-price='{$price}' data-period='{$periodId}'>{$texto}</option>";
            }
        } else {
            echo '<option value="" disabled>No hay cursos vigentes disponibles</option>';
        }
        ?>
    </select>
</div>
            </div>

            <!-- Fila 2: Sección / Crear Sección -->
            <div class="form-row">
              <div class="form-group col-md-8">
                <label for="id_seccion_fk">Sección</label>
                <select class="form-control" name="id_seccion_fk" id="id_seccion_fk" required>
                  <option value="">Seleccione una sección</option>
                </select>
                  <input type="hidden" name="period_course_id" id="period_course_id" value="">
              </div>
              <div class="form-group col-md-4 d-flex align-items-end">
                <button type="button" class="btn btn-outline-secondary w-100" id="btnCrearSeccion">
                  Crear Sección
                </button>
              </div>
            </div>

            <!-- Fila 3: Fechas -->
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

            <!-- Fila 4: Estado / Método de pago -->
            <div class="form-row">
              <div class="form-group col-md-6">
                <label for="estado_inscripcion">Estado</label>
                <select class="form-control" name="estado_inscripcion" id="estado_inscripcion" required>
                  <option value="Activa">Activa</option>
                  <option value="Inactiva">Inactiva</option>
                  <option value="Abandonada">Abandonada</option>
                </select>
              </div>

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

            <!-- Fila 5: Monto / Fecha de pago -->
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

            <!-- Fila 6: Comprobante -->
            <div class="form-row">
              <div class="form-group col-md-12">
                <label for="comprobante">Comprobante de Pago <span class="text-danger">*</span></label>
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


<!-- Modal de Transacciones -->
<div class="modal fade" id="modalTransacciones" tabindex="-1" role="dialog" aria-labelledby="modalTransaccionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">Transacciones de la Inscripción</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Tarjetas de Precios en la parte superior -->
        <!-- Tarjetas de Precios Ultra Compactas -->
<div class="row mb-2" id="tarjetasPrecios">
  <!-- Tarjeta de Módulos -->
  <div class="col-md-4 mb-1">
    <div class="card precio-card-compact h-100">
      <div class="card-body text-center p-2">
        <div class="precio-icono-compact mb-1">
          <i class="fas fa-cubes"></i>
        </div>
        <h6 class="card-title text-muted mb-1">MÓDULOS</h6>
        <div class="precio-display-compact mb-1" id="precioModulos">$150.00</div>
        <p class="text-muted small mb-0">Por módulo</p>
      </div>
    </div>
  </div>
  
  <!-- Tarjeta de Aranceles -->
  <div class="col-md-4 mb-1">
    <div class="card precio-card-compact h-100">
      <div class="card-body text-center p-2">
        <div class="precio-icono-compact mb-1">
          <i class="fas fa-file-invoice-dollar"></i>
        </div>
        <h6 class="card-title text-muted mb-1">ARANCELES</h6>
        <div class="precio-display-compact mb-1" id="precioAranceles">$75.50</div>
        <p class="text-muted small mb-0">Aranceles</p>
      </div>
    </div>
  </div>
  
  <!-- Tarjeta de Artículo 13 -->
  <div class="col-md-4 mb-1">
    <div class="card precio-card-compact h-100">
      <div class="card-body text-center p-2">
        <div class="precio-icono-compact mb-1">
          <i class="fas fa-gavel"></i>
        </div>
        <h6 class="card-title text-muted mb-1">ART. 13</h6>
        <div class="precio-display-compact mb-1" id="precioArt13">$200.00</div>
        <p class="text-muted small mb-0">Artículo 13</p>
      </div>
    </div>
  </div>
</div>
      <div class="modal-body">
  <div id="contenidoTransacciones">
    <p class="text-center">Cargando transacciones...</p>
  </div>

  <h5 class="mt-4">Registrar Nueva Transacción</h5>
  <form id="formNuevaTransaccion" enctype="multipart/form-data">
    <input type="hidden" name="id_inscripcion" id="transaccion_id_inscripcion">

    <!-- En el modal de transacciones, modifica el select de motivos de pago -->
<div class="form-group col-md-6">
    <label for="motivo_pago">Motivo de Pago</label>
    <select class="form-control" name="motivo_pago" id="motivo_pago" required>
        <option value="">Seleccione</option>
        <option value="Inscripción">Inscripción</option>
        
        <!-- Motivos para cursos no intensivos -->
        <option value="Módulo 1" class="motivo-no-intensivo">Módulo 1</option>
        <option value="Módulo 2" class="motivo-no-intensivo">Módulo 2</option>
        <option value="Módulo 3" class="motivo-no-intensivo">Módulo 3</option>
        <option value="Aranceles" class="motivo-no-intensivo">Aranceles</option>
        <option value="art13" class="motivo-no-intensivo">Artículo 13</option>
        
        <!-- Motivos para cursos intensivos -->
        <option value="Encuentro 1" class="motivo-intensivo">Encuentro 1</option>
        <option value="Encuentro 2" class="motivo-intensivo">Encuentro 2</option>
        <option value="Encuentro 3" class="motivo-intensivo">Encuentro 3</option>
        <option value="Encuentro 4" class="motivo-intensivo">Encuentro 4</option>
        <option value="Encuentro 5" class="motivo-intensivo">Encuentro 5</option>
        <option value="Encuentro 6" class="motivo-intensivo">Encuentro 6</option>
        <option value="Titulación" class="motivo-intensivo">Titulación</option>
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

