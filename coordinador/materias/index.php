
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");

$mensaje = "";
$tipoMensaje = "";

// ================================================
// REGISTRAR MATERIA SIN DUPLICADOS
// ================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");

    if ($nombre === "") {

        $mensaje = "Debes escribir el nombre de la materia.";
        $tipoMensaje = "danger";

    } elseif (mb_strlen($nombre, "UTF-8") > 100) {

        $mensaje = "El nombre de la materia no puede superar los 100 caracteres.";
        $tipoMensaje = "danger";

    } else {

        // Normalizar espacios repetidos entre palabras.
        $nombre = preg_replace('/\s+/u', ' ', $nombre);

        // Comprobar si la materia ya existe.
        // LOWER permite comparar sin distinguir mayúsculas y minúsculas.
        $stmt = $conexion->prepare("
            SELECT id
            FROM materias
            WHERE LOWER(TRIM(nombre)) = LOWER(?)
            LIMIT 1
        ");

        if (!$stmt) {

            $mensaje = "No se pudo comprobar si la materia ya existe.";
            $tipoMensaje = "danger";

        } else {

            $stmt->bind_param("s", $nombre);
            $stmt->execute();

            $resultadoDuplicado = $stmt->get_result();

            if ($resultadoDuplicado->num_rows > 0) {

                $mensaje = "Esta materia ya está registrada. No puedes ingresarla nuevamente.";
                $tipoMensaje = "warning";

            } else {

                // Insertar únicamente si no existe.
                $insertar = $conexion->prepare("
                    INSERT INTO materias (nombre)
                    VALUES (?)
                ");

                if (!$insertar) {

                    $mensaje = "No se pudo preparar el registro de la materia.";
                    $tipoMensaje = "danger";

                } else {

                    $insertar->bind_param("s", $nombre);

                    if ($insertar->execute()) {

                        $mensaje = "Materia registrada correctamente.";
                        $tipoMensaje = "success";

                    } else {

                        $mensaje = "No se pudo registrar la materia.";
                        $tipoMensaje = "danger";

                    }

                    $insertar->close();
                }
            }

            $stmt->close();
        }
    }
}


// ================================================
// ELIMINAR MATERIA
// ================================================

if (isset($_GET["eliminar"]) &&
    ctype_digit((string) $_GET["eliminar"])) {

    $id = (int) $_GET["eliminar"];

    $stmt = $conexion->prepare("
        DELETE FROM materias
        WHERE id = ?
    ");

    if (!$stmt) {

        $mensaje = "No se pudo preparar la eliminación.";
        $tipoMensaje = "danger";

    } else {

        $stmt->bind_param("i", $id);

        if ($stmt->execute() && $stmt->affected_rows > 0) {

            $mensaje = "Materia eliminada correctamente.";
            $tipoMensaje = "success";

        } else {

            $mensaje = "No se pudo eliminar la materia. Puede estar relacionada con una carga académica.";
            $tipoMensaje = "danger";

        }

        $stmt->close();
    }
}


// ================================================
// CONSULTAR MATERIAS REGISTRADAS
// ================================================

$resultadoMaterias = $conexion->query("
    SELECT id, nombre
    FROM materias
    ORDER BY nombre ASC
");


// ================================================
// ENCABEZADO Y MENÚ
// ================================================

include("../../includes/header.php");
include("../../includes/navbar.php");

?>

<div class="evalora-layout">

    <?php include("../../includes/sidebar.php"); ?>

    <main class="evalora-main materias-page">

        <div class="materias-contenido">

            <!-- ENCABEZADO -->

            <header class="materias-heading">

                <div class="materias-heading-icon" aria-hidden="true">
                    ▣
                </div>

                <div class="materias-heading-text">
                    <h1>Administrar materias</h1>

                    <p>
                        Registra y consulta las materias utilizadas
                        en la carga académica.
                    </p>
                </div>

            </header>


            <!-- MENSAJES -->

            <?php if ($mensaje !== ""): ?>

                <div
                    class="alert alert-<?= htmlspecialchars(
                        $tipoMensaje,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?> alert-dismissible fade show materias-alert"
                    role="alert"
                >

                    <?= htmlspecialchars(
                        $mensaje,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"
                    ></button>

                </div>

            <?php endif; ?>


            <!-- FORMULARIO PARA REGISTRAR MATERIAS -->

            <section class="card materias-card">

                <div class="materias-card-header">

                    <div class="materias-card-icon" aria-hidden="true">
                        +
                    </div>

                    <div>
                        <h2>Registrar nueva materia</h2>

                        <p>
                            Añade una asignatura al sistema académico.
                        </p>
                    </div>

                </div>

                <div class="card-body">

                    <form method="POST" action="index.php">

                        <div class="materias-form-group">

                            <label
                                for="nombre"
                                class="form-label"
                            >
                                Nombre de la materia
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control materias-input"
                                placeholder="Ejemplo: Matemáticas"
                                maxlength="100"
                                autocomplete="off"
                                required
                            >

                            <div class="form-text">
                                Escribe el nombre completo de la asignatura.
                            </div>

                        </div>

                        <div class="materias-form-actions">

                            <button
                                type="submit"
                                class="btn materias-btn-guardar"
                            >
                                Registrar materia
                            </button>

                            <button
                                type="reset"
                                class="btn materias-btn-limpiar"
                            >
                                Limpiar
                            </button>

                        </div>

                    </form>

                </div>

            </section>


            <!-- TABLA DE MATERIAS -->

            <section class="card materias-card">

                <div class="materias-card-header">

                    <div class="materias-card-icon materias-icon-lista"
                         aria-hidden="true">
                        ☷
                    </div>

                    <div>
                        <h2>Materias registradas</h2>

                        <p>
                            Consulta las asignaturas disponibles.
                        </p>
                    </div>

                    <?php if ($resultadoMaterias): ?>

                        <span class="materias-contador">
                            <?= $resultadoMaterias->num_rows ?>
                            <?= $resultadoMaterias->num_rows === 1
                                ? "materia"
                                : "materias" ?>
                        </span>

                    <?php endif; ?>

                </div>

                <div class="card-body p-0">

                    <?php if (
                        $resultadoMaterias &&
                        $resultadoMaterias->num_rows > 0
                    ): ?>

                        <div class="table-responsive">

                            <table class="table materias-table align-middle mb-0">

                                <thead>
                                    <tr>
                                        <th
                                            scope="col"
                                            class="materias-numero-col"
                                        >
                                            #
                                        </th>

                                        <th scope="col">
                                            Materia
                                        </th>

                                        <th
                                            scope="col"
                                            class="materias-accion-col"
                                        >
                                            Acción
                                        </th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php
                                    $contador = 1;

                                    while (
                                        $materia =
                                        $resultadoMaterias->fetch_assoc()
                                    ):
                                    ?>

                                        <tr>

                                            <td class="materias-numero">
                                                <?= $contador++ ?>
                                            </td>

                                            <td>
                                                <span class="materias-nombre">
                                                    <?= htmlspecialchars(
                                                        $materia["nombre"],
                                                        ENT_QUOTES,
                                                        "UTF-8"
                                                    ) ?>
                                                </span>
                                            </td>

                                            <td>
                                                <a
                                                    href="index.php?eliminar=<?= (int) $materia["id"] ?>"
                                                    class="btn materias-btn-eliminar"
                                                    onclick="return confirm('¿Seguro que deseas eliminar esta materia? Si está relacionada con una carga académica, la base de datos podría impedirlo.');"
                                                >
                                                    Eliminar
                                                </a>
                                            </td>

                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="materias-vacio">

                            <div
                                class="materias-vacio-icon"
                                aria-hidden="true"
                            >
                                ▣
                            </div>

                            <h3>No hay materias registradas</h3>

                            <p>
                                Registra la primera materia desde el
                                formulario superior.
                            </p>

                        </div>

                    <?php endif; ?>

                </div>

            </section>

        </div>

    </main>

</div>

<?php include("../../includes/footer.php"); ?>
```