<?php 
include("../../includes/auth.php"); 
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

$sql = "SELECT l.*, d.nombre AS nombre_departamento
        FROM laboratorios l
        INNER JOIN departamentos d 
        ON l.IDDepartamento = d.IDDepartamentos";
$result = $conn->query($sql);
?>

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>

<div class="content" id="content">

    <h3 class="mb-4">
        <i class="bi bi-pc-display"></i> Lista de laboratorios
    </h3>

    <!-- BUSCADOR -->
    <div class="mb-3">
        <input type="text" id="buscador" class="form-control" placeholder="Buscar usuario...">
    </div>

    <!-- TABLA -->
    <div class="card p-3">
        <table class="table table-hover" id="usuarios">
            <thead class="table-dark">
                <tr>
                    <th>Nombre</th>
                    <th>Número Maquinas</th>
                    <th>Descripcion</th>
                    <th>Activo</th>
                    <th>Número Laboratorio</th>
                    <th>Departamento</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>

            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['Nombre']; ?></td>
                    <td><?php echo $row['numMaquinas']; ?></td>
                    <td><?php echo $row['Descripcion']; ?></td>
                    <td>
                        <?php echo $row['activo'] ? 'Activo' : 'Inactivo'; ?>
                    </td>
                    <td><?php echo $row['numLab']; ?></td>
                    <td><?php echo $row['nombre_departamento']; ?></td>
                    <td>
                        <!--EDITAR -->
                        <button 
                            class="btn btn-warning btn-sm btnEditar"
                            data-id="<?php echo $row['Nombre']; ?>"
                            data-nombre="<?php echo $row['numMaquinas']; ?>"
                            data-email="<?php echo $row['Descripcion']; ?>"
                            data-rol="<?php echo $row['numLab']; ?>"
                        >
                            <i class="bi bi-pencil"></i>
                        </button>

                        <!--ELIMINAR -->
                        <button 
                            class="btn btn-danger btn-sm btnEliminar" 
                            data-id="<?php echo $row['IDLab']; ?>"
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

<!-- 🪟 MODAL EDITAR -->
<div class="modal fade" id="modalEditar" tabindex="-1">
  <div class="modal-dialog">
    <div class="modal-content">

      <div class="modal-header">
        <h5 class="modal-title">Editar Usuario</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
      </div>

      <div class="modal-body">
        <form id="formEditar">

          <input type="hidden" id="edit_id" name="id">

          <div class="mb-3">
            <label>Nombre</label>
            <input type="text" id="edit_nombre" name="nombre" class="form-control">
          </div>

          <div class="mb-3">
            <label>Email</label>
            <input type="email" id="edit_email" name="email" class="form-control">
          </div>

          <div class="mb-3">
            <label>Rol</label>
            <select id="edit_rol" name="rol" class="form-select">
                <option value="">Seleccionar</option>
                <option value="administrador">Administrador</option>
                <option value="invitado">Invitado</option>
                <option value="maestro">Maestro</option>
            </select>
          </div>

        </form>
      </div>

      <div class="modal-footer">
        <button class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
        <button class="btn btn-primary" id="btnGuardarCambios">Guardar</button>
      </div>

    </div>
  </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- TUS SCRIPTS -->
<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>
<script src="../../js/eliminarLab.js"></script>
