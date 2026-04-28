<?php
session_start();

include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

header('Content-Type: application/json');

if ($_SERVER["REQUEST_METHOD"] == "POST") {

    if(!isset($_POST['nombre'], $_POST['num_maquinas'], $_POST['descripcion'], $_POST['activo'], $_POST['num_lab'], $_POST['id_departamento'])){
        echo json_encode([
            "status" => "error",
            "msg" => "Datos incompletos"
        ]);
        exit;
    }

    $nombre = $_POST['nombre'];
    $numMaquinas = intval($_POST['num_maquinas']);
    $descripcion = $_POST['descripcion'];
    $activo = intval($_POST['activo']);
    $numLab = intval($_POST['num_lab']);
    $idDepartamento = intval($_POST['id_departamento']); 

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

    $stmt->bind_param("sisiii", 
        $nombre, 
        $numMaquinas,
        $descripcion,  
        $activo,
        $numLab,
        $idDepartamento
    );

    if($stmt->execute()){
        echo json_encode(["status" => "success"]);
    exit;
    } else {
        echo json_encode([
        "status" => "error",
        "msg" => $stmt->error
    ]);
    exit;
}
}