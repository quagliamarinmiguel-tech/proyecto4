<?php

if ($peticionAjax) {
    require_once "../modelos/cursoVigenteModelo.php";
} else {
    require_once "./modelos/cursoVigenteModelo.php";
}

class cursoVigenteControlador extends cursoVigenteModelo {

    /*--------- Controlador agregar curso vigente ---------*/
    public function agregar_curso_vigente_controlador() {

        $curso_fk = mainModel::limpiar_cadena($_POST['id_curso_fk'] ?? '');
        $cohorte_fk = mainModel::limpiar_cadena($_POST['id_cohorte_fk'] ?? '');
        $pago_inscripcion = mainModel::limpiar_cadena($_POST['pago_inscripcion'] ?? '');
        $pago_modulo = mainModel::limpiar_cadena($_POST['pago_modulo'] ?? '');
        $oferta = mainModel::limpiar_cadena($_POST['oferta'] ?? '');

        /*== Comprobando campos requeridos ==*/
        if ($curso_fk == "" || $cohorte_fk == "" || $pago_inscripcion == "" || $pago_modulo == "") {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Campos obligatorios faltantes",
                "Texto" => "Debes completar todos los datos necesarios para registrar la relación.",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }

        /*== Validar duplicado ==*/
        $check_existente = mainModel::ejecutar_consulta_simple("
            SELECT id_curso_vigente FROM curso_vigente 
            WHERE id_curso_fk = '$curso_fk' AND id_cohorte_fk = '$cohorte_fk'
        ");

        if ($check_existente->rowCount() > 0) {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Curso ya asociado",
                "Texto" => "Este curso ya está registrado como vigente para esa cohorte.",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }


        /*Validar que tipo del curso coincida con tipo de cohorte */

        $tipo_curso = mainModel::ejecutar_consulta_simple("
            SELECT tipo_curso FROM curso WHERE id_curso = '$curso_fk'
        ")->fetch()['tipo_curso'] ?? '';

        $tipo_cohorte = mainModel::ejecutar_consulta_simple("
            SELECT tipo_cohorte FROM cohorte WHERE id_cohorte = '$cohorte_fk'
        ")->fetch()['tipo_cohorte'] ?? '';

        if($tipo_curso !== $tipo_cohorte){
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Tipos incompatibles",
                "Texto" => "El tipo del curso ($tipo_curso) no coincide con el tipo de cohorte ($tipo_cohorte). Ambos deben ser del mismo tipo.",
                "Tipo" => "error"
            ];
            echo json_encode($alerta);
            exit();
        }






        /*== Insertar en la base de datos ==*/
        $datos = [
            "Curso" => $curso_fk,
            "Cohorte" => $cohorte_fk,
            "Inscripcion" => $pago_inscripcion,
            "Modulo" => $pago_modulo,
            "Oferta" => $oferta
        ];

        $registro = cursoVigenteModelo::agregar_curso_vigente_modelo($datos);

        if ($registro->rowCount() == 1) {
            $alerta = [
                "Alerta" => "limpiar",
                "Titulo" => "Curso vigente registrado",
                "Texto" => "La relación fue guardada exitosamente.",
                "Tipo" => "success"
            ];
        } else {
            $alerta = [
                "Alerta" => "simple",
                "Titulo" => "Ocurrió un error",
                "Texto" => "No se pudo registrar la información. Intenta nuevamente.",
                "Tipo" => "error"
            ];
        }

        echo json_encode($alerta);
    } // Fin agregar controlador



/*
            $consulta = "
                SELECT SQL_CALC_FOUND_ROWS cv.id_curso_vigente, c.nombre_curso, co.numero_cohorte, co.anio_cohorte, co.tipo_cohorte, 
                    cv.pago_inscripcion, cv.pago_modulo, cv.oferta
                FROM curso_vigente cv
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte WHERE estado_curso_vigente=1
                ORDER BY co.anio_cohorte DESC, co.numero_cohorte DESC
                LIMIT $inicio, $registros
            ";*/


    public function paginador_curso_vigente_controlador($pagina, $registros, $url, $busqueda) {
        $pagina = mainModel::limpiar_cadena($pagina);
        $registros = mainModel::limpiar_cadena($registros);
        $url = SERVERURL . "index.php?views=" . mainModel::limpiar_cadena($url) . "&page=";
        $busqueda = mainModel::limpiar_cadena($busqueda);

        $tabla = "";

        $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
/*                WHERE c.nombre_curso LIKE '%$busqueda%' OR co.numero_cohorte LIKE '%$busqueda%'
                AND cv.estado_curso_vigente=1*/
        if (!empty($busqueda)) {
            $consulta = "
                SELECT SQL_CALC_FOUND_ROWS cv.id_curso_vigente, c.nombre_curso, co.numero_cohorte, co.anio_cohorte, co.tipo_cohorte, 
                    cv.pago_inscripcion, cv.pago_modulo, cv.oferta
                FROM curso_vigente cv
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte


                WHERE (c.nombre_curso LIKE '%$busqueda%' OR co.numero_cohorte LIKE '%$busqueda%') 
                AND cv.estado_curso_vigente = 1

                ORDER BY co.anio_cohorte DESC, co.numero_cohorte DESC
                LIMIT $inicio, $registros
            ";
        } else {


            $consulta = "
                SELECT SQL_CALC_FOUND_ROWS cv.id_curso_vigente, c.nombre_curso, co.numero_cohorte, co.anio_cohorte, co.tipo_cohorte, 
                    cv.pago_inscripcion, cv.pago_modulo, cv.oferta
                FROM curso_vigente cv
                INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
                INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
                WHERE cv.estado_curso_vigente = 1
                AND co.estado_cohorte = 'Vigente'
                ORDER BY co.anio_cohorte DESC, co.numero_cohorte DESC
                LIMIT $inicio, $registros
            ";

        }

        $conexion = mainModel::conectar();
        $datos = $conexion->query($consulta)->fetchAll();
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        $Npaginas = ceil($total / $registros);

        $tabla .= '<div class="table-responsive">
            <table class="table table-dark table-sm">
                <thead>
                    <tr class="text-center roboto-medium">
                        <th>#</th>
                        <th>CURSO</th>
                        <th>COHORTE</th>
                        <th>INSCRIPCIÓN</th>
                        <th>MÓDULO</th>
                        <th>OFERTA</th>
                        <th>ACTUALIZAR</th>
                        <th>ARCHIVAR</th>
                    </tr>
                </thead>
                <tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $reg_inicio = $inicio + 1;

            foreach ($datos as $row) {
                $cohorte_str = $row['numero_cohorte'] . '.' . $row['anio_cohorte'] . ' ' . ucfirst($row['tipo_cohorte']);

                $tabla .= '
                    <tr class="text-center">
                        <td>' . $row['id_curso_vigente'] . '</td>
                        <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                        <td>' . htmlspecialchars($cohorte_str) . '</td>
                        <td>' . number_format($row['pago_inscripcion'], 2, ',', '.') . ' Bs</td>
                        <td>' . number_format($row['pago_modulo'], 2, ',', '.') . ' Bs</td>
                        <td>' . $row['oferta'] . '%</td>
                        <td>
                            <a href="' . SERVERURL . 'index.php?views=curso-vigente-update&curso_vigente_id_up=' . mainModel::encryption($row['id_curso_vigente']) . '" class="btn btn-success">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        </td>
                        <td>
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoVigenteAjax.php" method="POST" data-form="delete" autocomplete="off">
                                <input type="hidden" name="curso_vigente_id_archive" value="' . mainModel::encryption($row['id_curso_vigente']) . '">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                        </td>
                    </tr>';
            }

            $reg_final = $reg_inicio + count($datos) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="8">';
            $tabla .= ($total >= 1)
                ? '<a href="' . $url . '" class="btn btn-raised btn-primary btn-sm">Haga clic acá para recargar el listado</a>'
                : 'No hay registros en el sistema';
            $tabla .= '</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando relaciones ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
        }

        return $tabla;
    }




    public function paginador_curso_vigente_search_controlador($pagina, $registros, $url, $nombre = "", $tipo = "") {
        $pagina   = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $nombre   = mainModel::limpiar_cadena($nombre);
        $tipo     = mainModel::limpiar_cadena($tipo);
        $url      = SERVERURL . "index.php?views=" . mainModel::limpiar_cadena($url) . "&page=";
        $inicio   = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $conexion = mainModel::conectar();

        $filtros = [
            "cv.estado_curso_vigente = 1",
            "co.estado_cohorte = 'Vigente'"
        ];
        if ($nombre != "") $filtros[] = "c.nombre_curso LIKE '%$nombre%'";
        if ($tipo != "")   $filtros[] = "c.tipo_curso = '$tipo'";

        $where = implode(" AND ", $filtros);

        $consulta = "
            SELECT SQL_CALC_FOUND_ROWS 
                cv.id_curso_vigente, c.nombre_curso, co.numero_cohorte, co.anio_cohorte, co.tipo_cohorte,
                cv.pago_inscripcion, cv.pago_modulo, cv.oferta
            FROM curso_vigente cv
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            WHERE $where
            ORDER BY co.anio_cohorte DESC, co.numero_cohorte DESC
            LIMIT $inicio, $registros
        ";

        $datos = $conexion->query($consulta)->fetchAll();
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
        $Npaginas = ceil($total / $registros);

        $tabla = '<div class="table-responsive">
            <table class="table table-dark table-sm">
                <thead>
                    <tr class="text-center roboto-medium">
                        <th>#</th>
                        <th>CURSO</th>
                        <th>COHORTE</th>
                        <th>INSCRIPCIÓN</th>
                        <th>MÓDULO</th>
                        <th>OFERTA</th>
                        <th>ACTUALIZAR</th>
                        <th>ARCHIVAR</th>
                    </tr>
                </thead>
                <tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $reg_inicio = $inicio + 1;
            foreach ($datos as $row) {
                $cohorte_str = $row['numero_cohorte'] . '.' . $row['anio_cohorte'] . ' ' . ucfirst($row['tipo_cohorte']);
                $tabla .= '
                    <tr class="text-center">
                        <td>' . $row['id_curso_vigente'] . '</td>
                        <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                        <td>' . htmlspecialchars($cohorte_str) . '</td>
                        <td>' . number_format($row['pago_inscripcion'], 2, ',', '.') . ' Bs</td>
                        <td>' . number_format($row['pago_modulo'], 2, ',', '.') . ' Bs</td>
                        <td>' . $row['oferta'] . '%</td>
                        <td>
                            <a href="' . SERVERURL . 'index.php?views=curso-vigente-update&curso_vigente_id_up=' . mainModel::encryption($row['id_curso_vigente']) . '" class="btn btn-success">
                                <i class="fas fa-sync-alt"></i>
                            </a>
                        </td>
                        <td>
                            <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoVigenteAjax.php" method="POST" data-form="delete" autocomplete="off">
                                <input type="hidden" name="curso_vigente_id_archive" value="' . mainModel::encryption($row['id_curso_vigente']) . '">
                                <button type="submit" class="btn btn-warning">
                                    <i class="fas fa-archive"></i>
                                </button>
                            </form>
                        </td>
                    </tr>';
            }
            $reg_final = $reg_inicio + count($datos) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="8">';
            $tabla .= ($total >= 1)
                ? '<a href="' . $url . '" class="btn btn-raised btn-primary btn-sm">Haga clic acá para recargar el listado</a>'
                : 'No se encontraron cursos vigentes con esos criterios.';
            $tabla .= '</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando cursos ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
        }

        return $tabla;
    }








public function paginador_curso_vigente_archivado_controlador($pagina, $registros, $url, $busqueda){
    $pagina = mainModel::limpiar_cadena($pagina);
    $registros = mainModel::limpiar_cadena($registros);
    $url = SERVERURL . "index.php?views=" . mainModel::limpiar_cadena($url) . "&page=";
    $busqueda = mainModel::limpiar_cadena($busqueda);

    $tabla = "";
    $pagina = (isset($pagina) && $pagina > 0) ? (int) $pagina : 1;
    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

    if (!empty($busqueda)) {
        $consulta = "
            SELECT SQL_CALC_FOUND_ROWS 
                cv.id_curso_vigente, 
                c.nombre_curso, 
                co.numero_cohorte, 
                co.anio_cohorte, 
                co.tipo_cohorte 
            FROM curso_vigente cv
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            WHERE (c.nombre_curso LIKE '%$busqueda%' OR co.numero_cohorte LIKE '%$busqueda%')
            AND cv.estado_curso_vigente = 0
            ORDER BY co.anio_cohorte DESC, co.numero_cohorte DESC
            LIMIT $inicio, $registros
        ";
    } else {
        $consulta = "
            SELECT SQL_CALC_FOUND_ROWS 
                cv.id_curso_vigente, 
                c.nombre_curso, 
                co.numero_cohorte, 
                co.anio_cohorte, 
                co.tipo_cohorte 
            FROM curso_vigente cv
            INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
            INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
            WHERE cv.estado_curso_vigente = 0
            ORDER BY co.anio_cohorte DESC, co.numero_cohorte DESC
            LIMIT $inicio, $registros
        ";
    }

    $conexion = mainModel::conectar();
    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    $tabla .= '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>#</th>
                    <th>CURSO</th>
                    <th>COHORTE</th>
                    <th>RESTAURAR</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;

        foreach ($datos as $row) {
            $cohorte_str = $row['numero_cohorte'] . '.' . $row['anio_cohorte'] . ' ' . ucfirst($row['tipo_cohorte']);
            $tabla .= '
                <tr class="text-center">
                    <td>' . $row['id_curso_vigente'] . '</td>
                    <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                    <td>' . htmlspecialchars($cohorte_str) . '</td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoVigenteAjax.php" method="POST" data-form="restore" autocomplete="off">
                            <input type="hidden" name="curso_vigente_id_restore" value="' . mainModel::encryption($row['id_curso_vigente']) . '">
                            <button type="submit" class="btn btn-info">
                                <i class="fas fa-undo"></i> Restaurar
                            </button>
                        </form>
                    </td>
                </tr>';
        }

        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="4">';
        $tabla .= ($total >= 1)
            ? '<a href="' . $url . '" class="btn btn-raised btn-primary btn-sm">Haga clic aquí para recargar el listado</a>'
            : 'No hay relaciones archivadas en el sistema';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando relaciones ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
    }

    return $tabla;
}





public function paginador_curso_vigente_por_cohorte_controlador($pagina, $registros, $url, $id_cohorte) {
    $pagina = (int) mainModel::limpiar_cadena($pagina);
    $registros = (int) mainModel::limpiar_cadena($registros);
    $url = SERVERURL . "index.php?views=" . mainModel::limpiar_cadena($url) . "&cohorte_id=" . mainModel::limpiar_cadena($id_cohorte) . "&page=";

    $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;
    $id_cohorte = mainModel::limpiar_cadena($id_cohorte);

    $conexion = mainModel::conectar();

    $consulta = "
        SELECT SQL_CALC_FOUND_ROWS 
            cv.id_curso_vigente, c.nombre_curso, co.numero_cohorte, co.anio_cohorte, co.tipo_cohorte,
            cv.pago_inscripcion, cv.pago_modulo, cv.oferta
        FROM curso_vigente cv
        INNER JOIN curso c ON cv.id_curso_fk = c.id_curso
        INNER JOIN cohorte co ON cv.id_cohorte_fk = co.id_cohorte
        WHERE cv.estado_curso_vigente = 1 AND co.id_cohorte = '$id_cohorte'
        ORDER BY c.nombre_curso ASC
        LIMIT $inicio, $registros
    ";

    $datos = $conexion->query($consulta)->fetchAll();
    $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();
    $Npaginas = ceil($total / $registros);

    $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>#</th>
                    <th>CURSO</th>
                    <th>INSCRIPCIÓN</th>
                    <th>MÓDULO</th>
                    <th>OFERTA</th>
                    <th>EDITAR</th>
                    <th>ARCHIVAR</th>
                </tr>
            </thead>
            <tbody>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $reg_inicio = $inicio + 1;
        foreach ($datos as $row) {
            $tabla .= '
                <tr class="text-center">
                    <td>' . $row['id_curso_vigente'] . '</td>
                    <td>' . htmlspecialchars($row['nombre_curso']) . '</td>
                    <td>' . number_format($row['pago_inscripcion'], 2, ',', '.') . ' Bs</td>
                    <td>' . number_format($row['pago_modulo'], 2, ',', '.') . ' Bs</td>
                    <td>' . $row['oferta'] . '%</td>
                    <td>
                        <a href="' . SERVERURL . 'index.php?views=curso-vigente-update&curso_vigente_id_up=' . mainModel::encryption($row['id_curso_vigente']) . '" class="btn btn-success btn-sm">
                            <i class="fas fa-sync-alt"></i>
                        </a>
                    </td>
                    <td>
                        <form class="FormularioAjax" action="' . SERVERURL . 'ajax/cursoVigenteAjax.php" method="POST" data-form="delete" autocomplete="off">
                            <input type="hidden" name="curso_vigente_id_archive" value="' . mainModel::encryption($row['id_curso_vigente']) . '">
                            <button type="submit" class="btn btn-warning btn-sm">
                                <i class="fas fa-archive"></i>
                            </button>
                        </form>
                    </td>
                </tr>';
        }
        $reg_final = $reg_inicio + count($datos) - 1;
    } else {
        $tabla .= '<tr class="text-center"><td colspan="7">';
        $tabla .= ($total >= 1)
            ? '<a href="' . $url . '" class="btn btn-primary btn-sm">Recargar listado</a>'
            : 'No hay cursos vigentes registrados para esta cohorte.';
        $tabla .= '</td></tr>';
    }

    $tabla .= '</tbody></table></div>';

    if ($total >= 1 && $pagina <= $Npaginas) {
        $tabla .= '<p class="text-right">Mostrando cursos ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
        $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
    }

    return $tabla;
}












/*--------- Controlador archivar curso vigente ---------*/
public function archivar_curso_vigente_controlador(){

    $id = mainModel::decryption($_POST['curso_vigente_id_archive']);
    $id = mainModel::limpiar_cadena($id);

    $check = mainModel::ejecutar_consulta_simple("SELECT id_curso_vigente FROM curso_vigente WHERE id_curso_vigente='$id'");
    if($check->rowCount() <= 0){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Ocurrió un error inesperado",
            "Texto" => "La relación curso-cohorte no existe en el sistema",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    session_start(['name' => 'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Permiso denegado",
            "Texto" => "No tienes privilegios para archivar esta relación",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    $archivar = mainModel::ejecutar_consulta_simple("UPDATE curso_vigente SET estado_curso_vigente='0' WHERE id_curso_vigente='$id'");

    if ($archivar->rowCount() == 1){
        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Relación archivada",
            "Texto" => "La relación curso-cohorte fue archivada exitosamente",
            "Tipo" => "success"
        ];
    } else {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error al archivar",
            "Texto" => "No se pudo archivar, intenta nuevamente",
            "Tipo" => "error"
        ];
    }

    echo json_encode($alerta);
}










/*--------- Controlador restaurar curso vigente ---------*/
public function restaurar_curso_vigente_controlador(){

    $id = mainModel::decryption($_POST['curso_vigente_id_restore']);
    $id = mainModel::limpiar_cadena($id);

    $check = mainModel::ejecutar_consulta_simple("SELECT id_curso_vigente FROM curso_vigente WHERE id_curso_vigente='$id'");
    if($check->rowCount() <= 0){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Ocurrió un error inesperado",
            "Texto" => "La relación que intentás restaurar no fue encontrada",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    session_start(['name' => 'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4){
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Permiso denegado",
            "Texto" => "No tienes los permisos para restaurar esta relación",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    $restaurar = mainModel::ejecutar_consulta_simple("UPDATE curso_vigente SET estado_curso_vigente='1' WHERE id_curso_vigente='$id'");

    if ($restaurar->rowCount() == 1){
        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Relación restaurada",
            "Texto" => "La relación fue reactivada correctamente",
            "Tipo" => "success"
        ];
    } else {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Error inesperado",
            "Texto" => "No se pudo restaurar, intenta nuevamente",
            "Tipo" => "error"
        ];
    }

    echo json_encode($alerta);
}




















/*--------- Controlador datos curso vigente ---------*/
public function datos_curso_vigente_controlador($tipo, $id) {
    $tipo = mainModel::limpiar_cadena($tipo);
    $id = mainModel::decryption($id);
    $id = mainModel::limpiar_cadena($id);

    return cursoVigenteModelo::datos_curso_vigente_modelo($tipo, $id);
} /* Fin controlador */



/*--------- Controlador actualizar curso vigente ---------*/
public function actualizar_curso_vigente_controlador() {
    
    $id = mainModel::limpiar_cadena($_POST['curso_vigente_id_up']);

    // Verificar existencia
    $check = mainModel::ejecutar_consulta_simple("SELECT * FROM curso_vigente WHERE id_curso_vigente = '$id'");
    if ($check->rowCount() <= 0) {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Ocurrió un error inesperado",
            "Texto" => "No hemos encontrado la relación curso-cohorte en el sistema",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    } else {
        $campos = $check->fetch();
    }

    $inscripcion = mainModel::limpiar_cadena($_POST['pago_inscripcion_up']);
    $modulo = mainModel::limpiar_cadena($_POST['pago_modulo_up']);
    $oferta = mainModel::limpiar_cadena($_POST['oferta_up']);

    // Validar campos vacíos
    if ($inscripcion == "" || $modulo == "") {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Faltan datos",
            "Texto" => "Debes completar los campos requeridos",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    // Validar integridad de números
    if (!is_numeric($inscripcion) || !is_numeric($modulo) || (!is_numeric($oferta) && $oferta != "")) {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Valores no válidos",
            "Texto" => "Debes ingresar montos numéricos válidos",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    // Comprobar privilegios
    session_start(['name'=>'SPM']);
    if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != 4) {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Permiso denegado",
            "Texto" => "No tienes permisos para realizar esta operación",
            "Tipo" => "error"
        ];
        echo json_encode($alerta);
        exit();
    }

    $datos = [
        "Inscripcion" => $inscripcion,
        "Modulo" => $modulo,
        "Oferta" => $oferta ?? 0,
        "ID" => $id
    ];

    if (cursoVigenteModelo::actualizar_curso_vigente_modelo($datos)) {
        $alerta = [
            "Alerta" => "recargar",
            "Titulo" => "Datos actualizados",
            "Texto" => "La relación curso-cohorte ha sido actualizada correctamente",
            "Tipo" => "success"
        ];
    } else {
        $alerta = [
            "Alerta" => "simple",
            "Titulo" => "Actualización fallida",
            "Texto" => "No se pudo actualizar, por favor intente nuevamente",
            "Tipo" => "error"
        ];
    }

    echo json_encode($alerta);
} /* Fin controlador */









}