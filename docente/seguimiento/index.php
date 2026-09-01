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

$usuario_id = $_SESSION["id"] ?? 0;

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

    $stmt->close();

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

    $stmt->close();

    die("Esta asignación no pertenece al docente.");

}


$carga = $resultado->fetch_assoc();

$stmt->close();


// ======================================================
// OBTENER PERÍODOS HABILITADOS
// ======================================================

$periodos = $conexion->query("
    SELECT
        id,
        nombre,
        habilitado
    FROM periodos
    WHERE habilitado = 1
    ORDER BY id ASC
");


// ======================================================
// DETERMINAR PERÍODO SELECCIONADO
// ======================================================

$periodo_id = isset($_GET["periodo_id"])
    ? (int) $_GET["periodo_id"]
    : 0;


// ======================================================
// VARIABLE PARA SABER SI EL PERÍODO
// SOLICITADO ESTÁ CERRADO
// ======================================================

$periodo_bloqueado = false;

$periodo_actual = null;


// ======================================================
// SI EL USUARIO ESCRIBIÓ UN PERÍODO
// ======================================================

if ($periodo_id > 0) {

    $stmtPeriodo = $conexion->prepare("
        SELECT
            id,
            nombre,
            habilitado
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

        $periodo_bloqueado = true;

    } else {

        $periodoSolicitado =
            $resultadoPeriodo->fetch_assoc();


        if (
            (int) $periodoSolicitado["habilitado"] !== 1
        ) {

            // ==========================================
            // EL PERÍODO EXISTE PERO ESTÁ CERRADO
            // ==========================================

            $periodo_bloqueado = true;

        } else {

            $periodo_actual =
                $periodoSolicitado;

        }

    }


    $stmtPeriodo->close();

}


// ======================================================
// SI NO SE SELECCIONÓ PERÍODO,
// USAR EL PRIMER HABILITADO
// ======================================================

if (
    $periodo_id <= 0 &&
    $periodos &&
    $periodos->num_rows > 0
) {

    $periodos->data_seek(0);

    $primerPeriodo =
        $periodos->fetch_assoc();


    $periodo_id =
        (int) $primerPeriodo["id"];


    $periodo_actual =
        $primerPeriodo;

}


// ======================================================
// GUARDAR DESEMPEÑO
// ======================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {


    $estudiante_id =
        isset($_POST["estudiante_id"])
            ? (int) $_POST["estudiante_id"]
            : 0;


    $color_id =
        isset($_POST["color_id"])
            ? (int) $_POST["color_id"]
            : 0;


    $periodo_post =
        isset($_POST["periodo_id"])
            ? (int) $_POST["periodo_id"]
            : 0;


    // ==================================================
    // VERIFICAR PERÍODO NUEVAMENTE
    // ==================================================

    $periodo_valido = false;


    if ($periodo_post > 0) {

        $verificar = $conexion->prepare("
            SELECT id
            FROM periodos
            WHERE id = ?
            AND habilitado = 1
            LIMIT 1
        ");

        $verificar->bind_param(
            "i",
            $periodo_post
        );

        $verificar->execute();

        $resultadoVerificar =
            $verificar->get_result();


        if (
            $resultadoVerificar->num_rows === 1
        ) {

            $periodo_valido = true;

            $periodo_id = $periodo_post;

        }

        $verificar->close();

    }


    // ==================================================
    // BLOQUEAR SI ESTÁ DESHABILITADO
    // ==================================================

    if (!$periodo_valido) {

        header(
            "Location: index.php"
            . "?id=" . $carga_id
            . "&error="
            . urlencode(
                "El período está deshabilitado para los docentes."
            )
        );

        exit();

    }


    // ==================================================
    // VALIDAR ESTUDIANTE Y COLOR
    // ==================================================

    if (
        $estudiante_id <= 0 ||
        !in_array($color_id, [1, 2, 3])
    ) {

        header(
            "Location: index.php"
            . "?id=" . $carga_id
            . "&periodo_id=" . $periodo_id
            . "&error="
            . urlencode(
                "Datos de seguimiento inválidos."
            )
        );

        exit();

    }


    // ==================================================
    // VERIFICAR ESTUDIANTE
    // ==================================================

    $verificar_estudiante = $conexion->prepare("
        SELECT id
        FROM estudiantes
        WHERE id = ?
        AND curso_id = ?
        AND estado = 'Activo'
        LIMIT 1
    ");

    $verificar_estudiante->bind_param(
        "ii",
        $estudiante_id,
        $carga["curso_id"]
    );

    $verificar_estudiante->execute();

    $resultadoEstudiante =
        $verificar_estudiante->get_result();


    if (
        $resultadoEstudiante->num_rows === 0
    ) {

        $verificar_estudiante->close();

        header(
            "Location: index.php"
            . "?id=" . $carga_id
            . "&periodo_id=" . $periodo_id
            . "&error="
            . urlencode(
                "El estudiante no pertenece a este curso."
            )
        );

        exit();

    }

    $verificar_estudiante->close();


    // ==================================================
    // BUSCAR DESEMPEÑO EXISTENTE
    // ==================================================

    $buscar = $conexion->prepare("
        SELECT id
        FROM desempeno_estudiantes
        WHERE estudiante_id = ?
        AND carga_academica_id = ?
        AND periodo_id = ?
        LIMIT 1
    ");

    $buscar->bind_param(
        "iii",
        $estudiante_id,
        $carga_id,
        $periodo_id
    );

    $buscar->execute();

    $resultadoBuscar =
        $buscar->get_result();


    // ==================================================
    // ACTUALIZAR
    // ==================================================

    if ($resultadoBuscar->num_rows > 0) {

        $registro =
            $resultadoBuscar->fetch_assoc();

        $registro_id =
            (int) $registro["id"];


        $actualizar = $conexion->prepare("
            UPDATE desempeno_estudiantes

            SET
                color_id = ?,
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


        // ==================================================
        // INSERTAR
        // ==================================================

        $insertar = $conexion->prepare("
            INSERT INTO desempeno_estudiantes
            (
                estudiante_id,
                carga_academica_id,
                periodo_id,
                color_id
            )

            VALUES (?, ?, ?, ?)
        ");

        $insertar->bind_param(
            "iiii",
            $estudiante_id,
            $carga_id,
            $periodo_id,
            $color_id
        );

        $insertar->execute();

        $insertar->close();

    }


    $buscar->close();


    // ==================================================
    // REGRESAR
    // ==================================================

    header(
        "Location: index.php"
        . "?id=" . $carga_id
        . "&periodo_id=" . $periodo_id
        . "&guardado=1"
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
// OBTENER ESTUDIANTES
// ======================================================

$estudiantes = false;


if (
    $periodo_id > 0 &&
    $periodo_actual &&
    !$periodo_bloqueado
) {

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

            AND de.periodo_id = ?

        LEFT JOIN colores_desempeno cd
            ON de.color_id = cd.id

        WHERE e.curso_id = ?
        AND e.estado = 'Activo'

        ORDER BY
            u.apellidos ASC,
            u.nombres ASC
    ");

    $stmt->bind_param(
        "iii",
        $carga_id,
        $periodo_id,
        $carga["curso_id"]
    );

    $stmt->execute();

    $estudiantes =
        $stmt->get_result();

}


// ======================================================
// CABECERA
// ======================================================

include("../../includes/header.php");

include("../../includes/navbar.php");

?>


<div class="container-fluid">

    <div class="row">


        <!-- ==================================================
             SIDEBAR
        =================================================== -->

        <div class="col-md-2 p-0">

            <?php

            include("../../includes/sidebar.php");

            ?>

        </div>


        <!-- ==================================================
             CONTENIDO
        =================================================== -->

        <div class="col-md-10">

            <div class="container mt-4 mb-5">


                <!-- ==================================================
                     ENCABEZADO
                =================================================== -->

                <div class="d-flex justify-content-between
                            align-items-center mb-4">

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


                        <p class="text-muted mb-0">

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

                        ← Volver

                    </a>

                </div>


                <!-- ==================================================
                     MENSAJES
                =================================================== -->

                <?php if (
                    isset($_GET["guardado"])
                ): ?>

                    <div class="alert alert-success">

                        ✅ El desempeño fue guardado correctamente.

                    </div>

                <?php endif; ?>


                <?php if (
                    isset($_GET["error"])
                ): ?>

                    <div class="alert alert-danger">

                        <?= htmlspecialchars(
                            $_GET["error"]
                        ) ?>

                    </div>

                <?php endif; ?>


                <!-- ==================================================
                     PERÍODO BLOQUEADO
                =================================================== -->

                <?php if (
                    $periodo_bloqueado
                ): ?>

                    <div class="alert alert-danger shadow-sm">

                        <h5 class="alert-heading">

                            🔒 Período no disponible

                        </h5>

                        <p class="mb-0">

                            Este período está deshabilitado
                            por el coordinador.

                            No puedes registrar ni modificar
                            desempeños mientras permanezca cerrado.

                        </p>

                    </div>

                <?php endif; ?>


                <!-- ==================================================
                     SELECTOR DE PERÍODO
                =================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            📅 Período académico

                        </h5>

                    </div>


                    <div class="card-body">


                        <?php if (
                            $periodos &&
                            $periodos->num_rows > 0
                        ): ?>


                            <form
                                method="GET"
                                action="index.php"
                                class="row align-items-end"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $carga_id ?>"
                                >


                                <div class="col-md-8">

                                    <label
                                        for="periodo_id"
                                        class="form-label"
                                    >

                                        Selecciona el período
                                        habilitado

                                    </label>


                                    <select
                                        name="periodo_id"
                                        id="periodo_id"
                                        class="form-select"
                                        required
                                    >

                                        <?php

                                        $periodos->data_seek(0);

                                        while (
                                            $periodo =
                                            $periodos->fetch_assoc()
                                        ):

                                            $idPeriodo =
                                                (int) $periodo["id"];

                                        ?>

                                            <option
                                                value="<?= $idPeriodo ?>"
                                                <?= (
                                                    $idPeriodo ===
                                                    $periodo_id
                                                )
                                                    ? "selected"
                                                    : ""
                                                ?>
                                            >

                                                <?= htmlspecialchars(
                                                    $periodo["nombre"]
                                                ) ?>

                                                — 🟢 Habilitado

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>


                                <div
                                    class="col-md-4 mt-3 mt-md-0"
                                >

                                    <button
                                        type="submit"
                                        class="btn btn-primary w-100"
                                    >

                                        🔎 Ver período

                                    </button>

                                </div>

                            </form>


                        <?php else: ?>


                            <div class="alert alert-warning mb-0">

                                ⚠️ Actualmente no hay períodos
                                habilitados para los docentes.

                                <br><br>

                                El coordinador debe habilitar
                                un período para poder registrar
                                desempeños.

                            </div>


                        <?php endif; ?>


                    </div>

                </div>


                <!-- ==================================================
                     TABLA DE ESTUDIANTES
                =================================================== -->

                <?php if (
                    $periodo_actual &&
                    !$periodo_bloqueado &&
                    $estudiantes
                ): ?>


                    <div class="card shadow">

                        <div
                            class="card-header bg-success text-white"
                        >

                            <h5 class="mb-0">

                                Seguimiento de estudiantes -

                                <?= htmlspecialchars(
                                    $periodo_actual["nombre"]
                                ) ?>

                            </h5>

                        </div>


                        <div class="card-body">


                            <?php if (
                                $estudiantes->num_rows > 0
                            ): ?>


                                <?php while (
                                    $estudiante =
                                    $estudiantes->fetch_assoc()
                                ): ?>

                                    <?php

                                    $color_actual =
                                        (int) (
                                            $estudiante[
                                                "color_id"
                                            ] ?? 0
                                        );

                                    ?>


                                    <div
                                        class="card mb-3 border"
                                    >

                                        <div
                                            class="card-body"
                                        >

                                            <div
                                                class="row
                                                align-items-center"
                                            >


                                                <div
                                                    class="col-md-5"
                                                >

                                                    <strong>

                                                        <?= htmlspecialchars(
                                                            $estudiante[
                                                                "apellidos"
                                                            ]
                                                        ) ?>

                                                        ,

                                                        <?= htmlspecialchars(
                                                            $estudiante[
                                                                "nombres"
                                                            ]
                                                        ) ?>

                                                    </strong>


                                                    <br>


                                                    <small
                                                        class="text-muted"
                                                    >

                                                        Documento:

                                                        <?= htmlspecialchars(
                                                            $estudiante[
                                                                "documento"
                                                            ]
                                                        ) ?>

                                                    </small>

                                                </div>


                                                <div
                                                    class="col-md-7"
                                                >

                                                    <form
                                                        method="POST"
                                                        action="index.php?id=<?= $carga_id ?>"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="periodo_id"
                                                            value="<?= $periodo_id ?>"
                                                        >

                                                        <input
                                                            type="hidden"
                                                            name="estudiante_id"
                                                            value="<?= (int) $estudiante["estudiante_id"] ?>"
                                                        >


                                                        <div
                                                            class="d-flex
                                                            align-items-center
                                                            gap-2
                                                            flex-wrap"
                                                        >


                                                            <button
                                                                type="submit"
                                                                name="color_id"
                                                                value="1"
                                                                class="btn <?= ($color_actual === 1) ? 'btn-danger' : 'btn-outline-danger' ?>"
                                                            >

                                                                🔴 Bajo

                                                            </button>


                                                            <button
                                                                type="submit"
                                                                name="color_id"
                                                                value="2"
                                                                class="btn <?= ($color_actual === 2) ? 'btn-warning' : 'btn-outline-warning' ?>"
                                                            >

                                                                🟡 Básico

                                                            </button>


                                                            <button
                                                                type="submit"
                                                                name="color_id"
                                                                value="3"
                                                                class="btn <?= ($color_actual === 3) ? 'btn-success' : 'btn-outline-success' ?>"
                                                            >

                                                                🟢 Alto

                                                            </button>


                                                        </div>

                                                    </form>

                                                </div>

                                            </div>

                                        </div>

                                    </div>


                                <?php endwhile; ?>


                            <?php else: ?>


                                <div class="alert alert-info mb-0">

                                    No hay estudiantes activos
                                    registrados en este curso.

                                </div>


                            <?php endif; ?>


                        </div>

                    </div>


                <?php endif; ?>


            </div>

        </div>

    </div>

</div>


<?php

include("../../includes/footer.php");

?>