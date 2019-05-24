<?php
//para deshabilitar la proteccion del navegador
header('X-XSS-Protection:0');
require_once("../lib/my_lib.inc.php");

if (isset($_GET['Submit'])) {
    $nombre = $_GET['nombre'];
    $apellido = $_GET['apellido'];
    $email = $_GET['email'];
    $password = $_GET['password'];

    //Conexion a la base
    $mysqli = connectDB();

    if ($_GET['password'] == $_GET['password_confirm']) {
        $sql = "INSERT INTO usuarios (nombre, apellido, email, password) VALUES ('$nombre', '$apellido', '$email', '$password')";
        $resultado =  $mysqli->query($sql) or die("Query falló! SQL: $sql - Error: ".$mysqli->error);
        $mensaje = "¡Gracias por registrarte $nombre $apellido!";
    } else {
        $mensaje = "Las contraseñas no coinciden.";
    }

    $mysqli->close();
}
?>

<!doctype html>
<html lang="es">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">

  <link rel="stylesheet" href="../assets/css/bootstrap.min.css">
  <title>Taller de desarrollo seguro con PHP</title>
</head>

<body>
    <div class="container">
    <h3>Registro de usuario</h3>
    <?php if (isset($mensaje)) :
        echo '<div class="alert alert-info" role="alert">'.$mensaje.'</div>';
    endif; ?>
    <form>
        <div class="form-group">
            <label for="nombre">Nombre</label>
            <input type="text" name="nombre" required class="form-control">
        </div>
        <div class="form-group">
            <label for="apellido">Apellido</label>
            <input type="text" name="apellido" required class="form-control">
        </div>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" name="email" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password_confirm">Ingresa nuevamente la contraseña</label>
            <input type="password" name="password_confirm" required class="form-control">
        </div>
        <input type="submit" value="Aceptar" name="Submit" class="btn btn-primary">
    </form>
    </div>
</body>
</html>
