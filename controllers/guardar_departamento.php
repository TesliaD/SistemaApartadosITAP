<?php
session_start();
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador') {
    echo json_encode(["status" => "error", "message" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$nombre = trim($data['nombre'] ?? '');

if(empty($nombre)) {
    echo json_encode(["status" => "error", "message" => "El nombre es requerido"]);
    exit;
}

if(strlen($nombre) < 3) {
    echo json_encode(["status" => "error", "message" => "El nombre debe tener mínimo 3 caracteres"]);
    exit;
}

// Verificar si ya existe
$sqlCheck = "SELECT IDDepartamentos FROM departamentos WHERE nombre = ?";
$stmtCheck = $conn->prepare($sqlCheck);
$stmtCheck->bind_param("s", $nombre);
$stmtCheck->execute();
$resultCheck = $stmtCheck->get_result();

if($resultCheck->num_rows > 0) {
    echo json_encode(["status" => "error", "message" => "El departamento '$nombre' ya existe"]);
    exit;
}

$sql = "INSERT INTO departamentos (nombre, activo) VALUES (?, 1)";
$stmt = $conn->prepare($sql);
$stmt->bind_param("s", $nombre);

if($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Departamento registrado correctamente"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>