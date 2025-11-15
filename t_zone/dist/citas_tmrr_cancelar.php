<?php
include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

$id = intval($_POST['id_cita_cancelar']); // Sanitizamos el id por seguridad
$causal = $_POST['causal'];
$factura = $_POST['juntificacion'];
$observaciones = $_POST['observacancel'];


$sql = "SELECT * FROM sessions WHERE ID = $id";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($factura == 1) {

    $subtotal = $row['subtotal'];
    $ingresoRTval = $row['ingresoRT'];
    $valor_por_registro = $row['valor'];
    $comision = ($subtotal != 0) ? ($ingresoRTval * 100) / $subtotal : 0;

    // Calcular valores
    $ingresoRT = intval($subtotal * $comision / 100);
    $ingresoRT = $ingresoRT / 2;
    $ingresoPROPIO = $subtotal - $ingresoRT;
    $iva = round($ingresoPROPIO * 19 / 100, 2);
    $autorenta = round($ingresoPROPIO * 11 / 1000, 2);
    $margenNeto = ($valor_por_registro != 0) ? round($ingresoPROPIO / $valor_por_registro * 100, 2) : 0;
    $ica = round($ingresoPROPIO * 966 / 100000, 2);
    $baserenta = round($ingresoPROPIO - $ica, 2);
    $renta = round($baserenta * 35 / 100, 2);
    $utilidadBruta = $ingresoPROPIO - $ica - $renta;
    $margenNetoBruto = ($valor_por_registro != 0) ? round($utilidadBruta / $valor_por_registro * 100, 2) : 0;

    // Actualizar valpsi siempre
    $sql = "UPDATE sessions SET valpsi = 2, estado = 6, consits = 2, ingresoRT = ?, ingresoPROPIO = ?, iva = ?, autorenta = ?, margenNeto = ?, ica = ?, renta = ?, utilidadBruta = ?, margenNetoBruto = ?, caucan = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('dddddddddii', $ingresoRT, $ingresoPROPIO, $iva, $autorenta, $margenNeto, $ica, $renta, $utilidadBruta, $margenNetoBruto, $causal, $id);

    if ($stmt->execute()) {
        $encryptedStatus = simpleEncrypt('cancelexitosa', '2020');
        // Obtener los parámetros actuales de la URL
        $params = $_GET;
        
        // Reemplazar o agregar el parámetro 'sta'
        $params['sta'] = $encryptedStatus;
        
        // Construir la nueva query string
        $queryString = http_build_query($params);
        
        // Redirigir
        header('Location: citas_tmrr?' . $queryString);
        exit; 
    } else {
        $encryptedStatus = simpleEncrypt('error_insertcancel', '2020');
        header('Location: citas_tmrr?sta=' . urlencode($encryptedStatus));
        exit();
    } 

    $stmt->close();
}else{

    $order = $row['order'];
    $order = str_replace('ORDER_', 'CANCEL_', $order);
    $tipo = $row['tipo'];
    $valor = $row['valor'];
    $tipoValor = $row['tipoValor'];
    $estado = 3;
    $userID = $row['userID'];
    $use_date = $row['use_date'];
    $site = $row['site'];
    $fecha = "0000-00-00";
    $titulo = 1;
    $medio_pago = $row['medio_pago'];
    $archivo = $row['archivo'];
    $psi = $row['psi'];
    $hora = "00:00:00";
    $link_ingreso = "";
    $subtotal = $row['subtotal'];
    $ingresoRT = $row['ingresoRT'];
    $ingresoPROPIO = $row['ingresoPROPIO'];
    $iva = $row['iva'];
    $autorenta = $row['autorenta'];
    $margenNeto = $row['margenNeto'];
    $ica = $row['ica'];
    $renta = $row['renta'];
    $utilidadBruta = $row['utilidadBruta'];
    $margenNetoBruto = $row['margenNetoBruto'];
    $valpsi = 0;
    $fecpacom = "0000-00-00 00:00:00";
    $consits = 0;
    $observa_pago = $row['observa_pago'];
    $ref = $row['ref'];
    $fecpareal = $row['fecpareal'];
    $fact = $row['fact'];

    $sql1 = "UPDATE sessions SET valor = 0, subtotal = 0, valpsi = 2, estado = 5, consits = 2, ingresoRT = 0, ingresoPROPIO = 0, iva = 0, autorenta = 0, margenNeto = 0, ica = 0, renta = 0, utilidadBruta = 0, margenNetoBruto = 0, caucan = ? WHERE id = ?";
    $stmt = $conn->prepare($sql1);
    $stmt->bind_param('ii', $causal, $id);

    if ($stmt->execute()) {

        if($valor != 0){

            $sql = "INSERT INTO sessions (`order`, tipo, valor, tipoValor, estado, userID, use_date, `site`, fecha, titulo, medio_pago, archivo, psi, hora, link_ingreso, subtotal, ingresoRT, ingresoPROPIO, iva, autorenta, margenNeto, ica, renta, utilidadBruta, margenNetoBruto, valpsi, fecpacom, consits, observa_pago, ref, fecpareal, fact) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'ssdsissssssssssddddddddddisissss',
                $order, $tipo, $valor, $tipoValor, $estado, $userID, $use_date, $site, $fecha, $titulo,
                $medio_pago, $archivo, $psi, $hora, $link_ingreso, $subtotal, $ingresoRT, $ingresoPROPIO,
                $iva, $autorenta, $margenNeto, $ica, $renta, $utilidadBruta, $margenNetoBruto,
                $valpsi, $fecpacom, $consits, $observa_pago, $ref, $fecpareal, $fact
            );
        
            if ($stmt->execute()) {
                $encryptedStatus = simpleEncrypt('cancelexitosa', '2020');
                // Obtener los parámetros actuales de la URL
                $params = $_GET;
                
                // Reemplazar o agregar el parámetro 'sta'
                $params['sta'] = $encryptedStatus;
                
                // Construir la nueva query string
                $queryString = http_build_query($params);
                
                // Redirigir
                header('Location: citas_tmrr?' . $queryString);
                exit; 
            } else {
                $encryptedStatus = simpleEncrypt('error_insertcancel', '2020');
                header('Location: citas_tmrr?sta=' . urlencode($encryptedStatus));
                exit();
            }
        } else {
            $encryptedStatus = simpleEncrypt('cancelexitosa', '2020');
            // Obtener los parámetros actuales de la URL
            $params = $_GET;
            
            // Reemplazar o agregar el parámetro 'sta'
            $params['sta'] = $encryptedStatus;
            
            // Construir la nueva query string
            $queryString = http_build_query($params);
            
            // Redirigir
            header('Location: citas_tmrr?' . $queryString);
            exit; 
        }
    } else {
        $encryptedStatus = simpleEncrypt('error_insertcancel', '2020');
        header('Location: citas_tmrr?sta=' . urlencode($encryptedStatus));
        exit();
    }

    $stmt->close();

}
?>
