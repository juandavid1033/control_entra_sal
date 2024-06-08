<?php
require_once ("../../../db/conexion.php");
$daba = new Database();
$conex = $daba->conectar();
session_start();

// Obtener usuarios y sus roles
$control_documentos = $conex->prepare("
    SELECT usuario.documento, usuario.nombres, rol.nom_rol 
    FROM usuario 
    JOIN rol ON usuario.id_rol = rol.id_rol 
    WHERE usuario.id_rol IN (3, 4, 5)
");
$control_documentos->execute();
$usuarios = $control_documentos->fetchAll(PDO::FETCH_ASSOC);

$control = $conex->prepare("SELECT * FROM tipo_dispositivo");
$control->execute();
$tipos_dispositivo = $control->fetchAll(PDO::FETCH_ASSOC);

$control1 = $conex->prepare("SELECT * FROM marcas");
$control1->execute();
$marcas = $control1->fetchAll(PDO::FETCH_ASSOC);

$control2 = $conex->prepare("SELECT * FROM color");
$control2->execute();
$colores = $control2->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["validar_V"])) {
    $documento = $_POST['documento'];
    $serial = $_POST['serial'];
    $marca = $_POST['marca'];
    $color = $_POST['color'];
    $tipo_dispositivo = $_POST['tipo_dispositivo'];
    $imagen = $_FILES['imagen'];

    if (empty($documento) || empty($serial) || empty($marca) || empty($color) || empty($tipo_dispositivo) || empty($imagen['name'])) {
        echo '<script>alert("Por favor, complete todos los campos.");</script>';
        echo '<script>window.location="./dispositivo.php"</script>';
        exit;
    }

    $validar = $conex->prepare("SELECT * FROM dispositivos WHERE serial = ?");
    $validar->execute([$serial]);
    $queryi = $validar->fetch();

    if ($queryi) {
        echo '<script>alert("El dispositivo ya existe. Por favor, cambie el número de serie.");</script>';
        echo '<script>window.location="./dispositivo.php"</script>';
        exit;
    }

    $validar_usuario = $conex->prepare("SELECT * FROM usuario WHERE documento = ?");
    $validar_usuario->execute([$documento]);
    $queryi_usuario = $validar_usuario->fetch();

    if (!$queryi_usuario) {
        echo '<script>alert("El usuario no está registrado. No se puede asignar este dispositivo.");</script>';
        echo '<script>window.location="./dispositivo.php"</script>';
        exit;
    }

    $allowed_types = ['image/jpeg', 'image/png'];
    if (!in_array($imagen['type'], $allowed_types)) {
        echo '<script>alert("Solo se permiten archivos de imagen JPG o PNG.");</script>';
        echo '<script>window.location="./dispositivo.php"</script>';
        exit;
    }

    if ($imagen['size'] > 400000) {
        echo '<script>alert("El tamaño del archivo no puede exceder los 400kb.");</script>';
        echo '<script>window.location="./dispositivo.php"</script>';
        exit;
    }

    $target_dir = "../../../images/dispo/";
    $imagen_nombre = basename($imagen["name"]);
    $target_file = $target_dir . $imagen_nombre;
    if (!move_uploaded_file($imagen["tmp_name"], $target_file)) {
        echo '<script>alert("Hubo un error al cargar la imagen.");</script>';
        echo '<script>window.location="./dispositivo.php"</script>';
        exit;
    }

    $insertsql = $conex->prepare("INSERT INTO dispositivos (serial, imagen, id_marca, id_color, id_tipo_dispositivo, documento, estado) VALUES (?, ?, ?, ?, ?, ?, ?)");
    $insertsql->execute([$serial, $imagen_nombre, $marca, $color, $tipo_dispositivo, $documento, 1]);
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

    <title>Crear Dispositivo</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <link href="../../../css/sb-admin-2.min.css" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
    <style>
        .link-yellow {
            color: #FFC107; /* Color amarillo */
        }
    </style>
</head>

<body class="bg-gradient-primary">
    <a class="btn btn-success" href="../index.php" style="margin-left: 3.6%; margin-top:3%; position:absolute;">
        <i class="bi bi-chevron-left" style="padding:10px 14px 10px 10px; color:#fff; font-size:15px; background-color:#29CA8E; border-radius:10px;">REGRESAR</i>
    </a><br><br><br>
    <form method="post" autocomplete="off" name="cli" enctype="multipart/form-data">
        <div class="container">
            <div class="card o-hidden border-0 shadow-lg my-6">
                <div class="card-body p-0">
                    <div class="row">
                        <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                        <div class="col-lg-10">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 mb-4">Asignar Dispositivo</h1>
                                </div>
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
                                        <label>Serial</label>
                                        <input type="text" class="form-control form-control-user" id="serial" name="serial" placeholder="Ingresa serial del dispositivo" required oninput="validarSerial(this)" maxlength="16">
                                        <small id="serialHelp" class="form-text text-danger"></small>
                                    </div>
                                    <div class="col-sm-6 mb-3 mb-sm-2">
                                        <label>Imagen</label>
                                        <input type="file" class="form-control-file" name="imagen" accept="image/*" required>
                                    </div>
                                    <div class="col-sm-6 mb-3 mb-sm-2">
                                        <label>Marca
                                            <a href="./marca.php" class="link-yellow">Crear</a>
                                        </label>
                                        <select name="marca" class="form-control form-control-user" required>
                                            <option value="">Elegir</option>
                                            <?php foreach ($marcas as $marca): ?>
                                                <option value="<?php echo $marca['id_marca']; ?>">
                                                    <?php echo $marca['nom_marca']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-6 mb-3 mb-sm-2">
                                        <label>Color del Dispositivo
                                            <a href="./color.php" class="link-yellow">Crear</a>
                                        </label>
                                        <select name="color" class="form-control form-control-user" required>
                                            <option value="">Elegir</option>
                                            <?php foreach ($colores as $color): ?>
                                                <option value="<?php echo $color['id_color']; ?>">
                                                    <?php echo $color['nom_color']; ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-sm-6  mb-3 mb-sm-2">
                                            <label>Tipo del Dispositivo
                                            <a href="./tipo_dis.php" class="link-yellow">Crear</a>
                                            </label>
                                            <select name="tipo_dispositivo" class="form-control form-control-user" id="exampleFirstName" required>
                                                <option value="">Elegir</option>
                                                <?php foreach ($tipos_dispositivo as $tipo): ?>
                                                    <option value="<?php echo $tipo['id_tipo_dispositivo']; ?>">
                                                        <?php echo $tipo['nom_dispositivo']; ?>
                                                    </option>
                                                <?php endforeach; ?>
                                            </select>
                                        </div>
                                    <script>
                                        function validarSerial(input) {
                                            var serial = input.value;
                                            var regex = /^[A-Za-z0-9]*$/;
                                            if (!regex.test(serial)) {
                                                document.getElementById("serialHelp").innerText = "El serial solo puede contener letras y números.";
                                                input.setCustomValidity("El serial solo puede contener letras y números.");
                                            } else {
                                                document.getElementById("serialHelp").innerText = "";
                                                input.setCustomValidity("");
                                            }
                                        }
                                    </script>

                                    <!-- SOLO NUMERO,LONGITUD -->
                                    <script>
                                        function maxlengthNumber(obj) {
                                            console.log(obj.value);
                                            if (obj.value.length > obj.maxLength) {
                                                obj.value = obj.value.slice(0, obj.maxLength);
                                            }
                                        }
                                    </script>

                                    <!-- LONGITUD DE LETRA -->
                                    <script>
                                        function validaletras(obj) {
                                            console.log(obj.value);
                                            if (obj.value.length > obj.maxLength) {
                                                obj.value = obj.value.slice(0, obj.maxLength);
                                            }
                                        }
                                    </script>

                                    <!-- SOLO LETRA (ESPACIO DE LETRAS SE HACE EN LETRAS) -->
                                    <script>
                                        function sololetras(e) {
                                            key = e.keyCode || e.which;

                                            teclado = String.fromCharCode(key).toLowerCase();

                                            letras = "qwertyuiopasdfghjklñzxcvbnm ";

                                            especiales = "8-37-38-46-164-46";

                                            teclado_especial = false;

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

                                    <!-- SOLO NUMEROS () -->
                                    <script>
                                        function solonumeros(e) {
                                            key = e.keyCode || e.which;

                                            teclado = String.fromCharCode(key).toLowerCase();

                                            letras = "0123456789.";

                                            especiales = "8-37-38-46-164-46";

                                            teclado_especial = false;

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

                                    <!-- SOLO NUMERO -->
                                    <script>
                                        $(function () {
                                            $('input[type=number]').keypress(function (key) {
                                                if (key.charCode < 48 || key.charCode > 57)
                                                    return false;
                                            });
                                        });
                                    </script>

                                        <input type="submit" style="margin-top:10px;" class="btn btn-primary btn-user btn-block" name="Suscribir">
                                        <input type="hidden" name="validar_V" value="user">
                                </form>
                                <hr>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </form>


    <!-- Bootstrap core JavaScript-->
    <script src="vendor/jquery/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>

    <!-- Core plugin JavaScript-->
    <script src="vendor/jquery-easing/jquery.easing.min.js"></script>

    <!-- Custom scripts for all pages-->
    <script src="js/sb-admin-2.min.js"></script>

</body>

</html>
