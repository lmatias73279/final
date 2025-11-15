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

if($_SESSION['permiso_gastos'] !== 1 && $_SESSION['numdoc'] !== "1014273279" && $_SESSION['numdoc'] !== "1000693019"){
    header("location: login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Gastos</title>

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
              <div class="breadcrumb-item"><a href="#">Gastos</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Gastos</h2>
            <!-- Botones -->
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalCrearTipo">Crear Tipo de Gasto</button>
            <button class="btn btn-primary" data-toggle="modal" data-target="#modalRegistrarGasto">Registrar Gasto</button>
            <br></br>
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-md">
                        <tr>
                          <th style="text-align: center;">#</th>
                          <th style="text-align: center;">Fecha</th>
                          <th style="text-align: center;">Tipo</th>
                          <th style="text-align: center;">Valor</th>
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
                        $sqlCount = "SELECT COUNT(*) AS total FROM gastos";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "SELECT * FROM gastos ORDER BY date DESC LIMIT $offset, $registrosPorPagina";
                        $result = $conn->query($sql);

                        // Inicializamos un contador para el número de fila
                        $count = $offset + 1;

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {
                            // Consulta adicional para obtener la descripción
                            $id_gasto = $row['id_gasto'];
                            $query_desc = "SELECT description FROM idsgastos WHERE id = $id_gasto";
                            $result_desc = $conn->query($query_desc);
                            $desc = $result_desc->fetch_assoc();
                            $description = $desc ? $desc['description'] : 'No disponible';

                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            echo '<td style="text-align: center;">' . $row['date'] . '</td>';
                            echo '<td style="text-align: center;">' . $description . '</td>'; // Aquí agregas la descripción
                            echo '<td style="text-align: center;">$ ' . number_format($row['value'], 0, '.', ',') . '</td>';
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
  })
</script>





<!-- Modal Crear Tipo de Gasto -->
<div class="modal fade" id="modalCrearTipo" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Crear Tipo de Gasto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formCrearTipo" action="gastos_crear_tipos.php" method="POST">
          <div class="form-group">
            <label for="descripcionTipo">Descripción</label>
            <input type="text" class="form-control" id="descripcionTipo" name="descripcionTipo" required>
          </div>
          <div class="form-group">
            <label for="afecta">¿afecta estado de resultados?</label>
            <select type="text" class="form-control" id="afecta" name="afecta" required>
              <option value="">Seleccione una opción</option>
              <option value="1">Si</option>
              <option value="2">No</option>
            </select>
          </div>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>


<!-- Modal Registrar Gasto -->
<div class="modal fade" id="modalRegistrarGasto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Registrar Gasto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formRegistrarGasto" method="POST" action="gastos_registrar">
          <div class="form-group">
            <label for="tipoGasto">Tipo de Gasto</label>
            <select class="form-control" id="tipoGasto" name="tipoGasto" required>
            <?php
            // Consulta para obtener los datos de la tabla idsgastos
            $query = "SELECT ID, description FROM idsgastos";
            $result = $conn->query($query);

            // Verificamos si hay resultados
            if ($result->num_rows > 0) {
                // Recorremos los resultados y generamos las opciones
                echo '<option value="">Seleccione una opción</option>';
                while ($row = $result->fetch_assoc()) {
                    // Aquí llenamos el select con value como ID y el texto como description
                    echo '<option value="' . $row['ID'] . '">' . $row['description'] . '</option>';
                }
            }
            ?>
            </select>
          </div>
          <div class="form-group">
            <label for="fechaGasto">Fecha</label>
            <input type="date" class="form-control" id="fechaGasto" name="fechaGasto" required>
          </div>
          
          <div class="form-group">
            <label for="medio_pago">Medio de pago</label>
            <select class="form-control" id="medio_pago" name="medio_pago" required>
              <option value="">Seleccione una opción</option>
              <option value="1">Paypal</option>
              <option value="2">Bold</option>
              <option value="3">PayU</option>
              <option value="4">Efecivo</option>
              <option value="5">Bancolombia (Empresa)</option>
              <option value="6">Bancolombia (Personal)</option>
              <option value="7">Nequi</option>
              <option value="8">Daviplata</option>
            </select>
          </div>
          <div class="form-group">
            <label for="descripcionGasto">Descripción</label>
            <input type="text" class="form-control" id="descripcionGasto" name="descripcionGasto" required>
          </div>
          <div class="form-group">
            <label for="valorGasto">Valor</label>
            <input type="number" class="form-control" id="valorGasto" name="valorGasto" required>
          </div>
          <button type="submit" class="btn btn-primary">Guardar</button>
        </form>
      </div>
    </div>
  </div>
</div>



<!-- Modal Editar Gasto -->
<div class="modal fade" id="modalEditarGasto" tabindex="-1" role="dialog" aria-labelledby="exampleModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="exampleModalLabel">Editar Gasto</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formEditarGasto" method="POST" action="gastos_editar.php">
          <input type="hidden" id="editId" name="editId">
          <div class="form-group">
            <label for="editTipoGasto">Tipo de Gasto</label>
            <select class="form-control" id="editTipoGasto" name="editTipoGasto" required>
            <?php
            // Consulta para obtener los datos de la tabla idsgastos
            $query = "SELECT ID, description FROM idsgastos";
            $result = $conn->query($query);

            // Verificamos si hay resultados
            if ($result->num_rows > 0) {
                // Recorremos los resultados y generamos las opciones
                echo '<option value="">Seleccione una opción</option>';
                while ($row = $result->fetch_assoc()) {
                    // Aquí llenamos el select con value como ID y el texto como description
                    echo '<option value="' . $row['ID'] . '">' . $row['description'] . '</option>';
                }
            }
            ?>
            </select>
          </div>
          <div class="form-group">
            <label for="editFechaGasto">Fecha</label>
            <input type="date" class="form-control" id="editFechaGasto" name="editFechaGasto" required>
          </div> 
          <div class="form-group">
            <label for="editmedio_pago">Medio de pago</label>
            <select class="form-control" id="editmedio_pago" name="editmedio_pago" required>
              <option value="">Seleccione una opción</option>
              <option value="1">Paypal</option>
              <option value="2">Bold</option>
              <option value="3">PayU</option>
              <option value="4">Efecivo</option>
              <option value="5">Bancolombia (Empresa)</option>
              <option value="6">Bancolombia (Personal)</option>
              <option value="7">Nequi</option>
              <option value="8">Daviplata</option>
            </select>
          </div>
          <div class="form-group">
            <label for="editDescripcionGasto">Descripción</label>
            <input type="text" class="form-control" id="editDescripcionGasto" name="editDescripcionGasto" required>
          </div>
          <div class="form-group">
            <label for="editValorGasto">Valor</label>
            <input type="number" class="form-control" id="editValorGasto" name="editValorGasto" required>
          </div>
          <button type="submit" class="btn btn-primary">Guardar Cambios</button>
        </form>
      </div>
    </div>
  </div>
</div>


<script>
    $(document).ready(function() {
        // Cuando el botón de editar es clickeado
        $(".btn-edit").click(function() {
            var idGasto = $(this).data("id");

            // Realizamos una petición AJAX para obtener los datos del gasto
            $.ajax({
                url: 'gastos_obtener.php', // Archivo que obtiene los datos del gasto
                type: 'GET',
                data: { id: idGasto },
                success: function(response) {
                    // Suponiendo que la respuesta es un JSON con los datos
                    var gasto = JSON.parse(response);

                    // Cargar los datos en el modal
                    $('#editId').val(gasto.ID);
                    $('#editTipoGasto').val(gasto.id_gasto);
                    $('#editFechaGasto').val(gasto.date);
                    $('#editDescripcionGasto').val(gasto.description);
                    $('#editmedio_pago').val(gasto.banco);
                    $('#editValorGasto').val(gasto.value);

                    // Abrir el modal
                    $('#modalEditarGasto').modal('show');
                }
            });
        });
    });
</script>




<script>

    // Función que convierte el texto de todos los inputs tipo text a mayúsculas
function convertirMayusculas() {
    // Obtener todos los inputs de tipo text
    const inputsTexto = document.querySelectorAll('input[type="text"], textarea');
    
    inputsTexto.forEach(input => {
        input.addEventListener('input', function() {
            input.value = input.value.toUpperCase(); // Convertir a mayúsculas
        });
    });
}

// Llamar a la función para activar el comportamiento
document.addEventListener('DOMContentLoaded', convertirMayusculas);

</script>
</body>
</html>
