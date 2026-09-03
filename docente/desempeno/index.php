<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/docente.php");


// ======================================================
// VALIDAR ID DE CARGA
// ======================================================

if (
    !isset($_GET["id"]) ||
    !is_numeric($_GET["id"])
) {

    header("Location: ../index.php");
    exit();

}

$carga_id = (int) $_GET["id"];


// ======================================================
// OBTENER DOCENTE
// ======================================================

$usuario_id = intval($_SESSION["id"] ?? 0);

if ($usuario_id <= 0) {

    header("Location: ../index.php");
    exit();

}


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

    die("No se encontró el docente.");

}


$docente = $resultado->fetch_assoc();

$docente_id = intval($docente["id"]);

$stmt->close();


// ======================================================
// OBTENER CARGA ACADÉMICA
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


if (!$stmt) {

    die("Error preparando la carga académica.");

}


$stmt->bind_param(
    "ii",
    $carga_id,
    $docente_id
);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows === 0) {

    $stmt->close();

    die("Esta carga académica no pertenece al docente.");

}


$carga = $resultado->fetch_assoc();

$stmt->close();


// ======================================================
// PERÍODO SELECCIONADO
// ======================================================

$periodo_id = isset($_GET["periodo_id"])
    ? intval($_GET["periodo_id"])
    : 0;


// ======================================================
// OBTENER TODOS LOS PERÍODOS
// ======================================================

$periodos = [];

$resultadoPeriodos = $conexion->query("
    SELECT
        id,
        nombre,
        habilitado
    FROM periodos
    ORDER BY id ASC
");


if ($resultadoPeriodos) {

    while (
        $periodo = $resultadoPeriodos->fetch_assoc()
    ) {

        $periodos[] = $periodo;

    }

}


// ======================================================
// SI NO HAY PERÍODO,
// BUSCAR EL PRIMER PERÍODO HABILITADO
// ======================================================

if ($periodo_id <= 0) {

    foreach ($periodos as $periodo) {

        if (
            intval($periodo["habilitado"]) === 1
        ) {

            $periodo_id =
                intval($periodo["id"]);

            break;

        }

    }

}


// ======================================================
// OBTENER PERÍODO ACTUAL
// ======================================================

$periodo_actual = null;

$periodo_bloqueado = false;


if ($periodo_id > 0) {

    $stmt = $conexion->prepare("
        SELECT
            id,
            nombre,
            habilitado

        FROM periodos

        WHERE id = ?

        LIMIT 1
    ");

    $stmt->bind_param(
        "i",
        $periodo_id
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();


    if ($resultado->num_rows === 0) {

        $periodo_bloqueado = true;

    } else {

        $periodo_actual =
            $resultado->fetch_assoc();


        if (
            intval($periodo_actual["habilitado"]) !== 1
        ) {

            $periodo_bloqueado = true;

        }

    }

    $stmt->close();

}


// ======================================================
// OBTENER ESTUDIANTES
// ======================================================

$estudiantes = [];

$stmt = $conexion->prepare("
    SELECT
        e.id,
        u.nombres,
        u.apellidos,
        u.documento

    FROM estudiantes e

    INNER JOIN usuarios u
        ON e.usuario_id = u.id

    WHERE e.curso_id = ?
    AND e.estado = 'Activo'

    ORDER BY
        u.apellidos ASC,
        u.nombres ASC
");


if (!$stmt) {

    die(
        "Error preparando consulta de estudiantes."
    );

}


$stmt->bind_param(
    "i",
    $carga["curso_id"]
);

$stmt->execute();

$resultado =
    $stmt->get_result();


while (
    $estudiante = $resultado->fetch_assoc()
) {

    $estudiantes[] =
        $estudiante;

}

$stmt->close();


// ======================================================
// OBTENER DESEMPEÑOS YA GUARDADOS
// ======================================================

$desempenos = [];

if ($periodo_id > 0) {

    $stmt = $conexion->prepare("
        SELECT
            estudiante_id,
            color_id

        FROM desempeno_estudiantes

        WHERE carga_academica_id = ?
        AND periodo_id = ?
    ");


    if ($stmt) {

        $stmt->bind_param(
            "ii",
            $carga_id,
            $periodo_id
        );

        $stmt->execute();

        $resultado =
            $stmt->get_result();


        while (
            $fila = $resultado->fetch_assoc()
        ) {

            $desempenos[
                intval(
                    $fila["estudiante_id"]
                )
            ] =
                intval(
                    $fila["color_id"]
                );

        }

        $stmt->close();

    }

}


// ======================================================
// HEADER
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

                <div
                    class="d-flex
                           justify-content-between
                           align-items-center
                           mb-4"
                >

                    <div>

                        <h2>
                            Desempeño de estudiantes
                        </h2>


                        <p class="mb-1">

                            Curso:

                            <strong>

                                <?= htmlspecialchars(
                                    $carga["curso"]
                                ) ?>

                            </strong>

                        </p>


                        <p class="mb-0">

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
                     ERROR
                =================================================== -->

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
                     ÉXITO
                =================================================== -->

                <?php if (
                    isset($_GET["success"])
                ): ?>

                    <div class="alert alert-success">

                        ✅

                        <?= htmlspecialchars(
                            $_GET["success"]
                        ) ?>

                    </div>

                <?php endif; ?>


                <!-- ==================================================
                     PERÍODO
                =================================================== -->

                <div class="card shadow mb-4">

                    <div
                        class="card-header
                               bg-primary
                               text-white"
                    >

                        <h5 class="mb-0">

                            📅 Período académico

                        </h5>

                    </div>


                    <div class="card-body">


                        <?php

                        $periodos_habilitados =
                            array_filter(
                                $periodos,
                                function ($periodo) {

                                    return
                                        intval(
                                            $periodo["habilitado"]
                                        ) === 1;

                                }
                            );

                        ?>


                        <?php if (
                            count(
                                $periodos_habilitados
                            ) > 0
                        ): ?>


                            <form
                                method="GET"
                                action="index.php"
                            >

                                <input
                                    type="hidden"
                                    name="id"
                                    value="<?= $carga_id ?>"
                                >


                                <div
                                    class="row
                                           align-items-end"
                                >


                                    <div
                                        class="col-md-8"
                                    >

                                        <label
                                            class="form-label"
                                            for="periodo_id"
                                        >

                                            Selecciona un período
                                            habilitado

                                        </label>


                                        <select
                                            name="periodo_id"
                                            id="periodo_id"
                                            class="form-select"
                                            required
                                        >

                                            <?php foreach (
                                                $periodos_habilitados
                                                as $periodo
                                            ): ?>


                                                <?php

                                                $idPeriodo =
                                                    intval(
                                                        $periodo["id"]
                                                    );

                                                ?>


                                                <option
                                                    value="<?= $idPeriodo ?>"

                                                    <?=
                                                        (
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


                                            <?php endforeach; ?>

                                        </select>

                                    </div>


                                    <div
                                        class="col-md-4
                                               mt-3
                                               mt-md-0"
                                    >

                                        <button
                                            type="submit"
                                            class="btn
                                                   btn-primary
                                                   w-100"
                                        >

                                            🔎 Ver período

                                        </button>

                                    </div>


                                </div>

                            </form>


                        <?php else: ?>


                            <div
                                class="alert
                                       alert-warning
                                       mb-0"
                            >

                                ⚠️ No hay períodos
                                habilitados actualmente.

                            </div>


                        <?php endif; ?>


                        <?php if (
                            $periodo_actual &&
                            !$periodo_bloqueado
                        ): ?>

                            <div
                                class="alert
                                       alert-success
                                       mt-3
                                       mb-0"
                            >

                                Actualmente estás trabajando en:

                                <strong>

                                    <?= htmlspecialchars(
                                        $periodo_actual["nombre"]
                                    ) ?>

                                </strong>

                            </div>

                        <?php endif; ?>


                    </div>

                </div>


                <!-- ==================================================
                     PERÍODO BLOQUEADO
                =================================================== -->

                <?php if (
                    $periodo_bloqueado
                ): ?>


                    <div
                        class="alert
                               alert-danger
                               shadow-sm"
                    >

                        <h5>

                            🔒 Período no disponible

                        </h5>


                        <p class="mb-0">

                            Este período está deshabilitado
                            por el coordinador.

                            No puedes registrar ni modificar
                            desempeños en este período.

                        </p>

                    </div>


                <?php endif; ?>


                <!-- ==================================================
                     TABLA DE ESTUDIANTES
                =================================================== -->

                <?php if (
                    $periodo_actual &&
                    !$periodo_bloqueado
                ): ?>


                    <div class="card shadow">


                        <div
                            class="card-header
                                   bg-success
                                   text-white"
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
                                count($estudiantes) > 0
                            ): ?>


                                <form
                                    action="../seguimiento/guardar.php"
                                    method="POST"
                                >


                                    <input
                                        type="hidden"
                                        name="curso_id"
                                        value="<?= intval(
                                            $carga["curso_id"]
                                        ) ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="materia_id"
                                        value="<?= intval(
                                            $carga["materia_id"]
                                        ) ?>"
                                    >


                                    <input
                                        type="hidden"
                                        name="periodo_id"
                                        value="<?= $periodo_id ?>"
                                    >


                                    <div
                                        class="table-responsive"
                                    >

                                        <table
                                            class="table
                                                   table-bordered
                                                   table-striped
                                                   align-middle"
                                        >

                                            <thead>

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

                                                    <th
                                                        class="text-center"
                                                    >

                                                        Desempeño

                                                    </th>

                                                </tr>

                                            </thead>


                                            <tbody>


                                            <?php

                                            foreach (
                                                $estudiantes
                                                as $numero =>
                                                $estudiante
                                            ):

                                                $estudiante_id =
                                                    intval(
                                                        $estudiante["id"]
                                                    );


                                                $color_actual =
                                                    $desempenos[
                                                        $estudiante_id
                                                    ] ?? 0;

                                            ?>


                                                <tr>


                                                    <td>

                                                        <?= $numero + 1 ?>

                                                    </td>


                                                    <td>

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

                                                    </td>


                                                    <td>

                                                        <?= htmlspecialchars(
                                                            $estudiante[
                                                                "documento"
                                                            ]
                                                        ) ?>

                                                    </td>


                                                    <td
                                                        class="text-center"
                                                    >


                                                        <!-- ==================================
                                                             BAJO
                                                        =================================== -->

                                                        <button
                                                            type="button"

                                                            class="
                                                                btn
                                                                btn-outline-danger
                                                                btn-desempeno
                                                                btn-bajo

                                                                <?= (
                                                                    $color_actual === 1
                                                                )
                                                                    ? "seleccionado"
                                                                    : ""
                                                                ?>
                                                            "

                                                            data-color="1"

                                                            data-estudiante="<?= $estudiante_id ?>"
                                                        >

                                                            <span
                                                                class="circulo-semaforo"
                                                            ></span>

                                                            Bajo

                                                        </button>


                                                        <!-- ==================================
                                                             BÁSICO
                                                        =================================== -->

                                                        <button
                                                            type="button"

                                                            class="
                                                                btn
                                                                btn-outline-warning
                                                                btn-desempeno
                                                                btn-basico

                                                                <?= (
                                                                    $color_actual === 2
                                                                )
                                                                    ? "seleccionado"
                                                                    : ""
                                                                ?>
                                                            "

                                                            data-color="2"

                                                            data-estudiante="<?= $estudiante_id ?>"
                                                        >

                                                            <span
                                                                class="circulo-semaforo"
                                                            ></span>

                                                            Básico

                                                        </button>


                                                        <!-- ==================================
                                                             ALTO
                                                        =================================== -->

                                                        <button
                                                            type="button"

                                                            class="
                                                                btn
                                                                btn-outline-success
                                                                btn-desempeno
                                                                btn-alto

                                                                <?= (
                                                                    $color_actual === 3
                                                                )
                                                                    ? "seleccionado"
                                                                    : ""
                                                                ?>
                                                            "

                                                            data-color="3"

                                                            data-estudiante="<?= $estudiante_id ?>"
                                                        >

                                                            <span
                                                                class="circulo-semaforo"
                                                            ></span>

                                                            Alto

                                                        </button>


                                                        <!-- ==================================
                                                             INPUT OCULTO
                                                        ================================== -->

                                                        <input
                                                            type="hidden"

                                                            name="color[<?= $estudiante_id ?>]"

                                                            value="<?= $color_actual ?>"

                                                            class="input-color"
                                                        >


                                                    </td>

                                                </tr>


                                            <?php endforeach; ?>


                                            </tbody>

                                        </table>

                                    </div>


                                    <!-- ==================================
                                         GUARDAR
                                    =================================== -->

                                    <div
                                        class="text-end mt-3"
                                    >

                                        <button
                                            type="submit"
                                            class="btn btn-success"
                                        >

                                            💾 Guardar seguimiento

                                        </button>

                                    </div>


                                </form>


                            <?php else: ?>


                                <div
                                    class="alert
                                           alert-info
                                           mb-0"
                                >

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


<script>

document.addEventListener(
    "DOMContentLoaded",
    function () {


        const botones =
            document.querySelectorAll(
                ".btn-desempeno"
            );


        // ==================================================
        // APLICAR COLOR AL BOTÓN
        // ==================================================

        function aplicarColor(boton) {


            const color =
                boton.dataset.color;


            // ----------------------------------------------
            // BAJO
            // ----------------------------------------------

            if (color === "1") {

                boton.style.setProperty(
                    "background-color",
                    "#dc3545",
                    "important"
                );

                boton.style.setProperty(
                    "border-color",
                    "#dc3545",
                    "important"
                );

                boton.style.setProperty(
                    "color",
                    "#ffffff",
                    "important"
                );

            }


            // ----------------------------------------------
            // BÁSICO
            // ----------------------------------------------

            if (color === "2") {

                boton.style.setProperty(
                    "background-color",
                    "#ffc107",
                    "important"
                );

                boton.style.setProperty(
                    "border-color",
                    "#ffc107",
                    "important"
                );

                boton.style.setProperty(
                    "color",
                    "#212529",
                    "important"
                );

            }


            // ----------------------------------------------
            // ALTO
            // ----------------------------------------------

            if (color === "3") {

                boton.style.setProperty(
                    "background-color",
                    "#198754",
                    "important"
                );

                boton.style.setProperty(
                    "border-color",
                    "#198754",
                    "important"
                );

                boton.style.setProperty(
                    "color",
                    "#ffffff",
                    "important"
                );

            }


            // ==================================================
            // CÍRCULO
            // ==================================================

            const circulo =
                boton.querySelector(
                    ".circulo-semaforo"
                );


            if (!circulo) {

                return;

            }


            if (color === "1") {

                circulo.style.setProperty(
                    "background-color",
                    "#dc3545",
                    "important"
                );

                circulo.style.setProperty(
                    "border-color",
                    "#ffffff",
                    "important"
                );

            }


            if (color === "2") {

                circulo.style.setProperty(
                    "background-color",
                    "#ffc107",
                    "important"
                );

                circulo.style.setProperty(
                    "border-color",
                    "#212529",
                    "important"
                );

            }


            if (color === "3") {

                circulo.style.setProperty(
                    "background-color",
                    "#198754",
                    "important"
                );

                circulo.style.setProperty(
                    "border-color",
                    "#ffffff",
                    "important"
                );

            }

        }


        // ==================================================
        // QUITAR COLOR
        // ==================================================

        function quitarColor(boton) {


            boton.style.removeProperty(
                "background-color"
            );

            boton.style.removeProperty(
                "border-color"
            );

            boton.style.removeProperty(
                "color"
            );


            const circulo =
                boton.querySelector(
                    ".circulo-semaforo"
                );


            if (circulo) {

                circulo.style.removeProperty(
                    "background-color"
                );

                circulo.style.removeProperty(
                    "border-color"
                );

            }

        }


        // ==================================================
        // CLIC EN DESEMPEÑO
        // ==================================================

        botones.forEach(
            function (boton) {


                boton.addEventListener(
                    "click",
                    function () {


                        const fila =
                            boton.closest("tr");


                        if (!fila) {

                            return;

                        }


                        // ----------------------------------
                        // QUITAR ANTERIOR
                        // ----------------------------------

                        const botonesFila =
                            fila.querySelectorAll(
                                ".btn-desempeno"
                            );


                        botonesFila.forEach(
                            function (btn) {

                                btn.classList.remove(
                                    "seleccionado"
                                );

                                quitarColor(btn);

                            }
                        );


                        // ----------------------------------
                        // SELECCIONAR ACTUAL
                        // ----------------------------------

                        boton.classList.add(
                            "seleccionado"
                        );


                        // ----------------------------------
                        // PINTAR BOTÓN Y CÍRCULO
                        // ----------------------------------

                        aplicarColor(
                            boton
                        );


                        // ----------------------------------
                        // ACTUALIZAR INPUT
                        // ----------------------------------

                        const input =
                            fila.querySelector(
                                ".input-color"
                            );


                        if (input) {

                            input.value =
                                boton.dataset.color;

                        }

                    }
                );


                // ==================================================
                // PINTAR DESEMPEÑO YA GUARDADO
                // ==================================================

                if (
                    boton.classList.contains(
                        "seleccionado"
                    )
                ) {

                    aplicarColor(
                        boton
                    );

                }

            }
        );

    }
);

</script>


<?php

include("../../includes/footer.php");

?>