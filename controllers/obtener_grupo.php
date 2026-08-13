<?php
header('Content-Type: application/json');
require_once '../includes/conexion.php';
require_once '../includes/auth_maestro.php';

$idUsuario = $_SESSION['id'] ?? 0;
$id = $_GET['id'] ?? 0;

if (!$id) {
    echo json_encode(['error' => 'ID no válido']);
    exit;
}

$sql = "SELECT IDCarrera, Semestre, Periodo, Anio, cantidadAlumnos, Nombre, tipoGrupo 
        FROM grupos 
        WHERE IDGrupo = ? AND IDUsuario = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("ii", $id, $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if ($row = $result->fetch_assoc()) {
    echo json_encode($row);
} else {
    echo json_encode(['error' => 'Grupo no encontrado']);
}
?>