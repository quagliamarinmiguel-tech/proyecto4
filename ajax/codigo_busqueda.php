<?php
require_once "../config/APP.php"; // o tu archivo de conexión
require_once "autoloadcurso.php"; // si usás autoload modular

$termino = trim($_POST['busqueda'] ?? '');
$termino = mainModel::limpiar_cadena($termino);

$conexion = mainModel::conectar();
$query = $conexion->prepare("
    SELECT id_curso, nombre_curso, tipo_curso 
    FROM curso 
    WHERE nombre_curso LIKE :t 
    ORDER BY nombre_curso ASC 
    LIMIT 10
");
$query->execute([":t" => "%$termino%"]);

while($row = $query->fetch()){
    $nombre = htmlspecialchars($row["nombre_curso"])." (".htmlspecialchars($row["tipo_curso"]).")";
    echo '<div class="list-group-item item-fk" data-id="'.$row["id_curso"].'" data-nombre="'.$nombre.'">'.$nombre.'</div>';
}

if($query->rowCount() === 0){
    echo '<div class="list-group-item text-muted disabled">Sin coincidencias</div>';
}