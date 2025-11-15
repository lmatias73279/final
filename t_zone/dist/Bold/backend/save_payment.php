<?php
header('Content-Type: application/json');

// Incluye el archivo de conexión
include "../../../../conexionsm.php";

// Verifica si la conexión fue exitosa
if ($conn->connect_error) {
    die(json_encode(['success' => false, 'error' => 'Error de conexión a la base de datos']));
}

// Obtiene los datos enviados desde el frontend
$data = json_decode(file_get_contents('php://input'), true);

// Asegúrate de que todos los datos estén presentes antes de procesarlos
if (!isset($data['orderId'], $data['amount'], $data['currency'], $data['tipoTerapia'], $data['userID'], $data['site'], $data['q'])) {
    die(json_encode(['success' => false, 'error' => 'Faltan datos en la solicitud']));
}

// Escapa los datos para evitar inyección SQL
$q = $conn->real_escape_string($data['q']);
$site = $conn->real_escape_string($data['site']);
$userID = $conn->real_escape_string($data['userID']);
$orderId = $conn->real_escape_string($data['orderId']);
$amount = $conn->real_escape_string($data['amount']);
$currency = $conn->real_escape_string($data['currency']);
if($currency === "COP"){
    $currency = 1;
}else if($currency === "USD"){
    $currency = 2;
}else{
    $currency = 0;
}
$tipoTerapia = $conn->real_escape_string($data['tipoTerapia']);
$status = 'pending';

// Inserta el registro en la base de datos
$query = "INSERT INTO payments (order_id, amount, currency, tipoTerapia, status, userID, site, q) 
          VALUES ('$orderId', '$amount', '$currency', '$tipoTerapia', '$status', '$userID', '$site', '$q')";

if ($conn->query($query)) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => $conn->error]);
}

// Cierra la conexión
$conn->close();
?>
