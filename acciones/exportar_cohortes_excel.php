<?php
require_once __DIR__ . '/../controladores/ListCohortesAdministracionControlador.php';
require_once __DIR__ . '/../exportadores/exportar_excel_cohortes.php';

$controlador = new ListCohortesControlador();
$cohortes = $controlador->listar_cohortes_con_cursos(0, 1000); // Ajusta límite si lo necesitas

exportar_excel_cohortes($cohortes);
