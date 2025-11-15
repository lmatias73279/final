<?php
header('Content-Type: application/json');
include "../../conexionsm.php";

$year = isset($_GET['year']) ? intval($_GET['year']) : null;
$month = isset($_GET['month']) ? intval($_GET['month']) : null;
$day = isset($_GET['day']) ? intval($_GET['day']) : null;

$patients = 0;
$sessions = 0;
$ownIncome = 0;
$totalPayment = 0;

// Construcción de la consulta base para sesiones y pacientes
$whereClauses = [];
$params = [];

if ($year) {
    $whereClauses[] = "YEAR(fecha) = ?";
    $params[] = $year;
}
if ($month) {
    $whereClauses[] = "MONTH(fecha) = ?";
    $params[] = $month;
}
if ($day) {
    $whereClauses[] = "DAY(fecha) = ?";
    $params[] = $day;
}

if (empty($whereClauses)) {
    $whereClauses[] = "YEAR(fecha) >= YEAR(CURDATE()) - 4";
}

$whereSQL = implode(" AND ", $whereClauses);

// Consulta para sesiones y pacientes únicos
$query = "SELECT COUNT(*) as sesiones, COUNT(DISTINCT userID) as pacientes FROM sessions WHERE $whereSQL";
$stmt = $conn->prepare($query);
if (!empty($params)) {
    $stmt->bind_param(str_repeat('i', count($params)), ...$params);
}
$stmt->execute();
$stmt->bind_result($sessions, $patients);
$stmt->fetch();
$stmt->close();

// Consulta para ingresos y pagos usando use_date
$dateColumn = "use_date";
$whereClausesIncome = [];
$paramsIncome = [];

if ($year) {
    $whereClausesIncome[] = "YEAR($dateColumn) = ?";
    $paramsIncome[] = $year;
}
if ($month) {
    $whereClausesIncome[] = "MONTH($dateColumn) = ?";
    $paramsIncome[] = $month;
}
if ($day) {
    $whereClausesIncome[] = "DAY($dateColumn) = ?";
    $paramsIncome[] = $day;
}

if (empty($whereClausesIncome)) {
    $whereClausesIncome[] = "YEAR($dateColumn) >= YEAR(CURDATE()) - 4";
}

$whereSQLIncome = implode(" AND ", $whereClausesIncome);

$queryIncome = "SELECT SUM(ingresoPROPIO) as ownIncome, SUM(valor) as totalPayment FROM sessions WHERE $whereSQLIncome";
$stmtIncome = $conn->prepare($queryIncome);
if (!empty($paramsIncome)) {
    $stmtIncome->bind_param(str_repeat('i', count($paramsIncome)), ...$paramsIncome);
}
$stmtIncome->execute();
$stmtIncome->bind_result($ownIncome, $totalPayment);
$stmtIncome->fetch();
$stmtIncome->close();

// Datos para la gráfica
$labels = [];
$data = [];

if (!$year) {
    $currentYear = date("Y");
    for ($i = 4; $i >= 0; $i--) {
        $yearToQuery = $currentYear - $i;
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM sessions WHERE YEAR(fecha) = ?");
        $stmt->bind_param("i", $yearToQuery);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $labels[] = $yearToQuery;
        $data[] = $row['total'] ?? 0;
    }
} elseif ($year && !$month) {
    for ($i = 1; $i <= 12; $i++) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM sessions WHERE YEAR(fecha) = ? AND MONTH(fecha) = ?");
        $stmt->bind_param("ii", $year, $i);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $labels[] = date("F", mktime(0, 0, 0, $i, 1));
        $data[] = $row['total'] ?? 0;
    }
} elseif ($year && $month && !$day) {
    $daysInMonth = cal_days_in_month(CAL_GREGORIAN, $month, $year);
    for ($i = 1; $i <= $daysInMonth; $i++) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM sessions WHERE YEAR(fecha) = ? AND MONTH(fecha) = ? AND DAY(fecha) = ?");
        $stmt->bind_param("iii", $year, $month, $i);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $labels[] = $i;
        $data[] = $row['total'] ?? 0;
    }
} elseif ($year && $month && $day) {
    for ($i = 0; $i < 24; $i++) {
        $stmt = $conn->prepare("SELECT COUNT(*) as total FROM sessions WHERE YEAR(fecha) = ? AND MONTH(fecha) = ? AND DAY(fecha) = ? AND HOUR(hora) = ?");
        $stmt->bind_param("iiii", $year, $month, $day, $i);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $labels[] = sprintf("%02d:00", $i);
        $data[] = $row['total'] ?? 0;
    }
}

// Consulta para psicólogos activos
$queryPsychologists = "SELECT COUNT(*) FROM usuarios WHERE permiso = 3 AND estado = 1";
$stmtPsychologists = $conn->prepare($queryPsychologists);
$stmtPsychologists->execute();
$stmtPsychologists->bind_result($psychologists);
$stmtPsychologists->fetch();
$stmtPsychologists->close();

$response = [
    'ownIncome' => $ownIncome ?: 0,
    'totalPayment' => $totalPayment ?: 0,
    'psychologists' => $psychologists ?: 0,
    'patients' => $patients ?: 0,
    'sessions' => $sessions ?: 0,
    'chart' => [
        'labels' => $labels,
        'data' => $data
    ]
];

echo json_encode($response);
?>
