const form = document.querySelector("form");

form.addEventListener("submit", function(e) {

    e.preventDefault();

    // =========================
    // OBTENER DATOS
    // =========================

    const num_control = form.num_control.value.trim();
    const nombre = form.nombre.value.trim();
    const apellidos = form.apellidos.value.trim();
    const area = form.area.value.trim();
    const email = form.email.value.trim();
    const password = form.password.value.trim();
    const rol = form.rol.value;

    // =========================
    // EXPRESIONES REGULARES
    // =========================

    const regexNombre = /^[a-zA-ZáéíóúÁÉÍÓÚñÑ\s]+$/;
    const regexNumeros = /^[0-9]+$/;
    const regexEmail = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    // =========================
    // VALIDACIONES
    // =========================

    if(!regexNumeros.test(num_control)) {

        Swal.fire({
            icon: 'warning',
            title: 'Número inválido',
            text: 'El número de control solo debe contener números'
        });

        return;
    }

    if(!regexNombre.test(nombre)) {

        Swal.fire({
            icon: 'warning',
            title: 'Nombre inválido',
            text: 'El nombre solo debe contener letras'
        });

        return;
    }

    if(!regexNombre.test(apellidos)) {

        Swal.fire({
            icon: 'warning',
            title: 'Apellidos inválidos',
            text: 'Los apellidos solo deben contener letras'
        });

        return;
    }

    if(area.length < 3) {

        Swal.fire({
            icon: 'warning',
            title: 'Área inválida',
            text: 'El área debe tener mínimo 3 caracteres'
        });

        return;
    }

    if(!regexEmail.test(email)) {

        Swal.fire({
            icon: 'warning',
            title: 'Correo inválido',
            text: 'Ingresa un correo válido'
        });

        return;
    }

    if(password.length < 8) {

        Swal.fire({
            icon: 'warning',
            title: 'Contraseña insegura',
            text: 'La contraseña debe tener mínimo 8 caracteres'
        });

        return;
    }

    if(rol === "") {

        Swal.fire({
            icon: 'warning',
            title: 'Rol requerido',
            text: 'Selecciona un rol'
        });

        return;
    }

    // =========================
    // FORM DATA
    // =========================

    const formData = new FormData(form);

    // =========================
    // LOADING
    // =========================

    Swal.fire({
        title: 'Guardando usuario...',
        text: 'Por favor espera',
        allowOutsideClick: false,
        didOpen: () => {
            Swal.showLoading();
        }
    });

    // =========================
    // FETCH
    // =========================
    fetch("../../controllers/guardar_usuario.php", {

        method: "POST",
        body: formData

    })

    .then(response => response.json())

    .then(data => {

        console.log(data);

        if(data.status === "success") {

            Swal.fire({
                icon: 'success',
                title: 'Usuario guardado',
                text: data.message
            });

            form.reset();

        } else {

            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: data.message
            });
        }

    })

    .catch(error => {

        console.error(error);

        Swal.fire({
            icon: 'error',
            title: 'Error',
            text: 'Error en el servidor'
        });

    });
    });
