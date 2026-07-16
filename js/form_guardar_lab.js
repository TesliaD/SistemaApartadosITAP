document.addEventListener("DOMContentLoaded", function () {

    // Seleccionar el formulario por su ID correcto
    const form = document.querySelector("#formLaboratorio");

    if (!form) {
        console.error("Formulario no encontrado");
        return;
    }

    form.addEventListener("submit", function(e) {
        e.preventDefault();

        // =========================
        // VALIDACIONES EN FRONTEND
        // =========================
        const nombre = document.getElementById("nombre")?.value.trim();
        const num_maquinas = document.getElementById("numMaquinas")?.value.trim();
        const descripcion = document.getElementById("descripcion")?.value.trim();
        const num_lab = document.getElementById("numLab")?.value.trim();
        const id_departamento = document.getElementById("IDDepartamento")?.value;

        if(!nombre) {
            Swal.fire("Error", "Ingresa el nombre del laboratorio", "warning");
            return;
        }

        if(!num_maquinas || parseInt(num_maquinas) < 1) {
            Swal.fire("Error", "Ingresa un número válido de máquinas (mínimo 1)", "warning");
            return;
        }

        if(!descripcion || descripcion.length < 5) {
            Swal.fire("Error", "La descripción debe tener mínimo 5 caracteres", "warning");
            return;
        }

        if(!num_lab) {
            Swal.fire("Error", "Ingresa el número del laboratorio", "warning");
            return;
        }

        if(!id_departamento) {
            Swal.fire("Error", "Selecciona un departamento", "warning");
            return;
        }

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

            Swal.close();

            if(data.status === "success") {
                Swal.fire({
                    icon: 'success',
                    title: 'Laboratorio Registrado',
                    text: data.message || 'Se registró correctamente',
                    timer: 2000,
                    showConfirmButton: false
                });
                form.reset();
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: data.message || data.msg || 'No se pudo guardar'
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