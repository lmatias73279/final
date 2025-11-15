<?php
// Conexión a la base de datos
include "../../conexionsm.php";

function simpleEncrypt($text, $key) {
  $output = '';
  for ($i = 0; $i < strlen($text); $i++) {
      $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
  }
  return base64_encode($output); // Convertir a base64 para URL seguro
}

// Verificar conexión
if ($conn->connect_error) {
    die("Error de conexión: " . $conn->connect_error);
}

// Obtener el ID desde la URL y sanitizarlo
$id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($id > 0) {
    // Actualizar la alerta (cambiar 'del' a 1)
    $sql = "UPDATE alertas SET del = 1 WHERE ID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $id);

    if ($stmt->execute()) {
      $encryptedStatus = simpleEncrypt('alertelmext', '2020');
      header('Location: alertas?sta=' . urlencode($encryptedStatus));
      exit();
    } else {
      $encryptedStatus = simpleEncrypt('erralertelmext', '2020');
      header('Location: alertas?sta=' . urlencode($encryptedStatus));
      exit();
    }

    $stmt->close();
} else {
  $encryptedStatus = simpleEncrypt('delalernoid', '2020');
  header('Location: alertas?sta=' . urlencode($encryptedStatus));
  exit();
}

// Cerrar conexión
$conn->close();
?>
