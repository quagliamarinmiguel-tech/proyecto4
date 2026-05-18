<?php
echo "Directorio actual: " . __DIR__ . "<br>";
echo "Document root: " . $_SERVER['DOCUMENT_ROOT'] . "<br>";

// Probemos diferentes rutas
$rutas = [
    '../../../modelos/mainModel.php',
    '../../modelos/mainModel.php',
    '../modelos/mainModel.php',
    '/modelos/mainModel.php',
    'modelos/mainModel.php'
];

foreach ($rutas as $ruta) {
    $ruta_completa = __DIR__ . '/' . $ruta;
    echo "Probando: $ruta -> " . (file_exists($ruta_completa) ? 'EXISTE' : 'NO EXISTE') . "<br>";
}

// También probar con document root
$ruta_docroot = $_SERVER['DOCUMENT_ROOT'] . '/modelos/mainModel.php';
echo "Document root path: $ruta_docroot -> " . (file_exists($ruta_docroot) ? 'EXISTE' : 'NO EXISTE') . "<br>";
?>