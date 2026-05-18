<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_usuario = $_POST['id_usuario'] ?? null;

if (!$id_usuario) {
    echo '
    <div class="notificacion-vacia">
        <div class="text-center py-4">
            <i class="fas fa-exclamation-circle text-warning fa-2x mb-3"></i>
            <p class="text-muted mb-0">No se pudieron cargar las notificaciones</p>
        </div>
    </div>';
    exit;
}

// FUNCIÓN PARA OBTENER PRIORIDAD - MOVERLA AL INICIO
function obtenerPrioridad($tipo) {
    switch($tipo) {
        case 'sin_pago':
        case 'transaccion_inusual':
            return 'priority-critical';
        case 'pago_pendiente':
        case 'pago_proximo_vencer':
            return 'priority-high';
        case 'pago_movil_pendiente':
            return 'priority-medium';
        case 'cohorte_deuda':
        case 'resumen_mensual':
            return 'priority-low';
        default:
            return '';
    }
}

function tiempo_transcurrido($fecha) {
    $diferencia = time() - strtotime($fecha);
    
    if ($diferencia < 60) return 'Ahora mismo';
    if ($diferencia < 3600) return 'Hace ' . round($diferencia/60) . ' min';
    if ($diferencia < 86400) return 'Hace ' . round($diferencia/3600) . ' h';
    if ($diferencia < 2592000) return 'Hace ' . round($diferencia/86400) . ' días';
    
    return date('d/m/Y', strtotime($fecha));
}

try {
    require_once __DIR__ . '/../modelos/mainModel.php';
    require_once __DIR__ . '/../modelos/generar_notificaciones_pagos.php';

    $db = mainModel::conectar();
    $modelo = new NotificacionPagosModelo($db);

    $notificaciones_reales = $modelo->generarNotificacionesPagos($id_usuario);
    
    if (empty($notificaciones_reales)) {
        echo '
        <div class="notificacion-vacia">
            <div class="text-center py-4">
                <i class="fas fa-bell-slash text-muted fa-2x mb-3"></i>
                <h6 class="text-muted">No tienes notificaciones</h6>
                <small class="text-muted">Todo está al día en pagos</small>
            </div>
        </div>';
    } else {
        foreach ($notificaciones_reales as $index => $notificacion) {
            $tiempo = tiempo_transcurrido($notificacion['fecha_creacion']);
            
            switch ($notificacion['tipo']) {
                case 'pago_pendiente':
                    $icono = 'fa-money-bill-wave';
                    $color = 'text-warning';
                    $badge = 'bg-warning';
                    break;
                case 'sin_pago':
                    $icono = 'fa-exclamation-circle';
                    $color = 'text-danger';
                    $badge = 'bg-danger';
                    break;
                case 'resumen_mensual':
                    $icono = 'fa-chart-bar';
                    $color = 'text-info';
                    $badge = 'bg-info';
                    break;
                case 'pago_reciente':
                    $icono = 'fa-check-circle';
                    $color = 'text-success';
                    $badge = 'bg-success';
                    break;
                // NUEVOS TIPOS
                case 'pago_proximo_vencer':
                    $icono = 'fa-clock';
                    $color = 'text-warning';
                    $badge = 'bg-warning';
                    break;
                case 'pago_movil_pendiente':
                    $icono = 'fa-mobile-alt';
                    $color = 'text-info';
                    $badge = 'bg-info';
                    break;
                case 'cohorte_deuda':
                    $icono = 'fa-users';
                    $color = 'text-primary';
                    $badge = 'bg-primary';
                    break;
                case 'transaccion_inusual':
                    $icono = 'fa-exclamation-triangle';
                    $color = 'text-danger';
                    $badge = 'bg-danger';
                    break;
                default:
                    $icono = 'fa-info-circle';
                    $color = 'text-primary';
                    $badge = 'bg-primary';
            }
            
            // SOLO UN div de notificación-item - aplicar la clase de prioridad aquí
            echo '
            <div class="notificacion-item ' . obtenerPrioridad($notificacion['tipo']) . '" id="notif-'.$index.'">
                <div class="notificacion-icono '.$color.'">
                    <i class="fas '.$icono.'"></i>
                </div>
                <div class="notificacion-contenido">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="notificacion-titulo">'.$notificacion['motivo'].'</div>
                        <span class="badge '.$badge.' badge-sm">'.$notificacion['tipo'].'</span>
                    </div>
                    <div class="notificacion-descripcion">'.$notificacion['referencia'].'</div>';
            
            // Mostrar información adicional para pagos pendientes
            if ($notificacion['tipo'] == 'pago_pendiente') {
                echo '<div class="notificacion-detalles">
                        <small class="text-muted">Total pagado: $'.$notificacion['total_pagado'].'</small>
                      </div>';
            }
            
            // Mostrar información adicional para resumen mensual
            if ($notificacion['tipo'] == 'resumen_mensual') {
                echo '<div class="notificacion-detalles">
                        <small class="text-success">Recaudado este mes: $'.$notificacion['recaudado_mes'].'</small><br>
                        <small class="text-danger">Deuda pendiente: $'.$notificacion['total_deuda'].'</small>
                      </div>';
            }
            
            // Mostrar información adicional para pagos próximos a vencer
            if ($notificacion['tipo'] == 'pago_proximo_vencer') {
                echo '<div class="notificacion-detalles">
                        <small class="text-warning"><i class="fas fa-clock"></i> Vence en ' . $notificacion['dias_restantes'] . ' días</small>
                      </div>';
            }

            // Mostrar información adicional para pagos móviles pendientes
            if ($notificacion['tipo'] == 'pago_movil_pendiente') {
                echo '<div class="notificacion-detalles">
                        <small class="text-info"><i class="fas fa-history"></i> Pendiente por ' . $notificacion['dias_pendiente'] . ' días</small>
                      </div>';
            }

            // Mostrar información adicional para cohortes con deuda
            if ($notificacion['tipo'] == 'cohorte_deuda') {
                echo '<div class="notificacion-detalles">
                        <small class="text-danger">' . $notificacion['estudiantes_deudores'] . ' estudiantes - Deuda total: $' . $notificacion['deuda_total'] . '</small>
                      </div>';
            }

            // Mostrar información adicional para transacciones inusuales
            if ($notificacion['tipo'] == 'transaccion_inusual') {
                echo '<div class="notificacion-detalles">
                        <small class="text-danger"><i class="fas fa-exclamation-triangle"></i> Monto inusual: $' . $notificacion['monto'] . '</small>
                      </div>';
            }
            
            echo '<div class="notificacion-tiempo">'.$tiempo.'</div>
                </div>
                <div class="notificacion-acciones">
                    <button class="btn-cerrar-notificacion" onclick="marcarLeida('.$index.')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>'; // ← ESTE ES EL CIERRE DEL DIV PRINCIPAL
        }
        
        echo '
        <div class="notificacion-footer">
            <button class="btn-marcar-todas" onclick="marcarTodasLeidas()">
                <i class="fas fa-check-double"></i> Marcar todas como leídas
            </button>
        </div>';
    }

} catch (Exception $e) {
    echo '
    <div class="notificacion-error">
        <div class="text-center py-3">
            <i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i>
            <h6 class="text-danger">Error al cargar notificaciones</h6>
            <small class="text-muted">Intenta nuevamente</small>
        </div>
    </div>';
}
?>