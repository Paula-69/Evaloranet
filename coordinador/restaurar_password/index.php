<?php

session_start();

require_once("../../config/config.php");
require_once("../../config/seguridad/coordinador.php");

$mensaje = "";
$tipo = "";


// =====================================================
// RESTAURAR CONTRASEÑA
// =====================================================

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $usuario_id = isset($_POST["usuario_id"])
        ? (int) $_POST["usuario_id"]
        : 0;


    if ($usuario_id <= 0) {

        $mensaje = "Usuario no válido.";
        $tipo = "danger";

    } else {

        /*
        |--------------------------------------------------------------------------
        | BUSCAR USUARIO
        |--------------------------------------------------------------------------
        | Solamente permitimos docentes y coordinadores.
        */

        $sql = "
            SELECT
                id,
                documento,
                nombres,
                apellidos,
                rol,
                activo
            FROM usuarios
            WHERE id = ?
            AND rol IN ('docente', 'coordinador')
            LIMIT 1
        ";


        $stmt = $conexion->prepare($sql);


        if (!$stmt) {

            $mensaje = "Error preparando la consulta.";
            $tipo = "danger";

        } else {

            $stmt->bind_param("i", $usuario_id);

            $stmt->execute();

            $resultado = $stmt->get_result();


            if ($resultado->num_rows !== 1) {

                $mensaje =
                    "El usuario no existe o no es docente/coordinador.";

                $tipo = "danger";

            } else {

                $usuario = $resultado->fetch_assoc();


                /*
                |--------------------------------------------------------------------------
                | CREAR NUEVA CONTRASEÑA
                |--------------------------------------------------------------------------
                |
                | La contraseña temporal será el número de documento.
                |
                | IMPORTANTE:
                | Se guarda utilizando password_hash().
                */

                $passwordTemporal = $usuario["documento"];

                $passwordHash = password_hash(
                    $passwordTemporal,
                    PASSWORD_DEFAULT
                );


                /*
                |--------------------------------------------------------------------------
                | ACTUALIZAR CONTRASEÑA
                |--------------------------------------------------------------------------
                |
                | cambiar_password = 1 obliga al usuario a cambiarla
                | antes de poder utilizar normalmente el sistema.
                */

                $sqlUpdate = "
                    UPDATE usuarios
                    SET
                        password = ?,
                        cambiar_password = 1
                    WHERE id = ?
                    AND rol IN ('docente', 'coordinador')
                ";


                $stmtUpdate =
                    $conexion->prepare($sqlUpdate);


                if (!$stmtUpdate) {

                    $mensaje =
                        "Error preparando la actualización.";

                    $tipo = "danger";

                } else {

                    $stmtUpdate->bind_param(
                        "si",
                        $passwordHash,
                        $usuario_id
                    );


                    if ($stmtUpdate->execute()) {

                        $mensaje =
                            "Contraseña restaurada correctamente para "
                            . $usuario["nombres"]
                            . " "
                            . $usuario["apellidos"]
                            . ". La nueva contraseña temporal es su número de documento. "
                            . "Deberá cambiarla al iniciar sesión.";

                        $tipo = "success";

                    } else {

                        $mensaje =
                            "No se pudo restaurar la contraseña.";

                        $tipo = "danger";
                    }


                    $stmtUpdate->close();
                }
            }


            $stmt->close();
        }
    }
}


// =====================================================
// OBTENER DOCENTES Y COORDINADORES
// =====================================================

$sqlUsuarios = "
    SELECT
        id,
        documento,
        nombres,
        apellidos,
        rol,
        activo
    FROM usuarios
    WHERE rol IN ('docente', 'coordinador')
    ORDER BY
        rol ASC,
        apellidos ASC,
        nombres ASC
";


$resultadoUsuarios =
    $conexion->query($sqlUsuarios);

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Restaurar contraseña</title>


    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body>


<?php include("../../includes/navbar.php"); ?>


<div class="container-fluid">

    <div class="row">


        <!-- ==========================================
             SIDEBAR
        =========================================== -->

        <div class="col-md-2 p-0">

            <?php include("../../includes/sidebar.php"); ?>

        </div>


        <!-- ==========================================
             CONTENIDO
        =========================================== -->

        <div class="col-md-10">

            <div class="container mt-4">


                <h2>

                    Restaurar contraseña

                </h2>


                <p class="text-muted">

                    Restaura las contraseñas de docentes y coordinadores.

                </p>


                <hr>


                <!-- ==================================
                     MENSAJE
                =================================== -->

                <?php if ($mensaje !== ""): ?>

                    <div
                        class="alert alert-<?= htmlspecialchars($tipo) ?>"
                    >

                        <?= htmlspecialchars($mensaje) ?>

                    </div>

                <?php endif; ?>


                <!-- ==================================
                     INFORMACIÓN
                =================================== -->

                <div class="alert alert-warning">

                    <strong>Importante</strong>

                    <br><br>

                    Esta opción solamente está disponible
                    para <strong>docentes y coordinadores</strong>.

                    <br><br>

                    Al restaurar una contraseña:

                    <ul class="mb-0">

                        <li>
                            La contraseña temporal será el número
                            de documento.
                        </li>

                        <li>
                            La contraseña se almacena de forma segura.
                        </li>

                        <li>
                            El usuario deberá cambiarla al iniciar sesión.
                        </li>

                    </ul>

                </div>


                <!-- ==================================
                     TABLA
                =================================== -->

                <div class="card shadow">


                    <div class="card-header bg-primary text-white">

                        <h5 class="mb-0">

                            Docentes y coordinadores

                        </h5>

                    </div>


                    <div class="card-body p-0">


                        <?php

                        if (
                            $resultadoUsuarios &&
                            $resultadoUsuarios->num_rows > 0
                        ):

                        ?>


                            <div class="table-responsive">


                                <table
                                    class="table table-bordered table-hover mb-0"
                                >


                                    <thead class="table-light">


                                        <tr>

                                            <th>
                                                #
                                            </th>

                                            <th>
                                                Documento
                                            </th>

                                            <th>
                                                Nombre
                                            </th>

                                            <th>
                                                Rol
                                            </th>

                                            <th>
                                                Estado
                                            </th>

                                            <th>
                                                Acción
                                            </th>

                                        </tr>


                                    </thead>


                                    <tbody>


                                    <?php

                                    $contador = 1;


                                    while (
                                        $usuario =
                                        $resultadoUsuarios->fetch_assoc()
                                    ):

                                    ?>


                                        <tr>


                                            <!-- NUMERO -->

                                            <td>

                                                <?= $contador++ ?>

                                            </td>


                                            <!-- DOCUMENTO -->

                                            <td>

                                                <?= htmlspecialchars(
                                                    $usuario["documento"]
                                                ) ?>

                                            </td>


                                            <!-- NOMBRE -->

                                            <td>

                                                <?= htmlspecialchars(
                                                    $usuario["apellidos"]
                                                    . ", "
                                                    . $usuario["nombres"]
                                                ) ?>

                                            </td>


                                            <!-- ROL -->

                                            <td>


                                                <?php

                                                if (
                                                    $usuario["rol"]
                                                    === "coordinador"
                                                ):

                                                ?>

                                                    <span
                                                        class="badge bg-danger"
                                                    >

                                                        Coordinador

                                                    </span>


                                                <?php else: ?>


                                                    <span
                                                        class="badge bg-success"
                                                    >

                                                        Docente

                                                    </span>


                                                <?php endif; ?>


                                            </td>


                                            <!-- ESTADO -->

                                            <td>


                                                <?php

                                                if (
                                                    $usuario["activo"] == 1
                                                ):

                                                ?>

                                                    <span
                                                        class="badge bg-success"
                                                    >

                                                        Activo

                                                    </span>


                                                <?php else: ?>


                                                    <span
                                                        class="badge bg-secondary"
                                                    >

                                                        Inactivo

                                                    </span>


                                                <?php endif; ?>


                                            </td>


                                            <!-- ACCION -->

                                            <td>


                                                <?php

                                                /*
                                                |------------------------------------------
                                                | No mostramos el botón para usuarios
                                                | inactivos.
                                                |------------------------------------------
                                                */

                                                if (
                                                    $usuario["activo"] == 1
                                                ):

                                                ?>


                                                    <form
                                                        method="POST"
                                                        onsubmit="
                                                            return confirm(
                                                                '¿Seguro que deseas restaurar la contraseña de este usuario?'
                                                            );
                                                        "
                                                    >


                                                        <input
                                                            type="hidden"
                                                            name="usuario_id"
                                                            value="<?= (int) $usuario["id"] ?>"
                                                        >


                                                        <button
                                                            type="submit"
                                                            class="btn btn-warning btn-sm"
                                                        >

                                                            Restaurar contraseña

                                                        </button>


                                                    </form>


                                                <?php else: ?>


                                                    <span
                                                        class="text-muted"
                                                    >

                                                        Usuario inactivo

                                                    </span>


                                                <?php endif; ?>


                                            </td>


                                        </tr>


                                    <?php

                                    endwhile;

                                    ?>


                                    </tbody>


                                </table>


                            </div>


                        <?php else: ?>


                            <div class="alert alert-info m-3">

                                No hay docentes ni coordinadores
                                registrados.

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