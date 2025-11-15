<?php
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 3 && $_SESSION['permiso'] !== 99){
    header("location: login");
}
include "../../conexionsm.php";

// Verificar que el parámetro cod_pais esté presente en la solicitud GET
if (isset($_GET['cod_pais'])) {
    $cod_pais = $_GET['cod_pais'];
    
    // Preparar la consulta SQL para obtener el nombre del país
    $sql = "SELECT pais FROM paises WHERE cod_pais = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('s', $cod_pais);  // Suponiendo que cod_pais es un string
    
    if ($stmt->execute()) {
        // Obtener el resultado
        $stmt->bind_result($pais);
        $stmt->fetch();
        
        // Devolver el nombre del país como respuesta
        echo $pais;
    } else {
        // Si ocurre un error en la consulta
        echo 'Error al obtener el país';
    }
    
    // Cerrar la declaración
    $stmt->close();
} else {
    echo 'Código de país no proporcionado';
}
?>
