<?php
// Conexión a la base de datos
include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

// Configurar la zona horaria de Colombia
date_default_timezone_set('America/Bogota');

// Obtener fecha y hora actual en formato 'YYYY-MM-DD HH:MM:SS'
$fecha_actual = date("Y-m-d H:i:s");

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Recoger datos del formulario
$titulo = isset($_POST['alertaTitle']) ? $_POST['alertaTitle'] : '';
$mensaje = isset($_POST['alertaMessage']) ? $_POST['alertaMessage'] : '';
$empleado_id = isset($_POST['alertaEmpleado']) ? $_POST['alertaEmpleado'] : '';
$grupos = isset($_POST['alertaPublico']) ? $_POST['alertaPublico'] : [];

// Convertir array de grupos en string separado por comas
$grupos_str = !empty($grupos) ? implode(",", $grupos) : '';

// Determinar tipo de destinatario
$tipo = -1; // Valor por defecto

if (!empty($empleado_id)) {
    $tipo = 0; // Individual
} elseif (!empty($grupos)) {
    $tipo = 1; // Grupal
}

// Insertar en base de datos
$sql = "INSERT INTO alertas (tit, men, pub, ids, tip, fec) VALUES (?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ssssss", $titulo, $mensaje, $grupos_str, $empleado_id, $tipo, $fecha_actual);

if ($stmt->execute()) {
    $encryptedStatus = simpleEncrypt('alertenvext', '2020');
    header('Location: alertas?sta=' . urlencode($encryptedStatus));
    exit();
} else {
    $encryptedStatus = simpleEncrypt('erralertenvext', '2020');
    header('Location: alertas?sta=' . urlencode($encryptedStatus));
    exit();
}

// Cerrar conexiones
$stmt->close();
$conn->close();
?>
