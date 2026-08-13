<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador'){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)$data['id'];

if(empty($id)){
    echo json_encode(["error" => "ID requerido"]);
    exit;
}

$sql = "DELETE FROM horarios_laboratorio WHERE IDHorario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);

if($stmt->execute()) {
    echo json_encode(["mensaje" => "Horario eliminado correctamente"]);
} else {
    echo json_encode(["error" => "Error al eliminar: " . $stmt->error]);
}

$stmt->close();
$conn->close();
?>