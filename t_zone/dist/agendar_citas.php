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

if($_SESSION['permiso_citas_pagos'] !== 1 && $_SESSION['numdoc'] !== "1014273279" && $_SESSION['numdoc'] !== "1000693019"){
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
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">


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
              <div class="breadcrumb-item"><a href="#">Citas</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Agendar Citas</h2>
            <button class="btn btn-primary" data-toggle="modal" data-target="#CargarPagoModal">Cargar Pago</button>
            <br></br>
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-md">
                        <tr>
                          <th style="text-align: center;">#</th>
                          <th style="text-align: center;">Paciente</th>
                          <th style="text-align: center;">Psicologo</th>
                          <th style="text-align: center;">Tipo Tetapia</th>
                          <th style="text-align: center;">Tipo Atención</th>
                          <th style="text-align: center;">Fecha</th>
                          <th style="text-align: center;">Hora</th>
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
                        $sqlCount = "SELECT COUNT(*) AS total FROM sessions WHERE estado = 1 and titulo = '' ";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta SQL para obtener los registros de la página actual
                        $sql = "SELECT id, site, tipo, userID, psi, fecha, hora, estado FROM sessions WHERE estado = 1 and titulo = '' LIMIT $offset, $registrosPorPagina";
                        $result = $conn->query($sql);

                        // Inicializamos un contador para el número de fila
                        $count = $offset + 1;

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {
                            switch ($row['estado']) {
                                case 1:
                                    $statusText = 'Pte Pago';
                                    $statusBadge = '#FFB347'; 
                                    break;
                                case 2:
                                    $statusText = 'Agendada';
                                    $statusBadge = '#acabed'; 
                                    break;
                                case 3:
                                    $statusText = 'Pago Sin cita';
                                    $statusBadge = '#77DD77'; 
                                    break;
                                case 5:
                                    $statusText = 'Cancelada';
                                    $statusBadge = '#edabab'; 
                                    break;
                                case 6:
                                    $statusText = 'En validación';
                                    $statusBadge = '#FFB347'; 
                                    break;
                                case 7:
                                    $statusText = 'Pendiente pago';
                                    $statusBadge = '#acabed'; 
                                    break;
                                case 8:
                                    $statusText = 'Pagada';
                                    $statusBadge = '#77DD77'; 
                                    break;
                                default:
                                    $statusText = 'Desconocido';
                                    $statusBadge = '#D3D3D3'; 
                                    break;
                            }                                
                            
                            $tipoTerapia = $row['tipo'];
                            switch ($tipoTerapia) {
                                case 1:
                                    $tipoTerapiaText = 'Individual';
                                    break;
                                case 2:
                                    $tipoTerapiaText = 'P&F X2';
                                    break;
                                case 3:
                                    $tipoTerapiaText = 'P&F X3';
                                    break;
                                case 4:
                                    $tipoTerapiaText = 'P&F X4';
                                    break;
                                case 5:
                                    $tipoTerapiaText = 'P&F X5';
                                    break;
                                case 6:
                                    $tipoTerapiaText = 'Psiquiátrica';
                                    break;
                                case 7:
                                    $tipoTerapiaText = 'Valoración';
                                    break;
                                case 8:
                                    $tipoTerapiaText = 'Nutrición';
                                    break;
                                default:
                                    $tipoTerapiaText = 'Tipo de terapia desconocido';
                                    break;
                            }

                            $tipoAtencion = $row['site'];
                            switch ($tipoAtencion) {
                                case 1:
                                    $tipoAtencionText = 'Presencial';
                                    break;
                                case 2:
                                    $tipoAtencionText = 'Virtual';
                                    break;
                                default:
                                    $tipoAtencionText = 'Tipo de arención desconocido';
                                    break;
                            }
                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            // Obtener los datos del usuario para userID
                            $sqlUserID = "SELECT pn_usu, pa_usu FROM usuarios WHERE id = ?";
                            $stmtUserID = $conn->prepare($sqlUserID);
                            $stmtUserID->bind_param("i", $row['userID']);
                            $stmtUserID->execute();
                            $resultUserID = $stmtUserID->get_result();
                            $userIDData = $resultUserID->fetch_assoc();
                            $userIDText = $userIDData ? $userIDData['pn_usu'] . ' ' . $userIDData['pa_usu'] : '';

                            // Obtener los datos del usuario para psi
                            $sqlPsi = "SELECT pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE id = ?";
                            $stmtPsi = $conn->prepare($sqlPsi);
                            $stmtPsi->bind_param("i", $row['psi']);
                            $stmtPsi->execute();
                            $resultPsi = $stmtPsi->get_result();
                            $psiData = $resultPsi->fetch_assoc();
                            $psiText = $psiData ? $psiData['pn_usu'] . ' ' . $psiData['sn_usu'] . ' ' . $psiData['pa_usu'] . ' ' . $psiData['sa_usu'] : '';

                            // Mostrar los datos en la tabla
                            echo '<td style="text-align: center;">' . htmlspecialchars(ucwords(strtolower($userIDText))) . '</td>';
                            echo '<td style="text-align: center;">' . htmlspecialchars(ucwords(strtolower($psiText))) . '</td>';


                            echo '<td style="text-align: center;">' . $tipoTerapiaText . '</td>';
                            echo '<td style="text-align: center;">' . $tipoAtencionText . '</td>';
                            echo '<td style="text-align: center;">' . $row['fecha'] . '</td>';
                            echo '<td style="text-align: center;">' . $row['hora'] . '</td>';
                            echo '<td style="text-align: center;"><div class="badge" style="background-color: ' . $statusBadge . '; color: grey;"><strong>' . $statusText . '</strong></div></td>';
                            echo '<td style="text-align: center;"><button class="btn btn-danger btn-cancelar" data-id="' . $row['id'] . '" data-toggle="modal" data-target="#exampleModal"><i class="fas fa-times-circle"></i> Cancelar</button></td>';
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
<!-- Modal para Cargar Pago -->
<div class="modal fade" id="CargarPagoModal" tabindex="-1" role="dialog" aria-labelledby="CargarPagoModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CargarPagoModalLabel">Cargar Pago</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="busqueda">Buscar paciente:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="hisclicar" placeholder="Historia Clínica">
                        <input type="text" class="form-control" id="num_documentocar" placeholder="Número de Documento">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" id="buscar_paciente_car">Buscar</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pn_usu">Nombre Completo</label>
                    <input type="text" class="form-control" id="pn_usu_car" readonly>
                </div>
                <div class="form-group">
                    <label for="profesional_asignado">Profesional Asignado</label>
                    <input type="text" class="form-control" id="profesional_asignado_car" readonly>
                </div>
                <form id="formCargarPago" method="POST" enctype="multipart/form-data" action="agendar_citas_cargar_pagos">
                    <div class="form-group">
                        <label for="tipo_terapia_car">Tipo de terapia</label>
                        <input type="hidden" name="id_profesional_car" id="id_profesional_car" required>
                        <input type="hidden" name="id_paciente_car" id="id_paciente_car" required>
                        <input type="hidden" name="valor" id="valor" required>
                        <select class="form-control" id="tipo_terapia_car" name="tipo_terapia_car" required onchange="calcularCosto()">
                            <option value="">Seleccione un tipo de terapia</option>
                            <option value="1">Individual</option>
                            <option value="2">Pareja</option>
                            <option value="5">Familia</option>
                            <option value="6">Psiquiatría</option>
                            <option value="8">Nutrición</option>
                            <option value="9">Individual Infantil</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="cantidad_car">Cantidad</label>
                        <select type="number" polaceholder="Cantidad_car" class="form-control" id="cantidad_car" name="cantidad_car" required onchange="calcularCosto()">
                            <option value="">Seleccione una cantidad</option>
                            <option value="1">1</option>
                            <option value="2">2</option>
                            <option value="3">3</option>
                            <option value="4">4</option>
                            <option value="5">5</option>
                            <option value="6">6</option>
                            <option value="7">7</option>
                            <option value="8">8</option>
                            <option value="9">9</option>
                            <option value="10">10</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo_atencion_car">Tipo de Atención</label>
                        <select class="form-control" id="tipo_atencion_car" name="tipo_atencion_car" required onchange="calcularCosto()">
                            <option value="">Seleccione una opción</option>
                            <option value="1">Presencial</option>
                            <option value="2">Virtual</option>
                        </select>
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
                        <label for="currency">Moneda</label>
                        <select class="form-control" id="currency" name="currency" required>
                            <option value="">Seleccione una opción</option>
                            <option value="1">COP</option>
                            <option value="2">USD</option>
                            <option value="3">EUR</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="valor_real">Valor</label>
                        <input type="number" class="form-control" id="valor_real" name="valor_real" placeholder="Valor a pagar" required>
                    </div>
                    <div class="form-group">
                        <label for="ref">Referencia</label>
                        <input type="text" class="form-control" id="ref" name="ref" placeholder="Referencia" required onchange="verificarReferencia()">
                    </div>
                    <script>
                        function verificarReferencia() {
                            let referencia = $('#ref').val().trim();

                            if (referencia === "") return; // Si está vacío, no hace nada

                            $.ajax({
                                url: 'citas_tmrr_verificar_ref.php', // Archivo PHP que consultará la BD
                                type: 'POST',
                                data: { ref: referencia },
                                dataType: 'json',
                                success: function(response) {
                                    if (response.exists) {
                                        toastr.warning(`Esta referencia ya existe con la Fecha: ${response.fecha}, Valor: ${response.valor}`);
                                        // Borrar el input
                                        $('#ref').val('');
                                    } else {
                                        $('#refInfo').hide();
                                    }
                                },
                                error: function() {
                                    toastr.error('Error al verificar la referencia.');
                                }
                            });
                        }
                    </script>
                    <div class="form-group">
                        <label for="fecpareal">Fecha de pago</label>
                        <input type="date" class="form-control" id="fecpareal" name="fecpareal" required value="<?php echo date('Y-m-d'); ?>>
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones (opcional)</label>
                        <input type="text" class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones">
                    </div>

                    <div class="form-group">
                        <label for="soporte">Soporte</label>
                        <input type="file" class="form-control" id="soporte" name="soporte">
                    </div>
                    <button type="submit" class="btn btn-primary">Guardar Pago</button>
                </form>
            </div>
        </div>
    </div>
</div>


<script>

    document.getElementById('hisclicar').addEventListener('input', function() {
        if (this.value) {  // Si el campo 'hiscli' tiene valor
            document.getElementById('num_documentocar').value = '';  // Borra 'num_documento'
        }
    });
    document.getElementById('num_documentocar').addEventListener('input', function() {
        if (this.value) {  // Si el campo 'num_documento' tiene valor
            document.getElementById('hisclicar').value = '';  // Borra 'hiscli'
        }
    });
    document.getElementById('buscar_paciente_car').addEventListener('click', function() {
        const hiscli = document.getElementById('hisclicar').value;
        const num_documento = document.getElementById('num_documentocar').value;

        // Realizar la búsqueda en la base de datos
        fetch('agendar_citas_buscar_pacientes.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
            },
            body: JSON.stringify({ hiscli, num_documento}),
        })
        .then(response => response.json())
        .then(data => {
            if (data) {
                // Autocompletar los campos del formulario
                document.getElementById('pn_usu_car').value = data.nombre_paciente;
                document.getElementById('profesional_asignado_car').value = data.profesional_asignado;
                document.getElementById('id_profesional_car').value = data.id_profesional;
                document.getElementById('id_paciente_car').value = data.id_paciente;
                document.getElementById('currency').value = data.currency;
                document.getElementById('valor').value = data.valor;
            } else {
                alert('No se encontraron resultados.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al buscar la información.');
        });
    });






function calcularCosto() {
    const tipoTerapia = parseInt(document.getElementById("tipo_terapia_car").value);
    const tipoAtencion = parseInt(document.getElementById("tipo_atencion_car").value);
    const cantidad = parseInt(document.getElementById("cantidad_car").value) || 1;
    const idPaciente = document.getElementById("id_paciente_car").value; // Obtener el ID del input hidden
    let cost = parseInt(document.getElementById("valor").value) || 0;

    if (!tipoTerapia || !tipoAtencion || !cantidad) {
        return; // Si faltan datos, no hacer cálculos
    }

    // Hacer petición AJAX para obtener valor_base
    fetch('agendar_citas_obtener_valor_base.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: 'id_paciente=' + encodeURIComponent(idPaciente)
    })
    .then(response => response.json())
    .then(data => {
        if (tipoTerapia === 1) {
            if (tipoAtencion === 1) {
                cost = cost <= 85000 ? 90000 : cost + 5000;
            }
        } else if (tipoTerapia === 2) {
            if (cost <= 80000) {
                cost = tipoAtencion === 1 ? 140000 : 130000;
            } else if (cost === 85000) {
                cost = tipoAtencion === 1 ? 145000 : 140000;
            } else if (cost <= 90000) {
                cost = tipoAtencion === 1 ? 155000 : 150000;
            } else if (cost <= 95000) {
                cost = tipoAtencion === 1 ? 160000 : 155000;
            } else if (cost >= 10000) {
                cost = tipoAtencion === 1 ? 165000 : 160000;
            }
        } else if (tipoTerapia === 3) {
            if (cost <= 80000) {
                cost = tipoAtencion === 1 ? 155000 : 150000;
            } else if (cost === 85000) {
                cost = tipoAtencion === 1 ? 165000 : 160000;
            } else if (cost <= 90000) {
                cost = tipoAtencion === 1 ? 175000 : 170000;
            } else if (cost <= 95000) {
                cost = tipoAtencion === 1 ? 185000 : 180000;
            } else if (cost >= 10000) {
                cost = tipoAtencion === 1 ? 195000 : 190000;
            }
        } else if (tipoTerapia === 4) {
            if (cost <= 80000) {
                cost = tipoAtencion === 1 ? 165000 : 160000;
            } else if (cost === 85000) {
                cost = tipoAtencion === 1 ? 175000 : 170000;
            } else if (cost <= 90000) {
                cost = tipoAtencion === 1 ? 185000 : 180000;
            } else if (cost <= 95000) {
                cost = tipoAtencion === 1 ? 195000 : 190000;
            } else if (cost >= 10000) {
                cost = tipoAtencion === 1 ? 200000 : 195000;
            }
        } else if (tipoTerapia === 5) {
            if (cost <= 80000) {
                cost = tipoAtencion === 1 ? 175000 : 170000;
            } else if (cost === 85000) {
                cost = tipoAtencion === 1 ? 185000 : 180000;
            } else if (cost <= 90000) {
                cost = tipoAtencion === 1 ? 195000 : 190000;
            } else if (cost <= 95000) {
                cost = tipoAtencion === 1 ? 205000 : 200000;
            } else if (cost >= 10000) {
                cost = tipoAtencion === 1 ? 210000 : 205000;
            }
        } else if (tipoTerapia === 6) {
            if (cost <= 80000) {
                cost = tipoAtencion === 1 ? 160000 : 150000;
            } else if (cost === 85000) {
                cost = tipoAtencion === 1 ? 175000 : 160000;
            } else if (cost <= 90000) {
                cost = tipoAtencion === 1 ? 180000 : 165000;
            } else if (cost <= 95000) {
                cost = tipoAtencion === 1 ? 185000 : 170000;
            } else if (cost === 10000) {
                cost = tipoAtencion === 1 ? 195000 : 180000;
            } else if (cost === 10500) {
                cost = tipoAtencion === 1 ? 200000 : 185000;
            } else if (cost >= 11000) {
                cost = tipoAtencion === 1 ? 210000 : 195000;
            }
        }
        if (data.valor_base !== 0) {
            cost = data.valor_base; // Si el valor base en BD es distinto de 0, usarlo
        }   

        cost *= cantidad; // Multiplicar por cantidad
        document.getElementById("valor_real").value = cost; // Asignar al campo de valor
    })
    .catch(error => console.error('Error al obtener valor base:', error));
}


</script>

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
      const encryptedStatus = urlParams.get('sta');

      if (encryptedStatus) {
          const status = simpleDecrypt(encryptedStatus, '2020'); // Descifra usando la clave

          if (status === 'success_insert') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('El paciente se agendó correctamente.');
          } else if (status === 'error_insert') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Error en la base de datos');
          } else if (status === 'success_update') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('El paciente se agendó correctamente.');
          } else if (status === 'cancorr') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Cita cancelada exitosamente.');
          } else if (status === 'error_update') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Error en la base de datos');
          } else if (status === 'error_exists') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('El paciente ya tiene una cita agendada para la misma fecha y hora');
          }else if (status === 'carpagext') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Pago cargado correctamente.');
          }else if (status === 'atsok') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Actualización de tokens de Google efectuada exitosamente, por favor intente agendar de nuevo la cita.');
          }
      }
    });
</script>
<!-- Modal de confirmación -->
<div class="modal fade" id="modalConfirmarCancelacion" tabindex="-1" aria-labelledby="modalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalLabel">Confirmar Cancelación</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                ¿Estás seguro de que deseas cancelar esta cita?
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">No</button>
                <button type="button" class="btn btn-danger" id="btnConfirmarCancelacion">Sí, Cancelar</button>
            </div>
        </div>
    </div>
</div>


<!-- Script para manejar el clic en el botón de cancelar -->
<script>
$(document).ready(function () {
    var citaId = null; // Variable para almacenar el ID de la cita seleccionada

    // Manejar el clic en el botón "Cancelar"
    $('.btn-cancelar').on('click', function () {
        citaId = $(this).data('id'); // Guardar el ID de la cita seleccionada
        $('#modalConfirmarCancelacion').modal('show'); // Mostrar el modal de confirmación
    });

    // Manejar el clic en el botón "Sí, Cancelar" dentro del modal
    $('#btnConfirmarCancelacion').on('click', function () {
        if (citaId) {
            // Redirigir a la página PHP con el ID de la cita como parámetro
            window.location.href = 'agendar_citas_cancelar.php?id=' + encodeURIComponent(citaId);
        }
    });
});
</script>

</script>

</body>
</html>
