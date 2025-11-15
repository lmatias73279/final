<?php
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$fechaDesde = $_POST['fechaDesde'];
$fechaHasta = $_POST['fechaHasta'];

include "../../conexionsm.php";

// Consulta los datos entre las fechas seleccionadas
$sql = "SELECT * FROM meetchanges WHERE fechaant BETWEEN '$fechaDesde' AND '$fechaHasta'";
$result = $conn->query($sql);

// Crear archivo Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Paciente');
$sheet->setCellValue('B1', 'Psicologo');
$sheet->setCellValue('C1', 'Tipo terapia');
$sheet->setCellValue('D1', 'Lugar');
$sheet->setCellValue('E1', 'Fecha agendada');
$sheet->setCellValue('F1', 'Hora agendada');
$sheet->setCellValue('G1', 'Reprogramaciones');
$sheet->setCellValue('H1', 'Fecha reprogramada');
$sheet->setCellValue('I1', 'Hora reprogramada');
$sheet->setCellValue('J1', 'Observaciones');

// Establecer estilos para los títulos
$styleArray = [
    'font' => [
        'bold' => true, // Negrita
    ],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER, // Centrado horizontalmente
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER, // Centrado verticalmente
    ]
];

// Aplicar el estilo a las celdas de los títulos
$sheet->getStyle('A1:J1')->applyFromArray($styleArray);


// Llenar filas con datos
$fila = 2;
while ($row = $result->fetch_assoc()) {

    $sql1 = "SELECT 
            hora, 
            fecha, 
            userID, 
            psi, 
            tipo, 
            site,
            CONCAT_WS(' ', IFNULL(u.pn_usu, ''), IFNULL(u.sn_usu, ''), IFNULL(u.pa_usu, ''), IFNULL(u.sa_usu, '')) AS nombre_userID,
            CONCAT_WS(' ', IFNULL(u2.pn_usu, ''), IFNULL(u2.sn_usu, ''), IFNULL(u2.pa_usu, ''), IFNULL(u2.sa_usu, '')) AS nombre_psi
        FROM sessions s
        LEFT JOIN usuarios u ON s.userID = u.ID
        LEFT JOIN usuarios u2 ON s.psi = u2.ID
        WHERE s.ID = " . $row['idse'];

    $result1 = $conn->query($sql1);
    
    // Verificar si la consulta devuelve un resultado
    if ($result1->num_rows > 0) {
        // Obtener la fila de resultados
        $row1 = $result1->fetch_assoc();
        
        // Guardar los valores en variables
        $hora = $row1['hora'];
        $fecha = $row1['fecha'];
        $userID = $row1['nombre_userID'];
        $psi = $row1['nombre_psi'];
        // Obtener el tipo
        $tipo = $row1['tipo'];

        // Asignar el valor correspondiente al tipo
        switch ($tipo) {
            case 1:
                $tipoTexto = 'Individual';
                break;
            case 2:
                $tipoTexto = 'Pareja';
                break;
            case 5:
                $tipoTexto = 'Familia';
                break;
            case 6:
                $tipoTexto = 'Psiquiatría';
                break;
            case 7:
                $tipoTexto = 'Valoración';
                break;
            case 8:
                $tipoTexto = 'Nutrición';
                break;
            default:
                $tipoTexto = 'Desconocido'; // En caso de que el tipo no sea válido
                break;
        }
        $site = $row1['site'];
    } else {
        // Si no se encuentra ningún resultado, puedes manejar el caso aquí
        $hora = null;
        $fecha = null;
        $userID = null;
        $psi = null;
        $tipo = null;
        $site = null;
    }
    $idse = $row['idse']; // El valor que deseas contar en la columna
    // Consulta para contar las veces que aparece el idse en la columna
    $sql2 = "SELECT COUNT(*) AS cantidad FROM meetchanges WHERE idse = $idse";
    $result2 = $conn->query($sql2);

    // Verificar si la consulta devuelve un resultado
    if ($result2->num_rows > 0) {
        $rowCount = $result2->fetch_assoc()['cantidad'];
    } else {
        $rowCount = null;
    }

    // Formatear el nombre del paciente y del psicólogo
    $userID = ucwords(strtolower(preg_replace('/\s+/', ' ', trim($userID))));
    $psi = ucwords(strtolower(preg_replace('/\s+/', ' ', trim($psi))));

    // Agregar los valores al archivo Excel
    $sheet->setCellValue('A' . $fila, $userID);
    $sheet->setCellValue('B' . $fila, $psi);

    $sheet->setCellValue('C' . $fila, $tipoTexto);
    // Verificar el valor de $site y asignar el texto correspondiente
    if ($site == 1) {
        $siteValue = 'Presencial';
    } elseif ($site == 2) {
        $siteValue = 'Virtual';
    } else {
        $siteValue = 'Desconocido'; // Opcional, para manejar otros valores
    }

    // Asignar el valor a la celda
    $sheet->setCellValue('D' . $fila, $siteValue);
    $sheet->setCellValue('E' . $fila, $row['fechaant']);
    $sheet->setCellValue('F' . $fila, $row['horaant']);
    $sheet->setCellValue('G' . $fila, $rowCount);
    $sheet->setCellValue('H' . $fila, $fecha);
    $sheet->setCellValue('I' . $fila, $hora);
    $sheet->setCellValue('J' . $fila, $row['observacion']);
    $fila++;
}

// Ajustar el ancho de las columnas automáticamente
foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar el archivo Excel
$writer = new Xlsx($spreadsheet);
$nombreArchivo = "Reprogramaciones.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
$writer->save("php://output");
exit;
?>
