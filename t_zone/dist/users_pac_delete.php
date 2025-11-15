<?php
require_once __DIR__ . '/../../conexionsm.php'; // conexión mysqli

function simpleEncrypt($text, $key) {
    $output = '';
    for ($i = 0; $i < strlen($text); $i++) {
        $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
    }
    return base64_encode($output); // para URL segura
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id'])) {
    $id = (int) $_POST['id']; // seguridad

    $stmt = $conn->prepare("UPDATE usuarios SET delest = 9 WHERE id = ?");
    if ($stmt === false) {
        $encryptedError = simpleEncrypt('prepare_error', '2020');
        header("Location: users_pac?sta=" . urlencode($encryptedError));
        exit;
    }

    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
        if ($stmt->affected_rows > 0) {
            $encryptedSuccess = simpleEncrypt('deleted_ok', '2020');
            header("Location: users_pac?sta=" . urlencode($encryptedSuccess));
        } else {
            $encryptedNoChange = simpleEncrypt('no_update_delete', '2020');
            header("Location: users_pac?sta=" . urlencode($encryptedNoChange));
        }
    } else {
        $encryptedError = simpleEncrypt('execute_error', '2020');
        header("Location: users_pac?sta=" . urlencode($encryptedError));
    }

    $stmt->close();
    $conn->close();
    exit;
} else {
    $encryptedMissing = simpleEncrypt('missing_id', '2020');
    header("Location: users_pac?sta=" . urlencode($encryptedMissing));
    exit;
}
