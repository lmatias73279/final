<?php
include __DIR__ . "/../../../../conexionsm.php"; // tu conexión

// ==============================
// 🔧 CONFIGURACIÓN
// ==============================
$api_key = "e6821d366be5394b098e4e69508b023b";
$internal_id = "2ddf11bf-aa62-4f7d-a9d5-9e75543aec12";

// Número de lote (👈 aquí cambias manualmente: 1, 2, 3, etc.)
$lote = 6;

// Tamaño de cada lote
$tamano_lote = 500;

// Cálculo del OFFSET (desde dónde empezar)
$offset = ($lote - 1) * $tamano_lote;

// ==============================
// 🔍 CONSULTA BASE DE DATOS
// ==============================
$sql = "SELECT tel_usu, pais 
        FROM usuarios 
        WHERE permiso = 9 
          AND tel_usu IS NOT NULL 
          AND tel_usu != ''
        LIMIT ? OFFSET ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $tamano_lote, $offset);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    die("⚠️ No se encontraron usuarios para el lote $lote.");
}

echo "<h3>📦 Enviando LOTE #$lote (máximo $tamano_lote usuarios)</h3><hr>";

// ==============================
// 🚀 ENVÍO DE MENSAJES
// ==============================
while ($row = $result->fetch_assoc()) {
    $telefono = preg_replace('/\D/', '', $row['tel_usu']); // limpiar número
    $pais = $row['pais'];

    // Buscar indicativo (por defecto +57)
    $indicativo = '+57';
    $stmt2 = $conn->prepare("SELECT indicativo FROM paises WHERE cod_pais = ?");
    $stmt2->bind_param("s", $pais);
    $stmt2->execute();
    $res2 = $stmt2->get_result();
    if ($rowi = $res2->fetch_assoc()) {
        $indicativo = '+' . $rowi['indicativo'];
    }

    $telefono_final = $indicativo . $telefono;

    // Crear payload sin variables
    $payload = [
        "phone_number" => $telefono_final,
        "internal_id" => $internal_id,
        "template_params" => [] // sin variables
    ];

    // Enviar mensaje
    $curl = curl_init();
    curl_setopt_array($curl, [
        CURLOPT_URL => "https://app.mercately.com/retailers/api/v1/whatsapp/send_notification_by_id",
        CURLOPT_RETURNTRANSFER => true,
        CURLOPT_CUSTOMREQUEST => "POST",
        CURLOPT_HTTPHEADER => [
            "Content-Type: application/json",
            "api-key: $api_key"
        ],
        CURLOPT_POSTFIELDS => json_encode($payload),
    ]);

    $response = curl_exec($curl);
    $error = curl_error($curl);
    curl_close($curl);

    echo "<b>Enviando a:</b> $telefono_final<br>";
    if ($error) {
        echo "❌ Error: $error<br><br>";
    } else {
        echo "✅ Respuesta: $response<br><br>";
    }

    // Esperar un segundo entre envíos (opcional)
    sleep(1);
}

echo "<hr><b>✅ Lote $lote completado.</b>";
?>
