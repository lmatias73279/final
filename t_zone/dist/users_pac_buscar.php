<?php 
    // Función para encriptar los datos
    function simpleEncrypt($text, $key) {
        $text = (string) $text; // Asegurar que sea un string
        $output = '';
        for ($i = 0; $i < strlen($text); $i++) {
            $output .= chr(ord($text[$i]) ^ ord($key[$i % strlen($key)]));
        }
        return base64_encode($output); // Convertir a base64 para URL seguro
    }

    // Obtener y limpiar el filtro
    $filtro = trim($_POST['searchInput'] ?? '');

    // Encriptar el valor del filtro
    $encryptedFiltro = simpleEncrypt($filtro, '2020');

    // Redirigir a users_pac con el valor encriptado
    header('Location: users_pac?flt=' . urlencode($encryptedFiltro));
    exit();
?>