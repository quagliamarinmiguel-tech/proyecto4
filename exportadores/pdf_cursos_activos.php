<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);
$peticionAjax=true;
session_start(['name' => 'SPM']);   // ← clave

require_once "../controladores/cursoControlador.php";

$controlador = new cursoControlador();
$controlador->generar_pdf_cursos_activos();
