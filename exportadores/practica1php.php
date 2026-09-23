<?php
//require 'vendor/autoload.php';

//use PhpOffice\PhpSpreadsheet\Spreadsheet;
//use PhpOffice\PhpSpreadsheet\Writer\Xlsx;


require_once '../vendor/autoload.php';
  //  require_once '../librerias/autoload-phpspreadsheet.php'; // tu autoload personalizado
//require_once '../controladores/seccionControlador2.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
//use PhpOffice\PhpSpreadsheet\Style\Fill;
//use PhpOffice\PhpSpreadsheet\Style\Alignment;
//use PhpOffice\PhpSpreadsheet\Style\Border;    
    
    
    
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Hola Miguel');
$sheet->setCellValue('A2', '¡PhpSpreadsheet está funcionando!');

$writer = new Xlsx($spreadsheet);
$writer->save('salida.xlsx');

echo "Archivo Excel generado correctamente.";
