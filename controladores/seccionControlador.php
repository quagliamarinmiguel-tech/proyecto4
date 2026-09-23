<?php

if($peticionAjax){
    require_once "../modelos/seccionModelo.php";
}else{
    require_once "./modelos/seccionModelo.php";
}

//agregar_seccion_controlador()
//obtener_puntajes_evaluacion_controlador
    //type
    class seccionControlador extends seccionModelo{

//archivada
        //paginador_seccion_archivadas_controlador22
//$dia

public function listar_cohortes_disponibles_controlador(){
    $consulta = mainModel::conectar()->prepare("
        SELECT 
            id_cohorte,
            numero_cohorte,
            anio_cohorte,
            tipo_cohorte
        FROM cohorte
        WHERE estado_cohorte IN ('Vigente', 'Próximo') 
          AND archivar_cohorte = 1
        ORDER BY anio_cohorte DESC, numero_cohorte ASC
    ");
    $consulta->execute();
    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}

        
        
//para el conteo de evaluaciones >6 de seccion consult
        public function contar_evaluaciones_controlador($id_seccion) {
            $id_seccion = mainModel::limpiar_cadena($id_seccion);
            return seccionModelo::contar_evaluaciones_modelo($id_seccion);
        }


//de seccion-list


//modificar profe en seccion consult
public function modificar_profesor_seccion_controlador() {
    if (!isset($_POST['id_seccion_up']) || !isset($_POST['profesor_up']) || !isset($_POST['seccion_dia_up'])) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Datos incompletos",
            "Texto" => "Faltan datos para modificar el profesor",
            "Tipo" => "error"
        ]);
        exit();
    }

    $idSeccion = mainModel::decryption($_POST['id_seccion_up']);
    $idSec = $_POST['id_seccion_up'];
    $idProfesor = mainModel::decryption($_POST['profesor_up']);
    //$idSeccion = mainModel::limpiar_cadena($idSec);
    $idProfesor = mainModel::limpiar_cadena($_POST['profesor_up']);
    $dia = mainModel::limpiar_cadena($_POST['seccion_dia_up']); 
    $idSeccion = $idSec;
    
    if (!ctype_digit((string)$idSeccion) || !ctype_digit((string)$idProfesor)) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Datos inválidos",
            "Texto" => "Los identificadores no son válidos", 
            "Tipo" => "error"
        ]);
        exit();
    }

    // Verificar existencia de sección 
    $checkSeccion = mainModel::ejecutar_consulta_simple("SELECT id_seccion FROM seccion WHERE id_seccion = '$idSeccion'");
    if ($checkSeccion->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Sección no encontrada",
            "Texto" => "La sección no existe en el sistema",
            "Tipo" => "error"
        ]);
        exit();
    }

    // Verificar existencia y estado del profesor
    $checkProfesor = mainModel::ejecutar_consulta_simple("SELECT estado_profesor FROM profesor WHERE id_profesor = '$idProfesor'");
    if ($checkProfesor->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Profesor no encontrado",
            "Texto" => "El profesor seleccionado no existe",
            "Tipo" => "error"
        ]);
        exit();
    }

    $estado = $checkProfesor->fetch(PDO::FETCH_ASSOC)['estado_profesor'];
    if ((int)$estado !== 1) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Profesor inactivo",
            "Texto" => "No se puede asignar un profesor que está inactivo",
            "Tipo" => "error"
        ]);
        exit();
    }

    // Ejecutar actualización desde el modelo
    //require_once "../modelos/seccionModelo.php";
    //$seccion = new seccionModelo();
    //$actualizar = $seccion->actualizar_profesor_seccion_modelo($idSeccion, $idProfesor);

$pdo = mainModel::conectar();

    // Actualizar profesor
    $stmtUpdate = $pdo->prepare("UPDATE seccion SET ci_profesor = :idProf, dia = :dia WHERE id_seccion = :idSec");
    $stmtUpdate->execute([
        ':idProf' => $idProfesor,
        ':idSec' => $idSeccion,
        ':dia' => $dia
    ]);


    if ($stmtUpdate->rowCount() > 0) {
        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Profesor actualizado",
            "Texto" => "El profesor de la sección fue modificado correctamente",
            "Tipo" => "success"
        ]);
    } else {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Sin cambios",
            "Texto" => "No se realizaron modificaciones en la sección (s:$idSeccion p:$idProfesor)",
            "Tipo" => "info"
        ]);
    }

}







public function archivar_seccion_controlador() {
    // Desencriptar y limpiar
    $id = mainModel::decryption($_POST['id_seccion_archivar']);
    $id2 = $_POST['id_seccion_archivar'];
    $id = $id2;

    // Verificar existencia de la sección
    $check_seccion = mainModel::ejecutar_consulta_simple("SELECT id_seccion FROM seccion WHERE id_seccion = '$id'");
    if ($check_seccion->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error inesperado",
            "Texto" => "La sección que intenta archivar no existe en el sistema $id - $id2",
            "Tipo" => "error"
        ]);
        exit();
    }
	
    $consultaCV = mainModel::ejecutar_consulta_simple("SELECT id_curso_vigente_fk FROM seccion WHERE id_seccion = '$id'");
	$idCursoVigente = $consultaCV->fetchColumn();
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

        // Archivar sección
        $ps_up_seccion = $pdo->prepare("UPDATE seccion SET archivar_seccion = 0 WHERE id_seccion = :id");
        $ps_up_seccion->execute([':id' => $id]);

        // Archivar evaluaciones
        $ps_get_evals = $pdo->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $pdo->prepare("UPDATE evaluacion SET archivar_evaluacion = 0 WHERE id_evaluacion = :idEval");

        // Archivar puntajes
        $ps_up_puntaje = $pdo->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 0 WHERE id_evaluacion = :idEval");

        // Actualizar observaciones en inscripciones
        $ps_up_insc = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Sección archivada')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%Sección archivada%'
        ");

        $ps_get_evals->execute([':idSec' => $id]);
        while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
            $idEval = (int)$eval['id_evaluacion'];
            $ps_up_eval->execute([':idEval' => $idEval]);
            $ps_up_puntaje->execute([':idEval' => $idEval]);
        }

        $ps_up_insc->execute([':idSec' => $id]);

        $pdo->commit();
//            "URL" => SERVERURL . "index.php?views=" . $carpeta . "/seccion-list"

        echo json_encode([
            "Alerta" => "redireccionar",
            "Titulo" => "Sección archivada",
            "Texto" => "La sección y sus registros relacionados han sido archivados exitosamente",
            "Tipo" => "success",
            "URL" => SERVERURL . "index.php?views=" . $carpeta . "/seccion-archived2&curso_vigente=" . $idCursoVigente
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al archivar sección",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}

//$a2

public function restaurar_seccion_controlador() {
    if (!isset($_POST['id_seccion_restore'])) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Error","Texto"=>"Falta el ID de la sección","Tipo"=>"error"]); exit;
    }

    //$id = mainModel::decryption($_POST['id_seccion_restore']);
    $id_raw = $_POST['id_seccion_restore'] ?? '';
    $id = trim($id_raw);
    
	$a2 = $_POST['a2'] ?? $_GET['a2'] ?? '';
    
    $cargo = $_SESSION['cargo_spm'] ?? 1;
$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
    
    //$id = mainModel::limpiar_cadena($id);
    //error_log(">>«". $id ."»");       // para ver si hay espacios
    //error_log("ctype_digit? ". (ctype_digit((string)$id) ? 'sí' : 'no'));
    header('Content-Type: application/json; charset=utf-8');

    if (!ctype_digit((string)$id) || (int)$id <= 0) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Entrada inválida","Texto"=>"ID inválido","Tipo"=>"error"]); exit;
    }

    $pdo = mainModel::conectar();

    // Verificar existencia y obtener curso vigente y profesor
    $stmt = $pdo->prepare("SELECT id_seccion, id_curso_vigente_fk, ci_profesor FROM seccion WHERE id_seccion = :id AND archivar_seccion = 0");
    $stmt->execute([':id' => $id]);
    if ($stmt->rowCount() <= 0) {
        echo json_encode(["Alerta"=>"simple","Titulo"=>"No existe","Texto"=>"La sección no está archivada o no existe","Tipo"=>"error"]); exit;
    }

    $sec = $stmt->fetch(PDO::FETCH_ASSOC);
    $idCursoVigente = (int)$sec['id_curso_vigente_fk'];
    $idProfesor = (int)$sec['ci_profesor'];

    // Verificar curso vigente activo
    $stmtCurso = $pdo->prepare("SELECT estado_curso_vigente FROM curso_vigente WHERE id_curso_vigente = :id");
    $stmtCurso->execute([':id' => $idCursoVigente]);
    $cursoActivo = $stmtCurso->fetchColumn() == 1;

    // Verificar profesor activo
    $stmtProf = $pdo->prepare("SELECT estado_profesor FROM profesor WHERE id_profesor = :id");
    $stmtProf->execute([':id' => $idProfesor]);
    $profActivo = $stmtProf->fetchColumn() == 1;

    if (! $cursoActivo || ! $profActivo) {
        $omitted = [];
        if (! $cursoActivo) $omitted[] = "curso vigente";
        if (! $profActivo) $omitted[] = "profesor";
        echo json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"Restauración omitida",
            "Texto"=>"No se pudo restaurar la sección porque ".implode(" y ", $omitted)." está inactivo",
            "Tipo"=>"warning"
        ]);
        return;
    }

    try {
        $pdo->beginTransaction();

        // Restaurar sección
        $ps_up_seccion = $pdo->prepare("UPDATE seccion SET archivar_seccion = 1 WHERE id_seccion = :id");
        $ps_up_seccion->execute([':id' => $id]);

        // Restaurar evaluaciones
        $ps_get_evals = $pdo->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $pdo->prepare("UPDATE evaluacion SET archivar_evaluacion = 1 WHERE id_evaluacion = :idEval");
        $ps_up_puntaje = $pdo->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 1 WHERE id_evaluacion = :idEval");

        $ps_get_evals->execute([':idSec' => $id]);
        $evals = $ps_get_evals->fetchAll(PDO::FETCH_ASSOC);

        $restored = ['seccion'=>1, 'evaluaciones'=>0, 'puntajes'=>0];
        $omitted = ['puntajes'=>0];
        $examples = [];

        foreach ($evals as $eval) {
            $idEval = (int)$eval['id_evaluacion'];
            $ps_up_eval->execute([':idEval' => $idEval]);
            $restored['evaluaciones'] += 1;

            // Verificar inscripción activa
            $ps_get_insc = $pdo->prepare("SELECT id_inscripcion, archivada FROM inscripcion WHERE id_inscripcion = (SELECT id_inscripcion FROM puntaje_estudiante WHERE id_evaluacion = :idEval LIMIT 1)");
            $ps_get_insc->execute([':idEval' => $idEval]);
            $insc = $ps_get_insc->fetch(PDO::FETCH_ASSOC);

            if (!$insc || (int)$insc['archivada'] !== 1) {
                $omitted['puntajes'] += 1;
                if (count($examples) < 10) $examples[] = $idEval;
                continue;
            }

            $ps_up_puntaje->execute([':idEval' => $idEval]);
            $restored['puntajes'] += $ps_up_puntaje->rowCount() ?: 1;
        }

        // Actualizar observaciones en inscripciones
        $ps_up_obs = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | Sección restaurada')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%Sección restaurada%'
        ");
        $ps_up_obs->execute([':idSec' => $id]);

        $pdo->commit();
        
        
                // Recalcular nota final de la sección restaurada
		require_once "evaluacionControlador.php";
        $evalController = new evaluacionControlador();
		$evalController->actualizar_nota_final_seccion($id);
        
        
        $texto = "Sección restaurada correctamente.";
        $tipo = "success";
        if ($omitted['puntajes'] > 0) {
            $texto .= " Se omitieron {$omitted['puntajes']} puntaje(s) por inscripciones inactivas.";
            $tipo = "warning";
        }

        
        $redir = $carpeta . "/seccion-archived2&curso_vigente=" . $idCursoVigente;
       // $redir = ($a2 === '1') ?  $carpeta . "/seccion-archived" : $carpeta . "/seccion-list";
               echo json_encode([
            "Alerta" => "redireccionar",
            "Titulo" => "Sección restaurada",
            "Texto" => $texto,
            "Tipo" => $tipo,
            "URL" => SERVERURL . "index.php?views=" . $redir
        ]); 
        

        return;



    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode(["Alerta"=>"simple","Titulo"=>"Error","Texto"=>"No se pudo completar: ".$e->getMessage(),"Tipo"=>"error"]);
    }
}

//no probe lo de next page pero por lo menos lo que antes tenia aun funciona

    /*
            SELECT SQL_CALC_FOUND_ROWS
          cv.id_curso_vigente,
          c.nombre_curso,
          c.id_curso,
          ch.numero_cohorte,
          ch.anio_cohorte,
          ch.estado_cohorte,
          ch.tipo_cohorte,
          ch.id_cohorte,
          COUNT(DISTINCT i.id_inscripcion) AS total_inscripciones,
          COUNT(DISTINCT s.id_seccion) AS total_secciones
        FROM curso_vigente cv
        INNER JOIN cohorte ch 
          ON cv.id_cohorte_fk = ch.id_cohorte
        INNER JOIN curso c 
          ON cv.id_curso_fk = c.id_curso
        LEFT JOIN seccion s 
          ON s.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s.archivar_seccion = 1
        LEFT JOIN profesor p 
          ON s.ci_profesor = p.id_profesor 
          AND p.estado_profesor = 1
        LEFT JOIN inscripcion i 
          ON i.id_curso_vigente_fk = cv.id_curso_vigente 
          AND i.archivada = 1
        WHERE $where 
          AND c.estado_curso = 1
          AND cv.estado_curso_vigente = 1
        GROUP BY 
          cv.id_curso_vigente, 
          c.nombre_curso, 
          ch.numero_cohorte, 
          ch.anio_cohorte, 
          ch.estado_cohorte, 
          ch.tipo_cohorte

        $orden_sql
        LIMIT {$inicio}, {$registros}
    */
    
    
    
    
    
        
           public function paginador_secciones_controlador($pagina, $registros) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<script>console.log('[v0] Iniciando paginador_secciones_controlador');</script>";
    
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina    = ($pagina > 0) ? $pagina : 1;
    $inicio    = (($pagina * $registros) - $registros);

               $cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];
$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
               $vistaCompleta = urlencode($carpeta) . '/seccion-list';
               
    echo "<script>console.log('[v0] Pagina: {$pagina}, Registros: {$registros}, Inicio: {$inicio}');</script>";

    // Filtros
    $filtro_id           = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre       = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_tipo         = mainModel::limpiar_cadena($_GET['filtro_tipo'] ?? '');
    $filtro_estado       = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? '');
    $filtro_anio         = (int) ($_GET['filtro_anio'] ?? 0);
    $filtro_profesor     = mainModel::limpiar_cadena($_GET['filtro_profesor'] ?? '');
    $filtro_dia          = mainModel::limpiar_cadena($_GET['filtro_dia'] ?? '');
    $filtro_id_seccion   = mainModel::limpiar_cadena($_GET['filtro_id_seccion'] ?? '');
    $filtro_id_cohorte   = mainModel::limpiar_cadena($_GET['filtro_id_cohorte'] ?? '');
    $orden               = $_GET['orden'] ?? 'reciente';

    echo "<script>console.log('[v0] Filtros aplicados:', " . json_encode([
        'id' => $filtro_id,
        'nombre' => $filtro_nombre,
        'tipo' => $filtro_tipo,
        'estado' => $filtro_estado,
        'anio' => $filtro_anio,
        'orden' => $orden
    ]) . ");</script>";

    try {
        $conexion = mainModel::conectar();
        echo "<script>console.log('[v0] Conexión a BD exitosa');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('[v0] Error de conexión: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $where = "1=1"; // Condición base neutral
    if ($filtro_id != "")           $where .= " AND cv.id_curso_vigente = '$filtro_id'";
    if ($filtro_nombre != "")       $where .= " AND c.id_curso = '$filtro_nombre'";
    if ($filtro_tipo != "")         $where .= " AND ch.tipo_cohorte = '$filtro_tipo'";
    if ($filtro_estado != "")       $where .= " AND ch.estado_cohorte = '$filtro_estado'";
    if ($filtro_anio >= 25 && $filtro_anio <= 50) $where .= " AND ch.anio_cohorte = '$filtro_anio'";
    if ($filtro_profesor != "")     $where .= " AND s.ci_profesor = '$filtro_profesor'";
    if ($filtro_dia != "")     $where .= " AND s.dia = '$filtro_dia'";
    if ($filtro_id_seccion != "")   $where .= " AND s.id_seccion = '$filtro_id_seccion'";
    if ($filtro_id_cohorte != "")   $where .= " AND ch.id_cohorte = '$filtro_id_cohorte'";
        $filtro_arc = $_GET['filtro_arc'] ?? 'no';
     if ($filtro_arc === 'si') {
    $where .= " AND EXISTS (
        SELECT 1 FROM seccion s3
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
          AND s3.archivar_seccion = 0
    )";

    } elseif ($filtro_arc === 'no') {

    }   
        
        

    echo "<script>console.log('[v0] WHERE construido: " . addslashes($where) . "');</script>";

    // Ordenamiento
    $orden_sql = match($orden) {
        "alfabetico" => "ORDER BY c.nombre_curso ASC",
        "inscritos"  => "ORDER BY total_inscripciones DESC",
        default      => "ORDER BY ch.anio_cohorte DESC, ch.numero_cohorte ASC"
    };

    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
      cv.id_curso_vigente,
      c.nombre_curso,
      c.id_curso,
      ch.numero_cohorte,
      ch.anio_cohorte,
      ch.estado_cohorte,
      ch.tipo_cohorte,
      ch.id_cohorte,

      (
        SELECT COUNT(*) 
        FROM inscripcion i 
        INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion 
        WHERE i.id_curso_vigente_fk = cv.id_curso_vigente 
          AND i.archivada = 1 
          AND s2.archivar_seccion = 1
      ) AS total_inscripciones,

      COUNT(DISTINCT s.id_seccion) AS total_secciones,

      (
        SELECT COUNT(*) 
        FROM seccion s3 
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s3.archivar_seccion = 0
      ) AS total_secciones_archivadas

    FROM curso_vigente cv
    INNER JOIN cohorte ch 
      ON cv.id_cohorte_fk = ch.id_cohorte
    INNER JOIN curso c 
      ON cv.id_curso_fk = c.id_curso

    LEFT JOIN seccion s 
      ON s.id_curso_vigente_fk = cv.id_curso_vigente 
      AND s.archivar_seccion = 1

    LEFT JOIN profesor p 
      ON s.ci_profesor = p.id_profesor 
      AND p.estado_profesor = 1

    WHERE ch.archivar_cohorte = 1 
      AND c.estado_curso = 1
      AND cv.estado_curso_vigente = 1 
      AND $where
      
    GROUP BY 
      cv.id_curso_vigente, 
      c.nombre_curso, 
      ch.numero_cohorte, 
      ch.anio_cohorte, 
      ch.estado_cohorte, 
      ch.tipo_cohorte,
      ch.id_cohorte,
      c.id_curso

    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";

    echo "<script>console.log('[v0] Ejecutando consulta SQL');</script>";
    
    try {
        $datos = $conexion->query($consulta);
        
        if ($datos === false) {
            $errorInfo = $conexion->errorInfo();
            echo "<script>console.error('[v0] Error SQL:', " . json_encode($errorInfo) . ");</script>";
            return '<div class="alert alert-danger">Error en la consulta SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
        
        $datos = $datos->fetchAll();
        echo "<script>console.log('[v0] Consulta exitosa. Registros encontrados: " . count($datos) . "');</script>";
        
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        echo "<script>console.log('[v0] Total de registros: {$total}');</script>";
        
    } catch (Exception $e) {
        echo "<script>console.error('[v0] Excepción en consulta: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error al ejecutar la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $Npaginas = ceil($total / $registros);

   
  /*             // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-bordered table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID vigencia-Curso</th>
                    <th>Curso</th>
                    <th>Cohorte</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Inscritos</th>
                    <th>Secciones</th>
                    <th>Secciones archivadas</th>
                    <th>Crear sección</th>                    
                    <th>Archivar</th>
                </tr>
            </thead>
            <tbody>';
*/
               $cargo = $_SESSION['cargo_spm'] ?? 0;

$tabla = '<div class="table-responsive">
    <table class="table table-dark table-bordered table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>ID Periodo-Curso</th>
                <th>Curso</th>
                <th>Cohorte</th>
                <th>Tipo</th>
                <th>Estado</th>';

if ($cargo != 5) {
    $tabla .= '<th>Inscritos</th>';
}

$tabla .= '<th>Secciones</th>';

if ($cargo == 1 || $cargo == 4) {
    $tabla .= '
        <th>Secciones archivadas</th>
        <th>Crear sección</th>
        <th>Archivar</th>';
}

$tabla .= '
            </tr>
        </thead>
        <tbody>';
               
               
               
               
               
               
    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);
            $secCount = (int) $row['total_secciones'];

            if ($secCount > 0) {
                $boton = "<button type='button' class='btn btn-sm btn-secondary m-1 ver-secciones'
                              data-id='{$idCV}' data-nombre='{$nombreCurso}'>
                              Ver secciones ({$secCount})
                          </button>";
            } else {
                $boton = "<span class='text-muted'>Sin secciones</span>";
            }

            $secArchivadas = (int) $row['total_secciones_archivadas'];
                if ($secArchivadas > 0) {
                    //   '<a href="' . SERVERURL . 'index.php?views=seccion-archived2&curso_vigente=' . urlencode($idCV) . '&nombre_curso=' . urlencode($nombreCurso) . '" class="btn btn-sm btn-warning m-1">Ver archivadas (' . $secArchivadas . ')</a>';
                    $botonArchivadas = '<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-archived2&curso_vigente=' . urlencode($idCV) . '&nombre_curso=' . urlencode($nombreCurso) . '" class="btn btn-sm btn-warning m-1">Ver archivadas (' . $secArchivadas . ')</a>';

                     
                } else {
                    $botonArchivadas = "<span class='text-muted'>Sin archivadas</span>";
                }
            
            
            
            
            
            /*
            $tabla .= "<tr class='text-center'>
                <td>{$idCV}</td>
                <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                <td>{$row['numero_cohorte']}.{$row['anio_cohorte']} / id:{$row['id_cohorte']}</td>
                <td>{$row['tipo_cohorte']}</td>
                <td>{$row['estado_cohorte']}</td>
                <td>{$row['total_inscripciones']}</td>
                <td>{$boton}</td>
                <td>{$botonArchivadas}</td>

                <td>

                    <a href='" . SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-new&curso_vigente={$idCV}' class='btn btn-sm btn-success'>
                        <i class='fas fa-plus'></i> Crear
                    </a>

                </td>
                <td>
                    <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='delete' data-entity='curso_vigente' autocomplete='off'>
                        <input type='hidden' name='curso_vigente_id_archive' value='" . mainModel::encryption($idCV) . "'>
                        <button type='submit' class='btn btn-warning btn-sm'>
                            <i class='fas fa-archive'></i>
                        </button>
                    </form>
                </td>
            </tr>";
           */ 
            
            $tabla .= "<tr class='text-center'>
    <td>{$idCV}</td>
    <td>{$nombreCurso} / id:{$row['id_curso']}</td>
    <td>{$row['numero_cohorte']}.{$row['anio_cohorte']} / id:{$row['id_cohorte']}</td>
    <td>{$row['tipo_cohorte']}</td>
    <td>{$row['estado_cohorte']}</td>";

if ($cargo != 5) {
    $tabla .= "<td>{$row['total_inscripciones']}</td>";
}

$tabla .= "<td>{$boton}</td>";

if ($cargo == 1 || $cargo == 4) {
    $tabla .= "
    <td>{$botonArchivadas}</td>
    <td>

        <a href='" . SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-new&curso_vigente={$idCV}' class='btn btn-sm btn-success'>
        <i class='fas fa-plus'></i> Crear
        </a>
    </td>
    <td>
        <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='delete' data-entity='curso_vigente' autocomplete='off'>
            <input type='hidden' name='curso_vigente_id_archive' value='" . mainModel::encryption($idCV) . "'>
            <button type='submit' class='btn btn-warning btn-sm'>
                <i class='fas fa-archive'></i>
            </button>
        </form>
    </td>";
}

$tabla .= "</tr>";
            
            
            
            
            
            
            
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="10">No hay cursos vigentes registrados que coincidan con los filtros.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    $tabla .= '
    <div id="modalSecciones" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
        <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
            <h5 id="tituloSecciones" class="text-white text-center mb-3">Secciones del curso</h5>
            <div id="contenedorSecciones" class="d-flex flex-wrap justify-content-center"></div>
            <button onclick="document.getElementById(\'modalSecciones\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>



    <script>
    console.log("[v0] Agregando event listeners para modales");
    
    document.querySelectorAll(".ver-secciones").forEach(btn => {
        btn.addEventListener("click", function() {
            const modal = document.getElementById("modalSecciones");
            const contenedor = document.getElementById("contenedorSecciones");
            const titulo = document.getElementById("tituloSecciones");
            const carpeta = "' . urlencode($carpeta) . '";
            const cursoId = this.dataset.id;
            const nombreCurso = this.dataset.nombre;
            
            console.log("[v0] Abriendo modal de secciones para CV:", cursoId);
            
            titulo.textContent = "Secciones de " + nombreCurso;
            contenedor.innerHTML = "<span class=\'text-light\'>Cargando secciones...</span>";
            
            const body = new URLSearchParams({
                curso_vigente_id: cursoId
            }).toString();
            
            fetch("' . SERVERURL . 'ajax/seccionAjax.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body
            })
            .then(res => res.json())
            .then(data => {
                console.log("[v0] Datos recibidos:", data);
                contenedor.innerHTML = "";
                
                if (!data || data.length === 0) {
                    contenedor.innerHTML = "<span class=\'badge bg-danger\'>No hay secciones registradas</span>";
                    return;
                }
                
                data.forEach(sec => {
                    const nombre = sec.nombre_profesor || "";
                    const apellido = sec.apellido_profesor || "";
                    const profText = (nombre || apellido) ? nombre + " " + apellido : "<em>Sin profesor asignado</em>";
                    
                    const card = document.createElement("div");
                    card.className = "card bg-secondary text-white m-2 p-2";
                    card.style.width = "160px";
                    card.innerHTML = `
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-1">Sección ${sec.nombre_seccion}</h6>
                            <p class="card-text text-center mb-1">ID: ${sec.id_seccion}</p>
                            <p class="card-text text-center mb-1">Día: ${sec.dia}</p>
                            <p class="card-text text-center mb-1">Prof: ${profText}</p>
                            <p class="card-text text-center mb-1">Inscritos: ${sec.inscritos || 0}</p>
                            <p class="card-text text-center mb-1">Evaluaciones: ${sec.evaluaciones || 0}</p>
	
	                        <a href="' . SERVERURL . 'index.php?views=${carpeta}/seccion-consult&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
                           
                           
                      </div>
                    `;
                    contenedor.appendChild(card);
                });
            })
            .catch(err => {
                console.error("[v0] Error cargando secciones:", err);
                contenedor.innerHTML = "<span class=\'badge bg-danger\'>Error al cargar secciones</span>";
            });
            
            modal.style.display = "block";
        });
    });
    
   
   
    console.log("[v0] Event listeners agregados correctamente");
    </script>';
    
    // Paginación
                                           //<a href="' . SERVERURL . 'index.php?views=seccion-consult&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>

    if ($total > 0 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-center">Mostrando registros del <strong>' . $reg_inicio . '</strong> al <strong>' . $reg_final . '</strong> de un total de <strong>' . $total . '</strong></p>';
        
        $queryParams = http_build_query([
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_tipo' => $filtro_tipo,
            'filtro_estado' => $filtro_estado,
            'filtro_anio' => $filtro_anio,
            'filtro_profesor' => $filtro_profesor,
            'filtro_dia' => $filtro_dia,
            'filtro_id_seccion' => $filtro_id_seccion,
            'filtro_id_cohorte' => $filtro_id_cohorte,
            'filtro_arc' => $filtro_arc, 
            'origen' => $_GET['origen'] ?? '',
            'orden' => $orden
        ]);
        
       // $urlCompleta = SERVERURL . "index.php?views=seccion-list&" . $queryParams . "&page=";
		$urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }
    
    echo "<script>console.log('[v0] Retornando tabla HTML completa');</script>";
    return $tabla;
}
        
        
        
        
        
        
        
        
        
        
        
    /*
    public function paginador_secciones_controladorX($pagina, $registros) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<script>console.log('[v0] Iniciando paginador_secciones_controlador');</script>";
    
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina    = ($pagina > 0) ? $pagina : 1;
    $inicio    = (($pagina * $registros) - $registros);

    echo "<script>console.log('[v0] Pagina: {$pagina}, Registros: {$registros}, Inicio: {$inicio}');</script>";

    // Filtros
    $filtro_id           = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre       = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_tipo         = mainModel::limpiar_cadena($_GET['filtro_tipo'] ?? '');
    $filtro_estado       = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? '');
    $filtro_anio         = (int) ($_GET['filtro_anio'] ?? 0);
    $filtro_profesor     = mainModel::limpiar_cadena($_GET['filtro_profesor'] ?? '');
    $filtro_id_seccion   = mainModel::limpiar_cadena($_GET['filtro_id_seccion'] ?? '');
    $filtro_id_cohorte   = mainModel::limpiar_cadena($_GET['filtro_id_cohorte'] ?? '');
    $orden               = $_GET['orden'] ?? 'reciente';

    echo "<script>console.log('[v0] Filtros aplicados:', " . json_encode([
        'id' => $filtro_id,
        'nombre' => $filtro_nombre,
        'tipo' => $filtro_tipo,
        'estado' => $filtro_estado,
        'anio' => $filtro_anio,
        'orden' => $orden
    ]) . ");</script>";

    try {
        $conexion = mainModel::conectar();
        echo "<script>console.log('[v0] Conexión a BD exitosa');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('[v0] Error de conexión: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $where = "1=1"; // Condición base neutral
    if ($filtro_id != "")           $where .= " AND cv.id_curso_vigente = '$filtro_id'";
    if ($filtro_nombre != "")       $where .= " AND c.id_curso = '$filtro_nombre'";
    if ($filtro_tipo != "")         $where .= " AND ch.tipo_cohorte = '$filtro_tipo'";
    if ($filtro_estado != "")       $where .= " AND ch.estado_cohorte = '$filtro_estado'";
    if ($filtro_anio >= 25 && $filtro_anio <= 50) $where .= " AND ch.anio_cohorte = '$filtro_anio'";
    if ($filtro_profesor != "")     $where .= " AND s.ci_profesor = '$filtro_profesor'";
    if ($filtro_id_seccion != "")   $where .= " AND s.id_seccion = '$filtro_id_seccion'";
    if ($filtro_id_cohorte != "")   $where .= " AND ch.id_cohorte = '$filtro_id_cohorte'";
        $filtro_arc = $_GET['filtro_arc'] ?? 'no';
     if ($filtro_arc === 'si') {
    $where .= " AND EXISTS (
        SELECT 1 FROM seccion s3
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
          AND s3.archivar_seccion = 0
    )";

    } elseif ($filtro_arc === 'no') {

    }   
        
        

    echo "<script>console.log('[v0] WHERE construido: " . addslashes($where) . "');</script>";

    // Ordenamiento
    $orden_sql = match($orden) {
        "alfabetico" => "ORDER BY c.nombre_curso ASC",
        "inscritos"  => "ORDER BY total_inscripciones DESC",
        default      => "ORDER BY ch.anio_cohorte DESC, ch.numero_cohorte ASC"
    };

    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
      cv.id_curso_vigente,
      c.nombre_curso,
      c.id_curso,
      ch.numero_cohorte,
      ch.anio_cohorte,
      ch.estado_cohorte,
      ch.tipo_cohorte,
      ch.id_cohorte,

      (
        SELECT COUNT(*) 
        FROM inscripcion i 
        INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion 
        WHERE i.id_curso_vigente_fk = cv.id_curso_vigente 
          AND i.archivada = 1 
          AND s2.archivar_seccion = 1
      ) AS total_inscripciones,

      COUNT(DISTINCT s.id_seccion) AS total_secciones,

      (
        SELECT COUNT(*) 
        FROM seccion s3 
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s3.archivar_seccion = 0
      ) AS total_secciones_archivadas

    FROM curso_vigente cv
    INNER JOIN cohorte ch 
      ON cv.id_cohorte_fk = ch.id_cohorte
    INNER JOIN curso c 
      ON cv.id_curso_fk = c.id_curso

    LEFT JOIN seccion s 
      ON s.id_curso_vigente_fk = cv.id_curso_vigente 
      AND s.archivar_seccion = 1

    LEFT JOIN profesor p 
      ON s.ci_profesor = p.id_profesor 
      AND p.estado_profesor = 1

    WHERE ch.archivar_cohorte = 1 
      AND c.estado_curso = 1
      AND cv.estado_curso_vigente = 1 
      AND $where
      
    GROUP BY 
      cv.id_curso_vigente, 
      c.nombre_curso, 
      ch.numero_cohorte, 
      ch.anio_cohorte, 
      ch.estado_cohorte, 
      ch.tipo_cohorte,
      ch.id_cohorte,
      c.id_curso

    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";

    echo "<script>console.log('[v0] Ejecutando consulta SQL');</script>";
    
    try {
        $datos = $conexion->query($consulta);
        
        if ($datos === false) {
            $errorInfo = $conexion->errorInfo();
            echo "<script>console.error('[v0] Error SQL:', " . json_encode($errorInfo) . ");</script>";
            return '<div class="alert alert-danger">Error en la consulta SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
        
        $datos = $datos->fetchAll();
        echo "<script>console.log('[v0] Consulta exitosa. Registros encontrados: " . count($datos) . "');</script>";
        
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        echo "<script>console.log('[v0] Total de registros: {$total}');</script>";
        
    } catch (Exception $e) {
        echo "<script>console.error('[v0] Excepción en consulta: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error al ejecutar la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $Npaginas = ceil($total / $registros);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-bordered table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID vigencia-Curso</th>
                    <th>Curso</th>
                    <th>Cohorte</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Inscritos</th>
                    <th>Secciones</th>
                    <th>Secciones archivadas</th>
                    <th>Crear sección</th>                    
                    <th>Archivar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);
            $secCount = (int) $row['total_secciones'];

            if ($secCount > 0) {
                $boton = "<button type='button' class='btn btn-sm btn-secondary m-1 ver-secciones'
                              data-id='{$idCV}' data-nombre='{$nombreCurso}'>
                              Ver secciones ({$secCount})
                          </button>";
            } else {
                $boton = "<span class='text-muted'>Sin secciones</span>";
            }

            $secArchivadas = (int) $row['total_secciones_archivadas'];
            if ($secArchivadas > 0) {
                $botonArchivadas = "<button type='button' class='btn btn-sm btn-warning m-1 ver-secciones-archivadas'
                                        data-id='{$idCV}' data-nombre='{$nombreCurso}'>
                                        Ver archivadas ({$secArchivadas})
                                    </button>";
            } else {
                $botonArchivadas = "<span class='text-muted'>Sin archivadas</span>";
            }
            
            $tabla .= "<tr class='text-center'>
                <td>{$idCV}</td>
                <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                <td>{$row['numero_cohorte']}.{$row['anio_cohorte']} / id:{$row['id_cohorte']}</td>
                <td>{$row['tipo_cohorte']}</td>
                <td>{$row['estado_cohorte']}</td>
                <td>{$row['total_inscripciones']}</td>
                <td>{$boton}</td>
                <td>{$botonArchivadas}</td>
                <td>
                    <a href='" . SERVERURL . "index.php?views=seccion-new&curso_vigente={$idCV}' class='btn btn-sm btn-success'>
                        <i class='fas fa-plus'></i> Crear
                    </a>
                </td>
                <td>
                    <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='delete' data-entity='curso_vigente' autocomplete='off'>
                        <input type='hidden' name='curso_vigente_id_archive' value='" . mainModel::encryption($idCV) . "'>
                        <button type='submit' class='btn btn-warning btn-sm'>
                            <i class='fas fa-archive'></i>
                        </button>
                    </form>
                </td>
            </tr>";
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="10">No hay cursos vigentes registrados que coincidan con los filtros.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    $tabla .= '
    <div id="modalSecciones" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
        <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
            <h5 id="tituloSecciones" class="text-white text-center mb-3">Secciones del curso</h5>
            <div id="contenedorSecciones" class="d-flex flex-wrap justify-content-center"></div>
            <button onclick="document.getElementById(\'modalSecciones\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <div id="modalSeccionesArchivadas" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
        <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
            <h5 id="tituloSeccionesArchivadas" class="text-white text-center mb-3">Secciones Archivadas</h5>
            <div id="contenedorSeccionesArchivadas" class="d-flex flex-wrap justify-content-center"></div>
            <button onclick="document.getElementById(\'modalSeccionesArchivadas\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
    console.log("[v0] Agregando event listeners para modales");
    
    document.querySelectorAll(".ver-secciones").forEach(btn => {
        btn.addEventListener("click", function() {
            const modal = document.getElementById("modalSecciones");
            const contenedor = document.getElementById("contenedorSecciones");
            const titulo = document.getElementById("tituloSecciones");
            
            const cursoId = this.dataset.id;
            const nombreCurso = this.dataset.nombre;
            
            console.log("[v0] Abriendo modal de secciones para CV:", cursoId);
            
            titulo.textContent = "Secciones de " + nombreCurso;
            contenedor.innerHTML = "<span class=\'text-light\'>Cargando secciones...</span>";
            
            const body = new URLSearchParams({
                curso_vigente_id: cursoId
            }).toString();
            
            fetch("' . SERVERURL . 'ajax/seccionAjax.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body
            })
            .then(res => res.json())
            .then(data => {
                console.log("[v0] Datos recibidos:", data);
                contenedor.innerHTML = "";
                
                if (!data || data.length === 0) {
                    contenedor.innerHTML = "<span class=\'badge bg-danger\'>No hay secciones registradas</span>";
                    return;
                }
                
                data.forEach(sec => {
                    const nombre = sec.nombre_profesor || "";
                    const apellido = sec.apellido_profesor || "";
                    const profText = (nombre || apellido) ? nombre + " " + apellido : "<em>Sin profesor asignado</em>";
                    
                    const card = document.createElement("div");
                    card.className = "card bg-secondary text-white m-2 p-2";
                    card.style.width = "160px";
                    card.innerHTML = `
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-1">Sección ${sec.nombre_seccion}</h6>
                            <p class="card-text text-center mb-1">ID: ${sec.id_seccion}</p>
                            <p class="card-text text-center mb-1">Prof: ${profText}</p>
                            <p class="card-text text-center mb-1">Inscritos: ${sec.inscritos || 0}</p>
                            <p class="card-text text-center mb-1">Evaluaciones: ${sec.evaluaciones || 0}</p>
                           <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-arc-consult&curso_vigente=' . $cursoId . '&seccion=' . urlencode($sec['nombre_seccion']) . '" class="btn btn-sm btn-light w-100 mt-1">Ver</a>

                        </div>
                    `;
                    contenedor.appendChild(card);
                });
            })
            .catch(err => {
                console.error("[v0] Error cargando secciones:", err);
                contenedor.innerHTML = "<span class=\'badge bg-danger\'>Error al cargar secciones</span>";
            });
            
            modal.style.display = "block";
        });
    });
    
    document.querySelectorAll(".ver-secciones-archivadas").forEach(btn => {
        btn.addEventListener("click", function() {
            const modal = document.getElementById("modalSeccionesArchivadas");
            const contenedor = document.getElementById("contenedorSeccionesArchivadas");
            const titulo = document.getElementById("tituloSeccionesArchivadas");
            
            const cursoId = this.dataset.id;
            const nombreCurso = this.dataset.nombre;
            
            console.log("[v0] Abriendo modal de secciones archivadas para CV:", cursoId);
            
            titulo.textContent = "Secciones Archivadas de " + nombreCurso;
            contenedor.innerHTML = "<span class=\'text-light\'>Cargando secciones archivadas...</span>";
            
            const body = new URLSearchParams({
                curso_vigente_id: cursoId,
                solo_archivadas: "1"
            }).toString();
            
            fetch("' . SERVERURL . 'ajax/seccionAjax.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body
            })
            .then(res => res.json())
            .then(data => {
                console.log("[v0] Datos archivadas recibidos:", data);
                contenedor.innerHTML = "";
                
                if (!data || data.length === 0) {
                    contenedor.innerHTML = "<span class=\'badge bg-warning\'>No hay secciones archivadas</span>";
                    return;
                }
                
                data.forEach(sec => {
                    const nombre = sec.nombre_profesor || "";
                    const apellido = sec.apellido_profesor || "";
                    const profText = (nombre || apellido) ? nombre + " " + apellido : "<em>Sin profesor asignado</em>";
                    
                    const card = document.createElement("div");
                    card.className = "card bg-warning text-dark m-2 p-2";
                    card.style.width = "160px";
                    card.innerHTML = `
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-1">Sección ${sec.nombre_seccion}</h6>
                            <p class="card-text text-center mb-1">ID: ${sec.id_seccion}</p>
                            <p class="card-text text-center mb-1">Prof: ${profText}</p>
                            <p class="card-text text-center mb-1">Inscritos: ${sec.inscritos || 0}</p>
                            <p class="card-text text-center mb-1">Evaluaciones: ${sec.evaluaciones || 0}</p>
                            <a href="' . SERVERURL . 'index.php?views=seccion-arc-consult&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
                        </div>
                    `;
                    contenedor.appendChild(card);
                });
            })
            .catch(err => {
                console.error("[v0] Error cargando secciones archivadas:", err);
                contenedor.innerHTML = "<span class=\'badge bg-danger\'>Error al cargar secciones archivadas</span>";
            });
            
            modal.style.display = "block";
        });
    });
    
    console.log("[v0] Event listeners agregados correctamente");
    </script>';
    //                           <a href="' . SERVERURL . 'index.php?views=seccion-consult&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
    // Paginación
    if ($total > 0 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-center">Mostrando registros del <strong>' . $reg_inicio . '</strong> al <strong>' . $reg_final . '</strong> de un total de <strong>' . $total . '</strong></p>';
        
        $queryParams = http_build_query([
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_tipo' => $filtro_tipo,
            'filtro_estado' => $filtro_estado,
            'filtro_anio' => $filtro_anio,
            'filtro_profesor' => $filtro_profesor,
            'filtro_id_seccion' => $filtro_id_seccion,
            'filtro_id_cohorte' => $filtro_id_cohorte,
            'filtro_arc' => $filtro_arc, 
            'origen' => $_GET['origen'] ?? '',
            'orden' => $orden
        ]);
        
        $urlCompleta = SERVERURL . "index.php?views=seccion-list&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }
    
    echo "<script>console.log('[v0] Retornando tabla HTML completa');</script>";
    return $tabla;
}
*/
    
    // linea 1946                 archivadas: "1"

    
    
    
    
    
    
 
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public function paginador_secciones_controlador2($pagina, $registros) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<script>console.log('[v0] Iniciando paginador_secciones_controlador');</script>";
    
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina    = ($pagina > 0) ? $pagina : 1;
    $inicio    = (($pagina * $registros) - $registros);

        $cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
  $vistaCompleta = urlencode($carpeta) . '/seccion-archived';
      
        
        
    echo "<script>console.log('[v0] Pagina: {$pagina}, Registros: {$registros}, Inicio: {$inicio}');</script>";

    // Filtros
    $filtro_id           = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre       = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_tipo         = mainModel::limpiar_cadena($_GET['filtro_tipo'] ?? '');
    $filtro_estado       = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? '');
    $filtro_anio         = (int) ($_GET['filtro_anio'] ?? 0);
    $filtro_profesor     = mainModel::limpiar_cadena($_GET['filtro_profesor'] ?? '');
    $filtro_dia          = mainModel::limpiar_cadena($_GET['filtro_dia'] ?? '');
    $filtro_id_seccion   = mainModel::limpiar_cadena($_GET['filtro_id_seccion'] ?? '');
    $filtro_id_cohorte   = mainModel::limpiar_cadena($_GET['filtro_id_cohorte'] ?? '');
    $orden               = $_GET['orden'] ?? 'reciente';

    echo "<script>console.log('[v0] Filtros aplicados:', " . json_encode([
        'id' => $filtro_id,
        'nombre' => $filtro_nombre,
        'tipo' => $filtro_tipo,
        'estado' => $filtro_estado,
        'anio' => $filtro_anio,
        'orden' => $orden
    ]) . ");</script>";

    try {
        $conexion = mainModel::conectar();
        echo "<script>console.log('[v0] Conexión a BD exitosa');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('[v0] Error de conexión: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $where = "1=1"; // Condición base neutral
    if ($filtro_id != "")           $where .= " AND cv.id_curso_vigente = '$filtro_id'";
    if ($filtro_nombre != "")       $where .= " AND c.id_curso = '$filtro_nombre'";
    if ($filtro_tipo != "")         $where .= " AND ch.tipo_cohorte = '$filtro_tipo'";
    if ($filtro_estado != "")       $where .= " AND ch.estado_cohorte = '$filtro_estado'";
    if ($filtro_anio >= 25 && $filtro_anio <= 50) $where .= " AND ch.anio_cohorte = '$filtro_anio'";
    if ($filtro_profesor != "")     $where .= " AND s.ci_profesor = '$filtro_profesor'";
    if ($filtro_dia != "")     $where .= " AND s.dia = '$filtro_dia'";
    if ($filtro_id_seccion != "")   $where .= " AND s.id_seccion = '$filtro_id_seccion'";
    if ($filtro_id_cohorte != "")   $where .= " AND ch.id_cohorte = '$filtro_id_cohorte'";

    echo "<script>console.log('[v0] WHERE construido: " . addslashes($where) . "');</script>";

    // Ordenamiento
    $orden_sql = match($orden) {
        "alfabetico" => "ORDER BY c.nombre_curso ASC",
        "inscritos"  => "ORDER BY total_inscripciones DESC",
        default      => "ORDER BY ch.anio_cohorte DESC, ch.numero_cohorte ASC"
    };

    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
      cv.id_curso_vigente,
      c.nombre_curso,
      c.id_curso,
      ch.numero_cohorte,
      ch.anio_cohorte,
      ch.estado_cohorte,
      ch.tipo_cohorte,
      ch.id_cohorte,

      (
        SELECT COUNT(*) 
        FROM inscripcion i 
        INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion 
        WHERE i.id_curso_vigente_fk = cv.id_curso_vigente 
        
          AND s2.archivar_seccion = 0
      ) AS total_inscripciones,

      COUNT(DISTINCT s.id_seccion) AS total_secciones,

      (
        SELECT COUNT(*) 
        FROM seccion s3 
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s3.archivar_seccion = 0
      ) AS total_secciones_archivadas

    FROM curso_vigente cv
    INNER JOIN cohorte ch 
      ON cv.id_cohorte_fk = ch.id_cohorte
    INNER JOIN curso c 
      ON cv.id_curso_fk = c.id_curso

    LEFT JOIN seccion s 
      ON s.id_curso_vigente_fk = cv.id_curso_vigente 
      AND s.archivar_seccion = 0

    LEFT JOIN profesor p 
      ON s.ci_profesor = p.id_profesor 

    WHERE cv.estado_curso_vigente = 0 
      AND $where
      
    GROUP BY 
      cv.id_curso_vigente, 
      c.nombre_curso, 
      ch.numero_cohorte, 
      ch.anio_cohorte, 
      ch.estado_cohorte, 
      ch.tipo_cohorte,
      ch.id_cohorte,
      c.id_curso

    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";

    echo "<script>console.log('[v0] Ejecutando consulta SQL');</script>";
    
    try {
        $datos = $conexion->query($consulta);
        
        if ($datos === false) {
            $errorInfo = $conexion->errorInfo();
            echo "<script>console.error('[v0] Error SQL:', " . json_encode($errorInfo) . ");</script>";
            return '<div class="alert alert-danger">Error en la consulta SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
        
        $datos = $datos->fetchAll();
        echo "<script>console.log('[v0] Consulta exitosa. Registros encontrados: " . count($datos) . "');</script>";
        
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        echo "<script>console.log('[v0] Total de registros: {$total}');</script>";
        
    } catch (Exception $e) {
        echo "<script>console.error('[v0] Excepción en consulta: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error al ejecutar la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $Npaginas = ceil($total / $registros);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-bordered table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID Periodo-Curso</th>
                    <th>Curso</th>
                    <th>Cohorte</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Inscritos</th>
                    <th>Secciones archivadas</th>
                    <th>Archivar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);
            $secCount = (int) $row['total_secciones'];

            $secArchivadas = (int) $row['total_secciones_archivadas'];
            if ($secArchivadas > 0) {
                $botonArchivadas = "<button type='button' class='btn btn-sm btn-warning m-1 ver-secciones-archivadas'
                                        data-id='{$idCV}' data-nombre='{$nombreCurso}'>
                                        Ver archivadas ({$secArchivadas})
                                    </button>";
            } else {
                $botonArchivadas = "<span class='text-muted'>Sin archivadas</span>";
            }
            
            $tabla .= "<tr class='text-center'>
                <td>{$idCV}</td>
                <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                <td>{$row['numero_cohorte']}.{$row['anio_cohorte']} / id:{$row['id_cohorte']}</td>
                <td>{$row['tipo_cohorte']}</td>
                <td>{$row['estado_cohorte']}</td>
                <td>{$row['total_inscripciones']}</td>
                <td>{$botonArchivadas}</td>

           
                   <td>
                        <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='restore' autocomplete='off'>
                            <input type='hidden' name='curso_vigente_id_restore' value='" . mainModel::encryption($idCV) . "'>
                            <button type='submit' class='btn btn-info'>
                                <i class='fas fa-undo'></i> Restaurar
                            </button>
                        </form>
                    </td>
            </tr>";
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="10">No hay cursos vigentes registrados que coincidan con los filtros.</td></tr>';
    }

$tabla .= '</tbody></table></div>';
//href
$tabla .= '
<div id="modalSeccionesArchivadas" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
    <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
        <h5 id="tituloSeccionesArchivadas" class="text-white text-center mb-3">Secciones Archivadas</h5>
        <div id="contenedorSeccionesArchivadas" class="d-flex flex-wrap justify-content-center"></div>
        <button onclick="document.getElementById(\'modalSeccionesArchivadas\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
            <i class="fas fa-times"></i>
        </button>
    </div>
</div>

<script>
console.log("[v0] Agregando event listeners para modal de secciones archivadas");

document.querySelectorAll(".ver-secciones-archivadas").forEach(btn => {
    btn.addEventListener("click", function() {
        const modal = document.getElementById("modalSeccionesArchivadas");
        const contenedor = document.getElementById("contenedorSeccionesArchivadas");
        const titulo = document.getElementById("tituloSeccionesArchivadas");

        const cursoId = this.dataset.id;
        const nombreCurso = this.dataset.nombre;

        console.log("[v0] Abriendo modal de secciones archivadas para CV:", cursoId);

        titulo.textContent = "Secciones Archivadas de " + nombreCurso;
        contenedor.innerHTML = "<span class=\'text-light\'>Cargando secciones archivadas...</span>";

        const body = new URLSearchParams({
            curso_vigente_id: cursoId,
            solo_archivadas: "1"
        }).toString();

        fetch("' . SERVERURL . 'ajax/seccionAjax.php", {
            method: "POST",
            headers: { "Content-Type": "application/x-www-form-urlencoded" },
            body: body
        })
        .then(res => res.json())
        .then(data => {
            console.log("[v0] Datos archivadas recibidos:", data);
            contenedor.innerHTML = "";

            if (!data || data.length === 0) {
                contenedor.innerHTML = "<span class=\'badge bg-warning\'>No hay secciones archivadas</span>";
                return;
            }

            data.forEach(sec => {
                const nombre = sec.nombre_profesor || "";
                const apellido = sec.apellido_profesor || "";
                const profText = (nombre || apellido) ? nombre + " " + apellido : "<em>Sin profesor asignado</em>";

                const card = document.createElement("div");
                card.className = "card bg-warning text-dark m-2 p-2";
                card.style.width = "160px";
                card.innerHTML = `
                    <div class="card-body p-2">
                        <h6 class="card-title text-center mb-1">Sección ${sec.nombre_seccion}</h6>
                        <p class="card-text text-center mb-1">ID: ${sec.id_seccion}</p>
                        <p class="card-text text-center mb-1">Día: ${sec.dia}</p>
                        <p class="card-text text-center mb-1">Prof: ${profText}</p>
                        <p class="card-text text-center mb-1">Inscritos: ${sec.inscritos || 0}</p>
                        <p class="card-text text-center mb-1">Evaluaciones: ${sec.evaluaciones || 0}</p>
                        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-arc-consult&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}&a2=1" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
                    </div>
                `;
                contenedor.appendChild(card);
            });
        })
        .catch(err => {
            console.error("[v0] Error cargando secciones archivadas:", err);
            contenedor.innerHTML = "<span class=\'badge bg-danger\'>Error al cargar secciones archivadas</span>";
        });

        modal.style.display = "block";
    });
});

console.log("[v0] Event listeners para secciones archivadas agregados correctamente");
</script>';
    
    // Paginación
                                //<a href="' . SERVERURL . 'index.php?views=seccion-arc-consult&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}&a2=1" class="btn btn-sm btn-light w-100 mt-1">Ver</a>

    if ($total > 0 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-center">Mostrando registros del <strong>' . $reg_inicio . '</strong> al <strong>' . $reg_final . '</strong> de un total de <strong>' . $total . '</strong></p>';
        
        $queryParams = http_build_query([
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_tipo' => $filtro_tipo,
            'filtro_estado' => $filtro_estado,
            'filtro_anio' => $filtro_anio,
            'filtro_profesor' => $filtro_profesor,
            'filtro_dia' => $filtro_dia,
            'filtro_id_seccion' => $filtro_id_seccion,
            'filtro_id_cohorte' => $filtro_id_cohorte,
            'origen' => $_GET['origen'] ?? '',
            'orden' => $orden
        ]);
        
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        //$urlCompleta = SERVERURL . "index.php?views=seccion-archived&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }
    
    echo "<script>console.log('[v0] Retornando tabla HTML completa');</script>";
    return $tabla;
}
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    
   
                    
                    
                    
                    
                    
                    
                    /*
    
    // este es el mejorcito
    public function paginador_seccion_archivada_controlador($pagina, $registros) {
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina = ($pagina > 0) ? $pagina : 1;
    $inicio = (($pagina * $registros) - $registros);

    $conexion = mainModel::conectar();

    // Filtros
    $filtro_cv      = mainModel::limpiar_cadena($_GET['filtro_cv'] ?? '');
    $filtro_sec     = mainModel::limpiar_cadena($_GET['filtro_sec'] ?? '');
    $filtro_nombre  = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_tipo    = mainModel::limpiar_cadena($_GET['filtro_tipo'] ?? '');
    $filtro_anio    = (int) ($_GET['filtro_anio'] ?? 0);
    $orden          = $_GET['orden'] ?? '';

    // WHERE dinámico
    $where = "s.archivar_seccion = 0";
    if ($filtro_cv != "")        $where .= " AND cv.id_curso_vigente = '$filtro_cv'";
    if ($filtro_sec != "")       $where .= " AND s.id_seccion = '$filtro_sec'";
    if ($filtro_nombre != "")    $where .= " AND c.nombre_curso = '$filtro_nombre'";
    if ($filtro_tipo != "")      $where .= " AND ch.tipo_cohorte = '$filtro_tipo'";
    if ($filtro_anio >= 25 && $filtro_anio <= 50) $where .= " AND ch.anio_cohorte = '$filtro_anio'";

    // Ordenamiento
    switch ($orden) {
        case "alfabetico":
            $orden_sql = "ORDER BY c.nombre_curso ASC";
            break;
        case "fecha":
            $orden_sql = "ORDER BY ch.anio_cohorte DESC, ch.numero_cohorte DESC";
            break;
        default:
            $orden_sql = "ORDER BY ch.anio_cohorte DESC, ch.numero_cohorte ASC";
            break;
    }

    // Consulta principal
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS
            cv.id_curso_vigente,
            c.nombre_curso,
            c.id_curso,
			ch.id_cohorte,
            ch.numero_cohorte,
            ch.anio_cohorte,
            ch.estado_cohorte,
            ch.tipo_cohorte
        FROM curso_vigente cv
        INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente
        WHERE $where
        GROUP BY cv.id_curso_vigente
        $orden_sql
        LIMIT $inicio, $registros
    ";

    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-bordered table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID vigencia-Curso</th>
                    <th>Curso</th>
                    <th>Cohorte</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Secciones archivadas</th>
                </tr>
            </thead>
            <tbody>';

        //                <td>{$nombreCurso}</td>
//                <td>{$row['numero_cohorte']} / {$row['anio_cohorte']}</td>
        
            
    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);

            $boton = "<button type='button' class='btn btn-sm btn-warning m-1 ver-secciones-archivadas'
                        data-id='{$idCV}' data-nombre='{$nombreCurso}'>
                        Ver secciones archivadas
                      </button>";

            $tabla .= "<tr class='text-center'>
                <td>{$idCV}</td>
                <td>{$row['nombre_curso']} / id:{$row['id_curso']}</td>
				<td>{$row['numero_cohorte']} / {$row['anio_cohorte']} / id:{$row['id_cohorte']}</td>
                <td>{$row['tipo_cohorte']}</td>
                <td>{$row['estado_cohorte']}</td>
                <td>{$boton}</td>
            </tr>";
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="6">No hay cursos con secciones archivadas que coincidan con los filtros.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Modal emergente
    $tabla .= '
    <div id="modalSeccionesArchivadas" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
        <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
            <h5 id="tituloSeccionesArchivadas" class="text-white text-center mb-3">Secciones archivadas</h5>
            <div id="contenedorSeccionesArchivadas" class="d-flex flex-wrap justify-content-center"></div>
            <button onclick="document.getElementById(\'modalSeccionesArchivadas\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

<script>
    document.querySelectorAll(".ver-secciones-archivadas").forEach(btn => {
        btn.addEventListener("click", () => {
            const modal = document.getElementById("modalSeccionesArchivadas");
            const contenedor = modal.querySelector("#contenedorSeccionesArchivadas");
            const titulo = modal.querySelector("#tituloSeccionesArchivadas");
            const SERVERURL = "<?php echo SERVERURL; ?>";

            const cursoId = btn.dataset.id;
            const nombreCurso = btn.dataset.nombre;

            titulo.textContent = "Secciones archivadas de " + nombreCurso;
            contenedor.innerHTML = "<span class=\'text-light\'>Cargando secciones...</span>";

            const body = new URLSearchParams({
                curso_vigente_id: cursoId,
                solo_archivadas: "1"
            }).toString();
            fetch("' . SERVERURL . 'ajax/seccionAjax.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body
            })
            .then(res => res.json())
            .then(data => {
                console.log("Respuesta AJAX:", data);
                contenedor.innerHTML = "";
                if (!Array.isArray(data) || data.length === 0) {
                    contenedor.innerHTML = "<span class=\'badge bg-danger\'>No hay secciones archivadas</span>";
                    return;
                }

                data.forEach(sec => {
                    const card = document.createElement("div");
                    card.className = "card bg-dark text-white m-2 p-2";
                    card.style.width = "160px";
                    card.innerHTML = `
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-1">Sección ${sec.nombre_seccion}</h6>
                            <p class="card-text text-center mb-1">ID: ${sec.id_seccion}</p>
                            <p class="card-text text-center mb-1">Prof: ${sec.nombre_profesor} ${sec.apellido_profesor}</p>
                           
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/seccionAjax.php" method="POST" data-form="restore" data-entity="seccion" autocomplete="off">
                                <input type="hidden" name="id_seccion_restore" value="${sec.id_seccion}">
                                <button type="submit" class="btn btn-info btn-sm w-100 mt-1 btn-restore">
                                    <i class="fas fa-undo"></i> Restaurar
                                </button>
                            </form>
                        </div>
                    `;
                    contenedor.appendChild(card);
                });

                // Agregar el event listener para bloquear todos los botones al hacer clic en uno
                agregarManejoFormularios();
            });

            modal.style.display = "block";
        });
    });

    // Función para manejar el envío de formularios
    function agregarManejoFormularios() {
        document.querySelectorAll(\'.FormularioAjax[data-form="restore"]\').forEach(form => {
            form.addEventListener(\'submit\', function(e) {
                // Bloquear TODOS los botones de restaurar
                document.querySelectorAll(\'.btn-restore\').forEach(btn => {
                    btn.disabled = true;
                    btn.innerHTML = \'<i class="fas fa-spinner fa-spin"></i> Procesando...\';
                });
            });
        });
    }
</script>';

    // Paginación
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando cursos ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'filtro_cv'     => $filtro_cv,
            'filtro_sec'    => $filtro_sec,
            'filtro_nombre' => $filtro_nombre,
            'filtro_tipo'   => $filtro_tipo,
            'filtro_anio'   => $filtro_anio,
            'orden'         => $orden
        ]);

        $urlCompleta = SERVERURL . "index.php?views=seccion-archived&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}
    */
    
    
    
    
/*------------------------------------------------------------------------------------------------------------------------*/




//------------------------------------------------------seccion consult
//evaluaciones de la vista de seccion
        public function paginador_evaluaciones_controlador($pagina, $registros, $id_seccion){
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $evaluaciones = seccionModelo::obtener_evaluaciones_modelo($id_seccion, $inicio, $registros)->fetchAll();
        $total = count($evaluaciones);
        $Npaginas = ceil($total / $registros);

$cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";            

            
            /*
                                    <!--th>Archivar</th-->

                                <!--td>
                        <form class='FormularioAjax' action='".SERVERURL."ajax/seccionAjax.php' method='POST' data-form='delete2' data-entity='evaluacion' autocomplete='off'>
                            <input type='hidden' name='evaluacion_id_archivar' value='{$idEnc}'>
                            <button type='submit' class='btn btn-warning btn-sm'>
                                <i class='fas fa-archive'></i>
                            </button>
                        </form>
                    </td-->
            */
            
            
        $tabla = '<div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark">
                    <tr class="text-center roboto-medium">
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Objetivo</th>
                        <th>Porcentaje</th>
                        <th>Fecha</th>
                        <th>Modificar</th>
                        <th>Eliminar</th>
                    </tr>
                </thead>
                <tbody>';
            
            
     
            

        if ($total >= 1 && $pagina <= $Npaginas) {
            foreach ($evaluaciones as $eval) {
                $idEnc = mainModel::encryption($eval['id_evaluacion']);
                $tabla .= "<tr class='text-center'>
                    <td>{$eval['id_evaluacion']}</td>
                    <td>{$eval['tipo_evaluacion']}</td>
                    <td>{$eval['obj']}</td>
                    <td>{$eval['porcentaje']}%</td>
                    <td>".date("d/m/Y", strtotime($eval['fecha_evaluacion']))."</td>
                    <td>
	                    <a href='".SERVERURL."index.php?views=" . urlencode($carpeta) . "/evaluacion-update&evaluacion_id_up={$idEnc}' class='btn btn-success btn-sm'>
                            <i class='fas fa-edit'></i>
                        </a>
                    </td>
                                        <td>
                    <form class='FormularioAjax' action='".SERVERURL."ajax/seccionAjax.php' method='POST' data-form='delete2' data-entity='evaluacion' autocomplete='off'>
                        <input type='hidden' name='evaluacion_id_delete' value='".$idEnc."'>
                        <button type='submit' class='btn btn-danger btn-sm' title='Eliminar evaluación y sus calificaciones'>
                            <i class='fas fa-trash-alt'></i>
                        </button>
                    </form>
                </td>
                </tr>";
            }
        } else {
            $tabla .= "<tr class='text-center'><td colspan='7'>No hay evaluaciones registradas en esta sección.</td></tr>";
        }

        $tabla .= '</tbody></table></div>';

            //                        <a href='".SERVERURL."index.php?views=evaluacion-update&evaluacion_id_up={$idEnc}' class='btn btn-success btn-sm'>
            
            
        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando evaluaciones '.($inicio+1).' al '.($inicio+count($evaluaciones)).'</p>';
           
            
            
            $tabla .= mainModel::paginador_tablas(
                $pagina,
                $Npaginas,
                SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-view&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&eval_page=",
                7
            );
            
         //   $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, SERVERURL."index.php?views=seccion-view&curso_vigente=".$_GET['curso_vigente']."&seccion=".$_GET['seccion']."&eval_page=", 7);
        }

        return $tabla;
    }

    
    
    
    //paginador de estudiantes y puntajes
    
    
    
    
    
    public function paginador_estudiantes_con_puntajes_controlador($pagina, $registros, $id_seccion){
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        
        
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

    // 1. Evaluaciones ordenadas por obj
    $evaluaciones = seccionModelo::obtener_evaluaciones_modelo($id_seccion)->fetchAll(PDO::FETCH_ASSOC);
    usort($evaluaciones, function($a, $b) {
        return version_compare($a['obj'], $b['obj']);
    });

    // 2. Estudiantes ordenados por apellido
    $estudiantes = seccionModelo::obtener_estudiantes_modelo($id_seccion, $inicio, $registros)->fetchAll(PDO::FETCH_ASSOC);

    $tabla = '<div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-dark">
                <tr class="text-center roboto-medium">
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Apellido</th>';

    foreach ($evaluaciones as $eval) {
        $tabla .= "<th>{$eval['tipo_evaluacion']} (Obj {$eval['obj']})</th>";
    }

    $tabla .= "<th>Nota final</th></tr></thead><tbody>";

    foreach ($estudiantes as $est) {
        $tabla .= "<tr class='text-center'>
            <td>{$est['ci_estudiante']}</td>
            <td>{$est['nombre']}</td>
            <td>{$est['apellido']}</td>";

        // 3. Obtener puntajes del estudiante
        $puntajes = seccionModelo::obtener_puntajes_modelo($est['id_inscripcion'])->fetchAll(PDO::FETCH_ASSOC);
        $puntajes_map = [];
        foreach ($puntajes as $p) {
            $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
        }

        $notaFinal = 0;
        $totalPorcentaje = 0;

        foreach ($evaluaciones as $eval) {
            $idEval = $eval['id_evaluacion'];
            $porcentaje = (float)$eval['porcentaje'];
            $totalPorcentaje += $porcentaje;

            $valor = '-';
            if (isset($puntajes_map[$idEval])) {
                $puntaje = $puntajes_map[$idEval];
                $valor = $puntaje;
                $puntaje = ($puntaje === 'NP') ? 0 : floatval($puntaje);
                $notaFinal += $puntaje * $porcentaje / 100;
            }

            $tabla .= "<td>{$valor}</td>";
        }

        if ($totalPorcentaje == 100) {
            $notaRedondeada = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);
            $color = ($notaRedondeada > 10) ? 'text-success' : 'text-danger';
            $tabla .= "<td class='{$color}'><strong>{$notaRedondeada}</strong></td>";
        } else {
            $tabla .= "<td class='text-muted'>-</td>";
        }

        $tabla .= '</tr>';
    }

    $tabla .= '</tbody></table></div>';

    // 4. Paginador visual
    $total = seccionModelo::contar_estudiantes_modelo($id_seccion);
    $Npaginas = ceil($total / $registros);
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando estudiantes '.($inicio+1).' al '.($inicio+count($estudiantes)).'</p>';

        
        $tabla .= mainModel::paginador_tablas(
            $pagina,
            $Npaginas,
            SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-view&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&est_page=",
            7
        );
        
        
        //   $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, SERVERURL."index.php?views=seccion-view&curso_vigente=".$_GET['curso_vigente']."&seccion=".$_GET['seccion']."&est_page=", 7);
    }

    return $tabla;
}
    
    
    
    
   //este era el original bueno
    /*
    
    public function paginador_estudiantes_con_puntajes_controlador($pagina, $registros, $id_seccion){
                $pagina = (int) mainModel::limpiar_cadena($pagina);
                $registros = (int) mainModel::limpiar_cadena($registros);
                $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

                $evaluaciones = seccionModelo::obtener_evaluaciones_sin_limite_modelo($id_seccion)->fetchAll();
                $estudiantes = seccionModelo::obtener_estudiantes_modelo($id_seccion, $inicio, $registros)->fetchAll();

                $tabla = '<div class="table-responsive">
                    <table class="table table-bordered table-sm">
                        <thead class="thead-dark">
                            <tr class="text-center roboto-medium">
                                <th>Cédula</th>
                                <th>Nombre</th>
                                <th>Apellido</th>';

                foreach ($evaluaciones as $eval) {
                    $tabla .= "<th>{$eval['tipo_evaluacion']} ({$eval['porcentaje']}%)</th>";
                }

                $tabla .= '</tr></thead><tbody>';

                foreach ($estudiantes as $est) {
                    $tabla .= "<tr class='text-center'>
                        <td>{$est['ci_estudiante']}</td>
                        <td>{$est['nombre']}</td>
                        <td>{$est['apellido']}</td>";

                    $puntajes = seccionModelo::obtener_puntajes_modelo($est['id_inscripcion'])->fetchAll();
                    $puntajes_map = [];
                    foreach ($puntajes as $p) {
                        $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
                    }

                    foreach ($evaluaciones as $eval) {
                        $valor = isset($puntajes_map[$eval['id_evaluacion']]) ? $puntajes_map[$eval['id_evaluacion']] : '-';
                        $tabla .= "<td>{$valor}</td>";
                    }

                    $tabla .= '</tr>';
                }

                
                $tabla .= '</tbody></table></div>';

                // Paginador visual (opcional)
                $total = count($estudiantes); // Puedes usar COUNT(*) si prefieres precisión
                $Npaginas = ceil($total / $registros);
                if ($total >= 1 && $pagina <= $Npaginas) {
                    $tabla .= '<p class="text-right">Mostrando estudiantes '.($inicio+1).' al '.($inicio+count($estudiantes)).'</p>';
                    $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, SERVERURL."index.php?views=seccion-view&curso_vigente=".$_GET['curso_vigente']."&seccion=".$_GET['seccion']."&est_page=", 7);
                }

                return $tabla;
            }
*/
    
    
 
    
    public function obtener_datos_seccion_controlador_para_ambos_casos($curso_vigente_id, $seccion_letra){
        $datos = seccionModelo::obtener_datos_seccion_para_ambos_casos_modelo($curso_vigente_id, $seccion_letra);
     //   return $datos->fetch();
        return $datos;
    }    


    //datos de la seccion obtener_datos_seccion_controlador_para_ambos_casos
    public function obtener_datos_seccion_controlador($curso_vigente_id, $seccion_letra){
        $datos = seccionModelo::obtener_datos_seccion_modelo($curso_vigente_id, $seccion_letra);
        return $datos->fetch();
    }
    //datos de profesor
    public function obtener_profesores_activos_controlador(){
        $consulta = seccionModelo::obtener_profesores_activos_modelo();
        return $consulta->fetchAll();
    }

//--------------fin seccion consult



//de evaluacion_new
        public function obtener_estudiantes_inscritos_controlador($id_seccion){
            $id_seccion = mainModel::limpiar_cadena($id_seccion);
            $consulta = mainModel::conectar()->prepare("
                SELECT 
                    i.id_inscripcion,
                    e.ci_estudiante,
                    e.nombre,
                    e.apellido
                FROM inscripcion i
                INNER JOIN estudiante e ON i.id_estudiante_fk = e.id_estudiante
                WHERE i.id_seccion_fk = :id
                AND i.archivada = 1
                ORDER BY e.apellido ASC
            ");
            $consulta->bindParam(":id", $id_seccion);
            $consulta->execute();
            return $consulta->fetchAll();
        }


//de seccion new
        public function listar_cursos_vigentes_por_cohorte_ajax($id_cohorte){
            $id_cohorte = mainModel::limpiar_cadena($id_cohorte);
            $consulta = mainModel::conectar()->prepare("
                SELECT 
                    cv.id_curso_vigente,
                    c.nombre_curso,
                    COUNT(s.id_seccion) AS total_secciones
                FROM curso_vigente cv
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                LEFT JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente
                WHERE cv.id_cohorte_fk = :id AND cv.estado_curso_vigente = 1
                GROUP BY cv.id_curso_vigente
                ORDER BY c.nombre_curso ASC
            ");
            $consulta->bindParam(":id", $id_cohorte);
            $consulta->execute();
            return json_encode($consulta->fetchAll(PDO::FETCH_ASSOC));
        }






public function agregar_seccion_controlador(){
    $curso_vigente = mainModel::limpiar_cadena($_POST['id_curso_vigente']);
    $nombre = strtoupper(mainModel::limpiar_cadena($_POST['nombre_seccion']));
    $profesor = mainModel::limpiar_cadena($_POST['id_profesor_fk']);
    $dia = mainModel::limpiar_cadena($_POST['seccion_dia_reg']);
//c
    // Validación de campos obligatorios
    if($curso_vigente == "" || $nombre == "" || $profesor == "" || $dia == ""){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Campos incompletos",
            "Texto" => "Todos los campos son obligatorios",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    // Validar formato de nombre (una letra A-Z)
    if(!preg_match("/^[A-Z]{1}$/", $nombre)){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Formato inválido",
            "Texto" => "El nombre de la sección debe ser una sola letra (A-Z)",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    // Verificar duplicado
    $check = mainModel::ejecutar_consulta_simple("
        SELECT id_seccion FROM seccion 
        WHERE id_curso_vigente_fk = '$curso_vigente' AND nombre_seccion = '$nombre' AND archivar_seccion = 1
    ");
    if($check->rowCount() > 0){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Sección duplicada",
            "Texto" => "Ya existe una sección con ese nombre para el curso seleccionado",
            "Tipo" => "warning"
        ];
        echo json_encode($alerta);
        exit();
    }

    // Datos para el modelo
    $datos = [
        "CursoVigente" => $curso_vigente,
        "Nombre" => $nombre,
        "Profesor" => $profesor,
        "Dia" => $dia
    ];

    $guardar = seccionModelo::agregar_seccion_modelo($datos);

    if($guardar->rowCount() == 1){
        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Sección registrada",
            "Texto" => "La sección fue creada exitosamente",
            "Tipo" => "success"
        ];
    } else {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "No se pudo registrar la sección",
            "Tipo" => "error"
        ];
    }

    echo json_encode($alerta);
}

        

public function obtener_datos_seccion_archivada_controlador_para_ambos_casos($id_curso_vigente, $letra_seccion) {
    $id_curso_vigente = mainModel::limpiar_cadena($id_curso_vigente);
    $letra_seccion = mainModel::limpiar_cadena($letra_seccion);

    $pdo = mainModel::conectar();

    // 🔹 Consulta principal
    $stmt = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.nombre_seccion,
            s.id_curso_vigente_fk AS id_curso_vigente,
            cv.id_cohorte_fk,
            c.nombre_curso,
            p.id_profesor,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :id_cv
          AND s.nombre_seccion = :letra
          AND s.archivar_seccion = 0
        LIMIT 1
    ");
    $stmt->bindValue(":id_cv", $id_curso_vigente, PDO::PARAM_INT);
    $stmt->bindValue(":letra", $letra_seccion, PDO::PARAM_STR);
    $stmt->execute();

    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        return [
            "id_seccion" => 0,
            "nombre_seccion" => $letra_seccion,
            "id_curso_vigente" => $id_curso_vigente,
            "nombre_curso" => "Curso no encontrado",
            "numero_cohorte" => "-",
            "anio_cohorte" => "-",
            "fecha_inicio_int" => null,
            "fecha_fin_int" => null,
            "id_profesor" => null,
            "nombre_profesor" => "Sin",
            "apellido_profesor" => "asignar"
        ];
    }

    // 🔹 Si tiene cohorte, buscar sus datos
    if (!empty($datos['id_cohorte_fk']) && $datos['id_cohorte_fk'] != 0) {
        $sqlCohorte = $pdo->prepare("
            SELECT numero_cohorte, anio_cohorte
            FROM cohorte
            WHERE id_cohorte = :id
            LIMIT 1
        ");
        $sqlCohorte->bindParam(":id", $datos['id_cohorte_fk'], PDO::PARAM_INT);
        $sqlCohorte->execute();
        $cohorte = $sqlCohorte->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = $cohorte['numero_cohorte'] ?? null;
        $datos['anio_cohorte'] = $cohorte['anio_cohorte'] ?? null;
        $datos['fecha_inicio_int'] = null;
        $datos['fecha_fin_int'] = null;
    } else {
        // 🔹 Si no tiene cohorte, buscar periodo intensivo
        $sqlIntensivo = $pdo->prepare("
            SELECT fecha_inicio_int, fecha_fin_int
            FROM periodo_intensivo
            WHERE id_curso_vigente_fk = :id
            LIMIT 1
        ");
        $sqlIntensivo->bindParam(":id", $datos['id_curso_vigente'], PDO::PARAM_INT);
        $sqlIntensivo->execute();
        $intensivo = $sqlIntensivo->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = null;
        $datos['anio_cohorte'] = null;
        $datos['fecha_inicio_int'] = $intensivo['fecha_inicio_int'] ?? null;
        $datos['fecha_fin_int'] = $intensivo['fecha_fin_int'] ?? null;
    }

    return $datos;
}
        
        
        
        
public function obtener_datos_evaluacion_controlador_para_ambos_casos($id_evaluacion){
    $id_evaluacion = mainModel::decryption($id_evaluacion);
    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $pdo = mainModel::conectar();

    // 🔹 Consulta principal
    $consulta = $pdo->prepare("
        SELECT 
            e.id_evaluacion,
            e.tipo_evaluacion AS tipo,
            e.obj,
            e.porcentaje,
            e.fecha_evaluacion AS fecha,
            s.id_seccion,
            s.dia,
            s.nombre_seccion AS seccion,
            cv.id_curso_vigente,
            cv.id_cohorte_fk,
            c.nombre_curso,
            p.nombre_profesor,
            p.apellido_profesor
        FROM evaluacion e
        INNER JOIN seccion s ON e.id_seccion = s.id_seccion
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE e.id_evaluacion = :id
        LIMIT 1
    ");
    $consulta->bindParam(":id", $id_evaluacion, PDO::PARAM_INT);
    $consulta->execute();

    if ($consulta->rowCount() !== 1) {
        return false;
    }

    $datos = $consulta->fetch(PDO::FETCH_ASSOC);

    // 🔹 Si tiene cohorte, buscar sus datos
    if (!empty($datos['id_cohorte_fk']) && $datos['id_cohorte_fk'] != 0) {
        $sqlCohorte = $pdo->prepare("
            SELECT numero_cohorte, anio_cohorte
            FROM cohorte
            WHERE id_cohorte = :id
            LIMIT 1
        ");
        $sqlCohorte->bindParam(":id", $datos['id_cohorte_fk']);
        $sqlCohorte->execute();
        $cohorte = $sqlCohorte->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = $cohorte['numero_cohorte'] ?? null;
        $datos['anio_cohorte'] = $cohorte['anio_cohorte'] ?? null;
        $datos['fecha_inicio_int'] = null;
        $datos['fecha_fin_int'] = null;
    } else {
        // 🔹 Si no tiene cohorte, buscar periodo intensivo
        $sqlIntensivo = $pdo->prepare("
            SELECT fecha_inicio_int, fecha_fin_int
            FROM periodo_intensivo
            WHERE id_curso_vigente_fk = :id
            LIMIT 1
        ");
        $sqlIntensivo->bindParam(":id", $datos['id_curso_vigente']);
        $sqlIntensivo->execute();
        $intensivo = $sqlIntensivo->fetch(PDO::FETCH_ASSOC);

        $datos['numero_cohorte'] = null;
        $datos['anio_cohorte'] = null;
        $datos['fecha_inicio_int'] = $intensivo['fecha_inicio_int'] ?? null;
        $datos['fecha_fin_int'] = $intensivo['fecha_fin_int'] ?? null;
    }

    return $datos;
}        
        
        
        
        
        

//para evaluacion update 
public function obtener_datos_evaluacion_controlador($id_evaluacion){
    $id_evaluacion = mainModel::decryption($id_evaluacion);

    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $conexion = mainModel::conectar();

    $consulta = $conexion->prepare("
        SELECT 
            e.id_evaluacion,
            e.tipo_evaluacion AS tipo,
            e.obj,
            e.porcentaje,
            e.fecha_evaluacion AS fecha,
            s.id_seccion,
            s.nombre_seccion AS seccion,
            cv.id_curso_vigente,
            c.nombre_curso,
            ch.numero_cohorte,
            ch.anio_cohorte,
            p.nombre_profesor,
            p.apellido_profesor
        FROM evaluacion e
        INNER JOIN seccion s ON e.id_seccion = s.id_seccion
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE e.id_evaluacion = :id
        LIMIT 1
    ");

    $consulta->bindParam(":id", $id_evaluacion, PDO::PARAM_INT);
    $consulta->execute();

    if($consulta->rowCount() == 1){
        return $consulta->fetch();
    } else {
        return false;
    }
}

//tambien para evaluacion update
public function obtener_puntajes_evaluacion_controlador($id_evaluacion){
    // Desencriptar y limpiar
    $id_evaluacion = mainModel::decryption($id_evaluacion);
    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $conexion = mainModel::conectar();

$consulta = $conexion->prepare("
    SELECT 
        i.id_inscripcion,
        est.ci_estudiante,
        est.nombre,
        est.apellido,
        COALESCE(pe.puntaje, 'NP') AS puntaje
    FROM inscripcion i
    INNER JOIN estudiante est 
        ON i.id_estudiante_fk = est.id_estudiante
    LEFT JOIN puntaje_estudiante pe 
        ON pe.id_inscripcion = i.id_inscripcion 
        AND pe.id_evaluacion = :id
    WHERE 
        i.archivada = 1
        AND est.estado_estudiante = 1
        AND i.id_seccion_fk = (
            SELECT e.id_seccion
            FROM evaluacion e
            INNER JOIN seccion s ON e.id_seccion = s.id_seccion
            WHERE e.id_evaluacion = :id
              AND e.archivar_evaluacion = 1
              AND s.archivar_seccion = 1
              AND pe.archivar_puntaje = 1
        )
    ORDER BY est.apellido ASC, est.nombre ASC
");





    $consulta->bindParam(":id", $id_evaluacion, PDO::PARAM_INT);
    $consulta->execute();

    return $consulta->fetchAll();
}



public function obtener_puntajes_evaluacion_controlador_coninscritos($id_evaluacion){
    // Desencriptar y limpiar
    $id_evaluacion = mainModel::decryption($id_evaluacion);
    $id_evaluacion = mainModel::limpiar_cadena($id_evaluacion);

    $conexion = mainModel::conectar();

    // Obtener la sección asociada a la evaluación
    $consultaSeccion = $conexion->prepare("
        SELECT id_seccion
        FROM evaluacion
        WHERE id_evaluacion = :id
          AND archivar_evaluacion = 1
    ");
    $consultaSeccion->bindParam(":id", $id_evaluacion);
    $consultaSeccion->execute();
    $id_seccion = $consultaSeccion->fetchColumn();

    if (!$id_seccion) return [];

    // Traer todos los inscritos en la sección, con puntaje si existe
    $consulta = $conexion->prepare("
        SELECT 
            i.id_inscripcion,
            est.ci_estudiante,
            est.nombre,
            est.apellido,
            COALESCE((
                SELECT pe.puntaje
                FROM puntaje_estudiante pe
                WHERE pe.id_inscripcion = i.id_inscripcion
                  AND pe.id_evaluacion = :id
                  AND pe.archivar_puntaje = 1
                LIMIT 1
            ), 0) AS puntaje
        FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        WHERE i.id_seccion_fk = :id_seccion
          AND i.archivada = 1
          AND est.estado_estudiante = 1
        ORDER BY est.apellido ASC, est.nombre ASC
    ");
    $consulta->bindParam(":id", $id_evaluacion);
    $consulta->bindParam(":id_seccion", $id_seccion);
    $consulta->execute();

    return $consulta->fetchAll(PDO::FETCH_ASSOC);
}
        
//obtener_estudiantes_inscritos_controlador

//paginador_estudiantes_con_puntajes_controlador
//agregar_evaluacion_controlador
//archivar_seccion_controlador
//Entrada 
//restaurar

        
        
        
        
        
        
        
        
        
        
        
    
    /*----------------------------------------------------------------
        public function paginador_evaluacion_archivada_controlador($pagina, $registros, $id_seccion) {
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

            
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

        // Consulta directa sin modelo
        $stmt = $pdo->prepare("
            SELECT id_evaluacion, tipo_evaluacion, obj, porcentaje, fecha_evaluacion
            FROM evaluacion
            WHERE id_seccion = :id AND archivar_evaluacion = 0
            ORDER BY fecha_evaluacion ASC
            LIMIT :inicio, :registros
        ");
        $stmt->bindValue(":id", $id_seccion, PDO::PARAM_INT);
        $stmt->bindValue(":inicio", $inicio, PDO::PARAM_INT);
        $stmt->bindValue(":registros", $registros, PDO::PARAM_INT);
        $stmt->execute();
        $evaluaciones = $stmt->fetchAll();

        // Total sin paginación
        $totalStmt = $pdo->prepare("
            SELECT COUNT(*) FROM evaluacion
            WHERE id_seccion = :id AND archivar_evaluacion = 0
        ");
        $totalStmt->bindValue(":id", $id_seccion, PDO::PARAM_INT);
        $totalStmt->execute();
        $total = (int) $totalStmt->fetchColumn();
        $Npaginas = ceil($total / $registros);

        // Tabla HTML
        $tabla = '<div class="table-responsive">
            <table class="table table-bordered table-sm">
                <thead class="thead-dark">
                    <tr class="text-center roboto-medium">
                        <th>ID</th>
                        <th>Tipo</th>
                        <th>Objetivo</th>
                        <th>Porcentaje</th>
                        <th>Fecha</th>
                    </tr>
                </thead>
                <tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            foreach ($evaluaciones as $eval) {
                $idEnc = mainModel::encryption($eval['id_evaluacion']);
                $tabla .= "<tr class='text-center'>
                    <td>{$eval['id_evaluacion']}</td>
                    <td>{$eval['tipo_evaluacion']}</td>
                    <td>{$eval['obj']}</td>
                    <td>{$eval['porcentaje']}%</td>
                    <td>".date("d/m/Y", strtotime($eval['fecha_evaluacion']))."</td>

                </tr>";
            }
        } else {
            $tabla .= "<tr class='text-center'><td colspan='6'>No hay evaluaciones archivadas en esta sección.</td></tr>";
        }

        $tabla .= '</tbody></table></div>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando evaluaciones '.($inicio+1).' al '.($inicio+count($evaluaciones)).'</p>';
            
            
            $tabla .= mainModel::paginador_tablas(
                $pagina,
                $Npaginas,
                SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-arc-consult&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&eval_page=",
                7
            );
            
            
           // $tabla .= mainModel::paginador_tablas(
           //     $pagina,
         //       $Npaginas,
             //   SERVERURL."index.php?views=seccion-arc-consult&curso_vigente=".$_GET['curso_vigente']."&seccion=".$_GET['seccion']."&eval_page=",
           //     7
         //   );
        }

        return $tabla;
    }
    */
    
        
        
        
        
public function paginador_evaluacion_archivada_controlador($pagina, $registros, $id_seccion) {
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

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

    // Consulta directa sin modelo
    $stmt = $pdo->prepare("
        SELECT id_evaluacion, tipo_evaluacion, obj, porcentaje, fecha_evaluacion
        FROM evaluacion
        WHERE id_seccion = :id AND archivar_evaluacion = 0
        ORDER BY fecha_evaluacion ASC
        LIMIT :inicio, :registros
    ");
    $stmt->bindValue(":id", $id_seccion, PDO::PARAM_INT);
    $stmt->bindValue(":inicio", $inicio, PDO::PARAM_INT);
    $stmt->bindValue(":registros", $registros, PDO::PARAM_INT);
    $stmt->execute();
    $evaluaciones = $stmt->fetchAll();

    // Total sin paginación
    $totalStmt = $pdo->prepare("
        SELECT COUNT(*) FROM evaluacion
        WHERE id_seccion = :id AND archivar_evaluacion = 0
    ");
    $totalStmt->bindValue(":id", $id_seccion, PDO::PARAM_INT);
    $totalStmt->execute();
    $total = (int) $totalStmt->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-dark">
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>Tipo</th>
                    <th>Objetivo</th>
                    <th>Porcentaje</th>
                    <th>Fecha</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        foreach ($evaluaciones as $eval) {
            $idEnc = mainModel::encryption($eval['id_evaluacion']);
            $tabla .= "<tr class='text-center'>
                <td>{$eval['id_evaluacion']}</td>
                <td>{$eval['tipo_evaluacion']}</td>
                <td>{$eval['obj']}</td>
                <td>{$eval['porcentaje']}%</td>
                <td>".date("d/m/Y", strtotime($eval['fecha_evaluacion']))."</td>

            </tr>";
        }
    } else {
        $tabla .= "<tr class='text-center'><td colspan='6'>No hay evaluaciones archivadas en esta sección.</td></tr>";
    }

    $tabla .= '</tbody></table></div>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando evaluaciones '.($inicio+1).' al '.($inicio+count($evaluaciones)).'</p>';
        
        $tabla .= mainModel::paginador_tablas(
            $pagina,
            $Npaginas,
            SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-arc-consult&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&eval_page=",
            7
        );
    }

    return $tabla;
}
        
        

        
        
        
        
        
    public function paginador_puntaje_archivado_controlador($pagina, $registros, $id_seccion) {
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

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

    // 1. Evaluaciones archivadas
    $stmtEval = $pdo->prepare("
        SELECT id_evaluacion, tipo_evaluacion, obj, porcentaje
        FROM evaluacion
        WHERE id_seccion = :id AND archivar_evaluacion = 0
        ORDER BY obj ASC
    ");
    $stmtEval->bindValue(":id", $id_seccion, PDO::PARAM_INT);
    $stmtEval->execute();
    $evaluaciones = $stmtEval->fetchAll(PDO::FETCH_ASSOC);

    usort($evaluaciones, fn($a, $b) => version_compare($a['obj'], $b['obj']));

    // 2. Estudiantes con inscripciones activas o archivadas
    $stmtEst = $pdo->prepare("
        SELECT est.id_estudiante, est.ci_estudiante, est.nombre, est.apellido, i.id_inscripcion
        FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        WHERE i.id_seccion_fk = :id_seccion AND est.estado_estudiante = 1
        ORDER BY est.apellido ASC, est.nombre ASC
        LIMIT :inicio, :registros
    ");
    $stmtEst->bindValue(":id_seccion", $id_seccion, PDO::PARAM_INT);
    $stmtEst->bindValue(":inicio", $inicio, PDO::PARAM_INT);
    $stmtEst->bindValue(":registros", $registros, PDO::PARAM_INT);
    $stmtEst->execute();
    $estudiantes = $stmtEst->fetchAll(PDO::FETCH_ASSOC);

    // 3. Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-bordered table-sm">
            <thead class="thead-dark">
                <tr class="text-center roboto-medium">
                    <th>Cédula</th>
                    <th>Nombre</th>
                    <th>Apellido</th>';

    foreach ($evaluaciones as $eval) {
        $tabla .= "<th>{$eval['tipo_evaluacion']} (Obj {$eval['obj']})</th>";
    }

    $tabla .= "<th>Nota final</th></tr></thead><tbody>";

    foreach ($estudiantes as $est) {
        $tabla .= "<tr class='text-center'>
            <td>{$est['ci_estudiante']}</td>
            <td>{$est['nombre']}</td>
            <td>{$est['apellido']}</td>";

        // 4. Puntajes archivados
        $stmtPuntaje = $pdo->prepare("
            SELECT pe.id_evaluacion, pe.puntaje
            FROM puntaje_estudiante pe
            INNER JOIN evaluacion ev ON pe.id_evaluacion = ev.id_evaluacion
            WHERE pe.id_inscripcion = :id_inscripcion
              AND pe.archivar_puntaje = 0
              AND ev.archivar_evaluacion = 0
        ");
        $stmtPuntaje->bindValue(":id_inscripcion", $est['id_inscripcion'], PDO::PARAM_INT);
        $stmtPuntaje->execute();
        $puntajes = $stmtPuntaje->fetchAll(PDO::FETCH_ASSOC);

        $puntajes_map = [];
        foreach ($puntajes as $p) {
            $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
        }

        $notaFinal = 0;
        $totalPorcentaje = 0;

        foreach ($evaluaciones as $eval) {
            $idEval = $eval['id_evaluacion'];
            $porcentaje = (float)$eval['porcentaje'];
            $totalPorcentaje += $porcentaje;

            $valor = '-';
            if (isset($puntajes_map[$idEval])) {
                $puntaje = $puntajes_map[$idEval];
                $valor = $puntaje;
                $puntaje = ($puntaje === 'NP') ? 0 : floatval($puntaje);
                $notaFinal += $puntaje * $porcentaje / 100;
            }

            $tabla .= "<td>{$valor}</td>";
        }

        if ($totalPorcentaje == 100) {
            $notaRedondeada = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);
            $color = ($notaRedondeada > 10) ? 'text-success' : 'text-danger';
            $tabla .= "<td class='{$color}'><strong>{$notaRedondeada}</strong></td>";
        } else {
            $tabla .= "<td class='text-muted'>-</td>";
        }

        $tabla .= '</tr>';
    }

    $tabla .= '</tbody></table></div>';

    // 5. Paginador visual
    $stmtTotal = $pdo->prepare("
        SELECT COUNT(*) FROM inscripcion i
        INNER JOIN estudiante est ON i.id_estudiante_fk = est.id_estudiante
        WHERE i.id_seccion_fk = :id_seccion AND est.estado_estudiante = 1
    ");
    $stmtTotal->bindValue(":id_seccion", $id_seccion, PDO::PARAM_INT);
    $stmtTotal->execute();
    $total = (int) $stmtTotal->fetchColumn();
    $Npaginas = ceil($total / $registros);

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando estudiantes '.($inicio+1).' al '.($inicio+count($estudiantes)).'</p>';
      
        $tabla .= mainModel::paginador_tablas(
            $pagina,
            $Npaginas,
            SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-arc-consult&curso_vigente=" . $_GET['curso_vigente'] . "&seccion=" . $_GET['seccion'] . "&est_page=",
            7
        );
        
        
        
        
        //  $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, SERVERURL."index.php?views=seccion-arc-consult&curso_vigente=".$_GET['curso_vigente']."&seccion=".$_GET['seccion']."&est_page=", 7);
    }

    return $tabla;
}
    
    
    
    public function obtener_datos_seccion_archivada_controlador($id_curso_vigente, $letra_seccion) {
    $id_curso_vigente = mainModel::limpiar_cadena($id_curso_vigente);
    $letra_seccion = mainModel::limpiar_cadena($letra_seccion);

    $pdo = mainModel::conectar();

    $stmt = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.nombre_seccion,
            s.id_curso_vigente_fk AS id_curso_vigente,
            c.nombre_curso,
            ch.numero_cohorte,
            ch.anio_cohorte,
            p.id_profesor,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.id_curso_vigente_fk = :id_cv
          AND s.nombre_seccion = :letra
          AND s.archivar_seccion = 0
        LIMIT 1
    ");
    $stmt->bindValue(":id_cv", $id_curso_vigente, PDO::PARAM_INT);
    $stmt->bindValue(":letra", $letra_seccion, PDO::PARAM_STR);
    $stmt->execute();

    $datos = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$datos) {
        return [
            "id_seccion" => 0,
            "nombre_seccion" => $letra_seccion,
            "id_curso_vigente" => $id_curso_vigente,
            "nombre_curso" => "Curso no encontrado",
            "numero_cohorte" => "-",
            "anio_cohorte" => "-",
            "id_profesor" => null,
            "nombre_profesor" => "Sin",
            "apellido_profesor" => "asignar"
        ];
    }

    return $datos;
}
    
    
    
    
    
    
    
    
    
    
    
    
    
    
    public static function exsel_prueba_1($id_curso_vigente, $nombre_seccion) {
    $pdo = mainModel::conectar();
    $id_curso_vigente = mainModel::limpiar_cadena($id_curso_vigente);
    $nombre_seccion = mainModel::limpiar_cadena($nombre_seccion);

    // 1. Buscar la sección con todos los metadatos
    $sec = $pdo->prepare("
        SELECT 
            s.id_seccion,
            s.nombre_seccion,
            p.nombre_profesor,
            p.apellido_profesor,
            c.nombre_curso,
            coh.numero_cohorte,
            coh.anio_cohorte,
            coh.tipo_cohorte
        FROM seccion s
        INNER JOIN profesor p ON s.ci_profesor = p.id_profesor
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        WHERE s.id_curso_vigente_fk = :cv AND s.nombre_seccion = :nombre
        
        
    ");
    $sec->bindParam(":cv", $id_curso_vigente, PDO::PARAM_INT);
    $sec->bindParam(":nombre", $nombre_seccion, PDO::PARAM_STR);
    $sec->execute();
    $datos_seccion = $sec->fetch(PDO::FETCH_ASSOC);

    if (!$datos_seccion) return null;

    $id_seccion = $datos_seccion['id_seccion'];

    // 2. Evaluaciones
    $evaluaciones = seccionModelo::obtener_evaluaciones_modelo($id_seccion)->fetchAll(PDO::FETCH_ASSOC);

    // 3. Estudiantes
    $estudiantes = seccionModelo::obtener_estudiantes_modelo($id_seccion, 0, 10000)->fetchAll(PDO::FETCH_ASSOC);

    $resultado = [
        'seccion' => $datos_seccion,
        'evaluaciones' => $evaluaciones,
        'estudiantes' => []
    ];

    foreach ($estudiantes as $est) {
        $puntajes = seccionModelo::obtener_puntajes_modelo($est['id_inscripcion'])->fetchAll(PDO::FETCH_ASSOC);
        $puntajes_map = [];
        foreach ($puntajes as $p) {
            $puntajes_map[$p['id_evaluacion']] = $p['puntaje'];
        }

        $detalle = [
            'id_estudiante' => $est['id_estudiante'],
            'ci' => $est['ci_estudiante'],
            'nombre' => $est['nombre'],
            'apellido' => $est['apellido'],
            'evaluaciones' => [],
            'suma_porcentajes' => 0,
            'nota_final' => 0,
            'nota_redondeada' => '-'
        ];

        $notaFinal = 0;
        foreach ($evaluaciones as $eval) {
            $idEval = $eval['id_evaluacion'];
            $porcentaje = (float)$eval['porcentaje'];
            $puntaje = isset($puntajes_map[$idEval]) ? $puntajes_map[$idEval] : 'NP';
            $puntaje_valor = ($puntaje === 'NP') ? 0 : floatval($puntaje);

            $puntaje_maximo = 20; // Puedes ajustar si lo tienes en BD
            $porcentaje_obtenido = ($puntaje_maximo > 0) ? ($puntaje_valor / $puntaje_maximo) * $porcentaje : 0;

            $notaFinal += $porcentaje_obtenido;

            $detalle['evaluaciones'][] = [
                'id_evaluacion' => $idEval,
                'tipo' => $eval['tipo_evaluacion'],
                'obj' => $eval['obj'],
                'porcentaje' => $porcentaje,
                'puntaje' => $puntaje,
                'porcentaje_obtenido' => round($porcentaje_obtenido, 2)
            ];
        }

        $detalle['suma_porcentajes'] = round($notaFinal, 2);
        $detalle['nota_final'] = round($notaFinal, 2);
        $detalle['nota_redondeada'] = ($notaFinal - floor($notaFinal) >= 0.5) ? ceil($notaFinal) : floor($notaFinal);

        $resultado['estudiantes'][] = $detalle;
    }
var_dump($resultado);
exit();
        
        
    return $resultado;
}
    
    
        
        public function paginador_seccion_archivadas_controlador22($pagina, $registros, $curso_vigente_id){
    $pagina = (isset($pagina) && $pagina > 0) ? (int)$pagina : 1;
    $inicio = ($pagina - 1) * $registros;

    $conexion = mainModel::conectar();

    // Traer secciones archivadas del curso vigente con nombre del profesor
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            s.id_seccion, 
            s.dia,
            s.nombre_seccion, 
            s.id_curso_vigente_fk,
            p.nombre_profesor,
            p.apellido_profesor
        FROM seccion s
        LEFT JOIN profesor p ON s.ci_profesor = p.id_profesor
        WHERE s.archivar_seccion = 0 AND s.id_curso_vigente_fk = :curso_vigente
        ORDER BY s.nombre_seccion ASC
        LIMIT {$inicio}, {$registros}
    ";

    $stmt = $conexion->prepare($consulta);
    $stmt->execute([':curso_vigente' => $curso_vigente_id]);
    $datos = $stmt->fetchAll();

    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-bordered table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>Nombre</th>
                    <th>Día</th>
                    <th>Profesor</th>
                    <th>Inscritos</th>
                    <th>Evaluaciones</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1) {
        foreach ($datos as $row) {
            $id = $row['id_seccion'];
            $letra = htmlspecialchars($row['nombre_seccion']);
            $dia = htmlspecialchars($row['dia']);

            // 🔹 Contar inscritos
            $stmtInscritos = $conexion->prepare("SELECT COUNT(*) FROM inscripcion WHERE id_seccion_fk = :id");
            $stmtInscritos->execute([':id' => $id]);
            $inscritos = (int) $stmtInscritos->fetchColumn();

            // 🔹 Contar evaluaciones
            $stmtEvaluaciones = $conexion->prepare("SELECT COUNT(*) FROM evaluacion WHERE id_seccion = :id");
            $stmtEvaluaciones->execute([':id' => $id]);
            $evaluaciones = (int) $stmtEvaluaciones->fetchColumn();

            // 🔹 Nombre del profesor
            $profesor = "<em>Sin asignar</em>";
            if (!empty($row['nombre_profesor']) || !empty($row['apellido_profesor'])) {
                $profesor = htmlspecialchars(trim($row['nombre_profesor'] . ' ' . $row['apellido_profesor']));
            }

            $link = SERVERURL . "index.php?views=seccion-arc-consult&curso_vigente=" . urlencode($curso_vigente_id) . "&seccion=" . urlencode($letra);

            $tabla .= "<tr class='text-center'>
                <td>{$id}</td>
                <td>{$letra}</td>
                <td>{$dia}</td>
                <td>{$profesor}</td>
                <td>{$inscritos}</td>
                <td>{$evaluaciones}</td>
                <td>
                    <a href='{$link}' class='btn btn-sm btn-light' style='color:black;'>Ver sección</a>
                </td>
            </tr>";
        }
    } else {
        $tabla .= '<tr class="text-center"><td colspan="6">No hay secciones archivadas registradas para este curso.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador simple
    if ($total > 0 && $pagina <= $Npaginas) {
        $url = SERVERURL . "index.php?views=seccion-archived2&curso_vigente=" . $curso_vigente_id . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 5);
    }

    return $tabla;
}
        
        
        
        
        
        
        
        
        
        
        
        public function paginador_secciones_evaluadas($pagina, $registros) {
            $pagina    = (int) mainModel::limpiar_cadena($pagina);
            $registros = (int) mainModel::limpiar_cadena($registros);
            $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

            $conexion = mainModel::conectar();

            // Consulta principal con filtros y cálculo de porcentaje
            $consulta = "
                SELECT SQL_CALC_FOUND_ROWS
                    cv.id_curso_vigente AS id_periodo_curso,
                    c.nombre_curso,
                    CONCAT(coh.numero_cohorte, '.', RIGHT(coh.anio_cohorte, 2)) AS cohorte,
                    coh.tipo_cohorte,
                    coh.estado_cohorte,
                    CONCAT(s.nombre_seccion, ' / id: ', s.id_seccion) AS seccion,
                    IFNULL(SUM(e.porcentaje), 0) AS suma_porcentajes
                FROM seccion s
                INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
                LEFT JOIN evaluacion e ON e.id_seccion = s.id_seccion AND e.archivar_evaluacion = 1
                WHERE
                    s.archivar_seccion = 1
                    AND cv.estado_curso_vigente = 1
                    AND coh.archivar_cohorte = 1
                    AND coh.estado_cohorte IN ('Vigente', 'Terminado')
                    AND coh.fecha_inicio >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                GROUP BY s.id_seccion
                HAVING suma_porcentajes = 100
                ORDER BY coh.fecha_inicio DESC
                LIMIT $inicio, $registros
            ";

            $datos = $conexion->query($consulta)->fetchAll();
            $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
            $Npaginas = ceil($total / $registros);

            // Construcción de tabla
            $tabla = '<div class="table-responsive">
                <table class="table table-dark table-sm">
                    <thead>
                        <tr class="text-center roboto-medium">
                            <th>Id_periodo_curso</th>
                            <th>Nombre del curso</th>
                            <th>Cohorte</th>
                            <th>Tipo cohorte</th>
                            <th>Estado cohorte</th>
                            <th>Sección</th>
                            <th>Evaluado al 100%</th>
                        </tr>
                    </thead>
                    <tbody>';

            if ($total >= 1 && $pagina <= $Npaginas) {
                $reg_inicio = $inicio + 1;

                foreach ($datos as $row) {
                    $tabla .= '<tr class="text-center">
                        <td>' . $row['id_periodo_curso'] . '</td>
                        <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                        <td>' . htmlspecialchars($row['cohorte']) . '</td>
                        <td>' . htmlspecialchars($row['tipo_cohorte']) . '</td>
                        <td>' . htmlspecialchars($row['estado_cohorte']) . '</td>
                        <td>' . htmlspecialchars($row['seccion']) . '</td>
                        <td><span class="badge badge-success">Sí</span></td>
                    </tr>';
                }

                $reg_final = $reg_inicio + count($datos) - 1;
            } else {
                $tabla .= '<tr class="text-center"><td colspan="7">No hay registros en el sistema</td></tr>';
            }

            $tabla .= '</tbody></table></div>';

            // Paginador visual
            if ($total >= 1 && $pagina <= $Npaginas) {
                $tabla .= '<p class="text-right">Mostrando secciones ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
                $urlCompleta = SERVERURL . "index.php?views=admin/secciones-evaluadas&page=";
                $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
            }

            return $tabla;
        }
        
        
        
        
        
        
        
        
        /*
                        SELECT id_curso_vigente, id_curso_fk
                FROM curso_vigente
                WHERE id_cohorte_fk = :id AND estado_curso_vigente_fk = 1 AND archivar_curso_vigente = 1
            
            
        */
        
        
        
        
        
        
        
        /*
        public static function cohortes_para_excel_select() {
    $pdo = mainModel::conectar();
    $stmt = $pdo->prepare("
SELECT coh.id_cohorte, coh.numero_cohorte, coh.anio_cohorte, coh.tipo_cohorte
FROM cohorte coh
WHERE coh.archivar_cohorte = 1
  AND NOT EXISTS (
    SELECT 1
    FROM curso_vigente cv
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    INNER JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente
    WHERE cv.id_cohorte_fk = coh.id_cohorte
      AND cv.estado_curso_vigente = 1
      AND c.estado_curso = 1
      AND s.archivar_seccion = 1
      AND (
        SELECT ROUND(SUM(e.porcentaje), 2)
        FROM evaluacion e
        WHERE e.id_seccion = s.id_seccion AND e.archivar_evaluacion = 1
      ) != 100.00
  )
ORDER BY coh.anio_cohorte DESC, coh.numero_cohorte DESC
");
    $stmt->execute();
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}
        */
        
        
        public static function cohortes_para_excel_select() {
    $pdo = mainModel::conectar();

    // Buscar todos los cohortes archivados dentro de los últimos 13 meses
    $stmt = $pdo->prepare("
        SELECT id_cohorte, numero_cohorte, anio_cohorte, tipo_cohorte
        FROM cohorte
        WHERE archivar_cohorte = 1
          AND DATE_ADD(fecha_inicio, INTERVAL 13 MONTH) >= CURDATE()
        ORDER BY anio_cohorte DESC, numero_cohorte DESC
    ");
    $stmt->execute();
    $cohortes = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $cohortes_validos = [];

    foreach ($cohortes as $cohorte) {
        $id_cohorte = $cohorte['id_cohorte'];

        // Buscar todas las secciones activas del cohorte
        $secciones_stmt = $pdo->prepare("
            SELECT s.id_seccion
            FROM curso_vigente cv
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            INNER JOIN seccion s ON s.id_curso_vigente_fk = cv.id_curso_vigente
            WHERE cv.id_cohorte_fk = :id
              AND cv.estado_curso_vigente = 1
              AND c.estado_curso = 1
              AND s.archivar_seccion = 1
        ");
        $secciones_stmt->bindParam(":id", $id_cohorte, PDO::PARAM_INT);
        $secciones_stmt->execute();
        $secciones = $secciones_stmt->fetchAll(PDO::FETCH_COLUMN);

        if (empty($secciones)) continue;

        $todas_completas = true;

        foreach ($secciones as $id_seccion) {
            $eval_stmt = $pdo->prepare("
                SELECT ROUND(SUM(porcentaje), 2) as total
                FROM evaluacion
                WHERE id_seccion = :id AND archivar_evaluacion = 1
            ");
            $eval_stmt->bindParam(":id", $id_seccion, PDO::PARAM_INT);
            $eval_stmt->execute();
            $total = (float) $eval_stmt->fetchColumn();

            if ($total !== 100.00) {
                $todas_completas = false;
                break;
            }
        }

        if ($todas_completas) {
            $cohortes_validos[] = $cohorte;
        }
    }

    return $cohortes_validos;
}
        
        
        
        
        
        
        
        
        
        public function paginador_secciones_excel_controlador($pagina, $registros) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
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
  $vistaCompleta = urlencode($carpeta) . '/cal-excel';
      
            
    $conexion = mainModel::conectar();

    // Filtros
    $filtro_anio     = mainModel::limpiar_cadena($_GET['filtro_anio']     ?? "");
    $filtro_tipo     = mainModel::limpiar_cadena($_GET['filtro_tipo']     ?? "");
    $filtro_id       = mainModel::limpiar_cadena($_GET['filtro_id']       ?? "");
    $filtro_evaluado = mainModel::limpiar_cadena($_GET['filtro_evaluado'] ?? "");
    $filtro_estado   = mainModel::limpiar_cadena($_GET['filtro_estado']   ?? "");

    // WHERE dinámico
    $where = "s.archivar_seccion = 1 AND cv.estado_curso_vigente = 1 AND c.estado_curso = 1 AND coh.archivar_cohorte = 1 AND DATE_ADD(coh.fecha_inicio, INTERVAL 13 MONTH) >= CURDATE()";
    if ($filtro_anio != "")     $where .= " AND coh.anio_cohorte = '$filtro_anio'";
    if ($filtro_tipo != "")     $where .= " AND coh.tipo_cohorte = '$filtro_tipo'";
    if ($filtro_id != "")       $where .= " AND coh.id_cohorte = '$filtro_id'";
    if ($filtro_estado != "")   $where .= " AND coh.estado_cohorte = '$filtro_estado'";

    // Total de secciones
    $total_stmt = $conexion->query("
        SELECT COUNT(*) FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
        WHERE $where
    ");
    $total = (int) $total_stmt->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Secciones paginadas
    $secciones_stmt = $conexion->prepare("
        SELECT
            coh.id_cohorte,
            coh.numero_cohorte,
            coh.anio_cohorte,
            coh.tipo_cohorte,
            coh.estado_cohorte,
            c.nombre_curso,
            cv.id_curso_vigente,
            c.id_curso,
            s.id_seccion,
            s.nombre_seccion
        FROM seccion s
        INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte coh ON cv.id_cohorte_fk = coh.id_cohorte
        WHERE $where
        ORDER BY coh.fecha_inicio DESC
        LIMIT $inicio, $registros
    ");
    $secciones_stmt->execute();
    $secciones = $secciones_stmt->fetchAll(PDO::FETCH_ASSOC);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Cohorte</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Curso</th>
                    <th>Id Periodo Curso</th>
                    <th>Sección</th>
                    <th>Evaluado al 100%</th>
                </tr>
            </thead>
            <tbody>';

    foreach ($secciones as $sec) {
        $id_seccion = $sec['id_seccion'];

        // Verificar evaluación
        $eval_stmt = $conexion->prepare("
            SELECT SUM(porcentaje) as total
            FROM evaluacion
            WHERE id_seccion = :id AND archivar_evaluacion = 1
        ");
        $eval_stmt->bindParam(":id", $id_seccion, PDO::PARAM_INT);
        $eval_stmt->execute();
        $total_eval = (float) $eval_stmt->fetchColumn();
        $evaluado = (abs($total_eval - 100) < 0.01) ? "Sí" : "No";

        // Filtro evaluado
        if ($filtro_evaluado !== "") {
            if (($filtro_evaluado == "1" && $evaluado != "Sí") || ($filtro_evaluado == "0" && $evaluado != "No")) {
                continue;
            }
        }

        $cohorte_str  = $sec['numero_cohorte'] . '.' . substr($sec['anio_cohorte'], -2) . " / id: " . $sec['id_cohorte'];
        $curso_str    = $sec['nombre_curso'] . " / id:" . $sec['id_curso'];
        $seccion_str  = $sec['nombre_seccion'] . " / id:" . $sec['id_seccion'];

        $tabla .= '<tr class="text-center">
            <td>' . $cohorte_str . '</td>
            <td>' . $sec['tipo_cohorte'] . '</td>
            <td>' . $sec['estado_cohorte'] . '</td>
            <td>' . $curso_str . '</td>
            <td>' . $sec['id_curso_vigente'] . '</td>
            <td>' . $seccion_str . '</td>
            <td>' . $evaluado . '</td>
        </tr>';
    }

    if ($total == 0) {
        $tabla .= '<tr class="text-center"><td colspan="7">No hay registros disponibles</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando secciones ' . ($inicio + 1) . ' al ' . ($inicio + count($secciones)) . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'filtro_anio'     => $filtro_anio,
            'filtro_tipo'     => $filtro_tipo,
            'filtro_id'       => $filtro_id,
            'filtro_evaluado' => $filtro_evaluado,
            'filtro_estado'   => $filtro_estado
        ]);
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
//        $urlCompleta = SERVERURL . "index.php?views=cal-excel&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}
        
        
        
        
        /*
            public function paginador_secciones_excel_controlador($pagina, $registros) {
        $pagina    = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $conexion = mainModel::conectar();

        // Filtros
        $filtro_anio     = mainModel::limpiar_cadena($_GET['filtro_anio']     ?? "");
        $filtro_tipo     = mainModel::limpiar_cadena($_GET['filtro_tipo']     ?? "");
        $filtro_id       = mainModel::limpiar_cadena($_GET['filtro_id']       ?? "");
        $filtro_evaluado = mainModel::limpiar_cadena($_GET['filtro_evaluado'] ?? "");
		$filtro_estado = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? "");
                
        // WHERE dinámico
        $where = "archivar_cohorte = 1 AND DATE_ADD(fecha_inicio, INTERVAL 13 MONTH) >= CURDATE()";
        if ($filtro_anio != "")     $where .= " AND anio_cohorte = '$filtro_anio'";
        if ($filtro_tipo != "")     $where .= " AND tipo_cohorte = '$filtro_tipo'";
        if ($filtro_id != "")       $where .= " AND id_cohorte = '$filtro_id'";
		if ($filtro_estado != "") $where .= " AND estado_cohorte = '$filtro_estado'";
        
                // Cohortes
        $cohortes_stmt = $conexion->prepare("
            SELECT id_cohorte, numero_cohorte, anio_cohorte, tipo_cohorte, estado_cohorte
            FROM cohorte
            WHERE $where
            ORDER BY fecha_inicio DESC
            LIMIT $inicio, $registros
        ");
        $cohortes_stmt->execute();
        $cohortes = $cohortes_stmt->fetchAll(PDO::FETCH_ASSOC);

        $total_stmt = $conexion->query("SELECT COUNT(*) FROM cohorte WHERE $where");
        $total = (int) $total_stmt->fetchColumn();
        $Npaginas = ceil($total / $registros);

        $tabla = '<div class="table-responsive">
            <table class="table table-dark table-sm">
                <thead>
                    <tr class="text-center roboto-medium">
                        <th>Cohorte</th>
                        <th>Tipo</th>
                        <th>Estado</th>
                        <th>Curso</th>
                        <th>Id Periodo Curso</th>
                        <th>Sección</th>
                        <th>Evaluado al 100%</th>
                    </tr>
                </thead>
                <tbody>';

        foreach ($cohortes as $cohorte) {
            $id_cohorte = $cohorte['id_cohorte'];
            $cohorte_str = $cohorte['numero_cohorte'] . '.' . substr($cohorte['anio_cohorte'], -2) . " / id: " . $cohorte['id_cohorte'];
            //$cohorte_str = $cohorte['numero_cohorte'] . '.' . substr($cohorte['anio_cohorte'], -2);

            // Cursos vigentes
            $cursos_stmt = $conexion->prepare("
            
            SELECT cv.id_curso_vigente, cv.id_curso_fk, c.nombre_curso, c.estado_curso
                FROM curso_vigente cv
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                WHERE cv.id_cohorte_fk = :id
                  AND cv.estado_curso_vigente = 1
                  AND c.estado_curso = 1
            
            ");
            $cursos_stmt->bindParam(":id", $id_cohorte, PDO::PARAM_INT);
            $cursos_stmt->execute();
            $cursos = $cursos_stmt->fetchAll(PDO::FETCH_ASSOC);

            foreach ($cursos as $curso) {
                $id_curso_vigente = $curso['id_curso_vigente'];

                // Curso nombre
                $curso_nombre_stmt = $conexion->prepare("SELECT nombre_curso FROM curso WHERE id_curso = :id");
                $curso_nombre_stmt->bindParam(":id", $curso['id_curso_fk'], PDO::PARAM_INT);
                $curso_nombre_stmt->execute();
                $nombre_curso = $curso_nombre_stmt->fetchColumn();
                $curso_str = "$nombre_curso / id:{$curso['id_curso_fk']}";

                // Secciones
                $secciones_stmt = $conexion->prepare("
                    SELECT id_seccion, nombre_seccion
                    FROM seccion
                    WHERE id_curso_vigente_fk = :id AND archivar_seccion = 1
                ");
                $secciones_stmt->bindParam(":id", $id_curso_vigente, PDO::PARAM_INT);
                $secciones_stmt->execute();
                $secciones = $secciones_stmt->fetchAll(PDO::FETCH_ASSOC);

                foreach ($secciones as $seccion) {
                    $id_seccion = $seccion['id_seccion'];
                    $seccion_str = "{$seccion['nombre_seccion']} / id:$id_seccion";

                    // Evaluado al 100%
                    $eval_stmt = $conexion->prepare("
                        SELECT SUM(porcentaje) as total
                        FROM evaluacion
                        WHERE id_seccion = :id AND archivar_evaluacion = 1
                    ");
                    $eval_stmt->bindParam(":id", $id_seccion, PDO::PARAM_INT);
                    $eval_stmt->execute();
                    $total_eval = (float) $eval_stmt->fetchColumn();
                    $evaluado = (round($total_eval, 2) == 100.00) ? "Sí" : "No";

                    // Filtro evaluado
                    if ($filtro_evaluado !== "") {
                        if (($filtro_evaluado == "1" && $evaluado != "Sí") || ($filtro_evaluado == "0" && $evaluado != "No")) {
                            continue;
                        }
                    }

                    $tabla .= '<tr class="text-center">
                        <td>' . $cohorte_str . '</td>
                        <td>' . $cohorte['tipo_cohorte'] . '</td>
                        <td>' . $cohorte['estado_cohorte'] . '</td>
                        <td>' . $curso_str . '</td>
                        <td>' . $id_curso_vigente . '</td>
                        <td>' . $seccion_str . '</td>
                        <td>' . $evaluado . '</td>
                    </tr>';
                }
            }
        }

        if ($total == 0) {
            $tabla .= '<tr class="text-center"><td colspan="7">No hay registros disponibles</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        // Paginador
        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando cohortes ' . ($inicio + 1) . ' al ' . ($inicio + count($cohortes)) . ' de un total de ' . $total . '</p>';

            $queryParams = http_build_query([
                'filtro_anio' => $filtro_anio,
                'filtro_tipo' => $filtro_tipo,
                'filtro_id' => $filtro_id,
                'filtro_evaluado' => $filtro_evaluado,
                'filtro_estado'   => $filtro_estado
            ]);
            $urlCompleta = SERVERURL . "index.php?views=secciones-evaluadas&" . $queryParams . "&page=";
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
        }

        return $tabla;
    }
        
        */
        
        
        
        public function paginador_secciones_intensivos_controlador($pagina, $registros) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<script>console.log('[v0-intensivos] Iniciando paginador_secciones_intensivos_controlador');</script>";
    
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina    = ($pagina > 0) ? $pagina : 1;
    $inicio    = (($pagina * $registros) - $registros);

    $cargo = $_SESSION['cargo_spm'] ?? 1;

    $carpetasPorCargo = [
        1 => "admin",
        2 => "Control de estudio",
        3 => "administrador de pagos",
        4 => "Supervisor de cursos",
        5 => "Estudiante"
    ];
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";
    $vistaCompleta = urlencode($carpeta) . '/cv-int-list';
               
    echo "<script>console.log('[v0-intensivos] Pagina: {$pagina}, Registros: {$registros}, Inicio: {$inicio}');</script>";

    // Filtros
    $filtro_id           = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre       = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_estado       = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? '');
    $filtro_anio         = (int) ($_GET['filtro_anio'] ?? 0);
    $filtro_profesor     = mainModel::limpiar_cadena($_GET['filtro_profesor'] ?? '');
    $filtro_dia     = mainModel::limpiar_cadena($_GET['filtro_dia'] ?? '');
    $filtro_id_seccion   = mainModel::limpiar_cadena($_GET['filtro_id_seccion'] ?? '');
    $orden               = $_GET['orden'] ?? 'reciente';

    echo "<script>console.log('[v0-intensivos] Filtros aplicados:', " . json_encode([
        'id' => $filtro_id,
        'nombre' => $filtro_nombre,
        'estado' => $filtro_estado,
        'anio' => $filtro_anio,
        'orden' => $orden
    ]) . ");</script>";

    try {
        $conexion = mainModel::conectar();
        echo "<script>console.log('[v0-intensivos] Conexión a BD exitosa');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('[v0-intensivos] Error de conexión: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $where = "cv.id_cohorte_fk IS NULL AND c.tipo_curso = 'Intensivo'";
    
    if ($filtro_id != "")           $where .= " AND cv.id_curso_vigente = '$filtro_id'";
    if ($filtro_nombre != "")       $where .= " AND c.id_curso = '$filtro_nombre'";
    if ($filtro_estado != "")       $where .= " AND pi.estado_periodo = '$filtro_estado'";
    if ($filtro_anio >= 2025)       $where .= " AND YEAR(pi.fecha_inicio_int) = '$filtro_anio'";
    if ($filtro_profesor != "")     $where .= " AND s.ci_profesor = '$filtro_profesor'";
    if ($filtro_dia != "")     $where .= " AND s.dia = '$filtro_dia'";
    if ($filtro_id_seccion != "")   $where .= " AND s.id_seccion = '$filtro_id_seccion'";
    
    $filtro_arc = $_GET['filtro_arc'] ?? 'no';
    if ($filtro_arc === 'si') {
        $where .= " AND EXISTS (
            SELECT 1 FROM seccion s3
            WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
              AND s3.archivar_seccion = 0
        )";
    }

    echo "<script>console.log('[v0-intensivos] WHERE construido: " . addslashes($where) . "');</script>";

    // Ordenamiento
    $orden_sql = match($orden) {
        "alfabetico" => "ORDER BY c.nombre_curso ASC",
        "inscritos"  => "ORDER BY total_inscripciones DESC",
        default      => "ORDER BY pi.fecha_inicio_int DESC"
    };

    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
      cv.id_curso_vigente,
      c.nombre_curso,
      c.id_curso,
      pi.fecha_inicio_int,
      pi.fecha_fin_int,
      pi.pago_inscripcion_int,
      pi.pago_encuentro_int,
      pi.pago_titulo_int,
      pi.estado_periodo,
      pi.id_periodo_intensivo,

      (
        SELECT COUNT(*) 
        FROM inscripcion i 
        INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion 
        WHERE i.id_curso_vigente_fk = cv.id_curso_vigente 
          AND i.archivada = 1 
          AND s2.archivar_seccion = 1
      ) AS total_inscripciones,

      COUNT(DISTINCT s.id_seccion) AS total_secciones,

      (
        SELECT COUNT(*) 
        FROM seccion s3 
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s3.archivar_seccion = 0
      ) AS total_secciones_archivadas

    FROM curso_vigente cv
    INNER JOIN curso c 
      ON cv.id_curso_fk = c.id_curso
    INNER JOIN periodo_intensivo pi 
      ON pi.id_curso_vigente_fk = cv.id_curso_vigente

    LEFT JOIN seccion s 
      ON s.id_curso_vigente_fk = cv.id_curso_vigente 
      AND s.archivar_seccion = 1

    LEFT JOIN profesor p 
      ON s.ci_profesor = p.id_profesor 
      AND p.estado_profesor = 1

    WHERE c.estado_curso = 1
      AND cv.estado_curso_vigente = 1 
      AND $where
      
    GROUP BY 
      cv.id_curso_vigente, 
      c.nombre_curso,
      c.id_curso,
      pi.fecha_inicio_int,
      pi.fecha_fin_int,
      pi.pago_inscripcion_int,
      pi.pago_encuentro_int,
      pi.pago_titulo_int,
      pi.estado_periodo,
      pi.id_periodo_intensivo

    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";

    echo "<script>console.log('[v0-intensivos] Ejecutando consulta SQL');</script>";
    
    try {
        $datos = $conexion->query($consulta);
        
        if ($datos === false) {
            $errorInfo = $conexion->errorInfo();
            echo "<script>console.error('[v0-intensivos] Error SQL:', " . json_encode($errorInfo) . ");</script>";
            return '<div class="alert alert-danger">Error en la consulta SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
        
        $datos = $datos->fetchAll();
        echo "<script>console.log('[v0-intensivos] Consulta exitosa. Registros encontrados: " . count($datos) . "');</script>";
        
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        echo "<script>console.log('[v0-intensivos] Total de registros: {$total}');</script>";
        
    } catch (Exception $e) {
        echo "<script>console.error('[v0-intensivos] Excepción en consulta: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error al ejecutar la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $Npaginas = ceil($total / $registros);

    $cargo = $_SESSION['cargo_spm'] ?? 0;

    $tabla = '<div class="table-responsive">
    <table class="table table-dark table-bordered table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>ID Periodo-Curso</th>
                <th>Curso</th>
                <th>Fechas</th>
                <th>Pago Inscripción</th>
                <th>Pago Encuentro</th>
                <th>Pago Título</th>
                <th>Estado</th>';

    if ($cargo != 5) {
        $tabla .= '<th>Inscritos</th>';
    }

    $tabla .= '<th>Secciones</th>';

    if ($cargo == 1 || $cargo == 4) {
        $tabla .= '
            <th>Secciones archivadas</th>
            <th>Crear sección</th>
            <th>Actualizar</th>
            <th>Archivar</th>';
    }

    $tabla .= '
            </tr>
        </thead>
        <tbody>';
               
    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);
            $secCount = (int) $row['total_secciones'];

            // Formatear fechas
            $fechaInicio = $row['fecha_inicio_int'] ? date('d-m-y', strtotime($row['fecha_inicio_int'])) : 'N/A';
            $fechaFin = $row['fecha_fin_int'] ? date('d-m-y', strtotime($row['fecha_fin_int'])) : 'N/A';
            $fechas = "FI: {$fechaInicio} / FF: {$fechaFin}";

            if ($secCount > 0) {
                $boton = "<button type='button' class='btn btn-sm btn-secondary m-1 ver-secciones'
                              data-id='{$idCV}' data-nombre='{$nombreCurso}'>
                              Ver secciones ({$secCount})
                          </button>";
            } else {
                $boton = "<span class='text-muted'>Sin secciones</span>";
            }

            $secArchivadas = (int) $row['total_secciones_archivadas'];
            if ($secArchivadas > 0) {
                $botonArchivadas = '<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-archived2&curso_vigente=' . urlencode($idCV) . '&nombre_curso=' . urlencode($nombreCurso) . '" class="btn btn-sm btn-warning m-1">Ver archivadas (' . $secArchivadas . ')</a>';
            } else {
                $botonArchivadas = "<span class='text-muted'>Sin archivadas</span>";
            }
            
            $tabla .= "<tr class='text-center'>
                <td>{$idCV}</td>
                <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                <td>{$fechas}</td>
                <td>{$row['pago_inscripcion_int']}</td>
                <td>{$row['pago_encuentro_int']}</td>
                <td>{$row['pago_titulo_int']}</td>
                <td>{$row['estado_periodo']}</td>";

            if ($cargo != 5) {
                $tabla .= "<td>{$row['total_inscripciones']}</td>";
            }

            $tabla .= "<td>{$boton}</td>";

            if ($cargo == 1 || $cargo == 4) {
                $tabla .= "
                <td>{$botonArchivadas}</td>
                <td>
                    <a href='" . SERVERURL . "index.php?views=" . urlencode($carpeta) . "/seccion-new&curso_vigente={$idCV}' class='btn btn-sm btn-success'>
                        <i class='fas fa-plus'></i> Crear
                    </a>
                </td>
                <td>
                    <a href='" . SERVERURL . "index.php?views=" . urlencode($carpeta) . "/cv-int-update&course_id_up=" . mainModel::encryption($idCV) . "' class='btn btn-success btn-sm'>
                        <i class='fas fa-sync-alt'></i>
                    </a>
                </td>
                <td>
                    <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='delete' data-entity='curso_vigente' autocomplete='off'>
                        <input type='hidden' name='curso_vigente_id_archive' value='" . mainModel::encryption($idCV) . "'>
                        <button type='submit' class='btn btn-warning btn-sm'>
                            <i class='fas fa-archive'></i>
                        </button>
                    </form>
                </td>";
            }

            $tabla .= "</tr>";
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="' . ($cargo == 5 ? 8 : ($cargo == 1 || $cargo == 4 ? 12 : 9)) . '">No hay cursos intensivos registrados que coincidan con los filtros.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    $tabla .= '
    <div id="modalSecciones" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
        <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
            <h5 id="tituloSecciones" class="text-white text-center mb-3">Secciones del curso</h5>
            <div id="contenedorSecciones" class="d-flex flex-wrap justify-content-center"></div>
            <button type="button" onclick="document.getElementById(\'modalSecciones\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
    console.log("[v0-intensivos] Agregando event listeners para modales");
    
    document.querySelectorAll(".ver-secciones").forEach(btn => {
        btn.addEventListener("click", function() {
            const modal = document.getElementById("modalSecciones");
            const contenedor = document.getElementById("contenedorSecciones");
            const titulo = document.getElementById("tituloSecciones");
            const carpeta = "' . urlencode($carpeta) . '";
            const cursoId = this.dataset.id;
            const nombreCurso = this.dataset.nombre;
            
            console.log("[v0-intensivos] Abriendo modal de secciones para CV:", cursoId);
            
            titulo.textContent = "Secciones de " + nombreCurso;
            contenedor.innerHTML = "<span class=\'text-light\'>Cargando secciones...</span>";
            
            const body = new URLSearchParams({
                curso_vigente_id: cursoId
            }).toString();
            
            fetch("' . SERVERURL . 'ajax/seccionAjax.php", {
                method: "POST",
                headers: { "Content-Type": "application/x-www-form-urlencoded" },
                body: body
            })
            .then(res => res.json())
            .then(data => {
                console.log("[v0-intensivos] Datos recibidos:", data);
                contenedor.innerHTML = "";
                
                if (!data || data.length === 0) {
                    contenedor.innerHTML = "<span class=\'badge bg-danger\'>No hay secciones registradas</span>";
                    return;
                }
                
                data.forEach(sec => {
                    const nombre = sec.nombre_profesor || "";
                    const apellido = sec.apellido_profesor || "";
                    const profText = (nombre || apellido) ? nombre + " " + apellido : "<em>Sin profesor asignado</em>";
                    
                    const card = document.createElement("div");
                    card.className = "card bg-secondary text-white m-2 p-2";
                    card.style.width = "160px";
                    card.innerHTML = `
                        <div class="card-body p-2">
                            <h6 class="card-title text-center mb-1">Sección ${sec.nombre_seccion}</h6>
                            <p class="card-text text-center mb-1">ID: ${sec.id_seccion}</p>
                            <p class="card-text text-center mb-1">Día: ${sec.dia}</p>
                            <p class="card-text text-center mb-1">Prof: ${profText}</p>
                            <p class="card-text text-center mb-1">Inscritos: ${sec.inscritos || 0}</p>
                            <p class="card-text text-center mb-1">Evaluaciones: ${sec.evaluaciones || 0}</p>
	
	                        <a href="' . SERVERURL . 'index.php?views=${carpeta}/seccion-consult&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
                      </div>
                    `;
                    contenedor.appendChild(card);
                });
            })
            .catch(err => {
                console.error("[v0-intensivos] Error cargando secciones:", err);
                contenedor.innerHTML = "<span class=\'badge bg-danger\'>Error al cargar secciones</span>";
            });
            
            modal.style.display = "block";
        });
    });
    
    console.log("[v0-intensivos] Event listeners agregados correctamente");
    </script>';

    // Paginación
    if ($total > 0 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-center">Mostrando registros del <strong>' . $reg_inicio . '</strong> al <strong>' . $reg_final . '</strong> de un total de <strong>' . $total . '</strong></p>';
        
        $queryParams = http_build_query([
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_estado' => $filtro_estado,
            'filtro_anio' => $filtro_anio,
            'filtro_profesor' => $filtro_profesor,
            'filtro_dia' => $filtro_dia,
            'filtro_id_seccion' => $filtro_id_seccion,
            'filtro_arc' => $filtro_arc,
            'origen' => $_GET['origen'] ?? '',
            'orden' => $orden
        ]);
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }
    
    echo "<script>console.log('[v0-intensivos] Retornando tabla HTML completa');</script>";
    return $tabla;
}
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        
        public function paginador_secciones_intensivos_archivados_controlador($pagina, $registros) {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
    
    echo "<script>console.log('[v0-intensivos-archivados] Iniciando paginador_secciones_intensivos_archivados_controlador');</script>";
    
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $pagina    = ($pagina > 0) ? $pagina : 1;
    $inicio    = (($pagina * $registros) - $registros);

    $cargo = $_SESSION['cargo_spm'] ?? 1;

    $carpetasPorCargo = [
        1 => "admin",
        2 => "Control de estudio",
        3 => "administrador de pagos",
        4 => "Supervisor de cursos",
        5 => "Estudiante"
    ];
    $carpeta = $carpetasPorCargo[$cargo] ?? "admin";
    $vistaCompleta = urlencode($carpeta) . '/cv-int-list-archived';
               
    echo "<script>console.log('[v0-intensivos-archivados] Pagina: {$pagina}, Registros: {$registros}, Inicio: {$inicio}');</script>";

    // Filtros
    $filtro_id           = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre       = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_estado       = mainModel::limpiar_cadena($_GET['filtro_estado'] ?? '');
    $filtro_anio         = (int) ($_GET['filtro_anio'] ?? 0);
    $filtro_profesor     = mainModel::limpiar_cadena($_GET['filtro_profesor'] ?? '');
    $filtro_id_seccion   = mainModel::limpiar_cadena($_GET['filtro_id_seccion'] ?? '');
    $orden               = $_GET['orden'] ?? 'reciente';

    echo "<script>console.log('[v0-intensivos-archivados] Filtros aplicados:', " . json_encode([
        'id' => $filtro_id,
        'nombre' => $filtro_nombre,
        'estado' => $filtro_estado,
        'anio' => $filtro_anio,
        'orden' => $orden
    ]) . ");</script>";

    try {
        $conexion = mainModel::conectar();
        echo "<script>console.log('[v0-intensivos-archivados] Conexión a BD exitosa');</script>";
    } catch (Exception $e) {
        echo "<script>console.error('[v0-intensivos-archivados] Error de conexión: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error de conexión a la base de datos: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $where = "cv.id_cohorte_fk IS NULL AND c.tipo_curso = 'Intensivo' AND cv.estado_curso_vigente = 0";
    
    if ($filtro_id != "")           $where .= " AND cv.id_curso_vigente = '$filtro_id'";
    if ($filtro_nombre != "")       $where .= " AND c.id_curso = '$filtro_nombre'";
    if ($filtro_estado != "")       $where .= " AND pi.estado_periodo = '$filtro_estado'";
    if ($filtro_anio >= 2025)       $where .= " AND YEAR(pi.fecha_inicio_int) = '$filtro_anio'";
    if ($filtro_profesor != "")     $where .= " AND s.ci_profesor = '$filtro_profesor'";
    if ($filtro_id_seccion != "")   $where .= " AND s.id_seccion = '$filtro_id_seccion'";
    
    $filtro_arc = $_GET['filtro_arc'] ?? 'no';
    if ($filtro_arc === 'si') {
        $where .= " AND EXISTS (
            SELECT 1 FROM seccion s3
            WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente
              AND s3.archivar_seccion = 0
        )";
    }

    echo "<script>console.log('[v0-intensivos-archivados] WHERE construido: " . addslashes($where) . "');</script>";

    // Ordenamiento
    $orden_sql = match($orden) {
        "alfabetico" => "ORDER BY c.nombre_curso ASC",
        "inscritos"  => "ORDER BY total_inscripciones DESC",
        default      => "ORDER BY pi.fecha_inicio_int DESC"
    };

    $consulta = "
    SELECT SQL_CALC_FOUND_ROWS
      cv.id_curso_vigente,
      c.nombre_curso,
      c.id_curso,
      pi.fecha_inicio_int,
      pi.fecha_fin_int,
      pi.pago_inscripcion_int,
      pi.pago_encuentro_int,
      pi.pago_titulo_int,
      pi.estado_periodo,
      pi.id_periodo_intensivo,

      (
        SELECT COUNT(*) 
        FROM inscripcion i 
        INNER JOIN seccion s2 ON i.id_seccion_fk = s2.id_seccion 
        WHERE i.id_curso_vigente_fk = cv.id_curso_vigente 
          AND i.archivada = 1 
          AND s2.archivar_seccion = 0
      ) AS total_inscripciones,

      COUNT(DISTINCT s.id_seccion) AS total_secciones,

      (
        SELECT COUNT(*) 
        FROM seccion s3 
        WHERE s3.id_curso_vigente_fk = cv.id_curso_vigente 
          AND s3.archivar_seccion = 0
      ) AS total_secciones_archivadas

    FROM curso_vigente cv
    INNER JOIN curso c 
      ON cv.id_curso_fk = c.id_curso
    INNER JOIN periodo_intensivo pi 
      ON pi.id_curso_vigente_fk = cv.id_curso_vigente

    LEFT JOIN seccion s 
      ON s.id_curso_vigente_fk = cv.id_curso_vigente 
      AND s.archivar_seccion = 0

    LEFT JOIN profesor p 
      ON s.ci_profesor = p.id_profesor 
      AND p.estado_profesor = 1

    WHERE $where
      
    GROUP BY 
      cv.id_curso_vigente, 
      c.nombre_curso,
      c.id_curso,
      pi.fecha_inicio_int,
      pi.fecha_fin_int,
      pi.pago_inscripcion_int,
      pi.pago_encuentro_int,
      pi.pago_titulo_int,
      pi.estado_periodo,
      pi.id_periodo_intensivo

    $orden_sql
    LIMIT {$inicio}, {$registros}
    ";

    echo "<script>console.log('[v0-intensivos-archivados] Ejecutando consulta SQL');</script>";
    
    try {
        $datos = $conexion->query($consulta);
        
        if ($datos === false) {
            $errorInfo = $conexion->errorInfo();
            echo "<script>console.error('[v0-intensivos-archivados] Error SQL:', " . json_encode($errorInfo) . ");</script>";
            return '<div class="alert alert-danger">Error en la consulta SQL: ' . htmlspecialchars($errorInfo[2]) . '</div>';
        }
        
        $datos = $datos->fetchAll();
        echo "<script>console.log('[v0-intensivos-archivados] Consulta exitosa. Registros encontrados: " . count($datos) . "');</script>";
        
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        echo "<script>console.log('[v0-intensivos-archivados] Total de registros: {$total}');</script>";
        
    } catch (Exception $e) {
        echo "<script>console.error('[v0-intensivos-archivados] Excepción en consulta: " . addslashes($e->getMessage()) . "');</script>";
        return '<div class="alert alert-danger">Error al ejecutar la consulta: ' . htmlspecialchars($e->getMessage()) . '</div>';
    }

    $Npaginas = ceil($total / $registros);

    $cargo = $_SESSION['cargo_spm'] ?? 0;

    $tabla = '<div class="table-responsive">
    <table class="table table-dark table-bordered table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>ID Periodo-Curso</th>
                <th>Curso</th>
                <th>Fechas</th>
                <th>Pago Inscripción</th>
                <th>Pago Encuentro</th>
                <th>Pago Título</th>
                <th>Estado</th>';

    if ($cargo != 5) {
        $tabla .= '<th>Inscritos</th>';
    }

    $tabla .= '<th>Secciones archivadas</th>';

    if ($cargo == 1 || $cargo == 4) {
        $tabla .= '<th>Restaurar</th>';
    }

    $tabla .= '
            </tr>
        </thead>
        <tbody>';
               
    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idCV = $row['id_curso_vigente'];
            $nombreCurso = htmlspecialchars($row['nombre_curso']);

            // Formatear fechas
            $fechaInicio = $row['fecha_inicio_int'] ? date('d-m-y', strtotime($row['fecha_inicio_int'])) : 'N/A';
            $fechaFin = $row['fecha_fin_int'] ? date('d-m-y', strtotime($row['fecha_fin_int'])) : 'N/A';
            $fechas = "FI: {$fechaInicio} / FF: {$fechaFin}";

            $secArchivadas = (int) $row['total_secciones_archivadas'];
            if ($secArchivadas > 0) {
                $botonArchivadas = '<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-archived2&curso_vigente=' . urlencode($idCV) . '&nombre_curso=' . urlencode($nombreCurso) . '" class="btn btn-sm btn-warning m-1">Ver archivadas (' . $secArchivadas . ')</a>';
            } else {
                $botonArchivadas = "<span class='text-muted'>Sin archivadas</span>";
            }
            
            $tabla .= "<tr class='text-center'>
                <td>{$idCV}</td>
                <td>{$nombreCurso} / id:{$row['id_curso']}</td>
                <td>{$fechas}</td>
                <td>{$row['pago_inscripcion_int']}</td>
                <td>{$row['pago_encuentro_int']}</td>
                <td>{$row['pago_titulo_int']}</td>
                <td>{$row['estado_periodo']}</td>";

            if ($cargo != 5) {
                $tabla .= "<td>{$row['total_inscripciones']}</td>";
            }

            $tabla .= "<td>{$botonArchivadas}</td>";

            if ($cargo == 1 || $cargo == 4) {
                $tabla .= "
                <td>
                    <form class='FormularioAjax' action='" . SERVERURL . "ajax/cursoVigenteAjax.php' method='POST' data-form='restore' autocomplete='off'>
                        <input type='hidden' name='curso_vigente_id_restore' value='" . mainModel::encryption($idCV) . "'>
                        <button type='submit' class='btn btn-info btn-sm'>
                            <i class='fas fa-undo'></i> Restaurar
                        </button>
                    </form>
                </td>";
            }

            $tabla .= "</tr>";
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="' . ($cargo == 5 ? 8 : ($cargo == 1 || $cargo == 4 ? 9 : 8)) . '">No hay cursos intensivos archivados que coincidan con los filtros.</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginación
    if ($total > 0 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-center">Mostrando registros del <strong>' . $reg_inicio . '</strong> al <strong>' . $reg_final . '</strong> de un total de <strong>' . $total . '</strong></p>';
        
        $queryParams = http_build_query([
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_estado' => $filtro_estado,
            'filtro_anio' => $filtro_anio,
            'filtro_profesor' => $filtro_profesor,
            'filtro_id_seccion' => $filtro_id_seccion,
            'filtro_arc' => $filtro_arc,
            'origen' => $_GET['origen'] ?? '',
            'orden' => $orden
        ]);
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }
    
    echo "<script>console.log('[v0-intensivos-archivados] Retornando tabla HTML completa');</script>";
    return $tabla;
}
        
        
        
        
        
        
        
        
    
    
}

?>




