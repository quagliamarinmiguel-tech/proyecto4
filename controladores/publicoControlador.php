<?php
require_once "./modelos/publicoModelo.php";

class publicoControlador extends publicoModelo {

    public function procesar_evento_controlador($accion, $datos) {
        switch($accion){
            case "click":
                self::registrar_click($datos);
                break;
            case "vista":
                self::registrar_vista($datos);
                break;
        }
    }




public function cargar_inicio_controlador(){
    $datos = [];

    // Cursos publicitarios + cohorte vigente
    try {
        $resultadoCursos = self::obtener_cursos_publicitarios_modelo();
        if (!$resultadoCursos) {
            throw new Exception("La consulta de cursos publicitarios retornó un resultado inválido.");
        }
        $datos['curso'] = $resultadoCursos->fetchAll(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log(" Error al obtener cursos publicitarios: " . $e->getMessage());
        $datos['curso'] = [];
    }

    // Profesores mostrables
    try {
        $resultadoProfesores = self::obtener_profesores_modelo();
        if (!$resultadoProfesores || !is_array($resultadoProfesores)) {
            throw new Exception("La consulta de profesores retornó un resultado inválido.");
        }
        $datos['profesores'] = $resultadoProfesores;
    } catch (Exception $e) {
        error_log("Error al obtener profesores: " . $e->getMessage());
        $datos['profesores'] = [];
    }



    // Información institucional Ceproca
    try {
        $info = self::obtener_info_ceproca_modelo();
        if (!$info || $info->rowCount() != 1) {
            throw new Exception("No se encontró información institucional.");
        }
        $datos['infoceproca'] = $info->fetch(PDO::FETCH_ASSOC);
    } catch (Exception $e) {
        error_log(" Error al obtener información de Ceproca: " . $e->getMessage());
        $datos['infoceproca'] = [];
    }




    // Imágenes de sliders agrupadas por id_slider 
    try {
        $resultadoSliders = self::obtener_imagenes_slider_modelo(); 
        if (!$resultadoSliders || !is_array($resultadoSliders)) {
            throw new Exception("La consulta de sliders retornó un resultado inválido.");
        }
        $datos['slider'] = $resultadoSliders;
    } catch (Exception $e) {
        error_log(" Error al obtener imágenes de sliders: " . $e->getMessage());
        $datos['slider'] = [
            'slider_1' => [],
            'slider_2' => [],
            'slider_3' => []
        ];
    }


    return $datos;
}







    public function cargar_cursos_controlador() {
        $datos = [];

        try {
            $info = self::obtener_info_ceproca_modelo();
            $datos['infoceproca'] = ($info && $info->rowCount() === 1) ? $info->fetch(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log(" Info institucional: " . $e->getMessage());
            $datos['infoceproca'] = [];
        }

        try {
            $cursos = self::obtener_cursos_extendidos_modelo();
            $datos['cursos_ext'] = ($cursos) ? $cursos->fetchAll(PDO::FETCH_ASSOC) : [];
        } catch (Exception $e) {
            error_log(" Cursos extendidos: " . $e->getMessage());
            $datos['cursos_ext'] = [];
        }

        return $datos;
    }













}//fin controlador







