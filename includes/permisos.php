<?php
// ============================================================
// PERMISOS POR ROL
//
// administrador -> gestiona todo el sistema.
// manager       -> gestiona laboratorios, horarios, reservaciones
//                  y reportes SOLO de su departamento
//                  (usuarios.IDDepartamento).
// ============================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

function rolActual()
{
    return $_SESSION['rol'] ?? '';
}

function esAdmin()
{
    return rolActual() === 'administrador';
}

function esManager()
{
    return rolActual() === 'manager';
}

// Administrador o manager: pueden entrar al panel de gestión.
function puedeGestionar()
{
    return isset($_SESSION['id']) && (esAdmin() || esManager());
}

/*
   Departamento al que está limitado el usuario.
   - null : sin límite (administrador).
   - int  : ID del departamento del manager (0 si no tiene uno,
            con lo que no puede gestionar nada).

   Se consulta en la BD para que un cambio de departamento
   aplique sin tener que volver a iniciar sesión.
*/
function deptoRestringido($conn)
{
    static $depto = false;

    if (!esManager()) {
        return null;
    }

    if ($depto === false) {
        $id = (int)($_SESSION['id'] ?? 0);
        $stmt = $conn->prepare("SELECT IDDepartamento FROM usuarios WHERE IDUsuarios = ? AND rol = 'manager'");
        $stmt->bind_param("i", $id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();
        $stmt->close();

        $depto = (int)($row['IDDepartamento'] ?? 0);
    }

    return $depto;
}

function puedeGestionarDepto($conn, $idDepartamento)
{
    $depto = deptoRestringido($conn);
    return $depto === null || ($depto > 0 && (int)$idDepartamento === $depto);
}

function puedeGestionarLab($conn, $idLab)
{
    if (deptoRestringido($conn) === null) {
        return true;
    }

    $idLab = (int)$idLab;
    $stmt = $conn->prepare("SELECT IDDepartamento FROM laboratorios WHERE IDLab = ?");
    $stmt->bind_param("i", $idLab);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row !== null && puedeGestionarDepto($conn, $row['IDDepartamento']);
}

function puedeGestionarReservacion($conn, $idReservacion)
{
    if (deptoRestringido($conn) === null) {
        return true;
    }

    $idReservacion = (int)$idReservacion;
    $stmt = $conn->prepare("SELECT IDLab FROM reservaciones WHERE IDReservacion = ?");
    $stmt->bind_param("i", $idReservacion);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row !== null && puedeGestionarLab($conn, $row['IDLab']);
}

/*
   Condición SQL para limitar consultas al departamento del manager.
   $columna es la columna con el ID de departamento (p. ej. "l.IDDepartamento").
   Devuelve "1=1" para el administrador. El valor es un entero, no requiere escape.
*/
function filtroDeptoSQL($conn, $columna)
{
    $depto = deptoRestringido($conn);
    return $depto === null ? "1=1" : "$columna = " . (int)$depto;
}

function nombreDeptoSesion($conn)
{
    $depto = deptoRestringido($conn);
    if (!$depto) {
        return null;
    }

    $stmt = $conn->prepare("SELECT nombre FROM departamentos WHERE IDDepartamentos = ?");
    $stmt->bind_param("i", $depto);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $stmt->close();

    return $row['nombre'] ?? null;
}
?>
