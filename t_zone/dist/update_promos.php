<?php

session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
}

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
    // Obtén los datos del formulario (por ejemplo, del modal)
    $desde = isset($_POST['desde']) ? $_POST['desde'] : '';
    $hasta = isset($_POST['hasta']) ? $_POST['hasta'] : '';
    $codigo = isset($_POST['codigo']) ? $_POST['codigo'] : '';
    $descuento = isset($_POST['descuento']) ? $_POST['descuento'] : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : '';

    // Sanitizar los datos
    $desde = mysqli_real_escape_string($conn, $desde);
    $hasta = mysqli_real_escape_string($conn, $hasta);
    $codigo = mysqli_real_escape_string($conn, $codigo);
    $descuento = mysqli_real_escape_string($conn, $descuento);
    $estado = mysqli_real_escape_string($conn, $estado);

    // Obtener los datos actuales de la base de datos
    $sql = "SELECT * FROM promos WHERE codigo = '$codigo'";
    $result = mysqli_query($conn, $sql);
    $currentPromo = mysqli_fetch_assoc($result);

    // Verificar si los valores son iguales a los actuales
    if ($currentPromo['desde'] == $desde && $currentPromo['hasta'] == $hasta && $currentPromo['descuento'] == $descuento && $currentPromo['estado'] == $estado) {
        // Si no hay cambios, redirige con el mensaje 'no_update'
        $encryptedStatus = simpleEncrypt('no_update', '2020');
        header('Location: promos?sta=' . urlencode($encryptedStatus));
        exit();
    }

    // Preparar la consulta UPDATE
    $sql = "UPDATE promos 
            SET desde = '$desde', hasta = '$hasta', codigo = '$codigo', descuento = '$descuento', estado = '$estado' 
            WHERE codigo = '$codigo'";

    // Ejecutar la consulta
    if (mysqli_query($conn, $sql)) {
        // Si la actualización fue exitosa, redirige con éxito
        $encryptedStatus = simpleEncrypt('success_update', '2020');
        header('Location: promos?sta=' . urlencode($encryptedStatus));
        exit();
    } else {
        // Si hay un error, redirige con error
        $encryptedStatus = simpleEncrypt('error_update', '2020');
        header('Location: promos?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
