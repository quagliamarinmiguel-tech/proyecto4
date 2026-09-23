<?php

    if($peticionAjax){
        require_once "../modelos/usuarioModelo3.php";
    }else{
        require_once "./modelos/usuarioModelo3.php";
    }

    class usuarioControlador3 extends usuarioModelo3{

    

        
        
        
        
        public function datos_usuario_personal_controlador($id_usuario) {
    $id_usuario = mainModel::limpiar_cadena($id_usuario);
    $conexion = mainModel::conectar();

    $sql = $conexion->prepare("
        SELECT 
            u.ID_usuario, u.Nombre, u.Password, u.estado_usuario, u.cargo, u.fecha_creacion,
            p.id_personal, p.nombre_personal, p.apellido_personal, p.ci_personal, p.correo_personal
        FROM usuario u
        INNER JOIN personal p ON p.id_usuario_fk = u.ID_usuario
        WHERE u.ID_usuario = :id
        LIMIT 1
    ");
    $sql->bindParam(":id", $id_usuario, PDO::PARAM_INT);
    $sql->execute();

    return $sql;
}
        
        
        
     public function agregar_personal_controlador() {
        // 🔹 Datos de usuario
        $nombre_u = mainModel::limpiar_cadena($_POST['Nombre_u'] ?? '');
        $clave1   = mainModel::limpiar_cadena($_POST['clave1'] ?? '');
        $clave2   = mainModel::limpiar_cadena($_POST['clave2'] ?? '');
        $cargo    = (int) ($_POST['cargo'] ?? 0);

        // 🔹 Datos personales
        $ci_personal       = (int) ($_POST['ci_personal'] ?? 0);
        $nombre_personal   = mainModel::limpiar_cadena($_POST['nombre_personal'] ?? '');
        $apellido_personal = mainModel::limpiar_cadena($_POST['apellido_personal'] ?? '');
        $correo_personal   = mainModel::limpiar_cadena($_POST['correo_personal'] ?? '');

        // 🔹 Validaciones combinadas
        if($nombre_u == "" || $clave1 == "" || $clave2 == "" || $ci_personal <= 0 || $nombre_personal == "" || $apellido_personal == "" || $correo_personal == "") {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar todos los campos de usuario y personal.",
                "Tipo"=>"error"
            ]);
        }

        if($clave1 !== $clave2){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Claves no coinciden",
                "Texto"=>"Las contraseñas ingresadas deben ser iguales.",
                "Tipo"=>"error"
            ]);
        }

        if($cargo < 1 || $cargo > 5){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Cargo inválido",
                "Texto"=>"El cargo debe estar entre 1 y 5.",
                "Tipo"=>"error"
            ]);
        }

        if(!filter_var($correo_personal, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo inválido",
                "Texto"=>"Debes ingresar un correo electrónico válido.",
                "Tipo"=>"error"
            ]);
        }

        // 🔹 Verificar duplicados
        $pdo = mainModel::conectar();

        $check_usuario = $pdo->prepare("SELECT id_usuario FROM usuario WHERE Nombre = :nombre");
        $check_usuario->bindParam(":nombre", $nombre_u);
        $check_usuario->execute();
        if($check_usuario->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Usuario duplicado",
                "Texto"=>"Ya existe un usuario con ese nombre.",
                "Tipo"=>"error"
            ]);
        }

        $check_ci = $pdo->prepare("SELECT ci_personal FROM personal WHERE ci_personal = :ci");
        $check_ci->bindParam(":ci", $ci_personal);
        $check_ci->execute();
        if($check_ci->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Cédula duplicada",
                "Texto"=>"Ya existe un registro con esa cédula.",
                "Tipo"=>"error"
            ]);
        }

        $check_correo = $pdo->prepare("SELECT correo_personal FROM personal WHERE correo_personal = :correo");
        $check_correo->bindParam(":correo", $correo_personal);
        $check_correo->execute();
        if($check_correo->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo duplicado",
                "Texto"=>"Ya existe un registro con ese correo.",
                "Tipo"=>"error"
            ]);
        }

        // 🔹 Crear usuario
        //$clave_segura = password_hash($clave1, PASSWORD_BCRYPT, ["cost"=>10]);
        $clave_segura = mainModel::encryption($clave1);
        $id_usuario = usuarioModelo3::agregar_usuario_modelo([
            "nombre_usuario" => $nombre_u,
            "clave_usuario"  => $clave_segura,
            "cargo_usuario"  => $cargo
        ]);

        if($id_usuario <= 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al crear usuario",
                "Texto"=>"No se pudo registrar el usuario.",
                "Tipo"=>"error"
            ]);
        }

        // 🔹 Crear personal
        $guardar_personal = usuarioModelo3::agregar_personal_modelo([
            "ci_personal"      => $ci_personal,
            "nombre_personal"  => $nombre_personal,
            "apellido_personal"=> $apellido_personal,
            "correo_personal"  => $correo_personal,
            "id_usuario_fk"    => $id_usuario
        ]);

        if($guardar_personal == 1){
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Personal registrado",
                "Texto"=>"El usuario y sus datos personales fueron guardados correctamente.",
                "Tipo"=>"success"
            ]);
        } else {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al guardar personal",
                "Texto"=>"El usuario fue creado pero no se pudo guardar los datos personales.",
                "Tipo"=>"error"
            ]);
        }
    }

        

        
        
    public function actualizar_cuenta_per_controlador() {
        $id_usuario = mainModel::decryption($_POST['usuario_per_id_up'] ?? '');
        $id_usuario = mainModel::limpiar_cadena($id_usuario);


                
                
        // 🔹 Datos de usuario
        $nombre_u = mainModel::limpiar_cadena($_POST['Nombre_u'] ?? '');
        $clave1   = mainModel::limpiar_cadena($_POST['clave1'] ?? '');
        $clave2   = mainModel::limpiar_cadena($_POST['clave2'] ?? '');
        $cargo    = (int) ($_POST['cargo'] ?? 0);
        $estado   = isset($_POST['estado_usuario']) ? 1 : 0;

        // 🔹 Datos personales
        $ci_personal       = (int) ($_POST['ci_personal'] ?? 0);
        $nombre_personal   = mainModel::limpiar_cadena($_POST['nombre_personal'] ?? '');
        $apellido_personal = mainModel::limpiar_cadena($_POST['apellido_personal'] ?? '');
        $correo_personal   = mainModel::limpiar_cadena($_POST['correo_personal'] ?? '');

  //   echo "<pre>";
//print_r($_POST);
//echo "</pre>";
                         
        // 🔹 Validaciones
        if($nombre_u == "" || $ci_personal <= 0 || $nombre_personal == "" || $apellido_personal == "" || $correo_personal == "") {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar todos los campos obligatorios.",
                "Tipo"=>"error"
            ]);
        }

        if($cargo < 1 || $cargo > 5){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Cargo inválido",
                "Texto"=>"El cargo debe estar entre 1 y 5.",
                "Tipo"=>"error"
            ]);
        }

        if(!filter_var($correo_personal, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo inválido",
                "Texto"=>"Debes ingresar un correo electrónico válido.",
                "Tipo"=>"error"
            ]);
        }

        if($clave1 !== "" || $clave2 !== "") {
            if($clave1 !== $clave2 || strlen($clave1) < 7){
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"Error en la contraseña",
                    "Texto"=>"Las contraseñas deben coincidir y tener al menos 7 caracteres.",
                    "Tipo"=>"error"
                ]);
            }
            $clave_segura = mainModel::encryption($clave1);
        } else {
            $clave_segura = mainModel::conectar()
                ->query("SELECT Password FROM usuario WHERE ID_usuario = '$id_usuario'")
                ->fetchColumn();
        }

        // 🔹 Verificar duplicados excluyendo el actual
        $pdo = mainModel::conectar();

        $check_usuario = $pdo->prepare("SELECT ID_usuario FROM usuario WHERE Nombre = :nombre AND ID_usuario != :id");
        $check_usuario->bindParam(":nombre", $nombre_u);
        $check_usuario->bindParam(":id", $id_usuario);
        $check_usuario->execute();
        if($check_usuario->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Usuario duplicado",
                "Texto"=>"Ya existe otro usuario con ese nombre.",
                "Tipo"=>"error"
            ]);
        }

        $check_ci = $pdo->prepare("
            SELECT p.id_personal FROM personal p
            INNER JOIN usuario u ON u.ID_usuario = p.id_usuario_fk
            WHERE p.ci_personal = :ci AND u.ID_usuario != :id
        ");
        $check_ci->bindParam(":ci", $ci_personal);
        $check_ci->bindParam(":id", $id_usuario);
        $check_ci->execute();
        if($check_ci->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Cédula duplicada",
                "Texto"=>"Ya existe otro registro con esa cédula.",
                "Tipo"=>"error"
            ]);
        }

        $check_correo = $pdo->prepare("
            SELECT p.id_personal FROM personal p
            INNER JOIN usuario u ON u.ID_usuario = p.id_usuario_fk
            WHERE p.correo_personal = :correo AND u.ID_usuario != :id
        ");
        $check_correo->bindParam(":correo", $correo_personal);
        $check_correo->bindParam(":id", $id_usuario);
        $check_correo->execute();
        if($check_correo->rowCount() > 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo duplicado",
                "Texto"=>"Ya existe otro registro con ese correo.",
                "Tipo"=>"error"
            ]);
            
            

        }

        // 🔹 Actualizar usuario
        $usuario_actualizado = usuarioModelo3::actualizar_usuario_per_modelo([
            "ID"       => $id_usuario,
            "Nombre"   => $nombre_u,
            "Password" => $clave_segura,
            "Cargo"    => $cargo,
            "Estado"   => $estado
        ]);

        // 🔹 Actualizar personal
        $personal_actualizado = usuarioModelo3::actualizar_cuenta_personal_modelo([
            "ci_personal"      => $ci_personal,
            "nombre_personal"  => $nombre_personal,
            "apellido_personal"=> $apellido_personal,
            "correo_personal"  => $correo_personal,
            "id_usuario_fk"    => $id_usuario
        ]);

        if($usuario_actualizado->rowCount() > 0 || $personal_actualizado->rowCount() > 0){
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Datos actualizados",
                "Texto"=>"El usuario y sus datos personales fueron modificados correctamente.",
                "Tipo"=>"success"
            ]);
        } else {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Actualización incompleta",
                "Texto"=>"No se pudo actualizar correctamente el usuario o el personal.",
                "Tipo"=>"error"
            ]);
        }
    }
        
        
 
    public function paginador_personal_controlador($pagina, $registros, $vista, $busqueda) {
        $pagina    = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $vista     = mainModel::limpiar_cadena($vista);
        $busqueda  = mainModel::limpiar_cadena($busqueda);

        $orden = $_GET['orden'] ?? "";
        $orden_sql = match($orden) {
            "nombre" => "ORDER BY nombre_personal ASC",
            "fecha"  => "ORDER BY usuario.ID_usuario DESC",
            default  => "ORDER BY usuario.ID_usuario ASC"
        };

        $filtro_usuario  = $_GET['filtro_usuario']  ?? "";
        $filtro_personal = $_GET['filtro_personal'] ?? "";
        $filtro_ci       = $_GET['filtro_ci']       ?? "";
        $filtro_cargo    = $_GET['filtro_cargo']    ?? "";
        $filtro_estado   = $_GET['filtro_estado']   ?? "";

        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        // WHERE dinámico
        $where = "usuario.ID_usuario = personal.id_usuario_fk";
        if ($busqueda != "") {
            $where .= " AND (nombre_personal LIKE '%$busqueda%' OR apellido_personal LIKE '%$busqueda%' OR ci_personal LIKE '%$busqueda%')";
        }
        if ($filtro_usuario != "")  $where .= " AND usuario.ID_usuario = '$filtro_usuario'";
        if ($filtro_personal != "") $where .= " AND personal.id_personal = '$filtro_personal'";
        if ($filtro_ci != "")       $where .= " AND personal.ci_personal = '$filtro_ci'";
        if ($filtro_cargo != "")    $where .= " AND usuario.cargo = '$filtro_cargo'";
        if ($filtro_estado != "")   $where .= " AND usuario.estado_usuario = '$filtro_estado'";

        $consulta = "
            SELECT SQL_CALC_FOUND_ROWS 
                usuario.ID_usuario, usuario.Nombre, usuario.cargo, usuario.estado_usuario,
                personal.id_personal, personal.nombre_personal, personal.apellido_personal,
                personal.ci_personal, personal.correo_personal
            FROM usuario, personal
            WHERE $where
            $orden_sql
            LIMIT $inicio, $registros
        ";

        $conexion = mainModel::conectar();
        $datos    = $conexion->query($consulta)->fetchAll();
        $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        $Npaginas = ceil($total / $registros);

        // Construcción de tabla
        $tabla = '<div class="table-responsive">
            <table class="table table-dark table-sm">
                <thead>
                    <tr class="text-center roboto-medium">
                        <th>ID Usuario</th>
                        <th>ID Personal</th>
                        <th>Nombre</th>
                        <th>Apellido</th>
                        <th>Usuario</th>
                        <th>Cédula</th>
                        <th>Correo</th>
                        <th>Cargo</th>
                        <th>Activo</th>
                        <th>Actualizar</th>
                    </tr>
                </thead>
                <tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $reg_inicio = $inicio + 1;

            foreach ($datos as $rows) {
                $activo = $rows['estado_usuario'] == 1 ? "Sí" : "No";

                $tabla .= '
                <tr class="text-center">
                    <td>' . $rows['ID_usuario'] . '</td>
                    <td>' . $rows['id_personal'] . '</td>
                    <td>' . $rows['nombre_personal'] . '</td>
                    <td>' . $rows['apellido_personal'] . '</td>
                    <td>' . $rows['Nombre'] . '</td>
                    <td>' . $rows['ci_personal'] . '</td>
                    <td>' . $rows['correo_personal'] . '</td>
                    <td>' . $rows['cargo'] . '</td>
                    <td>' . $activo . '</td>
                    <td>
                        <a href="' . SERVERURL . 'index.php?views=user-upt-per&usuario_id_up=' . mainModel::encryption($rows['ID_usuario']) . '" class="btn btn-success">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </td>
                </tr>';
            }

            $reg_final = $reg_inicio + count($datos) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="10">';
            $tabla .= ($total >= 1)
                ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic acá para recargar el listado</a>'
                : 'No hay registros en el sistema';
            $tabla .= '</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador con filtros
        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando personal ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

            $queryParams = http_build_query([
                'orden' => $orden,
                'filtro_usuario' => $filtro_usuario,
                'filtro_personal' => $filtro_personal,
                'filtro_ci' => $filtro_ci,
                'filtro_cargo' => $filtro_cargo,
                'filtro_estado' => $filtro_estado,
                'busqueda' => $busqueda
            ]);

            $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
        }

        return $tabla;
    }
               
        
        
        
        
        
        
              
   /*     
        
        
        public function datos_usuario_estudiante_controlador($id_usuario) {
    $id_usuario = mainModel::limpiar_cadena($id_usuario);
    $conexion = mainModel::conectar();

    $sql = $conexion->prepare("
        SELECT 
            u.ID_usuario, u.Nombre, u.estado_usuario, u.fecha_creacion, u.Password,
            e.id_estudiante, e.nombre, e.apellido, e.ci_estudiante,
            e.telefono_estudiante, e.correo_estudiante
        FROM usuario u
        INNER JOIN usuario_estudiante ue ON ue.id_usuario_fk = u.ID_usuario
        INNER JOIN estudiante e ON e.id_estudiante = ue.id_estudiante_fk
        WHERE u.ID_usuario = :id
        LIMIT 1
    ");
    $sql->bindParam(":id", $id_usuario, PDO::PARAM_INT);
    $sql->execute();

    return $sql;
}*/
        
        
        
        /*
        public function agregar_cuenta_estudiante_controlador() {
            $id_estudiante = (int) ($_POST['id_estudiante'] ?? 0);
            $clave1        = mainModel::limpiar_cadena($_POST['clave1'] ?? '');
            $clave2        = mainModel::limpiar_cadena($_POST['clave2'] ?? '');

            if ($id_estudiante <= 0 || $clave1 === '' || $clave2 === '' || $clave1 !== $clave2 || strlen($clave1) < 7) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Datos inválidos",
                    "Texto"  => "Verifique que el estudiante esté seleccionado y que las contraseñas coincidan y tengan al menos 7 caracteres.",
                    "Tipo"   => "error"
                ]);
            }

            $pdo = mainModel::conectar();
            $sql = $pdo->prepare("SELECT ci_estudiante FROM estudiante WHERE id_estudiante = :id AND estado_estudiante = 1");
            $sql->bindParam(":id", $id_estudiante);
            $sql->execute();

            if ($sql->rowCount() !== 1) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Estudiante no válido",
                    "Texto"  => "El estudiante no existe o está inactivo.",
                    "Tipo"   => "error"
                ]);
            }

            $ci = $sql->fetchColumn();
	        $clave_segura = mainModel::encryption($clave1);

            $id_usuario = usuarioModelo3::agregar_cuenta_estudiante_modelo([
                "nombre_usuario" => $ci,
                "clave_usuario"  => $clave_segura,
                "cargo_usuario"  => 5
            ]);

            if ($id_usuario <= 0) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error al crear usuario",
                    "Texto"  => "No se pudo registrar el usuario.",
                    "Tipo"   => "error"
                ]);
            }

            $vinculado = usuarioModelo3::vincular_usuario_estudiante_modelo([
                "id_estudiante_fk" => $id_estudiante,
                "id_usuario_fk"    => $id_usuario
            ]);

            if ($vinculado == 1) {
                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Cuenta creada",
                    "Texto"  => "La cuenta del estudiante fue creada correctamente.",
                    "Tipo"   => "success"
                ]);
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error al vincular",
                    "Texto"  => "El usuario fue creado pero no se pudo vincular al estudiante.",
                    "Tipo"   => "error"
                ]);
            }
        }
        */
        
        
        
        
        
        
        
        
        /*
public function paginador_cuenta_estudiante_controlador($pagina, $registros, $vista, $busqueda) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $busqueda  = mainModel::limpiar_cadena($busqueda);

    $orden = $_GET['orden'] ?? "";
    $orden_sql = match($orden) {
        "nombre" => "ORDER BY estudiante.nombre ASC",
        "fecha"  => "ORDER BY usuario.ID_usuario DESC",
        "cedula" => "ORDER BY estudiante.ci_estudiante ASC",
        default  => "ORDER BY usuario.ID_usuario ASC"
    };

    $filtro_usuario   = $_GET['filtro_usuario']   ?? "";
    $filtro_estudiante= $_GET['filtro_estudiante']?? "";
    //$filtro_ci        = $_GET['filtro_ci']        ?? "";
	$filtro_ci = trim(mainModel::limpiar_cadena($_GET['filtro_ci'] ?? ''));

    $filtro_estado    = $_GET['filtro_estado']    ?? "";

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    // WHERE dinámico
    $where = "
        usuario.ID_usuario = usuario_estudiante.id_usuario_fk
        AND estudiante.id_estudiante = usuario_estudiante.id_estudiante_fk
        AND estudiante.estado_estudiante = 1
    ";

    if ($busqueda != "") {
        $where .= " AND (estudiante.nombre LIKE '%$busqueda%' OR estudiante.apellido LIKE '%$busqueda%' OR estudiante.ci_estudiante LIKE '%$busqueda%')";
    }
    if ($filtro_usuario != "")    $where .= " AND usuario.ID_usuario = '$filtro_usuario'";
    if ($filtro_estudiante != "") $where .= " AND estudiante.id_estudiante = '$filtro_estudiante'";
    if ($filtro_ci != "")         $where .= " AND estudiante.ci_estudiante = '$filtro_ci'";
    if ($filtro_estado != "")     $where .= " AND usuario.estado_usuario = '$filtro_estado'";

    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS
            usuario.ID_usuario, usuario.Nombre, usuario.estado_usuario,
            estudiante.id_estudiante, estudiante.nombre, estudiante.apellido,
            estudiante.ci_estudiante, estudiante.telefono_estudiante, estudiante.correo_estudiante
        FROM usuario, estudiante, usuario_estudiante
        WHERE $where
        $orden_sql
        LIMIT $inicio, $registros
    ";

    $conexion = mainModel::conectar();
    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Construcción de tabla
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID Usuario</th>
                    <th>ID Estudiante</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Cédula</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Activo</th>
                    <th>Actualizar</th>
                    <th>Eliminar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $rows) {
            $activo = $rows['estado_usuario'] == 1 ? "Sí" : "No";

            $tabla .= '
            <tr class="text-center">
                <td>' . $rows['ID_usuario'] . '</td>
                <td>' . $rows['id_estudiante'] . '</td>
                <td>' . $rows['nombre'] . '</td>
                <td>' . $rows['apellido'] . '</td>
                <td>' . $rows['ci_estudiante'] . '</td>
                <td>' . $rows['telefono_estudiante'] . '</td>
                <td>' . $rows['correo_estudiante'] . '</td>
                <td>' . $activo . '</td>
                <td>
                    
                    <a href="' . SERVERURL . 'index.php?views=user-upt-est&usuario_id_up=' . mainModel::encryption($rows['ID_usuario']) . '" class="btn btn-success">
                        <i class="fas fa-sync-alt"></i>
                    </a>                    
                </td>
                <td>
                                
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/usuarioAjax2.php" method="POST" data-form="delete" data-entity="usuario_estudiante" autocomplete="off">
                        <input type="hidden" name="usuario_est_id_del" value="' . mainModel::encryption($rows['ID_usuario']) . '">
                        <button type="submit" class="btn btn-danger btn-sm" title="Eliminar cuenta de usuario (mantiene estudiante)">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                
                </td>
            </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="9">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic acá para recargar el listado</a>'
            : 'No hay registros en el sistema';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando estudiante ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'orden' => $orden,
            'filtro_usuario' => $filtro_usuario,
            'filtro_estudiante' => $filtro_estudiante,
            'filtro_ci' => $filtro_ci,
            'filtro_estado' => $filtro_estado,
            'busqueda' => $busqueda
        ]);

        $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}        
        
        
        
        
        
public function actualizar_cuenta_est_controlador() {
   
    $id_usuario = mainModel::decryption($_POST['usuario_est_id_up']);
	$id_usuario = mainModel::limpiar_cadena($id_usuario); 
    $estado_usuario = isset($_POST['estado_usuario']) ? 1 : 0;

    $clave1 = $_POST['clave1'] ?? "";
    $clave2 = $_POST['clave2'] ?? "";

    // Validación de clave
    if ($clave1 !== "" && $clave2 !== "") {
        if (strlen($clave1) < 7 || strlen($clave2) < 7 || $clave1 !== $clave2) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error en la contraseña",
                "Texto"  => "Las contraseñas deben coincidir y tener al menos 7 caracteres.",
                "Tipo"   => "error"
            ]);
        }
        
        $clave_encriptada = mainModel::encryption($clave1);
    } else {
        // Si no se cambia la clave, mantener la actual
        $clave_encriptada = mainModel::conectar()
            ->query("SELECT Password FROM usuario WHERE ID_usuario = '$id_usuario'")
            ->fetchColumn();
    }

    $datos = [
        "Password" => $clave_encriptada,
        "Estado"   => $estado_usuario,
        "ID"       => $id_usuario
    ];


    $actualizar = $this->actualizar_cuenta_est_modelo($datos);

    if ($actualizar->execute()) {
        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Cuenta actualizada",
            "Texto"  => "Los datos han sido modificados correctamente.",
            "Tipo"   => "success"
        ]);
    } else {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto"  => "No se pudo actualizar la cuenta.",
            "Tipo"   => "error"
        ]);
    }
}        
        */
        
        
        
        
        /*
        
        
// En usuarioControlador3.php
public function eliminar_cuenta_estudiante_controlador() {
    // Verificar permisos
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1) {
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
        // Obtener ID del usuario
        $usuario_id = mainModel::decryption($_POST['usuario_est_id_del']);
        $usuario_id = mainModel::limpiar_cadena($usuario_id);

        if (empty($usuario_id)) {
            throw new Exception("ID de usuario inválido");
        }

        // Iniciar transacción
        $pdo->beginTransaction();

        // 1. Obtener el id_estudiante_fk antes de eliminar
        $consultaRelacion = $pdo->prepare("
            SELECT id_estudiante_fk 
            FROM usuario_estudiante 
            WHERE id_usuario_fk = :usuario_id
        ");
        $consultaRelacion->execute([':usuario_id' => $usuario_id]);
        
        if ($consultaRelacion->rowCount() === 0) {
            throw new Exception("No se encontró la relación usuario-estudiante");
        }
        
        $id_estudiante = $consultaRelacion->fetchColumn();

        // 2. Eliminar la relación en usuario_estudiante
        $deleteRelacion = $pdo->prepare("
            DELETE FROM usuario_estudiante 
            WHERE id_usuario_fk = :usuario_id
        ");
        $deleteRelacion->execute([':usuario_id' => $usuario_id]);

        // 3. Eliminar el usuario
        $deleteUsuario = $pdo->prepare("
            DELETE FROM usuario 
            WHERE ID_usuario = :usuario_id
        ");
        $deleteUsuario->execute([':usuario_id' => $usuario_id]);

        $pdo->commit();

        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Cuenta Eliminada",
            "Texto" => "La cuenta de usuario del estudiante ha sido eliminada exitosamente. El estudiante se mantiene en el sistema.",
            "Tipo" => "success"
        ];

    } catch (Exception $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "Error al eliminar la cuenta: " . $e->getMessage(),
            "Tipo" => "error"
        ];
    }

    return json_encode($alerta);
}
              
        
        /*--------- Controlador eliminar usuario ---------*
        public function eliminar_usuario_controlador(){

            $id=mainModel::decryption($_POST['usuario_id_del']);
            $id=mainModel::limpiar_cadena($id);

            if($id==1){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No podemos eliminar el usuario principal del sistema",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            $check_usuario=mainModel::ejecutar_consulta_simple("SELECT ID_usuario FROM usuario WHERE ID_usuario='$id'");
            if($check_usuario->rowCount()<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El usuario que intenta eliminar no existe en el sistema",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            session_start(['name'=>'SPM']);
            if($_SESSION['cargo_spm']!=1){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No tienes los permisos necesarios para realizar esta operación",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            // AQUÍ TAMBIÉN: usuarioModelo3, no usuarioModelo
            $eliminar_usuario=usuarioModelo3::eliminar_usuario_modelo($id);

            if($eliminar_usuario->rowCount()==1){
                $alerta=[
                    "Alerta"=>"recargar",
                    "Titulo"=>"Usuario eliminado",
                    "Texto"=>"El usuario ha sido eliminado del sistema exitosamente",
                    "Tipo"=>"success"
                ];
            }else{
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido eliminar el usuario, por favor intente nuevamente",
                    "Tipo"=>"error"
                ];
            }

            echo json_encode($alerta);
        }  /*Fin controlador */

        
        
        
        /*--------- Controlador actualizar usuario ---------/
        public function actualizar_usuario_controlador(){
            // Implementación básica
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Función no implementada",
                "Texto"=>"La función actualizar aún no está implementada",
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
        }*/
        
        
        
    }
?>
