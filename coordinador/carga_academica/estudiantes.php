<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");

include("../../includes/header.php");
include("../../includes/navbar.php");


// =========================================================
// VERIFICAR QUE VENGA EL CURSO
// =========================================================

if (!isset($_GET["curso_id"]) || !is_numeric($_GET["curso_id"])) {

    die("Curso no válido.");

}

$curso_id = (int) $_GET["curso_id"];


// =========================================================
// OBTENER NOMBRE DEL CURSO
// =========================================================

$stmt = $conexion->prepare("
    SELECT nombre
    FROM cursos
    WHERE id = ?
");

$stmt->bind_param("i", $curso_id);

$stmt->execute();

$resultado_curso = $stmt->get_result();


if ($resultado_curso->num_rows == 0) {

    die("El curso no existe.");

}


$curso = $resultado_curso->fetch_assoc();


// =========================================================
// OBTENER ESTUDIANTES DEL CURSO
// =========================================================

$stmt = $conexion->prepare("
    SELECT
        e.id,
        e.usuario_id,
        e.estado,
        u.documento,
        u.nombres,
        u.apellidos

    FROM estudiantes e

    INNER JOIN usuarios u
        ON e.usuario_id = u.id

    WHERE e.curso_id = ?

    ORDER BY
        u.apellidos,
        u.nombres
");


$stmt->bind_param(
    "i",
    $curso_id
);

$stmt->execute();

$estudiantes = $stmt->get_result();

?>



<div class="container mt-4">


    <!-- =====================================================
         ENCABEZADO
    ====================================================== -->

    <div
        class="d-flex justify-content-between align-items-center mb-4"
    >

        <div>

            <h2>
                Estudiantes
            </h2>

            <h5 class="text-muted">

                Curso:

                <?= htmlspecialchars(
                    $curso["nombre"]
                ) ?>

            </h5>

        </div>


        <a
            href="index.php"
            class="btn btn-secondary"
        >

            Volver

        </a>

    </div>



    <!-- =====================================================
         SIN ESTUDIANTES
    ====================================================== -->

    <?php if ($estudiantes->num_rows == 0): ?>

        <div class="alert alert-warning">

            Este curso todavía no tiene estudiantes
            asignados.

        </div>


    <?php else: ?>


        <!-- =================================================
             LISTA DE ESTUDIANTES
        ================================================== -->

        <div class="card shadow">


            <div class="card-header bg-primary text-white">

                <strong>

                    Lista de estudiantes

                </strong>

            </div>


            <div class="card-body p-0">


                <div class="table-responsive">


                    <table
                        class="table table-striped table-hover mb-0"
                    >


                        <thead>

                            <tr>

                                <th>
                                    #
                                </th>

                                <th>
                                    Documento
                                </th>

                                <th>
                                    Estudiante
                                </th>

                                <th>
                                    Estado
                                </th>

                            </tr>

                        </thead>


                        <tbody>


                        <?php

                        $numero = 1;

                        while (
                            $estudiante =
                            $estudiantes->fetch_assoc()
                        ):

                        ?>


                            <tr>


                                <!-- NÚMERO -->

                                <td>

                                    <?= $numero++ ?>

                                </td>


                                <!-- DOCUMENTO -->

                                <td>

                                    <?= htmlspecialchars(
                                        $estudiante["documento"]
                                    ) ?>

                                </td>


                                <!-- ESTUDIANTE -->

                                <td>

                                    <?= htmlspecialchars(
                                        $estudiante["nombres"]
                                        . " "
                                        . $estudiante["apellidos"]
                                    ) ?>

                                </td>


                                <!-- ESTADO -->

                                <td>


                                    <?php
                                    if (
                                        $estudiante["estado"]
                                        == "Activo"
                                    ):
                                    ?>

                                        <span
                                            class="badge bg-success"
                                        >

                                            Activo

                                        </span>


                                    <?php else: ?>


                                        <span
                                            class="badge bg-secondary"
                                        >

                                            <?= htmlspecialchars(
                                                $estudiante["estado"]
                                            ) ?>

                                        </span>


                                    <?php endif; ?>


                                </td>


                            </tr>


                        <?php endwhile; ?>


                        </tbody>


                    </table>


                </div>

            </div>

        </div>


    <?php endif; ?>


</div>



<?php

include("../../includes/footer.php");

?>