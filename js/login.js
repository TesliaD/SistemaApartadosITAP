const togglePassword = document.getElementById("togglePassword");
const passwordInput = document.getElementById("password");

if (togglePassword && passwordInput) {

    togglePassword.addEventListener("click", () => {

        const esVisible = passwordInput.type === "text";

        passwordInput.type = esVisible ? "password" : "text";

        const icon = togglePassword.querySelector("i");
        icon.className = esVisible ? "bi bi-eye" : "bi bi-eye-slash";

        togglePassword.setAttribute(
            "aria-label",
            esVisible ? "Mostrar contraseña" : "Ocultar contraseña"
        );

    });
}
