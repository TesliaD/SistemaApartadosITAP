<?php
// ============================================================
// Si el administrador desactiva a un usuario mientras tiene la
// sesión abierta, en su siguiente página se cierra la sesión.
// Se carga desde los archivos auth_*.php.
// ============================================================

require_once __DIR__ . "/conexion.php";
require_once __DIR__ . "/config.php";

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (isset($_SESSION['id'])) {
    $idSesion = (int)$_SESSION['id'];
    $stmtActivo = $conn->prepare("SELECT activo FROM usuarios WHERE IDUsuarios = ?");
    $stmtActivo->bind_param("i", $idSesion);
    $stmtActivo->execute();
    $filaActivo = $stmtActivo->get_result()->fetch_assoc();
    $stmtActivo->close();

    if (!$filaActivo || (int)$filaActivo['activo'] !== 1) {
        session_unset();
        session_destroy();
        header("Location: " . BASE_URL . "/index.html");
        exit();
    }
}
