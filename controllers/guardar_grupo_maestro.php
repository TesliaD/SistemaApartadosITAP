<?php

ob_clean();
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json; charset=utf-8');

if (!isset($_SESSION['id']) || ($_SESSION['rol'] ?? '') !== 'maestro') {
    echo json_encode([
        "error" => "No autorizado"
    ]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {
    echo json_encode([
        "error" => "Datos inválidos"
    ]);
    exit;
}

$idUsuario = $_SESSION['id'];

$IDGrupo   = isset($data['IDGrupo']) ? (int)$data['IDGrupo'] : 0;
$IDCarrera = isset($data['IDCarrera']) ? (int)$data['IDCarrera'] : 0;
$Semestre  = isset($data['Semestre']) ? (int)$data['Semestre'] : 0;
$Periodo   = trim($data['Periodo'] ?? '');
$Anio      = isset($data['Anio']) ? (int)$data['Anio'] : 0;
$Nombre    = trim($data['Nombre'] ?? '');
$tipoGrupo = $data['tipoGrupo'] ?? 'regular';


// ============================================
// VALIDACIONES
// ============================================

if (
    $IDCarrera <= 0 ||
    $Semestre < 1 || $Semestre > 12 ||
    $Anio < 2000 || $Anio > 2100 ||
    $Periodo === '' ||
    $Nombre === ''
) {

    echo json_encode([
        "error" => "Faltan datos obligatorios del grupo"
    ]);

    exit;
}

$periodosValidos = ['Enero - Junio', 'Agosto - Diciembre', 'Verano'];
$tiposValidos = ['regular', 'vespertino', 'sabado'];

if (!in_array($Periodo, $periodosValidos, true) || !in_array($tipoGrupo, $tiposValidos, true)) {
    echo json_encode([
        "error" => "El periodo o tipo de grupo no es válido"
    ]);
    exit;
}


// ============================================
// ACTUALIZAR GRUPO
// ============================================

if ($IDGrupo > 0) {

    // Verificar que el grupo pertenezca al maestro
    $check = $conn->prepare("
        SELECT IDGrupo
        FROM grupos
        WHERE IDGrupo = ?
        AND IDUsuario = ?
    ");

    if (!$check) {
        echo json_encode([
            "error" => "Error al preparar la consulta: " . $conn->error
        ]);
        exit;
    }

    $check->bind_param(
        "ii",
        $IDGrupo,
        $idUsuario
    );

    $check->execute();

    $resultadoCheck = $check->get_result();

    if ($resultadoCheck->num_rows === 0) {

        echo json_encode([
            "error" => "No tienes permiso para editar este grupo"
        ]);

        $check->close();
        exit;
    }

    $check->close();


    // ============================================
    // VERIFICAR DUPLICADOS
    // ============================================

    $dupCheck = $conn->prepare("
        SELECT IDGrupo
        FROM grupos
        WHERE IDCarrera = ?
        AND Semestre = ?
        AND Periodo = ?
        AND Anio = ?
        AND Nombre = ?
        AND IDUsuario = ?
        AND IDGrupo != ?
    ");

    if (!$dupCheck) {
        echo json_encode([
            "error" => "Error al verificar duplicados: " . $conn->error
        ]);
        exit;
    }

    $dupCheck->bind_param(
        "iisisii",
        $IDCarrera,
        $Semestre,
        $Periodo,
        $Anio,
        $Nombre,
        $idUsuario,
        $IDGrupo
    );

    $dupCheck->execute();

    if ($dupCheck->get_result()->num_rows > 0) {

        echo json_encode([
            "error" => "Ya existe otro grupo con ese nombre, carrera, semestre, periodo y año"
        ]);

        $dupCheck->close();
        exit;
    }

    $dupCheck->close();


    // ============================================
    // ACTUALIZAR
    // ============================================

    // IMPORTANTE:
    // Ya NO actualizamos cantidadAlumnos.
    // Ese valor se obtiene contando alumnos reales.

    $sql = "
        UPDATE grupos
        SET
            IDCarrera = ?,
            Semestre = ?,
            Periodo = ?,
            Anio = ?,
            Nombre = ?,
            tipoGrupo = ?
        WHERE IDGrupo = ?
        AND IDUsuario = ?
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            "error" => "Error al preparar actualización: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param(
        "iisissii",
        $IDCarrera,
        $Semestre,
        $Periodo,
        $Anio,
        $Nombre,
        $tipoGrupo,
        $IDGrupo,
        $idUsuario
    );


// ============================================
// INSERTAR GRUPO NUEVO
// ============================================

} else {

    // Verificar duplicados
    $check = $conn->prepare("
        SELECT IDGrupo
        FROM grupos
        WHERE IDCarrera = ?
        AND Semestre = ?
        AND Periodo = ?
        AND Anio = ?
        AND Nombre = ?
        AND IDUsuario = ?
    ");

    if (!$check) {
        echo json_encode([
            "error" => "Error al verificar duplicados: " . $conn->error
        ]);
        exit;
    }

    $check->bind_param(
        "iisisi",
        $IDCarrera,
        $Semestre,
        $Periodo,
        $Anio,
        $Nombre,
        $idUsuario
    );

    $check->execute();

    if ($check->get_result()->num_rows > 0) {

        echo json_encode([
            "error" => "Ya existe un grupo con ese nombre, carrera, semestre, periodo y año"
        ]);

        $check->close();
        exit;
    }

    $check->close();


    // ============================================
    // INSERTAR
    // ============================================

    // cantidadAlumnos ya NO se captura manualmente.
    // Se inicializa en 0.

    $cantidadAlumnos = 0;

    $sql = "
        INSERT INTO grupos
        (
            IDCarrera,
            Semestre,
            Periodo,
            Anio,
            cantidadAlumnos,
            Nombre,
            tipoGrupo,
            IDUsuario
        )
        VALUES (?, ?, ?, ?, ?, ?, ?, ?)
    ";

    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo json_encode([
            "error" => "Error al preparar inserción: " . $conn->error
        ]);
        exit;
    }

    $stmt->bind_param(
        "iisiissi",
        $IDCarrera,
        $Semestre,
        $Periodo,
        $Anio,
        $cantidadAlumnos,
        $Nombre,
        $tipoGrupo,
        $idUsuario
    );
}


// ============================================
// EJECUTAR
// ============================================

if ($stmt->execute()) {

    echo json_encode([
        "mensaje" => $IDGrupo > 0
            ? "Grupo actualizado correctamente"
            : "Grupo creado correctamente"
    ]);

} else {

    echo json_encode([
        "error" => "Error al guardar el grupo: " . $stmt->error
    ]);
}

$stmt->close();
$conn->close();

?>
