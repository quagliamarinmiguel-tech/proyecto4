<?php
if($peticionAjax){
    require_once "../modelos/profesorModelo.php";
    require_once "../modelos/imagenModelo.php";
}else{
    require_once "./modelos/profesorModelo.php";
    require_once "./modelos/imagenModelo.php";
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
        //$tipo = mainModel::limpiar_cadena($tipo);
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
    
    
    
    
    
    

    public function agregar_profesor_controlador()
    {
        // 1. Validar campos obligatorios
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
        
        // --- SUBIR IMAGEN ---
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

        // 2. Obtener y limpiar datos
        $ci = strtoupper(mainModel::limpiar_cadena($_POST['ci_reg'] ?? ''));
        $nombre = mainModel::limpiar_cadena($_POST['nombre_reg'] ?? '');
        $apellido = mainModel::limpiar_cadena($_POST['apellido_reg'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['correo_reg'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['telefono_reg'] ?? '');
        $mostrar = (bool) ($_POST['profe_mostrar'] ?? false);
        $descripcion = ($mostrar == 1) ? mainModel::limpiar_cadena($_POST['descripcion_reg'] ?? '') : '';
        $imagenCambiada = isset($_POST['imagen_cambiada']) ? (int) $_POST['imagen_cambiada'] : 0;
       // var_dump($mostrar,$descripcion);die();

        // 3. Validar campos obligatorios
        if ($ci == '' || $nombre == '' || $apellido == '' || $correo == '') {
            echo json_encode(mainModel::generarAlertaSimple('Todos los campos obligatorios deben estar llenos.', 'error', 'simple'));
            return;
        }

        // 4. Validar formatos
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

        // 5. Token Supabase
        $accessToken = $_SESSION['auth_access_token'] ?? null;
        if (!$accessToken) {
            echo json_encode(mainModel::generarAlertaSimple('La sesión de Supabase no está disponible.', 'error', 'simple'));
            return;
        }

        // 6. Obtener profesor actual (para saber ruta anterior y comprobar existencia)
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

        // 8. Procesar imagen nueva (solo si se cambió)
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

        // 9. Preparar datos para la RPC
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
       //var_dump($datosActualizar);die();

        // 10. Llamar a la RPC
        $resultado = profesorModelo::actualizar_profesor_con_imagen_supabase($datosActualizar, $accessToken);

        // 11. Manejar respuesta
        if ($resultado['ok']) {
            // Éxito: si se cambió la imagen, eliminar la anterior del disco
            if ($archivoSubido && $rutaImagenAnterior && file_exists("../vistas/" . $rutaImagenAnterior)) {
                unlink("../vistas/" . $rutaImagenAnterior);
            }
            echo json_encode(mainModel::generarAlertaSimple('Profesor actualizado exitosamente.', 'success', 'recargar'));
        } else {
            // Fallo: eliminar la imagen nueva subida (si existe)
            if ($archivoSubido && $rutaAbsolutaNueva && file_exists($rutaAbsolutaNueva)) {
                unlink($rutaAbsolutaNueva);
            }
            echo json_encode(mainModel::generarAlertaSimple('Error al actualizar el profesor: ' . ($resultado['message'] ?? 'Error desconocido'), 'error', 'simple'));
        }
    }
  
    
    
    
    
    
    
    
    
    
    
    
    
    public function paginador_profesor_controlador($pagina, $registros, $vista, $busqueda, $state) {
    $pagina = max(1, (int) $pagina);
    $registros =15;
//    $registros = max(1, (int) $registros);
    $token = $_SESSION['auth_access_token'] ?? null;

    if (!$token) {
        return '<div class="alert alert-danger">La sesión de Supabase no está disponible.</div>';
    }

    // Preparar parámetros para la RPC
    $params = [
        'p_state' => (int) $state,  // 1 o 0 según la vista
        'p_busqueda' => trim($busqueda) ?: null,
//        'p_id' => isset($_GET['filtro_id']) && $_GET['filtro_id'] !== '' ? (int) $_GET['filtro_id'] : null,
        'p_id' => isset($_GET['filtro_nombre']) && $_GET['filtro_nombre'] !== '' ? (int) $_GET['filtro_nombre'] : null,
        'p_ci' => trim($_GET['filtro_ci'] ?? '') ?: null,
        'p_orden' => $_GET['orden'] ?? 'default',
        'p_limit' => $registros,
        'p_offset' => ($pagina - 1) * $registros,
    ];
//var_dump($params);
  //      die();
    // Llamar directamente a la RPC (sin pasar por el modelo, como en cursos)
    $resultado = SupabaseApi::rpcWithToken('listar_profesores', $params, $token);

    if (!$resultado['ok']) {
        return '<div class="alert alert-danger">No se pudo cargar la lista de profesores.</div>';
    }

    $profesores = $resultado['data']['data'] ?? [];
    $total = (int) ($resultado['data']['total'] ?? 0);
    $paginas = max(1, (int) ceil($total / $registros));

    $cargo = (int) ($_SESSION['cargo_spm'] ?? 0);
    $carpetas = [1 => 'admin', 2 => 'Control de estudio', 3 => 'administrador de pagos', 4 => 'Supervisor de cursos', 5 => 'Estudiante'];
    $carpeta = $carpetas[$cargo] ?? 'admin';
    $vistaCompleta = urlencode($carpeta) . '/' . urlencode($vista);

    $tabla = '<div class="table-responsive"><table class="table table-dark table-sm"><thead><tr class="text-center roboto-medium"><th>Id</th><th>Cédula</th><th>Nombre</th><th>Imagen</th>';
    if (in_array($cargo, [1, 4], true)) {      
        if($state==1){$tabla .= '<th>Actualizar</th><th>Archivar</th>';}
        if($state==0){$tabla .= '<th>Restaurar</th>';}        
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
                
                
                if($state==1){
                    $tabla .= '<td><a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/profesor-update&profesor_id_up=' . $idEnc . '" class="btn btn-success btn-sm"><i class="fas fa-sync-alt"></i></a></td>';
                    $tabla .= '<td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/profesorAjax.php" method="POST" data-form="delete" data-entity="profesor" autocomplete="off">
                            <input type="hidden" name="profesor_id_archive" value="' . $idEnc . '">
                            <button type="submit" class="btn btn-warning btn-sm"><i class="fas fa-archive"></i></button>
                        </form>
                    </td>';
            	}
                
                if($state==0){
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

    // Paginador
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

    // Modal de imagen (igual que antes)
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
            echo json_encode(mainModel::generarAlertaSimple('Profesor restaurado correctamente.', 'success', 'recargar'));
        } else {
            echo json_encode(mainModel::generarAlertaSimple('Error al restaurar: ' . ($resultado['message'] ?? ''), 'error', 'simple'));
        }
    }    
    
    
    
    
    
    
    
    
    
/*
public function paginador_profesor_controlador($pagina, $registros, $vista, $busqueda){
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $busqueda  = mainModel::limpiar_cadena($busqueda);
    $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    
    
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
    
    
    $conexion = mainModel::conectar();

    // Filtros adicionales
    $filtro_id     = $_GET['filtro_id']     ?? "";
    $filtro_ci     = $_GET['filtro_ci']     ?? "";
    $filtro_nombre = $_GET['filtro_nombre'] ?? "";
    $orden         = $_GET['orden']         ?? "default";

    // WHERE dinámico
    $where = "p.estado_profesor = '1'";
    if ($busqueda != "") {
        $where .= " AND (p.nombre_profesor LIKE '%$busqueda%' OR p.apellido_profesor LIKE '%$busqueda%' OR p.ci_profesor LIKE '%$busqueda%')";
    }
    if ($filtro_id != "") {
        $where .= " AND p.id_profesor = '$filtro_id'";
    }
    if ($filtro_ci != "") {
        $where .= " AND p.ci_profesor LIKE '%$filtro_ci%'";
    }
    if ($filtro_nombre != "") {
        $where .= " AND p.id_profesor = '$filtro_nombre'";
    }

    // Ordenamiento
    $order_sql = "ORDER BY p.id_profesor DESC";
    if ($orden == "alfabetico") {
        $order_sql = "ORDER BY p.nombre_profesor ASC, p.apellido_profesor ASC";
    }

    // Consulta
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS  
            p.id_profesor, p.ci_profesor, p.nombre_profesor, p.estado_profesor, p.apellido_profesor,
            i.ruta AS ruta_imagen
        FROM profesor p
        INNER JOIN imagen i ON p.imagen_profesor_fk = i.id_imagen
        WHERE $where
        $order_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    
    $cargo = $_SESSION['cargo_spm'] ?? 0;

$tabla = '<div class="table-responsive">
    <table class="table table-dark table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>Id</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Periodos impartidos</th>
                <th>Imagen</th>
                <th>Ver clases impartidas</th>';

if ($cargo == 1 || $cargo == 4) {
    $tabla .= '
                <th>Actualizar</th>
                <th>Archivar</th>';
}

$tabla .= '
            </tr>
        </thead>
        <tbody>'; 

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;
        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_profesor']);

            // Conteo de cursos vigentes asignados
            $conteoCursos = $conexion->query("
                SELECT COUNT(DISTINCT cv.id_curso_vigente)
                FROM seccion s
                INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
                INNER JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
                WHERE s.ci_profesor = '{$row['id_profesor']}' AND c.estado_cohorte = 'Vigente' AND c.archivar_cohorte = 1 AND cv.estado_curso_vigente = 1
            ")->fetchColumn();

            
            
            $tabla .= '
    <tr class="text-center">
        <td>' . $row['id_profesor'] . '</td>
        <td>' . htmlspecialchars($row['ci_profesor']) . '</td>
        <td>' . htmlspecialchars($row['nombre_profesor'] . ' ' . $row['apellido_profesor']) . '</td>
        <td>' . $conteoCursos . '</td>
        <td>
            <button type="button" class="btn btn-info btn-sm ver-imagen" data-src="' . SERVERURL . 'vistas/' . $row['ruta_imagen'] . '">
                <i class="fas fa-eye"></i>
            </button>
        </td>


            
<td class="text-center">
    <div class="btn-group" role="group">
        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list&filtro_profesor=' . urlencode($row['id_profesor']) . '&origen=profe" class="btn btn-sm btn-info" title="Ver clases intensivas">
            <i class="fas fa-bolt"></i> Intensivos 
        </a>
        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list&filtro_profesor=' . urlencode($row['id_profesor']) . '&origen=profe" class="btn btn-sm btn-secondary" title="Ver clases por cohorte">
            <i class="fas fa-users"></i> Cohortes
        </a>
    </div>
</td>';
            
            
            
if ($cargo == 1 || $cargo == 4) {
    $tabla .= '
        <td>
       		<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/profesor-update&profesor_id_up=' . $idEnc . '" class="btn btn-success btn-sm">
                <i class="fas fa-sync-alt"></i>
            </a>
        </td>
        <td>
            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/profesorAjax.php" method="POST" data-form="delete" data-entity="profesor" autocomplete="off">
                <input type="hidden" name="profesor_id_archive" value="' . $idEnc . '">
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="fas fa-archive"></i>
                </button>
            </form>
        </td>';
}

$tabla .= '</tr>';
            
            
            
            
            
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="7">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-primary btn-sm">Recargar listado</a>'
            : 'No hay profesores registrados';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando profesor ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'busqueda' => $busqueda,
            'filtro_id' => $filtro_id,
            'filtro_ci' => $filtro_ci,
            'filtro_nombre' => $filtro_nombre,
            'orden' => $orden
        ]);

        // enlace de seccion list             <a href="' . SERVERURL . 'index.php?views=seccion-list&filtro_profesor=' . $row['id_profesor'] . '&origen=profe" class="btn btn-sm btn-primary">
        // profe update                         <a href="' . SERVERURL . 'index.php?views=profesor-update&profesor_id_up=' . $idEnc . '" class="btn btn-success btn-sm">
        // recargar listado                         ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-primary btn-sm">Recargar listado</a>'
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        //$urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    // Modal de imagen
    $tabla .= '
    <div id="modalImagen" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
        <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff;">
            <img id="imgPreview" src="" class="img-thumbnail" style="max-width:400px; width:80%; height:auto; display:block; margin:auto; border:3px solid white;">
            <button onclick="document.getElementById(\'modalImagen\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                <i class="fas fa-times"></i>
            </button>
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
}*/






public function paginador_profesor_archivado_controlador($pagina, $registros, $vista, $busqueda) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $busqueda  = mainModel::limpiar_cadena($busqueda);

    
    
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
    
    
    
    // Filtros tipo curso archivado
    $filtro_id     = isset($_GET['filtro_id'])     ? mainModel::limpiar_cadena($_GET['filtro_id'])     : "";
    $filtro_ci     = isset($_GET['filtro_ci'])     ? mainModel::limpiar_cadena($_GET['filtro_ci'])     : "";
    $filtro_nombre = isset($_GET['filtro_nombre']) ? mainModel::limpiar_cadena($_GET['filtro_nombre']) : "";
    $orden         = isset($_GET['orden'])         ? $_GET['orden'] : "";

    $orden_sql = "ORDER BY p.id_profesor DESC";
    if ($orden == "alfabetico") {
        $orden_sql = "ORDER BY p.nombre_profesor ASC";
    }

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    $conexion = mainModel::conectar();

    // WHERE dinámico
    $where = "p.estado_profesor = 0";
    if ($busqueda != "") {
        $where .= " AND (p.nombre_profesor LIKE '%$busqueda%' OR p.apellido_profesor LIKE '%$busqueda%' OR p.ci_profesor LIKE '%$busqueda%')";
    }
    if ($filtro_id != "") {
        $where .= " AND p.id_profesor = '$filtro_id'";
    }
    if ($filtro_ci != "") {
        $where .= " AND p.ci_profesor LIKE '%$filtro_ci%'";
    }
    if ($filtro_nombre != "") {
        $where .= " AND p.id_profesor = '$filtro_nombre'";
    }

    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            p.id_profesor, p.ci_profesor, p.nombre_profesor, p.apellido_profesor,
            p.telefono_profesor, p.correo,
            i.ruta AS ruta_imagen
        FROM profesor p
        INNER JOIN imagen i ON p.imagen_profesor_fk = i.id_imagen
        WHERE $where
        $orden_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int)$conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Img</th>
                    <th>Id</th>
                    <th>Ci</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Restaurar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $rows) {
            $idEnc = mainModel::encryption($rows['id_profesor']);
            $tabla .= '
                <tr class="text-center">
                    <td>
                        <button type="button" class="btn btn-info btn-sm ver-imagen" data-src="' . SERVERURL . 'vistas/' . $rows['ruta_imagen'] . '">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                    <td>' . $rows['id_profesor'] . '</td>
                    <td>' . $rows['ci_profesor'] . '</td>
                    <td>' . $rows['nombre_profesor'] . '</td>
                    <td>' . $rows['apellido_profesor'] . '</td>
                    <td>' . $rows['telefono_profesor'] . '</td>
                    <td>' . $rows['correo'] . '</td>

                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/profesorAjax.php" method="POST" data-form="restore" autocomplete="off">
                            <input type="hidden" name="profesor_id_restore" value="' . $idEnc . '">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="8">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay profesores archivados en el sistema';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando profesor ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'busqueda' => $busqueda,
            'filtro_id' => $filtro_id,
            'filtro_ci' => $filtro_ci,
            'filtro_nombre' => $filtro_nombre,
            'orden' => $orden
        ]);

                  //  ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
//        $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    // Modal de imagen
    $tabla .= '
<div id="modalImagen" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
    <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff;">
        <img id="imgPreview" src="" class="img-thumbnail" style="max-width:400px; width:80%; height:auto; display:block; margin:auto; border:3px solid white;">
        <button onclick="document.getElementById(\'modalImagen\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
            <i class="fas fa-times"></i>
        </button>
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






/*
public function actualizar_profesor_con_imagen_controlador(){
    try {
        $idEnc = $_POST['profesor_id_up'] ?? '';
        $idDec = mainModel::decryption($idEnc);

        if(!is_numeric($idDec) || $idDec <= 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"ID inválido",
                "Texto"=>"No se recibió un ID válido de profesor.",
                "Tipo"=>"error"
            ]);
        }

        // validar datos antes de procesar imagen
        $ci = mainModel::limpiar_cadena($_POST['ci_reg'] ?? '');
        $nombre = mainModel::limpiar_cadena($_POST['nombre_reg'] ?? '');
        $apellido = mainModel::limpiar_cadena($_POST['apellido_reg'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['correo_reg'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['telefono_reg'] ?? '');
        $descripcion = mainModel::limpiar_cadena($_POST['descripcion_reg'] ?? '');
        $mostrar = mainModel::limpiar_cadena($_POST['profe_mostrar'] ?? '');

        if($ci=="" || $nombre=="" || $apellido=="" || $correo==""){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar los campos obligatorios.",
                "Tipo"=>"error"
            ]);
        }

        if(!preg_match('/^[VE]{1}[0-9]{5,8}$/', $ci)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"CI inválida",
                "Texto"=>"La cédula debe comenzar con V o E seguida de 5 a 8 números.",
                "Tipo"=>"error"
            ]);
        }

        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $nombre)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Nombre inválido",
                "Texto"=>"Solo se permiten letras en el nombre.",
                "Tipo"=>"error"
            ]);
        }

        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $apellido)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Apellido inválido",
                "Texto"=>"Solo se permiten letras en el apellido.",
                "Tipo"=>"error"
            ]);
        }

        if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo inválido",
                "Texto"=>"Debes ingresar un correo electrónico válido.",
                "Tipo"=>"error"
            ]);
        }

        if($telefono!="" && !preg_match('/^[0-9]{7,12}$/', $telefono)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Teléfono inválido",
                "Texto"=>"Solo se permiten entre 7 y 12 dígitos numéricos.",
                "Tipo"=>"error"
            ]);
        }

        if(!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] !== 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Imagen inválida",
                "Texto"=>"No se pudo procesar la imagen enviada.",
                "Tipo"=>"error"
            ]);
        }

        $archivo = $_FILES['archivo_imagen'];
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];
        if(!in_array($ext, $permitidas)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Formato no permitido",
                "Texto"=>"La imagen debe estar en formato JPG, PNG o WEBP.",
                "Tipo"=>"error"
            ]);
        }

        $nombreFinal = uniqid("prof_").".".$ext;
        $rutaRelativa = "cm/prof/".$nombreFinal;
        $rutaCompleta = "../vistas/".$rutaRelativa;

        if(!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al guardar imagen",
                "Texto"=>"No se pudo almacenar la imagen en el servidor.",
                "Tipo"=>"error"
            ]);
        }

        // Obtener imagen actual para actualizar ruta
        $conexion = mainModel::conectar();
        $query = $conexion->prepare("
            SELECT i.id_imagen, i.ruta
            FROM profesor p
            INNER JOIN imagen i ON p.imagen_profesor_fk = i.id_imagen
            WHERE p.id_profesor = :ID
        ");
        $query->bindParam(":ID", $idDec, PDO::PARAM_INT);
        $query->execute();

        if($query->rowCount() != 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Imagen no encontrada",
                "Texto"=>"No se pudo obtener la imagen actual.",
                "Tipo"=>"error"
            ]);
        }

        $img = $query->fetch();
        $rutaAnterior = "../vistas/".$img['ruta'];
        $id_imagen_actual = $img['id_imagen'];

        //  Borrar imagen anterior del disco
        if(file_exists($rutaAnterior)) unlink($rutaAnterior);


        $updateRuta = imagenModelo::actualizar_ruta_imagen_modelo($id_imagen_actual, $rutaRelativa);
        $updateRuta->execute();



        return $this->actualizar_profesor_controlador();

    } catch (Exception $e){
        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"Error interno",
            "Texto"=>"Detalles: ".$e->getMessage(),
            "Tipo"=>"error"
        ]);
    }
}

//subir cosas actualizadas
public function actualizar_profesor_controlador(){
    $idEnc = $_POST['profesor_id_up'] ?? '';
    $idDec = mainModel::decryption($idEnc);

    if(!is_numeric($idDec) || $idDec <= 0){
        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"ID inválido",
            "Texto"=>"No se recibió un ID válido de profesor.",
            "Tipo"=>"error"
        ]);
    }

    // Campos limpios
    $ci = strtoupper(mainModel::limpiar_cadena($_POST['ci_reg'] ?? ''));
    $nombre = mainModel::limpiar_cadena($_POST['nombre_reg'] ?? '');
    $apellido = mainModel::limpiar_cadena($_POST['apellido_reg'] ?? '');
    $correo = mainModel::limpiar_cadena($_POST['correo_reg'] ?? '');
    $telefono = mainModel::limpiar_cadena($_POST['telefono_reg'] ?? '');
    $descripcion = mainModel::limpiar_cadena($_POST['descripcion_reg'] ?? '');
    $mostrar = mainModel::limpiar_cadena($_POST['profe_mostrar'] ?? '');
    $imagenCambiada = intval($_POST['imagen_cambiada'] ?? "0");

    // Validaciones finales 
    if($ci=="" || !preg_match('/^[VE]{1}[0-9]{5,9}$/', $ci)){
        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"CI inválida",
            "Texto"=>"La cédula debe comenzar con V o E seguida de 5 a 9 números.",
            "Tipo"=>"error"
        ]);
    }



        // Campos obligatorios
        if($ci=="" || $nombre=="" || $apellido=="" || $correo==""){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar los campos obligatorios",
                "Tipo"=>"error"
            ]);
        }

        // CI formato
        if(!preg_match('/^[VE]{1}[0-9]{5,9}$/', $ci)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"CI inválida",
                "Texto"=>"La cédula debe comenzar con V o E seguida de 5 a 8 números.",
                "Tipo"=>"error"
            ]);
        }

        // Validar nombre/apellido
        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $nombre)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Nombre inválido",
                "Texto"=>"Solo se permiten letras en el nombre.",
                "Tipo"=>"error"
            ]);
        }
        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $apellido)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Apellido inválido",
                "Texto"=>"Solo se permiten letras en el apellido.",
                "Tipo"=>"error"
            ]);
        }

        // Validar correo
        if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo inválido",
                "Texto"=>"Debes ingresar un correo electrónico válido.",
                "Tipo"=>"error"
            ]);
        }

        // Validar teléfono
        if($telefono!="" && !preg_match('/^[0-9]{7,12}$/', $telefono)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Teléfono inválido",
                "Texto"=>"Solo se permiten entre 7 y 12 dígitos numéricos.",
                "Tipo"=>"error"
            ]);
        }

        // Validar que CI o correo no esté repetido
        $check_ci = mainModel::ejecutar_consulta_simple("SELECT ci_profesor FROM profesor WHERE ci_profesor='$ci'");
        if($check_ci->rowCount() > 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Duplicado",
                "Texto"=>"Ya existe un profesor con esa cédula.",
                "Tipo"=>"error"
            ]);
        }

        $check_correo = mainModel::ejecutar_consulta_simple("SELECT correo FROM profesor WHERE correo='$correo'");
        if($check_correo->rowCount() > 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo registrado",
                "Texto"=>"Ya hay un profesor registrado con ese correo.",
                "Tipo"=>"error"
            ]);
        }




    // Armar arreglo para modelo
    $datos = [
        "CI" => $ci,
        "Nombre" => $nombre,
        "Apellido" => $apellido,
        "Correo" => $correo,
        "Telefono" => $telefono,
        "Descripcion" => $descripcion,
        "Mostrar" => $mostrar,
        "ID" => $idDec
    ];

    // Ejecutar update
    $actualizar = profesorModelo::actualizar_profesor_modelo($datos);

    if($actualizar->rowCount() == 1){
        return json_encode([
            "Alerta"=>"recargar",
            "Titulo"=>"Actualización exitosa",
            "Texto"=>"Los datos del profesor fueron actualizados correctamente.",
            "Tipo"=>"success"
        ]);
    } else {
        if($imagenCambiada === 1){
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Imagen actualizada",
                "Texto"=>"Se reemplazó la imagen del profesor exitosamente.",
                "Tipo"=>"success"
            ]);
        }

        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"Sin cambios",
            "Texto"=>"No se detectaron modificaciones en los datos.",
            "Tipo"=>"info"
        ]);
    }







}

*/



















/*
public function archivar_profesor_controlador() {
    $id = mainModel::decryption($_POST['profesor_id_archive']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia del profesor
    $check_prof = mainModel::ejecutar_consulta_simple("SELECT id_profesor FROM profesor WHERE id_profesor = '$id'");
    if ($check_prof->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "El profesor que intenta archivar no existe en el sistema",
            "Tipo" => "error"
        ]);
        exit();
    }

    // Verificar privilegios
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Permiso denegado",
            "Texto" => "No tienes los permisos necesarios para realizar esta operación",
            "Tipo" => "error"
        ]);
        exit();
    }

    $conexion = mainModel::conectar();

    try {
        $conexion->beginTransaction();

        // Archivar profesor
        $conexion->exec("UPDATE profesor SET estado_profesor = 0 WHERE id_profesor = '$id'");

        // Obtener secciones del profesor
        $secciones = $conexion->query("SELECT id_seccion FROM seccion WHERE ci_profesor = '$id'")->fetchAll();

        foreach ($secciones as $sec) {
            $idSec = $sec['id_seccion'];

            // Archivar sección
            $conexion->exec("UPDATE seccion SET archivar_seccion = 0 WHERE id_seccion = '$idSec'");

            // Obtener evaluaciones
            $evaluaciones = $conexion->query("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = '$idSec'")->fetchAll();

            foreach ($evaluaciones as $eval) {
                $idEval = $eval['id_evaluacion'];

                // Archivar evaluación
                $conexion->exec("UPDATE evaluacion SET archivar_evaluacion = 0 WHERE id_evaluacion = '$idEval'");

                // Archivar puntajes
                $conexion->exec("UPDATE puntaje_estudiante SET archivar_puntaje = 0 WHERE id_evaluacion = '$idEval'");
            }

            // Actualizar observaciones en inscripciones
            $conexion->exec("
                UPDATE inscripcion
                SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | profesor archivado o inactivo')
                WHERE id_seccion_fk = '$idSec'
            ");
        }

        $conexion->commit();

        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Profesor archivado",
            "Texto" => "El profesor y sus registros relacionados han sido archivados exitosamente",
            "Tipo" => "success"
        ]);
    } catch (Exception $e) {
        $conexion->rollBack();
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al archivar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}



public function restaurar_profesor_controlador() {
    $id = mainModel::decryption($_POST['profesor_id_restore']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia
    $check_prof = mainModel::ejecutar_consulta_simple("SELECT id_profesor FROM profesor WHERE id_profesor = '$id'");
    if ($check_prof->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "El profesor no existe",
            "Tipo" => "error"
        ]);
        exit();
    }

    $pdo = mainModel::conectar();
    try {
        $pdo->beginTransaction();

        $countsRestored = ['profesor'=>0,'secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $countsOmitted = ['secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $omittedExamples = [];

        $pushExample = function($tipo,$id) use (&$omittedExamples) {
            if (!isset($omittedExamples[$tipo])) $omittedExamples[$tipo] = [];
            if (count($omittedExamples[$tipo]) < 10) $omittedExamples[$tipo][] = $id;
        };

        // Restaurar profesor
        $pdo->exec("UPDATE profesor SET estado_profesor = 1 WHERE id_profesor = '$id'");
        $countsRestored['profesor'] = 1;

        // Obtener secciones del profesor
        $stmtSec = $pdo->prepare("SELECT id_seccion, id_curso_vigente_fk FROM seccion WHERE ci_profesor = :id");
        $stmtSec->execute([':id' => $id]);
        $secciones = $stmtSec->fetchAll();

        $ps_check_cv = $pdo->prepare("SELECT estado_curso_vigente FROM curso_vigente WHERE id_curso_vigente = :idCV");
        $ps_up_seccion = $pdo->prepare("UPDATE seccion SET archivar_seccion = 1 WHERE id_seccion = :idSec");

        $ps_get_evals = $pdo->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $pdo->prepare("UPDATE evaluacion SET archivar_evaluacion = 1 WHERE id_evaluacion = :idEval");
        $ps_up_puntaje = $pdo->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 1 WHERE id_evaluacion = :idEval");

        $ps_check_insc = $pdo->prepare("
            SELECT archivada FROM inscripcion 
            WHERE id_inscripcion = (
                SELECT id_inscripcion FROM puntaje_estudiante 
                WHERE id_evaluacion = :idEval LIMIT 1
            )
        ");

        $ps_up_obs = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | profesor reactivado')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%profesor reactivado%'
        ");

        foreach ($secciones as $sec) {
            $idSec = (int)$sec['id_seccion'];
            $idCV  = (int)$sec['id_curso_vigente_fk'];

            // Verificar curso vigente activo
            $ps_check_cv->execute([':idCV' => $idCV]);
            $cvActivo = $ps_check_cv->fetchColumn() == 1;

            if (! $cvActivo) {
                $countsOmitted['secciones'] += 1;
                $pushExample('secciones', $idSec);
                continue;
            }

            // Restaurar sección
            $ps_up_seccion->execute([':idSec' => $idSec]);
            $countsRestored['secciones'] += 1;

            // Restaurar evaluaciones
            $ps_get_evals->execute([':idSec' => $idSec]);
            while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
                $idEval = (int)$eval['id_evaluacion'];

                $ps_up_eval->execute([':idEval' => $idEval]);
                $countsRestored['evaluaciones'] += 1;

                // Verificar inscripción activa
                $ps_check_insc->execute([':idEval' => $idEval]);
                $inscRow = $ps_check_insc->fetch(PDO::FETCH_ASSOC);
                $inscActiva = $inscRow && (int)$inscRow['archivada'] === 1;

                if (! $inscActiva) {
                    $countsOmitted['puntajes'] += 1;
                    $pushExample('puntajes', $idEval);
                    continue;
                }

                $ps_up_puntaje->execute([':idEval' => $idEval]);
                $countsRestored['puntajes'] += $ps_up_puntaje->rowCount() ?: 1;
            }

            // Actualizar observaciones
            $ps_up_obs->execute([':idSec' => $idSec]);
        }

        $pdo->commit();

        $totalOmitidos = array_sum($countsOmitted);
        if ($totalOmitidos > 0) {
            $texto = "Profesor restaurado, pero se omitieron: " .
                $countsOmitted['secciones'] . " sección(es), " .
                $countsOmitted['evaluaciones'] . " evaluación(es), " .
                $countsOmitted['puntajes'] . " puntaje(s).";
            $tipo = "warning";
        } else {
            $texto = "Profesor restaurado exitosamente. Todos los registros relacionados fueron activados.";
            $tipo = "success";
        }

        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Restauración completada",
            "Texto" => $texto,
            "Tipo" => $tipo,
            "restored" => $countsRestored,
            "omitted" => $countsOmitted,
            "examples" => $omittedExamples
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al restaurar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}*/

    
}