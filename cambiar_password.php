<?php

session_start();

require_once __DIR__ . "/config/config.php";

/*
|--------------------------------------------------------------------------
| Verificar sesión
|--------------------------------------------------------------------------
*/

if (!isset($_SESSION["id"])) {

    header("Location: auth/login.php");
    exit();

}

$mensaje = "";
$error = "";


/*
|--------------------------------------------------------------------------
| Procesar cambio de contraseña
|--------------------------------------------------------------------------
*/

if ($_SERVER["REQUEST_METHOD"] === "POST") {

    $password = trim($_POST["password"] ?? "");
    $password_confirmar = trim($_POST["password_confirmar"] ?? "");

    if ($password === "" || $password_confirmar === "") {

        $error = "Debes completar todos los campos.";

    } elseif ($password !== $password_confirmar) {

        $error = "Las contraseñas no coinciden.";

    } elseif (strlen($password) < 6) {

        $error = "La contraseña debe tener mínimo 6 caracteres.";

    } else {

        $password_hash = password_hash(
            $password,
            PASSWORD_DEFAULT
        );

        $sql = "UPDATE usuarios
                SET password = ?,
                    cambiar_password = 0
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {

            $error = "Error al preparar la consulta.";

        } else {

            $stmt->bind_param(
                "si",
                $password_hash,
                $_SESSION["id"]
            );

            if ($stmt->execute()) {

                /*
                |----------------------------------------------
                | Redirigir según el rol
                |----------------------------------------------
                */

                if ($_SESSION["rol"] === "coordinador") {

                    header("Location: coordinador/index.php");
                    exit();

                }

                if ($_SESSION["rol"] === "docente") {

                    header("Location: docente/index.php");
                    exit();

                }

                if ($_SESSION["rol"] === "estudiante") {

                    header("Location: estudiante/index.php");
                    exit();

                }

                header("Location: auth/login.php");
                exit();

            } else {

                $error = "No se pudo cambiar la contraseña.";

            }

        }

    }

}

?>

<!DOCTYPE html>

<html lang="es">

<head>

    <meta charset="UTF-8">

    <meta
        name="viewport"
        content="width=device-width, initial-scale=1.0"
    >

    <title>Cambiar contraseña</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

</head>


<body class="bg-light">


<div class="container mt-5">

    <div class="row justify-content-center">

        <div class="col-md-5">

            <div class="card shadow">

                <div class="card-header bg-primary text-white">

                    <h4 class="mb-0">
                        Cambiar contraseña
                    </h4>

                </div>


                <div class="card-body">

                    <div class="alert alert-info">

                        Por seguridad, debes cambiar tu contraseña
                        antes de continuar.

                    </div>


                    <?php if ($error !== ""): ?>

                        <div class="alert alert-danger">

                            <?= htmlspecialchars($error) ?>

                        </div>

                    <?php endif; ?>


                    <form method="POST">


                        <div class="mb-3">

                            <label class="form-label">

                                Nueva contraseña

                            </label>

                            <input
                                type="password"
                                name="password"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <div class="mb-3">

                            <label class="form-label">

                                Confirmar contraseña

                            </label>

                            <input
                                type="password"
                                name="password_confirmar"
                                class="form-control"
                                minlength="6"
                                required
                            >

                        </div>


                        <button
                            type="submit"
                            class="btn btn-primary w-100"
                        >

                            Cambiar contraseña

                        </button>


                    </form>


                    <div class="text-center mt-3">

                        <a
                            href="auth/logout.php"
                            class="btn btn-outline-danger btn-sm"
                        >

                            Cerrar sesión

                        </a>

                    </div>


                </div>

            </div>

        </div>

    </div>

</div>


</body>

</html>