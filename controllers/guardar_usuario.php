<?php
session_start();
include("../includes/conexion.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $num_control = $_POST['num_control'];
    $nombre = $_POST['nombre'];
    $apellidos = $_POST['apellidos'];
    $area = $_POST['area'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $rol = isset($_POST['rol']) ? $_POST['rol'] : 'usuario';
    $activo = $_POST['activo'];

    $sql = "INSERT INTO usuarios 
    (num_control, nombre, apellidos, area, email, password, rol, activo) 
    VALUES (?, ?, ?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if(!$stmt){
        echo json_encode(["status" => "error"]);
        exit;
    }

    $stmt->bind_param("sssssssi", 
        $num_control,
        $nombre, 
        $apellidos,
        $area,  
        $email,  
        $password, 
        $rol, 
        $activo
    );

    if($stmt->execute()){
        echo json_encode(["status" => "success"]);
        exit; 
    }

    echo json_encode(["status" => "error"]);
    exit;
}