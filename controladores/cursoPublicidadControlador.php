<?php
if($peticionAjax){
    require_once "../modelos/cursoPublicidadModelo.php";

} else {
    require_once "./modelos/cursoPublicidadModelo.php";
}
class cursoPublicidadControlador extends cursoPublicidadModelo {



    
// Obtiene los cursos sin publicidad para la vista.
    public function obtener_cursos_sin_publicidad_controlador(): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return [];
        }
        return cursoPublicidadModelo::obtener_cursos_sin_publicidad_supabase($token);
    }


        
    public function agregar_publicidad_con_imagen_controlador() {
        try {
            $courseId = (int) mainModel::limpiar_cadena($_POST['id_curso'] ?? '');
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
                'icon_1' => isset($_POST['icono1']) ? true : false,
                'icon_2' => isset($_POST['icono2']) ? true : false,
                'icon_3' => isset($_POST['icono3']) ? true : false,
            ];

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

            $accessToken = $_SESSION['auth_access_token'] ?? null;
            if (!$accessToken) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

           $existe = cursoPublicidadModelo::validar_publicidad_duplicada($courseId, $accessToken);
                
            if ($existe['ok'] && !empty($existe['data'])) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Ya existe publicidad",
                    "Texto" => "Este curso ya tiene una publicidad activa.",
                    "Tipo" => "error"
                ]);
            }

            if (!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] !== 0) {
                return json_encode(mainModel::generarAlertaSimple('Debes seleccionar una imagen válida.', 'error', 'simple'));
            }

            $carpetaDestino = "../vistas/cm/curs-p/";
            $resultadoImagen = mainModel::subir_imagen_fisica($_FILES['archivo_imagen'], $carpetaDestino, 'pub_');

            if (!$resultadoImagen['ok']) {
                return json_encode(mainModel::generarAlertaSimple('Error al subir imagen: ' . $resultadoImagen['mensaje'], 'error', 'simple'));
            }

            $datos = [
                'course_id' => $courseId,
                'img_route' => $resultadoImagen['ruta'],
                'img_type' => 'curs-p',
                'introduction' => $introduction,
                'class' => $class,
                'description' => $description,
                'state' => 1,
            ] + $content + $requirements + $icons;

            $resultado = cursoPublicidadModelo::crear_marketing_curso_supabase($datos, $accessToken);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                mainModel::invalidarCachePorPrefijo('marketing_cursos_');
                mainModel::invalidarCachePorPrefijo('cursos_publicos_');
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
        //$carpetas = [1 => 'admin', 2 => 'Control de estudio', 3 => 'administrador de pagos', 4 => 'Supervisor de cursos', 5 => 'Estudiante'];
        $carpeta = 'admin';
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
            $idEnc = $_POST['publicidad_id_up'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            //Obtener datos actuales para saber la ruta de imagen anterior ---
            $datosActuales = cursoPublicidadModelo::obtener_marketing_curso_supabase($idReal, $token);
            $oldImgRoute = null;
            //if ($datosActuales['ok'] && isset($datosActuales['data']['img']['route'])) {
              //  $oldImgRoute = $datosActuales['data']['img']['route'];
            //}

            if ($datosActuales['ok'] && isset($datosActuales['data'][0]['img']['route'])) {
                $oldImgRoute = $datosActuales['data'][0]['img']['route'];
            }
            //var_dump($oldImgRoute);die();
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

            $datos = [
                'marketing_id' => $idReal,
                'introduction' => $introduction,
                'class' => $class,
                'description' => $description,
                'img_route' => $newImgRoute,
                'img_type' => $imgType,
            ] + $content + $requirements + $icons;

            $resultado = cursoPublicidadModelo::actualizar_marketing_curso_supabase($datos, $token);

            if ($resultado['ok'] && ($resultado['data']['ok'] ?? false) === true) {
                //eliminar la imagen anterior (si existe y es diferente a la nueva)
                if ($oldImgRoute && $seSubioImagen && $oldImgRoute !== $newImgRoute) {
                    // Construir ruta física absoluta
                    $rutaFisicaVieja = $_SERVER['DOCUMENT_ROOT'] . '/vistas/' . $oldImgRoute;
                    if (file_exists($rutaFisicaVieja)) {
                        unlink($rutaFisicaVieja);
                    }
                }
                mainModel::invalidarCachePorPrefijo('marketing_cursos_');
                mainModel::invalidarCachePorPrefijo('cursos_publicos_');
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
            $idEnc = $_POST['publicidad_id_archive'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $resultado = cursoPublicidadModelo::archivar_marketing_curso_supabase($idReal, $token);

            if ($resultado['ok']) { //&& ($resultado['status'] ?? 0) === 200
                mainModel::invalidarCachePorPrefijo('marketing_cursos_');
                mainModel::invalidarCachePorPrefijo('cursos_publicos_');
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
            $idEnc = $_POST['publicidad_id_restore'] ?? '';
            $idReal = mainModel::decryption($idEnc);
            $idReal = (int) $idReal;
            if ($idReal <= 0) {
                return json_encode(mainModel::generarAlertaSimple('ID inválido', 'error', 'simple'));
            }

            $token = $_SESSION['auth_access_token'] ?? null;
            if (!$token) {
                return json_encode(mainModel::generarAlertaSimple('Sesión no disponible', 'error', 'simple'));
            }

            $estado_cm_buscar=0;
            $datosMarketing = cursoPublicidadModelo::obtener_marketing_curso_supabase($idReal, $token,$estado_cm_buscar);
            if (!$datosMarketing['ok'] || empty($datosMarketing['data'])) {
                return json_encode(mainModel::generarAlertaSimple('Publicidad no encontrada', 'error', 'simple'));
            }
            $marketing = $datosMarketing['data'][0];

            $courseId = $marketing['course_id'] ?? null;
            if (!$courseId) {
                return json_encode(mainModel::generarAlertaSimple('El registro no tiene curso asociado', 'error', 'simple'));
            }

            $datosCurso = SupabaseApi::getWithToken('course', ['id' => 'eq.' . $courseId], $token);
            if (!$datosCurso['ok']) {
                 			// || empty($datosCurso['data'])  
                return json_encode(mainModel::generarAlertaSimple('Curso asociado no encontrado', 'error', 'simple'));
            }
            $curso = $datosCurso['data'][0];
            if ((int)$curso['state'] !== 1) {
                return json_encode(mainModel::generarAlertaSimple('No se puede restaurar porque el curso está inactivo', 'error', 'simple'));
            }

                        
            // Verificar que NO exista otro marketing_course activo (state=1) con el mismo course_id
            // Excluimos el propio registro con 'id' => 'neq.' . $idReal
            $existeActivo = SupabaseApi::getWithToken('marketing_course', [
                'course_id' => 'eq.' . $courseId,
                'state' => 'eq.1',
                'id' => 'neq.' . $idReal
            ], $token);
            if ($existeActivo['ok'] && !empty($existeActivo['data'])) {
                return json_encode(mainModel::generarAlertaSimple('Ya existe una publicidad activa para este curso', 'error', 'simple'));
            }

            //Restaurar (cambiar state a 1)
            $resultado = cursoPublicidadModelo::restaurar_marketing_curso_supabase($idReal, $token);
            if ($resultado['ok']) {// && ($resultado['status'] ?? 0) === 200
                mainModel::invalidarCachePorPrefijo('marketing_cursos_');
                mainModel::invalidarCachePorPrefijo('cursos_publicos_');
                return json_encode(mainModel::generarAlertaSimple('Publicidad restaurada correctamente', 'success', 'recargar'));
            } else {
                $mensaje = $resultado['data']['message'] ?? $resultado['message'] ?? 'Error desconocido';
                return json_encode(mainModel::generarAlertaSimple('Error al restaurar: ' . $mensaje, 'error', 'simple'));
            }

        } catch (Exception $e) {
            return json_encode(mainModel::generarAlertaSimple('Error: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
    
    
   
}