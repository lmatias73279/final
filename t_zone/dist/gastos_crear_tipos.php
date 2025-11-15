<?php
include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if (isset($_POST['descripcionTipo']) && isset($_POST['afecta'])) {
    $descripcion = $_POST['descripcionTipo'];
    $afecta = $_POST['afecta'];

    $sql = "INSERT INTO idsgastos (description, afecta) VALUES ('$descripcion', '$afecta')";
    if ($conn->query($sql)) {
        // Encriptar el estado y redirigir
        $encryptedStatus = simpleEncrypt('success_create', '2020');
        header('Location: gastos?sta=' . urlencode($encryptedStatus));
        exit(); // Asegurarse de que no se ejecute nada más después de redirigir
    } else {
        // Encriptar el estado de error y redirigir
        $encryptedStatus = simpleEncrypt('error_create', '2020');
        header('Location: gastos?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
