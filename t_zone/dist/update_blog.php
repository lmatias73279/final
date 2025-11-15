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

// Función para encriptar
function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verifica si se ha enviado un ID y los campos de texto
    if (isset($_POST['id'], $_POST['title'], $_POST['descripcion'], $_POST['resume'], $_POST['estado'])) {
        // Variables del formulario
        $id = $_POST['id'];
        $title = $_POST['title'];
        $p = $_POST['descripcion'];
        $resume = $_POST['resume'];
        $status = $_POST['estado'];
        date_default_timezone_set('America/Bogota');
        $date = date('d-m-Y H:i:s');

        // Consultar los valores actuales del registro en la base de datos
        $sql = "SELECT title, p, resume, status, img, log_changes FROM blog WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('i', $id);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();

        // Variables para el log
        $logChanges = '';

        // Preparar los campos para la base de datos
        $updateFields = [];
        $params = [];

        // Comparar y actualizar el campo 'title'
        if ($title !== $row['title']) {
            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ EL TÍTULO DE '{$row['title']}' A '$title'.\n";
            $updateFields[] = "title = ?";
            $params[] = $title;
        }

        // Comparar y actualizar el campo 'description'
        if ($p !== $row['p']) {
            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ LA DESCRIPCIÓN DE '{$row['p']}' A '$p'.\n";
            $updateFields[] = "p = ?";
            $params[] = $p;
        }

        // Comparar y actualizar el campo 'resume'
        if ($resume !== $row['resume']) {
            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ EL RESUMEN DE '{$row['resume']}' A '$resume'.\n";
            $updateFields[] = "resume = ?";
            $params[] = $resume;
        }

        // Comparar y actualizar el campo 'status'
        if ((int)$status !== (int)$row['status']) {
            $oldStatus = (int)$row['status'] == 1 ? 'ACTIVO' : 'INACTIVO';
            $newStatus = (int)$status == 1 ? 'ACTIVO' : 'INACTIVO';
            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ EL ESTADO DE '$oldStatus' A '$newStatus'.\n";
            $updateFields[] = "status = ?";
            $params[] = $status;
        }

        // Archivos subidos (si existen)
        $img = isset($_FILES['img']) ? $_FILES['img'] : null;

        // Si se subió una nueva imagen, validarla y procesarla
        if ($img && $img['error'] === UPLOAD_ERR_OK) {
            $allowedImgTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($img['type'], $allowedImgTypes)) {
                die("Error: Solo se permiten imágenes JPEG, PNG o GIF.");
            }

            // Validar y guardar la nueva imagen
            $imgExtension = pathinfo($img['name'], PATHINFO_EXTENSION);
            $imgUniqueName = uniqid('img_', true) . '.' . $imgExtension;
            $imgPath = 'assets/docs/blog/' . $imgUniqueName;

            if (!move_uploaded_file($img['tmp_name'], $imgPath)) {
                die("Error al guardar la imagen.");
            }

            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ LA IMAGEN.\n";  // Añadir salto de línea
            $updateFields[] = "img = ?";
            $params[] = $imgUniqueName;
        }

        // Si hubo cambios, añadir al log_cambios
        if (!empty($logChanges)) {
            $log_cabios = $logChanges . $row['log_changes'];  // Agregar el nuevo log al inicio
        }
        // Si hubo cambios en algún campo, actualizar el log
        $params[] = $log_cabios;  // El log actualizado
        $params[] = $id;          // El id del registro a actualizar

        // Completar la actualización en la base de datos
        if (!empty($updateFields)) {
            // Agregar el campo log_changes a los campos a actualizar
            $updateFields[] = "log_changes = ?";

            // Crear la consulta de actualización
            $sql = "UPDATE blog SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('s', count($params)-1) . 'i', ...$params);

            if ($stmt->execute()) {
                // Redirigir con un mensaje de éxito encriptado
                $encryptedStatus = simpleEncrypt('success_update', '2020');
                header('Location: blog_edit.php?sta=' . urlencode($encryptedStatus));
                exit();
            } else {
                // Redirigir con un mensaje de error encriptado
                $encryptedStatus = simpleEncrypt('error_update', '2020');
                header('Location: blog_edit.php?sta=' . urlencode($encryptedStatus));
                exit();
            }
        } else {
            // Si no hubo cambios, no hacer nada
            $encryptedStatus = simpleEncrypt('no_changes', '2020');
            header('Location: blog_edit.php?sta=' . urlencode($encryptedStatus));
            exit();
        }
    }
}
?>
