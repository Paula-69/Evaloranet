<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");


// =========================================================
// GUARDAR NUEVA CARGA ACADÉMICA
// =========================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $docente_id = isset($_POST["docente_id"])
        ? (int) $_POST["docente_id"]
        : 0;

    $curso_id = isset($_POST["curso_id"])
        ? (int) $_POST["curso_id"]
        : 0;

    $materia_id = isset($_POST["materia_id"])
        ? (int) $_POST["materia_id"]
        : 0;


    if (
        $docente_id <= 0 ||
        $curso_id <= 0 ||
        $materia_id <= 0
    ) {

        header("Location: index.php?error=1");
        exit();

    }


    // Verificar si ya existe
    $verificar = $conexion->prepare("
        SELECT id
        FROM carga_academica
        WHERE docente_id = ?
        AND curso_id = ?
        AND materia_id = ?
    ");

    $verificar->bind_param(
        "iii",
        $docente_id,
        $curso_id,
        $materia_id
    );

    $verificar->execute();

    $resultado_verificar = $verificar->get_result();


    if ($resultado_verificar->num_rows > 0) {

        header("Location: index.php?duplicado=1");
        exit();

    }


    // Insertar
    $insertar = $conexion->prepare("
        INSERT INTO carga_academica
        (
            docente_id,
            curso_id,
            materia_id
        )
        VALUES (?, ?, ?)
    ");

    $insertar->bind_param(
        "iii",
        $docente_id,
        $curso_id,
        $materia_id
    );


    if ($insertar->execute()) {

        header("Location: index.php?success=1");
        exit();

    } else {

        header("Location: index.php?error=2");
        exit();

    }

}


// =========================================================
// ELIMINAR CARGA ACADÉMICA
// =========================================================

if (
    isset($_GET["eliminar"]) &&
    is_numeric($_GET["eliminar"])
) {

    $id = (int) $_GET["eliminar"];


    $eliminar = $conexion->prepare("
        DELETE FROM carga_academica
        WHERE id = ?
    ");

    $eliminar->bind_param(
        "i",
        $id
    );

    $eliminar->execute();


    header("Location: index.php?eliminado=1");
    exit();

}


// =========================================================
// OBTENER DOCENTES
// =========================================================

$docentes = $conexion->query("
    SELECT
        d.id,
        u.nombres,
        u.apellidos,
        u.documento

    FROM docentes d

    INNER JOIN usuarios u
        ON d.usuario_id = u.id

    WHERE d.estado = 'Activo'

    ORDER BY
        u.apellidos,
        u.nombres
");


// =========================================================
// OBTENER CURSOS
// =========================================================

$cursos = $conexion->query("
    SELECT
        id,
        nombre

    FROM cursos

    ORDER BY nombre
");


// =========================================================
// OBTENER MATERIAS
// =========================================================

$materias = $conexion->query("
    SELECT
        id,
        nombre

    FROM materias

    ORDER BY nombre
");


// =========================================================
// OBTENER CARGA ACADÉMICA
// =========================================================

$asignaciones = $conexion->query("
    SELECT

        ca.id,

        ca.docente_id,

        ca.curso_id,

        ca.materia_id,

        u.nombres AS docente_nombres,

        u.apellidos AS docente_apellidos,

        u.documento AS docente_documento,

        c.nombre AS curso_nombre,

        m.nombre AS materia_nombre

    FROM carga_academica ca

    INNER JOIN docentes d
        ON ca.docente_id = d.id

    INNER JOIN usuarios u
        ON d.usuario_id = u.id

    INNER JOIN cursos c
        ON ca.curso_id = c.id

    INNER JOIN materias m
        ON ca.materia_id = m.id

    ORDER BY
        c.nombre,
        m.nombre,
        u.apellidos,
        u.nombres
");


include("../../includes/header.php");
include("../../includes/navbar.php");

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             SIDEBAR
        ====================================================== -->

        <div
            class="col-md-2 p-0"
            style="
                background: linear-gradient(
                    180deg,
                    #1f2937 0%,
                    #111827 100%
                );
                min-height: calc(100vh - 64px);
            "
        >

            <?php include("../../includes/sidebar.php"); ?>

        </div>


        <!-- =====================================================
             CONTENIDO PRINCIPAL
        ====================================================== -->

        <div class="col-md-10">

            <div class="container mt-4">


                <!-- =================================================
                     TÍTULO
                ================================================== -->

                <div class="mb-4">

                    <h2>
                        Carga académica
                    </h2>

                    <p class="text-muted">
                        Administra las asignaciones de docentes,
                        cursos y materias.
                    </p>

                </div>


                <!-- =================================================
                     MENSAJES
                ================================================== -->

                <?php if (isset($_GET["success"])): ?>

                    <div class="alert alert-success">

                        La carga académica fue asignada
                        correctamente.

                    </div>

                <?php endif; ?>


                <?php if (isset($_GET["duplicado"])): ?>

                    <div class="alert alert-warning">

                        Esta asignación ya existe.

                    </div>

                <?php endif; ?>


                <?php if (isset($_GET["error"])): ?>

                    <div class="alert alert-danger">

                        <?php

                        if ($_GET["error"] == "1") {

                            echo "Debe seleccionar docente, curso y materia.";

                        } else {

                            echo "No fue posible guardar la asignación.";

                        }

                        ?>

                    </div>

                <?php endif; ?>


                <?php if (isset($_GET["eliminado"])): ?>

                    <div class="alert alert-success">

                        La asignación fue eliminada correctamente.

                    </div>

                <?php endif; ?>


                <!-- =================================================
                     NUEVA ASIGNACIÓN
                ================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">
                            Nueva asignación
                        </h5>

                    </div>


                    <div class="card-body">

                        <form
                            method="POST"
                            action="index.php"
                        >

                            <div class="row">


                                <!-- DOCENTE -->

                                <div class="col-md-4 mb-3">

                                    <label class="form-label">
                                        Docente
                                    </label>

                                    <select
                                        name="docente_id"
                                        class="form-select"
                                        required
                                    >

                                        <option value="">
                                            Seleccione un docente
                                        </option>


                                        <?php while (
                                            $docente =
                                            $docentes->fetch_assoc()
                                        ): ?>

                                            <option
                                                value="<?= (int) $docente["id"] ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $docente["nombres"]
                                                    . " "
                                                    . $docente["apellidos"]
                                                ) ?>

                                                -
                                                <?= htmlspecialchars(
                                                    $docente["documento"]
                                                ) ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>


                                <!-- CURSO -->

                                <div class="col-md-4 mb-3">

                                    <label class="form-label">
                                        Curso
                                    </label>

                                    <select
                                        name="curso_id"
                                        class="form-select"
                                        required
                                    >

                                        <option value="">
                                            Seleccione un curso
                                        </option>


                                        <?php while (
                                            $curso =
                                            $cursos->fetch_assoc()
                                        ): ?>

                                            <option
                                                value="<?= (int) $curso["id"] ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $curso["nombre"]
                                                ) ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>


                                <!-- MATERIA -->

                                <div class="col-md-4 mb-3">

                                    <label class="form-label">
                                        Materia
                                    </label>

                                    <select
                                        name="materia_id"
                                        class="form-select"
                                        required
                                    >

                                        <option value="">
                                            Seleccione una materia
                                        </option>


                                        <?php while (
                                            $materia =
                                            $materias->fetch_assoc()
                                        ): ?>

                                            <option
                                                value="<?= (int) $materia["id"] ?>"
                                            >

                                                <?= htmlspecialchars(
                                                    $materia["nombre"]
                                                ) ?>

                                            </option>

                                        <?php endwhile; ?>

                                    </select>

                                </div>

                            </div>


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                Asignar carga académica

                            </button>

                        </form>

                    </div>

                </div>


                <!-- =================================================
                     FILTROS
                ================================================== -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-light">

                        <h5 class="mb-0">
                            🔎 Filtrar carga académica
                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="row g-3">


                            <!-- BUSCADOR -->

                            <div class="col-md-12">

                                <label
                                    for="buscarCarga"
                                    class="form-label"
                                >
                                    Buscar
                                </label>

                                <input
                                    type="text"
                                    id="buscarCarga"
                                    class="form-control"
                                    placeholder="Buscar por docente, documento, curso o materia..."
                                >

                            </div>


                            <!-- DOCENTE -->

                            <div class="col-md-4">

                                <label
                                    for="filtroDocente"
                                    class="form-label"
                                >
                                    👨‍🏫 Docente
                                </label>

                                <select
                                    id="filtroDocente"
                                    class="form-select"
                                >

                                    <option value="">
                                        Todos los docentes
                                    </option>


                                    <?php

                                    $filtro_docentes =
                                        $conexion->query("
                                            SELECT
                                                d.id,
                                                u.nombres,
                                                u.apellidos

                                            FROM docentes d

                                            INNER JOIN usuarios u
                                                ON d.usuario_id = u.id

                                            WHERE d.estado = 'Activo'

                                            ORDER BY
                                                u.apellidos,
                                                u.nombres
                                        ");

                                    ?>


                                    <?php while (
                                        $docente_filtro =
                                        $filtro_docentes->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?= (int) $docente_filtro["id"] ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $docente_filtro["nombres"]
                                                . " "
                                                . $docente_filtro["apellidos"]
                                            ) ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>


                            <!-- CURSO -->

                            <div class="col-md-4">

                                <label
                                    for="filtroCurso"
                                    class="form-label"
                                >
                                    🏫 Curso
                                </label>

                                <select
                                    id="filtroCurso"
                                    class="form-select"
                                >

                                    <option value="">
                                        Todos los cursos
                                    </option>


                                    <?php

                                    $filtro_cursos =
                                        $conexion->query("
                                            SELECT
                                                id,
                                                nombre

                                            FROM cursos

                                            ORDER BY nombre
                                        ");

                                    ?>


                                    <?php while (
                                        $curso_filtro =
                                        $filtro_cursos->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?= (int) $curso_filtro["id"] ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $curso_filtro["nombre"]
                                            ) ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>


                            <!-- MATERIA -->

                            <div class="col-md-4">

                                <label
                                    for="filtroMateria"
                                    class="form-label"
                                >
                                    📚 Materia
                                </label>

                                <select
                                    id="filtroMateria"
                                    class="form-select"
                                >

                                    <option value="">
                                        Todas las materias
                                    </option>


                                    <?php

                                    $filtro_materias =
                                        $conexion->query("
                                            SELECT
                                                id,
                                                nombre

                                            FROM materias

                                            ORDER BY nombre
                                        ");

                                    ?>


                                    <?php while (
                                        $materia_filtro =
                                        $filtro_materias->fetch_assoc()
                                    ): ?>

                                        <option
                                            value="<?= (int) $materia_filtro["id"] ?>"
                                        >

                                            <?= htmlspecialchars(
                                                $materia_filtro["nombre"]
                                            ) ?>

                                        </option>

                                    <?php endwhile; ?>

                                </select>

                            </div>


                            <!-- BOTONES -->

                            <div class="col-12 text-end">

                                <button
                                    type="button"
                                    id="aplicarFiltrosCarga"
                                    class="btn btn-primary me-2"
                                >

                                    🔎 Filtrar

                                </button>


                                <button
                                    type="button"
                                    id="limpiarFiltrosCarga"
                                    class="btn btn-secondary"
                                >

                                    🧹 Limpiar filtros

                                </button>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- =================================================
                     TABLA DE CARGA
                ================================================== -->

                <div class="card shadow">

                    <div class="card-header carga-header">

                        <div
                            class="d-flex justify-content-between align-items-center"
                        >

                            <h5 class="mb-0">
                                Carga académica asignada
                            </h5>


                            <span
                                id="contadorCarga"
                                class="badge"
                            >
                                0 resultados
                            </span>

                        </div>

                    </div>


                    <div class="card-body p-0">


                        <?php if (
                            $asignaciones->num_rows == 0
                        ): ?>

                            <div class="alert alert-info m-3">

                                Todavía no hay cargas académicas
                                asignadas.

                            </div>


                        <?php else: ?>


                            <div class="table-responsive">

                                <table
                                    id="tablaCargaAcademica"
                                    class="table table-striped table-hover mb-0"
                                >

                                    <thead>

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
                                                Curso
                                            </th>

                                            <th>
                                                Materia
                                            </th>

                                            <th class="text-center">
                                                Acciones
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                        <?php

                                        $numero = 1;

                                        while (
                                            $asignacion =
                                            $asignaciones->fetch_assoc()
                                        ):

                                            $nombre_docente =
                                                $asignacion[
                                                    "docente_nombres"
                                                ]
                                                . " "
                                                .
                                                $asignacion[
                                                    "docente_apellidos"
                                                ];

                                        ?>


                                            <tr
                                                class="fila-carga"
                                                data-docente-id="<?= (int) $asignacion["docente_id"] ?>"
                                                data-curso-id="<?= (int) $asignacion["curso_id"] ?>"
                                                data-materia-id="<?= (int) $asignacion["materia_id"] ?>"
                                            >

                                                <td class="numero-fila">

                                                    <?= $numero++ ?>

                                                </td>


                                                <td class="dato-docente">

                                                    <?= htmlspecialchars(
                                                        $nombre_docente
                                                    ) ?>

                                                </td>


                                                <td class="dato-documento">

                                                    <?= htmlspecialchars(
                                                        $asignacion[
                                                            "docente_documento"
                                                        ]
                                                    ) ?>

                                                </td>


                                                <td class="dato-curso">

                                                    <?= htmlspecialchars(
                                                        $asignacion[
                                                            "curso_nombre"
                                                        ]
                                                    ) ?>

                                                </td>


                                                <td class="dato-materia">

                                                    <?= htmlspecialchars(
                                                        $asignacion[
                                                            "materia_nombre"
                                                        ]
                                                    ) ?>

                                                </td>


                                                <td class="text-center">

                                                    <a
                                                        href="estudiantes.php?curso_id=<?= (int) $asignacion["curso_id"] ?>"
                                                        class="btn btn-primary btn-sm"
                                                    >
                                                        Ver estudiantes
                                                    </a>


                                                    <a
                                                        href="index.php?eliminar=<?= (int) $asignacion["id"] ?>"
                                                        class="btn btn-danger btn-sm"
                                                        onclick="return confirm('¿Está seguro de eliminar esta asignación?');"
                                                    >
                                                        Eliminar
                                                    </a>

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