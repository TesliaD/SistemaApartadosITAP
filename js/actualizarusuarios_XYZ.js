document.addEventListener("DOMContentLoaded", () => {

    const modalElement = document.getElementById("modalEditar");

    if (!modalElement) return;

    const modal = new bootstrap.Modal(modalElement);

    /* =========================
    ABRIR MODAL
    ========================= */
    document.querySelectorAll(".btnEditar").forEach(btn => {

        btn.addEventListener("click", function () {

            document.getElementById("edit_id").value =
                this.dataset.id;

            document.getElementById("edit_nombre").value =
                this.dataset.nombre;

            document.getElementById("edit_email").value =
                this.dataset.email;

            document.getElementById("edit_rol").value =
                this.dataset.rol;

            modal.show();

        });

    });

    /* =========================
    GUARDAR CAMBIOS
    ========================= */
    document.getElementById("btnGuardarCambios")
    .addEventListener("click", () => {

        const nombre =
            document.getElementById("edit_nombre").value.trim();

        const email =
            document.getElementById("edit_email").value.trim();

        const rol =
            document.getElementById("edit_rol").value;

        /* VALIDACIONES */

        if(nombre.length < 3){

            Swal.fire(
                "Nombre inválido",
                "El nombre debe tener mínimo 3 caracteres.",
                "warning"
            );

            return;
        }

        const regexEmail =
            /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

        if(!regexEmail.test(email)){

            Swal.fire(
                "Correo inválido",
                "Ingresa un correo válido.",
                "warning"
            );

            return;
        }

        if(rol === ""){

            Swal.fire(
                "Rol requerido",
                "Debes seleccionar un rol.",
                "warning"
            );

            return;
        }

        /* ENVIAR */

        const form =
            document.getElementById("formEditar");

        const formData =
            new FormData(form);

        fetch("../../controllers/actualizar_usuario.php", {

            method: "POST",
            body: formData

        })

        .then(response => response.json())

        .then(data => {

            console.log(data);

            if(data.status === "success"){

                Swal.fire(
                    "Actualizado",
                    "Usuario actualizado correctamente.",
                    "success"
                ).then(() => {

                    location.reload();

                });

            }else{

                Swal.fire(
                    "Error",
                    data.error || "Error desconocido.",
                    "error"
                );

            }

        })

        .catch(error => {

            console.error(error);

            Swal.fire(
                "Error",
                "Ocurrió un problema al conectar con el servidor.",
                "error"
            );

        });

    });

});