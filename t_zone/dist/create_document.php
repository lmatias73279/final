<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
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
    if (isset($_POST['name'], $_POST['descripcion'], $_FILES['img'], $_FILES['doc'])) {
        // Variables del formulario
        $name = $_POST['name'];
        $descripcion = $_POST['descripcion'];
        $log_cabios = $_SESSION['pn_usu']." ".$_SESSION['pa_usu']." REALIZÓ LA CREACIÓN DEL DOCUMENTO.";
        $status = 1;

        // Archivos subidos
        $img = $_FILES['img'];
        $doc = $_FILES['doc'];

        // Validar el archivo de imagen (solo imágenes)
        $allowedImgTypes = ['image/jpeg', 'image/png', 'image/gif'];
        if (!in_array($img['type'], $allowedImgTypes)) {
            die("Error: Solo se permiten imágenes JPEG, PNG o GIF.");
        }

        // Validar el archivo PDF (solo PDFs)
        if ($doc['type'] !== 'application/pdf') {
            die("Error: Solo se permiten archivos PDF.");
        }

        // Directorio de destino
        $uploadDir = 'assets/docs/library/';

        // Crear nombres únicos para los archivos
        $imgExtension = pathinfo($img['name'], PATHINFO_EXTENSION);
        $docExtension = pathinfo($doc['name'], PATHINFO_EXTENSION);

        // Generar nombres únicos para la imagen y el documento
        $imgUniqueName = uniqid('img_', true) . '.' . $imgExtension;
        $docUniqueName = uniqid('doc_', true) . '.' . $docExtension;

        // Guardar imagen
        $imgPath = $uploadDir . $imgUniqueName;
        if (!move_uploaded_file($img['tmp_name'], $imgPath)) {
            die("Error al guardar la imagen.");
        }

        // Guardar documento
        $docPath = $uploadDir . $docUniqueName;
        if (!move_uploaded_file($doc['tmp_name'], $docPath)) {
            die("Error al guardar el documento.");
        }

        // Insertar en la base de datos
        $sql = "INSERT INTO library (title, img, doc, description, status, log_cambios) 
                VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ssssis', $name, $imgUniqueName, $docUniqueName, $descripcion, $status, $log_cabios);

        if ($stmt->execute()) {
            // Redirigir con un mensaje de éxito encriptado
            $encryptedStatus = simpleEncrypt('success_create', '2020');
            header('Location: library_edit.php?sta=' . urlencode($encryptedStatus));
            exit();
        } else {
            // Redirigir con un mensaje de error encriptado
            $encryptedStatus = simpleEncrypt('error_create', '2020');
            header('Location: library_edit.php?sta=' . urlencode($encryptedStatus));
            exit();
        }
    }
}
?>
