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
        if (isset($row_data['codigo_barras']) && !empty($row_data['codigo_barras'])) {
            $codigoBarras = $row_data['codigo_barras'];
            $barcode = $generator->getBarcode($codigoBarras, $generator::TYPE_CODE_128);
            $barcodeFile = tempnam(sys_get_temp_dir(), 'barcode') . '.png';
            file_put_contents($barcodeFile, $barcode);

            // Insertar imagen en la celda
            $drawing = new Drawing();
            $drawing->setPath($barcodeFile);
            $drawing->setCoordinates('C' . $row);
            $drawing->setHeight(50);
            $drawing->setWorksheet($sheet);

            // Obtener el ancho de la imagen del código de barras
            $barcodeImageWidth = $drawing->getWidth();

            // Ajustar el ancho de la columna C basado en el ancho de la imagen del código de barras
            $sheet->getColumnDimension('C')->setWidth($barcodeImageWidth / 7); // Dividir por 7 para ajustar el ancho
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

    // Descargar el archivo
    header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    header('Content-Disposition: attachment; filename="reporte.xlsx"');
    readfile($filename);
    exit();
}

// Obtener los datos del usuario
$por_pagina = 5;
if (isset($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
} else {
    $pagina = 1;
}
$empieza = ($pagina - 1) * $por_pagina;
$sql1 = $conex->prepare("SELECT * FROM usuario WHERE id_rol = 5 ORDER BY documento LIMIT $empieza, $por_pagina");
$sql1->execute();
$resultado1 = $sql1->fetchAll(PDO::FETCH_ASSOC);

// Llamar a la función para generar el Excel
generarExcel($resultado1);
?>
