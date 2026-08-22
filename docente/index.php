<?php

session_start();

require_once("../config/config.php");
require_once("../config/seguridad/docente.php");

include("../includes/header.php");
include("../includes/navbar.php");

?>

<div class="container-fluid">

    <div class="row">

        <!-- MENU LATERAL -->

        <div class="col-md-2 p-0">

            <?php

            include("../includes/sidebar.php");

            ?>

        </div>


        <!-- CONTENIDO -->

        <div class="col-md-10">

            <div class="container mt-4">


                <!-- TITULO -->

                <h2>

                    Panel del docente

                </h2>


                <h4>

                    Bienvenido,

                    <?php

                    echo htmlspecialchars(
                        $_SESSION["nombre"] ?? "Docente"
                    );

                    ?>

                </h4>


                <hr>


                <?php

                /*
                |--------------------------------------------------------------------------
                | BUSCAR DOCENTE
                |--------------------------------------------------------------------------
                */

                $usuario_id = $_SESSION["id"];


                $sql = "
                    SELECT id
                    FROM docentes
                    WHERE usuario_id = ?
                    LIMIT 1
                ";


                $stmt = $conexion->prepare($sql);

                $stmt->bind_param(
                    "i",
                    $usuario_id
                );

                $stmt->execute();

                $resultado = $stmt->get_result();


                if ($resultado->num_rows === 0) {

                    ?>

                    <div class="alert alert-danger">

                        No se encontró el perfil de docente
                        asociado a este usuario.

                    </div>

                    <?php

                } else {


                    $docente = $resultado->fetch_assoc();

                    $docente_id = $docente["id"];

                    $stmt->close();


                    /*
                    |--------------------------------------------------------------------------
                    | CONSULTAR CARGA ACADÉMICA
                    |--------------------------------------------------------------------------
                    */

                    $sql = "
                        SELECT

                            ca.id,

                            c.nombre AS curso,

                            m.nombre AS materia

                        FROM carga_academica ca

                        INNER JOIN cursos c
                            ON ca.curso_id = c.id

                        INNER JOIN materias m
                            ON ca.materia_id = m.id

                        WHERE ca.docente_id = ?

                        ORDER BY
                            c.nombre,
                            m.nombre
                    ";


                    $stmt = $conexion->prepare($sql);

                    $stmt->bind_param(
                        "i",
                        $docente_id
                    );

                    $stmt->execute();

                    $resultado = $stmt->get_result();


                    $cantidad = $resultado->num_rows;

                    ?>


                    <!-- TARJETA -->

                    <div class="row mb-4">

                        <div class="col-md-4">

                            <div class="card bg-primary text-white shadow">

                                <div class="card-body">

                                    <h5>
                                        Mis asignaciones
                                    </h5>

                                    <h2>

                                        <?= $cantidad ?>

                                    </h2>

                                </div>

                            </div>

                        </div>

                    </div>


                    <!-- TABLA -->

                    <div class="card shadow">

                        <div class="card-header bg-primary text-white">

                            <h5 class="mb-0">

                                Mis cursos y materias

                            </h5>

                        </div>


                        <div class="card-body">

                            <div class="table-responsive">

                                <table class="table table-bordered table-striped">

                                    <thead>

                                        <tr>

                                            <th>
                                                #
                                            </th>

                                            <th>
                                                Curso
                                            </th>

                                            <th>
                                                Materia
                                            </th>

                                            <th>
                                                Acción
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>

                                    <?php

                                    if ($cantidad > 0) {

                                        $contador = 1;


                                        while (
                                            $fila =
                                            $resultado->fetch_assoc()
                                        ) {

                                    ?>

                                        <tr>

                                            <td>

                                                <?= $contador++ ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $fila["curso"]
                                                ) ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $fila["materia"]
                                                ) ?>

                                            </td>


                                            <td>

                                                <a
                                                    href="desempeno/index.php?id=<?= $fila["id"] ?>"
                                                    class="btn btn-success btn-sm"
                                                >

                                                    Ver estudiantes

                                                </a>

                                            </td>

                                        </tr>

                                    <?php

                                        }

                                    } else {

                                    ?>

                                        <tr>

                                            <td
                                                colspan="4"
                                                class="text-center"
                                            >

                                                No tienes carga académica
                                                asignada.

                                            </td>

                                        </tr>

                                    <?php

                                    }

                                    ?>

                                    </tbody>

                                </table>

                            </div>

                        </div>

                    </div>


                    <?php

                    $stmt->close();

                }

                ?>

            </div>

        </div>

    </div>

</div>


<?php

include("../includes/footer.php");

?>