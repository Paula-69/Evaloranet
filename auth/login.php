
<?php
require_once __DIR__ . '/../config/config.php';

/* Redirigir si el usuario ya inició sesión */
if (isset($_SESSION['usuario_id'], $_SESSION['rol'])) {
    $rol = strtolower(trim((string) $_SESSION['rol']));

    $rutas = [
        'administrador' => '../admin/index.php',
        'admin'         => '../admin/index.php',
        'coordinador'   => '../coordinador/index.php',
        'docente'       => '../docente/index.php',
        'estudiante'    => '../estudiante/index.php',
    ];

    if (isset($rutas[$rol])) {
        header('Location: ' . $rutas[$rol]);
        exit;
    }
}

require_once __DIR__ . '/../includes/header.php';
?>

<main class="login-shell">

    <!-- Fondo decorativo -->
    <div class="login-decoration login-decoration-one" aria-hidden="true"></div>
    <div class="login-decoration login-decoration-two" aria-hidden="true"></div>

    <!-- Presentación izquierda -->
    <section class="login-welcome">
        <div class="login-welcome-content">

            <div class="login-tag">
                <span aria-hidden="true">🎓</span>
                SISTEMA DE SEGUIMIENTO ACADÉMICO
            </div>

            <h1>
                Bienvenido a
                <span class="login-title-accent">EvaloraNet</span>
            </h1>

            <div class="login-yellow-line"></div>

            <p class="login-description">
                Un espacio diseñado para acompañar, organizar y fortalecer
                el proceso académico de nuestra comunidad educativa.
            </p>

            <div class="login-motivation">
                <div class="login-motivation-icon" aria-hidden="true">📊</div>

                <div class="login-motivation-text">
                    <strong>Conéctate con algo seguro</strong>
                    <span>para lograr un triunfo.</span>
                </div>
            </div>

        </div>
    </section>

    <!-- Elementos flotantes decorativos -->
    <div class="login-floating login-floating-book" aria-hidden="true">
        📚
    </div>

    <div class="login-floating login-floating-graduate" aria-hidden="true">
        🎓
    </div>

    <div class="login-floating login-floating-chart" aria-hidden="true">
        📊
    </div>

    <!-- Formulario de acceso -->
    <section class="login-panel">
        <div class="login-card">

            <div class="login-brand">
                <div class="login-brand-icon" aria-hidden="true">📚</div>

                <div class="login-brand-copy">
                    <span class="login-brand-name">
                        EVALORA<span>NET</span>
                    </span>

                    <small>Seguimiento Académico</small>
                </div>
            </div>

            <h2>Iniciar sesión</h2>

            <p class="login-subtitle">
                Ingresa tus datos para continuar.
            </p>

            <?php if (!empty($_SESSION['mensaje_error'])): ?>
                <div class="alert alert-danger" role="alert">
                    <?= htmlspecialchars(
                        (string) $_SESSION['mensaje_error'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>
                <?php unset($_SESSION['mensaje_error']); ?>
            <?php endif; ?>

            <?php if (!empty($_SESSION['mensaje_exito'])): ?>
                <div class="alert alert-success" role="alert">
                    <?= htmlspecialchars(
                        (string) $_SESSION['mensaje_exito'],
                        ENT_QUOTES,
                        'UTF-8'
                    ) ?>
                </div>
                <?php unset($_SESSION['mensaje_exito']); ?>
            <?php endif; ?>

            <form action="validar_login.php" method="POST">

                <div class="login-form-group">
                    <label for="usuario" class="form-label">
                        Usuario o correo electrónico
                    </label>

                    <div class="login-input-wrap">
                        <span class="login-input-icon" aria-hidden="true">♙</span>

                        <input
                            type="text"
                            id="usuario"
                            name="usuario"
                            class="form-control"
                            placeholder="Ingresa tu usuario"
                            autocomplete="username"
                            required
                            autofocus
                        >
                    </div>
                </div>

                <div class="login-form-group">
                    <label for="contrasena" class="form-label">
                        Contraseña
                    </label>

                    <div class="login-input-wrap">
                        <span class="login-input-icon" aria-hidden="true">🔒</span>

                        <input
                            type="password"
                            id="contrasena"
                            name="contrasena"
                            class="form-control"
                            placeholder="Ingresa tu contraseña"
                            autocomplete="current-password"
                            required
                        >
                    </div>
                </div>

                <button type="submit" class="login-submit">
                    <span>Iniciar sesión</span>
                    <span class="login-submit-arrow" aria-hidden="true">⟶</span>
                </button>

            </form>

            <div class="login-footer">
                <span aria-hidden="true">🔒</span>
                Tu información se maneja de forma segura.
            </div>

        </div>
    </section>

</main>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
```