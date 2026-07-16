<?php 
include("../../includes/auth.php"); 
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

$sql = "SELECT l.*, d.nombre AS nombre_departamento
        FROM laboratorios l
        LEFT JOIN departamentos d 
        ON l.IDDepartamento = d.IDDepartamentos
        ORDER BY l.IDLab DESC";
$result = $conn->query($sql);
?>

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>

<style>
    .badge-estado {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.75rem;
    }
    .badge-estado.activo {
        background: #d4edda;
        color: #155724;
    }
    .badge-estado.inactivo {
        background: #f8d7da;
        color: #721c24;
    }
</style>

<div class="content" id="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            <i class="bi bi-pc-display"></i> Lista de Laboratorios
        </h3>
        <a href="registro_laboratorio.php" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Nuevo Laboratorio
        </a>
    </div>

    <!-- BUSCADOR Y FILTROS -->
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar laboratorio...">
        </div>
        <div class="col-md-3">
            <select id="filtroDepartamento" class="form-select">
                <option value="">Todos los departamentos</option>
                <?php
                $deptos = $conn->query("SELECT IDDepartamentos, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre");
                while($depto = $deptos->fetch_assoc()):
                ?>
                <option value="<?= $depto['IDDepartamentos'] ?>"><?= $depto['nombre'] ?></option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-3">
            <select id="filtroEstado" class="form-select">
                <option value="">Todos los estados</option>
                <option value="1">Activo</option>
                <option value="0">Inactivo</option>
            </select>
        </div>
    </div>

    <!-- TABLA -->
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-hover" id="tablaLaboratorios">
                <thead class="table-dark">
                    <tr>
                        <th>Nombre</th>
                        <th>No. Máquinas</th>
                        <th>Descripción</th>
                        <th>No. Laboratorio</th>
                        <th>Departamento</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['Nombre']); ?></td>
                        <td class="text-center"><?= $row['numMaquinas']; ?></td>
                        <td><?= htmlspecialchars($row['Descripcion']); ?></td>
                        <td><?= htmlspecialchars($row['numLab']); ?></td>
                        <td><?= htmlspecialchars($row['nombre_departamento'] ?? 'Sin asignar'); ?></td>
                        <td>
                            <span class="badge-estado <?= $row['activo'] ? 'activo' : 'inactivo'; ?>">
                                <?= $row['activo'] ? 'Activo' : 'Inactivo'; ?>
                            </span>
                        </td>
                        <td>
                            <button 
                                class="btn btn-warning btn-sm btnEditar"
                                data-id="<?= $row['IDLab']; ?>"
                                data-nombre="<?= $row['Nombre']; ?>"
                                data-num_maquinas="<?= $row['numMaquinas']; ?>"
                                data-descripcion="<?= $row['Descripcion']; ?>"
                                data-num_lab="<?= $row['numLab']; ?>"
                                data-departamento="<?= $row['IDDepartamento']; ?>"
                                data-activo="<?= $row['activo']; ?>"
                            >
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button 
                                class="btn btn-danger btn-sm btnEliminar" 
                                data-id="<?= $row['IDLab']; ?>"
                                data-nombre="<?= $row['Nombre']; ?>"
                            >
                                <i class="bi bi-trash"></i>
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

</div>

<!-- ========================================== -->
<!-- MODAL EDITAR LABORATORIO (CORREGIDO) -->
<!-- ========================================== -->
<div class="modal fade" id="modalEditar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered"> 
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Laboratorio
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="row g-3">
                        <div class="col-md-12">
                            <label class="form-label">Nombre del Laboratorio</label>
                            <input type="text" id="edit_nombre" name="nombre" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">No. Máquinas</label>
                            <input type="number" id="edit_num_maquinas" name="num_maquinas" class="form-control" required min="1">
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Número de Laboratorio</label>
                            <input type="text" id="edit_num_lab" name="num_lab" class="form-control" required>
                        </div>

                        <div class="col-md-12">
                            <label class="form-label">Descripción</label>
                            <input type="text" id="edit_descripcion" name="descripcion" class="form-control" required>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Departamento</label>
                            <select id="edit_departamento" name="id_departamento" class="form-select" required>
                                <option value="">Seleccionar</option>
                                <?php
                                $deptos = $conn->query("SELECT IDDepartamentos, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre");
                                while($depto = $deptos->fetch_assoc()):
                                ?>
                                <option value="<?= $depto['IDDepartamentos'] ?>"><?= $depto['nombre'] ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="col-md-6">
                            <label class="form-label">Estado</label>
                            <select id="edit_activo" name="activo" class="form-select">
                                <option value="1">Activo</option>
                                <option value="0">Inactivo</option>
                            </select>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-primary" id="btnGuardarCambios">
                    <i class="bi bi-save"></i> Guardar
                </button>
            </div>
        </div>
    </div>
</div>

<!-- MODAL CONFIRMAR ELIMINACIÓN (CORREGIDO) -->
<div class="modal fade" id="modalEliminar" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-sm"> 
        <div class="modal-content">
            <div class="modal-header bg-danger text-white">
                <h5 class="modal-title">
                    <i class="bi bi-exclamation-triangle"></i> Confirmar Eliminación
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>¿Estás seguro de que deseas eliminar el laboratorio <strong id="eliminarNombre"></strong>?</p>
                <p class="text-muted small">Esta acción no se puede deshacer.</p>
                <input type="hidden" id="eliminarId">
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">
                    <i class="bi bi-x-circle"></i> Cancelar
                </button>
                <button type="button" class="btn btn-danger" id="btnConfirmarEliminar">
                    <i class="bi bi-trash"></i> Eliminar
                </button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- Scripts -->
<script src="../../js/logout.js"></script>
<script src="../../js/eliminarLab.js"></script>
<script src="../../js/actualizarlaboratorio.js"></script>

<!-- ========================================== -->
<!-- SCRIPT PARA FILTROS -->
<!-- ========================================== -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const buscador = document.getElementById("buscador");
    const filtroDepto = document.getElementById("filtroDepartamento");
    const filtroEstado = document.getElementById("filtroEstado");
    const tabla = document.getElementById("tablaLaboratorios");
    const filas = tabla.querySelectorAll("tbody tr");

    function filtrarTabla() {
        const busqueda = buscador.value.toLowerCase().trim();
        const deptoSeleccionado = filtroDepto.value;
        const estadoSeleccionado = filtroEstado.value;

        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            const deptoId = fila.querySelector(".btnEditar")?.dataset.departamento || '';
            const estado = fila.querySelector(".badge-estado")?.textContent.trim().toLowerCase() || '';

            let coincide = true;

            if(busqueda && !texto.includes(busqueda)) {
                coincide = false;
            }

            if(deptoSeleccionado && deptoId !== deptoSeleccionado) {
                coincide = false;
            }

            if(estadoSeleccionado) {
                const estadoMap = { '1': 'activo', '0': 'inactivo' };
                const estadoFiltro = estadoMap[estadoSeleccionado] || '';
                if(estado !== estadoFiltro) {
                    coincide = false;
                }
            }

            fila.style.display = coincide ? '' : 'none';
        });
    }

    buscador.addEventListener("keyup", filtrarTabla);
    filtroDepto.addEventListener("change", filtrarTabla);
    filtroEstado.addEventListener("change", filtrarTabla);
});
</script>