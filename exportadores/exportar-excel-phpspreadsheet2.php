<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
ob_start(); // al inicio del script
    require_once '../librerias/vendor/autoload.php';
require_once '../controladores/seccionControlador2.php';

if (strlen(ob_get_contents()) > 0) {
  die("Hay salida previa: «" . ob_get_contents() . "»");
}

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$id_cohorte = $_GET['id_cohorte'] ?? 0;
if ($id_cohorte == 0) die("Cohorte inválido.");

$info = seccionControlador::exsel_cohorte($id_cohorte);
if (!$info || empty($info['secciones'])) die("No hay secciones listas.");

$cohorte = $info['cohorte'];
$tipo_abrev = ($cohorte['tipo_cohorte'] === 'Intensivo') ? 'Int' :
              (($cohorte['tipo_cohorte'] === 'Extendido') ? 'Ext' : 'Tec');
$nombre_archivo = "Cal Co {$cohorte['numero_cohorte']}_{$cohorte['anio_cohorte']} {$tipo_abrev}.xlsx";

$spreadsheet = new Spreadsheet();
$spreadsheet->removeSheetByIndex(0); // eliminar hoja por defecto

foreach ($info['secciones'] as $seccion) {
    $datos = $seccion['datos'];
    $nombre_curso = $seccion['nombre_curso'];
    $tipo_curso = $seccion['tipo_curso'];
    $nombre_seccion = $seccion['nombre_seccion'];

    $tipo_abrev_curso = ($tipo_curso === 'Intensivo') ? 'Int' :
                        (($tipo_curso === 'Extendido') ? 'Ext' : 'Tec');
    $nombre_hoja = substr("{$nombre_curso} {$tipo_abrev_curso} {$nombre_seccion}", 0, 31);

    $sheet = $spreadsheet->createSheet();
    $sheet->setTitle($nombre_hoja);

    // Aquí reutilizas tu lógica actual de renderizado
 //   renderizar_tabla_excel($sheet, $datos);
	seccionControlador::renderizar_tabla_excel($sheet, $datos);

}

header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header("Content-Disposition: attachment; filename=\"$nombre_archivo\"");
header('Cache-Control: max-age=0');

ob_clean(); // limpia cualquier salida previa
$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;