<?php

session_start();

require_once __DIR__ . '/../../../config/config.php';
require_once __DIR__ . '/../../../config/seguridad/coordinador.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Plantilla de importación - EvaloraNet</title>


    <!-- Bootstrap 5 -->

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >


    <!-- CSS propio de EvaloraNet -->

    <link
        rel="stylesheet"
        href="/Evaloranet/assets/css/style.css"
    >

</head>


<body>


<div class="container mt-5">


    <!-- =====================================================
         TARJETA PRINCIPAL
    ====================================================== -->

    <div class="card shadow border-0">


        <!-- =================================================
             ENCABEZADO
        ================================================== -->

        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">

                📥 Plantilla de importación

            </h4>

        </div>


        <!-- =================================================
             CONTENIDO
        ================================================== -->

        <div class="card-body p-4">


            <div class="row align-items-center">


                <!-- =================================================
                     INFORMACIÓN
                ================================================== -->

                <div class="col-md-8">


                    <h4 class="fw-bold mb-3">

                        Descargar plantilla Excel

                    </h4>


                    <p class="text-muted">

                        Utiliza esta plantilla para registrar
                        la información de estudiantes y docentes
                        antes de realizar la importación.

                    </p>


                    <!-- =============================================
                         CONTENIDO DE LA PLANTILLA
                    ============================================== -->

                    <div class="alert alert-info mt-4">


                        <h5 class="fw-bold">

                            📋 La plantilla contiene:

                        </h5>


                        <ul class="mb-0 mt-3">

                            <li class="mb-2">

                                Una hoja llamada
                                <strong>Estudiantes</strong>.

                            </li>


                            <li class="mb-2">

                                Una hoja llamada
                                <strong>Docentes</strong>.

                            </li>


                            <li class="mb-2">

                                Las columnas necesarias para
                                realizar la importación.

                            </li>


                            <li class="mb-2">

                                Ejemplos para facilitar el
                                diligenciamiento.

                            </li>


                            <li>

                                Los cursos deben utilizar el
                                formato institucional, por ejemplo:

                                <strong>601, 602, 701, 801, 901,
                                1001, 1101</strong>.

                            </li>

                        </ul>

                    </div>


                    <!-- =============================================
                         RECOMENDACIÓN
                    ============================================== -->

                    <div class="alert alert-warning mt-3">

                        <strong>⚠️ Importante</strong>

                        <p class="mb-0 mt-2">

                            No cambies los nombres de las columnas
                            de la plantilla, ya que son utilizados
                            por el sistema para realizar la
                            importación correctamente.

                        </p>

                    </div>


                </div>


                <!-- =================================================
                     BOTÓN DE DESCARGA
                ================================================== -->

                <div class="col-md-4 text-center mt-4 mt-md-0">


                    <div class="p-4">


                        <div
                            style="
                                font-size: 70px;
                                margin-bottom: 20px;
                            "
                        >

                            📊

                        </div>


                        <h5 class="fw-bold mb-3">

                            Plantilla Excel

                        </h5>


                        <p class="text-muted">

                            Descarga el archivo y completa
                            la información.

                        </p>


                        <!-- =========================================
                             ESTE BOTÓN GENERA EL EXCEL
                        ========================================== -->

                        <a
                            href="generar_plantilla.php"
                            class="btn btn-primary btn-lg w-100"
                        >

                            📥 Descargar plantilla Excel

                        </a>


                    </div>

                </div>


            </div>


            <!-- =================================================
                 BOTONES INFERIORES
            ================================================== -->

            <hr class="my-4">


            <div class="d-flex justify-content-between flex-wrap gap-2">


                <a
                    href="../index.php"
                    class="btn btn-secondary"
                >

                    ← Volver a importar

                </a>


                <a
                    href="../index.php"
                    class="btn btn-outline-primary"
                >

                    📤 Ir a importar archivo

                </a>


            </div>


        </div>

    </div>

</div>


</body>

</html>