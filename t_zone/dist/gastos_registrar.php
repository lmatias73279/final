<?php
include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if (isset($_POST['tipoGasto'], $_POST['fechaGasto'], $_POST['descripcionGasto'], $_POST['valorGasto'], $_POST['medio_pago'])) {
    $tipo_gasto = $_POST['tipoGasto'];
    $fecha_gasto = $_POST['fechaGasto'];
    $descripcion_gasto = $_POST['descripcionGasto'];
    $valor_gasto = $_POST['valorGasto'];
    $medio_pago = $_POST['medio_pago'];

    // Obtener la hora actual en Colombia
    date_default_timezone_set('America/Bogota');
    $hora = date('H:i:s');

    $sqlcon = "SELECT afecta FROM idsgastos WHERE ID = $tipo_gasto";

    $sqlcon = "SELECT afecta FROM idsgastos WHERE ID = $tipo_gasto";
    $resultado = mysqli_query($conn, $sqlcon);

    if ($resultado && mysqli_num_rows($resultado) > 0) {
        $fila = mysqli_fetch_assoc($resultado);
        $afecta = $fila['afecta'];
    } else {
        $afecta = null; // O algún valor por defecto si no hay resultado
    }

    // Preparar la consulta de inserción
    $sql = "INSERT INTO gastos (date, time, id_gasto, description, value, banco, afecta) 
            VALUES ('$fecha_gasto', '$hora', '$tipo_gasto', '$descripcion_gasto', '$valor_gasto', '$medio_pago', '$afecta')";

    // Ejecutar la consulta
    if ($conn->query($sql)) {
        // Si la inserción es exitosa, encriptamos el estado 'success_create' y redirigimos
        $encryptedStatus = simpleEncrypt('success_create', '2020');
        header('Location: gastos?sta=' . urlencode($encryptedStatus));
        exit(); // Asegurarse de que no se ejecute nada más después de redirigir
    } else {
        // Si hay un error, encriptamos el estado 'error_create' y redirigimos
        $encryptedStatus = simpleEncrypt('error_create', '2020');
        header('Location: gastos?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
