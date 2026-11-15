<?php
require_once '../controladores/ListaSeccionesGraduadosControlador.php';

if ($_POST) {
    $controlador = new ListaSeccionesGraduadosControlador();
    
    $id_seccion = $_POST['id_seccion'] ?? null;
    $numero_base = $_POST['numero_base'] ?? null;
    
    if ($id_seccion && $numero_base !== null) {
        $resultado = $controlador->generarCodigosGraduadosControlador($id_seccion, $numero_base);
        echo json_encode($resultado);
    } else {
        echo json_encode([
            'success' => false,
            'message' => 'Datos insuficientes'
        ]);
    }
}