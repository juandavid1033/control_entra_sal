<?php
// Incluir el archivo de conexión a la base de datos
require_once("../../db/conexion.php");
require_once("../../vendor/autoload.php");

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Worksheet\Drawing;
use Picqer\Barcode\BarcodeGeneratorPNG;

// Establecer la conexión a la base de datos
$db = new Database();
$conexion = $db->conectar();

function obtenerNombreRol($conexion, $id_rol) {
    $sql = $conexion->prepare("SELECT nom_rol FROM rol WHERE id_rol = ?");
    $sql->execute([$id_rol]);
    $resultado = $sql->fetch(PDO::FETCH_ASSOC);
    return $resultado ? $resultado['nom_rol'] : '';
}

function obtenerNombreTipoDocumento($conexion, $id_tipo_documento) {
    $sql = $conexion->prepare("SELECT nom_doc FROM tipo_documento WHERE id_tipo_documento = ?");
    $sql->execute([$id_tipo_documento]);
    $resultado = $sql->fetch(PDO::FETCH_ASSOC);
    return $resultado ? $resultado['nom_doc'] : '';
}

function obtenerNombreEstado($conexion, $id_estado) {
    $sql = $conexion->prepare("SELECT nom_estado FROM estados WHERE id_estados = ?");
    $sql->execute([$id_estado]);
    $resultado = $sql->fetch(PDO::FETCH_ASSOC);
    return $resultado ? $resultado['nom_estado'] : '';
}

function generarExcel($conexion, $userData)
{
    $spreadsheet = new Spreadsheet();
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados de columna
    $sheet->setCellValue('A1', 'Documento');
    $sheet->setCellValue('B1', 'Nombres');
    $sheet->setCellValue('C1', 'Correo');
    $sheet->setCellValue('D1', 'Codigo');
    $sheet->setCellValue('E1', 'Rol');
    $sheet->setCellValue('F1', 'Tipo Documento');
    $sheet->setCellValue('G1', 'Estado');
    $sheet->setCellValue('H1', 'Código de Barras');

    // Barcode generator
    $generator = new BarcodeGeneratorPNG();

    // Fill data for the current user
    $sheet->setCellValue('A2', $userData['documento']);
    $sheet->setCellValue('B2', $userData['nombres']);
    $sheet->setCellValue('C2', $userData['correo']);
    $sheet->setCellValue('D2', $userData['codigo']);
    $sheet->setCellValue('E2', obtenerNombreRol($conexion, $userData['id_rol']));
    $sheet->setCellValue('F2', obtenerNombreTipoDocumento($conexion, $userData['id_tipo_documento']));
    $sheet->setCellValue('G2', obtenerNombreEstado($conexion, $userData['id_estados']));
    $sheet->setCellValue('H2', $userData['codigo_barras']);

    // Generate and insert barcode image
    if (isset($userData['codigo_barras']) && !empty($userData['codigo_barras'])) {
        $codigoBarras = $userData['codigo_barras'];
        $barcode = $generator->getBarcode($codigoBarras, $generator::TYPE_CODE_128);
        $barcodeFile = tempnam(sys_get_temp_dir(), 'barcode') . '.png';
        file_put_contents($barcodeFile, $barcode);

        $drawing = new Drawing();
        $drawing->setPath($barcodeFile);
        $drawing->setCoordinates('H2');
        $drawing->setHeight(50);
        $drawing->setWorksheet($sheet);
    }

    // Save Excel file
    $writer = new Xlsx($spreadsheet);
    $filename = 'reporte_usuario_' . $userData['documento'] . '.xlsx'; // Use the user's document as part of the filename
    $writer->save($filename);

    return $filename;
}

// Iniciar sesión si aún no se ha iniciado
session_start();

// Obtener datos del usuario si está conectado
if (isset($_SESSION['documento'])) {
    $documento = $_SESSION['documento'];
    try {
        // Obtener datos del usuario actualmente conectado
        $sql = $conexion->prepare("SELECT * FROM usuario WHERE documento = ?");
        $sql->execute([$documento]);
        $userData = $sql->fetch(PDO::FETCH_ASSOC);

        // Generar el archivo Excel solo para este usuario
        $archivo_excel = generarExcel($conexion, $userData);

        // Descargar el archivo Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $archivo_excel . '"');
        header('Cache-Control: max-age=0');
        readfile($archivo_excel);

        // Eliminar el archivo temporal
        unlink($archivo_excel);
        exit();
    } catch (PDOException $e) {
        echo 'Error al obtener los datos del usuario: ' . $e->getMessage();
    }
} else {
    echo 'No se ha iniciado sesión';
}
?>
