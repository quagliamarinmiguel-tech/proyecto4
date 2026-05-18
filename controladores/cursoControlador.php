<?php

if($peticionAjax){
    require_once "../modelos/cursoModelo.php";
}else{
    require_once "./modelos/cursoModelo.php";
}

class cursoControlador extends cursoModelo{
    
    
    
    //--------------- Validaciones -------------
    
    public function validar_curso_no_duplicado($nombre, $tipo, $id=null) {
        return cursoModelo::verificar_curso_duplicado($nombre, $tipo, $id);
    }
    
    public function validar_formato_nombre($nombre) {
        $patron = "[a-zA-ZáéíóúÁÉÍÓÚñÑ](?:[a-zA-ZáéíóúÁÉÍÓÚñÑ ]{0,59})";
        return mainModel::verificar_datos($patron, $nombre);  // false = cumple, true = no cumple
    }
    
    public function validar_tipo_curso($tipo_curso){
    	$tipos_validos = ["Intensivo", "Extendido", "Técnico Profesional"];
        if (!in_array($tipo_curso, $tipos_validos)) {
            return false;
  		}
        return true;
    }
    
    //------------------------------------------
    

    
    //---------------- datos que se traen del curso---------------------

    

    	//de course update, para verificar si un curso cuenta con un periodo activo
        public function verificar_curso_periodo_existente($course_id){
            $id= mainModel::decryption($course_id);

            //return cursoModelo:: verificar_curso_periodo_existente_modelo($id);
            $resultado = cursoModelo::verificar_curso_periodo_existente_modelo($id);
            
            if($resultado->rowCount() > 0) {
                $fila = $resultado->fetch();
                return $fila['tipo_curso'];  
            } else {
                return false;  
            }
        }
    
    
        //para course-list, si se tiene un filtro de nombre, se buscara para cargarlo en el input del filtro
        public function obtener_nombre_curso_para_filtro_controlador($id_curso) {
            $id_curso = mainModel::limpiar_cadena($id_curso);

            if(!is_numeric($id_curso)) {
                return false;
            }
            $resultado= cursoModelo::obtener_nombre_curso_para_filtro_modelo($id_curso);

            if($resultado && $resultado->rowCount() > 0) {
                return $resultado->fetch(PDO::FETCH_ASSOC); 
            }

        }

    
        // Controlador datos curso, el general q trae todo lo q se usa pa update de curso 
    //si
    public function datos_curso_controlador($tipo,$id){
        $tipo=mainModel::limpiar_cadena($tipo);
        $id=mainModel::decryption($id);
        $id=mainModel::limpiar_cadena($id);

        return cursoModelo::datos_curso_modelo($tipo,$id);
    } 

    
    
    //------------------------------------------------------------------
    
    
    
    
    

    /*--------- Controlador agregar curso ---------*/
    public function agregar_curso_controlador(){

        $nombre=mainModel::limpiar_cadena($_POST['curso_nombre_reg']);
        $tipo=mainModel::limpiar_cadena($_POST['curso_tipo_reg']);
        
        if($nombre=="" || $tipo==""){
            mainModel::generarAlertaControlador("No has llenado todos los campos que son obligatorios","error_simple");
        }
        
        if($this->validar_curso_no_duplicado($nombre, $tipo)) {
            mainModel::generarAlertaControlador("Ya existe un curso del mismo nombre y tipo","error_simple");
        }
        
        if($this-> validar_formato_nombre($nombre)){
          mainModel::generarAlertaControlador("El nombre del curso no coincide con el formato solicitado","error_simple");
        }
        
        if(!$this->validar_tipo_curso($tipo)) {
           mainModel::generarAlertaControlador("Debe seleccionar un tipo de curso válido (Intensivo, Extendido, Técnico Profesional)","error_simple");
        }
   
		$estado = "1";
        $datos_curso_reg=[
            "Nombre"=>$nombre,
            "Tipo"=>$tipo,
            "Estado"=>$estado
        ];

        $agregar_curso=cursoModelo::agregar_curso_modelo($datos_curso_reg);

        if($agregar_curso->rowCount()==1){
            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"Curso registrado",
                "Texto"=>"Los datos del curso han sido registrados con exito",
                "Tipo"=>"success"
            ];
        }else{
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No hemos podido registrar el curso",
                "Tipo"=>"error"
            ];
        }

        echo json_encode($alerta);
    }



    /*--------- Controlador actualizar curso ---------*/
    public function actualizar_curso_controlador(){

        $id=mainModel::limpiar_cadena($_POST['curso_id_up']);
        
        if(!mainModel::verificar_existencia_id("id_curso","curso",$id)){
        	mainModel::generarAlertaControlador("No se encontró el id del curso","error_simple");
        }
        
        $nombre=mainModel::limpiar_cadena($_POST['curso_nombre_up']);
        $tipo=mainModel::limpiar_cadena($_POST['curso_tipo_up']);
        
        if($nombre=="" || $tipo==""){
            mainModel::generarAlertaControlador("No has llenado todos los campos que son obligatorios","error_simple");
        }
        
        if($this->validar_curso_no_duplicado($nombre, $tipo,$id)) {
            mainModel::generarAlertaControlador("Ya existe un curso del mismo nombre y tipo","error_simple");
        }
        
        if($this-> validar_formato_nombre($nombre)){
          mainModel::generarAlertaControlador("El nombre del curso no coincide con el formato solicitado","error_simple");
        }
        
        if(!$this->validar_tipo_curso($tipo)) {
           mainModel::generarAlertaControlador("Debe seleccionar un tipo de curso válido (Intensivo, Extendido, Técnico Profesional)","error_simple");
        }
        
        if(mainModel::verificar_sin_permiso(4)){
        	mainModel::generarAlertaControlador("No tienes los permisos necesarios para realizar esta operación","error_simple");
        }
        
        $datos_curso_up=[
            "Nombre"=>$nombre,
            "Tipo"=>$tipo,
            "ID"=>$id
        ];

        if(cursoModelo::actualizar_curso_modelo($datos_curso_up)){
            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"Datos actualizados",
                "Texto"=>"Los datos del curso han sido actualizados con exito",
                "Tipo"=>"success"
            ];
        }else{
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No hemos podido actualizar los datos, por favor intente nuevamente",
                "Tipo"=>"error"
            ];
        }

        echo json_encode($alerta);
    } /* Fin controlador */

    
    
    
    
    
    
    
    
    
    
    

    /*
                    <td>
                    <a href="' . SERVERURL . 'index.php?views=course-vigente-list&curso_id=' . mainModel::encryption($rows['id_curso']) . '" class="btn btn-info btn-sm" title="Ver cursos vigentes">
                        <i class="fas fa-eye"></i> Ver
                    </a>
                </td>
 
                     <a href="' . SERVERURL . 'index.php?views=seccion-list&filtro_id_curso=' . $rows['id_curso'] . '&origen=curso" class="btn btn-info btn-sm" title="Ver cursos vigentes">
                        <i class="fas fa-eye"></i>
                    </a>
 */
    
    
    
    
    
    
    
    
    
    
    
    

    
    
    
    
    
    
    
    
    
    
    
public function paginador_curso_controlador($pagina, $registros, $vista, $busqueda) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista);
    $busqueda  = mainModel::limpiar_cadena($busqueda);
            $cargo = $_SESSION['cargo_spm'] ?? 0;

    $orden = $_GET['orden'] ?? "";
    $orden_sql = match($orden) {
        "tipo"  => "ORDER BY tipo_curso ASC",
        "fecha" => "ORDER BY id_curso DESC",
        default => "ORDER BY nombre_curso ASC"
    };

    $filtro_tipo       = $_GET['filtro_tipo']       ?? "";
    $filtro_id         = $_GET['filtro_id']         ?? "";
    $filtro_nombre     = $_GET['filtro_nombre']     ?? "";
//    $filtro_cupos      = $_GET['filtro_cupos']      ?? "";
    $filtro_publicidad = $_GET['filtro_publicidad'] ?? "";

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    // WHERE dinámico
    $where = "estado_curso = '1'";
    if ($busqueda != "") {
        $where .= " AND (nombre_curso LIKE '%$busqueda%' OR tipo_curso LIKE '%$busqueda%' OR id_curso LIKE '%$busqueda%')";
    }
    if ($filtro_tipo != "")       $where .= " AND tipo_curso = '$filtro_tipo'";
    if ($filtro_id != "")         $where .= " AND id_curso = '$filtro_id'";
    if ($filtro_nombre != "")     $where .= " AND id_curso = '$filtro_nombre'";
 //   if ($filtro_cupos != "")      $where .= " AND cupos = '$filtro_cupos'";
    
    if ($filtro_publicidad === "1") {
        $where .= " AND id_curso IN (
            SELECT id_curso FROM curso_publicidad WHERE estado_curso_publicidad = 1
        )";
    } elseif ($filtro_publicidad === "0") {
        $where .= " AND id_curso NOT IN (
            SELECT id_curso FROM curso_publicidad WHERE estado_curso_publicidad = 1
        )";
    }

    $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM curso WHERE $where $orden_sql LIMIT $inicio, $registros";

    $conexion = mainModel::conectar();
    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);
    
    
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
  
//                <th>Cupos</th>';
//	    <td>' . ($rows['cupos'] == 1 
//        ? '<span class="badge badge-success">Disponible</span>' 
//        : '<span class="badge badge-danger">No disponible</span>') . '</td>';

    // Construcción de tabla
$cargo = $_SESSION['cargo_spm'] ?? 0;

$tabla = '<div class="table-responsive">
    <table class="table table-dark table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>Id</th>
                <th>Nombre</th>
                <th>Tipo</th>';

if ($cargo != 5) {
    $tabla .= '
                <th>Vigente en periodo actual</th>
                <th>Publicidad</th>';
}

$tabla .= '
                <th>Ver periodos</th>';

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

        foreach ($datos as $rows) {
            $idCurso = $rows['id_curso'];
            $vistaDestino = ($rows['tipo_curso'] === "Intensivo") ? "cv-int-list" : "seccion-list";



            // Verificar si está vigente
            $vigente = $conexion->prepare("
            
            
            
            
SELECT cv.id_curso_vigente
FROM curso_vigente cv
LEFT JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
LEFT JOIN periodo_intensivo pi ON cv.id_curso_vigente = pi.id_curso_vigente_fk
WHERE cv.id_curso_fk = :idCurso
  AND cv.estado_curso_vigente = 1
  AND (
    (cv.id_cohorte_fk IS NOT NULL AND c.estado_cohorte = 'Vigente' AND c.archivar_cohorte = 1)
    OR
    (cv.id_cohorte_fk IS NULL AND pi.estado_periodo = 'Vigente')
  )
LIMIT 1
            
            
            

            ");
            $vigente->bindParam(":idCurso", $idCurso);
            $vigente->execute();
            $esVigente = $vigente->rowCount() > 0 ? "Sí" : "No";

            
            
            /*
                            SELECT cv.id_curso_vigente
                FROM curso_vigente cv
                INNER JOIN cohorte c ON cv.id_cohorte_fk = c.id_cohorte
                WHERE cv.id_curso_fk = :idCurso
                  AND cv.estado_curso_vigente = 1
                  AND c.estado_cohorte = 'Vigente'
                  AND c.archivar_cohorte = 1
                LIMIT 1
            */
            
            
            
            // Verificar publicidad
            $publicidad = $conexion->prepare("
                SELECT id_materia
                FROM curso_publicidad
                WHERE id_curso = :idCurso
                  AND estado_curso_publicidad = 1
                LIMIT 1
            ");
            $publicidad->bindParam(":idCurso", $idCurso);
            $publicidad->execute();
            $tienePublicidad = $publicidad->rowCount() > 0 ? "Sí" : "No";

            // Filtro por publicidad              
		//	if ($filtro_publicidad !== "") {
      //          if (($filtro_publicidad == "1" && $tienePublicidad != "Sí") || ($filtro_publicidad == "0" && $tienePublicidad == "Sí")) {
    //                continue;
  //              }
//            }

$tabla .= '<tr class="text-center">
    <td>' . $rows['id_curso'] . '</td>
    <td>' . $rows['nombre_curso'] . '</td>
    <td>' . $rows['tipo_curso'] . '</td>';


if ($cargo != 5) {
    $tabla .= '
    <td>' . $esVigente . '</td>
    <td>' . ($tienePublicidad == "Sí" 
        ? '<span class="badge badge-info">Sí</span>' 
        : '<span class="badge badge-secondary">No</span>') . '</td>';
}

$tabla .= '
    <td>
        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/' . $vistaDestino . '&filtro_nombre=' . urlencode($rows['id_curso']) . '&origen=curso" class="btn btn-info btn-sm" title="Ver cursos vigentes">
            <i class="fas fa-eye"></i>
        </a>
    </td>';

if ($cargo == 1 || $cargo == 4) {
    $tabla .= '
    <td>
        <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/course-update&course_id_up=' . mainModel::encryption($rows['id_curso']) . '" class="btn btn-success">
            <i class="fas fa-sync-alt"></i>
        </a>

    </td>
    <td>
        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoAjax.php" method="POST" data-form="delete" data-entity="curso" autocomplete="off">
            <input type="hidden" name="curso_id_archive" value="' . mainModel::encryption($rows['id_curso']) . '">
            <button type="submit" class="btn btn-warning">
                <i class="fas fa-archive"></i> 
            </button>
        </form>
    </td>';
}

$tabla .= '</tr>';


          
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="9">';
        $tabla .= ($total >= 1)
        //    ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic acá para recargar el listado</a>'
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic acá para recargar el listado</a>'
            : 'No hay registros en el sistema';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';
    // Paginador con filtros
    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando curso ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'orden' => $orden,
            'filtro_tipo' => $filtro_tipo,
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
    //        'filtro_cupos' => $filtro_cupos,
            'filtro_publicidad' => $filtro_publicidad,
            'busqueda' => $busqueda
        ]);
		$urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
  //      $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}



/*
                <td>
                    <a href="' . SERVERURL . 'index.php?views=course-update&course_id_up=' . mainModel::encryption($rows['id_curso']) . '" class="btn btn-success">
                        <i class="fas fa-sync-alt"></i>
                    </a>
                </td>
                <td>
                    <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoAjax.php" method="POST" data-form="delete" data-entity="curso" autocomplete="off">
                        <input type="hidden" name="curso_id_archive" value="' . mainModel::encryption($rows['id_curso']) . '">
                        <button type="submit" class="btn btn-warning">
                            <i class="fas fa-archive"></i> 
                        </button>
                    </form>
                </td>

  </tr>';
*/








public function paginador_curso_archivado_controlador($pagina, $registros, $vista, $busqueda) {
    $pagina    = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $vista     = mainModel::limpiar_cadena($vista); // solo el nombre de la vista, ej: "course-list-archived"
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
    
    $orden = $_GET['orden'] ?? "";
    $orden_sql = match($orden) {
        "tipo"  => "ORDER BY tipo_curso ASC",
        "fecha" => "ORDER BY id_curso DESC",
        default => "ORDER BY nombre_curso ASC"
    };

    $filtro_tipo   = $_GET['filtro_tipo']   ?? "";
    $filtro_id     = $_GET['filtro_id']     ?? "";
    $filtro_nombre = $_GET['filtro_nombre'] ?? "";
 //   $filtro_cupos  = $_GET['filtro_cupos']  ?? "";

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    $conexion = mainModel::conectar();

    // WHERE dinámico
    $where = "estado_curso = 0";
    if ($busqueda != "") {
        $where .= " AND (nombre_curso LIKE '%$busqueda%' OR tipo_curso LIKE '%$busqueda%' OR id_curso LIKE '%$busqueda%')";
    }
    if ($filtro_tipo != "")   $where .= " AND tipo_curso = '$filtro_tipo'";
    if ($filtro_id != "")     $where .= " AND id_curso = '$filtro_id'";
    if ($filtro_nombre != "") $where .= " AND id_curso = '$filtro_nombre'";
  //  if ($filtro_cupos != "")  $where .= " AND cupos = '$filtro_cupos'";

    $consulta = "SELECT SQL_CALC_FOUND_ROWS * FROM curso WHERE $where $orden_sql LIMIT $inicio, $registros";

    $datos    = $conexion->query($consulta)->fetchAll();
    $total    = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    					//                     <th>En Vigencia</th>
    //                    <td>' . $conteoVigencias . '</td>

    //<!--a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-archived&filtro_nombre=' . urlencode($rows['id_curso']) . '&origen=curso"-->

    
    
    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>Id</th>
                    <th>Nombre</th>
                    <th>Tipo</th>
                    <th>Secciones</th>
                    <th>Evaluaciones</th>
                    <th>Calificaciones</th>
                    <th>Ver periodos</th>
                    <th>Restaurar</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $rows) {
            $idCurso = $rows['id_curso'];

            $cursosVigentes = $conexion->query("SELECT id_curso_vigente FROM curso_vigente WHERE id_curso_fk = '$idCurso'")->fetchAll();
            $conteoVigencias = count($cursosVigentes);

            $conteoSecciones = 0;
            $conteoEvaluaciones = 0;
            $conteoCalificaciones = 0;

            foreach ($cursosVigentes as $cv) {
                $idCV = $cv['id_curso_vigente'];
                $secciones = $conexion->query("SELECT id_seccion FROM seccion WHERE id_curso_vigente_fk = '$idCV'")->fetchAll();
                $conteoSecciones += count($secciones);

                foreach ($secciones as $sec) {
                    $idSec = $sec['id_seccion'];
                    $evaluaciones = $conexion->query("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = '$idSec'")->fetchAll();
                    $conteoEvaluaciones += count($evaluaciones);

                    foreach ($evaluaciones as $eval) {
                        $idEval = $eval['id_evaluacion'];
                        $conteoCalificaciones += (int)$conexion->query("SELECT COUNT(*) FROM puntaje_estudiante WHERE id_evaluacion = '$idEval'")->fetchColumn();
                    }
                }
            }

            $vistaDestino = ($rows['tipo_curso'] === "Intensivo") ? "cv-int-list-archived" : "seccion-archived";

     /*       <!--                    <td>' . ($rows['cupos'] == 1 ? '<span class="badge badge-success">Disponible</span>' : '<span class="badge badge-danger">No disponible</span>') . '</td>
-->
                   <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/seccion-archived&filtro_nombre=' . urlencode($rows['id_curso']) . '&origen=curso" class="btn btn-info btn-sm" title="Ver cursos archivados">

*/
            $tabla .= '
                <tr class="text-center">
                    <td>' . $rows['id_curso'] . '</td>
                    <td>' . $rows['nombre_curso'] . '</td>
                    <td>' . $rows['tipo_curso'] . '</td>
                    <td>' . $conteoSecciones . '</td>
                    <td>' . $conteoEvaluaciones . '</td>
                    <td>' . $conteoCalificaciones . '</td>
                    <td>
                    
                    
                    <a href="' . SERVERURL . 'index.php?views=' . urlencode($carpeta) . '/' . $vistaDestino . '&filtro_nombre=' . urlencode($rows['id_curso']) . '&origen=curso" class="btn btn-info btn-sm" title="Ver cursos archivados">
           			 <i class="fas fa-eye"></i>
        			</a>

                    
                    </td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoAjax.php" method="POST" data-form="restore" data-entity="curso" autocomplete="off">
                            <input type="hidden" name="curso_id_restore" value="' . mainModel::encryption($rows['id_curso']) . '">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="9">';
        $tabla .= ($total >= 1)
            ? '<a href="' . SERVERURL . 'index.php?views=' . $vistaCompleta . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
           // ? '<a href="' . SERVERURL . 'index.php?views=' . $vista . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay cursos archivados en el sistema';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando curso ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';

        $queryParams = http_build_query([
            'orden' => $orden,
            'filtro_tipo' => $filtro_tipo,
            'filtro_id' => $filtro_id,
            'filtro_nombre' => $filtro_nombre,
    //       'filtro_cupos' => $filtro_cupos,
            'busqueda' => $busqueda
        ]);

          $urlCompleta = SERVERURL . "index.php?views=" . $vistaCompleta . "&" . $queryParams . "&page=";
      //  $urlCompleta = SERVERURL . "index.php?views=" . $vista . "&" . $queryParams . "&page=";
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $urlCompleta, 7);
    }

    return $tabla;
}





public function archivar_curso_controlador_2025(){

    $id = mainModel::decryption($_POST['curso_id_archive']);
    $id = mainModel::limpiar_cadena($id);

    // Verificar existencia del curso
    $check_curso = mainModel::ejecutar_consulta_simple("SELECT id_curso FROM curso WHERE id_curso='$id'");
    if($check_curso->rowCount() <= 0){
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Ocurrió un error inesperado",
            "Texto" => "El curso que intenta archivar no existe en el sistema",
            "Tipo" => "error"
        ]);
        exit();
    }

    // Verificar privilegios
    session_start(['name'=>'SPM']);
    if($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Ocurrió un error inesperado",
            "Texto" => "No tienes los permisos necesarios para realizar esta operación",
            "Tipo" => "error"
        ]);
        exit();
    }

    $conexion = mainModel::conectar();

    try {
        $conexion->beginTransaction();

        // Archivar curso
        $conexion->exec("UPDATE curso SET estado_curso = 0 WHERE id_curso = '$id'");

        // Archivar curso_publicidad
        $conexion->exec("UPDATE curso_publicidad SET estado_curso_publicidad = 0 WHERE id_curso = '$id'");

        // Obtener cursos vigentes
        $cursos_vigentes = $conexion->query("SELECT id_curso_vigente FROM curso_vigente WHERE id_curso_fk = '$id'")->fetchAll();



        foreach($cursos_vigentes as $cv){
            $idCV = $cv['id_curso_vigente'];
            $conexion->exec("UPDATE curso_vigente SET estado_curso_vigente = 0 WHERE id_curso_vigente = '$idCV'");

            // Obtener secciones
            $secciones = $conexion->query("SELECT id_seccion FROM seccion WHERE id_curso_vigente_fk = '$idCV'")->fetchAll();

            foreach($secciones as $sec){
                $idSec = $sec['id_seccion'];
                $conexion->exec("UPDATE seccion SET archivar_seccion = 0 WHERE id_seccion = '$idSec'");

                // Obtener evaluaciones
                $evaluaciones = $conexion->query("SELECT id_evaluacion FROM evaluacion WHERE id_seccion = '$idSec'")->fetchAll();

                foreach($evaluaciones as $eval){
                    $idEval = $eval['id_evaluacion'];
                    $conexion->exec("UPDATE evaluacion SET archivar_evaluacion = 0 WHERE id_evaluacion = '$idEval'");
                    $conexion->exec("UPDATE puntaje_estudiante SET archivar_puntaje = 0 WHERE id_evaluacion = '$idEval'");
                }

                // Actualizar observaciones en inscripciones
                $conexion->exec("
                    UPDATE inscripcion 
                    SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | curso archivado o inactivo') 
                    WHERE id_seccion_fk = '$idSec'
                ");
            }
        }

        $conexion->commit();

        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Curso archivado",
            "Texto" => "El curso y sus registros relacionados han sido archivados exitosamente",
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













    public function archivar_curso_controlador() {
        // ... (Desencriptación y validación de existencia igual que antes) ...

        $conexion = mainModel::conectar();

        // Definimos el mapa de relaciones
        $mapa_archivado = [
            'columna_estado' => 'estado_curso',
            'hijos' => [
                [
                    'tabla' => 'curso_vigente',
                    'columna_id' => 'id_curso_vigente',
                    'columna_fk' => 'id_curso_fk',
                    'columna_estado' => 'estado_curso_vigente',
                    'hijos' => [
                        [
                            'tabla' => 'seccion',
                            'columna_id' => 'id_seccion',
                            'columna_fk' => 'id_curso_vigente_fk',
                            'columna_estado' => 'archivar_seccion',
                            'extra_sql' => "UPDATE inscripcion SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | curso archivado') WHERE id_seccion_fk = :id",
                            'hijos' => [
                                [
                                    'tabla' => 'evaluacion',
                                    'columna_id' => 'id_evaluacion',
                                    'columna_fk' => 'id_seccion',
                                    'columna_estado' => 'archivar_evaluacion',
                                    'hijos' => [
                                        [
                                            'tabla' => 'puntaje_estudiante',
                                            'columna_id' => 'id_puntaje',
                                            'columna_fk' => 'id_evaluacion',
                                            'columna_estado' => 'archivar_puntaje'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ]
        ];

        try {
            $conexion->beginTransaction();
            self::archivar_registros_cascada($conexion, 'curso', 'id_curso', $id, $mapa_archivado);

            // Caso especial: curso_publicidad no tiene hijos pero depende de curso
            $conexion->exec("UPDATE curso_publicidad SET estado_curso_publicidad = 0 WHERE id_curso = '$id'");

            $conexion->commit();
            //return $this->respuesta_json("Éxito", "Todo se archivó correctamente", "success");
            echo json_encode([
                "Alerta" => "recargar",
                "Titulo" => "Curso archivado",
                "Texto" => "El curso y sus registros relacionados han sido archivados exitosamente",
                "Tipo" => "success"
            ]);
        } catch (Exception $e) {
            $conexion->rollBack();
            //return $this->respuesta_json("Error", $e->getMessage(), "error");
            echo json_encode([
            	"Alerta" => "simple",
            	"Titulo" => "Error al archivar",
            	"Texto" => "No se pudo completar el proceso: " . $e->getMessage(),
            	"Tipo" => "error"
        	]);
        }
    }



    /*--------- Controlador archivar curso ---------
    public function archivar_curso_controlador(){

        /* recibiendo id del curso*
        $id=mainModel::decryption($_POST['curso_id_archive']);
        $id=mainModel::limpiar_cadena($id);

        /* comprobando el curso en BD *
        $check_curso=mainModel::ejecutar_consulta_simple("SELECT id_curso FROM curso WHERE id_curso='$id'");
        if($check_curso->rowCount()<=0){
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"El curso que intenta archivar no existe en el sistema",
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* comprobando privilegios *
        session_start(['name'=>'SPM']);
        if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4){
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No tienes los permisos necesarios para realizar esta operacion",
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* Archivando curso (cambiando estado a 0) *
        $archivar_curso=mainModel::ejecutar_consulta_simple("UPDATE curso SET estado_curso='0' WHERE id_curso='$id'");

        if($archivar_curso->rowCount()==1){
            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"Curso archivado",
                "Texto"=>"El curso ha sido archivado exitosamente",
                "Tipo"=>"success"
            ];
        }else{
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No hemos podido archivar el curso, por favor intente nuevamente",
                "Tipo"=>"error"
            ];
        }

        echo json_encode($alerta);
    } /* Fin controlador */










    /*--------- Controlador restaurar curso ---------*/
public function restaurar_curso_controlador() {
    $id = mainModel::decryption($_POST['curso_id_restore']);
    $id = mainModel::limpiar_cadena($id);

    $check_curso = mainModel::ejecutar_consulta_simple("SELECT id_curso FROM curso WHERE id_curso='$id'");
    if ($check_curso->rowCount() <= 0) {
        echo json_encode([
            "Alerta" => "simple",
            "Titulo" => "Error",
            "Texto" => "El curso no existe",
            "Tipo" => "error"
        ]);
        exit();
    }

    $pdo = mainModel::conectar();
    try {
        $pdo->beginTransaction();

        $countsRestored = ['curso'=>0,'curso_vigente'=>0,'secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $countsOmitted = ['curso_vigente'=>0,'secciones'=>0,'evaluaciones'=>0,'puntajes'=>0];
        $omittedExamples = [];

        $pushExample = function($tipo,$id) use (&$omittedExamples) {
            if (!isset($omittedExamples[$tipo])) $omittedExamples[$tipo] = [];
            if (count($omittedExamples[$tipo]) < 10) $omittedExamples[$tipo][] = $id;
        };

        // Restaurar curso y publicidad
        $pdo->exec("UPDATE curso SET estado_curso = 1 WHERE id_curso = '$id'");
        $pdo->exec("UPDATE curso_publicidad SET estado_curso_publicidad = 1 WHERE id_curso = '$id'");
        $countsRestored['curso'] = 1;

        // Obtener cursos vigentes
        $stmt_cv = $pdo->prepare("SELECT id_curso_vigente, id_cohorte_fk FROM curso_vigente WHERE id_curso_fk = :id");
        $stmt_cv->execute([':id' => $id]);
        $cursos_vigentes = $stmt_cv->fetchAll();

        $ps_up_cv = $pdo->prepare("UPDATE curso_vigente SET estado_curso_vigente = 1 WHERE id_curso_vigente = :idCV");
        $ps_get_secciones = $pdo->prepare("SELECT id_seccion, ci_profesor FROM seccion WHERE id_curso_vigente_fk = :idCV");
        $ps_check_prof = $pdo->prepare("SELECT estado_profesor FROM profesor WHERE id_profesor = :ciProf");
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

        $ps_up_insc = $pdo->prepare("
            UPDATE inscripcion
            SET observaciones = CONCAT(IFNULL(observaciones, ''), ' | curso reactivado')
            WHERE id_seccion_fk = :idSec AND IFNULL(observaciones, '') NOT LIKE '%curso reactivado%'
        ");

        foreach ($cursos_vigentes as $cv) {
            $idCV = (int)$cv['id_curso_vigente'];
            $ps_up_cv->execute([':idCV' => $idCV]);
            $countsRestored['curso_vigente'] += 1;
			$cohorteFk = $cv['id_cohorte_fk'];

            if ($cohorteFk !== null && $cohorteFk !== 0) {
                $stmtCoh = $pdo->prepare("SELECT archivar_cohorte FROM cohorte WHERE id_cohorte = :id");
                $stmtCoh->execute([':id' => $cohorteFk]);
                $cohRow = $stmtCoh->fetch(PDO::FETCH_ASSOC);
                $cohorteActivo = $cohRow && (int)$cohRow['archivar_cohorte'] === 1;

                if (! $cohorteActivo) {
                    $countsOmitted['curso_vigente'] += 1;
                    $pushExample('curso_vigente', $idCV);
                    continue; // omitir este curso vigente
                }
            }

            
            
            
            $ps_get_secciones->execute([':idCV' => $idCV]);
            while ($sec = $ps_get_secciones->fetch(PDO::FETCH_ASSOC)) {
                $idSec = (int)$sec['id_seccion'];
                $ciProf = $sec['ci_profesor'];

                $ps_check_prof->execute([':ciProf' => $ciProf]);
                $profRow = $ps_check_prof->fetch(PDO::FETCH_ASSOC);
                $profActivo = $profRow && (int)$profRow['estado_profesor'] === 1;

                if (! $profActivo) {
                    $countsOmitted['secciones'] += 1;
                    $pushExample('secciones', $idSec);
                    continue;
                }

                $ps_up_seccion->execute([':idSec' => $idSec]);
                $countsRestored['secciones'] += 1;

                $ps_get_evals->execute([':idSec' => $idSec]);
                while ($eval = $ps_get_evals->fetch(PDO::FETCH_ASSOC)) {
                    $idEval = (int)$eval['id_evaluacion'];

                    $ps_up_eval->execute([':idEval' => $idEval]);
                    $countsRestored['evaluaciones'] += 1;

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

                $ps_up_insc->execute([':idSec' => $idSec]);
            }
        }

        $pdo->commit();
        $totalOmitidos = array_sum($countsOmitted);
        if ($totalOmitidos > 0) {
            $texto = "Curso restaurado, pero se omitieron: " .
                $countsOmitted['curso_vigente'] . " curso(s) vigente(s), " .
                $countsOmitted['secciones'] . " sección(es), " .
                $countsOmitted['evaluaciones'] . " evaluación(es), " .
                $countsOmitted['puntajes'] . " puntaje(s).";
            $tipo = "warning";
        } else {
            $texto = "Curso restaurado exitosamente. Todos los registros relacionados fueron activados.";
            $tipo = "success";
        }

        echo json_encode([
            "Alerta" => "recargar",
            "Titulo" => "Curso restaurado",
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









    public function eliminar_curso_controlador(){

        /* recibiendo id del curso*/
        $id=mainModel::decryption($_POST['curso_id_del']);
        $id=mainModel::limpiar_cadena($id);

        /* comprobando el curso en BD */
        $check_curso=mainModel::ejecutar_consulta_simple("SELECT id_curso FROM curso WHERE id_curso='$id'");
        if($check_curso->rowCount()<=0){
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"El curso que intenta eliminar no existe en el sistema",
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /* comprobando privilegios */
        session_start(['name'=>'SPM']);
        if($_SESSION['cargo_spm']!=1 && $_SESSION['cargo_spm']!=4){
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No tienes los permisos necesarios para realizar esta operacion",
                "Tipo"=>"error"
            ];
            echo json_encode($alerta);
            exit();
        }

        $eliminar_curso=cursoModelo::eliminar_curso_modelo($id);

        if($eliminar_curso->rowCount()==1){
            $alerta=[
                "Alerta"=>"recargar",
                "Titulo"=>"Curso eliminado",
                "Texto"=>"El curso ha sido eliminado del sistema exitosamente",
                "Tipo"=>"success"
            ];
        }else{
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No hemos podido eliminar el curso, por favor intente nuevamente",
                "Tipo"=>"error"
            ];
        }

        echo json_encode($alerta);
    } /* Fin controlador */



































    public function cursos_por_tipo_controlador($tipo){
        $tipo = mainModel::limpiar_cadena($tipo);

        $consulta = cursoModelo::obtener_cursos_por_tipo($tipo);
        $datos = $consulta->fetchAll(PDO::FETCH_ASSOC);

        // Mapear datos relevantes
        $respuesta = [];
        foreach($datos as $curso){
            $respuesta[] = [
                "id" => $curso['id_curso'],
                "nombre" => $curso['nombre_curso']
            ];
        }

        return json_encode($respuesta);
    }













    //lista de cursos pa anuncio_publicidad
    public function obtener_lista_cursos_tipo(){
        $conexion = mainModel::conectar();
        $sql = $conexion->prepare("
            SELECT id_curso, nombre_curso, tipo_curso 
            FROM curso 
            WHERE estado_curso = 1
            ORDER BY nombre_curso ASC
        ");
        $sql->execute();
        return $sql->fetchAll(PDO::FETCH_ASSOC);
    }



/*
-
    public function obtener_cursos_disponibles_por_cohorte_controlador_formdos($id_cohorte){
        $conexion = mainModel::conectar();

        // Verificar tipo de cohorte
        $tipo_stmt = $conexion->prepare("
            SELECT tipo_cohorte FROM cohorte WHERE id_cohorte = :ID LIMIT 1
        ");
        $tipo_stmt->bindParam(":ID", $id_cohorte, PDO::PARAM_INT);
        $tipo_stmt->execute();

        if($tipo_stmt->rowCount() != 1){
            return []; // Cohorte no encontrada
        }

        $tipo = $tipo_stmt->fetch()['tipo_cohorte'];

        // Obtener cursos que coincidan con el tipo y estén activos
        $cursos_stmt = $conexion->prepare("
            SELECT id_curso, nombre_curso, tipo_curso 
            FROM curso 
            WHERE tipo_curso = :TIPO AND estado_curso = 1
            ORDER BY nombre_curso ASC
        ");
        $cursos_stmt->bindParam(":TIPO", $tipo);
        $cursos_stmt->execute();

        return $cursos_stmt->fetchAll(PDO::FETCH_ASSOC);
    }
*/

}
?>
