document.addEventListener("DOMContentLoaded", () => {

    const form =
        document.getElementById("formNoticias");

    form.addEventListener("submit", function(e){

        e.preventDefault();

        // ==========================
        // CAMPOS
        // ==========================

        const titulo =
            form.Titulo.value.trim();

        const nombre =
            form.Nombre.value.trim();

        const cuerpo =
            form.Cuerpo.value.trim();

        const imagen =
            form.Imagen.files[0];

        // ==========================
        // VALIDACIONES
        // ==========================

        // TITULO

        if(titulo.length < 5){

            Swal.fire({
                icon: "warning",
                title: "Título muy corto",
                text: "El título debe tener al menos 5 caracteres"
            });

            return;
        }

        if(titulo.length > 120){

            Swal.fire({
                icon: "warning",
                title: "Título demasiado largo",
                text: "Máximo 120 caracteres"
            });

            return;
        }

        // AUTOR

        if(nombre.length < 3){

            Swal.fire({
                icon: "warning",
                title: "Nombre inválido",
                text: "El nombre debe tener al menos 3 caracteres"
            });

            return;
        }

        // CONTENIDO

        if(cuerpo.length < 20){

            Swal.fire({
                icon: "warning",
                title: "Contenido insuficiente",
                text: "La noticia debe tener mínimo 20 caracteres"
            });

            return;
        }

        // ==========================
        // VALIDAR IMAGEN
        // ==========================

        if(imagen){

            const tiposPermitidos = [
                "image/jpeg",
                "image/png",
                "image/webp"
            ];

            // TIPO

            if(!tiposPermitidos.includes(imagen.type)){

                Swal.fire({
                    icon: "error",
                    title: "Imagen inválida",
                    text: "Solo JPG, PNG o WEBP"
                });

                return;
            }

            // TAMAÑO

            const maxSize =
                5 * 1024 * 1024;

            if(imagen.size > maxSize){

                Swal.fire({
                    icon: "error",
                    title: "Imagen demasiado pesada",
                    text: "Máximo 5MB"
                });

                return;
            }

        }

        // ==========================
        // ENVIAR
        // ==========================

        const formData =
            new FormData(form);

        fetch(
            "/SistemaApartadosITAP/controllers/guardar_noticia.php",
            {
                method: "POST",
                body: formData
            }
        )

        .then(res => res.json())

        .then(data => {

            if(data.status === "success"){

                Swal.fire({
                    icon: "success",
                    title: "Éxito",
                    text: data.mensaje,
                    timer: 2000,
                    showConfirmButton: false
                });

                form.reset();

                document
                    .getElementById("preview")
                    .classList.add("d-none");

            } else {

                Swal.fire({
                    icon: "error",
                    title: "Error",
                    text: data.mensaje
                });

            }

        })

        .catch(error => {

            console.error(error);

            Swal.fire({
                icon: "error",
                title: "Error del servidor",
                text: "Ocurrió un problema inesperado"
            });

        });

    });

});