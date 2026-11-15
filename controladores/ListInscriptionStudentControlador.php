<?php
require_once "./modelos/mainModel.php";

class inscripcionControlador extends mainModel {

public function listar_inscripciones_estudiante() {
    if (empty($_SESSION['id_spm'])) {
        return [];
    }

    $resProfile = mainModel::supabaseGet('profile', 'id', 
        ['user_id' => 'eq.' . $_SESSION['id_spm']], 
        SUPABASE_SECRET_KEY
    );
    $profileId = $resProfile['data'][0]['id'] ?? null;
    if (!$profileId) return [];

    $resStudent = mainModel::supabaseGet('student', 'id', 
        ['profile_id' => 'eq.' . $profileId, 'state' => 'eq.1'], 
        SUPABASE_SECRET_KEY
    );
    $studentId = $resStudent['data'][0]['id'] ?? null;
    if (!$studentId) return [];

    $res = mainModel::supabaseGet('registration', 
        'id,registration_date,abandonment_date,current_state,last_qualification,observation,missing_payment,graduand_code,state,section_id,period_course_id',
        ['student_id' => 'eq.' . $studentId, 'state' => 'eq.1'],
        SUPABASE_SECRET_KEY
    );
    $inscripciones = $res['data'] ?? [];
    if (!is_array($inscripciones)) return [];

    foreach ($inscripciones as &$ins) {
        // Sección
        $sec = mainModel::supabaseGet('section', 'name,section_day', 
            ['id' => 'eq.' . ($ins['section_id'] ?? 0)], SUPABASE_SECRET_KEY);
        $ins['section'] = $sec['data'][0] ?? null;

        // Period course
        $pc = mainModel::supabaseGet('period_course', 'course_id,period_id', 
            ['id' => 'eq.' . ($ins['period_course_id'] ?? 0)], SUPABASE_SECRET_KEY);
        $pcData = $pc['data'][0] ?? null;
        if ($pcData) {
            $course = mainModel::supabaseGet('course', 'name', 
                ['id' => 'eq.' . $pcData['course_id']], SUPABASE_SECRET_KEY);
            $period = mainModel::supabaseGet('period', 'name', 
                ['id' => 'eq.' . $pcData['period_id']], SUPABASE_SECRET_KEY);
            $ins['course'] = $course['data'][0] ?? null;
            $ins['period'] = $period['data'][0] ?? null;
        } else {
            $ins['course'] = null;
            $ins['period'] = null;
        }
    }
    unset($ins);

    return $inscripciones;
}
}
?>