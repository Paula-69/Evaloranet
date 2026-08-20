<?php

session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/seguridad/coordinador.php";

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

                <h2 class="mb-4">
                    Carga académica
                </h2>


                <!-- MENSAJES -->

                <?php if (isset($_GET["success"])): ?>

                    <div class="alert alert-success">
                        <?= htmlspecialchars($_GET["success"]) ?>
                    </div>

                <?php endif; ?>


                <?php if (isset($_GET["error"])): ?>

                    <div class="alert alert-danger">
                        <?= htmlspecialchars($_GET["error"]) ?>
                    </div>

                <?php endif; ?>


                <!-- FORMULARIO -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">
                            Asignar carga académica
                        </h5>

                    </div>


                    <div class="card-body">

                        <form action="guardar.php" method="POST">


                            <!-- DOCENTE -->

                            <div class="mb-3">

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


                                    <?php

                                    $sql = "
                                        SELECT
                                            d.id,
                                            u.nombres,
                                            u.apellidos,
                                            u.documento

                                        FROM docentes d

                                        INNER JOIN usuarios u
                                            ON d.usuario_id = u.id

                                        WHERE d.estado = 'Activo'

                                        ORDER BY u.apellidos, u.nombres
                                    ";

                                    $resultado = $conexion->query($sql);


                                    if ($resultado):

                                        while ($docente = $resultado->fetch_assoc()):

                                    ?>

                                        <option value="<?= $docente["id"] ?>">

                                            <?= htmlspecialchars(
                                                $docente["apellidos"] . " " .
                                                $docente["nombres"] .
                                                " - " .
                                                $docente["documento"]
                                            ) ?>

                                        </option>

                                    <?php

                                        endwhile;

                                    endif;

                                    ?>

                                </select>

                            </div>


                            <!-- CURSO -->

                            <div class="mb-3">

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


                                    <?php

                                    $resultado = $conexion->query(
                                        "SELECT id, nombre
                                         FROM cursos
                                         ORDER BY nombre"
                                    );


                                    if ($resultado):

                                        while ($curso = $resultado->fetch_assoc()):

                                    ?>

                                        <option value="<?= $curso["id"] ?>">

                                            <?= htmlspecialchars($curso["nombre"]) ?>

                                        </option>

                                    <?php

                                        endwhile;

                                    endif;

                                    ?>

                                </select>

                            </div>


                            <!-- MATERIA -->

                            <div class="mb-3">

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


                                    <?php

                                    $resultado = $conexion->query(
                                        "SELECT id, nombre
                                         FROM materias
                                         ORDER BY nombre"
                                    );


                                    if ($resultado):

                                        while ($materia = $resultado->fetch_assoc()):

                                    ?>

                                        <option value="<?= $materia["id"] ?>">

                                            <?= htmlspecialchars($materia["nombre"]) ?>

                                        </option>

                                    <?php

                                        endwhile;

                                    endif;

                                    ?>

                                </select>

                            </div>


                            <button
                                type="submit"
                                class="btn btn-primary"
                            >

                                Guardar asignación

                            </button>

                        </form>

                    </div>

                </div>


                <!-- LISTADO -->

                <div class="card shadow">

                    <div class="card-header bg-dark text-white">

                        <h5 class="mb-0">
                            Cargas académicas asignadas
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
                                            Docente
                                        </th>

                                        <th>
                                            Curso
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

                                $sql = "
                                    SELECT

                                        ca.id,

                                        CONCAT(
                                            u.nombres,
                                            ' ',
                                            u.apellidos
                                        ) AS docente,

                                        c.nombre AS curso,

                                        m.nombre AS materia

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
                                        u.apellidos,
                                        c.nombre,
                                        m.nombre
                                ";


                                $resultado = $conexion->query($sql);


                                if ($resultado && $resultado->num_rows > 0):

                                    $contador = 1;


                                    while ($fila = $resultado->fetch_assoc()):

                                ?>

                                    <tr>

                                        <td>
                                            <?= $contador++ ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($fila["docente"]) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($fila["curso"]) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars($fila["materia"]) ?>
                                        </td>

                                        <td>

                                            <a
                                                href="eliminar.php?id=<?= $fila["id"] ?>"
                                                class="btn btn-danger btn-sm"
                                                onclick="return confirm('¿Desea eliminar esta asignación?');"
                                            >

                                                Eliminar

                                            </a>

                                        </td>

                                    </tr>

                                <?php

                                    endwhile;

                                else:

                                ?>

                                    <tr>

                                        <td
                                            colspan="5"
                                            class="text-center"
                                        >

                                            No hay cargas académicas asignadas.

                                        </td>

                                    </tr>

                                <?php endif; ?>

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