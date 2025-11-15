<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99){
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
              <div class="breadcrumb-item"><a href="#">Pacientes</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Pacientes</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="#createUserModal">Crear Paciente</button>
            <br></br>
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <!-- Campo de búsqueda -->
                    <!-- Campo de búsqueda con botón -->
                    <?php 

                    $registrosPorPagina = 25;
                    $paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                    if ($paginaActual < 1) $paginaActual = 1;
                    $offset = ($paginaActual - 1) * $registrosPorPagina;

                    function simpleDecrypt($text, $key) {
                        $text = base64_decode($text); // Decodificar desde base64
                        $output = '';
                        for ($i = 0; $i < strlen($text); $i++) {
                            $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
                        }
                        return $output;
                    }// Verificar si hay un filtro en la URL
                    $encryptedFiltro = $_GET['flt'] ?? '';

                    // Desencriptar el filtro
                    $filtro = simpleDecrypt($encryptedFiltro, '2020');
                    ?>
                    <form id="searchForm" method="post" action="users_pac_buscar">
                        <div class="input-group">
                            <input type="text" id="searchInput" name="searchInput" class="form-control" value="<?php echo $filtro;?>" placeholder="Buscar paciente por nombre, documento o historial">
                            <input type="hidden" id="page" name="page" value="<?php echo $paginaActual;?>">
                            <div class="input-group-append">
                                <button class="btn btn-primary" type="submit">Buscar</button>
                            </div>
                        </div>
                    </form>
                    <br>

                    <!-- Tabla de Pacientes -->
                    <div class="table-responsive">
                        <table class="table table-bordered table-md">
                            <thead>
                                <tr>
                                    <th style="text-align: center;">#</th>
                                    <th style="text-align: center;">Historial</th>
                                    <th style="text-align: center;">Nombre</th>
                                    <th style="text-align: center;">Documento</th>
                                    <th style="text-align: center;">Estado</th>
                                    <th style="text-align: center;">Servicio</th>
                                    <th style="text-align: center;">Sesiones sin usar</th>
                                    <th style="text-align: center;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody id="pacientesTable">
                                <?php
                                include "../../conexionsm.php";

                                $condicionAsignado = ($_SESSION['permiso'] == 3) ? " AND profesional_asignado = " . $_SESSION["id"] : "";

                                $filtro = trim($filtro); // Elimina espacios en blanco al inicio y final

                                $condicionFiltro = "";
                                if (!empty($filtro)) {
                                    $filtro = $conn->real_escape_string($filtro); // Evita inyección SQL
                                    $condicionFiltro = "AND (hiscli LIKE '%$filtro%' 
                                                        OR pn_usu LIKE '%$filtro%' 
                                                        OR sn_usu LIKE '%$filtro%' 
                                                        OR pa_usu LIKE '%$filtro%' 
                                                        OR sa_usu LIKE '%$filtro%' 
                                                        OR numdoc LIKE '%$filtro%')";
                                }

                                $sqlCount = "SELECT COUNT(*) AS total FROM usuarios WHERE permiso IN (9, 10) $condicionAsignado $condicionFiltro";
                                $totalResult = $conn->query($sqlCount);
                                $totalRegistros = $totalResult->fetch_assoc()['total'];

                                $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                                $sql = "SELECT id, hiscli, pn_usu, sn_usu, pa_usu, sa_usu, numdoc, estado, proceso FROM usuarios WHERE permiso IN (9, 10) AND delest != 9 $condicionAsignado $condicionFiltro LIMIT $offset, $registrosPorPagina";
                                $result = $conn->query($sql);

                                $count = $offset + 1;
                                while ($row = $result->fetch_assoc()) {
                                    $statusText = $row['estado'] == 1 ? 'Activo' : 'Inactivo';
                                    $statusBadge = $row['estado'] == 1 ? 'success' : 'danger';

                                    // Consultar la cantidad de sesiones activas del usuario
                                    $userID = $row['id'];
                                    $querySessions = "SELECT COUNT(*) AS total FROM sessions WHERE `order` <> '' AND userID = ? AND estado = 3";
                                    $stmt = $conn->prepare($querySessions);
                                    $stmt->bind_param("i", $userID);
                                    $stmt->execute();
                                    $resultSessions = $stmt->get_result();
                                    $sessinus = ($resultSessions->num_rows > 0) ? $resultSessions->fetch_assoc()['total'] : 0;
                                    $stmt->close();
                                    
                                    echo '<tr class="paciente-row">';
                                    echo '<td style="text-align: center;">' . $count . '</td>';
                                    echo '<td style="text-align: center;" class="hiscli">' . htmlspecialchars($row['hiscli']) . '</td>';
                                    echo '<td style="text-align: center;" class="pn_usu">' . htmlspecialchars($row['pn_usu']) . " " . htmlspecialchars($row['sn_usu']) . " " . htmlspecialchars($row['pa_usu']) . " " . htmlspecialchars($row['sa_usu']) . '</td>';
                                    echo '<td style="text-align: center;" class="numdoc">' . htmlspecialchars($row['numdoc']) . '</td>';
                                    echo '<td style="text-align: center;"><div class="badge badge-' . $statusBadge . '"><strong>' . $statusText . '</strong></div></td>';
                                    // Valor numérico obtenido de tu base de datos
                                    $proceso = $row['proceso'];

                                    // Traducción de valores
                                    if ($proceso == 1) {
                                        $proceso_texto = 'Individual';
                                    } elseif ($proceso == 2) {
                                        $proceso_texto = 'Pareja';
                                    } elseif ($proceso == 5) {
                                        $proceso_texto = 'Familia';
                                    } elseif ($proceso == 6) {
                                        $proceso_texto = 'Psiquiatría';
                                    } elseif ($proceso == 8) {
                                        $proceso_texto = 'Nutrición';
                                    } elseif ($proceso == 9) {
                                        $proceso_texto = 'Individual Infantil';
                                    } else {
                                        // Valor por defecto si no coincide con ninguno de los anteriores
                                        $proceso_texto = 'Desconocido';
                                    }

                                    // Imprimir en tu tabla
                                    echo '<td style="text-align: center;">' . $proceso_texto . '</td>';
                                    echo '<td style="text-align: center;">' . $sessinus . '</td>';
                                    echo '<td style="text-align: center;">
                                        <button class="btn btn-primary btn-edit" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#exampleModal" title="Editar datos de usuario">
                                            <i class="fas fa-edit"></i>
                                        </button>';
                                    echo '<button class="btn btn-primary btn-firmas ml-2" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#exampleModalFirmas" title="Solicitar firmas de consentimiento">
                                        <i class="fas fa-signature"></i>
                                    </button>';
                                    echo '<button class="btn btn-primary btn-firmas ml-2" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#exampleModalServicios" title="Añadir servicio">
                                        <i class="fas fa-plus"></i>
                                    </button>';
                                    echo '<button class="btn btn-primary btn-firmas ml-2" data-id="' . $row['hiscli'] . '" data-toggle="modal" data-target="#exampleModalDocFir" title="Ver documentos firmados">
                                        <i class="fas fa-eye"></i>
                                    </button>';
                                    echo '<button class="btn btn-primary btn-firmas ml-2" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#exampleModalDelete" title="Ver documentos firmados">
                                        <i class="fas fa-trash"></i>
                                    </button>';
                                    echo'</td>';
                            
                                    echo '</tr>';

                                    $count++;
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>

                    <?php
                    // Obtener el parámetro flt si existe en la URL
                    $flt = isset($_GET['flt']) ? '&flt=' . urlencode($_GET['flt']) : '';
                    
                    // Clona el array $_GET para poder modificarlo sin afectar el original
                    $params = $_GET;
                    
                    // Función para generar el URL con la página deseada
                    function buildPageLink($page, $params) {
                        $params['page'] = $page;
                        return '?' . http_build_query($params);
                    }
                    
                    // Número máximo de páginas visibles
                    $maxPagesVisible = 10;
                    
                    // Calcula el rango de páginas a mostrar
                    $startPage = max(1, $paginaActual - floor($maxPagesVisible / 2));
                    $endPage = min($totalPaginas, $startPage + $maxPagesVisible - 1);
                    
                    // Ajusta el inicio si el rango se desplaza hacia atrás
                    $startPage = max(1, $endPage - $maxPagesVisible + 1);
                    ?>
                    
                    <div class="card-footer text-right">
                        <nav class="d-inline-block">
                            <ul class="pagination mb-0">
                                <!-- Botón de página anterior -->
                                <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= buildPageLink($paginaActual - 1, $params) ?>"><i class="fas fa-chevron-left"></i></a>
                                </li>
                    
                                <!-- Enlaces de paginación con puntos suspensivos -->
                                <?php if ($startPage > 1): ?>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPageLink(1, $params) ?>">1</a>
                                    </li>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                <?php endif; ?>
                    
                                <?php for ($i = $startPage; $i <= $endPage; $i++): ?>
                                    <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
                                        <a class="page-link paginacion-link" href="<?= buildPageLink($i, $params) ?>"><?= $i ?></a>
                                    </li>
                                <?php endfor; ?>
                    
                                <!-- Puntos suspensivos y última página -->
                                <?php if ($endPage < $totalPaginas): ?>
                                    <li class="page-item disabled">
                                        <span class="page-link">...</span>
                                    </li>
                                    <li class="page-item">
                                        <a class="page-link" href="<?= buildPageLink($totalPaginas, $params) ?>"><?= $totalPaginas ?></a>
                                    </li>
                                <?php endif; ?>
                    
                                <!-- Botón de página siguiente -->
                                <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
                                    <a class="page-link" href="<?= buildPageLink($paginaActual + 1, $params) ?>"><i class="fas fa-chevron-right"></i></a>
                                </li>
                            </ul>
                        </nav>
                    </div>


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
          } else if (status === 'success_solfir') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Firmas solicitadas de manera exitosa.');
          } else if (status === 'fail_solfir') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Ocurrió un error al solicitar firmas.');
          } else if (status === 'no_new_records') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.info('Ya existe una solicitud de firma pendiente para este usuario y tipo de consentimiento.');
          } else if (status === 'okservices') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Servicio añadido con exito');
          } else if (status === 'deleted_ok') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Paciente eliminado con exito');
          } else if (status === 'no_update_delete') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('No fue posible eliminar al paciente');
          } else if (status === 'execute_error') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Ocurrió un error al intentar conextar con la base de datos.');
          } else if (status === 'missing_id') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('No se encuentra el ID del paciente');
          }
      }


      // Configuración para editar usuario
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
                  $('#modal-tidfac').val(data.tidfac);
                  $('#modal-idfact').val(data.idfact);
                  $('#modal-nomfac').val(data.nomfac);
                  $('#modal-tidfac').val(data.tidfac);
                  $('#modal-idfact').val(data.idfact);
                  $('#modal-nomfac').val(data.nomfac);
                  $('#modal-bold').val(data.bold);
                  $('#modal-estado').val(data.estado);
                  $('#modal-profesional_asignado').val(data.profesional_asignado);
                  $('#modal-valor_base').val(data.valor_base);
                  $('#modal-valor_pres').val(data.valor_pres);
                  $('#modal-profesion').val(data.profession);
                  $('#modal-sector').val(data.sector);
                  $('#modal-money').val(data.currency);
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
          $('#confirm-td_usu').text($('#modal-td_usu').val());
          $('#confirm-numdoc').text($('#modal-numdoc').val());
          $('#confirm-pn_usu').text($('#modal-pn_usu').val());
          $('#confirm-sn_usu').text($('#modal-sn_usu').val());
          $('#confirm-pa_usu').text($('#modal-pa_usu').val());
          $('#confirm-sa_usu').text($('#modal-sa_usu').val());
          $('#confirm-tel_usu').text($('#modal-tel_usu').val());
          $('#confirm-profesional_asignado').text($('#modal-profesional_asignado option:selected').text());
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
              case '10':
                  texto = 'EMPRESA';
                  break;
              case '9':
                  texto = 'PACIENTE';
                  break;
              default:
                  texto = 'INACTIVO';  // Por defecto si el valor no coincide con ninguno de los casos anteriores
                  break;
          }

          $('#confirm-per_usu').text(texto);
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
          $('#confirm-create-profesional_asignado').text($('#create-profesional_asignado option:selected').text());
          $('#confirm-create-cor_usu').text($('#create-cor_usu').val());
          $('#confirm-create-fna_usu').text($('#create-fna_usu').val());
          $('#confirm-create-proceso').text($('#create-proceso').val());
          var perUsu = $('#create-per_usu').val();
          var texto;

          switch (perUsu) {
              case '1':
                  texto = 'ADMINISTRATIVO';
                  break;
              case '3':
                  texto = 'PSICOLOGO';
                  break;
              case '10':
                  texto = 'EMPRESA';
                  break;
              case '9':
                  texto = 'PACIENTE';
                  break;
              default:
                  texto = 'INACTIVO';  // Por defecto si el valor no coincide con ninguno de los casos anteriores
                  break;
          }

          $('#confirm-create-per_usu').text(texto);
          $('#confirm-create-estado').text($('#create-estado').val() == '10' ? 'ACTIVO' : 'INACTIVO');

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
              <form id="edit-form" action="update_pac" method="POST">
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
                          <option value="RC">RC</option>
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
                        <input type="number" id="modal-tel_usu" name="tel_usu" class="form-control" 
                            required pattern="\d+" 
                            title="Por favor, ingrese un número de teléfono válido sin espacios ni caracteres especiales." 
                            placeholder="Ej. 1234567890"
                            oninput="this.value = this.value.replace(/\D/g, '')">
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-cor_usu">Correo</label>
                        <input type="text" id="modal-cor_usu" name="cor_usu" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="modal-fna_usu">Fecha Nacimiento</label>
                        <input type="date" id="modal-fna_usu" name="fna_usu" class="form-control">
                    </div>
                    <?php 
                    $permisoactual = $_SESSION['permiso'];
                    
                    if($permisoactual === 3){
                        $displaynone = "style='display:none;'";
                        $required = "";
                        $selected = "selected";
                        $valueval = 0;
                    }else{
                        $displaynone = "";
                        $selected = "";
                        $valueval = "";
                        $required = "required";
                    }
                    ?>
                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="modal-per_usu">Permiso</label>
                        <select type="text" id="modal-per_usu" name="per_usu" class="form-control" required>
                          <option value="">Seleccione una opción</option>
                          <option value="9">PACIENTE</option>
                          <option value="10">EMPRESA</option>
                          <?php
                          if($_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){
                          ?>
                          <option value="1">ADMINISTRATIVO</option>
                          <?php
                          }
                          ?>
                        </select>
                    </div>

                    <div id="facturacion-extra">
                        <div <?php echo $displaynone;?> class="form-group">
                            <label for="modal-tidfac">Tipo ID Factura</label>
                            <select id="modal-tidfac" name="tidfac" class="form-control">
                                <option value="">Seleccione una opción</option>
                                <option value="CC">CC</option>
                                <option value="CE">CE</option>
                                <option value="NIT">NIT</option>
                                <option value="TI">TI</option>
                                <option value="PT">PT</option>
                            </select>
                        </div>

                        <div <?php echo $displaynone;?> class="form-group">
                            <label for="modal-idfact">ID Factura</label>
                            <input type="number" placeholder="ID Factura" id="modal-idfact" name="idfact" class="form-control" <?php echo $required;?> >
                        </div>

                        <div <?php echo $displaynone;?> class="form-group">
                            <label for="modal-nomfac">Nombre Factura</label>
                            <input type="text" placeholder="Nombre Factura" id="modal-nomfac" name="nomfac" class="form-control" <?php echo $required;?> >
                        </div>
                    </div>

                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="modal-bold">Pagos Bold</label>
                        <select id="modal-bold" name="bold" class="form-control" required>
                            <option value="1">PERMITIR</option>
                            <option value="0">NO PERMITIR</option>
                        </select>
                    </div>

                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="modal-estado">Estado</label>
                        <select id="modal-estado" name="estado" class="form-control" required>
                            <option value="1">ACTIVO</option>
                            <option value="0">INACTIVO</option>
                        </select>
                    </div>

                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="modal-profesional_asignado">Profesional</label>
                        <select id="modal-profesional_asignado" name="profesional_asignado" class="form-control" required>
                            <option value="">Seleccione un profesional</option>
                            <?php

                            // Consulta a la base de datos
                            $idprofe = $_SESSION['id'];

                            if ($permisoactual == 3) {
                                // Si el permiso es 3, mostrar solo donde id = $idprofe
                                $sql = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 AND id = $idprofe";
                            } else {
                                // Si el permiso es diferente de 3, mostrar donde id != $idprofe
                                $sql = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 AND id != $idprofe";
                            }

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
                    
                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="modal-valor_base">Valor Virtual</label>
                        <input type="number" placeholder="Valor Virtual" id="modal-valor_base" name="valor_base" class="form-control" <?php echo $required;?> >
                    </div>
                    
                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="modal-valor_pres">Valor Presencial</label>
                        <input type="number" placeholder="Valor Presencial" id="modal-valor_pres" name="valor_pres" class="form-control" <?php echo $required;?> >
                    </div>

                    <div class="form-group">
                        <label for="modal-profesion">Profesión (opcional)</label>
                        <select id="modal-profesion" name="profesion" class="form-control">
                            <option value="">Seleccione un proceso</option>
                            <option value="1">Sin empleo | Estudiante</option>
                            <option value="2">Empleado Opetativo</option>
                            <option value="3">Hosteleria y Viajes</option>
                            <option value="4">Ingenieros y programadores</option>
                            <option value="5">Marketing y ventas</option>
                            <option value="6">Arquitectos</option>
                            <option value="7">Educación</option>
                            <option value="8">Médicos</option>
                            <option value="9">Abogados</option>
                            <option value="10">Pensionados</option>
                            <option value="11">Economistas y contadores</option>
                            <option value="12">Ciencias humanas y comunicación</option>
                            <option value="13">Diseñadores</option>
                            <option value="14">Políticos</option>
                            <option value="15">Independientes</option>
                            <option value="16">Extranjero Empleado | Estudiante</option>
                            <option value="17">Extrangero Profesional</option>
                            <option value="18">Extrangero Magister</option>
                            <option value="19">Extranjero Doctor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal-sector">Sector (opcional)</label>
                        <select id="modal-sector" name="sector" class="form-control">
                            <option value="">Seleccione un proceso</option>
                            <option value="1">Norte</option>
                            <option value="2">Sur</option>
                            <option value="3">Oriente</option>
                            <option value="4">Occidente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="modal-money">Moneda (opcional)</label>
                        <select id="modal-money" name="money" class="form-control">
                            <option value="">Seleccione un proceso</option>
                            <option value="1">COP</option>
                            <option value="2">USD</option>
                            <option value="3">EUR</option>
                        </select>
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
                      <li><strong>Pais:</strong> <span id="confirm-pais"></span></li>
                      <li><strong>Tipo de documento:</strong> <span id="confirm-td_usu"></span></li>
                      <li><strong>Número de documento:</strong> <span id="confirm-numdoc"></span></li>
                      <li><strong>Primer Nombre:</strong> <span id="confirm-pn_usu"></span></li>
                      <li><strong>Segundo Nombre:</strong> <span id="confirm-sn_usu"></span></li>
                      <li><strong>Primer Apellido:</strong> <span id="confirm-pa_usu"></span></li>
                      <li><strong>Segundo Apellido:</strong> <span id="confirm-sa_usu"></span></li>
                      <li><strong>Teléfono:</strong> <span id="confirm-tel_usu"></span></li>
                      <li><strong>Correo:</strong> <span id="confirm-cor_usu"></span></li>
                      <li><strong>Fecha Nacimiento:</strong> <span id="confirm-fna_usu"></span></li>
                      <li <?php echo $displaynone;?>><strong>Permiso:</strong> <span id="confirm-per_usu"></span></li>
                      <li <?php echo $displaynone;?>><strong>Estado:</strong> <span id="confirm-estado"></span></li>
                      <li <?php echo $displaynone;?>><strong>Profesional:</strong> <span id="confirm-profesional_asignado"></span></li>
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
                  <h5 class="modal-title" id="createUserLabel">Crear Paciente</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">×</span>
                  </button>
              </div>
              <form id="create-form" action="create_user_pac.php" method="POST">
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
                          <option value="RC">RC</option>
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
                        <input type="text" placeholder="Teléfono" id="create-tel_usu" name="tel_usu" class="form-control" 
                            required pattern="\d*" 
                            onkeypress="return event.charCode >= 48 && event.charCode <= 57"
                            title="Por favor, ingrese solo números.">
                    </div>

                    
                    <div class="form-group">
                        <label for="create-cor_usu">Correo</label>
                        <input type="text" placeholder="Correo" id="create-cor_usu" name="cor_usu" class="form-control" required>
                    </div>
                    
                    <div class="form-group">
                        <label for="create-fna_usu">Fecha Nacimiento</label>
                        <input type="date" placeholder="Correo" id="create-fna_usu" name="fna_usu" class="form-control">
                    </div>
                                
                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="create-per_usu">Permiso</label>
                        <select type="text" id="create-per_usu" name="per_usu" class="form-control" required>
                          <option value="">Seleccione una opción</option>
                          <option <?php echo $selected;?> value="9">PACIENTE</option>
                          <option value="10">EMPRESA</option>
                        </select>
                    </div>
                    

                    <div <?php echo $displaynone;?> class="form-group">
                        <label>
                            <input type="checkbox" id="same-as-patient1" checked>
                            ¿Los datos de facturación son los mismos que los del paciente?
                        </label>
                    </div>

                    <div id="facturacion-extra1">
                        <div class="form-group">
                            <label for="create-tidfac">Tipo ID Factura</label>
                            <select id="create-tidfac" name="tidfac" class="form-control">
                                <option value="">Seleccione una opción</option>
                                <option value="CC">CC</option>
                                <option value="CE">CE</option>
                                <option value="NIT">NIT</option>
                                <option value="TI">TI</option>
                                <option value="PT">PT</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="create-idfact">ID Factura</label>
                            <input type="number" placeholder="ID Factura" id="create-idfact" name="idfact" class="form-control">
                        </div>

                        <div class="form-group">
                            <label for="create-nomfac">Nombre Factura</label>
                            <input type="text" placeholder="Nombre Factura" id="create-nomfac" name="nomfac" class="form-control">
                        </div>
                    </div>

                    <script>
                        document.addEventListener('DOMContentLoaded', function () {
                            const checkbox = document.getElementById('same-as-patient1');
                            const extraSection = document.getElementById('facturacion-extra1');
                            const tidfac = document.getElementById('create-tidfac');
                            const idfact = document.getElementById('create-idfact');
                            const nomfac = document.getElementById('create-nomfac');

                            function toggleFacturaFields() {
                                if (checkbox.checked) {
                                    extraSection.style.display = 'none';
                                    tidfac.value = '';
                                    idfact.value = '';
                                    nomfac.value = '';
                                    tidfac.removeAttribute('required');
                                    idfact.removeAttribute('required');
                                    nomfac.removeAttribute('required');
                                } else {
                                    extraSection.style.display = 'block';
                                    tidfac.setAttribute('required', 'required');
                                    idfact.setAttribute('required', 'required');
                                    nomfac.setAttribute('required', 'required');
                                }
                            }

                            checkbox.addEventListener('change', toggleFacturaFields);

                            // Ejecutar al cargar la página también
                            toggleFacturaFields();
                        });
                    </script>

                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="create-bold">Pagos Bold</label>
                        <select id="create-bold" name="bold" class="form-control" required>
                            <option value="1">PERMITIR</option>
                            <option <?php echo $selected;?> value="0">NO PERMITIR</option>
                        </select>
                    </div>

                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="create-estado">Estado</label>
                        <select id="create-estado" name="estado" class="form-control" required>
                            <option <?php echo $selected;?> value="1">ACTIVO</option>
                            <option value="0">INACTIVO</option>
                        </select>
                    </div>

                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="create-profesional_asignado">Profesional</label>
                        <select id="create-profesional_asignado" name="profesional_asignado" class="form-control" required>
                            <?php

                            if ($permisoactual == 3) {
                                // Si el permiso es 3, mostrar solo donde id = $idprofe
                                $sql = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 AND id = $idprofe";
                            } else {
                                // Si el permiso es diferente de 3, mostrar donde id != $idprofe
                                $sql = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 AND id != $idprofe";
                                echo '<option value="">Seleccione un profesional</option>';
                            }

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
                            ?>
                        </select>
                    </div>
                    
                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="create-valor_base">Valor Virtual</label>
                        <input type="number" value="<?php echo $valueval;?>" placeholder="Valor Virtual" id="create-valor_base" name="valor_base" class="form-control" <?php echo $required;?>>
                    </div>
                    
                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="create-valor_pres">Valor Presencial</label>
                        <input type="number" value="<?php echo $valueval;?>" placeholder="Valor Presencial" id="create-valor_pres" name="valor_pres" class="form-control" <?php echo $required;?>>
                    </div>

                    <div class="form-group">
                        <label for="create-profesion">Profesión (opcional)</label>
                        <select id="create-profesion" name="profesion" class="form-control">
                            <option value="">Seleccione un proceso</option>
                            <option value="1">Sin empleo | Estudiante</option>
                            <option value="2">Empleado Opetativo</option>
                            <option value="3">Hosteleria y Viajes</option>
                            <option value="4">Ingenieros y programadores</option>
                            <option value="5">Marketing y ventas</option>
                            <option value="6">Arquitectos</option>
                            <option value="7">Educación</option>
                            <option value="8">Médicos</option>
                            <option value="9">Abogados</option>
                            <option value="10">Pensionados</option>
                            <option value="11">Economistas y contadores</option>
                            <option value="12">Ciencias humanas y comunicación</option>
                            <option value="13">Diseñadores</option>
                            <option value="14">Políticos</option>
                            <option value="15">Independientes</option>
                            <option value="16">Extranjero Empleado | Estudiante</option>
                            <option value="17">Extrangero Profesional</option>
                            <option value="18">Extrangero Magister</option>
                            <option value="19">Extranjero Doctor</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create-sector">Sector (opcional)</label>
                        <select id="create-sector" name="sector" class="form-control">
                            <option value="">Seleccione un proceso</option>
                            <option value="1">Norte</option>
                            <option value="2">Sur</option>
                            <option value="3">Oriente</option>
                            <option value="4">Occidente</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="create-money">Moneda (opcional)</label>
                        <select id="create-money" name="money" class="form-control">
                            <option value="">Seleccione un proceso</option>
                            <option value="1">COP</option>
                            <option value="2">USD</option>
                            <option value="3">EUR</option>
                        </select>
                    </div>

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
                      <li <?php echo $displaynone;?>><strong>Profesional:</strong> <span id="confirm-create-profesional_asignado"></span></li>
                      <li <?php echo $displaynone;?>><strong>Permiso:</strong> <span id="confirm-create-per_usu"></span></li>
                      <li <?php echo $displaynone;?>><strong>Estado:</strong> <span id="confirm-create-estado"></span></li>
                      <li><strong>Proceso:</strong> <span id="confirm-create-proceso"></span></li>
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

    <!-- Modal para solicitar firmas -->
    <div class="modal fade" id="exampleModalFirmas" tabindex="-1" role="dialog" aria-labelledby="firmasLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="firmasLabel">Solicitar Firmas</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="firmasForm" action="solicitar_firmas.php" method="POST">
                        <input type="hidden" name="id" id="firmasId">
                        
                        <div class="form-group">
                            <label>Seleccione los documentos a firmar:</label><br>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="documentos[]" value="Psicología" id="psicologia">
                                <label class="form-check-label" for="psicologia">Psicología</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="documentos[]" value="Psiquiatría" id="psiquiatria">
                                <label class="form-check-label" for="psiquiatria">Psiquiatría</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="documentos[]" value="Adultos" id="adultos">
                                <label class="form-check-label" for="adultos">Adultos</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="documentos[]" value="Pareja" id="pareja">
                                <label class="form-check-label" for="pareja">Pareja</label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" name="documentos[]" value="Niños" id="ninos">
                                <label class="form-check-label" for="ninos">Niños</label>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Solicitar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function() {
        $('.btn-firmas').click(function() {
        var id = $(this).data('id'); 
        $('#firmasId').val(id); 
        });
    });
    </script>





<!-- Modal -->
<div class="modal fade" id="exampleModalServicios" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Añadir Servicio</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <form action="users_pac_new_service.php" method="POST" id="serviceForm">
                <div class="modal-body">
                    <input type="hidden" id="modal-id" name="id">

                    <!-- Select con las opciones -->
                    <div class="form-group">
                        <label for="new_process">Seleccione un proceso:</label>
                        <select id="new_process" name="new_process" class="form-control" required>
                            <option value="">Seleccione un proceso</option>
                            <option value="2">Pareja</option>
                            <option value="5">Familia</option>
                            <option value="6">Psiquiatría</option>
                            <option value="8">Nutrición</option>
                            <option value="9">Individual Infantil</option>
                        </select>
                    </div>

                    <!-- Campo para acompañantes -->
                    <div class="form-group" id="acompanantesGroup" style="display: none;">
                        <label for="cantidad_acompanantes">Cantidad de acompañantes:</label>
                        <input type="number" id="cantidad_acompanantes" name="cantidad_acompanantes" class="form-control" min="1" max="10">
                    </div>

                    <!-- Contenedor para los datos de los acompañantes -->
                    <div id="acompanantes_fields"></div>

                    <!-- Detalles adicionales para Individual Infantil -->
                    <div id="individual_infantil_details" style="display: none;">
                        <h6>Acudiente</h6>
                        <div class="form-group">
                            <label>Tipo de documento:</label>
                            <select type="text" name="tipdoc" class="form-control">
                                <option value="">Seleccione una opción</option>
                                <option value="CC">CC</option>
                                <option value="CE">CE</option>
                                <option value="TI">TI</option>
                                <option value="PT">PT</option>
                                <option value="RC">RC</option>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Número de documento:</label>
                            <input type="number" name="numdoc" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="nombre_completo">Nombre completo:</label>
                            <input type="text" id="nombre_completo" name="nombre_completo" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="correo">Correo:</label>
                            <input type="email" id="correo" name="correo" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="telefono">Teléfono:</label>
                            <input type="tel" id="telefono" name="telefono" class="form-control">
                        </div>
                        <div class="form-group">
                            <label for="consanguinidad">Consanguinidad:</label>
                            <select id="consanguinidad" name="consanguinidad" class="form-control">
                                <option value="">Seleccione una opción</option>
                                <option value="1">Padre</option>
                                <option value="2">Madre</option>
                                <option value="3">Hijo/a</option>
                                <option value="4">Esposo/a</option>
                                <option value="5">Pareja</option>
                                <option value="6">Amigo/a</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="profesional_asignado">Profesional</label>
                        <select id="profesional_asignado" name="profesional_asignado" class="form-control" required>
                            <option value="">Seleccione un profesional</option>
                            <?php

                            // Consulta a la base de datos
                            $idprofe = $_SESSION['id'];

                            if ($permisoactual == 3) {
                                // Si el permiso es 3, mostrar solo donde id = $idprofe
                                $sql = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 AND id = $idprofe";
                            } else {
                                // Si el permiso es diferente de 3, mostrar donde id != $idprofe
                                $sql = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3 AND id != $idprofe";
                            }

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

                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="valns">Valor Virtual</label>
                        <input type="number" placeholder="Valor base de consulta" id="valns" name="valns" class="form-control" <?php echo $required;?> >
                    </div>

                    <div <?php echo $displaynone;?> class="form-group">
                        <label for="valpe">Valor Presencial</label>
                        <input type="number" placeholder="Valor base de consulta" id="valpe" name="valpe" class="form-control" <?php echo $required;?> >
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



<!-- Modal Delete -->
<div class="modal fade" id="exampleModalDelete" tabindex="-1" role="dialog" aria-labelledby="modalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <form action="users_pac_delete.php" method="POST" id="serviceForm">
        <div class="modal-header">
          <h5 class="modal-title" id="modalLabel">Confirmar Eliminación</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <input type="hidden" id="modal-id" name="id">
          <p>¿Estás seguro que deseas eliminar este paciente?, 
            <span style="color:red;"><strong>Este proceso es irreversible</strong></span>
          </p>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
        </div>
      </form>
    </div>
  </div>
</div>

<script>
$(document).ready(function() {
  // Al abrir el modal, obtener el data-id del botón que lo llamó
  $('#exampleModalDelete').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Botón que activó el modal
    var id = button.data('id'); // Extraer info del atributo data-id
    var modal = $(this);
    modal.find('#modal-id').val(id); // Ponerlo en el input hidden
  });
});
</script>




<!-- Script para manejar acompañantes dinámicos -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const newProcessSelect = document.getElementById('new_process');
    const cantidadInput = document.getElementById('cantidad_acompanantes');
    const acompanantesGroup = document.getElementById('acompanantesGroup');
    const acompanantesFields = document.getElementById('acompanantes_fields');
    const individualInfantilDetails = document.getElementById('individual_infantil_details');

    function crearCamposAcompanantes(cantidad) {
        acompanantesFields.innerHTML = '';
        for (let i = 1; i <= cantidad; i++) {
            acompanantesFields.innerHTML += `
                <div class="acompanante">
                    <h6>Acompañante ${i}</h6>
                    <div class="form-group">
                        <label>Tipo de documento:</label>
                        <select type="text" name="acompanantes${i}tipdoc" class="form-control">
                            <option value="">Seleccione una opción</option>
                            <option value="CC">CC</option>
                            <option value="CE">CE</option>
                            <option value="TI">TI</option>
                            <option value="PT">PT</option>
                            <option value="RC">RC</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label>Número de documento:</label>
                        <input type="number" name="acompanantes${i}numdoc" class="form-control">
                    </div>
                    <div class="form-group">
                        <label>Nombre completo:</label>
                        <input type="text" name="acompanantes${i}nombre" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-group">
                        <label>Correo:</label>
                        <input type="email" name="acompanantes${i}correo" class="form-control" required oninput="this.value = this.value.toUpperCase()">
                    </div>
                    <div class="form-group">
                        <label>Teléfono:</label>
                        <input type="number" name="acompanantes${i}telefono" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label>Consanguinidad:</label>
                        <select name="acompanantes${i}consanguinidad" class="form-control" required>
                            <option value="">Seleccione</option>
                            <option value="1">Padre</option>
                            <option value="2">Madre</option>
                            <option value="3">Hijo/a</option>
                            <option value="4">Esposo/a</option>
                            <option value="5">Pareja</option>
                            <option value="6">Amigo/a</option>
                        </select>
                    </div>
                    <hr>
                </div>
            `;
        }
    }

    function limpiarCamposIndividualInfantil() {
        const inputs = individualInfantilDetails.querySelectorAll('input, select');
        inputs.forEach(input => {
            input.value = '';
        });
    }


    newProcessSelect.addEventListener('change', function () {
        const selected = this.value;
        acompanantesFields.innerHTML = '';
        individualInfantilDetails.style.display = 'none';
        acompanantesGroup.style.display = 'none';
        cantidadInput.value = '';
        limpiarCamposIndividualInfantil();

        if (selected === '9') {
            individualInfantilDetails.style.display = 'block';
        } else if (['2', '3', '4', '5'].includes(selected)) {
            acompanantesGroup.style.display = 'block';
            let cantidadFija = 0;
            if (selected === '2') cantidadFija = 1;
            if (selected === '3') cantidadFija = 2;
            if (selected === '4') cantidadFija = 3;

            if (selected !== '5') {
                cantidadInput.value = cantidadFija;
                cantidadInput.readOnly = true;
                crearCamposAcompanantes(cantidadFija);
            } else {
                cantidadInput.readOnly = false;
            }
        }
    });

    cantidadInput.addEventListener('input', function () {
        const cant = parseInt(this.value);
        if (!isNaN(cant) && cant > 0) {
            crearCamposAcompanantes(cant);
        } else {
            acompanantesFields.innerHTML = '';
        }
    });
});
</script>

<script>
$('#exampleModalServicios').on('show.bs.modal', function (event) {
    var button = $(event.relatedTarget); // Botón que disparó el modal
    var id = button.data('id'); // Extraer el data-id
    var modal = $(this);
    modal.find('#modal-id').val(id); // Asignar el ID al input oculto
});
</script>



<!-- Script para pasar el ID al input oculto -->
<script>
    $(document).ready(function(){
        $('.btn-firmas').on('click', function(){
            let id = $(this).data('id');
            $('#modal-id').val(id);
        });
    });
</script>



    <!-- Modal para solicitar firmas -->
    <div class="modal fade" id="exampleModalDocFir" tabindex="-1" role="dialog" aria-labelledby="firmasLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="firmasLabel">Consentimientos</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                </div>
            </div>
        </div>
    </div>
    <script>
    $(document).ready(function () {
        $('#exampleModalDocFir').on('show.bs.modal', function (event) {
            var button = $(event.relatedTarget);
            var hiscli = button.data('id'); // Obtener el hiscli del botón
            var modal = $(this);

            $.ajax({
                url: 'users_pac_get_consentimientos.php',
                method: 'POST',
                data: { hiscli: hiscli },
                success: function (response) {
                    modal.find('.modal-body').html(response);
                },
                error: function () {
                    modal.find('.modal-body').html('<p>Error al cargar los documentos.</p>');
                }
            });
        });
    });
    </script>

</body>
</html>
