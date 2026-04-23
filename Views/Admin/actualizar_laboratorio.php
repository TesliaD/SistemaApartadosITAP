<?php
include("../../includes/conexion.php");

header('Content-Type: application/json');

if(!isset(
    $_POST['id'],
    $_POST['nombre'],
    $_POST['num_maquinas'],
    $_POST['descripcion'],
    $_POST['activo'],
    $_POST['num_lab'],
    $_POST['id_departamento']
)){
    echo json_encode([
        "status" => "error",
        "error" => "Datos incompletos",
        "post" => $_POST
    ]);
    exit;
}

$id = $_POST['id']; 
$nombre = $_POST['nombre'];
$numMaquinas = $_POST['num_maquinas'];
$descripcion = $_POST['descripcion'];
$activo = $_POST['activo'];
$numLab = $_POST['num_lab'];
$idDepartamento = $_POST['id_departamento'];

$stmt = $conn->prepare("UPDATE laboratorios 
SET Nombre=?, numMaquinas=?, Descripcion=?, activo=?, numLab=?, IDDepartamento=? 
WHERE IDLab=?");

if(!$stmt){
    echo json_encode([
        "status"=>"error",
        "error"=>$conn->error
    ]);
    exit;
}

$stmt->bind_param("sisssii", 
    $nombre,
    $numMaquinas,
    $descripcion,
    $activo,
    $numLab,
    $idDepartamento,
    $id
);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode([
        "status"=>"error",
        "error"=>$stmt->error
    ]);
}