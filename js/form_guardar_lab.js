document.addEventListener("DOMContentLoaded", function () {

    const form = document.querySelector("#formLabG");

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        const formData = new FormData(form);

        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("../../controllers/guardar_laboratorio.php", {
            method: "POST",
            body: formData
        })
        .then(res => res.json())
        .then(data => {

            if(data.status === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Laboratorio Registrado',
                    text: 'Se registró correctamente'
                });

                form.reset();

            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo guardar'
                });
            }

        })
        .catch((err) => {
            console.error(err);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: 'Error en el servidor'
            });
        });

    });

});