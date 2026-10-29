
/* =========================================================
   EVALORANET - JAVASCRIPT PRINCIPAL
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       GRÁFICA DE DESEMPEÑO DEL ESTUDIANTE
       Bajo = rojo | Básico = amarillo | Alto = verde
    ====================================================== */

    const graficaDesempeno =
        document.getElementById("graficaDesempeno");

    if (graficaDesempeno && typeof Chart !== "undefined") {

        const bajo = Number(graficaDesempeno.dataset.bajo || 0);
        const basico = Number(graficaDesempeno.dataset.basico || 0);
        const alto = Number(graficaDesempeno.dataset.alto || 0);

        // Evitar dibujar una gráfica vacía.
        if (bajo + basico + alto > 0) {

            new Chart(graficaDesempeno, {
                type: "doughnut",

                data: {
                    labels: ["Bajo", "Básico", "Alto"],

                    datasets: [{
                        data: [bajo, basico, alto],

                        backgroundColor: [
                            "#DC3545", // Bajo: rojo
                            "#FFC107", // Básico: amarillo
                            "#198754"  // Alto: verde
                        ],

                        borderColor: "#FFFFFF",
                        borderWidth: 3,
                        hoverOffset: 6
                    }]
                },

                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    cutout: "65%",

                    plugins: {
                        legend: {
                            position: "bottom",

                            labels: {
                                padding: 16,
                                usePointStyle: true,

                                font: {
                                    size: 12
                                }
                            }
                        },

                        tooltip: {
                            callbacks: {
                                label: function (context) {
                                    return " " + context.label + ": " + context.raw;
                                }
                            }
                        }
                    },

                    animation: {
                        animateRotate: true,
                        animateScale: true
                    }
                }
            });
        }
    }


    /* =====================================================
       FILTROS DE CARGA ACADÉMICA
    ====================================================== */

    const botonFiltrar =
        document.getElementById("aplicarFiltrosCarga");

    const botonLimpiar =
        document.getElementById("limpiarFiltrosCarga");

    const buscador =
        document.getElementById("buscarCarga");

    const filtroDocente =
        document.getElementById("filtroDocente");

    const filtroCurso =
        document.getElementById("filtroCurso");

    const filtroMateria =
        document.getElementById("filtroMateria");

    const contador =
        document.getElementById("contadorCarga");

    const filas =
        document.querySelectorAll(".fila-carga");


    function filtrarCargaAcademica() {

        const texto = buscador
            ? buscador.value.trim().toLowerCase()
            : "";

        const docenteSeleccionado =
            filtroDocente ? filtroDocente.value : "";

        const cursoSeleccionado =
            filtroCurso ? filtroCurso.value : "";

        const materiaSeleccionada =
            filtroMateria ? filtroMateria.value : "";

        let resultados = 0;
        let numero = 1;

        filas.forEach(function (fila) {

            const docenteId = fila.dataset.docenteId || "";
            const cursoId = fila.dataset.cursoId || "";
            const materiaId = fila.dataset.materiaId || "";

            const obtenerTexto = function (selector) {
                const elemento = fila.querySelector(selector);

                return elemento
                    ? elemento.textContent.trim().toLowerCase()
                    : "";
            };

            const docente = obtenerTexto(".dato-docente");
            const documento = obtenerTexto(".dato-documento");
            const curso = obtenerTexto(".dato-curso");
            const materia = obtenerTexto(".dato-materia");

            const coincideTexto =
                texto === "" ||
                docente.includes(texto) ||
                documento.includes(texto) ||
                curso.includes(texto) ||
                materia.includes(texto);

            const coincideDocente =
                docenteSeleccionado === "" ||
                docenteId === docenteSeleccionado;

            const coincideCurso =
                cursoSeleccionado === "" ||
                cursoId === cursoSeleccionado;

            const coincideMateria =
                materiaSeleccionada === "" ||
                materiaId === materiaSeleccionada;

            const mostrar =
                coincideTexto &&
                coincideDocente &&
                coincideCurso &&
                coincideMateria;

            fila.style.display = mostrar ? "" : "none";

            if (mostrar) {
                const numeroFila = fila.querySelector(".numero-fila");

                if (numeroFila) {
                    numeroFila.textContent = numero++;
                }

                resultados++;
            }
        });

        if (contador) {
            contador.textContent =
                resultados +
                (resultados === 1 ? " resultado" : " resultados");
        }
    }


    if (botonFiltrar) {
        botonFiltrar.addEventListener(
            "click",
            filtrarCargaAcademica
        );
    }

    if (botonLimpiar) {
        botonLimpiar.addEventListener("click", function () {

            if (buscador) buscador.value = "";
            if (filtroDocente) filtroDocente.value = "";
            if (filtroCurso) filtroCurso.value = "";
            if (filtroMateria) filtroMateria.value = "";

            filtrarCargaAcademica();
        });
    }

    if (buscador) {
        buscador.addEventListener("keydown", function (evento) {
            if (evento.key === "Enter") {
                evento.preventDefault();
                filtrarCargaAcademica();
            }
        });
    }

    [filtroDocente, filtroCurso, filtroMateria].forEach(function (filtro) {
        if (filtro) {
            filtro.addEventListener("change", filtrarCargaAcademica);
        }
    });

    if (filas.length > 0) {
        filtrarCargaAcademica();
    }


    /* =====================================================
       SEMÁFORO DE DESEMPEÑO
    ====================================================== */

    const botonesDesempeno =
        document.querySelectorAll(".btn-desempeno");

    botonesDesempeno.forEach(function (boton) {

        boton.addEventListener("click", function () {

            const fila = boton.closest("tr");

            if (!fila) return;

            fila.querySelectorAll(".btn-desempeno").forEach(function (btn) {
                btn.classList.remove("seleccionado");
            });

            boton.classList.add("seleccionado");

            const formulario = boton.closest("form");

            if (!formulario) return;

            const estudianteId = boton.dataset.estudiante;
            const colorId = boton.dataset.color;

            let inputEstudiante =
                formulario.querySelector('input[name="estudiante_id"]');

            if (!inputEstudiante) {
                inputEstudiante = document.createElement("input");
                inputEstudiante.type = "hidden";
                inputEstudiante.name = "estudiante_id";
                formulario.appendChild(inputEstudiante);
            }

            let inputColor =
                formulario.querySelector('input[name="color_id"]');

            if (!inputColor) {
                inputColor = document.createElement("input");
                inputColor.type = "hidden";
                inputColor.name = "color_id";
                formulario.appendChild(inputColor);
            }

            inputEstudiante.value = estudianteId || "";
            inputColor.value = colorId || "";
        });
    });


    /* =====================================================
       MENÚ LATERAL RESPONSIVE
    ====================================================== */

    const menu = document.getElementById("evaloraSidebar");
    const toggle = document.getElementById("evaloraMenuToggle");
    const cerrar = document.getElementById("evaloraMenuClose");
    const overlay = document.getElementById("evaloraSidebarOverlay");

    if (menu && toggle && cerrar && overlay) {

        function abrirMenu() {
            menu.classList.add("is-open");
            overlay.classList.add("is-visible");
            document.body.classList.add("evalora-menu-open");

            toggle.setAttribute("aria-expanded", "true");
            toggle.setAttribute(
                "aria-label",
                "Cerrar menú de navegación"
            );
        }

        function cerrarMenu() {
            menu.classList.remove("is-open");
            overlay.classList.remove("is-visible");
            document.body.classList.remove("evalora-menu-open");

            toggle.setAttribute("aria-expanded", "false");
            toggle.setAttribute(
                "aria-label",
                "Abrir menú de navegación"
            );
        }

        toggle.addEventListener("click", function () {
            if (menu.classList.contains("is-open")) {
                cerrarMenu();
            } else {
                abrirMenu();
            }
        });

        cerrar.addEventListener("click", cerrarMenu);
        overlay.addEventListener("click", cerrarMenu);

        document.addEventListener("keydown", function (evento) {
            if (evento.key === "Escape") {
                cerrarMenu();
            }
        });

        menu.querySelectorAll("a").forEach(function (enlace) {
            enlace.addEventListener("click", cerrarMenu);
        });

        window.addEventListener("resize", function () {
            if (window.innerWidth > 768) {
                cerrarMenu();
            }
        });
    }


    /* =====================================================
       CONFIRMACIÓN PARA CERRAR SESIÓN
    ====================================================== */

    document.querySelectorAll('a[href*="logout.php"]').forEach(
        function (enlace) {

            enlace.addEventListener("click", function (evento) {

                const confirmar = window.confirm(
                    "¿Estás seguro de que deseas cerrar sesión?"
                );

                if (!confirmar) {
                    evento.preventDefault();
                }
            });
        }
    );

});

