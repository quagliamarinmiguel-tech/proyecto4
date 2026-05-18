<?php
session_start();
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

$id_usuario = $_POST['id_usuario'] ?? null;
$rol = $_POST['rol'] ?? null;
$id_estudiante = $_POST['id_estudiante'] ?? null;

if (!$id_usuario || !$rol) {
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
    require_once __DIR__ . '/../modelos/NotificacionGeneralModelo.php';

    $db = mainModel::conectar();
    $modelo = new NotificacionGeneralModelo($db);

    $notificaciones = [];

    switch ((int)$rol) {
        case 1: // Admin
            $notificaciones = $modelo->generarNotificacionesAdmin($id_usuario);
            break;
        case 2: // Control de estudio
            $notificaciones = $modelo->generarNotificacionesControlEstudio($id_usuario);
            break;
        case 3: // Administrador de pagos
            $notificaciones = $modelo->generarNotificacionesAdminPagos($id_usuario);
            break;
        case 4: // Supervisor de cursos
            $notificaciones = $modelo->generarNotificacionesSupervisorCursos($id_usuario);
            break;
        case 5: // Estudiante
            if (!$id_estudiante) {
                throw new Exception("Falta id_estudiante para rol estudiante");
            }
            $notificaciones = $modelo->generarNotificacionesEstudiante($id_usuario, $id_estudiante);
            break;
        default:
            throw new Exception("Rol no reconocido: $rol");
    }

    // Renderizar notificaciones
    if (empty($notificaciones)) {
        echo '
        <div class="notificacion-vacia">
            <div class="text-center py-4">
                <i class="fas fa-bell-slash text-muted fa-2x mb-3"></i>
                <h6 class="text-muted">No tienes notificaciones</h6>
                <small class="text-muted">Cuando tengas notificaciones, aparecerán aquí</small>
            </div>
        </div>';
    } else {
        foreach ($notificaciones as $index => $notificacion) {
            $tiempo = tiempo_transcurrido($notificacion['fecha_creacion']);
            $icono = obtenerIconoPorTipo($notificacion['tipo']);
            $color = obtenerColorPorTipo($notificacion['tipo']);
            
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
            <small class="text-muted">'.$e->getMessage().'</small>
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

function obtenerIconoPorTipo($tipo) {
    $iconos = [
        'pago' => 'fa-money-bill-wave',
        'curso' => 'fa-calendar-alt',
        'academico' => 'fa-graduation-cap',
        'financiero' => 'fa-chart-line'
    ];
    return $iconos[$tipo] ?? 'fa-bell';
}

function obtenerColorPorTipo($tipo) {
    $colores = [
        'pago' => 'text-warning',
        'curso' => 'text-info',
        'academico' => 'text-success',
        'financiero' => 'text-danger'
    ];
    return $colores[$tipo] ?? 'text-primary';
}
?>

<script>
function marcarLeida(index) {
    $('#notif-' + index).fadeOut(300, function() {
        $(this).remove();
        actualizarContadores();
    });
}

function marcarTodasLeidas() {
    $('.notificacion-item').fadeOut(300, function() {
        $(this).remove();
        actualizarContadores();
    });
}

function actualizarContadores() {
    var contador = $('.notificacion-item').length;
    var $badge = $('#contador-notificaciones');
    var $dropdownCounter = $('#contador-dropdown');
    
    if (contador > 0) {
        $badge.text(contador).show();
        $dropdownCounter.text(contador + ' nuevas');
    } else {
        $badge.hide();
        $dropdownCounter.text('0 nuevas');
        
        // Mostrar estado vacío
        if ($('#lista-notificaciones .dropdown-body .notificacion-item').length === 0) {
            $('#lista-notificaciones .dropdown-body').html('<div class="notificacion-vacia"><div class="text-center py-4"><i class="fas fa-bell-slash text-muted fa-2x mb-3"></i><h6 class="text-muted">No tienes notificaciones</h6><small class="text-muted">Cuando tengas notificaciones, aparecerán aquí</small></div></div>');
        }
    }
}
</script>