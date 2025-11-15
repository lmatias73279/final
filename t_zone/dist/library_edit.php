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

if($_SESSION['permiso_biblioteca'] !== 1 && $_SESSION['numdoc'] !== "1014273279" && $_SESSION['numdoc'] !== "1000693019"){
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
              <div class="breadcrumb-item"><a href="#">Biblioteca</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Biblioteca</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="#createUserModal">Crear Documento</button>
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
                          <th style="text-align: center;">Imagen</th>
                          <th style="text-align: center;">Editar</th>
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
                        $sqlCount = "SELECT COUNT(*) AS total FROM library";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "SELECT * FROM library LIMIT $offset, $registrosPorPagina";
                        $result = $conn->query($sql);

                        // Inicializamos un contador para el número de fila
                        $count = $offset + 1;

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            echo '<td style="text-align: center;">' . htmlspecialchars($row['title']) . '</td>';
                            echo '<td style="text-align: center;"><a href="assets/docs/library/'.$row['doc'].'" download>Descargar</a></td>';
                            echo '<td style="text-align: center;"><img alt="image" src="assets/docs/library/'.$row['img'].'" class="rounded-circle" width="35" height="35" style="object-fit: cover;" data-toggle="tooltip" title="" data-original-title="'.$row['description'].'"></img></td>';
                            echo '<td style="text-align: center;"><button class="btn btn-secondary btn-edit" data-id="' . $row['ID'] . '" data-toggle="modal" data-target="#exampleModal"><strong>Editar</strong></button></td>';
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
              toastr.success('El documento se actualizó correctamente.');
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


      // Configuración para editar usuario
      $('.btn-edit').on('click', function() {
          const id = $(this).data('id');  // Obtener el ID del usuario
          $('#modal-id').val(id); // Asignar el valor al campo oculto en el formulario

          // Solicitud AJAX para obtener los datos del usuario
          $.ajax({
              url: 'get_library_data.php',
              method: 'GET',
              data: { id: id },
              success: function(response) {
                  const data = JSON.parse(response);
                  $('#modal-name').val(data.title);
                  $('#modal-descripcion').val(data.description);
                  $('#modal-estado').val(data.status);
                  $('#modal-log_cambios').val(data.log_cambios);
              }
          });
      });

      // Mostrar el modal de confirmación al hacer clic en "Guardar Cambios"
      $('#edit-form').on('submit', function(event) {
          event.preventDefault();

          // Cierra el modal de edición antes de abrir el de confirmación
          $('#exampleModal').modal('hide');

          // Rellenar el resumen en el modal de confirmación
          $('#confirm-pais').text('Cargando...');  // Muestra un mensaje mientras se carga el nombre del país

          // Suponiendo que `modal-pais` contiene el código `cod_pais`
          var codPais = $('#modal-pais').val();

          $.ajax({
              url: 'obtenerPais.php',  // Archivo PHP que va a obtener el nombre del país
              type: 'GET',
              data: { cod_pais: codPais },
              success: function(response) {
                  // Suponiendo que `response` contiene el nombre del país
                  $('#confirm-pais').text(response);
              },
              error: function() {
                  $('#confirm-pais').text('Error al cargar el país');
              }
          });
          $('#confirm-name').text($('#modal-name').val());
          $('#confirm-descripcion').text($('#modal-descripcion').val());

          // Abrir el modal de confirmación
          $('#confirmModal').modal('show');
      });

      // Enviar el formulario de edición si el usuario confirma
      $('#confirm-update').on('click', function() {
          $('#edit-form')[0].submit();
      });





      // Configuración para crear usuario
      $('#create-form').on('submit', function(event) {
          event.preventDefault();
          
          // Cierra el modal de creación antes de abrir el de confirmación
          $('#createUserModal').modal('hide');
          
          // Rellenar el resumen en el modal de confirmación
          $('#confirm-create-pais').text('Cargando...');  // Muestra un mensaje mientras se carga el nombre del país

          // Suponiendo que `modal-pais` contiene el código `cod_pais`
          var codPais = $('#create-pais').val();

          $.ajax({
              url: 'obtenerPais.php',  // Archivo PHP que va a obtener el nombre del país
              type: 'GET',
              data: { cod_pais: codPais },
              success: function(response) {
                  // Suponiendo que `response` contiene el nombre del país
                  $('#confirm-create-pais').text(response);
              },
              error: function() {
                  $('#confirm-create-pais').text('Error al cargar el país');
              }
          });
          $('#confirm-create-name').text($('#create-name').val());
          $('#confirm-create-descripcion').text($('#create-descripcion').val());

          // Abrir el modal de confirmación
          $('#confirmCreateModal').modal('show');
      });

      // Enviar el formulario de creación si el usuario confirma
      $('#confirm-create').on('click', function() {
          $('#create-form')[0].submit();
      });
  });



  </script>
  <!-- Modal -->
  <!-- Modal para editar usuario -->
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Editar Documento</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
              </div>
              <form id="edit-form" action="update_library" method="POST" enctype="multipart/form-data">
                  <div class="modal-body">
                    <!-- Campo oculto para el ID del usuario -->
                    <input type="hidden" id="modal-id" name="id">
                    
                    
                    <div class="form-group">
                        <label for="modal-name">Nombre</label>
                        <input type="text" id="modal-name" name="name" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-img">Imagen</label>
                        <input type="file" id="modal-img" name="img" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-doc">Documento</label>
                        <input type="file" id="modal-doc" name="doc" class="form-control">
                    </div>

                    <div class="form-group">
                        <label for="modal-estado">Estado</label>
                        <select id="modal-estado" name="estado" class="form-control" required>
                            <option value="1">ACTIVO</option>
                            <option value="0">INACTIVO</option>
                        </select>
                    </div>

                    <div class="form-group mb-0">
                        <label for="modal-descripcion">Descripción</label>
                        <textarea class="form-control" id="modal-descripcion" name="descripcion" style="height: 200px !important; resize: none;" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="modal-log_cambios">Historial</label>
                        <textarea onlyread disabled class="form-control" id="modal-log_cambios" name="log_cambios" style="height: 200px !important; resize: none;"></textarea>
                    </div>

                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                    <button type="submit" class="btn btn-primary">Guardar Cambios</button>
                </div>
            </form>
          </div>
      </div>
  </div>

  <!-- Modal de confirmación -->
  <div class="modal fade" id="confirmModal" tabindex="-1" role="dialog" aria-labelledby="confirmModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document" style="max-height: 75vh; overflow-y: auto;">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="confirmModalLabel">Confirmar actualización</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
              </div>
              <div class="modal-body">
                  <p><strong>¿Estás seguro de que deseas actualizar los datos?</strong></p>
                  <p><strong>Resumen de los datos a actualizar:</strong></p>
                  <ul>
                      <li><strong>Nombre:</strong> <span id="confirm-name"></span></li>
                      <li><strong>Descripción:</strong> <span id="confirm-descripcion"></span></li>
                  </ul>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-primary" id="confirm-update">Confirmar</button>
              </div>
          </div>
      </div>
  </div>


  <!-- Modal para Crear Usuario -->
  <div class="modal fade" id="createUserModal" tabindex="-1" role="dialog" aria-labelledby="createUserLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="createUserLabel">Crear Documento</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
              </div>
              <form id="create-form" action="create_document.php" method="POST" enctype="multipart/form-data">
                  <div class="modal-body">
                    <input type="hidden" id="modal-id" name="id">

                    
                    <div class="form-group">
                        <label for="create-name">Nombre del Documento</label>
                        <input type="text" id="create-name" name="name" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="create-img">Imagen</label>
                        <input type="file" id="create-img" name="img" class="form-control" accept=".jpg, .jpeg, .png, .gif" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-doc">Documento</label>
                        <input type="file" id="create-doc" name="doc" class="form-control" accept=".pdf" required>
                    </div>
                    
                    <div class="form-group mb-0">
                        <label for="create-descripcion">Descripción</label>
                        <textarea class="form-control" id="create-descripcion" name="descripcion" style="height: 200px !important; resize: none;" required></textarea>
                    </div>

                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                      <button type="submit" class="btn btn-primary">Guardar</button>
                  </div>
              </form>
          </div>
      </div>
  </div>

  <!-- Modal de Confirmación para Crear Usuario -->
  <div class="modal fade" id="confirmCreateModal" tabindex="-1" role="dialog" aria-labelledby="confirmCreateLabel" aria-hidden="true">
      <div class="modal-dialog" role="document" style="max-height: 75vh; overflow-y: auto;">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="confirmCreateLabel">Confirmar creación de usuario</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
              </div>
              <div class="modal-body">
                  <p>¿Estás seguro de que deseas crear este usuario?</p>
                  <ul>
                      <li><strong>Nombre:</strong> <span id="confirm-create-name"></span></li>
                      <li><strong>Descripción:</strong> <span id="confirm-create-descripcion"></span></li>
                      <!-- Agrega los demás campos aquí -->
                  </ul>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button type="button" class="btn btn-primary" id="confirm-create">Confirmar</button>
              </div>
          </div>
      </div>
  </div>


</body>
</html>
