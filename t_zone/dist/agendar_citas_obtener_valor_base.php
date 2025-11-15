<?php
include "../../conexionsm.php";

if (isset($_POST['id_paciente'])) {
    $id_paciente = $_POST['id_paciente'];

    $sql = "SELECT valor_base FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id_paciente);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    echo json_encode(['valor_base' => $row ? (int)$row['valor_base'] : 0]);
}
?>
