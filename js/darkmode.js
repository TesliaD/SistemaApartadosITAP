/* =========================================================
   ELEMENTOS
========================================================= */

const sidebar = document.getElementById("sidebar");
const toggleBtn = document.getElementById("toggleSidebar");
const overlay = document.getElementById("overlay");

const navbar = document.querySelector(".navbar");

const darkModeBtn = document.getElementById("darkModeBtn");
const darkModeIcon = darkModeBtn?.querySelector("i");


/* =========================================================
   LOCAL STORAGE
========================================================= */

const THEME_KEY = "itap-theme";
const SIDEBAR_KEY = "itap-maestro-sidebar-collapsed";


/* =========================================================
   ACTUALIZAR ICONO DEL TEMA
========================================================= */

function actualizarIconoTema(esOscuro) {

    if (!darkModeBtn || !darkModeIcon) {
        return;
    }

    darkModeIcon.className = esOscuro
        ? "bi bi-sun"
        : "bi bi-moon";

    darkModeBtn.setAttribute(
        "aria-pressed",
        String(esOscuro)
    );

    darkModeBtn.setAttribute(
        "aria-label",
        esOscuro
            ? "Cambiar a modo claro"
            : "Cambiar a modo oscuro"
    );
}


/* =========================================================
   APLICAR TEMA
========================================================= */

function aplicarTema(esOscuro, guardar = false) {

    document.body.classList.toggle(
        "dark",
        esOscuro
    );

    actualizarIconoTema(esOscuro);

    if (guardar) {

        try {

            localStorage.setItem(
                THEME_KEY,
                esOscuro ? "dark" : "light"
            );

        } catch (error) {

            console.warn(
                "No se pudo guardar el tema.",
                error
            );

        }
    }
}


/* =========================================================
   AJUSTAR SIDEBAR
========================================================= */

function ajustarSidebarColapsada(
    colapsada,
    guardar = false
) {

    /*
       En mobile no utilizamos el estado collapsed.
       Mobile utiliza show.
    */

    if (!sidebar || window.innerWidth <= 768) {
        return;
    }


    /* SIDEBAR */

    sidebar.classList.toggle(
        "collapsed",
        colapsada
    );


    /* NAVBAR */

    navbar?.classList.toggle(
        "expanded",
        colapsada
    );


    /*
       BODY

       Esta clase controla el espacio lateral
       y la posición del navbar.
    */

    document.body.classList.toggle(
        "sidebar-maestro-colapsada",
        colapsada
    );


    /* GUARDAR ESTADO */

    if (guardar) {

        try {

            localStorage.setItem(
                SIDEBAR_KEY,
                String(colapsada)
            );

        } catch (error) {

            console.warn(
                "No se pudo guardar el estado del sidebar.",
                error
            );

        }
    }
}


/* =========================================================
   RESTAURAR PREFERENCIAS
========================================================= */

try {

    const temaGuardado =
        localStorage.getItem(THEME_KEY);

    const sidebarGuardado =
        localStorage.getItem(SIDEBAR_KEY);


    /* TEMA */

    aplicarTema(
        temaGuardado === "dark"
    );


    /* SIDEBAR */

    ajustarSidebarColapsada(
        sidebarGuardado === "true"
    );

} catch (error) {

    console.warn(
        "No se pudo restaurar la preferencia de interfaz.",
        error
    );
}


/* =========================================================
   TOGGLE SIDEBAR
========================================================= */

if (toggleBtn && sidebar) {

    toggleBtn.addEventListener(
        "click",
        () => {

            /* =============================================
               MOBILE
            ============================================= */

            if (window.innerWidth <= 768) {

                const abierto =
                    sidebar.classList.contains("show");


                sidebar.classList.toggle(
                    "show",
                    !abierto
                );


                if (overlay) {

                    overlay.style.display =
                        !abierto
                            ? "block"
                            : "none";

                }


                return;
            }


            /* =============================================
               DESKTOP
            ============================================= */

            const colapsado =
                sidebar.classList.contains("collapsed");


            ajustarSidebarColapsada(
                !colapsado,
                true
            );

        }
    );
}


/* =========================================================
   CERRAR SIDEBAR MOBILE
========================================================= */

if (overlay && sidebar) {

    overlay.addEventListener(
        "click",
        () => {

            sidebar.classList.remove(
                "show"
            );

            overlay.style.display =
                "none";

        }
    );
}


/* =========================================================
   SUBMENÚS
========================================================= */

document
    .querySelectorAll(".toggle-submenu")
    .forEach(btn => {

        btn.addEventListener(
            "click",
            event => {

                event.preventDefault();


                const submenu =
                    btn.nextElementSibling;


                if (!submenu) {
                    return;
                }


                /*
                   Si el sidebar está colapsado
                   en desktop, no abrimos el submenu.
                */

                if (
                    window.innerWidth > 768 &&
                    sidebar?.classList.contains("collapsed")
                ) {

                    return;
                }


                submenu.classList.toggle(
                    "active"
                );

            }
        );

    });


/* =========================================================
   DARK MODE
========================================================= */

if (darkModeBtn) {

    darkModeBtn.addEventListener(
        "click",
        () => {

            const esOscuro =
                document.body.classList.contains("dark");


            aplicarTema(
                !esOscuro,
                true
            );

        }
    );
}


/* =========================================================
   EFECTO SCROLL NAVBAR
========================================================= */

if (navbar) {

    window.addEventListener(
        "scroll",
        () => {

            if (window.scrollY > 20) {

                navbar.classList.add(
                    "navbar-scrolled"
                );

            } else {

                navbar.classList.remove(
                    "navbar-scrolled"
                );

            }

        }
    );

}


/* =========================================================
   RESPONSIVE
========================================================= */

window.addEventListener(
    "resize",
    () => {

        /* =============================================
           DESKTOP
        ============================================= */

        if (window.innerWidth > 768) {

            /* Cerrar mobile */

            if (overlay) {

                overlay.style.display =
                    "none";

            }


            if (sidebar) {

                sidebar.classList.remove(
                    "show"
                );

            }


            /* Restaurar estado desktop */

            try {

                const colapsado =
                    localStorage.getItem(
                        SIDEBAR_KEY
                    ) === "true";


                ajustarSidebarColapsada(
                    colapsado
                );

            } catch (error) {

                console.warn(
                    "No se pudo restaurar el estado del sidebar.",
                    error
                );

            }

            return;
        }


        /* =============================================
           MOBILE
        ============================================= */

        /*
           Quitamos las clases desktop.
        */

        if (sidebar) {

            sidebar.classList.remove(
                "collapsed"
            );

        }


        if (navbar) {

            navbar.classList.remove(
                "expanded"
            );

        }


        document.body.classList.remove(
            "sidebar-maestro-colapsada"
        );


        /*
           El overlay se oculta cuando
           cambiamos a mobile.
        */

        if (overlay) {

            if (
                !sidebar?.classList.contains("show")
            ) {

                overlay.style.display =
                    "none";

            }

        }

    }
);