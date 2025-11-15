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
include "../../conexionsm.php";

$sqlbancos = "SELECT medio_pago, SUM(valor) as total FROM sessions GROUP BY medio_pago";
$result = $conn->query($sqlbancos);

// Inicializar variables por defecto en 0
$paypal = 0;
$bold = 0;
$payu = 0;
$efectivo = 0;
$bancolombia_emp = 0;
$bancolombia_per = 0;
$nequi = 0;
$daviplata = 0;

if ($result) {
    while ($row = $result->fetch_assoc()) {
        $medio = (int)$row['medio_pago'];
        $total = (float)$row['total'];

        switch ($medio) {
            case 1: $paypal = $total; break;
            case 2: $bold = $total; break;
            case 3: $payu = $total; break;
            case 4: $efectivo = $total; break;
            case 5: $bancolombia_emp = $total; break;
            case 6: $bancolombia_per = $total; break;
            case 7: $nequi = $total; break;
            case 8: $daviplata = $total; break;
        }
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Tablero</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="assets/modules/jqvmap/dist/jqvmap.min.css">
  <link rel="stylesheet" href="assets/modules/summernote/summernote-bs4.css">
  <link rel="stylesheet" href="assets/modules/owlcarousel2/dist/assets/owl.carousel.min.css">
  <link rel="stylesheet" href="assets/modules/owlcarousel2/dist/assets/owl.theme.default.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">
<!-- Start GA -->
<script async src="https://www.googletagmanager.com/gtag/js?id=UA-94034622-3"></script>
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
      <?php include "nav.php"?>

<!-- Main Content -->
<div class="main-content">
  <section class="section">
    <div class="container-fluid">
      <div class="row mb-4">
        <?php if($_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){?>
        <div class="col-12">
          <div class="card shadow rounded-lg p-4">
            <h4>Saldo en Bancos</h4>
            <table class="table table-striped table-bordered" style="table-layout: fixed; width: 100%;">
              <thead class="table-dark">
                <tr style="text-align: center;">
                  <th style="color:white;">Paypal</th>
                  <th style="color:white;">Bold</th>
                  <th style="color:white;">PayU</th>
                  <th style="color:white;">Efectivo</th>
                  <th style="color:white;">Bancolombia (Empresa)</th>
                  <th style="color:white;">Bancolombia (Personal)</th>
                  <th style="color:white;">Nequi</th>
                  <th style="color:white;">Daviplata</th>
                </tr>
              </thead>
              <tbody>
                <tr style="text-align: center;">
                  <td>$ <?= number_format($paypal, 0, ',', '.') ?></td>
                  <td>$ <?= number_format($bold, 0, ',', '.') ?></td>
                  <td>$ <?= number_format($payu, 0, ',', '.') ?></td>
                  <td>$ <?= number_format($efectivo, 0, ',', '.') ?></td>
                  <td>$ <?= number_format($bancolombia_emp, 0, ',', '.') ?></td>
                  <td>$ <?= number_format($bancolombia_per, 0, ',', '.') ?></td>
                  <td>$ <?= number_format($nequi, 0, ',', '.') ?></td>
                  <td>$ <?= number_format($daviplata, 0, ',', '.') ?></td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <?php } ?>
        <?php
        // Conteo de Psicólogos Activos
        $sql = "SELECT COUNT(*) AS count_psychologists FROM usuarios WHERE permiso = 3 AND estado = 1";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $countPsychologists = $row['count_psychologists'];

        // Conteo de Pacientes Únicos
        $sql = "SELECT COUNT(DISTINCT numdoc) AS count_patients FROM usuarios WHERE permiso = 9";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $countPatients = $row['count_patients'];

        // Conteo de Sesiones Realizadas
        $sql = "SELECT COUNT(*) AS count_sessions FROM sessions WHERE estado > 5";
        $result = mysqli_query($conn, $sql);
        $row = mysqli_fetch_assoc($result);
        $countSessions = $row['count_sessions'];
        ?>

        <!-- Psicólogos Activos -->
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
          <div class="card card-statistic-2 shadow rounded-lg h-100">
            <div class="card-icon bg-primary text-white">
              <i class="fas fa-users"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4 class="text-primary">Psicólogos Activos</h4>
              </div>
              <div class="card-body" id="active-psychologists"><?php echo $countPsychologists; ?></div>
            </div>
          </div>
        </div>

        <!-- Pacientes -->
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
          <div class="card card-statistic-2 shadow rounded-lg h-100">
            <div class="card-icon bg-success text-white">
              <i class="fas fa-user-check"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4 class="text-success">Pacientes</h4>
              </div>
              <div class="card-body" id="attended-patients"><?php echo $countPatients; ?></div>
            </div>
          </div>
        </div>

        <!-- Sesiones Realizadas -->
        <div class="col-lg-4 col-md-6 col-sm-12 mb-4">
          <div class="card card-statistic-2 shadow rounded-lg h-100">
            <div class="card-icon bg-warning text-white">
              <i class="fas fa-calendar-check"></i>
            </div>
            <div class="card-wrap">
              <div class="card-header">
                <h4 class="text-warning">Sesiones Realizadas</h4>
              </div>
              <div class="card-body" id="completed-sessions"><?php echo $countSessions; ?></div>
            </div>
          </div>
        </div>
      <div class="row mb-4">
        <div class="col-12">
          <div class="card shadow rounded-lg p-4">
            <h4>Resumen de pagos</h4>
            <?php
              // Filtros
              $fechaInicio = isset($_GET['fecha_inicio']) && $_GET['fecha_inicio'] !== '' ? $_GET['fecha_inicio'] : '';
              $fechaFin = isset($_GET['fecha_fin']) && $_GET['fecha_fin'] !== '' ? $_GET['fecha_fin'] : '';
              $estado = isset($_GET['estado']) ? $_GET['estado'] : '';
              $nombre = isset($_GET['nombre']) ? $_GET['nombre'] : '';
              $psico = isset($_GET['psico']) ? $_GET['psico'] : '';
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
                          <option value="0" <?= $estado == '0' ? 'selected' : '' ?>>Sin Factura</option>
                          <option value="1" <?= $estado == '1' ? 'selected' : '' ?>>Con Factura</option>
                      </select>
                  </div>

                  <div class="form-group col-md-2">
                  <label for="nombre">Paciente</label>
                  <select id="nombre" name="nombre" class="form-control">
                      <option value="">Seleccione un usuario</option>
                      <?php
                      $sql99 = "SELECT id, pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE permiso = 9";
                      $result99 = $conn->query($sql99);

                      while ($row99 = $result99->fetch_assoc()) {
                          $selected = ($row99['id'] == $nombre) ? 'selected' : ''; // Compara con $nombre
                          echo '<option value="' . $row99['id'] . '" ' . $selected . '>';
                          echo htmlspecialchars($row99['pn_usu'] . " " . $row99['sn_usu'] . " " . $row99['pa_usu'] . " " . $row99['sa_usu']);
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
            <div class="row">
              <div class="col-12 col-md-12 col-lg-12">
                <div class="card">
                  <div class="card-body">
                    <div class="table-responsive">
                      <table class="table table-bordered table-md">
                        <tr>
                          <th style="text-align: center;">#</th>
                          <th style="text-align: center;">Fecha</th>
                          <th style="text-align: center;">Facturación</th>
                          <th style="text-align: center;">Ingreso RT</th>
                          <th style="text-align: center;">Ingreso Propio</th>
                          <th style="text-align: center;">Margen Neto</th>
                          <th style="text-align: center;">Cuenta</th>
                          <th style="text-align: center;">Profesional</th>
                          <th style="text-align: center;">Paciente</th>
                          <th style="text-align: center;">Estado Factura</th>
                          <th style="text-align: center;"># Factura</th>
                          <th style="text-align: center;">Descripción</th>
                          <th style="text-align: center;">Tipo Sesión</th>
                          <th style="text-align: center;">Estado</th>
                          <th style="text-align: center;">Observación</th>
                          <th style="text-align: center;">Factura</th>
                        </tr>
                        <?php

                        $registrosPorPagina = 10;
                        $paginaActual = isset($_GET['page']) ? (int)$_GET['page'] : 1;
                        if ($paginaActual < 1) $paginaActual = 1;

                        $offset = ($paginaActual - 1) * $registrosPorPagina;

                        // Filtros
                        if($fechaInicio === '') {
                          $fechaInicio1 = '0000-00-00';
                        } else {
                          $fechaInicio1 = $fechaInicio;
                        }

                        if($fechaFin === '') {
                          $fechaFin1 = '5000-12-31';
                        } else {
                          $fechaFin1 = $fechaFin;
                        }

                        // Construcción de la consulta con filtros
                        $where = "WHERE `order` != ''";
                        $where .= " AND s.fecha BETWEEN '$fechaInicio1' AND '$fechaFin1'";

                        if ($estado === '0') {
                            // Si el estado es 0, mostramos los registros donde fact es igual a 0
                            $where .= " AND s.fact = 0";
                        } elseif ($estado === '1') {
                            // Si el estado es 1, mostramos los registros donde fact es diferente a 0
                            $where .= " AND s.fact != 0";
                        }                     
                        if (!empty($nombre)) {
                          $where .= " AND s.userID = $nombre";
                        }
                        if (!empty($psico)) {
                          $where .= " AND s.psi = $psico";
                        }

                        // Consulta para contar los registros únicos por "order" con filtros
                        $sqlCount = "SELECT COUNT(DISTINCT s.order) AS total FROM sessions s $where";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];

                        // Calcula el número total de páginas
                        $totalPaginas = ceil($totalRegistros / $registrosPorPagina);

                        // Consulta para obtener los registros agrupados con la paginación y filtros aplicados
                        $sql = "SELECT s.order, s.use_date, s.fecpareal,
                                    (SELECT SUM(valor) FROM pays WHERE pays.order = s.order) AS total_valor,
                                    SUM(s.ingresoRT) AS total_ingresoRT, 
                                    SUM(s.ingresoPROPIO) AS total_ingresoPROPIO, 
                                    AVG(s.margenNeto) AS promedio_margenNeto, 
                                    COUNT(*) AS count_registros,
                                    MAX(s.medio_pago) AS medio_pago, 
                                    MAX(s.psi) AS psi, 
                                    MAX(s.userID) AS userID,
                                    MAX(s.fact) AS fact,
                                    MAX(s.tipo) AS tipo,
                                    (SELECT observaciones FROM pays WHERE pays.order = s.order) AS observaciones
                              FROM sessions s
                              $where
                              GROUP BY s.order, s.use_date
                              ORDER BY s.use_date DESC
                              LIMIT $offset, $registrosPorPagina";
                              
                        $result = $conn->query($sql);

                        $count = $offset + 1;

                        // Definir los medios de pago
                        $medios_pago = [
                            1 => "Paypal",
                            2 => "Bold",
                            3 => "PayU",
                            4 => "Efectivo",
                            5 => "Bancolombia (Empresa)",
                            6 => "Bancolombia (Personal)",
                            7 => "Nequi",
                            8 => "Daviplata"
                        ];

                        // Definir los tipos de terapia
                        $tipos_terapia = [
                            1 => "Individual",
                            2 => "Pareja",
                            5 => "Familia",
                            6 => "Psiquiatría",
                            8 => "Nutrición"
                        ];

                        // Mostrar los registros
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>';
                            echo '<td style="text-align: center;">' . $count . '</td>';
                            echo '<td style="text-align: center;" title="Fecha de pago: ' . htmlspecialchars($row['fecpareal']) . '">' . htmlspecialchars($row['use_date']) . '</td>';
                            echo '<td style="text-align: center;">$ ' . number_format($row['total_valor'], 0, ',', '.') . '</td>';
                            echo '<td style="text-align: center;">$ ' . number_format($row['total_ingresoRT'], 0, ',', '.') . '</td>';
                            echo '<td style="text-align: center;">$ ' . number_format($row['total_ingresoPROPIO'], 0, ',', '.') . '</td>';
                            echo '<td style="text-align: center;">' . round($row['promedio_margenNeto'], 2) . ' %</td>';
                            
                            // Obtener el texto correspondiente al método de pago
                            $medio_pago_texto = isset($medios_pago[$row['medio_pago']]) ? $medios_pago[$row['medio_pago']] : "SIN MÉTODO DE PAGO";
                            echo '<td style="text-align: center;">' . htmlspecialchars($medio_pago_texto) . '</td>';

                            // Obtener el nombre del psicólogo
                            $queryPsi = "SELECT pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE id = ?";
                            $stmt = $conn->prepare($queryPsi);
                            $stmt->bind_param("i", $row['psi']);
                            $stmt->execute();
                            $resultPsi = $stmt->get_result();
                            $psi_nombre = "SIN ASIGNAR";

                            if ($resultPsi->num_rows > 0) {
                                $psi_data = $resultPsi->fetch_assoc();
                                $psi_nombre = trim("{$psi_data['pn_usu']} {$psi_data['sn_usu']} {$psi_data['pa_usu']} {$psi_data['sa_usu']}");
                            }

                            echo '<td style="text-align: center;">' . htmlspecialchars($psi_nombre) . '</td>';

                            // Obtener el nombre del usuario
                            $queryUser = "SELECT pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE id = ?";
                            $stmt = $conn->prepare($queryUser);
                            $stmt->bind_param("i", $row['userID']);
                            $stmt->execute();
                            $resultUser = $stmt->get_result();
                            $user_nombre = "SIN ASIGNAR";

                            if ($resultUser->num_rows > 0) {
                                $user_data = $resultUser->fetch_assoc();
                                $user_nombre = trim("{$user_data['pn_usu']} {$user_data['sn_usu']} {$user_data['pa_usu']} {$user_data['sa_usu']}");
                            }

                            echo '<td style="text-align: center;">' . htmlspecialchars($user_nombre) . '</td>';

                            // Facturación
                            echo '<td style="text-align: center;">' . ($row['fact'] == 0 ? 'SIN FACTURA' : htmlspecialchars($row['fact'])) . '</td>';
                            echo '<td style="text-align: center;">' . ($row['fact'] == 0 ? 'SIN FACTURA' : 'CON FACTURA') . '</td>';

                            // Sesiones (singular/plural)
                            echo '<td style="text-align: center;">' . ($row['count_registros'] > 1 ? $row['count_registros'] . ' sesiones' : '1 sesión') . '</td>';

                            // Tipo de terapia
                            $tipo_terapia_texto = isset($tipos_terapia[$row['tipo']]) ? $tipos_terapia[$row['tipo']] : "SIN TIPO DE TERAPIA";
                            echo '<td style="text-align: center;">' . htmlspecialchars($tipo_terapia_texto) . '</td>';

                            // Reclasificación
                            echo '<td style="text-align: center;">' . ($row['fact'] == 0 ? 'SIN FACTURA' : 'RECLASIFICADO 28') . '</td>';

                            // Observaciones de pago
                            echo '<td style="text-align: center;">' . htmlspecialchars($row['observaciones']) . '</td>';

                            echo "<td style='text-align: center;'>";
                            $color4 = ($row['fact'] == "0") ? 'red' : 'green';
                            echo "<span 
                                style='display:inline-block; width:15px; height:15px; border-radius:50%; background-color:$color4; margin-right:5px; cursor:pointer;'
                                data-id='{$row['order']}'
                                data-toggle='modal'
                                data-target='#facturaModal'
                            ></span>";
                            echo "</td>";
                            
                            echo '</tr>';

                            $count++;
                        }
                        
                        $sqlSuma = "SELECT 
                                        (SELECT SUM(valor) FROM pays WHERE pays.order = s.order) AS total_valor,
                                        SUM(s.ingresoPROPIO) AS total_ingresoPROPIO
                                    FROM sessions s
                                    $where
                                    GROUP BY s.order, s.use_date";
                        
                        $resultSuma = $conn->query($sqlSuma);
                        
                        $total_valor_total = 0;
                        $total_ingresoPROPIO_total = 0;
                        
                        while ($row = $resultSuma->fetch_assoc()) {
                            $total_valor_total += $row['total_valor'];
                            $total_ingresoPROPIO_total += $row['total_ingresoPROPIO'];
                        }
                        
                        // Filtros por defecto para fecha
                        if ($fechaInicio === '') {
                            $fechaInicio1 = '1900-01-01';
                        } else {
                            $fechaInicio1 = $fechaInicio;
                        }
                        
                        if ($fechaFin === '') {
                            $fechaFin1 = '5000-12-31';
                        } else {
                            $fechaFin1 = $fechaFin;
                        }
                        
                        // Consulta para obtener la suma del campo "value"
                        $stmt_gastos = $conn->prepare("SELECT SUM(value) as total_gastos FROM gastos WHERE date BETWEEN ? AND ?");
                        $stmt_gastos->bind_param("ss", $fechaInicio1, $fechaFin1);
                        $stmt_gastos->execute();
                        $stmt_gastos->bind_result($totalGastos);
                        $stmt_gastos->fetch();
                        $stmt_gastos->close();
                        
                        // Ahora $totalGastos contiene la suma total
                        // Puedes usar: echo "Total de gastos: $totalGastos";
                        $totalGastos = $totalGastos ?? 0; // Asignar 0 si es NULL

                        
                        if($_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){
                        
                            echo '<div style="margin-top: 20px; padding: 15px; background-color: #f8f9fa; border: 1px solid #ddd; border-radius: 8px; font-size: 16px;">';
                            echo '<strong>Total facturado:</strong> <span style="color: green;">$ ' . number_format($total_valor_total, 0, ',', '.') . '</span><br>';
                            echo '<strong>Total ingreso propio:</strong> <span style="color: blue;">$ ' . number_format($total_ingresoPROPIO_total, 0, ',', '.') . '</span><br>';
                            echo '<strong>Total gastos:</strong> <span style="color: red;">$ ' . number_format($totalGastos, 0, ',', '.') . '</span><br>';
                            $utilidad = $total_ingresoPROPIO_total - $totalGastos;
                            echo '<strong>Utilidad acumulada:</strong> <span style="color: black;">$ ' . number_format($utilidad, 0, ',', '.') . '</span>';
                            echo '</div>';
                        }
                        ?>

                      </table>
                    </div>
                  </div>
<?php
// Copiamos los parámetros actuales
$params = $_GET;

// Función para generar enlaces con page actualizada
function pageUrl($page, $params) {
    $params['page'] = $page;
    return '?' . http_build_query($params);
}

$maxVisible = 7; 
$half = floor($maxVisible / 2);

$start = max(1, $paginaActual - $half);
$end = min($totalPaginas, $paginaActual + $half);

if ($paginaActual <= $half) {
    $end = min($totalPaginas, $maxVisible);
}
if ($paginaActual > $totalPaginas - $half) {
    $start = max(1, $totalPaginas - $maxVisible + 1);
}
?>

<div class="card-footer text-right">
  <nav class="d-inline-block">
    <ul class="pagination mb-0">
      <!-- Botón anterior -->
      <li class="page-item <?= ($paginaActual <= 1) ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= pageUrl(max(1, $paginaActual - 1), $params) ?>"><i class="fas fa-chevron-left"></i></a>
      </li>

      <!-- Primera página -->
      <?php if ($start > 1): ?>
        <li class="page-item <?= (1 == $paginaActual) ? 'active' : '' ?>">
          <a class="page-link" href="<?= pageUrl(1, $params) ?>">1</a>
        </li>
        <?php if ($start > 2): ?>
          <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>
      <?php endif; ?>

      <!-- Páginas intermedias -->
      <?php for ($i = $start; $i <= $end; $i++): ?>
        <li class="page-item <?= ($i == $paginaActual) ? 'active' : '' ?>">
          <a class="page-link" href="<?= pageUrl($i, $params) ?>"><?= $i ?></a>
        </li>
      <?php endfor; ?>

      <!-- Última página -->
      <?php if ($end < $totalPaginas): ?>
        <?php if ($end < $totalPaginas - 1): ?>
          <li class="page-item disabled"><span class="page-link">...</span></li>
        <?php endif; ?>
        <li class="page-item <?= ($totalPaginas == $paginaActual) ? 'active' : '' ?>">
          <a class="page-link" href="<?= pageUrl($totalPaginas, $params) ?>"><?= $totalPaginas ?></a>
        </li>
      <?php endif; ?>

      <!-- Botón siguiente -->
      <li class="page-item <?= ($paginaActual >= $totalPaginas) ? 'disabled' : '' ?>">
        <a class="page-link" href="<?= pageUrl(min($totalPaginas, $paginaActual + 1), $params) ?>"><i class="fas fa-chevron-right"></i></a>
      </li>
    </ul>
  </nav>
</div>

                </div>
              </div>
            </div>
          </div>
        </div>
      </div>




        </section>
      </div>
      <?php include "footer.php"?>
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
  <script src="assets/modules/jquery.sparkline.min.js"></script>
  <script src="assets/modules/chart.min.js"></script>
  <script src="assets/modules/owlcarousel2/dist/owl.carousel.min.js"></script>
  <script src="assets/modules/summernote/summernote-bs4.js"></script>
  <script src="assets/modules/chocolat/dist/js/jquery.chocolat.min.js"></script>

  <!-- Page Specific JS File -->
  <script src="assets/js/page/index.js"></script>
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>


  

<!-- Modal -->
<div id="facturaModal" class="modal fade" tabindex="-1" role="dialog" aria-labelledby="facturaModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="facturaModalLabel">Ingresar Número de Factura</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form action="tablero_factura" method="POST">
                    <input type="hidden" name="id" id="facturaId">
                    <div class="form-group">
                        <label for="numero_factura">Número de Factura:</label>
                        <input type="number" class="form-control" name="numero_factura" id="numero_factura" required>
                    </div>
                    <div class="modal-footer">
                        <button type="submit" class="btn btn-primary">Guardar</button>
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).on('click', '[data-target="#facturaModal"]', function () {
        var id = $(this).data('id'); // Captura el ID correctamente
        $('#facturaId').val(id); // Lo asigna al input hidden
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

          if (status === 'factura_actualizada') {
              toastr.options = {
                  "timeOut": "5000",
                  "extendedTimeOut": "1000",
                  "showMethod": "fadeIn",
                  "hideMethod": "fadeOut"
              };
              toastr.success('Número de factura asignado correctamente.');
          }
      }
    });
</script>

</body>
</html>