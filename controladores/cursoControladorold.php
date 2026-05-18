<?php

    if($peticionAjax){
        require_once "../modelos/cursoModelo.php";
    }else{
        require_once "./modelos/cursoModelo.php";
    }

    class cursoControlador extends cursoModelo{

        /*--------- Controlador agregar curso ---------*/
        public function agregar_curso_controlador(){

            $nombre=mainModel::limpiar_cadena($_POST['curso_nombre_reg']);
            $tipo=mainModel::limpiar_cadena($_POST['curso_tipo_reg']);
            $cupos=mainModel::limpiar_cadena($_POST['curso_cupos_reg']);
            $estado=mainModel::limpiar_cadena($_POST['curso_estado_reg']);

            /*== comprobar campos vacios ==*/
            if($nombre=="" || $tipo==""){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No has llenado todos los campos que son obligatorios",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Verificando integridad de los datos ==*/
            if(mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,100}",$nombre)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El NOMBRE DEL CURSO no coincide con el formato solicitado",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando tipo de curso ==*/
            if($tipo!="Intensivo" && $tipo!="Extendido"){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"Debe seleccionar un tipo de curso válido (Intensivo o Extendido)",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando nombre del curso ==*/
            $check_nombre=mainModel::ejecutar_consulta_simple("SELECT nombre_curso FROM curso WHERE nombre_curso='$nombre'");
            if($check_nombre->rowCount()>0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El nombre del curso ingresado ya se encuentra registrado en el sistema",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando cupos ==*/
            if($cupos!="1" && $cupos!="0"){
                $cupos="1";
            }

            /*== Estableciendo estado activo por defecto ==*/
            $estado = "1";

            $datos_curso_reg=[
                "Nombre"=>$nombre,
                "Tipo"=>$tipo,
                "Cupos"=>$cupos,
                "Estado"=>$estado
            ];

            $agregar_curso=cursoModelo::agregar_curso_modelo($datos_curso_reg);

            if($agregar_curso->rowCount()==1){
                $alerta=[
                    "Alerta"=>"limpiar",
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
        } /* Fin controlador */

        /*--------- Controlador paginar curso ---------*/
        public function paginador_curso_controlador($pagina,$registros,$url,$busqueda){

            $pagina=mainModel::limpiar_cadena($pagina);
            $registros=mainModel::limpiar_cadena($registros);
            $url=mainModel::limpiar_cadena($url);
            $url=SERVERURL.$url."/";
            $busqueda=mainModel::limpiar_cadena($busqueda);

            $tabla="";

            $pagina= (isset($pagina) && $pagina>0) ? (int) $pagina : 1 ;
            $inicio= ($pagina>0) ? (($pagina*$registros)-$registros) : 0 ;

            if(isset($busqueda) && $busqueda!=""){
                $consulta="SELECT SQL_CALC_FOUND_ROWS * FROM curso WHERE (nombre_curso LIKE '%$busqueda%' OR tipo_curso LIKE '%$busqueda%') AND estado_curso='1' ORDER BY nombre_curso ASC LIMIT $inicio,$registros";
            }else{
                $consulta="SELECT SQL_CALC_FOUND_ROWS * FROM curso WHERE estado_curso='1' ORDER BY nombre_curso ASC LIMIT $inicio,$registros";
            }

            $conexion = mainModel::conectar();
            $datos = $conexion->query($consulta);
            $datos = $datos->fetchAll();

            $total = $conexion->query("SELECT FOUND_ROWS()");
            $total = (int) $total->fetchColumn();

            $Npaginas=ceil($total/$registros);

            $tabla.='<div class="table-responsive">
                <table class="table table-dark table-sm">
                    <thead>
                        <tr class="text-center roboto-medium">
                            <th>#</th>
                            <th>NOMBRE</th>
                            <th>TIPO</th>
                            <th>CUPOS</th>
                            <th>ESTADO</th>
                            <th>ACTUALIZAR</th>
                            <th>ELIMINAR</th>
                        </tr>
                    </thead>
                    <tbody>';

            if($total>=1 && $pagina<=$Npaginas){
                $contador=$inicio+1;
                $reg_inicio=$inicio+1;


//   <a href="'.SERVERURL.'course-update/'.mainModel::encryption($rows['id_curso']).'/" class="btn btn-success"> no borrrar aun nose si me sirve

                foreach($datos as $rows){
                    $tabla.='
                    <tr class="text-center" >
                        <td>'.$contador.'</td>
                        <td>'.$rows['nombre_curso'].'</td>
                        <td>'.$rows['tipo_curso'].'</td>
                        <td>'.($rows['cupos']==1 ? '<span class="badge badge-success">Disponible</span>' : '<span class="badge badge-danger">No disponible</span>').'</td>
                        <td>'.($rows['estado_curso']==1 ? '<span class="badge badge-success">Activo</span>' : '<span class="badge badge-secondary">Inactivo</span>').'</td>
                        <td>
                            
                            <a href="'.SERVERURL.'index.php?views=course-update&course_id_up='.mainModel::encryption($rows['id_curso']).'" class="btn btn-success">
                                    <i class="fas fa-sync-alt"></i> 
                            </a>
                        </td>
                        <td>
                            <form class="FormularioAjax" action="'.SERVERURL.'ajax/cursoAjax.php" method="POST" data-form="delete" autocomplete="off">
                                <input type="hidden" name="curso_id_del" value="'.mainModel::encryption($rows['id_curso']).'">
                                <button type="submit" class="btn btn-warning">
                                        <i class="far fa-trash-alt"></i>
                                </button>
                            </form>
                        </td>
                    </tr>';
                    $contador++;
                }
                $reg_final=$contador-1;
            }else{
                if($total>=1){
                    $tabla.='<tr class="text-center" ><td colspan="7">
                    <a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">Haga clic aca para recargar el listado</a>
                    </td></tr>';
                }else{
                    $tabla.='<tr class="text-center" ><td colspan="7">No hay registros en el sistema</td></tr>';
                }
            }

            $tabla.='</tbody></table></div>';

            if($total>=1 && $pagina<=$Npaginas){
                $tabla.='<p class="text-right">Mostrando curso '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
                $tabla.=mainModel::paginador_tablas($pagina,$Npaginas,$url,7);
            }

            return $tabla;
        } /* Fin controlador */

        /*--------- Controlador paginar cursos archivados ---------*/
        public function paginador_curso_archivado_controlador($pagina,$registros,$url,$busqueda){

            $pagina=mainModel::limpiar_cadena($pagina);
            $registros=mainModel::limpiar_cadena($registros);
            $url=mainModel::limpiar_cadena($url);
            $url=SERVERURL.$url."/";
            $busqueda=mainModel::limpiar_cadena($busqueda);

            $tabla="";

            $pagina= (isset($pagina) && $pagina>0) ? (int) $pagina : 1 ;
            $inicio= ($pagina>0) ? (($pagina*$registros)-$registros) : 0 ;

            if(isset($busqueda) && $busqueda!=""){
                $consulta="SELECT SQL_CALC_FOUND_ROWS * FROM curso WHERE (nombre_curso LIKE '%$busqueda%' OR tipo_curso LIKE '%$busqueda%') AND estado_curso='0' ORDER BY nombre_curso ASC LIMIT $inicio,$registros";
            }else{
                $consulta="SELECT SQL_CALC_FOUND_ROWS * FROM curso WHERE estado_curso='0' ORDER BY nombre_curso ASC LIMIT $inicio,$registros";
            }

            $conexion = mainModel::conectar();
            $datos = $conexion->query($consulta);
            $datos = $datos->fetchAll();

            $total = $conexion->query("SELECT FOUND_ROWS()");
            $total = (int) $total->fetchColumn();

            $Npaginas=ceil($total/$registros);

            $tabla.='<div class="table-responsive">
                <table class="table table-dark table-sm">
                    <thead>
                        <tr class="text-center roboto-medium">
                            <th>#</th>
                            <th>NOMBRE</th>
                            <th>TIPO</th>
                            <th>CUPOS</th>
                            <th>ESTADO</th>
                            <th>RESTAURAR</th>
                        </tr>
                    </thead>
                    <tbody>';

            if($total>=1 && $pagina<=$Npaginas){
                $contador=$inicio+1;
                $reg_inicio=$inicio+1;

                foreach($datos as $rows){
                    $tabla.='
                    <tr class="text-center" >
                        <td>'.$contador.'</td>
                        <td>'.$rows['nombre_curso'].'</td>
                        <td>'.$rows['tipo_curso'].'</td>
                        <td>'.($rows['cupos']==1 ? '<span class="badge badge-success">Disponible</span>' : '<span class="badge badge-danger">No disponible</span>').'</td>
                        <td><span class="badge badge-secondary">Archivado</span></td>
                        <td>
                            <form class="FormularioAjax" action="'.SERVERURL.'ajax/cursoAjax.php" method="POST" data-form="restore" autocomplete="off">
                                <input type="hidden" name="curso_id_restore" value="'.mainModel::encryption($rows['id_curso']).'">
                                <button type="submit" class="btn btn-info">
                                        <i class="fas fa-undo"></i> Restaurar
                                </button>
                            </form>
                        </td>
                    </tr>';
                    $contador++;
                }
                $reg_final=$contador-1;
            }else{
                if($total>=1){
                    $tabla.='<tr class="text-center" ><td colspan="6">
                    <a href="'.$url.'" class="btn btn-raised btn-primary btn-sm">Haga clic aca para recargar el listado</a>
                    </td></tr>';
                }else{
                    $tabla.='<tr class="text-center" ><td colspan="6">No hay cursos archivados en el sistema</td></tr>';
                }
            }

            $tabla.='</tbody></table></div>';

            if($total>=1 && $pagina<=$Npaginas){
                $tabla.='<p class="text-right">Mostrando curso '.$reg_inicio.' al '.$reg_final.' de un total de '.$total.'</p>';
                $tabla.=mainModel::paginador_tablas($pagina,$Npaginas,$url,7);
            }

            return $tabla;
        } /* Fin controlador */

        /*--------- Controlador restaurar curso ---------*/
        public function restaurar_curso_controlador(){

            $id=mainModel::decryption($_POST['curso_id_restore']);
            $id=mainModel::limpiar_cadena($id);

            $check_curso=mainModel::ejecutar_consulta_simple("SELECT id_curso FROM curso WHERE id_curso='$id'");
            if($check_curso->rowCount()<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El curso que intenta restaurar no existe en el sistema",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            session_start(['name'=>'SPM']);
            if($_SESSION['cargo_spm']!=1){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No tienes los permisos necesarios para realizar esta operacion",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            $restaurar_curso=mainModel::ejecutar_consulta_simple("UPDATE curso SET estado_curso='1' WHERE id_curso='$id'");

            if($restaurar_curso->rowCount()==1){
                $alerta=[
                    "Alerta"=>"recargar",
                    "Titulo"=>"Curso restaurado",
                    "Texto"=>"El curso ha sido restaurado exitosamente",
                    "Tipo"=>"success"
                ];
            }else{
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos podido restaurar el curso, por favor intente nuevamente",
                    "Tipo"=>"error"
                ];
            }

            echo json_encode($alerta);
        } /* Fin controlador */

        /*--------- Controlador eliminar curso ---------*/
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
            if($_SESSION['privilegio_spm']!=1){
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

        /*--------- Controlador datos curso ---------*/
        public function datos_curso_controlador($tipo,$id){
            $tipo=mainModel::limpiar_cadena($tipo);
            $id=mainModel::decryption($id);
            $id=mainModel::limpiar_cadena($id);

            return cursoModelo::datos_curso_modelo($tipo,$id);
        } /* Fin controlador */

        /*--------- Controlador actualizar curso ---------*/
        public function actualizar_curso_controlador(){

            // Recibiendo el id
            $id=mainModel::limpiar_cadena($_POST['curso_id_up']);

            //$id=mainModel::decryption($_POST['curso_id_up']);
            //$id=mainModel::limpiar_cadena($id);


            // comprobar el curso en la BD
            $check_curso=mainModel::ejecutar_consulta_simple("SELECT * FROM curso WHERE id_curso='$id'");
            if($check_curso->rowCount()<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No hemos encontrado el curso en el sistema",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }else{
                $campos=$check_curso->fetch();
            }

            $nombre=mainModel::limpiar_cadena($_POST['curso_nombre_up']);
            $tipo=mainModel::limpiar_cadena($_POST['curso_tipo_up']);
            $cupos=mainModel::limpiar_cadena($_POST['curso_cupos_up']);
            $estado=mainModel::limpiar_cadena($_POST['curso_estado_up']);

            $admin_usuario=mainModel::limpiar_cadena($_POST['usuario_admin']);
            $admin_clave=mainModel::limpiar_cadena($_POST['clave_admin']);

            /*== comprobar campos vacios ==*/
            if($nombre=="" || $tipo=="" || $admin_usuario=="" || $admin_clave==""){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No has llenado todos los campos que son obligatorios",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Verificando integridad de los datos ==*/
            if(mainModel::verificar_datos("[a-zA-Z0-9áéíóúÁÉÍÓÚñÑ().,#\- ]{1,100}",$nombre)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"El NOMBRE DEL CURSO no coincide con el formato solicitado",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            if(mainModel::verificar_datos("[a-zA-Z0-9]{1,35}",$admin_usuario)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"TU NOMBRE DE USUARIO no coincide con el formato solicitado",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            if(mainModel::verificar_datos("[a-zA-Z0-9$@.-]{7,100}",$admin_clave)){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"TU CLAVE no coincide con el formato solicitado",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            $admin_clave=mainModel::encryption($admin_clave);

            /*== Comprobando tipo de curso ==*/
            if($tipo!="Intensivo" && $tipo!="Extendido"){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"Debe seleccionar un tipo de curso válido (Intensivo o Extendido)",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Comprobando nombre del curso ==*/
            if($nombre!=$campos['nombre_curso']){
                $check_nombre=mainModel::ejecutar_consulta_simple("SELECT nombre_curso FROM curso WHERE nombre_curso='$nombre'");
                if($check_nombre->rowCount()>0){
                    $alerta=[
                        "Alerta"=>"simple",
                        "Titulo"=>"Ocurrió un error inesperado",
                        "Texto"=>"El nombre del curso ingresado ya se encuentra registrado en el sistema",
                        "Tipo"=>"error"
                    ];
                    echo json_encode($alerta);
                    exit();
                }
            }

            /*== Comprobando cupos ==*/
            if($cupos!="1" && $cupos!="0"){
                $cupos=$campos['cupos'];
            }

            /*== Comprobando estado ==*/
            if($estado!="1" && $estado!="0"){
                $estado=$campos['estado_curso'];
            }

            /*== Comprobando credenciales para actualizar datos ==*/
            session_start(['name'=>'SPM']);
            if($_SESSION['cargo_spm']!=1){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"No tienes los permisos necesarios para realizar esta operacion",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }
            $check_cuenta=mainModel::ejecutar_consulta_simple("SELECT ID_usuario FROM usuario WHERE Nombre='$admin_usuario' AND Password='$admin_clave'");
            //$check_cuenta=mainModel::ejecutar_consulta_simple("SELECT usuario_id FROM usuario WHERE usuario_usuario='$admin_usuario' AND usuario_clave='$admin_clave'");

            if($check_cuenta->rowCount()<=0){
                $alerta=[
                    "Alerta"=>"simple",
                    "Titulo"=>"Ocurrió un error inesperado",
                    "Texto"=>"Nombre y clave de administrador no validos",
                    "Tipo"=>"error"
                ];
                echo json_encode($alerta);
                exit();
            }

            /*== Preparando datos para enviarlos al modelo ==*/
            $datos_curso_up=[
                "Nombre"=>$nombre,
                "Tipo"=>$tipo,
                "Cupos"=>$cupos,
                "Estado"=>$estado,
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
    }
?>
