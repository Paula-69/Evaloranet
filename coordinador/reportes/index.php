<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");


// =====================================================
// ESTADÍSTICAS GENERALES
// =====================================================

$sql = $conexion->query("
    SELECT COUNT(*) AS total
    FROM estudiantes
");

$estudiantes = $sql ? $sql->fetch_assoc()["total"] : 0;


$sql = $conexion->query("
    SELECT COUNT(*) AS total
    FROM docentes
");

$docentes = $sql ? $sql->fetch_assoc()["total"] : 0;


$sql = $conexion->query("
    SELECT COUNT(*) AS total
    FROM cursos
");

$cursos = $sql ? $sql->fetch_assoc()["total"] : 0;


$sql = $conexion->query("
    SELECT COUNT(*) AS total
    FROM materias
");

$materias = $sql ? $sql->fetch_assoc()["total"] : 0;


$sql = $conexion->query("
    SELECT COUNT(*) AS total
    FROM carga_academica
");

$cargas = $sql ? $sql->fetch_assoc()["total"] : 0;


// =====================================================
// RESUMEN DE SEMÁFORO
// =====================================================

$sql = $conexion->query("
    SELECT
        cd.id,
        cd.desempeno,
        cd.color,
        COUNT(de.id) AS total
    FROM colores_desempeno cd
    LEFT JOIN desempeno_estudiantes de
        ON de.color_id = cd.id
    GROUP BY
        cd.id,
        cd.desempeno,
        cd.color
    ORDER BY cd.id
");


// Guardamos los colores para poder usarlos después
$resumen_colores = [];

if ($sql) {

    while ($fila = $sql->fetch_assoc()) {

        $resumen_colores[] = $fila;

    }

}


// =====================================================
// DOCENTES Y CARGA ACADÉMICA
// =====================================================

$sqlCargaDocente = "
    SELECT

        d.id AS docente_id,

        u.nombres,

        u.apellidos,

        u.documento,

        COUNT(ca.id) AS total

    FROM docentes d

    INNER JOIN usuarios u
        ON d.usuario_id = u.id

    LEFT JOIN carga_academica ca
        ON ca.docente_id = d.id

    GROUP BY
        d.id,
        u.nombres,
        u.apellidos,
        u.documento

    ORDER BY
        u.apellidos ASC,
        u.nombres ASC
";

$resultadoCargaDocente =
    $conexion->query($sqlCargaDocente);


// =====================================================
// MATERIAS
// =====================================================

$sqlMateriasAsignadas = "
    SELECT

        m.nombre AS materia,

        COUNT(ca.id) AS total

    FROM materias m

    LEFT JOIN carga_academica ca
        ON ca.materia_id = m.id

    GROUP BY
        m.id,
        m.nombre

    ORDER BY
        m.nombre ASC
";

$resultadoMateriasAsignadas =
    $conexion->query($sqlMateriasAsignadas);


// =====================================================
// ESTUDIANTES POR CURSO
// =====================================================

$sqlEstudiantesCurso = "
    SELECT

        c.id AS curso_id,

        c.nombre AS curso,

        COUNT(e.id) AS total

    FROM cursos c

    LEFT JOIN estudiantes e
        ON e.curso_id = c.id

    GROUP BY
        c.id,
        c.nombre

    ORDER BY
        c.nombre ASC
";

$resultadoEstudiantesCurso =
    $conexion->query($sqlEstudiantesCurso);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Reportes académicos</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>


<?php include("../../includes/navbar.php"); ?>


<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-2 p-0">

            <?php include("../../includes/sidebar.php"); ?>

        </div>


        <!-- CONTENIDO -->

        <div class="col-md-10">

            <div class="container mt-4">


                <h2>
                    Reportes académicos
                </h2>

                <p class="text-muted">

                    Resumen general y seguimiento del desempeño
                    de los estudiantes.

                </p>

                <hr>


                <!-- =================================================
                     ESTADÍSTICAS GENERALES
                ================================================== -->

                <div class="row g-3 mb-4">


                    <div class="col-md-4 col-lg-2">

                        <div class="card shadow bg-primary text-white">

                            <div class="card-body text-center">

                                <h6>
                                    Estudiantes
                                </h6>

                                <h2>
                                    <?= $estudiantes ?>
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4 col-lg-2">

                        <div class="card shadow bg-success text-white">

                            <div class="card-body text-center">

                                <h6>
                                    Docentes
                                </h6>

                                <h2>
                                    <?= $docentes ?>
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4 col-lg-2">

                        <div class="card shadow bg-warning">

                            <div class="card-body text-center">

                                <h6>
                                    Cursos
                                </h6>

                                <h2>
                                    <?= $cursos ?>
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4 col-lg-2">

                        <div class="card shadow bg-info text-white">

                            <div class="card-body text-center">

                                <h6>
                                    Materias
                                </h6>

                                <h2>
                                    <?= $materias ?>
                                </h2>

                            </div>

                        </div>

                    </div>


                    <div class="col-md-4 col-lg-2">

                        <div class="card shadow bg-secondary text-white">

                            <div class="card-body text-center">

                                <h6>
                                    Cargas
                                </h6>

                                <h2>
                                    <?= $cargas ?>
                                </h2>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     SEMÁFORO GENERAL
                ================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-dark text-white">

                        <h5 class="mb-0">
                            Balance general de desempeño
                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <?php foreach (
                                $resumen_colores
                                as $color
                            ): ?>

                                <div class="col-md-4">

                                    <div
                                        class="card text-center shadow-sm"
                                        style="
                                            border-left:
                                            8px solid
                                            <?= htmlspecialchars(
                                                $color["color"]
                                            ) ?>;
                                        "
                                    >

                                        <div class="card-body">

                                            <h5>

                                                <?= htmlspecialchars(
                                                    ucfirst(
                                                        $color["desempeno"]
                                                    )
                                                ) ?>

                                            </h5>

                                            <h2>

                                                <?= (int)
                                                    $color["total"] ?>

                                            </h2>

                                            <p class="text-muted mb-0">

                                                estudiantes

                                            </p>

                                        </div>

                                    </div>

                                </div>

                            <?php endforeach; ?>


                        </div>

                    </div>

                </div>


                <!-- =================================================
                     DOCENTES
                ================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-success text-white">

                        <h5 class="mb-0">

                            Docentes y seguimiento

                        </h5>

                    </div>


                    <div class="card-body">

                        <?php if (
                            $resultadoCargaDocente &&
                            $resultadoCargaDocente->num_rows > 0
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
                                                Docente
                                            </th>

                                            <th>
                                                Documento
                                            </th>

                                            <th>
                                                Asignaciones
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
                                        $resultadoCargaDocente->fetch_assoc()
                                    ):

                                    ?>

                                        <tr>

                                            <td>
                                                <?= $contador++ ?>
                                            </td>


                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $fila["apellidos"]
                                                        . ", "
                                                        . $fila["nombres"]
                                                    ) ?>

                                                </strong>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $fila["documento"]
                                                ) ?>

                                            </td>


                                            <td>

                                                <span
                                                    class="badge bg-success"
                                                >

                                                    <?= (int)
                                                        $fila["total"] ?>

                                                </span>

                                            </td>


                                            <td class="text-center">

                                                <?php if (
                                                    $fila["total"] > 0
                                                ): ?>

                                                    <a
                                                        href="docente.php?id=<?= (int) $fila["docente_id"] ?>"
                                                        class="btn btn-primary btn-sm"
                                                    >

                                                        Ver seguimiento

                                                    </a>

                                                <?php else: ?>

                                                    <span
                                                        class="text-muted"
                                                    >

                                                        Sin asignaciones

                                                    </span>

                                                <?php endif; ?>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>

                            <div class="alert alert-info">

                                No hay docentes registrados.

                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- =================================================
                     ESTUDIANTES POR CURSO
                ================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            Estudiantes por curso

                        </h5>

                    </div>


                    <div class="card-body">

                        <?php if (
                            $resultadoEstudiantesCurso &&
                            $resultadoEstudiantesCurso->num_rows > 0
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
                                                Estudiantes
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php

                                    $contador = 1;

                                    while (
                                        $fila =
                                        $resultadoEstudiantesCurso->fetch_assoc()
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

                                                <span
                                                    class="badge bg-primary"
                                                >

                                                    <?= (int)
                                                        $fila["total"] ?>

                                                </span>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>

                            <div class="alert alert-info">

                                No hay estudiantes registrados.

                            </div>

                        <?php endif; ?>

                    </div>

                </div>


                <!-- =================================================
                     MATERIAS
                ================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-info text-white">

                        <h5 class="mb-0">

                            Materias y asignaciones

                        </h5>

                    </div>


                    <div class="card-body">

                        <?php if (
                            $resultadoMateriasAsignadas &&
                            $resultadoMateriasAsignadas->num_rows > 0
                        ): ?>


                            <div class="table-responsive">

                                <table
                                    class="table table-bordered
                                    table-hover"
                                >

                                    <thead class="table-light">

                                        <tr>

                                            <th>
                                                #
                                            </th>

                                            <th>
                                                Materia
                                            </th>

                                            <th>
                                                Asignaciones
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php

                                    $contador = 1;

                                    while (
                                        $fila =
                                        $resultadoMateriasAsignadas->fetch_assoc()
                                    ):

                                    ?>

                                        <tr>

                                            <td>
                                                <?= $contador++ ?>
                                            </td>

                                            <td>

                                                <?= htmlspecialchars(
                                                    $fila["materia"]
                                                ) ?>

                                            </td>

                                            <td>

                                                <span
                                                    class="badge bg-info"
                                                >

                                                    <?= (int)
                                                        $fila["total"] ?>

                                                </span>

                                            </td>

                                        </tr>

                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>

                            <div class="alert alert-info">

                                No hay materias registradas.

                            </div>

                        <?php endif; ?>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<?php include("../../includes/footer.php"); ?>


</body>

</html>