<?php
header('Content-Type: application/json');
require_once '../includes/conexion.php';
require_once '../includes/auth_maestro.php';

$idUsuario = $_SESSION['id'] ?? 0;

$sql = "SELECT g.IDGrupo, g.IDCarrera, g.Semestre, g.Periodo, g.Anio, g.cantidadAlumnos, g.Nombre, g.tipoGrupo, c.Nombre AS Carrera 
        FROM grupos g
        LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
        WHERE g.IDUsuario = ?
        ORDER BY g.Anio DESC, g.Periodo DESC, g.Semestre DESC";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

$grupos = [];
while ($row = $result->fetch_assoc()) {
    $grupos[] = $row;
}

echo json_encode($grupos);
?>