<?php
    if(file_exists("data.txt")){
        $jsonClientes = file_get_contents("data.txt");
        $aClientes = json_decode($jsonClientes, true);
    }
    else{
        $aClientes = [];
    }

    $id = isset($_GET["id"]) ? $_GET["id"] : "";
    $aMsg = array("mensaje" => "", "codigo" => "");

    if(isset($_GET["do"]) && $_GET["do"] == "eliminar"){
        if($aClientes[$id]["imagen"] != ""){
            unlink("files/" . $aClientes[$id]["imagen"]);
        }
        unset($aClientes[$id]);
        $jsonClientes = json_encode($aClientes);
        file_put_contents("data.txt", $jsonClientes);
        $aMsg = array("mensaje" => "Eliminado correctamente", "codigo" => "danger");

        $id="";
    }

    if($_POST){
        $dni = trim($_POST["txtDni"]);
        $nombre = trim($_POST["txtNombre"]);
        $telefono = trim($_POST["txtTelefono"]);
        $correo = trim($_POST["txtCorreo"]);
        $nombreImagen = "";

        if($_FILES["archivo"]["error"] === UPLOAD_ERR_OK){
            $nombreAleatorio = date("Ymdhmsi");
            $archivoTmp = $_FILES["archivo"]["tmp_name"];
            $nombreArchivo = $_FILES["archivo"]["name"];
            $extension = pathinfo($nombreArchivo, PATHINFO_EXTENSION);
            $nombreImagen = "$nombreAleatorio.$extension";
            move_uploaded_file($archivoTmp, "files/$nombreImagen");
        }

        if(isset($_GET["id"])){
            // Si hay una imagen anterior eliminarla, siempre y cuando se suba una nueva imagen
            $imagenAnterior = $aClientes[$id]["imagen"];
            if($_FILES["archivo"]["error"] === UPLOAD_ERR_OK){
                if($imagenAnterior != ""){
                    unlink("files/$imagenAnterior");
                }
            }
            if($_FILES["archivo"]["error"] !== UPLOAD_ERR_OK) {
                $nombreImagen = $imagenAnterior; // Conservar la imagen que tenia previamente
            }
            // Actualizacion
            $aClientes[$id] = array(
                "dni" => $dni,
                "nombre" => $nombre,
                "telefono" => $telefono,
                "correo" => $correo,
                "imagen" => $nombreImagen
            );
            $aMsg = array("mensaje" => "Actualizado correctamente", "codigo" => "success");
        }
        else{
            // Insertar
            $aClientes[] = array(
                "dni" => $dni,
                "nombre" => $nombre,
                "telefono" => $telefono,
                "correo" => $correo,
                "imagen" => $nombreImagen
            );
            $aMsg = array("mensaje" => "Insertado correctamente", "codigo" => "success");
        }

        $jsonClientes = json_encode($aClientes);
        file_put_contents("data.txt", $jsonClientes);
    }
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, shrink-to-fit=no">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/css/bootstrap.min.css" integrity="sha384-9aIt2nRpC12Uk9gS9baDl411NQApFmC26EwAOH8WgZl5MYYxFfc+NcPb1dKGj7Sk" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.13.1/css/all.min.css">
    <link rel="stylesheet" href="css/style.css">

    <title>ABM Clientes</title>
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <div class="col-12 py-3 text-center">
                <h1>Registro de Clientes</h1>
            </div>
        </div>
        <div class="row">
            <div class="col-sm-6 col-12">
                <form action="" method="POST" enctype="multipart/form-data">
                    <?php if($aMsg["mensaje"] != ""): ?>
                        <div class="row">
                            <div class="col-12">
                                <div class="alert alert-<?php echo $aMsg["codigo"]; ?>" role="alert" >
                                    <?php echo $aMsg["mensaje"]; ?>
                                </div>
                            </div>
                        </div>
                    <?php endif; ?>
                    <div class="form-group">
                        <label for="txtDni">DNI:</label>
                        <input type="text" class="form-control" id="txtDni" name="txtDni" required value="<?php echo isset($aClientes[$id])? $aClientes[$id]["dni"] : ""; ?>">
                    </div>
                    <div class="form-group">
                        <label for="txtNombre">Nombre:</label>
                        <input type="text" class="form-control" id="txtNombre" name="txtNombre" required value="<?php echo isset($aClientes[$id])? $aClientes[$id]["nombre"] : ""; ?>">
                    </div>
                    <div class="form-group">
                        <label for="txtTelefono">Teléfono:</label>
                        <input type="text" class="form-control" id="txtTelefono" name="txtTelefono" required value="<?php echo isset($aClientes[$id])? $aClientes[$id]["telefono"] : ""; ?>">
                    </div>
                    <div class="form-group">
                        <label for="txtCorreo">Correo:</label>
                        <input type="text" class="form-control" id="txtCorreo" name="txtCorreo" required value="<?php echo isset($aClientes[$id])? $aClientes[$id]["correo"] : ""; ?>">
                    </div>
                    <div class="form-group">
                        <label for="archivo">Archivo Adjunto:</label>
                        <input type="file" class="form-control-file" id="archivo" name="archivo" class="form-control">
                    </div>
                    <button type="submit" id="btnGuardar" name="btnGuardar" class="btn btn-primary">Guardar</button>
                </form>
            </div>
            <div class="col-sm-6 col-12">
                <table class="table table-hover border">
                    <tr>
                        <th>Imagen</th>
                        <th>DNI</th>
                        <th>Nombre</th>
                        <th>Correo</th>
                        <th>Acciones</th>
                    </tr>
                    <?php foreach($aClientes as $key => $cliente): ?>
                        <tr>
                            <td><img src="files/<?php echo $cliente["imagen"]; ?>" alt="" class="img-thumbnail"></td>
                            <td><?php echo $cliente["dni"]; ?></td>
                            <td><?php echo $cliente["nombre"]; ?></td>
                            <td><?php echo $cliente["correo"]; ?></td>
                            <td style="width: 110px;">
                                <a href="index.php?id=<?php echo $key; ?>"><i class="fas fa-edit editar"></i></a>
                                <a href="index.php?id=<?php echo $key; ?>&do=eliminar"><i class="fas fa-trash-alt eliminar"></i></a>
                            </td>
                        </tr>
                    <?php endforeach;?>
                </table>
                <a href="index.php"><i class="fas fa-plus inicio"></i></a>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js" integrity="sha384-DfXdz2htPH0lsSSs5nCTpuj/zy4C+OGpamoFVy38MVBnE+IbbVYUew+OrCXaRkfj" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@1.16.0/dist/umd/popper.min.js" integrity="sha384-Q6E9RHvbIyZFJoft+2mJbHaEWldlvI9IOYy5n3zV9zzTtmI3UksdQRVvoxMfooAo" crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.0/js/bootstrap.min.js" integrity="sha384-OgVRvuATP1z7JjHLkuOU7Xw704+h835Lr+6QL9UvYjZE3Ipu6Tp75j7Bh/kR0JKI" crossorigin="anonymous"></script>
</body>
</html>