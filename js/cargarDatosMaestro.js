document.addEventListener("DOMContentLoaded", () => {
    fetch("../../controllers/obtener_datos_maestro.php")
        .then(res => res.json())
        .then(data => {
            if(data.error){
                Swal.fire("Error", data.error, "error");
                return;
            }

            // Llenar datos del docente
            document.getElementById("docente").value = data.docente.IDUsuarios;
            document.getElementById("nombreDocente").value = data.docente.Nombre;

            const grupoSelect = document.getElementById("grupo");
            grupoSelect.innerHTML = '<option value="">Seleccionar grupo</option>';

            data.grupos.forEach(g => {
                grupoSelect.innerHTML += `
                    <option value="${g.IDGrupo}" data-alumnos="${g.cantidadAlumnos}">
                        ${g.Carrera} - Semestre ${g.Semestre} - ${g.tipoGrupo || ''}
                    </option>
                `;
            });
        })
        .catch(err => {
            Swal.fire("Error", "No se pudieron cargar los grupos", "error");
        });
});

document.addEventListener("change", function(e){
    if(e.target.id === "grupo"){
        let opcion = e.target.options[e.target.selectedIndex];
        let alumnos = opcion.dataset.alumnos || "";
        document.getElementById("alumnos").value = alumnos;
    }
});