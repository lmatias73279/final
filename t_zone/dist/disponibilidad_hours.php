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
$lunes1d = $_POST['lunes-inicio1'];
$lunes1h = $_POST['lunes-fin1'];
$lunes2d = $_POST['lunes-inicio2'];
$lunes2h = $_POST['lunes-fin2'];

$martes1d = $_POST['martes-inicio1'];
$martes1h = $_POST['martes-fin1'];
$martes2d = $_POST['martes-inicio2'];
$martes2h = $_POST['martes-fin2'];

$miercoles1d = $_POST['miercoles-inicio1'];
$miercoles1h = $_POST['miercoles-fin1'];
$miercoles2d = $_POST['miercoles-inicio2'];
$miercoles2h = $_POST['miercoles-fin2'];

$jueves1d = $_POST['jueves-inicio1'];
$jueves1h = $_POST['jueves-fin1'];
$jueves2d = $_POST['jueves-inicio2'];
$jueves2h = $_POST['jueves-fin2'];

$viernes1d = $_POST['viernes-inicio1'];
$viernes1h = $_POST['viernes-fin1'];
$viernes2d = $_POST['viernes-inicio2'];
$viernes2h = $_POST['viernes-fin2'];

$sabado1d = $_POST['sabado-inicio1'];
$sabado1h = $_POST['sabado-fin1'];
$sabado2d = $_POST['sabado-inicio2'];
$sabado2h = $_POST['sabado-fin2'];

// Verificar si el usuario ya tiene una entrada en la base de datos
$sql_check = "SELECT id_user FROM disponibilidad WHERE id_user = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $id);
$stmt_check->execute();
$stmt_check->store_result();


// Función de encriptado XOR con clave
function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if ($stmt_check->num_rows > 0) {
    // Si el usuario ya existe, actualizar los datos
    $sql_update = "UPDATE disponibilidad SET 
        lu1d = ?, lu1h = ?, lu2d = ?, lu2h = ?, 
        ma1d = ?, ma1h = ?, ma2d = ?, ma2h = ?, 
        mi1d = ?, mi1h = ?, mi2d = ?, mi2h = ?, 
        ju1d = ?, ju1h = ?, ju2d = ?, ju2h = ?, 
        vi1d = ?, vi1h = ?, vi2d = ?, vi2h = ?, 
        sa1d = ?, sa1h = ?, sa2d = ?, sa2h = ? 
        WHERE id_user = ?";

    $stmt_update = $conn->prepare($sql_update);
    $stmt_update->bind_param(
        "iiiiiiiiiiiiiiiiiiiiiiiii", 
        $lunes1d, $lunes1h, $lunes2d, $lunes2h,
        $martes1d, $martes1h, $martes2d, $martes2h,
        $miercoles1d, $miercoles1h, $miercoles2d, $miercoles2h,
        $jueves1d, $jueves1h, $jueves2d, $jueves2h,
        $viernes1d, $viernes1h, $viernes2d, $viernes2h,
        $sabado1d, $sabado1h, $sabado2d, $sabado2h,
        $id
    );

    if ($stmt_update->execute()) {
        // Encriptar el estado de éxito y redirigir
        $encryptedStatus = simpleEncrypt('acthours', '2020');
        header('Location: disponibilidad?in=' . urlencode($encryptedStatus));
        exit(); // Asegúrate de usar exit() para detener la ejecución
    } else {
        // Si hay un error, encriptar y redirigir con el estado de error
        $encryptedStatus = simpleEncrypt('erracthours', '2020');
        header('Location: disponibilidad?in=' . urlencode($encryptedStatus));
        exit(); // Asegúrate de usar exit() para detener la ejecución
    }
    $stmt_update->close();
} else {
    // Si el usuario no existe, insertar los nuevos datos
    $sql_insert = "INSERT INTO disponibilidad (
        id_user, 
        lu1d, lu1h, lu2d, lu2h, 
        ma1d, ma1h, ma2d, ma2h, 
        mi1d, mi1h, mi2d, mi2h, 
        ju1d, ju1h, ju2d, ju2h, 
        vi1d, vi1h, vi2d, vi2h, 
        sa1d, sa1h, sa2d, sa2h
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param(
        "iiiiiiiiiiiiiiiiiiiiiiiii", 
        $id, 
        $lunes1d, $lunes1h, $lunes2d, $lunes2h,
        $martes1d, $martes1h, $martes2d, $martes2h,
        $miercoles1d, $miercoles1h, $miercoles2d, $miercoles2h,
        $jueves1d, $jueves1h, $jueves2d, $jueves2h,
        $viernes1d, $viernes1h, $viernes2d, $viernes2h,
        $sabado1d, $sabado1h, $sabado2d, $sabado2h
    );

    // Código PHP para la inserción o actualización
    if ($stmt_insert->execute()) {
        // Enviar el estado de éxito y redirigir
        $encryptedStatus = simpleEncrypt('newhours', '2020');
        header('Location: disponibilidad?in=' . urlencode($encryptedStatus));
        exit(); // Asegúrate de usar exit() para evitar que el script continúe ejecutándose
    } else {
        // Si hay un error, encriptar y redirigir con un estado de error
        $encryptedStatus = simpleEncrypt('errnewhours', '2020');
        header('Location: disponibilidad?in=' . urlencode($encryptedStatus));
        exit(); // Asegúrate de usar exit() para evitar que el script continúe ejecutándose
    }
    $stmt_insert->close();
}

$stmt_check->close();
$conn->close();
?>
