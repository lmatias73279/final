<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    include "../../conexionsm.php"; // Ajusta la ruta según tu estructura

    function simpleEncrypt($text, $key) {
        $output = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
        }
        return base64_encode($output); // Convertir a base64 para URL seguro
    }

    $id_user = $_POST['id']; 

    // 1. Obtener hiscli y numdoc con consulta preparada
    $sql = "SELECT hiscli, numdoc FROM usuarios WHERE id = ?";
    $stmt = mysqli_prepare($conn, $sql);
    mysqli_stmt_bind_param($stmt, "i", $id_user);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_bind_result($stmt, $hisclicon, $numdoccon);
    mysqli_stmt_fetch($stmt);
    mysqli_stmt_close($stmt);

    // Verificamos que se hayan obtenido datos antes de continuar
    if ($hisclicon && $numdoccon) {
        // 2. Buscar el primer id con ese numdoc
        $sql2 = "SELECT id FROM usuarios WHERE numdoc = ? ORDER BY id ASC LIMIT 1";
        $stmt2 = mysqli_prepare($conn, $sql2);
        mysqli_stmt_bind_param($stmt2, "s", $numdoccon);
        mysqli_stmt_execute($stmt2);
        mysqli_stmt_bind_result($stmt2, $id_usercon);
        mysqli_stmt_fetch($stmt2);
        mysqli_stmt_close($stmt2);
    } else {
        // Si no se encontró el usuario original
        $hisclicon = null;
        $numdoccon = null;
        $id_usercon = null;
    }

    date_default_timezone_set('America/Bogota'); 
    $fecha_actual = date("Y-m-d H:i:s");

    $tipos_consentimiento = [
        "Psicología" => 1,
        "Psiquiatría" => 2,
        "Adultos" => 3,
        "Pareja" => 4,
        "Niños" => 5
    ];

    if (isset($_POST['documentos']) && is_array($_POST['documentos'])) {
        $nuevos_insertados = 0;

        foreach ($_POST['documentos'] as $documento) {
            if (isset($tipos_consentimiento[$documento])) {
                $tipo = $tipos_consentimiento[$documento];

                // Verificar si ya existe un consentimiento con el mismo usuario, tipo y estado 0
                $check_sql = "SELECT id, hiscli FROM consentimientos WHERE id_user = ? AND tipo_consentimiento = ? AND hiscli = ? AND estado = 0";
                $stmt = mysqli_prepare($conn, $check_sql);
                mysqli_stmt_bind_param($stmt, "iis", $id_usercon, $tipo, $hisclicon);
                mysqli_stmt_execute($stmt);
                mysqli_stmt_store_result($stmt);

                if (mysqli_stmt_num_rows($stmt) == 0) {
                    // Si no existe, insertarlo
                    $insert_sql = "INSERT INTO consentimientos (id_user, fecha, tipo_consentimiento, hiscli, estado) 
                                   VALUES (?, ?, ?, ?, 0)";
                    $stmt_insert = mysqli_prepare($conn, $insert_sql);
                    mysqli_stmt_bind_param($stmt_insert, "isis", $id_usercon, $fecha_actual, $tipo, $hisclicon);
                    if (mysqli_stmt_execute($stmt_insert)) {
                        $nuevos_insertados++;
                    }
                }

                mysqli_stmt_close($stmt);
            }
        }

        if ($nuevos_insertados > 0) {
            $encryptedStatus = simpleEncrypt('success_solfir', '2020');
        } else {
            $encryptedStatus = simpleEncrypt('no_new_records', '2020'); // No se agregaron nuevos registros
        }
        header('Location: users_pac?sta=' . urlencode($encryptedStatus));
        exit();
    } else {
        $encryptedStatus = simpleEncrypt('fail_solfir', '2020');
        header('Location: users_pac?sta=' . urlencode($encryptedStatus));
        exit();
    }

    mysqli_close($conn);
}
