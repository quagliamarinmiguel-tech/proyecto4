<?php
if($peticionAjax){
    require_once "../modelos/imagenModelo.php";
    require_once "../modelos/cursoPublicidadModelo.php";

} else {
    require_once "./modelos/imagenModelo.php";
    require_once "./modelos/cursoPublicidadModelo.php";
}
class cursoPublicidadControlador extends cursoPublicidadModelo {





    /**
     * Obtiene los cursos sin publicidad para la vista.
     * @return array Array de cursos.
     */
    public function obtener_cursos_sin_publicidad_controlador(): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return [];
        }
        return cursoPublicidadModelo::obtener_cursos_sin_publicidad_supabase($token);
    }


        
    public function agregar_publicidad_con_imagen_controlador() {
        try {
            // 1. Validar y limpiar datos
            $courseId = (int) mainModel::limpiar_cadena($_POST['id_curso'] ?? '');
            $introduction = mainModel::limpiar_cadena($_POST['prefacio'] ?? '');
            $class = mainModel::limpiar_cadena($_POST['clase'] ?? '');
            $description = mainModel::limpiar_cadena($_POST['materia_descripcion'] ?? '');

            // Contenidos (content_1 a content_10)
            $content = [];
            for ($i = 1; $i <= 10; $i++) {
                $content["content_$i"] = mainModel::limpiar_cadena($_POST["cont$i"] ?? '');
            }

            // Requisitos (requirement_1 a requirement_4)
            $requirements = [];
            for ($i = 1; $i <= 4; $i++) {
                $requirements["requirement_$i"] = mainModel::limpiar_cadena($_POST["requisito$i"] ?? '');
            }

            // Iconos (icon_1, icon_2, icon_3)
            $icons = [
                'icon_1' => isset($_POST['icono1']) ? true : false,
                'icon_2' => isset($_POST['icono2']) ? true : false,
                'icon_3' => isset($_POST['icono3']) ? true : false,
            ];

            // 2. Validar campos obligatorios
            $faltantes = [];
            if ($courseId <= 0) $faltantes[] = "Curso";
            if ($introduction == '') $faltantes[] = "Prefacio";
            if ($description == '') $faltantes[] = "Descripción";
            if ($class == '') $faltantes[] = "Horario";

            if (!empty($faltantes)) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Campos obligatorios faltantes",
                    "Texto" => "Debes completar: " . implode(", ", $faltantes),
                    "Tipo" => "error"
                ]);
            }

            // 3. Token de Supabase
            $accessToken = $_SESSION['auth_access_token'] ?? null;
            if (!$accessToken) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            // 4. Validar que no exista publicidad activa para este curso
            $existe = SupabaseApi::getWithToken('marketing_course', [
                'select' => 'id',
                'course_id' => 'eq.' . $courseId,
                'state' => 'eq.1',
            ], $accessToken);

            if ($existe['ok'] && !empty($existe['data'])) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Ya existe publicidad",
                    "Texto" => "Este curso ya tiene una publicidad activa.",
                    "Tipo" => "error"
                ]);
            }

            // 5. Subir imagen
            if (!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] !== 0) {
                return json_encode(mainModel::generarAlertaSimple('Debes seleccionar una imagen válida.', 'error', 'simple'));
            }

            $carpetaDestino = "../vistas/cm/curs-p/";
            $resultadoImagen = mainModel::subir_imagen_fisica($_FILES['archivo_imagen'], $carpetaDestino, 'pub_');

            if (!$resultadoImagen['ok']) {
                return json_encode(mainModel::generarAlertaSimple('Error al subir imagen: ' . $resultadoImagen['mensaje'], 'error', 'simple'));
            }

            // 6. Preparar datos para la RPC
            $datos = [
                'course_id' => $courseId,
                'img_route' => $resultadoImagen['ruta'],
                'img_type' => 'curs-p',
                'introduction' => $introduction,
                'class' => $class,
                'description' => $description,
                'state' => 1,
            ] + $content + $requirements + $icons;

            // 7. Llamar a la RPC
            $resultado = cursoPublicidadModelo::crear_marketing_curso_supabase($datos, $accessToken);

            // 8. Manejar respuesta
            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                return json_encode(mainModel::generarAlertaSimple('Publicidad registrada exitosamente.', 'success', 'recargar'));
            } else {
                // Si falla, eliminar imagen
                if (file_exists($resultadoImagen['ruta_absoluta'])) {
                    unlink($resultadoImagen['ruta_absoluta']);
                }
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al guardar publicidad: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
        
        
        
        


    public function paginador_publicidad_controlador($pagina, $registros, $vista, $state = 1) {
        $pagina = max(1, (int) $pagina);
        $registros = max(1, (int) $registros);
        $token = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

        // Preparar filtros desde GET
        $filtros = [
            'busqueda' => trim($_GET['busqueda'] ?? ''),
            'tipo' => trim($_GET['filtro_tipo'] ?? '') ?: null,
            'estado' => trim($_GET['filtro_estado'] ?? '') ?: null,
            'id' => isset($_GET['filtro_id']) && $_GET['filtro_id'] !== '' ? (int) $_GET['filtro_id'] : null,
            'orden' => $_GET['orden'] ?? 'reciente',
            'state' => $state,
        ];

        $resultado = cursoPublicidadModelo::listar_marketing_cursos_supabase(
            $filtros,
            $token,
            $registros,
            ($pagina - 1) * $registros
        );

        if (!$resultado['ok']) {
            return '<div class="alert alert-danger">No se pudo cargar la lista de publicidad.</div>';
        }

        $items = $resultado['data']['data'] ?? [];
        $total = (int) ($resultado['data']['total'] ?? 0);
        $paginas = max(1, (int) ceil($total / $registros));

        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        $carpetas = [1 => 'admin', 2 => 'Control de estudio', 3 => 'administrador de pagos', 4 => 'Supervisor de cursos', 5 => 'Estudiante'];
        $carpeta = $carpetas[$cargo] ?? 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        // Generar tabla HTML (sin columna Interacciones)
        $tabla = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center roboto-medium">';
        $tabla .= '<th>ID</th><th>Curso</th><th>Tipo</th><th>Estado</th><th>Prefacio</th><th>Imagen</th>';
        if (in_array($cargo, [1, 4], true)) {
            if($state==1){$tabla .= '<th>Actualizar</th><th>Archivar</th>';}
            if($state==0){$tabla .= '<th>Restaurar</th>';}
            
            //$tabla .= '<th>Acciones</th>';
        }
        $tabla .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;
            foreach ($items as $item) {
                $id = (int) $item['id'];
                $courseName = htmlspecialchars($item['course_name'] ?? '', ENT_QUOTES, 'UTF-8');
                $courseType = htmlspecialchars($item['course_type'] ?? '', ENT_QUOTES, 'UTF-8');
                $introduction = htmlspecialchars($item['introduction'] ?? '', ENT_QUOTES, 'UTF-8');
                $imageRoute = $item['image_route'] ?? 'assets/img/default.webp';
                $estado = $item['estado'] ?? 'No vigente';
                $idEnc = mainModel::encryption($id);

                $estadoColor = match($estado) {
                    'Vigente'  => 'success',
                    'Próximo'  => 'info',
                    default    => 'info'
                };

                $tabla .= '<tr class="text-center">';
                $tabla .= '<td>' . $id . '</td>';
                $tabla .= '<td>' . $courseName . '</td>';
                $tabla .= '<td>' . $courseType . '</td>';
                $tabla .= '<td><span class="badge bg-' . $estadoColor . '" style="color:white;">' . $estado . '</span></td>';
                $tabla .= '<td>' . $introduction . '</td>';
                $tabla .= '<td>
                    <button type="button" class="btn btn-info btn-sm ver-imagen" data-src="' . SERVERURL . 'vistas/' . $imageRoute . '">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>';

                if (in_array($cargo, [1, 4], true)) {

                    if ($state == 1) {
                        
                       $tabla .= '<td>
                            <a  href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cursop-update&publicidad_id_up=' . $idEnc . '" class="btn btn-success btn-sm">
                               <i title="Actualizar" class="fas fa-sync-alt"></i>
                            </a>
                        </td>';
                        
                        $tabla .= '<td>
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/publicidadAjax.php" method="POST" data-form="archive" autocomplete="off">
                                <input type="hidden" name="publicidad_id_archive" value="' . $idEnc . '">
                                <button type="submit" class="btn btn-warning btn-sm"><i title="Archivar" class="fas fa-archive"></i></button>
                            </form>
                        </td>';
                    } else {
                        $tabla .= '<td>
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/publicidadAjax.php" method="POST" data-form="restore" autocomplete="off">
                                <input type="hidden" name="publicidad_id_restore" value="' . $idEnc . '">
                                <button title="Restaurar" type="submit" class="btn btn-info btn-sm"><i class="fas fa-undo"></i></button>
                            </form>
                        </td>';
                    }
                }
                $tabla .= '</tr>';
            }
            $regFinal = $regInicio + count($items) - 1;
        } else {
            $columnas = in_array($cargo, [1, 4]) ? 8 : 6;
            $tabla .= '<tr class="text-center"><td colspan="' . $columnas . '">No hay publicidad registrada.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador
        if ($total > 0) {
            $tabla .= '<p class="text-right">Mostrando ' . $regInicio . ' al ' . $regFinal . ' de ' . $total . '</p>';
            $paramsGet = array_filter([
                'busqueda' => $_GET['busqueda'] ?? '',
                'filtro_tipo' => $_GET['filtro_tipo'] ?? '',
                'filtro_estado' => $_GET['filtro_estado'] ?? '',
                'filtro_id' => $_GET['filtro_id'] ?? '',
                'orden' => $_GET['orden'] ?? '',
            ]);
            $queryString = http_build_query($paramsGet);
            if ($queryString) {
                $queryString = '&' . $queryString;
            }
            $tabla .= mainModel::paginador_tablas($pagina, $paginas, SERVERURL . 'index.php?views=' . $vistaCompleta . $queryString . '&page=', 7);
        }

        // Modal de imagen
        $tabla .= '
        <div id="modalImagen" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
            <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff; padding:20px; border-radius:8px;">
                <img id="imgPreview" src="" class="img-thumbnail" style="max-width:400px; width:80%; height:auto; display:block; margin:auto; border:3px solid white;">
                <button onclick="document.getElementById(\'modalImagen\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light"><i class="fas fa-times"></i></button>
            </div>
        </div>
        <script>
        document.querySelectorAll(".ver-imagen").forEach(btn => {
            btn.addEventListener("click", () => {
                const modal = document.getElementById("modalImagen");
                modal.querySelector("#imgPreview").src = btn.dataset.src;
                modal.style.display = "block";
            });
        });
        </script>';

        return $tabla;
    }
    
    
    
    
    
    
    
    
    


    // Obtener datos para la vista de actualización
    public function obtener_marketing_para_update_controlador(int $id): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible'];
        }
        $resultado = cursoPublicidadModelo::obtener_marketing_curso_supabase($id, $token);
        if (!$resultado['ok'] || empty($resultado['data'][0])) {
            return ['ok' => false, 'message' => 'Publicidad no encontrada'];
        }
        $data = $resultado['data'][0];
        return [
            'ok' => true,
            'marketing' => $data,
            'course' => $data['course'] ?? [],
            'img' => $data['img'] ?? null,
        ];
    }



    public function actualizar_marketing_controlador(): string {
        try {
            // 1. Obtener y validar ID
            $idEnc = $_POST['publicidad_id_up'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            // 2. Obtener token
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            // --- NUEVO: Obtener datos actuales para saber la ruta de imagen anterior ---
            $datosActuales = cursoPublicidadModelo::obtener_marketing_curso_supabase($idReal, $token);
            $oldImgRoute = null;
            //if ($datosActuales['ok'] && isset($datosActuales['data']['img']['route'])) {
              //  $oldImgRoute = $datosActuales['data']['img']['route'];
            //}

            if ($datosActuales['ok'] && isset($datosActuales['data'][0]['img']['route'])) {
                $oldImgRoute = $datosActuales['data'][0]['img']['route'];
            }
            //var_dump($oldImgRoute);die();
            // 3. Recoger y limpiar datos (igual que antes)
            $introduction = mainModel::limpiar_cadena($_POST['prefacio'] ?? '');
            $class = mainModel::limpiar_cadena($_POST['clase'] ?? '');
            $description = mainModel::limpiar_cadena($_POST['materia_descripcion'] ?? '');

            $content = [];
            for ($i = 1; $i <= 10; $i++) {
                $content["content_$i"] = mainModel::limpiar_cadena($_POST["cont$i"] ?? '');
            }

            $requirements = [];
            for ($i = 1; $i <= 4; $i++) {
                $requirements["requirement_$i"] = mainModel::limpiar_cadena($_POST["requisito$i"] ?? '');
            }

            $icons = [
                'icon_1' => isset($_POST['icono1']),
                'icon_2' => isset($_POST['icono2']),
                'icon_3' => isset($_POST['icono3']),
            ];

            if ($introduction == '' || $description == '' || $class == '') {
                return json_encode(mainModel::generarAlertaSimple('Prefacio, descripción y horario son obligatorios', 'error', 'simple'));
            }

            // 4. Procesar imagen (si se sube)
            $newImgRoute = null;
            $imgType = 'curs-p';
            $seSubioImagen = false;

            if (isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === 0) {
                $carpetaDestino = "../vistas/cm/curs-p/";
                $resultadoImagen = mainModel::subir_imagen_fisica($_FILES['archivo_imagen'], $carpetaDestino, 'pub_');
                if (!$resultadoImagen['ok']) {
                    return json_encode(mainModel::generarAlertaSimple('Error al subir imagen: ' . $resultadoImagen['mensaje'], 'error', 'simple'));
                }
                $newImgRoute = $resultadoImagen['ruta'];
                $seSubioImagen = true;
            }

            // 5. Preparar datos para la RPC
            $datos = [
                'marketing_id' => $idReal,
                'introduction' => $introduction,
                'class' => $class,
                'description' => $description,
                'img_route' => $newImgRoute,
                'img_type' => $imgType,
            ] + $content + $requirements + $icons;

            // 6. Llamar a la RPC
            $resultado = cursoPublicidadModelo::actualizar_marketing_curso_supabase($datos, $token);

    		// 7. Manejar respuesta y limpiar archivos
            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                // ÉXITO: eliminar la imagen anterior (si existe y es diferente a la nueva)
                if ($oldImgRoute && $seSubioImagen && $oldImgRoute !== $newImgRoute) {
                    // Construir ruta física absoluta
                    $rutaFisicaVieja = $_SERVER['DOCUMENT_ROOT'] . '/vistas/' . $oldImgRoute;
                    if (file_exists($rutaFisicaVieja)) {
                        unlink($rutaFisicaVieja);
                    }
                }
                return json_encode(mainModel::generarAlertaSimple('Publicidad actualizada correctamente', 'success', 'recargar'));
            } else {
                // ERROR: eliminar la imagen nueva subida
                if ($seSubioImagen && $newImgRoute) {
                    $rutaFisicaNueva = $_SERVER['DOCUMENT_ROOT'] . '/vistas/' . $newImgRoute;
                    if (file_exists($rutaFisicaNueva)) {
                        unlink($rutaFisicaNueva);
                    }
                }
                $mensaje = $resultado['data']['error'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al actualizar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            // En caso de excepción, también limpiar la imagen subida si existe
            if (isset($newImgRoute) && $newImgRoute && file_exists($newImgRoute)) {
                unlink($newImgRoute);
            }
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
      
    
    
    
    

    public function archivar_publicidad_controlador(): string{
        try {
            // 1. Validar ID
            $idEnc = $_POST['publicidad_id_archive'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            // 2. Obtener token de sesión
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            // 3. Llamar al modelo
            $resultado = cursoPublicidadModelo::archivar_marketing_curso_supabase($idReal, $token);

            // 4. Manejar respuesta
            if ($resultado['ok']) { //&& ($resultado['status'] ?? 0) === 200
                return json_encode(mainModel::generarAlertaSimple('Publicidad archivada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['message'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al archivar: ' . $mensaje, 'error', 'simple'));
            }
        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
    


    public function restaurar_publicidad_controlador(): string {
        try {
            // 1. Validar ID
            $idEnc = $_POST['publicidad_id_restore'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            // 2. Token de sesión
            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            // 3. Obtener datos del marketing (incluye course_id, state, y relación con course)
            $estado_cm_buscar=0;
            $datosMarketing = cursoPublicidadModelo::obtener_marketing_curso_supabase($idReal, $token,$estado_cm_buscar);
            if (!$datosMarketing['ok'] || empty($datosMarketing['data'])) {
                return json_encode(mainModel::generarAlertaSimple('Publicidad no encontrada', 'error', 'simple'));
            }
            $marketing = $datosMarketing['data'][0];

            // 4. Verificar que esté archivado (state = 0)
            /*if ((int)$marketing['state'] !== 0) {
                return json_encode(mainModel::generarAlertaSimple('La publicidad ya está activa', 'error', 'simple'));
            }*/

            $courseId = $marketing['course_id'] ?? null;
            if (!$courseId) {
                return json_encode(mainModel::generarAlertaSimple('El registro no tiene curso asociado', 'error', 'simple'));
            }

            // 5. Verificar que el curso asociado esté activo (state = 1)
            $datosCurso = SupabaseApi::getWithToken('course', ['id' => 'eq.' . $courseId], $token);
            if (!$datosCurso['ok']) {
                 			// || empty($datosCurso['data'])  
                return json_encode(mainModel::generarAlertaSimple('Curso asociado no encontrado', 'error', 'simple'));
            }
            $curso = $datosCurso['data'][0];
            if ((int)$curso['state'] !== 1) {
                return json_encode(mainModel::generarAlertaSimple('No se puede restaurar porque el curso está inactivo', 'error', 'simple'));
            }

                        
            // 6. Verificar que NO exista otro marketing_course activo (state=1) con el mismo course_id
            // Excluimos el propio registro con 'id' => 'neq.' . $idReal
            $existeActivo = SupabaseApi::getWithToken('marketing_course', [
                'course_id' => 'eq.' . $courseId,
                'state' => 'eq.1',
                'id' => 'neq.' . $idReal
            ], $token);
            if ($existeActivo['ok'] && !empty($existeActivo['data'])) {
                return json_encode(mainModel::generarAlertaSimple('Ya existe una publicidad activa para este curso', 'error', 'simple'));
            }

            // 7. Restaurar (cambiar state a 1)
            $resultado = cursoPublicidadModelo::restaurar_marketing_curso_supabase($idReal, $token);
            if ($resultado['ok']) {// && ($resultado['status'] ?? 0) === 200
                return json_encode(mainModel::generarAlertaSimple('Publicidad restaurada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['message'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al restaurar: ' . $mensaje, 'error', 'simple'));
            }

        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
    
    
    
    




/*
public function paginador_publicidad_archivada_controlador($pagina, $registros, $vista) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $conexion = mainModel::conectar();

    // Filtros
    $filtro_id     = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_tipo   = mainModel::limpiar_cadena($_GET['filtro_tipo'] ?? '');
    $orden         = $_GET['orden'] ?? '';

    $cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
$vistaCompleta = urlencode($carpeta) . '/' . $vista;
    
    
    // WHERE base
    $where = "cp.estado_curso_publicidad = 0";
    if ($filtro_id != "")     $where .= " AND cp.id_materia = '$filtro_id'";
    if ($filtro_nombre != "") $where .= " AND c.nombre_curso = '$filtro_nombre'";
    if ($filtro_tipo != "")   $where .= " AND c.tipo_curso = '$filtro_tipo'";

    // Ordenamiento
    $orden_sql = match($orden) {
        "nombre" => "ORDER BY c.nombre_curso ASC",
        default  => "ORDER BY cp.id_materia DESC"
    };

    // Consulta principal
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            cp.id_materia, 
            c.nombre_curso, 
            c.tipo_curso,
            cp.prefacio
        FROM curso_publicidad cp
        INNER JOIN curso c ON cp.id_curso = c.id_curso
        WHERE $where
        $orden_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int)$conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>CURSO</th>
                    <th>TIPO</th>
                    <th>PREFACIO</th>
                    <th>RESTAURAR</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_materia']);
            $tabla .= '
            <tr class="text-center">
                <td>' . $row['id_materia'] . '</td>
                <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                <td>' . htmlspecialchars($row['tipo_curso']) . '</td>
                <td>' . htmlspecialchars($row['prefacio']) . '</td>
                <td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/publicidadAjax.php" method="POST" data-form="restore" autocomplete="off">
                        <input type="hidden" name="publicidad_id_restore" value="' . $idEnc . '">
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="fas fa-undo"></i> Restaurar
                        </button>
                    </form>
                </td>
            </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="5">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-primary btn-sm">Recargar listado</a>'
            : 'No hay publicidades archivadas';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'filtro_id'     => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_tipo'   => $filtro_tipo,
            'orden'         => $orden
        ]);

        //            ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-primary btn-sm">Recargar listado</a>'

        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        //$urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}*/



















/*
    public function actualizar_publicidad_con_imagen_controlador() {
        try {
            $idEnc = $_POST['publicidad_id_up'] ?? '';
            $idDec = mainModel::decryption($idEnc);

            if (!is_numeric($idDec) || $idDec <= 0) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "ID inválido",
                    "Texto" => "No se recibió un ID válido para actualizar la publicidad.",
                    "Tipo" => "error"
                ]);
            }

            $prefacio = mainModel::limpiar_cadena($_POST['prefacio'] ?? '');
            $descripcion = mainModel::limpiar_cadena($_POST['materia_descripcion'] ?? '');
            $clase = mainModel::limpiar_cadena($_POST['clase'] ?? '');

            if ($prefacio == "" || $descripcion == "" || $clase == "") {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Campos requeridos vacíos",
                    "Texto" => "Debes completar el prefacio, horario y la descripción.",
                    "Tipo" => "error"
                ]);
            }

            // Validar imagen
            if (!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] !== 0) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Imagen faltante",
                    "Texto" => "No se pudo procesar la imagen enviada.",
                    "Tipo" => "error"
                ]);
            }

            $archivo = $_FILES['archivo_imagen'];
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $permitidas)) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Formato inválido",
                    "Texto" => "La imagen debe estar en formato JPG, PNG o WEBP.",
                    "Tipo" => "error"
                ]);
            }

            $nombreFinal = uniqid("pub_") . "." . $ext;
            $rutaRelativa = "cm/curs-p/" . $nombreFinal;
            $rutaCompleta = "../vistas/" . $rutaRelativa;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error de carga",
                    "Texto" => "No se pudo almacenar la nueva imagen.",
                    "Tipo" => "error"
                ]);
            }

            // Obtener imagen actual
            $conexion = mainModel::conectar();
            $query = $conexion->prepare("
                SELECT i.id_imagen, i.ruta
                FROM curso_publicidad cp
                INNER JOIN imagen i ON cp.id_imagen_materia_fk = i.id_imagen
                WHERE cp.id_materia = :ID
            ");
            $query->bindParam(":ID", $idDec, PDO::PARAM_INT);
            $query->execute();

            if ($query->rowCount() != 1) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Imagen no encontrada",
                    "Texto" => "No se pudo localizar la imagen actual.",
                    "Tipo" => "error"
                ]);
            }

            $img = $query->fetch();
            $rutaAnterior = "../vistas/" . $img['ruta'];
            $id_imagen_actual = $img['id_imagen'];

            if (file_exists($rutaAnterior)) unlink($rutaAnterior);

            $updateRuta = imagenModelo::actualizar_ruta_imagen_modelo($id_imagen_actual, $rutaRelativa);
            $updateRuta->execute();

            // Continuar con actualización general
            $_POST['imagen_cambiada'] = 1;
            return $this->actualizar_publicidad_controlador();

        } catch (Exception $e) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error interno",
                "Texto" => "Detalles: " . $e->getMessage(),
                "Tipo" => "error"
            ]);
        }
    }



    public function actualizar_publicidad_controlador() {
        $idEnc = $_POST['publicidad_id_up'] ?? '';
        $idDec = mainModel::decryption($idEnc);

        if (!is_numeric($idDec) || $idDec <= 0) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "ID inválido",
                "Texto" => "No se recibió un ID válido para la publicidad.",
                "Tipo" => "error"
            ]);
        }

        $prefacio = mainModel::limpiar_cadena($_POST['prefacio'] ?? '');
        $clase = mainModel::limpiar_cadena($_POST['clase'] ?? '');
        $descripcion = mainModel::limpiar_cadena($_POST['materia_descripcion'] ?? '');
        $imagenCambiada = intval($_POST['imagen_cambiada'] ?? 0);

        if ($prefacio == "" || $descripcion == "") {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Campos vacíos",
                "Texto" => "Prefacio y descripción son obligatorios.",
                "Tipo" => "error"
            ]);
        }

        $datos = [
            "prefacio" => $prefacio,
            "materia_descripcion" => $descripcion,
            "id_materia" => $idDec,
            "clase"=>$clase
        ];

        for ($i = 1; $i <= 10; $i++) {
            $datos["cont$i"] = mainModel::limpiar_cadena($_POST["cont$i"] ?? '');
        }

        for ($i = 1; $i <= 4; $i++) {
            $datos["requisito$i"] = mainModel::limpiar_cadena($_POST["requisito$i"] ?? '');
        }


        for ($i = 1; $i <= 3; $i++) {
            $datos["icono$i"] = isset($_POST["icono$i"]) ? 1 : 0;
        }        

        $update = cursoPublicidadModelo::actualizar_publicidad_modelo($datos);

        if ($update->rowCount() == 1) {
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Publicidad actualizada",
                "Texto" => "Los datos se han guardado correctamente.",
                "Tipo" => "success"
            ]);
        } elseif ($imagenCambiada === 1) {
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Imagen actualizada",
                "Texto" => "La imagen fue reemplazada exitosamente.",
                "Tipo" => "success"
            ]);
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Sin cambios",
                "Texto" => "No se detectaron modificaciones.",
                "Tipo" => "info"
            ]);
        }
    }*/



/*
    public function eliminar_publicidad_controlador(){
        $idEnc = $_POST['publicidad_id_del'] ?? '';
        $idDec = mainModel::decryption($idEnc);

        if(!is_numeric($idDec) || $idDec <= 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"ID inválido",
                "Texto"=>"No se recibió un ID válido de publicidad.",
                "Tipo"=>"error"
            ]);
        }

        // Conectar y obtener info de imagen
        $conexion = mainModel::conectar();
        $query = $conexion->prepare("
            SELECT cp.id_materia, i.id_imagen, i.ruta
            FROM curso_publicidad cp
            INNER JOIN imagen i ON cp.id_imagen_materia_fk = i.id_imagen
            WHERE cp.id_materia = :ID
        ");
        $query->bindParam(":ID", $idDec, PDO::PARAM_INT);
        $query->execute();

        if($query->rowCount() != 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"No encontrado",
                "Texto"=>"No se encontró la publicidad.",
                "Tipo"=>"error"
            ]);
        }

        $fila = $query->fetch();
        $id_imagen = $fila['id_imagen'];
        $ruta = "../vistas/".$fila['ruta'];

        // Borrar la publicidad
        $delPub = cursoPublicidadModelo::eliminar_publicidad_modelo($idDec);
        if($delPub->rowCount() != 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al eliminar",
                "Texto"=>"No se pudo eliminar la publicidad.",
                "Tipo"=>"error"
            ]);
        }

        // Borrar imagen física
        if(file_exists($ruta)) unlink($ruta);

        // Borrar registro de imagen
        $delImg = $conexion->prepare("DELETE FROM imagen WHERE id_imagen = :ID");
        $delImg->bindParam(":ID", $id_imagen, PDO::PARAM_INT);
        $delImg->execute();

        return json_encode([
            "Alerta"=>"recargar",
            "Titulo"=>"Eliminado correctamente",
            "Texto"=>"La publicidad fue eliminada del sistema.",
            "Tipo"=>"success"
        ]);
    }
*/


/*

    public function archivar_publicidad_controlador(){
        $idEnc = mainModel::limpiar_cadena($_POST['publicidad_id_archive']);
        $id = mainModel::decryption($idEnc);

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare("UPDATE curso_publicidad SET estado_curso_publicidad = 0 WHERE id_materia = :ID");
        $stmt->bindParam(":ID", $id, PDO::PARAM_INT);

        if($stmt->execute()){
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Publicidad archivada",
                "Texto" => "La publicidad fue movida correctamente al archivo.",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error",
                "Texto" => "No se pudo archivar la publicidad.",
                "Tipo" => "error"
            ];
        }

        return json_encode($alerta);
    }*/







/*
    public function restaurar_publicidad_controlador(){
        $idEnc = mainModel::limpiar_cadena($_POST['publicidad_id_restore']);
        $id = mainModel::decryption($idEnc);

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare("UPDATE curso_publicidad SET estado_curso_publicidad = 1 WHERE id_materia = :ID");
        $stmt->bindParam(":ID", $id, PDO::PARAM_INT);

        if($stmt->execute()){
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Publicidad restaurada",
                "Texto" => "La publicidad fue reactivada correctamente.",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error inesperado",
                "Texto" => "No se pudo restaurar la publicidad.",
                "Tipo" => "error"
            ];
        }

        return json_encode($alerta);
    }*/












}