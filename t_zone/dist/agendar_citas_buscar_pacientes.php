<?php
include "../../conexionsm.php"; // Incluir la conexión a la base de datos

// Obtener los datos enviados por AJAX
$data = json_decode(file_get_contents("php://input"), true);
$hiscli = $data['hiscli'] ?? null;
$num_documento = $data['num_documento'] ?? null;

// Inicializar la consulta y los parámetros
$query = "SELECT id, born_date, colombian, activity, profession, sector, currency, discount, pn_usu, sn_usu, pa_usu, sa_usu, profesional_asignado FROM usuarios WHERE ";
$params = [];

// Verificar cuál condición se debe usar en la consulta
if (!empty($hiscli)) {
    $query .= "hiscli = ?";
    $params[] = $hiscli;
} elseif (!empty($num_documento)) {
    $query .= "numdoc = ?";
    $params[] = $num_documento;
} else {
    // Si neither hiscli nor num_documento is provided, you can return an error or empty response
    echo json_encode(null);
    exit;
}

// Preparar la consulta
$stmt = $conn->prepare($query);

// Vincular parámetros dependiendo de cuál se esté usando
if (!empty($hiscli)) {
    $stmt->bind_param('s', $params[0]); // 's' para string
} elseif (!empty($num_documento)) {
    $stmt->bind_param('s', $params[0]); // 's' para string
}

// Ejecutar la consulta
$stmt->execute();

// Obtener el resultado
$result = $stmt->get_result()->fetch_assoc();

if ($result) {
    // Concatenar nombres del paciente
    $nombre_paciente = trim($result['pn_usu'] . ' ' . $result['sn_usu'] . ' ' . $result['pa_usu'] . ' ' . $result['sa_usu']);
    
    // Obtener el nombre del profesional asignado
    $born_date = !empty($result['born_date']) ? $result['born_date'] : '0000-00-00';
    $colombian = $result['colombian'];
    $activity = $result['activity'];
    $profession = $result['profession'];
    $sector = $result['sector'];
    $currency = $result['currency'];
    $sector = $result['sector'];
    date_default_timezone_set('America/Bogota');
    $fechaActual = date('Y-m-d');
    if ($born_date) {
        // Obtener el año actual
        $currentYear = date('Y');
        $currentMonth = date('m');
        $currentDay = date('d');
        
        // Separar el año, mes y día de la fecha de nacimiento
        list($year, $month, $day) = explode('-', $born_date);
        
        // Calcular la edad
        $age = $currentYear - $year;
        if ($currentMonth < $month || ($currentMonth == $month && $currentDay < $day)) {
            $age--; // Ajustar si el cumpleaños aún no ha ocurrido este año
        }
    
        // Determinar el rango de edad
        if ($age >= 18 && $age <= 25) {
            $old_range = 1;
        } elseif ($age >= 26 && $age <= 35) {
            $old_range = 2;
        } elseif ($age >= 36 && $age <= 50) {
            $old_range = 3;
        } elseif ($age > 50) {
            $old_range = 4;
        } else {
            $old_range = 4; // Edad fuera de rango (menor de 18)
        }
    }
    $sqlcost = "SELECT price FROM pricing WHERE old_range = $old_range AND colombian = $colombian AND activity = $activity AND profession = $profession AND sector = $sector";
    $row1 = $conn->query($sqlcost)->fetch_assoc();
    if (isset($row1['price'])) {
        $valor = $row1['price'];
    } else {
        $valor = ""; // O cualquier valor por defecto que desees asignar cuando no existe
    }
    

    $profesional_id = $result['profesional_asignado'];
    $id_paciente = $result['id'];
    $query_profesional = "SELECT pn_usu, sn_usu, pa_usu, sa_usu FROM usuarios WHERE id = ?";
    $stmt_profesional = $conn->prepare($query_profesional);
    $stmt_profesional->bind_param('i', $profesional_id); // 'i' para integer
    $stmt_profesional->execute();
    $result_profesional = $stmt_profesional->get_result()->fetch_assoc();

    // Preparar la respuesta
    $response = [
        'nombre_paciente' => $nombre_paciente,
        'profesional_asignado' => $result_profesional ? ($result_profesional['pn_usu']." ".$result_profesional['sn_usu']." ".$result_profesional['pa_usu']." ".$result_profesional['sa_usu']) : null,
        'id_profesional' => $profesional_id,
        'id_paciente' => $id_paciente,
        'valor' => $valor,
        'currency' => $currency
    ];

    // Devolver los datos en formato JSON
    echo json_encode($response);
} else {
    echo json_encode(null); // No se encontró información
}
?>
