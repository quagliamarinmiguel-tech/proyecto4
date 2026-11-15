<?php
// Autocargador exclusivo para contexto de curso_publicidad
spl_autoload_register(function($nombre_clase){
    $ruta_modelo = "../modelos/".$nombre_clase.".php";
    $ruta_controlador = "../controladores/".$nombre_clase.".php";

    if(file_exists($ruta_modelo)){
        require_once $ruta_modelo;
    } elseif(file_exists($ruta_controlador)){
        require_once $ruta_controlador;
    }
});