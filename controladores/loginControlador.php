<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

if($peticionAjax){
    require_once __DIR__ . '/../modelos/login_mdo.php';
}else{
    require_once __DIR__ . '/../modelos/login_mdo.php';
}

class loginControlador extends loginModelo{

    /*--------- Controlador iniciar sesion ---------*/
    public function iniciar_sesion_controlador(){
        $usuario=mainModel::limpiar_cadena($_POST['usuario_log']);
        $clave=mainModel::limpiar_cadena($_POST['clave_log']);

        /*== comprobar campos vacios ==*/
        if($usuario=="" || $clave==""){
            echo '
            <script>
                Swal.fire({
                    title: "Ocurrio un error inesperado",
                    text: "No has llenado todos los campos que son requeridos",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
            exit();
        }

        /*== Verificando integridad de los datos ==*/
        if(mainModel::verificar_datos("[a-zA-Z0-9\-]{1,35}",$usuario)){
            echo '
            <script>
                Swal.fire({
                    title: "Ocurrio un error inesperado",
                    text: "El NOMBRE DE USUARIO no coincide con el formato solicitado",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
            exit();
        }

        if(mainModel::verificar_datos("[-a-zA-Z0-9@._]{7,100}",$clave)){
            echo '
            <script>
                Swal.fire({
                    title: "Ocurrio un error inesperado",
                    text: "La CLAVE no coincide con el formato solicitado",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
            exit();
        }

        $clave=mainModel::encryption($clave);
		
        
        $datos_login=[
            "Usuario"=>$usuario,
            "Clave"=>$clave
        ];

        $datos_cuenta=loginModelo::iniciar_sesion_modelo($datos_login);
		       // mainModel::registrar_intento_ip($ip, 'recuperacion');
        if($datos_cuenta->rowCount()==1){
            $row=$datos_cuenta->fetch();

                    mainModel::reiniciar_intentos_ip($_SERVER['REMOTE_ADDR'], 'login');
            
            // DEBUG: Mostrar datos del usuario
            error_log("=== DEBUG LOGIN ===");
            error_log("Usuario encontrado:");
            error_log("ID_usuario: " . $row['ID_usuario']);
            error_log("Nombre: " . $row['Nombre']);
            error_log("Cargo: " . $row['cargo']);

            session_start(['name'=>'SPM']);

            // === VARIABLES DE SESIÓN ORIGINALES ===
            $_SESSION['id_spm'] = $row['ID_usuario'];
            $_SESSION['usuario_spm'] = $row['Nombre'];
            $_SESSION['cargo_spm'] = $row['cargo'];
            $_SESSION['token_spm']=md5(uniqid(mt_rand(),true));

            // === NUEVAS VARIABLES PARA NOTIFICACIONES ===
            $_SESSION['id_usuario'] = $row['ID_usuario'];
            
            // DEBUG
            error_log("Sesiones establecidas:");
            error_log("id_spm: " . $_SESSION['id_spm']);
            error_log("id_usuario: " . $_SESSION['id_usuario']);
            error_log("cargo_spm: " . $_SESSION['cargo_spm']);

            // Si es estudiante (rol 5), buscar su id_estudiante
            if($row['cargo'] == 5) {
                $id_estudiante = $this->obtener_id_estudiante($row['ID_usuario']);
                error_log("Buscando id_estudiante para usuario: " . $row['ID_usuario']);
                
                if($id_estudiante) {
                    $_SESSION['id_estudiante'] = $id_estudiante;
                    error_log("id_estudiante encontrado: " . $id_estudiante);
                } else {
                    error_log("NO se encontró id_estudiante para este usuario");
                }
            } else {
                error_log("No es estudiante (cargo != 5), no se busca id_estudiante");
            }

            // === REDIRECCIÓN ORIGINAL ===
            switch ((int)$row['cargo']) {
                case 1:
                    header("Location: ".SERVERURL."index.php?views=admin/home");
                    break;
                case 4:
                    header("Location: ".SERVERURL."index.php?views=Supervisor de cursos/home");
                    break;
                case 3:
                    header("Location: ".SERVERURL."index.php?views=administrador de pagos/home");
                    break;
                case 2:
                    header("Location: ".SERVERURL."index.php?views=Control de estudio/home");
                    break;
                case 5:
                    header("Location: ".SERVERURL."index.php?views=Estudiante/home");
                    break;
                default:
                    echo '
                    <script>
                        Swal.fire({
                            title: "Error",
                            text: "Cargo no reconocido",
                            icon: "error",
                            confirmButtonText: "Aceptar"
                        });
                    </script>';
            }
            exit();
        } else {
            mainModel::registrar_intento_ip($_SERVER['REMOTE_ADDR'], 'login');

            echo '
            <script>
                Swal.fire({
                    title: "Ocurrio un error inesperado",
                    text: "El USUARIO o CLAVE son incorrectos",
                    type: "error",
                    confirmButtonText: "Aceptar"
                });
            </script>
            ';
        }
    } /* Fin controlador */

            /*=== NUEVO MÉTODO PARA OBTENER ID_ESTUDIANTE ===*/
    private function obtener_id_estudiante($id_usuario) {
        try {
            error_log("=== BUSCANDO ID_ESTUDIANTE ===");
            error_log("ID Usuario recibido: " . $id_usuario);
            
            // Consulta usando la tabla intermedia usuario_estudiante
            $sql = "SELECT id_estudiante_fk 
                    FROM usuario_estudiante 
                    WHERE id_usuario_fk = ?";
                    
            $stmt = mainModel::conectar()->prepare($sql);
            $stmt->execute([$id_usuario]);
            
            error_log("Filas encontradas en usuario_estudiante: " . $stmt->rowCount());
            
            if($stmt->rowCount() == 1) {
                $result = $stmt->fetch();
                error_log("¡ID ESTUDIANTE ENCONTRADO!: " . $result['id_estudiante_fk']);
                return $result['id_estudiante_fk'];
            } else {
                error_log("NO se encontró relación en usuario_estudiante para el usuario: " . $id_usuario);
                
                // Debug: verificar qué hay en la tabla usuario_estudiante
                $sql_debug = "SELECT * FROM usuario_estudiante";
                $stmt_debug = mainModel::conectar()->prepare($sql_debug);
                $stmt_debug->execute();
                $todas_relaciones = $stmt_debug->fetchAll(PDO::FETCH_ASSOC);
                
                error_log("Todas las relaciones en usuario_estudiante: " . print_r($todas_relaciones, true));
                return null;
            }
            
        } catch (Exception $e) {
            error_log("Error crítico obteniendo id_estudiante: " . $e->getMessage());
            return null;
        }
    }

    /*--------- Controlador forzar cierre de sesion ---------*/
    public function forzar_cierre_sesion_controlador(){
        session_unset();
        session_destroy();
        if(headers_sent()){
            return "<script> window.location.href='".SERVERURL."index.php?views=login'; </script>";
        }else{
            return header("Location: ".SERVERURL."index.php?views=login");
        }
    } /* Fin controlador */

    /*--------- Controlador cierre de sesion ---------*/
    public function cerrar_sesion_controlador(){
        session_start(['name'=>'SPM']);
        $token=mainModel::decryption($_POST['token']);
        $usuario=mainModel::decryption($_POST['usuario']);

        if($token==$_SESSION['token_spm'] && $usuario==$_SESSION['usuario_spm']){
            session_unset();
            session_destroy();
            $alerta=[
                "Alerta"=>"redireccionar",
                "URL"=>SERVERURL."index.php?views=login"
            ];
        }else{
            $alerta=[
                "Alerta"=>"simple",
                "Titulo"=>"Ocurrió un error inesperado",
                "Texto"=>"No se pudo cerrar la sesion en el sistema",
                "Tipo"=>"error"
            ];
        }
        echo json_encode($alerta);
    } /* Fin controlador */
}