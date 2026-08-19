<?php

ob_clean();
session_start();

require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json; charset=utf-8');


// ============================================================
// SEGURIDAD
// ============================================================

if (!isset($_SESSION['id']) || $_SESSION['rol'] !== 'administrador') {

    echo json_encode([
        "error" => "No autorizado"
    ]);

    exit;
}


// ============================================================
// RECIBIR DATOS
// ============================================================

$data = json_decode(file_get_contents("php://input"), true);

if (!$data) {

    echo json_encode([
        "error" => "Datos inválidos"
    ]);

    exit;
}


$tipo = $data['tipo'] ?? '';
$fechaInicio = $data['fechaInicio'] ?? '';
$fechaFin = $data['fechaFin'] ?? '';
$filtros = $data['filtros'] ?? [];


// ============================================================
// VALIDAR FECHAS
// ============================================================

if (
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaInicio) ||
    !preg_match('/^\d{4}-\d{2}-\d{2}$/', $fechaFin)
) {

    echo json_encode([
        "error" => "Las fechas no tienen un formato válido"
    ]);

    exit;
}


if ($fechaInicio > $fechaFin) {

    echo json_encode([
        "error" => "La fecha de inicio no puede ser mayor a la fecha de fin"
    ]);

    exit;
}


// ============================================================
// RESPUESTA
// ============================================================

$response = [
    "titulo" => "",
    "fechaInicio" => $fechaInicio,
    "fechaFin" => $fechaFin,
    "html" => ""
];


// ============================================================
// SELECCIONAR REPORTE
// ============================================================

switch ($tipo) {

    case 'reporte_departamento':

        $response['titulo'] = 'REPORTE DE RESERVACIONES POR DEPARTAMENTO';

        $response['html'] = generarReporteDepartamento(
            $conn,
            $fechaInicio,
            $fechaFin,
            $filtros
        );

        break;


    case 'apartados_maestro':

        $response['titulo'] = 'REPORTE DE APARTADOS POR MAESTRO';

        $response['html'] = generarReporteApartadosMaestro(
            $conn,
            $fechaInicio,
            $fechaFin,
            $filtros
        );

        break;


    case 'reporte_general':

        $response['titulo'] = 'REPORTE GENERAL DE RESERVACIONES';

        $response['html'] = generarReporteGeneral(
            $conn,
            $fechaInicio,
            $fechaFin,
            $filtros
        );

        break;


    default:

        echo json_encode([
            "error" => "Tipo de reporte no válido"
        ]);

        exit;
}


echo json_encode(
    $response,
    JSON_UNESCAPED_UNICODE
);

exit;


// ============================================================
// FUNCIONES AUXILIARES
// ============================================================

function escapar($texto)
{
    return htmlspecialchars(
        (string)$texto,
        ENT_QUOTES,
        'UTF-8'
    );
}


function nombreEstado($estado)
{
    $estado = strtolower(trim((string)$estado));

    switch ($estado) {

        case 'activa':
            return 'Activa';

        case 'cancelada':
            return 'Cancelada';

        case 'pendiente':
            return 'Pendiente';

        case 'completada':
            return 'Completada';

        default:

            if ($estado === '') {
                return 'Sin estado';
            }

            return ucfirst($estado);
    }
}


function claseEstado($estado)
{
    $estado = strtolower(trim((string)$estado));

    switch ($estado) {

        case 'activa':
            return 'color:green; font-weight:bold;';

        case 'cancelada':
            return 'color:red; font-weight:bold;';

        case 'pendiente':
            return 'color:#b45309; font-weight:bold;';

        case 'completada':
            return 'color:#2563eb; font-weight:bold;';

        default:
            return 'font-weight:bold;';
    }
}


function fechaBonita($fecha)
{
    if (!$fecha) {
        return '';
    }

    $timestamp = strtotime($fecha);

    if (!$timestamp) {
        return $fecha;
    }

    return date('d/m/Y', $timestamp);
}


function horaBonita($hora)
{
    if (!$hora) {
        return '';
    }

    return date('H:i', strtotime($hora));
}


// ============================================================
// REPORTE POR DEPARTAMENTO
// ============================================================

function generarReporteDepartamento($conn, $inicio, $fin, $filtros)
{

    $where = [];

    // --------------------------------------------------------
    // FECHAS
    // --------------------------------------------------------

    $where[] = "
        r.fecha >= '$inicio'
        AND r.fecha < DATE_ADD('$fin', INTERVAL 1 DAY)
    ";


    // --------------------------------------------------------
    // DEPARTAMENTO
    // --------------------------------------------------------

    if (!empty($filtros['departamento'])) {

        $idDepartamento = (int)$filtros['departamento'];

        $where[] = "l.IDDepartamento = $idDepartamento";
    }


    // --------------------------------------------------------
    // LABORATORIO
    // --------------------------------------------------------

    if (!empty($filtros['laboratorio'])) {

        $idLab = (int)$filtros['laboratorio'];

        $where[] = "r.IDLab = $idLab";
    }


    $whereSQL = implode(" AND ", $where);


    // ========================================================
    // CONSULTA
    // ========================================================

    $sql = "
        SELECT

            r.IDReservacion,

            r.fecha,
            r.horaInicio,
            r.horaFin,

            r.Practica,
            r.Software,
            r.Estado,

            d.nombre AS departamento,

            l.Nombre AS laboratorio,
            l.numLab,

            CONCAT(
                COALESCE(u.nombre, ''),
                ' ',
                COALESCE(u.apellidos, '')
            ) AS docente,

            u.num_control,

            g.Nombre AS grupo,
            g.Semestre,

            c.Nombre AS carrera

        FROM reservaciones r

        LEFT JOIN laboratorios l
            ON r.IDLab = l.IDLab

        LEFT JOIN departamentos d
            ON l.IDDepartamento = d.IDDepartamentos

        LEFT JOIN usuarios u
            ON r.IDUsuario = u.IDUsuarios

        LEFT JOIN grupos g
            ON r.IDGrupo = g.IDGrupo

        LEFT JOIN carreras c
            ON g.IDCarrera = c.IDCarrera

        WHERE $whereSQL

        ORDER BY
            d.nombre ASC,
            l.Nombre ASC,
            r.fecha ASC,
            r.horaInicio ASC
    ";


    $result = $conn->query($sql);


    if (!$result) {

        return '
            <div class="alert alert-danger">
                Error al consultar las reservaciones por departamento:
                ' . escapar($conn->error) . '
            </div>
        ';
    }


    // ========================================================
    // ENCABEZADO
    // ========================================================

    $html = '

    <div style="
        margin-bottom:20px;
        padding:12px;
        background:#f8f9fa;
        border:1px solid #ddd;
        border-radius:6px;
    ">

        <strong>Período:</strong>
        ' . escapar(fechaBonita($inicio)) . '
        al
        ' . escapar(fechaBonita($fin)) . '

    </div>

    ';


    if ($result->num_rows === 0) {

        return $html . '

            <div style="
                padding:20px;
                text-align:center;
                border:1px solid #ddd;
            ">

                No existen reservaciones para los filtros seleccionados.

            </div>

        ';
    }


    // ========================================================
    // CONTADORES
    // ========================================================

    $total = 0;
    $activas = 0;
    $canceladas = 0;
    $pendientes = 0;


    // Guardamos las filas para poder generar resumen
    $filas = [];

    while ($row = $result->fetch_assoc()) {

        $filas[] = $row;

        $total++;

        $estado = strtolower(trim($row['Estado'] ?? ''));

        if ($estado === 'activa') {
            $activas++;
        }

        if ($estado === 'cancelada') {
            $canceladas++;
        }

        if ($estado === 'pendiente') {
            $pendientes++;
        }
    }


    // ========================================================
    // RESUMEN
    // ========================================================

    $html .= '

    <div style="
        display:flex;
        gap:12px;
        flex-wrap:wrap;
        margin-bottom:20px;
    ">

        <div style="
            padding:12px 20px;
            border:1px solid #ddd;
            border-radius:6px;
            background:#fff;
        ">
            <strong>Total:</strong><br>
            ' . $total . ' reservaciones
        </div>


        <div style="
            padding:12px 20px;
            border:1px solid #ddd;
            border-radius:6px;
            background:#fff;
        ">
            <strong>Activas:</strong><br>
            ' . $activas . '
        </div>


        <div style="
            padding:12px 20px;
            border:1px solid #ddd;
            border-radius:6px;
            background:#fff;
        ">
            <strong>Canceladas:</strong><br>
            ' . $canceladas . '
        </div>


        <div style="
            padding:12px 20px;
            border:1px solid #ddd;
            border-radius:6px;
            background:#fff;
        ">
            <strong>Pendientes:</strong><br>
            ' . $pendientes . '
        </div>

    </div>


    <table style="
        width:100%;
        border-collapse:collapse;
        font-size:12px;
    ">

        <thead>

            <tr>

                <th>Departamento</th>
                <th>Laboratorio</th>
                <th>Docente</th>
                <th>Grupo</th>
                <th>Carrera</th>
                <th>Fecha</th>
                <th>Horario</th>
                <th>Práctica</th>
                <th>Software</th>
                <th>Estado</th>

            </tr>

        </thead>

        <tbody>
    ';


    // ========================================================
    // DETALLE
    // ========================================================

    foreach ($filas as $row) {

        $estadoTexto = nombreEstado($row['Estado']);
        $estadoCSS = claseEstado($row['Estado']);

        $docente = trim($row['docente']);

        if ($docente === '') {
            $docente = 'Sin docente';
        }

        $grupo = $row['grupo'] ?: 'Sin grupo';
        $carrera = $row['carrera'] ?: 'Sin carrera';

        $laboratorio = $row['laboratorio'] ?: 'Sin laboratorio';

        if (!empty($row['numLab'])) {
            $laboratorio .= ' (' . $row['numLab'] . ')';
        }


        $html .= '

            <tr>

                <td>' .
                    escapar($row['departamento'] ?: 'Sin departamento') .
                '</td>

                <td>' .
                    escapar($laboratorio) .
                '</td>

                <td>' .
                    escapar($docente) .
                '</td>

                <td>' .
                    escapar($grupo) .
                '</td>

                <td>' .
                    escapar($carrera) .
                '</td>

                <td style="text-align:center;">
                    ' .
                    escapar(fechaBonita($row['fecha'])) .
                '
                </td>

                <td style="text-align:center;">
                    ' .
                    escapar(horaBonita($row['horaInicio'])) .
                    ' -
                    ' .
                    escapar(horaBonita($row['horaFin'])) .
                '
                </td>

                <td>' .
                    escapar($row['Practica'] ?: 'No especificada') .
                '</td>

                <td>' .
                    escapar($row['Software'] ?: 'No especificado') .
                '</td>

                <td style="text-align:center; ' .
                    $estadoCSS .
                '">
                    ' .
                    escapar($estadoTexto) .
                '
                </td>

            </tr>

        ';
    }


    $html .= '

        </tbody>

    </table>

    ';


    return $html;
}


// ============================================================
// REPORTE DE APARTADOS POR MAESTRO
// ============================================================

function generarReporteApartadosMaestro($conn, $inicio, $fin, $filtros)
{

    $where = [];

    // --------------------------------------------------------
    // FECHAS
    // --------------------------------------------------------

    $where[] = "
        r.fecha >= '$inicio'
        AND r.fecha < DATE_ADD('$fin', INTERVAL 1 DAY)
    ";


    // --------------------------------------------------------
    // DOCENTE
    // --------------------------------------------------------

    if (!empty($filtros['docente'])) {

        $idDocente = (int)$filtros['docente'];

        $where[] = "r.IDUsuario = $idDocente";
    }


    // --------------------------------------------------------
    // GRUPO
    // --------------------------------------------------------

    if (!empty($filtros['grupo'])) {

        $idGrupo = (int)$filtros['grupo'];

        $where[] = "r.IDGrupo = $idGrupo";
    }


    // --------------------------------------------------------
    // LABORATORIO
    // --------------------------------------------------------

    if (!empty($filtros['laboratorio'])) {

        $idLab = (int)$filtros['laboratorio'];

        $where[] = "r.IDLab = $idLab";
    }


    // --------------------------------------------------------
    // ESTADO
    // --------------------------------------------------------

    if (!empty($filtros['estado'])) {

        $estado = $conn->real_escape_string(
            $filtros['estado']
        );

        $where[] = "r.Estado = '$estado'";
    }


    $whereSQL = implode(" AND ", $where);


    // ========================================================
    // CONSULTA
    // ========================================================

    $sql = "
        SELECT

            r.IDReservacion,

            r.fecha,
            r.horaInicio,
            r.horaFin,

            r.Practica,
            r.Software,
            r.Estado,

            CONCAT(
                COALESCE(u.nombre, ''),
                ' ',
                COALESCE(u.apellidos, '')
            ) AS docente,

            u.num_control,

            l.Nombre AS laboratorio,
            l.numLab,

            d.nombre AS departamento,

            g.Nombre AS grupo,
            g.Semestre,

            c.Nombre AS carrera

        FROM reservaciones r

        INNER JOIN usuarios u
            ON r.IDUsuario = u.IDUsuarios

        LEFT JOIN laboratorios l
            ON r.IDLab = l.IDLab

        LEFT JOIN departamentos d
            ON l.IDDepartamento = d.IDDepartamentos

        LEFT JOIN grupos g
            ON r.IDGrupo = g.IDGrupo

        LEFT JOIN carreras c
            ON g.IDCarrera = c.IDCarrera

        WHERE $whereSQL

        ORDER BY
            u.nombre ASC,
            r.fecha ASC,
            r.horaInicio ASC
    ";


    $result = $conn->query($sql);


    if (!$result) {

        return '
            <div class="alert alert-danger">
                Error al consultar los apartados:
                ' . escapar($conn->error) . '
            </div>
        ';
    }


    $html = '

        <div style="
            margin-bottom:20px;
            padding:12px;
            background:#f8f9fa;
            border:1px solid #ddd;
            border-radius:6px;
        ">

            <strong>Período:</strong>
            ' . escapar(fechaBonita($inicio)) . '
            al
            ' . escapar(fechaBonita($fin)) . '

        </div>

    ';


    if ($result->num_rows === 0) {

        return $html . '

            <div style="
                padding:20px;
                text-align:center;
                border:1px solid #ddd;
            ">

                No existen apartados para el período y filtros seleccionados.

            </div>

        ';
    }


    // ========================================================
    // GUARDAR RESULTADOS
    // ========================================================

    $filas = [];

    $total = 0;
    $activas = 0;
    $canceladas = 0;
    $pendientes = 0;

    $laboratorios = [];
    $grupos = [];


    while ($row = $result->fetch_assoc()) {

        $filas[] = $row;

        $total++;

        $estado = strtolower(trim($row['Estado'] ?? ''));

        if ($estado === 'activa') {
            $activas++;
        }

        if ($estado === 'cancelada') {
            $canceladas++;
        }

        if ($estado === 'pendiente') {
            $pendientes++;
        }


        if (!empty($row['laboratorio'])) {

            $nombreLab = $row['laboratorio'];

            if (!empty($row['numLab'])) {
                $nombreLab .= ' (' . $row['numLab'] . ')';
            }

            $laboratorios[$nombreLab] = true;
        }


        if (!empty($row['grupo'])) {

            $grupos[$row['grupo']] = true;
        }
    }


    // ========================================================
    // DATOS DEL DOCENTE
    // ========================================================

    $primerRegistro = $filas[0];

    $docente = trim($primerRegistro['docente']);

    if ($docente === '') {
        $docente = 'Docente no identificado';
    }

    $numControl = $primerRegistro['num_control'] ?: 'No registrado';


    // ========================================================
    // ENCABEZADO DOCENTE
    // ========================================================

    $html .= '

        <div style="
            padding:15px;
            margin-bottom:20px;
            border:1px solid #d1d5db;
            border-radius:8px;
            background:#f8fafc;
        ">

            <h4 style="margin-bottom:8px;">
                ' . escapar($docente) . '
            </h4>

            <div>
                <strong>No. de control:</strong>
                ' . escapar($numControl) . '
            </div>

        </div>

    ';


    // ========================================================
    // RESUMEN
    // ========================================================

    $html .= '

        <div style="
            display:grid;
            grid-template-columns:repeat(4, 1fr);
            gap:10px;
            margin-bottom:20px;
        ">

            <div style="
                padding:12px;
                border:1px solid #ddd;
                border-radius:6px;
                text-align:center;
            ">
                <strong>Total de apartados</strong>
                <br>
                ' . $total . '
            </div>

            <div style="
                padding:12px;
                border:1px solid #ddd;
                border-radius:6px;
                text-align:center;
            ">
                <strong>Activos</strong>
                <br>
                ' . $activas . '
            </div>

            <div style="
                padding:12px;
                border:1px solid #ddd;
                border-radius:6px;
                text-align:center;
            ">
                <strong>Cancelados</strong>
                <br>
                ' . $canceladas . '
            </div>

            <div style="
                padding:12px;
                border:1px solid #ddd;
                border-radius:6px;
                text-align:center;
            ">
                <strong>Pendientes</strong>
                <br>
                ' . $pendientes . '
            </div>

        </div>

    ';


    // ========================================================
    // LABORATORIOS UTILIZADOS
    // ========================================================

    $html .= '

        <div style="margin-bottom:10px;">

            <strong>Laboratorios utilizados:</strong>

            ' .
            escapar(
                !empty($laboratorios)
                    ? implode(', ', array_keys($laboratorios))
                    : 'Ninguno'
            )
            . '

        </div>


        <div style="margin-bottom:20px;">

            <strong>Grupos atendidos:</strong>

            ' .
            escapar(
                !empty($grupos)
                    ? implode(', ', array_keys($grupos))
                    : 'Ninguno'
            )
            . '

        </div>

    ';


    // ========================================================
    // DETALLE
    // ========================================================

    $html .= '

        <h5 style="margin-bottom:10px;">
            Detalle de apartados
        </h5>

        <table style="
            width:100%;
            border-collapse:collapse;
            font-size:12px;
        ">

            <thead>

                <tr>

                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Departamento</th>
                    <th>Laboratorio</th>
                    <th>Grupo</th>
                    <th>Carrera</th>
                    <th>Práctica</th>
                    <th>Software</th>
                    <th>Estado</th>

                </tr>

            </thead>

            <tbody>

    ';


    foreach ($filas as $row) {

        $estadoTexto = nombreEstado($row['Estado']);
        $estadoCSS = claseEstado($row['Estado']);


        $laboratorio = $row['laboratorio'] ?: 'Sin laboratorio';

        if (!empty($row['numLab'])) {

            $laboratorio .=
                ' (' .
                $row['numLab'] .
                ')';
        }


        $html .= '

            <tr>

                <td style="text-align:center;">
                    ' .
                    escapar(fechaBonita($row['fecha'])) .
                '
                </td>

                <td style="text-align:center;">
                    ' .
                    escapar(horaBonita($row['horaInicio'])) .
                    ' -
                    ' .
                    escapar(horaBonita($row['horaFin'])) .
                '
                </td>

                <td>
                    ' .
                    escapar($row['departamento'] ?: 'Sin departamento') .
                '
                </td>

                <td>
                    ' .
                    escapar($laboratorio) .
                '
                </td>

                <td>
                    ' .
                    escapar($row['grupo'] ?: 'Sin grupo') .
                '
                </td>

                <td>
                    ' .
                    escapar($row['carrera'] ?: 'Sin carrera') .
                '
                </td>

                <td>
                    ' .
                    escapar($row['Practica'] ?: 'No especificada') .
                '
                </td>

                <td>
                    ' .
                    escapar($row['Software'] ?: 'No especificado') .
                '
                </td>

                <td style="
                    text-align:center;
                    ' .
                    $estadoCSS .
                '">

                    ' .
                    escapar($estadoTexto) .
                '

                </td>

            </tr>

        ';
    }


    $html .= '

            </tbody>

        </table>

    ';


    return $html;
}


// ============================================================
// REPORTE GENERAL
// ============================================================

function generarReporteGeneral($conn, $inicio, $fin, $filtros)
{

    $where = [];


    // --------------------------------------------------------
    // FECHAS
    // --------------------------------------------------------

    $where[] = "
        r.fecha >= '$inicio'
        AND r.fecha < DATE_ADD('$fin', INTERVAL 1 DAY)
    ";


    // --------------------------------------------------------
    // DEPARTAMENTO
    // --------------------------------------------------------

    if (!empty($filtros['departamento'])) {

        $idDepartamento = (int)$filtros['departamento'];

        $where[] = "l.IDDepartamento = $idDepartamento";
    }


    // --------------------------------------------------------
    // LABORATORIO
    // --------------------------------------------------------

    if (!empty($filtros['laboratorio'])) {

        $idLab = (int)$filtros['laboratorio'];

        $where[] = "r.IDLab = $idLab";
    }


    // --------------------------------------------------------
    // DOCENTE
    // --------------------------------------------------------

    if (!empty($filtros['docente'])) {

        $idDocente = (int)$filtros['docente'];

        $where[] = "r.IDUsuario = $idDocente";
    }


    // --------------------------------------------------------
    // GRUPO
    // --------------------------------------------------------

    if (!empty($filtros['grupo'])) {

        $idGrupo = (int)$filtros['grupo'];

        $where[] = "r.IDGrupo = $idGrupo";
    }


    // --------------------------------------------------------
    // CARRERA
    // --------------------------------------------------------

    if (!empty($filtros['carrera'])) {

        $idCarrera = (int)$filtros['carrera'];

        $where[] = "g.IDCarrera = $idCarrera";
    }


    // --------------------------------------------------------
    // ESTADO
    // --------------------------------------------------------

    if (!empty($filtros['estado'])) {

        $estado = $conn->real_escape_string(
            $filtros['estado']
        );

        $where[] = "r.Estado = '$estado'";
    }


    $whereSQL = implode(" AND ", $where);


    // ========================================================
    // CONSULTA
    // ========================================================

    $sql = "

        SELECT

            r.IDReservacion,

            r.fecha,
            r.horaInicio,
            r.horaFin,

            r.Practica,
            r.Software,
            r.Estado,

            d.nombre AS departamento,

            l.Nombre AS laboratorio,
            l.numLab,

            CONCAT(
                COALESCE(u.nombre, ''),
                ' ',
                COALESCE(u.apellidos, '')
            ) AS docente,

            u.num_control,

            g.Nombre AS grupo,
            g.Semestre,

            c.Nombre AS carrera

        FROM reservaciones r

        LEFT JOIN laboratorios l
            ON r.IDLab = l.IDLab

        LEFT JOIN departamentos d
            ON l.IDDepartamento = d.IDDepartamentos

        LEFT JOIN usuarios u
            ON r.IDUsuario = u.IDUsuarios

        LEFT JOIN grupos g
            ON r.IDGrupo = g.IDGrupo

        LEFT JOIN carreras c
            ON g.IDCarrera = c.IDCarrera

        WHERE $whereSQL

        ORDER BY
            r.fecha ASC,
            r.horaInicio ASC

    ";


    $result = $conn->query($sql);


    if (!$result) {

        return '

            <div class="alert alert-danger">

                Error al generar el reporte general:

                ' .
                escapar($conn->error) .
                '

            </div>

        ';
    }


    // ========================================================
    // ENCABEZADO
    // ========================================================

    $html = '

        <div style="
            margin-bottom:20px;
            padding:12px;
            background:#f8f9fa;
            border:1px solid #ddd;
            border-radius:6px;
        ">

            <strong>Período:</strong>

            ' .
            escapar(fechaBonita($inicio)) .
            '

            al

            ' .
            escapar(fechaBonita($fin)) .
            '

        </div>

    ';


    if ($result->num_rows === 0) {

        return $html . '

            <div style="
                padding:20px;
                text-align:center;
                border:1px solid #ddd;
            ">

                No existen reservaciones para los filtros seleccionados.

            </div>

        ';
    }


    // ========================================================
    // CONTADOR
    // ========================================================

    $total = $result->num_rows;


    $html .= '

        <div style="
            margin-bottom:15px;
            padding:12px;
            border:1px solid #ddd;
            border-radius:6px;
        ">

            <strong>
                Total de reservaciones encontradas:
            </strong>

            ' .
            $total .
            '

        </div>


        <table style="
            width:100%;
            border-collapse:collapse;
            font-size:11px;
        ">

            <thead>

                <tr>

                    <th>ID</th>
                    <th>Fecha</th>
                    <th>Horario</th>
                    <th>Departamento</th>
                    <th>Laboratorio</th>
                    <th>Docente</th>
                    <th>No. Control</th>
                    <th>Grupo</th>
                    <th>Carrera</th>
                    <th>Práctica</th>
                    <th>Software</th>
                    <th>Estado</th>

                </tr>

            </thead>

            <tbody>

    ';


    // ========================================================
    // DETALLE
    // ========================================================

    while ($row = $result->fetch_assoc()) {

        $estadoTexto = nombreEstado($row['Estado']);
        $estadoCSS = claseEstado($row['Estado']);


        $docente = trim($row['docente']);

        if ($docente === '') {
            $docente = 'Sin docente';
        }


        $laboratorio = $row['laboratorio'] ?: 'Sin laboratorio';

        if (!empty($row['numLab'])) {

            $laboratorio .=
                ' (' .
                $row['numLab'] .
                ')';
        }


        $html .= '

            <tr>

                <td style="text-align:center;">
                    ' .
                    escapar($row['IDReservacion']) .
                '
                </td>

                <td style="text-align:center;">
                    ' .
                    escapar(fechaBonita($row['fecha'])) .
                '
                </td>

                <td style="text-align:center;">
                    ' .
                    escapar(horaBonita($row['horaInicio'])) .
                    ' -
                    ' .
                    escapar(horaBonita($row['horaFin'])) .
                '
                </td>

                <td>
                    ' .
                    escapar($row['departamento'] ?: 'Sin departamento') .
                '
                </td>

                <td>
                    ' .
                    escapar($laboratorio) .
                '
                </td>

                <td>
                    ' .
                    escapar($docente) .
                '
                </td>

                <td>
                    ' .
                    escapar($row['num_control'] ?: 'No registrado') .
                '
                </td>

                <td>
                    ' .
                    escapar($row['grupo'] ?: 'Sin grupo') .
                '
                </td>

                <td>
                    ' .
                    escapar($row['carrera'] ?: 'Sin carrera') .
                '
                </td>

                <td>
                    ' .
                    escapar($row['Practica'] ?: 'No especificada') .
                '
                </td>

                <td>
                    ' .
                    escapar($row['Software'] ?: 'No especificado') .
                '
                </td>

                <td style="
                    text-align:center;
                    ' .
                    $estadoCSS .
                '">

                    ' .
                    escapar($estadoTexto) .
                '

                </td>

            </tr>

        ';
    }


    $html .= '

            </tbody>

        </table>

    ';


    return $html;
}

?>