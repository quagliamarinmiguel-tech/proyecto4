<?php
if($peticionAjax){
    require_once "../modelos/profesorModelo.php";
    require_once "../modelos/imagenModelo.php";
}else{
    require_once "./modelos/profesorModelo.php";
    require_once "./modelos/imagenModelo.php";
}

class profesorControlador extends profesorModelo{

public function agregar_profesor_controlador2(){
    // Obtener y validar campos básicos
    $ci = strtoupper(mainModel::limpiar_cadena($_POST['ci_reg'] ?? ''));
    $nombre = mainModel::limpiar_cadena($_POST['nombre_reg']);
    $apellido = mainModel::limpiar_cadena($_POST['apellido_reg']);
    $correo = mainModel::limpiar_cadena($_POST['correo_reg']);
    $telefono = mainModel::limpiar_cadena($_POST['telefono_reg']);
    $mostrar = mainModel::limpiar_cadena($_POST['profe_mostrar'] ?? "1");
    
    // Campos condicionales
    $descripcion = ($mostrar == "1") ? mainModel::limpiar_cadena($_POST['descripcion_reg'] ?? '') : '';
    $imagen_fk = "0"; // Valor por defecto

    // Validaciones comunes
    if($ci == "" || $nombre == "" || $apellido == "" || $correo == ""){
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Campos incompletos",
            "Texto" => "Debes completar los campos obligatorios",
            "Tipo" => "error"
        ]);
    }

    // Usar la validación universal para CI, Correo y Teléfono
    $val_ci = mainModel::validar_cedula_universal($ci, 'profesor', 'ci_profesor', null, null);
    if($val_ci['Tipo'] == 'error'){
        return json_encode($val_ci);
    }

    $val_correo = mainModel::validar_correo_universal($correo, 'profesor', 'correo', null, null);
    if($val_correo['Tipo'] == 'error'){
        return json_encode($val_correo);
    }

    if($telefono != ""){
        $val_tel = mainModel::validar_telefono_universal($telefono, 'profesor', 'telefono_profesor', null, null);
        if($val_tel['Tipo'] == 'error'){
            return json_encode($val_tel);
        }
    }

    // Validar nombre/apellido
    if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $nombre)){
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Nombre inválido",
            "Texto" => "Solo se permiten letras en el nombre.",
            "Tipo" => "error"
        ]);
    }
    if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $apellido)){
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Apellido inválido",
            "Texto" => "Solo se permiten letras en el apellido.",
            "Tipo" => "error"
        ]);
    }

    // Lógica condicional según $mostrar
    if($mostrar == "1") {
        // Validar y procesar imagen
        if(isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === 0){
            $archivo = $_FILES['archivo_imagen'];
            $nombreTemp = $archivo['tmp_name'];
            $nombreOriginal = basename($archivo['name']);
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $permitidas = ['jpg','jpeg','png','webp'];

            if(!in_array($ext, $permitidas)){
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Imagen inválida",
                    "Texto" => "Formato no permitido.",
                    "Tipo" => "error"
                ]);
            }

            $carpetaDestino = "../vistas/cm/prof/";
            if(!file_exists($carpetaDestino)){
                mkdir($carpetaDestino, 0777, true);
            }

            $nombreFinal = uniqid("prof_").".".$ext;
            $rutaCompleta = $carpetaDestino.$nombreFinal;

            if(move_uploaded_file($nombreTemp, $rutaCompleta)){
                $rutaRelativa = "cm/prof/".$nombreFinal;
                $id_imagen = (new imagenModelo())->guardar_imagen_modelo2($rutaRelativa, "prof");

                if(intval($id_imagen) > 0){
                    $imagen_fk = $id_imagen;
                } else {
                    return json_encode([
                        "Alerta" => "simple",
                        "Titulo" => "Error al guardar imagen",
                        "Texto" => "La imagen no pudo registrarse en BD.",
                        "Tipo" => "error"
                    ]);
                }
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error al mover imagen",
                    "Texto" => "No se pudo almacenar la imagen en el servidor.",
                    "Tipo" => "error"
                ]);
            }
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Imagen faltante",
                "Texto" => "Debes seleccionar una imagen válida.",
                "Tipo" => "error"
            ]);
        }

        // Validar descripción (solo cuando mostrar = 1)
        if(empty($descripcion)){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Descripción requerida",
                "Texto" => "La descripción es obligatoria cuando el profesor se muestra.",
                "Tipo" => "error"
            ]);
        }
    } else {
        // Cuando mostrar = 0, usar imagen default
        $descripcion = ''; // Forzar vacío
        
        // Copiar imagen default y crear nuevo registro
        $rutaOrigen = "../vistas/assets/img/default.webp";
        $carpetaDestino = "../vistas/cm/prof/";
        
        // Verificar que existe la imagen default
        if(!file_exists($rutaOrigen)){
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Imagen default no encontrada",
                "Texto" => "No se encontró la imagen default en: assets/img/default.webp",
                "Tipo" => "error"
            ]);
        }
        
        // Crear carpeta si no existe
        if(!file_exists($carpetaDestino)){
            mkdir($carpetaDestino, 0777, true);
        }
        
        // Generar nombre único para la copia
        $nombreCopia = uniqid("prof_default_") . ".webp";
        $rutaCompleta = $carpetaDestino . $nombreCopia;
        
        // Copiar la imagen
        if(copy($rutaOrigen, $rutaCompleta)){
            $rutaRelativa = "cm/prof/" . $nombreCopia;
            
            // Guardar en tabla imagen
            $id_imagen = (new imagenModelo())->guardar_imagen_modelo2($rutaRelativa, "prof");
            
            if(intval($id_imagen) > 0){
                $imagen_fk = $id_imagen;
            } else {
                return json_encode([
                    "Alerta" => "simple",
                    "Titulo" => "Error al guardar imagen default",
                    "Texto" => "No se pudo registrar la imagen default en la base de datos.",
                    "Tipo" => "error"
                ]);
            }
        } else {
            return json_encode([
                "Alerta" => "simple",
                "Titulo" => "Error al copiar imagen",
                "Texto" => "No se pudo copiar la imagen default a la carpeta de profesores.",
                "Tipo" => "error"
            ]);
        }
    }
        // Cuando mostrar = 0, no se requiere imagen ni descripción
      //  $descripcion = ''; // Forzar vacío
    //    $imagen_fk = "148"; // Valor por defecto para no mostrar
        
        
          
    

    // Preparar datos para el modelo
    $datos = [
        "CI" => $ci,
        "Nombre" => $nombre,
        "Apellido" => $apellido,
        "Correo" => $correo,
        "Telefono" => $telefono,
        "Descripcion" => $descripcion,
        "Imagen" => $imagen_fk,
        "Estado" => "1",
        "Mostrar" => $mostrar
    ];

    // Guardar en base de datos
    $guardar = profesorModelo::agregar_profesor_modelo($datos);
    if($guardar->rowCount() == 1){
        return json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Profesor registrado",
            "Texto" => "Los datos se guardaron correctamente.",
            "Tipo" => "success"
        ]);
    } else {
        return json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error inesperado",
            "Texto" => "No se pudo guardar el registro.",
            "Tipo" => "error"
        ]);
    }
}

    
public function agregar_profesor_controlador(){
    $ci = mainModel::limpiar_cadena($_POST['ci_reg']);
    $nombre = mainModel::limpiar_cadena($_POST['nombre_reg']);
    $apellido = mainModel::limpiar_cadena($_POST['apellido_reg']);
    $correo = mainModel::limpiar_cadena($_POST['correo_reg']);
    $telefono = mainModel::limpiar_cadena($_POST['telefono_reg']);
    $descripcion = mainModel::limpiar_cadena($_POST['descripcion_reg']);
    $imagen_fk = mainModel::limpiar_cadena($_POST['imagen_fk']);
    $estado = "1";
    $mostrar = mainModel::limpiar_cadena($_POST['profe_mostrar'] ?? "1");

        if(!is_numeric($imagen_fk) || intval($imagen_fk) <= 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Imagen no asociada",
                "Texto"=>"No se ha recibido correctamente el ID de la imagen. Espera unos segundos tras subir la imagen antes de guardar.",
                "Tipo"=>"error"
            ]);
        }

        $verif_img = mainModel::ejecutar_consulta_simple("SELECT id_imagen FROM imagen WHERE id_imagen = '$imagen_fk'");
        if($verif_img->rowCount() == 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Imagen no encontrada",
                "Texto"=>"El ID de imagen enviado no coincide con ninguna imagen guardada.",
                "Tipo"=>"error"
            ]);
        }


    $datos = [
        "CI"=>$ci,
        "Nombre"=>$nombre,
        "Apellido"=>$apellido,
        "Correo"=>$correo,
        "Telefono"=>$telefono,
        "Descripcion"=>$descripcion,
        "Imagen"=>$imagen_fk,
        "Estado"=>$estado,
        "Mostrar"=>$mostrar
    ];

    $guardar = profesorModelo::agregar_profesor_modelo($datos);
    if($guardar->rowCount() == 1){
        return json_encode([
            "Alerta"=>"recargar",
            "Titulo"=>"Profesor registrado",
            "Texto"=>"Los datos se guardaron correctamente.",
            "Tipo"=>"success"
        ]);
    } else {
        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"Error inesperado",
            "Texto"=>"No se pudo guardar el registro.",
            "Tipo"=>"error"
        ]);
    }
}




public function agregar_profesor_con_imagen_controlador(){

    // Validar todos los campos primero
    //$ci = mainModel::limpiar_cadena($_POST['ci_reg']);
    $ci = strtoupper(mainModel::limpiar_cadena($_POST['ci_reg'] ?? ''));
    $nombre = mainModel::limpiar_cadena($_POST['nombre_reg']);
    $apellido = mainModel::limpiar_cadena($_POST['apellido_reg']);
    $correo = mainModel::limpiar_cadena($_POST['correo_reg']);
    $telefono = mainModel::limpiar_cadena($_POST['telefono_reg']);
    $descripcion = mainModel::limpiar_cadena($_POST['descripcion_reg']);





        // Campos obligatorios
        if($ci=="" || $nombre=="" || $apellido=="" || $correo==""){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar los campos obligatorios",
                "Tipo"=>"error"
            ]);
        }

        // Usar la validación universal para CI, Correo y Teléfono
        $val_ci = mainModel::validar_cedula_universal($ci, 'profesor', 'ci_profesor', null, null);
        if($val_ci['Tipo'] == 'error'){
            return json_encode($val_ci);
        }

        $val_correo = mainModel::validar_correo_universal($correo, 'profesor', 'correo', null, null);
        if($val_correo['Tipo'] == 'error'){
            return json_encode($val_correo);
        }

        if($telefono != ""){
            $val_tel = mainModel::validar_telefono_universal($telefono, 'profesor', 'telefono_profesor', null, null);
            if($val_tel['Tipo'] == 'error'){
                return json_encode($val_tel);
            }
        }

        // Validar nombre/apellido
        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $nombre)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Nombre inválido",
                "Texto"=>"Solo se permiten letras en el nombre.",
                "Tipo"=>"error"
            ]);
        }
        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $apellido)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Apellido inválido",
                "Texto"=>"Solo se permiten letras en el apellido.",
                "Tipo"=>"error"
            ]);
        }


    // Subir imagen primero
    if(isset($_FILES['archivo_imagen']) && $_FILES['archivo_imagen']['error'] === 0){
        $archivo = $_FILES['archivo_imagen'];
        $nombreTemp = $archivo['tmp_name'];
        $nombreOriginal = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];

        if(!in_array($ext, $permitidas)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Imagen inválida",
                "Texto"=>"Formato no permitido.",
                "Tipo"=>"error"
            ]);
        }

        $carpetaDestino = "../vistas/cm/prof/";
        if(!file_exists($carpetaDestino)){
            mkdir($carpetaDestino, 0777, true);
        }

        $nombreFinal = uniqid("prof_").".".$ext;
        $rutaCompleta = $carpetaDestino.$nombreFinal;

        if(move_uploaded_file($nombreTemp, $rutaCompleta)){
            $rutaRelativa = "cm/prof/".$nombreFinal;
            $id_imagen = (new imagenModelo())->guardar_imagen_modelo2($rutaRelativa, "prof");

            if(intval($id_imagen) > 0){

                // Ejecutar flujo tradicional con imagen_fk ya disponible
                $_POST['imagen_fk'] = strval($id_imagen); 
                return $this->agregar_profesor_controlador();
            } else {
                return json_encode([
                    "Alerta"=>"simple",
                    "Titulo"=>"Error al guardar imagen",
                    "Texto"=>"La imagen no pudo registrarse en BD.",
                    "Tipo"=>"error"
                ]);
            }
        } else {
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al mover imagen",
                "Texto"=>"No se pudo almacenar la imagen en el servidor.",
                "Tipo"=>"error"
            ]);
        }
    } else {
        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"Imagen faltante",
            "Texto"=>"Debes seleccionar una imagen válida.",
            "Tipo"=>"error"
        ]);
    }




}




public function paginador_profesor_controlador($pagina, $registros, $vista, $busqueda){
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $busqueda  = mainModel::limpiar_cadena($busqueda);
    $inicio    = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    
    
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
    
    
    $conexion = mainModel::conectar();

    // Filtros adicionales
    $filtro_id     = $_GET['filtro_id']     ?? "";
    $filtro_ci     = $_GET['filtro_ci']     ?? "";
    $filtro_nombre = $_GET['filtro_nombre'] ?? "";
    $orden         = $_GET['orden']         ?? "default";

    // WHERE dinámico
    $where = "p.estado_profesor = '1'";
    if ($busqueda != "") {
        $where .= " AND (p.nombre_profesor LIKE '%$busqueda%' OR p.apellido_profesor LIKE '%$busqueda%' OR p.ci_profesor LIKE '%$busqueda%')";
    }
    if ($filtro_id != "") {
        $where .= " AND p.id_profesor = '$filtro_id'";
    }
    if ($filtro_ci != "") {
        $where .= " AND p.ci_profesor LIKE '%$filtro_ci%'";
    }
    if ($filtro_nombre != "") {
        $where .= " AND p.id_profesor = '$filtro_nombre'";
    }

    // Ordenamiento
    $order_sql = "ORDER BY p.id_profesor DESC";
    if ($orden == "alfabetico") {
        $order_sql = "ORDER BY p.nombre_profesor ASC, p.apellido_profesor ASC";
    }

    // Consulta
    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS  
            p.id_profesor, p.ci_profesor, p.nombre_profesor, p.estado_profesor, p.apellido_profesor,
            i.ruta AS ruta_imagen
        FROM profesor p
        INNER JOIN imagen i ON p.imagen_profesor_fk = i.id_imagen
        WHERE $where
        $order_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    
    $cargo = $_SESSION['cargo_spm'] ?? 0;

$tabla = '<div class="table-responsive">
    <table class="table table-dark table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>Id</th>
                <th>Cédula</th>
                <th>Nombre</th>
                <th>Periodos impartidos</th>
                <th>Imagen</th>
                <th>Ver clases impartidas</th>';

if ($cargo == 1 || $cargo == 4) {
    $tabla .= '
                <th>Actualizar</th>
                <th>Archivar</th>';
}

$tabla .= '
            </tr>
        </thead>
        <tbody>';
    
    
    
    
    
    
    
    
    
    

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;
        foreach ($datos as $row) {
            $idEnc = mainModel::encryption($row['id_profesor']);

            // Conteo de cursos vigentes asignados
            $conteoCursos = $conexion->query("
                SELECT COUNT(DISTINCT cv.id_curso_vigente)
                FROM seccion s
                INNER JOIN curso_vigente cv ON s.id_curso_vigente_fk = cv.id_curso_vigente
                INNER JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
                WHERE s.ci_profesor = '{$row['id_profesor']}' AND c.estado_cohorte = 'Vigente' AND c.archivar_cohorte = 1 AND cv.estado_curso_vigente = 1
            ")->fetchColumn();

            
            
            $tabla .= '
    <tr class="text-center">
        <td>' . $row['id_profesor'] . '</td>
        <td>' . htmlspecialchars($row['ci_profesor']) . '</td>
        <td>' . htmlspecialchars($row['nombre_profesor'] . ' ' . $row['apellido_profesor']) . '</td>
        <td>' . $conteoCursos . '</td>
        <td>
            <button type="button" class="btn btn-info btn-sm ver-imagen" data-src="' . SERVERURL . 'vistas/' . $row['ruta_imagen'] . '">
                <i class="fas fa-eye"></i>
            </button>
        </td>


            
<td class="text-center">
    <div class="btn-group" role="group">
        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/cv-int-list&filtro_profesor=' . urlencode($row['id_profesor']) . '&origen=profe" class="btn btn-sm btn-info" title="Ver clases intensivas">
            <i class="fas fa-bolt"></i> Intensivos 
        </a>
        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-list&filtro_profesor=' . urlencode($row['id_profesor']) . '&origen=profe" class="btn btn-sm btn-secondary" title="Ver clases por cohorte">
            <i class="fas fa-users"></i> Cohortes
        </a>
    </div>
</td>';
            
            
            
if ($cargo == 1 || $cargo == 4) {
    $tabla .= '
        <td>
       		<a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/profesor-update&profesor_id_up=' . $idEnc . '" class="btn btn-success btn-sm">
                <i class="fas fa-sync-alt"></i>
            </a>
        </td>
        <td>
            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/profesorAjax.php" method="POST" data-form="delete" data-entity="profesor" autocomplete="off">
                <input type="hidden" name="profesor_id_archive" value="' . $idEnc . '">
                <button type="submit" class="btn btn-warning btn-sm">
                    <i class="fas fa-archive"></i>
                </button>
            </form>
        </td>';
}

$tabla .= '</tr>';
            
            
            
            
            
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="7">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-primary btn-sm">Recargar listado</a>'
            : 'No hay profesores registrados';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando profesor ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'busqueda' => $busqueda,
            'filtro_id' => $filtro_id,
            'filtro_ci' => $filtro_ci,
            'filtro_nombre' => $filtro_nombre,
            'orden' => $orden
        ]);

        // enlace de seccion list             <a href="' . SERVERURL . 'index.php?views=seccion-list&filtro_profesor=' . $row['id_profesor'] . '&origen=profe" class="btn btn-sm btn-primary">
        // profe update                         <a href="' . SERVERURL . 'index.php?views=profesor-update&profesor_id_up=' . $idEnc . '" class="btn btn-success btn-sm">
        // recargar listado                         ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-primary btn-sm">Recargar listado</a>'
        
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        //$urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    // Modal de imagen
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






public function paginador_profesor_archivado_controlador($pagina, $registros, $vista, $busqueda) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $busqueda  = mainModel::limpiar_cadena($busqueda);

    
    
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
    
    
    
    // Filtros tipo curso archivado
    $filtro_id     = isset($_GET['filtro_id'])     ? mainModel::limpiar_cadena($_GET['filtro_id'])     : "";
    $filtro_ci     = isset($_GET['filtro_ci'])     ? mainModel::limpiar_cadena($_GET['filtro_ci'])     : "";
    $filtro_nombre = isset($_GET['filtro_nombre']) ? mainModel::limpiar_cadena($_GET['filtro_nombre']) : "";
    $orden         = isset($_GET['orden'])         ? $_GET['orden'] : "";

    $orden_sql = "ORDER BY p.id_profesor DESC";
    if ($orden == "alfabetico") {
        $orden_sql = "ORDER BY p.nombre_profesor ASC";
    }

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    $conexion = mainModel::conectar();

    // WHERE dinámico
    $where = "p.estado_profesor = 0";
    if ($busqueda != "") {
        $where .= " AND (p.nombre_profesor LIKE '%$busqueda%' OR p.apellido_profesor LIKE '%$busqueda%' OR p.ci_profesor LIKE '%$busqueda%')";
    }
    if ($filtro_id != "") {
        $where .= " AND p.id_profesor = '$filtro_id'";
    }
    if ($filtro_ci != "") {
        $where .= " AND p.ci_profesor LIKE '%$filtro_ci%'";
    }
    if ($filtro_nombre != "") {
        $where .= " AND p.id_profesor = '$filtro_nombre'";
    }

    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            p.id_profesor, p.ci_profesor, p.nombre_profesor, p.apellido_profesor,
            p.telefono_profesor, p.correo,
            i.ruta AS ruta_imagen
        FROM profesor p
        INNER JOIN imagen i ON p.imagen_profesor_fk = i.id_imagen
        WHERE $where
        $orden_sql
        LIMIT $inicio, $registros
    ";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int)$conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Img</th>
                    <th>Id</th>
                    <th>Ci</th>
                    <th>Nombre</th>
                    <th>Apellido</th>
                    <th>Teléfono</th>
                    <th>Correo</th>
                    <th>Restaurar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $rows) {
            $idEnc = mainModel::encryption($rows['id_profesor']);
            $tabla .= '
                <tr class="text-center">
                    <td>
                        <button type="button" class="btn btn-info btn-sm ver-imagen" data-src="' . SERVERURL . 'vistas/' . $rows['ruta_imagen'] . '">
                            <i class="fas fa-eye"></i>
                        </button>
                    </td>
                    <td>' . $rows['id_profesor'] . '</td>
                    <td>' . $rows['ci_profesor'] . '</td>
                    <td>' . $rows['nombre_profesor'] . '</td>
                    <td>' . $rows['apellido_profesor'] . '</td>
                    <td>' . $rows['telefono_profesor'] . '</td>
                    <td>' . $rows['correo'] . '</td>

                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/profesorAjax.php" method="POST" data-form="restore" autocomplete="off">
                            <input type="hidden" name="profesor_id_restore" value="' . $idEnc . '">
                            <button type="submit" class="btn btn-info btn-sm">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="8">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay profesores archivados en el sistema';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    // Paginador con filtros persistentes
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando profesor ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'busqueda' => $busqueda,
            'filtro_id' => $filtro_id,
            'filtro_ci' => $filtro_ci,
            'filtro_nombre' => $filtro_nombre,
            'orden' => $orden
        ]);

                  //  ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
//        $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    // Modal de imagen
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











    /*--------- Datos profesor ---------*/
    public function datos_profesor_controlador($tipo, $id){
        $tipo = mainModel::limpiar_cadena($tipo);
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);
        return profesorModelo::datos_profesor_modelo($tipo, $id);
    }

    /*--------- Eliminar profesor ---------*/
    public function eliminar_profesor_controlador(){
        $id = mainModel::decryption($_POST['profesor_id_del']);
        $id = mainModel::limpiar_cadena($id);

        $check = mainModel::ejecutar_consulta_simple("SELECT id_profesor FROM profesor WHERE id_profesor='$id'");
        if($check->rowCount()<=0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"No existe",
                "Texto"=>"Este profesor no se encuentra registrado.",
                "Tipo"=>"error"
            ]);
        }

        session_start(['name'=>'SPM']);
        if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Sin permisos",
                "Texto"=>"No estás autorizado para eliminar profesores.",
                "Tipo"=>"error"
            ]);
        }

        $eliminar = profesorModelo::eliminar_profesor_modelo($id);
        if($eliminar->rowCount()==1){
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Profesor eliminado",
                "Texto"=>"El registro se eliminó correctamente.",
                "Tipo"=>"success"
            ]);
        }else{
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error",
                "Texto"=>"No se pudo eliminar el profesor.",
                "Tipo"=>"error"
            ]);
        }
    }



public function actualizar_profesor_con_imagen_controlador(){
    try {
        $idEnc = $_POST['profesor_id_up'] ?? '';
        $idDec = mainModel::decryption($idEnc);

        if(!is_numeric($idDec) || $idDec <= 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"ID inválido",
                "Texto"=>"No se recibió un ID válido de profesor.",
                "Tipo"=>"error"
            ]);
        }

        // validar datos antes de procesar imagen
        $ci = mainModel::limpiar_cadena($_POST['ci_reg'] ?? '');
        $nombre = mainModel::limpiar_cadena($_POST['nombre_reg'] ?? '');
        $apellido = mainModel::limpiar_cadena($_POST['apellido_reg'] ?? '');
        $correo = mainModel::limpiar_cadena($_POST['correo_reg'] ?? '');
        $telefono = mainModel::limpiar_cadena($_POST['telefono_reg'] ?? '');
        $descripcion = mainModel::limpiar_cadena($_POST['descripcion_reg'] ?? '');
        $mostrar = mainModel::limpiar_cadena($_POST['profe_mostrar'] ?? '');

        if($ci=="" || $nombre=="" || $apellido=="" || $correo==""){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar los campos obligatorios.",
                "Tipo"=>"error"
            ]);
        }

        if(!preg_match('/^[VE]{1}[0-9]{5,8}$/', $ci)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"CI inválida",
                "Texto"=>"La cédula debe comenzar con V o E seguida de 5 a 8 números.",
                "Tipo"=>"error"
            ]);
        }

        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $nombre)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Nombre inválido",
                "Texto"=>"Solo se permiten letras en el nombre.",
                "Tipo"=>"error"
            ]);
        }

        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $apellido)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Apellido inválido",
                "Texto"=>"Solo se permiten letras en el apellido.",
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

        if($telefono!="" && !preg_match('/^[0-9]{7,12}$/', $telefono)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Teléfono inválido",
                "Texto"=>"Solo se permiten entre 7 y 12 dígitos numéricos.",
                "Tipo"=>"error"
            ]);
        }

        if(!isset($_FILES['archivo_imagen']) || $_FILES['archivo_imagen']['error'] !== 0){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Imagen inválida",
                "Texto"=>"No se pudo procesar la imagen enviada.",
                "Tipo"=>"error"
            ]);
        }

        $archivo = $_FILES['archivo_imagen'];
        $ext = strtolower(pathinfo($archivo['name'], PATHINFO_EXTENSION));
        $permitidas = ['jpg','jpeg','png','webp'];
        if(!in_array($ext, $permitidas)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Formato no permitido",
                "Texto"=>"La imagen debe estar en formato JPG, PNG o WEBP.",
                "Tipo"=>"error"
            ]);
        }

        $nombreFinal = uniqid("prof_").".".$ext;
        $rutaRelativa = "cm/prof/".$nombreFinal;
        $rutaCompleta = "../vistas/".$rutaRelativa;

        if(!move_uploaded_file($archivo['tmp_name'], $rutaCompleta)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Error al guardar imagen",
                "Texto"=>"No se pudo almacenar la imagen en el servidor.",
                "Tipo"=>"error"
            ]);
        }

        // Obtener imagen actual para actualizar ruta
        $conexion = mainModel::conectar();
        $query = $conexion->prepare("
            SELECT i.id_imagen, i.ruta
            FROM profesor p
            INNER JOIN imagen i ON p.imagen_profesor_fk = i.id_imagen
            WHERE p.id_profesor = :ID
        ");
        $query->bindParam(":ID", $idDec, PDO::PARAM_INT);
        $query->execute();

        if($query->rowCount() != 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Imagen no encontrada",
                "Texto"=>"No se pudo obtener la imagen actual.",
                "Tipo"=>"error"
            ]);
        }

        $img = $query->fetch();
        $rutaAnterior = "../vistas/".$img['ruta'];
        $id_imagen_actual = $img['id_imagen'];

        //  Borrar imagen anterior del disco
        if(file_exists($rutaAnterior)) unlink($rutaAnterior);


        $updateRuta = imagenModelo::actualizar_ruta_imagen_modelo($id_imagen_actual, $rutaRelativa);
        $updateRuta->execute();



        return $this->actualizar_profesor_controlador();

    } catch (Exception $e){
        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"Error interno",
            "Texto"=>"Detalles: ".$e->getMessage(),
            "Tipo"=>"error"
        ]);
    }
}

//subir cosas actualizadas
public function actualizar_profesor_controlador(){
    $idEnc = $_POST['profesor_id_up'] ?? '';
    $idDec = mainModel::decryption($idEnc);

    if(!is_numeric($idDec) || $idDec <= 0){
        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"ID inválido",
            "Texto"=>"No se recibió un ID válido de profesor.",
            "Tipo"=>"error"
        ]);
    }

    // Campos limpios
    $ci = strtoupper(mainModel::limpiar_cadena($_POST['ci_reg'] ?? ''));
    $nombre = mainModel::limpiar_cadena($_POST['nombre_reg'] ?? '');
    $apellido = mainModel::limpiar_cadena($_POST['apellido_reg'] ?? '');
    $correo = mainModel::limpiar_cadena($_POST['correo_reg'] ?? '');
    $telefono = mainModel::limpiar_cadena($_POST['telefono_reg'] ?? '');
    $descripcion = mainModel::limpiar_cadena($_POST['descripcion_reg'] ?? '');
    $mostrar = mainModel::limpiar_cadena($_POST['profe_mostrar'] ?? '');
    $imagenCambiada = intval($_POST['imagen_cambiada'] ?? "0");

    // Validaciones finales 
    if($ci=="" || !preg_match('/^[VE]{1}[0-9]{5,9}$/', $ci)){
        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"CI inválida",
            "Texto"=>"La cédula debe comenzar con V o E seguida de 5 a 9 números.",
            "Tipo"=>"error"
        ]);
    }



        // Campos obligatorios
        if($ci=="" || $nombre=="" || $apellido=="" || $correo==""){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Campos incompletos",
                "Texto"=>"Debes completar los campos obligatorios",
                "Tipo"=>"error"
            ]);
        }

        // CI formato
        if(!preg_match('/^[VE]{1}[0-9]{5,9}$/', $ci)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"CI inválida",
                "Texto"=>"La cédula debe comenzar con V o E seguida de 5 a 8 números.",
                "Tipo"=>"error"
            ]);
        }

        // Validar nombre/apellido
        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $nombre)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Nombre inválido",
                "Texto"=>"Solo se permiten letras en el nombre.",
                "Tipo"=>"error"
            ]);
        }
        if(mainModel::verificar_datos('[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{1,20}', $apellido)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Apellido inválido",
                "Texto"=>"Solo se permiten letras en el apellido.",
                "Tipo"=>"error"
            ]);
        }

        // Validar correo
        if(!filter_var($correo, FILTER_VALIDATE_EMAIL)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo inválido",
                "Texto"=>"Debes ingresar un correo electrónico válido.",
                "Tipo"=>"error"
            ]);
        }

        // Validar teléfono
        if($telefono!="" && !preg_match('/^[0-9]{7,12}$/', $telefono)){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Teléfono inválido",
                "Texto"=>"Solo se permiten entre 7 y 12 dígitos numéricos.",
                "Tipo"=>"error"
            ]);
        }

        // Validar que CI o correo no esté repetido
        $check_ci = mainModel::ejecutar_consulta_simple("SELECT ci_profesor FROM profesor WHERE ci_profesor='$ci'");
        if($check_ci->rowCount() > 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Duplicado",
                "Texto"=>"Ya existe un profesor con esa cédula.",
                "Tipo"=>"error"
            ]);
        }

        $check_correo = mainModel::ejecutar_consulta_simple("SELECT correo FROM profesor WHERE correo='$correo'");
        if($check_correo->rowCount() > 1){
            return json_encode([
                "Alerta"=>"simple",
                "Titulo"=>"Correo registrado",
                "Texto"=>"Ya hay un profesor registrado con ese correo.",
                "Tipo"=>"error"
            ]);
        }




    // Armar arreglo para modelo
    $datos = [
        "CI" => $ci,
        "Nombre" => $nombre,
        "Apellido" => $apellido,
        "Correo" => $correo,
        "Telefono" => $telefono,
        "Descripcion" => $descripcion,
        "Mostrar" => $mostrar,
        "ID" => $idDec
    ];

    // Ejecutar update
    $actualizar = profesorModelo::actualizar_profesor_modelo($datos);

    if($actualizar->rowCount() == 1){
        return json_encode([
            "Alerta"=>"recargar",
            "Titulo"=>"Actualización exitosa",
            "Texto"=>"Los datos del profesor fueron actualizados correctamente.",
            "Tipo"=>"success"
        ]);
    } else {
        if($imagenCambiada === 1){
            return json_encode([
                "Alerta"=>"recargar",
                "Titulo"=>"Imagen actualizada",
                "Texto"=>"Se reemplazó la imagen del profesor exitosamente.",
                "Tipo"=>"success"
            ]);
        }

        return json_encode([
            "Alerta"=>"simple",
            "Titulo"=>"Sin cambios",
            "Texto"=>"No se detectaron modificaciones en los datos.",
            "Tipo"=>"info"
        ]);
    }







}






















public function archivar_profesor_controlador() {
    $id = mainModel::decryption($_POST['profesor_id_archive']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia del profesor
    $check_prof = mainModel::ejecutar_consulta_simple("SELECT id_profesor FROM profesor WHERE id_profesor = '$id'");
    if ($check_prof->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "El profesor que intenta archivar no existe en el sistema",
            "Tipo" => "error"
        ]);
        exit();
    }

    // Verificar privilegios
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Permiso denegado",
            "Texto" => "No tienes los permisos necesarios para realizar esta operación",
            "Tipo" => "error"
        ]);
        exit();
    }

    $conexion = mainModel::conectar();

    try {
        $conexion->beginTransaction();

        // Archivar profesor
        $conexion->exec("UPDATE profesor SET estado_profesor = 0 WHERE id_profesor = '$id'");

        // Obtener secciones del profesor
        $secciones = $conexion->query("SELECT id_seccion FROM seccion WHERE ci_profesor = '$id'")->fetchAll();

        foreach ($secciones as $sec) {
            $idSec = $sec['id_seccion'];

            // Archivar sección
            $conexion->exec("UPDATE seccion SET archivar_seccion = 0 WHERE id_seccion = '$idSec'");

            // Obtener evaluaciones
            $evaluaciones = $conexion->query("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = '$idSec'")->fetchAll();

            foreach ($evaluaciones as $eval) {
                $idEval = $eval['id_evaluacion'];

                // Archivar evaluación
                $conexion->exec("UPDATE evaluacion SET archivar_evaluacion = 0 WHERE id_evaluacion = '$idEval'");

                // Archivar puntajes
                $conexion->exec("UPDATE puntaje_estudiante SET archivar_puntaje = 0 WHERE id_evaluacion = '$idEval'");
            }

            // Actualizar observaciones en inscripciones
            $conexion->exec("
                UPDATE inscripcion
                SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | profesor archivado o inactivo')
                WHERE id_seccion_fk = '$idSec'
            ");
        }

        $conexion->commit();

        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Profesor archivado",
            "Texto" => "El profesor y sus registros relacionados han sido archivados exitosamente",
            "Tipo" => "success"
        ]);
    } catch (Exception $e) {
        $conexion->rollBack();
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al archivar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}



public function restaurar_profesor_controlador() {
    $id = mainModel::decryption($_POST['profesor_id_restore']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia
    $check_prof = mainModel::ejecutar_consulta_simple("SELECT id_profesor FROM profesor WHERE id_profesor = '$id'");
    if ($check_prof->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "El profesor no existe",
            "Tipo" => "error"
        ]);
        exit();
    }

    $pdo = mainModel::conectar();
    try {
        $pdo->beginTransaction();

        $countsRestored = ['profesor'=>0,'secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $countsOmitted = ['secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $omittedExamples = [];

        $pushExample = function($tipo,$id) use (&$omittedExamples) {
            if (!isset($omittedExamples[$tipo])) $omittedExamples[$tipo] = [];
            if (count($omittedExamples[$tipo]) < 10) $omittedExamples[$tipo][] = $id;
        };

        // Restaurar profesor
        $pdo->exec("UPDATE profesor SET estado_profesor = 1 WHERE id_profesor = '$id'");
        $countsRestored['profesor'] = 1;

        // Obtener secciones del profesor
        $stmtSec = $pdo->prepare("SELECT id_seccion, id_curso_vigente_fk FROM seccion WHERE ci_profesor = :id");
        $stmtSec->execute([':id' => $id]);
        $secciones = $stmtSec->fetchAll();

        $ps_check_cv = $pdo->prepare("SELECT estado_curso_vigente FROM curso_vigente WHERE id_curso_vigente = :idCV");
        $ps_up_seccion = $pdo->prepare("UPDATE seccion SET archivar_seccion = 1 WHERE id_seccion = :idSec");

        $ps_get_evals = $pdo->prepare("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = :idSec");
        $ps_up_eval = $pdo->prepare("UPDATE evaluacion SET archivar_evaluacion = 1 WHERE id_evaluacion = :idEval");
        $ps_up_puntaje = $pdo->prepare("UPDATE puntaje_estudiante SET archivar_puntaje = 1 WHERE id_evaluacion = :idEval");

        $ps_check_insc = $pdo->prepare("
            SELECT archivada FROM inscripcion 
            WHERE id_inscripcion = (
                SELECT id_inscripcion FROM puntaje_estudiante 
                WHERE id_evaluacion = :idEval LIMIT 1
            )
        ");

        $ps_up_obs = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | profesor reactivado')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%profesor reactivado%'
        ");

        foreach ($secciones as $sec) {
            $idSec = (int)$sec['id_seccion'];
            $idCV  = (int)$sec['id_curso_vigente_fk'];

            // Verificar curso vigente activo
            $ps_check_cv->execute([':idCV' => $idCV]);
            $cvActivo = $ps_check_cv->fetchColumn() == 1;

            if (! $cvActivo) {
                $countsOmitted['secciones'] += 1;
                $pushExample('secciones', $idSec);
                continue;
            }

            // Restaurar sección
            $ps_up_seccion->execute([':idSec' => $idSec]);
            $countsRestored['secciones'] += 1;

            // Restaurar evaluaciones
            $ps_get_evals->execute([':idSec' => $idSec]);
            while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
                $idEval = (int)$eval['id_evaluacion'];

                $ps_up_eval->execute([':idEval' => $idEval]);
                $countsRestored['evaluaciones'] += 1;

                // Verificar inscripción activa
                $ps_check_insc->execute([':idEval' => $idEval]);
                $inscRow = $ps_check_insc->fetch(PDO::FETCH_ASSOC);
                $inscActiva = $inscRow && (int)$inscRow['archivada'] === 1;

                if (! $inscActiva) {
                    $countsOmitted['puntajes'] += 1;
                    $pushExample('puntajes', $idEval);
                    continue;
                }

                $ps_up_puntaje->execute([':idEval' => $idEval]);
                $countsRestored['puntajes'] += $ps_up_puntaje->rowCount() ?: 1;
            }

            // Actualizar observaciones
            $ps_up_obs->execute([':idSec' => $idSec]);
        }

        $pdo->commit();

        $totalOmitidos = array_sum($countsOmitted);
        if ($totalOmitidos > 0) {
            $texto = "Profesor restaurado, pero se omitieron: " .
                $countsOmitted['secciones'] . " sección(es), " .
                $countsOmitted['evaluaciones'] . " evaluación(es), " .
                $countsOmitted['puntajes'] . " puntaje(s).";
            $tipo = "warning";
        } else {
            $texto = "Profesor restaurado exitosamente. Todos los registros relacionados fueron activados.";
            $tipo = "success";
        }

        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Restauración completada",
            "Texto" => $texto,
            "Tipo" => $tipo,
            "restored" => $countsRestored,
            "omitted" => $countsOmitted,
            "examples" => $omittedExamples
        ]);
    } catch (Exception $e) {
        $pdo->rollBack();
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error al restaurar",
            "Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            "Tipo" => "error"
        ]);
    }
}

}