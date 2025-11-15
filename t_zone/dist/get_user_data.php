<?php
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99){
    header("location: login");
}
include "../../conexionsm.php";

if (isset($_GET['id'])) {
    $id = $_GET['id'];
    
    // Consulta para obtener los datos del usuario
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    
    // Obtener el resultado
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();
    
    // Devolver los datos como JSON
    echo json_encode($user);
}
?>
