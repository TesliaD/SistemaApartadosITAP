<?php

header('Content-Type: application/json; charset=utf-8');

require_once '../includes/conexion.php';
require_once '../includes/auth_maestro.php';

$idUsuario = $_SESSION['id'] ?? 0;

if (!$idUsuario) {
    echo json_encode([
        'error' => 'Sesión no válida'
    ]);
    exit;
}

$sql = "
    SELECT 
        g.IDGrupo,
        g.IDCarrera,
        g.Semestre,
        g.Periodo,
        g.Anio,

        COUNT(a.NoControl) AS cantidadAlumnos,

        g.Nombre,
        g.tipoGrupo,
        c.Nombre AS Carrera

    FROM grupos g

    LEFT JOIN carreras c 
        ON g.IDCarrera = c.IDCarrera

    LEFT JOIN alumnos a 
        ON a.IDGrupo = g.IDGrupo

    WHERE g.IDUsuario = ?

    GROUP BY 
        g.IDGrupo,
        g.IDCarrera,
        g.Semestre,
        g.Periodo,
        g.Anio,
        g.Nombre,
        g.tipoGrupo,
        c.Nombre

    ORDER BY 
        g.Anio DESC,
        g.Periodo DESC,
        g.Semestre DESC
";

$stmt = $conn->prepare($sql);

if (!$stmt) {
    echo json_encode([
        'error' => 'Error al preparar consulta: ' . $conn->error
    ]);
    exit;
}

$stmt->bind_param("i", $idUsuario);

if (!$stmt->execute()) {
    echo json_encode([
        'error' => 'Error al ejecutar consulta: ' . $stmt->error
    ]);
    exit;
}

$result = $stmt->get_result();

$grupos = [];

while ($row = $result->fetch_assoc()) {
    $grupos[] = $row;
}

echo json_encode($grupos, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();

?>