<?php
require_once '../resset_pass/includes/config.php';
require_once '../resset_pass/includes/class-db.php';
require_once '../vendor/autoload.php';

session_start();
if (empty($_SESSION["id"])) {
  header("location: ../login");
}
include "../../../conexionsm.php";

$fullname = $_GET['name'];
$fullname = ucwords(strtolower($fullname));

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}


// session_update.php
function actualizarSesion($conn) {
    if (isset($_SESSION["id"])) {
        // Consulta la base de datos para obtener los datos m谩s recientes del usuario
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE ID = ? LIMIT 1");
        $stmt->bind_param("i", $_SESSION["id"]);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($datos = $result->fetch_object()) {
            // Actualiza los datos de la sesi贸n con los valores actuales de la base de datos
            $_SESSION["pais"] = $datos->pais;
            $_SESSION["tipdoc"] = $datos->tipdoc;
            $_SESSION["numdoc"] = $datos->numdoc;
            $_SESSION["pn_usu"] = $datos->pn_usu;
            $_SESSION["sn_usu"] = $datos->sn_usu;
            $_SESSION["pa_usu"] = $datos->pa_usu;
            $_SESSION["sa_usu"] = $datos->sa_usu;
            $_SESSION["permiso"] = $datos->permiso;
            $_SESSION["foto"] = $datos->foto;
            $_SESSION["bold"] = $datos->bold;
            $_SESSION["profesional_asignado"] = $datos->profesional_asignado;
            $_SESSION["permiso_blog"] = $datos->permiso_blog;
            $_SESSION["permiso_biblioteca"] = $datos->permiso_biblioteca;
            $_SESSION["permiso_citas"] = $datos->permiso_citas;
            $_SESSION["permiso_promociones"] = $datos->permiso_promociones;
            $_SESSION["permiso_gastos"] = $datos->permiso_gastos;
            $_SESSION["permiso_citas_pagos"] = $datos->permiso_citas_pagos;
        }
        
        $stmt->close();
    }
}
include "../../../conexionsm.php";

actualizarSesion($conn);
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\SMTP;
use PHPMailer\PHPMailer\OAuth;
use League\OAuth2\Client\Provider\Google;

require_once '../vendor/autoload.php';
require_once '../resset_pass/includes/class-db.php';

$correo = $_GET['correo'];

$id_usuario = $_SESSION['id'];

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
$mail->addAddress($correo, $fullname);
$mail->CharSet = 'UTF-8';
$mail->isHTML(true);
$mail->Subject = 'Saludo de Bienvenida - Sana Mente';
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
    <div style="font-size: 48px; margin-bottom: 20px;">🎉👏🤝</div>
    <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Hola ' . $fullname . '!</h1>
  </div>

  <!-- Contenido principal -->
  <div style="text-align: center; padding: 30px 20px; background-color: #ffffff;">
    <p style="font-size: 16px; color: #666666;">Antes de empezar quiero decirte algo importante:</p>
    <p style="font-size: 16px; color: #666666;">Has hecho algo profundamente valiente. Buscar ayuda no es rendirse, es reconocer que mereces sentirte mejor. Y aquí, no vas a caminar a solas.</p>
    <p style="font-size: 16px; color: #666666;">En <strong>Sana Mente</strong> creemos en el poder de la palabra que no juzga. En la fuerza de una presencia que acompaña. Y en el valor de cada historia, incluida la tuya.</p>
    <p style="font-size: 16px; color: #666666;">Tu espacio terapeutico no será perfecto. Pero será honesto. Seguro. Amoroso. Un lugar donde lo que sientes no será corregido, sino comprendido. Un lugar donde no tengas que ser fuerte todo el tiempo, porque también mereces descansar.</p>
    <p style="font-size: 16px; color: #666666;">Gracias por confiar en nosotr@s. Gracias por permitirnos acompañarte en este camino de autodescubrimiento.</p>
    <p style="font-size: 16px; color: #666666;">Estamos aquí para ti. Y nos sentimos honrad@s de caminar a tu lado.</p>
    <p style="font-size: 16px; color: #666666;">Con Cariño</p>
    <p style="font-size: 16px; color: #666666;">Angie Gonzalez - Lider de equipo</p>
    <br>
    <div style="font-size: 48px; margin-bottom: 20px;">🤗💖😊</div>
    <p style="font-size: 16px; color: #666666;">Bienvenid@ a tu proceso en Sana Mente</p>
    <p style="font-size: 24px; font-weight: bold; background: #2cbcd4; color: white; padding: 10px; border-radius: 5px; display: inline-block;"><a href="https://saludmentalsanamente.com.co/t_zone/dist/login" style="color: white;">Ingresa aquí</a></p>
    <p style="font-size: 14px; color: #888888; margin-top: 20px;">También queremos contarte que, desde ahora, tienes acceso a nuestro portal web. Allí podrás ver tus citas y explorar recursos pensados especialmente para acompañarte en tu proceso. En Sana Mente trabajamos cada día para que tu experiencia con nosotros sea cercana, sencilla y enriquecedora.</p>
    <p style="font-size: 16px; color: #666666;">Tu usuario es tu correo electrónico</p>
    <p style="font-size: 16px; color: #666666;">Tu contraseña es tu número de documento</p>
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
} else {
    $encryptedStatus = simpleEncrypt('success_create', '2020');
    header('Location: ../users_pac?sta=' . urlencode($encryptedStatus));
    exit();
}