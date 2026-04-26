<?php 
include("../../includes/auth.php"); 
include("../../includes/conexion.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
?>

<div class="content p-4">

<h3>Reservación de Laboratorios</h3>

<!-- FORMULARIO -->
<div class="card p-3 mb-4">
<form id="formReservacion">

<input type="date" name="fecha" required>

<input type="time" name="horaInicio" required>
<input type="time" name="horaFin" required>

<!-- LABORATORIOS -->
<select name="IDLab" required>
<?php
$labs = $conn->query("SELECT IDLab, Nombre FROM laboratorios");
while($lab = $labs->fetch_assoc()):
?>
<option value="<?= $lab['IDLab'] ?>">
    <?= $lab['Nombre'] ?>
</option>
<?php endwhile; ?>
</select>

<!-- DOCENTES -->
<select name="IDDocentes">
<?php
$doc = $conn->query("SELECT IDDocentes, nombre FROM docentes");
while($d = $doc->fetch_assoc()):
?>
<option value="<?= $d['IDDocentes'] ?>">
    <?= $d['nombre'] ?>
</option>
<?php endwhile; ?>
</select>

<button type="submit">Guardar</button>

</form>
</div>

<!-- TABLA -->
<?php
$sql = "SELECT r.*, l.Nombre AS laboratorio, d.nombre AS docente
        FROM reservaciones r
        JOIN laboratorios l ON r.IDLab = l.IDLab
        LEFT JOIN docentes d ON r.IDDocentes = d.IDDocentes";

$result = $conn->query($sql);
?>

<table class="table">
<tr>
<th>Fecha</th>
<th>Hora</th>
<th>Lab</th>
<th>Docente</th>
</tr>

<?php while($row = $result->fetch_assoc()): ?>
<tr>
<td><?= $row['fecha'] ?></td>
<td><?= $row['horaInicio'] ?> - <?= $row['horaFin'] ?></td>
<td><?= $row['laboratorio'] ?></td>
<td><?= $row['docente'] ?></td>
</tr>
<?php endwhile; ?>

</table>

<!-- CALENDARIO -->
<link href="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.css" rel="stylesheet">
<script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.8/index.global.min.js"></script>

<div id="calendar"></div>

<script src="../../js/reservaciones.js"></script>

<!--SCRIPTS -->
<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>
<script src="../../js/eliminarLab.js"></script>
<script src="../../js/actualizarlaboratorio.js"></script>


</div>