
<?php
session_start();

require_once __DIR__ . "/config/config.php";

/* Verificar sesión */
if (!isset($_SESSION["id"])) {
    header("Location: auth/login.php");
    exit();
}

$mensaje = "";
$error = "";

/* Procesar cambio de contraseña */
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
        $password_hash = password_hash($password, PASSWORD_DEFAULT);

        $sql = "UPDATE usuarios
                SET password = ?, cambiar_password = 0
                WHERE id = ?";

        $stmt = $conexion->prepare($sql);

        if (!$stmt) {
            $error = "Error al preparar la consulta.";
        } else {
            $id_usuario = (int) $_SESSION["id"];
            $stmt->bind_param("si", $password_hash, $id_usuario);

            if ($stmt->execute() && $stmt->affected_rows >= 0) {
                $rol = strtolower(trim((string) ($_SESSION["rol"] ?? "")));

                $rutas = [
                    "administrador" => "admin/index.php",
                    "admin"         => "admin/index.php",
                    "coordinador"   => "coordinador/index.php",
                    "docente"       => "docente/index.php",
                    "estudiante"    => "estudiante/index.php",
                ];

                if (isset($rutas[$rol])) {
                    header("Location: " . $rutas[$rol]);
                    exit();
                }

                header("Location: auth/login.php");
                exit();
            } else {
                $error = "No se pudo cambiar la contraseña.";
            }

            $stmt->close();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambiar contraseña | EvaloraNet</title>

    <link
        href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css"
        rel="stylesheet"
    >

    <!-- Hoja de estilos del proyecto -->
    <link
        rel="stylesheet"
        href="/Evaloranet/assets/style.css?v=20"
    >

    <style>
        html,
        body {
            min-height: 100%;
            margin: 0;
        }

        body.password-page {
            min-height: 100vh;
            color: #fff;
            font-family: "Segoe UI", Arial, sans-serif;
            background:
                linear-gradient(
                    115deg,
                    rgba(3, 20, 49, .77),
                    rgba(8, 43, 91, .60),
                    rgba(3, 20, 49, .78)
                ),
                url("/Evaloranet/img/Login.png") center center / cover fixed no-repeat;
        }

        .password-page *,
        .password-page *::before,
        .password-page *::after {
            box-sizing: border-box;
        }

        .password-screen {
            min-height: 100vh;
            width: 100%;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 35px 18px;
        }

        .password-card {
            width: 100%;
            max-width: 470px;
            padding: 32px;
            border: 1px solid rgba(255,255,255,.35);
            border-radius: 25px;
            background: rgba(43, 65, 98, .76);
            box-shadow: 0 24px 65px rgba(0,0,0,.30);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
        }

        .password-brand {
            display: flex;
            align-items: center;
            gap: 13px;
            margin-bottom: 30px;
        }

        .password-brand-icon {
            width: 54px;
            height: 54px;
            display: flex;
            align-items: center;
            justify-content: center;
            flex-shrink: 0;
            border-radius: 15px;
            background: #ffc400;
            font-size: 27px;
        }

        .password-brand-name {
            margin: 0;
            color: #fff;
            font-size: 21px;
            font-weight: 850;
        }

        .password-brand-name span {
            color: #ffc400;
        }

        .password-brand small {
            display: block;
            margin-top: 4px;
            color: rgba(255,255,255,.75);
            font-size: 12px;
        }

        .password-card h1 {
            margin: 0 0 9px;
            color: #fff;
            font-size: 29px;
            font-weight: 800;
        }

        .password-description {
            margin-bottom: 24px;
            color: rgba(255,255,255,.78);
            font-size: 14px;
        }

        .password-notice {
            padding: 13px 15px;
            margin-bottom: 24px;
            color: #fff;
            background: rgba(255,196,0,.10);
            border: 1px solid rgba(255,196,0,.42);
            border-radius: 12px;
            font-size: 13px;
            line-height: 1.6;
        }

        .password-field {
            margin-bottom: 20px;
        }

        .password-field label {
            display: block;
            margin-bottom: 9px;
            color: #fff;
            font-size: 13px;
            font-weight: 700;
        }

        .password-field input {
            width: 100%;
            min-height: 52px;
            padding: 13px 15px;
            color: #fff;
            background: rgba(255,255,255,.12);
            border: 1px solid rgba(255,255,255,.30);
            border-radius: 13px;
            outline: none;
            font-size: 14px;
        }

        .password-field input::placeholder {
            color: rgba(255,255,255,.60);
        }

        .password-field input:focus {
            background: rgba(255,255,255,.15);
            border-color: #ffc400;
            box-shadow: 0 0 0 3px rgba(255,196,0,.15);
        }

        .password-submit {
            width: 100%;
            min-height: 54px;
            margin-top: 5px;
            border: 0;
            border-radius: 14px;
            color: #fff;
            background: linear-gradient(110deg, #1684ff, #0754db);
            font-size: 15px;
            font-weight: 800;
            cursor: pointer;
            transition: transform .2s ease, box-shadow .2s ease;
        }

        .password-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(0,91,255,.3);
        }

        .password-logout {
            display: block;
            width: fit-content;
            margin: 18px auto 0;
            padding: 10px 18px;
            color: #fff;
            background: transparent;
            border: 1px solid rgba(255,255,255,.42);
            border-radius: 11px;
            text-decoration: none;
            font-size: 13px;
        }

        .password-logout:hover {
            color: #fff;
            background: rgba(255,255,255,.12);
        }

        .password-error {
            padding: 12px 14px;
            margin-bottom: 20px;
            color: #fff;
            background: rgba(220,53,69,.22);
            border: 1px solid rgba(255,120,130,.55);
            border-radius: 11px;
            font-size: 13px;
        }

        @media (max-width: 520px) {
            .password-card {
                padding: 24px 20px;
                border-radius: 21px;
            }

            .password-card h1 {
                font-size: 25px;
            }
        }
    </style>
</head>

<body class="password-page">

<main class="password-screen">
    <section class="password-card">

        <div class="password-brand">
            <div class="password-brand-icon" aria-hidden="true">📚</div>
            <div>
                <p class="password-brand-name">
                    EVALORA<span>NET</span>
                </p>
                <small>Seguimiento Académico</small>
            </div>
        </div>

        <h1>Cambiar contraseña</h1>
        <p class="password-description">
            Actualiza tu contraseña para continuar de forma segura.
        </p>

        <div class="password-notice">
            🔒 Por seguridad, debes cambiar tu contraseña antes de continuar.
        </div>

        <?php if ($error !== ""): ?>
            <div class="password-error" role="alert">
                <?= htmlspecialchars($error, ENT_QUOTES, "UTF-8") ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="password-field">
                <label for="password">Nueva contraseña</label>
                <input
                    type="password"
                    id="password"
                    name="password"
                    placeholder="Ingresa tu nueva contraseña"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >
            </div>

            <div class="password-field">
                <label for="password_confirmar">Confirmar contraseña</label>
                <input
                    type="password"
                    id="password_confirmar"
                    name="password_confirmar"
                    placeholder="Confirma tu nueva contraseña"
                    minlength="6"
                    autocomplete="new-password"
                    required
                >
            </div>

            <button type="submit" class="password-submit">
                Cambiar contraseña&nbsp; →
            </button>
        </form>

        <a href="auth/logout.php" class="password-logout">
            Cerrar sesión
        </a>

    </section>
</main>

</body>
</html>
