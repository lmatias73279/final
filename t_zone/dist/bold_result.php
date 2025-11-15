<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);

// Habilitar la visualización de errores para depuración
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Incluir el archivo de conexión
include "../../conexionsm.php"; // Asumiendo que $conn está definido en este archivo

// Verifica si la conexión fue exitosa
if ($conn->connect_error) {
    die('Error de conexión a la base de datos: ' . $conn->connect_error);
}

// Obtén el estado del pago y el ID de la orden desde los parámetros de la URL
$status = isset($_GET['bold-tx-status']) ? $_GET['bold-tx-status'] : 'unknown';
$orderId = isset($_GET['bold-order-id']) ? $_GET['bold-order-id'] : null;

if (in_array($status, ['approved', 'rejected', 'failed']) && $orderId) {
    // Escapar el ID de la orden
    $orderId = $conn->real_escape_string($orderId);

    // Seleccionar datos de la tabla payments
    $selectdata = "SELECT tipoTerapia, amount, userID, q, site, currency FROM payments WHERE order_id = '$orderId'";
    $result = $conn->query($selectdata);

    if ($result && $row = $result->fetch_assoc()) {
        $tipoTerapia = $row['tipoTerapia'];
        $currency = $row['currency'];
        $amount = $row['amount'];
        $userID = $row['userID'];
        $site = $row['site'];
        $q = $row['q'];
        $amount_real = $amount / $q;
        // Obtener la fecha actual
        date_default_timezone_set('America/Bogota');
        $fechaActual = date('Y-m-d H:i:s');

        // Contar cuántos registros ya existen en la tabla sessions para esta orden
        $countQuery = "SELECT COUNT(*) as count FROM sessions WHERE `order` = '$orderId'";
        $countResult = $conn->query($countQuery);
        $existingRecords = 0;

        if ($countResult && $countRow = $countResult->fetch_assoc()) {
            $existingRecords = $countRow['count'];
        }

        // Calcular cuántos registros faltan por insertar
        $recordsToInsert = $q - $existingRecords;

        // Si faltan registros por insertar, realizar el insert
        if ($recordsToInsert > 0) {
            for ($i = 0; $i < $recordsToInsert; $i++) {
                // Primero verificamos si existe un registro que cumpla con las condiciones para actualizar
                $checkSql = "SELECT * FROM sessions 
                            WHERE tipo = '$tipoTerapia' 
                            AND estado = 1 
                            AND titulo = '' 
                            AND userID = '$userID' 
                            AND site = '$site'";
            
                $result = $conn->query($checkSql);
            
                if ($result->num_rows > 0) {
                    // Si existe el registro, lo actualizamos
                    $updateSql = "UPDATE sessions SET 
                                    `order` = '$orderId', 
                                    valor = '$amount_real', 
                                    tipoValor = '$currency', 
                                    use_date = '$fechaActual', 
                                    estado = 2, 
                                    titulo = 1
                                  WHERE tipo = '$tipoTerapia' 
                                    AND estado = 1 
                                    AND titulo = '' 
                                    AND userID = '$userID' 
                                    AND site = '$site'";
            
                    if (!$conn->query($updateSql)) {
                        echo "Error al actualizar en sessions: " . $conn->error;
                    }
                } else {
                    // Si no existe, insertamos el nuevo registro
                    $insertSql = "INSERT INTO sessions (`order`, tipo, valor, tipoValor, use_date, userID, site) 
                                  VALUES ('$orderId', '$tipoTerapia', '$amount_real', '$currency', '$fechaActual', '$userID', '$site')";
            
                    if (!$conn->query($insertSql)) {
                        echo "Error al insertar en sessions: " . $conn->error;
                    }
                }
            }        
        }
    } else {
      header("Location: mis_citas");
      exit();
    }
}else {
  header("Location: mis_citas");
  exit();
}

// Actualiza el estado del pago en la base de datos
if ($orderId) {
  // Validar y asignar el estado del pago
  $iconStatus = "error";
  if ($status === 'approved') {
      $paymentStatus = 'approved';
      $statusMessage = 'El pago fue aprobado de manera exitosa';
      $iconStatus = "success";
  } elseif ($status === 'rejected') {
    $paymentStatus = 'rejected';
    $statusMessage = 'El pago fue rechazado';
    $iconStatus = "error";
  } elseif ($status === 'failed') {
    $paymentStatus = 'failed';
    $statusMessage = 'El pago generó un fallo en el proceso';
    $iconStatus = "error";
  } else {
      $paymentStatus = 'failed';
      $statusMessage = 'Estado de la transacción invalida';
      $iconStatus = "warning";
  }

  // Ejecutar la consulta para actualizar el estado del pago
  $query = "UPDATE payments SET status = '$paymentStatus' WHERE order_id = '$orderId'";
  if ($conn->query($query)) {
      $dbMessage = [
          'class' => $iconStatus,
          'text' => $statusMessage,
      ];
  } else {
      $dbMessage = [
          'class' => 'warning',
          'text' => 'Error actualizando el estado del pago: ' . $conn->error,
      ];
  }
} else {
  $dbMessage = [
      'class' => 'error',
      'text' => 'No se recibió un ID de orden válido para actualizar.',
  ];
}


// Cierra la conexión
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>Invoice &mdash; Stisla</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
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
      
      <?php include "nav.php";?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Comprobante de compra</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="#">Compra</a></div>
              <div class="breadcrumb-item">Resultado</div>
            </div>
          </div>

          <div class="section-body">
            <div class="invoice">
              <div class="invoice-print">
                <div class="row">
                  <div class="col-lg-12">
                    <div class="invoice-title">
                    <style>
                      .message {
                          display: flex;
                          align-items: center;
                          gap: 10px;
                          font-family: Arial, sans-serif;
                          margin: 20px 0;
                      }
                      .icon {
                          display: flex;
                          justify-content: center;
                          align-items: center;
                          width: 40px;
                          height: 40px;
                          border-radius: 50%;
                          font-size: 18px;
                          color: white;
                      }
                      .success {
                          background-color: #28a745;
                      }
                      .warning {
                          background-color: #ffc107;
                      }
                      .error {
                          background-color: #dc3545;
                      }
                    </style>
                    <?php if (isset($dbMessage)): ?>
                      <div class="message">
                          <div class="icon <?= $dbMessage['class'] ?>">
                              <?php if ($dbMessage['class'] === 'success'): ?>
                                  ✓
                              <?php elseif ($dbMessage['class'] === 'warning'): ?>
                                  !
                              <?php elseif ($dbMessage['class'] === 'error'): ?>
                                  ✗
                              <?php endif; ?>
                          </div>
                          <span><?= htmlspecialchars($dbMessage['text']) ?></span>
                      </div>
                    <?php endif; ?>
                      <div class="invoice-number">Orden número <?php echo str_replace('ORDER_', '', $orderId); ?></div>
                    </div>
                    <hr>
                    <div class="row">
                      <div class="col-md-6">
                        <address>
                          <strong>Comprado Por:</strong><br>
                            Ujang Maman<br>
                            1234 Main<br>
                            Apt. 4B<br>
                            Bogor Barat, Indonesia
                        </address>
                      </div>
                      <div class="col-md-6 text-md-right">
                        <address>
                          <strong>Tienda:</strong><br>
                          Salud Mental Sana Mente<br>
                          Carrera 49a # 94 - 32<br>
                          Piso 1<br>
                          Bogotá, Colombia
                        </address>
                      </div>
                    </div>
                    <div class="row">
                      <div class="col-md-6">
                        <address>
                          <strong>Metodo de Pago:</strong><br>
                          Transacción Bold<br>
                          citas@saludmentalsanamente.com.co
                        </address>
                      </div>
                      <div class="col-md-6 text-md-right">
                        <address>
                          <strong>Fecha de Orden:</strong><br>
                          <?php echo $fechaActual;?><br><br>
                        </address>
                      </div>
                    </div>
                  </div>
                </div>
                
                <div class="row mt-4">
                  <div class="col-md-12">
                    <div class="section-title">Resumen de la orden</div>
                    <p class="section-lead">Los items relacionados son los comprados por usted en nuestra tienda</p>
                    <div class="table-responsive">
                      <table class="table table-striped table-hover table-md">
                        <tr>
                          <th data-width="40">#</th>
                          <th>Item</th>
                          <th class="text-center">Modalidad</th>
                          <th class="text-center">Precio</th>
                          <th class="text-center">Cantidad</th>
                          <th class="text-right">Total</th>
                        </tr>
                        <tr>
                          <td>1</td>
                          <td>
                            <?php
                            switch ($tipoTerapia) {
                                case 1:
                                    echo "Terapia Individual";
                                    break;
                                case 2:
                                    echo "Terapia de Pareja";
                                    break;
                                case 5:
                                    echo "Terapia de Familia";
                                    break;
                                case 6:
                                    echo "Terapia Psiquiatría";
                                    break;
                                default:
                                    echo "no especificado";
                            }
                            ?>
                          </td>
                          <td class="text-center">
                            <?php
                            switch ($site) {
                              case 1:
                                echo "Presencial";
                                break;
                              case 2:
                                echo "Virtual";
                                break;
                              default:
                                echo "no especificado";
                            }
                            ?>
                          </td>
                          <td class="text-center"><?php echo '$ ' . number_format($amount_real, 2, ',', '.'); ?></td>
                          <td class="text-center"><?php echo $q;?></td>
                          <td class="text-right"><?php echo '$ ' . number_format($amount, 2, ',', '.'); ?></td>
                        </tr>
                      </table>
                    </div>
                    <div class="row mt-4">
                      <div class="col-lg-8">
                        <div class="section-title">Metodo de pago</div>
                        <p class="section-lead">Este pago fue procesado por la pasarela de pagos de Bold, en el tipo de moneda <?php switch ($currency){case 1: echo "Pesos Colombianos"; case 2: echo "Dolares";}?></p>
                        <div class="images">
                          <img src="assets/img/bold.png" alt="bold">
                          <img src="assets/img/americanexpress.png" alt="americanexpress">
                          <img src="assets/img/dinersclub.png" alt="dinersclub">
                          <img src="assets/img/discover.png" alt="discover">
                          <img src="assets/img/mastercard.png" alt="mastercard">
                          <img src="assets/img/visa.png" alt="visa">
                        </div>
                      </div>
                      <div class="col-lg-4 text-right">
                        <hr class="mt-2 mb-2">
                        <div class="invoice-detail-item">
                          <div class="invoice-detail-name">Total</div>
                          <div class="invoice-detail-value invoice-detail-value-lg"><?php echo '$ ' . number_format($amount, 2, ',', '.'); ?></div>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>
              </div>
              <hr>
              <div class="text-md-right">
              <div class="float-lg-left mb-lg-0 mb-3">
                <a href="mis_citas" class="btn btn-primary btn-icon icon-left"><i class="fa-regular fa-circle-left"></i> Volver a mis citas</a>
              </div>
              <button class="btn btn-warning btn-icon icon-left" onclick="printSectionBody();">
                <i class="fas fa-print"></i> Imprimir
              </button>
              </div>
            </div>
          </div>
        </section>
      </div>

      <?php include "footer.php";?>

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
  <script>
  function printSectionBody() {
      // Selecciona el contenido del div con clase 'section-body'
      var content = document.querySelector('.section-body').innerHTML;

      // Crea un documento nuevo para la impresión
      var printWindow = window.open('', '_blank');
      printWindow.document.open();
      printWindow.document.write(`
          <html>
          <head>
              <title>Resultado de compra</title>
              <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
              <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">
              <link rel="stylesheet" href="assets/css/style.css">
              <link rel="stylesheet" href="assets/css/components.css">
          </head>
          <body onload="window.print(); window.close();">
              ${content}
          </body>
          </html>
      `);
      printWindow.document.close();
  }
  </script>


  <!-- JS Libraies -->

  <!-- Page Specific JS File -->
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>