<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador') {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)$data['id'];

if(empty($id)) {
    echo json_encode(["status" => "error", "message" => "ID requerido"]);
    exit;
}

// Verificar si tiene laboratorios asociados
$sqlCheck = "SELECT COUNT(*) as total FROM laboratorios WHERE IDDepartamento = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("i", $id);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();
$row = $resultCheck->fetch_assoc();

if($row['total'] > 0) {
    echo json_encode([
        "status" => "error", 
        "message" => "No se puede eliminar, tiene $row[total] laboratorios asociados. Desactívalo en su lugar."
    ]);
    exit;
}

// Verificar si tiene carreras asociadas
$sqlCheck2 = "SELECT COUNT(*) as total FROM carreras WHERE IDDepartamento = ?";
$stmtCheck2 = $conn->prepare($sqlCheck2);
$stmtCheck2->bind_param("i", $id);
$stmtCheck2->execute();
$resultCheck2 = $stmtCheck2->get_result();
$row2 = $resultCheck2->fetch_assoc();

if($row2['total'] > 0) {
    echo json_encode([
        "status" => "error", 
        "message" => "No se puede eliminar, tiene $row2[total] carreras asociadas. Desactívalo en su lugar."
    ]);
    exit;
}

// Verificar si tiene usuarios asociados
$sqlCheck3 = "SELECT COUNT(*) as total FROM usuarios WHERE IDDepartamento = ?";
$stmtCheck3 = $conn->prepare($sqlCheck3);
$stmtCheck3->bind_param("i", $id);
$stmtCheck3->execute();
$resultCheck3 = $stmtCheck3->get_result();
$row3 = $resultCheck3->fetch_assoc();

if($row3['total'] > 0) {
    echo json_encode([
        "status" => "error", 
        "message" => "No se puede eliminar, tiene $row3[total] usuarios asociados. Desactívalo en su lugar."
    ]);
    exit;
}

$sql = "DELETE FROM departamentos WHERE IDDepartamentos = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Departamento eliminado correctamente"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>