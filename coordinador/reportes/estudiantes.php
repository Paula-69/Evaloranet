<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");


// =====================================================
// VALIDAR CARGA ACADÉMICA
// =====================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: index.php");
    exit();

}

$carga_id = (int) $_GET["id"];


// =====================================================
// OBTENER INFORMACIÓN DE LA CARGA
// =====================================================

$sql = "
    SELECT

        ca.id AS carga_id,

        d.id AS docente_id,

        u.nombres,
        u.apellidos,

        c.id AS curso_id,
        c.nombre AS curso,

        m.id AS materia_id,
        m.nombre AS materia

    FROM carga_academica ca

    INNER JOIN docentes d
        ON ca.docente_id = d.id

    INNER JOIN usuarios u
        ON d.usuario_id = u.id

    INNER JOIN cursos c
        ON ca.curso_id = c.id

    INNER JOIN materias m
        ON ca.materia_id = m.id

    WHERE ca.id = ?

    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $carga_id);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {

    $stmt->close();

    header("Location: index.php");
    exit();

}


$carga = $resultado->fetch_assoc();

$stmt->close();


// =====================================================
// BUSCAR ESTUDIANTES DEL CURSO
// =====================================================

$sql = "
    SELECT

        e.id AS estudiante_id,

        e.usuario_id,

        u.nombres,
        u.apellidos,

        u.documento,

        e.estado,

        de.id AS desempeno_id,

        cd.id AS color_id,

        cd.desempeno,

        cd.color

    FROM estudiantes e

    INNER JOIN usuarios u
        ON e.usuario_id = u.id

    LEFT JOIN desempeno_estudiantes de
        ON de.estudiante_id = e.id
        AND de.carga_academica_id = ?

    LEFT JOIN colores_desempeno cd
        ON de.color_id = cd.id

    WHERE e.curso_id = ?

    ORDER BY
        u.apellidos ASC,
        u.nombres ASC
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ii",
    $carga_id,
    $carga["curso_id"]
);

$stmt->execute();

$estudiantes = $stmt->get_result();


// =====================================================
// HTML
// =====================================================

include("../../includes/header.php");
include("../../includes/navbar.php");

?>

<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-2 p-0">

            <?php

            include("../../includes/sidebar.php");

            ?>

        </div>


        <!-- CONTENIDO -->

        <div class="col-md-10">

            <div class="container mt-4">


                <!-- VOLVER -->

                <div class="mb-3">

                    <a
                        href="docente.php?id=<?= (int) $carga["docente_id"] ?>"
                        class="btn btn-secondary"
                    >

                        ← Volver al docente

                    </a>

                </div>


                <!-- INFORMACIÓN -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h4 class="mb-0">

                            Estudiantes

                        </h4>

                    </div>


                    <div class="card-body">

                        <h3>

                            <?= htmlspecialchars(
                                $carga["materia"]
                            ) ?>

                        </h3>


                        <p class="mb-1">

                            <strong>
                                Curso:
                            </strong>

                            <?= htmlspecialchars(
                                $carga["curso"]
                            ) ?>

                        </p>


                        <p class="mb-0">

                            <strong>
                                Docente:
                            </strong>

                            <?= htmlspecialchars(
                                $carga["nombres"]
                                . " "
                                . $carga["apellidos"]
                            ) ?>

                        </p>

                    </div>

                </div>


                <!-- TABLA DE ESTUDIANTES -->

                <div class="card shadow">

                    <div class="card-header bg-success text-white">

                        <h5 class="mb-0">

                            Seguimiento de estudiantes

                        </h5>

                    </div>


                    <div class="card-body">


                        <?php if (
                            $estudiantes->num_rows > 0
                        ): ?>


                            <div class="table-responsive">

                                <table
                                    class="table table-bordered
                                    table-hover align-middle"
                                >

                                    <thead class="table-light">

                                        <tr>

                                            <th>
                                                #
                                            </th>

                                            <th>
                                                Estudiante
                                            </th>

                                            <th>
                                                Documento
                                            </th>

                                            <th>
                                                Estado
                                            </th>

                                            <th class="text-center">
                                                Desempeño
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php

                                    $contador = 1;

                                    while (
                                        $estudiante =
                                        $estudiantes->fetch_assoc()
                                    ):

                                    ?>


                                        <tr>


                                            <td>

                                                <?= $contador++ ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $estudiante["apellidos"]
                                                        . ", "
                                                        . $estudiante["nombres"]
                                                    ) ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $estudiante["documento"]
                                                ) ?>

                                            </td>


                                            <td>

                                                <?php

                                                if (
                                                    $estudiante["estado"]
                                                    === "Activo"
                                                ) {

                                                    echo '<span class="badge bg-success">
                                                            Activo
                                                          </span>';

                                                } else {

                                                    echo '<span class="badge bg-secondary">'
                                                        . htmlspecialchars(
                                                            $estudiante["estado"]
                                                        )
                                                        . '</span>';

                                                }

                                                ?>

                                            </td>


                                            <td class="text-center">


                                                <?php if (
                                                    !empty(
                                                        $estudiante["desempeno"]
                                                    )
                                                ): ?>


                                                    <span
                                                        class="badge"
                                                        style="
                                                            background-color:
                                                            <?= htmlspecialchars(
                                                                $estudiante["color"]
                                                            ) ?>;

                                                            color:
                                                            #000;

                                                            font-size:
                                                            14px;
                                                        "
                                                    >

                                                        <?= htmlspecialchars(
                                                            ucfirst(
                                                                $estudiante["desempeno"]
                                                            )
                                                        ) ?>

                                                    </span>


                                                <?php else: ?>


                                                    <span
                                                        class="badge bg-secondary"
                                                    >

                                                        Sin registro

                                                    </span>


                                                <?php endif; ?>


                                            </td>


                                        </tr>


                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>


                            <div class="alert alert-warning">

                                No hay estudiantes registrados
                                en este curso.

                            </div>


                        <?php endif; ?>


                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<?php

$stmt->close();

include("../../includes/footer.php");

?>