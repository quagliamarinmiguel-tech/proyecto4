<?php

require_once __DIR__ . '/../config/conection.php';
require_once __DIR__ . '/../config/supabaseApi.php';

class mainModel
{
    
    
    /*--------- Funcion conectar a BD ---------*
    public static function conectar()
    {
        $conexion = new PDO(SGBD, USER, PASS);
        $conexion->exec("SET CHARACTER SET utf8");
        return $conexion;
    }*/
    
    
    
    
    /*--------- Funcion conectar a Supabase REST/PostgREST ---------*/
    public static function conectarSupabase()
    {
        return [
            'url' => SUPABASE_URL,
            'key' => SUPABASE_KEY,
            'rest_url' => SUPABASE_REST_URL,
        ];
    }

    public static function supabaseGet($tabla, $select = '*', $filtros = [], $token = null)
    {
        $params = ['select' => $select];

        foreach ($filtros as $campo => $valor) {
            $params[$campo] = $valor;
        }

        if ($token !== null) {
        	return SupabaseApi::getWithToken($tabla, $params, $token);
    	}
    	return SupabaseApi::get($tabla, $params);
    }
    

    
    ## LA REMPLACE POR ESTA FUNCION POST, YA SABES. ME AVISAS
    
    public static function supabasePost($table, $data, $token = null) {
    $token = $token ?? SUPABASE_SECRET_KEY; // o el nombre que uses

    $ch = curl_init();
    curl_setopt_array($ch, [
        CURLOPT_URL => SUPABASE_URL . '/rest/v1/' . $table,
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_POST => true,
        CURLOPT_POSTFIELDS => json_encode($data),
        CURLOPT_HTTPHEADER => [
            'apikey: ' . $token,
            'Authorization: Bearer ' . $token,
            'Content-Type: application/json',
            'Prefer: return=representation'   // ← importante para que te devuelva el registro
        ]
    ]);

    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    $result = [
        'ok' => $httpCode >= 200 && $httpCode < 300,
        'data' => json_decode($response, true),
        'message' => $response,
        'http_code' => $httpCode
    ];

    return $result;
}
    
//tuvo que ser comentado
    /* public static function supabasePost($tabla, $datos, $token)
    {
        return SupabaseApi::post($tabla, $datos);
    }
	*/
    
    public static function supabasePut($tabla, $filtros, $datos)
    {
        return SupabaseApi::put($tabla, $filtros, $datos);
    }

    public static function supabaseDelete($tabla, $filtros = [])
    {
        return SupabaseApi::delete($tabla, $filtros);
    }
    
    public static function supabasePostWithToken($tabla, $datos, $token)
	{
    	return SupabaseApi::postWithToken($tabla, $datos, $token);
	}

	public static function supabaseDeleteWithToken($tabla, $filtros, $token)
	{
    	// Usamos request directamente porque no hay deleteWithToken
    	return SupabaseApi::request('DELETE', $tabla, $filtros, [], $token);
    }
    
    ###################################################################
    
    
    public static function supabaseQuery($tabla, $select = '*', $filtros = [], $opciones = [])
{
    // Parámetros básicos de consulta
    $queryParams = ['select' => $select];

    // Agregar filtros simples (campo=valor)
    foreach ($filtros as $key => $value) {
        if (is_string($key) && strpos($value, 'like.') === 0) {
            // Filtro like: p.ej. 'like.*busqueda*'
            $queryParams[$key] = $value;
        } elseif (is_string($key) && strpos($value, 'in.') === 0) {
            // Filtro in: p.ej. 'in.(a,b)'
            $queryParams[$key] = $value;
        } else {
            // Filtro igualdad simple
            $queryParams[$key] = $value;
        }
    }

    // Opciones de paginación, orden, límite
    if (isset($opciones['limit'])) {
        $queryParams['limit'] = $opciones['limit'];
    }
    if (isset($opciones['offset'])) {
        $queryParams['offset'] = $opciones['offset'];
    }
    if (isset($opciones['order'])) {
        $queryParams['order'] = $opciones['order'];
    }

    // Construir URL
    $url = SUPABASE_REST_URL . "/" . $tabla . "?" . http_build_query($queryParams);

    $headers = [
        'apikey: ' . SUPABASE_KEY,
        'Authorization: Bearer ' . SUPABASE_KEY,
        'Content-Type: application/json',
    ];

    // Si se pide conteo exacto, agregar header especial
    if (isset($opciones['count']) && $opciones['count'] === true) {
        $headers[] = 'Prefer: count=exact';
    }

    $ch = curl_init($url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    $responseHeaders = curl_getinfo($ch, CURLINFO_HEADER_OUT); // no nos da los headers de respuesta, pero para depuración
    curl_close($ch);

    if ($httpCode >= 200 && $httpCode < 300) {
        $data = json_decode($response, true);
        $result = [
            'data' => $data ?? [],
        ];
        // Si se pidió count, intentar leer Content-Range de las cabeceras
        if (isset($opciones['count']) && $opciones['count'] === true) {
            // No podemos leer las cabeceras de respuesta con curl_getinfo directamente,
            // pero podemos hacer otra petición rápida con CURLOPT_HEADER true y luego separarlas.
            // Para simplificar, haremos una segunda petición con HEADER para obtener el total.
            $ch2 = curl_init($url);
            curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch2, CURLOPT_HEADER, true);
            curl_setopt($ch2, CURLOPT_HTTPHEADER, $headers);
            $responseWithHeaders = curl_exec($ch2);
            $headerSize = curl_getinfo($ch2, CURLINFO_HEADER_SIZE);
            $headersStr = substr($responseWithHeaders, 0, $headerSize);
            curl_close($ch2);
            if (preg_match('/Content-Range:\s*(\d+)\/(\d+)/i', $headersStr, $matches)) {
                $result['total'] = (int)$matches[2];
            } else {
                $result['total'] = count($data);
            }
        }
        return $result;
    } else {
        // Error
        return [
            'data' => [],
            'error' => "Error HTTP {$httpCode}: " . $response,
        ];
    }
}
    /* Aqui termina lo que hice */
    ###################################################################
    
    
    
    
    /*------Funcion para crear la API hacia DolarApi.com--------------*/
    public static function obtenerTasaBCV() {
    	$url = 'https://ve.dolarapi.com/v1/dolares/oficial';
    
    	$ch = curl_init();
    	curl_setopt_array($ch, [
        	CURLOPT_URL => $url,
        	CURLOPT_RETURNTRANSFER => true,
        	CURLOPT_TIMEOUT => 10,
        	CURLOPT_HTTPHEADER => ['Accept: application/json']
    	]);
    
    	$response = curl_exec($ch);
    	$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    	curl_close($ch);
    
    	if ($httpCode === 200 && $response) {
        	$data = json_decode($response, true);
        	// La API devuelve un array de cotizaciones, buscamos la oficial
        	foreach ($data as $cotizacion) {
            	if (isset($cotizacion['fuente']) && $cotizacion['fuente'] === 'oficial') {
                	return (float) $cotizacion['promedio']; // Tasa exacta en VES
            	}
        	}
    	}
    	// Si falla la API, devuelve 0 para que el frontend muestre un error o use una tasa de respaldo
    	return 0.00; 
	}

    /*------Aqui termina la funcion para la API DolarAPI----------------*/

    
    
    
    
//devuelve el id de page info
    public static function traer_id_page_info($token){
        $params = [
            'select' => 'id',
            'limit'  => '1',
        ];
        return SupabaseApi::getWithToken('page_info', $params, $token);
    }
    

    
    
/*--------- Funcion ejecutar consultas simples ---------*/
    protected static function ejecutar_consulta_simple($consulta)
    {
        $sql = self::conectar()->prepare($consulta);
        $sql->execute();
        return $sql;
    }


/*--------- Encriptar cadenas ---------*/
    public static function encryption($string)
    {
        $output = FALSE;
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        $output = openssl_encrypt($string, METHOD, $key, 0, $iv);
        $output = base64_encode($output);
        return $output;
    }


/*--------- Desencriptar cadenas ---------*/
    public static function decryption($string)
    {
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        $output = openssl_decrypt(base64_decode($string), METHOD, $key, 0, $iv);
        return $output;
    }


/*--------- Funcion generar codigos aleatorios ---------*/
    //esta creo que no se usa
    protected static function generar_codigo_aleatorio($letra, $longitud, $numero)
    {
        for ($i = 1; $i <= $longitud; $i++) {
            $aleatorio = rand(0, 9);
            $letra .= $aleatorio;
        }
        return $letra . "-" . $numero;
    }




    public static function generarAlertaSimple($texto, $manejo_alerta, $accion)
    //alerta es o simple o recargar
    {
        if ($manejo_alerta == "error") {
            $titulo = "Ocurrió un error inesperado";
            $tipo = "error";
        } else { // si es success
            $titulo = "Operación exitosa";
            $tipo = "success";
        }

        $alerta = [
            "Alerta" => $accion,
            "Titulo" => $titulo,
            "Texto" => $texto,
            "Tipo" => $tipo
        ];

        return $alerta;
    }


//esta permite redireccionar una ves se  devuelva la alerta
    public static function generarAlertaRedireccion($titulo, $texto, $tipo, $url): array{
        return [
            'Alerta' => 'redireccionar',
            'Titulo' => $titulo,
            'Texto' => $texto,
            'Tipo' => $tipo,
            'URL' => $url
        ];
    }
    
    
//esta no se usa
    public static function verificar_sin_permiso($nivel){
        session_start(['name' => 'SPM']);
        if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != $nivel) {
            return true;
        }
        return false;
    }

//esta no se usa
    public static function verificar_existencia_id($elemento, $nombre_tabla, $id){
        $elemento = self::limpiar_cadena($elemento);
        $nombre_tabla = self::limpiar_cadena($nombre_tabla);
        $id = self::limpiar_cadena($id);

        $consulta = " SELECT $elemento FROM $nombre_tabla
             WHERE $elemento = '$id'";
        $resultado = self::ejecutar_consulta_simple($consulta);
        return ($resultado->rowCount() > 0);
    }

//esta no se usa
	 public static function verificar_si_usuario_no_es_activo(){
        if ($_SESSION['estado_usuario'] == 0) {
            return true;        
        }
        return false;
    }
    
    

    /** Reglas:
     *   - Mínimo 8 caracteres
     *   - Al menos 1 mayúscula
     *   - Al menos 1 minúscula
     *   - Al menos 1 número
     *   - Solo letras y números (sin caracteres especiales)
     *
     * @param string $clave Contraseña a validar
     * @return string|null  null si es válida, o el mensaje del primer error encontrado*/
    public static function validar_clave_segura(string $clave): ?string{
        if (strlen($clave) < 8) {
            return 'La contraseña debe tener al menos 8 caracteres';
        }
        if (!preg_match('/[A-Z]/', $clave)) {
            return 'La contraseña debe contener al menos una mayúscula';
        }
        if (!preg_match('/[a-z]/', $clave)) {
            return 'La contraseña debe contener al menos una minúscula';
        }
        if (!preg_match('/[0-9]/', $clave)) {
            return 'La contraseña debe contener al menos un número';
        }
        if (!preg_match('/^[A-Za-z0-9]+$/', $clave)) {
            return 'La contraseña solo puede contener letras y números (sin caracteres especiales)';
        }
        return null;
    }
    
    
    
    
    
    
//las siguientes funciones se usaran para los datos de personalizacion de la pagina, incluyendo solicitud-inscripcion-view, crear-cuenta-view, cambiar-clave-view, login-view y el navlateral.
    
//esta funcion mediante una rpc trae datos del local que se usaran para los formularios de las vistas publicas y en las vistas privadas se guardaran dichos datos en el session
    public static function obtener_config_visual_supabase(): array {
        return SupabaseApi::rpcWithoutToken('obtener_config_visual', []);
    }

//Trae la configuración visual (paleta, nombre, icono, logo).
    public static function obtener_config_visual(): array {
        $resp = SupabaseApi::rpcWithoutToken('obtener_config_visual', []);
        if (!$resp['ok'] || empty($resp['data'])) {
            return self::config_visual_default();
        }
        $data = is_array($resp['data']) ? $resp['data'] : [];
        return [
            'paleta_id'       => (int) ($data['paleta_id'] ?? 0),
            'nombre_corto'    => (string) ($data['nombre_corto'] ?? ''),
            'nombre_completo' => (string) ($data['nombre_completo'] ?? ''),
            'icono'           => (string) ($data['icono'] ?? ''),
            'logo'            => (string) ($data['logo'] ?? ''),
        ];
    }

//esto carga lo que se devuelva como default para los datos publicos
    public static function config_visual_default(): array {
        return [
            'paleta_id'       => 0,
            'nombre_corto'    => '',
            'nombre_completo' => '',
            'icono'           => '',
            'logo'            => '',
        ];
    }

    
//estos son todos los colores disponibles
	// la idea es q el primer color sea el principal de la empresa, el segundo sea uno oscuro q sirva de contraste o sea un tono mas oscuro del mismo color principal,
    // se usaria para hovers, son textos vivos y un poco oscuros para texto claro
	// los otros son colores claros pero que combian con la pagina, colores claros para texto en negro
    
    public static function obtener_paletas(): array {//las paletas 8 y 11 tienen 1 color muy oscuro
        return [
            0  => ['#00a9f1', '#002a6f', '#72F2EB', '#FF5F5D', '#3ca6a6'],
            1  => ['#9C2113', '#014B43', '#F9ECEC', '#FFD6D1', '#099078'], 
            2  => ['#8C1B1B', '#1B1E59', '#1C338C', '#F2F2F2', '#F54927'], /*5:#262626*/
            3  => ['#BF0413', '#A60303', '#F24F13', '#F9ECEC', '#F27329'],
            4  => ['#667302', '#515916', '#518C81', '#D9C196', '#D92A1A'],           
            5  => ['#7A2B1F', '#471B00', '#FFEFC8', '#C76338', '#E8A149'], /*4:#C76338*/
            6  => ['#4F7FE7', '#142C49', '#F3E3C6', '#F9D746', '#FFFFFF'],            
            7  => ['#B3A5CC', '#9E82D9', '#CCBAA6', '#E9EEFF', '#C2ACF2'],            
            8  => ['#27498C', '#1A4173', '#BAA1FF', '#B1BBC7', '#D9D9D7'], /*3:#2E578C*/
            9  => ['#EB7428', '#4F342B', '#F1CDAB', '#D7EFE1', '#EFA438'],
            10 => ['#B73736', '#D48392', '#FBFFFF', '#EBC9C0', '#CFAA7D'],           
            11 => ['#024959', '#208C8C', '#6EFFB4', '#89D9C2', '#3ca6a6'], /*3:#010326 / 5:#0D0D0D*/
		    12 => ['#94215A', '#5C1C3C', '#F5F3F0', '#E8DFD1', '#BAAC97'],   		   
            13 => ['#2E5902', '#193C40', '#3CBA36', '#D96941', '#CF3427'], /*3:#1E661B / 5:#A62B1F*/
        ];
    }

//esto devuelve el color elegido
    public static function obtener_colores_config(): array {
        $config = self::obtener_config_visual();
        $paletas = self::obtener_paletas();
        $id = (int) ($config['paleta_id'] ?? 0);
        return $paletas[$id] ?? $paletas[0];
    }

//esto cambia el formato de los colores elegidos a rgba
    public static function hex2rgba(string $hex, float $alpha = 1): string {
        $hex = str_replace('#', '', $hex);
        if (strlen($hex) === 3) {
            $r = hexdec(substr($hex, 0, 1) . substr($hex, 0, 1));
            $g = hexdec(substr($hex, 1, 1) . substr($hex, 1, 1));
            $b = hexdec(substr($hex, 2, 1) . substr($hex, 2, 1));
        } else {
            $r = hexdec(substr($hex, 0, 2));
            $g = hexdec(substr($hex, 2, 2));
            $b = hexdec(substr($hex, 4, 2));
        }
        return "rgba($r, $g, $b, $alpha)";
    }
    
    
   
//Extrae la paleta activa desde el JSON de colores de local view.
//Acepta array, stdClass o string (incluso doblemente escapado).  
    public static function extraer_paleta_activa($colores_pagina, int $default = 0): int {
        if (empty($colores_pagina)) return $default;

        $decoded = null;

        if (is_array($colores_pagina)) {
            $decoded = $colores_pagina;
        } elseif (is_object($colores_pagina)) {
            $decoded = (array) $colores_pagina;
        } else {
            $decoded = json_decode((string) $colores_pagina, true);
            if (!is_array($decoded)) {
                $clean = stripslashes((string) $colores_pagina);
                $decoded = json_decode($clean, true);
            }
        }

        return (is_array($decoded) && isset($decoded['paleta_id']))
            ? (int) $decoded['paleta_id']
            : $default;
    }


// Ruta del JSON
    private static function ruta_json_local(): string {
        return __DIR__ . '/../vistas/cm/local_data.json';
    }


    public static function guardar_json_local(array $textos, int $paletaId, array $contacto = []): bool {
        $data = [
            'textos'     => $textos,
            'paleta_id'  => $paletaId,
            'contacto'   => $contacto,
            'updated_at' => date('c'),
        ];

        $json = json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
        if ($json === false) return false;

        $ruta = self::ruta_json_local();
        $dir  = dirname($ruta);
        if (!is_dir($dir)) @mkdir($dir, 0755, true);

        $tmp = $ruta . '.tmp';
        if (@file_put_contents($tmp, $json) === false) return false;
        return @rename($tmp, $ruta);
    }
    
//Lee el JSON de vistas/cm/local_data.json.
//@param array|null $claves  Claves a extraer del JSON. Si es null, trae todo.
//@return array|null
 
    public static function leer_json_local(?array $claves = null): ?array {
        $ruta = __DIR__ . '/../vistas/cm/local_data.json';
        if (!file_exists($ruta)) return null;

        $contenido = @file_get_contents($ruta);
        if ($contenido === false) return null;

        $data = json_decode($contenido, true);
        if (!is_array($data)) return null;

        // Sin claves → devuelve todo
        if ($claves === null) {
            return $data;
        }

        // Con claves → filtra solo las que existan
        $resultado = [];
        foreach ($claves as $clave) {
            if (array_key_exists($clave, $data)) {
                $resultado[$clave] = $data[$clave];
            }
        }

        return $resultado;
    }
    
    
 
    
    
/*--------- Funcion limpiar cadenas ---------*/
    public static function limpiar_cadena($cadena)
    {
        $cadena = trim($cadena);
        $cadena = stripslashes($cadena);
        $cadena = str_ireplace("<script>", "", $cadena);
        $cadena = str_ireplace("</script>", "", $cadena);
        $cadena = str_ireplace("<script src", "", $cadena);
        $cadena = str_ireplace("<script type=", "", $cadena);
        $cadena = str_ireplace("SELECT * FROM", "", $cadena);
        $cadena = str_ireplace("DELETE FROM", "", $cadena);
        $cadena = str_ireplace("INSERT INTO", "", $cadena);
        $cadena = str_ireplace("DROP TABLE", "", $cadena);
        $cadena = str_ireplace("DROP DATABASE", "", $cadena);
        $cadena = str_ireplace("TRUNCATE TABLE", "", $cadena);
        $cadena = str_ireplace("SHOW TABLES", "", $cadena);
        $cadena = str_ireplace("SHOW DATABASES", "", $cadena);
        $cadena = str_ireplace("<?php", "", $cadena);
        $cadena = str_ireplace("?>", "", $cadena);
        $cadena = str_ireplace("--", "", $cadena);
        $cadena = str_ireplace(">", "", $cadena);
        $cadena = str_ireplace("<", "", $cadena);
        $cadena = str_ireplace("[", "", $cadena);
        $cadena = str_ireplace("]", "", $cadena);
        $cadena = str_ireplace("^", "", $cadena);
        $cadena = str_ireplace("==", "", $cadena);
        $cadena = str_ireplace(";", "", $cadena);
        $cadena = str_ireplace("::", "", $cadena);
        $cadena = stripslashes($cadena);
        $cadena = trim($cadena);
        return $cadena;
    }


/*--------- Funcion verificar datos ---------*/
    public static function verificar_datos($filtro, $cadena)
    {
        if (preg_match("/^" . $filtro . "$/", $cadena)) {
            return false;
        } else {
            return true;
        }
    }


/*--------- Funcion verificar fechas ---------*/
    protected static function verificar_fecha($fecha)
    {
        $valores = explode('-', $fecha);
        if (count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])) {
            return false;
        } else {
            return true;
        }
    }

    
    
/*-------------------------validaciones de cedula, telefono y correo---------------------------*/
    public static function validar_cedula_universal($ci){
        $ci = self::limpiar_cadena($ci);
        $ci_upper = strtoupper($ci);
        if (!preg_match('/^[VE][1-9][0-9]{6,7}$/', $ci_upper)) {
            return true;
        }
        return false;
    }

	public static function validar_telefono_universal($telefono){
        //telefono debe tener 11 digitos y la compania o zona geografica de numeros telefonicos deben ser de u operar en guarico
        //retorna true tonces if true te jodes
        $telefono = self::limpiar_cadena($telefono);
        if (!preg_match('/^\d{11}$/', $telefono)) {
            return true;        
        }
        $prefijos_validos = ['0212', '0412', '0414', '0415', '0416', '0417', '0418', '0424', '0426', '0246', '0247', '0235', '0238', '0239'];
        $prefijo = substr($telefono, 0, 4);
        $sufijo = substr($telefono, 4);
        if (!in_array($prefijo, $prefijos_validos)) {
            return true;        
        }
        if ($sufijo === '0000000') {
            return true;        
        }

        return false;
    }    

		public static function verificar_cedula_duplicada_supabase($ci, $tabla, $accessToken, $id_excluir = null): array{ 
            // basicamente define ci para compararlo despues, eq es equal y neq.0 significa not equal to 0
            $filtros = [
                'select' => 'id',
                'ci' => 'eq.' . $ci,
                'state' => 'neq.0',
            ];

            if ($id_excluir !== null && $id_excluir !== '') {
                $filtros['id'] = 'neq.' . $id_excluir;
            }

            return SupabaseApi::getWithToken($tabla, $filtros, $accessToken);
        }

        public static function verificar_correo_duplicado_supabase($email, $tabla, $accessToken, $id_excluir = null): array{
            $filtros = [
                'select' => 'id',
                'email' => 'eq.' . $email,
                'state' => 'neq.0',
            ];

            if ($id_excluir !== null && $id_excluir !== '') {
                $filtros['id'] = 'neq.' . $id_excluir;
            }

            return SupabaseApi::getWithToken($tabla, $filtros, $accessToken);
        }

        public static function verificar_telefono_duplicado_supabase($telefono, $tabla, $accessToken, $id_excluir = null): array{
            // basicamente define ci para compararlo despues, eq es equal y neq.0 significa not equal to 0
            $filtros = [
                'select' => 'id',
                'phone' => 'eq.' . $telefono,
                'state' => 'neq.0',
            ];

            if ($id_excluir !== null && $id_excluir !== '') {
                $filtros['id'] = 'neq.' . $id_excluir;
            }

            return SupabaseApi::getWithToken($tabla, $filtros, $accessToken);
        }


    
    
    
    
    
    
    



//esta no se si se usa, creo que no
/*--------- Función para archivado en cascada ---------*/
    protected static function archivar_registros_cascada($conexion, $tabla, $columna_id, $id_valor, $configuracion){
        try {
            // 1. Archivar el registro principal
            // El nombre de la columna de estado se define en el mapa para mayor flexibilidad
            $col_estado = $configuracion['columna_estado'];
            $sql = $conexion->prepare("UPDATE $tabla SET $col_estado = 0 WHERE $columna_id = :id");
            $sql->execute([':id' => $id_valor]);

            // 2. Procesar hijos (si existen)
            if (isset($configuracion['hijos']) && is_array($configuracion['hijos'])) {
                foreach ($configuracion['hijos'] as $hijo) {

                    // Buscar los IDs de los hijos para seguir la cadena
                    $stmt = $conexion->prepare("SELECT {$hijo['columna_id']} FROM {$hijo['tabla']} WHERE {$hijo['columna_fk']} = :id");
                    $stmt->execute([':id' => $id_valor]);
                    $registros_hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($registros_hijos as $reg_hijo) {
                        $id_hijo = $reg_hijo[$hijo['columna_id']];

                        // Llamada recursiva: Archiva al hijo y a sus propios hijos
                        self::archivar_registros_cascada(
                            $conexion,
                            $hijo['tabla'],
                            $hijo['columna_id'],
                            $id_hijo,
                            $hijo
                        );
                    }
                }
            }

            if (isset($configuracion['extra_sql'])) {
                $stmt_extra = $conexion->prepare($configuracion['extra_sql']);
                $stmt_extra->execute([':id' => $id_valor]);
            }

        } catch (Exception $e) {
            throw new Exception("Error en tabla [$tabla]: " . $e->getMessage());
        }
    }

    
    
    
    

/*--------- Funcion paginador de tablas ---------*/
    protected static function paginador_tablas($pagina, $Npaginas, $url, $botones)
    {
        $tabla = '<nav aria-label="Page navigation example"><ul class="pagination justify-content-center">';

        if ($pagina == 1) {
            $tabla .= '<li class="page-item disabled"><a class="page-link"><i class="fas fa-angle-double-left"></i></a></li>';
        } else {
            $tabla .= '
                <li class="page-item"><a class="page-link" href="' . $url . '1"><i class="fas fa-angle-double-left"></i></a></li>
                <li class="page-item"><a class="page-link" href="' . $url . ($pagina - 1) . '">Anterior</a></li>
                ';
        }

        $ci = 0;
        for ($i = $pagina; $i <= $Npaginas; $i++) {
            if ($ci >= $botones) {
                break;
            }
            if ($pagina == $i) {
                $tabla .= '<li class="page-item"><a class="page-link active" href="' . $url . $i . '">' . $i . '</a></li>';
            } else {
                $tabla .= '<li class="page-item"><a class="page-link" href="' . $url . $i . '">' . $i . '</a></li>';
            }
            $ci++;
        }

        if ($pagina == $Npaginas) {
            $tabla .= '<li class="page-item disabled"><a class="page-link"><i class="fas fa-angle-double-right"></i></a></li>';
        } else {
            $tabla .= '
                <li class="page-item"><a class="page-link" href="' . $url . ($pagina + 1) . '">Siguiente</a></li>
                <li class="page-item"><a class="page-link" href="' . $url . $Npaginas . '"><i class="fas fa-angle-double-right"></i></a></li>
                ';
        }

        $tabla .= '</ul></nav>';
        return $tabla;
    }
    

    protected static function ejecutar_consulta_paginada($consulta, $inicio, $registros)
    {
        $conexion = self::conectar();
        $datos = $conexion->query($consulta)->fetchAll();
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();

        return [
            "datos" => $datos,
            "total" => $total,
            "paginas" => ceil($total / $registros)
        ];
    }



    
    
    
    
/**
 * Sube una imagen al servidor y devuelve la ruta relativa.
 * @param array $archivo $_FILES['archivo_imagen']
 * @param string $prefijo Prefijo para el nombre (ej: "prof_")
 * @return array ['ok' => bool, 'ruta' => string, 'ruta_absoluta' => string, 'mensaje' => string]
 */
    public static function subir_imagen_fisica($archivo, $carpetaDestino, $prefijo = 'img_'){
        // Validar archivo
        if (!isset($archivo['tmp_name']) || $archivo['error'] !== 0) {
            return ['ok' => false, 'mensaje' => 'Archivo no válido o con errores de subida.'];
        }

        // Validar extensión
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp'];
        if (!in_array($ext, $permitidas)) {
            return ['ok' => false, 'mensaje' => 'Formato no permitido. Use JPG, JPEG, PNG o WEBP.'];
        }

        // Crear carpeta si no existe
        if (!file_exists($carpetaDestino)) {
            if (!mkdir($carpetaDestino, 0777, true)) {
                return ['ok' => false, 'mensaje' => 'No se pudo crear la carpeta de destino.'];
            }
        }

        // Generar nombre único
        $nombreFinal = uniqid($prefijo) . '.' . $ext;
        $rutaCompleta = $carpetaDestino . $nombreFinal;

        // Mover archivo
        if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            return ['ok' => false, 'mensaje' => 'Error al mover el archivo al servidor.'];
        }

        // Calcular ruta relativa (desde la raíz del proyecto)
        // Ejemplo: si $carpetaDestino = "../vistas/cm/prof/", la ruta relativa será "cm/prof/nombre.jpg"
        $rutaRelativa = str_replace("../vistas/", "", $carpetaDestino) . $nombreFinal;

        return [
            'ok' => true,
            'ruta' => $rutaRelativa,
            'ruta_absoluta' => $rutaCompleta,
            'mensaje' => 'Imagen subida correctamente.'
        ];
    }
    


    public static function validar_imagenes(array $archivos, array $restriccionesPorImagen): array{
        foreach ($restriccionesPorImagen as $nombre => $restricciones) {
            if (!isset($archivos[$nombre]) || $archivos[$nombre]['error'] !== 0) {
                continue;
            }

            $nombreOriginal = basename($archivos[$nombre]['name']);
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'ico'];

            if (!in_array($ext, $permitidas)) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' tiene un formato no permitido."
                ];
            }

            $dimensiones = getimagesize($archivos[$nombre]['tmp_name']);
            if (!$dimensiones) {
                return [
                    'status' => 'error',
                    'mensaje' => "No se pudieron obtener las dimensiones de la imagen '$nombre'."
                ];
            }

            $ancho = $dimensiones[0];
            $alto = $dimensiones[1];
            $formaDetectada = self::determinar_forma_imagen($ancho, $alto);
            $formaEsperada = $restricciones['forma'] ?? '';

            if ($formaEsperada !== '') {
                if ($formaEsperada === 'horizontal_o_cuadrada') {
                    if ($formaDetectada === 'vertical') {
                        return [
                            'status' => 'error',
                            'mensaje' => "La imagen '$nombre' debe ser horizontal o cuadrada, pero se detectó vertical."
                        ];
                    }
                } elseif ($formaDetectada !== $formaEsperada) {
                    return [
                        'status' => 'error',
                        'mensaje' => "La imagen '$nombre' debe ser $formaEsperada, pero se detectó $formaDetectada."
                    ];
                }
            }

            if (!empty($restricciones['min_width']) && $ancho < $restricciones['min_width']) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' debe tener al menos {$restricciones['min_width']} px de ancho."
                ];
            }
            if (!empty($restricciones['min_height']) && $alto < $restricciones['min_height']) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' debe tener al menos {$restricciones['min_height']} px de alto."
                ];
            }
            if (!empty($restricciones['max_width']) && $ancho > $restricciones['max_width']) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' no debe superar {$restricciones['max_width']} px de ancho."
                ];
            }
            if (!empty($restricciones['max_height']) && $alto > $restricciones['max_height']) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' no debe superar {$restricciones['max_height']} px de alto."
                ];
            }
        }

        return ['status' => 'success', 'mensaje' => 'OK'];
    }

    private static function determinar_forma_imagen(int $ancho, int $alto, float $tolerancia = 0.05): string{
        if ($alto === 0) {return 'desconocida';}

        $ratio = $ancho / $alto;
        if (abs($ratio - 1) <= $tolerancia) {return 'cuadrada';}

        return $ratio > 1 ? 'horizontal' : 'vertical';   
    }
    
    
        /**
     * Devuelve la URL de una imagen local por nombre base.
     * Busca cualquier extensión (jpg, jpeg, png, webp, ico).
     * Agrega ?v=mtime para cache busting.
     */
    public static function obtenerRutaImagenLocal(string $nombreBase): string {
        $carpeta = __DIR__ . '/../vistas/cm/ifpg/';
        $extensiones = ['jpg', 'jpeg', 'png', 'webp', 'ico'];

        foreach ($extensiones as $ext) {
            $ruta = $carpeta . $nombreBase . '.' . $ext;
            if (file_exists($ruta)) {
                return SERVERURL . 'vistas/cm/ifpg/' . $nombreBase . '.' . $ext . '?v=' . filemtime($ruta);
            }
        }

        // Fallback por defecto
        return SERVERURL . 'vistas/assets/img/default.webp';
    }

    /**
     * Devuelve true si existe una imagen local con ese nombre base.
     */
    public static function existeImagenLocal(string $nombreBase): bool {
        $carpeta = __DIR__ . '/../vistas/cm/ifpg/';
        $extensiones = ['jpg', 'jpeg', 'png', 'webp', 'ico'];
        foreach ($extensiones as $ext) {
            if (file_exists($carpeta . $nombreBase . '.' . $ext)) {
                return true;
            }
        }
        return false;
    }
    

    
    
//----------------------funciones de cache-------------------------------------------------------
  
//Genera la ruta completa para un archivo de caché.
    private static function getCachePath(string $clave): string {
        // Aseguramos que el nombre del archivo sea seguro
        $nombreArchivo = preg_replace('/[^a-zA-Z0-9_-]/', '_', $clave) . '.json';
        return __DIR__ . '/../cache/' . $nombreArchivo;
    }

// * Guarda datos en un archivo de caché.
    public static function guardarCache(string $clave, $datos, int $ttl = 3600): bool {
        $ruta = self::getCachePath($clave);
        $contenido = [
            'expira' => time() + $ttl,
            'datos'  => $datos,
        ];
        return @file_put_contents($ruta, json_encode($contenido, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)) !== false;
    }

// * Lee datos de un archivo de caché si no ha expirado.
    public static function leerCache(string $clave) {
        $ruta = self::getCachePath($clave);
        if (!file_exists($ruta)) {
            return null;
        }

        $contenido = @file_get_contents($ruta);
        if ($contenido === false) {
            return null;
        }

        $data = json_decode($contenido, true);
        if (!is_array($data) || !isset($data['expira']) || !isset($data['datos'])) {
            return null;
        }

        // Si ha expirado, borramos el archivo y devolvemos null
        if ($data['expira'] < time()) {
            @unlink($ruta);
            return null;
        }

        return $data['datos'];
    }

/**
     * Invalida (borra) un archivo de caché específico.
     * Útil para llamar después de actualizar datos.
*/
    public static function invalidarCache(string $clave): void {
        $ruta = self::getCachePath($clave);
        if (file_exists($ruta)) {
            @unlink($ruta);
        }
    }
    

/**
     * Invalida todos los archivos de caché cuyo nombre empiece por el prefijo dado.
     * Útil cuando las claves son dinámicas (filtros + paginación).
*/
    public static function invalidarCachePorPrefijo(string $prefijo): void {
        $patron = __DIR__ . '/../cache/' . $prefijo . '*.json';
        $archivos = glob($patron);
        if ($archivos === false) {
            return;
        }
        foreach ($archivos as $archivo) {
            @unlink($archivo);
        }
    }
    
    
    
    
    

}