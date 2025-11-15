<?php
require_once '../resset_pass/includes/config1.php';
require_once '../resset_pass/includes/class-db1.php';
require_once '../vendor/autoload.php';

session_start();
if (empty($_SESSION["id"])) {
  header("location: ../login");
}
include "../../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

function simpleDecrypt($encryptedText, $key) {
    $decodedText = base64_decode($encryptedText); // Decodificar desde base64
    
    $output = '';
    for ($i = 0; $i < strlen($decodedText); $i++) {
        $output .= chr(ord($decodedText[$i]) ^ ord($key[$i % strlen($key)]));
    }
    
    return $output;
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
require_once '../resset_pass/includes/class-db1.php';

$dasta = $_GET['dasta'];
$dasta = simpleDecrypt($dasta, '2020');
$correo = $_GET['mail'];
$corpro = $_GET['corpro'];
$nombre = $_GET['name'];
$nombre = ucwords(strtolower($nombre));
$fecha = $_GET['fecha'];
$fecha = date('d-m-Y', strtotime($fecha));
$hora = $_GET['hora'];
$hora = date("g:i A", strtotime(substr($hora, 0, 2) . ':' . substr($hora, 2, 2) . ':' . substr($hora, 4, 2)));
$link = $_GET['link'];
$profesional_id = $_GET['profesional'];

$sqlp = "SELECT pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE id = ?";
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

  $nombre_completo = capitalizar_palabras_utf8($nombre_completo);

  // Guardar en la variable $profesional
  $profesional = $nombre_completo;
} else {
    $profesional = "Profesional no encontrado";
}

$tipo_terapia = $_GET['tipo_terapia'];
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
$modalidad = $_GET['modalidad'];
if($modalidad === "1"){
  $modalidad = "Presencial";
}else if($modalidad === "2"){
  $modalidad = "virtual";
}else{
  $modalidad = "Desconocida";
}

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

$email = 'sanamente@saludmentalsanamente.com.co'; // the email used to register google app
$clientId = '413760559356-6hqarcvp7l1a8ds16tmu886853b18d94.apps.googleusercontent.com';
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
$mail->Subject = 'Reagendamiento de cita - Sana Mente';
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
    <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Tu cita está reagendada! 📅💖</h1>
  </div>

  <!-- Contenido principal -->
  <div style="text-align: center; padding: 30px 20px; background-color: #ffffff;">
    <p style="font-size: 16px; color: #666666;">🌟 Tu sesión ha sido reagendada con éxito. Aquí están todos los detalles para que no te pierdas nada:</p>
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
} else {
  header('Location: ../citas_tmrr?' . $dasta);
  exit();
}