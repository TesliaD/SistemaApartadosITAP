<?php 
include("../../includes/auth.php"); 
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

// Consulta con JOIN para obtener el nombre del departamento
$sql = "SELECT u.*, d.nombre AS departamento_nombre 
        FROM usuarios u
        LEFT JOIN departamentos d ON u.IDDepartamento = d.IDDepartamentos
        ORDER BY u.IDUsuarios DESC";
$result = $conn->query($sql);
?>

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>

<style>
    .badge-rol {
        font-size: 0.75rem;
        padding: 4px 10px;
        border-radius: 20px;
    }
    .badge-rol.administrador { background: #dc3545; color: white; }
    .badge-rol.maestro { background: #0d6efd; color: white; }
    .badge-rol.invitado { background: #6c757d; color: white; }
    
    .badge-departamento {
        font-size: 0.7rem;
        padding: 3px 8px;
        border-radius: 12px;
        background: #e9ecef;
        color: #495057;
    }
</style>

<div class="content" id="content">

    <div class="d-flex justify-content-between align-items-center mb-4">
        <h3 class="mb-0">
            <i class="bi bi-people"></i> Lista de Usuarios
        </h3>
        <a href="registro_usuario.php" class="btn btn-primary">
            <i class="bi bi-person-plus"></i> Nuevo Usuario
        </a>
    </div>

    <!-- FILTROS Y BUSCADOR -->
    <div class="row g-2 mb-3">
        <div class="col-md-6">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar por nombre, correo o control...">
        </div>
        <div class="col-md-3">
            <select id="filtroRol" class="form-select">
                <option value="">Todos los roles</option>
                <option value="administrador">Administrador</option>
                <option value="maestro">Maestro</option>
                <option value="invitado">Invitado</option>
            </select>
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
    </div>

    <!-- TABLA -->
    <div class="card p-3">
        <div class="table-responsive">
            <table class="table table-hover" id="usuarios">
                <thead class="table-dark">
                    <tr>
                        <th>#</th>
                        <th>Control</th>
                        <th>Nombre</th>
                        <th>Área</th>
                        <th>Email</th>
                        <th>Rol</th>
                        <th>Departamento</th>
                        <th>Acciones</th>
                    </tr>
                </thead>
                <tbody>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['IDUsuarios']); ?></td>
                        <td><?= htmlspecialchars($row['num_control']); ?></td>
                        <td><?= htmlspecialchars($row['nombre'] . ' ' . $row['apellidos']); ?></td>
                        <td><?= htmlspecialchars($row['area']); ?></td>
                        <td><?= htmlspecialchars($row['email']); ?></td>
                        <td>
                            <span class="badge-rol <?= $row['rol']; ?>">
                                <?= ucfirst($row['rol']); ?>
                            </span>
                        </td>
                        <td>
                            <?php if($row['departamento_nombre']): ?>
                                <span class="badge-departamento">
                                    <?= htmlspecialchars($row['departamento_nombre']); ?>
                                </span>
                            <?php else: ?>
                                <span class="text-muted">Sin asignar</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <button 
                                class="btn btn-warning btn-sm btnEditar"
                                data-id="<?= $row['IDUsuarios']; ?>"
                                data-nombre="<?= $row['nombre']; ?>"
                                data-apellidos="<?= $row['apellidos']; ?>"
                                data-area="<?= $row['area']; ?>"
                                data-email="<?= $row['email']; ?>"
                                data-rol="<?= $row['rol']; ?>"
                                data-departamento="<?= $row['IDDepartamento']; ?>"
                            >
                                <i class="bi bi-pencil"></i>
                            </button>
                            <button 
                                class="btn btn-danger btn-sm btnEliminar" 
                                data-id="<?= $row['IDUsuarios']; ?>"
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
<!-- MODAL EDITAR (ACTUALIZADO) -->
<!-- ========================================== -->
<div class="modal fade" id="modalEditar" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <i class="bi bi-pencil-square"></i> Editar Usuario
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <form id="formEditar">
                    <input type="hidden" id="edit_id" name="id">

                    <div class="row g-3">
                        <!-- NOMBRE -->
                        <div class="col-md-6">
                            <label class="form-label">Nombre</label>
                            <input type="text" id="edit_nombre" name="nombre" class="form-control" required>
                        </div>

                        <!-- APELLIDOS -->
                        <div class="col-md-6">
                            <label class="form-label">Apellidos</label>
                            <input type="text" id="edit_apellidos" name="apellidos" class="form-control" required>
                        </div>

                        <!-- ÁREA -->
                        <div class="col-md-6">
                            <label class="form-label">Área</label>
                            <input type="text" id="edit_area" name="area" class="form-control" required>
                        </div>

                        <!-- EMAIL -->
                        <div class="col-md-6">
                            <label class="form-label">Email</label>
                            <input type="email" id="edit_email" name="email" class="form-control" required>
                        </div>

                        <!-- ROL -->
                        <div class="col-md-6">
                            <label class="form-label">Rol</label>
                            <select id="edit_rol" name="rol" class="form-select" required>
                                <option value="">Seleccionar</option>
                                <option value="administrador">Administrador</option>
                                <option value="invitado">Invitado</option>
                                <option value="maestro">Maestro</option>
                            </select>
                        </div>

                        <!-- DEPARTAMENTO (NUEVO) -->
                        <div class="col-md-6">
                            <label class="form-label">Departamento</label>
                            <select id="edit_departamento" name="IDDepartamento" class="form-select">
                                <option value="">Sin departamento</option>
                                <?php
                                $deptos = $conn->query("SELECT IDDepartamentos, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre");
                                while($depto = $deptos->fetch_assoc()):
                                ?>
                                <option value="<?= $depto['IDDepartamentos'] ?>"><?= $depto['nombre'] ?></option>
                                <?php endwhile; ?>
                            </select>
                            <small class="text-muted">Obligatorio solo para maestros</small>
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

<!--BOOTSTRAP JS-->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!--TUS SCRIPTS -->
<script src="../../js/logout.js"></script>
<script src="../../js/buscadorUsuarios.js"></script>
<script src="../../js/eliminarusuario_XYZ.js"></script>
<script src="../../js/actualizarusuarios_XYZ.js"></script>

<!-- ========================================== -->
<!-- SCRIPT PARA FILTROS Y BUSCADOR -->
<!-- ========================================== -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    const buscador = document.getElementById("buscador");
    const filtroRol = document.getElementById("filtroRol");
    const filtroDepto = document.getElementById("filtroDepartamento");
    const tabla = document.getElementById("usuarios");
    const filas = tabla.querySelectorAll("tbody tr");

    function filtrarTabla() {
        const busqueda = buscador.value.toLowerCase().trim();
        const rolSeleccionado = filtroRol.value;
        const deptoSeleccionado = filtroDepto.value;

        filas.forEach(fila => {
            const texto = fila.textContent.toLowerCase();
            const rol = fila.querySelector(".badge-rol")?.textContent.toLowerCase().trim() || '';
            const depto = fila.querySelector(".badge-departamento")?.textContent.toLowerCase().trim() || 'sin asignar';

            let coincide = true;

            if(busqueda && !texto.includes(busqueda)) {
                coincide = false;
            }

            if(rolSeleccionado && rol !== rolSeleccionado) {
                coincide = false;
            }

            // Para el filtro de departamento, necesitamos obtener el ID del departamento
            if(deptoSeleccionado) {
                const deptoId = fila.querySelector(".btnEditar")?.dataset.departamento || '';
                if(deptoId !== deptoSeleccionado) {
                    coincide = false;
                }
            }

            fila.style.display = coincide ? '' : 'none';
        });
    }

    buscador.addEventListener("keyup", filtrarTabla);
    filtroRol.addEventListener("change", filtrarTabla);
    filtroDepto.addEventListener("change", filtrarTabla);
});
</script>