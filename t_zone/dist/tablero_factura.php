<?php
include "../../conexionsm.php";

if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['id']) && isset($_POST['numero_factura'])) {
    $id = $_POST['id'];
    $numero_factura = intval($_POST['numero_factura']); // Sanitizar el número de factura

    if ($id > 0 && $numero_factura > 0) {
        // Actualizar la columna fact
        $sql = "UPDATE sessions SET fact = ? WHERE `order` = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("is", $numero_factura, $id);

        if ($stmt->execute()) {
            $mensaje = "factura_actualizada";
        } else {
            $mensaje = "error_actualizar";
        }

        $stmt->close();
    } else {
        $mensaje = "datos_invalidos";
    }
} else {
    $mensaje = "sin_datos";
}

// Función para encriptar mensajes
function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output);
}

// Redirección con mensaje encriptado
$encryptedStatus = simpleEncrypt($mensaje, '2020');
header('Location: tablero?sta=' . urlencode($encryptedStatus));
exit();
?>
