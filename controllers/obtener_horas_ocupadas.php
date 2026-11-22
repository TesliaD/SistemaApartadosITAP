<?php
session_start();
require_once __DIR__ . "/../includes/conexion.php";

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
//    Se bloquea cada hora que cubre la reservación (desde el inicio hasta antes del fin),
//    en formato "HH:MM" igual que los botones de la interfaz.
require_once __DIR__ . "/../includes/reglas_reservacion.php";
$horas_reservadas = [];
$sqlRes = "SELECT r.horaInicio, " . SQL_FIN_RESERVACION . " AS horaFin
           FROM reservaciones r
           WHERE r.fecha = ? AND r.IDLab = ? AND " . SQL_RESERVACION_VIGENTE;
$stmtRes = $conn->prepare($sqlRes);
$stmtRes->bind_param("si", $fecha, $idLab);
$stmtRes->execute();
$resultRes = $stmtRes->get_result();
while ($row = $resultRes->fetch_assoc()) {
    $inicio = strtotime($row['horaInicio']);
    $fin = strtotime($row['horaFin']);
    for ($t = $inicio; $t < $fin; $t += 3600) {
        $horas_reservadas[] = date('H:i', $t);
    }
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

// 6. Si la fecha es hoy, bloqueamos también las horas que ya comenzaron
require_once __DIR__ . "/../includes/reglas_reservacion.php";
foreach ($todas_las_horas as $hora) {
    // (el administrador puede reservar horas que ya pasaron)
    if (!reservaSinRestricciones() && horaYaPaso($fecha, $hora) && !in_array($hora, $horas_a_bloquear)) {
        $horas_a_bloquear[] = $hora;
    }
}

// Devolvemos el array final
echo json_encode(array_values($horas_a_bloquear));
?>