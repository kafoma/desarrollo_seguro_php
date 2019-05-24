<?php
//para deshabilitar la proteccion del navegador
header('X-XSS-Protection:0');

if (isset($_POST['Submit'])) {

    $dir_subida = './archivos/';
    $archivoSubido = $dir_subida . basename($_FILES['archivo']['name']);

    if (move_uploaded_file($_FILES['archivo']['tmp_name'], $archivoSubido)) {
        $mensaje =  "El archivo es válido y se subió con éxito.";
    } else {
        $mensaje =  "No se pudo subir el archivo.";
    }
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
    <h3>Subir archivo</h3>
    <?php if (isset($mensaje)) :
        echo '<div class="alert alert-info" role="alert">'.$mensaje.'</div>';
    endif; ?>
    <form method="POST" enctype="multipart/form-data">
        <div class="custom-file">
            <input type="file" class="custom-file-input" name="archivo">
            <label class="custom-file-label" for="customFile" data-browse="Buscar">Seleccione el archivo</label>
        </div>
        <br/><br/>
        <input type="submit" value="Aceptar" name="Submit" class="btn btn-primary">
    </form>
    </div>
</body>
</html>
