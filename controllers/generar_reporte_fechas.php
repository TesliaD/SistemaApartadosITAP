<?php
ob_clean();
session_start();
require_once $_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php";

header('Content-Type: application/json');

if(!isset($_SESSION['id']) || $_SESSION['rol'] != 'administrador'){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if(!$data){
    echo json_encode(["error" => "Datos inválidos"]);
    exit;
}

$tipo = $data['tipo'];
$fechaInicio = $data['fechaInicio'];
$fechaFin = $data['fechaFin'];
$filtros = $data['filtros'] ?? [];

$response = [
    "titulo" => "",
    "fechaInicio" => $fechaInicio,
    "fechaFin" => $fechaFin,
    "html" => ""
];

switch($tipo) {
    case 'reporte_departamento':
        $response['titulo'] = 'REPORTE DE LABORATORIOS POR DEPARTAMENTO';
        $response['html'] = generarReporteDepartamento($conn, $fechaInicio, $fechaFin, $filtros);
        break;
        
    case 'apartados_maestro':
        $response['titulo'] = 'REPORTE DE APARTADOS POR MAESTRO';
        $response['html'] = generarReporteApartadosMaestro($conn, $fechaInicio, $fechaFin, $filtros);
        break;
        
    case 'reporte_general':
        $response['titulo'] = 'REPORTE GENERAL DE RESERVACIONES';
        $response['html'] = generarReporteGeneral($conn, $fechaInicio, $fechaFin, $filtros);
        break;
        
    default:
        echo json_encode(["error" => "Tipo de reporte no válido"]);
        exit;
}

echo json_encode($response);


// =============================================
// FUNCIONES GENERADORAS DE REPORTES
// =============================================

function generarReporteDepartamento($conn, $inicio, $fin, $filtros) {
    $where = "l.activo = 1";
    
    if(!empty($filtros['departamento'])) {
        $where .= " AND l.IDDepartamento = " . (int)$filtros['departamento'];
    }
    if(!empty($filtros['laboratorio'])) {
        $where .= " AND l.IDLab = " . (int)$filtros['laboratorio'];
    }
    
    $sql = "SELECT 
                d.nombre AS departamento,
                l.IDLab,
                l.Nombre AS laboratorio,
                l.numLab,
                COUNT(r.IDReservacion) AS total_reservaciones,
                SUM(CASE WHEN r.Estado = 'activa' THEN 1 ELSE 0 END) AS reservaciones_activas,
                SUM(CASE WHEN r.Estado = 'cancelada' THEN 1 ELSE 0 END) AS reservaciones_canceladas,
                COUNT(DISTINCT r.IDUsuario) AS docentes_distintos
            FROM laboratorios l
            LEFT JOIN departamentos d ON l.IDDepartamento = d.IDDepartamentos
            LEFT JOIN reservaciones r ON l.IDLab = r.IDLab 
                AND r.fecha BETWEEN '$inicio' AND '$fin'
                AND r.Estado != 'cancelada'
            WHERE $where
            GROUP BY l.IDLab
            ORDER BY d.nombre, l.Nombre";
    
    $result = $conn->query($sql);
    
    $html = '
    <p><strong>Período:</strong> ' . $inicio . ' al ' . $fin . '</p>
    <table>
        <thead>
            <tr>
                <th>Departamento</th>
                <th>Laboratorio</th>
                <th>No. Lab</th>
                <th>Total Reservaciones</th>
                <th>Activas</th>
                <th>Canceladas</th>
                <th>Docentes Distintos</th>
            </tr>
        </thead>
        <tbody>';
    
    if($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $html .= "
                <tr>
                    <td>{$row['departamento']}</td>
                    <td>{$row['laboratorio']}</td>
                    <td style='text-align:center;'>{$row['numLab']}</td>
                    <td style='text-align:center;'>{$row['total_reservaciones']}</td>
                    <td style='text-align:center; color:green;'>{$row['reservaciones_activas']}</td>
                    <td style='text-align:center; color:red;'>{$row['reservaciones_canceladas']}</td>
                    <td style='text-align:center;'>{$row['docentes_distintos']}</td>
                </tr>
            ";
        }
    } else {
        $html .= '<tr><td colspan="7" style="text-align:center;">No hay datos para mostrar</td></tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}

function generarReporteApartadosMaestro($conn, $inicio, $fin, $filtros) {
    $where = "r.fecha BETWEEN '$inicio' AND '$fin'";
    
    if(!empty($filtros['docente'])) {
        $where .= " AND r.IDUsuario = " . (int)$filtros['docente'];
    }
    if(!empty($filtros['grupo'])) {
        $where .= " AND r.IDGrupo = " . (int)$filtros['grupo'];
    }
    if(!empty($filtros['laboratorio'])) {
        $where .= " AND r.IDLab = " . (int)$filtros['laboratorio'];
    }
    if(!empty($filtros['estado'])) {
        $where .= " AND r.Estado = '" . $filtros['estado'] . "'";
    }
    
    $sql = "SELECT 
                CONCAT(u.nombre, ' ', u.apellidos) AS docente,
                u.num_control,
                COUNT(r.IDReservacion) AS total_apartados,
                SUM(CASE WHEN r.Estado = 'activa' THEN 1 ELSE 0 END) AS activas,
                SUM(CASE WHEN r.Estado = 'cancelada' THEN 1 ELSE 0 END) AS canceladas,
                COUNT(DISTINCT r.IDLab) AS laboratorios_utilizados,
                COUNT(DISTINCT r.IDGrupo) AS grupos_atendidos
            FROM usuarios u
            LEFT JOIN reservaciones r ON u.IDUsuarios = r.IDUsuario 
                AND $where
            WHERE u.rol = 'maestro' AND u.activo = 1
            GROUP BY u.IDUsuarios
            HAVING total_apartados > 0 OR activas > 0
            ORDER BY total_apartados DESC";
    
    $result = $conn->query($sql);
    
    $html = '
    <p><strong>Período:</strong> ' . $inicio . ' al ' . $fin . '</p>
    <table>
        <thead>
            <tr>
                <th>Docente</th>
                <th>No. Control</th>
                <th>Total Apartados</th>
                <th>Activas</th>
                <th>Canceladas</th>
                <th>Laboratorios Utilizados</th>
                <th>Grupos Atendidos</th>
            </tr>
        </thead>
        <tbody>';
    
    if($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $html .= "
                <tr>
                    <td>{$row['docente']}</td>
                    <td style='text-align:center;'>{$row['num_control']}</td>
                    <td style='text-align:center; font-weight:bold;'>{$row['total_apartados']}</td>
                    <td style='text-align:center; color:green;'>{$row['activas']}</td>
                    <td style='text-align:center; color:red;'>{$row['canceladas']}</td>
                    <td style='text-align:center;'>{$row['laboratorios_utilizados']}</td>
                    <td style='text-align:center;'>{$row['grupos_atendidos']}</td>
                </tr>
            ";
        }
    } else {
        $html .= '<tr><td colspan="7" style="text-align:center;">No hay datos para mostrar</td></tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}

function generarReporteGeneral($conn, $inicio, $fin, $filtros) {
    $where = "r.fecha BETWEEN '$inicio' AND '$fin'";
    
    if(!empty($filtros['departamento'])) {
        $where .= " AND l.IDDepartamento = " . (int)$filtros['departamento'];
    }
    if(!empty($filtros['laboratorio'])) {
        $where .= " AND r.IDLab = " . (int)$filtros['laboratorio'];
    }
    if(!empty($filtros['docente'])) {
        $where .= " AND r.IDUsuario = " . (int)$filtros['docente'];
    }
    if(!empty($filtros['grupo'])) {
        $where .= " AND r.IDGrupo = " . (int)$filtros['grupo'];
    }
    if(!empty($filtros['carrera'])) {
        $where .= " AND c.IDCarrera = " . (int)$filtros['carrera'];
    }
    if(!empty($filtros['estado'])) {
        $where .= " AND r.Estado = '" . $filtros['estado'] . "'";
    }
    
    $sql = "SELECT 
                d.nombre AS departamento,
                l.Nombre AS laboratorio,
                CONCAT(u.nombre, ' ', u.apellidos) AS docente,
                g.Nombre AS grupo,
                c.Nombre AS carrera,
                r.fecha,
                r.horaInicio,
                r.horaFin,
                r.Practica,
                r.Software,
                r.Estado
            FROM reservaciones r
            LEFT JOIN laboratorios l ON r.IDLab = l.IDLab
            LEFT JOIN departamentos d ON l.IDDepartamento = d.IDDepartamentos
            LEFT JOIN usuarios u ON r.IDUsuario = u.IDUsuarios
            LEFT JOIN grupos g ON r.IDGrupo = g.IDGrupo
            LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
            WHERE $where
            ORDER BY r.fecha DESC, r.horaInicio DESC";
    
    $result = $conn->query($sql);
    
    $html = '
    <p><strong>Período:</strong> ' . $inicio . ' al ' . $fin . '</p>
    <table>
        <thead>
            <tr>
                <th>Departamento</th>
                <th>Laboratorio</th>
                <th>Docente</th>
                <th>Grupo</th>
                <th>Carrera</th>
                <th>Fecha</th>
                <th>Hora</th>
                <th>Práctica</th>
                <th>Software</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>';
    
    if($result && $result->num_rows > 0) {
        while($row = $result->fetch_assoc()) {
            $estadoColor = $row['Estado'] == 'cancelada' ? 'color:red;' : 'color:green;';
            $html .= "
                <tr>
                    <td>{$row['departamento']}</td>
                    <td>{$row['laboratorio']}</td>
                    <td>{$row['docente']}</td>
                    <td>{$row['grupo']}</td>
                    <td>{$row['carrera']}</td>
                    <td style='text-align:center;'>{$row['fecha']}</td>
                    <td style='text-align:center;'>{$row['horaInicio']} - {$row['horaFin']}</td>
                    <td>{$row['Practica']}</td>
                    <td>{$row['Software']}</td>
                    <td style='text-align:center; {$estadoColor} font-weight:bold;'>{$row['Estado']}</td>
                </tr>
            ";
        }
    } else {
        $html .= '<tr><td colspan="10" style="text-align:center;">No hay datos para mostrar</td></tr>';
    }
    
    $html .= '</tbody></table>';
    return $html;
}
?>