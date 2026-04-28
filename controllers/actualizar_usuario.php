<?php
include("../includes/conexion.php");

header('Content-Type: application/json');

if(!isset($_POST['id'], $_POST['nombre'], $_POST['email'], $_POST['rol'])){
    echo json_encode([
        "status" => "error",
        "error" => "Datos incompletos",
        "post" => $_POST
    ]);
    exit;
}

$id = $_POST['id']; 
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$rol = $_POST['rol'];

$stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE IDUsuarios=?");

if(!$stmt){
    echo json_encode([
        "status"=>"error",
        "error"=>$conn->error
    ]);
    exit;
}

$stmt->bind_param("sssi", $nombre, $email, $rol, $id);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
} else {
    echo json_encode([
        "status"=>"error",
        "error"=>$stmt->error
    ]);
}