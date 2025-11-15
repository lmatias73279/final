<?php

session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
include "../../conexionsm.php";

actualizarSesion($conn);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

require_once 'vendor/autoload.php';
require_once 'class-db.php';


  if (!empty($_SESSION["id"])) {
      session_destroy();
  }
  $mail = new PHPMailer();
  $mail->isSMTP();
  $mail->Host = 'smtp.gmail.com';
  $mail->Port = 465;
  
  // - STARTTLS (explicit TLS on port 587)
  $mail->SMTPSecure = PHPMailer::ENCRYPTION_SMTPS;
  
  $mail->SMTPAuth = true;
  $mail->AuthType = 'XOAUTH2';
  
  $email = 'citas@saludmentalsanamente.com.co'; // the email used to register google app
  $clientId = '478499622206-pg853ang5jmkaduv9im5d56t0vorf3ei.apps.googleusercontent.com';
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
  $fecha = "2025-02-09";
  $hora = "13:00:00";
  $profesional = "Maria Fuentes";
  $mail->setFrom($email, 'Sana Mente');
  $mail->addAddress('lmatiasmont@gmail.com', 'felipe matias');
  $mail->CharSet = 'UTF-8';
  $mail->isHTML(true);
  $mail->Subject = 'Recordatorio de Sesión - Sana Mente';
  $mail->Body = '
    <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
    <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <meta name="x-apple-disable-message-reformatting">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <title>Recordatorio de Sesión - SanaMente</title>
        <style type="text/css">
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
        <img src="https://cdn.templates.unlayer.com/assets/1593141680866-reset.png" alt="Icono Calendario" style="width: 50px; height: auto; margin-bottom: 20px;" />
        <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Recordatorio de Sesión</h1>
        </div>

        <!-- Contenido principal -->
        <div style="text-align: center; padding: 30px 20px; background-color: #ffffff;">
        <p style="font-size: 16px; color: #666666;">Este es un recordatorio de tu próxima sesión en SanaMente.</p>
        <p style="font-size: 16px; color: #666666;"><strong>Fecha:</strong> ' . $fecha . '</p>
        <p style="font-size: 16px; color: #666666;"><strong>Hora:</strong> ' . $hora . '</p>
        <p style="font-size: 16px; color: #666666;"><strong>Profesional:</strong> ' . $profesional . '</p>
        <p style="font-size: 14px; color: #888888; margin-top: 20px;">Si no puedes asistir, por favor notifícanos con anticipación.</p>
        </div>

        <!-- Sección de contacto -->
        <div style="background-color: #2cbcd4; padding: 20px; color: #ffffff;">
        <h3 style="margin: 0; font-size: 18px;">Contacto</h3>
        <p style="margin: 5px 0;">Teléfono: 301 714 0134</p>
        <p style="margin: 5px 0;">Correo: <a href="mailto:correo@saludmentalsanamente.com.co" style="color: #ffffff; text-decoration: none;">correo@saludmentalsanamente.com.co</a></p>
        </div>

        <!-- Pie de página -->
        <div style="text-align: center; padding: 20px; background-color: #1c103b; color: #ffffff;">
        <p style="margin: 5px 0;">Visítanos en: <a href="https://www.saludmentalsanamente.com.co" style="color: #2cbcd4; text-decoration: none;">www.saludmentalsanamente.com.co</a></p>
        <p style="margin: 5px 0; font-size: 12px;">© SanaMente. Todos los derechos reservados.</p>
        </div>

    </body>
    </html>
    ';

  // Configuración del texto alternativo
  $mail->AltBody = 'Tu contraseña ha sido actualizada correctamente. Si no realizaste este cambio, por favor contáctanos de inmediato: Teléfono 301 714 0134, Correo: correo@saludmentalsanamente.com.co.';
  //send the message, check for errors
  if (!$mail->send()) {
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  } else {
    // Redirigir con los valores cifrados
    header("location: login?cnts=y");
    exit;
  }
$stmt->close();
$conn->close();
