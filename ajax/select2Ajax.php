<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

session_start(['name' => 'SPM']);

if (isset($_GET['accion']) && $_GET['accion'] === 'buscar_curso') {
    header('Content-Type: application/json; charset=utf-8');
    $token = $_SESSION['auth_access_token'] ?? null;
    $term = trim(mainModel::limpiar_cadena($_GET['q'] ?? ''));

    if (!$token || mb_strlen($term) < 2) {
        echo json_encode([]);
        exit();
    }

    $resultado = SupabaseApi::getWithToken('course', [
        'select' => 'id,name,type',
        'state' => 'neq.0',
        'name' => 'ilike.*' . $term . '*',
        'order' => 'name.asc',
        'limit' => '10',
    ], $token);

    $cursos = [];
    foreach (($resultado['data'] ?? []) as $curso) {
        $cursos[] = [
            'id_curso' => $curso['id'],
            'nombre_curso' => $curso['name'],
            'tipo_curso' => $curso['type'],
        ];
    }

    echo json_encode($cursos, JSON_UNESCAPED_UNICODE);
    exit();
}

if (isset($_GET['accion']) && $_GET['accion'] === 'buscar_curso_archivado') {
    header('Content-Type: application/json; charset=utf-8');
    $token = $_SESSION['auth_access_token'] ?? null;
    $term = trim(mainModel::limpiar_cadena($_GET['q'] ?? ''));

    if (!$token || mb_strlen($term) < 2) {
        echo json_encode([]);
        exit();
    }

    $resultado = SupabaseApi::getWithToken('course', [
        'select' => 'id,name,type',
        'state' => 'neq.1',
        'name' => 'ilike.*' . $term . '*',
        'order' => 'name.asc',
        'limit' => '10',
    ], $token);

    $cursos = [];
    foreach (($resultado['data'] ?? []) as $curso) {
        $cursos[] = [
            'id_curso' => $curso['id'],
            'nombre_curso' => $curso['name'],
            'tipo_curso' => $curso['type'],
        ];
    }

    echo json_encode($cursos, JSON_UNESCAPED_UNICODE);
    exit();
}


if (isset($_GET['accion']) && $_GET['accion'] === 'buscar_profesor') {
    header('Content-Type: application/json; charset=utf-8');
    $token = $_SESSION['auth_access_token'] ?? null;
    $term = trim(mainModel::limpiar_cadena($_GET['q'] ?? ''));

    if (!$token || mb_strlen($term) < 2) {
        echo json_encode([]);
        exit();
    }

    $resultado = SupabaseApi::getWithToken('teacher', [
        'select' => 'id,name,last_name',
        'state' => 'neq.0',
        'or' => '(name.ilike.*' . $term . '*,last_name.ilike.*' . $term . '*)',
        'order' => 'name.asc',
        'limit' => '10',
    ], $token);

    $profesores = [];
    foreach (($resultado['data'] ?? []) as $profe) {
        $profesores[] = [
            'id' => $profe['id'],
            'name' => $profe['name'],
            'last_name' => $profe['last_name'],
        ];
    }

    echo json_encode($profesores, JSON_UNESCAPED_UNICODE);
    exit();
}


if (isset($_GET['accion']) && $_GET['accion'] === 'buscar_profesor_archivado') {
    header('Content-Type: application/json; charset=utf-8');
    $token = $_SESSION['auth_access_token'] ?? null;
    $term = trim(mainModel::limpiar_cadena($_GET['q'] ?? ''));

    if (!$token || mb_strlen($term) < 2) {
        echo json_encode([]);
        exit();
    }

    $resultado = SupabaseApi::getWithToken('teacher', [
        'select' => 'id,name,last_name',
        'state' => 'neq.1',
        'or' => '(name.ilike.*' . $term . '*,last_name.ilike.*' . $term . '*)',
        'order' => 'name.asc',
        'limit' => '10',
    ], $token);

    $profesores = [];
    foreach (($resultado['data'] ?? []) as $profe) {
        $profesores[] = [
            'id' => $profe['id'],
            'name' => $profe['name'],
            'last_name' => $profe['last_name'],
        ];
    }

    echo json_encode($profesores, JSON_UNESCAPED_UNICODE);
    exit();
}



if (isset($_GET['accion']) && $_GET['accion'] === 'buscar_periodo') {
    header('Content-Type: application/json; charset=utf-8');
    $token = $_SESSION['auth_access_token'] ?? null;
    $term  = trim(mainModel::limpiar_cadena($_GET['q'] ?? ''));

    if (!$token || mb_strlen($term) < 2) {
        echo json_encode([]);
        exit();
    }

    $resultado = SupabaseApi::getWithToken('period', [
        'select'  => 'id,name,type,current_state',
        'state'   => 'eq.1',
        'name'    => 'ilike.*' . $term . '*',
        'order'   => 'start_date.desc',
        'limit'   => '15',
    ], $token);

    $out = [];
    foreach (($resultado['data'] ?? []) as $p) {
        $out[] = [
            'id_periodo'     => $p['id'],
            'nombre_periodo' => $p['name'] . (!empty($p['type']) ? ' (' . $p['type'] . ')' : ''),
            'estado'         => $p['current_state'],
        ];
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit();
}


if (isset($_GET['accion']) && $_GET['accion'] === 'buscar_curso_para_reportes') {
    header('Content-Type: application/json; charset=utf-8');
    $token = $_SESSION['auth_access_token'] ?? null;
    $term = trim(mainModel::limpiar_cadena($_GET['q'] ?? ''));

    if (!$token || mb_strlen($term) < 2) {
        echo json_encode([]);
        exit();
    }

    $resultado = SupabaseApi::getWithToken('course', [
        'select' => 'id,name,type',
        'name' => 'ilike.*' . $term . '*',
        'order' => 'name.asc',
        'limit' => '10',
    ], $token);

    $cursos = [];
    foreach (($resultado['data'] ?? []) as $curso) {
        $cursos[] = [
            'id_curso' => $curso['id'],
            'nombre_curso' => $curso['name'],
            'tipo_curso' => $curso['type'],
        ];
    }

    echo json_encode($cursos, JSON_UNESCAPED_UNICODE);
    exit();
}

if (isset($_GET['accion']) && $_GET['accion'] === 'secciones_por_curso_periodo') {
    header('Content-Type: application/json; charset=utf-8');
    $token    = $_SESSION['auth_access_token'] ?? null;
    $courseId = (int) ($_GET['course_id'] ?? 0);
    $periodId = (int) ($_GET['period_id'] ?? 0);

    if (!$token || $courseId <= 0 || $periodId <= 0) {
        echo json_encode([]);
        exit();
    }

    // 1) Buscar el period_course (unique: course+period, state=1)
    $pc = SupabaseApi::getWithToken('period_course', [
        'select'    => 'id',
        'course_id' => 'eq.' . $courseId,
        'period_id' => 'eq.' . $periodId,
        'state'     => 'eq.1',
        'limit'     => '1',
    ], $token);

    if (empty($pc['data'][0]['id'])) {
        echo json_encode([]);
        exit();
    }

    $pcId = (int) $pc['data'][0]['id'];

    // 2) Traer secciones activas de ese period_course
    $secs = SupabaseApi::getWithToken('section', [
        'select'           => 'id,name,section_day',
        'period_course_id' => 'eq.' . $pcId,
        'state'            => 'eq.1',
        'order'            => 'name.asc',
    ], $token);

    $out = [];
    foreach (($secs['data'] ?? []) as $s) {
        $out[] = [
            'id_seccion'     => $s['id'],
            'nombre_seccion' => $s['name'] . (!empty($s['section_day']) ? ' (' . $s['section_day'] . ')' : ''),
        ];
    }
    echo json_encode($out, JSON_UNESCAPED_UNICODE);
    exit();
}





?>