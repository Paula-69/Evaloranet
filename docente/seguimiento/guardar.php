<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/docente.php");


// ======================================================
// SOLO PERMITIR POST
// ======================================================

if ($_SERVER["REQUEST_METHOD"] !== "POST") {

    header("Location: ../index.php");
    exit();

}


// ======================================================
// DATOS RECIBIDOS DESDE DESEMPEÑO
// ======================================================

$curso_id = intval(
    $_POST["curso_id"] ?? 0
);

$materia_id = intval(
    $_POST["materia_id"] ?? 0
);

$colores = $_POST["color"] ?? [];


// ======================================================
// VALIDAR DATOS
// ======================================================

if (
    $curso_id <= 0 ||
    $materia_id <= 0 ||
    !is_array($colores)
) {

    header(
        "Location: ../desempeno/index.php?error=" .
        urlencode("Datos de desempeño inválidos.")
    );

    exit();

}


// ======================================================
// OBTENER DOCENTE
// ======================================================

$usuario_id = intval(
    $_SESSION["id"] ?? 0
);


if ($usuario_id <= 0) {

    header(
        "Location: ../index.php"
    );

    exit();

}


$stmt = $conexion->prepare("
    SELECT id
    FROM docentes
    WHERE usuario_id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Error preparando consulta del docente."
    );

}


$stmt->bind_param(
    "i",
    $usuario_id
);

$stmt->execute();

$resultado =
    $stmt->get_result();


if ($resultado->num_rows === 0) {

    $stmt->close();

    die(
        "No se encontró el docente."
    );

}


$docente = $resultado->fetch_assoc();

$docente_id = intval(
    $docente["id"]
);

$stmt->close();


// ======================================================
// VERIFICAR QUE EL DOCENTE TENGA LA CARGA
// ======================================================

$stmt = $conexion->prepare("
    SELECT id
    FROM carga_academica
    WHERE docente_id = ?
    AND curso_id = ?
    AND materia_id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Error preparando la carga académica."
    );

}


$stmt->bind_param(
    "iii",
    $docente_id,
    $curso_id,
    $materia_id
);

$stmt->execute();

$resultado =
    $stmt->get_result();


if ($resultado->num_rows === 0) {

    $stmt->close();

    header(
        "Location: ../desempeno/index.php?error=" .
        urlencode(
            "No tienes asignada esta materia para este curso."
        )
    );

    exit();

}


$carga = $resultado->fetch_assoc();

$carga_id = intval(
    $carga["id"]
);

$stmt->close();


// ======================================================
// OBTENER PERÍODO
// ======================================================
//
// Como tu sistema maneja un período seleccionado
// en la URL del reporte, lo recibimos desde el formulario.
// ======================================================

$periodo_id = intval(
    $_POST["periodo_id"] ?? 0
);


// ======================================================
// SI NO LLEGÓ PERÍODO, INTENTAR USAR EL PRIMERO
// HABILITADO
// ======================================================

if ($periodo_id <= 0) {

    $resultadoPeriodo =
        $conexion->query("
            SELECT id
            FROM periodos
            WHERE habilitado = 1
            ORDER BY id ASC
            LIMIT 1
        ");


    if (
        $resultadoPeriodo &&
        $resultadoPeriodo->num_rows > 0
    ) {

        $periodo =
            $resultadoPeriodo->fetch_assoc();

        $periodo_id =
            intval($periodo["id"]);

    }

}


// ======================================================
// VALIDAR PERÍODO
// ======================================================

if ($periodo_id <= 0) {

    header(
        "Location: ../desempeno/index.php?id=" .
        $carga_id .
        "&error=" .
        urlencode(
            "No hay un período válido seleccionado."
        )
    );

    exit();

}


$stmt = $conexion->prepare("
    SELECT
        id,
        nombre,
        habilitado
    FROM periodos
    WHERE id = ?
    LIMIT 1
");


if (!$stmt) {

    die(
        "Error preparando consulta del período."
    );

}


$stmt->bind_param(
    "i",
    $periodo_id
);

$stmt->execute();

$resultado =
    $stmt->get_result();


if ($resultado->num_rows === 0) {

    $stmt->close();

    header(
        "Location: ../desempeno/index.php?id=" .
        $carga_id .
        "&error=" .
        urlencode(
            "El período seleccionado no existe."
        )
    );

    exit();

}


$periodo =
    $resultado->fetch_assoc();

$stmt->close();


// ======================================================
// BLOQUEAR SI EL COORDINADOR DESHABILITÓ EL PERÍODO
// ======================================================

if (
    intval($periodo["habilitado"]) !== 1
) {

    header(
        "Location: ../desempeno/index.php?id=" .
        $carga_id .
        "&periodo_id=" .
        $periodo_id .
        "&error=" .
        urlencode(
            "El período está deshabilitado por el coordinador."
        )
    );

    exit();

}


// ======================================================
// PREPARAR INSERT / UPDATE
// ======================================================

$stmt = $conexion->prepare("
    SELECT id
    FROM desempeno_estudiantes

    WHERE estudiante_id = ?
    AND carga_academica_id = ?
    AND periodo_id = ?

    LIMIT 1
");


if (!$stmt) {

    die(
        "Error preparando consulta de desempeño."
    );

}


// ======================================================
// GUARDAR CADA ESTUDIANTE
// ======================================================

$guardados = 0;


foreach (
    $colores as $estudiante_id => $color_id
) {

    $estudiante_id =
        intval($estudiante_id);

    $color_id =
        intval($color_id);


    // --------------------------------------------------
    // IGNORAR SI NO HAY DESEMPEÑO SELECCIONADO
    // --------------------------------------------------

    if (
        $estudiante_id <= 0 ||
        !in_array(
            $color_id,
            [1, 2, 3],
            true
        )
    ) {

        continue;

    }


    // --------------------------------------------------
    // VERIFICAR ESTUDIANTE
    // --------------------------------------------------

    $verificarEstudiante =
        $conexion->prepare("
            SELECT id
            FROM estudiantes

            WHERE id = ?
            AND curso_id = ?
            AND estado = 'Activo'

            LIMIT 1
        ");


    if (!$verificarEstudiante) {

        continue;

    }


    $verificarEstudiante->bind_param(
        "ii",
        $estudiante_id,
        $curso_id
    );

    $verificarEstudiante->execute();

    $resultadoEstudiante =
        $verificarEstudiante->get_result();


    if (
        $resultadoEstudiante->num_rows === 0
    ) {

        $verificarEstudiante->close();

        continue;

    }


    $verificarEstudiante->close();


    // --------------------------------------------------
    // BUSCAR DESEMPEÑO EXISTENTE
    // --------------------------------------------------

    $stmt->bind_param(
        "iii",
        $estudiante_id,
        $carga_id,
        $periodo_id
    );

    $stmt->execute();

    $resultado =
        $stmt->get_result();


    // --------------------------------------------------
    // ACTUALIZAR
    // --------------------------------------------------

    if (
        $resultado->num_rows > 0
    ) {

        $registro =
            $resultado->fetch_assoc();

        $registro_id =
            intval($registro["id"]);


        $actualizar =
            $conexion->prepare("
                UPDATE desempeno_estudiantes

                SET color_id = ?

                WHERE id = ?
            ");


        if (!$actualizar) {

            continue;

        }


        $actualizar->bind_param(
            "ii",
            $color_id,
            $registro_id
        );


        if (
            $actualizar->execute()
        ) {

            $guardados++;

        }


        $actualizar->close();


    } else {


        // --------------------------------------------------
        // INSERTAR
        // --------------------------------------------------

        $insertar =
            $conexion->prepare("
                INSERT INTO desempeno_estudiantes
                (
                    estudiante_id,
                    carga_academica_id,
                    periodo_id,
                    color_id
                )

                VALUES
                (
                    ?,
                    ?,
                    ?,
                    ?
                )
            ");


        if (!$insertar) {

            continue;

        }


        $insertar->bind_param(
            "iiii",
            $estudiante_id,
            $carga_id,
            $periodo_id,
            $color_id
        );


        if (
            $insertar->execute()
        ) {

            $guardados++;

        }


        $insertar->close();

    }

}


$stmt->close();


// ======================================================
// RESULTADO
// ======================================================

if ($guardados > 0) {

    $mensaje =
        "Desempeño guardado correctamente.";

} else {

    $mensaje =
        "No se seleccionó ningún desempeño.";

}


// ======================================================
// REGRESAR AL REPORTE
// ======================================================

header(
    "Location: ../desempeno/index.php"
    . "?id=" . $carga_id
    . "&periodo_id=" . $periodo_id
    . "&success="
    . urlencode($mensaje)
);

exit();

?>