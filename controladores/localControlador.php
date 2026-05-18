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
        // No requiere desencriptar ni filtrar tipo, ya que el ID es fijo
        return localModelo::datos_local_modelo();
    }




public function actualizar_local_con_imagenes_controlador(){
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

        // Validar campos
        $tel    = mainModel::limpiar_cadena($_POST['telefono_contacto'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['correo_contacto'] ?? '');
        $fb     = mainModel::limpiar_cadena($_POST['link_facebook'] ?? '');
        $ig     = mainModel::limpiar_cadena($_POST['link_instagram'] ?? '');
        $ciudad = mainModel::limpiar_cadena($_POST['direccion_local_ciudad'] ?? '');
        $pais   = mainModel::limpiar_cadena($_POST['direccion_local_pais'] ?? '');

        if($tel=="" || $correo=="" || $ciudad=="" || $pais==""){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar los campos obligatorios.",
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

        // Obtener rutas actuales
        $conexion = mainModel::conectar();
        $query = $conexion->prepare("
            SELECT i.id_informacion_pagina,
                img1.id_imagen AS img_local_id, img1.ruta AS img_local_ruta,
                img2.id_imagen AS img_mapa_id, img2.ruta AS img_mapa_ruta,
                img3.id_imagen AS img_satelite_id, img3.ruta AS img_satelite_ruta
            FROM informacion_pagina i
            LEFT JOIN imagen img1 ON i.imagen_local = img1.id_imagen
            LEFT JOIN imagen img2 ON i.imagen_local_mapa = img2.id_imagen
            LEFT JOIN imagen img3 ON i.imagen_local_satelite = img3.id_imagen
            WHERE i.id_informacion_pagina = 1
        ");
        $query->execute();
        $actual = $query->fetch();

        $imagenes = [
            "imagen_local" => ["id" => $actual['img_local_id'], "ruta" => $actual['img_local_ruta']],
            "imagen_local_mapa" => ["id" => $actual['img_mapa_id'], "ruta" => $actual['img_mapa_ruta']],
            "imagen_local_satelite" => ["id" => $actual['img_satelite_id'], "ruta" => $actual['img_satelite_ruta']]
        ];

        $cambioImagenes = 0;

        foreach ($imagenes as $campo => $info) {
            if (isset($_FILES[$campo]) && $_FILES[$campo]['error'] === 0) {
                $archivo = $_FILES[$campo];
                $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
                $permitidas = ['jpg','jpeg','png','webp'];

                if(!in_array($ext, $permitidas)){
                    return json_encode([
                        "Alerta"=>"simple",
                        "Titulo"=>"Formato inválido",
                        "Texto"=>"La imagen \"$campo\" debe estar en formato JPG, PNG o WEBP.",
                        "Tipo"=>"error"
                    ]);
                }

                $nombreFinal = uniqid("local_") . "." . $ext;
                $rutaRelativa = "cm/ifpg/" . $nombreFinal;
                $rutaCompleta = "../vistas/" . $rutaRelativa;

                if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                    return json_encode([
                        "Alerta"=>"simple",
                        "Titulo"=>"Error al guardar \"$campo\"",
                        "Texto"=>"No se pudo almacenar la imagen en el servidor.",
                        "Tipo"=>"error"
                    ]);
                }

                $vieja = "../vistas/" . $info['ruta'];
                if (file_exists($vieja)) unlink($vieja);

                $actualizarRuta = imagenModelo::actualizar_ruta_imagen_modelo($info['id'], $rutaRelativa);
                $actualizarRuta->execute();

                $cambioImagenes++;
            }
        }

        $datos = [
            "Telefono" => $tel,
            "Correo" => $correo,
            "Facebook" => $fb,
            "Instagram" => $ig,
            "Ciudad" => $ciudad,
            "Pais" => $pais
        ];

        $actualizar = localModelo::actualizar_local_modelo($datos);
        $cambioTexto = ($actualizar->rowCount() == 1);

        // Lógica final de alertas
        if ($cambioTexto && $cambioImagenes > 0) {
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Actualización completa",
                "Texto"=>"Datos institucionales e imagen" . ($cambioImagenes > 1 ? "es" : "") . " fueron actualizados correctamente.",
                "Tipo"=>"success"
            ]);
        } elseif ($cambioTexto) {
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Datos actualizados",
                "Texto"=>"Los datos institucionales fueron modificados correctamente.",
                "Tipo"=>"success"
            ]);
        } elseif ($cambioImagenes > 0) {
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Imagen" . ($cambioImagenes > 1 ? "es actualizadas" : " actualizada"),
                "Texto"=>"Se reemplazó " . $cambioImagenes . " imagen" . ($cambioImagenes > 1 ? "es correctamente." : " correctamente."),
                "Tipo"=>"success"
            ]);
        } else {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Sin cambios",
                "Texto"=>"No se detectaron modificaciones.",
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




    public function actualizar_local_sin_imagenes_controlador(){
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

            // Limpiar datos
            $tel    = mainModel::limpiar_cadena($_POST['telefono_contacto'] ?? '');
            $correo = mainModel::limpiar_cadena($_POST['correo_contacto'] ?? '');
            $fb     = mainModel::limpiar_cadena($_POST['link_facebook'] ?? '');
            $ig     = mainModel::limpiar_cadena($_POST['link_instagram'] ?? '');
            $ciudad = mainModel::limpiar_cadena($_POST['direccion_local_ciudad'] ?? '');
            $pais   = mainModel::limpiar_cadena($_POST['direccion_local_pais'] ?? '');

            if($tel=="" || $correo=="" || $ciudad=="" || $pais==""){
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"Campos incompletos",
                    "Texto"=>"Debes completar los campos obligatorios.",
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

            $datos = [
                "Telefono" => $tel,
                "Correo" => $correo,
                "Facebook" => $fb,
                "Instagram" => $ig,
                "Ciudad" => $ciudad,
                "Pais" => $pais
            ];

            $actualizar = localModelo::actualizar_local_modelo($datos);

            if($actualizar->rowCount() == 1){
                return json_encode([
                    "Alerta"=>"recargar",
                    "Titulo"=>"Actualización exitosa",
                    "Texto"=>"Los datos institucionales fueron actualizados correctamente.",
                    "Tipo"=>"success"
                ]);
            } else {
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"Sin cambios",
                    "Texto"=>"No se detectaron modificaciones en los campos.",
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









}//fin controlador