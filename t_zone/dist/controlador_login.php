<?php
// Incluir la conexión a la base de datos
include "../../conexionsm.php";

// Definir las variables de mensajes de error
$errorMessage = '';
$errorType = '';

if (!empty($_POST["btningresar"])) {
    if (!empty($_POST["user"]) && !empty($_POST["password"])) {
        $user = $_POST["user"];
        $password = $_POST["password"];
        
        // Configuración de conexión
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE cor_usu = ? LIMIT 1");
        $stmt->bind_param("s", $user);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($datos = $result->fetch_object()) {
            if ($datos->estado != 1) {
                // Usuario inactivo
                $errorMessage = 'Usuario inactivo';
                $errorType = 'loginac';  // Asigna un tipo de error
            } elseif (password_verify($password, $datos->clave_sys)) {
                // La contraseña ya está encriptada y el usuario está activo, inicia sesión
                $_SESSION["id"] = $datos->ID;
                $_SESSION["pais"] = $datos->pais;
                $_SESSION["tipdoc"] = $datos->tipdoc;
                $_SESSION["numdoc"] = $datos->numdoc;
                $_SESSION["pn_usu"] = $datos->pn_usu;
                $_SESSION["sn_usu"] = $datos->sn_usu;
                $_SESSION["pa_usu"] = $datos->pa_usu;
                $_SESSION["sa_usu"] = $datos->sa_usu;
                $_SESSION["cor_usu"] = $datos->cor_usu;
                $_SESSION["permiso"] = $datos->permiso;
                $_SESSION["profesional_asignado"] = $datos->profesional_asignado;
                $_SESSION["foto"] = $datos->foto;
                $_SESSION["bold"] = $datos->bold;
                $_SESSION["permiso_blog"] = $datos->permiso_blog;
                $_SESSION["permiso_biblioteca"] = $datos->permiso_biblioteca;
                $_SESSION["permiso_citas"] = $datos->permiso_citas;
                $_SESSION["permiso_promociones"] = $datos->permiso_promociones;
                $_SESSION["permiso_gastos"] = $datos->permiso_gastos;
                $_SESSION["permiso_citas_pagos"] = $datos->permiso_citas_pagos;
                header("Location: index");
                exit();
            } elseif ($password === $datos->clave_sys) {
                // Contraseña en texto plano: haz la transición a password_hash
                $hashedPassword = password_hash($password, PASSWORD_DEFAULT);
                
                // Actualiza la base de datos con el nuevo hash
                $updateStmt = $conn->prepare("UPDATE usuarios SET clave_sys = ? WHERE ID = ?");
                $updateStmt->bind_param("si", $hashedPassword, $datos->ID);
                $updateStmt->execute();
                $updateStmt->close();
                
                // Verifica nuevamente que el usuario esté activo antes de iniciar sesión
                if ($datos->estado == 1) {
                    $_SESSION["id"] = $datos->ID;
                    $_SESSION["pais"] = $datos->pais;
                    $_SESSION["tipdoc"] = $datos->tipdoc;
                    $_SESSION["numdoc"] = $datos->numdoc;
                    $_SESSION["pn_usu"] = $datos->pn_usu;
                    $_SESSION["sn_usu"] = $datos->sn_usu;
                    $_SESSION["pa_usu"] = $datos->pa_usu;
                    $_SESSION["sa_usu"] = $datos->sa_usu;
                    $_SESSION["cor_usu"] = $datos->cor_usu;
                    $_SESSION["permiso"] = $datos->permiso;
                    $_SESSION["profesional_asignado"] = $datos->profesional_asignado;
                    $_SESSION["foto"] = $datos->foto;
                    $_SESSION["bold"] = $datos->bold;
                    $_SESSION["permiso_blog"] = $datos->permiso_blog;
                    $_SESSION["permiso_biblioteca"] = $datos->permiso_biblioteca;
                    $_SESSION["permiso_citas"] = $datos->permiso_citas;
                    $_SESSION["permiso_promociones"] = $datos->permiso_promociones;
                    $_SESSION["permiso_gastos"] = $datos->permiso_gastos;
                    $_SESSION["permiso_citas_pagos"] = $datos->permiso_citas_pagos;
                    header("Location: index");
                    exit();
                } else {
                    $errorMessage = 'Usuario inactivo';
                    $errorType = 'loginac';  // Asigna un tipo de error
                }
            } else {
                // Contraseña incorrecta
                $errorMessage = 'Contraseña incorrecta';
                $errorType = 'loguoci';  // Asigna un tipo de error
            }
        } else {
            // Usuario no encontrado
            $errorMessage = 'Usuario no existe';
            $errorType = 'logne';  // Asigna un tipo de error
        }
        
        $stmt->close();
    } else {
        // Campos vacíos
        $errorMessage = 'Digite todos los datos';
        $errorType = 'logsd';  // Asigna un tipo de error
    }
}
?>
