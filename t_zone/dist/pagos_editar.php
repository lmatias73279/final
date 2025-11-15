<?php
include "../../conexionsm.php"; // conexión en $conn con mysqli

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validar y sanitizar entradas
    $id            = isset($_POST['id']) ? (int) $_POST['id'] : 0;
    $valor         = isset($_POST['valor']) ? (float) $_POST['valor'] : 0;
    $paciente      = isset($_POST['paciente']) ? trim($_POST['paciente']) : '';
    $tipo_consulta = isset($_POST['tipo_consulta']) ? trim($_POST['tipo_consulta']) : '';

    if ($id > 0) {
        try {
            // 1. Obtener order del registro en pays
            $sql1 = "SELECT `order` FROM pays WHERE id = ?";
            $stmt1 = $conn->prepare($sql1);
            $stmt1->bind_param("i", $id);
            $stmt1->execute();
            $stmt1->bind_result($order);
            $stmt1->fetch();
            $stmt1->close();

            $fact = 0;

            // 2. Consultar datos en sessions con ese order
            $tipoValor = $psi = $use_date = $medio_pago = $ref = $fecpareal = null;
            $fact = 0;
            $estados = []; // aquí se guardan todos los estados

            if (!empty($order)) {
                // 2a. Traer el primer registro para las variables únicas
                $sql2 = "SELECT tipo, tipoValor, psi, use_date, medio_pago, ref, fecpareal, fact, observa_pago
                        FROM sessions
                        WHERE `order` = ?
                        LIMIT 1";
                $stmt2 = $conn->prepare($sql2);
                $stmt2->bind_param("s", $order);
                $stmt2->execute();
                $stmt2->bind_result($tipomod, $tipoValor, $psi, $use_date, $medio_pago, $ref, $fecpareal, $fact, $observa_pago);
                $stmt2->fetch();
                $stmt2->close();

                // 2b. Traer todos los estados
                $sql2b = "SELECT estado 
                          FROM sessions
                          WHERE `order` = ?";
                $stmt2b = $conn->prepare($sql2b);
                $stmt2b->bind_param("s", $order);
                $stmt2b->execute();
                $result2b = $stmt2b->get_result();
                while ($row = $result2b->fetch_assoc()) {
                    $estados[] = $row['estado'];
                }
                $stmt2b->close();
            }


            if($fact === 0){

              // Validar que TODOS los estados sean 2 o 3
              $soloPermitidos = true;
              foreach ($estados as $e) {
                  if ($e != 2 && $e != 3) {
                      $soloPermitidos = false;
                      break;
                  }
              }

              if ($soloPermitidos) {
             

                // 3. Actualizar registro en pays
                $sql = "UPDATE pays SET valor = ? WHERE id = ?";
                $stmt = $conn->prepare($sql);
                $stmt->bind_param("di", $valor, $id);
                $stmt->execute();
                $stmt->close();

                // 4. Contar cuántas sesiones tiene ese order
                $count_sessions = 0;
                if (!empty($order)) {
                    $sql3 = "SELECT COUNT(*) FROM sessions WHERE `order` = ?";
                    $stmt3 = $conn->prepare($sql3);
                    $stmt3->bind_param("s", $order);
                    $stmt3->execute();
                    $stmt3->bind_result($count_sessions);
                    $stmt3->fetch();
                    $stmt3->close();
                }

                // 5. Eliminar registros en sessions con condiciones
                $deleted_rows = 0;
                if (!empty($order)) {
                    $sql_del = "DELETE FROM sessions 
                                WHERE `order` = ?
                                  AND (fecha IS NULL OR fecha = '0000-00-00')
                                  AND (link_ingreso IS NULL OR link_ingreso = '')
                                  AND (idevent IS NULL OR idevent = '')
                                  AND estado = 3";
                    $stmt_del = $conn->prepare($sql_del);
                    $stmt_del->bind_param("s", $order);
                    $stmt_del->execute();
                    $deleted_rows = $stmt_del->affected_rows;
                    $stmt_del->close();
                }

                // 6. Actualizar registros en sessions con estado = 2
                $updated_rows = 0;
                if (!empty($order)) {
                    $sql_upd = "UPDATE sessions
                                SET 
                                    `order` = '',
                                    estado = 1,
                                    use_date = '0000-00-00',  -- si es DATE usa esto, si es VARCHAR puedes usar ''
                                    titulo = '',
                                    medio_pago = 0,
                                    valor = 0,
                                    tipoValor = 0,
                                    archivo = '',
                                    subtotal = 0,
                                    ingresoRT = 0,
                                    ingresoPROPIO = 0,
                                    iva = 0.00,
                                    autorenta = 0.00,
                                    margenNeto = 0.00,
                                    ica = 0.00,
                                    renta = 0.00,
                                    utilidadBruta = 0.00,
                                    margenNetoBruto = 0.00,
                                    observa_pago = '',
                                    ref = '',
                                    fecpareal = '0000-00-00'  -- mismo caso, según tipo de columna
                                WHERE `order` = ?
                                  AND estado = 2";
                    $stmt_upd = $conn->prepare($sql_upd);
                    $stmt_upd->bind_param("s", $order);
                    $stmt_upd->execute();
                    $updated_rows = $stmt_upd->affected_rows;
                    $stmt_upd->close();
                }

                // Mostrar resultado de la actualización
                echo "Sesiones actualizadas (estado=2 -> limpio): " . htmlspecialchars($updated_rows) . "<br>";


                // 6. Mostrar resultados
                echo "ID actualizado: " . htmlspecialchars($id) . "<br>";
                echo "Valor nuevo: " . htmlspecialchars($valor) . "<br>";
                echo "Paciente: " . htmlspecialchars($paciente) . "<br>";
                echo "Tipo de consulta: " . htmlspecialchars($tipo_consulta) . "<br>";
                echo "Order asociado: " . htmlspecialchars($order) . "<br>";
                echo "Sesiones asociadas: " . htmlspecialchars($count_sessions) . "<br>";
                echo "Sesiones eliminadas: " . htmlspecialchars($deleted_rows) . "<br><br>";

                // Variables de sessions
                echo "tipoValor: " . htmlspecialchars($tipoValor) . "<br>";
                echo "psi: " . htmlspecialchars($psi) . "<br>";
                echo "use_date: " . htmlspecialchars($use_date) . "<br>";
                echo "medio_pago: " . htmlspecialchars($medio_pago) . "<br>";
                echo "ref: " . htmlspecialchars($ref) . "<br>";
                echo "fecpareal: " . htmlspecialchars($fecpareal) . "<br>";
                echo "fact: $fact<br>";
                echo "Estados: " . implode(", ", $estados) . "<br>";

                
                // Recoger los datos del formulario
                $id_profesional_car = $psi;
                $id_paciente_car = $paciente;
                $tipo_terapia_car = $tipomod;
                $cantidad_car = $count_sessions;
                $tipo_atencion_car = $tipo_consulta;
                $medio_pago = $medio_pago;
                $currency = $tipoValor;
                $valor_real = $valor;
                $ref = $ref;
                $fecpareal = $fecpareal;
                $observaciones = $observa_pago;
                date_default_timezone_set('America/Bogota');
                $fechaActual = date('Y-m-d H:i:s');

                // Calcular el valor dividido por la cantidad
                $valor_por_registro = $valor_real / $cantidad_car;


                // Obtener comisión del usuario sin seguridad (NO RECOMENDADO)
                $result = $conn->query("SELECT comision, numdoc, tip_pro FROM usuarios WHERE id = $id_profesional_car LIMIT 1");
                $row = $result->fetch_assoc();
                $comision = $row['comision'];
                $numdocpsi = $row['numdoc'];
                $tip_pro = $row['tip_pro'];

                if($cantidad_car > 2 && $comision > 60){
                    if($numdocpsi !== "39463776" && $numdocpsi !== "1010129373"){
                        $comision = 60;
                    }
                }

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

                if($tip_pro === 2){
                    if($tipo_atencion_car === 1){
                        $ingresoRT = 160000;
                    }else if($tipo_atencion_car === 2){
                        $ingresoRT = 135000;
                    }else{
                        $ingresoRT = 0;
                    }
                }

                $order = $order;
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
                            echo "<br>";
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

                // Encriptar el nuevo valor de 'sta'
                $encryptedStatus = simpleEncrypt('editpagook', '2020');
                
                // Obtener los parámetros actuales de la URL
                $params = $_GET;
                
                // Reemplazar o agregar el parámetro 'sta'
                $params['sta'] = $encryptedStatus;
                
                // Construir la nueva query string
                $queryString = http_build_query($params);
                
                // Redirigir
                header('Location: pagos?' . $queryString);
                exit;    

                // Cerrar la conexión
                $conn->close();


              }else{
                  
                // Encriptar el nuevo valor de 'sta'
                $encryptedStatus = simpleEncrypt('nosepuedeporestado', '2020');
                
                // Obtener los parámetros actuales de la URL
                $params = $_GET;
                
                // Reemplazar o agregar el parámetro 'sta'
                $params['sta'] = $encryptedStatus;
                
                // Construir la nueva query string
                $queryString = http_build_query($params);
                
                // Redirigir
                header('Location: pagos?' . $queryString);
                exit; 
            
              }
            }else{
                
                // Encriptar el nuevo valor de 'sta'
                $encryptedStatus = simpleEncrypt('nosepuedefacturado', '2020');
                
                // Obtener los parámetros actuales de la URL
                $params = $_GET;
                
                // Reemplazar o agregar el parámetro 'sta'
                $params['sta'] = $encryptedStatus;
                
                // Construir la nueva query string
                $queryString = http_build_query($params);
                
                // Redirigir
                header('Location: pagos?' . $queryString);
                exit; 
            }

        } catch (mysqli_sql_exception $e) {
            echo "<br><b>Error en la base de datos</b><br>";
            echo "Mensaje: " . htmlspecialchars($e->getMessage()) . "<br>";
            echo "Código: " . htmlspecialchars($e->getCode()) . "<br>";
            echo "Archivo: " . htmlspecialchars($e->getFile()) . "<br>";
            echo "Línea: " . htmlspecialchars($e->getLine()) . "<br>";
            echo "Trace:<pre>" . htmlspecialchars($e->getTraceAsString()) . "</pre><br>";

            // Si quieres ver el último SQL ejecutado (cuando usas mysqli_prepare)
            if (isset($sql)) {
                echo "Última consulta SQL: <pre>" . htmlspecialchars($sql) . "</pre><br>";
            }
            
            // Encriptar el nuevo valor de 'sta'
            $encryptedStatus = simpleEncrypt('errorsql', '2020');
            
            // Obtener los parámetros actuales de la URL
            $params = $_GET;
            
            // Reemplazar o agregar el parámetro 'sta'
            $params['sta'] = $encryptedStatus;
            
            // Construir la nueva query string
            $queryString = http_build_query($params);
            
            // Redirigir
            header('Location: pagos?' . $queryString);
            exit;  
        }

    } else {
        echo "ID inválido.";
    }
} else {
    echo "Acceso no permitido.";
}
