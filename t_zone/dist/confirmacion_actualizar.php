<?php
include "../../conexionsm.php";

if (isset($_GET['id']) && isset($_GET['valpsi'])) {
    $id = intval($_GET['id']);
    $valpsi = intval($_GET['valpsi']); // 1 o 2

    // Obtener comisión del usuario
    $result = $conn->query("SELECT subtotal, ingresoRT, valor FROM sessions WHERE id = $id LIMIT 1");
    $row = $result->fetch_assoc();

    if ($row) {
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
        $ica = round($ingresoPROPIO * 966 / 100000, 2);
        $baserenta = round($ingresoPROPIO - $ica, 2);
        $renta = round($baserenta * 35 / 100, 2);
        $utilidadBruta = $ingresoPROPIO - $ica - $renta;

        // Verificar si $valor_por_registro es mayor que cero antes de la división
        if ($valor_por_registro > 0) {
            $margenNeto = round($ingresoPROPIO / $valor_por_registro * 100, 2);
            $margenNetoBruto = round($utilidadBruta / $valor_por_registro * 100, 2);
        } else {
            // Si $valor_por_registro es cero, asignar 0 al margen neto y al margen neto bruto
            $margenNeto = 0;
            $margenNetoBruto = 0;
        }

        // Actualizar valpsi siempre
        $sql = "UPDATE sessions SET valpsi = ?, estado = 6, consits = 1 WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('ii', $valpsi, $id);

        if ($stmt->execute()) {
            echo "valpsi actualizado correctamente. ";
        } else {
            echo "Error al actualizar valpsi: " . $conn->error;
        }

        // Si valpsi es 2, también actualiza los otros valores
        if ($valpsi === 2) {
            $sql = "UPDATE sessions SET 
                ingresoRT = ?, 
                ingresoPROPIO = ?, 
                iva = ?, 
                autorenta = ?, 
                margenNeto = ?, 
                ica = ?, 
                renta = ?, 
                utilidadBruta = ?, 
                margenNetoBruto = ?,
                consits = 2
                WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param(
                'dddddddddi',
                $ingresoRT,
                $ingresoPROPIO,
                $iva,
                $autorenta,
                $margenNeto,
                $ica,
                $renta,
                $utilidadBruta,
                $margenNetoBruto,
                $id
            );

            if ($stmt->execute()) {
                echo "Valores calculados actualizados correctamente.";
            } else {
                echo "Error al actualizar los valores calculados: " . $conn->error;
            }
        }

        $stmt->close();
    } else {
        echo "No se encontró la sesión con el ID proporcionado.";
    }


    function simpleEncrypt($text, $key) {
        $output = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
        }
        return base64_encode($output); // Convertir a base64 para URL seguro
    }

    $encryptedStatus = simpleEncrypt('validado', '2020');
    header('Location: confirmacion?sta=' . urlencode($encryptedStatus));
    exit();

    // Cerrar la conexión
    $conn->close();

} else {
    echo "Parámetros no válidos.";
}
?>
