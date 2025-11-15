<?php
// Incluir el archivo de conexión
include "../../conexionsm.php"; // Asegúrate de que la ruta sea correcta

try {
    // Obtener el ID del profesional desde la consulta
    $id_profesional = isset($_GET['id_profesional']) ? (int)$_GET['id_profesional'] : 0;

    // Consulta para obtener las fechas bloqueadas
    $stmt = $conn->prepare("SELECT date, status FROM days_exception WHERE id_user = ? AND status = 0");
    $stmt->bind_param("i", $id_profesional); // "i" indica que el parámetro es un entero
    $stmt->execute();

    // Obtener los resultados
    $result = $stmt->get_result();
    $fechas = $result->fetch_all(MYSQLI_ASSOC);

    // Devolver las fechas en formato JSON
    header('Content-Type: application/json');
    echo json_encode($fechas);
} catch (mysqli_sql_exception $e) {
    // Manejo de errores
    http_response_code(500);
    echo json_encode(['error' => 'Error en la conexión a la base de datos: ' . $e->getMessage()]);
}
?>