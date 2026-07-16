document.addEventListener("DOMContentLoaded", function () {

    const modal = new bootstrap.Modal(document.getElementById('modalEditar'));

    // =========================
    // ABRIR MODAL Y CARGAR DATOS
    // =========================
    document.querySelectorAll(".btnEditar").forEach(btn => {
        btn.addEventListener("click", function () {

            document.getElementById("edit_id").value = this.dataset.id || '';
            document.getElementById("edit_nombre").value = this.dataset.nombre || '';
            document.getElementById("edit_num_maquinas").value = this.dataset.num_maquinas || '';
            document.getElementById("edit_descripcion").value = this.dataset.descripcion || '';
            document.getElementById("edit_num_lab").value = this.dataset.num_lab || '';
            document.getElementById("edit_departamento").value = this.dataset.departamento || '';
            document.getElementById("edit_activo").value = this.dataset.activo || '1';

            modal.show();
        });
    });

    // =========================
    // GUARDAR CAMBIOS
    // =========================
    document.getElementById("btnGuardarCambios").addEventListener("click", function () {

        // Validaciones
        const nombre = document.getElementById("edit_nombre").value.trim();
        const numMaquinas = document.getElementById("edit_num_maquinas").value.trim();
        const descripcion = document.getElementById("edit_descripcion").value.trim();
        const numLab = document.getElementById("edit_num_lab").value.trim();
        const departamento = document.getElementById("edit_departamento").value;

        if(!nombre) {
            Swal.fire("Error", "El nombre del laboratorio es requerido", "warning");
            return;
        }

        if(!numMaquinas || parseInt(numMaquinas) < 1) {
            Swal.fire("Error", "Ingresa un número válido de máquinas (mínimo 1)", "warning");
            return;
        }

        if(!descripcion || descripcion.length < 5) {
            Swal.fire("Error", "La descripción debe tener mínimo 5 caracteres", "warning");
            return;
        }

        if(!numLab) {
            Swal.fire("Error", "El número de laboratorio es requerido", "warning");
            return;
        }

        if(!departamento) {
            Swal.fire("Error", "Selecciona un departamento", "warning");
            return;
        }

        const form = document.getElementById("formEditar");
        const formData = new FormData(form);

        Swal.fire({
            title: 'Guardando cambios...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("../../controllers/actualizar_laboratorio.php", { 
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            Swal.close();

            if (data.status === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Actualizado',
                    text: data.message || 'Laboratorio actualizado correctamente',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    location.reload();
                });
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || data.error || 'No se pudo actualizar'
                });
            }

        })
        .catch(() => {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error en el servidor'
            });
        });

    });

});