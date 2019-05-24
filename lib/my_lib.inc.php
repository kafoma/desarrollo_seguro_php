<?php

/**
 * Realiza la conexión a la base de datos y regresa el identificador de la conexion
 *
 * @return void
 */
function connectDB()
{
    $mysqli = new mysqli('127.0.0.1', 'root', '', 'taller_seguridad');

    if ($mysqli->connect_errno) {
        echo "Error: Failed to make a MySQL connection, here is why: \n";
        echo "Errno: " . $mysqli->connect_errno . "\n";
        echo "Error: " . $mysqli->connect_error . "\n";
    }

    return $mysqli;
}

/**
  * Valida que se haya ingresado un valor
  *
  * @param [type] $valor
  * @return bool True si se ingresó un valor False en caso contrario
  */
function requerido($valor)
{
    $trimmedValue = trim($valor);
    return isset($trimmedValue) && $trimmedValue !== "";
}

/**
 * Valida que el valor se encuentre dentro de la lista especificada
 * @param mixed $valor Valor que se debe validar
 * @param array $set Lista de valores donde se desea buscar el valor
 *
 * @return True si el valor se encuentra en la lista, False en caso contrario
 */

/**
 * Valida que el valor se encuentre dentro de la lista especificada
 *
 * @param [type] $valor
 * @param array $set Lista de valores donde se desea buscar el valor
 * @return void
 */
function enLista($valor, $set = [])
{
    return in_array($valor, $set);
}


/**
 * Valida la longitud de una cadena, de acuerdo a la opción
 * especificada en el segundo parametro
 *
 * Ejemplos:
 * longitud($curp, ['exacto' => 18])
 * longitud($nombre, ['min' => 5, 'max' => 100])
 *
 * @param mixed $valor Valor que se debe validar
 * @param array $opciones Arreglo para especificar opciones 'exacto', 'min', 'max' (obligatorio especificar)
 * @return True si el valor cumple con la longitud, False en caso contrario
 */
function longitud($valor, $opciones)
{
    if (isset($opciones['max']) && (strlen($valor) > (int)$opciones['max'])) {
        return false;
    }
    if (isset($opciones['min']) && (strlen($valor) < (int)$opciones['min'])) {
        return false;
    }
    if (isset($opciones['exacto']) && (strlen($valor) != (int)$opciones['exacto'])) {
        return false;
    }
    return true;
}

/**
 * Valida que el valor cumpla con el formato especificado en la expresion regular
 *
 *
 * @param mixed $valor Valor que se debe validar
 * @param string $regex Expresion regular contra la que se desea validar
 *
 * @return True si el valor cumple con la expresion regular, False en caso contrario
 */
function formato($valor, $regex = '//')
{
    return preg_match($regex, $valor);
}

/**
 * Valida que el valor sea un número entero
 *
 * @param mixed $valor Valor que se debe validar
 * @param array $opciones Arreglo para especificar opcionalmente 'min' y 'max'
 *
 * @return True si el valor es un numero entero y cumple las especificaciones, False en caso contrario
 */
function numeroEntero($valor, $opciones = [])
{
    if (!is_numeric($valor)) {
        return false;
    }
    if (isset($opciones['max']) && ($valor > (int)$opciones['max'])) {
        return false;
    }
    if (isset($opciones['min']) && ($valor < (int)$opciones['min'])) {
        return false;
    }
    return true;
}

/**
 * FUNCIONES ANTI CSRF
 */

 /**
 * Genera token anti-CSRF y lo almacena en la sesión
 *
 * @return void
 */
function generateSessionToken()
{
    if (isset($_SESSION['csrf_token'])) {
        unset($_SESSION['csrf_token']);
    }
    $_SESSION['csrf_token'] = md5(uniqid(rand(), true));
    $_SESSION['csrf_token_time'] = time();
}

/**
 * Verifica si el token recibido por POST es igual al de la SESION
 *
 * @return void
 */
function csrf_token_is_valid()
{
    if (isset($_POST['csrf_token'])) {
        $user_token = $_POST['csrf_token'];
        $stored_token = $_SESSION['csrf_token'];
        return $user_token === $stored_token;
    }

    return false;
}

/**
 * Verifica que el token sea reciente, máximo 1 dia de vigencia
 *
 * @return void
 */
function csrf_token_is_recent()
{
    $max_elapsed = 60 * 60 * 24; // 1 dia
    if (isset($_SESSION['csrf_token_time'])) {
        $stored_time = $_SESSION['csrf_token_time'];
        return ($stored_time + $max_elapsed) >= time();
    } else {
        // Eliminar token expirado
        destroy_csrf_token();
        return false;
    }
}
