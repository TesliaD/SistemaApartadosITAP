<?php
// ============================================================
// REGLAS GENERALES DE RESERVACIÓN (todos los roles)
//
// Se puede reservar desde hoy hasta 2 días después.
// Para el día de hoy solo se permiten horas que todavía
// no han comenzado.
// ============================================================

const RESERVA_DIAS_MAX = 2;

// Inicio del último bloque de horario del día (21:00 - 22:00)
const RESERVA_ULTIMA_HORA = '21:00';

/*
   El administrador puede reservar cualquier día y hora, sin límites de
   anticipación. Manager y maestro siguen las reglas de este archivo.
   (El empalme con otras reservaciones se valida para todos.)
*/
function reservaSinRestricciones()
{
    return ($_SESSION['rol'] ?? '') === 'administrador';
}

// Primer día reservable: hoy, si aún queda algún horario; si no, mañana.
function fechaMinReservacion()
{
    return date('H:i') < RESERVA_ULTIMA_HORA
        ? date('Y-m-d')
        : date('Y-m-d', strtotime('+1 day'));
}

function fechaMaxReservacion()
{
    return date('Y-m-d', strtotime('+' . RESERVA_DIAS_MAX . ' days'));
}

// ¿La hora ("HH:MM" o "HH:MM:SS") ya comenzó en la fecha indicada?
function horaYaPaso($fecha, $hora)
{
    return $fecha === date('Y-m-d') && substr($hora, 0, 5) <= date('H:i');
}

/*
   Devuelve un mensaje de error, o null si es válida.
   $horaInicio es opcional; si se envía y la fecha es hoy,
   se valida que esa hora todavía no haya comenzado.
*/
function validarFechaReservacion($fecha, $horaInicio = null)
{
    if (!is_string($fecha) || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
        return "La fecha no tiene un formato válido";
    }

    // Administrador: cualquier fecha y hora
    if (reservaSinRestricciones()) {
        return null;
    }

    if ($fecha < date('Y-m-d')) {
        return "No puedes reservar en una fecha que ya pasó";
    }

    if ($fecha > fechaMaxReservacion()) {
        return "Solo puedes reservar con máximo " . RESERVA_DIAS_MAX . " días de anticipación";
    }

    if ($horaInicio !== null && horaYaPaso($fecha, $horaInicio)) {
        return "Ese horario ya comenzó. Para hoy solo puedes reservar horas que aún no han iniciado";
    }

    return null;
}

// ============================================================
// HORARIOS Y EMPALMES
// ============================================================

/*
   Convierte las horas seleccionadas en un solo rango.
   Acepta bloques "07:00 - 08:00" o solo el inicio "07:00" (dura 1 hora).
   Los bloques deben ser consecutivos.
   Devuelve ['inicio' => 'HH:MM', 'fin' => 'HH:MM'] o ['error' => '...'].
*/
function rangoDesdeHoras($horas)
{
    if (!is_array($horas) || count($horas) === 0) {
        return ['error' => "Selecciona al menos una hora"];
    }

    $bloques = [];
    foreach ($horas as $h) {
        $partes = array_map('trim', explode('-', (string)$h, 2));
        $inicio = $partes[0];
        $fin = $partes[1] ?? date('H:i', strtotime($inicio) + 3600);

        if (!preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $inicio) ||
            !preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $fin) ||
            $fin <= $inicio) {
            return ['error' => "El horario seleccionado no es válido"];
        }

        $bloques[] = [$inicio, $fin];
    }

    usort($bloques, fn($a, $b) => strcmp($a[0], $b[0]));

    for ($i = 1; $i < count($bloques); $i++) {
        if ($bloques[$i][0] !== $bloques[$i - 1][1]) {
            return ['error' => "Las horas seleccionadas deben ser consecutivas"];
        }
    }

    return ['inicio' => $bloques[0][0], 'fin' => end($bloques)[1]];
}

/*
   Fin real de una reservación en SQL. Las reservaciones antiguas guardadas
   con horaFin igual a horaInicio se consideran de 1 hora.
*/
const SQL_FIN_RESERVACION =
    "CASE WHEN r.horaFin > r.horaInicio THEN r.horaFin ELSE ADDTIME(r.horaInicio, '01:00:00') END";

// Reservaciones que cuentan como ocupadas (todo excepto canceladas)
const SQL_RESERVACION_VIGENTE =
    "LOWER(COALESCE(r.Estado, '')) NOT IN ('cancelada', 'cancelado')";

// ¿Hay otra reservación vigente del laboratorio que se empalme con [inicio, fin)?
function hayEmpalme($conn, $fecha, $idLab, $inicio, $fin)
{
    $sql = "SELECT COUNT(*) AS total
            FROM reservaciones r
            WHERE r.fecha = ?
              AND r.IDLab = ?
              AND " . SQL_RESERVACION_VIGENTE . "
              AND r.horaInicio < ?
              AND " . SQL_FIN_RESERVACION . " > ?";

    $idLab = (int)$idLab;
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("siss", $fecha, $idLab, $fin, $inicio);
    $stmt->execute();
    $total = (int)$stmt->get_result()->fetch_assoc()['total'];
    $stmt->close();

    return $total > 0;
}
?>
