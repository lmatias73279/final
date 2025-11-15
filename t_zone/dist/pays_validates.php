<?php
session_start();

// Verificar que el usuario esté autenticado
if (empty($_SESSION["id"])) {
    header("location: login");
    exit();
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
}

// Conectar a la base de datos
include "../../conexionsm.php";

// Función para encriptar los datos
function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Recuperar el ID de la comisión desde el formulario
    $idComision = isset($_POST['idComision']) ? $_POST['idComision'] : null;

    if ($idComision) {
        // Sanitizar el ID
        $idComision = mysqli_real_escape_string($conn, $idComision);

        // Consulta para actualizar el estado de la comisión en la tabla `sessions`
        $sql = "UPDATE sessions SET estado = 7 WHERE ID = '$idComision'";

        // Ejecutar la consulta
        if (mysqli_query($conn, $sql)) {
            // Si la actualización fue exitosa, redirige a la página `pays.php` con un mensaje de éxito
            $encryptedStatus = simpleEncrypt('success_update', '2020');
            header('Location: pays?sta=' . urlencode($encryptedStatus));
            exit();
        } else {
            // Si hay un error en la actualización, redirige con un mensaje de error
            $encryptedStatus = simpleEncrypt('error_update', '2020');
            header('Location: pays?sta=' . urlencode($encryptedStatus));
            exit();
        }
    } else {
        // Si no se recibió el ID de la comisión, redirige con un mensaje de error
        $encryptedStatus = simpleEncrypt('invalid_id', '2020');
        header('Location: pays?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
