<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99 && $_SESSION['numdoc'] !== '1000693019'){
    header("location: login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Bienvenid@</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
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
    <div class="section-header">
      <h1>Configuración de Disponibilidad</h1>
    </div>

    <div class="section-body">
      <h2 class="section-title">Gestión de Horarios</h2>
      <p class="section-lead">
        Selecciona las franjas horarias en las que el profesional estará disponible para cada día de la semana. Además, podrás establecer días de excepción con su respectiva causa.
      </p>

      <div class="row">
        <!-- Sección de disponibilidad por día -->
        <div class="col-12">
          <h4>Disponibilidad por Día</h4>
          <form action="disponibilidad_hours" method="POST" id="disponibilidad-form">
            <div class="d-flex flex-wrap justify-content-between">
              <!-- Contenedor para los días de la semana -->
              <div class="days-container">
              <?php
              include "../../conexionsm.php";
              $id = intval($_SESSION["id"]);

              // Consulta para obtener la disponibilidad de todos los días de la semana
              $query = "SELECT 
                  lu1d, lu1h, lu2d, lu2h, 
                  ma1d, ma1h, ma2d, ma2h, 
                  mi1d, mi1h, mi2d, mi2h, 
                  ju1d, ju1h, ju2d, ju2h, 
                  vi1d, vi1h, vi2d, vi2h, 
                  sa1d, sa1h, sa2d, sa2h 
              FROM disponibilidad WHERE id_user = ?";
              $stmt = $conn->prepare($query);
              $stmt->bind_param("i", $id);
              $stmt->execute();
              $result = $stmt->get_result();

              if ($result->num_rows > 0) {
                  $row = $result->fetch_assoc();
                  // Lunes
                  $lu1d = $row['lu1d'];
                  $lu1h = $row['lu1h'];
                  $lu2d = $row['lu2d'];
                  $lu2h = $row['lu2h'];
                  // Martes
                  $ma1d = $row['ma1d'];
                  $ma1h = $row['ma1h'];
                  $ma2d = $row['ma2d'];
                  $ma2h = $row['ma2h'];
                  // Miércoles
                  $mi1d = $row['mi1d'];
                  $mi1h = $row['mi1h'];
                  $mi2d = $row['mi2d'];
                  $mi2h = $row['mi2h'];
                  // Jueves
                  $ju1d = $row['ju1d'];
                  $ju1h = $row['ju1h'];
                  $ju2d = $row['ju2d'];
                  $ju2h = $row['ju2h'];
                  // Viernes
                  $vi1d = $row['vi1d'];
                  $vi1h = $row['vi1h'];
                  $vi2d = $row['vi2d'];
                  $vi2h = $row['vi2h'];
                  // Sábado
                  $sa1d = $row['sa1d'];
                  $sa1h = $row['sa1h'];
                  $sa2d = $row['sa2d'];
                  $sa2h = $row['sa2h'];
              } else {
                  // Si no hay datos, asignamos 0 a todos
                  $lu1d = $lu1h = $lu2d = $lu2h = 0;
                  $ma1d = $ma1h = $ma2d = $ma2h = 0;
                  $mi1d = $mi1h = $mi2d = $mi2h = 0;
                  $ju1d = $ju1h = $ju2d = $ju2h = 0;
                  $vi1d = $vi1h = $vi2d = $vi2h = 0;
                  $sa1d = $sa1h = $sa2d = $sa2h = 0;
              }
              ?>

              <!-- Lunes -->
              <div class="day-block" style="border: 2px solid #dfdfdf; padding: 10px; border-radius: 5px;">
                <label for="lunes">Lunes</label>
                <div class="franjas">
                  <div class="franja">
                    <span>Franja AM</span>
                    <select id="lunes-inicio1" name="lunes-inicio1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $lu1d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="lunes-fin1" name="lunes-fin1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $lu1h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="franja">
                    <span>Franja PM</span>
                    <select id="lunes-inicio2" name="lunes-inicio2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $lu2d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="lunes-fin2" name="lunes-fin2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $lu2h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Martes -->
              <div class="day-block" style="border: 2px solid #dfdfdf; padding: 10px; border-radius: 5px;">
                <label for="martes">Martes</label>
                <div class="franjas">
                  <div class="franja">
                    <span>Franja AM</span>
                    <select id="martes-inicio1" name="martes-inicio1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ma1d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="martes-fin1" name="martes-fin1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ma1h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="franja">
                    <span>Franja PM</span>
                    <select id="martes-inicio2" name="martes-inicio2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ma2d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="martes-fin2" name="martes-fin2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ma2h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Miércoles -->
              <div class="day-block" style="border: 2px solid #dfdfdf; padding: 10px; border-radius: 5px;">
                <label for="miercoles">Miércoles</label>
                <div class="franjas">
                  <div class="franja">
                    <span>Franja AM</span>
                    <select id="miercoles-inicio1" name="miercoles-inicio1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $mi1d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="miercoles-fin1" name="miercoles-fin1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $mi1h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="franja">
                    <span>Franja PM</span>
                    <select id="miercoles-inicio2" name="miercoles-inicio2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $mi2d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="miercoles-fin2" name="miercoles-fin2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $mi2h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Jueves -->
              <div class="day-block" style="border: 2px solid #dfdfdf; padding: 10px; border-radius: 5px;">
                <label for="jueves">Jueves</label>
                <div class="franjas">
                  <div class="franja">
                    <span>Franja AM</span>
                    <select id="jueves-inicio1" name="jueves-inicio1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ju1d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="jueves-fin1" name="jueves-fin1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ju1h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="franja">
                    <span>Franja PM</span>
                    <select id="jueves-inicio2" name="jueves-inicio2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ju2d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="jueves-fin2" name="jueves-fin2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $ju2h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Viernes -->
              <div class="day-block" style="border: 2px solid #dfdfdf; padding: 10px; border-radius: 5px;">
                <label for="viernes">Viernes</label>
                <div class="franjas">
                  <div class="franja">
                    <span>Franja AM</span>
                    <select id="viernes-inicio1" name="viernes-inicio1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $vi1d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="viernes-fin1" name="viernes-fin1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $vi1h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="franja">
                    <span>Franja PM</span>
                    <select id="viernes-inicio2" name="viernes-inicio2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $vi2d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="viernes-fin2" name="viernes-fin2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $vi2h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Sábado -->
              <div class="day-block" style="border: 2px solid #dfdfdf; padding: 10px; border-radius: 5px;">
                <label for="sabado">Sábado</label>
                <div class="franjas">
                  <div class="franja">
                    <span>Franja AM</span>
                    <select id="sabado-inicio1" name="sabado-inicio1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $sa1d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="sabado-fin1" name="sabado-fin1" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $sa1h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                  <div class="franja">
                    <span>Franja PM</span>
                    <select id="sabado-inicio2" name="sabado-inicio2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $sa2d ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                    <span> - </span>
                    <select id="sabado-fin2" name="sabado-fin2" class="form-control">
                      <?php for ($i = 0; $i < 24; $i++): ?>
                        <option value="<?= $i ?>" <?= $i == $sa2h ? 'selected' : '' ?>><?= str_pad($i, 2, '0', STR_PAD_LEFT) ?>:00</option>
                      <?php endfor; ?>
                    </select>
                  </div>
                </div>
              </div>

              </div>
            </div>

            <!-- Botón de guardar -->
            <div class="col-12 mt-4 text-center">
              <button type="submit" class="btn btn-primary">Guardar Cambios</button>
            </div>
          </form>
        </div>
      </div>

      <!-- Sección de días de excepción -->

      <div class="container mt-5">
        <ul class="nav nav-tabs">
            <li class="nav-item">
                <button class="nav-link" data-tab="0">Excepción Dias</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-tab="2">Excepción Horas</button>
            </li>
            <li class="nav-item">
                <button class="nav-link" data-tab="1">Trabajo Presencial</button>
            </li>
        </ul>

        <div id="excepcion-section" class="wizard-section mt-4">
          <form action="disponibilidad_exeption" method="POST" id="excepciones-form">
            <div class="form-group">
                <label for="fecha-excepcion">Fecha de Excepción</label>
                <input type="date" class="form-control" id="fecha-excepcion" name="fecha-excepcion" required min="<?php echo date('Y-m-d', strtotime('tomorrow')); ?>">
            </div>
            <div class="form-group">
              <label for="causal-excepcion">Causal</label>
              <input type="text" class="form-control" id="causal-excepcion" name="causal-excepcion" placeholder="Ej. Permiso de trabajo" required oninput="this.value = this.value.toUpperCase()">
            </div>
            <div class="text-center mt-3">
              <button type="submit" class="btn btn-primary">Agregar Excepción</button>
            </div>
          </form>

          <?php 
          // Realizar la consulta para obtener los registros de la base de datos donde id_user = $id
          $query = "SELECT id, date, description FROM days_exception WHERE id_user = ? AND status = 0";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $id); // Vincula el id del usuario
          $stmt->execute();
          $result = $stmt->get_result();

          ?>

          <table class="table table-bordered mt-4">
              <thead>
                  <tr>
                      <th>Fecha</th>
                      <th>Causal</th>
                      <th>Acciones</th>
                  </tr>
              </thead>
              <tbody>
                  <?php
                  // Verificar si hay resultados
                  if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                          // Mostrar los registros
                          echo "<tr>";
                          echo "<td>" . date("d/m/Y", strtotime($row['date'])) . "</td>";  // Formato dd/mm/yyyy
                          echo "<td>" . ucfirst(strtolower(htmlspecialchars($row['description']))) . "</td>";
                          echo "<td><button class='btn btn-danger' data-toggle='modal' data-target='#deleteModal' data-id='" . $row['id'] . "'>Eliminar</button></td>";
                          echo "</tr>";
                      }
                  } else {
                      // Si no hay resultados, mostrar un mensaje
                      echo "<tr><td colspan='3'>No hay excepciones registradas para este usuario.</td></tr>";
                  }
                  ?>
              </tbody>
          </table>



          <?php
          $stmt->close();
          ?>
        </div>




        <div id="excepcion-section-hours" class="wizard-section mt-4">
          <form action="disponibilidad_exeption_hour" method="POST" id="excepciones-form">
            <div class="form-group">
                <label for="fecha-excepcion-hour">Fecha de Excepción Horas</label>
                <input type="date" class="form-control" id="fecha-excepcion-hour" name="fecha-excepcion-hour" required min="<?php echo date('Y-m-d', strtotime('tomorrow')); ?>">
            </div>
            <div class="form-group">
              <label for="hora-excepcion-hour">Hora</label>
              <select class="form-control" id="hora-excepcion-hour" name="hora-excepcion-hour" required>
                <option value="">Selecciones una hora</option>
                <option value="000000">00:00 AM</option>
                <option value="010000">01:00 AM</option>
                <option value="020000">02:00 AM</option>
                <option value="030000">03:00 AM</option>
                <option value="040000">04:00 AM</option>
                <option value="050000">05:00 AM</option>
                <option value="060000">06:00 AM</option>
                <option value="070000">07:00 AM</option>
                <option value="080000">08:00 AM</option>
                <option value="090000">09:00 AM</option>
                <option value="100000">10:00 AM</option>
                <option value="110000">11:00 AM</option>
                <option value="120000">12:00 AM</option>
                <option value="130000">01:00 PM</option>
                <option value="140000">02:00 PM</option>
                <option value="150000">03:00 PM</option>
                <option value="160000">04:00 PM</option>
                <option value="170000">05:00 PM</option>
                <option value="180000">06:00 PM</option>
                <option value="190000">07:00 PM</option>
                <option value="200000">08:00 PM</option>
                <option value="210000">09:00 PM</option>
                <option value="220000">10:00 PM</option>
                <option value="230000">11:00 PM</option>
              </select>
            </div>
            <div class="form-group">
              <label for="causal-excepcion-hour">Causal</label>
              <input type="text" class="form-control" id="causal-excepcion-hour" name="causal-excepcion-hour" placeholder="Ej. Gimnasio" required>
            </div>
            <div class="text-center mt-3">
              <button type="submit" class="btn btn-primary">Agregar Excepción</button>
            </div>
          </form>

          <?php 
          // Realizar la consulta para obtener los registros de la base de datos donde id_user = $id
          $query = "SELECT id, date, hour, description FROM hours_exception WHERE id_user = ? AND status = 0";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $id); // Vincula el id del usuario
          $stmt->execute();
          $result = $stmt->get_result();

          ?>

          <table class="table table-bordered mt-4">
              <thead>
                  <tr>
                      <th>Fecha</th>
                      <th>Hora</th>
                      <th>Causal</th>
                      <th>Acciones</th>
                  </tr>
              </thead>
              <tbody>
                  <?php
                  // Verificar si hay resultados
                  if ($result->num_rows > 0) {
                      while ($row = $result->fetch_assoc()) {
                          // Mostrar los registros
                          echo "<tr>";
                          echo "<td>" . date("d/m/Y", strtotime($row['date'])) . "</td>";  // Formato dd/mm/yyyy
                          echo "<td>" . date("h:i A", strtotime($row['hour'])) . "</td>";  // Ejemplo: 03:45 PM
                          echo "<td>" . ucfirst(strtolower(htmlspecialchars($row['description']))) . "</td>";
                          echo "<td><button class='btn btn-danger' data-toggle='modal' data-target='#deleteModalHora' data-id='" . $row['id'] . "'>Eliminar</button></td>";
                          echo "</tr>";
                      }
                  } else {
                      // Si no hay resultados, mostrar un mensaje
                      echo "<tr><td colspan='3'>No hay excepciones registradas para este usuario.</td></tr>";
                  }
                  ?>
              </tbody>
          </table>



          <?php
          $stmt->close();
          ?>
        </div>



        
        <!-- Sección de Presencialidad -->
        <div id="presencial-section" class="wizard-section mt-4">
          <form action="disponibilidad_presencial" method="POST" id="presencial-form">
            <div class="form-group">
              <label for="fecha-presencial">Fecha de presencialidad</label>
              <input type="date" class="form-control" id="fecha-presencial" name="fecha-presencial" required min="<?php echo date('Y-m-d', strtotime('today')); ?>">
            </div>
            <div class="text-center mt-3">
              <button type="submit" class="btn btn-primary">Agregar Fecha</button>
            </div>
          </form>

          <?php 
          // Consulta para obtener los registros de la base de datos donde id_user = $id
          $query = "SELECT id, date FROM days_presencial WHERE id_user = ? AND status = 0";
          $stmt = $conn->prepare($query);
          $stmt->bind_param("i", $id); // Vincula el id del usuario
          $stmt->execute();
          $result = $stmt->get_result();
          ?>

          <table class="table table-bordered mt-4">
            <thead>
              <tr>
                <th>Fecha</th>
                <th>Acciones</th>
              </tr>
            </thead>
            <tbody>
              <?php
              // Verificar si hay resultados
              if ($result->num_rows > 0) {
                while ($row = $result->fetch_assoc()) {
                  // Mostrar los registros
                  echo "<tr>";
                  echo "<td>" . date("d/m/Y", strtotime($row['date'])) . "</td>";  // Formato dd/mm/yyyy
                  echo "<td><button class='btn btn-danger' data-toggle='modal' data-target='#deletePresencialModal' data-id='" . $row['id'] . "'>Eliminar</button></td>";
                  echo "</tr>";
                }
              } else {
                // Si no hay resultados, mostrar un mensaje
                echo "<tr><td colspan='2'>No hay fechas de presencialidad registradas para este usuario.</td></tr>";
              }
              ?>
            </tbody>
          </table>

          <?php
          $stmt->close();
          ?>
        </div>
      </div>
    </div>
  </section>
</div>

<!-- Estilos en línea -->
<style>


#excepciones-form .form-control,
    #presencial-form .form-control {
      width: 100%;
    }
    .wizard-section {
      display: none;
    }
    .wizard-section.active {
      display: block;
    }

  .form-control {
    width: 100px;
    margin-bottom: 10px;
  }

  .days-container {
    display: flex;
    flex-wrap: wrap;
    gap: 20px; /* Espacio entre los días */
    justify-content: space-between;
  }

  .day-block {
    width: 32%; /* Tamaño flexible para tres días por fila */
    text-align: center;
  }

  .franjas {
    display: flex;
    flex-direction: column;
    gap: 10px; /* Espacio entre las franjas */
  }

  .franja {
    display: flex;
    justify-content: space-between;
  }

  .btn-primary {
    background-color: #007bff;
    color: white;
  }

  .btn-primary:hover {
    background-color: #0056b3;
  }

  .table {
    width: 100%;
    margin-top: 20px;
  }

  .mt-4 {
    margin-top: 20px;
  }

  .mt-5 {
    margin-top: 40px;
  }

  label {
    display: block;
  }

  /* Media Queries para hacerlo responsive */
  @media (max-width: 768px) {
    .day-block {
      width: 48%; /* Dos días por fila */
    }
  }

  @media (max-width: 576px) {
    .day-block {
      width: 100%; /* Un solo día por fila */
    }
  }
</style>



      <?php include "footer.php"?>
    </div>
  </div>

  <!-- Modal de Confirmación para Eliminar Presencialidad -->
  <div class="modal fade" id="deletePresencialModal" tabindex="-1" role="dialog" aria-labelledby="deletePresencialModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deletePresencialModalLabel">Confirmar Eliminación</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que quieres eliminar esta fecha de presencialidad?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="confirmDeletePresencial">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmación para Eliminar Excepción -->
  <div class="modal fade" id="deleteModal" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabel">Confirmar Eliminación</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que quieres eliminar esta excepción?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="confirmDelete">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal de Confirmación para Eliminar Excepción Hora-->
  <div class="modal fade" id="deleteModalHora" tabindex="-1" role="dialog" aria-labelledby="deleteModalLabelHora" aria-hidden="true">
    <div class="modal-dialog" role="document">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteModalLabelHora">Confirmar Eliminación</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Close">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          ¿Estás seguro de que quieres eliminar esta excepción de hora?
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-danger" id="confirmDeleteHora">Eliminar</button>
        </div>
      </div>
    </div>
  </div>

  <!-- Script para pasar el ID del registro al modal -->
  <script>
  document.addEventListener('DOMContentLoaded', function () {
      // Al abrir el modal de eliminación para presencialidad
      $('#deletePresencialModal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget); // Botón que activó el modal
          var recordId = button.data('id'); // Extraer el ID del botón
          var modal = $(this);
          modal.find('#confirmDeletePresencial').data('id', recordId); // Asignar el ID al botón de confirmación
      });

      // Al abrir el modal de eliminación para excepciones
      $('#deleteModal').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget); // Botón que activó el modal
          var recordId = button.data('id'); // Extraer el ID del botón
          var modal = $(this);
          modal.find('#confirmDelete').data('id', recordId); // Asignar el ID al botón de confirmación
      });

      
      
      // Al abrir el modal de eliminación para excepciones de horas (AGREGADO)
      $('#deleteModalHora').on('show.bs.modal', function (event) {
          var button = $(event.relatedTarget); // Botón que activó el modal
          var recordId = button.data('id'); // Extraer el ID del botón
          var modal = $(this);
          modal.find('#confirmDeleteHora').data('id', recordId); // Asignar el ID al botón de confirmación
      });
      
      // Función de encriptado XOR con clave
      function simpleEncrypt(text, key) {
          let output = '';
          for (let i = 0; i < text.length; i++) {
              output += String.fromCharCode(text.charCodeAt(i) ^ key.charCodeAt(i % key.length));
          }
          return btoa(output); // Codificar el resultado en base64
      }

      // Al hacer clic en el botón de "Eliminar" dentro del modal de presencialidad
      document.getElementById('confirmDeletePresencial').addEventListener('click', function () {
          var recordId = $(this).data('id'); // Obtener el ID del registro que se eliminará

          // Enviar la solicitud fetch para actualizar el estado
          fetch('disponibilidad_update_status_pre.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded', // Tipo de contenido
              },
              body: `id=${recordId}&status=1` // Los datos a enviar
          })
          .then(response => response.text())
          .then(data => {
            // Ejemplo de uso con encriptación para las cadenas de redirección
            if (data === 'success') {
                // Redirige con el parámetro 'in' encriptado para éxito
                window.location.href = 'disponibilidad?in=' + simpleEncrypt('delpres', '2020') + '&tab=1'; // Redirige a la URL deseada
            } else {
                // Redirige con el parámetro 'in' encriptado para error
                window.location.href = 'disponibilidad?in=' + simpleEncrypt('errdelpres', '2020') + '&tab=1'; // Redirige a la URL deseada
            }
          })
          .catch(error => {
              console.log('Error:', error); // Muestra cualquier error en la consola
          });

          // Cerrar el modal después de hacer clic en "Eliminar"
          $('#deletePresencialModal').modal('hide');
      });

      // Al hacer clic en el botón de "Eliminar" dentro del modal de excepción
      document.getElementById('confirmDelete').addEventListener('click', function () {
          var recordId = $(this).data('id'); // Obtener el ID del registro que se eliminará

          // Enviar la solicitud fetch para actualizar el estado
          fetch('disponibilidad_update_status_exp.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded', // Tipo de contenido
              },
              body: `id=${recordId}&status=1` // Los datos a enviar
          })
          .then(response => response.text())
          .then(data => {
            // Ejemplo de uso con encriptación para las cadenas de redirección
            if (data === 'success') {
                // Redirige con el parámetro 'in' encriptado para éxito
                window.location.href = 'disponibilidad?in=' + simpleEncrypt('delexp', '2020'); // Redirige a la URL deseada
            } else {
                // Redirige con el parámetro 'in' encriptado para error
                window.location.href = 'disponibilidad?in=' + simpleEncrypt('errdelexp', '2020'); // Redirige a la URL deseada
            }
          })
          .catch(error => {
              console.log('Error:', error); // Muestra cualquier error en la consola
          });

          // Cerrar el modal después de hacer clic en "Eliminar"
          $('#deleteModal').modal('hide');
      });

      

      // Al hacer clic en el botón de "Eliminar" dentro del modal de excepción
      document.getElementById('confirmDeleteHora').addEventListener('click', function () {
          var recordId = $(this).data('id'); // Obtener el ID del registro que se eliminará

          // Enviar la solicitud fetch para actualizar el estado
          fetch('disponibilidad_update_status_exp_hora.php', {
              method: 'POST',
              headers: {
                  'Content-Type': 'application/x-www-form-urlencoded', // Tipo de contenido
              },
              body: `id=${recordId}&status=1` // Los datos a enviar
          })
          .then(response => response.text())
          .then(data => {
            // Ejemplo de uso con encriptación para las cadenas de redirección
            if (data === 'success') {
                // Redirige con el parámetro 'in' encriptado para éxito
                window.location.href = 'disponibilidad?in=' + simpleEncrypt('delexph', '2020'); // Redirige a la URL deseada
            } else {
                // Redirige con el parámetro 'in' encriptado para error
                window.location.href = 'disponibilidad?in=' + simpleEncrypt('errdelexph', '2020'); // Redirige a la URL deseada
            }
          })
          .catch(error => {
              console.log('Error:', error); // Muestra cualquier error en la consola
          });

          // Cerrar el modal después de hacer clic en "Eliminar"
          $('#deleteModalHora').modal('hide');
      });
  });
  </script>
  <script>
  document.addEventListener("DOMContentLoaded", function () {
      // Obtención de las secciones y botones
      const exceptionSection = document.getElementById("excepcion-section");
      const exceptionSectionHours = document.getElementById("excepcion-section-hours");
      const presencialSection = document.getElementById("presencial-section");
      const buttons = document.querySelectorAll(".nav-link");

      // Función para mostrar la sección correspondiente
      function showSection(selectedTab) {
          // Remover clases 'active' de todas las secciones
          exceptionSection.classList.remove("active");
          exceptionSectionHours.classList.remove("active");
          presencialSection.classList.remove("active");

          // Mostrar la sección correspondiente según el tab seleccionado
          if (selectedTab === "1") {
              presencialSection.classList.add("active");
          }else if(selectedTab === "2") {
            exceptionSectionHours.classList.add("active");
          } else {
              exceptionSection.classList.add("active");
          }

          // Activar el botón correspondiente en el nav
          buttons.forEach(button => {
              // Remover la clase 'active' de todos los botones
              button.classList.remove("active");
              // Si el botón corresponde a la sección seleccionada, añadirle la clase 'active'
              if (button.getAttribute("data-tab") === selectedTab) {
                  button.classList.add("active");
              }
          });
      }

      // Inicializar según el parámetro en la URL (si existe)
      const params = new URLSearchParams(window.location.search);
      const tab = params.get("tab");
      if (tab) {
          showSection(tab);
      } else {
          // Si no hay parámetro 'tab', mostrar la primera sección por defecto
          showSection("0");
      }

      // Manejar los clics en los botones del nav
      buttons.forEach(button => {
          button.addEventListener("click", function () {
              // Obtener el valor del atributo 'data-tab' para determinar la sección a mostrar
              const selectedTab = this.getAttribute("data-tab");

              // Cambiar la sección activa
              showSection(selectedTab);

              // Actualizar el parámetro 'tab' en la URL para reflejar el estado de la sección seleccionada
              const newUrl = window.location.pathname + "?tab=" + selectedTab;
              window.history.pushState({ path: newUrl }, "", newUrl);
          });
      });
  });
  </script>
  <!-- General JS Scripts -->
  <!-- Incluir jQuery desde un CDN (recomendado) -->

  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/popper.js"></script>
  <script src="assets/modules/tooltip.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/nicescroll/jquery.nicescroll.min.js"></script>
  <script src="assets/modules/moment.min.js"></script>
  <script src="assets/js/stisla.js"></script>
  
  <!-- JS Libraies -->

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

            if (status === 'acthours') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Disponibilidad horaria actualizada correctamente.');
            } else if (status === 'erracthours') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.error('No se pudo realizar conexión con la base de datos');
            } else if (status === 'newhours') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Disponibilidad horaria creada correctamente.');
            } else if (status === 'arrnewhours') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.error('No se pudo realizar conexión con la base de datos');
            } else if (status === 'delexp') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Día de excepción borrado correctamente.');
            } else if (status === 'errdelexp') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.error('No se pudo realizar conexión con la base de datos');
            } else if (status === 'delexph') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Hora de excepción borrada correctamente.');
            } else if (status === 'errdelexph') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.error('No se pudo realizar conexión con la base de datos');
            } else if (status === 'delpres') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.success('Día presencial borrado correctamente.');
            } else if (status === 'errdelpres') {
                toastr.options = {
                    "timeOut": "5000",
                    "extendedTimeOut": "1000",
                    "showMethod": "fadeIn",
                    "hideMethod": "fadeOut"
                };
                toastr.error('No se pudo realizar conexión con la base de datos');
            }
        }
      });
  </script>
  <!-- Page Specific JS File -->
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>