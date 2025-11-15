<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99){
    header("location: login");
}

include "../../conexionsm.php";


function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica si se ha subido un archivo y si todos los campos están presentes
    if (isset($_POST['title'], $_POST['descripcion'], $_POST['resume'], $_FILES['img'])) {
        // Variables del formulario
        $title = $_POST['title'];
        $resume = $_POST['resume'];
        $descripcion = $_POST['descripcion'];
        $log_cabios = $_SESSION['pn_usu']." ".$_SESSION['pa_usu']." REALIZÓ LA CREACIÓN DEL BLOG.";
        $status = 1;
        date_default_timezone_set('America/Bogota'); // Configurar la zona horaria de Colombia
        $date = date('Y-m-d H:i:s'); // Formato de fecha y hora
        $propietary = $_SESSION['numdoc'];

        // Archivos subidos
        $img = $_FILES['img'];

        // Validar el archivo de imagen (solo imágenes)
        $allowedImgTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($img['type'], $allowedImgTypes)) {
            die("Error: Solo se permiten imágenes JPEG, PNG o GIF.");
        }

        // Directorio de destino
        $uploadDir = 'assets/docs/blog/';

        // Crear nombres únicos para los archivos
        $imgExtension = pathinfo($img['name'], PATHINFO_EXTENSION);

        // Generar nombres únicos para la imagen y el documento
        $imgUniqueName = uniqid('img_', true) . '.' . $imgExtension;

        // Guardar imagen
        $imgPath = $uploadDir . $imgUniqueName;
        if (!move_uploaded_file($img['tmp_name'], $imgPath)) {
            die("Error al guardar la imagen.");
        }

        // Insertar en la base de datos
        $sql = "INSERT INTO blog (title, date, date_edit, p, img, resume, log_changes, propietary, status) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssssssi', $title, $date, $date, $descripcion, $imgUniqueName, $resume, $log_cabios, $propietary, $status);


        if ($stmt->execute()) {
            // Redirigir con un mensaje de éxito encriptado
            $encryptedStatus = simpleEncrypt('success_create', '2020');
            header('Location: blog_edit.php?sta=' . urlencode($encryptedStatus));
            exit();
        } else {
            // Redirigir con un mensaje de error encriptado
            $encryptedStatus = simpleEncrypt('error_create', '2020');
            header('Location: blog_edit.php?sta=' . urlencode($encryptedStatus));
            exit();
        }
    }
}
?>
