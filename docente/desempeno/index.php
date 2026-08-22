<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/docente.php");


// ======================================================
// VERIFICAR ID DE CARGA ACADÉMICA
// ======================================================

if (!isset($_GET["id"]) || !is_numeric($_GET["id"])) {

    header("Location: ../index.php");
    exit();

}

$carga_id = (int) $_GET["id"];


// ======================================================
// BUSCAR DOCENTE LOGUEADO
// ======================================================

$usuario_id = $_SESSION["id"];

$stmt = $conexion->prepare("
    SELECT id
    FROM docentes
    WHERE usuario_id = ?
    LIMIT 1
");

$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    die("No se encontró el perfil del docente.");

}

$docente = $resultado->fetch_assoc();

$docente_id = (int) $docente["id"];

$stmt->close();


// ======================================================
// OBTENER INFORMACIÓN DE LA CARGA
// ======================================================

$stmt = $conexion->prepare("
    SELECT
        ca.id,
        ca.docente_id,
        ca.curso_id,
        ca.materia_id,
        c.nombre AS curso,
        m.nombre AS materia
    FROM carga_academica ca

    INNER JOIN cursos c
        ON ca.curso_id = c.id

    INNER JOIN materias m
        ON ca.materia_id = m.id

    WHERE ca.id = ?
    AND ca.docente_id = ?

    LIMIT 1
");

$stmt->bind_param(
    "ii",
    $carga_id,
    $docente_id
);

$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows === 0) {

    die("Esta asignación no pertenece al docente.");

}

$carga = $resultado->fetch_assoc();

$stmt->close();


// ======================================================
// GUARDAR COLOR
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $estudiante_id = isset($_POST["estudiante_id"])
        ? (int) $_POST["estudiante_id"]
        : 0;

    $color_id = isset($_POST["color_id"])
        ? (int) $_POST["color_id"]
        : 0;


    if ($estudiante_id > 0 && $color_id > 0) {


        // Verificar que el estudiante pertenezca
        // al curso de esta carga académica

        $verificar = $conexion->prepare("
            SELECT id
            FROM estudiantes
            WHERE id = ?
            AND curso_id = ?
            AND estado = 'Activo'
            LIMIT 1
        ");

        $verificar->bind_param(
            "ii",
            $estudiante_id,
            $carga["curso_id"]
        );

        $verificar->execute();

        $resultado_verificar = $verificar->get_result();


        if ($resultado_verificar->num_rows === 1) {


            // Verificar si ya existe desempeño

            $buscar = $conexion->prepare("
                SELECT id
                FROM desempeno_estudiantes
                WHERE estudiante_id = ?
                AND carga_academica_id = ?
                LIMIT 1
            ");

            $buscar->bind_param(
                "ii",
                $estudiante_id,
                $carga_id
            );

            $buscar->execute();

            $resultado_buscar = $buscar->get_result();


            if ($resultado_buscar->num_rows > 0) {

                // ACTUALIZAR COLOR

                $registro = $resultado_buscar->fetch_assoc();

                $registro_id = (int) $registro["id"];


                $actualizar = $conexion->prepare("
                    UPDATE desempeno_estudiantes
                    SET color_id = ?,
                        fecha_registro = CURRENT_TIMESTAMP
                    WHERE id = ?
                ");

                $actualizar->bind_param(
                    "ii",
                    $color_id,
                    $registro_id
                );

                $actualizar->execute();

                $actualizar->close();


            } else {

                // INSERTAR COLOR

                $insertar = $conexion->prepare("
                    INSERT INTO desempeno_estudiantes
                    (
                        estudiante_id,
                        carga_academica_id,
                        color_id
                    )
                    VALUES (?, ?, ?)
                ");

                $insertar->bind_param(
                    "iii",
                    $estudiante_id,
                    $carga_id,
                    $color_id
                );

                $insertar->execute();

                $insertar->close();

            }

            $buscar->close();

        }

        $verificar->close();

    }


    header(
        "Location: index.php?id=" .
        $carga_id .
        "&guardado=1"
    );

    exit();

}


// ======================================================
// OBTENER COLORES
// ======================================================

$colores = $conexion->query("
    SELECT
        id,
        desempeno,
        color
    FROM colores_desempeno
    ORDER BY id
");


// ======================================================
// OBTENER ESTUDIANTES DEL CURSO
// ======================================================

$stmt = $conexion->prepare("
    SELECT

        e.id AS estudiante_id,

        u.documento,

        u.nombres,

        u.apellidos,

        de.color_id,

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
    AND e.estado = 'Activo'

    ORDER BY
        u.apellidos,
        u.nombres
");

$stmt->bind_param(
    "ii",
    $carga_id,
    $carga["curso_id"]
);

$stmt->execute();

$estudiantes = $stmt->get_result();


// ======================================================
// CABECERA
// ======================================================

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


                <!-- ENCABEZADO -->

                <div class="d-flex justify-content-between align-items-center mb-4">

                    <div>

                        <h2>

                            Desempeño de estudiantes

                        </h2>

                        <p class="text-muted mb-1">

                            Curso:

                            <strong>

                                <?= htmlspecialchars(
                                    $carga["curso"]
                                ) ?>

                            </strong>

                        </p>

                        <p class="text-muted">

                            Materia:

                            <strong>

                                <?= htmlspecialchars(
                                    $carga["materia"]
                                ) ?>

                            </strong>

                        </p>

                    </div>


                    <a
                        href="../index.php"
                        class="btn btn-secondary"
                    >

                        Volver

                    </a>

                </div>


                <!-- MENSAJE -->

                <?php if (isset($_GET["guardado"])): ?>

                    <div class="alert alert-success">

                        El desempeño fue guardado correctamente.

                    </div>

                <?php endif; ?>


                <!-- LEYENDA -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <strong>

                            Niveles de desempeño

                        </strong>

                    </div>


                    <div class="card-body">

                        <div class="row">

                            <?php

                            $colores->data_seek(0);

                            while ($color = $colores->fetch_assoc()):

                            ?>

                                <div class="col-md-4">

                                    <div
                                        class="p-3 rounded text-center"
                                        style="
                                            background-color:
                                            <?= htmlspecialchars(
                                                $color["color"]
                                            ) ?>;
                                        "
                                    >

                                        <strong>

                                            <?= htmlspecialchars(
                                                ucfirst(
                                                    $color["desempeno"]
                                                )
                                            ) ?>

                                        </strong>

                                    </div>

                                </div>

                            <?php endwhile; ?>

                        </div>

                    </div>

                </div>


                <!-- TABLA -->

                <div class="card shadow">

                    <div class="card-header bg-dark text-white">

                        <strong>

                            Estudiantes

                        </strong>

                    </div>


                    <div class="card-body p-0">

                        <?php if ($estudiantes->num_rows === 0): ?>

                            <div class="alert alert-warning m-3">

                                Este curso no tiene estudiantes activos.

                            </div>

                        <?php else: ?>


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
                                                Desempeño actual
                                            </th>

                                            <th>
                                                Registrar
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

                                            <td>

                                                <?= $numero++ ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $estudiante[
                                                        "documento"
                                                    ]
                                                ) ?>

                                            </td>


                                            <td>

                                                <?= htmlspecialchars(
                                                    $estudiante[
                                                        "nombres"
                                                    ] .
                                                    " " .
                                                    $estudiante[
                                                        "apellidos"
                                                    ]
                                                ) ?>

                                            </td>


                                            <!-- COLOR ACTUAL -->

                                            <td>

                                                <?php if (
                                                    !empty(
                                                        $estudiante[
                                                            "color"
                                                        ]
                                                    )
                                                ): ?>

                                                    <span
                                                        class="badge p-2"
                                                        style="
                                                            background-color:
                                                            <?= htmlspecialchars(
                                                                $estudiante[
                                                                    "color"
                                                                ]
                                                            ) ?>;
                                                        "
                                                    >

                                                        <?= htmlspecialchars(
                                                            ucfirst(
                                                                $estudiante[
                                                                    "desempeno"
                                                                ]
                                                            )
                                                        ) ?>

                                                    </span>

                                                <?php else: ?>

                                                    <span
                                                        class="badge bg-secondary"
                                                    >

                                                        Sin registrar

                                                    </span>

                                                <?php endif; ?>

                                            </td>


                                            <!-- BOTONES -->

                                            <td>

                                                <form
                                                    method="POST"
                                                    class="d-flex gap-2"
                                                >

                                                    <input
                                                        type="hidden"
                                                        name="estudiante_id"
                                                        value="<?= (int) $estudiante[
                                                            "estudiante_id"
                                                        ] ?>"
                                                    >


                                                    <?php

                                                    $colores->data_seek(0);

                                                    while (
                                                        $color =
                                                        $colores->fetch_assoc()
                                                    ):

                                                    ?>

                                                        <button
                                                            type="submit"
                                                            name="color_id"
                                                            value="<?= (int) $color["id"] ?>"
                                                            class="btn btn-sm"
                                                            style="
                                                                background-color:
                                                                <?= htmlspecialchars(
                                                                    $color[
                                                                        "color"
                                                                    ]
                                                                ) ?>;
                                                                border: 1px solid #999;
                                                            "
                                                            title="<?= htmlspecialchars(
                                                                ucfirst(
                                                                    $color[
                                                                        "desempeno"
                                                                    ]
                                                                )
                                                            ) ?>"
                                                        >

                                                            <?= htmlspecialchars(
                                                                ucfirst(
                                                                    $color[
                                                                        "desempeno"
                                                                    ]
                                                                )
                                                            ) ?>

                                                        </button>

                                                    <?php endwhile; ?>

                                                </form>

                                            </td>

                                        </tr>


                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php endif; ?>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<?php

include("../../includes/footer.php");

?>