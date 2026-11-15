<?php
require_once "../config/APP.php";
require_once "../autoload.php";

$termino = trim($_POST['termino'] ?? '');
$termino = mainModel::limpiar_cadena($termino);

$conexion = mainModel::conectar();
$query = $conexion->prepare("SELECT id_curso, nombre_curso, tipo_curso FROM curso WHERE nombre_curso LIKE :t LIMIT 10");
$query->execute([':t' => "%$termino%"]);

$salida = "";
while($row = $query->fetch()){
    $nombre = htmlspecialchars($row['nombre_curso'])." (".htmlspecialchars($row['tipo_curso']).")";
    $id = $row['id_curso'];

    $salida .= '<div class="list-group-item list-group-item-action item-curso" data-id="'.$id.'" data-nombre="'.$nombre.'">'.$nombre.'</div>';
}

echo $salida ?: '<div class="list-group-item disabled text-muted">Sin coincidencias</div>';