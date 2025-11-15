<!DOCTYPE html>
<html lang="en">

  <head>

    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="https://fonts.googleapis.com/css?family=Open+Sans:100,200,300,400,500,600,700,800,900" rel="stylesheet">

    <title>Sana Mente</title>

    <!-- Bootstrap core CSS -->
    <link href="vendor/bootstrap/css/bootstrap.min.css" rel="stylesheet">


    <!-- Additional CSS Files -->
    <link rel="stylesheet" href="assets/css/fontawesome.css">
    <link rel="stylesheet" href="assets/css/templatemo-eduwell-style.css">
    <link rel="stylesheet" href="assets/css/owl.css">
    <link rel="stylesheet" href="assets/css/lightbox.css">
    <link rel="icon" href="assets/images/icolet.png" type="image/x-icon">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css" rel="stylesheet">

    <style>
    footer {
      background-color: #282828;
      color: #ffffff;
      padding: 20px 40px;
      display: flex;
      justify-content: center;
      align-items: center;
      flex-wrap: wrap;
    }

    .footer-container {
      display: flex;
      flex-wrap: wrap;
      max-width: 1200px;
      width: 100%;
      justify-content: space-between;
    }

    .footer-logo img {
      max-width: 150px;
    }

    .footer-contact, .footer-social, .footer-links {
      margin: 10px;
      flex: 1 1 200px;
    }

    .footer-contact h4, .footer-social h4, .footer-links h4 {
      font-size: 18px;
      margin-bottom: 10px;
    }
    
    .footer-contact p {
      color: white;
    }

    .footer-social a {
      margin: 0 10px;
      font-size: 24px;
      color: #ffffff;
      text-decoration: none;
    }

    .footer-social a:hover {
      color: #ff4500;
    }

    .footer-links a {
      display: block;
      margin: 5px 0;
      color: #ffffff;
      text-decoration: none;
    }

    .footer-links a:hover {
      text-decoration: underline;
    }

    /* Estilo responsivo */
    @media (max-width: 768px) {
      .footer-container {
        flex-direction: column;
        text-align: center;
      }
    }
    </style>
<!--

TemplateMo 573 EduWell

https://templatemo.com/tm-573-eduwell

-->
  </head>

<body>


<?php 
include "header.php";
?>

  <section class="get-info">
    <div class="container">
      <div class="row">
        <div class="col-lg-6">
          <div class="left-image">
            <img src="assets/images/Logo_responsive_vertical3x-9375193.webp" alt="">
          </div>
        </div>
        <div class="col-lg-6 align-self-center">
          <div class="section-heading">
            <h4>Sobre <em>Nosotros</em></h4>
            <p>No esperes más para comenzar tu viaje hacia un cambio funcional. Te invitamos a unirte a nuestra comunidad de personas comprometidas con su salud mental y emocional. Juntos, podemos construir un camino hacia tu bienestar integral. ¡Contáctanos hoy mismo y descubre cómo podemos ayudarte a lograrlo!</p>
          </div>
          <div class="row">
            <div class="col-lg-6">
              <div class="info-item">
                <div class="icon">
                  <img src="assets/images/service-icon-01.png" alt="">
                </div>
                <h4>Estrategia Personalizada</h4>
                <p>"Desarrollamos planes de acompañamiento adaptados a tus necesidades, con estrategias personalizadas que te ayudarán a alcanzar tus metas de bienestar emocional de manera efectiva y sostenible."</p>
              </div>
            </div>
            <div class="col-lg-6">
              <div class="info-item">
                <div class="icon">
                  <img src="assets/images/service-icon-02.png" alt="">
                </div>
                <h4>Ideas Creativas para tu Bienestar</h4>
                <p>"Incorporamos técnicas innovadoras y enfoques creativos en nuestras sesiones, para ofrecerte herramientas y perspectivas que impulsen tu crecimiento personal y emocional."</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </section>

  <section class="our-team">
    <div class="container">
      <div class="row">
        <div class="col-lg-6 offset-lg-3">
          <div class="section-heading">
            <h6>Nuestro equipo</h6>
            <h4>Staff de <em>Profesionales</em></h4>
          </div>
        </div>
        <?php
        // Suponiendo que ya tienes la conexión establecida en $conn
        include "conexionsm.php";
        $query = "SELECT * FROM usuarios WHERE numdoc != '1014273279' AND estado = 1 AND permiso = 3";
        $result = $conn->query($query);

        // Verificamos si hay resultados
        if ($result->num_rows > 0) {
            // Inicializamos un contador para poder asignar clases 'active' a los primeros elementos
            $contador = 0;
        ?>
        <div class="col-lg-11 offset-lg-1">
          <div class="naccs">
            <div class="tabs">
              <div class="row">
                <div class="col-lg-12">
                  <div class="menu" id="carousel-container" style="width: 590px;">
                    <?php 
                    $contador = 0;
                    while($row = $result->fetch_assoc()) { 
                      $foto = ($row['foto'] === "") ? "avatar-1.png" : $row['foto']; 
                    ?>
                      <div class="carousel-item" style="<?php echo ($contador < 4) ? '' : 'display: none;'; ?>">
                        <img src="t_zone/dist/assets/img/profile-photos/<?php echo $foto; ?>" alt="" style="width: 100%;">
                        <h4><?php echo $row['pn_usu'] . " " . $row['pa_usu']; ?></h4>
                        <span>
                          <?php
                          if($row['tip_pro'] === "2"){
                            echo "Psiquiatra";
                          }else{
                            echo "Psicologo/a Clínico";
                          }
                          ?>
                        </span>
                      </div>
                    <?php 
                      $contador++; 
                    } 
                    ?>
                  </div>
                </div>
                
                <div class="col-lg-11">
                    <button class="carousel-navigation" id="prev-button">&laquo;</button>
                    <button class="carousel-navigation" id="next-button">&raquo;</button>
                </div>
                
                <style>
                  .carousel-item {
                    text-align: center;
                    margin: 10px 0;
                  }
                  #carousel-container {
                    position: relative;
                    overflow: hidden;
                  }
                  .carousel-navigation {
                    background-color: #7843ee;
                    color: white;
                    border: none;
                    padding: 10px 15px;
                    cursor: pointer;
                    border-radius: 5px;
                  }
                  .carousel-navigation button:hover {
                    background-color: #7843ee;
                  }
                </style>
                
                <script>
                  document.addEventListener('DOMContentLoaded', function () {
                    const items = document.querySelectorAll('.carousel-item');
                    const totalItems = items.length;
                    const itemsPerPage = 4;
                    let currentIndex = 0;
                
                    // Mostrar grupo actual
                    function showItems(index) {
                      items.forEach((item, i) => {
                        if (i >= index && i < index + itemsPerPage) {
                          item.style.display = '';
                        } else {
                          item.style.display = 'none';
                        }
                      });
                    }
                
                    // Avanzar al siguiente grupo
                    function nextGroup() {
                      currentIndex += itemsPerPage;
                      if (currentIndex >= totalItems) {
                        currentIndex = 0; // Reiniciar al principio
                      }
                      showItems(currentIndex);
                    }
                
                    // Retroceder al grupo anterior
                    function prevGroup() {
                      currentIndex -= itemsPerPage;
                      if (currentIndex < 0) {
                        currentIndex = totalItems - (totalItems % itemsPerPage || itemsPerPage);
                      }
                      showItems(currentIndex);
                    }
                
                    // Configurar botones de navegación
                    document.getElementById('next-button').addEventListener('click', nextGroup);
                    document.getElementById('prev-button').addEventListener('click', prevGroup);
                
                    // Iniciar carrusel automático
                    setInterval(nextGroup, 10000);
                
                    // Mostrar los primeros elementos
                    showItems(currentIndex);
                  });
                </script>

                <div class="col-lg-12">
                  <ul class="nacc">
                    <?php 
                    // Reiniciamos el puntero de los resultados
                    $result->data_seek(0); 
                    while($row = $result->fetch_assoc()) { ?>
                      <li class="<?php echo ($contador == 0) ? 'active' : ''; ?>">
                        <div>
                          <div class="left-content">
                            <h4><?php echo $row['pn_usu']." ".$row['pa_usu']; ?></h4>
                            <p><?php echo nl2br(htmlspecialchars($row['descripcion'])); ?></p>

                            <span><a href="https://www.facebook.com/sana.mente.colombia" target="_blank">
                              <i class="fab fa-facebook" style="color: #1877F2; font-size: 24px;"></i>
                            </a></span>
                            <span><a href="https://www.tiktok.com/@sana_mente.co" target="_blank">
                              <i class="fab fa-tiktok" style="color: #000; font-size: 24px;"></i>
                            </a></span>
                            <span class="last-span"><a href="https://www.instagram.com/sana_mente.co/" target="_blank">
                              <i class="fab fa-instagram" style="color: #E4405F; font-size: 24px;"></i>
                            </a></span>
                            <div class="text-button">
                              <a href="https://wa.me/573214193875" style="display: inline-flex; align-items: center; background-color: #25D366; color: white; padding: 10px 20px; border-radius: 5px; text-decoration: none;">
                                <i class="fab fa-whatsapp" style="margin-right: 10px; font-size: 20px;"></i> Contactar
                              </a>
                            </div>
                          </div>
                          <div class="right-image">
                          <?php
                          if($row['foto'] === ""){
                            $foto = "avatar-1.png";
                          }else{
                            $foto = $row['foto'];
                          }
                          ?>
                            <img src="t_zone/dist/assets/img/profile-photos/<?php echo $foto; ?>" alt="" style="width: 400px;">
                          </div>
                        </div>
                      </li>
                    <?php } ?>
                  </ul>
                </div>
              </div>
            </div>
          </div>
        </div>
        <?php
        } else {
            echo "No se encontraron resultados.";
        }
        ?>
    </div>
  </section>

  <section class="more-info">
    <div class="container">
      <div class="row">
        <div class="col-lg-5">
          <div class="section-heading">
            <h6>Más Información</h6>
            <h4>Conoce <em>Más Sobre Nosotros</em></h4>
          </div>
          <p>Nos dedicamos a brindar apoyo integral para el desarrollo personal y emocional. Nuestro enfoque está basado en el compromiso con el bienestar y en el uso de técnicas basadas en evidencia. 
          <br><br>Queremos acompañarte en cada paso, promoviendo un espacio seguro y profesional donde puedas trabajar en tu crecimiento personal.</p>
          <ul>
            <li>- Acompañamiento personalizado en cada sesión.</li>
            <li>- Enfoque centrado en el bienestar integral.</li>
            <li>- Uso de herramientas prácticas para tu vida diaria.</li>
            <li>- Comunidad de apoyo y respeto.</li>
          </ul>
        </div>
        <div class="col-lg-6 offset-lg-1 align-self-center">
          <div class="row">
            <div class="col-6">
              <div class="count-area-content">
                <div class="count-wrapper">
                  <span class="count-prefix">+</span>
                  <div class="count-digit">25</div>
                </div>
                <div class="count-title">Profesionales</div>
              </div>
            </div>
            <div class="col-6">
              <div class="count-area-content">
                <div class="count-wrapper">
                  <span class="count-prefix">+</span>
                  <div class="count-digit">25000</div>
                </div>
                <div class="count-title">Consultas realizadas</div>
              </div>
            </div>
            <div class="col-6">
              <div class="count-area-content">
                <div class="count-wrapper">
                  <div class="count-digit">98</div>
                  <span class="count-suffix">%</span>
                </div>
                <div class="count-title">De satisfacción</div>
              </div>
            </div>
            <div class="col-6">
              <div class="count-area-content">
                <div class="count-wrapper">
                  <div class="count-digit">100</div>
                  <span class="count-suffix">%</span>
                </div>
                <div class="count-title">Personalizado</div>
              </div>
            </div>
          </div>
        </div>
        <style>
          .count-wrapper {
          display: flex;
          align-items: center;
          justify-content: center;
        }

        .count-prefix, .count-suffix {
          font-size: 1.5em; /* Ajusta el tamaño según el diseño */
          font-weight: bold;
        }

        .count-prefix {
          margin-right: 5px; /* Espacio a la derecha del + */
        }

        .count-suffix {
          margin-left: 5px; /* Espacio a la izquierda del % */
        }

        </style>
      </div>
    </div>
  </section>
  <br>

  <footer>
    <div class="footer-container">
      <!-- Sección del logo -->
      <div class="footer-logo">
        <img src="assets/images/icosn.png" alt="Logo de la empresa" />
      </div>

      <!-- Sección de contacto -->
      <div class="footer-contact">
        <h4>Contacto</h4>
        <p>Teléfono: +57 321 419 3875</p>
        <p>Email: contacto@saludmentalsanamente.com.co</p>
        <p>Ubicación: Carrera 49a # 94 - 32 Piso1, Bogotá, Colombia</p>
      </div>

      <!-- Redes sociales -->
      <div class="footer-social">
        <h4>Síguenos</h4>
        <a href="https://www.facebook.com/sana.mente.colombia" target="_blank" aria-label="Facebook">
          <i class="fab fa-facebook-f"></i>
        </a>
        <a href="https://www.tiktok.com/@sana_mente.co" target="_blank" aria-label="TikTok">
          <i class="fab fa-tiktok"></i>
        </a>
        <a href="https://www.instagram.com/sana_mente.co/" target="_blank" aria-label="Instagram">
          <i class="fab fa-instagram"></i>
        </a>
      </div>

      <!-- Links adicionales -->
      <div class="footer-links">
        <h4>Información</h4>
        <a href="#">Política de privacidad</a>
        <a href="#">Términos y condiciones</a>
        <a href="#">Protección de datos</a>
        <a href="#">Ayuda</a>
      </div>
    </div>
  </footer>


  <!-- Scripts -->
  <!-- Bootstrap core JavaScript -->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <script src="assets/js/isotope.min.js"></script>
    <script src="assets/js/owl-carousel.js"></script>
    <script src="assets/js/lightbox.js"></script>
    <script src="assets/js/tabs.js"></script>
    <script src="assets/js/video.js"></script>
    <script src="assets/js/slick-slider.js"></script>
    <script src="assets/js/custom.js"></script>
    <script>
        //according to loftblog tut
        $('.nav li:first').addClass('active');

        var showSection = function showSection(section, isAnimate) {
          var
          direction = section.replace(/#/, ''),
          reqSection = $('.section').filter('[data-section="' + direction + '"]'),
          reqSectionPos = reqSection.offset().top - 0;

          if (isAnimate) {
            $('body, html').animate({
              scrollTop: reqSectionPos },
            800);
          } else {
            $('body, html').scrollTop(reqSectionPos);
          }

        };

        var checkSection = function checkSection() {
          $('.section').each(function () {
            var
            $this = $(this),
            topEdge = $this.offset().top - 80,
            bottomEdge = topEdge + $this.height(),
            wScroll = $(window).scrollTop();
            if (topEdge < wScroll && bottomEdge > wScroll) {
              var
              currentId = $this.data('section'),
              reqLink = $('a').filter('[href*=\\#' + currentId + ']');
              reqLink.closest('li').addClass('active').
              siblings().removeClass('active');
            }
          });
        };

        $('.main-menu, .responsive-menu, .scroll-to-section').on('click', 'a', function (e) {
          e.preventDefault();
          showSection($(this).attr('href'), true);
        });

        $(window).scroll(function () {
          checkSection();
        });
    </script>
    
</body>
</html>