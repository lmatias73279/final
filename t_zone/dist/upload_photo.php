<?php
// Iniciar la sesión
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

// Verificar si el formulario ha sido enviado y si hay un archivo cargado
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['profilePhoto']) && $_FILES['profilePhoto']['error'] === UPLOAD_ERR_OK) {
    // Verificar si la sesión está iniciada y si existe 'id' en $_SESSION
    if (!isset($_SESSION['id'])) {
        echo "<script>alert('No estás autenticado.');window.location.href = 'profile';</script>";
        exit;
    }

    // Ruta de la carpeta donde se guardarán las fotos
    $targetDir = "assets/img/profile-photos/";

    // Obtener el ID de usuario y la fecha actual
    $userId = $_SESSION['id'];
    $numdoc = $_SESSION['numdoc'];
    $currentDate = date("YmdHis");

    // Obtener la extensión del archivo
    $imageFileType = strtolower(pathinfo($_FILES['profilePhoto']['name'], PATHINFO_EXTENSION));

    // Crear el nombre del archivo basado en el ID de usuario y la fecha
    $targetFile = $targetDir . $userId . "_" . $numdoc . "_" . $currentDate . "." . $imageFileType;
    $targetFileSQL = $userId . "_" . $numdoc . "_" . $currentDate . "." . $imageFileType;

    // Validar MIME type
    $mimeType = mime_content_type($_FILES['profilePhoto']['tmp_name']);
    if (!in_array($mimeType, ['image/jpeg', 'image/png'])) {
        echo "<script>alert('El archivo no es un tipo de imagen válido (JPEG o PNG).');window.location.href = 'profile';</script>";
        exit;
    }

    // Verificar el tamaño del archivo (por ejemplo, máximo 5MB)
    if ($_FILES['profilePhoto']['size'] > 5000000) {
        echo "<script>alert('Lo siento, el archivo es demasiado grande.');window.location.href = 'profile';</script>";
        exit;
    }

    // Procesar la imagen según el tipo
    $image = null;
    switch ($imageFileType) {
        case 'jpg':
        case 'jpeg':
            $image = @imagecreatefromjpeg($_FILES['profilePhoto']['tmp_name']);
            break;
        case 'png':
            $image = @imagecreatefrompng($_FILES['profilePhoto']['tmp_name']);
            break;
        default:
            echo "<script>alert('Tipo de imagen no soportado.')</script>";
            exit;
    }

    // Verificar si la imagen se cargó correctamente
    if (!$image) {
        echo "<script>alert('No se pudo procesar la imagen. Asegúrate de que el archivo sea una imagen válida.');window.location.href = 'profile';</script>";
        exit;
    }

    // Obtener las dimensiones de la imagen
    $width = imagesx($image);
    $height = imagesy($image);

    // Calcular el lado más pequeño para hacer la imagen cuadrada
    $size = min($width, $height);

    // Crear una nueva imagen cuadrada
    $squareImage = imagecreatetruecolor($size, $size);

    // Recortar la imagen desde el centro para que quede cuadrada
    imagecopy($squareImage, $image, 0, 0, 0, 0, $size, $size);

    // Guardar la imagen recortada
    switch ($imageFileType) {
        case 'jpg':
        case 'jpeg':
            imagejpeg($squareImage, $targetFile);
            break;
        case 'png':
            imagepng($squareImage, $targetFile);
            break;
    }

    // Liberar memoria
    imagedestroy($image);
    imagedestroy($squareImage);

    // Actualizar el campo 'foto' en la tabla 'usuarios'
    $sql = "UPDATE usuarios SET foto = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("si", $targetFileSQL, $userId);

    // Ejecutar la consulta y verificar si fue exitosa
    if ($stmt->execute()) {
        // Redirigir a profile.php después de la actualización
        header("Location: profile");
        exit;
    } else {
        echo "<script>alert('Error al actualizar la base de datos: " . $stmt->error . "');window.location.href = 'profile';</script>";
    }

    // Cerrar la conexión
    $stmt->close();
    $conn->close();
} else {
    echo "<script>alert('No se ha cargado ningún archivo o hubo un error en el proceso.');window.location.href = 'profile';</script>";
}
?>
