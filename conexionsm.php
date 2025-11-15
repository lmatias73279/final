<?php
require_once __DIR__ . '/vendor/autoload.php';

use Dotenv\Dotenv;

// Cargar variables de entorno
$dotenv = Dotenv::createImmutable(__DIR__ . '/../');
$dotenv->load();

// Configurar MySQLi para lanzar excepciones en errores
mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);

try {
    $conn = new mysqli(
        $_ENV['DB_HOST'],
        $_ENV['DB_USER'],
        $_ENV['DB_PASS'],
        $_ENV['DB_NAME']
    );

    // Usar utf8mb4 para compatibilidad completa
    $conn->set_charset("utf8mb4");

    // Forzar modo estricto
    $conn->query("SET SESSION sql_mode = 'STRICT_ALL_TABLES'");

} catch (mysqli_sql_exception $e) {
    error_log("Error de conexión MySQL: " . $e->getMessage());
    die("Error interno de conexión. Intente más tarde.");
}
