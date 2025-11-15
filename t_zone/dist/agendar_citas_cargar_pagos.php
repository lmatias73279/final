<?php
// Incluir la conexión a la base de datos
include "../../conexionsm.php";

// Comprobar si el formulario fue enviado
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    // Recoger los datos del formulario
    $id_profesional_car = $_POST['id_profesional_car'];
    $id_paciente_car = $_POST['id_paciente_car'];
    $tipo_terapia_car = $_POST['tipo_terapia_car'];
    $cantidad_car = $_POST['cantidad_car'];
    $tipo_atencion_car = $_POST['tipo_atencion_car'];
    $medio_pago = $_POST['medio_pago'];
    $currency = $_POST['currency'];
    $ref = $_POST['ref'];
    $fecpareal = $_POST['fecpareal'];
    $valor_real = $_POST['valor_real'];
    $observaciones = $_POST['observaciones'];
    date_default_timezone_set('America/Bogota');
    $fechaActual = date('Y-m-d H:i:s');

    // Calcular el valor dividido por la cantidad
    $valor_por_registro = $valor_real / $cantidad_car;


    // Obtener comisión del usuario sin seguridad (NO RECOMENDADO)
    $result = $conn->query("SELECT comision, numdoc FROM usuarios WHERE id = $id_profesional_car LIMIT 1");
    $row = $result->fetch_assoc();
    $comision = $row['comision'];
    $numdocpsi = $row['numdoc'];

    if($cantidad_car > 2 && $comision > 60){
        if($numdocpsi !== "39463776" && $numdocpsi !== "1010129373"){
            $comision = 60;
        }
    }

    if($comision === 60){
        // Definir la comisión (si $amount_real está definido)
        $subtotal = intval($valor_por_registro * 92937 / 100000);
    }else{
        // Definir la comisión (si $amount_real está definido)
        $subtotal = intval($valor_por_registro * 94608 / 100000);
    }

    // Valores iniciales para las variables calculadas
    $ingresoRT = intval($subtotal * $comision / 100);
    $ingresoPROPIO = $subtotal - $ingresoRT;
    $iva = round($ingresoPROPIO * 19 / 100 , 2);
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

    $order = 'ORDER_' . uniqid();
    $archivo_subido = "";
    // Procesar el archivo subido
    if (isset($_FILES['soporte']) && $_FILES['soporte']['error'] === UPLOAD_ERR_OK) {
        // Obtener la extensión del archivo
        $ext = pathinfo($_FILES['soporte']['name'], PATHINFO_EXTENSION);
        
        // Generar un nombre único para el archivo
        $nombre_archivo = uniqid('pago_') . '.' . $ext;

        // Establecer el directorio de destino
        $directorio = 'assets/docs/pays/' . $nombre_archivo;

        // Subir el archivo al directorio
        if (move_uploaded_file($_FILES['soporte']['tmp_name'], $directorio)) {
            // Archivo subido con éxito
            $archivo_subido = $nombre_archivo;
        } else {
            // Error al subir el archivo
            echo "Error al subir el archivo.";
            exit;
        }
    }

    // Ajustar la lógica para tipos de terapia 1 y 7
    if ($tipo_terapia_car == 1 || $tipo_terapia_car == 7) {
        $tipos_terapia = [1, 7];
        $placeholders = implode(',', array_fill(0, count($tipos_terapia), '?'));
    } else {
        $tipos_terapia = [$tipo_terapia_car];
        $placeholders = '?';
    }

    // 1. Contar cuántos registros coinciden con psi, userID, tipos de terapia y titulo vacío
    $sql_count = "SELECT COUNT(*) FROM sessions 
                  WHERE site = ? AND userID = ? AND tipo IN ($placeholders) AND titulo = ''";
    if ($stmt_count = $conn->prepare($sql_count)) {
        // Enlazar los parámetros de la consulta (psi, userID, tipos de terapia)
        $bind_params = [$tipo_atencion_car, $id_paciente_car];
        foreach ($tipos_terapia as $tipo) {
            $bind_params[] = $tipo;
        }
        $stmt_count->bind_param(str_repeat('i', count($bind_params)), ...$bind_params);
        $stmt_count->execute();
        $stmt_count->bind_result($num_registros_existentes);
        $stmt_count->fetch();
        $stmt_count->close();
    } else {
        echo "Error al contar los registros.";
        exit;
    }

    // Calcular cuántos registros actualizar y cuántos insertar
    $registros_a_actualizar = min($num_registros_existentes, $cantidad_car); // Actualizamos hasta el mínimo entre registros existentes y cantidad
    $registros_a_insertar = $cantidad_car - $registros_a_actualizar; // Insertamos el resto

    $sqlorders = "INSERT INTO pays (`order`, fecha, valor, archivo, ref, fecpareal, observaciones) VALUES (?, ?, ?, ?, ?, ?, ?)";
    if ($stmt = $conn->prepare($sqlorders)) {
        // Vincular los parámetros a la consulta
        $stmt->bind_param("ssdssss", $order, $fechaActual, $valor_real, $archivo_subido, $ref, $fecpareal, $observaciones);
    
        // Ejecutar la consulta
        if ($stmt->execute()) {
            echo "Registro insertado correctamente.";
        } else {
            echo "Error al insertar el registro: " . $stmt->error;
        }
    
        // Cerrar el statement
        $stmt->close();
    } else {
        echo "Error al preparar la consulta: " . $conn->error;
    }

    // 2. Actualizar los registros existentes
    if ($registros_a_actualizar > 0) {
        $sql_update = "UPDATE sessions SET subtotal = ?, medio_pago = ?, tipoValor = ?, valor = ?, archivo = ?, use_date = ?, `order` = ?, ingresoRT = ?, ingresoPROPIO = ?, iva = ?, autorenta = ?, margenNeto = ?, ica = ?, renta = ?, utilidadBruta = ?, margenNetoBruto = ?, ref = ?, fecpareal = ?, titulo = 1, estado = 2
                    WHERE site = ? AND userID = ? AND tipo IN ($placeholders) AND titulo = '' AND estado != 5 LIMIT ?";
        if ($stmt_update = $conn->prepare($sql_update)) {
            // Crear el array de parámetros
            $bind_params = [$subtotal, $medio_pago, $currency, $valor_por_registro, $archivo_subido, $fechaActual, $order,
                            $ingresoRT, $ingresoPROPIO, $iva, $autorenta, $margenNeto, $ica, $renta,
                            $utilidadBruta, $margenNetoBruto, $ref, $fecpareal, $tipo_atencion_car, $id_paciente_car];
            foreach ($tipos_terapia as $tipo) {
                $bind_params[] = $tipo;
            }
            $bind_params[] = $registros_a_actualizar;

            // Definir los tipos de datos
            $types = "iiiisss" . str_repeat('d', 9) . "ssii" . str_repeat('i', count($tipos_terapia)) . "i";

            // Vincular los parámetros
            $stmt_update->bind_param($types, ...$bind_params);

            // Ejecutar la consulta
            if ($stmt_update->execute()) {
                echo "$registros_a_actualizar registros actualizados correctamente.";
            } else {
                echo "Error al actualizar los registros: " . $stmt_update->error;
            }

            $stmt_update->close();
        } else {
            echo "Error al preparar la consulta de actualización: " . $conn->error;
        }
    }

    // 3. Insertar los registros restantes
    if ($registros_a_insertar > 0) {
        $sql_insert = "INSERT INTO sessions (subtotal, psi, userID, tipo, site, medio_pago, tipoValor, valor, archivo, use_date, `order`, ingresoRT, ingresoPROPIO, iva, autorenta, margenNeto, ica, renta, utilidadBruta, margenNetoBruto, ref, fecpareal, titulo, estado) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1, 3)";

        for ($i = 0; $i < $registros_a_insertar; $i++) {
            if ($stmt_insert = $conn->prepare($sql_insert)) {
                // Ajustar los tipos de datos según la estructura de la tabla
                $stmt_insert->bind_param(
                    "iiiiiiiisssdddddddddss", 
                    $subtotal,  // psi: int
                    $id_profesional_car,  // psi: int
                    $id_paciente_car,     // userID: int
                    $tipo_terapia_car,    // tipo: int
                    $tipo_atencion_car,   // site: int
                    $medio_pago,          // medio_pago: int
                    $currency,            // tipoValor: int
                    $valor_por_registro,  // valor: int
                    $archivo_subido,      // archivo: text -> string
                    $fechaActual,         // use_date: datetime -> string
                    $order,               // order: text -> string
                    $ingresoRT,           // ingresoRT: decimal -> double
                    $ingresoPROPIO,       // ingresoPROPIO: decimal -> double
                    $iva,                 // iva: decimal -> double
                    $autorenta,           // autorenta: decimal -> double
                    $margenNeto,          // margenNeto: decimal -> double
                    $ica,                 // ica: decimal -> double
                    $renta,               // renta: decimal -> double
                    $utilidadBruta,       // utilidadBruta: decimal -> double
                    $margenNetoBruto,      // margenNetoBruto: decimal -> double
                    $ref,      // ref: decimal -> string
                    $fecpareal      // fecpareal: decimal -> string
                );

                if ($stmt_insert->execute()) {
                    echo "Registro agregado con éxito.";
                } else {
                    echo "Error al agregar el registro: " . $stmt_insert->error;
                }

                $stmt_insert->close();
            } else {
                echo "Error al preparar la consulta de inserción: " . $conn->error;
            }
        }
    }


    function simpleEncrypt($text, $key) {
        $output = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
        }
        return base64_encode($output); // Convertir a base64 para URL seguro
    }

    $encryptedStatus = simpleEncrypt('carpagext', '2020');
    header('Location: agendar_citas?sta=' . urlencode($encryptedStatus));
    exit();

    // Cerrar la conexión
    $conn->close();
}
?>
