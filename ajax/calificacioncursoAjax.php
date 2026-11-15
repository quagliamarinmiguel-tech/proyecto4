<?php
// Forzar salida como JSON
header('Content-Type: application/json; charset=utf-8');

// Activar errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir dependencias
require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

$conexion = mainModel::conectar();

$consulta = $conexion->prepare("
    SELECT 
        cv.id_curso_vigente,
        c.nombre_curso,
        c.tipo_curso,
        co.numero_cohorte,
        co.anio_cohorte
    FROM curso_vigente cv
    INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
    INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
    WHERE cv.estado_curso_vigente = 'activo'
");

$consulta->execute();
$cursos = [];

while ($row = $consulta->fetch(PDO::FETCH_ASSOC)) {
    $cursos[] = [
        'id_curso'    => $row['id_curso_vigente'],
        'nombre_curso'=> $row['nombre_curso'],
        'tipo'        => $row['tipo_curso'],
        'cohorte'     => "Cohorte {$row['numero_cohorte']} - {$row['anio_cohorte']}"
    ];
}

echo json_encode($cursos);