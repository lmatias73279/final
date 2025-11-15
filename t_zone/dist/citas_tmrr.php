<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99 && $_SESSION['permiso'] !== 3){
    header("location: login");
}

if($_SESSION['permiso_citas'] !== 1 && $_SESSION['numdoc'] !== "1014273279" && $_SESSION['numdoc'] !== "1000693019"){
    header("location: login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Citas Mañana</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">

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
              <div class="breadcrumb-item"><a href="#">Citas</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Citas</h2>
            <?php if($_SESSION['permiso'] === 1 || $_SESSION['permiso'] === 99){ ?>
            <div class="d-flex justify-content-start" style="gap: 10px;">
                <button class="btn btn-primary" style="min-width: 200px;" data-toggle="modal" data-target="#AgendarCitaModal">
                    Agendar Cita
                </button>
                
                <button class="btn" style="background-color: #217346; color: white; border: none; min-width: 200px;"
                    data-toggle="modal" data-target="#GenerarExcelModal"
                    onmouseover="this.style.backgroundColor='#1e5e3e'"
                    onmouseout="this.style.backgroundColor='#217346'">
                    <i class="fas fa-file-excel"></i> Citas Reprogramadas
                </button>
                
                <button class="btn" style="background-color: #217346; color: white; border: none; min-width: 200px;"
                    data-toggle="modal" data-target="#GenerarExcelModal1"
                    onmouseover="this.style.backgroundColor='#1e5e3e'"
                    onmouseout="this.style.backgroundColor='#217346'">
                    <i class="fas fa-file-excel"></i> Citas General
                </button>
            </div>
            <?php } ?>

            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <?php
                      include "../../conexionsm.php";

                      // Número de registros por página
                      $registrosPorPagina = 10;

                      // Página actual
                      $paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                      if ($paginaActual < 1) $paginaActual = 1;

                      // Filtros
                      $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? $_GET['fecha_inicio'] : '';
                      $fechaFin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? $_GET['fecha_fin'] : '';
                      $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
                      $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
                      $psico = isset($_GET['psico']) ? $_GET['psico'] : '';

                      if($fechaInicio === ''){
                        $fechaInicio1 = '1900-01-01';
                      }else{
                        $fechaInicio1 = $fechaInicio;
                      }

                      if($fechaFin === ''){
                        $fechaFin1 = '5000-12-31';
                      }else{
                        $fechaFin1 = $fechaFin;
                      }

                      // Construcción de la consulta con filtros
                      $where = "WHERE fecha != '0000-00-00'";
                      $where .= " AND fecha BETWEEN '$fechaInicio1' AND '$fechaFin1'";

                      if (!empty($estado)) {
                          $where .= " AND estado = '$estado'";
                      }
                      if (!empty($nombre)) {
                          $where .= " AND userID = $nombre";
                      }

                      if($_SESSION['permiso'] === 3){
                        $psico = $_SESSION['id'];
                      }
                      if (!empty($psico)) {
                        $where .= " AND psi = $psico";
                      }

                      // Contar total de registros con filtro
                      $sqlCount = "SELECT COUNT(*) AS total FROM sessions $where";
                      $totalResult = $conn->query($sqlCount);
                      $totalRegistros = $totalResult->fetch_assoc()['total'];

                      // Calcula el número total de páginas
                      $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                      // Calcula el offset para la consulta
                      $offset = ($paginaActual - 1) * $registrosPorPagina;

                      // Consulta SQL con paginación y filtros
                      date_default_timezone_set('America/Bogota');
                      $fechaHoy = date('Y-m-d');
                      $sql = "SELECT * FROM sessions $where 
                        ORDER BY (fecha = '$fechaHoy') DESC, fecha DESC, hora
                        LIMIT $registrosPorPagina OFFSET $offset";
                      $result = $conn->query($sql);
                      ?>

                      <form method="GET">
                          <div class="form-row">
                              <div class="form-group col-md-2">
                                  <label for="fecha_inicio">Fecha Inicio</label>
                                  <input type="date" id="fecha_inicio" name="fecha_inicio" class="form-control" value="<?= $fechaInicio ?>">
                              </div>
                              <div class="form-group col-md-2">
                                  <label for="fecha_fin">Fecha Fin</label>
                                  <input type="date" id="fecha_fin" name="fecha_fin" class="form-control" value="<?= $fechaFin ?>">
                              </div>
                              <div class="form-group col-md-2">
                                  <label for="estado">Estado</label>
                                  <select id="estado" name="estado" class="form-control">
                                      <option value="">Todos</option>
                                      <option value="1" <?= $estado == '1' ? 'selected' : '' ?>>Consulta pendiente de pago</option>
                                      <option value="2" <?= $estado == '2' ? 'selected' : '' ?>>Pendiente Confirmación</option>
                                      <option value="3" <?= $estado == '3' ? 'selected' : '' ?>>Pago sin Cita</option>
                                      <option value="5" <?= $estado == '5' ? 'selected' : '' ?>>Cancelada</option>
                                      <option value="6" <?= $estado == '6' ? 'selected' : '' ?>>Comisión en Validación</option>
                                      <option value="7" <?= $estado == '7' ? 'selected' : '' ?>>Comisión por pagar</option>
                                      <option value="8" <?= $estado == '8' ? 'selected' : '' ?>>Comisión Pagada</option>
                                  </select>
                              </div>

                              <div class="form-group col-md-2">
                              <label for="nombre">Paciente</label>
                              <select id="nombre" name="nombre" class="form-control">
                                  <option value="">Seleccione un usuario</option>
                                  <?php
                                  $sql99 = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu, numdoc FROM usuarios WHERE permiso = 9";
                                  $result99 = $conn->query($sql99);
  
                                  while ($row99 = $result99->fetch_assoc()) {
                                      $selected = ($row99['id'] == $nombre) ? 'selected' : ''; // Compara con $nombre
                                      echo '<option value="' . $row99['id'] . '" ' . $selected . '>';
                                      echo htmlspecialchars(trim(preg_replace('/\s+/', ' ', $row99['numdoc'] . " | " . $row99['pn_usu'] . " " . $row99['sn_usu'] . " " . $row99['pa_usu'] . " " . $row99['sa_usu'])));
                                      echo '</option>';
                                  }
                                  ?>
                              </select>
                              </div>
  
                              <div class="form-group col-md-2">
                              <label for="psico">Profesional</label>
                              <select id="psico" name="psico" class="form-control">
                                  <option value="">Seleccione un usuario</option>
                                  <?php
                                  $sql66 = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 3";
                                  if ($_SESSION['permiso'] === 3) {
                                    $sql66 .= " AND id = " . $_SESSION['id'];
                                  }
                                  $result66 = $conn->query($sql66);
  
                                  while ($row66 = $result66->fetch_assoc()) {
                                      $selected = ($row66['id'] == $psico) ? 'selected' : ''; // Compara con $nombre
                                      echo '<option value="' . $row66['id'] . '" ' . $selected . '>';
                                      echo htmlspecialchars($row66['pn_usu'] . " " . $row66['sn_usu'] . " " . $row66['pa_usu'] . " " . $row66['sa_usu'], ENT_QUOTES, 'UTF-8');
                                      echo '</option>';
                                  }
                                  ?>
                              </select>
                              </div>


                              <script>
                                  $(document).ready(function() {
                                      $('#nombre').select2();
                                  });
                              </script>

                              <div class="form-group col-md-2">
                                <label for="">...</label>
                                  <input type="submit" class="form-group col-md-12 btn btn-primary" value="Filtrar" style="height: 43px;">
                              </div>
                          </div>
                      </form>

                      <table class="table table-bordered table-md">
                          <tr>
                              <th style='text-align: center;'>#</th>
                              <th style='text-align: center;'>Historial</th>
                              <th style='text-align: center;'>Profesional</th>
                              <th style='text-align: center;'>Paciente</th>
                              <th style='text-align: center;'>Fecha</th>
                              <th style='text-align: center;'>Hora</th>
                              <th style='text-align: center;'>Link Meet</th>
                              <th style='text-align: center;'>Lugar</th>
                              <th style='text-align: center;'>Tipo</th>
                              <th style='text-align: center;'>Estado</th>
                              <th style='text-align: center;'>Semaforo</th>
                              <th style='text-align: center;'>Acciones</th>
                          </tr>
                          <?php
                          $count = $offset + 1;
                          date_default_timezone_set('America/Bogota');
                          $fechaActual = date('Y-m-d');

                          while ($row = $result->fetch_assoc()) {
                              $psisql = $row['psi'];
                              $sqlpsi = "SELECT pn_usu, pa_usu FROM usuarios WHERE id = $psisql";
                              $row1 = $conn->query($sqlpsi)->fetch_assoc();

                              $pacsql = $row['userID'];
                              $sqlpac = "SELECT pn_usu, sn_usu, pa_usu, sa_usu, tel_usu, cor_usu, hiscli, valor_base FROM usuarios WHERE id = $pacsql";
                              $row2 = $conn->query($sqlpac)->fetch_assoc();
                              
                              $fechaComision = $row['fecha'];
                              switch ($row['estado']) {
                                  case 1:
                                      $statusText = "Consulta pendiente de pago";
                                      $statusBadge = "warning";
                                      break;
                                  case 2:
                                      if ($fechaComision < $fechaActual) {
                                          $statusText = "Pendiente Confirmación";
                                          $statusBadge = "info";
                                      } elseif ($fechaComision == $fechaActual) {
                                          $statusText = "Consulta en Proceso";
                                          $statusBadge = "orange";
                                      } else {
                                          $statusText = "Consulta Programada";
                                          $statusBadge = "primary";
                                      }
                                      break;
                                  case 3:
                                      $statusText = "Pago sin Cita";
                                      $statusBadge = "purple";
                                      break;
                                  case 5:
                                      $statusText = "Cancelada";
                                      $statusBadge = "danger";
                                      break;
                                  case 6:
                                      $statusText = "Comisión en Validación";
                                      $statusBadge = "yellow";
                                      break;
                                  case 7:
                                      $statusText = "Comisión por pagar";
                                      $statusBadge = "success";
                                      break;
                                  case 8:
                                      $statusText = "Comisión Pagada";
                                      $statusBadge = "dark";
                                      break;
                                  default:
                                      $statusText = "Estado desconocido";
                                      $statusBadge = "secondary";
                                      break;
                              }
                              
                              echo "<tr>";
                              echo "<td style='text-align: center;'>$count</td>";
                              echo "<td style='text-align: center;'>{$row2['hiscli']}</td>";
                              echo "<td style='text-align: center;'>{$row1['pn_usu']} {$row1['pa_usu']}</td>";
                              echo "<td style='text-align: center;'>{$row2['pn_usu']} {$row2['sn_usu']} {$row2['pa_usu']} {$row2['sa_usu']}</a></td>";
                              echo "<td style='text-align: center;'>{$row['fecha']}</td>";
                              echo "<td style='text-align: center;'>{$row['hora']}</td>";
                              echo "<td style='text-align: center;'><a href='{$row['link_ingreso']}'>{$row['link_ingreso']}</a></td>";
                              $siteText = ($row['site'] == 1) ? 'Presencial' : 'Virtual';
                              echo "<td style='text-align: center;'>$siteText</td>";
                              $tiposTerapia = [
                                1 => 'Individual',
                                2 => 'Pareja',
                                5 => 'Familia',
                                6 => 'Psiquiatría',
                                7 => 'Valoración',
                                8 => 'Nutrición',
                                9 => 'Individual Infantil'
                              ];
                            
                              $tipoText = isset($tiposTerapia[$row['tipo']]) ? $tiposTerapia[$row['tipo']] : 'Desconocido';
                            
                              echo "<td style='text-align: center;'>$tipoText</td>";
                              echo "<td style='text-align: center;'><div class='badge badge-$statusBadge'><strong>$statusText</strong></div></td>";
                              echo "<td style='text-align: center;'>";

                              // Consulta para verificar si hay un registro en meetchanges con idse igual a $row['id']
                              $sqlsem = "SELECT COUNT(*) as total FROM meetchanges WHERE idse = ?";
                              $stmt = $conn->prepare($sqlsem);
                              $stmt->bind_param("i", $row['ID']);
                              $stmt->execute();
                              $resultsem = $stmt->get_result();
                              $data = $resultsem->fetch_assoc();

                              // Determinar el color del segundo círculo
                              $color2 = ($row['estado'] == 5) ? 'red' : (($data['total'] > 0) ? 'yellow' : 'green');

                              // Primer círculo (según $row['estado'])
                              $color1 = ($row['ref'] == '') ? 'red' : (($row['estado'] == 1) ? 'yellow' : 'green');
                              echo "<span title='Pago' style='display:inline-block; width:15px; height:15px; border-radius:50%; background-color:$color1; margin-right:5px;'></span>";

                              // Segundo círculo (según la consulta en meetchanges)
                              echo "<span title='Agenda' style='display:inline-block; width:15px; height:15px; border-radius:50%; background-color:$color2; margin-right:5px;'></span>";

                              // Tercer círculo (según $row['consits'])
                              $color3 = ($row['estado'] == 5) ? 'red' : (($row['consits'] == 1) ? 'green' : (($row['consits'] == 2) ? 'red' : 'gray'));
                              echo "<span title='Sesión' style='display:inline-block; width:15px; height:15px; border-radius:50%; background-color:$color3;'></span>";

                              echo "</td>";
                              
                              $esDeshabilitado = ($color1 === 'green');
                              $disabledAttr = $esDeshabilitado ? 'disabled' : '';
                              $dataToggleAttr = $esDeshabilitado ? '' : "data-toggle='modal'";
                              $onClickAttr = $esDeshabilitado ? '' : "onclick='cargarDatosPago(this)'";
                              $colorpago = $esDeshabilitado ? 'btn-black' : "btn-success";

                              $deshabilitarReagendar = in_array($row['estado'], [6, 7, 8]);
                              $disabledReagendarAttr = $deshabilitarReagendar ? 'disabled' : '';
                              $dataToggleReagendarAttr = $deshabilitarReagendar ? '' : "data-toggle='modal'";
                              $colorreagenda = $deshabilitarReagendar ? 'btn-black' : "btn-warning";

                              $deshabilitarCancelar = in_array($row['estado'], [5, 6, 7, 8]);
                              $disabledCancelarAttr = $deshabilitarCancelar ? 'disabled' : '';
                              $dataToggleCancelarAttr = $deshabilitarCancelar ? '' : "data-toggle='modal'";
                              $onClickCancelarAttr = $deshabilitarCancelar ? '' : "onclick='cargarDatosCancelar(this)'";
                              $colorrecancelar = $deshabilitarCancelar ? 'btn-black' : "btn-danger";

                              $deshabilitarReactivar = in_array($row['estado'], [1,2,3,4,6,7,8]);
                              $disabledReactivarAttr = $deshabilitarReactivar ? 'disabled' : '';
                              $dataToggleReactivarAttr = $deshabilitarReactivar ? '' : "data-toggle='modal'";
                              $colorreactivar = $deshabilitarReactivar ? 'btn-black' : "btn-primary";

                              echo "<td style='text-align: center; white-space: nowrap;'>";
                              if($_SESSION['permiso'] === 1 || $_SESSION['permiso'] === 99){
                              echo "<!-- Botón para Cargar Pago -->
                                    <button class='btn $colorpago btn-sm ml-2' 
                                        data-id='{$row['ID']}' 
                                        data-psi='{$row['psi']}' 
                                        data-userid='{$row['userID']}' 
                                        data-valorbase='{$row2['valor_base']}' 
                                        $dataToggleAttr 
                                        data-target='#CargarPagoModal' 
                                        $onClickAttr 
                                        title='Cargar Pagos'
                                        $disabledAttr>
                                        <i class='fas fa-dollar-sign'></i>
                                    </button>";
                              }
                              echo "<button class='btn $colorreagenda btn-sm ml-2' 
                                        data-id='{$row['ID']}' 
                                        data-psi='{$row['psi']}'
                                        $dataToggleReagendarAttr 
                                        data-target='#EditarFechaHoraModal' 
                                        title='Reagendar Cita'
                                        $disabledReagendarAttr>
                                        <i class='fas fa-calendar-alt'></i>
                                    </button>";
                              echo "<!-- Botón para Ver -->
                                    <button class='btn btn-info btn-sm ml-2' 
                                        data-id='" . htmlspecialchars($row['userID'], ENT_QUOTES, 'UTF-8') . "' 
                                        data-nombre='" . htmlspecialchars(trim($row2['pn_usu'] . ' ' . $row2['sn_usu'] . ' ' . $row2['pa_usu'] . ' ' . $row2['sa_usu']), ENT_QUOTES, 'UTF-8') . "' 
                                        data-telefono='" . htmlspecialchars($row2['tel_usu'], ENT_QUOTES, 'UTF-8') . "' 
                                        data-correo='" . htmlspecialchars($row2['cor_usu'], ENT_QUOTES, 'UTF-8') . "'
                                        data-toggle='modal' 
                                        data-target='#VerDetallesModal' 
                                        title='Ver Información'>
                                        <i class='fas fa-eye'></i>
                                    </button>";
                              if($_SESSION['permiso'] === 1 || $_SESSION['permiso'] === 99){
                              echo "<!-- Botón para Cancelar Cita -->
                                    <button class='btn $colorrecancelar btn-sm ml-2' 
                                        data-id='{$row['ID']}' 
                                        $dataToggleCancelarAttr 
                                        data-target='#CancelarCitaModal' 
                                        $onClickCancelarAttr 
                                        title='Cancelar Cita'
                                        $disabledCancelarAttr>
                                        <i class='fas fa-times'></i>
                                    </button>";
                              }
                              echo "<button class='btn $colorreactivar btn-sm ml-2' 
                                        data-id='{$row['ID']}' 
                                        $dataToggleReactivarAttr 
                                        data-target='#ReactivarModal' 
                                        title='Reactivar Cita'
                                        $disabledReactivarAttr>
                                        <i class='fas fa-arrow-up'></i>
                                    </button>";
                              echo "</td>";

                              echo "</tr>";
                              $count++;
                          }
                          ?>
                      </table>
                    </div>
                  </div>
                    <?php
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
                                        <a class="page-link" href="<?= buildPageLink($i, $params) ?>"><?= $i ?></a>
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
        </section>
      </div>

      <?php include "footer.php"; ?>
    </div>
  </div>

  <!-- General JS Scripts -->
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css" rel="stylesheet" />
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
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

  
<?php if($_SESSION['permiso'] === 1 || $_SESSION['permiso'] === 99){?>
<!-- Modal para Agendar Cita -->
<div class="modal fade" id="AgendarCitaModal" tabindex="-1" role="dialog" aria-labelledby="AgendarCitaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="AgendarCitaModalLabel">Agendar Cita</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="busqueda">Buscar paciente:</label>
                    <div class="input-group">
                        <input type="text" class="form-control" id="hiscli" placeholder="Historia Clínica">
                        <input type="text" class="form-control" id="num_documento" placeholder="Número de Documento">
                        <div class="input-group-append">
                            <button type="button" class="btn btn-primary" id="buscar_paciente">Buscar</button>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <label for="pn_usu">Nombre Completo</label>
                    <input type="text" class="form-control" id="pn_usu" readonly>
                </div>
                <div class="form-group">
                    <label for="profesional_asignado">Profesional Asignado</label>
                    <input type="text" class="form-control" id="profesional_asignado" readonly>
                </div>
                <form id="formAgendarCita" method="POST" action="agregar_cita_sessions">
                    <div class="form-group">
                        <label for="fecha">Fecha</label>
                        <input type="hidden" name="id_profesional" id="id_profesional" required>
                        <input type="hidden" name="id_paciente" id="id_paciente" required>
                        <input type="date" class="form-control" id="fecha" name="fecha" required 
                            readonly style="appearance: none; -webkit-appearance: none; -moz-appearance: none;" 
                            onchange="cargarHorasDisponibles()">
                    </div>

                    <div class="form-group">
                        <label for="hora">Hora</label>
                        <select class="form-control" id="hora" name="hora" required></select>
                    </div>

                    <div class="form-group">
                        <label>
                            <input type="checkbox" id="agendarDosHoras" name="agendarDosHoras" onchange="toggleAgendarDosHoras()"> Agendar dos horas
                        </label>
                    </div>

                    <div class="form-group">
                        <label for="tipo_terapia">Tipo de terapia</label>
                        <select class="form-control" id="tipo_terapia" name="tipo_terapia" required>
                            <option value="">Seleccione una cantidad</option>
                            <option value="1">Individual</option>
                            <option value="2">Pareja</option>
                            <option value="5">Familia</option>
                            <option value="6">Psiquiatría</option>
                            <option value="7">Valoración</option>
                            <option value="8">Nutrición</option>
                            <option value="9">Individual Infantil</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="tipo_atencion">Tipo de atención</label>
                        <select class="form-control" id="tipo_atencion" name="tipo_atencion" required>
                            <option value="">Seleccione una cantidad</option>
                            <option value="2">Virtual</option>
                        </select>
                    </div>
                    <button type="submit" class="btn btn-primary">Agendar Cita</button>
                </form>
            </div>
        </div>
    </div>
</div>



<!-- Modal Generar Excel-->
<div class="modal fade" id="GenerarExcelModal" tabindex="-1" role="dialog" aria-labelledby="excelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="excelModalLabel">Generar Reporte de Citas Reprogramadas</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formExcel" action="citas_tmrr_generar_excel" method="POST">
                    <div class="form-group">
                        <label for="fechaDesde">Desde</label>
                        <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" required>
                    </div>
                    <div class="form-group">
                        <label for="fechaHasta">Hasta</label>
                        <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="formExcel" class="btn btn-primary">Generar Excel</button>
            </div>
        </div>
    </div>
</div>



<!-- Modal Generar Excel-->
<div class="modal fade" id="GenerarExcelModal1" tabindex="-1" role="dialog" aria-labelledby="excelModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="excelModalLabel">Generar Reporte de Citas General</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formExcel1" action="citas_tmrr_generar_excel1" method="POST">
                    <div class="form-group">
                        <label for="fechaDesde">Desde</label>
                        <input type="date" class="form-control" id="fechaDesde" name="fechaDesde" required>
                    </div>
                    <div class="form-group">
                        <label for="fechaHasta">Hasta</label>
                        <input type="date" class="form-control" id="fechaHasta" name="fechaHasta" required>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-danger" data-dismiss="modal">Cancelar</button>
                <button type="submit" form="formExcel1" class="btn btn-primary">Generar Excel</button>
            </div>
        </div>
    </div>
</div>
<?php } ?>


<script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script><!-- Incluye moment.js -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment.js/2.29.1/moment.min.js"></script>
<!-- Incluye moment-timezone -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/moment-timezone/0.5.34/moment-timezone-with-data.min.js"></script>
<script>

function llenarTodasLasHorasDelDia() {
    const selectHora = document.getElementById('hora');
    selectHora.innerHTML = '';

    for (let h = 0; h < 24; h++) {
        const horaValue = String(h).padStart(2, '0') + '0000';
        const horaDisplay = moment().startOf('day').add(h, 'hours').format('hh:mm A');

        const option = document.createElement('option');
        option.value = horaValue;
        option.textContent = horaDisplay;
        selectHora.appendChild(option);
    }
}

function cargarHorasDisponibles() {
    const id_profesional = document.getElementById('id_profesional').value;
    const fechaSeleccionada = document.getElementById('fecha').value;
    const selectHora = document.getElementById('hora');

    fetch(`agendar_citas_get_hours.php?id_profesional=${id_profesional}&fecha=${fechaSeleccionada}`)
        .then(response => response.json())
        .then(horasDisponibles => {
            selectHora.innerHTML = '';

            horasDisponibles.forEach(hora => {
                const horaValue = String(hora).padStart(2, '0') + '0000';
                const horaDisplay = moment().startOf('day').add(hora, 'hours').format('hh:mm A');

                const option = document.createElement('option');
                option.value = horaValue;
                option.textContent = horaDisplay;
                selectHora.appendChild(option);
            });

            // Verificar tipo de atención
            $.ajax({
                url: 'agendar_citas_validate_date.php',
                method: 'POST',
                data: {
                    fecha: fechaSeleccionada,
                    id_profesional: id_profesional
                },
                success: function (response) {
                    const tipoAtencionSelect = document.getElementById('tipo_atencion');
                    tipoAtencionSelect.innerHTML = '<option value="">Seleccione tipo de atención</option><option value="2">Virtual</option>';

                    if (response == 'presencial') {
                        const optionPresencial = document.createElement("option");
                        optionPresencial.value = "1";
                        optionPresencial.text = "Presencial";
                        tipoAtencionSelect.appendChild(optionPresencial);
                    }
                }
            });
        })
        .catch(error => {
            console.error('Error al obtener las horas disponibles:', error);
        });
}

function toggleAgendarDosHoras() {
    const agendarDosHoras = document.getElementById('agendarDosHoras');
    const selectHora = document.getElementById('hora');

    if (agendarDosHoras.checked) {
        const horaSeleccionada = selectHora.value;

        // Buscar si existe la siguiente hora (una hora más)
        const opciones = Array.from(selectHora.options);
        const indexActual = opciones.findIndex(opt => opt.value === horaSeleccionada);

        const siguienteOpcion = opciones[indexActual + 1];

        // Validar que exista y que sea exactamente una hora después
        if (!siguienteOpcion) {
            agendarDosHoras.checked = false;
            toastr.error('No es posible agendar dos horas porque no hay una hora continua disponible.');
            return;
        }

        const siguienteHora = siguienteOpcion.value;

        // Validar que sea una hora después exacta (ej. 10:00 AM y 11:00 AM → 100000 y 110000)
        const diferencia = parseInt(siguienteHora) - parseInt(horaSeleccionada);
        if (diferencia !== 10000) {
            agendarDosHoras.checked = false;
            toastr.error('No es posible agendar dos horas porque la hora siguiente no está disponible.');
            return;
        }

    }
}


async function obtenerFechasBloqueadas(id_profesional) {
    const response = await fetch(`agendar_citas_days_exception?id_profesional=${id_profesional}`);
    const data = await response.json();

    return data
        .filter(item => item.status === 0)
        .map(item => {
            // Convertir string a fecha en UTC
            let fechaUTC = new Date(item.date);

            // Ajustar a la zona horaria de Bogotá manualmente
            let opcionesFormato = { timeZone: "America/Bogota", year: "numeric", month: "2-digit", day: "2-digit" };
            let formatoBogota = new Intl.DateTimeFormat("es-CO", opcionesFormato).format(fechaUTC);

            // Convertir formato "DD/MM/YYYY" a objeto Date
            let [dia, mes, año] = formatoBogota.split("/");
            return new Date(`${año}-${mes}-${dia}`);
        });
}


async function inicializarCalendario() {
    const id_profesional = document.getElementById('id_profesional').value;
    const fechasBloqueadas = await obtenerFechasBloqueadas(id_profesional);

    const hoy = new Date();
    hoy.setHours(0, 0, 0, 0); // Asegura que se compare sin la hora

    const picker = new Pikaday({
        field: document.getElementById('fecha'),
        format: 'YYYY-MM-DD',
        disableDayFn: function(date) {
            date.setHours(0, 0, 0, 0); // Normalizar la fecha para comparar sin hora

            // Deshabilitar domingos
            if (date.getDay() === 0) {
                return true;
            }
            // Deshabilitar fechas bloqueadas
            if (fechasBloqueadas.some(fechaBloqueada => fechaBloqueada.getTime() === date.getTime())) {
                return true;
            }
            // Deshabilitar fechas anteriores a hoy
            if (date.getTime() < hoy.getTime()) {
                return true;
            }
            return false;
        }
    });
}


    document.getElementById('hiscli').addEventListener('input', function() {
        if (this.value) {  // Si el campo 'hiscli' tiene valor
            document.getElementById('num_documento').value = '';  // Borra 'num_documento'
        }
    });

    document.getElementById('num_documento').addEventListener('input', function() {
        if (this.value) {  // Si el campo 'num_documento' tiene valor
            document.getElementById('hiscli').value = '';  // Borra 'hiscli'
        }
    });
    document.getElementById('buscar_paciente').addEventListener('click', function() {
        const hiscli = document.getElementById('hiscli').value;
        const num_documento = document.getElementById('num_documento').value;

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
                document.getElementById('pn_usu').value = data.nombre_paciente;
                document.getElementById('profesional_asignado').value = data.profesional_asignado;
                document.getElementById('id_profesional').value = data.id_profesional;
                document.getElementById('id_paciente').value = data.id_paciente;

                inicializarCalendario();

            } else {
                alert('No se encontraron resultados.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Ocurrió un error al buscar la información.');
        });
    });


</script>













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
                <form id="formCargarPago" method="POST" enctype="multipart/form-data" action="citas_tmrr_cargar_pagos?<?php echo http_build_query($_GET); ?>">
                    <input type="hidden" name="id_profesional_car" id="id_profesional_car">
                    <input type="hidden" name="id_paciente_car" id="id_paciente_car">
                    <input type="hidden" name="valor" id="valor">

                    <div class="form-group">
                        <label for="tipo_terapia_car">Tipo de Terapia</label>
                        <select class="form-control" id="tipo_terapia_car" name="tipo_terapia_car" required>
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
                        <input type="date" class="form-control" id="fecpareal" name="fecpareal" required value="<?php echo date('Y-m-d'); ?>">
                    </div>

                    <div class="form-group">
                        <label for="observaciones">Observaciones (opcional)</label>
                        <input type="text" class="form-control" id="observaciones" name="observaciones" placeholder="Observaciones">
                    </div>

                    <div class="form-group">
                        <label for="soporte">Soporte (opcional)</label>
                        <input type="file" class="form-control" id="soporte" name="soporte">
                    </div>

                    <button type="submit" class="btn btn-primary">Guardar Pago</button>
                </form>
            </div>
        </div>
    </div>
</div>

<!-- Modal para Editar Fecha y Hora -->
<div class="modal fade" id="EditarFechaHoraModal" tabindex="-1" role="dialog" aria-labelledby="EditarFechaHoraModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="EditarFechaHoraModalLabel">Editar Fecha y Hora</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formEditarFecha" method="post" action="citas_tmrr_reagendar?<?php echo http_build_query($_GET); ?>">
                    <input type="hidden" id="editar_id" name="editar_id">
                    <input type="hidden" id="editar_id_profesional" name="editar_id_profesional">

                    <div class="form-group">
                        <label for="nueva_fecha">Nueva Fecha</label>
                        <input type="date" class="form-control" id="nueva_fecha" name="nueva_fecha" required readonly>
                    </div>
                    
                    <div class="form-group">
                        <label for="nueva_hora">Nueva Hora</label>
                        <select class="form-control" id="nueva_hora" name="nueva_hora" required></select>
                    </div>

                    <div class="form-group">
                        <label for="observareage">Observaciones (opcional)</label>
                        <input type="text" class="form-control" id="observareage" name="observareage">
                    </div>

                    <button type="submit" class="btn btn-warning">Actualizar</button>
                </form>
            </div>
        </div>
    </div>
</div>

<script>

document.addEventListener("DOMContentLoaded", function () {
    $(document).on("click", "[data-target='#EditarFechaHoraModal']", function () {
        let id = $(this).data("id"); // Obtiene el ID del registro
        let id_profesional = $(this).data("psi"); // Obtiene el ID del profesional

        console.log("ID del registro:", id); // 🔍 Verifica en consola
        console.log("ID del profesional:", id_profesional); // 🔍 Verifica en consola

        // Asigna los valores a los inputs ocultos
        $("#editar_id").val(id);
        $("#editar_id_profesional").val(id_profesional);
    });
});




document.addEventListener("DOMContentLoaded", function () {
    let pickerEditarFecha = new Pikaday({
        field: document.getElementById("nueva_fecha"),
        format: "YYYY-MM-DD",
        disableDayFn: function (date) {
            date.setHours(0, 0, 0, 0);

            //Habilita el día actual
            const today = new Date();
            today.setHours(0, 0, 0, 0);

            return date.getDay() === 0 || date < today; // Bloquea domingos y días pasados
        },
        onSelect: async function (date) {
            let fechaSeleccionada = moment(date).format("YYYY-MM-DD");
            let id_profesional = document.getElementById("editar_id_profesional").value;

            if (id_profesional) {
                await cargarHorasDisponibles(id_profesional, fechaSeleccionada);
            }
        },
    });

    async function cargarHorasDisponibles(id_profesional, fechaSeleccionada) {
        try {
            const response = await fetch(`agendar_citas_get_hours.php?id_profesional=${id_profesional}&fecha=${fechaSeleccionada}`);
            const horasDisponibles = await response.json();

            // Seleccionar el <select> dentro del modal
            let selectHoras = document.getElementById("nueva_hora");

            // Limpiar opciones anteriores
            selectHoras.innerHTML = "";

            // Verificar si hay horas disponibles
            if (horasDisponibles.length === 0) {
                let option = new Option("No hay horas disponibles", "");
                selectHoras.appendChild(option);
                return;
            }

            // Agregar opciones al <select>
            horasDisponibles.forEach(hora => {
                // Convertir la hora a formato HHMMSS
                const horaValue = String(hora).padStart(2, '0') + '0000'; // Ejemplo: 05 -> "050000"

                // Formatear la hora en "hh:mm A" (Ej: 05:00 AM)
                const horaDisplay = moment().startOf('day').add(hora, 'hours').format('hh:mm A');

                const option = document.createElement("option");
                option.value = horaValue;  // Ejemplo: "050000"
                option.textContent = horaDisplay;  // Ejemplo: "05:00 AM"
                selectHoras.appendChild(option);
            });

        } catch (error) {
            console.error("Error al obtener horas:", error);
        }
    }



    document
        .getElementById("EditarFechaHoraModal")
        .addEventListener("show.bs.modal", function (event) {
            let button = event.relatedTarget;
            let id = button.getAttribute("data-id");
            let id_profesional = button.getAttribute("data-psi");

            document.getElementById("editar_id").value = id;
            document.getElementById("editar_id_profesional").value = id_profesional;

            pickerEditarFecha.setDate(null); // Resetea la fecha seleccionada
        });
});

</script>


<!-- Modal -->
<div class="modal fade" id="VerDetallesModal" tabindex="-1" role="dialog" aria-labelledby="VerDetallesModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="VerDetallesModalLabel">Detalles del Usuario</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <p><strong>Nombre Completo:</strong> <span id="detalle_nombre"></span></p>
                <p><strong>Teléfono:</strong> <span id="detalle_telefono"></span></p>
                <p><strong>Correo Electrónico:</strong> <span id="detalle_correo"></span></p>
            </div>
        </div>
    </div>
</div>
<script>
$(document).ready(function() {
    $('.btn-info').on('click', function () {
        var button = $(this); // Se refiere al botón que hizo clic

        // Obtener los valores de los atributos data-*
        var nombre = button.attr('data-nombre');
        var telefono = button.attr('data-telefono');
        var correo = button.attr('data-correo');

        // Llenar el modal con los datos
        $('#detalle_nombre').text(nombre);
        $('#detalle_telefono').text(telefono);
        $('#detalle_correo').text(correo);

        // Mostrar el modal manualmente
        $('#VerDetallesModal').modal('show');
    });
});
</script>

<div class="modal fade" id="CancelarCitaModal" tabindex="-1" role="dialog" aria-labelledby="CancelarCitaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="CancelarCitaModalLabel">Cancelar Cita</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center">
                <p><strong>¿Está seguro que desea cancelar esta cita?</strong></p>
                <p class="text-danger">Este proceso es irreversible.</p>
            </div>
            
            <div class="modal-body">
                <form id="formCancelarCita" method="post" action="citas_tmrr_cancelar?<?php echo http_build_query($_GET); ?>">

                    <input type="hidden" id="id_cita_cancelar" name="id_cita_cancelar">

                    <div class="form-group">
                        <label for="causal">Causal</label>
                        <select type="date" class="form-control" id="causal" name="causal" required>
                            <option value="">Seleccione una opción</option>
                            <option value="1">Económico</option>
                            <option value="2">Enfoque Terapeutico</option>
                            <option value="3">Match</option>
                            <option value="4">No Contesta</option>
                            <option value="5">Tiempo</option>
                            <option value="6">Varias reprogramaciones</option>
                            <option value="7">No llegó a sesión</option>
                            <option value="8">Otros</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="juntificacion">¿se da por facturado?</label>
                        <select class="form-control" id="juntificacion" name="juntificacion" required>
                            <option value="">Selecciones una opción</option>
                            <option value="1">Si</option>
                            <option value="2">No</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="observacancel">Observaciones (opcional)</label>
                        <input type="text" class="form-control" id="observacancel" name="observacancel">
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">No, Volver</button>
                        <button type="submit" class="btn btn-danger">Sí, Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>



<script>
    function cargarDatosPago(button) {
        document.getElementById("id_profesional_car").value = button.getAttribute("data-psi");
        document.getElementById("id_paciente_car").value = button.getAttribute("data-userid");
        document.getElementById("valor").value = button.getAttribute("data-valorbase");
    }

    function cargarDatosEditar(button) {
        let idCita = button.getAttribute("data-id");
        console.log("Editar cita ID:", idCita);
    }

    function cargarDatosVer(button) {
        let idCita = button.getAttribute("data-id");
        document.getElementById("detalle_id").innerText = idCita;
        document.getElementById("detalle_paciente").innerText = "Nombre del Paciente"; // Puedes cargarlo dinámicamente
        document.getElementById("detalle_profesional").innerText = "Profesional Asignado"; // Puedes cargarlo dinámicamente
        document.getElementById("detalle_fecha").innerText = "2025-04-10"; // Puedes cargarlo dinámicamente
        document.getElementById("detalle_hora").innerText = "10:00 AM"; // Puedes cargarlo dinámicamente
    }

    function cargarDatosCancelar(button) {
        let idCita = button.getAttribute("data-id");
        document.getElementById("id_cita_cancelar").value = idCita;
    }

</script>




<!-- Modal para Reactivar citas canceladas -->
<div class="modal fade" id="ReactivarModal" tabindex="-1" role="dialog" aria-labelledby="ReactivarModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="ReactivarModalLabel">Reactivar Sesión</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form id="formEditarFecha" method="post" action="citas_tmrr_reactivar.php">
          <input type="hidden" id="reactivar_id" name="reactivar_id">
          <span>¿Está seguro que desea reactivar esta cita?</span>
          <div class="mt-3 text-right">
            <button type="submit" class="btn btn-primary">Reactivar</button>
          </div>
        </form>
      </div>
    </div>
  </div>
</div>



<script>
$(document).ready(function () {
  $(document).on('click', 'button[data-target="#ReactivarModal"]', function () {
    var id = $(this).data('id');
    $('#reactivar_id').val(id);
  });
});
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
          }else if (status === 'factura_actualizada') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Número de factura asignado correctamente.');
          }else if (status === 'cancelexitosa') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Cancelación exitosa.');
          }else if (status === 'error_insertcancel') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Error al cancelar la cita.');
          }else if (status === 'reagcorr') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Cita reagendada de manera exitosa.');
          }else if (status === 'reactivado_ok') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Cita reactivada de manera exitosa.');
          }else if (status === 'error_update_react') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Ocurrio un error al reactivar la cita.');
          }else if (status === 'no_found') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Cita no existe');
          }else if (status === 'id_invalido') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('ID Invalido.');
          }else if (status === 'metodo_invalido') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.error('Método invalido');
          }
      }
    });
</script>


</body>
</html>
