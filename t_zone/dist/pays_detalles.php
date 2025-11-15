<?php
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
}
include '../../conexionsm.php';  // Conexión a la base de datos

// Verificamos si se recibe el ID
if (isset($_GET['id'])) {
    $id = $_GET['id'];

    // Consulta SQL para obtener los detalles de la base de datos
    $sql = "SELECT * FROM sessions WHERE ID = $id";
    $result = $conn->query($sql);

    if ($result && $result->num_rows > 0) {
        // Obtener los datos de la consulta
        $row = $result->fetch_assoc();

        // Retornar los datos en formato JSON
        echo json_encode($row);
    } else {
        echo json_encode(['error' => 'No se encontraron detalles.']);
    }
}
?>
