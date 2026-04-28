document.addEventListener("click", function (e) {
    if (e.target.closest(".btnEliminar")) {

        let btn = e.target.closest(".btnEliminar");
        let id = btn.getAttribute("data-id");

        Swal.fire({
            title: "¿Eliminar usuario?",
            icon: "warning",
            showCancelButton: true,
            confirmButtonText: "Sí, eliminar"
        }).then((result) => {
            if (result.isConfirmed) {
                window.location.href = "../../controllers/eliminarusuario.php?IDUsuarios=" + id;
            }
        });
    }
});