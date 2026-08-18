<?php

require_once __DIR__ . '/../config/conection.php';

class mainModel
{
    //reiniciar_intentos_ip
    /*--------- Funcion conectar a BD ---------*/
    public static function conectar()
    {
        $conexion = new PDO(SGBD, USER, PASS);
        $conexion->exec("SET CHARACTER SET utf8");
        return $conexion;
    }


    /*--------- Funcion ejecutar consultas simples ---------*/
    protected static function ejecutar_consulta_simple($consulta)
    {
        $sql = self::conectar()->prepare($consulta);
        $sql->execute();
        return $sql;
    }


    /*--------- Encriptar cadenas ---------*/
    public static function encryption($string)
    {
        $output = FALSE;
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        $output = openssl_encrypt($string, METHOD, $key, 0, $iv);
        $output = base64_encode($output);
        return $output;
    }


    /*--------- Desencriptar cadenas ---------*/
    public static function decryption($string)
    {
        $key = hash('sha256', SECRET_KEY);
        $iv = substr(hash('sha256', SECRET_IV), 0, 16);
        $output = openssl_decrypt(base64_decode($string), METHOD, $key, 0, $iv);
        return $output;
    }


    /*--------- Funcion generar codigos aleatorios ---------*/
    protected static function generar_codigo_aleatorio($letra, $longitud, $numero)
    {
        for ($i = 1; $i <= $longitud; $i++) {
            $aleatorio = rand(0, 9);
            $letra .= $aleatorio;
        }
        return $letra . "-" . $numero;
    }




    /*
    public static function generarAlertaControlador($tipoAlerta, $titulo, $texto, $tipo,$manejo_alerta) {

        if(manejo_alerta=="error_simple"){
            $tipo
        }

        $alerta = [
            "Alerta" => $tipoAlerta,
            "Titulo" => $titulo,
            "Texto" => $texto,
            "Tipo" => $tipo
        ];
        echo json_encode($alerta);
        exit();
    }
    */


    public static function generarAlertaSimple($texto, $manejo_alerta, $accion)
    //alerta es o simple o recargar
    {
        if ($manejo_alerta == "error") {
            $titulo = "Ocurrió un error inesperado";
            $tipo = "error";
        } else { // si es success
            $titulo = "Operación exitosa";
            $tipo = "success";
        }

        $alerta = [
            "Alerta" => $accion,
            "Titulo" => $titulo,
            "Texto" => $texto,
            "Tipo" => $tipo
        ];

        return $alerta;
    }

    public static function generarAlertaControlador($texto, $manejo_alerta)
    {

        if ($manejo_alerta == "error_simple") {
            $tipoAlerta = "simple";
            $titulo = "Ocurrió un error inesperado";
            $tipo = "error";
        } elseif ($manejo_alerta == "error_recargar") {
            $tipoAlerta = "recargar";
            $titulo = "Ocurrió un error inesperado";
            $tipo = "error";
        } elseif ($manejo_alerta == "success_simple") {
            $tipoAlerta = "simple";
            $titulo = "Operación exitosa";
            $tipo = "success";
        } else {    //($manejo_alerta=="success_recargar")
            $tipoAlerta = "recargar";
            $titulo = "Operación exitosa";
            $tipo = "success";
        }

        $alerta = [
            "Alerta" => $tipoAlerta,
            "Titulo" => $titulo,
            "Texto" => $texto,
            "Tipo" => $tipo
        ];
        echo json_encode($alerta);
        exit();
    }


    public static function verificar_sin_permiso($nivel)
    {
        session_start(['name' => 'SPM']);
        if ($_SESSION['cargo_spm'] != 1 && $_SESSION['cargo_spm'] != $nivel) {
            return true;
        }
        return false;
    }

    public static function verificar_existencia_id($elemento, $nombre_tabla, $id)
    {

        $elemento = self::limpiar_cadena($elemento);
        $nombre_tabla = self::limpiar_cadena($nombre_tabla);
        $id = self::limpiar_cadena($id);

        $consulta = " SELECT $elemento FROM $nombre_tabla
             WHERE $elemento = '$id'";
        $resultado = self::ejecutar_consulta_simple($consulta);
        return ($resultado->rowCount() > 0);
    }




    /*--------- Funcion limpiar cadenas ---------*/
    public static function limpiar_cadena($cadena)
    {
        $cadena = trim($cadena);
        $cadena = stripslashes($cadena);
        $cadena = str_ireplace("<script>", "", $cadena);
        $cadena = str_ireplace("</script>", "", $cadena);
        $cadena = str_ireplace("<script src", "", $cadena);
        $cadena = str_ireplace("<script type=", "", $cadena);
        $cadena = str_ireplace("SELECT * FROM", "", $cadena);
        $cadena = str_ireplace("DELETE FROM", "", $cadena);
        $cadena = str_ireplace("INSERT INTO", "", $cadena);
        $cadena = str_ireplace("DROP TABLE", "", $cadena);
        $cadena = str_ireplace("DROP DATABASE", "", $cadena);
        $cadena = str_ireplace("TRUNCATE TABLE", "", $cadena);
        $cadena = str_ireplace("SHOW TABLES", "", $cadena);
        $cadena = str_ireplace("SHOW DATABASES", "", $cadena);
        $cadena = str_ireplace("<?php", "", $cadena);
        $cadena = str_ireplace("?>", "", $cadena);
        $cadena = str_ireplace("--", "", $cadena);
        $cadena = str_ireplace(">", "", $cadena);
        $cadena = str_ireplace("<", "", $cadena);
        $cadena = str_ireplace("[", "", $cadena);
        $cadena = str_ireplace("]", "", $cadena);
        $cadena = str_ireplace("^", "", $cadena);
        $cadena = str_ireplace("==", "", $cadena);
        $cadena = str_ireplace(";", "", $cadena);
        $cadena = str_ireplace("::", "", $cadena);
        $cadena = stripslashes($cadena);
        $cadena = trim($cadena);
        return $cadena;
    }


    /*--------- Funcion verificar datos ---------*/
    public static function verificar_datos($filtro, $cadena)
    {
        if (preg_match("/^" . $filtro . "$/", $cadena)) {
            return false;
        } else {
            return true;
        }
    }


    /*--------- Funcion verificar fechas ---------*/
    protected static function verificar_fecha($fecha)
    {
        $valores = explode('-', $fecha);
        if (count($valores) == 3 && checkdate($valores[1], $valores[2], $valores[0])) {
            return false;
        } else {
            return true;
        }
    }

    /*--------- Funciones Universales de Validación ---------*/
    public static function validar_cedula_universal($ci, $tabla = null, $columna_bd = null, $id_valor = null, $columna_id = null)
    {
        $ci = self::limpiar_cadena($ci);
        $ci_upper = strtoupper($ci);
        if (!preg_match('/^[VE][1-9][0-9]{6,7}$/', $ci_upper)) {
            return self::generarAlertaSimple("La cédula no tiene un formato válido (ej: V12345678).", "error", "simple");
        }
        if ($tabla != null && $columna_bd != null) {
            $pdo = self::conectar();
            $sql_str = "SELECT `$columna_bd` FROM `$tabla` WHERE `$columna_bd` = :ci";
            if ($id_valor != null && $columna_id != null) {
                $sql_str .= " AND `$columna_id` != :id";
            }

            $query_col = $pdo->prepare("DESCRIBE `$tabla`");
            $query_col->execute();
            $cols = $query_col->fetchAll(PDO::FETCH_COLUMN);
            $estado_col = null;
            foreach ($cols as $c) {
                $cl = strtolower($c);
                if (str_starts_with($cl, 'estado_') || $cl === 'estado' || str_starts_with($cl, 'archivar_') || $cl === 'archivada' || $cl === 'archivado') {
                    $estado_col = $c;
                    break;
                }
            }
            if ($estado_col != null) {
                $sql_str .= " AND `$estado_col` = 1";
            }

            $sql = $pdo->prepare($sql_str);
            $sql->bindParam(":ci", $ci_upper);
            if ($id_valor != null && $columna_id != null) {
                $sql->bindParam(":id", $id_valor);
            }
            $sql->execute();
            if ($sql->rowCount() > 0) {
                return self::generarAlertaSimple("La cédula ya se encuentra registrada en el sistema.", "error", "simple");
            }
        }
        return ["Tipo" => "success"];
    }

    public static function validar_telefono_universal($telefono, $tabla = null, $columna_bd = null, $id_valor = null, $columna_id = null)
    {
        $telefono = self::limpiar_cadena($telefono);
        if (!preg_match('/^\d{11}$/', $telefono)) {
            return self::generarAlertaSimple("El teléfono debe tener exactamente 11 dígitos numéricos.", "error", "simple");
        }
        $prefijos_validos = ['0212', '0412', '0414', '0415', '0416', '0417', '0418', '0424', '0426', '0246', '0247', '0235', '0238', '0239'];
        $prefijo = substr($telefono, 0, 4);
        $sufijo = substr($telefono, 4);
        if (!in_array($prefijo, $prefijos_validos)) {
            return self::generarAlertaSimple("El prefijo del teléfono no es válido.", "error", "simple");
        }
        if ($sufijo === '0000000') {
            return self::generarAlertaSimple("El número de teléfono es inválido.", "error", "simple");
        }
        if ($tabla != null && $columna_bd != null) {
            $pdo = self::conectar();
            $sql_str = "SELECT `$columna_bd` FROM `$tabla` WHERE `$columna_bd` = :tel";
            if ($id_valor != null && $columna_id != null) {
                $sql_str .= " AND `$columna_id` != :id";
            }

            $query_col = $pdo->prepare("DESCRIBE `$tabla`");
            $query_col->execute();
            $cols = $query_col->fetchAll(PDO::FETCH_COLUMN);
            $estado_col = null;
            foreach ($cols as $c) {
                $cl = strtolower($c);
                if (str_starts_with($cl, 'estado_') || $cl === 'estado' || str_starts_with($cl, 'archivar_') || $cl === 'archivada' || $cl === 'archivado') {
                    $estado_col = $c;
                    break;
                }
            }
            if ($estado_col != null) {
                $sql_str .= " AND `$estado_col` = 1";
            }

            $sql = $pdo->prepare($sql_str);
            $sql->bindParam(":tel", $telefono);
            if ($id_valor != null && $columna_id != null) {
                $sql->bindParam(":id", $id_valor);
            }
            $sql->execute();
            if ($sql->rowCount() > 0) {
                return self::generarAlertaSimple("El teléfono ya se encuentra registrado en el sistema.", "error", "simple");
            }
        }
        return ["Tipo" => "success"];
    }

    public static function validar_correo_universal($correo, $tabla = null, $columna_bd = null, $id_valor = null, $columna_id = null)
    {
        $correo = strtolower(self::limpiar_cadena($correo));
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            return self::generarAlertaSimple("El formato de correo no es válido.", "error", "simple");
        }
        $dominios_validos = ['@gmail.com', '@yahoo.com', '@hotmail.com', '@outlook.com', '@live.com', '@icloud.com', '@aol.com', '@protonmail.com', '@gmx.com', '@zoho.com'];
        $dominio_valido = false;
        foreach ($dominios_validos as $dom) {
            if (str_ends_with($correo, $dom)) {
                $dominio_valido = true;
                break;
            }
        }
        if (!$dominio_valido) {
            return self::generarAlertaSimple("Solo se permiten correos de dominios personales conocidos (Ej: Gmail, Yahoo, etc).", "error", "simple");
        }
        if ($tabla != null && $columna_bd != null) {
            $pdo = self::conectar();
            $sql_str = "SELECT `$columna_bd` FROM `$tabla` WHERE `$columna_bd` = :correo";
            if ($id_valor != null && $columna_id != null) {
                $sql_str .= " AND `$columna_id` != :id";
            }

            $query_col = $pdo->prepare("DESCRIBE `$tabla`");
            $query_col->execute();
            $cols = $query_col->fetchAll(PDO::FETCH_COLUMN);
            $estado_col = null;
            foreach ($cols as $c) {
                $cl = strtolower($c);
                if (str_starts_with($cl, 'estado_') || $cl === 'estado' || str_starts_with($cl, 'archivar_') || $cl === 'archivada' || $cl === 'archivado') {
                    $estado_col = $c;
                    break;
                }
            }
            if ($estado_col != null) {
                $sql_str .= " AND `$estado_col` = 1";
            }

            $sql = $pdo->prepare($sql_str);
            $sql->bindParam(":correo", $correo);
            if ($id_valor != null && $columna_id != null) {
                $sql->bindParam(":id", $id_valor);
            }
            $sql->execute();
            if ($sql->rowCount() > 0) {
                return self::generarAlertaSimple("El correo ya se encuentra registrado en el sistema.", "error", "simple");
            }
        }
        return ["Tipo" => "success"];
    }



    /*--------- Función para archivado en cascada ---------*/
    protected static function archivar_registros_cascada($conexion, $tabla, $columna_id, $id_valor, $configuracion)
    {
        try {
            // 1. Archivar el registro principal
            // El nombre de la columna de estado se define en el mapa para mayor flexibilidad
            $col_estado = $configuracion['columna_estado'];
            $sql = $conexion->prepare("UPDATE $tabla SET $col_estado = 0 WHERE $columna_id = :id");
            $sql->execute([':id' => $id_valor]);

            // 2. Procesar hijos (si existen)
            if (isset($configuracion['hijos']) && is_array($configuracion['hijos'])) {
                foreach ($configuracion['hijos'] as $hijo) {

                    // Buscar los IDs de los hijos para seguir la cadena
                    $stmt = $conexion->prepare("SELECT {$hijo['columna_id']} FROM {$hijo['tabla']} WHERE {$hijo['columna_fk']} = :id");
                    $stmt->execute([':id' => $id_valor]);
                    $registros_hijos = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    foreach ($registros_hijos as $reg_hijo) {
                        $id_hijo = $reg_hijo[$hijo['columna_id']];

                        // Llamada recursiva: Archiva al hijo y a sus propios hijos
                        self::archivar_registros_cascada(
                            $conexion,
                            $hijo['tabla'],
                            $hijo['columna_id'],
                            $id_hijo,
                            $hijo
                        );
                    }
                }
            }

            // 3. Ejecutar consultas extras (como el CONCAT de observaciones)
            if (isset($configuracion['extra_sql'])) {
                $stmt_extra = $conexion->prepare($configuracion['extra_sql']);
                $stmt_extra->execute([':id' => $id_valor]);
            }

        } catch (Exception $e) {
            // Lanzamos la excepción indicando en qué tabla falló
            throw new Exception("Error en tabla [$tabla]: " . $e->getMessage());
        }
    }


    /*--------- Funcion paginador de tablas - ACTUALIZADA ---------*/
    protected static function paginador_tablas($pagina, $Npaginas, $url, $botones)
    {
        $tabla = '<nav aria-label="Page navigation example"><ul class="pagination justify-content-center">';

        if ($pagina == 1) {
            $tabla .= '<li class="page-item disabled"><a class="page-link"><i class="fas fa-angle-double-left"></i></a></li>';
        } else {
            $tabla .= '
                <li class="page-item"><a class="page-link" href="' . $url . '1"><i class="fas fa-angle-double-left"></i></a></li>
                <li class="page-item"><a class="page-link" href="' . $url . ($pagina - 1) . '">Anterior</a></li>
                ';
        }

        $ci = 0;
        for ($i = $pagina; $i <= $Npaginas; $i++) {
            if ($ci >= $botones) {
                break;
            }
            if ($pagina == $i) {
                $tabla .= '<li class="page-item"><a class="page-link active" href="' . $url . $i . '">' . $i . '</a></li>';
            } else {
                $tabla .= '<li class="page-item"><a class="page-link" href="' . $url . $i . '">' . $i . '</a></li>';
            }
            $ci++;
        }

        if ($pagina == $Npaginas) {
            $tabla .= '<li class="page-item disabled"><a class="page-link"><i class="fas fa-angle-double-right"></i></a></li>';
        } else {
            $tabla .= '
                <li class="page-item"><a class="page-link" href="' . $url . ($pagina + 1) . '">Siguiente</a></li>
                <li class="page-item"><a class="page-link" href="' . $url . $Npaginas . '"><i class="fas fa-angle-double-right"></i></a></li>
                ';
        }

        $tabla .= '</ul></nav>';
        return $tabla;
    }



    //modal de secciones

    public static function generar_modal_secciones_js($vista_destino = "seccion-consult")
    {
        // $vista_destino será algo como "seccion-consult"
        return '
            <div id="modalSecciones" class="modal" tabindex="-1" style="display:none; position:fixed; z-index:9999; top:0; left:0; width:100%; height:100%; background: rgba(0,0,0,0.75); overflow:auto;">
                <div style="position:relative; margin:auto; top:5%; max-width:800px; background:#222; padding:20px; border:3px solid #fff; border-radius:8px;">
                    <h5 id="tituloSecciones" class="text-white text-center mb-3">Secciones</h5>
                    <div id="contenedorSecciones" class="d-flex flex-wrap justify-content-center"></div>
                    <button type="button" onclick="document.getElementById(\'modalSecciones\').style.display=\'none\'" style="position:absolute; top:10px; right:10px;" class="btn btn-sm btn-light">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>

            <script>
            document.querySelectorAll(".ver-secciones, .ver-secciones-archivadas").forEach(btn => {
                btn.addEventListener("click", function() {
                    const modal = document.getElementById("modalSecciones");
                    const contenedor = document.getElementById("contenedorSecciones");
                    const titulo = document.getElementById("tituloSecciones");
                    const cursoId = this.dataset.id;
                    const nombreCurso = this.dataset.nombre;
                    const soloArchivadas = this.dataset.archivadas === "1";
                    const vistaDestino = this.dataset.vista || "' . $vista_destino . '";
                    const carpeta = this.dataset.carpeta || "admin";

                    titulo.textContent = (soloArchivadas ? "Secciones Archivadas de " : "Secciones de ") + nombreCurso;
                    contenedor.innerHTML = "<div class=\'text-light\'>Cargando...</div>";

                    const params = { curso_vigente_id: cursoId };
                    if (soloArchivadas) {
                        params.solo_archivadas = "1";
                    }
                    const body = new URLSearchParams(params).toString();

                    fetch("' . SERVERURL . 'ajax/periodoAjax.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/x-www-form-urlencoded" },
                        body: body
                    })
                    .then(res => res.json())
                    .then(data => {
                        contenedor.innerHTML = "";
                        if (!data || data.length === 0) {
                            contenedor.innerHTML = `<span class="badge ${soloArchivadas ? \'bg-warning text-dark\' : \'bg-danger\'}">No hay secciones</span>`;
                            return;
                        }
                        data.forEach(sec => {
                            const name = sec.nombre_profesor || "";
                            const lastname = sec.apellido_profesor || "";
                            const profText = (name || lastname) ? `${name} ${lastname}` : "<em>Sin asignar</em>";
                            const card = document.createElement("div");
                            card.className = soloArchivadas ? "card bg-warning text-dark m-2 p-2" : "card bg-secondary text-white m-2 p-2";
                            card.style.width = "160px";
                            const a2Param = soloArchivadas ? "&a2=1" : "";
                            card.innerHTML = `
                                <div class="card-body p-2 text-center">
                                    <h6 class="mb-1">Sección ${sec.nombre_seccion}</h6>
                                    <p class="small mb-1">Id: ${sec.id_seccion}</p>
                                    <p class="small mb-1">Día: ${sec.dia}</p>
                                    <p class="small mb-1">Prof.: ${profText}</p>
                                    <p class="small mb-1">Inscritos: ${sec.inscritos || 0}</p>
                                    <p class="small mb-1">Evaluaciones: ${sec.evaluaciones || 0}</p>
                                    <a href="' . SERVERURL . 'index.php?views=${encodeURIComponent(carpeta)}/${encodeURIComponent(vistaDestino)}&curso_vigente=${cursoId}&seccion=${sec.nombre_seccion}${a2Param}" class="btn btn-sm btn-light w-100 mt-1">Ver</a>
                                </div>`;
                            contenedor.appendChild(card);
                        });
                    })
                    .catch(err => {
                        console.error("Error al cargar secciones:", err);
                        contenedor.innerHTML = "<span class=\'badge bg-danger\'>Error al cargar secciones</span>";
                    });
                    modal.style.display = "block";
                });
            });
            </script>';
    }


    protected static function ejecutar_consulta_paginada($consulta, $inicio, $registros)
    {
        $conexion = self::conectar();
        $datos = $conexion->query($consulta)->fetchAll();
        $total = (int) $conexion->query("SELECT FOUND_ROWS()")->fetchColumn();

        return [
            "datos" => $datos,
            "total" => $total,
            "paginas" => ceil($total / $registros)
        ];
    }




    //---------------- interacciones, regitros de ip e intentos fallidos de formularios------------------------
    //interacciones banner
    public static function registrar_ip_visitante($pdo, $direccionIP)
    {
        $sqlBuscar = "SELECT id_ip_visitante FROM ip_visitante WHERE direccion_ip = :ip";
        $stmt = $pdo->prepare($sqlBuscar);
        $stmt->execute([':ip' => $direccionIP]);
        $idIP = $stmt->fetchColumn();

        if (!$idIP) {
            $sqlInsert = "INSERT INTO ip_visitante (direccion_ip) VALUES (:ip)";
            $stmt = $pdo->prepare($sqlInsert);
            $stmt->execute([':ip' => $direccionIP]);
            $idIP = $pdo->lastInsertId();
        }

        return intval($idIP);
    }

    public static function registrar_interaccion_banner($pdo, $idMateria, $idIP, $tipoBanner)
    {
        $sqlCurso = "SELECT id_curso FROM curso_publicidad WHERE id_materia = :idMateria";
        $stmtCurso = $pdo->prepare($sqlCurso);
        $stmtCurso->execute([':idMateria' => $idMateria]);
        $idCurso = $stmtCurso->fetchColumn();

        if ($idCurso) {
            $fechaClick = date("Y-m-d H:i:s");
            $sql = "INSERT INTO interaccion_banner (fecha_hora, id_curso_fk, id_ip_fk, tipo_banner)
						VALUES (:fecha, :idCurso, :idIP, :tipo)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':fecha' => $fechaClick,
                ':idCurso' => $idCurso,
                ':idIP' => $idIP,
                ':tipo' => $tipoBanner
            ]);
        }
    }








    public static function registrar_intento_ip($ip, $tipo = 'login')
    {
        $pdo = self::conectar();

        // Buscar registro de IP
        $sqlBuscar = "SELECT intentos_login_fallidos, intentos_recuperacion_fallidos, intentos_cc_fallidos,
                             bloqueo_login_hasta, bloqueo_recuperacion_hasta, bloqueo_cc_hasta
                      FROM ip_visitante WHERE direccion_ip = :ip";
        $stmtBuscar = $pdo->prepare($sqlBuscar);
        $stmtBuscar->execute([':ip' => $ip]);
        $registro = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

        $intentos = 0;
        $bloqueoHasta = null;
        $campoIntentos = '';
        $campoBloqueo = '';
        //    var_dump($ip, $tipo);
        //exit(); // para detener la ejecución y ver el resultado

        if ($registro) {
            if ($tipo === 'login') {
                $intentos = (int) $registro['intentos_login_fallidos'] + 1;
                $campoIntentos = 'intentos_login_fallidos';
                $campoBloqueo = 'bloqueo_login_hasta';

                if ($intentos >= 15) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 86400);
                } elseif ($intentos == 12) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 3600);
                } elseif ($intentos == 9) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 600);
                } elseif ($intentos == 6) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 300);
                } elseif ($intentos == 3) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 60);
                }

            } elseif ($tipo === 'recuperacion') {
                $intentos = (int) $registro['intentos_recuperacion_fallidos'] + 1;
                $campoIntentos = 'intentos_recuperacion_fallidos';
                $campoBloqueo = 'bloqueo_recuperacion_hasta';

                if ($intentos >= 10) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 86400);
                } elseif ($intentos == 5) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 600);
                }

            } elseif ($tipo === 'cc') {
                $intentos = (int) $registro['intentos_cc_fallidos'] + 1;
                $campoIntentos = 'intentos_cc_fallidos';
                $campoBloqueo = 'bloqueo_cc_hasta';

                if ($intentos >= 10) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 86400);
                } elseif ($intentos == 5) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 600);
                }
            }

            // Construir consulta dinámica
            $sqlUpdate = "UPDATE ip_visitante SET $campoIntentos = :intentos";
            if ($bloqueoHasta) {
                $sqlUpdate .= ", $campoBloqueo = :bloqueo";
            }
            $sqlUpdate .= " WHERE direccion_ip = :ip";

            $stmtUpdate = $pdo->prepare($sqlUpdate);
            $params = [':intentos' => $intentos, ':ip' => $ip];
            if ($bloqueoHasta) {
                $params[':bloqueo'] = $bloqueoHasta;
            }
            $stmtUpdate->execute($params);

        } else {
            // Crear nuevo registro con el campo correspondiente
            $campoIntentos = match ($tipo) {
                'login' => 'intentos_login_fallidos',
                'recuperacion' => 'intentos_recuperacion_fallidos',
                'cc' => 'intentos_cc_fallidos',
                default => 'intentos_login_fallidos'
            };

            $sqlInsert = "INSERT INTO ip_visitante (direccion_ip, $campoIntentos) VALUES (:ip, 1)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([':ip' => $ip]);
        }
    }













    /*
    public static function registrar_intento_ip($ip, $tipo = 'login') {
        $pdo = self::conectar();

        // Buscar registro de IP
        $sqlBuscar = "SELECT intentos_login_fallidos, intentos_recuperacion_fallidos, intentos_cc_fallidos FROM ip_visitante WHERE direccion_ip = :ip";
        $stmtBuscar = $pdo->prepare($sqlBuscar);
        $stmtBuscar->execute([':ip' => $ip]);
        $registro = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

        $intentos = 0;
        $bloqueoHasta = null;

        if ($registro) {
            if ($tipo === 'login') {
                $intentos = (int)$registro['intentos_login_fallidos'] + 1;

                if ($intentos >= 15) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 86400);
                } elseif ($intentos == 12) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 3600);
                } elseif ($intentos == 9) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 600);
                } elseif ($intentos == 6) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 300);
                } elseif ($intentos == 3) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 60);
                }

                $sqlUpdate = "UPDATE ip_visitante 
                              SET intentos_login_fallidos = :intentos" . 
                              ($bloqueoHasta ? ", bloqueo_hasta = :bloqueo" : "") . 
                              " WHERE direccion_ip = :ip";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $params = [':intentos' => $intentos, ':ip' => $ip];
                if ($bloqueoHasta) $params[':bloqueo'] = $bloqueoHasta;
                $stmtUpdate->execute($params);

            } elseif ($tipo === 'recuperacion') {
                $intentos = (int)$registro['intentos_recuperacion_fallidos'] + 1;

                if ($intentos >= 10) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 86400);
                } elseif ($intentos == 5) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 600);
                }

                $sqlUpdate = "UPDATE ip_visitante 
                              SET intentos_recuperacion_fallidos = :intentos" . 
                              ($bloqueoHasta ? ", bloqueo_hasta = :bloqueo" : "") . 
                              " WHERE direccion_ip = :ip";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $params = [':intentos' => $intentos, ':ip' => $ip];
                if ($bloqueoHasta) $params[':bloqueo'] = $bloqueoHasta;
                $stmtUpdate->execute($params);

            } elseif ($tipo === 'cc') {
                $intentos = (int)$registro['intentos_cc_fallidos'] + 1;

                if ($intentos >= 10) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 86400);
                } elseif ($intentos == 5) {
                    $bloqueoHasta = date("Y-m-d H:i:s", time() + 600);
                }

                $sqlUpdate = "UPDATE ip_visitante 
                              SET intentos_cc_fallidos = :intentos" . 
                              ($bloqueoHasta ? ", bloqueo_hasta = :bloqueo" : "") . 
                              " WHERE direccion_ip = :ip";
                $stmtUpdate = $pdo->prepare($sqlUpdate);
                $params = [':intentos' => $intentos, ':ip' => $ip];
                if ($bloqueoHasta) $params[':bloqueo'] = $bloqueoHasta;
                $stmtUpdate->execute($params);
            }

        } else {
            // Crear nuevo registro
            $campo = match ($tipo) {
                'login' => 'intentos_login_fallidos',
                'recuperacion' => 'intentos_recuperacion_fallidos',
                'cc' => 'intentos_cc_fallidos',
                default => 'intentos_login_fallidos'
            };

            $sqlInsert = "INSERT INTO ip_visitante (direccion_ip, $campo) VALUES (:ip, 1)";
            $stmtInsert = $pdo->prepare($sqlInsert);
            $stmtInsert->execute([':ip' => $ip]);
        }
    }
    */

    /* no tiene cc
            public static function registrar_intento_ip($ip, $tipo = 'login') {
                $pdo = self::conectar();

                // Buscar registro de IP
                $sqlBuscar = "SELECT intentos_login_fallidos, intentos_recuperacion_fallidos FROM ip_visitante WHERE direccion_ip = :ip";
                $stmtBuscar = $pdo->prepare($sqlBuscar);
                $stmtBuscar->execute([':ip' => $ip]);
                $registro = $stmtBuscar->fetch(PDO::FETCH_ASSOC);

                // Inicializar contadores
                $intentos = 0;
                $bloqueoHasta = null;

                if ($registro) {
                    if ($tipo === 'login') {
                        $intentos = (int)$registro['intentos_login_fallidos'] + 1;

                        // Penalización progresiva para login
                        if ($intentos >= 15) {
                            $bloqueoHasta = date("Y-m-d H:i:s", time() + 86400); // 1 día
                        } elseif ($intentos >= 12) {
                            $bloqueoHasta = date("Y-m-d H:i:s", time() + 3600); // 1 hora
                        } elseif ($intentos >= 9) {
                            $bloqueoHasta = date("Y-m-d H:i:s", time() + 600); // 10 minutos
                        } elseif ($intentos >= 6) {
                            $bloqueoHasta = date("Y-m-d H:i:s", time() + 300); // 5 minutos
                        } elseif ($intentos >= 3) {
                            $bloqueoHasta = date("Y-m-d H:i:s", time() + 60); // 1 minuto
                        }

                        $sqlUpdate = "UPDATE ip_visitante 
                                    SET intentos_login_fallidos = :intentos" . 
                                    ($bloqueoHasta ? ", bloqueo_hasta = :bloqueo" : "") . 
                                    " WHERE direccion_ip = :ip";
                        $stmtUpdate = $pdo->prepare($sqlUpdate);
                        $params = [':intentos' => $intentos, ':ip' => $ip];
                        if ($bloqueoHasta) $params[':bloqueo'] = $bloqueoHasta;
                        $stmtUpdate->execute($params);

                    } elseif ($tipo === 'recuperacion') {
                        $intentos = (int)$registro['intentos_recuperacion_fallidos'] + 1;

                        // Penalización progresiva para recuperación
                        if ($intentos >= 10) {
                            $bloqueoHasta = date("Y-m-d H:i:s", time() + 86400); // 1 día
                        } elseif ($intentos >= 5) {
                            $bloqueoHasta = date("Y-m-d H:i:s", time() + 600); // 10 minutos
                        }

                        $sqlUpdate = "UPDATE ip_visitante 
                                    SET intentos_recuperacion_fallidos = :intentos" . 
                                    ($bloqueoHasta ? ", bloqueo_hasta = :bloqueo" : "") . 
                                    " WHERE direccion_ip = :ip";
                        $stmtUpdate = $pdo->prepare($sqlUpdate);
                        $params = [':intentos' => $intentos, ':ip' => $ip];
                        if ($bloqueoHasta) $params[':bloqueo'] = $bloqueoHasta;
                        $stmtUpdate->execute($params);
                    }

                } else {
                    // Crear nuevo registro
                    $sqlInsert = "INSERT INTO ip_visitante (direccion_ip, " . 
                                ($tipo === 'login' ? "intentos_login_fallidos" : "intentos_recuperacion_fallidos") . ") 
                                VALUES (:ip, 1)";
                    $stmtInsert = $pdo->prepare($sqlInsert);
                    $stmtInsert->execute([':ip' => $ip]);
                }
            }
    */


    public static function ip_bloqueada($ip, $tipo = 'login')
    {
        $pdo = self::conectar();

        // Determinar el campo de bloqueo según el tipo
        $campoBloqueo = match ($tipo) {
            'login' => 'bloqueo_login_hasta',
            'recuperacion' => 'bloqueo_recuperacion_hasta',
            'cc' => 'bloqueo_cc_hasta',
            default => 'bloqueo_login_hasta'
        };

        // Consulta dinámica
        $sql = "SELECT $campoBloqueo FROM ip_visitante WHERE direccion_ip = :ip";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':ip' => $ip]);
        $bloqueo = $stmt->fetchColumn();

        if ($bloqueo) {
            $timestamp = strtotime($bloqueo);
            $ahora = time();

            if ($timestamp > $ahora) {
                $restante = $timestamp - $ahora;
                return [
                    'bloqueado' => true,
                    'tiempo_restante' => $restante
                ];
            }
        }

        return [
            'bloqueado' => false,
            'tiempo_restante' => null
        ];
    }


    // se llama en formularios para ver si los usuarios ya sobrepasaron el limite de intentos
/*
		public static function ip_bloqueada($ip, $tipo = 'login') {
			$pdo = self::conectar();
			$sql = "SELECT bloqueo_hasta FROM ip_visitante WHERE direccion_ip = :ip";
			$stmt = $pdo->prepare($sql);
			$stmt->execute([':ip' => $ip]);
			$bloqueo = $stmt->fetchColumn();

			if ($bloqueo) {
				$timestamp = strtotime($bloqueo);
				$ahora = time();

				if ($timestamp > $ahora) {
					$restante = $timestamp - $ahora;
					return [
						'bloqueado' => true,
						'tiempo_restante' => $restante
					];
				}
			}

			return [
				'bloqueado' => false,
				'tiempo_restante' => null
			];
		}
*/




    public static function reiniciar_intentos_ip($ip, $contexto)
    {
        $conexion = self::conectar();

        if ($contexto === 'login') {
            $sql = $conexion->prepare("
            UPDATE ip_visitante 
            SET intentos_login_fallidos = 0, bloqueo_login_hasta = NULL 
            WHERE direccion_ip = :ip
        ");
        } elseif ($contexto === 'recuperacion') {
            $sql = $conexion->prepare("
            UPDATE ip_visitante 
            SET intentos_recuperacion_fallidos = 0, bloqueo_recuperacion_hasta = NULL 
            WHERE direccion_ip = :ip
        ");
        } elseif ($contexto === 'cc') {
            $sql = $conexion->prepare("
            UPDATE ip_visitante 
            SET intentos_cc_fallidos = 0, bloqueo_cc_hasta = NULL 
            WHERE direccion_ip = :ip
        ");
        } else {
            return false; // Contexto inválido
        }

        $sql->bindParam(":ip", $ip);
        return $sql->execute();
    }


    /* sin intentos cc 24925
            public static function reiniciar_intentos_ip($ip, $contexto) {
                $conexion = self::conectar();

                if ($contexto === 'login') {
                    $sql = $conexion->prepare("
                        UPDATE ip_visitante 
                        SET intentos_login_fallidos = 0, bloqueo_hasta = NULL 
                        WHERE direccion_ip = :ip
                    ");
                } elseif ($contexto === 'recuperacion') {
                    $sql = $conexion->prepare("
                        UPDATE ip_visitante 
                        SET intentos_recuperacion_fallidos = 0, bloqueo_hasta = NULL 
                        WHERE direccion_ip = :ip
                    ");
                } else {
                    return false; // Contexto inválido
                }

                $sql->bindParam(":ip", $ip);
                return $sql->execute();
            }
    */


    public static function actualizarEstadoCohortes(PDO $pdo)
    {
        $hoy = date('Y-m-d');

        try {
            // Activar cohortes que inician hoy
            $sqlInicio = "
            UPDATE cohorte
            SET estado_cohorte = 'Vigente'
            WHERE fecha_inicio <= :hoy
              AND estado_cohorte = 'Próximo'
        ";
            $stmtInicio = $pdo->prepare($sqlInicio);
            $stmtInicio->execute([':hoy' => $hoy]);

            // Finalizar cohortes que terminan hoy
            $sqlFin = "
            UPDATE cohorte
            SET estado_cohorte = 'Terminado'
            WHERE fecha_fin <= :hoy
              AND estado_cohorte = 'Vigente'
        ";
            $stmtFin = $pdo->prepare($sqlFin);
            $stmtFin->execute([':hoy' => $hoy]);

            return [
                'activadas' => $stmtInicio->rowCount(),
                'finalizadas' => $stmtFin->rowCount()
            ];

        } catch (PDOException $e) {
            error_log(" Error al actualizar estado de cohortes: " . $e->getMessage());
            return false;
        }
    }


    public static function actualizarEstadoPeriodosIntensivos(PDO $pdo)
    {
        $hoy = date('Y-m-d');

        try {
            // Activar periodos que inician hoy o antes
            $sqlInicio = "
            UPDATE periodo_intensivo
            SET estado_periodo = 'Vigente'
            WHERE fecha_inicio_int <= :hoy
              AND estado_periodo = 'Próximo'
        ";
            $stmtInicio = $pdo->prepare($sqlInicio);
            $stmtInicio->execute([':hoy' => $hoy]);

            // Finalizar periodos que ya terminaron
            $sqlFin = "
            UPDATE periodo_intensivo
            SET estado_periodo = 'Terminado'
            WHERE fecha_fin_int <= :hoy
              AND estado_periodo = 'Vigente'
        ";
            $stmtFin = $pdo->prepare($sqlFin);
            $stmtFin->execute([':hoy' => $hoy]);

            return [
                'activados' => $stmtInicio->rowCount(),
                'finalizados' => $stmtFin->rowCount()
            ];

        } catch (PDOException $e) {
            error_log(" Error al actualizar estado de periodos intensivos: " . $e->getMessage());
            return false;
        }
    }

    public static function limpiarVisitantesYInteracciones(PDO $pdo)
    {
        $limite = date('Y-m-d H:i:s', strtotime('-90 days'));

        try {
            // 1. Eliminar interacciones antiguas
            $sqlInteracciones = "
            DELETE FROM interaccion_banner
            WHERE STR_TO_DATE(fecha_hora, '%Y-%m-%d %H:%i:%s') < :limite
        ";
            $stmtInteracciones = $pdo->prepare($sqlInteracciones);
            $stmtInteracciones->execute([':limite' => $limite]);

            // 2. Eliminar visitantes antiguos
            $sqlVisitantes = "
            DELETE FROM visitante
            WHERE STR_TO_DATE(fecha_hora_entrada, '%Y-%m-%d %H:%i:%s') < :limite
        ";
            $stmtVisitantes = $pdo->prepare($sqlVisitantes);
            $stmtVisitantes->execute([':limite' => $limite]);

            // 3. Eliminar IPs sin actividad reciente
            $sqlIPs = "
            DELETE FROM ip_visitante
            WHERE id_ip NOT IN (
                SELECT DISTINCT id_ip_fk FROM visitante
                WHERE STR_TO_DATE(fecha_hora_entrada, '%Y-%m-%d %H:%i:%s') >= :limite
                UNION
                SELECT DISTINCT id_ip_fk FROM interaccion_banner
                WHERE STR_TO_DATE(fecha_hora, '%Y-%m-%d %H:%i:%s') >= :limite
            )
        ";
            $stmtIPs = $pdo->prepare($sqlIPs);
            $stmtIPs->execute([':limite' => $limite]);

            return [
                'interacciones_eliminadas' => $stmtInteracciones->rowCount(),
                'visitantes_eliminados' => $stmtVisitantes->rowCount(),
                'ips_eliminadas' => $stmtIPs->rowCount()
            ];

        } catch (PDOException $e) {
            error_log(" Error al limpiar visitantes e interacciones: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Función universal para subir imágenes y registrarlas en la tabla 'imagen'.
     * @param array $archivo El array $_FILES['input']
     * @param string $carpetaDestino Ruta destino, ej: "../vistas/cm/ifpg/"
     * @param string $tipoImagen El tipo para BD, ej: "ifpg"
     * @param string $prefijo_nombre Prefijo para uniqid, ej: "logo"
     * @return array Array asociativo con 'status', 'mensaje', 'id_imagen' y 'ruta'
     */
    public static function subir_imagen_universal($archivo, $carpetaDestino, $tipoImagen, $prefijo_nombre)
    {
        if (!isset($archivo['tmp_name']) || $archivo['error'] !== 0) {
            return ["status" => "error", "mensaje" => "Archivo no válido o con errores de subida."];
        }

        $nombreOriginal = basename($archivo['name']);
        $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
        $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'ico'];

        if (!in_array($ext, $permitidas)) {
            return ["status" => "error", "mensaje" => "Formato no permitido: solo JPG, JPEG, PNG, WEBP o ICO"];
        }

        if (!file_exists($carpetaDestino)) {
            mkdir($carpetaDestino, 0777, true);
        }

        $nombreFinal = uniqid($prefijo_nombre . "_") . "." . $ext;
        $rutaCompleta = $carpetaDestino . $nombreFinal;

        // Extraer la ruta relativa desde "cm/..."
        $rutaRelativa = str_replace("../vistas/", "", $carpetaDestino) . $nombreFinal;

        if (move_uploaded_file($archivo['tmp_name'], $rutaCompleta)) {
            $conexion = self::conectar();
            $sql = $conexion->prepare("INSERT INTO imagen (ruta, tipo_imagen) VALUES (:Ruta, :Tipo)");
            $sql->bindParam(":Ruta", $rutaRelativa);
            $sql->bindParam(":Tipo", $tipoImagen);

            if ($sql->execute()) {
                return [
                    "status" => "success",
                    "id_imagen" => $conexion->lastInsertId(),
                    "ruta" => $rutaRelativa
                ];
            } else {
                unlink($rutaCompleta);
                return ["status" => "error", "mensaje" => "No se pudo registrar la imagen en la base de datos."];
            }
        } else {
            return ["status" => "error", "mensaje" => "Fallo al copiar el archivo en el servidor."];
        }
    }

    public static function validar_imagenes(array $archivos, array $restriccionesPorImagen): array
    {
        foreach ($restriccionesPorImagen as $nombre => $restricciones) {
            if (!isset($archivos[$nombre]) || $archivos[$nombre]['error'] !== 0) {
                continue;
            }

            $nombreOriginal = basename($archivos[$nombre]['name']);
            $ext = strtolower(pathinfo($nombreOriginal, PATHINFO_EXTENSION));
            $permitidas = ['jpg', 'jpeg', 'png', 'webp', 'ico'];

            if (!in_array($ext, $permitidas)) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' tiene un formato no permitido."
                ];
            }

            $dimensiones = getimagesize($archivos[$nombre]['tmp_name']);
            if (!$dimensiones) {
                return [
                    'status' => 'error',
                    'mensaje' => "No se pudieron obtener las dimensiones de la imagen '$nombre'."
                ];
            }

            $ancho = $dimensiones[0];
            $alto = $dimensiones[1];
            $formaDetectada = self::determinar_forma_imagen($ancho, $alto);
            $formaEsperada = $restricciones['forma'] ?? '';

            if ($formaEsperada !== '') {
                if ($formaEsperada === 'horizontal_o_cuadrada') {
                    if ($formaDetectada === 'vertical') {
                        return [
                            'status' => 'error',
                            'mensaje' => "La imagen '$nombre' debe ser horizontal o cuadrada, pero se detectó vertical."
                        ];
                    }
                } elseif ($formaDetectada !== $formaEsperada) {
                    return [
                        'status' => 'error',
                        'mensaje' => "La imagen '$nombre' debe ser $formaEsperada, pero se detectó $formaDetectada."
                    ];
                }
            }

            if (!empty($restricciones['min_width']) && $ancho < $restricciones['min_width']) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' debe tener al menos {$restricciones['min_width']} px de ancho."
                ];
            }
            if (!empty($restricciones['min_height']) && $alto < $restricciones['min_height']) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' debe tener al menos {$restricciones['min_height']} px de alto."
                ];
            }
            if (!empty($restricciones['max_width']) && $ancho > $restricciones['max_width']) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' no debe superar {$restricciones['max_width']} px de ancho."
                ];
            }
            if (!empty($restricciones['max_height']) && $alto > $restricciones['max_height']) {
                return [
                    'status' => 'error',
                    'mensaje' => "La imagen '$nombre' no debe superar {$restricciones['max_height']} px de alto."
                ];
            }
        }

        return ['status' => 'success', 'mensaje' => 'OK'];
    }

    private static function determinar_forma_imagen(int $ancho, int $alto, float $tolerancia = 0.1): string
    {
        if ($alto === 0) {
            return 'desconocida';
        }

        $ratio = $ancho / $alto;
        if (abs($ratio - 1) <= $tolerancia) {
            return 'cuadrada';
        }

        return $ratio > 1 ? 'horizontal' : 'vertical';
    }

}