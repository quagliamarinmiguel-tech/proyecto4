<?php
//require_once "./modelos/imagenModelo.php";
//require_once __DIR__."/../modelos/imagenModelo.php";

if($peticionAjax){
    require_once "../modelos/imagenModelo.php";
}else{
    require_once "./modelos/imagenModelo.php";
}


class imagenControlador extends imagenModelo {


public function subir_slider_img_controlador(){
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0){

        $archivo = $_FILES['imagen'];
        $nombreTemp = $archivo['tmp_name'];
        $nombreOriginal = basename($archivo['name']);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];

        // Validar extensión
        if(!in_array($extension, $permitidas)){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Formato no permitido",
                "Texto" => "Solo se aceptan imágenes: ".implode(", ", $permitidas),
                "Tipo" => "error"
            ]);
        }

        // Validar dimensiones reales
        $dimensiones = getimagesize($nombreTemp);
        if(!$dimensiones){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Archivo inválido",
                "Texto" => "No se pudieron obtener las dimensiones de la imagen.",
                "Tipo" => "error"
            ]);
        }

        $ancho = $dimensiones[0];
        $alto = $dimensiones[1];

        $formaDetectada = $this->obtenerFormaImagen($ancho, $alto);
        $tipoDetectado = $formaDetectada === 'vertical' ? 'sldr2' : 'sldr1';

        $tipoUsuario = $_POST['tipo_seleccionado'] ?? '';
        if ($tipoUsuario === 'sldr2' && $formaDetectada !== 'vertical') {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Orientación incorrecta",
                "Texto" => "La imagen es $formaDetectada, pero seleccionaste vertical.",
                "Tipo" => "error"
            ]);
        }
        if ($tipoUsuario === 'sldr1' && $formaDetectada === 'vertical') {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Orientación incorrecta",
                "Texto" => "La imagen es vertical, pero seleccionaste horizontal.",
                "Tipo" => "error"
            ]);
        }

        // Carpetas
        $carpetaDestino = "../vistas/cm/".$tipoDetectado."/";
        if(!file_exists($carpetaDestino)){
            mkdir($carpetaDestino, 0777, true);
        }

        $nombreSanitizado = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $nombreOriginal);
        $nombreBase = pathinfo($nombreSanitizado, PATHINFO_FILENAME);
        $ext = pathinfo($nombreSanitizado, PATHINFO_EXTENSION);

        // Asegurar nombre único
        $contador = 0;
        $nombreFinal = $nombreSanitizado;
        $rutaCompleta = $carpetaDestino.$nombreFinal;
        while(file_exists($rutaCompleta)){
            $contador++;
            $nombreFinal = $nombreBase."_".$contador.".".$ext;
            $rutaCompleta = $carpetaDestino.$nombreFinal;
        }

        $rutaRelativa = "cm/".$tipoDetectado."/".$nombreFinal;

        // Mover archivo
        if(move_uploaded_file($nombreTemp, $rutaCompleta)){
            $guardar = $this->guardar_imagen_modelo($rutaRelativa, $tipoDetectado);

            if($guardar->rowCount() > 0){
                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Imagen subida",
                    "Texto" => "La imagen fue guardada correctamente como ".$nombreFinal,
                    "Tipo" => "success"
                ]);
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error en base de datos",
                    "Texto" => "No se pudo registrar la imagen en la tabla.",
                    "Tipo" => "error"
                ]);
            }

        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Fallo al guardar",
                "Texto" => "No se pudo mover la imagen al servidor.",
                "Tipo" => "error"
            ]);
        }

    } else {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Archivo no recibido",
            "Texto" => "Debes seleccionar una imagen válida.",
            "Tipo" => "error"
        ]);
    }
}














private function obtenerFormaImagen(int $ancho, int $alto): string
{
    if ($alto === 0) {
        return 'desconocida';
    }

    $ratio = $ancho / $alto;
    if (abs($ratio - 1) <= 0.1) {
        return 'cuadrada';
    }

    return $ratio > 1 ? 'horizontal' : 'vertical';
}

public function subir_profesor_img_controlador(){
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0){
        $archivo = $_FILES['imagen'];
        $nombreTemp = $archivo['tmp_name'];
        $nombreOriginal = basename($archivo['name']);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];

        // Validar extensión
        if(!in_array($extension, $permitidas)){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Formato no permitido",
                "Texto" => "Solo se aceptan: ".implode(", ", $permitidas),
                "Tipo" => "error"
            ]);
        }

        $carpetaDestino = "../vistas/cm/prof/";
        if(!file_exists($carpetaDestino)){
            mkdir($carpetaDestino, 0777, true);
        }

        // Asegurar nombre único y sanitizar
        $nombreSanitizado = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $nombreOriginal);
        $nombreBase = pathinfo($nombreSanitizado, PATHINFO_FILENAME);
        $ext = pathinfo($nombreSanitizado, PATHINFO_EXTENSION);
        $contador = 0;
        $nombreFinal = $nombreSanitizado;
        $rutaCompleta = $carpetaDestino.$nombreFinal;

        while(file_exists($rutaCompleta)){
            $contador++;
            $nombreFinal = $nombreBase."_".$contador.".".$ext;
            $rutaCompleta = $carpetaDestino.$nombreFinal;
        }

        $rutaRelativa = "cm/prof/".$nombreFinal;

        if(move_uploaded_file($nombreTemp, $rutaCompleta)){
            $guardar = $this->guardar_imagen_modelo($rutaRelativa, "prof");
            if($guardar->rowCount() > 0){
                $id = mainModel::conectar()->lastInsertId();
                return json_encode([
                    "Alerta" => "exito",
                    "Titulo" => "Imagen subida",
                    "Texto" => "Imagen registrada correctamente.",
                    "Tipo" => "success",
                    "ID" => $id,
                    "Ruta" => $rutaRelativa
                ]);
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error al registrar",
                    "Texto" => "La imagen no se pudo guardar en la base de datos.",
                    "Tipo" => "error"
                ]);
            }
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Fallo al mover archivo",
                "Texto" => "No se pudo guardar la imagen en el servidor.",
                "Tipo" => "error"
            ]);
        }
    } else {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Imagen no válida",
            "Texto" => "Debes seleccionar una imagen válida.",
            "Tipo" => "error"
        ]);
    }
}

















public function subir_publicidad_img_controlador(){
    if(isset($_FILES['imagen']) && $_FILES['imagen']['error'] === 0){

        $archivo = $_FILES['imagen'];
        $nombreTemp = $archivo['tmp_name'];
        $nombreOriginal = basename($archivo['name']);
        $extension = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ['png','webp']; 

        if(!in_array($extension, $permitidas)){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Formato no válido",
                "Texto" => "Solo se permiten imágenes PNG o WEBP sin fondo.",
                "Tipo" => "error"
            ]);
        }

        $dimensiones = getimagesize($nombreTemp);
        if(!$dimensiones){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Archivo inválido",
                "Texto" => "El archivo no parece ser una imagen válida.",
                "Tipo" => "error"
            ]);
        }

        // Carpeta destino
        $carpetaDestino = "../vistas/cm/fond/";
        if(!file_exists($carpetaDestino)){
            mkdir($carpetaDestino, 0777, true);
        }

        // Nombre único
        $nombreSanitizado = preg_replace('/[^a-zA-Z0-9\._-]/', '_', $nombreOriginal);
        $nombreBase = pathinfo($nombreSanitizado, PATHINFO_FILENAME);
        $ext = pathinfo($nombreSanitizado, PATHINFO_EXTENSION);

        $contador = 0;
        $nombreFinal = $nombreSanitizado;
        $rutaCompleta = $carpetaDestino.$nombreFinal;

        while(file_exists($rutaCompleta)){
            $contador++;
            $nombreFinal = $nombreBase."_".$contador.".".$ext;
            $rutaCompleta = $carpetaDestino.$nombreFinal;
        }

        $rutaRelativa = "cm/fond/".$nombreFinal;

        if(move_uploaded_file($nombreTemp, $rutaCompleta)){
            $guardar = $this->guardar_imagen_modelo($rutaRelativa, "fond");

            if($guardar->rowCount() > 0){
                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Imagen subida",
                    "Texto" => "La imagen fue guardada como ".$nombreFinal,
                    "Tipo" => "success"
                ]);
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error al guardar",
                    "Texto" => "No se pudo registrar en la base de datos.",
                    "Tipo" => "error"
                ]);
            }
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Fallo al mover archivo",
                "Texto" => "No se pudo copiar la imagen al servidor.",
                "Tipo" => "error"
            ]);
        }

    } else {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Ningún archivo recibido",
            "Texto" => "Debes seleccionar una imagen para subir.",
            "Tipo" => "error"
        ]);
    }
}






//paginador de fond y color
public function paginador_imagen_nuevo_ajax($tipo, $pagina, $por_pagina, $mostrarEliminar){
    $html = '';
    $inicio = ($pagina > 0) ? (($pagina - 1) * $por_pagina) : 0;

    if($tipo === "fond"){
        $imagenes = $this->obtener_imagenes_modelo("fond", $inicio, $por_pagina)->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->contar_imagenes_modelo("fond")->fetch()['total'];
        $total_paginas = ceil($total / $por_pagina);

        $html .= '<div class="row justify-content-center">';
        foreach($imagenes as $img){
            $html .= '
                <div class="col-md-3 mb-4 text-center">
                    <img src="'.SERVERURL.'vistas/'.$img['ruta'].'" class="img-fluid rounded shadow" alt="imagen fond" style="max-height:160px; object-fit:cover;">
                    <p class="mt-2 small text-muted">'.$img['ruta'].'</p>';

            if($mostrarEliminar){
                $html .= '
                    <form class="FormEliminarImagenAjax" action="'.SERVERURL.'ajax/imagenAjax.php" method="POST" data-form="delete">
                        <input type="hidden" name="id_imagen_del" value="'.$img['id_imagen'].'">
                        <input type="hidden" name="ruta_imagen_del" value="'.$img['ruta'].'">
                        <button type="submit" class="btn btn-danger btn-sm"><i class="fas fa-trash-alt"></i> Eliminar</button>
                    </form>';
            }

            $html .= '</div>';
        }

    }

    if($tipo === "color"){
        $total = 15;
        $total_paginas = ceil($total / $por_pagina);

        $html .= '<div class="row justify-content-center">';
        for($i = $inicio + 1; $i <= min($inicio + $por_pagina, $total); $i++){
            $ruta = "assets/img/color{$i}.webp";
            $html .= '
                <div class="col-md-3 mb-4 text-center">
                    <img src="'.SERVERURL.'vistas/'.$ruta.'" class="img-fluid rounded shadow" alt="color '.$i.'" style="max-height:160px; object-fit:cover;">
                    <p class="mt-2 small text-muted">color'.$i.'.webp</p>
                </div>';
        }
        $html .= '</div>';
    }

    // Navegación
    if($total_paginas > 1){
        $html .= '<nav aria-label="Paginación imágenes"><ul class="pagination justify-content-center">';
        for($i = 1; $i <= $total_paginas; $i++){
            $active = ($i == $pagina) ? 'active' : '';
            $html .= '<li class="page-item '.$active.'">
                <button class="page-link btn-img-paginador" data-pagina="'.$i.'" data-tipo="'.$tipo.'" data-cantidad="'.$por_pagina.'">'.$i.'</button>
            </li>';
        }
        $html .= '</ul></nav>';
    }

    return $html;
}







//paginador ajax
public function paginador_imagen_ajax($tipo, $pagina_actual, $por_pagina, $excluir){
    $inicio = ($pagina_actual > 0) ? (($pagina_actual - 1) * $por_pagina) : 0;

    $excluirIDs = [];

    if($tipo === "sldr1" && $excluir === 1){
        $excluirIDs = $this->obtener_ids_imagenes_por_slider("sldr2");
    }

    if($tipo === "sldr1" && $excluir === 2){
        $excluirIDs = $this->obtener_ids_imagenes_por_slider("sldr1");
    }

    $datos = $this->obtener_imagenes_filtradas_modelo($tipo, $excluirIDs, $inicio, $por_pagina)->fetchAll(PDO::FETCH_ASSOC);
    $total = $this->contar_imagenes_filtradas_modelo($tipo, $excluirIDs)->fetch()['total'];
    $total_paginas = ceil($total / $por_pagina);

    $html = '<div class="row justify-content-center">';

    foreach($datos as $img){
        $html .= '
        <div class="col-md-3 mb-4 text-center">
            <img src="'.SERVERURL.'vistas/'.$img['ruta'].'" class="img-fluid rounded shadow" alt="imagen disponible" style="max-height:160px; object-fit:cover;">
            <p class="mt-2 small text-muted">'.$img['ruta'].'</p>
        </div>';
    }

    $html .= '</div>';

    if($total_paginas > 1){
        $html .= '<nav aria-label="Paginador de imágenes"><ul class="pagination justify-content-center">';
        for($i = 1; $i <= $total_paginas; $i++){
            $active = ($i == $pagina_actual) ? 'active' : '';
            $html .= '<li class="page-item '.$active.'">
                <button class="page-link paginarAjax" data-page="'.$i.'">'.$i.'</button>
            </li>';
        }
        $html .= '</ul></nav>';
    }

    return $html;
}


//obtener rutas e ids de sliders

public function obtener_rutas_slider($tipo_slider){
    $imagenes = $this->obtener_imagenes_modelo($tipo_slider, 0, 8)->fetchAll(PDO::FETCH_ASSOC);
    return array_column($imagenes, 'ruta');
}

public function obtener_ids_slider($tipo_slider){
    $imagenes = $this->obtener_imagenes_modelo($tipo_slider, 0, 8)->fetchAll(PDO::FETCH_ASSOC);
    return array_column($imagenes, 'id_imagen');
}


public function obtener_imagenes_slider($tipo_slider){
    $imagenes = $this->obtener_imagenes_modelo($tipo_slider, 0, 8)->fetchAll(PDO::FETCH_ASSOC);
    
    $rutas = [];
    $ids = [];

    foreach($imagenes as $img){
        $rutas[] = $img['ruta'];
        $ids[] = $img['id_imagen'];
    }

    return ['rutas' => $rutas, 'ids' => $ids];
}


//actualizar slider

public function actualizar_slider_imagenes_controlador($slider_id, $ids_recibidos){
    $slider_id = (int)$slider_id;
    $ids_recibidos = array_map('intval', $ids_recibidos);



    $cantidad_esperada = 8;
    if ($slider_id === 3) {
        $cantidad_esperada = 6;
    }

    // Validación de cantidad y unicidad
    if(count($ids_recibidos) !== $cantidad_esperada || count(array_unique($ids_recibidos)) !== $cantidad_esperada){
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Datos inválidos",
            "Texto" => "Debes seleccionar exactamente {$cantidad_esperada} imágenes distintas.",
            "Tipo" => "error"
        ]);
    }

    $db = mainModel::conectar();

    // Verificar existencia en la base de datos
    $placeholders = implode(',', array_fill(0, count($ids_recibidos), '?'));
    $stmt = $db->prepare("SELECT COUNT(*) AS total FROM imagen WHERE id_imagen IN ($placeholders)");
    $stmt->execute($ids_recibidos);
    $total_existentes = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

    if((int)$total_existentes !== $cantidad_esperada){
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Imágenes inválidas",
            "Texto" => "Una o más imágenes no existen en el sistema.",
            "Tipo" => "error"
        ]);
    }

    // Reemplazar imágenes asociadas
    $del = $db->prepare("DELETE FROM imagen_slider WHERE id_slider_fk = ?");
    $del->execute([$slider_id]);

    $insert = $db->prepare("INSERT INTO imagen_slider (id_slider_fk, id_imagen_fk) VALUES (?, ?)");
    foreach($ids_recibidos as $id_img){
        $insert->execute([$slider_id, $id_img]);
    }

    return json_encode([
        "Alerta" => "recargar",
        "Titulo" => "Actualización exitosa",
        "Texto" => "Las imágenes del slider fueron actualizadas correctamente.",
        "Tipo" => "success"
    ]);
}







    //paginar slider 1 creo
    public function paginar_slider_imagenes_controlador($tipo, $pagina_actual, $por_pagina){
        $inicio = ($pagina_actual > 0) ? (($pagina_actual - 1) * $por_pagina) : 0;
        
        $datos = $this->obtener_imagenes_modelo($tipo, $inicio, $por_pagina)->fetchAll(PDO::FETCH_ASSOC);
        $total = $this->contar_imagenes_modelo($tipo)->fetch()['total'];
        $total_paginas = ceil($total / $por_pagina);

        $html = '<div class="row justify-content-center">';
        foreach($datos as $img){
            $html .= '
            <div class="col-md-3 mb-4 text-center">
                <img src="'.SERVERURL.'vistas/'.$img['ruta'].'" class="img-fluid rounded shadow" alt="imagen slider" style="max-height:160px; object-fit:cover;">
                <p class="mt-2 small text-muted">'.$img['ruta'].'</p>
                   
                  <form class="FormEliminarImagenAjax" action="'.SERVERURL.'ajax/imagenAjax.php" method="POST" data-form="delete" style="margin-top:10px;">
                    <input type="hidden" name="id_imagen_del" value="'.$img['id_imagen'].'">
                    <input type="hidden" name="ruta_imagen_del" value="'.$img['ruta'].'"> 
                   
                   
                    <button type="submit" class="btn btn-danger btn-sm">
                        <i class="fas fa-trash-alt"></i> Eliminar
                    </button>
                </form>

            </div>';
        }
        $html .= '</div>';

        // paginación simple
        if($total_paginas > 1){
            $html .= '<nav aria-label="Slider navigation"><ul class="pagination justify-content-center">';
            for($i = 1; $i <= $total_paginas; $i++){
                $active = ($i == $pagina_actual) ? 'active' : '';
                $html .= '<li class="page-item '.$active.'">
                    <a class="page-link" href="'.SERVERURL.'index.php?views=imagen-new&page='.$i.'">'.$i.'</a>
                </li>';
            }
            $html .= '</ul></nav>';
        }

        return $html;
    }
 


        public function eliminar_imagen_controlador($id_imagen, $ruta){
            $rutaAbsoluta = "../vistas/" . $ruta;

            $conexion = mainModel::conectar();

            // 0. Verificar si la imagen está en uso en imagen_slider
            $verificarUso = $conexion->prepare("
                SELECT COUNT(*) FROM imagen_slider WHERE id_imagen_fk = :ID
            ");
            $verificarUso->bindParam(":ID", $id_imagen, PDO::PARAM_INT);
            $verificarUso->execute();

            if($verificarUso->fetchColumn() > 0){
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Imagen en uso",
                    "Texto" => "Esta imagen está siendo utilizada en el slider. No puede eliminarse.",
                    "Tipo" => "warning"
                ]);
            }

            // 1. Eliminar el archivo físico si existe
            if(file_exists($rutaAbsoluta)){
                unlink($rutaAbsoluta);
            }

            // 2. Eliminar el registro en la tabla imagen
            $eliminada = $this->eliminar_imagen_modelo($id_imagen);
            
            if($eliminada->rowCount() > 0){
                return json_encode([
                    "Alerta" => "recargar",
                    "Titulo" => "Imagen eliminada",
                    "Texto" => "Se ha borrado correctamente.",
                    "Tipo" => "success"
                ]);
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error al eliminar",
                    "Texto" => "No se encontró el registro en la base de datos.",
                    "Tipo" => "error"
                ]);
            }
        }




    public function obtener_imagenes_por_id_slider($id_slider){
        $conexion = mainModel::conectar();

        $sql = $conexion->prepare("
            SELECT i.ruta 
            FROM imagen_slider AS isr
            INNER JOIN imagen AS i ON isr.id_imagen_fk = i.id_imagen
            WHERE isr.id_slider_fk = :SliderID
            ORDER BY i.id_imagen DESC
        ");
        $sql->bindParam(":SliderID", $id_slider, PDO::PARAM_INT);
        $sql->execute();

        $rutas = [];
        while($row = $sql->fetch(PDO::FETCH_ASSOC)){
            $rutas[] = SERVERURL.'vistas/'.$row['ruta'];
        }

        return $rutas;
    }


    //traer imagen tipo fond pa anuncio
    public function obtener_imagenes_por_tipo($tipo){
        $datos = imagenModelo::obtener_imagenes_por_tipo_modelo($tipo)->fetchAll(PDO::FETCH_ASSOC);
        return $datos;
    }






//para updtate slider

    public function paginar_slider_imagenes_update_controlador($tipo, $pagina_actual, $por_pagina, $excluir_slider_id = null) {
        $inicio = ($pagina_actual > 0) ? (($pagina_actual - 1) * $por_pagina) : 0;

        // Construir condición dinámica
        $condicionExclusion = "";
        $parametros = [":tipo" => $tipo, ":inicio" => $inicio, ":cantidad" => $por_pagina];

        if ($excluir_slider_id !== null) {
            $condicionExclusion = "AND NOT EXISTS (
                SELECT 1 FROM imagen_slider s
                WHERE s.id_imagen_fk = i.id_imagen
                AND s.id_slider_fk = :excluir
            )";
            $parametros[":excluir"] = $excluir_slider_id;
        }

        $conexion = mainModel::conectar();
        $sql = $conexion->prepare("
            SELECT i.id_imagen, i.tipo_imagen, i.ruta
            FROM imagen i
            WHERE i.tipo_imagen = :tipo
            $condicionExclusion
            ORDER BY i.id_imagen DESC
            LIMIT :inicio, :cantidad
        ");

        foreach($parametros as $key => $val){
            $sql->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
        }

        $sql->execute();
        $datos = $sql->fetchAll(PDO::FETCH_ASSOC);



    $condicionExclusionCount = "";
    $parametrosCount = [":tipo" => $tipo];

    if ($excluir_slider_id !== null) {
        $condicionExclusionCount = "AND NOT EXISTS (
            SELECT 1 FROM imagen_slider s
            WHERE s.id_imagen_fk = i.id_imagen
            AND s.id_slider_fk = :excluir
        )";
        $parametrosCount[":excluir"] = $excluir_slider_id;
    }

    $sqlCount = $conexion->prepare("
        SELECT COUNT(*) AS total
        FROM imagen i
        WHERE i.tipo_imagen = :tipo
        $condicionExclusionCount
    ");

    foreach($parametrosCount as $key => $val){
        $sqlCount->bindValue($key, $val, is_int($val) ? PDO::PARAM_INT : PDO::PARAM_STR);
    }

    $sqlCount->execute();
    $total = $sqlCount->fetch()['total'];

        $total_paginas = ceil($total / $por_pagina);

        // Generar HTML
        $html = '<div class="row justify-content-center">';
        foreach($datos as $img){
            $html .= '
            <div class="col-md-3 mb-4 text-center">
                <img src="'.SERVERURL.'vistas/'.$img['ruta'].'" class="img-fluid rounded shadow" alt="imagen slider" style="max-height:160px; object-fit:cover;">
                <p class="mt-2 small text-muted">'.htmlspecialchars($img['ruta']).'</p>
            </div>';
        }
        $html .= '</div>';

        $vista_base = 'imagen-new'; // por defecto

        if ($excluir_slider_id === 2) {
            $vista_base = 'slider1-update';
        } elseif ($excluir_slider_id === 1) {
            $vista_base = 'slider2-update';
        } elseif ($excluir_slider_id === null && $tipo === 'sldr2') {
            $vista_base = 'slider3-update';
        }


        // Paginación
        if($total_paginas > 1){
            $html .= '<nav aria-label="Slider navigation"><ul class="pagination justify-content-center">';
            for($i = 1; $i <= $total_paginas; $i++){
                $active = ($i == $pagina_actual) ? 'active' : '';
                $html .= '<li class="page-item '.$active.'">
                    <a class="page-link" href="'.SERVERURL.'index.php?views='.$vista_base.'&page='.$i.'">'.$i.'</a>
                                        
                </li>';
            }
            $html .= '</ul></nav>';
        }

        return $html;
    }







    public function imagen_disponible_update_controlador($tipo_imagen, $slider_actual, $sliders_excluir = []) {
        $conexion = mainModel::conectar();

        $sqlActuales = $conexion->prepare("
            SELECT i.id_imagen, i.ruta
            FROM imagen_slider s
            JOIN imagen i ON i.id_imagen = s.id_imagen_fk
            WHERE s.id_slider_fk = :slider_actual
            ORDER BY s.id_imagen_fk ASC
            LIMIT 8
        ");
        $sqlActuales->bindParam(":slider_actual", $slider_actual, PDO::PARAM_INT);
        $sqlActuales->execute();
        $actuales = $sqlActuales->fetchAll(PDO::FETCH_ASSOC);

        $ids_actuales = array_column($actuales, 'id_imagen');
        $rutas_actuales = array_column($actuales, 'ruta');

        $exclusion = "";
        $valores = [$tipo_imagen]; 

        if (!empty($sliders_excluir)) {
            $placeholders = implode(',', array_fill(0, count($sliders_excluir), '?'));
            $exclusion = "AND NOT EXISTS (
                SELECT 1 FROM imagen_slider s
                WHERE s.id_imagen_fk = i.id_imagen
                AND s.id_slider_fk IN ($placeholders)
            )";
            $valores = array_merge($valores, $sliders_excluir); 
        }

        $sqlDisponibles = $conexion->prepare("
            SELECT i.id_imagen, i.ruta
            FROM imagen i
            WHERE i.tipo_imagen = ?
            $exclusion
        ");

        $sqlDisponibles->execute($valores);
        $disponibles = $sqlDisponibles->fetchAll(PDO::FETCH_ASSOC);

        return [
            "ids" => $ids_actuales,
            "rutas" => $rutas_actuales,
            "disponibles" => $disponibles
        ];
    }





//imagen_disponible_update_controlador
}