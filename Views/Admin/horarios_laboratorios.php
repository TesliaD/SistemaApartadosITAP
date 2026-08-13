<?php
include("../../includes/auth.php");
include("../../includes/conexion.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/horarios.php")
?>

<div class="container mt-4">

    <div class="card-modern">

        <div class="card-header-modern">
            <h4>
                <i class="bi bi-clock"></i> Gestión de Horarios - Laboratorios
            </h4>
            <span class="badge-admin" style="background:rgba(255,255,255,0.15); color:white; padding:4px 14px; border-radius:20px; font-size:0.7rem;">
                <i class="bi bi-shield-lock"></i> Administrador
            </span>
        </div>

        <div class="card-body-modern">

            <!-- LEYENDA -->
            <div class="leyenda">
                <div class="leyenda-item">
                    <span class="color-box habilitada"></span>
                    <span>Horario habilitado</span>
                </div>
                <div class="leyenda-item">
                    <span class="color-box deshabilitada"></span>
                    <span>Horario deshabilitado</span>
                </div>
                <div class="leyenda-item">
                    <i class="bi bi-info-circle" style="color:#457b9d;"></i>
                    <span>Haz clic en una hora para cambiar su estado</span>
                </div>
            </div>

            <hr class="divider-modern">

            <!-- SELECCIÓN DE LABORATORIO -->
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label-modern">
                        <i class="bi bi-laptop"></i> Seleccionar Laboratorio
                    </label>
                    <select id="labSeleccionado" class="form-select-modern form-select">
                        <option value="">Seleccionar laboratorio</option>
                        <?php
                        $labs = $conn->query("
                            SELECT l.IDLab, l.Nombre, l.numLab, d.nombre AS depto 
                            FROM laboratorios l
                            LEFT JOIN departamentos d ON l.IDDepartamento = d.IDDepartamentos
                            WHERE l.activo = 1 
                            ORDER BY l.Nombre
                        ");
                        while($lab = $labs->fetch_assoc()):
                        ?>
                        <option value="<?= $lab['IDLab'] ?>">
                            <?= $lab['Nombre'] ?> (Lab <?= $lab['numLab'] ?>) - <?= $lab['depto'] ?? 'Sin departamento' ?>
                        </option>
                        <?php endwhile; ?>
                    </select>
                </div>

                <div class="col-md-6">
                    <label class="form-label-modern">
                        <i class="bi bi-calendar3"></i> Aplicar a día
                    </label>
                    <select id="diaAplicar" class="form-select-modern form-select">
                        <option value="todos">Todos los días</option>
                        <option value="lunes">Lunes</option>
                        <option value="martes">Martes</option>
                        <option value="miercoles">Miércoles</option>
                        <option value="jueves">Jueves</option>
                        <option value="viernes">Viernes</option>
                        <option value="sabado">Sábado</option>
                        <option value="domingo">Domingo</option>
                    </select>
                </div>
            </div>

            <hr class="divider-modern">

            <!-- HORARIOS DISPONIBLES -->
            <div class="row">
                <div class="col-12">
                    <label class="form-label-modern">
                        <i class="bi bi-clock-history"></i> Horarios del Laboratorio
                    </label>
                    <div class="d-flex flex-wrap gap-2 mt-2 justify-content-center" id="contenedorHorarios">
                        <?php
                        $horas = [
                            "07:00 - 08:00","08:00 - 09:00","09:00 - 10:00","10:00 - 11:00",
                            "11:00 - 12:00","12:00 - 13:00","13:00 - 14:00","14:00 - 15:00",
                            "15:00 - 16:00","16:00 - 17:00","17:00 - 18:00","18:00 - 19:00",
                            "19:00 - 20:00","20:00 - 21:00","21:00 - 22:00"
                        ];
                        foreach($horas as $h):
                        ?>
                        <button type="button" class="hora-btn-modern hora-horario" data-hora="<?= $h ?>">
                            <?= $h ?>
                        </button>
                        <?php endforeach; ?>
                    </div>
                    <div class="info-label mt-2 text-center">
                        <i class="bi bi-info-circle"></i> 
                        Haz clic en una hora para habilitarla o deshabilitarla
                    </div>
                </div>
            </div>

            <hr class="divider-modern">

            <!-- BOTONES -->
            <div class="row">
                <div class="col-md-4 mx-auto">
                    <button class="btn-guardar-modern" id="btnGuardarHorarios">
                        <i class="bi bi-save"></i> Guardar Horarios
                    </button>
                </div>
            </div>

            <!-- TABLA DE HORARIOS GUARDADOS -->
            <hr class="divider-modern">

            <div class="row">
                <div class="col-12">
                    <h5 class="mb-3"><i class="bi bi-table"></i> Horarios Configurados</h5>
                    <div class="table-responsive">
                        <table class="table table-horarios" id="tablaHorarios">
                            <thead>
                                <tr>
                                    <th>Laboratorio</th>
                                    <th>Día</th>
                                    <th>Horario</th>
                                    <th>Estado</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td colspan="5" class="text-center py-3">Selecciona un laboratorio para ver sus horarios</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </div>
    </div>

</div>

<!-- Scripts -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="../../js/horarios_laboratorios.js"></script>
<script src="../../js/logout.js"></script>