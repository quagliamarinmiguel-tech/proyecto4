<?php
// Respaldo automático de base de datos para InfinityFree
// Sin autenticación - protegido por sesión del sistema

// Configuración de la base de datos
define('DB_HOST', 'sql201.infinityfree.com');
define('DB_USER', 'if0_39352414');
define('DB_PASS', 'ERaipak2kDC');
define('DB_NAME', 'if0_39352414_bddcecilioenero2025');

// Aumentar límites
set_time_limit(300);
ini_set('memory_limit', '256M');

// Conectar a la base de datos
try {
    $pdo = new PDO(
        "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8mb4",
        DB_USER,
        DB_PASS,
        [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
    );
} catch (PDOException $e) {
    die("Error de conexión: " . $e->getMessage());
}

// Nombre del archivo
$filename = 'ceproca_backup_' . date('Y-m-d_H-i-s') . '.sql';

// Iniciar buffer de salida
ob_start();

// Encabezado del archivo SQL
echo "-- Respaldo de Base de Datos CEPROCA\n";
echo "-- Generado: " . date('Y-m-d H:i:s') . "\n";
echo "-- Base de datos: " . DB_NAME . "\n\n";
echo "SET SQL_MODE = \"NO_AUTO_VALUE_ON_ZERO\";\n";
echo "SET time_zone = \"+00:00\";\n\n";

// Obtener todas las tablas
$tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

foreach ($tables as $table) {
    echo "\n-- --------------------------------------------------------\n";
    echo "-- Estructura de tabla para la tabla `$table`\n";
    echo "-- --------------------------------------------------------\n\n";
    
    // Obtener estructura de la tabla
    echo "DROP TABLE IF EXISTS `$table`;\n";
    $createTable = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_ASSOC);
    echo $createTable['Create Table'] . ";\n\n";
    
    // Obtener datos de la tabla
    $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
    
    if (count($rows) > 0) {
        echo "-- Volcado de datos para la tabla `$table`\n\n";
        
        foreach ($rows as $row) {
            $columns = array_keys($row);
            $values = array_map(function($value) use ($pdo) {
                if ($value === null) {
                    return 'NULL';
                }
                return $pdo->quote($value);
            }, array_values($row));
            
            echo "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ");\n";
        }
        echo "\n";
    }
}

// Obtener contenido del buffer
$sqlContent = ob_get_clean();

// Enviar headers para descarga
header('Content-Type: application/sql');
header('Content-Disposition: attachment; filename="' . $filename . '"');
header('Content-Length: ' . strlen($sqlContent));
header('Cache-Control: no-cache, must-revalidate');
header('Pragma: no-cache');

// Enviar contenido
echo $sqlContent;
exit;
?>
