<?php
include("../../includes/auth.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/conexion.php");
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

<style>
    .reporte-preview {
        background: #f8f9fa;
        padding: 20px;
        border-radius: 8px;
        border: 1px solid #dee2e6;
        display: none;
        margin-top: 20px;
    }
    .reporte-preview table {
        width: 100%;
        border-collapse: collapse;
        font-size: 13px;
    }
    .reporte-preview table th,
    .reporte-preview table td {
        border: 1px solid #000;
        padding: 5px 8px;
        text-align: left;
    }
    .reporte-preview table th {
        background: #f1f1f1;
        text-align: center;
    }
    .reporte-header {
        text-align: center;
        margin-bottom: 20px;
    }
    .reporte-header h5 {
        margin: 0;
        font-weight: bold;
    }
    .reporte-header .subtitulo {
        font-size: 14px;
        margin-top: 5px;
    }
    .footer-reporte {
        margin-top: 30px;
        font-size: 12px;
        text-align: center;
    }
    .firma-linea {
        display: inline-block;
        width: 200px;
        border-top: 1px solid #000;
        margin-top: 30px;
        padding-top: 5px;
    }
</style>

<div class="container mt-4">

    <div class="card shadow-lg p-4 border-0">
        <h4 class="mb-4 text-primary">
            <i class="bi bi-file-earmark-text"></i>
            Generar Reporte de Asistencia a Prácticas
        </h4>

        <div class="row g-3">

            <!-- FILTROS -->
            <div class="col-md-3">
                <label class="fw-bold">Laboratorio</label>
                <select id="filtroLab" class="form-select">
                    <option value="">Todos</option>
                    <?php
                    $labs = $conn->query("SELECT IDLab, Nombre FROM laboratorios WHERE activo = 1 ORDER BY Nombre");
                    while($lab = $labs->fetch_assoc()):
                    ?>
                    <option value="<?= $lab['IDLab'] ?>"><?= $lab['Nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="fw-bold">Docente</label>
                <select id="filtroDocente" class="form-select">
                    <option value="">Todos</option>
                    <?php
                    $docentes = $conn->query("
                        SELECT IDUsuarios, CONCAT(nombre, ' ', apellidos) AS Nombre 
                        FROM usuarios 
                        WHERE rol = 'maestro' AND activo = 1
                        ORDER BY nombre
                    ");
                    while($d = $docentes->fetch_assoc()):
                    ?>
                    <option value="<?= $d['IDUsuarios'] ?>"><?= $d['Nombre'] ?></option>
                    <?php endwhile; ?>
                </select>
            </div>

            <div class="col-md-3">
                <label class="fw-bold">Grupo</label>
                <select id="filtroGrupo" class="form-select">
                    <option value="">Todos</option>
                </select>
            </div>

            <div class="col-md-3">
                <label class="fw-bold">Reservación</label>
                <select id="filtroReservacion" class="form-select">
                    <option value="">Seleccionar reservación</option>
                </select>
            </div>

        </div>

        <div class="row g-3 mt-2">
            <div class="col-md-3">
                <label class="fw-bold">Fecha Inicio</label>
                <input type="date" id="fechaInicio" class="form-control">
            </div>
            <div class="col-md-3">
                <label class="fw-bold">Fecha Fin</label>
                <input type="date" id="fechaFin" class="form-control">
            </div>
            <div class="col-md-6 d-flex align-items-end">
                <button class="btn btn-primary me-2" onclick="generarReporte()">
                    <i class="bi bi-search"></i> Generar Reporte
                </button>
                <button class="btn btn-success me-2" onclick="exportarExcel()">
                    <i class="bi bi-file-excel"></i> Exportar Excel
                </button>
                <button class="btn btn-secondary" onclick="imprimirReporte()">
                    <i class="bi bi-printer"></i> Imprimir
                </button>
            </div>
        </div>

    </div>

    <!-- Vista previa del reporte -->
    <div class="reporte-preview" id="reportePreview">
        <div id="contenidoReporte">
            <!-- Se llena con JS -->
        </div>
    </div>

</div>

<!-- SweetAlert -->
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/reportes_admin.js"></script>
