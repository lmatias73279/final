<?php

include  __DIR__ .  "/../../../../conexionsm.php";

date_default_timezone_set('America/Bogota'); // Establecer zona horaria de Colombia

$fechaActual = date('Y-m-d', strtotime('+3 days')); // Fecha actual + 2 días
$minuto = date('i');
$hora = date('H');
if ($minuto >= 30) {
    $hora = (int)$hora + 1; // Sube a la siguiente hora si pasa de 30 minutos
}
$horaCerrada = str_pad($hora, 2, '0', STR_PAD_LEFT) . ':00:00';

$sql = "SELECT fecha, hora, link_ingreso, psi, userID, tipo, `site`
        FROM sessions 
        WHERE fecha = ? AND estado < 5";

$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $fechaActual);
$stmt->execute();
$result = $stmt->get_result();

// Función personalizada para poner en mayúscula la primera letra de cada palabra (UTF-8 safe)
function capitalizar_palabras_utf8($texto) {
    $palabras = explode(' ', $texto);
    foreach ($palabras as &$palabra) {
        $primera = mb_substr($palabra, 0, 1, 'UTF-8');
        $resto = mb_substr($palabra, 1, null, 'UTF-8');
        $palabra = mb_strtoupper($primera, 'UTF-8') . $resto;
    }
    return implode(' ', $palabras);
}

while ($row = $result->fetch_assoc()) {
    $fecha = $row['fecha'];
    $fecha = date('d-m-Y', strtotime($fecha));
    $hora = $row['hora'];
    $hora = date("h:i A", strtotime($hora));
    $link = $row['link_ingreso'];
    $profesional_id = $row['psi'];

    
    $tipo_terapia = $row['tipo'];
    $tipos_terapia = [
        "1" => "Terapia Individual",
        "2" => "Terapia de Pareja",
        "5" => "Terapia de Familia",
        "6" => "Terapia Psiquiátrica",
        "7" => "Valoración",
        "8" => "Terapia Nutrición",
        "9" => "Terapia Infantil"
    ];
    $tipo_terapia = isset($tipos_terapia[$tipo_terapia]) ? $tipos_terapia[$tipo_terapia] : "Tipo de terapia desconocido";
    $modalidad = $row['site'];

    $sqlp = "SELECT pn_usu, sn_usu, pa_usu, sa_usu, cor_usu FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sqlp);
    $stmt->bind_param("i", $profesional_id);
    $stmt->execute();
    $resultp = $stmt->get_result();

    if ($rowp = $resultp->fetch_assoc()) {
      // Concatenar los nombres y apellidos
      $nombre_completo = trim($rowp['pn_usu'] . ' ' . $rowp['sn_usu'] . ' ' . $rowp['pa_usu'] . ' ' . $rowp['sa_usu']);

      // Quitar espacios duplicados
      $nombre_completo = preg_replace('/\s+/', ' ', $nombre_completo);

      // Convertir todo a minúsculas correctamente (UTF-8 seguro)
      $nombre_completo = mb_strtolower($nombre_completo, 'UTF-8');

      $nombre_completo = capitalizar_palabras_utf8($nombre_completo);

      // Guardar en la variable $profesional
      $profesional = $nombre_completo;
    } else {
        $profesional = "Profesional no encontrado";
    }

    $corpro = $rowp['cor_usu'];

    $userID = $row['userID'];


    $sqlu = "SELECT pn_usu, sn_usu, pa_usu, sa_usu, cor_usu, tel_usu, pais FROM usuarios WHERE id = ?";
    $stmt = $conn->prepare($sqlu);
    $stmt->bind_param("i", $userID);
    $stmt->execute();
    $resultu = $stmt->get_result();

    if ($rowu = $resultu->fetch_assoc()) {
      // Concatenar los nombres y apellidos
      $nombre_completou = trim($rowu['pn_usu'] . ' ' . $rowu['sn_usu']);

      // Quitar espacios duplicados
      $nombre_completou = preg_replace('/\s+/', ' ', $nombre_completou);

      // Convertir todo a minúsculas correctamente (UTF-8 seguro)
      $nombre_completou = mb_strtolower($nombre_completou, 'UTF-8');

      $nombre_completou = capitalizar_palabras_utf8($nombre_completou);

      // Guardar en la variable $profesional
      $nombre = $nombre_completou;
    } else {
        $nombre = "Profesional no encontrado";
    }

    
    if($modalidad === 1){
      $modalidad = "Presencial";
      $direccion = "Carrera 49a # 94 - 32";
    }else if($modalidad === 2){
      $modalidad = "Virtual";
      $direccion = $row['link_ingreso'];
    }else{
      $modalidad = "Desconocida";
      $direccion = "";
    }

    $correo = $rowu['cor_usu'];
    $telefono = $rowu['tel_usu'];

    $curl = curl_init();

    $pais = $rowu['pais'];
    $indicativo = "+57";

    $sqli = "SELECT indicativo FROM paises WHERE cod_pais = ?";
    $stmt = $conn->prepare($sqli);
    $stmt->bind_param("s", $pais);
    $stmt->execute();
    $resulti = $stmt->get_result();

    if ($rowi = $resulti->fetch_assoc()) {
        $indicativo = '+' . $rowi['indicativo'];
    }

    $payload = array(
      "phone_number"  => $indicativo.$telefono,
      "internal_id" => "4dc4a0ef-f7ba-41f0-aca7-74b08f256aa4",
      "template_params" => [$nombre_completou, $modalidad, $profesional, $fecha, $hora, $direccion]
    );

    curl_setopt_array($curl, [
      CURLOPT_HTTPHEADER => [
        "Content-Type: application/json",
        "api-key: e6821d366be5394b098e4e69508b023b"
      ],
      CURLOPT_POSTFIELDS => json_encode($payload),
      CURLOPT_URL => "https://app.mercately.com/retailers/api/v1/whatsapp/send_notification_by_id",
      CURLOPT_RETURNTRANSFER => true,
      CURLOPT_CUSTOMREQUEST => "POST",
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);

    curl_close($curl);

    if ($error) {
      echo "cURL Error #:" . $error;
      continue;
    } else {
      echo $response;
    }

}