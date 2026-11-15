<?php
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

$conexion = mainModel::conectar();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $id_curso = mainModel::limpiar_cadena($_POST['id_curso'] ?? '');
        $ci_profesor = mainModel::limpiar_cadena($_POST['ci_profesor'] ?? '');

        if (empty($id_curso)) {
            throw new Exception("No se recibió el curso.");
        }

        // 🔍 PRIMERO: Obtener el tipo de curso
        $consultaTipoCurso = $conexion->prepare("
            SELECT tipo_curso 
            FROM curso 
            WHERE id_curso = :id
        ");
        $consultaTipoCurso->execute([':id' => $id_curso]);
        
        if ($consultaTipoCurso->rowCount() !== 1) {
            throw new Exception("No se encontró el curso.");
        }
        
        $tipo_curso = $consultaTipoCurso->fetchColumn();

        // 🔍 BUSCAR CURSO VIGENTE SEGÚN EL TIPO
        if ($tipo_curso === 'Intensivo') {
            // PARA CURSOS INTENSIVOS: Verificar que exista período intensivo vigente
            $consultaCursoVigente = $conexion->prepare("
                SELECT cv.id_curso_vigente 
                FROM curso_vigente cv
                INNER JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
                WHERE cv.id_curso_fk = :id 
                AND cv.estado_curso_vigente = 1
                AND pi.estado_periodo = 'Vigente'
            ");
        } else {
            // PARA CURSOS NO INTENSIVOS: Verificar que exista cohorte vigente
            $consultaCursoVigente = $conexion->prepare("
                SELECT cv.id_curso_vigente 
                FROM curso_vigente cv
                INNER JOIN cohorte ch ON cv.id_cohorte_fk = ch.id_cohorte
                WHERE cv.id_curso_fk = :id 
                AND cv.estado_curso_vigente = 1
                AND ch.estado_cohorte = 'Vigente'
            ");
        }

        $consultaCursoVigente->execute([':id' => $id_curso]);

        if ($consultaCursoVigente->rowCount() !== 1) {
            if ($tipo_curso === 'Intensivo') {
                throw new Exception("No se encontró un período intensivo vigente para este curso.");
            } else {
                throw new Exception("No se encontró una cohorte vigente para este curso.");
            }
        }

        $id_curso_vigente = $consultaCursoVigente->fetchColumn();

        // 🔍 OBTENER LETRAS YA USADAS
        $consultaLetras = $conexion->prepare("
            SELECT nombre_seccion 
            FROM seccion 
            WHERE id_curso_vigente_fk = :id
        ");
        $consultaLetras->execute([':id' => $id_curso_vigente]);

        $letrasUsadas = array_map(function($row) {
            return strtoupper(trim($row['nombre_seccion']));
        }, $consultaLetras->fetchAll());

        // 🔠 DETERMINAR SIGUIENTE LETRA DISPONIBLE
        $letra = null;
        foreach (range('A', 'Z') as $l) {
            if (!in_array($l, $letrasUsadas)) {
                $letra = $l;
                break;
            }
        }

        if (!$letra) {
            throw new Exception("Ya se han creado todas las secciones posibles (A-Z).");
        }

        // 🧱 CREAR NUEVA SECCIÓN
        $crearSeccion = $conexion->prepare("
            INSERT INTO seccion (id_curso_vigente_fk, ci_profesor, nombre_seccion, archivar_seccion)
            VALUES (:curso_vigente, :profesor, :nombre, 1)
        ");
        $crearSeccion->execute([
            ':curso_vigente' => $id_curso_vigente,
            ':profesor' => $ci_profesor ?: null,
            ':nombre' => $letra
        ]);

        $mensaje = "Sección {$letra} creada exitosamente";
        if ($tipo_curso === 'Intensivo') {
            $mensaje .= " para curso intensivo";
        } else {
            $mensaje .= " para curso {$tipo_curso}";
        }

        echo json_encode(['status' => 'ok', 'mensaje' => $mensaje]);
        exit;

    } catch (Exception $e) {
        http_response_code(400);
        echo json_encode(['status' => 'error', 'mensaje' => "Error: " . $e->getMessage()]);
        exit;
    }
} else {
    exit("Acceso no permitido.");
}