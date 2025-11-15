<?php
require '../../conexionsm.php'; // Asegúrate de conectar tu base de datos

if (isset($_POST['ref'])) {
    $ref = $_POST['ref'];

    // Consulta a la base de datos
    $stmt = $conn->prepare("SELECT fecha, valor FROM pays WHERE ref = ?");
    $stmt->bind_param("s", $ref);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows > 0) {
        $stmt->bind_result($fecha, $valor);
        $stmt->fetch();
        
        echo json_encode(["exists" => true, "fecha" => $fecha, "valor" => $valor]);
    } else {
        echo json_encode(["exists" => false]);
    }

    $stmt->close();
    $conn->close();
}
?>
