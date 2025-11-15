<?php
session_start();
if (empty($_SESSION["id"])) {
    header("location: login");
    exit();
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99 && $_SESSION['numdoc'] !== '1000693019'){
    header("location: login");
}

include "../../conexionsm.php";

$id = $_SESSION["id"];

// Datos de los días (lunes a sábado)
$hora = $_POST['hora-excepcion-hour'];
$date = $_POST['fecha-excepcion-hour'];
$description = $_POST['causal-excepcion-hour'];

// Verificar si la fecha ya existe para el id_user (o para id_user = 0) y si el status es 0 y la hora es igual a $hora
$query_check = "SELECT COUNT(*) FROM hours_exception WHERE (id_user = ? OR id_user = 0) AND date = ? AND hour = ? AND status = 0";
$stmt_check = $conn->prepare($query_check);
$stmt_check->bind_param("iss", $id, $date, $hora);
$stmt_check->execute();
$stmt_check->bind_result($count);
$stmt_check->fetch();
$stmt_check->close();

// Función de encriptado XOR con clave
function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if ($count > 0) {
    // Redirige con el estado de error encriptado
    $encryptedStatus = simpleEncrypt('errexp', '2020');
    header('Location: disponibilidad?tab=2&in=' . urlencode($encryptedStatus));
    exit();
} else {
    // Si la fecha no existe, realizar el insert
    $query_insert = "INSERT INTO hours_exception (id_user, date, hour, description, status) VALUES (?, ?, ?, ?, 0)";
    $stmt_insert = $conn->prepare($query_insert);
    $stmt_insert->bind_param("isss", $id, $date, $hora, $description);
    
    if ($stmt_insert->execute()) {
        // Encriptar y redirigir con éxito
        $encryptedStatus = simpleEncrypt('newexphour', '2020');
        header('Location: disponibilidad?tab=2&in=' . urlencode($encryptedStatus));
        exit(); // Asegúrate de usar exit() para detener la ejecución
    } else {
        echo "Error al agregar la excepción.";
    }
    $stmt_insert->close();
}
?>
