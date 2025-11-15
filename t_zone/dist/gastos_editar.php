<?php
include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if (isset($_POST['editId'], $_POST['editTipoGasto'], $_POST['editFechaGasto'], $_POST['editDescripcionGasto'], $_POST['editValorGasto'], $_POST['editmedio_pago'])) {
    $idGasto = $_POST['editId'];
    $tipoGasto = $_POST['editTipoGasto'];
    $fechaGasto = $_POST['editFechaGasto'];
    $descripcionGasto = $_POST['editDescripcionGasto'];
    $valorGasto = $_POST['editValorGasto'];
    $medio_pago = $_POST['editmedio_pago'];

    // Actualizar el gasto en la base de datos
    $sql = "UPDATE gastos SET date = ?, id_gasto = ?, description = ?, value = ?, banco = ? WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('sisiii', $fechaGasto, $tipoGasto, $descripcionGasto, $valorGasto, $medio_pago, $idGasto);

    if ($stmt->execute()) {
        // Redirigir con mensaje de éxito
        $encryptedStatus = simpleEncrypt('success_update', '2020');
        header('Location: gastos?sta=' . urlencode($encryptedStatus));
    } else {
        // Redirigir con mensaje de error
        $encryptedStatus = simpleEncrypt('error_update', '2020');
        header('Location: gastos?sta=' . urlencode($encryptedStatus));
    }

    $stmt->close();
}
?>
