
<?php

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

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

        $sql = "
            SELECT id, documento, nombres, apellidos, rol, activo
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

                if ((int) $usuario["activo"] !== 1) {

                    $mensaje =
                        "No se puede restaurar la contraseña de un usuario inactivo.";
                    $tipo = "danger";

                } else {

                    $passwordTemporal = $usuario["documento"];

                    $passwordHash = password_hash(
                        $passwordTemporal,
                        PASSWORD_DEFAULT
                    );

                    $sqlUpdate = "
                        UPDATE usuarios
                        SET password = ?, cambiar_password = 1
                        WHERE id = ?
                        AND rol IN ('docente', 'coordinador')
                        AND activo = 1
                    ";

                    $stmtUpdate = $conexion->prepare($sqlUpdate);

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

                        if (
                            $stmtUpdate->execute()
                            && $stmtUpdate->affected_rows === 1
                        ) {

                            $mensaje =
                                "Contraseña restaurada correctamente para "
                                . $usuario["nombres"] . " "
                                . $usuario["apellidos"]
                                . ". La contraseña temporal es su número de documento. "
                                . "Deberá cambiarla al iniciar sesión.";

                            $tipo = "success";

                        } else {

                            $mensaje =
                                "No se pudo restaurar la contraseña. Verifica el estado del usuario.";

                            $tipo = "danger";
                        }

                        $stmtUpdate->close();
                    }
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
    SELECT id, documento, nombres, apellidos, rol, activo
    FROM usuarios
    WHERE rol IN ('docente', 'coordinador')
    ORDER BY rol ASC, apellidos ASC, nombres ASC
";

$resultadoUsuarios = $conexion->query($sqlUsuarios);

?>

<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Restaurar contraseña | EvaloraNet</title>

    <!-- Bootstrap -->
    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- CSS de EvaloraNet -->
    <link rel="stylesheet" href="/Evaloranet/assets/style.css?v=13">

    <!-- JavaScript del proyecto -->
    <script
        src="/Evaloranet/assets/js/app.js"
        defer
    ></script>
</head>

<body>

<?php include("../../includes/navbar.php"); ?>

<div class="evalora-layout">

    <?php include("../../includes/sidebar.php"); ?>

    <main class="evalora-main">

        <div class="restaurar-page">

            <header class="restaurar-heading">
                <h1>Restaurar contraseña</h1>
                <p>
                    Administra las contraseñas de docentes y coordinadores.
                </p>
            </header>

            <?php if ($mensaje !== ""): ?>
                <div
                    class="alert alert-<?= htmlspecialchars(
                        $tipo,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>"
                    role="alert"
                >
                    <?= htmlspecialchars(
                        $mensaje,
                        ENT_QUOTES,
                        "UTF-8"
                    ) ?>
                </div>
            <?php endif; ?>

            <section class="alert alert-warning restaurar-aviso">
                <h2 class="h6 fw-bold">Importante</h2>

                <p>
                    Esta opción está disponible para
                    <strong>docentes y coordinadores</strong>.
                </p>

                <p class="mb-2">Al restaurar una contraseña:</p>

                <ul class="mb-0">
                    <li>
                        La contraseña temporal será el número de documento.
                    </li>
                    <li>
                        La contraseña se almacena mediante un hash seguro.
                    </li>
                    <li>
                        El usuario deberá cambiarla al iniciar sesión.
                    </li>
                </ul>
            </section>

            <section class="card shadow-sm restaurar-card">

                <div class="card-header bg-primary text-white">
                    <h2 class="h5 mb-0">
                        Docentes y coordinadores
                    </h2>
                </div>

                <div class="card-body p-0">

                    <?php if (
                        $resultadoUsuarios &&
                        $resultadoUsuarios->num_rows > 0
                    ): ?>

                        <div class="table-responsive">

                            <table class="table table-bordered table-hover align-middle mb-0">

                                <thead class="table-light">
                                    <tr>
                                        <th scope="col">#</th>
                                        <th scope="col">Documento</th>
                                        <th scope="col">Nombre</th>
                                        <th scope="col">Rol</th>
                                        <th scope="col">Estado</th>
                                        <th scope="col">Acción</th>
                                    </tr>
                                </thead>

                                <tbody>

                                <?php
                                $contador = 1;

                                while (
                                    $usuario = $resultadoUsuarios->fetch_assoc()
                                ):
                                ?>

                                    <tr>
                                        <td><?= $contador++ ?></td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $usuario["documento"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </td>

                                        <td>
                                            <?= htmlspecialchars(
                                                $usuario["apellidos"] . ", " .
                                                $usuario["nombres"],
                                                ENT_QUOTES,
                                                "UTF-8"
                                            ) ?>
                                        </td>

                                        <td>
                                            <?php if (
                                                $usuario["rol"] === "coordinador"
                                            ): ?>
                                                <span class="badge bg-danger">
                                                    Coordinador
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-success">
                                                    Docente
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if (
                                                (int) $usuario["activo"] === 1
                                            ): ?>
                                                <span class="badge bg-success">
                                                    Activo
                                                </span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">
                                                    Inactivo
                                                </span>
                                            <?php endif; ?>
                                        </td>

                                        <td>
                                            <?php if (
                                                (int) $usuario["activo"] === 1
                                            ): ?>

                                                <form
                                                    method="POST"
                                                    class="restaurar-form"
                                                    onsubmit="return confirm('¿Seguro que deseas restaurar la contraseña de este usuario?');"
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

                                                <span class="text-muted">
                                                    Usuario inactivo
                                                </span>

                                            <?php endif; ?>
                                        </td>
                                    </tr>

                                <?php endwhile; ?>

                                </tbody>
                            </table>
                        </div>

                    <?php else: ?>

                        <div class="alert alert-info m-3">
                            No hay docentes ni coordinadores registrados.
                        </div>

                    <?php endif; ?>

                </div>
            </section>

        </div>

    </main>
</div>

<?php include("../../includes/footer.php"); ?>

</body>
</html>
