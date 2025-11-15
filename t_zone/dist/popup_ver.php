<?php
include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Iniciar transacción en MySQLi
    $conn->autocommit(false);

    try {
        // Actualiza todos los registros a 1
        $sql1 = "UPDATE popups SET est = 1";
        if (!$conn->query($sql1)) {
            throw new Exception("Error al actualizar los registros a 1");
        }

        // Actualiza solo el registro seleccionado a 0
        $stmt = $conn->prepare("UPDATE popups SET est = 0 WHERE id = ?");
        $stmt->bind_param("i", $id);
        if (!$stmt->execute()) {
            throw new Exception("Error al actualizar el registro seleccionado");
        }

        // Confirmar cambios
        $conn->commit();
        $conn->autocommit(true);

        // Si todo salió bien, redirigir con estado cifrado
        $encryptedStatus = simpleEncrypt('verok', '2020'); 
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    } catch (Exception $e) {
        // Revertir cambios si hay error
        $conn->rollback();
        $conn->autocommit(true);

        // Enviar error cifrado
        $encryptedStatus = simpleEncrypt('vererror', '2020'); 
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    }
} else {
    // Si no hay ID, redirigir con error
    $encryptedStatus = simpleEncrypt('vernonoid', '2020'); 
    header('Location: popup?sta=' . urlencode($encryptedStatus));
    exit();
}
