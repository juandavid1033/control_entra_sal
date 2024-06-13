<?php
require_once("../../../db/conexion.php");
require_once("../../../vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

$daba = new Database();
$conex = $daba->conectar();

// Obtener datos de las entradas y salidas desde la base de datos
$por_pagina = 5;
if (isset($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
} else {
    $pagina = 1;
}
$empieza = ($pagina - 1) * $por_pagina;
$sql1 = $conex->prepare("SELECT entrada_salidas.*, tipo_entrada.nom_tipo, estados.nom_estado 
                         FROM entrada_salidas 
                         LEFT JOIN tipo_entrada ON entrada_salidas.tipo_entrada = tipo_entrada.id_tipo_entrada 
                         LEFT JOIN estados ON entrada_salidas.estado = estados.id_estados
                         ORDER BY entrada_salidas.documento 
                         LIMIT :empieza, :por_pagina");

$sql1->bindParam(':empieza', $empieza, PDO::PARAM_INT);
$sql1->bindParam(':por_pagina', $por_pagina, PDO::PARAM_INT);
$sql1->execute();
$resultado1 = $sql1->fetchAll(PDO::FETCH_ASSOC);

function generarExcel($resultado)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados de columna
    $sheet->setCellValue('B1', 'Fecha y Hora de Entrada');
    $sheet->setCellValue('C1', 'Fecha y Hora de Salida');
    $sheet->setCellValue('D1', 'Documento');
    $sheet->setCellValue('E1', 'Tipo de Entrada');
    $sheet->setCellValue('F1', 'Placa');
    $sheet->setCellValue('G1', 'Serial');
    $sheet->setCellValue('H1', 'Estado');

    // Llenar datos
    $row = 2;
    foreach ($resultado as $data) {

        $sheet->setCellValue('B' . $row, date("Y-m-d H:i:s", strtotime($data['entrada_fecha_hora'])));
        $sheet->setCellValue('C' . $row, !empty($data['salida_fecha_hora']) ? date("Y-m-d H:i:s", strtotime($data['salida_fecha_hora'])) : 'No registrado');
        $sheet->setCellValue('D' . $row, $data['documento']);
        $sheet->setCellValue('E' . $row, $data['nom_tipo']);
        $sheet->setCellValue('F' . $row, $data['id_placa']);
        $sheet->setCellValue('G' . $row, $data['serial']);
        $sheet->setCellValue('H' . $row, $data['nom_estado']);
        $row++;
    }

    // Guardar archivo Excel
    $writer = new Xlsx($spreadsheet);
    $filename = 'reporte_entrada_salidas.xlsx';
    $writer->save($filename);

    return $filename;
}

$archivo_excel = generarExcel($resultado1);

// Descargar el archivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment;filename="' . $archivo_excel . '"');
header('Cache-Control: max-age=0');
readfile($archivo_excel);
exit();
?>
