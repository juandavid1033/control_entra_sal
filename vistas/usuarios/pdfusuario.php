<?php
require_once("../../db/conexion.php");
require_once("../../vendor/autoload.php");

use Dompdf\Dompdf;
use Dompdf\Options;
use Picqer\Barcode\BarcodeGeneratorPNG;

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

function generarPDF($usuario, $nombre_rol, $nombre_tipo_documento, $nombre_estado) {
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('isPhpEnabled', true);
    $options->set('defaultPaperSize', 'landscape');

    $dompdf = new Dompdf($options);

    $html = "
    <!DOCTYPE html>
    <html lang='en'>
    <head>
        <meta charset='UTF-8'>
        <meta http-equiv='X-UA-Compatible' content='IE=edge'>
        <meta name='viewport' content='width=device-width, initial-scale=1.0'>
        <title>Reporte PDF de Usuario</title>
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
                font-size: 10px;
            }
            th {
                background-color: #f2f2f2;
            }
            img {
                width: 100px;
                height: auto;
            }
        </style>
    </head>
    <body>
        <h1>Reporte PDF de Usuario</h1>
        <table>
            <thead>
                <tr>
                    <th style='width: 8%'>Documento</th>
                    <th style='width: 10%'>Nombres</th>
                    <th style='width: 10%'>Correo</th>
                    <th style='width: 8%'>Código de Barras</th>
                    <th style='width: 8%'>Rol</th>
                    <th style='width: 8%'>Tipo de Documento</th>
                    <th style='width: 8%'>Estado</th>
                </tr>
            </thead>
            <tbody>";

    if ($usuario) {
        $generator = new BarcodeGeneratorPNG();
        $barcode = base64_encode($generator->getBarcode($usuario['documento'], $generator::TYPE_CODE_128));
        $barcodeImg = "<img src='data:image/png;base64,{$barcode}' alt='Código de Barras'>";

        $html .= "<tr>
                    <td>{$usuario['documento']}</td>
                    <td>{$usuario['nombres']}</td>
                    <td>{$usuario['correo']}</td>
                    <td>{$barcodeImg}</td>
                    <td>{$nombre_rol}</td>
                    <td>{$nombre_tipo_documento}</td>
                    <td>{$nombre_estado}</td>
                </tr>";
    } else {
        $html .= "<tr><td colspan='7'>No se encontraron datos para el documento proporcionado.</td></tr>";
    }

    $html .= "</tbody>
        </table>
    </body>
    </html>";

    $dompdf->loadHtml($html);
    $dompdf->render();
    $dompdf->stream('reporte_usuario.pdf', ['Attachment' => true]);
}

session_start();

if (isset($_SESSION['documento'])) {
    $documento = $_SESSION['documento'];
    
    // Obtener datos del usuario desde la base de datos
    $sql = $conexion->prepare("SELECT * FROM usuario WHERE documento = ?");
    $sql->execute([$documento]);
    $usuario = $sql->fetch(PDO::FETCH_ASSOC);

    if ($usuario) {
        $nombre_rol = obtenerNombreRol($conexion, $usuario['id_rol']);
        $nombre_tipo_documento = obtenerNombreTipoDocumento($conexion, $usuario['id_tipo_documento']);
        $nombre_estado = obtenerNombreEstado($conexion, $usuario['id_estados']);
        generarPDF($usuario, $nombre_rol, $nombre_tipo_documento, $nombre_estado);
    } else {
        echo 'No se encontraron datos para el documento proporcionado.';
    }
} else {
    echo 'No se ha iniciado sesión';
}
?>
