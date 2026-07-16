document.addEventListener("DOMContentLoaded", function() {

    // =========================
    // GUARDAR DEPARTAMENTO
    // =========================
    window.guardarDepartamento = function() {
        const nombre = document.getElementById("nombreDepto").value.trim();
        
        if(!nombre) {
            Swal.fire("Error", "Escribe el nombre del departamento", "warning");
            return;
        }

        if(nombre.length < 3) {
            Swal.fire("Error", "El nombre debe tener mínimo 3 caracteres", "warning");
            return;
        }

        Swal.fire({
            title: 'Guardando...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("../../controllers/guardar_departamento.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ nombre: nombre })
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === "success") {
                Swal.fire("Éxito", data.message, "success").then(() => location.reload());
            } else {
                Swal.fire("Error", data.message, "error");
            }
        })
        .catch(err => {
            Swal.fire("Error", "Error en el servidor", "error");
        });
    };

    // =========================
    // EDITAR - ABRIR MODAL
    // =========================
    document.querySelectorAll(".btnEditarDepto").forEach(btn => {
        btn.addEventListener("click", function() {
            document.getElementById("edit_depto_id").value = this.dataset.id;
            document.getElementById("edit_depto_nombre").value = this.dataset.nombre;
            document.getElementById("edit_depto_activo").value = this.dataset.activo;
            new bootstrap.Modal(document.getElementById("modalEditarDepto")).show();
        });
    });

    // =========================
    // GUARDAR CAMBIOS
    // =========================
    document.getElementById("btnGuardarDepto").addEventListener("click", function() {
        const id = document.getElementById("edit_depto_id").value;
        const nombre = document.getElementById("edit_depto_nombre").value.trim();
        const activo = document.getElementById("edit_depto_activo").value;

        if(!nombre || nombre.length < 3) {
            Swal.fire("Error", "El nombre debe tener mínimo 3 caracteres", "warning");
            return;
        }

        Swal.fire({
            title: 'Actualizando...',
            text: 'Por favor espera',
            allowOutsideClick: false,
            didOpen: () => Swal.showLoading()
        });

        fetch("../../controllers/actualizar_departamento.php", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({ id: id, nombre: nombre, activo: activo })
        })
        .then(response => response.json())
        .then(data => {
            if(data.status === "success") {
                Swal.fire("Éxito", data.message, "success").then(() => location.reload());
            } else {
                Swal.fire("Error", data.message, "error");
            }
        })
        .catch(err => {
            Swal.fire("Error", "Error en el servidor", "error");
        });
    });

    // =========================
    // ELIMINAR DEPARTAMENTO
    // =========================
    document.querySelectorAll(".btnEliminarDepto").forEach(btn => {
        btn.addEventListener("click", function() {
            const id = this.dataset.id;
            const nombre = this.dataset.nombre;

            Swal.fire({
                title: "¿Eliminar departamento?",
                html: `Estás a punto de eliminar <strong>${nombre}</strong>.`,
                text: "Esta acción no se puede deshacer",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#d33",
                confirmButtonText: "Sí, eliminar",
                cancelButtonText: "Cancelar"
            }).then(result => {
                if(result.isConfirmed) {
                    fetch("../../controllers/eliminar_departamento.php", {
                        method: "POST",
                        headers: { "Content-Type": "application/json" },
                        body: JSON.stringify({ id: id })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if(data.status === "success") {
                            Swal.fire("Eliminado", data.message, "success").then(() => location.reload());
                        } else {
                            Swal.fire("Error", data.message, "error");
                        }
                    });
                }
            });
        });
    });

});