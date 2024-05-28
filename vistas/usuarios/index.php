<?php
// Incluir el archivo de conexión a la base de datos
require_once("../../db/conexion.php");
session_start();

// Inicializar $userData como un array vacío para evitar el error de "Undefined variable"
$userData = [];

// Obtener datos del usuario si el documento está en la sesión
if (isset($_SESSION['documento'])) {
    try {
        // Instanciar la clase Database
        $db = new Database();
        // Conectar a la base de datos
        $conn = $db->conectar();

        // Obtener el documento del usuario de la sesión
        $documento = $_SESSION['documento'];

        // Consulta SQL para obtener los datos del usuario y unir con las tablas relacionadas
        $sql = "SELECT u.*, r.nom_rol AS rol, td.nom_doc AS tipo_documento, e.nom_estado AS estado, em.nombre AS nombre_empresa
                FROM usuario u
                LEFT JOIN rol r ON u.id_rol = r.id_rol
                LEFT JOIN tipo_documento td ON u.id_tipo_documento = td.id_tipo_documento
                LEFT JOIN estados e ON u.id_estados = e.id_estados
                LEFT JOIN empresas em ON u.nit_empresa = em.nit_empresa
                WHERE u.documento = ?";
        
        // Preparar la consulta SQL
        $stmt = $conn->prepare($sql);
        // Ejecutar la consulta con el documento del usuario
        $stmt->execute([$documento]);
        // Obtener los resultados de la consulta como un array asociativo
        $userData = $stmt->fetch(PDO::FETCH_ASSOC);

        // Mapeo de números a nombres para el campo "Tipo de Entrada"
        $tipoEntradaMap = [
            1 => 'Entrada',
            2 => 'Salida'
            // Agregar más mapeos según sea necesario
        ];

        // Mapeo de números a nombres para el campo "Estado"
        $estadoMap = [
            1 => 'Activo',
            2 => 'Inactivo'
            // Agregar más mapeos según sea necesario
        ];

        // Reemplazar los números con los nombres correspondientes
        if (isset($userData['tipo_entrada']) && isset($tipoEntradaMap[$userData['tipo_entrada']])) {
            $userData['tipo_entrada'] = $tipoEntradaMap[$userData['tipo_entrada']];
        }
        if (isset($userData['estado']) && isset($estadoMap[$userData['estado']])) {
            $userData['estado'] = $estadoMap[$userData['estado']];
        }

    } catch (PDOException $e) {
        // Mostrar un mensaje de error si ocurre una excepción
        echo 'Error al obtener los datos del usuario: ' . $e->getMessage();
    } finally {
        // Cerrar la conexión a la base de datos
        $conn = null;
    }
}
?>


<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>
        <?php
        // Verificar si se ha iniciado sesión y el usuario tiene nombre
        if (!empty($userData['nombres'])) {
            echo htmlspecialchars($userData['nombres']) . " - Datos del Usuario";
        } else {
            echo "Datos del Usuario";
        }
        ?>
    </title>
    <style>
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
    </style>
</head>
<body>
    <div class="container">
        <h1>
            <?php
            // Verificar si se ha iniciado sesión y el usuario tiene nombre
            if (!empty($userData['nombres'])) {
                echo "Datos de " . htmlspecialchars($userData['nombres']);
            } else {
                echo "Datos del Usuario";
            }
            ?>
        </h1>
        <?php if (!empty($userData)): ?>
            <table>
                <tr>
                    <th>Campo</th>
                    <th>Valor</th>
                </tr>
                <?php foreach ($userData as $key => $value): ?>
                    <?php 
                    // Excluir ciertos campos de ser mostrados en la tabla
                    if (!in_array($key, ['id_rol', 'id_tipo_documento', 'id_estados', 'contrasena', 'foto', 'codigo_barras', 'nit_empresa'])): ?>
                        <tr>
                            <td><?php echo ucwords(str_replace('_', ' ', $key)); ?>:</td>
                            <td>
                                <?php 
                                // Reemplazar los números con los nombres correspondientes
                                if ($key === 'tipo_entrada' && isset($tipoEntradaMap[$value])) {
                                    echo $tipoEntradaMap[$value];
                                } elseif ($key === 'estado' && isset($estadoMap[$value])) {
                                    echo $estadoMap[$value];
                                } else {
                                    echo htmlspecialchars($value);
                                }
                                ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                <?php endforeach; ?>
                <?php if (!empty($userData['codigo_barras'])): ?>
                    <tr>
                        <td>Código de Barras:</td>
                        <td><img src="./../../images/<?= $userData["codigo_barras"] ?>.png" style="max-width: 300px; height: auto; border: 2px solid #ffffff;"><br><?= $userData["codigo_barras"] ?></td>

                    </tr>
                <?php endif; ?>
            </table>
            <div class="btn-container">
                <a class="btn btn-success" href="excelusuario.php">Exportar a Excel</a>
                <a class="btn btn-danger" href="pdfusuario.php">Descargar PDF</a>
                <a class="btn btn-secondary" href="entradasalida.php?documento=<?php echo htmlspecialchars($documento); ?>">Ver Entrada y Salida</a>
                <button type="button" onclick="location.href='../../index.html'" class="btn btn-secondary">Cerrar Sesión</button>
            </div>
        <?php else: ?>
            <p>No se encontraron datos para el documento proporcionado.</p>
        <?php endif; ?>
    </div>
</body>
</html>
