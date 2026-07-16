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
                this.dataset.id || '';

            document.getElementById("edit_nombre").value =
                this.dataset.nombre || '';

            document.getElementById("edit_apellidos").value =
                this.dataset.apellidos || '';

            document.getElementById("edit_area").value =
                this.dataset.area || '';

            document.getElementById("edit_email").value =
                this.dataset.email || '';

            document.getElementById("edit_rol").value =
                this.dataset.rol || '';

            document.getElementById("edit_departamento").value =
                this.dataset.departamento || '';

            modal.show();

        });

    });

    /* =========================
    VALIDAR DEPARTAMENTO SEGÚN ROL
    ========================= */
    const rolSelect = document.getElementById("edit_rol");
    const deptoSelect = document.getElementById("edit_departamento");

    if(rolSelect && deptoSelect) {
        rolSelect.addEventListener("change", function() {
            if(this.value === "maestro") {
                deptoSelect.required = true;
                // Cambiar el mensaje de ayuda si existe
                const helpText = deptoSelect.parentElement.querySelector('small');
                if(helpText) {
                    helpText.textContent = '⚠️ Obligatorio para maestros';
                    helpText.style.color = '#dc3545';
                }
            } else {
                deptoSelect.required = false;
                const helpText = deptoSelect.parentElement.querySelector('small');
                if(helpText) {
                    helpText.textContent = 'Opcional para otros roles';
                    helpText.style.color = '#6c757d';
                }
            }
        });
    }

    /* =========================
    GUARDAR CAMBIOS
    ========================= */
    document.getElementById("btnGuardarCambios")
    .addEventListener("click", () => {

        const id =
            document.getElementById("edit_id").value;

        const nombre =
            document.getElementById("edit_nombre").value.trim();

        const apellidos =
            document.getElementById("edit_apellidos").value.trim();

        const area =
            document.getElementById("edit_area").value.trim();

        const email =
            document.getElementById("edit_email").value.trim();

        const rol =
            document.getElementById("edit_rol").value;

        const departamento =
            document.getElementById("edit_departamento").value;

        /* =========================
        VALIDACIONES
        ========================= */

        // Validar ID
        if(!id || id === "") {
            Swal.fire(
                "Error",
                "ID de usuario no válido.",
                "error"
            );
            return;
        }

        // Validar Nombre
        if(nombre.length < 3) {
            Swal.fire(
                "Nombre inválido",
                "El nombre debe tener mínimo 3 caracteres.",
                "warning"
            );
            return;
        }

        // Validar Apellidos
        if(apellidos.length < 3) {
            Swal.fire(
                "Apellidos inválidos",
                "Los apellidos deben tener mínimo 3 caracteres.",
                "warning"
            );
            return;
        }

        // Validar Área
        if(area.length < 3) {
            Swal.fire(
                "Área inválida",
                "El área debe tener mínimo 3 caracteres.",
                "warning"
            );
            return;
        }

        // Validar Email
        const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if(!regexEmail.test(email)) {
            Swal.fire(
                "Correo inválido",
                "Ingresa un correo electrónico válido.",
                "warning"
            );
            return;
        }

        // Validar Rol
        if(rol === "") {
            Swal.fire(
                "Rol requerido",
                "Debes seleccionar un rol.",
                "warning"
            );
            return;
        }

        // Validar Departamento (si es maestro, es obligatorio)
        if(rol === "maestro" && !departamento) {
            Swal.fire(
                "Departamento requerido",
                "Los maestros deben tener un departamento asignado.",
                "warning"
            );
            return;
        }

        /* =========================
        ENVIAR
        ========================= */

        const form =
            document.getElementById("formEditar");

        const formData =
            new FormData(form);

        // Verificar que los datos se están enviando correctamente
        console.log("Enviando datos:", {
            id, nombre, apellidos, area, email, rol, departamento
        });

        Swal.fire({
            title: 'Guardando cambios...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => {
                Swal.showLoading();
            }
        });

        fetch("../../controllers/actualizar_usuario.php", {

            method: "POST",
            body: formData

        })

        .then(response => response.json())

        .then(data => {

            console.log("Respuesta:", data);

            if(data.status === "success") {

                Swal.fire({
                    icon: 'success',
                    title: 'Actualizado',
                    text: data.message || 'Usuario actualizado correctamente.',
                    timer: 2000,
                    showConfirmButton: false
                }).then(() => {

                    location.reload();

                });

            } else {

                Swal.fire(
                    "Error",
                    data.message || data.error || "Error desconocido.",
                    "error"
                );

            }

        })

        .catch(error => {

            console.error("Error en fetch:", error);

            Swal.fire(
                "Error",
                "Ocurrió un problema al conectar con el servidor.",
                "error"
            );

        });

    });

});