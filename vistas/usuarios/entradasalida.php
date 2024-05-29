<?php
// Incluir el archivo de conexión a la base de datos
require_once("../../db/conexion.php");
session_start();

// Verificar si el documento del usuario está en la sesión o como parámetro GET
if (isset($_SESSION['documento']) || isset($_GET['documento'])) {
    try {
        // Instanciar la clase Database
        $db = new Database();
        // Conectar a la base de datos
        $conn = $db->conectar();

        // Obtener el documento del usuario de la sesión o del parámetro GET
        $documento = isset($_GET['documento']) ? $_GET['documento'] : $_SESSION['documento'];

        // Obtener las fechas del formulario si están presentes
        $fechaInicio = isset($_GET['fecha_inicio']) ? $_GET['fecha_inicio'] : '';
        $fechaFin = isset($_GET['fecha_fin']) ? $_GET['fecha_fin'] : '';

        // Consulta SQL básica
        $sql = "SELECT id_entrada_salida, entrada_fecha_hora, salida_fecha_hora, documento, tipo_entrada, id_placa, serial, estado
                FROM entrada_salidas
                WHERE documento = ?";

        // Agregar condiciones de fecha si están presentes
        if ($fechaInicio && $fechaFin) {
            $sql .= " AND entrada_fecha_hora BETWEEN ? AND ?";
        }

        // Preparar la consulta SQL
        $stmt = $conn->prepare($sql);

        // Ejecutar la consulta con los parámetros adecuados
        if ($fechaInicio && $fechaFin) {
            $stmt->execute([$documento, $fechaInicio . ' 00:00:00', $fechaFin . ' 23:59:59']);
        } else {
            $stmt->execute([$documento]);
        }

        // Obtener los resultados de la consulta
        $entries = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Mapeo de números a nombres para el campo "Tipo de Entrada"
        $tipoEntradaMap = [
            1 => 'Entrada',
            2 => 'Salida'
            // Puedes agregar más mapeos según sea necesario
        ];

        // Mapeo de números a nombres para el campo "Estado"
        $estadoMap = [
            1 => 'Activo',
            2 => 'Inactivo'
            // Puedes agregar más mapeos según sea necesario
        ];

    } catch (PDOException $e) {
        // Mostrar un mensaje de error si ocurre una excepción
        echo 'Error al obtener los datos de entrada y salida: ' . $e->getMessage();
    } finally {
        // Cerrar la conexión a la base de datos
        $conn = null;
    }
} else {
    // Mostrar un mensaje si no se proporciona un documento válido
    echo 'No se proporcionó un documento válido.';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reporte de Entrada y Salida</title>
    <style>
        /* Estilos CSS para la página */
        body {
            font-family: Arial, sans-serif;
            background-color: #f4f4f4;
            color: #333;
            margin: 0;
            padding: 0;
        }
        .container {
            max-width: 800px;
            margin: 50px auto;
            padding: 20px;
            background-color: #fff;
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
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
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }
        th {
            background-color: #f2f2f2;
        }
        .btn-container {
            margin-top: 20px;
            text-align: center;
        }
        .btn {
            padding: 10px 20px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            margin-right: 10px;
            text-decoration: none;
            color: #fff;
            font-weight: bold;
            transition: background-color 0.3s;
        }
        .btn-success {
            background-color: #28a745;
        }
        .btn-danger {
            background-color: #dc3545;
        }
        .btn-secondary {
            background-color: #6c757d;
        }
        .btn:hover {
            background-color: #007bff;
        }
        .filter-container {
            margin-bottom: 20px;
            text-align: center;
        }
        .filter-container input {
            padding: 10px;
            margin-right: 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Reporte de Entrada y Salida</h1>
        
        <!-- Formulario para filtrar por fecha -->
        <div class="filter-container">
            <form method="GET" action="">
                <input type="hidden" name="documento" value="<?php echo htmlspecialchars($documento); ?>">
                <input type="date" name="fecha_inicio" value="<?php echo htmlspecialchars($fechaInicio); ?>" required>
                <input type="date" name="fecha_fin" value="<?php echo htmlspecialchars($fechaFin); ?>" required>
                <button type="submit" class="btn btn-secondary">Filtrar</button>
            </form>
        </div>
        
        <?php if (!empty($entries)): ?>
            <table>
                <tr>
                    <th>ID</th>
                    <th>Entrada Fecha y Hora</th>
                    <th>Salida Fecha y Hora</th>
                    <th>Documento</th>
                    <th>Tipo de Entrada</th>
                    <th>ID Placa</th>
                    <th>Serial</th>
                    <th>Estado</th>
                </tr>
                <?php foreach ($entries as $entry): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($entry['id_entrada_salida']); ?></td>
                        <td><?php echo htmlspecialchars($entry['entrada_fecha_hora']); ?></td>
                        <td><?php echo htmlspecialchars($entry['salida_fecha_hora']); ?></td>
                        <td><?php echo htmlspecialchars($entry['documento']); ?></td>
                        <td><?php echo isset($tipoEntradaMap[$entry['tipo_entrada']]) ? $tipoEntradaMap[$entry['tipo_entrada']] : ''; ?></td>
                        <td><?php echo htmlspecialchars($entry['id_placa']); ?></td>
                        <td><?php echo htmlspecialchars($entry['serial']); ?></td>
                        <td><?php echo isset($estadoMap[$entry['estado']]) ? $estadoMap[$entry['estado']] : ''; ?></td>
                    </tr>
                <?php endforeach; ?>
            </table>
            <div class="btn-container">
                <!-- Enlaces para descargar en PDF y Excel con el documento como parámetro -->
                <a class="btn btn-success" href="excelentra.php?documento=<?php echo urlencode($documento); ?>&fecha_inicio=<?php echo urlencode($fechaInicio); ?>&fecha_fin=<?php echo urlencode($fechaFin); ?>">Exportar a Excel</a>
                <a class="btn btn-danger" href="pdfentra.php?documento=<?php echo urlencode($documento); ?>&fecha_inicio=<?php echo urlencode($fechaInicio); ?>&fecha_fin=<?php echo urlencode($fechaFin); ?>">Descargar PDF</a>
            </div>
        <?php else: ?>
            <p>No se encontraron registros de entrada y salida para el documento proporcionado.</p>
        <?php endif; ?>

        <!-- Botón de regreso -->
        <div style="text-align: center; margin-top: 20px;">
            <button onclick="history.back()" style="padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; text-decoration: none; color: #fff; background-color: #6c757d; font-weight: bold; transition: background-color 0.3s;">Regresar</button>
        </div>
    </div>
</body>
</html>
