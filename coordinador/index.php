<?php

session_start();

require_once("../config/config.php");
require_once("../config/seguridad/coordinador.php");

include("../includes/header.php");
include("../includes/navbar.php");

?>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->
        <div class="col-md-2 p-0">

            <?php
            include("../includes/sidebar.php");
            ?>

        </div>


        <!-- CONTENIDO PRINCIPAL -->
        <div class="col-md-10">

            <div class="container mt-4">


                <!-- TITULO -->

                <h2>
                    Panel del coordinador
                </h2>


                <h4>

                    Bienvenido,

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["nombre"] ?? "Coordinador"
                    );

                    ?>

                </h4>


                <hr>


                <?php

                /*
                |--------------------------------------------------------------------------
                | ESTUDIANTES
                |--------------------------------------------------------------------------
                */

                $sql = $conexion->query(
                    "SELECT COUNT(*) AS total
                     FROM estudiantes"
                );

                $estudiantes = $sql->fetch_assoc();


                /*
                |--------------------------------------------------------------------------
                | DOCENTES
                |--------------------------------------------------------------------------
                */

                $sql = $conexion->query(
                    "SELECT COUNT(*) AS total
                     FROM docentes"
                );

                $docentes = $sql->fetch_assoc();


                /*
                |--------------------------------------------------------------------------
                | CURSOS
                |--------------------------------------------------------------------------
                */

                $sql = $conexion->query(
                    "SELECT COUNT(*) AS total
                     FROM cursos"
                );

                $cursos = $sql->fetch_assoc();


                /*
                |--------------------------------------------------------------------------
                | REPORTES / DESEMPEÑOS
                |--------------------------------------------------------------------------
                |
                | Los registros que actualmente tenemos guardados
                | se encuentran en desempeno_estudiantes.
                |
                */

                $sql = $conexion->query(
                    "SELECT COUNT(*) AS total
                     FROM desempeno_estudiantes"
                );

                $reportes = $sql->fetch_assoc();

                ?>


                <!-- TARJETAS -->

                <div class="row">


                    <!-- ESTUDIANTES -->

                    <div class="col-md-3">

                        <div class="card bg-primary text-white mb-4">

                            <div class="card-body">

                                <h5>
                                    👨‍🎓 Estudiantes
                                </h5>

                                <h2>

                                    <?php
                                    echo $estudiantes["total"];
                                    ?>

                                </h2>

                                <small>
                                    Registrados
                                </small>

                            </div>

                        </div>

                    </div>


                    <!-- DOCENTES -->

                    <div class="col-md-3">

                        <div class="card bg-success text-white mb-4">

                            <div class="card-body">

                                <h5>
                                    👨‍🏫 Docentes
                                </h5>

                                <h2>

                                    <?php
                                    echo $docentes["total"];
                                    ?>

                                </h2>

                                <small>
                                    Registrados
                                </small>

                            </div>

                        </div>

                    </div>


                    <!-- CURSOS -->

                    <div class="col-md-3">

                        <div class="card bg-warning mb-4">

                            <div class="card-body">

                                <h5>
                                    🏫 Cursos
                                </h5>

                                <h2>

                                    <?php
                                    echo $cursos["total"];
                                    ?>

                                </h2>

                                <small>
                                    Disponibles
                                </small>

                            </div>

                        </div>

                    </div>


                    <!-- REPORTES -->

                    <div class="col-md-3">

                        <div class="card bg-danger text-white mb-4">

                            <div class="card-body">

                                <h5>
                                    📊 Reportes
                                </h5>

                                <h2>

                                    <?php
                                    echo $reportes["total"];
                                    ?>

                                </h2>

                                <small>
                                    Generados
                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- SEPARADOR -->

                <hr>


                <!-- ACCESOS RÁPIDOS -->

                <h4>
                    Accesos rápidos
                </h4>


                <div class="row">


                    <!-- IMPORTAR -->

                    <div class="col-md-4">

                        <a
                            href="importar/"
                            class="btn btn-primary w-100"
                        >

                            📄 Importar estudiantes y docentes

                        </a>

                    </div>


                    <!-- MATERIAS -->

                    <div class="col-md-4">

                        <a
                            href="materias/"
                            class="btn btn-success w-100"
                        >

                            📚 Administrar materias

                        </a>

                    </div>


                    <!-- CARGA ACADÉMICA -->

                    <div class="col-md-4">

                        <a
                            href="carga_academica/"
                            class="btn btn-warning w-100"
                        >

                            👨‍🏫 Asignar carga académica

                        </a>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<?php

include("../includes/footer.php");

?>