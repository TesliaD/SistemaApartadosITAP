<?php
include("../../includes/conexion.php");
include("../../includes/auth_maestro.php");
include("../../includes/header.php");
include("../../includes/navbar_maestros.php");

$idUsuario = $_SESSION['id'] ?? 0;

// Obtener datos del usuario desde la base de datos
$sqlUsuario = "SELECT nombre, apellidos, num_control FROM usuarios WHERE IDUsuarios = ?";
$stmt = $conn->prepare($sqlUsuario);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $nombreCompleto = trim($usuario['nombre'] . " " . ($usuario['apellidos'] ?? ''));
    $numControl = $usuario['num_control'] ?? '';
} else {
    $nombreCompleto = "Docente";
    $numControl = '';
}
?>

<style>
        /* Para que el navbar no tape el contenido */
    body {
        padding-top: 70px; /* Ajusta según la altura de tu navbar */
    }

    /* Si tu navbar es fijo/sticky */
    .navbar-fixed-top,
    .navbar-sticky-top {
        position: fixed;
        top: 0;
        width: 100%;
        z-index: 1000;
    }

    /* Espacio extra para el primer container */
    .container.mt-4:first-of-type {
        margin-top: 20px !important;
    }
</style>

<div class="container mt-4">

    <div class="card shadow-lg p-4 border-0">

        <h4 class="mb-4 text-primary">
            <i class="bi bi-calendar-check"></i>
            Nueva Reservación
        </h4>

        <div class="row g-4">

            <!-- FECHA -->
            <div class="col-md-3">
                <label class="fw-bold">Fecha</label>
                <input
                    type="date"
                    id="fecha"
                    class="form-control shadow-sm"
                    min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                    max="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                <small class="text-muted">Solo puedes reservar con 1 a 3 días de anticipación. No disponible domingos.</small>
            </div>

            <!-- HORAS -->
            <div class="col-md-5">

                <label class="fw-bold">
                    Selecciona Horas
                </label>

                <div class="d-flex flex-wrap gap-2 mt-2">

                    <?php

                    $horas = [
                        "07:00","08:00","09:00","10:00",
                        "11:00","12:00","13:00","14:00",
                        "15:00","16:00","17:00","18:00",
                        "19:00","20:00","21:00","22:00"
                    ];

                    foreach($horas as $h){

                        echo "
                        <button
                            type='button'
                            class='btn btn-outline-primary hora-btn'>
                            $h
                        </button>";
                    }

                    ?>

                </div>

            </div>

            <!-- LAB -->
            <div class="col-md-4">

                <label class="fw-bold">
                    Laboratorio
                </label>

                <select id="lab" class="form-select shadow-sm">

                    <?php

                    $labs = $conn->query("
                        SELECT IDLab, Nombre
                        FROM laboratorios
                        ORDER BY Nombre
                    ");

                    while($lab = $labs->fetch_assoc()):

                    ?>

                        <option value="<?= $lab['IDLab'] ?>">
                            <?= $lab['Nombre'] ?>
                        </option>

                    <?php endwhile; ?>

                </select>

            </div>

        </div>

        <hr>

        <div class="row g-3">

            <!-- DOCENTE LOGUEADO -->
            <div class="col-md-4">
                <label>Docente</label>
                <input 
                    type="text" 
                    id="docente"  
                    name="docente"
                    class="form-control" 
                    value="<?= htmlspecialchars($nombreCompleto) ?>" 
                    readonly>
            </div>
            
            <!-- GRUPO -->
            <div class="col-md-4">

                <label>Grupo</label>

                <select
                    id="grupo"
                    class="form-select shadow-sm">

                    <option value="">
                        Seleccionar grupo
                    </option>

                </select>

            </div>

            <!-- SOFTWARE -->
            <div class="col-md-4">

                <label>Software</label>

                <input
                    type="text"
                    id="software"
                    class="form-control shadow-sm">

            </div>

            <!-- PRACTICA -->
            <div class="col-md-4">

                <label>Práctica</label>

                <input
                    type="text"
                    id="practica"
                    class="form-control shadow-sm">

            </div>

            <!-- ALUMNOS -->
            <div class="col-md-2">

                <label>Alumnos</label>

                <input
                    type="number"
                    id="alumnos"
                    class="form-control shadow-sm"
                    readonly>

            </div>

            <!-- BOTON -->
            <div class="col-md-2 d-flex align-items-end">

                <button
                    class="btn btn-success w-100 shadow"
                    id="btnGuardar">

                    <i class="bi bi-save"></i>
                    Apartar

                </button>

            </div>

        </div>

    </div>

</div>

<!-- ========================= -->
<!-- TABLA -->
<!-- ========================= -->
<div class="container mt-4">

    <!-- FILTROS -->
<div class="card shadow-sm p-3 mb-3">
    <label class="fw-bold mb-2">Filtrar reservaciones</label>
    <div class="row g-2">
        <div class="col-md-2">
            <input type="date" id="fechaInicio" class="form-control" placeholder="Fecha inicio">
        </div>
        <div class="col-md-2">
            <input type="date" id="fechaFin" class="form-control" placeholder="Fecha fin">
        </div>
        <div class="col-md-3">
            <input type="text" id="buscar" 
                   placeholder="Buscar docente o laboratorio"
                   class="form-control">
        </div>
        <div class="col-md-2">
            <select id="filtroEstado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="activa">Activa</option>
                <option value="cancelada">Cancelada</option>
                <option value="finalizada">Finalizada</option>
            </select>
        </div>
        <div class="col-md-3">
            <div class="d-flex gap-2">
                <button class="btn btn-primary flex-grow-1" onclick="cargarTabla(1)">
                    <i class="bi bi-funnel"></i> Filtrar
                </button>
                <button class="btn btn-secondary" onclick="limpiarFiltros()" title="Limpiar filtros">
                    <i class="bi bi-x-circle"></i>
                </button>
            </div>
        </div>
    </div>
</div>


    <!-- TABLA -->
    <div class="card shadow border-0">

        <div class="card-header bg-dark text-white">
            <i class="bi bi-list"></i> Reservaciones
        </div>

        <div class="card-body">

            <div class="table-responsive">
                <table class="table table-hover align-middle" id="tablaReservaciones">

                    <thead class="table-dark text-center">
                        <tr>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Lab</th>
                            <th>Docente</th>
                            <th>Grupo</th>
                            <th>Práctica</th>
                            <th>Software</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>

                    <tbody class="text-center">
                        <!-- JS llena aquí -->
                    </tbody>

                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div id="paginacion" class="mt-3 text-center"></div>

        </div>

    </div>

</div>

<!-- Bootstrap JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- CSS -->
<link rel="stylesheet" href="/SistemaApartadosITAP/css/reservaciones.css">

<!-- LIBS -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<!-- TU JS -->
<script src="../../js/reservaciones_maestro.js"></script>


    
<!-- Scripts -->
<script src="../../js/logout.js"></script>
<script src="../../js/eliminarLab.js"></script>
