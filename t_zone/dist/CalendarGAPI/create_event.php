<?php
require 'vendor/autoload.php';
include "../../../conexionsm.php"; // Tu conexión a la base de datos

session_start();

$user_id = $_SESSION['id']; 

// Obtener tokens de la base de datos
$sql = "SELECT access_token, refresh_token, expires_in, UNIX_TIMESTAMP(created_at) as created_at FROM usuarios WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Si no hay access_token en la base de datos, ir a auth.php
if (!$user || !$user['access_token']) {
    header("Location: auth.php");
    exit;
}

$client = new Google\Client();
$client->setAuthConfig('client_secret.json');

// Verificar si el token está vencido
$expires_at = $user['created_at'] + $user['expires_in'];
if (time() >= $expires_at) {
    if (!$user['refresh_token']) {
        header("Location: auth.php"); // Redirigir si no hay refresh_token
        exit;
    }

    $client->refreshToken($user['refresh_token']);
    $new_token = $client->getAccessToken();

    // Guardar el nuevo access_token en la base de datos
    $sql = "UPDATE usuarios SET access_token=?, expires_in=?, created_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("sii", $new_token['access_token'], $new_token['expires_in'], $user_id);
    $stmt->execute();
    $stmt->close();

    $access_token = $new_token['access_token'];
} else {
    $access_token = $user['access_token'];
}

$client->setAccessToken($access_token);
$service = new Google\Service\Calendar($client);

// Crear evento en Google Calendar
$event = new Google\Service\Calendar\Event([
    'summary' => 'Prueba de Evento',
    'start' => ['dateTime' => date('c', strtotime('+1 hour'))], // 1 hora después de ahora
    'end' => ['dateTime' => date('c', strtotime('+2 hour'))], // 2 horas después de ahora
    'conferenceData' => [
        'createRequest' => [
            'requestId' => uniqid(),
            'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
        ],
    ],
    'attendees' => [
        ['email' => 'lmatiasmont@gmail.com']
    ],
]);

$calendarId = 'primary';
$event = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);

$meet_link = $event->getHangoutLink();
echo "✅ Evento creado con éxito: <a href='$meet_link'>Unirse a la reunión</a>";

?>
