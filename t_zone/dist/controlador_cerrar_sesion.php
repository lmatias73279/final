<?php
session_start();

// Verificar si hay una sesión activa
if (!empty($_SESSION["id"])) {
    // Destruir la sesión
    session_destroy();
}

// Redireccionar al usuario a la página de inicio de sesión
header("location: login");
exit;
?>