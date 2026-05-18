<?php

if($peticionAjax){
    require_once "../modelos/anuncioModelo.php";
}else{
    require_once "./modelos/anuncioModelo.php";
}

class anuncioControlador extends anuncioModelo {

    /*--------- Agregar anuncio ---------*/
    public function agregar_anuncio_controlador(){
        $titulo = mainModel::limpiar_cadena($_POST['titulo_reg']);
        $texto = mainModel::limpiar_cadena($_POST['texto_reg']);
        $fondo = mainModel::limpiar_cadena($_POST['fondo_reg']);
        $imagen_id = (int) $_POST['imagen_reg'];
        $curso_id = (int) $_POST['curso_reg'];

        // Validaciones básicas
        if($titulo == "" || $texto == "" || $imagen_id == 0 || $curso_id == 0){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Campos incompletos",
                "Texto" => "Debes completar todos los campos obligatorios.",
                "Tipo" => "error"
            ]);
        }

        $conexion = mainModel::conectar();

        // Validar si imagen existe
        $checkImg = $conexion->prepare("SELECT id_imagen FROM imagen WHERE id_imagen = ?");
        $checkImg->execute([$imagen_id]);
        if($checkImg->rowCount() != 1){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Imagen inválida",
                "Texto" => "La imagen seleccionada no existe.",
                "Tipo" => "error"
            ]);
        }

        // Validar si curso existe
        $checkCurso = $conexion->prepare("SELECT id_curso FROM curso WHERE id_curso = ?");
        $checkCurso->execute([$curso_id]);
        if($checkCurso->rowCount() != 1){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Curso inválido",
                "Texto" => "El curso seleccionado no existe.",
                "Tipo" => "error"
            ]);
        }

        // Insertar anuncio
        $stmt = $conexion->prepare("
            INSERT INTO anuncio_publicidad (titulo_publicidad, texto_publicidad, fondo_publicidad, imagen_publicidad_fk, link_publicidad)
            VALUES (?, ?, ?, ?, ?)
        ");

        $exito = $stmt->execute([$titulo, $texto, $fondo, $imagen_id, $curso_id]);

        if($exito){
            return json_encode([
                "Alerta" => "limpiar",
                "Titulo" => "Anuncio creado",
                "Texto" => "El anuncio fue registrado correctamente.",
                "Tipo" => "success"
            ]);
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error inesperado",
                "Texto" => "No se pudo guardar el anuncio.",
                "Tipo" => "error"
            ]);
        }
    }

    /*--------- Datos de anuncio ---------*/
    public function datos_anuncio_controlador($tipo, $id){
        $tipo = mainModel::limpiar_cadena($tipo);
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);

        return anuncioModelo::datos_anuncio_modelo($tipo, $id);
    }

    /*--------- Eliminar anuncio ---------*/
    public function eliminar_anuncio_controlador(){
        $id = mainModel::decryption($_POST['publicidad_id_del']);
        $id = mainModel::limpiar_cadena($id);

        $stmt = mainModel::conectar()->prepare("DELETE FROM anuncio_publicidad WHERE id_publicidad = ?");
        $stmt->execute([$id]);

        if($stmt->rowCount() == 1){
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Anuncio eliminado",
                "Texto" => "El anuncio fue borrado del sistema.",
                "Tipo" => "success"
            ]);
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error al eliminar",
                "Texto" => "No se pudo eliminar el anuncio.",
                "Tipo" => "error"
            ]);
        }
    }


public function paginador_anuncio_controlador($pagina, $registros, $url, $busqueda){
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $url = SERVERURL."index.php?views=".mainModel::limpiar_cadena($url)."&page=";
    $busqueda = mainModel::limpiar_cadena($busqueda);
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $conexion = mainModel::conectar();

    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            a.id_publicidad, a.titulo_publicidad, 
            i.ruta AS ruta_imagen, 
            c.nombre_curso
        FROM anuncio_publicidad a
        INNER JOIN imagen i ON a.imagen_publicidad_fk = i.id_imagen
        INNER JOIN curso c ON a.link_publicidad = c.id_curso
        ORDER BY a.id_publicidad DESC
        LIMIT $inicio, $registros
    ";

    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>TÍTULO</th>
                    <th>CURSO</th>
                    <th>IMAGEN</th>
                    <th>ACTUALIZAR</th>
                    <th>ELIMINAR</th>
                </tr>
            </thead>
            <tbody>';

    if($total >= 1 && $pagina <= $Npaginas){
        $reg_inicio = $inicio + 1;
        foreach($datos as $row){
            $idEnc = mainModel::encryption($row['id_publicidad']);
            $tabla .= '
            <tr class="text-center">
                <td>'.$row['id_publicidad'].'</td>
                <td>'.htmlspecialchars($row['titulo_publicidad']).'</td>
                <td>'.htmlspecialchars($row['nombre_curso']).'</td>
                <td>
                    <button type="button" class="btn btn-info btn-sm ver-imagen" data-src="'.SERVERURL.'vistas/'.$row['ruta_imagen'].'">
                        <i class="fas fa-eye"></i>
                    </button>
                </td>
                <td>
                    <a href="'.SERVERURL.'index.php?views=anuncio-update&anuncio_id_up='.$idEnc.'" class="btn btn-success btn-sm">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </td>
                <td>
                    <form class="FormularioAjax" action="'.SERVERURL.'ajax/anuncioAjax.php" method="POST" data-form="delete" autocomplete="off">
                        <input type="hidden" name="publicidad_id_del" value="'.$idEnc.'">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>';
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="6">';
        $tabla .= ($total >= 1) 
            ? '<a href="'.$url.'" class="btn btn-primary btn-sm">Recargar listado</a>' 
            : 'No hay anuncios registrados';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    if($total >= 1 && $pagina <= $Npaginas){
        $tabla .= '<p class="text-right">Mostrando anuncio '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
    }

    // Modal emergente para ver imagen (solo se inyecta una vez)
    $tabla .= '
    <div id="modalImagen" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75);">
        <div style="position:relative; margin:auto; top:10%; max-width:60%; background:#000; border:3px solid #fff;">
            <img id="imgPreview" src="" class="img-thumbnail" style="max-width:400px; width:80%; height:auto; display:block; margin:auto; border:3px solid white;">
            <button onclick="document.getElementById(\'modalImagen\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                <i class="fas fa-times"></i>
            </button>
        </div>
    </div>

    <script>
    document.querySelectorAll(".ver-imagen").forEach(btn => {
        btn.addEventListener("click", () => {
            const modal = document.getElementById("modalImagen");
            modal.querySelector("#imgPreview").src = btn.dataset.src;
            modal.style.display = "block";
        });
    });
    </script>';

    return $tabla;
    //            <img id="imgPreview" src="" class="img-fluid" style="width:100%; height:auto;">

}


//no usar este soy gafo olvide q lo tenia
public function paginador_anuncio_controladorviejoporfanousar($pagina, $registros, $url, $busqueda){
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $url = SERVERURL."index.php?views=".mainModel::limpiar_cadena($url)."&page=";
    $busqueda = mainModel::limpiar_cadena($busqueda);
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $conexion = mainModel::conectar();

    // Consulta con JOIN a imagen, curso y cohorte
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            a.id_publicidad, a.titulo_publicidad, a.texto_publicidad, a.fondo_publicidad,
            i.ruta AS ruta_imagen,
            c.nombre_curso,
            coh.estado_cohorte
        FROM anuncio_publicidad a
        INNER JOIN imagen i ON a.imagen_publicidad_fk = i.id_imagen
        INNER JOIN curso c ON a.link_publicidad = c.id_curso
        INNER JOIN cohorte coh ON c.id_cohorte_fk = coh.id_cohorte
        ORDER BY a.id_publicidad DESC
        LIMIT $inicio, $registros
    ";

    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Tabla de resultados
    $tabla = '<div class="table-responsive"><table class="table table-dark table-bordered table-sm">
        <thead><tr class="text-center roboto-medium">
            <th>#</th>
            <th>TÍTULO</th>
            <th>IMAGEN</th>
            <th>CURSO</th>
            <th>FONDO</th>
            <th>VIGENTE</th>
            <th>ACTUALIZAR</th>
            <th>ELIMINAR</th>
        </tr></thead><tbody>';

    if($total >= 1 && $pagina <= $Npaginas){
        $reg_inicio = $inicio + 1;
        foreach($datos as $row){
            $vigente = ($row['estado_cohorte'] === "Vigente") ? "Sí" : "No";
            $idEnc = mainModel::encryption($row['id_publicidad']);

            $tabla .= '
            <tr class="text-center">
                <td>'.$row['id_publicidad'].'</td>
                <td>'.htmlspecialchars($row['titulo_publicidad']).'</td>
                <td><img src="'.SERVERURL.'vistas/'.$row['ruta_imagen'].'" style="height:40px;"></td>
                <td>'.htmlspecialchars($row['nombre_curso']).'</td>
                <td><small>'.htmlspecialchars($row['fondo_publicidad']).'</small></td>
                <td>'.$vigente.'</td>
                <td>
                    <a href="'.SERVERURL.'index.php?views=publicidad-update&publicidad_id_up='.$idEnc.'" class="btn btn-success btn-sm">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </td>
                <td>
                    <form class="FormularioAjax" action="'.SERVERURL.'ajax/anuncioAjax.php" method="POST" data-form="delete" autocomplete="off">
                        <input type="hidden" name="publicidad_id_del" value="'.$idEnc.'">
                        <button type="submit" class="btn btn-danger btn-sm">
                            <i class="fas fa-trash-alt"></i>
                        </button>
                    </form>
                </td>
            </tr>';
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="8">';
        $tabla .= ($total >= 1) 
            ? '<a href="'.$url.'" class="btn btn-primary btn-sm">Haga clic aquí para recargar el listado</a>' 
            : 'No hay anuncios registrados';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    if($total >= 1 && $pagina <= $Npaginas){
        $tabla .= '<p class="text-right">Mostrando anuncio '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
    }

    return $tabla;
}











    /*--------- Actualizar anuncio ---------*/
    public function actualizar_anuncio_controlador(){
        $id = mainModel::decryption($_POST['publicidad_id_up']);
        $id = mainModel::limpiar_cadena($id);

        $titulo = mainModel::limpiar_cadena($_POST['titulo_up']);
        $texto = mainModel::limpiar_cadena($_POST['texto_up']);
        $fondo = mainModel::limpiar_cadena($_POST['fondo_up']);
        $imagen_id = (int) $_POST['imagen_up'];
        $curso_id = (int) $_POST['curso_up'];

        if($titulo == "" || $texto == "" || $imagen_id == 0 || $curso_id == 0){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Campos vacíos",
                "Texto" => "Todos los campos son obligatorios.",
                "Tipo" => "error"
            ]);
        }

        $conexion = mainModel::conectar();

        $stmt = $conexion->prepare("
            UPDATE anuncio_publicidad SET 
                titulo_publicidad = ?, texto_publicidad = ?, fondo_publicidad = ?, 
                imagen_publicidad_fk = ?, link_publicidad = ?
            WHERE id_publicidad = ?
        ");
        $exito = $stmt->execute([$titulo, $texto, $fondo, $imagen_id, $curso_id, $id]);

        if($exito){
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Anuncio actualizado",
                "Texto" => "Los datos del anuncio fueron modificados.",
                "Tipo" => "success"
            ]);
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error al actualizar",
                "Texto" => "No se pudo modificar el anuncio.",
                "Tipo" => "error"
            ]);
        }
    }
}