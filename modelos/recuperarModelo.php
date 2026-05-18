<?php

//reiniciar_intentos_ip

// 1. Declarar los namespaces primero
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

// 2. Incluir las clases necesarias
require_once __DIR__ . '/../librerias/PHPMailer/src/Exception.php';
require_once __DIR__ . '/../librerias/PHPMailer/src/PHPMailer.php';
require_once __DIR__ . '/../librerias/PHPMailer/src/SMTP.php';

require_once "mainModel.php";

class recuperarModelo extends mainModel {

    /* Buscar usuario por cédula, teléfono y correo 
    protected static function buscar_usuario_por_datos($datos) {
        $sql = self::conectar()->prepare("
            SELECT id_estudiante FROM estudiante 
            WHERE ci_estudiante = :Cedula AND telefono_estudiante = :Telefono AND correo_estudiante = :Correo
            LIMIT 1
        ");
        $sql->bindParam(":Cedula", $datos['Cedula']);
        $sql->bindParam(":Telefono", $datos['Telefono']);
        $sql->bindParam(":Correo", $datos['Correo']);
        $sql->execute();
        return $sql;
    }
*/




public static function buscar_usuario_por_datos($datos) {
    $sql = self::conectar()->prepare("
        SELECT u.ID_usuario, u.cargo
        FROM usuario u
        INNER JOIN usuario_estudiante ue ON ue.id_usuario_fk = u.ID_usuario
        INNER JOIN estudiante e ON e.id_estudiante = ue.id_estudiante_fk
        WHERE e.ci_estudiante = :Cedula AND e.telefono_estudiante = :Telefono AND e.correo_estudiante = :Correo AND e.fecha_nacimiento = :FechaNacimiento AND estado_estudiante = 1
        LIMIT 1
    ");
    $sql->bindParam(":Cedula", $datos['Cedula']);
    $sql->bindParam(":Telefono", $datos['Telefono']);
    $sql->bindParam(":Correo", $datos['Correo']);
    $sql->bindParam(":FechaNacimiento", $datos['FechaNacimiento']);
    $sql->execute();
    return $sql;
}




public static function buscar_estudiante_sin_usuario($datos) {
    $sql = self::conectar()->prepare("
        SELECT e.id_estudiante
        FROM estudiante e
        WHERE e.ci_estudiante = :Cedula
          AND e.telefono_estudiante = :Telefono
          AND e.correo_estudiante = :Correo
          AND e.fecha_nacimiento = :FechaNacimiento
          AND NOT EXISTS (
              SELECT 1
              FROM usuario_estudiante ue
              WHERE ue.id_estudiante_fk = e.id_estudiante AND estado_estudiante = 1
          )
        LIMIT 1
    ");
    $sql->bindParam(":Cedula", $datos['Cedula']);
    $sql->bindParam(":Telefono", $datos['Telefono']);
    $sql->bindParam(":Correo", $datos['Correo']);
    $sql->bindParam(":FechaNacimiento", $datos['FechaNacimiento']);
    $sql->execute();
    return $sql;
}


    
    
    
    public static function crear_usuario_estudiante($id_estudiante, $clave_encriptada) {
        try {
            $pdo = self::conectar();

            // Buscar CI del estudiante
            $stmt = $pdo->prepare("
                SELECT ci_estudiante
                FROM estudiante
                WHERE id_estudiante = :id
                LIMIT 1
            ");
            $stmt->bindParam(":id", $id_estudiante);
            $stmt->execute();

            if ($stmt->rowCount() !== 1) {
                return false; // No se encontró el estudiante
            }

            $est = $stmt->fetch();
            $nombre_usuario = trim($est['ci_estudiante']); // ← usar CI como nombre de usuario
            $fecha_creacion = date("Y-m-d H:i:s"); // ← fecha y hora actual

            // Insertar nuevo usuario
            $sql = $pdo->prepare("
                INSERT INTO usuario (Nombre, Password, cargo, estado_usuario, fecha_creacion)
                VALUES (:nombre_usuario, :clave, 5, 1, :fecha_creacion)
            ");
            $sql->bindParam(":nombre_usuario", $nombre_usuario);
            $sql->bindParam(":clave", $clave_encriptada);
            $sql->bindParam(":fecha_creacion", $fecha_creacion);
            $sql->execute();

            // Obtener el ID del nuevo usuario
            $id_usuario = $pdo->lastInsertId();

            // Registrar relación con estudiante
            $registrado = self::registrar_usuario_estudiante($id_usuario, $id_estudiante);

            if ($registrado) {
                return $id_usuario;
            } else {
                // Si falla la relación, eliminar el usuario creado
                $pdo->prepare("DELETE FROM usuario WHERE ID_usuario = :id")->execute([':id' => $id_usuario]);
                return false;
            }

        } catch (PDOException $e) {
            error_log("⛔ Error al crear usuario estudiante: " . $e->getMessage());
            return false;
        }
    }
    
    
/* no tiene datetime
public static function crear_usuario_estudiante($id_estudiante, $clave_encriptada) {
    try {
        $pdo = self::conectar();

        // Buscar nombre y apellido del estudiante
        $stmt = $pdo->prepare("
            SELECT nombre, apellido
            FROM estudiante
            WHERE id_estudiante = :id
            LIMIT 1
        ");
        $stmt->bindParam(":id", $id_estudiante);
        $stmt->execute();

        if ($stmt->rowCount() !== 1) {
            return false; // No se encontró el estudiante
        }

        $est = $stmt->fetch();
        $nombre = strtolower(trim($est['nombre']));
        $apellido = strtolower(trim($est['apellido']));

        // Generar nombre de usuario
        $nombre_usuario = preg_replace('/\s+/', '', $nombre . '_' . $apellido);

        // Insertar nuevo usuario
        $sql = $pdo->prepare("
            INSERT INTO usuario (Nombre, Password, cargo, estado_usuario)
            VALUES (:nombre_usuario, :clave, 5, 1)
        ");
        $sql->bindParam(":nombre_usuario", $nombre_usuario);
        $sql->bindParam(":clave", $clave_encriptada);
        $sql->execute();

        // Obtener el ID del nuevo usuario
        $id_usuario = $pdo->lastInsertId();

        // Registrar relación con estudiante
        $registrado = self::registrar_usuario_estudiante($id_usuario, $id_estudiante);

        if ($registrado) {
            return $id_usuario;
        } else {
            // Si falla la relación, eliminar el usuario creado
            $pdo->prepare("DELETE FROM usuario WHERE ID_usuario = :id")->execute([':id' => $id_usuario]);
            return false;
        }

    } catch (PDOException $e) {
        error_log("⛔ Error al crear usuario estudiante: " . $e->getMessage());
        return false;
    }
}
*/





public static function registrar_usuario_estudiante($id_usuario, $id_estudiante) {
    try {
        $sql = self::conectar()->prepare("
            INSERT INTO usuario_estudiante (id_usuario_fk, id_estudiante_fk)
            VALUES (:usuario, :estudiante)
        ");
        $sql->bindParam(":usuario", $id_usuario, PDO::PARAM_INT);
        $sql->bindParam(":estudiante", $id_estudiante, PDO::PARAM_INT);
        return $sql->execute();
    } catch (PDOException $e) {
        error_log("⛔ Error al registrar usuario_estudiante: " . $e->getMessage());
        return false;
    }
}





/*

public static function crear_usuario_estudiante($id_estudiante, $clave_encriptada) {
    try {
        $pdo = self::conectar();

        // Insertar nuevo usuario
        $sql = $pdo->prepare("
            INSERT INTO usuario (usuario_clave, cargo, estado_usuario)
            VALUES (:clave, 5, 1)
        ");
        $sql->bindParam(":clave", $clave_encriptada);
        $sql->execute();

        // Obtener el ID del nuevo usuario
        $id_usuario = $pdo->lastInsertId();

        // Registrar relación con estudiante
        $registrado = self::registrar_usuario_estudiante($id_usuario, $id_estudiante);

        if ($registrado) {
            return $id_usuario;
        } else {
            // Si falla la relación, eliminar el usuario creado
            $pdo->prepare("DELETE FROM usuario WHERE ID_usuario = :id")->execute([':id' => $id_usuario]);
            return false;
        }

    } catch (PDOException $e) {
        error_log("⛔ Error al crear usuario estudiante: " . $e->getMessage());
        return false;
    }
}*/

    /* Guardar código OTP temporal 
    protected static function guardar_codigo_otp($usuario_id, $codigo, $expira) {
        $sql = self::conectar()->prepare("
            INSERT INTO codigo_otp (id_usuario, codigo, expira, usado)
            VALUES (:ID_usuario, :Codigo, FROM_UNIXTIME(:Expira), 0)
        ");
        $sql->bindParam(":ID_usuario", $usuario_id);
        $sql->bindParam(":Codigo", $codigo);
        $sql->bindParam(":Expira", $expira);
        $sql->execute();
        return $sql;
    }

    /* Validar código OTP 
    protected static function validar_codigo_otp($usuario_id, $codigo) {
        $sql = self::conectar()->prepare("
            SELECT id_codigo FROM codigo_otp 
            WHERE id_usuario = :ID_usuario AND codigo = :Codigo 
            AND expira > NOW() AND usado = 0
            ORDER BY expira DESC LIMIT 1
        ");
        $sql->bindParam(":ID_usuario", $usuario_id);
        $sql->bindParam(":Codigo", $codigo);
        $sql->execute();

        if ($sql->rowCount() == 1) {
            // Marcar como usado
            $row = $sql->fetch();
            $update = self::conectar()->prepare("
                UPDATE codigo_otp SET usado = 1 WHERE id_codigo = :ID_codigo
            ");
            $update->bindParam(":ID_codigo", $row['ID_codigo']);
            $update->execute();
            return true;
        }
        return false;
    }
*/


    /* Actualizar clave del usuario */
    protected static function actualizar_clave($usuario_id, $clave_encriptada) {
        $sql = self::conectar()->prepare("
            UPDATE usuario SET Password = :Clave WHERE ID_usuario = :ID_usuario
        ");
        $sql->bindParam(":Clave", $clave_encriptada);
        $sql->bindParam(":ID_usuario", $usuario_id);
        $sql->execute();
        return $sql;
    }

    /* Obtener registro de IP visitante */
    protected static function obtener_ip_visitante($ip) {
        $sql = self::conectar()->prepare("
            SELECT * FROM ip_visitante WHERE direccion_ip = :IP LIMIT 1
        ");
        $sql->bindParam(":IP", $ip);
        $sql->execute();
        return $sql->fetch();
    }

    /* Registrar intento fallido por IP 
    protected static function registrar_intento_ip($ip, $tipo) {
        $registro = self::obtener_ip_visitante($ip);

        if (!$registro) {
            $sql = self::conectar()->prepare("
                INSERT INTO ip_visitante (direccion_ip, intentos_login_fallidos, intentos_recuperacion_fallidos)
                VALUES (:IP, 0, 1)
            ");
            $sql->bindParam(":IP", $ip);
            $sql->execute();
        } else {
            $campo = $tipo === 'login' ? 'intentos_login_fallidos' : 'intentos_recuperacion_fallidos';
            $intentos = $registro[$campo] + 1;

            $bloqueo = null;
            if ($intentos == 3) $bloqueo = time() + 300;
            elseif ($intentos == 6) $bloqueo = time() + 600;
            elseif ($intentos == 9) $bloqueo = time() + 86400;

            $sql = self::conectar()->prepare("
                UPDATE ip_visitante SET $campo = :intentos, bloqueado_hasta = :bloqueo WHERE direccion_ip = :IP
            ");
            $sql->bindParam(":intentos", $intentos);
            $sql->bindParam(":bloqueo", $bloqueo ? date("Y-m-d H:i:s", $bloqueo) : null);
            $sql->bindParam(":IP", $ip);
            $sql->execute();
        }
    }*/

    /* Verificar si IP está bloqueada 
    protected static function ip_bloqueada($ip, $tipo) {
        $registro = self::obtener_ip_visitante($ip);
        if ($registro && strtotime($registro['bloqueado_hasta']) > time()) {
            return true;
        }
        return false;
    }*/




/*
public static function enviar_codigo_por_correo($correo, $codigo) {


    $mail = new PHPMailer(true); // 1 Instancia PHPMailer con manejo de excepciones
    try {
        $mail->isSMTP();
        $mail->Host = 'smtp.gmail.com'; // ← CORRECTO para Gmail        $mail->SMTPAuth = true; // Activar autenticación
        $mail->Username = 'santiagosan2022@gmail.com'; // Usuario SMTP
        $mail->Password = 'mrle gvxr sdvh nxck'; // Contraseña SMTP
        $mail->SMTPSecure = 'tls';
        $mail->Port = 587; 

        $mail->setFrom('santiagosan2022@gmail.com', 'Recuperación de cuenta');
        $mail->addAddress($correo); // Destinatario

        $mail->isHTML(true); // Permitir HTML
        $mail->Subject = 'Código de recuperación'; // Asunto
        $mail->Body = "Tu código de verificación para recuperar tu cuenta de la página web de CEPROCA es: <strong>$codigo</strong><br>Este código expirará en 5 minutos."; // Cuerpo HTML

        $mail->send();
        return true; // Éxito
    } catch (Exception $e) {
        return false; // Fallo
    }
}
8*/






    protected static function guardar_solicitud_inscripcion_modelo($datos) {
        $sql = "
            INSERT INTO solicitud_inscripcion (
                nombre_estudiante,
                apellido_estudiante,
                correo_estudiante,
                id_curso_seleccionado,
                fecha_nacimiento,
                cedula_estudiante,
                telefono_estudiante,
                monto,
                fecha_pago,
                metodo_pago,
                captura_pago,
                clave,
                fecha_solicitud,
            	estado_solicitud
            ) VALUES (
                :nombre,
                :apellido,
                :correo,
                :id_curso,
                :fecha_nacimiento,
                :cedula,
                :telefono,
                :monto,
                :fecha_pago,
                :metodo_pago,
                :foto,
                :clave,
                :fecha_solicitud,
	            :estado_solicitud
            )
        ";

        $stmt = mainModel::conectar()->prepare($sql);
            $fechaActual = date('Y-m-d H:i:s');
		    $estado = "Pendiente";

        $stmt->bindParam(':nombre',           $datos['nombre']);
        $stmt->bindParam(':apellido',         $datos['apellido']);
        $stmt->bindParam(':correo',           $datos['correo']);
        $stmt->bindParam(':id_curso',         $datos['id_curso'], PDO::PARAM_INT);
        $stmt->bindParam(':fecha_nacimiento', $datos['fecha_nac']);
        $stmt->bindParam(':cedula',           $datos['cedula']);
        $stmt->bindParam(':telefono',         $datos['telefono']);
        $stmt->bindParam(':monto',            $datos['monto']);
        $stmt->bindParam(':fecha_pago',       $datos['fecha_pago']);
        $stmt->bindParam(':metodo_pago',      $datos['metodo']);
        $stmt->bindParam(':foto',             $datos['foto']);
        $stmt->bindParam(':clave',            $datos['clave']);
        $stmt->bindParam(':fecha_solicitud',  $fechaActual);
	    $stmt->bindParam(':estado_solicitud', $estado);

        $stmt->execute();
        return $stmt;
    }











    /* Limpiar intentos tras éxito */
    protected static function limpiar_intentos_ip($ip, $tipo) {
        $campo = $tipo === 'login' ? 'intentos_login_fallidos' : 'intentos_recuperacion_fallidos';
        $sql = self::conectar()->prepare("
            UPDATE ip_visitante SET $campo = 0, bloqueado_hasta = NULL WHERE direccion_ip = :IP
        ");
        $sql->bindParam(":IP", $ip);
        $sql->execute();
    }
}



?>