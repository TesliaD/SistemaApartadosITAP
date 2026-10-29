<?php
ob_clean();
session_start();

header('Content-Type: application/json');

require_once __DIR__ . "/../includes/permisos.php";
if (!puedeGestionar()){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

require_once __DIR__ . "/../includes/conexion.php";

if(!$conn){
    echo json_encode(["error" => "Error de conexión a BD"]);
    exit;
}

// Manager: solo carreras de su departamento
$sql = "SELECT IDCarrera, Nombre FROM carreras
        WHERE " . filtroDeptoSQL($conn, "IDDepartamento") . "
        ORDER BY Nombre";
$result = $conn->query($sql);

$carreras = [];
while($row = $result->fetch_assoc()){
    $carreras[] = $row;
}

echo json_encode($carreras);
?>