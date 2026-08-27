<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");


// =====================================================
// PROCESAR CAMBIO DE ESTADO
// =====================================================

$mensaje = "";
$tipo_mensaje = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $periodo_id = isset($_POST["periodo_id"])
        ? (int) $_POST["periodo_id"]
        : 0;

    $nuevo_estado = isset($_POST["nuevo_estado"])
        ? (int) $_POST["nuevo_estado"]
        : 0;


    // =================================================
    // VALIDAR ID
    // =================================================

    if ($periodo_id <= 0) {

        $mensaje = "El período seleccionado no es válido.";
        $tipo_mensaje = "danger";

    } elseif ($nuevo_estado !== 0 && $nuevo_estado !== 1) {

        $mensaje = "El estado seleccionado no es válido.";
        $tipo_mensaje = "danger";

    } else {

        // =================================================
        // VERIFICAR QUE EL PERÍODO EXISTA
        // =================================================

        $stmt = $conexion->prepare("
            SELECT
                id,
                nombre
            FROM periodos
            WHERE id = ?
            LIMIT 1
        ");


        if (!$stmt) {

            $mensaje =
                "No se pudo consultar el período.";

            $tipo_mensaje = "danger";

        } else {

            $stmt->bind_param(
                "i",
                $periodo_id
            );

            $stmt->execute();

            $resultado =
                $stmt->get_result();


            if ($resultado->num_rows === 0) {

                $mensaje =
                    "El período no existe.";

                $tipo_mensaje = "danger";

                $stmt->close();

            } else {

                $periodo =
                    $resultado->fetch_assoc();

                $stmt->close();


                // =================================================
                // ACTUALIZAR ESTADO
                // =================================================

                $stmtUpdate = $conexion->prepare("
                    UPDATE periodos
                    SET habilitado = ?
                    WHERE id = ?
                ");


                if (!$stmtUpdate) {

                    $mensaje =
                        "No se pudo actualizar el período.";

                    $tipo_mensaje = "danger";

                } else {

                    $stmtUpdate->bind_param(
                        "ii",
                        $nuevo_estado,
                        $periodo_id
                    );


                    if ($stmtUpdate->execute()) {

                        if ($nuevo_estado === 1) {

                            $mensaje =
                                "El " .
                                htmlspecialchars(
                                    $periodo["nombre"]
                                ) .
                                " fue habilitado correctamente.";

                        } else {

                            $mensaje =
                                "El " .
                                htmlspecialchars(
                                    $periodo["nombre"]
                                ) .
                                " fue deshabilitado correctamente.";

                        }


                        $tipo_mensaje = "success";

                    } else {

                        $mensaje =
                            "No se pudo cambiar el estado del período.";

                        $tipo_mensaje = "danger";

                    }


                    $stmtUpdate->close();

                }

            }

        }

    }

}


// =====================================================
// OBTENER PERÍODOS
// =====================================================

$resultadoPeriodos = $conexion->query("
    SELECT
        id,
        nombre,
        habilitado
    FROM periodos
    ORDER BY id ASC
");


// =====================================================
// HEADER
// =====================================================

include("../../includes/header.php");

include("../../includes/navbar.php");

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <div class="col-md-2 p-0">

            <?php

            include("../../includes/sidebar.php");

            ?>

        </div>


        <!-- =====================================================
             CONTENIDO PRINCIPAL
        ====================================================== -->

        <div class="col-md-10">

            <div class="container mt-4 mb-5">


                <!-- =================================================
                     ENCABEZADO
                ================================================== -->

                <div class="d-flex justify-content-between
                            align-items-center mb-4">

                    <div>

                        <h2 class="mb-1">

                            Administración de períodos

                        </h2>


                        <p class="text-muted mb-0">

                            Controla qué períodos están disponibles
                            para que los docentes registren
                            desempeños.

                        </p>

                    </div>


                    <a
                        href="../index.php"
                        class="btn btn-dark"
                    >

                        ← Volver al dashboard

                    </a>

                </div>


                <!-- =================================================
                     MENSAJE
                ================================================== -->

                <?php if ($mensaje !== ""): ?>

                    <div
                        class="alert alert-<?= $tipo_mensaje ?>
                        alert-dismissible fade show"
                        role="alert"
                    >

                        <?= $mensaje ?>


                        <button
                            type="button"
                            class="btn-close"
                            data-bs-dismiss="alert"
                            aria-label="Cerrar"
                        ></button>

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     INFORMACIÓN
                ================================================== -->

                <div class="alert alert-info shadow-sm">

                    <strong>

                        ℹ️ ¿Cómo funciona?

                    </strong>

                    <p class="mb-0 mt-2">

                        Un período habilitado permite que los docentes
                        registren y modifiquen desempeños.

                        Un período deshabilitado queda cerrado para
                        los docentes.

                        Los estudiantes podrán seguir consultando
                        sus resultados registrados.

                    </p>

                </div>


                <!-- =================================================
                     LISTA DE PERÍODOS
                ================================================== -->

                <div class="card shadow">

                    <div
                        class="card-header bg-primary text-white"
                    >

                        <h4 class="mb-0">

                            Períodos académicos

                        </h4>

                    </div>


                    <div class="card-body">


                        <?php

                        if (
                            $resultadoPeriodos &&
                            $resultadoPeriodos->num_rows > 0
                        ):

                        ?>


                            <div class="table-responsive">

                                <table
                                    class="table table-bordered
                                    table-hover align-middle mb-0"
                                >


                                    <thead class="table-light">

                                        <tr>

                                            <th
                                                style="width:80px;"
                                            >

                                                #

                                            </th>


                                            <th>

                                                Período

                                            </th>


                                            <th
                                                class="text-center"
                                            >

                                                Estado

                                            </th>


                                            <th
                                                class="text-center"
                                            >

                                                Acción

                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php

                                    $contador = 1;


                                    while (
                                        $periodo =
                                        $resultadoPeriodos->fetch_assoc()
                                    ):

                                        $habilitado =
                                            (int)
                                            $periodo["habilitado"];

                                    ?>


                                        <tr>


                                            <!-- =================================
                                                 NUMERO
                                            ================================== -->

                                            <td>

                                                <?= $contador++ ?>

                                            </td>


                                            <!-- =================================
                                                 NOMBRE
                                            ================================== -->

                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $periodo["nombre"]
                                                    ) ?>

                                                </strong>

                                            </td>


                                            <!-- =================================
                                                 ESTADO
                                            ================================== -->

                                            <td class="text-center">


                                                <?php if (
                                                    $habilitado === 1
                                                ): ?>


                                                    <span
                                                        class="badge bg-success
                                                        p-2"
                                                    >

                                                        🟢 Habilitado

                                                    </span>


                                                <?php else: ?>


                                                    <span
                                                        class="badge bg-danger
                                                        p-2"
                                                    >

                                                        🔴 Deshabilitado

                                                    </span>


                                                <?php endif; ?>


                                            </td>


                                            <!-- =================================
                                                 ACCIÓN
                                            ================================== -->

                                            <td class="text-center">


                                                <form
                                                    method="POST"
                                                    action="index.php"
                                                    style="display:inline;"
                                                >


                                                    <input
                                                        type="hidden"
                                                        name="periodo_id"
                                                        value="<?= (int) $periodo["id"] ?>"
                                                    >


                                                    <?php if (
                                                        $habilitado === 1
                                                    ): ?>


                                                        <input
                                                            type="hidden"
                                                            name="nuevo_estado"
                                                            value="0"
                                                        >


                                                        <button
                                                            type="submit"
                                                            class="btn btn-danger btn-sm"
                                                            onclick="
                                                                return confirm(
                                                                    '¿Seguro que deseas deshabilitar este período?'
                                                                );
                                                            "
                                                        >

                                                            🔒 Deshabilitar

                                                        </button>


                                                    <?php else: ?>


                                                        <input
                                                            type="hidden"
                                                            name="nuevo_estado"
                                                            value="1"
                                                        >


                                                        <button
                                                            type="submit"
                                                            class="btn btn-success btn-sm"
                                                            onclick="
                                                                return confirm(
                                                                    '¿Seguro que deseas habilitar este período?'
                                                                );
                                                            "
                                                        >

                                                            🔓 Habilitar

                                                        </button>


                                                    <?php endif; ?>


                                                </form>


                                            </td>


                                        </tr>


                                    <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>


                            <div class="alert alert-warning mb-0">

                                No hay períodos académicos registrados.

                            </div>


                        <?php endif; ?>


                    </div>

                </div>


                <!-- =================================================
                     BOTÓN INFERIOR
                ================================================== -->

                <div class="mt-4">

                    <a
                        href="../index.php"
                        class="btn btn-secondary"
                    >

                        ← Regresar al dashboard

                    </a>

                </div>


            </div>

        </div>

    </div>

</div>


<?php

include("../../includes/footer.php");

?>