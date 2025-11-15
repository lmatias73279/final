<?php
require 'CalendarGAPI/vendor/autoload.php';
include "../../conexionsm.php"; // Tu conexión a la base de datos

session_start();

$user_id = $_SESSION['id']; 

// Obtener datos del formulario
$id_profesional = $_POST['id_profesional'];
$id_paciente = $_POST['id_paciente'];
$fecha = $_POST['fecha'];
$hora = $_POST['hora'];
$tipo_terapia = $_POST['tipo_terapia'];
$tipo_atencion = $_POST['tipo_atencion'];
if($tipo_atencion === '1'){
    $atencionmeet = "Presencial";
}else if($tipo_atencion === '2'){
    $atencionmeet = "Virtual";
}else{
    $atencionmeet = "";
}

$sqlfnp = "SELECT pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sqlfnp);
$stmt->bind_param("i", $id_paciente);
$stmt->execute();
$resultfnp = $stmt->get_result();

if ($rowfnp = $resultfnp->fetch_assoc()) {
    $pn_usu = ucfirst(strtolower($rowfnp['pn_usu'] ?? ''));
    $sn_usu = ucfirst(strtolower($rowfnp['sn_usu'] ?? ''));
    $pa_usu = ucfirst(strtolower($rowfnp['pa_usu'] ?? ''));
    $sa_usu = ucfirst(strtolower($rowfnp['sa_usu'] ?? ''));    
    
    $fullnamepac = '';
    
    if (!empty($pn_usu)) {
        $fullnamepac .= $pn_usu;
        if (!empty($sn_usu)) {
            $fullnamepac .= ' ' . $sn_usu;
        }
    }
    
    if (!empty($pa_usu)) {
        $fullnamepac .= ' ' . $pa_usu;
        if (!empty($sa_usu)) {
            $fullnamepac .= ' ' . $sa_usu;
        }
    }

    // Convertir todo a minúsculas primero
    $fullnamepac = mb_strtolower($fullnamepac, 'UTF-8');

    // Poner la primera letra de cada palabra en mayúscula
    $fullnamepac = mb_convert_case($fullnamepac, MB_CASE_TITLE, "UTF-8");
    
} else {
    $fullnamepac = "";
}

$tipos_terapia = [
    "1" => "Terapia Individual",
    "2" => "Terapia de Pareja",
    "5" => "Terapia de Familia",
    "6" => "Terapia Psiquiátrica",
    "7" => "Valoración",
    "8" => "Terapia Nutrición",
    "9" => "Terapia Infantil"
];

// Guardar la variable con el formato "Terapia {tipo}"
$descripcion_terapia = $tipos_terapia[$tipo_terapia] . " " . $atencionmeet . " | " . $fullnamepac;

// Consultar la tabla usuarios para obtener cor_usu de $id_profesional
$stmt_profesional = $conn->prepare("SELECT cor_usu FROM usuarios WHERE id = ?");
$stmt_profesional->bind_param("i", $id_profesional);
$stmt_profesional->execute();
$stmt_profesional->bind_result($cor_usu_profesional);
$stmt_profesional->fetch();
$stmt_profesional->close();

// Consultar la tabla usuarios para obtener cor_usu de $id_paciente
$stmt_paciente = $conn->prepare("SELECT cor_usu, hiscli, proceso FROM usuarios WHERE id = ?");
$stmt_paciente->bind_param("i", $id_paciente);
$stmt_paciente->execute();
$stmt_paciente->bind_result($cor_usu_paciente, $hiscli_paciente, $proceso_paciente);
$stmt_paciente->fetch();
$stmt_paciente->close();

// Verificar si proceso es igual a 2
if ($proceso_paciente === 2) {
    // Consultar la tabla familiares para obtener el correo del familiar según hiscli
    $stmt_familiar = $conn->prepare("SELECT correo FROM familiares WHERE hiscli = ?");
    $stmt_familiar->bind_param("s", $hiscli_paciente);
    $stmt_familiar->execute();
    $stmt_familiar->bind_result($corpareja);
    $stmt_familiar->fetch();
    $stmt_familiar->close();
} else {
    $corpareja = 'citas@saludmentalsanamente.com.co';
}

// Obtener tokens de la base de datos
$sql = "SELECT access_token, refresh_token, expires_in, UNIX_TIMESTAMP(created_at) as created_at FROM usuarios WHERE id=?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_profesional);
$stmt->execute();
$result = $stmt->get_result();
$user = $result->fetch_assoc();
$stmt->close();

// Si no hay access_token en la base de datos, ir a auth.php
if (!$user || !$user['access_token']) {
    header("Location: CalendarGAPI/auth.php");
    exit;
}

$client = new Google\Client();
$client->setAuthConfig('CalendarGAPI/client_secret.json');

// Verificar si el token está vencido
$expires_at = $user['created_at'] + $user['expires_in'];
if (time() >= $expires_at) {
    if (!$user['refresh_token']) {
        header("Location: CalendarGAPI/auth.php"); // Redirigir si no hay refresh_token
        exit;
    }

    $client->refreshToken($user['refresh_token']);
    $new_token = $client->getAccessToken();
    
    // Verificar si Google devolvió un nuevo refresh_token
    $refresh_token = $user['refresh_token']; // Mantén el actual
    if (isset($new_token['refresh_token'])) {
        $refresh_token = $new_token['refresh_token']; // Actualiza si hay uno nuevo
    }
    
    // Guardar el nuevo access_token y refresh_token en la base de datos
    $sql = "UPDATE usuarios SET access_token=?, refresh_token=?, expires_in=?, created_at=NOW() WHERE id=?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ssii", $new_token['access_token'], $refresh_token, $new_token['expires_in'], $user_id);
    $stmt->execute();
    $stmt->close();

    $access_token = $new_token['access_token'];
} else {
    $access_token = $user['access_token'];
}

$client->setAccessToken($access_token);
$service = new Google\Service\Calendar($client);

// Convertir $hora a formato correcto (H:i:s)
$hora_formateada = substr($hora, 0, 2) . ':' . substr($hora, 2, 2) . ':' . substr($hora, 4, 2);

// Asegurar que la fecha y hora están en la zona horaria correcta
$timezone = new DateTimeZone('America/Bogota');
$fecha_hora_inicio = new DateTime("$fecha $hora_formateada", $timezone);
$fecha_hora_fin = clone $fecha_hora_inicio;
if (isset($_POST['agendarDosHoras'])) {
    // Si se seleccionó el checkbox, sumar 2 horas
    $fecha_hora_fin->modify('+2 hours');
    $checkdh = 2;
} else {
    // Si no se seleccionó, sumar solo 1 hora
    $fecha_hora_fin->modify('+1 hour');
    $checkdh = 0;
}

// Formatear en ISO 8601 correctamente
$datetime_inicio = $fecha_hora_inicio->format('Y-m-d\TH:i:s');
$datetime_fin = $fecha_hora_fin->format('Y-m-d\TH:i:s');


$stmt = $conn->prepare("SELECT * FROM sessions WHERE psi = ? AND userID = ? AND fecha = ? AND hora = ? AND tipo = ? AND estado < 5");
$stmt->bind_param("iisss", $id_profesional, $id_paciente, $fecha, $hora, $tipo_terapia);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $encryptedStatus = simpleEncrypt('error_exists', '2020');
    header('Location: citas_tmrr?sta=' . urlencode($encryptedStatus));
    exit();
} else {
    // Crear evento en Google Calendar con zona horaria explícita
    $event = new Google\Service\Calendar\Event([
        'summary' => $descripcion_terapia,
        'start' => [
            'dateTime' => $datetime_inicio,
            'timeZone' => 'America/Bogota'
        ],
        'end' => [
            'dateTime' => $datetime_fin,
            'timeZone' => 'America/Bogota'
        ],
        'conferenceData' => [
            'createRequest' => [
                'requestId' => uniqid(),
                'conferenceSolutionKey' => ['type' => 'hangoutsMeet'],
            ],
        ],
        'attendees' => [
            ['email' => $corpareja],
            ['email' => $cor_usu_paciente],
            ['email' => 'citas@saludmentalsanamente.com.co']
        ],   
    ]);
}

try {
    $calendarId = 'primary';
    $event = $service->events->insert($calendarId, $event, ['conferenceDataVersion' => 1]);
    $meet_link = $event->getHangoutLink();
    $eventId = $event->getId(); // Obtén el ID del evento
    
    $intentos = 0;

    // Reintenta hasta 5 veces si el ID está vacío o es '0'
    while ((empty($eventId) || $eventId === '0') && $intentos < 10) {
        sleep(2); // espera 2 segundos
        $eventId = $event->getId();
        $intentos++;
    }
    
    // Si el link está vacío, volver a obtener el evento
    if (empty($meet_link)) {
        sleep(2); // espera unos segundos
    
        // Obtener el evento nuevamente
        $fetchedEvent = $service->events->get(
            $calendarId,
            $eventId
        );
    
        $meet_link = $fetchedEvent->getHangoutLink();
    
        // Si aún está vacío, forzar creación de conferenceData
        if (empty($meet_link)) {
            // Agregar conferenceData manualmente al evento ya creado
            $fetchedEvent->setConferenceData([
                'createRequest' => [
                    'requestId' => uniqid(),
                    'conferenceSolutionKey' => ['type' => 'hangoutsMeet']
                ]
            ]);
    
            $updatedEvent = $service->events->patch(
                $calendarId,
                $eventId,
                $fetchedEvent,
                ['conferenceDataVersion' => 1]
            );
    
            $meet_link = $updatedEvent->getHangoutLink();
        }
    }

} catch (Google\Service\Exception $e) {
    $error = json_decode($e->getMessage(), true);

    // Si el error es 401 (Invalid Credentials), redirigir a auth.php
    if ($error['error']['code'] == 401) {
        header("Location: CalendarGAPI/auth.php");
        exit();
    } else {
        // Si es otro error, mostrarlo
        echo "Error inesperado: " . $e->getMessage();
        exit();
    }
}

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

// Verificar si el registro ya existe con estado 3
if ($tipo_terapia == 7) {
    $stmt = $conn->prepare("SELECT * FROM sessions WHERE userID = ? AND (tipo = 7 OR tipo = 1) AND estado = 3 ORDER BY id ASC LIMIT 1");
    $stmt->bind_param("i", $id_paciente);
} else {
    $stmt = $conn->prepare("SELECT * FROM sessions WHERE userID = ? AND tipo = ? AND estado = 3 ORDER BY id ASC LIMIT 1");
    $stmt->bind_param("ii", $id_paciente, $tipo_terapia);
}

$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $session_id = $row['ID'];

    $stmt = $conn->prepare("UPDATE sessions SET idevent = ?, fecha = ?, hora = ?, site = ?, tipo = ?, link_ingreso = ?, estado = 2 WHERE id = ?");
    $stmt->bind_param("ssssisi", $eventId, $fecha, $hora, $tipo_atencion, $tipo_terapia, $meet_link, $session_id);
        
    if ($stmt->execute()) {
        header('Location: mails/agendar_cita_sessions?mail='.$cor_usu_paciente.'&name='.$pn_usu.' '.$sn_usu.'&fecha='.$fecha.'&hora='.$hora.'&link='.$meet_link.'&tipo_terapia='.$tipo_terapia.'&modalidad='.$tipo_atencion.'&profesional='.$id_profesional.'&corpro='.$cor_usu_profesional);
        exit();
    } else {
        $encryptedStatus = simpleEncrypt('error_update', '2020');
        header('Location: citas_tmrr?sta=' . urlencode($encryptedStatus));
        exit();
    }
} else {
    $stmt = $conn->prepare("SELECT * FROM sessions WHERE psi = ? AND userID = ? AND fecha = ? AND hora = ? AND tipo = ? AND estado < 5");
    $stmt->bind_param("iisss", $id_profesional, $id_paciente, $fecha, $hora, $tipo_terapia);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $encryptedStatus = simpleEncrypt('error_exists', '2020');
        header('Location: citas_tmrr?sta=' . urlencode($encryptedStatus));
        exit();
    } else {
        // Función para generar el código único
        function generarCodigoUnico() {
            // 1. Parte aleatoria (5 caracteres alfanuméricos en MAYÚSCULAS)
            $caracteres = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
            $randomPart = '';
            for ($i = 0; $i < 5; $i++) {
                $randomPart .= $caracteres[random_int(0, 35)]; // 0-35 por los 36 caracteres posibles
            }

            // 2. Parte de fecha actual (YYYYMMDDHHMMSS)
            $fechaPart = date('YmdHis'); // Formato: AñoMesDíaHoraMinutosSegundos

            // Combinar ambas partes
            return $randomPart . $fechaPart;
        }

        // Guardar el código en una variable (sin imprimirlo)
        $codigoUnico = generarCodigoUnico();

        $stmt = $conn->prepare("INSERT INTO sessions (idevent, psi, userID, fecha, hora, tipo, site, estado, link_ingreso, dh, iddh) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)");
        $stmt->bind_param("siissiisis", $eventId, $id_profesional, $id_paciente, $fecha, $hora, $tipo_terapia, $tipo_atencion, $meet_link, $checkdh, $codigoUnico);

        if ($stmt->execute()) {
            if($checkdh === 2){
                $horadh = $hora + 10000;
                $stmt = $conn->prepare("INSERT INTO sessions (idevent, psi, userID, fecha, hora, tipo, site, estado, link_ingreso, dh, iddh) VALUES (?, ?, ?, ?, ?, ?, ?, 1, ?, ?, ?)");
                $stmt->bind_param("siissiisis", $eventId, $id_profesional, $id_paciente, $fecha, $horadh, $tipo_terapia, $tipo_atencion, $meet_link, $checkdh, $codigoUnico);
                if ($stmt->execute()) {
                    header('Location: mails/agendar_cita_sessions?mail='.$cor_usu_paciente.'&name='.$pn_usu.' '.$sn_usu.'&fecha='.$fecha.'&hora='.$hora.'&link='.$meet_link.'&tipo_terapia='.$tipo_terapia.'&modalidad='.$tipo_atencion.'&profesional='.$id_profesional.'&corpro='.$cor_usu_profesional);
                    exit();
                } else {
                    $encryptedStatus = simpleEncrypt('error_insert', '2020');
                    header('Location: citas_tmrr?sta=' . urlencode($encryptedStatus));
                    exit();
                }
            }else{
                header('Location: mails/agendar_cita_sessions?mail='.$cor_usu_paciente.'&name='.$pn_usu.' '.$sn_usu.'&fecha='.$fecha.'&hora='.$hora.'&link='.$meet_link.'&tipo_terapia='.$tipo_terapia.'&modalidad='.$tipo_atencion.'&profesional='.$id_profesional.'&corpro='.$cor_usu_profesional);
                exit();
            }
        } else {
            $encryptedStatus = simpleEncrypt('error_insert', '2020');
            header('Location: citas_tmrr?sta=' . urlencode($encryptedStatus));
            exit();
        }
    }
}

$stmt->close();
$conn->close();
?>