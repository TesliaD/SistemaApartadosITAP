<?php

ob_clean();
session_start();

require_once __DIR__ . "/../includes/conexion.php";

header('Content-Type: application/json; charset=utf-8');


// ============================================================
// SEGURIDAD
// ============================================================

require_once __DIR__ . "/../includes/permisos.php";
if (!puedeGestionar()) {

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

// Manager: todos sus reportes quedan limitados a su departamento
// (se ignora cualquier otro departamento que venga en la petición).
$deptoManager = deptoRestringido($conn);
if ($deptoManager !== null) {
    $filtros['departamento'] = $deptoManager > 0 ? $deptoManager : -1;
}


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
    "html" => "",
    // Datos para el encabezado del documento
    "generadoPor" => $_SESSION['usuario'] ?? '',
    "generadoEl" => date('d/m/Y H:i'),
    "departamento" => nombreDeptoSesion($conn) // solo para manager; null para admin
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


// ------------------------------------------------------------
// ESTADOS
// En la BD hay variantes ("activa", "Activo", "Cancelado"...):
// se normalizan para contarlas y mostrarlas igual.
// ------------------------------------------------------------

function normalizarEstado($estado)
{
    $e = strtolower(trim((string)$estado));

    if ($e === '') return 'sin-estado';
    if (strpos($e, 'cancel') === 0) return 'cancelada';
    if (strpos($e, 'activ') === 0) return 'activa';
    if (strpos($e, 'finaliz') === 0 || strpos($e, 'complet') === 0) return 'finalizada';
    if (strpos($e, 'pend') === 0) return 'pendiente';

    return 'otro';
}


function nombreEstado($estado)
{
    $nombres = [
        'activa'     => 'Activa',
        'cancelada'  => 'Cancelada',
        'finalizada' => 'Finalizada',
        'pendiente'  => 'Pendiente',
        'sin-estado' => 'Sin estado',
    ];

    $clave = normalizarEstado($estado);

    return $nombres[$clave] ?? ucfirst(trim((string)$estado));
}


function badgeEstado($estado)
{
    return '<b class="rpt-estado rpt-estado-' . normalizarEstado($estado) . '">'
        . escapar(nombreEstado($estado))
        . '</b>';
}


// Condición SQL del filtro de estado (acepta las variantes de la BD)
function sqlFiltroEstado($conn, $estado)
{
    $estado = strtolower(trim((string)$estado));

    if ($estado === 'activa') {
        return "LOWER(r.Estado) IN ('activa', 'activo')";
    }

    if ($estado === 'cancelada') {
        return "LOWER(r.Estado) IN ('cancelada', 'cancelado')";
    }

    return "LOWER(r.Estado) = '" . $conn->real_escape_string($estado) . "'";
}


// ------------------------------------------------------------
// HORAS
// ------------------------------------------------------------

// Duración en horas (registros antiguos con fin = inicio cuentan como 1 h)
function horasReservacion($row)
{
    $inicio = strtotime($row['horaInicio'] ?? '');
    $fin = strtotime($row['horaFin'] ?? '');

    if (!$inicio) return 0;
    if (!$fin || $fin <= $inicio) $fin = $inicio + 3600;

    return ($fin - $inicio) / 3600;
}


function formatoHoras($horas)
{
    $texto = rtrim(rtrim(number_format($horas, 1, '.', ''), '0'), '.');
    return $texto . ' h';
}


function horarioTexto($row)
{
    return horaBonita($row['horaInicio']) . ' – ' . horaBonita($row['horaFin']);
}


// Totales por estado y horas de uso (las canceladas no suman horas)
function contarReservaciones($filas)
{
    $c = ['total' => 0, 'activa' => 0, 'cancelada' => 0, 'finalizada' => 0, 'pendiente' => 0, 'horas' => 0];

    foreach ($filas as $row) {
        $c['total']++;
        $estado = normalizarEstado($row['Estado'] ?? '');

        if (isset($c[$estado])) {
            $c[$estado]++;
        }

        if ($estado !== 'cancelada') {
            $c['horas'] += horasReservacion($row);
        }
    }

    return $c;
}


// ------------------------------------------------------------
// PIEZAS DEL DOCUMENTO
// Se usan tablas y clases "rpt-*" (ver css/reporte_documento.css)
// para que se vea igual en pantalla, impreso y exportado a Excel.
// ------------------------------------------------------------

function textoODefecto($valor, $defecto)
{
    $valor = trim((string)$valor);

    return $valor !== ''
        ? escapar($valor)
        : '<em class="rpt-vacio">' . escapar($defecto) . '</em>';
}


function etiquetaLab($row)
{
    $nombre = trim((string)($row['laboratorio'] ?? ''));

    if ($nombre === '') return 'Sin laboratorio';

    return !empty($row['numLab']) ? $nombre . ' (Lab ' . $row['numLab'] . ')' : $nombre;
}


// $items: [[valor, etiqueta, variante], ...]  variante: total|ok|cancel|info|neutral
function rptResumen($items)
{
    $html = '<table class="rpt-resumen"><tr>';

    foreach ($items as [$valor, $etiqueta, $variante]) {
        $html .= '<td class="rpt-kpi rpt-kpi-' . $variante . '">'
            . '<div class="rpt-kpi-valor">' . escapar($valor) . '</div>'
            . '<div class="rpt-kpi-etiqueta">' . escapar($etiqueta) . '</div>'
            . '</td>';
    }

    return $html . '</tr></table>';
}


function rptSeccion($titulo, $detalle = '')
{
    return '<div class="rpt-seccion">'
        . '<div class="rpt-seccion-titulo">' . escapar($titulo) . '</div>'
        . ($detalle !== '' ? '<div class="rpt-seccion-detalle">' . $detalle . '</div>' : '')
        . '</div>';
}


// $columnas: [[titulo, clase], ...]   $filas: [[celdaHtml, ...], ...] (ya escapadas)
function rptTabla($columnas, $filas)
{
    $html = '<table class="rpt-tabla"><thead><tr>';

    foreach ($columnas as [$titulo, $clase]) {
        $html .= '<th class="' . $clase . '">' . escapar($titulo) . '</th>';
    }

    $html .= '</tr></thead><tbody>';

    foreach ($filas as $i => $celdas) {
        $html .= '<tr class="' . ($i % 2 ? 'rpt-par' : '') . '">';

        foreach ($celdas as $j => $celda) {
            $html .= '<td class="' . ($columnas[$j][1] ?? '') . '">' . $celda . '</td>';
        }

        $html .= '</tr>';
    }

    return $html . '</tbody></table>';
}


function rptVacio($mensaje)
{
    return '<div class="rpt-sin-datos">' . escapar($mensaje) . '</div>';
}


function rptFilas($result)
{
    $filas = [];

    while ($row = $result->fetch_assoc()) {
        $filas[] = $row;
    }

    return $filas;
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


    $filas = rptFilas($result);

    if (count($filas) === 0) {
        return rptVacio('No existen reservaciones para los filtros seleccionados.');
    }

    // ========================================================
    // RESUMEN GENERAL
    // ========================================================

    $c = contarReservaciones($filas);

    // Uso por laboratorio (agrupado por departamento)
    $usoLabs = [];
    $porDepto = [];

    foreach ($filas as $row) {
        $depto = $row['departamento'] ?: 'Sin departamento';
        $lab = etiquetaLab($row);
        $clave = $depto . '|' . $lab;

        if (!isset($usoLabs[$clave])) {
            $usoLabs[$clave] = ['depto' => $depto, 'lab' => $lab, 'total' => 0, 'canceladas' => 0, 'horas' => 0];
        }

        $usoLabs[$clave]['total']++;

        if (normalizarEstado($row['Estado']) === 'cancelada') {
            $usoLabs[$clave]['canceladas']++;
        } else {
            $usoLabs[$clave]['horas'] += horasReservacion($row);
        }

        $porDepto[$depto][] = $row;
    }

    ksort($usoLabs);
    ksort($porDepto);

    $html = rptResumen([
        [$c['total'], 'Reservaciones', 'total'],
        [$c['activa'], 'Activas', 'ok'],
        [$c['cancelada'], 'Canceladas', 'cancel'],
        [formatoHoras($c['horas']), 'Horas de uso', 'info'],
        [count($usoLabs), 'Laboratorios utilizados', 'neutral'],
    ]);

    // ========================================================
    // USO POR LABORATORIO
    // ========================================================

    $html .= rptSeccion('Uso por laboratorio');

    $filasUso = [];
    foreach ($usoLabs as $uso) {
        $filasUso[] = [
            escapar($uso['depto']),
            escapar($uso['lab']),
            $uso['total'],
            $uso['canceladas'],
            formatoHoras($uso['horas']),
        ];
    }

    $html .= rptTabla([
        ['Departamento', ''],
        ['Laboratorio', ''],
        ['Reservaciones', 'rpt-num'],
        ['Canceladas', 'rpt-num'],
        ['Horas de uso', 'rpt-num'],
    ], $filasUso);

    // ========================================================
    // DETALLE POR DEPARTAMENTO
    // ========================================================

    foreach ($porDepto as $depto => $reservas) {
        $cd = contarReservaciones($reservas);

        $html .= rptSeccion(
            $depto,
            $cd['total'] . ' reservaciones · ' . formatoHoras($cd['horas']) . ' de uso'
        );

        $detalle = [];
        foreach ($reservas as $row) {
            $detalle[] = [
                escapar(fechaBonita($row['fecha'])),
                escapar(horarioTexto($row)),
                escapar(etiquetaLab($row)),
                textoODefecto(trim($row['docente']), 'Sin docente'),
                textoODefecto($row['grupo'], 'Sin grupo'),
                textoODefecto($row['carrera'], 'Sin carrera'),
                textoODefecto($row['Practica'], 'No especificada'),
                textoODefecto($row['Software'], 'No especificado'),
                badgeEstado($row['Estado']),
            ];
        }

        $html .= rptTabla([
            ['Fecha', 'rpt-centro rpt-nowrap'],
            ['Horario', 'rpt-centro rpt-nowrap'],
            ['Laboratorio', ''],
            ['Docente', ''],
            ['Grupo', ''],
            ['Carrera', ''],
            ['Práctica', ''],
            ['Software', ''],
            ['Estado', 'rpt-centro'],
        ], $detalle);
    }

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
    // DEPARTAMENTO (se fija automáticamente para el manager)
    // --------------------------------------------------------

    if (!empty($filtros['departamento'])) {

        $idDepartamento = (int)$filtros['departamento'];

        $where[] = "l.IDDepartamento = $idDepartamento";
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

        $where[] = sqlFiltroEstado($conn, $filtros['estado']);
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


    $filas = rptFilas($result);

    if (count($filas) === 0) {
        return rptVacio('No existen apartados para el período y filtros seleccionados.');
    }

    // ========================================================
    // AGRUPAR POR DOCENTE
    // (antes todo se mostraba bajo el nombre del primer docente)
    // ========================================================

    $porDocente = [];

    foreach ($filas as $row) {
        $nombre = trim($row['docente']) ?: 'Docente no identificado';
        $clave = $nombre . '|' . ($row['num_control'] ?? '');

        if (!isset($porDocente[$clave])) {
            $porDocente[$clave] = [
                'nombre' => $nombre,
                'control' => $row['num_control'] ?: 'No registrado',
                'filas' => [],
            ];
        }

        $porDocente[$clave]['filas'][] = $row;
    }

    // ========================================================
    // RESUMEN GENERAL
    // ========================================================

    $c = contarReservaciones($filas);

    $html = rptResumen([
        [count($porDocente), count($porDocente) === 1 ? 'Docente' : 'Docentes', 'neutral'],
        [$c['total'], 'Apartados', 'total'],
        [$c['activa'], 'Activos', 'ok'],
        [$c['cancelada'], 'Cancelados', 'cancel'],
        [formatoHoras($c['horas']), 'Horas de uso', 'info'],
    ]);

    // ========================================================
    // UNA SECCIÓN POR DOCENTE
    // ========================================================

    foreach ($porDocente as $docente) {
        $cd = contarReservaciones($docente['filas']);

        $labs = [];
        $grupos = [];
        foreach ($docente['filas'] as $row) {
            $labs[etiquetaLab($row)] = true;
            if (!empty($row['grupo'])) {
                $grupos[$row['grupo']] = true;
            }
        }

        $html .= rptSeccion(
            $docente['nombre'],
            'No. de control: <b>' . escapar($docente['control']) . '</b>'
            . ' · ' . $cd['total'] . ' apartados'
            . ' · ' . $cd['activa'] . ' activos'
            . ' · ' . $cd['cancelada'] . ' cancelados'
            . ' · ' . formatoHoras($cd['horas']) . ' de uso'
        );

        $html .= '<table class="rpt-datos"><tr>'
            . '<td><b>Laboratorios utilizados:</b> ' . escapar(implode(', ', array_keys($labs)) ?: 'Ninguno') . '</td>'
            . '<td><b>Grupos atendidos:</b> ' . escapar(implode(', ', array_keys($grupos)) ?: 'Ninguno') . '</td>'
            . '</tr></table>';

        $detalle = [];
        foreach ($docente['filas'] as $row) {
            $detalle[] = [
                escapar(fechaBonita($row['fecha'])),
                escapar(horarioTexto($row)),
                escapar(etiquetaLab($row)),
                textoODefecto($row['departamento'], 'Sin departamento'),
                textoODefecto($row['grupo'], 'Sin grupo'),
                textoODefecto($row['carrera'], 'Sin carrera'),
                textoODefecto($row['Practica'], 'No especificada'),
                textoODefecto($row['Software'], 'No especificado'),
                badgeEstado($row['Estado']),
            ];
        }

        $html .= rptTabla([
            ['Fecha', 'rpt-centro rpt-nowrap'],
            ['Horario', 'rpt-centro rpt-nowrap'],
            ['Laboratorio', ''],
            ['Departamento', ''],
            ['Grupo', ''],
            ['Carrera', ''],
            ['Práctica', ''],
            ['Software', ''],
            ['Estado', 'rpt-centro'],
        ], $detalle);
    }

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

        $where[] = sqlFiltroEstado($conn, $filtros['estado']);
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


    $filas = rptFilas($result);

    if (count($filas) === 0) {
        return rptVacio('No existen reservaciones para los filtros seleccionados.');
    }

    // ========================================================
    // RESUMEN
    // ========================================================

    $c = contarReservaciones($filas);

    $docentes = [];
    $labs = [];
    foreach ($filas as $row) {
        if (trim($row['docente']) !== '') $docentes[trim($row['docente'])] = true;
        $labs[etiquetaLab($row)] = true;
    }

    $html = rptResumen([
        [$c['total'], 'Reservaciones', 'total'],
        [$c['activa'], 'Activas', 'ok'],
        [$c['cancelada'], 'Canceladas', 'cancel'],
        [formatoHoras($c['horas']), 'Horas de uso', 'info'],
        [count($labs), 'Laboratorios', 'neutral'],
        [count($docentes), 'Docentes', 'neutral'],
    ]);

    // ========================================================
    // DETALLE
    // ========================================================

    $html .= rptSeccion('Detalle de reservaciones', $c['total'] . ' registros ordenados por fecha y hora');

    $detalle = [];
    foreach ($filas as $i => $row) {
        $docente = textoODefecto(trim($row['docente']), 'Sin docente');

        if (!empty($row['num_control'])) {
            $docente .= '<div class="rpt-sub">No. control ' . escapar($row['num_control']) . '</div>';
        }

        $detalle[] = [
            $i + 1,
            escapar(fechaBonita($row['fecha'])),
            escapar(horarioTexto($row)),
            textoODefecto($row['departamento'], 'Sin departamento'),
            escapar(etiquetaLab($row)),
            $docente,
            textoODefecto($row['grupo'], 'Sin grupo'),
            textoODefecto($row['carrera'], 'Sin carrera'),
            textoODefecto($row['Practica'], 'No especificada'),
            textoODefecto($row['Software'], 'No especificado'),
            badgeEstado($row['Estado']),
        ];
    }

    $html .= rptTabla([
        ['#', 'rpt-centro'],
        ['Fecha', 'rpt-centro rpt-nowrap'],
        ['Horario', 'rpt-centro rpt-nowrap'],
        ['Departamento', ''],
        ['Laboratorio', ''],
        ['Docente', ''],
        ['Grupo', ''],
        ['Carrera', ''],
        ['Práctica', ''],
        ['Software', ''],
        ['Estado', 'rpt-centro'],
    ], $detalle);

    return $html;
}

?>
