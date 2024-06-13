<?php
require_once("../../../db/conexion.php");
$daba = new Database();
$conex = $daba->conectar();
require_once("../../../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;

// Función para generar el reporte en formato PDF
function generarPDF($resultado)
{
    // Configurar opciones para Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);

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
            table {
                width: 100%;
                border-collapse: collapse;
            }
            th, td {
                border: 1px solid #dddddd;
                text-align: left;
                padding: 8px;
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
                    <th>Fecha y Hora de Entrada</th>
                    <th>Fecha y Hora de Salida</th>
                    <th>Documento</th>
                    <th>Tipo de Entrada</th>
                    <th>Placa</th>
                    <th>Serial</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>";

    // Agregar datos de las entradas y salidas a la tabla
    foreach ($resultado as $row_data) {
        $html .= "<tr>
                    <td>" . date("Y-m-d H:i:s", strtotime($row_data['entrada_fecha_hora'])) . "</td>
                    <td>" . (!empty($row_data['salida_fecha_hora']) ? date("Y-m-d H:i:s", strtotime($row_data['salida_fecha_hora'])) : 'No registrado') . "</td>
                    <td>{$row_data['documento']}</td>
                    <td>{$row_data['nom_tipo']}</td>
                    <td>{$row_data['id_placa']}</td>
                    <td>{$row_data['serial']}</td>
                    <td>{$row_data['nom_estado']}</td>
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
    $dompdf->stream('reporte_entrada_salidas.pdf');
}

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

// Generar reporte PDF
generarPDF($resultado1);
?>
