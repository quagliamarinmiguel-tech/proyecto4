<?php

    require_once "mainModel.php";

    class usuarioModelo3 extends mainModel{

        /*--------- Modelo agregar usuario ---------
        protected static function agregar_usuario_modelo($datos){
            $sql=mainModel::conectar()->prepare("INSERT INTO usuario(Nombre,Password,cargo,estado_usuario) VALUES(:Nombre,:Password,:Cargo,:Estado)");

            $sql->bindParam(":Nombre",$datos['Nombre']);
            $sql->bindParam(":Password",$datos['Password']);
            $sql->bindParam(":Cargo",$datos['Cargo']);
            $sql->bindParam(":Estado",$datos['Estado']);

            $sql->execute();
            return $sql;
        }/*fin crear usuario modelo*/
        
        
        protected static function agregar_usuario_modelo($datos){
            try {
                $pdo = mainModel::conectar();
                $sql = $pdo->prepare("
                    INSERT INTO usuario(Nombre, Password, cargo, estado_usuario, fecha_creacion)
                    VALUES(:nombre, :clave, :cargo, 1, NOW())
                ");
                $sql->execute([
                    ":nombre" => $datos['nombre_usuario'],
                    ":clave"  => $datos['clave_usuario'],
                    ":cargo"  => $datos['cargo_usuario']
                ]);
                return $pdo->lastInsertId();
            } catch (Exception $e) {
                error_log("Error al insertar usuario: " . $e->getMessage());
                return 0;
            }
        }
        
        
        
        
        
        
        protected static function agregar_personal_modelo($datos){
            try {
                $pdo = mainModel::conectar();
                $sql = $pdo->prepare("
                    INSERT INTO personal(ci_personal, nombre_personal, apellido_personal, correo_personal, id_usuario_fk)
                    VALUES(:ci, :nombre, :apellido, :correo, :usuario)
                ");
                $sql->execute([
                    ":ci"      => $datos['ci_personal'],
                    ":nombre"  => $datos['nombre_personal'],
                    ":apellido"=> $datos['apellido_personal'],
                    ":correo"  => $datos['correo_personal'],
                    ":usuario" => $datos['id_usuario_fk']
                ]);
                return $sql->rowCount();
            } catch (Exception $e) {
                error_log("Error al insertar personal: " . $e->getMessage());
                return 0;
            }
        }
        
        
        
        protected static function agregar_cuenta_estudiante_modelo($datos){
            try {
                $pdo = mainModel::conectar();
                $sql = $pdo->prepare("
                    INSERT INTO usuario(Nombre, Password, cargo, estado_usuario, fecha_creacion)
                    VALUES(:nombre, :clave, :cargo, 1, NOW())
                ");
                $sql->execute([
                    ":nombre" => $datos['nombre_usuario'],
                    ":clave"  => $datos['clave_usuario'],
                    ":cargo"  => $datos['cargo_usuario']
                ]);
                return $pdo->lastInsertId();
            } catch (Exception $e) {
                error_log("Error al insertar usuario: " . $e->getMessage());
                return 0;
            }
        }

        
        
        protected static function vincular_usuario_estudiante_modelo($datos){
            try {
                $pdo = mainModel::conectar();
                $sql = $pdo->prepare("
                    INSERT INTO usuario_estudiante(id_estudiante_fk, id_usuario_fk)
                    VALUES(:estudiante, :usuario)
                ");
                $sql->execute([
                    ":estudiante" => $datos['id_estudiante_fk'],
                    ":usuario"    => $datos['id_usuario_fk']
                ]);
                return 1;
            } catch (Exception $e) {
                error_log("Error al vincular usuario con estudiante: " . $e->getMessage());
                return 0;
            }
        }
        
        

        /*--------- Modelo eliminar usuario ---------*/
        protected static function eliminar_usuario_modelo($id){
            $sql=mainModel::conectar()->prepare("DELETE FROM usuario WHERE ID_usuario=:ID");
            $sql->bindParam(":ID",$id);
            $sql->execute();
            return $sql;
        }

        /*--------- Modelo datos usuario ---------*/
        protected static function datos_usuario_modelo($tipo,$id){
            if($tipo=="Unico"){
                $sql=mainModel::conectar()->prepare("SELECT * FROM usuario WHERE ID_usuario=:ID");
                $sql->bindParam(":ID",$id);
            }elseif($tipo=="Conteo"){
                $sql=mainModel::conectar()->prepare("SELECT ID_usuario FROM usuario WHERE ID_usuario!='1'");
            }

            $sql->execute();
            return $sql;
        }

        
        
        
        protected function actualizar_cuenta_est_modelo($datos) {
            $sql = mainModel::conectar()->prepare("
                UPDATE usuario 
                SET 
                    Password = :Password,
                    estado_usuario = :Estado 
                WHERE ID_usuario = :ID
            ");

            $sql->bindParam(":Password", $datos['Password']);
            $sql->bindParam(":Estado", $datos['Estado']);
            $sql->bindParam(":ID", $datos['ID']);

            return $sql;
        }
        
        
        protected function actualizar_usuario_per_modelo($datos) {
            
            
    $sql = mainModel::conectar()->prepare("
        UPDATE usuario 
        SET Nombre = :Nombre, Password = :Password, cargo = :Cargo, estado_usuario = :Estado 
        WHERE ID_usuario = :ID
    ");
    $sql->bindParam(":Nombre", $datos['Nombre']);
    $sql->bindParam(":Password", $datos['Password']);
    $sql->bindParam(":Cargo", $datos['Cargo']);
    $sql->bindParam(":Estado", $datos['Estado']);
    $sql->bindParam(":ID", $datos['ID']);
            
$sql->execute();
return $sql;
        }
        
        
        
        protected function actualizar_cuenta_personal_modelo($datos) {
    $sql = mainModel::conectar()->prepare("
        UPDATE personal 
        SET ci_personal = :ci, nombre_personal = :nombre, apellido_personal = :apellido, correo_personal = :correo 
        WHERE id_usuario_fk = :id_usuario
    ");
    $sql->bindParam(":ci", $datos['ci_personal']);
    $sql->bindParam(":nombre", $datos['nombre_personal']);
    $sql->bindParam(":apellido", $datos['apellido_personal']);
    $sql->bindParam(":correo", $datos['correo_personal']);
    $sql->bindParam(":id_usuario", $datos['id_usuario_fk']);
            $sql->execute();

    return $sql;
}
        
        
        
        
        /*--------- Modelo actualizar usuario ---------*/
        protected static function actualizar_usuario_modelo($datos){
            $sql=mainModel::conectar()->prepare("UPDATE usuario SET Nombre=:Nombre,Password=:Password,cargo=:Cargo,estado_usuario=:Estado WHERE ID_usuario=:ID");

            $sql->bindParam(":Nombre",$datos['Nombre']);
            $sql->bindParam(":Password",$datos['Password']);
            $sql->bindParam(":Cargo",$datos['Cargo']);
            $sql->bindParam(":Estado",$datos['Estado']);
            $sql->bindParam(":ID",$datos['ID']);

            $sql->execute();
            return $sql;
        }
    }
?>
