<?php
include "../../conexionsm.php"; // Tu conexión correcta

function obtenerTipoConsentimiento($tipo) {
    $tipos = [
        1 => 'Psicología',
        2 => 'Psiquiatría',
        3 => 'Adultos',
        4 => 'Pareja',
        5 => 'Niños'
    ];
    return $tipos[$tipo] ?? 'Desconocido';
}

if (isset($_POST['hiscli'])) {
    $hiscli = $_POST['hiscli'];
    $stmt = $conn->prepare("SELECT id, estado, tipo_consentimiento, fecha, fecha_sign FROM consentimientos WHERE hiscli = ?");
    $stmt->bind_param("s", $hiscli);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        echo "<ul class='list-group'>";
        while ($row = $result->fetch_assoc()) {
            $estado = $row['estado'];
            $tipoTexto = obtenerTipoConsentimiento($row['tipo_consentimiento']);
            $fecha = $row['fecha'];
            $fechaSign = $row['fecha_sign'];

            echo "<li class='list-group-item'>";
            echo "<div class='d-flex justify-content-between align-items-center flex-wrap'>";
            echo "<div><strong>$tipoTexto</strong></div>";

            if ($estado == 1) {
                echo "<div><a href='descargar_consentimiento.php?id=" . $row['id'] . "' class='btn btn-sm btn-primary mt-2' target='_blank'>Descargar</a></div>";
            } else {
                echo "<div><span class='badge badge-danger mt-2'>Pendiente de firma</span></div>";
            }

            echo "</div>"; // Cierra encabezado

            // Fechas
            echo "<div class='mt-2'><small><strong>Fecha:</strong> $fecha</small></div>";
            echo "<div><small><strong>Fecha de firma:</strong> " . ($fechaSign ? $fechaSign : '—') . "</small></div>";

            echo "</li>";
        }
        echo "</ul>";
    } else {
        echo "<p>No se encontraron documentos.</p>";
    }

    $stmt->close();
    $conn->close();
}
?>
