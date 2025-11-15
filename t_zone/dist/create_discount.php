<?php
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
}

include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['desde'], $_POST['hasta'], $_POST['codigo'], $_POST['descuento'])) {
        var_dump($_POST);
    }

    $desde = $_POST['desde'];
    $hasta = $_POST['hasta'];
    $codigo = $_POST['codigo'];
    $descuento = $_POST['descuento'];
    $estado = 1;

    // SQL corregido: Agregado campo faltante y corrección en nombre
    $sql = "INSERT INTO promos (desde, hasta, codigo, descuento, estado) 
            VALUES (?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Corrección en los valores enlazados: todos los campos deben coincidir
    $stmt->bind_param('sssii', $desde, $hasta, $codigo, $descuento, $estado);

    if ($stmt->execute()) {
        $encryptedStatus = simpleEncrypt('success_create', '2020');
        header('Location: promos.php?sta=' . urlencode($encryptedStatus));
        exit();
    } else {
        $encryptedStatus = simpleEncrypt('error_create', '2020');
        header('Location: promos.php?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
