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

$correo = $_SESSION['cor_usu'];
$nombre = $_SESSION['pn_usu']." ".$_SESSION['pa_usu'];

$id_usuario = $_SESSION['id'];

$codigo_codificado_url = $_GET['codrp'];
          
// Decodificar la URL
$codigo_decodificado_base64 = urldecode($codigo_codificado_url);

// Decodificar el código Base64
$codigo_decodificado = base64_decode($codigo_decodificado_base64);

// Aplicar XOR para recuperar el código original
function xorEncryptDecrypt($input, $key) {
    $output = '';
    for ($i = 0; $i < strlen($input); $i++) {
        $output .= chr(ord($input[$i]) ^ $key);
    }
    return $output;
}

// El código recuperado
$codigo_original = xorEncryptDecrypt($codigo_decodificado, 2020);

if ($_SERVER["REQUEST_METHOD"] == "POST") {
  $codigo = $_POST["codigo"];
  $password = $_POST["password"];
  $confirmPassword = $_POST["confirm-password"];
  $error_message = "";

  // Validar si el código es correcto
  if ($codigo !== $codigo_original) {
      $error_message = "El código no coincide.";
  } else {
      // Verificar si las contraseñas coinciden
      if ($password !== $confirmPassword) {
          $error_message = "Las contraseñas no coinciden.";
      } else {
          $password_escaped = mysqli_real_escape_string($conn, $password);
          $sql = "UPDATE usuarios SET clave_sys = '$password_escaped' WHERE id = ?";
          $stmt = $conn->prepare($sql);
          $stmt->bind_param("i", $id_usuario);
          if ($stmt->execute()) {
            if (!empty($_SESSION["id"])) {
                session_destroy();
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
            $mail->CharSet = 'UTF-8';
            $mail->isHTML(true);
            $mail->Subject = 'Cambio de contraseña exitoso - Sana Mente';
            $mail->Body = '
            <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
            <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
            <head>
              <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
              <meta name="viewport" content="width=device-width, initial-scale=1.0">
              <meta name="x-apple-disable-message-reformatting">
              <meta http-equiv="X-UA-Compatible" content="IE=edge">
              <title>Cambio de contraseña exitoso</title>
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
                <img src="https://cdn.templates.unlayer.com/assets/1593141680866-reset.png" alt="Icono Confirmación" style="width: 50px; height: auto; margin-bottom: 20px;" />
                <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Cambio de contraseña exitoso</h1>
              </div>

              <!-- Contenido principal -->
              <div style="text-align: center; padding: 30px 20px; background-color: #ffffff;">
                <p style="font-size: 16px; color: #666666;">Tu contraseña ha sido actualizada correctamente.</p>
                <p style="font-size: 16px; color: #666666;">Ahora puedes acceder a tu cuenta con tu nueva contraseña.</p>
                <p style="font-size: 14px; color: #888888; margin-top: 20px;">Si no realizaste este cambio, por favor contáctanos de inmediato.</p>
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
            // Configuración del texto alternativo
            $mail->AltBody = 'Tu contraseña ha sido actualizada correctamente. Si no realizaste este cambio, por favor contáctanos de inmediato: Teléfono 321 419 3875, Correo: citas@saludmentalsanamente.com.co.';
            //send the message, check for errors
            if (!$mail->send()) {
                echo 'Mailer Error: ' . $mail->ErrorInfo;
            } else {
              // Redirigir con los valores cifrados
              header("location: login?cnts=y");
              exit;
            }

          } else {
              echo "Error al actualizar la contraseña: " . $stmt->error;
          }
          $stmt->close();
          $conn->close();
      }
  }
}else{

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
  $mail->CharSet = 'UTF-8';
  $mail->isHTML(true);
  $mail->Subject = 'Restauración de contraseña - Sana Mente';
  $mail->Body = '
  <!DOCTYPE HTML PUBLIC "-//W3C//DTD XHTML 1.0 Transitional //EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
  <html xmlns="http://www.w3.org/1999/xhtml" xmlns:v="urn:schemas-microsoft-com:vml" xmlns:o="urn:schemas-microsoft-com:office:office">
  <head>
    <meta http-equiv="Content-Type" content="text/html; charset=UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="x-apple-disable-message-reformatting">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>Restauración de contraseña</title>
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
      <img src="https://cdn.templates.unlayer.com/assets/1593141680866-reset.png" alt="Icono Restauración" style="width: 50px; height: auto; margin-bottom: 20px;" />
      <h1 style="color: #ffffff; font-size: 24px; margin: 0;">Restauración de contraseña</h1>
    </div>

    <!-- Contenido principal -->
    <div style="text-align: center; padding: 30px 20px; background-color: #ffffff;">
      <p style="font-size: 16px; color: #666666;">Hemos recibido una solicitud para restaurar la contraseña de tu cuenta en Sana Mente.</p>
      <p style="font-size: 16px; color: #666666;">Para restaurar tu contraseña, ingresa el siguiente código en el formulario de restauración:</p>
      <p style="font-size: 24px; font-weight: bold; background: #2cbcd4; color: white; padding: 10px; border-radius: 5px; display: inline-block;">' . $codigo_original . '</p>
      <p style="font-size: 14px; color: #888888; margin-top: 20px;">Si no realizaste esta solicitud, ignora este mensaje y no compartas el código con nadie.</p>
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
  $mail->AltBody = 'Hemos recibido una solicitud para restaurar tu contraseña. El código es: ' . $codigo_original . '. Si no realizaste esta solicitud, ignora este mensaje.';

  //send the message, check for errors
  if (!$mail->send()) {
      echo 'Mailer Error: ' . $mail->ErrorInfo;
  } else {
      echo 'Message sent!';
  }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Cambiar Contraseña</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css" rel="stylesheet">
  <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-94034622-3');
</script>
<!-- /END GA --></head>

<body>
  <div id="app">
  <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="login-brand">
              <img src="assets/img/stisla-fill.svg" alt="logo" width="100" class="shadow-light rounded-circle">
            </div>

            <div class="card card-primary">
              <div class="card-header"><h4>Restablecer Contraseña</h4></div>

              <div class="card-body">
                <p class="text-muted">Hemos enviado un código de confirmación a tu correo registrado.</p>

                <form method="POST">
                  <div class="form-group">
                    <label for="codigo">Código</label>
                    <input id="codigo" type="text" class="form-control" name="codigo" tabindex="1" required autofocus>
                  </div>

                  <div class="form-group">
                    <label for="password">Nueva contraseña</label>
                    <input id="password" type="password" class="form-control pwstrength" name="password" tabindex="2" required>
                  </div>

                  <div class="form-group">
                    <label for="password-confirm">Confirmar contraseña</label>
                    <input id="password-confirm" type="password" class="form-control" name="confirm-password" tabindex="3" required>
                  </div>

                  <div class="form-group">
                    <button type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4">
                      Ingrese la contraseña
                    </button>
                  </div>
                </form>
                <!-- Mostrar mensaje de error o éxito -->
                <?php if (!empty($error_message)): ?>
                <div id="message" class="alert <?php echo $error_message === 'Contraseña cambiada exitosamente.' ? 'alert-success' : 'alert-danger'; ?>">
                <?php echo $error_message; ?>
                </div>
                <?php endif; ?>

                <style>
                  #message {
                    position: fixed; /* Lo fija en la pantalla */
                    top: 10px;       /* Distancia desde la parte superior */
                    right: 10px;     /* Distancia desde la parte derecha */
                    z-index: 9999;   /* Asegura que el mensaje esté encima de otros elementos */
                    max-width: 300px; /* Limita el ancho del mensaje */
                    padding: 10px;    /* Espaciado interno */
                    border-radius: 5px; /* Esquinas redondeadas */
                    
                    /* Manteniendo tu estilo actual */
                    opacity: 1;
                    transition: opacity 1s ease-out; /* La transición de opacidad durará 1 segundo */
                  }
                </style>
              </div>
            </div>
            <div class="simple-footer">
              Copyright &copy; Sanamente 2025
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>

  <!-- General JS Scripts -->

<!-- Cargar jQuery antes de Toastr -->
<script src="assets/modules/jquery.min.js"></script>

<!-- Cargar Toastr después de jQuery -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>

<!-- Cargar el resto de scripts -->
<script src="assets/modules/popper.js"></script>
<script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
<script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
<script src="assets/modules/moment.min.js"></script>
<script src="assets/js/stisla.js"></script>
<script>
  // Obtener los elementos de las contraseñas
  const passwordInput = document.getElementById('password');
  const confirmPasswordInput = document.getElementById('password-confirm');
  const submitButton = document.querySelector('button[type="submit"]');

  // Función para verificar si las contraseñas coinciden
  function checkPasswordsMatch() {
    if (passwordInput.value === confirmPasswordInput.value) {
      // Si las contraseñas coinciden
      submitButton.disabled = false;
      submitButton.textContent = 'Cambiar Contraseña'; // Texto del botón cambia
      confirmPasswordInput.setCustomValidity(''); // Elimina cualquier mensaje de error
    } else {
      // Si las contraseñas no coinciden
      submitButton.disabled = true;
      submitButton.textContent = 'Contraseñas no coinciden'; // Texto del botón cambia
      confirmPasswordInput.setCustomValidity('Las contraseñas no coinciden');
    }
  }

  // Agregar los eventos a los campos de contraseña
  passwordInput.addEventListener('input', checkPasswordsMatch);
  confirmPasswordInput.addEventListener('input', checkPasswordsMatch);
  
  window.onload = function() {
    var messageDiv = document.getElementById("message");
    if (messageDiv) {
      setTimeout(function() {
        messageDiv.style.opacity = 0; // Comienza a desvanecerse
      }, 3000); // 3000 ms = 3 segundos
    }
  };
</script>
<!-- Tu código para mostrar los mensajes de Toastr -->
</body>
</html>