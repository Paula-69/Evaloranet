<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");


// =====================================================
// VALIDAR DOCENTE
// =====================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: index.php");
    exit();

}

$docente_id = (int) $_GET["id"];


// =====================================================
// BUSCAR DOCENTE
// =====================================================

$sql = "
    SELECT
        d.id,
        u.nombres,
        u.apellidos,
        u.documento

    FROM docentes d

    INNER JOIN usuarios u
        ON d.usuario_id = u.id

    WHERE d.id = ?

    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $docente_id);

$stmt->execute();

$resultado = $stmt->get_result();


// Si no existe
if ($resultado->num_rows === 0) {

    $stmt->close();

    header("Location: index.php");
    exit();

}

$docente = $resultado->fetch_assoc();

$stmt->close();


// =====================================================
// BUSCAR CARGA ACADÉMICA
// =====================================================

$sql = "
    SELECT

        ca.id AS carga_id,

        c.id AS curso_id,
        c.nombre AS curso,

        m.id AS materia_id,
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

$stmt = $conexion->prepare($sql);

$stmt->bind_param("i", $docente_id);

$stmt->execute();

$cargas = $stmt->get_result();


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


                <!-- BOTÓN VOLVER -->

                <div class="mb-3">

                    <a
                        href="index.php"
                        class="btn btn-secondary"
                    >

                        ← Volver a reportes

                    </a>

                </div>


                <!-- INFORMACIÓN DEL DOCENTE -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-success text-white">

                        <h4 class="mb-0">

                            Seguimiento del docente

                        </h4>

                    </div>


                    <div class="card-body">

                        <h3>

                            <?= htmlspecialchars(
                                $docente["nombres"]
                                . " "
                                . $docente["apellidos"]
                            ) ?>

                        </h3>


                        <p class="mb-1">

                            <strong>
                                Documento:
                            </strong>

                            <?= htmlspecialchars(
                                $docente["documento"]
                            ) ?>

                        </p>


                        <p class="mb-0">

                            <strong>
                                Docente ID:
                            </strong>

                            <?= $docente["id"] ?>

                        </p>

                    </div>

                </div>


                <!-- CARGA ACADÉMICA -->

                <div class="card shadow">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            Cursos y materias asignadas

                        </h5>

                    </div>


                    <div class="card-body">


                        <?php if (
                            $cargas->num_rows > 0
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
                                                Curso
                                            </th>

                                            <th>
                                                Materia
                                            </th>

                                            <th class="text-center">
                                                Acción
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php

                                    $contador = 1;

                                    while (
                                        $fila =
                                        $cargas->fetch_assoc()
                                    ):

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


                                            <td class="text-center">

                                                <a
                                                    href="estudiantes.php?id=<?= (int) $fila["carga_id"] ?>"
                                                    class="btn btn-primary btn-sm"
                                                >

                                                    Ver estudiantes

                                                </a>

                                            </td>

                                        </tr>


                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>


                            <div class="alert alert-warning">

                                Este docente no tiene carga académica
                                asignada.

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