<?php
require_once __DIR__ . "/../../../controladores/ListInscripcionesAdministracionControlador.php";
$controlador = new ListInscripcionesControlador();
$inscripciones = $controlador->listar_inscripciones_completas();
?>

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
                    $estudiantes = mainModel::conectar()->query("SELECT id_estudiante, ci_estudiante, nombre, apellido FROM estudiante");
                    foreach ($estudiantes as $e) {
                      echo "<option value='{$e['id_estudiante']}'>{$e['ci_estudiante']} - {$e['nombre']} {$e['apellido']}</option>";
                    }
                  ?>
                </select>
              </div>

              <div class="form-group col-md-6">
    <label for="id_curso">Curso</label>
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

            <!-- Fila 2: Sección / Crear Sección -->
            <div class="form-row">
              <div class="form-group col-md-8">
                <label for="id_seccion_fk">Sección</label>
                <select class="form-control" name="id_seccion_fk" id="id_seccion_fk" required>
                  <option value="">Seleccione una sección</option>
                </select>
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

<!-- Scripts necesarios para Bootstrap -->
<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script>
$(document).ready(function () {
  // 🔍 FUNCIÓN PRINCIPAL para cargar inscripciones
  function cargarInscripciones(pagina = 1) {
    const busqueda = $('#busqueda').val().trim();
    const filtro = $('#filtro').val();

    console.log('Cargando página:', pagina, 'Búsqueda:', busqueda, 'Filtro:', filtro);

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
      success: function (data) {
        $('#tabla-container').html(data);
        console.log('Datos cargados exitosamente');
      },
      error: function (xhr, status, error) {
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

  // 🔁 Eventos de búsqueda
  $('#busqueda, #filtro').on('input change', function () {
    console.log('Búsqueda/filtro cambiado, recargando...');
    cargarInscripciones(1);
  });

  // ⬅️ Evento para paginación dinámica
  $(document).on('click', '.pagina-link', function (e) {
    e.preventDefault();
    const pagina = $(this).data('pagina');
    console.log('Cambiando a página:', pagina);
    cargarInscripciones(pagina);
    
    // Scroll suave hacia arriba
    $('html, body').animate({
      scrollTop: $('#tabla-container').offset().top - 50
    }, 300);
  });

  // 📥 Transacciones
  $(document).on('click', '.btn-transacciones', function () {
    const id = $(this).data('id');
    console.log('Abriendo transacciones para inscripción:', id);
    
    $('#transaccion_id_inscripcion').val(id);
    $('#contenidoTransacciones').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando transacciones...</p>');

    // Cargar precios reales
    cargarPreciosReales(id);
    
    // Cargar transacciones existentes
    $.post('../../../ajax/obtenerTransacciones.php', { id_inscripcion: id }, function (data) {
      $('#contenidoTransacciones').html(data);
    }).fail(function() {
      $('#contenidoTransacciones').html('<p class="text-danger text-center">Error al cargar transacciones.</p>');
    });
  });

  // ✏️ Modificar inscripción
  $(document).on('click', '.btn-modificar', function () {
    const id = $(this).data('id');
    console.log("Modificando inscripción ID:", id);

    $('#modalModificar').modal('show');
    $('#contenidoModificar').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando datos...</p>');

    $.ajax({
      url: '../../../ajax/obtenerInscripcionParaEditar.php',
      method: 'POST',
      data: { id_inscripcion: id },
      success: function (data) {
        $('#contenidoModificar').html(data);
      },
      error: function (xhr) {
        console.error("Error AJAX:", xhr.responseText);
        $('#contenidoModificar').html('<p class="text-danger text-center">Error al cargar la inscripción.</p>');
      }
    });
  });

  // Manejar el envío del formulario de modificación
  $('#formModificarInscripcion').on('submit', function (e) {
    e.preventDefault();
    const datos = $(this).serialize();

    $.ajax({
      url: '../../../controladores/modificarInscripcionControlador.php',
      method: 'POST',
      data: datos,
      dataType: 'json',
      success: function (resp) {
        if (resp.status === 'success') {
          Swal.fire({
            icon: 'success',
            title: '¡Modificado!',
            text: resp.mensaje,
            confirmButtonText: 'Aceptar'
          }).then(() => {
            $('#modalModificar').modal('hide');
            cargarInscripciones(); // 🔥 RECARGAR LA TABLA
          });
        } else {
          Swal.fire({
            icon: 'error',
            title: 'Error',
            text: resp.mensaje || 'No se pudo modificar la inscripción.'
          });
        }
      },
      error: function () {
        Swal.fire({
          icon: 'error',
          title: 'Error de conexión',
          text: 'No se pudo contactar al servidor.'
        });
      }
    });
  });

  // 🗃️ Archivar inscripción
  $(document).on('click', '.btn-archivar-inscripcion', function () {
    const id = $(this).data('id');
    console.log('Archivando inscripción ID:', id);

    Swal.fire({
      title: '¿Quieres archivar esta inscripción?',
      text: 'Todas las transacciones ligadas a esta inscripción serán archivadas también.',
      icon: 'warning',
      showCancelButton: true,
      confirmButtonText: 'Sí, archivar',
      cancelButtonText: 'Cancelar',
      confirmButtonColor: '#d33'
    }).then((result) => {
      if (result.isConfirmed) {
        $.ajax({
          url: '../../../controladores/archivarInscripcionControlador.php',
          method: 'POST',
          data: { id_inscripcion: id },
          success: function (resp) {
            try {
              const data = JSON.parse(resp);
              if (data.status === 'success') {
                Swal.fire({
                  icon: 'success',
                  title: 'Archivado',
                  text: data.mensaje,
                  confirmButtonText: 'Aceptar'
                }).then(() => {
                  cargarInscripciones(); // 🔥 RECARGAR LA TABLA
                });
              } else {
                Swal.fire('Error', data.mensaje, 'error');
              } 
            } catch (e) {
              Swal.fire('Error', 'Respuesta inesperada del servidor.', 'error');
            }
          },
          error: function (xhr) {
            Swal.fire('Error', xhr.responseText, 'error');
          }
        });
      }
    });
  });

  // 🚀 Cargar lista al inicio
  console.log('Inicializando carga de inscripciones...');
  cargarInscripciones();
});
</script>

<script>
  $(document).ready(function () {
    $('.btn-modificar').click(function () {
      const id = $(this).data('id');
      $('#contenidoModificar').html('<p class="text-center">Cargando datos...</p>');

      $.ajax({
        url: '../../../ajax/obtenerInscripcionParaEditar.php',
        method: 'POST',
        data: { id_inscripcion: id },
        success: function (data) {
          $('#contenidoModificar').html(data);
        },
        error: function () {
          $('#contenidoModificar').html('<p class="text-danger text-center">Error al cargar la inscripción.</p>');
        }
      });
    });
  });
</script>

<script>
$(document).ready(function () {
  // 🔍 Carga inicial y dinámica de inscripciones
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
      success: function (data) {
        $('#tabla-original').hide();
        $('#tabla-inscripciones').html(data).show();
      },
      error: function () {
        $('#tabla-inscripciones').html('<p class="text-danger">Error al cargar inscripciones.</p>').show();
      }
    });
  }

  // 🔁 Evento para búsqueda en tiempo real
  $('#busqueda, #filtro').on('input change', function () {
    cargarInscripciones();
  });

  // ⬅️ Evento para paginación dinámica
  $(document).on('click', '.pagina-link', function (e) {
    e.preventDefault();
    const pagina = $(this).data('pagina');
    cargarInscripciones(pagina);
  });

$(document).on('click', '.btn-transacciones', function () {
    const id = $(this).data('id');
    console.log('Abriendo transacciones para inscripción:', id);
    
    $('#transaccion_id_inscripcion').val(id);
    $('#contenidoTransacciones').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando transacciones...</p>');

    // Cargar información del curso
    cargarInformacionCursoTransacciones(id);
    
    // Cargar transacciones existentes
    $.post('../../../ajax/obtenerTransacciones.php', { id_inscripcion: id }, function (data) {
        $('#contenidoTransacciones').html(data);
    }).fail(function() {
        $('#contenidoTransacciones').html('<p class="text-danger text-center">Error al cargar transacciones.</p>');
    });
});

  // ✏️ Modificar inscripción
$(document).on('click', '.btn-modificar', function () {
  const id = $(this).data('id');
  console.log("ID enviado:", id);

  $('#modalModificar').modal('show');
  $('#contenidoModificar').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando datos...</p>');

  $.ajax({
    url: '../../../ajax/obtenerInscripcionParaEditar.php',
    method: 'POST',
    data: { id_inscripcion: id },
    success: function (data) {
      $('#contenidoModificar').html(data);

      // Esperamos a que el contenido se cargue para activar el submit
      $('#formModificarInscripcion').on('submit', function (e) {
        e.preventDefault();

        const datos = $(this).serialize();

        $.ajax({
          url: '../../../controladores/modificarInscripcionControlador.php',
          method: 'POST',
          data: datos,
          dataType: 'json',
          success: function (resp) {
            if (resp.status === 'success') {
              Swal.fire({
                icon: 'success',
                title: '¡Modificado!',
                text: resp.mensaje,
                confirmButtonText: 'Aceptar'
              }).then(() => {
                $('#modalModificar').modal('hide');
                $('.modal-backdrop').remove();
                $('body').removeClass('modal-open');
                // Opcional: recargar tabla o contenido
              });
            } else {
              Swal.fire({
                icon: 'error',
                title: 'Error',
                text: resp.mensaje || 'No se pudo modificar la inscripción.'
              });
            }
          },
          error: function () {
            Swal.fire({
              icon: 'error',
              title: 'Error de conexión',
              text: 'No se pudo contactar al servidor.'
            });
          }
        });
      });
    },
    error: function (xhr) {
      console.error("Error AJAX:", xhr.responseText);
      $('#contenidoModificar').html('<p class="text-danger text-center">Error al cargar la inscripción.</p>');
    }
  });
});
    
    
// Función para cargar datos via AJAX
function cargarDatosInscripciones(pagina = 1) {
    const busqueda = $('#busqueda').val();
    const filtro = $('#filtro').val();
    
    $.ajax({
        url: '<?php echo SERVERURL; ?>../../../ajax/buscarinscripciones.php', // Ajusta la ruta
        type: 'POST',
        data: {
            busqueda: busqueda,
            filtro: filtro,
            pagina: pagina
        },
        beforeSend: function() {
            $('#tabla-original tbody').html('<tr><td colspan="14" class="text-center">Cargando...</td></tr>');
        },
        success: function(response) {
            $('#tabla-original tbody').html(response);
            
            // Re-inicializar tooltips y eventos después de cargar nuevos datos
            $('[data-toggle="tooltip"]').tooltip();
            inicializarEventosBotones();
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar datos:', error);
            $('#tabla-original tbody').html('<tr><td colspan="14" class="text-center text-danger">Error al cargar los datos</td></tr>');
        }
    });
}

// Función para inicializar eventos de botones
function inicializarEventosBotones() {
    // Evento para botones de paginación
    $(document).off('click', '.pagina-link').on('click', '.pagina-link', function(e) {
        e.preventDefault();
        const pagina = $(this).data('pagina');
        cargarDatosInscripciones(pagina);
    });
    
    // Evento para botones de transacciones
    $(document).off('click', '.btn-transacciones').on('click', '.btn-transacciones', function() {
        const idInscripcion = $(this).data('id');
        $('#transaccion_id_inscripcion').val(idInscripcion);
        cargarPreciosReales(idInscripcion);
        cargarTransaccionesExistentes(idInscripcion);
    });
    
    // Evento para botones de modificar
    $(document).off('click', '.btn-modificar').on('click', '.btn-modificar', function() {
        const idInscripcion = $(this).data('id');
        cargarDatosInscripcion(idInscripcion);
    });
    
    // Evento para botones de archivar
    $(document).off('click', '.btn-archivar-inscripcion').on('click', '.btn-archivar-inscripcion', function() {
        const idInscripcion = $(this).data('id');
        archivarInscripcion(idInscripcion);
    });
}

// Eventos para búsqueda
$('#busqueda').on('input', function() {
    cargarDatosInscripciones(1);
});

$('#filtro').on('change', function() {
    cargarDatosInscripciones(1);
});

// Cargar datos iniciales cuando la página esté lista
$(document).ready(function() {
    cargarDatosInscripciones(1);
});

$(document).on('click', '.btn-archivar-inscripcion', function () {
  const id = $(this).data('id');

  Swal.fire({
    title: '¿Quieres archivar esta inscripción?',
    text: 'Todas las transacciones ligadas a esta inscripción serán archivadas también.',
    icon: 'warning',
    showCancelButton: true,
    confirmButtonText: 'Sí, archivar',
    cancelButtonText: 'Cancelar'
  }).then((result) => {
    if (result.isConfirmed) {
      $.ajax({
        url: '../../../controladores/archivarInscripcionControlador.php',
        method: 'POST',
        data: { id_inscripcion: id },
        success: function (resp) {
          try {
            const data = JSON.parse(resp);
            if (data.status === 'success') {
              Swal.fire('Archivado', data.mensaje, 'success').then(() => {
              // Cerrar modal, refrescar vista, etc.
                $.post('../../../ajax/obtenerInscripciones.php', {}, function (html) {
                  $('#contenidoInscripciones').html(html);
                });
              });
            } else {
            Swal.fire('Error', data.mensaje, 'error');
            } 
          } catch (e) {
            Swal.fire('Error', 'Respuesta inesperada del servidor.', 'error');
          }
        },
        error: function (xhr) {
          Swal.fire('Error', xhr.responseText, 'error');
        }
      });
    }
  });
});


  // 📤 Enviar nueva transacción
$('#formNuevaTransaccion').on('submit', function (e) {
    e.preventDefault();
    const formData = new FormData(this);

    // Mostrar loading
    Swal.fire({
        title: 'Registrando transacción...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    $.ajax({
        url: '../../../ajax/registrarTransaccion.php', // Tu archivo existente
        method: 'POST',
        data: formData,
        contentType: false,
        processData: false,
        success: function (response) {
            Swal.close();
            
            try {
                const data = typeof response === 'string' ? JSON.parse(response) : response;
                
                if (data.status === 'success') {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Transacción registrada!',
                        text: data.mensaje,
                        confirmButtonText: 'Aceptar'
                    }).then(() => {
                        const id = $('#transaccion_id_inscripcion').val();
                        $('#formNuevaTransaccion')[0].reset();
                        
                        // Recargar información
                        cargarInformacionCursoTransacciones(id);
                        
                        // Recargar lista de transacciones
                        $.post('../../../ajax/obtenerTransacciones.php', { id_inscripcion: id }, function (data) {
                            $('#contenidoTransacciones').html(data);
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje
                    });
                }
            } catch (e) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error de respuesta',
                    text: 'Respuesta del servidor no válida'
                });
            }
        },
        error: function (xhr, status, error) {
            Swal.close();
            Swal.fire({
                icon: 'error',
                title: 'Error del servidor',
                text: 'Error al conectar con el servidor'
            });
        }
    });
});

$(document).on('click', '.btn-editar-transaccion', function () {
  const id = $(this).data('id');
  console.log("ID transacción:", id);

  $('#modalEditarTransaccion').modal('show');

  $.post('../../../ajax/obtenerTransaccionPorId.php', { id_transaccion: id }, function (data) {
    try {
      const trans = JSON.parse(data);
      $('#edit_id_transaccion').val(trans.id_transaccion);
      $('#edit_monto').val(trans.monto);
      $('#edit_fecha_pago').val(trans.fecha_pago);
      $('#edit_metodo_pago').val(trans.metodo_pago);
    } catch (e) {
      console.error("Error al parsear JSON:", e, data);
      Swal.fire({
        icon: 'error',
        title: 'Error',
        text: 'No se pudo cargar la transacción.'
      });
    }
  });
});

// ✅ Enviar edición de transacción
$(document).on('submit', '#formEditarTransaccion', function (e) {
  e.preventDefault();
  const formData = new FormData(this);

  $.ajax({
    url: '../../../ajax/editarTransaccion.php',
    method: 'POST',
    data: formData,
    contentType: false,
    processData: false,
    success: function (resp) {
      Swal.fire({
        icon: 'success',
        title: '¡Actualizado!',
        text: 'La transacción fue modificada correctamente.'
      }).then(() => {
        $('#modalEditarTransaccion').modal('hide');
        $('.modal-backdrop').remove();
        $('body').removeClass('modal-open');

        const id = $('#transaccion_id_inscripcion').val();
        $.post('../../../ajax/obtenerTransacciones.php', { id_inscripcion: id }, function (data) {
          $('#contenidoTransacciones').html(data);
        });
      });
    },
    error: function (xhr) {
      Swal.fire({
        icon: 'error',
        title: 'Depuración',
        html: `<pre>${xhr.responseText}</pre>`
      });
    }
  });
});
  

  $('#id_curso').on('change', function () {
    const idCurso = $(this).val();
    console.log("id Enviada", idCurso);

    if (idCurso === '') {
      $('#id_seccion_fk').html('<option value="">Seleccione una sección</option>');
      return;
    }

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


  // 🚀 Cargar lista al inicio
  cargarInscripciones();
});
</script>

<script>
  $('#id_curso').on('change', function () {
    const idCurso = $(this).val();
    console.log("id Enviada2", idCurso);
    $('#id_seccion_fk').html('<option value="">Cargando...</option>');
    $.ajax({
      url: 'ajax/obtenerSeccionesPorCurso.php',
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

  <script>
$('#btnCrearSeccion').on('click', function() {
    console.log('Botón presionado');
    const idCurso = $('#id_curso').val();

    if (!idCurso) {
        alert('Selecciona un curso primero.');
        return;
    }

    $.ajax({
        url: 'controladores/crearSeccionAdministracionControlador.php',
        method: 'POST',
        dataType: 'json', // 🔹 IMPORTANTE: Esperar respuesta JSON
        data: {
            id_curso: idCurso
        },
        success: function(respuesta) {
            if (respuesta.status === 'ok') {
                // Mostrar mensaje de éxito
                Swal.fire({
                    icon: 'success',
                    title: '¡Éxito!',
                    text: respuesta.mensaje
                }).then(() => {
                    // Recargar las secciones
                    $('#id_curso').trigger('change');
                });
            } else {
                // Mostrar mensaje de error
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: respuesta.mensaje
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Error en la solicitud:', error);
            Swal.fire({
                icon: 'error',
                title: 'Error de conexión',
                text: 'No se pudo crear la sección. Error: ' + error
            });
        }
    });
});
  </script>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$('#formInscripcion').on('submit', function(e) {
    e.preventDefault();
    
    var comprobanteInput = $('#comprobante')[0];
    var comprobanteError = $('#comprobanteError');
    
    // Validación básica del comprobante
    if (comprobanteInput.files.length === 0) {
        comprobanteError.text('El comprobante de pago es obligatorio');
        comprobanteError.show();
        $('#comprobante').addClass('is-invalid');
        return false;
    }
    
    var file = comprobanteInput.files[0];
    var validTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    
    if (!validTypes.includes(file.type)) {
        comprobanteError.text('Por favor, seleccione una imagen válida (JPG, JPEG, PNG, GIF o WEBP)');
        comprobanteError.show();
        $('#comprobante').addClass('is-invalid');
        return false;
    }

    // Mostrar loading
    Swal.fire({
        title: 'Procesando inscripción...',
        text: 'Por favor espere',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // Usar FormData para enviar el formulario
    var formData = new FormData(this);

    // 🔹 USAR EL NUEVO CONTROLADOR DE ADMINISTRACIÓN
    $.ajax({
        url: 'controladores/inscripcionAdministracionControlador.php', // Ruta relativa al nuevo controlador
        type: 'POST',
        data: formData,
        processData: false,
        contentType: false,
        dataType: 'json',
        success: function(data) {
    Swal.close();
    
    if (data.status === 'success') {
        Swal.fire({
            icon: 'success',
            title: '¡Inscripción registrada!',
            text: data.mensaje,
            confirmButtonText: 'Aceptar',
            timer: 1500, // Cierra automáticamente después de 1.5 segundos
            timerProgressBar: true
        }).then(() => {
            location.reload();
        });
        
        // Recargar después de 2 segundos (incluso si el usuario no hace clic)
        setTimeout(() => {
            location.reload();
        }, 2000);
        
    } else {
        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: data.mensaje || 'Error desconocido'
        });
    }
},
        error: function(xhr, status, error) {
            Swal.close();
            
            console.error('Error en la solicitud:', error);
            console.error('Respuesta del servidor:', xhr.responseText);
            
            let errorMessage = 'Error al conectar con el servidor';
            
            try {
                const response = JSON.parse(xhr.responseText);
                if (response.mensaje) {
                    errorMessage = response.mensaje;
                }
            } catch (e) {
                if (xhr.responseText) {
                    errorMessage = xhr.responseText;
                }
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Error del servidor',
                text: errorMessage
            });
        }
    });
});
</script>
    
<script>
$(document).ready(function() {
  console.log("Script de inscripción cargado");

  // Mostrar precio cuando se selecciona un curso
$('#id_curso').on('change', function() {
    const selectedOption = $(this).find('option:selected');
    const precio = selectedOption.data('precio');
    const tipoCurso = selectedOption.data('tipo');
    const fuente = selectedOption.data('fuente');
    const textoCurso = selectedOption.text();
    
    console.log("Curso seleccionado:", {
        precio: precio,
        tipo: tipoCurso,
        fuente: fuente,
        texto: textoCurso
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
        
        console.log("Precio mostrado:", precioFormateado);
    } else {
        $('#precioCurso').slideUp(300);
        $('#sinPrecio').slideDown(300);
        console.log("No hay precio disponible para este curso");
    }
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

  // Inicializar modal
  $('#modalInscripcion').on('show.bs.modal', function() {
    console.log("Modal de inscripción abierto");
    
    // Establecer fechas actuales
    const today = new Date().toISOString().split('T')[0];
    $('#fecha_inscripcion').val(today);
    $('#fecha_pago').val(today);
    
    // Inicializar estado de comprobante
    $('#metodo_pago').trigger('change');
    
    // Mostrar estado inicial
    $('#precioCurso').hide();
    $('#sinPrecio').show();
  });

  // Cuando el modal está completamente visible
  $('#modalInscripcion').on('shown.bs.modal', function() {
    // Si ya hay un curso seleccionado, mostrar su precio
    if ($('#id_curso').val()) {
      $('#id_curso').trigger('change');
    }
  });

  // Limpiar al cerrar modal
  $('#modalInscripcion').on('hidden.bs.modal', function() {
    $('#formInscripcion')[0].reset();
    $('#precioCurso').hide();
    $('#sinPrecio').show();
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
});
</script>
    
<script>
// Función para cargar precios reales via AJAX
function cargarPreciosReales(idInscripcion) {
    // Mostrar estado de carga
    $('#precioModulos').html('<small class="text-muted">Cargando...</small>');
    $('#precioAranceles').html('<small class="text-muted">Cargando...</small>');
    $('#precioArt13').html('<small class="text-muted">Cargando...</small>');

    $.ajax({
        url: '<?php echo SERVERURL; ?>/../../../controladores/obtenerPreciosControlador.php',
        type: 'POST',
        dataType: 'json',
        data: {
            id_inscripcion: idInscripcion,
            accion: 'obtener_precios'
        },
        success: function(response) {
            if (response.success) {
                // Actualizar tarjetas con datos reales
                $('#precioModulos').text('$' + parseFloat(response.precios.modulos).toFixed(2));
                $('#precioAranceles').text('$' + parseFloat(response.precios.aranceles).toFixed(2));
                $('#precioArt13').text('$' + parseFloat(response.precios.art13).toFixed(2));
                
                // Guardar precios y montos restantes para usarlos después
                $('#tarjetasPrecios').data('precios', response.precios);
                $('#tarjetasPrecios').data('montosRestantes', response.montos_restantes);
                
                console.log('Precios cargados correctamente:', response);
                
                // Actualizar información adicional en las tarjetas
                actualizarInfoAdicionalTarjetas(response.montos_restantes);
            } else {
                mostrarErrorPrecios(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar precios:', error);
            mostrarErrorPrecios('Error al cargar los precios');
        }
    });
}

// Función para actualizar información adicional en las tarjetas
function actualizarInfoAdicionalTarjetas(montosRestantes) {
    // Actualizar tooltips o información adicional
    $('#tarjetasPrecios .precio-card-compact').each(function() {
        const tipo = $(this).find('.card-title').text().trim();
        let info = '';
        
        switch(tipo) {
            case 'MÓDULOS':
                info = `Módulo 1: $${montosRestantes['Módulo 1'].toFixed(2)} restante<br>
                        Módulo 2: $${montosRestantes['Módulo 2'].toFixed(2)} restante<br>
                        Módulo 3: $${montosRestantes['Módulo 3'].toFixed(2)} restante`;
                break;
            case 'ARANCELES':
                info = `Restante: $${montosRestantes['Aranceles'].toFixed(2)}`;
                break;
            case 'ART. 13':
                info = `Restante: $${montosRestantes['art13'].toFixed(2)}`;
                break;
        }
        
        $(this).attr('data-toggle', 'tooltip');
        $(this).attr('data-html', 'true');
        $(this).attr('title', info);
    });
    
    // Inicializar tooltips
    $('[data-toggle="tooltip"]').tooltip();
}

// Función para manejar clic en tarjetas (auto-completar formulario)
function configurarEventosTarjetas() {
    $('#tarjetasPrecios .precio-card-compact').off('click').on('click', function() {
        const precios = $('#tarjetasPrecios').data('precios');
        const montosRestantes = $('#tarjetasPrecios').data('montosRestantes');
        
        if (!precios) {
            alert('Los precios no están disponibles');
            return;
        }
        
        const tipo = $(this).find('.card-title').text().trim();
        let motivo = '';
        let monto = 0;
        let montoRestante = 0;
        
        switch(tipo) {
            case 'MÓDULOS':
                // Buscar el primer módulo que tenga saldo pendiente
                if (montosRestantes['Módulo 1'] > 0) {
                    motivo = 'Módulo 1';
                    montoRestante = montosRestantes['Módulo 1'];
                } else if (montosRestantes['Módulo 2'] > 0) {
                    motivo = 'Módulo 2';
                    montoRestante = montosRestantes['Módulo 2'];
                } else if (montosRestantes['Módulo 3'] > 0) {
                    motivo = 'Módulo 3';
                    montoRestante = montosRestantes['Módulo 3'];
                } else {
                    alert('Todos los módulos están pagados completamente');
                    return;
                }
                monto = montoRestante; // Sugerir el monto restante
                break;
                
            case 'ARANCELES':
                motivo = 'Aranceles';
                montoRestante = montosRestantes['Aranceles'];
                monto = montoRestante > 0 ? montoRestante : precios.aranceles;
                break;
                
            case 'ART. 13':
                motivo = 'art13';
                montoRestante = montosRestantes['art13'];
                monto = montoRestante > 0 ? montoRestante : precios.art13;
                break;
        }
        
        // Si no hay saldo pendiente, usar el precio completo
        if (montoRestante <= 0) {
            monto = precios[tipo.toLowerCase()];
        }
        
        $('#motivo_pago').val(motivo);
        $('#monto_transaccion').val(monto.toFixed(2));
        
        // Mostrar información del monto restante
        if (montoRestante > 0) {
            alert(`Monto restante para ${motivo}: $${montoRestante.toFixed(2)}`);
        }
        
        // Efecto visual de selección
        $('#tarjetasPrecios .precio-card-compact').removeClass('selected');
        $(this).addClass('selected');
    });
}

// Función para mostrar error en las tarjetas
function mostrarErrorPrecios(mensaje) {
    $('#precioModulos').html('<small class="text-danger">Error</small>');
    $('#precioAranceles').html('<small class="text-danger">Error</small>');
    $('#precioArt13').html('<small class="text-danger">Error</small>');
    
    console.error('Error al cargar precios:', mensaje);
}

// Cuando se abre el modal de transacciones
$('#modalTransacciones').on('show.bs.modal', function (e) {
    const idInscripcion = $(e.relatedTarget).data('id');
    $('#transaccion_id_inscripcion').val(idInscripcion);
    
    // Cargar precios reales
    cargarPreciosReales(idInscripcion);
    
    // Configurar eventos después de un pequeño delay
    setTimeout(configurarEventosTarjetas, 100);
});

// También cargar transacciones existentes
function cargarTransaccionesExistentes(idInscripcion) {
    $.ajax({
        url: '<?php echo SERVERURL; ?>/../../../controladores/obtenerTransaccionesControlador.php',
        type: 'POST',
        data: {
            id_inscripcion: idInscripcion,
            accion: 'obtener_transacciones'
        },
        success: function(response) {
            $('#contenidoTransacciones').html(response);
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar transacciones:', error);
            $('#contenidoTransacciones').html('<p class="text-danger">Error al cargar transacciones</p>');
        }
    });
}
</script>
    
<script>

// Función para mostrar/ocultar motivos de pago según el tipo de curso
function actualizarMotivosPago(tipoCurso) {
    if (tipoCurso === 'Intensivo') {
        $('.motivo-no-intensivo').hide();
        $('.motivo-intensivo').show();
        
        // Seleccionar primer motivo intensivo por defecto
        $('#motivo_pago').val('Encuentro 1');
    } else {
        $('.motivo-intensivo').hide();
        $('.motivo-no-intensivo').show();
        
        // Seleccionar primer motivo no intensivo por defecto
        $('#motivo_pago').val('Módulo 1');
    }
}

// Llamar esta función cuando se seleccione un curso en el modal de transacciones
$(document).on('click', '.btn-transacciones', function() {
    const idInscripcion = $(this).data('id');
    // Obtener el tipo de curso de esta inscripción y llamar a actualizarMotivosPago()
});

// También cuando se cambie el curso en el modal de inscripción
$('#id_curso').on('change', function() {
    const tipoCurso = $(this).find('option:selected').data('tipo');
    // Guardar el tipo de curso para usarlo luego en transacciones
    $('#modalInscripcion').data('tipo-curso', tipoCurso);
});
    
</script> 

<script>

function cargarInformacionCursoTransacciones(idInscripcion) {
    console.log('Cargando información del curso para inscripción:', idInscripcion);
    
    // Mostrar estado de carga
    $('#tarjetasPrecios').html(`
        <div class="col-12 text-center">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
            <p class="mt-2 text-muted">Cargando información del curso...</p>
        </div>
    `);

    $.ajax({
        url: '../../../controladores/obtenerInfoCursoControlador.php',
        type: 'POST',
        dataType: 'json',
        data: {
            id_inscripcion: idInscripcion,
            accion: 'obtener_info_curso'
        },
        success: function(response) {
            if (response.success) {
                const curso = response.curso;
                const montosPagados = response.montos_pagados;
                
                console.log('Información del curso:', curso);
                
                // Mostrar las tarjetas correspondientes al tipo de curso
                if (curso.tipo_curso === 'Intensivo') {
                    mostrarTarjetasIntensivas(curso, montosPagados);
                    actualizarSelectMotivosIntensivo();
                } else {
                    mostrarTarjetasNoIntensivas(curso, montosPagados);
                    actualizarSelectMotivosNoIntensivo();
                }
            } else {
                mostrarErrorTarjetas(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error:', error);
            mostrarErrorTarjetas('Error al cargar la información del curso');
        }
    });
}

// Mostrar tarjetas para cursos NO intensivos
function mostrarTarjetasNoIntensivas(curso, montosPagados) {
    const tarjetasPrecios = $('#tarjetasPrecios');
    tarjetasPrecios.empty();
    
    // Calcular montos restantes
    const montosRestantes = calcularMontosRestantesNoIntensivos(curso, montosPagados);
    
    // Tarjeta de Módulos
    tarjetasPrecios.append(`
        <div class="col-md-4 mb-3">
            <div class="card precio-card-compact h-100" onclick="seleccionarMotivo('modulos', ${montosRestantes.modulo1}, ${montosRestantes.modulo2}, ${montosRestantes.modulo3})">
                <div class="card-body text-center p-3">
                    <div class="precio-icono-compact mb-2">
                        <i class="fas fa-cubes fa-2x"></i>
                    </div>
                    <h6 class="card-title text-muted mb-2">MÓDULOS</h6>
                    <div class="precio-display-compact mb-2">$${parseFloat(curso.pago_modulo || 0).toFixed(2)}</div>
                    <p class="text-muted small mb-2">Por módulo</p>
                    <div class="info-montos-restantes">
                        <small class="text-success">
                            <strong>Restantes:</strong><br>
                            M1: $${montosRestantes.modulo1.toFixed(2)}<br>
                            M2: $${montosRestantes.modulo2.toFixed(2)}<br>
                            M3: $${montosRestantes.modulo3.toFixed(2)}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    // Tarjeta de Aranceles
    tarjetasPrecios.append(`
        <div class="col-md-4 mb-3">
            <div class="card precio-card-compact h-100" onclick="seleccionarMotivo('aranceles', ${montosRestantes.aranceles})">
                <div class="card-body text-center p-3">
                    <div class="precio-icono-compact mb-2">
                        <i class="fas fa-file-invoice-dollar fa-2x"></i>
                    </div>
                    <h6 class="card-title text-muted mb-2">ARANCELES</h6>
                    <div class="precio-display-compact mb-2">$${parseFloat(curso.pago_aranceles || 0).toFixed(2)}</div>
                    <p class="text-muted small mb-2">Aranceles</p>
                    <div class="info-montos-restantes">
                        <small class="text-success">
                            <strong>Restante:</strong><br>
                            $${montosRestantes.aranceles.toFixed(2)}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `);
    
    // Tarjeta de Artículo 13
    tarjetasPrecios.append(`
        <div class="col-md-4 mb-3">
            <div class="card precio-card-compact h-100" onclick="seleccionarMotivo('art13', ${montosRestantes.art13})">
                <div class="card-body text-center p-3">
                    <div class="precio-icono-compact mb-2">
                        <i class="fas fa-gavel fa-2x"></i>
                    </div>
                    <h6 class="card-title text-muted mb-2">ART. 13</h6>
                    <div class="precio-display-compact mb-2">$${parseFloat(curso.pago_art13 || 0).toFixed(2)}</div>
                    <p class="text-muted small mb-2">Artículo 13</p>
                    <div class="info-montos-restantes">
                        <small class="text-success">
                            <strong>Restante:</strong><br>
                            $${montosRestantes.art13.toFixed(2)}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `);
}

// Mostrar tarjetas para cursos INTENSIVOS
function mostrarTarjetasIntensivas(curso, montosPagados) {
    const tarjetasPrecios = $('#tarjetasPrecios');
    tarjetasPrecios.empty();
    
    // Por defecto mostramos 6 encuentros, pero puedes cambiar este número
    const cantidadEncuentros = 6;
    const montosRestantes = calcularMontosRestantesIntensivos(curso, montosPagados, cantidadEncuentros);
    
    // Generar tarjetas para cada encuentro
    for (let i = 1; i <= cantidadEncuentros; i++) {
        tarjetasPrecios.append(`
            <div class="col-md-4 mb-3">
                <div class="card precio-card-compact h-100" onclick="seleccionarMotivoIntensivo('encuentro', ${i}, ${montosRestantes[`encuentro${i}`]})">
                    <div class="card-body text-center p-3">
                        <div class="precio-icono-compact mb-2">
                            <i class="fas fa-handshake fa-2x"></i>
                        </div>
                        <h6 class="card-title text-muted mb-2">ENCUENTRO ${i}</h6>
                        <div class="precio-display-compact mb-2">$${parseFloat(curso.pago_encuentro_int || 0).toFixed(2)}</div>
                        <p class="text-muted small mb-2">Encuentro ${i}</p>
                        <div class="info-montos-restantes">
                            <small class="text-success">
                                <strong>Restante:</strong><br>
                                $${montosRestantes[`encuentro${i}`].toFixed(2)}
                            </small>
                        </div>
                    </div>
                </div>
            </div>
        `);
    }
    
    // Tarjeta de Titulación
    tarjetasPrecios.append(`
        <div class="col-md-4 mb-3">
            <div class="card precio-card-compact h-100" onclick="seleccionarMotivoIntensivo('titulacion', null, ${montosRestantes.titulacion})">
                <div class="card-body text-center p-3">
                    <div class="precio-icono-compact mb-2">
                        <i class="fas fa-graduation-cap fa-2x"></i>
                    </div>
                    <h6 class="card-title text-muted mb-2">TITULACIÓN</h6>
                    <div class="precio-display-compact mb-2">$${parseFloat(curso.pago_titulo_int || 0).toFixed(2)}</div>
                    <p class="text-muted small mb-2">Titulación</p>
                    <div class="info-montos-restantes">
                        <small class="text-success">
                            <strong>Restante:</strong><br>
                            $${montosRestantes.titulacion.toFixed(2)}
                        </small>
                    </div>
                </div>
            </div>
        </div>
    `);
}

// Actualizar select para cursos NO intensivos
function actualizarSelectMotivosNoIntensivo() {
    const selectMotivo = $('#motivo_pago');
    selectMotivo.empty();
    selectMotivo.append('<option value="">Seleccione</option>');
    selectMotivo.append('<option value="Inscripción">Inscripción</option>');
    selectMotivo.append('<option value="Módulo 1">Módulo 1</option>');
    selectMotivo.append('<option value="Módulo 2">Módulo 2</option>');
    selectMotivo.append('<option value="Módulo 3">Módulo 3</option>');
    selectMotivo.append('<option value="Aranceles">Aranceles</option>');
    selectMotivo.append('<option value="art13">Artículo 13</option>');
}

// Actualizar select para cursos INTENSIVOS
function actualizarSelectMotivosIntensivo() {
    const selectMotivo = $('#motivo_pago');
    selectMotivo.empty();
    selectMotivo.append('<option value="">Seleccione</option>');
    selectMotivo.append('<option value="Inscripción">Inscripción</option>');
    
    // Agregar opciones para encuentros (6 por defecto)
    for (let i = 1; i <= 6; i++) {
        selectMotivo.append(`<option value="Encuentro ${i}">Encuentro ${i}</option>`);
    }
    
    selectMotivo.append('<option value="Titulación">Titulación</option>');
}

// Funciones para calcular montos restantes
function calcularMontosRestantesNoIntensivos(curso, montosPagados) {
    const montosRestantes = {
        modulo1: parseFloat(curso.pago_modulo || 0),
        modulo2: parseFloat(curso.pago_modulo || 0),
        modulo3: parseFloat(curso.pago_modulo || 0),
        aranceles: parseFloat(curso.pago_aranceles || 0),
        art13: parseFloat(curso.pago_art13 || 0)
    };
    
    // Restar montos ya pagados
    montosPagados.forEach(pago => {
        switch(pago.motivo_pago) {
            case 'Módulo 1': montosRestantes.modulo1 -= parseFloat(pago.total_pagado); break;
            case 'Módulo 2': montosRestantes.modulo2 -= parseFloat(pago.total_pagado); break;
            case 'Módulo 3': montosRestantes.modulo3 -= parseFloat(pago.total_pagado); break;
            case 'Aranceles': montosRestantes.aranceles -= parseFloat(pago.total_pagado); break;
            case 'art13': montosRestantes.art13 -= parseFloat(pago.total_pagado); break;
        }
    });
    
    // Asegurar que no sean negativos
    Object.keys(montosRestantes).forEach(key => {
        montosRestantes[key] = Math.max(0, montosRestantes[key]);
    });
    
    return montosRestantes;
}

function calcularMontosRestantesIntensivos(curso, montosPagados, cantidadEncuentros) {
    const montosRestantes = {
        titulacion: parseFloat(curso.pago_titulo_int || 0)
    };
    
    // Inicializar montos para cada encuentro
    for (let i = 1; i <= cantidadEncuentros; i++) {
        montosRestantes[`encuentro${i}`] = parseFloat(curso.pago_encuentro_int || 0);
    }
    
    // Restar montos ya pagados
    montosPagados.forEach(pago => {
        if (pago.motivo_pago.startsWith('Encuentro ')) {
            const numeroEncuentro = parseInt(pago.motivo_pago.replace('Encuentro ', ''));
            if (!isNaN(numeroEncuentro)) {
                montosRestantes[`encuentro${numeroEncuentro}`] -= parseFloat(pago.total_pagado);
            }
        } else if (pago.motivo_pago === 'Titulación') {
            montosRestantes.titulacion -= parseFloat(pago.total_pagado);
        }
    });
    
    // Asegurar que no sean negativos
    Object.keys(montosRestantes).forEach(key => {
        montosRestantes[key] = Math.max(0, montosRestantes[key]);
    });
    
    return montosRestantes;
}

// Funciones para seleccionar motivos
function seleccionarMotivo(tipo, monto1, monto2, monto3) {
    let motivo = '';
    let monto = 0;
    
    switch(tipo) {
        case 'modulos':
            if (monto1 > 0) {
                motivo = 'Módulo 1';
                monto = monto1;
            } else if (monto2 > 0) {
                motivo = 'Módulo 2';
                monto = monto2;
            } else if (monto3 > 0) {
                motivo = 'Módulo 3';
                monto = monto3;
            } else {
                motivo = 'Módulo 1';
                monto = monto1;
            }
            break;
        case 'aranceles':
            motivo = 'Aranceles';
            monto = monto1 > 0 ? monto1 : monto1;
            break;
        case 'art13':
            motivo = 'art13';
            monto = monto1 > 0 ? monto1 : monto1;
            break;
    }
    
    $('#motivo_pago').val(motivo);
    $('input[name="monto"]').val(monto.toFixed(2));
}

function seleccionarMotivoIntensivo(tipo, numero, monto) {
    let motivo = '';
    
    if (tipo === 'encuentro') {
        motivo = `Encuentro ${numero}`;
    } else if (tipo === 'titulacion') {
        motivo = 'Titulación';
    }
    
    $('#motivo_pago').val(motivo);
    $('input[name="monto"]').val(monto.toFixed(2));
}

function mostrarErrorTarjetas(mensaje) {
    $('#tarjetasPrecios').html(`
        <div class="col-12 text-center">
            <div class="alert alert-warning">
                <i class="fas fa-exclamation-triangle"></i>
                <p class="mt-2">${mensaje}</p>
            </div>
        </div>
    `);
}

    
</script>
    
#formNuevaTransaccion
