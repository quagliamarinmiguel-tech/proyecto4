<?php
require_once __DIR__ . '/../controladores/ListCoursesCohorteAdministracionControlador.php';
require_once __DIR__ . '/../exportadores/exportar_excel_cursos_cohorte.php';

$id_cohorte = $_GET['id'] ?? null;
if (!$id_cohorte) exit("ID no proporcionado");

$controlador = new ListCursosCohorteControlador();
$cursos = $controlador->listar_cursos_por_cohorte($id_cohorte);
$nombre = $controlador->obtener_nombre_cohorte($id_cohorte);

$nombre_formato = "{$nombre['tipo_cohorte']}_{$nombre['numero_cohorte']}_{$nombre['anio_cohorte']}";
exportar_excel_cursos_cohorte($cursos, $nombre_formato);
