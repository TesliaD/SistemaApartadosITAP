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
?>
