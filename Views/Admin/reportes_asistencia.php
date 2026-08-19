<?php
include("../../includes/auth.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/conexion.php");
include("../../includes/admin_reportes_asistencias.php");
?>

<style>

</style>

<div class="container mt-4">

    <div class="card shadow-lg p-4 border-0">
        <h4 class="mb-4 text-primary">
            <i class="bi bi-file-earmark-text"></i>
            Generador de Reportes
        </h4>

        <p class="text-muted">Selecciona el tipo de reporte que deseas generar y luego haz clic en "Configurar Reporte".</p>

        <!-- Tarjetas de tipos de reporte - SOLO 3 -->
        <div class="row g-4 mt-2">

            <!-- Reporte 1: Reporte por Departamento -->
            <div class="col-md-4">
                <div class="reporte-card" data-reporte="reporte_departamento" onclick="seleccionarReporte(this)">
                    <span class="icono"><i class="bi bi-building"></i></span>
                    <h6>Reporte por Departamento</h6>
                    <small>Uso de laboratorios por departamento</small>
                </div>
            </div>

            <!-- Reporte 2: Apartados por Maestro -->
            <div class="col-md-4">
                <div class="reporte-card" data-reporte="apartados_maestro" onclick="seleccionarReporte(this)">
                    <span class="icono"><i class="bi bi-person-workspace"></i></span>
                    <h6>Apartados por Maestro</h6>
                    <small>Historial de apartados por docente</small>
                </div>
            </div>

            <!-- Reporte 3: Reporte General de Reservaciones -->
            <div class="col-md-4">
                <div class="reporte-card" data-reporte="reporte_general" onclick="seleccionarReporte(this)">
                    <span class="icono"><i class="bi bi-list-ul"></i></span>
                    <h6>Reporte General de Reservaciones</h6>
                    <small>Todas las reservaciones con filtros</small>
                </div>
            </div>

        </div>

        <!-- Botón para abrir modal -->
        <div class="mt-4 text-center">
            <button class="btn btn-primary btn-lg" id="btnConfigurarReporte" disabled onclick="abrirModalFechas()">
                <i class="bi bi-gear"></i> Configurar Reporte
            </button>
            <small class="d-block text-muted mt-2">Selecciona un tipo de reporte para habilitar el botón</small>
        </div>

    </div>

    <!-- Vista previa del reporte -->
    <div class="reporte-preview" id="reportePreview">
        <div id="contenidoReporte">
            <!-- Se llena con JS -->
        </div>
        <div class="mt-3 text-center">
            <button class="btn btn-success me-2" onclick="exportarExcel()">
                <i class="bi bi-file-excel"></i> Exportar Excel
            </button>
            <button class="btn btn-secondary" onclick="imprimirReporte()">
                <i class="bi bi-printer"></i> Imprimir
            </button>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- MODAL PARA SELECCIONAR FECHAS Y FILTROS -->
<!-- ========================================== -->
<div class="modal fade" id="modalFechas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-calendar-range"></i> Configurar Reporte
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div id="infoReporte" class="alert alert-info">
                    <strong>Tipo de reporte:</strong> <span id="tipoReporteSeleccionado">-</span>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha de Inicio</label>
                        <input type="date" id="modalFechaInicio" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha de Fin</label>
                        <input type="date" id="modalFechaFin" class="form-control">
                    </div>
                </div>

                <!-- Filtros específicos según el reporte -->
                <div id="filtrosAdicionales" class="mt-3">
                    <!-- Se llena con JS dinámicamente -->
                </div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" onclick="generarReporteDesdeModal()">
                    <i class="bi bi-file-earmark-text"></i> Generar Reporte
                </button>
            </div>
        </div>
    </div>
</div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/reportes_admin.js"></script>