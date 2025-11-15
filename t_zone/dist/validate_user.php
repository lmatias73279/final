<?php
// Incluir la conexión a la base de datos
include "../../conexionsm.php";
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
}

// Validar si se recibieron los parámetros
if (isset($_POST['numdoc']) && isset($_POST['tipdoc'])) {
    $numdoc = $_POST['numdoc'];
    $tipdoc = $_POST['tipdoc'];

    // Preparar y ejecutar la consulta
    $stmt = $conn->prepare("SELECT COUNT(*) AS total FROM usuarios WHERE numdoc = ? AND tipdoc = ?");
    $stmt->bind_param("ss", $numdoc, $tipdoc);
    $stmt->execute();
    $result = $stmt->get_result();
    $row = $result->fetch_assoc();

    // Retornar respuesta en formato JSON
    if ($row['total'] > 0) {
        echo json_encode(['exists' => true]);
    } else {
        echo json_encode(['exists' => false]);
    }

    // Cerrar conexiones
    $stmt->close();
    $conn->close();
}
?>
