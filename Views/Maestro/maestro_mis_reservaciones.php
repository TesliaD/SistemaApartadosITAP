<?php
include("../../includes/conexion.php");
include("../../includes/auth_maestro.php");
include("../../includes/header.php");
include("../../includes/navbar_maestros.php");

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

<style>
    body {
        background: #f5f7fb;
        padding-top: 70px;
    }

    .card-modern {
        background: #ffffff;
        border-radius: 20px;
        border: none;
        box-shadow: 0 10px 40px rgba(29, 53, 87, 0.08);
        max-width: 1200px;
        margin: 0 auto;
        overflow: hidden;
    }

    .card-modern .card-header-modern {
        background: linear-gradient(135deg, #1d3557, #2a4a7a);
        color: white;
        padding: 18px 25px;
        border: none;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .card-modern .card-header-modern h4 {
        margin: 0;
        font-weight: 600;
        font-size: 1.3rem;
    }

    .card-modern .card-header-modern h4 i {
        margin-right: 12px;
        color: #a8dadc;
    }

    .card-modern .card-body-modern {
        padding: 25px 30px;
    }

    .form-control-modern,
    .form-select-modern {
        border: 2px solid #e2e8f0;
        border-radius: 10px;
        padding: 10px 16px;
        font-size: 0.95rem;
        transition: all 0.2s ease;
        background: #f8fafc;
        height: 46px;
        color: #1d3557;
    }

    .form-control-modern:focus,
    .form-select-modern:focus {
        border-color: #457b9d;
        box-shadow: 0 0 0 4px rgba(69, 123, 157, 0.12);
        background: #ffffff;
    }

    .filtros-card {
        background: #f8fafc;
        border-radius: 16px;
        padding: 18px 20px;
        border: 1px solid #e8f0f5;
        margin-bottom: 20px;
    }

    .table-reservaciones {
        font-size: 0.9rem;
    }

    .table-reservaciones thead th {
        background: #1d3557 !important;
        color: white !important;
        font-size: 0.75rem;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        padding: 12px 10px;
        text-align: center;
        border: none;
    }

    .table-reservaciones tbody td {
        padding: 10px 10px;
        vertical-align: middle;
        text-align: center;
        border-bottom: 1px solid #e8f0f5;
    }

    .badge-estado {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.7rem;
        font-weight: 600;
    }
    .badge-estado.activa { background: #d4edda; color: #155724; }
    .badge-estado.cancelada { background: #f8d7da; color: #721c24; }
    .badge-estado.finalizada { background: #e2e3e5; color: #383d41; }

    .btn-accion {
        border-radius: 8px;
        padding: 4px 12px;
        font-size: 0.75rem;
        font-weight: 500;
        transition: all 0.2s ease;
    }

    .btn-accion:hover {
        transform: translateY(-1px);
    }

    @media (max-width: 768px) {
        .card-modern .card-body-modern {
            padding: 18px;
        }
        .table-reservaciones {
            font-size: 0.75rem;
        }
        .table-reservaciones thead th,
        .table-reservaciones tbody td {
            padding: 6px 4px;
        }
    }
</style>

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