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
        .blog-section {
            padding: 40px 0;
            background-color: #f9f9f9;
        }

        .blog-card {
            margin-bottom: 30px;
            border: none;
            transition: transform 0.3s, box-shadow 0.3s;
        }

        .blog-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
        }

        .blog-card img {
            max-height: 200px;
            object-fit: cover;
        }

        .blog-card .card-title {
            color: #333;
            font-weight: 600;
        }

        .blog-card .card-text {
            color: #666;
        }

        .read-more {
            color: #007bff;
            font-weight: 500;
        }

        .read-more:hover {
            text-decoration: underline;
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
include "conexionsm.php";
?>

<!-- Blog Section -->
<section class="blog-section">
    <div class="container">
        <div class="text-center mb-5">
            <div class="col-lg-12">
                <div class="section-heading">
                    <h4>Bienvenid@ al blog de <em>sana mente</em></h4>
                    <h6>Descubre artículos interesantes sobre salud mental y bienestar.</h6>
                </div>
            </div>
        </div>
        <!-- Inicia la fila -->
        <div class="row">
            <?php
            $query = "SELECT ID, date, title, resume, img FROM blog";
            $result = mysqli_query($conn, $query);

            if ($result) {
                while ($row = mysqli_fetch_assoc($result)) {
                    ?>
                    <div class="col-md-4 mb-4"> <!-- Asegúrate de que las columnas tengan márgenes -->
                        <div class="card blog-card">
                            <img src="t_zone/dist/assets/docs/blog/<?php echo htmlspecialchars($row['img']); ?>" class="card-img-top" alt="Imagen del blog">
                            <div class="card-body">
                                <h5 class="card-title"><?php echo htmlspecialchars($row['title']); ?></h5>
                                <p class="card-text"><?php echo nl2br(htmlspecialchars($row['resume'])); ?></p>
                                <br>
                                <h6><?php echo urlencode($row['date']); ?></h6>
                                <br>
                                <a href="post?id=<?php echo urlencode($row['ID']); ?>" class="read-more">Leer más &raquo;</a>
                            </div>
                        </div>
                    </div>
                    <?php
                }
            } else {
                echo "Error en la consulta: " . mysqli_error($conn);
            }
            ?>
        </div> <!-- Termina la fila -->
    </div>
</section>


    <!-- Footer -->

  <footer>
  <div class="footer-container">
    <!-- Sección del logo -->
    <div class="footer-logo">
      <img src="assets/images/icosn.png" alt="Logo de la empresa" />
    </div>

    <!-- Sección de contacto -->
    <div class="footer-contact">
      <h4>Contacto</h4>
      <p style="color: white;">Teléfono: +57 321 419 3875</p>
      <p style="color: white;">Email: contacto@saludmentalsanamente.com.co</p>
      <p style="color: white;">Ubicación: Carrera 49a # 94 - 32 Piso1, Bogotá, Colombia</p>
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