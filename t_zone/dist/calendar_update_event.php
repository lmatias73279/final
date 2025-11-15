<?php
require 'CalendarGAPI/vendor/autoload.php';
include "../../conexionsm.php"; // Tu conexión correcta

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

// Paso 1: Obtener datos POST
$editar_id = $_POST['editId']; // ID de la cita a editar
$nueva_fecha = $_POST['editDate']; // Nueva fecha de la cita
$nueva_hora = $_POST['editTime']; // Nueva hora de la cita
$observacion = $_POST['observa']; // Observación de cambio

// Paso 2: Obtener datos actuales de la cita
$query = "SELECT idevent, fecha, hora, userID, psi, link_ingreso, tipo, `site` FROM sessions WHERE ID = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $editar_id);
$stmt->execute();
$result = $stmt->get_result();
$oldData = $result->fetch_assoc();
$stmt->close();

if (!$oldData) {
    die("Error: No se encontró la cita.");
}

$eventId = $oldData['idevent'];
$fecha_ant = $oldData['fecha'];
$hora_ant = $oldData['hora'];
$userID = $oldData['userID'];
$id_profesional = $oldData['psi'];
$meet_link = $oldData['link_ingreso'];
$tipo_terapia = $oldData['tipo'];
$tipo_atencion = $oldData['site'];

// Obtener datos del usuario
$queryUser = "SELECT pn_usu, sn_usu, cor_usu FROM usuarios WHERE id = ?";
$stmtUser = $conn->prepare($queryUser);
$stmtUser->bind_param("i", $userID);
$stmtUser->execute();
$resultUser = $stmtUser->get_result();
$userData = $resultUser->fetch_assoc();
$stmtUser->close();

if (!$userData) {
    die("Error: No se encontraron los datos del usuario.");
}

$pn_usu = $userData['pn_usu'];
$sn_usu = $userData['sn_usu'];
$cor_usu_paciente = $userData['cor_usu'];

// Obtener datos del profesional
$queryProfesional = "SELECT cor_usu FROM usuarios WHERE id = ?";
$stmtProfesional = $conn->prepare($queryProfesional);
$stmtProfesional->bind_param("i", $id_profesional);
$stmtProfesional->execute();
$resultProfesional = $stmtProfesional->get_result();
$profesionalData = $resultProfesional->fetch_assoc();
$stmtProfesional->close();

if (!$profesionalData) {
    die("Error: No se encontraron los datos del profesional.");
}

$cor_usu_profesional = $profesionalData['cor_usu'];

// Paso 3: Registrar el cambio en `meetchanges`
$queryInsert = "INSERT INTO meetchanges (idse, fechaant, horaant, observacion) VALUES (?, ?, ?, ?)";
$stmtInsert = $conn->prepare($queryInsert);
$stmtInsert->bind_param("isss", $editar_id, $fecha_ant, $hora_ant, $observacion);
if (!$stmtInsert->execute()) {
    die("Error al insertar en meetchanges: " . $stmtInsert->error);
}
$stmtInsert->close();

// Paso 4: Configurar cliente Google
$client = new Google\Client();
$client->setAuthConfig('CalendarGAPI/client_secret.json');

// Obtener tokens desde la base de datos
$queryToken = "SELECT access_token, refresh_token, expires_in, UNIX_TIMESTAMP(created_at) as created_at 
               FROM usuarios u INNER JOIN sessions s ON u.id = s.psi 
               WHERE s.id = ?";
$stmt = $conn->prepare($queryToken);
$stmt->bind_param("i", $editar_id);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

if (!$user || !$user['access_token']) {
    die("Error: No hay token para Google Calendar.");
}

// Validar expiración del token
$expires_at = $user['created_at'] + $user['expires_in'];
if (time() >= $expires_at) {
    if (!$user['refresh_token']) {
        die("Error: Token expirado y no hay refresh_token.");
    }

    $client->refreshToken($user['refresh_token']);
    $new_token = $client->getAccessToken();

    // Guardar nuevo token
    $sql = "UPDATE usuarios SET access_token=?, refresh_token=?, expires_in=?, created_at=NOW() 
            WHERE id=(SELECT psi FROM sessions WHERE id = ?)";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $new_token['access_token'], $new_token['refresh_token'], $new_token['expires_in'], $editar_id);
    $stmt->execute();
    $stmt->close();

    $access_token = $new_token['access_token'];
} else {
    $access_token = $user['access_token'];
}

$client->setAccessToken($access_token);
$service = new Google\Service\Calendar($client);

// Paso 5: Obtener y actualizar el evento
try {
    $calendarId = 'primary';
    $event = $service->events->get($calendarId, $eventId);

    $timezone = new DateTimeZone('America/Bogota');
    $start = new DateTime("$nueva_fecha $nueva_hora", $timezone);
    $end = clone $start;
    $end->modify('+1 hour');

    $event->setStart(new Google\Service\Calendar\EventDateTime([
        'dateTime' => $start->format('Y-m-d\TH:i:s'),
        'timeZone' => 'America/Bogota',
    ]));

    $event->setEnd(new Google\Service\Calendar\EventDateTime([
        'dateTime' => $end->format('Y-m-d\TH:i:s'),
        'timeZone' => 'America/Bogota',
    ]));

    $service->events->update($calendarId, $eventId, $event);

    // Paso 6: Actualizar base de datos local
    $updateQuery = "UPDATE sessions SET fecha = ?, hora = ? WHERE ID = ?";
    $stmt = $conn->prepare($updateQuery);
    $stmt->bind_param("ssi", $nueva_fecha, $nueva_hora, $editar_id);
    $stmt->execute();
    $stmt->close();

    // Paso 7: Redirigir con estado encriptado
    $encryptedStatus = simpleEncrypt('reagcorr', '2020');
    $params = $_GET;
    $params['sta'] = $encryptedStatus;
    $queryString = http_build_query($params);
    $queryString = simpleEncrypt($queryString, '2020');

    // Redirigir
    header('Location: mails/calendar_update_event?mail='.$cor_usu_paciente.'&name='.$pn_usu.' '.$sn_usu.'&fecha='.$nueva_fecha.'&hora='.$nueva_hora.'&link='.$meet_link.'&tipo_terapia='.$tipo_terapia.'&modalidad='.$tipo_atencion.'&profesional='.$id_profesional.'&corpro='.$cor_usu_profesional.'&dasta='.$queryString);
    exit();

} catch (Google_Service_Exception $e) {
    die("Error: No se pudo actualizar el evento en Google Calendar. " . $e->getMessage());
}
?>
