<?php 
include("../../includes/auth.php"); 
include("../../includes/conexion.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
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

<!-- TU JS -->
<script src="../../js/reservaciones.js"></script>
<script src="../../js/logout.js"></script>
<script src="../../js/eliminarLab.js"></script>