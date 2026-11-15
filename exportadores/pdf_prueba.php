<?php

ini_set('display_errors', 1);
error_reporting(E_ALL);

$peticionAjax=true;
require_once "../controladores/cursoControlador.php";

$controlador = new cursoControlador();
$controlador->generar_pdf_prueba();
