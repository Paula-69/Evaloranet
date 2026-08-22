<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/docente.php");


/*
|--------------------------------------------------------------------------
| Validar datos
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");

    exit();

}


$curso_id = intval($_POST["curso_id"] ?? 0);
$materia_id = intval($_POST["materia_id"] ?? 0);

$colores = $_POST["color"] ?? [];


if ($curso_id <= 0 || $materia_id <= 0) {

    header(
        "Location: index.php?error=Datos inválidos."
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Obtener docente
|--------------------------------------------------------------------------
*/

$usuario_id = $_SESSION["id"];

$sql = "
    SELECT id
    FROM docentes
    WHERE usuario_id = ?
    LIMIT 1
";

$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $usuario_id
);

$stmt->execute();

$resultado = $stmt->get_result();


if ($resultado->num_rows == 0) {

    die("No se encontró el docente.");

}

$docente = $resultado->fetch_assoc();

$docente_id = $docente["id"];


/*
|--------------------------------------------------------------------------
| Verificar que el docente tenga esa carga académica
|--------------------------------------------------------------------------
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


if ($resultado->num_rows == 0) {

    header(
        "Location: index.php?error=No tienes asignada esta materia."
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Preparar INSERT / UPDATE
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO seguimiento_desempeno
    (
        estudiante_id,
        docente_id,
        curso_id,
        materia_id,
        color_id
    )

    VALUES (?, ?, ?, ?, ?)

    ON DUPLICATE KEY UPDATE

        color_id = VALUES(color_id),

        fecha_actualizacion = CURRENT_TIMESTAMP
";

$stmt = $conexion->prepare($sql);


/*
|--------------------------------------------------------------------------
| Guardar cada estudiante
|--------------------------------------------------------------------------
*/

foreach ($colores as $estudiante_id => $color_id) {

    $estudiante_id = intval($estudiante_id);
    $color_id = intval($color_id);


    /*
     * Solo permitimos los colores
     * existentes en colores_desempeno.
     */

    if (
        $estudiante_id <= 0 ||
        !in_array($color_id, [1, 2, 3])
    ) {

        continue;

    }


    /*
     * Verificar que el estudiante
     * pertenezca al curso seleccionado.
     */

    $sql_verificar = "
        SELECT id
        FROM estudiantes
        WHERE id = ?
        AND curso_id = ?
        AND estado = 'Activo'
        LIMIT 1
    ";

    $verificar = $conexion->prepare($sql_verificar);

    $verificar->bind_param(
        "ii",
        $estudiante_id,
        $curso_id
    );

    $verificar->execute();

    $resultado_estudiante =
        $verificar->get_result();


    if ($resultado_estudiante->num_rows == 0) {

        continue;

    }


    /*
     * Guardar seguimiento.
     */

    $stmt->bind_param(
        "iiiii",
        $estudiante_id,
        $docente_id,
        $curso_id,
        $materia_id,
        $color_id
    );

    $stmt->execute();

}


/*
|--------------------------------------------------------------------------
| Volver
|--------------------------------------------------------------------------
*/

header(
    "Location: index.php"
    . "?curso_id=" . $curso_id
    . "&materia_id=" . $materia_id
    . "&success="
    . urlencode("Seguimiento guardado correctamente.")
);

exit();