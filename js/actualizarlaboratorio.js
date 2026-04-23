document.addEventListener("DOMContentLoaded", function () {

    const modal = new bootstrap.Modal(document.getElementById('modalEditar'));

    // ABRIR MODAL Y CARGAR DATOS
    document.querySelectorAll(".btnEditar").forEach(btn => {
        btn.addEventListener("click", function () {

            document.getElementById("edit_id").value = this.dataset.id;
            document.getElementById("edit_nombre").value = this.dataset.nombre;
            document.getElementById("edit_num_maquinas").value = this.dataset.num_maquinas;
            document.getElementById("edit_descripcion").value = this.dataset.descripcion;
            document.getElementById("edit_num_lab").value = this.dataset.num_lab;
            document.getElementById("edit_departamento").value = this.dataset.departamento;
            document.getElementById("edit_activo").value = this.dataset.activo;

            modal.show();
        });
    });

    // GUARDAR CAMBIOS
    document.getElementById("btnGuardarCambios").addEventListener("click", function () {

        const form = document.getElementById("formEditar");
        const formData = new FormData(form);

        fetch("actualizar_laboratorio.php", { // 👈 CAMBIADO
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            if (data.status === "success") {
                Swal.fire("Actualizado", "Laboratorio actualizado", "success")
                    .then(() => location.reload());
            } else {
                Swal.fire("Error", data.error || "No se pudo actualizar", "error");
            }

        })
        .catch(() => {
            Swal.fire("Error", "Error en el servidor", "error");
        });

    });

});