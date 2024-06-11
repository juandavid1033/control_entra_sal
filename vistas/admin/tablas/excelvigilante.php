<?php
require_once("../../../db/conexion.php");
$daba = new Database();
$conex = $daba->conectar();
require_once("../../../vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Picqer\Barcode\BarcodeGeneratorPNG;

// Función para generar el archivo Excel
function generarExcel($resultado)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados de columna
    $sheet->setCellValue('A1', 'Documento');
    $sheet->setCellValue('B1', 'Nombre');
    $sheet->setCellValue('C1', 'Código de Barras');

    $generator = new BarcodeGeneratorPNG();

    // Llenar datos en la hoja
    $row = 2;
    foreach ($resultado as $row_data) {
        $sheet->setCellValue('A' . $row, $row_data['documento']);
        $sheet->setCellValue('B' . $row, $row_data['nombres']);

        // Generar código de barras
        if (isset($row_data['documento']) && !empty($row_data['documento'])) {
            $codigoBarras = $row_data['documento'];
            $barcode = $generator->getBarcode($codigoBarras, $generator::TYPE_CODE_128);
            $barcodeFile = tempnam(sys_get_temp_dir(), 'barcode') . '.png';
            file_put_contents($barcodeFile, $barcode);

            // Insertar imagen en la celda
            $drawing = new Drawing();
            $drawing->setPath($barcodeFile);
            $drawing->setCoordinates('C' . $row);
            $drawing->setHeight(50);
            $drawing->setWorksheet($sheet);

            // Ajustar el ancho de la columna C basado en el ancho de la imagen del código de barras
            $sheet->getColumnDimension('C')->setWidth(30); // Ajustar el ancho de la columna
        }

        $row++;
    }

    // Ajustar el tamaño de las filas
    for ($i = 2; $i < $row; $i++) {
        $sheet->getRowDimension($i)->setRowHeight(50);
    }

    // Crear el archivo Excel
    $writer = new Xlsx($spreadsheet);
    $filename = 'reporte.xlsx';
    $writer->save($filename);

    return $filename;
}

// Obtener los datos del usuario
$por_pagina = 5;
if (isset($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
} else {
    $pagina = 1;
}
$empieza = ($pagina - 1) * $por_pagina;
$sql = $conex->prepare("SELECT * FROM usuario  WHERE id_rol = 3 ORDER BY documento LIMIT $empieza, $por_pagina");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

// Llamar a la función para generar el archivo Excel
$archivo_excel = generarExcel($resultado);

// Descargar el archivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="reporte.xlsx"');
readfile($archivo_excel);
exit();
?>
