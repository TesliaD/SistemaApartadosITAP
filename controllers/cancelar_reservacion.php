<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

include("../includes/conexion.php");

$data = json_decode(file_get_contents("php://input"), true);
$idReservacion = $data['id'];
$idUsuario = $_SESSION['id'];

// Usar 'cancelada' en minúscula para consistencia
$sql = "UPDATE reservaciones SET Estado = 'cancelada' WHERE IDReservacion = ? AND IDUsuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $idReservacion, $idUsuario);

if($stmt->execute()){
    echo json_encode(["mensaje" => "Reservación cancelada correctamente"]);
} else {
    echo json_encode(["error" => "Error al cancelar: " . $stmt->error]);
}
?>