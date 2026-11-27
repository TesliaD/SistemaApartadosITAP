<?php require_once __DIR__ . "/config.php"; ?>
<link rel="stylesheet" href="<?= recurso('/css/navbar.css') ?>">
<div class="overlay" id="overlay"></div>
<nav class="navbar navbar-dark px-3">
    <button class="btn btn-light" id="toggleSidebar" type="button"><i class="bi bi-list"></i></button>
    <span class="navbar-brand ms-2"><i class="bi bi-pc-display"></i> Centro de Cómputo</span>
    <div class="ms-auto d-flex align-items-center gap-3 text-white">
        <button class="btn btn-sm btn-light" id="darkModeBtn" type="button" aria-label="Cambiar tema"><i class="bi bi-moon"></i></button>
        <div class="text-end"><strong><?= htmlspecialchars($_SESSION['usuario'] ?? 'Invitado') ?></strong><br><small>Invitado</small></div>
    </div>
</nav>
<div class="sidebar" id="sidebar">
    <h5 class="mb-4">Menú</h5>
    <a href="../Admin/ReservacionesPorDia.php"><i class="bi bi-calendar-day"></i><span>Reservaciones diarias</span></a>
    <a href="apartar.php"><i class="bi bi-calendar-plus"></i><span>Hacer reservación</span></a>
    <hr>
    <a href="../../logout.php"><i class="bi bi-box-arrow-right"></i><span>Salir</span></a>
</div>
<script src="<?= recurso('/js/darkmode.js') ?>"></script>