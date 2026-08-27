<?php

session_start();

require_once("../config/config.php");
require_once("../config/seguridad/estudiante.php");


// =====================================================
// DATOS DEL USUARIO
// =====================================================

$nombre = $_SESSION["nombre"] ?? "Estudiante";

$usuario_id = $_SESSION["usuario_id"]
    ?? $_SESSION["id"]
    ?? $_SESSION["user_id"]
    ?? 0;


// =====================================================
// VALORES INICIALES
// =====================================================

$seguimientos = 0;
$bajo = 0;
$basico = 0;
$alto = 0;


// =====================================================
// PERÍODO SELECCIONADO
// =====================================================

// Por defecto se muestra el Período 1

$periodo_id = isset($_GET["periodo_id"])
    ? (int) $_GET["periodo_id"]
    : 1;


// =====================================================
// VERIFICAR QUE EL PERÍODO EXISTA
// =====================================================

$stmtPeriodo = $conexion->prepare("
    SELECT
        id,
        nombre
    FROM periodos
    WHERE id = ?
    LIMIT 1
");

if ($stmtPeriodo) {

    $stmtPeriodo->bind_param(
        "i",
        $periodo_id
    );

    $stmtPeriodo->execute();

    $resultadoPeriodo =
        $stmtPeriodo->get_result();


    // Si el período no existe,
    // regresar al Período 1

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


        if ($stmtPeriodo) {

            $stmtPeriodo->bind_param(
                "i",
                $periodo_id
            );

            $stmtPeriodo->execute();

            $resultadoPeriodo =
                $stmtPeriodo->get_result();

        }

    }


    if (
        isset($resultadoPeriodo) &&
        $resultadoPeriodo->num_rows > 0
    ) {

        $periodo =
            $resultadoPeriodo->fetch_assoc();

    } else {

        $periodo = [
            "id" => 1,
            "nombre" => "Periodo 1"
        ];

    }


    $periodo_nombre =
        $periodo["nombre"] ?? "Periodo 1";


    $stmtPeriodo->close();

} else {

    $periodo_nombre = "Periodo 1";

}


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
// BUSCAR ESTUDIANTE
// =====================================================

if ($usuario_id > 0) {

    $stmt = $conexion->prepare("
        SELECT id
        FROM estudiantes
        WHERE usuario_id = ?
        LIMIT 1
    ");


    if ($stmt) {

        $stmt->bind_param(
            "i",
            $usuario_id
        );

        $stmt->execute();

        $resultado =
            $stmt->get_result();

        $estudiante =
            $resultado->fetch_assoc();

        $stmt->close();


        // =================================================
        // BUSCAR DESEMPEÑO DEL PERÍODO SELECCIONADO
        // =================================================

        if ($estudiante) {

            $estudiante_id =
                (int) $estudiante["id"];


            $stmt = $conexion->prepare("
                SELECT

                    COUNT(*) AS total,

                    SUM(
                        CASE
                            WHEN color_id = 1
                            THEN 1
                            ELSE 0
                        END
                    ) AS bajo,

                    SUM(
                        CASE
                            WHEN color_id = 2
                            THEN 1
                            ELSE 0
                        END
                    ) AS basico,

                    SUM(
                        CASE
                            WHEN color_id = 3
                            THEN 1
                            ELSE 0
                        END
                    ) AS alto

                FROM desempeno_estudiantes

                WHERE estudiante_id = ?

                AND periodo_id = ?
            ");


            if ($stmt) {

                $stmt->bind_param(
                    "ii",
                    $estudiante_id,
                    $periodo_id
                );

                $stmt->execute();

                $resultado =
                    $stmt->get_result();

                $datos =
                    $resultado->fetch_assoc();

                $stmt->close();


                if ($datos) {

                    $seguimientos =
                        (int) (
                            $datos["total"] ?? 0
                        );

                    $bajo =
                        (int) (
                            $datos["bajo"] ?? 0
                        );

                    $basico =
                        (int) (
                            $datos["basico"] ?? 0
                        );

                    $alto =
                        (int) (
                            $datos["alto"] ?? 0
                        );

                }

            }

        }

    }

}


// =====================================================
// HEADER
// =====================================================

include("../includes/header.php");

include("../includes/navbar.php");

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <div class="col-md-2 p-0">

            <?php

            include("../includes/sidebar.php");

            ?>

        </div>


        <!-- =====================================================
             CONTENIDO PRINCIPAL
        ====================================================== -->

        <div class="col-md-10">

            <div class="container mt-4">


                <!-- =================================================
                     ENCABEZADO
                ================================================== -->

                <div class="mb-4">

                    <h2>

                        Panel del estudiante

                    </h2>


                    <h4>

                        Bienvenido,

                        <?= htmlspecialchars(
                            $nombre
                        ) ?>

                    </h4>

                </div>


                <hr>


                <!-- =================================================
                     SELECTOR DE PERÍODO
                ================================================== -->

                <div class="card shadow border-0 mb-4">

                    <div class="card-body p-4">

                        <div class="row align-items-end">


                            <div class="col-md-7">

                                <label
                                    for="periodo_id"
                                    class="form-label"
                                >

                                    📅 Selecciona el período
                                    académico que deseas consultar

                                </label>


                                <form
                                    method="GET"
                                    action="index.php"
                                >

                                    <div class="input-group">

                                        <select
                                            name="periodo_id"
                                            id="periodo_id"
                                            class="form-select"
                                            required
                                        >

                                            <?php

                                            if ($periodos):

                                                while (
                                                    $p =
                                                    $periodos->fetch_assoc()
                                                ):

                                            ?>

                                                <option
                                                    value="<?= (int) $p["id"] ?>"
                                                    <?= (
                                                        (int) $p["id"]
                                                        === $periodo_id
                                                    )
                                                        ? "selected"
                                                        : ""
                                                    ?>
                                                >

                                                    <?= htmlspecialchars(
                                                        $p["nombre"]
                                                    ) ?>

                                                </option>

                                            <?php

                                                endwhile;

                                            endif;

                                            ?>

                                        </select>


                                        <button
                                            type="submit"
                                            class="btn btn-primary"
                                        >

                                            🔎 Ver período

                                        </button>

                                    </div>

                                </form>

                            </div>


                            <div class="col-md-5 mt-3 mt-md-0">

                                <div
                                    class="alert alert-info mb-0"
                                >

                                    Estás consultando:

                                    <strong>

                                        <?= htmlspecialchars(
                                            $periodo_nombre
                                        ) ?>

                                    </strong>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- =================================================
                     TARJETAS DE RESUMEN
                ================================================== -->

                <div class="row">


                    <!-- SEGUIMIENTOS -->

                    <div class="col-md-3 mb-4">

                        <div class="card shadow h-100">

                            <div class="card-body">

                                <div
                                    style="
                                        font-size:45px;
                                        margin-bottom:10px;
                                    "
                                >

                                    📚

                                </div>


                                <h6 class="text-muted">

                                    Seguimientos

                                </h6>


                                <h2 class="fw-bold">

                                    <?= $seguimientos ?>

                                </h2>


                                <small class="text-muted">

                                    Registros de
                                    <?= htmlspecialchars(
                                        $periodo_nombre
                                    ) ?>

                                </small>

                            </div>

                        </div>

                    </div>


                    <!-- BAJO -->

                    <div class="col-md-3 mb-4">

                        <div class="card shadow h-100">

                            <div class="card-body">

                                <div
                                    style="
                                        width:45px;
                                        height:45px;
                                        background:#dc3545;
                                        border-radius:50%;
                                        border:3px solid #000;
                                        margin-bottom:10px;
                                    "
                                ></div>


                                <h6 class="text-muted">

                                    Desempeño bajo

                                </h6>


                                <h2
                                    class="fw-bold text-danger"
                                >

                                    <?= $bajo ?>

                                </h2>


                                <small class="text-muted">

                                    En
                                    <?= htmlspecialchars(
                                        $periodo_nombre
                                    ) ?>

                                </small>

                            </div>

                        </div>

                    </div>


                    <!-- BÁSICO -->

                    <div class="col-md-3 mb-4">

                        <div class="card shadow h-100">

                            <div class="card-body">

                                <div
                                    style="
                                        width:45px;
                                        height:45px;
                                        background:#ffc107;
                                        border-radius:50%;
                                        border:3px solid #000;
                                        margin-bottom:10px;
                                    "
                                ></div>


                                <h6 class="text-muted">

                                    Desempeño básico

                                </h6>


                                <h2
                                    class="fw-bold"
                                    style="color:#ffc107;"
                                >

                                    <?= $basico ?>

                                </h2>


                                <small class="text-muted">

                                    En
                                    <?= htmlspecialchars(
                                        $periodo_nombre
                                    ) ?>

                                </small>

                            </div>

                        </div>

                    </div>


                    <!-- ALTO -->

                    <div class="col-md-3 mb-4">

                        <div class="card shadow h-100">

                            <div class="card-body">

                                <div
                                    style="
                                        width:45px;
                                        height:45px;
                                        background:#198754;
                                        border-radius:50%;
                                        border:3px solid #000;
                                        margin-bottom:10px;
                                    "
                                ></div>


                                <h6 class="text-muted">

                                    Desempeño alto

                                </h6>


                                <h2
                                    class="fw-bold text-success"
                                >

                                    <?= $alto ?>

                                </h2>


                                <small class="text-muted">

                                    En
                                    <?= htmlspecialchars(
                                        $periodo_nombre
                                    ) ?>

                                </small>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     GRÁFICA DE DESEMPEÑO
                ================================================== -->

                <div class="card shadow border-0 mb-4">

                    <div class="card-body p-4">

                        <div class="row align-items-center">


                            <!-- INFORMACIÓN -->

                            <div class="col-md-6">

                                <h4 class="fw-bold">

                                    📊 Mi desempeño

                                </h4>


                                <p class="text-muted">

                                    Distribución de tu desempeño
                                    académico en:

                                    <strong>

                                        <?= htmlspecialchars(
                                            $periodo_nombre
                                        ) ?>

                                    </strong>

                                </p>


                                <div class="mt-3">


                                    <p class="mb-2">

                                        🔴 Bajo:

                                        <strong>

                                            <?= $bajo ?>

                                        </strong>

                                    </p>


                                    <p class="mb-2">

                                        🟡 Básico:

                                        <strong>

                                            <?= $basico ?>

                                        </strong>

                                    </p>


                                    <p class="mb-2">

                                        🟢 Alto:

                                        <strong>

                                            <?= $alto ?>

                                        </strong>

                                    </p>


                                </div>

                            </div>


                            <!-- GRÁFICA -->

                            <div class="col-md-6">

                                <div
                                    style="
                                        max-width:350px;
                                        margin:auto;
                                    "
                                >

                                    <canvas
                                        id="graficaDesempeno"
                                        data-bajo="<?= (int) $bajo ?>"
                                        data-basico="<?= (int) $basico ?>"
                                        data-alto="<?= (int) $alto ?>"
                                    ></canvas>

                                </div>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- =================================================
                     MI DESEMPEÑO ACADÉMICO
                ================================================== -->

                <div class="card shadow border-0 mb-4">

                    <div class="card-body p-4">

                        <div class="row align-items-center">


                            <div class="col-md-8">

                                <small class="text-muted">

                                    SEGUIMIENTO ACADÉMICO

                                </small>


                                <h2 class="fw-bold mt-2">

                                    Mi desempeño académico

                                </h2>


                                <p class="text-muted">

                                    Consulta el estado de tu desempeño
                                    académico correspondiente al

                                    <strong>

                                        <?= htmlspecialchars(
                                            $periodo_nombre
                                        ) ?>

                                    </strong>

                                    y revisa las materias que
                                    requieren seguimiento.

                                </p>


                                <div class="mt-3">


                                    <span
                                        class="badge bg-danger me-2 p-2"
                                    >

                                        🔴 Bajo:
                                        <?= $bajo ?>

                                    </span>


                                    <span
                                        class="badge bg-warning text-dark me-2 p-2"
                                    >

                                        🟡 Básico:
                                        <?= $basico ?>

                                    </span>


                                    <span
                                        class="badge bg-success p-2"
                                    >

                                        🟢 Alto:
                                        <?= $alto ?>

                                    </span>


                                </div>

                            </div>


                            <div
                                class="col-md-4 text-md-end mt-3 mt-md-0"
                            >

                                <a
                                    href="desempeno.php?periodo_id=<?= $periodo_id ?>"
                                    class="btn btn-primary btn-lg"
                                >

                                    📊 Ver mi desempeño

                                </a>

                            </div>


                        </div>

                    </div>

                </div>


                <!-- =================================================
                     INFORMACIÓN
                ================================================== -->

                <div class="card shadow border-0">

                    <div class="card-body p-4">

                        <h5 class="fw-bold">

                            💡 Información

                        </h5>


                        <p class="text-muted mb-0">

                            Actualmente estás consultando el

                            <strong>

                                <?= htmlspecialchars(
                                    $periodo_nombre
                                ) ?>

                            </strong>.

                            Puedes utilizar el selector de período
                            para consultar tu desempeño académico
                            correspondiente a cualquiera de los
                            períodos disponibles.

                        </p>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<!-- =====================================================
     CHART.JS
====================================================== -->

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {

        const canvas =
            document.getElementById(
                "graficaDesempeno"
            );


        if (!canvas) {

            return;

        }


        const bajo =
            Number(
                canvas.dataset.bajo || 0
            );


        const basico =
            Number(
                canvas.dataset.basico || 0
            );


        const alto =
            Number(
                canvas.dataset.alto || 0
            );


        const total =
            bajo +
            basico +
            alto;


        // Si no existen registros,
        // mostramos un mensaje en lugar
        // de una gráfica vacía.

        if (total === 0) {

            const contenedor =
                canvas.parentElement;

            contenedor.innerHTML = `

                <div
                    class="alert alert-secondary text-center"
                >

                    📊

                    <br>

                    No hay registros de desempeño
                    para este período.

                </div>

            `;

            return;

        }


        new Chart(
            canvas,
            {

                type: "doughnut",


                data: {

                    labels: [

                        "Bajo",
                        "Básico",
                        "Alto"

                    ],


                    datasets: [

                        {

                            data: [

                                bajo,
                                basico,
                                alto

                            ],


                            backgroundColor: [

                                "#dc3545",
                                "#ffc107",
                                "#198754"

                            ],


                            borderColor: "#ffffff",

                            borderWidth: 3

                        }

                    ]

                },


                options: {

                    responsive: true,

                    maintainAspectRatio: true,


                    plugins: {

                        legend: {

                            position: "bottom"

                        },


                        tooltip: {

                            callbacks: {

                                label: function (
                                    context
                                ) {

                                    const valor =
                                        context.raw;


                                    const porcentaje =
                                        (
                                            valor /
                                            total *
                                            100
                                        ).toFixed(1);


                                    return (
                                        " " +
                                        context.label +
                                        ": " +
                                        valor +
                                        " (" +
                                        porcentaje +
                                        "%)"
                                    );

                                }

                            }

                        }

                    }

                }

            }
        );

    }

);

</script>


<?php

include("../includes/footer.php");

?>