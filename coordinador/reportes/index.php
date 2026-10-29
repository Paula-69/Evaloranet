
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");

// =====================================================
// ESTADÍSTICAS GENERALES
// =====================================================

$sql = $conexion->query("SELECT COUNT(*) AS total FROM estudiantes");
$estudiantes = $sql ? (int) $sql->fetch_assoc()["total"] : 0;

$sql = $conexion->query("SELECT COUNT(*) AS total FROM docentes");
$docentes = $sql ? (int) $sql->fetch_assoc()["total"] : 0;

$sql = $conexion->query("SELECT COUNT(*) AS total FROM cursos");
$cursos = $sql ? (int) $sql->fetch_assoc()["total"] : 0;

$sql = $conexion->query("SELECT COUNT(*) AS total FROM materias");
$materias = $sql ? (int) $sql->fetch_assoc()["total"] : 0;

$sql = $conexion->query("SELECT COUNT(*) AS total FROM carga_academica");
$cargas = $sql ? (int) $sql->fetch_assoc()["total"] : 0;


// =====================================================
// RESUMEN DE DESEMPEÑO
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
    GROUP BY cd.id, cd.desempeno, cd.color
    ORDER BY cd.id
");

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
    ORDER BY u.apellidos ASC, u.nombres ASC
";

$resultadoCargaDocente = $conexion->query($sqlCargaDocente);


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
    GROUP BY c.id, c.nombre
    ORDER BY c.nombre ASC
";

$resultadoEstudiantesCurso = $conexion->query($sqlEstudiantesCurso);


// =====================================================
// ESTRUCTURA VISUAL COMPARTIDA
// =====================================================

include("../../includes/header.php");
include("../../includes/navbar.php");

?>

<div class="evalora-layout">

    <?php include("../../includes/sidebar.php"); ?>

    <main class="evalora-main reportes-page">

        <div class="reportes-contenido">

            <!-- ENCABEZADO -->
            <header class="reportes-heading">
                <div class="reportes-heading-icon" aria-hidden="true">
                    <span>▤</span>
                </div>

                <div>
                    <h1>Reportes académicos</h1>
                    <p class="reportes-descripcion">
                        Resumen general y seguimiento del desempeño
                        de los estudiantes.
                    </p>
                </div>
            </header>


            <!-- ESTADÍSTICAS GENERALES -->
            <section class="reportes-estadisticas">

                <article class="reportes-estadistica">
                    <div class="reportes-stat-icon stat-azul" aria-hidden="true">
                        ♙
                    </div>
                    <div class="estadistica-titulo">Estudiantes</div>
                    <p class="estadistica-valor">
                        <?= $estudiantes ?>
                    </p>
                    <div class="reportes-stat-pie">Registrados en el sistema</div>
                </article>

                <article class="reportes-estadistica">
                    <div class="reportes-stat-icon stat-verde" aria-hidden="true">
                        ♧
                    </div>
                    <div class="estadistica-titulo">Docentes</div>
                    <p class="estadistica-valor">
                        <?= $docentes ?>
                    </p>
                    <div class="reportes-stat-pie">Personal docente</div>
                </article>

                <article class="reportes-estadistica">
                    <div class="reportes-stat-icon stat-amarillo" aria-hidden="true">
                        ▦
                    </div>
                    <div class="estadistica-titulo">Cursos</div>
                    <p class="estadistica-valor">
                        <?= $cursos ?>
                    </p>
                    <div class="reportes-stat-pie">Cursos registrados</div>
                </article>

                <article class="reportes-estadistica">
                    <div class="reportes-stat-icon stat-cian" aria-hidden="true">
                        ▣
                    </div>
                    <div class="estadistica-titulo">Materias</div>
                    <p class="estadistica-valor">
                        <?= $materias ?>
                    </p>
                    <div class="reportes-stat-pie">Materias registradas</div>
                </article>

                <article class="reportes-estadistica">
                    <div class="reportes-stat-icon stat-gris" aria-hidden="true">
                        ▤
                    </div>
                    <div class="estadistica-titulo">Cargas académicas</div>
                    <p class="estadistica-valor">
                        <?= $cargas ?>
                    </p>
                    <div class="reportes-stat-pie">Asignaciones registradas</div>
                </article>

            </section>


            <!-- BALANCE DE DESEMPEÑO -->
            <section class="card reportes-panel">

                <div class="reportes-panel-header">
                    <div class="reportes-panel-title-icon" aria-hidden="true">
                        ◉
                    </div>

                    <div>
                        <h2>Balance general de desempeño</h2>
                        <p>Distribución de estudiantes por nivel de desempeño.</p>
                    </div>
                </div>

                <div class="card-body">
                    <div class="row g-3">

                        <?php foreach ($resumen_colores as $color): ?>

                            <div class="col-12 col-md-4">

                                <article
                                    class="reportes-desempeno"
                                    style="--desempeno-color: <?= htmlspecialchars(
                                        $color["color"],
                                        ENT_QUOTES,
                                        "UTF-8"
                                    ) ?>;"
                                >

                                    <div class="reportes-desempeno-indicador"></div>

                                    <div class="reportes-desempeno-contenido">

                                        <h3>
                                            <?= htmlspecialchars(
                                                ucfirst($color["desempeno"]),
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </h3>

                                        <div class="reportes-desempeno-total">
                                            <?= (int) $color["total"] ?>
                                        </div>

                                        <p>estudiantes</p>

                                    </div>

                                </article>

                            </div>

                        <?php endforeach; ?>

                    </div>
                </div>

            </section>


            <!-- DOCENTES Y SEGUIMIENTO -->
            <section class="card reportes-panel">

                <div class="reportes-panel-header">
                    <div class="reportes-panel-title-icon" aria-hidden="true">
                        ♧
                    </div>

                    <div>
                        <h2>Docentes y seguimiento</h2>
                        <p>Consulta las asignaciones y el seguimiento de cada docente.</p>
                    </div>
                </div>

                <div class="card-body p-0">

                    <?php if (
                        $resultadoCargaDocente &&
                        $resultadoCargaDocente->num_rows > 0
                    ): ?>

                        <div class="table-responsive">

                            <table class="table reportes-table align-middle">

                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Docente</th>
                                        <th scope="col">Documento</th>
                                        <th scope="col">Asignaciones</th>
                                        <th scope="col" class="text-center">
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php
                                    $contador = 1;

                                    while (
                                        $fila = $resultadoCargaDocente->fetch_assoc()
                                    ):
                                    ?>

                                        <tr>

                                            <td class="reportes-numero">
                                                <?= $contador++ ?>
                                            </td>

                                            <td>
                                                <strong class="reportes-nombre">
                                                    <?= htmlspecialchars(
                                                        $fila["apellidos"] . ", " .
                                                        $fila["nombres"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ) ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <?= htmlspecialchars(
                                                    $fila["documento"],
                                                    ENT_QUOTES,
                                                    "UTF-8"
                                                ) ?>
                                            </td>

                                            <td>
                                                <span class="reportes-badge">
                                                    <?= (int) $fila["total"] ?>
                                                </span>
                                            </td>

                                            <td class="text-center">

                                                <?php if ((int) $fila["total"] > 0): ?>

                                                    <a
                                                        href="docente.php?id=<?= (int) $fila["docente_id"] ?>"
                                                        class="btn reportes-btn-seguimiento"
                                                    >
                                                        Ver seguimiento
                                                        <span aria-hidden="true">→</span>
                                                    </a>

                                                <?php else: ?>

                                                    <span class="reportes-sin-asignaciones">
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

                        <div class="reportes-vacio">
                            No hay docentes registrados.
                        </div>

                    <?php endif; ?>

                </div>

            </section>


            <!-- ESTUDIANTES POR CURSO -->
            <section class="card reportes-panel">

                <div class="reportes-panel-header">
                    <div class="reportes-panel-title-icon" aria-hidden="true">
                        ▦
                    </div>

                    <div>
                        <h2>Estudiantes por curso</h2>
                        <p>Consulta la cantidad de estudiantes registrados en cada curso.</p>
                    </div>
                </div>

                <div class="card-body p-0">

                    <?php if (
                        $resultadoEstudiantesCurso &&
                        $resultadoEstudiantesCurso->num_rows > 0
                    ): ?>

                        <div class="table-responsive">

                            <table class="table reportes-table align-middle">

                                <thead>
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Curso</th>
                                        <th scope="col">Estudiantes</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php
                                    $contador = 1;

                                    while (
                                        $fila = $resultadoEstudiantesCurso->fetch_assoc()
                                    ):
                                    ?>

                                        <tr>

                                            <td class="reportes-numero">
                                                <?= $contador++ ?>
                                            </td>

                                            <td>
                                                <strong class="reportes-nombre">
                                                    <?= htmlspecialchars(
                                                        $fila["curso"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ) ?>
                                                </strong>
                                            </td>

                                            <td>
                                                <span class="reportes-badge reportes-badge-azul">
                                                    <?= (int) $fila["total"] ?>
                                                </span>
                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="reportes-vacio">
                            No hay cursos registrados.
                        </div>

                    <?php endif; ?>

                </div>

            </section>

        </div>

    </main>

</div>

<?php include("../../includes/footer.php"); ?>
```