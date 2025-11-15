<?php
session_start();
include "session_update.php";
actualizarSesion($conn);
if($_SESSION['permiso'] !== 1 && $_SESSION['permiso'] !== 99 && $_SESSION['permiso'] !== 3){
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
    if (isset($_POST['pais'], $_POST['td_usu'], $_POST['numdoc'], $_POST['pn_usu'], $_POST['sn_usu'], $_POST['pa_usu'], $_POST['sa_usu'], $_POST['tel_usu'], $_POST['cor_usu'], $_POST['fna_usu'], $_POST['per_usu'], $_POST['bold'], $_POST['estado'], $_POST['profesional_asignado'])) {
        var_dump($_POST);
    }
    $pais = $_POST['pais'];
    $tipdoc = $_POST['td_usu'];
    $numdoc = $_POST['numdoc'];
    $pn_usu = $_POST['pn_usu'];
    $sn_usu = $_POST['sn_usu'];
    $pa_usu = $_POST['pa_usu'];
    $sa_usu = $_POST['sa_usu'];
    $tidfac = isset($_POST['tidfac']) ? $_POST['tidfac'] : '';
    if($tidfac == ''){
        $tidfac = $tipdoc;
    }
    $idfact = isset($_POST['idfact']) ? $_POST['idfact'] : '';
    if($idfact == ''){
        $idfact = $numdoc;
    }
    $nomfac = isset($_POST['nomfac']) ? $_POST['nomfac'] : '';
    if ($nomfac == '') {
        $nomfac = implode(' ', array_filter([$pn_usu, $sn_usu, $pa_usu, $sa_usu]));
    }
    $tel_usu = $_POST['tel_usu'];
    $cor_usu = $_POST['cor_usu'];
    $cor_usu = preg_replace('/\s+/', '', $cor_usu);
    $fna_usu = (!empty($_POST['fna_usu'])) ? $_POST['fna_usu'] : null;
    $permiso = $_POST['per_usu'];
    $bold = $_POST['bold'];
    $estado = $_POST['estado'];
    $profesional_asignado = $_POST['profesional_asignado'];
    $proceso = 1;
    $valor_base = !empty($_POST['valor_base']) ? $_POST['valor_base'] : 0;
    $valor_pres = !empty($_POST['valor_pres']) ? $_POST['valor_pres'] : 0;
    $profesion = $_POST['profesion'];
    $sector = $_POST['sector'];
    $money = $_POST['money'];

    // Verificar si ya existe un usuario con los mismos datos
    $sqlCheck = "SELECT id FROM usuarios WHERE pais = ? AND tipdoc = ? AND numdoc = ? AND cor_usu = ?";
    $stmtCheck = $conn->prepare($sqlCheck);
    $stmtCheck->bind_param('ssss', $pais, $tipdoc, $numdoc, $cor_usu);
    $stmtCheck->execute();
    $stmtCheck->store_result();

    if ($stmtCheck->num_rows > 0) {
        $stmtCheck->close();
        $encryptedStatus = simpleEncrypt('repetido', '2020');
        header('Location: users_pac?sta=' . urlencode($encryptedStatus));
        exit();
    }
    $stmtCheck->close();

    // Ruta al archivo JSON
    $jsonFile = 'hisclinic.json';
    $data = json_decode(file_get_contents($jsonFile), true);
    $hisclinica = "SM" . $data['autoincremental'];
    $data['autoincremental'] += 1;
    file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

    $colombiano = "";
    if($pais === "343"){
        $colombiano = 1;
    }

    // SQL corregido: Agregado campo faltante y corrección en nombre
    $sql = "INSERT INTO usuarios (pais, tipdoc, numdoc, pn_usu, sn_usu, pa_usu, sa_usu, tel_usu, cor_usu, born_date, permiso, bold, estado, clave_sys, profesional_asignado, proceso, hiscli, valor_base, valor_pres, colombian, profession, sector, currency, tidfac, idfact, nomfac) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql);

    // Corrección en los valores enlazados: todos los campos deben coincidir
    $stmt->bind_param('ssssssssssssssiisiiiiiisss', $pais, $tipdoc, $numdoc, $pn_usu, $sn_usu, $pa_usu, $sa_usu, $tel_usu, $cor_usu, $fna_usu, $permiso, $bold, $estado, $numdoc, $profesional_asignado, $proceso, $hisclinica, $valor_base, $valor_pres, $colombiano, $profesion, $sector, $money, $tidfac, $idfact, $nomfac);

    if ($stmt->execute()) {
        header('Location: mails/welcome?name='.$pn_usu.' '.$sn_usu.'&correo='.$cor_usu);
        exit();
    } else {
        $encryptedStatus = simpleEncrypt('error_create', '2020');
        header('Location: users_pac?sta=' . urlencode($encryptedStatus));
        exit();
    }
}
?>
