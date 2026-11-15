<?php
$id_usuario = $_SESSION['id_usuario'] ?? $_SESSION['id_spm'] ?? null;
$id_estudiante = $_SESSION['id_estudiante'] ?? null;
$cargo = $_SESSION['cargo_spm'] ?? null;

// Determinar qué notificaciones mostrar según el rol
$mostrar_notificaciones = false;
$controlador_notificaciones = '';

switch ($cargo) {
    case 1: // Admin
        $mostrar_notificaciones = false;// era true pero lo cambie pa q no joda el pdo, olvier arreglalo
       // $controlador_notificaciones = 'Cron_admin_controlador.php';
        break;
  /*  case 2: // Supervisor de cursos
        $mostrar_notificaciones = true;
        $controlador_notificaciones = 'Cron_supervisor_controlador.php';
        break;
    case 3: // Administración de pagos
        $mostrar_notificaciones = true;
        $controlador_notificaciones = 'Cron_pagos_controlador.php';
        break;
   	case 4: // Control de estudio
        $mostrar_notificaciones = true;
        $controlador_notificaciones = 'Cron_control_estudio_controlador.php';
        break;
    case 5: // Estudiante
        $mostrar_notificaciones = ($id_usuario && $id_estudiante);
        $controlador_notificaciones = 'Cron_estudiante_controlador.php';
        break;
        */
    // Puedes agregar más roles aquí
    default:
        $mostrar_notificaciones = false;
}
?>

<nav class="full-box navbar-info">
    <?php if($mostrar_notificaciones): ?>
    <div class="notificaciones-wrapper">
        <button id="btn-notificaciones" class="btn-notificaciones">
            <i class="fas fa-bell"></i>
            <span id="contador-notificaciones" class="contador-notificaciones">0</span>
        </button>
        <div id="lista-notificaciones" class="dropdown-notificaciones">
            <div class="dropdown-header">
                <h5>Notificaciones</h5>
                <span id="contador-dropdown">0 nuevas</span>
            </div>
            <div class="dropdown-body">
                <div class="cargando-notificaciones">
                    <div class="spinner"></div>
                    <p>Cargando notificaciones...</p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>
    <button type="button" id="btn-dark-mode" class="btn btn-sm btn-outline-light" title="Modo oscuro/claro">
        <i class="fas fa-moon" style="color:orange;" id="icon-dark-mode"></i>
    </button>
    <a href="#" class="float-left show-nav-lateral">
        <i class="fas fa-exchange-alt"></i>
    </a>
    
    <a href="#" class="btn-exit-system">
        <i class="fas fa-power-off"></i>
    </a>
</nav>

<!--script>
console.log('[CEPROCA][NavBar] renderizado', {
    cargo: <php echo json_encode($cargo); ?>,
    mostrarNotificaciones: <php echo $mostrar_notificaciones ? 'true' : 'false'; ?>,
    botonMenu: document.querySelectorAll('.show-nav-lateral').length,
    botonSalida: document.querySelectorAll('.btn-exit-system').length
});
</script-->

<?php if($mostrar_notificaciones): ?>
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script>
$(document).ready(function() {
    // Cargar notificaciones al hacer clic
    $('#btn-notificaciones').on('click', function (e) {
        e.stopPropagation();
        
        var $dropdown = $('#lista-notificaciones');
        var $body = $dropdown.find('.dropdown-body');
        
        if ($dropdown.hasClass('show')) {
            $dropdown.removeClass('show');
            return;
        }
        
        $dropdown.addClass('show');
        $body.html('<div class="cargando-notificaciones"><div class="spinner"></div><p>Cargando notificaciones...</p></div>');
        
        var datos = {
            //id_usuario: <= $id_usuario ?>
            id_usuario: <?php echo json_encode($id_usuario); ?>
         
        };
        
        // Agregar id_estudiante solo si es estudiante
        <?php if($cargo == 5): ?>
        //datos.id_estudiante = <= $id_estudiante ?>;
        datos.id_estudiante = <?php echo json_encode($id_estudiante); ?>;
        <?php endif; ?>
        
        $.post('<?php echo SERVERURL; ?>controladores/<?= $controlador_notificaciones ?>', 
            datos, 
            function (html) {
                $body.html(html);
                actualizarContadores();
            }
        ).fail(function(xhr, status, error) {
            $body.html('<div class="notificacion-error"><div class="text-center py-3"><i class="fas fa-exclamation-triangle text-danger fa-2x mb-2"></i><h6 class="text-danger">Error al cargar</h6><small class="text-muted">Intenta nuevamente</small></div></div>');
        });
    });
    
    // Cerrar al hacer clic fuera
    $(document).on('click', function(e) {
        if (!$(e.target).closest('.notificaciones-wrapper').length) {
            $('#lista-notificaciones').removeClass('show');
        }
    });
});

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
            $('#lista-notificaciones .dropdown-body').html('<div class="notificacion-vacia"><div class="text-center py-4"><i class="fas fa-bell-slash text-muted fa-2x mb-3"></i><h6 class="text-muted">No tienes notificaciones</h6><small class="text-muted">Todo está bajo control</small></div></div>');
        }
    }
}
</script>

<style>
/* (Mantener los mismos estilos del código anterior) */
.notificaciones-wrapper {
    position: relative;
    display: inline-block;
}

.btn-notificaciones {
    background: none;
    border: none;
    color: #253556;
    font-size: 1.2rem;
    padding: 0.5rem;
    position: relative;
    cursor: pointer;
    transition: all 0.3s ease;
}

.btn-notificaciones:hover {
    color: #dd2938;
    transform: scale(1.1);
}

.contador-notificaciones {
    position: absolute;
    top: 0;
    right: 0;
    background: #dc3545;
    color: white;
    border-radius: 50%;
    width: 18px;
    height: 18px;
    font-size: 0.7rem;
    display: flex;
    align-items: center;
    justify-content: center;
    display: none;
}

.dropdown-notificaciones {
    position: absolute;
    top: 100%;
    right: 0;
    width: 420px; /* Un poco más ancho para más información */
    background: white;
    border-radius: 10px;
    box-shadow: 0 5px 25px rgba(0,0,0,0.15);
    border: 1px solid #e0e0e0;
    z-index: 1000;
    display: none;
    max-height: 500px;
    overflow: hidden;
}

.dropdown-notificaciones.show {
    display: block;
    animation: fadeInUp 0.3s ease;
}

@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(10px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.dropdown-header {
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #e0e0e0;
    background: #f8f9fa;
    display: flex;
    justify-content: space-between;
    align-items: center;
}

.dropdown-header h5 {
    margin: 0;
    font-size: 1rem;
    font-weight: 600;
}

#contador-dropdown {
    font-size: 0.8rem;
    color: #6c757d;
    font-weight: 500;
}

.dropdown-body {
    max-height: 400px;
    overflow-y: auto;
}

/* ITEM DE NOTIFICACIÓN */
.notificacion-item {
    display: flex;
    align-items: flex-start;
    padding: 1rem 1.5rem;
    border-bottom: 1px solid #f0f0f0;
    transition: background-color 0.2s ease;
    cursor: pointer;
}

.notificacion-item:hover {
    background-color: #f8f9fa;
}

.notificacion-icono {
    font-size: 1.2rem;
    margin-right: 1rem;
    margin-top: 0.2rem;
    flex-shrink: 0;
}

.notificacion-contenido {
    flex-grow: 1;
    min-width: 0;
}

.notificacion-titulo {
    font-weight: 600;
    color: #333;
    margin-bottom: 0.2rem;
    font-size: 0.9rem;
}

.notificacion-descripcion {
    color: #666;
    font-size: 0.85rem;
    margin-bottom: 0.3rem;
    line-height: 1.3;
}

.notificacion-detalles {
    background: #f8f9fa;
    padding: 0.5rem;
    border-radius: 4px;
    margin: 0.5rem 0;
    font-size: 0.8rem;
}

.notificacion-tiempo {
    color: #999;
    font-size: 0.75rem;
}

.notificacion-acciones {
    margin-left: 0.5rem;
}

.btn-cerrar-notificacion {
    background: none;
    border: none;
    color: #ccc;
    cursor: pointer;
    padding: 0.2rem;
    border-radius: 3px;
    transition: all 0.2s ease;
}

.btn-cerrar-notificacion:hover {
    color: #666;
    background: #f0f0f0;
}

/* ESTADOS ESPECIALES */
.notificacion-vacia,
.notificacion-error {
    padding: 2rem 1.5rem;
    text-align: center;
}

.cargando-notificaciones {
    padding: 2rem 1.5rem;
    text-align: center;
}

.spinner {
    border: 2px solid #f3f3f3;
    border-top: 2px solid #007bff;
    border-radius: 50%;
    width: 30px;
    height: 30px;
    animation: spin 1s linear infinite;
    margin: 0 auto 1rem;
}

@keyframes spin {
    0% { transform: rotate(0deg); }
    100% { transform: rotate(360deg); }
}

.notificacion-footer {
    padding: 1rem 1.5rem;
    border-top: 1px solid #e0e0e0;
    text-align: center;
}

.btn-marcar-todas {
    background: none;
    border: none;
    color: #007bff;
    cursor: pointer;
    font-size: 0.85rem;
    transition: color 0.2s ease;
}

.btn-marcar-todas:hover {
    color: #0056b3;
}

/* SCROLLBAR PERSONALIZADO */
.dropdown-body::-webkit-scrollbar {
    width: 6px;
}

.dropdown-body::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 3px;
}

.dropdown-body::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 3px;
}

.dropdown-body::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
    
.priority-critical {
    border-left: 4px solid #dc3545;
    background: rgba(220, 53, 69, 0.05);
}

.priority-high {
    border-left: 4px solid #fd7e14;
    background: rgba(253, 126, 20, 0.05);
}

.priority-medium {
    border-left: 4px solid #ffc107;
}

.priority-low {
    border-left: 4px solid #28a745;
}

.notificacion-item {
    transition: all 0.3s ease;
}

.notificacion-item:hover {
    transform: translateX(2px);
    box-shadow: 0 2px 8px rgba(0,0,0,0.1);
}
    
</style>
<?php endif; ?>