<?php
session_start();
include "../../conexionsm.php";

$user_id = $_SESSION["id"];
$query = "SELECT id, tipo, userID, site, fecha, hora, link_ingreso FROM sessions WHERE 1=1 AND psi = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

$events = [];
while ($row = $result->fetch_assoc()) {
    // Consulta para obtener el nombre del usuario
    $userQuery = "SELECT CONCAT(pn_usu, ' ', pa_usu) AS nombre FROM usuarios WHERE id = ?";
    $userStmt = $conn->prepare($userQuery);
    $userStmt->bind_param("i", $row["userID"]);
    $userStmt->execute();
    $userResult = $userStmt->get_result();
    $userRow = $userResult->fetch_assoc();
    
    $nombreUsuario = $userRow ? $userRow["nombre"] : "Desconocido"; // Manejo de usuario no encontrado

    $events[] = [
        "id" => $row["id"],
        "title" => $nombreUsuario, // Se usa el nombre completo
        "start" => $row["fecha"] . "T" . $row["hora"], // Hora de inicio
        // Calculamos la hora de fin añadiendo 1 hora
        "end" => date('Y-m-d\TH:i:s', strtotime($row["fecha"] . "T" . $row["hora"] . " +1 hour")),
        "tipo" => $row["tipo"],
        "site" => $row["site"],
        "link_ingreso" => $row["link_ingreso"]
    ];
    
}

echo json_encode($events);
?>
