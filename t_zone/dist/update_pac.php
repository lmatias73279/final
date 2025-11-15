<?php

session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99){
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
    // Verifica si los datos están llegando
    if (isset($_POST['id'], $_POST['pais'], $_POST['td_usu'], $_POST['numdoc'], $_POST['pn_usu'], $_POST['sn_usu'], $_POST['pa_usu'], $_POST['sa_usu'], $_POST['tel_usu'], $_POST['cor_usu'], $_POST['fna_usu'], $_POST['per_usu'], $_POST['bold'], $_POST['estado'], $_POST['profesional_asignado'], $_POST['proceso'])) {
        var_dump($_POST);  // Imprime los datos del formulario que llegaron
    }

    // Obtén los datos del formulario (por ejemplo, del modal)
    $id = isset($_POST['id']) ? $_POST['id'] : '';
    $pais = isset($_POST['pais']) ? $_POST['pais'] : '';
    $tipdoc = isset($_POST['td_usu']) ? $_POST['td_usu'] : '';
    $numdoc = isset($_POST['numdoc']) ? $_POST['numdoc'] : '';
    $pn_usu = isset($_POST['pn_usu']) ? $_POST['pn_usu'] : '';
    $sn_usu = isset($_POST['sn_usu']) ? $_POST['sn_usu'] : '';
    $pa_usu = isset($_POST['pa_usu']) ? $_POST['pa_usu'] : '';
    $sa_usu = isset($_POST['sa_usu']) ? $_POST['sa_usu'] : '';
    $tel_usu = isset($_POST['tel_usu']) ? $_POST['tel_usu'] : '';
    $cor_usu = isset($_POST['cor_usu']) ? $_POST['cor_usu'] : '';
    $fna_usu = isset($_POST['fna_usu']) ? $_POST['fna_usu'] : '';
    $permiso = isset($_POST['per_usu']) ? $_POST['per_usu'] : '';
    $bold = isset($_POST['bold']) ? $_POST['bold'] : '';
    $tidfac = isset($_POST['tidfac']) ? $_POST['tidfac'] : '';
    $idfact = isset($_POST['idfact']) ? $_POST['idfact'] : '';
    $nomfac = isset($_POST['nomfac']) ? $_POST['nomfac'] : '';
    $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
    $proceso = isset($_POST['proceso']) ? $_POST['proceso'] : '';
    $profesion = isset($_POST['profesion']) ? $_POST['profesion'] : '';
    $valor_base = isset($_POST['valor_base']) ? $_POST['valor_base'] : '';
    $valor_pres = isset($_POST['valor_pres']) ? $_POST['valor_pres'] : '';
    $sector = isset($_POST['sector']) ? $_POST['sector'] : '';
    $money = isset($_POST['money']) ? $_POST['money'] : '';
    $profesional_asignado = isset($_POST['profesional_asignado']) ? $_POST['profesional_asignado'] : '';
    

    // Consulta los valores actuales del usuario en la base de datos
    $sql = "SELECT * FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param('i', $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    // Obtener la fecha y hora actual en el formato requerido
    $fechaHora = date('d-m-Y H:i:s');

    // Variable para almacenar el resumen de cambios
    $changesLog = '';

    // Solo actualiza si el nuevo valor es distinto al actual o si no es vacío
    $updateFields = [];

    // Convertimos los valores de pais, permiso, estado y numdoc a enteros antes de comparar
    $pais = (int)$pais;
    $permiso = (int)$permiso;
    $bold = (int)$bold;
    $estado = (int)$estado;
    $proceso = (int)$proceso;
    $profesion = (int)$profesion;
    $valor_base = (int)$valor_base;
    $valor_pres = (int)$valor_pres;
    $sector = (int)$sector;
    $money = (int)$money;
    $idfact = (int)$idfact;
    $profesional_asignado = (int)$profesional_asignado;

    // Verificar cambios y agregar al log solo si los valores son diferentes
    if ($pais !== (int)$user['pais'] && $pais !== 0) {
        $updateFields['pais'] = $pais;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PAIS DE {$user['pais']} A $pais.\n";
    }
    if ($tipdoc !== $user['tipdoc'] && $tipdoc !== '' && $_SESSION['permiso'] === 1) {
        $updateFields['tipdoc'] = $tipdoc;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL TIPO DE DOCUMENTO DE {$user['tipdoc']} A $tipdoc.\n";
    }
    if ($numdoc !== $user['numdoc'] && $numdoc !== 0 && $_SESSION['permiso'] === 1) {
        $updateFields['numdoc'] = $numdoc;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL NÚMERO DE DOCUMENTO DE {$user['numdoc']} A $numdoc.\n";
    }
    if ($profesional_asignado !== (int)$user['profesional_asignado'] && $profesional_asignado !== 0 && $_SESSION['permiso'] === 1) {
        // Obtener el nombre del profesional anterior
        $sqlAnterior = "SELECT pn_usu, pa_usu FROM usuarios WHERE id = ?";
        $stmtAnterior = $conn->prepare($sqlAnterior);
        $stmtAnterior->bind_param("i", $user['profesional_asignado']);
        $stmtAnterior->execute();
        $resultAnterior = $stmtAnterior->get_result();
        $profesionalAnterior = $resultAnterior->fetch_assoc();
        $nombreAnterior = $profesionalAnterior ? $profesionalAnterior['pn_usu'] . " " . $profesionalAnterior['pa_usu'] : "Desconocido";

        // Obtener el nombre del nuevo profesional
        $sqlNuevo = "SELECT pn_usu, pa_usu FROM usuarios WHERE id = ?";
        $stmtNuevo = $conn->prepare($sqlNuevo);
        $stmtNuevo->bind_param("i", $profesional_asignado);
        $stmtNuevo->execute();
        $resultNuevo = $stmtNuevo->get_result();
        $profesionalNuevo = $resultNuevo->fetch_assoc();
        $nombreNuevo = $profesionalNuevo ? $profesionalNuevo['pn_usu'] . " " . $profesionalNuevo['pa_usu'] : "Desconocido";

        // Actualizar el log con nombres en lugar de IDs
        $updateFields['profesional_asignado'] = $profesional_asignado;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . 
        " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PROFESIONAL DE $nombreAnterior A $nombreNuevo.\n";
    }
    if ($pn_usu !== '' && $pn_usu !== $user['pn_usu']) {
        $updateFields['pn_usu'] = $pn_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PRIMER NOMBRE DE {$user['pn_usu']} A $pn_usu.\n";
    }
    if ($sn_usu !== '' && $sn_usu !== $user['sn_usu']) {
        $updateFields['sn_usu'] = $sn_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL SEGUNDO NOMBRE DE {$user['sn_usu']} A $sn_usu.\n";
    }
    if ($pa_usu !== '' && $pa_usu !== $user['pa_usu']) {
        $updateFields['pa_usu'] = $pa_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PRIMER APELLIDO DE {$user['pa_usu']} A $pa_usu.\n";
    }
    if ($sa_usu !== '' && $sa_usu !== $user['sa_usu']) {
        $updateFields['sa_usu'] = $sa_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL SEGUNDO APELLIDO DE {$user['sa_usu']} A $sa_usu.\n";
    }
    if ($tel_usu !== '' && $tel_usu !== $user['tel_usu']) {
        $updateFields['tel_usu'] = $tel_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL TELÉFONO DE {$user['tel_usu']} A $tel_usu.\n";
    }
    if ($cor_usu !== '' && $cor_usu !== $user['cor_usu']) {
        $updateFields['cor_usu'] = $cor_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL CORREO ELECTRÓNICO DE {$user['cor_usu']} A $cor_usu.\n";
    }
    if ($fna_usu !== '' && $fna_usu !== $user['fna_usu']) {
        $updateFields['born_date'] = $fna_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ LA FECHA DE NACIMIENTO DE {$user['fna_usu']} A $fna_usu.\n";
    }
    if ($tidfac !== '' && $tidfac !== $user['tidfac']) {
        $updateFields['tidfac'] = $tidfac;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL TIPO DE IDENTIFICACION DEL FACTURADO DE {$user['tidfac']} A $tidfac.\n";
    }
    if ($idfact !== '' && $idfact !== (int)$user['idfact']) {
        $updateFields['idfact'] = $idfact;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL NÚMERO DE DOCUMENTO DEL FACTURADO DE {$user['idfact']} A $idfact.\n";
    }
    if ($nomfac !== '' && $nomfac !== $user['nomfac']) {
        $updateFields['nomfac'] = $nomfac;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL NOMBRE DEL FACTURADO DE {$user['nomfac']} A $nomfac.\n";
    }


    // Para las columnas 'permiso' y 'estado', usamos valores legibles
    if ($permiso !== (int)$user['permiso'] && $permiso !== 0 && $_SESSION['permiso'] === 1) {
        $oldPermiso = $user['permiso']; 
        $newPermiso = $permiso; 
        $updateFields['permiso'] = $permiso;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PERMISO DE $oldPermiso A $newPermiso.\n";
    }
    if ($estado !== (int)$user['estado'] && $_SESSION['permiso'] === 1) {
        $oldEstado = $user['estado']; 
        $newEstado = $estado; 
        $updateFields['estado'] = $estado;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL ESTADO DE $oldEstado A $newEstado.\n";
    }
    if ($profesion !== (int)$user['profession'] && $_SESSION['permiso'] === 1) {
        $oldProfesion = $user['profession']; 
        $newProfesion = $profesion; 
        $updateFields['profession'] = $profesion;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PROFESIÓN DE $oldProfesion A $newProfesion.\n";
    }
    if ($valor_base !== (int)$user['valor_base'] && $_SESSION['permiso'] === 1) {
        $oldvalor_base = $user['valor_base']; 
        $newvalor_base = $valor_base; 
        $updateFields['valor_base'] = $valor_base;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL VALOR VIRTUAL DE $oldvalor_base A $newvalor_base.\n";
    }
    if ($profesion !== (int)$user['valor_pres'] && $_SESSION['permiso'] === 1) {
        $oldvalor_pres = $user['valor_pres']; 
        $newvalor_pres = $valor_pres; 
        $updateFields['valor_pres'] = $valor_pres;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL VALOR PRESENCIAL DE $oldvalor_pres A $newvalor_pres.\n";
    }
    if ($sector !== (int)$user['sector'] && $_SESSION['permiso'] === 1) {
        $oldSector = $user['sector']; 
        $newSector = $sector; 
        $updateFields['sector'] = $sector;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL SECTOR DE $oldSector A $newSector.\n";
    }
    if ($money !== (int)$user['currency'] && $_SESSION['permiso'] === 1) {
        $oldMoney = $user['currency']; 
        $newMoney = $money; 
        $updateFields['currency'] = $money;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL MONEDA DE $oldMoney A $newMoney.\n";
    }
    if ($bold !== (int)$user['bold'] && $_SESSION['permiso'] === 1) {
        $oldbold = $user['bold']; 
        $newbold = $bold; 
        $updateFields['bold'] = $bold;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ BOLD DE $oldbold A $newbold.\n";
    }

    // Si hay campos para actualizar
    if (!empty($updateFields)) {
        // Construir la consulta de actualización dinámicamente
        $updateSql = "UPDATE usuarios SET ";
        $updateParams = [];
        $types = '';
        foreach ($updateFields as $field => $value) {
            $updateSql .= "$field = ?, ";
            $updateParams[] = $value;
            $types .= 's'; // Asume que todos los campos son cadenas (ajustar según el tipo real de cada campo)
        }
        $updateSql = rtrim($updateSql, ', ') . " WHERE id = ?";
        $updateParams[] = $id;
        $types .= 'i'; // El ID es un entero

        // Prepara la consulta con los parámetros
        $stmt = $conn->prepare($updateSql);
        $stmt->bind_param($types, ...$updateParams);

        if ($stmt->execute()) {
            // Actualizar log_cambios con el nuevo resumen de cambios
            $newLog = strtoupper($changesLog) . (empty($user['log_cambios']) ? '' : "\n" . strtoupper($user['log_cambios']));
            $sqlLog = "UPDATE usuarios SET log_cambios = ? WHERE id = ?";
            $stmtLog = $conn->prepare($sqlLog);
            $stmtLog->bind_param('si', $newLog, $id);
            $stmtLog->execute();

            // Redirige con éxito
            $encryptedStatus = simpleEncrypt('success_update', '2020');
            header('Location: users_pac?sta=' . urlencode($encryptedStatus));
            exit();
        } else {
            // Si hay un error, redirige con error
            $encryptedStatus = simpleEncrypt('error_update', '2020');
            header('Location: users_pac?sta=' . urlencode($encryptedStatus));
            exit();
        }
    } else {
        // Si no hay cambios, redirige sin hacer nada
        $encryptedStatus = simpleEncrypt('no_update', '2020');
        header('Location: users_pac?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
