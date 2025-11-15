<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99 && $_SESSION['numdoc'] !== '1000693019'){
    header("location: login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Confirmaciones</title>

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
              <div class="breadcrumb-item"><a href="#">Confirmaciones</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Confirmaciones</h2>
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-md">
                        <tr>
                          <th style="text-align: center;">#</th>
                          <th style="text-align: center;">Historia Clinica</th>
                          <th style="text-align: center;">ID Paciente</th>
                          <th style="text-align: center;">Paciente</th>
                          <th style="text-align: center;">Fecha Consulta</th>
                          <th style="text-align: center;">Hora Consulta</th>
                          <th style="text-align: center;">Acciones</th>
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

                        // Establecer la zona horaria de Colombia
                        date_default_timezone_set('America/Bogota');

                        // Obtener la fecha y hora actual en Colombia
                        $fechaActual = date('Y-m-d');
                        $horaActual = date('H:i:s');

                        // Calcular la hora límite (una hora antes de la actual)
                        $horaLimite = date('H:i:s', strtotime('-1 hour'));

                        // Consulta SQL para contar el total de registros
                        $sqlCount = "
                            SELECT COUNT(*) AS total 
                            FROM sessions 
                            WHERE estado = 2 
                            AND valpsi = 0 AND(
                                fecha < '$fechaActual'
                            )
                        ";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        $psicolog = $_SESSION["id"];

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "
                            SELECT * 
                            FROM sessions 
                            WHERE estado = 2 
                            AND valpsi = 0 AND(
                                fecha < '$fechaActual' OR 
                                (fecha = '$fechaActual' AND hora <= '$horaLimite')
                            ) AND psi = '$psicolog'
                            LIMIT $offset, $registrosPorPagina
                        ";
                        $result = $conn->query($sql);


                        // Inicializamos un contador para el número de fila
                        $count = $offset + 1;

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {

                            $pacsql = $row['userID'];
                            $sqlpac = "SELECT numdoc, pn_usu, pa_usu, hiscli FROM usuarios WHERE id = $pacsql";
                            $result2 = $conn->query($sqlpac);
                            $row2 = $result2->fetch_assoc();

                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            echo '<td style="text-align: center;">' . $row2['hiscli'] . '</td>';
                            echo '<td style="text-align: center;">' . $row2['numdoc'] . '</td>';
                            echo '<td style="text-align: center;">' . $row2['pn_usu'] . ' ' . $row2['pa_usu'] . '</td>';
                            echo '<td style="text-align: center;">' . $row['fecha'] . '</td>';
                            echo '<td style="text-align: center;">' . $row['hora'] . '</td>';
                            echo '<td style="text-align: center;"><button class="btn btn-success btn-sm" onclick="abrirModal(' . $row['ID'] . ', 1)">Validar</button>
                            &nbsp;<button class="btn btn-danger btn-sm" onclick="abrirModal(' . $row['ID'] . ', 2)">No validar</button></td>';
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
<!-- Modal -->
<div id="confirmacionModal" class="modal" tabindex="-1" role="dialog">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Confirmación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p id="mensajeModal"></p> <!-- Aquí cambiaremos el texto dinámicamente -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" id="confirmarAccion" class="btn btn-primary">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<script>
    let idSesion;
    let accion;

    function abrirModal(id, accionValpsi) {
        idSesion = id;
        accion = accionValpsi; // 1 para validar, 2 para no validar

        // Cambiar el mensaje según la acción
        const mensaje = accion === 1 
            ? "¿Está seguro que desea confirmar que SI se tuvo la consulta?" 
            : "¿Está seguro que desea confirmar que NO se tuvo la consulta?";
        document.getElementById('mensajeModal').textContent = mensaje;

        // Mostrar el modal
        $('#confirmacionModal').modal('show');
    }

    document.getElementById('confirmarAccion').addEventListener('click', function () {
        // Redirige a la página PHP con los parámetros necesarios
        window.location.href = `confirmacion_actualizar.php?id=${idSesion}&valpsi=${accion}`;
    });
</script>

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

          if (status === 'validado') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Confirmación enviada exitosamente');
          }
      }
    });

  </script>
  <!-- Modal -->


</body>
</html>
