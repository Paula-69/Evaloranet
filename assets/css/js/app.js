/* =========================================================
   EVALORANET - JAVASCRIPT PRINCIPAL
========================================================= */

document.addEventListener("DOMContentLoaded", function () {


    /* =====================================================
       GRÁFICA DE DESEMPEÑO DEL ESTUDIANTE
    ====================================================== */

    const graficaDesempeno =
        document.getElementById("graficaDesempeno");


    /*
    |--------------------------------------------------------------------------
    | COMPROBAR QUE EXISTE LA GRÁFICA
    |--------------------------------------------------------------------------
    */

    if (graficaDesempeno && typeof Chart !== "undefined") {


        /*
        |--------------------------------------------------------------------------
        | OBTENER DATOS DESDE PHP
        |--------------------------------------------------------------------------
        */

        const bajo =
            Number(
                graficaDesempeno.dataset.bajo || 0
            );


        const basico =
            Number(
                graficaDesempeno.dataset.basico || 0
            );


        const alto =
            Number(
                graficaDesempeno.dataset.alto || 0
            );


        /*
        |--------------------------------------------------------------------------
        | CREAR GRÁFICA
        |--------------------------------------------------------------------------
        */

        new Chart(
            graficaDesempeno,
            {

                type: "doughnut",

                data: {

                    labels: [
                        "Bajo",
                        "Básico",
                        "Alto"
                    ],

                    datasets: [

                        {

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

                        }

                    ]

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

            }
        );

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