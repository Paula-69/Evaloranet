<?php

session_start();

require_once("../config/config.php");
require_once("../config/seguridad/estudiante.php");


// =====================================================
// BUSCAR ESTUDIANTE RELACIONADO CON EL USUARIO
// =====================================================

$usuario_id = $_SESSION["id"] ?? 0;


if ($usuario_id <= 0) {

    header("Location: index.php");
    exit();

}


$sql = "
    SELECT

        e.id AS estudiante_id,

        e.curso_id,

        c.nombre AS curso

    FROM estudiantes e

    INNER JOIN cursos c
        ON e.curso_id = c.id

    WHERE e.usuario_id = ?

    LIMIT 1
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $usuario_id
);

$stmt->execute();

$resultado = $stmt->get_result();


// =====================================================
// VERIFICAR ESTUDIANTE
// =====================================================

if ($resultado->num_rows === 0) {

    $stmt->close();

    ?>

    <div class="container mt-4">

        <div class="alert alert-danger">

            No se encontró información académica
            asociada a este estudiante.

        </div>

    </div>

    <?php

    include("../includes/footer.php");

    exit();

}


$estudiante = $resultado->fetch_assoc();

$estudiante_id =
    (int) $estudiante["estudiante_id"];

$curso_id =
    (int) $estudiante["curso_id"];

$curso =
    $estudiante["curso"];

$stmt->close();


// =====================================================
// OBTENER PERÍODO SELECCIONADO
// =====================================================

// Por defecto mostramos el Período 1

$periodo_id = isset($_GET["periodo_id"])
    ? (int) $_GET["periodo_id"]
    : 1;


// =====================================================
// VERIFICAR PERÍODO
// =====================================================

$stmtPeriodo = $conexion->prepare("
    SELECT
        id,
        nombre
    FROM periodos
    WHERE id = ?
    LIMIT 1
");

$stmtPeriodo->bind_param(
    "i",
    $periodo_id
);

$stmtPeriodo->execute();

$resultadoPeriodo =
    $stmtPeriodo->get_result();


if ($resultadoPeriodo->num_rows === 0) {

    $periodo_id = 1;

    $stmtPeriodo->close();


    $stmtPeriodo = $conexion->prepare("
        SELECT
            id,
            nombre
        FROM periodos
        WHERE id = ?
        LIMIT 1
    ");

    $stmtPeriodo->bind_param(
        "i",
        $periodo_id
    );

    $stmtPeriodo->execute();

    $resultadoPeriodo =
        $stmtPeriodo->get_result();

}


$periodo_actual =
    $resultadoPeriodo->fetch_assoc();

$periodo_nombre =
    $periodo_actual["nombre"] ?? "Periodo 1";

$stmtPeriodo->close();


// =====================================================
// OBTENER TODOS LOS PERÍODOS
// =====================================================

$periodos = $conexion->query("
    SELECT
        id,
        nombre
    FROM periodos
    ORDER BY id ASC
");


// =====================================================
// BUSCAR DESEMPEÑO
// =====================================================

$sql = "

    SELECT

        m.nombre AS materia,

        cd.desempeno,

        cd.color,

        ds.fecha_registro

    FROM desempeno_estudiantes ds

    INNER JOIN carga_academica ca
        ON ds.carga_academica_id = ca.id

    INNER JOIN materias m
        ON ca.materia_id = m.id

    INNER JOIN colores_desempeno cd
        ON ds.color_id = cd.id

    WHERE ds.estudiante_id = ?

    AND ds.periodo_id = ?

    ORDER BY
        m.nombre ASC

";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "ii",
    $estudiante_id,
    $periodo_id
);

$stmt->execute();

$resultado =
    $stmt->get_result();


// =====================================================
// HEADER
// =====================================================

include("../includes/header.php");

include("../includes/navbar.php");

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             MENÚ LATERAL
        ====================================================== -->

        <div class="col-md-2 p-0">

            <?php

            include("../includes/sidebar.php");

            ?>

        </div>


        <!-- =====================================================
             CONTENIDO
        ====================================================== -->

        <div class="col-md-10">

            <div class="container mt-4">


                <!-- =================================================
                     ENCABEZADO
                ================================================== -->

                <div class="mb-4">

                    <h2>

                        Mi desempeño académico

                    </h2>


                    <h5 class="text-muted">

                        Estudiante:

                        <?= htmlspecialchars(
                            $_SESSION["nombre"] ?? "Estudiante"
                        ) ?>

                    </h5>


                    <h6 class="text-muted">

                        Curso:

                        <?= htmlspecialchars(
                            $curso
                        ) ?>

                    </h6>

                </div>


                <!-- =================================================
                     SELECTOR DE PERÍODO
                ================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            📅 Consultar período académico

                        </h5>

                    </div>


                    <div class="card-body">

                        <form
                            method="GET"
                            action="desempeno.php"
                            class="row align-items-end"
                        >


                            <div class="col-md-7">

                                <label
                                    for="periodo_id"
                                    class="form-label"
                                >

                                    Selecciona el período
                                    que deseas consultar

                                </label>


                                <select
                                    name="periodo_id"
                                    id="periodo_id"
                                    class="form-select"
                                    required
                                >

                                    <?php

                                    if ($periodos):

                                        while (
                                            $periodo =
                                            $periodos->fetch_assoc()
                                        ):

                                    ?>

                                        <option
                                            value="<?= (int) $periodo["id"] ?>"
                                            <?= (
                                                (int) $periodo["id"]
                                                === $periodo_id
                                            )
                                                ? "selected"
                                                : ""
                                            ?>
                                        >

                                            <?= htmlspecialchars(
                                                $periodo["nombre"]
                                            ) ?>

                                        </option>

                                    <?php

                                        endwhile;

                                    endif;

                                    ?>

                                </select>

                            </div>


                            <div class="col-md-3 mt-3 mt-md-0">

                                <button
                                    type="submit"
                                    class="btn btn-primary w-100"
                                >

                                    🔎 Ver período

                                </button>

                            </div>


                        </form>


                        <div class="alert alert-info mt-3 mb-0">

                            Actualmente estás viendo:

                            <strong>

                                <?= htmlspecialchars(
                                    $periodo_nombre
                                ) ?>

                            </strong>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     LEYENDA
                ================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            Semáforo de desempeño

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="d-flex flex-wrap gap-3">


                            <span
                                class="badge p-3"
                                style="
                                    background-color:#dc3545;
                                    font-size:14px;
                                "
                            >

                                🔴 Bajo

                            </span>


                            <span
                                class="badge p-3 text-dark"
                                style="
                                    background-color:#ffc107;
                                    font-size:14px;
                                "
                            >

                                🟡 Básico

                            </span>


                            <span
                                class="badge p-3"
                                style="
                                    background-color:#198754;
                                    font-size:14px;
                                "
                            >

                                🟢 Alto

                            </span>


                        </div>

                    </div>

                </div>


                <!-- =================================================
                     TABLA DE DESEMPEÑO
                ================================================== -->

                <div class="card shadow">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            Desempeño por materia -

                            <?= htmlspecialchars(
                                $periodo_nombre
                            ) ?>

                        </h5>

                    </div>


                    <div class="card-body">

                        <?php if (
                            $resultado->num_rows > 0
                        ): ?>


                            <div class="table-responsive">

                                <table
                                    class="table table-bordered table-striped align-middle"
                                >

                                    <thead>

                                        <tr>

                                            <th>
                                                #
                                            </th>

                                            <th>
                                                Materia
                                            </th>

                                            <th class="text-center">
                                                Desempeño
                                            </th>

                                            <th class="text-center">
                                                Último registro
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php

                                    $contador = 1;


                                    while (
                                        $fila =
                                        $resultado->fetch_assoc()
                                    ):

                                    ?>


                                        <tr>


                                            <!-- NÚMERO -->

                                            <td>

                                                <?= $contador++ ?>

                                            </td>


                                            <!-- MATERIA -->

                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $fila["materia"]
                                                    ) ?>

                                                </strong>

                                            </td>


                                            <!-- DESEMPEÑO -->

                                            <td class="text-center">


                                                <span
                                                    class="badge p-2"
                                                    style="
                                                        background-color:
                                                        <?= htmlspecialchars(
                                                            $fila["color"]
                                                        ) ?>;
                                                    "
                                                >

                                                    <?= htmlspecialchars(
                                                        ucfirst(
                                                            $fila[
                                                                "desempeno"
                                                            ]
                                                        )
                                                    ) ?>

                                                </span>


                                            </td>


                                            <!-- FECHA -->

                                            <td class="text-center">

                                                <?php

                                                if (
                                                    !empty(
                                                        $fila[
                                                            "fecha_registro"
                                                        ]
                                                    )
                                                ) {

                                                    echo htmlspecialchars(
                                                        date(
                                                            "d/m/Y H:i",
                                                            strtotime(
                                                                $fila[
                                                                    "fecha_registro"
                                                                ]
                                                            )
                                                        )
                                                    );

                                                } else {

                                                    echo "Sin fecha";

                                                }

                                                ?>

                                            </td>


                                        </tr>


                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>


                            <div class="alert alert-info mb-0">

                                No tienes registros de desempeño
                                para

                                <strong>

                                    <?= htmlspecialchars(
                                        $periodo_nombre
                                    ) ?>

                                </strong>.

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

include("../includes/footer.php");

?>