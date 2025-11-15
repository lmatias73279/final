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
                          <th style="text-align: center;">Historia Clinica</th>
                          <th style="text-align: center;">ID Paciente</th>
                          <th style="text-align: center;">Paciente</th>
                          <th style="text-align: center;">Fecha Consulta</th>
                          <th style="text-align: center;">Valor Recibido</th>
                          <th style="text-align: center;">Comisión a pagar</th>
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
                        $sqlCount = "SELECT COUNT(*) AS total FROM sessions WHERE estado = 6 AND valpsi = 1";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "SELECT * FROM sessions WHERE estado = 6 AND valpsi = 1 LIMIT $offset, $registrosPorPagina";
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
                            echo '<td style="text-align: center;">$ ' . number_format($row['valor'], 0, ',', '.') . '</td>';
                            echo '<td style="text-align: center;">$ ' . number_format($row['ingresoRT'], 0, ',', '.') . '</td>';
                            echo '<td style="text-align: center;"><i class="fas fa-eye text-primary" style="cursor: pointer;" onclick="verDetalles(' . $row['ID'] . ')" title="Ver Detalles"></i>&nbsp;&nbsp; <i class="fas fa-check-circle text-info" style="cursor: pointer;" onclick="aprobarComision(' . $row['ID'] . ')" title="Aprobar Comisión"></i>&nbsp;&nbsp; <i class="fas fa-pencil-alt text-warning edit-btn" style="cursor: pointer;" data-id="' . $row['ID'] . '" data-ingreso="' . $row['ingresoRT'] . '" title="Editar Ingreso RT"data-bs-toggle="modal" data-bs-target="#editModal"></i></td>';                    
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
    <!-- Modal de Detalles -->
    <div class="modal" id="modalDetalles" tabindex="-1" aria-labelledby="modalDetallesLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalDetallesLabel">Detalles de la Consulta</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <table class="table table-bordered" id="detallesTabla">
                <!-- Aquí se mostrarán los detalles dinámicamente -->
                </table>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
            </div>
        </div>
    </div>
    
    <!-- Modal de Confirmación -->
    <div class="modal fade" id="modalConfirmar" tabindex="-1" role="dialog" aria-labelledby="modalConfirmarLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalConfirmarLabel">Confirmar Comisión</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <p>¿Está seguro que desea aprobar esta comisión para pago? Este proceso no podrá ser revertido.</p>
                    <form id="formConfirmar" action="pays_validates" method="POST">
                        <input type="hidden" id="idComision" name="idComision" value="">
                        <button type="submit" class="btn btn-primary">Confirmar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <!-- Modal para Editar Ingreso RT -->
    <div class="modal fade" id="editModal" tabindex="-1" role="dialog" aria-labelledby="editModalLabel" aria-hidden="true">
        <div class="modal-dialog" role="document">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="editModalLabel">Editar Ingreso RT</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body">
                    <form id="updateForm" action="pays_actualizarmonto" method="POST">
                        <input type="hidden" id="editId" name="id">
                        <div class="mb-3">
                            <label for="editIngreso" class="form-label">Ingreso RT</label>
                            <input type="number" class="form-control" id="editIngreso" name="ingreso_rt" min="0" required>
                        </div>
                        <button type="submit" class="btn btn-primary">Actualizar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
    function verDetalles(id) {
        // Hacer la solicitud AJAX
        $.ajax({
            url: 'pays_detalles.php', // Archivo PHP que maneja la consulta
            type: 'GET',
            data: { id: id },  // Pasamos el ID
            success: function(response) {
                // Convertir la respuesta a JSON
                const data = JSON.parse(response);

                // Verificamos si la respuesta contiene datos o un error
                if (data.error) {
                    alert(data.error);
                    return;
                }

                // Llenar el modal con los detalles obtenidos
                const detallesTabla = document.getElementById('detallesTabla');
                function formatCurrency(value) {
                    // Aseguramos que el número tenga siempre dos decimales
                    value = parseFloat(value).toFixed(2);

                    // Reemplazamos el punto por coma para separar los decimales
                    value = value.replace('.', ',');

                    // Añadimos separador de miles con un punto
                    let [integer, decimal] = value.split(',');

                    // Agregamos punto como separador de miles
                    integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                    return '$ ' + integer + ',' + decimal;
                }

                function formatPercentage(value) {
                    // Multiplicamos por 100 y aseguramos dos decimales
                    let percentage = (value * 1).toFixed(2);
                    
                    // Reemplazamos el punto por coma para separar los decimales
                    percentage = percentage.replace('.', ',');

                    // Añadimos separador de miles con un punto
                    let [integer, decimal] = percentage.split(',');

                    // Agregamos punto como separador de miles
                    integer = integer.replace(/\B(?=(\d{3})+(?!\d))/g, ".");

                    return integer + ',' + decimal + '%';
                }

                detallesTabla.innerHTML = `
                <div class="tabla">
                    <div class="fila">
                        <div class="celda-titulo">NUC</div>
                        <div class="celda-valor">${id}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">Ingreso Unitario</div>
                        <div class="celda-valor">${formatCurrency(data.valor)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">Ingreso RT</div>
                        <div class="celda-valor">${formatCurrency(data.ingresoRT)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">Ingreso Propio</div>
                        <div class="celda-valor">${formatCurrency(data.ingresoPROPIO)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">IVA</div>
                        <div class="celda-valor">${formatCurrency(data.iva)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">AutoRenta</div>
                        <div class="celda-valor">${formatCurrency(data.autorenta)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">Margen Neto</div>
                        <div class="celda-valor">${formatPercentage(data.margenNeto)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">ICA</div>
                        <div class="celda-valor">${formatCurrency(data.ica)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">Renta</div>
                        <div class="celda-valor">${formatCurrency(data.renta)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">Utilidad Bruta</div>
                        <div class="celda-valor">${formatCurrency(data.utilidadBruta)}</div>
                    </div>
                    <div class="fila">
                        <div class="celda-titulo">Margen Neto</div>
                        <div class="celda-valor">${formatPercentage(data.margenNetoBruto)}</div>
                    </div>
                </div>
                `;

                // Mostrar el modal de detalles
                $('#modalDetalles').modal('show');
            },
            error: function() {
                alert('Hubo un error al obtener los detalles.');
            }
        });
    }
    function aprobarComision(id) {
        // Asignar el ID al campo oculto dentro del modal de confirmación
        document.getElementById('idComision').value = id;

        // Mostrar el modal de confirmación
        $('#modalConfirmar').modal('show');
    }




    </script>
    <style>
    /* Contenedor principal */
    .tabla {
        display: flex;
        flex-direction: column;
        width: 100%;
        font-family: Arial, sans-serif;
        border: 1px solid #ccc;
        margin: 10px 0;
    }

    /* Fila */
    .fila {
        display: flex;
        justify-content: space-between;
        align-items: center;
        height: 30px; /* Ajusta la altura de las filas */
        padding: 0 10px;
        border-bottom: 1px solid #ccc;
    }

    /* Celda de Título (derecha) */
    .celda-titulo {
        font-weight: bold;          /* Negrita */
        text-align: right;          /* Alineado a la derecha */
        flex: 1;
        padding: 5px;
        font-size: 14px;
        background-color: #f0f0f0; /* Fondo gris claro */
        height: 100%;
    }

    /* Celda de Valor (izquierda) */
    .celda-valor {
        text-align: left;           /* Alineado a la izquierda */
        flex: 1;
        padding: 5px;
        font-size: 14px;
        height: 100%;
        font-weight: normal;        /* No negrita */
    }

    /* Eliminar borde inferior en la última fila */
    .fila:last-child {
        border-bottom: none;
    }

    /* Estilo para un fondo alterno en las celdas */
    .celda-titulo:nth-child(odd),
    .celda-valor:nth-child(odd) {
        background-color: #f9f9f9;
    }
    </style>
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
          } else if (status === 'updatecomi') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Comisioón actualizada correctamente.');
          }
      }
    });

  </script>
  <!-- Modal -->




  <script>
    $(document).ready(function () {
        // Al hacer clic en el icono de edición
        $(".edit-btn").click(function () {
            let id = $(this).data("id");
            let ingreso = $(this).data("ingreso");
            
            $("#editId").val(id);
            $("#editIngreso").val(ingreso);
            
            // Forzar la apertura del modal
            $("#editModal").modal("show");
        });
    });
    </script>    
    <style>
        /* Oculta las flechas del input number */
        input[type=number]::-webkit-inner-spin-button,
        input[type=number]::-webkit-outer-spin-button {
            -webkit-appearance: none;
            margin: 0;
        }
        input[type=number] {
            -moz-appearance: textfield;
        }
    </style>
</body>
</html>
