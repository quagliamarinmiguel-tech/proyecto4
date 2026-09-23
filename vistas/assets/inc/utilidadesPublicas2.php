<?php
require_once "./modelos/mainModel.php"; // Asegúrate que esta ruta es válida

function conectarBase() {
    return mainModel::conectar();
}

function registrar_ip_visitante($pdo, $direccionIP) {
    $sqlBuscar = "SELECT id_ip_visitante FROM ip_visitante WHERE direccion_ip = :ip";
    $stmt = $pdo->prepare($sqlBuscar);
    $stmt->execute([':ip' => $direccionIP]);
    $idIP = $stmt->fetchColumn();

    if (!$idIP) {
        $sqlInsert = "INSERT INTO ip_visitante (direccion_ip) VALUES (:ip)";
        $stmt = $pdo->prepare($sqlInsert);
        $stmt->execute([':ip' => $direccionIP]);
        $idIP = $pdo->lastInsertId();
    }

    return intval($idIP);
}

function registrar_visita($pdo, $idIP) {
    $fecha = date("Y-m-d H:i:s");
    $sql = "INSERT INTO visitante (id_ip_fk, fecha_hora_entrada) VALUES (:idip, :fecha)";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':idip' => $idIP,
        ':fecha' => $fecha
    ]);
}



/*
function registrar_interaccion_banner($pdo, $idMateria, $idIP, $tipoBanner = "Informativo") {
    //$sqlCurso = "SELECT id_curso FROM curso_publicidad WHERE id_materia = :idMateria";
    $stmtCurso = $idMateria;
    $stmtCurso->execute([':idMateria' => $idMateria]);
    $idCurso = $stmtCurso->fetchColumn();

    if ($idCurso) {
        $fechaClick = date("Y-m-d H:i:s");
        $sql = "INSERT INTO interaccion_banner (fecha_hora, id_curso_fk, id_ip_fk, tipo_banner)
                VALUES (:fecha, :idCurso, :idIP, :tipo)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':fecha' => $fechaClick,
            ':idCurso' => $idCurso,
            ':idIP' => $idIP,
            ':tipo' => $tipoBanner
        ]);
    }
}*/





function registrar_interaccion_banner(PDO $pdo, int $idCurso, int $idIP, string $tipoBanner = "Informativo"){
    $fechaClick = date("Y-m-d H:i:s");
    $sql = "
        INSERT INTO interaccion_banner
            (fecha_hora, id_curso_fk, id_ip_fk, tipo_banner)
        VALUES
            (:fecha, :idCurso, :idIP, :tipo)
    ";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':fecha'   => $fechaClick,
        ':idCurso' => $idCurso,
        ':idIP'    => $idIP,
        ':tipo'    => $tipoBanner
    ]);
}




?>
