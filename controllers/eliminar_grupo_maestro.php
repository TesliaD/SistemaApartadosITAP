<?php

ob_start();

session_start();

header('Content-Type: application/json; charset=utf-8');

require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";


// ======================================================
// VERIFICAR SESIÓN
// ======================================================

if (!isset($_SESSION['id'])) {

    ob_clean();

    echo json_encode([
        "error" => "No autorizado"
    ]);

    exit;
}


// ======================================================
// LEER DATOS
// ======================================================

$input = file_get_contents("php://input");

$data = json_decode($input, true);

if (!is_array($data)) {

    ob_clean();

    echo json_encode([
        "error" => "Datos inválidos"
    ]);

    exit;
}


$idGrupo = isset($data['id'])
    ? intval($data['id'])
    : 0;

$idUsuario = intval($_SESSION['id']);


if ($idGrupo <= 0) {

    ob_clean();

    echo json_encode([
        "error" => "ID de grupo inválido"
    ]);

    exit;
}


// ======================================================
// VERIFICAR QUE EL GRUPO PERTENEZCA AL MAESTRO
// ======================================================

$check = $conn->prepare("
    SELECT IDGrupo
    FROM grupos
    WHERE IDGrupo = ?
    AND IDUsuario = ?
");

if (!$check) {

    ob_clean();

    echo json_encode([
        "error" => "Error preparando consulta: " . $conn->error
    ]);

    exit;
}

$check->bind_param(
    "ii",
    $idGrupo,
    $idUsuario
);

$check->execute();

$resultado = $check->get_result();


if ($resultado->num_rows === 0) {

    $check->close();

    ob_clean();

    echo json_encode([
        "error" => "No tienes permiso para eliminar este grupo"
    ]);

    exit;
}

$check->close();


// ======================================================
// INICIAR TRANSACCIÓN
// ======================================================

$conn->begin_transaction();


try {

    // ==================================================
    // 1. ELIMINAR RESERVACIONES DEL GRUPO
    // ==================================================

    $stmtReservaciones = $conn->prepare("
        DELETE FROM reservaciones
        WHERE IDGrupo = ?
    ");

    if (!$stmtReservaciones) {
        throw new Exception(
            "Error preparando eliminación de reservaciones: "
            . $conn->error
        );
    }

    $stmtReservaciones->bind_param(
        "i",
        $idGrupo
    );

    if (!$stmtReservaciones->execute()) {

        throw new Exception(
            "Error eliminando reservaciones: "
            . $stmtReservaciones->error
        );
    }

    $reservacionesEliminadas =
        $stmtReservaciones->affected_rows;

    $stmtReservaciones->close();


    // ==================================================
    // 2. ELIMINAR GRUPO
    // ==================================================

    $stmtGrupo = $conn->prepare("
        DELETE FROM grupos
        WHERE IDGrupo = ?
        AND IDUsuario = ?
    ");

    if (!$stmtGrupo) {

        throw new Exception(
            "Error preparando eliminación del grupo: "
            . $conn->error
        );
    }

    $stmtGrupo->bind_param(
        "ii",
        $idGrupo,
        $idUsuario
    );

    if (!$stmtGrupo->execute()) {

        throw new Exception(
            "Error eliminando grupo: "
            . $stmtGrupo->error
        );
    }

    if ($stmtGrupo->affected_rows === 0) {

        throw new Exception(
            "No se pudo eliminar el grupo."
        );
    }

    $stmtGrupo->close();


    // ==================================================
    // CONFIRMAR TODO
    // ==================================================

    $conn->commit();


    ob_clean();

    echo json_encode([
        "mensaje" => "Grupo eliminado correctamente",
        "reservacionesEliminadas" => $reservacionesEliminadas
    ]);

} catch (Exception $e) {

    // ==================================================
    // DESHACER TODO SI ALGO FALLA
    // ==================================================

    $conn->rollback();

    ob_clean();

    echo json_encode([
        "error" => $e->getMessage()
    ]);
}


$conn->close();

exit;
?>