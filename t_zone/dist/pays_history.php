<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
}
if($_SESSION['numdoc'] !== "1014273279" && $_SESSION['numdoc'] !== "1000693019"){
    header("location: login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Biblioteca</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <!-- Toastr CSS -->
  <!-- Toastr CSS desde un CDN personalizado -->
  
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
  <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">


<!-- Start GA -->
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
      
      <?php include "nav.php"; ?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Administración</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="#">Administración</a></div>
              <div class="breadcrumb-item"><a href="#">Comisiones</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Comisiones</h2>
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-md">
                        <tr>
                          <th style="text-align: center;">#</th>
                          <th style="text-align: center;">ID Profesional</th>
                          <th style="text-align: center;">Profesional</th>
                          <th style="text-align: center;">Historia</th>
                          <th style="text-align: center;">ID Paciente</th>
                          <th style="text-align: center;">Paciente</th>
                          <th style="text-align: center;">Fecha Consulta</th>
                          <th style="text-align: center;">Valor de comisión</th>
                          <th style="text-align: center;">Estado</th>
                        </tr>
                        <?php
                        include "../../conexionsm.php";

                        // Número de registros por página
                        $registrosPorPagina = 10;

                        // Página actual
                        $paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        if ($paginaActual < 1) $paginaActual = 1;

                        // Calcula el límite y el offset para la consulta
                        $offset = ($paginaActual - 1) * $registrosPorPagina;

                        $filtro = $_SESSION['id'];

                        // Consulta SQL para contar el total de registros
                        $sqlCount = "SELECT COUNT(*) AS total FROM sessions";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "SELECT * FROM sessions LIMIT $offset, $registrosPorPagina";
                        $result = $conn->query($sql);

                        // Inicializamos un contador para el número de fila
                        $count = $offset + 1;

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {

                            $psisql = $row['psi'];
                            $sqlpsi = "SELECT numdoc, pn_usu, pa_usu FROM usuarios WHERE id = $psisql";
                            $result1 = $conn->query($sqlpsi);
                            $row1 = $result1->fetch_assoc();

                            $pacsql = $row['userID'];
                            $sqlpac = "SELECT numdoc, pn_usu, pa_usu, hiscli FROM usuarios WHERE id = $pacsql";
                            $result2 = $conn->query($sqlpac);
                            $row2 = $result2->fetch_assoc();

                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            echo '<td style="text-align: center;">' . $row1['numdoc'] . '</td>';
                            echo '<td style="text-align: center;">' . $row1['pn_usu'] . ' ' . $row1['pa_usu'] . '</td>';
                            echo '<td style="text-align: center;">' . $row2['hiscli'] . '</td>';
                            echo '<td style="text-align: center;">' . $row2['numdoc'] . '</td>';
                            echo '<td style="text-align: center;">' . $row2['pn_usu'] . ' ' . $row2['pa_usu'] . '</td>';
                            echo '<td style="text-align: center;">' . $row['fecha'] . '</td>';
                            echo '<td style="text-align: center;">$ ' . number_format($row['ingresoRT'], 0, ',', '.') . '</td>';
                            date_default_timezone_set('America/Bogota');
                            $fechaActual = date('Y-m-d');

                            // Obtener la fecha de la base de datos
                            $fechaComision = $row['fecha'];

                            // Determinar el texto y el color de fondo
                            $texto = "";
                            $colorFondo = "";

                            switch ($row['estado']) {
                              case 1:
                                  $texto = "Consulta pendiente de pago";
                                  $colorFondo = "#F4B400"; // Amarillo oscuro
                                  break;
                              case 2:
                                  // Compara las fechas
                                  if ($fechaComision < $fechaActual) {
                                      $texto = "Pendiente Confirmación";
                                      $colorFondo = "#17A2B8"; // Azul cielo intenso
                                  } elseif ($fechaComision == $fechaActual) {
                                      $texto = "Consulta en Proceso";
                                      $colorFondo = "#FF5722"; // Naranja oscuro
                                  } else {
                                      $texto = "Consulta Programada";
                                      $colorFondo = "#007BFF"; // Azul fuerte
                                  }
                                  break;
                              case 3:
                                  $texto = "Pago sin Cita";
                                  $colorFondo = "#6F42C1"; // Morado intenso
                                  break;
                              case 5:
                                  $texto = "Cancelada";
                                  $colorFondo = "#DC3545"; // Rojo fuerte
                                  break;
                              case 6:
                                  $texto = "Comisión en Validación";
                                  $colorFondo = "#FFC107"; // Mostaza
                                  break;
                              case 7:
                                  $texto = "Comisión por pagar";
                                  $colorFondo = "#20C997"; // Verde intenso
                                  break;
                              case 8:
                                  $texto = "Comisión Pagada";
                                  $colorFondo = "#155724"; // Verde oscuro
                                  break;
                              default:
                                  $texto = "Estado desconocido";
                                  $colorFondo = "#6C757D"; // Gris oscuro
                                  break;
                          }                          

                            echo '<td style="text-align: center; color:white; background-color: ' . $colorFondo . ';"><strong>' . $texto . '</strong></td>';
                            echo '</tr>';

                            $count++;
                        }
                        ?>
                      </table>
                    </div>
                  </div>
                  <div class="card-footer text-right">
                    <nav class="d-inline-block">
                      <ul class="pagination mb-0">
                        <!-- Botón de página anterior -->
                        <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                          <a class="page-link" href="?page=<?= $paginaActual - 1 ?>"><i class="fas fa-chevron-left"></i></a>
                        </li>

                        <!-- Enlaces de paginación -->
                        <?php for ($i = 1; $i <= $totalPaginas; $i++): ?>
                          <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                          </li>
                        <?php endfor; ?>

                        <!-- Botón de página siguiente -->
                        <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                          <a class="page-link" href="?page=<?= $paginaActual + 1 ?>"><i class="fas fa-chevron-right"></i></a>
                        </li>
                      </ul>
                    </nav>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>

      <?php include "footer.php"; ?>
    </div>
  </div>
  
  <!-- General JS Scripts -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>
  
  <!-- JS Libraies -->
  <script src="assets/modules/prism/prism.js"></script>

  <!-- Page Specific JS File -->
  <script src="assets/js/page/bootstrap-modal.js"></script>
  <!-- JS Libraies -->
  <script src="assets/modules/jquery-ui/jquery-ui.min.js"></script>

  <!-- Page Specific JS File -->
  <script src="assets/js/page/components-table.js"></script>
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>

  <!-- Toastr JS -->
  <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.js"></script>
  <script>

  // Función que convierte el texto de todos los inputs de la página a mayúsculas
  function convertirMayusculas() {
      // Obtener todos los elementos de formulario de la página
      const formularios = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"], textarea');
      
      formularios.forEach(input => {
          // Agregar un evento 'input' que convierta el valor del campo a mayúsculas
          input.addEventListener('input', function() {
              input.value = input.value.toUpperCase();
          });
      });
  }

  // Llamar a la función para activar el comportamiento
  document.addEventListener('DOMContentLoaded', convertirMayusculas);






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
      const encryptedStatus = urlParams.get('sta');

      if (encryptedStatus) {
          const status = simpleDecrypt(encryptedStatus, '2020'); // Descifra usando la clave

          if (status === 'success_create') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('El documento se creó correctamente.');
          } else if (status === 'error_create') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Hubo un error al crear el registro.');
          }else if (status === 'success_update') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Solicitud exitosa');
          } else if (status === 'error_update') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Hubo un error al actualizar el registro.');
          } else if (status === 'no_changes') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('No se realizó ningún cambio.');
          }
      }
    });

  </script>
  <!-- Modal -->


</body>
</html>
