<?php
include "../../conexionsm.php"; // Conexión a la base de datos

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Directorio donde se guardarán las imágenes
    $target_dir = "assets/img/popups/";

    function simpleEncrypt($text, $key) {
        $output = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
        }
        return base64_encode($output); // Convertir a base64 para URL seguro
    }

    // Obtener información del archivo
    $file_name = basename($_FILES["popupImage"]["name"]);
    $target_file = $target_dir . $file_name;
    $imageFileType = strtolower(pathinfo($target_file, PATHINFO_EXTENSION));

    // Validar si es una imagen
    $check = getimagesize($_FILES["popupImage"]["tmp_name"]);
    if ($check === false) {
        $encryptedStatus = simpleEncrypt('imgnovalid', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    }

    // Extensiones permitidas
    $allowed_extensions = ["jpg", "jpeg", "png", "gif"];
    if (!in_array($imageFileType, $allowed_extensions)) {
        $encryptedStatus = simpleEncrypt('soloseper', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    }

    // Mover la imagen al directorio de destino
    if (!move_uploaded_file($_FILES["popupImage"]["tmp_name"], $target_file)) {
        $encryptedStatus = simpleEncrypt('erralsubirimg', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    }

    // Obtener la lista de públicos seleccionados
    if (!empty($_POST["popupPublico"])) {
        $publico = implode(",", $_POST["popupPublico"]); // Convertir el array en una cadena separada por comas
    } else {
        $encryptedStatus = simpleEncrypt('almenosunpub', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    }

    // Ruta relativa de la imagen para la base de datos
    $ruta_img = "assets/img/popups/" . $file_name;

    // ❗ Actualizar todos los registros de la comuna en `est` a `1` antes de insertar el nuevo Pop Up
    $update_sql = "UPDATE popups SET est = 1";
    if (!$conn->query($update_sql)) {
        $encryptedStatus = simpleEncrypt('erralactregexist', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    }

    // Insertar en la base de datos
    $stmt = $conn->prepare("INSERT INTO popups (img, pub) VALUES (?, ?)");
    $stmt->bind_param("ss", $ruta_img, $publico);

    if ($stmt->execute()) {
        $encryptedStatus = simpleEncrypt('okok', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    } else {
        $encryptedStatus = simpleEncrypt('erralguarbd', '2020');
        header('Location: popup?sta=' . urlencode($encryptedStatus));
        exit();
    }

    $stmt->close();
    $conn->close();
}
?>
