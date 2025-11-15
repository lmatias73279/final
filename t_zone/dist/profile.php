<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99 && $_SESSION['numdoc'] === "1014273279" && $_SESSION['numdoc'] === "1000693019"){
    header("location: login");
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Perfil</title>

  <!-- General CSS Files -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">

  <!-- CSS Libraries -->
  <link rel="stylesheet" href="assets/modules/bootstrap-social/bootstrap-social.css">
  <link rel="stylesheet" href="assets/modules/owlcarousel2/dist/assets/owl.carousel.min.css">
  <link rel="stylesheet" href="assets/modules/owlcarousel2/dist/assets/owl.theme.default.min.css">

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
            <h1>Administración</h1><i class="fa-brands fa-x-twitter"></i>
            <div class="section-header-breadcrumb">
              <div class="breadcrumb-item active"><a href="#">Administración</a></div>
              <div class="breadcrumb-item"><a href="#">Perfil</a></div>
              <div class="breadcrumb-item">Ver</div>
            </div>
          </div>

          <div class="section-body">
            <h2 class="section-title">Perfil</h2>
            <button type="button" class="btn btn-primary" id="openEditModal">Editar Usuario</button>
            <br></br>

            <?php 
            if($_SESSION['foto'] === ""){
              $photo = "avatar-1.png";
            }else{
              $photo = $_SESSION['foto'];
            }
            ?> 
            <div class="row">
              <div class="col-12 col-sm-12 col-lg-12">
                <div class="card profile-widget">
                <div class="profile-widget-header">                 
                  <img alt="image" src="assets/img/profile-photos/<?php echo $photo;?>" class="rounded-circle profile-widget-picture">
                  <!-- Icono de lápiz para editar -->
                  <button class="btn btn-link edit-icon" data-toggle="modal" data-target="#editPhotoModal">
                    <i class="fas fa-pencil-alt"></i>
                  </button>
                  
                  <div class="profile-widget-items">
                    <div class="profile-widget-item">
                      <?php
                        $propietary = $_SESSION['numdoc'];
                        $sqlCount = "SELECT COUNT(*) AS total FROM blog WHERE propietary = '$propietary'";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];
                      ?>
                      <div class="profile-widget-item-label">Blogs</div>
                      <div class="profile-widget-item-value"><?php echo $totalRegistros;?></div>
                    </div>
                    <div class="profile-widget-item">
                      <?php
                        $propietary = $_SESSION['id'];
                        $sqlCount = "SELECT COUNT(*) AS total FROM usuarios WHERE profesional_asignado = '$propietary' AND estado = 1";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];
                      ?>
                      <div class="profile-widget-item-label">Pacientes</div>
                      <div class="profile-widget-item-value"><?php echo $totalRegistros;?></div>
                    </div>
                    <div class="profile-widget-item">
                      <?php
                        $propietary = $_SESSION['id'];
                        $sqlCount = "SELECT COUNT(*) AS total FROM sessions WHERE psi = '$propietary'";
                        $totalResult = $conn->query($sqlCount);
                        $totalRegistros = $totalResult->fetch_assoc()['total'];
                      ?>
                      <div class="profile-widget-item-label">Consultas</div>
                      <div class="profile-widget-item-value"><?php echo $totalRegistros;?></div>
                    </div>
                  </div>
                </div>

                  <div class="profile-widget-description pb-0">
                    <div class="profile-widget-name"><?php echo ucfirst(strtolower($_SESSION['pn_usu'])) . " " . ucfirst(strtolower($_SESSION['sn_usu'])) . " " . ucfirst(strtolower($_SESSION['pa_usu'])) . " " . ucfirst(strtolower($_SESSION['sa_usu'])); ?> <div class="text-muted d-inline font-weight-normal"><div class="slash"></div><?php switch ($_SESSION['permiso']) {case 1: echo "Administrativo"; break; case 2: echo "Profesional"; break; case 3: echo "Empresa"; break; case 4: echo "Paciente"; break; default: echo "Permiso desconocido";}?></div></div>
                    <p><?php if (isset($_SESSION['id'])) {
                        // Prepara la consulta
                        $stmt = $conn->prepare("SELECT descripcion FROM usuarios WHERE ID = ? LIMIT 1");
                        $stmt->bind_param("i", $_SESSION['id']);  // Vincula el parámetro de ID
                        $stmt->execute();

                        // Obtén el resultado
                        $result = $stmt->get_result();

                        // Verifica si la consulta devolvió algún resultado
                        if ($row = $result->fetch_assoc()) {
                            // Muestra el valor de la columna "descripcion"
                            echo htmlspecialchars(ucfirst(mb_strtolower($row['descripcion'], 'UTF-8')));
                        } else {
                            echo "No se encontró una descripción para el usuario con ese ID.";
                        }

                        // Cierra la declaración
                        $stmt->close();
                    } else {
                        echo "Sin Descipción";
                    }?></p>
                  </div>
                  <div class="card-footer text-center pt-0">
                    <div class="font-weight-bold mb-2 text-small">Mis redes sociales</div>
                    <a href="https://www.facebook.com/sana.mente.colombia" class="btn btn-social-icon mr-1">
                        <img src="assets/icons/Facebook.svg" alt="" style="width: 35px; height: 35px; filter: invert(28%) sepia(83%) saturate(4687%) hue-rotate(203deg) brightness(95%) contrast(108%);">
                    </a>
                    <a href="https://www.tiktok.com/@sana_mente.co" class="btn btn-social-icon mr-1">
                      <img src="assets/icons/TikTok.svg" alt="" style="width: 35px; height: 35px;">
                    </a>
                    <a href="https://www.instagram.com/sana_mente.co/" class="btn btn-social-icon mr-1">
                      <img src="assets/icons/Instagram.svg" alt="" style="width: 35px; height: 35px;">
                    </a>
                  </div>
                </div>
                
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
  
  <!-- JS Libraies -->
  <script src="assets/modules/owlcarousel2/dist/owl.carousel.min.js"></script>

  <!-- Page Specific JS File -->
  <script src="assets/js/page/components-user.js"></script>
  
  <!-- Template JS File -->
  <script src="assets/js/scripts.js"></script>
  <script src="assets/js/custom.js"></script>
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
      // Mostrar el modal de edición
      $('#openEditModal').on('click', function() {
          // Abre el modal de edición sin necesidad de cargar datos desde una tabla
          $('#exampleModal').modal('show');
      });

      // Mostrar el modal de confirmación al hacer clic en "Guardar Cambios"
      $('#edit-form').on('submit', function(event) {
          event.preventDefault();

          // Cierra el modal de edición antes de abrir el de confirmación
          $('#exampleModal').modal('hide');

          // Rellenar el resumen en el modal de confirmación
          $('#confirm-pais').text('Cargando...');  // Muestra un mensaje mientras se carga el país

          var codPais = $('#modal-pais').val();

          $.ajax({
              url: 'obtenerPais.php',  // Archivo PHP que va a obtener el nombre del país
              type: 'GET',
              data: { cod_pais: codPais },
              success: function(response) {
                  $('#confirm-pais').text(response);
              },
              error: function() {
                  $('#confirm-pais').text('Error al cargar el país');
              }
          });

          $('#confirm-tel_usu').text($('#modal-tel_usu').val());
          $('#confirm-cor_usu').text($('#modal-cor_usu').val());
          $('#confirm-descrip').text($('#modal-descripcion').val());

          // Mostrar el modal de confirmación
          $('#confirmModal').modal('show');
      });

      // Enviar el formulario de edición si el usuario confirma
      $('#confirm-update').on('click', function() {
          $('#edit-form')[0].submit();
      });
  });

  </script>

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
                  <input type="hidden" id="modal-id" name="id" value="<?php echo $_SESSION['id']; ?>">

                  <?php
                  // Conexión a la base de datos
                  include "controlador_login.php"; // Asegúrate de tener el archivo de conexión a la base de datos

                  // Obtener los datos del usuario usando su ID
                  $userId = $_SESSION['id'];
                  $sqlUser = "SELECT pais, tel_usu, cor_usu, descripcion FROM usuarios WHERE id = '$userId'"; // Asegúrate de que la tabla y las columnas sean correctas
                  $resultUser = $conn->query($sqlUser);
                  
                  if ($resultUser->num_rows > 0) {
                      $user = $resultUser->fetch_assoc(); // Obtener los datos del usuario
                  } else {
                      $user = []; // Si no se encuentra el usuario, usar un array vacío
                  }

                  // Consulta para obtener los países desde la base de datos ordenados por el nombre del país
                  $sqlPaises = "SELECT cod_pais, pais FROM paises ORDER BY pais ASC"; // Ordenado alfabéticamente por 'pais'
                  $resultPaises = $conn->query($sqlPaises);

                  if ($resultPaises->num_rows > 0) {
                      $paises = $resultPaises->fetch_all(MYSQLI_ASSOC); // Almacenamos los países en un array
                  } else {
                      $paises = []; // Si no hay resultados, definimos un array vacío
                  }
                  ?>

                  <!-- Campo para el país -->
                  <div class="form-group">
                      <label for="modal-pais">Seleccione un país</label>
                      <select id="modal-pais" name="pais" class="form-control" required>
                          <option value="">Seleccione un país</option>
                          <!-- Generar las opciones desde la base de datos -->
                          <?php foreach ($paises as $pais): ?>
                              <option value="<?php echo $pais['cod_pais']; ?>"
                                  <?php echo isset($user['pais']) && $user['pais'] == $pais['cod_pais'] ? 'selected' : ''; ?>>
                                  <?php echo $pais['pais']; ?>
                              </option>
                          <?php endforeach; ?>
                      </select>
                  </div>

                  <!-- Campo para el teléfono -->
                  <div class="form-group">
                      <label for="modal-tel_usu">Teléfono</label>
                      <input type="text" id="modal-tel_usu" name="tel_usu" class="form-control" value="<?php echo isset($user['tel_usu']) ? $user['tel_usu'] : ''; ?>" required>
                  </div>

                  <!-- Campo para el correo -->
                  <div class="form-group">
                      <label for="modal-cor_usu">Correo</label>
                      <input type="text" id="modal-cor_usu" name="cor_usu" class="form-control" value="<?php echo isset($user['cor_usu']) ? $user['cor_usu'] : ''; ?>" required>
                  </div>

                  <!-- Campo para la descripción -->
                  <div class="form-group mb-0">
                      <label>Descripción</label>
                      <textarea class="form-control" id="modal-descripcion" name="descripcion" style="height: 200px !important; resize: none;" required><?php echo isset($user['descripcion']) ? $user['descripcion'] : ''; ?></textarea>
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
                    <li><strong>Teléfono:</strong> <span id="confirm-tel_usu"></span></li>
                    <li><strong>Correo:</strong> <span id="confirm-cor_usu"></span></li>
                    <li><strong>Descripción:</strong> <span id="confirm-descrip"></span></li>
                </ul>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="confirm-update">Confirmar</button>
            </div>
        </div>
    </div>
</div>

<!-- Modal para editar foto de perfil -->
<div class="modal fade" id="editPhotoModal" tabindex="-1" aria-labelledby="editPhotoModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="editPhotoModalLabel">Editar Foto de Perfil</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body">
        <form action="upload_photo.php" method="POST" enctype="multipart/form-data">
          <div class="form-group">
            <label for="profilePhoto">Selecciona una nueva foto</label>
            <input type="file" class="form-control-file" id="profilePhoto" name="profilePhoto" required>
          </div>
          <button type="submit" class="btn btn-primary">Subir Foto</button>
        </form>
      </div>
    </div>
  </div>
</div>


</body>
</html>