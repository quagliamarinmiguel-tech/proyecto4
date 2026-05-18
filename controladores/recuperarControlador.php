<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

//enviar_codigo_por_correo
	if($peticionAjax){
		require_once __DIR__ . '/../modelos/login_mdo.php';
        require_once "../modelos/recuperarModelo.php";
	}else{
		require_once __DIR__ . '/../modelos/login_mdo.php';
        require_once "./modelos/recuperarModelo.php";

	}



	class recuperarControlador extends recuperarModelo{


public function verificar_identidad_controlador() {
    $cedula = mainModel::limpiar_cadena($_POST['cedula']);
    $telefono = mainModel::limpiar_cadena($_POST['telefono']);
    $correo = mainModel::limpiar_cadena($_POST['correo']);
    $clave = mainModel::limpiar_cadena($_POST['clave']);
    $confirmar = mainModel::limpiar_cadena($_POST['confirmar_clave']);
    $fecha_nacimiento = mainModel::limpiar_cadena($_POST['fecha_nacimiento']);
    $ip = $_SERVER['REMOTE_ADDR'];


    mainModel::registrar_ip_visitante(mainModel::conectar(), $ip);

    if ($cedula == "" || $telefono == "" || $correo == "" || $clave == "" || $confirmar == "" || $fecha_nacimiento == "") {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Campos incompletos",
            "Texto" => "Debes llenar todos los campos requeridos, incluyendo la nueva clave y fecha de nacimiento.",
            "Tipo" => "error"
        ]);
    }

    if ($clave !== $confirmar) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "Las contraseñas no coinciden",
            "Tipo" => "error"
        ]);
    }

    if (strlen($clave) < 7 || strlen($clave) > 100) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Clave inválida",
            "Texto" => "La contraseña debe tener entre 7 y 100 caracteres",
            "Tipo" => "error"
        ]);
    }

    if (strtotime($fecha_nacimiento) < strtotime("1900-01-01")) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Fecha inválida",
            "Texto" => "La fecha de nacimiento no puede ser anterior a 1900.",
            "Tipo" => "error"
        ]);
    }
    
    $bloqueo = mainModel::ip_bloqueada($ip, 'recuperacion');
    if ($bloqueo['bloqueado']) {
        $minutos = ceil($bloqueo['tiempo_restante'] / 60);
        /*echo '<script>
            Swal.fire({
                title: "Acceso bloqueado",
                text: "Has excedido el número de intentos. Intenta más tarde. Tiempo restante: ' . $minutos . ' minutos.",
                icon: "error",
                confirmButtonText: "Aceptar"
            });
        </script>';*/
        
        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Acceso bloqueado",
            "Texto" => "Has excedido el número de intentos. Intenta más tarde. Tiempo restante: $minutos minutos.",
            "Tipo" => "error"
        ]);
        
        //exit();
    }

    $datos = [
        "Cedula" => $cedula,
        "Telefono" => $telefono,
        "Correo" => $correo,
        "FechaNacimiento" => $fecha_nacimiento
    ];
    $usuario = recuperarModelo::buscar_usuario_por_datos($datos);

    if ($usuario->rowCount() == 1) {
        $row = $usuario->fetch();

        if ((int)$row['cargo'] !== 5) {
        return json_encode([

            "Alerta" => "simple",
            "Titulo" => "Acceso denegado",
            "Texto" => "Solo los usuarios con rol de estudiante pueden recuperar su clave por este medio.",
            "Tipo" => "error"
        ]);

           // exit();
        }

        //$codigo = random_int(100000, 999999);
       // $expira = time() + 300;
        //recuperarModelo::guardar_codigo_otp($row['ID_usuario'], $codigo, $expira);
        //recuperarModelo::enviar_codigo_por_correo($correo, $codigo);

        //session_start(['name'=>'SPM']);

        //$_SESSION['recuperacion_autorizada'] = true;
        //$_SESSION['usuario_recuperacion'] = $row['ID_usuario'];
        mainModel::reiniciar_intentos_ip($ip, 'recuperacion');

        $clave_encriptada = mainModel::encryption($clave);
        $usuario_encriptado = mainModel::encryption($row['ID_usuario']);
        $wou=$row['ID_usuario'];
        $ip_encriptada = mainModel::encryption($ip); // ← función que busca el ID en la tabla



            $actualizado = recuperarModelo::actualizar_clave($row['ID_usuario'], $clave_encriptada);

            if ($actualizado) {
                // Limpiar sesión
                unset($_SESSION['recuperacion_autorizada']);
                unset($_SESSION['usuario_recuperacion']);
                unset($_SESSION['codigo_validado']);

                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Contraseña actualizada",
                    "Texto" => "Ya puedes iniciar sesión con tu nueva clave. Tu identidad ha sido verificada correctamente.",
                    "Tipo" => "success",
                   // "URL" => SERVERURL . "index.php"
                ]);
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error",
                    "Texto" => "No se pudo actualizar la contraseña. Intenta nuevamente.",
                    "Tipo" => "error"
                ]);
            }

        //$url = SERVERURL . "recuperar-cuenta-validar&id_usuario=$usuario_encriptado&id_ip=$ip_encriptada";

      //  header("Location: " . $url);
        //exit();
                        
                    //  "URL" => SERVERURL . "index.php?views=cambiar-clave?id_usuario=$wou&id_ip=$ip_encriptada"
        //  "URL" => SERVERURL . "views/cambiar-clave?id_usuario=$wou&id_ip=$ip_encriptada"
          // "URL" => SERVERURL . "index.php?views=recuperar-cuenta-validar&id_usuario=$usuario_encriptado&id_ip=$ip_encriptada"
            //$url = SERVERURL . "recuperar-cuenta-validar?id_usuario=$usuario_encriptado&id_ip=$ip_encriptada";

        //header("Location: " . SERVERURL . "index.php?views=public/recuperar_cuenta_validar");
       // exit();

/*
        echo '<script>
            Swal.fire({
                title: "Verificación exitosa",
                text: "Se ha enviado un código a tu correo",
                icon: "success",
                confirmButtonText: "Continuar"
            }).then(() => {
                window.location.href = "' . SERVERURL . 'rrecuperar_cuenta_valida";
            });
        </script>';*/
    } else {
        mainModel::registrar_intento_ip($ip, 'recuperacion');

        return json_encode([

            "Alerta" => "simple",
            "Titulo" => "Datos incorrectos",
            "Texto" => "No se encontró coincidencia con los datos ingresados",
            "Tipo" => "error"
        ]);
        //exit();  echo
    }
}


/*
public function verificar_identidad_controlador() {
    session_start(['name'=>'SPM']);
    $cedula = mainModel::limpiar_cadena($_POST['cedula']);
    $telefono = mainModel::limpiar_cadena($_POST['telefono']);
    $correo = mainModel::limpiar_cadena($_POST['correo']);
    $ip = $_SERVER['REMOTE_ADDR'];

    // Validación básica
    if ($cedula == "" || $telefono == "" || $correo == "") {
        echo '<script>
            Swal.fire({
                title: "Campos incompletos",
                text: "Debes llenar todos los campos requeridos",
                icon: "error",
                confirmButtonText: "Aceptar"
            });
        </script>';
        exit();
    }

    // Verificar si IP está bloqueada
    if (mainModel::ip_bloqueada($ip, 'recuperacion')) {
        echo '<script>
            Swal.fire({
                title: "Acceso bloqueado",
                text: "Has excedido el número de intentos. Intenta más tarde.",
                icon: "error",
                confirmButtonText: "Aceptar"
            });
        </script>';
        exit();
    }

    // Buscar usuario
    $datos = [
        "Cedula" => $cedula,
        "Telefono" => $telefono,
        "Correo" => $correo
    ];
    $usuario = recuperarModelo::buscar_usuario_por_datos($datos);

    if ($usuario->rowCount() == 1) {
        $row = $usuario->fetch();

        // Validar que el cargo sea estudiante (5)
        if ((int)$row['cargo'] !== 5) {
            echo '<script>
                Swal.fire({
                    title: "Acceso denegado",
                    text: "Solo los usuarios con rol de estudiante pueden recuperar su clave por este medio.",
                    icon: "warning",
                    confirmButtonText: "Aceptar"
                });
            </script>';
            exit();
        }

        // Generar código OTP
        $codigo = random_int(100000, 999999);
        $expira = time() + 300;
        recuperarModelo::guardar_codigo_otp($row['ID_usuario'], $codigo, $expira);

        $_SESSION['recuperacion_autorizada'] = true;
        $_SESSION['usuario_recuperacion'] = $row['ID_usuario'];

        echo '<script>
            Swal.fire({
                title: "Verificación exitosa",
                text: "Se ha enviado un código a tu correo",
                icon: "success",
                confirmButtonText: "Continuar"
            }).then(() => {
                window.location.href = "' . SERVERURL . 'recuperar_cuenta_validar/";
            });
        </script>';
    } else {
        // Registrar intento fallido
        mainModel::registrar_intento_ip($ip, 'recuperacion');
        echo '<script>
            Swal.fire({
                title: "Datos incorrectos",
                text: "No se encontró coincidencia con los datos ingresados",
                icon: "error",
                confirmButtonText: "Aceptar"
            });
        </script>';
    }
}
*/



public function validar_codigo_controlador() {
    $codigo = mainModel::limpiar_cadena($_POST['codigo_otp']);
    $usuario_id = mainModel::limpiar_cadena($_POST['id_usuario']);
    $ip = mainModel::limpiar_cadena($_POST['direccion_ip']);

    // Validar que la sesión esté activa
    if (!isset($_SESSION['recuperacion_autorizada']) || $_SESSION['usuario_recuperacion'] != $usuario_id) {
        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Acceso no autorizado",
            "Texto" => "Debe completar el paso anterior",
            "Tipo" => "error"
        ]);
    }

    // Validar el código OTP
    $valido = recuperarModelo::validar_codigo_otp($usuario_id, $codigo);

    if ($valido) {
        $_SESSION['codigo_validado'] = true;

        // Encriptar datos para la vista de cambio de clave
        $usuario_encriptado = mainModel::encryption($usuario_id);
        $ip_encriptada = mainModel::encryption($ip);


        //header("Location: " . SERVERURL . "cambiar-clave?id_usuario=' . $usuario_encriptado . '&id_ip=' . $ip_encriptada . '");
        header("Location: " . SERVERURL . "cambiar-clave?id_usuario=" . $usuario_encriptado . "&id_ip=" . $ip_encriptada);
        exit();

    } else {

        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Código incorrecto",
            "Texto" => "Verifica el código ingresado",
            "Tipo" => "error"
        ]);
    }
}












        public function cambiar_clave_controlador() {
            $clave = mainModel::limpiar_cadena($_POST['nueva_clave']);
            $confirmar = mainModel::limpiar_cadena($_POST['confirmar_clave']);
            $usuario_id = mainModel::limpiar_cadena($_POST['id_usuario']);

            //$usuario_id = $_POST['id_usuario'] ?? ($_SESSION['usuario_recuperacion'] ?? null);

            // Validar sesión y autorización
            /*
            if (!$usuario_id || !isset($_SESSION['codigo_validado']) || !isset($_SESSION['recuperacion_autorizada'])) {
                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Acceso no autorizado",
                    "Texto" => "Debes validar tu código antes de cambiar la clave",
                    "Tipo" => "error"
                ]);
            }*/

            // Validar coincidencia de claves
            if ($clave !== $confirmar) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error",
                    "Texto" => "Las contraseñas no coinciden",
                    "Tipo" => "error"
                ]);
            }

            // Validar patrón mínimo (opcional pero recomendable)
            if (strlen($clave) < 7 || strlen($clave) > 100) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Clave inválida",
                    "Texto" => "La contraseña debe tener entre 7 y 100 caracteres",
                    "Tipo" => "error"
                ]);
            }

            // Encriptar con método del sistema
            $clave_encriptada = mainModel::encryption($clave);

            // Actualizar en la base de datos
            $actualizado = recuperarModelo::actualizar_clave($usuario_id, $clave_encriptada);

            if ($actualizado) {
                // Limpiar sesión
                unset($_SESSION['recuperacion_autorizada']);
                unset($_SESSION['usuario_recuperacion']);
                unset($_SESSION['codigo_validado']);

                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Contraseña actualizada",
                    "Texto" => "Ya puedes iniciar sesión con tu nueva clave $usuario_id",
                    "Tipo" => "success",
                    //"URL" => SERVERURL . "index.php"
                ]);
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error",
                    "Texto" => "No se pudo actualizar la contraseña. Intenta nuevamente.",
                    "Tipo" => "error"
                ]);
            }
        }


		
		






public function crear_cuenta_est_controlador() {
    $cedula = mainModel::limpiar_cadena($_POST['est-cedula']);
    $telefono = mainModel::limpiar_cadena($_POST['est-telefono']);
    $correo = mainModel::limpiar_cadena($_POST['est-correo']);
    $clave = mainModel::limpiar_cadena($_POST['clave']);
    $confirmar = mainModel::limpiar_cadena($_POST['confirmar_clave']);
    $fecha_nacimiento = mainModel::limpiar_cadena($_POST['est-fecha_nacimiento']);
    $ip = $_SERVER['REMOTE_ADDR'];

    mainModel::registrar_ip_visitante(mainModel::conectar(), $ip);

    // Validaciones básicas
    if ($cedula == "" || $telefono == "" || $correo == "" || $clave == "" || $confirmar == "" || $fecha_nacimiento == "") {

            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "Campos incompletos. Debes llenar todos los campos.",
                "Tipo" => "error"
            ]);

       // return mainModel::respuesta_error("Campos incompletos", "Debes llenar todos los campos.");
    }

    if ($clave !== $confirmar) {

            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "Verifica que ambas claves sean iguales.",
                "Tipo" => "error"
            ]);

       // return mainModel::respuesta_error("Contraseñas no coinciden", "Verifica que ambas claves sean iguales.");
    }

    if (strlen($clave) < 7 || strlen($clave) > 100) {

            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "La clave debe tener entre 7 y 100 caracteres.",
                "Tipo" => "error"
            ]);
       // return mainModel::respuesta_error("Clave inválida", "Debe tener entre 7 y 100 caracteres.");
    }

    if (strtotime($fecha_nacimiento) < strtotime("1900-01-01")) {

            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "No se aceptan fechas anteriores a 1900.",
                "Tipo" => "error"
            ]);

     //   return mainModel::respuesta_error("Fecha inválida", "No se aceptan fechas anteriores a 1900.");
    }

    // Verificar si IP está bloqueada
    $bloqueo = mainModel::ip_bloqueada($ip, 'cc');
    if ($bloqueo['bloqueado']) {
        $minutos = ceil($bloqueo['tiempo_restante'] / 60);

            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Error",
                "Texto" => "Has excedido los intentos. Intenta en $minutos minutos.",
                "Tipo" => "error"
            ]);
       // return mainModel::respuesta_error("Acceso bloqueado", "Has excedido los intentos. Intenta en $minutos minutos.");
    }

    // Buscar estudiante
    $datos = [
        "Cedula" => $cedula,
        "Telefono" => $telefono,
        "Correo" => $correo,
        "FechaNacimiento" => $fecha_nacimiento
    ];
    $estudiante = recuperarModelo::buscar_estudiante_sin_usuario($datos);

    if ($estudiante->rowCount() == 1) {
        $row = $estudiante->fetch();

    //    if ((int)$row['cargo'] !== 5) {
      //      return mainModel::respuesta_error("Acceso denegado", "Solo estudiantes pueden crear cuenta por este medio.");
        //}

        // Verificar si ya tiene cuenta
       // $existeCuenta = recuperarModelo::verificar_usuario_existente($row['ID_estudiante']);
    //  if ($existeCuenta->rowCount() > 0) {
  //          return mainModel::respuesta_error("Cuenta existente", "Este estudiante ya tiene una cuenta registrada.");
//        }

        // Crear usuario
        $clave_encriptada = mainModel::encryption($clave);
        $usuarioCreado = recuperarModelo::crear_usuario_estudiante($row['id_estudiante'], $clave_encriptada);

        if ($usuarioCreado) {
            // Registrar relación en usuario_estudiante
           // recuperarModelo::registrar_usuario_estudiante($usuarioCreado, $row['id_estudiante']);

            mainModel::reiniciar_intentos_ip($ip, 'cc');

            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Cuenta creada",
                "Texto" => "Tu cuenta ha sido creada exitosamente. Ya puedes iniciar sesión.",
                "Tipo" => "success",
                //"URL" => SERVERURL . "index.php?views=login"
            ]);
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto" => "No se pudo crear la cuenta. Intenta nuevamente.",
                "Tipo" => "error"
            ]);
        //  return mainModel::respuesta_error("Error", "No se pudo crear la cuenta. Intenta nuevamente.");
        }

    } else {
        // Sumar intento fallido
        mainModel::registrar_intento_ip($ip, 'cc');
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "No se encontró ningún estudiante con esos datos.",
            "Tipo" => "error"
        ]);       
        // return mainModel::respuesta_error("Datos no encontrados", "No se encontró ningún estudiante con esos datos.");
    }
}





//class recuperarControlador extends mainModel {
        
        
        
        
        public function paginador_solicitud_inscripcion_controlador($pagina, $registros, $vista, $busqueda){
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

    // Filtros
    //$filtro_nombre   = $_GET['filtro_nombre']   ?? "";
    $filtro_estudiante = $_GET['filtro_estudiante'] ?? "";
    $filtro_ci       = $_GET['filtro_ci']       ?? "";
    $filtro_curso    = $_GET['filtro_curso']    ?? "";
    $orden           = $_GET['orden']           ?? "reciente";

    // WHERE dinámico
    $where = "1=1";
    if ($busqueda != "") {
        $where .= " AND (si.nombre_estudiante LIKE '%$busqueda%' OR si.apellido_estudiante LIKE '%$busqueda%' OR si.cedula_estudiante LIKE '%$busqueda%')";
    }
            
            
            
    //if ($filtro_nombre != "") {
  //      $where .= " AND (si.nombre_estudiante LIKE '%$filtro_nombre%' OR si.apellido_estudiante LIKE '%$filtro_nombre%')";
//    }
            

           /// if ($filtro_nombre != "") {
        ///        $where .= " AND si.id_solicitud = '$filtro_nombre'";
     ///       }

            if ($filtro_estudiante != "") {
                $where .= " AND si.id_solicitud = '$filtro_estudiante'";
            }
                       
            
    if ($filtro_ci != "") {
        $where .= " AND si.cedula_estudiante LIKE '%$filtro_ci%'";
    }
    //if ($filtro_curso != "") {
  //      $where .= " AND c.nombre_curso LIKE '%$filtro_curso%'";
//    }

            if ($filtro_curso != "") {
    $where .= " AND c.id_curso = '$filtro_curso'";
}
            
            
    // Ordenamiento
    $order_sql = "ORDER BY si.fecha_solicitud DESC";
    if ($orden == "apellido") {
        $order_sql = "ORDER BY si.apellido_estudiante ASC";
    } elseif ($orden == "curso") {
        $order_sql = "ORDER BY c.nombre_curso ASC";
    }

    // Consulta principal
            
            
            
/*
        SELECT SQL_CALC_FOUND_ROWS
            si.*, c.nombre_curso, c.tipo_curso
        FROM solicitud_inscripcion si
        LEFT JOIN curso c ON si.id_curso_seleccionado = c.id_curso
        
        
--        FROM solicitud_inscripcion si
-- INNER JOIN curso_vigente cv ON si.id_curso_seleccionado = cv.id_curso_vigente
-- INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        
        WHERE $where
        $order_sql
        LIMIT $inicio, $registros

*/
            
            
            
            
            
            
            
            
            
            
            
    $consulta = "
    
    SELECT SQL_CALC_FOUND_ROWS
    si.*, 
    COALESCE(c.nombre_curso, c2.nombre_curso) as nombre_curso,
    COALESCE(c.tipo_curso, c2.tipo_curso) as tipo_curso
FROM solicitud_inscripcion si
LEFT JOIN curso_vigente cv ON si.id_curso_seleccionado = cv.id_curso_vigente
LEFT JOIN curso c ON cv.id_curso_fk = c.id_curso
LEFT JOIN curso c2 ON si.id_curso_seleccionado = c2.id_curso
WHERE $where
$order_sql
LIMIT $inicio, $registros
    
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Construcción de tabla
    $tabla = '<div class="table-responsive">
        <table class="table table-bordered table-sm table-striped">
            <thead class="text-center bg-dark text-white">
                <tr>
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Fecha Nacimiento</th>
                    <th>Curso Seleccionado</th>
                    <th>Fecha Solicitud</th>
                    <th>Estado Solicitud</th>
                    <th>Captura de Pago</th>
                    <th>Ir a Solicitud</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;
        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_solicitud']);
            $cursoInfo = "ID: {$row['id_curso_seleccionado']} / {$row['nombre_curso']} ({$row['tipo_curso']})";

            $tabla .= '<tr class="text-center">
                <td>' . $row['id_solicitud'] . '</td>
                <td>' . htmlspecialchars($row['nombre_estudiante'] . ' ' . $row['apellido_estudiante']) . '</td>
                <td>' . htmlspecialchars($row['cedula_estudiante']) . '</td>
                <td>' . htmlspecialchars($row['telefono_estudiante']) . '</td>
                <td>' . htmlspecialchars($row['correo_Estudiante']) . '</td>
                <td>' . htmlspecialchars($row['fecha_nacimiento']) . '</td>
                <td>' . htmlspecialchars($cursoInfo) . '</td>
                <td>' . htmlspecialchars($row['fecha_solicitud']) . '</td>
                <td>' . htmlspecialchars($row['estado_solicitud']) . '</td>
                <td>
                    <button type="button" class="btn btn-info btn-sm ver-captura" data-src="' . SERVERURL . 'vistas/' . $row['captura_pago'] . '">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
                <td>
	                <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/solicitud-check&solicitud_id=' . $idEnc . '" class="btn btn-sm btn-primary">
                        <i class="fas fa-arrow-right"></i>
                    </a>
                </td>
                <td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/recuperarAjax.php" method="POST" data-form="delete2" data-entity="solicitud_inscripcion" 
 autocomplete="off">
                        <input type="hidden" name="solicitud_id_delete" value="' . $idEnc . '">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>';
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="12">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-primary btn-sm">Recargar listado</a>'
            : 'No hay solicitudes registradas';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando solicitud ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'busqueda' => $busqueda,
            'filtro_estudiante' => $filtro_estudiante,
            'filtro_ci' => $filtro_ci,
            'filtro_curso' => $filtro_curso,
            'orden' => $orden
        ]);

        //check                     <a href="' . SERVERURL . 'index.php?views=solicitud-check&solicitud_id=' . $idEnc . '" class="btn btn-sm btn-primary">
        //recarga listado              ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-primary btn-sm">Recargar listado</a>'
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
     //   $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    // Modal de imagen
    $tabla .= '
    <div id="modalCaptura" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
      <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff;">
        <img id="imgCaptura" src="" class="img-thumbnail" style="max-width:400px; width:80%; height:auto; display:block; margin:auto; border:3px solid white;">
        <button onclick="document.getElementById(\'modalCaptura\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
          <i class="fas fa-times"></i>
        </button>
      </div>
    </div>

    <script>
    document.querySelectorAll(".ver-captura").forEach(btn => {
      btn.addEventListener("click", () => {
        const modal = document.getElementById("modalCaptura");
        modal.querySelector("#imgCaptura").src = btn.dataset.src;
        modal.style.display = "block";
      });
    });
    </script>';
return $tabla;
}

        

    public function registrar_solicitud_inscripcion_controlador() {
        // Sanitizar entradas
        $nombre     = mainModel::limpiar_cadena($_POST['nombre']);
        $apellido   = mainModel::limpiar_cadena($_POST['apellido']);
        $correo     = mainModel::limpiar_cadena($_POST['correo']);
        $id_curso   = intval($_POST['id_curso']);
        $fecha_nac  = $_POST['fecha_nacimiento'];
        $cedula     = mainModel::limpiar_cadena($_POST['cedula']);
        $telefono   = mainModel::limpiar_cadena($_POST['telefono']);
       // $direccion  = mainModel::limpiar_cadena($_POST['direccion']);
        $monto      = floatval($_POST['monto']);
        $fecha_pago = $_POST['fecha_pago'];
        $metodo     = mainModel::limpiar_cadena($_POST['metodo_pago']);
        $clave1     = $_POST['clave'];
        $clave2     = $_POST['confirmar_clave'];

        // Validaciones
        if (preg_match('/[0-9]/', $nombre) || preg_match('/[0-9]/', $apellido)) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Nombre inválido",
                "Texto"  => "El nombre y apellido no deben contener números.",
                "Tipo"   => "error"
            ]);
        }

        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Correo inválido",
                "Texto"  => "El formato del correo electrónico no es válido.",
                "Tipo"   => "error"
            ]);
        }

        if (strtotime($fecha_nac) < strtotime("1900-01-01")) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Fecha inválida",
                "Texto"  => "La fecha de nacimiento no puede ser anterior a 1900.",
                "Tipo"   => "error"
            ]);
        }
/* este bno es el pattern pero no quiero cambiar eso orita
        if (!preg_match('/^[0-9]{6,10}$/', $cedula)) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Cédula inválida",
                "Texto"  => "La cédula debe contener solo números (6 a 10 dígitos).",
                "Tipo"   => "error"
            ]);
        }
*/
        if (!preg_match('/^[0-9]{10,15}$/', $telefono)) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Teléfono inválido",
                "Texto"  => "El teléfono debe contener solo números (10 a 15 dígitos).",
                "Tipo"   => "error"
            ]);
        }

        if (strlen($clave1) < 7 || $clave1 !== $clave2) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Clave inválida",
                "Texto"  => "La clave debe tener al menos 7 caracteres y coincidir con la confirmación.",
                "Tipo"   => "error"
            ]);
        }

        if (!isset($_FILES['captura_pago']) || $_FILES['captura_pago']['error'] !== UPLOAD_ERR_OK) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Imagen faltante",
                "Texto"  => "Debe subir una captura de pago válida.",
                "Tipo"   => "error"
            ]);
        }

        $archivo = $_FILES['captura_pago'];
        //$extension = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
//$carpetaDestino

        $nombreOriginal = basename($archivo['name']); // Ej: imagen.png
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));



        if (!in_array($extension, ['jpg', 'jpeg', 'png', 'gif'])) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Formato inválido",
                "Texto"  => "La imagen debe estar en formato JPG, PNG o GIF.",
                "Tipo"   => "error"
            ]);
        }

                // Validar longitud del nombre original (sin ruta)
        if (strlen($nombreOriginal) > 55) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Nombre de imagen demasiado largo",
                "Texto"  => "El nombre del archivo no debe exceder los 55 caracteres.",
                "Tipo"   => "error"
            ]);
        }




        // Procesar imagen
        $nombreFinal = uniqid("pago_") . "." . $extension;
        $rutaRelativa = "cm/pay/" . $nombreFinal;
        //$rutaAbsoluta = __DIR__ . "/../cm/pay/" . $nombreFinal;

        $carpetaDestino = __DIR__ . "/../vistas/cm/pay/";
        //$carpetaDestino = __DIR__ . "/../cm/pay/";
        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $rutaAbsoluta = $carpetaDestino . $nombreFinal;

        if (!move_uploaded_file($archivo['tmp_name'], $rutaAbsoluta)) {
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Error de imagen",
                "Texto"  => "No se pudo guardar la imagen en el servidor.",
                "Tipo"   => "error"
            ]);
        }

        // Encriptar clave
        $clave = mainModel::encryption($clave1);

        // Enviar al modelo
        $datos = [
            "nombre"     => $nombre,
            "apellido"   => $apellido,
            "correo"     => $correo,
            "id_curso"   => $id_curso,
            "fecha_nac"  => $fecha_nac,
            "cedula"     => $cedula,
            "telefono"   => $telefono,
            "monto"      => $monto,
            "fecha_pago" => $fecha_pago,
            "metodo"     => $metodo,
            "foto"       => $rutaRelativa,
            "clave"      => $clave
        ];

        $guardar = recuperarModelo::guardar_solicitud_inscripcion_modelo($datos);

        if ($guardar->rowCount() >= 1) {
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Solicitud registrada",
                "Texto"  => "La solicitud fue enviada correctamente.",
                "Tipo"   => "success"
            ]);
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error",
                "Texto"  => "No se pudo registrar la solicitud. Intente nuevamente.",
                "Tipo"   => "error"
            ]);
        }
    }

  
        
        public function reprobar_solicitud_controlador() {
    $id = mainModel::decryption($_POST['id_solicitud_reprobar']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia
    $check = mainModel::ejecutar_consulta_simple("SELECT id_solicitud FROM solicitud_inscripcion WHERE id_solicitud = '$id'");
    if ($check->rowCount() <= 0) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Solicitud no encontrada",
            "Texto" => "La solicitud no existe o el ID es inválido",
            "Tipo" => "error"
        ]);
    }

    // Determinar carpeta según cargo
    $cargo = $_SESSION['cargo_spm'] ?? 1;
    $carpetasPorCargo = [
        1 => "admin",
        2 => "Control de estudio",
        3 => "administrador de pagos",
        4 => "Supervisor de cursos",
        5 => "Estudiante"
    ];
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";

    $pdo = mainModel::conectar();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("UPDATE solicitud_inscripcion SET estado_solicitud = 'Rechazada' WHERE id_solicitud = :id");
        $stmt->execute([':id' => $id]);

        $pdo->commit();

        return json_encode([
            "Alerta" => "redireccionar",
            "Titulo" => "Solicitud reprobada",
            "Texto" => "La solicitud ha sido marcada como reprobada exitosamente",
            "Tipo" => "success",
            "URL" => SERVERURL . "index.php?views=" . $carpeta . "/solicitud-list"
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al reprobar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}
        
        
   /* no elimina imagen si esta en cm pay     
        public function eliminar_solicitud_controlador() {
    $id = mainModel::decryption($_POST['solicitud_id_delete']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia
    $check = mainModel::ejecutar_consulta_simple("SELECT id_solicitud FROM solicitud_inscripcion WHERE id_solicitud = '$id'");
    if ($check->rowCount() <= 0) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Solicitud no encontrada",
            "Texto" => "La solicitud que intenta eliminar no existe o el ID es inválido",
            "Tipo" => "error"
        ]);
    }

    $pdo = mainModel::conectar();

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("DELETE FROM solicitud_inscripcion WHERE id_solicitud = :id");
        $stmt->execute([':id' => $id]);

        $pdo->commit();

        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Solicitud eliminada",
            "Texto" => "La solicitud ha sido eliminada exitosamente",
            "Tipo" => "success"
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al eliminar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}
 */       
        
        
        public function eliminar_solicitud_controlador() {
    $id = mainModel::decryption($_POST['solicitud_id_delete']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia
    $check = mainModel::ejecutar_consulta_simple("SELECT captura_pago FROM solicitud_inscripcion WHERE id_solicitud = '$id'");
    if ($check->rowCount() <= 0) {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Solicitud no encontrada",
            "Texto" => "La solicitud que intenta eliminar no existe o el ID es inválido",
            "Tipo" => "error"
        ]);
    }

    $datos = $check->fetch(PDO::FETCH_ASSOC);
    $rutaRelativa = $datos['captura_pago'] ?? '';
    $nombreArchivo = basename($rutaRelativa); // Extrae solo el nombre del archivo

    // Ruta física del archivo
    $rutaFisica = "../vistas/cm/pay/" . $nombreArchivo;

    $pdo = mainModel::conectar();

    try {
        $pdo->beginTransaction();

        // Eliminar archivo si existe
        if (file_exists($rutaFisica)) {
            unlink($rutaFisica);
        }

        // Eliminar registro
        $stmt = $pdo->prepare("DELETE FROM solicitud_inscripcion WHERE id_solicitud = :id");
        $stmt->execute([':id' => $id]);

        $pdo->commit();

        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Solicitud eliminada",
            "Texto" => "La solicitud y su comprobante han sido eliminados exitosamente",
            "Tipo" => "success"
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al eliminar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}
        
        
        /* esta permite varias secciones pero aja ta el tema del pago q puede varia y eso da error
public function aceptar_solicitud_controlador() {
    // Verificar permisos
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 3) {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Acceso Denegado",
            "Texto" => "No tienes permisos para realizar esta acción",
            "Tipo" => "error"
        ];
        return json_encode($alerta);
    }

    $pdo = mainModel::conectar();

    try {
        // Obtener y limpiar datos del formulario
        $nombre_estudiante = mainModel::limpiar_cadena($_POST['nombre_estudiante']);
        $apellido_estudiante = mainModel::limpiar_cadena($_POST['apellido_estudiante']);
        $cedula_estudiante = mainModel::limpiar_cadena($_POST['cedula_estudiante']);
        $telefono_estudiante = mainModel::limpiar_cadena($_POST['telefono_estudiante']);
        $correo_estudiante = mainModel::limpiar_cadena($_POST['correo_Estudiante']);
        $fecha_nacimiento = mainModel::limpiar_cadena($_POST['fecha_nacimiento']);
        $monto = mainModel::limpiar_cadena($_POST['monto']);
        $fecha_pago = mainModel::limpiar_cadena($_POST['fecha_pago']);
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago']);
        $captura_pago = mainModel::limpiar_cadena($_POST['captura_pago']);
        $clave_estudiante = mainModel::limpiar_cadena($_POST['clave_estudiante']);
        $solicitud_id = mainModel::decryption($_POST['solicitud_id_check']);

        // ⚠️ CAMBIO IMPORTANTE: Ya NO usamos id_curso_vigente_fk del formulario
        $seccion_id = mainModel::limpiar_cadena($_POST['seccion_id'] ?? '');

        // Validar campos obligatorios
        if ($nombre_estudiante == "" || $apellido_estudiante == "" || $cedula_estudiante == "" || 
            $telefono_estudiante == "" || $correo_estudiante == "" || $fecha_nacimiento == "" || 
            $monto == "" || $fecha_pago == "" || $metodo_pago == "" || $captura_pago == "" || 
            $clave_estudiante == "" || $solicitud_id == "" || empty($seccion_id)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto"  => "No has llenado todos los campos que son obligatorios",
                "Tipo"   => "error"
            ];
            return json_encode($alerta);
        }

        // MANEJO DE LA IMAGEN (igual que antes)
        $nombreImagen = basename($captura_pago);
        $rutaOrigen = __DIR__ . "/../vistas/cm/pay/" . $nombreImagen;
        $rutaDestino = __DIR__ . "/../comprobantes/" . $nombreImagen;
        
        if (!file_exists($rutaOrigen)) {
            throw new Exception("La imagen de pago no se encontró en el servidor");
        }
        
        if (!copy($rutaOrigen, $rutaDestino)) {
            throw new Exception("No se pudo copiar la imagen de pago a la carpeta de comprobantes");
        }
        
        if (!unlink($rutaOrigen)) {
            throw new Exception("No se pudo eliminar la imagen temporal de pago");
        }

        // Ahora sí, iniciar la transacción
        $pdo->beginTransaction();

        // ✅ NUEVO: OBTENER EL CURSO VIGENTE DE LA SECCIÓN SELECCIONADA
        $consultaSeccionCV = $pdo->prepare("
            SELECT 
                s.id_curso_vigente_fk,
                s.id_seccion,
                COUNT(i.id_inscripcion) as inscritos_actuales,
                cv.estado_curso_vigente,
                -- Datos de cohorte (si existe)
                ch.pago_inscripcion,
                ch.estado_cohorte,
                -- Datos de periodo intensivo (si existe)
                pi.pago_inscripcion_int,
                pi.estado_periodo,
                -- Determinar tipo
                CASE 
                    WHEN cv.id_cohorte_fk IS NOT NULL THEN 'cohorte'
                    ELSE 'intensivo'
                END AS tipo_curso
            FROM seccion s
            INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
            LEFT JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            LEFT JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk AND i.archivada = 1
            WHERE s.id_seccion = :seccion_id 
            AND s.archivar_seccion = 1
            AND cv.estado_curso_vigente = 1
            GROUP BY s.id_seccion
        ");
        $consultaSeccionCV->execute([':seccion_id' => $seccion_id]);
        
        if ($consultaSeccionCV->rowCount() !== 1) {
            throw new Exception("La sección seleccionada no está disponible");
        }
        
        $datosSeccion = $consultaSeccionCV->fetch();
        
        // ✅ Ahora usamos el CV de la sección seleccionada
        $id_curso_vigente_fk = $datosSeccion['id_curso_vigente_fk'];
        $id_seccion_final = $datosSeccion['id_seccion'];
        
        // Validar cupos de la sección
        if ($datosSeccion['inscritos_actuales'] >= 20) {
            throw new Exception("La sección seleccionada ya tiene 20 estudiantes inscritos");
        }

        // ✅ Determinar pago según el tipo de curso (del CV de la sección)
        if ($datosSeccion['tipo_curso'] === 'cohorte') {
            $pago_inscripcion = $datosSeccion['pago_inscripcion'];
            // Validar estado del cohorte
            if (!in_array($datosSeccion['estado_cohorte'], ['Vigente', 'Próximo'])) {
                throw new Exception("El cohorte no está Vigente o Próximo (estado: {$datosSeccion['estado_cohorte']})");
            }
        } else {
            $pago_inscripcion = $datosSeccion['pago_inscripcion_int'];
            // Validar estado del periodo intensivo
            if (!in_array($datosSeccion['estado_periodo'], ['Vigente', 'Próximo'])) {
                throw new Exception("El periodo intensivo no está Vigente o Próximo (estado: {$datosSeccion['estado_periodo']})");
            }
        }
        
        // Validar que el pago_inscripcion no sea nulo
        if ($pago_inscripcion === null) {
            throw new Exception("No se pudo determinar el monto de inscripción para este curso");
        }
        
        // Validar monto
        if ($monto > $pago_inscripcion) {
            throw new Exception("El monto ingresado ($monto) excede el precio de inscripción permitido ($pago_inscripcion)");
        }

        // Calcular pago faltante
        $pago_faltante = max(0, $pago_inscripcion - $monto);

        // PASO 1: Verificar si el estudiante ya existe (igual que antes)
        $consultaEstudiante = $pdo->prepare("
            SELECT id_estudiante FROM estudiante 
            WHERE ci_estudiante = :cedula 
            AND estado_estudiante = 1
        ");
        $consultaEstudiante->execute([':cedula' => $cedula_estudiante]);
        
        if ($consultaEstudiante->rowCount() > 0) {
            // Estudiante existe, obtener su ID
            $id_estudiante = $consultaEstudiante->fetch()['id_estudiante'];
            
            // Actualizar datos del estudiante existente si es necesario
            $updateEstudiante = $pdo->prepare("
                UPDATE estudiante SET 
                nombre = :nombre,
                apellido = :apellido,
                telefono_estudiante = :telefono,
                correo_estudiante = :correo,
                fecha_nacimiento = :fecha_nac
                WHERE id_estudiante = :id
            ");
            $updateEstudiante->execute([
                ':nombre' => $nombre_estudiante,
                ':apellido' => $apellido_estudiante,
                ':telefono' => $telefono_estudiante,
                ':correo' => $correo_estudiante,
                ':fecha_nac' => $fecha_nacimiento,
                ':id' => $id_estudiante
            ]);
        } else {
            // Crear nuevo estudiante
            $insertEstudiante = $pdo->prepare("
                INSERT INTO estudiante (
                    ci_estudiante, nombre, apellido, fecha_nacimiento, 
                    telefono_estudiante, correo_estudiante, estado_estudiante
                ) VALUES (
                    :cedula, :nombre, :apellido, :fecha_nac, 
                    :telefono, :correo, 1
                )
            ");
            $insertEstudiante->execute([
                ':cedula' => $cedula_estudiante,
                ':nombre' => $nombre_estudiante,
                ':apellido' => $apellido_estudiante,
                ':fecha_nac' => $fecha_nacimiento,
                ':telefono' => $telefono_estudiante,
                ':correo' => $correo_estudiante
            ]);
            $id_estudiante = $pdo->lastInsertId();
        }

        // CREAR USUARIO PARA EL ESTUDIANTE (igual que antes)
        $fechaCreacion = date("Y-m-d H:i:s");
        $claveEncriptada = password_hash($clave_estudiante, PASSWORD_BCRYPT);
        
        $insertUsuario = $pdo->prepare("
            INSERT INTO usuario (cargo, estado_usuario, fecha_creacion, Nombre, Password)
            VALUES (5, 1, :fecha, :nombre, :clave)
        ");
        $insertUsuario->execute([
            ':fecha' => $fechaCreacion,
            ':nombre' => $cedula_estudiante,
            ':clave' => $claveEncriptada
        ]);
        $id_usuario = $pdo->lastInsertId();

        $insertUsuarioEstudiante = $pdo->prepare("
            INSERT INTO usuario_estudiante (id_usuario_fk, id_estudiante_fk) 
            VALUES (:usuario, :estudiante)
        ");
        $insertUsuarioEstudiante->execute([
            ':usuario' => $id_usuario,
            ':estudiante' => $id_estudiante
        ]);

        // ✅ PASO 2: Crear la inscripción (usando el CV de la sección)
        $fecha_inscripcion_actual = date('Y-m-d');
        
        $insertInscripcion = $pdo->prepare("
            INSERT INTO inscripcion (
                id_curso_vigente_fk, id_estudiante_fk, id_seccion_fk,
                fecha_inscripcion, estado_inscripcion, archivada, pago_faltante
            ) VALUES (
                :curso_vigente, :estudiante, :seccion,
                :fecha_insc, 'Activa', 1, :pago_faltante
            )
        ");
        $insertInscripcion->execute([
            ':curso_vigente' => $id_curso_vigente_fk, // ✅ CV de la sección
            ':estudiante' => $id_estudiante,
            ':seccion' => $id_seccion_final,
            ':fecha_insc' => $fecha_inscripcion_actual,
            ':pago_faltante' => $pago_faltante
        ]);
        
        $id_inscripcion = $pdo->lastInsertId();

        // PASO 3: Crear transacción de pago (igual que antes)
        $insertTransaccion = $pdo->prepare("
            INSERT INTO transaccion (
                id_inscripcion, monto, estatus_pago, motivo_pago,
                fecha_pago, metodo_pago, if_pagomovil_imagen, pago_faltante
            ) VALUES (
                :inscripcion, :monto, 1, 'Inscripción',
                :fecha_pago, :metodo, :imagen, :pago_faltante
            )
        ");
        $insertTransaccion->execute([
            ':inscripcion' => $id_inscripcion,
            ':monto' => $monto,
            ':fecha_pago' => $fecha_pago,
            ':metodo' => $metodo_pago,
            ':imagen' => $nombreImagen,
            ':pago_faltante' => $pago_faltante
        ]);

        // PASO 4: Actualizar estado de la solicitud (igual que antes)
        $updateSolicitud = $pdo->prepare("
            UPDATE solicitud_inscripcion 
            SET estado_solicitud = 'Aceptada' 
            WHERE id_solicitud = :id_solicitud
        ");
        $updateSolicitud->execute([':id_solicitud' => $solicitud_id]);

        $pdo->commit();

        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Solicitud Aceptada",
            "Texto" => "La solicitud ha sido aceptada exitosamente. El estudiante ha sido inscrito en el curso.",
            "Tipo" => "success"
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "Error al aceptar la solicitud: " . $e->getMessage(),
            "Tipo" => "error"
        ];
    }

    return json_encode($alerta);
} 
        
        */
        
        
        
        
        
        
        
        
        
        // esta es la q solo permite secciones del cv enviado 71125
public function aceptar_solicitud_controlador() {
    // Verificar permisos
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 3) {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Acceso Denegado",
            "Texto" => "No tienes permisos para realizar esta acción",
            "Tipo" => "error"
        ];
        return json_encode($alerta);
    }

    $pdo = mainModel::conectar();

    try {
        // Obtener y limpiar datos del formulario
        $nombre_estudiante = mainModel::limpiar_cadena($_POST['nombre_estudiante']);
        $apellido_estudiante = mainModel::limpiar_cadena($_POST['apellido_estudiante']);
        $cedula_estudiante = mainModel::limpiar_cadena($_POST['cedula_estudiante']);
        $telefono_estudiante = mainModel::limpiar_cadena($_POST['telefono_estudiante']);
        $correo_estudiante = mainModel::limpiar_cadena($_POST['correo_Estudiante']);
        $fecha_nacimiento = mainModel::limpiar_cadena($_POST['fecha_nacimiento']);
        $monto = mainModel::limpiar_cadena($_POST['monto']);
        $fecha_pago = mainModel::limpiar_cadena($_POST['fecha_pago']);
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago']);
        $captura_pago = mainModel::limpiar_cadena($_POST['captura_pago']);
        $clave_estudiante = mainModel::limpiar_cadena($_POST['clave_estudiante']);
        $solicitud_id = mainModel::decryption($_POST['solicitud_id_check']);

        $id_curso_vigente_fk = mainModel::limpiar_cadena($_POST['id_curso_vigente_fk']);
        $seccion_id = mainModel::limpiar_cadena($_POST['seccion_id'] ?? '');

        // Validar campos obligatorios
        if ($nombre_estudiante == "" || $apellido_estudiante == "" || $cedula_estudiante == "" || 
            $telefono_estudiante == "" || $correo_estudiante == "" || $fecha_nacimiento == "" || 
            $monto == "" || $fecha_pago == "" || $metodo_pago == "" || $captura_pago == "" || 
            $clave_estudiante == "" || $id_curso_vigente_fk == "" || $solicitud_id == "" || 
            empty($seccion_id)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto"  => "No has llenado todos los campos que son obligatorios",
                "Tipo"   => "error"
            ];
            return json_encode($alerta);
        }

        // MANEJO DE LA IMAGEN (COMPLETADO)
        $nombreImagen = basename($captura_pago);
        $rutaOrigen = __DIR__ . "/../vistas/cm/pay/" . $nombreImagen;
        $rutaDestino = __DIR__ . "/../comprobantes/" . $nombreImagen;
        
        if (!file_exists($rutaOrigen)) {
            throw new Exception("La imagen de pago no se encontró en el servidor");
        }
        
        if (!copy($rutaOrigen, $rutaDestino)) {
            throw new Exception("No se pudo copiar la imagen de pago a la carpeta de comprobantes");
        }
        
        if (!unlink($rutaOrigen)) {
            throw new Exception("No se pudo eliminar la imagen temporal de pago");
        }

        // Ahora sí, iniciar la transacción
        $pdo->beginTransaction();

        // PASO 1: Verificar si el estudiante ya existe
        $consultaEstudiante = $pdo->prepare("
            SELECT id_estudiante FROM estudiante 
            WHERE ci_estudiante = :cedula 
            AND estado_estudiante = 1
        ");
        $consultaEstudiante->execute([':cedula' => $cedula_estudiante]);
        
        if ($consultaEstudiante->rowCount() > 0) {
            // Estudiante existe, obtener su ID
            $id_estudiante = $consultaEstudiante->fetch()['id_estudiante'];
            
            // Actualizar datos del estudiante existente si es necesario
            $updateEstudiante = $pdo->prepare("
                UPDATE estudiante SET 
                nombre = :nombre,
                apellido = :apellido,
                telefono_estudiante = :telefono,
                correo_estudiante = :correo,
                fecha_nacimiento = :fecha_nac
                WHERE id_estudiante = :id
            ");
            $updateEstudiante->execute([
                ':nombre' => $nombre_estudiante,
                ':apellido' => $apellido_estudiante,
                ':telefono' => $telefono_estudiante,
                ':correo' => $correo_estudiante,
                ':fecha_nac' => $fecha_nacimiento,
                ':id' => $id_estudiante
            ]);
        } else {
            // PASO 2: Crear nuevo estudiante
            $insertEstudiante = $pdo->prepare("
                INSERT INTO estudiante (
                    ci_estudiante, nombre, apellido, fecha_nacimiento, 
                    telefono_estudiante, correo_estudiante, estado_estudiante
                ) VALUES (
                    :cedula, :nombre, :apellido, :fecha_nac, 
                    :telefono, :correo, 1
                )
            ");
            $insertEstudiante->execute([
                ':cedula' => $cedula_estudiante,
                ':nombre' => $nombre_estudiante,
                ':apellido' => $apellido_estudiante,
                ':fecha_nac' => $fecha_nacimiento,
                ':telefono' => $telefono_estudiante,
                ':correo' => $correo_estudiante
            ]);
            $id_estudiante = $pdo->lastInsertId();
        }

        // CREAR USUARIO PARA EL ESTUDIANTE
        $fechaCreacion = date("Y-m-d H:i:s");
        $claveEncriptada = password_hash($clave_estudiante, PASSWORD_BCRYPT);
        
        $insertUsuario = $pdo->prepare("
            INSERT INTO usuario (cargo, estado_usuario, fecha_creacion, Nombre, Password)
            VALUES (5, 1, :fecha, :nombre, :clave)
        ");
        $insertUsuario->execute([
            ':fecha' => $fechaCreacion,
            ':nombre' => $cedula_estudiante,
            ':clave' => $claveEncriptada
        ]);
        $id_usuario = $pdo->lastInsertId();

        $insertUsuarioEstudiante = $pdo->prepare("
            INSERT INTO usuario_estudiante (id_usuario_fk, id_estudiante_fk) 
            VALUES (:usuario, :estudiante)
        ");
        $insertUsuarioEstudiante->execute([
            ':usuario' => $id_usuario,
            ':estudiante' => $id_estudiante
        ]);

        // PASO 3: Verificar sección existente
        $id_seccion_final = $seccion_id;
        
        $consultaSeccion = $pdo->prepare("
            SELECT 
                s.id_seccion,
                COUNT(i.id_inscripcion) as inscritos_actuales
            FROM seccion s
            LEFT JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk AND i.archivada = 1
            WHERE s.id_seccion = :seccion_id 
            AND s.archivar_seccion = 1
            GROUP BY s.id_seccion
        ");
        $consultaSeccion->execute([':seccion_id' => $seccion_id]);
        
        if ($consultaSeccion->rowCount() !== 1) {
            throw new Exception("La sección seleccionada no está disponible");
        }
        
        $datosSeccion = $consultaSeccion->fetch();
        if ($datosSeccion['inscritos_actuales'] >= 20) {
            throw new Exception("La sección seleccionada ya tiene 20 estudiantes inscritos");
        }

        // PASO 4: Obtener datos del curso vigente para el pago
        $consultaCursoVigente = $pdo->prepare("
            SELECT 
                cv.id_curso_vigente, 
                cv.id_cohorte_fk,
                -- Datos de cohorte (si existe)
                ch.pago_inscripcion,
                ch.estado_cohorte,
                -- Datos de periodo intensivo (si existe)
                pi.pago_inscripcion_int,
                pi.estado_periodo,
                -- Determinar tipo
                CASE 
                    WHEN cv.id_cohorte_fk IS NOT NULL THEN 'cohorte'
                    ELSE 'intensivo'
                END AS tipo_curso
            FROM curso_vigente cv
            LEFT JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
            LEFT JOIN curso c ON cv.id_curso_fk = c.id_curso
            LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
            WHERE cv.id_curso_vigente = :id_curso_vigente
            AND cv.estado_curso_vigente = 1
        ");
        $consultaCursoVigente->execute([':id_curso_vigente' => $id_curso_vigente_fk]);
        
        if ($consultaCursoVigente->rowCount() !== 1) {
            throw new Exception("El curso vigente no está disponible");
        }
        
        $datosCurso = $consultaCursoVigente->fetch();
        
        // Determinar pago según el tipo de curso
        if ($datosCurso['tipo_curso'] === 'cohorte') {
            $pago_inscripcion = $datosCurso['pago_inscripcion'];
            // Validar estado del cohorte
            if (!in_array($datosCurso['estado_cohorte'], ['Vigente', 'Próximo'])) {
                throw new Exception("El cohorte no está Vigente o Próximo (estado: {$datosCurso['estado_cohorte']})");
            }
        } else {
            $pago_inscripcion = $datosCurso['pago_inscripcion_int'];
            // Validar estado del periodo intensivo
            if (!in_array($datosCurso['estado_periodo'], ['Vigente', 'Próximo'])) {
                throw new Exception("El periodo intensivo no está Vigente o Próximo (estado: {$datosCurso['estado_periodo']})");
            }
        }
        
        // Validar que el pago_inscripcion no sea nulo
        if ($pago_inscripcion === null) {
            throw new Exception("No se pudo determinar el monto de inscripción para este curso");
        }
        
        // Validar monto
        if ($monto > $pago_inscripcion) {
            throw new Exception("El monto ingresado ($monto) excede el precio de inscripción permitido ($pago_inscripcion)");
        }

        // Calcular pago faltante
        $pago_faltante = max(0, $pago_inscripcion - $monto);

        // PASO 5: Crear la inscripción
        $fecha_inscripcion_actual = date('Y-m-d');
        
        $insertInscripcion = $pdo->prepare("
            INSERT INTO inscripcion (
                id_curso_vigente_fk, id_estudiante_fk, id_seccion_fk,
                fecha_inscripcion, estado_inscripcion, archivada, pago_faltante
            ) VALUES (
                :curso_vigente, :estudiante, :seccion,
                :fecha_insc, 'Activa', 1, :pago_faltante
            )
        ");
        $insertInscripcion->execute([
            ':curso_vigente' => $id_curso_vigente_fk,
            ':estudiante' => $id_estudiante,
            ':seccion' => $id_seccion_final,
            ':fecha_insc' => $fecha_inscripcion_actual,
            ':pago_faltante' => $pago_faltante
        ]);
        
        $id_inscripcion = $pdo->lastInsertId();

        // PASO 6: Crear transacción de pago
        $insertTransaccion = $pdo->prepare("
            INSERT INTO transaccion (
                id_inscripcion, monto, estatus_pago, motivo_pago,
                fecha_pago, metodo_pago, if_pagomovil_imagen, pago_faltante
            ) VALUES (
                :inscripcion, :monto, 1, 'Inscripción',
                :fecha_pago, :metodo, :imagen, :pago_faltante
            )
        ");
        $insertTransaccion->execute([
            ':inscripcion' => $id_inscripcion,
            ':monto' => $monto,
            ':fecha_pago' => $fecha_pago,
            ':metodo' => $metodo_pago,
            ':imagen' => $nombreImagen,
            ':pago_faltante' => $pago_faltante
        ]);

        // PASO 7: Actualizar estado de la solicitud
        $updateSolicitud = $pdo->prepare("
            UPDATE solicitud_inscripcion 
            SET estado_solicitud = 'Aceptada' 
            WHERE id_solicitud = :id_solicitud
        ");
        $updateSolicitud->execute([':id_solicitud' => $solicitud_id]);

        $pdo->commit();

        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Solicitud Aceptada",
            "Texto" => "La solicitud ha sido aceptada exitosamente. El estudiante ha sido inscrito en el curso.",
            "Tipo" => "success"
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "Error al aceptar la solicitud: " . $e->getMessage(),
            "Tipo" => "error"
        ];
    }

    return json_encode($alerta);
}
//*/        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        /* no apto para cv int
public function aceptar_solicitud_controlador() {
    // Verificar permisos
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 3) {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Acceso Denegado",
            "Texto" => "No tienes permisos para realizar esta acción",
            "Tipo" => "error"
        ];
        return json_encode($alerta);
    }

    $pdo = mainModel::conectar();

    try {
        // Obtener y limpiar datos del formulario
        $nombre_estudiante = mainModel::limpiar_cadena($_POST['nombre_estudiante']);
        $apellido_estudiante = mainModel::limpiar_cadena($_POST['apellido_estudiante']);
        $cedula_estudiante = mainModel::limpiar_cadena($_POST['cedula_estudiante']);
        $telefono_estudiante = mainModel::limpiar_cadena($_POST['telefono_estudiante']);
        $correo_estudiante = mainModel::limpiar_cadena($_POST['correo_Estudiante']);
        $fecha_nacimiento = mainModel::limpiar_cadena($_POST['fecha_nacimiento']);
        $monto = mainModel::limpiar_cadena($_POST['monto']);
        $fecha_pago = mainModel::limpiar_cadena($_POST['fecha_pago']);
        $metodo_pago = mainModel::limpiar_cadena($_POST['metodo_pago']);
        $captura_pago = mainModel::limpiar_cadena($_POST['captura_pago']);
        $clave_estudiante = mainModel::limpiar_cadena($_POST['clave_estudiante']);
        $solicitud_id = mainModel::decryption($_POST['solicitud_id_check']);
      //$id = mainModel::decryption($_POST['solicitud_id_delete']);



        $id_curso_vigente_fk = mainModel::limpiar_cadena($_POST['id_curso_vigente_fk']);
        $seccion_id = mainModel::limpiar_cadena($_POST['seccion_id'] ?? '');
        $id_profesor_fk_reg = mainModel::limpiar_cadena($_POST['id_profesor_fk_reg'] ?? '');

        // Validar campos obligatorios
        if ($nombre_estudiante == "" || $apellido_estudiante == "" || $cedula_estudiante == "" || 
            $telefono_estudiante == "" || $correo_estudiante == "" || $fecha_nacimiento == "" || 
            $monto == "" || $fecha_pago == "" || $metodo_pago == "" || $captura_pago == "" || 
            $clave_estudiante == "" || $id_curso_vigente_fk == "" || $solicitud_id == "") {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto"  => "No has llenado todos los campos que son obligatorios",
                "Tipo"   => "error"
            ];
            return json_encode($alerta);
        }

        if (empty($seccion_id) && empty($id_profesor_fk_reg)) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error inesperado",
                "Texto"  => "Debes elegir una sección donde inscribir al alumno o un profesor para crear una nueva",
                "Tipo"   => "error"
            ];
            return json_encode($alerta);
        }

        $nombreImagen = basename($captura_pago);
        $rutaOrigen = __DIR__ . "/../vistas/cm/pay/" . $nombreImagen;
        $rutaDestino = __DIR__ . "/../comprobantes/" . $nombreImagen;
        
        if (!file_exists($rutaOrigen)) {
            throw new Exception("La imagen de pago no se encontró en el servidor");
        }
        
        if (!copy($rutaOrigen, $rutaDestino)) {
            throw new Exception("No se pudo copiar la imagen de pago a la carpeta de comprobantes");
        }
        
        if (!unlink($rutaOrigen)) {
            throw new Exception("No se pudo eliminar la imagen temporal de pago");
        }

        // Ahora sí, iniciar la transacción
        $pdo->beginTransaction();

        // PASO 1: Verificar si el estudiante ya existe
        $consultaEstudiante = $pdo->prepare("
            SELECT id_estudiante FROM estudiante 
            WHERE ci_estudiante = :cedula 
            AND estado_estudiante = 1
        ");
        $consultaEstudiante->execute([':cedula' => $cedula_estudiante]);
        
        if ($consultaEstudiante->rowCount() > 0) {
            // Estudiante existe, obtener su ID
            $id_estudiante = $consultaEstudiante->fetch()['id_estudiante'];
            
            // Actualizar datos del estudiante existente si es necesario
            $updateEstudiante = $pdo->prepare("
                UPDATE estudiante SET 
                nombre = :nombre,
                apellido = :apellido,
                telefono_estudiante = :telefono,
                correo_estudiante = :correo,
                fecha_nacimiento = :fecha_nac
                WHERE id_estudiante = :id
            ");
            $updateEstudiante->execute([
                ':nombre' => $nombre_estudiante,
                ':apellido' => $apellido_estudiante,
                ':telefono' => $telefono_estudiante,
                ':correo' => $correo_estudiante,
                ':fecha_nac' => $fecha_nacimiento,
                ':id' => $id_estudiante
            ]);
        } else {
            // PASO 2: Crear nuevo estudiante
            $insertEstudiante = $pdo->prepare("
                INSERT INTO estudiante (
                    ci_estudiante, nombre, apellido, fecha_nacimiento, 
                    telefono_estudiante, correo_estudiante, estado_estudiante
                ) VALUES (
                    :cedula, :nombre, :apellido, :fecha_nac, 
                    :telefono, :correo, 1
                )
            ");
            $insertEstudiante->execute([
                ':cedula' => $cedula_estudiante,
                ':nombre' => $nombre_estudiante,
                ':apellido' => $apellido_estudiante,
                ':fecha_nac' => $fecha_nacimiento,
                ':telefono' => $telefono_estudiante,
                ':correo' => $correo_estudiante
            ]);
            $id_estudiante = $pdo->lastInsertId();
        }

        $fechaCreacion = date("Y-m-d H:i:s");
        $claveEncriptada = password_hash($clave_estudiante, PASSWORD_BCRYPT);
        
        $insertUsuario = $pdo->prepare("
            INSERT INTO usuario (cargo, estado_usuario, fecha_creacion, Nombre, Password)
            VALUES (5, 1, :fecha, :nombre, :clave)
        ");
        $insertUsuario->execute([
            ':fecha' => $fechaCreacion,
            ':nombre' => $cedula_estudiante,
            ':clave' => $claveEncriptada
        ]);
        $id_usuario = $pdo->lastInsertId();

        $insertUsuarioEstudiante = $pdo->prepare("
            INSERT INTO usuario_estudiante (id_usuario_fk, id_estudiante_fk) 
            VALUES (:usuario, :estudiante)
        ");
        $insertUsuarioEstudiante->execute([
            ':usuario' => $id_usuario,
            ':estudiante' => $id_estudiante
        ]);

        // PASO 3: Manejar la sección (existente o nueva)
        if (!empty($seccion_id)) {
            // Usar sección existente
            $id_seccion_final = $seccion_id;
            
            // Verificar que la sección esté activa y tenga cupo
            $consultaSeccion = $pdo->prepare("
                SELECT 
                    s.id_seccion,
                    COUNT(i.id_inscripcion) as inscritos_actuales
                FROM seccion s
                LEFT JOIN inscripcion i ON s.id_seccion = i.id_seccion_fk AND i.archivada = 1
                WHERE s.id_seccion = :seccion_id 
                AND s.archivar_seccion = 1
                GROUP BY s.id_seccion
            ");
            $consultaSeccion->execute([':seccion_id' => $seccion_id]);
            
            if ($consultaSeccion->rowCount() !== 1) {
                throw new Exception("La sección seleccionada no está disponible");
            }
            
            $datosSeccion = $consultaSeccion->fetch();
            if ($datosSeccion['inscritos_actuales'] >= 20) {
                throw new Exception("La sección seleccionada ya tiene 20 estudiantes inscritos");
            }
        } else {
            // Crear nueva sección
            // Obtener el último nombre de sección para este curso
            $consultaUltimaSeccion = $pdo->prepare("
                SELECT nombre_seccion FROM seccion 
                WHERE id_curso_vigente_fk = :id_curso_vigente 
                AND archivar_seccion = 1
                ORDER BY nombre_seccion DESC 
                LIMIT 1
            ");
            $consultaUltimaSeccion->execute([':id_curso_vigente' => $id_curso_vigente_fk]);
            $ultimaSeccion = $consultaUltimaSeccion->fetchColumn();
            
            // Generar nuevo nombre de sección (A, B, C, ...)
            $nuevoNombreSeccion = 'A';
            if ($ultimaSeccion) {
                $nuevoNombreSeccion = chr(ord($ultimaSeccion) + 1);
            }

            // Obtener CI del profesor
            $consultaProfesor = $pdo->prepare("
                SELECT ci_profesor FROM profesor 
                WHERE id_profesor = :id_profesor 
                AND estado_profesor = 1
            ");
            $consultaProfesor->execute([':id_profesor' => $id_profesor_fk_reg]);
            
            if ($consultaProfesor->rowCount() !== 1) {
                throw new Exception("El profesor seleccionado no está disponible");
            }
            
            $ci_profesor = $consultaProfesor->fetch()['ci_profesor'];

            // Crear nueva sección
            $insertSeccion = $pdo->prepare("
                INSERT INTO seccion (
                    id_curso_vigente_fk, ci_profesor, nombre_seccion, archivar_seccion
                ) VALUES (
                    :curso_vigente, :profesor, :nombre, 1
                )
            ");
            $insertSeccion->execute([
                ':curso_vigente' => $id_curso_vigente_fk,
                ':profesor' => $ci_profesor,
                ':nombre' => $nuevoNombreSeccion
            ]);
            $id_seccion_final = $pdo->lastInsertId();
        }

        // PASO 4: Obtener datos del curso vigente para el pago
        $consultaCursoVigente = $pdo->prepare("
            SELECT cv.id_curso_vigente, cv.id_cohorte_fk, c.pago_inscripcion 
            FROM curso_vigente cv
            JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
            WHERE cv.id_curso_vigente = :id_curso_vigente
            AND cv.estado_curso_vigente = 1
            AND c.archivar_cohorte = 1
        ");
        $consultaCursoVigente->execute([':id_curso_vigente' => $id_curso_vigente_fk]);
        
        if ($consultaCursoVigente->rowCount() !== 1) {
            throw new Exception("El curso vigente no está disponible");
        }
        
        $datosCurso = $consultaCursoVigente->fetch();
        $pago_inscripcion = $datosCurso['pago_inscripcion'];
        
        // Validar monto
        if ($monto > $pago_inscripcion) {
            throw new Exception("El monto ingresado ($monto) excede el precio de inscripción permitido ($pago_inscripcion)");
        }

        // Calcular pago faltante
        $pago_faltante = max(0, $pago_inscripcion - $monto);

        // PASO 5: Crear la inscripción
        $fecha_inscripcion_actual = date('Y-m-d');
        
        $insertInscripcion = $pdo->prepare("
            INSERT INTO inscripcion (
                id_curso_vigente_fk, id_estudiante_fk, id_seccion_fk,
                fecha_inscripcion, estado_inscripcion, archivada, pago_faltante
            ) VALUES (
                :curso_vigente, :estudiante, :seccion,
                :fecha_insc, 'Activa', 1, :pago_faltante
            )
        ");
        $insertInscripcion->execute([
            ':curso_vigente' => $id_curso_vigente_fk,
            ':estudiante' => $id_estudiante,
            ':seccion' => $id_seccion_final,
            ':fecha_insc' => $fecha_inscripcion_actual,
            ':pago_faltante' => $pago_faltante
        ]);
        
        $id_inscripcion = $pdo->lastInsertId();

        $insertTransaccion = $pdo->prepare("
            INSERT INTO transaccion (
                id_inscripcion, monto, estatus_pago, motivo_pago,
                fecha_pago, metodo_pago, if_pagomovil_imagen, pago_faltante
            ) VALUES (
                :inscripcion, :monto, 1, 'Inscripción',
                :fecha_pago, :metodo, :imagen, :pago_faltante
            )
        ");
        $insertTransaccion->execute([
            ':inscripcion' => $id_inscripcion,
            ':monto' => $monto,
            ':fecha_pago' => $fecha_pago,
            ':metodo' => $metodo_pago,
            ':imagen' => $nombreImagen,
            ':pago_faltante' => $pago_faltante
        ]);

        // PASO 7: Actualizar estado de la solicitud
        $updateSolicitud = $pdo->prepare("
            UPDATE solicitud_inscripcion 
            SET estado_solicitud = 'Aceptada' 
            WHERE id_solicitud = :id_solicitud
        ");
        $updateSolicitud->execute([':id_solicitud' => $solicitud_id]);

        $pdo->commit();

        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Solicitud Aceptada",
            "Texto" => "La solicitud ha sido aceptada exitosamente. El estudiante ha sido inscrito en el curso.",
            "Tipo" => "success"
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "Error al aceptar la solicitud: " . $e->getMessage(),
            "Tipo" => "error"
        ];
    }

    return json_encode($alerta);
}*/


    }
