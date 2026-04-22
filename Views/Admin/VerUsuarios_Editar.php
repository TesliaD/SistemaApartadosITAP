<?php 
include("../../includes/auth.php"); 
include($_SERVER['DOCUMENT_ROOT'] . "/SistemaApartadosITAP/includes/conexion.php");

$sql = "SELECT * FROM usuarios";
$result = $conn->query($sql);
?>

<?php include("../../includes/header.php");?>
<?php include("../../includes/navbar.php"); ?>

<div class="content" id="content">

    <h3 class="mb-4">
        <i class="bi bi-people"></i> Lista de Usuarios
    </h3>

    <!-- 🔍 BUSCADOR -->
    <div class="mb-3">
        <input type="text" id="buscador" class="form-control" placeholder="Buscar usuario...">
    </div>

    <!-- 📋 TABLA -->
    <div class="card p-3">
        <table class="table table-hover" id="usuarios">
            <thead class="table-dark">
                <tr>
                    <th>#</th>
                    <th>Control</th>
                    <th>Nombre</th>
                    <th>Email</th>
                    <th>Rol</th>
                    <th>Estado</th>
                    <th>Acciones</th>
                </tr>
            </thead>
            <tbody>

            <?php while($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo $row['IDUsuarios']; ?></td>
                    <td><?php echo $row['num_control']; ?></td>
                    <td><?php echo $row['nombre']; ?></td>
                    <td><?php echo $row['email']; ?></td>
                    <td><?php echo $row['rol']; ?></td>
                    <td>
                        <?php echo $row['activo'] ? 'Activo' : 'Inactivo'; ?>
                    </td>
                    <td>
                        <!-- ✏️ EDITAR -->
                        <button 
                            class="btn btn-warning btn-sm btnEditar"
                            data-id="<?php echo $row['IDUsuarios']; ?>"
                            data-nombre="<?php echo $row['nombre']; ?>"
                            data-email="<?php echo $row['email']; ?>"
                            data-rol="<?php echo $row['rol']; ?>"
                        >
                            <i class="bi bi-pencil"></i>
                        </button>

                        <!-- 🗑️ ELIMINAR -->
                        <button 
                            class="btn btn-danger btn-sm btnEliminar" 
                            data-id="<?php echo $row['IDUsuarios']; ?>"
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

<!-- ✅ BOOTSTRAP JS (OBLIGATORIO) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<!-- 🔥 TUS SCRIPTS -->
<script src="../../js/darkmode.js"></script>
<script src="../../js/logout.js"></script>
<script src="../../js/buscadorUsuarios.js"></script>
<script src="../../js/eliminarusuario.js"></script>
<script src="../../js/actualizarusuarios.js"></script>