<?php
require_once("../../../db/conexion.php");
$daba = new Database();
$conex = $daba->conectar();

$control_documentos = $conex->prepare("
    SELECT usuario.documento, usuario.nombres, rol.nom_rol 
    FROM usuario 
    JOIN rol ON usuario.id_rol = rol.id_rol 
    WHERE usuario.id_rol IN (3, 4, 5)
");

$control_documentos->execute(); // Ejecutar la consulta

// Verificar si hay resultados
if ($control_documentos->rowCount() > 0) {
    // Recorrer los resultados y mostrarlos en el select
    $usuarios = $control_documentos->fetchAll();
} else {
    // Manejar el caso donde no hay resultados
    $usuarios = []; // Inicializar $usuarios como un arreglo vacío
}

$control_tipos = $conex->prepare("SELECT * FROM tipo_vehiculo ");
$control_tipos->execute();
$tipos_vehiculo = $control_tipos->fetchAll();

$control_marcas = $conex->prepare("SELECT * FROM marca_vehi"); 
$control_marcas->execute();
$marcas_vehiculo = $control_marcas->fetchAll();

$control_colores = $conex->prepare("SELECT * FROM color ");
$control_colores->execute();
$colores_vehiculo = $control_colores->fetchAll();

?>

<?php
if (isset($_POST["validar_V"]) && $_POST["validar_V"] === "envia") {
    $cedula = $_POST['documento'];
    $placa = $_POST['placa'];
    $marca = $_POST['marca'];
    $color = $_POST['color'];
    $tipovehiculo = $_POST['tipovehiculo'];

    $validar = $conex->prepare("SELECT * FROM vehiculos where id_placa ='$placa' ");
    $validar->execute();
    $queryi = $validar->fetch();

    $validar4 = $conex->prepare("SELECT * FROM usuario WHERE documento='$cedula' ");
    $validar4->execute();
    $queryi4 = $validar4->fetch();

    if ($cedula == "" || $placa == "" || $marca == "" || $color == ""  || $tipovehiculo == "" ) {

        echo '<script>alert ("EXISTEN DATOS VACIOS");</script>';
        
    } else if ($queryi) {
        echo '<script>alert ("El Vehiculo YA EXISTEN // CAMBIELO//");</script>';
        
    } elseif ($queryi4) {
        $insertsql = $conex->prepare("INSERT INTO vehiculos(documento,id_placa,id_marca,id_color,id_tipo_vehiculo) VALUES (?,?,?,?,?)");
        $insertsql->execute([$cedula, $placa, $marca,$color, $tipovehiculo]);
        echo '<script>alert ("Vehiculo Creado exitosamente, Gracias");</script>';
        
    } else {
        echo '<script>alert ("El usuario no esta registrado // asi que no puede asignar este vehiculo ");</script>';
        

    }
}

?>

<!DOCTYPE html>
<html lang="en">

<head>

    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="shortcut icon" href="../../src/img/camara-de-cctv.png" type="image/x-icon">

    <title>Crear Vehiculo</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">

    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i"
        rel="stylesheet">

    <!-- Custom styles for this template-->
    <link href="../../../css/sb-admin-2.min.css" rel="stylesheet">

    <!-- direccion para que funcione solo numero -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>

    <style>
        .link-yellow {
            color: yellow;
        }
    </style>

</head>

<body class="bg-gradient-primary">
    <a class="btn btn success" href="../index.php" style="margin-left: 3.6%; margin-top:3%; position:absolute;">
        <i class="bi bi-chevron-left"
            style="padding:10px 14px 10px 10px; color:#fff; font-size:15px; background-color:#29CA8E; border-radius:10px;">
            REGRESAR</i>
    </a><br><br><br>
    <form method="post" autocomplete="off" name="cli" id="formVehiculo">
        <div class="container">
            <div class="card o-hidden border-0 shadow-lg my-6">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                        <div class="col-lg-10">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 mb-4">Asignar Vehiculo</h1>
                                </div>
                                <form class="user">
                                    <div class="form-group row">
                                        <div class="col-sm-6 mb-3 mb-sm-2">
                                            <label>Persona</label>
                                            <select name="documento" class="form-control" id="tipoEntrada" required>
                                                <option>Elegir</option>
                                                <?php foreach ($usuarios as $usuario): ?>
                                                    <option value="<?php echo $usuario['documento']; ?>">
                                                        <?php echo $usuario['documento']; ?> - 
                                                        <?php echo $usuario['nombres']; ?> - 
                                                        <?php echo $usuario['nom_rol']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6 mb-3 mb-sm-2">
                                            <label>Placa</label>
                                            <input type="text" class="form-control form-control-user" id="placa" name="placa" placeholder="Ingresa la placa del vehiculo" required>
                                            <small id="placaHelp" class="form-text text-danger"></small>
                                        </div>

                                        <div class="col-sm-6  mb-3 mb-sm-2">
                                            <label>Marca
                                                <a href="./marca_vehi.php" class="link-yellow">Crear</a>
                                            </label>
                                            <select name="marca" class="form-control form-control-user" id="exampleFirstName" required>
                                                <option value="">Elegir</option>
                                                <?php foreach ($marcas_vehiculo as $marca): ?>
                                                    <option value="<?php echo $marca['id_marca']; ?>"><?php echo $marca['nom_mar']; ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                        </div>
                                        <div class="col-sm-6  mb-3 mb-sm-2">
                                            <label>Color del Vehiculo
                                                <a href="./cor_veh.php" class="link-yellow">Crear</a>
                                            </label>
                                            <select name="color" class="form-control form-control-user" id="exampleFirstName" required>
                                                <option value="">Elegir</option>
                                                <?php foreach ($colores_vehiculo as $color): ?>
                                                    <option value="<?php echo $color['id_color']; ?>"><?php echo $color['nom_color']; ?></option>
                                                <?php endforeach; ?>
                                            </select>

                                        </div>
                                        <div class="col-sm-6  mb-3 mb-sm-2">
                                            <label>Tipo del Vehiculo
                                                <a href="./tipo_vehic.php" class="link-yellow">Crear</a>
                                            </label>
                                            <select name="tipovehiculo" class="form-control form-control-user" id="exampleFirstName" required>
                                                <option value="">Elegir</option>
                                                <?php foreach ($tipos_vehiculo as $tipo): ?>
                                                    <option value="<?php echo $tipo['id_tipo_vehiculo']; ?>"><?php echo $tipo['nom_vehiculo']; ?></option>
                                                <?php endforeach; ?>
                                            </select>


                                        </div>
                                        <input type="submit" style="margin-top:10px;"
                                            class="btn btn-primary btn-user btn-block" name="validar_V" value="envia">
                                        <input type="hidden" name="validar_V" value="envia">
                                </form>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>

    <script>
        // FUNCION DE JAVASCRIPT PARA VALIDAR LOS AÑOS DE RANGO PARA LA FECHA DE NACIMIENTO
        var fechaInput = document.getElementById('fecha');
        // Calcular las fechas mínima y máxima permitidas
        var fechaMaxima = new Date();
        fechaMaxima.setFullYear(fechaMaxima.getFullYear() - 14); // Restar 18 años para que la persona se registre 
        var fechaMinima = new Date();
        fechaMinima.setFullYear(fechaMinima.getFullYear() - 60); // Restar 80 años
        // Formatear las fechas mínima y máxima en formato de fecha adecuado (YYYY-MM-DD)
        var fechaMaximaFormateada = fechaMaxima.toISOString().split('T')[0];
        var fechaMinimaFormateada = fechaMinima.toISOString().split('T')[0];

        // Establecer los atributos min y max del campo de entrada de fecha
        fechaInput.setAttribute('min', fechaMinimaFormateada);
        fechaInput.setAttribute('max', fechaMaximaFormateada);
    </script>
    
    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>

 
