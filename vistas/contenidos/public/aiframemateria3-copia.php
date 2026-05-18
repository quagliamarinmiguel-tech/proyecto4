



<?php
require_once __DIR__ . "/../../../modelos/mainModel.php";
date_default_timezone_set("America/Caracas");

$cursosPorPagina = 20;
$idCurso = isset($_GET['id']) ? intval($_GET['id']) : null;
$whichadd = isset($_GET['valor']) ? intval($_GET['valor']) : null;
$ip = $_SERVER['REMOTE_ADDR'];

$tipoBanner = match($whichadd) {
    1 => "Anuncio",
    2 => "Informativo",
    default => "Otro"
};

try {
    $pdo = mainModel::conectar();

    if ($idCurso !== null) {

        // 🔐 Registrar IP
        $idIP = mainModel::registrar_ip_visitante($pdo, $ip);

        // 🧭 Registrar interacción
        mainModel::registrar_interaccion_banner($pdo, $idCurso, $idIP, $tipoBanner);

        // 📍 Calcular la página para redirigir
        $sqlPosicion = "
SELECT COUNT(DISTINCT c.id_curso) AS total
FROM curso_publicidad m
LEFT JOIN curso c ON m.id_curso = c.id_curso
LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk 
LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor 
WHERE cv.estado_curso_vigente = 1
    AND p.estado_profesor = 1
    AND c.estado_curso = 1
    AND s.archivar_seccion = 1
    AND m.estado_curso_publicidad = 1
    AND s.nombre_seccion = 'A'
    AND (
        cv.id_cohorte_fk IN (SELECT id_cohorte FROM cohorte WHERE estado_cohorte IN ('Vigente', 'Próximo'))
        OR cv.id_curso_vigente IN (SELECT id_curso_vigente_fk FROM periodo_intensivo WHERE estado_periodo IN ('Vigente', 'Próximo'))
        OR c.id_curso IN (
            SELECT cvp.id_curso_fk 
            FROM curso_vigente cvp 
            INNER JOIN cohorte cop ON cvp.id_cohorte_fk = cop.id_cohorte 
            WHERE cop.estado_cohorte = 'Próximo'
        )
        OR c.id_curso IN (
            SELECT pcv.id_curso_fk 
            FROM periodo_intensivo pcp 
            INNER JOIN curso_vigente pcv ON pcp.id_curso_vigente_fk = pcv.id_curso_vigente 
            WHERE pcp.estado_periodo = 'Próximo'
        )
    )

 AND m.id_materia <= :idCurso  -- ← AGREGAR ESTA LÍNEA
        ";
        $stmt = $pdo->prepare($sqlPosicion);
        $stmt->execute([':idCurso' => $idCurso]);
        $posicion = intval($stmt->fetchColumn());
        $paginaCurso = ceil($posicion / $cursosPorPagina);

        // 🚀 Redirigir al curso correspondiente
        header("Location: /index.php?views=cursos&pagina=$paginaCurso&cursoSeleccionado=$idCurso#$idCurso");
        exit;
    } else {
        echo "<div style='padding:20px;background:#fff3cd;border:1px solid #ffc107;color:#856404;font-family:sans-serif;'>
                <strong>Curso no encontrado:</strong> el parámetro 'id' es nulo o inválido.
              </div>";
    }

} catch (PDOException $e) {
    error_log("⛔ Error en aiframemateria: " . $e->getMessage());
    echo "<div style='padding:20px;background:#ffdddd;border:1px solid #cc0000;color:#990000;font-family:sans-serif;'>
            <strong>Error al guardar la interacción:</strong> por favor inténtalo de nuevo más tarde.
          </div>";
    exit;
}
?>
<!--
            SELECT COUNT(*) AS posicion
            FROM curso_publicidad m
            LEFT JOIN curso c ON m.id_curso = c.id_curso
            LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk
            LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
            WHERE co.estado_cohorte = 'Vigente'
            AND cv.estado_curso_vigente = 1
            AND c.estado_curso = 1
            AND m.estado_curso_publicidad = 1
            AND p.estado_profesor = 1
            AND s.archivar_seccion = 1
            AND s.nombre_seccion = 'A'
            AND m.id_materia <= :idCurso


    $sqlTotal = "
SELECT COUNT(DISTINCT c.id_curso) AS total
FROM curso_publicidad m
LEFT JOIN curso c ON m.id_curso = c.id_curso
LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
LEFT JOIN seccion s ON cv.id_curso_vigente = s.id_curso_vigente_fk 
LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor 
WHERE     cv.estado_curso_vigente = 1
    AND p.estado_profesor = 1
    AND c.estado_curso = 1
    AND s.archivar_seccion = 1
    AND m.estado_curso_publicidad = 1
    AND s.nombre_seccion = 'A'
    AND (
        cv.id_cohorte_fk IN (SELECT id_cohorte FROM cohorte WHERE estado_cohorte IN ('Vigente', 'Próximo'))
        OR cv.id_curso_vigente IN (SELECT id_curso_vigente_fk FROM periodo_intensivo WHERE estado_periodo IN ('Vigente', 'Próximo'))
        OR c.id_curso IN (
            SELECT cvp.id_curso_fk 
            FROM curso_vigente cvp 
            INNER JOIN cohorte cop ON cvp.id_cohorte_fk = cop.id_cohorte 
            WHERE cop.estado_cohorte = 'Próximo'
        )
        OR c.id_curso IN (
            SELECT pcv.id_curso_fk 
            FROM periodo_intensivo pcp 
            INNER JOIN curso_vigente pcv ON pcp.id_curso_vigente_fk = pcv.id_curso_vigente 
            WHERE pcp.estado_periodo = 'Próximo'
        )
    )
";





-->







<!--php
//include("connection.php"); // Conectar a la base de datos

// Configurar zona horaria correcta para Venezuela
date_default_timezone_set("America/Caracas");

// Número de cursos por página
$cursosPorPagina = 20;

$idCurso = isset($_GET['id']) ? intval($_GET['id']) : null;

// REALIZAR LA PAGINACIÓN NORMALMENTE
if ($idCurso !== null) {
    $consulta = "SELECT COUNT(*) AS posicion FROM curso_publicidad WHERE id_materia <= ?";
    $stmt = $connecting->prepare($consulta);
    $stmt->bind_param("i", $idCurso);
    $stmt->execute();
    $stmt->bind_result($posicion);
    $stmt->fetch();
    $stmt->close();

    $paginaCurso = ceil($posicion / $cursosPorPagina);

    header("Location: ../index.php?views=cursos&pagina=$paginaCurso&cursoSeleccionado=$idCurso#$idCurso");


    exit;
}

$connecting->close();
?-->


<!--php este funciona pero no guarda interaccion banner
require_once __DIR__ . "/../../../modelos/mainModel.php"; // Ajustar según ubicación
date_default_timezone_set("America/Caracas");

$cursosPorPagina = 20;
$idCurso = isset($_GET['id']) ? intval($_GET['id']) : null;

try {
    $pdo = mainModel::conectar();

    if ($idCurso !== null) {
        // Consulta para encontrar cuántos cursos anteriores hay con cohorte vigente
        $sql = "
            SELECT COUNT(*) AS posicion
            FROM curso_publicidad m
            LEFT JOIN curso c ON m.id_curso = c.id_curso
            LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            WHERE co.estado_cohorte = 'Vigente'
            AND m.id_materia <= :idCurso
        ";

        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':idCurso', $idCurso, PDO::PARAM_INT);
        $stmt->execute();

        $posicion = intval($stmt->fetchColumn());
        $paginaCurso = ceil($posicion / $cursosPorPagina);

        // Redirigir al curso paginado y resaltado
        //header("http://www.cecilioceproca.wuaze.com/index.php?views=cursos&pagina=$paginaCurso&cursoSeleccionado=$idCurso#$idCurso");
        header("Location: http://www.cecilioceproca.wuaze.com/index.php?views=cursos&pagina=$paginaCurso&cursoSeleccionado=$idCurso#$idCurso");
        exit;
    }
    else {
    echo "Parámetro 'id' no definido o nulo.";
    }

} catch (PDOException $e) {
    error_log("⛔ Error en aiframemateria3: " . $e->getMessage());
    // Podés mostrar un error suave aquí si querés
}

?-->









<!--consulta vieja q no excluye archivados
            SELECT COUNT(*) AS posicion
            FROM curso_publicidad m
            LEFT JOIN curso c ON m.id_curso = c.id_curso
            LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
            LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            WHERE co.estado_cohorte = 'Vigente'
            AND cv.estado_curso_vigente = 1
            AND c.estado_curso = 1
            AND m.estado_curso_publicidad = 1
            AND p.estado_profesor = 1
            AND s.archivar_seccion = 1
            AND m.id_materia <= :idCurso

-->
<!--?php registra banner mas no pagina
require_once __DIR__ . "/../../../modelos/mainModel.php";
date_default_timezone_set("America/Caracas");

$idMateria = isset($_GET['id']) ? intval($_GET['id']) : null;
$whichadd = isset($_GET['valor']) ? intval($_GET['valor']) : null;
$ip = $_SERVER['REMOTE_ADDR'];

$tipoBanner = match($whichadd) {
    1 => "Anuncio",
    2 => "Informativo",
    default => "Otro"
};

try {
    $pdo = mainModel::conectar();
    $idIP = mainModel::registrar_ip_visitante($pdo, $ip);
    mainModel::registrar_interaccion_banner($pdo, $idMateria, $idIP, $tipoBanner);

    $sqlPosicion = "
        SELECT COUNT(*) AS posicion
        FROM curso_publicidad m
        LEFT JOIN curso c ON m.id_curso = c.id_curso
        LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
        LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
        WHERE co.estado_cohorte = 'Vigente'
        AND m.id_materia <= :idMateria
    ";
    $stmt = $pdo->prepare($sqlPosicion);
    $stmt->execute([':idMateria' => $idMateria]);
    $posicion = intval($stmt->fetchColumn());
    $paginaCurso = ceil($posicion / 20);

    //header("Location: ../index.php?views=cursos&pagina=$paginaCurso&cursoSeleccionado=$idMateria#$idMateria");
    header("Location: http://www.cecilioceproca.wuaze.com/index.php?views=cursos&pagina=$paginaCurso&cursoSeleccionado=$idCurso#$idCurso");

    exit;

} catch (PDOException $e) {
    error_log("⛔ Error en aiframemateria: " . $e->getMessage());
    echo "<div style='padding:20px;background:#ffdddd;border:1px solid #cc0000;color:#990000;font-family:sans-serif;'>
            <strong>Error al guardar la interacción:</strong> por favor inténtalo de nuevo más tarde.
          </div>";
    exit;
}

?-->






<!--php
require_once __DIR__ . "/../../../vistas/inc/utilidadesPublicas.php";
date_default_timezone_set("America/Caracas");

// Datos desde GET
$idMateria = isset($_GET['id']) ? intval($_GET['id']) : null;
$idBanner = isset($_GET['banner']) ? intval($_GET['banner']) : null;
$whichadd = isset($_GET['valor']) ? intval($_GET['valor']) : null;
$ip = $_SERVER['REMOTE_ADDR'];
$fechaClick = date("Y-m-d H:i:s");
$tipoBanner = match($whichadd) {
    1 => "Anuncio",
    2 => "Informativo",
    default => "Otro"
};

try {
    $pdo = conectarBase();

    // 🔐 Registrar IP si no existe
    $idIP = registrar_ip_visitante($pdo, $ip);

    // 🧩 Buscar el curso vinculado a la materia
    $sqlCurso = "SELECT id_curso FROM curso_publicidad WHERE id_materia = :idMateria";
    $stmtCurso = $pdo->prepare($sqlCurso);
    $stmtCurso->execute([':idMateria' => $idMateria]);
    $idCurso = $stmtCurso->fetchColumn();

    if ($idCurso) {
        // 📝 Guardar interacción
        $sql = "
            INSERT INTO interaccion_banner (fecha_hora, id_curso_fk, id_ip_fk, tipo_banner)
            VALUES (:fecha, :idCurso, :idIP, :tipo)
        ";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fecha' => $fechaClick,
            ':idCurso' => $idCurso,
            ':idIP' => $idIP,
            ':tipo' => $tipoBanner
        ]);
    }

    // 📍 Redirigir a cursos-view como antes
    $sqlPosicion = "
        SELECT COUNT(*) AS posicion
        FROM curso_publicidad m
        LEFT JOIN curso c ON m.id_curso = c.id_curso
        LEFT JOIN curso_vigente cv ON c.id_curso = cv.id_curso_fk
        LEFT JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
        WHERE co.estado_cohorte = 'Vigente'
        AND m.id_materia <= :idMateria
    ";
    $stmt = $pdo->prepare($sqlPosicion);
    $stmt->execute([':idMateria' => $idMateria]);
    $posicion = intval($stmt->fetchColumn());
    $paginaCurso = ceil($posicion / 20);

    header("Location: ../index.php?views=cursos&pagina=$paginaCurso&cursoSeleccionado=$idMateria#$idMateria");
    exit;

} catch (PDOException $e) {
    error_log("⛔ Error en aiframemateria: " . $e->getMessage());
    echo "<div style='padding:20px;background:#ffdddd;border:1px solid #cc0000;color:#990000;font-family:sans-serif;'>
            <strong>Error al guardar la interacción:</strong> por favor inténtalo de nuevo más tarde.
          </div>";
    exit;}
?-->































<!--?php
include("connection.php");

// Número de cursos por página
$cursosPorPagina = 20;

$idCurso = isset($_GET['id']) ? intval($_GET['id']) : null;
if ($idCurso !== null) {
    $consulta = "SELECT COUNT(*) AS posicion FROM curso_publicidad WHERE id_materia <= $idCurso";
    $resultado = mysqli_query($connecting, $consulta);
    if ($resultado) {
        $fila = mysqli_fetch_assoc($resultado);
        $posicion = $fila['posicion'];

        $paginaCurso = ceil($posicion / $cursosPorPagina);
        //c

        
  //      header("Location: ceprocacursos.php?pagina=$paginaCurso#$idCurso");
        header("Location: ceprocacursos.php?pagina=$paginaCurso&cursoSeleccionado=$idCurso#$idCurso");

        exit;
    } else {
        echo "Error in the query execution";
    }
}

mysqli_close($connecting);
?-->
 
<!--
/*
$idBanner = isset($_GET['banner']) ? intval($_GET['banner']) : null;
$whichadd= isset($_GET['valor']) ? intval($_GET['valor']) : null;
$ip = $_SERVER['REMOTE_ADDR']; // Obtener IP del usuario

if ($whichadd==1) {$tipoBanner = "Curso publicidad";}
elseif ($whichadd==2) {$tipoBanner = "Anuncio";}

$fechaClick = date("Y-m-d H:i:s"); // Capturar fecha y hora

// rEGISTRAR CLICK EN LA BASE DE DATOS
if ($idBanner !== null) {
    $stmt = $connecting->prepare("INSERT INTO interaccion_banner (fecha_hora, id_banner, ip_visitante, tipo_banner) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("siss", $fechaClick, $idBanner, $ip, $tipoBanner);
    $stmt->execute();
    $stmt->close();
}
*/-->





