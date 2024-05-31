<?php
require_once("../../../db/conexion.php");
require_once("../../../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;
use Picqer\Barcode\BarcodeGeneratorPNG;

$daba = new Database();
$conex = $daba->conectar();

// Verifica que la conexión se haya establecido correctamente
if ($conex === false) {
    die("ERROR: No se pudo conectar a la base de datos.");
}

// Configuración de DomPdf
$options = new Options();
$options->set('isHtml5ParserEnabled', true);
$options->set('isPhpEnabled', true);

// Crear una instancia de Dompdf
$dompdf = new Dompdf($options);

// Obtener los datos de la primera tabla
$sql1 = $conex->prepare("SELECT * FROM usuario  WHERE id_rol = 5 ORDER BY documento");
$sql1->execute();
$resultado1 = $sql1->fetchAll(PDO::FETCH_ASSOC);

// Generar el contenido HTML del PDF
$html = "<html>
<head>
    <title>Reporte PDF</title>
    <style>
        table {
            width: 100%;
            border-collapse: collapse;
        }
        th, td {
            border: 1px solid black;
            padding: 8px;
            text-align: left;
        }
        th {
            background-color: #f2f2f2;
        }
        .barcode-img {
            max-width: 200px; /* Ajusta el ancho máximo de la imagen del código de barras */
            height: auto;
        }
    </style>
</head>
<body>
    <h1>Instructores</h1>
    <table>
        <thead>
            <tr>
                <th>Documento</th>
                <th>Código de Barras</th>
                <th>Nombre</th>
            </tr>
        </thead>
        <tbody>";

$generator = new BarcodeGeneratorPNG();

foreach ($resultado1 as $row) {
    // Verificar que 'codigo_barras' esté definido y no esté vacío
    if (isset($row['codigo_barras']) && !empty($row['codigo_barras'])) {
        $codigoBarras = $row['codigo_barras'];
        $barcode = base64_encode($generator->getBarcode($codigoBarras, $generator::TYPE_CODE_128));
        $barcodeImg = "<img class='barcode-img' src='data:image/png;base64,{$barcode}' alt='Código de Barras'>";
    } else {
        $barcodeImg = "No disponible";
    }

    $html .= "<tr>";
    $html .= "<td>{$row['documento']}</td>";
    $html .= "<td>{$barcodeImg}</td>";
    $html .= "<td>{$row['nombres']}</td>";
    $html .= "</tr>";
}

$html .= "</tbody></table></body></html>";

// Cargar el contenido HTML en DomPdf
$dompdf->loadHtml($html);

// Renderizar el PDF
$dompdf->render();

// Salida del PDF (descargar o mostrar)
$dompdf->stream('reporte.pdf', ['Attachment' => true]);
?>
