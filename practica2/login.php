<?php
//para deshabilitar la proteccion del navegador
header('X-XSS-Protection:0');

require_once("../lib/my_lib.inc.php");

if (isset($_GET['Submit'])) {
    $email = $_GET['email'];
    $password = $_GET['password'];

    $mysqli = connectDB();

    $sql = "SELECT usuario_id, nombre, apellido FROM usuarios WHERE email = '$email' and password='$password'";
    $resultado =  $mysqli->query($sql) or trigger_error("Query falló! SQL: $sql - Error: ".mysqli_error(), E_USER_ERROR);

    if ($resultado->num_rows === 0) {
        $mensaje = "No existe un usuario con ese correo y contraseña";
    } else {
        $usuario = $resultado->fetch_assoc();
        $mensaje = "Bienvenid@ {$usuario['nombre']}";
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
    <h3>Autenticación</h3>
    <?php if (isset($mensaje)) :
        echo '<div class="alert alert-info" role="alert">'.$mensaje.'</div>';
    endif; ?>
    <form>
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" name="email" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" required class="form-control">
        </div>
        <input type="submit" value="Aceptar" name="Submit" class="btn btn-primary">
    </form>
    </div>
</body>
</html>
