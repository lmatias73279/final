<?php
require_once __DIR__ . '/../../conexionsm.php'; // conexión mysqli

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['reactivar_id']) && is_numeric($_POST['reactivar_id'])) {
        $id = intval($_POST['reactivar_id']);

        // 1. Consultar la fila en sessions por ID
        $sql = "SELECT `order` FROM sessions WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $resultado = $stmt->get_result();

        if ($resultado->num_rows === 1) {
            $fila = $resultado->fetch_assoc();
            $order = trim($fila['order']);

            // 2. Determinar el nuevo estado
            $nuevo_estado = ($order === '') ? 1 : 2;

            // 3. Actualizar el estado
            $update = "UPDATE sessions SET estado = ? WHERE id = ?";
            $stmt_update = $conn->prepare($update);
            $stmt_update->bind_param("ii", $nuevo_estado, $id);

            if ($stmt_update->execute()) {
                $mensaje = simpleEncrypt('reactivado_ok', '2020');
            } else {
                $mensaje = simpleEncrypt('error_update_react', '2020');
            }

        } else {
            $mensaje = simpleEncrypt('no_found', '2020');
        }

    } else {
        $mensaje = simpleEncrypt('id_invalido', '2020');
    }

    // 🟦 Redirigir al referer (URL previa), preservando los parámetros y actualizando sólo 'sta'
    $referer = $_SERVER['HTTP_REFERER'] ?? 'citas_tmrr';
    $url_parts = parse_url($referer);

    // Parsear query existente
    parse_str($url_parts['query'] ?? '', $query_params);

    // Reemplazar o agregar 'sta'
    $query_params['sta'] = $mensaje;

    // Reconstruir query y URL final
    $new_query = http_build_query($query_params);
    $new_url = $url_parts['path'] . '?' . $new_query;

    // Redirigir
    header('Location: ' . $new_url);
    exit();

} else {
    $mensaje = simpleEncrypt('metodo_invalido', '2020');
    header('Location: citas_tmrr?sta=' . urlencode($mensaje));
    exit();
}
