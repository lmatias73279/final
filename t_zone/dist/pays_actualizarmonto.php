<?php

session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
    header("location: login");
}
include "../../conexionsm.php";

$id = $_POST['id'];
$ingreso_rt = $_POST['ingreso_rt'];

// Obtener comisión del usuario sin seguridad (NO RECOMENDADO)
$result = $conn->query("SELECT valor FROM sessions WHERE id = $id LIMIT 1");
$row = $result->fetch_assoc();
$valor_por_registro = $row['valor'];

// Valores iniciales para las variables calculadas
$ingresoRT = $ingreso_rt;
$subtotal = $valor_por_registro - $ingreso_rt;
$ingresoPROPIO = $subtotal / 1.19;
$iva = round($ingresoPROPIO * 19 / 100 , 2);
$autorenta = round($ingresoPROPIO * 11 / 1000, 2);
$margenNeto = round($ingresoPROPIO / $valor_por_registro * 100, 2);
$ica = round($ingresoPROPIO * 966 / 100000, 2);
$baserenta = round($ingresoPROPIO - $ica, 2);
$renta = round($baserenta * 35 / 100, 2);
$utilidadBruta = $ingresoPROPIO - $ica - $renta;
$margenNetoBruto = round($utilidadBruta / $valor_por_registro * 100, 2);

$sqlumc = "UPDATE sessions 
           SET ingresoRT = ?, ingresoPROPIO = ?, iva = ?, autorenta = ?, margenNeto = ?, 
               ica = ?, renta = ?, utilidadBruta = ?, margenNetoBruto = ? 
           WHERE ID = ?";

$stmt = $conn->prepare($sqlumc);
$stmt->bind_param("dddddddddi", $ingresoRT, $ingresoPROPIO, $iva, $autorenta, $margenNeto, 
                                 $ica, $renta, $utilidadBruta, $margenNetoBruto, $id);

if ($stmt->execute()) {
    
    function simpleEncrypt($text, $key) {
        $output = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
        }
        return base64_encode($output); // Convertir a base64 para URL seguro
    }
    
    $encryptedStatus = simpleEncrypt('updatecomi', '2020');
    header('Location: pays?sta=' . urlencode($encryptedStatus));
    exit();

} else {
    echo "Error: " . $stmt->error;
}

$stmt->close();


?>