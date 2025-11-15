<?php
// Configuración básica
$api_key = "e6821d366be5394b098e4e69508b023b";
$internal_id = "358eb03e-d0f8-40f4-86b6-158eeb577ae5";

// Lista de contactos de prueba
$usuarios = [
    ["nombre" => "🖤", "telefono" => "573241149729"],
    ["nombre" => "🖤", "telefono" => "573236600769"],
    ["nombre" => "Diego", "telefono" => "18095433088"],
    ["nombre" => "🖤", "telefono" => "593990010708"],
    ["nombre" => "Valentina", "telefono" => "573183520987"],
    ["nombre" => "Lucas", "telefono" => "5492963401252"],
    ["nombre" => "Jennyfer", "telefono" => "573156480695"],
    ["nombre" => "Yannyn", "telefono" => "56967765751"],
    ["nombre" => "Alejandro", "telefono" => "584242586564"],
    ["nombre" => "Laura", "telefono" => "4917684581088"],
    ["nombre" => "Pedro", "telefono" => "573168792707"],
    ["nombre" => "🖤", "telefono" => "573011500527"],
    ["nombre" => "Dassy", "telefono" => "573138891192"],
    ["nombre" => "Nayeli", "telefono" => "13055257844"],
    ["nombre" => "Iraima", "telefono" => "584143193747"],
    ["nombre" => "Ana", "telefono" => "5216862340212"],
    ["nombre" => "Sergio", "telefono" => "34640946805"],
    ["nombre" => "Maria", "telefono" => "573203774202"],
    ["nombre" => "Nataly", "telefono" => "573504335815"],
    ["nombre" => "Alex", "telefono" => "5213318104972"],
    ["nombre" => "🖤", "telefono" => "34613591243"],
    ["nombre" => "Juls", "telefono" => "5212871315570"],
    ["nombre" => "Alan", "telefono" => "5491127103582"],
    ["nombre" => "Angélica", "telefono" => "573223343702"],
    ["nombre" => "Serena", "telefono" => "584242387293"],
    ["nombre" => "Ale", "telefono" => "56985213058"],
    ["nombre" => "Mon", "telefono" => "5215550993349"],
    ["nombre" => "Rochi", "telefono" => "34603968861"],
    ["nombre" => "Jessy", "telefono" => "51941750612"],
    ["nombre" => "🖤", "telefono" => "573017784520"],
    ["nombre" => "Joha", "telefono" => "573208525698"],
    ["nombre" => "🖤", "telefono" => "5492926544449"],
    ["nombre" => "Jihad", "telefono" => "13464206337"],
    ["nombre" => "🖤", "telefono" => "573104815277"],
    ["nombre" => "Aldo", "telefono" => "5215536446873"],
    ["nombre" => "Magui", "telefono" => "573182289316"],
    ["nombre" => "Sophia", "telefono" => "573174449798"],
    ["nombre" => "🖤", "telefono" => "5491138192946"],
    ["nombre" => "Jennifer", "telefono" => "573123290286"],
    ["nombre" => "🖤", "telefono" => "573103566592"],
    ["nombre" => "Mijal", "telefono" => "56944367142"],
    ["nombre" => "Karen", "telefono" => "573183327838"],
    ["nombre" => "Aura", "telefono" => "573104795760"],
    ["nombre" => "Felipe Matias", "telefono" => "573183496907"],
    ["nombre" => "Ana Rodríguez", "telefono" => "573150732799"]
];

// Función para capitalizar correctamente
function capitalizar_palabras_utf8($texto) {
    $palabras = explode(' ', $texto);
    foreach ($palabras as &$palabra) {
        $primera = mb_substr($palabra, 0, 1, 'UTF-8');
        $resto = mb_substr($palabra, 1, null, 'UTF-8');
        $palabra = mb_strtoupper($primera, 'UTF-8') . $resto;
    }
    return implode(' ', $palabras);
}

// Enviar a cada usuario
foreach ($usuarios as $usuario) {
    $nombre = capitalizar_palabras_utf8(mb_strtolower($usuario['nombre'], 'UTF-8'));
    $telefono = $usuario['telefono'];

    // Si tu plantilla solo requiere el nombre, se manda así:
    $payload = [
        "phone_number" => $telefono,
        "internal_id" => $internal_id,
        "template_params" => [$nombre] // solo el nombre
    ];

    // Inicializar cURL
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

    echo "<b>Enviando a:</b> $nombre ($telefono)<br>";

    if ($error) {
        echo "❌ Error: $error<br><br>";
    } else {
        echo "✅ Respuesta: $response<br><br>";
    }

    // Pausa corta entre envíos
    sleep(1);
}
?>
