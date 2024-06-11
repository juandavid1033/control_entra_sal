<?php
require_once("../../../db/conexion.php");
require_once("../../../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;
use Picqer\Barcode\BarcodeGeneratorPNG;

$daba = new Database();
$conex = $daba->conectar();

// Función para generar el archivo PDF
function generarPDF($resultado)
{
    // Configuración de DomPdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

    // Crear una instancia de Dompdf
    $dompdf = new Dompdf($options);

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
            img {
                width: 100%;
                height: 50px; /* Ajustar la altura de la imagen */
            }
            .barcode-container {
                width: 200px; /* Aumentar el tamaño del contenedor del código de barras */
                height: auto;
                text-align: center;
            }
        </style>
    </head>
    <body>
        <h1>Lista de visitantes</h1>
        <table>
            <thead>
                <tr>
                    <th>Documento</th>
                    <th>Nombre</th>
                    <th>Código de Barras</th>
                </tr>
            </thead>
            <tbody>";

    $generator = new BarcodeGeneratorPNG();

    foreach ($resultado as $row) {
        // Verificar que 'codigo_barras' esté definido y no esté vacío
        if (isset($row['codigo_barras']) && !empty($row['codigo_barras'])) {
            $codigoBarras = $row['codigo_barras'];
            $barcode = base64_encode($generator->getBarcode($codigoBarras, $generator::TYPE_CODE_128));
            $barcodeImg = "<div class='barcode-container'><img src='data:image/png;base64,{$barcode}' alt='Código de Barras'></div>";
        } else {
            $barcodeImg = "No disponible";
        }

        $html .= "<tr>";
        $html .= "<td>{$row['documento']}</td>";
        $html .= "<td>{$row['nombres']}</td>";
        $html .= "<td>{$barcodeImg}</td>";
        $html .= "</tr>";
    }
    $html .= "</tbody></table></body></html>";

    // Cargar el contenido HTML en DomPdf
    $dompdf->loadHtml($html);

    // Renderizar el PDF
    $dompdf->render();

    // Descargar el PDF
    $dompdf->stream('reporte.pdf', ['Attachment' => true]);
}

// Obtener los datos de los visitantes
$por_pagina = 5;
if (isset($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
} else {
    $pagina = 1;
}
$empieza = ($pagina - 1) * $por_pagina;
$sql = $conex->prepare("SELECT * FROM usuario WHERE id_rol = 3 ORDER BY documento LIMIT $empieza, $por_pagina");
$sql->execute();
$resultado = $sql->fetchAll(PDO::FETCH_ASSOC);

// Llamar a la función para generar el archivo PDF
generarPDF($resultado);
?>
