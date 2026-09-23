<?php


//ANTES ESTE ERA recuperarModelo, Le cambie el nommbre
//esta vista maneja todo lo relacionado con las solicitudes de nuevo ingreso
//su ajax solo maneja la peticion de inscribir al estudiante, la de crear la solicitud esta en loginAjax
//este controlador en 2025 si recuerdo bien manejaba el bloqueo de intentos de acceso fallidos, cambios de clave, creacion de cuentas, y funciones de solicitudes de nuevo ingreso

    
    
// 1. Declarar los namespaces primero
//use PHPMailer\PHPMailer\PHPMailer;
//use PHPMailer\PHPMailer\Exception;
// 2. Incluir las clases necesarias
//require_once __DIR__ . '/../librerias/PHPMailer/src/Exception.php';
//require_once __DIR__ . '/../librerias/PHPMailer/src/PHPMailer.php';
//require_once __DIR__ . '/../librerias/PHPMailer/src/SMTP.php';



require_once "mainModel.php";

class solicitudModelo extends mainModel {


    public static function registrar_solicitud_inscripcion_supabase(
        string $email, string $ci, string $name, string $lastName,
        string $birthday, string $phone, string $whatsapp,
        string $address, string $birthplace, string $educationLevel,
        bool $work, string $currentWork, int $periodCourseId,
        int $cost, string $paymentMethod, string $imgRoute,
        string $tempPassword, string $reference
    ): array {
        return SupabaseApi::rpcWithoutToken('registrar_solicitud_inscripcion', [
            'p_email'             => $email,
            'p_ci'                => $ci,
            'p_name'              => $name,
            'p_last_name'         => $lastName,
            'p_birthday'          => $birthday,
            'p_phone'             => $phone,
            'p_whatsapp'          => $whatsapp,
            'p_address'           => $address,
            'p_birthplace'        => $birthplace,
            'p_education_level'   => $educationLevel,
            'p_work'              => $work,
            'p_current_work'      => $currentWork,
            'p_period_course_id'  => $periodCourseId,
            'p_cost'              => $cost,
            'p_payment_method'    => $paymentMethod,
            'p_img_route'         => $imgRoute,
            'p_temp_password'     => $tempPassword,
            'p_reference'         => $reference,
        ]);
    }
    
    
    public static function listar_solicitudes_nuevo_ingreso_supabase(
        array $filtros,
        string $accessToken,
        int $limit,
        int $offset
    ): array {
        $params = [
            'p_busqueda' => $filtros['busqueda'] ?? null,
            'p_orden'    => $filtros['orden']    ?? 'reciente',
        	'p_state'       => $filtros['estado']      ?? null,
        	'p_fecha_desde' => $filtros['fecha_desde'] ?? null,  
        	'p_fecha_hasta' => $filtros['fecha_hasta'] ?? null,   
            'p_limit'    => $limit,
            'p_offset'   => $offset,
        ];
        return SupabaseApi::rpcWithToken('listar_solicitudes_nuevo_ingreso', $params, $accessToken);
    }


    
    
    
    /*
//se obtiene para solicitud nuevo ingreso view, los datos a mostrar de la solicitud
    public static function obtener_solicitud_inscripcion_supabase(int $registrationId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('obtener_solicitud_nuevo_ingreso', [
            'p_registration_id' => $registrationId,
        ], $accessToken);
    }*/
    
        
     // ============================================
    public static function obtener_solicitud_inscripcion_supabase(int $registrationId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('obtener_solicitud_inscripcion', [
            'p_registration_id' => $registrationId,
        ], $accessToken);
    }

    
/* esta ya no se usa, lo hace la rpc
//crea una seccion desde solicitud nuevo ingreso check
    public static function crear_seccion_automatica_supabase(int $periodCourseId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('crear_seccion_automatica', [
            'p_period_course_id' => $periodCourseId,
        ], $accessToken);
    }*/
    
    
    
    // ============================================
    // RECHAZAR
    // ============================================
    public static function rechazar_solicitud_nuevo_ingreso_supabase(
        int $registrationId,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('rechazar_solicitud_nuevo_ingreso', [
            'p_registration_id' => $registrationId,
        ], $accessToken);
    }    
    
    
    
    // ============================================
    // DATOS PARA VALIDAR ACEPTACIÓN
    // ============================================
    public static function obtener_datos_aceptacion_supabase(
        int $registrationId,
        string $accessToken
    ): array {
        return SupabaseApi::getWithToken('registration', [
            'select' => 'id,student_id,period_course_id,'
                      . 'student!inner(email,profile!inner(id,temp_password)),'
                      . 'transaction_detail!inner(amount,motive_payment,transaction!inner(cost))',
            'id'    => 'eq.' . $registrationId,
            'state' => 'eq.2',
            'limit' => '1',
        ], $accessToken);
    }
    
    
    
    // ============================================
    // OBTENER PRECIO ESPERADO DE INSCRIPCIÓN DEL PERIODO
    // ============================================
    public static function obtener_precio_inscripcion_supabase(
        int $periodCourseId,
        string $accessToken
    ): array {
        return SupabaseApi::getWithToken('period_course', [
            'select' => 'period_id,period!inner(id,period_costs!inner(cost_description,price))',
            'id'     => 'eq.' . $periodCourseId,
            'limit'  => '1',
        ], $accessToken);
    }
    
    
    

    // ============================================
    // ACEPTAR
    // ============================================
    public static function aceptar_solicitud_nuevo_ingreso_supabase(
        int $registrationId,
        string $userId,
        ?int $sectionId,
        ?string $newSectionName,
        ?int $teacherId,
        ?string $sectionDay,    //  nuevo
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('aceptar_solicitud_nuevo_ingreso', [
            'p_registration_id'  => $registrationId,
            'p_user_id'          => $userId,
            'p_section_id'       => $sectionId,
            'p_new_section_name' => $newSectionName,
            'p_teacher_id'       => $teacherId,
            'p_section_day'      => $sectionDay,
        ], $accessToken);
    }

    
    


    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    /*
    public static function registrar_solicitud_inscripcion_supabase(
        string $userId,
        string $email,
        string $ci,
        string $name,
        string $lastName,
        string $birthday,
        string $phone,
        string $whatsapp,
        string $address,
        string $birthplace,
        string $educationLevel,
        bool $work,
        string $currentWork,
        int $courseId,
        int $cost,
        string $paymentMethod,
        string $imgRoute,
        string $registrationDate,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('registrar_solicitud_inscripcion', [
            'p_user_id'           => $userId,
            'p_email'             => $email,
            'p_ci'                => $ci,
            'p_name'              => $name,
            'p_last_name'         => $lastName,
            'p_birthday'          => $birthday,
            'p_phone'             => $phone,
            'p_whatsapp'          => $whatsapp,
            'p_address'           => $address,
            'p_birthplace'        => $birthplace,
            'p_education_level'   => $educationLevel,
            'p_work'              => $work,
            'p_current_work'      => $currentWork,
            'p_course_id'         => $courseId,
            'p_cost'              => $cost,
            'p_payment_method'    => $paymentMethod,
            'p_img_route'         => $imgRoute,
            'p_registration_date' => $registrationDate,
        ], $accessToken);
    }*/
    
   
    /*
public static function buscar_usuario_por_datos($datos) {
    $sql = self::conectar()->prepare("
        SELECT u.ID_usuario, u.cargo
        FROM usuario u
        INNER JOIN usuario_estudiante ue ON ue.id_usuario_fk = u.ID_usuario
        INNER JOIN estudiante e ON e.id_estudiante = ue.id_estudiante_fk
        WHERE e.ci_estudiante = :Cedula AND e.telefono_estudiante = :Telefono AND e.correo_estudiante = :Correo AND e.fecha_nacimiento = :FechaNacimiento AND estado_estudiante = 1
        LIMIT 1
    ");
    $sql->bindParam(":Cedula", $datos['Cedula']);
    $sql->bindParam(":Telefono", $datos['Telefono']);
    $sql->bindParam(":Correo", $datos['Correo']);
    $sql->bindParam(":FechaNacimiento", $datos['FechaNacimiento']);
    $sql->execute();
    return $sql;
}*/



/*
public static function buscar_estudiante_sin_usuario($datos) {
    $sql = self::conectar()->prepare("
        SELECT e.id_estudiante
        FROM estudiante e
        WHERE e.ci_estudiante = :Cedula
          AND e.telefono_estudiante = :Telefono
          AND e.correo_estudiante = :Correo
          AND e.fecha_nacimiento = :FechaNacimiento
          AND NOT EXISTS (
              SELECT 1
              FROM usuario_estudiante ue
              WHERE ue.id_estudiante_fk = e.id_estudiante AND estado_estudiante = 1
          )
        LIMIT 1
    ");
    $sql->bindParam(":Cedula", $datos['Cedula']);
    $sql->bindParam(":Telefono", $datos['Telefono']);
    $sql->bindParam(":Correo", $datos['Correo']);
    $sql->bindParam(":FechaNacimiento", $datos['FechaNacimiento']);
    $sql->execute();
    return $sql;
}

 
    
    public static function crear_usuario_estudiante($id_estudiante, $clave_encriptada) {
        try {
            $pdo = self::conectar();

            // Buscar CI del estudiante
            $stmt = $pdo->prepare("
                SELECT ci_estudiante
                FROM estudiante
                WHERE id_estudiante = :id
                LIMIT 1
            ");
            $stmt->bindParam(":id", $id_estudiante);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                return false; // No se encontró el estudiante
            }

            $est = $stmt->fetch();
            $nombre_usuario = trim($est['ci_estudiante']); // ← usar CI como nombre de usuario
            $fecha_creacion = date("Y-m-d H:i:s"); // ← fecha y hora actual

            // Insertar nuevo usuario
            $sql = $pdo->prepare("
                INSERT INTO usuario (Nombre, Password, cargo, estado_usuario, fecha_creacion)
                VALUES (:nombre_usuario, :clave, 5, 1, :fecha_creacion)
            ");
            $sql->bindParam(":nombre_usuario", $nombre_usuario);
            $sql->bindParam(":clave", $clave_encriptada);
            $sql->bindParam(":fecha_creacion", $fecha_creacion);
            $sql->execute();

            // Obtener el ID del nuevo usuario
            $id_usuario = $pdo->lastInsertId();

            // Registrar relación con estudiante
            $registrado = self::registrar_usuario_estudiante($id_usuario, $id_estudiante);

            if ($registrado) {
                return $id_usuario;
            } else {
                // Si falla la relación, eliminar el usuario creado
                $pdo->prepare("DELETE FROM usuario WHERE ID_usuario = :id")->execute([':id' => $id_usuario]);
                return false;
            }

        } catch (PDOException $e) {
            error_log("⛔ Error al crear usuario estudiante: " . $e->getMessage());
            return false;
        }
    }
    
    





public static function registrar_usuario_estudiante($id_usuario, $id_estudiante) {
    try {
        $sql = self::conectar()->prepare("
            INSERT INTO usuario_estudiante (id_usuario_fk, id_estudiante_fk)
            VALUES (:usuario, :estudiante)
        ");
        $sql->bindParam(":usuario", $id_usuario, PDO::PARAM_INT);
        $sql->bindParam(":estudiante", $id_estudiante, PDO::PARAM_INT);
        return $sql->execute();
    } catch (PDOException $e) {
        error_log("⛔ Error al registrar usuario_estudiante: " . $e->getMessage());
        return false;
    }
}






    /* Actualizar clave del usuario *
    protected static function actualizar_clave($usuario_id, $clave_encriptada) {
        $sql = self::conectar()->prepare("
            UPDATE usuario SET Password = :Clave WHERE ID_usuario = :ID_usuario
        ");
        $sql->bindParam(":Clave", $clave_encriptada);
        $sql->bindParam(":ID_usuario", $usuario_id);
        $sql->execute();
        return $sql;
    }

    /* Obtener registro de IP visitante *
    protected static function obtener_ip_visitante($ip) {
        $sql = self::conectar()->prepare("
            SELECT * FROM ip_visitante WHERE direccion_ip = :IP LIMIT 1
        ");
        $sql->bindParam(":IP", $ip);
        $sql->execute();
        return $sql->fetch();
    }

   */





/*
    protected static function guardar_solicitud_inscripcion_modelo($datos) {
        $sql = "
            INSERT INTO solicitud_inscripcion (
                nombre_estudiante,
                apellido_estudiante,
                correo_estudiante,
                id_curso_seleccionado,
                fecha_nacimiento,
                cedula_estudiante,
                telefono_estudiante,
                monto,
                fecha_pago,
                metodo_pago,
                captura_pago,
                clave,
                fecha_solicitud,
            	estado_solicitud
            ) VALUES (
                :nombre,
                :apellido,
                :correo,
                :id_curso,
                :fecha_nacimiento,
                :cedula,
                :telefono,
                :monto,
                :fecha_pago,
                :metodo_pago,
                :foto,
                :clave,
                :fecha_solicitud,
	            :estado_solicitud
            )
        ";

        $stmt = mainModel::conectar()->prepare($sql);
            $fechaActual = date('Y-m-d H:i:s');
		    $estado = "Pendiente";

        $stmt->bindParam(':nombre',           $datos['nombre']);
        $stmt->bindParam(':apellido',         $datos['apellido']);
        $stmt->bindParam(':correo',           $datos['correo']);
        $stmt->bindParam(':id_curso',         $datos['id_curso'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nac']);
        $stmt->bindParam(':cedula',           $datos['cedula']);
        $stmt->bindParam(':telefono',         $datos['telefono']);
        $stmt->bindParam(':monto',            $datos['monto']);
        $stmt->bindParam(':fecha_pago',       $datos['fecha_pago']);
        $stmt->bindParam(':metodo_pago',      $datos['metodo']);
        $stmt->bindParam(':foto',             $datos['foto']);
        $stmt->bindParam(':clave',            $datos['clave']);
        $stmt->bindParam(':fecha_solicitud',  $fechaActual);
	    $stmt->bindParam(':estado_solicitud', $estado);

        $stmt->execute();
        return $stmt;
    }*/











    /* Limpiar intentos tras éxito *
    protected static function limpiar_intentos_ip($ip, $tipo) {
        $campo = $tipo === 'login' ? 'intentos_login_fallidos' : 'intentos_recuperacion_fallidos';
        $sql = self::conectar()->prepare("
            UPDATE ip_visitante SET $campo = 0, bloqueado_hasta = NULL WHERE direccion_ip = :IP
        ");
        $sql->bindParam(":IP", $ip);
        $sql->execute();
    }*/
}



?>