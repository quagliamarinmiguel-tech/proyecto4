<?php
$id_usuario = $_SESSION['id_usuario'] ?? null;
?>

<div class="mx-auto" style="width: 90%;">
    <style>
        .chart-wrapper {
            position: relative;
            height: 300px; /* Altura fija para las gráficas */
            width: 100%;
            margin-bottom: 1rem;
        }
        
        .chart-container {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        
        /* Asegurar que las tarjetas tengan altura consistente */
        .card {
            min-height: 400px;
            display: flex;
            flex-direction: column;
        }
        
        .card-body {
            flex: 1;
            display: flex;
            flex-direction: column;
        }
        
        .card-body canvas {
            flex: 1;
            max-height: 100% !important;
        }
    </style>

    <h2 class="text-center mb-4">🎓 Mi Dashboard Estudiantil</h2>
    
    <!-- Información del usuario -->
    <div class="alert alert-info text-center">
        <small>
            ID de usuario: <strong><?php echo $id_usuario ?? 'No disponible'; ?></strong> | 
            <a href="#" onclick="probarControlador()" class="text-dark"><u>Probar Controlador</u></a>
        </small>
    </div>

    <!-- Fila 1: Resumen rápido -->
    <div class="row mb-4">
        <div class="col-md-4 mb-3">
            <div class="card border-primary shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Cursos Totales</h5>
                    <h3 id="total-cursos" class="text-primary">0</h3>
                    <small class="text-muted">Cursos inscritos</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card border-success shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Cursos Aprobados</h5>
                    <h3 id="cursos-aprobados" class="text-success">0</h3>
                    <small class="text-muted">Cursos completados</small>
                </div>
            </div>
        </div>
        
        <div class="col-md-4 mb-3">
            <div class="card border-info shadow h-100">
                <div class="card-body text-center">
                    <h5 class="card-title">Pagos Realizados</h5>
                    <h3 id="pagos-realizados" class="text-info">Bs. 0</h3>
                    <small class="text-muted">Total pagado</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje cuando no hay datos -->
    <div id="mensaje-sin-datos" class="alert alert-warning text-center" style="display: none;">
        <h5>📝 Aún no tienes cursos inscritos</h5>
        <p class="mb-0">Una vez que te inscribas en cursos, podrás ver tu progreso académico y estado de pagos aquí.</p>
    </div>

    <!-- Fila 2: Gráficas principales (ocultas inicialmente) -->
    <div id="contenedor-graficas" style="display: none;">
        <div class="row mb-4">
            <div class="col-md-6 mb-3">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title text-center">📊 Mi Progreso Académico</h5>
                        <div class="chart-wrapper">
                            <canvas id="graficoProgreso"></canvas>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6 mb-3">
                <div class="card shadow h-100">
                    <div class="card-body">
                        <h5 class="card-title text-center">💰 Estado de Mis Pagos</h5>
                        <div class="chart-wrapper">
                            <canvas id="graficoPagos"></canvas>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mensaje de estado -->
    <div id="mensaje-estado" class="alert alert-info text-center">
        Cargando tus datos...
    </div>

    <!-- Botones de control -->
    <div class="text-center mt-4">
        <button class="btn btn-warning btn-sm me-2" onclick="usarDatosPrueba()">
            <i class="fas fa-vial"></i> Ver Datos de Prueba
        </button>
        <button class="btn btn-outline-primary btn-sm" onclick="cargarDashboardEstudiante()">
            <i class="fas fa-sync-alt"></i> Recargar Datos Reales
        </button>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
// Configuración
const USER_ID = <?php echo $id_usuario ?? 0; ?>;
const BASE_URL = '../controladores';

// Variables para almacenar las instancias de gráficas
let chartProgreso = null;
let chartPagos = null;

// Función principal para cargar datos
async function cargarDashboardEstudiante() {
    const mensaje = document.getElementById('mensaje-estado');
    const mensajeSinDatos = document.getElementById('mensaje-sin-datos');
    const contenedorGraficas = document.getElementById('contenedor-graficas');
    
    try {
        if (!USER_ID) {
            throw new Error('No hay ID de usuario disponible. Recarga la página.');
        }

        mostrarMensaje('Cargando tus datos...', 'info');
        ocultarElemento(mensajeSinDatos);
        ocultarElemento(contenedorGraficas);
        
        const params = new URLSearchParams({ user_id: USER_ID });
        const url = `${BASE_URL}/DashboardEstudianteController.php?${params}`;
        
        console.log('Solicitando datos a:', url);
        
        const response = await fetch(url);
        
        if (!response.ok) {
            throw new Error(`Error del servidor: ${response.status}`);
        }
        
        const data = await response.json();
        console.log('Datos recibidos:', data);
        
        if (!data.success) {
            throw new Error(data.error || 'Error al cargar los datos');
        }

        // Verificar si hay datos
        if (data.progreso.total === 0 && data.pagos.pagado === 0 && data.pagos.faltante === 0) {
            mostrarElemento(mensajeSinDatos);
            mostrarMensaje('✅ Datos cargados - No tienes cursos inscritos aún', 'info');
        } else {
            mostrarElemento(contenedorGraficas);
            mostrarMensaje('✅ Datos cargados correctamente', 'success');
            // Pequeño delay para asegurar que el contenedor esté visible
            setTimeout(() => {
                actualizarDashboard(data.progreso, data.pagos);
            }, 100);
        }

        setTimeout(() => {
            ocultarElemento(mensaje);
        }, 3000);

    } catch (error) {
        console.error('Error:', error);
        mostrarMensaje(`❌ Error: ${error.message}`, 'danger');
    }
}

// Función para usar datos de prueba
function usarDatosPrueba() {
    const datosPrueba = {
        progreso: {
            aprobadas: 3,
            pendientes: 2,
            total: 5
        },
        pagos: {
            pagado: 1500,
            faltante: 500
        }
    };
    
    const mensajeSinDatos = document.getElementById('mensaje-sin-datos');
    const contenedorGraficas = document.getElementById('contenedor-graficas');
    
    ocultarElemento(mensajeSinDatos);
    mostrarElemento(contenedorGraficas);
    mostrarMensaje('📊 Mostrando datos de prueba', 'warning');
    
    setTimeout(() => {
        document.getElementById('mensaje-estado').style.display = 'none';
    }, 2000);
    
    // Pequeño delay para asegurar que el contenedor esté visible
    setTimeout(() => {
        actualizarDashboard(datosPrueba.progreso, datosPrueba.pagos);
    }, 100);
}

// Actualizar toda la interfaz
function actualizarDashboard(progreso, pagos) {
    actualizarResumen(progreso, pagos);
    crearGraficas(progreso, pagos);
}

// Actualizar números del resumen
function actualizarResumen(progreso, pagos) {
    document.getElementById('total-cursos').textContent = progreso.total;
    document.getElementById('cursos-aprobados').textContent = progreso.aprobadas;
    document.getElementById('pagos-realizados').textContent = 'Bs. ' + pagos.pagado.toLocaleString();
}

// Crear gráficas
function crearGraficas(progreso, pagos) {
    crearGraficoProgreso(progreso);
    crearGraficoPagos(pagos);
}

// Gráfico de progreso académico - CON TAMAÑO FIJO
function crearGraficoProgreso(progreso) {
    const canvas = document.getElementById('graficoProgreso');
    const ctx = canvas.getContext('2d');
    
    // Limpiar canvas y establecer tamaño fijo
    canvas.width = canvas.parentElement.offsetWidth;
    canvas.height = canvas.parentElement.offsetHeight;
    
    // Destruir gráfica anterior si existe
    if (chartProgreso) {
        chartProgreso.destroy();
    }
    
    // Si no hay datos, mostrar mensaje en el canvas
    if (progreso.total === 0) {
        mostrarMensajeEnCanvas('graficoProgreso', 'No hay datos de cursos');
        return;
    }
    
    chartProgreso = new Chart(ctx, {
        type: 'doughnut',
        data: {
            labels: ['✅ Aprobados', '📚 En Progreso'],
            datasets: [{
                data: [progreso.aprobadas, progreso.pendientes],
                backgroundColor: ['#28a745', '#ffc107'],
                borderColor: ['#1e7e34', '#e0a800'],
                borderWidth: 2,
                hoverOffset: 10
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // IMPORTANTE: No mantener aspecto ratio
            layout: {
                padding: {
                    top: 10,
                    bottom: 10,
                    left: 10,
                    right: 10
                }
            },
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: {
                        padding: 15,
                        usePointStyle: true,
                        boxWidth: 10,
                        font: {
                            size: 12
                        }
                    }
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            const label = context.label || '';
                            const value = context.raw || 0;
                            const total = context.dataset.data.reduce((a, b) => a + b, 0);
                            const percentage = total > 0 ? Math.round((value / total) * 100) : 0;
                            return `${label}: ${value} curso${value !== 1 ? 's' : ''} (${percentage}%)`;
                        }
                    }
                }
            },
            animation: {
                animateScale: true,
                animateRotate: true,
                duration: 800
            },
            // Tamaño máximo para evitar expansión infinita
            aspectRatio: 1.5
        }
    });
}

// Gráfico de pagos - CON TAMAÑO FIJO
function crearGraficoPagos(pagos) {
    const canvas = document.getElementById('graficoPagos');
    const ctx = canvas.getContext('2d');
    
    // Limpiar canvas y establecer tamaño fijo
    canvas.width = canvas.parentElement.offsetWidth;
    canvas.height = canvas.parentElement.offsetHeight;
    
    // Destruir gráfica anterior si existe
    if (chartPagos) {
        chartPagos.destroy();
    }
    
    // Si no hay datos, mostrar mensaje en el canvas
    if (pagos.pagado === 0 && pagos.faltante === 0) {
        mostrarMensajeEnCanvas('graficoPagos', 'No hay datos de pagos');
        return;
    }
    
    chartPagos = new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['💰 Pagado', '💳 Pendiente'],
            datasets: [{
                label: 'Monto en Bolívares',
                data: [pagos.pagado, pagos.faltante],
                backgroundColor: ['#007bff', '#dc3545'],
                borderColor: ['#0056b3', '#c82333'],
                borderWidth: 1,
                borderRadius: 5,
                barPercentage: 0.6, // Controlar ancho de barras
                categoryPercentage: 0.8
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false, // IMPORTANTE: No mantener aspecto ratio
            layout: {
                padding: {
                    top: 10,
                    bottom: 20,
                    left: 10,
                    right: 10
                }
            },
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return 'Bs. ' + value.toLocaleString();
                        },
                        font: {
                            size: 11
                        }
                    },
                    title: {
                        display: true,
                        text: 'Monto (Bs.)',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        drawBorder: false
                    }
                },
                x: {
                    title: {
                        display: true,
                        text: 'Estado de Pagos',
                        font: {
                            size: 12
                        }
                    },
                    grid: {
                        display: false
                    },
                    ticks: {
                        font: {
                            size: 11
                        }
                    }
                }
            },
            plugins: {
                legend: {
                    display: false
                },
                tooltip: {
                    callbacks: {
                        label: function(context) {
                            return `Monto: Bs. ${context.raw.toLocaleString()}`;
                        }
                    }
                }
            },
            animation: {
                duration: 800,
                easing: 'easeInOutQuart'
            },
            // Tamaño máximo para evitar expansión infinita
            aspectRatio: 1.5
        }
    });
}

// Mostrar mensaje en canvas
function mostrarMensajeEnCanvas(canvasId, mensaje) {
    const canvas = document.getElementById(canvasId);
    const ctx = canvas.getContext('2d');
    
    // Establecer tamaño fijo
    canvas.width = canvas.parentElement.offsetWidth;
    canvas.height = canvas.parentElement.offsetHeight;
    
    ctx.clearRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#f8f9fa';
    ctx.fillRect(0, 0, canvas.width, canvas.height);
    ctx.fillStyle = '#6c757d';
    ctx.textAlign = 'center';
    ctx.textBaseline = 'middle';
    ctx.font = '14px Arial';
    ctx.fillText(mensaje, canvas.width / 2, canvas.height / 2);
}

// Mostrar mensajes de estado
function mostrarMensaje(texto, tipo) {
    const mensaje = document.getElementById('mensaje-estado');
    mostrarElemento(mensaje);
    mensaje.className = `alert alert-${tipo} text-center`;
    mensaje.innerHTML = texto;
}

// Utilidades para mostrar/ocultar elementos
function mostrarElemento(elemento) {
    elemento.style.display = 'block';
}

function ocultarElemento(elemento) {
    elemento.style.display = 'none';
}

// Función para probar el controlador manualmente
function probarControlador() {
    const params = new URLSearchParams({ user_id: USER_ID });
    const url = `${BASE_URL}/DashboardEstudianteController.php?${params}`;
    
    window.open(url, '_blank');
}

// Redimensionar gráficas cuando cambie el tamaño de la ventana
window.addEventListener('resize', function() {
    if (chartProgreso || chartPagos) {
        console.log('Redimensionando gráficas...');
        // Recargar datos para recrear gráficas con nuevo tamaño
        cargarDashboardEstudiante();
    }
});

// Inicializar cuando la página cargue
document.addEventListener('DOMContentLoaded', function() {
    console.log('Dashboard estudiantil iniciado');
    console.log('USER_ID:', USER_ID);
    
    cargarDashboardEstudiante();
});

// Recargar automáticamente cada 60 segundos
setInterval(() => {
    console.log('Actualización automática de datos...');
    cargarDashboardEstudiante();
}, 60000);
</script>