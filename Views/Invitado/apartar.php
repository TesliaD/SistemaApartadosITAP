<?php
require_once __DIR__ . "/../../includes/auth_invitados.php";
require_once __DIR__ . "/../../includes/conexion.php";
require_once __DIR__ . "/../../includes/reglas_reservacion.php";
require_once __DIR__ . "/../../includes/header.php";
require_once __DIR__ . "/../../includes/navbar_invitados.php";
$labs = $conn->query("SELECT IDLab, Nombre, numLab FROM laboratorios WHERE activo = 1 ORDER BY Nombre");
$horas = ["07:00 - 08:00","08:00 - 09:00","09:00 - 10:00","10:00 - 11:00","11:00 - 12:00","12:00 - 13:00","13:00 - 14:00","14:00 - 15:00","15:00 - 16:00","16:00 - 17:00","17:00 - 18:00","18:00 - 19:00","19:00 - 20:00","20:00 - 21:00","21:00 - 22:00"];
?>
<link rel="stylesheet" href="<?= recurso('/css/reservacion-admin.css') ?>">
<div class="container mt-4 mb-5">
    <div class="card-modern">
        <div class="card-header-modern">
            <h4><i class="bi bi-calendar-plus"></i> Hacer una reservación</h4>
            <span class="badge-admin"><i class="bi bi-person"></i> <?= htmlspecialchars($_SESSION['usuario'] ?? 'Invitado') ?></span>
        </div>
        <div class="card-body-modern">
            <div class="row g-4">
                <div class="col-md-4">
                    <label class="form-label-modern" for="fecha"><i class="bi bi-calendar3"></i> Fecha</label>
                    <input type="date" id="fecha" class="form-control-modern form-control" min="<?= htmlspecialchars(fechaMinReservacion()) ?>" max="<?= htmlspecialchars(fechaMaxReservacion()) ?>" value="<?= htmlspecialchars(fechaMinReservacion()) ?>" required>
                    <div class="info-label">Puedes reservar desde hoy hasta 2 días después.</div>
                </div>
                <div class="col-md-8">
                    <label class="form-label-modern" for="lab"><i class="bi bi-laptop"></i> Laboratorio</label>
                    <select id="lab" class="form-select-modern form-select" required>
                        <option value="">Selecciona un laboratorio</option>
                        <?php if ($labs): while ($row = $labs->fetch_assoc()): ?>
                        <option value="<?= (int)$row['IDLab'] ?>"><?= htmlspecialchars($row['Nombre']) ?> (Lab <?= htmlspecialchars($row['numLab']) ?>)</option>
                        <?php endwhile; endif; ?>
                    </select>
                </div>
                <div class="col-12">
                    <label class="form-label-modern"><i class="bi bi-clock"></i> Horario</label>
                    <div class="d-flex flex-wrap gap-2 justify-content-center" id="contenedorHoras">
                        <?php foreach ($horas as $hora): ?><button type="button" class="hora-btn-modern hora-btn"><?= htmlspecialchars($hora) ?></button><?php endforeach; ?>
                    </div>
                    <div class="info-label text-center">Selecciona uno o más bloques consecutivos.</div>
                </div>
                <div class="col-md-6">
                    <label class="form-label-modern" for="practica">Motivo o práctica</label>
                    <input type="text" id="practica" class="form-control-modern form-control" maxlength="255" placeholder="Opcional">
                </div>
                <div class="col-md-6">
                    <label class="form-label-modern" for="software">Software requerido</label>
                    <input type="text" id="software" class="form-control-modern form-control" maxlength="100" placeholder="Opcional">
                </div>
                <div class="col-md-6 mx-auto">
                    <button type="button" class="btn-guardar-modern w-100" id="btnGuardar"><i class="bi bi-check-circle"></i> Confirmar reservación</button>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="<?= recurso('/js/reservacion_invitado.js') ?>"></script>
</body>
</html>