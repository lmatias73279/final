<?php
session_start();
include "../../conexionsm.php"; // Ajusta la ruta según tu proyecto

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id_alerta'])) {
    $id_alerta = $_POST['id_alerta'];
    $id_user = $_SESSION['id']; 

    // Insertar solo si no existe ya
    $sql = "INSERT IGNORE INTO alertasviews (id_user, id_alerta, fecha) VALUES (?, ?, NOW())";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $id_user, $id_alerta);

    if ($stmt->execute()) {
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode(["status" => "error", "message" => $stmt->error]);
    }

    $stmt->close();
    $conn->close();
} else {
    echo json_encode(["status" => "error", "message" => "Solicitud inválida"]);
}
?>
