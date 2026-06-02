<?php 
include("../includes/conexion.php");

header('Content-Type: application/json');

$data = json_decode(file_get_contents("php://input"), true);

// ==========================
// DATOS
// ==========================
$fecha = $data['fecha'] ?? null;

$horas = $data['horas'] ?? [];

$lab = $data['IDLab'] ?? null;

$practica = $data['Practica'] ?? '';


$software = $data['software'] ?? '';

$alumnos = $data['Alumnos'] ?? 0;

$docente = $data['IDDocentes'] ?? null;

$grupo = $data['IDGrupo'] ?? null;


// ==========================
// VALIDACIONES
// ==========================
if(!$fecha){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Fecha requerida"
    ]);

    exit;

}

if(empty($horas)){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Selecciona al menos una hora"
    ]);

    exit;

}

if(!$lab){

    echo json_encode([
        "status" => "error",
        "mensaje" => "Selecciona laboratorio"
    ]);

    exit;

}


// ==========================
// RECORRER HORAS
// ==========================
foreach($horas as $hora){

    $inicio = $hora . ":00";

    $fin = date(
        "H:i:s",
        strtotime($inicio . " +1 hour")
    );

    // ==========================
    // VALIDAR CRUCE HORARIOS
    // ==========================
    $check = $conn->prepare("
        SELECT 1 
        FROM reservaciones 
        WHERE IDLab = ?
        AND fecha = ?
        AND (
            horaInicio < ?
            AND horaFin > ?
        )
        AND Estado = 'Activo'
    ");

    $check->bind_param(
        "isss",
        $lab,
        $fecha,
        $fin,
        $inicio
    );

    $check->execute();

    // ==========================
    // SI YA EXISTE
    // ==========================
    if($check->get_result()->num_rows > 0){

        continue;

    }

    // ==========================
    // INSERTAR RESERVACION
    // ==========================
    $stmt = $conn->prepare("
        INSERT INTO reservaciones
        (
            fecha,
            horaInicio,
            horaFin,
            IDLab,
            IDDocentes,
            IDGrupo,
            Practica,
            Software,
            Estado
        )
        VALUES
        (
            ?, ?, ?, ?, ?, ?, ?, ?, 'Activo'
        )
    ");

    $stmt->bind_param(
        "sssiiiss",

        $fecha,
        $inicio,
        $fin,
        $lab,
        $docente,
        $grupo,
        $practica,
        $software
    );

    $stmt->execute();

}


// ==========================
// RESPUESTA
// ==========================
echo json_encode([
    "status" => "success",
    "mensaje" => "Reservación guardada correctamente"
]);
?>