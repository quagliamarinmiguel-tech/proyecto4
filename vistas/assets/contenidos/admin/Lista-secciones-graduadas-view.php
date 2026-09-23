<?php
// Verificar permisos
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 3 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema de Gestión de Graduados</title>
    <style>
/* ===== ESTILOS GENERALES ===== */
* {
    margin: 0;
    padding: 0;
    box-sizing: border-box;
}

body {
    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
    background-color: #f8f9fa;
    color: #333;
    line-height: 1.6;
}

.container {
    width: 100%;
    max-width: 1400px;
    margin: 0 auto;
    padding: 20px;
}

/* ===== HEADER ===== */
header {
    background: linear-gradient(135deg, #3498db, #2c3e50);
    color: white;
    padding: 20px 0;
    border-radius: 8px;
    margin-bottom: 30px;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.header-content {
    display: flex;
    justify-content: space-between;
    align-items: center;
    padding: 0 20px;
}

.header-content h1 {
    font-size: 28px;
    font-weight: 600;
}

.status-indicator {
    display: flex;
    align-items: center;
    gap: 10px;
}

.status-dot {
    width: 12px;
    height: 12px;
    background-color: #2ecc71;
    border-radius: 50%;
    animation: pulse 2s infinite;
}

@keyframes pulse {
    0% { opacity: 1; }
    50% { opacity: 0.5; }
    100% { opacity: 1; }
}

.status-text {
    font-size: 14px;
}

/* ===== CARD STYLES ===== */
.card {
    background: white;
    border-radius: 8px;
    box-shadow: 0 2px 10px rgba(0,0,0,0.1);
    margin-bottom: 30px;
    overflow: hidden;
}

.card-header {
    padding: 20px;
    border-bottom: 1px solid #e0e0e0;
    background-color: #f8f9fa;
}

.card-header h2 {
    margin: 0;
    color: #2c3e50;
    font-size: 24px;
}

.card-body {
    padding: 20px;
}

/* ===== FILTERS ===== */
.filters {
    display: flex;
    gap: 15px;
    margin-bottom: 20px;
    flex-wrap: wrap;
}

.filter-group {
    display: flex;
    flex-direction: column;
    gap: 5px;
}

.filter-group label {
    font-size: 14px;
    font-weight: 600;
    color: #495057;
}

.filter-group input, .filter-group select {
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
    transition: border-color 0.3s;
}

.filter-group input:focus, .filter-group select:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.btn {
    padding: 8px 16px;
    background-color: #3498db;
    color: white;
    border: none;
    border-radius: 4px;
    cursor: pointer;
    font-size: 14px;
    transition: background-color 0.3s;
    align-self: flex-end;
}

.btn:hover {
    background-color: #2980b9;
}

.btn-success {
    background-color: #28a745;
}

.btn-success:hover {
    background-color: #218838;
}

.btn-danger {
    background-color: #dc3545;
}

.btn-danger:hover {
    background-color: #c82333;
}

.btn-warning {
    background-color: #ffc107;
    color: #212529;
}

.btn-warning:hover {
    background-color: #e0a800;
}

.btn-primary {
    background-color: #007bff;
}

.btn-primary:hover {
    background-color: #0069d9;
}

.btn-secondary {
    background-color: #6c757d;
}

.btn-secondary:hover {
    background-color: #5a6268;
}

.btn-sm {
    padding: 5px 10px;
    font-size: 12px;
}

/* ===== TABLE STYLES ===== */
table {
    width: 100%;
    border-collapse: collapse;
    margin-top: 20px;
    background-color: white;
    box-shadow: 0 1px 3px rgba(0,0,0,0.1);
}

thead {
    background-color: #2c3e50;
    color: white;
}

th, td {
    padding: 12px 15px;
    text-align: left;
    border-bottom: 1px solid #e0e0e0;
}

th {
    font-weight: 600;
    text-transform: uppercase;
    font-size: 14px;
}

tbody tr:hover {
    background-color: #f5f5f5;
}

/* ===== PROGRESS BAR ===== */
.progress-bar-container {
    display: flex;
    align-items: center;
    gap: 10px;
}

.progress-bar {
    width: 100px;
    height: 8px;
    background-color: #e0e0e0;
    border-radius: 4px;
    overflow: hidden;
    flex-shrink: 0;
}

.progress {
    height: 100%;
    border-radius: 4px;
    transition: width 0.3s ease;
}

.progress-complete {
    background-color: #28a745;
}

.progress-high {
    background-color: #17a2b8;
}

.progress-medium {
    background-color: #ffc107;
}

.progress-low {
    background-color: #dc3545;
}

.progress-text {
    font-size: 12px;
    font-weight: bold;
    color: #495057;
    min-width: 40px;
}

/* ===== BADGES ===== */
.badge {
    display: inline-block;
    padding: 5px 10px;
    border-radius: 12px;
    font-size: 12px;
    font-weight: bold;
    text-align: center;
}

.badge-success {
    background-color: #28a745;
    color: white;
}

.badge-warning {
    background-color: #ffc107;
    color: #212529;
}

.badge-danger {
    background-color: #dc3545;
    color: white;
}

.badge-info {
    background-color: #17a2b8;
    color: white;
}

.badge-secondary {
    background-color: #6c757d;
    color: white;
}

/* ===== PAGINATION ===== */
.pagination-container {
    display: flex;
    justify-content: space-between;
    align-items: center;
    margin-top: 15px;
}

.pagination-info {
    color: #6c757d;
    font-size: 14px;
}

.pagination {
    display: flex;
    list-style: none;
    padding: 0;
    margin: 0;
}

.page-item {
    margin: 0 2px;
}

.page-item.active .page-link {
    background-color: #3498db;
    border-color: #3498db;
    color: white;
}

.page-link {
    display: block;
    padding: 6px 12px;
    text-decoration: none;
    color: #3498db;
    border: 1px solid #dee2e6;
    border-radius: 4px;
    font-size: 14px;
    transition: all 0.3s;
}

.page-link:hover {
    color: #2c3e50;
    background-color: #e9ecef;
    border-color: #dee2e6;
}

/* ===== BUTTONS ===== */
.btn-ver-estudiantes {
    background-color: #3498db;
    color: white;
    border: none;
    padding: 8px 12px;
    border-radius: 4px;
    cursor: pointer;
    font-size: 12px;
    display: flex;
    align-items: center;
    gap: 5px;
    transition: background-color 0.3s;
}

.btn-ver-estudiantes:hover {
    background-color: #2980b9;
}

.btn-cargar-codigos, .btn-borrar-codigos, .btn-asignar-codigo {
    display: inline-flex;
    align-items: center;
    gap: 5px;
}

/* ===== STATISTICS CARDS ===== */
.stat-card {
    border-radius: 8px;
    text-align: center;
    box-shadow: 0 2px 4px rgba(0,0,0,0.1);
    padding: 15px;
}

.stat-card h3 {
    margin: 0;
    font-size: 24px;
    font-weight: bold;
}

.stat-card p {
    margin: 5px 0 0 0;
    font-size: 14px;
}

/* ===== MODAL STYLES ===== */
.modal {
    display: none;
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background-color: rgba(0,0,0,0.5);
    justify-content: center;
    align-items: center;
    z-index: 1000;
}

.modal-content {
    background-color: white;
    border-radius: 8px;
    width: 90%;
    max-width: 800px;
    max-height: 90vh;
    overflow-y: auto;
    box-shadow: 0 4px 6px rgba(0,0,0,0.1);
}

.modal-header {
    padding: 15px 20px;
    border-bottom: 1px solid #e0e0e0;
    display: flex;
    justify-content: space-between;
    align-items: center;
    background-color: #f8f9fa;
    border-radius: 8px 8px 0 0;
}

.modal-title {
    margin: 0;
    font-size: 18px;
    font-weight: 600;
    color: #2c3e50;
}

.close {
    font-size: 24px;
    cursor: pointer;
    color: #7f8c8d;
}

.close:hover {
    color: #34495e;
}

.modal-body {
    padding: 20px;
}

/* ===== FORM STYLES ===== */
.form-group {
    margin-bottom: 15px;
}

.form-group label {
    display: block;
    margin-bottom: 5px;
    font-weight: 600;
    color: #495057;
}

.form-control {
    width: 100%;
    padding: 8px 12px;
    border: 1px solid #ced4da;
    border-radius: 4px;
    font-size: 14px;
    box-sizing: border-box;
}

.form-control:focus {
    border-color: #3498db;
    outline: none;
    box-shadow: 0 0 0 2px rgba(52, 152, 219, 0.2);
}

.form-text {
    font-size: 12px;
    color: #6c757d;
}

.text-right {
    text-align: right;
}

.text-center {
    text-align: center;
}

.text-muted {
    color: #6c757d !important;
}

/* ===== LOADING STATES ===== */
.loading {
    text-align: center;
    padding: 20px;
    color: #6c757d;
}

/* ===== ALERTS ===== */
.alert {
    padding: 12px 15px;
    border-radius: 4px;
    margin-bottom: 15px;
}

.alert-info {
    background-color: #d1ecf1;
    color: #0c5460;
    border: 1px solid #bee5eb;
}

.alert-danger {
    background-color: #f8d7da;
    color: #721c24;
    border: 1px solid #f5c6cb;
}

.alert-success {
    background-color: #d4edda;
    color: #155724;
    border: 1px solid #c3e6cb;
}

.alert-warning {
    background-color: #fff3cd;
    color: #856404;
    border: 1px solid #ffeaa7;
}

/* ===== UTILITY CLASSES ===== */
.mb-0 { margin-bottom: 0 !important; }
.mb-2 { margin-bottom: 0.5rem !important; }
.mb-3 { margin-bottom: 1rem !important; }
.mb-4 { margin-bottom: 1.5rem !important; }
.mt-2 { margin-top: 0.5rem !important; }
.mt-3 { margin-top: 1rem !important; }
.mt-4 { margin-top: 1.5rem !important; }
.p-3 { padding: 1rem !important; }
.p-4 { padding: 1.5rem !important; }
.py-4 { padding-top: 1.5rem !important; padding-bottom: 1.5rem !important; }
.rounded { border-radius: 4px !important; }
.bg-white { background-color: white !important; }
.bg-primary { background-color: #007bff !important; color: white; }
.bg-success { background-color: #28a745 !important; color: white; }
.bg-danger { background-color: #dc3545 !important; color: white; }
.bg-warning { background-color: #ffc107 !important; color: #212529; }
.bg-info { background-color: #17a2b8 !important; color: white; }
.bg-light { background-color: #f8f9fa !important; }
.text-white { color: white !important; }
.text-primary { color: #007bff !important; }
.text-success { color: #28a745 !important; }
.text-danger { color: #dc3545 !important; }
.text-warning { color: #ffc107 !important; }
.text-muted { color: #6c757d !important; }
.text-dark { color: #343a40 !important; }
.d-flex { display: flex !important; }
.justify-content-between { justify-content: space-between !important; }
.justify-content-center { justify-content: center !important; }
.justify-content-end { justify-content: flex-end !important; }
.align-items-center { align-items: center !important; }
.flex-wrap { flex-wrap: wrap !important; }
.gap-10 { gap: 10px !important; }

/* ===== RESPONSIVE DESIGN ===== */
@media (max-width: 768px) {
    .container {
        padding: 10px;
    }
    
    .header-content {
        flex-direction: column;
        gap: 10px;
        text-align: center;
    }
    
    .filters {
        flex-direction: column;
    }
    
    .filter-group {
        width: 100%;
    }
    
    .pagination-container {
        flex-direction: column;
        gap: 10px;
    }
    
    table {
        font-size: 14px;
    }
    
    th, td {
        padding: 8px 10px;
    }
    
    .modal-content {
        width: 95%;
        margin: 10px;
    }
    
    .card-body {
        padding: 15px;
    }
}

@media (max-width: 576px) {
    .card-header h2 {
        font-size: 20px;
    }
    
    .header-content h1 {
        font-size: 24px;
    }
    
    table {
        display: block;
        overflow-x: auto;
    }
    
    .btn-ver-estudiantes {
        font-size: 11px;
        padding: 6px 8px;
    }
}

/* ===== ANIMATIONS ===== */
@keyframes fadeIn {
    from { opacity: 0; }
    to { opacity: 1; }
}

.fade-in {
    animation: fadeIn 0.3s ease-in;
}

/* ===== CUSTOM SCROLLBAR ===== */
::-webkit-scrollbar {
    width: 8px;
}

::-webkit-scrollbar-track {
    background: #f1f1f1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb {
    background: #c1c1c1;
    border-radius: 4px;
}

::-webkit-scrollbar-thumb:hover {
    background: #a8a8a8;
}
    </style>
</head>
<body>
    <div class="container">
        <header>
            <div class="header-content">
                <h1>Sistema de Gestión de Graduados</h1>
                <div class="status-indicator">
                    <div class="status-dot"></div>
                    <div class="status-text">Sistema Conectado</div>
                </div>
            </div>
        </header>
        
        <div class="card">
            <div class="card-header">
                <h2>Lista de Graduados - Secciones de Cursos Vigentes</h2>
            </div>
            <div class="card-body">
                <div class="filters">
                    <div class="filter-group">
                        <label for="busqueda">Buscar:</label>
                        <input type="text" id="busqueda" placeholder="Buscar sección o curso...">
                    </div>
                    <div class="filter-group">
                        <label for="registros">Registros por página:</label>
                        <select id="registros">
                            <option value="10">10</option>
                            <option value="25">25</option>
                            <option value="50">50</option>
                            <option value="100">100</option>
                        </select>
                    </div>
                    <div class="filter-group">
                        <label style="visibility: hidden;">Buscar</label>
                        <button class="btn" id="btn-buscar">Buscar</button>
                    </div>
                </div>
                
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>SECCIÓN</th>
                            <th>CURSO</th>
                            <th>TIPO</th>
                            <th>PROFESOR</th>
                            <th>TOTAL EVALUACIONES</th>
                            <th>PORCENTAJE TOTAL</th>
                            <th>ESTUDIANTES APROBADOS</th>
                            <th>ESTUDIANTES REPROBADOS</th>
                            <th>ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="sections-table-body">
                        <tr>
                            <td colspan="10" class="loading">Cargando datos de la base de datos...</td>
                        </tr>
                    </tbody>
                </table>
                
                <!-- Paginador -->
                <nav>
                    <ul class="pagination justify-content-center mt-3" id="paginador">
                    </ul>
                </nav>
            </div>
        </div>
    </div>
    
    <!-- Modal para mostrar estudiantes de la sección -->
    <div id="studentsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title" id="modalTitle">Estudiantes de la Sección</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <div id="studentsContent">
                    <div class="loading">Cargando estudiantes...</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal para asignar código de diploma -->
    <div id="codigoModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h3 class="modal-title">Asignar Código de Diploma</h3>
                <span class="close">&times;</span>
            </div>
            <div class="modal-body">
                <form id="formCodigo">
                    <input type="hidden" id="id_inscripcion_codigo" name="id_inscripcion_codigo">
                    <div class="form-group">
                        <label for="nombre_estudiante_codigo">Estudiante:</label>
                        <input type="text" id="nombre_estudiante_codigo" class="form-control" readonly>
                    </div>
                    <div class="form-group">
                        <label for="codigo_graduado">Código de Diploma:</label>
                        <input type="text" id="codigo_graduado" name="codigo_graduado" class="form-control" required>
                    </div>
                    <div class="form-group text-right">
                        <button type="submit" class="btn btn-success">Guardar Código</button>
                        <button type="button" class="btn btn-secondary" onclick="cerrarModalCodigo()">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/sweetalert2@11/dist/sweetalert2.min.css">
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
// Variables globales
let paginaActual = 1;
let registrosPorPagina = 10;
let busquedaActual = '';
let currentSectionId = null;
let currentSectionName = null;

// Elementos del DOM
const modal = document.getElementById('studentsModal');
const modalCodigo = document.getElementById('codigoModal');
const closeBtns = document.querySelectorAll('.close');
const modalTitle = document.getElementById('modalTitle');
const studentsContent = document.getElementById('studentsContent');
const sectionsTableBody = document.getElementById('sections-table-body');
const paginador = document.getElementById('paginador');
const formCodigo = document.getElementById('formCodigo');
    
function mostrarAlerta(titulo, mensaje, tipo = 'success') {
    return Swal.fire({
        title: titulo,
        text: mensaje,
        icon: tipo,
        confirmButtonText: 'Aceptar',
        confirmButtonColor: '#3498db'
    });
}

// FUNCIÓN NUEVA: Confirmación para acciones críticas
function confirmarAccion(titulo, mensaje, textoConfirmar = 'Sí, continuar') {
    return Swal.fire({
        title: titulo,
        text: mensaje,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#e74c3c',
        cancelButtonColor: '#95a5a6',
        confirmButtonText: textoConfirmar,
        cancelButtonText: 'Cancelar'
    });
}

// Cargar las secciones
function loadSections(pagina = 1, registros = 10, busqueda = '') {
    const formData = new FormData();
    formData.append('accion', 'listar_secciones_graduados');
    formData.append('pagina', pagina);
    formData.append('registros', registros);
    formData.append('busqueda', busqueda);

    const url = '<?php echo SERVERURL; ?>ajax/Lista-secciones-graduadosAjax.php';

    sectionsTableBody.innerHTML = '<tr><td colspan="10" class="loading">Cargando secciones...</td></tr>';

    fetch(url, {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            sectionsTableBody.innerHTML = data.tabla;
            paginador.innerHTML = data.paginador;
            attachViewStudentEvents();
            attachPaginationEvents();
        } else {
            sectionsTableBody.innerHTML = `<tr><td colspan="10" class="text-center text-danger">${data.message}</td></tr>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        sectionsTableBody.innerHTML = '<tr><td colspan="10" class="text-center text-danger">Error al cargar las secciones</td></tr>';
    });
}

// Cargar estudiantes de una sección (APROBADOS Y REPROBADOS) - VERSIÓN ACTUALIZADA
function loadStudents(sectionId, sectionName) {
    const formData = new FormData();
    formData.append('accion', 'obtener_estudiantes_seccion');
    formData.append('id_seccion', sectionId);

    studentsContent.innerHTML = '<div class="loading">Cargando estudiantes...</div>';
    modalTitle.textContent = `Estudiantes - ${sectionName}`;
    modal.style.display = 'flex';

    // Guardar la sección actual
    currentSectionId = sectionId;
    currentSectionName = sectionName;

    fetch('<?php echo SERVERURL; ?>ajax/Lista-secciones-graduadosAjax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            let html = '';
            
            // Botones de gestión de códigos (solo si hay aprobados)
            if (data.boton_codigos) {
                html += data.boton_codigos;
            }

            // Sección de Estudiantes APROBADOS
            html += `
                <div class="card mb-4">
                    <div class="card-header bg-success text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-graduation-cap"></i>
                            Estudiantes Aprobados - Total: ${data.count_aprobados}
                        </h4>
                    </div>
                    <div class="card-body">
                        ${data.tabla_aprobados}
                    </div>
                </div>
            `;

            // Sección de Estudiantes REPROBADOS
            html += `
                <div class="card mb-4">
                    <div class="card-header bg-danger text-white">
                        <h4 class="mb-0">
                            <i class="fas fa-exclamation-triangle"></i>
                            Estudiantes Reprobados - Total: ${data.count_reprobados}
                        </h4>
                    </div>
                    <div class="card-body">
                        ${data.tabla_reprobados}
                    </div>
                </div>
            `;

            studentsContent.innerHTML = html;
            
            // Agregar event listener al botón de cargar códigos si existe
            const btnCargarCodigos = document.querySelector('.btn-cargar-codigos');
            if (btnCargarCodigos) {
                btnCargarCodigos.addEventListener('click', function() {
                    const seccionId = this.getAttribute('data-seccion');
                    const cantidad = this.getAttribute('data-cantidad');
                    mostrarModalCodigos(seccionId, cantidad);
                });
            }

            // Agregar event listeners a los botones de edición individual
            attachEditCodeEvents();
            
            // Agregar event listeners a los botones de borrado
            attachDeleteCodeEvents();
        } else {
            studentsContent.innerHTML = `<div class="alert alert-danger">${data.error}</div>`;
        }
    })
    .catch(error => {
        console.error('Error:', error);
        studentsContent.innerHTML = '<div class="alert alert-danger">Error al cargar los estudiantes</div>';
    });
}

// NUEVA FUNCIÓN: Mostrar modal para cargar códigos MASIVOS
function mostrarModalCodigos(seccionId, cantidadEstudiantes) {
    const modalHTML = `
    <div class="modal" id="modalCodigosMasivos" style="display: flex; background-color: rgba(0,0,0,0.5); position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 1000; justify-content: center; align-items: center;">
        <div class="modal-content" style="background-color: white; border-radius: 8px; width: 500px; max-width: 90%; padding: 0;">
            <div class="modal-header" style="padding: 15px 20px; border-bottom: 1px solid #e0e0e0; display: flex; justify-content: space-between; align-items: center; background-color: #ecf0f1;">
                <h3 class="modal-title" style="font-size: 18px; font-weight: 600; color: #2c3e50;">Generar Códigos de Graduados</h3>
                <span class="close" onclick="cerrarModalCodigosMasivos()" style="font-size: 24px; cursor: pointer; color: #7f8c8d;">&times;</span>
            </div>
            <div class="modal-body" style="padding: 20px;">
                <p>Se generarán códigos para <strong>${cantidadEstudiantes}</strong> estudiantes aprobados.</p>
                <div class="form-group">
                    <label for="numeroBase">Número Base (XXXX):</label>
                    <input type="number" id="numeroBase" class="form-control" 
                           min="0" max="9999" placeholder="Ej: 9956" required
                           style="padding: 8px 12px; border: 1px solid #ddd; border-radius: 4px; font-size: 14px; width: 100%;">
                    <small class="form-text text-muted">
                        Ejemplo: CPS-9956-SNE-G, CPS-9957-SNE-G, etc.
                    </small>
                </div>
                <div class="form-group text-right" style="margin-top: 20px; text-align: right;">
                    <button type="button" class="btn btn-secondary" onclick="cerrarModalCodigosMasivos()" style="background-color: #95a5a6; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer; margin-right: 10px;">Cancelar</button>
                    <button type="button" class="btn btn-primary" id="btnGenerarCodigos" style="background-color: #3498db; color: white; padding: 8px 15px; border: none; border-radius: 4px; cursor: pointer;">Generar Códigos</button>
                </div>
            </div>
        </div>
    </div>
    `;
    
    // Remover modal existente
    const existingModal = document.getElementById('modalCodigosMasivos');
    if (existingModal) {
        existingModal.remove();
    }
    
    document.body.insertAdjacentHTML('beforeend', modalHTML);
    
    // Manejar clic en generar
    document.getElementById('btnGenerarCodigos').addEventListener('click', function() {
        generarCodigosGraduados(seccionId);
    });
}

// NUEVA FUNCIÓN: Cerrar modal de códigos masivos
function cerrarModalCodigosMasivos() {
    const modal = document.getElementById('modalCodigosMasivos');
    if (modal) {
        modal.remove();
    }
}

// NUEVA FUNCIÓN: Generar códigos de graduados MASIVOS
function generarCodigosGraduados(seccionId) {
    const numeroBase = document.getElementById('numeroBase').value;
    
    if (!numeroBase || numeroBase < 0 || numeroBase > 9999) {
        mostrarAlerta('Validación', 'Por favor ingrese un número válido entre 0 y 9999', 'warning');
        return;
    }
    
    const btn = document.getElementById('btnGenerarCodigos');
    btn.disabled = true;
    btn.textContent = 'Generando...';
    
    const formData = new FormData();
    formData.append('accion', 'generar_codigos_graduados');
    formData.append('id_seccion', seccionId);
    formData.append('numero_base', numeroBase);
    
    fetch('<?php echo SERVERURL; ?>ajax/Lista-secciones-graduadosAjax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Éxito', 'Códigos generados exitosamente!', 'success').then(() => {
                cerrarModalCodigosMasivos();
                // Recargar los estudiantes
                if (currentSectionId && currentSectionName) {
                    loadStudents(currentSectionId, currentSectionName);
                }
            });
        } else {
            mostrarAlerta('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        mostrarAlerta('Error', 'Error al comunicarse con el servidor', 'error');
    })
    .finally(() => {
        btn.disabled = false;
        btn.textContent = 'Generar Códigos';
    });
}

// NUEVA FUNCIÓN: Guardar código de graduado INDIVIDUAL
function guardarCodigoGraduado(event) {
    event.preventDefault();
    
    const idInscripcion = document.getElementById('id_inscripcion_codigo').value;
    const codigoGraduado = document.getElementById('codigo_graduado').value;
    
    if (!codigoGraduado) {
        mostrarAlerta('Validación', 'Por favor ingrese un código de graduado', 'warning');
        return;
    }

    const formData = new FormData();
    formData.append('accion', 'guardar_codigo_graduado');
    formData.append('id_inscripcion', idInscripcion);
    formData.append('codigo_graduado', codigoGraduado);

    fetch('<?php echo SERVERURL; ?>ajax/Lista-secciones-graduadosAjax.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            mostrarAlerta('Éxito', data.message || 'Código guardado correctamente', 'success').then(() => {
                cerrarModalCodigo();
                // Recargar los estudiantes para actualizar la tabla
                if (currentSectionId && currentSectionName) {
                    loadStudents(currentSectionId, currentSectionName);
                }
            });
        } else {
            mostrarAlerta('Error', data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error al guardar el código:', error);
        mostrarAlerta('Error', 'Error al guardar el código', 'error');
    });
}
    
// FUNCIÓN NUEVA: Borrar todos los códigos de una sección
function borrarCodigosSeccion(seccionId, seccionNombre) {
    confirmarAccion(
        '¿Borrar todos los códigos?', 
        `Esta acción eliminará TODOS los códigos de graduados de la sección "${seccionNombre}". Esta acción no se puede deshacer.`,
        'Sí, borrar todos'
    ).then((result) => {
        if (result.isConfirmed) {
            const formData = new FormData();
            formData.append('accion', 'borrar_codigos_seccion');
            formData.append('id_seccion', seccionId);

            fetch('<?php echo SERVERURL; ?>ajax/Lista-secciones-graduadosAjax.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    mostrarAlerta('Éxito', data.message, 'success').then(() => {
                        // Recargar los estudiantes para actualizar la vista
                        if (currentSectionId && currentSectionName) {
                            loadStudents(currentSectionId, currentSectionName);
                        }
                    });
                } else {
                    mostrarAlerta('Error', data.message, 'error');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                mostrarAlerta('Error', 'Error al comunicarse con el servidor', 'error');
            });
        }
    });
}

// FUNCIÓN NUEVA: Agregar event listeners a los botones de borrado
function attachDeleteCodeEvents() {
    document.querySelectorAll('.btn-borrar-codigos').forEach(button => {
        button.addEventListener('click', function() {
            const seccionId = this.getAttribute('data-seccion');
            const seccionNombre = this.getAttribute('data-nombre');
            borrarCodigosSeccion(seccionId, seccionNombre);
        });
    });
}

// NUEVA FUNCIÓN: Agregar event listeners a los botones de edición individual
function attachEditCodeEvents() {
    document.querySelectorAll('.btn-asignar-codigo').forEach(button => {
        button.addEventListener('click', function() {
            const idInscripcion = this.getAttribute('data-inscripcion');
            const nombreEstudiante = this.getAttribute('data-nombre');
            const codigoActual = this.getAttribute('data-codigo') || '';
            
            document.getElementById('id_inscripcion_codigo').value = idInscripcion;
            document.getElementById('nombre_estudiante_codigo').value = nombreEstudiante;
            document.getElementById('codigo_graduado').value = codigoActual;
            
            // Mostrar modal de código individual
            modalCodigo.style.display = 'flex';
        });
    });
}

// Event Listeners ORIGINALES
function attachViewStudentEvents() {
    document.querySelectorAll('.btn-ver-estudiantes').forEach(button => {
        button.addEventListener('click', function() {
            const sectionId = this.getAttribute('data-id');
            const sectionName = this.getAttribute('data-nombre');
            loadStudents(sectionId, sectionName);
        });
    });
}

function attachPaginationEvents() {
    document.querySelectorAll('.page-link').forEach(link => {
        link.addEventListener('click', function(e) {
            e.preventDefault();
            const pagina = this.getAttribute('data-pagina');
            if (pagina) {
                paginaActual = parseInt(pagina);
                loadSections(paginaActual, registrosPorPagina, busquedaActual);
            }
        });
    });
}

// Cerrar modales
function cerrarModal() {
    modal.style.display = 'none';
}

// NUEVA FUNCIÓN: Cerrar modal de código individual
function cerrarModalCodigo() {
    modalCodigo.style.display = 'none';
    formCodigo.reset();
}

// Event listeners para cerrar modales
closeBtns.forEach(btn => {
    btn.addEventListener('click', function() {
        if (this.closest('#studentsModal')) {
            cerrarModal();
        } else if (this.closest('#codigoModal')) {
            cerrarModalCodigo();
        }
    });
});

// Cerrar modal al hacer clic fuera
window.addEventListener('click', function(event) {
    if (event.target === modal) {
        cerrarModal();
    }
    if (event.target === modalCodigo) {
        cerrarModalCodigo();
    }
});

// Event listeners para filtros
document.getElementById('btn-buscar').addEventListener('click', function() {
    busquedaActual = document.getElementById('busqueda').value;
    paginaActual = 1;
    loadSections(paginaActual, registrosPorPagina, busquedaActual);
});

document.getElementById('registros').addEventListener('change', function() {
    registrosPorPagina = this.value;
    paginaActual = 1;
    loadSections(paginaActual, registrosPorPagina, busquedaActual);
});

// Buscar con Enter
document.getElementById('busqueda').addEventListener('keypress', function(e) {
    if (e.key === 'Enter') {
        document.getElementById('btn-buscar').click();
    }
});

// NUEVO: Form submit para código individual
formCodigo.addEventListener('submit', guardarCodigoGraduado);

// Inicializar
document.addEventListener('DOMContentLoaded', function() {
    loadSections(paginaActual, registrosPorPagina, busquedaActual);
});
</script>
</body>
</html>