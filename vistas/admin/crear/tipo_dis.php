<?php
require_once ("../../../db/conexion.php");
$daba = new Database();
$conex = $daba->conectar();
session_start();

$control_documentos = $conex->prepare("SELECT documento FROM usuario WHERE id_rol IN (3, 4, 5)");
$control_documentos->execute();
$documentos = $control_documentos->fetchAll(PDO::FETCH_ASSOC);

$control = $conex->prepare("SELECT * From tipo_dispositivo");
$control->execute();
$query = $control->fetch();


if (isset($_POST["validar_V"])) {
    $tipo_dispositivo = $_POST['tipo_dispositivo'];

    if (empty($tipo_dispositivo)) {
        echo '<script>alert("Por favor, complete todos los campos.");</script>';
        echo '<script>window.location="./dispositivo.php"</script>';
        exit;
    }

    $insertsql = $conex->prepare("INSERT INTO tipo_dispositivo (nom_dispositivo) VALUES (?)");
    $insertsql->execute([$tipo_dispositivo]);    
    echo '<script>alert("Dispositivo creado exitosamente. Gracias.");</script>';
    echo '<script>window.location="./dispositivo.php"</script>';
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <meta name="description" content="">
    <meta name="author" content="">

    <link rel="shortcut icon" href="../../src/img/camara-de-cctv.png" type="image/x-icon">

    <title>Crear Color</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <style>
        .container-centered {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }
        .form-control-lg {
            height: calc(1.25em + 0.75rem + 2px); /* Ajuste para hacerlo más delgado */
            padding: 0.25rem 0.75rem; /* Ajuste del padding */
            font-size: 1rem; /* Ajuste del tamaño de fuente */
            line-height: 1.25;
            border-radius: 0.3rem;
        }
        .w-100 {
            width: 100%;
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <a class="btn btn-success" href="../index.php" style="margin-left: 3.6%; margin-top:3%; position:absolute;">
        <i class="bi bi-chevron-left" style="padding:10px 14px 10px 10px; color:#fff; font-size:15px; background-color:#29CA8E; border-radius:10px;">REGRESAR</i>
    </a>
    <div class="container container-centered">
        <div class="card o-hidden border-0 shadow-lg my-5">
            <div class="card-body p-0">
                <div class="row">
                    <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                    <div class="col-lg-20">
                        <div class="p-5">
                            <div class="text-center">
                                <h1 class="h4 mb-4">Crear Tipo Dispositivo</h1>
                            </div>
                            <form method="post" autocomplete="off" name="cli" enctype="multipart/form-data">
                                <div class="col-sm-20 mb-7 mb-sm-2">
                                    <label>Tipo Dispositivo</label>
                                    <input type="text" class="form-control form-control-lg form-control-user w-100" id="tipo_dispositivo" name="tipo_dispositivo" placeholder="Ingresa el Dispositivo" required maxlength="16" onkeypress="return sololetras(event)">
                                    <small id="tipo_dispositivoHelp" class="form-text text-danger"></small>
                                </div>
                                <input type="submit" style="margin-top:10px;"
                                class="btn btn-primary btn-user btn-block" name="Suscribir">
                                <input type="hidden" name="validar_V" value="user">
                            </form>
                            <hr>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function sololetras(e) {
            var key = e.keyCode || e.which;
            var teclado = String.fromCharCode(key).toLowerCase();
            var letras = " qwertyuiopasdfghjklñzxcvbnm";
            var especiales = [8, 37, 38, 46, 164];

            var teclado_especial = false;
            for (var i in especiales) {
                if (key == especiales[i]) {
                    teclado_especial = true;
                    break;
                }
            }

            if (letras.indexOf(teclado) == -1 && !teclado_especial) {
                return false;
            }
        }
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
