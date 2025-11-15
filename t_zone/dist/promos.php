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

if($_SESSION['permiso_promociones'] !== 1 && $_SESSION['numdoc'] !== "1014273279" && $_SESSION['numdoc'] !== "1000693019"){
    header("location: login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Usuarios</title>

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
              <div class="breadcrumb-item"><a href="#">Promociones</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Promociones</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="#createUserModal">Crear Promoción</button>
            <br></br>
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-md">
                        <tr>
                          <th style="text-align: center;">#</th>
                          <th style="text-align: center;">desde</th>
                          <th style="text-align: center;">hasta</th>
                          <th style="text-align: center;">codigo</th>
                          <th style="text-align: center;">Descuento</th>
                          <th style="text-align: center;">Estado</th>
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
                        $sqlCount = "SELECT COUNT(*) AS total FROM promos";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "SELECT id, desde, hasta, codigo, descuento, estado FROM promos LIMIT $offset, $registrosPorPagina";
                        $result = $conn->query($sql);

                        // Inicializamos un contador para el número de fila
                        $count = $offset + 1;

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {
                            $statusText = $row['estado'] == 1 ? 'Activo' : 'Inactivo';
                            $statusBadge = $row['estado'] == 1 ? 'success' : 'danger';

                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            echo '<td style="text-align: center;">' . $row['desde'] . '</td>';
                            echo '<td style="text-align: center;">' . $row['hasta'] . '</td>';
                            echo '<td style="text-align: center;">' . $row['codigo'] . '</td>';
                            echo '<td style="text-align: center;">' . $row['descuento'] . ' %</td>';
                            echo '<td style="text-align: center;"><div class="badge badge-' . $statusBadge . '"><strong>' . $statusText . '</strong></div></td>';
                            echo '<td style="text-align: center;"><button class="btn btn-secondary btn-edit" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#exampleModal"><strong>Editar</strong></button></td>';
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
              toastr.success('El registro se creó correctamente.');
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
              toastr.success('El registro se actualizó correctamente.');
          } else if (status === 'error_update') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Hubo un error al actualizar el registro.');
          } else if (status === 'no_update') {
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
              url: 'get_promos_data.php',
              method: 'GET',
              data: { id: id },
              success: function(response) {
                  const data = JSON.parse(response);
                  $('#modal-desde').val(data.desde);
                  $('#modal-hasta').val(data.hasta);
                  $('#modal-codigo').val(data.codigo);
                  $('#modal-descuento').val(data.descuento);
                  $('#modal-estado').val(data.estado);
              }
          });
      });

      // Mostrar el modal de confirmación al hacer clic en "Guardar Cambios"
      $('#edit-form').on('submit', function(event) {
          event.preventDefault();

          // Cierra el modal de edición antes de abrir el de confirmación
          $('#exampleModal').modal('hide');
          $('#confirm-desde').text($('#modal-desde').val());
          $('#confirm-hasta').text($('#modal-hasta').val());
          $('#confirm-codigo').text($('#modal-codigo').val());
          $('#confirm-descuento').text($('#modal-descuento').val());
          $('#confirm-estado').text($('#modal-estado').val() == '1' ? 'ACTIVO' : 'INACTIVO');

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

          $('#confirm-create-desde').text($('#create-desde').val());
          $('#confirm-create-hasta').text($('#create-hasta').val());
          $('#confirm-create-codigo').text($('#create-codigo').val());
          $('#confirm-create-descuento').text($('#create-descuento').val() + ' %');

          // Abrir el modal de confirmación
          $('#confirmCreateModal').modal('show');
      });

      // Enviar el formulario de creación si el usuario confirma
      $('#confirm-create').on('click', function() {
          $('#create-form')[0].submit();
      });
  });



  </script>
  <script>
    function validateUser() {
        const numdoc = document.getElementById("create-numdoc").value;
        const tipdoc = document.getElementById("create-td_usu").value;

        // Verificar que ambos campos tengan valores antes de enviar
        if (!numdoc || !tipdoc) {
        return;
        }

        // Crear la petición AJAX
        const xhr = new XMLHttpRequest();
        xhr.open("POST", "validate_user.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

        xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            const response = JSON.parse(xhr.responseText);

            if (response.exists) {
                alert("No es posible crear un usuario porque ya existe en la base de datos.");
                document.getElementById("create-numdoc").value = ""; // Limpiar el input
                document.getElementById("create-td_usu").value = ""; // Limpiar el input
            }
        }
        };

        // Enviar datos al servidor
        xhr.send(`numdoc=${encodeURIComponent(numdoc)}&tipdoc=${encodeURIComponent(tipdoc)}`);
    }
    </script>
  <!-- Modal -->
  <!-- Modal para editar usuario -->
  <div class="modal fade" id="exampleModal" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="exampleModalLabel">Editar Usuario</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
              </div>
              <form id="edit-form" action="update_promos" method="POST">
                  <div class="modal-body">
                    <!-- Campo oculto para el ID del usuario -->
                    <input type="hidden" id="modal-id" name="id">

                    <div class="form-group">
                        <label for="modal-desde">Desde</label>
                        <input type="date" id="modal-desde" name="desde" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-hasta">Hasta</label>
                        <input type="date" id="modal-hasta" name="hasta" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-codigo">Código</label>
                        <input type="text" id="modal-codigo" name="codigo" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-descuento">Descuento</label>
                        <input type="text" id="modal-descuento" name="descuento" class="form-control" required onkeydown="preventInvalidChars1(event)">
                        <script>
                            function preventInvalidChars1(event) {
                                const invalidChars = ['.', ',', 'e'];

                                if (invalidChars.includes(event.key)) {
                                    event.preventDefault();
                                }
                            }
                        </script>
                    </div>

                    <div class="form-group">
                        <label for="modal-estado">Estado</label>
                        <select id="modal-estado" name="estado" class="form-control" required>
                            <option value="1">ACTIVO</option>
                            <option value="0">INACTIVO</option>
                        </select>
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
                      <li><strong>Desde:</strong> <span id="confirm-desde"></span></li>
                      <li><strong>Hasta:</strong> <span id="confirm-hasta"></span></li>
                      <li><strong>Código:</strong> <span id="confirm-codigo"></span></li>
                      <li><strong>Descuento:</strong> <span id="confirm-descuento"></span></li>
                      <li><strong>Estado:</strong> <span id="confirm-estado"></span></li>
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
                  <h5 class="modal-title" id="createUserLabel">Crear Descuento</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
              </div>
              <form id="create-form" action="create_discount.php" method="POST">
                  <div class="modal-body">
                    <input type="hidden" id="modal-id" name="id">

                    <div class="form-group">
                        <label for="create-desde">Desde</label>
                        <input type="date" id="create-desde" name="desde" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="create-hasta">Hasta</label>
                        <input type="date" id="create-hasta" name="hasta" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="create-codigo">Código</label>
                        <input type="text" id="create-codigo" placeholder="Ingrese el código de descuento" name="codigo" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="create-descuento">Descuento</label>
                        <input type="text" placeholder="Ingrese el descuento" id="create-descuento" name="descuento" class="form-control" required onkeydown="preventInvalidChars(event)">
                        <script>
                            function preventInvalidChars(event) {
                                const invalidChars = ['.', ',', 'e'];

                                if (invalidChars.includes(event.key)) {
                                    event.preventDefault();
                                }
                            }
                        </script>
                    </div>
                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                      <button type="submit" class="btn btn-primary">Guardar Descuento</button>
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
                      <li><strong>Desde:</strong> <span id="confirm-create-desde"></span></li>
                      <li><strong>Hasta:</strong> <span id="confirm-create-hasta"></span></li>
                      <li><strong>Código:</strong> <span id="confirm-create-codigo"></span></li>
                      <li><strong>Descuento:</strong> <span id="confirm-create-descuento"></span></li>
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
