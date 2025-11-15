<?php
// Incluir el archivo de conexión
include "../../conexionsm.php"; // Asegúrate de que la ruta sea correcta

try {
    // Obtener el ID del profesional y la fecha desde la consulta
    $id_profesional = isset($_GET['id_profesional']) ? (int)$_GET['id_profesional'] : 0;
    $fecha = isset($_GET['fecha']) ? $_GET['fecha'] : '';

    // Obtener el día de la semana (0 = Domingo, 1 = Lunes, ..., 6 = Sábado)
    $dia_semana = date('w', strtotime($fecha));

    // Consulta para obtener la disponibilidad horaria
    $stmt = $conn->prepare("SELECT * FROM disponibilidad WHERE id_user = ?");
    $stmt->bind_param("i", $id_profesional);
    $stmt->execute();
    $result = $stmt->get_result();

    $horas_disponibles = [];

    while ($row = $result->fetch_assoc()) {
        // Dependiendo del día de la semana, agrega las horas disponibles
        switch ($dia_semana) {
            case 1: // Lunes
                if ($row['lu1d'] && $row['lu1h']) {
                    for ($hora = (int)$row['lu1d']; $hora < (int)$row['lu1h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                if ($row['lu2d'] && $row['lu2h']) {
                    for ($hora = (int)$row['lu2d']; $hora < (int)$row['lu2h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                break;
            case 2: // Martes
                if ($row['ma1d'] && $row['ma1h']) {
                    for ($hora = (int)$row['ma1d']; $hora < (int)$row['ma1h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                if ($row['ma2d'] && $row['ma2h']) {
                    for ($hora = (int)$row['ma2d']; $hora < (int)$row['ma2h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                break;
            case 3: // Miércoles
                if ($row['mi1d'] && $row['mi1h']) {
                    for ($hora = (int)$row['mi1d']; $hora < (int)$row['mi1h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                if ($row['mi2d'] && $row['mi2h']) {
                    for ($hora = (int)$row['mi2d']; $hora < (int)$row['mi2h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                break;
            case 4: // Jueves
                if ($row['ju1d'] && $row['ju1h']) {
                    for ($hora = (int)$row['ju1d']; $hora < (int)$row['ju1h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                if ($row['ju2d'] && $row['ju2h']) {
                    for ($hora = (int)$row['ju2d']; $hora < (int)$row['ju2h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                break;
            case 5: // Viernes
                if ($row['vi1d'] && $row['vi1h']) {
                    for ($hora = (int)$row['vi1d']; $hora < (int)$row['vi1h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                if ($row['vi2d'] && $row['vi2h']) {
                    for ($hora = (int)$row['vi2d']; $hora < (int)$row['vi2h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                break;
            case 6: // Sábado
                if ($row['sa1d'] && $row['sa1h']) {
                    for ($hora = (int)$row['sa1d']; $hora < (int)$row['sa1h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                if ($row['sa2d'] && $row['sa2h']) {
                    for ($hora = (int)$row['sa2d']; $hora < (int)$row['sa2h']; $hora++) {
                        $horas_disponibles[] = $hora;
                    }
                }
                break;
        }
    }    

    // Obtener las horas ocupadas de la tabla sessions
    $stmt = $conn->prepare("SELECT hora FROM sessions WHERE psi = ? AND fecha = ? AND estado != 3 AND estado != 5");
    $stmt->bind_param("is", $id_profesional, $fecha);
    $stmt->execute();
    $result = $stmt->get_result();

    $horas_ocupadas = [];
    while ($row = $result->fetch_assoc()) {
        // Convertir la hora a un número entero (HH0000)
        $hora = strtotime($row['hora']); // Convertir a timestamp
        $hora_entero = (int)date('H', $hora); // Convertir a formato HH0000
        $horas_ocupadas[] = $hora_entero; // Agregar a la lista de horas ocupadas
    }

    // Obtener las horas de la tabla hours_exception
    $stmt = $conn->prepare("SELECT hour FROM hours_exception WHERE date = ? AND id_user = ? AND status = 0");
    $stmt->bind_param("si", $fecha, $id_profesional);
    $stmt->execute();
    $result = $stmt->get_result();

    $horas_excepcion = [];
    while ($row = $result->fetch_assoc()) {
        // Convertir la hora a un número entero (HH0000)
        $hora = strtotime($row['hour']); // Convertir a timestamp
        $hora_entero = (int)date('H', $hora); // Convertir a formato HH0000
        $horas_excepcion[] = $hora_entero; // Agregar a la lista de horas de excepción
    }

    // Filtrar las horas disponibles para eliminar las horas ocupadas y las horas de excepción
    $horas_disponibles = array_diff($horas_disponibles, $horas_ocupadas, $horas_excepcion);

    // Devolver las horas disponibles como JSON
    echo json_encode(array_values(array_unique($horas_disponibles)));
} catch (Exception $e) {
    echo json_encode(['error' => $e->getMessage()]);
}
?>