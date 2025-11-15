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
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Pup Up´s</title>

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
              <div class="breadcrumb-item"><a href="#">Pop Up´s</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Pop Up´s</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="#popupModal">Publicar Nuevo Pop Up</button>
            <br></br>
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-md">
                        <tr>
                          <th style="text-align: center;">#</th>
                          <th style="text-align: center;">Nombre</th>
                          <th style="text-align: center;">Documento</th>
                          <th style="text-align: center;">Estado</th>
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

                        // Consulta SQL para contar el total de registros
                        $sqlCount = "SELECT COUNT(*) AS total FROM popups WHERE del = 0";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "SELECT id, img, pub, est FROM popups WHERE del = 0 ORDER BY id DESC LIMIT $offset, $registrosPorPagina";
                        $result = $conn->query($sql);

                        // Inicializamos un contador para el número de fila
                        $count = $offset + 1;

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {
                            // Determinar el estado
                            $statusText = $row['est'] == 0 ? 'Activo' : 'Inactivo';
                            $statusBadge = $row['est'] == 0 ? 'success' : 'danger';

                            // Determinar el público
                            $publicoArray = [];
                            $pubValues = explode(',', $row['pub']); // En caso de que haya múltiples valores
                            if (in_array('1', $pubValues)) $publicoArray[] = 'Administrativos';
                            if (in_array('3', $pubValues)) $publicoArray[] = 'Profesionales';
                            if (in_array('4', $pubValues)) $publicoArray[] = 'Pacientes';
                            $publicoText = implode(', ', $publicoArray);

                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            echo '<td style="text-align: center;">
                                    <a class="btn btn-link btn-view-img" href="' . htmlspecialchars($row['img']) . '" download>
                                        <i class="fas fa-download"></i>
                                    </a>
                                </td>';
                            echo '<td style="text-align: center;"><div class="badge badge-' . $statusBadge . '"><strong>' . $statusText . '</strong></div></td>';
                            echo '<td style="text-align: center;">' . $publicoText . '</td>';
                            echo '<td style="text-align: center;"><a href="popup_ver?id='.$row['id'].'"><i class="fas fa-eye text-success"></i></a> <a href="popup_verno?id='.$row['id'].'"><i class="fas fa-eye-slash text-warning"></i></a> <a href="popup_x?id='.$row['id'].'"><i class="fas fa-trash-alt text-danger"></i></a></td>';
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
    <div class="modal fade" id="popupModal" tabindex="-1" aria-labelledby="popupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="popupModalLabel">Publicar Nuevo Pop Up</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <!-- Formulario -->
                    <form action="popup_publicar.php" method="POST" enctype="multipart/form-data">
                        <div class="form-group">
                            <label for="popupImage">Imagen del Pop Up</label>
                            <input type="file" class="form-control" id="popupImage" name="popupImage" required>
                        </div>
                        <div class="form-group">
                            <label>Público</label>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="admin" name="popupPublico[]" value="1">
                                <label class="form-check-label" for="admin">Administrativos</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="profesionales" name="popupPublico[]" value="3">
                                <label class="form-check-label" for="profesionales">Profesionales</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="pacientes" name="popupPublico[]" value="4">
                                <label class="form-check-label" for="pacientes">Pacientes</label>
                            </div>
                        </div>

                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <button type="submit" class="btn btn-success" onclick="return validarCheckboxes()">Publicar</button>
                        </div>
                        <script>
                            function validarCheckboxes() {
                                var checkboxes = document.querySelectorAll('input[name="popupPublico[]"]:checked');

                                if (checkboxes.length === 0) {
                                    toastr.error("Debes seleccionar al menos un público.", "Error");
                                    return false;
                                }
                                return true;
                            }
                        </script>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
      // Función de descifrado XOR con clave
      function simpleDecrypt(text, key) {
          const decodedText = atob(text);  // Decodificar base64
          let output = '';
          for (let i = 0; i < decodedText.length; i++) {
              output += String.fromCharCode(decodedText.charCodeAt(i) ^ key.charCodeAt(i % key.length));
          }
          return output;
      }

        document.addEventListener("DOMContentLoaded", function () {
            const urlParams = new URLSearchParams(window.location.search);
            const encryptedStatus = urlParams.get('sta');

            if (encryptedStatus) {
                const status = simpleDecrypt(encryptedStatus, '2020');
                console.log("Estado desencriptado:", status);

                toastr.options = {
                    "timeOut": 5000,
                    "extendedTimeOut": 1000,
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };

                if (status === 'erralguarbd') {
                    toastr.error('Error al guardar en la base de datos.');
                } else if (status === 'okok') {
                    toastr.success('PopUp publicado correctamente.');
                } else if (status === 'almenosunpub') {
                    toastr.error('Debe seleccionar al menos un público.');
                } else if (status === 'erralactregexist') {
                    toastr.error('Error al deshabilitar PopUps existentes.');
                } else if (status === 'erralsubirimg') {
                    toastr.error('Error al subir imagen.');
                } else if (status === 'soloseper') {
                    toastr.error('Solo se permiten imágenes jpg, jpeg, png y gif.');
                } else if (status === 'imgnovalid') {
                    toastr.error('El archivo cargado no es una imagen.');
                } else if (status === 'vererror') {
                    toastr.error('Error al activar el PopUp.');
                } else if (status === 'verok') {
                    toastr.success('PopUp activado correctamente.');
                } else if (status === 'vernoid') {
                    toastr.error('Error al identificar el id.');
                } else if (status === 'vernoerror') {
                    toastr.error('Error al desactivar el PopUp.');
                } else if (status === 'vernook') {
                    toastr.success('PopUp desactivado correctamente.');
                } else if (status === 'vernonoid') {
                    toastr.error('Error al identificar el id.');
                } else if (status === 'delerror') {
                    toastr.error('Error al eliminar el PopUp.');
                } else if (status === 'delok') {
                    toastr.success('PopUp eliminar correctamente.');
                } else if (status === 'delnoid') {
                    toastr.error('Error al identificar el id.');
                }
            }
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

</body>
</html>
