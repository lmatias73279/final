<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 9 && $_SESSION['permiso'] !== 99 || $_SESSION['profesional_asignado'] === 0){
    header("location: login");
}






$id = $_SESSION['profesional_asignado'];

// Cargar profesionales disponibles para filtrar citas
$profesionales = $conn->query("SELECT id, pa_usu FROM usuarios WHERE id = $id")->fetch_all(MYSQLI_ASSOC);

// Consulta SQL para obtener las fechas deshabilitadas
$sql = "SELECT date FROM days_exception WHERE id_user = ? AND status = 0";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();

// Crear un array de fechas para pasar a JavaScript
$disabled_dates = [];
while ($row = $result->fetch_assoc()) {
    $disabled_dates[] = $row['date'];
}


// Verificar si se ha enviado una solicitud AJAX
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $data = json_decode(file_get_contents("php://input"));
  $fecha = $data->fecha;
  $id = $_SESSION['profesional_asignado'];

  // Obtener el día de la semana de la fecha seleccionada
  $dia_semana = date('N', strtotime($fecha)); // 'N' devuelve el número del día de la semana (1 para lunes, 7 para domingo)

  // Consulta para obtener la disponibilidad
  $query = "SELECT lu1d, lu1h, lu2d, lu2h, ma1d, ma1h, ma2d, ma2h, mi1d, mi1h, mi2d, mi2h, ju1d, ju1h, ju2d, ju2h, vi1d, vi1h, vi2d, vi2h, sa1d, sa1h, sa2d, sa2h FROM disponibilidad WHERE id_user = $id";
  $result = mysqli_query($conn, $query);

  $horas = [];

  if ($result) {
      $row = mysqli_fetch_assoc($result);
      if ($row) {
          // Obtener las horas según el día de la semana
          switch ($dia_semana) {
              case 1: // Lunes
                  $rango1_inicio = (int)$row['lu1d'];
                  $rango1_fin = (int)$row['lu1h'];
                  $rango2_inicio = (int)$row['lu2d'];
                  $rango2_fin = (int)$row['lu2h'];
                  break;
              case 2: // Martes
                  $rango1_inicio = (int)$row['ma1d'];
                  $rango1_fin = (int)$row['ma1h'];
                  $rango2_inicio = (int)$row['ma2d'];
                  $rango2_fin = (int)$row['ma2h'];
                  break;
              case 3: // Miércoles
                  $rango1_inicio = (int)$row['mi1d'];
                  $rango1_fin = (int)$row['mi1h'];
                  $rango2_inicio = (int)$row['mi2d'];
                  $rango2_fin = (int)$row['mi2h'];
                  break;
              case 4: // Jueves
                  $rango1_inicio = (int)$row['ju1d'];
                  $rango1_fin = (int)$row['ju1h'];
                  $rango2_inicio = (int)$row['ju2d'];
                  $rango2_fin = (int)$row['ju2h'];
                  break;
              case 5: // Viernes
                  $rango1_inicio = (int)$row['vi1d'];
                  $rango1_fin = (int)$row['vi1h'];
                  $rango2_inicio = (int)$row['vi2d'];
                  $rango2_fin = (int)$row['vi2h'];
                  break;
              case 6: // Sábado
                  $rango1_inicio = (int)$row['sa1d'];
                  $rango1_fin = (int)$row['sa1h'];
                  $rango2_inicio = (int)$row['sa2d'];
                  $rango2_fin = (int)$row['sa2h'];
                  break;
              default:
                  $rango1_inicio = $rango1_fin = $rango2_inicio = $rango2_fin = null; // No hay disponibilidad para domingo
                  break;
          }

          // Generar las horas disponibles para el primer rango
          if ($rango1_inicio !== null && $rango1_fin !== null) {
              for ($i = $rango1_inicio; $i < $rango1_fin; $i++) {
                  $horas[] = [
                      'value' => str_pad($i, 2, '0', STR_PAD_LEFT) . '0000', // Formato militar
                      'label' => date('h:i A', strtotime("$i:00")) // Formato legible
                  ];
              }
          }

          // Gener ar las horas disponibles para el segundo rango
          if ($rango2_inicio !== null && $rango2_fin !== null) {
              for ($i = $rango2_inicio; $i < $rango2_fin; $i++) {
                  $horas[] = [
                      'value' => str_pad($i, 2, '0', STR_PAD_LEFT) . '0000', // Formato militar
                      'label' => date('h:i A', strtotime("$i:00")) // Formato legible
                  ];
              }
          }
      }
  }

  // Consulta para obtener las horas ya agendadas
  $query_eventos = "SELECT hora FROM sessions WHERE psi = $id AND fecha = '$fecha'";
  $result_eventos = mysqli_query($conn, $query_eventos);
  $horas_ocupadas = [];

  if ($result_eventos) {
      while ($row_evento = mysqli_fetch_assoc($result_eventos)) {
          $hora_ocupada = date('H', strtotime($row_evento['hora'])); // Obtener solo la hora
          $horas_ocupadas[] = str_pad($hora_ocupada, 2, '0', STR_PAD_LEFT) . '0000'; // Formato militar
      }
  }

  // Eliminar las horas ocupadas de $horas
  $horas = array_filter($horas, function($hora) use ($horas_ocupadas) {
      return !in_array($hora['value'], $horas_ocupadas);
  });

  // Agregar la opción "Seleccione una hora" solo si hay horas disponibles
  if (!empty($horas)) {
      array_unshift($horas, [
          'value' => '',
          'label' => 'Seleccione una hora'
      ]);
  } else {
      $horas[] = [
          'value' => '',
          'label' => 'No hay horas disponibles para este día'
      ];
  }
  
  // Devolver las horas en formato JSON
  echo json_encode(['horas' => $horas]);
  exit; // Terminar el script después de la respuesta AJAX
}
?>
<!DOCTYPE html>
<html lang="en">
<head>

  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Mis Citas</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/pikaday/css/pikaday.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/2.1.4/toastr.min.css">

  <!-- Popper.js -->
  <script src="assets/modules/popper.js"></script>
  <!-- Bootstrap JS -->
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
</head>

<body>
  <div id="app">
    <div class="main-wrapper main-wrapper-1">
      <div class="navbar-bg"></div>
      <?php include "nav.php";?>

      <!-- Main Content -->
      <div class="main-content">
        <section class="section">
          <div class="section-header">
            <h1>Mis Citas</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="#">Herramientas</a></div>
              <div class="breadcrumb-item"><a href="#">Mis Citas</a></div>
            </div>
          </div>

          <div class="section-body">
            <div style="display: flex; justify-content: space-between; align-items: center;">
              <h2 class="section-title">Mis Citas</h2>
              <?php 
                $paciente = $_SESSION['id'];
                $sqlcredit = "SELECT COUNT(*) AS total FROM sessions WHERE userID = $paciente AND estado = 3";
                $total = $conn->query($sqlcredit)->fetch_assoc()['total'];
                if ($total > 0) {
              ?>
                <!--<button class="btn btn-primary" data-toggle="modal" data-target="#agendarCitaModal">Agendar cita</button>-->
              <?php }else{ ?>
                <span>Tu cuenta no tiene saldo disponible para tu siguiente cita. 🙈</span>
              <?php } if($_SESSION['bold'] === 1){?>
              <button class="btn btn-primary" data-toggle="modal" data-target="#comprarCreditosModal">Comprar Créditos</button>
              <?php }?>
            </div>
            <div class="row">
            <?php
            include "../../conexionsm.php";
            $paciente = $_SESSION['id'];
            $sqlcitas = "SELECT * FROM sessions WHERE userID = $paciente AND estado IN (1, 2)";
            $resultado = mysqli_query($conn, $sqlcitas);

            if (!$resultado) {
                die("Error en la consulta: " . mysqli_error($conn));
            }

            while ($fila = mysqli_fetch_assoc($resultado)) {
            $profes = $fila['psi'];
            $sqlpro = "SELECT pn_usu, pa_usu, foto FROM usuarios WHERE id = $profes";
            $result1 = mysqli_query($conn, $sqlpro);
            $row1 = mysqli_fetch_assoc($result1);
            ?>
            <div class="col-12 col-sm-12 col-lg-12">
                <div class="card author-box card-primary">
                <div class="card-body">
                    <div class="author-box-left">
                    <?php
                    $foto = $row1 ? "profile-photos/" . $row1['foto'] : "avatar/avatar-1.png";
                    ?>
                    <img alt="image" src="assets/img/<?php echo $foto; ?>" class="rounded-circle author-box-picture">
                    <div class="clearfix"></div>
                    </div>
                    <div class="author-box-details">
                    <div class="author-box-name">
                        <a href="#"><?php echo ucwords(strtolower($row1['pn_usu'] . " " . $row1['pa_usu'])); ?></a>
                    </div>
                    <div class="author-box-job">Psicólogo/a clínico</div>
                    <div class="author-box-description">
                        <p>Su cita está programada para el <?php echo $fila['fecha']; ?> a las <?php echo $fila['hora']; ?>.</p>
                    </div>

                    <?php if ($fila['estado'] == 1): ?>
                        <div class="alert alert-danger mb-2 p-2" role="alert" style="width: fit-content;">
                        Pago pendiente
                        </div>
                    <?php endif; ?>

                    <div class="float-right mt-sm-0 mt-3">
                        <?php if ($fila['estado'] == 2): ?>
                        <a href="<?php echo $fila['link_ingreso']; ?>" class="btn btn-primary" target="_blank">
                            Ingresar a consulta <i class="fas fa-chevron-right"></i>
                        </a>
                        <?php else: ?>
                        <button class="btn btn-secondary" disabled>
                            Esperando confirmación de pago
                        </button>
                        <?php endif; ?>
                    </div>
                    </div>
                </div>
                </div>
            </div>
            <?php
            }
            ?>
            </div>
          </div>
        </section>
      </div>
      <?php include "footer.php";?>
    </div>
  </div>

  <?php if ($total > 0) { ?>
  <!-- Modal para Agendar Cita -->
  <div class="modal fade" id="agendarCitaModal" tabindex="-1" role="dialog" aria-labelledby="agendarCitaLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="agendarCitaLabel">Agendar Cita</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <form id="guardar_cita" method="POST">
            <div class="form-group">
                <label for="profesional">Seleccione el Profesional</label>
                <select class="form-control" id="profesional" name="profesional" required onchange="cargarHorasDisponibles()">
                    <option value="">Seleccione</option>
                    <?php foreach ($profesionales as $profesional): ?>
                        <option value="<?php echo $profesional['id']; ?>">
                            <?php echo $profesional['pa_usu']; ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>

            <div class="form-group">
                <label for="fechaCita">Fecha</label>
                <input type="date" class="form-control" id="fechaCita" name="fecha" required onchange="cargarHorasDisponibles()">
            </div>

            <div class="form-group">
                <label for="horaCita">Hora</label>
                <select class="form-control" id="horaCita" name="hora" required>
                    <option value="">Seleccione una hora</option>
                </select>
            </div>

            <div class="form-group">
                <label for="tipo_atencion">Tipo de atención</label>
                <select id="tipo_atencion" name="tipo_atencion" class="form-control" required>
                    <option value="">Seleccione tipo de atención</option>
                    <option value="2">Virtual</option>
                    <!-- Se añadirá dinámicamente la opción 'Presencial' si la fecha y el status son correctos -->
                </select>
            </div>

            <button type="submit" class="btn btn-primary">Guardar Cita</button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <?php } ?>

  <!-- Modal para Comprar Creditos -->
  <div class="modal fade" id="comprarCreditosModal" tabindex="-1" role="dialog" aria-labelledby="comprarCreditosLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="comprarCreditosLabel">Comprar Créditos</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <div class="form-group">
            <label for="tipoTerapia">Tipo de terapia</label>
            <select class="form-control" id="tipoTerapia" name="tipoTerapia" required>
              <option value="">Seleccione una cantidad</option>
              <option value="1">Terapia Individual</option>
              <option value="2">Terapia de Pareja</option>
              <option value="5">Terapia Familia</option>
              <option value="6">Terapia Psiquiatría</option>
            </select>
            <span class="error-message text-danger" id="tipoTerapiaError" style="display: none;">Por favor, seleccione el tipo de terapia.</span>
          </div>

          <div class="form-group">
            <label for="creditos">Cantidad de sesiones</label>
            <select class="form-control" id="creditos" name="creditos" required>
              <option value="">Seleccione una cantidad</option>
              <option value="1">1</option>
              <option value="3">3 (5% descuento)</option>
              <option value="6">6 (8% descuento)</option>
              <option value="9">9 (10% descuento)</option>
            </select>
            <span class="error-message text-danger" id="creditosError" style="display: none;">Por favor, seleccione la cantidad de sesiones.</span>
          </div>

          <div class="form-group">
            <label for="site">Tipo de atención</label>
            <select class="form-control" id="site" name="site" required>
              <option value="">Seleccione una cantidad</option>
              <option value="1">Presencial</option>
              <option value="2">Virtual</option>
            </select>
            <span class="error-message text-danger" id="siteError" style="display: none;">Por favor, seleccione el tipo de atención.</span>
          </div>

          <div class="form-group">
            <label for="codpromo">Código Promocional</label>
            <input type="text" class="form-control" id="codpromo" name="codpromo" placeholder="Ingrese el código" required>
          </div>

          <button id="custom-button-payment" date-toggle="modal" data-target="#confirmacionModal" class="btn btn-primary">Comprar</button>
          <input type="hidden" value="<?php echo $_SESSION['id'];?>" id="userID">
        </div>
      </div>
    </div>
  </div>

  


  <!-- Modal para Confirmación de Información -->
  <div class="modal fade" id="confirmacionModal" tabindex="-1" role="dialog" aria-labelledby="confirmacionModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="confirmacionModalLabel">Confirmar Información</h5>
                  <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <p><strong>Tipo de Terapia:</strong> <span id="confirmacionTipoTerapia"></span></p>
                  <p><strong>Cantidad de Sesiones:</strong> <span id="confirmacionCreditos"></span></p>
                  <p><strong>Tipo de Atención:</strong> <span id="confirmacionSite"></span></p>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                  <button id="custom-button-payment1" class="btn btn-primary">Confirmar y Proceder</button>
              </div>
          </div>
      </div>
  </div>


  <script src="assets/modules/jquery.min.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/pikaday/pikaday.js"></script>
  <script>
    // Inicializa Bold Checkout
    const initBoldCheckout = () => {
        if (document.querySelector('script[src="https://checkout.bold.co/library/boldPaymentButton.js"]')) {
            return;
        }

        const script = document.createElement('script');
        script.onload = () => window.dispatchEvent(new Event('boldCheckoutLoaded'));
        script.src = 'https://checkout.bold.co/library/boldPaymentButton.js';
        document.head.appendChild(script);
    };

    // Llama a la función para cargar el script
    initBoldCheckout();

    const button = document.getElementById('custom-button-payment');
    const button1 = document.getElementById('custom-button-payment1');
    // Función para mostrar mensajes de error
    function mostrarError(id, mensaje) {
        const errorSpan = document.getElementById(id);
        errorSpan.textContent = mensaje;
        errorSpan.style.display = 'block';

        // Ocultar después de 3 segundos
        setTimeout(() => {
            errorSpan.style.display = 'none';
        }, 3000);
    }


    // Una vez que Bold se cargue, inicializa el botón
    button.addEventListener('click', async () => {
      
            
      const creditosSelect = document.getElementById('creditos');
      const userID = document.getElementById('userID');
      const site = document.getElementById('site');
      const tipoTerapia = document.getElementById('tipoTerapia');


      let valido = true;

      // Validar tipo de terapia
      if (!tipoTerapia.value) {
          mostrarError('tipoTerapiaError', 'Por favor, seleccione el tipo de terapia.');
          valido = false;
      }

      // Validar cantidad de sesiones
      if (!creditosSelect.value) {
          mostrarError('creditosError', 'Por favor, seleccione la cantidad de sesiones.');
          valido = false;
      }

      // Validar tipo de atención
      if (!site.value) {
          mostrarError('siteError', 'Por favor, seleccione el tipo de atención.');
          valido = false;
      }

      if (valido) {
        console.log('Bold Checkout cargado con éxito.');
         // Rellena la información del modal de confirmación

        // Obtiene los valores seleccionados y su texto
        const tipoTerapia = document.getElementById('tipoTerapia');
        const creditos = document.getElementById('creditos');
        const site = document.getElementById('site');

        // Actualiza los elementos en el modal con el texto de las opciones seleccionadas
        document.getElementById('confirmacionTipoTerapia').innerText = tipoTerapia.options[tipoTerapia.selectedIndex].text;
        document.getElementById('confirmacionCreditos').innerText = creditos.options[creditos.selectedIndex].text;
        document.getElementById('confirmacionSite').innerText = site.options[site.selectedIndex].text;

        // Cierra el modal actual (Comprar Créditos) y abre el nuevo modal (Confirmación)
        $('#comprarCreditosModal').modal('hide'); // Cierra el modal actual
        $('#confirmacionModal').modal('show');    // Abre el modal de confirmación

        // Manejo del cierre o cancelación del modal de confirmación
        $('#confirmacionModal').on('hidden.bs.modal', function () {
            // Reabre el modal anterior (Comprar Créditos) si se cierra el de confirmación
            $('#comprarCreditosModal').modal('show');
        });

        try {

            const codpromo = document.getElementById('codpromo');
            // Obtiene el hash, la orden y otros datos desde el backend
            const url = `Bold/backend/hash_generator.php?creditos=${creditosSelect.value}&site=${site.value}&tipoTerapia=${tipoTerapia.value}&codpromo=${codpromo.value}`;
            const response = await fetch(url);
            const { orderId, hash, amount, currency } = await response.json();

            // Configura Bold Checkout
            const checkout = new BoldCheckout({
                orderId,
                currency: currency,
                amount: amount, // Monto en centavos
                apiKey: "oKJaKHRcFWZ26rxPjgdWKOynj_Iidu5-NYAKoC0Sgn4", // Cambia esto por tu API Key
                integritySignature: hash,
                description: "Compra de Sesiones | Sana Mente",
                redirectionUrl: "http://saludmentalsanamente.com.co/t_zone/dist/bold_result",
            });

            button1.addEventListener('click', async () => {
                  try {
                      // Envía los datos al servidor antes de abrir la pasarela
                      const savePaymentResponse = await fetch('Bold/backend/save_payment.php', {
                          method: 'POST',
                          headers: {
                              'Content-Type': 'application/json',
                          },
                          body: JSON.stringify({
                              orderId,
                              amount: amount,
                              currency: currency,
                              userID: userID.value,
                              tipoTerapia: tipoTerapia.value,
                              site: site.value,
                              q: creditosSelect.value,
                              codpromo: codpromo,
                          }),
                      });

                      const savePaymentResult = await savePaymentResponse.json();
                      if (savePaymentResult.success) {
                          // Abre la pasarela de pagos si el guardado fue exitoso
                          checkout.open();
                      } else {
                          console.error('Error guardando el pago en el servidor:', savePaymentResult.error);
                      }
                  } catch (error) {
                      console.error('Error guardando el pago:', error);
                  }
            });
        } catch (error) {
            console.error('Error inicializando Bold Checkout:', error);
        }
      }
    });

  </script>
  
  <script>
      // Obtener las fechas deshabilitadas desde PHP
      var disabledDates = <?php echo json_encode($disabled_dates); ?>;

      // Inicializar Pikaday
      var picker = new Pikaday({
          field: document.getElementById('fechaCita'),
          format: 'YYYY-MM-DD',  // Mantener el formato de fecha adecuado
          disableDayFn: function(date) {
              // Deshabilitar los domingos (0 = Domingo)
              if (date.getDay() === 0) return true;

              // Deshabilitar las fechas provenientes de la base de datos
              var dateString = date.toISOString().split('T')[0];
              if (disabledDates.includes(dateString)) return true;

              // Deshabilitar las fechas de hoy hacia atrás
              var today = new Date();
              today.setHours(0, 0, 0, 0); // Establecer la hora a medianoche para comparar solo la fecha
              return date < today; // Deshabilitar si la fecha es anterior a hoy
          },
          onSelect: function(date) {
              // Asegurar que el campo se actualice con la fecha seleccionada en el formato correcto
              document.getElementById('fechaCita').value = date.toISOString().split('T')[0];
              cargarHorasDisponibles();
          }
      });

      // Asegurarse de que el campo no se pueda vaciar
      document.getElementById('fechaCita').addEventListener('blur', function() {
          if (this.value === "") {
              // Restaurar el valor si el campo se quedó vacío
              this.value = picker.getMoment().format('YYYY-MM-DD');
          }
      });


      function presencialidad() {
        var fechaSeleccionada = document.getElementById("fechaCita").value;

        if (fechaSeleccionada) {
            // Realizamos la consulta AJAX para verificar la disponibilidad
            $.ajax({
                url: 'calendar_validate_date_mis_citas.php',
                method: 'POST',
                data: { fecha: fechaSeleccionada },
                success: function(response) {
                    // Limpiar el select de tipo de atención
                    var tipoAtencionSelect = document.getElementById('tipo_atencion');
                    tipoAtencionSelect.innerHTML = '<option value="">Seleccione tipo de atención</option><option value="2">Virtual</option>';

                    // Verificar si la fecha es válida para tipo presencial
                    if (response == 'presencial') {
                        var optionPresencial = document.createElement("option");
                        optionPresencial.value = "1";
                        optionPresencial.text = "Presencial";
                        tipoAtencionSelect.appendChild(optionPresencial);
                    }
                }
            });
        }
      }

      
      function cargarHorasDisponibles() {
          const fecha = document.getElementById('fechaCita').value;
          const id = 2; // ID fijo como solicitaste
          presencialidad()
          if (fecha) {
              fetch('', { // Enviar la solicitud al mismo archivo
                  method: 'POST',
                  headers: {
                      'Content-Type': 'application/json',
                  },
                  body: JSON.stringify({ fecha: fecha, id: id }),
              })
              .then(response => response.json())
              .then(data => {
                  const timeSelect = document.getElementById('horaCita');
                  timeSelect.innerHTML = ''; // Limpiar opciones anteriores
                  data.horas.forEach(hora => {
                      const option = document.createElement('option');
                      option.value = hora.value;
                      option.textContent = hora.label;
                      timeSelect.appendChild(option);
                  });
              })
              .catch(error => console.error('Error:', error));
          }
      }



    
    
    // Función de encriptado XOR con clave
    function simpleEncrypt(text, key) {
        let output = '';
        for (let i = 0; i < text.length; i++) {
            output += String.fromCharCode(text.charCodeAt(i) ^ key.charCodeAt(i % key.length));
        }
        return btoa(output); // Codificar el resultado en base64
    }

      
    // Guardar nuevo evento
    $('#guardar_cita').on('submit', function (e) {
        e.preventDefault();
        $.post('calendar_save_events_mis_citas.php', $(this).serialize(), function (respuesta) {
        // Ejemplo de uso:
        if (respuesta.trim() === "Evento guardado correctamente") {
            // Redirige con el parámetro 'in' encriptado
            window.location.href = 'mis_citas?in=' + simpleEncrypt('newcal', '2020');
        } else if (respuesta.trim() === "No se encontraron registros con estado 0 para el usuario especificado") {
            window.location.href = 'mis_citas?in=' + simpleEncrypt('nocre', '2020');
        } else {
            window.location.href = 'mis_citas?in=' + simpleEncrypt('errnewcal', '2020');
        }
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
        const encryptedStatus = urlParams.get('in');

        if (encryptedStatus) {
            const status = simpleDecrypt(encryptedStatus, '2020'); // Descifra usando la clave

            if (status === 'newcal') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('La cita se agendó correctamente.');
            } else if (status === 'nocre') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.error('Usted no tiene créditos para este tipo de consulta');
            } else if (status === 'errnewcal') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.error('No se pudo realizar la conexión con la base de datos');
            }
        }
      });
  </script>
    <!-- General JS Scripts -->
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

  <?php
  $userID = $_SESSION['id'];

  // Verificar si el userID es válido
  if (!isset($userID) || empty($userID)) {
      die("Error: No se ha encontrado el usuario.");
  }

  // Opciones de terapia
  $tiposTerapia = [
      1 => "Individual",
      2 => "Pareja",
      5 => "Familia",
      6 => "Psiquiatría",
      7 => "Valoración",
      8 => "Nutrición"
  ];

  // 🔍 Nueva consulta SQL asegurando que devuelve datos correctos
  $sql = "SELECT tipo, COUNT(*) as cantidad FROM sessions WHERE userID = ? AND estado = 3 GROUP BY tipo";
  $stmt = $conn->prepare($sql);
  $stmt->bind_param("i", $userID);
  $stmt->execute();
  $result = $stmt->get_result();

  $terapias = [];

  // Verificar si hay resultados
  if ($result->num_rows > 0) {
      while ($row = $result->fetch_assoc()) {
          $tipo = intval($row['tipo']); // Convertir a entero por seguridad
          if (isset($tiposTerapia[$tipo])) { // Verificar si el tipo existe en la lista
              $terapias[$tipo] = $row['cantidad'];
          }
      }
  } else {
      error_log("🔴 No se encontraron sesiones para el usuario ID: " . $userID);
  }

  // Cerrar conexión
  $stmt->close();
  $conn->close();
  ?>

  <!-- Modal para Mostrar Sesiones Disponibles -->
  <div class="modal fade" id="sesionesModal" tabindex="-1" role="dialog" aria-labelledby="sesionesModalLabel" aria-hidden="true">
      <div class="modal-dialog" role="document">
          <div class="modal-content">
              <div class="modal-header">
                  <h5 class="modal-title" id="sesionesModalLabel">Sesiones Disponibles</h5>
                  <button type="button" class="close" data-bs-dismiss="modal" aria-label="Cerrar">
                      <span aria-hidden="true">&times;</span>
                  </button>
              </div>
              <div class="modal-body">
                  <?php if (!empty($terapias)) : ?>
                      <ul class="list-group">
                          <?php foreach ($terapias as $tipo => $cantidad) : ?>
                              <li class="list-group-item d-flex justify-content-between align-items-center">
                                  <?= $tiposTerapia[$tipo] ?>
                                  <span style="color:white;" class="badge bg-primary rounded-pill"><?= $cantidad ?></span>
                              </li>
                          <?php endforeach; ?>
                      </ul>
                  <?php else : ?>
                      <div class="alert alert-danger text-center" role="alert">
                          😢 En este momento no cuentas con saldo para agendar una cita. Escribenos a nuestro chat de atención. <a href="https://wa.me/573214193875">+57 321 419 3875</a> 😢
                      </div>
                  <?php endif; ?>
              </div>
              <div class="modal-footer">
                  <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cerrar</button>
              </div>
          </div>
      </div>
  </div>

  <!-- Script para abrir el modal automáticamente -->
  <script>
  document.addEventListener("DOMContentLoaded", function() {
      var sesionesModal = new bootstrap.Modal(document.getElementById('sesionesModal'));
      sesionesModal.show();
  });

  document.addEventListener("DOMContentLoaded", function() {
      var sesionesModalEl = document.getElementById('sesionesModal');
      var sesionesModal = new bootstrap.Modal(sesionesModalEl);

      // Mostrar el modal automáticamente al cargar la página
      sesionesModal.show();

      // Detectar todos los botones que cierran el modal
      document.querySelectorAll("[data-bs-dismiss='modal']").forEach(function(btn) {
          btn.addEventListener("click", function() {
              sesionesModal.hide();

              // Eliminar manualmente el backdrop
              setTimeout(function() {
                  document.querySelectorAll('.modal-backdrop').forEach(function(backdrop) {
                      backdrop.remove();
                  });
              }, 200); // Esperar un poco para evitar parpadeo
          });
      });
  });
  </script>


  
</body>
</html>
