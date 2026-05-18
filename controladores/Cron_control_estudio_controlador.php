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
    require_once __DIR__ . '/../modelos/generar_notificaciones_control_estudio.php';

    $db = mainModel::conectar();
    $modelo = new NotificacionControlEstudioModelo($db);

    $notificaciones_reales = $modelo->generarNotificacionesControlEstudio($id_usuario);
    
    if (empty($notificaciones_reales)) {
        echo '
        <div class="notificacion-vacia">
            <div class="text-center py-4">
                <i class="fas fa-bell-slash text-muted fa-2x mb-3"></i>
                <h6 class="text-muted">No tienes notificaciones</h6>
                <small class="text-muted">Todo está al día en control de estudio</small>
            </div>
        </div>';
    } else {
        foreach ($notificaciones_reales as $index => $notificacion) {
            $tiempo = tiempo_transcurrido($notificacion['fecha_creacion']);
            
            // Definir icono y color según el tipo
            switch ($notificacion['tipo']) {
                case 'puntajes_pendientes':
                    $icono = 'fa-clipboard-list';
                    $color = 'text-warning';
                    $badge = 'bg-warning';
                    break;
                case 'seccion_sin_evaluacion':
                    $icono = 'fa-chalkboard-teacher';
                    $color = 'text-info';
                    $badge = 'bg-info';
                    break;
                case 'estudiante_sin_evaluacion':
                    $icono = 'fa-user-graduate';
                    $color = 'text-danger';
                    $badge = 'bg-danger';
                    break;
                case 'evaluacion_proxima':
                    $icono = 'fa-calendar-alt';
                    $color = 'text-primary';
                    $badge = 'bg-primary';
                    break;
                case 'bajo_rendimiento':
                    $icono = 'fa-exclamation-triangle';
                    $color = 'text-danger';
                    $badge = 'bg-danger';
                    break;
                case 'resumen_academico':
                    $icono = 'fa-chart-bar';
                    $color = 'text-success';
                    $badge = 'bg-success';
                    break;
                default:
                    $icono = 'fa-info-circle';
                    $color = 'text-secondary';
                    $badge = 'bg-secondary';
            }
            
            echo '
            <div class="notificacion-item" id="notif-'.$index.'">
                <div class="notificacion-icono '.$color.'">
                    <i class="fas '.$icono.'"></i>
                </div>
                <div class="notificacion-contenido">
                    <div class="d-flex justify-content-between align-items-start">
                        <div class="notificacion-titulo">'.$notificacion['motivo'].'</div>
                        <span class="badge '.$badge.' badge-sm">'.$notificacion['tipo'].'</span>
                    </div>
                    <div class="notificacion-descripcion">'.$notificacion['referencia'].'</div>';
            
            // Mostrar información adicional para puntajes pendientes
            if ($notificacion['tipo'] == 'puntajes_pendientes') {
                echo '<div class="notificacion-detalles">
                        <small class="text-muted">Fecha evaluación: '.date('d/m/Y', strtotime($notificacion['fecha_evaluacion'])).'</small><br>
                        <small class="text-warning">Puntajes faltantes: '.$notificacion['puntajes_faltantes'].'</small>
                      </div>';
            }
            
            // Mostrar información adicional para evaluaciones próximas
            if ($notificacion['tipo'] == 'evaluacion_proxima') {
                $fecha_formateada = date('d/m/Y', strtotime($notificacion['fecha_evaluacion']));
                echo '<div class="notificacion-detalles">
                        <small class="text-muted">Programada para: '.$fecha_formateada.'</small>
                      </div>';
            }
            
            // Mostrar información adicional para bajo rendimiento
            if ($notificacion['tipo'] == 'bajo_rendimiento') {
                echo '<div class="notificacion-detalles">
                        <small class="text-danger">Nota final: '.$notificacion['nota_final'].'</small>
                      </div>';
            }
            
            // Mostrar información adicional para resumen académico
            if ($notificacion['tipo'] == 'resumen_academico') {
                echo '<div class="notificacion-detalles">
                        <small class="text-success">Estudiantes activos: '.$notificacion['total_estudiantes'].'</small><br>
                        <small class="text-info">Secciones activas: '.$notificacion['total_secciones'].'</small><br>
                        <small class="text-warning">Bajo rendimiento: '.$notificacion['estudiantes_bajo_rendimiento'].'</small>
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