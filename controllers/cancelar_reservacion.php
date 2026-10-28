<?php
session_start();
header('Content-Type: application/json');
error_reporting(0);

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

include("../includes/conexion.php");
require_once __DIR__ . "/../includes/permisos.php";

$data = json_decode(file_get_contents("php://input"), true);
$idReservacion = (int)($data['id'] ?? 0);
$idUsuario = (int)$_SESSION['id'];

if(puedeGestionar()){
    // Administrador: cualquier reservación. Manager: solo las de su departamento.
    if(!puedeGestionarReservacion($conn, $idReservacion)){
        echo json_encode(["error" => "Solo puedes cancelar reservaciones de tu departamento"]);
        exit;
    }

    $sql = "UPDATE reservaciones SET Estado = 'cancelada' WHERE IDReservacion = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $idReservacion);
} else {
    // Maestro: solo sus propias reservaciones
    // Usar 'cancelada' en minúscula para consistencia
    $sql = "UPDATE reservaciones SET Estado = 'cancelada' WHERE IDReservacion = ? AND IDUsuario = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ii", $idReservacion, $idUsuario);
}

if($stmt->execute() && $stmt->affected_rows > 0){
    echo json_encode(["mensaje" => "Reservación cancelada correctamente"]);
} else {
    echo json_encode(["error" => "No se pudo cancelar la reservación"]);
}
?>
