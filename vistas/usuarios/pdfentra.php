<?php
// Incluir el archivo de conexión a la base de datos
require_once("../../db/conexion.php");
require_once("../../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// Establecer la conexión a la base de datos
$db = new Database();
$conexion = $db->conectar();

// Función para generar el reporte en formato PDF
function generarPDF($conexion)
{
    // Configurar opciones para Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

    // Establecer orientación horizontal
    $options->set('defaultPaperSize', 'landscape');

    // Inicializar Dompdf con las opciones configuradas
    $dompdf = new Dompdf($options);

    // Contenido HTML para el reporte
    $html = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Reporte PDF de Entradas y Salidas</title>
        <style>
            body {
                font-family: Arial, sans-serif;
                margin: 20px;
            }
            h1 {
                text-align: center;
                margin-bottom: 20px;
            }
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
                font-size: 10px; /* Tamaño de fuente ajustado */
            }
            th {
                background-color: #f2f2f2;
            }
        </style>
    </head>
    <body>
        <h1>Reporte PDF de Entradas y Salidas</h1>
        <table>
            <thead>
                <tr>
                    <th style='width: 5%'>ID</th>
                    <th style='width: 10%'>Entrada Fecha y Hora</th>
                    <th style='width: 10%'>Salida Fecha y Hora</th>
                    <th style='width: 8%'>Documento</th>
                    <th style='width: 8%'>Tipo de Entrada</th>
                    <th style='width: 8%'>ID Placa</th>
                    <th style='width: 8%'>Serial</th>
                    <th style='width: 8%'>Estado</th>
                </tr>
            </thead>
            <tbody>";

    // Obtener el documento del usuario de la sesión o como parámetro GET
    $documento = isset($_GET['documento']) ? $_GET['documento'] : $_SESSION['documento'];

    // Consulta SQL para obtener los datos de entrada y salida del usuario
    $sql = $conexion->prepare("SELECT * FROM entrada_salidas WHERE documento = ?");
    $sql->execute([$documento]);
    $entradasSalidas = $sql->fetchAll(PDO::FETCH_ASSOC);

    // Agregar datos de entrada_salidas a la tabla
    foreach ($entradasSalidas as $entradaSalida) {
        $html .= "<tr>
                    <td>{$entradaSalida['id_entrada_salida']}</td>
                    <td>{$entradaSalida['entrada_fecha_hora']}</td>
                    <td>{$entradaSalida['salida_fecha_hora']}</td>
                    <td>{$entradaSalida['documento']}</td>
                    <td>{$entradaSalida['tipo_entrada']}</td>
                    <td>{$entradaSalida['id_placa']}</td>
                    <td>{$entradaSalida['serial']}</td>
                    <td>{$entradaSalida['estado']}</td>
                </tr>";
    }

    $html .= "</tbody>
        </table>
    </body>
    </html>";

    // Cargar contenido HTML en Dompdf
    $dompdf->loadHtml($html);

    // Renderizar PDF
    $dompdf->render();

    // Generar descarga del PDF
    $dompdf->stream('reporte_entradas_salidas.pdf', array("Attachment" => false));

    exit;
}

// Generar el reporte PDF
generarPDF($conexion);
?>
