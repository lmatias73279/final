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

include "../../conexionsm.php";
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Biblioteca</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->

  <!-- Template CSS -->
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="stylesheet" href="assets/css/components.css">
  <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">

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
            <h1>Aplicativo</h1>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="#">Aplicativo</a></div>
              <div class="breadcrumb-item"><a href="#">Biblioteca</a></div>
              <div class="breadcrumb-item">ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Biclioteca</h2>
            <p class="section-lead">
            Bienvenido a la Biblioteca Virtual de Sana Mente, donde encontrarás recursos gratuitos pensados para tu bienestar y crecimiento personal.
            </p>

            <div class="row">
            <?php
            // Suponiendo que $conn ya está configurada y conectada a la base de datos
            $query = "SELECT * FROM library";
            $result = mysqli_query($conn, $query);

            if ($result) {
              while ($row = mysqli_fetch_assoc($result)) {
                // Extrae los valores de cada fila
                $title = $row['title'];
                $doc = $row['doc'];
                $description = $row['description'];
                $img = $row['img'];
                ?>

                <div class="col-12 col-md-6 col-lg-6">
                  <div class="card">
                    <div class="card-body">
                      <div class="media">
                        <a href="assets/docs/library/<?php echo ($doc); ?>" download>
                          <img class="mr-3" src="assets/docs/library/<?php echo htmlspecialchars($img); ?>" alt="Image" style="width: 80px; height: 80px;">
                          <div class="media-body">
                            <h5 class="mt-0"><?php echo htmlspecialchars($title); ?></h5>
                            <p class="mb-0"><?php echo htmlspecialchars($description); ?></p>
                          </a>
                        </div>
                      </div>
                    </div>
                  </div>
                </div>

                <?php
              }
            } else {
              echo "Error en la consulta: " . mysqli_error($conn);
            }
            ?>

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
  
  <!-- JS Libraies -->

  <!-- Page Specific JS File -->
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
</body>
</html>