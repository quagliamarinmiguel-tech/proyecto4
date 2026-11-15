<?php

    require_once "mainModel.php";

    class periodoModelo extends mainModel{

/*
* period new, period update, period list, period list archived
* period course new, period course list, section new (por ahora)
*/
        


//estas 2 son para traer cursos y profes para la seccion en periodo new y update de agregar cursos vigentes o period_courses    
    public static function listar_cursos_supabase_para_periodo(string $accessToken, ?string $type = null): array {
        $params = [
            'select' => 'id, name, type',
            'state'  => 'eq.1',
            'order'  => 'name.asc'
        ];

        // Filtro opcional por tipo de curso
        if (!empty($type)) {
            $params['type'] = 'eq.' . $type;
        }

        return SupabaseApi::getWithToken('course', $params, $accessToken);
    }
        
    public static function listar_profesores_supabase_para_periodo(string $accessToken): array {
        $params = [
            'select' => 'id, name, last_name',
            'state' => 'eq.1',
            'order' => 'name.asc'
        ];
        return SupabaseApi::getWithToken('teacher', $params, $accessToken);

    
    }

//estas son las validaciones de periodo new y update
//para verificar nombre de periodo duplicado en periodo new y update, lo usa el ajax para validar q el nombre sea valido antes de enviarlo
    public static function verificar_periodo_duplicado_supabase($nombre, $accessToken, $id_excluir = null): array {
        $filtros = [
            'select' => 'id',
            'name' => 'eq.' . $nombre,
            'state' => 'neq.0',
        ];
        if ($id_excluir !== null && $id_excluir !== '') {
            $filtros['id'] = 'neq.' . $id_excluir;
        }
        return SupabaseApi::getWithToken('period', $filtros, $accessToken);
    }        
   
    public static function obtener_ultimo_periodo_supabase(string $tipo, string $accessToken): array {
        // Query to get the last period of the same type ordered by start_date descending
        $params = [
            'select' => 'id, name, start_date, end_date',
            'type' => 'eq.' . $tipo,
            'order' => 'start_date.desc',
            'limit' => '1'
        ];
        return SupabaseApi::getWithToken('period', $params, $accessToken);
    }

        
        


//crea el perido con sus period cost y period course y sections
    //    @parametros que incluyen el periodo, sus costos o periodcosts que cambian dependiendo del type del periodo, sus periodcourse para crear si los incluye y una section A para cada periodCourse
     public static function crear_periodo_rpc(array $params, string $accessToken): array {
    	return SupabaseApi::rpcWithToken('crear_periodo', $params, $accessToken);
	}   
        
        //patra actualizar el periodo en period update
    public static function actualizar_periodo(array $params, string $accessToken): array{
        return SupabaseApi::rpcWithToken('actualizar_periodo', $params, $accessToken);
    }
        
         

//lista periodos, en teorioa con state 1 y 0        
    public static function listar_periodos_con_costos_supabase(array $filtros, string $accessToken, int $limit, int $offset): array {
        $params = [
            'p_busqueda' => $filtros['busqueda'] ?? null,
            'p_tipo'     => $filtros['tipo'] ?? null,
            'p_estado'   => $filtros['estado'] ?? null,
            'p_orden'    => $filtros['orden'] ?? 'id_desc',
            'p_limit'    => $limit,
            'p_offset'   => $offset,
            'p_state'    => (int) ($filtros['state'] ?? 1),
            'p_id'       => isset($filtros['id']) && $filtros['id'] !== '' ? (int) $filtros['id'] : null,
            'p_anio'     => isset($filtros['anio']) && $filtros['anio'] !== '' ? (int) $filtros['anio'] : null,
        ];

        $claveCache = 'periodos_' . md5(json_encode($params));

        $dataCache = mainModel::leerCache($claveCache);
        if ($dataCache !== null) {
            return $dataCache;
        }

        $resultado = SupabaseApi::rpcWithToken('listar_periodos', $params, $accessToken);

        if (!empty($resultado['ok'])) {
            mainModel::guardarCache($claveCache, $resultado, 3600);
        }

        return $resultado;
    } 
        
        

    public static function obtener_periodo_con_cursos_costos_supabase(int $id, string $accessToken): array{
        return SupabaseApi::rpcWithToken('obtener_periodo_con_cursos_para_period_update', ['p_period_id' => $id], $accessToken);
    }
        
        
    public static function archivar_periodo_cascada_supabase(int $periodId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('archivar_periodo_cascada', ['p_period_id' => $periodId], $accessToken);
    }
        
    public static function restaurar_periodo_cascada_supabase(int $periodId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('restaurar_periodo', ['p_period_id' => $periodId], $accessToken);
    }
        
        /*----------------------------------------------------------funciones de period course---------------------------------------------------------------------*/
 

    public static function listar_period_course_resumen_supabase(array $filtros, string $accessToken, int $limit, int $offset): array{
        $params = [
            'p_busqueda_periodo' => $filtros['busqueda_periodo'] ?? null,
            'p_busqueda_curso'   => $filtros['busqueda_curso'] ?? null,
            'p_id_period_course' => isset($filtros['id_period_course']) && $filtros['id_period_course'] !== '' ? (int) $filtros['id_period_course'] : null,
            'p_estado_periodo'   => $filtros['estado_periodo'] ?? null,
            'p_orden'            => $filtros['orden'] ?? 'periodo_desc',
            'p_limit'            => $limit,
            'p_offset'           => $offset,
            'p_state'            => (int) ($filtros['state'] ?? 1),
            'p_course_id'        => isset($filtros['course_id']) && $filtros['course_id'] !== '' ? (int) $filtros['course_id'] : null,
            'p_period_id'        => isset($filtros['period_id']) && $filtros['period_id'] !== '' ? (int) $filtros['period_id'] : null,
            'p_section_id'       => $filtros['section_id']       ?? null,

        ];
        return SupabaseApi::rpcWithToken('listar_period_course_resumen', $params, $accessToken);
    }
        
        
//para period list, desde el link de ver periodos de course list busca el curso para mostrar datos y filtrar la lista de periodcourse
    public static function obtener_curso_supabase(int $id, string $accessToken): array{
        return SupabaseApi::getWithToken('course', [
            'select' => 'id, name, type',
            'id' => 'eq.' . $id,
        ], $accessToken);
    }
        
        
//para period list, desde el link de ver periodos de period list busca el periodo para mostrar datos y filtrar la lista de periodcourse
    public static function obtener_periodo_supabase(int $id, string $accessToken): array{
        return SupabaseApi::getWithToken('period', [
            'select' => 'id, name, type',
            'id' => 'eq.' . $id,
        ], $accessToken);
    }
        
        
//esto es para el modal de secciones hecho con js en period list
    public static function obtener_secciones_por_period_course_supabase(
        int $periodCourseId,
        bool $soloArchivadas,
        string $accessToken
    ): array {
        $params = [
            'p_period_course_id' => $periodCourseId,
            'p_solo_archivadas' => $soloArchivadas,
        ];
        return SupabaseApi::rpcWithToken('obtener_secciones_para_period_course', $params, $accessToken);
    }    
        
        
//archivar periodo de curso sin funcion de reembolso
    public static function archivar_period_course_supabase(int $periodCourseId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('archivar_period_course', ['p_period_course_id' => $periodCourseId], $accessToken);
    }
        
        
        
       
//modal de secciones

    public static function generar_modal_secciones_js($vista_destino = "seccion-consult"){
        return '
        <div id="modalSecciones" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
            <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
                <h5 id="tituloSecciones" class="text-white text-center mb-3">Secciones</h5>
                <div id="contenedorSecciones" class="d-flex flex-wrap justify-content-center"></div>
                <button type="button" onclick="document.getElementById(\'modalSecciones\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                    <i class="fas fa-times"></i>
                </button>
            </div>
        </div>

        <script>
        (function() {
            document.addEventListener("DOMContentLoaded", function() {
                document.querySelectorAll(".ver-secciones, .ver-secciones-archivadas").forEach(btn => {
                    btn.addEventListener("click", function() {
                        const modal = document.getElementById("modalSecciones");
                        const contenedor = document.getElementById("contenedorSecciones");
                        const titulo = document.getElementById("tituloSecciones");
                        const cursoId = this.dataset.id;
                        const nombreCurso = this.dataset.nombre || "curso";
                        const soloArchivadas = this.dataset.archivadas === "1";
                        const vistaDestino = this.dataset.vista || "' . $vista_destino . '";
                        const carpeta = "admin";

                        titulo.textContent = (soloArchivadas ? "Secciones Archivadas de " : "Secciones de ") + nombreCurso;
                        contenedor.innerHTML = `<div class="text-light text-center">Cargando...</div>`;

                        const params = new URLSearchParams({
                            curso_vigente_id: cursoId,
                            solo_archivadas: soloArchivadas ? "1" : "0"
                        });

                        fetch("' . SERVERURL . 'ajax/periodoAjax.php", {
                            method: "POST",
                            headers: { "Content-Type": "application/x-www-form-urlencoded" },
                            body: params.toString()
                        })
                        .then(res => {
                            if (!res.ok) throw new Error("Error en la respuesta del servidor");
                            return res.json();
                        })
                        .then(data => {
                            contenedor.innerHTML = "";
                            // Si la respuesta es un objeto con una propiedad "data", extraerla 
                            // "bg-warning text-dark" 
                            const secciones = Array.isArray(data) ? data : (data.data || []);
                            if (!secciones.length) {
                                contenedor.innerHTML = `<span class="badge ${soloArchivadas ? "card bg-danger text-white m-2 p-2" : "bg-danger"}">No hay secciones</span>`;
                                return;
                            }
                            secciones.forEach(sec => {
                                const profName = sec.nombre_profesor || "";
                                const profLast = sec.apellido_profesor || "";
                                const profText = (profName || profLast) ? `${profName} ${profLast}` : "<em>Sin asignar</em>";
                                const card = document.createElement("div");
                                card.className = soloArchivadas ? "card bg-warning text-dark m-2 p-2" : "card bg-secondary text-white m-2 p-2";
                                card.style.width = "160px";
                                const a2Param = soloArchivadas ? "&a2=1" : "";
                                // Escapar correctamente los valores para la URL
                                const carpetaEnc = encodeURIComponent(carpeta);
                                const vistaEnc = encodeURIComponent(vistaDestino);
                                const cursoIdEnc = encodeURIComponent(cursoId);
                                //const seccionEnc = encodeURIComponent(sec.nombre_seccion || "");
                                const seccionIdEnc = encodeURIComponent(sec.id_seccion || "");
                                //    <a href="c . SERVERURL . cindex.php?views=${carpetaEnc}/${vistaEnc}&curso_vigente=${cursoIdEnc}&seccion=${seccionEnc}${a2Param}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
                                card.innerHTML = `
                                    <div class="card-body p-2 text-center">
                                        <h6 class="mb-1">Sección ${sec.nombre_seccion || "Sin nombre"}</h6>
                                        <p class="small mb-1">Id: ${sec.id_seccion || ""}</p>
                                        <p class="small mb-1">Día: ${sec.dia || ""}</p>
                                        <p class="small mb-1">Prof.: ${profText}</p>
                                        <p class="small mb-1">Inscritos: ${sec.inscritos || 0}</p>
                                        <p class="small mb-1">Evaluaciones: ${sec.evaluaciones || 0}</p>
                                        <a href="' . SERVERURL . 'index.php?views=${carpetaEnc}/${vistaEnc}&curso_vigente=${cursoIdEnc}&seccion_id=${seccionIdEnc}${a2Param}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
                                    </div>
                                `;
                                contenedor.appendChild(card);
                            });
                        })
                        .catch(err => {
                            console.error("Error al cargar secciones:", err);
                            contenedor.innerHTML = `<span class="badge bg-danger">Error al cargar secciones: ${err.message}</span>`;
                        });

                        modal.style.display = "block";
                    });
                });
            });
        })();
        </script>';
    }
    
        
        
/*----------------------------------------------a partir de aqui empiezan las secciones---------------------------------------------------------*/
        
    //esto es de seccion consult, trae las notas totales de la seccion para pdf o excel    
    public static function datos_pdf_seccion_supabase(int $sectionId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('datos_pdf_seccion', [
            'p_section_id' => $sectionId,
        ], $accessToken);
    }
        
    public static function crear_seccion_supabase(array $datos, string $accessToken): array{
        return SupabaseApi::postWithToken('section', $datos, $accessToken);
    }
        
    public static function actualizar_seccion_supabase(int $sectionId, array $data, string $accessToken): array{
    	return SupabaseApi::patchWithToken('section', ['id' => 'eq.' . $sectionId], $data, $accessToken);
	}
        

    /**
     * Obtiene el current_state del periodo asociado a una sección o period_course.
     * @param int $periodCourseId ID del period_course (si se conoce)
     * @param int|null $sectionId ID de la sección (alternativa)
     * @return string|null Estado del periodo ('Próximo', 'Vigente', 'Terminado') o null si no existe
     */
    public static function obtener_estado_periodo_de_seccion_supabase(
        string $accessToken,
		int $periodCourseId,
        ?int $sectionId = null
    ): ?string {
        if ($sectionId !== null) {
            $resultado = SupabaseApi::getWithToken('section', [
                'select' => 'period_course_id',
                'id' => 'eq.' . $sectionId,
            ], $accessToken);
            if (!$resultado['ok'] || empty($resultado['data'][0])) {
                return null;
            }
            $periodCourseId = (int) $resultado['data'][0]['period_course_id'];
        }

        // Obtener periodo desde period_course
        $resultado = SupabaseApi::getWithToken('period_course', [
            'select' => 'period!inner(current_state)',
            'id' => 'eq.' . $periodCourseId,
        ], $accessToken);

        if (!$resultado['ok'] || empty($resultado['data'][0])) {
            return null;
        }

        return $resultado['data'][0]['period']['current_state'] ?? null;
    }
        
        
    public static function eliminar_seccion_supabase(int $sectionId, string $accessToken): array{
        return SupabaseApi::rpcWithToken('eliminar_seccion', ['p_section_id' => $sectionId], $accessToken);
    }

        // Obtener datos para el modal de transferencia de estudiantes entre secciones, si es true, trae secciones y un conteo de sus estudiantes, es para el modal de transferencia
    public static function obtener_datos_transferencia_supabase(int $sectionId, string $accessToken): array{
        //obtener_datos_transferencia_estudiantes
        return SupabaseApi::rpcWithToken('aux_traer_estudiantes_de_seccion', ['p_section_id' => $sectionId,'p_incluir_hermanas' => true], $accessToken);
    }

    // Transferir estudiantes seleccionados
    public static function transferir_estudiantes_seleccionados_supabase(int $origenId, int $destinoId, array $registrationIds, string $accessToken): array{
        $params = [
            'p_section_origen_id' => $origenId,
            'p_section_destino_id' => $destinoId,
            'p_registration_ids' => $registrationIds,
        ];
        return SupabaseApi::rpcWithToken('transferir_estudiantes_seleccionados', $params, $accessToken);
    }
           
//para seccion consult, trae los datos de la seccion, su profe, y las evaluaciones de state 1 para el calculo de porcentaje y limite de evaluaciones
    public static function obtener_seccion_por_id(int $sectionId,string $accessToken): array {
        return SupabaseApi::getWithToken('section', [
            'select' => 
                'id, period_course_id, name, section_day, teacher_id, state, ' .
                'period_course!inner(course_id, period_id, ' .
                    'period!inner(name, type, start_date, end_date), ' .
                    'course!inner(name, type)' .
                '), ' .
                'teacher(id, name, last_name), ' .
                'evaluation(id, evaluation_type, percentage, state)',
            'id' => 'eq.' . $sectionId,
            'evaluation.state' => 'eq.1', // ← Filtro en el recurso anidado
        ], $accessToken);
    }
        
        

        
        
      
        

    // reportes del período: casos 1=estudiantes, 2=cursos, 3=recaudación
    public static function reportes_periodo_individual_supabase(
        int $periodId,
        int $tipo,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('reportes_periodo_individual', [
            'p_period_id' => $periodId,
            'p_tipo'      => $tipo,
        ], $accessToken);
    }

    // reportes de un period_course: 1=aprobados, 2=pagos
    public static function reportes_period_course_individual_supabase(
        int $periodCourseId,
        int $tipo,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('reportes_period_course_individual', [
            'p_period_course_id' => $periodCourseId,
            'p_tipo'             => $tipo,
        ], $accessToken);
    }
        


    }
?>