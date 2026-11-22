<?php
include("../../includes/auth_gestion.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/conexion.php");
include("../../includes/admin_reportes_asistencias.php");

$deptoManager = esManager() ? nombreDeptoSesion($conn) : null;
?>

<div class="container-fluid rpt-page">

    <!-- ========================= -->
    <!-- ENCABEZADO -->
    <!-- ========================= -->
    <div class="rpt-page-header">
        <div class="rpt-page-icon"><i class="bi bi-file-earmark-bar-graph"></i></div>
        <div>
            <h3 class="rpt-page-title">Reportes</h3>
            <div class="rpt-page-subtitle">
                Genera reportes de uso de laboratorios, listos para imprimir o exportar a Excel.
            </div>
        </div>
        <?php if ($deptoManager): ?>
            <div class="rpt-depto-badge ms-auto">
                <i class="bi bi-building"></i> <?= htmlspecialchars($deptoManager) ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- ========================= -->
    <!-- PASOS -->
    <!-- ========================= -->
    <div class="rpt-pasos">
        <div class="rpt-paso activo" id="paso1"><i>1</i> Tipo de reporte</div>
        <div class="rpt-paso-linea"></div>
        <div class="rpt-paso" id="paso2"><i>2</i> Período y filtros</div>
        <div class="rpt-paso-linea"></div>
        <div class="rpt-paso" id="paso3"><i>3</i> Resultado</div>
    </div>

    <!-- ========================= -->
    <!-- TIPOS DE REPORTE -->
    <!-- ========================= -->
    <div class="rpt-panel">
        <div class="rpt-panel-titulo">Elige el tipo de reporte</div>

        <div class="row g-3">

            <div class="col-lg-4 col-md-6">
                <div class="reporte-card" role="button" tabindex="0"
                     data-reporte="reporte_departamento" data-nombre="Reporte por Departamento"
                     onclick="seleccionarReporte(this)">
                    <div class="reporte-check"><i class="bi bi-check-lg"></i></div>
                    <div class="reporte-icono"><i class="bi bi-building"></i></div>
                    <h6>Reporte por Departamento</h6>
                    <small>Uso de laboratorios agrupado por departamento.</small>
                    <ul class="reporte-incluye">
                        <li><i class="bi bi-check2"></i> Horas de uso por laboratorio</li>
                        <li><i class="bi bi-check2"></i> Totales por estado</li>
                        <li><i class="bi bi-check2"></i> Detalle por departamento</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4 col-md-6">
                <div class="reporte-card" role="button" tabindex="0"
                     data-reporte="apartados_maestro" data-nombre="Apartados por Maestro"
                     onclick="seleccionarReporte(this)">
                    <div class="reporte-check"><i class="bi bi-check-lg"></i></div>
                    <div class="reporte-icono"><i class="bi bi-person-workspace"></i></div>
                    <h6>Apartados por Maestro</h6>
                    <small>Historial de apartados de cada docente.</small>
                    <ul class="reporte-incluye">
                        <li><i class="bi bi-check2"></i> Una sección por docente</li>
                        <li><i class="bi bi-check2"></i> Laboratorios y grupos atendidos</li>
                        <li><i class="bi bi-check2"></i> Horas de uso por docente</li>
                    </ul>
                </div>
            </div>

            <div class="col-lg-4 col-md-12">
                <div class="reporte-card" role="button" tabindex="0"
                     data-reporte="reporte_general" data-nombre="Reporte General de Reservaciones"
                     onclick="seleccionarReporte(this)">
                    <div class="reporte-check"><i class="bi bi-check-lg"></i></div>
                    <div class="reporte-icono"><i class="bi bi-list-ul"></i></div>
                    <h6>Reporte General</h6>
                    <small>Todas las reservaciones con filtros combinados.</small>
                    <ul class="reporte-incluye">
                        <li><i class="bi bi-check2"></i> Filtros por docente, grupo y carrera</li>
                        <li><i class="bi bi-check2"></i> Resumen de laboratorios y docentes</li>
                        <li><i class="bi bi-check2"></i> Listado completo por fecha</li>
                    </ul>
                </div>
            </div>

        </div>

        <div class="rpt-acciones">
            <small class="text-muted" id="ayudaConfigurar">
                <i class="bi bi-info-circle"></i> Selecciona un tipo de reporte para continuar.
            </small>
            <button class="btn btn-rpt-primario" id="btnConfigurarReporte" disabled onclick="abrirModalFechas()">
                Configurar reporte <i class="bi bi-arrow-right"></i>
            </button>
        </div>
    </div>

    <!-- ========================= -->
    <!-- VISTA PREVIA -->
    <!-- ========================= -->
    <div class="rpt-panel rpt-preview" id="reportePreview">
        <div class="rpt-preview-barra">
            <div>
                <div class="rpt-panel-titulo mb-0">Vista previa</div>
                <small class="text-muted" id="resumenPreview"></small>
            </div>
            <div class="rpt-preview-botones">
                <button class="btn btn-rpt-secundario" onclick="abrirModalFechas()">
                    <i class="bi bi-sliders"></i> Cambiar filtros
                </button>
                <button class="btn btn-rpt-excel" onclick="exportarExcel()">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>
                <button class="btn btn-rpt-primario" onclick="imprimirReporte()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>

        <!-- Hoja del documento -->
        <div class="rpt-hoja-contenedor">
            <div class="rpt-hoja">
                <div id="contenidoReporte" class="rpt-doc"></div>
            </div>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- MODAL: PERÍODO Y FILTROS -->
<!-- ========================================== -->
<div class="modal fade rpt-modal" id="modalFechas" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-sliders"></i> Período y filtros
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">

                <div class="rpt-tipo-chip">
                    <i class="bi bi-file-earmark-text"></i>
                    <span id="tipoReporteSeleccionado">-</span>
                </div>

                <div class="rpt-modal-seccion">Período</div>

                <div class="rpt-presets" id="presetsFechas">
                    <button type="button" class="rpt-preset" data-preset="semana">Esta semana</button>
                    <button type="button" class="rpt-preset" data-preset="mes">Este mes</button>
                    <button type="button" class="rpt-preset" data-preset="mesAnterior">Mes anterior</button>
                    <button type="button" class="rpt-preset" data-preset="semestre">Este semestre</button>
                    <button type="button" class="rpt-preset" data-preset="anio">Este año</button>
                </div>

                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha de inicio</label>
                        <input type="date" id="modalFechaInicio" class="form-control">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Fecha de fin</label>
                        <input type="date" id="modalFechaFin" class="form-control">
                    </div>
                </div>

                <div class="rpt-modal-seccion mt-4">Filtros <small class="text-muted fw-normal">(opcionales)</small></div>

                <!-- Filtros específicos según el reporte -->
                <div id="filtrosAdicionales"></div>

            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-rpt-secundario" data-bs-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-rpt-primario" onclick="generarReporteDesdeModal()">
                    <i class="bi bi-file-earmark-bar-graph"></i> Generar reporte
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script>
    // CSS del documento: se incluye al imprimir y al exportar a Excel
    const URL_CSS_REPORTE = "<?= recurso('/css/reporte_documento.css') ?>";
</script>
<script src="<?= recurso('/js/reportes_admin.js') ?>"></script>
