<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

$fecha = $_GET['fecha'] ?? date('Y-m-d');

// Obtener todos los laboratorios
$sqlLabs = "SELECT IDLab, Nombre, numLab FROM laboratorios WHERE activo = 1 ORDER BY Nombre";
$resultLabs = $conn->query($sqlLabs);

$laboratorios = [];

while($lab = $resultLabs->fetch_assoc()) {
    // Obtener reservaciones para este laboratorio en la fecha indicada
    $sqlRes = "SELECT 
                    r.IDReservacion,
                    r.horaInicio,
                    r.horaFin,
                    r.Practica,
                    r.Estado,
                    CONCAT(u.nombre, ' ', u.apellidos) AS docente,
                    g.Nombre AS grupo
                FROM reservaciones r
                LEFT JOIN usuarios u ON r.IDUsuario = u.IDUsuarios
                LEFT JOIN grupos g ON r.IDGrupo = g.IDGrupo
                WHERE r.IDLab = ? AND r.fecha = ? AND r.Estado != 'cancelada'
                ORDER BY r.horaInicio";

    $stmtRes = $conn->prepare($sqlRes);
    $stmtRes->bind_param("is", $lab['IDLab'], $fecha);
    $stmtRes->execute();
    $resultRes = $stmtRes->get_result();

    $reservaciones = [];
    while($res = $resultRes->fetch_assoc()) {
        $reservaciones[] = $res;
    }

    $laboratorios[] = [
        'IDLab' => $lab['IDLab'],
        'Nombre' => $lab['Nombre'],
        'numLab' => $lab['numLab'],
        'ocupado' => count($reservaciones) > 0,
        'reservaciones' => $reservaciones
    ];
}

echo json_encode($laboratorios);
?>