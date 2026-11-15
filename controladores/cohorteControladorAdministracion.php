<?php
require_once __DIR__ . "/../modelos/cohorteModeloAdministracion.php";

class cohorteControladorAdministracion extends cohorteModeloAdministracion {

    /* Obtener los datos de un cohorte específico */
    public function datos_cohorte_controlador($tipo, $id) {
        $tipo = mainModel::limpiar_cadena($tipo);
        $id = mainModel::decryption($id);
        $id = mainModel::limpiar_cadena($id);
        return $this->datos_cohorte_modelo($tipo, $id);
    }

    /* Listado paginado de cohortes para administración */
    public function paginador_cohorte_controlador($pagina, $registros, $url, $busqueda) {
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $url = SERVERURL . "index.php?views=" . mainModel::limpiar_cadena($url) . "&page=";
        $busqueda = mainModel::limpiar_cadena($busqueda);
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $resultado = $this->obtener_cohortes($inicio, $registros, $busqueda);
        $datos = $resultado["datos"];
        $total = $resultado["total"];
        $Npaginas = ceil($total / $registros);

        $tabla = '<div class="table-responsive">
        <table class="table table-dark table-sm">
            <thead>
                <tr class="text-center roboto-medium">
                    <th>ID</th>
                    <th>COHORTE</th>
                    <th>TIPO</th>
                    <th>ESTADO</th>
                    <th>NÚMERO</th>
                    <th>AÑO</th>
                    <th>VER CURSOS</th>
                </tr>
            </thead>
            <tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $reg_inicio = $inicio + 1;
            foreach ($datos as $row) {
                $nombre = $row['numero_cohorte'] . "." . substr($row['anio_cohorte'], -2);
                $idEnc = mainModel::encryption($row['id_cohorte']);
                $tabla .= '
                <tr class="text-center">
                    <td>' . $row['id_cohorte'] . '</td>
                    <td>' . $nombre . '</td>
                    <td>' . $row['tipo_cohorte'] . '</td>
                    <td>' . $row['estado_cohorte'] . '</td>
                    <td>' . $row['numero_cohorte'] . '</td>
                    <td>' . $row['anio_cohorte'] . '</td>
                    <td>
                        <a href="' . SERVERURL . 'index.php?views=administrador de pagos/ListCursosCohorteAdministracion&id=' . $idEnc . '" class="btn btn-info btn-sm">
                            Ver Cursos
                        </a>
                    </td>
                </tr>';
            }
            $reg_final = $reg_inicio + count($datos) - 1;
        } else {
            $tabla .= '<tr class="text-center"><td colspan="7">No hay cohortes registrados.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando cohorte ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
        }

        return $tabla;
    }

    /* Búsqueda específica por número y año */
    public function paginador_cohorte_search_controlador($pagina, $registros, $url, $numero, $anio) {
        $pagina = (int) mainModel::limpiar_cadena($pagina);
        $registros = (int) mainModel::limpiar_cadena($registros);
        $numero = mainModel::limpiar_cadena($numero);
        $anio = mainModel::limpiar_cadena($anio);
        $url = SERVERURL . "index.php?views=" . mainModel::limpiar_cadena($url) . "&page=";
        $inicio = ($pagina > 0) ? (($pagina * $registros) - $registros) : 0;

        $resultado = $this->buscar_cohorte($inicio, $registros, $numero, $anio);
        $datos = $resultado["datos"];
        $total = $resultado["total"];
        $Npaginas = ceil($total / $registros);

        $tabla = '<div class="table-responsive"><table class="table table-dark table-bordered table-sm">
        <thead>
            <tr class="text-center roboto-medium">
                <th>ID</th>
                <th>COHORTE</th>
                <th>TIPO</th>
                <th>ESTADO</th>
                <th>AÑO</th>
                <th>VER CURSOS</th>
            </tr>
        </thead>
        <tbody>';

        if ($total >= 1 && $pagina <= $Npaginas) {
            $reg_inicio = $inicio + 1;
            foreach ($datos as $row) {
                $idEnc = mainModel::encryption($row['id_cohorte']);
                $nombre = $row['numero_cohorte'] . "." . substr($row['anio_cohorte'], -2);
                $tabla .= '
                <tr class="text-center">
                    <td>' . $row['id_cohorte'] . '</td>
                    <td>' . $nombre . '</td>
                    <td>' . $row['tipo_cohorte'] . '</td>
                    <td>' . $row['estado_cohorte'] . '</td>
                    <td>' . $row['anio_cohorte'] . '</td>
                    <td>
                        <a href="' . SERVERURL . 'index.php?views=administrador de pagos/ListCursosCohorteAdministracion&id=' . $idEnc . '" class="btn btn-info btn-sm">
                            Ver Cursos
                        </a>
                    </td>
                </tr>';
            }
            $reg_final = $reg_inicio + count($datos) - 1;
        } else {
            $tabla .= '<tr><td colspan="6" class="text-center">No se encontraron cohortes con esos datos.</td></tr>';
        }

        $tabla .= '</tbody></table></div>';
        if ($total >= 1 && $pagina <= $Npaginas) {
            $tabla .= '<p class="text-right">Mostrando cohorte ' . $reg_inicio . ' al ' . $reg_final . ' de un total de ' . $total . '</p>';
            $tabla .= mainModel::paginador_tablas($pagina, $Npaginas, $url, 7);
        }

        return $tabla;
    }
}
