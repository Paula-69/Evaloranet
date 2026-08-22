<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");

include("../../includes/header.php");
include("../../includes/navbar.php");

?>

<div class="container-fluid">

    <div class="row">

        <!-- SIDEBAR -->

        <div class="col-md-2 p-0">

            <?php include("../../includes/sidebar.php"); ?>

        </div>


        <!-- CONTENIDO -->

        <div class="col-md-10">

            <div class="container mt-4">

                <h2>Administrar materias</h2>

                <p class="text-muted">

                    Aquí puedes registrar y consultar las materias
                    que serán utilizadas en la carga académica.

                </p>

                <hr>


                <?php

                /*
                |--------------------------------------------------------------------------
                | PROCESAR ELIMINACIÓN
                |--------------------------------------------------------------------------
                */

                if (
                    isset($_GET["eliminar"]) &&
                    is_numeric($_GET["eliminar"])
                ) {

                    $id = (int) $_GET["eliminar"];

                    $sql = "DELETE FROM materias WHERE id = ?";

                    $stmt = $conexion->prepare($sql);

                    $stmt->bind_param("i", $id);

                    if ($stmt->execute()) {

                        echo '<div class="alert alert-success">
                                Materia eliminada correctamente.
                              </div>';

                    } else {

                        echo '<div class="alert alert-danger">
                                No se pudo eliminar la materia.
                              </div>';

                    }

                    $stmt->close();

                }


                /*
                |--------------------------------------------------------------------------
                | REGISTRAR MATERIA
                |--------------------------------------------------------------------------
                */

                if ($_SERVER["REQUEST_METHOD"] === "POST") {

                    $nombre = trim($_POST["nombre"] ?? "");

                    if ($nombre === "") {

                        echo '<div class="alert alert-danger">
                                Debes escribir el nombre de la materia.
                              </div>';

                    } else {

                        $sql = "SELECT id
                                FROM materias
                                WHERE nombre = ?
                                LIMIT 1";

                        $stmt = $conexion->prepare($sql);

                        $stmt->bind_param("s", $nombre);

                        $stmt->execute();

                        $resultado = $stmt->get_result();


                        if ($resultado->num_rows > 0) {

                            echo '<div class="alert alert-warning">
                                    Esta materia ya está registrada.
                                  </div>';

                        } else {

                            $stmt->close();


                            $sql = "INSERT INTO materias (nombre)
                                    VALUES (?)";

                            $stmt = $conexion->prepare($sql);

                            $stmt->bind_param("s", $nombre);


                            if ($stmt->execute()) {

                                echo '<div class="alert alert-success">
                                        Materia registrada correctamente.
                                      </div>';

                            } else {

                                echo '<div class="alert alert-danger">
                                        Error al registrar la materia.
                                      </div>';

                            }

                        }

                        $stmt->close();

                    }

                }

                ?>


                <!-- FORMULARIO -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            Registrar nueva materia

                        </h5>

                    </div>


                    <div class="card-body">

                        <form method="POST">

                            <div class="row">

                                <div class="col-md-8">

                                    <label class="form-label">

                                        Nombre de la materia

                                    </label>

                                    <input
                                        type="text"
                                        name="nombre"
                                        class="form-control"
                                        placeholder="Ejemplo: Matemáticas"
                                        required
                                    >

                                </div>


                                <div class="col-md-4 d-flex align-items-end">

                                    <button
                                        type="submit"
                                        class="btn btn-primary w-100"
                                    >

                                        Registrar materia

                                    </button>

                                </div>

                            </div>

                        </form>

                    </div>

                </div>


                <!-- LISTA -->

                <div class="card shadow">

                    <div class="card-header bg-success text-white">

                        <h5 class="mb-0">

                            Materias registradas

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="table-responsive">

                            <table class="table table-bordered table-striped">

                                <thead>

                                    <tr>

                                        <th>
                                            #
                                        </th>

                                        <th>
                                            Materia
                                        </th>

                                        <th>
                                            Acción
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php

                                $sql = "SELECT *
                                        FROM materias
                                        ORDER BY nombre ASC";

                                $resultado = $conexion->query($sql);


                                if ($resultado->num_rows > 0) {

                                    $contador = 1;


                                    while (
                                        $materia =
                                        $resultado->fetch_assoc()
                                    ) {

                                ?>

                                    <tr>

                                        <td>

                                            <?= $contador++ ?>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $materia["nombre"]
                                            ) ?>

                                        </td>


                                        <td>

                                            <a
                                                href="?eliminar=<?= $materia["id"] ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Seguro que deseas eliminar esta materia?');"
                                            >

                                                Eliminar

                                            </a>

                                        </td>

                                    </tr>

                                <?php

                                    }

                                } else {

                                ?>

                                    <tr>

                                        <td
                                            colspan="3"
                                            class="text-center"
                                        >

                                            No hay materias registradas.

                                        </td>

                                    </tr>

                                <?php

                                }

                                ?>

                                </tbody>

                            </table>

                        </div>

                    </div>

                </div>

            </div>

        </div>

    </div>

</div>


<?php

include("../../includes/footer.php");

?>