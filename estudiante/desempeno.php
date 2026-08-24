<?php

require_once("../config/config.php");
require_once("../config/seguridad/estudiante.php");

include("../includes/header.php");
include("../includes/navbar.php");


// =====================================================
// BUSCAR EL ESTUDIANTE RELACIONADO CON EL USUARIO
// =====================================================

$usuario_id = $_SESSION["id"];

$sql = "
    SELECT 
        e.id AS estudiante_id,
        e.curso_id,
        c.nombre AS curso
    FROM estudiantes e
    INNER JOIN cursos c 
        ON e.curso_id = c.id
    WHERE e.usuario_id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $usuario_id);
$stmt->execute();

$resultado = $stmt->get_result();


// =====================================================
// VERIFICAR QUE EXISTA EL ESTUDIANTE
// =====================================================

if ($resultado->num_rows === 0) {

    ?>

    <div class="container mt-4">

        <div class="alert alert-danger">

            No se encontró información académica
            asociada a este estudiante.

        </div>

    </div>

    <?php

    include("../includes/footer.php");
    exit();
}


$estudiante = $resultado->fetch_assoc();

$estudiante_id = $estudiante["estudiante_id"];
$curso_id      = $estudiante["curso_id"];
$curso         = $estudiante["curso"];

$stmt->close();


// =====================================================
// BUSCAR DESEMPEÑO DEL ESTUDIANTE
// =====================================================

$sql = "
    SELECT

        m.nombre AS materia,

        cd.desempeno,

        cd.color,

        ds.fecha_registro

    FROM desempeno_estudiantes ds

    INNER JOIN carga_academica ca
        ON ds.carga_academica_id = ca.id

    INNER JOIN materias m
        ON ca.materia_id = m.id

    INNER JOIN colores_desempeno cd
        ON ds.color_id = cd.id

    WHERE ds.estudiante_id = ?

    ORDER BY m.nombre
";

$stmt = $conexion->prepare($sql);
$stmt->bind_param("i", $estudiante_id);
$stmt->execute();

$resultado = $stmt->get_result();

?>

<div class="container-fluid">

    <div class="row">

        <!-- MENÚ LATERAL -->

        <div class="col-md-2 p-0">

            <?php include("../includes/sidebar.php"); ?>

        </div>


        <!-- CONTENIDO -->

        <div class="col-md-10">

            <div class="container mt-4">


                <!-- ENCABEZADO -->

                <div class="mb-4">

                    <h2>
                        Mi desempeño académico
                    </h2>

                    <h5 class="text-muted">

                        Estudiante:
                        <?= htmlspecialchars($_SESSION["nombre"]) ?>

                    </h5>

                    <h6 class="text-muted">

                        Curso:
                        <?= htmlspecialchars($curso) ?>

                    </h6>

                </div>


                <!-- LEYENDA -->

                <div class="card shadow mb-4">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">
                            Semáforo de desempeño
                        </h5>

                    </div>

                    <div class="card-body">

                        <div class="d-flex gap-4 flex-wrap">

                            <div>

                                <span
                                    class="badge"
                                    style="
                                        background-color:#dc3545;
                                        font-size:15px;
                                    "
                                >
                                    Bajo
                                </span>

                            </div>


                            <div>

                                <span
                                    class="badge"
                                    style="
                                        background-color:#ffc107;
                                        color:#000;
                                        font-size:15px;
                                    "
                                >
                                    Básico
                                </span>

                            </div>


                            <div>

                                <span
                                    class="badge"
                                    style="
                                        background-color:#198754;
                                        font-size:15px;
                                    "
                                >
                                    Alto
                                </span>

                            </div>

                        </div>

                    </div>

                </div>


                <!-- TABLA DE DESEMPEÑO -->

                <div class="card shadow">

                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            Desempeño por materia

                        </h5>

                    </div>


                    <div class="card-body">

                        <div class="table-responsive">

                            <table
                                class="table table-bordered table-striped align-middle"
                            >

                                <thead>

                                    <tr>

                                        <th>
                                            #
                                        </th>

                                        <th>
                                            Materia
                                        </th>

                                        <th class="text-center">
                                            Desempeño
                                        </th>

                                        <th class="text-center">
                                            Último registro
                                        </th>

                                    </tr>

                                </thead>


                                <tbody>

                                <?php

                                if ($resultado->num_rows > 0) {

                                    $contador = 1;


                                    while (
                                        $fila =
                                        $resultado->fetch_assoc()
                                    ) {

                                        $color = $fila["color"];

                                ?>

                                    <tr>

                                        <td>

                                            <?= $contador++ ?>

                                        </td>


                                        <td>

                                            <?= htmlspecialchars(
                                                $fila["materia"]
                                            ) ?>

                                        </td>


                                        <td class="text-center">

                                            <span
                                                class="badge"
                                                style="
                                                    background-color:
                                                    <?= htmlspecialchars($color) ?>;
                                                    font-size:15px;
                                                    padding:8px 15px;
                                                "
                                            >

                                                <?= htmlspecialchars(
                                                    ucfirst(
                                                        $fila["desempeno"]
                                                    )
                                                ) ?>

                                            </span>

                                        </td>


                                        <td class="text-center">

                                            <?= htmlspecialchars(
                                                $fila["fecha_registro"]
                                            ) ?>

                                        </td>

                                    </tr>

                                <?php

                                    }

                                } else {

                                ?>

                                    <tr>

                                        <td
                                            colspan="4"
                                            class="text-center"
                                        >

                                            <div class="alert alert-info mb-0">

                                                Todavía no hay registros
                                                de desempeño para este
                                                estudiante.

                                            </div>

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

$stmt->close();

include("../includes/footer.php");

?>