<?php 
  if (session_status() === PHP_SESSION_NONE) {
    session_start();
  }

  if (!empty($_SESSION["id"])) {
    header("location: index");
    exit();
  }
  
  include "controlador_login.php";

  $cambio_clave = $_GET['cnts'] ?? '';
  
?>


<!DOCTYPE html>
<html lang="es">
<head>
  <meta charset="UTF-8">
  <meta content="width=device-width, initial-scale=1, maximum-scale=1, shrink-to-fit=no" name="viewport">
  <title>SM &rsaquo; Login</title>
  <!-- Incluir las librerías CSS y JS necesarias -->
  <link rel="stylesheet" href="assets/modules/bootstrap/css/bootstrap.min.css">
  <link rel="stylesheet" href="assets/modules/fontawesome/css/all.min.css">
  <link rel="stylesheet" href="assets/modules/izitoast/css/iziToast.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <link rel="icon" href="../../assets/images/icolet.png" type="image/x-icon">
  <style>
        /* Estilos para el toast */
        #toast {
            visibility: hidden; /* Ocultarlo por defecto */
            min-width: 300px;
            margin: 0 auto;
            background-color: #4CAF50; /* Verde */
            color: #fff;
            text-align: left;
            border-radius: 8px;
            padding: 15px;
            position: fixed;
            z-index: 1000;
            top: 20px;
            right: 20px;
            box-shadow: 0px 4px 10px rgba(0, 0, 0, 0.2);
            opacity: 0;
            transition: opacity 0.5s ease-out, visibility 0s linear 0.5s;
            font-family: Arial, sans-serif;
            font-size: 14px;
        }

        #toast.show {
            visibility: visible;
            opacity: 1;
            transition: opacity 0.5s ease-in;
        }
    </style>
</head>
<body>
  <div id="app">
    <section class="section">
      <div class="container mt-5">
        <div class="row">
          <div class="col-12 col-sm-8 offset-sm-2 col-md-6 offset-md-3 col-lg-6 offset-lg-3 col-xl-4 offset-xl-4">
            <div class="login-brand">
              <img src="../../assets/images/icolet.png" alt="logo" width="100" class="shadow-light rounded-circle">
            </div>

            <div class="card card-primary">
              <div class="card-header"><h4>Inicio de Sesión</h4></div>
              <div class="card-body">
                <form method="POST" action="login" class="needs-validation" novalidate="">
                  <div class="form-group">
                    <label for="user">Usuario</label>
                    <input id="user" type="email" class="form-control" name="user" tabindex="1" required autofocus>
                  </div>

                  <div class="form-group">
                    <div class="d-block">
                      <label for="password" class="control-label">Contraseña</label>
                    </div>
                    <input id="password" type="password" class="form-control" name="password" tabindex="2" required>
                  </div>

                  <div class="form-group">
                    <input type="submit" class="btn btn-primary btn-lg btn-block" tabindex="4" value="Iniciar Sesión" name="btningresar">
                  </div>
                </form>
              </div>
            </div>
            <div class="mt-5 text-muted text-center">
              ¿no tienes una cuenta? <a href="auth-register.html">Crear una</a>
            </div>
          </div>
        </div>
      </div>
    </section>
  </div>
  <div id="toast">Cambio de clave exitoso. Por favor, inicie sesión nuevamente.</div>

    <!-- Div del toast -->
    <div id="toast">✅ Cambio de clave exitoso. Por favor, inicie sesión nuevamente.</div>

    <script>
        // Verifica si debe mostrar el toast
        <?php if ($cambio_clave === 'y'): ?>
        // Mostrar el toast
        const toast = document.getElementById('toast');
        toast.classList.add('show');

        // Ocultar después de 5 segundos
        setTimeout(() => {
            toast.classList.remove('show');
        }, 5000);
        <?php endif; ?>
    </script>
  <!-- Incluir JS -->
  <script src="assets/modules/jquery.min.js"></script>
  <script src="assets/modules/bootstrap/js/bootstrap.min.js"></script>
  <script src="assets/modules/izitoast/js/iziToast.min.js"></script>

  <script>
    <?php if (!empty($errorMessage)) { ?>
      iziToast.error({
        title: 'Error',
        message: '<?php echo $errorMessage; ?>',
        position: 'topRight'
      });
    <?php } ?>
  </script>
</body>
</html>
