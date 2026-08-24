<?php

session_start();

require_once("../config/config.php");
require_once("../config/seguridad/estudiante.php");

include("../includes/header.php");
include("../includes/navbar.php");


/*
|--------------------------------------------------------------------------
| DATOS DEL USUARIO
|--------------------------------------------------------------------------
*/

$nombre = $_SESSION["nombre"] ?? "Estudiante";

$usuario_id = $_SESSION["usuario_id"]
    ?? $_SESSION["id"]
    ?? $_SESSION["user_id"]
    ?? 0;


/*
|--------------------------------------------------------------------------
| VALORES INICIALES
|--------------------------------------------------------------------------
*/

$seguimientos = 0;
$bajo = 0;
$basico = 0;
$alto = 0;


/*
|--------------------------------------------------------------------------
| BUSCAR ESTUDIANTE
|--------------------------------------------------------------------------
*/

if ($usuario_id > 0) {

    $stmt = $conexion->prepare("
        SELECT id
        FROM estudiantes
        WHERE usuario_id = ?
        LIMIT 1
    ");

    if ($stmt) {

        $stmt->bind_param("i", $usuario_id);

        $stmt->execute();

        $resultado = $stmt->get_result();

        $estudiante = $resultado->fetch_assoc();

        $stmt->close();


        /*
        |--------------------------------------------------------------------------
        | BUSCAR DESEMPEÑO
        |--------------------------------------------------------------------------
        */

        if ($estudiante) {

            $estudiante_id = (int) $estudiante["id"];


            $stmt = $conexion->prepare("
                SELECT
                    COUNT(*) AS total,

                    SUM(
                        CASE
                            WHEN color_id = 1 THEN 1
                            ELSE 0
                        END
                    ) AS bajo,

                    SUM(
                        CASE
                            WHEN color_id = 2 THEN 1
                            ELSE 0
                        END
                    ) AS basico,

                    SUM(
                        CASE
                            WHEN color_id = 3 THEN 1
                            ELSE 0
                        END
                    ) AS alto

                FROM desempeno_estudiantes

                WHERE estudiante_id = ?
            ");


            if ($stmt) {

                $stmt->bind_param(
                    "i",
                    $estudiante_id
                );

                $stmt->execute();

                $resultado = $stmt->get_result();

                $datos = $resultado->fetch_assoc();

                $stmt->close();


                if ($datos) {

                    $seguimientos =
                        (int) ($datos["total"] ?? 0);

                    $bajo =
                        (int) ($datos["bajo"] ?? 0);

                    $basico =
                        (int) ($datos["basico"] ?? 0);

                    $alto =
                        (int) ($datos["alto"] ?? 0);
                }
            }
        }
    }
}

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <div class="col-md-2 p-0">

            <?php include("../includes/sidebar.php"); ?>

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
                        <?= htmlspecialchars($nombre) ?>

                    </h4>

                </div>


                <hr>


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

                                    Registros académicos

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

                                <h2 class="fw-bold text-danger">

                                    <?= $bajo ?>

                                </h2>

                                <small class="text-muted">

                                    Necesitan atención

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

                                    En seguimiento

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

                                <h2 class="fw-bold text-success">

                                    <?= $alto ?>

                                </h2>

                                <small class="text-muted">

                                    Buen desempeño

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
                                    académico.

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
                                        data-bajo="<?= $bajo ?>"
                                        data-basico="<?= $basico ?>"
                                        data-alto="<?= $alto ?>"
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
                                    académico y revisa las materias que
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
                                    href="desempeno.php"
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

                            El sistema utiliza un semáforo académico
                            para mostrar tu desempeño. Revisa
                            periódicamente esta sección para conocer
                            tu estado académico.

                        </p>

                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<?php

include("../includes/footer.php");

?>