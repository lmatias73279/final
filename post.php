<?php
// Incluir el archivo de conexión a la base de datos
include "conexionsm.php";

// Obtener el ID del blog desde la URL
$blog_id = $_GET['id']; // Suponiendo que el ID del blog se pasa por la URL

// Consulta para obtener los detalles del blog, junto con la información del creador
$query = "SELECT b.title, b.p, b.img, b.date, b.date_edit, b.propietary, u.pn_usu, u.pa_usu, u.foto 
          FROM blog b 
          JOIN usuarios u ON b.propietary = u.numdoc 
          WHERE b.id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param('i', $blog_id); // 'i' es para integer
$stmt->execute();
$result = $stmt->get_result();

// Comprobar si se obtuvo el resultado
if ($result && $row = $result->fetch_assoc()) {
    $title = $row['title'];
    $content = $row['p'];
    $img = $row['img'];
    $created_date = $row['date'];
    $updated_date = $row['date_edit'];
    
    // Concatenar el primer nombre y primer apellido
    $author_name = $row['pn_usu'] . ' ' . $row['pa_usu'];  // Obtener nombre completo
    
    $author_photo = $row['foto'];
} else {
    echo "Blog no encontrado.";
    exit;
}
$meses = [
    "Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio",
    "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"
];

// Formatear la fecha manualmente
$fecha_creacion = new DateTime($created_date);
$mes_creacion = $meses[$fecha_creacion->format('n') - 1];  // Obtiene el mes en español
$fecha_creacion_formateada = $fecha_creacion->format('d') . ' de ' . $mes_creacion . ' de ' . $fecha_creacion->format('Y');

// Repetir el mismo proceso para la fecha de última actualización
$fecha_actualizacion = new DateTime($updated_date);
$mes_actualizacion = $meses[$fecha_actualizacion->format('n') - 1];  // Obtiene el mes en español
$fecha_actualizacion_formateada = $fecha_actualizacion->format('d') . ' de ' . $mes_actualizacion . ' de ' . $fecha_actualizacion->format('Y');
?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
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
        /* Estilos específicos para la página del blog */
        .blog-header {
            background-color: #f9f9f9;
            padding: 30px 0;
            text-align: center;
        }

        .blog-header h1 {
            color: #333;
            font-size: 2.5rem;
            font-weight: 700;
        }

        .blog-header p {
            color: #666;
            font-size: 1.2rem;
        }

        .blog-content {
            padding: 40px 15px;
            line-height: 1.8;
        }

        .blog-content img {
            max-width: 100%;
            margin: 20px 0;
            border-radius: 8px;
        }

        .author-photo {
            border-radius: 50%;
            width: 50px;
            height: 50px;
            object-fit: cover;
            margin-right: 15px;
        }

        .author-info {
            display: flex;
            align-items: center;
            margin-top: 30px;
        }

        .author-info .name {
            font-size: 1.2rem;
            font-weight: bold;
        }

        .blog-footer {
            margin-top: 40px;
            text-align: center;
        }

        .blog-footer a {
            color: #007bff;
            font-weight: 500;
            text-decoration: none;
        }

        .blog-footer a:hover {
            text-decoration: underline;
        }

        footer {
            background-color: #282828;
            color: #ffffff;
            padding: 20px 40px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-wrap: wrap;
        }

        /* Estilo de la sección de pie de página */
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
    </style>
</head>

<body>
    <!-- Header -->
    <?php include "header.php"; ?>
    <br><br><br><br><br><br>


    <!-- Blog Header -->
    <div class="blog-header">
        <div class="container">
            <h1><?php echo htmlspecialchars($title); ?></h1>
            <p>Publicado el <?php echo $fecha_creacion_formateada; ?> | Última actualización: <?php echo $fecha_actualizacion_formateada; ?></p>
        </div>
    </div>


    <!-- Blog Content -->
    <div class="container blog-content">
        <img src="t_zone/dist/assets/docs/blog/<?php echo htmlspecialchars($img); ?>" alt="Imagen del blog" style="object-fit: cover; display: block; margin: 0 auto; width: 100%; height: 200px; border-radius: 10px;">
        <br>
        <p><?php echo nl2br(htmlspecialchars($content)); ?></p>
    </div>


    <!-- Autor Info -->
    <div class="author-info-container" style="display: flex; justify-content: flex-end;">
        <div class="author-info">
            <img src="t_zone/dist/assets/img/profile-photos/<?php echo htmlspecialchars($author_photo); ?>" alt="Foto del autor" class="author-photo">
            <div>
                <div class="name"><?php echo htmlspecialchars($author_name); ?></div>
                <div class="date-info">
                    <small>&copy; <?php echo date('Y'); ?> | Derechos reservados</small>
                </div>
            </div>
        </div>
    </div>


    <!-- Blog Footer -->
    <div class="blog-footer">
        <div class="container">
            <a href="blog">&laquo; Volver al Blog</a>
        </div>
    </div>
<br><br>
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
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
</body>

</html>
