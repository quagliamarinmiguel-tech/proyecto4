<?php
// Activar mostrar errores para depuración (quitar en producción)
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

require_once "../config/APP.php";
require_once "../modelos/mainModel.php";

// HEADER para permitir CORS y JSON
header('Content-Type: text/html; charset=utf-8');

try {
    $conexion = mainModel::conectar();
} catch (Exception $e) {
    die("Error de conexión: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // DEBUG: Mostrar todos los datos recibidos
    error_log("=== DATOS RECIBIDOS ===");
    foreach ($_POST as $key => $value) {
        error_log("$key: $value");
    }
    
    try {
        // Obtener y limpiar datos
        $ci = isset($_POST['ci']) ? trim($_POST['ci']) : '';
        $nombre = isset($_POST['nombre']) ? trim($_POST['nombre']) : '';
        $apellido = isset($_POST['apellido']) ? trim($_POST['apellido']) : '';
        $fecha_nac = isset($_POST['fecha_nacimiento']) ? $_POST['fecha_nacimiento'] : '';
        $telefono = isset($_POST['telefono']) ? trim($_POST['telefono']) : '';
        $correo = isset($_POST['correo']) ? trim($_POST['correo']) : '';
        $usuario = isset($_POST['usuario']) ? trim($_POST['usuario']) : '';
        $clave1 = isset($_POST['clave1']) ? $_POST['clave1'] : '';
        $clave2 = isset($_POST['clave2']) ? $_POST['clave2'] : '';

        // Validaciones básicas
        if (empty($ci) || empty($nombre) || empty($apellido) || empty($fecha_nac) || 
            empty($telefono) || empty($correo) || empty($usuario) || empty($clave1)) {
            throw new Exception("Todos los campos son obligatorios.");
        }

        // **MODIFICACIÓN: Validar cédula con nuevo formato (V-12345678 o E-12345678)**
        if (!preg_match('/^[VE]\d{8}$/', $ci)) {
            throw new Exception("La cédula debe tener el formato V-12345678 o E-12345678 (letra V o E y 8 dígitos).");
        }

        // Validar nombre y apellido (solo letras)
        if (!preg_match('/^[A-Za-záéíóúñÑ\s]{2,100}$/', $nombre)) {
    throw new Exception("El nombre solo puede contener letras y espacios (2-100 caracteres).");
}

        if (!preg_match('/^[A-Za-záéíóúñÑ\s]{2,100}$/', $apellido)) {
            throw new Exception("El apellido solo puede contener letras (2-50 caracteres).");
        }

        // Validar teléfono
        error_log("Teléfono recibido: '$telefono' - Longitud: " . strlen($telefono));
        
        if (strlen($telefono) !== 11) {
            throw new Exception("El teléfono debe tener 11 dígitos. Se recibió: '" . $telefono . "' (" . strlen($telefono) . " dígitos)");
        }

        $codigos_permitidos = ['0412', '0414', '0416', '0422', '0424', '0426'];
        $codigo_telefono = substr($telefono, 0, 4);
        
        if (!in_array($codigo_telefono, $codigos_permitidos)) {
            throw new Exception("Código de teléfono no válido: " . $codigo_telefono);
        }

        // Validar correo
        if (!filter_var($correo, FILTER_VALIDATE_EMAIL)) {
            throw new Exception("El correo electrónico no es válido: " . $correo);
        }

        // Validar contraseña
        if (!preg_match('/^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[A-Za-z\d]{8,}$/', $clave1)) {
            throw new Exception("La contraseña debe tener al menos 8 caracteres, una mayúscula, una minúscula y un número. Solo se permiten letras y números.");
        }

        if ($clave1 !== $clave2) {
            throw new Exception("Las contraseñas no coinciden.");
        }

		// Validar fecha
		$fecha_nac_obj = new DateTime($fecha_nac);
		$hoy = new DateTime();
		$fecha_minima = (new DateTime())->modify('-6 years');

		// Verificar que no sea fecha futura
		if ($fecha_nac_obj > $hoy) {
    		throw new Exception("La fecha de nacimiento no puede ser futura.");
		}

		// Verificar que sea al menos 6 años antes
		if ($fecha_nac_obj > $fecha_minima) {
    		throw new Exception("El estudiante debe tener al menos 6 años de edad.");
		}

        // Verificar duplicados
        $verificarCI = $conexion->prepare("SELECT COUNT(*) FROM estudiante WHERE ci_estudiante = :ci");
        $verificarCI->bindParam(':ci', $ci);
        $verificarCI->execute();
        if ($verificarCI->fetchColumn() > 0) {
            throw new Exception("La cédula ya está registrada.");
        }

        $verificarUsuario = $conexion->prepare("SELECT COUNT(*) FROM usuario WHERE Nombre = :usuario");
        $verificarUsuario->bindParam(':usuario', $usuario);
        $verificarUsuario->execute();
        if ($verificarUsuario->fetchColumn() > 0) {
            throw new Exception("El nombre de usuario ya está en uso.");
        }

        // Iniciar transacción
        $conexion->beginTransaction();

        // Insertar en estudiante (estado_estudiante = 1)
        $insertEst = $conexion->prepare("
            INSERT INTO estudiante (ci_estudiante, nombre, apellido, fecha_nacimiento, telefono_estudiante, correo_estudiante, estado_estudiante)
            VALUES (:ci, :nombre, :apellido, :fecha, :telefono, :correo, 1)
        ");
        $insertEst->execute([
            ':ci' => $ci,  // Aquí se guardará el formato "V-12345678" o "E-12345678"
            ':nombre' => $nombre,
            ':apellido' => $apellido,
            ':fecha' => $fecha_nac,
            ':telefono' => $telefono,
            ':correo' => $correo
        ]);
        $idEstudiante = $conexion->lastInsertId();

        // Insertar en usuario (estado_usuario = 1) - USANDO TU MÉTODO DE ENCRIPCIÓN
        $claveHash = mainModel::encryption($clave1);
        $insertUser = $conexion->prepare("
            INSERT INTO usuario (Nombre, Password, cargo, estado_usuario, fecha_creacion)
            VALUES (:usuario, :clave, '5', 1, NOW())
        ");
        $insertUser->execute([
            ':usuario' => $usuario,
            ':clave' => $claveHash
        ]);
        $idUsuario = $conexion->lastInsertId();

        // Relacionar en usuario_estudiante
        $relacion = $conexion->prepare("
            INSERT INTO usuario_estudiante (id_usuario_fk, id_estudiante_fk)
            VALUES (:usuario, :estudiante)
        ");
        $relacion->execute([
            ':usuario' => $idUsuario,
            ':estudiante' => $idEstudiante
        ]);

        // Confirmar transacción
        $conexion->commit();

        echo "<div class='alert alert-success text-center mt-3'>
                <i class='bi bi-check-circle-fill'></i> ¡Registro exitoso!<br>
                <strong>Estudiante:</strong> {$nombre} {$apellido}<br>
                <strong>Cédula:</strong> {$ci}<br>
                <strong>Usuario:</strong> {$usuario}<br>
                <strong>Teléfono:</strong> {$telefono}<br>
                <strong>Correo:</strong> {$correo}
              </div>";

    } catch (Exception $e) {
        // Revertir transacción en caso de error
        if ($conexion->inTransaction()) {
            $conexion->rollBack();
        }
        
        $mensajeError = htmlspecialchars($e->getMessage());
        error_log("Error en registro: " . $mensajeError);
        
        echo "<div class='alert alert-danger text-center mt-3'>
                <i class='bi bi-exclamation-triangle-fill'></i> Error: {$mensajeError}
              </div>";
    }
} else {
    echo "<div class='alert alert-danger text-center mt-3'>Acceso no permitido.</div>";
}
?>