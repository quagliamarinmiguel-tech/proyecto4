<?php
require_once "./modelos/mainModel.php"; // Asegurate que esta ruta es válida desde donde lo incluyas

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
?>