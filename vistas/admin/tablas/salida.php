<?php
require_once ("../../../db/conexion.php");
$daba = new Database();
$conex = $daba->conectar();

// PAGINACION
$por_pagina = 5;
if (isset($_GET['pagina'])) {
    $pagina = $_GET['pagina'];
} else {
    $pagina = 1;
}
$empieza = ($pagina - 1) * $por_pagina;
$sql1 = $conex->prepare("SELECT * FROM entrada_salidas 
    LEFT JOIN usuario ON entrada_salidas.documento = usuario.documento 
    LEFT JOIN tipo_entrada ON entrada_salidas.tipo_entrada = tipo_entrada.id_tipo_entrada 
    WHERE entrada_salidas.estado = 2
    ORDER BY entrada_salidas.documento 
    LIMIT :empieza, :por_pagina");

$sql1->bindParam(':empieza', $empieza, PDO::PARAM_INT);
$sql1->bindParam(':por_pagina', $por_pagina, PDO::PARAM_INT);
$sql1->execute();
$resultado1 = $sql1->fetchAll(PDO::FETCH_ASSOC);

$sql = $conex->prepare("SELECT COUNT(*) FROM entrada_salidas WHERE estado = 2");
$sql->execute();
$resul = $sql->fetchColumn();
$total_paginas = ceil($resul / $por_pagina);
if ($total_paginas == 0) {
    echo "<center>" . 'Lista Vacia' . "</center>";
} else {
    echo "<center><a href='t_cliente.php?pagina=1'>" . "<i class='fa fa-arrow-left'></i>" . "</a>";
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="../../../css/stiledi.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.4/font/bootstrap-icons.css">
    <title>Lista de salida</title>
    <link href="../../../img1/logo9.png" rel="icon">
</head>

<body>

    <a class="btn btn-success" href="../index.php" style="margin-left: -45%; margin-top:3%; position:absolute;">
        <i class="bi bi-chevron-left" style="padding:10px 14px 10px 10px; color:#fff; font-size:15px; background-color:#0d6efd; border-radius:10px;">
            REGRESAR
        </i>
    </a>

    <br>
    <a class="btn btn-success" href="../excel/excel_clientes.php" style="margin-left: 90%; margin-top:0%;">
        <i class="bi bi-file-earmark-excel" style="padding:10px 10px 10px 10px; border-radius:10px; color:#fff; font-size:15px; background-color:#198754;">
            EXCEL
        </i>
    </a>
    <br>
    <a class="btn btn-success" href="../reporte/repor_cliente.php" style="margin-left: 88%; margin-top:1%;">
        <i class="bi bi-printer" style="padding:10px 16px 10px 16px; border-radius:10px; color:#fff; font-size:15px; background-color:#E00000;">
            IMPRIMIR
        </i>
    </a>

    <!--creamos la tabla-->
    <table>
        <!--El tr nos sirve sirve para crear las filas-->
        <!--El th se crea la cabecera-->
        <thead>
            <tr>
                <th>Documento</th>
                <th>Nombre</th>
                <th>Tipo de Entrada</th>
                <th>Fecha y Hora de Ingreso</th>
                <th>Fecha y Hora de Salida</th>
                <th>Placa</th>
                <th>Serial</th>
            </tr>
        </thead>

        <?php foreach ($resultado1 as $usu) { ?>
            <tr>
                <td><?= $usu['documento'] ?></td>
                <td><?= $usu['nombres'] ?></td>
                <td><?= $usu['nom_tipo'] ?></td>
                <td><?= date("Y-m-d H:i:s", strtotime($usu['entrada_fecha_hora'])) ?></td>
                <td><?= !empty($usu['salida_fecha_hora']) ? date("Y-m-d H:i:s", strtotime($usu['salida_fecha_hora'])) : 'No registrado' ?></td>
                <td><?= !empty($usu['id_placa']) ? $usu['id_placa'] : 'No' ?></td>
                <td><?= !empty($usu['serial']) ? $usu['serial'] : 'No' ?></td>
            </tr>
        <?php } ?>
    </table>
    <div class="text-center" role="toolbar" aria-label="Toolbar with button groups">
        <div class="btn-group me-2" role="group" aria-label="First group">
            <?php for ($i = 1; $i <= $total_paginas; $i++) {
                echo "<a class='btn btn-primary' href='t_cliente.php?pagina=" . $i . "'> " . $i . " </a>";
            }
            echo "<a class='btn btn-primary' href='t_cliente.php?pagina=$total_paginas'><i class='fa fa-arrow-right'></i></a>";
            ?>
        </div>
    </div>

</body>

</html>
