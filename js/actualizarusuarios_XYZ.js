document.addEventListener("DOMContentLoaded", function () {

    const modal = new bootstrap.Modal(document.getElementById('modalEditar'));

    document.querySelectorAll(".btnEditar").forEach(btn => {
        btn.addEventListener("click", function () {

            document.getElementById("edit_id").value = this.getAttribute("data-id");
            document.getElementById("edit_nombre").value = this.getAttribute("data-nombre");
            document.getElementById("edit_email").value = this.getAttribute("data-email");
            document.getElementById("edit_rol").value = this.getAttribute("data-rol");

            modal.show();
        });
    });

    document.getElementById("btnGuardarCambios").addEventListener("click", function () {

        const form = document.getElementById("formEditar");
        const formData = new FormData(form);

        fetch("../../controllers/actualizar_usuario.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            if (data.status === "success") {
                Swal.fire("Actualizado", "Usuario actualizado", "success")
                    .then(() => location.reload());
            } else {
                Swal.fire("Error", "No se pudo actualizar", "error");
            }

        });

    });

});