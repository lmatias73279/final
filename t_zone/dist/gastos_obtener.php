<?php
include "../../conexionsm.php";

// Verificar que se ha enviado un ID
if (isset($_GET['id'])) {
    $idGasto = $_GET['id'];

    // Consulta para obtener los detalles del gasto
    $sql = "SELECT * FROM gastos WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $idGasto);
    $stmt->execute();
    $result = $stmt->get_result();

    // Si existe el gasto, devolver los datos como JSON
    if ($result->num_rows > 0) {
        $gasto = $result->fetch_assoc();
        echo json_encode($gasto);
    } else {
        echo json_encode(["error" => "Gasto no encontrado"]);
    }

    $stmt->close();
}
?>
