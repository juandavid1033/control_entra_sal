<?php
// Incluir el archivo de conexión a la base de datos
require_once("../../db/conexion.php");
require_once("../../vendor/autoload.php");

// Establecer la conexión a la base de datos
$db = new Database();
$conexion = $db->conectar();

// Función para generar el archivo Excel
function generarExcel($conexion, $documento)
{
    // Crear un nuevo objeto Spreadsheet (hoja de cálculo)
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    // Obtener la hoja activa del objeto Spreadsheet
    $sheet = $spreadsheet->getActiveSheet();

    // Encabezados de las columnas
    $sheet->setCellValue('A1', 'ID Entrada-Salida');
    $sheet->setCellValue('B1', 'Fecha y Hora de Entrada');
    $sheet->setCellValue('C1', 'Fecha y Hora de Salida');
    $sheet->setCellValue('D1', 'Documento');
    $sheet->setCellValue('E1', 'Tipo de Entrada');
    $sheet->setCellValue('F1', 'ID Placa');
    $sheet->setCellValue('G1', 'Serial');
    $sheet->setCellValue('H1', 'Estado');

    // Consulta SQL para obtener los datos de entrada y salida del usuario
    $sql = "SELECT id_entrada_salida, entrada_fecha_hora, salida_fecha_hora, documento, tipo_entrada, id_placa, serial, estado
            FROM entrada_salidas
            WHERE documento = ?";
    
    // Preparar la consulta SQL
    $stmt = $conexion->prepare($sql);
    // Ejecutar la consulta con el documento del usuario
    $stmt->execute([$documento]);
    // Obtener los resultados de la consulta
    $entradasSalidas = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Llenar la hoja de cálculo con los datos de entrada y salida
    $row = 2;
    foreach ($entradasSalidas as $entradaSalida) {
        $sheet->setCellValue('A' . $row, $entradaSalida['id_entrada_salida']);
        $sheet->setCellValue('B' . $row, $entradaSalida['entrada_fecha_hora']);
        $sheet->setCellValue('C' . $row, $entradaSalida['salida_fecha_hora']);
        $sheet->setCellValue('D' . $row, $entradaSalida['documento']);
        $sheet->setCellValue('E' . $row, $entradaSalida['tipo_entrada']);
        $sheet->setCellValue('F' . $row, $entradaSalida['id_placa']);
        $sheet->setCellValue('G' . $row, $entradaSalida['serial']);
        $sheet->setCellValue('H' . $row, $entradaSalida['estado']);
        $row++;
    }

    // Guardar el archivo Excel
    $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
    $filename = 'reporte_entrada_salida_' . $documento . '.xlsx'; // Nombre del archivo con el documento del usuario
    $writer->save($filename);

    return $filename; // Devolver el nombre del archivo generado
}

// Iniciar sesión si aún no se ha iniciado
session_start();

// Obtener documento del usuario si está conectado
if (isset($_SESSION['documento'])) {
    $documento = $_SESSION['documento'];
    try {
        // Generar el archivo Excel solo con los datos de entrada y salida para este usuario
        $archivo_excel = generarExcel($conexion, $documento);

        // Descargar el archivo Excel
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $archivo_excel . '"');
        header('Cache-Control: max-age=0');
        readfile($archivo_excel);
        exit();
    } catch (PDOException $e) {
        echo 'Error al obtener los datos de entrada y salida: ' . $e->getMessage();
    }
} else {
    echo 'No se ha iniciado sesión';
}
?>
