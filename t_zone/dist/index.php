<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}

include "../../conexionsm.php";

$id = $_SESSION["id"];

// Incrementar visitas
$sql = "UPDATE usuarios
SET visit = COALESCE(visit, 0) + 1
WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

// Obtener el permiso del usuario desde la sesión
$permisoUsuario = $_SESSION['permiso'];

$popupImage = "";
$mostrarModal = false;

if ($permisoUsuario) {
    $sql = "SELECT img, pub FROM popups WHERE est = 0 ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $popupImage = $row['img'];
        $publicos = explode(',', $row['pub']); // Convertimos la cadena en un array

        // Verificamos si el permiso del usuario está en la lista de públicos permitidos
        if (in_array($permisoUsuario, $publicos)) {
            $mostrarModal = true;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Bienvenid@</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">

<!-- Start GA -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
<script>
  window.dataLayer = window.dataLayer || [];
  function gtag(){dataLayer.push(arguments);}
  gtag('js', new Date());

  gtag('config', 'UA-94034622-3');
</script>
<!-- /END GA --></head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>

      <?php include "nav.php"?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
        <div class="section-header" style="display: flex; justify-content: center; align-items: center; width: 100%;">
          <h1 style="color: #6f42c1;">Bienvenido/a a SANA MENTE</h1>
        </div>

          <div class="section-body">
            <h2 class="section-title">Tu Espacio para el Bienestar</h2>
            <p class="section-lead">
              Este sistema está diseñado para facilitar el acceso a servicios de apoyo emocional y bienestar, ya sea para pacientes, colaboradores o empresas. Aquí encontrarás herramientas, recursos y un espacio seguro para gestionar tus consultas y servicios.
            </p>
            <div class="card">
              <div class="card-header">
                <h4>¿Qué encontrarás aquí?</h4>
              </div>
              <div class="card-body">
                <p>
                  En el sistema de SANA MENTE, puedes programar citas, revisar información de bienestar, recibir asesoría y gestionar servicios de salud mental de manera fácil y segura. Nuestro objetivo es apoyarte en tu crecimiento personal y profesional, y contribuir al bienestar emocional en el entorno laboral.
                </p>
              </div>
              <div class="card-footer bg-whitesmoke">
                Tu bienestar es nuestra prioridad.
              </div>
            </div>
          </div>
        </section>
      </div>

      <?php include "footer.php"?>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
  <script>
        $(document).ready(function() {
        // Función de descifrado XOR con clave
        function simpleDecrypt(text, key) {
            const decodedText = atob(text);  // Decodificar base64
            let output = '';
            for (let i = 0; i < decodedText.length; i++) {
                output += String.fromCharCode(decodedText.charCodeAt(i) ^ key.charCodeAt(i % key.length));
            }
            return output;
        }

        // Obtiene el valor cifrado de 'sta' en la URL
        const urlParams = new URLSearchParams(window.location.search);
        const encryptedStatus = urlParams.get('f');

        if (encryptedStatus) {
            const status = simpleDecrypt(encryptedStatus, '2020'); // Descifra usando la clave
            
            if (status === 'ok') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Aceptación de políticas firmada correctamente');
            }
            if (status === 'ok_psicologo') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Consentimiento de psicología firmado correctamente');
            }
            if (status === 'ok_psiquiatra') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Consentimiento de psiquiatría firmado correctamente');
            }
            if (status === 'ok_adultos') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Consentimiento de adultos firmado correctamente');
            }
            if (status === 'ok_kids') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Consentimiento de niños firmado correctamente');
            }
            if (status === 'ok_pareja') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Consentimiento de pareja firmado correctamente');
            }
        }
      });
  </script>
  <!-- General JS Scripts -->

<!-- Modal de Popup -->
<?php if ($mostrarModal && !empty($popupImage)) : ?>
    <div class="modal fade" id="popupModal" tabindex="-1" role="dialog" aria-labelledby="popupModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content text-center">
                <div class="modal-header border-0 p-2">
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0 d-flex justify-content-center align-items-center">
                    <img src="<?php echo htmlspecialchars($popupImage); ?>" class="img-fluid" alt="PopUp"
                        style="max-width: 90vw; max-height: 90vh; width: auto; height: auto;">
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            $('#popupModal').modal('show'); // Muestra el modal automáticamente al cargar la página
        });
    </script>
<?php endif; ?>









  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>
  
  <!-- JS Libraies -->

  <!-- Page Specific JS File -->
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>