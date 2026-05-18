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

try {
    require_once __DIR__ . '/../modelos/mainModel.php';
    require_once __DIR__ . '/../modelos/generar_notificaciones_admin.php';

    $db = mainModel::conectar();
    $modelo = new NotificacionAdminModelo($db);

    $notificaciones_reales = $modelo->generarNotificacionesAdmin($id_usuario);
    
    if (empty($notificaciones_reales)) {
        echo '
        <div class="notificacion-vacia">
            <div class="text-center py-4">
                <i class="fas fa-bell-slash text-muted fa-2x mb-3"></i>
                <h6 class="text-muted">No tienes notificaciones</h6>
                <small class="text-muted">El sistema funciona correctamente</small>
            </div>
        </div>';
    } else {
        foreach ($notificaciones_reales as $index => $notificacion) {
            $tiempo = tiempo_transcurrido($notificacion['fecha_creacion']);
            
            // Definir icono y color según el tipo
            switch ($notificacion['tipo']) {
                case 'cohorte_urgente':
                    $icono = 'fa-calendar-times';
                    $color = 'text-danger';
                    $badge = 'bg-danger';
                    $priority = 'priority-critical';
                    break;
                case 'deuda_significativa':
                    $icono = 'fa-money-bill-wave';
                    $color = 'text-warning';
                    $badge = 'bg-warning';
                    $priority = 'priority-high';
                    break;
                case 'evaluacion_pendiente':
                    $icono = 'fa-clipboard-list';
                    $color = 'text-warning';
                    $badge = 'bg-warning';
                    $priority = 'priority-medium';
                    break;
                case 'curso_baja_inscripcion':
                    $icono = 'fa-chart-line';
                    $color = 'text-danger';
                    $badge = 'bg-danger';
                    $priority = 'priority-medium';
                    break;
                case 'pagos_recientes':
                    $icono = 'fa-cash-register';
                    $color = 'text-success';
                    $badge = 'bg-success';
                    $priority = 'priority-low';
                    break;
                case 'bajo_rendimiento_critico':
                    $icono = 'fa-exclamation-triangle';
                    $color = 'text-danger';
                    $badge = 'bg-danger';
                    $priority = 'priority-high';
                    break;
                case 'seccion_sin_evaluacion':
                    $icono = 'fa-chalkboard-teacher';
                    $color = 'text-info';
                    $badge = 'bg-info';
                    $priority = 'priority-medium';
                    break;
                default:
                    $icono = 'fa-info-circle';
                    $color = 'text-primary';
                    $badge = 'bg-primary';
                    $priority = 'priority-low';
            }
            
            echo '
            <div class="notificacion-item '.$priority.'" id="notif-'.$index.'">
                <div class="notificacion-icono '.$color.'">
                    <i class="fas '.$icono.'"></i>
                </div>
                <div class="notificacion-contenido">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="notificacion-titulo">'.$notificacion['motivo'].'</div>
                        <span class="badge '.$badge.' badge-sm">'.$notificacion['tipo'].'</span>
                    </div>
                    <div class="notificacion-descripcion">'.$notificacion['referencia'].'</div>';
            
            // Información adicional específica por tipo
            if ($notificacion['tipo'] == 'cohorte_urgente') {
                echo '<div class="notificacion-detalles">
                        <small class="text-danger">Finaliza en '.$notificacion['dias_restantes'].' días</small>
                      </div>';
            }
            
            if ($notificacion['tipo'] == 'deuda_significativa') {
                echo '<div class="notificacion-detalles">
                        <small class="text-warning">Monto total: $'.number_format($notificacion['monto_deuda'], 2).'</small>
                      </div>';
            }
            
            if ($notificacion['tipo'] == 'evaluacion_pendiente') {
                echo '<div class="notificacion-detalles">
                        <small class="text-warning">Puntajes faltantes: '.$notificacion['puntajes_faltantes'].'</small>
                      </div>';
            }
            
            if ($notificacion['tipo'] == 'pagos_recientes') {
                echo '<div class="notificacion-detalles">
                        <small class="text-success">Total pagos: '.$notificacion['total_pagos'].'</small><br>
                        <small class="text-success">Monto: $'.number_format($notificacion['monto_total'], 2).'</small>
                      </div>';
            }
            
            if ($notificacion['tipo'] == 'bajo_rendimiento_critico') {
                echo '<div class="notificacion-detalles">
                        <small class="text-danger">Nota final: '.$notificacion['nota_final'].'</small>
                      </div>';
            }
            
            echo '<div class="notificacion-tiempo">'.$tiempo.'</div>
                </div>
                <div class="notificacion-acciones">
                    <button class="btn-cerrar-notificacion" onclick="marcarLeida('.$index.')">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>';
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

function tiempo_transcurrido($fecha) {
    $diferencia = time() - strtotime($fecha);
    
    if ($diferencia < 60) return 'Ahora mismo';
    if ($diferencia < 3600) return 'Hace ' . round($diferencia/60) . ' min';
    if ($diferencia < 86400) return 'Hace ' . round($diferencia/3600) . ' h';
    if ($diferencia < 2592000) return 'Hace ' . round($diferencia/86400) . ' días';
    
    return date('d/m/Y', strtotime($fecha));
}
?>