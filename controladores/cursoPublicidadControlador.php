<?php
if($peticionAjax){
    require_once "../modelos/imagenModelo.php";
    require_once "../modelos/cursoPublicidadModelo.php";

} else {
    require_once "./modelos/imagenModelo.php";
    require_once "./modelos/cursoPublicidadModelo.php";
}
class cursoPublicidadControlador extends cursoPublicidadModelo {


    public function agregar_publicidad_con_imagen_controlador() {
        // Validaciones iniciales
        $id_curso = mainModel::limpiar_cadena($_POST['id_curso'] ?? '');
        $prefacio = mainModel::limpiar_cadena($_POST['prefacio'] ?? '');
        $clase = mainModel::limpiar_cadena($_POST['clase'] ?? '');
        $descripcion = mainModel::limpiar_cadena($_POST['materia_descripcion'] ?? '');

        $cont = [];
        for($i = 1; $i <= 10; $i++) {
            $cont["cont$i"] = mainModel::limpiar_cadena($_POST["cont$i"] ?? '');
        }

        $req = [];
        for($i = 1; $i <= 4; $i++) {
            $req["requisito$i"] = mainModel::limpiar_cadena($_POST["requisito$i"] ?? '');
        }

        $iconos = [];
        for($i = 1; $i <= 3; $i++) {
            $iconos["icono$i"] = isset($_POST["icono$i"]) ? 1 : 0;
        }

$camposFaltantes = [];

if($id_curso == "") $camposFaltantes[] = "Curso";
if($prefacio == "") $camposFaltantes[] = "Prefacio";
if($descripcion == "") $camposFaltantes[] = "Descripción";
if($clase == "") $camposFaltantes[] = "Horario";

if(!empty($camposFaltantes)){
    $listaCampos = implode(", ", $camposFaltantes);
    return json_encode([
        "Alerta" => "simple",
        "Titulo" => "Campos obligatorios faltantes",
        "Texto"  => "Debes completar los siguientes campos: $listaCampos.",
        "Tipo"   => "error"
    ]);
}


        $verificar = mainModel::conectar()->prepare("SELECT COUNT(*) FROM curso_publicidad WHERE id_curso = :id AND estado_curso_publicidad = 1");
        $verificar->bindParam(":id", $id_curso, PDO::PARAM_INT);
        $verificar->execute();
        $existePublicidad = $verificar->fetchColumn();

        if($existePublicidad > 0){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Ya existe publicidad",
        "Texto"  => "Este curso ya tiene ".$id_curso." publicidad registrada.",
                "Tipo"   => "error"
            ]);

        }




        // Validar archivo
        if(!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] !== 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Imagen faltante",
                "Texto"=>"Debes seleccionar una imagen válida.",
                "Tipo"=>"error"
            ]);
        }

        $archivo = $_FILES['archivo_imagen'];
        $nombreTemp = $archivo['tmp_name'];
        $nombreOriginal = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];

        if(!in_array($ext, $permitidas)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Formato no permitido",
                "Texto"=>"Solo se permiten imágenes JPG, PNG o WEBP.",
                "Tipo"=>"error"
            ]);
        }

        // Carpeta de destino
        $carpetaDestino = "../vistas/cm/curs-p/";
        if(!file_exists($carpetaDestino)){
            mkdir($carpetaDestino, 0777, true);
        }

        $nombreFinal = uniqid("pub_").".".$ext;
        $rutaCompleta = $carpetaDestino.$nombreFinal;
        $rutaRelativa = "cm/curs-p/".$nombreFinal;

        if(!move_uploaded_file($nombreTemp, $rutaCompleta)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al subir imagen",
                "Texto"=>"La imagen no pudo almacenarse.",
                "Tipo"=>"error"
            ]);
        }

        // Guardar ruta en imagen y obtener ID
        $id_imagen = (new imagenModelo())->guardar_imagen_modelo2($rutaRelativa, "curs-p");

        if(intval($id_imagen) <= 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al registrar imagen",
                "Texto"=>"No se pudo guardar la imagen en BD.",
                "Tipo"=>"error"
            ]);
        }

        // Reunir todos los datos
        $datos = [
            "id_curso" => $id_curso,
            "id_imagen_materia_fk" => $id_imagen,
            "prefacio" => $prefacio,
            "clase" => $clase,
            "materia_descripcion" => $descripcion,
        ] + $cont + $req + $iconos;

        $registro = cursoPublicidadModelo::agregar_publicidad_modelo($datos);

        if($registro->rowCount() == 1){
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Publicidad registrada",
                "Texto"=>"Los datos fueron almacenados correctamente.",
                "Tipo"=>"success"
            ]);
        } else {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error inesperado",
                "Texto"=>"No se pudo guardar la publicidad.",
                "Tipo"=>"error"
            ]);
        }
    }








public function paginador_publicidad_controlador($pagina, $registros, $vista) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    
    
    $conexion = mainModel::conectar();

    // Filtros
    $filtro_id     = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_tipo   = mainModel::limpiar_cadena($_GET['filtro_tipo'] ?? '');
    $filtro_estado = mainModel::limpiar_cadena($_GET['estado'] ?? '');
    $orden         = $_GET['orden'] ?? '';
    
    
    
    $cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
      $vistaCompleta = urlencode($carpeta) . '/' . $vista;

    

    // WHERE base
    $where = "cp.estado_curso_publicidad = 1 AND c.estado_curso = 1";
    if ($filtro_id != "")     $where .= " AND cp.id_materia = '$filtro_id'";
    if ($filtro_nombre != "") $where .= " AND c.nombre_curso = '$filtro_nombre'";
    if ($filtro_tipo != "")   $where .= " AND c.tipo_curso = '$filtro_tipo'";
    if ($filtro_estado != "") {
        $where .= " AND (
            SELECT CASE
                WHEN EXISTS (
                    SELECT 1 FROM curso_vigente cv2
                    INNER JOIN cohorte ch ON cv2.id_cohorte_fk = ch.id_cohorte
                    WHERE cv2.id_curso_fk = c.id_curso AND ch.estado_cohorte = 'Vigente'
                ) THEN 'Vigente'
                WHEN EXISTS (
                    SELECT 1 FROM curso_vigente cv3
                    INNER JOIN cohorte ch ON cv3.id_cohorte_fk = ch.id_cohorte
                    WHERE cv3.id_curso_fk = c.id_curso AND ch.estado_cohorte = 'Próximo'
                ) THEN 'Próximo'
                ELSE 'No vigente'
            END
        ) = '$filtro_estado'";
    }

    // Ordenamiento
    $orden_sql = match($orden) {
        "nombre"        => "ORDER BY c.nombre_curso ASC",
        "interacciones" => "ORDER BY interacciones DESC",
        "reciente"      => "ORDER BY cp.id_materia DESC",
        default         => ""
    };

    // Consulta principal
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS
            cp.id_materia,
            c.nombre_curso,
            c.tipo_curso,
            cp.prefacio,
            i.ruta AS ruta_imagen,
            (
                SELECT CASE
                    WHEN EXISTS (
                        SELECT 1 FROM curso_vigente cv2
                        INNER JOIN cohorte ch ON cv2.id_cohorte_fk = ch.id_cohorte
                        WHERE cv2.id_curso_fk = c.id_curso AND ch.estado_cohorte = 'Vigente' AND ch.archivar_cohorte = 1
                    ) THEN 'Vigente'
                    WHEN EXISTS (
                        SELECT 1 FROM curso_vigente cv3
                        INNER JOIN cohorte ch ON cv3.id_cohorte_fk = ch.id_cohorte
                        WHERE cv3.id_curso_fk = c.id_curso AND ch.estado_cohorte = 'Próximo' AND ch.archivar_cohorte = 1
                    ) THEN 'Próximo'
                    ELSE 'No vigente'
                END
            ) AS estado_publicidad,
            (
                SELECT COUNT(*) FROM interaccion_banner ib
                WHERE ib.id_curso_fk = c.id_curso
            ) AS interacciones
        FROM curso_publicidad cp
        INNER JOIN curso c ON cp.id_curso = c.id_curso
        LEFT JOIN imagen i ON cp.id_imagen_materia_fk = i.id_imagen
        WHERE $where
        $orden_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Id</th>
                    <th>Curso</th>
                    <th>Tipo</th>
                    <th>Estado</th>
                    <th>Interacciones</th>
                    <th>Prefacio</th>
                    <th>Imagen</th>
                    <th>Actualizar</th>
                    <th>Archivar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;
        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_materia']);
            $estadoColor = match($row['estado_publicidad']) {
                'Vigente'  => 'success',
                'Próximo'  => 'info',
                default    => 'secondary'
            };

            $tabla .= '
                <tr class="text-center">
                    <td>' . $row['id_materia'] . '</td>
                    <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                    <td>' . htmlspecialchars($row['tipo_curso']) . '</td>
                    <td><span style="color:white;" class="badge bg-' . $estadoColor . '">' . htmlspecialchars($row['estado_publicidad']) . '</span></td>
                    <td><span style="color:white;" class="badge bg-primary">' . $row['interacciones'] . '</span></td>
                    <td>' . htmlspecialchars($row['prefacio']) . '</td>
                    <td>
                        <button type="button" class="btn btn-info btn-sm ver-imagen" data-src="' . SERVERURL . 'vistas/' . $row['ruta_imagen'] . '">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                    <td>
                    <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cursop-update&publicidad_id_up=' . $idEnc . '" class="btn btn-success btn-sm">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/publicidadAjax.php" method="POST" data-form="archive" autocomplete="off">
                            <input type="hidden" name="publicidad_id_archive" value="' . $idEnc . '">
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-archive"></i>
                            </button>
                        </form>
                    </td>
                </tr>';
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="9">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/' . $vista . '" class="btn btn-primary btn-sm">Recargar listado</a>'
            : 'No hay registros de publicidad disponibles';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';
//            ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-primary btn-sm">Recargar listado</a>'
//                        <a href="' . SERVERURL . 'index.php?views=cursop-update&publicidad_id_up=' . $idEnc . '" class="btn btn-success btn-sm">

    
    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'filtro_id'     => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_tipo'   => $filtro_tipo,
            'estado'        => $filtro_estado,
            'orden'         => $orden
        ]);

        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
//        $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    // Modal para imagen

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
}









public function paginador_publicidad_archivada_controlador($pagina, $registros, $vista) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $conexion = mainModel::conectar();

    // Filtros
    $filtro_id     = mainModel::limpiar_cadena($_GET['filtro_id'] ?? '');
    $filtro_nombre = mainModel::limpiar_cadena($_GET['filtro_nombre'] ?? '');
    $filtro_tipo   = mainModel::limpiar_cadena($_GET['filtro_tipo'] ?? '');
    $orden         = $_GET['orden'] ?? '';

    $cargo = $_SESSION['cargo_spm'] ?? 1;

$carpetasPorCargo = [
    1 => "admin",
    2 => "Control de estudio",
    3 => "administrador de pagos",
    4 => "Supervisor de cursos",
    5 => "Estudiante"
];

$carpeta = $carpetasPorCargo[$cargo] ?? "admin";
$vistaCompleta = urlencode($carpeta) . '/' . $vista;
    
    
    // WHERE base
    $where = "cp.estado_curso_publicidad = 0";
    if ($filtro_id != "")     $where .= " AND cp.id_materia = '$filtro_id'";
    if ($filtro_nombre != "") $where .= " AND c.nombre_curso = '$filtro_nombre'";
    if ($filtro_tipo != "")   $where .= " AND c.tipo_curso = '$filtro_tipo'";

    // Ordenamiento
    $orden_sql = match($orden) {
        "nombre" => "ORDER BY c.nombre_curso ASC",
        default  => "ORDER BY cp.id_materia DESC"
    };

    // Consulta principal
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            cp.id_materia, 
            c.nombre_curso, 
            c.tipo_curso,
            cp.prefacio
        FROM curso_publicidad cp
        INNER JOIN curso c ON cp.id_curso = c.id_curso
        WHERE $where
        $orden_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int)$conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    // Tabla HTML
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>CURSO</th>
                    <th>TIPO</th>
                    <th>PREFACIO</th>
                    <th>RESTAURAR</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_materia']);
            $tabla .= '
            <tr class="text-center">
                <td>' . $row['id_materia'] . '</td>
                <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                <td>' . htmlspecialchars($row['tipo_curso']) . '</td>
                <td>' . htmlspecialchars($row['prefacio']) . '</td>
                <td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/publicidadAjax.php" method="POST" data-form="restore" autocomplete="off">
                        <input type="hidden" name="publicidad_id_restore" value="' . $idEnc . '">
                        <button type="submit" class="btn btn-info btn-sm">
                            <i class="fas fa-undo"></i> Restaurar
                        </button>
                    </form>
                </td>
            </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="5">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-primary btn-sm">Recargar listado</a>'
            : 'No hay publicidades archivadas';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando registros ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'filtro_id'     => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
            'filtro_tipo'   => $filtro_tipo,
            'orden'         => $orden
        ]);

        //            ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-primary btn-sm">Recargar listado</a>'

        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        //$urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}




















    public function actualizar_publicidad_con_imagen_controlador() {
        try {
            $idEnc = $_POST['publicidad_id_up'] ?? '';
            $idDec = mainModel::decryption($idEnc);

            if (!is_numeric($idDec) || $idDec <= 0) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "ID inválido",
                    "Texto" => "No se recibió un ID válido para actualizar la publicidad.",
                    "Tipo" => "error"
                ]);
            }

            $prefacio = mainModel::limpiar_cadena($_POST['prefacio'] ?? '');
            $descripcion = mainModel::limpiar_cadena($_POST['materia_descripcion'] ?? '');
            $clase = mainModel::limpiar_cadena($_POST['clase'] ?? '');

            if ($prefacio == "" || $descripcion == "" || $clase == "") {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Campos requeridos vacíos",
                    "Texto" => "Debes completar el prefacio, horario y la descripción.",
                    "Tipo" => "error"
                ]);
            }

            // Validar imagen
            if (!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] !== 0) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Imagen faltante",
                    "Texto" => "No se pudo procesar la imagen enviada.",
                    "Tipo" => "error"
                ]);
            }

            $archivo = $_FILES['archivo_imagen'];
            $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp'];

            if (!in_array($ext, $permitidas)) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Formato inválido",
                    "Texto" => "La imagen debe estar en formato JPG, PNG o WEBP.",
                    "Tipo" => "error"
                ]);
            }

            $nombreFinal = uniqid("pub_") . "." . $ext;
            $rutaRelativa = "cm/curs-p/" . $nombreFinal;
            $rutaCompleta = "../vistas/" . $rutaRelativa;

            if (!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error de carga",
                    "Texto" => "No se pudo almacenar la nueva imagen.",
                    "Tipo" => "error"
                ]);
            }

            // Obtener imagen actual
            $conexion = mainModel::conectar();
            $query = $conexion->prepare("
                SELECT i.id_imagen, i.ruta
                FROM curso_publicidad cp
                INNER JOIN imagen i ON cp.id_imagen_materia_fk = i.id_imagen
                WHERE cp.id_materia = :ID
            ");
            $query->bindParam(":ID", $idDec, PDO::PARAM_INT);
            $query->execute();

            if ($query->rowCount() != 1) {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Imagen no encontrada",
                    "Texto" => "No se pudo localizar la imagen actual.",
                    "Tipo" => "error"
                ]);
            }

            $img = $query->fetch();
            $rutaAnterior = "../vistas/" . $img['ruta'];
            $id_imagen_actual = $img['id_imagen'];

            if (file_exists($rutaAnterior)) unlink($rutaAnterior);

            $updateRuta = imagenModelo::actualizar_ruta_imagen_modelo($id_imagen_actual, $rutaRelativa);
            $updateRuta->execute();

            // Continuar con actualización general
            $_POST['imagen_cambiada'] = 1;
            return $this->actualizar_publicidad_controlador();

        } catch (Exception $e) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error interno",
                "Texto" => "Detalles: " . $e->getMessage(),
                "Tipo" => "error"
            ]);
        }
    }









    public function actualizar_publicidad_controlador() {
        $idEnc = $_POST['publicidad_id_up'] ?? '';
        $idDec = mainModel::decryption($idEnc);

        if (!is_numeric($idDec) || $idDec <= 0) {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "ID inválido",
                "Texto" => "No se recibió un ID válido para la publicidad.",
                "Tipo" => "error"
            ]);
        }

        $prefacio = mainModel::limpiar_cadena($_POST['prefacio'] ?? '');
        $clase = mainModel::limpiar_cadena($_POST['clase'] ?? '');
        $descripcion = mainModel::limpiar_cadena($_POST['materia_descripcion'] ?? '');
        $imagenCambiada = intval($_POST['imagen_cambiada'] ?? 0);

        if ($prefacio == "" || $descripcion == "") {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Campos vacíos",
                "Texto" => "Prefacio y descripción son obligatorios.",
                "Tipo" => "error"
            ]);
        }

        $datos = [
            "prefacio" => $prefacio,
            "materia_descripcion" => $descripcion,
            "id_materia" => $idDec,
            "clase"=>$clase
        ];

        for ($i = 1; $i <= 10; $i++) {
            $datos["cont$i"] = mainModel::limpiar_cadena($_POST["cont$i"] ?? '');
        }

        for ($i = 1; $i <= 4; $i++) {
            $datos["requisito$i"] = mainModel::limpiar_cadena($_POST["requisito$i"] ?? '');
        }


        for ($i = 1; $i <= 3; $i++) {
            $datos["icono$i"] = isset($_POST["icono$i"]) ? 1 : 0;
        }        

        $update = cursoPublicidadModelo::actualizar_publicidad_modelo($datos);

        if ($update->rowCount() == 1) {
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Publicidad actualizada",
                "Texto" => "Los datos se han guardado correctamente.",
                "Tipo" => "success"
            ]);
        } elseif ($imagenCambiada === 1) {
            return json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Imagen actualizada",
                "Texto" => "La imagen fue reemplazada exitosamente.",
                "Tipo" => "success"
            ]);
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Sin cambios",
                "Texto" => "No se detectaron modificaciones.",
                "Tipo" => "info"
            ]);
        }
    }




    public function eliminar_publicidad_controlador(){
        $idEnc = $_POST['publicidad_id_del'] ?? '';
        $idDec = mainModel::decryption($idEnc);

        if(!is_numeric($idDec) || $idDec <= 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"ID inválido",
                "Texto"=>"No se recibió un ID válido de publicidad.",
                "Tipo"=>"error"
            ]);
        }

        // Conectar y obtener info de imagen
        $conexion = mainModel::conectar();
        $query = $conexion->prepare("
            SELECT cp.id_materia, i.id_imagen, i.ruta
            FROM curso_publicidad cp
            INNER JOIN imagen i ON cp.id_imagen_materia_fk = i.id_imagen
            WHERE cp.id_materia = :ID
        ");
        $query->bindParam(":ID", $idDec, PDO::PARAM_INT);
        $query->execute();

        if($query->rowCount() != 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"No encontrado",
                "Texto"=>"No se encontró la publicidad.",
                "Tipo"=>"error"
            ]);
        }

        $fila = $query->fetch();
        $id_imagen = $fila['id_imagen'];
        $ruta = "../vistas/".$fila['ruta'];

        // Borrar la publicidad
        $delPub = cursoPublicidadModelo::eliminar_publicidad_modelo($idDec);
        if($delPub->rowCount() != 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al eliminar",
                "Texto"=>"No se pudo eliminar la publicidad.",
                "Tipo"=>"error"
            ]);
        }

        // Borrar imagen física
        if(file_exists($ruta)) unlink($ruta);

        // Borrar registro de imagen
        $delImg = $conexion->prepare("DELETE FROM imagen WHERE id_imagen = :ID");
        $delImg->bindParam(":ID", $id_imagen, PDO::PARAM_INT);
        $delImg->execute();

        return json_encode([
            "Alerta"=>"recargar",
            "Titulo"=>"Eliminado correctamente",
            "Texto"=>"La publicidad fue eliminada del sistema.",
            "Tipo"=>"success"
        ]);
    }





    public function archivar_publicidad_controlador(){
        $idEnc = mainModel::limpiar_cadena($_POST['publicidad_id_archive']);
        $id = mainModel::decryption($idEnc);

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare("UPDATE curso_publicidad SET estado_curso_publicidad = 0 WHERE id_materia = :ID");
        $stmt->bindParam(":ID", $id, PDO::PARAM_INT);

        if($stmt->execute()){
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Publicidad archivada",
                "Texto" => "La publicidad fue movida correctamente al archivo.",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error",
                "Texto" => "No se pudo archivar la publicidad.",
                "Tipo" => "error"
            ];
        }

        return json_encode($alerta);
    }








    public function restaurar_publicidad_controlador(){
        $idEnc = mainModel::limpiar_cadena($_POST['publicidad_id_restore']);
        $id = mainModel::decryption($idEnc);

        $conexion = mainModel::conectar();
        $stmt = $conexion->prepare("UPDATE curso_publicidad SET estado_curso_publicidad = 1 WHERE id_materia = :ID");
        $stmt->bindParam(":ID", $id, PDO::PARAM_INT);

        if($stmt->execute()){
            $alerta = [
                "Alerta" => "recargar",
                "Titulo" => "Publicidad restaurada",
                "Texto" => "La publicidad fue reactivada correctamente.",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Error inesperado",
                "Texto" => "No se pudo restaurar la publicidad.",
                "Tipo" => "error"
            ];
        }

        return json_encode($alerta);
    }












}