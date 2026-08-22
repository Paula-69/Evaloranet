<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/docente.php");

include("../../includes/header.php");
include("../../includes/navbar.php");

$usuario_id = $_SESSION["id"];

/*
|--------------------------------------------------------------------------
| Obtener el docente
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT id
    FROM docentes
    WHERE usuario_id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();

if ($resultado->num_rows == 0) {
    die("No se encontró el docente asociado al usuario.");
}

$docente = $resultado->fetch_assoc();
$docente_id = $docente["id"];


/*
|--------------------------------------------------------------------------
| Obtener cursos y materias asignados al docente
|--------------------------------------------------------------------------
*/

$sql = "
    SELECT
        ca.curso_id,
        ca.materia_id,
        c.nombre AS curso,
        m.nombre AS materia

    FROM carga_academica ca

    INNER JOIN cursos c
        ON c.id = ca.curso_id

    INNER JOIN materias m
        ON m.id = ca.materia_id

    WHERE ca.docente_id = ?

    ORDER BY c.nombre, m.nombre
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $docente_id);
$stmt->execute();

$asignaciones = $stmt->get_result();


/*
|--------------------------------------------------------------------------
| Valores seleccionados
|--------------------------------------------------------------------------
*/

$curso_id = isset($_GET["curso_id"])
    ? intval($_GET["curso_id"])
    : 0;

$materia_id = isset($_GET["materia_id"])
    ? intval($_GET["materia_id"])
    : 0;

?>

<div class="container mt-4">

    <h2>
        Seguimiento académico
    </h2>

    <p class="text-muted">
        Registra únicamente el nivel de desempeño mediante el semáforo.
    </p>

    <hr>


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


    <!-- SELECCIÓN DE CURSO Y MATERIA -->

    <form method="GET" class="card shadow-sm p-4 mb-4">

        <div class="row">

            <div class="col-md-5">

                <label class="form-label">
                    Curso y materia
                </label>

                <select
                    name="asignacion"
                    class="form-select"
                    onchange="cambiarAsignacion(this)"
                    required
                >

                    <option value="">
                        Seleccione...
                    </option>

                    <?php

                    /*
                     * Reiniciamos el puntero para poder
                     * recorrer las asignaciones.
                     */

                    $asignaciones->data_seek(0);

                    while ($asignacion = $asignaciones->fetch_assoc()):

                        $valor =
                            $asignacion["curso_id"]
                            . "-"
                            . $asignacion["materia_id"];

                        $seleccionado =
                            (
                                $curso_id == $asignacion["curso_id"]
                                &&
                                $materia_id == $asignacion["materia_id"]
                            )
                            ? "selected"
                            : "";

                    ?>

                        <option
                            value="<?= $valor ?>"
                            <?= $seleccionado ?>
                        >

                            <?= htmlspecialchars($asignacion["curso"]) ?>
                            -
                            <?= htmlspecialchars($asignacion["materia"]) ?>

                        </option>

                    <?php endwhile; ?>

                </select>

            </div>

        </div>

    </form>


<?php

/*
|--------------------------------------------------------------------------
| Mostrar estudiantes
|--------------------------------------------------------------------------
*/

if ($curso_id > 0 && $materia_id > 0):

    /*
     * Verificar que esta asignación realmente
     * pertenece al docente.
     */

    $sql = "
        SELECT id
        FROM carga_academica
        WHERE docente_id = ?
        AND curso_id = ?
        AND materia_id = ?
        LIMIT 1
    ";

    $stmt = $conexion->prepare($sql);

    $stmt->bind_param(
        "iii",
        $docente_id,
        $curso_id,
        $materia_id
    );

    $stmt->execute();

    $resultado = $stmt->get_result();

    if ($resultado->num_rows == 0):

?>

        <div class="alert alert-danger">

            Esta asignación no pertenece al docente.

        </div>

<?php

    else:

        /*
        |--------------------------------------------------------------------------
        | Obtener estudiantes
        |--------------------------------------------------------------------------
        */

        $sql = "
            SELECT
                e.id,
                u.nombres,
                u.apellidos,
                sd.color_id,
                cd.desempeno,
                cd.color

            FROM estudiantes e

            INNER JOIN usuarios u
                ON u.id = e.usuario_id

            LEFT JOIN seguimiento_desempeno sd
                ON sd.estudiante_id = e.id
                AND sd.docente_id = ?
                AND sd.curso_id = ?
                AND sd.materia_id = ?

            LEFT JOIN colores_desempeno cd
                ON cd.id = sd.color_id

            WHERE e.curso_id = ?
            AND e.estado = 'Activo'

            ORDER BY u.apellidos, u.nombres
        ";

        $stmt = $conexion->prepare($sql);

        $stmt->bind_param(
            "iiii",
            $docente_id,
            $curso_id,
            $materia_id,
            $curso_id
        );

        $stmt->execute();

        $estudiantes = $stmt->get_result();

?>

        <form
            action="guardar.php"
            method="POST"
        >

            <input
                type="hidden"
                name="curso_id"
                value="<?= $curso_id ?>"
            >

            <input
                type="hidden"
                name="materia_id"
                value="<?= $materia_id ?>"
            >


            <div class="card shadow-sm">

                <div class="card-header bg-primary text-white">

                    <h5 class="mb-0">
                        Estudiantes
                    </h5>

                </div>


                <div class="card-body p-0">

                    <div class="table-responsive">

                        <table class="table table-hover mb-0">

                            <thead>

                                <tr>

                                    <th>
                                        Estudiante
                                    </th>

                                    <th class="text-center">
                                        Desempeño
                                    </th>

                                </tr>

                            </thead>


                            <tbody>

                            <?php

                            if ($estudiantes->num_rows == 0):

                            ?>

                                <tr>

                                    <td
                                        colspan="2"
                                        class="text-center text-muted p-4"
                                    >

                                        No hay estudiantes activos
                                        en este curso.

                                    </td>

                                </tr>

                            <?php

                            else:

                                while ($estudiante = $estudiantes->fetch_assoc()):

                                    $color_actual =
                                        $estudiante["color_id"];

                            ?>

                                <tr>

                                    <td>

                                        <strong>

                                            <?= htmlspecialchars(
                                                $estudiante["apellidos"]
                                            ) ?>

                                            ,

                                            <?= htmlspecialchars(
                                                $estudiante["nombres"]
                                            ) ?>

                                        </strong>

                                    </td>


                                    <td>

                                        <div class="d-flex justify-content-center gap-2">

                                            <!-- ROJO -->

                                            <label
                                                class="btn btn-outline-danger"
                                            >

                                                <input
                                                    type="radio"
                                                    class="btn-check"
                                                    name="color[<?= $estudiante["id"] ?>]"
                                                    value="1"
                                                    <?= $color_actual == 1 ? "checked" : "" ?>
                                                >

                                                🔴 Bajo

                                            </label>


                                            <!-- AMARILLO -->

                                            <label
                                                class="btn btn-outline-warning"
                                            >

                                                <input
                                                    type="radio"
                                                    class="btn-check"
                                                    name="color[<?= $estudiante["id"] ?>]"
                                                    value="2"
                                                    <?= $color_actual == 2 ? "checked" : "" ?>
                                                >

                                                🟡 Básico

                                            </label>


                                            <!-- VERDE -->

                                            <label
                                                class="btn btn-outline-success"
                                            >

                                                <input
                                                    type="radio"
                                                    class="btn-check"
                                                    name="color[<?= $estudiante["id"] ?>]"
                                                    value="3"
                                                    <?= $color_actual == 3 ? "checked" : "" ?>
                                                >

                                                🟢 Alto

                                            </label>

                                        </div>

                                    </td>

                                </tr>

                            <?php

                                endwhile;

                            endif;

                            ?>

                            </tbody>

                        </table>

                    </div>

                </div>


                <?php if ($estudiantes->num_rows > 0): ?>

                    <div class="card-footer text-end">

                        <button
                            type="submit"
                            class="btn btn-primary"
                        >

                            Guardar seguimiento

                        </button>

                    </div>

                <?php endif; ?>

            </div>

        </form>

<?php

    endif;

endif;

?>

</div>


<script>

function cambiarAsignacion(select) {

    if (!select.value) {
        return;
    }

    const partes = select.value.split("-");

    const curso = partes[0];
    const materia = partes[1];

    window.location.href =
        "index.php?curso_id="
        + curso
        + "&materia_id="
        + materia;
}

</script>


<?php

include("../../includes/footer.php");

?>