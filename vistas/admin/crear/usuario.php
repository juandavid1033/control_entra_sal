<?php
require_once("../../../db/conexion.php");
$daba = new Database();
$conex = $daba->conectar();

$control2 = $conex->prepare("SELECT * From rol LIMIT 1, 7");
$control2->execute();
$query2 = $control2->fetchAll(PDO::FETCH_ASSOC);

$control3 = $conex->prepare("SELECT * From tipo_documento");
$control3->execute();
$query3 = $control3->fetchAll(PDO::FETCH_ASSOC);

$control6 = $conex->prepare("SELECT * From empresas LIMIT 1");
$control6->execute();
$query6 = $control6->fetchAll(PDO::FETCH_ASSOC);

if (isset($_POST["validar_V"])) {
    $cedula = $_POST['documento'];
    $nombres = $_POST['nombres'];
    $correo = $_POST['correo'];
    $contra = $_POST['contrasena'];
    $rol = $_POST['rol'];
    $tipo = $_POST['tipo'];
    $nit_empresa = $_POST['nit_empresa'];

    // Validar que no haya campos vacíos
    if ($cedula == "" || $nombres == "" || $correo == "" || $contra == "" || $rol == "" || $tipo == "" || $nit_empresa == "") {
        echo '<script>alert("EXISTEN DATOS VACIOS");</script>';
        echo '<script>window.location="./usuario.php"</script>';
    } else {
        // Verificar si el documento ya existe en la base de datos
        $validarDocumento = $conex->prepare("SELECT * FROM usuario WHERE documento = ?");
        $validarDocumento->execute([$cedula]);
        $queryDocumento = $validarDocumento->fetch();

        if ($queryDocumento) {
            echo '<script>alert("El documento ya está registrado. Por favor, elija otro.");</script>';
            echo '<script>window.location="./usuario.php"</script>';
        } else {
            // Verificar si el correo ya existe en la base de datos
            $validarCorreo = $conex->prepare("SELECT * FROM usuario WHERE correo = ?");
            $validarCorreo->execute([$correo]);
            $queryCorreo = $validarCorreo->fetch();

            if ($queryCorreo) {
                echo '<script>alert("El correo electrónico ya está registrado. Por favor, elija otro.");</script>';
                echo '<script>window.location="./usuario.php"</script>';
            } else {
                // Si el documento y el correo no están registrados, procedemos con la inserción del nuevo usuario
                $hashed_password = password_hash($contra, PASSWORD_BCRYPT, ['cost' => 14]);
                $insertsql = $conex->prepare("INSERT INTO usuario (documento, codigo_barras, nombres, contrasena, id_rol, id_estados, correo, id_tipo_documento, nit_empresa) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)");
                $insertsql->execute([$cedula, $cedula, $nombres, $hashed_password, $rol, 1, $correo, $tipo, $nit_empresa]);
                echo '<script>alert("Usuario creado exitosamente. Gracias.");</script>';
                echo '<script>window.location="../index.php"</script>';
            }
        }
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
    <title>Crear Usuario</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <!-- Custom fonts for this template-->
    <link href="vendor/fontawesome-free/css/all.min.css" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:200,200i,300,300i,400,400i,600,600i,700,700i,800,800i,900,900i" rel="stylesheet">
    <!-- Custom styles for this template-->
    <link href="../../../css/sb-admin-2.min.css" rel="stylesheet">
    <!-- dirección para que funcione solo número -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
</head>
<body class="bg-gradient-primary">
    <a class="btn btn-success" href="../index.php" style="margin-left: 3.6%; margin-top:3%; position:absolute;">
        <i class="bi bi-chevron-left" style="padding:10px 14px 10px 10px; color:#fff; font-size:15px; background-color:#29CA8E; border-radius:10px;">REGRESAR</i>
    </a><br><br><br>
    <form method="post" autocomplete="off" name="cli" enctype="multipart/form-data" onsubmit="return validarContrasena();">
        <div class="container">
            <div class="card o-hidden border-0 shadow-lg my-6">
                <div class="card-body p-0">
                    <!-- Nested Row within Card Body -->
                    <div class="row">
                        <div class="col-lg-5 d-none d-lg-block bg-register-image"></div>
                        <div class="col-lg-7">
                            <div class="p-5">
                                <div class="text-center">
                                    <h1 class="h4 mb-4">Crear Usuario</h1>
                                </div>
                                <form class="user">
                                    <div class="form-group row">
                                        <div class="col-sm-6">
                                            <label>Tipo de Documento</label>
                                            <select name="tipo" class="form-control form-control-user" id="exampleFirstName" required>
                                                <option value="">Elegir</option>
                                                <?php
                                                foreach ($query3 as $tipoDocumento) {
                                                ?>
                                                <option value="<?php echo ($tipoDocumento['id_tipo_documento']) ?>">
                                                    <?php echo ($tipoDocumento['nom_doc']) ?>
                                                </option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label>Documento</label>
                                            <input type="text" style="margin-bottom:5px;" class="form-control form-control-user" id="documento" name="documento" placeholder="Documento" required minlength="8" maxlength="11" pattern="\d{8,11}" onkeypress="return event.charCode >= 48 && event.charCode <= 57">
                                            <small class="form-text text-muted">El documento debe contener entre 8 y 11 números.</small>
                                        </div>
                                        <div class="col-sm-6">
                                            <label>Nombres</label>
                                            <input type="text" class="form-control" name="nombres" required pattern="[a-zA-Z\s]{1,30}" maxlength="30" oninput="validateInput(event)">
                                        </div>
                                        <div class="col-sm-6">
                                            <label>Correo</label>
                                            <input type="email" class="form-control form-control-user" id="correo" name="correo" placeholder="Correo electrónico" required>
                                        </div>
                                        <div class="col-sm-6">
                                            <label>Rol</label>
                                            <select name="rol" class="form-control form-control-user" id="exampleFirstName" required>
                                                <option value="">Elegir</option>
                                                <?php
                                                foreach ($query2 as $rol) {
                                                ?>
                                                <option value="<?php echo ($rol['id_rol']) ?>">
                                                    <?php echo ($rol['nom_rol']) ?>
                                                </option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        <div class="col-sm-6">
                                            <label>Nit Empresa</label>
                                            <select name="nit_empresa" class="form-control form-control-user" id="exampleFirstName" required style="width: 100%;">
                                                <option value="">Elegir</option>
                                                <?php
                                                foreach ($query6 as $empresa) {
                                                ?>
                                                <option value="<?php echo ($empresa['nit_empresa']) ?>">
                                                    <?php echo ($empresa['nombre']) ?>
                                                </option>
                                                <?php
                                                }
                                                ?>
                                            </select>
                                        </div>
                                        
                                        <div class="form-group">
                                            <label for="contrasena">Contraseña</label>
                                            <input type="password" class="form-control" name="contrasena" required minlength="8" pattern="^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)[a-zA-Z\d]{8,}$" oninput="validatePassword(event)">
                                            <small class="text-muted">Debe contener al menos 8 caracteres, incluyendo al menos una letra mayúscula, una letra minúscula y un número.</small>
                                        </div>
                                    </div>
                                    <input type="submit" style="margin-top:10px;" class="btn btn-primary btn-user btn-block" name="Suscribir">
                                    <input type="hidden" name="validar_V" value="cli">
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
    function validarContrasena
