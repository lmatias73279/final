<?php
require 'vendor/autoload.php';
include "../../../conexionsm.php"; // Tu conexión a la base de datos

session_start();

$client = new Google\Client();
$client->setAuthConfig('client_secret.json');
$client->setRedirectUri('https://saludmentalsanamente.com.co/t_zone/dist/CalendarGAPI/callback.php');
$client->addScope(Google\Service\Calendar::CALENDAR_EVENTS);
$client->setAccessType('offline'); 
$client->setPrompt('consent'); // Obliga a Google a enviar el refresh_token

$authUrl = $client->createAuthUrl();
header('Location: ' . filter_var($authUrl, FILTER_SANITIZE_URL));
exit;
?>
