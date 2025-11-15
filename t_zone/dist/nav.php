<?php
include "../../conexionsm.php";
date_default_timezone_set('America/Bogota');
$fecha_actual = date('Y-m-d');
$fecha_dos_anos_atras = date('Y-m-d', strtotime('-2 years', strtotime($fecha_actual)));
$userpoliticas = $_SESSION['id'];
$sql = "SELECT * FROM politicas WHERE fecha >= '$fecha_dos_anos_atras' AND userID = '$userpoliticas'";
$result = $conn->query($sql);

if ($result->num_rows == 0) {
  header("Location: aceptar_politicas");
  exit();
}

// Verifica si hay un consentimiento con estado 0 para el usuario y selecciona el tipo
$sql_consentimientos = "SELECT tipo_consentimiento FROM consentimientos WHERE id_user = '$userpoliticas' AND estado = 0 LIMIT 1";
$result_consentimientos = $conn->query($sql_consentimientos);

if ($result_consentimientos->num_rows > 0) {
    $row = $result_consentimientos->fetch_assoc();
    $tipo = $row['tipo_consentimiento'];

    // Redirige dependiendo del tipo de consentimiento
    switch ($tipo) {
        case 1:
            header("Location: consentimiento_psicologia");
            break;
        case 2:
            header("Location: consentimiento_psiquiatria");
            break;
        case 3:
            header("Location: consentimiento_adultos");
            break;
        case 4:
            header("Location: consentimiento_pareja");
            break;
        case 5:
            header("Location: consentimiento_kids");
            break;
        default:
            header("Location: consentimiento");
            break;
    }
    exit();
}
?>


<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">
                    <style>

                        .whatsapp-button {
                          position: fixed;
                          bottom: 20px; /* Ajusta según necesites */
                          right: 20px; /* Ajusta según necesites */
                          z-index: 9999;
                          animation: whatsapp-vibrate 4s infinite alternate;
                        }
                    
                        @keyframes whatsapp-vibrate {
                          0% { transform: translate(0, 0); }
                          2% { transform: translate(3px, 3px); }
                          4% { transform: translate(-3px, -3px); }
                          6% { transform: translate(3px, -3px); }
                          8% { transform: translate(-3px, 3px); }
                          10% { transform: translate(0, 0); }
                          100% { transform: translate(0, 0); }
                        }
                    
                        .whatsapp-button img {
                          width: 70px; /* Ajusta el tamaño del icono según necesites */
                          height: auto;
                        }
                        
                        .message-indicator {
                          position: absolute;
                          top: 5px;
                          right: 5px;
                          background-color: red;
                          color: white;
                          border-radius: 50%;
                          width: 20px;
                          height: 20px;
                          display: flex;
                          justify-content: center;
                          align-items: center;
                          font-size: 10px;
                        }
                        
                        .modal-backdrop {
                        background-color: transparent !important; /* Elimina la sombra visible */
                      }
                    
                    
                    </style>

                
                    <a href="https://api.whatsapp.com/send?phone=573214193875" class="whatsapp-button" target="_blank">
                      <img src="../../assets/images/whatsapp.png" alt="WhatsApp">
                      <div class="message-indicator">1</div>
                    </a>

      <nav class="navbar navbar-expand-lg main-navbar">
        <form class="form-inline mr-auto">
          <ul class="navbar-nav mr-3">
            <li><a href="#" data-toggle="sidebar" class="nav-link nav-link-lg"><i class="fas fa-bars"></i></a></li>
            <li><a href="#" data-toggle="search" class="nav-link nav-link-lg d-sm-none"><i class="fas fa-search"></i></a></li>
          </ul>
          <!--  <div class="search-element">
            <input class="form-control" type="search" placeholder="Search" aria-label="Search" data-width="250">
            <button class="btn" type="submit"><i class="fas fa-search"></i></button>
            <div class="search-backdrop"></div>
            <div class="search-result">
              <div class="search-header">
                Histories
              </div>
              <div class="search-item">
                <a href="#">How to hack NASA using CSS</a>
                <a href="#" class="search-close"><i class="fas fa-times"></i></a>
              </div>
              <div class="search-item">
                <a href="#">Kodinger.com</a>
                <a href="#" class="search-close"><i class="fas fa-times"></i></a>
              </div>
              <div class="search-item">
                <a href="#">#Stisla</a>
                <a href="#" class="search-close"><i class="fas fa-times"></i></a>
              </div>
              <div class="search-header">
                Result
              </div>
              <div class="search-item">
                <a href="#">
                  <img class="mr-3 rounded" width="30" src="assets/img/products/product-3-50.png" alt="product">
                  oPhone S9 Limited Edition
                </a>
              </div>
              <div class="search-item">
                <a href="#">
                  <img class="mr-3 rounded" width="30" src="assets/img/products/product-2-50.png" alt="product">
                  Drone X2 New Gen-7
                </a>
              </div>
              <div class="search-item">
                <a href="#">
                  <img class="mr-3 rounded" width="30" src="assets/img/products/product-1-50.png" alt="product">
                  Headphone Blitz
                </a>
              </div>
              <div class="search-header">
                Projects
              </div>
              <div class="search-item">
                <a href="#">
                  <div class="search-icon bg-danger text-white mr-3">
                    <i class="fas fa-code"></i>
                  </div>
                  Stisla Admin Template
                </a>
              </div>
              <div class="search-item">
                <a href="#">
                  <div class="search-icon bg-primary text-white mr-3">
                    <i class="fas fa-laptop"></i>
                  </div>
                  Create a new Homepage Design
                </a>
              </div>
            </div>
          </div>-->
        </form>
        <ul class="navbar-nav navbar-right">
          <!--<li class="dropdown dropdown-list-toggle"><a href="#" data-toggle="dropdown" class="nav-link nav-link-lg message-toggle beep"><i class="far fa-envelope"></i></a>
            <div class="dropdown-menu dropdown-list dropdown-menu-right">
              <div class="dropdown-header">Mensajes
                <div class="float-right">
                  <a href="#">Marcar todo como leído</a>
                </div>
              </div>
              <div class="dropdown-list-content dropdown-list-message">
                <a href="#" class="dropdown-item">
                  <div class="dropdown-item-avatar">
                    <img alt="image" src="assets/img/avatar/avatar-4.png" class="rounded-circle">
                  </div>
                  <div class="dropdown-item-desc">
                    <b>Ardian Rahardiansyah</b>
                    <p>Duis aute irure dolor in reprehenderit in voluptate velit ess</p>
                    <div class="time">16 Hours Ago</div>
                  </div>
                </a>
                <a href="#" class="dropdown-item">
                  <div class="dropdown-item-avatar">
                    <img alt="image" src="assets/img/avatar/avatar-5.png" class="rounded-circle">
                  </div>
                  <div class="dropdown-item-desc">
                    <b>Alfa Zulkarnain</b>
                    <p>Exercitation ullamco laboris nisi ut aliquip ex ea commodo</p>
                    <div class="time">Yesterday</div>
                  </div>
                </a>
              </div>
              <div class="dropdown-footer text-center">
                <a href="#">View All <i class="fas fa-chevron-right"></i></a>
              </div>
            </div>
          </li>-->
          <?php

            $sql = "SELECT * FROM alertas 
            WHERE del = 0 
            AND (FIND_IN_SET(?, pub) OR ids = ?) 
            AND ID NOT IN (SELECT id_alerta FROM alertasviews WHERE id_user = ?) 
            ORDER BY fec DESC";
            
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("iii", $_SESSION['permiso'], $_SESSION['id'], $_SESSION['id']); // Asegúrate que $_SESSION['permiso'] es correcto
            $stmt->execute();
            $result = $stmt->get_result();

            // Contar cuántas alertas hay
            $num_alertas = $result->num_rows;

            // Función para calcular el tiempo transcurrido
            function tiempoTranscurrido($fecha) {
                $segundos = time() - strtotime($fecha);
                $minutos = round($segundos / 60);
                $horas = round($minutos / 60);
                $dias = round($horas / 24);

                if ($segundos < 60) {
                    return "hace unos segundos";
                } elseif ($minutos < 60) {
                    return "hace $minutos min";
                } elseif ($horas < 24) {
                    return "hace $horas h";
                } else {
                    return "hace $dias días";
                }
            }
            ?>

            <li class="dropdown dropdown-list-toggle <?= ($num_alertas > 0) ? 'beep' : '' ?>">
                <a href="#" data-toggle="dropdown" class="nav-link notification-toggle nav-link-lg">
                    <i class="far fa-bell"></i>
                </a>
                <div class="dropdown-menu dropdown-list dropdown-menu-right">
                    <div class="dropdown-header">Alertas
                        <div class="float-right">
                            <a href="marcar_todo_leido.php">Marcar todo como leído</a>
                        </div>
                    </div>
                    <div class="dropdown-list-content dropdown-list-icons">
                        <?php
                        if ($num_alertas > 0) {
                          while ($row = $result->fetch_assoc()) {
                            // Limitar el mensaje a 35 caracteres y agregar "..." si es más largo
                            $mensajeCorto = strlen($row['men']) > 35 ? substr($row['men'], 0, 35) . '...' : $row['men'];
                        
                            echo '<div class="dropdown-item d-flex align-items-center border-bottom p-3" data-toggle="tooltip" data-placement="left" title="' . htmlspecialchars($row['men']) . '">
                                    <div class="dropdown-item-icon bg-info text-white rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                                        <i class="fas fa-bell"></i>
                                    </div>
                                    <div class="ml-3 flex-grow-1">
                                        <strong class="d-block text-dark" style="font-size: 14px;">' . htmlspecialchars($row['tit']) . '</strong>
                                        <span class="text-muted d-block" style="font-size: 12px;">' . htmlspecialchars($mensajeCorto) . '</span>
                                        <small class="text-secondary d-block mt-1">' . tiempoTranscurrido($row['fec']) . '</small>
                                    </div>
                                    <div>
                                    <button class="btn btn-sm btn-outline-primary marcar-leido" data-id="' . $row['ID'] . '">✓</button>
                                    </div>
                                  </div>';
                          }

                        } else {
                            echo '<a href="#" class="dropdown-item text-center text-muted">No hay alertas</a>';
                        }
                        ?>
                        <script>
                        $(document).ready(function(){
                            $('[data-toggle="tooltip"]').tooltip(); 
                        });
                        </script>
                        <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
                        <script>
                        $(document).ready(function() {
                            $(".dropdown-list-content").on("click", ".marcar-leido", function(e) {
                                e.preventDefault();

                                var alertaID = $(this).data("id");

                                $.ajax({
                                    url: "alertas_marcar_leido.php",
                                    type: "POST",
                                    data: { id_alerta: alertaID },
                                    dataType: "json",
                                    success: function(response) {
                                        if (response.status === "success") {
                                            location.reload(); // Recargar la página después de marcar como leído
                                        } else {
                                            alert("Error: " + response.message);
                                        }
                                    },
                                });
                            });
                        });


                        </script>                        
                    </div>
                    <div class="dropdown-footer text-center">
                        <a>...................</a>
                    </div>
                </div>
            </li>

          <li class="dropdown"><a href="#" data-toggle="dropdown" class="nav-link dropdown-toggle nav-link-lg nav-link-user">
            <?php 
            if($_SESSION['foto'] === ""){
              $photo = "avatar-1.png";
            }else{
              $photo = $_SESSION['foto'];
            }
            ?>
            <img alt="image" src="assets/img/profile-photos/<?php echo $photo;?>" class="rounded-circle mr-1">
            <div class="d-sm-none d-lg-inline-block">Hola, <?php echo ucfirst(strtolower($_SESSION['pn_usu'])) . " " . ucfirst(strtolower($_SESSION['pa_usu'])); ?></div></a>
            <div class="dropdown-menu dropdown-menu-right">
              <div class="dropdown-title">Administración</div>
              <a href="profile" class="dropdown-item has-icon">
                <i class="far fa-user"></i> Perfil
              </a>
              <?php
              // Definir el conjunto de caracteres permitidos
              $caracteres_permitidos = '0123456789';
              $codigo_recuperacion = '';

              // Generar el código de 6 caracteres
              for ($i = 0; $i < 6; $i++) {
                  $codigo_recuperacion .= $caracteres_permitidos[random_int(0, strlen($caracteres_permitidos) - 1)];
              }

              // Función XOR + Base64
              function xorEncryptDecrypt($input, $key) {
                  $output = '';
                  for ($i = 0; $i < strlen($input); $i++) {
                      $output .= chr(ord($input[$i]) ^ $key);
                  }

                  // Codificar el resultado en Base64 para que sea seguro en URLs y HTML
                  return base64_encode($output);
              }

              // Codificar el código de recuperación con la clave
              $codigo_codificado = xorEncryptDecrypt($codigo_recuperacion, 2020);

              // Codificar el código en la URL
              $codigo_codificado_url = urlencode($codigo_codificado);
              ?>

              <!-- Enviar el código en el enlace -->
              <a href="resset_pass/reset_pass.php?codrp=<?php echo $codigo_codificado_url;?>" class="dropdown-item has-icon">
                  <i class="fas fa-cog"></i> Cambiar Contraseña
              </a>
              <div class="dropdown-divider"></div>
              <a href="controlador_cerrar_sesion" class="dropdown-item has-icon text-danger">
                <i class="fas fa-sign-out-alt"></i> Cerrar Sesión
              </a>
            </div>
          </li>
        </ul>
      </nav>
      <div class="main-sidebar sidebar-style-2">
        <aside id="sidebar-wrapper">
          <div class="sidebar-brand">
            <a href="index"><img src="../../assets/images/icosn.png" alt="" style="width: 60%; height: auto;"></a>
          </div>
          <div class="sidebar-brand sidebar-brand-sm">
            <a href="index"><img src="../../assets/images/icolet.png" alt="" style="width: 70%; height: auto;"></a>
          </div>
          <ul class="sidebar-menu">
            <?php
            if($_SESSION['permiso'] === 1 || $_SESSION['permiso'] === 99){
            ?>
              <li class="menu-header">Administración</li>
                <li><a class="nav-link" href="tablero"><i class="fas fa-pencil-ruler"></i> <span>Tablero</span></a></li>
                <li><a class="nav-link" href="pagos"><i class="fas fa-money-bill"></i> <span>Pagos</span></a></li>
              <li class="dropdown">
                <a href="#" class="nav-link has-dropdown"><i class="fa-solid fa-users"></i> <span>Usuarios</span></a>
                <ul class="dropdown-menu">
                  <?php
                  if($_SESSION['permiso'] === 99 || $_SESSION['numdoc'] === '1000693019'){
                  ?>
                  <li><a class="nav-link" href="usuarios">Internos</a></li>
                  <?php
                  }
                  ?>
                  <li><a class="nav-link" href="users_pac">Pacientes</a></li>
                </ul>
              </li>
              <?php if($_SESSION['permiso_blog'] === 1 || $_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){?>
              <li><a class="nav-link" href="blog_edit"><i class="fa-solid fa-blog"></i> <span>Blog</span></a></li>
              <?php
              }
              if($_SESSION['permiso_biblioteca'] === 1 || $_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){
              ?>
              <li><a class="nav-link" href="library_edit"><i class="fa-solid fa-book"></i> <span>Biblioteca</span></a></li>
              <?php
              }
              if($_SESSION['permiso_citas'] === 1 || $_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){
              ?>
              <li><a class="nav-link" href="citas_tmrr"><i class="fa-solid fa-calendar-days"></i> <span>Citas</span></a></li>
              <?php
              }
              if($_SESSION['permiso_promociones'] === 1 || $_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){
              ?>
              <li><a class="nav-link" href="promos"><i class="fa-solid fa-tag"></i> <span>Promociones</span></a></li>
              <?php
              }
              if($_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){
              ?>
              <li><a class="nav-link" href="popup"><i class="fas fa-box-open"></i> <span>Pop Up´s</span></a></li>
              <li><a class="nav-link" href="alertas"><i class="fas fa-stopwatch"></i> <span>Alertas</span></a></li>
              <?php
              }
              if($_SESSION['permiso_gastos'] === 1 || $_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){
              ?>
              <li><a class="nav-link" href="gastos"><i class="fa-solid fa-piggy-bank"></i> <span>Gastos</span></a></li>
              <?php
              }
              ?>
              <li class="dropdown">
                <a href="#" class="nav-link has-dropdown"><i class="fa-solid fa-cash-register"></i> <span>Comisiones</span></a>
                <ul class="dropdown-menu">
                  <li><a class="nav-link" href="pays">Por validar</a></li>
                  <li><a class="nav-link" href="pays_to_pay">Por pagar</a></li>
                  <li><a class="nav-link" href="pays_history">Historico</a></li>
                </ul>
              </li>
              <?php
              if($_SESSION['permiso_citas_pagos'] === 1 || $_SESSION['numdoc'] === "1014273279" || $_SESSION['numdoc'] === "1000693019"){
              ?>
              <li><a class="nav-link" href="agendar_citas"><i class="fa-solid fa-brain"></i> <span>Pagos</span></a></li>
            <?php
              }
            }
            if($_SESSION['permiso'] === 3 || $_SESSION['permiso'] === 99 || $_SESSION['numdoc'] === '1000693019'){
            ?>
              <li class="menu-header">Aplicativo</li>
              <li><a class="nav-link" href="calendar"><i class="fa-solid fa-calendar-days"></i> <span>Agenda</span></a></li>
              <li><a class="nav-link" href="citas_tmrr"><i class="fa-solid fa-calendar-days"></i> <span>Citas</span></a></li>
              <li><a class="nav-link" href="confirmacion"><i class="fa-solid fa-check"></i> <span>Confirmaciones</span></a></li>
              <li><a class="nav-link" href="users_pac"><i class="fa-solid fa-users"></i> <span>Pacientes</span></a></li>
              <li><a class="nav-link" href="blog_edit"><i class="fa-solid fa-blog"></i> <span>Blog</span></a></li>
              <li><a class="nav-link" href="disponibilidad"><i class="fa-solid fa-business-time"></i> <span>Disponibilidad</span></a></li>
              <li><a class="nav-link" href="comisiones"><i class="fa-solid fa-piggy-bank"></i> <span>Comisiones</span></a></li>
            <?php
            }
            if($_SESSION['permiso'] === 9 || $_SESSION['permiso'] === 99 || $_SESSION['profesional_asignado'] > 0){
            ?>
              <li class="menu-header">Herramientas</li>
              <li><a class="nav-link" href="mis_citas"><i class="fa-solid fa-calendar-days"></i> <span>Mis Citas</span></a></li>
              <li><a class="nav-link" href="library"><i class="fa-solid fa-book"></i> <span>Biblioteca</span></a></li>
            <?php
            }
            ?>
          </ul>

          <!--<div class="mt-4 mb-4 p-3 hide-sidebar-mini">
            <a href="https://getstisla.com/docs" class="btn btn-primary btn-lg btn-block btn-icon-split">
              <i class="fas fa-rocket"></i> Documentation
            </a>
          </div>  -->  
        </aside>
      </div>

      <script>
        document.addEventListener("DOMContentLoaded", function () {
        // Obtenemos la ruta actual de la URL
        const currentUrl = window.location.pathname.split("/").pop() || "index.html"; // Extrae el último segmento de la URL

        // Seleccionamos todos los enlaces del menú lateral
        const menuItems = document.querySelectorAll(".sidebar-menu a");

        menuItems.forEach(item => {
            const itemHref = item.getAttribute("href");

            // Verifica si el href del enlace coincide con la URL actual o si la URL actual es el directorio raíz ("/" o "index.html")
            if (itemHref === currentUrl || (currentUrl === "index.html" && itemHref === "index")) {
            item.classList.add("active"); // Añade la clase active al enlace

            // Encuentra el elemento <li> que contiene el enlace
            const parentLi = item.closest("li");
            if (parentLi) {
                parentLi.classList.add("active"); // Añade la clase active al <li> contenedor

                // Verifica si el <li> es parte de un dropdown (menú desplegable)
                const dropdownParent = parentLi.closest(".dropdown");
                if (dropdownParent) {
                dropdownParent.classList.add("active"); // Añade la clase active al menú desplegable padre
                }
            }
            }
        });
        });
</script>