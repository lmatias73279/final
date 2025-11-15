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

    // Preparar la consulta para actualizar "del" y "est" a 1
    $stmt = $conn->prepare("UPDATE popups SET del = 1, est = 1 WHERE id = ?");
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        // Redirigir con mensaje de éxito
        $encryptedStatus = simpleEncrypt('delok', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    } else {
        // Redirigir con mensaje de error
        $encryptedStatus = simpleEncrypt('delerror', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    }
} else {
    // Redirigir si no hay ID en la URL
    $encryptedStatus = simpleEncrypt('delnoid', '2020');
    header('Location: popup?sta=' . urlencode($encryptedStatus));
    exit();
}
