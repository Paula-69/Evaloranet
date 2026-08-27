<?php

session_start();

require_once __DIR__ . '/../../config/config.php';
require_once __DIR__ . '/../../config/seguridad/coordinador.php';

?>

<!DOCTYPE html>
<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Importar Excel - EvaloraNet</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <link
        rel="stylesheet"
        href="/Evaloranet/assets/css/style.css"
    >

</head>

<body>

<div class="container mt-5">

    <div class="card shadow border-0">

        <div class="card-header bg-primary text-white">

            <h4 class="mb-0">
                Importar estudiantes y docentes
            </h4>

        </div>


        <div class="card-body">


            <!-- =====================================================
                 MENSAJES
            ====================================================== -->

            <?php if (isset($_GET['error'])): ?>

                <div class="alert alert-danger">

                    <strong>Error:</strong>

                    <br>

                    <?= htmlspecialchars($_GET['error']) ?>

                </div>

            <?php endif; ?>


            <?php if (isset($_GET['success'])): ?>

                <div class="alert alert-success">

                    <strong>Importación completada:</strong>

                    <br>

                    <?= htmlspecialchars($_GET['success']) ?>

                </div>

            <?php endif; ?>


            <!-- =====================================================
                 INFORMACIÓN
            ====================================================== -->

            <div class="alert alert-info">

                <h5 class="fw-bold">
                    📋 Formato del archivo Excel
                </h5>

                <p class="mb-2">

                    El archivo debe contener las siguientes hojas:

                </p>

                <ul class="mb-0">

                    <li>
                        <strong>Estudiantes</strong>
                    </li>

                    <li>
                        <strong>Docentes</strong>
                    </li>

                </ul>

            </div>


            <!-- =====================================================
                 DESCARGAR PLANTILLA
            ====================================================== -->

            <div class="card border-primary mb-4">

                <div class="card-body">

                    <div class="row align-items-center">

                        <div class="col-md-8">

                            <h5 class="fw-bold mb-2">

                                📥 Descargar plantilla Excel

                            </h5>

                            <p class="text-muted mb-0">

                                Descarga la plantilla oficial de
                                EvaloraNet, completa los datos y
                                posteriormente vuelve a subir el archivo.

                            </p>

                        </div>


                        <div class="col-md-4 text-md-end mt-3 mt-md-0">

                            <a
                                href="plantilla/plantilla_importacion.php"
                                class="btn btn-primary"
                            >

                                📥 Descargar plantilla

                            </a>

                        </div>

                    </div>

                </div>

            </div>


            <!-- =====================================================
                 INFORMACIÓN SOBRE CURSOS
            ====================================================== -->

            <div class="alert alert-warning">

                <strong>⚠️ Importante sobre los cursos</strong>

                <p class="mb-0 mt-2">

                    Los cursos deben escribirse utilizando el formato
                    utilizado por la institución.

                    <br><br>

                    Ejemplos:

                    <strong>601</strong>,
                    <strong>602</strong>,
                    <strong>603</strong>,
                    <strong>701</strong>,
                    <strong>702</strong>,
                    <strong>801</strong>,
                    <strong>901</strong>,
                    <strong>1001</strong>,
                    <strong>1101</strong>.

                </p>

            </div>


            <!-- =====================================================
                 FORMULARIO DE IMPORTACIÓN
            ====================================================== -->

            <div class="card border-0 shadow-sm">

                <div class="card-body">

                    <h5 class="fw-bold mb-3">

                        📤 Importar archivo Excel

                    </h5>


                    <form
                        action="procesar.php"
                        method="POST"
                        enctype="multipart/form-data"
                    >

                        <div class="mb-3">

                            <label
                                for="archivo_excel"
                                class="form-label"
                            >

                                Seleccione el archivo Excel

                            </label>


                            <input
                                type="file"
                                id="archivo_excel"
                                name="archivo_excel"
                                class="form-control"
                                accept=".xlsx,.xls"
                                required
                            >

                            <div class="form-text">

                                Formatos permitidos:
                                .xlsx y .xls

                            </div>

                        </div>


                        <!-- =================================================
                             CONTRASEÑA INICIAL
                        ================================================== -->

                        <div class="alert alert-warning mb-4">

                            <strong>🔐 Contraseña inicial</strong>

                            <p class="mb-0 mt-2">

                                La contraseña inicial de estudiantes y
                                docentes será su número de documento.

                                <br><br>

                                Los docentes deberán cambiar
                                obligatoriamente su contraseña en el
                                primer ingreso.

                            </p>

                        </div>


                        <!-- =================================================
                             BOTONES
                        ================================================== -->

                        <div class="d-flex gap-2 flex-wrap">

                            <button
                                type="submit"
                                class="btn btn-success"
                            >

                                📤 Importar archivo

                            </button>


                            <a
                                href="../index.php"
                                class="btn btn-secondary"
                            >

                                ← Volver al panel

                            </a>

                        </div>

                    </form>

                </div>

            </div>


            <!-- =====================================================
                 AYUDA
            ====================================================== -->

            <div class="mt-4">

                <div class="alert alert-light border">

                    <h6 class="fw-bold">

                        💡 Recomendación

                    </h6>

                    <p class="mb-0 text-muted">

                        Utiliza siempre la plantilla descargable de
                        EvaloraNet para evitar errores en los nombres
                        de las columnas o en el formato de los datos.

                    </p>

                </div>

            </div>


        </div>

    </div>

</div>


</body>

</html>