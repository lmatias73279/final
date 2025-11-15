<?php
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 99 && $_SESSION['numdoc'] !== '1000693019'){
    header("location: login");
}
include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // Convertir a base64 para URL seguro
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['pais'], $_POST['td_usu'], $_POST['numdoc'], $_POST['pn_usu'], $_POST['sn_usu'], $_POST['pa_usu'], $_POST['sa_usu'], $_POST['tel_usu'], $_POST['cor_usu'], $_POST['fna_usu'], $_POST['per_usu'], $_POST['estado'], $_POST['descripcion'])) {
        var_dump($_POST);
    }

    $pais = isset($_POST['pais']) ? $_POST['pais'] : '';
    $tipdoc = isset($_POST['td_usu']) ? $_POST['td_usu'] : '';
    $numdoc = isset($_POST['numdoc']) ? $_POST['numdoc'] : '';
    $pn_usu = isset($_POST['pn_usu']) ? $_POST['pn_usu'] : '';
    $sn_usu = isset($_POST['sn_usu']) ? $_POST['sn_usu'] : '';
    $pa_usu = isset($_POST['pa_usu']) ? $_POST['pa_usu'] : '';
    $sa_usu = isset($_POST['sa_usu']) ? $_POST['sa_usu'] : '';
    $tel_usu = isset($_POST['tel_usu']) ? $_POST['tel_usu'] : '';
    $cor_usu = isset($_POST['cor_usu']) ? $_POST['cor_usu'] : '';
    $fna_usu = !empty($_POST['fna_usu']) ? $_POST['fna_usu'] : null;
    $permiso = isset($_POST['per_usu']) ? $_POST['per_usu'] : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
    $comision = isset($_POST['comision']) ? $_POST['comision'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $tip_pro = isset($_POST['tip_pro']) ? $_POST['tip_pro'] : '';
    $profesional_asignado = isset($_POST['profesional_asignado']) ? $_POST['profesional_asignado'] : '';
    
    $permisosSeleccionados = isset($_POST['permisos']) ? $_POST['permisos'] : [];

    $permiso_blog = in_array('blog', $permisosSeleccionados) ? 1 : 0;
    $permiso_biblioteca = in_array('biblioteca', $permisosSeleccionados) ? 1 : 0;
    $permiso_citas = in_array('citas', $permisosSeleccionados) ? 1 : 0;
    $permiso_promociones = in_array('promociones', $permisosSeleccionados) ? 1 : 0;
    $permiso_gastos = in_array('gastos', $permisosSeleccionados) ? 1 : 0;
    $permiso_citas_pagos = in_array('citas y pagos', $permisosSeleccionados) ? 1 : 0;
    
    // Verificar si ya existe un usuario con los mismos datos
    $sqlCheck = "SELECT id FROM usuarios WHERE pais = ? AND tipdoc = ? AND numdoc = ? AND cor_usu = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param('ssss', $pais, $tipdoc, $numdoc, $cor_usu);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        $encryptedStatus = simpleEncrypt('repetido', '2020');
        header('Location: usuarios?sta=' . urlencode($encryptedStatus));
        exit();
    }
    $stmtCheck->close();
    

    // Ruta al archivo JSON
    $jsonFile = 'hisclinic.json';
    $data = json_decode(file_get_contents($jsonFile), true);
    $hisclinica = "SM" . $data['autoincremental'];
    $data['autoincremental'] += 1;
    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

    // SQL corregido: Agregado campo faltante y corrección en nombre
    $sql = "INSERT INTO usuarios (pais, tipdoc, numdoc, pn_usu, sn_usu, pa_usu, sa_usu, tel_usu, cor_usu, born_date, permiso, estado, descripcion, clave_sys, comision, hiscli, tip_pro, profesional_asignado, permiso_blog, permiso_biblioteca, permiso_citas, permiso_promociones, permiso_gastos, permiso_citas_pagos) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Corrección en los valores enlazados: todos los campos deben coincidir
    $stmt->bind_param('ssssssssssssssisiiiiiiii', $pais, $tipdoc, $numdoc, $pn_usu, $sn_usu, $pa_usu, $sa_usu, $tel_usu, $cor_usu, $fna_usu, $permiso, $estado, $descripcion, $numdoc, $comision, $hisclinica, $tip_pro, $profesional_asignado, $permiso_blog, $permiso_biblioteca, $permiso_citas, $permiso_promociones, $permiso_gastos, $permiso_citas_pagos);

    if ($stmt->execute()) {
        $encryptedStatus = simpleEncrypt('success_create', '2020');
        header('Location: usuarios?sta=' . urlencode($encryptedStatus));
        exit();
    } else {
        $encryptedStatus = simpleEncrypt('error_create', '2020');
        header('Location: usuarios?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
