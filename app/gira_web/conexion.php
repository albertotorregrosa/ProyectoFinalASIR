<?php
/**
 * Conexión a la base de datos GiraHub
 * Detecta el entorno (Local vs Hostinger VPS) automáticamente.
 */

$es_local = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1', '::1']);

if ($es_local) {
    // Entorno Local (XAMPP / Antigravity)
    $db_host = 'localhost';
    $db_user = 'root';
    $db_pass = '';
    $db_name = 'gira_db';
    $db_port = 3306;
} else {
    // Entorno Producción (VPS)
    $db_host = '31.97.157.220';
    $db_user = 'gira_user';
    $db_pass = 'contrasena_user'; // Manteniendo el password existente en servidor
    $db_name = 'gira_db';
    $db_port = 3306;
}

// Intentar la conexión con mysqli (Sentencias Preparadas)
try {
    // Desactivar el reporte de errores predeterminado para capturar excepciones
    mysqli_report(MYSQLI_REPORT_ERROR | MYSQLI_REPORT_STRICT);
    
    $conn = new mysqli($db_host, $db_user, $db_pass, $db_name, $db_port);
    $conn->set_charset("utf8mb4");

} catch (mysqli_sql_exception $e) {
    // En producción es mejor no mostrar errores detallados,
    // pero para desarrollo mostraremos qué falla
    die("Error fatal de conexión a la base de datos." . ($es_local ? " Detalles: " . $e->getMessage() : ""));
}
?>