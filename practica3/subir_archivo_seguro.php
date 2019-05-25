<?php
//para deshabilitar la proteccion del navegador
header('X-XSS-Protection:0');
require_once("../lib/my_lib.inc.php");
session_start();

// Revisar que se cuenta con la extensión GD utilizada para las imagenes
if (( !extension_loaded('gd') || !function_exists('gd_info') )) {
    trigger_error("No está instalado el módulo GD", E_USER_NOTICE);
}

if (isset($_POST['Submit'])) {
    if (csrf_token_is_valid() && csrf_token_is_recent()) {
        $uploaded_name = $_FILES[ 'archivo' ][ 'name' ];
        $uploaded_ext  = substr($uploaded_name, strrpos($uploaded_name, '.') + 1);
        $uploaded_size = $_FILES[ 'archivo' ][ 'size' ];
        $uploaded_type = $_FILES[ 'archivo' ][ 'type' ];
        $uploaded_tmp  = $_FILES[ 'archivo' ][ 'tmp_name' ];


        $target_path   = './archivos/';
        $target_file   =  md5(uniqid() . $uploaded_name) . '.' . $uploaded_ext;
        $temp_file     = ( ( ini_get('upload_tmp_dir') == '' ) ? ( sys_get_temp_dir() ) : ( ini_get('upload_tmp_dir') ) );
        $temp_file    .= DIRECTORY_SEPARATOR . md5(uniqid() . $uploaded_name) . '.' . $uploaded_ext;


        //Validar que sea una imagen
        if (( strtolower($uploaded_ext) == 'jpg' || strtolower($uploaded_ext) == 'jpeg' || strtolower($uploaded_ext) == 'png' ) &&
        ( $uploaded_size < 100000 ) &&
        ( $uploaded_type == 'image/jpeg' || $uploaded_type == 'image/png' ) &&
        getimagesize($uploaded_tmp) ) {
            // Se re-codifica la imagen para quitar cualquier metadato que pudiera tener
            // Nota, se recomienda usar php-Imagick en lugar de php-GD
            if ($uploaded_type == 'image/jpeg') {
                $img = imagecreatefromjpeg($uploaded_tmp);
                imagejpeg($img, $temp_file, 100);
            } else {
                $img = imagecreatefrompng($uploaded_tmp);
                imagepng($img, $temp_file, 9);
            }
            imagedestroy($img);

            //Movemos el archivo
            if (rename($temp_file, ( getcwd() . DIRECTORY_SEPARATOR . $target_path . $target_file ))) {
                $mensaje = "<a href='${target_path}${target_file}'>${target_file}</a> se subió exitosamente!";
            } else {
                $errorMessage = 'No se pudo subir la imagen.';
            }

            //Borrar temporal
            if (file_exists($temp_file)) {
                unlink($temp_file);
            }
        } else {
            $errorMessage = 'La imagen no se subió. Solo se aceptan imágenes JPEG y PNG';
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
    <h3>Subir archivo de imagen</h3>
    <?php if (isset($mensaje)) :
        echo '<div class="alert alert-info" role="alert">'.$mensaje.'</div>';
    endif;
    if (isset($errorMessage)) :
        echo '<div class="alert alert-danger" role="alert">'.$errorMessage.'</div>';
    endif;
    ?>
    <form method="POST" enctype="multipart/form-data" action="#">
        <div class="custom-file">
            <input type="hidden" name="MAX_FILE_SIZE" value="100000" />
            <input type="file" class="custom-file-input" name="archivo">
            <label class="custom-file-label" for="customFile" data-browse="Buscar">Seleccione el archivo</label>
        </div>
        <br/><br/>
        <input type="hidden" name="csrf_token" value="<?= isset($_SESSION['csrf_token']) ? $_SESSION['csrf_token'] : ""; ?>">
        <input type="submit" value="Aceptar" name="Submit" class="btn btn-primary">
    </form>
    </div>
</body>
</html>
