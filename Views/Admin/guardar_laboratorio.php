<?php
session_start();
include("../../includes/conexion.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    $nombre = $_POST['nombre'];
    $numMaquinas = $_POST['num_maquinas'];
    $descripcion = $_POST['descripcion'];
    $activo = $_POST['activo'];
    $numLab = $_POST['num_lab'];
    $idDepartamento = $_POST['id_departamento']; 

    $sql = "INSERT INTO laboratorios 
    (Nombre, numMaquinas, Descripcion, activo, numLab, IDDepartamento) 
    VALUES (?, ?, ?, ?, ?, ?)";

    $stmt = $conn->prepare($sql);

    if(!$stmt){
        echo json_encode([
            "status" => "error",
            "msg" => $conn->error
        ]);
        exit;
    }

    $stmt->bind_param("sssssi", 
        $nombre, 
        $numMaquinas,
        $descripcion,  
        $activo,
        $numLab,
        $idDepartamento
    );

    if($stmt->execute()){
        echo json_encode(["status" => "success"]);
    } else {
        echo json_encode([
            "status" => "error",
            "msg" => $stmt->error
        ]);
    }
}