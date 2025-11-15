<?php
require_once __DIR__ . '/../../resset_pass/includes/config.php';
require_once __DIR__ . '/../../resset_pass/includes/class-db.php';
require_once __DIR__ . '/../../vendor/autoload.php';

include __DIR__ . "/../../../../conexionsm.php";

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;


date_default_timezone_set('America/Bogota'); // Establecer zona horaria de Colombia

$fechaActual = date('Y-m-d', strtotime('+3 days')); // Fecha actual + 2 días
$minuto = date('i');
$hora = date('H');
if ($minuto >= 30) {
    $hora = (int)$hora + 1; // Sube a la siguiente hora si pasa de 30 minutos
}
$horaCerrada = str_pad($hora, 2, '0', STR_PAD_LEFT) . ':00:00';

$sql = "SELECT fecha, hora, link_ingreso, psi, userID, tipo, `site`
        FROM sessions 
        WHERE fecha = ? AND estado < 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fechaActual);
$stmt->execute();
$result = $stmt->get_result();



// Función personalizada para poner en mayúscula la primera letra de cada palabra (UTF-8 safe)
function capitalizar_palabras_utf8($texto) {
    $palabras = explode(' ', $texto);
    foreach ($palabras as &$palabra) {
        $primera = mb_substr($palabra, 0, 1, 'UTF-8');
        $resto = mb_substr($palabra, 1, null, 'UTF-8');
        $palabra = mb_strtoupper($primera, 'UTF-8') . $resto;
    }
    return implode(' ', $palabras);
}

while ($row = $result->fetch_assoc()) {
    $fecha = $row['fecha'];
    $fecha = date('d-m-Y', strtotime($fecha));
    $hora = $row['hora'];
    $hora = date("h:i A", strtotime($hora));
    $link = $row['link_ingreso'];
    $profesional_id = $row['psi'];

    
    $tipo_terapia = $row['tipo'];
    $tipos_terapia = [
        "1" => "Terapia Individual",
        "2" => "Terapia de Pareja",
        "5" => "Terapia de Familia",
        "6" => "Terapia Psiquiátrica",
        "7" => "Valoración",
        "8" => "Terapia Nutrición",
        "9" => "Terapia Infantil"
    ];
    $tipo_terapia = isset($tipos_terapia[$tipo_terapia]) ? $tipos_terapia[$tipo_terapia] : "Tipo de terapia desconocido";
    $modalidad = $row['site'];
    if($modalidad === 1){
      $modalidad = "Presencial";
    }else if($modalidad === 2){
      $modalidad = "virtual";
    }else{
      $modalidad = "Desconocida";
    }


    $sqlp = "SELECT pn_usu, sn_usu, pa_usu, sa_usu, cor_usu FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sqlp);
    $stmt->bind_param("i", $profesional_id);
    $stmt->execute();
    $resultp = $stmt->get_result();

    if ($rowp = $resultp->fetch_assoc()) {
      // Concatenar los nombres y apellidos
      $nombre_completo = trim($rowp['pn_usu'] . ' ' . $rowp['sn_usu'] . ' ' . $rowp['pa_usu'] . ' ' . $rowp['sa_usu']);

      // Quitar espacios duplicados
      $nombre_completo = preg_replace('/\s+/', ' ', $nombre_completo);

      // Convertir todo a minúsculas correctamente (UTF-8 seguro)
      $nombre_completo = mb_strtolower($nombre_completo, 'UTF-8');

      $nombre_completo = capitalizar_palabras_utf8($nombre_completo);

      // Guardar en la variable $profesional
      $profesional = $nombre_completo;
    } else {
        $profesional = "Profesional no encontrado";
    }

    $corpro = $rowp['cor_usu'];

    $userID = $row['userID'];


    $sqlu = "SELECT pn_usu, sn_usu, pa_usu, sa_usu, cor_usu FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sqlu);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $resultu = $stmt->get_result();

    if ($rowu = $resultu->fetch_assoc()) {
      // Concatenar los nombres y apellidos
      $nombre_completou = trim($rowu['pn_usu'] . ' ' . $rowu['sn_usu']);

      // Quitar espacios duplicados
      $nombre_completou = preg_replace('/\s+/', ' ', $nombre_completou);

      // Convertir todo a minúsculas correctamente (UTF-8 seguro)
      $nombre_completou = mb_strtolower($nombre_completou, 'UTF-8');

      $nombre_completou = capitalizar_palabras_utf8($nombre_completou);

      // Guardar en la variable $profesional
      $nombre = $nombre_completou;
    } else {
        $nombre = "Profesional no encontrado";
    }

    $correo = $rowp['cor_usu'];

    
    $mail = new PHPMailer();
    $mail->isSMTP();
    $mail->Host = 'smtp.gmail.com';
    $mail->Port = 465;

    //Set the encryption mechanism to use:
    // - SMTPS (implicit TLS on port 465) or
    // - STARTTLS (explicit TLS on port 587)
    $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;

    $mail->SMTPAuth = true;
    $mail->AuthType = 'XOAUTH2';

    $email = 'Angie.gonzalez@saludmentalsanamente.com.co'; // the email used to register google app
    $clientId = '160003501197-dd0etlfjvk6mrdb6go0134gaijdpq938.apps.googleusercontent.com';
    $clientSecret = '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';

    $db = new DB();
    $refreshToken = $db->get_refresh_token();

    //Create a new OAuth2 provider instance
    $provider = new Google(
        [
            'clientId' => $clientId,
            'clientSecret' => $clientSecret,
        ]
    );

    //Pass the OAuth provider instance to PHPMailer
    $mail->setOAuth(
        new OAuth(
            [
                'provider' => $provider,
                'clientId' => $clientId,
                'clientSecret' => $clientSecret,
                'refreshToken' => $refreshToken,
                'userName' => $email,
            ]
        )
    );

    $mail->setFrom($email, 'Sana Mente');
    $mail->addAddress($correo, $nombre);
    $mail->addAddress($corpro, $profesional);
    $mail->CharSet = 'UTF-8';
    $mail->isHTML(true);
    $mail->Subject = 'Recordatorio de cita - Sana Mente';
    $mail->Body = '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
      <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <meta name="x-apple-disable-message-reformatting">
      <meta http-equiv="X-UA-Compatible" content="IE=edge">
      <title>Saludo de Bienvenida</title>
      <style type="text/css">
        /* Estilos responsivos y generales */
        body {
          margin: 0;
          padding: 0;
          -webkit-text-size-adjust: 100%;
          background-color: #f9f9f9;
          color: #000000;
        }
        table, td, tr {
          border-collapse: collapse;
          vertical-align: top;
        }
        a[x-apple-data-detectors=true] {
          color: inherit !important;
          text-decoration: none !important;
        }
        @media only screen and (min-width: 620px) {
          .u-row {
            width: 600px !important;
          }
          .u-row .u-col {
            vertical-align: top;
          }
        }
        @media only screen and (max-width: 620px) {
          .u-row-container {
            max-width: 100% !important;
            padding-left: 0px !important;
            padding-right: 0px !important;
          }
          .u-row {
            width: 100% !important;
          }
          .u-row .u-col {
            display: block !important;
            width: 100% !important;
            min-width: 320px !important;
            max-width: 100% !important;
          }
        }
      </style>
    </head>
    <body style="margin: 0; padding: 0; background-color: #f9f9f9; color: #000000;">

      <!-- Contenedor del logo -->
      <div style="text-align: center; padding: 20px 0; background-color: #ffffff;">
        <img src="https://saludmentalsanamente.com.co/assets/images/icosn.png" alt="Logo" style="width: 300px; height: auto;" />
      </div>

      <!-- Contenedor de encabezado con fondo azul -->
      <div style="text-align: center; padding: 40px 20px; background-color: #2cbcd4;">
        <div style="font-size: 48px; margin-bottom: 20px; color:white;">¡Hola ' . $nombre . ',</div>
        <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Queremos hacer un recordatorio de tu cita! 📅💖</h1>
      </div>

      <!-- Contenido principal -->
      <div style="text-align: center; padding: 30px 20px; background-color: #ffffff;">
        <p style="font-size: 16px; color: #666666;">🌟 Tu sesión está esperandote, aquí están todos los detalles para que no te pierdas nada:</p>
        <p style="font-size: 16px; color: #666666;">📅<strong> Fecha:</strong> ' . $fecha . '</p>
        <p style="font-size: 16px; color: #666666;">⏰<strong> Hora:</strong> ' . $hora . '</p>
        <p style="font-size: 16px; color: #666666;">👩‍⚕️<strong> Profesional:</strong> ' . $profesional . '</p>
        <p style="font-size: 16px; color: #666666;">📍<strong> Modalidad:</strong> ' . $modalidad . '</p>
        <br>
        <p style="font-size: 16px; color: #666666;"><strong>¿Quieres ver o gestionar tus citas?</strong></p>
        <p style="font-size: 16px; color: #666666;">Puedes revisar todas tus sesiones agendadas en cualquier momento desde nuestra plataforma:</p>
        <p style="font-size: 16px; color: #666666;">👉 <a href="https://saludmentalsanamente.com.co/t_zone/dist/login">Haz clic aquí</a> 👈</p>
        <br>
        
        <p style="font-size: 16px; color: #666666;"><strong>¿Quieres ingresar directamente a tu cita?</strong></p>
        <p style="font-size: 24px; font-weight: bold; background: #2cbcd4; color: white; padding: 10px; border-radius: 5px; display: inline-block;"><a href="' . $link . '" style="color:white">Ingresa aquí a tu sesión</a></p>
        <p style="font-size: 14px; color: #888888; margin-top: 20px;">Recuerda que este espacio es solo para ti, un momento para cuidarte y sentirte mejor. 💆‍♀️💖 Si necesitas cambiar algo, avísanos con tiempo. ¡Estamos aquí para ayudarte!</p>
        <p style="font-size: 16px; color: #666666;">Nos vemos pronto, ¡que tengas un día increíble! 🌈✨</p>
        <p style="font-size: 16px; color: #666666;">Con cariño,</p>
        <p style="font-size: 16px; color: #666666;"><strong>Sana Mente</strong></p>
      </div>

      <!-- Sección de contacto -->
      <div style="background-color: #2cbcd4; padding: 20px; color: #ffffff;">
        <h3 style="margin: 0; font-size: 18px;">Contacto</h3>
        <p style="margin: 5px 0;">Teléfono: 321 419 3875</p>
        <p style="margin: 5px 0;">Correo: <a href="mailto:citas@saludmentalsanamente.com.co" style="color: #ffffff; text-decoration: none;">citas@saludmentalsanamente.com.co</a></p>
      </div>

      <!-- Pie de página -->
      <div style="text-align: center; padding: 20px; background-color: #1c103b; color: #ffffff;">
        <p style="margin: 5px 0;">Visítanos en: <a href="https://www.saludmentalsanamente.com.co" style="color: #2cbcd4; text-decoration: none;">www.saludmentalsanamente.com.co</a></p>
        <p style="margin: 5px 0; font-size: 12px;">© Sana Mente. Todos los derechos reservados.</p>
      </div>

    </body>
    </html>
    ';
    $mail->AltBody = 'Saludo de Bienvenida - Sana Mente';

    //send the message, check for errors
    if (!$mail->send()) {
        echo 'Mailer Error: ' . $mail->ErrorInfo;
    }

}
