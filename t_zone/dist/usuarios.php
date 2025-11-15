<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 99 && $_SESSION['numdoc'] !== '1000693019'){
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
              <div class="breadcrumb-item"><a href="#">Usuarios</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Usuarios</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="#createUserModal">Crear Usuario</button>
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
                        $sqlCount = "SELECT COUNT(*) AS total FROM usuarios WHERE permiso IN (1, 3)";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "SELECT id, pn_usu, pa_usu, numdoc, estado FROM usuarios WHERE permiso IN (1, 3) LIMIT $offset, $registrosPorPagina";
                        $result = $conn->query($sql);

                        // Inicializamos un contador para el número de fila
                        $count = $offset + 1;

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {
                            $statusText = $row['estado'] == 1 ? 'Activo' : 'Inactivo';
                            $statusBadge = $row['estado'] == 1 ? 'success' : 'danger';

                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            echo '<td style="text-align: center;">' . htmlspecialchars($row['pn_usu']) . " " . htmlspecialchars($row['pa_usu']) . '</td>';
                            echo '<td style="text-align: center;">' . htmlspecialchars($row['numdoc']) . '</td>';
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
      const formularios = document.querySelectorAll('input[type="text"], input[type="email"], input[type="password"]');
      
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
          }else if (status === 'erralactregexist') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Error al actualizar registro existente.');
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


        $('.btn-edit').on('click', function() {
            const id = $(this).data('id');  // Obtener el ID del usuario
            $('#modal-id').val(id); // Asignar el valor al campo oculto en el formulario

            // Solicitud AJAX para obtener los datos del usuario
            $.ajax({
                url: 'get_user_data.php',
                method: 'GET',
                data: { id: id },
                success: function(response) {
                    const data = JSON.parse(response);
                    console.log(data); // Para depuración en la consola

                    $('#modal-pais').val(data.pais);
                    $('#modal-td_usu').val(data.tipdoc);
                    $('#modal-numdoc').val(data.numdoc);
                    $('#modal-pn_usu').val(data.pn_usu);
                    $('#modal-sn_usu').val(data.sn_usu);
                    $('#modal-pa_usu').val(data.pa_usu);
                    $('#modal-sa_usu').val(data.sa_usu);
                    $('#modal-tel_usu').val(data.tel_usu);
                    $('#modal-cor_usu').val(data.cor_usu);
                    $('#modal-fna_usu').val(data.born_date);
                    $('#modal-per_usu').val(data.permiso);
                    $('#modal-estado').val(data.estado);
                    $('#modal-comision').val(data.comision);
                    $('#modal-log_cambios').val(data.log_cambios);
                    $('#modal-descripcion').val(data.descripcion);
                    $('#modal-tip_pro').val(data.tip_pro);
                    $('#modal-profesional_asignado').val(data.profesional_asignado);

                    // Limpiar checkboxes antes de marcar los que correspondan
                    $('input[name="permisos[]"]').prop('checked', false);

                    // Marcar checkboxes según la base de datos
                    if (data.permiso_blog == 1) $('#permiso-blog').prop('checked', true);
                    if (data.permiso_biblioteca == 1) $('#permiso-biblioteca').prop('checked', true);
                    if (data.permiso_citas == 1) $('#permiso-citas').prop('checked', true);
                    if (data.permiso_promociones == 1) $('#permiso-promociones').prop('checked', true);
                    if (data.permiso_gastos == 1) $('#permiso-gastos').prop('checked', true);
                    if (data.permiso_citas_pagos == 1) $('#permiso-citas-pagos').prop('checked', true);

                    toggleFields2();
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
          $('#confirm-td_usu').text($('#modal-td_usu').val());
          $('#confirm-numdoc').text($('#modal-numdoc').val());
          $('#confirm-pn_usu').text($('#modal-pn_usu').val());
          $('#confirm-sn_usu').text($('#modal-sn_usu').val());
          $('#confirm-pa_usu').text($('#modal-pa_usu').val());
          $('#confirm-sa_usu').text($('#modal-sa_usu').val());
          $('#confirm-tel_usu').text($('#modal-tel_usu').val());
          $('#confirm-comision').text($('#modal-comision').val() + ' %');
          $('#confirm-cor_usu').text($('#modal-cor_usu').val());
          $('#confirm-fna_usu').text($('#modal-fna_usu').val());
          var perUsu = $('#modal-per_usu').val();
          var texto;

          switch (perUsu) {
              case '1':
                  texto = 'ADMINISTRATIVO';
                  break;
              case '2':
                  texto = 'PSICOLOGO';
                  break;
              case '3':
                  texto = 'EMPRESA';
                  break;
              case '4':
                  texto = 'PACIENTE';
                  break;
              default:
                  texto = 'INACTIVO';  // Por defecto si el valor no coincide con ninguno de los casos anteriores
                  break;
          }

          $('#confirm-per_usu').text(texto);
          $('#confirm-estado').text($('#modal-estado').val() == '1' ? 'ACTIVO' : 'INACTIVO');
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
          $('#confirm-create-td_usu').text($('#create-td_usu').val());
          $('#confirm-create-numdoc').text($('#create-numdoc').val());
          $('#confirm-create-pn_usu').text($('#create-pn_usu').val());
          $('#confirm-create-sn_usu').text($('#create-sn_usu').val());
          $('#confirm-create-pa_usu').text($('#create-pa_usu').val());
          $('#confirm-create-sa_usu').text($('#create-sa_usu').val());
          $('#confirm-create-tel_usu').text($('#create-tel_usu').val());
          $('#confirm-create-cor_usu').text($('#create-cor_usu').val());
          $('#confirm-create-fna_usu').text($('#create-fna_usu').val());
          $('#confirm-create-comision').text($('#create-comision').val() + ' %');
          var perUsu = $('#create-per_usu').val();
          var texto;

          switch (perUsu) {
              case '1':
                  texto = 'ADMINISTRATIVO';
                  break;
              case '2':
                  texto = 'PSICOLOGO';
                  break;
              case '3':
                  texto = 'EMPRESA';
                  break;
              case '4':
                  texto = 'PACIENTE';
                  break;
              default:
                  texto = 'INACTIVO';  // Por defecto si el valor no coincide con ninguno de los casos anteriores
                  break;
          }

          $('#confirm-create-per_usu').text(texto);
          $('#confirm-create-estado').text($('#create-estado').val() == '1' ? 'ACTIVO' : 'INACTIVO');
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
              <form id="edit-form" action="update" method="POST">
                  <div class="modal-body">
                    <!-- Campo oculto para el ID del usuario -->
                    <input type="hidden" id="modal-id" name="id">
                    
                    <?php

                    // Consulta para obtener los países desde la base de datos ordenados por el nombre del país
                    $sql = "SELECT cod_pais, pais FROM paises ORDER BY pais ASC"; // Ordenado alfabéticamente por 'pais'
                    $result = $conn->query($sql);

                    // Verificamos si se obtuvieron resultados
                    if ($result->num_rows > 0) {
                        $paises = $result->fetch_all(MYSQLI_ASSOC); // Almacenamos los países en un array
                    } else {
                        $paises = []; // Si no hay resultados, definimos un array vacío
                    }
                    ?>

                    <!-- Tu HTML para el select -->
                    <div class="form-group">
                        <label for="modal-pais">Seleccione un país</label>
                        <select id="modal-pais" name="pais" class="form-control" required>
                            <option value="">Seleccione un país</option> <!-- Opción por defecto -->
                            <!-- Generar las opciones desde la base de datos -->
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?php echo $pais['cod_pais']; ?>"><?php echo $pais['pais']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    
                    <div class="form-group">
                        <label for="modal-td_usu">Tipo de documento</label>
                        <select type="text" id="modal-td_usu" name="td_usu" class="form-control" required>
                          <option value="">Seleccione una opción</option>
                          <option value="CC">CC</option>
                          <option value="CE">CE</option>
                          <option value="TI">TI</option>
                          <option value="PT">PT</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal-numdoc">Número de Documento</label>
                        <input type="text" id="modal-numdoc" name="numdoc" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-pn_usu">Primer Nombre</label>
                        <input type="text" id="modal-pn_usu" name="pn_usu" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-sn_usu">Segundo Nombre</label>
                        <input type="text" id="modal-sn_usu" name="sn_usu" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-pa_usu">Primer Apellido</label>
                        <input type="text" id="modal-pa_usu" name="pa_usu" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-sa_usu">Segundo Apellido</label>
                        <input type="text" id="modal-sa_usu" name="sa_usu" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-tel_usu">Teléfono</label>
                        <input type="tel" placeholder="Teléfono" id="modal-tel_usu" name="tel_usu" 
                            class="form-control" required pattern="[0-9]{10}" maxlength="10"
                            title="Por favor, ingrese un número de teléfono de 10 dígitos sin espacios ni caracteres especiales." 
                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-cor_usu">Correo</label>
                        <input type="text" id="modal-cor_usu" name="cor_usu" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-fna_usu">Fecha Nacimiento</label>
                        <input type="date" id="modal-fna_usu" name="fna_usu" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-per_usu">Permiso</label>
                        <select type="text" id="modal-per_usu" name="per_usu" class="form-control" required onchange="toggleFields2()">
                          <option value="">Seleccione una opción</option>
                          <option value="1">ADMINISTRATIVO</option>
                          <option value="3">PROFESIONAL</option>
                        </select>
                    </div>
                    
                    <div class="form-group" id="comision-groupo" style="display: none;">
                        <label for="modal-comision">Porcentaje Comisión</label>
                        <input type="number" placeholder="% de comisión" id="modal-comision" name="comision" class="form-control" onkeydown="preventInvalidChars(event)">
                        </script>
                    </div>
                    
                    <div class="form-group" id="tip_pro-groupo" style="display: none;">
                        <label for="modal-tip_pro">Tipo Profesional</label>
                        <select type="text" id="modal-tip_pro" name="tip_pro" class="form-control">
                          <option value="">Seleccione una opción</option>
                          <option value="1">PSICÓLOGO</option>
                          <option value="2">PSIQUIATRA</option>
                          <option value="3">NUTRICIONISTA</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal-estado">Estado</label>
                        <select id="modal-estado" name="estado" class="form-control" required>
                            <option value="1">ACTIVO</option>
                            <option value="0">INACTIVO</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Asignación de Permisos</label><br>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-blog" name="permisos[]" value="blog">
                            <label class="form-check-label" for="permiso-blog">
                                Blog
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-biblioteca" name="permisos[]" value="biblioteca">
                            <label class="form-check-label" for="permiso-biblioteca">
                                Biblioteca
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-citas" name="permisos[]" value="citas">
                            <label class="form-check-label" for="permiso-citas">
                                Citas
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-promociones" name="permisos[]" value="promociones">
                            <label class="form-check-label" for="permiso-promociones">
                                Promociones
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-gastos" name="permisos[]" value="gastos">
                            <label class="form-check-label" for="permiso-gastos">
                                Gastos
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-citas-pagos" name="permisos[]" value="citas y pagos">
                            <label class="form-check-label" for="permiso-citas-pagos">
                                Citas y Pagos
                            </label>
                        </div>
                    </div>


                    <div class="form-group">
                        <label for="modal-profesional_asignado">Profesional Asignado</label>
                        <select id="modal-profesional_asignado" name="profesional_asignado" class="form-control">
                            <option value="">Seleccione un profesional</option>
                            <?php
                            // Consulta a la base de datos
                            $idprofe = $_SESSION['id'];
                            $sql = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 AND id != $idprofe";
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Crear las opciones
                                    $id = $row['id'];
                                    $nombreCompleto = trim($row['pn_usu'] . ' ' . $row['sn_usu'] . ' ' . $row['pa_usu'] . ' ' . $row['sa_usu']);
                                    echo "<option value=\"$id\">" . htmlspecialchars($nombreCompleto) . "</option>";
                                }
                            } else {
                                // Si no hay datos
                                echo '<option value="">No hay profesionales disponibles</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mb-0" id="descripcion-groupo" style="display: none;">
                        <label for="modal-descripcion">Descripción</label>
                        <textarea class="form-control" id="modal-descripcion" name="descripcion" style="height: 200px !important; resize: none;"></textarea>
                    </div>

                    <script>
                        function preventInvalidChars(event) {
                            const invalidChars = ['.', ',', 'e'];
                            if (invalidChars.includes(event.key)) {
                                event.preventDefault();
                            }
                        }

                        function toggleFields2() {
                            const permiso = document.getElementById("modal-per_usu").value;
                            const comisionGroup = document.getElementById("comision-groupo");
                            const descipcionGroup = document.getElementById("descripcion-groupo");
                            const tipProGroup = document.getElementById("tip_pro-groupo");
                            const comisionInput = document.getElementById("modal-comision");
                            const descripcionInput = document.getElementById("modal-descripcion");
                            const tipProSelect = document.getElementById("modal-tip_pro");

                            if (permiso === "3") { // PSICÓLOGO seleccionado
                                comisionGroup.style.display = "block";
                                descipcionGroup.style.display = "block";
                                tipProGroup.style.display = "block";
                                comisionInput.setAttribute("required", "true");
                                descripcionInput.setAttribute("required", "true");
                                tipProSelect.setAttribute("required", "true");
                            } else {
                                comisionGroup.style.display = "none";
                                descipcionGroup.style.display = "none";
                                tipProGroup.style.display = "none";
                                comisionInput.removeAttribute("required");
                                descripcionInput.removeAttribute("required");
                                tipProSelect.removeAttribute("required");
                            }
                        }
                    </script>

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
                      <li><strong>Pais:</strong> <span id="confirm-pais"></span></li>
                      <li><strong>Tipo de documento:</strong> <span id="confirm-td_usu"></span></li>
                      <li><strong>Número de documento:</strong> <span id="confirm-numdoc"></span></li>
                      <li><strong>Primer Nombre:</strong> <span id="confirm-pn_usu"></span></li>
                      <li><strong>Segundo Nombre:</strong> <span id="confirm-sn_usu"></span></li>
                      <li><strong>Primer Apellido:</strong> <span id="confirm-pa_usu"></span></li>
                      <li><strong>Segundo Apellido:</strong> <span id="confirm-sa_usu"></span></li>
                      <li><strong>Teléfono:</strong> <span id="confirm-tel_usu"></span></li>
                      <li><strong>Correo:</strong> <span id="confirm-cor_usu"></span></li>
                      <li><strong>Fecha Naicimiento:</strong> <span id="confirm-fna_usu"></span></li>
                      <li><strong>Permiso:</strong> <span id="confirm-per_usu"></span></li>
                      <li><strong>Comisión:</strong> <span id="confirm-comision"></span></li>
                      <li><strong>Estado:</strong> <span id="confirm-estado"></span></li>
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
                  <h5 class="modal-title" id="createUserLabel">Crear Usuario</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
              </div>
              <form id="create-form" action="create_user.php" method="POST">
                  <div class="modal-body">
                    <!-- Campo oculto para el ID del usuario -->
                    <input type="hidden" id="modal-id" name="id">
                    
                    <?php

                    // Consulta para obtener los países desde la base de datos ordenados por el nombre del país
                    $sql = "SELECT cod_pais, pais FROM paises ORDER BY pais ASC"; // Ordenado alfabéticamente por 'pais'
                    $result = $conn->query($sql);

                    // Verificamos si se obtuvieron resultados
                    if ($result->num_rows > 0) {
                        $paises = $result->fetch_all(MYSQLI_ASSOC); // Almacenamos los países en un array
                    } else {
                        $paises = []; // Si no hay resultados, definimos un array vacío
                    }
                    ?>

                    <!-- Tu HTML para el select -->
                    <div class="form-group">
                        <label for="create-pais">Seleccione un país</label>
                        <select id="create-pais" name="pais" class="form-control" required>
                            <option value="">Seleccione un país</option> <!-- Opción por defecto -->
                            <!-- Generar las opciones desde la base de datos -->
                            <?php foreach ($paises as $pais): ?>
                                <option value="<?php echo $pais['cod_pais']; ?>"><?php echo $pais['pais']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>


                    
                    <div class="form-group">
                        <label for="create-td_usu">Tipo de documento</label>
                        <select type="text" id="create-td_usu" name="td_usu" class="form-control" onchange="validateUser()" required>
                          <option value="">Seleccione una opción</option>
                          <option value="CC">CC</option>
                          <option value="CE">CE</option>
                          <option value="TI">TI</option>
                          <option value="PT">PT</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create-numdoc">Número de Documento</label>
                        <input type="text" placeholder="Número de documento" id="create-numdoc" name="numdoc" onchange="validateUser()" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-pn_usu">Primer Nombre</label>
                        <input type="text" placeholder="Primer Nombre" id="create-pn_usu" name="pn_usu" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-sn_usu">Segundo Nombre</label>
                        <input type="text" placeholder="Segundo Nombre" id="create-sn_usu" name="sn_usu" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="create-pa_usu">Primer Apellido</label>
                        <input type="text" placeholder="Primer Apellido" id="create-pa_usu" name="pa_usu" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-sa_usu">Segundo Apellido</label>
                        <input type="text" placeholder="Segundo Apellido" id="create-sa_usu" name="sa_usu" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="create-tel_usu">Teléfono</label>
                        <input type="tel" placeholder="Teléfono" id="create-tel_usu" name="tel_usu" 
                            class="form-control" required pattern="[0-9]{10}" maxlength="10"
                            title="Por favor, ingrese un número de teléfono de 10 dígitos sin espacios ni caracteres especiales." 
                            oninput="this.value = this.value.replace(/\D/g, '').slice(0, 10)">
                    </div>
                    
                    <div class="form-group">
                        <label for="create-cor_usu">Correo</label>
                        <input type="text" placeholder="Correo" id="create-cor_usu" name="cor_usu" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-fna_usu">Fecha Nacimiento</label>
                        <input type="date" placeholder="Correo" id="create-fna_usu" name="fna_usu" class="form-control">
                    </div>
                    
                    <div class="form-group">
                        <label for="create-per_usu">Permiso</label>
                        <select type="text" id="create-per_usu" name="per_usu" class="form-control" onchange="toggleFields()">
                            <option value="">Seleccione una opción</option>
                            <option value="1">ADMINISTRATIVO</option>
                            <option value="3">PROFESIONAL</option>
                        </select>
                    </div>

                    <div class="form-group" id="comision-group" style="display: none;">
                        <label for="create-comision">Porcentaje Comisión</label>
                        <input type="number" placeholder="% de comisión" id="create-comision" name="comision" class="form-control" onkeydown="preventInvalidChars(event)">
                    </div>

                    <div class="form-group" id="tip_pro-group" style="display: none;">
                        <label for="create-tip_pro">Tipo Profesional</label>
                        <select type="text" id="create-tip_pro" name="tip_pro" class="form-control">
                            <option value="">Seleccione una opción</option>
                            <option value="1">PSICÓLOGO</option>
                            <option value="2">PSIQUIATRA</option>
                            <option value="3">NUTRICIONISTA</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create-estado">Estado</label>
                        <select id="create-estado" name="estado" class="form-control" required>
                            <option value="1">ACTIVO</option>
                            <option value="0">INACTIVO</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label>Asignación de Permisos</label><br>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-blog" name="permisos[]" value="blog">
                            <label class="form-check-label" for="permiso-blog">
                                Blog
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-biblioteca" name="permisos[]" value="biblioteca">
                            <label class="form-check-label" for="permiso-biblioteca">
                                Biblioteca
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-citas" name="permisos[]" value="citas">
                            <label class="form-check-label" for="permiso-citas">
                                Citas
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-promociones" name="permisos[]" value="promociones">
                            <label class="form-check-label" for="permiso-promociones">
                                Promociones
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-gastos" name="permisos[]" value="gastos">
                            <label class="form-check-label" for="permiso-gastos">
                                Gastos
                            </label>
                        </div>
                        <div class="form-check">
                            <input class="form-check-input" type="checkbox" id="permiso-citas-pagos" name="permisos[]" value="citas y pagos">
                            <label class="form-check-label" for="permiso-citas-pagos">
                                Citas y Pagos
                            </label>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="create-profesional_asignado">Profesional Asignado</label>
                        <select id="create-profesional_asignado" name="profesional_asignado" class="form-control">
                            <option value="">Seleccione un profesional</option>
                            <?php
                            // Consulta a la base de datos
                            $idprofe = $_SESSION['id'];
                            $sql = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 AND id != $idprofe";
                            $result = mysqli_query($conn, $sql);

                            if ($result && mysqli_num_rows($result) > 0) {
                                while ($row = mysqli_fetch_assoc($result)) {
                                    // Crear las opciones
                                    $id = $row['id'];
                                    $nombreCompleto = trim($row['pn_usu'] . ' ' . $row['sn_usu'] . ' ' . $row['pa_usu'] . ' ' . $row['sa_usu']);
                                    echo "<option value=\"$id\">" . htmlspecialchars($nombreCompleto) . "</option>";
                                }
                            } else {
                                // Si no hay datos
                                echo '<option value="">No hay profesionales disponibles</option>';
                            }
                            ?>
                        </select>
                    </div>

                    <div class="form-group mb-0" id="descripcion-group" style="display: none;">
                        <label for="create-descripcion">Descripción</label>
                        <textarea class="form-control" id="create-descripcion" name="descripcion" style="height: 200px !important; resize: none;"></textarea>
                    </div>

                    <script>
                        function preventInvalidChars(event) {
                            const invalidChars = ['.', ',', 'e'];
                            if (invalidChars.includes(event.key)) {
                                event.preventDefault();
                            }
                        }

                        function toggleFields() {
                            const permiso = document.getElementById("create-per_usu").value;
                            const comisionGroup = document.getElementById("comision-group");
                            const descipcionGroup = document.getElementById("descripcion-group");
                            const tipProGroup = document.getElementById("tip_pro-group");
                            const comisionInput = document.getElementById("create-comision");
                            const descripcionInput = document.getElementById("create-descripcion");
                            const tipProSelect = document.getElementById("create-tip_pro");

                            if (permiso === "3") { // PSICÓLOGO seleccionado
                                comisionGroup.style.display = "block";
                                descipcionGroup.style.display = "block";
                                tipProGroup.style.display = "block";
                                comisionInput.setAttribute("required", "true");
                                descripcionInput.setAttribute("required", "true");
                                tipProSelect.setAttribute("required", "true");
                            } else {
                                comisionGroup.style.display = "none";
                                descipcionGroup.style.display = "none";
                                tipProGroup.style.display = "none";
                                comisionInput.removeAttribute("required");
                                descripcionInput.removeAttribute("required");
                                tipProSelect.removeAttribute("required");
                            }
                        }
                    </script>

                  </div>
                  <div class="modal-footer">
                      <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                      <button type="submit" class="btn btn-primary">Guardar Usuario</button>
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
                      <li><strong>Pais:</strong> <span id="confirm-create-pais"></span></li>
                      <li><strong>Tipo de documento:</strong> <span id="confirm-create-td_usu"></span></li>
                      <li><strong>Número de documento:</strong> <span id="confirm-create-numdoc"></span></li>
                      <li><strong>Primer Nombre:</strong> <span id="confirm-create-pn_usu"></span></li>
                      <li><strong>Segundo Nombre:</strong> <span id="confirm-create-sn_usu"></span></li>
                      <li><strong>Primer Apellido:</strong> <span id="confirm-create-pa_usu"></span></li>
                      <li><strong>Segundo Apellido:</strong> <span id="confirm-create-sa_usu"></span></li>
                      <li><strong>Teléfono:</strong> <span id="confirm-create-tel_usu"></span></li>
                      <li><strong>Correo:</strong> <span id="confirm-create-cor_usu"></span></li>
                      <li><strong>Fecha Nacimiento:</strong> <span id="confirm-create-fna_usu"></span></li>
                      <li><strong>Comisión:</strong> <span id="confirm-create-comision"></span></li>
                      <li><strong>Permiso:</strong> <span id="confirm-create-per_usu"></span></li>
                      <li><strong>Estado:</strong> <span id="confirm-create-estado"></span></li>
                      <li><strong>Descripción:</strong> <span id="confirm-create-descripcion"></span></li>
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
