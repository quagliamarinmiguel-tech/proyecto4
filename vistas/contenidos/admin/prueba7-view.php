<?php
//require_once '../librerias/vendor/autoload.php';
require_once './controladores/seccionControlador2.php';

$id_cohorte = 47;

$info = seccionControlador::exsel_cohorte($id_cohorte);

echo "<pre>";
print_r($info);
echo "</pre>";
exit;

?>

