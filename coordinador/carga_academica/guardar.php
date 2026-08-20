<?php

session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/seguridad/coordinador.php";


if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: index.php");
    exit();

}


$docente_id = intval($_POST["docente_id"] ?? 0);
$curso_id   = intval($_POST["curso_id"] ?? 0);
$materia_id = intval($_POST["materia_id"] ?? 0);


if (
    $docente_id <= 0 ||
    $curso_id <= 0 ||
    $materia_id <= 0
) {

    header(
        "Location: index.php?error=" .
        urlencode("Debe seleccionar docente, curso y materia.")
    );

    exit();

}


/*
|--------------------------------------------------------------------------
| Verificar que no exista la misma asignación
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


if ($resultado->num_rows > 0) {

    $stmt->close();

    header(
        "Location: index.php?error=" .
        urlencode("Esta carga académica ya existe.")
    );

    exit();

}

$stmt->close();


/*
|--------------------------------------------------------------------------
| Guardar
|--------------------------------------------------------------------------
*/

$sql = "
    INSERT INTO carga_academica
    (
        docente_id,
        curso_id,
        materia_id
    )

    VALUES (?, ?, ?)
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "iii",
    $docente_id,
    $curso_id,
    $materia_id
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success=" .
        urlencode("Carga académica asignada correctamente.")
    );

    exit();

}


$error = $stmt->error;

$stmt->close();


header(
    "Location: index.php?error=" .
    urlencode("No fue posible guardar la asignación: " . $error)
);

exit();