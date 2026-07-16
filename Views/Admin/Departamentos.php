<?php
include("../../includes/auth.php");
include("../../includes/header.php");
include("../../includes/navbar.php");
include("../../includes/conexion.php");
?>

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
            <i class="bi bi-building"></i> Gestión de Departamentos
        </h3>
    </div>

    <!-- Formulario para agregar departamento -->
    <div class="card p-4 mb-4">
        <h5 class="mb-3"><i class="bi bi-plus-circle"></i> Nuevo Departamento</h5>
        <div class="row g-3">
            <div class="col-md-8">
                <input type="text" id="nombreDepto" class="form-control" placeholder="Nombre del departamento (Ej: Sistemas, Electrónica, etc.)">
            </div>
            <div class="col-md-4">
                <button class="btn btn-success w-100" onclick="guardarDepartamento()">
                    <i class="bi bi-save"></i> Guardar Departamento
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla de departamentos -->
    <div class="card p-3">
        <h5 class="mb-3"><i class="bi bi-list"></i> Departamentos Registrados</h5>
        <div class="table-responsive">
            <table class="table table-hover" id="tablaDepartamentos">
                <thead class="table-dark">
                    <tr>
                        <th>ID</th>
                        <th>Nombre</th>
                        <th>Estado</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                    <?php
                    $sql = "SELECT IDDepartamentos, nombre, activo FROM departamentos ORDER BY nombre";
                    $result = $conn->query($sql);
                    while($depto = $result->fetch_assoc()):
                    ?>
                    <tr>
                        <td><?= $depto['IDDepartamentos'] ?></td>
                        <td><?= htmlspecialchars($depto['nombre']) ?></td>
                        <td>
                            <span class="badge-estado <?= $depto['activo'] ? 'activo' : 'inactivo' ?>">
                                <?= $depto['activo'] ? 'Activo' : 'Inactivo' ?>
                            </span>
                        </td>
                        <td>
                            <button class="btn btn-warning btn-sm btnEditarDepto" 
                                    data-id="<?= $depto['IDDepartamentos'] ?>"
                                    data-nombre="<?= htmlspecialchars($depto['nombre']) ?>"
                                    data-activo="<?= $depto['activo'] ?>">
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button class="btn btn-danger btn-sm btnEliminarDepto" 
                                    data-id="<?= $depto['IDDepartamentos'] ?>"
                                    data-nombre="<?= htmlspecialchars($depto['nombre']) ?>">
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
<!-- MODAL EDITAR DEPARTAMENTO -->
<!-- ========================================== -->
<div class="modal fade" id="modalEditarDepto" tabindex="-1">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title"><i class="bi bi-pencil-square"></i> Editar Departamento</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditarDepto">
                    <input type="hidden" id="edit_depto_id" name="id">
                    <div class="mb-3">
                        <label class="form-label">Nombre</label>
                        <input type="text" id="edit_depto_nombre" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Estado</label>
                        <select id="edit_depto_activo" class="form-select">
                            <option value="1">Activo</option>
                            <option value="0">Inactivo</option>
                        </select>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" id="btnGuardarDepto">Guardar</button>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
<script src="../../js/logout.js"></script>
<script src="../../js/departamentos.js"></script>

</body>
</html>