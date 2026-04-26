<?php 
include("../../includes/auth.php"); 
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");
?>

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>

<div class="content p-4">

<h3 class="mb-4">
    <i class="bi bi-calendar-check"></i> Lista de Reservaciones
</h3>

<?php
$sql = "SELECT r.*, 
               l.Nombre AS laboratorio,
               d.nombre AS docente
        FROM reservaciones r
        INNER JOIN laboratorios l ON r.IDLab = l.IDLab
        LEFT JOIN docentes d ON r.IDDocentes = d.IDDocentes";

$result = $conn->query($sql);

if(!$result){
    echo "<div class='alert alert-danger'>Error en la consulta: " . $conn->error . "</div>";
}
?>

<div class="card p-3">
<table class="table table-hover">
<thead class="table-dark">
<tr>
    <th>Fecha</th>
    <th>Hora</th>
    <th>Laboratorio</th>
    <th>Docente</th>
    <th>Estado</th>
</tr>
</thead>
<tbody>

<?php if($result && $result->num_rows > 0): ?>
    <?php while($row = $result->fetch_assoc()): ?>
    <tr>
        <td><?= $row['fecha'] ?></td>
        <td><?= $row['horaInicio'] ?> - <?= $row['horaFin'] ?></td>
        <td><?= $row['laboratorio'] ?></td>
        <td><?= $row['docente'] ?? 'N/A' ?></td>
        <td><?= $row['Estado'] ?></td>
    </tr>
    <?php endwhile; ?>
<?php else: ?>
    <tr>
        <td colspan="5" class="text-center">No hay reservaciones</td>
    </tr>
<?php endif; ?>

</tbody>
</table>
</div>

</div>

<select name="IDLab" class="form-select" required>
<?php
$labs = $conn->query("SELECT IDLab, Nombre FROM laboratorios");
while($lab = $labs->fetch_assoc()):
?>
<option value="<?= $lab['IDLab'] ?>">
    <?= $lab['Nombre'] ?>
</option>
<?php endwhile; ?>
</select>

<select name="IDDocentes" class="form-select">
<?php
$doc = $conn->query("SELECT IDDocentes, nombre FROM docentes");
while($d = $doc->fetch_assoc()):
?>
<option value="<?= $d['IDDocentes'] ?>">
    <?= $d['nombre'] ?>
</option>
<?php endwhile; ?>
</select>