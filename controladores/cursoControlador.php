<?php

if($peticionAjax){
    require_once "../modelos/cursoModelo.php";
}else{
    require_once "./modelos/cursoModelo.php";
}

class cursoControlador extends cursoModelo{
    
    
    
    //--------------- Validaciones -------------
    
    public function validar_curso_no_duplicado($nombre, $tipo, $id=null) {
        return cursoModelo::verificar_curso_duplicado($nombre, $tipo, $id);
    }

    public function validar_curso_no_duplicado_supabase($nombre, $tipo, $accessToken, $id = null): array {
        return cursoModelo::verificar_curso_duplicado_supabase($nombre, $tipo, $accessToken, $id);
    }
    
    public function validar_formato_nombre($nombre) {
        $patron = "[a-zA-ZáéíóúÁÉÍÓÚñÑ](?:[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,59})";
        return mainModel::verificar_datos($patron, $nombre);  // false = cumple, true = no cumple
    }
    
    public function validar_tipo_curso($tipo_curso){
    	$tipos_validos = ["Intensivo", "Extendido", "Técnico Profesional"];
        if (!in_array($tipo_curso, $tipos_validos)) {
            return false;
  		}
        return true;
    }
    
    
    
//---------------- datos que se traen del curso---------------------

    	//de course update, para verificar si un curso cuenta con un periodo activo
        public function verificar_curso_periodo_existente($course_id): bool {
            $id    = mainModel::decryption($course_id);
            $idInt = filter_var($id, FILTER_VALIDATE_INT);

            if ($idInt === false || $idInt <= 0) {
                return false;
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return false;
            }

            $resultado = cursoModelo::verificar_curso_periodo_supabase((int) $idInt, $token);

            return $resultado['ok'] && !empty($resultado['data']);
        }
    
    
    
    
//para course-list, si se tiene un filtro de nombre, se buscara para cargarlo en el input del filtro
        public function obtener_nombre_curso_para_filtro_controlador($id_curso) {
            $id_curso = mainModel::limpiar_cadena($id_curso);

            if(!is_numeric($id_curso)) {
                return false;
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if ($token) {
                $resultado = cursoModelo::obtener_nombre_curso_supabase((int) $id_curso, $token);
                return $resultado['ok'] && !empty($resultado['data'][0]) ? $resultado['data'][0] : false;
            }
            $resultado= cursoModelo::obtener_nombre_curso_para_filtro_modelo($id_curso);

            if($resultado && $resultado->rowCount() > 0) {
                return $resultado->fetch(PDO::FETCH_ASSOC); 
            }

        }

    
// Controlador datos curso, el general q trae todo lo q se usa pa update de curso 
//si
    public function datos_curso_controlador($tipo,$id){
        $tipo=mainModel::limpiar_cadena($tipo);
        $id=mainModel::decryption($id);
        $id=mainModel::limpiar_cadena($id);

        if ($tipo === 'Unico' && filter_var($id, FILTER_VALIDATE_INT)) {
            $token = $_SESSION['auth_access_token'] ?? null;
            if ($token) {
                $resultado = cursoModelo::obtener_curso_supabase((int) $id, $token);
                if ($resultado['ok'] && !empty($resultado['data'][0])) {
                    return $resultado['data'][0];
                }
            }
            return null;
        }

        return cursoModelo::datos_curso_modelo($tipo,$id);
    } 


    
    
    
    
    

/*--------- Controlador agregar curso ---------*/
    public function agregar_curso_controlador(){

        $nombre=mainModel::limpiar_cadena($_POST['curso_nombre_reg']);
        $tipo=mainModel::limpiar_cadena($_POST['curso_tipo_reg']);
        
        if($nombre=="" || $tipo==""){
            //mainModel::generarAlertaControlador("No has llenado todos los campos que son obligatorios","error_simple");
            mainModel::generarAlertaSimple("Todos los campos son obligatorios", "error", "simple");
        }
        
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if (!$accessToken) {
           // mainModel::generarAlertaControlador("La sesión de Supabase no está disponible", "error_simple");
            mainModel::generarAlertaSimple("La sesión de Supabase no está disponible", "error", "simple");
        }

        $validacion = $this->validar_curso_no_duplicado_supabase($nombre, $tipo, $accessToken);
        if (!$validacion['ok']) {
            mainModel::generarAlertaSimple("No se pudo validar el curso en Supabase", "error", "simple");
        }

        if (!empty($validacion['data'])) {
            mainModel::generarAlertaSimple("Ya existe un curso del mismo nombre y tipo", "error", "simple");
        }

        if($this-> validar_formato_nombre($nombre)){
          mainModel::generarAlertaSimple("El nombre del curso no coincide con el formato solicitado","error", "simple");
        }
        
        if(!$this->validar_tipo_curso($tipo)) {
           mainModel::generarAlertaSimple("Debe seleccionar un tipo de curso válido (Intensivo, Extendido, Técnico Profesional)","error", "simple");
        }
   
		$estado = "1";
        $datos_curso_reg=[
            "Nombre"=>$nombre,
            "Tipo"=>$tipo,
            "Estado"=>$estado
        ];

        $agregar_curso = cursoModelo::agregar_curso_supabase($datos_curso_reg, $accessToken);

        if($agregar_curso['ok'] && !empty($agregar_curso['data'])){
            mainModel::invalidarCachePorPrefijo('cursos_publicos_');
            //mainModel::invalidarCachePorPrefijo('marketing_cursos_');
            $alerta= mainModel::generarAlertaSimple("Los datos del curso han sido registrados con exito", "success", "recargar");

        }else{
            $texto="No hemos podido registrar el curso: " . ($agregar_curso['message'] ?? 'respuesta inválida');
            $alerta= mainModel::generarAlertaSimple($texto, "error", "simple");
        }

        echo json_encode($alerta);
    }



/*--------- Controlador actualizar curso ---------*/
    public function actualizar_curso_controlador(){

        $id=mainModel::limpiar_cadena($_POST['curso_id_up']);

        if(!filter_var($id, FILTER_VALIDATE_INT) || (int) $id <= 0){
            echo json_encode(mainModel::generarAlertaSimple("El identificador del curso no es válido", "error", "simple"));
            return;
        }
        
        $nombre=mainModel::limpiar_cadena($_POST['curso_nombre_up']);
        $tipo=mainModel::limpiar_cadena($_POST['curso_tipo_up']);
        
        if($nombre=="" || $tipo==""){
            echo json_encode(mainModel::generarAlertaSimple("No has llenado todos los campos que son obligatorios", "error", "simple"));
            return;
        }
        
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if (!$accessToken) {
            echo json_encode(mainModel::generarAlertaSimple("La sesión de Supabase no está disponible", "error", "simple"));
            return;
        }

        $validacion = $this->validar_curso_no_duplicado_supabase($nombre, $tipo, $accessToken, $id);
        if (!$validacion['ok']) {
            echo json_encode(mainModel::generarAlertaSimple("No se pudo validar el curso en Supabase", "error", "simple"));
            return;
        }

        $tipoFijo = $this->verificar_curso_periodo_existente($id);
        if ($tipoFijo) {
            echo json_encode(mainModel::generarAlertaSimple("Este curso ya se encuentra vigente en periodos, no puede cambiarse.", "error", "simple"));
            return;
        }
        
        if (!empty($validacion['data'])) {
            echo json_encode(mainModel::generarAlertaSimple("Ya existe un curso del mismo nombre y tipo", "error", "simple"));
            return;
        }
        
        if($this-> validar_formato_nombre($nombre)){
                    echo json_encode(mainModel::generarAlertaSimple("El nombre del curso no coincide con el formato solicitado", "error", "simple"));
                    return;
        }
        
        if(!$this->validar_tipo_curso($tipo)) {
                     echo json_encode(mainModel::generarAlertaSimple("Debe seleccionar un tipo de curso válido (Intensivo, Extendido, Técnico Profesional)", "error", "simple"));
                     return;
        }
        
        if(!in_array((int) ($_SESSION['cargo_spm'] ?? 0), [1, 4], true)){
                        echo json_encode(mainModel::generarAlertaSimple("No tienes los permisos necesarios para realizar esta operación", "error", "simple"));
                        return;
        }
        
        $datos_curso_up=[
            "Nombre"=>$nombre,
            "Tipo"=>$tipo,
            "ID"=>$id
        ];

        $resultado = cursoModelo::actualizar_curso_supabase($datos_curso_up, $accessToken);

        if($resultado['ok']){
            mainModel::invalidarCachePorPrefijo('cursos_publicos_');
            //mainModel::invalidarCachePorPrefijo('marketing_cursos_');
            $alerta = mainModel::generarAlertaSimple("Datos del curso actualizados con exito", "success", "recargar");
        }else{
            $alerta = mainModel::generarAlertaSimple("No hemos podido actualizar los datos: " . ($resultado['message'] ?? 'respuesta inválida'), "error", "simple");
        }

        echo json_encode($alerta);
    } 

    
    
    
   
    
    
    public function paginador_curso_supabase_controlador($pagina, $registros, $vista, $busqueda, $state = 1) {

   		$pagina    = max(1, (int) $pagina);
        $registros = max(1, (int) $registros);
        $token     = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

        $filtros = [
            'tipo'          => trim($_GET['filtro_tipo'] ?? '') ?: null,
            'has_marketing' => isset($_GET['filtro_publicidad']) && $_GET['filtro_publicidad'] !== ''
                                ? ($_GET['filtro_publicidad'] === '1' ? true : false)
                                : null,
            'id'            => isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] !== ''
                                ? (int) $_GET['filtro_nombre']
                                : null,
            'orden'         => $_GET['orden'] ?? 'name',
            'state'         => (int) $state,
        ];

        $resultado = cursoModelo::listar_cursos_supabase(
            $filtros,
            $token,
            $registros,
            ($pagina - 1) * $registros
        );

        if (empty($resultado['ok'])) {
            return '<div class="alert alert-danger">No se pudo cargar la lista de cursos.</div>';
        }

        $cursos = $resultado['data']['data']  ?? [];
        $total  = (int) ($resultado['data']['total'] ?? 0);
        $paginas = max(1, (int) ceil($total / $registros));
        
        
        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        $carpeta =  'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        $tabla = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center roboto-medium"><th>Id</th><th>Nombre</th><th>Tipo</th>';
        if ($cargo !== 5 && $state==1) {
            $tabla .= '<th>Vigente en periodo actual</th><th>Publicidad</th>';
        }
        $tabla .= '<th>Ver secciones</th>';

        if (in_array($cargo, [1, 4], true) && $state==1) {
           // $tabla .= '<th>Actualizar</th><th>Archivar</th>';
           $tabla .= '<th>Acciones</th>';
        }

        if($state==0 && in_array($cargo, [1, 4], true)){$tabla .= '<th>Restaurar</th>';}
        $tabla .= '</tr></thead><tbody>';

        foreach ($cursos as $curso) {
            $id = (int) $curso['id'];
            $nombre = htmlspecialchars((string) $curso['name'], ENT_QUOTES, 'UTF-8');
            $tipo = htmlspecialchars((string) $curso['type'], ENT_QUOTES, 'UTF-8');

            if($state==1){$destino = 'seccion-list';}
            if($state==0){$destino = 'period-course-list-archived';}

            $esVigente = (bool) $curso['has_periodo'];
            $tienePublicidad = (bool) $curso['has_publicidad'];

            $tabla .= '<tr class="text-center"><td>' . $id . '</td><td>' . $nombre . '</td><td>' . $tipo . '</td>';
            if ($cargo !== 5 && $state==1) {
                $tabla .= '<td>' . ($esVigente ? 'Sí' : 'No') . '</td><td>' . ($tienePublicidad ? '<span class="badge badge-info">Sí</span>' : '<span class="badge badge-secondary">No</span>') . '</td>';
            }
            $tabla .= '<td><a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta . '/' . $destino) . '&filtro_id_curso=' . $id . '&origen=curso" class="btn btn-info btn-sm" title="Ver cursos vigentes"><i class="fas fa-eye"></i></a></td>';

            if (in_array($cargo, [1, 4], true) && $state==1) {
                $tabla .= '<td style="white-space:nowrap;display:flex;justify-content:center;align-items:center;">
                <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta . '/course-update') . '&course_id_up=' . mainModel::encryption($id) . '" class="btn btn-success" title="actualizar y consultar">
                	<i class="fas fa-sync-alt"></i>
                </a>
              <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoAjax.php" method="POST" data-form="delete" data-entity="curso" autocomplete="off"><input type="hidden" name="curso_id_archive" value="' . mainModel::encryption($id) . '">
                	<button type="submit" title="Archivar" class="btn btn-warning">
                		<i class="fas fa-archive"></i>
                    </button>
                </form>
                </td>';
                
            }

        if($state==0 && in_array($cargo, [1, 4], true)){
            $tabla .= '<td>
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoAjax.php" method="POST" data-form="restore" data-entity="curso" autocomplete="off">
                                <input type="hidden" name="curso_id_restore" value="' . mainModel::encryption($id) . '">
                                <button type="submit" class="btn btn-info">
                                    <i class="fas fa-undo"></i> Restaurar
                                </button>
                            </form>
                       </td>';
        }


            $tabla .= '</tr>';
        }

        if ($total === 0) {
            $columnas = $cargo !== 5 ? 7 : 5;
            $tabla .= '<tr class="text-center"><td colspan="' . $columnas . '">No hay registros en el sistema</td></tr>';
        }
        $tabla .= '</tbody></table></div>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;
            $regFinal = min($regInicio + count($cursos) - 1, $total);
            $tabla .= '<p class="text-right">Mostrando curso ' . $regInicio . ' al ' . $regFinal . ' de un total de ' . $total . '</p>';
            $paramsGet = [
                'orden' => $_GET['orden'] ?? '',
                'filtro_tipo' => $_GET['filtro_tipo'] ?? '',
                'filtro_publicidad' => $_GET['filtro_publicidad'] ?? '',
                'filtro_nombre' => $_GET['filtro_nombre'] ?? '',
            ];
            $paramsGet = array_filter($paramsGet, function($v) { return $v !== ''; });
            $queryString = http_build_query($paramsGet);
            if ($queryString) {
                $queryString = '&' . $queryString;
            }
            $tabla .= mainModel::paginador_tablas($pagina, $paginas, SERVERURL . 'index.php?views=' . $vistaCompleta . $queryString . '&page=', 7);
        }

        return $tabla;
    }    


    
//archiva el curso de modo q no saldra disponible en la vista publica ni para agregarlo a un periodo, sus registros activos no seran archivados
    public function archivar_curso_controlador(): string{
        try {
            $idEnc = $_POST['curso_id_archive'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }
            $resultado = cursoModelo::archivar_curso_cascada_supabase($idReal, $token);
            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                
                mainModel::invalidarCachePorPrefijo('cursos_publicos_');
            	//mainModel::invalidarCachePorPrefijo('marketing_cursos_');
                
                return json_encode(mainModel::generarAlertaSimple($resultado['data']['message'] ?? 'Curso archivado correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al archivar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }

    
    public function restaurar_curso_controlador(): string{
        try {
            $idEnc = $_POST['curso_id_restore'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $resultado = cursoModelo::restaurar_curso_supabase($idReal, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                
                mainModel::invalidarCachePorPrefijo('cursos_publicos_');
            	//mainModel::invalidarCachePorPrefijo('marketing_cursos_');
                
                return json_encode(mainModel::generarAlertaSimple($resultado['data']['message'] ?? 'Curso restaurado correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al restaurar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
    

    
//3134
//@@@@@@@@@@@@@ reportes generales @@@@@@@@@@@@@
   
    // constantes de tipO (para los reportes)
    const GEN_TOP_INSCRITOS = 1;
    const GEN_TOP_APROBADOS = 2;
    const GEN_HISTORIAL     = 3;

    const IND_INSCRITOS     = 1;
    const IND_RECAUDACION   = 2;
    const IND_APROBADOS     = 3;
    const IND_PROFESORES    = 4;

    public function reporte_top_inscritos_controlador(int $meses = 12, int $limit = 10): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = cursoModelo::reportes_cursos_generales_supabase(self::GEN_TOP_INSCRITOS, $meses, $limit, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }
        return ['ok' => true, 'items' => $r['data']['data'] ?? []];
    }

    public function reporte_top_aprobados_controlador(int $meses = 12, int $limit = 10): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = cursoModelo::reportes_cursos_generales_supabase(self::GEN_TOP_APROBADOS, $meses, $limit, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }
        return ['ok' => true, 'items' => $r['data']['data'] ?? []];
    }

    public function reporte_historial_cursos_controlador(int $meses = 12): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = cursoModelo::reportes_cursos_generales_supabase(self::GEN_HISTORIAL, $meses, 0, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }
        return ['ok' => true, 'items' => $r['data']['data'] ?? []];
    }

    public function reporte_curso_inscritos_controlador(int $courseId): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = cursoModelo::reportes_curso_individual_supabase($courseId, self::IND_INSCRITOS, 12, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }
        return ['ok' => true, 'curso' => $r['data']['curso'] ?? [], 'items' => $r['data']['data'] ?? []];
    }

    public function reporte_curso_recaudacion_controlador(int $courseId, int $meses = 12): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = cursoModelo::reportes_curso_individual_supabase($courseId, self::IND_RECAUDACION, $meses, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }
        return ['ok' => true, 'curso' => $r['data']['curso'] ?? [], 'items' => $r['data']['data'] ?? []];
    }

    public function reporte_curso_aprobados_controlador(int $courseId, int $meses = 12): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = cursoModelo::reportes_curso_individual_supabase($courseId, self::IND_APROBADOS, $meses, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }
        return ['ok' => true, 'curso' => $r['data']['curso'] ?? [], 'items' => $r['data']['data'] ?? []];
    }

    public function reporte_curso_profesores_controlador(int $courseId): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];

        $r = cursoModelo::reportes_curso_individual_supabase($courseId, self::IND_PROFESORES, 12, $token);
        if (!$r['ok'] || empty($r['data']['ok'])) {
            return ['ok' => false, 'message' => $r['data']['error'] ?? 'No se pudieron cargar los datos'];
        }
        return ['ok' => true, 'curso' => $r['data']['curso'] ?? [], 'items' => $r['data']['data'] ?? []];
    }

    //@@@@@@@@@@@@@@@@@@@@@@@ TOP CURSOS POR INSCRITOS @@@@@@@@@@@@@@@@@@@@@@@@@@
    
//pdf
    public function generar_pdf_top_inscritos_controlador(int $meses = 12, int $limit = 10) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_top_inscritos_controlador($meses, $limit);
            if (!$datos['ok']) die($datos['message']);

            $items = $datos['items'];
            $total = array_sum(array_column($items, 'total'));

            $pdf = formatoControlador::pdfIniciar('TOP CURSOS CON MÁS INSCRITOS');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'PERÍODO:',   'text'=>'Últimos ' . $meses . ' meses'],
                ['bold'=>'LÍMITE:',    'text'=>$limit . ' cursos'],
                ['bold'=>'REGISTROS:', 'text'=>count($items)],
                ['bold'=>'TOTAL:',     'text'=>$total . ' inscritos'],
            ]);

            if (empty($items)) {
                formatoControlador::pdfSinDatos($pdf);
            } else {
                $filas = [];
                foreach ($items as $i => $it) {
                    $it['pos'] = $i + 1;
                    $it['pct'] = $total > 0 ? round(($it['total'] / $total) * 100, 1) : 0;
                    $filas[] = $it;
                }
                $columnas = [
                    ['key'=>'pos',         'label'=>'#',           'ancho'=>10, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'course_name', 'label'=>'Curso',       'ancho'=>80, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'course_type', 'label'=>'Tipo',        'ancho'=>26, 'align'=>'C'],
                    ['key'=>'total',       'label'=>'Inscritos',   'ancho'=>30, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'pct',         'label'=>'% del total', 'ancho'=>50, 'align'=>'C', 'formato'=>'percent'],
                ];
                formatoControlador::pdfTabla($pdf, $columnas, $filas);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo('Top_Inscritos', 'pdf'));
        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
//excel
    public function generar_excel_top_inscritos_controlador(int $meses = 12, int $limit = 10) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_top_inscritos_controlador($meses, $limit);
            if (!$datos['ok']) die($datos['message']);

            $items = $datos['items'];
            $total = array_sum(array_column($items, 'total'));

            $columnas = [
                ['key'=>'pos',         'label'=>'#',           'align'=>'C', 'formato'=>'int'],
                ['key'=>'course_name', 'label'=>'Curso',       'wrap'=>true],
                ['key'=>'course_type', 'label'=>'Tipo',        'align'=>'C'],
                ['key'=>'total',       'label'=>'Inscritos',   'align'=>'C', 'formato'=>'int'],
                ['key'=>'pct',         'label'=>'% del total', 'align'=>'C', 'formato'=>'percent'],
            ];
            $anchos = ['pos'=>6, 'course_name'=>40, 'course_type'=>16, 'total'=>12, 'pct'=>14];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'TOP CURSOS CON MÁS INSCRITOS', 'Top Inscritos', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'PERÍODO: Últimos ' . $meses . ' meses',
                'LÍMITE: ' . $limit . ' cursos',
                'REGISTROS: ' . count($items),
                'TOTAL: ' . $total . ' inscritos',
            ], count($columnas));

            if (empty($items)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay datos para mostrar.', count($columnas));
            } else {
                $filas = [];
                foreach ($items as $i => $it) {
                    $it['pos'] = $i + 1;
                    $it['pct'] = $total > 0 ? round(($it['total'] / $total) * 100, 1) : 0;
                    $filas[] = $it;
                }
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo('Top_Inscritos', 'xlsx'));
        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

//csv
    public function generar_csv_top_inscritos_controlador(int $meses = 12, int $limit = 10) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_top_inscritos_controlador($meses, $limit);
            if (!$datos['ok']) die($datos['message']);

            $items = $datos['items'];
            $total = array_sum(array_column($items, 'total'));

            $columnas = [
                ['key'=>'pos',         'label'=>'#',           'formato'=>'int'],
                ['key'=>'course_name', 'label'=>'Curso'],
                ['key'=>'course_type', 'label'=>'Tipo'],
                ['key'=>'total',       'label'=>'Inscritos',   'formato'=>'int'],
                ['key'=>'pct',         'label'=>'% del total', 'formato'=>'percent'],
            ];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo('Top_Inscritos', 'csv'));
            formatoControlador::csvContexto($out, [
                'TOP CURSOS CON MÁS INSCRITOS',
                'PERÍODO: Últimos ' . $meses . ' meses',
                'LÍMITE: ' . $limit . ' cursos',
                'REGISTROS: ' . count($items),
                'TOTAL: ' . $total . ' inscritos',
            ]);

            if (empty($items)) {
                formatoControlador::csvSinDatos($out, 'No hay datos para mostrar.');
            } else {
                $filas = [];
                foreach ($items as $i => $it) {
                    $it['pos'] = $i + 1;
                    $it['pct'] = $total > 0 ? round(($it['total'] / $total) * 100, 1) : 0;
                    $filas[] = $it;
                }
                formatoControlador::csvTabla($out, $columnas, $filas);
            }

            formatoControlador::csvSalida($out);
        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    //@@@@@@@@@@@@@@@@@@@@@@@@@ TOP CURSOS POR APROBADOS @@@@@@@@@@@@@@@@@@@@@@@@@@
    
//pdf
    public function generar_pdf_top_aprobados_controlador(int $meses = 12, int $limit = 10) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_top_aprobados_controlador($meses, $limit);
            if (!$datos['ok']) die($datos['message']);

            $items = $datos['items'];

            $pdf = formatoControlador::pdfIniciar('TOP CURSOS CON MÁS APROBADOS', 'L');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'PERÍODO:',   'text'=>'Últimos ' . $meses . ' meses'],
                ['bold'=>'LÍMITE:',    'text'=>$limit . ' cursos'],
                ['bold'=>'REGISTROS:', 'text'=>count($items)],
            ]);

            if (empty($items)) {
                formatoControlador::pdfSinDatos($pdf);
            } else {
                $filas = [];
                foreach ($items as $i => $it) { $it['pos'] = $i + 1; $filas[] = $it; }
                $columnas = [
                    ['key'=>'pos',                  'label'=>'#',         'ancho'=>10, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'course_name',          'label'=>'Curso',     'ancho'=>80, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'course_type',          'label'=>'Tipo',      'ancho'=>26, 'align'=>'C'],
                    ['key'=>'inscritos',            'label'=>'Inscritos', 'ancho'=>25, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'aprobados',            'label'=>'Aprobados', 'ancho'=>25, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'reprobados',           'label'=>'Reprobados','ancho'=>25, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'sin_nota',             'label'=>'Sin nota',  'ancho'=>25, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'porcentaje_aprobados', 'label'=>'% Aprob.',  'ancho'=>45, 'align'=>'C', 'formato'=>'percent'],
                ];
                formatoControlador::pdfTabla($pdf, $columnas, $filas, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'porcentaje_aprobados') return null;
                        return ((float)($row['porcentaje_aprobados'] ?? 0) >= 50)
                            ? ['bg'=>[198,239,206], 'fg'=>[0,97,0]]
                            : ['bg'=>[255,199,206], 'fg'=>[156,0,6]];
                    },
                ]);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo('Top_Aprobados', 'pdf'));
        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
    
    
//excel
    public function generar_excel_top_aprobados_controlador(int $meses = 12, int $limit = 10) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_top_aprobados_controlador($meses, $limit);
            if (!$datos['ok']) die($datos['message']);

            $items = $datos['items'];

            $columnas = [
                ['key'=>'pos',                  'label'=>'#',         'align'=>'C', 'formato'=>'int'],
                ['key'=>'course_name',          'label'=>'Curso',     'wrap'=>true],
                ['key'=>'course_type',          'label'=>'Tipo',      'align'=>'C'],
                ['key'=>'inscritos',            'label'=>'Inscritos', 'align'=>'C', 'formato'=>'int'],
                ['key'=>'aprobados',            'label'=>'Aprobados', 'align'=>'C', 'formato'=>'int'],
                ['key'=>'reprobados',           'label'=>'Reprobados','align'=>'C', 'formato'=>'int'],
                ['key'=>'sin_nota',             'label'=>'Sin nota',  'align'=>'C', 'formato'=>'int'],
                ['key'=>'porcentaje_aprobados', 'label'=>'% Aprob.',  'align'=>'C', 'formato'=>'percent'],
            ];
            $anchos = ['pos'=>5,'course_name'=>30,'course_type'=>14,'inscritos'=>9,'aprobados'=>9,'reprobados'=>10,'sin_nota'=>9,'porcentaje_aprobados'=>9];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'TOP CURSOS CON MÁS APROBADOS', 'Top Aprobados', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'PERÍODO: Últimos ' . $meses . ' meses',
                'LÍMITE: ' . $limit . ' cursos',
                'REGISTROS: ' . count($items),
            ], count($columnas));

            if (empty($items)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay datos para mostrar.', count($columnas));
            } else {
                $filas = [];
                foreach ($items as $i => $it) { $it['pos'] = $i + 1; $filas[] = $it; }
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'porcentaje_aprobados') return null;
                        return ((float)($row['porcentaje_aprobados'] ?? 0) >= 50)
                            ? ['bg'=>'C6EFCE', 'fg'=>'006100']
                            : ['bg'=>'FFC7CE', 'fg'=>'9C0006'];
                    },
                ]);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo('Top_Aprobados', 'xlsx'));
        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
    
//csv
    public function generar_csv_top_aprobados_controlador(int $meses = 12, int $limit = 10) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_top_aprobados_controlador($meses, $limit);
            if (!$datos['ok']) die($datos['message']);

            $items = $datos['items'];

            $columnas = [
                ['key'=>'pos',                  'label'=>'#',         'formato'=>'int'],
                ['key'=>'course_name',          'label'=>'Curso'],
                ['key'=>'course_type',          'label'=>'Tipo'],
                ['key'=>'inscritos',            'label'=>'Inscritos', 'formato'=>'int'],
                ['key'=>'aprobados',            'label'=>'Aprobados', 'formato'=>'int'],
                ['key'=>'reprobados',           'label'=>'Reprobados','formato'=>'int'],
                ['key'=>'sin_nota',             'label'=>'Sin nota',  'formato'=>'int'],
                ['key'=>'porcentaje_aprobados', 'label'=>'% Aprob.',  'formato'=>'percent'],
            ];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo('Top_Aprobados', 'csv'));
            formatoControlador::csvContexto($out, [
                'TOP CURSOS CON MÁS APROBADOS',
                'PERÍODO: Últimos ' . $meses . ' meses',
                'LÍMITE: ' . $limit . ' cursos',
                'REGISTROS: ' . count($items),
            ]);

            if (empty($items)) {
                formatoControlador::csvSinDatos($out, 'No hay datos para mostrar.');
            } else {
                $filas = [];
                foreach ($items as $i => $it) { $it['pos'] = $i + 1; $filas[] = $it; }
                formatoControlador::csvTabla($out, $columnas, $filas);
            }

            formatoControlador::csvSalida($out);
        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }


//@@@@@@@@@@@@@@@@@@@@@@@@ HISTORIAL DE CURSOS POR PERÍODO @@@@@@@@@@@@@@@@@@@@@@@@@@@@@@

//pdf
// no usa las funciones del controlador de reportes porque este necesitaba un diseño muy distinto
    public function generar_pdf_historial_cursos_controlador(int $meses = 12) {
        try {
            $fpdfPath = dirname(__DIR__) . "/librerias/fpdf/fpdf.php";
            if (!file_exists($fpdfPath)) die("No se encontró FPDF en: $fpdfPath");
            require_once $fpdfPath;

            if (session_status() !== PHP_SESSION_ACTIVE) {
                session_start(['name' => 'SPM']);
            }
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) die("La sesión de Supabase no está disponible");

            $datos = $this->reporte_historial_cursos_controlador($meses);
            if (!$datos['ok']) die($datos['message']);

            $cursos = $datos['items'];

            $pdf = new FPDF('P', 'mm', 'Letter');
            $pdf->SetMargins(10, 10, 10);
            $pdf->SetAutoPageBreak(true, 15);
            $pdf->AddPage();

            // Cintillo
            $rutaCintillo = mainModel::obtenerRutaImagenLocal('cintillo');
            if (stripos($rutaCintillo, 'http') === 0) {
                $rutaCintillo = parse_url($rutaCintillo, PHP_URL_PATH);
            }
            $rutaCintillo = dirname(__DIR__) . '/' . ltrim($rutaCintillo, '/');

            if (file_exists($rutaCintillo)) {
                $info = @getimagesize($rutaCintillo);
                if ($info !== false) {
                    $ratio = $info[1] / $info[0];
                    $w = 216; $h = $w * $ratio;
                    $pdf->Image($rutaCintillo, 0, 0, $w);
                    $pdf->SetY($h + 3);
                } else { $pdf->Ln(15); }
            } else { $pdf->Ln(15); }

            // Título
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, mb_convert_encoding('HISTORIAL DE CURSOS POR PERÍODO', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            $pdf->SetFont('Arial', '', 9);
            $pdf->Cell(0, 5, mb_convert_encoding('Últimos ' . $meses . ' meses', 'ISO-8859-1', 'UTF-8'), 0, 1, 'C');
            $pdf->Ln(4);

            // Cabecera
            $pdf->SetFont('Arial', 'B', 8);
            $pdf->SetFillColor(220, 220, 255);
            $pdf->Cell(55, 8, mb_convert_encoding('Curso', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            $pdf->Cell(47, 8, mb_convert_encoding('Período', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            $pdf->Cell(40, 8, mb_convert_encoding('Fechas', 'ISO-8859-1', 'UTF-8'), 1, 0, 'C', true);
            $pdf->Cell(15, 8, 'Secc.', 1, 0, 'C', true);
            $pdf->Cell(15, 8, 'Insc.', 1, 0, 'C', true);
            $pdf->Cell(24, 8, '% Aprob.', 1, 1, 'C', true);
            $pdf->SetFont('Arial', '', 8);

            foreach ($cursos as $curso) {
                $nombreCurso = $curso['course_name'] ?? '';
                $tipoCurso   = $curso['course_type'] ?? '';
                $periodos    = $curso['periodos'] ?? [];

                if (empty($periodos)) {continue;}

                // Fila del curso (encabezado de grupo, ocupa todo el ancho)
                $pdf->SetFont('Arial', 'B', 8);
                $pdf->SetFillColor(240, 240, 240);
                $pdf->Cell(196, 6, mb_convert_encoding(
                    $nombreCurso . ($tipoCurso ? ' (' . $tipoCurso . ')' : ''),
                    'ISO-8859-1', 'UTF-8'
                ), 1, 1, 'L', true);

                // Filas de cada período
                $pdf->SetFont('Arial', '', 8);
                foreach ($periodos as $p) {
                    $fechas = ($p['start_date'] ?? '') . ' / ' . ($p['end_date'] ?? '');

                    // % aprobados
                    $pct = $p['porcentaje_aprobados'];
                    $pctStr = ($pct === null || $pct === '') ? 'Sin datos' : $pct . '%';

                    // Período ocupa el espacio de Curso + Período (102mm)
                    $pdf->Cell(102, 6, mb_convert_encoding($p['period_name'] ?? '', 'ISO-8859-1', 'UTF-8'), 1, 0, 'L');
                    $pdf->Cell(40, 6, $fechas, 1, 0, 'C');
                    $pdf->Cell(15, 6, (int) ($p['secciones'] ?? 0), 1, 0, 'C');
                    $pdf->Cell(15, 6, (int) ($p['inscritos'] ?? 0), 1, 0, 'C');
                    $pdf->Cell(24, 6, $pctStr, 1, 1, 'C');
                }
            }

            $pdf->Ln(4);
            $pdf->SetFont('Arial', 'I', 7);
            $pdf->Cell(0, 4, mb_convert_encoding(
                'Secc. = Secciones | Insc. = Inscritos | % Aprob. calculado sobre los que tienen nota final',
                'ISO-8859-1', 'UTF-8'
            ), 0, 1);

            $nombreArchivo = 'Historial_Cursos_' . $meses . 'meses.pdf';
            $pdf->Output('I', $nombreArchivo);
            exit();

        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
//excel
    public function generar_excel_historial_cursos_controlador(int $meses = 12) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_historial_cursos_controlador($meses);
            if (!$datos['ok']) die($datos['message']);

            $items = $datos['items'];

            $filas = [];
            foreach ($items as $c) {
                foreach (($c['periodos'] ?? []) as $p) {
                    $filas[] = [
                        'course_name'          => $c['course_name'] ?? '',
                        'course_type'          => $c['course_type'] ?? '',
                        'period_name'          => $p['period_name'] ?? '',
                        'period_type'          => $p['period_type'] ?? '',
                        'period_state'         => $p['period_state'] ?? '',
                        'start_date'           => $p['start_date'] ?? '',
                        'end_date'             => $p['end_date'] ?? '',
                        'secciones'            => (int)($p['secciones'] ?? 0),
                        'inscritos'            => (int)($p['inscritos'] ?? 0),
                        'aprobados'            => (int)($p['aprobados'] ?? 0),
                        'reprobados'           => (int)($p['reprobados'] ?? 0),
                        'sin_nota'             => (int)($p['sin_nota'] ?? 0),
                        'porcentaje_aprobados' => $p['porcentaje_aprobados'],
                    ];
                }
            }

            $columnas = [
                ['key'=>'course_name',          'label'=>'Curso',      'wrap'=>true],
                ['key'=>'course_type',          'label'=>'Tipo',       'align'=>'C'],
                ['key'=>'period_name',          'label'=>'Período',    'align'=>'C'],
                ['key'=>'period_type',          'label'=>'Tipo P.',    'align'=>'C'],
                ['key'=>'period_state',         'label'=>'Estado',     'align'=>'C'],
                ['key'=>'start_date',           'label'=>'Inicio',     'align'=>'C', 'formato'=>'date'],
                ['key'=>'end_date',             'label'=>'Fin',        'align'=>'C', 'formato'=>'date'],
                ['key'=>'secciones',            'label'=>'Secc.',      'align'=>'C', 'formato'=>'int'],
                ['key'=>'inscritos',            'label'=>'Inscritos',  'align'=>'C', 'formato'=>'int'],
                ['key'=>'aprobados',            'label'=>'Aprobados',  'align'=>'C', 'formato'=>'int'],
                ['key'=>'reprobados',           'label'=>'Reprobados', 'align'=>'C', 'formato'=>'int'],
                ['key'=>'sin_nota',             'label'=>'Sin nota',   'align'=>'C', 'formato'=>'int'],
                ['key'=>'porcentaje_aprobados', 'label'=>'% Aprob.',   'align'=>'C', 'formato'=>'percent'],
            ];
            $anchos = ['course_name'=>25,'course_type'=>12,'period_name'=>14,'period_type'=>12,'period_state'=>12,'start_date'=>12,'end_date'=>12,'secciones'=>7,'inscritos'=>9,'aprobados'=>9,'reprobados'=>10,'sin_nota'=>8,'porcentaje_aprobados'=>9];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'HISTORIAL DE CURSOS POR PERÍODO', 'Historial', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'PERÍODO: Últimos ' . $meses . ' meses',
                'CURSOS: ' . count($items),
                'REGISTROS: ' . count($filas) . ' curso-período',
            ], count($columnas));

            if (empty($filas)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay datos para mostrar.', count($columnas));
            } else {
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'porcentaje_aprobados') return null;
                        if ($row['porcentaje_aprobados'] === null) return null;
                        return ((float)$row['porcentaje_aprobados'] >= 50)
                            ? ['bg'=>'C6EFCE', 'fg'=>'006100']
                            : ['bg'=>'FFC7CE', 'fg'=>'9C0006'];
                    },
                ]);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo('Historial_Cursos', 'xlsx'));
        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
    
//csv
    public function generar_csv_historial_cursos_controlador(int $meses = 12) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_historial_cursos_controlador($meses);
            if (!$datos['ok']) die($datos['message']);

            $items = $datos['items'];

            $filas = [];
            foreach ($items as $c) {
                foreach (($c['periodos'] ?? []) as $p) {
                    $filas[] = [
                        'course_name'          => $c['course_name'] ?? '',
                        'course_type'          => $c['course_type'] ?? '',
                        'period_name'          => $p['period_name'] ?? '',
                        'period_type'          => $p['period_type'] ?? '',
                        'period_state'         => $p['period_state'] ?? '',
                        'start_date'           => $p['start_date'] ?? '',
                        'end_date'             => $p['end_date'] ?? '',
                        'secciones'            => (int)($p['secciones'] ?? 0),
                        'inscritos'            => (int)($p['inscritos'] ?? 0),
                        'aprobados'            => (int)($p['aprobados'] ?? 0),
                        'reprobados'           => (int)($p['reprobados'] ?? 0),
                        'sin_nota'             => (int)($p['sin_nota'] ?? 0),
                        'porcentaje_aprobados' => $p['porcentaje_aprobados'],
                    ];
                }
            }

            $columnas = [
                ['key'=>'course_name',          'label'=>'Curso'],
                ['key'=>'course_type',          'label'=>'Tipo'],
                ['key'=>'period_name',          'label'=>'Período'],
                ['key'=>'period_type',          'label'=>'Tipo P.'],
                ['key'=>'period_state',         'label'=>'Estado'],
                ['key'=>'start_date',           'label'=>'Inicio',     'formato'=>'date'],
                ['key'=>'end_date',             'label'=>'Fin',        'formato'=>'date'],
                ['key'=>'secciones',            'label'=>'Secc.',      'formato'=>'int'],
                ['key'=>'inscritos',            'label'=>'Inscritos',  'formato'=>'int'],
                ['key'=>'aprobados',            'label'=>'Aprobados',  'formato'=>'int'],
                ['key'=>'reprobados',           'label'=>'Reprobados', 'formato'=>'int'],
                ['key'=>'sin_nota',             'label'=>'Sin nota',   'formato'=>'int'],
                ['key'=>'porcentaje_aprobados', 'label'=>'% Aprob.',   'formato'=>'percent'],
            ];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo('Historial_Cursos', 'csv'));
            formatoControlador::csvContexto($out, [
                'HISTORIAL DE CURSOS POR PERÍODO',
                'PERÍODO: Últimos ' . $meses . ' meses',
                'CURSOS: ' . count($items),
                'REGISTROS: ' . count($filas) . ' curso-período',
            ]);

            if (empty($filas)) {
                formatoControlador::csvSinDatos($out, 'No hay datos para mostrar.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $filas);
            }

            formatoControlador::csvSalida($out);
        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

//@@@@@@@@@@@@@@@@@@@@@@@@ INSCRITOS ACTUALES AL CURSO @@@@@@@@@@@@@@@@@@@@@@@@@@

   
//pdf
    public function generar_pdf_curso_inscritos_controlador(int $courseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_inscritos_controlador($courseId);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $filas = [];
            //'ci'                => $it['ci'] ?? '',
            foreach ($items as $i => $it) {
                $filas[] = [
                    'n'                 => $i + 1,
                    'ci'                => mainModel::decryption($it['ci'] ?? ''),
                    'nombre'            => trim(($it['student_last_name'] ?? '') . ' ' . ($it['student_name'] ?? '')),
                    'period_name'       => $it['period_name'] ?? '',
                    'period_state'      => $it['period_state'] ?? '',
                    'section_name'      => $it['section_name'] ?? '—',
                    'registration_date' => $it['registration_date'] ?? '',
                ];
            }

            $pdf = formatoControlador::pdfIniciar('INSCRITOS ACTUALES AL CURSO');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'CURSO:', 'text'=>($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : '')],
                ['bold'=>'ID:',    'text'=>($curso['id'] ?? '—')],
                ['bold'=>'TOTAL:', 'text'=>count($items) . ' inscritos'],
            ]);

            if (empty($filas)) {
                formatoControlador::pdfSinDatos($pdf);
            } else {
                $columnas = [
                    ['key'=>'n',                 'label'=>'#',                   'ancho'=>8,  'align'=>'C', 'formato'=>'int'],
                    ['key'=>'ci',                'label'=>'C.I',                 'ancho'=>24, 'align'=>'C'],
                    ['key'=>'nombre',            'label'=>'Apellidos y Nombres', 'ancho'=>70, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'period_name',       'label'=>'Período',             'ancho'=>26, 'align'=>'C', 'wrap'=>true],
                    ['key'=>'period_state',      'label'=>'Estado P.',           'ancho'=>24, 'align'=>'C'],
                    ['key'=>'section_name',      'label'=>'Sección',             'ancho'=>22, 'align'=>'C'],
                    ['key'=>'registration_date', 'label'=>'Inscripción',         'ancho'=>22, 'align'=>'C', 'formato'=>'date'],
                ];
                formatoControlador::pdfTabla($pdf, $columnas, $filas);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo('Inscritos_Curso', 'pdf'));
        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
//excel
    public function generar_excel_curso_inscritos_controlador(int $courseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_inscritos_controlador($courseId);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $filas = [];
            //'ci'                => $it['ci'] ?? '',
            foreach ($items as $i => $it) {
                $filas[] = [
                    'n'                 => $i + 1,
                    'ci'                => mainModel::decryption($it['ci'] ?? ''),
                    'nombre'            => trim(($it['student_last_name'] ?? '') . ' ' . ($it['student_name'] ?? '')),
                    'period_name'       => $it['period_name'] ?? '',
                    'period_state'      => $it['period_state'] ?? '',
                    'section_name'      => $it['section_name'] ?? '—',
                    'registration_date' => $it['registration_date'] ?? '',
                ];
            }

            $columnas = [
                ['key'=>'n',                 'label'=>'#',                   'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',                'label'=>'C.I',                 'align'=>'C'],
                ['key'=>'nombre',            'label'=>'Apellidos y Nombres', 'wrap'=>true],
                ['key'=>'period_name',       'label'=>'Período',             'align'=>'C'],
                ['key'=>'period_state',      'label'=>'Estado P.',           'align'=>'C'],
                ['key'=>'section_name',      'label'=>'Sección',             'align'=>'C'],
                ['key'=>'registration_date', 'label'=>'Inscripción',         'align'=>'C', 'formato'=>'date'],
            ];
            $anchos = ['n'=>5,'ci'=>14,'nombre'=>40,'period_name'=>14,'period_state'=>12,'section_name'=>10,'registration_date'=>12];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'INSCRITOS ACTUALES AL CURSO', 'Inscritos', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'CURSO: ' . ($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : ''),
                'ID: ' . ($curso['id'] ?? '—'),
                'TOTAL: ' . count($items) . ' inscritos',
            ], count($columnas));

            if (empty($filas)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay datos para mostrar.', count($columnas));
            } else {
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo('Inscritos_Curso', 'xlsx'));
        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
    
//csv    
    public function generar_csv_curso_inscritos_controlador(int $courseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_inscritos_controlador($courseId);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $filas = [];
            //'ci'                => $it['ci'] ?? '',
            foreach ($items as $i => $it) {
                $filas[] = [
                    'n'                 => $i + 1,
                    'ci'                => mainModel::decryption($it['ci'] ?? ''),
                    'nombre'            => trim(($it['student_last_name'] ?? '') . ' ' . ($it['student_name'] ?? '')),
                    'period_name'       => $it['period_name'] ?? '',
                    'period_state'      => $it['period_state'] ?? '',
                    'section_name'      => $it['section_name'] ?? '—',
                    'registration_date' => $it['registration_date'] ?? '',
                ];
            }

            $columnas = [
                ['key'=>'n',                 'label'=>'#',                   'formato'=>'int'],
                ['key'=>'ci',                'label'=>'C.I'],
                ['key'=>'nombre',            'label'=>'Apellidos y Nombres'],
                ['key'=>'period_name',       'label'=>'Período'],
                ['key'=>'period_state',      'label'=>'Estado P.'],
                ['key'=>'section_name',      'label'=>'Sección'],
                ['key'=>'registration_date', 'label'=>'Inscripción', 'formato'=>'date'],
            ];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo('Inscritos_Curso', 'csv'));
            formatoControlador::csvContexto($out, [
                'INSCRITOS ACTUALES AL CURSO',
                'CURSO: ' . ($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : ''),
                'ID: ' . ($curso['id'] ?? '—'),
                'TOTAL: ' . count($items) . ' inscritos',
            ]);

            if (empty($filas)) {
                formatoControlador::csvSinDatos($out, 'No hay datos para mostrar.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $filas);
            }

            formatoControlador::csvSalida($out);
        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    //@@@@@@@@@@@@@@@@@@@@@@@@@@@@ RECAUDACIÓN POR PERÍODO @@@@@@@@@@@@@@@@@@@@
    
//pdf
    public function generar_pdf_curso_recaudacion_controlador(int $courseId, int $meses = 12) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_recaudacion_controlador($courseId, $meses);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $totalRec = 0.0;
            foreach ($items as $it) $totalRec += (float)($it['recaudacion'] ?? 0);

            $pdf = formatoControlador::pdfIniciar('RECAUDACIÓN POR PERÍODO');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'CURSO:',   'text'=>($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : '')],
                ['bold'=>'ID:',      'text'=>($curso['id'] ?? '—')],
                ['bold'=>'PERÍODO:', 'text'=>'Últimos ' . $meses . ' meses'],
                ['bold'=>'TOTAL:',   'text'=>'$' . number_format($totalRec, 2, '.', ',')],
            ]);

            if (empty($items)) {
                formatoControlador::pdfSinDatos($pdf);
            } else {
                $columnas = [
                    ['key'=>'period_name',  'label'=>'Período',     'ancho'=>40, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'period_type',  'label'=>'Tipo',        'ancho'=>26, 'align'=>'C'],
                    ['key'=>'period_state', 'label'=>'Estado',      'ancho'=>26, 'align'=>'C'],
                    ['key'=>'start_date',   'label'=>'Inicio',      'ancho'=>22, 'align'=>'C', 'formato'=>'date'],
                    ['key'=>'end_date',     'label'=>'Fin',         'ancho'=>22, 'align'=>'C', 'formato'=>'date'],
                    ['key'=>'recaudacion',  'label'=>'Recaudación', 'ancho'=>60, 'align'=>'R', 'formato'=>'money_dec'],
                ];
                formatoControlador::pdfTabla($pdf, $columnas, $items);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo('Recaudacion_Curso', 'pdf'));
        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    
    
    
//excel
    public function generar_excel_curso_recaudacion_controlador(int $courseId, int $meses = 12) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_recaudacion_controlador($courseId, $meses);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $totalRec = 0.0;
            foreach ($items as $it) $totalRec += (float)($it['recaudacion'] ?? 0);

            $columnas = [
                ['key'=>'period_name',  'label'=>'Período'],
                ['key'=>'period_type',  'label'=>'Tipo',        'align'=>'C'],
                ['key'=>'period_state', 'label'=>'Estado',      'align'=>'C'],
                ['key'=>'start_date',   'label'=>'Inicio',      'align'=>'C', 'formato'=>'date'],
                ['key'=>'end_date',     'label'=>'Fin',         'align'=>'C', 'formato'=>'date'],
                ['key'=>'recaudacion',  'label'=>'Recaudación', 'align'=>'R', 'formato'=>'money_dec'],
            ];
            $anchos = ['period_name'=>22,'period_type'=>14,'period_state'=>12,'start_date'=>12,'end_date'=>12,'recaudacion'=>16];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'RECAUDACIÓN POR PERÍODO', 'Recaudación', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'CURSO: ' . ($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : ''),
                'ID: ' . ($curso['id'] ?? '—'),
                'PERÍODO: Últimos ' . $meses . ' meses',
                'TOTAL: $' . number_format($totalRec, 2, '.', ','),
            ], count($columnas));

            if (empty($items)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay datos para mostrar.', count($columnas));
            } else {
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $items, $anchos);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo('Recaudacion_Curso', 'xlsx'));
        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
//csv
    public function generar_csv_curso_recaudacion_controlador(int $courseId, int $meses = 12) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_recaudacion_controlador($courseId, $meses);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $totalRec = 0.0;
            foreach ($items as $it) $totalRec += (float)($it['recaudacion'] ?? 0);

            $columnas = [
                ['key'=>'period_name',  'label'=>'Período'],
                ['key'=>'period_type',  'label'=>'Tipo'],
                ['key'=>'period_state', 'label'=>'Estado'],
                ['key'=>'start_date',   'label'=>'Inicio',      'formato'=>'date'],
                ['key'=>'end_date',     'label'=>'Fin',         'formato'=>'date'],
                ['key'=>'recaudacion',  'label'=>'Recaudación', 'formato'=>'money_dec'],
            ];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo('Recaudacion_Curso', 'csv'));
            formatoControlador::csvContexto($out, [
                'RECAUDACIÓN POR PERÍODO',
                'CURSO: ' . ($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : ''),
                'ID: ' . ($curso['id'] ?? '—'),
                'PERÍODO: Últimos ' . $meses . ' meses',
                'TOTAL: $' . number_format($totalRec, 2, '.', ','),
            ]);

            if (empty($items)) {
                formatoControlador::csvSinDatos($out, 'No hay datos para mostrar.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $items);
            }

            formatoControlador::csvSalida($out);
        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
    
    //@@@@@@@@@@@@@@@@@@ APROBADOS Y REPROBADOS POR PERÍODO @@@@@@@@@@@@@@@@@@@@@@@@

//pdf    
    public function generar_pdf_curso_aprobados_controlador(int $courseId, int $meses = 12) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_aprobados_controlador($courseId, $meses);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $pdf = formatoControlador::pdfIniciar('APROBADOS Y REPROBADOS POR PERÍODO');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'CURSO:',     'text'=>($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : '')],
                ['bold'=>'ID:',        'text'=>($curso['id'] ?? '—')],
                ['bold'=>'PERÍODO:',   'text'=>'Últimos ' . $meses . ' meses'],
                ['bold'=>'REGISTROS:', 'text'=>count($items) . ' período(s)'],
            ]);

            if (empty($items)) {
                formatoControlador::pdfSinDatos($pdf);
            } else {
                $columnas = [
                    ['key'=>'period_name',          'label'=>'Período',     'ancho'=>36, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'inscritos',            'label'=>'Inscritos',   'ancho'=>22, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'aprobados',            'label'=>'Aprobados',   'ancho'=>22, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'reprobados',           'label'=>'Reprobados',  'ancho'=>24, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'sin_nota',             'label'=>'Sin nota',    'ancho'=>22, 'align'=>'C', 'formato'=>'int'],
                    ['key'=>'porcentaje_aprobados', 'label'=>'% Aprob.',    'ancho'=>70, 'align'=>'C', 'formato'=>'percent'],
                ];
                formatoControlador::pdfTabla($pdf, $columnas, $items, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'porcentaje_aprobados') return null;
                        return ((float)($row['porcentaje_aprobados'] ?? 0) >= 50)
                            ? ['bg'=>[198,239,206], 'fg'=>[0,97,0]]
                            : ['bg'=>[255,199,206], 'fg'=>[156,0,6]];
                    },
                ]);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo('Aprobados_Curso', 'pdf'));
        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }
    
    
//excel
    public function generar_excel_curso_aprobados_controlador(int $courseId, int $meses = 12) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_aprobados_controlador($courseId, $meses);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $columnas = [
                ['key'=>'period_name',          'label'=>'Período'],
                ['key'=>'inscritos',            'label'=>'Inscritos',   'align'=>'C', 'formato'=>'int'],
                ['key'=>'aprobados',            'label'=>'Aprobados',   'align'=>'C', 'formato'=>'int'],
                ['key'=>'reprobados',           'label'=>'Reprobados',  'align'=>'C', 'formato'=>'int'],
                ['key'=>'sin_nota',             'label'=>'Sin nota',    'align'=>'C', 'formato'=>'int'],
                ['key'=>'porcentaje_aprobados', 'label'=>'% Aprob.',    'align'=>'C', 'formato'=>'percent'],
            ];
            $anchos = ['period_name'=>20,'inscritos'=>10,'aprobados'=>10,'reprobados'=>10,'sin_nota'=>9,'porcentaje_aprobados'=>11];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'APROBADOS Y REPROBADOS POR PERÍODO', 'Aprobados', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'CURSO: ' . ($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : ''),
                'ID: ' . ($curso['id'] ?? '—'),
                'PERÍODO: Últimos ' . $meses . ' meses',
                'REGISTROS: ' . count($items) . ' período(s)',
            ], count($columnas));

            if (empty($items)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay datos para mostrar.', count($columnas));
            } else {
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $items, $anchos, [
                    'color_cell' => function(array $row, array $col) {
                        if ($col['key'] !== 'porcentaje_aprobados') return null;
                        return ((float)($row['porcentaje_aprobados'] ?? 0) >= 50)
                            ? ['bg'=>'C6EFCE', 'fg'=>'006100']
                            : ['bg'=>'FFC7CE', 'fg'=>'9C0006'];
                    },
                ]);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo('Aprobados_Curso', 'xlsx'));
        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
    
//csv
    public function generar_csv_curso_aprobados_controlador(int $courseId, int $meses = 12) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_aprobados_controlador($courseId, $meses);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $columnas = [
                ['key'=>'period_name',          'label'=>'Período'],
                ['key'=>'inscritos',            'label'=>'Inscritos',   'formato'=>'int'],
                ['key'=>'aprobados',            'label'=>'Aprobados',   'formato'=>'int'],
                ['key'=>'reprobados',           'label'=>'Reprobados',  'formato'=>'int'],
                ['key'=>'sin_nota',             'label'=>'Sin nota',    'formato'=>'int'],
                ['key'=>'porcentaje_aprobados', 'label'=>'% Aprob.',    'formato'=>'percent'],
            ];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo('Aprobados_Curso', 'csv'));
            formatoControlador::csvContexto($out, [
                'APROBADOS Y REPROBADOS POR PERÍODO',
                'CURSO: ' . ($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : ''),
                'ID: ' . ($curso['id'] ?? '—'),
                'PERÍODO: Últimos ' . $meses . ' meses',
                'REGISTROS: ' . count($items) . ' período(s)',
            ]);

            if (empty($items)) {
                formatoControlador::csvSinDatos($out, 'No hay datos para mostrar.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $items);
            }

            formatoControlador::csvSalida($out);
        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

//@@@@@@@@@@@@@@@ PROFESORES QUE DICTARON EL CURSO @@@@@@@@@@@@@@@@

    
//pdf
    public function generar_pdf_curso_profesores_controlador(int $courseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_profesores_controlador($courseId);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $filas = [];
            foreach ($items as $i => $it) {
                $periodos = [];
                foreach (($it['periodos'] ?? []) as $p) {
                    $nombre = $p['period_name'] ?? '';
                    if (!empty($p['period_state'])) $nombre .= ' (' . $p['period_state'] . ')';
                    $periodos[] = $nombre;
                }
                $filas[] = [
                    'n'        => $i + 1,
                    'ci'       => $it['ci'] ?? '',
                    'nombre'   => trim(($it['last_name'] ?? '') . ' ' . ($it['name'] ?? '')),
                    'email'    => $it['email'] ?? '',
                    'phone'    => $it['phone'] ?? '',
                    'periodos' => implode(', ', $periodos),
                ];
            }

            $pdf = formatoControlador::pdfIniciar('PROFESORES QUE DICTARON EL CURSO');

            formatoControlador::pdfContexto($pdf, [
                ['bold'=>'CURSO:', 'text'=>($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : '')],
                ['bold'=>'ID:',    'text'=>($curso['id'] ?? '—')],
                ['bold'=>'TOTAL:', 'text'=>count($items) . ' profesor(es)'],
            ]);

            if (empty($filas)) {
                formatoControlador::pdfSinDatos($pdf);
            } else {
                $columnas = [
                    ['key'=>'n',        'label'=>'#',                   'ancho'=>8,  'align'=>'C', 'formato'=>'int'],
                    ['key'=>'ci',       'label'=>'C.I',                 'ancho'=>22, 'align'=>'C'],
                    ['key'=>'nombre',   'label'=>'Apellidos y Nombres', 'ancho'=>60, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'email',    'label'=>'Email',               'ancho'=>42, 'align'=>'L', 'wrap'=>true],
                    ['key'=>'phone',    'label'=>'Teléfono',            'ancho'=>24, 'align'=>'C'],
                    ['key'=>'periodos', 'label'=>'Períodos',            'ancho'=>40, 'align'=>'L', 'wrap'=>true],
                ];
                formatoControlador::pdfTabla($pdf, $columnas, $filas);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::pdfFooter($pdf, ['ubicacion' => $direccion]);

            formatoControlador::pdfSalida($pdf, formatoControlador::nombreArchivo('Profesores_Curso', 'pdf'));
        } catch (Throwable $e) {
            die("Error al generar PDF: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

    
//excel
    public function generar_excel_curso_profesores_controlador(int $courseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_profesores_controlador($courseId);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $filas = [];
            foreach ($items as $i => $it) {
                $periodos = [];
                foreach (($it['periodos'] ?? []) as $p) {
                    $nombre = $p['period_name'] ?? '';
                    if (!empty($p['period_state'])) $nombre .= ' (' . $p['period_state'] . ')';
                    $periodos[] = $nombre;
                }
                $filas[] = [
                    'n'        => $i + 1,
                    'ci'       => $it['ci'] ?? '',
                    'nombre'   => trim(($it['last_name'] ?? '') . ' ' . ($it['name'] ?? '')),
                    'email'    => $it['email'] ?? '',
                    'phone'    => $it['phone'] ?? '',
                    'periodos' => implode(', ', $periodos),
                ];
            }

            $columnas = [
                ['key'=>'n',        'label'=>'#',                   'align'=>'C', 'formato'=>'int'],
                ['key'=>'ci',       'label'=>'C.I',                 'align'=>'C'],
                ['key'=>'nombre',   'label'=>'Apellidos y Nombres', 'wrap'=>true],
                ['key'=>'email',    'label'=>'Email',               'wrap'=>true],
                ['key'=>'phone',    'label'=>'Teléfono',            'align'=>'C'],
                ['key'=>'periodos', 'label'=>'Períodos',            'wrap'=>true],
            ];
            $anchos = ['n'=>5,'ci'=>12,'nombre'=>30,'email'=>25,'phone'=>14,'periodos'=>30];
            $anchoTotalPx = formatoControlador::excelAnchoTotalPx($columnas, $anchos);

            $excel = formatoControlador::excelIniciar(
                'PROFESORES QUE DICTARON EL CURSO', 'Profesores', count($columnas), $anchoTotalPx
            );
            $sheet = $excel['sheet'];
            $fila  = $excel['fila'];

            $fila = formatoControlador::excelContexto($sheet, $fila, [
                'CURSO: ' . ($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : ''),
                'ID: ' . ($curso['id'] ?? '—'),
                'TOTAL: ' . count($items) . ' profesor(es)',
            ], count($columnas));

            if (empty($filas)) {
                $fila = formatoControlador::excelSinDatos($sheet, $fila, 'No hay datos para mostrar.', count($columnas));
            } else {
                $fila = formatoControlador::excelTabla($sheet, $fila, $columnas, $filas, $anchos);
            }

            $jsonLocal    = mainModel::leer_json_local(['contacto']);
            $contactoJSON = $jsonLocal['contacto'] ?? [];
            $direccion    = trim($contactoJSON['country_address'] ?? '') ?: 'Valle de la Pascua';
            formatoControlador::excelFooter($sheet, $fila + 1, [
                'ubicacion' => $direccion, 'num_cols' => count($columnas),
            ]);

            formatoControlador::excelSalida($excel['spreadsheet'], formatoControlador::nombreArchivo('Profesores_Curso', 'xlsx'));
        } catch (Throwable $e) {
            die("Error al generar Excel: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

//csv
    public function generar_csv_curso_profesores_controlador(int $courseId) {
        try {
            if (session_status() !== PHP_SESSION_ACTIVE) session_start(['name' => 'SPM']);
            require_once 'formatoreporteControlador.php';

            $datos = $this->reporte_curso_profesores_controlador($courseId);
            if (!$datos['ok']) die($datos['message']);

            $curso = $datos['curso'];
            $items = $datos['items'];

            $filas = [];
            foreach ($items as $i => $it) {
                $periodos = [];
                foreach (($it['periodos'] ?? []) as $p) {
                    $nombre = $p['period_name'] ?? '';
                    if (!empty($p['period_state'])) $nombre .= ' (' . $p['period_state'] . ')';
                    $periodos[] = $nombre;
                }
                $filas[] = [
                    'n'        => $i + 1,
                    'ci'       => $it['ci'] ?? '',
                    'nombre'   => trim(($it['last_name'] ?? '') . ' ' . ($it['name'] ?? '')),
                    'email'    => $it['email'] ?? '',
                    'phone'    => $it['phone'] ?? '',
                    'periodos' => implode(', ', $periodos),
                ];
            }

            $columnas = [
                ['key'=>'n',        'label'=>'#',                   'formato'=>'int'],
                ['key'=>'ci',       'label'=>'C.I'],
                ['key'=>'nombre',   'label'=>'Apellidos y Nombres'],
                ['key'=>'email',    'label'=>'Email'],
                ['key'=>'phone',    'label'=>'Teléfono'],
                ['key'=>'periodos', 'label'=>'Períodos'],
            ];

            $out = formatoControlador::csvIniciar(formatoControlador::nombreArchivo('Profesores_Curso', 'csv'));
            formatoControlador::csvContexto($out, [
                'PROFESORES QUE DICTARON EL CURSO',
                'CURSO: ' . ($curso['name'] ?? '') . (!empty($curso['type']) ? ' (' . $curso['type'] . ')' : ''),
                'ID: ' . ($curso['id'] ?? '—'),
                'TOTAL: ' . count($items) . ' profesor(es)',
            ]);

            if (empty($filas)) {
                formatoControlador::csvSinDatos($out, 'No hay datos para mostrar.');
            } else {
                formatoControlador::csvTabla($out, $columnas, $filas);
            }

            formatoControlador::csvSalida($out);
        } catch (Throwable $e) {
            die("Error al generar CSV: " . $e->getMessage() . " en " . $e->getFile() . ":" . $e->getLine());
        }
    }

//fin controlador y excels, fueron como 21 casos creo
}
?>
