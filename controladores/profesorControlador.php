<?php
if($peticionAjax){
    require_once "../modelos/profesorModelo.php";
}else{
    require_once "./modelos/profesorModelo.php";
}

class profesorControlador extends profesorModelo{

    
    
    public function validar_cedula_no_duplicado(string $ci, ?int $id_excluir = null): array{
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        return mainModel::verificar_cedula_duplicada_supabase($ci, "teacher", $accessToken, $id_excluir);
    }

    public function validar_correo_no_duplicado(string $correo, ?int $id_excluir = null): array{
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        return mainModel::verificar_correo_duplicado_supabase($correo, "teacher", $accessToken, $id_excluir);
    }

    public function validar_telefono_no_duplicado(string $telefono, ?int $id_excluir = null): array{
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        return mainModel::verificar_telefono_duplicado_supabase($telefono, "teacher", $accessToken, $id_excluir);
    }
    
    public function validar_telefono_formato($telefono) {
        return mainModel::validar_telefono_universal($telefono);
    }

    
//de profe update para traer sus datos
    public function datos_profesor_controlador($id){
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);
		$intid = (int)$id;
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if (!$accessToken) {
            echo json_encode (mainModel::generarAlertaSimple('La sesión de Supabase no está disponible.', 'error', 'simple'));
            return;
        }
        return profesorModelo::obtener_profesor_con_imagen_supabase($intid,$accessToken);
    }  

    
    public function obtener_nombre_profesor_para_filtro_controlador($id_profesor){
        $id_profesor = mainModel::limpiar_cadena($id_profesor);
        if (!is_numeric($id_profesor)) {return false;}

        $token = $_SESSION['auth_access_token'] ?? null;
        if ($token) {
            $resultado = profesorModelo::obtener_profesor_supabase((int) $id_profesor, $token);
            return $resultado['ok'] && !empty($resultado['data'][0]) ? $resultado['data'][0] : false;
        }
        return false;
    }
    
    
    
    
    
    

    public function agregar_profesor_controlador(){
        $ci = strtoupper(mainModel::limpiar_cadena($_POST['ci_reg'] ?? ''));
        $nombre = mainModel::limpiar_cadena($_POST['nombre_reg'] ?? '');
        $apellido = mainModel::limpiar_cadena($_POST['apellido_reg'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['correo_reg'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['telefono_reg'] ?? '');
        $mostrar = (int) ($_POST['profe_mostrar'] ?? 0);
        $descripcion = ($mostrar == 1) ? mainModel::limpiar_cadena($_POST['descripcion_reg'] ?? '') : '';

        if ($ci == '' || $nombre == '' || $apellido == '' || $correo == '') {
            echo json_encode (mainModel::generarAlertaSimple('Todos los campos obligatorios deben estar llenos.', 'error', 'simple'));
            return;
        }

        if (!preg_match('/^[VE]{1}[0-9]{5,9}$/', $ci)) {
            echo json_encode (mainModel::generarAlertaSimple('La cédula debe comenzar con V o E seguida de 5 a 9 números.', 'error', 'simple'));
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode (mainModel::generarAlertaSimple('El correo electrónico no es válido.', 'error', 'simple'));
            return;
        }

        if ($telefono != '' && !preg_match('/^[0-9]{7,12}$/', $telefono)) {
            echo json_encode (mainModel::generarAlertaSimple('El teléfono debe tener entre 7 y 12 dígitos.', 'error', 'simple'));
            return;
        }
        
        if ($telefono != '') {
            $tlfnoformato=self::validar_telefono_formato($telefono);
			if($tlfnoformato){
                echo json_encode (mainModel::generarAlertaSimple('Teléfono debe tener 11 digitos y la compañía movil o zona geográfica del número deben ser de u operar en Guárico.', 'error', 'simple'));
            	return;
            }        
        }

        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if (!$accessToken) {
            echo json_encode (mainModel::generarAlertaSimple('La sesión de Supabase no está disponible.', 'error', 'simple'));
            return;
        }
   		        
        $this->validar_cedula_no_duplicado($ci); 
        if (!empty($this->data)) {
            echo json_encode(mainModel::generarAlertaSimple("Ya existe un profesor con esa cédula", "error", "simple"));
            return;
        }
        
        $this->validar_correo_no_duplicado($correo); 
        if (!empty($this->data)) {
            echo json_encode(mainModel::generarAlertaSimple("Ya existe un profesor con ese correo", "error", "simple"));
            return;
        }
        
        $this->validar_telefono_no_duplicado($telefono); 
        if (!empty($this->data)) {
            echo json_encode(mainModel::generarAlertaSimple("Ya existe un profesor con ese teléfono", "error", "simple"));
            return;
        }
        
        //  SUBIR IMAGEN 
        $rutaImagen = null;
        $archivoSubido = false;
        $rutaAbsoluta = null;

        if ($mostrar == 1) {
            // Validar que se haya enviado imagen
            if (!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] !== 0) {
                echo json_encode (mainModel::generarAlertaSimple('Debes seleccionar una imagen válida.', 'error', 'simple'));
                return;
            }

            // Subir imagen
            $carpetaDestino = "../vistas/cm/prof/";
            $resultadoImagen = mainModel::subir_imagen_fisica($_FILES['archivo_imagen'], $carpetaDestino, 'prof_');

            if (!$resultadoImagen['ok']) {
                echo json_encode (mainModel::generarAlertaSimple('Error al subir la imagen: ' . $resultadoImagen['mensaje'], 'error', 'simple'));
                return;
            }

            $rutaImagen = $resultadoImagen['ruta'];
            $archivoSubido = true;
            $rutaAbsoluta = $resultadoImagen['ruta_absoluta'];
        } else {
            // Caso: show_teacher = 0 → usar imagen por defecto
            $rutaOrigen = "../vistas/assets/img/default.webp";
            $carpetaDestino = "../vistas/cm/prof/";

            if (!file_exists($rutaOrigen)) {
                echo json_encode (mainModel::generarAlertaSimple('No se encontró la imagen por defecto.', 'error', 'simple'));
                return;
            }

            if (!file_exists($carpetaDestino)) {
                mkdir($carpetaDestino, 0777, true);
            }

            $nombreCopia = uniqid('prof_default_') . '.webp';
            $rutaCompleta = $carpetaDestino . $nombreCopia;

            if (copy($rutaOrigen, $rutaCompleta)) {
                $rutaImagen = "cm/prof/" . $nombreCopia;
                $archivoSubido = true;
                $rutaAbsoluta = $rutaCompleta;
            } else {
                echo json_encode (mainModel::generarAlertaSimple('No se pudo copiar la imagen por defecto.', 'error', 'simple'));
                return;
            }
        }
        
        $showteacher=$mostrar;
        if($mostrar==1){$showteacher= true;}
        else{$showteacher=false;}
        
        
        $params = [
            'ci' => $ci,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'telefono' => $telefono,
            'correo' => $correo,
            'descripcion' => $descripcion,
            'mostrar' => $mostrar, 
            'ruta_imagen' => $rutaImagen
        ];
		$guardar= profesorModelo::guardar_profesor_con_imagen_supabase($params, $accessToken);

        if ($guardar['ok']) {
                mainModel::invalidarCachePorPrefijo('profesores_');
            echo json_encode (mainModel::generarAlertaSimple('Profesor registrado exitosamente.', 'success', 'recargar'));
        } else {
            if ($archivoSubido && isset($rutaAbsoluta) && file_exists($rutaAbsoluta)) {
                unlink($rutaAbsoluta);
            }
            echo json_encode (mainModel::generarAlertaSimple('Error al guardar el profesor: ' . ($guardar['message'] ?? 'Error desconocido'), 'error', 'simple'));
        }
    }
    
    
    
    
    public function actualizar_profesor_controlador(){
        // 1. Validar ID
        $idEnc = $_POST['profesor_id_up'] ?? '';
        $idDec = mainModel::decryption($idEnc);
        if (!is_numeric($idDec) || $idDec <= 0) {
            echo json_encode(mainModel::generarAlertaSimple('ID inválido.', 'error', 'simple'));
            return;
        }

        $ci = strtoupper(mainModel::limpiar_cadena($_POST['ci_reg'] ?? ''));
        $nombre = mainModel::limpiar_cadena($_POST['nombre_reg'] ?? '');
        $apellido = mainModel::limpiar_cadena($_POST['apellido_reg'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['correo_reg'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['telefono_reg'] ?? '');
        $mostrar = (bool) ($_POST['profe_mostrar'] ?? false);
        $descripcion = ($mostrar == 1) ? mainModel::limpiar_cadena($_POST['descripcion_reg'] ?? '') : '';
        $imagenCambiada = isset($_POST['imagen_cambiada']) ? (int) $_POST['imagen_cambiada'] : 0;

        if ($ci == '' || $nombre == '' || $apellido == '' || $correo == '') {
            echo json_encode(mainModel::generarAlertaSimple('Todos los campos obligatorios deben estar llenos.', 'error', 'simple'));
            return;
        }

        if (!preg_match('/^[VE]{1}[0-9]{5,9}$/', $ci)) {
            echo json_encode(mainModel::generarAlertaSimple('La cédula debe comenzar con V o E seguida de 5 a 9 números.', 'error', 'simple'));
            return;
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            echo json_encode(mainModel::generarAlertaSimple('El correo electrónico no es válido.', 'error', 'simple'));
            return;
        }

        if ($telefono != '' && !preg_match('/^[0-9]{7,12}$/', $telefono)) {
            echo json_encode(mainModel::generarAlertaSimple('El teléfono debe tener entre 7 y 12 dígitos.', 'error', 'simple'));
            return;
        }

        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if (!$accessToken) {
            echo json_encode(mainModel::generarAlertaSimple('La sesión de Supabase no está disponible.', 'error', 'simple'));
            return;
        }

        $profesorActual = profesorModelo::obtener_profesor_con_imagen_supabase($idDec, $accessToken);
        if (!$profesorActual['ok'] || empty($profesorActual['data'][0])) {
            echo json_encode(mainModel::generarAlertaSimple('No se encontró el profesor.', 'error', 'simple'));
            return;
        }
        $profe = $profesorActual['data'][0];
        $rutaImagenAnterior = $profe['img']['route'] ?? null;

        $this->validar_cedula_no_duplicado($ci); 
        if (!empty($this->data)) {
            echo json_encode(mainModel::generarAlertaSimple("Ya existe un profesor con esa cédula", "error", "simple"));
            return;
        }
        
        $this->validar_correo_no_duplicado($correo); 
        if (!empty($this->data)) {
            echo json_encode(mainModel::generarAlertaSimple("Ya existe un profesor con ese correo", "error", "simple"));
            return;
        }
        
        $this->validar_telefono_no_duplicado($telefono); 
        if (!empty($this->data)) {
            echo json_encode(mainModel::generarAlertaSimple("Ya existe un profesor con ese teléfono", "error", "simple"));
            return;
        }

        $nuevaRutaImagen = null;
        $archivoSubido = false;
        $rutaAbsolutaNueva = null;

        if ($imagenCambiada == 1 && isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === 0) {
            $carpetaDestino = "../vistas/cm/prof/";
            $resultadoImagen = mainModel::subir_imagen_fisica($_FILES['archivo_imagen'], $carpetaDestino, 'prof_');
            if (!$resultadoImagen['ok']) {
                echo json_encode(mainModel::generarAlertaSimple('Error al subir la imagen: ' . $resultadoImagen['mensaje'], 'error', 'simple'));
                return;
            }
            $nuevaRutaImagen = $resultadoImagen['ruta'];
            $rutaAbsolutaNueva = $resultadoImagen['ruta_absoluta'];
            $archivoSubido = true;
        }

        $datosActualizar = [
            'id' => $idDec,
            'ci' => $ci,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'correo' => $correo,
            'telefono' => $telefono,
            'descripcion' => $descripcion,
            'mostrar' => $mostrar,
            'nueva_ruta_imagen' => $nuevaRutaImagen, 
        ];

        $resultado = profesorModelo::actualizar_profesor_con_imagen_supabase($datosActualizar, $accessToken);

        if ($resultado['ok']) {
            // Éxito: si se cambió la imagen, eliminar la anterior del disco
            if ($archivoSubido && $rutaImagenAnterior && file_exists("../vistas/" . $rutaImagenAnterior)) {
                unlink("../vistas/" . $rutaImagenAnterior);
            }
                mainModel::invalidarCachePorPrefijo('profesores_');
            echo json_encode(mainModel::generarAlertaSimple('Profesor actualizado exitosamente.', 'success', 'recargar'));
        } else {
            // Fallo: eliminar la imagen nueva subida (si existe)
            if ($archivoSubido && $rutaAbsolutaNueva && file_exists($rutaAbsolutaNueva)) {
                unlink($rutaAbsolutaNueva);
            }
            echo json_encode(mainModel::generarAlertaSimple('Error al actualizar el profesor: ' . ($resultado['message'] ?? 'Error desconocido'), 'error', 'simple'));
        }
    }
  
  

//lista de proesores de profesor list
    public function paginador_profesor_controlador($pagina, $registros, $vista, $busqueda, $state) {
        $pagina = max(1, (int) $pagina);
        $registros = 15;
        $token = $_SESSION['auth_access_token'] ?? null;

        if (!$token) {
            return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
        }

        
        $idFiltro = null;
        if (isset($_GET['filtro_id']) && $_GET['filtro_id'] !== '') {
            $idFiltro = (int) $_GET['filtro_id'];
        } elseif (isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] !== '') {
            $idFiltro = (int) $_GET['filtro_nombre'];
        }
        
        $filtros = [
            'state'    => (int) $state,
            'busqueda' => trim($busqueda) ?: null,
            'id'       => $idFiltro,
            'ci'       => trim($_GET['filtro_ci'] ?? '') ?: null,
            'orden'    => $_GET['orden'] ?? 'default',
        ];

        $resultado = profesorModelo::listar_profesores_supabase(
            $filtros,
            $token,
            $registros,
            ($pagina - 1) * $registros
        );

        if (!$resultado['ok']) {
            return '<div class="alert alert-danger">No se pudo cargar la lista de profesores.</div>';
        }

        $profesores = $resultado['data']['data'] ?? [];
        $total = (int) ($resultado['data']['total'] ?? 0);
        $paginas = max(1, (int) ceil($total / $registros));

        $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
        //$carpetas = [1 => 'admin', 2 => 'Control de estudio', 3 => 'administrador de pagos', 4 => 'Supervisor de cursos', 5 => 'Estudiante'];
        $carpeta = 'admin';
        $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

        $tabla = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center roboto-medium"><th>Id</th><th>Cédula</th><th>Nombre</th><th>Imagen</th>';
        if (in_array($cargo, [1, 4], true)) {
            if ($state == 1) { $tabla .= '<th>Actualizar</th><th>Archivar</th>'; }
            if ($state == 0) { $tabla .= '<th>Restaurar</th>'; }
        }
        $tabla .= '</tr></thead><tbody>';

        if ($total > 0) {
            $regInicio = ($pagina - 1) * $registros + 1;
            foreach ($profesores as $profe) {
                $id = (int) $profe['id'];
                $ci = htmlspecialchars($profe['ci'] ?? '', ENT_QUOTES, 'UTF-8');
                $nombreCompleto = htmlspecialchars(($profe['name'] ?? '') . ' ' . ($profe['last_name'] ?? ''), ENT_QUOTES, 'UTF-8');
                $imgRoute = $profe['route'] ?? 'assets/img/default.webp';
                $idEnc = mainModel::encryption($id);

                $tabla .= '<tr class="text-center">';
                $tabla .= '<td>' . $id . '</td>';
                $tabla .= '<td>' . $ci . '</td>';
                $tabla .= '<td>' . $nombreCompleto . '</td>';
                $tabla .= '<td><button type="button" class="btn btn-info btn-sm ver-imagen" data-src="' . SERVERURL . 'vistas/' . $imgRoute . '"><i class="fas fa-eye"></i></button></td>';
                if (in_array($cargo, [1, 4], true)) {
                    if ($state == 1) {
                        $tabla .= '<td><a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/profesor-update&profesor_id_up=' . $idEnc . '" class="btn btn-success btn-sm"><i class="fas fa-sync-alt"></i></a></td>';
                        $tabla .= '<td>
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/profesorAjax.php" method="POST" data-form="delete" data-entity="profesor" autocomplete="off">
                                <input type="hidden" name="profesor_id_archive" value="' . $idEnc . '">
                                <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-archive"></i></button>
                            </form>
                        </td>';
                    }
                    if ($state == 0) {
                        $tabla .= '<td>
                                <form class="FormularioAjax" action="' . SERVERURL . 'ajax/profesorAjax.php" method="POST" data-form="restore" autocomplete="off">
                                    <input type="hidden" name="profesor_id_restore" value="' . $idEnc . '">
                                    <button type="submit" class="btn btn-info btn-sm">
                                        <i class="fas fa-undo"></i> Restaurar
                                    </button>
                                </form>
                        </td>';
                    }
                }
                $tabla .= '</tr>';
            }
            $regFinal = $regInicio + count($profesores) - 1;
        } else {
            $columnas = in_array($cargo, [1, 4]) ? 6 : 4;
            $tabla .= '<tr class="text-center"><td colspan="' . $columnas . '">No hay profesores registrados.</td></tr>';
        }
        $tabla .= '</tbody></table></div>';

        if ($total > 0) {
            $tabla .= '<p class="text-right">Mostrando profesor ' . $regInicio . ' al ' . $regFinal . ' de un total de ' . $total . '</p>';
            $paramsGet = array_filter([
                'busqueda' => $busqueda,
                'filtro_id' => $_GET['filtro_id'] ?? '',
                'filtro_ci' => $_GET['filtro_ci'] ?? '',
                'filtro_nombre' => $_GET['filtro_nombre'] ?? '',
                'orden' => $_GET['orden'] ?? ''
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
    
    


    public function archivar_profesor_controlador(){
        $idEnc = $_POST['profesor_id_archive'] ?? '';
        $idDec = mainModel::decryption($idEnc);
        if (!is_numeric($idDec) || $idDec <= 0) {
            echo json_encode(mainModel::generarAlertaSimple('ID inválido.', 'error', 'simple'));
            return;
        }

        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            echo json_encode(mainModel::generarAlertaSimple('Sesión no disponible.', 'error', 'simple'));
            return;
        }

        $resultado = profesorModelo::archivar_profesor_supabase($idDec, $token);
        if ($resultado['ok']) {
                mainModel::invalidarCachePorPrefijo('profesores_');
            echo json_encode(mainModel::generarAlertaSimple('Profesor archivado correctamente.', 'success', 'recargar'));
        } else {
            echo json_encode(mainModel::generarAlertaSimple('Error al archivar: ' . ($resultado['message'] ?? ''), 'error', 'simple'));
        }
    }

    public function restaurar_profesor_controlador(){
        $idEnc = $_POST['profesor_id_restore'] ?? '';
        $idDec = mainModel::decryption($idEnc);
        if (!is_numeric($idDec) || $idDec <= 0) {
            echo json_encode(mainModel::generarAlertaSimple('ID inválido.', 'error', 'simple'));
            return;
        }

        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            echo json_encode(mainModel::generarAlertaSimple('Sesión no disponible.', 'error', 'simple'));
            return;
        }

        $resultado = profesorModelo::restaurar_profesor_supabase($idDec, $token);
        if ($resultado['ok']) {
                mainModel::invalidarCachePorPrefijo('profesores_');
            echo json_encode(mainModel::generarAlertaSimple('Profesor restaurado correctamente.', 'success', 'recargar'));
        } else {
            echo json_encode(mainModel::generarAlertaSimple('Error al restaurar: ' . ($resultado['message'] ?? ''), 'error', 'simple'));
        }
    }    



    
}