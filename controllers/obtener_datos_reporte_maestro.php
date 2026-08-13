<?php

ob_clean();

session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json; charset=utf-8');


// ============================================================
// VALIDAR SESIÓN
// ============================================================

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'maestro') {

    echo json_encode([
        "error" => "No autorizado"
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// RECIBIR DATOS POR GET
// ============================================================

$grupoId = isset($_GET['grupo'])
    ? (int)$_GET['grupo']
    : 0;

$fecha = $_GET['fecha'] ?? '';

$hora = $_GET['hora'] ?? '';

$idUsuario = (int)$_SESSION['id'];


// ============================================================
// VALIDAR DATOS
// ============================================================

if ($grupoId <= 0 || empty($fecha) || empty($hora)) {

    echo json_encode([
        "error" => "Faltan datos para buscar la reservación."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// NORMALIZAR HORA
// ============================================================

$hora = trim($hora);

if (strlen($hora) === 5) {
    $horaBusqueda = $hora . ":00";
} else {
    $horaBusqueda = $hora;
}


// ============================================================
// BUSCAR RESERVACIÓN
// ============================================================
//
// Buscamos específicamente:
// - Grupo
// - Fecha
// - Hora de inicio
//
// ============================================================

$sqlReserva = "
    SELECT
        r.IDReservacion,
        r.IDGrupo,
        r.fecha,
        r.horaInicio,
        r.horaFin,
        r.Practica,
        r.Software,

        g.Nombre AS nombreGrupo,
        g.Semestre,

        c.Nombre AS carrera,

        l.Nombre AS laboratorio

    FROM reservaciones r

    INNER JOIN grupos g
        ON g.IDGrupo = r.IDGrupo

    LEFT JOIN carreras c
        ON c.IDCarrera = g.IDCarrera

    LEFT JOIN laboratorios l
        ON l.IDLab = r.IDLab

    WHERE r.IDGrupo = ?
      AND r.fecha = ?
      AND TIME(r.horaInicio) = TIME(?)

    ORDER BY r.IDReservacion DESC

    LIMIT 1
";


$stmtReserva = $conn->prepare($sqlReserva);

if (!$stmtReserva) {

    echo json_encode([
        "error" => "Error preparando la consulta de reservación: " . $conn->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$stmtReserva->bind_param(
    "iss",
    $grupoId,
    $fecha,
    $horaBusqueda
);


$stmtReserva->execute();

$resultReserva = $stmtReserva->get_result();

$reserva = $resultReserva->fetch_assoc();


// ============================================================
// SI NO EXISTE RESERVACIÓN
// ============================================================

if (!$reserva) {

    echo json_encode([
        "error" => "No existe una reservación para el grupo seleccionado, fecha y hora indicadas."
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


// ============================================================
// OBTENER ALUMNOS DEL GRUPO
// ============================================================
//
// IMPORTANTE:
//
// NO usamos la cantidad de alumnos guardada en la reservación.
//
// Consultamos directamente:
//
// alumnos.IDGrupo = grupo seleccionado
//
// Así siempre obtenemos los alumnos actuales del grupo.
//
// ============================================================

$sqlAlumnos = "
    SELECT
        IDAlumnos,
        NoControl,
        nombre,
        plan,
        IDGrupo

    FROM alumnos

    WHERE IDGrupo = ?

    ORDER BY nombre ASC
";


$stmtAlumnos = $conn->prepare($sqlAlumnos);

if (!$stmtAlumnos) {

    echo json_encode([
        "error" => "Error preparando consulta de alumnos: " . $conn->error
    ], JSON_UNESCAPED_UNICODE);

    exit;
}


$stmtAlumnos->bind_param(
    "i",
    $grupoId
);


$stmtAlumnos->execute();

$resultAlumnos = $stmtAlumnos->get_result();


$listaAlumnos = [];

$numero = 1;


while ($alumno = $resultAlumnos->fetch_assoc()) {

    $listaAlumnos[] = [

        "numero" => $numero,

        "IDAlumnos" => $alumno['IDAlumnos'],

        "NoControl" => $alumno['NoControl'],

        "nombre" => $alumno['nombre'],

        "plan" => $alumno['plan'],

        "IDGrupo" => $alumno['IDGrupo']

    ];

    $numero++;
}


// ============================================================
// OBTENER MAESTRO
// ============================================================

$sqlMaestro = "
    SELECT
        nombre,
        apellidos

    FROM usuarios

    WHERE IDUsuarios = ?
";


$stmtMaestro = $conn->prepare($sqlMaestro);

$stmtMaestro->bind_param(
    "i",
    $idUsuario
);

$stmtMaestro->execute();

$resultMaestro = $stmtMaestro->get_result();

$maestro = $resultMaestro->fetch_assoc();


$nombreMaestro = '';

if ($maestro) {

    $nombreMaestro = trim(
        ($maestro['nombre'] ?? '') . ' ' .
        ($maestro['apellidos'] ?? '')
    );
}


// ============================================================
// NOMBRE DEL GRUPO
// ============================================================

$nombreGrupo = $reserva['nombreGrupo'] ?? '';

if (empty($nombreGrupo)) {

    $nombreGrupo =
        ($reserva['Semestre'] ?? '') .
        '° Semestre';
}


// ============================================================
// CREAR RESPUESTA
// ============================================================
//
// ESTA ESTRUCTURA ES LA QUE TU JAVASCRIPT ACTUAL ESPERA.
//
// ============================================================

$response = [

    "data" => [

        "IDReservacion" =>
            $reserva['IDReservacion'],

        "IDGrupo" =>
            $reserva['IDGrupo'],

        "fecha" =>
            $reserva['fecha'],

        "horaInicio" =>
            $reserva['horaInicio'],

        "horaFin" =>
            $reserva['horaFin'],

        "Practica" =>
            $reserva['Practica'] ?? '',

        "Software" =>
            $reserva['Software'] ?? '',

        "laboratorio" =>
            $reserva['laboratorio'] ?? '',

        "carrera" =>
            $reserva['carrera'] ?? '',

        "docente" =>
            $nombreMaestro,

        "nombreGrupo" =>
            $nombreGrupo,

        // Cantidad de alumnos
        "Alumnos" =>
            count($listaAlumnos),

        // LISTA REAL DE ALUMNOS
        "listaAlumnos" =>
            $listaAlumnos

    ]

];


// ============================================================
// RESPONDER JSON
// ============================================================

echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE
);

exit;
?>