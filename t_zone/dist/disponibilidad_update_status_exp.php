<?php
// Conectar a la base de datos
include "../../conexionsm.php";
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99 && $_SESSION['numdoc'] !== '1000693019'){
    header("location: login");
}

// Verificar si se han enviado los datos
if (isset($_POST['id']) && isset($_POST['status'])) {
    $id = $_POST['id'];
    $status = $_POST['status'];

    // Actualizar el estado a 1 (eliminado) para la fila con el id correspondiente
    $query = "UPDATE days_exception SET status = ? WHERE id = ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ii", $status, $id); // El parámetro "ii" indica que ambos son enteros
    if ($stmt->execute()) {
        echo 'success'; // Enviar una respuesta exitosa
    } else {
        echo 'error'; // En caso de error
    }

    $stmt->close();
} else {
    echo 'error';
}

$conn->close();
?>
