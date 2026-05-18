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
    require_once __DIR__ . '/../modelos/generar_notificaciones_supervisor.php';

    $db = mainModel::conectar();
    $modelo = new NotificacionSupervisorModelo($db);

    $notificaciones_reales = $modelo->generarNotificacionesSupervisor($id_usuario);
    
    if (empty($notificaciones_reales)) {
        echo '
        <div class="notificacion-vacia">
            <div class="text-center py-4">
                <i class="fas fa-bell-slash text-muted fa-2x mb-3"></i>
                <h6 class="text-muted">No tienes notificaciones</h6>
                <small class="text-muted">Todo está bajo control</small>
            </div>
        </div>';
    } else {
        foreach ($notificaciones_reales as $index => $notificacion) {
            $tiempo = tiempo_transcurrido($notificacion['fecha_creacion']);
            
            // Definir icono y color según el tipo
            switch ($notificacion['tipo']) {
                case 'cohorte':
                    $icono = 'fa-calendar-times';
                    $color = 'text-danger';
                    break;
                case 'baja_inscripcion':
                    $icono = 'fa-chart-line';
                    $color = 'text-warning';
                    break;
                case 'cohorte_nuevo':
                    $icono = 'fa-calendar-check';
                    $color = 'text-success';
                    break;
                default:
                    $icono = 'fa-info-circle';
                    $color = 'text-info';
            }
            
            echo '
            <div class="notificacion-item" id="notif-'.$index.'">
                <div class="notificacion-icono '.$color.'">
                    <i class="fas '.$icono.'"></i>
                </div>
                <div class="notificacion-contenido">
                    <div class="notificacion-titulo">'.$notificacion['motivo'].'</div>
                    <div class="notificacion-descripcion">'.$notificacion['referencia'].'</div>';
            
            // Mostrar información adicional para cohortes
            if ($notificacion['tipo'] == 'cohorte' && !empty($notificacion['cursos_asociados'])) {
                echo '<div class="notificacion-detalles">';
                echo '<small class="text-muted">Cursos asociados:</small>';
                foreach ($notificacion['cursos_asociados'] as $curso) {
                    echo '<br><small class="text-muted">• '.$curso['nombre_curso'].' ('.$curso['inscritos'].'/'.$curso['oferta'].' inscritos)</small>';
                }
                echo '</div>';
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