<?php

session_start();

require_once("../config/config.php");
require_once("../config/seguridad/docente.php");

include("../includes/header.php");
include("../includes/navbar.php");

?>

<div class="container-fluid">

    <div class="row">

        <!-- ==================================================
             MENU LATERAL
        =================================================== -->

        <div class="col-md-2 p-0">

            <?php

            include("../includes/sidebar.php");

            ?>

        </div>


        <!-- ==================================================
             CONTENIDO PRINCIPAL
        =================================================== -->

        <div class="col-md-10">

            <div class="container mt-4">


                <!-- ==================================================
                     TITULO
                =================================================== -->

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

                $usuario_id = intval(
                    $_SESSION["id"] ?? 0
                );


                if ($usuario_id <= 0) {

                    echo '
                    <div class="alert alert-danger">
                        Sesión de usuario no válida.
                    </div>
                    ';

                } else {


                    $sql = "
                        SELECT id
                        FROM docentes
                        WHERE usuario_id = ?
                        LIMIT 1
                    ";


                    $stmt = $conexion->prepare($sql);


                    if (!$stmt) {

                        die(
                            "Error preparando consulta del docente: "
                            . $conexion->error
                        );

                    }


                    $stmt->bind_param(
                        "i",
                        $usuario_id
                    );


                    $stmt->execute();


                    $resultado =
                        $stmt->get_result();


                    /*
                    |--------------------------------------------------------------------------
                    | VERIFICAR DOCENTE
                    |--------------------------------------------------------------------------
                    */

                    if (
                        $resultado->num_rows === 0
                    ) {

                        ?>

                        <div class="alert alert-danger">

                            No se encontró el perfil de docente
                            asociado a este usuario.

                        </div>

                        <?php

                        $stmt->close();

                    } else {


                        $docente =
                            $resultado->fetch_assoc();


                        $docente_id =
                            intval(
                                $docente["id"]
                            );


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
                                c.nombre ASC,
                                m.nombre ASC

                        ";


                        $stmt =
                            $conexion->prepare($sql);


                        if (!$stmt) {

                            die(
                                "Error preparando consulta de carga académica: "
                                . $conexion->error
                            );

                        }


                        $stmt->bind_param(
                            "i",
                            $docente_id
                        );


                        $stmt->execute();


                        $resultado =
                            $stmt->get_result();


                        $cantidad =
                            $resultado->num_rows;

                        ?>


                        <!-- ==================================================
                             TARJETA DE ASIGNACIONES
                        =================================================== -->

                        <div class="row mb-4">

                            <div class="col-md-4">

                                <div
                                    class="card bg-primary text-white shadow"
                                >

                                    <div class="card-body">

                                        <h5>
                                            Mis asignaciones
                                        </h5>

                                        <h2>

                                            <?= $cantidad ?>

                                        </h2>

                                        <small>
                                            Cargas académicas asignadas
                                        </small>

                                    </div>

                                </div>

                            </div>

                        </div>


                        <!-- ==================================================
                             TABLA DE CARGA ACADÉMICA
                        =================================================== -->

                        <div class="card shadow">


                            <div
                                class="card-header
                                       bg-primary
                                       text-white"
                            >

                                <h5 class="mb-0">

                                    Mis cursos y materias

                                </h5>

                            </div>


                            <div class="card-body">


                                <div class="table-responsive">


                                    <table
                                        class="table
                                               table-bordered
                                               table-striped
                                               align-middle"
                                    >


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

                                        /*
                                        |--------------------------------------------------------------------------
                                        | MOSTRAR CARGAS
                                        |--------------------------------------------------------------------------
                                        */

                                        if (
                                            $cantidad > 0
                                        ) {

                                            $contador = 1;


                                            while (
                                                $fila =
                                                $resultado->fetch_assoc()
                                            ) {

                                                ?>


                                                <tr>


                                                    <!-- NUMERO -->

                                                    <td>

                                                        <?= $contador++ ?>

                                                    </td>


                                                    <!-- CURSO -->

                                                    <td>

                                                        <?= htmlspecialchars(
                                                            $fila["curso"]
                                                        ) ?>

                                                    </td>


                                                    <!-- MATERIA -->

                                                    <td>

                                                        <?= htmlspecialchars(
                                                            $fila["materia"]
                                                        ) ?>

                                                    </td>


                                                    <!-- ACCION -->

                                                    <td>

                                                        <a
                                                            href="desempeno/index.php?id=<?= intval(
                                                                $fila["id"]
                                                            ) ?>"
                                                            class="btn btn-success btn-sm"
                                                        >

                                                            👁️ Ver estudiantes

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

                                                    <div
                                                        class="alert
                                                               alert-info
                                                               mb-0"
                                                    >

                                                        📚 No tienes carga
                                                        académica asignada.

                                                    </div>

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

                }

                ?>


            </div>

        </div>

    </div>

</div>


<?php

include("../includes/footer.php");

?>