<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_usuario = $_POST['id_usuario'] ?? null;
$id_estudiante = $_POST['id_estudiante'] ?? null;

if (!$id_usuario || !$id_estudiante) {
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
    require_once __DIR__ . '/../modelos/generar_notificaciones_estudiantes.php';

    $db = mainModel::conectar();
    $modelo = new NotificacionModelo($db);

    $notificaciones_reales = $modelo->generarNotificacionesEstudiante($id_usuario, $id_estudiante);
    
    if (empty($notificaciones_reales)) {
        echo '
        <div class="notificacion-vacia">
            <div class="text-center py-4">
                <i class="fas fa-bell-slash text-muted fa-2x mb-3"></i>
                <h6 class="text-muted">No tienes notificaciones</h6>
                <small class="text-muted">Cuando tengas notificaciones, aparecerán aquí</small>
            </div>
        </div>';
    } else {
        foreach ($notificaciones_reales as $index => $notificacion) {
            $tiempo = tiempo_transcurrido($notificacion['fecha_creacion']);
            $icono = $notificacion['tipo'] == 'pago' ? 'fa-money-bill-wave' : 'fa-calendar-alt';
            $color = $notificacion['tipo'] == 'pago' ? 'text-warning' : 'text-info';
            
            echo '
            <div class="notificacion-item" id="notif-'.$index.'">
                <div class="notificacion-icono '.$color.'">
                    <i class="fas '.$icono.'"></i>
                </div>
                <div class="notificacion-contenido">
                    <div class="notificacion-titulo">'.$notificacion['motivo'].'</div>
                    <div class="notificacion-descripcion">'.$notificacion['referencia'].'</div>
                    <div class="notificacion-tiempo">'.$tiempo.'</div>
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

<script>
function marcarLeida(index) {
    $('#notif-' + index).fadeTo(200, 0.3);
}

function marcarTodasLeidas() {
    $('.notificacion-item').fadeTo(200, 0.3);
}
</script>