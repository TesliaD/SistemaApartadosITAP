<?php
include("../../includes/conexion.php");
include("../../includes/auth_maestro.php");
include("../../includes/header.php");
include("../../includes/navbar_maestros.php");
include("../../includes/maestro_mis_reservaciones.php");

$idUsuario = $_SESSION['id'] ?? 0;

// Obtener nombre del maestro
$sqlUsuario = "SELECT nombre, apellidos FROM usuarios WHERE IDUsuarios = ?";
$stmt = $conn->prepare($sqlUsuario);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();
$usuario = $result->fetch_assoc();
$nombreCompleto = trim($usuario['nombre'] . " " . ($usuario['apellidos'] ?? ''));
?>

<div class="container mt-4">

    <div class="card-modern">

        <div class="card-header-modern">
            <h4>
                <i class="bi bi-list"></i> Mis Reservaciones
            </h4>
            <span style="background:rgba(255,255,255,0.15); color:white; padding:4px 14px; border-radius:20px; font-size:0.7rem;">
                <i class="bi bi-person"></i> <?= htmlspecialchars($nombreCompleto) ?>
            </span>
        </div>

        <div class="card-body-modern">

            <!-- FILTROS -->
            <div class="filtros-card">
                <div class="row g-2">
                    <div class="col-md-2">
                        <input type="date" id="fechaInicio" class="form-control-modern form-control" placeholder="Fecha inicio">
                    </div>
                    <div class="col-md-2">
                        <input type="date" id="fechaFin" class="form-control-modern form-control" placeholder="Fecha fin">
                    </div>
                    <div class="col-md-3">
                        <input type="text" id="buscar" class="form-control-modern form-control" placeholder="Buscar laboratorio o práctica">
                    </div>
                    <div class="col-md-2">
                        <select id="filtroEstado" class="form-select-modern form-select">
                            <option value="">Todos los estados</option>
                            <option value="activa">Activa</option>
                            <option value="cancelada">Cancelada</option>
                            <option value="finalizada">Finalizada</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <div class="d-flex gap-2">
                            <button class="btn btn-primary flex-grow-1" onclick="cargarTabla(1)" style="border-radius:10px; background:#1d3557; border:none;">
                                <i class="bi bi-funnel"></i> Filtrar
                            </button>
                            <button class="btn btn-secondary" onclick="limpiarFiltros()" style="border-radius:10px; background:#6c757d; border:none;">
                                <i class="bi bi-x-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- TABLA -->
            <div class="table-responsive">
                <table class="table table-reservaciones" id="tablaReservaciones">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Horario</th>
                            <th>Laboratorio</th>
                            <th>Docente</th>
                            <th>Grupo</th>
                            <th>Práctica</th>
                            <th>Software</th>
                            <th>Estado</th>
                            <th>Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                        <!-- JS llena aquí -->
                    </tbody>
                </table>
            </div>

            <!-- PAGINACIÓN -->
            <div id="paginacion" class="mt-3 text-center"></div>

        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../js/reservaciones_maestro.js"></script>
<script src="../../js/logout.js"></script>