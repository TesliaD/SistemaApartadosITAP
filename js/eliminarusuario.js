document.addEventListener("DOMContentLoaded", function () {

    document.querySelectorAll(".btnEliminar").forEach(btn => {
        btn.addEventListener("click", function () {

            let id = this.getAttribute("data-id");

            Swal.fire({
                title: "¿Eliminar usuario?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonText: "Sí, eliminar"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "/SistemaApartadosITAP/views/Admin/eliminarusuario.php?IDUsuarios=" + id;
                }
            });

        });
    });

});