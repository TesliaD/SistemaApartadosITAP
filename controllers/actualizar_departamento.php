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
$nombre = trim($data['nombre'] ?? '');
$activo = (int)$data['activo'];

if(empty($id) || empty($nombre)) {
    echo json_encode(["status" => "error", "message" => "Datos incompletos"]);
    exit;
}

if(strlen($nombre) < 3) {
    echo json_encode(["status" => "error", "message" => "El nombre debe tener mínimo 3 caracteres"]);
    exit;
}

$sql = "UPDATE departamentos SET nombre = ?, activo = ? WHERE IDDepartamentos = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("sii", $nombre, $activo, $id);

if($stmt->execute()) {
    echo json_encode(["status" => "success", "message" => "Departamento actualizado correctamente"]);
} else {
    echo json_encode(["status" => "error", "message" => $stmt->error]);
}
?>