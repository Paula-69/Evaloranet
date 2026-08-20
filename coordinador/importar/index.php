<?php

/*
|--------------------------------------------------------------------------
| SEGURIDAD
|--------------------------------------------------------------------------
*/

require_once(__DIR__ . "/../config/seguridad/coordinador.php");


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include(__DIR__ . "/../includes/header.php");


/*
|--------------------------------------------------------------------------
| BARRA SUPERIOR
|--------------------------------------------------------------------------
*/

include(__DIR__ . "/../includes/navbar.php");

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             MENU LATERAL
        ====================================================== -->

        <div class="col-md-2 p-0">

            <?php

            include(__DIR__ . "/../includes/sidebar.php");

            ?>

        </div>


        <!-- =====================================================
             CONTENIDO
        ====================================================== -->

        <div class="col-md-10">

            <div class="container-fluid mt-4">


                <!-- TITULO -->

                <h1 class="mb-2">
                    Panel del Coordinador
                </h1>


                <!-- BIENVENIDA -->

                <h4 class="mb-4">

                    Bienvenido

                    <strong>

                        <?php

                        echo htmlspecialchars(
                            $_SESSION["nombres"]
                        );

                        ?>

                    </strong>

                </h4>


                <!-- =================================================
                     TARJETAS
                ================================================== -->

                <div class="row">


                    <!-- ESTUDIANTES -->

                    <div class="col-md-3 mb-4">

                        <div class="card text-white bg-primary shadow">

                            <div class="card-body">

                                <h5 class="card-title">
                                    Estudiantes
                                </h5>

                                <h2>
                                    0
                                </h2>

                                <p class="card-text">
                                    Estudiantes registrados
                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- DOCENTES -->

                    <div class="col-md-3 mb-4">

                        <div class="card text-white bg-success shadow">

                            <div class="card-body">

                                <h5 class="card-title">
                                    Docentes
                                </h5>

                                <h2>
                                    0
                                </h2>

                                <p class="card-text">
                                    Docentes registrados
                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- CURSOS -->

                    <div class="col-md-3 mb-4">

                        <div class="card text-dark bg-warning shadow">

                            <div class="card-body">

                                <h5 class="card-title">
                                    Cursos
                                </h5>

                                <h2>
                                    0
                                </h2>

                                <p class="card-text">
                                    Cursos registrados
                                </p>

                            </div>

                        </div>

                    </div>


                    <!-- REPORTES -->

                    <div class="col-md-3 mb-4">

                        <div class="card text-white bg-danger shadow">

                            <div class="card-body">

                                <h5 class="card-title">
                                    Reportes
                                </h5>

                                <h2>
                                    0
                                </h2>

                                <p class="card-text">
                                    Reportes realizados
                                </p>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     ACCESOS RAPIDOS
                ================================================== -->

                <hr>

                <h3 class="mb-4">
                    Accesos rápidos
                </h3>


                <div class="row">


                    <!-- IMPORTAR -->

                    <div class="col-md-4 mb-3">

                        <a
                            href="/Evaloranet/coordinador/importar/index.php"
                            class="btn btn-primary w-100"
                        >
                            Importar estudiantes y docentes
                        </a>

                    </div>


                    <!-- MATERIAS -->

                    <div class="col-md-4 mb-3">

                        <a
                            href="/Evaloranet/coordinador/materias/index.php"
                            class="btn btn-success w-100"
                        >
                            Administrar materias
                        </a>

                    </div>


                    <!-- CARGA ACADEMICA -->

                    <div class="col-md-4 mb-3">

                        <a
                            href="/Evaloranet/coordinador/carga_academica/index.php"
                            class="btn btn-warning w-100"
                        >
                            Asignar carga académica
                        </a>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php

/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

include(__DIR__ . "/../includes/footer.php");

?>

<?php

/*
|--------------------------------------------------------------------------
| SEGURIDAD
|--------------------------------------------------------------------------
*/

require_once(__DIR__ . "/../../config/seguridad/coordinador.php");


/*
|--------------------------------------------------------------------------
| HEADER
|--------------------------------------------------------------------------
*/

include(__DIR__ . "/../../includes/header.php");


/*
|--------------------------------------------------------------------------
| NAVBAR
|--------------------------------------------------------------------------
*/

include(__DIR__ . "/../../includes/navbar.php");

?>

<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             MENU LATERAL
        ====================================================== -->

        <div class="col-md-2 p-0">

            <?php

            include(__DIR__ . "/../../includes/sidebar.php");

            ?>

        </div>


        <!-- =====================================================
             CONTENIDO
        ====================================================== -->

        <div class="col-md-10">

            <div class="container mt-4">


                <!-- TITULO -->

                <h1 class="mb-3">

                    Importar estudiantes y docentes

                </h1>


                <p class="text-muted">

                    Desde esta sección el coordinador puede
                    cargar la información de estudiantes y docentes
                    mediante un archivo Excel.

                </p>


                <!-- =================================================
                     TARJETA PRINCIPAL
                ================================================== -->

                <div class="card shadow">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            Cargar archivo Excel

                        </h5>

                    </div>


                    <div class="card-body">


                        <!-- FORMULARIO -->

                        <form
                            action="procesar.php"
                            method="POST"
                            enctype="multipart/form-data"
                        >


                            <!-- ARCHIVO -->

                            <div class="mb-4">

                                <label
                                    for="archivo"
                                    class="form-label"
                                >

                                    Seleccione el archivo Excel

                                </label>


                                <input
                                    type="file"
                                    name="archivo"
                                    id="archivo"
                                    class="form-control"
                                    accept=".xlsx,.xls"
                                    required
                                >


                                <div class="form-text">

                                    Formatos permitidos:
                                    .xlsx y .xls

                                </div>

                            </div>


                            <!-- TIPO DE INFORMACION -->

                            <div class="mb-4">

                                <label
                                    for="tipo"
                                    class="form-label"
                                >

                                    Tipo de información

                                </label>


                                <select
                                    name="tipo"
                                    id="tipo"
                                    class="form-select"
                                    required
                                >

                                    <option value="">

                                        Seleccione una opción

                                    </option>


                                    <option value="estudiantes">

                                        Estudiantes

                                    </option>


                                    <option value="docentes">

                                        Docentes

                                    </option>

                                </select>

                            </div>


                            <!-- BOTONES -->

                            <div class="d-flex gap-2">

                                <button
                                    type="submit"
                                    class="btn btn-primary"
                                >

                                    Importar archivo

                                </button>


                                <a
                                    href="/Evaloranet/coordinador/index.php"
                                    class="btn btn-secondary"
                                >

                                    Volver

                                </a>

                            </div>


                        </form>

                    </div>

                </div>


                <!-- =================================================
                     INFORMACION
                ================================================== -->

                <div class="alert alert-info mt-4">

                    <strong>Importante:</strong>

                    Antes de importar información debemos utilizar
                    la estructura correcta del archivo Excel.

                    Los datos serán validados antes de guardarse
                    en la base de datos.

                </div>


                <!-- =================================================
                     PROXIMO PASO
                ================================================== -->

                <div class="card mt-4">

                    <div class="card-body">

                        <h5>

                            ¿Cómo funcionará la importación?

                        </h5>


                        <ol>

                            <li>
                                Seleccionar el archivo Excel.
                            </li>

                            <li>
                                Indicar si contiene estudiantes
                                o docentes.
                            </li>

                            <li>
                                El sistema revisará los datos.
                            </li>

                            <li>
                                Se detectarán registros repetidos.
                            </li>

                            <li>
                                Los datos correctos serán guardados.
                            </li>

                            <li>
                                El sistema mostrará un resumen
                                de la importación.
                            </li>

                        </ol>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php

/*
|--------------------------------------------------------------------------
| FOOTER
|--------------------------------------------------------------------------
*/

include(__DIR__ . "/../../includes/footer.php");

?>