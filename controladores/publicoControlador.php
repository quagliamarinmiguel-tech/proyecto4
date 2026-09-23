<?php
if ($peticionAjax) {
    require_once "../modelos/publicoModelo.php";
} else {
    require_once "./modelos/publicoModelo.php";
}

// ---> deberia separar section y evaluation de periodoControlador y dejar este solo con period y period course, y un poco de section pero seccion consult pide muchas otras cosas y reduzco mil lineas en el proceso
// ---> deberia proteger las entradas del paginador ante ataques
// ---> deberia separar lo publico de lo de local por logica
// ---> deberia proteger las entradas de formularios en vistas publicas


//esta vista maneja los datos de la vista de local view y los sliders
//ahora maneja tambien las consultas de las vistas publicas, creo q no hace da;o porque las consultas q cambian la bdd piden el token y este solo se obtiene mediante funciones privadas
// hay una funcion q trae los datos de page_info con sus page_info img q usan todas las vistas,
//otra q trae profes sliders y cursos para inicio view,
//otra q lista los cursos para cursos view y otra q muestra los datos de un curso en cursos check

class publicoControlador extends publicoModelo {

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

    /**
     * Obtiene el ID de la página institucional (por defecto 6 según tu sistema).
     * @return int ID de la página.
     */
    private function obtenerPaginaId(): int {
        return 6; // Ajusta según tu sistema
    }

    
    
    
    
//las siguientes 2 funciones traen los datos para inicio view y proximamente cursos view y cursos check
    public function obtener_imagenes_pagina_publico_controlador(?int $pageId = null): array {
        $pageId = $pageId ?? $this->obtenerPaginaId();
        $imagenes = publicoModelo::obtener_imagenes_pagina_publico_supabase($pageId);
        $imagenDefault = SERVERURL . 'vistas/assets/img/default.webp';
        $camposImagen = [
            'icono', 'logo', 'img_header',
            'img_desc1', 'img_desc2', 'img_desc3',
            'loca', 'mapa_google', 'mapa_vista_satelital', 'img_footer','img_header_cursos',
        ];

        foreach ($camposImagen as $campo) {
            $ruta = $imagenes[$campo] ?? '';
            if (is_string($ruta) && trim($ruta) !== '') {
                $imagenes[$campo] = preg_match('/^https?:\/\//i', $ruta)
                    ? $ruta
                    : SERVERURL . 'vistas/' . ltrim($ruta, '/');
            } else {
                $imagenes[$campo] = $imagenDefault;
            }
        }

        return $imagenes;
    }    
    
    
//2da funcion que trae datos para las vistas publicas en base a 1 o 0
	public function datos_local_publico_controlador(int $tipo = 1): array {
        $respuesta = publicoModelo::obtener_datos_local_publico_supabase();
        if (!$respuesta['ok'] || empty($respuesta['data'][0])) {
            return [
                'ok' => false,
                'data' => null,
                'message' => $respuesta['message'] ?? 'No se encontró la información institucional.',
            ];
        }

        $pagina = $respuesta['data'][0];
        $textosPagina = $pagina['page_texts'] ?? [];
        if (is_string($textosPagina)) {
            $textosPagina = json_decode($textosPagina, true);
            if (is_string($textosPagina)) {
                $textosPagina = json_decode($textosPagina, true);
            }
        }
        $textosPagina = is_array($textosPagina) ? $textosPagina : [];

        // Definición base de campos de texto (sin los campos informativos/comentarios si $tipo === 0)
        $camposTexto = [
            'txt_header', 'txt_titulo_post_header', 'txt_subtitulo_post_header',
            'nombre_completo', 'nombre_corto',
        ];

        if ($tipo === 1) {
            $camposTextoAdicionales = [
                'txt_titulo_info1', 'txt_subtitulo_info1',
                'txt_titulo_info2', 'txt_subtitulo_info2',
                'txt_titulo_info3', 'txt_subtitulo_info3',
                'url_add',
                'nombre_com1', 'texto_com1', 'nombre_com2', 'texto_com2',
                'nombre_com3', 'texto_com3', 'nombre_com4', 'texto_com4',
                'nombre_com5', 'texto_com5', 'nombre_com6', 'texto_com6',
                'nombre_com7', 'texto_com7', 'nombre_com8', 'texto_com8',
                'nombre_com9', 'texto_com9',
            ];
            // Se insertan los campos adicionales en su posición original
            array_splice($camposTexto, 5, 0, $camposTextoAdicionales);
          
            // -> seria bueno poner un contador de 5 min entre visita y visita aprovechando la consulta donde buscamos ips, mrd, buscamos ip visitor, no visit, eso es mete otra tabla0 a la consulta
            
            //cuando es 1, significa q la peticion es desde inicio view, solo ahi registro las visitas a la pagina,
            //si es 0, significa q se cargan los datos para cursos view o cursos detail,
                $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
                ?? $_SERVER['HTTP_CLIENT_IP']
                ?? $_SERVER['REMOTE_ADDR']
                ?? null;

            //var_dump($ip);die();
                // Si hay varias IPs separadas por coma, tomar la primera
                if ($ip && strpos($ip, ',') !== false) {
                    $ip = trim(explode(',', $ip)[0]);
                }
          
        }

        $camposTextoFinales = array_merge($camposTexto, [
            'txt_titulo_footer', 'txt_ult_titulo_footer', 'txt_ult_subtitulo_footer',
            'txt_imagen_header_cursos', 'txt2_imagen_header_cursos','txt3_imagen_header_cursos',
        ]);

        foreach ($camposTextoFinales as $campo) {
            $valor = $textosPagina[$campo] ?? '';
            $textosPagina[$campo] = is_scalar($valor) && trim((string) $valor) !== ''
                ? (string) $valor
                : 'default';
        }

        $coloresPagina = $pagina['page_color'] ?? [];
        if (is_string($coloresPagina)) {
            $coloresPagina = json_decode($coloresPagina, true);
            if (is_string($coloresPagina)) {
                $coloresPagina = json_decode($coloresPagina, true);
            }
        }
        $coloresPagina = is_array($coloresPagina) ? $coloresPagina : ['paleta_id' => 0];
        
        $datosRetorno = [
            'id' => $pagina['id'] ?? null,
            'telefono_contacto' => $pagina['phone'] ?? null,
            'correo_contacto' => $pagina['email'] ?? null,
            'link_facebook' => $pagina['facebook_link'] ?? null,
            'link_instagram' => $pagina['instagram_link'] ?? null,
            'direccion_local_ciudad' => $pagina['city_address'] ?? null,
            'direccion_local_pais' => $pagina['country_address'] ?? null,
            'rif' => $pagina['rif'] ?? null,
            'colores_pagina' => $coloresPagina,
            'textos_pagina' => $textosPagina,
        ];

        // Lógica condicional para sliders, profesores y cursos
        if ($tipo === 1) {
            $datos = publicoModelo::obtener_datos_inicio_supabase($ip);
            if ($datos['ok'] && isset($datos['data'])) {
                $datosRetorno['sliders'] = $datos['data']['sliders'] ?? [];
                $datosRetorno['profesores'] = $datos['data']['teachers'] ?? [];
                $datosRetorno['cursos'] = $datos['data']['courses'] ?? [];
                
                //var_dump($datosRetorno['cursos']);die();
                $cursos = $datos['data']['courses'] ?? [];
                
                // Encriptar el marketing_id de cada curso, de modo q al enviar el id al controlador se desencripte sin problemas
                foreach ($cursos as &$curso) {
                    if (isset($curso['marketing_id'])) {
                        $curso['marketing_id'] = mainModel::encryption((int) $curso['marketing_id']);
                    }
                }
                
                unset($curso); // romper la referencia
		        $datosRetorno['cursos'] = $cursos;
                //var_dump($cursos);die();
            } else {
                $datosRetorno['sliders'] = [];
                $datosRetorno['profesores'] = [];
                $datosRetorno['cursos'] = [];
            }
        }

        return [
            'ok' => true,
            'data' => $datosRetorno,
        ];
    }
    
    
    
    
    
    
    
//la lista de cursos de cursos view
    public function listar_cursos_publico_controlador($pagina = 1, $filtro_tipo = null, $busqueda = null) {
        $pagina = max(1, (int) $pagina);
        $limite = 20;
        $token = $_SESSION['auth_access_token'] ?? null;
		
        //incluyo un div extra en cada html porque en la vista se abre un div que cierra los cursos con un width de 100% 
        
        $resultado = publicoModelo::listar_cursos_publico_supabase($filtro_tipo, $pagina, $limite, $busqueda);

        if (!$resultado['ok']) {
            return '<div class="alert alert-danger" style="font-size:5rem;">No se pudieron cargar los cursos.</div></div>';
        }

        $cursos = $resultado['data']['data'] ?? [];
        $total = (int) ($resultado['data']['total'] ?? 0);
        $paginas = max(1, (int) ceil($total / $limite));

        // Preparamos los cursos con todos los campos necesarios para la tarjeta
        $cursosPreparados = [];
        foreach ($cursos as $curso) {
            $teacher = $curso['teacher'] ?? null;
            $profeMostrar = ($teacher !== null);
            $nombreProf = $profeMostrar ? $teacher['name'] . ' ' . $teacher['last_name'] : '';
            $imgProf = $profeMostrar ? $teacher['img_route'] ?? 'default.webp' : '';
            $inscripcion = (float) ($curso['inscripcion'] ?? 0);
            $modulo = (float) ($curso['modulo'] ?? 0);
            $precioTotal = $inscripcion + ($modulo * 3);

            $cursosPreparados[] = [
                'id_materia' => $curso['id'],
                'marketing_id' => $curso['marketing_course_id'],   // el id a enviar en la tarjeta
                'nombre_curso' => $curso['name'] ?? '',
                'tipo_curso' => $curso['type'] ?? '',
                'prefacio' => $curso['introduction'] ?? '',
                'ruta_materia' => $curso['img_route'] ?? 'default.webp',
                'profe_mostrar' => $profeMostrar ? 1 : 0,
                'ruta_profesor' => $imgProf,
                'nombre_profesor' => $profeMostrar ? $teacher['name'] ?? '' : '',
                'apellido_profesor' => $profeMostrar ? $teacher['last_name'] ?? '' : '',
                'pago_inscripcion_vigente' => $inscripcion,
                'pago_modulo_vigente' => $modulo,
                'precio_total' => $precioTotal,
            ];
        }

        // Ahora generamos el HTML usando la misma estructura que tu vista
        $html = '<div class="row tarjconteiners">';
        if (empty($cursosPreparados)) {
            $html .= '<div class="col-12"><p class="text-center" style="font-size:5rem;">No se encontraron cursos disponibles.</p></div></div>';
        } else {
            for ($index = 0; $index < count($cursosPreparados); $index += 2) {
                for ($offset = 0; $offset <= 1; $offset++) {
                    if (isset($cursosPreparados[$index + $offset])) {
                        $curso = $cursosPreparados[$index + $offset];
                        $currentIndex = $index + $offset;
                        $idEncriptado = mainModel::encryption($curso['marketing_id']);
                        // Aquí va exactamente el mismo HTML de tu tarjeta, reutilizando las variables
                        
                        // <div class="tarjetacurso3 tarjeta_curso car" id="' . htmlspecialchars($curso['id_materia']) . '" onclick="toggleDiv(' . $currentIndex . ', \'oculto' . htmlspecialchars($curso['id_materia']) . '\', 130)">

                        $html .= '
    						<div class="tarjetacurso3 tarjeta_curso car" id="' . htmlspecialchars($curso['id_materia']) . '" onclick="registrarYRedirigir(\'' . $idEncriptado . '\')">
                                <img loading="lazy" src="../vistas/' . htmlspecialchars($curso['ruta_materia']) . '" />
                                <div class="subtarjetacurso3 tarjeta_curso_texto">
                                    <p>' . htmlspecialchars($curso['nombre_curso']) . '</p>
                                    <p class="tarjeta_curso_texto_prefacio">' . htmlspecialchars($curso['prefacio']) . '</p>';

                        if ($curso['profe_mostrar'] == 1) {
                            $html .= '
                                    <section class="datosprofe_curso tarjeta_datosprofe_curso">
                                        <img loading="lazy" src="../vistas/' . htmlspecialchars($curso['ruta_profesor']) . '" />
                                        <p><b>Prof. ' . htmlspecialchars($curso['nombre_profesor'] . ' ' . $curso['apellido_profesor']) . '</b></p>
                                    </section>';
                        }

                        $html .= '
                                    <div class="info_precio_tarjeta3 div_precio_tarjeta">
                                        <div class="precio_n_tarjeta3 precio_tarjeta">
                                            <p>Insc: ' . number_format($curso['precio_total'], 2, ',', '.') . '$</p>
                                            <p>- ' . htmlspecialchars($curso['tipo_curso']) . '</p>
                                        </div>
                                    </div>
                                </div>
                            </div>';
                    }
                }
            }
        }
        $html .= '</div>';

        // Paginador (opcional)
        if ($total > 0) {
            $html .= $this->generarPaginador($pagina, $paginas, $filtro_tipo, $busqueda);
        }

        return $html;
    }
    

    
    
//paginador para cursos view porque no usa bootstrap ni funciones del mainmodel
    private function generarPaginador($pagina, $paginas, $filtro_tipo, $busqueda) {
        $base = SERVERURL . 'index.php?views=cursos';
        $params = [];
        if ($filtro_tipo) $params[] = 'tipo=' . urlencode($filtro_tipo);
        if ($busqueda) $params[] = 'busqueda=' . urlencode($busqueda);
        $query = !empty($params) ? '&' . implode('&', $params) : '';

        $html = '</div><nav><ul class="pagination_cursos justify-content-center">';

        if ($pagina > 1) {
            $prevPage = $pagina - 1;
            $html .= '<li class="page-item_cursos"><a class="page-link_cursos" href="' . $base . $query . '&page=' . $prevPage . '">&laquo;</a></li>';
        }

        for ($i = 1; $i <= $paginas; $i++) {
            $active = ($i == $pagina) ? 'active' : '';
            $html .= '<li class="page-item_cursos ' . $active . '"><a class="page-link_cursos" href="' . $base . $query . '&page=' . $i . '">' . $i . '</a></li>';
        }

        if ($pagina < $paginas) {
            $nextPage = $pagina + 1;
            $html .= '<li class="page-item_cursos"><a class="page-link_cursos" href="' . $base . $query . '&page=' . $nextPage . '">&raquo;</a></li>';
        }

        $html .= '</ul></nav>';
        return $html;
    }
    
    
    
    
 
    
//al presionar una tarjeta de curso, mediante ajax llama a esta funcion, desencripta el id, si no existe la ip, la crea,
//luego registra el click, devuelve el msj, y el js redirije al usuario al curso seleccionado
    public function registrar_interaccion_controlador(): void {
        header('Content-Type: application/json');

        // 1. Validar ID
        $idEnc = $_GET['id'] ?? '';
        //var_dump($idEnc);die();
        $marketingId = (int) mainModel::decryption($idEnc);
        if ($marketingId <= 0) {
            echo json_encode(['ok' => false, 'error' => 'ID inválido']);
            return;
        }

        // 2. Obtener IP real
        $ip = $_SERVER['HTTP_X_FORWARDED_FOR']
            ?? $_SERVER['HTTP_CLIENT_IP']
            ?? $_SERVER['REMOTE_ADDR']
            ?? '';
        if ($ip && strpos($ip, ',') !== false) {
            $ip = trim(explode(',', $ip)[0]);
        }

        // 3. Llamar al modelo
        $resp = publicoModelo::registrar_interaccion_supabase($ip, $marketingId);

        if ($resp['ok'] && ($resp['data']['ok'] ?? false) === true) {
            echo json_encode(['ok' => true]);
        } else {
            $error = $resp['data']['error'] ?? $resp['message'] ?? 'Error al registrar';
            echo json_encode(['ok' => false, 'error' => $error]);
        }
    }
    
    
    
//trae datos del curso seleccionado de cursos detail
    public function detalle_curso_publico_controlador(int $marketingId): array {
        if ($marketingId <= 0) {
            return [
                'ok' => false,
                'data' => null,
                'message' => 'ID de curso inválido.',
            ];
        }
        //$id_enc=mainModel::decryption($marketingId);
		//$marketingId = $id_enc;
        
        $respuesta = publicoModelo::obtener_curso_detalle_publico_supabase($marketingId);
        if (!$respuesta['ok'] || empty($respuesta['data']['ok'])) {
            return [
                'ok' => false,
                'data' => null,
                'message' => $respuesta['data']['error'] ?? $respuesta['message'] ?? 'Curso no encontrado.',
            ];
        }

        $datos = $respuesta['data'];
        $marketing = $datos['marketing'] ?? [];
        $course = $datos['course'] ?? [];
        $teacher = $datos['teacher'] ?? null;
        $period = $datos['period'] ?? null;

        // Calcular precios según tipo de período
        $inscripcion = 0;
        $modulo = 0;
        $textoCuota = '';
        $multiplicador = 0;
        $total = 0;

        if ($period !== null && !empty($period['costs'])) {
            foreach ($period['costs'] as $c) {
                if (($c['cost_description'] ?? '') === 'pago_inscripcion') {
                    $inscripcion = (float) ($c['price'] ?? 0);
                }
            }

            if (($period['type'] ?? '') === 'Intensivo') {
                foreach ($period['costs'] as $c) {
                    if (($c['cost_description'] ?? '') === 'pago_encuentro') {
                        $modulo = (float) ($c['price'] ?? 0);
                        $textoCuota = 'Pago por encuentro';
                    }
                }
                $multiplicador = 6;
            } else {
                foreach ($period['costs'] as $c) {
                    if (($c['cost_description'] ?? '') === 'pago_modulo1') {
                        $modulo = (float) ($c['price'] ?? 0);
                        $textoCuota = 'Pago por módulo';
                    }
                }
                $multiplicador = 3;
            }

            $total = $inscripcion + ($modulo * $multiplicador);
        }

        // Preparar los contenidos (1-10) como array limpio
        $contenidos = [];
        for ($i = 1; $i <= 10; $i++) {
            $val = $marketing["content_$i"] ?? '';
            if (trim((string) $val) !== '') {
                $contenidos[] = (string) $val;
            }
        }

        // Preparar los requisitos (1-4) como array limpio
        $requisitos = [];
        for ($i = 1; $i <= 4; $i++) {
            $val = $marketing["requirement_$i"] ?? '';
            if (trim((string) $val) !== '') {
                $requisitos[] = (string) $val;
            }
        }

        $datosRetorno = [
            'marketing' => [
                'id' => $marketing['id'] ?? null,
                'introduction' => $marketing['introduction'] ?? '',
                'description' => $marketing['description'] ?? '',
                'class' => $marketing['class'] ?? '',
                'img_route' => $marketing['img_route'] ?? 'default.webp',
                'contenidos' => $contenidos,
                'requisitos' => $requisitos,
                'icon_1' => (bool) ($marketing['icon_1'] ?? false),
                'icon_2' => (bool) ($marketing['icon_2'] ?? false),
                'icon_3' => (bool) ($marketing['icon_3'] ?? false),
            ],
            'course' => [
                'id' => $course['id'] ?? null,
                'name' => $course['name'] ?? '',
                'type' => $course['type'] ?? '',
            ],
            'teacher' => $teacher !== null ? [
                'id' => $teacher['id'] ?? null,
                'name' => $teacher['name'] ?? '',
                'last_name' => $teacher['last_name'] ?? '',
                'description' => $teacher['description'] ?? '',
                'img_route' => $teacher['img_route'] ?? 'default.webp',
            ] : null,
            'period' => $period !== null ? [
                'id' => $period['id'] ?? null,
                'name' => $period['name'] ?? '',
                'type' => $period['type'] ?? '',
                'current_state' => $period['current_state'] ?? '',
                'start_date' => $period['start_date'] ?? null,
                'end_date' => $period['end_date'] ?? null,
            ] : null,
            'precios' => [
                'inscripcion' => $inscripcion,
                'modulo' => $modulo,
                'texto_cuota' => $textoCuota,
                'multiplicador' => $multiplicador,
                'total' => $total,
            ],
        ];

        return [
            'ok' => true,
            'data' => $datosRetorno,
        ];
    }

    

    //para solicitud inscripcion view, una lista de cursos disponibles para inscribirse   
    public function opciones_inscripcion_controlador(): array {
        $respuesta = publicoModelo::listar_cursos_inscripcion_publico_supabase();
        //var_dump($respuesta);die();
        if (!$respuesta['ok'] || empty($respuesta['data']['ok'])) {
            return [
                'ok' => false,
                'data' => [],
                'message' => 'No se pudieron cargar los cursos disponibles.',
            ];
        }

        return [
            'ok' => true,
            'data' => $respuesta['data']['cursos'] ?? [],
        ];
    }
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*------------------------------------------------funciones viejas--------------------------------------------------*/
  
    
    
/*    public function procesar_evento_controlador($accion, $datos) {
        switch($accion){
            case "click":
                self::registrar_click($datos);
                break;
            case "vista":
                self::registrar_vista($datos);
                break;
        }
    }




public function cargar_inicio_controlador(){
    $datos = [];

    // Cursos publicitarios + cohorte vigente
    try {
        $resultadoCursos = self::obtener_cursos_publicitarios_modelo();
        if (!$resultadoCursos) {
            throw new Exception("La consulta de cursos publicitarios retornó un resultado inválido.");
        }
        $datos['curso'] = $resultadoCursos->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log(" Error al obtener cursos publicitarios: " . $e->getMessage());
        $datos['curso'] = [];
    }

    // Profesores mostrables
    try {
        $resultadoProfesores = self::obtener_profesores_modelo();
        if (!$resultadoProfesores || !is_array($resultadoProfesores)) {
            throw new Exception("La consulta de profesores retornó un resultado inválido.");
        }
        $datos['profesores'] = $resultadoProfesores;
    } catch (Exception $e) {
        error_log("Error al obtener profesores: " . $e->getMessage());
        $datos['profesores'] = [];
    }



    // Información institucional Ceproca
    try {
        $info = self::obtener_info_ceproca_modelo();
        if (!$info || $info->rowCount() != 1) {
            throw new Exception("No se encontró información institucional.");
        }
        $datos['infoceproca'] = $info->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log(" Error al obtener información de Ceproca: " . $e->getMessage());
        $datos['infoceproca'] = [];
    }




    // Imágenes de sliders agrupadas por id_slider 
    try {
        $resultadoSliders = self::obtener_imagenes_slider_modelo(); 
        if (!$resultadoSliders || !is_array($resultadoSliders)) {
            throw new Exception("La consulta de sliders retornó un resultado inválido.");
        }
        $datos['slider'] = $resultadoSliders;
    } catch (Exception $e) {
        error_log(" Error al obtener imágenes de sliders: " . $e->getMessage());
        $datos['slider'] = [
            'slider_1' => [],
            'slider_2' => [],
            'slider_3' => []
        ];
    }


    return $datos;
}







    public function cargar_cursos_controlador() {
        $datos = [];

        try {
            $info = self::obtener_info_ceproca_modelo();
            $datos['infoceproca'] = ($info && $info->rowCount() === 1) ? $info->fetch(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log(" Info institucional: " . $e->getMessage());
            $datos['infoceproca'] = [];
        }

        try {
            $cursos = self::obtener_cursos_extendidos_modelo();
            $datos['cursos_ext'] = ($cursos) ? $cursos->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log(" Cursos extendidos: " . $e->getMessage());
            $datos['cursos_ext'] = [];
        }

        return $datos;
    }

*/

}//fin controlador







