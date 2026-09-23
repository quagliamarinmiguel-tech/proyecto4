//dd



//aclaremos varias cosas, de los datos de period traidos a la vista, enviamos el rango d inicio y fin a la funcion de supabase q se trae todos los encuentros, no puede haber un encuentro de una section en un dia distinto
//la letra debe generarse automaticamente, timestart no puede ser posterior a timeend, no pueden haber una reunion en el salon 2 de la seccion a de ingles ii intensivo de 10 a 12 si la seccion b de tecnico de unas
//tiene una clase en el salon 2 de 9 a 11, osea q se necesitan las fechas del periodo para cada encuentro,
/*-----------------------------------------------------------------SECCION NEW-----------------------------------------------------------------------*/
/*-----------------------------------------------------------------SECCION NEW-----------------------------------------------------------------------*/
/*-----------------------------------------------------------------SECCION NEW-----------------------------------------------------------------------*/

<?php
if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
    echo $lc->forzar_cierre_sesion_controlador();
    exit();
}
$cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [1 => "admin", 2 => "Control de estudio", 3 => "administrador de pagos", 4 => "Supervisor de cursos", 5 => "Estudiante"];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";

$periodCourseId = $_GET['curso_vigente'] ?? '';
//$periodCourseId = $_GET['period_course_id'] ?? '';
require_once "./controladores/periodoControlador.php";
$ctrl = new periodoControlador();
$datos = $ctrl->datos_para_nueva_seccion_controlador($periodCourseId);
if (!$datos['ok']) {
    echo '<div class="alert alert-danger">No se encontró el periodo‑curso.</div>';
    exit();
}
$info = $datos['data'];
$period = $info['period'];
$course = $info['course'];

//var_dump($datos['encuentros_existentes']);
//die();
// Obtener profesores y aulas
$profesores = $ctrl->obtener_profesores_activos_para_select();
$aulas = $ctrl->obtener_aulas_para_select();
?>

<style>
select {
    max-height: 200px; /* Altura máxima antes de mostrar scroll */
    overflow-y: auto;  /* Muestra scroll vertical cuando sea necesario */
}
</style> 
    



<div class="full-box page-header">
    <h3 class="text-left"><i class="fas fa-layer-group fa-fw"></i> &nbsp; NUEVA SECCIÓN</h3>
</div>

<div class="container-fluid">
    <ul class="full-box list-unstyled page-nav-tabs">
        <li><a class="active" href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-new&period_course_id=' . $periodCourseId; ?>"><i class="fas fa-plus"></i> NUEVA SECCIÓN</a></li>
        <li><a href="<?php echo SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list'; ?>"><i class="fas fa-list"></i> LISTA DE PERIODOS</a></li>
    </ul>
</div>

<div class="container-fluid">
    <p><strong>Periodo:</strong> <?= htmlspecialchars($period['name']) ?> (<?= $period['type'] ?>)<br>
    <strong>Curso:</strong> <?= htmlspecialchars($course['name']) ?> (<?= $course['type'] ?>)<br>
    <strong>ID Period-Curso:</strong> <?= $periodCourseId ?></p>

    <form id="form-seccion" class="form-neon FormularioAjax" action="<?php echo SERVERURL; ?>ajax/periodoAjax.php" method="POST" data-form="save" autocomplete="off">
        <input type="hidden" name="period_course_id" value="<?= $periodCourseId ?>">
        <input type="hidden" name="clases" id="clases-input">

        <fieldset>
            <legend><i class="fas fa-info-circle"></i> Datos de la sección</legend>
            <div class="row">
                <div class="col-md-6">
                    <!--div class="form-group">
                        <label>Nombre de sección</label>
                        <!--input type="text" name="section_name" id="section_name" class="form-control" value="<?= 'A' ?>" readonly required->
                        <input type="text" name="section_name" id="section_name" class="form-control" 
       					value="<php echo htmlspecialchars($datos['sugerencia_nombre_seccion'] ?? 'A'); ?>" readonly required>       
                    </div-->
                    
         <div class="col-md-2 mb-2">
            <label class="form-label">Nombre de sección</label> 
            <select class="form-control" name="section_name" required>
              <option value="" selected disabled>Seleccione...</option>
              <option value="A">A</option>
              <option value="B">B</option>
              <option value="C">C</option>
              <option value="D">D</option>
              <option value="E">E</option>
              <option value="F">F</option>
              <option value="G">G</option>
              <option value="H">H</option>
              <option value="I">I</option>
              <option value="J">J</option>
              <option value="K">K</option>
              <option value="L">L</option>
              <option value="M">M</option>
              <option value="N">N</option>
              <option value="Ñ">Ñ</option>
              <option value="O">O</option>
              <option value="P">P</option>
              <option value="Q">Q</option>
              <option value="R">R</option>
              <option value="S">S</option>
              <option value="T">T</option>
              <option value="U">U</option>
              <option value="V">V</option>
              <option value="W">W</option>
              <option value="X"><X/option>
              <option value="Y">Y</option>
              <option value="Z">Z</option>
            </select>
         </div>
                    
                </div>     
            </div>
        </fieldset>

        
        
         <div class="col-md-2 mb-2">
            <label class="form-label">Día de clases</label> 
            <select class="form-control" name="section_day" required>
              <option value="" selected disabled>Seleccione...</option>
              <option value="Lunes">Lunes</option>
              <option value="Martes">Martes</option>
              <option value="Miércoles">Miércoles</option>
              <option value="Jueves">Jueves</option>
              <option value="Viernes">Viernes</option>
              <option value="Sábado">Sábado</option>
              <option value="Domingo">Domingo</option>
            </select>
         </div>
        
        <fieldset>
            <legend><i class="fas fa-calendar-day"></i> Encuentros (máximo 10)</legend>
            <div id="encuentros-contenedor"></div>
            <p class="text-end">
                <button type="button" id="agregarEncuentro" class="btn btn-outline-primary btn-sm">
                    <i class="fas fa-plus"></i> Agregar encuentro
                </button>
            </p>
        </fieldset>

        <p class="text-center">
            <button type="submit" id="btn-submit-seccion" class="btn btn-info btn-sm" disabled>
                <i class="far fa-save"></i> GUARDAR SECCIÓN
            </button>
        </p>
    </form>
</div>

<script src="<?php echo SERVERURL; ?>vistas/js/miscript.js"></script>
    
    <script>
document.addEventListener("DOMContentLoaded", function() {
    const contenedor = document.getElementById("encuentros-contenedor");
    const btnAgregar = document.getElementById("agregarEncuentro");
    const btnSubmit = document.getElementById("btn-submit-seccion");
    const inputClases = document.getElementById("clases-input");
    const sectionDaySelect = document.querySelector("select[name='section_day']");
    let contador = 0;

    // Datos desde PHP
    const profesores = <?php echo json_encode($profesores['data'] ?? []); ?>;
    const aulas = <?php echo json_encode($aulas ?? []); ?>;
    let encuentrosExistentes = <?php echo json_encode($datos['encuentros_existentes'] ?? []); ?>;
    // Asegurar que encuentrosExistentes sea un array
    if (encuentrosExistentes && typeof encuentrosExistentes === 'object' && !Array.isArray(encuentrosExistentes) && encuentrosExistentes.data) {
        encuentrosExistentes = encuentrosExistentes.data;
    }
    if (!Array.isArray(encuentrosExistentes)) {
        encuentrosExistentes = [];
    }
    const periodCourseId = <?php echo json_encode($periodCourseId); ?>;

    // ============================================
    // FUNCIONES DE RENDERIZADO DE SELECTS
    // ============================================
    function renderizarSelectProfesores(select, selected = '') {
        select.innerHTML = '<option value="">Seleccione...</option>';
        profesores.forEach(p => {
            const opt = document.createElement('option');
            opt.value = p.id;
            opt.textContent = p.name + ' ' + p.last_name;
            if (p.id == selected) opt.selected = true;
            select.appendChild(opt);
        });
    }

    function renderizarSelectAulas(select, selected = '') {
        select.innerHTML = '<option value="">Seleccione...</option>';
        aulas.forEach(a => {
            const opt = document.createElement('option');
            opt.value = a.id;
            opt.textContent = a.classroom;
            if (a.id == selected) opt.selected = true;
            select.appendChild(opt);
        });
    }

    // ============================================
    // VALIDACIÓN DE CONFLICTOS
    // ============================================
    function validarSolapamiento() {
        const filas = document.querySelectorAll('.fila-encuentro');
        const sectionDay = sectionDaySelect.value;

        if (!sectionDay || filas.length === 0) {
            btnSubmit.disabled = true;
            return false;
        }

        // 1. Recolectar encuentros nuevos
        const nuevos = [];
        let todosLlenos = true;
        filas.forEach(fila => {
            const start = fila.querySelector('.start-time').value;
            const end = fila.querySelector('.end-time').value;
            const teacher = fila.querySelector('.teacher-select').value;
            const classroom = fila.querySelector('.classroom-select').value;
            if (start && end && teacher && classroom) {
                nuevos.push({
                    start_time: start,
                    end_time: end,
                    teacher_id: parseInt(teacher),
                    classroom_id: parseInt(classroom),
                    element: fila
                });
            } else {
                todosLlenos = false;
            }
        });

        if (!todosLlenos || nuevos.length === 0) {
            btnSubmit.disabled = true;
            filas.forEach(f => f.style.border = '');
            return false;
        }

        // 2. Construir lista completa de encuentros a validar (nuevos + existentes)
        const todos = [...nuevos];
        encuentrosExistentes.forEach(e => {
            todos.push({
                start_time: e.start_time,
                end_time: e.end_time,
                classroom_id: e.classroom_id,
                section_day: e.section_day,
                isExisting: true
            });
        });

        // 3. Validar conflictos
        let hayConflicto = false;
        for (let i = 0; i < todos.length; i++) {
            for (let j = i + 1; j < todos.length; j++) {
                const a = todos[i];
                const b = todos[j];
                const dayA = a.section_day || sectionDay;
                const dayB = b.section_day || sectionDay;
                if (a.classroom_id === b.classroom_id && dayA === dayB && dayA === sectionDay) {
                    // Convertir horas a minutos
                    const startA = a.start_time.split(':').map(Number);
                    const endA = a.end_time.split(':').map(Number);
                    const startB = b.start_time.split(':').map(Number);
                    const endB = b.end_time.split(':').map(Number);
                    const minA = startA[0] * 60 + startA[1];
                    const maxA = endA[0] * 60 + endA[1];
                    const minB = startB[0] * 60 + startB[1];
                    const maxB = endB[0] * 60 + endB[1];
                    if (maxA > minB && maxB > minA) {
                        hayConflicto = true;
                        if (a.element) a.element.style.border = '2px solid red';
                        if (b.element) b.element.style.border = '2px solid red';
                    } else {
                        if (a.element) a.element.style.border = '';
                        if (b.element) b.element.style.border = '';
                    }
                }
            }
        }

        btnSubmit.disabled = hayConflicto;
        if (hayConflicto) {
            document.getElementById('alerta-conflicto')?.remove();
            const alerta = document.createElement('div');
            alerta.id = 'alerta-conflicto';
            alerta.className = 'alert alert-danger mt-2';
            alerta.textContent = 'Existen conflictos de horario o aula. Revisa los encuentros marcados en rojo.';
            document.querySelector('fieldset:last-of-type').after(alerta);
        } else {
            document.getElementById('alerta-conflicto')?.remove();
        }

        return !hayConflicto;
    }

    // ============================================
    // ACTUALIZAR CAMPO OCULTO CON DATOS DE CLASES
    // ============================================
    function actualizarClasesInput() {
        const filas = document.querySelectorAll('.fila-encuentro');
        const clasesData = [];
        filas.forEach(fila => {
            const start = fila.querySelector('.start-time').value;
            const end = fila.querySelector('.end-time').value;
            const teacher = fila.querySelector('.teacher-select').value;
            const classroom = fila.querySelector('.classroom-select').value;
            if (start && end && teacher && classroom) {
                clasesData.push({
                    start_time: start,
                    end_time: end,
                    teacher_id: parseInt(teacher),
                    classroom_id: parseInt(classroom)
                });
            }
        });
        inputClases.value = JSON.stringify(clasesData);
            console.log('Clases enviadas:', inputClases.value);

    }

    // ============================================
    // AGREGAR FILA DE ENCUENTRO
    // ============================================
    function agregarFila(initialData = null) {
        if (contador >= 10) {
            alert('Máximo 10 encuentros por sección');
            return;
        }
        const div = document.createElement('div');
        div.className = 'row border rounded p-3 mb-3 bg-light fila-encuentro';
        div.innerHTML = `
            <div class="col-md-3">
                <label>Hora inicio</label>
                <input type="time" min="08:00" max="18:00" class="form-control start-time" value="${initialData?.start_time || ''}">
            </div>
            <div class="col-md-3">
                <label>Hora fin</label>
                <input type="time" min="08:00" max="18:00" class="form-control end-time" value="${initialData?.end_time || ''}">
            </div>
            <div class="col-md-3">
                <label>Profesor</label>
                <select class="form-control teacher-select">
                    <option value="">Seleccione...</option>
                </select>
            </div>
            <div class="col-md-2">
                <label>Aula</label>
                <select class="form-control classroom-select">
                    <option value="">Seleccione...</option>
                </select>
            </div>
            <div class="col-md-1 d-flex align-items-end">
                <button type="button" class="btn btn-danger btn-sm eliminar-encuentro"><i class="fas fa-trash-alt"></i></button>
            </div>
        `;
        contenedor.appendChild(div);
        contador++;

        const teacherSelect = div.querySelector('.teacher-select');
        const classroomSelect = div.querySelector('.classroom-select');
        renderizarSelectProfesores(teacherSelect, initialData?.teacher_id || '');
        renderizarSelectAulas(classroomSelect, initialData?.classroom_id || '');

        div.querySelector('.eliminar-encuentro').addEventListener('click', function() {
            div.remove();
            actualizarClasesInput();
            validarSolapamiento();
        });
        div.querySelectorAll('input, select').forEach(el => {
            el.addEventListener('change', function() {
                actualizarClasesInput();
                validarSolapamiento();
            });
            el.addEventListener('input', function() {
                actualizarClasesInput();
                validarSolapamiento();
            });
        });

        actualizarClasesInput();
        validarSolapamiento();
    }

    // ============================================
    // EVENTOS
    // ============================================
    btnAgregar.addEventListener('click', function() {
        agregarFila();
    });

    sectionDaySelect.addEventListener('change', function() {
        validarSolapamiento();
    });

    document.getElementById('form-seccion').addEventListener('submit', function(e) {
        if (!validarSolapamiento()) {
            e.preventDefault();
            alert('Corrige los errores antes de guardar.');
        }
    });

    // Cargar una fila inicial
    agregarFila();

    // Inicializar validación
    validarSolapamiento();
});
</script>
   





<!-----------------------------------------------------------------PERIODO CONTROLADOR------------------------------------------------------------------------->
<!-----------------------------------------------------------------PERIODO CONTROLADOR------------------------------------------------------------------------->
<!-----------------------------------------------------------------PERIODO CONTROLADOR------------------------------------------------------------------------->


       /**
         * Crea una nueva sección con sus encuentros (classes)
         * Espera: period_course_id, section_name, section_day, clases (JSON)
         */
    public function datos_para_nueva_seccion_controlador($periodCourseId): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }

        // Obtener period_course con periodo y curso
        $resultadoPC = SupabaseApi::getWithToken('period_course', [
            'select' => 'id,period_id,period(name,type,start_date,end_date),course_id,course(name,type)',
            'id' => 'eq.' . $periodCourseId,
        ], $token);
        if (!$resultadoPC['ok'] || empty($resultadoPC['data'][0])) {
            return ['ok' => false, 'message' => 'Period‑course no encontrado'];
        }
        $data = $resultadoPC['data'][0];
        $period = $data['period'];
        $course = $data['course'];

        // Obtener encuentros existentes que solapan con el rango del periodo
        $encuentros = periodoModelo::obtener_encuentros_para_validacion_supabase(
            $period['start_date'],
            $period['end_date'],
            null,
            $token
        );
        $encuentrosData = $encuentros['data'] ?? [];

        // Obtener profesores y aulas
        $profesores = $this->obtener_profesores_activos_para_select();
        $aulas = $this->obtener_aulas_para_select();

        // Obtener siguiente letra (corregido)
        $siguienteLetra = $this->obtener_siguiente_letra_seccion($periodCourseId, $token);
        if ($siguienteLetra === null) {
            return ['ok' => false, 'message' => 'No se pueden crear más secciones (máximo 26)'];
        }

        return [
            'ok' => true,
            'data' => $data,
            'encuentros_existentes' => $encuentrosData,
            'profesores' => $profesores,
            'aulas' => $aulas,
            'period_start_date' => $period['start_date'],
            'period_end_date' => $period['end_date'],
            'next_section_name' => $siguienteLetra, // <-- Agregado
        ];
    }

        

    public function obtener_profesores_activos_para_select(): array{
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        return periodoModelo::listar_profesores_supabase_para_periodo($accessToken);
    }

        
    public function obtener_aulas_para_select(): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return [];
         return periodoModelo::obtener_salones_para_seccion_new($token);
    }   



    public function crear_seccion_controlador(): string
    {
        try {
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $periodCourseId = (int) ($_POST['period_course_id'] ?? 0);
            $sectionName = trim(mainModel::limpiar_cadena($_POST['section_name'] ?? ''));
            $sectionDay = trim(mainModel::limpiar_cadena($_POST['section_day'] ?? ''));
            
            
            $clasesJson = $_POST['clases'] ?? '[]';
            $clases = json_decode($clasesJson, true);

            // Depuración
            if (!is_array($clases)) {
                // Si no es array, probablemente json_decode falló o llegó vacío
                error_log("clasesJson recibido: " . $clasesJson);
                error_log("clases decodificado: " . print_r($clases, true));
                return json_encode(mainModel::generarAlertaSimple('Formato de encuentros inválido', 'error', 'simple'));
            }
            
            

            if ($periodCourseId <= 0 || $sectionName === '' || $sectionDay === '' || empty($clases)) {
                return json_encode(mainModel::generarAlertaSimple('Datos incompletos', 'error', 'simple'));
            }
            if (count($clases) > 10) {
                return json_encode(mainModel::generarAlertaSimple('Máximo 10 encuentros por sección', 'error', 'simple'));
            }

            foreach ($clases as $idx => $clase) {
                if (!isset($clase['start_time'], $clase['end_time'], $clase['teacher_id'], $clase['classroom_id'])) {
                    return json_encode(mainModel::generarAlertaSimple('Encuentro #' . ($idx+1) . ' incompleto', 'error', 'simple'));
                }
                if ((int) $clase['teacher_id'] <= 0 || (int) $clase['classroom_id'] <= 0) {
                    return json_encode(mainModel::generarAlertaSimple('Profesor o aula inválida en encuentro #' . ($idx+1), 'error', 'simple'));
                }
            }

            // 🔥 Obtener encuentros existentes (CORREGIDO: usar el modelo)
            $resultadoExistentes = periodoModelo::obtener_encuentros_para_validacion_supabase_para_controlador_crear_seccion(
                $periodCourseId,
                null,
                $token
            );

            // Asegurar que los datos sean un array
            $existentes = [];
            if ($resultadoExistentes['ok'] && isset($resultadoExistentes['data']) && is_array($resultadoExistentes['data'])) {
                $existentes = $resultadoExistentes['data'];
            }

            // 🔥 Validar conflictos (completo y sin die)
            foreach ($clases as $nuevo) {
                foreach ($existentes as $existente) {
                    // Verificar que $existente tenga las claves necesarias
                    if (is_array($existente) && isset($existente['section_day'], $existente['classroom_id'], $existente['start_time'], $existente['end_time'])) {
                        if ($existente['section_day'] === $sectionDay &&
                            $existente['classroom_id'] == $nuevo['classroom_id']) {
                            $start1 = strtotime($nuevo['start_time']);
                            $end1 = strtotime($nuevo['end_time']);
                            $start2 = strtotime($existente['start_time']);
                            $end2 = strtotime($existente['end_time']);
                            if ($start1 < $end2 && $end1 > $start2) {
                                return json_encode(mainModel::generarAlertaSimple(
                                    'Conflicto: el aula ya está ocupada en ese horario y día.',
                                    'error', 'simple'
                                ));
                            }
                        }
                    }
                }
            }

            // Preparar datos para la RPC
            $datos = [
                'period_course_id' => $periodCourseId,
                'section_name' => $sectionName,
                'section_day' => $sectionDay,
                'clases' => $clases,
            ];

            // Llamar al modelo (ya está en periodoModelo)
            $resultado = periodoModelo::crear_seccion_con_clases_supabase($datos, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                return json_encode(mainModel::generarAlertaSimple('Sección y encuentros creados exitosamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al crear: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }        
        
        















<!-----------------------------------------------------------------PERIODO MODELO------------------------------------------------------------------------->
<!-----------------------------------------------------------------PERIODO MODELO------------------------------------------------------------------------->
<!-----------------------------------------------------------------PERIODO MODELO------------------------------------------------------------------------->


public function obtener_salones_para_seccion_new($token): array{
        //$token = $_SESSION['auth_access_token'] ?? null;
        //if (!$token) return [];
        $resultado = SupabaseApi::getWithToken('classroom', [
            'select' => 'id,classroom',
            'order' => 'classroom.asc',
        ], $token);
        return $resultado['data'] ?? [];
    }


        
    public static function obtener_encuentros_para_validacion_supabase(
    string $startDate,
    string $endDate,
    ?int $excluirSectionId,
    string $accessToken
	): array {
    $params = [
        'p_start_date' => $startDate,
        'p_end_date' => $endDate,
        'p_excluir_section_id' => $excluirSectionId,
    ];
    return SupabaseApi::rpcWithToken('obtener_encuentros_para_validacion_de_seccion_new', $params, $accessToken);
}
          
        

// }
        
 /**
     * Crea una sección y sus encuentros en Supabase
     */
    public static function crear_seccion_con_clases_supabase(array $datos, string $accessToken): array{
        $params = [
            'p_period_course_id' => $datos['period_course_id'],
            'p_section_name' => $datos['section_name'],
            'p_section_day' => $datos['section_day'],
            'p_clases' => json_encode($datos['clases']),
        ];
        return SupabaseApi::rpcWithToken('crear_seccion_con_clases_de_section_new', $params, $accessToken);
    }
        
        
        
        /**
 * Obtiene la siguiente letra disponible para una sección
 * @param int $periodCourseId
 * @param string $accessToken
 * @return string|null Letra asignada o null si ya hay más de 26 secciones
 */
    public function obtener_siguiente_letra_seccion(int $periodCourseId, string $accessToken): ?string{
        // Contar secciones existentes para este period_course_id
        $resultado = SupabaseApi::getWithToken('section', [
            'select' => 'count',
            'period_course_id' => 'eq.' . $periodCourseId,
            'state' => 'eq.1', // solo secciones activas
        ], $accessToken);

        if (!$resultado['ok']) {
            return null;
        }

        $count = (int) ($resultado['data'][0]['count'] ?? 0);
        if ($count >= 26) {
            return null; // ya hay 26 secciones (A-Z)
        }

        $letras = range('A', 'Z');
        return $letras[$count];
    }
        


    public static function obtener_encuentros_para_validacion_supabase_para_controlador_crear_seccion(
        int $periodCourseId,
        ?int $excluirSectionId,
        string $accessToken
    ): array {
        // Primero necesitamos obtener el rango de fechas del period_course
        $periodo = SupabaseApi::getWithToken('period_course', [
            'select' => 'period_id,period(start_date,end_date)',
            'id' => 'eq.' . $periodCourseId,
        ], $accessToken);
        if (!$periodo['ok'] || empty($periodo['data'][0])) {
            return ['ok' => false, 'message' => 'Period‑course no encontrado'];
        }
        $period = $periodo['data'][0]['period'];
        $startDate = $period['start_date'];
        $endDate = $period['end_date'];

        $params = [
            'p_start_date' => $startDate,
            'p_end_date' => $endDate,
            'p_excluir_section_id' => $excluirSectionId,
        ];
        return SupabaseApi::rpcWithToken('obtener_encuentros_para_validacion_de_seccion_new', $params, $accessToken);
    }       
        
        
        















<!-----------------------------------------------------------------CONSULTAS SUPABASE------------------------------------------------------------------------->
<!-----------------------------------------------------------------CONSULTAS SUPABASE------------------------------------------------------------------------->
<!-----------------------------------------------------------------CONSULTAS SUPABASE------------------------------------------------------------------------->


//crear_seccion_con_clases_para_section_new
DECLARE
    v_section_id BIGINT;
    v_clase JSONB;
BEGIN
    -- 1. Crear la sección
    INSERT INTO section (period_course_id, name, state)
    VALUES (p_period_course_id, p_section_name, 1)
    RETURNING id INTO v_section_id;

    -- 2. Insertar cada encuentro en classes
    FOR v_clase IN SELECT * FROM jsonb_array_elements(p_clases)
    LOOP
        INSERT INTO classes (
            period_course_id,
            start_date,
            end_date,
            teacher_id,
            classroom_id
        ) VALUES (
            p_period_course_id,
            (v_clase->>'start_date')::DATE,
            (v_clase->>'end_date')::DATE,
            (v_clase->>'teacher_id')::BIGINT,
            (v_clase->>'classroom_id')::BIGINT
        );
    END LOOP;

    RETURN jsonb_build_object(
        'ok', true,
        'section_id', v_section_id
    );
EXCEPTION
    WHEN OTHERS THEN
        RETURN jsonb_build_object(
            'ok', false,
            'error', SQLERRM
        );
END;



//crear_seccion_con_clases_de_seccion_new
    //no se en q se diferencian

DECLARE
    v_section_id BIGINT;
    v_clase JSONB;
BEGIN
    -- Crear la sección
    INSERT INTO section (period_course_id, name, section_day, state)
    VALUES (p_period_course_id, p_section_name, p_section_day, 1)
    RETURNING id INTO v_section_id;

    -- Insertar cada encuentro
    FOR v_clase IN SELECT * FROM jsonb_array_elements(p_clases)
    LOOP
        INSERT INTO classes (
            section_id,
            start_time,
            end_time,
            teacher_id,
            classroom_id,
            state
        ) VALUES (
            v_section_id,
            (v_clase->>'start_time')::TIME,
            (v_clase->>'end_time')::TIME,
            (v_clase->>'teacher_id')::BIGINT,
            (v_clase->>'classroom_id')::BIGINT,
            1
        );
    END LOOP;

    RETURN jsonb_build_object('ok', true, 'section_id', v_section_id);
EXCEPTION
    WHEN OTHERS THEN
        RETURN jsonb_build_object('ok', false, 'error', SQLERRM);
END;




//obtener_encuentros_para_validacion_de_seccion_new
    //esta funcionaba parece

DECLARE
    v_resultado JSONB;
BEGIN
    SELECT COALESCE(
        jsonb_agg(
            jsonb_build_object(
                'id', c.id,
                'start_time', c.start_time,
                'end_time', c.end_time,
                'classroom_id', c.classroom_id,
                'teacher_id', c.teacher_id,
                'section_id', s.id,
                'section_name', s.name,
                'period_course_id', pc.id,
                'course_name', cr.name,
                'period_id', p.id,
                'period_name', p.name,
                'period_start_date', p.start_date,
                'period_end_date', p.end_date,
                'section_day', s.section_day  -- Día de la semana de la sección
            )
            ORDER BY c.start_time
        ),
        '[]'::JSONB
    ) INTO v_resultado
    FROM classes c
    JOIN section s ON c.section_id = s.id
    JOIN period_course pc ON s.period_course_id = pc.id
    JOIN period p ON pc.period_id = p.id
    JOIN course cr ON pc.course_id = cr.id
    WHERE s.state = 1
      AND pc.state = 1
      AND p.state = 1
      AND c.classroom_id IS NOT NULL
      AND (p_excluir_section_id IS NULL OR s.id != p_excluir_section_id)
      AND p.start_date <= p_end_date
      AND p.end_date >= p_start_date;  -- Solapamiento de periodos

    RETURN jsonb_build_object('ok', true, 'data', v_resultado);
END;





































