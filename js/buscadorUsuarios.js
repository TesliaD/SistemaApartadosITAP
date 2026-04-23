document.addEventListener("DOMContentLoaded", function () {

    const input = document.getElementById("buscador");

    if (!input) return; // evita errores si no existe

    input.addEventListener("keyup", function () {
        let filtro = input.value.toLowerCase();
        let filas = document.querySelectorAll("#usuarios tbody tr");

        filas.forEach(fila => {
            let texto = fila.textContent.toLowerCase();
            fila.style.display = texto.includes(filtro) ? "" : "none";
        });
    });

});