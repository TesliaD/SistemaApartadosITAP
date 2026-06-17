<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include("../includes/conexion.php");

header('Content-Type: application/json');

if(!isset($_SESSION['id'])){
    echo json_encode(["error" => "No autorizado"]);
    exit;
}

require_once('../vendor/autoload.php');

use PhpOffice\PhpSpreadsheet\IOFactory;

if($_FILES['archivoAlumnos']['error'] !== UPLOAD_ERR_OK) {
    echo json_encode(["error" => "Error al subir archivo"]);
    exit;
}

$grupoId = $_POST['grupoId'];
$archivo = $_FILES['archivoAlumnos']['tmp_name'];

// Verificar que el grupo pertenece al maestro
$verificar = $conn->prepare("SELECT IDGrupo FROM grupos WHERE IDGrupo = ? AND IDUsuario = ?");
$verificar->bind_param("ii", $grupoId, $_SESSION['id']);
$verificar->execute();
if($verificar->get_result()->num_rows == 0){
    echo json_encode(["error" => "No tienes permiso para modificar este grupo"]);
    exit;
}
$verificar->close();

try {
    $spreadsheet = IOFactory::load($archivo);
    $hoja = $spreadsheet->getActiveSheet();
    $datos = $hoja->toArray();
    
    // Eliminar encabezado
    array_shift($datos);
    
    $procesados = 0;
    $duplicados = 0;
    $errores = 0;
    $mensajes = [];
    
    foreach($datos as $fila) {
        // Validar campos obligatorios: Matrícula y Nombre
        if(empty($fila[0]) || empty($fila[1])) continue;
        
        $noControl = trim($fila[0]);
        $nombre = trim($fila[1]);
        $plan = isset($fila[2]) ? trim($fila[2]) : null; // Columna C: Plan (ISC, IMA, etc.)
        
        // PRIMERO: Verificar si el alumno YA EXISTE en ESTE grupo
        $check = $conn->prepare("SELECT IDAlumnos FROM alumnos WHERE NoControl = ? AND IDGrupo = ?");
        $check->bind_param("si", $noControl, $grupoId);
        $check->execute();
        $check->store_result();
        
        if($check->num_rows > 0) {
            $duplicados++;
            $mensajes[] = "Duplicado (ya en este grupo): $noControl - $nombre";
            $check->close();
            continue;
        }
        $check->close();
        
        // SEGUNDO: Verificar si el alumno existe en OTRO grupo
        $checkGlobal = $conn->prepare("SELECT IDAlumnos, IDGrupo FROM alumnos WHERE NoControl = ?");
        $checkGlobal->bind_param("s", $noControl);
        $checkGlobal->execute();
        $resultGlobal = $checkGlobal->get_result();
        
        if($resultGlobal->num_rows > 0) {
            // El alumno existe en otro grupo, lo movemos a este grupo
            $alumno = $resultGlobal->fetch_assoc();
            $grupoAnterior = $alumno['IDGrupo'] ?: 'NINGUNO';
            
            $sql = "UPDATE alumnos SET nombre = ?, plan = ?, IDGrupo = ? WHERE NoControl = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssis", $nombre, $plan, $grupoId, $noControl);
            
            if($stmt->execute()) {
                $procesados++;
                $mensajes[] = "Movido de grupo $grupoAnterior a este: $noControl - $nombre (Plan: $plan)";
            } else {
                $errores++;
                $mensajes[] = "Error al mover: $noControl";
            }
            $stmt->close();
        } else {
            // Alumno nuevo, insertar normalmente
            $sql = "INSERT INTO alumnos (NoControl, nombre, plan, IDGrupo) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("sssi", $noControl, $nombre, $plan, $grupoId);
            
            if($stmt->execute()) {
                $procesados++;
            } else {
                $errores++;
                $mensajes[] = "Error al insertar: $noControl - " . $stmt->error;
            }
            $stmt->close();
        }
        $checkGlobal->close();
    }
    
    // Actualizar cantidad de alumnos en el grupo
    $sqlUpdate = "UPDATE grupos SET cantidadAlumnos = (SELECT COUNT(*) FROM alumnos WHERE IDGrupo = ?) WHERE IDGrupo = ?";
    $stmtUpdate = $conn->prepare($sqlUpdate);
    $stmtUpdate->bind_param("ii", $grupoId, $grupoId);
    $stmtUpdate->execute();
    $stmtUpdate->close();
    
    // Respuesta detallada
    $respuesta = [
        "procesados" => $procesados,
        "duplicados" => $duplicados,
        "errores" => $errores,
        "total" => $procesados + $duplicados,
        "mensajes" => $mensajes
    ];
    
    echo json_encode($respuesta);
    
} catch(Exception $e) {
    echo json_encode(["error" => "Error al procesar Excel: " . $e->getMessage()]);
}
?>