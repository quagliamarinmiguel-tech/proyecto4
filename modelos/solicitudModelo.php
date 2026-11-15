<?php


//ANTES ESTE ERA recuperarModelo, Le cambie el nommbre
//esta vista maneja todo lo relacionado con las solicitudes de nuevo ingreso
//su ajax solo maneja la peticion de inscribir al estudiante, la de crear la solicitud esta en loginAjax
//este controlador en 2025 si recuerdo bien manejaba el bloqueo de intentos de acceso fallidos, cambios de clave, creacion de cuentas, y funciones de solicitudes de nuevo ingreso


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

    
        
    public static function obtener_solicitud_inscripcion_supabase(int $registrationId, string $accessToken): array {
        return SupabaseApi::rpcWithToken('obtener_solicitud_inscripcion', [
            'p_registration_id' => $registrationId,
        ], $accessToken);
    }

    
    
    // RECHAZAR
    public static function rechazar_solicitud_nuevo_ingreso_supabase(
        int $registrationId,
        string $accessToken
    ): array {
        return SupabaseApi::rpcWithToken('rechazar_solicitud_nuevo_ingreso', [
            'p_registration_id' => $registrationId,
        ], $accessToken);
    }    
    
    
    
    // DATOS PARA VALIDAR ACEPTACIÓN
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
    
    
    // OBTENER PRECIO ESPERADO DE INSCRIPCIÓN DEL PERIODO
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
    
    
    

    // ACEPTAR
    public static function aceptar_solicitud_nuevo_ingreso_supabase(
        int $registrationId,
        string $userId,
        ?int $sectionId,
        ?string $newSectionName,
        ?int $teacherId,
        ?string $sectionDay, 
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






}



?>