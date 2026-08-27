/* =========================================================
   EVALORANET - JAVASCRIPT PRINCIPAL
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    /* =====================================================
       GRÁFICA DE DESEMPEÑO DEL ESTUDIANTE
    ====================================================== */

    const graficaDesempeno =
        document.getElementById("graficaDesempeno");

    if (
        graficaDesempeno &&
        typeof Chart !== "undefined"
    ) {

        const bajo =
            Number(graficaDesempeno.dataset.bajo || 0);

        const basico =
            Number(graficaDesempeno.dataset.basico || 0);

        const alto =
            Number(graficaDesempeno.dataset.alto || 0);

        new Chart(graficaDesempeno, {

            type: "doughnut",

            data: {

                labels: [
                    "Bajo",
                    "Básico",
                    "Alto"
                ],

                datasets: [{
                    data: [
                        bajo,
                        basico,
                        alto
                    ],

                    backgroundColor: [
                        "#dc3545",
                        "#ffc107",
                        "#198754"
                    ],

                    borderColor: [
                        "#ffffff",
                        "#ffffff",
                        "#ffffff"
                    ],

                    borderWidth: 3
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

                            padding: 20,

                            usePointStyle: true,

                            font: {
                                size: 14
                            }
                        }
                    },

                    tooltip: {

                        callbacks: {

                            label: function (context) {

                                const valor =
                                    context.raw;

                                return (
                                    " " +
                                    context.label +
                                    ": " +
                                    valor
                                );
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


    /* =====================================================
       FILTROS DE CARGA ACADÉMICA
    ====================================================== */

    const botonFiltrar =
        document.getElementById(
            "aplicarFiltrosCarga"
        );

    const botonLimpiar =
        document.getElementById(
            "limpiarFiltrosCarga"
        );

    const buscador =
        document.getElementById(
            "buscarCarga"
        );

    const filtroDocente =
        document.getElementById(
            "filtroDocente"
        );

    const filtroCurso =
        document.getElementById(
            "filtroCurso"
        );

    const filtroMateria =
        document.getElementById(
            "filtroMateria"
        );

    const contador =
        document.getElementById(
            "contadorCarga"
        );

    const filas =
        document.querySelectorAll(
            ".fila-carga"
        );


    /* =====================================================
       FUNCIÓN FILTRAR CARGA ACADÉMICA
    ====================================================== */

    function filtrarCargaAcademica() {

        const texto =
            buscador
                ? buscador.value
                    .trim()
                    .toLowerCase()
                : "";

        const docenteSeleccionado =
            filtroDocente
                ? filtroDocente.value
                : "";

        const cursoSeleccionado =
            filtroCurso
                ? filtroCurso.value
                : "";

        const materiaSeleccionada =
            filtroMateria
                ? filtroMateria.value
                : "";

        let resultados = 0;


        filas.forEach(function (fila) {

            const docenteId =
                fila.dataset.docenteId || "";

            const cursoId =
                fila.dataset.cursoId || "";

            const materiaId =
                fila.dataset.materiaId || "";


            const docente =
                fila.querySelector(
                    ".dato-docente"
                )?.textContent
                    .trim()
                    .toLowerCase() || "";


            const documento =
                fila.querySelector(
                    ".dato-documento"
                )?.textContent
                    .trim()
                    .toLowerCase() || "";


            const curso =
                fila.querySelector(
                    ".dato-curso"
                )?.textContent
                    .trim()
                    .toLowerCase() || "";


            const materia =
                fila.querySelector(
                    ".dato-materia"
                )?.textContent
                    .trim()
                    .toLowerCase() || "";


            /* BUSCADOR GENERAL */

            const coincideTexto =
                texto === "" ||
                docente.includes(texto) ||
                documento.includes(texto) ||
                curso.includes(texto) ||
                materia.includes(texto);


            /* FILTRO DOCENTE */

            const coincideDocente =
                docenteSeleccionado === "" ||
                docenteId === docenteSeleccionado;


            /* FILTRO CURSO */

            const coincideCurso =
                cursoSeleccionado === "" ||
                cursoId === cursoSeleccionado;


            /* FILTRO MATERIA */

            const coincideMateria =
                materiaSeleccionada === "" ||
                materiaId === materiaSeleccionada;


            /* RESULTADO */

            const mostrar =
                coincideTexto &&
                coincideDocente &&
                coincideCurso &&
                coincideMateria;


            if (mostrar) {

                fila.style.display = "";

                resultados++;

            } else {

                fila.style.display = "none";

            }
        });


        /* =================================================
           RENUMERAR FILAS
        ================================================== */

        let numero = 1;

        filas.forEach(function (fila) {

            if (fila.style.display !== "none") {

                const numeroFila =
                    fila.querySelector(
                        ".numero-fila"
                    );

                if (numeroFila) {

                    numeroFila.textContent =
                        numero++;

                }
            }
        });


        /* =================================================
           CONTADOR
        ================================================== */

        if (contador) {

            contador.textContent =
                resultados +
                (
                    resultados === 1
                        ? " resultado"
                        : " resultados"
                );
        }
    }


    /* =====================================================
       BOTÓN BUSCAR / FILTRAR
    ====================================================== */

    if (botonFiltrar) {

        botonFiltrar.addEventListener(
            "click",
            function () {

                filtrarCargaAcademica();

            }
        );
    }


    /* =====================================================
       BOTÓN LIMPIAR
    ====================================================== */

    if (botonLimpiar) {

        botonLimpiar.addEventListener(
            "click",
            function () {

                if (buscador) {
                    buscador.value = "";
                }

                if (filtroDocente) {
                    filtroDocente.value = "";
                }

                if (filtroCurso) {
                    filtroCurso.value = "";
                }

                if (filtroMateria) {
                    filtroMateria.value = "";
                }

                filtrarCargaAcademica();

            }
        );
    }


    /* =====================================================
       BUSCAR CON ENTER
    ====================================================== */

    if (buscador) {

        buscador.addEventListener(
            "keydown",
            function (evento) {

                if (evento.key === "Enter") {

                    evento.preventDefault();

                    filtrarCargaAcademica();

                }
            }
        );
    }


    /* =====================================================
       FILTRAR AUTOMÁTICAMENTE AL CAMBIAR SELECT
    ====================================================== */

    if (filtroDocente) {

        filtroDocente.addEventListener(
            "change",
            function () {

                filtrarCargaAcademica();

            }
        );
    }


    if (filtroCurso) {

        filtroCurso.addEventListener(
            "change",
            function () {

                filtrarCargaAcademica();

            }
        );
    }


    if (filtroMateria) {

        filtroMateria.addEventListener(
            "change",
            function () {

                filtrarCargaAcademica();

            }
        );
    }


    /* =====================================================
       CONTADOR INICIAL
    ====================================================== */

    if (filas.length > 0) {

        filtrarCargaAcademica();

    }


    /* =====================================================
       ANIMACIÓN SUAVE DE LAS TARJETAS
    ====================================================== */

    const tarjetas =
        document.querySelectorAll(".card");

    tarjetas.forEach(function (tarjeta) {

        tarjeta.addEventListener(
            "mouseenter",
            function () {

                tarjeta.style.transform =
                    "translateY(-3px)";

                tarjeta.style.transition =
                    "transform 0.2s ease";

            }
        );

        tarjeta.addEventListener(
            "mouseleave",
            function () {

                tarjeta.style.transform =
                    "translateY(0)";

            }
        );

    });


    /* =====================================================
       CONFIRMACIÓN PARA CERRAR SESIÓN
    ====================================================== */

    const enlacesLogout =
        document.querySelectorAll(
            'a[href*="logout.php"]'
        );

    enlacesLogout.forEach(function (enlace) {

        enlace.addEventListener(
            "click",
            function (evento) {

                const confirmar =
                    confirm(
                        "¿Estás seguro de que deseas cerrar sesión?"
                    );

                if (!confirmar) {

                    evento.preventDefault();

                }
            }
        );

    });

});