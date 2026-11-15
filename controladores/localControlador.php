<?php
if ($peticionAjax) {
    require_once "../modelos/localModelo.php";
} else {
    require_once "./modelos/localModelo.php";
}

// deberia separar section y evaluation de periodoControlador

//esta vista maneja los datos de la vista de local view y los sliders
//junto con las estadisticas de visitas en vistas home, y home view
//ahora lasimagenes (por lo menos las tipo ifpg no se guardan en la bdd, lo mismo con los sliders)


class localControlador extends localModelo {

    /**
     * Obtiene el token de acceso de Supabase.
     * @return string Token de autenticación.
     */
    private function obtenerAccessToken(): string {
        if (!empty($_SESSION['auth_access_token'])) {
            return (string) $_SESSION['auth_access_token'];
        }
        if (defined('SUPABASE_KEY')) {
            return (string) SUPABASE_KEY;
        }
        return '';
    }

    
//devuelve el id de pageinfo
    private function obtenerPaginaId(): int {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible', 'data' => []];
        }
        
        $p= mainModel::traer_id_page_info($token);     
        $pageId = $p['data'][0]['id'] ?? null;
        
	    return $pageId === null ? null : (int) $pageId;
    }


// para home view, trae graficos y contadores
    public function dashboard_datos_controlador(): array{
        $token = $_SESSION['auth_access_token'] ?? null;
        $role  = (int) ($_SESSION['cargo_spm'] ?? 0);

        if (!$token) return ['ok' => false, 'message' => 'Sesión no disponible'];
        if ($role < 1 || $role > 5) return ['ok' => false, 'message' => 'Rol inválido'];

        $r = localModelo::dashboard_datos_supabase($role, $token);
        if (!$r['ok'] || empty($r['data'])) {
            return ['ok' => false, 'message' => 'No se pudieron cargar los datos'];
        }

        $d = $r['data'];
        $g = $d['graficos'] ?? [];

        $toChart = fn($arr) => [
            'labels' => array_column($arr, 'label'),
            'data'   => array_map(fn($x) => (float) $x['value'], $arr),
        ];

        $contadores = [
            'cursos'               => (int) ($d['cursos']               ?? 0),
            'cursos_vigentes'      => (int) ($d['cursos_vigentes']      ?? 0),
            'periodos'             => (int) ($d['periodos']             ?? 0),
            'periodos_vigentes'    => (int) ($d['periodos_vigentes']    ?? 0),
            'profesores'           => (int) ($d['profesores']           ?? 0),
            'estudiantes'          => (int) ($d['estudiantes']          ?? 0),
            'estudiantes_vigentes' => (int) ($d['estudiantes_inscritos_vigentes'] ?? 0),
        ];
        if (array_key_exists('solicitudes_pendientes', $d)) {
            $contadores['solicitudes'] = (int) $d['solicitudes_pendientes'];
        }
        if (array_key_exists('transacciones', $d)) {
            $contadores['transacciones'] = (int) $d['transacciones'];
        }
        if (array_key_exists('staff', $d)) {
            $contadores['staff'] = (int) $d['staff'];
        }

        return [
            'ok'         => true,
            'contadores' => $contadores,
            'graficos'   => [
                'inscritos_periodo'   => isset($g['inscritos_periodo'])   ? $toChart($g['inscritos_periodo'])   : null,
                'inscritos_curso'     => isset($g['inscritos_curso'])     ? $toChart($g['inscritos_curso'])     : null,
                'recaudacion_periodo' => isset($g['recaudacion_periodo']) ? $toChart($g['recaudacion_periodo']) : null,
                'promedios_periodo'   => isset($g['promedios_periodo'])   ? $toChart($g['promedios_periodo'])   : null,
            ],
        ];
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
//para vistas home
    public function estadisticas_home_controlador(): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) {
            return ['ok' => false, 'message' => 'Sesión no disponible', 'data' => []];
        }

        $rpc = localModelo::obtener_estadisticas_home_supabase($token);

        if (!$rpc['ok'] || empty($rpc['data']['ok'])) {
            return [
                'ok'      => false,
                'message' => $rpc['message'] ?? 'No se pudieron cargar las estadísticas',
                'data'    => [],
            ];
        }

        $data = $rpc['data'];

        // Formatear la última visita
        $ultima = $data['panel']['ultima'] ?? null;
        $ultimaFmt = 'Sin visitas';
        if ($ultima) {
            try {
                $dt = new DateTime($ultima);
                $ultimaFmt = $dt->format('d/m/Y H:i');
            } catch (Exception $e) {
                $ultimaFmt = $ultima;
            }
        }

        // Formatear fechas de interacciones recientes
        $interacciones = $data['interacciones'] ?? [];
        foreach ($interacciones as &$i) {
            if (!empty($i['fecha_hora'])) {
                try {
                    $dt = new DateTime($i['fecha_hora']);
                    $i['fecha_hora'] = $dt->format('d/m/Y H:i');
                } catch (Exception $e) {}
            }
        }
        unset($i);

        // Preparar arrays para Chart.js
        $visitasDia = $data['visitasDia'] ?? [];
        $labels1 = array_map(fn($r) => $r['dia'] ?? '', $visitasDia);
        $data1   = array_map(fn($r) => (int) ($r['visitas'] ?? 0), $visitasDia);

        $unicosDia = $data['unicosDia'] ?? [];
        $labels2 = array_map(fn($r) => $r['dia'] ?? '', $unicosDia);
        $data2   = array_map(fn($r) => (int) ($r['unicos'] ?? 0), $unicosDia);

        $topChart = $data['topCursosChart'] ?? [];
        $labelsTopCursos = array_map(fn($r) => $r['nombre_curso'] ?? '', $topChart);
        $clicks          = array_map(fn($r) => (int) ($r['clicks'] ?? 0), $topChart);
        $unicos          = array_map(fn($r) => (int) ($r['unicos'] ?? 0), $topChart);

        $clicksTipo = $data['clicksTipo'] ?? [];
        $labelsType = array_map(fn($r) => $r['tipo_curso'] ?? '', $clicksTipo);
        $dataType   = array_map(fn($r) => (int) ($r['clicks'] ?? 0), $clicksTipo);

        return [
            'ok'   => true,
            'data' => [
                'panel'           => [
                    'ips'      => (int) ($data['panel']['ips'] ?? 0),
                    'total'    => (int) ($data['panel']['total'] ?? 0),
                    'ultima'   => $ultimaFmt,
                    'cursoTop' => $data['panel']['cursoTop'] ?? 'Sin datos',
                ],
                'topCursos'       => $data['topCursos'] ?? [],
                'interacciones'   => $interacciones,
                'labels1'         => $labels1,
                'data1'           => $data1,
                'labels2'         => $labels2,
                'data2'           => $data2,
                'labelsTopCursos' => $labelsTopCursos,
                'clicks'          => $clicks,
                'unicos'          => $unicos,
                'labelsType'      => $labelsType,
                'dataType'        => $dataType,
            ],
        ];
    }
    
/*---------------------------------------------------------------------------------------------------*/
					 
    private function subirImagenEnServidorYNoBdd(array $archivo, string $nombreCampo): array {
        // 1. Validar archivo
        if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name']) || $archivo['error'] !== 0) {
            return ['status' => 'error', 'mensaje' => 'Archivo no válido para ' . $nombreCampo . '.'];
        }

        $extension = strtolower(pathinfo(basename($archivo['name']), PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'ico'];
        if (!in_array($extension, $permitidas, true)) {
            return ['status' => 'error', 'mensaje' => 'Formato no permitido para ' . $nombreCampo . '.'];
        }

        // Reglas por campo
        if ($nombreCampo === 'icono' && $extension !== 'ico') {
            return ['status' => 'error', 'mensaje' => 'El favicon debe ser un archivo .ico'];
        }
        if ($nombreCampo !== 'icono' && $extension === 'ico') {
            return ['status' => 'error', 'mensaje' => 'El formato .ico solo está permitido para el favicon'];
        }

        // Crear carpeta si no existe
        $carpetaDestino = __DIR__ . '/../vistas/cm/ifpg/';
        if (!is_dir($carpetaDestino) && !mkdir($carpetaDestino, 0777, true)) {
            return ['status' => 'error', 'mensaje' => 'No se pudo crear la carpeta.'];
        }

        // 2. Guardar nueva imagen con nombre temporal
        $tempNombre = 'tmp_' . uniqid() . '.' . $extension;
        $rutaTemp   = $carpetaDestino . $tempNombre;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaTemp)) {
            return ['status' => 'error', 'mensaje' => 'No se pudo guardar la imagen.'];
        }

        // 3. Buscar archivo viejo (cualquier extensión)
        $viejoArchivo = null;
        foreach (glob($carpetaDestino . $nombreCampo . '.*') as $f) {
            if (is_file($f)) {
                $viejoArchivo = $f;
                break;
            }
        }

        // 4. Respaldar el viejo
        $backupPath = null;
        if ($viejoArchivo !== null) {
            $backupPath = $viejoArchivo . '.bak';
            if (!@rename($viejoArchivo, $backupPath)) {
                @unlink($rutaTemp);
                return ['status' => 'error', 'mensaje' => 'No se pudo respaldar la imagen anterior.'];
            }
        }

        // 5. Renombrar el temp al nombre final
        $nombreFinal  = $nombreCampo . '.' . $extension;
        $rutaFinal    = $carpetaDestino . $nombreFinal;

        if (!@rename($rutaTemp, $rutaFinal)) {
            // Rollback: restaurar el viejo
            if ($backupPath !== null) @rename($backupPath, $viejoArchivo);
            @unlink($rutaTemp);
            return ['status' => 'error', 'mensaje' => 'No se pudo finalizar la operación.'];
        }

        // 6. Devolver info del backup para que el controlador decida cuándo borrarlo
        return [
            'status'        => 'success',
            'ruta'          => 'cm/ifpg/' . $nombreFinal,
            'ruta_absoluta' => $rutaFinal,
            'backup_path'   => $backupPath,   // null si no había viejo
            'viejo_path'    => $viejoArchivo, // null si no había viejo
        ];
    }
    
    
    
    
    
    
    
    


//Renderiza los <option> de un <select> de bancos
    public static function render_options_bancos(string $key, array $textos, string $placeholder = '-- Seleccione un banco --'): string{
        $guardado = $textos[$key] ?? '';
        $bancos   = self::bancos_venezuela();

        $html = '<option value="">' . htmlspecialchars($placeholder, ENT_QUOTES, 'UTF-8') . '</option>';

        foreach ($bancos as $banco) {
            $selected = ($guardado === $banco) ? ' selected' : '';
            $safe     = htmlspecialchars($banco, ENT_QUOTES, 'UTF-8');
            $html    .= '<option value="' . $safe . '"' . $selected . '>' . $safe . '</option>';
        }

        return $html;
    }


//Lista de bancos de Venezuela para Pago Móvil (orden alfabético). 
    public static function bancos_venezuela(): array{
        return [
            '(0156) 100% Banco',
            '(0171) Banco Activo',
            '(0166) Banco Agrícola de Venezuela',
            '(0172) Bancamiga',
            '(0114) Bancaribe',
            '(0175) Banco Bicentenario',
            '(0128) Banco Caroní',
            '(0102) Banco de Venezuela (BDV)',
            '(0146) Banco de la Gente Emprendedora (Bangente)',
            '(0163) Banco del Tesoro',
            '(0115) Banco Exterior',
            '(0177) Banco Fanb',
            '(0151) Banco Fondo Común (BFC)',
            '(0105) Banco Mercantil',
            '(0191) Banco Nacional de Crédito (BNC)',
            '(0138) Banco Plaza',
            '(0108) Banco Provincial (BBVA)',
            '(0137) Banco Sofitasa',
            '(0104) Banco Venezolano de Crédito (BVC)',
            '(0168) Bancrecer',
            '(0134) Banesco',
            '(0174) Banplus',
            '(0157) DelSur Banco Universal',
            '(0169) Mi Banco',
        ];
    }
    
    
    
    
    public function actualizar_local_controlador() {
        try {
            $accessToken = $_SESSION['auth_access_token'] ?? null;
            if (!$accessToken) {
                return json_encode(mainModel::generarAlertaSimple('La sesión de Supabase no está disponible.', 'error', 'simple'));
            }

            $textoCampos = [
                'txt_header', 'txt_titulo_post_header', 'txt_subtitulo_post_header',
                'nombre_completo', 'nombre_corto',
                'txt_titulo_info1', 'txt_subtitulo_info1',
                'txt_titulo_info2', 'txt_subtitulo_info2',
                'txt_titulo_info3', 'txt_subtitulo_info3',
                'url_add',
                'nombre_com1', 'texto_com1', 'nombre_com2', 'texto_com2',
                'nombre_com3', 'texto_com3', 'nombre_com4', 'texto_com4',
                'nombre_com5', 'texto_com5', 'nombre_com6', 'texto_com6',
                'nombre_com7', 'texto_com7', 'nombre_com8', 'texto_com8',
                'nombre_com9', 'texto_com9',
                'txt_titulo_footer', 'txt_ult_titulo_footer', 'txt_ult_subtitulo_footer',
                'txt_imagen_header_cursos', 'txt2_imagen_header_cursos', 'txt3_imagen_header_cursos', 'banco1', 'banco2', 'ci_pagomovil',
            ];

            $textos = [];
            foreach ($textoCampos as $campo) {
                $textos[$campo] = mainModel::limpiar_cadena((string) ($_POST[$campo] ?? ''));
            }

            $pageColor = [
                'paleta_id' => max(0, (int) ($_POST['paleta_activa'] ?? 0)),
            ];

            
                $phone           = ($_POST['telefono_contacto'] ?? '') !== '' ? (int) $_POST['telefono_contacto'] : null;
                $email           = mainModel::limpiar_cadena((string) ($_POST['correo_contacto'] ?? ''));
                $facebook_link   = mainModel::limpiar_cadena((string) ($_POST['link_facebook'] ?? ''));
                $instagram_link  = mainModel::limpiar_cadena((string) ($_POST['link_instagram'] ?? ''));
                $city_address    = mainModel::limpiar_cadena((string) ($_POST['direccion_local_ciudad'] ?? ''));
                $country_address = mainModel::limpiar_cadena((string) ($_POST['direccion_local_pais'] ?? ''));
                $rif             = mainModel::limpiar_cadena((string) ($_POST['rif_universal'] ?? ''));
            
            
            $datos = [
                'page_id'         => $this->obtenerPaginaId(),
                'phone'           => $phone,
                'email'           => $email,
                'facebook_link'   => $facebook_link,
                'instagram_link'  => $instagram_link,
                'city_address'    => $city_address,
                'country_address' => $country_address,
                'page_color'      => json_encode($pageColor, JSON_UNESCAPED_UNICODE),
                'rif'             => $rif,
                'page_texts'      => json_encode($textos, JSON_UNESCAPED_UNICODE),
                'images'          => [],
            ];
            
            

            $resultado    = localModelo::actualizar_local_supabase($datos, $accessToken);
            $rpcRespuesta = $resultado['data'] ?? null;

            if (!($resultado['ok'] ?? false) || (is_array($rpcRespuesta) && array_key_exists('ok', $rpcRespuesta) && $rpcRespuesta['ok'] !== true)) {
                $mensaje = $rpcRespuesta['error'] ?? $resultado['message'] ?? 'La RPC no pudo actualizar la información del local.';
                return json_encode(mainModel::generarAlertaSimple($mensaje, 'error', 'simple'));
            }

            // PROCESAR IMÁGENES (solo si la BD tuvo éxito)
            $imagenCampos = [
                'icono', 'logo', 'img_header',
                'img_desc1', 'img_desc2', 'img_desc3',
                'local', 'mapa_google', 'mapa_vista_satelital', 'img_footer', 'img_header_cursos','cintillo',
            ];

            $backups = []; // Guardar backups para rollback si algo falla

            foreach ($imagenCampos as $nombreCampo) {
                if (!isset($_FILES[$nombreCampo]) || $_FILES[$nombreCampo]['error'] !== 0) {
                    continue;
                }

                $subida = $this->subirImagenEnServidorYNoBdd($_FILES[$nombreCampo], $nombreCampo);

                if ($subida['status'] !== 'success') {
                    // Algo falló: restaurar TODOS los backups
                    foreach ($backups as $b) {
                        if ($b['backup_path'] !== null && file_exists($b['backup_path'])) {
                            // Restaurar el viejo
                            @rename($b['backup_path'], $b['viejo_path']);
                        }
                        // Borrar la imagen nueva que se había guardado
                        if (file_exists($b['ruta_absoluta'])) {
                            @unlink($b['ruta_absoluta']);
                        }
                    }

                    return json_encode(mainModel::generarAlertaSimple(
                        'Error al subir "' . $nombreCampo . '": ' . $subida['mensaje'],
                        'error', 'simple'
                    ));
                }

                // Guardar info del backup para posible rollback posterior
                $backups[] = [
                    'backup_path'   => $subida['backup_path'],
                    'viejo_path'    => $subida['viejo_path'],
                    'ruta_absoluta' => $subida['ruta_absoluta'],
                ];
            }

            // TODO OK: eliminar los backups
            foreach ($backups as $b) {
                if ($b['backup_path'] !== null && file_exists($b['backup_path'])) {
                    @unlink($b['backup_path']);
                }
            }
                   
           $contacto = [
                'phone'           => $phone,
                'email'           => $email,
                'facebook_link'   => $facebook_link,
                'instagram_link'  => $instagram_link,
                'city_address'    => $city_address,
                'country_address' => $country_address,
                'rif'             => $rif,
            ];
            
            
            mainModel::guardar_json_local($textos, (int) $pageColor['paleta_id'], $contacto);
      
            
            return json_encode(mainModel::generarAlertaSimple(
                'Información del local actualizada correctamente.',
                'success', 'recargar'
            ));

        } catch (Exception $e) {
            // En caso de excepción, restaurar backups si los hay
            if (!empty($backups)) {
                foreach ($backups as $b) {
                    if ($b['backup_path'] !== null && file_exists($b['backup_path'])) {
                        @rename($b['backup_path'], $b['viejo_path']);
                    }
                    if (file_exists($b['ruta_absoluta'])) {
                        @unlink($b['ruta_absoluta']);
                    }
                }
            }
            return json_encode(mainModel::generarAlertaSimple('Error interno: ' . $e->getMessage(), 'error', 'simple'));
        }
    }
    
    
    public function actualizar_sliders_controlador() {
        $backups = [];

        try {
            $campos = [];
            $restricciones = [];

            for ($i = 1; $i <= 8; $i++) {
                $nombre = 'slider_auto_' . $i;
                $campos[] = $nombre;
                $restricciones[$nombre] = ['forma' => 'horizontal_o_cuadrada'];
            }

            for ($i = 1; $i <= 6; $i++) {
                $nombre = 'slider_vertical_' . $i;
                $campos[] = $nombre;
                $restricciones[$nombre] = ['forma' => 'vertical'];
            }

            $validacion = mainModel::validar_imagenes($_FILES, $restricciones);
            if ($validacion['status'] === 'error') {
                return json_encode([
                    'Alerta' => 'simple',
                    'Titulo' => 'Error en la imagen',
                    'Texto'  => $validacion['mensaje'],
                    'Tipo'   => 'error',
                ]);
            }

            foreach ($campos as $nombreCampo) {
                if (!isset($_FILES[$nombreCampo]) || $_FILES[$nombreCampo]['error'] !== 0) {
                    continue;
                }

                $subida = $this->subirImagenEnServidorYNoBdd($_FILES[$nombreCampo], $nombreCampo);

                if ($subida['status'] !== 'success') {
                    foreach ($backups as $b) {
                        if ($b['backup_path'] !== null && file_exists($b['backup_path'])) {
                            @rename($b['backup_path'], $b['viejo_path']);
                        }
                        if (file_exists($b['ruta_absoluta'])) @unlink($b['ruta_absoluta']);
                    }
                    return json_encode(mainModel::generarAlertaSimple(
                        $nombreCampo . ': ' . $subida['mensaje'],
                        'error', 'simple'
                    ));
                }

                $backups[] = [
                    'backup_path'   => $subida['backup_path'],
                    'viejo_path'    => $subida['viejo_path'],
                    'ruta_absoluta' => $subida['ruta_absoluta'],
                ];
            }

            foreach ($backups as $b) {
                if ($b['backup_path'] !== null && file_exists($b['backup_path'])) {
                    @unlink($b['backup_path']);
                }
            }

            return json_encode(mainModel::generarAlertaSimple(
                'Sliders actualizados correctamente.',
                'success', 'recargar'
            ));

        } catch (Exception $e) {
            foreach ($backups as $b) {
                if ($b['backup_path'] !== null && file_exists($b['backup_path'])) {
                    @rename($b['backup_path'], $b['viejo_path']);
                }
                if (file_exists($b['ruta_absoluta'])) @unlink($b['ruta_absoluta']);
            }
            return json_encode([
                'Alerta' => 'simple',
                'Titulo' => 'Error interno',
                'Texto'  => 'Detalles: ' . $e->getMessage(),
                'Tipo'   => 'error',
            ]);
        }
    }
    
    

    
/**
 * Sube una imagen al servidor y devuelve la ruta relativa y absoluta.
 * @param array $archivo $_FILES['campo']
 * @param string $tipo 'sldr1' o 'sldr2'
 * @return array ['status' => 'success'|'error', 'ruta' => string, 'ruta_absoluta' => string, 'mensaje' => string]
 */
    private function subirImagenSliderServidor($archivo, $tipo) {
        // Validar archivo
        if (!isset($archivo['tmp_name']) || $archivo['error'] !== 0) {
            return ['status' => 'error', 'mensaje' => 'Archivo inválido'];
        }

        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $permitidas)) {
            return ['status' => 'error', 'mensaje' => 'Formato no permitido. Usa JPG, PNG o WEBP.'];
        }

        // Generar nombre único y carpeta según el tipo
        $nombreFinal = uniqid() . '.' . $ext;
        $carpetaDestino = "../vistas/cm/" . $tipo . "/";
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $rutaCompleta = $carpetaDestino . $nombreFinal;
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return ['status' => 'error', 'mensaje' => 'Error al mover el archivo'];
        }

        // Ruta relativa (desde la raíz del proyecto)
        $rutaRelativa = "cm/" . $tipo . "/" . $nombreFinal;

        return [
            'status' => 'success',
            'ruta' => $rutaRelativa,
            'ruta_absoluta' => $rutaCompleta,
            'mensaje' => 'Imagen subida correctamente',
        ];
    }

    /**
     * Obtiene las imágenes de un tipo específico (sldr1 o sldr2).
     * @param string $type 'sldr1' o 'sldr2'
     * @return array Array de rutas.
     */
    public function obtener_imagenes_por_tipo_controlador(string $type): array {
        $token = $_SESSION['auth_access_token'] ?? null;
        if (!$token) return [];

        $resultado = localModelo::obtener_imagenes_por_tipo_supabase($type, $token);
        return $resultado['data'] ?? [];
    }

    
    
    
    
    
    /**
     * Obtiene los datos estructurados de los sliders para la vista.
     * @return array ['sldr1' => [rutas], 'sldr2' => [rutas]]
     */
    public function obtener_datos_sliders_controlador(): array {
        $token = $this->obtenerAccessToken();
        if (!$token) {
            return ['sldr1' => [], 'sldr2' => []];
        }

        // Obtener imágenes de ambos sliders desde el modelo
        $sldr1 = localModelo::obtener_imagenes_por_tipo_supabase('sldr1', $token);
        $sldr2 = localModelo::obtener_imagenes_por_tipo_supabase('sldr2', $token);

        // Extraer solo las rutas en arrays indexados (posición 0 = imagen 1)
        $rutas1 = array_column($sldr1['data'] ?? [], 'route');
        $rutas2 = array_column($sldr2['data'] ?? [], 'route');

        return [
            'sldr1' => $rutas1,
            'sldr2' => $rutas2,
        ];
    }

    
    
    
    
    
    

    /**
     * Copia la imagen por defecto a la carpeta del slider y devuelve la ruta relativa.
     * @param string $tipo 'sldr1' o 'sldr2'
     * @param int $indice Posición (0-7 o 0-5)
     * @return string Ruta relativa (ej. "cm/sldr1/default_0.webp")
     */
    private function copiarImagenDefaultSlider(string $tipo, int $indice): string {
        $rutaOrigen = "../vistas/assets/img/default.webp";
        $carpetaDestino = "../vistas/cm/" . $tipo . "/";
        $nombreFinal = "default_" . $indice . ".webp";
        $rutaCompleta = $carpetaDestino . $nombreFinal;

        // Crear carpeta si no existe
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        // Si el archivo ya existe, no sobrescribir (opcional)
        if (!file_exists($rutaCompleta)) {
            if (file_exists($rutaOrigen)) {
                copy($rutaOrigen, $rutaCompleta);
            } else {
                // Si no existe el default, crear un archivo vacío o lanzar error
                file_put_contents($rutaCompleta, '');
            }
        }

        return "cm/" . $tipo . "/" . $nombreFinal;
    }

        
    
    
    
   private function subirImagenLocalServidor(array $archivo, string $nombreCampo): array {
        if (!isset($archivo['tmp_name']) || !is_uploaded_file($archivo['tmp_name']) || $archivo['error'] !== 0) {
            return ['status' => 'error', 'mensaje' => 'Archivo no válido para ' . $nombreCampo . '.'];
        }

        $extension = strtolower(pathinfo(basename($archivo['name']), PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'ico'];
        if (!in_array($extension, $permitidas, true)) {
            return ['status' => 'error', 'mensaje' => 'Formato no permitido para ' . $nombreCampo . '.'];
        }

        $carpetaDestino = __DIR__ . '/../vistas/cm/ifpg/';
        if (!is_dir($carpetaDestino) && !mkdir($carpetaDestino, 0777, true)) {
            return ['status' => 'error', 'mensaje' => 'No se pudo crear la carpeta de imágenes institucionales.'];
        }

        $nombre = uniqid('ifpg_', true) . '.' . $extension;
        $rutaAbsoluta = $carpetaDestino . $nombre;
        if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
            return ['status' => 'error', 'mensaje' => 'No se pudo guardar la imagen en el servidor.'];
        }

        return [
            'status' => 'success',
            'ruta' => 'cm/ifpg/' . $nombre,
            'ruta_absoluta' => $rutaAbsoluta,
        ];
    }

    
    
    
    
    private function obtenerImagenesIfpgMapSupabase(): array {
        $token = $this->obtenerAccessToken();
        $respuesta = SupabaseApi::getWithToken('page_info_img', [
            'select' => 'img_id,img_name,page_id',
            'page_id' => 'eq.' . $this->obtenerPaginaId(),
        ], $token);

        if (!$respuesta['ok'] || empty($respuesta['data'])) {
            return [];
        }

        $ids = array_values(array_unique(array_filter(array_map(function ($item) {
            return isset($item['img_id']) && $item['img_id'] !== null ? (int) $item['img_id'] : null;
        }, $respuesta['data']))));

        if (empty($ids)) {
            return [];
        }

        $imgResp = SupabaseApi::getWithToken('img', [
            'select' => 'id,route,type',
            'id' => 'in.(' . implode(',', $ids) . ')',
            'type' => 'eq.ifpg',
        ], $token);

        if (!$imgResp['ok'] || empty($imgResp['data'])) {
            return [];
        }

        $rutaPorId = [];
        foreach ($imgResp['data'] as $img) {
            $rutaPorId[(int) ($img['id'] ?? 0)] = (string) ($img['route'] ?? '');
        }

        $map = [];
        foreach ($respuesta['data'] as $fila) {
            $id = isset($fila['img_id']) ? (int) $fila['img_id'] : 0;
            $imgName = trim((string) ($fila['img_name'] ?? ''));
            if ($id > 0 && $imgName !== '' && array_key_exists($id, $rutaPorId)) {
                $map[$imgName] = $rutaPorId[$id];
            }
        }

        return $map;
    }
    
        public function obtener_imagenes_pagina_controlador() {
    	    return $this->obtenerImagenesIfpgMapSupabase();
    	}
    
    
    
   
    public function datos_local_controlador() {
        $respuesta = localModelo::obtener_datos_local_supabase($this->obtenerAccessToken());
        if (!$respuesta['ok'] || empty($respuesta['data'][0])) {
            return [
                'ok' => false,
                'data' => null,
                'message' => $respuesta['message'] ?? 'No se encontró la información institucional.',
            ];
        }

        $pagina = $respuesta['data'][0];
        return [
            'ok' => true,
            'data' => [
                'id' => $pagina['id'] ?? null,
                'telefono_contacto' => $pagina['phone'] ?? null,
                'correo_contacto' => $pagina['email'] ?? null,
                'link_facebook' => $pagina['facebook_link'] ?? null,
                'link_instagram' => $pagina['instagram_link'] ?? null,
                'direccion_local_ciudad' => $pagina['city_address'] ?? null,
                'direccion_local_pais' => $pagina['country_address'] ?? null,
                'rif' => $pagina['rif'] ?? null,
                'colores_pagina' => $pagina['page_color'] ?? null,
                'textos_pagina' => $pagina['page_texts'] ?? null,
                'nombre_local' => null,
                'nombre_local_corto' => null,
                'imagen_local' => null,
                'imagen_local_mapa' => null,
                'imagen_local_satelite' => null,
            ],
        ];
    }

    public function obtener_imagenes_local_controlador() {
        return $this->obtenerImagenesMapSupabase();
    }
    
    
    
}
