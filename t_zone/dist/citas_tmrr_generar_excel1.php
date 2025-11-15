<?php
require __DIR__ . '/../../vendor/autoload.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$fechaDesde = $_POST['fechaDesde'];
$fechaHasta = $_POST['fechaHasta'];

include "../../conexionsm.php";

// Consulta directamente desde sessions con JOINs a usuarios y meetChanges
$sql = "
    SELECT 
        s.ID,
        s.fecha AS fecha,
        s.hora AS hora,
        s.userID,
        s.psi,
        s.tipo,
        s.site,
        CONCAT_WS(' ', IFNULL(u.pn_usu, ''), IFNULL(u.sn_usu, ''), IFNULL(u.pa_usu, ''), IFNULL(u.sa_usu, '')) AS nombre_userID,
        CONCAT_WS(' ', IFNULL(u2.pn_usu, ''), IFNULL(u2.sn_usu, ''), IFNULL(u2.pa_usu, ''), IFNULL(u2.sa_usu, '')) AS nombre_psi
    FROM sessions s
    LEFT JOIN usuarios u ON s.userID = u.ID
    LEFT JOIN usuarios u2 ON s.psi = u2.ID
    WHERE s.fecha BETWEEN '$fechaDesde' AND '$fechaHasta'
";


$result = $conn->query($sql);

// Crear archivo Excel
$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();
$sheet->setCellValue('A1', 'Paciente');
$sheet->setCellValue('B1', 'Psicólogo');
$sheet->setCellValue('C1', 'Tipo terapia');
$sheet->setCellValue('D1', 'Lugar');
$sheet->setCellValue('E1', 'Fecha agendada');
$sheet->setCellValue('F1', 'Hora agendada');

// Estilos
$styleArray = [
    'font' => ['bold' => true],
    'alignment' => [
        'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
        'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
    ]
];
$sheet->getStyle('A1:J1')->applyFromArray($styleArray);

// Llenar datos
$fila = 2;
while ($row = $result->fetch_assoc()) {
    $userID = ucwords(strtolower(preg_replace('/\s+/', ' ', trim($row['nombre_userID']))));
    $psi = ucwords(strtolower(preg_replace('/\s+/', ' ', trim($row['nombre_psi']))));

    // Tipo de terapia
    switch ($row['tipo']) {
        case 1: $tipoTexto = 'Individual'; break;
        case 2: $tipoTexto = 'Pareja'; break;
        case 5: $tipoTexto = 'Familia'; break;
        case 6: $tipoTexto = 'Psiquiatría'; break;
        case 7: $tipoTexto = 'Valoración'; break;
        case 8: $tipoTexto = 'Nutrición'; break;
        default: $tipoTexto = 'Desconocido'; break;
    }

    // Lugar
    $siteValue = match ($row['site']) {
        '1' => 'Presencial',
        '2' => 'Virtual',
        default => 'Desconocido',
    };

    $sheet->setCellValue('A' . $fila, $userID);
    $sheet->setCellValue('B' . $fila, $psi);
    $sheet->setCellValue('C' . $fila, $tipoTexto);
    $sheet->setCellValue('D' . $fila, $siteValue);
    $sheet->setCellValue('E' . $fila, $row['fecha']);
    $sheet->setCellValue('F' . $fila, $row['hora']);
    $fila++;
}

// Ajustar ancho de columnas
foreach (range('A', 'J') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Descargar archivo
$writer = new Xlsx($spreadsheet);
$nombreArchivo = "Citas Generales.xlsx";
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="' . $nombreArchivo . '"');
$writer->save("php://output");
exit;
?>
