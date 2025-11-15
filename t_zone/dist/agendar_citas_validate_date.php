<?php
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
}
include "../../conexionsm.php";

if (isset($_POST['fecha'])) {
    $fecha = $_POST['fecha'];
    $idUsuario = $_POST['id_profesional'];

    // Consulta para verificar si existe un registro con la fecha y el status = 0
    $query = "SELECT * FROM days_presencial WHERE id_user = $idUsuario AND date = '$fecha' AND status = 0";
    $result = mysqli_query($conn, $query);

    // Si existe el registro, devolver 'presencial', si no, devolver vacío
    if (mysqli_num_rows($result) > 0) {
        echo 'presencial';
    } else {
        echo '';
    }
}
?>
