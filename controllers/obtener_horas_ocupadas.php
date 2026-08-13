<?php
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if (!isset($_GET['fecha']) || !isset($_GET['lab'])) {
    echo json_encode([]);
    exit;
}

$fecha = $_GET['fecha'];
$idLab = (int)$_GET['lab'];

// 1. Obtener el día de la semana en español
$dias_semana = ['domingo', 'lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado'];
$timestamp = strtotime($fecha);
$dia_semana = $dias_semana[date('w', $timestamp)];

// 2. Obtener las horas PERMITIDAS por el administrador
$horas_permitidas = [];
$sqlAdmin = "SELECT hora FROM horarios_laboratorio 
             WHERE IDLab = ? AND (dia = ? OR dia = 'todos') AND habilitado = 1";
$stmtAdmin = $conn->prepare($sqlAdmin);
$stmtAdmin->bind_param("is", $idLab, $dia_semana);
$stmtAdmin->execute();
$resultAdmin = $stmtAdmin->get_result();
while ($row = $resultAdmin->fetch_assoc()) {
    $hora_db = $row['hora'];
    
    // CORRECCIÓN: Extraer solo la hora de inicio si el admin guardó el rango completo
    if (strpos($hora_db, ' - ') !== false) {
        $hora_db = explode(' - ', $hora_db)[0];
    }
    
    $horas_permitidas[] = $hora_db;
}

// 3. Obtener las horas ya RESERVADAS
$horas_reservadas = [];
$sqlRes = "SELECT horaInicio FROM reservaciones 
           WHERE fecha = ? AND IDLab = ? AND Estado != 'cancelada'";
$stmtRes = $conn->prepare($sqlRes);
$stmtRes->bind_param("si", $fecha, $idLab);
$stmtRes->execute();
$resultRes = $stmtRes->get_result();
while ($row = $resultRes->fetch_assoc()) {
    $horas_reservadas[] = $row['horaInicio'];
}

$stmtAdmin->close();
$stmtRes->close();
$conn->close();

// 4. DECISIÓN FINAL: Horas que se BLOQUEARÁN en la interfaz
$horas_a_bloquear = [];

$todas_las_horas = [
    "07:00", "08:00", "09:00", "10:00", "11:00", "12:00", 
    "13:00", "14:00", "15:00", "16:00", "17:00", "18:00", 
    "19:00", "20:00", "21:00"
];

// Si el administrador NO ha configurado nada para este día, el array $horas_permitidas estará vacío.
// En ese caso, NO bloqueamos nada por configuración (dejamos todo libre).
if (!empty($horas_permitidas)) {
    foreach ($todas_las_horas as $hora) {
        if (!in_array($hora, $horas_permitidas)) {
            $horas_a_bloquear[] = $hora;
        }
    }
}

// 5. Además, SIEMPRE bloqueamos las horas que ya están reservadas
foreach ($horas_reservadas as $hora) {
    if (!in_array($hora, $horas_a_bloquear)) {
        $horas_a_bloquear[] = $hora;
    }
}

// Devolvemos el array final
echo json_encode(array_values($horas_a_bloquear));
?>