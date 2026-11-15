<?php  
    $state = $_SESSION['profile_status'] ?? 0;
	$cargo = $_SESSION['cargo_spm'] ?? 0;
	$carpeta =  "admin";

    if (!in_array($cargo, [1]) || $state != 1) {
        echo $lc->forzar_cierre_sesion_controlador();
        exit();
    }

?>

<div class="container-fluid">
    <h3 class="mb-4">Reportes</h3>

    <div class="card mb-4">
        <div class="card-header"><strong>Reportes generales de cursos</strong></div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Top cursos con más inscritos (últ. año)</span>
                        <div class="d-flex gap-2">
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_top_inscritos&meses=12&limit=10" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_top_inscritos&meses=12&limit=10" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_top_inscritos&meses=12&limit=10" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Top cursos con más aprobados (últ. año)</span>
                        <div class="d-flex gap-2">
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_top_aprobados&meses=12&limit=10" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_top_aprobados&meses=12&limit=10" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_top_aprobados&meses=12&limit=10" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Historial de cursos</span>
                        <div class="d-flex gap-2">
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_historial_cursos&meses=12" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_historial_cursos&meses=12" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_historial_cursos&meses=12" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card mb-4">
        <div class="card-header"><strong>Reportes por período</strong></div>
        <div class="card-body">
            <div class="mb-3">
                <label for="sel-periodo">Buscar período</label>
                <select id="sel-periodo" class="form-control" style="width:100%;"></select>
            </div>

            <div class="row" id="rep-periodo" style="opacity:.4; pointer-events:none;">
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Inscritos actuales del período</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=pdf_periodo_estudiantes&period_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=excel_periodo_estudiantes&period_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=csv_periodo_estudiantes&period_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Datos de los cursos del período</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=pdf_periodo_cursos&period_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=excel_periodo_cursos&period_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=csv_periodo_cursos&period_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <!--div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Recaudación del período</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=pdf_periodo_recaudacion&period_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=excel_periodo_recaudacion&period_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/periodoAjax.php?accion=csv_periodo_recaudacion&period_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div-->
            </div>
        </div>
    </div>


    <div class="card mb-4">
        <div class="card-header"><strong>Reportes por curso</strong></div>
        <div class="card-body">
            <div class="mb-3">
                <label for="sel-curso">Buscar curso</label>
                <select id="sel-curso" class="form-control" style="width:100%;"></select>
            </div>

            <div class="row" id="rep-curso" style="opacity:.4; pointer-events:none;">
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Inscritos actuales en el curso</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_curso_inscritos&course_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_curso_inscritos&course_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_curso_inscritos&course_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Recaudación del curso (últ. año)</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_curso_recaudacion&course_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_curso_recaudacion&course_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_curso_recaudacion&course_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Historial de aprobados (últ. año)</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_curso_aprobados&course_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_curso_aprobados&course_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_curso_aprobados&course_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-3 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Profesores del curso</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=pdf_curso_profesores&course_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=excel_curso_profesores&course_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/cursoAjax.php?accion=csv_curso_profesores&course_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>


    <div class="card mb-4">
        <div class="card-header"><strong>Reportes por sección</strong></div>
        <div class="card-body">
            <div class="mb-3">
                <label for="sel-seccion">Sección</label>
                <select id="sel-seccion" class="form-control" disabled>
                    <option value="">Selecciona un período y un curso primero</option>
                </select>
                <small class="text-muted" id="info-pc" style="display:none;"></small>
            </div>

            <div class="row" id="rep-seccion" style="opacity:.4; pointer-events:none;">
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Nómina</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_nomina&seccion_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_nomina&seccion_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=csv_nomina&seccion_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Calificaciones</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_seccion&seccion_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_seccion&seccion_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=csv_seccion&seccion_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Pagos</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_pagos_seccion&seccion_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_pagos_seccion&seccion_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=csv_pagos_seccion&seccion_id=" class="btn btn-sm btn-reporte" target="_blank" title="CSV"><i style="color:purple;" class="fas fa-file-csv"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Acta de calificaciones</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_acta_cal&seccion_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_acta_cal&seccion_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 mb-3">
                    <div class="d-flex align-items-center card justify-content-between p-2">
                        <span class="fw-bold text-secondary">Formato de asistencia</span>
                        <div class="d-flex gap-2">
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=pdf_asistencia&num_fechas=6&seccion_id=" class="btn btn-warning btn-sm btn-reporte" target="_blank" title="PDF"><i class="fas fa-file-pdf"></i></a>
                            <a href="#" data-url="<?= SERVERURL ?>ajax/seccionAjax.php?accion=excel_asistencia&num_fechas=6&seccion_id=" class="btn btn-success btn-sm btn-reporte" target="_blank" title="Excel"><i class="fas fa-file-excel"></i></a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
                    
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>


<script>
document.addEventListener('DOMContentLoaded', function () {
    // ─── Utilidades ──────────────────────────────────────
    function habilitarBloque(id) {
        const el = document.getElementById(id);
        el.style.opacity = '1';
        el.style.pointerEvents = 'auto';
    }
    function deshabilitarBloque(id) {
        const el = document.getElementById(id);
        el.style.opacity = '.4';
        el.style.pointerEvents = 'none';
    }

    // Select2 — Períodos
    $('#sel-periodo').select2({
        placeholder: 'Buscar período por nombre…',
        minimumInputLength: 2,
        ajax: {
            url: '<?= SERVERURL ?>ajax/select2Ajax.php',
            dataType: 'json',
            delay: 400,
            data: params => ({ accion: 'buscar_periodo', q: params.term }),
            processResults: data => ({
                results: data.map(p => ({
                    id: p.id_periodo,
                    text: p.nombre_periodo
                }))
            })
        }
    });

    // Select2 — Cursos
    $('#sel-curso').select2({
        placeholder: 'Buscar curso por nombre…',
        minimumInputLength: 2,
        ajax: {
            url: '<?= SERVERURL ?>ajax/select2Ajax.php',
            dataType: 'json',
            delay: 400,
            data: params => ({ accion: 'buscar_curso', q: params.term }),
            processResults: data => ({
                results: data.map(c => ({
                    id: c.id_curso,
                    text: c.nombre_curso + (c.tipo_curso ? ' (' + c.tipo_curso + ')' : '')
                }))
            })
        }
    });

    // Período → habilitar reportes de período
    let periodId = null;
    let courseId = null;
    let sectionId = null;

    $('#sel-periodo').on('select2:select', function (e) {
        periodId = e.params.data.id;
        habilitarBloque('rep-periodo');

        // Actualizar href reales con el period_id
        document.querySelectorAll('#rep-periodo .btn-reporte').forEach(a => {
            a.href = a.dataset.url + periodId;
        });

        // Si también hay curso, recargar secciones
        if (courseId) cargarSecciones();
    });

    $('#sel-periodo').on('select2:clear', function () {
        periodId = null;
        deshabilitarBloque('rep-periodo');
        resetSecciones();
    });

    // Curso → habilitar reportes de curso
    $('#sel-curso').on('select2:select', function (e) {
        courseId = e.params.data.id;
        habilitarBloque('rep-curso');

        document.querySelectorAll('#rep-curso .btn-reporte').forEach(a => {
            a.href = a.dataset.url + courseId;
        });

        // Si también hay período, recargar secciones
        if (periodId) cargarSecciones();
    });

    $('#sel-curso').on('select2:clear', function () {
        courseId = null;
        deshabilitarBloque('rep-curso');
        resetSecciones();
    });

    // Cargar secciones (course + period → sections)
    const selSeccion = document.getElementById('sel-seccion');
    const infoPc     = document.getElementById('info-pc');

    function cargarSecciones() {
        if (!periodId || !courseId) return;

        selSeccion.disabled = true;
        selSeccion.innerHTML = '<option value="">Cargando secciones…</option>';
        infoPc.style.display = 'none';

        fetch(`<?= SERVERURL ?>ajax/select2Ajax.php?accion=secciones_por_curso_periodo&course_id=${courseId}&period_id=${periodId}`)
            .then(r => r.json())
            .then(data => {
                if (!data.length) {
                    selSeccion.innerHTML = '<option value="">Sin secciones disponibles</option>';
                    infoPc.style.display = 'block';
                    infoPc.textContent = 'Este curso no se ofreció en el período seleccionado, o no tiene secciones activas.';
                    return;
                }
                let html = '<option value="">Selecciona una sección…</option>';
                data.forEach(s => {
                    html += `<option value="${s.id_seccion}">${s.nombre_seccion}</option>`;
                });
                selSeccion.innerHTML = html;
                selSeccion.disabled = false;
            })
            .catch(err => {
                selSeccion.innerHTML = '<option value="">Error al cargar secciones</option>';
                console.error(err);
            });
    }

    function resetSecciones() {
        sectionId = null;
        selSeccion.innerHTML = '<option value="">Selecciona un período y un curso primero</option>';
        selSeccion.disabled = true;
        infoPc.style.display = 'none';
        deshabilitarBloque('rep-seccion');
    }

    // Sección → habilitar reportes de sección
    selSeccion.addEventListener('change', function () {
        sectionId = this.value || null;
        if (!sectionId) {
            deshabilitarBloque('rep-seccion');
            return;
        }
        habilitarBloque('rep-seccion');

        document.querySelectorAll('#rep-seccion .btn-reporte').forEach(a => {
            a.href = a.dataset.url + sectionId;
        });
    });

    // GUARD: solo 1 reporte a la vez
    document.querySelectorAll('.btn-reporte').forEach(btn => {
        btn.addEventListener('click', function (e) {
            if (this.dataset.emitiendo === '1') {
                e.preventDefault();
                return;
            }

            // Marcar este botón como "en emisión" y deshabilitar el resto
            const todos = document.querySelectorAll('.btn-reporte');
            todos.forEach(b => {
                b.classList.add('disabled');
                b.style.pointerEvents = 'none';
                b.style.opacity = '.4';
            });
            this.dataset.emitiendo = '1';

            // Re-habilitar a los 5 segundos (o cuando el usuario vuelva a esta pestaña)
            const reenable = () => {
                todos.forEach(b => {
                    b.classList.remove('disabled');
                    b.style.pointerEvents = '';
                    b.style.opacity = '';
                    delete b.dataset.emitiendo;
                });
            };

            // Re-habilitar cuando el usuario vuelva a la pestaña
            const onFocus = () => {
                setTimeout(reenable, 500);
                window.removeEventListener('focus', onFocus);
            };
            window.addEventListener('focus', onFocus);

            // Fallback: si nunca vuelve a la pestaña, re-habilitar a los 10s
            setTimeout(reenable, 10000);
        });
    });
});
</script>
                    
                    
                    