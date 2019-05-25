<?php
//para deshabilitar la proteccion del navegador
header('X-XSS-Protection:0');

require_once("../lib/my_lib.inc.php");
session_start();

// Valores por defecto
   $total_failed_login = 3;
   $lockout_time       = 15;
/**
 * [Valida los datos que se pasan como parametro]
 * @param  [string] $email    [email a validar]
 * @param  [string] $password [contraseña a validar]
 * @return [Bool] true o fals [resultado]
 */
function formValidation($email,$password){
    //Se valida que los datos no esten vacios
    if (!requerido($email) || !requerido($email)) {
        return false;
    }

    //Se revisa que sea un email valido
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        return false;
    }

    return true;
}

function failedLogin($mysqli, $total_failed_login, $lockout_time, $email){
    //Se revisan sesiones fallidas y ultimo acceso
    $data = $mysqli->prepare( 'SELECT intentos_fallidos, ultimo_acceso FROM usuarios WHERE email = ?' );
    $data->bind_param('s', $email);
    $data->execute();
    $data->bind_result($usuario['intentos_fallidos'], $usuario['ultimo_acceso']);
    $data->fetch();
    $data->close();

    if ($usuario['intentos_fallidos'] > $total_failed_login) {
        // Se calcula si el usuario puede volver a conectarse
        $last_login = strtotime($usuario['ultimo_acceso']);
        $timeout    = $last_login + ($lockout_time * 60 * 60);
        $timenow    = time();
        // Si el tiempo suficiente no ha pasado, se bloquea
        if( $timenow < $timeout ) {
            return false;
        }else{
            return true;
        }
        return false;
    }

    return true;
}

if (isset($_POST['Submit'])) {
    $email = $_POST['email'];
    $password = $_POST['password'];
    if (csrf_token_is_valid() && csrf_token_is_recent()) {
      //Se validan los datos
      if (formValidation($email,$password)) {

          //Se hace conexion con la base
          $mysqli = connectDB();
          //Codificacion de caracteres html
          $email = htmlentities($email);
          //Se escapan los caracteres especiales de una cadena para usarla en una sentencia SQL
          $email = $mysqli->real_escape_string($email);

          if (failedLogin($mysqli, $total_failed_login, $lockout_time, $email)) {
              //Se hacen consultas preparadas
              $data = $mysqli->prepare( 'SELECT usuario_id, nombre, apellido, password FROM usuarios WHERE email = ?' );
              $data->bind_param('s', $email);
              $data->execute();
              $data->bind_result($usuario['id'], $usuario['nombre'], $usuario['apellido'], $usuario['password']);
              $data->fetch();
              $data->close();

              //Si usuario no existe
              if (empty($usuario)) {
                  $mensaje = "No existe un usuario con ese correo y contraseña";
              //Si contrasenia no es correcta
              }else{
                  if(!password_verify($password, $usuario['password'])){
                      $mensaje = "Usuario o contraseña equivocados";
                      //Se suma un intento fallido
                      $data = $mysqli->prepare( 'UPDATE usuarios SET intentos_fallidos = (intentos_fallidos + 1) WHERE email = ?' );
                      $data->bind_param('s', $email);
                  $data->execute();
                  }
                  else {
                      $mensaje = "Bienvenid@ {$usuario['nombre']}";
                      //Se limpia registro de intentos fallidos
                  $data = $mysqli->prepare( 'UPDATE usuarios SET intentos_fallidos = "0" WHERE email = ?' );
                  $data->bind_param('s', $email);
                  $data->execute();
                  }
              }
          }else{
              $errorMessage = "Cuenta bloqueada por exceso de intentos fallidos";
          }
          //se registra el ultimo intento de inicio de sesion
          $data = $mysqli->prepare( 'UPDATE usuarios SET ultimo_acceso = now() WHERE email = ?' );
          $data->bind_param('s', $email);
          $data->execute();
          //Se cierra conexion
          $mysqli->close();
      }else{
          $errorMessage = "Error en la validación de los datos";
      }
    }else {
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
    <h3>Autenticación</h3>
    <?php
        if (isset($mensaje)) :
            echo '<div class="alert alert-info" role="alert">'.$mensaje.'</div>';
        endif;
        if (isset($errorMessage)) :
            echo '<div class="alert alert-danger" role="alert">'.$errorMessage.'</div>';
        endif;
    ?>
    <form method="post">
        <div class="form-group">
            <label for="email">Email</label>
            <input type="text" name="email" required class="form-control">
        </div>
        <div class="form-group">
            <label for="password">Contraseña</label>
            <input type="password" name="password" required class="form-control">
        </div>
        <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ""; ?>">
        <input type="submit" value="Aceptar" name="Submit" class="btn btn-primary">
    </form>
    </div>
</body>
</html>
