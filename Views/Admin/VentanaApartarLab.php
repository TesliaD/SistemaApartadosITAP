<?php 
include("../../includes/auth.php"); 
include("../../includes/conexion.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/reservacion_admin.php")
?>

<div class="container mt-4">

    <div class="card-modern">

        <!-- HEADER -->
        <div class="card-header-modern">
            <h4>
                <i class="bi bi-calendar-check"></i> Nueva Reservación
            </h4>
            <span class="badge-admin">
                <i class="bi bi-shield-lock"></i> Administrador
            </span>
        </div>

        <!-- BODY -->
        <div class="card-body-modern">

            <div class="row g-4">

                <!-- FECHA -->
                <div class="col-md-3">
                    <label class="form-label-modern">
                        <i class="bi bi-calendar3"></i> Fecha <span class="required">*</span>
                    </label>
                    <input 
                        type="date" 
                        id="fecha" 
                        class="form-control-modern form-control"
                        min="<?= date('Y-m-d', strtotime('+1 days')) ?>">
                    <div class="info-label">
                        <i class="bi bi-info-circle"></i> Mínimo 1 día de anticipación
                    </div>
                </div>

                <!-- LAB -->
                <div class="col-md-9">
                    <label class="form-label-modern">
                        <i class="bi bi-laptop"></i> Laboratorio <span class="required">*</span>
                    </label>
                    <select id="lab" class="form-select-modern form-select">
                        <option value="">Seleccionar laboratorio</option>
                        <?php
                        $labs = $conn->query("
                            SELECT l.IDLab, l.Nombre, l.numLab, l.Descripcion, d.nombre AS depto 
                            FROM laboratorios l
                            LEFT JOIN departamentos d ON l.IDDepartamento = d.IDDepartamentos
                            WHERE l.activo = 1 
                            ORDER BY l.Nombre
                        ");
                        while($lab = $labs->fetch_assoc()):
                        ?>
                        <option value="<?= $lab['IDLab'] ?>" data-descripcion="<?= htmlspecialchars($lab['Descripcion'] ?? '') ?>">
                            <?= $lab['Nombre'] ?> (Lab <?= $lab['numLab'] ?>) - <?= $lab['depto'] ?? 'Sin departamento' ?>
                        </option>
                        <?php endwhile; ?>
                    </select>

                    <!-- DESCRIPCIÓN DEL LABORATORIO -->
                    <div id="labDescripcion" class="lab-descripcion">
                        <div class="desc-titulo">
                            <i class="bi bi-info-circle"></i> Descripción del laboratorio
                        </div>
                        <div id="labDescripcionTexto" class="desc-texto empty">
                            Selecciona un laboratorio para ver su descripción
                        </div>
                    </div>
                </div>

            </div>

            <hr class="divider-modern">

            <div class="row g-4">

                <!-- DOCENTE -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-person-badge"></i> Docente <span class="required">*</span>
                    </label>
                    <select id="docente" class="form-select-modern form-select">
                        <option value="">Seleccionar docente</option>
                        <?php
                        $docentes = $conn->query("
                            SELECT IDUsuarios, CONCAT(nombre, ' ', apellidos) AS Nombre 
                            FROM usuarios 
                            WHERE rol = 'maestro' AND activo = 1
                            ORDER BY nombre
                        ");
                        while($d = $docentes->fetch_assoc()):
                        ?>
                        <option value="<?= $d['IDUsuarios'] ?>">
                            <?= $d['Nombre'] ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                    <div class="info-label">
                        <i class="bi bi-info-circle"></i> Selecciona el docente que usará el laboratorio
                    </div>
                </div>

                <!-- GRUPO -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-people"></i> Grupo <span class="required">*</span>
                    </label>
                    <select id="grupo" class="form-select-modern form-select">
                        <option value="">Primero selecciona un docente</option>
                    </select>
                </div>

                <!-- ALUMNOS -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-person"></i> Alumnos
                    </label>
                    <input 
                        type="number" 
                        id="alumnos" 
                        class="form-control-modern form-control" 
                        readonly
                        placeholder="Selecciona un grupo">
                    <div class="info-label">
                        <i class="bi bi-arrow-right"></i> Se autocompleta al seleccionar grupo
                    </div>
                </div>

            </div>

            <div class="row g-4 mt-2">

                <!-- SOFTWARE -->
                <div class="col-md-6">
                    <label class="form-label-modern">
                        <i class="bi bi-code-square"></i> Software
                    </label>
                    <input 
                        type="text" 
                        id="software" 
                        class="form-control-modern form-control" 
                        placeholder="Ej: Visual Studio, Cisco Packet Tracer, etc.">
                </div>

                <!-- PRÁCTICA -->
                <div class="col-md-6">
                    <label class="form-label-modern">
                        <i class="bi bi-file-text"></i> Práctica
                    </label>
                    <input 
                        type="text" 
                        id="practica" 
                        class="form-control-modern form-control" 
                        placeholder="Nombre de la práctica">
                </div>

            </div>

            <hr class="divider-modern">

            <!-- SELECCIÓN DE HORAS -->
            <div class="row">
                <div class="col-12">
                    <label class="form-label-modern">
                        <i class="bi bi-clock-history"></i> Selecciona Horas <span class="required">*</span>
                    </label>
                    <div class="d-flex flex-wrap gap-2 mt-2 justify-content-center" id="contenedorHoras">
                        <?php
                        $horas = [
                            "07:00 - 08:00","08:00 - 09:00","09:00 - 10:00","10:00 - 11:00",
                            "11:00 - 12:00","12:00 - 13:00","13:00 - 14:00","14:00 - 15:00",
                            "15:00 - 16:00","16:00 - 17:00","17:00 - 18:00","18:00 - 19:00",
                            "19:00 - 20:00","20:00 - 21:00","21:00 - 22:00"
                        ];
                        foreach($horas as $h):
                        ?>
                        <button type="button" class="hora-btn-modern hora-btn">
                            <?= $h ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="info-label mt-2 text-center">
                        <i class="bi bi-info-circle"></i> 
                        Haz clic para seleccionar múltiples horas consecutivas
                    </div>
                </div>
            </div>

            <hr class="divider-modern">

            <!-- BOTONES -->
            <div class="row">
                <div class="col-md-6 mx-auto">
                    <button class="btn-guardar-modern" id="btnGuardar">
                        <i class="bi bi-check-circle"></i> Apartar Laboratorio
                    </button>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../js/reservaciones.js"></script>
<script src="../../js/logout.js"></script>

<!-- ========================= -->
<!-- SCRIPT PARA MOSTRAR DESCRIPCIÓN -->
<!-- ========================= -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const selectLab = document.getElementById("lab");
    const descContainer = document.getElementById("labDescripcion");
    const descTexto = document.getElementById("labDescripcionTexto");

    if(selectLab) {
        selectLab.addEventListener("change", function() {
            const selectedOption = this.options[this.selectedIndex];
            const descripcion = selectedOption ? selectedOption.dataset.descripcion : '';
            
            if(descripcion && descripcion.trim() !== '') {
                descTexto.textContent = descripcion;
                descTexto.className = 'desc-texto';
                descContainer.classList.add('visible');
            } else {
                descTexto.textContent = 'Este laboratorio no tiene descripción registrada';
                descTexto.className = 'desc-texto empty';
                descContainer.classList.add('visible');
            }
        });

        // Si ya hay un laboratorio seleccionado al cargar, mostrar su descripción
        if(selectLab.value) {
            const event = new Event('change');
            selectLab.dispatchEvent(event);
        }
    }
});
</script>