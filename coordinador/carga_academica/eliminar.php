<?php

session_start();

require_once __DIR__ . "/../../config/config.php";
require_once __DIR__ . "/../../config/seguridad/coordinador.php";


$id = intval($_GET["id"] ?? 0);


if ($id <= 0) {

    header(
        "Location: index.php?error=" .
        urlencode("Asignación no válida.")
    );

    exit();

}


$sql = "
    DELETE FROM carga_academica
    WHERE id = ?
";


$stmt = $conexion->prepare($sql);

$stmt->bind_param(
    "i",
    $id
);


if ($stmt->execute()) {

    $stmt->close();

    header(
        "Location: index.php?success=" .
        urlencode("Asignación eliminada correctamente.")
    );

    exit();

}


$error = $stmt->error;

$stmt->close();


header(
    "Location: index.php?error=" .
    urlencode("No fue posible eliminar la asignación: " . $error)
);

exit();