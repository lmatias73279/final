<?php
require 'vendor/autoload.php';
include "../../../conexionsm.php"; // Tu conexión a la base de datos

session_start();

$client = new Google\Client();
$client->setAuthConfig('client_secret.json');
$client->setRedirectUri('https://saludmentalsanamente.com.co/t_zone/dist/CalendarGAPI/callback.php');
$client->addScope(Google\Service\Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline');

if (!isset($_GET['code'])) {
    die("❌ Error: No se recibió el código de autenticación.");
}

$token = $client->fetchAccessTokenWithAuthCode($_GET['code']);

if (isset($token['error'])) {
    die("❌ Error: " . $token['error']);
}

$access_token = $token['access_token'];
$refresh_token = isset($token['refresh_token']) ? $token['refresh_token'] : null;
$expires_in = $token['expires_in'];
$user_id = $_SESSION['id']; // Asegúrate de que el usuario tiene sesión iniciada

// Guardar o actualizar en la base de datos
$sql = "UPDATE usuarios SET access_token=?, refresh_token=IFNULL(?, refresh_token), expires_in=?, created_at=NOW() WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssii", $access_token, $refresh_token, $expires_in, $user_id);
$stmt->execute();
$stmt->close();

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output);
}

$encryptedStatus = simpleEncrypt('atsok', '2020');

$qsess = $_SESSION['permiso'];
if($qsess == 1){
    header('Location: ../agendar_citas?sta=' . urlencode($encryptedStatus));
}else if($qsess == 3){
    header('Location: ../calendar?sta=' . urlencode($encryptedStatus));
}else{
    header('Location: ../controlador_cerrar_sesion');
}
exit();
exit;
?>
