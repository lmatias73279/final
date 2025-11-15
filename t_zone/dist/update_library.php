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
    if (isset($_POST['id'], $_POST['name'], $_POST['descripcion'], $_POST['estado'])) {
        // Variables del formulario
        $id = $_POST['id'];
        $name = $_POST['name'];
        $descripcion = $_POST['descripcion'];
        $status = $_POST['estado'];
        date_default_timezone_set('America/Bogota');
        $date = date('d-m-Y H:i:s');

        // Consultar los valores actuales del registro en la base de datos
        $sql = "SELECT title, description, status, img, doc, log_cambios FROM library WHERE id = ?";
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

        // Comparar y actualizar el campo 'title' (name)
        if ($name !== $row['title']) {
            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ EL TITULO DE '{$row['title']}' A '$name'.\n";
            $updateFields[] = "title = ?";
            $params[] = $name;
        }

        // Comparar y actualizar el campo 'description'
        if ($descripcion !== $row['description']) {
            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ LA DESCRIPCIÓN DE '{$row['description']}' A '$descripcion'.\n";
            $updateFields[] = "description = ?";
            $params[] = $descripcion;
        }

        // Comparar y actualizar el campo 'status'
        if ((int)$status !== (int)$row['status']) {
            // Convertir el valor numérico a las palabras correspondientes
            $oldStatus = (int)$row['status'] == 1 ? 'ACTIVO' : 'INACTIVO';
            $newStatus = (int)$status == 1 ? 'ACTIVO' : 'INACTIVO';
            
            // Agregar al log
            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ EL ESTADO DE '$oldStatus' A '$newStatus'.\n";
            
            // Agregar a los campos a actualizar
            $updateFields[] = "status = ?";
            $params[] = $status;
        }

        

        // Archivos subidos (si existen)
        $img = isset($_FILES['img']) ? $_FILES['img'] : null;
        $doc = isset($_FILES['doc']) ? $_FILES['doc'] : null;

        // Si se subió una nueva imagen, validarla y procesarla
        if ($img && $img['error'] === UPLOAD_ERR_OK) {
            $allowedImgTypes = ['image/jpeg', 'image/png', 'image/gif'];
            if (!in_array($img['type'], $allowedImgTypes)) {
                die("Error: Solo se permiten imágenes JPEG, PNG o GIF.");
            }

            // Validar y guardar la nueva imagen
            $imgExtension = pathinfo($img['name'], PATHINFO_EXTENSION);
            $imgUniqueName = uniqid('img_', true) . '.' . $imgExtension;
            $imgPath = 'assets/docs/library/' . $imgUniqueName;

            if (!move_uploaded_file($img['tmp_name'], $imgPath)) {
                die("Error al guardar la imagen.");
            }

            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ LA IMAGEN.\n";  // Añadir salto de línea
            $updateFields[] = "img = ?";
            $params[] = $imgUniqueName;
        }

        // Si se subió un nuevo archivo PDF, validarlo y procesarlo
        if ($doc && $doc['error'] === UPLOAD_ERR_OK) {
            if ($doc['type'] !== 'application/pdf') {
                die("Error: Solo se permiten archivos PDF.");
            }

            // Validar y guardar el nuevo documento
            $docExtension = pathinfo($doc['name'], PATHINFO_EXTENSION);
            $docUniqueName = uniqid('doc_', true) . '.' . $docExtension;
            $docPath = 'assets/docs/library/' . $docUniqueName;

            if (!move_uploaded_file($doc['tmp_name'], $docPath)) {
                die("Error al guardar el documento.");
            }

            $logChanges .= "EL ".$date." EL USUARIO ".$_SESSION['pn_usu']." ".$_SESSION['pa_usu']." CAMBIÓ EL DOCUMENTO.\n";  // Añadir salto de línea
            $updateFields[] = "doc = ?";
            $params[] = $docUniqueName;
        }


        // Si hubo cambios, añadir al log_cambios
        if (!empty($logChanges)) {
            // Prependizar el nuevo log al principio, y agregar salto de línea
            $log_cabios = $logChanges . $row['log_cambios'];  // Agregar el nuevo log al inicio
        }
        // Si hubo cambios en algún campo, actualizar el log
        $params[] = $log_cabios;  // El log actualizado
        $params[] = $id;          // El id del registro a actualizar

        // Completar la actualización en la base de datos
        if (!empty($updateFields)) {
            // Agregar el campo log_cambios a los campos a actualizar
            $updateFields[] = "log_cambios = ?";

            // Crear la consulta de actualización
            $sql = "UPDATE library SET " . implode(", ", $updateFields) . " WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(str_repeat('s', count($params)-1) . 'i', ...$params);

            if ($stmt->execute()) {
                // Redirigir con un mensaje de éxito encriptado
                $encryptedStatus = simpleEncrypt('success_update', '2020');
                header('Location: library_edit.php?sta=' . urlencode($encryptedStatus));
                exit();
            } else {
                // Redirigir con un mensaje de error encriptado
                $encryptedStatus = simpleEncrypt('error_update', '2020');
                header('Location: library_edit.php?sta=' . urlencode($encryptedStatus));
                exit();
            }
        } else {
            // Si no hubo cambios, no hacer nada
            $encryptedStatus = simpleEncrypt('no_changes', '2020');
            header('Location: library_edit.php?sta=' . urlencode($encryptedStatus));
            exit();
        }
    }
}
?>
