<?php
include "../../conexionsm.php"; // Incluir la conexión a la base de datos

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

// Verificar si se ha recibido el ID de la cita
if (isset($_GET['id'])) {
    $citaId = intval($_GET['id']); // Convertir el ID en un número entero

    // Actualizar el estado en la tabla `sessions`
    $query = "UPDATE sessions SET estado = 5 WHERE id = ?";
    if ($stmt = $conn->prepare($query)) {
        $stmt->bind_param("i", $citaId);
        if ($stmt->execute()) {
            // Generar el estado cifrado para redirigir
            $encryptedStatus = simpleEncrypt('cancorr', '2020');
            header('Location: agendar_citas?sta=' . urlencode($encryptedStatus));
            exit();
        } else {
            die("Error al actualizar la cita en la base de datos.");
        }
        $stmt->close();
    } else {
        die("Error al preparar la consulta.");
    }
} else {
    die("ID de cita no proporcionado.");
}

// Cerrar la conexión
$conn->close();
?>
