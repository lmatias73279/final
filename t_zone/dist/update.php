<?php

session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
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
    // Verifica si los datos están llegando
    if (isset($_POST['id'], $_POST['pais'], $_POST['td_usu'], $_POST['numdoc'], $_POST['pn_usu'], $_POST['sn_usu'], $_POST['pa_usu'], $_POST['sa_usu'], $_POST['tel_usu'], $_POST['cor_usu'], $_POST['fna_usu'], $_POST['per_usu'], $_POST['estado'])) {
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
    $estado = isset($_POST['estado']) ? $_POST['estado'] : '';
    $comision = isset($_POST['comision']) ? $_POST['comision'] : '';
    $descripcion = isset($_POST['descripcion']) ? $_POST['descripcion'] : '';
    $tip_pro = isset($_POST['tip_pro']) ? $_POST['tip_pro'] : '';
    $pro_asig = isset($_POST['profesional_asignado']) ? $_POST['profesional_asignado'] : '';
    
    $permisosSeleccionados = isset($_POST['permisos']) ? $_POST['permisos'] : [];

    $permiso_blog = in_array('blog', $permisosSeleccionados) ? 1 : 0;
    $permiso_biblioteca = in_array('biblioteca', $permisosSeleccionados) ? 1 : 0;
    $permiso_citas = in_array('citas', $permisosSeleccionados) ? 1 : 0;
    $permiso_promociones = in_array('promociones', $permisosSeleccionados) ? 1 : 0;
    $permiso_gastos = in_array('gastos', $permisosSeleccionados) ? 1 : 0;
    $permiso_citas_pagos = in_array('citas y pagos', $permisosSeleccionados) ? 1 : 0;
    

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
    $estado = (int)$estado;
    $numdoc = (int)$numdoc;
    $comision = (int)$comision;
    $tip_pro = (int)$tip_pro;
    $pro_asig = (int)$pro_asig;

    // Verificar cambios y agregar al log solo si los valores son diferentes
    if ($pais !== (int)$user['pais'] && $pais !== 0) {
        $updateFields['pais'] = $pais;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PAIS DE {$user['pais']} A $pais.\n";
    }
    if ($tipdoc !== $user['tipdoc'] && $tipdoc !== '' && $_SESSION['numdoc'] === '1000693019') {
        $updateFields['tipdoc'] = $tipdoc;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL TIPO DE DOCUMENTO DE {$user['tipdoc']} A $tipdoc.\n";
    }
    if ($numdoc !== (int)$user['numdoc'] && $numdoc !== 0 && $_SESSION['numdoc'] === '1000693019') {
        $updateFields['numdoc'] = $numdoc;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL NÚMERO DE DOCUMENTO DE {$user['numdoc']} A $numdoc.\n";
    }
    if ($comision !== (int)$user['comision'] && $comision !== 0 && $_SESSION['numdoc'] === '1000693019') {
        $updateFields['comision'] = $comision;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ LA COMISIÓN DE {$user['comision']} A $comision.\n";
    }
    if ($pn_usu !== '' && $pn_usu !== $user['pn_usu'] && $_SESSION['numdoc'] === '1000693019') {
        $updateFields['pn_usu'] = $pn_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PRIMER NOMBRE DE {$user['pn_usu']} A $pn_usu.\n";
    }
    if ($sn_usu !== '' && $sn_usu !== $user['sn_usu'] && $_SESSION['numdoc'] === '1000693019') {
        $updateFields['sn_usu'] = $sn_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL SEGUNDO NOMBRE DE {$user['sn_usu']} A $sn_usu.\n";
    }
    if ($pa_usu !== '' && $pa_usu !== $user['pa_usu'] && $_SESSION['numdoc'] === '1000693019') {
        $updateFields['pa_usu'] = $pa_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PRIMER APELLIDO DE {$user['pa_usu']} A $pa_usu.\n";
    }
    if ($sa_usu !== '' && $sa_usu !== $user['sa_usu'] && $_SESSION['numdoc'] === '1000693019') {
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
    if ($fna_usu !== '' && $fna_usu !== $user['born_date']) {
        $updateFields['born_date'] = $fna_usu;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ LA FECHA DE NACIMIENTO DE {$user['fna_usu']} A $fna_usu.\n";
    }
    if ($tip_pro !== '' && $tip_pro !== $user['tip_pro'] && $_SESSION['numdoc'] === '1000693019') {
        $updateFields['tip_pro'] = $tip_pro;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL TIPO DE PROFESIONAL DE {$user['tip_pro']} A $tip_pro.\n";
    }
    if ($pro_asig !== '' && $pro_asig !== $user['profesional_asignado'] && $_SESSION['numdoc'] === '1000693019') {
        $updateFields['profesional_asignado'] = $pro_asig;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PROFESIONAL ASIGNADO DE {$user['tip_pro']} A $tip_pro.\n";
    }
    if ($permiso_blog !== (int)$user['permiso_blog']) {
        $updateFields['permiso_blog'] = $permiso_blog;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . 
            " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PERMISO BLOG DE {$user['permiso_blog']} A $permiso_blog.\n";
    }
    
    if ($permiso_biblioteca !== (int)$user['permiso_biblioteca']) {
        $updateFields['permiso_biblioteca'] = $permiso_biblioteca;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . 
            " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PERMISO BIBLIOTECA DE {$user['permiso_biblioteca']} A $permiso_biblioteca.\n";
    }
    
    if ($permiso_citas !== (int)$user['permiso_citas']) {
        $updateFields['permiso_citas'] = $permiso_citas;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . 
            " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PERMISO CITAS DE {$user['permiso_citas']} A $permiso_citas.\n";
    }
    
    if ($permiso_promociones !== (int)$user['permiso_promociones']) {
        $updateFields['permiso_promociones'] = $permiso_promociones;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . 
            " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PERMISO PROMOCIONES DE {$user['permiso_promociones']} A $permiso_promociones.\n";
    }
    
    if ($permiso_gastos !== (int)$user['permiso_gastos']) {
        $updateFields['permiso_gastos'] = $permiso_gastos;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . 
            " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PERMISO GASTOS DE {$user['permiso_gastos']} A $permiso_gastos.\n";
    }
    
    if ($permiso_citas_pagos !== (int)$user['permiso_citas_pagos']) {
        $updateFields['permiso_citas_pagos'] = $permiso_citas_pagos;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . 
            " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PERMISO CITAS Y PAGOS DE {$user['permiso_citas_pagos']} A $permiso_citas_pagos.\n";
    }
    

    // Para las columnas 'permiso' y 'estado', usamos valores legibles
    if ($permiso !== (int)$user['permiso'] && $permiso !== 0 && $_SESSION['numdoc'] === '1000693019') {
        $oldPermiso = $user['permiso']; 
        $newPermiso = $permiso; 
        $updateFields['permiso'] = $permiso;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL PERMISO DE $oldPermiso A $newPermiso.\n";
    }
    if ($estado !== (int)$user['estado'] && $_SESSION['numdoc'] === '1000693019') {
        $oldEstado = $user['estado']; 
        $newEstado = $estado; 
        $updateFields['estado'] = $estado;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ EL ESTADO DE $oldEstado A $newEstado.\n";
    }
    
    if ($descripcion !== '' && $descripcion !== $user['descripcion']) {
        $updateFields['descripcion'] = $descripcion;
        $changesLog .= "EL " . $fechaHora . " " . $_SESSION['pn_usu'] . " " . $_SESSION['pa_usu'] . " (USERID" . $_SESSION['id'] . ") ACTUALIZÓ LA DESCRIPCIÓN DE {$user['descripcion']} ______A______ $descripcion.\n";
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
            header('Location: usuarios?sta=' . urlencode($encryptedStatus));
            exit();
        } else {
            // Si hay un error, redirige con error
            $encryptedStatus = simpleEncrypt('error_update', '2020');
            header('Location: usuarios?sta=' . urlencode($encryptedStatus));
            exit();
        }
    } else {
        // Si no hay cambios, redirige sin hacer nada
        $encryptedStatus = simpleEncrypt('no_update', '2020');
        header('Location: usuarios?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
