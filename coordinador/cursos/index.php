<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");


// ==========================================
// VARIABLES
// ==========================================

$mensaje = "";
$tipoMensaje = "";


// ==========================================
// CREAR CURSO
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["crear_curso"])) {

    $nombre = trim($_POST["nombre"] ?? "");


    if ($nombre === "") {

        $mensaje = "Debe escribir el nombre del curso.";
        $tipoMensaje = "danger";

    } else {

        // Verificar si ya existe
        $sqlVerificar = "
            SELECT id
            FROM cursos
            WHERE nombre = ?
        ";

        $stmt = $conexion->prepare($sqlVerificar);

        if ($stmt) {

            $stmt->bind_param("s", $nombre);
            $stmt->execute();

            $resultado = $stmt->get_result();


            if ($resultado->num_rows > 0) {

                $mensaje = "Ese curso ya existe.";
                $tipoMensaje = "warning";

            } else {

                $sqlInsertar = "
                    INSERT INTO cursos (nombre)
                    VALUES (?)
                ";

                $stmtInsertar = $conexion->prepare($sqlInsertar);


                if ($stmtInsertar) {

                    $stmtInsertar->bind_param("s", $nombre);


                    if ($stmtInsertar->execute()) {

                        $mensaje = "Curso creado correctamente.";
                        $tipoMensaje = "success";

                    } else {

                        $mensaje = "Error al crear el curso: "
                                 . $stmtInsertar->error;

                        $tipoMensaje = "danger";
                    }


                    $stmtInsertar->close();

                } else {

                    $mensaje = "Error preparando la consulta.";
                    $tipoMensaje = "danger";
                }
            }


            $stmt->close();

        } else {

            $mensaje = "Error preparando la consulta.";
            $tipoMensaje = "danger";
        }
    }
}


// ==========================================
// ELIMINAR CURSO
// ==========================================

if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST["eliminar_curso"])) {

    $curso_id = (int) ($_POST["curso_id"] ?? 0);


    if ($curso_id > 0) {

        // Primero verificamos si tiene carga académica
        $sqlVerificar = "
            SELECT id
            FROM carga_academica
            WHERE curso_id = ?
            LIMIT 1
        ";

        $stmt = $conexion->prepare($sqlVerificar);

        if ($stmt) {

            $stmt->bind_param("i", $curso_id);
            $stmt->execute();

            $resultado = $stmt->get_result();


            if ($resultado->num_rows > 0) {

                $mensaje = "No se puede eliminar el curso porque tiene una carga académica asignada.";
                $tipoMensaje = "warning";

            } else {

                $sqlEliminar = "
                    DELETE FROM cursos
                    WHERE id = ?
                ";

                $stmtEliminar = $conexion->prepare($sqlEliminar);


                if ($stmtEliminar) {

                    $stmtEliminar->bind_param("i", $curso_id);


                    if ($stmtEliminar->execute()) {

                        $mensaje = "Curso eliminado correctamente.";
                        $tipoMensaje = "success";

                    } else {

                        $mensaje = "No se pudo eliminar el curso.";
                        $tipoMensaje = "danger";
                    }


                    $stmtEliminar->close();
                }
            }


            $stmt->close();
        }
    }
}


// ==========================================
// OBTENER CURSOS
// ==========================================

$sqlCursos = "
    SELECT
        id,
        nombre
    FROM cursos
    ORDER BY nombre ASC
";

$resultadoCursos = $conexion->query($sqlCursos);

?>


<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Administrar cursos</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>


<?php include("../../includes/navbar.php"); ?>


<div class="container-fluid">

    <div class="row">


        <!-- SIDEBAR -->

        <div class="col-md-2 p-0">

            <?php include("../../includes/sidebar.php"); ?>

        </div>


        <!-- CONTENIDO -->

        <div class="col-md-10">

            <div class="container mt-4">


                <h2>
                    Administración de cursos
                </h2>

                <p class="text-muted">
                    Crea y administra los cursos del sistema.
                </p>

                <hr>


                <!-- MENSAJE -->

                <?php if ($mensaje !== ""): ?>

                    <div class="alert alert-<?= htmlspecialchars($tipoMensaje) ?>">

                        <?= htmlspecialchars($mensaje) ?>

                    </div>

                <?php endif; ?>


                <!-- CREAR CURSO -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">
                            Crear nuevo curso
                        </h5>

                    </div>


                    <div class="card-body">

                        <form method="POST">

                            <div class="row align-items-end">


                                <div class="col-md-8">

                                    <label class="form-label">

                                        Nombre del curso

                                    </label>

                                    <input
                                        type="text"
                                        name="nombre"
                                        class="form-control"
                                        placeholder="Ejemplo: 10-A"
                                        required
                                    >

                                </div>


                                <div class="col-md-4">

                                    <button
                                        type="submit"
                                        name="crear_curso"
                                        class="btn btn-primary w-100"
                                    >

                                        Crear curso

                                    </button>

                                </div>


                            </div>

                        </form>

                    </div>

                </div>


                <!-- LISTADO -->

                <div class="card shadow">

                    <div class="card-header bg-dark text-white">

                        <h5 class="mb-0">
                            Cursos registrados
                        </h5>

                    </div>


                    <div class="card-body p-0">


                        <?php if ($resultadoCursos && $resultadoCursos->num_rows > 0): ?>


                            <div class="table-responsive">

                                <table class="table table-bordered table-hover mb-0">

                                    <thead class="table-light">

                                        <tr>

                                            <th width="80">
                                                #
                                            </th>

                                            <th>
                                                Curso
                                            </th>

                                            <th width="150">
                                                Acción
                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                        <?php

                                        $numero = 1;

                                        while ($curso = $resultadoCursos->fetch_assoc()):

                                        ?>

                                            <tr>

                                                <td>
                                                    <?= $numero++ ?>
                                                </td>

                                                <td>
                                                    <?= htmlspecialchars($curso["nombre"]) ?>
                                                </td>

                                                <td>

                                                    <form
                                                        method="POST"
                                                        onsubmit="return confirm('¿Está seguro de eliminar este curso?');"
                                                    >

                                                        <input
                                                            type="hidden"
                                                            name="curso_id"
                                                            value="<?= (int) $curso["id"] ?>"
                                                        >

                                                        <button
                                                            type="submit"
                                                            name="eliminar_curso"
                                                            class="btn btn-danger btn-sm"
                                                        >

                                                            Eliminar

                                                        </button>

                                                    </form>

                                                </td>

                                            </tr>

                                        <?php endwhile; ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>

                            <div class="alert alert-info m-3">

                                No hay cursos registrados todavía.

                            </div>

                        <?php endif; ?>


                    </div>

                </div>


            </div>

        </div>

    </div>

</div>


<?php include("../../includes/footer.php"); ?>


</body>

</html>