<?php
session_start();
if (empty($_SESSION["id"])) {
  header("location: login");
}
header('Content-Type: application/json');

$q = intval($_GET['creditos']);
$site = intval($_GET['site']);
$ttera = intval($_GET['tipoTerapia']);
$codpromo = isset($_GET['codpromo']) ? $_GET['codpromo'] : '';

// Configuración de la clave secreta
$secretKey = "gVPkY2ACSJRcvFSyTVnmRA";

// Función para generar un ID único
function generateOrderId() {
    return uniqid("ORDER_");
}

// Función para generar el hash de integridad
function generateHash($orderId, $amount, $currency, $secretKey) {
    $stringToHash = "{$orderId}{$amount}{$currency}{$secretKey}";
    return hash('sha256', $stringToHash);
}

include "../../../../conexionsm.php";
$id = $_SESSION["id"];
$sqlvalue = "SELECT born_date, colombian, activity, profession, sector, currency, discount FROM usuarios WHERE id = $id";
$row = $conn->query($sqlvalue)->fetch_assoc();
$born_date = $row['born_date'];
$old_range = 0;
if ($born_date && $born_date !== '0000-00-00') {
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
        $old_range = null; // Edad fuera de rango (menor de 18)
    }
}
$colombian = $row['colombian'];
$activity = $row['activity'];
$profession = $row['profession'];
$sector = $row['sector'];
$currency = $row['currency'];
$discount = $row['discount'];

$sqlcost = "SELECT price FROM pricing WHERE old_range = $old_range AND colombian = $colombian AND activity = $activity AND profession = $profession AND sector = $sector";
$row1 = $conn->query($sqlcost)->fetch_assoc();
$cost = $row1['price'];

$user_id = $_SESSION['id'];
$sql = "SELECT valor_base FROM usuarios WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();
$valor_base = $row ? (int)$row['valor_base'] : 0;

if($ttera === 1){
    if($site === 1){
        if($cost <= 85000){
            $cost = 90000;
        }else{
            $cost = $cost + 5000;
        }
    }
}else if($ttera === 2){
    if($cost <= 80000){
        if($site === 1){
            $cost = 140000;
        }else if($site === 2){
            $cost = 130000;
        }
    }else if($cost === 85000){
        if($site === 1){
            $cost = 145000;
        }else if($site === 2){
            $cost = 140000;
        }
    }else if($cost <= 90000){
        if($site === 1){
            $cost = 155000;
        }else if($site === 2){
            $cost = 150000;
        }
    }else if($cost <= 95000){
        if($site === 1){
            $cost = 160000;
        }else if($site === 2){
            $cost = 155000;
        }
    }else if($cost >= 10000){
        if($site === 1){
            $cost = 165000;
        }else if($site === 2){
            $cost = 160000;
        }
    }
}else if($ttera === 3){
    if($cost <= 80000){
        if($site === 1){
            $cost = 155000;
        }else if($site === 2){
            $cost = 150000;
        }
    }else if($cost === 85000){
        if($site === 1){
            $cost = 165000;
        }else if($site === 2){
            $cost = 160000;
        }
    }else if($cost <= 90000){
        if($site === 1){
            $cost = 175000;
        }else if($site === 2){
            $cost = 170000;
        }
    }else if($cost <= 95000){
        if($site === 1){
            $cost = 185000;
        }else if($site === 2){
            $cost = 180000;
        }
    }else if($cost >= 10000){
        if($site === 1){
            $cost = 195000;
        }else if($site === 2){
            $cost = 190000;
        }
    }
}else if($ttera === 4){
    if($cost <= 80000){
        if($site === 1){
            $cost = 165000;
        }else if($site === 2){
            $cost = 160000;
        }
    }else if($cost === 85000){
        if($site === 1){
            $cost = 175000;
        }else if($site === 2){
            $cost = 170000;
        }
    }else if($cost <= 90000){
        if($site === 1){
            $cost = 185000;
        }else if($site === 2){
            $cost = 180000;
        }
    }else if($cost <= 95000){
        if($site === 1){
            $cost = 195000;
        }else if($site === 2){
            $cost = 190000;
        }
    }else if($cost >= 10000){
        if($site === 1){
            $cost = 200000;
        }else if($site === 2){
            $cost = 195000;
        }
    }
}else if($ttera === 5){
    if($cost <= 80000){
        if($site === 1){
            $cost = 175000;
        }else if($site === 2){
            $cost = 170000;
        }
    }else if($cost === 85000){
        if($site === 1){
            $cost = 185000;
        }else if($site === 2){
            $cost = 180000;
        }
    }else if($cost <= 90000){
        if($site === 1){
            $cost = 195000;
        }else if($site === 2){
            $cost = 190000;
        }
    }else if($cost <= 95000){
        if($site === 1){
            $cost = 205000;
        }else if($site === 2){
            $cost = 200000;
        }
    }else if($cost >= 10000){
        if($site === 1){
            $cost = 210000;
        }else if($site === 2){
            $cost = 205000;
        }
    }
}else if($ttera === 6){
    if($cost <= 80000){
        if($site === 1){
            $cost = 160000;
        }else if($site === 2){
            $cost = 150000;
        }
    }else if($cost === 85000){
        if($site === 1){
            $cost = 175000;
        }else if($site === 2){
            $cost = 160000;
        }
    }else if($cost <= 90000){
        if($site === 1){
            $cost = 180000;
        }else if($site === 2){
            $cost = 165000;
        }
    }else if($cost <= 95000){
        if($site === 1){
            $cost = 185000;
        }else if($site === 2){
            $cost = 170000;
        }
    }else if($cost === 10000){
        if($site === 1){
            $cost = 195000;
        }else if($site === 2){
            $cost = 180000;
        }
    }else if($cost === 10500){
        if($site === 1){
            $cost = 200000;
        }else if($site === 2){
            $cost = 185000;
        }
    }else if($cost >= 11000){
        if($site === 1){
            $cost = 210000;
        }else if($site === 2){
            $cost = 195000;
        }
    }
}

if($valor_base !== 0){
    $cost = $valor_base;
}

$cost = $cost * $q;

if($q === 3 || $q === 4 || $q === 5){
    $descuento = $cost * 5 / 100;
    $cost = $cost - $descuento;
}else if($q === 6 || $q === 7 || $q === 8){
    $descuento = $cost * 8 / 100;
    $cost = $cost - $descuento;
}else if($q === 10 || $q === 9){
    $descuento = $cost * 10 / 100;
    $cost = $cost - $descuento;
}


if (!empty($codpromo)) {
    $stmt = $conn->prepare("SELECT desde, hasta, descuento FROM promos WHERE codigo = ?");
    $stmt->bind_param("i", $codpromo);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();

        $desde = new DateTime($row['desde']);
        $hasta = new DateTime($row['hasta']);
        $hoy = new DateTime('now', new DateTimeZone('America/Bogota'));

        if ($hoy >= $desde && $hoy <= $hasta) {
            $descuentopromo = floatval($row['descuento']);
            $descpromo = $cost * $descuentopromo /100;
            $cost = $cost - $descpromo;
        }
    }

    $stmt->close();
}


$currency = intval($currency);
if ($currency === 1) {
    $currency = (string)"COP";
} else if ($currency === 2) {
    $currency = (string)"USD";
}


// Generar datos dinámicos
$orderId = generateOrderId();
$amount = $cost;
$currency = $currency;
$hash = generateHash($orderId, $amount, $currency, $secretKey);

// Devuelve los datos como JSON
echo json_encode([
    "orderId" => $orderId,
    "amount" => $amount,
    "currency" => $currency,
    "hash" => $hash,
]);
?>
