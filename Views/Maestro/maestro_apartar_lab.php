<?php
include("../../includes/conexion.php");
include("../../includes/auth_maestro.php");
include("../../includes/header.php");
include("../../includes/navbar_maestros.php");
include("../../includes/maestro_apartar_lab.php");

$idUsuario = $_SESSION['id'] ?? 0;

// Obtener datos del usuario
$sqlUsuario = "SELECT nombre, apellidos, num_control FROM usuarios WHERE IDUsuarios = ?";
$stmt = $conn->prepare($sqlUsuario);
$stmt->bind_param("i", $idUsuario);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows > 0) {
    $usuario = $result->fetch_assoc();
    $nombreCompleto = trim($usuario['nombre'] . " " . ($usuario['apellidos'] ?? ''));
    $numControl = $usuario['num_control'] ?? '';
} else {
    $nombreCompleto = "Docente";
    $numControl = '';
}
?>

<div class="container mt-4">

    <div class="card-modern">

        <div class="card-header-modern">
            <h4>
                <i class="bi bi-calendar-check"></i> Nueva Reservación
            </h4>
            <span style="background:rgba(255,255,255,0.15); color:white; padding:4px 14px; border-radius:20px; font-size:0.7rem;">
                <i class="bi bi-person"></i> <?= htmlspecialchars($nombreCompleto) ?>
            </span>
        </div>

        <div class="card-body-modern">

            <div class="row g-4">

                <!-- FECHA -->
                <div class="col-md-3">
                    <label class="form-label-modern">
                        <i class="bi bi-calendar3"></i> Fecha <span style="color:#e63946;">*</span>
                    </label>
                    <input
                        type="date"
                        id="fecha"
                        class="form-control-modern form-control"
                        min="<?= date('Y-m-d', strtotime('+1 day')) ?>"
                        max="<?= date('Y-m-d', strtotime('+3 days')) ?>">
                    <div class="info-label">
                        <i class="bi bi-info-circle"></i> 1 a 3 días de anticipación
                    </div>
                </div>

                <!-- HORAS -->
                <div class="col-md-5">
                    <label class="form-label-modern">
                        <i class="bi bi-clock-history"></i> Selecciona una Hora <span style="color:#e63946;">*</span>
                    </label>
                    <div class="d-flex flex-wrap gap-2 mt-2">
                        <?php
                        $horas = [
                            "07:00 - 08:00","08:00 - 09:00","09:00 - 10:00","10:00 - 11:00",
                            "11:00 - 12:00","12:00 - 13:00","13:00 - 14:00","14:00 - 15:00",
                            "15:00 - 16:00","16:00 - 17:00","17:00 - 18:00","18:00 - 19:00",
                            "19:00 - 20:00","20:00 - 21:00","21:00 - 22:00"
                        ];
                        foreach($horas as $h){
                            echo "<button type='button' class='hora-btn-modern hora-btn'>$h</button>";
                        }
                        ?>
                    </div>
                    <div class="info-label mt-1">
                        <i class="bi bi-info-circle"></i> Solo puedes seleccionar una hora por reservación
                    </div>
                </div>

                <!-- LAB -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-laptop"></i> Laboratorio <span style="color:#e63946;">*</span>
                    </label>
                    <select id="lab" class="form-select-modern form-select">
                        <option value="">Seleccionar laboratorio</option>
                        <?php
                        $labs = $conn->query("
                            SELECT IDLab, Nombre, numLab, Descripcion
                            FROM laboratorios
                            WHERE activo = 1
                            ORDER BY Nombre
                        ");
                        while($lab = $labs->fetch_assoc()):
                        ?>
                        <option value="<?= $lab['IDLab'] ?>" data-descripcion="<?= htmlspecialchars($lab['Descripcion'] ?? '') ?>">
                            <?= $lab['Nombre'] ?> (Lab <?= $lab['numLab'] ?>)
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

            <div class="row g-3">

                <!-- DOCENTE -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-person-badge"></i> Docente
                    </label>
                    <input
                        type="text"
                        id="docente"
                        class="form-control-modern form-control"
                        value="<?= htmlspecialchars($nombreCompleto) ?>"
                        readonly>
                </div>

                <!-- GRUPO -->
                <div class="col-md-4">
                    <label class="form-label-modern">
                        <i class="bi bi-people"></i> Grupo <span style="color:#e63946;">*</span>
                    </label>
                    <select id="grupo" class="form-select-modern form-select">
                        <option value="">Seleccionar grupo</option>
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
                </div>

            </div>

            <div class="row g-3 mt-2">

                <!-- SOFTWARE -->
                <div class="col-md-6">
                    <label class="form-label-modern">
                        <i class="bi bi-code-square"></i> Software
                    </label>
                    <input
                        type="text"
                        id="software"
                        class="form-control-modern form-control"
                        placeholder="Ej: Visual Studio, Cisco Packet Tracer">
                </div>

                <!-- PRACTICA -->
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

            <div class="row mt-4">
                <div class="col-md-4 mx-auto">
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
<script src="../../js/reservaciones_maestro.js"></script>
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