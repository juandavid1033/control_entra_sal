<?php
require_once("../../../db/conexion.php");
$db = new Database();
$conexion = $db->conectar();
require_once("../../../vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

function generarExcel($resultado)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Column headers
    $sheet->setCellValue('A1', 'Placa');
    $sheet->setCellValue('B1', 'Marca');
    $sheet->setCellValue('C1', 'Color');
    $sheet->setCellValue('D1', 'Tipo de Vehiculo');
    $sheet->setCellValue('E1', 'Documento');
    $sheet->setCellValue('F1', 'Estado');

    // Fill data
    $row = 2;
    foreach ($resultado as $row_data) {
        // Assuming nom_mar is the field name for the brand name
        $sheet->setCellValue('A' . $row, $row_data['id_placa']);
        $sheet->setCellValue('B' . $row, $row_data['nom_mar']); // Use the brand name field
        // Assuming nom_color is the field name for the color name
        $sheet->setCellValue('C' . $row, $row_data['nom_color']); // Use the color name field
        // Assuming nom_vehiculo is the field name for the vehicle type name
        $sheet->setCellValue('D' . $row, $row_data['nom_vehiculo']); // Use the vehicle type name field
        $sheet->setCellValue('E' . $row, $row_data['documento']);
        // Assuming nom_estado is the field name for the state name
        $sheet->setCellValue('F' . $row, $row_data['nom_estado']); // Use the state name field
        $row++;
    }

    // Save Excel file
    $writer = new Xlsx($spreadsheet);
    $filename = 'reporte_vehiculos.xlsx';
    $writer->save($filename);

    return $filename;
}

// Fetch vehicle data from the database
$por_pagina = 5;
if (isset($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
} else {
    $pagina = 1;
}
$empieza = ($pagina - 1) * $por_pagina;
$sql = $conexion->prepare("SELECT vehiculos.id_placa, marca_vehi.nom_mar, color.nom_color, 
                                tipo_vehiculo.nom_vehiculo, vehiculos.documento, estados.nom_estado
                         FROM vehiculos 
                         LEFT JOIN marca_vehi ON vehiculos.id_marca = marca_vehi.id_marca
                         LEFT JOIN color ON vehiculos.id_color = color.id_color 
                         LEFT JOIN tipo_vehiculo ON vehiculos.id_tipo_vehiculo = tipo_vehiculo.id_tipo_vehiculo 
                         LEFT JOIN estados ON vehiculos.estado = estados.id_estados
                         ORDER BY vehiculos.id_placa LIMIT $empieza, $por_pagina");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

// Generate Excel file
$archivo_excel = generarExcel($resultado);

// Download Excel file
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $archivo_excel . '"');
header('Cache-Control: max-age=0');
readfile($archivo_excel);
exit();
?>
