const sidebar = document.getElementById("sidebar");
const content = document.getElementById("content");
const toggleBtn = document.getElementById("toggleSidebar");
const overlay = document.getElementById("overlay");
const navbar = document.querySelector(".navbar");
const darkModeBtn = document.getElementById("darkModeBtn");

/* =========================
TOGGLE SIDEBAR
========================= */
if (toggleBtn && sidebar) {

    toggleBtn.addEventListener("click", () => {

        // MOBILE
        if (window.innerWidth <= 768) {

            sidebar.classList.toggle("show");

            if (overlay) {

                overlay.style.display =
                    sidebar.classList.contains("show")
                        ? "block"
                        : "none";
            }

        }
        // DESKTOP
        else {

            sidebar.classList.toggle("collapsed");

            // AJUSTAR CONTENIDO
            if (content) {
                content.classList.toggle("expanded");
            }

            // AJUSTAR NAVBAR
            if (navbar) {
                navbar.classList.toggle("expanded");
            }

        }

    });

}

/* =========================
CERRAR SIDEBAR MOBILE
========================= */
if (overlay && sidebar) {

    overlay.addEventListener("click", () => {

        sidebar.classList.remove("show");
        overlay.style.display = "none";

    });

}

/* =========================
SUBMENÚS
========================= */
document.querySelectorAll(".toggle-submenu").forEach(btn => {

    btn.addEventListener("click", (e) => {

        e.preventDefault();

        const submenu = btn.nextElementSibling;

        if (submenu) {
            submenu.classList.toggle("active");
        }

    });

});

/* =========================
DARK MODE
========================= */
if (darkModeBtn) {

    darkModeBtn.addEventListener("click", () => {

        document.body.classList.toggle("dark");

    });

}

/* =========================
EFECTO SCROLL NAVBAR
========================= */
if (navbar) {

    window.addEventListener("scroll", () => {

        if (window.scrollY > 20) {

            navbar.classList.add("navbar-scrolled");

        } else {

            navbar.classList.remove("navbar-scrolled");

        }

    });

}

/* =========================
RESETEAR MOBILE/DESKTOP
========================= */
window.addEventListener("resize", () => {

    if (window.innerWidth > 768) {

        if (overlay) {
            overlay.style.display = "none";
        }

        if (sidebar) {
            sidebar.classList.remove("show");
        }

    }

});