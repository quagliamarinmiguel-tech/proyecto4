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












































/*-------------------------------------------------------------------------------------------------------------------------*/
//$pdo = mainModel::conectar();

/*
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_profesor') {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT id_profesor, nombre_profesor, apellido_profesor 
        FROM profesor 
        WHERE (nombre_profesor LIKE :term OR apellido_profesor LIKE :term)
        AND estado_profesor = 1
        ORDER BY nombre_profesor, apellido_profesor
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $profesores = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($profesores);
    exit();
}

// Buscar curso activo por nombre
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso') {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT id_curso, nombre_curso, tipo_curso
        FROM curso
        WHERE estado_curso = 1
          AND nombre_curso LIKE :term
        ORDER BY nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}*/


// Buscar cursos activos que NO tengan publicidad
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso_publicidad') {
 //   $pdo = mainModel::conectar();
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT c.id_curso, c.nombre_curso, c.tipo_curso
        FROM curso c
        WHERE c.estado_curso = 1
          AND c.nombre_curso LIKE :term
          AND c.id_curso NOT IN (
              SELECT cp.id_curso FROM curso_publicidad cp
          )
        ORDER BY c.nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}


// Buscar cursos activos que SÍ tienen publicidad
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso_publicitado') {
 //   $pdo = mainModel::conectar();
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT c.id_curso, c.nombre_curso, c.tipo_curso
        FROM curso c
        WHERE c.estado_curso = 1
          AND c.nombre_curso LIKE :term
          AND c.id_curso IN (
              SELECT cp.id_curso FROM curso_publicidad cp
          )
        ORDER BY c.nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}

if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso_archivado') {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT c.id_curso, c.nombre_curso, c.tipo_curso
        FROM curso c
        WHERE c.estado_curso = 0
          AND c.nombre_curso LIKE :term
          AND c.id_curso IN (
              SELECT cp.id_curso FROM curso_publicidad cp
          )
        ORDER BY c.nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}





if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_cualquier_profesor') {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT id_profesor, nombre_profesor, apellido_profesor 
        FROM profesor 
        WHERE nombre_profesor LIKE :term OR apellido_profesor LIKE :term
        ORDER BY nombre_profesor, apellido_profesor
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $profesores = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($profesores);
    exit();
}


if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_cualquier_curso') {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT id_curso, nombre_curso, tipo_curso
        FROM curso
        WHERE nombre_curso LIKE :term
        ORDER BY nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}








//buscar estudiantes de solicitud list
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_estudiante_solicitud') {
    //$pdo = mainModel::conectar();
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT id_solicitud, nombre_estudiante, apellido_estudiante
        FROM solicitud_inscripcion
        WHERE (nombre_estudiante LIKE :term OR apellido_estudiante LIKE :term)
        ORDER BY nombre_estudiante ASC, apellido_estudiante ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $estudiantes = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($estudiantes);
    exit();
}

//consulta larga de cursos de cursop disponibles pa buscar en solicitud list
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso_solicitud') {
    $pdo = mainModel::conectar();
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT DISTINCT c.id_curso, c.nombre_curso, c.tipo_curso
        FROM curso_publicidad m
        LEFT JOIN curso c ON m.id_curso = c.id_curso
        LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
        LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
        LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk AND s.nombre_seccion = 'A' AND s.archivar_seccion = 1
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor AND p.estado_profesor = 1
        WHERE co.estado_cohorte = 'Vigente'
          AND cv.estado_curso_vigente = 1
          AND p.estado_profesor = 1
          AND c.estado_curso = 1
          AND s.archivar_seccion = 1
          AND m.estado_curso_publicidad = 1
          AND s.nombre_seccion = 'A'
          AND c.nombre_curso LIKE :term
        ORDER BY c.nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}




//consulta para course-vigente 1 que trae los cursos disponibles para una cohorte
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso_por_cohorte' && isset($_GET['id_cohorte'])) {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');
    $idCohorte = mainModel::limpiar_cadena($_GET['id_cohorte']);

    // 1. Obtener tipo_cohorte
    $stmtTipo = $pdo->prepare("SELECT tipo_cohorte FROM cohorte WHERE id_cohorte = :id");
    $stmtTipo->execute([':id' => $idCohorte]);
    $tipoCohorte = $stmtTipo->fetchColumn();

    if (!$tipoCohorte) {
        echo json_encode([]); exit();
    }

    // 2. Buscar cursos activos del mismo tipo que NO estén en curso_vigente con esa cohorte
    $consulta = $pdo->prepare("
        SELECT c.id_curso, c.nombre_curso, c.tipo_curso
        FROM curso c
        WHERE c.estado_curso = 1
          AND c.tipo_curso = :tipo
          AND c.nombre_curso LIKE :term
          AND c.id_curso NOT IN (
              SELECT cv.id_curso_fk
              FROM curso_vigente cv
              WHERE cv.id_cohorte_fk = :idCohorte
              AND cv.estado_curso_vigente = 1
          )
        ORDER BY c.nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([
        ':tipo' => $tipoCohorte,
        ':term' => "%$term%",
        ':idCohorte' => $idCohorte
    ]);

    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);
    echo json_encode($cursos);
    exit();
}






// Buscar cursos activos tipo Intensivo para course-vigente-int y cv-list-int
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso_intensivo') {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT id_curso, nombre_curso, tipo_curso
        FROM curso
        WHERE estado_curso = 1
          AND tipo_curso = 'Intensivo'
          AND nombre_curso LIKE :term
        ORDER BY nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}


// Buscar cursos activos tipo Intensivo para cv-list-int archivado
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso_int_archivado') {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT id_curso, nombre_curso, tipo_curso
        FROM curso
        WHERE tipo_curso = 'Intensivo'
          AND nombre_curso LIKE :term
        ORDER BY nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}


// Buscar cursos activos tipo cohorte para seccion archived
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_curso_co_archivado') {
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT id_curso, nombre_curso, tipo_curso
        FROM curso
        WHERE tipo_curso != 'Intensivo'
          AND nombre_curso LIKE :term
        ORDER BY nombre_curso ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $cursos = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($cursos);
    exit();
}



// Buscar estudiantes activos que están en usuario_estudiante
if (isset($_GET['accion']) && $_GET['accion'] == 'buscar_estudiante_usuario') {
  //  $pdo = mainModel::conectar();
    $term = mainModel::limpiar_cadena($_GET['q'] ?? '');

    $consulta = $pdo->prepare("
        SELECT e.id_estudiante, e.nombre, e.apellido
        FROM estudiante e
        INNER JOIN usuario_estudiante ue ON ue.id_estudiante_fk = e.id_estudiante
        WHERE e.estado_estudiante = 1
          AND (e.nombre LIKE :term OR e.apellido LIKE :term)
        ORDER BY e.nombre ASC
        LIMIT 10
    ");

    $consulta->execute([':term' => "%$term%"]);
    $estudiantes = $consulta->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($estudiantes);
    exit();
}



?>