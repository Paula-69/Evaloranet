
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");

// ================================================
// PROCESAR CREACIÓN DEL CURSO
// ================================================

$mensaje = "";
$tipo_mensaje = "";
$nombre = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");

    if ($nombre === "") {

        $mensaje = "Debes ingresar el nombre del curso.";
        $tipo_mensaje = "danger";

    } elseif (!preg_match('/^[0-9]{3,4}$/', $nombre)) {

        $mensaje = "El curso debe tener 3 o 4 números. Ejemplos: 601, 701, 1001, 1101.";
        $tipo_mensaje = "danger";

    } else {

        $stmt = $conexion->prepare("
            SELECT id
            FROM cursos
            WHERE nombre = ?
            LIMIT 1
        ");

        if (!$stmt) {

            $mensaje = "No se pudo comprobar si el curso ya existe.";
            $tipo_mensaje = "danger";

        } else {

            $stmt->bind_param("s", $nombre);
            $stmt->execute();
            $resultado = $stmt->get_result();

            if ($resultado->num_rows > 0) {

                $mensaje = "El curso " . htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") . " ya existe.";
                $tipo_mensaje = "warning";

            } else {

                $stmt->close();
                $stmt = null;

                $insertar = $conexion->prepare("
                    INSERT INTO cursos (nombre)
                    VALUES (?)
                ");

                if (!$insertar) {

                    $mensaje = "No se pudo preparar el registro del curso.";
                    $tipo_mensaje = "danger";

                } else {

                    $insertar->bind_param("s", $nombre);

                    if ($insertar->execute()) {

                        $mensaje = "El curso " . htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") . " fue creado correctamente.";
                        $tipo_mensaje = "success";
                        $nombre = "";

                    } else {

                        $mensaje = "No se pudo guardar el curso.";
                        $tipo_mensaje = "danger";

                    }

                    $insertar->close();
                }
            }

            if ($stmt instanceof mysqli_stmt) {
                $stmt->close();
            }
        }
    }
}

// ================================================
// OBTENER CURSOS REGISTRADOS
// ================================================

$resultadoCursos = $conexion->query("
    SELECT id, nombre
    FROM cursos
    ORDER BY nombre ASC
");

// ================================================
// ESTRUCTURA COMPARTIDA
// ================================================

include("../../includes/header.php");
include("../../includes/navbar.php");

?>

<div class="evalora-layout">

    <?php include("../../includes/sidebar.php"); ?>

    <main class="evalora-main cursos-page">

        <div class="cursos-contenido">

            <!-- ENCABEZADO -->
            <header class="cursos-heading">

                <div class="cursos-heading-icon" aria-hidden="true">
                    ▦
                </div>

                <div>
                    <h1>Crear cursos</h1>
                    <p>Administra los cursos de la institución.</p>
                </div>

                <a href="../index.php" class="btn cursos-btn-volver">
                    ← Volver al dashboard
                </a>

            </header>

            <!-- MENSAJES -->
            <?php if ($mensaje !== ""): ?>

                <div class="alert alert-<?= htmlspecialchars($tipo_mensaje, ENT_QUOTES, "UTF-8") ?> alert-dismissible fade show cursos-alert" role="alert">

                    <?= $mensaje ?>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="alert"
                        aria-label="Cerrar"
                    ></button>

                </div>

            <?php endif; ?>

            <!-- FORMULARIO DE CREACIÓN -->
            <section class="card cursos-card">

                <div class="cursos-card-header">

                    <div class="cursos-card-icon" aria-hidden="true">
                        ＋
                    </div>

                    <div>
                        <h2>Nuevo curso</h2>
                        <p>Registra un curso con el formato de tu institución.</p>
                    </div>

                </div>

                <div class="card-body">

                    <form action="index.php" method="POST">

                        <div class="cursos-form-group">

                            <label for="nombre" class="form-label">
                                Nombre del curso
                            </label>

                            <input
                                type="text"
                                name="nombre"
                                id="nombre"
                                class="form-control cursos-input"
                                placeholder="Ejemplo: 601"
                                pattern="[0-9]{3,4}"
                                minlength="3"
                                maxlength="4"
                                inputmode="numeric"
                                autocomplete="off"
                                value="<?= htmlspecialchars($nombre, ENT_QUOTES, "UTF-8") ?>"
                                required
                            >

                            <div class="form-text">
                                Ingresa entre 3 y 4 números. Por ejemplo:
                                <strong>601, 701, 801, 1001 o 1101.</strong>
                            </div>

                        </div>

                        <div class="cursos-form-actions">

                            <button type="submit" class="btn cursos-btn-guardar">
                                Guardar curso
                            </button>

                            <a href="index.php" class="btn cursos-btn-cancelar">
                                Limpiar
                            </a>

                        </div>

                    </form>

                </div>

            </section>

            <!-- LISTADO DE CURSOS -->
            <section class="card cursos-card">

                <div class="cursos-card-header">

                    <div class="cursos-card-icon cursos-card-icon-lista" aria-hidden="true">
                        ☷
                    </div>

                    <div>
                        <h2>Cursos registrados</h2>
                        <p>Consulta los cursos disponibles en el sistema.</p>
                    </div>

                    <?php if ($resultadoCursos): ?>
                        <span class="cursos-contador">
                            <?= $resultadoCursos->num_rows ?>
                            <?= $resultadoCursos->num_rows === 1 ? "curso" : "cursos" ?>
                        </span>
                    <?php endif; ?>

                </div>

                <div class="card-body p-0">

                    <?php if ($resultadoCursos && $resultadoCursos->num_rows > 0): ?>

                        <div class="table-responsive">

                            <table class="table cursos-table align-middle mb-0">

                                <thead>
                                    <tr>
                                        <th scope="col" class="cursos-col-numero">#</th>
                                        <th scope="col">Curso</th>
                                    </tr>
                                </thead>

                                <tbody>

                                    <?php
                                    $contador = 1;

                                    while ($curso = $resultadoCursos->fetch_assoc()):
                                    ?>

                                        <tr>
                                            <td class="cursos-numero">
                                                <?= $contador++ ?>
                                            </td>

                                            <td>
                                                <span class="cursos-nombre">
                                                    <?= htmlspecialchars($curso["nombre"], ENT_QUOTES, "UTF-8") ?>
                                                </span>
                                            </td>
                                        </tr>

                                    <?php endwhile; ?>

                                </tbody>

                            </table>

                        </div>

                    <?php else: ?>

                        <div class="cursos-vacio">
                            <div class="cursos-vacio-icon" aria-hidden="true">▦</div>
                            <h3>Aún no hay cursos registrados</h3>
                            <p>Utiliza el formulario superior para crear el primer curso.</p>
                        </div>

                    <?php endif; ?>

                </div>

            </section>

        </div>

    </main>

</div>

<?php include("../../includes/footer.php"); ?>
```