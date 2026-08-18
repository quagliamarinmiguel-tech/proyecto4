<?php
if($peticionAjax){
    require_once "../modelos/localModelo.php";
    require_once "../modelos/imagenModelo.php";
}else{
    require_once "./modelos/localModelo.php";
    require_once "./modelos/imagenModelo.php";
}

class localControlador extends localModelo{

    public function datos_local_controlador(){
        return localModelo::datos_local_modelo();
    }

    public function obtener_imagenes_local_controlador(){
        return localModelo::obtener_imagenes_local_modelo();
    }

    public function actualizar_local_controlador(){
        try {
            $id_local = intval($_POST['local_id_up'] ?? 0);
            if($id_local !== 1){
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"ID inválido",
                    "Texto"=>"Solo se admite el registro institucional #1.",
                    "Tipo"=>"error"
                ]);
            }

            // 1. Recibir datos fijos y nuevos
            $tel    = mainModel::limpiar_cadena($_POST['telefono_contacto'] ?? '');
            $correo = mainModel::limpiar_cadena($_POST['correo_contacto'] ?? '');
            $fb     = mainModel::limpiar_cadena($_POST['link_facebook'] ?? '');
            $ig     = mainModel::limpiar_cadena($_POST['link_instagram'] ?? '');
            $ciudad = mainModel::limpiar_cadena($_POST['direccion_local_ciudad'] ?? '');
            $pais   = mainModel::limpiar_cadena($_POST['direccion_local_pais'] ?? '');

            $nombre_completo = mainModel::limpiar_cadena($_POST['nombre_completo'] ?? '');
            $nombre_corto    = mainModel::limpiar_cadena($_POST['nombre_corto'] ?? '');
            $rif             = mainModel::limpiar_cadena($_POST['rif_universal'] ?? '');
            $paleta_activa   = intval($_POST['paleta_activa'] ?? 0);

            if($tel=="" || $correo=="" || $ciudad=="" || $pais=="" || $nombre_completo=="" || $nombre_corto=="" || $rif==""){
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"Campos incompletos",
                    "Texto"=>"Debes completar todos los campos obligatorios (Teléfono, Correo, Ciudad, País, Nombre completo, Nombre corto y RIF).",
                    "Tipo"=>"error"
                ]);
            }

            if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"Correo inválido",
                    "Texto"=>"Debes ingresar un correo electrónico válido.",
                    "Tipo"=>"error"
                ]);
            }

            // 2. Recoger datos dinámicos para JSON
            $textosJSON = [
                "txt_header" => mainModel::limpiar_cadena($_POST['txt_header'] ?? ''),
                "txt_titulo_post_header" => mainModel::limpiar_cadena($_POST['txt_titulo_post_header'] ?? ''),
                "txt_subtitulo_post_header" => mainModel::limpiar_cadena($_POST['txt_subtitulo_post_header'] ?? ''),
                "txt_titulo_info1" => mainModel::limpiar_cadena($_POST['txt_titulo_info1'] ?? ''),
                "txt_subtitulo_info1" => mainModel::limpiar_cadena($_POST['txt_subtitulo_info1'] ?? ''),
                "txt_titulo_info2" => mainModel::limpiar_cadena($_POST['txt_titulo_info2'] ?? ''),
                "txt_subtitulo_info2" => mainModel::limpiar_cadena($_POST['txt_subtitulo_info2'] ?? ''),
                "txt_titulo_info3" => mainModel::limpiar_cadena($_POST['txt_titulo_info3'] ?? ''),
                "txt_subtitulo_info3" => mainModel::limpiar_cadena($_POST['txt_subtitulo_info3'] ?? ''),
                "url_add" => mainModel::limpiar_cadena($_POST['url_add'] ?? ''),
                "txt_titulo_footer" => mainModel::limpiar_cadena($_POST['txt_titulo_footer'] ?? ''),
                "txt_ult_titulo_footer" => mainModel::limpiar_cadena($_POST['txt_ult_titulo_footer'] ?? ''),
                "txt_ult_subtitulo_footer" => mainModel::limpiar_cadena($_POST['txt_ult_subtitulo_footer'] ?? ''),
                "nombre_local_completo" => $nombre_completo,
                "nombre_local_corto" => $nombre_corto
            ];

            // Comentarios 1 al 9
            for($i = 1; $i <= 9; $i++) {
                $textosJSON["nombre_com$i"] = mainModel::limpiar_cadena($_POST["nombre_com$i"] ?? '');
                $textosJSON["texto_com$i"] = mainModel::limpiar_cadena($_POST["texto_com$i"] ?? '');
            }

            // Generar JSON para la paleta elegida
            $paletas = [
                0 => ['#00a9f1', '#002a6f', '#72F2EB', '#FF5F5D', '#3ca6a6'],
                1 => ['#9C2113', '#F9ECEC', '#014B43', '#FFD6D1', '#099078'],
                2 => ['#1C338C', '#1B1E59', '#8C1B1B', '#F2F2F2', '#262626'],
                3 => ['#BF0413', '#F27329', '#F24F13', '#F9ECEC', '#A60303'],
                4 => ['#518C81', '#667302', '#515916', '#D9C196', '#D92A1A'],
                5 => ['#FFEFC8', '#7A2B1F', '#471B00', '#522D00', '#E8A149'],
                6 => ['#F9D746', '#142C49', '#F3E3C6', '#4F7FE7', '#FFFFFF'],
                7 => ['#B3A5CC', '#E9EEFF', '#CCBAA6', '#9E82D9', '#C2ACF2'],
                8 => ['#27498C', '#1A4173', '#2E578C', '#B1BBC7', '#D9D9D7'],
                9 => ['#EB7428', '#4F342B', '#F1CDAB', '#D7EFE1', '#EFA438'],
                10 => ['#FBFFFF', '#EBC9C0', '#D48392', '#B73736', '#CFAA7D'],
                11 => ['#010326', '#024959', '#208C8C', '#89D9C2', '#0D0D0D'],
                12 => ['#F5F3F0', '#E8DFD1', '#5C1C3C', '#94215A', '#BAAC97'],
                13 => ['#193C40', '#2E5902', '#1E661B', '#D96941', '#A62B1F']
            ];
            $colores = $paletas[$paleta_activa] ?? $paletas[0];
            $coloresJSON = [
                "paleta_id" => $paleta_activa,
                "color1" => $colores[0],
                "color2" => $colores[1],
                "color3" => $colores[2],
                "color4" => $colores[3],
                "color5" => $colores[4]
            ];
            $colores_json_str = json_encode($coloresJSON, JSON_UNESCAPED_UNICODE);

            $datos = [
                "Telefono" => $tel,
                "Correo" => $correo,
                "Facebook" => $fb,
                "Instagram" => $ig,
                "Ciudad" => $ciudad,
                "Pais" => $pais,
                "NombreCompleto" => $nombre_completo,
                "NombreCorto" => $nombre_corto,
                "Rif" => $rif,
                "ColoresJson" => $colores_json_str,
                "TextosJson" => json_encode($textosJSON, JSON_UNESCAPED_UNICODE)
            ];

            $nombres_especiales = [
                'icono', 'logo', 'img_header', 
                'img_desc1', 'img_desc2', 'img_desc3', 
                'loca', 'mapa_google', 'mapa_vista_satelital', 
                'img_footer'
            ];

            $restriccionesPorImagen = [
                'icono' => ['forma' => 'cuadrada', 'min_width' => 64, 'min_height' => 64],
                'logo' => ['forma' => 'cuadrada', 'min_width' => 300, 'min_height' => 300],
                'img_header' => ['forma' => 'horizontal', 'min_width' => 900, 'min_height' => 300],
                'img_desc1' => ['forma' => 'cuadrada', 'min_width' => 400, 'min_height' => 400],
                'img_desc2' => ['forma' => 'cuadrada', 'min_width' => 400, 'min_height' => 400],
                'img_desc3' => ['forma' => 'cuadrada', 'min_width' => 400, 'min_height' => 400],
                'loca' => ['forma' => 'cuadrada', 'min_width' => 400, 'min_height' => 400],
                'mapa_google' => ['forma' => 'horizontal', 'min_width' => 800, 'min_height' => 400],
                'mapa_vista_satelital' => ['forma' => 'horizontal', 'min_width' => 800, 'min_height' => 400],
                'img_footer' => ['forma' => 'horizontal', 'min_width' => 900, 'min_height' => 300]
            ];

            $validacionImagenes = mainModel::validar_imagenes($_FILES, $restriccionesPorImagen);
            if ($validacionImagenes['status'] === 'error') {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error en la imagen",
                    "Texto" => $validacionImagenes['mensaje'],
                    "Tipo" => "error"
                ]);
            }

            $actualizar = localModelo::actualizar_local_modelo($datos);
            $cambioTexto = ($actualizar->rowCount() == 1);

            // 3. Procesar Imágenes Dinámicas (11 imágenes en total)
            $cambioImagenes = 0;
            $erroresImg = "";

            foreach($nombres_especiales as $nom_esp) {
                if(isset($_FILES[$nom_esp]) && $_FILES[$nom_esp]['error'] === 0) {
                    // Obtener datos de la imagen vieja si existe
                    $vieja = localModelo::obtener_id_imagen_local_modelo($nom_esp)->fetch(PDO::FETCH_ASSOC);

                    // Subir nueva usando la universal
                    $subida = mainModel::subir_imagen_universal($_FILES[$nom_esp], "../vistas/cm/ifpg/", "ifpg", $nom_esp);
                    
                    if($subida['status'] == 'success') {
                        // Vincular en DB
                        localModelo::vincular_imagen_local_modelo($subida['id_imagen'], $nom_esp);
                        
                        // Si había una vieja, borrar del disco y de la base de datos
                        if($vieja) {
                            $ruta_vieja = "../vistas/" . $vieja['ruta'];
                            if(file_exists($ruta_vieja)) unlink($ruta_vieja);
                            
                            localModelo::eliminar_registro_imagen_modelo($vieja['id_img_fk']);
                        }
                        $cambioImagenes++;
                    } else {
                        $erroresImg .= " " . $nom_esp . ": " . $subida['mensaje'];
                    }
                }
            }

            // 4. Respuestas
            if ($cambioTexto || $cambioImagenes > 0) {
                $mensaje = "Datos guardados.";
                if($cambioImagenes > 0) $mensaje .= " Se subieron $cambioImagenes imágenes.";
                if($erroresImg != "") $mensaje .= " Avisos:$erroresImg";
                
                return json_encode([
                    "Alerta"=>"recargar",
                    "Titulo"=>"Actualización exitosa",
                    "Texto"=>$mensaje,
                    "Tipo"=>$erroresImg == "" ? "success" : "warning"
                ]);
            } else {
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"Sin cambios",
                    "Texto"=>"No se detectaron modificaciones en textos ni imágenes.",
                    "Tipo"=>"info"
                ]);
            }

        } catch (Exception $e){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error interno",
                "Texto"=>"Detalles: " . $e->getMessage(),
                "Tipo"=>"error"
            ]);
        }
    }

    public function actualizar_sliders_controlador(){
        try {
            $sliderAuto = [];
            $sliderVertical = [];
            $restricciones = [];

            for ($i = 1; $i <= 8; $i++) {
                $nombre = "slider_auto_$i";
                $sliderAuto[] = $nombre;
                $restricciones[$nombre] = ['forma' => 'cuadrada'];
            }

            for ($i = 1; $i <= 6; $i++) {
                $nombre = "slider_vertical_$i";
                $sliderVertical[] = $nombre;
                $restricciones[$nombre] = ['forma' => 'vertical'];
            }

            $validacionImagenes = mainModel::validar_imagenes($_FILES, $restricciones);
            if ($validacionImagenes['status'] === 'error') {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error en la imagen",
                    "Texto" => $validacionImagenes['mensaje'],
                    "Tipo" => "error"
                ]);
            }

            $cambioImagenes = 0;
            $erroresImg = "";

            foreach (array_merge($sliderAuto, $sliderVertical) as $nom_esp) {
                if (isset($_FILES[$nom_esp]) && $_FILES[$nom_esp]['error'] === 0) {
                    $vieja = localModelo::obtener_id_imagen_local_modelo($nom_esp)->fetch(PDO::FETCH_ASSOC);
                    $tipoImagen = strpos($nom_esp, 'slider_auto_') === 0 ? 'slider_auto' : 'slider_vertical';

                    $subida = mainModel::subir_imagen_universal($_FILES[$nom_esp], "../vistas/cm/ifpg/", $tipoImagen, $nom_esp);
                    if ($subida['status'] == 'success') {
                        localModelo::vincular_imagen_local_modelo($subida['id_imagen'], $nom_esp);

                        if ($vieja) {
                            $ruta_vieja = "../vistas/" . $vieja['ruta'];
                            if (file_exists($ruta_vieja)) {
                                unlink($ruta_vieja);
                            }
                            localModelo::eliminar_registro_imagen_modelo($vieja['id_img_fk']);
                        }
                        $cambioImagenes++;
                    } else {
                        $erroresImg .= " " . $nom_esp . ": " . $subida['mensaje'];
                    }
                }
            }

            if ($cambioImagenes > 0) {
                $mensaje = "Imágenes actualizadas correctamente.";
                if ($erroresImg !== "") {
                    $mensaje .= " Avisos:" . $erroresImg;
                }
                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Actualización de sliders exitosa",
                    "Texto" => $mensaje,
                    "Tipo" => $erroresImg == "" ? "success" : "warning"
                ]);
            }

            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Sin cambios",
                "Texto" => "No se detectaron imágenes nuevas para subir.",
                "Tipo" => "info"
            ]);

        } catch (Exception $e) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error interno",
                "Texto" => "Detalles: " . $e->getMessage(),
                "Tipo" => "error"
            ]);
        }
    }

}//fin controlador