<?php 
include("../../includes/auth.php"); 
include("../../includes/conexion.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
?>

<div class="container mt-4">

    <!-- ========================= -->
    <!-- FORMULARIO -->
    <!-- ========================= -->
    <div class="card shadow-lg p-4 border-0">

        <h4 class="mb-4 text-primary">
            <i class="bi bi-calendar-check"></i> Nueva Reservación
        </h4>

        <div class="row g-4">

            <!-- FECHA -->
            <div class="col-md-3">
                <label class="fw-bold">Fecha</label>
                <input type="date" id="fecha" class="form-control shadow-sm">
            </div>

            <!-- HORAS -->
            <div class="col-md-5">
                <label class="fw-bold">Selecciona Horas</label>

                <div class="d-flex flex-wrap gap-2 mt-2">

                    <?php
                    $horas = [
                        "07:00","08:00","09:00","10:00","11:00","12:00",
                        "13:00","14:00","15:00","16:00","17:00","18:00",
                        "19:00","20:00"
                    ];

                    foreach($horas as $h){
                        echo "<button type='button' class='btn btn-outline-primary hora-btn'>$h</button>";
                    }
                    ?>

                </div>
            </div>

            <!-- LAB -->
            <div class="col-md-4">
                <label class="fw-bold">Laboratorio</label>
                <select id="lab" class="form-select shadow-sm">
                <?php
                $labs = $conn->query("SELECT IDLab, Nombre FROM laboratorios");
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

        <!-- EVENTO -->
        <div class="col-md-4">
            <label>Evento</label>
            <select id="evento" class="form-select shadow-sm">
                <option value="">Seleccionar evento</option>
                <?php
                $eventos = $conn->query("SELECT IDEvento, Practica FROM eventos");
                while($e = $eventos->fetch_assoc()):
                ?>
                <option value="<?= $e['IDEvento'] ?>">
                    <?= $e['Practica'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- DOCENTE -->
        <div class="col-md-4">
            <label>Docente</label>
            <select id="docente" class="form-select shadow-sm">
                <option value="">Seleccionar docente</option>
                <?php
                $docentes = $conn->query("SELECT IDDocentes, Nombre FROM docentes");
                while($d = $docentes->fetch_assoc()):
                ?>
                <option value="<?= $d['IDDocentes'] ?>">
                    <?= $d['Nombre'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- GRUPO -->
        <div class="col-md-4">
            <label>Grupo</label>
            <select id="grupo" class="form-select shadow-sm">
                <option value="">Seleccionar grupo</option>
                <?php
                $grupos = $conn->query("
                    SELECT g.IDGrupo, c.Nombre AS carrera, g.Semestre
                    FROM grupos g
                    LEFT JOIN carreras c ON g.IDCarrera = c.IDCarrera
                ");
                while($g = $grupos->fetch_assoc()):
                ?>
                <option value="<?= $g['IDGrupo'] ?>">
                    <?= $g['carrera'] ?> - Sem <?= $g['Semestre'] ?>
                </option>
                <?php endwhile; ?>
            </select>
        </div>

        <!-- SOFTWARE -->
        <div class="col-md-4">
            <label>Software</label>
            <input type="text" id="software" class="form-control shadow-sm">
        </div>

        <!-- ALUMNOS -->
        <div class="col-md-2">
            <label>Alumnos</label>
            <input type="number" id="alumnos" class="form-control shadow-sm">
        </div>

        <!-- BOTÓN -->
        <div class="col-md-2 d-flex align-items-end">
            <button class="btn btn-success w-100 shadow" id="btnGuardar">
                <i class="bi bi-save"></i> Apartar
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

            <div class="col-md-3">
                <input type="date" id="fechaInicio" class="form-control">
            </div>

            <div class="col-md-3">
                <input type="date" id="fechaFin" class="form-control">
            </div>

            <div class="col-md-3">
                <input type="text" id="buscar" 
                       placeholder="Buscar docente o laboratorio"
                       class="form-control">
            </div>

            <div class="col-md-3">
                <button class="btn btn-primary w-100" onclick="cargarTabla(1)">
                    Filtrar
                </button>
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
                            <th>Evento</th>
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



    <!-- CSS -->
    <link rel="stylesheet" href="/SistemaApartadosITAP/css/reservaciones.css">

    <!-- LIBS -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- TU JS -->
    <script src="../../js/reservaciones.js"></script>