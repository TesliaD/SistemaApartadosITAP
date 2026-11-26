<?php
include '../../includes/auth_maestro.php';
include '../../includes/config.php';
include '../../includes/header.php';
include '../../includes/navbar_maestros.php';
?>
<link rel="stylesheet" href="<?= recurso('/css/control_asistencia.css') ?>">

<div class="container-fluid asistencia-page">
    <div class="asistencia-header">
        <div class="asistencia-icon"><i class="bi bi-clipboard2-check"></i></div>
        <div>
            <h3>Asistencia a prácticas</h3>
            <p>Registra la asistencia de tus alumnos y genera el reporte de la práctica seleccionada.</p>
        </div>
    </div>
    <div class="asistencia-panel filtros-panel no-imprimir">
        <div class="asistencia-panel-title"><i class="bi bi-funnel"></i> Selección de práctica</div>
        <div class="row g-3 align-items-end">
            <div class="col-lg-6"><label class="form-label">Mi grupo</label><select id="grupoAsistencia" class="form-select"><option value="">Selecciona un grupo</option></select></div>
            <div class="col-lg-6"><label class="form-label">Práctica / reservación</label><select id="reservacionAsistencia" class="form-select" disabled><option value="">Primero selecciona un grupo</option></select></div>
        </div>
    </div>
    <div id="areaReporteAsistencia">
        <div id="encabezadoReporteAsistencia" class="reporte-asistencia-encabezado"></div>
        <div id="grupoResumen" class="grupo-resumen d-none"></div>
        <div class="row g-4">
            <div class="col-xl-8">
                <div class="asistencia-panel h-100">
                    <div class="asistencia-table-header">
                        <div><div class="asistencia-panel-title mb-1"><i class="bi bi-people"></i> Lista de alumnos</div><small id="ayudaAsistencia" class="text-muted">Selecciona un grupo y una práctica para registrar asistencia.</small></div>
                        <div class="asistencia-actions no-imprimir"><button type="button" id="btnTodosPresentes" class="btn btn-asistencia-secondary" disabled><i class="bi bi-check2-all"></i> Todos presentes</button><button type="button" id="btnGuardarAsistencia" class="btn btn-asistencia-primary" disabled><i class="bi bi-save"></i> Guardar</button><button type="button" id="btnImprimirAsistencia" class="btn btn-asistencia-secondary" disabled><i class="bi bi-printer"></i> Imprimir reporte</button></div>
                    </div>
                    <div class="table-responsive"><table class="table asistencia-table align-middle mb-0"><thead><tr><th>#</th><th>Núm. de control</th><th>Alumno</th><th>Plan</th><th class="text-center">Asistencia</th></tr></thead><tbody id="tablaAsistenciaBody"><tr><td colspan="5" class="estado-vacio">Aún no hay información para mostrar.</td></tr></tbody></table></div>
                </div>
            </div>
            <div class="col-xl-4 no-imprimir"><div class="asistencia-panel h-100"><div class="asistencia-panel-title"><i class="bi bi-clock-history"></i> Registros de prácticas</div><div id="historialAsistencia" class="historial-asistencia"><div class="estado-vacio">Selecciona un grupo para consultar sus registros.</div></div></div></div>
        </div>
    </div>
</div>
<script>const BASE_URL = <?= json_encode(BASE_URL) ?>;</script>
<script src="<?= recurso('/js/control_asistencia.js') ?>"></script>