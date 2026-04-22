const sidebar = document.getElementById("sidebar");
const content = document.getElementById("content");
const toggleBtn = document.getElementById("toggleSidebar");
const overlay = document.getElementById("overlay");

/* TOGGLE SIDEBAR */
toggleBtn.addEventListener("click", () => {
    if (window.innerWidth <= 768) {
        sidebar.classList.toggle("show");
        overlay.style.display = sidebar.classList.contains("show") ? "block" : "none";
    } else {
        sidebar.classList.toggle("collapsed");
        content.classList.toggle("expanded");
    }
});

/* CERRAR EN MOBILE */
overlay.addEventListener("click", () => {
    sidebar.classList.remove("show");
    overlay.style.display = "none";
});

/* SUBMENÚ */
document.querySelectorAll(".toggle-submenu").forEach(btn => {
    btn.addEventListener("click", () => {
        const submenu = btn.nextElementSibling;
        submenu.style.display = submenu.style.display === "block" ? "none" : "block";
    });
});

/* DARK MODE */
document.getElementById("darkModeBtn").addEventListener("click", () => {
    document.body.classList.toggle("dark");
});