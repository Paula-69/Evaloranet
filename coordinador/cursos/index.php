<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");


// =====================================================
// PROCESAR CREACIÓN DEL CURSO
// =====================================================

$mensaje = "";
$tipo_mensaje = "";

$nombre = "";


if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $nombre = trim($_POST["nombre"] ?? "");


    // =================================================
    // VALIDAR CAMPO VACÍO
    // =================================================

    if ($nombre === "") {

        $mensaje = "Debes ingresar el nombre del curso.";
        $tipo_mensaje = "danger";

    }


    // =================================================
    // VALIDAR FORMATO DEL CURSO
    // =================================================

    elseif (!preg_match('/^[0-9]{3,4}$/', $nombre)) {

        $mensaje =
            "El curso debe tener 3 o 4 números. " .
            "Ejemplos: 601, 602, 701, 1001, 1101.";

        $tipo_mensaje = "danger";

    }


    else {

        // =================================================
        // COMPROBAR SI YA EXISTE
        // =================================================

        $sql = "
            SELECT id
            FROM cursos
            WHERE nombre = ?
            LIMIT 1
        ";

        $stmt = $conexion->prepare($sql);


        if (!$stmt) {

            $mensaje =
                "No se pudo realizar la consulta.";

            $tipo_mensaje = "danger";

        } else {

            $stmt->bind_param(
                "s",
                $nombre
            );

            $stmt->execute();

            $resultado = $stmt->get_result();


            // =================================================
            // CURSO YA EXISTENTE
            // =================================================

            if ($resultado->num_rows > 0) {

                $mensaje =
                    "El curso " .
                    htmlspecialchars($nombre) .
                    " ya existe.";

                $tipo_mensaje = "warning";

            }


            // =================================================
            // CREAR CURSO
            // =================================================

            else {

                $stmt->close();


                $sqlInsert = "
                    INSERT INTO cursos (nombre)
                    VALUES (?)
                ";

                $stmtInsert =
                    $conexion->prepare($sqlInsert);


                if (!$stmtInsert) {

                    $mensaje =
                        "No se pudo preparar el registro del curso.";

                    $tipo_mensaje = "danger";

                } else {

                    $stmtInsert->bind_param(
                        "s",
                        $nombre
                    );


                    if ($stmtInsert->execute()) {

                        $mensaje =
                            "El curso " .
                            htmlspecialchars($nombre) .
                            " fue creado correctamente.";

                        $tipo_mensaje = "success";

                        // Limpiar campo
                        $nombre = "";

                    } else {

                        $mensaje =
                            "No se pudo guardar el curso.";

                        $tipo_mensaje = "danger";

                    }


                    $stmtInsert->close();

                }

            }


            if (isset($stmt)) {

                $stmt->close();

            }

        }

    }

}


// =====================================================
// OBTENER CURSOS
// =====================================================

$sqlCursos = "
    SELECT
        id,
        nombre
    FROM cursos
    ORDER BY nombre ASC
";

$resultadoCursos =
    $conexion->query($sqlCursos);


// =====================================================
// HEADER
// =====================================================

include("../../includes/header.php");

include("../../includes/navbar.php");

?>


<div class="container-fluid">

    <div class="row">


        <!-- =====================================================
             SIDEBAR DEL COORDINADOR
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

                            Crear cursos

                        </h2>


                        <p class="text-muted mb-0">

                            Administra los cursos de la institución.

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
                     MENSAJES
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
                     FORMULARIO
                ================================================== -->

                <div class="card shadow mb-4">


                    <div
                        class="card-header bg-primary text-white"
                    >

                        <h4 class="mb-0">

                            Nuevo curso

                        </h4>

                    </div>


                    <div class="card-body">


                        <form
                            action="index.php"
                            method="POST"
                        >


                            <!-- =====================================
                                 NOMBRE DEL CURSO
                            ====================================== -->

                            <div class="mb-3">

                                <label
                                    for="nombre"
                                    class="form-label"
                                >

                                    Nombre del curso

                                </label>


                                <input
                                    type="text"
                                    name="nombre"
                                    id="nombre"
                                    class="form-control"
                                    placeholder="Ejemplo: 601"
                                    pattern="[0-9]{3,4}"
                                    minlength="3"
                                    maxlength="4"
                                    inputmode="numeric"
                                    autocomplete="off"
                                    value="<?= htmlspecialchars(
                                        $nombre
                                    ) ?>"
                                    required
                                >


                                <div class="form-text">

                                    Ingresa el curso utilizando
                                    el formato de tu colegio.

                                    <strong>

                                        Ejemplos:
                                        601, 602, 603, 701,
                                        702, 801, 901, 1001, 1101

                                    </strong>

                                </div>

                            </div>


                            <!-- =====================================
                                 BOTONES
                            ====================================== -->

                            <div class="mt-4">


                                <button
                                    type="submit"
                                    class="btn btn-success"
                                >

                                    Guardar curso

                                </button>


                                <a
                                    href="index.php"
                                    class="btn btn-secondary"
                                >

                                    Cancelar

                                </a>


                                <a
                                    href="../index.php"
                                    class="btn btn-dark"
                                >

                                    ← Volver al dashboard

                                </a>


                            </div>


                        </form>

                    </div>

                </div>


                <!-- =================================================
                     LISTA DE CURSOS
                ================================================== -->

                <div class="card shadow">


                    <div
                        class="card-header bg-dark text-white"
                    >

                        <h5 class="mb-0">

                            Cursos registrados

                        </h5>

                    </div>


                    <div class="card-body">


                        <?php

                        if (
                            $resultadoCursos &&
                            $resultadoCursos->num_rows > 0
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
                                                style="width:100px;"
                                            >

                                                #

                                            </th>


                                            <th>

                                                Curso

                                            </th>

                                        </tr>

                                    </thead>


                                    <tbody>


                                    <?php

                                    $contador = 1;


                                    while (
                                        $curso =
                                        $resultadoCursos->fetch_assoc()
                                    ):

                                    ?>


                                        <tr>

                                            <td>

                                                <?= $contador++ ?>

                                            </td>


                                            <td>

                                                <strong>

                                                    <?= htmlspecialchars(
                                                        $curso["nombre"]
                                                    ) ?>

                                                </strong>

                                            </td>

                                        </tr>


                                    <?php

                                    endwhile;

                                    ?>


                                    </tbody>

                                </table>

                            </div>


                        <?php else: ?>


                            <div class="alert alert-info mb-0">

                                No hay cursos registrados todavía.

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