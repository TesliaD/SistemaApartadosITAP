<?php
include("../includes/conexion.php");

header('Content-Type: application/json');

$id = $_GET['id'] ?? 0;

$sql = "
SELECT 
    g.IDGrupo,
    g.Semestre,
    g.cantidadAlumnos,
    c.Nombre AS carrera
FROM grupos g
LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
WHERE g.IDDocente = ?
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();

$res = $stmt->get_result();

$data = [];

while($row = $res->fetch_assoc()){
    $data[] = $row;
}

echo json_encode($data);