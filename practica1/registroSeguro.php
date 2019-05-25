<?php
//para deshabilitar la proteccion del navegador
header('X-XSS-Protection:0');
require_once("../lib/my_lib.inc.php");
session_start();

/**
 * Valida los datos que se reciben del formulario de registro de nuevo usuario
 *
 * @param  [array] $data Datos a validar del formulario de registro
 * @return bool True si los datos están bien, false en caso contrario
 */
function validateForm($data)
{

    //Se valida que los datos no esten vacios
    foreach ($data as $key => $value) {
        if (!requerido($value)) {
            return false;
        }
    }

    //Se revisa que sea un email valido
    if (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return true;
}

if (isset($_POST['Submit'])) {
    if (csrf_token_is_valid() && csrf_token_is_recent()) {
        //Validación de datos
        if (validateForm($_POST)) {
            $nombre = $_POST['nombre'];
            $apellido = $_POST['apellido'];
            $email = $_POST['email'];
            $password = $_POST['password'];

            //Conexion a la base
            $mysqli = connectDB();

            //Se valida que las contraseñas sean iguales
            if ($_POST['password'] == $_POST['password_confirm']) {
                //Saneado de datos
                //Codificacion de caracteres html
                $email = htmlspecialchars($email);
                $nombre = htmlspecialchars($nombre);
                $apellido = htmlspecialchars($apellido);

                //Preparacion de consulta
                $sql =  'INSERT INTO usuarios (nombre, apellido, email, password) VALUES (?, ?, ?, ?)';
                $sentencia = $mysqli->prepare($sql) or trigger_error("Query falló! SQL: $sql - Error: ".$mysqli->error);

                if ($sentencia) {
                    //Cifrado de la contrasenia
                    $password = password_hash($password, PASSWORD_DEFAULT);

                    //Asociacion de variables con sus respectivos lugares en la consulta almacenada
                    $sentencia->bind_param('ssss', $nombre, $apellido, $email, $password);

                    //ejecución de la consulta
                    if ($sentencia->execute() or trigger_error("Query falló! SQL: $sql - Error: ".$mysqli->error)) {
                        $mensaje = "¡Gracias por registrarte $nombre $apellido!";
                    } else {
                        $errorMessage = "Ha ocurrido un error al guardar los datos.";
                    }
                } else {
                    $errorMessage = "Ha ocurrido un error al guardar los datos.";
                }
            } else {
                $errorMessage = "Las contraseñas no coinciden.";
            }
            //Se cierra conexion con base de datos
            $mysqli->close();
        } else {
            $errorMessage = "Hubo un error en la validación de los datos.";
        }
    } else {
        $errorMessage = "Token inválido.";
    }
}

generateSessionToken();
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
    <?php
    if (isset($mensaje)) :
        echo '<div class="alert alert-info" role="alert">'. $mensaje .'</div>';
    endif;
    if (isset($errorMessage)) :
        echo '<div class="alert alert-danger" role="alert">'.$errorMessage.'</div>';
    endif;
    ?>
    <form method="post">
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
        <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ""; ?>">
        <input type="submit" value="Aceptar" name="Submit" class="btn btn-primary">
    </form>
    </div>
</body>
</html>
