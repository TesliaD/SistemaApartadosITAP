<?php
ob_clean();
session_start();
require_once __DIR__ . "/../includes/conexion.php";

header('Content-Type: application/json');

require_once __DIR__ . "/../includes/permisos.php";
if (!puedeGestionar()){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);
$id = (int)$data['id'];

if(empty($id)){
    echo json_encode(["error" => "ID requerido"]);
    exit;
}

// Manager: solo horarios de laboratorios de su departamento
$stmtLab = $conn->prepare("SELECT IDLab FROM horarios_laboratorio WHERE IDHorario = ?");
$stmtLab->bind_param("i", $id);
$stmtLab->execute();
$horario = $stmtLab->get_result()->fetch_assoc();
$stmtLab->close();

if(!$horario || !puedeGestionarLab($conn, $horario['IDLab'])){
    echo json_encode(["error" => "Solo puedes modificar horarios de laboratorios de tu departamento"]);
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