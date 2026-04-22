document.addEventListener("DOMContentLoaded", function () {

    const btn = document.getElementById("logoutBtn");

    if (btn) {
        btn.addEventListener("click", function (e) {
            e.preventDefault();

            Swal.fire({
                title: '¿Cerrar sesión?',
                text: "Se cerrará tu sesión actual",
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'Sí, salir',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = "/SistemaApartadosITAP/logout.php"; 
                }
            });
        });
    }

});