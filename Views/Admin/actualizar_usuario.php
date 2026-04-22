<?php
include("../../includes/conexion.php");

header('Content-Type: application/json');

$id = $_POST['id']; 
$nombre = $_POST['nombre'];
$email = $_POST['email'];
$rol = $_POST['rol'];

$stmt = $conn->prepare("UPDATE usuarios SET nombre=?, email=?, rol=? WHERE IDUsuarios=?");
$stmt->bind_param("sssi", $nombre, $email, $rol, $id);

if($stmt->execute()){
    echo json_encode(["status"=>"success"]);
}else{
    echo json_encode(["status"=>"error"]);
}
?>