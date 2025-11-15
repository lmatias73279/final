<?php
include "../../conexionsm.php";
// session_update.php
function actualizarSesion($conn) {
    if (isset($_SESSION["id"])) {
        // Consulta la base de datos para obtener los datos más recientes del usuario
        $stmt = $conn->prepare("SELECT * FROM usuarios WHERE ID = ? LIMIT 1");
        $stmt->bind_param("i", $_SESSION["id"]);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($datos = $result->fetch_object()) {
            // Actualiza los datos de la sesión con los valores actuales de la base de datos
            $_SESSION["pais"] = $datos->pais;
            $_SESSION["tipdoc"] = $datos->tipdoc;
            $_SESSION["numdoc"] = $datos->numdoc;
            $_SESSION["pn_usu"] = $datos->pn_usu;
            $_SESSION["sn_usu"] = $datos->sn_usu;
            $_SESSION["pa_usu"] = $datos->pa_usu;
            $_SESSION["sa_usu"] = $datos->sa_usu;
            $_SESSION["permiso"] = $datos->permiso;
            $_SESSION["foto"] = $datos->foto;
            $_SESSION["bold"] = $datos->bold;
            $_SESSION["profesional_asignado"] = $datos->profesional_asignado;
            $_SESSION["permiso_blog"] = $datos->permiso_blog;
            $_SESSION["permiso_biblioteca"] = $datos->permiso_biblioteca;
            $_SESSION["permiso_citas"] = $datos->permiso_citas;
            $_SESSION["permiso_promociones"] = $datos->permiso_promociones;
            $_SESSION["permiso_gastos"] = $datos->permiso_gastos;
            $_SESSION["permiso_citas_pagos"] = $datos->permiso_citas_pagos;
        }
        
        $stmt->close();
    }
}

?>