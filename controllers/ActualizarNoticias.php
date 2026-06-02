<?php

include("../includes/conexion.php");

header('Content-Type: application/json');

if($_SERVER["REQUEST_METHOD"] != "POST"){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Método no permitido"
    ]);

    exit;
}

// ==========================
// DATOS
// ==========================
$id         = $_POST['IdNoticias'] ?? 0;
$titulo     = $_POST['Titulo'] ?? '';
$nombre     = $_POST['Nombre'] ?? '';
$cuerpo     = $_POST['Cuerpo'] ?? '';
$categoria  = $_POST['Categoria'] ?? '';
$activo     = $_POST['Activo'] ?? 1;
$noControl  = $_POST['noControl'] ?? 'admin';

$imagenActual = $_POST['ImagenActual'] ?? '';
$nuevaImagen  = $imagenActual;

// ==========================
// VALIDACIONES
// ==========================
if(
    empty($id) ||
    empty($titulo) ||
    empty($nombre) ||
    empty($cuerpo)
){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Faltan datos obligatorios"
    ]);

    exit;
}

// ==========================
// IMAGEN (SI VIENE NUEVA)
// ==========================
if(isset($_FILES['Imagen']) && $_FILES['Imagen']['error'] == 0){

    $carpeta = "../uploads/noticias/";

    if(!file_exists($carpeta)){
        mkdir($carpeta, 0777, true);
    }

    $extension = pathinfo($_FILES['Imagen']['name'], PATHINFO_EXTENSION);

    $nombreImagen =
        time() . "_" . rand(1000,9999) . "." . $extension;

    $rutaFinal = $carpeta . $nombreImagen;

    move_uploaded_file($_FILES['Imagen']['tmp_name'], $rutaFinal);

    $nuevaImagen = $nombreImagen;
}

// ==========================
// UPDATE
// ==========================
$sql = "
UPDATE noticias
SET
    Titulo = ?,
    Nombre = ?,
    Cuerpo = ?,
    noControl = ?,
    Activo = ?,
    Categoria = ?,
    Imagen = ?
WHERE IDNoticias = ?
";

$stmt = $conn->prepare($sql);

$stmt->bind_param(
    "ssssiisi",
    $titulo,
    $nombre,
    $cuerpo,
    $noControl,
    $activo,
    $categoria,
    $nuevaImagen,
    $id
);

// ==========================
// RESPUESTA
// ==========================
if($stmt->execute()){

    echo json_encode([
        "status" => "success",
        "mensaje" => "Noticia actualizada correctamente"
    ]);

} else {

    echo json_encode([
        "status" => "error",
        "mensaje" => "Error SQL: " . $stmt->error
    ]);

}
?>