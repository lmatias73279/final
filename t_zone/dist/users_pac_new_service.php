<?php
include "../../conexionsm.php";

$id_paciente = $_POST['id'];

// Primero, verificar si ya existe un usuario con ese id_paciente
$sql_check = "SELECT * FROM usuarios WHERE ID = ?";
$stmt_check = $conn->prepare($sql_check);
$stmt_check->bind_param("i", $id_paciente);
$stmt_check->execute();
$result = $stmt_check->get_result();

// Obtener directamente la fila como un array asociativo
$row = $result->fetch_assoc();
$pais = $row['pais'];
$tipdoc = $row['tipdoc'];
$numdoc = $row['numdoc'];
$pn_usu = $row['pn_usu'];
$sn_usu = $row['sn_usu'];
$pa_usu = $row['pa_usu'];
$sa_usu = $row['sa_usu'];
$tel_usu = $row['tel_usu'];
$cor_usu = $row['cor_usu'];

// Ruta al archivo JSON
$jsonFile = 'hisclinic.json';
$data = json_decode(file_get_contents($jsonFile), true);
$hiscli = "SM" . $data['autoincremental'];
$data['autoincremental'] += 1;
file_put_contents($jsonFile, json_encode($data, JSON_PRETTY_PRINT));

$permiso = $row['permiso'];
$estado = $row['estado'];
$clave_sys = 'qSbALBYlG0Wo8rAVl3Z690wE';

$profesional_asignado = $_POST['profesional_asignado'];
$proceso = $_POST['new_process'];
$born_date = $row['born_date'];
$colombian = $row['colombian'];
$activity = $row['activity'];
$profession = $row['profession'];
$sector = $row['sector'];
$currency = $row['currency'];
$discount = $row['discount'];
$valor_base = $_POST['valns'];
$valor_pres = $_POST['valpe'];

// No existe, insertamos en usuarios
$sql = "INSERT INTO usuarios (pais, tipdoc, numdoc, pn_usu, sn_usu, pa_usu, sa_usu, tel_usu, cor_usu, hiscli, permiso, estado, clave_sys, proceso, born_date, colombian, activity, profession, sector, currency, discount, valor_base, valor_pres, profesional_asignado) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("isssssssssiisisiiiiiiiii", $pais, $tipdoc, $numdoc, $pn_usu, $sn_usu, $pa_usu, $sa_usu, $tel_usu, $cor_usu, $hiscli, $permiso, $estado, $clave_sys, $proceso, $born_date, $colombian, $activity, $profession, $sector, $currency, $discount, $valor_base, $valor_pres, $profesional_asignado);
$stmt->execute();
$id_usuario = $stmt->insert_id;

// Si es Individual Infantil (acudiente)
if ($proceso == '9') {
    $tipdocp = $_POST['tipdoc'];
    $numdocp = $_POST['numdoc'];
    $nombrep = $_POST['nombre_completo'];
    $correop = $_POST['correo'];
    $telefonop = $_POST['telefono'];
    $consanguinidadp = $_POST['consanguinidad'];

    $sql_fam = "INSERT INTO familiares (tipdoc, numdoc, nombre, correo, telefono, consanguinidad, hiscli, proceso) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
    $stmt_fam = $conn->prepare($sql_fam);
    $stmt_fam->bind_param("sssssisi", $tipdocp, $numdocp, $nombrep, $correop, $telefonop, $consanguinidadp, $hiscli, $proceso);
    $stmt_fam->execute();
}

// Si es proceso familiar (acompañantes)
if (in_array($proceso, ['2', '3', '4', '5'])) {
    $cantidad = intval($_POST['cantidad_acompanantes']);
    for ($i = 1; $i <= $cantidad; $i++) {
        $tipdocf = $_POST["acompanantes{$i}tipdoc"];
        $numdocf = $_POST["acompanantes{$i}numdoc"];
        $nombref = $_POST["acompanantes{$i}nombre"];
        $correof = $_POST["acompanantes{$i}correo"];
        $telefonof = $_POST["acompanantes{$i}telefono"];
        $consanguinidadf = $_POST["acompanantes{$i}consanguinidad"];

        $sql_fam = "INSERT INTO familiares (tipdoc, numdoc, nombre, correo, telefono, consanguinidad, hiscli, proceso) 
                    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";
        $stmt_fam = $conn->prepare($sql_fam);
        $stmt_fam->bind_param("sssssisi", $tipdocf, $numdocf, $nombref, $correof, $telefonof, $consanguinidadf, $hiscli, $proceso);
        $stmt_fam->execute();
    }
}

// Redirección final
function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // URL-safe encoding
}

$encryptedStatus = simpleEncrypt('okservices', '2020');
header('Location: users_pac?sta=' . urlencode($encryptedStatus));
exit;

?>
