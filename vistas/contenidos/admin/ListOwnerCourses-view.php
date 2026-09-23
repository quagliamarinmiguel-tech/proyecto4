<?php
require_once "./controladores/ListInscriptionStudentControlador.php";
$inscripcionControlador = new inscripcionControlador();
$inscripciones = $inscripcionControlador->listar_inscripciones_estudiante();
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Mis Inscripciones</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <style>
    .precio-card-compact {
        border: 1px solid #e0e0e0;
        border-radius: 10px;
        transition: all 0.3s ease;
        cursor: pointer;
        background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%);
    }

    .precio-card-compact:hover {
        border-color: #007bff;
        transform: translateY(-2px);
        box-shadow: 0 4px 8px rgba(0,0,0,0.1);
    }

    .precio-card-compact.border-primary {
        border: 2px solid #007bff;
        box-shadow: 0 4px 12px rgba(0,123,255,0.2);
    }

    .precio-display-compact {
        font-size: 1.5rem;
        font-weight: 700;
        color: #28a745;
    }

    .precio-icono-compact {
        color: #6c757d;
    }

    .info-montos-restantes {
        background: #f8f9fa;
        padding: 8px;
        border-radius: 5px;
        margin-top: 8px;
        border-left: 3px solid #28a745;
    }

    .table-responsive {
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 0 4px 6px rgba(0,0,0,0.1);
    }
    </style>
</head>
<body class="bg-light">
<div class="container mt-4">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            <i class="fas fa-list-alt text-primary"></i> Mis Inscripciones
        </h3>
        <div>
            <a href="reportes/inscripcionesPDF.php" class="btn btn-danger">
                <i class="fas fa-file-pdf"></i> Exportar PDF
            </a>
            <a href="reportes/inscripcionesExcel.php" class="btn btn-success">
                <i class="fas fa-file-excel"></i> Exportar Excel
            </a>
        </div>
    </div>

    <div class="table-responsive">
        <table class="table table-bordered table-hover bg-white">
            <thead class="thead-dark">
                <tr>
                    <th>ID</th>
                    <th>Curso</th>
                    <th>Tipo</th>
                    <th>Sección</th>
                    <th>Cohorte/Intensivo</th>
                    <th>Fecha Inscripción</th>
                    <th>Fecha Abandono</th>
                    <th>Estado</th>
                    <th>Nota Final</th>
                    <th>Observaciones</th>
                    <th>Pago Faltante</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($inscripciones)): ?>
                <tr>
                    <td colspan="12" class="text-center py-4">
                        <i class="fas fa-inbox fa-3x text-muted mb-3"></i>
                        <p class="text-muted">No tienes inscripciones registradas.</p>
                        <a href="index.php?views=Estudiante/inscribirse" class="btn btn-primary">
                            <i class="fas fa-plus"></i> Inscribirse a un curso
                        </a>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($inscripciones as $row): 
                    $pago = strtolower($row['pago_faltante'] ?? '');
                    $color = $pago === 'atrasado' ? 'danger' : ($pago === 'pendiente' ? 'warning' : 'success');
                    $fecha_abandono = $row['if_fecha_abandono'] ? date("d/m/Y", strtotime($row['if_fecha_abandono'])) : '—';
                    
                    // Determinar texto del cohorte/intensivo
                    if ($row['tipo_curso'] === 'Intensivo') {
                        $cohorte_text = 'Intensivo';
                        if ($row['fecha_inicio_int']) {
                            $cohorte_text .= ' (' . date("m/Y", strtotime($row['fecha_inicio_int'])) . ')';
                        }
                    } else {
                        $cohorte_text = $row['numero_cohorte'] ? "Cohorte {$row['numero_cohorte']} - {$row['anio_cohorte']}" : '—';
                    }
                ?>
                <tr>
                    <td><?= htmlspecialchars($row['id_inscripcion']) ?></td>
                    <td><?= htmlspecialchars($row['nombre_curso']) ?></td>
                    <td>
                        <span class="badge badge-<?= $row['tipo_curso'] === 'Intensivo' ? 'info' : 'primary' ?>">
                            <?= htmlspecialchars($row['tipo_curso']) ?>
                        </span>
                    </td>
                    <td><?= htmlspecialchars($row['nombre_seccion']) ?></td>
                    <td><?= htmlspecialchars($cohorte_text) ?></td>
                    <td><?= date("d/m/Y", strtotime($row['fecha_inscripcion'])) ?></td>
                    <td><?= $fecha_abandono ?></td>
                    <td>
                        <span class="badge badge-<?= $row['estado_inscripcion'] === 'Activa' ? 'success' : ($row['estado_inscripcion'] === 'Inactiva' ? 'secondary' : 'warning') ?>">
                            <?= htmlspecialchars($row['estado_inscripcion']) ?>
                        </span>
                    </td>
                    <td><?= $row['nota_final'] ? htmlspecialchars($row['nota_final']) : '—' ?></td>
                    <td><?= $row['observaciones'] ? htmlspecialchars($row['observaciones']) : '—' ?></td>
                    <td>
                        <span class="badge badge-<?= $color ?>">
                            <?= $row['pago_faltante'] ? '$' . number_format($row['pago_faltante'], 2) : 'Completo' ?>
                        </span>
                    </td>
                    <td>
                        <div class="btn-group btn-group-sm" role="group">
                            <button class="btn btn-info btn-transacciones" data-id="<?= $row['id_inscripcion'] ?>" data-toggle="modal" data-target="#modalTransacciones" title="Ver transacciones">
                                <i class="fas fa-money-bill-wave"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Modal de Transacciones -->
<div class="modal fade" id="modalTransacciones" tabindex="-1" role="dialog" aria-labelledby="modalTransaccionesLabel" aria-hidden="true">
  <div class="modal-dialog modal-xl" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title">
            <i class="fas fa-money-bill-wave"></i> Transacciones de la Inscripción
        </h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <!-- Tarjetas de Precios Dinámicas -->
        <div class="row mb-4" id="tarjetasPrecios">
          <!-- Aquí se cargan las tarjetas dinámicamente -->
        </div>

        <div id="contenidoTransacciones">
          <p class="text-center">
            <i class="fas fa-spinner fa-spin text-primary"></i> Cargando transacciones...
          </p>
        </div>

        <hr>
        <h5 class="mt-4">
            <i class="fas fa-plus-circle text-success"></i> Registrar Nueva Transacción
        </h5>
        <form id="formNuevaTransaccion" enctype="multipart/form-data">
          <input type="hidden" name="id_inscripcion" id="transaccion_id_inscripcion">

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="motivo_pago"><i class="fas fa-tag"></i> Motivo de Pago</label>
              <select class="form-control" name="motivo_pago" id="motivo_pago" required>
                <option value="">Seleccione</option>
                <!-- Las opciones se cargan dinámicamente según el tipo de curso -->
              </select>
            </div>
            <div class="form-group col-md-6">
              <label for="monto"><i class="fas fa-dollar-sign"></i> Monto</label>
              <input type="number" class="form-control" name="monto" required step="0.01" min="0.01" placeholder="0.00">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-6">
              <label for="fecha_pago"><i class="fas fa-calendar"></i> Fecha de Pago</label>
              <input type="date" class="form-control" name="fecha_pago" required>
            </div>
            <div class="form-group col-md-6">
              <label for="metodo_pago"><i class="fas fa-credit-card"></i> Método de Pago</label>
              <select class="form-control" name="metodo_pago" required>
                <option value="">Seleccione</option>
                <option value="Transferencia">Transferencia</option>
                <option value="Pago móvil">Pago móvil</option>
                <option value="Efectivo">Efectivo</option>
              </select>
            </div>
          </div>

          <div class="form-row">
            <div class="form-group col-md-12">
              <label for="comprobante"><i class="fas fa-file-image"></i> Comprobante</label>
              <input type="file" class="form-control-file" name="comprobante" accept="image/*" required>
              <small class="form-text text-muted">Formatos permitidos: JPG, JPEG, PNG, GIF, WEBP. Máximo 5MB.</small>
            </div>
          </div>

          <button type="submit" class="btn btn-primary btn-block">
            <i class="fas fa-save"></i> Registrar Transacción
          </button>
        </form>
      </div>
    </div>
  </div>
</div>

<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
// ==============================================
// FUNCIONES PARA MODAL DE TRANSACCIONES - ESTUDIANTE
// ==============================================

// Función para cargar información del curso
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
        url: 'controladores/obtenerInfoCursoControlador.php',
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
                
                console.log('Información del curso cargada:', curso);
                
                // Mostrar las tarjetas correspondientes al tipo de curso
                if (curso.tipo_curso === 'Intensivo') {
                    mostrarTarjetasIntensivas(curso, montosPagados);
                    actualizarSelectMotivosIntensivo(curso);
                } else {
                    mostrarTarjetasNoIntensivas(curso, montosPagados);
                    actualizarSelectMotivosNoIntensivo();
                }
                
                // Configurar eventos para las tarjetas
                configurarEventosTarjetas();
            } else {
                mostrarErrorTarjetas(response.message);
            }
        },
        error: function(xhr, status, error) {
            console.error('Error al cargar información del curso:', error);
            mostrarErrorTarjetas('Error al cargar la información del curso');
        }
    });
}

// Función para mostrar tarjetas de cursos NO intensivos (Extendido/Técnico Profesional)
function mostrarTarjetasNoIntensivas(curso, montosPagados) {
    const tarjetasPrecios = $('#tarjetasPrecios');
    tarjetasPrecios.empty();
    
    // Calcular montos restantes
    const montosRestantes = calcularMontosRestantesNoIntensivos(curso, montosPagados);
    
    // Tarjeta de Módulos
    tarjetasPrecios.append(`
        <div class="col-md-4 mb-3">
            <div class="card precio-card-compact h-100" data-tipo="no-intensivo" data-categoria="modulos">
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
            <div class="card precio-card-compact h-100" data-tipo="no-intensivo" data-categoria="aranceles">
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
            <div class="card precio-card-compact h-100" data-tipo="no-intensivo" data-categoria="art13">
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

// Función para mostrar tarjetas de cursos INTENSIVOS
function mostrarTarjetasIntensivas(curso, montosPagados) {
    const tarjetasPrecios = $('#tarjetasPrecios');
    tarjetasPrecios.empty();
    
    const cantidadEncuentros = curso.cantidad_encuentros || 6; // Valor por defecto
    const montosRestantes = calcularMontosRestantesIntensivos(curso, montosPagados, cantidadEncuentros);
    
    // Generar tarjetas para cada encuentro
    for (let i = 1; i <= cantidadEncuentros; i++) {
        tarjetasPrecios.append(`
            <div class="col-md-4 mb-3">
                <div class="card precio-card-compact h-100" data-tipo="intensivo" data-categoria="encuentro-${i}">
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
            <div class="card precio-card-compact h-100" data-tipo="intensivo" data-categoria="titulacion">
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

// Función para actualizar select de motivos para cursos NO intensivos
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

// Función para actualizar select de motivos para cursos INTENSIVOS
function actualizarSelectMotivosIntensivo(curso) {
    const selectMotivo = $('#motivo_pago');
    selectMotivo.empty();
    selectMotivo.append('<option value="">Seleccione</option>');
    selectMotivo.append('<option value="Inscripción">Inscripción</option>');
    
    const cantidadEncuentros = curso.cantidad_encuentros || 6;
    
    // Agregar opciones para cada encuentro
    for (let i = 1; i <= cantidadEncuentros; i++) {
        selectMotivo.append(`<option value="Encuentro ${i}">Encuentro ${i}</option>`);
    }
    
    selectMotivo.append('<option value="Titulación">Titulación</option>');
}

// Funciones auxiliares para calcular montos restantes
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
            case 'Módulo 1':
                montosRestantes.modulo1 -= parseFloat(pago.total_pagado);
                break;
            case 'Módulo 2':
                montosRestantes.modulo2 -= parseFloat(pago.total_pagado);
                break;
            case 'Módulo 3':
                montosRestantes.modulo3 -= parseFloat(pago.total_pagado);
                break;
            case 'Aranceles':
                montosRestantes.aranceles -= parseFloat(pago.total_pagado);
                break;
            case 'art13':
                montosRestantes.art13 -= parseFloat(pago.total_pagado);
                break;
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

// Configurar eventos para las tarjetas (auto-completar formulario)
function configurarEventosTarjetas() {
    $('.precio-card-compact').off('click').on('click', function() {
        const tipo = $(this).data('tipo');
        const categoria = $(this).data('categoria');
        const precioText = $(this).find('.precio-display-compact').text();
        const precio = parseFloat(precioText.replace('$', ''));
        
        let motivoPago = '';
        let montoSugerido = precio;
        
        if (tipo === 'no-intensivo') {
            switch(categoria) {
                case 'modulos':
                    // Buscar el primer módulo con saldo pendiente
                    const moduloRestante = $(this).find('small.text-success').text();
                    if (moduloRestante.includes('M1:')) {
                        const m1Restante = parseFloat(moduloRestante.split('M1: $')[1].split('\n')[0]);
                        if (m1Restante > 0) {
                            motivoPago = 'Módulo 1';
                            montoSugerido = m1Restante;
                        } else {
                            const m2Restante = parseFloat(moduloRestante.split('M2: $')[1].split('\n')[0]);
                            if (m2Restante > 0) {
                                motivoPago = 'Módulo 2';
                                montoSugerido = m2Restante;
                            } else {
                                const m3Restante = parseFloat(moduloRestante.split('M3: $')[1]);
                                motivoPago = 'Módulo 3';
                                montoSugerido = m3Restante;
                            }
                        }
                    }
                    break;
                case 'aranceles':
                    motivoPago = 'Aranceles';
                    const arancelesRestante = parseFloat($(this).find('small.text-success').text().split('Restante: $')[1]);
                    montoSugerido = arancelesRestante > 0 ? arancelesRestante : precio;
                    break;
                case 'art13':
                    motivoPago = 'art13';
                    const art13Restante = parseFloat($(this).find('small.text-success').text().split('Restante: $')[1]);
                    montoSugerido = art13Restante > 0 ? art13Restante : precio;
                    break;
            }
        } else if (tipo === 'intensivo') {
            if (categoria.startsWith('encuentro-')) {
                const numeroEncuentro = categoria.split('encuentro-')[1];
                motivoPago = `Encuentro ${numeroEncuentro}`;
                const encuentroRestante = parseFloat($(this).find('small.text-success').text().split('Restante: $')[1]);
                montoSugerido = encuentroRestante > 0 ? encuentroRestante : precio;
            } else if (categoria === 'titulacion') {
                motivoPago = 'Titulación';
                const titulacionRestante = parseFloat($(this).find('small.text-success').text().split('Restante: $')[1]);
                montoSugerido = titulacionRestante > 0 ? titulacionRestante : precio;
            }
        }
        
        if (motivoPago) {
            $('#motivo_pago').val(motivoPago);
            $('input[name="monto"]').val(montoSugerido.toFixed(2));
            
            // Efecto visual de selección
            $('.precio-card-compact').removeClass('border-primary');
            $(this).addClass('border-primary');
            
            console.log('Formulario auto-completado:', { motivoPago, montoSugerido });
        }
    });
}

// Función para mostrar error en las tarjetas
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

// ==============================================
// EVENTOS PRINCIPALES - ESTUDIANTE
// ==============================================

// Evento para abrir modal de transacciones
$(document).on('click', '.btn-transacciones', function () {
    const id = $(this).data('id');
    console.log('Abriendo transacciones para inscripción:', id);
    
    $('#transaccion_id_inscripcion').val(id);
    $('#contenidoTransacciones').html('<p class="text-center"><i class="fas fa-spinner fa-spin"></i> Cargando transacciones...</p>');

    // Cargar información del curso y mostrar tarjetas/motivos correspondientes
    cargarInformacionCursoTransacciones(id);
    
    // Cargar transacciones existentes
    $.post('ajax/obtenerTransacciones.php', { id_inscripcion: id }, function (data) {
        $('#contenidoTransacciones').html(data);
    }).fail(function() {
        $('#contenidoTransacciones').html('<p class="text-danger text-center">Error al cargar transacciones.</p>');
    });
});

// Enviar nueva transacción
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
        url: 'ajax/registrarTransaccion.php',
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
                        // Recargar transacciones
                        const id = $('#transaccion_id_inscripcion').val();
                        $('#formNuevaTransaccion')[0].reset();
                        
                        // Recargar información del curso (para actualizar montos restantes)
                        cargarInformacionCursoTransacciones(id);
                        
                        // Recargar lista de transacciones
                        $.post('ajax/obtenerTransacciones.php', { id_inscripcion: id }, function (data) {
                            $('#contenidoTransacciones').html(data);
                        });
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.mensaje || 'Error desconocido'
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

// Limpiar formulario cuando se cierra el modal
$('#modalTransacciones').on('hidden.bs.modal', function () {
    $('#formNuevaTransaccion')[0].reset();
    $('.precio-card-compact').removeClass('border-primary');
});

// Establecer fecha actual por defecto en el formulario de transacciones
$('#modalTransacciones').on('show.bs.modal', function () {
    const hoy = new Date().toISOString().split('T')[0];
    $('input[name="fecha_pago"]').val(hoy);
});
</script>

</body>
</html>
