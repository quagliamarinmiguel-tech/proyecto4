<?php
require_once __DIR__ . '/../controladores/ListSectionCohorteAdministracionControlador.php';
require_once __DIR__ . '/../exportadores/exportar_pdf_secciones.php';

$id_curso = $_GET['id'] ?? null;
if (!$id_curso) exit("ID no proporcionado");

$controlador = new ListSeccionesCursoControlador();
$secciones = $controlador->listar_secciones_por_curso($id_curso);
$nombre = $controlador->obtener_nombre_curso($id_curso);

exportar_pdf_secciones($secciones, $nombre);
