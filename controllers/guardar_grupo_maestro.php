<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

$idUsuario = $_SESSION['id'];

// Verificar si es actualización o nuevo
if(isset($data['IDGrupo']) && $data['IDGrupo'] != "" && $data['IDGrupo'] != 0 && $data['IDGrupo'] != "null"){
    
    // ACTUALIZAR - Verificar que el grupo existe y pertenece al maestro
    $check = $conn->prepare("SELECT IDGrupo FROM grupos WHERE IDGrupo = ? AND IDUsuario = ?");
    $check->bind_param("ii", $data['IDGrupo'], $idUsuario);
    $check->execute();
    if($check->get_result()->num_rows == 0){
        echo json_encode(["error" => "No tienes permiso para editar este grupo"]);
        exit;
    }
    $check->close();
    
    // Verificar duplicidad (mismo nombre, carrera, semestre pero diferente ID)
    $dupCheck = $conn->prepare("SELECT IDGrupo FROM grupos WHERE IDCarrera = ? AND Semestre = ? AND Nombre = ? AND IDUsuario = ? AND IDGrupo != ?");
    $dupCheck->bind_param("iissi", $data['IDCarrera'], $data['Semestre'], $data['Nombre'], $idUsuario, $data['IDGrupo']);
    $dupCheck->execute();
    if($dupCheck->get_result()->num_rows > 0){
        echo json_encode(["error" => "Ya existe un grupo con ese nombre, carrera y semestre"]);
        exit;
    }
    $dupCheck->close();
    
    $sql = "UPDATE grupos SET 
                IDCarrera = ?, 
                Semestre = ?, 
                cantidadAlumnos = ?, 
                Nombre = ?, 
                tipoGrupo = ?
            WHERE IDGrupo = ? AND IDUsuario = ?";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissii", 
        $data['IDCarrera'],
        $data['Semestre'],
        $data['cantidadAlumnos'],
        $data['Nombre'],
        $data['tipoGrupo'],
        $data['IDGrupo'],
        $idUsuario
    );
    
} else {
    // INSERTAR NUEVO - Verificar duplicidad
    $check = $conn->prepare("SELECT IDGrupo FROM grupos WHERE IDCarrera = ? AND Semestre = ? AND Nombre = ? AND IDUsuario = ?");
    $check->bind_param("iisi", $data['IDCarrera'], $data['Semestre'], $data['Nombre'], $idUsuario);
    $check->execute();
    if($check->get_result()->num_rows > 0){
        echo json_encode(["error" => "Ya existe un grupo con ese nombre, carrera y semestre"]);
        exit;
    }
    $check->close();
    
    $sql = "INSERT INTO grupos (IDCarrera, Semestre, cantidadAlumnos, Nombre, tipoGrupo, IDUsuario) 
            VALUES (?, ?, ?, ?, ?, ?)";
            
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("iiissi", 
        $data['IDCarrera'],
        $data['Semestre'],
        $data['cantidadAlumnos'],
        $data['Nombre'],
        $data['tipoGrupo'],
        $idUsuario
    );
}

if($stmt->execute()){
    echo json_encode(["mensaje" => "Grupo guardado correctamente"]);
} else {
    echo json_encode(["error" => "Error: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>